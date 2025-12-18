<?php
require_once __DIR__ . '/_bootstrap.php';

// Allow either viewer or creator sessions
$method = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput ?: '[]', true) ?: [];
$action = $_GET['action'] ?? ($jsonInput['action'] ?? '');

// Distinguish follower (viewer or user) and creator sessions
$viewerId = isset($_SESSION['viewer_id']) ? (int)$_SESSION['viewer_id'] : 0;
$userUid  = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0;
$followerId = $viewerId > 0 ? $viewerId : $userUid; // allow following from either session type

$pdo = DatabaseConnectionFactory::createConnection();

// Ensure table exists (prefer followed_id; keep creator_id for compatibility)
$pdo->exec('CREATE TABLE IF NOT EXISTS followers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  follower_id INT NOT NULL,
  followed_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_pair_followed (follower_id, followed_id),
  KEY idx_followed (followed_id)
)');
try { $pdo->exec('ALTER TABLE followers ADD COLUMN creator_id INT NULL'); } catch (Exception $e) { /* ignore */ }

// Detect which creator column is present
$useCreatorCol = 'followed_id';
try { $chk = $pdo->query("SHOW COLUMNS FROM followers LIKE 'followed_id'"); if (!$chk->fetch()) { $useCreatorCol = 'creator_id'; } } catch (Exception $e) { $useCreatorCol = 'creator_id'; }

if (isset($_GET['count'])) {
    $creatorId = (int)($_GET['creator_id'] ?? $userUid);
    if ($creatorId <= 0) { json_response(['error' => 'Creator required'], 401); }
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM followers WHERE {$useCreatorCol} = ?");
    $stmt->execute([$creatorId]);
    $row = $stmt->fetch();
    json_response(['count' => (int)($row['c'] ?? 0)]);
}

if (($action === 'toggle' || ($action === '' && $method === 'POST')) && $method === 'POST') {
    // Accept both creator_id and user_id from client
    $creatorId = (int)($jsonInput['creator_id'] ?? ($jsonInput['user_id'] ?? 0));
    if ($creatorId <= 0) { json_response(['error'=>'Invalid creator'], 422); }
    if ($followerId <= 0) { json_response(['error' => 'Auth required'], 401); }
    if ($creatorId === $followerId) { json_response(['error'=>'Cannot follow self'], 422); }

    // Toggle
    $stmt = $pdo->prepare("SELECT id FROM followers WHERE follower_id = ? AND {$useCreatorCol} = ?");
    $stmt->execute([$followerId, $creatorId]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare('DELETE FROM followers WHERE id = ?')->execute([$row['id']]);
        json_response(['ok'=>true, 'following'=>false]);
    } else {
        if ($useCreatorCol === 'followed_id') {
            $ins = $pdo->prepare('INSERT INTO followers (follower_id, followed_id, creator_id) VALUES (?, ?, ?)');
            $ins->execute([$followerId, $creatorId, $creatorId]);
        } else {
            $ins = $pdo->prepare('INSERT INTO followers (follower_id, creator_id) VALUES (?, ?)');
            $ins->execute([$followerId, $creatorId]);
        }
        // Insert simple follow notification
        try {
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
            $payload = json_encode(['creator_id' => (int)$creatorId, 'follower_id' => (int)$followerId, 'title' => 'New Follower', 'message' => 'Someone started following you!']);
            $pdo->prepare('INSERT INTO notifications (user_id, type, payload) VALUES (?, ?, ?)')->execute([$creatorId, 'follow', $payload]);
        } catch (Exception $e) { /* ignore */ }
        json_response(['ok'=>true, 'following'=>true]);
    }
}

if (isset($_GET['is_following'])) {
    $creatorId = (int)($_GET['creator_id'] ?? 0);
    if ($creatorId <= 0) { json_response(['error'=>'Invalid creator'], 422); }
    if ($followerId <= 0) { json_response(['following' => false]); }
    $stmt = $pdo->prepare("SELECT 1 FROM followers WHERE follower_id = ? AND {$useCreatorCol} = ?");
    $stmt->execute([$followerId, $creatorId]);
    json_response(['following' => (bool)$stmt->fetch()]);
}

// Friendly default: GET with no params returns follower count for logged-in creator
if ($method === 'GET' && $action === '' && !isset($_GET['count']) && !isset($_GET['is_following'])) {
    if ($userUid > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM followers WHERE {$useCreatorCol} = ?");
        $stmt->execute([$userUid]);
        $row = $stmt->fetch();
        json_response(['count' => (int)($row['c'] ?? 0)]);
    }
}

json_response(['error'=>'Not Found'], 404);



