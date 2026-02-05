# WhatsApp Gateway Library

A comprehensive PHP library for sending WhatsApp messages through multiple backend services.

## Installation

```bash
composer require mdestafadilah/restapiwa
```

### Pre-Required (Before Use This simple Library)

1. https://github.com/andresayac/baileys-api
2. https://github.com/wppconnect-team/wppconnect-server
3. https://github.com/mimamch/wa-gateway
4. https://github.com/avoylenko/wwebjs-api
5. https://github.com/asternic/wuzapi
6. https://github.com/LuizFelipeNeves/go-whatsapp-web-multidevice
7. https://github.com/EvolutionAPI/evolution-api
8. https://github.com/farinchan/chatery_whatsapp

Please, make sure you already running 8 backend services above.

## Features

- ✅ Multiple backend support (Backend 3, 4, 8, and 99)
- ✅ Automatic backend selection with health check
- ✅ Phone number normalization
- ✅ Group message support
- ✅ Custom logging
- ✅ Error handling with Guzzle exceptions
- ✅ Configurable message footer

## Usage

### Basic Usage

```php
<?php

require 'vendor/autoload.php';

use Mdestafadilah\ApiWaRest\WhatsAppGateway;

// Configuration
$config = [
    'footer' => "\n\n--\nPowered by WhatsApp Gateway",
    'servers' => [
        3 => [
            'base_url' => 'https://v3.apiwa.persahabatan.co.id',
            'session_id' => 'your-session-id',
            'token' => 'your-token'
        ],
        4 => [
            'base_url' => 'https://v4.apiwa.persahabatan.co.id/',
            'token' => 'your-token'
        ],
        8 => [
            'base_url' => 'https://v8.apiwa.persahabatan.co.id',
            'session_id' => 'your-session-id',
            'token' => 'your-api-key'
        ],
        99 => [
            'base_url' => 'https://otp-service.com',
            'userkey' => 'your-userkey',
            'passkey' => 'your-passkey'
        ]
    ]
];

// Initialize
$wa = new WhatsAppGateway($config);

// Send message
$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Halo, ini pesan dari WhatsApp Gateway!'
], 3); // Using backend 3

// Check response
if ($response['status'] == 200) {
    echo "Message sent successfully!\n";
    echo "Response: " . $response['message'] . "\n";
} else {
    echo "Failed to send message\n";
    echo "Error: " . $response['message'] . "\n";
}
```

### Automatic Backend Selection

```php
// Enable automatic backend selection with health check
$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Halo dari auto backend!'
], 3, false, true); // otomatis = true
```

### Send to Group

```php
// Send message to WhatsApp group
$response = $wa->sendMessage([
    'nomorhp' => '120363xxxxxxxxxx@g.us',
    'pesanwa' => 'Halo grup!'
], 3, true); // isGroup = true
```

### With Custom Logger

```php
// Set custom logger
$wa->setLogger(function($logData) {
    file_put_contents('wa-log.txt', json_encode($logData) . "\n", FILE_APPEND);
    error_log("WA Log: " . json_encode($logData));
});

// Send message (will be logged)
$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Test dengan logger',
], 3, false, false, 'UNIQUE-ID-123');
```

### With Custom Unique ID

```php
// Send message with custom unique identifier
$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Test dengan ID unik'
], 3, false, false, 'ORDER-2024-001');
```

## Backend Services

### Backend 3
- Free service
- Requires: `base_url`, `session_id`
- Supports: Individual & Group messages
- Delay: 5000ms

### Backend 4
- Free service
- Requires: `base_url`, `token`
- Supports: Individual & Group messages

### Backend 8
- Free service
- Requires: `base_url`, `session_id`, `token`
- Supports: Individual & Group messages
- Typing time: 5000ms

### Backend 99
- Premium OTP service (Paid)
- Requires: `base_url`, `userkey`, `passkey`
- Supports: Individual messages only
- No footer appended

## API Reference

### sendMessage()

```php
public function sendMessage(
    array $data = [],      // ['nomorhp' => '...', 'pesanwa' => '...']
    int $be = 3,           // Backend ID: 3, 4, 8, or 99
    bool $isGroup = false, // Is recipient a group?
    bool $otomatis = false,// Enable automatic backend selection
    string $idUnik = ''    // Custom unique identifier for logging
): array
```

**Parameters:**
- `$data` - Array containing:
  - `nomorhp` (required): Recipient phone number
  - `pesanwa` (required): Message text
- `$be` - Backend service ID (3, 4, 8, or 99)
- `$isGroup` - Set to `true` if sending to a group
- `$otomatis` - Set to `true` to enable automatic backend selection
- `$idUnik` - Custom unique identifier for logging

**Returns:**
```php
[
    'status' => 200,                    // HTTP status code
    'message' => '{"success": true}'   // Response body
]
```

### setLogger()

```php
public function setLogger(callable $logger): void
```

Set a custom logger callback function.

### setConfig()

```php
public function setConfig(array $config): void
```

Update configuration after initialization.

### getConfig()

```php
public function getConfig(): array
```

Get current configuration.

## Phone Number Format

The library automatically normalizes phone numbers:
- `081234567890` → `62812345678900`
- `+62812345678` → `62812345678`
- `0812-3456-7890` → `628123456789`

## Error Handling

```php
try {
    $response = $wa->sendMessage([
        'nomorhp' => '081234567890',
        'pesanwa' => 'Test message'
    ], 3);
    
    if ($response['status'] >= 400) {
        // Handle HTTP errors
        echo "Error: " . $response['message'];
    }
} catch (\Exception $e) {
    // Handle exceptions
    echo "Exception: " . $e->getMessage();
}
```

## Requirements

- PHP 7.4 or higher
- Guzzle HTTP Client ^7.9

## License

MIT License

## Author

**mdestafadilah**  
Email: desta.08b@gmail.com

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
