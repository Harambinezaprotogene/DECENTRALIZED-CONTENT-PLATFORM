<?php
require_once '_bootstrap.php';

// Auth and role detection: allow viewers to access GET for approved content
$user_id = $_SESSION['uid'] ?? ($_SESSION['viewer_id'] ?? null);
$role = $_SESSION['role'] ?? (isset($_SESSION['viewer_id']) ? 'viewer' : null);
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$is_admin = ($role === 'admin');
$is_creator = ($role === 'creator');
$is_viewer = ($role === 'viewer');
$method = $_SERVER['REQUEST_METHOD'];

// Only creators/admins may modify content
if ($method !== 'GET' && !$is_admin && !$is_creator) {
    http_response_code(403);
    echo json_encode(['error' => 'Only creators or admins may modify content']);
    exit;
}

try {
    $pdo = DatabaseConnectionFactory::createConnection();
    
    // Get platform settings with fallback defaults
    $platform_settings = null;
    try {
        $platform_settings = $pdo->query("SELECT * FROM platform_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Table doesn't exist, use defaults
        $platform_settings = null;
    }
    
    if (!$platform_settings) {
        $platform_settings = [
            'max_upload_size_mb' => 25,
            'auto_approve' => 1,
            'require_creator_approval' => 0
        ];
    }
    
    $max_upload_size = $platform_settings['max_upload_size_mb'] * 1024 * 1024; // Convert to bytes
    $auto_approve = (int)($platform_settings['auto_approve'] ?? 0) === 1;

    // If a moderation settings table exists, prefer its auto-approve flag when present
    try {
        $moderation = $pdo->query("SELECT auto_approve_uploads FROM moderation_auto_actions LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($moderation && array_key_exists('auto_approve_uploads', $moderation)) {
            $auto_approve = (int)$moderation['auto_approve_uploads'] === 1;
        }
    } catch (Throwable $e) {
        // Table may not exist; safely ignore and keep platform_settings fallback
    }
    
    switch ($method) {
        case 'GET':
            // Get single content item details
            if (isset($_GET['id']) && !isset($_GET['action'])) {
                $content_id = (int)$_GET['id'];
                
                if ($is_admin) {
                    // Admin can view any content
                    $stmt = $pdo->prepare("
                        SELECT 
                            c.*,
                            u.display_name as creator_name,
                            u.email as creator_email,
                            (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'view') as view_count,
                            (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'like') as like_count,
                            (SELECT COUNT(*) FROM comments WHERE content_id = c.id) as comment_count
                        FROM content c
                        LEFT JOIN users u ON c.user_id = u.id
                        WHERE c.id = ?
                    ");
                    $stmt->execute([$content_id]);
                } else {
                    if ($is_creator) {
                        // Creator can only view their own content
                        $stmt = $pdo->prepare("
                            SELECT 
                                c.*,
                                (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'view') as view_count,
                                (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'like') as like_count,
                                (SELECT COUNT(*) FROM comments WHERE content_id = c.id) as comment_count
                            FROM content c
                            WHERE c.id = ? AND c.user_id = ?
                        ");
                        $stmt->execute([$content_id, $user_id]);
                    } else {
                        // Viewer can only view approved content
                        $stmt = $pdo->prepare("
                            SELECT 
                                c.*,
                                (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'view') as view_count,
                                (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'like') as like_count,
                                (SELECT COUNT(*) FROM comments WHERE content_id = c.id) as comment_count
                            FROM content c
                            WHERE c.id = ? AND c.status = 'approved'
                        ");
                        $stmt->execute([$content_id]);
                    }
                }
                $content = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$content) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Content not found']);
                    exit;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $content
                ]);
                break;
            }
            
            // Summary for overview
            if (isset($_GET['action']) && $_GET['action'] === 'summary') {
                // Safe platform settings defaults
                if (!$platform_settings) {
                    $platform_settings = [
                        'max_upload_size_mb' => 25,
                        'auto_approve' => 1,
                        'require_creator_approval' => 0,
                    ];
                }

                // Ensure auxiliary tables exist (no-ops if present)
                try {
                    $pdo->exec('CREATE TABLE IF NOT EXISTS engagements (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        content_id INT NOT NULL,
                        user_id VARCHAR(255) NOT NULL,
                        type ENUM("view", "like", "follow") NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_content_id (content_id),
                        INDEX idx_user_id (user_id),
                        INDEX idx_type (type),
                        INDEX idx_created_at (created_at)
                    )');
                } catch (Throwable $e) { /* ignore */ }
                try {
                    $pdo->exec('CREATE TABLE IF NOT EXISTS followers (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        follower_id INT NOT NULL,
                        followed_id INT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_followed_id (followed_id)
                    )');
                } catch (Throwable $e) { /* ignore */ }

                // Content counts
                if ($is_admin) {
                    $counts = $pdo->prepare("SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) as rejected
                    FROM content");
                    $counts->execute();
                } else {
                    $counts = $pdo->prepare("SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) as rejected
                    FROM content WHERE user_id = ?");
                    $counts->execute([$user_id]);
                }
                $content_counts = $counts->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'approved'=>0,'pending'=>0,'rejected'=>0];

                // Engagement totals (guard with try/catch)
                $total_views = 0; $total_likes = 0; $total_comments = 0; $followers_count = 0;
                try {
                    if ($is_admin) {
                        $viewsStmt = $pdo->prepare("SELECT COUNT(*) FROM engagements e JOIN content c ON c.id=e.content_id WHERE e.type='view'");
                        $likesStmt = $pdo->prepare("SELECT COUNT(*) FROM engagements e JOIN content c ON c.id=e.content_id WHERE e.type='like'");
                        $commentsStmt = $pdo->prepare("SELECT COUNT(*) FROM comments");
                        $viewsStmt->execute();
                        $likesStmt->execute();
                        $commentsStmt->execute();
                    } else {
                        $viewsStmt = $pdo->prepare("SELECT COUNT(*) FROM engagements e JOIN content c ON c.id=e.content_id WHERE c.user_id=? AND e.type='view'");
                        $likesStmt = $pdo->prepare("SELECT COUNT(*) FROM engagements e JOIN content c ON c.id=e.content_id WHERE c.user_id=? AND e.type='like'");
                        $commentsStmt = $pdo->prepare("SELECT COUNT(*) FROM comments cm JOIN content c ON cm.content_id = c.id WHERE c.user_id = ?");
                        $viewsStmt->execute([$user_id]);
                        $likesStmt->execute([$user_id]);
                        $commentsStmt->execute([$user_id]);
                    }
                    $total_views = (int)$viewsStmt->fetchColumn();
                    $total_likes = (int)$likesStmt->fetchColumn();
                    $total_comments = (int)$commentsStmt->fetchColumn();
                } catch (Throwable $e) { /* default zeros */ }

                // Followers count (support both followed_id and creator_id schemas)
                try {
                    $useCreatorCol = 'followed_id';
                    try {
                        $chk = $pdo->query("SHOW COLUMNS FROM followers LIKE 'followed_id'");
                        if (!$chk->fetch()) { $useCreatorCol = 'creator_id'; }
                    } catch (Throwable $e2) { $useCreatorCol = 'creator_id'; }
                    if ($is_admin) {
                        $followersStmt = $pdo->prepare("SELECT COUNT(*) FROM followers");
                        $followersStmt->execute();
                    } else {
                        $followersStmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE {$useCreatorCol} = ?");
                        $followersStmt->execute([$user_id]);
                    }
                    $followers_count = (int)$followersStmt->fetchColumn();
                } catch (Throwable $e) { $followers_count = 0; }

                // Recent content (last 5)
                if ($is_admin) {
                    $recent = $pdo->prepare("SELECT c.id, c.title, c.category, c.status, c.created_at, u.display_name as creator_name FROM content c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 5");
                    $recent->execute();
                } else {
                    $recent = $pdo->prepare("SELECT id, title, category, status, created_at FROM content WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
                    $recent->execute([$user_id]);
                }
                $recent_items = $recent->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'data' => [
                        'content_counts' => [
                            'total' => (int)$content_counts['total'],
                            'approved' => (int)$content_counts['approved'],
                            'pending' => (int)$content_counts['pending'],
                            'rejected' => (int)$content_counts['rejected']
                        ],
                        'engagement' => [
                            'views' => $total_views,
                            'likes' => $total_likes,
                            'comments' => $total_comments,
                            'followers' => $followers_count
                        ],
                        'recent' => $recent_items,
                        'platform' => [
                            'max_upload_size_mb' => (int)$platform_settings['max_upload_size_mb'],
                            // reflect the effective flag after considering moderation_auto_actions
                            'auto_approve' => (bool)$auto_approve,
                            'require_creator_approval' => (bool)$platform_settings['require_creator_approval']
                        ]
                    ]
                ]);
                break;
            }

            // Get content list with filtering
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause based on filters
            if ($is_admin) {
                $where_conditions = ["1=1"]; // Admin can see all content
                $params = [];
            } else {
                if ($is_creator) {
                    $where_conditions = ["c.user_id = ?"];
                    $params = [$user_id];
                } else {
                    // Viewer: only approved content
                    $where_conditions = ["c.status = 'approved'"];
                    $params = [];
                }
            }
            
            // Search filter
            if (!empty($_GET['search'])) {
                $where_conditions[] = "(c.title LIKE ? OR c.description LIKE ?)";
                $search_term = '%' . $_GET['search'] . '%';
                $params[] = $search_term;
                $params[] = $search_term;
            }
            
            // Category filter
            if (!empty($_GET['category'])) {
                $where_conditions[] = "c.category = ?";
                $params[] = $_GET['category'];
            }
            
            // Status filter
            if (!empty($_GET['status'])) {
                $where_conditions[] = "c.status = ?";
                $params[] = $_GET['status'];
            }
            
            // Sort order
            $order_by = "c.created_at DESC";
            if (!empty($_GET['sort'])) {
                switch ($_GET['sort']) {
                    case 'created_asc':
                        $order_by = "c.created_at ASC";
                        break;
                    case 'created_desc':
                        $order_by = "c.created_at DESC";
                        break;
                    case 'title_asc':
                        $order_by = "c.title ASC";
                        break;
                    case 'title_desc':
                        $order_by = "c.title DESC";
                        break;
                    case 'views_asc':
                        $order_by = "view_count ASC";
                        break;
                    case 'views_desc':
                        $order_by = "view_count DESC";
                        break;
                    case 'likes_asc':
                        $order_by = "like_count ASC";
                        break;
                    case 'likes_desc':
                        $order_by = "like_count DESC";
                        break;
                }
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            if ($is_admin) {
                $content_stmt = $pdo->prepare("
                    SELECT 
                        c.*,
                        u.display_name as creator_name,
                        u.email as creator_email,
                        (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'view') as view_count,
                        (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'like') as like_count,
                        (SELECT COUNT(*) FROM comments WHERE content_id = c.id) as comment_count
                    FROM content c
                    LEFT JOIN users u ON c.user_id = u.id
                    WHERE {$where_clause}
                    ORDER BY {$order_by}
                    LIMIT ? OFFSET ?
                ");
            } else {
                if ($is_creator) {
                    $content_stmt = $pdo->prepare("
                    SELECT 
                        c.*,
                        (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'view') as view_count,
                        (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'like') as like_count,
                        (SELECT COUNT(*) FROM comments WHERE content_id = c.id) as comment_count
                    FROM content c
                    WHERE {$where_clause}
                    ORDER BY {$order_by}
                    LIMIT ? OFFSET ?
                ");
                } else {
                    // Viewer: include creator name via join
                    $content_stmt = $pdo->prepare("
                    SELECT 
                        c.*,
                        u.display_name as creator_name,
                        (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'view') as view_count,
                        (SELECT COUNT(*) FROM engagements WHERE content_id = c.id AND type = 'like') as like_count,
                        (SELECT COUNT(*) FROM comments WHERE content_id = c.id) as comment_count
                    FROM content c
                    LEFT JOIN users u ON c.user_id = u.id
                    WHERE {$where_clause}
                    ORDER BY {$order_by}
                    LIMIT ? OFFSET ?
                ");
                }
            }
            
            $params[] = $limit;
            $params[] = $offset;
            $content_stmt->execute($params);
            $content_list = $content_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count with same filters
            if ($is_admin) {
                $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM content c LEFT JOIN users u ON c.user_id = u.id WHERE {$where_clause}");
            } else {
                $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM content c WHERE {$where_clause}");
            }
            $count_params = array_slice($params, 0, -2); // Remove limit and offset
            $count_stmt->execute($count_params);
            $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'content' => $content_list,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => (int)$total_count,
                        'total_pages' => ceil($total_count / $limit)
                    ]
                ]
            ]);
            break;
            
        case 'POST':
            // Upload new content (creators only)
            if (!$is_admin && $_SESSION['role'] !== 'creator') {
                http_response_code(403);
                echo json_encode(['error' => 'Only creators can upload content']);
                exit;
            }
            
            if (!isset($_POST['title']) || !isset($_FILES['media'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Title and media file are required']);
                exit;
            }
            
            $title = trim($_POST['title']);
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $category = isset($_POST['category']) ? trim($_POST['category']) : 'general';
            $tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
            $thumbnail_url = isset($_POST['thumbnail_url']) ? trim($_POST['thumbnail_url']) : null;
            $ownership_note = isset($_POST['ownership_note']) ? trim($_POST['ownership_note']) : null;
            
            // Validate file
            $file = $_FILES['media'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['error' => 'File upload failed']);
                exit;
            }
            
            if ($file['size'] > $max_upload_size) {
                http_response_code(400);
                echo json_encode(['error' => "File size exceeds maximum allowed size of {$platform_settings['max_upload_size_mb']}MB"]);
                exit;
            }
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'audio/mpeg', 'audio/mp3', 'audio/mp4', 'audio/ogg', 'audio/wav', 'audio/webm'];
            if (!in_array($file['type'], $allowed_types)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid file type. Only images, videos, and audio files are allowed']);
                exit;
            }
            
            // Create upload directory if it doesn't exist
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to save uploaded file']);
                exit;
            }
            
            // Determine content status based on admin settings
            $status = $auto_approve ? 'approved' : 'pending';
            
            // Insert content into database
            $insert_stmt = $pdo->prepare("
                INSERT INTO content (
                    user_id, title, description, category, tags, 
                    media_url, file_path, file_size, file_type, original_filename,
                    thumbnail_url, status, ownership_note, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $media_url = '/kabaka/public/uploads/' . $filename;
            $insert_stmt->execute([
                $user_id, $title, $description, $category, $tags,
                $media_url, $file_path, $file['size'], $file['type'], $file['name'],
                $thumbnail_url, $status, $ownership_note
            ]);
            
            $content_id = $pdo->lastInsertId();
            
            // Create notification if content is pending
            if ($status === 'pending') {
                $notification_payload = json_encode([
                    'title' => 'Content Pending Review',
                    'message' => "Your content \"{$title}\" is pending admin approval.",
                    'related_id' => $content_id
                ]);
                $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, payload) VALUES (?, 'content_pending', ?)");
                $notification_stmt->execute([$user_id, $notification_payload]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $status === 'approved' ? 'Content uploaded and approved!' : 'Content uploaded and pending approval!',
                'content_id' => $content_id,
                'status' => $status
            ]);
            break;
            
        case 'PUT':
            // Update content (creators only)
            if (!$is_admin && $_SESSION['role'] !== 'creator') {
                http_response_code(403);
                echo json_encode(['error' => 'Only creators can update content']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Content ID is required']);
                exit;
            }
            
            $content_id = (int)$input['id'];
            
            // Verify content belongs to user
            $verify_stmt = $pdo->prepare("SELECT id FROM content WHERE id = ? AND user_id = ?");
            $verify_stmt->execute([$content_id, $user_id]);
            if (!$verify_stmt->fetch()) {
                http_response_code(403);
                echo json_encode(['error' => 'Content not found or access denied']);
                exit;
            }
            
            // Update fields
            $update_fields = [];
            $update_values = [];
            
            if (isset($input['title'])) {
                $update_fields[] = 'title = ?';
                $update_values[] = trim($input['title']);
            }
            
            if (isset($input['description'])) {
                $update_fields[] = 'description = ?';
                $update_values[] = trim($input['description']);
            }
            
            if (isset($input['category'])) {
                $update_fields[] = 'category = ?';
                $update_values[] = trim($input['category']);
            }
            
            if (isset($input['tags'])) {
                $update_fields[] = 'tags = ?';
                $update_values[] = trim($input['tags']);
            }

            if (isset($input['thumbnail_url'])) {
                $update_fields[] = 'thumbnail_url = ?';
                $update_values[] = trim($input['thumbnail_url']);
            }

            if (isset($input['ownership_note'])) {
                $update_fields[] = 'ownership_note = ?';
                $update_values[] = trim($input['ownership_note']);
            }
            
            if (empty($update_fields)) {
                http_response_code(400);
                echo json_encode(['error' => 'No fields to update']);
                exit;
            }
            
            $update_values[] = $content_id;
            $update_sql = "UPDATE content SET " . implode(', ', $update_fields) . " WHERE id = ? AND user_id = ?";
            $update_values[] = $user_id;
            
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute($update_values);
            
            echo json_encode([
                'success' => true,
                'message' => 'Content updated successfully!'
            ]);
            break;
            
        case 'DELETE':
            // Delete content (creators only)
            if (!$is_admin && $_SESSION['role'] !== 'creator') {
                http_response_code(403);
                echo json_encode(['error' => 'Only creators can delete content']);
                exit;
            }
            
            $content_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$content_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Content ID is required']);
                exit;
            }
            
            // Get content info for file deletion
            $content_stmt = $pdo->prepare("SELECT file_path FROM content WHERE id = ? AND user_id = ?");
            $content_stmt->execute([$content_id, $user_id]);
            $content = $content_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$content) {
                http_response_code(403);
                echo json_encode(['error' => 'Content not found or access denied']);
                exit;
            }
            
            // Delete file from server
            if ($content['file_path'] && file_exists($content['file_path'])) {
                unlink($content['file_path']);
            }
            
            // Delete from database
            $delete_stmt = $pdo->prepare("DELETE FROM content WHERE id = ? AND user_id = ?");
            $delete_stmt->execute([$content_id, $user_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Content deleted successfully!'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}