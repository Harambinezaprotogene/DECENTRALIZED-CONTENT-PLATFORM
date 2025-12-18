<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/db.php';

$envLoaded = false;
try {
    EnvLoader::load(__DIR__ . '/../../.env');
    $envLoaded = true;
} catch (Throwable $e) {
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
$contentId = isset($data['content_id']) ? (int)$data['content_id'] : 0;
$txHash = isset($data['tx_hash']) ? trim((string)$data['tx_hash']) : '';

if ($contentId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'content_id required']);
    exit;
}

// Dev-only fake success: set environment variable PPD_FAKE_SUCCESS=1
$fake = getenv('PPD_FAKE_SUCCESS');
if ($fake === '1') {
    echo json_encode([
        'ok' => true,
        'verified' => true,
        'reason' => 'fake_success'
    ]);
    exit;
}

try {
    $pdo = DatabaseConnectionFactory::createConnection();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

// If no tx hash yet, tell client to wait
if ($txHash === '') {
    echo json_encode(['ok' => true, 'verified' => false, 'reason' => 'tx_missing']);
    exit;
}

// Load target details for expected token, amount and recipient
try {
    $stmt = $pdo->prepare('SELECT u.usdt_address, u.id AS creator_id FROM content c INNER JOIN users u ON u.id = c.user_id WHERE c.id = ?');
    $stmt->execute([$contentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Content not found']);
        exit;
    }
    $creatorAddress = strtolower(trim((string)($row['usdt_address'] ?? '')));

    $tokenAddress = strtolower((string)(getenv('PPD_USDT_ADDRESS') ?: ''));
    $tokenDecimals = (int)(getenv('PPD_TOKEN_DECIMALS') ?: 6);
    $priceUsdt = getenv('PPD_PRICE_USDT') ?: '1';

    // compute expected amount in smallest units as decimal string
    $parts = explode('.', $priceUsdt, 2);
    $whole = preg_replace('/\D/', '', $parts[0] ?? '0');
    $frac = preg_replace('/\D/', '', $parts[1] ?? '');
    if (strlen($frac) > $tokenDecimals) { $frac = substr($frac, 0, $tokenDecimals); }
    $frac = str_pad($frac, $tokenDecimals, '0');
    $expectedAmount = ltrim($whole . $frac, '0');
    if ($expectedAmount === '') { $expectedAmount = '0'; }

    if ($tokenAddress === '' || $creatorAddress === '') {
        echo json_encode(['ok' => true, 'verified' => false, 'reason' => 'config_missing']);
        exit;
    }

    // Query RPC for transaction data and receipt
    $rpcUrl = getenv('PPD_RPC_URL') ?: (getenv('AMOY_RPC_URL') ?: 'https://rpc-amoy.polygon.technology');

    $rpc = function($method, $params = []) use ($rpcUrl) {
        $payload = json_encode([ 'jsonrpc' => '2.0', 'id' => 1, 'method' => $method, 'params' => $params ]);
        $opts = [ 'http' => [ 'method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => $payload, 'timeout' => 15 ] ];
        $ctx = stream_context_create($opts);
        $out = @file_get_contents($rpcUrl, false, $ctx);
        if ($out === false) { return null; }
        return json_decode($out, true);
    };

    $tx = $rpc('eth_getTransactionByHash', [$txHash]);
    $rcpt = $rpc('eth_getTransactionReceipt', [$txHash]);
    if (!$tx || !$rcpt || isset($tx['error']) || isset($rcpt['error'])) {
        echo json_encode(['ok' => true, 'verified' => false, 'reason' => 'rpc_error']);
        exit;
    }
    $tx = $tx['result'] ?? null;
    $rcpt = $rcpt['result'] ?? null;
    if (!$tx || !$rcpt) {
        echo json_encode(['ok' => true, 'verified' => false, 'reason' => 'pending']);
        exit;
    }

    // Basic checks: to == token, input starts with transfer selector
    $toAddr = strtolower($tx['to'] ?? '');
    $input = strtolower($tx['input'] ?? '');
    $status = strtolower($rcpt['status'] ?? '0x0');
    if ($status !== '0x1') {
        echo json_encode(['ok' => true, 'verified' => false, 'reason' => 'failed']);
        exit;
    }
    if ($toAddr !== $tokenAddress) {
        echo json_encode(['ok' => true, 'verified' => false, 'reason' => 'wrong_token']);
        exit;
    }
    if (strpos($input, '0xa9059cbb') !== 0 || strlen($input) < 10 + 64 + 64) {
        echo json_encode(['ok' => true, 'verified' => false, 'reason' => 'not_transfer']);
        exit;
    }

    // Decode transfer(address,uint256)
    $toHex = '0x' . substr($input, 10 + 24, 40); // address rightmost 20 bytes
    $amountHex = '0x' . substr($input, 10 + 64, 64);

    $toHex = strtolower($toHex);
    $expectedTo = '0x' . strtolower(ltrim($creatorAddress, '0x'));
    if ($toHex !== $expectedTo) {
        echo json_encode(['ok' => true, 'verified' => false, 'reason' => 'wrong_recipient']);
        exit;
    }

    // Compare amounts as big ints (hex to decimal string is heavy; compare hex normalized)
    $amountHexStr = strtolower(ltrim($amountHex, '0x'));
    $expectedHex = strtolower(str_pad(dechex((int)0), 1, '0')); // placeholder
    // Convert expectedAmount (decimal string) to hex string safely
    $decToHex = function($decStr) {
        $decStr = ltrim($decStr, '0');
        if ($decStr === '') return '0';
        $hex = '';
        $carry = $decStr;
        while ($carry !== '' && $carry !== '0') {
            $q = '';
            $r = 0;
            for ($i = 0; $i < strlen($carry); $i++) {
                $num = $r * 10 + (ord($carry[$i]) - 48);
                $digit = intdiv($num, 16);
                $r = $num % 16;
                if (!($q === '' && $digit === 0)) { $q .= chr(48 + $digit); }
            }
            $hex = dechex($r) . $hex;
            $carry = $q === '' ? '0' : $q;
        }
        return $hex === '' ? '0' : $hex;
    };
    $expectedHex = $decToHex($expectedAmount);
    $amountHexStr = ltrim($amountHexStr, '0'); if ($amountHexStr === '') $amountHexStr = '0';
    $expectedHex = ltrim(strtolower($expectedHex), '0'); if ($expectedHex === '') $expectedHex = '0';

    if ($amountHexStr !== $expectedHex) {
        echo json_encode(['ok' => true, 'verified' => false, 'reason' => 'wrong_amount']);
        exit;
    }

    // Persist successful payment for auditing/unlock logic
    try {
        // Create table if it doesn't exist
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

        // Insert if not exists
        $ins = $pdo->prepare(
            'INSERT IGNORE INTO ppd_payments (content_id, creator_id, tx_hash, sender_address, recipient_address, token_address, amount_smallest, status) VALUES (?,?,?,?,?,?,?,?)'
        );
        $sender = strtolower($tx['from'] ?? '');
        $recipient = $expectedTo;
        $ins->execute([$contentId, (int)$row['creator_id'], $txHash, $sender, $recipient, $tokenAddress, $expectedAmount, 'confirmed']);
    } catch (Throwable $ignore) {
        // Non-fatal: do not block download if logging fails
    }

    // Optionally, return a signed URL (not implemented yet). Frontend falls back to original link.
    echo json_encode(['ok' => true, 'verified' => true, 'url' => null]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
    exit;
}


