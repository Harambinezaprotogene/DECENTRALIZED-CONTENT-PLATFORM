<?php
require_once __DIR__ . '/_bootstrap.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'report') {
    try {
        $inputRaw = file_get_contents('php://input');
        $data = json_decode($inputRaw ?: '{}', true) ?: [];
        $contentId = (int)($data['content_id'] ?? 0);
        $reason = trim((string)($data['reason'] ?? ''));
        $note = trim((string)($data['note'] ?? ''));
        if ($contentId <= 0 || $reason === '') {
            http_response_code(400);
            echo json_encode(['error' => 'content_id and reason are required']);
            exit;
        }
        // clamp reason/note
        $allowedReasons = ['spam','sexual','violence','copyright','other'];
        if (!in_array(strtolower($reason), $allowedReasons, true)) {
            $reason = 'other';
        }
        if (strlen($note) > 1000) { $note = substr($note, 0, 1000); }

        $pdo = DatabaseConnectionFactory::createConnection();

        // Ensure content exists and fetch owner/title
        $chk = $pdo->prepare('SELECT id, user_id, title, status FROM content WHERE id = ?');
        $chk->execute([$contentId]);
        $row = $chk->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Content not found']);
            exit;
        }

        // Build reporter id (viewer_id preferred, else uid, else anon hash)
        $reporterId = null;
        if (isset($_SESSION['viewer_id'])) { $reporterId = 'viewer:' . (int)$_SESSION['viewer_id']; }
        elseif (isset($_SESSION['uid'])) { $reporterId = 'user:' . (int)$_SESSION['uid']; }
        else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $reporterId = 'anon_' . substr(hash('sha256', $ip.'|'.$ua), 0, 16);
        }

        // Ensure reports table exists
        $pdo->exec('CREATE TABLE IF NOT EXISTS content_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_id INT NOT NULL,
            reporter_id VARCHAR(255) NOT NULL,
            reason VARCHAR(64) NOT NULL,
            note TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_report (content_id, reporter_id),
            KEY idx_content_id (content_id),
            KEY idx_reporter_id (reporter_id),
            KEY idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        // Insert report (idempotent per reporter/content)
        $ins = $pdo->prepare('INSERT INTO content_reports (content_id, reporter_id, reason, note) VALUES (?, ?, ?, ?)');
        try { $ins->execute([$contentId, $reporterId, strtolower($reason), $note]); } catch (Exception $e) { /* duplicate -> ignore */ }

        // Count reports
        $cnt = $pdo->prepare('SELECT COUNT(*) FROM content_reports WHERE content_id = ?');
        $cnt->execute([$contentId]);
        $reportCount = (int)$cnt->fetchColumn();

        // Read auto-flag threshold from moderation_settings
        $threshold = 3; // default fallback
        try {
            $ms = $pdo->query('SELECT auto_flag_threshold FROM moderation_settings ORDER BY id DESC LIMIT 1');
            $msRow = $ms->fetch(PDO::FETCH_ASSOC);
            if ($msRow && isset($msRow['auto_flag_threshold'])) {
                $threshold = max(1, (int)$msRow['auto_flag_threshold']);
            }
        } catch (Exception $e) { /* table may not exist yet */ }

        $flagged = false;
        if ($reportCount >= $threshold) {
            // Flag content if not already flagged
            $upd = $pdo->prepare("UPDATE content SET status = 'flagged' WHERE id = ? AND status <> 'flagged'");
            $upd->execute([$contentId]);
            $flagged = $upd->rowCount() > 0 || ($row['status'] === 'flagged');

            // Write moderation log if table exists
            try {
                $pdo->exec('CREATE TABLE IF NOT EXISTS content_moderation_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    content_id INT NOT NULL,
                    admin_id INT NULL,
                    action ENUM("flag","unflag","approve","reject") NOT NULL,
                    details VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    KEY idx_content_id (content_id),
                    KEY idx_action (action),
                    KEY idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
                $log = $pdo->prepare('INSERT INTO content_moderation_log (content_id, admin_id, action, details) VALUES (?, NULL, "flag", ?)');
                $log->execute([$contentId, 'auto-flagged: '.$reportCount.' reports >= threshold '.$threshold]);
            } catch (Exception $e) { /* ignore */ }

            // Notify content owner (simple notification)
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
                $payload = json_encode([
                    'content_id' => (int)$contentId,
                    'title' => (string)($row['title'] ?? ''),
                    'reports' => $reportCount,
                    'title_text' => 'Content flagged',
                    'message' => 'Your content was auto-flagged due to reports.'
                ]);
                $pdo->prepare('INSERT INTO notifications (user_id, type, payload) VALUES (?, ?, ?)')
                    ->execute([(int)$row['user_id'], 'content_flagged', $payload]);
            } catch (Exception $e) { /* ignore */ }
        }

        echo json_encode([
            'ok' => true,
            'reported' => true,
            'reports' => $reportCount,
            'flagged' => $reportCount >= $threshold
        ]);
        exit;
        } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>
