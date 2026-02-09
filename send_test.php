<?php
// Load library (pastikan path ke file utamanya benar)
require_once('vendor/autoload.php'); // Sesuaikan dengan struktur folder di repo tersebut

use Mdestafadilah\ApiWaRest\WhatsAppGateway;

// Inisialisasi API
// Catatan: Library ini biasanya membutuhkan API Key atau Session ID 
// tergantung konfigurasi server backend-nya.
// Konfigurasi Server (Sesuaikan dengan data Anda)
$config = [
    'servers' => [
        3 => [
            'base_url' => 'https://api.example.com', // Ganti dengan URL API Anda
            'session_id' => 'session_id_here', // Ganti dengan Session ID Anda
            'token' => 'api_token_here' // Ganti dengan Token Anda jika ada
        ]
    ]
];

$wa = new WhatsAppGateway($config);

$nomor_tujuan = '6283898973731'; // Gunakan format internasional tanpa '+'
$pesan = 'Halo! Ini adalah pesan tes dari Project PHP.';

// Mengirim pesan teks
$send = $wa->sendMessage([
    'nomorhp' => $nomor_tujuan,
    'pesanwa' => $pesan
], 3);

if (isset($send['status']) && $send['status'] == 200) {
    echo "Pesan berhasil dikirim ke $nomor_tujuan\n";
    echo "Response: " . print_r($send, true);
} else {
    echo "Gagal mengirim pesan.\n";
    echo "Response: " . print_r($send, true);
}