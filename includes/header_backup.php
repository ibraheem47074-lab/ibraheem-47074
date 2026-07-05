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
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Live TV CSS -->
    <link href="assets/css/live-tv.css" rel="stylesheet">
    <!-- Heat Map CSS -->
    <link href="assets/css/heatmap.css" rel="stylesheet">
    <!-- Weather CSS -->
    <link href="assets/css/weather.css" rel="stylesheet">
    <!-- Image Lightbox CSS -->
    <link href="assets/css/image-lightbox.css" rel="stylesheet">
    <!-- Video Lightbox CSS -->
    <link href="assets/css/video-lightbox.css" rel="stylesheet">
    <!-- Affiliate Products CSS -->
    <link href="assets/css/affiliate-products.css" rel="stylesheet">
    
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
        
        /* Fix for any overlapping elements */
        .dropdown.show .dropdown-menu {
            z-index: 1055 !important;
        }
    </style>
    
    <!-- Custom CSS from System Settings -->
    <style>
        <?php echo get_system_setting('custom_css', ''); ?>
    </style>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'GA_MEASUREMENT_ID');
    </script>
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
    <header class="main-header bg-white shadow-sm">
        <div class="container">
            <div class="row align-items-center py-3">
                <div class="col-md-4">
                    <div class="logo">
                        <a href="index.php" class="text-decoration-none">
                            <h1 class="text-danger mb-0">PK-<span class="text-dark">LIVE</span> NEWS</h1>
                        </a>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="header-right d-flex align-items-center justify-content-end">
                        <?php if (is_logged_in()): ?>
                            <!-- Notifications -->
                            <div class="dropdown me-3">
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
                            <div class="dropdown">
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
                                        echo '<img src="' . $user_image . '" alt="Profile" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">';
                                    } else {
                                        echo '<i class="fas fa-user-circle me-2"></i>';
                                    }
                                    ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="bookmarks.php"><i class="fas fa-bookmark me-2"></i>Bookmarks</a></li>
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
                                        <li><a class="dropdown-item" href="admin/manage-news.php"><i class="fas fa-newspaper me-2"></i>Manage News</a></li>
                                        <li><a class="dropdown-item" href="admin/editor-comments.php"><i class="fas fa-comments me-2"></i>Manage Comments</a></li>
                                        <li><a class="dropdown-item" href="admin/editor-polls.php"><i class="fas fa-poll me-2"></i>Manage Polls</a></li>
                                        <li><a class="dropdown-item" href="admin/editor-statistics.php"><i class="fas fa-chart-line me-2"></i>Statistics</a></li>
                                        <li><a class="dropdown-item" href="admin/manage-categories.php"><i class="fas fa-tags me-2"></i>Categories</a></li>
                                        <li><a class="dropdown-item" href="admin/editor-profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
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
                            <a href="login.php" class="btn btn-outline-danger me-2">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                            <a href="signup.php" class="btn btn-danger">
                                <i class="fas fa-user-plus me-1"></i>Sign Up
                            </a>
                        <?php endif; ?>
                        
                        <!-- Language Switcher -->
                        <div class="language-switcher">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" 
                                        type="button" 
                                        id="languageDropdown" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false">
                                    <span class="me-1">🌐</span>
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
                        
                        <!-- Search -->
                        <div class="search-dropdown dropdown">
                            <button class="btn btn-outline-danger dropdown-toggle" type="button" id="searchDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-search"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 350px;">
                                <li>
                                    <!-- Search Type Selector -->
                                    <div class="mb-3">
                                        <label class="form-label small text-muted">Search Type:</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="searchType" id="newsSearch" value="news" checked>
                                            <label class="btn btn-outline-danger" for="newsSearch">
                                                <i class="fas fa-newspaper me-1"></i>News
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="searchType" id="weatherSearch" value="weather">
                                            <label class="btn btn-outline-warning" for="weatherSearch">
                                                <i class="fas fa-cloud-sun me-1"></i>Weather
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- News Search Form -->
                                    <form id="newsSearchForm" action="search.php" method="GET">
                                        <div class="input-group">
                                            <input type="text" name="q" class="form-control" placeholder="Search news...">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <!-- Weather Search Form (Hidden by default) -->
                                    <form id="weatherSearchForm" action="weather.php" method="GET" style="display: none;">
                                        <div class="input-group">
                                            <input type="text" name="city" class="form-control" placeholder="Search weather...">
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-cloud-sun"></i>
                                            </button>
                                        </div>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

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
                      <?php
                    $cat_query = "SELECT * FROM categories WHERE status = 'active' AND slug NOT IN ('cnn-politics', 'cnn-world', 'cnn-international', 'cnn-environment', 'cnn-us-news', 'cnn-media', 'cnn-business', 'cnn-technology', 'cnn-entertainment', 'cnn-sports', 'ary-world', 'ary-politics', 'ary-business', 'ary-technology', 'ary-entertainment', 'ary-sports', 'bbc-world', 'bbc-politics', 'bbc-business', 'bbc-technology', 'bbc-entertainment', 'bbc-sports') AND name NOT LIKE '%CNN International%' AND name NOT LIKE '%BBC World News%' AND name NOT LIKE '%ARY%' ORDER BY name ASC";
                    $cat_result = mysqli_query($conn, $cat_query);
                    while ($category = mysqli_fetch_assoc($cat_result)) {
                        echo '<li class="nav-item">';
                        echo '<a class="nav-link d-flex align-items-center" href="category.php?slug=' . $category['slug'] . '">';
                        echo '<i class="fas fa-tag me-2"></i>';
                        echo '<span>' . htmlspecialchars($category['name']) . '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    ?>
                   
                  
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="live.php">
                            <i class="fas fa-tv me-2"></i>
                            <span>Live TV</span>
                        </a>
                    </li>
                    
                    <?php
                    // Check if affiliate tables exist and show products link
                    $affiliate_tables_exist = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
                    if (mysqli_num_rows($affiliate_tables_exist) > 0) {
                    ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center text-primary fw-bold" href="products.php">
                            <i class="fas fa-shopping-cart me-2"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <?php } ?>
                    
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center text-warning fw-bold" href="weather.php">
                            <i class="fas fa-cloud-sun me-2"></i>
                            <span>Weather</span>
                        </a>
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
                         <!-- Jobs Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link d-flex align-items-center dropdown-toggle" href="#" id="jobsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-briefcase me-2"></i>
                            <span>Jobs</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="jobsDropdown">
                            <li><a class="dropdown-item" href="jobs.php">
                                <i class="fas fa-list me-2"></i>All Jobs
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Job Categories</h6></li>
                            <?php
                            $job_categories_query = "SELECT * FROM categories WHERE slug = 'jobs' OR parent_id IN (SELECT id FROM categories WHERE slug = 'jobs') AND status = 'active' ORDER BY name ASC";
                            $job_cats_result = mysqli_query($conn, $job_categories_query);
                            while ($job_cat = mysqli_fetch_assoc($job_cats_result)) {
                                echo '<li><a class="dropdown-item" href="jobs.php?category=' . $job_cat['slug'] . '">';
                                if ($job_cat['slug'] == 'jobs') {
                                    echo '<i class="fas fa-briefcase me-2"></i>' . htmlspecialchars($job_cat['name']);
                                } else {
                                    echo '<i class="fas fa-angle-right me-2"></i>' . htmlspecialchars($job_cat['name']);
                                }
                                echo '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                  
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

// Mark all notifications as read
function markAllNotificationsRead() {
    fetch('api/notifications.php?action=mark_all_read', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notificationCount = 0;
            updateNotificationBadge();
            loadNotifications(); // Reload the list
        }
    })
    .catch(error => console.error('Error marking all notifications as read:', error));
}

