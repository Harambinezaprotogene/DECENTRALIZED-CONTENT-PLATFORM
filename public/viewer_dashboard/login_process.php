<?php
session_start();
require_once '../../config/env.php';
require_once '../../config/db.php';

EnvLoader::load(__DIR__ . '/../../.env');
$pdo = DatabaseConnectionFactory::createConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($email) || empty($password)) {
    header('Location: login.php?error=Email and password are required');
    exit;
}

try {
    // Get viewer by email
    $stmt = $pdo->prepare('SELECT id, display_name, email, password FROM viewers WHERE email = ?');
    $stmt->execute([$email]);
    $viewer = $stmt->fetch();
    
    if (!$viewer) {
        header('Location: login.php?error=Invalid email or password');
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $viewer['password'])) {
        header('Location: login.php?error=Invalid email or password');
        exit;
    }
    
    // Set session
    $_SESSION['viewer_id'] = $viewer['id'];
    $_SESSION['viewer_name'] = $viewer['display_name'];
    $_SESSION['viewer_email'] = $viewer['email'];
    
    // Redirect to dashboard
    header('Location: dashboard.php');
    exit;
    
} catch (Exception $e) {
    header('Location: login.php?error=Login failed. Please try again.');
    exit;
}
?>
