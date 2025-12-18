<?php
session_start();
require_once '../../config/env.php';
require_once '../../config/db.php';

EnvLoader::load(__DIR__ . '/../../.env');
$pdo = DatabaseConnectionFactory::createConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$display_name = trim($_POST['display_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($display_name)) {
    $errors[] = 'Display name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

// Check if email already exists
if (empty($errors)) {
    try {
        $stmt = $pdo->prepare('SELECT id FROM viewers WHERE email = ?');
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered';
        }
    } catch (Exception $e) {
        $errors[] = 'Database error occurred';
    }
}

// If there are errors, redirect back with error
if (!empty($errors)) {
    $error_msg = implode(', ', $errors);
    header("Location: register.php?error=" . urlencode($error_msg));
    exit;
}

// Create viewer account
try {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare('INSERT INTO viewers (display_name, email, password, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$display_name, $email, $hashed_password]);
    
    $viewer_id = $pdo->lastInsertId();
    
    // Set session
    $_SESSION['viewer_id'] = $viewer_id;
    $_SESSION['viewer_name'] = $display_name;
    $_SESSION['viewer_email'] = $email;
    
    // Redirect to dashboard
    header('Location: dashboard.php');
    exit;
    
} catch (Exception $e) {
    header('Location: register.php?error=Registration failed. Please try again.');
    exit;
}
?>
