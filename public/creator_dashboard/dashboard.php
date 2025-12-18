<?php
// Ensure session cookie is accessible from all paths
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
session_start();


// Restrict to creators (adjust later if roles change)
if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'creator') {
    header('Location: login.php');
    exit;
}

// Get user info from database
require_once '../../config/env.php';
require_once '../../config/db.php';

// Load environment variables from .env file
EnvLoader::load(__DIR__ . '/../../.env');

try {
    $pdo = DatabaseConnectionFactory::createConnection();
    $stmt = $pdo->prepare('SELECT display_name, email FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['uid']]);
    $user = $stmt->fetch();
    $user_name = $user['display_name'] ?? 'Creator';
    $user_email = $user['email'] ?? '';
} catch (Exception $e) {
    $user_name = 'Creator';
    $user_email = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Creator Dashboard</title>
	<link rel="icon" type="image/svg+xml" href="/kabaka/public/favicon.svg">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
	<style>
        :root {
            --bg1: #0a3757;
            --bg2: #5628a6;
            --bg3: #0d1321;
            --bg4: #0a0f1f;
            --accent: #22d3ee;
            --admin-red: #dc2626;
            --admin-red-dark: #b91c1c;
        }

        /* Toast Notifications - Easy UI */
        .toast-container {
			position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            max-width: 450px;
        }

        .toast {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            opacity: 0;
            transform: translateX(100%) scale(0.9);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .toast::before {
            content: '';
            position: absolute;
			top: 0;
			left: 0;
            width: 4px;
            height: 100%;
            background: var(--admin-red);
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0) scale(1);
        }

        .toast.success::before {
            background: #10b981;
        }

        .toast.error::before {
            background: #ef4444;
        }

        .toast.warning::before {
            background: #f59e0b;
        }

        .toast.info::before {
            background: #3b82f6;
        }

        .toast-header {
			display: flex;
            justify-content: space-between;
			align-items: center;
            margin-bottom: 8px;
        }

        .toast-title {
            font-weight: 600;
            font-size: 15px;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toast-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .toast.success .toast-icon {
            background: #10b981;
            color: white;
        }

        .toast.error .toast-icon {
            background: #ef4444;
            color: white;
        }

        .toast.warning .toast-icon {
            background: #f59e0b;
            color: white;
        }

        .toast.info .toast-icon {
            background: #3b82f6;
            color: white;
        }

        .toast-close {
            background: none;
            border: none;
			color: #6b7280;
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
            line-height: 1;
        }

        .toast-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: #374151;
        }

        .toast-message {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.4;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .toast-container {
                top: 70px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
            
            .toast {
                margin-bottom: 8px;
                padding: 14px 16px;
            }
        }
        
        body {
            min-height: 100vh;
            color: #f8fafc;
            background: #1a1a1a;
            padding-top: 24px;
            max-width: 1400px;
            margin: 0 auto;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .glass {
            background: rgba(255,255,255,.14);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(0,0,0,.35);
        }
        
        .navbar {
            background: rgba(220,38,38,.15);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(220,38,38,.3);
            padding: 1rem 0;
            border-radius: 0 0 20px 20px;
        }
        
        .navbar-brand {
            font-weight: 800;
            letter-spacing: .6px;
            color: white !important;
            font-size: 1.4rem;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, var(--admin-red), var(--admin-red-dark));
            border: 0;
            box-shadow: 0 3px 8px rgba(220,38,38,.15);
			font-weight: 600;
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            filter: brightness(1.06);
            box-shadow: 0 6px 16px rgba(220,38,38,.25);
        }
        
        .btn-outline-light {
            border: 2px solid rgba(255,255,255,.3);
			background: transparent;
            color: white;
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
			transition: all 0.3s ease;
		}
        
        .btn-outline-light:hover {
            background: rgba(255,255,255,.1);
            border-color: rgba(255,255,255,.5);
			transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255,255,255,.1);
        }
        
                 .admin-card {
             background: rgba(255,255,255,.08);
             border: 1px solid rgba(255,255,255,.15);
             border-radius: 20px;
             padding: 2rem;
             transition: all 0.3s ease;
             box-shadow: 0 8px 32px rgba(0,0,0,.2);
             height: 100%;
             display: flex;
             flex-direction: column;
         }
        
        .admin-card:hover {
            transform: translateY(-3px);
            background: rgba(255,255,255,.12);
            border-color: rgba(255,255,255,.25);
            box-shadow: 0 12px 48px rgba(0,0,0,.3);
        }
        
        .admin-card h5 {
            font-size: 1.3rem;
			font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(220,38,38,0.1), rgba(185,28,28,0.1));
            border: 1px solid rgba(220,38,38,0.2);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(220,38,38,.1);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(220,38,38,.2);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--admin-red);
            margin-bottom: 0.3rem;
            text-shadow: 0 2px 4px rgba(0,0,0,.3);
        }
        
        .stat-label {
            color: rgba(255,255,255,.8);
			font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* Sortable table headers */
        .sortable {
            user-select: none;
            transition: background-color 0.2s ease;
        }
        .sortable:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .sortable i {
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }
        .sortable:hover i {
            opacity: 1;
        }
        .sortable.active i {
            opacity: 1;
            color: var(--admin-red);
        }

        /* Table column widths - Professional compact layout */
        .table th:nth-child(1) { width: 30px; } /* Checkbox */
        .table th:nth-child(2) { width: 250px; text-align: left; } /* Title */
        .table th:nth-child(3) { width: 100px; } /* Category */
        .table th:nth-child(4) { width: 100px; } /* Status */
        .table th:nth-child(5) { width: 90px; } /* Views */
        .table th:nth-child(6) { width: 90px; } /* Likes */
        .table th:nth-child(7) { width: 130px; } /* Created */
        .table th:nth-child(8) { width: 100px; } /* Actions */
        
        /* Clickable title styling */
        .table a {
            transition: color 0.2s ease;
            text-decoration: underline;
            color: #4dabf7 !important;
        }
        
        /* Title column alignment */
        .table td:nth-child(2) {
            text-align: left !important;
        }
        
        /* Professional table styling */
        .table {
            table-layout: fixed;
            width: 100%;
        }
        
        .table td {
            padding: 8px 6px;
            vertical-align: middle;
        }
        
        .table th {
            padding: 10px 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        /* Compact badges */
        .table .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }
        
        /* Compact action buttons */
        .table .btn {
            padding: 4px 8px;
            font-size: 0.8rem;
            margin: 0 2px;
        }
        
        /* Refresh button animation */
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .table a:hover {
            color: #339af0 !important;
            text-decoration: underline;
        }
        
        .table {
            background: rgba(255,255,255,.05);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,.1);
        }
        
        
        
        .table td {
            border: none;
            color: rgba(255,255,255,.9);
            vertical-align: middle;
            padding: 1rem;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: rgba(255,255,255,.08);
            transform: scale(1.01);
        }
        
        .badge-admin {
            background: linear-gradient(45deg, var(--admin-red), var(--admin-red-dark));
			color: white;
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .badge-creator {
            background: linear-gradient(45deg, #059669, #047857);
			color: white;
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .badge-viewer {
            background: linear-gradient(45deg, #7c3aed, #6d28d9);
			color: white;
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .modal-content {
            background: #1a1a1a !important;
            border: 1px solid rgba(220,38,38,.3) !important;
            border-radius: 20px !important;
            box-shadow: 0 20px 60px rgba(0,0,0,.5) !important;
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(220,38,38,.3) !important;
            border-radius: 20px 20px 0 0 !important;
        }
        
        .modal-footer {
            border-top: 1px solid rgba(220,38,38,.3) !important;
            border-radius: 0 0 20px 20px !important;
        }
        
        .form-control {
            background: rgba(255,255,255,.1) !important;
            border: 1px solid rgba(255,255,255,.2) !important;
            color: white !important;
            border-radius: 12px !important;
        }
        
        .form-control:focus {
            background: rgba(255,255,255,.15) !important;
            border-color: var(--admin-red) !important;
            box-shadow: 0 0 0 0.2rem rgba(220,38,38,.25) !important;
            color: white !important;
        }
        
        .form-control::placeholder {
            color: rgba(255,255,255,.6) !important;
        }
        
        .form-check-input:checked {
            background-color: var(--admin-red) !important;
            border-color: var(--admin-red) !important;
        }
        
                 .btn-group .btn {
             border-radius: 8px !important;
             margin: 0 2px;
             min-width: 40px;
             padding: 0.5rem 0.75rem;
         }
        
        .btn-outline-success {
            border-color: #059669 !important;
            color: #059669 !important;
        }
        
        .btn-outline-success:hover {
            background-color: #059669 !important;
            border-color: #059669 !important;
        }
        
        .btn-outline-warning {
            border-color: #d97706 !important;
            color: #d97706 !important;
        }
        
        .btn-outline-warning:hover {
            background-color: #d97706 !important;
            border-color: #d97706 !important;
        }
        
        .btn-outline-danger {
            border-color: var(--admin-red) !important;
            color: var(--admin-red) !important;
        }
        
        .btn-outline-danger:hover {
            background-color: var(--admin-red) !important;
            border-color: var(--admin-red) !important;
        }
        
        .container {
            max-width: 1200px;
        }
        
        .row {
            margin-bottom: 2rem;
        }
        
        .text-secondary {
            color: rgba(255,255,255,.7) !important;
        }
        
        .text-white {
            color: white !important;
        }
        
        .fw-semibold {
            font-weight: 600 !important;
        }
        
        .fs-4 {
            font-size: 1.5rem !important;
        }
        
        .small {
            font-size: 0.875rem !important;
        }
        
        .nav-tabs {
            border-bottom: 2px solid rgba(220,38,38,.3);
            margin-bottom: 1.5rem;
        }
        
        .nav-tabs .nav-link {
            background: transparent;
            border: none;
            color: rgba(255,255,255,.7);
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 0;
            transition: all 0.3s ease;
				font-size: 0.9rem;
			}
			
        .nav-tabs .nav-link:hover {
            background: rgba(220,38,38,.1);
            color: white;
            border-color: transparent;
        }
        
        .nav-tabs .nav-link.active {
            background: rgba(220,38,38,.2);
            color: white;
            border-color: transparent;
            border-bottom: 3px solid var(--admin-red);
        }
        
        .tab-content {
            min-height: 400px;
        }
        
        .table {
            background: rgba(255,255,255,.05);
            border-radius: 8px;
            overflow: hidden;
        }
        
                 .table thead th {
             background: rgba(220,38,38,.2);
             border: none;
             color: white;
             font-weight: 600;
             padding: 0.75rem;
             font-size: 0.85rem;
             vertical-align: middle;
				text-align: left;
         }
         
         .table thead th i {
             vertical-align: middle;
             margin-right: 0.5rem;
         }
        
        .table tbody td {
            background: transparent;
            border: none;
            color: rgba(255,255,255,.9);
				padding: 0.75rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
            font-size: 0.9rem;
        }
        
        .table tbody tr:hover {
            background: rgba(220,38,38,.1);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .dropdown-menu {
            background: rgba(30,30,30,.95);
            border: 1px solid rgba(220,38,38,.3);
            backdrop-filter: blur(10px);
        }
        
        .dropdown-item {
            color: rgba(255,255,255,.9);
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(220,38,38,.2);
            color: white;
        }
        
        footer {
            border-top: 1px solid rgba(255,255,255,.15);
        }
        
        .brand {
            font-weight: 800;
            letter-spacing: .6px;
        }
        
        .social-links a {
            transition: color 0.3s ease;
        }
        
        .social-links a:hover {
            color: var(--admin-red) !important;
        }
        
        .activity-item {
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 8px;
        }
        
        .activity-item:hover {
            background: rgba(255,255,255,.05);
            transform: translateX(5px);
        }
        
        .activity-icon {
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover .activity-icon {
            transform: scale(1.1);
        }
        
        .system-health-item {
            transition: all 0.3s ease;
            padding: 1rem;
            border-radius: 12px;
        }
        
        .system-health-item:hover {
            background: rgba(255,255,255,.05);
            transform: translateY(-2px);
        }
        
        .system-health-icon {
            transition: all 0.3s ease;
        }
        
                 .system-health-item:hover .system-health-icon {
             transform: scale(1.1);
             box-shadow: 0 4px 12px rgba(0,0,0,.3);
         }

         /* Enhanced Table Styles */
         .sortable {
             cursor: pointer;
             user-select: none;
             transition: background-color 0.2s ease;
         }

         .sortable:hover {
             background: rgba(220,38,38,.15);
         }

         .table-hover tbody tr:hover {
             background: rgba(220,38,38,.1);
             transform: scale(1.005);
         }

         .pagination .page-link {
             background: rgba(255,255,255,.08);
             border: 1px solid rgba(255,255,255,.15);
             color: white;
             transition: all 0.3s ease;
         }

         .pagination .page-link:hover {
             background: rgba(220,38,38,.2);
             border-color: rgba(220,38,38,.3);
             color: white;
         }

         .pagination .page-item.active .page-link {
             background: var(--admin-red);
             border-color: var(--admin-red);
             color: white;
         }

         .pagination .page-item.disabled .page-link {
             background: rgba(255,255,255,.05);
             border-color: rgba(255,255,255,.1);
             color: rgba(255,255,255,.5);
             cursor: not-allowed;
         }

         .input-group-text {
             background: rgba(255,255,255,.1);
             border-color: rgba(255,255,255,.2);
             color: rgba(255,255,255,.8);
         }

         .form-select {
             background: rgba(255,255,255,.1);
             border: 1px solid rgba(255,255,255,.2);
             color: white;
             border-radius: 12px;
         }

         .form-select:focus {
             background: rgba(255,255,255,.15);
             border-color: var(--admin-red);
             box-shadow: 0 0 0 0.2rem rgba(220,38,38,.25);
             color: white;
         }

        /* Prevent this card from stretching to fill height */
         .admin-card.auto-height {
             height: auto;
         }

        /* Ensure modal media fits nicely */
        #contentDetailsModal #cdMedia img,
        #contentDetailsModal #cdMedia video {
            max-width: 100%;
            height: auto;
            max-height: 50vh;
            display: block;
            object-fit: contain;
            margin: 0 auto;
        }
        #contentDetailsModal #cdMedia audio {
            width: 100%;
        }
        #contentDetailsModal #cdDescription {
            max-height: 16vh;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 7;
            -webkit-box-orient: vertical;
        }
        .quick-actions .qa-item {
            display: flex;
            align-items: center;
            padding: .6rem .75rem;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 10px;
            background: rgba(255,255,255,.06);
            margin-bottom: .5rem;
        }
        .quick-actions .qa-icon {
            width: 34px; height: 34px; border-radius: 8px;
            background: rgba(255,255,255,.16);
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff;
        }
        .quick-actions .qa-title { color: #fff; font-weight: 600; font-size: .9rem; line-height: 1; }
        .quick-actions .qa-desc { color: rgba(255,255,255,.75); font-size: .8rem; }
        .btn-upload {
            background: linear-gradient(90deg, var(--admin-red), var(--admin-red-dark));
            border: 0;
            color: #fff;
            padding: 0.7rem 1.25rem;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(220,38,38,.25);
            font-weight: 700;
            letter-spacing: .2px;
        }
        .btn-upload:hover {
            transform: translateY(-1px);
            filter: brightness(1.06);
            box-shadow: 0 10px 24px rgba(220,38,38,.35);
        }
        /* Content search input: square left corners */
        #contentSearch {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }
+        /* Keep tabs underline within tabs width */
+        #dashTabs { border-bottom-color: rgba(255,255,255,.2); }
+        #dashTabs + .tab-content { border-top: 0; }
+        /* Small-screen alignment with admin dashboard */
+        @media (max-width: 576px) {
+            #dashTabs { overflow-x: auto; white-space: nowrap; flex-wrap: nowrap; }
+            #dashTabs .nav-link { padding: .5rem .75rem; font-size: .9rem; }
+            .admin-card { padding: 12px; }
+            .table { font-size: .9rem; }
+            .input-group-text { padding: .35rem .5rem; }
+            .form-select, .form-control { padding: .4rem .55rem; font-size: .9rem; }
+            footer form.d-flex { max-width: 100% !important; }
+        }
    </style>
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="Cache-Control" content="no-store" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="referrer" content="no-referrer" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="color-scheme" content="dark light" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="theme-color" content="#1a1a1a" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta http-equiv="x-dns-prefetch-control" content="on" />
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
</head>
<body>
<style>
  .container, .container-fluid { max-width: 100% !important; width: 100% !important; }
  .table { width: 100%; }
  @media (max-width: 992px) {
    .table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; white-space: nowrap; }
  }
  @media (max-width: 576px) {
    .nav.nav-tabs { overflow: visible; white-space: normal; flex-wrap: wrap; }
    /* Move user dropdown above tabs */
    .d-flex.justify-content-between.align-items-center.mb-3 { flex-direction: column; align-items: flex-start; gap: .5rem; }
    .d-flex.justify-content-between.align-items-center.mb-3 > .dropdown { order: -1; align-self: stretch; }
    /* Stack header actions under titles in cards */
    .admin-card > .d-flex.justify-content-between.align-items-center.mb-4 { flex-direction: column; align-items: flex-start; gap: .5rem; }
    .admin-card > .d-flex.justify-content-between.align-items-center.mb-4 > h5 { font-size: 1.25rem; line-height: 1.2; }
    .admin-card > .d-flex.justify-content-between.align-items-center.mb-4 > div:last-child { width: 100%; display: flex; flex-wrap: wrap; gap: .5rem; }
    .admin-card > .d-flex.justify-content-between.align-items-center.mb-4 > div:last-child > .btn { flex: 1 1 auto; padding: .45rem .75rem; font-size: .95rem; }
    /* Stat cards: ensure nice stacking */
    #statsCards > * { width: 100% !important; }
    /* Table headers and cells: tighter spacing but readable */
    .table thead th { white-space: nowrap; font-size: .95rem; }
    .table td { padding: .75rem; }
  }
  
  /* Category select styling */
  .form-select {
    background-color: transparent !important;
    color: white !important;
    border: 1px solid rgba(255,255,255,0.3) !important;
  }
  
  .form-select:focus {
    background-color: transparent !important;
    color: white !important;
    border-color: rgba(255,255,255,0.5) !important;
    box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.25) !important;
  }
  
  .form-select option {
    background-color: #1a1a1a !important;
    color: white !important;
  }
</style>
<div class="container pt-2 px-3 px-md-4 container-centered">
    <div class="row align-items-center mb-3">
        <div class="col-lg-1 d-none d-lg-block"></div>
        <div class="col-lg-10">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <ul class="nav nav-tabs m-0" id="dashTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overviewTab" type="button" role="tab"><i class="bi bi-speedometer2 me-1"></i> Overview</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="uploads-tab" data-bs-toggle="tab" data-bs-target="#uploadsTab" type="button" role="tab"><i class="bi bi-cloud-upload me-1"></i> Uploads</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#contentTab" type="button" role="tab"><i class="bi bi-collection-play me-1"></i> Content</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#paymentTab" type="button" role="tab"><i class="bi bi-credit-card me-1"></i> Payments</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settingsTab" type="button" role="tab"><i class="bi bi-gear me-1"></i> Settings</button>
            </li>
        </ul>
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i>
                <?php echo htmlspecialchars($user_name); ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" onclick="selectTab('#settingsTab');return false;"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="/kabaka/public/api/auth.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
        </div>
        <div class="col-lg-1 d-none d-lg-block"></div>
    </div>

    <div class="tab-content">
        <!-- Overview -->
        <div class="tab-pane fade show active" id="overviewTab" role="tabpanel">
            <div class="row g-2 g-md-3 mb-3" id="statsCards">
                <div class="d-none d-xl-block col-xl-1"></div>
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-label">Total Content</div>
                        <div class="stat-value" id="ovTotalContent">0</div>
                        <div class="stat-icon"><i class="bi bi-collection-play"></i></div>
							</div>
						</div>
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-label">Views</div>
                        <div class="stat-value" id="ovViews">0</div>
                        <div class="stat-icon"><i class="bi bi-eye"></i></div>
					</div>
				</div>
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-label">Comments</div>
                        <div class="stat-value" id="ovComments">0</div>
                        <div class="stat-icon"><i class="bi bi-chat"></i></div>
			</div>
		</div>
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-label">Followers</div>
                        <div class="stat-value" id="ovFollowers">0</div>
                        <div class="stat-icon"><i class="bi bi-people"></i></div>
		</div>
						</div>
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="stat-card">
                         <div class="stat-label">Likes</div>
                        <div class="stat-value" id="ovLikes">0</div>
                        <div class="stat-icon"><i class="bi bi-heart-fill"></i></div>
					</div>
				</div>
			</div>

            <div class="row g-3">
                <div class="d-none d-xl-block col-xl-1"></div>
                <div class="col-lg-5 col-xl-5">
                    <div class="admin-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-white mb-0"><i class="bi bi-clock-history me-2"></i> Recent Content</h6>
                            <a href="#" class="small text-decoration-none" onclick="selectTab('#contentTab');return false;">View all</a>
						</div>
                        <div id="recentList" class="small text-secondary">Loading...</div>
					</div>
				</div>
                <div class="col-lg-5 col-xl-5">
                    <div class="admin-card quick-actions">
                        <h6 class="text-white mb-3"><i class="bi bi-lightning-charge me-2"></i> Quick Actions</h6>
                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <button class="btn btn-outline-light w-100" onclick="selectTab('#uploadsTab')"><i class="bi bi-cloud-upload "></i> Upload</button>
			</div>
                            <div class="col-sm-6">
                                <button class="btn btn-outline-light w-100" onclick="selectTab('#contentTab')"><i class="bi bi-collection-play me-1"></i> Manage</button>
						</div>
					</div>
                        <div class="qa-item">
                            <div class="qa-icon"><i class="bi bi-gear"></i></div>
                            <div class="ms-2">
                                <div class="qa-title">Open Settings</div>
                                <div class="qa-desc">Update profile and payout details</div>
				</div>
                            <button class="btn btn-sm btn-outline-light ms-auto" onclick="selectTab('#settingsTab')">Open</button>
			</div>
                        <div class="qa-item">
                            <div class="qa-icon"><i class="bi bi-graph-up"></i></div>
                            <div class="ms-2">
                                <div class="qa-title">View Analytics</div>
                                <div class="qa-desc">Check performance of your posts</div>
						</div>
                            <button class="btn btn-sm btn-outline-light ms-auto" onclick="scrollToStats()">View</button>
					</div>
                        <div class="qa-item">
                            <div class="qa-icon position-relative">
                                <i class="bi bi-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="font-size: 0.6rem; display: none;">
                                    0
                                </span>
                            </div>
                            <div class="ms-2">
                                <div class="qa-title">Notifications</div>
                                <div class="qa-desc">See latest updates and alerts</div>
                            </div>
                            <button class="btn btn-sm btn-outline-light ms-auto" onclick="showNotificationsModal()">Open</button>
                        </div>
                    </div>
				</div>
			</div>
		</div>

        <!-- Uploads -->
        <div class="tab-pane fade" id="uploadsTab" role="tabpanel">
            <div class="row g-3">
                <div class="d-none d-xl-block col-xl-2"></div>
                <div class="col-lg-11 col-xl-8">
                    <div class="admin-card">
                <h6 class="text-white mb-3"><i class="bi bi-cloud-upload me-2"></i> <span class="fs-5">Upload new content</span></h6>
                <form id="uploadForm" enctype="multipart/form-data" onsubmit="handleUpload(event)">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" placeholder="Enter title" required>
                            <div class="form-text text-secondary">Keep it short and descriptive.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" style="background-color: transparent; color: white; border: 1px solid rgba(255,255,255,0.3);">
                                <option value="Video" style="background-color: #1a1a1a; color: white;">Video</option>
                                <option value="Music" style="background-color: #1a1a1a; color: white;">Music</option>
                                <option value="Audio" style="background-color: #1a1a1a; color: white;">Audio</option>
                                <option value="Podcast" style="background-color: #1a1a1a; color: white;">Podcast</option>
                                <option value="Image" style="background-color: #1a1a1a; color: white;">Image</option>
                                <option value="Other" style="background-color: #1a1a1a; color: white;">Other</option>
                            </select>
                            <div class="form-text text-secondary">Choose the most relevant category.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Optional"></textarea>
                            <div class="form-text text-secondary">Tell viewers what this content is about.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tags</label>
                            <input type="text" name="tags" class="form-control" placeholder="e.g. travel, nature, vlog">
                            <div class="form-text text-secondary">Comma-separated keywords.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thumbnail URL</label>
                            <input type="url" name="thumbnail_url" class="form-control" placeholder="https://... (optional)">
                            <div class="form-text text-secondary">Provide a custom thumbnail URL (optional).</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ownership Note</label>
                            <textarea name="ownership_note" class="form-control" rows="2" placeholder="Optional note about ownership/rights"></textarea>
                            <div class="form-text text-secondary">Visible to admins if review is required.</div>
                        </div>
                        <div class="col-12">
                            <div id="uploadArea" class="upload-area">
                                <i class="bi bi-upload" style="font-size:2rem;"></i>
                                <div class="mt-2">Drag & drop file here, or click to browse</div>
                                <input type="file" name="media" id="mediaInput" accept="image/*,video/*,audio/*" hidden>
                            </div>
                            <div class="mt-2 small text-secondary" id="fileInfo">No file selected.</div>
                            <div class="small text-secondary" id="maxSizeNote"></div>
                            <div class="progress mt-2" id="uploadProgressWrap" style="height:8px; display:none; background: rgba(255,255,255,.08);">
                                <div class="progress-bar bg-success" id="uploadProgressBar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-outline-light bg-success">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Upload
                            </button>
                            <button type="button" class="btn btn-outline-light" onclick="document.getElementById('uploadForm').reset(); updateFileInfo();">
                                <i class="bi bi-x-circle me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </form>
                    </div>
                </div>
                <div class="d-none d-xl-block col-xl-2"></div>
            </div>
        </div>

        <!-- Content -->
        <div class="tab-pane fade" id="contentTab" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-1"></div>
                <div class="col-lg-10">
            <div class="admin-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-white mb-0"><i class="bi bi-collection-play me-2"></i> My Content</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-light" onclick="selectTab('#uploadsTab')"><i class="bi bi-cloud-upload me-1"></i> New Upload</button>
                        <button class="btn btn-outline-light" onclick="refreshContent()"><i class="bi bi-arrow-repeat"></i></button>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-sm-6 col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent text-white border-secondary"><i class="bi bi-search"></i></span>
                            <input id="contentSearch" type="search" class="form-control" placeholder="Search by title...">
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <select id="contentCategory" class="form-select" onchange="filterContent()">
                            <option value="">All Categories</option>
                            <option value="Video">Video</option>
                            <option value="Music">Music</option>
                            <option value="Audio">Audio</option>
                            <option value="Podcast">Podcast</option>
                            <option value="Image">Image</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <select id="contentStatus" class="form-select" onchange="filterContent()">
                            <option value="">All Status</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <select id="contentSort" class="form-select" onchange="filterContent()">
                            <option value="created_desc">Newest First</option>
                            <option value="created_asc">Oldest First</option>
                            <option value="title_asc">Title A–Z</option>
                            <option value="title_desc">Title Z–A</option>
                            <option value="views_desc">Most Views</option>
                            <option value="views_asc">Least Views</option>
                            <option value="likes_desc">Most Likes</option>
                            <option value="likes_asc">Least Likes</option>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <select id="contentPageSize" class="form-select" onchange="changePageSize()">
                            <option value="10">10 / page</option>
                            <option value="20" selected>20 / page</option>
                            <option value="50">50 / page</option>
                            <option value="100">100 / page</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark align-middle mb-3">
                        <thead>
                        <tr>
                            <th style="width: 36px;"><input class="form-check-input" type="checkbox" id="contentSelectAll"></th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th class="sortable" onclick="sortTable('views')" style="cursor: pointer;">
                                Views 
                                <i class="bi bi-arrow-down-up ms-1" id="sort-views"></i>
                            </th>
                            <th class="sortable" onclick="sortTable('likes')" style="cursor: pointer;">
                                Likes 
                                <i class="bi bi-arrow-down-up ms-1" id="sort-likes"></i>
                            </th>
                            <th class="sortable" onclick="sortTable('created')" style="cursor: pointer;">
                                Created 
                                <i class="bi bi-arrow-down-up ms-1" id="sort-created"></i>
                            </th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody id="contentTableBody">
                        <tr><td colspan="8" class="text-center text-secondary">No items</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-secondary small" id="contentPageInfo">Page 1</div>
                    <div class="btn-group">
                        <button class="btn btn-outline-light btn-sm" id="contentPagePrev"><i class="bi bi-chevron-left"></i></button>
                        <button class="btn btn-outline-light btn-sm" id="contentPageNext"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>
                </div>
                <div class="col-lg-1"></div>
            </div>
        </div>

        <!-- Payments -->
        <div class="tab-pane fade" id="paymentTab" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-1"></div> <!-- Left gap -->
                <div class="col-lg-10"> <!-- Main content -->
                    <div class="row g-4">
                        <!-- Earned Money Available for Withdrawal -->
                        <div class="col-lg-4">
                            <div class="admin-card">
                                <h6 class="text-white mb-3"><i class="bi bi-cash-coin me-2"></i> Earned Money Available</h6>
                                <div class="text-center">
                                    <div class="stat-value" id="walletBalance"></div>
                                    <div class="stat-label">Available for Withdrawal</div>
                                    <div class="mt-3">
                                        <div class="text-secondary small" id="pendingAmount"></div>
                                        <div class="text-secondary small" id="totalEarnings"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Eligibility Status -->
                        <div class="col-lg-8">
                            <div class="admin-card">
                                <h6 class="text-white mb-3"><i class="bi bi-shield-check me-2"></i> Payment Eligibility</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center justify-content-between p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="bi bi-person-check fs-4" id="verificationIcon" style="color: #ef4444;"></i>
                                                </div>
                                                <div>
                                                    <div class="text-white fw-semibold">Verification</div>
                                                    <div class="text-secondary small" id="verificationStatus">Not Verified</div>
                                                </div>
                                            </div>
                                            <button class="btn btn-outline-light btn-sm d-flex align-items-center" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" onclick="openVerificationModal()" id="verificationBtn">
                                                <i class="bi bi-shield-check me-1"></i><span>Request</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center justify-content-between p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="bi bi-currency-dollar fs-4" id="monetizationIcon" style="color: #ef4444;"></i>
                                                </div>
                                                <div>
                                                    <div class="text-white fw-semibold">Monetization</div>
                                                    <div class="text-secondary small" id="monetizationStatus">Disabled</div>
                                                </div>
                                            </div>
                                            <button class="btn btn-outline-light btn-sm d-flex align-items-center" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" onclick="openMonetizationModal()" id="monetizationBtn">
                                                <i class="bi bi-currency-dollar me-1"></i><span>Request</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                            <div class="me-3">
                                                <i class="bi bi-geo-alt fs-4" id="payoutIcon" style="color: #ef4444;"></i>
                                            </div>
                                            <div>
                                                <div class="text-white fw-semibold">Payout Destination</div>
                                                <div class="text-secondary small" id="payoutStatus">Not Set</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                            <div class="me-3">
                                                <i class="bi bi-graph-up fs-4" id="minimumIcon" style="color: #ef4444;"></i>
                                            </div>
                                            <div>
                                                <div class="text-white fw-semibold">Minimum Balance</div>
                                                <div class="text-secondary small" id="minimumStatus">$0.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Rules & Withdrawal -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="admin-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-white mb-0"><i class="bi bi-info-circle me-2"></i> Payment Rules & Withdrawal</h6>
                                </div>
                                
                                <!-- Admin Settings Display -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                            <div class="text-white fw-semibold">Minimum Withdrawal</div>
                                            <div class="text-secondary small" id="minWithdrawal">$10.00</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                            <div class="text-white fw-semibold">Platform Fee</div>
                                            <div class="text-secondary small" id="platformFee">5%</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                            <div class="text-white fw-semibold">Processing Fee</div>
                                            <div class="text-secondary small" id="processingFee">$1.00</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Withdrawal Form -->
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Withdrawal Amount (USDT)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent text-white border-secondary" style="border-right: none;">$</span>
                                            <input type="number" class="form-control" id="withdrawalAmount" placeholder="0.00" step="0.01" min="0">
                                        </div>
                                        <div class="form-text text-secondary">Minimum: $<span id="minAmountDisplay">10.00</span></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Payout Destination</label>
                                        <input type="text" class="form-control" id="payoutDestination" placeholder="USDT Address or Phone Number" readonly>
                                        <div class="form-text text-secondary">Set in Settings → Profile</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #93c5fd;">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Withdrawal Process:</strong> 
                                            <span id="withdrawalProcess">Automatic monthly payouts are enabled. Withdrawal will be processed automatically if eligible.</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-outline-light bg-success" id="requestWithdrawalBtn" onclick="requestWithdrawal()" disabled>
                                            <i class="bi bi-arrow-up-circle me-1"></i> Request Withdrawal
                                        </button>
                                        <button class="btn btn-outline-light ms-2" onclick="loadPaymentData()">
                                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            
            <!-- PPD Transactions Modal -->
            <div class="modal fade" id="ppdTxModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content" style="background:#111827; color:white; border-radius:12px; border: 1px solid rgba(255,255,255,.1)">
                        <div class="modal-header border-0">
                            <h5 class="modal-title"><i class="bi bi-receipt-cutoff me-2"></i>Download Transactions</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-dark table-striped align-middle mb-0" id="ppdTxTable" style="table-layout: fixed;">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 18%">Time</th>
                                            <th scope="col" style="width: 14%">Content</th>
                                            <th scope="col" style="width: 38%">Sender</th>
                                            <th scope="col" style="width: 14%">Amount</th>
                                            <th scope="col" style="width: 16%">Tx</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-secondary small" id="ppdPageInfo"></div>
                                <div class="btn-group">
                                    <button class="btn btn-outline-light btn-sm" id="ppdPrev" style="min-width: 90px;">Prev</button>
                                    <button class="btn btn-outline-light btn-sm" id="ppdNext" style="min-width: 90px;">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                    <!-- Recent Transactions -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="admin-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-white mb-0"><i class="bi bi-clock-history me-2"></i> Recent Transactions</h6>
                                    <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#ppdTxModal">
                                        <i class="bi bi-receipt-cutoff me-1"></i> Download Transactions
                                    </button>
                                </div>
                               <div class="table-responsive">
                                   <table class="table table-dark align-middle">
                                       <thead>
                                           <tr>
                                               <th class="text-center" style="width: 20%;">Date</th>
                                               <th class="text-center" style="width: 15%;">Type</th>
                                               <th class="text-center" style="width: 20%;">Amount</th>
                                               <th class="text-center" style="width: 15%;">Status</th>
                                               <th class="text-center" style="width: 30%;">Details</th>
                                           </tr>
                                       </thead>
                                       <tbody id="transactionsTableBody">
                                           <tr><td colspan="5" class="text-center text-secondary">Loading transactions...</td></tr>
                                       </tbody>
                                   </table>
                               </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-1"></div> <!-- Right gap -->
            </div>
        </div>

        <!-- Settings -->
        <div class="tab-pane fade" id="settingsTab" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-1"></div> <!-- Left gap -->
                <div class="col-lg-10"> <!-- Main content -->
                    <div class="row g-4">
                        <!-- Profile & Settings -->
                        <div class="col-lg-8">
                            <div class="admin-card">
                                <h6 class="text-white mb-3"><i class="bi bi-person me-2"></i> Profile & Settings</h6>
                                <form id="settingsForm" onsubmit="handleSettingsSave(event)">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Display Name</label>
                                            <input type="text" class="form-control" id="displayName" value="<?php echo htmlspecialchars($user_name); ?>" required>
                                            <div class="form-text text-secondary">This is how other users will see your name.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                                            <div class="form-text text-secondary">Email cannot be changed. Contact support if needed.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">User ID</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['uid']); ?>" readonly>
                                            <div class="form-text text-secondary">Your unique user identifier.</div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">USDT Address</label>
                                            <input type="text" class="form-control" id="usdtAddress" placeholder="TRC20 / ERC20">
                                            <div class="form-text text-secondary">For receiving payments (optional).</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-outline-light bg-success" type="submit"><i class="bi bi-save me-1"></i> Save Changes</button>
                                                <button class="btn btn-outline-light" type="reset" onclick="loadSettings()"><i class="bi bi-arrow-clockwise me-1"></i> Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Account Security -->
                        <div class="col-lg-4">
                            <div class="admin-card">
                                <h6 class="text-white mb-3"><i class="bi bi-shield-lock me-2"></i> Account Security</h6><hr>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="currentPassword" placeholder="Enter current password">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="newPassword" placeholder="Enter new password">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
                                    </div>
                                    <div class="col-12"><br>
                                        <button class="btn btn-outline-light w-100" onclick="handlePasswordChange()">
                                            <i class="bi bi-key me-1"></i> Change Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Actions -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="admin-card" style="background: transparent; border: 1px solid transparent; box-shadow: none;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                                    <h6 class="text-danger mb-0">Account Actions:</h6>
                                </div>
                                <div class="form-text text-danger mt-1">
                                            This action cannot be undone and everything will be permanently removed.
                                        </div>
                                
                                <div class="row g-3 mb-0">
                                    <div class="col-12">
                                        <hr class="my-2">
                                        <div class="d-flex gap-1 mb-0">
                                            <input type="text" class="form-control " id="deleteConfirmation" placeholder="Type DELETE here" style="border-radius: 0;">
                                            <button class="btn btn-danger d-flex align-items-center" id="deleteAccountBtn" onclick="deleteAccount()" disabled>
                                                <i class="bi bi-trash me-1"></i>Delete
                                            </button>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-1"></div> <!-- Right gap -->
            </div>
        </div>
		</div>
	</div>

<div class="position-fixed" style="right:16px; bottom:16px; z-index:1080;" id="toastHost"></div>

<!-- Notifications Modal -->
<div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background: rgba(13, 19, 33, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,.16);">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-white" id="notificationsModalLabel">
                    <i class="bi bi-bell me-2"></i>Notifications
                </h5>
                &nbsp;&nbsp;<button type="button" class="btn btn-outline bg-danger" onclick="deleteAllNotifications()">
                    <i class="bi bi-trash me-1"></i>
                </button>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="notificationsList">
                    <div class="text-center text-secondary">
                        <i class="bi bi-hourglass-split fs-1"></i>
                        <div class="mt-2">Loading notifications...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-light btn-sm" onclick="markAllAsRead()">
                    <i class="bi bi-check-all me-1"></i> Mark All as Read
                </button>
                <button type="button" class="btn btn-outline-light bg-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Content Details Modal - Simple Version -->
<div class="modal fade" id="contentDetailsModal" tabindex="-1" aria-labelledby="contentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: rgba(13, 19, 33, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,.16);">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-white" id="contentDetailsModalLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>Content Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <strong class="text-white"><i class="bi bi-file-text me-2"></i>Title:</strong>
                    <div class="text-white mt-2" id="cdTitle">Loading...</div>
                </div>
                
                <div class="mb-4">
                    <strong class="text-white"><i class="bi bi-tag me-2"></i>Category:</strong>
                    <span class="ms-2" id="cdCategory" style="color: #74c0fc;">Loading...</span>
                </div>
                
                <div class="mb-4">
                    <strong class="text-white"><i class="bi bi-shield-check me-2"></i>Status:</strong>
                    <span class="ms-2" id="cdStatus">Loading...</span>
                </div>
                
                <div class="mb-4">
                    <strong class="text-white"><i class="bi bi-card-text me-2"></i>Description:</strong>
                    <div class="text-secondary mt-2" id="cdDescription">Loading...</div>
                </div>
                
                <div class="mb-4">
                    <strong class="text-white"><i class="bi bi-play-circle me-2"></i>Media Preview:</strong>
                    <div class="mt-2" id="cdMediaPreview" style="max-width: 100%; max-height: 200px; border-radius: 8px; overflow: hidden;">
                        <!-- Media preview will be loaded here -->
                    </div>
                </div>
                
                <div class="mb-4">
                    <strong class="text-white"><i class="bi bi-bar-chart me-2"></i>Stats:</strong>
                    <div class="text-secondary mt-2">
                        <i class="bi bi-eye me-1"></i><span id="cdViews">0</span> views | 
                        <i class="bi bi-heart me-1"></i><span id="cdLikes">0</span> likes | 
                        <i class="bi bi-chat me-1"></i><span id="cdComments">0</span> comments
                    </div>
                </div>
                
                <div class="mb-4">
                    <strong class="text-white"><i class="bi bi-calendar me-2"></i>Created:</strong>
                    <div class="text-secondary mt-2" id="cdCreatedAt">Loading...</div>
                </div>
                
                <div class="mb-4" id="cdTagsContainer" style="display: none;">
                    <strong class="text-white"><i class="bi bi-hash me-2"></i>Tags:</strong>
                    <div class="text-secondary mt-2" id="cdTags">Loading...</div>
                </div>
                
                <div class="mb-4" id="cdOwnershipContainer" style="display: none;">
                    <strong class="text-white"><i class="bi bi-info-circle me-2"></i>Ownership Note:</strong>
                    <div class="text-secondary mt-2" id="cdOwnership">Loading...</div>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteContentFromModal()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <!-- Verification Request Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="verificationModalLabel">
                        <i class="bi bi-shield-check me-2"></i>Account Verification Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Requirements -->
                        <div class="col-md-6">
                            <h6 class="text-white mb-3">Verification Requirements</h6>
                            <div class="p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                <div class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <span class="text-white">Minimum Content Posts:</span>
                                    <span class="text-secondary ms-2" id="verificationMinPosts">5 posts</span>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <span class="text-white">Account Age:</span>
                                    <span class="text-secondary ms-2" id="verificationMinAge">30 days</span>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <span class="text-white">Email Verification:</span>
                                    <span class="text-secondary ms-2">Required</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Current Status -->
                        <div class="col-md-6">
                            <h6 class="text-white mb-3">Your Current Status</h6>
                            <div class="p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                <div class="mb-2">
                                    <i class="bi bi-file-text text-info me-2"></i>
                                    <span class="text-white">Your Posts:</span>
                                    <span class="text-secondary ms-2" id="verificationCurrentPosts">0 posts</span>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-calendar text-info me-2"></i>
                                    <span class="text-white">Account Age:</span>
                                    <span class="text-secondary ms-2" id="verificationCurrentAge">0 days</span>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-envelope text-info me-2"></i>
                                    <span class="text-white">Email Status:</span>
                                    <span class="text-secondary ms-2" id="verificationEmailStatus">Verified</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Auto Verification Notice -->
                    <div class="mt-4 p-3" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px;">
                        <div class="text-success">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Automatic Verification:</strong> If you meet all requirements, your account will be verified instantly!
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-info " onclick="submitVerificationRequest()">
                        <i class="bi bi-shield-check me-1"></i>Verify Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Monetization Request Modal -->
    <div class="modal fade" id="monetizationModal" tabindex="-1" aria-labelledby="monetizationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="monetizationModalLabel">
                        <i class="bi bi-currency-dollar me-2"></i>Monetization Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Requirements -->
                        <div class="col-md-6">
                            <h6 class="text-white mb-3">Monetization Requirements</h6>
                            <div class="p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                <div class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <span class="text-white">Minimum Followers:</span>
                                    <span class="text-secondary ms-2" id="monetizationMinFollowers">100 followers</span>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <span class="text-white">Minimum Views:</span>
                                    <span class="text-secondary ms-2" id="monetizationMinViews">1000 views</span>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <span class="text-white">Account Verification:</span>
                                    <span class="text-secondary ms-2">Required</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Current Status -->
                        <div class="col-md-6">
                            <h6 class="text-white mb-3">Your Current Status</h6>
                            <div class="p-3" style="background: rgba(255,255,255,.05); border-radius: 12px;">
                                <div class="mb-2">
                                    <i class="bi bi-people text-info me-2"></i>
                                    <span class="text-white">Your Followers:</span>
                                    <span class="text-secondary ms-2" id="monetizationCurrentFollowers">0 followers</span>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-eye text-info me-2"></i>
                                    <span class="text-white">Your Views:</span>
                                    <span class="text-secondary ms-2" id="monetizationCurrentViews">0 views</span>
                                </div>
                                <div class="mb-2">
                                    <i class="bi bi-shield-check text-info me-2"></i>
                                    <span class="text-white">Verification:</span>
                                    <span class="text-secondary ms-2" id="monetizationVerificationStatus">Not Verified</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Auto Monetization Notice -->
                    <div class="mt-4 p-3" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px;">
                        <div class="text-success">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Automatic Monetization:</strong> If you meet all requirements, monetization will be enabled instantly!
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="submitMonetizationRequest()">
                        <i class="bi bi-currency-dollar me-1"></i>Enable Monetization
                    </button>
                </div>
            </div>
        </div>
    </div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
function showToast(message, type = 'info') {
    try { if (typeof __isUnloading !== 'undefined' && __isUnloading) { return; } } catch(e) {}
    const host = document.getElementById('toastHost');
    const id = 't' + Date.now();
    const bg = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-secondary';
    host.insertAdjacentHTML('beforeend', `
      <div id="${id}" class="toast text-white ${bg}" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">${message}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
				</div>
      </div>`);
    new bootstrap.Toast(document.getElementById(id), { delay: 3000 }).show();
}

// Basic in-page logic (placeholders; wire to APIs later)
function selectTab(selector) {
    const trigger = document.querySelector(`[data-bs-target="${selector}"]`);
    if (trigger) new bootstrap.Tab(trigger).show();
}

function scrollToStats() {
    // Scroll to the top of the page to show stats cards
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

function refreshContent() {
    try {
        if (typeof loadContentTable === 'function') {
            loadContentTable();
        }
    } catch (e) {
        console.error('refreshContent error:', e);
    }
}

// Upload interactions (no backend call yet)
const uploadArea = document.getElementById('uploadArea');
const mediaInput = document.getElementById('mediaInput');
const fileInfo = document.getElementById('fileInfo');
const maxSizeNote = document.getElementById('maxSizeNote');
const uploadProgressWrap = document.getElementById('uploadProgressWrap');
const uploadProgressBar = document.getElementById('uploadProgressBar');

uploadArea.addEventListener('click', () => mediaInput.click());
uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('dragover'); });
uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
uploadArea.addEventListener('drop', e => { e.preventDefault(); uploadArea.classList.remove('dragover'); if (e.dataTransfer.files?.length) { mediaInput.files = e.dataTransfer.files; updateFileInfo(); }});
mediaInput.addEventListener('change', updateFileInfo);

function updateFileInfo() {
    if (!mediaInput.files || mediaInput.files.length === 0) {
        fileInfo.textContent = 'No file selected.';
        maxSizeNote.textContent = '';
        uploadProgressWrap.style.display = 'none';
        return;
    }
    const f = mediaInput.files[0];
    fileInfo.textContent = `${f.name} • ${(f.size/1024/1024).toFixed(2)} MB`;

    // Set max size note
    const maxSize = 100 * 1024 * 1024; // 100MB in bytes
    if (f.size > maxSize) {
        maxSizeNote.textContent = `File size exceeds 100MB limit. Please choose a smaller file.`;
        maxSizeNote.style.color = 'red';
        uploadArea.classList.add('file-too-large');
    } else {
        maxSizeNote.textContent = '';
        maxSizeNote.style.color = 'white';
        uploadArea.classList.remove('file-too-large');
    }
}

// Load overview stats and recent content from API
async function loadOverviewAndRecent() {
    try {
        const res = await fetch('/kabaka/public/api/content.php?action=summary');
        if (!res.ok) {
            console.error('Summary API error:', res.status, res.statusText);
            throw new Error('Failed to fetch summary');
        }
        const data = await res.json();
        if (!data || !data.success) {
            console.error('Summary API response:', data);
            throw new Error(data?.error || 'Unexpected response');
        }

        const d = data.data;
        document.getElementById('ovTotalContent').textContent = d.content_counts?.total ?? 0;
        document.getElementById('ovViews').textContent = d.engagement?.views ?? 0;
        document.getElementById('ovComments').textContent = d.engagement?.comments ?? 0;
        document.getElementById('ovFollowers').textContent = d.engagement?.followers ?? 0;
        const likesEl = document.getElementById('ovLikes');
        if (likesEl) likesEl.textContent = d.engagement?.likes ?? 0;

        // Show platform max size note for uploads
        if (d.platform?.max_upload_size_mb) {
            const note = document.getElementById('maxSizeNote');
            if (note) note.textContent = `Max size: ${d.platform.max_upload_size_mb} MB`;
            window.__maxUploadBytes = d.platform.max_upload_size_mb * 1024 * 1024;
        }

        const recentHost = document.getElementById('recentList');
        const items = d.recent || [];
        if (!items.length) {
            recentHost.innerHTML = '<div class="text-secondary">No content yet.</div>';
        } else {
            recentHost.innerHTML = items.map(c => `
            <div class=\"d-flex align-items-center py-2 border-bottom border-white-10\">
                <div class=\"flex-grow-1\">
                    <div class=\"text-white small fw-semibold\">${escapeHtml(c.title || 'Untitled')}</div>
                    <div class=\"text-secondary small\">${escapeHtml(c.category || '—')} · ${new Date(c.created_at).toLocaleDateString()}</div>
                </div>
                <div class=\"ms-3 small text-secondary\">${(c.status || 'pending')}</div>
            </div>`).join('');
        }
    } catch (e) {
        if (typeof __isAbort === 'function' && __isAbort(e)) return;
        if (typeof __isUnloading !== 'undefined' && __isUnloading) return;
        document.getElementById('recentList').innerHTML = '<div class="text-danger">Failed to load.</div>';
        console.error(e);
    }
}

function handleUpload(e) {
    e.preventDefault();
    const form = document.getElementById('uploadForm');
    const submitBtn = form.querySelector('button[type=\"submit\"]');
    if (!mediaInput.files || mediaInput.files.length === 0) { showToast('Please select a file', 'error'); return; }
    const file = mediaInput.files[0];
    if (window.__maxUploadBytes && file.size > window.__maxUploadBytes) {
        showToast('File exceeds platform max size', 'error');
        return;
    }
    const fd = new FormData(form);
    submitBtn.disabled = true;
    submitBtn.classList.add('disabled');

    const barWrap = document.getElementById('uploadProgressWrap');
    const bar = document.getElementById('uploadProgressBar');
    if (barWrap && bar) { barWrap.style.display = ''; bar.style.width = '0%'; }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/kabaka/public/api/content.php');
    xhr.upload.onprogress = (evt) => {
        if (!evt.lengthComputable || !bar) return;
        const pct = Math.round((evt.loaded / evt.total) * 100);
        bar.style.width = pct + '%';
    };
    xhr.onreadystatechange = () => {
        if (xhr.readyState !== 4) return;
        try {
            const json = JSON.parse(xhr.responseText || '{}');
            if (xhr.status >= 200 && xhr.status < 300 && json && json.success) {
                showToast(json.message || 'Uploaded', 'success');
                form.reset();
                updateFileInfo();
                loadOverviewAndRecent();
                if (typeof loadContentTable === 'function') loadContentTable();
            } else {
                throw new Error(json?.error || 'Upload failed');
            }
        } catch (err) {
            console.error('Upload error:', err);
            console.error('Response text:', xhr.responseText);
            showToast(err.message || 'Upload failed', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled');
            if (barWrap) barWrap.style.display = 'none';
        }
    };
    xhr.send(fd);
}

// Content management functionality
let currentPage = 1;
let totalPages = 1;
let currentSort = {
    field: 'created',
    direction: 'desc'
};
let currentFilters = {
    search: '',
    category: '',
    status: '',
    sort: 'created_desc',
    pageSize: 20
};

// Initial load for table as well
loadContentTable();

// Settings functionality
async function loadSettings() {
    try {
        const res = await fetch('/kabaka/public/api/profile.php?action=get');
        if (!res.ok) throw new Error('Failed to load settings');
        const data = await res.json();
        if (data.success) {
            document.getElementById('displayName').value = data.data.display_name || '';
            document.getElementById('usdtAddress').value = data.data.usdt_address || '';
        }
    } catch (e) {
        if (typeof __isAbort === 'function' && __isAbort(e)) return;
        if (typeof __isUnloading !== 'undefined' && __isUnloading) return;
        console.error('Settings load error:', e);
        showToast('Failed to load settings', 'error');
    }
}

async function handleSettingsSave(e) {
    e.preventDefault();
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Saving...';
    
    try {
        const formData = new FormData();
        formData.append('display_name', document.getElementById('displayName').value);
        formData.append('usdt_address', document.getElementById('usdtAddress').value);
        
        const res = await fetch('/kabaka/public/api/profile.php?action=update', {
            method: 'POST',
            body: formData
        });
        
        if (!res.ok) throw new Error('Failed to save settings');
        const data = await res.json();
        
        if (data.success) {
            showToast('Settings saved successfully', 'success');
            // Update session display name if it changed
            if (data.data.display_name) {
                // Could update UI to reflect new name
            }
        } else {
            throw new Error(data.error || 'Save failed');
        }
    } catch (e) {
        console.error('Settings save error:', e);
        showToast(e.message || 'Failed to save settings', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-save me-1"></i> Save Changes';
    }
}

async function handlePasswordChange() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        showToast('Please fill all password fields', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showToast('New passwords do not match', 'error');
        return;
    }
    
    if (newPassword.length < 6) {
        showToast('Password must be at least 6 characters', 'error');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('current_password', currentPassword);
        formData.append('new_password', newPassword);
        
        const res = await fetch('/kabaka/public/api/profile.php?action=change_password', {
            method: 'POST',
            body: formData
        });
        
        if (!res.ok) throw new Error('Failed to change password');
        const data = await res.json();
        
        if (data.success) {
            showToast('Password changed successfully', 'success');
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        } else {
            throw new Error(data.error || 'Password change failed');
        }
    } catch (e) {
        console.error('Password change error:', e);
        showToast(e.message || 'Failed to change password', 'error');
    }
}

// Enable/disable delete button based on input
document.addEventListener('DOMContentLoaded', function() {
    const confirmationInput = document.getElementById('deleteConfirmation');
    const deleteBtn = document.getElementById('deleteAccountBtn');
    
    if (confirmationInput && deleteBtn) {
        confirmationInput.addEventListener('input', function() {
            deleteBtn.disabled = this.value !== 'DELETE';
        });
    }
});

// PPD Transactions loader
(function(){
  let page = 1, pageSize = 10, total = 0;
  async function loadPage(p){
    try {
      const res = await fetch(`/kabaka/public/api/ppd_payments.php?page=${p}&page_size=${pageSize}`, { credentials: 'same-origin' });
      const data = await res.json();
      if (!res.ok || !data || !data.ok) throw new Error('Failed to load transactions');
      const { items = [], total: t = 0, page: cur = 1, page_size: ps = 10 } = data.data || {};
      total = t; page = cur; pageSize = ps;
      const tbody = document.querySelector('#ppdTxTable tbody');
      if (!tbody) return;
      tbody.innerHTML = '';
      for (const it of items) {
        const tr = document.createElement('tr');
        const ts = new Date((it.created_at || '').replace(' ', 'T'));
        const amount = it.amount_smallest || '0';
        const human = Number(amount) / 1_000_000; // decimals=6
        const txUrl = `https://www.oklink.com/amoy/tx/${encodeURIComponent(it.tx_hash)}`;
        tr.innerHTML = `
          <td><span class="text-truncate" style="max-width:140px; display:inline-block">${isNaN(ts) ? (it.created_at || '') : ts.toLocaleString()}</span></td>
          <td><span class="text-truncate" style="max-width:80px; display:inline-block">#${it.content_id}</span></td>
          <td><span class="text-truncate" style="max-width:320px; display:inline-block">${it.sender_address}</span></td>
          <td><span class="text-truncate" style="max-width:100px; display:inline-block">${human.toFixed(6)} USDT</span></td>
          <td><a href="${txUrl}" target="_blank" rel="noopener" class="text-decoration-none text-info" title="Open in explorer">View  <i class="bi bi-box-arrow-up-right"></i></a></td>
        `;
        tbody.appendChild(tr);
      }
      const info = document.getElementById('ppdPageInfo');
      const totalPages = Math.max(1, Math.ceil(total / pageSize));
      if (info) info.textContent = `Page ${page} of ${totalPages} • ${total} total`;
      const prev = document.getElementById('ppdPrev');
      const next = document.getElementById('ppdNext');
      if (prev) prev.disabled = page <= 1;
      if (next) next.disabled = page >= totalPages;
    } catch (e) {
      console.error('Failed to load PPD txs', e);
    }
  }
  document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('ppdTxModal');
    if (!modal) return;
    modal.addEventListener('shown.bs.modal', function(){ loadPage(1); });
    document.getElementById('ppdPrev')?.addEventListener('click', function(){ if (page>1) loadPage(page-1); });
    document.getElementById('ppdNext')?.addEventListener('click', function(){ loadPage(page+1); });
  });
})();

async function deleteAccount() {
    try {
        const res = await fetch('/kabaka/public/api/profile.php?action=delete_account', {
            method: 'POST'
        });
        
        if (!res.ok) throw new Error('Failed to delete account');
        const data = await res.json();
        
        if (data.success) {
            showToast('Account deleted successfully', 'success');
            setTimeout(() => {
                window.location.href = '/kabaka/public/creator_dashboard/login.php';
            }, 2000);
        } else {
            throw new Error(data.error || 'Account deletion failed');
        }
    } catch (e) {
        console.error('Account deletion error:', e);
        showToast(e.message || 'Failed to delete account', 'error');
    }
}

async function exportAccountData() {
    try {
        showToast('Preparing your data export...', 'info');
        const res = await fetch('/kabaka/public/api/profile.php?action=export_data');
        
        if (!res.ok) throw new Error('Failed to export data');
        
        const blob = await res.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `kabaka-account-data-${new Date().toISOString().split('T')[0]}.zip`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        showToast('Data export completed', 'success');
    } catch (e) {
        console.error('Data export error:', e);
        showToast(e.message || 'Failed to export data', 'error');
    }
}

function filterContent() {
    currentFilters.search = document.getElementById('contentSearch').value;
    currentFilters.category = document.getElementById('contentCategory').value;
    currentFilters.status = document.getElementById('contentStatus').value;
    currentFilters.sort = document.getElementById('contentSort').value;
    currentPage = 1;
    loadContentTable();
}

function changePageSize() {
    currentFilters.pageSize = parseInt(document.getElementById('contentPageSize').value);
    currentPage = 1;
    loadContentTable();
}

function showNotificationsModal() {
    const modal = new bootstrap.Modal(document.getElementById('notificationsModal'));
    modal.show();
    loadNotifications();
}

async function loadNotifications() {
    try {
        const response = await fetch('/kabaka/public/api/notifications.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const text = await response.text();
        if (!text.trim()) {
            throw new Error('Empty response from server');
        }
        
        const data = JSON.parse(text);
        
        if (data.success) {
            displayNotifications(data.notifications);
            updateNotificationBadge(data.unread_count);
        } else {
            document.getElementById('notificationsList').innerHTML = `
                <div class="text-center text-secondary">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <div class="mt-2">Failed to load notifications</div>
                    <div class="small">${data.error || 'Unknown error'}</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
        document.getElementById('notificationsList').innerHTML = `
            <div class="text-center text-secondary">
                <i class="bi bi-exclamation-triangle fs-1"></i>
                <div class="mt-2">Error loading notifications</div>
                <div class="small">${error.message}</div>
            </div>
        `;
    }
}

function displayNotifications(notifications) {
    const container = document.getElementById('notificationsList');
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center text-secondary">
                <i class="bi bi-bell-slash fs-1"></i>
                <div class="mt-2">No notifications yet</div>
            </div>
        `;
        return;
    }
    
    container.innerHTML = notifications.map(notification => {
        const payload = JSON.parse(notification.payload || '{}');
        const isRead = notification.read_at !== null;
        const timeAgo = getTimeAgo(notification.created_at);
        
        let icon = 'bi-bell';
        let iconColor = 'text-secondary';
        
        switch (notification.type) {
            case 'content_approved':
                icon = 'bi-check-circle';
                iconColor = 'text-success';
                break;
            case 'content_rejected':
                icon = 'bi-x-circle';
                iconColor = 'text-danger';
                break;
            case 'new_follower':
                icon = 'bi-person-plus';
                iconColor = 'text-info';
                break;
            case 'new_comment':
                icon = 'bi-chat';
                iconColor = 'text-warning';
                break;
        }
        
        return `
            <div class="notification-item p-3 mb-2 ${isRead ? 'opacity-75' : ''}" style="background: rgba(255,255,255,.05); border-radius: 8px; border-left: 3px solid ${isRead ? 'transparent' : 'var(--admin-red)'};">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="bi ${icon} ${iconColor} fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-white fw-semibold">${escapeHtml(payload.title || notification.type)}</div>
                        <div class="text-secondary small">${escapeHtml(payload.message || '')}</div>
                        <div class="text-secondary small mt-1">${timeAgo}</div>
                    </div>
                    ${!isRead ? `
                        <button class="btn btn-sm btn-outline-light" onclick="markAsRead(${notification.id})" title="Mark as read">
                            <i class="bi bi-check"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }).join('');
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

async function markAsRead(notificationId) {
    try {
        const response = await fetch('/kabaka/public/api/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_read',
                notification_id: notificationId
            })
        });
        
        if (response.ok) {
            loadNotifications(); // Reload to update the list
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

async function markAllAsRead() {
    try {
        const response = await fetch('/kabaka/public/api/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_all_read'
            })
        });
        
        if (response.ok) {
            loadNotifications(); // Reload to update the list
        }
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
}

async function deleteAllNotifications() {
    if (!confirm('Are you sure you want to delete all notifications? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('/kabaka/public/api/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_all'
            })
        });
        
        if (response.ok) {
            loadNotifications(); // Reload to update the list
        } else {
            alert('Failed to delete notifications');
        }
    } catch (error) {
        console.error('Error deleting all notifications:', error);
        alert('Error deleting notifications');
    }
}

function getTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    return date.toLocaleDateString();
}

// Abort/suppress helpers for safe reloads
let __isUnloading = false;
const __inFlight = new Set();
window.addEventListener('beforeunload', function() {
    __isUnloading = true;
    __inFlight.forEach(function(ctrl) { try { ctrl.abort(); } catch (e) {} });
});
function __makeCtrl() { const c = new AbortController(); __inFlight.add(c); return c; }
function __doneCtrl(c) { __inFlight.delete(c); }
function __isAbort(e) { return e && (e.name === 'AbortError' || String(e).indexOf('NetworkError') !== -1); }

// Load notification count on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotificationCount();
    startNotificationPolling();
});

async function loadNotificationCount() {
    try {
        if (__isUnloading) return;
        const ctrl = __makeCtrl();
        const response = await fetch('/kabaka/public/api/notifications.php?action=count', { signal: ctrl.signal });
        __doneCtrl(ctrl);
        if (!response.ok) { return; }
        const text = await response.text();
        if (!text.trim()) { return; }
        const data = JSON.parse(text);
        if (data.success) {
            updateNotificationBadge(data.unread_count);
        }
    } catch (e) { /* silent */ }
}

// Lightweight polling to keep notification badge fresh without reload
let __notifPollTimer = null;
function startNotificationPolling() {
    stopNotificationPolling();
    const poll = () => { if (!document.hidden) { loadNotificationCount(); } };
    __notifPollTimer = setInterval(poll, 20000); // every 20s when visible
    document.addEventListener('visibilitychange', function onVis() {
        if (document.hidden) return; // when tab becomes visible, refresh once
        loadNotificationCount();
    });
}
function stopNotificationPolling() { if (__notifPollTimer) { clearInterval(__notifPollTimer); __notifPollTimer = null; } }
window.addEventListener('beforeunload', stopNotificationPolling);

