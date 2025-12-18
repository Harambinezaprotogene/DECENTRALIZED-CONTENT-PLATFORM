<?php
session_start();
// Simple admin check (adjust if your app uses different session keys)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /kabaka/public/admin_dashboard/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabaka Admin - Support</title>
    <link rel="icon" type="image/svg+xml" href="/kabaka/public/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #0a0a0a;
            --secondary-bg: #1a1a1a;
            --accent-color: #6366f1;
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
        }

        body {
            background: var(--primary-bg);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            padding-top: 24px;
        }

        .guide-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1rem;
        }

        .guide-header {
            margin-bottom: 3rem;
        }

        .guide-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--accent-color), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .guide-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .guide-card {
            background: var(--secondary-bg);
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .guide-card:hover {
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .guide-card h3 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }

        .guide-card h3 i {
            color: var(--accent-color);
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .guide-card p { color: var(--text-secondary); line-height: 1.5; margin-bottom: 0.75rem; font-size: 0.9rem; }
        .feature-list { list-style: none; padding: 0; }
        .feature-list li { color: var(--text-secondary); margin-bottom: 0.5rem; padding-left: 1.5rem; position: relative; }
        .feature-list li::before { content: "✓"; color: var(--accent-color); font-weight: bold; position: absolute; left: 0; }

        .back-btn { background: var(--accent-color); border: none; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.3s ease; margin-bottom: 2rem; }
        .back-btn:hover { background: #5856eb; color: white; transform: translateY(-1px); }
        .back-btn i { margin-right: 0.5rem; }

        .danger-card { background: rgba(220, 38, 38, 0.1); border: 1px solid rgba(220, 38, 38, 0.3); }
        .danger-card h3 i { color: #ef4444; }
    </style>
</head>
<body>
    <div class="guide-container">
        <a href="/kabaka/public/admin_dashboard/dashboard.php" class="back-btn">
            <i class="bi bi-arrow-left"></i>Back to Dashboard
        </a>

        <div class="guide-header">
            <h1 class="guide-title">Admin Support</h1>
            <p class="guide-subtitle">Help, policies, and quick guidance for administrators</p>
                </div>

        <div class="guide-card">
            <h3><i class="bi bi-info-circle"></i>Getting Started</h3>
            <p>Learn the layout of the Admin Dashboard and where to find common actions. Each tab focuses on a specific domain, and most actions are available via clear buttons on tables or within modals.</p>
            <ul class="feature-list">
                <li>Tabs: Overview, Users, Content, Settings</li>
                <li>Use the Content tab to review <strong>Flagged Content</strong> with report counts and reasons</li>
                <li><strong>Recent Activity</strong> shows the latest moderation actions with timestamps</li>
                <li>Most tables support sorting and paging; use search to narrow results quickly</li>
                            </ul>
                        </div>

        <div class="guide-card">
            <h3><i class="bi bi-people-fill"></i>User Management</h3>
            <p>Search, filter, and manage users by role and status. Use the action buttons to respond quickly to abuse or resolve account issues.</p>
            <ul class="feature-list">
                <li><strong>Approve</strong> creators that meet your criteria; <strong>Ban</strong> users violating policies</li>
                <li>Open <strong>Settings</strong> to edit credentials or delete an account when requested</li>
                <li>Use filters (role, status) and search (email, name) to quickly narrow down results</li>
                <li>Export user lists for audits or reports where available</li>
                            </ul>
                        </div>

        <div class="guide-card">
            <h3><i class="bi bi-collection-play"></i>Manage Content</h3>
            <p>Review reports, approve flagged content, and monitor activity. Focus first on items with higher report counts or serious reasons.</p>
            <ul class="feature-list">
                <li>Open <strong>Flagged Content</strong> from the Content tab; view aggregated report reasons</li>
                <li><strong>Approve</strong> restores visibility and automatically clears related reports</li>
                <li>Use <strong>View Reports</strong> to see individual submissions before deciding</li>
                <li>Cross-check <strong>Recent Activity</strong> to confirm who took which action and when</li>
                            </ul>
                        </div>

        <div class="guide-card">
            <h3><i class="bi bi-gear-fill"></i>Settings & Monetization</h3>
            <p>Configure monetization thresholds and enable or disable payouts. Small changes may impact creator eligibility, so communicate major updates.</p>
            <ul class="feature-list">
                <li>Adjust <strong>payment per 1000 views</strong> and minimum eligibility thresholds</li>
                <li>Toggle <strong>enable monetization</strong> to turn payouts on or off platform-wide</li>
                <li>Changes apply immediately; verify expected results on creator dashboards</li>
                            </ul>
                        </div>

        <div class="guide-card danger-card">
            <h3><i class="bi bi-exclamation-triangle"></i>Troubleshooting</h3>
            <p>Quick tips when something isn’t working as expected. Start with the browser console, then validate API responses and database connectivity.</p>
            <ul class="feature-list">
                <li>Check console/network tab for failing requests or JavaScript errors</li>
                <li>Verify DB connection and API endpoints (e.g., admin.php, moderation.php)</li>
                <li>Confirm roles and sessions for access issues; re-login if the session expired</li>
                <li>If media auto-downloads on private pages, ensure headers use <code>Content-Disposition: inline</code></li>
                            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
