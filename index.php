<?php
require_once 'config/database.php';
require_once 'config/weather.php';
require_once 'includes/language_functions.php';
require_once 'includes/html_encoding_helper.php';

$page_title = 'Home';
$current_lang = get_current_language();

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15; // Show 15 posts per page
$offset = ($page - 1) * $per_page;

// Function to get source icon based on source name
function getSourceIcon($sourceName) {
    $sourceIcons = [
        'PK-LIVE' => '<i class="fas fa-home" style="color: white; font-size: 0.7rem;" title="PK-LIVE"></i>',
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
                    WHEN n.video_path IS NOT NULL AND n.video_path != '' THEN 2
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
                COALESCE((SELECT COUNT(*) FROM post_likes WHERE news_id = n.id), 0) as likes_count,
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
                WHERE n.status = 'published' AND n.published_at <= NOW() 
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
                  WHERE n.status = 'published' AND n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                  ORDER BY n.views DESC, real_post_time DESC LIMIT 5";
$trending_result = mysqli_query($conn, $trending_query);

// Get categories for sidebar (excluding BBC and CNN categories)
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' AND slug NOT IN ('bbc-world', 'cnn-international') ORDER BY name ASC");

// Get live stream info
$live_stream = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM live_stream WHERE status = 'online' ORDER BY id DESC LIMIT 1"));

// Get active poll
$active_poll = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM polls 
     WHERE status = 'active' AND (ends_at IS NULL OR ends_at > NOW()) 
     ORDER BY id DESC LIMIT 1"
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
$editions_query = "SELECT ne.*, 
                  GROUP_CONCAT(n.title SEPARATOR ', ') as news_titles,
                  GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names,
                  COUNT(ea.article_id) as article_count
                  FROM news_editions ne
                  LEFT JOIN edition_articles ea ON ne.id = ea.edition_id
                  LEFT JOIN news n ON ea.article_id = n.id
                  LEFT JOIN categories c ON n.category_id = c.id
                  WHERE ne.status = 'published'
                  GROUP BY ne.id
                  ORDER BY ne.created_at DESC LIMIT 6";

// Check if news_editions table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_editions'");
if (mysqli_num_rows($table_check) > 0) {
    $editions_result = mysqli_query($conn, $editions_query);
}

// Get breaking news editions (check if table exists first)
$breaking_editions_result = null;
$breaking_editions_query = "SELECT ne.*, 
                            GROUP_CONCAT(n.title SEPARATOR ', ') as news_titles,
                            GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names,
                            COUNT(ea.article_id) as article_count
                            FROM news_editions ne
                            LEFT JOIN edition_articles ea ON ne.id = ea.edition_id
                            LEFT JOIN news n ON ea.article_id = n.id
                            LEFT JOIN categories c ON n.category_id = c.id
                            WHERE ne.status = 'published'
                            GROUP BY ne.id
                            ORDER BY ne.created_at DESC LIMIT 3";

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

// Get upcoming events for homepage
$upcoming_events_result = null;
$upcoming_events = array();

// Check if events table exists, create it if not
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'events'");
if (mysqli_num_rows($table_check) == 0) {
    // Create events table
    $create_table_sql = "CREATE TABLE `events` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `description` text DEFAULT NULL,
        `event_date` date NOT NULL,
        `event_time` time DEFAULT NULL,
        `end_date` date DEFAULT NULL,
        `end_time` time DEFAULT NULL,
        `location` varchar(255) DEFAULT NULL,
        `category` varchar(100) DEFAULT NULL,
        `type` enum('conference','meeting','webinar','workshop','social','sports','political','other') DEFAULT 'other',
        `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
        `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
        `image` varchar(255) DEFAULT NULL,
        `url` varchar(500) DEFAULT NULL,
        `organizer` varchar(255) DEFAULT NULL,
        `contact_email` varchar(255) DEFAULT NULL,
        `max_attendees` int(11) DEFAULT NULL,
        `current_attendees` int(11) DEFAULT 0,
        `is_public` tinyint(1) DEFAULT 1,
        `requires_registration` tinyint(1) DEFAULT 0,
        `registration_deadline` datetime DEFAULT NULL,
        `tags` varchar(500) DEFAULT NULL,
        `created_by` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `event_date` (`event_date`),
        KEY `status` (`status`),
        KEY `type` (`type`),
        KEY `category` (`category`),
        KEY `priority` (`priority`),
        KEY `created_by` (`created_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    mysqli_query($conn, $create_table_sql);
    
    // Insert sample events
    $insert_sql = "INSERT INTO `events` (`title`, `description`, `event_date`, `event_time`, `end_date`, `end_time`, `location`, `category`, `type`, `status`, `priority`, `organizer`, `contact_email`, `max_attendees`, `requires_registration`, `tags`) VALUES
    ('Tech Conference 2026', 'Annual technology conference featuring latest innovations', '2026-05-15', '09:00:00', '2026-05-15', '18:00:00', 'Convention Center, Karachi', 'technology', 'conference', 'upcoming', 'high', 'Tech Association', 'info@techconf.pk', 500, 1, 'technology,innovation,conference'),
    ('Political Rally', 'Community gathering for political discussion', '2026-04-20', '16:00:00', '2026-04-20', '20:00:00', 'Public Park, Lahore', 'politics', 'political', 'upcoming', 'medium', 'Political Party', 'contact@party.pk', 1000, 0, 'politics,community,rally'),
    ('Sports Tournament', 'Inter-city cricket championship', '2026-04-25', '10:00:00', '2026-04-27', '18:00:00', 'Sports Complex, Islamabad', 'sports', 'sports', 'upcoming', 'medium', 'Sports Federation', 'sports@federation.pk', 200, 1, 'sports,cricket,tournament'),
    ('Business Workshop', 'Entrepreneurship and startup strategies', '2026-05-01', '14:00:00', '2026-05-01', '17:00:00', 'Business Center, Karachi', 'business', 'workshop', 'upcoming', 'low', 'Business Council', 'workshop@business.pk', 50, 1, 'business,workshop,entrepreneurship'),
    ('Health Webinar', 'Mental health awareness session', '2026-04-18', '19:00:00', '2026-04-18', '20:30:00', 'Online', 'health', 'webinar', 'upcoming', 'medium', 'Health Organization', 'webinar@health.org', 100, 1, 'health,webinar,mental-health')";
    
    mysqli_query($conn, $insert_sql);
}

// Now try to get upcoming events
$upcoming_events_query = "SELECT * FROM events 
                         WHERE status IN ('upcoming', 'ongoing') 
                         AND (event_date >= CURDATE() OR (event_date = CURDATE() AND event_time >= CURTIME()))
                         AND is_public = 1
                         ORDER BY priority DESC, event_date ASC, event_time ASC 
                         LIMIT 5";
$upcoming_events_result = mysqli_query($conn, $upcoming_events_query);

// Update views for news articles
if (isset($_GET['slug'])) {
    $slug = clean_input($_GET['slug']);
    mysqli_query($conn, "UPDATE news SET views = views + 1 WHERE slug = '$slug'");
}
?>

<?php include 'includes/header.php'; ?>

<!-- Quick Access Dashboard for Logged-in Users -->
<?php if (is_logged_in() && (is_reporter() || is_editor() || is_admin())): ?>
<section class="quick-access-dashboard py-2 py-md-3 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-md-8">
                <div class="d-flex align-items-center flex-wrap">
                    <div class="me-2 me-md-3">
                        <span class="badge bg-<?php echo is_admin() ? 'danger' : (is_editor() ? 'primary' : 'warning'); ?> fs-6">
                            <?php echo ucfirst($_SESSION['user_role']); ?>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-0 mb-md-0 fs-5 fs-md-4">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h5>
                        <small class="text-muted d-none d-md-block">
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
            <div class="col-12 col-md-4 text-center text-md-end mt-2 mt-md-0">
                <div class="btn-group-vertical btn-group-sm d-md-none" role="group">
                    <?php if (is_reporter()): ?>
                        <a href="admin/reporter-dashboard-enhanced.php" class="btn btn-warning btn-sm mb-1">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                        <a href="admin/add-news.php" class="btn btn-primary btn-sm mb-1">
                            <i class="fas fa-plus me-1"></i>Create Article
                        </a>
                    <?php elseif (is_editor()): ?>
                        <a href="admin/editor-dashboard-enhanced.php" class="btn btn-primary btn-sm mb-1">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                        <a href="admin/manage-news.php?status=pending" class="btn btn-warning btn-sm mb-1">
                            <i class="fas fa-clock me-1"></i>Review Pending
                        </a>
                    <?php elseif (is_admin()): ?>
                        <a href="admin/admin-dashboard.php" class="btn btn-danger btn-sm mb-1">
                            <i class="fas fa-tachometer-alt me-1"></i>Admin Dashboard
                        </a>
                        <a href="admin/system-settings.php" class="btn btn-secondary btn-sm mb-1">
                            <i class="fas fa-cogs me-1"></i>Settings
                        </a>
                    <?php endif; ?>
                </div>
                <div class="btn-group d-none d-md-flex" role="group">
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



<?php include 'slideshow_component.php'; ?>





<!-- Main Content -->
<div class="container py-5">
    <div class="row">
        <!-- Main Content Area -->
        <div class="col-lg-8">
            <!-- Latest News Section -->
            <section class="latest-news mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3><i class="fas fa-newspaper me-2"></i><?php echo t('all_latest_news', 'All Latest News'); ?></h3>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success btn-sm" onclick="refreshNews()" title="<?php echo t('refresh_now', 'Refresh Now'); ?>">
                            <i class="fas fa-sync me-1"></i><?php echo t('refresh_now', 'Refresh Now'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- News Stats Bar -->
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <i class="fas fa-chart-line me-2"></i>
                        <span id="newsStats"><?php echo t('news_statistics', 'Loading news statistics...'); ?></span>
                    </div>
                    <div id="lastUpdate">
                        <small><i class="fas fa-clock me-1"></i><?php echo t('last_updated', 'Last updated'); ?></small>
                    </div>
                </div>
                
                <div class="row g-4" id="latestNewsContainer">
                    <?php while ($news = mysqli_fetch_assoc($latest_result)): ?>
                        <div class="col-md-6">
                            <div class="card border-0 shadow news-card">
                                <?php if ($news['video_url']): ?>
                                <div class="position-relative video-thumbnail" data-video-url="<?php echo htmlspecialchars($news['video_url']); ?>" data-video-title="<?php echo display_news_title($news); ?>">
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
                                    <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>" class="card-img-top" alt="<?php echo display_news_title($news); ?>" style="height: auto;">
                                    
                                    <!-- Video Play Button -->
                                    <div class="video-play-button">
                                        <i class="fas fa-play"></i>
                                    </div>
                                    
                                                                        
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
                                <?php elseif ($news['video_path']): ?>
                                <div class="position-relative video-thumbnail" data-video-path="<?php echo htmlspecialchars($news['video_path']); ?>" data-video-title="<?php echo display_news_title($news); ?>">
                                    <img src="<?php echo htmlspecialchars($news['image'] ?? 'https://via.placeholder.com/400x225/000000/ffffff?text=Video'); ?>" class="card-img-top" alt="<?php echo display_news_title($news); ?>" style="height: auto;">
                                    
                                    <!-- Video Play Button -->
                                    <div class="video-play-button">
                                        <i class="fas fa-play"></i>
                                    </div>
                                    
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
                                    <img src="<?php echo htmlspecialchars($news['image']); ?>" class="card-img-top" alt="<?php echo display_news_title($news); ?>" style="height: auto;">
                                    
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
                                            <?php if ($news['source_name'] === 'PK-LIVE'): ?>
                                                <span class="badge source-badge ms-2 d-inline-block" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; font-weight: 600; padding: 6px 12px; font-size: 0.75rem; border-radius: 20px; box-shadow: 0 2px 6px rgba(220,53,69,0.3); border: 1px solid rgba(255,255,255,0.2);">
                                                    <i class="fas fa-home" style="color: white; font-size: 0.7rem;" title="PK-LIVE"></i>
                                                    <span class="ms-1" style="text-shadow: 0 1px 2px rgba(0,0,0,0.2); letter-spacing: 0.3px;">PK-LIVE</span>
                                                    <i class="fas fa-star ms-1" style="font-size: 0.65rem; color: #ffd700;"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-gradient source-badge ms-2 d-inline-block" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold; padding: 6px 10px; font-size: 0.75rem; border-radius: 15px;">
                                                    <?php echo getSourceIcon($news['source_name']); ?>
                                                    <span class="ms-2"><?php echo htmlspecialchars($news['source_name']); ?></span>
                                                    <i class="fas fa-external-link-alt ms-2" style="font-size: 0.65rem;"></i>
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
                                            <?php echo display_news_title($news); ?>
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
                                    <!-- Facebook-Style Social Actions -->
                                    <div class="facebook-social-actions mt-3">
                                        <!-- Like Count Display -->
                                        <div class="like-summary mb-2" id="like-summary-<?php echo $news['id']; ?>" style="display: <?php echo $news['likes_count'] > 0 ? 'block' : 'none'; ?>;">
                                            <div class="d-flex align-items-center">
                                                <div class="like-reactions me-2">
                                                    <span class="reaction-icon" style="background: #1877f2; width: 18px; height: 18px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: -8px; border: 2px solid white;">
                                                        <i class="fas fa-thumbs-up" style="color: white; font-size: 10px;"></i>
                                                    </span>
                                                    <?php if ($news['likes_count'] > 1): ?>
                                                        <span class="reaction-icon" style="background: #f44336; width: 18px; height: 18px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: 2px solid white;">
                                                            <i class="fas fa-heart" style="color: white; font-size: 10px;"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class=y"like-text small text-muted">
                                                    <span class="likes-count-display"><?php echo number_format($news['likes_count']); ?></span>
                                                    <?php echo $news['likes_count'] == 1 ? 'person likes this' : 'people like this'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="facebook-action-buttons">
                                            <div class="d-flex border-top border-bottom">
                                                <!-- Like Button -->
                                                <button class="facebook-action-btn like-btn flex-fill" onclick="toggleLike(<?php echo $news['id']; ?>, this)" data-news-id="<?php echo $news['id']; ?>">
                                                    <i class="far fa-thumbs-up"></i>
                                                    <span class="btn-text">Like</span>
                                                </button>
                                                
                                                <!-- Comment Button -->
                                                <button class="facebook-action-btn comment-btn flex-fill" onclick="toggleInlineComments(<?php echo $news['id']; ?>, this)" data-news-id="<?php echo $news['id']; ?>">
                                                    <i class="far fa-comment"></i>
                                                    <span class="btn-text">Comment</span>
                                                </button>
                                                
                                                <!-- Share Button -->
                                                <button class="facebook-action-btn share-btn flex-fill" onclick="showShareOptions(<?php echo $news['id']; ?>, '<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo display_news_title($news); ?>')" data-news-id="<?php echo $news['id']; ?>">
                                                    <i class="fas fa-share"></i>
                                                    <span class="btn-text">Share</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Facebook-Style Comments Section -->
                                    <div id="inline-comments-<?php echo $news['id']; ?>" class="facebook-comments-section" style="display: none;">
                                        <div class="comments-loading text-center py-3">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <div class="small text-muted mt-2">Loading comments...</div>
                                        </div>
                                        <div class="comments-container" style="display: none;">
                                            <!-- Comments List -->
                                            <div class="comments-list mb-3"></div>
                                            
                                            <!-- Facebook-Style Comment Form -->
                                            <div class="facebook-comment-form">
                                                <div class="d-flex">
                                                    <?php if (is_logged_in()): ?>
                                                        <div class="user-avatar me-2">
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #1877f2; color: white; font-weight: bold; font-size: 14px;">
                                                                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                                            </div>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <div class="comment-input-wrapper">
                                                                <textarea class="form-control comment-input" name="comment" placeholder="Write a comment..." rows="1" style="resize: none; border-radius: 18px; border: 1px solid #ccd0d5; padding: 8px 12px;"></textarea>
                                                            </div>
                                                            <div class="comment-actions d-flex justify-content-between align-items-center mt-2" style="display: none;">
                                                                <div class="d-flex gap-3">
                                                                    <button type="button" class="btn btn-sm text-muted">
                                                                        <i class="fas fa-image"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm text-muted">
                                                                        <i class="fas fa-smile"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm text-muted">
                                                                        <i class="fas fa-sticky-note"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="d-flex gap-2">
                                                                    <button type="button" class="btn btn-sm btn-secondary cancel-comment" onclick="cancelComment(<?php echo $news['id']; ?>)">Cancel</button>
                                                                    <button type="submit" class="btn btn-sm btn-primary post-comment-btn" disabled>Post</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="flex-fill">
                                                            <div class="alert alert-info py-2 mb-0">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                <a href="login.php" class="text-decoration-none">Log in</a> to like or comment
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
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
                                    <?php if (!empty($edition['news_image'] ?? null)): ?>
                                        <img src="<?php echo htmlspecialchars($edition['news_image'] ?? ''); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($edition['news_title'] ?? 'Edition'); ?>" style="height: 180px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="edition-badge-top" style="background-color: <?php echo $edition['edition_color'] ?? '#007bff'; ?>; color: white; position: absolute; top: 10px; right: 10px; padding: 5px 10px; border-radius: 20px; font-size: 0.8em; font-weight: 600;">
                                        <i class="fas <?php echo $edition['edition_icon'] ?? 'fa-newspaper'; ?> me-1"></i>
                                        <?php echo htmlspecialchars($edition['edition_category_name'] ?? 'News Edition'); ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <span class="badge bg-info"><?php echo htmlspecialchars($edition['category_name'] ?? 'Uncategorized'); ?></span>
                                    </div>
                                    <h6 class="card-title edition-title">
                                        <?php echo htmlspecialchars($edition['title'] ?? $edition['edition_name'] ?? 'Untitled Edition'); ?>
                                    </h6>
                                    <p class="card-text">
                                        <a href="editions.php?id=<?php echo $edition['id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars(substr($edition['news_titles'] ?? 'No articles available', 0, 80)) . '...'; ?>
                                        </a>
                                    </p>
                                    <?php if (!empty($edition['description'] ?? $edition['content'] ?? null)): ?>
                                        <p class="card-text text-muted small">
                                            <?php echo htmlspecialchars(substr(strip_tags($edition['description'] ?? $edition['content'] ?? ''), 0, 100)) . '...'; ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="edition-meta">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i> <?php echo format_date($edition['edition_date'] ?? $edition['created_at'] ?? $edition['published_at'] ?? date('Y-m-d H:i:s')); ?>
                                            <?php if (($edition['priority'] ?? 0) > 0): ?>
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
            <!-- Sidebar Background Card -->
            <div class="sidebar-background-card">
                <!-- Advanced Weather Widget -->
                <div class="sidebar-widget">
                <h3><i class="fas fa-cloud-sun me-2"></i>Weather Dashboard</h3>
                <?php 
                // Get weather data for homepage if not already loaded
                if (!$weatherData && isApiKeyConfigured()) {
                    $defaultCity = 'Islamabad';
                    $weatherData = getWeatherData($defaultCity, 'metric');
                    if ($weatherData) {
                        $weatherData = formatWeatherData($weatherData);
                        // Get forecast data
                        $forecastData = getWeatherForecast($defaultCity, 'metric');
                        $hourlyForecast = $forecastData ? formatForecastData($forecastData) : [];
                    }
                }
                ?>
                <?php if ($weatherData && isApiKeyConfigured()): ?>
                    <div class="advanced-weather-widget">
                        <!-- Current Weather Card -->
                        <div class="current-weather-card mb-3">
                            <div class="weather-header">
                                <div class="location-info">
                                    <h5 class="mb-0">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                        <?php echo htmlspecialchars($weatherData['city']); ?>
                                    </h5>
                                    <small class="text-muted"><?php echo htmlspecialchars($weatherData['country']); ?></small>
                                </div>
                                <div class="weather-icon-main">
                                    <?php echo getWeatherIcon($weatherData['icon'], 'large'); ?>
                                </div>
                            </div>
                            
                            <div class="weather-main">
                                <div class="temperature-display">
                                    <span class="temp-main"><?php echo formatTemperature($weatherData['temperature'], 'metric'); ?></span>
                                    <span class="temp-feels">Feels like <?php echo formatTemperature($weatherData['feels_like'], 'metric'); ?></span>
                                </div>
                                <div class="weather-desc">
                                    <span class="text-capitalize"><?php echo htmlspecialchars($weatherData['description']); ?></span>
                                </div>
                            </div>
                            
                            <div class="weather-details-grid">
                                <div class="detail-item">
                                    <i class="fas fa-tint text-info"></i>
                                    <div>
                                        <small>Humidity</small>
                                        <span><?php echo $weatherData['humidity']; ?>%</span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-wind text-primary"></i>
                                    <div>
                                        <small>Wind</small>
                                        <span><?php echo $weatherData['wind_speed']; ?> m/s</span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-compress-arrows-alt text-warning"></i>
                                    <div>
                                        <small>Pressure</small>
                                        <span><?php echo $weatherData['pressure']; ?> hPa</span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-eye text-secondary"></i>
                                    <div>
                                        <small>Visibility</small>
                                        <span><?php echo $weatherData['visibility'] ? $weatherData['visibility'] . ' km' : 'N/A'; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sun Times -->
                            <div class="sun-times">
                                <div class="sun-item">
                                    <i class="fas fa-sun text-warning"></i>
                                    <div>
                                        <small>Sunrise</small>
                                        <span><?php echo $weatherData['sunrise']; ?></span>
                                    </div>
                                </div>
                                <div class="sun-item">
                                    <i class="fas fa-moon text-info"></i>
                                    <div>
                                        <small>Sunset</small>
                                        <span><?php echo $weatherData['sunset']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hourly Forecast -->
                        <?php if (!empty($hourlyForecast)): ?>
                        <div class="hourly-forecast mb-3">
                            <h6 class="forecast-title">Next 24 Hours</h6>
                            <div class="hourly-scroll">
                                <?php 
                                $hourlyItems = array_slice($hourlyForecast, 0, 8);
                                foreach ($hourlyItems as $hour): 
                                    $time = date('H:i', $hour['dt']);
                                    $temp = round($hour['main']['temp']);
                                    $icon = $hour['weather'][0]['icon'];
                                ?>
                                <div class="hour-item">
                                    <small><?php echo $time; ?></small>
                                    <?php echo getWeatherIcon($icon, 'small'); ?>
                                    <span><?php echo $temp; ?>°</span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Quick Actions -->
                        <div class="weather-actions">
                            <a href="weather.php?city=<?php echo urlencode($weatherData['city']); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-chart-line me-1"></i>Detailed Forecast
                            </a>
                            <button onclick="refreshWeather()" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="weather-widget-error">
                        <div class="text-center">
                            <i class="fas fa-cloud-sun fa-3x text-muted mb-3"></i>
                            <h6>Weather Service</h6>
                            <p class="text-muted small">Advanced weather data temporarily unavailable</p>
                            <div class="weather-actions">
                                <a href="weather.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-search me-1"></i>Search Weather
                                </a>
                                <button onclick="refreshWeather()" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-sync-alt me-1"></i>Retry
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Upcoming Events -->
            <?php if ($upcoming_events_result && mysqli_num_rows($upcoming_events_result) > 0): ?>
            <div class="sidebar-widget">
                <h3><i class="fas fa-calendar-alt me-2"></i>Upcoming Events</h3>
                <div class="events-list">
                    <?php while ($event = mysqli_fetch_assoc($upcoming_events_result)): ?>
                        <div class="event-item mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($event['title']); ?></h6>
                                <?php
                                $priority_colors = [
                                    'low' => 'secondary',
                                    'medium' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger'
                                ];
                                $color = $priority_colors[$event['priority']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?> badge-sm"><?php echo ucfirst($event['priority']); ?></span>
                            </div>
                            
                            <?php if ($event['image']): ?>
                                <img src="<?php echo $event['image']; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                     class="w-100 mb-2" style="height: 120px; object-fit: cover; border-radius: 5px;">
                            <?php endif; ?>
                            
                            <div class="event-details">
                                <div class="event-date mb-1">
                                    <i class="fas fa-calendar text-primary me-2"></i>
                                    <strong><?php echo date('M j, Y', strtotime($event['event_date'])); ?></strong>
                                    <?php if ($event['event_time']): ?>
                                        at <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($event['location']): ?>
                                <div class="event-location mb-1">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($event['organizer']): ?>
                                <div class="event-organizer mb-2">
                                    <i class="fas fa-user text-info me-2"></i>
                                    <?php echo htmlspecialchars($event['organizer']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($event['description']): ?>
                                <div class="event-description mb-2">
                                    <small class="text-muted"><?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...</small>
                                </div>
                                <?php endif; ?>
                                
                                <div class="event-meta mb-2">
                                    <span class="badge bg-info me-1"><?php echo ucfirst($event['type']); ?></span>
                                    <?php
                                    $status_colors = [
                                        'upcoming' => 'primary',
                                        'ongoing' => 'success',
                                        'completed' => 'secondary',
                                        'cancelled' => 'danger'
                                    ];
                                    $status_color = $status_colors[$event['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $status_color; ?>"><?php echo ucfirst($event['status']); ?></span>
                                </div>
                                
                                <?php if ($event['url'] || $event['requires_registration']): ?>
                                <div class="event-actions">
                                    <?php if ($event['url']): ?>
                                        <a href="<?php echo htmlspecialchars($event['url']); ?>" target="_blank" class="btn btn-primary btn-sm me-2">
                                            <i class="fas fa-external-link-alt me-1"></i>Visit Event
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($event['requires_registration'] && $event['registration_deadline']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Register by: <?php echo date('M j, Y', strtotime($event['registration_deadline'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="text-center mt-3">
                    <a href="events.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-calendar me-1"></i>View All Events
                    </a>
                </div>
            </div>
            <?php endif; ?>


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
                                <?php 
                                $total_votes = array_sum(array_column($poll_options, 'votes'));
                                foreach ($poll_options as $option): 
                                    $percentage = $total_votes > 0 ? round(($option['votes'] / $total_votes) * 100, 1) : 0;
                                ?>
                                    <div class="form-check mb-2 poll-option-with-count">
                                        <input class="form-check-input" type="radio" name="poll_option" value="<?php echo $option['id']; ?>" id="option_<?php echo $option['id']; ?>">
                                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="option_<?php echo $option['id']; ?>">
                                            <span><?php echo htmlspecialchars($option['option_text']); ?></span>
                                            <span class="badge bg-secondary poll-vote-count">
                                                <?php echo $option['votes']; ?> votes
                                                <?php if ($total_votes > 0): ?>
                                                    (<?php echo $percentage; ?>%)
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($total_votes > 0): ?>
                                <div class="text-center mt-2 mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        <?php echo $total_votes; ?> total votes cast
                                    </small>
                                </div>
                            <?php endif; ?>
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
                <div class="category-dropdown">
                    <select class="form-select category-select" onchange="window.location.href=this.value">
                        <option value="">Select a category...</option>
                        <?php 
                        // Reset the categories result pointer to start
                        mysqli_data_seek($categories, 0);
                        while ($category = mysqli_fetch_assoc($categories)): 
                            $count_query = "SELECT COUNT(*) as count FROM news WHERE category_id = " . $category['id'] . " AND status = 'published' AND published_at <= NOW()";
                            $count_result = mysqli_query($conn, $count_query);
                            $count = mysqli_fetch_assoc($count_result)['count'];
                        ?>
                            <option value="category.php?slug=<?php echo $category['slug']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?> (<?php echo $count; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
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
// Helper function to fix API URLs for localhost development
function getApiUrl(endpoint) {
    const siteUrl = '<?php echo SITE_URL; ?>/';
    return siteUrl + endpoint;
}

// Global variables
let autoRefreshEnabled = false;
let autoRefreshInterval = null;
let currentPage = 2; // For load more functionality

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
                        <input type="text" class="form-control" id="quickCommentName" name="quick_comment_name" placeholder="Your name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" id="quickCommentEmail" name="quick_comment_email" placeholder="Your email" required>
                    </div>
                `}
                <div class="mb-3">
                    <textarea class="form-control" id="quickCommentText" name="quick_comment_text" rows="3" placeholder="Your comment" required></textarea>
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
    
    fetch('/api/submit-comment.php', {
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

// Enhanced Facebook-style share function
function showShareOptions(newsId, url, title) {
    // Create share modal
    const modal = document.createElement('div');
    modal.className = 'share-options-modal';
    modal.innerHTML = `
        <div class="share-options-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Share this post</h5>
                <button class="btn btn-sm btn-light" onclick="closeShareModal(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="share-options-list">
                <button class="share-option-btn" onclick="shareOnFacebook('${url}', '${title}')">
                    <div class="share-option-icon facebook-icon">
                        <i class="fab fa-facebook-f"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Facebook</div>
                        <div class="small text-muted">Share on Facebook</div>
                    </div>
                </button>
                <button class="share-option-btn" onclick="shareOnTwitter('${url}', '${title}')">
                    <div class="share-option-icon twitter-icon">
                        <i class="fab fa-twitter"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Twitter</div>
                        <div class="small text-muted">Share on Twitter</div>
                    </div>
                </button>
                <button class="share-option-btn" onclick="shareOnWhatsApp('${url}', '${title}')">
                    <div class="share-option-icon whatsapp-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div>
                        <div class="fw-bold">WhatsApp</div>
                        <div class="small text-muted">Share on WhatsApp</div>
                    </div>
                </button>
                <button class="share-option-btn" onclick="shareViaEmail('${url}', '${title}')">
                    <div class="share-option-icon email-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Email</div>
                        <div class="small text-muted">Share via email</div>
                    </div>
                </button>
                <button class="share-option-btn" onclick="copyToClipboard('${url}')">
                    <div class="share-option-icon link-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Copy link</div>
                        <div class="small text-muted">Copy to clipboard</div>
                    </div>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeShareModal(modal);
        }
    });
}

function closeShareModal(element) {
    const modal = element.closest('.share-options-modal');
    if (modal) {
        modal.remove();
    }
}

function shareOnFacebook(url, title) {
    const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
    window.open(shareUrl, '_blank', 'width=600,height=400');
    closeShareModal(document.querySelector('.share-options-modal'));
}

function shareOnTwitter(url, title) {
    const shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
    window.open(shareUrl, '_blank', 'width=600,height=400');
    closeShareModal(document.querySelector('.share-options-modal'));
}

function shareOnWhatsApp(url, title) {
    const shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`;
    window.open(shareUrl, '_blank');
    closeShareModal(document.querySelector('.share-options-modal'));
}

function shareViaEmail(url, title) {
    const subject = encodeURIComponent(title);
    const body = encodeURIComponent(`Check out this article: ${title}\n\n${url}`);
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
    closeShareModal(document.querySelector('.share-options-modal'));
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Link copied to clipboard!', 'success');
        closeShareModal(document.querySelector('.share-options-modal'));
    }).catch(() => {
        showNotification('Failed to copy link', 'error');
    });
}

function cancelComment(newsId) {
    const commentsSection = document.getElementById(`inline-comments-${newsId}`);
    const commentInput = commentsSection?.querySelector('.comment-input');
    const commentActions = commentsSection?.querySelector('.comment-actions');
    
    if (commentInput) {
        commentInput.value = '';
        commentInput.style.height = 'auto';
    }
    
    if (commentActions) {
        commentActions.style.display = 'none';
    }
}

// Enhanced comment input handling
document.addEventListener('DOMContentLoaded', function() {
    // Add input event listeners to comment inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('comment-input')) {
            const commentActions = e.target.closest('.facebook-comment-form')?.querySelector('.comment-actions');
            const postBtn = commentActions?.querySelector('.post-comment-btn');
            
            if (commentActions && postBtn) {
                if (e.target.value.trim()) {
                    commentActions.style.display = 'flex';
                    postBtn.disabled = false;
                } else {
                    commentActions.style.display = 'none';
                    postBtn.disabled = true;
                }
            }
            
            // Auto-resize textarea
            e.target.style.height = 'auto';
            e.target.style.height = e.target.scrollHeight + 'px';
        }
    });
    
    // Handle comment form submissions
    document.addEventListener('submit', function(e) {
        if (e.target.closest('.facebook-comment-form')) {
            e.preventDefault();
            const form = e.target;
            const newsId = form.closest('.facebook-comments-section').id.replace('inline-comments-', '');
            const comment = form.querySelector('.comment-input').value.trim();
            
            if (comment) {
                submitInlineComment(newsId, comment, form);
            }
        }
    });
});

