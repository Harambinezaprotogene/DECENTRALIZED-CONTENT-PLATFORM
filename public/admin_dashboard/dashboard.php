<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    $role = htmlspecialchars($_SESSION['role'] ?? 'NOT SET', ENT_QUOTES, 'UTF-8');
    echo "<div style=\"min-height:100vh;display:flex;align-items:center;justify-content:center;\">";
    echo "  <div style=\"max-width:640px;width:100%;background:rgba(17,24,39,.9);color:#fff;border:1px solid rgba(255,255,255,.12);border-radius:16px;box-shadow:0 12px 36px rgba(0,0,0,.5);overflow:hidden\">";
    echo "    <div style=\"padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.08);background:linear-gradient(180deg,rgba(239,68,68,.25),rgba(239,68,68,.05))\">";
    echo "      <div style=\"display:flex;align-items:center;gap:12px;justify-content:center\">";
    echo "        <span style=\"font-size:28px\">🚫</span>";
    echo "        <h2 style=\"margin:0;font-size:22px;font-weight:700;letter-spacing:.3px\">Access Denied</h2>";
    echo "      </div>";
    echo "    </div>";
    echo "    <div style=\"padding:22px 24px;text-align:center\">";
    echo "      <p style=\"margin:.25rem 0;color:#fca5a5\">Current role: <strong>" . $role . "</strong></p>";
    echo "      <p style=\"margin:.5rem 0 1rem;color:#d1d5db\">You need admin privileges to access this dashboard.</p>";
    echo "      <div style=\"display:flex;gap:10px;justify-content:center;flex-wrap:wrap\">";
    echo "        <a href='login.php' style=\"display:inline-block;padding:8px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.2);color:#fff;text-decoration:none;background:linear-gradient(180deg,#ef4444,#b91c1c);box-shadow:0 4px 14px rgba(239,68,68,.35)\">Admin Login</a>";
    echo "        <a href='/kabaka/public/viewer_dashboard/login.php' style=\"display:inline-block;padding:8px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.2);color:#fff;text-decoration:none;background:linear-gradient(180deg,#374151,#1f2937)\">Viewer Login</a>";
    echo "      </div>";
    echo "    </div>";
    echo "  </div>";
    echo "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kabaka</title>
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

        /* Impact tab cards */
        .impact-card { 
            background: rgba(255,255,255,.06); 
            border: 1px solid rgba(255,255,255,.12); 
            box-shadow: 0 8px 32px rgba(0,0,0,.25);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .impact-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,.35);
        }
        
        .verification-card {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.1), rgba(255,255,255,.06));
            border: 1px solid transparent;
        }
        
        .monetization-card {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(255,255,255,.06));
            border: 1px solid transparent;
        }
        
        .total-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(255,255,255,.06));
            border: 1px solid transparent;
        }
        
        .impact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        }
        .receipts-table .text-truncate { max-width: 280px; }
        /* Widen numeric columns in Impact table: Posts, Age, Followers, Views */
        #impact .receipts-table th:nth-child(4),
        #impact .receipts-table td:nth-child(4) { min-width: 110px; }
        #impact .receipts-table th:nth-child(5),
        #impact .receipts-table td:nth-child(5) { min-width: 130px; }
        #impact .receipts-table th:nth-child(6),
        #impact .receipts-table td:nth-child(6) { min-width: 130px; }
        #impact .receipts-table th:nth-child(7),
        #impact .receipts-table td:nth-child(7) { min-width: 120px; }

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
        /* Receipts dark theme */
        .receipts-card { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15); box-shadow: 0 8px 32px rgba(0,0,0,.2); }
        .receipts-table thead th { background: rgba(255,255,255,.06); color: rgba(255,255,255,.85); border-bottom: 1px solid rgba(255,255,255,.1); }
        .receipts-table tbody tr { transition: background-color .2s ease; }
        .receipts-table tbody tr:hover { background: rgba(255,255,255,.06); }
        .receipts-table td, .receipts-table th { color: rgba(255,255,255,.9); border-color: rgba(255,255,255,.08); }
        .receipts-table a.link { color: #93c5fd; text-decoration: underline; }
        .badge.bg-danger-subtle { background: linear-gradient(45deg, var(--admin-red), var(--admin-red-dark)); border: 1px solid rgba(220,38,38,.4); }
        
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

         .form-select option {
             background: #1a1a1a;
             color: white;
         }

         /* Prevent this card from stretching to fill height */
         .admin-card.auto-height {
             height: auto;
         }
+        /* Small-screen alignment with creator dashboard */
+        @media (max-width: 576px) {
+            .nav.nav-tabs { overflow-x: auto; white-space: nowrap; flex-wrap: nowrap; }
+            .nav-tabs .nav-link { padding: .5rem .75rem; font-size: .9rem; }
+            .dropdown > .btn { padding: .35rem .6rem; font-size: .9rem; }
+            .admin-card { padding: 12px; }
+            .table { font-size: .9rem; }
+            .input-group-text { padding: .35rem .5rem; }
+            .form-select, .form-control { padding: .4rem .55rem; font-size: .9rem; }
+            footer #adminNewsletterForm { max-width: 100% !important; }
+        }
 
</style>
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
</style>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

            <!-- Main Content -->
        <div class="container-fluid pt-4">
            <div class="row">
                <div class="col-lg-1"></div> <!-- Left gap -->
                <div class="col-lg-10"> <!-- Main content -->


        <!-- Tabs Navigation with Admin Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center px-3">
                    <ul class="nav nav-tabs" id="adminTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                                <i class="bi bi-speedometer2 me-2"></i>Overview
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                                <i class="bi bi-people-fill me-2"></i>Users
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content" type="button" role="tab">
                                <i class="bi bi-collection-play-fill me-2"></i>Content
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">
                                <i class="bi bi-gear-fill me-2"></i>Settings
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="receipts-tab" data-bs-toggle="tab" data-bs-target="#receipts" type="button" role="tab">
                                <i class="bi bi-receipt-cutoff me-2"></i>Muted Receipts
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="impact-tab" data-bs-toggle="tab" data-bs-target="#impact" type="button" role="tab">
                                <i class="bi bi-exclamation-triangle me-2"></i>Impact
                            </button>
                        </li>
                    </ul>
                    
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['email'] ?? 'Admin') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="document.getElementById('settings-tab').click(); return false;"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/kabaka/public/api/auth.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content px-4" id="adminTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <!-- Stats Overview -->
        <div class="row g-4 mb-5">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stat-card">
                            <div class="text-center">
                                <i class="bi bi-people-fill fs-2 text-white mb-2"></i>
                                <div class="stat-value" id="totalUsers">—</div>
                                <div class="stat-label">Users</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stat-card">
                            <div class="text-center">
                                <i class="bi bi-person-badge-fill fs-2 text-white mb-2"></i>
                                <div class="stat-value" id="totalCreators">—</div>
                                <div class="stat-label">Creators</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stat-card">
                            <div class="text-center">
                                <i class="bi bi-collection-play-fill fs-2 text-white mb-2"></i>
                                <div class="stat-value" id="totalContent">—</div>
                                <div class="stat-label">Content</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stat-card">
                            <div class="text-center">
                                <i class="bi bi-eye-fill fs-2 text-white mb-2"></i>
                                <div class="stat-value" id="totalViews">—</div>
                                <div class="stat-label">Views</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stat-card">
                            <div class="text-center">
                                <i class="bi bi-heart-fill fs-2 text-white mb-2"></i>
                                <div class="stat-value" id="totalLikes">—</div>
                                <div class="stat-label">Likes</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="stat-card">
                            <div class="text-center">
                                <i class="bi bi-chat-fill fs-2 text-white mb-2"></i>
                                <div class="stat-value" id="totalComments">—</div>
                                <div class="stat-label">Comments</div>
                            </div>
                        </div>
                    </div>
                </div>

                                 <!-- Platform Overview -->
                 <div class="row g-4">
                     <div class="col-lg-5">
                         <div class="admin-card">
                             <div class="d-flex justify-content-between align-items-center mb-4">
                                 <h5 class="text-white mb-0">
                                     <i class="bi bi-graph-up me-2"></i>Recent Activity
                                 </h5>
                                 <button class="btn btn-outline-light btn-sm rounded-pill" onclick="showAllActivity()">
                                     <i class="bi bi-list-ul me-1"></i>View all
                                 </button>
                             </div>
                             <div class="activity-list" id="recentActivityList">
                                 <div class="text-secondary small">Loading recent activity...</div>
                             </div>
                             
                         </div>
                     </div>
                     
                     <div class="col-lg-5">
                         <div class="admin-card auto-height">
                             <h5 class="text-white mb-4">
                                 <i class="bi bi-speedometer2 me-2"></i>Quick Actions
                             </h5>
                             <div class="row g-3">
                                 <div class="col-6">
                                     <button class="btn btn-outline-light w-100 d-flex flex-column align-items-center justify-content-center py-2 rounded-pill" onclick="refreshDashboard()">
                                         <i class="bi bi-arrow-clockwise fs-5 mb-1"></i>
                                         <span class="small">Refresh</span>
                                     </button>
                                 </div>
                                 <div class="col-6">
                                     <button class="btn btn-outline-light w-100 d-flex flex-column align-items-center justify-content-center py-2 rounded-pill" onclick="goToTab('#users')">
                                         <i class="bi bi-people-fill fs-5 mb-1"></i>
                                         <span class="small">Users</span>
                                     </button>
                                 </div>
                                 <div class="col-6">
                                     <button class="btn btn-outline-light w-100 d-flex flex-column align-items-center justify-content-center py-2 rounded-pill" onclick="goToTab('#content')">
                                         <i class="bi bi-collection-play-fill fs-5 mb-1"></i>
                                         <span class="small">Content</span>
                                     </button>
                                 </div>
                                 <div class="col-6">
                                     <button class="btn btn-outline-light w-100 d-flex flex-column align-items-center justify-content-center py-2 rounded-pill" onclick="goToTab('#settings')">
                                         <i class="bi bi-gear-fill fs-5 mb-1"></i>
                                         <span class="small">Settings</span>
                                     </button>
                                 </div>

                             </div>
                             
                         </div>
                     </div>
                 </div>
                 
                 
            </div>

            <!-- Muted Receipts Tab -->
            <div class="tab-pane fade" id="receipts" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0"><i class="bi bi-receipt-cutoff me-2"></i>Blockchain Receipts</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <input id="receiptsSearch" class="form-control form-control" placeholder="Search creator, email, tx or payment" style="width:260px;">
                        <select id="receiptsStatus" class="form-select form-select" style="width:160px;">
                            <option value="">All statuses</option>
                            <option value="confirmed">confirmed</option>
                            <option value="pending">pending</option>
                            <option value="failed">failed</option>
                        </select>
                        <button id="receiptsRefreshBtn" class="btn btn-sm btn-danger"><i class="bi bi-arrow-clockwise"></i></button>
                    </div>
                </div>
                <div class="card p-3 receipts-card mt-2">
                    <br>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle receipts-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Creator</th>
                                    <th>Payment</th>
                                    <th>Amount (wei)</th>
                                    <th>Tx</th>
                                    <th>Block</th>
                                    <th>Status</th>
                                    <th>When</th>
                                </tr>
                            </thead>
                            <tbody id="receiptsRows">
                                <tr><td colspan="8" class="text-center text-secondary">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-secondary" id="receiptsInfo"></small>
                        <div class="btn-group">
                            <button class="btn btn-outline-light btn-sm" id="receiptsPrev">Prev</button>
                            <button class="btn btn-outline-light btn-sm" id="receiptsNext">Next</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Impact Tab -->
            <div class="tab-pane fade" id="impact" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0"><i class="bi bi-exclamation-triangle me-2"></i>Impact</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <button id="impactRefreshBtn" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-clockwise"></i></button>
                        <button id="impactUnverifyBtn" class="btn btn-sm btn-danger" disabled><i class="bi bi-person-x"></i> Unverify Selected</button>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card impact-card verification-card">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="bi bi-shield-exclamation text-danger me-2"></i>
                                    <span class="text-danger fw-semibold">Verification Impact</span>
                                </div>
                                <div class="display-4 fw-bold text-white mb-1" id="verificationImpactCount">0</div>
                                <div class="text-secondary small">Users who would lose verification</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card impact-card monetization-card">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="bi bi-currency-dollar text-warning me-2"></i>
                                    <span class="text-warning fw-semibold">Monetization Impact</span>
                                </div>
                                <div class="display-4 fw-bold text-white mb-1" id="monetizationImpactCount">0</div>
                                <div class="text-secondary small">Users who would lose monetization</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card impact-card total-card">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="bi bi-people text-info me-2"></i>
                                    <span class="text-info fw-semibold">Total Affected</span>
                                </div>
                                <div class="display-4 fw-bold text-white mb-1" id="totalImpactCount">0</div>
                                <div class="text-secondary small">Users affected by changes</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card p-3 receipts-card mt-4">
                    <div class="table-responsive"><br>
                        <table class="table table-sm align-middle receipts-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllImpact" class="form-check-input"></th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Posts</th>
                                    <th>Age (days)</th>
                                    <th>Followers</th>
                                    <th>Views</th>
                                    <th>Impact</th>
                                </tr>
                            </thead>
                            <tbody id="impactRows">
                                <tr><td colspan="8" class="text-center text-secondary">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Receipt Payment Details Modal -->
            <div class="modal fade" id="receiptPaymentModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background:#0f0f0f;border:1px solid rgba(255,255,255,.15);">
                  <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div id="receiptPaymentBody" class="small text-secondary">Loading…</div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
                         <!-- Users Tab -->
             <div class="tab-pane fade" id="users" role="tabpanel">
                 <div class="admin-card">
                     <!-- Header with Actions -->
                     <div class="d-flex justify-content-between align-items-center mb-4">
                         <h5 class="text-white mb-0">
                             <i class="bi bi-people-fill me-2"></i>User Management
                         </h5>
                         <div class="d-flex gap-2">
                             <button class="btn btn-outline-light" onclick="exportUsers()" title="Export Users">
                                 <i class="bi bi-download me-1"></i>Export
                             </button>
                             <button class="btn btn-primary" onclick="openAddUserModal()">
                                 <i class="bi bi-plus-circle me-1"></i>Add User
                             </button>
                         </div>
                     </div>

                     <!-- Search and Filters -->
                     <div class="row g-3 mb-4">
                         <div class="col-lg-4">
                             <div class="input-group">
                                 <span class="input-group-text bg-transparent border-secondary text-white border-end-0">
                                     <i class="bi bi-search"></i>
                                 </span>
                                 <input type="text" class="form-control border-start-0" id="userSearch" placeholder="Search users..." onkeyup="filterUsers()" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                             </div>
                         </div>
                         <div class="col-lg-3">
                             <select class="form-select" id="roleFilter" onchange="filterUsers()">
                                 <option value="">All Roles</option>
                                 <option value="admin">Admin</option>
                                 <option value="creator">Creator</option>
                                 <option value="viewer">Viewer</option>
                             </select>
                         </div>
                         <div class="col-lg-3">
                             <select class="form-select" id="userStatusFilter" onchange="filterUsers()">
                                 <option value="">All Status</option>
                                 <option value="active">Active</option>
                                 <option value="inactive">Inactive</option>
                                 <option value="banned">Banned</option>
                             </select>
                         </div>
                         <div class="col-lg-2">
                             <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                 <i class="bi bi-x-circle me-1"></i>Clear
                             </button>
                         </div>
                     </div>

                     <!-- Results Summary -->
                     <div class="d-flex justify-content-between align-items-center mb-3">
                         <div class="text-secondary small">
                             <span id="userResultsCount">0</span> users found
                         </div>
                                                   <div class="d-flex align-items-center gap-2">
                              <label class="text-secondary small mb-0">Show:</label>
                              <select class="form-select form-select-sm" id="pageSize" onchange="changePageSize()" style="width: auto;">
                                  <option value="10">10</option>
                                  <option value="25" selected>25</option>
                                  <option value="50">50</option>
                                  <option value="100">100</option>
                                  <option value="250">250</option>
                                  <option value="500">500</option>
                                  <option value="1000">1000</option>
                                  <option value="all">All</option>
                              </select>
                          </div>
                     </div>

                     <!-- Enhanced Table -->
                     <div class="table-responsive">
                         <table class="table table-hover">
                             <thead>
                                 <tr>
                                     <th>
                                         <i class="bi bi-person me-1"></i>User
                                     </th>
                                     <th>
                                         <i class="bi bi-shield me-1"></i>Role
                                     </th>
                                     <th>
                                         <i class="bi bi-envelope me-1"></i>Email
                                     </th>
                                     <th class="sortable" onclick="sortTable('joined')">
                                         <i class="bi bi-calendar me-1"></i>Joined
                                         <i class="bi bi-arrow-down-up ms-1 text-secondary"></i>
                                     </th>
                                     <th>
                                         <i class="bi bi-circle me-1"></i>Status
                                     </th>
                                     <th><i class="bi bi-gear me-1"></i>Actions</th>
                                 </tr>
                             </thead>
                             <tbody id="usersTable">
                                 <tr>
                                     <td colspan="6" class="text-center text-secondary py-4">
                                         <i class="bi bi-hourglass-split me-2"></i>Loading users...
                                     </td>
                                 </tr>
                             </tbody>
                         </table>
                     </div>

                     <!-- Pagination -->
                     <nav aria-label="Users pagination" class="mt-4">
                         <ul class="pagination justify-content-center" id="userPagination">
                             <!-- Pagination will be generated here -->
                         </ul>
                     </nav>
                 </div>
             </div>

                         <!-- Content Tab -->
             <div class="tab-pane fade" id="content" role="tabpanel">
                 <div class="admin-card">
                     <!-- Header with Actions -->
                     <div class="d-flex justify-content-between align-items-center mb-4">
                         <h5 class="text-white mb-0">
                             <i class="bi bi-collection-play-fill me-2"></i>Content Moderation
                         </h5>
                         <div class="d-flex gap-2">
                             <button class="btn btn-outline-light" onclick="exportContent()" title="Export Content" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3);">
                                 <i class="bi bi-download me-1"></i>Export
                             </button>
                         </div>
                     </div>

                     <!-- Search and Filters -->
                     <div class="row g-3 mb-4">
                         <div class="col-lg-4">
                             <div class="input-group">
                                 <span class="input-group-text bg-transparent border-secondary text-white border-end-0">
                                     <i class="bi bi-search"></i>
                                 </span>
                                 <input type="text" class="form-control border-start-0" id="contentSearch" placeholder="Search content..." onkeyup="filterContent()" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                             </div>
                         </div>
                         <div class="col-lg-3">
                             <select class="form-select" id="categoryFilter" onchange="filterContent()">
                                 <option value="">All Categories</option>
                                 <option value="video">Video</option>
                                 <option value="music">Music</option>
                                 <option value="audio">Audio</option>
                                 <option value="podcast">Podcast</option>
                                 <option value="image">Image</option>
                                 <option value="other">Other</option>
                             </select>
                         </div>
                         <div class="col-lg-3">
                             <select class="form-select" id="statusFilter" onchange="filterContent();">
                                 <option value="">All Status</option>
                                 <option value="approved">Approved</option>
                                 <option value="pending">Pending</option>
                                 <option value="rejected">Rejected</option>
                             </select>
                         </div>
                         <div class="col-lg-2">
                             <button class="btn btn-outline-secondary w-100" onclick="clearContentFilters()">
                                 <i class="bi bi-x-circle me-1"></i>Clear
                             </button>
                         </div>
                     </div>

                     <!-- Results Summary -->
                     <div class="d-flex justify-content-between align-items-center mb-3">
                         <div class="text-secondary small">
                             <span id="contentResultsCount">0</span> content items found
                         </div>
                         <div class="d-flex align-items-center gap-2">
                             <label class="text-secondary small mb-0">Show:</label>
                             <select class="form-select form-select-sm" id="contentPageSize" onchange="changeContentPageSize()" style="width: auto;">
                                 <option value="10" selected>10</option>
                                 <option value="25">25</option>
                                 <option value="50">50</option>
                                 <option value="100">100</option>
                                 <option value="250">250</option>
                                 <option value="500">500</option>
                                 <option value="1000">1000</option>
                                 <option value="all">All</option>
                             </select>
                         </div>
                     </div>

                     <!-- Enhanced Table -->
                     <div class="table-responsive">
                         <table class="table table-hover">
                             <thead>
                                 <tr>
                                     <th>
                                         <i class="bi bi-file-text me-1"></i>Title
                                     </th>
                                     <th>
                                         <i class="bi bi-person-badge me-1"></i>Creator
                                     </th>
                                     <th>
                                         <i class="bi bi-tag me-1"></i>Category
                                     </th>
                                     <th>
                                         <i class="bi bi-eye me-1"></i>Status
                                     </th>
                                     <th class="sortable" onclick="sortContentTable('views')">
                                         <i class="bi bi-bar-chart me-1"></i>Views
                                         <i class="bi bi-arrow-down-up ms-1 text-secondary"></i>
                                     </th>
                                     <th class="sortable" onclick="sortContentTable('created')">
                                         <i class="bi bi-calendar me-1"></i>Created
                                         <i class="bi bi-arrow-down-up ms-1 text-secondary"></i>
                                     </th>
                                     <th><i class="bi bi-gear me-1"></i>Actions</th>
                                 </tr>
                             </thead>
                             <tbody id="contentTable">
                                 <tr>
                                     <td colspan="7" class="text-center text-secondary py-4">
                                         <i class="bi bi-hourglass-split me-2"></i>Loading content...
                                     </td>
                                 </tr>
                             </tbody>
                         </table>
                     </div>

                     <!-- Pagination -->
                     <nav aria-label="Content pagination" class="mt-4">
                         <ul class="pagination justify-content-center" id="contentPagination">
                             <!-- Pagination will be generated here -->
                         </ul>
                     </nav>
                 </div>

                 <!-- Content Tab Button to view flagged content -->
                 <div class="d-flex justify-content-end mb-2">
                     <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#flaggedModal">
                         <i class="bi bi-flag me-1"></i>Flagged Content
                     </button>
                 </div>

                 <!-- Flagged Content Modal -->
                 <div class="modal fade" id="flaggedModal" tabindex="-1" aria-hidden="true">
                     <div class="modal-dialog modal-xl">
                         <div class="modal-content" style="background: rgba(13, 19, 33, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,.16);">
                             <div class="modal-header border-secondary">
                                 <h5 class="modal-title text-white"><i class="bi bi-flag me-2"></i>Flagged Content</h5>
                                 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                             </div>
                             <div class="modal-body">
                                 <style>
                                 /* Reduce flagged modal width by ~20% */
                                  #flaggedModal .modal-dialog { max-width: 80% !important; }