function sortTable(field) {
    // Update sort state
    if (currentSort.field === field) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.field = field;
        currentSort.direction = 'desc'; // Default to descending for numbers/dates
    }
    
    // Update sort icons
    document.querySelectorAll('.sortable').forEach(th => {
        th.classList.remove('active');
        const icon = th.querySelector('i');
        icon.className = 'bi bi-arrow-down-up ms-1';
    });
    
    const activeTh = document.querySelector(`[onclick="sortTable('${field}')"]`);
    if (activeTh) {
        activeTh.classList.add('active');
        const icon = activeTh.querySelector('i');
        icon.className = currentSort.direction === 'asc' ? 'bi bi-arrow-up ms-1' : 'bi bi-arrow-down ms-1';
    }
    
    // Update filters and reload
    currentFilters.sort = `${field}_${currentSort.direction}`;
    currentPage = 1;
    loadContentTable();
}

async function loadContentTable() {
    try {
        const params = new URLSearchParams({
            page: currentPage,
            limit: currentFilters.pageSize,
            search: currentFilters.search,
            category: currentFilters.category,
            status: currentFilters.status,
            sort: currentFilters.sort
        });
        
        const res = await fetch(`/kabaka/public/api/content.php?${params}`);
        if (!res.ok) {
            console.error('Content list API error:', res.status, res.statusText);
            throw new Error('Failed to fetch content list');
        }
        const data = await res.json();
        if (!data || !data.success) {
            console.error('Content list API response:', data);
            throw new Error(data?.error || 'Unexpected response');
        }
        
        const items = data?.data?.content || [];
        const pagination = data?.data?.pagination || {};
        
        totalPages = pagination.total_pages || 1;
        currentPage = pagination.current_page || 1;
        
        const tbody = document.getElementById('contentTableBody');
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-secondary">No content found</td></tr>';
            return;
        }
        
        tbody.innerHTML = items.map(item => `
            <tr>
                <td><input class="form-check-input" type="checkbox" value="${item.id}"></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            ${item.thumbnail_url ? 
                                `<img src="${escapeHtml(item.thumbnail_url)}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">` :
                                `<div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="bi bi-file-earmark"></i></div>`
                            }
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <div class="text-white small fw-semibold" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px;">
                                <a href="#" onclick="viewContentDetails(${item.id}); return false;" class="text-white text-decoration-none" title="Click to view full details">
                                    ${escapeHtml(item.title || 'Untitled')}
                                </a>
                            </div>
                            <div class="text-secondary small" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px;" title="${escapeHtml(item.description || '')}">
                                ${escapeHtml(item.description || '').substring(0, 50)}${(item.description || '').length > 50 ? '...' : ''}
                            </div>
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-secondary">${escapeHtml(item.category || '—')}</span></td>
                <td>
                    <span class="${item.status === 'approved' ? 'text-success' : item.status === 'pending' ? 'text-warning' : 'text-danger'} fw-semibold">
                        ${escapeHtml(item.status || '—')}
                    </span>
                </td>
                <td class="text-center">${item.view_count || 0}</td>
                <td class="text-center">${item.like_count || 0}</td>
                <td class="text-secondary small">${item.created_at ? new Date(item.created_at).toLocaleDateString() : '—'}</td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-light" onclick="viewContent(${item.id})" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteContent(${item.id})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
        updatePagination();
        
    } catch (e) {
        console.error('Content table load error:', e);
        document.getElementById('contentTableBody').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Failed to load content</td></tr>';
    }
}

function updatePagination() {
    const pageInfo = document.getElementById('contentPageInfo');
    const prevBtn = document.getElementById('contentPagePrev');
    const nextBtn = document.getElementById('contentPageNext');
    
    if (pageInfo) {
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    }
    
    if (prevBtn) {
        prevBtn.disabled = currentPage <= 1;
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                loadContentTable();
            }
        };
    }
    
    if (nextBtn) {
        nextBtn.disabled = currentPage >= totalPages;
        nextBtn.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                loadContentTable();
            }
        };
    }
}

function editContent(contentId) {
    showToast('Edit functionality coming soon', 'info');
    // TODO: Implement edit modal/form
}

let currentContentId = null;

async function viewContentDetails(contentId) {
    currentContentId = contentId;
    
    try {
        // Show loading state
        document.getElementById('cdTitle').textContent = 'Loading...';
        document.getElementById('cdCategory').textContent = 'Loading...';
        document.getElementById('cdStatus').textContent = 'Loading...';
        document.getElementById('cdDescription').textContent = 'Loading...';
        document.getElementById('cdViews').textContent = '0';
        document.getElementById('cdLikes').textContent = '0';
        document.getElementById('cdComments').textContent = '0';
        document.getElementById('cdCreatedAt').textContent = 'Loading...';
        
        // Fetch content details
        const res = await fetch(`/kabaka/public/api/content.php?id=${contentId}`);
        if (!res.ok) throw new Error('Failed to fetch content details');
        
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load content');
        
        const content = data.data;
        
        // Populate modal with content data
        document.getElementById('cdTitle').textContent = content.title || 'Untitled';
        document.getElementById('cdCategory').textContent = content.category || '—';
        document.getElementById('cdDescription').textContent = content.description || 'No description';
        document.getElementById('cdViews').textContent = content.view_count || 0;
        document.getElementById('cdLikes').textContent = content.like_count || 0;
        document.getElementById('cdComments').textContent = content.comment_count || 0;
        document.getElementById('cdCreatedAt').textContent = content.created_at ? new Date(content.created_at).toLocaleString() : '—';
        
        // Status badge
        const statusEl = document.getElementById('cdStatus');
        statusEl.textContent = content.status || '—';
        // Remove badge styling and add text colors
        statusEl.className = '';
        if (content.status === 'approved') {
            statusEl.style.color = '#51cf66'; // Green
        } else if (content.status === 'pending') {
            statusEl.style.color = '#ffd43b'; // Yellow/Warning
        } else if (content.status === 'rejected') {
            statusEl.style.color = '#ff6b6b'; // Red
        } else {
            statusEl.style.color = '#adb5bd'; // Gray
        }
        
        // Tags
        if (content.tags) {
            document.getElementById('cdTagsContainer').style.display = 'block';
            document.getElementById('cdTags').textContent = content.tags;
        } else {
            document.getElementById('cdTagsContainer').style.display = 'none';
        }
        
        // Ownership note
        if (content.ownership_note) {
            document.getElementById('cdOwnershipContainer').style.display = 'block';
            document.getElementById('cdOwnership').textContent = content.ownership_note;
        } else {
            document.getElementById('cdOwnershipContainer').style.display = 'none';
        }
        
        // Media preview
        const mediaPreviewEl = document.getElementById('cdMediaPreview');
        if (content.media_url) {
            const fileType = content.file_type || '';
            if (fileType.startsWith('video/')) {
                mediaPreviewEl.innerHTML = `
                    <video controls style="width: 100%; max-height: 200px; object-fit: contain; background: #000;">
                        <source src="${escapeHtml(content.media_url)}" type="${escapeHtml(fileType)}">
                        Your browser does not support the video tag.
                    </video>
                `;
            } else if (fileType.startsWith('audio/')) {
                mediaPreviewEl.innerHTML = `
                    <audio controls style="width: 100%;">
                        <source src="${escapeHtml(content.media_url)}" type="${escapeHtml(fileType)}">
                        Your browser does not support the audio tag.
                    </audio>
                `;
            } else if (fileType.startsWith('image/')) {
                mediaPreviewEl.innerHTML = `
                    <img src="${escapeHtml(content.media_url)}" style="width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px;" alt="Content preview">
                `;
            } else {
                mediaPreviewEl.innerHTML = `
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                        <div class="text-center">
                            <i class="bi bi-file-earmark fs-1 text-white"></i>
                            <div class="text-white mt-2">File Preview</div>
                        </div>
                    </div>
                `;
            }
        } else {
            mediaPreviewEl.innerHTML = `
                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                    <div class="text-center">
                        <i class="bi bi-file-earmark fs-1 text-white"></i>
                        <div class="text-white mt-2">No Media Available</div>
                    </div>
                </div>
            `;
        }
        
        // Show modal
        const modalEl = document.getElementById('contentDetailsModal');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function() {
                const container = document.getElementById('cdMediaPreview');
                if (!container) return;
                container.querySelectorAll('video, audio').forEach(function(m) {
                    try { m.pause(); m.currentTime = 0; } catch (_) {}
                });
            }, { once: true });
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
        
    } catch (e) {
        console.error('Error loading content details:', e);
        showToast('Failed to load content details', 'error');
    }
}

function viewContent(contentId) {
    viewContentDetails(contentId);
}

function editContentFromModal() {
    if (currentContentId) {
        editContent(currentContentId);
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('contentDetailsModal'));
        if (modal) modal.hide();
    }
}

function deleteContentFromModal() {
    if (currentContentId) {
        // Close modal first
        const modal = bootstrap.Modal.getInstance(document.getElementById('contentDetailsModal'));
        if (modal) modal.hide();
        
        // Then delete
        deleteContent(currentContentId);
    }
}

async function deleteContent(contentId) {
    if (!confirm('Are you sure you want to delete this content? This action cannot be undone.')) {
        return;
    }
    
    try {
        const res = await fetch(`/kabaka/public/api/content.php?id=${contentId}`, {
            method: 'DELETE'
        });
        
        if (!res.ok) throw new Error('Failed to delete content');
        const data = await res.json();
        
        if (data.success) {
            showToast('Content deleted successfully', 'success');
            loadContentTable();
            loadOverviewAndRecent(); // Refresh overview stats
        } else {
            throw new Error(data.error || 'Delete failed');
        }
    } catch (e) {
        console.error('Content delete error:', e);
        showToast(e.message || 'Failed to delete content', 'error');
    }
}

// Add search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('contentSearch');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterContent();
            }, 500); // Debounce search
        });
    }
});

function escapeHtml(t) { 
    const d = document.createElement('div'); 
    d.textContent = t ?? ''; 
    return d.innerHTML; 
}

// Payment functionality
async function loadPaymentData() {
    try {
        if (__isUnloading) return;
        const ctrl = __makeCtrl();
        const commonOpts = { credentials: 'same-origin', signal: ctrl.signal };

        const walletRes = await fetch('/kabaka/public/api/payment.php?action=wallet', commonOpts);
        if (walletRes.ok) {
            const contentType = walletRes.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const walletData = await walletRes.json();
                if (walletData.success) updateWalletDisplay(walletData.data);
            } else {
                const responseText = await walletRes.text();
                console.error('Wallet API returned non-JSON response:', responseText.substring(0, 200));
                if (!__isUnloading) { if (typeof showToast === 'function') showToast('Authentication required. Please log in again.', 'error'); }
                __doneCtrl(ctrl);
                return;
            }
        }

        const eligibilityRes = await fetch('/kabaka/public/api/payment.php?action=eligibility', commonOpts);
        if (eligibilityRes.ok) {
            const contentType = eligibilityRes.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const eligibilityData = await eligibilityRes.json();
                if (eligibilityData.success) updateEligibilityDisplay(eligibilityData.data);
            }
        }

        const settingsRes = await fetch('/kabaka/public/api/payment.php?action=settings', commonOpts);
        if (settingsRes.ok) {
            const contentType = settingsRes.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const settingsData = await settingsRes.json();
                if (settingsData.success) updateSettingsDisplay(settingsData.data);
            }
        }

        const transactionsRes = await fetch('/kabaka/public/api/payment.php?action=transactions', commonOpts);
        if (transactionsRes.ok) {
            const contentType = transactionsRes.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const transactionsData = await transactionsRes.json();
                if (transactionsData.success) updateTransactionsDisplay(transactionsData.data);
            }
        }

        __doneCtrl(ctrl);
    } catch (e) {
        if (__isAbort(e) || __isUnloading) return;
        console.error('Payment data load error:', e);
        if (typeof showToast === 'function') showToast('Failed to load payment data. Please check your login status.', 'error');
    }
}

function updateWalletDisplay(data) {
    // Show available money for withdrawal (earned - withdrawn)
    const availableForWithdrawal = (data.available_for_withdrawal || 0) / 100; // Convert cents to dollars
    const totalEarned = (data.earned_from_views || 0) / 100; // Total earned from views
    const totalClaimed = (data.total_claimed || 0) / 100; // Total claimed
    // Cache available amount (in cents) for other sections (e.g., eligibility min-balance check)
    try { window.__availableForWithdrawalCents = data.available_for_withdrawal || 0; } catch (_) {}
    
    document.getElementById('walletBalance').textContent = '$' + availableForWithdrawal.toFixed(2);
    document.getElementById('pendingAmount').textContent = 'Pending: $' + (data.pending_cents / 100).toFixed(2);
    document.getElementById('totalEarnings').textContent = 'Total Earned: $' + totalEarned.toFixed(2) + ' | Claimed: $' + totalClaimed.toFixed(2);
}

function updateEligibilityDisplay(data) {
    // Account verification
    const verificationIcon = document.getElementById('verificationIcon');
    const verificationStatus = document.getElementById('verificationStatus');
    const verificationBtn = document.getElementById('verificationBtn');
    if (data.is_verified) {
        verificationIcon.style.color = '#10b981';
        verificationStatus.textContent = 'Verified';
        verificationBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i><span>Verified</span>';
        verificationBtn.disabled = true;
        verificationBtn.style.fontSize = '0.75rem';
        verificationBtn.style.padding = '0.25rem 0.5rem';
    } else {
        verificationIcon.style.color = '#ef4444';
        verificationStatus.textContent = 'Not Verified';
        verificationBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i><span>Request</span>';
        verificationBtn.disabled = false;
        verificationBtn.style.fontSize = '0.75rem';
        verificationBtn.style.padding = '0.25rem 0.5rem';
    }

    // Monetization
    const monetizationIcon = document.getElementById('monetizationIcon');
    const monetizationStatus = document.getElementById('monetizationStatus');
    const monetizationBtn = document.getElementById('monetizationBtn');
    if (data.monetization_enabled) {
        monetizationIcon.style.color = '#10b981';
        monetizationStatus.textContent = 'Enabled';
        monetizationBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i><span>Enabled</span>';
        monetizationBtn.disabled = true;
        monetizationBtn.style.fontSize = '0.75rem';
        monetizationBtn.style.padding = '0.25rem 0.5rem';
    } else {
        monetizationIcon.style.color = '#ef4444';
        monetizationStatus.textContent = 'Disabled';
        monetizationBtn.innerHTML = '<i class="bi bi-currency-dollar me-1"></i><span>Request</span>';
        monetizationBtn.disabled = false;
        monetizationBtn.style.fontSize = '0.75rem';
        monetizationBtn.style.padding = '0.25rem 0.5rem';
    }

    // Payout destination
    const payoutIcon = document.getElementById('payoutIcon');
    const payoutStatus = document.getElementById('payoutStatus');
    if (data.payout_destination) {
        payoutIcon.style.color = '#10b981';
        payoutStatus.textContent = 'Set';
        document.getElementById('payoutDestination').value = data.payout_destination;
    } else {
        payoutIcon.style.color = '#ef4444';
        payoutStatus.textContent = 'Not Set';
    }

    // Minimum balance check
    const minimumIcon = document.getElementById('minimumIcon');
    const minimumStatus = document.getElementById('minimumStatus');
    // Use available-for-withdrawal (from views) if we have it; fallback to wallet balance
    const centsAvailable = (typeof window !== 'undefined' && typeof window.__availableForWithdrawalCents === 'number')
        ? window.__availableForWithdrawalCents
        : (data.balance_cents || 0);
    const currentBalance = centsAvailable / 100;
    const minRequired = data.min_withdrawal_amount / 100;
    
    if (currentBalance >= minRequired) {
        minimumIcon.style.color = '#10b981';
        minimumStatus.textContent = 'Met ($' + minRequired.toFixed(2) + ')';
    } else {
        minimumIcon.style.color = '#ef4444';
        minimumStatus.textContent = 'Not Met ($' + minRequired.toFixed(2) + ')';
    }

    // Update withdrawal button state
    updateWithdrawalButtonState(data);
}

function updateSettingsDisplay(data) {
    document.getElementById('minWithdrawal').textContent = '$' + (data.min_withdrawal_amount / 100).toFixed(2);
    document.getElementById('minAmountDisplay').textContent = (data.min_withdrawal_amount / 100).toFixed(2);
    document.getElementById('platformFee').textContent = data.platform_fee_percent + '%';
    document.getElementById('processingFee').textContent = '$' + (data.processing_fee / 100).toFixed(2);
    
    // Update withdrawal process message
    const processMsg = document.getElementById('withdrawalProcess');
    if (data.auto_monthly_payouts) {
        processMsg.textContent = 'Automatic monthly payouts are enabled. Withdrawal will be processed automatically if eligible.';
    } else {
        processMsg.textContent = 'Manual payout processing. Withdrawal will be reviewed by admin before processing.';
    }
}

function updateTransactionsDisplay(transactions) {
    const tbody = document.getElementById('transactionsTableBody');
    if (!transactions || transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary">No transactions yet</td></tr>';
        return;
    }

    tbody.innerHTML = transactions.map(tx => `
        <tr>
            <td class="text-center text-secondary small">${new Date(tx.created_at).toLocaleDateString()}</td>
            <td class="text-center">
                <span class="badge ${tx.source === 'tip' ? 'bg-success' : 'bg-primary'}">
                    ${tx.source === 'tip' ? 'Tip' : 'Payout'}
                </span>
            </td>
            <td class="text-center text-white">$${(tx.amount_cents / 100).toFixed(2)}</td>
            <td class="text-center">
                <span class="badge ${tx.status === 'completed' ? 'bg-success' : tx.status === 'pending' ? 'bg-warning' : tx.status === 'failed' ? 'bg-danger' : 'bg-success'}">
                    ${tx.status || 'completed'}
                </span>
            </td>
            <td class="text-center text-secondary small">${tx.tx_id || '—'}</td>
        </tr>
    `).join('');
}

function updateWithdrawalButtonState(data) {
    const btn = document.getElementById('requestWithdrawalBtn');
    const amountInput = document.getElementById('withdrawalAmount');
    
    // Check if user is eligible
    const isEligible = data.is_verified && 
                      data.monetization_enabled && 
                      data.payout_destination && 
                      (data.balance_cents >= data.min_withdrawal_amount);

    if (isEligible) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-up-circle me-1"></i> Request Withdrawal';
    } else {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Not Eligible';
    }

    // Update amount input validation
    amountInput.min = data.min_withdrawal_amount / 100;
    amountInput.max = data.balance_cents / 100;
}

async function requestWithdrawal() {
    const amount = parseFloat(document.getElementById('withdrawalAmount').value);
    const minAmount = parseFloat(document.getElementById('minAmountDisplay').textContent);
    
    if (!amount || amount < minAmount) {
        showToast('Please enter a valid amount above the minimum', 'error');
        return;
    }

    if (!confirm(`Request withdrawal of $${amount.toFixed(2)}?`)) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('amount', amount);
        
        const res = await fetch('/kabaka/public/api/payment.php?action=withdraw', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        if (!res.ok) throw new Error('Failed to request withdrawal');
        const data = await res.json();
        
        if (data.success) {
            showToast('Withdrawal requested successfully', 'success');
            document.getElementById('withdrawalAmount').value = '';
            loadPaymentData(); // Refresh data
        } else {
            throw new Error(data.error || 'Withdrawal request failed');
        }
    } catch (e) {
        console.error('Withdrawal request error:', e);
        showToast(e.message || 'Failed to request withdrawal', 'error');
    }
}

// Add event listener for amount input validation
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('withdrawalAmount');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            const btn = document.getElementById('requestWithdrawalBtn');
            const amount = parseFloat(this.value);
            const minAmount = parseFloat(document.getElementById('minAmountDisplay').textContent);
            
            if (amount && amount >= minAmount) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-up-circle me-1"></i> Request Withdrawal';
            } else {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Not Eligible';
            }
        });
    }
});

// Modal functions
function openVerificationModal() {
    // Load current user data and requirements
    loadVerificationData();
    const modal = new bootstrap.Modal(document.getElementById('verificationModal'));
    modal.show();
}

function openMonetizationModal() {
    // Load current user data and requirements
    loadMonetizationData();
    const modal = new bootstrap.Modal(document.getElementById('monetizationModal'));
    modal.show();
}

async function loadVerificationData() {
    try {
        // Load creator requirements
        const requirementsRes = await fetch('/kabaka/public/api/payment.php?action=requirements', {
            credentials: 'same-origin'
        });
        if (requirementsRes.ok) {
            const requirementsData = await requirementsRes.json();
            if (requirementsData.success) {
                // Update requirements display
                document.getElementById('verificationMinPosts').textContent = requirementsData.data.min_content_posts + ' posts';
                document.getElementById('verificationMinAge').textContent = requirementsData.data.min_account_age_days + ' days';
                
                // Update current status
                document.getElementById('verificationCurrentPosts').textContent = requirementsData.data.current_posts + ' posts';
                document.getElementById('verificationCurrentAge').textContent = requirementsData.data.account_age_days + ' days';
                document.getElementById('verificationEmailStatus').textContent = requirementsData.data.email_verified ? 'Verified' : 'Not Verified';
            }
        }
    } catch (e) {
        console.error('Failed to load verification data:', e);
    }
}

async function loadMonetizationData() {
    try {
        // Load monetization requirements
        const requirementsRes = await fetch('/kabaka/public/api/payment.php?action=requirements', {
            credentials: 'same-origin'
        });
        if (requirementsRes.ok) {
            const requirementsData = await requirementsRes.json();
            if (requirementsData.success) {
                // Update requirements display
                document.getElementById('monetizationMinFollowers').textContent = requirementsData.data.min_followers_for_pay + ' followers';
                document.getElementById('monetizationMinViews').textContent = requirementsData.data.min_views_for_payment + ' views';
                
                // Update current status
                document.getElementById('monetizationCurrentFollowers').textContent = requirementsData.data.current_followers + ' followers';
                document.getElementById('monetizationCurrentViews').textContent = requirementsData.data.current_views + ' views';
                document.getElementById('monetizationVerificationStatus').textContent = requirementsData.data.is_verified ? 'Verified' : 'Not Verified';
            }
        }
    } catch (e) {
        console.error('Failed to load monetization data:', e);
    }
}

async function submitVerificationRequest() {
    try {
        const response = await fetch('/kabaka/public/api/payment.php?action=request_verification', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'same-origin',
            body: ''
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                showToast(data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('verificationModal')).hide();
                // Reload payment data to update status
                loadPaymentData();
            } else {
                // Show detailed error message with missing requirements
                let errorMsg = data.error;
                if (data.missing_requirements && data.missing_requirements.length > 0) {
                    errorMsg += '\n\nMissing requirements:\n• ' + data.missing_requirements.join('\n• ');
                }
                showToast(errorMsg, 'error');
            }
        }
    } catch (e) {
        console.error('Verification request error:', e);
        showToast('Network error. Please try again.', 'error');
    }
}

async function submitMonetizationRequest() {
    try {
        const response = await fetch('/kabaka/public/api/payment.php?action=request_monetization', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'same-origin',
            body: ''
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                showToast(data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('monetizationModal')).hide();
                // Reload payment data to update status
                loadPaymentData();
            } else {
                // Show detailed error message with missing requirements
                let errorMsg = data.error;
                if (data.missing_requirements && data.missing_requirements.length > 0) {
                    errorMsg += '\n\nMissing requirements:\n• ' + data.missing_requirements.join('\n• ');
                }
                showToast(errorMsg, 'error');
            }
        }
    } catch (e) {
        console.error('Monetization request error:', e);
        showToast('Network error. Please try again.', 'error');
    }
}

// Call loaders
loadOverviewAndRecent();
loadSettings();
loadContentTable();
loadPaymentData();
	</script>

<footer class="py-3 mt-3">
    <div class="container px-3 px-md-4 ps-lg-5 container-centered">
        <div class="row g-4">
            <div class="d-none d-lg-block col-lg-1"></div>
            <div class="col-lg-2 col-md-6">
                <h6 class="text-white mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#overviewTab" class="text-secondary text-decoration-none small" onclick="selectTab('#overviewTab');return false;">Overview</a></li>
                    <li class="mb-2"><a href="#uploadsTab" class="text-secondary text-decoration-none small" onclick="selectTab('#uploadsTab');return false;">Upload</a></li>
                    <li class="mb-2"><a href="#contentTab" class="text-secondary text-decoration-none small" onclick="selectTab('#contentTab');return false;">My Content</a></li>
                    <li class="mb-2"><a href="#settingsTab" class="text-secondary text-decoration-none small" onclick="selectTab('#settingsTab');return false;">Settings</a></li>
                </ul>
					</div>
            <div class="col-lg-2 col-md-6">
                <h6 class="text-white mb-3">Resources</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="guide.php" class="text-secondary text-decoration-none small">Creator Guide</a></li>
                    <li class="mb-2"><a href="guide.php#community" class="text-secondary text-decoration-none small">Community</a></li>
                    <li class="mb-2"><a href="guide.php#support" class="text-secondary text-decoration-none small">Contact Support</a></li>
                </ul>
						</div>
            <div class="col-lg-2 col-md-6">
                <h6 class="text-white mb-3">Payment</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#paymentTab" class="text-secondary text-decoration-none small" onclick="selectTab('#paymentTab');return false;"><i class="bi bi-cash-coin me-2" style="color: #10b981;"></i>Withdraw Funds</a></li>
                    <li class="mb-2"><a href="#paymentTab" class="text-secondary text-decoration-none small" onclick="selectTab('#paymentTab');return false;"><i class="bi bi-graph-up me-2" style="color: #10b981;"></i>Earnings</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="text-white mb-3">Stay Updated</h6>
                <p class="text-secondary small mb-3">Get product news and tips.</p>
                <form class="d-flex" onsubmit="event.preventDefault(); showToast('Subscribed', 'success');">
                    <input type="email" id="creatorNewsletterEmail" class="form-control form-control me-2" placeholder="you@example.com" required>
                    <button type="submit" class="btn btn-danger btn" onclick="(async function(ev){ try { ev.preventDefault(); var v=document.getElementById('creatorNewsletterEmail').value.trim(); if(!v){ showToast('Please enter your email','error'); return; } var res=await fetch('/kabaka/public/api/newsletter.php',{ method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ email: v })}); var data=await res.json(); if(res.ok && data && data.ok){ showToast('Subscribed','success'); document.getElementById('creatorNewsletterEmail').value=''; } else { showToast((data && data.error) || 'Subscription failed','error'); } } catch(e){ showToast('Network error','error'); } })(event)"><i class="bi bi-envelope"></i></button>
                </form>
				</div>
				</div>
        <hr class="my-2" style="border-color: rgba(255,255,255,.15);">
        <div class="row align-items-center">
            <div class="col-12 text-center">
                <p class="text-secondary small mb-0">© <span id="yearNow"></span> Kabaka. All rights reserved.</p>
                <p class="text-secondary small mb-0 mt-2"><i class="bi bi-shield-check me-1"></i>Creator Dashboard</p>
            </div>
        </div>
    </div>
</footer>
<script>document.getElementById('yearNow').textContent = new Date().getFullYear();</script>
</body>
</html>


