<?php
// Load environment-specific overrides if present (e.g. injected at deploy time)
if (file_exists(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
}

// Fallback to environment variables or local development defaults
defined('DB_HOST') || define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
defined('DB_NAME') || define('DB_NAME', getenv('DB_NAME') ?: 'adisoma');
defined('DB_USER') || define('DB_USER', getenv('DB_USER') ?: 'root');
defined('DB_PASS') || define('DB_PASS', getenv('DB_PASS') ?: '');
defined('BASE_URL') || define('BASE_URL', getenv('BASE_URL') ?: '/adisoma');

const CO = [
    'name' => 'ADISOMA Solution',
    'address' => 'Mumbai, Maharashtra',
    'phone' => '+91 98765 43210',
    'email' => 'info@adisomasolution.com',
    'facebook' => '#',
    'linkedin' => '#',
    'twitter' => '#',
    'instagram' => '#'
];

session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

function db(): PDO {
    static $p;
    if (!$p) {
        $p = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }
    return $p;
}
