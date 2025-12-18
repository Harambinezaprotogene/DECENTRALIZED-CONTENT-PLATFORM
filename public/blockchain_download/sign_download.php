<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

$requireEnv = __DIR__ . '/../../config/env.php';
$requireDb  = __DIR__ . '/../../config/db.php';
if (file_exists($requireEnv)) require_once $requireEnv;
if (file_exists($requireDb)) require_once $requireDb;

$envLoaded = false;
if (class_exists('EnvLoader')) {
    try { EnvLoader::load(__DIR__ . '/../../.env'); $envLoaded = true; } catch (Throwable $e) {}
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
$contentId = isset($data['content_id']) ? (int)$data['content_id'] : 0;

if ($contentId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'content_id required']);
    exit;
}

// For now, just return a placeholder URL; later generate short-lived signed URL
echo json_encode([
    'ok' => true,
    'url' => null,
    'reason' => 'not_implemented'
]);
exit;


