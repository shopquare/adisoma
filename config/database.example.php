<?php
const DB_HOST = 'localhost';
const DB_NAME = 'adisoma';
const DB_USER = 'root';
const DB_PASS = '';
const BASE_URL = '/adisoma'; // Subfolder path or '' if hosted on domain root

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
