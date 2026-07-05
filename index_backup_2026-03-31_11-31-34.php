<?php
require_once 'config/database.php';
require_once 'config/weather.php';
require_once 'includes/language_functions.php';

$page_title = 'Home';
$current_lang = get_current_language();

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15; // Show 15 posts per page
$offset = ($page - 1) * $per_page;

// Function to get source icon based on source name
function getSourceIcon($sourceName) {
    $sourceIcons = [
        'PK Live News' => '<i class="fas fa-home text-primary" title="PK Live News"></i>',
        'BBC News' => '<i class="fas fa-broadcast-tower text-primary" title="BBC News"></i>',
        'CNN' => '<i class="fas fa-satellite-dish text-danger" title="CNN"></i>',
        'ARY News' => '<i class="fas fa-tv text-success" title="ARY News"></i>',
        'Reuters' => '<i class="fas fa-newspaper text-info" title="Reuters"></i>',
        'Al Jazeera' => '<i class="fas fa-globe-africa text-warning" title="Al Jazeera"></i>',
        'Fox News' => '<i class="fas fa-fox text-orange" title="Fox News"></i>',
        'Associated Press' => '<i class="fas fa-press-release text-primary" title="Associated Press"></i>',
        'AP News' => '<i class="fas fa-press-release text-primary" title="AP News"></i>',
        'Bloomberg' => '<i class="fas fa-chart-line text-success" title="Bloomberg"></i>',
        'The Guardian' => '<i class="fas fa-shield-alt text-info" title="The Guardian"></i>',
        'Washington Post' => '<i class="fas fa-newspaper text-dark" title="Washington Post"></i>',
        'New York Times' => '<i class="fas fa-times-circle text-secondary" title="New York Times"></i>',
        'NBC News' => '<i class="fas fa-peacock text-info" title="NBC News"></i>',
        'CBS News' => '<i class="fas fa-eye text-primary" title="CBS News"></i>',
        'ABC News' => '<i class="fas fa-tv text-danger" title="ABC News"></i>',
        'CNBC' => '<i class="fas fa-chart-pie text-success" title="CNBC"></i>',
        'WSJ' => '<i class="fas fa-building text-dark" title="Wall Street Journal"></i>',
        'Wall Street Journal' => '<i class="fas fa-building text-dark" title="Wall Street Journal"></i>',
        'USA Today' => '<i class="fas fa-flag-usa text-primary" title="USA Today"></i>',
        'NPR' => '<i class="fas fa-radio text-info" title="NPR"></i>',
        'PBS' => '<i class="fas fa-tv text-secondary" title="PBS"></i>',
        'Sky News' => '<i class="fas fa-cloud text-info" title="Sky News"></i>',
        'EuroNews' => '<i class="fas fa-globe-europe text-primary" title="EuroNews"></i>',
        'Deutsche Welle' => '<i class="fas fa-microphone text-warning" title="Deutsche Welle"></i>',
        'France 24' => '<i class="fas fa-flag text-primary" title="France 24"></i>',
        'RT' => '<i class="fas fa-satellite text-red" title="RT"></i>',
        'CGTN' => '<i class="fas fa-globe-asia text-red" title="CGTN"></i>',
        'NDTV' => '<i class="fas fa-tv text-orange" title="NDTV"></i>',
        'Times of India' => '<i class="fas fa-newspaper text-warning" title="Times of India"></i>',
        'Hindustan Times' => '<i class="fas fa-newspaper text-danger" title="Hindustan Times"></i>',
        'Dawn' => '<i class="fas fa-sun text-warning" title="Dawn"></i>',
        'Geo News' => '<i class="fas fa-globe text-primary" title="Geo News"></i>',
        'Express Tribune' => '<i class="fas fa-newspaper text-info" title="Express Tribune"></i>',
        'default' => '<i class="fas fa-rss text-secondary" title="News Source"></i>'
    ];
    
    return $sourceIcons[$sourceName] ?? $sourceIcons['default'];
}

// Function to detect news source from URL
function detectNewsSource($sourceUrl) {
    if (empty($sourceUrl)) return null;
    
    $sourcePatterns = [
        'BBC News' => ['bbc.co.uk', 'bbc.com'],
        'CNN' => ['cnn.com'],
        'ARY News' => ['arynews.tv', 'arydigital.tv'],
        'Reuters' => ['reuters.com'],
        'Al Jazeera' => ['aljazeera.com'],
        'Fox News' => ['foxnews.com'],
        'Associated Press' => ['apnews.com'],
        'AP News' => ['ap.org'],
        'Bloomberg' => ['bloomberg.com'],
        'The Guardian' => ['theguardian.com'],
        'Washington Post' => ['washingtonpost.com'],
        'New York Times' => ['nytimes.com'],
        'NBC News' => ['nbcnews.com'],
        'CBS News' => ['cbsnews.com'],
        'ABC News' => ['abcnews.go.com'],
        'CNBC' => ['cnbc.com'],
        'Wall Street Journal' => ['wsj.com'],
        'USA Today' => ['usatoday.com'],
        'NPR' => ['npr.org'],
        'PBS' => ['pbs.org'],
        'Sky News' => ['news.sky.com'],
        'EuroNews' => ['euronews.com'],
        'Deutsche Welle' => ['dw.com'],
        'France 24' => ['france24.com'],
        'RT' => ['rt.com'],
        'CGTN' => ['cgtn.com'],
        'NDTV' => ['ndtv.com'],
        'Times of India' => ['timesofindia.indiatimes.com'],
        'Hindustan Times' => ['hindustantimes.com'],
        'Dawn' => ['dawn.com'],
        'Geo News' => ['geo.tv'],
        'Express Tribune' => ['tribune.com.pk']
    ];
    
    foreach ($sourcePatterns as $sourceName => $domains) {
        foreach ($domains as $domain) {
            if (strpos(strtolower($sourceUrl), $domain) !== false) {
                return $sourceName;
            }
        }
    }
    
    return null;
}

