<?php
// Simple debug script to check what's happening
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Script path: " . __FILE__ . "\n";

// Test if bootstrap exists
$bootstrap_path = __DIR__ . '/_bootstrap.php';
echo "Bootstrap path: " . $bootstrap_path . "\n";
echo "Bootstrap exists: " . (file_exists($bootstrap_path) ? 'YES' : 'NO') . "\n";

// Test if config files exist
$env_path = __DIR__ . '/../../config/env.php';
$db_path = __DIR__ . '/../../config/db.php';
echo "Config env exists: " . (file_exists($env_path) ? 'YES' : 'NO') . "\n";
echo "Config db exists: " . (file_exists($db_path) ? 'YES' : 'NO') . "\n";

// Test database connection
try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=kabaka', 'root', '');
    echo "Database connection: SUCCESS\n";
} catch (Exception $e) {
    echo "Database connection: FAILED - " . $e->getMessage() . "\n";
}

// Test session
echo "Session status: " . session_status() . "\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "Session started\n";
}
echo "Session ID: " . session_id() . "\n";
?>
