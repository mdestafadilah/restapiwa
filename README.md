# Apa Ini

Ini Hanya REST API WhatsApp Client sederhana yang saya buat untuk keperluan pribadi, jika ada yang mau menggunakan silahkan, tapi jangan lupa untuk menghargai karya orang lain.

### Pre-Required (Before Use This simple Library)

Pastikan sudah menjalankan service whatsapp berikut:

1. https://github.com/andresayac/baileys-api
2. https://github.com/wppconnect-team/wppconnect-server
3. https://github.com/mimamch/wa-gateway
4. https://github.com/avoylenko/wwebjs-api
5. https://github.com/asternic/wuzapi
6. https://github.com/LuizFelipeNeves/go-whatsapp-web-multidevice
7. https://github.com/EvolutionAPI/evolution-api
8. https://github.com/farinchan/chatery_whatsapp

Please, make sure you already running 8 backend services above.

## Installation

```bash
composer require mdestafadilah/restapiwa
```

## Features

- ✅ Multiple backend support (Backend 3, 4, 7, 8, and 99)
- ✅ Automatic backend selection with health check
- ✅ Phone number normalization
- ✅ Group message support
- ✅ Custom logging
- ✅ Error handling with Guzzle exceptions
- ✅ Configurable message footer
- ✅ **SQLite database for server configuration management**
- ✅ **Automatic message logging to database**
- ✅ **Admin web interface for server management**

## Quick Start

### With Database (Recommended)

```php
<?php

require 'vendor/autoload.php';

use Mdestafadilah\ApiWaRest\WhatsAppGateway;
use Mdestafadilah\ApiWaRest\DatabaseManager;

// Initialize database
$db = new DatabaseManager(__DIR__ . '/data/wa_gateway.db');
$db->init();

// Initialize WhatsApp Gateway with database
$wa = new WhatsAppGateway([], null, $db);

// Send message - configuration loaded from database automatically
$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Hello from WhatsApp Gateway!'
], 3);
```

### With Array Configuration (Traditional)

```php
<?php

require 'vendor/autoload.php';

use Mdestafadilah\ApiWaRest\WhatsAppGateway;

$config = [
    'footer' => "\n\n--\nPowered by WhatsApp Gateway",
    'servers' => [
        3 => [
            'base_url' => 'https://v3.apiwa.persahabatan.co.id',
            'session_id' => 'your-session-id',
            'token' => ''
        ],
        4 => [
            'base_url' => 'https://v4.apiwa.persahabatan.co.id/',
            'token' => 'your-token'
        ]
    ]
];

$wa = new WhatsAppGateway($config);
$response = $wa->sendMessage(['nomorhp' => '081234567890', 'pesanwa' => 'Hello!'], 3);
```

## Database Management

### Admin Web Interface

Access the admin interface to manage server configurations:

1. Open `admin/index.php` in your browser
2. Add, edit, or delete server configurations
3. Toggle servers active/inactive
4. View all configured backends

### Programmatic Server Management

```php
use Mdestafadilah\ApiWaRest\DatabaseManager;

$db = new DatabaseManager(__DIR__ . '/data/wa_gateway.db');
$db->init();

// Add a server
$serverId = $db->create([
    'backend_id' => 3,
    'name' => 'Production Server',
    'base_url' => 'https://v3.apiwa.persahabatan.co.id',
    'session_id' => 'your-session-id',
    'token' => '',
    'phone' => '628123456789',
    'is_active' => 1
]);

// Get all servers
$servers = $db->getAll();

// Get active servers only
$activeServers = $db->getActiveServers();

// Update server
$db->update($serverId, ['name' => 'Updated Name', 'is_active' => 0]);

// Delete server
$db->delete($serverId);
```

### Message Logging

All messages are automatically logged to the database when using DatabaseManager:

```php
// Get recent logs
$logs = $db->getMessageLogs(50); // Get last 50 messages

// Get logs by phone number
$logs = $db->getMessageLogsByNumber('628123456789', 20);

// Get log by unique ID
$log = $db->getMessageLogByUniqueId('ORDER-2024-001');
```

## Usage Examples

### Send to Group

```php
$response = $wa->sendMessage([
    'nomorhp' => '120363xxxxxxxxxx@g.us',
    'pesanwa' => 'Hello group!'
], 3, true); // isGroup = true
```

### Automatic Backend Selection

```php
$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Auto backend!'
], 3, false, true); // otomatis = true
```

### With Custom Logger

```php
$wa->setLogger(function($logData) {
    file_put_contents('wa-log.txt', json_encode($logData) . "\n", FILE_APPEND);
});
```

### With Custom Unique ID

```php
$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Order confirmation'
], 3, false, false, 'ORDER-2024-001');
```

## Backend Services

### Backend 3
- Free service
- Requires: `base_url`, `session_id`
- Supports: Individual & Group messages

### Backend 4
- Free service
- Requires: `base_url`, `token`
- Supports: Individual & Group messages

### Backend 7
- Free service
- Requires: `base_url`, `token`
- Supports: Individual & Group messages

### Backend 8
- Free service
- Requires: `base_url`, `session_id`, `token`
- Supports: Individual & Group messages

### Backend 99
- Premium OTP service (Paid)
- Requires: `base_url`, `userkey`, `passkey`
- Supports: Individual messages only

## Database Schema

### wa_servers table
- `id` - Auto increment primary key
- `backend_id` - Backend ID (3, 4, 8, 99)
- `name` - Server name/description
- `base_url` - API base URL
- `token` - API token
- `session_id` - Session ID (for backends 3 & 8)
- `phone` - Phone number
- `userkey` - User key (for backend 99)
- `passkey` - Pass key (for backend 99)
- `is_active` - Active status (1 or 0)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

### wa_message_logs table
- `id` - Auto increment primary key
- `number` - Recipient phone number
- `message` - Message content
- `payload` - JSON payload sent to backend
- `id_unik` - Unique identifier
- `status` - Message status
- `created_at` - Timestamp

## Requirements

- PHP 7.4 or higher
- PDO SQLite extension
- Guzzle HTTP Client ^7.9

## License

MIT License

## Author

**mdestafadilah**  
Email: desta.08b@gmail.com

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
