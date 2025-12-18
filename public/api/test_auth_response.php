<?php
require_once __DIR__ . '/_bootstrap.php';

// Test what the auth API returns for login
header('Content-Type: application/json');

// Simulate a login request
$test_email = 'test@example.com';
$test_password = 'password123';

$stmt = $pdo->prepare('SELECT id, password_hash, role, display_name FROM users WHERE email = ?');
$stmt->execute([$test_email]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode([
        'test_user_found' => true,
        'user_id' => $user['id'],
        'user_role' => $user['role'],
        'expected_response' => [
            'ok' => true,
            'user' => [
                'id' => (int)$user['id'],
                'email' => $test_email,
                'role' => $user['role'],
                'display_name' => $user['display_name']
            ]
        ]
    ]);
} else {
    echo json_encode([
        'test_user_found' => false,
        'message' => 'No test user found. Create a user first.',
        'expected_response' => [
            'error' => 'User not found'
        ]
    ]);
}
?>
