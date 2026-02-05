<?php

namespace Mdestafadilah\ApiWaRest;

use PDO;
use PDOException;

/**
 * Database Manager for WhatsApp Gateway
 * 
 * Manages SQLite database operations for storing server configurations
 * 
 * @package Mdestafadilah\ApiWaRest
 * @author mdestafadilah <desta.08b@gmail.com>
 */
class DatabaseManager
{
    /**
     * @var PDO Database connection
     */
    private $pdo;

    /**
     * @var string Database file path
     */
    private $dbPath;

    /**
     * Constructor
     * 
     * @param string $dbPath Path to SQLite database file
     * @throws PDOException If connection fails
     */
    public function __construct($dbPath = null)
    {
        $this->dbPath = $dbPath ?? __DIR__ . '/../data/wa_gateway.db';
        
        // Create data directory if not exists
        $dir = dirname($this->dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Initialize PDO connection
        $this->pdo = new PDO('sqlite:' . $this->dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Initialize Database
     * 
     * Creates tables if they don't exist
     * 
     * @return bool True on success
     */
    public function init()
    {
        $sql = "CREATE TABLE IF NOT EXISTS wa_servers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            backend_id INTEGER NOT NULL,
            name VARCHAR(255) NOT NULL,
            base_url TEXT NOT NULL,
            token TEXT,
            session_id VARCHAR(255),
            phone VARCHAR(50),
            userkey VARCHAR(255),
            passkey VARCHAR(255),
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

        // Create message logs table
        $sqlLogs = "CREATE TABLE IF NOT EXISTS wa_message_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            number VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            payload TEXT,
            id_unik VARCHAR(255),
            status VARCHAR(50),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

        try {
            $this->pdo->exec($sql);
            $this->pdo->exec($sqlLogs);
            
            // Create indexes for faster queries
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_backend_id ON wa_servers(backend_id)");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_is_active ON wa_servers(is_active)");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_number ON wa_message_logs(number)");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_id_unik ON wa_message_logs(id_unik)");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_created_at ON wa_message_logs(created_at)");
            
            return true;
        } catch (PDOException $e) {
            throw new \Exception("Failed to initialize database: " . $e->getMessage());
        }
    }

    /**
     * Get All Servers
     * 
     * @return array List of all servers
     */
    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM wa_servers ORDER BY backend_id ASC, id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get Server by ID
     * 
     * @param int $id Server ID
     * @return array|false Server data or false if not found
     */
    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM wa_servers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get Server by Backend ID
     * 
     * @param int $backendId Backend ID (3, 4, 8, 99)
     * @param bool $activeOnly Get only active servers
     * @return array|false Server data or false if not found
     */
    public function getByBackendId($backendId, $activeOnly = true)
    {
        $sql = "SELECT * FROM wa_servers WHERE backend_id = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY id DESC LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$backendId]);
        return $stmt->fetch();
    }

    /**
     * Get Active Servers
     * 
     * @return array List of active servers
     */
    public function getActiveServers()
    {
        $stmt = $this->pdo->query("SELECT * FROM wa_servers WHERE is_active = 1 ORDER BY backend_id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get Configuration Array
     * 
     * Converts database records to WhatsAppGateway configuration format
     * 
     * @return array Configuration array
     */
    public function getConfigArray()
    {
        $servers = $this->getActiveServers();
        $config = [
            'footer' => '',
            'servers' => []
        ];

        foreach ($servers as $server) {
            $backendId = (int)$server['backend_id'];
            
            $serverConfig = [
                'base_url' => $server['base_url']
            ];

            // Add specific fields based on backend type
            if (in_array($backendId, [3, 8])) {
                $serverConfig['session_id'] = $server['session_id'] ?? '';
            }
            
            if (in_array($backendId, [3, 4, 8])) {
                $serverConfig['token'] = $server['token'] ?? '';
            }

            if ($backendId === 99) {
                $serverConfig['userkey'] = $server['userkey'] ?? '';
                $serverConfig['passkey'] = $server['passkey'] ?? '';
            }

            $config['servers'][$backendId] = $serverConfig;
        }

        return $config;
    }

    /**
     * Create New Server
     * 
     * @param array $data Server data
     * @return int|false Last insert ID or false on failure
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO wa_servers (
            backend_id, name, base_url, token, session_id, 
            phone, userkey, passkey, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $data['backend_id'] ?? 3,
                $data['name'] ?? '',
                $data['base_url'] ?? '',
                $data['token'] ?? null,
                $data['session_id'] ?? null,
                $data['phone'] ?? null,
                $data['userkey'] ?? null,
                $data['passkey'] ?? null,
                $data['is_active'] ?? 1
            ]);

            return $result ? $this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            throw new \Exception("Failed to create server: " . $e->getMessage());
        }
    }

    /**
     * Update Server
     * 
     * @param int $id Server ID
     * @param array $data Updated data
     * @return bool True on success
     */
    public function update($id, array $data)
    {
        $sql = "UPDATE wa_servers SET 
            backend_id = ?,
            name = ?,
            base_url = ?,
            token = ?,
            session_id = ?,
            phone = ?,
            userkey = ?,
            passkey = ?,
            is_active = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['backend_id'] ?? 3,
                $data['name'] ?? '',
                $data['base_url'] ?? '',
                $data['token'] ?? null,
                $data['session_id'] ?? null,
                $data['phone'] ?? null,
                $data['userkey'] ?? null,
                $data['passkey'] ?? null,
                $data['is_active'] ?? 1,
                $id
            ]);
        } catch (PDOException $e) {
            throw new \Exception("Failed to update server: " . $e->getMessage());
        }
    }

    /**
     * Delete Server
     * 
     * @param int $id Server ID
     * @return bool True on success
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM wa_servers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Toggle Active Status
     * 
     * @param int $id Server ID
     * @return bool True on success
     */
    public function toggleActive($id)
    {
        $sql = "UPDATE wa_servers SET 
            is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Get PDO Connection
     * 
     * @return PDO Database connection
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Close Connection
     * 
     * @return void
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * Log Message
     * 
     * @param array $logData Log data containing number, message, payload, id_unik
     * @return int|false Last insert ID or false on failure
     */
    public function logMessage(array $logData)
    {
        $sql = "INSERT INTO wa_message_logs (
            number, message, payload, id_unik, status
        ) VALUES (?, ?, ?, ?, ?)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $logData['number'] ?? '',
                $logData['message'] ?? '',
                is_array($logData['payload']) ? json_encode($logData['payload']) : $logData['payload'],
                $logData['id_unik'] ?? '',
                $logData['status'] ?? 'sent'
            ]);

            return $result ? $this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Failed to log message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Message Logs
     * 
     * @param int $limit Number of logs to retrieve
     * @param int $offset Offset for pagination
     * @return array List of message logs
     */
    public function getMessageLogs($limit = 100, $offset = 0)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM wa_message_logs ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get Message Logs by Number
     * 
     * @param string $number Phone number
     * @param int $limit Number of logs to retrieve
     * @return array List of message logs
     */
    public function getMessageLogsByNumber($number, $limit = 50)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM wa_message_logs WHERE number = ? ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$number, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get Message Log by Unique ID
     * 
     * @param string $idUnik Unique identifier
     * @return array|false Log data or false if not found
     */
    public function getMessageLogByUniqueId($idUnik)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM wa_message_logs WHERE id_unik = ? ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$idUnik]);
        return $stmt->fetch();
    }
}
