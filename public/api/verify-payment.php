<?php
<?php
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../vendor/autoload.php';

use Web3\Web3;
use Web3\Contract;

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['contentId']) || !isset($data['transactionHash'])) {
        throw new Exception('Missing parameters');
    }

    $web3 = new Web3('YOUR_ETHEREUM_NODE_URL');
    
    // Get transaction details
    $transaction = $web3->eth->getTransaction($data['transactionHash']);
    
    if ($transaction) {
        // Record transaction in database
        $stmt = $pdo->prepare("INSERT INTO blockchain_transactions (content_id, transaction_hash, amount) VALUES (?, ?, ?)");
        $stmt->execute([$data['contentId'], $data['transactionHash'], $transaction->value]);
        
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Invalid transaction');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}