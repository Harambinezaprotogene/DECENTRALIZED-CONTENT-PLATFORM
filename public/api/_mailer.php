<?php
// Mailer helper using PHPMailer from lib with env-configured SMTP.
// Falls back to PHP mail() if SMTP not configured.

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require_once __DIR__ . '/../../lib/PHPMailer-6.8.1/src/Exception.php';
require_once __DIR__ . '/../../lib/PHPMailer-6.8.1/src/PHPMailer.php';
require_once __DIR__ . '/../../lib/PHPMailer-6.8.1/src/SMTP.php';

if (!function_exists('send_app_mail')) {
    function send_app_mail(string $to, string $subject, string $htmlBody, string $textBody = ''): bool {
        $enabled = getenv('MAIL_ENABLED') ?: '0';
        if ($enabled !== '1') { return false; }

        $from = getenv('MAIL_FROM') ?: 'no-reply@example.com';
        $fromName = getenv('MAIL_FROM_NAME') ?: 'Creator Portal';

        $smtpHost = getenv('SMTP_HOST') ?: '';
        $smtpPort = (int)(getenv('SMTP_PORT') ?: 0);
        $smtpUser = getenv('SMTP_USER') ?: '';
        $smtpPass = getenv('SMTP_PASS') ?: '';
        $smtpSecure = getenv('SMTP_SECURE') ?: 'tls'; // tls|ssl|empty

        // Use PHPMailer
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            if ((getenv('SMTP_DEBUG') ?: '0') === '1') {
                $mail->SMTPDebug = 2; // verbose
                $mail->Debugoutput = function($str) { error_log('SMTP: '.trim($str)); };
            }

            if ($smtpHost) {
                $mail->isSMTP();
                $mail->Host = $smtpHost;
                if ($smtpPort > 0) { $mail->Port = $smtpPort; }
                if ($smtpUser !== '') {
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtpUser;
                    $mail->Password = $smtpPass;
                }
                if ($smtpSecure === 'ssl') { $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; }
                elseif ($smtpSecure === 'tls') { $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; }
            }

            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody ?: nl2br($textBody);
            if ($textBody) { $mail->AltBody = $textBody; }

            return $mail->send();
        } catch (Exception $e) {
            // Fallback to mail() if PHPMailer fails
            $headers = [];
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
            $headers[] = 'From: '.$from;
            $headers[] = 'Reply-To: '.$from;
            $headers[] = 'X-Mailer: PHP/'.phpversion();
            $headersStr = implode("\r\n", $headers);
            return (bool)@mail($to, $subject, $htmlBody ?: ($textBody ?: ''), $headersStr);
        }
    }
}


