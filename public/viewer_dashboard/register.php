<?php
session_start();
if (isset($_SESSION['viewer_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Kabaka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--accent:#2563eb}
        body{min-height:100vh;background:#1a1a1a;color:#f8fafc;font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
        .card{position:relative;background:#1f1f1f;-webkit-backdrop-filter: blur(16px);backdrop-filter: blur(16px);border:1px solid rgba(255,255,255,.08);border-radius:22px;box-shadow:0 18px 44px rgba(15,23,42,.25);overflow:hidden;max-width:420px;margin:0 auto}
        .card:before{content:"";position:absolute;inset:auto auto 20% -20%;width:300px;height:300px;background:radial-gradient(closest-side, rgba(220,38,38,.25), transparent 65%);filter:blur(10px);}
        .card:after{content:"";position:absolute;inset:10% -15% auto auto;width:260px;height:260px;background:radial-gradient(closest-side, rgba(220,38,38,.18), transparent 65%);filter:blur(12px);}
        .auth-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#dc2626,#b91c1c);display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.2rem;margin:0 auto 1rem;box-shadow:0 12px 28px rgba(220,38,38,.35);border:3px solid rgba(255,255,255,.18)}
        .card > *{position:relative;z-index:1}
        .form-control,.form-select{background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.22);color:#ffffff;border-radius:12px;height:48px}
        .form-control::placeholder{color:rgba(255,255,255,.65)}
        .form-control:focus,.form-select:focus{background:rgba(255,255,255,.14);border-color:#dc2626;box-shadow:0 0 0 .2rem rgba(220,38,38,.25)}
        .form-control:focus::placeholder{color:#e5e7eb}
        .input-group-text{display:flex;align-items:center;justify-content:center;width:48px;min-width:48px;background:transparent;border:1px solid rgba(255,255,255,.22);border-right:0;border-top-left-radius:12px;border-bottom-left-radius:12px;color:#e5e7eb}
        .input-group>.form-control{border-left:0;border-top-right-radius:12px;border-bottom-right-radius:12px;height:48px}
        .btn-primary{background:linear-gradient(135deg,#dc2626,#b91c1c);border:0;border-radius:12px;box-shadow:0 6px 16px rgba(220,38,38,.35)}
        .btn-primary:hover{filter:brightness(1.03)}
        .small-link{color:#cbd5e1}
        .small-link a{color:#93c5fd;text-decoration:none}
        .small-link a:hover{text-decoration:underline}
        .header-link{color:#0f172a;text-decoration:none;border-radius:12px;padding:.25rem .5rem;transition:background .15s ease}
        .header-link:hover{background:#f1f5f9}
        .card-header-lite{display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem;color:#cbd5e1;font-weight:600}
        .card-header-lite i{color:#93c5fd}
        .alert{background:#1f2937;border:1px solid rgba(255,255,255,.1);color:#ffffff;border-radius:12px}
        .alert-danger{background:#1f2937;border-color:#ef4444;color:#fca5a5}
        .alert-success{background:#1f2937;border-color:#10b981;color:#a7f3d0}
        .form-check-input{background:#1f2937;border:1px solid rgba(255,255,255,.1)}
        .form-check-input:checked{background-color:#60a5fa;border-color:#60a5fa}
        .form-check-label{color:#cbd5e1}
        .form-check-label a{color:#93c5fd;text-decoration:none}
        .form-check-label a:hover{text-decoration:underline}
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="card p-4 p-lg-5">
                    <div class="auth-icon"><i class="bi bi-person-plus"></i></div>
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                        <span class="text-uppercase small" style="letter-spacing:.08em;color:#cbd5e1">Sign up</span>
                    </div>
                    <h2 class="h3 fw-bold text-light mb-3 text-center">Viewer Portal</h2>
                    <p class="mb-4 text-secondary text-center">Create your account to start discovering and engaging with content.</p>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger mb-3">
                            <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success mb-3">
                            <?= htmlspecialchars($_GET['success']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="registerForm" method="POST" action="register_process.php" class="vstack gap-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" id="display_name" name="display_name" class="form-control" placeholder="Display Name" required>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Password (min 6)" minlength="6" required>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm Password" minlength="6" required>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#">Terms</a> and <a href="#">Policy</a>
                            </label>
                        </div>
                        
                        <button class="btn btn-primary w-100" type="submit">Continue</button>
                    </form>
                    
                    <div class="mt-3 small small-link">Have an account? <a href="login.php">Login</a></div>
                    <div class="mt-2 small small-link">
                        <a href="/kabaka/public/" style="color:#94a3b8">
                            <i class="bi bi-arrow-left me-1"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>
</html>

