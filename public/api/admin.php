<?php
require_once __DIR__ . '/_bootstrap.php';

// Admin-only access
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'Admin access required'], 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    if ($action === 'settings') {
        // Load current settings from tables
        try {
            $result = [];

            // Creator Requirements
            try {
                $stmt = $pdo->query('SELECT min_content_posts, min_account_age_days, require_verification FROM creator_requirements ORDER BY id ASC LIMIT 1');
                $result['creator_requirements'] = $stmt->fetch() ?: null;
            } catch (Exception $e) {
                $result['creator_requirements'] = null;
            }

            // Payment
            try {
                $stmt = $pdo->query('SELECT min_withdrawal_amount, platform_fee_percent, processing_fee, auto_payouts FROM payment_settings ORDER BY id ASC LIMIT 1');
                $result['payment'] = $stmt->fetch() ?: null;
            } catch (Exception $e) {
                $result['payment'] = null;
            }

            // Monetization
            try {
                $stmt = $pdo->query('SELECT payment_per_1000_views, min_followers_for_pay, min_views_for_payment, enable_monetization FROM monetization_settings ORDER BY id ASC LIMIT 1');
                $result['monetization'] = $stmt->fetch() ?: null;
            } catch (Exception $e) {
                $result['monetization'] = null;
            }

            // Platform
            try {
                $stmt = $pdo->query('SELECT site_name, max_upload_size_mb, maintenance_mode, auto_approve, require_creator_approval FROM platform_settings ORDER BY id ASC LIMIT 1');
                $result['platform'] = $stmt->fetch() ?: null;
            } catch (Exception $e) {
                $result['platform'] = null;
            }

            // Moderation
            try {
                $stmt = $pdo->query('SELECT auto_flag_threshold, review_time_limit_hours FROM moderation_settings ORDER BY id ASC LIMIT 1');
                $result['moderation'] = $stmt->fetch() ?: null;
            } catch (Exception $e) {
                $result['moderation'] = null;
            }

            // Auto Actions (global auto-approve/reject/moderate)
            try {
                $stmt = $pdo->query('SELECT auto_approve_uploads, auto_reject_uploads, auto_moderate_uploads FROM moderation_auto_actions ORDER BY id ASC LIMIT 1');
                $result['auto_actions'] = $stmt->fetch() ?: ['auto_approve_uploads' => 0, 'auto_reject_uploads' => 0, 'auto_moderate_uploads' => 0];
            } catch (Exception $e) {
                $result['auto_actions'] = ['auto_approve_uploads' => 0, 'auto_reject_uploads' => 0, 'auto_moderate_uploads' => 0];
            }

            json_response($result);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }
    if ($action === 'stats') {
        // Get platform statistics
        try {
            // Total users
            $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
            $totalUsers = (int)$stmt->fetch()['count'];
            
            // Total creators
            $stmt = $pdo->query('SELECT COUNT(*) as count FROM users WHERE role = "creator"');
            $totalCreators = (int)$stmt->fetch()['count'];
            
            // Total content
            $stmt = $pdo->query('SELECT COUNT(*) as count FROM content');
            $totalContent = (int)$stmt->fetch()['count'];
            
            // Total views
            $stmt = $pdo->query('SELECT COUNT(*) as count FROM engagements WHERE type = "view"');
            $totalViews = (int)$stmt->fetch()['count'];
            
            // Total likes
            $stmt = $pdo->query('SELECT COUNT(*) as count FROM engagements WHERE type = "like"');
            $totalLikes = (int)$stmt->fetch()['count'];
            
            // Total comments
            $stmt = $pdo->query('SELECT COUNT(*) as count FROM comments');
            $totalComments = (int)$stmt->fetch()['count'];
            
            json_response([
                'total_users' => $totalUsers,
                'total_creators' => $totalCreators,
                'total_content' => $totalContent,
                'total_views' => $totalViews,
                'total_likes' => $totalLikes,
                'total_comments' => $totalComments
            ]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }
    
    if ($action === 'recent_users') {
        // Get recent users
        try {
            $stmt = $pdo->query('
                SELECT id, email, role, display_name, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT 10
            ');
            $users = $stmt->fetchAll();
            
            json_response(['users' => $users]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }
    
    if ($action === 'recent_content') {
        // Get recent content
        try {
            $stmt = $pdo->query('
                SELECT c.id, c.title, c.description, c.category, c.status, c.created_at, c.file_type,
                       u.display_name as creator_name,
                       COALESCE(SUM(CASE WHEN e.type = "view" THEN 1 ELSE 0 END), 0) AS view_count
                FROM content c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN engagements e ON c.id = e.content_id AND e.type = "view"
                GROUP BY c.id
                ORDER BY c.created_at DESC 
                LIMIT 10
            ');
            $content = $stmt->fetchAll();
            
            json_response(['content' => $content]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }

    if ($action === 'recent_activity') {
        // Aggregate recent platform activity
        try {
            $activities = [];

            // New users
            $stmt = $pdo->query('
                SELECT id, display_name, email, created_at
                FROM users
                ORDER BY created_at DESC
                LIMIT 10
            ');
            foreach ($stmt->fetchAll() as $row) {
                $activities[] = [
                    'type' => 'new_user',
                    'title' => 'New user registered',
                    'details' => ($row['display_name'] ?: $row['email']) ?: 'User',
                    'ref_id' => (int)$row['id'],
                    'created_at' => $row['created_at']
                ];
            }

            // New content
            $stmt = $pdo->query('
                SELECT id, title, created_at
                FROM content
                ORDER BY created_at DESC
                LIMIT 10
            ');
            foreach ($stmt->fetchAll() as $row) {
                $activities[] = [
                    'type' => 'new_content',
                    'title' => 'New content uploaded',
                    'details' => $row['title'] ?: 'Untitled',
                    'ref_id' => (int)$row['id'],
                    'created_at' => $row['created_at']
                ];
            }

            // Moderation actions (enhanced to include content title)
            try {
                $stmt = $pdo->query('
                    SELECT l.content_id, l.admin_id, l.action, l.created_at, c.title
                    FROM content_moderation_log l
                    LEFT JOIN content c ON c.id = l.content_id
                    ORDER BY l.created_at DESC
                    LIMIT 10
                ');
                foreach ($stmt->fetchAll() as $row) {
                    $title = $row['title'] ?: ('#'.(int)$row['content_id']);
                    $activities[] = [
                        'type' => 'moderation',
                        'title' => ucfirst($row['action']).' content: '.$title,
                        'details' => $title,
                        'ref_id' => (int)$row['content_id'],
                        'created_at' => $row['created_at']
                    ];
                }
            } catch (Exception $e) { /* table may not exist yet */ }

            // Comments
            try {
                $stmt = $pdo->query('
                    SELECT id, content_id, created_at
                    FROM comments
                    ORDER BY created_at DESC
                    LIMIT 10
                ');
                foreach ($stmt->fetchAll() as $row) {
                    $activities[] = [
                        'type' => 'comment',
                        'title' => 'New comment',
                        'details' => 'On content #'.(int)$row['content_id'],
                        'ref_id' => (int)$row['content_id'],
                        'created_at' => $row['created_at']
                    ];
                }
            } catch (Exception $e) { /* may not exist */ }

            // Sort all activities by created_at DESC (newest first)
            usort($activities, function($a, $b) {
                $ta = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
                $tb = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
                return $tb <=> $ta;
            });

            json_response(['activities' => $activities]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }
    
    if ($action === 'all_users') {
        // Get all users for management
        try {
            $stmt = $pdo->query('
                SELECT id, email, role, display_name, created_at, 
                       COALESCE(status, "active") as status
                FROM users 
                ORDER BY created_at DESC
            ');
            $users = $stmt->fetchAll();
            
            json_response(['users' => $users]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }
    
    if ($action === 'all_content') {
        // Get all content for moderation
        try {
            $stmt = $pdo->query('
                SELECT c.id, c.title, c.description, c.category, c.status, c.created_at, c.file_type,
                       u.display_name as creator_name,
                       COALESCE(SUM(CASE WHEN e.type = "view" THEN 1 ELSE 0 END), 0) AS view_count
                FROM content c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN engagements e ON c.id = e.content_id AND e.type = "view"
                GROUP BY c.id
                ORDER BY c.created_at DESC
            ');
            $content = $stmt->fetchAll();
            
            json_response(['content' => $content]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }
    
    if ($action === 'user_details') {
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            json_response(['error' => 'Invalid user ID'], 400);
        }
        
        try {
            $stmt = $pdo->prepare('
                SELECT id, email, role, display_name, binance_pay_id, usdt_address, created_at, 
                       COALESCE(status, "active") as status
                FROM users 
                WHERE id = ?
            ');
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                json_response(['error' => 'User not found'], 404);
            }
            
            // Status is already fetched from database with COALESCE
            
            json_response(['user' => $user]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }

    if ($action === 'content_reports' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            $contentId = isset($_GET['content_id']) ? (int)$_GET['content_id'] : 0;
            if ($contentId <= 0) { json_response(['error' => 'Content ID required'], 400); }
            // Fetch reports for this content (mask reporter_id lightly for privacy)
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
            $stmt = $pdo->prepare('SELECT reporter_id, reason, note, created_at FROM content_reports WHERE content_id = ? ORDER BY created_at DESC');
            $stmt->execute([$contentId]);
            $reports = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $rid = (string)($r['reporter_id'] ?? '');
                $masked = $rid;
                if (strlen($rid) > 8) { $masked = substr($rid, 0, 4) . '***' . substr($rid, -3); }
                $reports[] = [
                    'reporter' => $masked,
                    'reason' => $r['reason'],
                    'note' => $r['note'],
                    'created_at' => $r['created_at'],
                ];
            }
            json_response(['ok' => true, 'items' => $reports]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }

    if ($action === 'flagged_content') {
        try {
            // List flagged items with report count and top reason
            $stmt = $pdo->query('
                SELECT c.id, c.title, c.status, c.created_at,
                       COALESCE(r.cnt, 0) as report_count,
                       r.top_reason
                FROM content c
                LEFT JOIN (
                    SELECT content_id,
                           COUNT(*) as cnt,
                           SUBSTRING_INDEX(GROUP_CONCAT(reason ORDER BY reason SEPARATOR ","), ",", 1) as top_reason
                    FROM content_reports
                    GROUP BY content_id
                ) r ON r.content_id = c.id
                WHERE c.status = "flagged"
                ORDER BY c.created_at DESC
                LIMIT 50
            ');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            json_response(['ok' => true, 'items' => $rows]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }

    if ($action === 'blockchain_receipts') {
        try {
            // Filters & pagination
            $limit = isset($_GET['limit']) ? max(1, min(200, (int)$_GET['limit'])) : 25;
            $page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $offset = ($page - 1) * $limit;
            $q = trim((string)($_GET['q'] ?? ''));
            $status = trim((string)($_GET['status'] ?? ''));

            $where = [];
            $params = [];
            if ($q !== '') {
                $where[] = '(u.display_name LIKE ? OR u.email LIKE ? OR br.tx_hash LIKE ? OR CAST(br.payment_id AS CHAR) LIKE ?)';
                $like = '%' . $q . '%';
                array_push($params, $like, $like, $like, $like);
            }
            if ($status !== '') {
                $where[] = 'br.onchain_status = ?';
                $params[] = $status;
            }
            $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

            // Total count
            $countSql = '
                SELECT COUNT(*) AS total
                FROM blockchain_reciept br
                LEFT JOIN payments p ON p.id = br.payment_id
                LEFT JOIN users u ON u.id = p.user_id
                ' . $whereSql;
            $countStmt = $pdo->prepare($countSql);
            foreach ($params as $i => $val) { $countStmt->bindValue($i+1, $val); }
            $countStmt->execute();
            $total = (int)($countStmt->fetch()['total'] ?? 0);

            // Page rows
            $sql = '
                SELECT br.id,
                       br.payment_id,
                       br.payment_id_hash,
                       br.chain,
                       br.contract_address,
                       br.tx_hash,
                       br.payer_address,
                       br.amount_wei,
                       br.onchain_status,
                       br.onchain_written_at,
                       br.onchain_confirmed_at,
                       br.block_number,
                       br.created_at,
                       p.user_id,
                       p.amount_cents AS payment_amount_cents,
                       p.currency AS payment_currency,
                       p.source   AS payment_source,
                       u.display_name AS creator_name,
                       u.email AS creator_email
                FROM blockchain_reciept br
                LEFT JOIN payments p ON p.id = br.payment_id
                LEFT JOIN users u ON u.id = p.user_id
                ' . $whereSql . '
                ORDER BY br.created_at DESC
                LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
            $stmt = $pdo->prepare($sql);
            foreach ($params as $i => $val) { $stmt->bindValue($i+1, $val); }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $hasMore = ($offset + count($rows)) < $total;
            json_response(['ok' => true, 'items' => $rows, 'total' => $total, 'page' => $page, 'limit' => $limit, 'has_more' => $hasMore]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }

    if ($action === 'requirement_impact') {
        try {
            // Get current requirements
            $stmt = $pdo->query('SELECT min_content_posts, min_account_age_days, require_verification FROM creator_requirements ORDER BY id ASC LIMIT 1');
            $creator_req = $stmt->fetch() ?: ['min_content_posts' => 5, 'min_account_age_days' => 30, 'require_verification' => 0];
            
            $stmt = $pdo->query('SELECT min_followers_for_pay, min_views_for_payment FROM monetization_settings ORDER BY id ASC LIMIT 1');
            $monetization_req = $stmt->fetch() ?: ['min_followers_for_pay' => 100, 'min_views_for_payment' => 1000];

            // Get all creators with their stats
            $sql = '
                SELECT u.id, u.display_name, u.email, u.is_verified, u.monetization_enabled, u.created_at,
                       COALESCE(c.post_count, 0) as post_count,
                       COALESCE(f.follower_count, 0) as follower_count,
                       COALESCE(v.view_count, 0) as view_count,
                       DATEDIFF(NOW(), u.created_at) as account_age_days
                FROM users u
                LEFT JOIN (
                    SELECT user_id, COUNT(*) as post_count 
                    FROM content 
                    GROUP BY user_id
                ) c ON c.user_id = u.id
                LEFT JOIN (
                    SELECT creator_id, COUNT(*) as follower_count 
                    FROM followers 
                    GROUP BY creator_id
                ) f ON f.creator_id = u.id
                LEFT JOIN (
                    SELECT c.user_id, COUNT(*) as view_count
                    FROM engagements e
                    INNER JOIN content c ON c.id = e.content_id
                    WHERE e.type = "view"
                    GROUP BY c.user_id
                ) v ON v.user_id = u.id
                WHERE u.role = "creator"
                ORDER BY u.created_at DESC
            ';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Analyze impact for each user
            $impact_users = [];
            $verification_impact = 0;
            $monetization_impact = 0;
            
            foreach ($users as $user) {
                $user['verification_impact'] = false;
                $user['monetization_impact'] = false;
                
                // Check verification impact (if currently verified but doesn't meet new requirements)
                if ($user['is_verified']) {
                    if ($user['post_count'] < $creator_req['min_content_posts'] || 
                        $user['account_age_days'] < $creator_req['min_account_age_days']) {
                        $user['verification_impact'] = true;
                        $verification_impact++;
                    }
                }
                
                // Check monetization impact (if currently monetized but doesn't meet new requirements)
                if ($user['monetization_enabled']) {
                    if ($user['follower_count'] < $monetization_req['min_followers_for_pay'] || 
                        $user['view_count'] < $monetization_req['min_views_for_payment']) {
                        $user['monetization_impact'] = true;
                        $monetization_impact++;
                    }
                }
                
                // Only include users who would be affected
                if ($user['verification_impact'] || $user['monetization_impact']) {
                    $impact_users[] = $user;
                }
            }
            
            $total_impact = count($impact_users);
            
            json_response([
                'ok' => true,
                'users' => $impact_users,
                'counts' => [
                    'verification_impact' => $verification_impact,
                    'monetization_impact' => $monetization_impact,
                    'total_impact' => $total_impact
                ]
            ]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
}

if ($method === 'POST') {
    if ($action === 'save_settings') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $group = $input['group'] ?? '';
        $data = $input['data'] ?? [];

        if (!in_array($group, ['creator_requirements', 'payment', 'monetization', 'platform', 'moderation', 'auto_actions'])) {
            json_response(['error' => 'Invalid group'], 400);
        }

        try {
            if ($group === 'creator_requirements') {
                $stmt = $pdo->query('SELECT id FROM creator_requirements ORDER BY id ASC LIMIT 1');
                $row = $stmt->fetch();
                if ($row) {
                    $stmt = $pdo->prepare('UPDATE creator_requirements SET min_content_posts=?, min_account_age_days=?, require_verification=?, updated_by=?, updated_at=NOW() WHERE id=?');
                    $stmt->execute([
                        (int)($data['min_content_posts'] ?? 5),
                        (int)($data['min_account_age_days'] ?? 30),
                        !empty($data['require_verification']) ? 1 : 0,
                        (int)$_SESSION['uid'],
                        (int)$row['id']
                    ]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO creator_requirements (min_content_posts, min_account_age_days, require_verification, updated_by, updated_at) VALUES (?, ?, ?, ?, NOW())');
                    $stmt->execute([
                        (int)($data['min_content_posts'] ?? 5),
                        (int)($data['min_account_age_days'] ?? 30),
                        !empty($data['require_verification']) ? 1 : 0,
                        (int)$_SESSION['uid']
                    ]);
                }
            }

            if ($group === 'payment') {
                $stmt = $pdo->query('SELECT id FROM payment_settings ORDER BY id ASC LIMIT 1');
                $row = $stmt->fetch();
                if ($row) {
                    $stmt = $pdo->prepare('UPDATE payment_settings SET min_withdrawal_amount=?, platform_fee_percent=?, processing_fee=?, auto_payouts=?, updated_by=?, updated_at=NOW() WHERE id=?');
                    $stmt->execute([
                        (float)($data['min_withdrawal_amount'] ?? 50.0),
                        (float)($data['platform_fee_percent'] ?? 10.0),
                        (float)($data['processing_fee'] ?? 2.5),
                        !empty($data['auto_payouts']) ? 1 : 0,
                        (int)$_SESSION['uid'],
                        (int)$row['id']
                    ]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO payment_settings (min_withdrawal_amount, platform_fee_percent, processing_fee, auto_payouts, updated_by, updated_at) VALUES (?, ?, ?, ?, ?, NOW())');
                    $stmt->execute([
                        (float)($data['min_withdrawal_amount'] ?? 50.0),
                        (float)($data['platform_fee_percent'] ?? 10.0),
                        (float)($data['processing_fee'] ?? 2.5),
                        !empty($data['auto_payouts']) ? 1 : 0,
                        (int)$_SESSION['uid']
                    ]);
                }
            }

            if ($group === 'monetization') {
                $stmt = $pdo->query('SELECT id FROM monetization_settings ORDER BY id ASC LIMIT 1');
                $row = $stmt->fetch();
                if ($row) {
                    $stmt = $pdo->prepare('UPDATE monetization_settings SET payment_per_1000_views=?, min_followers_for_pay=?, min_views_for_payment=?, enable_monetization=?, updated_by=?, updated_at=NOW() WHERE id=?');
                    $stmt->execute([
                        (float)($data['payment_per_1000_views'] ?? 0.5),
                        (int)($data['min_followers_for_pay'] ?? 1000),
                        (int)($data['min_views_for_payment'] ?? 10000),
                        !empty($data['enable_monetization']) ? 1 : 0,
                        (int)$_SESSION['uid'],
                        (int)$row['id']
                    ]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO monetization_settings (payment_per_1000_views, min_followers_for_pay, min_views_for_payment, enable_monetization, updated_by, updated_at) VALUES (?, ?, ?, ?, ?, NOW())');
                    $stmt->execute([
                        (float)($data['payment_per_1000_views'] ?? 0.5),
                        (int)($data['min_followers_for_pay'] ?? 1000),
                        (int)($data['min_views_for_payment'] ?? 10000),
                        !empty($data['enable_monetization']) ? 1 : 0,
                        (int)$_SESSION['uid']
                    ]);
                }
            }

            if ($group === 'platform') {
                $stmt = $pdo->query('SELECT id FROM platform_settings ORDER BY id ASC LIMIT 1');
                $row = $stmt->fetch();
                if ($row) {
                    $stmt = $pdo->prepare('UPDATE platform_settings SET site_name=?, max_upload_size_mb=?, maintenance_mode=?, auto_approve=?, require_creator_approval=?, updated_by=?, updated_at=NOW() WHERE id=?');
                    $stmt->execute([
                        (string)($data['site_name'] ?? 'Kabaka'),
                        (int)($data['max_upload_size_mb'] ?? 100),
                        !empty($data['maintenance_mode']) ? 1 : 0,
                        !empty($data['auto_approve']) ? 1 : 0,
                        !empty($data['require_creator_approval']) ? 1 : 0,
                        (int)$_SESSION['uid'],
                        (int)$row['id']
                    ]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO platform_settings (site_name, max_upload_size_mb, maintenance_mode, auto_approve, require_creator_approval, updated_by, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
                    $stmt->execute([
                        (string)($data['site_name'] ?? 'Kabaka'),
                        (int)($data['max_upload_size_mb'] ?? 100),
                        !empty($data['maintenance_mode']) ? 1 : 0,
                        !empty($data['auto_approve']) ? 1 : 0,
                        !empty($data['require_creator_approval']) ? 1 : 0,
                        (int)$_SESSION['uid']
                    ]);
                }
            }

            if ($group === 'moderation') {
                $stmt = $pdo->query('SELECT id FROM moderation_settings ORDER BY id ASC LIMIT 1');
                $row = $stmt->fetch();
                if ($row) {
                    $stmt = $pdo->prepare('UPDATE moderation_settings SET auto_flag_threshold=?, review_time_limit_hours=?, updated_by=?, updated_at=NOW() WHERE id=?');
                    $stmt->execute([
                        (int)($data['auto_flag_threshold'] ?? 5),
                        (int)($data['review_time_limit_hours'] ?? 24),
                        (int)$_SESSION['uid'],
                        (int)$row['id']
                    ]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO moderation_settings (auto_flag_threshold, review_time_limit_hours, updated_by, updated_at) VALUES (?, ?, ?, NOW())');
                    $stmt->execute([
                        (int)($data['auto_flag_threshold'] ?? 5),
                        (int)($data['review_time_limit_hours'] ?? 24),
                        (int)$_SESSION['uid']
                    ]);
                }
            }

            if ($group === 'auto_actions') {
                try {
                    $stmt = $pdo->query('SELECT id FROM moderation_auto_actions ORDER BY id ASC LIMIT 1');
                    $row = $stmt->fetch();

                    // Support either a single 'mode' or individual flags
                    $mode = isset($data['mode']) ? (string)$data['mode'] : null;
                    if ($mode !== null) {
                        $autoApprove = $mode === 'approve' ? 1 : 0;
                        $autoReject = $mode === 'reject' ? 1 : 0;
                        $autoModerate = $mode === 'moderate' ? 1 : 0;
                    } else {
                        $autoApprove = !empty($data['auto_approve_uploads']) ? 1 : 0;
                        $autoReject = !empty($data['auto_reject_uploads']) ? 1 : 0;
                        $autoModerate = !empty($data['auto_moderate_uploads']) ? 1 : 0;

                        // Ensure exclusivity if multiple were accidentally sent
                        if ($autoApprove + $autoReject + $autoModerate > 1) {
                            // Prefer reject > moderate > approve
                            $autoReject = $autoReject ? 1 : 0;
                            $autoModerate = $autoReject ? 0 : ($autoModerate ? 1 : 0);
                            $autoApprove = ($autoReject || $autoModerate) ? 0 : ($autoApprove ? 1 : 0);
                        }
                    }

                    if ($row) {
                        $stmt = $pdo->prepare('UPDATE moderation_auto_actions SET auto_approve_uploads=?, auto_reject_uploads=?, auto_moderate_uploads=?, updated_by=?, updated_at=NOW() WHERE id=?');
                        $stmt->execute([$autoApprove, $autoReject, $autoModerate, (int)$_SESSION['uid'], (int)$row['id']]);
                    } else {
                        $stmt = $pdo->prepare('INSERT INTO moderation_auto_actions (auto_approve_uploads, auto_reject_uploads, auto_moderate_uploads, updated_by, updated_at) VALUES (?, ?, ?, ?, NOW())');
                        $stmt->execute([$autoApprove, $autoReject, $autoModerate, (int)$_SESSION['uid']]);
                    }
                } catch (Exception $e) {
                    json_response(['error' => 'Database error'], 500);
                }
            }

            json_response(['ok' => true]);
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }
    if ($action === 'update_user') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $userId = (int)($input['user_id'] ?? 0);
        $role = $input['role'] ?? '';
        $status = $input['status'] ?? '';
        $displayName = $input['display_name'] ?? '';
        $email = $input['email'] ?? '';
        $usdtAddress = $input['usdt_address'] ?? '';
        $password = $input['password'] ?? '';
        
        if ($userId <= 0) {
            json_response(['error' => 'Invalid user ID'], 400);
        }
        
        if (!in_array($role, ['viewer', 'creator', 'admin']) && $role !== '') {
            json_response(['error' => 'Invalid role'], 400);
        }
        
        try {
            $updates = [];
            $params = [];
            
            if ($role !== '') {
                $updates[] = 'role = ?';
                $params[] = $role;
            }
            
            if ($displayName !== '') {
                $updates[] = 'display_name = ?';
                $params[] = $displayName;
            }
            
            if ($email !== '') {
                $updates[] = 'email = ?';
                $params[] = $email;
            }
            
            if ($usdtAddress !== '') {
                $updates[] = 'usdt_address = ?';
                $params[] = $usdtAddress;
            }
            
            if ($password !== '') {
                $updates[] = 'password_hash = ?';
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            if (!empty($updates)) {
                $params[] = $userId;
                $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                json_response(['ok' => true, 'message' => 'User updated successfully']);
            } else {
                json_response(['error' => 'No updates provided'], 400);
            }
        } catch (Exception $e) {
            json_response(['error' => 'Database error'], 500);
        }
    }
    
    if ($action === 'create_user') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $displayName = $input['display_name'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? '';
        $usdtAddress = $input['usdt_address'] ?? '';
        $status = $input['status'] ?? 'active';
        
        // Validate required fields
        if (empty($displayName) || empty($email) || empty($password) || empty($role)) {
            json_response(['error' => 'Missing required fields'], 400);
        }
        
        if (!in_array($role, ['viewer', 'creator', 'admin'])) {
            json_response(['error' => 'Invalid role'], 400);
        }
        
        try {
            // Check if email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                json_response(['error' => 'Email already exists'], 400);
            }
            
            // Create new user (without is_verified and monetization_enabled for now)
            $stmt = $pdo->prepare('
                INSERT INTO users (display_name, email, password_hash, role, usdt_address, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            
            $stmt->execute([
                $displayName,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $role,
                $usdtAddress
            ]);
            
            $userId = $pdo->lastInsertId();
            
            json_response(['ok' => true, 'message' => 'User created successfully', 'user_id' => $userId]);
            
        } catch (Exception $e) {
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
    
    if ($action === 'ban_user') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $userId = intval($input['user_id'] ?? 0);
        
        if ($userId <= 0) {
            json_response(['error' => 'Invalid user ID'], 400);
        }
        
        try {
            // First, ensure status column exists with correct ENUM values
            try {
                $pdo->exec('ALTER TABLE users ADD COLUMN status ENUM("active", "banned", "inactive", "pending", "suspended", "rejected") DEFAULT "active"');
            } catch (Exception $e) {
                // Column might already exist, try to update ENUM values
                try {
                    $pdo->exec('ALTER TABLE users MODIFY COLUMN status ENUM("active", "banned", "inactive", "pending", "suspended", "rejected") DEFAULT "active"');
                } catch (Exception $e2) {
                    // Continue anyway
                }
            }
            
            // Update user status to banned
            $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
            $stmt->execute(['banned', $userId]);
            
            json_response(['ok' => true, 'message' => 'User banned successfully']);
            
        } catch (Exception $e) {
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
    
    if ($action === 'approve_user') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $userId = intval($input['user_id'] ?? 0);
        
        if ($userId <= 0) {
            json_response(['error' => 'Invalid user ID'], 400);
        }
        
        try {
            // First, ensure status column exists with correct ENUM values
            try {
                $pdo->exec('ALTER TABLE users ADD COLUMN status ENUM("active", "banned", "inactive", "pending", "suspended", "rejected") DEFAULT "active"');
            } catch (Exception $e) {
                // Column might already exist, try to update ENUM values
                try {
                    $pdo->exec('ALTER TABLE users MODIFY COLUMN status ENUM("active", "banned", "inactive", "pending", "suspended", "rejected") DEFAULT "active"');
                } catch (Exception $e2) {
                    // Continue anyway
                }
            }
            
            // Update user status to active
            $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
            $stmt->execute(['active', $userId]);
            
            json_response(['ok' => true, 'message' => 'User approved successfully']);
            
        } catch (Exception $e) {
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
    
    if ($action === 'delete_user') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $userId = intval($input['user_id'] ?? 0);
        
        if ($userId <= 0) {
            json_response(['error' => 'Invalid user ID'], 400);
        }
        
        // Prevent admin from deleting themselves
        if ($userId == $_SESSION['uid']) {
            json_response(['error' => 'Cannot delete your own account'], 400);
        }
        
        try {
            // Check if user exists
            $stmt = $pdo->prepare('SELECT id, display_name, role FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                json_response(['error' => 'User not found'], 404);
            }
            
            // Delete user (this will cascade to related tables due to foreign keys)
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            
            json_response(['ok' => true, 'message' => 'User deleted successfully', 'deleted_user' => $user['display_name']]);
            
        } catch (Exception $e) {
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
    
    // Content moderation endpoints
    if ($action === 'approve_content') {
        $contentId = $_POST['content_id'] ?? null;
        
        if (!$contentId) {
            json_response(['error' => 'Content ID required'], 400);
        }
        
        try {
            // Check if content exists
            $stmt = $pdo->prepare('SELECT id, title, status FROM content WHERE id = ?');
            $stmt->execute([$contentId]);
            $content = $stmt->fetch();
            
            if (!$content) {
                json_response(['error' => 'Content not found'], 404);
            }
            
            // Update content status to approved
            $stmt = $pdo->prepare('UPDATE content SET status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute(['approved', $contentId]);

            // Clear prior reports to prevent immediate re-flagging after approval
            try { $pdo->prepare('DELETE FROM content_reports WHERE content_id = ?')->execute([$contentId]); } catch (Exception $e) {}

            // Log moderation approval
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
                $adminId = $_SESSION['uid'] ?? null;
                $log = $pdo->prepare('INSERT INTO content_moderation_log (content_id, admin_id, action, details) VALUES (?, ?, "approve", ?)');
                $log->execute([$contentId, $adminId, 'approved by admin']);
            } catch (Exception $e) { /* ignore */ }
            
            json_response(['ok' => true, 'message' => 'Content approved successfully', 'content_title' => $content['title']]);
            
        } catch (Exception $e) {
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
    
    if ($action === 'remove_content') {
        $contentId = $_POST['content_id'] ?? null;
        
        if (!$contentId) {
            json_response(['error' => 'Content ID required'], 400);
        }
        
        try {
            // Check if content exists
            $stmt = $pdo->prepare('SELECT id, title, status FROM content WHERE id = ?');
            $stmt->execute([$contentId]);
            $content = $stmt->fetch();
            
            if (!$content) {
                json_response(['error' => 'Content not found'], 404);
            }
            
            // Update content status to removed
            $stmt = $pdo->prepare('UPDATE content SET status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute(['removed', $contentId]);
            
            json_response(['ok' => true, 'message' => 'Content removed successfully', 'content_title' => $content['title']]);
            
        } catch (Exception $e) {
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
    
    if ($action === 'moderate_content') {
        $contentId = $_POST['content_id'] ?? null;
        
        if (!$contentId) {
            json_response(['error' => 'Content ID required'], 400);
        }
        
        try {
            // Check if content exists
            $stmt = $pdo->prepare('SELECT id, title, status FROM content WHERE id = ?');
            $stmt->execute([$contentId]);
            $content = $stmt->fetch();
            
            if (!$content) {
                json_response(['error' => 'Content not found'], 404);
            }
            
            // Update content status to pending
            $stmt = $pdo->prepare('UPDATE content SET status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute(['pending', $contentId]);
            
            json_response(['ok' => true, 'message' => 'Content set to pending review', 'content_title' => $content['title']]);
            
        } catch (Exception $e) {
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
    
    if ($action === 'reject_content') {
        $contentId = $_POST['content_id'] ?? null;
        
        if (!$contentId) {
            json_response(['error' => 'Content ID required'], 400);
        }
        
        try {
            // Check if content exists
            $stmt = $pdo->prepare('SELECT id, title, status FROM content WHERE id = ?');
            $stmt->execute([$contentId]);
            $content = $stmt->fetch();
            
            if (!$content) {
                json_response(['error' => 'Content not found'], 404);
            }
            
            // Update content status to rejected
            $stmt = $pdo->prepare('UPDATE content SET status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute(['rejected', $contentId]);
            
            json_response(['ok' => true, 'message' => 'Content rejected', 'content_title' => $content['title']]);
            
        } catch (Exception $e) {
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
    
    if ($action === 'bulk_unverify') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $userIds = $input['user_ids'] ?? [];
        
        if (!is_array($userIds) || empty($userIds)) {
            json_response(['error' => 'User IDs required'], 400);
        }
        
        // Validate all user IDs are integers
        $userIds = array_filter(array_map('intval', $userIds), function($id) { return $id > 0; });
        if (empty($userIds)) {
            json_response(['error' => 'Invalid user IDs'], 400);
        }
        
        try {
            $pdo->beginTransaction();
            
            $unverified_count = 0;
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            
            // Unverify users (set is_verified = 0 and monetization_enabled = 0)
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 0, monetization_enabled = 0 WHERE id IN ($placeholders) AND role = 'creator'");
            $stmt->execute($userIds);
            $unverified_count = $stmt->rowCount();
            
            // Log the bulk action
            try {
                $pdo->exec('CREATE TABLE IF NOT EXISTS admin_actions_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    admin_id INT NOT NULL,
                    action VARCHAR(64) NOT NULL,
                    target_type VARCHAR(32) NOT NULL,
                    target_ids TEXT NOT NULL,
                    details TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    KEY idx_admin_id (admin_id),
                    KEY idx_action (action),
                    KEY idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
                
                $stmt = $pdo->prepare('INSERT INTO admin_actions_log (admin_id, action, target_type, target_ids, details) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([
                    $_SESSION['uid'],
                    'bulk_unverify',
                    'users',
                    implode(',', $userIds),
                    "Bulk unverified $unverified_count creators due to requirement changes"
                ]);
            } catch (Exception $e) {
                // Log table creation failed, but don't fail the transaction
            }
            
            $pdo->commit();
            
            json_response([
                'ok' => true, 
                'message' => "Successfully unverified $unverified_count users",
                'unverified_count' => $unverified_count
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
}

json_response(['error' => 'Not Found'], 404);
?>
