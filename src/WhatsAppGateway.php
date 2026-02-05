<?php

namespace Mdestafadilah\ApiWaRest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * WhatsApp Gateway Library
 * 
 * A comprehensive library for sending WhatsApp messages through multiple backend services
 * 
 * @package Mdestafadilah\ApiWaRest
 * @author mdestafadilah <desta.08b@gmail.com>
 */
class WhatsAppGateway
{
    /**
     * @var Client Guzzle HTTP client
     */
    private $clientGuzzle;

    /**
     * @var array Configuration settings for WhatsApp servers
     */
    private $config;

    /**
     * @var callable|null Logger callback function
     */
    private $logger;

    /**
     * Constructor
     * 
     * @param array $config Configuration array for WhatsApp servers
     * @param callable|null $logger Optional logger callback function
     */
    public function __construct(array $config = [], $logger = null)
    {
        $this->clientGuzzle = new Client();
        $this->config = $this->mergeDefaultConfig($config);
        $this->logger = $logger;
    }

    /**
     * Send WhatsApp Message
     * 
     * Main method to send WhatsApp messages through different backend services
     * 
     * @param array $data Message data containing 'nomorhp' and 'pesanwa'
     * @param int $be Backend service ID (3, 4, 8, or 99)
     * @param bool $isGroup Whether the recipient is a group
     * @param bool $otomatis Enable automatic backend selection with health check
     * @param string $idUnik Unique identifier for logging
     * @return array Response with 'status' and 'message'
     * @throws \Exception If invalid backend is selected
     */
    public function sendMessage($data = [], $be = 3, $isGroup = false, $otomatis = false, $idUnik = '')
    {
        // 1. Validation
        if (!in_array($be, [3, 4, 8, 99]) && $otomatis == false) {
            throw new \Exception('BACKEND SERVICE WHATSAPP YANG TERSEDIA HANYA [3, 4, 8 (Free) dan 99 (Khusus OTP, berbayar! bro..)]');
        }

        // Validate required data
        if (empty($data['nomorhp']) || empty($data['pesanwa'])) {
            throw new \Exception('Data nomorhp dan pesanwa harus diisi');
        }

        // 2. Normalize Number
        $number = $this->normalizePhoneNumber($data['nomorhp']);

        // 3. Automatic Backend Selection (Health Check)
        if ($otomatis) {
            $randomBackend = $this->generateRandomServer();
            if ($this->checkBackendHealth($randomBackend)) {
                $be = $randomBackend;
            }
        }

        // 4. Build Payload
        $payload = $this->buildPayload($be, $number, $data['pesanwa'], $isGroup);

        if (!$payload) {
            throw new \Exception('BUDEG APA YA, PILIH SERVER 3, 4, 8 (Free) dan 99 (Khusus OTP, berbayar! bro..) JANGAN ASAL!!');
        }

        // 5. Save Log
        $this->logKirim($number, $data['pesanwa'], $payload['data'], empty($idUnik) ? $this->generateRandomText(4) : $idUnik);

        // 6. Send Request
        try {
            if ($be === 99) {
                $response = $this->clientGuzzle->request('POST', $payload['endpoint'], [
                    'headers' => $payload['headers'],
                    'form_params' => $payload['data']
                ]);
            } else {
                $requestOptions = [
                    'headers' => $payload['headers']
                ];

                // Only add body if it's not null
                if ($payload['data'] !== null) {
                    $requestOptions['body'] = $payload['is_json'] ? json_encode($payload['data']) : $payload['data'];
                }

                $response = $this->clientGuzzle->request('POST', $payload['endpoint'], $requestOptions);
            }

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
        } catch (ServerException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
        } catch (\Exception $e) {
            $statusCode = 500;
            $body = json_encode(['error' => $e->getMessage()]);
        }

        $returnRequest = [
            'status' => $statusCode,
            'message' => $body
        ];

        return $returnRequest;
    }

    /**
     * Build Message Payload
     *
     * @param int $backend Backend ID
     * @param string $number Recipient number
     * @param string $messageRaw Message raw content
     * @param bool $isGroup Is group
     * @return array|null Payload data or null
     */
    private function buildPayload($backend, $number, $messageRaw, $isGroup)
    {
        $isGroupBool = $isGroup ? true : false;
        $messageFull = $messageRaw . ($this->config['footer'] ?? '');
        $sender = $this->config['servers'][$backend] ?? null;

        if (!$sender) {
            return null;
        }

        switch ($backend) {
            case 3:
                return [
                    'data' => [
                        "session"  => $sender['session_id'],
                        "to"       => $number,
                        "is_group" => $isGroupBool,
                        "delay"    => 5000,
                        "text"     => str_replace(['\r\n', '\n', '\r'], "\n", $messageFull)
                    ],
                    'endpoint' => $sender['base_url'] . "/message/send-text",
                    'headers' => $sender['token'] ? ['Authorization' => "Bearer " . $sender['token']] : [],
                    'is_json' => true
                ];

            case 4:
                $n = strpos($number, 'g.us') !== false ? $number : ($isGroup ? $number . "@g.us" : $number);
                return [
                    'data' => [
                        "Phone" => $n,
                        "Body" => str_replace(['\r\n', '\n', '\r'], "\n", $messageFull),
                        "Id" => $this->generateRandomText(20)
                    ],
                    'endpoint' => $sender['base_url'] . "chat/send/text",
                    'headers' => $sender['token'] ? ['Token' => $sender['token']] : [],
                    'is_json' => true
                ];

            case 8:
                $n = strpos($number, 'g.us') !== false ? $number : $number;
                return [
                    'data' => [
                        "sessionId" => $sender['session_id'],
                        "chatId" => $n,
                        "message" => str_replace(['\r\n', '\n', '\r'], "\n", $messageFull),
                        "typingTime" => 5000,
                        "replyTo" => null
                    ],
                    'endpoint' => $sender['base_url'] . "/chats/send-text",
                    'headers' => $sender['token'] ? ['X-Api-Key' => $sender['token']] : [],
                    'is_json' => true
                ];

            case 99:
                if ($isGroup) return null;
                return [
                    'data' => [
                        'userkey' => $sender['userkey'],
                        'passkey' => $sender['passkey'],
                        'to' => $number,
                        'message' => $messageRaw // NO FOOTER
                    ],
                    'endpoint' => $sender['base_url'] . '/wareguler/api/sendWA',
                    'headers' => [],
                    'is_json' => false
                ];

            default:
                return null;
        }
    }

