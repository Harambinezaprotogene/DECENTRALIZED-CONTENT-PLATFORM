<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? null;

switch ($action) {
    case 'save_receipt':
        try {
            $paymentId = isset($input['payment_id']) ? (int)$input['payment_id'] : null;
            if (!$paymentId) {
                json_response(['ok' => false, 'error' => 'payment_id required'], 400);
            }

            $stmt = $pdo->prepare("INSERT INTO blockchain_reciept (
                payment_id, payment_id_hash, chain, contract_address, tx_hash, payer_address, amount_wei, onchain_status, block_number, onchain_written_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', ?, NOW())");

            $stmt->execute([
                $paymentId,
                $input['payment_id_hash'] ?? null,
                $input['chain'] ?? 'polygon-amoy',
                $input['contract_address'] ?? null,
                $input['tx_hash'] ?? null,
                $input['payer_address'] ?? null,
                $input['amount_wei'] ?? null,
                isset($input['block_number']) ? (int)$input['block_number'] : null,
            ]);

            json_response(['ok' => true]);
        } catch (Throwable $e) {
            json_response(['ok' => false, 'error' => $e->getMessage()], 500);
        }
        break;

    default:
        json_response(['ok' => false, 'error' => 'Unknown action'], 400);
}