// Enhanced Facebook-style inline comments function
function toggleInlineComments(newsId, button) {
    const commentsSection = document.getElementById(`inline-comments-${newsId}`);
    const isVisible = commentsSection.style.display !== 'none';
    
    if (isVisible) {
        // Hide comments with animation
        commentsSection.style.display = 'none';
        button.classList.remove('active');
    } else {
        // Show comments with animation
        commentsSection.style.display = 'block';
        button.classList.add('active');
        
        // Load comments if not already loaded
        const commentsContainer = commentsSection.querySelector('.comments-container');
        if (commentsContainer && commentsContainer.style.display === 'none') {
            loadInlineComments(newsId);
        }
        
        // Focus comment input if user is logged in
        const commentInput = commentsSection.querySelector('.comment-input');
        if (commentInput) {
            setTimeout(() => commentInput.focus(), 300);
        }
    }
}

function loadInlineComments(newsId) {
    const commentsSection = document.getElementById(`inline-comments-${newsId}`);
    const loadingDiv = commentsSection.querySelector('.comments-loading');
    const containerDiv = commentsSection.querySelector('.comments-container');
    
    // Show loading
    loadingDiv.style.display = 'block';
    containerDiv.style.display = 'none';
    
    // Fetch comments
    fetch(`api/get-comments.php?news_id=${newsId}`)
        .then(response => response.json())
        .then(data => {
            loadingDiv.style.display = 'none';
            containerDiv.style.display = 'block';
            
            if (data.success) {
                displayInlineComments(data.comments, data.news, newsId);
            } else {
                containerDiv.innerHTML = `<p class="text-muted">Error loading comments: ${data.message || 'Unknown error'}</p>`;
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            loadingDiv.style.display = 'none';
            containerDiv.style.display = 'block';
            containerDiv.innerHTML = '<p class="text-muted">Error loading comments</p>';
        });
}

function displayInlineComments(comments, news, newsId) {
    const commentsSection = document.getElementById(`inline-comments-${newsId}`);
    const commentsList = commentsSection.querySelector('.comments-list');
    
    let html = '';
    
    if (comments.length === 0) {
        html = '<p class="text-muted text-center py-2 mb-2">No comments yet. Be the first to comment!</p>';
    } else {
        // Show only first 3 comments inline with "View more" option if there are more
        const displayComments = comments.slice(0, 3);
        const hasMore = comments.length > 3;
        
        displayComments.forEach(comment => {
            html += `
                <div class="comment-item border-bottom pb-2 mb-2">
                    <div class="d-flex">
                        <div class="comment-avatar me-2">
                            <i class="fas fa-user-circle text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="comment-author fw-bold small">
                                        ${comment.name}
                                        ${comment.is_admin ? '<i class="fas fa-star text-warning ms-1" title="Admin"></i>' : ''}
                                    </div>
                                    <div class="comment-date text-muted small">${formatDate(comment.created_at)}</div>
                                </div>
                            </div>
                            <div class="comment-content mt-1 small">
                                ${comment.comment.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        if (hasMore) {
            html += `
                <div class="text-center">
                    <button class="btn btn-sm btn-outline-secondary" onclick="showCommentsModal(${newsId})">
                        <i class="fas fa-comments me-1"></i>View all ${comments.length} comments
                    </button>
                </div>
            `;
        }
    }
    
    commentsList.innerHTML = html;
}

function submitInlineComment(newsId, comment, form) {
    const data = {
        news_id: parseInt(newsId),
        comment: comment,
        parent_comment_id: form.dataset.parentId ? parseInt(form.dataset.parentId) : null
    };
    
    // Validate comment
    if (!data.comment || data.comment.trim() === '') {
        showNotification('Please enter a comment', 'error');
        return;
    }
    
    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Posting...';
    
    fetch('api/submit-comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        if (result.success) {
            showNotification('Comment submitted successfully! It will be visible after approval.', 'success');
            form.reset();
            // Reload comments
            loadInlineComments(newsId);
        } else {
            showNotification(result.message || 'Error submitting comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting comment:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        showNotification('Error submitting comment', 'error');
    });
}

// Auto-like functionality removed - likes only happen when users manually click

// Enhanced Facebook-style toggle like function
function toggleLike(newsId, button) {
    const icon = button.querySelector('i');
    const btnText = button.querySelector('.btn-text');
    const originalContent = button.innerHTML;
    const isLiked = button.classList.contains('liked');
    
    // Add haptic feedback for mobile
    if (navigator.vibrate) {
        navigator.vibrate(50);
    }
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span class="btn-text">...</span>';
    
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
        button.disabled = false;
        console.log('API Response:', data);
        
        if (data.success) {
            // Update like summary display
            updateLikeSummary(newsId, data.likes_count);
            
            // Update button state with Facebook-style animation
            if (data.action === 'liked') {
                button.classList.add('liked');
                button.innerHTML = '<i class="fas fa-thumbs-up"></i> <span class="btn-text">Like</span>';
                showNotification('Post liked!', 'success');
                
                // Add like animation
                icon.style.animation = 'likeAnimation 0.3s ease';
                setTimeout(() => {
                    icon.style.animation = '';
                }, 300);
            } else {
                button.classList.remove('liked');
                button.innerHTML = '<i class="far fa-thumbs-up"></i> <span class="btn-text">Like</span>';
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

// Update like summary display
function updateLikeSummary(newsId, likesCount) {
    console.log('Updating like summary for news ID:', newsId, 'with count:', likesCount);
    
    // Find all elements that need updating
    const likeSummary = document.getElementById(`like-summary-${newsId}`);
    
    // Update all likes-count-display elements for this news item
    const allLikesDisplays = document.querySelectorAll(`[data-news-id="${newsId}"] .likes-count-display`);
    
    // Update the main like summary
    if (likeSummary) {
        const likesCountDisplay = likeSummary.querySelector('.likes-count-display');
        const likeText = likeSummary.querySelector('.like-text');
        
        if (likesCountDisplay) {
            likesCountDisplay.textContent = likesCount;
        }
        
        if (likeText) {
            if (likesCount > 0) {
                likeSummary.style.display = 'block';
                likeText.innerHTML = `<span class="likes-count-display">${likesCount}</span> ${likesCount == 1 ? 'person likes this' : 'people like this'}`;
            } else {
                likeSummary.style.display = 'none';
            }
        }
    }
    
    // Update all other like count displays (like in buttons)
    allLikesDisplays.forEach(element => {
        element.textContent = likesCount;
    });
    
    // Also update any elements with just the count class
    const allCountElements = document.querySelectorAll(`.likes-count`);
    allCountElements.forEach(element => {
        // Check if this element belongs to the current news item
        const newsCard = element.closest('[data-news-id="' + newsId + '"]');
        if (newsCard) {
            element.textContent = likesCount;
        }
    });
}

// Auto-like initialization removed - likes are now manual only

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
            fetch(getApiUrl('auto_refresh_news.php'))
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
                            `<img src="${item.image}" class="card-img-top" alt="${item.title}" style="height: auto;">` :
                            `<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: auto;">
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
                
                // Also update the vote counts in the form (in case user navigates back)
                updatePollFormCounts(data.options, totalVotes);
            }
        })
        .catch(error => {
            console.error('Error fetching poll results:', error);
        });
}

function updatePollFormCounts(options, totalVotes) {
    // Update vote count badges in the poll form
    options.forEach(option => {
        const percentage = totalVotes > 0 ? Math.round((option.votes / totalVotes) * 100) : 0;
        const voteCountBadge = document.querySelector(`#option_${option.id} + .form-check-label .poll-vote-count`);
        if (voteCountBadge) {
            voteCountBadge.innerHTML = `${option.votes} votes${totalVotes > 0 ? ` (${percentage}%)` : ''}`;
        }
    });
    
    // Update total votes display
    const totalVotesElement = document.querySelector('.poll-options').nextElementSibling;
    if (totalVotesElement && totalVotesElement.querySelector('.text-muted')) {
        totalVotesElement.querySelector('.text-muted').innerHTML = 
            `<i class="fas fa-chart-bar me-1"></i>${totalVotes} total votes cast`;
    }
}
</script>

<script>
    {
        // Overall Sentiment
        const overallSentiment = document.getElementById('overallSentiment');
        if (overallSentiment) {
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
        }
        
        // News Sources
        const sourcesSentiment = document.getElementById('sourcesSentiment');
        if (sourcesSentiment) {
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
        }
        
        // Top Sentiments
        setTimeout(function() {
            const topSentiments = document.getElementById('topSentiments');
            if (topSentiments) {
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
            }
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
}

/* Inline Comments Styles */
.inline-comments-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
    border: 1px solid #e9ecef;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.comments-loading {
    color: #6c757d;
    font-size: 0.9rem;
}

.comments-container {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.comment-item {
    background: white;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 8px;
    border-left: 3px solid #007bff;
    transition: all 0.2s ease;
}

.comment-item:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transform: translateX(2px);
}

.comment-avatar {
    color: #6c757d;
    font-size: 1.2rem;
}

.comment-author {
    color: #495057;
    font-size: 0.85rem;
}

.comment-date {
    font-size: 0.75rem;
}

.comment-content {
    color: #6c757d;
    line-height: 1.4;
    font-size: 0.8rem;
}

.inline-comment-form {
    background: white;
    border-radius: 6px;
    padding: 12px;
    border: 1px solid #dee2e6;
    margin-top: 10px;
}

.inline-comment-form h6 {
    color: #495057;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.inline-comment-form .form-control-sm {
    border-radius: 4px;
    font-size: 0.8rem;
}

.inline-comment-form .btn-sm {
    border-radius: 4px;
    font-size: 0.8rem;
    padding: 4px 12px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .inline-comments-section {
        padding: 10px;
        margin-top: 8px;
    }
    
    .comment-item {
        padding: 8px;
    }
    
    .inline-comment-form {
        padding: 10px;
    }
    
    .inline-comment-form .row {
        gap: 10px;
    }
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

.poll-option-with-count {
    background: white;
    padding: 8px 12px;
    border-radius: 5px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.poll-option-with-count:hover {
    border-color: #007bff;
    background: #f8f9ff;
}

.poll-vote-count {
    font-size: 0.75rem;
    font-weight: 500;
    background: linear-gradient(45deg, #6c757d, #495057) !important;
    border: none;
    padding: 4px 8px;
    border-radius: 12px;
}

.poll-vote-count:hover {
    background: linear-gradient(45deg, #5a6268, #343a40) !important;
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

/* Advanced Weather Widget Styles */
.advanced-weather-widget {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.current-weather-card {
    background: rgba(255, 255, 255, 0.95);
    margin: 15px;
    border-radius: 12px;
    padding: 20px;
    backdrop-filter: blur(10px);
}

.weather-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.location-info h5 {
    color: #2c3e50;
    font-weight: 600;
}

.weather-icon-main {
    font-size: 1.8rem;
}

.weather-main {
    text-align: center;
    margin-bottom: 25px;
}

.temperature-display {
    margin-bottom: 10px;
}

.temp-main {
    font-size: 3rem;
    font-weight: 700;
    color: #2c3e50;
    display: block;
}

.temp-feels {
    font-size: 0.9rem;
    color: #6c757d;
}

.weather-desc {
    font-size: 1.1rem;
    color: #495057;
    font-weight: 500;
}

.weather-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.detail-item:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateY(-2px);
}

.detail-item i {
    font-size: 1rem;
}

.detail-item div {
    flex: 1;
}

.detail-item small {
    display: block;
    color: #6c757d;
    font-size: 0.75rem;
    margin-bottom: 2px;
}

.detail-item span {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.sun-times {
    display: flex;
    justify-content: space-between;
    padding: 15px;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 8px;
}

.sun-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sun-item i {
    font-size: 1rem;
}

.sun-item div {
    flex: 1;
}

.sun-item small {
    display: block;
    color: #6c757d;
    font-size: 0.75rem;
}

.sun-item span {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.hourly-forecast {
    background: rgba(255, 255, 255, 0.9);
    margin: 0 15px 15px;
    border-radius: 12px;
    padding: 15px;
}

.forecast-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 0.95rem;
}

.hourly-scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.hourly-scroll::-webkit-scrollbar {
    height: 4px;
}

.hourly-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.hourly-scroll::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.hour-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 60px;
    padding: 10px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 8px;
    text-align: center;
    transition: all 0.3s ease;
}

.hour-item:hover {
    background: rgba(255, 255, 255, 1);
    transform: translateY(-2px);
}

.hour-item small {
    color: #6c757d;
    font-size: 0.7rem;
    margin-bottom: 5px;
}

.hour-item span {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.85rem;
    margin-top: 5px;
}

.weather-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    padding: 0 15px 15px;
}

.weather-actions .btn {
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.weather-actions .btn:hover {
    transform: translateY(-1px);
}

.weather-widget-error {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 30px 20px;
    text-align: center;
    margin: 15px;
}

.weather-widget-error i {
    opacity: 0.6;
}

.weather-widget-error h6 {
    color: #6c757d;
    margin-bottom: 10px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .temp-main {
        font-size: 2.5rem;
    }
    
    .weather-details-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .hourly-scroll {
        gap: 8px;
    }
    
    .hour-item {
        min-width: 50px;
        padding: 8px;
    }
}

/* Facebook-Style Social Actions */
.facebook-social-actions {
    background: #f0f2f5;
    border-radius: 8px;
    padding: 8px 12px;
    margin-top: 12px;
}

.like-summary {
    padding: 6px 0;
    cursor: pointer;
    transition: background-color 0.2s;
    border-radius: 4px;
}

.like-summary:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.like-reactions {
    display: flex;
    align-items: center;
}

.reaction-icon {
    transition: transform 0.2s;
}

.reaction-icon:hover {
    transform: scale(1.1);
}

.facebook-action-buttons {
    border-radius: 0 0 8px 8px;
}

.facebook-action-btn {
    background: none;
    border: none;
    padding: 8px 16px;
    font-weight: 600;
    color: #65676b;
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 0;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 15px;
}

.facebook-action-btn:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.facebook-action-btn.like-btn:hover {
    background-color: #e7f3ff;
    color: #1877f2;
}

.facebook-action-btn.like-btn.liked {
    color: #1877f2;
    background-color: #e7f3ff;
}

.facebook-action-btn.like-btn.liked i {
    animation: likeAnimation 0.3s ease;
}

@keyframes likeAnimation {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

.facebook-action-btn.comment-btn:hover {
    background-color: #e4e6ea;
    color: #050505;
}

.facebook-action-btn.share-btn:hover {
    background-color: #e4e6ea;
    color: #050505;
}

.facebook-action-btn i {
    font-size: 18px;
}

.btn-text {
    font-size: 15px;
}

/* Facebook-Style Comments Section */
.facebook-comments-section {
    background: #f0f2f5;
    border-radius: 0 0 8px 8px;
    margin-top: -1px;
    padding: 0 12px 12px;
}

.comments-loading {
    padding: 20px 0;
}

.comments-list {
    max-height: 400px;
    overflow-y: auto;
}

.facebook-comment-form {
    padding-top: 8px;
    border-top: 1px solid #e4e6ea;
    margin-top: 8px;
}

.user-avatar img {
    background-color: #e4e6ea;
}

.comment-input {
    background: #f0f2f5;
    border: 1px solid #ccd0d5;
    transition: all 0.2s ease;
}

.comment-input:focus {
    background: white;
    border-color: #1877f2;
    outline: none;
    box-shadow: 0 0 0 2px rgba(24, 119, 254, 0.2);
}

.comment-input-wrapper {
    position: relative;
}

.comment-actions {
    animation: slideDown 0.2s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.post-comment-btn {
    background: #1877f2;
    border: none;
    padding: 6px 16px;
    border-radius: 6px;
    font-weight: 600;
}

.post-comment-btn:disabled {
    background: #e4e6ea;
    color: #8a8d91;
}

/* Share Options Modal */
.share-options-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.2s ease;
}

.share-options-content {
    background: white;
    border-radius: 12px;
    padding: 20px;
    max-width: 400px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.share-option-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.share-option-btn:hover {
    background-color: #f0f2f5;
}

.share-option-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
}

.facebook-icon { background: #1877f2; }
.twitter-icon { background: #1da1f2; }
.whatsapp-icon { background: #25d366; }
.email-icon { background: #ea4335; }
.link-icon { background: #65676b; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .facebook-action-btn {
        padding: 8px 12px;
        font-size: 14px;
    }
    
    .facebook-action-btn i {
        font-size: 16px;
    }
    
    .btn-text {
        font-size: 14px;
    }
    
    .share-options-content {
        width: 95%;
        padding: 16px;
    }
}
</style>


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

// Advanced Weather Widget Functions
function refreshWeather() {
    const weatherWidget = document.querySelector('.advanced-weather-widget');
    if (!weatherWidget) return;
    
    // Show loading state
    weatherWidget.style.opacity = '0.6';
    weatherWidget.style.pointerEvents = 'none';
    
    // Create loading indicator
    const loadingHtml = `
        <div class="weather-loading text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading weather...</span>
            </div>
            <p class="mt-2 text-muted small">Updating weather data...</p>
        </div>
    `;
    
    const currentContent = weatherWidget.innerHTML;
    weatherWidget.innerHTML = loadingHtml;
    
    // Fetch fresh weather data
    const weatherUrl = '<?php echo SITE_URL; ?>/index.php?refresh_weather=1';
    fetch(weatherUrl)
        .then(response => response.text())
        .then(html => {
            // Parse the response and extract the updated weather widget
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newWeatherWidget = doc.querySelector('.advanced-weather-widget');
            
            if (newWeatherWidget) {
                weatherWidget.innerHTML = newWeatherWidget.innerHTML;
                // Re-initialize any weather-related functionality
                initializeWeatherWidget();
            } else {
                // Fallback: restore original content and show error
                weatherWidget.innerHTML = currentContent;
                showWeatherError('Failed to refresh weather data');
            }
        })
        .catch(error => {
            console.error('Weather refresh error:', error);
            weatherWidget.innerHTML = currentContent;
            showWeatherError('Network error while refreshing weather');
        })
        .finally(() => {
            weatherWidget.style.opacity = '1';
            weatherWidget.style.pointerEvents = 'auto';
        });
}

function showWeatherError(message) {
    const weatherWidget = document.querySelector('.advanced-weather-widget');
    if (!weatherWidget) return;
    
    const errorHtml = `
        <div class="weather-widget-error">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                <h6>Weather Update Failed</h6>
                <p class="text-muted small">${message}</p>
                <button onclick="refreshWeather()" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-redo me-1"></i>Try Again
                </button>
            </div>
        </div>
    `;
    
    weatherWidget.innerHTML = errorHtml;
}

function initializeWeatherWidget() {
    // Add hover effects to detail items
    const detailItems = document.querySelectorAll('.detail-item');
    detailItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Add smooth scrolling to hourly forecast
    const hourlyScroll = document.querySelector('.hourly-scroll');
    if (hourlyScroll) {
        let isDown = false;
        let startX;
        let scrollLeft;
        
        hourlyScroll.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - hourlyScroll.offsetLeft;
            scrollLeft = hourlyScroll.scrollLeft;
        });
        
        hourlyScroll.addEventListener('mouseleave', () => {
            isDown = false;
        });
        
        hourlyScroll.addEventListener('mouseup', () => {
            isDown = false;
        });
        
        hourlyScroll.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - hourlyScroll.offsetLeft;
            const walk = (x - startX) * 2;
            hourlyScroll.scrollLeft = scrollLeft - walk;
        });
    }
    
    // Auto-refresh weather every 10 minutes
    setTimeout(() => {
        if (document.querySelector('.advanced-weather-widget')) {
            refreshWeather();
        }
    }, 10 * 60 * 1000); // 10 minutes
}

// Initialize weather widget when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.advanced-weather-widget')) {
        initializeWeatherWidget();
    }
});

// Handle URL parameter for weather refresh
if (window.location.search.includes('refresh_weather=1')) {
    // Remove the parameter from URL without page reload
    const url = new URL(window.location);
    url.searchParams.delete('refresh_weather');
    window.history.replaceState({}, '', url);
}

// Add event listeners for inline comment forms
document.addEventListener('DOMContentLoaded', function() {
    // Handle inline comment form submissions
    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('inline-comment-submit')) {
            e.preventDefault();
            const newsId = e.target.dataset.newsId;
            submitInlineComment(newsId, e.target);
        }
    });
});
</script>

</body>
</html>
