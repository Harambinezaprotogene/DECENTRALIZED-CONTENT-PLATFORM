<?php
// Simple test to check if auth API is working
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/_bootstrap.php';
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Auth API bootstrap loaded successfully',
        'session_status' => session_status(),
        'session_id' => session_id()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Auth API bootstrap failed: ' . $e->getMessage()
    ]);
}
?>
