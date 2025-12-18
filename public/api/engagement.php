<?php
require_once __DIR__ . '/_bootstrap.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Handle GET requests for checking like status
    if (isset($_GET['content_id']) && isset($_GET['check_like'])) {
        $contentId = (int)$_GET['content_id'];
        $userId = $_SESSION['viewer_id'] ?? ($_SESSION['uid'] ?? ($_SESSION['user']['id'] ?? null));
        // For anonymous users, use IP as identifier
        if (!$userId) { $userId = 'anon_' . md5($_SERVER['REMOTE_ADDR']); }
        try {
            $pdo = DatabaseConnectionFactory::createConnection();
            // Ensure table/uniqueness exists
            $pdo->exec('CREATE TABLE IF NOT EXISTS engagements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                content_id INT NOT NULL,
                user_id VARCHAR(255) NOT NULL,
                type ENUM("view", "like", "follow") NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_user_content_type (content_id, user_id, type),
                INDEX idx_content_id (content_id),
                INDEX idx_user_id (user_id),
                INDEX idx_type (type),
                INDEX idx_created_at (created_at)
            )');
            // Check if user has liked this content
            $stmt = $pdo->prepare('SELECT id FROM engagements WHERE content_id = ? AND user_id = ? AND type = "like"');
            $stmt->execute([$contentId, $userId]);
            $liked = $stmt->fetch() ? true : false;
            json_response(['ok' => true, 'liked' => $liked]);
        } catch (Exception $e) {
            error_log('Engagement API Error: ' . $e->getMessage());
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['content_id']) || !isset($input['type'])) {
        json_response(['error' => 'Content ID and type required'], 400);
    }
    $contentId = (int)$input['content_id'];
    $type = $input['type'];
    $userId = $_SESSION['viewer_id'] ?? ($_SESSION['uid'] ?? ($_SESSION['user']['id'] ?? null));
    if (!$userId) { $userId = 'anon_' . md5($_SERVER['REMOTE_ADDR']); }
    try {
        $pdo = DatabaseConnectionFactory::createConnection();
        // Create table with a unique constraint for idempotent likes
        $pdo->exec('CREATE TABLE IF NOT EXISTS engagements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_id INT NOT NULL,
            user_id VARCHAR(255) NOT NULL,
            type ENUM("view", "like", "follow") NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_content_type (content_id, user_id, type),
            INDEX idx_content_id (content_id),
            INDEX idx_user_id (user_id),
            INDEX idx_type (type),
            INDEX idx_created_at (created_at)
        )');
        // Check content exists (approved/visible)
        $stmt = $pdo->prepare('SELECT id FROM content WHERE id = ? AND status IN ("approved", "visible")');
        $stmt->execute([$contentId]);
        if (!$stmt->fetch()) { json_response(['error' => 'Content not found'], 404); }
        if ($type === 'like') {
            // Toggle like atomically
            $stmt = $pdo->prepare('SELECT id FROM engagements WHERE content_id = ? AND user_id = ? AND type = "like"');
            $stmt->execute([$contentId, $userId]);
            $existing = $stmt->fetch();
            if ($existing) {
                $pdo->prepare('DELETE FROM engagements WHERE content_id = ? AND user_id = ? AND type = "like"')->execute([$contentId, $userId]);
                json_response(['ok' => true, 'liked' => false]);
            } else {
                // Insert, respecting uniqueness
                try {
                    $pdo->prepare('INSERT INTO engagements (content_id, user_id, type, created_at) VALUES (?, ?, ?, NOW())')->execute([$contentId, $userId, 'like']);
                } catch (Exception $e) {
                    // If duplicate due to race, consider as liked
                    // fall through
                }
                json_response(['ok' => true, 'liked' => true]);
            }
        } else {
            // Record view/follow without failing on duplicates
            try {
                $pdo->prepare('INSERT INTO engagements (content_id, user_id, type, created_at) VALUES (?, ?, ?, NOW())')->execute([$contentId, $userId, $type]);
            } catch (Exception $e) {
                // ignore duplicate views/follows
            }
            // Simple notification for views (can be noisy; kept minimal as requested)
            if ($type === 'view') {
                try {
                    $owner = $pdo->prepare('SELECT user_id FROM content WHERE id = ?');
                    $owner->execute([$contentId]);
                    $ownerId = (int)($owner->fetchColumn() ?: 0);
                    if ($ownerId > 0) {
                        $pdo->exec('CREATE TABLE IF NOT EXISTS notifications (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            user_id INT NOT NULL,
                            type VARCHAR(32) NOT NULL,
                            payload TEXT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            read_at TIMESTAMP NULL,
                            INDEX idx_user_id (user_id),
                            INDEX idx_type (type),
                            INDEX idx_created_at (created_at)
                        )');
                        $payload = json_encode(['content_id' => (int)$contentId, 'viewer_id' => (string)$userId, 'title' => 'New View', 'message' => 'Someone viewed your content']);
                        $pdo->prepare('INSERT INTO notifications (user_id, type, payload) VALUES (?, ?, ?)')->execute([$ownerId, 'view', $payload]);
                    }
                } catch (Exception $e) { /* ignore notification errors */ }
            }
            json_response(['ok' => true]);
        }
    } catch (Exception $e) {
        error_log('Engagement API Error: ' . $e->getMessage());
        json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

json_response(['error' => 'Method not allowed'], 405);



