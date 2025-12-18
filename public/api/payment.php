<?php
// Start output buffering to catch any warnings/notices
ob_start();

// Set error reporting to catch all errors
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, we'll handle them

// Ensure session cookie is accessible from all paths
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', '');
    session_start();
}
header('Content-Type: application/json');

// Function to handle errors and return JSON
function handleError($message, $code = 500) {
    // Clean any buffered output
    if (ob_get_level()) {
        ob_clean();
    }
    
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: application/json');
    }
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// Function to record blockchain receipt (muted - no user interaction)
function recordBlockchainReceipt($payment_id, $amount_cents, $user_id) {
    // Convert cents to wei (assuming USDT has 6 decimals, but we'll use 18 for consistency)
    $amount_wei = strval($amount_cents * 1000000000000); // Convert cents to wei (1 USDT = 10^18 wei)
    
    // Generate unique payment ID hash
    $payment_id_hash = hash('sha256', 'withdrawal_' . $payment_id . '_' . time());
    
    // Call signer service
    $signer_url = 'http://localhost:4001/record';
    $auth_token = 'kabaka-secret-2024'; // Match your .env AUTH_TOKEN
    
    $data = [
        'payment_id' => $payment_id_hash,
        'amount_wei' => $amount_wei,
        'payer_address' => '0x0000000000000000000000000000000000000000' // Platform address
    ];
    
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\nAuthorization: Bearer $auth_token\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($signer_url, false, $context);
    
    if ($result === false) {
        throw new Exception('Failed to call signer service');
    }
    
    $response = json_decode($result, true);
    if (!$response || !$response['ok']) {
        throw new Exception('Signer service error: ' . ($response['error'] ?? 'Unknown error'));
    }
    
    // Save receipt to database
    global $pdo;
    $stmt = $pdo->prepare('
        INSERT INTO blockchain_reciept 
        (payment_id, payment_id_hash, chain, contract_address, tx_hash, payer_address, amount_wei, onchain_status, onchain_written_at, block_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?, "confirmed", NOW(), ?)
    ');
    $stmt->execute([
        $payment_id,
        $payment_id_hash,
        'polygon-amoy',
        $response['contract'],
        $response['tx_hash'],
        $data['payer_address'],
        $amount_wei,
        $response['block_number']
    ]);
}


// Restrict to creators only
if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'creator') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized - Please log in as a creator']);
    exit;
}

try {
    // Get the correct path to config files (from public/api/ to root)
    $config_path = __DIR__ . '/../../config/';
    require_once $config_path . 'env.php';
    require_once $config_path . 'db.php';

    // Load environment variables
    EnvLoader::load(__DIR__ . '/../../.env');

    $pdo = DatabaseConnectionFactory::createConnection();
    $user_id = $_SESSION['uid'];
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'wallet':
            handleWalletRequest($pdo, $user_id);
            break;
        case 'eligibility':
            handleEligibilityRequest($pdo, $user_id);
            break;
        case 'settings':
            handleSettingsRequest($pdo);
            break;
        case 'transactions':
            handleTransactionsRequest($pdo, $user_id);
            break;
        case 'withdraw':
            handleWithdrawalRequest($pdo, $user_id);
            break;
        case 'requirements':
            handleRequirementsRequest($pdo, $user_id);
            break;
        case 'request_verification':
            handleVerificationRequest($pdo, $user_id);
            break;
        case 'request_monetization':
            handleMonetizationRequest($pdo, $user_id);
            break;
        default:
            handleError('Invalid action: ' . $action, 400);
    }
} catch (Exception $e) {
    handleError('Database error: ' . $e->getMessage(), 500);
} catch (Error $e) {
    handleError('PHP error: ' . $e->getMessage(), 500);
}

