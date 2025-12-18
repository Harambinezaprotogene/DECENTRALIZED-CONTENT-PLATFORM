<?php
require_once '_bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['uid'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            // Get user profile data
            $stmt = $pdo->prepare('SELECT display_name, email, usdt_address, created_at FROM users WHERE id = ?');
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'display_name' => $user['display_name'],
                    'email' => $user['email'],
                    'usdt_address' => $user['usdt_address'] ?? '',
                    'bio' => '', // Bio column doesn't exist yet
                    'created_at' => $user['created_at']
                ]
            ]);
            break;
            
        case 'update':
            // Update user profile
            $display_name = trim($_POST['display_name'] ?? '');
            $usdt_address = trim($_POST['usdt_address'] ?? '');
            
            if (empty($display_name)) {
                throw new Exception('Display name is required');
            }
            
            // Check if display name is already taken by another user
            $stmt = $pdo->prepare('SELECT id FROM users WHERE display_name = ? AND id != ?');
            $stmt->execute([$display_name, $user_id]);
            if ($stmt->fetch()) {
                throw new Exception('Display name is already taken');
            }
            
            $stmt = $pdo->prepare('UPDATE users SET display_name = ?, usdt_address = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$display_name, $usdt_address, $user_id]);
            
            // Update session display name
            $_SESSION['display_name'] = $display_name;
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => ['display_name' => $display_name]
            ]);
            break;
            
        case 'change_password':
            // Change user password
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            
            if (empty($current_password) || empty($new_password)) {
                throw new Exception('Current and new passwords are required');
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception('New password must be at least 6 characters');
            }
            
            // Verify current password
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($current_password, $user['password_hash'])) {
                throw new Exception('Current password is incorrect');
            }
            
            // Update password
            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$new_password_hash, $user_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
            break;
            
        case 'delete_account':
            // Delete user account (soft delete by updating status)
            $stmt = $pdo->prepare('UPDATE users SET status = "deleted", updated_at = NOW() WHERE id = ?');
            $stmt->execute([$user_id]);
            
            // Also mark all user content as deleted
            $stmt = $pdo->prepare('UPDATE content SET status = "deleted", updated_at = NOW() WHERE user_id = ?');
            $stmt->execute([$user_id]);
            
            // Destroy session
            session_destroy();
            
            echo json_encode([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
            break;
            
        case 'export_data':
            // Export user data
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare('SELECT * FROM content WHERE user_id = ?');
            $stmt->execute([$user_id]);
            $content_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $export_data = [
                'user' => $user_data,
                'content' => $content_data,
                'exported_at' => date('Y-m-d H:i:s'),
                'exported_by' => 'Kabaka Platform'
            ];
            
            // Set headers for file download
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="kabaka-account-data-' . date('Y-m-d') . '.json"');
            
            echo json_encode($export_data, JSON_PRETTY_PRINT);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}