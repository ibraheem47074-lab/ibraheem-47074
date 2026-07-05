<?php require_once '../config/database.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PK Live News Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 12px 20px;
            border-radius: 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,.1);
        }
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        .main-content {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            color: #fff !important;
            font-weight: bold;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
            border: 1px solid rgba(0,0,0,.125);
        }
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="navbar-brand mb-4 text-center">
                        <h4 class="text-white">PK Live News</h4>
                        <small class="text-muted">Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper"></i>
                                Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics-dashboard.php">
                                <i class="fas fa-chart-line"></i>
                                Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="website-performance.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Performance Monitor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-tags"></i>
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_languages.php">
                                <i class="fas fa-language"></i>
                                Languages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_news_multilang.php">
                                <i class="fas fa-globe"></i>
                                Multilingual News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-comments.php">
                                <i class="fas fa-comments"></i>
                                Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-ads.php">
                                <i class="fas fa-ad"></i>
                                Ads
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-affiliate-products.php">
                                <i class="fas fa-shopping-cart"></i>
                                Affiliate Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-affiliate-categories.php">
                                <i class="fas fa-tags"></i>
                                Product Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="affiliate-analytics.php">
                                <i class="fas fa-chart-line"></i>
                                Affiliate Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower"></i>
                                Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i>
                                Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo isset($page_title) ? $page_title : 'Admin Dashboard'; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.open('../index.php', '_blank')">
                                <i class="fas fa-external-link-alt me-1"></i>View Site
                            </button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
