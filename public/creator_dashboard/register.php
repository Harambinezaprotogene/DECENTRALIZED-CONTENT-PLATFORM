<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Registration - Kabaka</title>
    <link rel="icon" type="image/svg+xml" href="/kabaka/public/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .auth-card { position: relative; background: #1f1f1f; -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px); border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,.4); border: 1px solid rgba(255,255,255,.15); overflow: hidden; width: 100%; max-width: 420px; padding: 2.2rem; }
        .auth-card::before { content: ""; position: absolute; inset: auto auto 18% -20%; width: 300px; height: 300px; background: radial-gradient(closest-side, rgba(220,38,38,.28), transparent 65%); filter: blur(10px); }
        .auth-card::after { content: ""; position: absolute; inset: 8% -15% auto auto; width: 260px; height: 260px; background: radial-gradient(closest-side, rgba(220,38,38,.2), transparent 65%); filter: blur(12px); }
        .auth-header { text-align: center; margin-bottom: 1.25rem; }
        .creator-icon { width: 110px; height: 110px; border-radius: 50%; background: linear-gradient(135deg, #dc2626, #b91c1c); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 2.4rem; margin: 0 auto 1rem; box-shadow: 0 12px 28px rgba(220,38,38,.35); border: 3px solid rgba(255,255,255,.18); }
        .form-control { background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.22); border-radius: 12px; color: #fff; padding: 0.75rem 1rem; }
        .form-control:focus { background: rgba(255,255,255,.14); border-color: #dc2626; box-shadow: 0 0 0 0.2rem rgba(220,38,38,.25); color: #fff; }
        .form-control::placeholder { color: rgba(255,255,255,.65); }
        .input-group-text { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.22); border-right: none; border-radius: 12px 0 0 12px; color: #e5e7eb; }
        .input-group .form-control { border-left: none; border-radius: 0 12px 12px 0; }
        .btn-primary { background: linear-gradient(135deg, #dc2626, #b91c1c); border: none; border-radius: 12px; padding: 0.8rem; font-weight: 700; letter-spacing: .2px; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(220,38,38,.32); }
        .alert { border-radius: 12px; border: 1px solid rgba(220,38,38,.35); background: rgba(220,38,38,.10); color: #fca5a5; }
        .muted a { color: #60a5fa; text-decoration: none; }
        .muted a:hover { text-decoration: underline; }
        label { color: #fff; }
        .text-secondary { color: rgba(255,255,255,.7) !important; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="auth-card">
                    <div class="auth-header">
                        <div class="creator-icon"><i class="bi bi-person-plus"></i></div>
                        <h2 class="mb-0 text-white fw-bold">Creator Registration</h2>
                        <p class="mb-0 mt-2 text-secondary">Join Kabaka as a content creator</p>
                    </div>
                    <div class="auth-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($_GET['error']) ?>
                            </div>
                        <?php endif; ?>
                        <form id="registerForm" class="vstack gap-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" id="display_name" name="display_name" class="form-control" placeholder="Display Name" required>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control" placeholder="Email Address" required>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Password (min 6 characters)" minlength="6" required>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm Password" minlength="6" required>
                            </div>
                            <div class="form-check">
                               
                                
                            </div>
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-person-plus me-2"></i>Create Creator Account
                            </button>
                        </form>
                        <div class="text-center mt-4">
                            <p class="muted mb-0 text-secondary">Already have an account? <a href="login.php">Sign in here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const password = formData.get('password');
            const confirmPassword = formData.get('confirm_password');
            if (password !== confirmPassword) { alert('Passwords do not match!'); return; }
            if (password.length < 6) { alert('Password must be at least 6 characters long!'); return; }
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creating Account...';
            submitBtn.disabled = true;
            try {
                const response = await fetch('/kabaka/public/api/auth.php?action=register', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: formData.get('email'), password: password, role: 'creator', display_name: formData.get('display_name') })
                });
                const result = await response.json();
                if (response.ok && result.ok) {
                    submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Account Created!';
                    setTimeout(() => { window.location.href = 'dashboard.php'; }, 800);
                } else {
                    alert(result.error || 'Registration failed. Please try again.');
                    submitBtn.innerHTML = originalText; submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Registration error:', error);
                alert('Network error. Please check your connection and try again.');
                submitBtn.innerHTML = originalText; submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
