<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter or editor
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

// Get reporter's statistics
$reporter_id = $_SESSION['user_id'];
$my_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $reporter_id"))['count'];
$published_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $reporter_id AND status = 'published'"))['count'];
$draft_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $reporter_id AND status = 'draft'"))['count'];
$pending_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $reporter_id AND status = 'pending'"))['count'];
$total_views = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(views) as total FROM news WHERE author_id = $reporter_id"))['total'];

// Get my recent news
$my_news_query = "SELECT n.*, c.name as category_name 
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 WHERE n.author_id = $reporter_id 
                 ORDER BY n.created_at DESC LIMIT 5";
$my_recent_news = mysqli_query($conn, $my_news_query);

// Get breaking news count
$my_breaking_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $reporter_id AND is_breaking = 1"))['count'];

// Get categories for dropdown
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories = mysqli_query($conn, $categories_query);

// Get live stream status
$live_stream = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM live_stream ORDER BY id DESC LIMIT 1"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporter Dashboard - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .reporter-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .reporter-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .reporter-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .reporter-sidebar .nav-link:hover,
        .reporter-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .reporter-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            color: inherit;
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .live-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .live-online {
            background-color: #00ff00;
            animation: pulse 2s infinite;
        }
        .live-offline {
            background-color: #ff0000;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .breaking-news-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .quick-action-btn {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block reporter-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK-Live News</h4>
                        <small>Reporter Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="reporter_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_news.php">
                                <i class="fas fa-newspaper me-2"></i>My Articles
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link bg-danger text-white" href="breaking_news_reporter.php">
                                <i class="fas fa-bolt me-2"></i>Breaking News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live_reporter.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Reporting
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="media_gallery.php">
                                <i class="fas fa-images me-2"></i>Media Gallery
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_profile.php">
                                <i class="fas fa-user me-2"></i>My Profile
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 reporter-main-content">
                <!-- Header -->
                <div class="reporter-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Reporter Dashboard</h1>
                        <small>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="live-indicator <?php echo $live_stream && $live_stream['status'] == 'online' ? 'live-online' : 'live-offline'; ?>"></span>
                        <span class="me-3">Live: <?php echo $live_stream && $live_stream['status'] == 'online' ? 'ON AIR' : 'OFFLINE'; ?></span>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="reporter_profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-rocket me-2"></i>Quick Actions</h5>
                        <div class="row">
                                                        <div class="col-md-3">
                                <a href="breaking_news_reporter.php" class="quick-action-btn btn btn-danger text-white w-100">
                                    <i class="fas fa-bolt me-2"></i>Post Breaking News
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="live_reporter.php" class="quick-action-btn btn btn-success text-white w-100">
                                    <i class="fas fa-broadcast-tower me-2"></i>Start Live Report
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="media_upload.php" class="quick-action-btn btn btn-info text-white w-100">
                                    <i class="fas fa-camera me-2"></i>Upload Media
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="reporter_news.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-primary text-white">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <h3><?php echo $my_news; ?></h3>
                                <p class="text-muted mb-0">My Articles</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="reporter_news.php?status=published" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-success text-white">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3><?php echo $published_news; ?></h3>
                                <p class="text-muted mb-0">Published</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="reporter_news.php?status=draft" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-warning text-white">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h3><?php echo $draft_news; ?></h3>
                                <p class="text-muted mb-0">Drafts</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="reporter_news.php?status=breaking" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-danger text-white">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <h3><?php echo $my_breaking_news; ?></h3>
                                <p class="text-muted mb-0">Breaking News</p>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- My Recent Articles -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>My Recent Articles</h5>
                                <a href="reporter_news.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Views</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($news = mysqli_fetch_assoc($my_recent_news)): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if ($news['is_breaking']): ?>
                                                                <span class="breaking-news-badge me-2">BREAKING</span>
                                                            <?php endif; ?>
                                                            <a href="../news.php?slug=<?php echo $news['slug']; ?>" class="text-decoration-none" target="_blank">
                                                                <?php echo htmlspecialchars(substr($news['title'], 0, 40)) . '...'; ?>
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($news['category_name'] ?? 'Uncategorized'); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_class = $news['status'] == 'published' ? 'bg-success' : 
                                                                       ($news['status'] == 'draft' ? 'bg-warning' : 'bg-info');
                                                        ?>
                                                        <span class="badge <?php echo $status_class; ?>">
                                                            <?php echo ucfirst($news['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo number_format($news['views']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($news['created_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="edit-news.php?id=<?php echo $news['id']; ?>" class="btn btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="../news.php?slug=<?php echo $news['slug']; ?>" class="btn btn-outline-info" target="_blank">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                            <?php if (mysqli_num_rows($my_recent_news) == 0): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="fas fa-newspaper fa-3x mb-3 d-block"></i>
                                                        No articles yet.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Performance Stats -->
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Performance Stats</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Total Views:</span>
                                    <strong><?php echo number_format($total_views ?: 0); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Avg Views/Article:</span>
                                    <strong><?php echo $my_news > 0 ? number_format($total_views / $my_news, 1) : '0'; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Published Rate:</span>
                                    <strong><?php echo $my_news > 0 ? round(($published_news / $my_news) * 100, 1) : '0'; ?>%</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Categories</h6>
                            </div>
                            <div class="card-body">
                                <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                    <a href="reporter_news.php?category=<?php echo $category['id']; ?>" class="badge bg-light text-dark me-2 mb-2 text-decoration-none">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <!-- Quick Tips -->
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Quick Tips</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Use breaking news for urgent updates</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Add relevant categories and tags</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Upload high-quality images</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Engage with reader comments</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Keep headlines catchy and accurate</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
