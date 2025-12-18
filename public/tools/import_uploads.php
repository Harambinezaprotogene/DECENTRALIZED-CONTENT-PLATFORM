<?php
session_start();
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

try { EnvLoader::load(__DIR__ . '/../../.env'); } catch (Exception $e) {}

function respond($data, int $status = 200): void {
	http_response_code($status);
	echo json_encode($data);
	exit;
}

// Auth: allow creators (uid) only
if (!isset($_SESSION['uid'])) {
	respond(['error' => 'Creator login required'], 401);
}

$pdo = DatabaseConnectionFactory::createConnection();

// Inputs
$uploadsDir = realpath(__DIR__ . '/../uploads');
if ($uploadsDir === false) { respond(['error' => 'Uploads directory not found'], 500); }

$assignUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)$_SESSION['uid'];
if ($assignUserId <= 0) { respond(['error' => 'Invalid user_id'], 422); }

$category = isset($_GET['category']) ? trim((string)$_GET['category']) : null;
$status = isset($_GET['status']) ? trim((string)$_GET['status']) : 'visible';
$dryRun = isset($_GET['dry_run']) ? (($_GET['dry_run'] === '1' || strtolower($_GET['dry_run']) === 'true') ? true : false) : false;

// Optional prefix filter (?prefix=foo_ to only import files starting with foo_)
$prefix = isset($_GET['prefix']) ? (string)$_GET['prefix'] : '';

$finfo = new finfo(FILEINFO_MIME_TYPE);

// Collect files (only regular files, skip dot files)
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadsDir, FilesystemIterator::SKIP_DOTS));

$files = [];
foreach ($rii as $file) {
	if (!$file->isFile()) { continue; }
	$relPath = str_replace($uploadsDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
	if ($prefix !== '' && strpos($relPath, $prefix) !== 0) { continue; }
	$files[] = $relPath;
}

if (empty($files)) { respond(['ok' => true, 'inserted' => 0, 'skipped' => 0, 'details' => [], 'message' => 'No files to import (check prefix or uploads content)']); }

// Prepare checks
$checkStmt = $pdo->prepare('SELECT id FROM content WHERE file_path = ? LIMIT 1');
$insertStmt = $pdo->prepare('INSERT INTO content (user_id, title, description, category, file_path, file_type, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');

$inserted = 0; $skipped = 0; $details = [];

foreach ($files as $relPath) {
	$abs = $uploadsDir . DIRECTORY_SEPARATOR . $relPath;
	if (!is_file($abs)) { $skipped++; $details[] = ['file' => $relPath, 'action' => 'skip', 'reason' => 'not_a_file']; continue; }

	// Already in DB?
	$checkStmt->execute([$relPath]);
	if ($checkStmt->fetch()) { $skipped++; $details[] = ['file' => $relPath, 'action' => 'skip', 'reason' => 'exists']; continue; }

	$mime = $finfo->file($abs) ?: 'application/octet-stream';
	$title = pathinfo($relPath, PATHINFO_FILENAME);
	$desc = '';
	$cat = $category ?: 'General';

	if ($dryRun) {
		$details[] = ['file' => $relPath, 'action' => 'would_insert', 'title' => $title, 'type' => $mime, 'category' => $cat, 'status' => $status];
		continue;
	}

	try {
		$insertStmt->execute([$assignUserId, $title, $desc, $cat, $relPath, $mime, $status]);
		$inserted++;
		$details[] = ['file' => $relPath, 'action' => 'inserted', 'id' => $pdo->lastInsertId()];
	} catch (Exception $e) {
		$skipped++;
		$details[] = ['file' => $relPath, 'action' => 'error', 'error' => $e->getMessage()];
	}
}

respond(['ok' => true, 'inserted' => $inserted, 'skipped' => $skipped, 'dry_run' => $dryRun, 'details' => $details]);

?>


