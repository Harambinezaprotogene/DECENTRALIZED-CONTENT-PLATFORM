<?php
require_once __DIR__ . '/_bootstrap.php';

// Simple authenticated test endpoint to verify email delivery
// Usage: /kabaka/public/api/mail_test.php?to=you@example.com

if (!isset($_SESSION['uid'])) {
    json_response(['error' => 'Auth required'], 401);
}

$to = trim($_GET['to'] ?? '');
if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    json_response(['error' => 'Provide a valid ?to=email@example.com'], 422);
}

$subj = 'Test email from Creator Portal';
$html = '<p>This is a test email to confirm SMTP configuration works.</p>';
$ok = send_app_mail($to, $subj, $html, strip_tags($html));

if ($ok) {
    json_response(['ok' => true]);
} else {
    json_response(['error' => 'Mail send failed. Check MAIL_ENABLED/SMTP_* env and server logs.'], 500);
}