+                                 /* Stronger backdrop for better focus */
+                                 .modal-backdrop.show { background: rgba(0,0,0,0.85) !important; }
+                                 /* Deeper shadow on modal content */
+                                 #flaggedModal .modal-content { box-shadow: 0 20px 60px rgba(0,0,0,0.9) !important; }
                                  #flaggedModal table.table td, #flaggedModal table.table th { white-space: nowrap; }
                                  #flaggedModal .title-cell { max-width: 480px; }
                                  #flaggedModal .title-cell .text-truncate { display: inline-block; max-width: 100%; vertical-align: middle; }
                                 </style>
                                 <div class="table-responsive">
                                     <table class="table table-sm table-dark align-middle">
                                         <thead>
                                             <tr>
                                                 <th>Title</th>
                                                 <th>Reports</th>
                                                 <th>Top Reason</th>
                                                 <th>Created</th>
                                                 <th>Action</th>
                                             </tr>
                                         </thead>
                                         <tbody id="flaggedTableBody">
                                             <tr><td colspan="5" class="text-secondary">Loading...</td></tr>
                                         </tbody>
                                     </table>
                                 </div>
                             </div>
                             <div class="modal-footer border-secondary">
                                 <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Close</button>
                             </div>
                         </div>
                     </div>
                 </div>

                 <script>
