<?php
require_once __DIR__ . '/_bootstrap.php';

// Simple test endpoint to check if login API is working
header('Content-Type: application/json');

try {
    // Test database connection
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();
    
    echo json_encode([
        'status' => 'ok',
        'database_connected' => true,
        'user_count' => $result['count'],
        'session_status' => session_status(),
        'session_id' => session_id()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'database_connected' => false,
        'error' => $e->getMessage()
    ]);
}
?>
