<?php
/**
 * WhatsApp Gateway - Database Example
 * 
 * Example demonstrating how to use the WhatsApp Gateway library
 * with SQLite database for configuration management and logging
 */

require 'vendor/autoload.php';

use Mdestafadilah\ApiWaRest\WhatsAppGateway;
use Mdestafadilah\ApiWaRest\DatabaseManager;

// Initialize Database
$db = new DatabaseManager(__DIR__ . '/data/wa_gateway.db');
$db->init();

echo "=== WhatsApp Gateway - Database Example ===\n\n";

// Check if there are any servers configured
$servers = $db->getAll();

if (empty($servers)) {
    echo "❌ No servers configured yet!\n";
    echo "Please add servers through the admin interface: admin/index.php\n\n";
    
    echo "Example: Adding a server programmatically:\n\n";
    
    // Example: Add a server
    $serverId = $db->create([
        'backend_id' => 3,
        'name' => 'Example Server 3',
        'base_url' => 'https://v3.apiwa.persahabatan.co.id',
        'session_id' => 'your-session-id',
        'token' => '',
        'phone' => '628123456789',
        'is_active' => 1
    ]);
    
    if ($serverId) {
        echo "✅ Server added successfully with ID: $serverId\n\n";
    }
} else {
    echo "✅ Found " . count($servers) . " configured server(s)\n\n";
    
    // Display configured servers
    echo "Configured Servers:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($servers as $server) {
        $status = $server['is_active'] ? '🟢 Active' : '🔴 Inactive';
        echo sprintf(
            "ID: %d | Backend: %d | Name: %s | Status: %s\n",
            $server['id'],
            $server['backend_id'],
            $server['name'],
            $status
        );
    }
    echo str_repeat("-", 80) . "\n\n";
}

// Initialize WhatsApp Gateway with Database
echo "Initializing WhatsApp Gateway with database...\n";
$wa = new WhatsAppGateway([], null, $db);

// Display loaded configuration
$config = $wa->getConfig();
echo "Loaded configuration from database:\n";
echo "Available backends: " . implode(', ', array_keys($config['servers'])) . "\n\n";

// Example: Send a test message (commented out for safety)
/*
echo "Sending test message...\n";
$response = $wa->sendMessage([
    'nomorhp' => '081234567890',
    'pesanwa' => 'Halo! Ini adalah pesan test dari WhatsApp Gateway dengan database.'
], 3, false, false, 'TEST-' . date('YmdHis'));

if ($response['status'] == 200) {
    echo "✅ Message sent successfully!\n";
    echo "Response: " . $response['message'] . "\n";
} else {
    echo "❌ Failed to send message\n";
    echo "Status: " . $response['status'] . "\n";
    echo "Error: " . $response['message'] . "\n";
}
*/

// Example: Retrieve message logs
echo "\nRetrieving recent message logs...\n";
$logs = $db->getMessageLogs(5);

if (empty($logs)) {
    echo "No message logs found.\n";
} else {
    echo "\nRecent Message Logs:\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-5s %-15s %-50s %-20s\n", "ID", "Number", "Message", "Timestamp");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($logs as $log) {
        $message = strlen($log['message']) > 47 ? substr($log['message'], 0, 47) . '...' : $log['message'];
        printf(
            "%-5d %-15s %-50s %-20s\n",
            $log['id'],
            $log['number'],
            $message,
            $log['created_at']
        );
    }
    echo str_repeat("-", 100) . "\n";
}

echo "\n=== Example Complete ===\n";
echo "For more examples, check the example.php file\n";
echo "To manage servers, open admin/index.php in your browser\n";
