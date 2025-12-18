<?php
// Test the auth API directly
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/_bootstrap.php';
    
    // Test database connection
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Auth API is working',
        'database_connected' => true,
        'user_count' => $result['count'],
        'session_status' => session_status(),
        'session_id' => session_id()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Auth API failed: ' . $e->getMessage(),
        'database_connected' => false
    ]);
}
?>