// Update notification badge
function updateNotificationBadge() {
    const notificationBadge = document.getElementById('notificationCount');
    if (notificationCount > 0) {
        notificationBadge.textContent = notificationCount > 99 ? '99+' : notificationCount;
        notificationBadge.style.display = 'inline-block';
    } else {
        notificationBadge.style.display = 'none';
    }
}

// Auto-refresh notifications every 30 seconds
setInterval(() => {
    if (document.getElementById('notificationCount')) {
        fetch('api/notifications.php?action=get')
            .then(response => response.json())
            .then(data => {
                notificationCount = data.unread_count;
                updateNotificationBadge();
            })
            .catch(error => console.error('Error refreshing notifications:', error));
    }
}, 30000);

// Load notification count on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('notificationCount')) {
        fetch('api/notifications.php?action=get')
            .then(response => response.json())
            .then(data => {
                notificationCount = data.unread_count;
                updateNotificationBadge();
            })
            .catch(error => console.error('Error loading notification count:', error));
    }
    
    // Initialize search type switcher
    initializeSearchTypeSwitcher();
});

// Search Type Switcher Functionality
function initializeSearchTypeSwitcher() {
    const newsSearchRadio = document.getElementById('newsSearch');
    const weatherSearchRadio = document.getElementById('weatherSearch');
    const newsSearchForm = document.getElementById('newsSearchForm');
    const weatherSearchForm = document.getElementById('weatherSearchForm');
    
    if (newsSearchRadio && weatherSearchRadio && newsSearchForm && weatherSearchForm) {
        newsSearchRadio.addEventListener('change', function() {
            if (this.checked) {
                newsSearchForm.style.display = 'block';
                weatherSearchForm.style.display = 'none';
            }
        });
        
        weatherSearchRadio.addEventListener('change', function() {
            if (this.checked) {
                newsSearchForm.style.display = 'none';
                weatherSearchForm.style.display = 'block';
            }
        });
    }
}

// Load Push Notification System
if ('serviceWorker' in navigator) {
    const script = document.createElement('script');
    script.src = 'assets/js/push-notifications.js';
    script.defer = true;
    document.head.appendChild(script);
}

// Load Affiliate Products JavaScript
const affiliateScript = document.createElement('script');
affiliateScript.src = 'assets/js/affiliate-products.js';
affiliateScript.defer = true;
document.head.appendChild(affiliateScript);
</script>
