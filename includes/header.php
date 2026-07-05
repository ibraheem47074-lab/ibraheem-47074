<?php
// Fix path for includes when called from admin directory
$basePath = dirname(__DIR__) . '/';
require_once $basePath . 'config/database.php';
require_once $basePath . 'config/helpers.php';
require_once $basePath . 'includes/language_functions.php';

// Set security headers (only if not already sent)
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Initialize language system
$current_lang = get_current_language();
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    
    <!-- Google AdSense Verification -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3649343603124512" crossorigin="anonymous"></script>
    
    <!-- AMP Ad Library -->
    <script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>
    
    <!-- Android PWA Manifest -->
    <link rel="manifest" href="<?php echo SITE_URL; ?>manifest.json">
    
    <!-- Android Theme Color -->
    <meta name="theme-color" content="#dc3545">
    <meta name="msapplication-TileColor" content="#dc3545">
    <meta name="msapplication-config" content="<?php echo SITE_URL; ?>browserconfig.xml">
    
    <!-- Android App Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo SITE_URL; ?>assets/images/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>assets/images/icons/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>assets/images/icons/icon-16x16.png">
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>favicon.ico">
    
    <!-- Android Optimizations -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PK Live News">
    <meta name="application-name" content="PK Live News">
    
    <!-- Android Performance -->
    <meta name="format-detection" content="telephone=no">
    <meta name="format-detection" content="address=no">
    <meta name="format-detection" content="email=no">
    
    <!-- Android Security Headers are set via PHP headers -->
    
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PK Live News</title>
    
    <!-- Multi-language SEO -->
    <?php if (get_site_setting('multilingual_seo', '1') == '1'): ?>
        <?php echo generate_hreflang_tags(); ?>
    <?php endif; ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">
    <!-- Live TV CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/live-tv.css" rel="stylesheet">
    <!-- Android PWA Optimizations -->
    <link href="<?php echo SITE_URL; ?>assets/css/android-optimizations.css" rel="stylesheet">
    <!-- Heat Map CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/heatmap.css" rel="stylesheet">
    <!-- Weather CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/weather.css" rel="stylesheet">
    <!-- Image Lightbox CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/image-lightbox.css" rel="stylesheet">
    <!-- Video Lightbox CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/video-lightbox.css" rel="stylesheet">
    <!-- Affiliate Products CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/affiliate-products.css" rel="stylesheet">
    
    <!-- Custom CSS for Dropdowns and Notifications Z-Index Fix -->
    <style>
        /* Navigation Bar */
        .navbar {
            z-index: 1000 !important;
        }
        
        /* All Dropdown Menus */
        .dropdown-menu {
            z-index: 1050 !important;
        }
        
        /* Notifications Dropdown */
        .notifications-dropdown .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Search Dropdown */
        .search-dropdown .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* User Dropdown */
        .user-dropdown .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Language Switcher Dropdown */
        .language-switcher .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Notification Alerts */
        .alert.position-fixed {
            z-index: 1060 !important;
        }
        
        /* Modal Backdrop */
        .modal-backdrop {
            z-index: 1040 !important;
        }
        
        /* Modal */
        .modal {
            z-index: 1055 !important;
        }
        
        /* Toast Notifications */
        .toast {
            z-index: 1065 !important;
        }
        
        /* Ensure dropdowns appear above navigation */
        .nav-item .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Header Category Dropdown Styles */
        #categoriesDropdown + .dropdown-menu {
            min-width: 280px;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            padding: 8px 0;
        }
        
        #categoriesDropdown + .dropdown-menu .dropdown-item {
            padding: 10px 20px;
            border-radius: 4px;
            margin: 2px 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        #categoriesDropdown + .dropdown-menu .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #dc3545;
            transform: translateX(5px);
        }
        
        #categoriesDropdown + .dropdown-menu .dropdown-item .badge {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            background-color: #6c757d;
        }
        
        #categoriesDropdown + .dropdown-menu .dropdown-item:hover .badge {
            background-color: #dc3545;
        }
        
        /* Fix for any overlapping elements */
        .dropdown.show .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Remove space between Main Header and Navigation */
        .main-header {
            margin-bottom: 0 !important;
        }
        
        /* Mobile Category Dropdown */
        @media (max-width: 768px) {
            #categoriesDropdown + .dropdown-menu {
                min-width: 250px;
                max-height: 300px;
            }
            
            #categoriesDropdown + .dropdown-menu .dropdown-item {
                padding: 8px 15px;
                font-size: 0.85rem;
            }
            
            #categoriesDropdown + .dropdown-menu .dropdown-item .badge {
                font-size: 0.65rem;
                padding: 1px 4px;
            }
        }
        
        .navbar {
            margin-top: 0 !important;
        }
        
        /* Hide any bell icon between header and navigation */
        .main-header + .navbar .fa-bell,
        .main-header + .navbar .fa-bell-slash,
        header + nav .fa-bell,
        header + nav .fa-bell-slash,
        .custom-header .fa-bell,
        .custom-header .fa-bell-slash,
        header ~ nav .fa-bell,
        header ~ nav .fa-bell-slash,
        .main-header ~ .navbar .fa-bell,
        .main-header ~ .navbar .fa-bell-slash,
        header::after .fa-bell,
        header::before .fa-bell {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }
        
        /* Remove any floating or positioned bell icons in header area */
        .main-header .fa-bell-slash,
        .main-header .fa-bell:not(.notification-btn .fa-bell) {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Header Icon Buttons - Modern Styling */
        .header-right {
            gap: 8px;
            align-items: center;
            flex-wrap: nowrap;
        }
        
        .header-icon-wrapper,
        .language-switcher,
        .search-dropdown {
            margin: 0 !important;
        }
        
        .header-icon-btn,
        .language-switcher .btn,
        .search-dropdown .btn {
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            width: 38px !important;
            min-width: 38px !important;
            max-width: 38px !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 10px !important;
            border: none !important;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.05) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            cursor: pointer;
        }
        
        /* Header right buttons that are NOT auth buttons (icon-only buttons) */
        .header-right .btn:not(.auth-btn) {
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            width: 38px !important;
            min-width: 38px !important;
            max-width: 38px !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 10px !important;
            border: none !important;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.05) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            cursor: pointer;
        }
        
        /* Icon colors */
        .header-icon-btn i,
        .header-right .btn i,
        .language-switcher .btn span,
        .search-dropdown .btn i {
            font-size: 16px;
            color: #495057;
            transition: color 0.3s ease;
        }
        
        /* Notification button specific */
        .notification-btn {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
        }
        
        .notification-btn i {
            color: #f39c12 !important;
        }
        
        /* Search button specific */
        .search-dropdown .btn {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%) !important;
        }
        
        .search-dropdown .btn i {
            color: #dc3545 !important;
        }
        
        /* User menu button specific */
        .header-right .btn-outline-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%) !important;
        }
        
        .header-right .btn-outline-danger i,
        .header-right .btn-outline-danger img {
            color: #dc3545 !important;
        }
        
        /* Language switcher specific */
        .language-switcher .btn {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%) !important;
        }
        
        .language-switcher .btn span {
            font-size: 18px;
        }
        
        /* Products button specific */
        .header-right .btn-outline-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%) !important;
        }
        
        .header-right .btn-outline-success i {
            color: #28a745 !important;
        }
        
        /* Hover effects */
        .header-icon-btn:hover,
        .header-right .btn:hover,
        .language-switcher .btn:hover,
        .search-dropdown .btn:hover {
            transform: translateY(-2px) scale(1.05) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15), 0 2px 6px rgba(0,0,0,0.1) !important;
        }
        
        .notification-btn:hover {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%) !important;
        }
        
        .search-dropdown .btn:hover {
            background: linear-gradient(135deg, #f5c6cb 0%, #f1b0b7 100%) !important;
        }
        
        .header-right .btn-outline-success:hover {
            background: linear-gradient(135deg, #c3e6cb 0%, #b1dfbb 100%) !important;
        }
        
        /* Active/Focus states */
        .header-icon-btn:active,
        .header-right .btn:active,
        .language-switcher .btn:active,
        .search-dropdown .btn:active {
            transform: translateY(0) scale(0.95) !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        }
        
        /* Hide dropdown arrows - icon only buttons */
        .header-icon-wrapper .dropdown-toggle::after,
        .language-switcher .dropdown-toggle::after,
        .search-dropdown .dropdown-toggle::after {
            display: none !important;
        }
        
        /* Ensure all header buttons show only icons */
        .header-right .btn,
        .language-switcher .btn,
        .search-dropdown .btn {
            overflow: hidden !important;
        }
        
        .header-right .btn > *,
        .language-switcher .btn > *,
        .search-dropdown .btn > * {
            margin: 0 !important;
        }
        
        /* Header Search Forms - Modern Styling */
        .header-search-form,
        .weather-search-form {
            display: flex;
            align-items: center;
        }
        
        .header-search-form .form-control,
        .weather-search-form .form-control {
            height: 36px !important;
            font-size: 14px;
            border: 2px solid #e9ecef;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .header-search-form .form-control:focus,
        .weather-search-form .form-control:focus {
            border-color: #dc3545;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05), 0 0 0 3px rgba(220,53,69,0.1);
            outline: none;
        }
        
        .header-search-form .btn {
            height: 36px !important;
            width: 36px !important;
            min-width: 36px !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(220,53,69,0.3) !important;
            transition: all 0.3s ease !important;
        }
        
        .header-search-form .btn:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%) !important;
            box-shadow: 0 4px 12px rgba(220,53,69,0.4) !important;
            transform: translateY(-1px);
        }
        
        .weather-search-form .btn {
            height: 36px !important;
            width: 36px !important;
            min-width: 36px !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(255,193,7,0.3) !important;
            transition: all 0.3s ease !important;
        }
        
        .weather-search-form .btn:hover {
            background: linear-gradient(135deg, #e0a800 0%, #c69500 100%) !important;
            box-shadow: 0 4px 12px rgba(255,193,7,0.4) !important;
            transform: translateY(-1px);
        }
        
        .header-search-form .btn i,
        .weather-search-form .btn i {
            font-size: 14px;
            color: #fff;
        }
        
        /* Weather toggle button */
        .header-right .btn-outline-warning {
            height: 36px !important;
            width: 36px !important;
            min-width: 36px !important;
            padding: 0 !important;
            border-radius: 10px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(255,193,7,0.2) !important;
            transition: all 0.3s ease !important;
        }
        
        .header-right .btn-outline-warning:hover {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%) !important;
            box-shadow: 0 4px 12px rgba(255,193,7,0.3) !important;
            transform: translateY(-2px) scale(1.05);
        }
        
        .header-right .btn-outline-warning i {
            color: #f39c12 !important;
            font-size: 16px;
        }
        
        /* Mobile / Android adjustments */
        @media (max-width: 768px) {
            .header-right {
                gap: 6px;
            }
            
            .header-icon-btn,
            .language-switcher .btn,
            .search-dropdown .btn,
            .header-right .btn:not(.auth-btn) {
                height: 36px !important;
                min-height: 36px !important;
                max-height: 36px !important;
                width: 36px !important;
                min-width: 36px !important;
                max-width: 36px !important;
            }
            
            .header-right .dropdown-toggle:not(.header-icon-btn):not(.auth-btn) {
                min-width: 36px !important;
                padding: 0 8px !important;
            }
            
            .header-right .btn:not(.auth-btn) i,
            .language-switcher .btn span,
            .search-dropdown .btn i {
                font-size: 14px;
            }
        }
        
        /* Small mobile devices */
        @media (max-width: 576px) {
            .header-right {
                gap: 4px;
            }
            
            .header-icon-btn,
            .language-switcher .btn,
            .search-dropdown .btn,
            .header-right .btn:not(.auth-btn) {
                height: 34px !important;
                min-height: 34px !important;
                max-height: 34px !important;
                width: 34px !important;
                min-width: 34px !important;
                max-width: 34px !important;
            }
            
            .header-right .dropdown-toggle:not(.header-icon-btn):not(.auth-btn) {
                min-width: 34px !important;
                padding: 0 6px !important;
            }
        }
        
        /* Auth Buttons Styling */
        .auth-btn {
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            padding: 0 16px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: 2px solid !important;
            text-decoration: none !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.05) !important;
        }
        
        .login-btn {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            border-color: #dc3545 !important;
            color: #dc3545 !important;
        }
        
        .login-btn:hover {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
            border-color: #dc3545 !important;
            color: #ffffff !important;
            transform: translateY(-2px) scale(1.02) !important;
            box-shadow: 0 4px 12px rgba(220,53,69,0.3) !important;
        }
        
        .signup-btn {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
            border-color: #dc3545 !important;
            color: #ffffff !important;
        }
        
        .signup-btn:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%) !important;
            border-color: #c82333 !important;
            color: #ffffff !important;
            transform: translateY(-2px) scale(1.02) !important;
            box-shadow: 0 4px 12px rgba(220,53,69,0.4) !important;
        }
        
        .auth-btn:active {
            transform: translateY(0) scale(0.98) !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        }
        
        .auth-btn i {
            font-size: 14px !important;
            margin-right: 6px !important;
        }
        
        /* Mobile adjustments for auth buttons */
        @media (max-width: 768px) {
            .auth-btn {
                height: 36px !important;
                min-height: 36px !important;
                max-height: 36px !important;
                padding: 0 12px !important;
                font-size: 13px !important;
            }
            
            .auth-btn i {
                font-size: 12px !important;
                margin-right: 4px !important;
            }
        }
        
        @media (max-width: 576px) {
            .auth-btn {
                height: 34px !important;
                min-height: 34px !important;
                max-height: 34px !important;
                padding: 0 10px !important;
                font-size: 12px !important;
            }
            
            .auth-btn i {
                font-size: 11px !important;
                margin-right: 3px !important;
            }
        }
    </style>
    
    <!-- Custom CSS from System Settings -->
    <style>
        <?php echo get_system_setting('custom_css', ''); ?>
    </style>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
  
