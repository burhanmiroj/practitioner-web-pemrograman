<?php
declare(strict_types=1);

/**
 * Koneksi database (PDO).
 * Ubah nilai di bawah sesuai server Anda, atau isi lewat environment
 * variable DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS.
 */
$db = [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'name' => getenv('DB_NAME') ?: 'stokku',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: 'root',
];

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4",
        $db['user'],
        $db['pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Koneksi database gagal: ' . $e->getMessage()); // detail hanya di log server
    http_response_code(500);
    exit('Tidak bisa terhubung ke database. Periksa pengaturan di connection.php.');
}
