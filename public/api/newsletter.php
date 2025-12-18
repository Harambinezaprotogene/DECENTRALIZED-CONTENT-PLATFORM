<?php
require_once __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Accept application/json or form-urlencoded
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$inputEmail = '';
if (stripos($contentType, 'application/json') !== false) {
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
    $inputEmail = trim((string)($payload['email'] ?? ''));
} else {
    $inputEmail = trim((string)($_POST['email'] ?? ''));
}

if ($inputEmail === '' || !filter_var($inputEmail, FILTER_VALIDATE_EMAIL)) {
    json_response(['error' => 'Valid email required'], 400);
}

try {
    // Ensure table exists (id PK auto-increment, email FK optional depending on schema)
    $pdo->exec('CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $stmt = $pdo->prepare('INSERT INTO newsletter_subscribers (email, created_at) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE created_at = VALUES(created_at)');
    $stmt->execute([$inputEmail]);

    json_response(['ok' => true, 'message' => 'Subscribed']);
} catch (Exception $e) {
    json_response(['error' => 'Database error'], 500);
}

?>


