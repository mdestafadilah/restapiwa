<?php

/**
 * WhatsApp Gateway - Usage Examples
 * 
 * This file demonstrates various ways to use the WhatsApp Gateway library
 */

require 'vendor/autoload.php';

use Mdestafadilah\ApiWaRest\WhatsAppGateway;

// ============================================
// 1. CONFIGURATION
// ============================================

$config = [
    'footer' => "\n\n--\nPesan otomatis dari sistem",
    'servers' => [
        1 => [
            'base_url' => 'https://v1.apiwa.persahabatan.co.id',
            'session_id' => 'notifprima2v3ABCDE1234',
            'token' => ''
        ],
        2 => [
            'base_url' => 'https://v2.apiwa.persahabatan.co.id',
            'session_id' => 'notifprima2v3ABCDE1234',
            'token' => ''
        ],
        3 => [
            'base_url' => 'https://v3.apiwa.persahabatan.co.id',
            'session_id' => 'notifprima2v3ABCDE1234',
            'token' => ''
        ],
        4 => [
            'base_url' => 'https://v4.apiwa.persahabatan.co.id/',
            'token' => 'your-token-here'
        ],
        5 => [
            'base_url' => 'https://v5.apiwa.persahabatan.co.id/',
            'token' => 'your-token-here'
        ],
        6 => [
            'base_url' => 'https://v6.apiwa.persahabatan.co.id/',
            'token' => 'your-token-here'
        ],
        7 => [
            'base_url' => 'https://v7.apiwa.persahabatan.co.id/',
            'token' => 'your-token-here'
        ],
        8 => [
            'base_url' => 'https://v8.apiwa.persahabatan.co.id',
            'session_id' => 'session-name',
            'token' => 'your-api-key-here'
        ],
        99 => [
            'base_url' => 'https://otp-service.com',
            'userkey' => 'your-userkey',
            'passkey' => 'your-passkey'
        ]
    ]
];

// ============================================
// 2. BASIC USAGE
// ============================================

echo "=== EXAMPLE 1: Basic Message ===\n";
$wa = new WhatsAppGateway($config);

$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Hallo, ini adalah pesan test dari WhatsApp Gateway!'
], 3);

echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['message'] . "\n\n";

// ============================================
// 3. SEND TO GROUP
// ============================================

echo "=== EXAMPLE 2: Send to Group ===\n";

$response = $wa->sendMessage([
    'nomorhp' => '120363xxxxxxxxxx@g.us', // Group ID
    'pesanwa' => 'Hallo grup! Ini pesan dari bot.'
], 3, true); // isGroup = true

echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['message'] . "\n\n";

// ============================================
// 4. AUTOMATIC BACKEND SELECTION
// ============================================

echo "=== EXAMPLE 3: Automatic Backend Selection ===\n";

$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Pesan ini dikirim melalui backend otomatis!'
], 3, false, true); // otomatis = true

echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['message'] . "\n\n";

// ============================================
// 5. WITH CUSTOM LOGGER
// ============================================

echo "=== EXAMPLE 4: With Custom Logger ===\n";

// Set logger to save to file
$wa->setLogger(function($logData) {
    $logFile = __DIR__ . '/wa-messages.log';
    $logEntry = sprintf(
        "[%s] TO: %s | MSG: %s | ID: %s\n",
        $logData['timestamp'],
        $logData['number'],
        substr($logData['message'], 0, 50) . '...',
        $logData['id_unik']
    );
    file_put_contents($logFile, $logEntry, FILE_APPEND);
});

$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Pesan dengan logging'
], 3, false, false, 'LOG-001');

echo "Status: " . $response['status'] . "\n";
echo "Message logged to wa-messages.log\n\n";

// ============================================
// 6. USING BACKEND 99 (OTP SERVICE)
// ============================================

echo "=== EXAMPLE 5: OTP Message (Backend 99) ===\n";

$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Kode OTP Anda: 123456. Jangan berikan kode ini kepada siapapun.'
], 99, false, false, 'OTP-' . time());

echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['message'] . "\n\n";

