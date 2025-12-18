<?php
// Ensure session is started even when endpoints are hit directly (not via api_router.php)
if (session_status() === PHP_SESSION_NONE) {
    // Configure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    ini_set('session.cookie_samesite', 'Lax');
    
    session_start();
}
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/_mailer.php';

// Load .env if it exists, otherwise use defaults
EnvLoader::load(__DIR__ . '/../../.env');

// Set default database values if not in .env
if (!getenv('DB_HOST')) {
    putenv('DB_HOST=localhost');
    $_ENV['DB_HOST'] = 'localhost';
}
if (!getenv('DB_PORT')) {
    putenv('DB_PORT=3306');
    $_ENV['DB_PORT'] = '3306';
}
if (!getenv('DB_NAME')) {
    putenv('DB_NAME=kabaka');
    $_ENV['DB_NAME'] = 'kabaka';
}
if (!getenv('DB_USER')) {
    putenv('DB_USER=root');
    $_ENV['DB_USER'] = 'root';
}
if (!getenv('DB_PASS')) {
    putenv('DB_PASS=');
    $_ENV['DB_PASS'] = '';
}

header('Content-Type: application/json');

function json_response($data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}


