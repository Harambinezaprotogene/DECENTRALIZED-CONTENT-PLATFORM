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
    <title>Support & Legal - Kabaka</title>
    <link rel="icon" type="image/svg+xml" href="/kabaka/public/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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

        .danger-card {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .danger-card h3 i {
            color: #ef4444;
        }

        .form-control {
            background: var(--secondary-bg);
            border: 1px solid #2a2a2a;
            color: var(--text-primary);
        }

        .form-control:focus {
            background: var(--secondary-bg);
            border-color: var(--accent-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
        }

        .btn-danger {
            background: #dc2626;
            border-color: #dc2626;
        }

        .btn-danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="guide-container">
        <a href="dashboard.php" class="back-btn">
            <i class="bi bi-arrow-left"></i>Back to Dashboard
        </a>

        <div class="guide-header">
            <h1 class="guide-title">Support & Help</h1>
            <p class="guide-subtitle">Get help, find answers, and manage your account</p>
        </div>
       
                    <!-- Help Center -->
        <div class="guide-card">
            <h3><i class="bi bi-question-circle"></i>Help Center</h3>
                                <p>Find answers to common questions and learn how to use our platform effectively.</p>
                                
            <h4 class="text-white mt-3 mb-2">Frequently Asked Questions:</h4>
            <ul class="feature-list">
                <li><strong>Account Creation:</strong> Click "Register" on the login page, enter your email and password, then verify your account via email.</li>
                <li><strong>Content Issues:</strong> Make sure you're logged in and check your internet connection. Try refreshing the page or clearing your browser cache.</li>
                <li><strong>Password Reset:</strong> Click "Forgot Password" on the login page, enter your email, and follow the instructions sent to your inbox.</li>
                <li><strong>Viewing Content:</strong> Browse through different tabs (Featured, Trending, Recent) to discover new content from creators.</li>
                <li><strong>Following Creators:</strong> Click the "Follow" button on any creator's content to stay updated with their latest posts.</li>
            </ul>
                                </div>
                                
        <!-- Contact Support -->
        <div class="guide-card">
            <h3><i class="bi bi-envelope"></i>Contact Support</h3>
            <p>Have a question or need assistance? Our support team is here to help you.</p>
            
            <h4 class="text-white mt-3 mb-2">How to Contact Us:</h4>
            <ul class="feature-list">
                <li><strong>Email Support:</strong> Send us an email at support@kabaka.com for general inquiries</li>
                <li><strong>Technical Issues:</strong> Report bugs and technical problems through our bug reporting system</li>
                <li><strong>Response Time:</strong> We typically respond within 24 hours during business days</li>
                <li><strong>Priority Levels:</strong> High priority issues are addressed first, followed by medium and low priority</li>
            </ul>
                    </div>
                    
        <!-- Report Issues -->
        <div class="guide-card">
            <h3><i class="bi bi-bug"></i>Report Issues</h3>
                                <p>Found a bug or experiencing technical problems? Help us improve by reporting the issue.</p>
                                
            <h4 class="text-white mt-3 mb-2">What to Report:</h4>
            <ul class="feature-list">
                <li><strong>Technical Bugs:</strong> Pages not loading, buttons not working, or error messages</li>
                <li><strong>Performance Issues:</strong> Slow loading times or content not displaying properly</li>
                <li><strong>Content Problems:</strong> Inappropriate content or copyright violations</li>
                <li><strong>Account Issues:</strong> Login problems, profile updates not saving, or notification issues</li>
            </ul>
                    </div>
                    
        <!-- Account Management -->
        <div class="guide-card">
            <h3><i class="bi bi-person-gear"></i>Account Management</h3>
            <p>Manage your account settings, privacy preferences, and personal information.</p>
            
            <h4 class="text-white mt-3 mb-2">Account Features:</h4>
            <ul class="feature-list">
                <li><strong>Profile Settings:</strong> Update your display name, email, and profile information</li>
                <li><strong>Privacy Controls:</strong> Manage who can see your activity and follow you</li>
                <li><strong>Notification Settings:</strong> Control what notifications you receive</li>
                <li><strong>Data Export:</strong> Download your account data and activity history</li>
            </ul>
            </div>

        <!-- Legal & Policies -->
        <div class="guide-card">
            <h3><i class="bi bi-shield-check"></i>Legal & Policies</h3>
            <p>Important information about our terms of service, privacy policy, and community guidelines.</p>
            
            <h4 class="text-white mt-3 mb-2">Important Documents:</h4>
            <ul class="feature-list">
                <li><strong>Terms of Service:</strong> Our rules and guidelines for using the platform</li>
                <li><strong>Privacy Policy:</strong> How we collect, use, and protect your personal information</li>
                <li><strong>Community Guidelines:</strong> Standards for content and behavior on our platform</li>
                <li><strong>Copyright Policy:</strong> Information about intellectual property and content ownership</li>
            </ul>
                                </div>
                     
                     <!-- Delete Account -->
        <div class="guide-card danger-card">
            <h3><i class="bi bi-exclamation-triangle"></i>Delete Account</h3>
            <p>Permanently delete your account and all associated data. This action cannot be undone.</p>
            
            <div class="mt-3">
                                     <form id="deleteAccountForm">
                                         <div class="mb-3">
                        <label class="form-label">Type "DELETE" to confirm account deletion:</label>
                        <input type="text" class="form-control" id="deleteConfirmation" placeholder="Type DELETE to confirm" required>
                                         </div>
                                         <button type="submit" class="btn btn-danger btn-md" id="deleteAccountBtn" disabled>
                                             <i class="bi bi-trash me-1"></i>Delete My Account
                                         </button>
                                     </form>
             </div>
         </div>
     </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
         // Delete account confirmation
         document.addEventListener('DOMContentLoaded', function() {
             const deleteConfirmation = document.getElementById('deleteConfirmation');
             const deleteAccountBtn = document.getElementById('deleteAccountBtn');
             
             if (deleteConfirmation && deleteAccountBtn) {
                 deleteConfirmation.addEventListener('input', function() {
                     if (this.value === 'DELETE') {
                         deleteAccountBtn.disabled = false;
                         deleteAccountBtn.classList.remove('btn-secondary');
                         deleteAccountBtn.classList.add('btn-danger');
                     } else {
                         deleteAccountBtn.disabled = true;
                         deleteAccountBtn.classList.remove('btn-danger');
                         deleteAccountBtn.classList.add('btn-secondary');
                     }
                 });
                 
                 // Delete account form submission
                 document.getElementById('deleteAccountForm').addEventListener('submit', function(e) {
                     e.preventDefault();
                     
                         if (confirm('Are you absolutely sure you want to delete your account? This action cannot be undone!')) {
                             // Simulate account deletion
                             const btn = deleteAccountBtn;
                             const originalText = btn.innerHTML;
                             btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Deleting Account...';
                             btn.disabled = true;
                             
                             setTimeout(() => {
                                 alert('Account deletion request submitted. Your account will be permanently deleted within 24 hours.');
                                 this.reset();
                                 deleteAccountBtn.disabled = true;
                                 deleteAccountBtn.classList.remove('btn-danger');
                                 deleteAccountBtn.classList.add('btn-secondary');
                                 btn.innerHTML = originalText;
                             }, 2000);
                     }
                 });
             }
         });
     </script>
</body>
</html>