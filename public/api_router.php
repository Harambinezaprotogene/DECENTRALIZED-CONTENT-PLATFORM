<?php
session_start();
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/api/maintenance_check.php';

EnvLoader::load(__DIR__ . '/../.env');
$pdo = DatabaseConnectionFactory::createConnection();

// Check maintenance mode for all API requests (except admin login)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!str_contains($path, '/admin/login') && !str_contains($path, '/admin_dashboard/login')) {
    check_maintenance_mode_api();
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!function_exists('starts_with')) {
    function starts_with(string $haystack, string $needle): bool {
        return $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($scriptDir && $scriptDir !== '/') {
    if (starts_with($path, $scriptDir)) {
        $path = substr($path, strlen($scriptDir));
        if ($path === '') { $path = '/'; }
    }
}

// Check if this is an API request by looking at the rewrite rule
// When .htaccess rewrites /api/* to api_router.php, the original path is in $_SERVER['REQUEST_URI']
$originalUri = $_SERVER['REQUEST_URI'] ?? '';
$isApiRequest = strpos($originalUri, '/api/') !== false;

if ($isApiRequest) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    // Extract the API endpoint from the original URI
    $apiPath = substr($originalUri, strpos($originalUri, '/api/') + 5); // Remove '/api/' prefix
    
    // Remove query parameters if present
    $apiPath = strtok($apiPath, '?');
    
    $parts = explode('/', $apiPath);
    $endpoint = $parts[0];
    
    // Check if endpoint already has .php extension
    if (strpos($endpoint, '.php') !== false) {
        $file = __DIR__ . '/api/' . $endpoint;
    } else {
        $file = __DIR__ . '/api/' . $endpoint . '.php';
    }
    
    if (is_file($file)) { 
        require $file; 
        exit; 
    }
    
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found: ' . $endpoint, 'file' => $file]);
    exit;
}

// If not an API request, redirect to viewer dashboard
header('Location: /kabaka/public/viewer_dashboard/dashboard.php');
exit;
?>
