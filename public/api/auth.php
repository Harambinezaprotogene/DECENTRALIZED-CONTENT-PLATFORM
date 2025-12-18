<?php
require_once __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($action === 'register' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $display_name = trim($input['display_name'] ?? '');
    $role = in_array(($input['role'] ?? 'viewer'), ['viewer','creator']) ? $input['role'] : 'viewer';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        json_response(['error' => 'Invalid email or password'], 422);
    }
    
    if (empty($display_name)) {
        json_response(['error' => 'Display name is required'], 422);
    }
    
    try {
        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, display_name) VALUES (?, ?, ?, ?)');
        $stmt->execute([$email, password_hash($password, PASSWORD_BCRYPT), $role, $display_name]);
        $userId = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO wallets (user_id) VALUES (?)')->execute([$userId]);
        $_SESSION['uid'] = $userId;
        $_SESSION['role'] = $role;
        $_SESSION['display_name'] = $display_name;
        json_response(['ok' => true, 'user_id' => $userId, 'role' => $role, 'display_name' => $display_name]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) === 1062) json_response(['error' => 'Email already registered'], 409);
        json_response(['error' => 'Server error'], 500);
    }
}

if ($action === 'login' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id, password_hash, role, display_name FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) json_response(['error' => 'Invalid credentials'], 401);
    $_SESSION['uid'] = (int)$user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['display_name'] = $user['display_name'];
    json_response(['ok' => true, 'user' => [ 'id' => (int)$user['id'], 'email' => $email, 'role' => $user['role'], 'display_name' => $user['display_name'] ]]);
}

if ($action === 'me' && $method === 'GET') {
    if (!isset($_SESSION['uid'])) json_response(['user' => null]);
    $stmt = $pdo->prepare('SELECT id, email, role, display_name FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['uid']]);
    $user = $stmt->fetch();
    json_response(['user' => $user ?: null]);
}

if ($action === 'change_password' && $method === 'POST') {
    if (!isset($_SESSION['uid'])) json_response(['error' => 'Auth required'], 401);
    
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $currentPassword = $input['current_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword)) {
        json_response(['error' => 'Both passwords are required'], 422);
    }
    
    if (strlen($newPassword) < 6) {
        json_response(['error' => 'New password must be at least 6 characters'], 422);
    }
    
    // Verify current password
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['uid']]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        json_response(['error' => 'Current password is incorrect'], 401);
    }
    
    // Update password
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $stmt->execute([$newHash, $_SESSION['uid']]);
    // emit notification
    try {
        $pdo->prepare('INSERT INTO notifications (user_id, type, payload) VALUES (?, ?, ?)')
            ->execute([$_SESSION['uid'], 'password_changed', json_encode(['time'=>time()])]);
    } catch (Exception $e) {}
    
    // Send confirmation email
    try {
        $meStmt = $pdo->prepare('SELECT email, display_name FROM users WHERE id = ?');
        $meStmt->execute([$_SESSION['uid']]);
        $me = $meStmt->fetch();
        if ($me && !empty($me['email'])) {
            $name = $me['display_name'] ?: $me['email'];
            send_app_mail($me['email'], 'Password changed', '<p>Hi '.htmlspecialchars($name).',</p><p>Your password was changed successfully. If this wasn\'t you, please contact support immediately.</p>');
        }
    } catch (Exception $e) {}

    json_response(['ok' => true, 'message' => 'Password updated successfully']);
}

if ($action === 'logout' && in_array($method, ['POST','GET'], true)) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    
    // Redirect to login page instead of just returning JSON
    header('Location: /kabaka/public/viewer_dashboard/login.php');
    exit;
}

json_response(['error' => 'Not Found'], 404);



