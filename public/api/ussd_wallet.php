<?php
// Africa's Talking USSD endpoint (public)
// Expects POST with: sessionId, serviceCode, phoneNumber, text

require_once __DIR__ . '/_bootstrap.php';

header('Content-Type: text/plain');

$sessionId   = $_POST['sessionId']   ?? '';
$serviceCode = $_POST['serviceCode'] ?? '';
$phone       = $_POST['phoneNumber'] ?? '';
$text        = trim((string)($_POST['text'] ?? ''));

// State machine using text segments (Africa's Talking uses * separator)
$segments = $text === '' ? [] : explode('*', $text);

function reply($message, $end = false) {
    // CON for continuation, END to terminate
    echo ($end ? 'END ' : 'CON ') . $message;
    exit;
}

try {
    // Menu
    if (count($segments) === 0) {
        reply("1. Check balance\n2. View wallet address");
    }

    $choice = trim($segments[0]);
    if ($choice === '1') {
        // Check balance by USDT address
        if (count($segments) === 1) {
            reply("Enter USDT address:");
        }
        $addr = trim($segments[1]);
        if ($addr === '') { reply("Invalid address.\nEnter USDT address:"); }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE LOWER(usdt_address) = LOWER(?)');
        $stmt->execute([$addr]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$u) { reply("Wallet not found.\nEnter USDT address:"); }

        $userId = (int)$u['id'];
        $stmt = $pdo->prepare('SELECT id, user_id, balance_cents, pending_cents, updated_at FROM wallets WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1');
        $stmt->execute([$userId]);
        $w = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$w) {
            $w = [ 'id' => 0, 'user_id' => $userId, 'balance_cents' => 0, 'pending_cents' => 0, 'updated_at' => null ];
        }

        $balance = number_format(((int)$w['balance_cents']) / 100.0, 2, '.', '');
        $pending = number_format(((int)$w['pending_cents']) / 100.0, 2, '.', '');
        reply("Balance: $${balance}\nPending: $${pending}", true);
    }

    if ($choice === '2') {
        // View wallet address by user id
        if (count($segments) === 1) {
            reply("Enter your user ID:");
        }
        $uid = trim($segments[1]);
        if ($uid === '' || !ctype_digit($uid) || (int)$uid <= 0) { reply("Invalid ID.\nEnter your user ID:"); }

        $stmt = $pdo->prepare('SELECT usdt_address FROM users WHERE id = ?');
        $stmt->execute([(int)$uid]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        $addr = $u['usdt_address'] ?? '';
        if (!$u) { reply('User not found.', true); }
        if ($addr === '' || $addr === null) { reply('No wallet address set.', true); }
        reply($addr, true);
    }

    // Fallback
    reply("1. Check balance\n2. View wallet address");
} catch (Exception $e) {
    reply('Service unavailable. Try again later.', true);
}

?>


