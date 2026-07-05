<?php
// Fix path for includes when called from admin directory
$basePath = dirname(__DIR__) . '/';
require_once $basePath . 'config/database.php';
require_once $basePath . 'config/helpers.php';
require_once $basePath . 'includes/language_functions.php';

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

    
    <!-- Content Security Policy -->
    <meta http-equiv="Content-Security-Policy" content="
        default-src 'self';
      script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.youtube.com https://www.google.com https://www.gstatic.com https://pagead2.googlesyndication.com;
        style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com;
        font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com;
        img-src 'self' data: https: http: https://via.placeholder.com;
        connect-src 'self' https://www.googletagmanager.com https://pagead2.googlesyndication.com;
    ">
    
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PK Live News - Pakistan's Leading News Platform</title>
    
    <!-- Meta Description -->
    <meta name="description" content="PK Live News delivers breaking news, latest updates, and comprehensive coverage from Pakistan and around the world. Stay informed with real-time news, politics, sports, technology, and more.">
    
    <!-- Meta Keywords -->
    <meta name="keywords" content="Pakistan news, breaking news, latest news, politics, sports, technology, business, entertainment, Karachi, Lahore, Islamabad, world news">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PK Live News">
    <meta property="og:description" content="PK Live News delivers breaking news, latest updates, and comprehensive coverage from Pakistan and around the world.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://pk-news.com/">
    <meta property="og:image" content="https://pk-news.com/assets/images/og-image.jpg">
    <meta property="og:site_name" content="PK Live News">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PK Live News">
    <meta name="twitter:description" content="PK Live News delivers breaking news, latest updates, and comprehensive coverage from Pakistan and around the world.">
    <meta name="twitter:image" content="https://pk-news.com/assets/images/og-image.jpg">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://pk-news.com<?php echo $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Structured Data (Schema.org) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsMediaOrganization",
        "name": "PK Live News",
        "url": "https://pk-news.com",
        "logo": "https://pk-news.com/assets/images/logo.png",
        "description": "PK Live News delivers breaking news, latest updates, and comprehensive coverage from Pakistan and around the world.",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Islamabad",
            "addressCountry": "PK"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+92-XXX-XXXXXXX",
            "contactType": "customer service",
            "email": "admin@pklivenews.com"
        },
        "sameAs": [
            "https://www.facebook.com/pklivenews",
            "https://twitter.com/pklivenews"
        ]
    }
    </script>
    
    <!-- Multi-language SEO -->
    <?php if (get_site_setting('multilingual_seo', '1') == '1'): ?>
        <?php echo generate_hreflang_tags(); ?>
    <?php endif; ?>
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Consolidated PK Live News CSS (Replaces all separate CSS files) -->
    <link href="assets/css/pk-live-news-consolidated.css" rel="stylesheet">
    
    <!-- Custom CSS from System Settings -->
    <style>
        <?php echo get_system_setting('custom_css', ''); ?>
    </style>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Critical CSS for above-the-fold content -->
    <style>
        /* Critical styles for immediate rendering */
        .navbar { z-index: 1000 !important; }
        .dropdown-menu { z-index: 1050 !important; }
        .notifications-dropdown .dropdown-menu { z-index: 1055 !important; }
        .search-dropdown .dropdown-menu { z-index: 1055 !important; }
        .user-dropdown .dropdown-menu { z-index: 1055 !important; }
        .language-switcher .dropdown-menu { z-index: 1055 !important; }
        .alert.position-fixed { z-index: 1060 !important; }
        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1055 !important; }
        .toast { z-index: 1065 !important; }
        .nav-item .dropdown-menu { z-index: 1055 !important; }
    </style>
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XXXXXXXXXX');
    </script>
