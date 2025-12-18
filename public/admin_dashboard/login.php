<?php
session_start();

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['uid']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Kabaka</title>
    <link rel="icon" type="image/svg+xml" href="/kabaka/public/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: #1a1a1a;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            position: relative;
            background: #1f1f1f;
            -webkit-backdrop-filter: blur(16px);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 20px;
            padding: 2.25rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,.4);
            overflow: hidden;
        }
        .login-container::before {
            content: "";
            position: absolute; inset: auto auto 18% -20%;
            width: 300px; height: 300px;
            background: radial-gradient(closest-side, rgba(220,38,38,.28), transparent 65%);
            filter: blur(10px);
        }
        .login-container::after {
            content: "";
            position: absolute; inset: 8% -15% auto auto;
            width: 260px; height: 260px;
            background: radial-gradient(closest-side, rgba(220,38,38,.2), transparent 65%);
            filter: blur(12px);
        }
        
        .admin-icon {
            width: 92px;
            height: 92px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2.2rem;
            color: white;
            box-shadow: 0 8px 32px rgba(220,38,38,.30);
            border: 3px solid rgba(255,255,255,.18);
        }
        
        .form-control {
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 12px;
            color: white;
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus {
            background: rgba(255,255,255,.14);
            border-color: #dc2626;
            box-shadow: 0 0 0 0.2rem rgba(220,38,38,.25);
            color: white;
        }
        
        .form-control::placeholder { color: rgba(255,255,255,.65); }
        .input-group-text {
            background: transparent;
            border: 1px solid rgba(255,255,255,.22);
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #e5e7eb;
        }
        .input-group .form-control { border-left: none; border-radius: 0 12px 12px 0; }
        
        .btn-admin {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border: none;
            border-radius: 12px;
            padding: 0.8rem 1.5rem;
            font-weight: 700;
            letter-spacing: .2px;
            transition: all 0.25s ease;
        }
        .btn-admin:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(220,38,38,.32); }
        .btn-admin:active { transform: translateY(0); }
        
        .alert {
            background: rgba(220,38,38,.10);
            border: 1px solid rgba(220,38,38,.35);
            border-radius: 12px;
            color: #fca5a5;
        }
        
        .text-secondary { color: rgba(255,255,255,.7) !important; }
        a.text-secondary:hover { color: #fff !important; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="admin-icon">
            <i class="bi bi-shield-check"></i>
        </div>
        
        <h2 class="text-white text-center mb-3 fw-bold">Admin Login</h2>
        <p class="text-white text-center mb-4">Enter your admin credentials to continue</p>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login_process.php">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" name="email" class="form-control" placeholder="admin@example.com" required>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-admin w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login as Admin
            </button>
        </form>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
