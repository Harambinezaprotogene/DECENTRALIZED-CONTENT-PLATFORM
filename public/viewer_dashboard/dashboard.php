<?php
session_start();

if (!isset($_SESSION['viewer_id'])) {
    header('Location: /kabaka/public/viewer_dashboard/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewer Dashboard - Kabaka</title>
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
        }
        
        body {
            min-height: 100vh;
            background: #1a1a1a;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            color: #f8fafc;
            padding-top: 24px;
        }
        
        .glass {
            background: rgba(255,255,255,.14);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(0,0,0,.35);
        }
        
        .navbar {
            background: rgba(15,23,42,.3);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,.1);
            padding: 1rem 0;
        }
        /* Transparent profile dropdown */
        .dropdown-menu {
            background: rgba(15,23,42,.6) !important;
            backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255,255,255,.12) !important;
            box-shadow: 0 8px 24px rgba(0,0,0,.35) !important;
        }
        .dropdown-menu .dropdown-item {
            color: rgba(255,255,255,.9) !important;
        }
        .dropdown-menu .dropdown-item:hover,
        .dropdown-menu .dropdown-item:focus {
            background: rgba(255,255,255,.08) !important;
            color: #fff !important;
        }
        
        .navbar-brand {
            font-weight: 800;
            letter-spacing: .6px;
            color: white !important;
        }
        
        .btn-primary {
            background: linear-gradient(90deg,#2563eb,#7c3aed);
            border: 0;
            box-shadow: 0 3px 8px rgba(124,58,237,.15);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            filter: brightness(1.06);
        }
        
        .btn-outline-light {
            border: 2px solid rgba(255,255,255,.3);
            background: transparent;
            color: white;
            font-weight: 600;
        }
        
        .btn-outline-light:hover {
            background: rgba(255,255,255,.1);
            border-color: rgba(255,255,255,.5);
            transform: translateY(-1px);
        }
        
        .search-input {
            background: rgba(255,255,255,.1);
            border: 2px solid rgba(255,255,255,.2);
            border-radius: 12px 0 0 12px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            color: white;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            background: rgba(255,255,255,.15);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(34,211,238,.2);
            color: white;
        }
        
        .search-input::placeholder {
            color: rgba(255,255,255,.6);
        }
        
        .input-group .btn {
            border-radius: 0 12px 12px 0;
            border-left: none;
        }
        
        .nav-tabs {
            border-bottom: 2px solid rgba(255,255,255,.2);
            margin-bottom: 1.5rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: rgba(255,255,255,.7);
            font-weight: 600;
            padding: 0.8rem 1.2rem;
            border-radius: 0;
            transition: all 0.3s ease;
            background: transparent;
            font-size: 0.9rem;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--accent);
            border-bottom: 2px solid var(--accent);
            background: transparent;
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--accent);
            border-color: transparent;
        }
        
        .content-card {
            background: rgba(255,255,255,.14);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,.35);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .content-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0,0,255,.45);
        }
        
        .content-thumbnail {
            position: relative;
            height: 160px;
            overflow: hidden;
        }
        
        .content-thumbnail img,
        .content-thumbnail video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3rem;
            color: white;
            text-shadow: 0 2px 10px rgba(0,0,0,.5);
            opacity: 0.9;
        }
        
        .content-info {
            padding: 1rem;
        }
        
        .content-title {
            font-weight: 600;
            margin-bottom: 0.3rem;
            color: #f8fafc;
            font-size: 0.95rem;
        }
        
        .content-meta {
            color: rgba(255,255,255,.7);
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        
        .card-desc {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
        }
        
        .interaction-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 6px;
        }
        
        .text-secondary {
            color: rgba(255,255,255,.75)!important;
        }
        
                                   .comment-section {
              background: rgba(255,255,255,.05) !important;
              border-radius: 12px !important;
              padding: 15px 25px;
              margin: 15px 25px 0 25px;
              border: 1px solid rgba(255,255,255,.1) !important;
              min-height: 180px;
              max-width: calc(100% - 50px);
          }
        
                 .comment-input {
             background: rgba(255,255,255,0.05);
             border: 1px solid rgba(255,255,255,0.2);
             border-radius: 8px;
             color: #fff;
             padding: 10px 14px;
             width: 100%;
             margin-bottom: 15px;
             font-size: 0.9rem;
             resize: vertical;
             min-height: 50px;
             max-width: 100%;
             box-sizing: border-box;
         }
        
        .comment-input::placeholder {
            color: rgba(255,255,255,0.6);
        }
        
        .comment-input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 2px rgba(34,211,238,0.2);
        }
        
        .comment-item {
            background: rgba(255,255,255,0.02);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .comment-author {
            font-weight: 600;
            color: #fff;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }
        
        .comment-text {
            color: #e0e0e0;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 6px;
        }
        
        .comment-time {
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
            font-style: italic;
        }
        
        .comment-item .btn-outline-secondary {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .comment-item .btn-outline-secondary:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.3);
            color: #fff;
            transform: translateY(-1px);
        }
        
        .comment-item .btn-outline-secondary.liked {
            background: #dc3545;
            border-color: #dc3545;
            color: #fff;
            box-shadow: 0 2px 8px rgba(220,53,69,0.3);
        }
        
        .btn-outline-light {
            background: transparent !important;
            border: 0 !important;
            color: rgba(255,255,255,0.8) !important;
            transition: all 0.3s ease;
        }
        
        .btn-outline-light:hover {
            background: rgba(255,255,255,0.1) !important;
            color: white !important;
            transform: translateY(-1px);
        }
        
        .btn-outline-light:focus {
            box-shadow: none !important;
        }
        
        .btn-success {
            background: transparent !important;
            border: 0 !important;
            color: rgba(255,255,255,0.9) !important;
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            background: rgba(255,255,255,0.1) !important;
            color: white !important;
            transform: translateY(-1px);
        }
        
        .btn-success:focus {
            box-shadow: none !important;
        }
        
        .modal-content video,
        .modal-content img,
        .modal-content audio {
            object-fit: contain !important;
            max-width: 100%;
            max-height: 70vh;
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        
        .media-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .media-container video,
        .media-container img,
        .media-container audio {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .reply-box {
            margin-top: 15px;
        }
        
        .reply-box .d-flex {
            align-items: stretch !important;
            gap: 10px;
        }
        
        .reply-box textarea {
            flex: 1;
            min-height: 60px;
            resize: vertical;
            margin: 0;
        }
        
        .reply-box .btn {
            align-self: stretch;
            height: auto;
            white-space: nowrap;
            margin: 0;
        }
        
        .read-more-btn {
            background: none !important;
            border: none !important;
            color: #065fd4 !important;
            text-decoration: underline;
            cursor: pointer;
            padding: 0;
            margin: 0;
            font-size: inherit;
            font-weight: inherit;
            transition: color 0.2s ease;
        }
        
        .read-more-btn:hover {
            color: #1a73e8 !important;
            text-decoration: none;
        }
        
        .read-more-btn:focus {
            outline: none;
            box-shadow: none;
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
            color: var(--accent) !important;
        }
        
        @media (max-width: 992px) {
            .modal-dialog { max-width: 92vw; }
        }
        @media (max-width: 768px) {
            .content-info { padding: 1rem; }
            .interaction-buttons { flex-direction: column; }
            .btn-sm { width: 100%; }
            .media-container { aspect-ratio: 16/9; max-height: 45vh; }
            .modal-body { max-height: 65vh; }
        }
        
                 .modal-content {
             background: #0f0f0f !important;
             border: none !important;
             border-radius: 0 !important;
             box-shadow: 0 0 30px rgba(255,255,255,0.15), 0 0 60px rgba(255,255,255,0.1) !important;
             max-height: 80vh;
             overflow: hidden;
         }
                 .modal-header {
             border: none !important;
             padding: 15px 25px;
             background: #0f0f0f !important;
             border-radius: 0 !important;
             position: relative;
         }
        .modal-title {
            font-weight: 600;
            font-size: 1.2rem;
            color: #fff;
            margin: 0;
            line-height: 1.4;
        }
                 .modal-body {
             padding: 0;
             max-height: 70vh;
             overflow-y: auto;
             background: #0f0f0f !important;
         }
                 .modal-footer {
             position: sticky;
             bottom: 0;
             z-index: 2;
             border: none !important;
             border-top: 1px solid #272727 !important;
             padding: 15px 25px;
             background: #0f0f0f !important;
             border-radius: 0 !important;
         }
        
                                   .content-description {
              background: #0f0f0f !important;
              border: none !important;
              border-radius: 0 !important;
              padding: 12px 25px 12px 25px;
              margin: 0 25px 0 25px;
              max-width: calc(100% - 50px);
          }
        .content-description + .content-description {
            padding-top: 0;
        }
        .content-description p {
            color: #ddd;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
        }
        .content-description .d-flex.gap-4 span {
            font-size: 0.95rem;
        }
                 .copy-link-btn {
             background: transparent;
             border: 2px solid rgba(255,255,255,.3);
             border-radius: 20px;
             color: #22d3ee;
             cursor: pointer;
             font-size: 0.85rem;
             padding: 6px 12px;
             margin-left: 10px;
             transition: all 0.2s ease;
             display: inline-block;
             vertical-align: middle;
         }
         .copy-link-btn:hover { 
             background: rgba(34, 211, 238, 0.1);
             border-color: #22d3ee;
             transform: translateY(-2px);
             box-shadow: 0 4px 12px rgba(0,0,0,0.3);
         }
        .btn-close-white {
            filter: invert(1) brightness(200%);
            opacity: 0.8;
            transition: all 0.3s ease;
            position: absolute;
            right: 24px;
            top: 16px;
        }
        .btn-close-white:hover {
            opacity: 1;
            transform: scale(1.1);
        }
        
                 .media-container {
             background: #000 !important;
             border-radius: 0 !important;
             padding: 0;
             border: none !important;
             position: relative;
             overflow: hidden;
             margin-bottom: 0;
             width: 100%;
             aspect-ratio: 16/9;
             max-height: 60vh;
         }
        .media-container video,
        .media-container audio,
        .media-container img {
            border-radius: 0;
            box-shadow: none;
            max-height: none;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .modal-footer .btn {
            background: transparent !important;
            border: none !important;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 4px 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: none !important;
            margin: 0;
        }
        
        .modal-footer .btn-outline-primary {
            color: #22d3ee !important;
        }
        .modal-footer .btn-outline-primary:hover {
            color: #22d3ee !important;
            background: transparent !important;
            transform: none;
        }
        
        .modal-footer .btn-outline-info {
            color: #22d3ee !important;
        }
        .modal-footer .btn-outline-info:hover {
            color: #22d3ee !important;
            background: transparent !important;
            transform: none;
        }
        
        .modal-footer .btn-outline-warning {
            color: #22d3ee !important;
        }
        .modal-footer .btn-outline-warning:hover {
            color: #22d3ee !important;
            background: transparent !important;
            transform: none;
        }
        
        .modal-footer .btn-outline-success {
            color: #22d3ee !important;
        }
        .modal-footer .btn-outline-success:hover {
            color: #22d3ee !important;
            background: transparent !important;
            transform: none;
        }
        
        .modal-dialog {
            max-width: 70vw;
            margin: 15px auto;
        }
        @media (min-width: 768px) {
            .modal-dialog { max-width: 65vw; }
        }
        @media (min-width: 1200px) {
            .modal-dialog { max-width: 60vw; }
        }
        @media (min-width: 1400px) {
            .modal-dialog { max-width: 55vw; }
        }
        
        .modal-dialog.widescreen {
            max-width: 90vw !important;
        }
        .modal-dialog.widescreen .media-container {
            max-height: 80vh;
        }
        
        .copy-link-btn {
            background: transparent;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 20px;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            color: #22d3ee;
            text-decoration: none;
            display: inline-block;
            margin-left: 1rem;
        }
        
                 .copy-link-btn:hover {
             transform: translateY(-2px);
             box-shadow: 0 4px 12px rgba(0,0,0,0.3);
             border-color: #22d3ee;
             background: rgba(34, 211, 238, 0.1);
             color: #22d3ee;
             text-decoration: none;
         }
         
         
         
         /* Comment section layout improvements */
         .comment-section .d-flex.gap-2 {
             max-width: 100%;
             flex-wrap: wrap;
         }
         
         .comment-section .flex-grow-1 {
             min-width: 200px;
             max-width: calc(100% - 100px);
         }
         
         .comment-section .btn-primary {
             min-width: 90px;
             max-width: 120px;
             flex-shrink: 0;
         }
         
         /* Description section improvements */
         .content-description p {
             display: flex;
             align-items: center;
             gap: 10px;
             flex-wrap: wrap;
         }
         
         .content-description .description-text {
             flex: 1;
             min-width: 200px;
         }
         
         .content-description .copy-link-btn {
             flex-shrink: 0;
             margin-left: auto;
         }
         
         /* Force modal styling to override Bootstrap defaults */
         .modal {
             background: rgba(0,0,0,0.8) !important;
         }
         
         .modal-backdrop {
             background: rgba(0,0,0,0.8) !important;
             box-shadow: inset 0 0 50px rgba(255,255,255,0.05) !important;
         }
         
         /* Ensure all modal elements have proper backgrounds */
         .modal * {
             box-sizing: border-box;
         }
         
         /* Fix any potential z-index issues */
         .modal-dialog {
             z-index: 1055 !important;
         }
         
         .modal-backdrop {
             z-index: 1054 !important;
         }
        
        /* Newsletter input styling */
        #newsletterEmail::placeholder {
            color: #e2e8f0 !important;
            opacity: 1;
        }
        
        #newsletterEmail {
            color: #ffffff !important;
        }
        
        #newsletterEmail:focus {
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
    </style>
</head>
<body>
    <!-- Toolbar: tabs + search + user menu -->
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <ul class="nav nav-tabs" id="contentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="featured-tab" data-bs-toggle="tab" data-bs-target="#featured" type="button" role="tab">
                        <i class="bi bi-star-fill text-warning me-2"></i>Featured
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="trending-tab" data-bs-toggle="tab" data-bs-target="#trending" type="button" role="tab">
                        <i class="bi bi-fire text-danger me-2"></i>Trending
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent" type="button" role="tab">
                        <i class="bi bi-clock text-info me-2"></i>Recent
                    </button>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <div class="input-group">
                    <input type="text" class="form-control search-input" id="searchInput" placeholder="Search content...">
                    <button class="btn btn-primary" type="button" onclick="performSearch()">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2"></i><?= htmlspecialchars($_SESSION['viewer_name'] ?? 'User') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/kabaka/public/creator_dashboard/login.php"><i class="bi bi-person-plus me-2"></i>Become Creator</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/kabaka/public/api/auth.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Padded content container -->
    <div class="container px-3 px-md-4 px-lg-5">
        <!-- Tab Content -->
        <div class="tab-content" id="contentTabContent">
            <!-- Featured Content -->
            <div class="tab-pane fade show active" id="featured" role="tabpanel">
                <div class="row g-3" id="featuredContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>

            <!-- Trending Content -->
            <div class="tab-pane fade" id="trending" role="tabpanel">
                <div class="row g-3" id="trendingContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>

            <!-- Recent Content -->
            <div class="tab-pane fade" id="recent" role="tabpanel">
                <div class="row g-3" id="recentContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="text-center py-5 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Footer - Exact same as index.php -->
    <footer class="py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <!-- Brand & Description -->
                <div class="col-lg-4 col-md-6">
                    <div class="mb-4">
                        <h5 class="brand mb-3">
                            <a href="/kabaka/public/viewer_dashboard/dashboard.php" class="text-white text-decoration-none">
                                <i class="bi bi-play-circle-fill me-2"></i>Kabaka
                            </a>
                        </h5>
                        <p class="text-secondary small mb-3">Discover and share amazing content with the world. Connect with creators, explore trending videos, and be part of a vibrant community.</p>
                        <div class="d-flex gap-3 social-links">
                            <a href="https://twitter.com" target="_blank" class="text-secondary text-decoration-none" title="Follow us on Twitter">
                                <i class="bi bi-twitter fs-5"></i>
                            </a>
                            <a href="https://instagram.com" target="_blank" class="text-secondary text-decoration-none" title="Follow us on Instagram">
                                <i class="bi bi-instagram fs-5"></i>
                            </a>
                            <a href="https://youtube.com" target="_blank" class="text-secondary text-decoration-none" title="Follow us on YouTube">
                                <i class="bi bi-youtube fs-5"></i>
                            </a>
                            <a href="https://tiktok.com" target="_blank" class="text-secondary text-decoration-none" title="Follow us on TikTok">
                                <i class="bi bi-tiktok fs-5"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white mb-3 fw-semibold">Quick Links</h6>
                                         <ul class="list-unstyled">
                         <li class="mb-2">
                             <a href="#" class="text-secondary text-decoration-none small" onclick="switchToTab('featured'); return false;">
                                 <i class="bi bi-play-circle me-1"></i>Featured Content
                             </a>
                         </li>
                         <li class="mb-2">
                             <a href="#" class="text-secondary text-decoration-none small" onclick="switchToTab('trending'); return false;">
                                 <i class="bi bi-fire me-1"></i>Trending Now
                             </a>
                         </li>
                         <li class="mb-2">
                             <a href="#" class="text-secondary text-decoration-none small" onclick="switchToTab('recent'); return false;">
                                 <i class="bi bi-clock me-1"></i>Recent Content
                             </a>
                         </li>
                         <li class="mb-2">
                             <a href="/kabaka/public/creator_dashboard/login.php" class="text-secondary text-decoration-none small">
                                 <i class="bi bi-person-plus me-1"></i>Become Creator
                             </a>
                         </li>
                     </ul>
                </div>
                
                <!-- Support -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white mb-3 fw-semibold">Support</h6>
                                        <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="/kabaka/public/viewer_dashboard/support.php" class="text-secondary text-decoration-none small">
                                <i class="bi bi-question-circle me-1"></i>Help Center
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="/kabaka/public/viewer_dashboard/support.php#privacy" class="text-secondary text-decoration-none small">
                                <i class="bi bi-shield-check me-1"></i>Privacy Policy
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="/kabaka/public/viewer_dashboard/support.php#terms" class="text-secondary text-decoration-none small">
                                <i class="bi bi-file-text me-1"></i>Terms of Service
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="/kabaka/public/viewer_dashboard/support.php#delete" class="text-secondary text-decoration-none small">
                                <i class="bi bi-trash me-1"></i>Delete Your Account
                            </a>
                        </li>
                     </ul>
                </div>
                
                <!-- Newsletter -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white mb-3 fw-semibold">Stay Updated</h6>
                    <p class="text-secondary small mb-3">Get the latest content and creator updates.</p>
                    <div class="input-group input-group-sm">
                        <input id="newsletterEmail" type="email" class="form-control form-control-sm bg-transparent border-secondary text-white" placeholder="Your email" style="font-size: 0.8rem; color: #ffffff !important;" onfocus="this.style.color='#ffffff'" onblur="this.style.color='#ffffff'">
                        <button id="newsletterBtn" class="btn btn-primary btn-sm" type="button">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                    <div id="newsletterMsg" class="small mt-2"></div>
                </div>
            </div>
            
            <!-- Bottom Footer -->
            <div class="row mt-4 pt-4" style="border-top: 1px solid rgba(255,255,255,.1)">
                <div class="col-md-6">
                    <p class="text-secondary small mb-0">
                        <i class="bi bi-heart-fill text-danger me-1"></i>
                        Made with love for content creators worldwide
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-secondary small mb-0">
                        <i class="bi bi-c-circle me-1"></i>
                        © <?= date('Y') ?> Kabaka. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Content Modal - YouTube-like -->
    <div class="modal fade" id="contentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contentModalTitle">Content Title</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="contentModalBody"></div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex flex-wrap">
                        <button id="modalLikeButton" class="btn btn-outline-primary btn-sm" onclick="likeFromModal(currentContentId)" aria-label="Like content">
                            <i class="bi bi-heart me-1" aria-hidden="true"></i>Like
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="toggleComments()" aria-label="Show comments">
                            <i class="bi bi-chat me-1" aria-hidden="true"></i>Comments
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="toggleShare()" aria-label="Share content">
                            <i class="bi bi-share me-1" aria-hidden="true"></i>Share
                        </button>
                        <button id="openReportModalBtn" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal" aria-label="Report content">
                            <i class="bi bi-flag me-1" aria-hidden="true"></i>Report
                        </button>
                        <a id="downloadLink" class="btn btn-outline-success btn-sm" download title="Download content">
                            <i class="bi bi-download me-1" aria-hidden="true"></i>Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 420px;border-radius: 16px;">
            <div class="modal-content" style="background: linear-gradient(180deg, rgba(90,14,14,0.98), rgba(40,7,7,0.98)); backdrop-filter: blur(12px); border: 1px solid rgba(255,0,0,.25); border-radius: 16px; overflow: hidden; box-shadow: 0 12px 36px rgba(0,0,0,.6);">
                <div class="modal-header border-danger px-3 py-2">
                    <h5 class="modal-title text-white"><i class="bi bi-flag me-2"></i>Report Content</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label text-white mb-1">Reason</label>
                        <input id="reportReasonModal" class="form-control form-control-sm bg-transparent text-white border-danger" placeholder="e.g., Spam, Harassment, Violence, Copyright" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-white mb-1">Optional note</label>
                        <input id="reportNoteModal" class="form-control form-control-sm bg-transparent text-white border-danger" placeholder="Add a short note (optional)" />
                    </div>
                </div>
                <div class="modal-footer border-danger gap-2 px-3 py-2">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button id="modalReportButton" type="button" class="btn btn-danger btn-sm" onclick="reportContentFromModal()">
                        <i class="bi bi-flag me-1"></i>Submit Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/kabaka/public/blockchain_download/payDownload.js"></script>
    <script>
        let currentTab = 'featured';
        
        // Newsletter subscribe
        (function initNewsletter(){
            const btn = document.getElementById('newsletterBtn');
            const email = document.getElementById('newsletterEmail');
            const msg = document.getElementById('newsletterMsg');
            if (!btn || !email) return;
            btn.addEventListener('click', async ()=>{
                msg.textContent = '';
                msg.className = 'small mt-2';
                const v = (email.value||'').trim();
                if (!v || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) { msg.textContent='Please enter a valid email.'; msg.classList.add('text-danger'); return; }
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                try{
                    const r = await fetch('/kabaka/public/api/newsletter.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({email:v}) });
                    const ct = r.headers.get('content-type')||''; let data=null; if(ct.includes('application/json')){ try{ data = await r.json(); }catch(_){} }
                    if (r.ok && (data?.ok || data?.duplicate)) { msg.textContent = data?.duplicate ? 'You are already subscribed.' : 'Thanks for subscribing!'; msg.classList.add('text-success'); email.value=''; }
                    else { msg.textContent = data?.error || 'Subscription failed. Please try again.'; msg.classList.add('text-danger'); }
                }catch(_){ msg.textContent='Network error. Please try again.'; msg.classList.add('text-danger'); }
                finally{ btn.disabled=false; btn.innerHTML = '<i class="bi bi-arrow-right"></i>'; }
            });
        })();

        // Load content on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadContent('featured');
            
            // Add event listeners for tab buttons
            document.getElementById('featured-tab').addEventListener('click', () => loadContent('featured'));
            document.getElementById('trending-tab').addEventListener('click', () => loadContent('trending'));
            document.getElementById('recent-tab').addEventListener('click', () => loadContent('recent'));
            
            // Initialize video functionality
            setupModalVideo();
            initVideoHover();
        });
        
        // Load content function
        async function loadContent(tab) {
            currentTab = tab;
            try {
                // Distinct sorting per tab
                let sort = 'created_desc';
                if (tab === 'featured') sort = 'likes_desc';
                if (tab === 'trending') sort = 'views_desc';
                if (tab === 'recent') sort = 'created_desc';
                const params = new URLSearchParams({ sort, page: '1', limit: '20' });
                const url = `/kabaka/public/api/content.php?${params.toString()}`;
                    const response = await fetch(url);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                const items = data?.data?.content || data?.items || [];
                if (items.length > 0) {
                    renderContent(items, tab);
                } else {
                    renderEmptyState(tab);
                }
            } catch (error) {
                console.error('Error loading content:', error);
                renderError(tab);
            }
        }
        
        // Render content
        async function renderContent(items, tab) {
            const container = document.getElementById(`${tab}Content`);
            container.innerHTML = items.map(item => createContentCard(item, tab)).join('');
            initFollowButtons();
            initVideoHover();
            // Initialize liked heart visuals on cards
            try {
                const ids = Array.from(container.querySelectorAll('[data-content-id]')).map(el => parseInt(el.getAttribute('data-content-id')||'0',10)).filter(Boolean);
                for (const id of ids) {
                    const chk = await fetch(`/kabaka/public/api/engagement.php?content_id=${id}&check_like=1`, { credentials: 'same-origin' });
                    const j = await chk.json();
                    setCardHeartVisual(id, !!(j && j.liked));
                }
            } catch (_) {}

        }
        
        // Create content card HTML (match index.php)
        function createContentCard(item, tab) {
            const isVideo = item.file_type && item.file_type.startsWith('video/');
            const isImage = item.file_type && item.file_type.startsWith('image/');
            const category = item.category || 'General';
            const creatorName = item.creator_name || 'Unknown';
            const shortDesc = (item.description || '').length > 80
                ? `${item.description.substring(0, 80)}...`
                : (item.description || '');
            
            // Use the same look for all tabs (Featured style)
            let borderClass = '';
            let badgeClass = 'bg-primary';
            let viewBtnClass = 'btn btn-primary btn-sm';
            let followBtnClass = 'btn btn-outline-light btn-sm';
            
            const creatorId = item.user_id || item.creator_id || item.owner_id || 0;
            
            // Thumbnail block like index.php
            let thumbHtml = '';
            if (isVideo) {
                thumbHtml = `
                    <div class="content-thumbnail position-relative">
                        <video class="w-100 h-100" style="object-fit: cover; border-radius: 12px;" loop preload="metadata" muted>
                            <source src="${item.media_url || ('/kabaka/public/uploads/' + (item.file_path || ''))}" type="${item.file_type}">
                        </video>
                        <div class="position-absolute top-50 start-50 translate-middle play-overlay">
                            <i class="bi bi-play-circle-fill display-4 text-white"></i>
                        </div>
                    </div>
                `;
            } else if (isImage) {
                thumbHtml = `
                    <div class="content-thumbnail position-relative">
                        <img src="${item.media_url || ('/kabaka/public/uploads/' + (item.file_path || ''))}" 
                              class="w-100 h-100" 
                              style="object-fit: cover; border-radius: 12px;" 
                              alt="${escapeHtml(item.title || '')}"
                              onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                         <div class="position-absolute top-50 start-50 translate-middle d-none align-items-center justify-content-center bg-secondary w-100 h-100" style="border-radius: 12px;">
                             <i class="bi bi-image display-4"></i>
                         </div>
                     </div>
                 `;
            } else {
                thumbHtml = `
                    <div class="content-thumbnail d-flex align-items-center justify-content-center bg-secondary">
                        <i class="bi bi-music-note-beamed display-4"></i>
                    </div>
                `;
            }
            
            return `
                <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
                    <div class="content-card glass p-3 h-100${borderClass}" data-content-id="${item.id}">
                        <div class="position-relative mb-3">
                            ${thumbHtml}
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge ${badgeClass}">${escapeHtml(category)}</span>
                            </div>
                        </div>
                        <h5 class="h6 mb-2">${escapeHtml(item.title || 'Untitled')}</h5>
                        <p class="text-secondary small mb-2 card-desc">${escapeHtml(shortDesc)}</p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small text-secondary">
                                <i class="bi bi-eye me-1"></i>${item.view_count || 0}
                                <i class="bi bi-heart ms-3 me-1"></i><span class="like-count">${item.like_count || 0}</span>
                            </div>
                            <button class="${viewBtnClass}" onclick="showContentModal(${item.id})">
                                <i class="bi bi-play me-1"></i>View
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-secondary">by ${escapeHtml(creatorName)}</small>
                            <button class="${followBtnClass}" data-creator-id="${creatorId}" onclick="toggleFollow(${creatorId}, this)" aria-label="Follow creator">
                                <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Follow
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Render empty state
        function renderEmptyState(tab) {
            const container = document.getElementById(`${tab}Content`);
            
            let icon, title, description, buttonText;
            
            switch(tab) {
                case 'featured':
                    icon = 'bi-inbox';
                    title = 'No content available yet';
                    description = 'Be the first to share amazing content!';
                    buttonText = 'Upload Content';
                    break;
                case 'trending':
                    icon = 'bi-fire';
                    title = 'No trending content yet';
                    description = 'Content will appear here when it gets popular!';
                    buttonText = null;
                    break;
                case 'recent':
                    icon = 'bi-clock';
                    title = 'No recent content yet';
                    description = 'Latest content will appear here!';
                    buttonText = null;
                    break;
                default:
                    icon = 'bi-inbox';
                    title = 'No content available';
                    description = 'Check back later for new content!';
                    buttonText = null;
            }
            
            let buttonHtml = '';
            if (buttonText) {
                buttonHtml = `
                    <a href="/kabaka/public/creator_dashboard/login.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload me-1"></i>${buttonText}
                    </a>
                `;
            }
            
            container.innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="bi ${icon} display-4 text-secondary mb-2"></i>
                    <h5 class="text-secondary">${title}</h5>
                    <p class="text-secondary small">${description}</p>
                    ${buttonHtml}
                </div>
            `;
        }
        
        // Render error
        function renderError(tab) {
            const container = document.getElementById(`${tab}Content`);
            container.innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="bi bi-exclamation-triangle display-4 text-danger"></i>
                    <h6 class="mt-2 text-danger">Error loading content</h6>
                    <p class="text-secondary small">Please try again later.</p>
                </div>
            `;
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        // Perform search
        async function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            if (searchTerm.length === 0) {
                loadContent(currentTab);
                return;
            }
            
            try {
                const response = await fetch(`/kabaka/public/api/content.php?search=1&q=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();
                
                if (data.items && data.items.length > 0) {
                    renderContent(data.items, currentTab);
                } else {
                    renderSearchEmptyState(searchTerm);
                }
            } catch (error) {
                renderError(currentTab);
            }
        }
        
        // Render search empty state
        function renderSearchEmptyState(searchTerm) {
            const container = document.getElementById(`${currentTab}Content`);
            container.innerHTML = `
                <div class="col-12 text-center py-4">
                    <div class="glass p-3" style="max-width: 400px; margin: 0 auto;">
                        <i class="bi bi-search display-4 text-secondary mb-3"></i>
                        <h5 class="text-white mb-2">No Results Found</h5>
                        <p class="text-secondary small mb-3">No content found for "<strong>${escapeHtml(searchTerm)}</strong>". Try different keywords.</p>
                        <button class="btn btn-primary btn-sm" onclick="clearSearch()">
                            <i class="bi bi-arrow-left me-1"></i>Clear Search
                        </button>
                    </div>
                </div>
            `;
        }
        
        // Clear search
        function clearSearch() {
            document.getElementById('searchInput').value = '';
            loadContent(currentTab);
        }
        
        // Utility functions
        function formatFileSize(bytes) {
            if (!bytes) return 'Unknown';
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            return Math.round(bytes / Math.pow(1024, i)) + ' ' + sizes[i];
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Comment functionality
        let currentContentId = null;
        
        // Show content modal with comments
        async function showContentModal(contentId) {
            currentContentId = contentId;
            try { window.currentContentId = contentId; } catch (_) {}
            
            try {
                const resp = await fetch(`/kabaka/public/api/content.php?id=${contentId}`);
                const raw = await resp.text();
                if (!resp.ok) {
                    console.error('Content API error', resp.status, raw);
                    alert(`Error loading content: ${resp.status}`);
                    return;
                }
                let data;
                try { data = JSON.parse(raw); } catch (e) {
                    console.error('Invalid JSON from content API', raw);
                    alert('Error loading content: invalid server response');
                    return;
                }
                const item = (data && (data.data || data.content || data.item)) || null;
                if (!item) {
                    console.error('Missing content payload', data);
                    alert('Error loading content: not found');
                    return;
                }
                
                const modalEl = document.getElementById('contentModal');
                const modalTitleEl = document.getElementById('contentModalTitle');
                const modalBodyEl = document.getElementById('contentModalBody');
                const modal = new bootstrap.Modal(modalEl);
                
                modalTitleEl.textContent = item.title || 'Content';
                
                // Build media section
                const body = document.createElement('div');
                body.className = 'media-container';
                if (item.file_type && item.file_type.startsWith('video/')) {
                    const video = document.createElement('video');
                    video.controls = true;
                    video.autoplay = true;
                    video.muted = false;
                    video.className = 'w-100';
                    const src = item.media_url || `/kabaka/public/uploads/${item.file_path || ''}`;
                    video.innerHTML = `<source src="${src}" type="${item.file_type}">`;
                    body.appendChild(video);
                } else if (item.file_type && item.file_type.startsWith('audio/')) {
                    const audio = document.createElement('audio');
                    audio.controls = true;
                    audio.autoplay = true;
                    audio.muted = false;
                    audio.className = 'w-100';
                    const src = item.media_url || `/kabaka/public/uploads/${item.file_path || ''}`;
                    audio.innerHTML = `<source src="${src}" type="${item.file_type}">`;
                    body.appendChild(audio);
                } else if (item.file_type && item.file_type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = item.media_url || `/kabaka/public/uploads/${item.file_path || ''}`;
                    img.className = 'w-100';
                    img.style.objectFit = 'contain';
                    body.appendChild(img);
                } else {
                    const fallback = document.createElement('div');
                    fallback.className = 'text-center p-5 text-secondary';
                    fallback.innerHTML = '<i class="bi bi-file-earmark display-4 d-block mb-3"></i>Unsupported file type';
                    body.appendChild(fallback);
                }
                
                // Description and stats block
                const descWrapper = document.createElement('div');
                descWrapper.className = 'content-description';
                descWrapper.setAttribute('data-full-description', item.description || '');
                const isLong = (item.description || '').length > 100;
                const displayText = isLong ? (item.description || '').substring(0,100) + '...' : (item.description || 'No description');
                descWrapper.innerHTML = `
                    <p>
                        <span class="description-text ${isLong ? 'collapsed' : ''}" id="descriptionText">${displayText}</span>
                        ${isLong ? `<button class="read-more-btn" onclick="toggleDescription()">Read More</button>` : ''}
                        <button class="copy-link-btn" onclick="copyContentLink('${(item.title || '').replace(/'/g, "\'")}', ${item.id})">
                            <i class="bi bi-link-45deg me-1"></i>Copy Link
                        </button>
                    </p>
                `;
                
                const statsDiv = document.createElement('div');
                statsDiv.className = 'content-description';
                statsDiv.style.paddingTop = '0';
                statsDiv.innerHTML = `
                    <div class="d-flex gap-4 text-secondary">
                        <span><i class="bi bi-eye me-1"></i>${item.view_count || 0} views</span>
                        <span id="modalLikeWrapper"><i id="modalLikeIcon" class="bi bi-heart me-1"></i><span id="modalLikeCount">${item.like_count || 0}</span> likes</span>
                    </div>
                `;
                
                // Comment section
                const commentSection = document.createElement('div');
                commentSection.className = 'comment-section';
                commentSection.innerHTML = `
                    <h6 class="text-light mb-4">
                        <i class="bi bi-chat-dots me-2"></i>Comments
                        <span class="text-secondary ms-2" id="commentCount">(0 comments)</span>
                    </h6>
                    <div class="mb-4">
                        <div class="d-flex gap-2 align-items-stretch flex-wrap">
                            <div class="flex-grow-1" style="min-width: 220px;">
                                <textarea class="comment-input" placeholder="Share your thoughts on this content..." rows="3" style="min-height:64px;height:64px;"></textarea>
                            </div>
                            <button class="btn btn-primary h-100" onclick="addComment()" style="min-width: 90px;">
                                <i class="bi bi-send me-1"></i>Post
                            </button>
                        </div>
                    </div>
                    <div id="commentsList" class="mt-4">
                        <div class="text-center text-secondary py-3">
                            <i class="bi bi-chat-dots me-2"></i>Loading comments...
                        </div>
                    </div>
                `;
                
                modalBodyEl.innerHTML = '';
                modalBodyEl.appendChild(body);
                modalBodyEl.appendChild(descWrapper);
                modalBodyEl.appendChild(statsDiv);
                modalBodyEl.appendChild(commentSection);
                
                const downloadLink = document.getElementById('downloadLink');
                if (downloadLink) {
                    downloadLink.href = item.media_url || `/kabaka/public/uploads/${item.file_path || ''}`;
                    downloadLink.download = item.title || 'download';
                }
                
                // Record view
                fetch('/kabaka/public/api/engagement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ content_id: contentId, type: 'view' })
                }).then(()=>{}).catch(() => {});
                
                // Initialize like state for current user
                try {
                    const chk = await fetch(`/kabaka/public/api/engagement.php?content_id=${contentId}&check_like=1`, { credentials: 'same-origin' });
                    const j = await chk.json();
                    const likeIcon = document.getElementById('modalLikeIcon');
                    const likeWrap = document.getElementById('modalLikeWrapper');
                    if (j && j.liked) { likeIcon.classList.remove('bi-heart'); likeIcon.classList.add('bi-heart-fill','text-danger'); if (likeWrap) likeWrap.classList.add('text-danger'); setCardHeartVisual(contentId, true); }
                    else { likeIcon.classList.remove('bi-heart-fill','text-danger'); likeIcon.classList.add('bi-heart'); if (likeWrap) likeWrap.classList.remove('text-danger'); setCardHeartVisual(contentId, false); }
                } catch (_) {}
                
                loadComments(contentId);
                modal.show();
            } catch (error) {
                console.error('Error loading content:', error);
                alert('Error loading content: ' + (error?.message || 'unknown'));
            }
        }

        function toggleWidescreen() {
            const dialog = document.querySelector('#contentModal .modal-dialog');
            if (dialog) dialog.classList.toggle('widescreen');
        }
        
        async function togglePiP() {
            const video = document.querySelector('#contentModalBody .media-container video');
            if (!video || !document.pictureInPictureEnabled) return;
            try {
                if (document.pictureInPictureElement) {
                    await document.exitPictureInPicture();
                } else {
                    await video.requestPictureInPicture();
                }
            } catch (_) {}
        }
        
        function toggleComments() {
            const section = document.querySelector('#contentModalBody .comment-section');
            if (!section) return;
            section.style.display = 'block';
            setTimeout(() => section.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
        }
        
                  function toggleShare() {
               // Scroll to the copy link button in the description section
               const copyLinkBtn = document.querySelector('#contentModalBody .copy-link-btn');
               if (copyLinkBtn) {
                   copyLinkBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
               }
           }
        
        // Like content from modal and update counts
        async function likeFromModal(id) {
            try {
                const likeBtn = document.getElementById('modalLikeButton');
                const original = likeBtn ? likeBtn.innerHTML : '';
                if (likeBtn) { likeBtn.disabled = true; likeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Working…'; }
                // Check current state first
                let wasLiked = false;
                try {
                    const chk = await fetch(`/kabaka/public/api/engagement.php?content_id=${id || currentContentId}&check_like=1`, { credentials: 'same-origin' });
                    const j = await chk.json();
                    wasLiked = !!(j && j.liked);
                } catch (_) { /* ignore */ }
                const response = await fetch('/kabaka/public/api/engagement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ content_id: id || currentContentId, type: 'like' })
                });
                const ct = response.headers.get('content-type') || '';
                const raw = await response.text();
                let data = {}; try { data = ct.includes('application/json') && raw.trim() ? JSON.parse(raw) : {}; } catch (_) { data = {}; }
                if (response.status === 401) { alert('Please log in to like content.'); return; }
                if (!response.ok) { alert(`Like failed (${response.status}): ${raw.substring(0,200)}`); return; }
                // Determine liked state; default to toggled value on empty success
                let liked = typeof data.liked === 'boolean' ? data.liked : !wasLiked;
                // Double-check by re-reading if server didn't tell us
                if (typeof data.liked !== 'boolean') {
                    try { const chk2 = await fetch(`/kabaka/public/api/engagement.php?content_id=${id || currentContentId}&check_like=1`, { credentials: 'same-origin' }); const j2 = await chk2.json(); liked = !!(j2 && j2.liked); } catch(_) {}
                }
                const likeCountEl = document.getElementById('modalLikeCount');
                const likeIcon = document.getElementById('modalLikeIcon');
                const likeWrap = document.getElementById('modalLikeWrapper');
                const current = parseInt(likeCountEl.textContent || '0', 10);
                let next = current;
                if (!wasLiked && liked) next = current + 1;
                if (wasLiked && !liked) next = Math.max(0, current - 1);
                likeCountEl.textContent = String(next);
                if (liked) {
                    likeIcon.classList.remove('bi-heart');
                    likeIcon.classList.add('bi-heart-fill', 'text-danger');
                    if (likeWrap) likeWrap.classList.add('text-danger');
                } else {
                    likeIcon.classList.remove('bi-heart-fill', 'text-danger');
                    likeIcon.classList.add('bi-heart');
                    if (likeWrap) likeWrap.classList.remove('text-danger');
                }
                updateContentCardLikes(id || currentContentId, liked);
            } catch (e) {
                console.error('Error liking content', e);
                alert('Error liking content');
            } finally {
                const likeBtn = document.getElementById('modalLikeButton');
                if (likeBtn) { likeBtn.disabled = false; likeBtn.innerHTML = '<i class="bi bi-heart me-1"></i>Like'; }
            }
        }
        
        // Update card like count and icon color in real-time
        function updateContentCardLikes(contentId, isLiked) {
            const cards = document.querySelectorAll(`[data-content-id="${contentId}"]`);
            cards.forEach(card => {
                const likeCountSpan = card.querySelector('.like-count');
                const heartIcon = card.querySelector('.bi-heart, .bi-heart-fill');
                if (likeCountSpan) {
                    const current = parseInt(likeCountSpan.textContent || '0', 10);
                    likeCountSpan.textContent = String(isLiked ? current + 1 : Math.max(0, current - 1));
                }
                if (heartIcon) {
                    if (isLiked) {
                        heartIcon.classList.remove('bi-heart');
                        heartIcon.classList.add('bi-heart-fill', 'text-danger');
                    } else {
                        heartIcon.classList.remove('bi-heart-fill', 'text-danger');
                        heartIcon.classList.add('bi-heart');
                    }
                }
            });
        }

        // Set card heart icon visual only (no count change)
        function setCardHeartVisual(contentId, liked) {
            const cards = document.querySelectorAll(`[data-content-id="${contentId}"]`);
            cards.forEach(card => {
                const heartIcon = card.querySelector('.bi-heart, .bi-heart-fill');
                if (!heartIcon) return;
                if (liked) {
                    heartIcon.classList.remove('bi-heart');
                    heartIcon.classList.add('bi-heart-fill', 'text-danger');
                } else {
                    heartIcon.classList.remove('bi-heart-fill', 'text-danger');
                    heartIcon.classList.add('bi-heart');
                }
            });
        }
        
        // Follow/unfollow toggle
        async function toggleFollow(userId, button) {
            // Derive creator id from button if not passed
            if ((!userId || userId === 0) && button) {
                const attr = button.getAttribute('data-creator-id');
                userId = parseInt(attr || '0', 10) || 0;
            }
            if (!userId) return;
            let original = button ? button.innerHTML : '';
            let prevFollowing = false;
            try {
                if (button) { button.disabled = true; button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Working…'; }
                const response = await fetch('/kabaka/public/api/follow.php?action=toggle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ creator_id: userId, user_id: userId })
                });
                const ct = response.headers.get('content-type') || '';
                const raw = await response.text();
                let data = null;
                try { data = ct.includes('application/json') && raw.trim() ? JSON.parse(raw) : {}; } catch (_) { data = {}; }
                if (response.status === 401) { alert('Please log in to follow creators.'); return; }
                if (!response.ok) { console.error('Follow error HTTP', response.status, raw); alert(`Follow failed (${response.status})`); return; }
                if (data && typeof data.following === 'boolean') {
                    prevFollowing = !data.following; // we were the opposite state before
                    const allButtons = document.querySelectorAll(`button[data-creator-id="${userId}"]`);
                    allButtons.forEach(btn => setFollowButtonState(btn, data.following));
                } else {
                    // Fallback: verify state
                    const r = await fetch(`/kabaka/public/api/follow.php?is_following=1&creator_id=${userId}`, { credentials: 'same-origin' });
                    const j = await r.json();
                    const allButtons = document.querySelectorAll(`button[data-creator-id="${userId}"]`);
                    allButtons.forEach(btn => setFollowButtonState(btn, !!(j && j.following)));
                }
            } catch (e) {
                console.error('Follow error', e);
                alert('Error following creator');
                // revert all buttons to previous state on error
                const allButtons = document.querySelectorAll(`button[data-creator-id="${userId}"]`);
                allButtons.forEach(btn => setFollowButtonState(btn, prevFollowing));
            } finally {
                if (button) { button.disabled = false; }
            }
        }

        function setFollowButtonState(button, following) {
            if (!button) return;
            if (following) {
                button.classList.remove('btn-outline-light');
                button.classList.add('btn-success');
                button.innerHTML = '<i class="bi bi-person-check me-1"></i>Following';
            } else {
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-light');
                button.innerHTML = '<i class="bi bi-person-plus me-1"></i>Follow';
            }
        }

        async function initFollowButtons() {
            const buttons = Array.from(document.querySelectorAll('button[data-creator-id]'));
            if (buttons.length === 0) return;
            const seen = new Map();
            for (const btn of buttons) {
                const id = parseInt(btn.getAttribute('data-creator-id') || '0', 10);
                if (!id || seen.has(id)) continue;
                seen.set(id, true);
                try {
                    const r = await fetch(`/kabaka/public/api/follow.php?is_following=1&creator_id=${id}`, { credentials: 'same-origin' });
                    const j = await r.json();
                    const sameButtons = document.querySelectorAll(`button[data-creator-id="${id}"]`);
                    sameButtons.forEach(b => setFollowButtonState(b, !!(j && j.following)));
                } catch (_) {}
            }
        }
        window.initFollowButtons = initFollowButtons;
        
        // Copy link to clipboard
        function copyLink(contentId) {
            const url = `${window.location.origin}/kabaka/public/?content=${contentId}`;
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard');
            }).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                alert('Link copied to clipboard');
            });
        }

        // Load comments
        function loadComments(contentId) {
            fetch(`/kabaka/public/api/comments.php?content_id=${contentId}`)
                .then(r => r)
                .then(async response => {
                    const commentsList = document.getElementById('commentsList');
                    const commentCount = document.getElementById('commentCount');
                    const ct = response.headers.get('content-type') || '';
                    let data = { comments: [] };
                    try {
                        const raw = await response.text();
                        if (ct.includes('application/json') && raw.trim().length) {
                            data = JSON.parse(raw);
                        }
                    } catch (_) {
                        data = { comments: [] };
                    }
                    
                    const comments = Array.isArray(data.comments) ? data.comments : [];
                    commentCount.textContent = `(${comments.length} comments)`;
                    disableTopLevelInputIfNeeded(data.my_comment_id);
                    
                    if (comments.length === 0) {
                        commentsList.innerHTML = `
                            <div class="text-center text-secondary py-3">
                                <i class="bi bi-chat-dots me-2"></i>No comments yet. Be the first to comment!
                            </div>
                        `;
                        return;
                    }
                    
                    // Build parent->children map
                    const byParent = new Map();
                    comments.forEach(c => {
                        const key = c.parent_id ? String(c.parent_id) : 'root';
                        if (!byParent.has(key)) byParent.set(key, []);
                        byParent.get(key).push(c);
                    });
                    
                    // Render top-level and their replies
                    commentsList.innerHTML = '';
                    (byParent.get('root') || []).forEach(parent => {
                        const node = renderCommentWithReplies(parent, byParent);
                        commentsList.appendChild(node);
                    });
                })
                .catch(error => {
                    console.error('Error loading comments:', error);
                    const commentsList = document.getElementById('commentsList');
                    commentsList.innerHTML = `
                        <div class="text-center text-secondary py-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>Error loading comments
                        </div>
                    `;
                });
        }
        // Ensure global access when called from other handlers
        window.loadComments = loadComments;

        function renderCommentWithReplies(comment, byParent) {
            const container = document.createElement('div');
            container.className = 'comment-item';
            
            const children = byParent.get(String(comment.id)) || [];
            const firstReplier = children[0]?.author_name || null;
            const repliesCount = children.length;
            const hintText = repliesCount === 0
                ? ''
                : (repliesCount === 1
                    ? `${firstReplier ? escapeHtml(firstReplier) : 'Someone'} replied`
                    : `${firstReplier ? escapeHtml(firstReplier) : 'Someone'} and ${repliesCount - 1} others replied`);
            
            const isMine = comment.is_mine == 1 || comment.is_mine === '1';
            
            container.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="comment-author">${escapeHtml(comment.author_name || 'Anonymous')}</div>
                    <small class="comment-time">${escapeHtml(formatTimeAgo(comment.created_at))}</small>
                </div>
                <div class="comment-text">${escapeHtml(comment.text || '')}</div>
                <div class="d-flex gap-2 mt-3 align-items-center">
                    <button class="btn btn-sm btn-outline-secondary" onclick="this.classList.toggle('liked')">
                        <i class="bi bi-heart me-1"></i>Like
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleReplyBox(${comment.id}, this)">
                        <i class="bi bi-reply me-1"></i>Reply
                    </button>
                    ${repliesCount > 0 ? `<a href="#" class="small text-decoration-none" onclick="toggleReplies(${comment.id}, this); return false;">Hide replies</a>` : ''}
                    ${isMine ? `<button class="btn btn-sm btn-outline-danger ms-auto" onclick="deleteComment(${comment.id})"><i class=\"bi bi-trash\"></i> Delete</button>` : ''}
                </div>
                <div class="reply-box mt-3 d-none">
                    <div class="d-flex gap-2">
                        <textarea class="comment-input" placeholder="Write a reply..." rows="2" style="font-size: 0.85rem;"></textarea>
                        <button class="btn btn-primary btn-sm" onclick="submitReply(${comment.id}, this)" style="align-self: flex-end;">
                            <i class="bi bi-send me-1"></i>Reply
                        </button>
                    </div>
                </div>
                <div class="replies mt-2" data-parent-id="${comment.id}"></div>
            `;
            
            // Render children (hidden by default)
            const repliesContainer = container.querySelector('.replies');
            children.forEach(child => {
                const childNode = document.createElement('div');
                childNode.className = 'ms-3 mt-2';
                // include delete if child's is_mine
                const childEl = createCommentElement(child);
                if (child.is_mine == 1 || child.is_mine === '1') {
                    const actions = document.createElement('div');
                    actions.className = 'mt-2';
                    actions.innerHTML = `<button class=\"btn btn-sm btn-outline-danger\" onclick=\"deleteComment(${child.id})\"><i class=\"bi bi-trash\"></i> Delete</button>`;
                    childEl.appendChild(actions);
                }
                childNode.appendChild(childEl);
                repliesContainer.appendChild(childNode);
            });
            return container;
        }

        // After loading comments, disable top-level input if already commented
        function disableTopLevelInputIfNeeded(myCommentId) {
            const input = document.querySelector('#contentModalBody .comment-input');
            const button = document.querySelector('#contentModalBody .btn.btn-primary');
            if (!input || !button) return;
            // Allow multiple top-level comments now
            input.disabled = false;
            button.disabled = false;
        }

        // Delete own comment/reply
        function deleteComment(id) {
            if (!confirm('Delete this comment?')) return;
            fetch(`/kabaka/public/api/comments.php?id=${id}`, { method: 'DELETE' })
                .then(r => r.json())
                .then(data => {
                    if (data && data.ok) {
                        loadComments(currentContentId);
                    } else {
                        alert(data.error || 'Failed to delete');
                    }
                })
                .catch(() => alert('Failed to delete'));
        }
        window.deleteComment = deleteComment;
        
        function toggleReplies(parentId, linkEl) {
            const container = linkEl.closest('.comment-item');
            const replies = container.querySelector('.replies');
            if (!replies) return;
            const isHidden = replies.classList.contains('d-none');
            if (isHidden) {
                replies.classList.remove('d-none');
                linkEl.textContent = 'Hide replies';
            } else {
                replies.classList.add('d-none');
                // Attempt to restore the original hint text
                const count = replies.children.length;
                if (count > 0) {
                    const first = replies.querySelector('.comment-item .comment-author');
                    const name = first ? first.textContent : 'Someone';
                    linkEl.textContent = count === 1 ? `${name} replied` : `${name} and ${count - 1} others replied`;
                }
            }
        }
        window.toggleReplies = toggleReplies;

        function toggleReplyBox(parentId, button) {
            const item = button.closest('.comment-item');
            const box = item.querySelector('.reply-box');
            if (!box) return;
            box.classList.toggle('d-none');
        }

        function submitReply(parentId, button) {
            const item = button.closest('.comment-item');
            const textarea = item.querySelector('.reply-box textarea');
            if (!textarea) return;
            const text = (textarea.value || '').trim();
            if (!text || !currentContentId) return;
            fetch('/kabaka/public/api/comments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ content_id: currentContentId, parent_id: parentId, text })
            })
            .then(async r => {
                const raw = await r.text();
                let data; try { data = JSON.parse(raw); } catch { data = {}; }
                if (r.ok && data && data.ok) {
                    textarea.value = '';
                    loadComments(currentContentId);
                    return;
                }
                if (r.status === 409 && (data.code === 'ALREADY_REPLIED')) {
                    // Disable this reply box
                    textarea.disabled = true;
                    button.disabled = true;
                    alert('You already replied to this comment.');
                    return;
                }
                alert(data.error || 'Failed to post reply');
            })
            .catch(() => alert('Failed to post reply'));
        }
        window.submitReply = submitReply;
        window.toggleReplyBox = toggleReplyBox;

        // Create a single comment DOM node
        function createCommentElement(comment) {
            const div = document.createElement('div');
            div.className = 'comment-item';
            const author = comment.author_name || 'Anonymous';
            const time = comment.created_at ? formatTimeAgo(comment.created_at) : '';
            const text = comment.text || '';
            div.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="comment-author">${escapeHtml(author)}</div>
                    <small class="comment-time">${escapeHtml(time)}</small>
                </div>
                <div class="comment-text">${escapeHtml(text)}</div>
                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-sm btn-outline-secondary" onclick="this.classList.toggle('liked')">
                        <i class="bi bi-heart me-1"></i>Like
                    </button>
                </div>
            `;
            return div;
        }

        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return `${Math.floor(seconds/60)} minutes ago`;
            if (seconds < 86400) return `${Math.floor(seconds/3600)} hours ago`;
            if (seconds < 2592000) return `${Math.floor(seconds/86400)} days ago`;
            return date.toLocaleDateString();
        }

        function addComment() {
            const input = document.querySelector('#contentModalBody .comment-input');
            const postBtn = document.querySelector('#contentModalBody .comment-section .btn.btn-primary');
            if (!input) return;
            const text = (input.value || '').trim();
            if (!text) return;
            if (!currentContentId) return;
            if (postBtn) { postBtn.disabled = true; postBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Posting…'; }
            fetch('/kabaka/public/api/comments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ content_id: currentContentId, text })
            })
            .then(async r => {
                const raw = await r.text();
                let data; try { data = raw.trim() ? JSON.parse(raw) : {}; } catch { data = {}; }
                if (r.ok && data && data.ok) {
                    input.value = '';
                    loadComments(currentContentId);
                    return;
                }
                if (r.status === 401) { alert('Please log in to comment.'); return; }
                if (r.status === 409 && (data.code === 'ALREADY_COMMENTED')) {
                    disableTopLevelInputIfNeeded(true);
                    alert('You already commented on this content.');
                    return;
                }
                alert(data.error || `Failed to add comment (${r.status}): ${raw.substring(0,200)}`);
            })
            .catch((e) => { console.error('addComment failed', e); alert('Failed to add comment'); })
            .finally(() => { if (postBtn) { postBtn.disabled = false; postBtn.innerHTML = '<i class="bi bi-send me-1"></i>Post'; } });
        }
        window.addComment = addComment;
        
        // Toggle description expansion
        function toggleDescription() {
            const descText = document.getElementById('descriptionText');
            const readMoreBtn = document.querySelector('.read-more-btn');
            const fullDesc = document.querySelector('.content-description').getAttribute('data-full-description');
            
            if (descText.classList.contains('collapsed')) {
                // Expand
                descText.textContent = fullDesc;
                descText.classList.remove('collapsed');
                readMoreBtn.textContent = 'Read Less';
            } else {
                // Collapse
                descText.textContent = fullDesc.substring(0, 100) + '...';
                descText.classList.add('collapsed');
                readMoreBtn.textContent = 'Read More';
            }
        }
        
        // Copy content link to clipboard
        function copyContentLink(title, contentId) {
            const contentUrl = `${window.location.origin}/kabaka/public/?content=${contentId}`;
            navigator.clipboard.writeText(contentUrl).then(() => {
                alert('Content link copied to clipboard!');
            }).catch(() => {
                const textArea = document.createElement('textarea');
                textArea.value = contentUrl;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Content link copied to clipboard!');
            });
        }

        // Video hover play functionality
        function initVideoHover() {
            const videoCards = document.querySelectorAll('.content-thumbnail video');
            videoCards.forEach(video => {
                const card = video.closest('.content-thumbnail');
                
                card.addEventListener('mouseenter', () => {
                    if (video.paused) {
                        video.currentTime = 0;
                        video.muted = true; // Keep muted (no sound)
                        video.play().catch(() => {}); // Ignore autoplay errors
                    }
                });
                
                card.addEventListener('mouseleave', () => {
                    if (!video.paused) {
                        video.pause();
                        video.currentTime = 0;
                        video.muted = true; // Keep muted
                    }
                });
            });
        }
        
                // Auto-play video in modal and stop when modal closes
       function setupModalVideo() {
           const modal = document.getElementById('contentModal');
           let currentVideo = null;
           
           modal.addEventListener('show.bs.modal', function() {
               // Reset any existing video state
               if (currentVideo) {
                   currentVideo.pause();
                   currentVideo.currentTime = 0;
                   currentVideo.muted = true;
                   currentVideo = null;
               }
           });
           
           modal.addEventListener('shown.bs.modal', function() {
               const video = this.querySelector('video');
               if (video) {
                   currentVideo = video;
                   video.currentTime = 0;
                   video.muted = false; // Enable sound in modal
                   video.play().catch(() => {}); // Ignore autoplay errors
               }
           });
           
           modal.addEventListener('hidden.bs.modal', function() {
               if (currentVideo && !currentVideo.paused) {
                   currentVideo.pause();
                   currentVideo.currentTime = 0;
                   currentVideo.muted = true; // Mute when modal closes
               }
               currentVideo = null;
           });
       }
        
                // Footer link functions
         function switchToTab(tabName) {
             try {
                 // Remove active class from all tabs
                 document.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
                 
                 // Add active class to selected tab
                 const targetTab = document.getElementById(`${tabName}-tab`);
                 if (targetTab) {
                     targetTab.classList.add('active');
                 }
                 
                 // Hide all tab content
                 document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
                 
                 // Show selected tab content
                 const targetPane = document.getElementById(tabName);
                 if (targetPane) {
                     targetPane.classList.add('show', 'active');
                 }
                 
                 // Load content for the selected tab
                 loadContent(tabName);
                 
                 // Smooth scroll to top
                 window.scrollTo({ top: 0, behavior: 'smooth' });
             } catch (error) {
                 console.error('Error switching tabs:', error);
             }
         }
        
        // Report content using values from the report modal
        async function reportContentFromModal() {
            try {
                if (!currentContentId) { alert('No content selected.'); return; }
                const btn = document.getElementById('modalReportButton');
                const reasonSel = document.getElementById('reportReasonModal');
                const noteEl = document.getElementById('reportNoteModal');
                const reason = (reasonSel?.value || 'spam').toLowerCase();
                const note = (noteEl?.value || '').trim();
                if (btn && btn.disabled) return;
                if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Reporting…'; }
                const res = await fetch('/kabaka/public/api/moderation.php?action=report', {
                    method:'POST', headers:{ 'Content-Type':'application/json' }, credentials:'same-origin',
                    body: JSON.stringify({ content_id: currentContentId, reason, note })
                });
                const raw = await res.text();
                let data = {}; try { data = raw.trim() ? JSON.parse(raw) : {}; } catch(_) { data={}; }
                if (!res.ok || !data.ok) { throw new Error(data.error || ('HTTP '+res.status)); }
                if (btn) { btn.innerHTML = '<i class="bi bi-flag me-1"></i>Reported'; }
                alert('Thanks. Your report has been submitted.' + (data.flagged ? ' This content has been auto-flagged.' : ''));
                if (data.flagged) {
                    const modalEl = document.getElementById('contentModal');
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) bsModal.hide();
                    const cards = document.querySelectorAll(`[data-content-id="${currentContentId}"]`);
                    cards.forEach(el => el.closest('.col-12, .col-sm-6, .col-md-6, .col-lg-4, .col-xl-3')?.remove());
                }
            } catch (e) {
                console.error('Report failed', e);
                alert('Failed to submit report. Please try again.');
            } finally {
                const btn = document.getElementById('modalReportButton');
                if (btn && btn.innerText.toLowerCase().indexOf('report') !== -1) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-flag me-1"></i>Submit Report';
                }
            }
        }

     </script>
 </body>
 </html>