</head>
<body>
    <!-- Breaking News Ticker -->
    <div class="breaking-news-ticker bg-danger text-white py-2">
        <div class="container">
            <div class="d-flex align-items-center">
                <span class="breaking-label bg-dark text-white px-3 py-1 me-3">BREAKING</span>
                <div class="breaking-news-scroll">
                    <marquee behavior="scroll" direction="left">
                        <?php
                        $breaking_query = "SELECT n.*, c.name as category_name FROM news n 
                                         LEFT JOIN categories c ON n.category_id = c.id 
                                         WHERE n.is_breaking = 1 AND n.status = 'published' 
                                         ORDER BY n.published_at DESC LIMIT 5";
                        $breaking_result = mysqli_query($conn, $breaking_query);
                        while ($breaking = mysqli_fetch_assoc($breaking_result)) {
                            $title = get_news_title($breaking);
                            echo '<a href="news.php?slug=' . $breaking['slug'] . '" class="text-white text-decoration-none me-4">' . htmlspecialchars($title) . '</a>';
                        }
                        ?>
                    </marquee>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header bg-white">
        <div class="container">
            <div class="header-single-line d-flex align-items-center justify-content-between py-1">
                <!-- Logo -->
                <div class="logo">
                    <a href="index.php" class="text-decoration-none">
                        <h1 class="text-danger mb-0">PK-<span class="text-dark">LIVE</span> NEWS</h1>
                    </a>
                </div>
                <!-- Header Icons -->
                <div class="header-right d-flex align-items-center">
                        <?php if (is_logged_in()): ?>



                         <!-- Search News (Always Visible) -->
                         <div class="dropdown search-dropdown">
                            <button class="btn btn-outline-secondary header-icon-btn" 
                                    type="button" id="searchDropdown" data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <i class="fas fa-search"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                                <form action="search.php" method="GET" class="header-search-form">
                                    <div class="input-group">
                                        <input type="text" name="q" class="form-control form-control-sm" 
                                               placeholder="Search news..." id="searchInput">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                                <div class="search-results mt-2" id="searchResults" style="display: none;"></div>
                            </div>
                        </div>
                        
                 



                            <!-- Notifications -->
                            <div class="dropdown header-icon-wrapper">
                                <button class="btn btn-outline-secondary position-relative notification-btn" 
                                        type="button" id="notificationDropdownBtn" data-bs-toggle="dropdown" 
                                        aria-expanded="false" onclick="loadNotifications()">
                                    <i class="fas fa-bell"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" 
                                          id="notificationCount" style="display: none;">0</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end notification-dropdown" 
                                    id="notificationDropdownMenu" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
                                    <li class="dropdown-header d-flex justify-content-between align-items-center">
                                        <span>Notifications</span>
                                        <button class="btn btn-sm btn-outline-primary" onclick="markAllNotificationsRead()">
                                            Mark all read
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li id="notificationList">
                                        <div class="text-center p-3">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="d-none" id="noNotifications">
                                        <div class="text-center p-3 text-muted">
                                            <i class="fas fa-bell-slash fa-2x mb-2"></i>
                                            <p class="mb-0">No new notifications</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            



                           

                            <!-- Language Switcher -->
                        <div class="language-switcher ms-2">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary header-icon-btn" 
                                        type="button" 
                                        id="languageDropdown" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false">
                                    <span>🌐</span>
                                </button>
                                
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="?lang=en">
                                            <span class="me-2">🇺🇸</span>
                                            <span>English</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="?lang=ur">
                                            <span class="me-2">🇵🇰</span>
                                            <span>اردو</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="?lang=hi">
                                            <span class="me-2">🇮🇳</span>
                                            <span>हिन्दी</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="?lang=ps">
                                            <span class="me-2">🇦🇫</span>
                                            <span>پښتو</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="?lang=zh">
                                            <span class="me-2">🇨🇳</span>
                                            <span>中文</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>



                        
                            <!-- User Menu -->
                            <div class="dropdown header-icon-wrapper">
                                <button class="btn btn-outline-danger" type="button" 
                                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php
                                    // Get user profile image - handle missing columns gracefully
                                    $user_image = '';
                                    if (isset($_SESSION['user_id'])) {
                                        $user_id = $_SESSION['user_id'];
                                        
                                        // Check if image column exists
                                        $column_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'image'");
                                        if (mysqli_num_rows($column_check) > 0) {
                                            $image_query = "SELECT image FROM users WHERE id = ?";
                                            $stmt = mysqli_prepare($conn, $image_query);
                                            mysqli_stmt_bind_param($stmt, 'i', $user_id);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            $user_data = mysqli_fetch_assoc($result);
                                            
                                            if ($user_data && !empty($user_data['image'])) {
                                                $user_image = $user_data['image'];
                                            }
                                        } else {
                                            // Try avatar column instead
                                            $avatar_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'avatar'");
                                            if (mysqli_num_rows($avatar_check) > 0) {
                                                $avatar_query = "SELECT avatar FROM users WHERE id = ?";
                                                $stmt = mysqli_prepare($conn, $avatar_query);
                                                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                                                mysqli_stmt_execute($stmt);
                                                $result = mysqli_stmt_get_result($stmt);
                                                $user_data = mysqli_fetch_assoc($result);
                                                
                                                if ($user_data && !empty($user_data['avatar'])) {
                                                    $user_image = $user_data['avatar'];
                                                }
                                            }
                                        }
                                        
                                        if (!empty($user_image)) {
                                            echo '<img src="' . htmlspecialchars($user_image) . '" alt="Profile" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #dc3545;">';
                                        } else {
                                            echo '<i class="fas fa-user-circle" style="font-size: 32px; color: #dc3545;"></i>';
                                        }
                                    } else {
                                        echo '<i class="fas fa-user-circle"></i>';
                                    }
                                    ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="index.php"><i class="fas fa-bookmark me-2"></i>Bookmarks</a></li>
                                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                    
                                    <!-- Reporter Dashboard Access -->
                                    <?php if (is_reporter()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Reporter Panel</h6></li>
                                        <li><a class="dropdown-item" href="admin/reporter-dashboard-enhanced.php"><i class="fas fa-tachometer-alt me-2"></i>Reporter Dashboard</a></li>
                                        <li><a class="dropdown-item" href="admin/add-news.php"><i class="fas fa-plus me-2"></i>Create Article</a></li>
                                        <li><a class="dropdown-item" href="admin/my-articles.php"><i class="fas fa-newspaper me-2"></i>My Articles</a></li>
                                        <li><a class="dropdown-item" href="admin/my-comments.php"><i class="fas fa-comments me-2"></i>My Comments</a></li>
                                        <li><a class="dropdown-item" href="admin/reporter-profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                                    <?php endif; ?>
                                    
                                    <!-- Editor Dashboard Access -->
                                    <?php if (is_editor()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Editor Panel</h6></li>
                                        <li><a class="dropdown-item" href="admin/editor-dashboard-enhanced.php"><i class="fas fa-tachometer-alt me-2"></i>Editor Dashboard</a></li>
                                        <li><a class="dropdown-item" href="admin/editor_news.php"><i class="fas fa-newspaper me-2"></i>Manage News</a></li>
                                        <li><a class="dropdown-item" href="admin/editor_comments_enhanced.php"><i class="fas fa-comments me-2"></i>Manage Comments</a></li>
                                        <li><a class="dropdown-item" href="admin/editor-polls.php"><i class="fas fa-poll me-2"></i>Manage Polls</a></li>
                                        <li><a class="dropdown-item" href="admin/editor-statistics.php"><i class="fas fa-chart-line me-2"></i>Statistics</a></li>
                                        <li><a class="dropdown-item" href="admin/manage-categories.php"><i class="fas fa-tags me-2"></i>Categories</a></li>
                                        <li><a class="dropdown-item" href="admin/editor_profile_enhanced.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                                    <?php endif; ?>
                                    
                                    <!-- Admin Dashboard Access -->
                                    <?php if (is_admin()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Admin Panel</h6></li>
                                        <li><a class="dropdown-item" href="admin/admin-dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</a></li>
                                        <li><a class="dropdown-item" href="admin/manage-users.php"><i class="fas fa-users me-2"></i>Manage Users</a></li>
                                        <li><a class="dropdown-item" href="admin/manage-news.php"><i class="fas fa-newspaper me-2"></i>Manage News</a></li>
                                        <li><a class="dropdown-item" href="admin/manage-categories-enhanced.php"><i class="fas fa-tags me-2"></i>Categories</a></li>
                                        <li><a class="dropdown-item" href="admin/live-stream-control.php"><i class="fas fa-broadcast-tower me-2"></i>Live Stream</a></li>
                                        <li><a class="dropdown-item" href="admin/system-settings.php"><i class="fas fa-cogs me-2"></i>Settings</a></li>
                                    <?php endif; ?>
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <!-- Login/Signup Buttons -->
                            <a href="login.php" class="btn btn-outline-danger me-2 auth-btn login-btn">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                            <a href="signup.php" class="btn btn-danger auth-btn signup-btn">
                                <i class="fas fa-user-plus me-1"></i>Sign Up
                            </a>
                        <?php endif; ?>
                        
                        
                        
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </header>
    
     <!-- Header Advertisement Banner -->
    <div class="container text-center my-3 d-none d-lg-block">
        <amp-ad
             layout="fixed"
             width="728"
             height="90"
             type="adsense"
             data-ad-client="ca-pub-3649343603124512"
             data-ad-slot="8565082420">
        </amp-ad>
    </div>



    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="index.php">
                            <i class="fas fa-home me-2"></i>
                            <span>Home</span>
                        </a>
                    </li>
                    <!-- Categories Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link d-flex align-items-center dropdown-toggle" href="#" 
                           id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-tags me-2"></i>
                            <span>Categories</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                            <?php
                            $cat_query = "SELECT * FROM categories WHERE status = 'active' AND slug NOT IN ('cnn-politics', 'cnn-world', 'cnn-international', 'cnn-environment', 'cnn-us-news', 'cnn-media', 'cnn-business', 'cnn-technology', 'cnn-entertainment', 'cnn-sports', 'ary-world', 'ary-politics', 'ary-business', 'ary-technology', 'ary-entertainment', 'ary-sports', 'bbc-world', 'bbc-politics', 'bbc-business', 'bbc-technology', 'bbc-entertainment', 'bbc-sports') AND name NOT LIKE '%CNN International%' AND name NOT LIKE '%BBC World News%' AND name NOT LIKE '%ARY%' ORDER BY name ASC";
                            $cat_result = mysqli_query($conn, $cat_query);
                            while ($category = mysqli_fetch_assoc($cat_result)) {
                                echo '<li>';
                                echo '<a class="dropdown-item d-flex align-items-center justify-content-between" href="category.php?slug=' . $category['slug'] . '">';
                                echo '<span><i class="fas fa-tag me-2 text-primary"></i>' . htmlspecialchars($category['name']) . '</span>';
                                $count_query = "SELECT COUNT(*) as count FROM news WHERE category_id = " . $category['id'] . " AND status = 'published'";
                                $count_result = mysqli_query($conn, $count_query);
                                $count = mysqli_fetch_assoc($count_result)['count'];
                                echo '<span class="badge bg-secondary ms-2">' . $count . '</span>';
                                echo '</a>';
                                echo '</li>';
                            }
                            ?>
                        </ul>
                    </li>
                  
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="live.php">
                            <i class="fas fa-tv me-2"></i>
                            <span>Live TV</span>
                        </a>
                    </li>
                    
                    <?php
                    // Check if affiliate tables exist and show products menu
                    $affiliate_tables_exist = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
                    if (mysqli_num_rows($affiliate_tables_exist) > 0):
                        // Get product categories
                        require_once $basePath . 'includes/affiliate-functions.php';
                        $product_categories = get_product_categories();
                        
                        // Debug: Show category count (remove this in production)
                        if (empty($product_categories)) {
                            echo "<!-- DEBUG: No product categories found -->";
                        } else {
                            echo "<!-- DEBUG: Found " . count($product_categories) . " product categories -->";
                        }
                    ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link d-flex align-items-center text-primary fw-bold dropdown-toggle" href="#" 
                           id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-shopping-cart me-2"></i>
                            <span>Products</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                            <li>
                                <a class="dropdown-item" href="products.php">
                                    <i class="fas fa-th-large me-2"></i>All Products
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php foreach ($product_categories as $category): ?>
                                <li>
                                    <a class="dropdown-item" href="products.php?category=<?php echo $category['id']; ?>">
                                        <?php if (!empty($category['icon'])): ?>
                                            <i class="<?php echo htmlspecialchars($category['icon']); ?> me-2"></i>
                                        <?php else: ?>
                                            <i class="fas fa-tag me-2"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link d-flex align-items-center text-warning fw-bold dropdown-toggle" href="#" 
                           id="weatherDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" onclick="toggleWeatherSearch()">
                            <i class="fas fa-cloud-sun me-2"></i>
                            <span>Weather</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;">
                            <!-- Weather Search Form (Hidden by default) -->
                            <form id="weatherSearchForm" action="weather.php" method="GET" style="display: none;">
                                <div class="input-group">
                                    <input type="text" name="city" id="weatherCityInput" class="form-control" 
                                           placeholder="Enter city name..." autocomplete="off">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                            <div class="text-center mb-2">
                                <small class="text-muted">Quick Weather Access</small>
                            </div>
                            <a href="weather.php" class="btn btn-outline-warning btn-sm w-100">
                                <i class="fas fa-cloud-sun me-1"></i>Full Weather Dashboard
                            </a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center text-success fw-bold" href="news-performance.php">
                            <i class="fas fa-chart-bar me-2"></i>
                            <span>Performance</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center text-info fw-bold" href="news_map.php">
                            <i class="fas fa-globe-americas me-2"></i>
                            <span>Map</span>
                        </a>
                    </li>
                    <li class="nav-item">
                         <a class="nav-link d-flex align-items-center" href="about.php">
                            <i class="fas fa-info-circle me-2"></i>
                            <span>About Us</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="contact.php">
                            <i class="fas fa-envelope me-2"></i>
                            <span>Contact</span>
                        </a>
                    </li>
                </ul>
                
                <!-- Dark Mode Toggle -->
                <div class="dark-mode-toggle">
                    <button class="btn btn-outline-light btn-sm" onclick="toggleDarkMode()">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Custom Header Content from System Settings -->
    <?php
    $custom_header_content = get_system_setting('header_content', '');
    if (!empty($custom_header_content)) {
        echo '<div class="custom-header">' . $custom_header_content . '</div>';
    }
    ?>

<!-- Notification JavaScript -->
<script>
// Toggle Weather Search Form
function toggleWeatherSearch() {
    const weatherForm = document.getElementById('weatherSearchForm');
    if (weatherForm.style.display === 'none') {
        weatherForm.style.display = 'block';
        const weatherInput = document.getElementById('weatherCityInput');
        if (weatherInput) {
            weatherInput.focus();
        }
    } else {
        weatherForm.style.display = 'none';
    }
}

let notificationCount = 0;

// Load notifications when dropdown is opened
function loadNotifications() {
    fetch('api/notifications.php?action=get')
        .then(response => response.json())
        .then(data => {
            updateNotificationUI(data);
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showNotificationError();
        });
}

// Update notification UI
function updateNotificationUI(data) {
    const notificationList = document.getElementById('notificationList');
    const noNotifications = document.getElementById('noNotifications');
    const notificationBadge = document.getElementById('notificationCount');
    
    // Update badge
    notificationCount = data.unread_count;
    if (notificationCount > 0) {
        notificationBadge.textContent = notificationCount > 99 ? '99+' : notificationCount;
        notificationBadge.style.display = 'inline-block';
    } else {
        notificationBadge.style.display = 'none';
    }
    
    // Update notification list
    if (data.notifications && data.notifications.length > 0) {
        let html = '';
        data.notifications.forEach(notification => {
            const priorityClass = getPriorityClass(notification.priority);
            html += `
                <li>
                    <a href="${notification.url || '#'}" class="dropdown-item notification-item ${priorityClass}" 
                       onclick="markNotificationRead(${notification.id}, event)">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${notification.title}</h6>
                                <p class="mb-1 small text-muted">${notification.message}</p>
                                <small class="text-muted">${notification.time_ago}</small>
                            </div>
                            <div class="ms-2">
                                ${getPriorityIcon(notification.priority)}
                            </div>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
            `;
        });
        notificationList.innerHTML = html;
        noNotifications.classList.add('d-none');
    } else {
        notificationList.innerHTML = '';
        noNotifications.classList.remove('d-none');
    }
}

// Show notification error
function showNotificationError() {
    const notificationList = document.getElementById('notificationList');
    notificationList.innerHTML = `
        <li>
            <div class="dropdown-item text-center text-muted">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Failed to load notifications
            </div>
        </li>
    `;
}

// Get priority class for styling
function getPriorityClass(priority) {
    switch(priority) {
        case 'urgent': return 'border-start border-4 border-danger';
        case 'high': return 'border-start border-4 border-warning';
        case 'medium': return 'border-start border-4 border-info';
        default: return '';
    }
}

// Get priority icon
function getPriorityIcon(priority) {
    switch(priority) {
        case 'urgent': return '<i class="fas fa-exclamation-circle text-danger"></i>';
        case 'high': return '<i class="fas fa-exclamation-triangle text-warning"></i>';
        case 'medium': return '<i class="fas fa-info-circle text-info"></i>';
        default: return '<i class="fas fa-bell text-muted"></i>';
    }
}

// Mark notification as read
function markNotificationRead(notificationId, event) {
    if (notificationId) {
        fetch('api/notifications.php?action=mark_read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `notification_id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificationCount--;
                updateNotificationBadge();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }
}

// Load notifications
function loadNotifications() {
    const notificationList = document.getElementById('notificationList');
    const noNotifications = document.getElementById('noNotifications');
    
    if (!notificationList || !noNotifications) return;
    
    // Show loading state
    notificationList.innerHTML = `
        <div class="text-center p-3">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    fetch('api/notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotifications(data.notifications);
                updateNotificationBadge(data.unread_count);
            } else {
                showNotificationError();
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showNotificationError();
        });
}

// Mark all notifications as read
function markAllNotificationsRead() {
    fetch('api/notifications.php?action=mark_all_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notificationCount = 0;
            updateNotificationBadge();
            loadNotifications(); // Reload notifications
        }
    })
    .catch(error => console.error('Error marking all notifications as read:', error));
}

// Update notification badge
function updateNotificationBadge(count = 0) {
    const badge = document.getElementById('notificationCount');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Display notifications
function displayNotifications(notifications) {
    const notificationList = document.getElementById('notificationList');
    const noNotifications = document.getElementById('noNotifications');
    
    if (!notificationList || !noNotifications) return;
    
    if (notifications && notifications.length > 0) {
        const html = notifications.map(notification => `
            <li class="dropdown-item ${notification.is_read ? '' : 'bg-light'}" 
                onclick="markNotificationRead(${notification.id}, event)">
                <div class="d-flex align-items-start">
                    <div class="me-2">
                        ${getPriorityIcon(notification.priority)}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${notification.title}</div>
                        <div class="small text-muted">${notification.message}</div>
                        <div class="small text-muted">${formatDate(notification.created_at)}</div>
                    </div>
                </div>
            </li>
        `).join('');
        notificationList.innerHTML = html;
        noNotifications.classList.add('d-none');
    } else {
        notificationList.innerHTML = '';
        noNotifications.classList.remove('d-none');
    }
}


// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    
    if (searchInput && searchResults) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 3) {
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 500);
            } else {
                searchResults.style.display = 'none';
            }
        });
        
        // Prevent dropdown from closing when clicking inside search
        searchInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});

// Load Header Interactions Fix
const headerFixScript = document.createElement('script');
headerFixScript.src = 'assets/js/header-interactions-fix.js';
headerFixScript.defer = true;
document.head.appendChild(headerFixScript);

// Load Affiliate Products JavaScript
const affiliateScript = document.createElement('script');
affiliateScript.src = 'assets/js/affiliate-products.js';
affiliateScript.defer = true;
document.head.appendChild(affiliateScript);

// Load Main JavaScript
const mainScript = document.createElement('script');
mainScript.src = 'assets/js/main.js';
mainScript.defer = true;
document.head.appendChild(mainScript);
</script>