let __rPage = 1;
let __rLimit = 25;
async function loadReceiptsTab() {
  const tbody = document.getElementById('receiptsRows');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="8" class="text-center text-secondary">Loading...</td></tr>';
  try {
    const q = encodeURIComponent(document.getElementById('receiptsSearch')?.value || '');
    const status = encodeURIComponent(document.getElementById('receiptsStatus')?.value || '');
    const res = await fetch(`/kabaka/public/api/admin.php?action=blockchain_receipts&page=${__rPage}&limit=${__rLimit}&q=${q}&status=${status}`);
    if (!res.ok) throw new Error('Failed');
    const data = await res.json();
    if (!data.ok) throw new Error(data.error || 'Error');
    const items = data.items || [];
    if (!items.length) { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-secondary">No receipts yet</td></tr>'; return; }
    tbody.innerHTML = items.map((r, i) => {
      const name = r.creator_name || ('User #' + (r.user_id || '—'));
      const tx = r.tx_hash ? `<a class=\"link\" target=\"_blank\" href=\"https://amoy.polygonscan.com/tx/${r.tx_hash}\">${r.tx_hash.slice(0,10)}…</a>` : '—';
      const when = r.created_at ? new Date(r.created_at.replace(' ', 'T')).toLocaleString() : '—';
      const status = r.onchain_status || 'confirmed';
      return `<tr>
        <td>${i+1 + ((data.page-1)*data.limit)}</td>
        <td>${name}</td>
        <td>
          ${r.payment_id ? `<a href="#" class="link" onclick="openReceiptPayment(${r.payment_id}); return false;">#${r.payment_id}</a>` : '—'}
        </td>
        <td class=\"text-nowrap\">${r.amount_wei ?? '—'}</td>
        <td>${tx}</td>
        <td>${r.block_number ?? '—'}</td>
        <td><span class=\"badge bg-danger-subtle text-white\">${status}</span></td>
        <td>${when}</td>
      </tr>`;
    }).join('');
    const info = document.getElementById('receiptsInfo');
    if (info) info.textContent = `Page ${data.page} • ${items.length} of ${data.total} items`;
    const prev = document.getElementById('receiptsPrev');
    const next = document.getElementById('receiptsNext');
    if (prev) prev.disabled = data.page <= 1;
    if (next) next.disabled = !data.has_more;
  } catch (e) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Failed to load</td></tr>';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const tabBtn = document.getElementById('receipts-tab');
  if (tabBtn) {
    tabBtn.addEventListener('shown.bs.tab', loadReceiptsTab);
  }
  const refBtn = document.getElementById('receiptsRefreshBtn');
  if (refBtn) refBtn.addEventListener('click', loadReceiptsTab);
  const search = document.getElementById('receiptsSearch');
  if (search) search.addEventListener('input', () => { __rPage = 1; loadReceiptsTab(); });
  const statusSel = document.getElementById('receiptsStatus');
  if (statusSel) statusSel.addEventListener('change', () => { __rPage = 1; loadReceiptsTab(); });
  const prev = document.getElementById('receiptsPrev');
  if (prev) prev.addEventListener('click', () => { if (__rPage > 1) { __rPage--; loadReceiptsTab(); } });
  const next = document.getElementById('receiptsNext');
  if (next) next.addEventListener('click', () => { __rPage++; loadReceiptsTab(); });
});

// Impact tab functionality
let selectedImpactUsers = new Set();

async function loadImpactTab() {
  const tbody = document.getElementById('impactRows');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="8" class="text-center text-secondary">Loading...</td></tr>';
  
  try {
    const response = await fetch('/kabaka/public/api/admin.php?action=requirement_impact');
    const data = await response.json();
    
    if (data.ok && data.users) {
      updateImpactCounts(data.counts);
      renderImpactTable(data.users);
    } else {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Failed to load impact data</td></tr>';
    }
  } catch (error) {
    console.error('Error loading impact data:', error);
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>';
  }
}

function updateImpactCounts(counts) {
  document.getElementById('verificationImpactCount').textContent = counts.verification_impact || 0;
  document.getElementById('monetizationImpactCount').textContent = counts.monetization_impact || 0;
  document.getElementById('totalImpactCount').textContent = counts.total_impact || 0;
}

function renderImpactTable(users) {
  const tbody = document.getElementById('impactRows');
  if (!users || users.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary">No users affected by current requirements</td></tr>';
    return;
  }
  
  tbody.innerHTML = users.map(user => {
    const verificationText = user.is_verified ? 
      '<span class="text-success">Verified</span>' : 
      '<span class="text-secondary">Not Verified</span>';
    const monetizationText = user.monetization_enabled ? 
      '<span class="text-success">Monetized</span>' : 
      '<span class="text-secondary">Not Monetized</span>';
    
    const impacts = [];
    if (user.verification_impact) impacts.push('<span class="text-warning">Verification</span>');
    if (user.monetization_impact) impacts.push('<span class="text-warning">Monetization</span>');
    
    const impactText = impacts.length > 0 ? impacts.join(', ') : '<span class="text-success">No Impact</span>';
    
    return `
      <tr>
        <td><input type="checkbox" class="form-check-input impact-checkbox" value="${user.id}" ${impacts.length > 0 ? '' : 'disabled'}></td>
        <td>
          <div class="text-truncate" style="max-width: 280px;">
            <strong>${user.display_name || 'Unknown'}</strong><br>
            <small class="text-white-50 d-inline-block align-middle" style="max-width: 250px;" title="${user.email}">${maskEmail(user.email)}</small>
            <button type="button" class="btn btn-sm btn-outline-light align-middle ms-2 py-0 px-1" onclick="copyText('${user.email.replace(/'/g, "&#39;")}')" title="Copy email">
              <i class="bi bi-clipboard"></i>
            </button>
          </div>
        </td>
        <td class="text-center">${verificationText} &nbsp;•&nbsp; ${monetizationText}</td>
        <td class="text-center">${user.post_count || 0}</td>
        <td class="text-center">${user.account_age_days || 0}</td>
        <td class="text-center">${user.follower_count || 0}</td>
        <td class="text-center">${user.view_count || 0}</td>
        <td class="text-center">${impactText}</td>
      </tr>
    `;
  }).join('');
  
  // Add event listeners to checkboxes
  document.querySelectorAll('.impact-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateUnverifyButton);
  });
  
  updateUnverifyButton();
}

function updateUnverifyButton() {
  const checkedBoxes = document.querySelectorAll('.impact-checkbox:checked');
  const unverifyBtn = document.getElementById('impactUnverifyBtn');
  
  if (unverifyBtn) {
    unverifyBtn.disabled = checkedBoxes.length === 0;
    unverifyBtn.textContent = `Unverify Selected (${checkedBoxes.length})`;
  }
}

async function unverifySelectedUsers() {
  const checkedBoxes = document.querySelectorAll('.impact-checkbox:checked');
  if (checkedBoxes.length === 0) return;
  
  const userIds = Array.from(checkedBoxes).map(cb => cb.value);
  
  if (!confirm(`Are you sure you want to unverify ${userIds.length} users? This action cannot be undone.`)) {
    return;
  }
  
  try {
    const response = await fetch('/kabaka/public/api/admin.php?action=bulk_unverify', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_ids: userIds })
    });
    
    const data = await response.json();
    
    if (data.ok) {
      showToast(`Successfully unverified ${data.unverified_count} users`, 'success');
      loadImpactTab(); // Refresh the table
    } else {
      showToast(data.error || 'Failed to unverify users', 'error');
    }
  } catch (error) {
    console.error('Error unverifying users:', error);
    showToast('Error unverifying users', 'error');
  }
}

// Event listeners for impact tab
document.addEventListener('DOMContentLoaded', function() {
  const tabBtn = document.getElementById('impact-tab');
  if (tabBtn) {
    tabBtn.addEventListener('shown.bs.tab', loadImpactTab);
  }
  
  const refBtn = document.getElementById('impactRefreshBtn');
  if (refBtn) refBtn.addEventListener('click', loadImpactTab);
  
  const unverifyBtn = document.getElementById('impactUnverifyBtn');
  if (unverifyBtn) unverifyBtn.addEventListener('click', unverifySelectedUsers);
  
  const selectAllBtn = document.getElementById('selectAllImpact');
  if (selectAllBtn) {
    selectAllBtn.addEventListener('change', function() {
      const checkboxes = document.querySelectorAll('.impact-checkbox:not([disabled])');
      checkboxes.forEach(cb => cb.checked = this.checked);
      updateUnverifyButton();
    });
  }
});
</script>
                <script>
function copyText(text){
  try {
    navigator.clipboard.writeText(text).then(() => {
      if (typeof showToast === 'function') showToast('Copied to clipboard', 'success');
    }).catch(() => {
      // Fallback
      const ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      if (typeof showToast === 'function') showToast('Copied to clipboard', 'success');
    });
  } catch (e) {
    if (typeof showToast === 'function') showToast('Failed to copy', 'error');
  }
}

function maskEmail(email){
  try {
    const e = String(email || '');
    if (!e) return '';
    const at = e.indexOf('@');
    if (at <= 0) return e.length > 4 ? (e.slice(0,4) + '........') : (e + '........');
    const name = e.slice(0, at);
    const domain = e.slice(at); // keep full domain hidden from mask style
    const prefix = name.length >= 4 ? name.slice(0,8) : name;
    return prefix + '........';
  } catch (_) {
    return email;
  }
}
function openReceiptPayment(paymentId){
  const el = document.getElementById('receiptPaymentBody');
  if (el) el.innerHTML = 'Loading…';
  try {
    fetch(`/kabaka/public/api/admin.php?action=blockchain_receipts&limit=1&q=${encodeURIComponent(String(paymentId))}`)
      .then(r=>r.json())
      .then(d=>{
        const item = (d && Array.isArray(d.items) && d.items[0]) ? d.items[0] : null;
        if (!item) { if (el) el.innerHTML = 'Not found'; return; }
        const amountUsd = typeof item.payment_amount_cents === 'number' ? (item.payment_amount_cents/100).toFixed(2) : null;
        const lines = [
          `<div><strong>Payment ID:</strong> #${item.payment_id ?? '—'}</div>`,
          `<div><strong>Creator:</strong> ${item.creator_name || ('User #' + (item.user_id||'—'))} (${item.creator_email||'—'})</div>`,
          `<div><strong>Amount (app):</strong> ${amountUsd ? ('$' + amountUsd) : '—'} ${item.payment_currency || ''}</div>`,
          `<div><strong>Source:</strong> ${item.payment_source || 'payout'}</div>`,
          `<hr>`,
          `<div><strong>On-chain Tx:</strong> ${item.tx_hash ? `<a class="link" target="_blank" href="https://amoy.polygonscan.com/tx/${item.tx_hash}">${item.tx_hash}</a>` : '—'}</div>`,
          `<div><strong>Amount (wei):</strong> ${item.amount_wei ?? '—'}</div>`,
          `<div><strong>Block:</strong> ${item.block_number ?? '—'}</div>`,
          `<div><strong>Status:</strong> ${item.onchain_status || 'confirmed'}</div>`,
          `<div><strong>Contract:</strong> ${item.contract_address || '—'}</div>`,
          `<div><strong>Chain:</strong> ${item.chain || 'polygon-amoy'}</div>`
        ].join('');
        if (el) el.innerHTML = lines;
      })
      .catch(()=>{ if (el) el.innerHTML = 'Failed to load'; });
  } catch(_) { if (el) el.innerHTML = 'Failed to load'; }
  const modal = new bootstrap.Modal(document.getElementById('receiptPaymentModal'));
  modal.show();
}
                // Safe HTML escape for dynamic text used in the flagged content modal
                 function escapeHtml(text) {
                     var div = document.createElement('div');
                     div.textContent = (text === undefined || text === null) ? '' : String(text);
                     return div.innerHTML;
                 }
                 document.getElementById('flaggedModal').addEventListener('shown.bs.modal', function(){
                     fetch('/kabaka/public/api/admin.php?action=flagged_content')
                         .then(r => r.json())
                         .then(d => {
                             const body = document.getElementById('flaggedTableBody');
                             if (!d || !d.ok || !Array.isArray(d.items) || d.items.length === 0) {
                                 body.innerHTML = '<tr><td colspan="5" class="text-secondary text-center">No flagged items</td></tr>';
                                 return;
                             }
                             body.innerHTML = d.items.map(it => `
                                 <tr>
                                     <td class="title-cell"><span class="text-truncate" title="${escapeHtml(it.title || 'Untitled')}">${escapeHtml(it.title || 'Untitled')}</span></td>
                                     <td>${it.report_count ?? 0}</td>
                                     <td>${escapeHtml(it.top_reason || '')}</td>
                                     <td>${escapeHtml(it.created_at || '')}</td>
                                     <td>
                                         <div class="d-flex align-items-center gap-2 flex-wrap">
                                             <form class="d-inline-block m-0" onsubmit="return approveContentInline(${it.id}, this);">
                                                 <input type="hidden" name="content_id" value="${it.id}">
                                                 <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                             </form>
                                             <button class="btn btn-sm btn-secondary" type="button" onclick="loadReportsFor(${it.id}, this)">View Reports</button>
                                         </div>
                                     </td>
                                 </tr>
                                 <tr class="d-none"><td colspan="5" id="reports_row_${it.id}"></td></tr>
                             `).join('');
                         })
                         .catch(() => {
                             document.getElementById('flaggedTableBody').innerHTML = '<tr><td colspan="5" class="text-secondary">Failed to load</td></tr>';
                         });
                 });

                 async function approveContentInline(id, formEl){
                     try {
                         const fd = new FormData();
                         fd.append('content_id', String(id));
                         const res = await fetch('/kabaka/public/api/admin.php?action=approve_content', { method:'POST', body: fd });
                         const data = await res.json();
                         if (res.ok && data && data.ok) {
                             // remove both the row and optional reports row
                             const row = formEl.closest('tr');
                             const next = row?.nextElementSibling;
                             if (next && next.id && next.id.indexOf('reports_row_') !== -1) next.remove();
                             if (row) row.remove();
                             if (typeof showToast === 'function') showToast('Content approved', 'success');
                         } else {
                             alert(data.error || 'Failed to approve');
                         }
                     } catch (e) {
                         alert('Failed to approve');
                     }
                     return false;
                 }

                 async function loadReportsFor(id, btn){
                     try{
                         if (btn) { btn.disabled = true; btn.textContent = 'Loading…'; }
                         const r = await fetch('/kabaka/public/api/admin.php?action=content_reports&content_id=' + encodeURIComponent(id));
                         const d = await r.json();
                         const cell = document.getElementById('reports_row_' + id);
                         const row = cell ? cell.parentElement : null;
                         if (!cell || !row) return;
                         if (!d || !d.ok || !Array.isArray(d.items) || d.items.length === 0) {
                             cell.innerHTML = '<div class="text-secondary small">No reports found.</div>';
                         } else {
                             cell.innerHTML = '<div class="small">' + d.items.map(rep => `
                                 <div class="mb-2 p-2 border border-secondary rounded">
                                     <div><strong>Reason:</strong> ${escapeHtml(rep.reason || '')}</div>
                                     <div><strong>Note:</strong> ${escapeHtml(rep.note || '')}</div>
                                     <div class="text-secondary">Reported by ${escapeHtml(rep.reporter || '')} • ${escapeHtml(rep.created_at || '')}</div>
                                 </div>
                             `).join('') + '</div>';
                         }
                         row.classList.remove('d-none');
                     }catch(e){
                         alert('Failed to load reports');
                     } finally {
                         if (btn) { btn.disabled = false; btn.textContent = 'View Reports'; }
                     }
                 }
                 </script>
             </div>

                                                   <!-- Settings Tab -->
              <div class="tab-pane fade" id="settings" role="tabpanel">
                  <div class="row g-4">
                                             <!-- Creator Requirements -->
                       <div class="col-lg-4">
                           <div class="admin-card">
                               <h5 class="text-white mb-4">
                                   <i class="bi bi-person-badge me-2"></i>Eligible Creator
                               </h5>
                               <div class="mb-3">
                                   <label class="form-label text-white">Minimum Content Posts</label>
                                   <input type="number" class="form-control" id="minContentPosts" value="5" min="0">
                                   <small class="text-secondary">Minimum posts required to become verified creator</small>
                               </div>
                               <div class="mb-3">
                                   <label class="form-label text-white">Minimum Account Age (Days)</label>
                                   <input type="number" class="form-control" id="minAccountAge" value="30" min="0">
                                   <small class="text-secondary">Minimum days since registration</small>
                               </div>

                               <div class="mb-3" style="display: none;">
                                   <div class="form-check">
                                       <input class="form-check-input" type="checkbox" id="requireVerification" checked>
                                       <label class="form-check-label text-white" for="requireVerification">
                                           Require identity verification
                                       </label>
                                   </div>
                               </div>
                                                               <button class="btn btn-secondary" onclick="saveCreatorRequirements()">
                                    <i class="bi bi-check-circle me-1"></i>Save Requirements
                                </button>
                           </div>
                       </div>

                       <!-- Payment & Withdrawal -->
                       <div class="col-lg-4">
                           <div class="admin-card">
                               <h5 class="text-white mb-4">
                                   <i class="bi bi-bank me-2"></i>Payment Withdrawal
                               </h5>
                               <div class="mb-3">
                                   <label class="form-label text-white">Minimum Withdrawal Amount ($)</label>
                                   <input type="number" class="form-control" id="minWithdrawal" value="50.00" step="0.01" min="0">
                                   <small class="text-secondary">Minimum amount creators can withdraw</small>
                               </div>
                               <div class="mb-3">
                                   <label class="form-label text-white">Platform Fee (%)</label>
                                   <input type="number" class="form-control" id="platformFee" value="10.0" step="0.1" min="0" max="100">
                                   <small class="text-secondary">Percentage taken by platform</small>
                               </div>
                               <div class="mb-3">
                                   <label class="form-label text-white">Payment Processing Fee ($)</label>
                                   <input type="number" class="form-control" id="processingFee" value="2.50" step="0.01" min="0">
                                   <small class="text-secondary">Fixed fee per withdrawal</small>
                               </div>
                               <div class="mb-3">
                                   <div class="form-check">
                                       <input class="form-check-input" type="checkbox" id="autoPayouts" checked>
                                       <label class="form-check-label text-white" for="autoPayouts">
                                           Enable automatic monthly payouts
                                       </label>
                                   </div>
                               </div>
                                                               <button class="btn btn-success" onclick="savePaymentSettings()">
                                    <i class="bi bi-check-circle me-1"></i>Save Payment
                                </button>
                           </div>
                       </div>

                                              <!-- Monetization Settings -->
                        <div class="col-lg-4">
                            <div class="admin-card" id="monetizationSettingsCard">
                                <h5 class="text-white mb-4">
                                    <i class="bi bi-cash-coin me-2"></i>Monetization Settings
                                </h5>
                                <div class="mb-3">
                                    <label class="form-label text-white">Payment Per 1000 Views</label>
                                    <input type="number" class="form-control" id="paymentPerViews" value="0" min="0" step="0.01">
                                    <small class="text-secondary">Amount paid to creator per 1,000 valid views.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Min Followers For Pay</label>
                                    <input type="number" class="form-control" id="minFollowersForPay" value="0" min="0" step="1">
                                    <small class="text-secondary">Minimum followers required to be eligible for payout.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Min Views For Payment</label>
                                    <input type="number" class="form-control" id="minViewsForPay" value="0" min="0" step="1">
                                    <small class="text-secondary">Minimum monthly views required to receive payment.</small>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enableMonetization">
                                        <label class="form-check-label text-white" for="enableMonetization">
                                            Enable creator monetization
                                        </label>
                                    </div>
                                    <small class="text-secondary">Toggle program on/off platform-wide.</small>
                                </div>
                                <button class="btn btn-secondary" onclick="saveMonetizationSettings()">
                                    <i class="bi bi-check-circle me-1"></i>Save Monetization
                                </button>
                            </div>
                        </div>

                        <!-- Platform Settings -->
                        <div class="col-lg-4">
                            <div class="admin-card" id="platformSettingsCard">
                                <h5 class="text-white mb-4">
                                    <i class="bi bi-gear-fill me-2"></i>Platform Settings
                                </h5>
                                <div class="mb-3">
                                    <label class="form-label text-white">Site Name</label>
                                    <input type="text" class="form-control" id="siteName" value="Kabaka">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Max File Upload Size (MB)</label>
                                    <input type="number" class="form-control" id="maxUploadSize" value="100">
                                </div>
                                <div class="mb-3" style="display: none;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="maintenanceMode">
                                        <label class="form-check-label text-white" for="maintenanceMode">
                                            Enable maintenance mode
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3" style="display: none;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="autoApprove" checked>
                                        <label class="form-check-label text-white" for="autoApprove">
                                            Auto-approve new content
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3" style="display: none;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="requireCreatorApproval">
                                        <label class="form-check-label text-white" for="requireCreatorApproval">
                                            Require manual approval for new creators
                                        </label>
                                        <small class="text-secondary">New creators must be approved by admin</small>
                                    </div>
                                </div>
                                                                 <br><button class="btn btn-secondary" onclick="savePlatformSettings()">
                                     <i class="bi bi-check-circle me-1"></i>Save Settings
                                 </button>
                            </div>
                        </div>

                       <!-- Content Moderation -->
                       <div class="col-lg-4">
                           <div class="admin-card" id="moderationSettingsCard">
                               <h5 class="text-white mb-4">
                                   <i class="bi bi-shield-check me-2"></i>Content Moderation
                               </h5>
                               <div class="mb-3">
                                   <label class="form-label text-white">Auto-flag Threshold</label>
                                   <input type="number" class="form-control" id="autoFlagThreshold" value="5" min="1">
                                   <small class="text-secondary">Number of flags before auto moderation</small>
                               </div>
                               <div class="mb-3">
                                   <label class="form-label text-white">Review Time Limit (Hours)</label>
                                   <input type="number" class="form-control" id="reviewTimeLimit" value="24" min="1">
                                   <small class="text-secondary">Time to review flagged content</small>
                               </div>
                               
                               <button class="btn btn-danger" onclick="saveModerationSettings()">
                                    <i class="bi bi-check-circle me-1"></i>Save Moderation
                                </button>
                           </div>
                       </div>

                       <!-- Auto Actions -->
                       <div class="col-lg-4">
                           <div class="admin-card" id="autoActionsSettingsCard" style="background: transparent; box-shadow: none; border: none;">
                               <h5 class="text-white mb-4">
                                   <i class="bi bi-lightning-charge me-2"></i>Auto Actions here :
                               </h5>
                               <div class="mb-3">
                                   <div class="form-check">
                                       <input class="form-check-input" type="radio" name="autoActionMode" id="autoActionApprove" value="approve">
                                       <label class="form-check-label text-white" for="autoActionApprove">
                                           Auto Approve Uploads
                                       </label>
                                   </div>
                               </div>
                               <div class="mb-3">
                                   <div class="form-check">
                                       <input class="form-check-input" type="radio" name="autoActionMode" id="autoActionModerate" value="moderate">
                                       <label class="form-check-label text-white" for="autoActionModerate">
                                           Auto Moderate Uploads
                                       </label>
                                   </div>
                               </div>
                               <div class="mb-3">
                                   <div class="form-check">
                                       <input class="form-check-input" type="radio" name="autoActionMode" id="autoActionReject" value="reject">
                                       <label class="form-check-label text-white" for="autoActionReject">
                                           Auto Reject Uploads
                                       </label>
                                   </div>
                               </div>
                               
                               <button class="btn btn-secondary" type="button" onclick="saveAutoActionsSettings()">
                                   <i class="bi bi-check-circle me-1"></i>Save Auto Actions
                               </button>
                           </div>
                       </div>

                     
                 </div>
             </div>
        </div>
    </div>

         <!-- Footer -->
     <footer class="py-3 mt-3">
        <div class="container">
            <div class="row g-4">
            <div class="col-lg-1"></div>
                 <!-- Quick Links -->
                 <div class="col-lg-2 col-md-6">
                     <h6 class="text-white mb-3">Quick Links</h6>
                     <ul class="list-unstyled">
						<li class="mb-2"><a href="#overview" class="text-secondary text-decoration-none small">Dashboard</a></li>						
                        <li class="mb-2"><a href="#users" class="text-secondary text-decoration-none small">User Management</a></li>
						<li class="mb-2"><a href="#content" class="text-secondary text-decoration-none small">Manage Content</a></li>
						<li class="mb-2"><a href="#settings" class="text-secondary text-decoration-none small">Platform Settings</a></li>
