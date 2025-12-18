<?php
session_start();

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');



if (empty($email) || empty($password)) {
    header('Location: login.php?error=Email and password are required');
    exit;
}

try {
    // Direct database connection (same as working test scripts)
    $host = 'localhost';
    $dbname = 'kabaka';
    $username = 'root';
    $db_password = '';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if user exists and is admin
    $stmt = $pdo->prepare('SELECT id, email, password_hash, role, display_name FROM users WHERE email = ? AND role = ?');
    $stmt->execute([$email, 'admin']);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: login.php?error=Invalid admin credentials');
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        header('Location: login.php?error=Invalid admin credentials');
        exit;
    }
    
    // Set admin session
    $_SESSION['uid'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['display_name'] = $user['display_name'];
    
    // Redirect to admin dashboard
    header('Location: dashboard.php');
    exit;
    
} catch (Exception $e) {
    error_log('Admin login error: ' . $e->getMessage());
    header('Location: login.php?error=Login failed. Please try again.');
    exit;
}
?>
