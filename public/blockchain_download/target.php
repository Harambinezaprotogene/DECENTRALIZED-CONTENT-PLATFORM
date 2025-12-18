<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/db.php';

EnvLoader::load(__DIR__ . '/../../.env');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$contentId = isset($_GET['content_id']) ? (int)$_GET['content_id'] : 0;
if ($contentId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'content_id required']);
    exit;
}

try {
    $pdo = DatabaseConnectionFactory::createConnection();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed']);
    exit;
}

try {
    // Find creator user and their USDT address for the given content
    $stmt = $pdo->prepare('SELECT u.usdt_address, u.id AS creator_id FROM content c INNER JOIN users u ON u.id = c.user_id WHERE c.id = ?');
    $stmt->execute([$contentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Content not found']);
        exit;
    }

    $creatorAddress = trim((string)($row['usdt_address'] ?? ''));
    $creatorId = (int)$row['creator_id'];

    // Config from environment
    $chainId = (int)(getenv('PPD_CHAIN_ID') ?: 80002); // Polygon Amoy
    $rpcUrl = getenv('PPD_RPC_URL') ?: (getenv('AMOY_RPC_URL') ?: 'https://rpc-amoy.polygon.technology');
    $usdtAddress = getenv('PPD_USDT_ADDRESS') ?: '';
    // Fixed price in whole USDT (decimal string). Default 1 USDT.
    $priceUsdt = getenv('PPD_PRICE_USDT') ?: '1';
    // Token decimals (USDT is typically 6 on Polygon). Allow override.
    $tokenDecimals = (int)(getenv('PPD_TOKEN_DECIMALS') ?: 6);

    if ($usdtAddress === '') {
        echo json_encode(['ok' => false, 'error' => 'USDT token address not configured']);
        exit;
    }

    // Compute smallest unit amount as string to avoid float issues
    // priceUsdt can be like "1" or "0.50"
    $parts = explode('.', $priceUsdt, 2);
    $whole = preg_replace('/\D/', '', $parts[0] ?? '0');
    $frac = preg_replace('/\D/', '', $parts[1] ?? '');
    if (strlen($frac) > $tokenDecimals) {
        $frac = substr($frac, 0, $tokenDecimals);
    }
    $frac = str_pad($frac, $tokenDecimals, '0');
    $amountSmallest = ltrim($whole . $frac, '0');
    if ($amountSmallest === '') { $amountSmallest = '0'; }

    echo json_encode([
        'ok' => true,
        'data' => [
            'chainId' => $chainId,
            'rpcUrl' => $rpcUrl,
            'token' => [
                'address' => $usdtAddress,
                'decimals' => $tokenDecimals,
                'symbol' => getenv('PPD_TOKEN_SYMBOL') ?: 'USDT'
            ],
            'price' => [
                'human' => $priceUsdt,
                'amount' => $amountSmallest // smallest units as string
            ],
            'creator' => [
                'id' => $creatorId,
                'usdt_address' => $creatorAddress
            ]
        ]
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
    exit;
}