function handleWalletRequest($pdo, $user_id) {
    try {
        // Get wallet balance
        $stmt = $pdo->prepare('SELECT balance_cents, pending_cents FROM wallets WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $wallet = $stmt->fetch();
        
        if (!$wallet) {
            // Create wallet if doesn't exist
            $stmt = $pdo->prepare('INSERT INTO wallets (user_id, balance_cents, pending_cents) VALUES (?, 0, 0)');
            $stmt->execute([$user_id]);
            
            $wallet = [
                'balance_cents' => 0,
                'pending_cents' => 0
            ];
        }

        // Get total earned from payments
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(amount_cents), 0) as total_earned FROM payments WHERE user_id = ? AND source = "tip" AND status = "completed"');
        $stmt->execute([$user_id]);
        $total_earned = $stmt->fetch()['total_earned'];

        // Calculate earned money from engagements (views) on this creator's content
        $stmt = $pdo->prepare('
            SELECT COUNT(*) as view_count 
            FROM engagements e
            INNER JOIN content c ON c.id = e.content_id
            WHERE c.user_id = ? AND e.type = "view"
        ');
        $stmt->execute([$user_id]);
        $view_count = $stmt->fetch()['view_count'];
        
        // Get monetization rate
        $stmt = $pdo->prepare('SELECT payment_per_1000_views FROM monetization_settings LIMIT 1');
        $stmt->execute();
        $rate = $stmt->fetch()['payment_per_1000_views'] ?? 0.50;
        
        // Calculate earned money from unclaimed views only (views on this creator's content)
        $stmt = $pdo->prepare('
            SELECT COUNT(*) as unclaimed_views 
            FROM engagements e
            INNER JOIN content c ON c.id = e.content_id
            WHERE c.user_id = ? AND e.type = "view" AND (e.claimed = 0 OR e.claimed IS NULL)
        ');
        $stmt->execute([$user_id]);
        $unclaimed_views = $stmt->fetch()['unclaimed_views'];
        
        // Calculate available money from unclaimed views
        $available_for_withdrawal = ($unclaimed_views / 1000) * $rate * 100; // Convert to cents
        
        // Calculate total earned from all views (for display)
        $earned_from_views = ($view_count / 1000) * $rate * 100; // Convert to cents
        
        // Calculate total claimed (for display)
        $total_claimed = $earned_from_views - $available_for_withdrawal;

        // Clean any buffered output before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'balance_cents' => $wallet['balance_cents'],
                'pending_cents' => $wallet['pending_cents'],
                'currency' => 'USDT', // Default currency
                'total_earned' => $total_earned,
                'earned_from_views' => $earned_from_views,
                'available_for_withdrawal' => $available_for_withdrawal,
                'total_claimed' => $total_claimed,
                'view_count' => $view_count,
                'unclaimed_views' => $unclaimed_views
            ]
        ]);
    } catch (Exception $e) {
        handleError('Failed to load wallet data: ' . $e->getMessage());
    }
}