    /**
     * Check Backend Server Health
     *
     * @param int $be Backend ID
     * @return bool True if healthy
     */
    private function checkBackendHealth($be)
    {
        $sender = $this->config['servers'][$be] ?? null;
        
        if (!$sender) {
            return false;
        }

        $endPoint = $sender['base_url'];
        $token = $sender['token'] ?? '';
        $headers = ['Accept' => 'application/json'];

        switch ($be) {
            case 3:
                // No specific token header for backend 3
                break;
            case 4:
                $headers['Token'] = $token;
                break;
            case 8:
                $headers['X-Api-Key'] = $token;
                break;
            case 99:
                $headers['Authorization'] = "Bearer $token";
                break;
        }

        try {
            $response = $this->clientGuzzle->request('GET', $endPoint, [
                'headers' => $headers,
                'http_errors' => false,
                'timeout' => 5
            ]);

            $res = json_decode($response->getBody()->getContents(), true);

            switch ($be) {
                case 3:
                    return is_array($res) && isset($res['success']);
                case 4:
                    return ($res['code'] ?? 0) == 200;
                case 8:
                    return $res['success'] ?? false;
                case 99:
                    return ($res['status'] ?? '') == 'success';
                default:
                    return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate Random Server ID
     *
     * @param int $min Min ID
     * @param int $max Max ID
     * @param array $exclude Excluded IDs
     * @return int Server ID
     */
    private function generateRandomServer($min = 3, $max = 8, $exclude = [])
    {
        do {
            if (function_exists('random_int')) {
                $n = random_int($min, $max); // more secure
            } elseif (function_exists('mt_rand')) {
                $n = mt_rand($min, $max); // faster
            } else {
                $n = rand($min, $max); // old
            }
        } while (in_array($n, $exclude));

        return $n;
    }

    /**
     * Normalize Phone Number
     * Formats to 62...
     *
     * @param string $number Input number
     * @return string Normalized number
     */
    private function normalizePhoneNumber($number)
    {
        $output = preg_replace('/[^0-9]/', '', $number);
        return (mb_substr($output, 0, 1) == '0') ? "62" . ltrim($output, '0') : $output;
    }

    /**
     * Log Kirim Message
     * 
     * @param string $number Phone number
     * @param string $message Message content
     * @param mixed $payload Payload data
     * @param string $idUnik Unique identifier
     * @return void
     */
    private function logKirim($number, $message, $payload, $idUnik)
    {
        if (is_callable($this->logger)) {
            call_user_func($this->logger, [
                'number' => $number,
                'message' => $message,
                'payload' => $payload,
                'id_unik' => $idUnik,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Generate Random Text
     * 
     * @param int $length Length of random text
     * @param string $type Type of characters (alnum, numeric, alpha)
     * @return string Random text
     */
    private function generateRandomText($length = 10, $type = 'alnum')
    {
        $characters = '';
        
        switch ($type) {
            case 'numeric':
                $characters = '0123456789';
                break;
            case 'alpha':
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alnum':
            default:
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }

        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }

    /**
     * Merge Default Configuration
     * 
     * @param array $config User configuration
     * @return array Merged configuration
     */
    private function mergeDefaultConfig($config)
    {
        $default = [
            'footer' => '',
            'servers' => [
                3 => [
                    'base_url' => '',
                    'session_id' => '',
                    'token' => ''
                ],
                4 => [
                    'base_url' => '',
                    'token' => ''
                ],
                8 => [
                    'base_url' => '',
                    'session_id' => '',
                    'token' => ''
                ],
                99 => [
                    'base_url' => '',
                    'userkey' => '',
                    'passkey' => ''
                ]
            ]
        ];

        return array_replace_recursive($default, $config);
    }

    /**
     * Set Logger
     * 
     * @param callable $logger Logger callback function
     * @return void
     */
    public function setLogger(callable $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get Configuration
     * 
     * @return array Current configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set Configuration
     * 
     * @param array $config Configuration array
     * @return void
     */
    public function setConfig(array $config)
    {
        $this->config = $this->mergeDefaultConfig($config);
    }
}
