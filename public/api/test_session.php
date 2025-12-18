<?php
require_once __DIR__ . '/_bootstrap.php';

// Test session functionality
header('Content-Type: application/json');

echo json_encode([
    'session_status' => session_status(),
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'uid' => $_SESSION['uid'] ?? 'NOT SET',
    'role' => $_SESSION['role'] ?? 'NOT SET',
    'session_name' => session_name(),
    'session_save_path' => session_save_path(),
    'session_cookie_params' => session_get_cookie_params()
]);
?>