function handleEligibilityRequest($pdo, $user_id) {
    try {
        // Get user info
        $stmt = $pdo->prepare('SELECT is_verified, monetization_enabled, usdt_address FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('User not found');
        }

        // Get wallet balance
        $stmt = $pdo->prepare('SELECT balance_cents FROM wallets WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $wallet = $stmt->fetch();
        $balance_cents = $wallet ? $wallet['balance_cents'] : 0;

        // Get admin settings for minimum withdrawal
        $stmt = $pdo->prepare('SELECT min_withdrawal_amount FROM payment_settings LIMIT 1');
        $stmt->execute();
        $settings = $stmt->fetch();
        $min_withdrawal_amount = $settings ? intval($settings['min_withdrawal_amount'] * 100) : 1000; // Convert to cents

        echo json_encode([
            'success' => true,
            'data' => [
                'is_verified' => (bool)$user['is_verified'],
                'monetization_enabled' => (bool)$user['monetization_enabled'],
                'payout_destination' => $user['usdt_address'],
                'balance_cents' => $balance_cents,
                'min_withdrawal_amount' => $min_withdrawal_amount
            ]
        ]);
    } catch (Exception $e) {
        handleError('Failed to load eligibility data: ' . $e->getMessage());
    }
}

function handleSettingsRequest($pdo) {
    try {
        // Get admin payment settings
        $stmt = $pdo->prepare('SELECT * FROM payment_settings LIMIT 1');
        $stmt->execute();
        $settings = $stmt->fetch();
        
        if (!$settings) {
            // Default settings if none exist
            $settings = [
                'min_withdrawal_amount' => 10.00, // $10
                'platform_fee_percent' => 5.00,
                'processing_fee' => 1.00, // $1
                'auto_payouts' => 1,
                'currency' => 'USDT'
            ];
        } else {
            // Convert decimal amounts to cents for consistency
            $settings['min_withdrawal_amount'] = intval($settings['min_withdrawal_amount'] * 100);
            $settings['processing_fee'] = intval($settings['processing_fee'] * 100);
            $settings['auto_monthly_payouts'] = $settings['auto_payouts'];
        }

        echo json_encode([
            'success' => true,
            'data' => $settings
        ]);
    } catch (Exception $e) {
        handleError('Failed to load settings: ' . $e->getMessage());
    }
}

function handleTransactionsRequest($pdo, $user_id) {
    try {
        // Get recent transactions (last 20)
        $stmt = $pdo->prepare('
            SELECT id, amount_cents, currency, source, 
                   CASE 
                       WHEN status IS NULL OR status = "" THEN "completed"
                       ELSE status 
                   END as status, 
                   tx_id, created_at 
            FROM payments 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 20
        ');
        $stmt->execute([$user_id]);
        $transactions = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'data' => $transactions
        ]);
    } catch (Exception $e) {
        handleError('Failed to load transactions: ' . $e->getMessage());
    }
}

