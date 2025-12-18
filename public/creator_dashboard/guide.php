<?php
session_start();
if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'creator') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Guide - Kabaka</title>
    <link rel="icon" type="image/svg+xml" href="/kabaka/public/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }

        .navbar {
            background: var(--secondary-bg) !important;
            border-bottom: 1px solid #2a2a2a;
        }

        .navbar-brand {
            color: var(--text-primary) !important;
            font-weight: 600;
        }

        .guide-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1rem;
        }

        .guide-header {
            text-align: center;
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

        .guide-card p {
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .feature-list li::before {
            content: "✓";
            color: var(--accent-color);
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .back-btn {
            background: var(--accent-color);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .back-btn:hover {
            background: #5856eb;
            color: white;
            transform: translateY(-1px);
        }

        .back-btn i {
            margin-right: 0.5rem;
        }

        .step-number {
            background: var(--accent-color);
            color: white;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .step-content h4 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .step-content p {
            color: var(--text-secondary);
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="guide-container">
        <a href="dashboard.php" class="back-btn">
            <i class="bi bi-arrow-left"></i>Back to Dashboard
        </a>

        <!-- Overview -->
        <div class="guide-card">
            <h3><i class="bi bi-info-circle"></i>Dashboard Overview</h3>
            <p>The Kabaka Creator Dashboard is your central hub for managing content, tracking performance, and earning money. It's designed to be simple and intuitive while providing all the tools you need to succeed as a creator.</p>
            
            <h4 class="text-white mt-3 mb-2">Main Sections:</h4>
            <ul class="feature-list">
                <li><strong>Overview:</strong> Quick stats and analytics at a glance</li>
                <li><strong>Upload:</strong> Create and publish new content</li>
                <li><strong>My Content:</strong> Manage your existing posts</li>
                <li><strong>Settings:</strong> Update your profile and preferences</li>
                <li><strong>Payments:</strong> Track earnings and withdraw funds</li>
            </ul>
        </div>

        <!-- Getting Started -->
        <div class="guide-card">
            <h3><i class="bi bi-play-circle"></i>Getting Started</h3>
            
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Complete Your Profile</h4>
                    <p>Go to Settings and fill out your profile information. Add a profile picture, bio, and payout details for payments.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Get Verified</h4>
                    <p>Request account verification in the Payments section. This unlocks monetization features and higher earning potential.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Upload Your First Content</h4>
                    <p>Use the Upload tab to create your first post. Add engaging titles, descriptions, and relevant tags to maximize visibility.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>Track Your Performance</h4>
                    <p>Monitor your content performance in the Overview section. Track views, likes, and earnings to optimize your strategy.</p>
                </div>
            </div>
        </div>

        <!-- Content Management -->
        <div class="guide-card">
            <h3><i class="bi bi-camera-reels"></i>Content Management</h3>
            <p>Creating engaging content is key to building your audience and increasing earnings.</p>
            
            <h4 class="text-white mt-3 mb-2">Best Practices:</h4>
            <ul class="feature-list">
                <li><strong>Quality Content:</strong> Upload high-quality images and videos</li>
                <li><strong>Engaging Titles:</strong> Write compelling titles that grab attention</li>
                <li><strong>Descriptions:</strong> Add detailed descriptions with relevant keywords</li>
                <li><strong>Consistent Posting:</strong> Maintain a regular posting schedule</li>
                <li><strong>Interact:</strong> Respond to comments and engage with your audience</li>
            </ul>

            <h4 class="text-white mt-3 mb-2">Content Types:</h4>
            <ul class="feature-list">
                <li><strong>Images:</strong> Photos, artwork, infographics</li>
                <li><strong>Videos:</strong> Short clips, tutorials, entertainment</li>
                <li><strong>Text Posts:</strong> Stories, tips, announcements</li>
            </ul>
        </div>

        <!-- Earnings & Payments -->
        <div class="guide-card">
            <h3><i class="bi bi-cash-coin"></i>Earnings & Payments</h3>
            <p>Earn money from your content through views and engagement. The more popular your content becomes, the more you earn.</p>
            
            <h4 class="text-white mt-3 mb-2">How Earnings Work:</h4>
            <ul class="feature-list">
                <li><strong>View-Based:</strong> Earn money for each view your content receives</li>
                <li><strong>Engagement Bonus:</strong> Higher engagement rates increase earning potential</li>
                <li><strong>Verification Benefits:</strong> Verified creators earn more per view</li>
                <li><strong>Minimum Threshold:</strong> Withdraw funds once you reach the minimum amount</li>
            </ul>

            <h4 class="text-white mt-3 mb-2">Payment Process:</h4>
            <ul class="feature-list">
                <li><strong>Automatic Processing:</strong> Withdrawals are processed automatically</li>
                <li><strong>USDT Payments:</strong> Funds are sent to your USDT address</li>
                <li><strong>Fee Structure:</strong> Small platform and processing fees apply</li>
                <li><strong>Transaction History:</strong> Track all payments in the Payments section</li>
            </ul>
        </div>

        <!-- Tips for Success -->
        <div class="guide-card">
            <h3><i class="bi bi-lightbulb"></i>Tips for Success</h3>
            
            <h4 class="text-white mb-2">Content Strategy:</h4>
            <ul class="feature-list">
                <li>Post consistently to maintain audience engagement</li>
                <li>Use trending topics and hashtags when relevant</li>
                <li>Create content that provides value to your audience</li>
                <li>Experiment with different content formats</li>
            </ul>

            <h4 class="text-white mt-3 mb-2">Growth Tips:</h4>
            <ul class="feature-list">
                <li>Engage with other creators' content</li>
                <li>Share your content on other social platforms</li>
                <li>Build a community around your niche</li>
                <li>Listen to feedback and adapt your content</li>
            </ul>

            <h4 class="text-white mt-3 mb-2">Technical Tips:</h4>
                <li>Use the refresh button to update your content list</li>
                <li>Check the View Analytics for performance insights</li>
                <li>Keep your profile information up to date</li>
                <li>Monitor your earnings regularly</li>
            </ul>
        </div>

        <!-- Support -->
        <div class="guide-card">
            <h3><i class="bi bi-headset"></i>Need Help?</h3>
            <p>If you have questions or need assistance, we're here to help!</p>
            
            <h4 class="text-white mt-3 mb-2">Support Options:</h4>
            <ul class="feature-list">
                <li><strong>Community:</strong> Connect with other creators for tips and advice</li>
                <li><strong>Contact Support:</strong> Reach out to our support team for technical issues</li>
                <li><strong>Creator Guide:</strong> This guide covers all the basics</li>
                <li><strong>Dashboard Help:</strong> Use the refresh and analytics features to troubleshoot</li>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