// Get featured news
$featured_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                  (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                  (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count,
                  CASE 
                    WHEN n.source_url LIKE '%bbc.co.uk%' OR n.source_url LIKE '%bbc.com%' THEN 'BBC News'
                    WHEN n.source_url LIKE '%cnn.com%' THEN 'CNN'
                    WHEN n.source_url LIKE '%arynews.tv%' OR n.source_url LIKE '%arydigital.tv%' THEN 'ARY News'
                    WHEN n.source_url LIKE '%reuters.com%' THEN 'Reuters'
                    WHEN n.source_url LIKE '%aljazeera.com%' THEN 'Al Jazeera'
                    WHEN n.source_url LIKE '%foxnews.com%' THEN 'Fox News'
                    WHEN n.source_url LIKE '%apnews.com%' OR n.source_url LIKE '%ap.org%' THEN 'Associated Press'
                    WHEN n.source_url LIKE '%bloomberg.com%' THEN 'Bloomberg'
                    WHEN n.source_url LIKE '%theguardian.com%' THEN 'The Guardian'
                    WHEN n.source_url LIKE '%washingtonpost.com%' THEN 'Washington Post'
                    WHEN n.source_url LIKE '%nytimes.com%' THEN 'New York Times'
                    WHEN n.source_url LIKE '%nbcnews.com%' THEN 'NBC News'
                    WHEN n.source_url LIKE '%cbsnews.com%' THEN 'CBS News'
                    WHEN n.source_url LIKE '%abcnews.go.com%' THEN 'ABC News'
                    WHEN n.source_url LIKE '%cnbc.com%' THEN 'CNBC'
                    WHEN n.source_url LIKE '%wsj.com%' THEN 'Wall Street Journal'
                    WHEN n.source_url LIKE '%usatoday.com%' THEN 'USA Today'
                    WHEN n.source_url LIKE '%npr.org%' THEN 'NPR'
                    WHEN n.source_url LIKE '%pbs.org%' THEN 'PBS'
                    WHEN n.source_url LIKE '%news.sky.com%' THEN 'Sky News'
                    WHEN n.source_url LIKE '%euronews.com%' THEN 'EuroNews'
                    WHEN n.source_url LIKE '%dw.com%' THEN 'Deutsche Welle'
                    WHEN n.source_url LIKE '%france24.com%' THEN 'France 24'
                    WHEN n.source_url LIKE '%rt.com%' THEN 'RT'
                    WHEN n.source_url LIKE '%cgtn.com%' THEN 'CGTN'
                    WHEN n.source_url LIKE '%ndtv.com%' THEN 'NDTV'
                    WHEN n.source_url LIKE '%timesofindia.indiatimes.com%' THEN 'Times of India'
                    WHEN n.source_url LIKE '%hindustantimes.com%' THEN 'Hindustan Times'
                    WHEN n.source_url LIKE '%dawn.com%' THEN 'Dawn'
                    WHEN n.source_url LIKE '%geo.tv%' THEN 'Geo News'
                    WHEN n.source_url LIKE '%tribune.com.pk%' THEN 'Express Tribune'
                    ELSE NULL
                  END as source_name
                  FROM news n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  LEFT JOIN users u ON n.author_id = u.id 
                  WHERE n.status = 'featured' AND n.published_at <= NOW() 
                  ORDER BY n.published_at DESC LIMIT 3";
$featured_result = mysqli_query($conn, $featured_query);

// Get latest news (including scraped news) - Ordered by real post time
$latest_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                CASE 
                    WHEN n.image IS NOT NULL AND n.image != '' THEN 3
                    WHEN n.video_url IS NOT NULL AND n.video_url != '' THEN 2
                    ELSE 1
                END as media_priority,
                CASE 
                    WHEN n.source_url IS NOT NULL AND n.source_url != '' THEN 'external'
                    ELSE 'internal'
                END as news_type,
                CASE 
                    WHEN COALESCE(n.published_at, n.created_at) >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'new'
                    WHEN COALESCE(n.published_at, n.created_at) >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'recent'
                    ELSE 'older'
                END as time_status,
                n.news_type as article_type,
                (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count,
                CASE 
                    WHEN n.source_url LIKE '%bbc.co.uk%' OR n.source_url LIKE '%bbc.com%' THEN 'BBC News'
                    WHEN n.source_url LIKE '%cnn.com%' THEN 'CNN'
                    WHEN n.source_url LIKE '%arynews.tv%' OR n.source_url LIKE '%arydigital.tv%' THEN 'ARY News'
                    WHEN n.source_url LIKE '%reuters.com%' THEN 'Reuters'
                    WHEN n.source_url LIKE '%aljazeera.com%' THEN 'Al Jazeera'
                    WHEN n.source_url LIKE '%foxnews.com%' THEN 'Fox News'
                    WHEN n.source_url LIKE '%apnews.com%' OR n.source_url LIKE '%ap.org%' THEN 'Associated Press'
                    WHEN n.source_url LIKE '%bloomberg.com%' THEN 'Bloomberg'
                    WHEN n.source_url LIKE '%theguardian.com%' THEN 'The Guardian'
                    WHEN n.source_url LIKE '%washingtonpost.com%' THEN 'Washington Post'
                    WHEN n.source_url LIKE '%nytimes.com%' THEN 'New York Times'
                    WHEN n.source_url LIKE '%nbcnews.com%' THEN 'NBC News'
                    WHEN n.source_url LIKE '%cbsnews.com%' THEN 'CBS News'
                    WHEN n.source_url LIKE '%abcnews.go.com%' THEN 'ABC News'
                    WHEN n.source_url LIKE '%cnbc.com%' THEN 'CNBC'
                    WHEN n.source_url LIKE '%wsj.com%' THEN 'Wall Street Journal'
                    WHEN n.source_url LIKE '%usatoday.com%' THEN 'USA Today'
                    WHEN n.source_url LIKE '%npr.org%' THEN 'NPR'
                    WHEN n.source_url LIKE '%pbs.org%' THEN 'PBS'
                    WHEN n.source_url LIKE '%news.sky.com%' THEN 'Sky News'
                    WHEN n.source_url LIKE '%euronews.com%' THEN 'EuroNews'
                    WHEN n.source_url LIKE '%dw.com%' THEN 'Deutsche Welle'
                    WHEN n.source_url LIKE '%france24.com%' THEN 'France 24'
                    WHEN n.source_url LIKE '%rt.com%' THEN 'RT'
                    WHEN n.source_url LIKE '%cgtn.com%' THEN 'CGTN'
                    WHEN n.source_url LIKE '%ndtv.com%' THEN 'NDTV'
                    WHEN n.source_url LIKE '%timesofindia.indiatimes.com%' THEN 'Times of India'
                    WHEN n.source_url LIKE '%hindustantimes.com%' THEN 'Hindustan Times'
                    WHEN n.source_url LIKE '%dawn.com%' THEN 'Dawn'
                    WHEN n.source_url LIKE '%geo.tv%' THEN 'Geo News'
                    WHEN n.source_url LIKE '%tribune.com.pk%' THEN 'Express Tribune'
                    ELSE 'PK Live News'
                END as source_name,
                COALESCE(n.published_at, n.created_at) as real_post_time
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                LEFT JOIN users u ON n.author_id = u.id 
                WHERE n.status = 'published' AND (n.published_at <= NOW() OR n.published_at IS NULL) 
                ORDER BY real_post_time DESC, media_priority DESC LIMIT ? OFFSET ?";
$latest_stmt = mysqli_prepare($conn, $latest_query);
mysqli_stmt_bind_param($latest_stmt, 'ii', $per_page, $offset);
mysqli_stmt_execute($latest_stmt);
$latest_result = mysqli_stmt_get_result($latest_stmt);

// Get total news count for pagination
$count_query = "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND published_at <= NOW()";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Get trending news (most viewed in last 7 days) - Enhanced source detection
$trending_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                  (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                  (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count,
                  CASE 
                    WHEN n.source_url LIKE '%bbc.co.uk%' OR n.source_url LIKE '%bbc.com%' THEN 'BBC News'
                    WHEN n.source_url LIKE '%cnn.com%' THEN 'CNN'
                    WHEN n.source_url LIKE '%arynews.tv%' OR n.source_url LIKE '%arydigital.tv%' THEN 'ARY News'
                    WHEN n.source_url LIKE '%reuters.com%' THEN 'Reuters'
                    WHEN n.source_url LIKE '%aljazeera.com%' THEN 'Al Jazeera'
                    WHEN n.source_url LIKE '%foxnews.com%' THEN 'Fox News'
                    WHEN n.source_url LIKE '%apnews.com%' OR n.source_url LIKE '%ap.org%' THEN 'Associated Press'
                    WHEN n.source_url LIKE '%bloomberg.com%' THEN 'Bloomberg'
                    WHEN n.source_url LIKE '%theguardian.com%' THEN 'The Guardian'
                    WHEN n.source_url LIKE '%washingtonpost.com%' THEN 'Washington Post'
                    WHEN n.source_url LIKE '%nytimes.com%' THEN 'New York Times'
                    WHEN n.source_url LIKE '%nbcnews.com%' THEN 'NBC News'
                    WHEN n.source_url LIKE '%cbsnews.com%' THEN 'CBS News'
                    WHEN n.source_url LIKE '%abcnews.go.com%' THEN 'ABC News'
                    WHEN n.source_url LIKE '%cnbc.com%' THEN 'CNBC'
                    WHEN n.source_url LIKE '%wsj.com%' THEN 'Wall Street Journal'
                    WHEN n.source_url LIKE '%usatoday.com%' THEN 'USA Today'
                    WHEN n.source_url LIKE '%npr.org%' THEN 'NPR'
                    WHEN n.source_url LIKE '%pbs.org%' THEN 'PBS'
                    WHEN n.source_url LIKE '%news.sky.com%' THEN 'Sky News'
                    WHEN n.source_url LIKE '%euronews.com%' THEN 'EuroNews'
                    WHEN n.source_url LIKE '%dw.com%' THEN 'Deutsche Welle'
                    WHEN n.source_url LIKE '%france24.com%' THEN 'France 24'
                    WHEN n.source_url LIKE '%rt.com%' THEN 'RT'
                    WHEN n.source_url LIKE '%cgtn.com%' THEN 'CGTN'
                    WHEN n.source_url LIKE '%ndtv.com%' THEN 'NDTV'
                    WHEN n.source_url LIKE '%timesofindia.indiatimes.com%' THEN 'Times of India'
                    WHEN n.source_url LIKE '%hindustantimes.com%' THEN 'Hindustan Times'
                    WHEN n.source_url LIKE '%dawn.com%' THEN 'Dawn'
                    WHEN n.source_url LIKE '%geo.tv%' THEN 'Geo News'
                    WHEN n.source_url LIKE '%tribune.com.pk%' THEN 'Express Tribune'
                    ELSE NULL
                  END as source_name,
                  COALESCE(n.published_at, n.created_at) as real_post_time
                  FROM news n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  LEFT JOIN users u ON n.author_id = u.id 
                  WHERE n.status = 'published' AND COALESCE(n.published_at, n.created_at) >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                  ORDER BY n.views DESC, real_post_time DESC LIMIT 5";
$trending_result = mysqli_query($conn, $trending_query);

// Get categories for sidebar (excluding BBC and CNN categories)
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' AND slug NOT IN ('bbc-world', 'cnn-international') ORDER BY name ASC");

// Get live stream info
$live_stream = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM live_stream WHERE status = 'online' ORDER BY id DESC LIMIT 1"));

// Get active poll
$active_poll = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT p.*, po.option_text, po.votes, po.id as option_id 
     FROM polls p 
     LEFT JOIN poll_options po ON p.id = po.poll_id 
     WHERE p.status = 'active' AND (p.ends_at IS NULL OR p.ends_at > NOW()) 
     ORDER BY p.id DESC, po.id ASC LIMIT 1"
));

// Get poll options if active poll exists
$poll_options = [];
if ($active_poll) {
    $poll_id = $active_poll['id'];
    $options_query = "SELECT * FROM poll_options WHERE poll_id = $poll_id ORDER BY id ASC";
    $options_result = mysqli_query($conn, $options_query);
    while ($option = mysqli_fetch_assoc($options_result)) {
        $poll_options[] = $option;
    }
}

// Get latest news editions (check if table exists first)
$editions_result = null;
$editions_query = "SELECT ne.*, n.title as news_title, n.slug as news_slug, n.image as news_image,
                  c.name as category_name, ec.name as edition_category_name, ec.color as edition_color, ec.icon as edition_icon
                  FROM news_editions ne
                  LEFT JOIN news n ON ne.news_id = n.id
                  LEFT JOIN categories c ON n.category_id = c.id
                  LEFT JOIN edition_categories ec ON ne.edition_type = ec.slug
                  WHERE ne.status = 'published' AND ne.published_at <= NOW()
                  ORDER BY ne.priority DESC, ne.published_at DESC LIMIT 6";

// Check if news_editions table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_editions'");
if (mysqli_num_rows($table_check) > 0) {
    $editions_result = mysqli_query($conn, $editions_query);
}

// Get breaking news editions (check if table exists first)
$breaking_editions_result = null;
$breaking_editions_query = "SELECT ne.*, n.title as news_title, n.slug as news_slug, n.image as news_image,
                            c.name as category_name, ec.name as edition_category_name, ec.color as edition_color, ec.icon as edition_icon
                            FROM news_editions ne
                            LEFT JOIN news n ON ne.news_id = n.id
                            LEFT JOIN categories c ON n.category_id = c.id
                            LEFT JOIN edition_categories ec ON ne.edition_type = ec.slug
                            WHERE ne.status = 'published' AND ne.edition_type = 'breaking' AND ne.published_at <= NOW()
                            ORDER BY ne.published_at DESC LIMIT 3";

if (mysqli_num_rows($table_check) > 0) {
    $breaking_editions_result = mysqli_query($conn, $breaking_editions_query);
}

// Get weather data for homepage
$weatherData = null;
$defaultCity = getUserLocationCity();
$weatherData = getWeatherData($defaultCity, 'metric');
if ($weatherData) {
    $weatherData = formatWeatherData($weatherData);
}

// Update views for news articles
if (isset($_GET['slug'])) {
    $slug = clean_input($_GET['slug']);
    mysqli_query($conn, "UPDATE news SET views = views + 1 WHERE slug = '$slug'");
}
?>

<?php include 'includes/header.php'; ?>

<!-- Quick Access Dashboard for Logged-in Users -->
<?php if (is_logged_in() && (is_reporter() || is_editor() || is_admin())): ?>
<section class="quick-access-dashboard py-3 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-<?php echo is_admin() ? 'danger' : (is_editor() ? 'primary' : 'warning'); ?> fs-6">
                            <?php echo ucfirst($_SESSION['user_role']); ?>
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h5>
                        <small class="text-muted">
                            <?php if (is_reporter()): ?>
                                Create articles, track submissions, and manage your content
                            <?php elseif (is_editor()): ?>
                                Review submissions, manage content, and moderate discussions
                            <?php elseif (is_admin()): ?>
                                Full system access and administrative controls
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group">
                    <?php if (is_reporter()): ?>
                                                <a href="admin/reporter-dashboard-enhanced.php" class="btn btn-warning btn-sm">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                        <a href="admin/add-news.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Create Article
                        </a>
                    <?php elseif (is_editor()): ?>
                                                <a href="admin/editor-dashboard-enhanced.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                        <a href="admin/manage-news.php?status=pending" class="btn btn-warning btn-sm">
                            <i class="fas fa-clock me-1"></i>Review Pending
                        </a>
                    <?php elseif (is_admin()): ?>
                                                <a href="admin/admin-dashboard.php" class="btn btn-danger btn-sm">
                            <i class="fas fa-tachometer-alt me-1"></i>Admin Dashboard
                        </a>
                        <a href="admin/system-settings.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-cogs me-1"></i>Settings
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>



<!-- Hero Section with Featured News -->
<section class="hero-section py-4">
    <div class="container">
        <div class="row g-4">
            <?php if (mysqli_num_rows($featured_result) > 0): ?>
                <!-- Main Featured News -->
                <div class="col-lg-8">
                    <?php $featured = mysqli_fetch_assoc($featured_result); ?>
                    <div class="card border-0 shadow-lg news-card featured-news">
                        <div class="position-relative image-container">
                            <?php if ($featured['video_url']): ?>
                                <div class="position-relative video-thumbnail" data-video-url="<?php echo htmlspecialchars($featured['video_url']); ?>" data-video-title="<?php echo htmlspecialchars(get_news_title($featured)); ?>">
                                    <?php 
                                    // Generate video thumbnail
                                    $videoId = '';
                                    $thumbnailUrl = '';
                                    if (strpos($featured['video_url'], 'youtube.com') !== false || strpos($featured['video_url'], 'youtu.be') !== false) {
                                        if (strpos($featured['video_url'], 'youtube.com/watch?v=') !== false) {
                                            $videoId = substr($featured['video_url'], strpos($featured['video_url'], 'v=') + 2);
                                        } elseif (strpos($featured['video_url'], 'youtu.be/') !== false) {
                                            $videoId = substr($featured['video_url'], strpos($featured['video_url'], 'youtu.be/') + 9);
                                        }
                                        $videoId = explode('?', $videoId)[0];
                                        $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
                                    } else {
                                        $thumbnailUrl = $featured['image'] ?? 'https://via.placeholder.com/800x450/000000/ffffff?text=Video';
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>" class="card-img-top" alt="<?php echo htmlspecialchars(get_news_title($featured)); ?>" style="height: 400px; object-fit: cover;">
                                    
                                    <!-- Video Play Button -->
                                    <div class="video-play-button">
                                        <i class="fas fa-play"></i>
                                    </div>
                                    
                                    <!-- Video Badge -->
                                    <div class="video-quality-badge">VIDEO</div>
                                    
                                    <!-- Views Badge on Top -->
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-dark bg-opacity-75 text-white">
                                            <i class="fas fa-eye me-1"></i> <?php echo number_format($featured['views']); ?> views
                                        </span>
                                    </div>
                                    
                                    <!-- Date Overlay at Bottom -->
                                    <div class="position-absolute bottom-0 start-0 end-0 p-3">
                                        <div class="date-overlay text-white rounded p-2" style="background: rgba(220, 53, 69, 0.9); backdrop-filter: blur(5px);">
                                            <small class="fw-bold"><i class="fas fa-clock me-1"></i>
                                                <?php 
                                                // Use real_post_time for consistent display
                                                $display_date = !empty($featured['real_post_time']) ? $featured['real_post_time'] : $featured['created_at'];
                                                echo format_date_realtime($display_date); 
                                                ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- News Status Badges -->
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <?php if ($featured['time_status'] === 'new'): ?>
                                                <span class="badge bg-secondary animate-pulse">
                                                    <i class="fas fa-sparkles me-1"></i>NEW
                                                </span>
                                            <?php elseif ($featured['time_status'] === 'recent'): ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-clock me-1"></i>Recent
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($featured['is_breaking']): ?>
                                                <span class="badge bg-secondary">BREAKING</span>
                                            <?php endif; ?>
                                        </div>
                                </div>
                            <?php elseif ($featured['image']): ?>
                                <img src="<?php echo htmlspecialchars($featured['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars(get_news_title($featured)); ?>" style="height: 400px; object-fit: cover;">
                                
                                <!-- Views Badge on Top -->
                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-dark bg-opacity-75 text-white">
                                        <i class="fas fa-eye me-1"></i> <?php echo number_format($featured['views']); ?> views
                                    </span>
                                </div>
                                
                                <!-- Date Overlay at Bottom -->
                                <div class="position-absolute bottom-0 start-0 end-0 p-3">
                                    <div class="date-overlay text-white rounded p-2" style="background: rgba(220, 53, 69, 0.9); backdrop-filter: blur(5px);">
                                        <small class="fw-bold"><i class="fas fa-clock me-1"></i>
                                            <?php 
                                            // Use real_post_time for consistent display
                                            $display_date = !empty($featured['real_post_time']) ? $featured['real_post_time'] : $featured['created_at'];
                                            echo format_date_realtime($display_date); 
                                            ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- News Status Badges -->
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <?php if ($featured['time_status'] === 'new'): ?>
                                            <span class="badge bg-secondary animate-pulse">
                                                <i class="fas fa-sparkles me-1"></i>NEW
                                            </span>
                                        <?php elseif ($featured['time_status'] === 'recent'): ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-clock me-1"></i>Recent
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($featured['is_breaking']): ?>
                                            <span class="badge bg-secondary">BREAKING</span>
                                        <?php endif; ?>
                                    </div>
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($featured['category_name'] ?? 'Uncategorized'); ?></span>
                                <?php if (!empty($featured['source_name'])): ?>
                                    <span class="badge bg-gradient source-badge ms-2" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold; padding: 8px 12px; font-size: 0.85rem; border-radius: 20px;">
                                        <?php echo getSourceIcon($featured['source_name']); ?>
                                        <span class="ms-2"><?php echo htmlspecialchars($featured['source_name']); ?></span>
                                        <i class="fas fa-external-link-alt ms-2" style="font-size: 0.7rem;"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h2 class="card-title">
                                <a href="news.php?slug=<?php echo $featured['slug']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars(get_news_title($featured)); ?>
                                </a>
                            </h2>
                            <div class="text-muted mb-2">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <strong>
                                    <?php 
                                    $display_date = !empty($featured['real_post_time']) ? $featured['real_post_time'] : $featured['created_at'];
                                    echo format_date_realtime($display_date); 
                                    ?>
                                </strong>
                            </div>
                            <p class="card-text"><?php echo htmlspecialchars(substr(clean_news_content($featured['excerpt'] ?? ''), 0, 200)) . '...'; ?></p>
                            <div class="news-meta text-muted">
                            </div>
                            <div class="news-actions mt-3">
                                <div class="d-flex gap-2 justify-content-between align-items-center">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-secondary like-btn" onclick="toggleLike(<?php echo $featured['id']; ?>, this)" title="Like this article">
                                            <i class="fas fa-heart"></i> <span class="likes-count"><?php echo $featured['likes_count']; ?></span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" onclick="shareOnFacebook('<?php echo SITE_URL; ?>news.php?slug=<?php echo $featured['slug']; ?>', '<?php echo htmlspecialchars(get_news_title($featured)); ?>')">
                                            <i class="fab fa-facebook-f"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" onclick="shareOnTwitter('<?php echo SITE_URL; ?>news.php?slug=<?php echo $featured['slug']; ?>', '<?php echo htmlspecialchars(get_news_title($featured)); ?>')">
                                            <i class="fab fa-twitter"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="shareOnWhatsApp('<?php echo SITE_URL; ?>news.php?slug=<?php echo $featured['slug']; ?>', '<?php echo htmlspecialchars(get_news_title($featured)); ?>')">
                                            <i class="fab fa-whatsapp"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('<?php echo SITE_URL; ?>news.php?slug=<?php echo $featured['slug']; ?>')">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    </div>
                                    <button class="btn btn-sm btn-secondary" onclick="showCommentsModal(<?php echo $featured['id']; ?>)" title="View and post comments">
                                        <i class="fas fa-comments"></i> <?php echo $featured['comment_count']; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Side Featured News -->
                <div class="col-lg-4">
                    <?php while ($featured = mysqli_fetch_assoc($featured_result)): ?>
                        <div class="card border-0 shadow mb-3 news-card">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <div class="position-relative image-container">
                                        <?php if ($featured['video_url']): ?>
                                            <div class="position-relative video-thumbnail" data-video-url="<?php echo htmlspecialchars($featured['video_url']); ?>" data-video-title="<?php echo htmlspecialchars(get_news_title($featured)); ?>">
                                                <?php 
                                                // Generate video thumbnail
                                                $videoId = '';
                                                $thumbnailUrl = '';
                                                if (strpos($featured['video_url'], 'youtube.com') !== false || strpos($featured['video_url'], 'youtu.be') !== false) {
                                                    if (strpos($featured['video_url'], 'youtube.com/watch?v=') !== false) {
                                                        $videoId = substr($featured['video_url'], strpos($featured['video_url'], 'v=') + 2);
                                                    } elseif (strpos($featured['video_url'], 'youtu.be/') !== false) {
                                                        $videoId = substr($featured['video_url'], strpos($featured['video_url'], 'youtu.be/') + 9);
                                                    }
                                                    $videoId = explode('?', $videoId)[0];
                                                    $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
                                                } else {
                                                    $thumbnailUrl = $featured['image'] ?? 'https://via.placeholder.com/160x120/000000/ffffff?text=Video';
                                                }
                                                ?>
                                                <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars(get_news_title($featured)); ?>" style="height: 120px; object-fit: cover;">
                                                
                                                <!-- Video Play Button -->
                                                <div class="video-play-button" style="width: 35px; height: 35px;">
                                                    <i class="fas fa-play" style="font-size: 14px;"></i>
                                                </div>
                                                
                                                <!-- Video Badge -->
                                                <div class="video-quality-badge" style="font-size: 9px;">VIDEO</div>
                                                
                                                <!-- Views Badge on Top -->
                                                <div class="position-absolute top-0 start-0 m-1">
                                                    <span class="badge bg-dark bg-opacity-75 text-white" style="font-size: 0.7rem;">
                                                        <i class="fas fa-eye me-1"></i> <?php echo number_format($featured['views']); ?>
                                                    </span>
                                                </div>
                                                
                                                <!-- Date Overlay at Bottom -->
                                                <div class="position-absolute bottom-0 start-0 end-0 p-2">
                                                    <div class="date-overlay text-white rounded p-1" style="background: rgba(220, 53, 69, 0.9); backdrop-filter: blur(5px); font-size: 0.65rem;">
                                                        <small class="fw-bold">
                                                            <?php 
                                                            $display_date = !empty($featured['real_post_time']) ? $featured['real_post_time'] : $featured['created_at'];
                                                            echo format_date_realtime($display_date); 
                                                            ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                
                                                <!-- News Status Badges -->
                                                <div class="position-absolute top-0 end-0 m-1">
                                                    <?php if ($featured['time_status'] === 'new'): ?>
                                                        <span class="badge bg-secondary animate-pulse" style="font-size: 0.6rem;">
                                                            <i class="fas fa-sparkles me-1"></i>NEW
                                                        </span>
                                                    <?php elseif ($featured['time_status'] === 'recent'): ?>
                                                        <span class="badge bg-secondary" style="font-size: 0.6rem;">
                                                            <i class="fas fa-clock me-1"></i>Recent
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($featured['is_breaking']): ?>
                                                        <span class="badge bg-secondary" style="font-size: 0.6rem;">BREAKING</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php elseif ($featured['image']): ?>
                                            <img src="<?php echo htmlspecialchars($featured['image']); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars(get_news_title($featured)); ?>" style="height: 120px; object-fit: cover;">
                                            
                                            <!-- Views Badge on Top -->
                                            <div class="position-absolute top-0 start-0 m-1">
                                                <span class="badge bg-dark bg-opacity-75 text-white" style="font-size: 0.7rem;">
                                                    <i class="fas fa-eye me-1"></i> <?php echo number_format($featured['views']); ?>
                                                </span>
                                            </div>
                                            
                                            <!-- Date Overlay at Bottom -->
                                            <div class="position-absolute bottom-0 start-0 end-0 p-2">
                                                <div class="date-overlay text-white rounded p-1" style="background: rgba(220, 53, 69, 0.9); backdrop-filter: blur(5px); font-size: 0.65rem;">
                                                    <small class="fw-bold">
                                                        <?php 
                                                        $display_date = !empty($featured['real_post_time']) ? $featured['real_post_time'] : $featured['created_at'];
                                                        echo format_date_realtime($display_date); 
                                                        ?>
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <!-- News Status Badges -->
                                            <div class="position-absolute top-0 end-0 m-1">
                                                <?php if ($featured['time_status'] === 'new'): ?>
                                                    <span class="badge bg-secondary animate-pulse" style="font-size: 0.6rem;">
                                                        <i class="fas fa-sparkles me-1"></i>NEW
                                                    </span>
                                                <?php elseif ($featured['time_status'] === 'recent'): ?>
                                                    <span class="badge bg-secondary" style="font-size: 0.6rem;">
                                                        <i class="fas fa-clock me-1"></i>Recent
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($featured['is_breaking']): ?>
                                                    <span class="badge bg-secondary" style="font-size: 0.6rem;">BREAKING</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center rounded-start" style="height: 120px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($featured['category_name'] ?? 'Uncategorized'); ?></span>
                                            <?php if (!empty($featured['source_name'])): ?>
                                                <span class="badge bg-gradient source-badge ms-2 d-inline-block" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold; padding: 6px 10px; font-size: 0.75rem; border-radius: 15px;">
                                                    <?php echo getSourceIcon($featured['source_name']); ?>
                                                    <span class="ms-1"><?php echo htmlspecialchars($featured['source_name']); ?></span>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <h6 class="card-title mt-2">
                                            <a href="news.php?slug=<?php echo $featured['slug']; ?>" class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars(substr(get_news_title($featured), 0, 60)) . '...'; ?>
                                            </a>
                                        </h6>
                                        <div class="text-muted small mb-2">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <?php 
                                            // Use real_post_time for consistent display
                                            $display_date = !empty($featured['real_post_time']) ? $featured['real_post_time'] : $featured['created_at'];
                                            echo format_date_realtime($display_date); 
                                            ?>
                                        </div>
                                                                                <div class="news-actions mt-2">
                                            <div class="d-flex gap-1 justify-content-between">
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-xs btn-outline-danger like-btn" onclick="toggleLike(<?php echo $featured['id']; ?>, this)" title="Like">
                                                        <i class="fas fa-heart"></i> <span class="likes-count"><?php echo $featured['likes_count']; ?></span>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-primary" onclick="shareOnFacebook('<?php echo SITE_URL; ?>news.php?slug=<?php echo $featured['slug']; ?>', '<?php echo htmlspecialchars(get_news_title($featured)); ?>')" title="Share on Facebook">
                                                        <i class="fab fa-facebook-f"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-success" onclick="shareOnWhatsApp('<?php echo SITE_URL; ?>news.php?slug=<?php echo $featured['slug']; ?>', '<?php echo htmlspecialchars(get_news_title($featured)); ?>')" title="Share on WhatsApp">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </button>
                                                </div>
                                                <button class="btn btn-xs btn-outline-danger" onclick="showCommentsModal(<?php echo $featured['id']; ?>)" title="View Comments">
                                                    <i class="fas fa-comments"></i> <?php echo $featured['comment_count']; ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <h3>Welcome to PK Live News</h3>
                        <p class="text-muted">Your trusted source for breaking news and updates</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Live TV Section -->
<?php if ($live_stream): ?>
<section class="live-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="live-video-container">
                    <div class="p-4 text-center">
                        <span class="on-air-indicator me-3">ON AIR</span>
                        <h4 class="text-white mb-3"><?php echo htmlspecialchars($live_stream['title'] ?? 'Live Stream'); ?></h4>
                        <div class="embed-responsive embed-responsive-16by9">
                            <?php if ($live_stream['embed_code']): ?>
                                <?php echo $live_stream['embed_code']; ?>
                            <?php elseif ($live_stream['stream_url']): ?>
                                <iframe src="<?php echo htmlspecialchars($live_stream['stream_url'] ?? ''); ?>" 
                                        frameborder="0" allowfullscreen
                                        class="embed-responsive-item"></iframe>
                            <?php else: ?>
                                <div class="bg-dark text-white p-5">
                                    <i class="fas fa-broadcast-tower fa-3x mb-3"></i>
                                    <p>Live stream will be available soon</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center text-white">
                    <h5><i class="fas fa-eye me-2"></i>Live Viewers</h5>
                    <h2 class="viewer-count" id="viewerCount">1,234</h2>
                    <p class="mt-3"><?php echo htmlspecialchars($live_stream['description']); ?></p>
                    <a href="live.php" class="btn btn-light mt-3">
                        <i class="fas fa-expand me-2"></i>Watch Full Screen
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Main Content -->
<div class="container py-5">
    <div class="row">
        <!-- Main Content Area -->
        <div class="col-lg-8">
            <!-- Latest News Section -->
            <section class="latest-news mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3><i class="fas fa-newspaper me-2"></i>All Latest News</h3>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success btn-sm" onclick="refreshNews()" title="Refresh latest news">
                            <i class="fas fa-sync me-1"></i>Refresh Now
                        </button>
                        <a href="category.php" class="btn btn-outline-danger btn-sm" title="View all news categories">View All</a>
                    </div>
                </div>
                
                <!-- News Stats Bar -->
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <i class="fas fa-chart-line me-2"></i>
                        <span id="newsStats">Loading news statistics...</span>
                    </div>
                    <div id="lastUpdate">
                        <small><i class="fas fa-clock me-1"></i>Last updated</small>
                    </div>
                </div>
                
                <div class="row g-4" id="latestNewsContainer">
                    <?php while ($news = mysqli_fetch_assoc($latest_result)): ?>
                        <div class="col-md-6">
                            <div class="card border-0 shadow news-card">
                                <?php if ($news['video_url']): ?>
                                <div class="position-relative video-thumbnail" data-video-url="<?php echo htmlspecialchars($news['video_url']); ?>" data-video-title="<?php echo htmlspecialchars(get_news_title($news)); ?>">
                                    <?php 
                                    // Generate video thumbnail
                                    $videoId = '';
                                    $thumbnailUrl = '';
                                    if (strpos($news['video_url'], 'youtube.com') !== false || strpos($news['video_url'], 'youtu.be') !== false) {
                                        if (strpos($news['video_url'], 'youtube.com/watch?v=') !== false) {
                                            $videoId = substr($news['video_url'], strpos($news['video_url'], 'v=') + 2);
                                        } elseif (strpos($news['video_url'], 'youtu.be/') !== false) {
                                            $videoId = substr($news['video_url'], strpos($news['video_url'], 'youtu.be/') + 9);
                                        }
                                        $videoId = explode('?', $videoId)[0];
                                        $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
                                    } else {
                                        $thumbnailUrl = $news['image'] ?? 'https://via.placeholder.com/400x225/000000/ffffff?text=Video';
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>" class="card-img-top" alt="<?php echo htmlspecialchars(get_news_title($news)); ?>" style="height: 200px; object-fit: cover;">
                                    
                                    <!-- Video Play Button -->
                                    <div class="video-play-button">
                                        <i class="fas fa-play"></i>
                                    </div>
                                    
                                    <!-- Video Badge -->
                                    <div class="video-quality-badge">VIDEO</div>
                                    
                                    <!-- Views Badge on Top -->
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-dark bg-opacity-75 text-white">
                                            <i class="fas fa-eye me-1"></i> <?php echo number_format($news['views']); ?>
                                        </span>
                                    </div>
                                    
                                                                        
                                    <!-- News Status Badges -->
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <?php if ($news['time_status'] === 'new'): ?>
                                            <span class="badge bg-secondary animate-pulse">
                                                <i class="fas fa-sparkles me-1"></i>NEW
                                            </span>
                                        <?php elseif ($news['time_status'] === 'recent'): ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-clock me-1"></i>Recent
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($news['is_breaking']): ?>
                                            <span class="badge bg-secondary">BREAKING</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php elseif ($news['image']): ?>
                                <div class="position-relative image-container">
                                    <img src="<?php echo htmlspecialchars($news['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars(get_news_title($news)); ?>" style="height: 200px; object-fit: cover;">
                                    
                                    <!-- Views Badge on Top -->
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-dark bg-opacity-75 text-white">
                                            <i class="fas fa-eye me-1"></i> <?php echo number_format($news['views']); ?>
                                        </span>
                                    </div>
                                    
                                                                        
                                    <!-- News Status Badges -->
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <?php if ($news['time_status'] === 'new'): ?>
                                            <span class="badge bg-secondary animate-pulse">
                                                <i class="fas fa-sparkles me-1"></i>NEW
                                            </span>
                                        <?php elseif ($news['time_status'] === 'recent'): ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-clock me-1"></i>Recent
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($news['is_breaking']): ?>
                                            <span class="badge bg-secondary">BREAKING</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <span class="badge bg-info"><?php echo htmlspecialchars($news['category_name'] ?? ''); ?></span>
                                        <?php if (!empty($news['source_name'])): ?>
                                            <?php if ($news['source_name'] === 'PK Live News'): ?>
                                                <span class="badge bg-primary source-badge ms-2 d-inline-block" style="color: white; font-weight: bold; padding: 6px 10px; font-size: 0.75rem; border-radius: 15px;">
                                                    <?php echo getSourceIcon($news['source_name']); ?>
                                                    <span class="ms-1"><?php echo htmlspecialchars($news['source_name']); ?></span>
                                                    <i class="fas fa-star ms-1" style="font-size: 0.65rem;"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-gradient source-badge ms-2 d-inline-block" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold; padding: 6px 10px; font-size: 0.75rem; border-radius: 15px;">
                                                    <?php echo getSourceIcon($news['source_name']); ?>
                                                    <span class="ms-1"><?php echo htmlspecialchars($news['source_name']); ?></span>
                                                    <i class="fas fa-external-link-alt ms-1" style="font-size: 0.65rem;"></i>
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($news['sentiment_label'])): ?>
                                            <?php 
                                            $sentiment_colors = ['positive' => 'success', 'negative' => 'danger', 'neutral' => 'secondary'];
                                            $sentiment_icons = ['positive' => '😊', 'negative' => '😔', 'neutral' => '😐'];
                                            $color = $sentiment_colors[$news['sentiment_label']] ?? 'secondary';
                                            $icon = $sentiment_icons[$news['sentiment_label']] ?? '😐';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?> ms-1" title="Sentiment: <?php echo $news['sentiment_label']; ?> (Score: <?php echo $news['sentiment_score']; ?>)">
                                                <?php echo $icon; ?> <?php echo ucfirst($news['sentiment_label']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="card-title">
                                        <a href="news.php?slug=<?php echo $news['slug']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars(get_news_title($news)); ?>
                                        </a>
                                    </h5>
                                    <div class="text-start text-muted small mb-3">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <strong>
                                            <?php 
                                            // Use real_post_time for consistent display
                                            $display_date = !empty($news['real_post_time']) ? $news['real_post_time'] : $news['created_at'];
                                            echo format_date_realtime($display_date); 
                                            ?>
                                        </strong>
                                    </div>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars(substr(clean_news_content($news['excerpt'] ?? ''), 0, 120)) . '...'; ?></p>
                                                                        <div class="news-actions mt-3">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-xs btn-outline-danger like-btn" onclick="toggleLike(<?php echo $news['id']; ?>, this)" title="Like">
                                                    <i class="fas fa-heart"></i> <span class="likes-count"><?php echo $news['likes_count']; ?></span>
                                                </button>
                                                <button class="btn btn-xs btn-outline-primary" onclick="shareOnFacebook('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo htmlspecialchars(get_news_title($news)); ?>')" title="Share on Facebook">
                                                    <i class="fab fa-facebook-f"></i>
                                                </button>
                                                <button class="btn btn-xs btn-outline-info" onclick="shareOnTwitter('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo htmlspecialchars(get_news_title($news)); ?>')" title="Share on Twitter">
                                                    <i class="fab fa-twitter"></i>
                                                </button>
                                                <button class="btn btn-xs btn-outline-success" onclick="shareOnWhatsApp('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo htmlspecialchars($news['title']); ?>')" title="Share on WhatsApp">
                                                    <i class="fab fa-whatsapp"></i>
                                                </button>
                                                <button class="btn btn-xs btn-outline-secondary" onclick="copyToClipboard('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>')" title="Copy Link">
                                                    <i class="fas fa-link"></i>
                                                </button>
                                            </div>
                                            <button class="btn btn-xs btn-secondary" onclick="showCommentsModal(<?php echo $news['id']; ?>)" title="View Comments">
                                                <i class="fas fa-comments me-1"></i><?php echo $news['comment_count']; ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <!-- Pagination for Latest News -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Latest news pagination" class="mt-5">
                    <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                        <ul class="pagination mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                            }
                            ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        
                        
                    </div>
                </nav>
            <?php endif; ?>

            <!-- News Editions Section -->
            <?php if ($editions_result && mysqli_num_rows($editions_result) > 0): ?>
            <section class="news-editions mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3><i class="fas fa-layer-group me-2"></i>News Editions</h3>
                    <a href="admin/manage-editions.php" class="btn btn-outline-danger btn-sm">View All Editions</a>
                </div>
                
                <div class="row g-4">
                    <?php while ($edition = mysqli_fetch_assoc($editions_result)): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow edition-card">
                                <div class="position-relative">
                                    <?php if ($edition['news_image']): ?>
                                        <img src="<?php echo htmlspecialchars($edition['news_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($edition['news_title']); ?>" style="height: 180px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="edition-badge-top" style="background-color: <?php echo $edition['edition_color']; ?>; color: white; position: absolute; top: 10px; right: 10px; padding: 5px 10px; border-radius: 20px; font-size: 0.8em; font-weight: 600;">
                                        <i class="fas <?php echo $edition['edition_icon']; ?> me-1"></i>
                                        <?php echo htmlspecialchars($edition['edition_category_name'] ?? 'Uncategorized'); ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <span class="badge bg-info"><?php echo htmlspecialchars($edition['category_name'] ?? 'Uncategorized'); ?></span>
                                    </div>
                                    <h6 class="card-title edition-title">
                                        <?php echo htmlspecialchars($edition['edition_name']); ?>
                                    </h6>
                                    <p class="card-text">
                                        <a href="news.php?slug=<?php echo $edition['news_slug']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars(substr($edition['news_title'], 0, 80)) . '...'; ?>
                                        </a>
                                    </p>
                                    <?php if (!empty($edition['content'])): ?>
                                        <p class="card-text text-muted small">
                                            <?php echo htmlspecialchars(substr(strip_tags($edition['content']), 0, 100)) . '...'; ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="edition-meta">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i> <?php echo format_date($edition['published_at']); ?>
                                            <?php if ($edition['priority'] > 0): ?>
                                                <span class="ms-2 badge bg-warning">
                                                    <i class="fas fa-star me-1"></i>Priority <?php echo $edition['priority']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>


        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Weather Widget -->
            <div class="sidebar-widget">
                <h3><i class="fas fa-cloud-sun me-2"></i>Weather</h3>
                <?php 
                // Get weather data for homepage if not already loaded
                if (!$weatherData && isApiKeyConfigured()) {
                    $defaultCity = 'Islamabad';
                    $weatherData = getWeatherData($defaultCity, 'metric');
                    if ($weatherData) {
                        $weatherData = formatWeatherData($weatherData);
                    }
                }
                ?>
                <?php if ($weatherData && isApiKeyConfigured()): ?>
                    <div class="weather-widget text-center">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h5 class="mb-0">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                    <?php echo htmlspecialchars($weatherData['city']); ?>
                                </h5>
                                <small class="text-muted"><?php echo htmlspecialchars($weatherData['country']); ?></small>
                            </div>
                            <div>
                                <?php echo getWeatherIcon($weatherData['icon'], 'medium'); ?>
                            </div>
                        </div>
                        <div class="mb-2">
                            <h4 class="mb-0"><?php echo formatTemperature($weatherData['temperature'], 'metric'); ?></h4>
                            <small class="text-muted text-capitalize"><?php echo htmlspecialchars($weatherData['description']); ?></small>
                        </div>
                        <div class="row text-start small">
                            <div class="col-6">
                                <i class="fas fa-tint text-info me-1"></i>
                                <?php echo $weatherData['humidity']; ?>%
                            </div>
                            <div class="col-6">
                                <i class="fas fa-wind text-primary me-1"></i>
                                <?php echo $weatherData['wind_speed']; ?> m/s
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="weather.php?city=<?php echo urlencode($weatherData['city']); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-search me-1"></i>Details
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <i class="fas fa-cloud-sun fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Weather data temporarily unavailable</p>
                        <a href="weather.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-search me-1"></i>Search Weather
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Trending News -->
            <div class="sidebar-widget">
                <h3><i class="fas fa-fire me-2"></i>Trending News</h3>
                <?php if (mysqli_num_rows($trending_result) > 0): ?>
                    <?php $trending_num = 1; ?>
                    <?php while ($trending = mysqli_fetch_assoc($trending_result)): ?>
                        <div class="trending-item">
                            <div class="trending-number"><?php echo $trending_num++; ?></div>
                            <div class="trending-content">
                                <h6>
                                    <a href="news.php?slug=<?php echo $trending['slug']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($trending['title']); ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($trending['category_name'] ?? 'Uncategorized'); ?> • 
                                    <?php echo number_format($trending['views']); ?> views •
                                    <?php echo $trending['likes_count']; ?> likes •
                                    <?php echo $trending['comment_count']; ?> comments
                                </small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No trending news available</p>
                <?php endif; ?>
            </div>

            <!-- Poll Widget -->
            <?php if ($active_poll): ?>
                <div class="sidebar-widget">
                    <h3><i class="fas fa-poll me-2"></i>Poll</h3>
                    <div class="poll-widget">
                        <h6 class="poll-question"><?php echo htmlspecialchars($active_poll['question']); ?></h6>
                        <?php if (!empty($active_poll['description'])): ?>
                            <p class="poll-description text-muted small"><?php echo htmlspecialchars($active_poll['description']); ?></p>
                        <?php endif; ?>
                        
                        <form id="pollForm" class="poll-form">
                            <input type="hidden" name="poll_id" value="<?php echo $active_poll['id']; ?>">
                            <div class="poll-options">
                                <?php foreach ($poll_options as $option): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="poll_option" value="<?php echo $option['id']; ?>" id="option_<?php echo $option['id']; ?>">
                                        <label class="form-check-label" for="option_<?php echo $option['id']; ?>">
                                            <?php echo htmlspecialchars($option['option_text']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-vote-yea me-1"></i>Vote
                            </button>
                        </form>
                        
                        <div id="pollResults" class="poll-results" style="display: none;">
                            <?php 
                            $total_votes = array_sum(array_column($poll_options, 'votes'));
                            foreach ($poll_options as $option): 
                                $percentage = $total_votes > 0 ? round(($option['votes'] / $total_votes) * 100, 1) : 0;
                            ?>
                                <div class="poll-result mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small"><?php echo htmlspecialchars($option['option_text']); ?></span>
                                        <span class="small text-muted"><?php echo $percentage; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?php echo $option['votes']; ?> votes</small>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-2">
                                <small class="text-muted"><?php echo $total_votes; ?> total votes</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Categories -->
            <div class="sidebar-widget">
                <h3><i class="fas fa-tags me-2"></i>Categories</h3>
                <div class="category-list">
                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <a href="category.php?slug=<?php echo $category['slug']; ?>" class="text-decoration-none text-dark">
                                <i class="fas fa-chevron-right me-2 text-danger"></i>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                            <span class="badge bg-secondary">
                                <?php 
                                $count_query = "SELECT COUNT(*) as count FROM news WHERE category_id = " . $category['id'] . " AND status = 'published'";
                                $count_result = mysqli_query($conn, $count_query);
                                $count = mysqli_fetch_assoc($count_result)['count'];
                                echo $count;
                                ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Advertisement -->
            <?php
            require_once 'includes/ads_functions.php';
            displayAdWidget('sidebar');
            ?>
        </div>
    </div>
</div>

<!-- Comments Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentsModalLabel">
                    <i class="fas fa-comments me-2"></i>Comments
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="commentsLoading" class="text-center py-4">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading comments...</p>
                </div>
                <div id="commentsContent" style="display: none;">
                    <!-- Comments will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" title="Close comments modal">Close</button>
                <button type="button" class="btn btn-secondary" id="viewFullArticleBtn" title="View full news article">
                    <i class="fas fa-external-link-alt me-2"></i>View Full Article
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateNewsStats();
    if (autoRefreshEnabled) {
        startAutoRefresh();
    }
});

// Comments Modal Functions
function showCommentsModal(newsId) {
    const modal = new bootstrap.Modal(document.getElementById('commentsModal'));
    const loadingDiv = document.getElementById('commentsLoading');
    const contentDiv = document.getElementById('commentsContent');
    const viewFullBtn = document.getElementById('viewFullArticleBtn');
    
    // Show loading state
    loadingDiv.style.display = 'block';
    contentDiv.style.display = 'none';
    
    // Set the view full article button
    viewFullBtn.onclick = function() {
        window.location.href = `news.php?id=${newsId}`;
    };
    
    // Fetch comments
    fetch(`api/get-comments.php?news_id=${newsId}`)
        .then(response => response.json())
        .then(data => {
            loadingDiv.style.display = 'none';
            contentDiv.style.display = 'block';
            
            if (data.success) {
                displayComments(data.comments, data.news);
            } else {
                contentDiv.innerHTML = '<div class="alert alert-danger">Error loading comments. Please try again.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            loadingDiv.style.display = 'none';
            contentDiv.style.display = 'block';
            contentDiv.innerHTML = '<div class="alert alert-danger">Error loading comments. Please try again.</div>';
        });
    
    modal.show();
}

function displayComments(comments, news) {
    const contentDiv = document.getElementById('commentsContent');
    
    let html = `
        <div class="news-summary mb-4">
            <h6>${news.title}</h6>
            <small class="text-muted">${news.category_name} • ${formatDate(news.published_at)}</small>
        </div>
        <div class="comments-section">
    `;
    
    if (comments.length === 0) {
        html += '<p class="text-muted text-center py-3">No comments yet. Be the first to comment!</p>';
    } else {
        comments.forEach(comment => {
            html += `
                <div class="comment-item border-bottom pb-3 mb-3">
                    <div class="d-flex">
                        <div class="comment-avatar me-3">
                            <i class="fas fa-user-circle fa-2x text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="comment-author fw-bold">
                                        ${comment.name}
                                    </div>
                                    <div class="comment-date text-muted small">${formatDate(comment.created_at)}</div>
                                </div>
                            </div>
                            <div class="comment-content mt-2">
                                ${comment.comment.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    // Check if user is logged in (PHP variable passed to JavaScript)
    const isLoggedIn = <?php echo is_logged_in() ? 'true' : 'false'; ?>;
    const userName = <?php echo isset($_SESSION['user_name']) ? '"' . addslashes($_SESSION['user_name']) . '"' : '"Logged in user"'; ?>;
    const isAdmin = <?php echo is_admin() ? 'true' : 'false'; ?>;
    
    html += `
        </div>
        <div class="add-comment-section mt-4">
            <h6>Leave a Comment</h6>
            <form id="quickCommentForm">
                ${isLoggedIn ? `
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-user-circle me-2"></i>
                        Commenting as ${userName}
                        ${isAdmin ? '<i class="fas fa-star text-warning ms-1" title="Admin User"></i>' : ''}
                    </div>
                ` : `
                    <div class="mb-3">
                        <input type="text" class="form-control" id="quickCommentName" placeholder="Your name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" id="quickCommentEmail" placeholder="Your email" required>
                    </div>
                `}
                <div class="mb-3">
                    <textarea class="form-control" id="quickCommentText" rows="3" placeholder="Your comment" required></textarea>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm" title="Post comment">
                    <i class="fas fa-paper-plane me-2"></i>Post Comment
                </button>
            </form>
        </div>
    `;
    
    contentDiv.innerHTML = html;
    
    // Add form submit handler
    document.getElementById('quickCommentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitQuickComment(news.id);
    });
}

function submitQuickComment(newsId) {
    const comment = document.getElementById('quickCommentText').value;
    
    if (!comment.trim()) {
        showNotification('Please enter a comment', 'error');
        return;
    }
    
    // Prepare data object
    const data = {
        news_id: newsId,
        comment: comment
    };
    
    // Only add name and email for guest users (when fields exist)
    const nameField = document.getElementById('quickCommentName');
    const emailField = document.getElementById('quickCommentEmail');
    if (nameField && emailField) {
        data.name = nameField.value;
        data.email = emailField.value;
    }
    
    fetch('api/submit-comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Comment submitted successfully! It will be visible after approval.', 'success');
            document.getElementById('quickCommentForm').reset();
            // Reload comments
            showCommentsModal(newsId);
        } else {
            showNotification(data.message || 'Error submitting comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting comment:', error);
        showNotification('Error submitting comment', 'error');
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Copy to clipboard function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showNotification('Link copied to clipboard!', 'success');
    }, function(err) {
        console.error('Could not copy text: ', err);
        // Fallback for older browsers
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showNotification('Link copied to clipboard!', 'success');
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }
        document.body.removeChild(textArea);
    });
}

// Show notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(notification);
    
    // Auto-remove notification after 3 seconds
    setTimeout(() => {
        if (notification && notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Toggle like function
function toggleLike(newsId, button) {
    const likesCountSpan = button.querySelector('.likes-count');
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span class="likes-count">...</span>';
    
    fetch('api/toggle-like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            news_id: newsId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update likes count
            likesCountSpan.textContent = data.likes_count;
            
            // Update button state
            if (data.action === 'liked') {
                button.classList.remove('btn-outline-danger');
                button.classList.add('btn-secondary');
                button.classList.add('liked');
                showNotification('Post liked!', 'success');
            } else {
                button.classList.remove('btn-secondary');
                button.classList.remove('liked');
                button.classList.add('btn-outline-danger');
                showNotification('Post unliked', 'info');
            }
        } else {
            showNotification(data.message || 'Error updating like', 'error');
            // Restore original content
            button.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error toggling like:', error);
        showNotification('Error updating like', 'error');
        // Restore original content
        button.innerHTML = originalContent;
    })
    .finally(() => {
        button.disabled = false;
    });
}

function loadMoreNews() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    button.disabled = true;
    
    fetch(`api/load-news.php?page=${currentPage}`)
        .then(response => response.json())
        .then(data => {
            if (data.news && data.news.length > 0) {
                appendNews(data.news);
                currentPage++;
                button.innerHTML = originalText;
                button.disabled = false;
            } else {
                button.innerHTML = '<i class="fas fa-check me-2"></i>No more news';
                button.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error loading more news:', error);
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

// Toggle auto-refresh
function autoRefreshNews() {
    autoRefreshEnabled = !autoRefreshEnabled;
    const button = event.target;
    
    if (autoRefreshEnabled) {
        button.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Auto Refresh ON';
        button.className = 'btn btn-success btn-sm';
        startAutoRefresh();
    } else {
        button.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Auto Refresh OFF';
        button.className = 'btn btn-outline-secondary btn-sm';
        stopAutoRefresh();
    }
}

function startAutoRefresh() {
    // Update stats every 30 seconds
    setInterval(updateNewsStats, 30000);
    
    // Full refresh every 2 minutes
    autoRefreshInterval = setInterval(() => {
        if (autoRefreshEnabled) {
            console.log('Auto-refreshing news...');
            fetch('auto_refresh_news.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Auto-refresh result:', data);
                    updateLastUpdateTime();
                    if (data.new_articles > 0) {
                        showNewArticlesNotification(data.new_articles);
                        // Optionally reload page after notification
                        setTimeout(() => {
                            if (confirm('New articles are available! Would you like to refresh the page?')) {
                                location.reload();
                            }
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Auto-refresh error:', error);
                });
        }
    }, 120000); // 2 minutes
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
}

function toggleAutoRefresh() {
    const button = document.getElementById('unifiedNotificationBtn');
    const icon = document.getElementById('notificationIcon');
    const text = document.getElementById('notificationText');
    
    if (autoRefreshInterval) {
        stopAutoRefresh();
        if (button) {
            button.dataset.mode = 'auto-off';
            icon.className = 'fas fa-bell-slash me-1';
            text.textContent = 'Auto-refresh OFF';
            button.className = 'btn btn-sm btn-secondary';
        }
        
        // Show notification that auto-refresh is stopped
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 80px; right: 20px; z-index: 9999; max-width: 300px;';
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i>
                <span>Auto-refresh disabled</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert">
                <i class="fas fa-times"></i>
            </button>
        `;
        document.body.appendChild(notification);
        
        // Auto-remove notification after 3 seconds
        setTimeout(() => {
            if (notification && notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    } else {
        // Start auto-refresh
        startAutoRefresh();
        if (button) {
            button.dataset.mode = 'auto-on';
            icon.className = 'fas fa-bell me-1';
            text.textContent = 'Auto-refresh ON';
            button.className = 'btn btn-sm btn-success';
        }
        
        // Show notification that auto-refresh is enabled
        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 80px; right: 20px; z-index: 9999; max-width: 300px;';
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <span>Auto-refresh enabled</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert">
                <i class="fas fa-times"></i>
            </button>
        `;
        document.body.appendChild(notification);
        
        // Auto-remove notification after 3 seconds
        setTimeout(() => {
            if (notification && notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }
}

// Unified Notification Handler - Simple Click Toggle
function handleUnifiedNotification() {
    const button = document.getElementById('unifiedNotificationBtn');
    const icon = document.getElementById('notificationIcon');
    const text = document.getElementById('notificationText');
    
    // Toggle between refresh and auto-refresh modes
    if (!button.dataset.mode || button.dataset.mode === 'refresh') {
        // Refresh mode
        icon.className = 'fas fa-spinner fa-spin me-1';
        text.textContent = 'Refreshing...';
        button.disabled = true;
        
        fetch('auto_refresh_news.php')
            .then(response => response.json())
            .then(data => {
                console.log('News refresh result:', data);
                updateLastUpdateTime();
                
                // Show result notification
                const notification = document.createElement('div');
                notification.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
                notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
                notification.innerHTML = `
                    <strong>${data.success ? 'Success!' : 'Error'}</strong><br>
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(notification);
                
                // Reload the page to show updated news
                setTimeout(() => {
                    location.reload();
                }, 1500);
            })
            .catch(error => {
                console.error('Error refreshing news:', error);
                button.dataset.mode = 'refresh';
                icon.className = 'fas fa-bell me-1';
                text.textContent = 'Refresh';
                button.disabled = false;
            });
    } else {
        // Auto-refresh toggle mode
        toggleAutoRefresh();
    }
}

// Update news statistics
function updateNewsStats() {
    fetch('api/load-news.php?stats=1')
        .then(response => response.json())
        .then(data => {
            const statsElement = document.getElementById('newsStats');
            if (statsElement && data.stats) {
                statsElement.innerHTML = `
                    <strong>${data.stats.total_published}</strong> total articles • 
                    <strong>${data.stats.today}</strong> today • 
                    <strong>${data.stats.this_hour}</strong> this hour
                `;
            }
        })
        .catch(error => {
            console.error('Error updating stats:', error);
        });
}

function updateLastUpdateTime() {
    const lastUpdateElement = document.getElementById('lastUpdate');
    if (lastUpdateElement) {
        const now = new Date();
        lastUpdateElement.innerHTML = `<small><i class="fas fa-clock me-1"></i>Last updated: ${now.toLocaleTimeString()}</small>`;
    }
}

function showNewArticlesNotification(count) {
    // Create notification container
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
    
    notification.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h6 class="alert-heading mb-2">
                    <i class="fas fa-sparkles me-2"></i>
                    <strong>New Articles Available!</strong>
                </h6>
                <p class="mb-2">${count} new articles have been added to the site.</p>
            </div>
            <div class="d-flex gap-2">
                <!-- Unified Notification Icon: Refresh + Auto-refresh Toggle -->
                <button type="button" class="btn btn-sm btn-primary" id="unifiedNotificationBtn" onclick="handleUnifiedNotification()" title="Refresh / Toggle Auto-refresh">
                    <i class="fas fa-bell me-1" id="notificationIcon"></i>
                    <span class="d-none d-md-inline" id="notificationText">Refresh</span>
                </button>
            </div>
        </div>
        <hr>
        <button type="button" class="btn-close" data-bs-dismiss="alert">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove notification after 20 seconds
    setTimeout(() => {
        if (notification && notification.parentNode) {
            notification.remove();
        }
    }, 20000);
}

// Refresh news functionality
function refreshNews() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Refreshing...';
    button.disabled = true;
    
    // Trigger scraping and publishing
    fetch('auto_refresh_news.php')
        .then(response => response.json())
        .then(data => {
            console.log('News refresh result:', data);
            updateLastUpdateTime();
            
            // Show result notification
            const notification = document.createElement('div');
            notification.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
            notification.innerHTML = `
                <strong>${data.success ? 'Success!' : 'Error'}</strong><br>
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);
            
            // Reload the page to show updated news
            setTimeout(() => {
                location.reload();
            }, 2000);
        })
        .catch(error => {
            console.error('Error refreshing news:', error);
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

function appendNews(news) {
    const container = document.querySelector('.latest-news .row');
    
    news.forEach(item => {
        const newsHtml = `
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-lg news-card">
                    <div class="position-relative">
                        ${item.image ? 
                            `<img src="${item.image}" class="card-img-top" alt="${item.title}" style="height: 200px; object-fit: cover;">` :
                            `<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-2x text-muted"></i>
                            </div>`
                        }
                        
                        <!-- Views Badge on Top -->
                        <div class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-dark bg-opacity-75 text-white">
                                <i class="fas fa-eye me-1"></i> ${item.views}
                            </span>
                        </div>
                        
                        <!-- Date Overlay at Bottom -->
                        <div class="position-absolute bottom-0 start-0 end-0 p-3">
                            <div class="date-overlay text-white rounded p-2" style="background: rgba(220, 53, 69, 0.9); backdrop-filter: blur(5px);">
                                <small class="fw-bold"><i class="fas fa-clock me-1"></i>${formatDate(item.published_at)}</small>
                            </div>
                        </div>
                        
                        <!-- News Status Badges -->
                        <div class="position-absolute top-0 end-0 m-2">
                            ${item.time_status === 'new' ? 
                                `<span class="badge bg-secondary animate-pulse">
                                    <i class="fas fa-sparkles me-1"></i>NEW
                                </span>` : 
                                item.time_status === 'recent' ? 
                                `<span class="badge bg-secondary">
                                    <i class="fas fa-clock me-1"></i>Recent
                                </span>` : ''
                            }
                            ${item.is_breaking ? 
                                `<span class="badge bg-secondary">BREAKING</span>` : ''
                            }
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-info">${item.category_name}</span>
                            ${item.source_name ? 
                                `<span class="badge bg-gradient source-badge ms-2 d-inline-block" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold; padding: 6px 10px; font-size: 0.75rem; border-radius: 15px;">
                                    ${getSourceIcon(item.source_name)}
                                    <span class="ms-2">${item.source_name}</span>
                                    <i class="fas fa-external-link-alt ms-2" style="font-size: 0.7rem;"></i>
                                </span>` : ''
                            }
                        </div>
                        <h5 class="card-title">
                            <a href="news.php?slug=${item.slug}" class="text-decoration-none text-dark">
                                ${item.title}
                            </a>
                        </h5>
                        <div class="text-muted small mb-2">
                            <i class="fas fa-calendar-alt me-1"></i>
                            <strong>${formatDate(item.published_at)}</strong>
                        </div>
                        <p class="card-text text-muted">${item.excerpt.substring(0, 120)}...</p>
                        <div class="news-actions mt-3">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="d-flex gap-1">
                                    <button class="btn btn-xs btn-secondary like-btn" onclick="toggleLike(${item.id}, this)" title="Like">
                                        <i class="fas fa-heart"></i> <span class="likes-count">${item.likes_count}</span>
                                    </button>
                                    <button class="btn btn-xs btn-outline-primary" onclick="shareOnFacebook('${SITE_URL}news.php?slug=${item.slug}', '${item.title}')" title="Share on Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-info" onclick="shareOnTwitter('${SITE_URL}news.php?slug=${item.slug}', '${item.title}')" title="Share on Twitter">
                                        <i class="fab fa-twitter"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-success" onclick="shareOnWhatsApp('${SITE_URL}news.php?slug=${item.slug}', '${item.title}')" title="Share on WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-secondary" onclick="copyToClipboard('${SITE_URL}news.php?slug=${item.slug}')" title="Copy Link">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </div>
                                <button class="btn btn-xs btn-secondary" onclick="showCommentsModal(${item.id})" title="View Comments">
                                    <i class="fas fa-comments me-1"></i>${item.comment_count}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', newsHtml);
    });
}

// Format date for JavaScript
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

// Simulate live viewer count updates
setInterval(() => {
    const viewerCount = document.getElementById('viewerCount');
    if (viewerCount) {
        const currentCount = parseInt(viewerCount.textContent.replace(',', ''));
        const change = Math.floor(Math.random() * 21) - 10; // Random change between -10 and +10
        const newCount = Math.max(100, currentCount + change);
        viewerCount.textContent = newCount.toLocaleString();
    }
}, 5000);

// Auto-refresh news every 5 minutes
setInterval(() => {
    console.log('Auto-refreshing news...');
    fetch('auto_refresh_news.php')
        .then(response => response.json())
        .then(data => {
            console.log('Auto-refresh result:', data);
            if (data.new_articles > 0) {
                // Show notification for new articles
                const notification = document.createElement('div');
                notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
                notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
                notification.innerHTML = `
                    <strong>New Articles Available!</strong><br>
                    ${data.new_articles} new articles have been added.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(notification);
                
                // Auto-remove notification after 10 seconds
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 10000);
            }
        })
        .catch(error => {
            console.error('Auto-refresh error:', error);
        });
}, 300000); // 5 minutes
</script>

<script>
// Poll voting functionality
document.addEventListener('DOMContentLoaded', function() {
    const pollForm = document.getElementById('pollForm');
    if (pollForm) {
        pollForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const pollId = formData.get('poll_id');
            const optionId = formData.get('poll_option');
            
            if (!optionId) {
                alert('Please select an option to vote!');
                return;
            }
            
            // Send vote to server
            fetch('vote_poll.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show results
                    document.getElementById('pollForm').style.display = 'none';
                    document.getElementById('pollResults').style.display = 'block';
                    
                    // Update results with new data
                    updatePollResults(pollId);
                } else {
                    alert(data.message || 'Error voting in poll');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting vote');
            });
        });
    }
});

function updatePollResults(pollId) {
    fetch('get_poll_results.php?poll_id=' + pollId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const resultsContainer = document.getElementById('pollResults');
                let totalVotes = 0;
                
                // Calculate total votes
                data.options.forEach(option => {
                    totalVotes += parseInt(option.votes);
                });
                
                // Generate results HTML
                let resultsHTML = '';
                data.options.forEach(option => {
                    const percentage = totalVotes > 0 ? Math.round((option.votes / totalVotes) * 100) : 0;
                    resultsHTML += `
                        <div class="poll-result mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">${option.option_text}</span>
                                <span class="small text-muted">${percentage}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" style="width: ${percentage}%"></div>
                            </div>
                            <small class="text-muted">${option.votes} votes</small>
                        </div>
                    `;
                });
                
                resultsHTML += `
                    <div class="text-center mt-2">
                        <small class="text-muted">${totalVotes} total votes</small>
                    </div>
                `;
                
                resultsContainer.innerHTML = resultsHTML;
            }
        })
        .catch(error => {
            console.error('Error fetching poll results:', error);
        });
}
</script>

<script>
    {
        // Overall Sentiment
        const overallSentiment = document.getElementById('overallSentiment');
        overallSentiment.innerHTML = `
            <div class="d-flex justify-content-between mb-2">
                <span>😊 Positive</span>
                <span class="badge bg-success">45%</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>😐 Neutral</span>
                <span class="badge bg-secondary">35%</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>😔 Negative</span>
                <span class="badge bg-danger">20%</span>
            </div>
        `;
        
        // News Sources
        const sourcesSentiment = document.getElementById('sourcesSentiment');
        sourcesSentiment.innerHTML = `
            <div class="small mb-2">
                <div class="d-flex justify-content-between">
                    <span>CNN</span>
                    <span class="badge bg-success">😊</span>
                </div>
            </div>
            <div class="small mb-2">
                <div class="d-flex justify-content-between">
                    <span>BBC</span>
                    <span class="badge bg-secondary">😐</span>
                </div>
            </div>
            <div class="small mb-2">
                <div class="d-flex justify-content-between">
                    <span>ARY News</span>
                    <span class="badge bg-danger">😔</span>
                </div>
            </div>
            <div class="small">
                <div class="d-flex justify-content-between">
                    <span>PK Live</span>
                    <span class="badge bg-success">😊</span>
                </div>
            </div>
        `;
        
        // Top Sentiments
        setTimeout(function() {
            const topSentiments = document.getElementById('topSentiments');
            topSentiments.innerHTML = `
            <div class="small mb-2">
                <div class="text-success">📈 Most Positive:</div>
                <div class="text-muted">Technology breakthrough announced</div>
            </div>
            <div class="small">
                <div class="text-danger">📉 Most Negative:</div>
                <div class="text-muted">Economic concerns raised</div>
            </div>
            `;
    }, 1500); // Simulate loading time
}
</script>

<style>
.source-badge {
    position: relative;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    border: 2px solid transparent;
}

.source-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    border-color: rgba(255,255,255,0.3);
}

.source-badge.external {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
}

.source-badge i {
    margin-right: 4px;
}

.breaking-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(45deg, #dc3545, #e74c3c);
    color: white;
    padding: 6px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(220,53,69,0.3);
    animation: pulse 2s infinite;
}

.news-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.news-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.refresh-btn {
    transition: all 0.3s ease;
}

.refresh-btn:hover {
    transform: rotate(180deg);
}

/* Enhanced source badge colors for different sources */
.source-badge.bbc { background: linear-gradient(45deg, #0052cc, #0047b3); }
.source-badge.cnn { background: linear-gradient(45deg, #cc0000, #990000); }
.source-badge.reuters { background: linear-gradient(45deg, #ff6600, #cc5200); }
.source-guardian { background: linear-gradient(45deg, #005689, #004066); }
.source-badge.bloomberg { background: linear-gradient(45deg, #73ab84, #5a8f6a); }
.source-badge.fox { background: linear-gradient(45deg, #003366, #002244); }
.source-badge.ap { background: linear-gradient(45deg, #1a1a1a, #000000); }

/* Make source badges stand out more */
.badge.bg-gradient {
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    font-weight: 700 !important;
    border: 1px solid rgba(255,255,255,0.2) !important;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3) !important;
    transition: all 0.3s ease !important;
}

.badge.bg-gradient:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5) !important;
}

/* Add subtle animation to new source badges */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.source-badge {
    animation: slideIn 0.5s ease-out;
}

/* Enhanced content display */
.news-detail-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
    max-width: none;
}

.news-detail-content p {
    margin-bottom: 1.5rem;
    text-align: justify;
}

.news-detail-content h1, .news-detail-content h2, .news-detail-content h3, 
.news-detail-content h4, .news-detail-content h5, .news-detail-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
    color: #222;
}

.news-detail-content ul, .news-detail-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.news-detail-content li {
    margin-bottom: 0.5rem;
}

.news-detail-content blockquote {
    border-left: 4px solid #dc3545;
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #666;
}

.news-detail-content strong {
    font-weight: 600;
    color: #222;
}

.news-detail-content em {
    font-style: italic;
}

.news-detail-content a {
    color: #007bff;
    text-decoration: underline;
}

.news-detail-content a:hover {
    color: #0056b3;
}

/* Clean up any code artifacts */
.news-detail-content code, .news-detail-content pre {
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}

.news-detail-content pre {
    padding: 1rem;
    overflow-x: auto;
}

/* Pulsing animation for new content */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}

.animate-pulse {
    animation: pulse 2s infinite;
}

/* Enhanced news card styling */
.news-card .badge {
    font-size: 0.7rem;
    padding: 4px 8px;
}

/* Auto-refresh button styling */
.btn-success.auto-refresh-on {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
}

/* Stats bar styling */
.alert-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    border: none;
    color: white;
}

/* Notification styling */
.alert-dismissible {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: none;
}

/* Loading spinner enhancement */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* News time indicator */
.time-indicator {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .news-card {
        margin-bottom: 1rem;
    }
    
    .alert-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
}

/* Poll Widget Styling */
.poll-widget {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.poll-question {
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
    line-height: 1.3;
}

.poll-description {
    margin-bottom: 15px;
    font-style: italic;
}

.poll-options .form-check {
    background: white;
    padding: 8px 12px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}

.poll-options .form-check:hover {
    border-color: #007bff;
    background: #f8f9ff;
}

.poll-options .form-check-input {
    margin-right: 8px;
}

.poll-options .form-check-label {
    cursor: pointer;
    font-size: 0.9rem;
    margin-bottom: 0;
}

.poll-results {
    background: white;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.poll-result {
    margin-bottom: 12px;
}

.poll-result .progress {
    background-color: #e9ecef;
    border-radius: 10px;
}

.poll-result .progress-bar {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border-radius: 10px;
    transition: width 0.6s ease;
}

.poll-result small {
    font-size: 0.75rem;
}

.poll-form .btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.poll-form .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}
</style>

<!-- Affiliate Products Section -->
<?php
// Check if affiliate tables exist and include products section
$affiliate_tables_exist = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
if (mysqli_num_rows($affiliate_tables_exist) > 0) {
    require_once 'includes/news-products.php';
    echo display_homepage_products_section();
}
?>

<?php include 'includes/footer.php'; ?>

<script>
// Real-time date formatting function
function format_date_realtime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 0) {
        return `${days} day${days > 1 ? 's' : ''} ago`;
    } else if (hours > 0) {
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else if (minutes > 0) {
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else {
        return '';
    }
}

// Update all real-time dates
function updateRealtimeDates() {
    document.querySelectorAll('.realtime-date').forEach(element => {
        const date = element.getAttribute('data-date');
        if (date) {
            element.textContent = format_date_realtime(date);
        }
    });
}

// Update dates every minute
setInterval(updateRealtimeDates, 60000);

// Initial update
document.addEventListener('DOMContentLoaded', updateRealtimeDates);
</script>

</body>
</html>
