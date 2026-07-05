<?php
// Fix path for includes when called from admin directory
$basePath = dirname(__DIR__) . '/';

// Check if database tables exist first
$database_ready = false;
if (file_exists($basePath . 'config/database.php')) {
    require_once $basePath . 'config/database.php';
    if ($conn) {
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'languages'");
        $database_ready = mysqli_num_rows($table_check) > 0;
    }
}

if ($database_ready) {
    // Use full language functions
    require_once $basePath . 'includes/language_functions.php';
    $current_lang = get_current_language();
    $get_active_languages = 'get_active_languages';
    $get_setting = 'get_setting';
    $get_language_url = 'get_language_url';
    $generate_hreflang_tags = 'generate_hreflang_tags';
    $get_news_title = 'get_news_title';
    $get_news_content = 'get_news_content';
} else {
    // Use minimal language functions
    require_once $basePath . 'includes/language_functions_minimal.php';
    $current_lang = get_current_language_minimal();
    $get_active_languages = 'get_active_languages_minimal';
    $get_setting = 'get_setting_minimal';
    $get_language_url = 'get_language_url_minimal';
    $generate_hreflang_tags = 'generate_hreflang_tags_minimal';
    $get_news_title = 'get_news_title_minimal';
    $get_news_content = 'get_news_content_minimal';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PK Live News</title>
    
     <!-- Google AdSense Verification -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3649343603124512" crossorigin="anonymous"></script>

    
    <!-- Multi-language SEO -->
    <?php if (get_site_setting('multilingual_seo', '1') == '1'): ?>
        <?php echo generate_hreflang_tags(); ?>
    <?php endif; ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo $database_ready ? '' : '../'; ?>assets/css/style.css" rel="stylesheet">
    <!-- Live TV CSS -->
    <link href="<?php echo $database_ready ? '' : '../'; ?>assets/css/live-tv.css" rel="stylesheet">
    <!-- Heat Map CSS -->
    <link href="<?php echo $database_ready ? '' : '../'; ?>assets/css/heatmap.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $database_ready ? '' : '../'; ?>assets/images/favicon.ico">
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
                        if ($database_ready) {
                            $breaking_query = "SELECT n.*, c.name as category_name FROM news n 
                                             LEFT JOIN categories c ON n.category_id = c.id 
                                             WHERE n.is_breaking = 1 AND n.status = 'published' 
                                             ORDER BY n.published_at DESC LIMIT 5";
                            $breaking_result = mysqli_query($conn, $breaking_query);
                            while ($breaking = mysqli_fetch_assoc($breaking_result)) {
                                $title = $get_news_title($breaking);
                                echo '<a href="' . ($database_ready ? '' : '../') . 'news.php?slug=' . $breaking['slug'] . '" class="text-white text-decoration-none me-4">' . htmlspecialchars($title) . '</a>';
                            }
                        } else {
                            echo '<a href="#" class="text-white text-decoration-none me-4">Breaking news will appear here after database setup</a>';
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
                        <a href="<?php echo $database_ready ? '' : '../'; ?>index.php" class="text-decoration-none">
                            <h1 class="text-danger mb-0">PK <span class="text-dark">LIVE</span> NEWS</h1>
                        </a>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="header-right d-flex align-items-center justify-content-end">
                        <?php if ($database_ready && function_exists('is_logged_in') && is_logged_in()): ?>
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
                                <button class="btn btn-outline-danger dropdown-toggle" type="button" 
                                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2"></i>My Profile</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-bookmark me-2"></i>My Bookmarks</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                    <?php if ($database_ready && function_exists('is_admin') && is_admin()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Admin Panel</h6></li>
                                        <li><a class="dropdown-item" href="<?php echo $database_ready ? '' : '../'; ?>admin/manage-drafts.php"><i class="fas fa-edit me-2"></i>Manage Drafts</a></li>
                                        <li><a class="dropdown-item" href="<?php echo $database_ready ? '' : '../'; ?>admin/add-news.php"><i class="fas fa-plus me-2"></i>Add News</a></li>
                                        <li><a class="dropdown-item" href="<?php echo $database_ready ? '' : '../'; ?>admin/categories.php"><i class="fas fa-tags me-2"></i>Categories</a></li>
                                        <li><a class="dropdown-item" href="<?php echo $database_ready ? '' : '../'; ?>admin/users.php"><i class="fas fa-users me-2"></i>Users</a></li>
                                        <li><a class="dropdown-item" href="<?php echo $database_ready ? '' : '../'; ?>admin/settings.php"><i class="fas fa-cogs me-2"></i>Settings</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="<?php echo $database_ready ? '' : '../'; ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <!-- Login/Signup Buttons -->
                            <a href="<?php echo $database_ready ? '' : '../'; ?>login.php" class="btn btn-outline-danger me-2">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                            <a href="<?php echo $database_ready ? '' : '../'; ?>signup.php" class="btn btn-danger">
                                <i class="fas fa-user-plus me-1"></i>Sign Up
                            </a>
                        <?php endif; ?>
                        
                        <!-- Language Switcher -->
                        <?php include $basePath . 'components/language_switcher_minimal.php'; ?>
                        
                        <!-- Search -->
                        <form action="<?php echo $database_ready ? '' : '../'; ?>search.php" method="GET" class="search-form d-flex ms-3">
                            <input type="text" name="q" class="form-control me-2" placeholder="Search news...">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
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
                        <a class="nav-link" href="<?php echo $database_ready ? '' : '../'; ?>index.php">Home</a>
                    </li>
                    <?php
                    if ($database_ready) {
                        // Load categories from database
                        $cat_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC LIMIT 8";
                        $cat_result = mysqli_query($conn, $cat_query);
                        while ($category = mysqli_fetch_assoc($cat_result)) {
                            echo '<li class="nav-item">';
                            echo '<a class="nav-link" href="' . ($database_ready ? '' : '../') . 'category.php?slug=' . $category['slug'] . '">' . htmlspecialchars($category['name']) . '</a>';
                            echo '</li>';
                        }
                    } else {
                        // Show placeholder categories
                        $placeholder_cats = ['Politics', 'Sports', 'Technology', 'Business', 'Entertainment', 'World', 'Health', 'Science'];
                        foreach ($placeholder_cats as $cat) {
                            echo '<li class="nav-item">';
                            echo '<a class="nav-link" href="#">' . $cat . '</a>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>