// ============================================
// 7. ERROR HANDLING
// ============================================

echo "=== EXAMPLE 6: Error Handling ===\n";

try {
    $response = $wa->sendMessage([
        'nomorhp' => '081234567890',
        'pesanwa' => 'Testing error handling'
    ], 3);
    
    if ($response['status'] == 200) {
        echo "✓ Message sent successfully!\n";
    } else {
        echo "✗ Failed to send message\n";
        echo "Status Code: " . $response['status'] . "\n";
        echo "Error: " . $response['message'] . "\n";
    }
} catch (\Exception $e) {
    echo "✗ Exception occurred: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================
// 8. BATCH SENDING
// ============================================

echo "=== EXAMPLE 7: Batch Sending ===\n";

$recipients = [
    ['nomorhp' => '081234567890', 'nama' => 'Ahmad'],
    ['nomorhp' => '082345678901', 'nama' => 'Budi'],
    ['nomorhp' => '083456789012', 'nama' => 'Citra']
];

foreach ($recipients as $recipient) {
    $message = "Hallo {$recipient['nama']}, ini adalah pesan broadcast!";
    
    $response = $wa->sendMessage([
        'nomorhp' => $recipient['nomorhp'],
        'pesanwa' => $message
    ], 3, false, false, 'BATCH-' . time());
    
    echo "Sent to {$recipient['nama']} ({$recipient['nomorhp']}): ";
    echo $response['status'] == 200 ? "✓\n" : "✗\n";
    
    // Delay to avoid rate limiting
    sleep(2);
}

echo "\n";

// ============================================
// 9. DYNAMIC CONFIGURATION
// ============================================

echo "=== EXAMPLE 8: Dynamic Configuration ===\n";

$wa2 = new WhatsAppGateway();

// Set configuration after initialization
$wa2->setConfig([
    'footer' => "\n\nTerima kasih!",
    'servers' => [
        3 => [
            'base_url' => 'https://v3.apiwa.persahabatan.co.id',
            'session_id' => 'session-123',
            'token' => ''
        ]
    ]
]);

$response = $wa2->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Pesan dengan konfigurasi dinamis'
], 3);

echo "Status: " . $response['status'] . "\n\n";

// ============================================
// 10. NOTIFICATION TEMPLATE
// ============================================

echo "=== EXAMPLE 9: Notification Template ===\n";

function sendNotification($wa, $phoneNumber, $type, $data) {
    $templates = [
        'appointment' => "🗓️ *Pengingat Janji Temu*\n\nHallo {name},\n\nAnda memiliki janji temu pada:\n📅 Tanggal: {date}\n🕐 Waktu: {time}\n📍 Lokasi: {location}\n\nJangan lupa untuk datang tepat waktu!",
        
        'payment' => "💳 *Konfirmasi Pembayaran*\n\nHallo {name},\n\nPembayaran Anda telah diterima:\n💰 Jumlah: Rp {amount}\n📝 No. Transaksi: {transaction_id}\n📅 Tanggal: {date}\n\nTerima kasih!",
        
        'order' => "🛍️ *Status Pesanan*\n\nHallo {name},\n\nPesanan Anda:\n📦 No. Order: {order_id}\n📊 Status: {status}\n🚚 Estimasi: {estimate}\n\nTerima kasih telah berbelanja!"
    ];
    
    $message = $templates[$type] ?? "Notifikasi untuk {name}";
    
    // Replace placeholders
    foreach ($data as $key => $value) {
        $message = str_replace('{' . $key . '}', $value, $message);
    }
    
    return $wa->sendMessage([
        'nomorhp' => $phoneNumber,
        'pesanwa' => $message
    ], 3);
}

// Example: Send appointment reminder
$result = sendNotification($wa, '081234567890', 'appointment', [
    'name' => 'Pak Ahmad',
    'date' => '15 Februari 2024',
    'time' => '10:00 WIB',
    'location' => 'RS Persahabatan, Lt. 2'
]);

echo "Notification sent: " . ($result['status'] == 200 ? "✓" : "✗") . "\n";

echo "\n=== All Examples Completed ===\n";