</head>
<body>
    <!-- Breaking News Ticker -->
    <div class="breaking-news-ticker">
        <div class="container">
            <div class="d-flex align-items-center">
                <span class="breaking-label">BREAKING</span>
                <div class="breaking-news-scroll flex-grow-1">
                    <marquee behavior="scroll" direction="left" onmouseover="this.stop()" onmouseout="this.start()">
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
    <header class="main-header">
        <div class="container">
            <div class="row align-items-center py-3">
                <div class="col-md-4 col-6">
                    <div class="logo">
                        <a href="index.php" class="text-decoration-none">
                            <h1 class="text-danger mb-0">PK-<span class="text-dark">LIVE</span> NEWS</h1>
                        </a>
                    </div>
                </div>
                <div class="col-md-8 col-6">
                    <div class="header-right justify-content-end">
                        <!-- Search Button (Mobile Optimized) -->
                        <div class="dropdown search-dropdown d-none d-md-block">
                            <button class="btn btn-outline-secondary" type="button" id="searchDropdown" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-search"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                                <li class="dropdown-header">Search News</li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form class="px-3 py-2" action="search.php" method="GET">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="q" 
                                                   placeholder="Search news..." required>
                                            <button class="btn btn-danger" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        
                        <?php if (is_logged_in()): ?>
                            <!-- Notifications (Desktop Only) -->
                            <div class="dropdown notifications-dropdown d-none d-lg-block">
                                <button class="btn btn-outline-secondary position-relative notification-btn" 
                                        type="button" id="notificationDropdown" data-bs-toggle="dropdown" 
                                        aria-expanded="false" onclick="loadNotifications()">
                                    <i class="fas fa-bell"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" 
                                          id="notificationCount" style="display: none;">0</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end notification-dropdown" 
                                    id="notificationDropdown" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
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
                            
                            <!-- User Menu -->
                            <div class="dropdown user-dropdown">
                                <button class="btn btn-outline-danger dropdown-toggle d-flex align-items-center" type="button" 
                                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php
                                    // Get user profile image
                                    $user_image = '';
                                    if (isset($_SESSION['user_id'])) {
                                        $user_id = $_SESSION['user_id'];
                                        $image_query = "SELECT image FROM users WHERE id = ?";
                                        $stmt = mysqli_prepare($conn, $image_query);
                                        mysqli_stmt_bind_param($stmt, 'i', $user_id);
                                        mysqli_stmt_execute($stmt);
                                        $result = mysqli_stmt_get_result($stmt);
                                        $user_data = mysqli_fetch_assoc($result);
                                        
                                        if ($user_data && !empty($user_data['image'])) {
                                            $user_image = htmlspecialchars($user_data['image']);
                                        }
                                    }
                                    
                                    if (!empty($user_image) && file_exists($user_image)) {
                                        echo '<img src="' . $user_image . '" alt="Profile" class="rounded-circle me-2">';
                                    } else {
                                        echo '<i class="fas fa-user-circle me-2"></i>';
                                    }
                                    ?>
                                    <span class="d-none d-md-inline">Account</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="bookmarks.php"><i class="fas fa-bookmark me-2"></i>Bookmarks</a></li>
                                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                    
                                    <!-- Reporter Dashboard Access -->
                                    <?php if (is_reporter()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Reporter Panel</h6></li>
                                        <li><a class="dropdown-item" href="admin/reporter-dashboard-enhanced.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                        <li><a class="dropdown-item" href="admin/add-news.php"><i class="fas fa-plus me-2"></i>Create Article</a></li>
                                        <li><a class="dropdown-item" href="admin/my-articles.php"><i class="fas fa-newspaper me-2"></i>My Articles</a></li>
                                    <?php endif; ?>
                                    
                                    <!-- Editor Dashboard Access -->
                                    <?php if (is_editor()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Editor Panel</h6></li>
                                        <li><a class="dropdown-item" href="admin/editor-dashboard-enhanced.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                        <li><a class="dropdown-item" href="admin/manage-news.php"><i class="fas fa-newspaper me-2"></i>Manage News</a></li>
                                        <li><a class="dropdown-item" href="admin/manage-categories-enhanced.php"><i class="fas fa-tags me-2"></i>Categories</a></li>
                                    <?php endif; ?>
                                    
                                    <!-- Admin Dashboard Access -->
                                    <?php if (is_admin()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Admin Panel</h6></li>
                                        <li><a class="dropdown-item" href="admin/admin-dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                        <li><a class="dropdown-item" href="admin/manage-users.php"><i class="fas fa-users me-2"></i>Users</a></li>
                                        <li><a class="dropdown-item" href="admin/system-settings.php"><i class="fas fa-cogs me-2"></i>Settings</a></li>
                                    <?php endif; ?>
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <!-- Login/Signup Buttons (Mobile Optimized) -->
                            <a href="login.php" class="btn btn-outline-danger d-none d-md-inline-block me-2">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                            <a href="signup.php" class="btn btn-danger d-none d-md-inline-block">
                                <i class="fas fa-user-plus me-1"></i>Sign Up
                            </a>
                            
                            <!-- Mobile Login Button -->
                            <a href="login.php" class="btn btn-outline-danger d-md-none">
                                <i class="fas fa-sign-in-alt"></i>
                            </a>
                        <?php endif; ?>
                        
                        <!-- Language Switcher (Simplified) -->
                        <div class="language-switcher">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" 
                                        type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="me-1">🌐</span>
                                    <span class="d-none d-md-inline">Language</span>
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
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Mobile Menu Toggle -->
                        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" 
                                aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-dark navbar-expand-md">
        <div class="container">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                           href="index.php">
                            <i class="fas fa-home me-2"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'live.php' ? 'active' : ''; ?>" 
                           href="live.php">
                            <i class="fas fa-tv me-2"></i>Live TV
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-th-large me-2"></i>Categories
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                            <?php
                            $cat_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC LIMIT 8";
                            $cat_result = mysqli_query($conn, $cat_query);
                            while ($category = mysqli_fetch_assoc($cat_result)) {
                                echo '<li><a class="dropdown-item" href="category.php?id=' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'weather.php' ? 'active' : ''; ?>" 
                           href="weather.php">
                            <i class="fas fa-cloud-sun me-2"></i>Weather
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" 
                           href="contact.php">
                            <i class="fas fa-envelope me-2"></i>Contact
                        </a>
                    </li>
                    
                    <!-- Mobile Only Items -->
                    <li class="nav-item d-md-none">
                        <a class="nav-link" href="search.php">
                            <i class="fas fa-search me-2"></i>Search
                        </a>
                    </li>
                    
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item d-md-none">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user-circle me-2"></i>Profile
                            </a>
                        </li>
                        <li class="nav-item d-md-none">
                            <a class="nav-link" href="bookmarks.php">
                                <i class="fas fa-bookmark me-2"></i>Bookmarks
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item d-md-none">
                            <a class="nav-link" href="signup.php">
                                <i class="fas fa-user-plus me-2"></i>Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
