<?php
require_once '_bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['uid'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = DatabaseConnectionFactory::createConnection();
    
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'count') {
            // Get unread notification count
            $stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND read_at IS NULL");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'unread_count' => (int)$result['unread_count']
            ]);
        } else {
            // Get notifications list
            $stmt = $pdo->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 20
            ");
            $stmt->execute([$user_id]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get unread count
            $count_stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND read_at IS NULL");
            $count_stmt->execute([$user_id]);
            $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => (int)$count_result['unread_count']
            ]);
        }
        
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        if ($action === 'mark_read') {
            $notification_id = (int)$input['notification_id'];
            
            // Verify notification belongs to user
            $verify_stmt = $pdo->prepare("SELECT id FROM notifications WHERE id = ? AND user_id = ?");
            $verify_stmt->execute([$notification_id, $user_id]);
            
            if ($verify_stmt->fetch()) {
                $update_stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?");
                $update_stmt->execute([$notification_id, $user_id]);
                
                echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Notification not found']);
            }
            
        } elseif ($action === 'mark_all_read') {
            $update_stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL");
            $update_stmt->execute([$user_id]);
            
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
            
        } elseif ($action === 'delete_all') {
            $delete_stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
            $delete_stmt->execute([$user_id]);
            
            echo json_encode(['success' => true, 'message' => 'All notifications deleted']);
            
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}