</ul>
                 </div>
                 
                 <!-- Settings Shortcuts -->
                 <div class="col-lg-2 col-md-6">
                     <h6 class="text-white mb-3">Settings Shortcuts</h6>
                     <ul class="list-unstyled">
				<li class="mb-2"><a href="#settings" class="text-secondary text-decoration-none small" onclick="(function(){var l=document.querySelector('button[data-bs-toggle=\'tab\'][data-bs-target=\'#settings\']'); if(l){new bootstrap.Tab(l).show(); setTimeout(function(){var t=document.getElementById('platformSettingsCard'); if(t){t.scrollIntoView({behavior:'smooth',block:'start'});} },50);} })(); return false;"><span class="text-success me-1"><i class="bi bi-gear-fill"></i></span>Content Settings</a></li>
				<li class="mb-2"><a href="#settings" class="text-secondary text-decoration-none small" onclick="(function(){var l=document.querySelector('button[data-bs-toggle=\'tab\'][data-bs-target=\'#settings\']'); if(l){new bootstrap.Tab(l).show(); setTimeout(function(){var t=document.getElementById('moderationSettingsCard'); if(t){t.scrollIntoView({behavior:'smooth',block:'start'});} },50);} })(); return false;"><span class="text-success me-1"><i class="bi bi-shield-check"></i></span>Moderation</a></li>
				<li class="mb-2"><a href="#settings" class="text-secondary text-decoration-none small" onclick="(function(){var l=document.querySelector('button[data-bs-toggle=\'tab\'][data-bs-target=\'#settings\']'); if(l){new bootstrap.Tab(l).show(); setTimeout(function(){var t=document.getElementById('autoActionsSettingsCard'); if(t){t.scrollIntoView({behavior:'smooth',block:'start'});} },50);} })(); return false;"><span class="text-success me-1"><i class="bi bi-lightning-charge"></i></span>Auto Actions</a></li>
				</ul>
 					</div>
                 
                 <!-- Support -->
                 <div class="col-lg-2 col-md-6">
                     <h6 class="text-white mb-3">Support</h6>
                     <ul class="list-unstyled">
                     <li class="mb-2"><a href="/kabaka/public/admin_dashboard/support.php" class="text-secondary text-decoration-none small">Documentation</a></li>
                     <li class="mb-2"><a href="/kabaka/public/admin_dashboard/support.php#troubleshoot" class="text-secondary text-decoration-none small">System Logs</a></li>
                     <li class="mb-2"><a href="/kabaka/public/admin_dashboard/support.php#contact" class="text-secondary text-decoration-none small">Contact Support</a></li>
 						</ul>
                 </div>
                 
                 <!-- Newsletter -->
                 <div class="col-lg-3 col-md-6">
                     <h6 class="text-white mb-3">Stay Updated</h6>
                     <p class="text-secondary small mb-3">Get system updates and security alerts.</p>
                     <form id="adminNewsletterForm" class="d-flex" style="max-width: 340px; width: 100%;">
                         <input type="email" class="form-control form-control-sm me-2" placeholder="admin@example.com" required style="min-width: 260px;">
                         <button type="submit" class="btn btn-primary btn-sm">
                             <i class="bi bi-envelope"></i>
                         </button>
                     </form>
                 </div>
                
                
            </div>
            
                                      <hr class="my-2" style="border-color: rgba(255,255,255,.15);">
             <div class="row align-items-center">
                 <div class="col-12 text-center">
                     <p class="text-secondary small mb-0">
                         © <?= date('Y') ?> Kabaka Admin. All rights reserved.
                     </p>
                     <p class="text-secondary small mb-0 mt-2">
                         <i class="bi bi-shield-check me-1"></i>Secure Admin Panel
                     </p>
                 </div>
             </div>
       
       <div class="col-lg-1"></div>
        </div>
    </footer>

    <!-- Recent Activity Modal -->
    <div class="modal fade" id="recentActivityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background: #1a1a1a; border: 1px solid rgba(255,255,255,.2);">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,.2);">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-list-ul me-2"></i>All Recent Activity
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="allActivityList">
                        <div class="text-secondary small">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Details Modal -->
    <div class="modal fade" id="contentDetailsModal" tabindex="-1">
        <div class="modal-dialog" style="max-width: 720px;">
            <div class="modal-content" style="background: #1a1a1a; border: 1px solid rgba(255,255,255,.2); max-height: 92vh;">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,.2);">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-file-text me-2"></i><span id="cdTitle">Content</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 80vh; overflow: auto;">
                    <div id="cdLoading" class="text-secondary small mb-3">Loading...</div>
                    <div id="cdBody" style="display:none;">
                        <!-- Creator -->
                        <div class="mb-3">
                            <div class="text-secondary small">Creator:</div>
                            <div class="text-white" id="cdCreator">No creator</div>
                        </div>
                        
                        <!-- Category -->
                        <div class="mb-3">
                            <div class="text-secondary small">Category:</div>
                            <div class="text-white" id="cdCategory">No category</div>
                        </div>
                        
                        <!-- Status -->
                        <div class="mb-3">
                            <div class="text-secondary small">Status:</div>
                            <div class="text-white" id="cdStatus">No status</div>
                        </div>
                        
                        <!-- Created Date -->
                        <div class="mb-3">
                            <div class="text-secondary small">Created Date:</div>
                            <div class="text-white" id="cdCreatedAt">No date</div>
                        </div>
                        
                        <!-- Updated Date -->
                        <div class="mb-3">
                            <div class="text-secondary small">Updated Date:</div>
                            <div class="text-white" id="cdUpdatedAt">No update date</div>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-3">
                            <div class="text-secondary small">Description:</div>
                            <div class="text-white" id="cdDescription">No description</div>
                        </div>
                        
                        <!-- Tags -->
                        <div class="mb-3">
                            <div class="text-secondary small">Tags:</div>
                            <div class="text-white" id="cdTags">No tags</div>
                        </div>
                        
                        <!-- Thumbnail -->
                        <div class="mb-3">
                            <div class="text-secondary small">Thumbnail:</div>
                            <div id="cdThumbnail" class="rounded" style="background: rgba(255,255,255,.05); padding: 8px; min-height: 80px; display: flex; align-items: center; justify-content: flex-start;">No thumbnail</div>
                        </div>
                        
                        <!-- Media Content -->
                        <div class="mb-3">
                            <div class="text-secondary small">Media Content:</div>
                            <div id="cdMedia" class="rounded" style="background: rgba(255,255,255,.05); padding: 8px; width: 100%; display: flex; align-items: center; justify-content: flex-start;">No media content</div>
                        </div>
                        
                        <!-- File Type -->
                        <div class="mb-3">
                            <div class="text-secondary small">File Type:</div>
                            <div class="text-white" id="cdFileType">No file type</div>
                        </div>
                        
                        <!-- File Size -->
                        <div class="mb-3">
                            <div class="text-secondary small">File Size:</div>
                            <div class="text-white" id="cdFileSize">No file size</div>
                        </div>
                        
                        <!-- Original Filename -->
                        <div class="mb-3">
                            <div class="text-secondary small">Original Filename:</div>
                            <div class="text-white" id="cdOriginalFilename">No filename</div>
                        </div>
                        
                        <!-- Ownership Note -->
                        <div class="mb-3">
                            <div class="text-secondary small">Ownership Note:</div>
                            <div class="text-white" id="cdOwnershipNote">No ownership note</div>
                        </div>
                        
                        <!-- Stats Row -->
                        <div class="d-flex gap-3">
                            <div class="text-secondary small">Views: <span class="text-white" id="cdViews">0</span></div>
                            <div class="text-secondary small">Likes: <span class="text-white" id="cdLikes">0</span></div>
                            <div class="text-secondary small">Comments: <span class="text-white" id="cdComments">0</span></div>
                        </div>
                    </div>
                    <div id="cdError" class="text-danger small" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Settings Modal -->
    <div class="modal fade" id="userSettingsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background: #1a1a1a; border: 1px solid rgba(255,255,255,.2);">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,.2);">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-gear me-2"></i>User Settings
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userSettingsForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white">Display Name</label>
                                <input type="text" class="form-control" id="userDisplayName" placeholder="Enter display name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">Email</label>
                                <input type="email" class="form-control" id="userEmail" placeholder="Enter email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">Role</label>
                                <select class="form-select" id="userRole">
                                    <option value="viewer">Viewer</option>
                                    <option value="creator">Creator</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">Status</label>
                                <select class="form-select" id="userStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="banned">Banned</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">New Password (Optional)</label>
                                <input type="password" class="form-control" id="userPassword" placeholder="Leave blank to keep current">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">USDT Address</label>
                                <input type="text" class="form-control" id="userUsdtAddress" placeholder="Enter USDT address">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,.2);">
                    <button type="button" class="btn btn-danger" onclick="deleteUser()" id="deleteUserBtn">
                        <i class="bi bi-trash me-1"></i>Delete User
                    </button>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="updateUserSettings()">Update User</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background: #1a1a1a; border: 1px solid rgba(255,255,255,.2);">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,.2);">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-person-plus me-2"></i>Add New User
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white">Display Name *</label>
                                <input type="text" class="form-control" id="newUserDisplayName" placeholder="Enter display name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">Email *</label>
                                <input type="email" class="form-control" id="newUserEmail" placeholder="Enter email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">Password *</label>
                                <input type="password" class="form-control" id="newUserPassword" placeholder="Enter password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">Role *</label>
                                <select class="form-select" id="newUserRole" required>
                                    <option value="">Select Role</option>
                                    <option value="viewer">Viewer</option>
                                    <option value="creator">Creator</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">USDT Address</label>
                                <input type="text" class="form-control" id="newUserUsdtAddress" placeholder="Enter USDT address (optional)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white">Status</label>
                                <select class="form-select" id="newUserStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,.2);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="createNewUser()">Create User</button>
                </div>
            </div>
        </div>
    </div>
                </div> <!-- End main content -->
                <div class="col-lg-1"></div> <!-- Right gap -->
            </div> <!-- End row -->
        </div> <!-- End container-fluid -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
                 // Global variables for user management
         let allUsers = [];
         let filteredUsers = [];
         let currentPage = 1;
         let pageSize = 25;
         let currentSort = { field: 'name', direction: 'asc' };

         // Load admin dashboard data
         async function loadAdminData() {
            try {
                const [stats, users, content, activity] = await Promise.all([
                    fetch('/kabaka/public/api/admin.php?action=stats').then(r => r.json()),
                    fetch('/kabaka/public/api/admin.php?action=recent_users').then(r => r.json()),
                    fetch('/kabaka/public/api/admin.php?action=recent_content').then(r => r.json()),
                    fetch('/kabaka/public/api/admin.php?action=recent_activity').then(r => r.json())
                ]);
                
                // Update stats
                if (stats.total_users !== undefined) document.getElementById('totalUsers').textContent = stats.total_users;
                if (stats.total_creators !== undefined) document.getElementById('totalCreators').textContent = stats.total_creators;
                if (stats.total_content !== undefined) document.getElementById('totalContent').textContent = stats.total_content;
                if (stats.total_views !== undefined) document.getElementById('totalViews').textContent = stats.total_views;
                if (stats.total_likes !== undefined) document.getElementById('totalLikes').textContent = stats.total_likes;
                if (stats.total_comments !== undefined) document.getElementById('totalComments').textContent = stats.total_comments;
                
                // Update users table
                renderUsersTable(users.users || []);
                
                // Update content table
                renderContentTable(content.content || []);
                
                // Update recent activity
                renderRecentActivity(activity.activities || []);
                
            } catch (error) {
                console.error('Error loading admin data:', error);
            }
        }

        function timeAgo(dateStr) {
            const now = new Date();
            const then = new Date(dateStr || Date.now());
            const seconds = Math.floor((now - then) / 1000);
            const intervals = [
                ['year', 31536000], ['month', 2592000], ['day', 86400],
                ['hour', 3600], ['minute', 60], ['second', 1]
            ];
            for (const [label, secs] of intervals) {
                const count = Math.floor(seconds / secs);
                if (count >= 1) return `${count} ${label}${count>1?'s':''} ago`;
            }
            return 'just now';
        }

        function renderRecentActivity(items) {
            cachedActivityItems = items || [];
            const container = document.getElementById('recentActivityList');
            if (!items || items.length === 0) {
                container.innerHTML = '<div class="text-secondary small">No recent activity</div>';
                return;
            }
            const iconByType = {
                new_user: { cls: 'bg-success', icon: 'bi-person-plus' },
                new_content: { cls: 'bg-primary', icon: 'bi-collection-play' },
                moderation: { cls: 'bg-warning', icon: 'bi-shield-exclamation' },
                comment: { cls: 'bg-info', icon: 'bi-chat' },
                default: { cls: 'bg-secondary', icon: 'bi-activity' }
            };
            container.innerHTML = items.slice(0, 5).map(it => {
                const meta = iconByType[it.type] || iconByType.default;
                const title = it.title || 'Activity';
                const details = it.details || '';
                const when = timeAgo(it.created_at);
                return `
                    <div class="activity-item d-flex align-items-center mb-3">
                        <div class="activity-icon ${meta.cls} rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi ${meta.icon} text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-white small fw-semibold">${title}</div>
                            <div class="text-secondary small">${details} · ${when}</div>
                        </div>
                    </div>`;
            }).join('');
        }
        
                 function renderUsersTable() {
             const tbody = document.getElementById('usersTable');
             const startIndex = (currentPage - 1) * pageSize;
             const endIndex = startIndex + pageSize;
             const pageUsers = filteredUsers.slice(startIndex, endIndex);

             if (filteredUsers.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">No users found</td></tr>';
                 return;
             }

             if (pageUsers.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">No users on this page</td></tr>';
                 return;
             }
             
             tbody.innerHTML = pageUsers.map(user => `
                 <tr>
                     <td>
                         <div class="d-flex align-items-center">
                             <i class="bi bi-person-circle me-2"></i>
                             <div>
                                 <div class="fw-semibold">${user.display_name || 'No Name'}</div>
                             </div>
                         </div>
                     </td>
                     <td>
                         <span class="text-white">
                             ${user.role}
                         </span>
                     </td>
                     <td>${user.email}</td>
                     <td>${new Date(user.created_at).toLocaleDateString()}</td>
                     <td>
                         <span class="badge ${user.status === 'banned' ? 'bg-danger' : user.status === 'inactive' ? 'bg-warning' : 'bg-success'}">
                             ${user.status || 'active'}
                         </span>
                     </td>
                     <td>
                         <div class="btn-group btn-group-sm">
                             <button class="btn btn-outline-success" onclick="approveUser(${user.id})" title="Approve User">
                                 <i class="bi bi-check-circle"></i>
                             </button>
                             <button class="btn btn-outline-danger" onclick="banUser(${user.id})" title="Ban User">
                                 <i class="bi bi-person-x"></i>
                             </button>
                             <button class="btn btn-outline-light" onclick="openUserSettingsModal(${user.id})" title="User Settings">
                                 <i class="bi bi-gear"></i>
                             </button>
                         </div>
                     </td>
                 </tr>
             `).join('');
         }
        
        function renderContentTable(content) {
            const tbody = document.getElementById('contentTable');
            if (content.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary">No content found</td></tr>';
                return;
            }
            
            tbody.innerHTML = content.map(item => `
                <tr>
                    <td>
                        <div class="fw-semibold">
                            <a href="#" onclick="openContentDetails(${item.id}); return false;" title="${item.title || 'Untitled'}">${(item.title || 'Untitled').length > 20 ? (item.title || 'Untitled').substring(0, 20) + '...' : (item.title || 'Untitled')}</a>
                        </div>
                    </td>
                    <td>${item.creator_name || 'Unknown'}</td>
                    <td>${(item.category || 'other').toString().toLowerCase().replace(/^./, c => c.toUpperCase())}</td>
                    <td>
                        <span class="${getStatusBadgeClass(item.status)} fw-semibold">
                            ${getStatusDisplayText(item.status)}
                        </span>
                    </td>
                    <td>${item.view_count || 0}</td>
                    <td>${item.created_at ? new Date(item.created_at).toLocaleDateString() : '—'}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success" onclick="approveContent(${item.id})" title="Approve">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button class="btn btn-outline-warning" onclick="moderateContent(${item.id})" title="Set to Pending">
                                <i class="bi bi-clock"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="rejectContent(${item.id})" title="Reject">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        
        function refreshStats() {
            loadAdminData();
        }
        
        function openAddUserModal() {
            // Clear form
            document.getElementById('addUserForm').reset();
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
            modal.show();
        }

        async function createNewUser() {
            const formData = {
                display_name: document.getElementById('newUserDisplayName').value,
                email: document.getElementById('newUserEmail').value,
                password: document.getElementById('newUserPassword').value,
                role: document.getElementById('newUserRole').value,
                usdt_address: document.getElementById('newUserUsdtAddress').value,
                status: document.getElementById('newUserStatus').value
            };
            
            // Validate required fields
            if (!formData.display_name || !formData.email || !formData.password || !formData.role) {
                showToast('Please fill in all required fields', 'error');
                return;
            }
            
            try {
                // Send create request to API
                const response = await fetch('/kabaka/public/api/admin.php?action=create_user', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (!response.ok || result.error) {
                    throw new Error(result.error || 'Failed to create user');
                }
                
                showToast('User created successfully', 'success');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                modal.hide();
                
                // Refresh user data
                loadAdminData();
                
            } catch (error) {
                console.error('Error creating user:', error);
                showToast('Failed to create user: ' + error.message, 'error');
            }
        }
        
        function showPlatformSettings() {
            const modal = new bootstrap.Modal(document.getElementById('platformSettingsModal'));
            modal.show();
        }
        
        function manageUser(userId) {
            if (confirm(`Are you sure you want to manage user ${userId}?`)) {
                // Here you would implement user management actions
                alert(`User ${userId} management - Feature coming soon!`);
            }
        }
        
        async function moderateContent(contentId) {
            if (confirm(`Are you sure you want to set this content to pending review?`)) {
                try {
                    const formData = new FormData();
                    formData.append('content_id', contentId);
                    
                    const response = await fetch('/kabaka/public/api/admin.php?action=moderate_content', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.ok) {
                        showToast(data.message, 'success');
                        loadContentModerationData(); // Refresh content table
                    } else {
                        showToast(data.error || 'Failed to moderate content', 'error');
                    }
                } catch (error) {
                    showToast('Error moderating content: ' + error.message, 'error');
                }
            }
        }
        
                 async function loadUserManagementData() {
             try {
                 const response = await fetch('/kabaka/public/api/admin.php?action=all_users');
                 const data = await response.json();
                 allUsers = data.users || [];
                 filteredUsers = [...allUsers];
                 currentPage = 1;
                 renderUsersTable();
                 updateUserResultsCount();
                 renderUserPagination();
             } catch (error) {
                 console.error('Error loading user management data:', error);
                 document.getElementById('usersTable').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading users</td></tr>';
             }
         }

         // Filter users based on search and filters
         function filterUsers() {
             const searchTerm = document.getElementById('userSearch').value.toLowerCase();
             const roleFilter = document.getElementById('roleFilter').value;
             const statusFilter = document.getElementById('userStatusFilter').value;

             filteredUsers = allUsers.filter(user => {
                 const matchesSearch = !searchTerm || 
                     user.display_name?.toLowerCase().includes(searchTerm) ||
                     user.email.toLowerCase().includes(searchTerm);
                 
                 const matchesRole = !roleFilter || user.role === roleFilter;
                 const matchesStatus = !statusFilter || user.status === statusFilter;

                 return matchesSearch && matchesRole && matchesStatus;
             });

             currentPage = 1;
             renderUsersTable();
             updateUserResultsCount();
             renderUserPagination();
         }

         // Clear all filters
         function clearFilters() {
             document.getElementById('userSearch').value = '';
             document.getElementById('roleFilter').value = '';
             document.getElementById('userStatusFilter').value = '';
             filteredUsers = [...allUsers];
             currentPage = 1;
             renderUsersTable();
             updateUserResultsCount();
             renderUserPagination();
         }

                   // Change page size
          function changePageSize() {
              const selectedValue = document.getElementById('pageSize').value;
              if (selectedValue === 'all') {
                  pageSize = filteredUsers.length;
              } else {
                  pageSize = parseInt(selectedValue);
              }
              currentPage = 1;
              renderUsersTable();
              renderUserPagination();
          }

         // Sort table by column
         function sortTable(field) {
             if (currentSort.field === field) {
                 currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
             } else {
                 currentSort.field = field;
                 currentSort.direction = 'asc';
             }

             filteredUsers.sort((a, b) => {
                 let aVal = a[field] || '';
                 let bVal = b[field] || '';

                 if (field === 'joined') {
                     aVal = new Date(a.created_at || 0);
                     bVal = new Date(b.created_at || 0);
                 }

                 if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
                 if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
                 return 0;
             });

             renderUsersTable();
         }

         // Update results count
         function updateUserResultsCount() {
             document.getElementById('userResultsCount').textContent = filteredUsers.length;
         }

                                       // Render pagination
           function renderUserPagination() {
               const totalPages = Math.ceil(filteredUsers.length / pageSize);
               const pagination = document.getElementById('userPagination');
               
               // Always show pagination info for debugging
               if (totalPages <= 1) {
                   pagination.innerHTML = `
                       <li class="page-item disabled">
                           <span class="page-link text-secondary">
                               <i class="bi bi-info-circle me-1"></i>
                               ${filteredUsers.length} users (1 page)
                           </span>
                       </li>
                   `;
                   return;
               }

               let paginationHTML = '';
               
               // Previous button
               paginationHTML += `
                   <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                       <a class="page-link" href="#" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'tabindex="-1"' : ''}>
                           Prev
                       </a>
                   </li>
               `;

               // Current page
               paginationHTML += `
                   <li class="page-item active">
                       <span class="page-link">
                           Page ${currentPage}
                       </span>
                   </li>
               `;

               // Next button
               paginationHTML += `
                   <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                       <a class="page-link" href="#" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'tabindex="-1"' : ''}>
                           Next
                       </a>
                   </li>
               `;

               pagination.innerHTML = paginationHTML;
           }

         // Go to specific page
         function goToPage(page) {
             const totalPages = Math.ceil(filteredUsers.length / pageSize);
             if (page >= 1 && page <= totalPages) {
                 currentPage = page;
                 renderUsersTable();
                 renderUserPagination();
             }
         }

         // Export users
         function exportUsers() {
             const csvContent = generateUserCSV();
             const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
             const link = document.createElement('a');
             const url = URL.createObjectURL(blob);
             link.setAttribute('href', url);
             link.setAttribute('download', `users_export_${new Date().toISOString().split('T')[0]}.csv`);
             link.style.visibility = 'hidden';
             document.body.appendChild(link);
             link.click();
             document.body.removeChild(link);
         }

         // Generate CSV for export
         function generateUserCSV() {
             const headers = ['Name', 'Email', 'Role', 'Status', 'Joined Date'];
             const rows = filteredUsers.map(user => [
                 user.display_name || 'No Name',
                 user.email,
                 user.role,
                 user.status || 'active',
                 new Date(user.created_at).toLocaleDateString()
             ]);
             
             return [headers, ...rows].map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
         }
        
                 // Global variables for content management
         let allContent = [];
         let filteredContent = [];
         let currentContentPage = 1;
         let contentPageSize = 10;
         let currentContentSort = { field: 'views', direction: 'asc' };

         async function loadContentModerationData() {
             try {
                 console.log('Loading content moderation data...');
                 const response = await fetch('/kabaka/public/api/admin.php?action=all_content');
                 const data = await response.json();
                 console.log('Content data received:', data);
                 allContent = data.content || [];
                 filteredContent = [...allContent];
                 currentContentPage = 1;
                 renderContentTable();
                 updateContentResultsCount();
                 renderContentPagination();
             } catch (error) {
                 console.error('Error loading content moderation data:', error);
                 document.getElementById('contentTable').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading content</td></tr>';
             }
         }

         function renderContentTable() {
             const tbody = document.getElementById('contentTable');
             const startIndex = (currentContentPage - 1) * contentPageSize;
             const endIndex = startIndex + contentPageSize;
             const pageContent = filteredContent.slice(startIndex, endIndex);

             if (filteredContent.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">No content found</td></tr>';
                 return;
             }

             if (pageContent.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">No content on this page</td></tr>';
                 return;
             }
             
                           tbody.innerHTML = pageContent.map(item => `
                  <tr>
                      <td>
                          <div class="fw-semibold">
                              <a href="#" onclick="openContentDetails(${item.id}); return false;" title="${item.title || 'Untitled'}">${(item.title || 'Untitled').length > 20 ? (item.title || 'Untitled').substring(0, 20) + '...' : (item.title || 'Untitled')}</a>
                          </div>
                      </td>
                     <td>${item.creator_name || 'Unknown'}</td>
                     <td>${(item.category || 'other').toString().toLowerCase().replace(/^./, c => c.toUpperCase())}</td>
                     <td>
                         <span class="${getStatusBadgeClass(item.status)} fw-semibold">
                             ${getStatusDisplayText(item.status)}
                         </span>
                     </td>
                     <td>${item.view_count || 0}</td>
                     <td>${item.created_at ? new Date(item.created_at).toLocaleDateString() : '—'}</td>
                     <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success" onclick="approveContent(${item.id})" title="Approve">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button class="btn btn-outline-warning" onclick="moderateContent(${item.id})" title="Set to Pending">
                                <i class="bi bi-clock"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="rejectContent(${item.id})" title="Reject">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                     </td>
                 </tr>
             `).join('');
         }

         // Filter content based on search and filters
         function filterContent() {
             const searchTerm = document.getElementById('contentSearch').value.toLowerCase();
             const categoryFilter = document.getElementById('categoryFilter').value;
             const statusFilter = document.getElementById('statusFilter').value;

             filteredContent = allContent.filter(item => {
                 const matchesSearch = !searchTerm || 
                     item.title?.toLowerCase().includes(searchTerm) ||
                     item.description?.toLowerCase().includes(searchTerm) ||
                     item.creator_name?.toLowerCase().includes(searchTerm);
                 
                 const matchesCategory = !categoryFilter || (item.category ? item.category.toLowerCase() === categoryFilter : (categoryFilter === 'other'));
                 
                 // Handle status filtering with multiple possible values
                 let matchesStatus = true;
                 if (statusFilter) {
                     if (statusFilter === 'approved') {
                         matchesStatus = item.status === 'approved' || item.status === 'visible';
                     } else {
                         matchesStatus = item.status === statusFilter;
                     }
                 }

                 return matchesSearch && matchesCategory && matchesStatus;
             });

             currentContentPage = 1;
             renderContentTable();
             updateContentResultsCount();
             renderContentPagination();
         }

         // Clear all content filters
         function clearContentFilters() {
             document.getElementById('contentSearch').value = '';
             document.getElementById('categoryFilter').value = '';
             document.getElementById('statusFilter').value = '';
             filteredContent = [...allContent];
             currentContentPage = 1;
             renderContentTable();
             updateContentResultsCount();
             renderContentPagination();
         }

         // Change content page size
         function changeContentPageSize() {
             const selectedValue = document.getElementById('contentPageSize').value;
             if (selectedValue === 'all') {
                 contentPageSize = filteredContent.length;
             } else {
                 contentPageSize = parseInt(selectedValue);
             }
             currentContentPage = 1;
             renderContentTable();
             renderContentPagination();
         }

         // Sort content table by column
         function sortContentTable(field) {
             if (currentContentSort.field === field) {
                 currentContentSort.direction = currentContentSort.direction === 'asc' ? 'desc' : 'asc';
             } else {
                 currentContentSort.field = field;
                 currentContentSort.direction = 'asc';
             }

             filteredContent.sort((a, b) => {
                 let aVal = a[field] || '';
                 let bVal = b[field] || '';

                 if (field === 'created') {
                     aVal = new Date(a.created_at || 0);
                     bVal = new Date(b.created_at || 0);
                 }

                 if (field === 'views') {
                     aVal = parseInt(a.view_count || 0);
                     bVal = parseInt(b.view_count || 0);
                 }

                 if (aVal < bVal) return currentContentSort.direction === 'asc' ? -1 : 1;
                 if (aVal > bVal) return currentContentSort.direction === 'asc' ? 1 : -1;
                 return 0;
             });

             renderContentTable();
         }

         // Update content results count
         function updateContentResultsCount() {
             document.getElementById('contentResultsCount').textContent = filteredContent.length;
         }

         // Render content pagination
         function renderContentPagination() {
             const totalPages = Math.ceil(filteredContent.length / contentPageSize);
             const pagination = document.getElementById('contentPagination');
             
             // Always show pagination info for debugging
             if (totalPages <= 1) {
                 pagination.innerHTML = `
                     <li class="page-item disabled">
                         <span class="page-link text-secondary">
                             <i class="bi bi-info-circle me-1"></i>
                             ${filteredContent.length} content items (1 page)
                         </span>
                     </li>
                 `;
                 return;
             }

             let paginationHTML = '';
             
             // Previous button
             paginationHTML += `
                 <li class="page-item ${currentContentPage === 1 ? 'disabled' : ''}">
                     <a class="page-link" href="#" onclick="goToContentPage(${currentContentPage - 1})" ${currentContentPage === 1 ? 'tabindex="-1"' : ''}>
                         Prev
                     </a>
                 </li>
             `;

             // Current page
             paginationHTML += `
                 <li class="page-item active">
                     <span class="page-link">
                         Page ${currentContentPage}
                     </span>
                 </li>
             `;

             // Next button
             paginationHTML += `
                 <li class="page-item ${currentContentPage === totalPages ? 'disabled' : ''}">
                     <a class="page-link" href="#" onclick="goToContentPage(${currentContentPage + 1})" ${currentContentPage === totalPages ? 'tabindex="-1"' : ''}>
                         Next
                     </a>
                 </li>
             `;

             pagination.innerHTML = paginationHTML;
         }

         // Go to specific content page
         function goToContentPage(page) {
             const totalPages = Math.ceil(filteredContent.length / contentPageSize);
             if (page >= 1 && page <= totalPages) {
                 currentContentPage = page;
                 renderContentTable();
                 renderContentPagination();
             }
         }

         // Export content
         function exportContent() {
             const csvContent = generateContentCSV();
             const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
             const link = document.createElement('a');
             const url = URL.createObjectURL(blob);
             link.setAttribute('href', url);
             link.setAttribute('download', `content_export_${new Date().toISOString().split('T')[0]}.csv`);
             link.style.visibility = 'hidden';
             document.body.appendChild(link);
             link.click();
             document.body.removeChild(link);
         }

         // Generate CSV for content export
         function generateContentCSV() {
             const headers = ['Title', 'Creator', 'Category', 'Status', 'Views', 'Created Date', 'Description'];
             const rows = filteredContent.map(item => [
                 item.title || 'Untitled',
                 item.creator_name || 'Unknown',
                 item.category || 'General',
                 item.status || 'pending',
                 item.view_count || 0,
                 new Date(item.created_at || Date.now()).toLocaleDateString(),
                 item.description ? item.description.substring(0, 100) : 'No description'
             ]);
             
             return [headers, ...rows].map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
         }
        
        function renderUserManagementTable(users) {
            const tbody = document.getElementById('userManagementTable');
            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary">No users found</td></tr>';
                return;
            }
            
            tbody.innerHTML = users.map(user => `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle me-2 fs-4"></i>
                            <div>
                                <div class="fw-semibold">${user.display_name || 'No Name'}</div>
                                <small class="text-secondary">ID: ${user.id}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="text-white">
                            ${user.role}
                        </span>
                    </td>
                    <td>${user.email}</td>
                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success" onclick="approveUser(${user.id})" title="Approve User">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="banUser(${user.id})" title="Ban User">
                                <i class="bi bi-person-x"></i>
                            </button>
                            <button class="btn btn-outline-light" onclick="openUserSettingsModal(${user.id})" title="User Settings">
                                <i class="bi bi-gear"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        
                 function renderContentModerationTable(content) {
             const tbody = document.getElementById('contentModerationTable');
             if (content.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary">No content found</td></tr>';
                 return;
             }
             
             tbody.innerHTML = content.map(item => `
                 <tr>
                     <td>
                         <div class="fw-semibold" title="${item.title || 'Untitled'}">${(item.title || 'Untitled').length > 30 ? (item.title || 'Untitled').substring(0, 30) + '...' : (item.title || 'Untitled')}</div>
                         <small class="text-secondary">${item.description ? item.description.substring(0, 50) + '...' : 'No description'}</small>
                     </td>
                    <td>${item.creator_name || 'Unknown'}</td>
                    <td>${(item.category || 'other').toString().toLowerCase().replace(/^./, c => c.toUpperCase())}</td>
                    <td>
                        <span class="${getStatusBadgeClass(item.status)} fw-semibold">
                            ${getStatusDisplayText(item.status)}
                        </span>
                    </td>
                    <td>${item.view_count || 0}</td>
                    <td>${item.created_at ? new Date(item.created_at).toLocaleDateString() : '—'}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success" onclick="approveContent(${item.id})" title="Approve">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button class="btn btn-outline-warning" onclick="moderateContent(${item.id})" title="Set to Pending">
                                <i class="bi bi-clock"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="rejectContent(${item.id})" title="Reject">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        
        function changeUserRole(userId) {
            const newRole = prompt('Enter new role (admin, creator, viewer):');
            if (newRole && ['admin', 'creator', 'viewer'].includes(newRole)) {
                // Here you would implement role change
                alert(`User ${userId} role changed to ${newRole} - Feature coming soon!`);
            }
        }
        
        async function approveUser(userId) {
            if (confirm(`Are you sure you want to approve user ${userId}?`)) {
                try {
                    const response = await fetch('/kabaka/public/api/admin.php?action=approve_user', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ user_id: userId })
                    });
                    
                    const result = await response.json();
                    
                    if (!response.ok || result.error) {
                        throw new Error(result.error || 'Failed to approve user');
                    }
                    
                    showToast('User approved successfully', 'success');
                    
                    // Refresh user data
                    loadAdminData();
                    
                } catch (error) {
                    console.error('Error approving user:', error);
                    showToast('Failed to approve user: ' + error.message, 'error');
                }
            }
        }

        async function banUser(userId) {
            if (confirm(`Are you sure you want to ban user ${userId}?`)) {
                try {
                    const response = await fetch('/kabaka/public/api/admin.php?action=ban_user', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ user_id: userId })
                    });
                    
                    const result = await response.json();
                    
                    if (!response.ok || result.error) {
                        throw new Error(result.error || 'Failed to ban user');
                    }
                    
                    showToast('User banned successfully', 'success');
                    
                    // Refresh user data
                    loadAdminData();
                    
                } catch (error) {
                    console.error('Error banning user:', error);
                    showToast('Failed to ban user: ' + error.message, 'error');
                }
            }
        }

        async function openUserSettingsModal(userId) {
            try {
                // Fetch user data from API (no toast for loading)
                const response = await fetch(`/kabaka/public/api/admin.php?action=user_details&user_id=${userId}`);
                const result = await response.json();
                
                if (!response.ok || result.error) {
                    throw new Error(result.error || 'Failed to load user data');
                }
                
                const userData = result.user;
                
                // Populate form with real user data
                document.getElementById('userDisplayName').value = userData.display_name || '';
                document.getElementById('userEmail').value = userData.email || '';
                document.getElementById('userRole').value = userData.role || 'viewer';
                document.getElementById('userStatus').value = userData.status || 'active';
                document.getElementById('userUsdtAddress').value = userData.usdt_address || '';
                document.getElementById('userPassword').value = '';
                
                // Store current user ID for update
                document.getElementById('userSettingsModal').setAttribute('data-user-id', userId);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('userSettingsModal'));
                modal.show();
                
            } catch (error) {
                console.error('Error loading user data:', error);
                showToast('Failed to load user data: ' + error.message, 'error');
            }
        }

        async function updateUserSettings() {
            const userId = document.getElementById('userSettingsModal').getAttribute('data-user-id');
            const formData = {
                user_id: userId,
                display_name: document.getElementById('userDisplayName').value,
                email: document.getElementById('userEmail').value,
                role: document.getElementById('userRole').value,
                status: document.getElementById('userStatus').value,
                password: document.getElementById('userPassword').value,
                usdt_address: document.getElementById('userUsdtAddress').value
            };
            
            try {
                // Send update request to API (no loading toast)
                const response = await fetch('/kabaka/public/api/admin.php?action=update_user', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (!response.ok || result.error) {
                    throw new Error(result.error || 'Failed to update user');
                }
                
                // Only show success toast when changes are made
                showToast('User updated successfully', 'success');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('userSettingsModal'));
                modal.hide();
                
                // Refresh user data
                loadAdminData();
                
            } catch (error) {
                console.error('Error updating user:', error);
                showToast('Failed to update user: ' + error.message, 'error');
            }
        }
        
        async function deleteUser() {
            const userId = document.getElementById('userSettingsModal').getAttribute('data-user-id');
            const userName = document.getElementById('userDisplayName').value || 'this user';
            
            if (confirm(`Are you sure you want to permanently delete ${userName}? This action cannot be undone!`)) {
                try {
                    const response = await fetch('/kabaka/public/api/admin.php?action=delete_user', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ user_id: userId })
                    });
                    
                    const result = await response.json();
                    
                    if (!response.ok || result.error) {
                        throw new Error(result.error || 'Failed to delete user');
                    }
                    
                    showToast('User deleted successfully', 'success');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('userSettingsModal'));
                    modal.hide();
                    
                    // Refresh user data
                    loadAdminData();
                    
                } catch (error) {
                    console.error('Error deleting user:', error);
                    showToast('Failed to delete user: ' + error.message, 'error');
                }
            }
        }
        
        async function approveContent(contentId) {
            if (confirm(`Are you sure you want to approve this content?`)) {
                try {
                    const formData = new FormData();
                    formData.append('content_id', contentId);
                    
                    const response = await fetch('/kabaka/public/api/admin.php?action=approve_content', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.ok) {
                        showToast(data.message, 'success');
                        loadContentModerationData(); // Refresh content table
                    } else {
                        showToast(data.error || 'Failed to approve content', 'error');
                    }
                } catch (error) {
                    showToast('Error approving content: ' + error.message, 'error');
                }
            }
        }
        
        async function rejectContent(contentId) {
            if (confirm(`Are you sure you want to reject this content?`)) {
                try {
                    const formData = new FormData();
                    formData.append('content_id', contentId);
                    
                    const response = await fetch('/kabaka/public/api/admin.php?action=reject_content', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.ok) {
                        showToast(data.message, 'success');
                        loadContentModerationData(); // Refresh content table
                    } else {
                        showToast(data.error || 'Failed to reject content', 'error');
                    }
                } catch (error) {
                    showToast('Error rejecting content: ' + error.message, 'error');
                }
            }
        }

        // Helper functions for content status display
        function getStatusBadgeClass(status) {
            switch (status) {
                case 'approved':
                case 'visible':
                    return 'text-success';
                case 'pending':
                    return 'text-warning';
                case 'rejected':
                    return 'text-danger';
                case 'removed':
                    return 'text-danger';
                default:
                    return 'text-secondary';
            }
        }
        
        function getStatusDisplayText(status) {
            switch (status) {
                case 'approved':
                    return 'Approved';
                case 'visible':
                    return 'Visible';
                case 'pending':
                    return 'Pending';
                case 'rejected':
                    return 'Rejected';
                case 'removed':
                    return 'Removed';
                default:
                    return status || 'Unknown';
            }
        }

        // Toast Notification Functions - Easy UI
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            // Get appropriate icon for each type
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            
            toast.innerHTML = `
                <div class="toast-header">
                    <span class="toast-title">
                        <span class="toast-icon">${icons[type] || icons.info}</span>
                        ${message}
                    </span>
                    <button class="toast-close" onclick="removeToast(this)" title="Close">&times;</button>
                </div>
            `;
            
            container.appendChild(toast);
            
            // Show toast with smooth animation
            setTimeout(() => toast.classList.add('show'), 100);
            
            // Auto remove after 4 seconds (shorter for better UX)
            setTimeout(() => removeToast(toast.querySelector('.toast-close')), 4000);
        }

        function removeToast(closeButton) {
            const toast = closeButton.closest('.toast');
            if (toast) {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }
        }

        // Dashboard refresh function
        function refreshDashboard() {
            showToast('Dashboard reloaded', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }



        // Form Validation Functions
        function validatePlatformSettings() {
            const siteName = document.getElementById('siteName').value.trim();
            const maxUploadSize = parseInt(document.getElementById('maxUploadSize').value);
            
            if (!siteName) {
                showToast('Site name is required', 'error');
                return false;
            }
            
            if (isNaN(maxUploadSize) || maxUploadSize < 1 || maxUploadSize > 1000) {
                showToast('Max upload size must be between 1 and 1000 MB', 'error');
                return false;
            }
            
            return true;
        }

        function validateMonetizationSettings() {
            const paymentPerViews = parseFloat(document.getElementById('paymentPerViews').value);
            const minFollowers = parseInt(document.getElementById('minFollowersForPay').value);
            const minViews = parseInt(document.getElementById('minViewsForPay').value);
            
            if (isNaN(paymentPerViews) || paymentPerViews < 0) {
                showToast('Payment per 1000 views must be a positive number', 'error');
                return false;
            }
            
            if (isNaN(minFollowers) || minFollowers < 0) {
                showToast('Minimum followers must be a positive number', 'error');
                return false;
            }
            
            if (isNaN(minViews) || minViews < 0) {
                showToast('Minimum views must be a positive number', 'error');
                return false;
            }
            
            return true;
        }

        function validatePaymentSettings() {
            const minWithdrawal = parseFloat(document.getElementById('minWithdrawal').value);
            const platformFee = parseFloat(document.getElementById('platformFee').value);
            const processingFee = parseFloat(document.getElementById('processingFee').value);
            
            if (isNaN(minWithdrawal) || minWithdrawal < 0) {
                showToast('Minimum withdrawal amount must be a positive number', 'error');
                return false;
            }
            
            if (isNaN(platformFee) || platformFee < 0 || platformFee > 100) {
                showToast('Platform fee must be between 0 and 100%', 'error');
                return false;
            }
            
            if (isNaN(processingFee) || processingFee < 0) {
                showToast('Processing fee must be a positive number', 'error');
                return false;
            }
            
            return true;
        }

        function validateCreatorRequirements() {
            const minContentPosts = parseInt(document.getElementById('minContentPosts').value);
            const minAccountAge = parseInt(document.getElementById('minAccountAge').value);
            
            if (isNaN(minContentPosts) || minContentPosts < 0) {
                showToast('Minimum content posts must be a positive number', 'error');
                return false;
            }
            
            if (isNaN(minAccountAge) || minAccountAge < 0) {
                showToast('Minimum account age must be a positive number', 'error');
                return false;
            }
            
            return true;
        }

        function validateModerationSettings() {
            const autoFlagThreshold = parseInt(document.getElementById('autoFlagThreshold').value);
            const reviewTimeLimit = parseInt(document.getElementById('reviewTimeLimit').value);
            
            if (isNaN(autoFlagThreshold) || autoFlagThreshold < 0) {
                showToast('Auto flag threshold must be a positive number', 'error');
                return false;
            }
            
            if (isNaN(reviewTimeLimit) || reviewTimeLimit < 1) {
                showToast('Review time limit must be at least 1 hour', 'error');
                return false;
            }
            
            return true;
        }
        
                 function savePlatformSettings() {
               if (!validatePlatformSettings()) return;
               
               const siteName = document.getElementById('siteName').value.trim();
               const maintenanceMode = document.getElementById('maintenanceMode').checked;
               const maxUploadSize = parseInt(document.getElementById('maxUploadSize').value);
               const autoApprove = document.getElementById('autoApprove').checked;
               const requireCreatorApproval = document.getElementById('requireCreatorApproval').checked;
               
               fetch('/kabaka/public/api/admin.php?action=save_settings', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({
                   group: 'platform',
                   data: {
                     site_name: siteName,
                     max_upload_size_mb: maxUploadSize,
                     maintenance_mode: !!maintenanceMode,
                     auto_approve: !!autoApprove,
                     require_creator_approval: !!requireCreatorApproval
                   }
                 })
               })
               .then(r => r.json())
               .then(data => {
                 if (data && data.ok) {
                   showToast('Platform settings updated', 'success');
                 } else {
                   showToast((data && data.error) || 'Failed to save platform settings', 'error');
                 }
               })
               .catch(() => showToast('Network error - please try again', 'error'));
           }
 
           function saveMonetizationSettings() {
               if (!validateMonetizationSettings()) return;
               
               const paymentPerViews = parseFloat(document.getElementById('paymentPerViews').value);
               const minFollowersForPay = parseInt(document.getElementById('minFollowersForPay').value);
               const minViewsForPay = parseInt(document.getElementById('minViewsForPay').value);
               const enableMonetization = document.getElementById('enableMonetization').checked;
               
               fetch('/kabaka/public/api/admin.php?action=save_settings', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({
                   group: 'monetization',
                   data: {
                     payment_per_1000_views: paymentPerViews,
                     min_followers_for_pay: minFollowersForPay,
                     min_views_for_payment: minViewsForPay,
                     enable_monetization: !!enableMonetization
                   }
                 })
               })
               .then(r => r.json())
               .then(data => {
                 if (data && data.ok) {
                   showToast('Monetization settings updated', 'success');
                 } else {
                   showToast((data && data.error) || 'Failed to save monetization settings', 'error');
                 }
               })
               .catch(() => showToast('Network error - please try again', 'error'));
           }
 
           function saveCreatorRequirements() {
               if (!validateCreatorRequirements()) return;
               
               const minContentPosts = parseInt(document.getElementById('minContentPosts').value);
               const minAccountAge = parseInt(document.getElementById('minAccountAge').value);
               const requireVerification = document.getElementById('requireVerification').checked;
               
               fetch('/kabaka/public/api/admin.php?action=save_settings', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({
                   group: 'creator_requirements',
                   data: {
                     min_content_posts: minContentPosts,
                     min_account_age_days: minAccountAge,
                     require_verification: !!requireVerification
                   }
                 })
               })
               .then(r => r.json())
               .then(data => {
                 if (data && data.ok) {
                   showToast('Creator requirements updated', 'success');
                 } else {
                   showToast((data && data.error) || 'Failed to save creator requirements', 'error');
                 }
               })
               .catch(() => showToast('Network error - please try again', 'error'));
           }
 
           function savePaymentSettings() {
               if (!validatePaymentSettings()) return;
               
               const minWithdrawal = parseFloat(document.getElementById('minWithdrawal').value);
               const platformFee = parseFloat(document.getElementById('platformFee').value);
               const processingFee = parseFloat(document.getElementById('processingFee').value);
               const autoPayouts = document.getElementById('autoPayouts').checked;
               
               fetch('/kabaka/public/api/admin.php?action=save_settings', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({
                   group: 'payment',
                   data: {
                     min_withdrawal_amount: minWithdrawal,
                     platform_fee_percent: platformFee,
                     processing_fee: processingFee,
                     auto_payouts: !!autoPayouts
                   }
                 })
               })
               .then(r => r.json())
               .then(data => {
                 if (data && data.ok) {
                   showToast('Payment settings updated', 'success');
                 } else {
                   showToast((data && data.error) || 'Failed to save payment settings', 'error');
                 }
               })
               .catch(() => showToast('Network error - please try again', 'error'));
           }
 
           function saveModerationSettings() {
               if (!validateModerationSettings()) return;
               
               const autoFlagThreshold = parseInt(document.getElementById('autoFlagThreshold').value);
               const reviewTimeLimit = parseInt(document.getElementById('reviewTimeLimit').value);
               
               fetch('/kabaka/public/api/admin.php?action=save_settings', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({
                   group: 'moderation',
                   data: {
                     auto_flag_threshold: autoFlagThreshold,
                     review_time_limit_hours: reviewTimeLimit
                   }
                 })
               })
               .then(r => r.json())
               .then(data => {
                 if (data && data.ok) {
                   showToast('Moderation settings updated', 'success');
                 } else {
                   showToast((data && data.error) || 'Failed to save moderation settings', 'error');
                 }
               })
               .catch(() => showToast('Network error - please try again', 'error'));
           }
        
                     // Tab event listeners
             document.addEventListener('DOMContentLoaded', function() {
                 loadAdminData();
                 
                 // Initialize user management data on page load
                 loadUserManagementData();
                 
                 // Load data when tabs are clicked
                 document.getElementById('users-tab').addEventListener('click', function() {
                     loadUserManagementData();
                 });
                 
                 document.getElementById('content-tab').addEventListener('click', function() {
                     loadContentModerationData();
                 });
                 
                 // Add event listener for content status filter
                 const contentStatusFilter = document.getElementById('statusFilter');
                 if (contentStatusFilter) {
                     contentStatusFilter.addEventListener('change', function() {
                         filterContent();
                     });
                 }
                 
                 document.getElementById('settings-tab').addEventListener('click', function() {
                     loadSettings();
                 });
                 
                 // Admin newsletter form
                 document.getElementById('adminNewsletterForm').addEventListener('submit', function(e) {
                     e.preventDefault();
                     const email = this.querySelector('input[type="email"]').value;
                     if (email) {
                         alert('Admin newsletter subscription successful!');
                         this.reset();
                     }
                 });
             });

        let cachedActivityItems = [];
        function renderRecentActivity(items) {
            cachedActivityItems = items || [];
            const container = document.getElementById('recentActivityList');
            if (!items || items.length === 0) {
                container.innerHTML = '<div class="text-secondary small">No recent activity</div>';
                return;
            }
            const iconByType = {
                new_user: { cls: 'bg-success', icon: 'bi-person-plus' },
                new_content: { cls: 'bg-primary', icon: 'bi-collection-play' },
                moderation: { cls: 'bg-warning', icon: 'bi-shield-exclamation' },
                comment: { cls: 'bg-info', icon: 'bi-chat' },
                default: { cls: 'bg-secondary', icon: 'bi-activity' }
            };
            container.innerHTML = items.slice(0, 5).map(it => {
                const meta = iconByType[it.type] || iconByType.default;
                const title = it.title || 'Activity';
                const details = it.details || '';
                const when = timeAgo(it.created_at);
                return `
                    <div class="activity-item d-flex align-items-center mb-3">
                        <div class="activity-icon ${meta.cls} rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi ${meta.icon} text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-white small fw-semibold">${title}</div>
                            <div class="text-secondary small">${details} · ${when}</div>
                        </div>
                    </div>`;
            }).join('');
        }

        function renderAllActivityList(items) {
            const list = document.getElementById('allActivityList');
            if (!items || items.length === 0) {
                list.innerHTML = '<div class="text-secondary small">No recent activity</div>';
                return;
            }
            const iconByType = {
                new_user: { cls: 'bg-success', icon: 'bi-person-plus' },
                new_content: { cls: 'bg-primary', icon: 'bi-collection-play' },
                moderation: { cls: 'bg-warning', icon: 'bi-shield-exclamation' },
                comment: { cls: 'bg-info', icon: 'bi-chat' },
                default: { cls: 'bg-secondary', icon: 'bi-activity' }
            };
            list.innerHTML = items.map(it => {
                const meta = iconByType[it.type] || iconByType.default;
                const title = it.title || 'Activity';
                const details = it.details || '';
                const when = timeAgo(it.created_at);
                return `
                    <div class="activity-item d-flex align-items-center mb-2">
                        <div class="activity-icon ${meta.cls} rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                            <i class="bi ${meta.icon} text-white" style="font-size: 0.9rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-white small fw-semibold">${title}</div>
                            <div class="text-secondary small">${details} · ${when}</div>
                        </div>
                    </div>`;
            }).join('');
        }

        function showAllActivity() {
            renderAllActivityList(cachedActivityItems);
            const modal = new bootstrap.Modal(document.getElementById('recentActivityModal'));
            modal.show();
        }

        function goToTab(selector) {
            const triggerEl = document.querySelector(`[data-bs-target="${selector}"]`);
            if (triggerEl) {
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
            if (selector === '#users') {
                loadUserManagementData();
            } else if (selector === '#content') {
                loadContentModerationData();
            }
        }

        async function openContentDetails(id) {
            try {
                console.log('Opening content details for ID:', id);
                document.getElementById('cdLoading').style.display = '';
                document.getElementById('cdBody').style.display = 'none';
                document.getElementById('cdError').style.display = 'none';
                const modal = new bootstrap.Modal(document.getElementById('contentDetailsModal'));
                
                // Remove any existing event listeners to prevent duplicates
                const modalElement = modal._element;
                const existingHandler = modalElement._mediaStopHandler;
                if (existingHandler) {
                    modalElement.removeEventListener('hidden.bs.modal', existingHandler);
                }
                
                // Add event listener to stop media when modal is hidden
                const mediaStopHandler = function() {
                    stopAllMedia();
                };
                modalElement.addEventListener('hidden.bs.modal', mediaStopHandler);
                modalElement._mediaStopHandler = mediaStopHandler; // Store reference for cleanup
                
                modal.show();
                const res = await fetch(`/kabaka/public/api/content.php?id=${id}`);
                const data = await res.json();
                console.log('Content API response:', data);
                
                if (!data || !data.success || data.error) {
                    document.getElementById('cdError').style.display = '';
                    document.getElementById('cdError').textContent = data?.error || 'Failed to load content';
                    document.getElementById('cdLoading').style.display = 'none';
                    return;
                }
                const c = data.data;
                
                // Helper function to format file size
                function formatFileSize(bytes) {
                    if (!bytes || bytes === 0) return 'No';
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(1024));
                    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
                }
                
                // Helper function to format date
                function formatDate(dateStr) {
                    if (!dateStr) return 'No';
                    return new Date(dateStr).toLocaleString();
                }
                
                // Helper function to show descriptive "No" messages for empty values
                function showValue(value, fallback = 'No') {
                    return value && value.toString().trim() !== '' ? value : fallback;
                }
                
                // Populate all fields with descriptive messages
                document.getElementById('cdTitle').textContent = showValue(c.title, 'Untitled');
                document.getElementById('cdCreator').textContent = showValue(c.creator_name, 'No creator');
                document.getElementById('cdCategory').textContent = showValue(c.category, 'No category');
                document.getElementById('cdStatus').textContent = showValue(c.status, 'No status');
                document.getElementById('cdCreatedAt').textContent = formatDate(c.created_at);
                document.getElementById('cdDescription').textContent = showValue(c.description, 'No description');
                document.getElementById('cdTags').textContent = showValue(c.tags, 'No tags');
                document.getElementById('cdFileType').textContent = showValue(c.file_type, 'No file type');
                document.getElementById('cdFileSize').textContent = formatFileSize(c.file_size);
                document.getElementById('cdOriginalFilename').textContent = showValue(c.original_filename, 'No filename');
                document.getElementById('cdOwnershipNote').textContent = showValue(c.ownership_note, 'No ownership note');
                document.getElementById('cdViews').textContent = c.view_count || 0;
                document.getElementById('cdLikes').textContent = c.like_count || 0;
                document.getElementById('cdComments').textContent = c.comment_count || 0;
                document.getElementById('cdUpdatedAt').textContent = formatDate(c.updated_at);
                
                // Handle thumbnail
                const thumbnail = document.getElementById('cdThumbnail');
                if (c.thumbnail_url && c.thumbnail_url.trim() !== '') {
                    thumbnail.style.background = 'rgba(255,255,255,.05)';
                    thumbnail.style.justifyContent = 'center';
                    thumbnail.style.padding = '8px';
                    thumbnail.innerHTML = `<img src="${c.thumbnail_url}" alt="thumbnail" class="img-fluid rounded" style="max-height: 100px; width: auto;">`;
                } else {
                    thumbnail.style.background = 'transparent';
                    thumbnail.style.justifyContent = 'flex-start';
                    thumbnail.style.padding = '0';
                    thumbnail.style.minHeight = 'auto';
                    thumbnail.innerHTML = '<div class="text-secondary">No thumbnail</div>';
                }
                
                // Handle media content
                const media = document.getElementById('cdMedia');
                if (c.media_url && c.media_url.trim() !== '') {
                    media.style.background = 'rgba(255,255,255,.05)';
                    media.style.justifyContent = 'center';
                    media.style.padding = '8px';
                    if ((c.file_type || '').startsWith('image/')) {
                        media.innerHTML = `<img src="${c.media_url}" alt="media" class="img-fluid rounded" style="width: 100%; height: auto;">`;
                    } else if ((c.file_type || '').startsWith('video/')) {
                        media.innerHTML = `<video src="${c.media_url}" controls class="w-100 rounded" style="width: 100%; height: auto;"></video>`;
                    } else if ((c.file_type || '').startsWith('audio/')) {
                        media.innerHTML = `<audio src="${c.media_url}" controls class="w-100"></audio>`;
                    } else {
                        media.innerHTML = `<a href="${c.media_url}" target="_blank" class="text-white small">${c.media_url}</a>`;
                    }
                } else {
                    media.style.background = 'transparent';
                    media.style.justifyContent = 'flex-start';
                    media.style.padding = '0';
                    media.style.minHeight = 'auto';
                    media.innerHTML = '<div class="text-secondary">No media content</div>';
                }
                document.getElementById('cdLoading').style.display = 'none';
                document.getElementById('cdBody').style.display = '';
            } catch (e) {
                document.getElementById('cdError').style.display = '';
                document.getElementById('cdError').textContent = 'Error loading content';
                document.getElementById('cdLoading').style.display = 'none';
            }
        }

        function stopAllMedia() {
            // Stop all audio and video elements in the content details modal
            const mediaContainer = document.getElementById('cdMedia');
            if (mediaContainer) {
                const audioElements = mediaContainer.querySelectorAll('audio');
                const videoElements = mediaContainer.querySelectorAll('video');
                
                // Pause all audio elements
                audioElements.forEach(audio => {
                    audio.pause();
                    audio.currentTime = 0; // Reset to beginning
                });
                
                // Pause all video elements
                videoElements.forEach(video => {
                    video.pause();
                    video.currentTime = 0; // Reset to beginning
                });
            }
        }

        function loadSettings() {
            fetch('/kabaka/public/api/admin.php?action=settings')
                .then(r => r.json())
                .then(s => {
                    if (!s || s.error) {
                        showToast('Warning', 'Could not load settings from server', 'warning');
                        return;
                    }
                    
                    let loadedCount = 0;
                    
                    // Platform
                    if (s.platform) {
                        if (document.getElementById('siteName')) document.getElementById('siteName').value = s.platform.site_name ?? '';
                        if (document.getElementById('maxUploadSize')) document.getElementById('maxUploadSize').value = s.platform.max_upload_size_mb ?? 0;
                        if (document.getElementById('maintenanceMode')) document.getElementById('maintenanceMode').checked = !!Number(s.platform.maintenance_mode);
                        if (document.getElementById('autoApprove')) document.getElementById('autoApprove').checked = !!Number(s.platform.auto_approve);
                        if (document.getElementById('requireCreatorApproval')) document.getElementById('requireCreatorApproval').checked = !!Number(s.platform.require_creator_approval);
                        loadedCount++;
                    }
                    // Monetization
                    if (s.monetization) {
                        if (document.getElementById('paymentPerViews')) document.getElementById('paymentPerViews').value = s.monetization.payment_per_1000_views ?? 0;
                        if (document.getElementById('minFollowersForPay')) document.getElementById('minFollowersForPay').value = s.monetization.min_followers_for_pay ?? 0;
                        if (document.getElementById('minViewsForPay')) document.getElementById('minViewsForPay').value = s.monetization.min_views_for_payment ?? 0;
                        if (document.getElementById('enableMonetization')) document.getElementById('enableMonetization').checked = !!Number(s.monetization.enable_monetization);
                        loadedCount++;
                    }
                    // Payment
                    if (s.payment) {
                        if (document.getElementById('minWithdrawal')) document.getElementById('minWithdrawal').value = s.payment.min_withdrawal_amount ?? 0;
                        if (document.getElementById('platformFee')) document.getElementById('platformFee').value = s.payment.platform_fee_percent ?? 0;
                        if (document.getElementById('processingFee')) document.getElementById('processingFee').value = s.payment.processing_fee ?? 0;
                        if (document.getElementById('autoPayouts')) document.getElementById('autoPayouts').checked = !!Number(s.payment.auto_payouts);
                        loadedCount++;
                    }
                    // Creator requirements
                    if (s.creator_requirements) {
                        if (document.getElementById('minContentPosts')) document.getElementById('minContentPosts').value = s.creator_requirements.min_content_posts ?? 0;
                        if (document.getElementById('minAccountAge')) document.getElementById('minAccountAge').value = s.creator_requirements.min_account_age_days ?? 0;
                        if (document.getElementById('requireVerification')) document.getElementById('requireVerification').checked = !!Number(s.creator_requirements.require_verification);
                        loadedCount++;
                    }
                    // Moderation
                    if (s.moderation) {
                        if (document.getElementById('autoFlagThreshold')) document.getElementById('autoFlagThreshold').value = s.moderation.auto_flag_threshold ?? 5;
                        if (document.getElementById('reviewTimeLimit')) document.getElementById('reviewTimeLimit').value = s.moderation.review_time_limit_hours ?? 24;
                        loadedCount++;
                    }
                    // Auto Actions
                    if (s.auto_actions) {
                        const aa = s.auto_actions;
                        const mode = Number(aa.auto_reject_uploads) ? 'reject' : Number(aa.auto_moderate_uploads) ? 'moderate' : Number(aa.auto_approve_uploads) ? 'approve' : '';
                        // Clear all first
                        document.querySelectorAll('input[name="autoActionMode"]').forEach(r => r.checked = false);
                        if (mode) {
                            const el = document.querySelector(`input[name="autoActionMode"][value="${mode}"]`);
                            if (el) el.checked = true;
                        }
                        loadedCount++;
                    }
                    
                    // Settings loaded silently without toast
                })
                .catch(() => { 
                    showToast('Failed to load!', 'error');
                });
        }

        function saveAutoActionsSettings() {
            const selected = document.querySelector('input[name="autoActionMode"]:checked');
            const mode = selected ? selected.value : 'none';

            fetch('/kabaka/public/api/admin.php?action=save_settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    group: 'auto_actions',
                    data: { mode }
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.ok) {
                    showToast('Auto actions updated', 'success');
                } else {
                    showToast((data && data.error) || 'Failed to save auto actions', 'error');
                }
            })
            .catch(() => showToast('Network error - please try again', 'error'));
        }

        // Load settings immediately on page load as well
        loadSettings();

        // Footer Quick Links: activate tabs and scroll to top (no UI changes)
        document.querySelectorAll('footer a[href^="#"]').forEach(function(anchor){
            anchor.addEventListener('click', function(e){
                // Skip links that have their own onclick (e.g., Settings Shortcuts)
                if (anchor.hasAttribute('onclick')) { return; }
                var href = anchor.getAttribute('href');
                var tabEl = document.querySelector('button[data-bs-toggle="tab"][data-bs-target="'+href+'"]') || document.querySelector('a[data-bs-toggle="tab"][href="'+href+'"]');
                if (tabEl) {
                    e.preventDefault();
                    new bootstrap.Tab(tabEl).show();
                    // Trigger data loads for specific tabs
                    if (href === '#content' && typeof loadContentModerationData === 'function') {
                        setTimeout(function(){ loadContentModerationData(); }, 0);
                    }
                    if (href === '#users' && typeof loadUserManagementData === 'function') {
                        setTimeout(function(){ loadUserManagementData(); }, 0);
                    }
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        });

        // Newsletter form: simple in-page handler
        (function(){
            var form = document.getElementById('adminNewsletterForm');
            if (!form) return;
            form.addEventListener('submit', async function(e){
                e.preventDefault();
                var input = form.querySelector('input[type="email"]');
                if (!input || !input.value.trim()) { if (typeof showToast === 'function') showToast('Please enter your email', 'error'); return; }
                try {
                    var res = await fetch('/kabaka/public/api/newsletter.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email: input.value.trim() }) });
                    var data = await res.json();
                    if (res.ok && data && data.ok) { if (typeof showToast === 'function') showToast('Subscribed to updates', 'success'); input.value=''; }
                    else { if (typeof showToast === 'function') showToast(data.error || 'Subscription failed', 'error'); }
                } catch(err){ if (typeof showToast === 'function') showToast('Network error', 'error'); }
            });
        })();
    </script>
</body>
</html>
