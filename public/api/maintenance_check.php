<?php
require_once __DIR__ . '/_bootstrap.php';

/**
 * Check if maintenance mode is enabled
 * Returns true if maintenance mode is ON and user is not admin
 */
function is_maintenance_mode() {
    // Don't use global $pdo to avoid connection issues
    try {
        // Simple database connection for maintenance check only
        $host = 'localhost';
        $dbname = 'kabaka';
        $username = 'root';
        $password = '';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query('SELECT maintenance_mode FROM platform_settings ORDER BY id ASC LIMIT 1');
        $settings = $stmt->fetch();
        
        if ($settings && $settings['maintenance_mode']) {
            // Check if current user is admin (from any system)
            $isAdmin = (isset($_SESSION['uid']) && $_SESSION['role'] === 'admin') || 
                      (isset($_SESSION['viewer_id']) && isset($_SESSION['admin_override'])); // Special case for admin testing
            
            if (!$isAdmin) {
                return true; // Maintenance mode is ON and user is not admin
            }
        }
        
        return false; // Maintenance mode is OFF or user is admin
    } catch (Exception $e) {
        // If any error, assume maintenance mode is OFF (fail safe)
        return false;
    }
}

/**
 * Show maintenance page and exit
 */
function show_maintenance_page() {
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Site Under Maintenance</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
            .maintenance-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); }
        </style>
    </head>
    <body class="d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card maintenance-card shadow-lg border-0">
                        <div class="card-body text-center p-5">
                            <div class="mb-4">
                                <i class="bi bi-tools text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h1 class="card-title text-dark mb-3">🚧 Site Under Maintenance</h1>
                            <p class="card-text text-muted mb-4">
                                We're currently performing scheduled maintenance to improve your experience.
                                <br><br>
                                <strong>Expected completion:</strong> Soon<br>
                                <strong>For urgent matters:</strong> Contact support
                            </p>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Thank you for your patience!
                            </div>
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Check Again
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Check maintenance mode for API endpoints
 * Returns JSON error if maintenance mode is ON
 */
function check_maintenance_mode_api() {
    if (is_maintenance_mode()) {
        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Site is under maintenance',
            'code' => 'MAINTENANCE_MODE',
            'message' => 'Please try again later'
        ]);
        exit;
    }
}
?>
