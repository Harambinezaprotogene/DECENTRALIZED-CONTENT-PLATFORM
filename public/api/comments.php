<?php
require_once __DIR__ . '/_bootstrap.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $contentId = isset($_GET['content_id']) ? (int)$_GET['content_id'] : null;
    
    if (!$contentId) {
        json_response(['error' => 'Content ID required'], 400);
    }
    
    try {
        $pdo = DatabaseConnectionFactory::createConnection();
        // Ensure comments table exists with flexible schema
        $pdo->exec('CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_id INT NOT NULL,
            user_id INT NULL,
            viewer_id INT NULL,
            parent_id INT NULL,
            `text` TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_content_id (content_id),
            INDEX idx_user_id (user_id),
            INDEX idx_viewer_id (viewer_id),
            INDEX idx_parent_id (parent_id),
            INDEX idx_created_at (created_at)
        )');
        $viewerId = isset($_SESSION['viewer_id']) ? (int)$_SESSION['viewer_id'] : null;

        // Detect author id column and available tables/columns
        $hasUserId = false; $hasViewerId = false; $hasUsersTable = false; $hasViewersTable = false; $hasCreatedAt = false; $hasParentId = false;
        try { $chk = $pdo->query("SHOW COLUMNS FROM comments LIKE 'user_id'"); $hasUserId = (bool)$chk->fetch(); } catch (Exception $e) {}
        try { $chk = $pdo->query("SHOW COLUMNS FROM comments LIKE 'viewer_id'"); $hasViewerId = (bool)$chk->fetch(); } catch (Exception $e) {}
        try { $chk = $pdo->query("SHOW COLUMNS FROM comments LIKE 'created_at'"); $hasCreatedAt = (bool)$chk->fetch(); } catch (Exception $e) {}
        try { $chk = $pdo->query("SHOW COLUMNS FROM comments LIKE 'parent_id'"); $hasParentId = (bool)$chk->fetch(); } catch (Exception $e) {}
        try { $chk = $pdo->query("SHOW TABLES LIKE 'users'"); $hasUsersTable = (bool)$chk->fetch(); } catch (Exception $e) {}
        try { $chk = $pdo->query("SHOW TABLES LIKE 'viewers'"); $hasViewersTable = (bool)$chk->fetch(); } catch (Exception $e) {}
        $authorIdCol = $hasUserId ? 'user_id' : ($hasViewerId ? 'viewer_id' : 'user_id');

        // Build query safely based on detected schema
        $sql = "SELECT c.*, ";
        if ($hasUsersTable && $hasViewersTable) {
            $sql .= "COALESCE(v.display_name, u.display_name, 'Anonymous') AS author_name, ";
        } elseif ($hasUsersTable) {
            $sql .= "COALESCE(u.display_name, 'Anonymous') AS author_name, ";
        } elseif ($hasViewersTable) {
            $sql .= "COALESCE(v.display_name, 'Anonymous') AS author_name, ";
        } else {
            $sql .= "'Anonymous' AS author_name, ";
        }
        $sql .= "CASE WHEN :viewer_id_check IS NOT NULL AND c.$authorIdCol = :viewer_id_match THEN 1 ELSE 0 END AS is_mine ";
        $sql .= "FROM comments c ";
        if ($hasUsersTable) { $sql .= "LEFT JOIN users u ON c.$authorIdCol = u.id "; }
        if ($hasViewersTable) { $sql .= "LEFT JOIN viewers v ON c.$authorIdCol = v.id "; }
        $sql .= "WHERE c.content_id = :content_id ";
        if ($hasCreatedAt) { $sql .= "ORDER BY c.created_at DESC "; } else { $sql .= "ORDER BY c.id DESC "; }
        $sql .= "LIMIT 200";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':viewer_id_check' => $viewerId, ':viewer_id_match' => $viewerId, ':content_id' => $contentId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Find this viewer's existing top-level comment id (if any)
        $myCommentId = null;
        if ($viewerId) {
            if ($hasParentId) {
                $chk = $pdo->prepare("SELECT id FROM comments WHERE content_id = ? AND $authorIdCol = ? AND (parent_id IS NULL OR parent_id = 0) LIMIT 1");
                $chk->execute([$contentId, $viewerId]);
            } else {
                $chk = $pdo->prepare("SELECT id FROM comments WHERE content_id = ? AND $authorIdCol = ? LIMIT 1");
                $chk->execute([$contentId, $viewerId]);
            }
            $row = $chk->fetch(PDO::FETCH_ASSOC);
            if ($row) { $myCommentId = (int)$row['id']; }
        }
        
        json_response(['comments' => $comments, 'my_comment_id' => $myCommentId]);
        
    } catch (Exception $e) {
        json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['content_id']) || !isset($input['text'])) {
        json_response(['error' => 'Content ID and text required'], 400);
    }
    
    $contentId = (int)$input['content_id'];
    $text = trim($input['text']);
    $parentId = isset($input['parent_id']) ? (int)$input['parent_id'] : null;
    $userId = $_SESSION['viewer_id'] ?? ($_SESSION['uid'] ?? ($_SESSION['user']['id'] ?? null));
    
    if (empty($text)) {
        json_response(['error' => 'Comment text cannot be empty'], 400);
    }
    
    // Only allow posting for logged-in viewers in this dashboard context
    if (!$userId) {
        json_response(['error' => 'Login required', 'code' => 'AUTH_REQUIRED'], 401);
    }
    
    try {
        $pdo = DatabaseConnectionFactory::createConnection();
        // Ensure comments table exists
        $pdo->exec('CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_id INT NOT NULL,
            user_id INT NULL,
            viewer_id INT NULL,
            parent_id INT NULL,
            `text` TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_content_id (content_id),
            INDEX idx_user_id (user_id),
            INDEX idx_viewer_id (viewer_id),
            INDEX idx_parent_id (parent_id),
            INDEX idx_created_at (created_at)
        )');

        // Detect author id column and available tables
        $hasUserId = false; $hasViewerId = false; $hasUsersTable = false; $hasViewersTable = false;
        try { $chk = $pdo->query("SHOW COLUMNS FROM comments LIKE 'user_id'"); $hasUserId = (bool)$chk->fetch(); } catch (Exception $e) {}
        try { $chk = $pdo->query("SHOW COLUMNS FROM comments LIKE 'viewer_id'"); $hasViewerId = (bool)$chk->fetch(); } catch (Exception $e) {}
        try { $chk = $pdo->query("SHOW TABLES LIKE 'users'"); $hasUsersTable = (bool)$chk->fetch(); } catch (Exception $e) {}
        try { $chk = $pdo->query("SHOW TABLES LIKE 'viewers'"); $hasViewersTable = (bool)$chk->fetch(); } catch (Exception $e) {}
        $authorIdCol = $hasUserId ? 'user_id' : ($hasViewerId ? 'viewer_id' : 'user_id');

        // Check if content exists (approved or visible)
        $stmt = $pdo->prepare('SELECT id FROM content WHERE id = ? AND status IN ("approved", "visible")');
        $stmt->execute([$contentId]);
        if (!$stmt->fetch()) {
            json_response(['error' => 'Content not found'], 404);
        }
        // Enforce constraint: one reply per parent per viewer (but allow multiple top-level comments)
        if (!empty($parentId)) {
            $chk = $pdo->prepare("SELECT id FROM comments WHERE parent_id = ? AND $authorIdCol = ? LIMIT 1");
            $chk->execute([$parentId, $userId]);
            if ($chk->fetch()) {
                json_response(['error' => 'You already replied to this comment', 'code' => 'ALREADY_REPLIED'], 409);
            }
        }

        // Insert comment
        $stmt = $pdo->prepare("\n            INSERT INTO comments (content_id, $authorIdCol, parent_id, `text`, created_at) \n            VALUES (?, ?, ?, ?, NOW())\n        ");
        $stmt->execute([$contentId, $userId, $parentId, $text]);

        $commentId = $pdo->lastInsertId();

        // Get the created comment with author info
        $select = "SELECT c.*, ";
        if ($hasUsersTable && $hasViewersTable) {
            $select .= "COALESCE(v.display_name, u.display_name, 'Anonymous') as author_name ";
        } elseif ($hasUsersTable) {
            $select .= "COALESCE(u.display_name, 'Anonymous') as author_name ";
        } elseif ($hasViewersTable) {
            $select .= "COALESCE(v.display_name, 'Anonymous') as author_name ";
        } else {
            $select .= "'Anonymous' as author_name ";
        }
        $select .= "FROM comments c ";
        if ($hasUsersTable) { $select .= "LEFT JOIN users u ON c.$authorIdCol = u.id "; }
        if ($hasViewersTable) { $select .= "LEFT JOIN viewers v ON c.$authorIdCol = v.id "; }
        $select .= "WHERE c.id = ?";
        $stmt = $pdo->prepare($select);
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        // Create notification for content owner (simple and safe)
        try {
            // Ensure notifications table exists
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
            // Lookup content owner
            $ownStmt = $pdo->prepare('SELECT user_id FROM content WHERE id = ?');
            $ownStmt->execute([$contentId]);
            $ownerId = (int)($ownStmt->fetchColumn() ?: 0);
            if ($ownerId > 0) {
                $payload = json_encode([
                    'content_id' => $contentId,
                    'comment_id' => (int)$commentId,
                    'by_user_id' => (int)$userId,
                    'text' => $text,
                    'title' => 'New Comment',
                    'message' => 'Someone commented on your content: "' . substr($text, 0, 50) . (strlen($text) > 50 ? '..."' : '"')
                ]);
                $ins = $pdo->prepare('INSERT INTO notifications (user_id, type, payload) VALUES (?, ?, ?)');
                $ins->execute([$ownerId, 'comment', $payload]);
            }
        } catch (Exception $e) { /* ignore notification errors */ }

        json_response(['ok' => true, 'comment' => $comment]);

    } catch (Exception $e) {
        json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// DELETE: allow viewer to delete own comment/reply
if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
    $id = isset($qs['id']) ? (int)$qs['id'] : 0;
    if ($id <= 0) { json_response(['error' => 'Invalid id'], 400); }
    $viewerId = $_SESSION['viewer_id'] ?? null;
    if (!$viewerId) { json_response(['error' => 'Login required', 'code' => 'AUTH_REQUIRED'], 401); }
    try {
        $pdo = DatabaseConnectionFactory::createConnection();
        // Detect author id column
        $hasUserId = false; $hasViewerId = false;
        try { $chk = $pdo->query("SHOW COLUMNS FROM comments LIKE 'user_id'"); $hasUserId = (bool)$chk->fetch(); } catch (Exception $e) {}
        try { $chk = $pdo->query("SHOW COLUMNS FROM comments LIKE 'viewer_id'"); $hasViewerId = (bool)$chk->fetch(); } catch (Exception $e) {}
        $authorIdCol = $hasUserId ? 'user_id' : ($hasViewerId ? 'viewer_id' : 'user_id');
        // Ensure ownership
        $own = $pdo->prepare("SELECT id FROM comments WHERE id = ? AND $authorIdCol = ?");
        $own->execute([$id, $viewerId]);
        if (!$own->fetch()) { json_response(['error' => 'Forbidden'], 403); }
        // Delete comment and its children
        $pdo->prepare('DELETE FROM comments WHERE parent_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM comments WHERE id = ?')->execute([$id]);
        json_response(['ok' => true]);
    } catch (Exception $e) {
        json_response(['error' => 'Database error'], 500);
    }
}

json_response(['error' => 'Method not allowed'], 405);