function handleWithdrawalRequest($pdo, $user_id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    try {
        $amount = floatval($_POST['amount'] ?? 0);
        $amount_cents = intval($amount * 100); // Convert dollars to cents

        if ($amount <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid amount']);
            return;
        }

        // Check eligibility
        $stmt = $pdo->prepare('SELECT is_verified, monetization_enabled, usdt_address FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user || !$user['is_verified'] || !$user['monetization_enabled'] || !$user['usdt_address']) {
            echo json_encode(['success' => false, 'error' => 'Account not eligible for withdrawals']);
            return;
        }

        // Check monetization requirements from admin settings
        $stmt = $pdo->prepare('SELECT min_followers_for_pay, min_views_for_payment FROM monetization_settings LIMIT 1');
        $stmt->execute();
        $monetization_settings = $stmt->fetch();
        
        if ($monetization_settings) {
            // Check minimum followers (assuming followers table exists)
            $stmt = $pdo->prepare('SELECT COUNT(*) as follower_count FROM followers WHERE creator_id = ?');
            $stmt->execute([$user_id]);
            $follower_count = $stmt->fetch()['follower_count'];
            
            if ($follower_count < $monetization_settings['min_followers_for_pay']) {
                echo json_encode(['success' => false, 'error' => 'Not enough followers for monetization']);
                return;
            }
            
            // Check minimum views (views on this creator's content)
            $stmt = $pdo->prepare('SELECT COUNT(*) as view_count FROM engagements e INNER JOIN content c ON c.id = e.content_id WHERE c.user_id = ? AND e.type = "view"');
            $stmt->execute([$user_id]);
            $view_count = $stmt->fetch()['view_count'];
            
            if ($view_count < $monetization_settings['min_views_for_payment']) {
                echo json_encode(['success' => false, 'error' => 'Not enough views for payment']);
                return;
            }
        }

        // Check if wallet exists (no balance check needed since we're adding money)
        $stmt = $pdo->prepare('SELECT balance_cents FROM wallets WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $wallet = $stmt->fetch();
        
        if (!$wallet) {
            // Create wallet if doesn't exist
            $stmt = $pdo->prepare('INSERT INTO wallets (user_id, balance_cents, pending_cents) VALUES (?, 0, 0)');
            $stmt->execute([$user_id]);
        }

        // Get admin settings
        $stmt = $pdo->prepare('SELECT min_withdrawal_amount, platform_fee_percent, processing_fee, auto_payouts FROM payment_settings LIMIT 1');
        $stmt->execute();
        $settings = $stmt->fetch();
        $min_withdrawal = $settings ? intval($settings['min_withdrawal_amount'] * 100) : 1000; // Convert to cents
        $platform_fee_percent = $settings ? $settings['platform_fee_percent'] : 10.0;
        $processing_fee = $settings ? intval($settings['processing_fee'] * 100) : 250; // Convert to cents
        $auto_payouts = $settings ? $settings['auto_payouts'] : 1;
        
        // Get monetization rate for view calculation
        $stmt = $pdo->prepare('SELECT payment_per_1000_views FROM monetization_settings LIMIT 1');
        $stmt->execute();
        $rate = $stmt->fetch()['payment_per_1000_views'] ?? 0.50;

        if ($amount_cents < $min_withdrawal) {
            echo json_encode(['success' => false, 'error' => 'Amount below minimum withdrawal']);
            return;
        }

        // Check if withdrawal amount exceeds available earnings
        $stmt = $pdo->prepare('
            SELECT COUNT(*) as unclaimed_views 
            FROM engagements e
            INNER JOIN content c ON c.id = e.content_id
            WHERE c.user_id = ? AND e.type = "view" AND (e.claimed = 0 OR e.claimed IS NULL)
        ');
        $stmt->execute([$user_id]);
        $unclaimed_views = $stmt->fetch()['unclaimed_views'];
        $available_earnings = ($unclaimed_views / 1000) * $rate * 100; // Convert to cents
        
        if ($amount_cents > $available_earnings) {
            echo json_encode(['success' => false, 'error' => 'Withdrawal amount exceeds available earnings']);
            return;
        }

        // Calculate fees
        $platform_fee = intval($amount_cents * ($platform_fee_percent / 100));
        $net_amount = $amount_cents - $platform_fee - $processing_fee;

        if ($net_amount <= 0) {
            echo json_encode(['success' => false, 'error' => 'Amount too small after fees']);
            return;
        }

        // Start transaction
        $pdo->beginTransaction();

        try {
            // Calculate how many views to mark as claimed
            $views_to_claim = intval(($amount_cents / 100) / $rate * 1000); // Convert withdrawal amount to views
            
            // Mark views as claimed
            $stmt = $pdo->prepare('
                UPDATE engagements e
                INNER JOIN content c ON c.id = e.content_id
                SET e.claimed = 1 
                WHERE c.user_id = ? AND e.type = "view" AND (e.claimed = 0 OR e.claimed IS NULL)
                LIMIT ?
            ');
            $stmt->execute([$user_id, $views_to_claim]);
            
            // Add net amount to wallet balance (money is added, not subtracted)
            $stmt = $pdo->prepare('UPDATE wallets SET balance_cents = balance_cents + ? WHERE user_id = ?');
            $stmt->execute([$net_amount, $user_id]);

            // Create payment record with net amount (after fees) and completed status
            $tx_id = 'TX_' . time() . '_' . $user_id;
            $stmt = $pdo->prepare('
                INSERT INTO payments (user_id, amount_cents, currency, source, status, tx_id, created_at) 
                VALUES (?, ?, ?, "payout", "completed", ?, NOW())
            ');
            $stmt->execute([$user_id, $net_amount, 'USDT', $tx_id]);
            $payment_id = $pdo->lastInsertId();

            $pdo->commit();

            // Record blockchain receipt (muted - no user interaction)
            try {
                recordBlockchainReceipt($payment_id, $net_amount, $user_id);
            } catch (Exception $e) {
                // Log error but don't fail withdrawal
                error_log("Blockchain receipt failed for payment $payment_id: " . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Withdrawal processed successfully',
                'data' => [
                    'payment_id' => $payment_id,
                    'amount' => $amount,
                    'net_amount' => $net_amount / 100, // Convert back to dollars for display
                    'fees_deducted' => ($platform_fee + $processing_fee) / 100,
                    'status' => 'completed'
                ]
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        handleError('Failed to process withdrawal request: ' . $e->getMessage());
    }
}

function handleRequirementsRequest($pdo, $user_id) {
    try {
        // Get creator requirements
        $stmt = $pdo->prepare('SELECT min_content_posts, min_account_age_days, require_verification FROM creator_requirements LIMIT 1');
        $stmt->execute();
        $requirements = $stmt->fetch();
        
        if (!$requirements) {
            // Default requirements if none exist
            $requirements = [
                'min_content_posts' => 5,
                'min_account_age_days' => 30,
                'require_verification' => 0
            ];
        }

        // Get monetization requirements
        $stmt = $pdo->prepare('SELECT min_followers_for_pay, min_views_for_payment FROM monetization_settings LIMIT 1');
        $stmt->execute();
        $monetization = $stmt->fetch();
        
        if (!$monetization) {
            // Default monetization requirements
            $monetization = [
                'min_followers_for_pay' => 100,
                'min_views_for_payment' => 1000
            ];
        }

        // Get user's current stats
        $stmt = $pdo->prepare('SELECT created_at, is_verified FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        // Calculate account age
        $account_age_days = 0;
        if ($user) {
            $created_at = new DateTime($user['created_at']);
            $now = new DateTime();
            $account_age_days = $now->diff($created_at)->days;
        }

        // Count user's content posts
        $stmt = $pdo->prepare('SELECT COUNT(*) as post_count FROM content WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $current_posts = $stmt->fetch()['post_count'];

        // Count user's followers (assuming followers table exists)
        $stmt = $pdo->prepare('SELECT COUNT(*) as follower_count FROM followers WHERE creator_id = ?');
        $stmt->execute([$user_id]);
        $current_followers = $stmt->fetch()['follower_count'];

        // Count user's views (on this creator's content)
        $stmt = $pdo->prepare('SELECT COUNT(*) as view_count FROM engagements e INNER JOIN content c ON c.id = e.content_id WHERE c.user_id = ? AND e.type = "view"');
        $stmt->execute([$user_id]);
        $current_views = $stmt->fetch()['view_count'];

        echo json_encode([
            'success' => true,
            'data' => [
                // Creator requirements
                'min_content_posts' => $requirements['min_content_posts'],
                'min_account_age_days' => $requirements['min_account_age_days'],
                'require_verification' => $requirements['require_verification'],
                
                // Monetization requirements
                'min_followers_for_pay' => $monetization['min_followers_for_pay'],
                'min_views_for_payment' => $monetization['min_views_for_payment'],
                
                // Current user stats
                'current_posts' => $current_posts,
                'account_age_days' => $account_age_days,
                'current_followers' => $current_followers,
                'current_views' => $current_views,
                'is_verified' => $user ? (bool)$user['is_verified'] : false,
                'email_verified' => true // Assuming email is verified if user can log in
            ]
        ]);
    } catch (Exception $e) {
        handleError('Failed to load requirements: ' . $e->getMessage());
    }
}

function handleVerificationRequest($pdo, $user_id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    try {
        // Check if user is already verified
        $stmt = $pdo->prepare('SELECT is_verified FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user && $user['is_verified']) {
            echo json_encode(['success' => false, 'error' => 'Your account is already verified']);
            return;
        }

        // Get creator requirements
        $stmt = $pdo->prepare('SELECT min_content_posts, min_account_age_days FROM creator_requirements LIMIT 1');
        $stmt->execute();
        $requirements = $stmt->fetch();
        
        if (!$requirements) {
            // Default requirements if none exist
            $requirements = [
                'min_content_posts' => 5,
                'min_account_age_days' => 30
            ];
        }

        // Get user's current stats
        $stmt = $pdo->prepare('SELECT created_at FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        
        // Calculate account age
        $account_age_days = 0;
        if ($user_data) {
            $created_at = new DateTime($user_data['created_at']);
            $now = new DateTime();
            $account_age_days = $now->diff($created_at)->days;
        }

        // Count user's content posts
        $stmt = $pdo->prepare('SELECT COUNT(*) as post_count FROM content WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $current_posts = $stmt->fetch()['post_count'];

        // Check if user meets all requirements
        $meets_requirements = true;
        $missing_requirements = [];

        if ($current_posts < $requirements['min_content_posts']) {
            $meets_requirements = false;
            $missing_requirements[] = "Need " . ($requirements['min_content_posts'] - $current_posts) . " more posts";
        }

        if ($account_age_days < $requirements['min_account_age_days']) {
            $meets_requirements = false;
            $missing_requirements[] = "Account must be " . $requirements['min_account_age_days'] . " days old (currently " . $account_age_days . " days)";
        }

        if ($meets_requirements) {
            // Auto-approve verification
            $stmt = $pdo->prepare('UPDATE users SET is_verified = 1 WHERE id = ?');
            $stmt->execute([$user_id]);

            echo json_encode([
                'success' => true,
                'message' => 'Congratulations! Your account has been verified automatically.',
                'verified' => true
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'You do not meet the verification requirements yet.',
                'missing_requirements' => $missing_requirements,
                'verified' => false
            ]);
        }
    } catch (Exception $e) {
        handleError('Failed to process verification request: ' . $e->getMessage());
    }
}

function handleMonetizationRequest($pdo, $user_id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    try {
        // Check if user already has monetization enabled
        $stmt = $pdo->prepare('SELECT monetization_enabled FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user && $user['monetization_enabled']) {
            echo json_encode(['success' => false, 'error' => 'Monetization is already enabled for your account']);
            return;
        }

        // Check if user is verified first
        $stmt = $pdo->prepare('SELECT is_verified FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        
        if (!$user_data || !$user_data['is_verified']) {
            echo json_encode(['success' => false, 'error' => 'You must be verified first before enabling monetization']);
            return;
        }

        // Get monetization requirements
        $stmt = $pdo->prepare('SELECT min_followers_for_pay, min_views_for_payment FROM monetization_settings LIMIT 1');
        $stmt->execute();
        $requirements = $stmt->fetch();
        
        if (!$requirements) {
            // Default monetization requirements
            $requirements = [
                'min_followers_for_pay' => 100,
                'min_views_for_payment' => 1000
            ];
        }

        // Count user's followers (assuming followers table exists)
        $stmt = $pdo->prepare('SELECT COUNT(*) as follower_count FROM followers WHERE creator_id = ?');
        $stmt->execute([$user_id]);
        $current_followers = $stmt->fetch()['follower_count'];

        // Count user's views (on their content)
        $stmt = $pdo->prepare('SELECT COUNT(*) as view_count FROM engagements e INNER JOIN content c ON c.id = e.content_id WHERE c.user_id = ? AND e.type = "view"');
        $stmt->execute([$user_id]);
        $current_views = $stmt->fetch()['view_count'];

        // Check if user meets all requirements
        $meets_requirements = true;
        $missing_requirements = [];

        if ($current_followers < $requirements['min_followers_for_pay']) {
            $meets_requirements = false;
            $missing_requirements[] = "Need " . ($requirements['min_followers_for_pay'] - $current_followers) . " more followers";
        }

        if ($current_views < $requirements['min_views_for_payment']) {
            $meets_requirements = false;
            $missing_requirements[] = "Need " . ($requirements['min_views_for_payment'] - $current_views) . " more views";
        }

        if ($meets_requirements) {
            // Auto-approve monetization
            $stmt = $pdo->prepare('UPDATE users SET monetization_enabled = 1 WHERE id = ?');
            $stmt->execute([$user_id]);

            echo json_encode([
                'success' => true,
                'message' => 'Congratulations! Monetization has been enabled automatically.',
                'monetization_enabled' => true
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'You do not meet the monetization requirements yet.',
                'missing_requirements' => $missing_requirements,
                'monetization_enabled' => false
            ]);
        }
    } catch (Exception $e) {
        handleError('Failed to process monetization request: ' . $e->getMessage());
    }
}
?>