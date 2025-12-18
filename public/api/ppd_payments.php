<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['uid']) || (($_SESSION['role'] ?? '') !== 'creator')) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/db.php';

try { EnvLoader::load(__DIR__ . '/../../.env'); } catch (Throwable $e) {}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = (int)($_GET['page_size'] ?? 10);
if ($pageSize < 1) { $pageSize = 10; }
if ($pageSize > 100) { $pageSize = 100; }
$offset = ($page - 1) * $pageSize;

try {
    $pdo = DatabaseConnectionFactory::createConnection();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'db_failed']);
    exit;
}

// Ensure table exists (safe no-op if already present)
try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS ppd_payments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            content_id BIGINT UNSIGNED NOT NULL,
            creator_id BIGINT UNSIGNED NOT NULL,
            tx_hash VARCHAR(100) NOT NULL,
            sender_address VARCHAR(64) NOT NULL,
            recipient_address VARCHAR(64) NOT NULL,
            token_address VARCHAR(64) NOT NULL,
            amount_smallest VARCHAR(78) NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'confirmed',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_tx (tx_hash),
            KEY idx_content (content_id),
            KEY idx_creator (creator_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
} catch (Throwable $e) {
    // ignore
}

$creatorId = (int)$_SESSION['uid'];

try {
    $countStmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM ppd_payments WHERE creator_id = ?');
    $countStmt->execute([$creatorId]);
    $total = (int)($countStmt->fetchColumn() ?: 0);

    $stmt = $pdo->prepare('SELECT id, content_id, tx_hash, sender_address, recipient_address, token_address, amount_smallest, status, created_at FROM ppd_payments WHERE creator_id = ? ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->bindValue(1, $creatorId, PDO::PARAM_INT);
    $stmt->bindValue(2, $pageSize, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    echo json_encode([
        'ok' => true,
        'data' => [
            'page' => $page,
            'page_size' => $pageSize,
            'total' => $total,
            'items' => $rows
        ]
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'query_failed']);
    exit;
}


