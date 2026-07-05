<?php
require_once 'config/database.php';
require_once 'config/weather.php';
require_once 'includes/language_functions.php';
require_once 'includes/ai_fake_news_detector.php';
require_once 'includes/html_encoding_helper.php';

// Function to generate video embed HTML
function generateVideoEmbed($videoUrl, $videoPath = null, $autoplay = false) {
    if ($videoPath) {
        // Handle uploaded video
        return '<div class="video-embed-container">
                    <video controls ' . ($autoplay ? 'autoplay muted' : '') . ' style="width: 100%; height: auto; border-radius: 8px;">
                        <source src="' . htmlspecialchars($videoPath) . '" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>';
    }
    
    if ($videoUrl) {
        // Handle YouTube URLs
        $videoId = '';
        if (strpos($videoUrl, 'youtube.com/watch?v=') !== false) {
            $videoId = substr($videoUrl, strpos($videoUrl, 'v=') + 2);
            $videoId = explode('?', $videoId)[0];
        } elseif (strpos($videoUrl, 'youtu.be/') !== false) {
            $videoId = substr($videoUrl, strpos($videoUrl, 'youtu.be/') + 9);
            $videoId = explode('?', $videoId)[0];
        }
        
        if ($videoId) {
            $autoplayParam = $autoplay ? '?autoplay=1&mute=1&rel=0' : '?rel=0';
            return '<div class="video-embed-container">
                        <iframe src="https://www.youtube.com/embed/' . $videoId . $autoplayParam . '" 
                                allowfullscreen 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                style="width: 100%; height: 400px; border: none; border-radius: 8px;">
                        </iframe>
                    </div>';
        }
        
        // Handle Vimeo URLs
        if (strpos($videoUrl, 'vimeo.com/') !== false) {
            $videoId = explode('vimeo.com/', $videoUrl)[1];
            $videoId = explode('?', $videoId)[0];
            if ($videoId) {
                $autoplayParam = $autoplay ? '?autoplay=1&muted=1' : '';
                return '<div class="video-embed-container">
                            <iframe src="https://player.vimeo.com/video/' . $videoId . $autoplayParam . '" 
                                    allowfullscreen 
                                    allow="autoplay; fullscreen; picture-in-picture"
                                    style="width: 100%; height: 400px; border: none; border-radius: 8px;">
                            </iframe>
                        </div>';
            }
        }
    }
    
    return ''; // Return empty if no valid video
}

// Function to get source icon based on source name
function getSourceIcon($sourceName) {
    $sourceIcons = [
        'PK-LIVE' => '<i class="fas fa-home" style="color: white; font-size: 0.7rem;" title="PK-LIVE"></i>',
        'BBC News' => '<i class="fas fa-broadcast-tower text-primary" title="BBC News"></i>',
        'CNN' => '<i class="fas fa-satellite-dish text-danger" title="CNN"></i>',
        'ARY News' => '<i class="fas fa-tv text-success" title="ARY News"></i>',
        'Dawn News' => '<i class="fas fa-newspaper text-info" title="Dawn News"></i>',
        'Geo News' => '<i class="fas fa-globe text-warning" title="Geo News"></i>',
        'Express Tribune' => '<i class="fas fa-exclamation-triangle text-secondary" title="Express Tribune"></i>',
        'Al Jazeera' => '<i class="fas fa-globe-americas text-primary" title="Al Jazeera"></i>',
        'Reuters' => '<i class="fas fa-chart-line text-success" title="Reuters"></i>',
        'Associated Press' => '<i class="fas fa-newspaper text-dark" title="Associated Press"></i>',
        'AFP' => '<i class="fas fa-flag text-danger" title="AFP"></i>',
        'Bloomberg' => '<i class="fas fa-chart-bar text-info" title="Bloomberg"></i>',
        'The Guardian' => '<i class="fas fa-shield-alt text-primary" title="The Guardian"></i>',
        'New York Times' => '<i class="fas fa-building text-dark" title="New York Times"></i>',
        'Washington Post' => '<i class="fas fa-monument text-secondary" title="Washington Post"></i>',
        'Fox News' => '<i class="fas fa-fox text-orange" title="Fox News"></i>',
        'MSNBC' => '<i class="fas fa-peacock text-info" title="MSNBC"></i>',
        'CNBC' => '<i class="fas fa-chart-line text-success" title="CNBC"></i>',
        'Financial Times' => '<i class="fas fa-chart-area text-warning" title="Financial Times"></i>',
        'Wall Street Journal' => '<i class="fas fa-building text-dark" title="Wall Street Journal"></i>'
    ];
    
    return $sourceIcons[$sourceName] ?? '<i class="fas fa-rss text-muted" title="' . htmlspecialchars($sourceName) . '"></i>';
}

// Get news slug from URL
$slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';

if (empty($slug)) {
    redirect('index.php');
}

// Initialize detector with existing database connection
$detector = new AIFakeNewsDetector($conn);

// Get news article with credibility data
$query = "SELECT n.*, c.name as category_name, c.slug as category_slug, u.name as author_name, u.email as author_email,
          nca.credibility_score, nca.risk_level, nca.content_category, nca.requires_review, nca.source_verified,
          nca.analysis_date, nca.confidence_level,
          COALESCE(n.published_at, n.created_at) as real_post_time
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          LEFT JOIN users u ON n.author_id = u.id 
          LEFT JOIN news_credibility_analysis nca ON n.id = nca.news_id
          WHERE n.slug = ? AND n.status = 'published'";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    redirect('index.php');
}

$news = mysqli_fetch_assoc($result);

// If no credibility analysis exists, trigger one
if (empty($news['credibility_score'])) {
    $detector->analyzeArticle($news['id']);
    // Get updated data
    $analysis = $detector->getCredibilityReport($news['id']);
    if ($analysis) {
        $news['credibility_score'] = $analysis['credibility_score'];
        $news['risk_level'] = $analysis['risk_level'];
        $news['content_category'] = $analysis['content_category'];
        $news['requires_review'] = $analysis['requires_review'];
        $news['source_verified'] = $analysis['source_verified'];
        $news['analysis_date'] = $analysis['analysis_date'];
        $news['confidence_level'] = $analysis['confidence_level'];
    }
}

// Update view count
mysqli_query($conn, "UPDATE news SET views = views + 1 WHERE id = " . $news['id']);

// Get related news (same category) - more recent first
$related_query = "SELECT *, COALESCE(published_at, created_at) as real_post_time FROM news 
                 WHERE category_id = ? AND id != ? AND status = 'published' 
                 ORDER BY real_post_time DESC LIMIT 6";
$related_stmt = mysqli_prepare($conn, $related_query);
mysqli_stmt_bind_param($related_stmt, 'ii', $news['category_id'], $news['id']);
mysqli_stmt_execute($related_stmt);
$related_result = mysqli_stmt_get_result($related_stmt);

// Convert result to array for consistent handling
$related_news_array = mysqli_fetch_all($related_result, MYSQLI_ASSOC);
$related_count = count($related_news_array);

// If not enough related news from same category, get recent news as fallback
if ($related_count < 4) {
    $fallback_query = "SELECT *, COALESCE(published_at, created_at) as real_post_time FROM news 
                      WHERE id != ? AND status = 'published' AND category_id != ?
                      ORDER BY real_post_time DESC LIMIT " . (4 - $related_count);
    $fallback_stmt = mysqli_prepare($conn, $fallback_query);
    mysqli_stmt_bind_param($fallback_stmt, 'ii', $news['id'], $news['category_id']);
    mysqli_stmt_execute($fallback_stmt);
    $fallback_result = mysqli_stmt_get_result($fallback_stmt);
    
    // Combine results
    while ($row = mysqli_fetch_assoc($fallback_result)) {
        $related_news_array[] = $row;
    }
}

// Use the array format consistently
$related_result = $related_news_array;

// Get comments
$comments_query = "SELECT * FROM comments WHERE news_id = ? AND status = 'approved' AND parent_id IS NULL ORDER BY created_at DESC";
$comments_stmt = mysqli_prepare($conn, $comments_query);
mysqli_stmt_bind_param($comments_stmt, 'i', $news['id']);
mysqli_stmt_execute($comments_stmt);
$comments_result = mysqli_stmt_get_result($comments_stmt);

// Handle comment submission
$comment_error = '';
$comment_success = '';

// Handle main comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $comment = clean_input($_POST['comment']);
    
    // Check if user is logged in
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    
    if (empty($comment)) {
        $comment_error = 'Comment is required';
    } elseif (!is_logged_in() && (empty($name) || empty($email))) {
        $comment_error = 'Name and email are required for guest comments';
    } elseif (!is_logged_in() && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $comment_error = 'Invalid email address';
    } else {
        // If user is logged in, use their info
        if (is_logged_in()) {
            $user_query = "SELECT name, email FROM users WHERE id = ?";
            $user_stmt = mysqli_prepare($conn, $user_query);
            mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
            mysqli_stmt_execute($user_stmt);
            $user_result = mysqli_stmt_get_result($user_stmt);
            $user_data = mysqli_fetch_assoc($user_result);
            
            if ($user_data) {
                $name = $user_data['name'];
                $email = $user_data['email'];
            }
        }
        
        $insert_query = "INSERT INTO comments (news_id, user_id, name, email, comment, status) VALUES (?, ?, ?, ?, ?, 'pending')";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'iisss', $news['id'], $user_id, $name, $email, $comment);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $comment_success = 'Comment submitted successfully! It will be visible after approval.';
            // Clear comment form
            $_POST['name'] = $_POST['email'] = $_POST['comment'] = '';
        } else {
            $comment_error = 'Error submitting comment. Please try again.';
        }
    }
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_comment'])) {
    $parent_id = (int)$_POST['parent_id'];
    $reply_name = clean_input($_POST['reply_name']);
    $reply_email = clean_input($_POST['reply_email']);
    $reply_comment = clean_input($_POST['reply_comment']);
    
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    
    if (empty($reply_comment)) {
        $comment_error = 'Reply is required';
    } elseif (!is_logged_in() && (empty($reply_name) || empty($reply_email))) {
        $comment_error = 'Name and email are required for guest replies';
    } elseif (!is_logged_in() && !filter_var($reply_email, FILTER_VALIDATE_EMAIL)) {
        $comment_error = 'Invalid email address';
    } else {
        // If user is logged in, use their info
        if (is_logged_in()) {
            $user_query = "SELECT name, email FROM users WHERE id = ?";
            $user_stmt = mysqli_prepare($conn, $user_query);
            mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
            mysqli_stmt_execute($user_stmt);
            $user_result = mysqli_stmt_get_result($user_stmt);
            $user_data = mysqli_fetch_assoc($user_result);
            
            if ($user_data) {
                $reply_name = $user_data['name'];
                $reply_email = $user_data['email'];
            }
        }
        
        $status = is_admin() ? 'approved' : 'pending';
        $insert_reply_query = "INSERT INTO comments (news_id, parent_id, user_id, name, email, comment, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_reply_stmt = mysqli_prepare($conn, $insert_reply_query);
        mysqli_stmt_bind_param($insert_reply_stmt, 'iissss', $news['id'], $parent_id, $user_id, $reply_name, $reply_email, $reply_comment, $status);
        
        if (mysqli_stmt_execute($insert_reply_stmt)) {
            $comment_success = $status === 'approved' ? 'Reply posted successfully!' : 'Reply submitted for approval.';
            // Clear reply form
            $_POST['reply_comment'] = '';
        } else {
            $comment_error = 'Error submitting reply. Please try again.';
        }
    }
}

$page_title = htmlspecialchars(get_news_title($news));

// SEO Meta Tags
$meta_description = htmlspecialchars(strip_tags(substr($news['content'], 0, 160)));
$meta_keywords = htmlspecialchars($news['category_name'] . ', news, ' . htmlspecialchars($news['title']));
$canonical_url = SITE_URL . 'news.php?slug=' . $news['slug'];
$og_image = !empty($news['image']) ? SITE_URL . $news['image'] : SITE_URL . 'assets/images/default-news.jpg';


// Get weather data for news page
$weatherData = null;
$defaultCity = getUserLocationCity();
$weatherData = getWeatherData($defaultCity, 'metric');
if ($weatherData) {
    $weatherData = formatWeatherData($weatherData);
}

// Helper functions for credibility display
function getCredibilityScoreColor($score) {
    if ($score >= 80) return 'success';
    if ($score >= 60) return 'info';
    if ($score >= 40) return 'warning';
    return 'danger';
}

function getCredibilityRiskColor($riskLevel) {
    $colors = [
        'LOW' => 'success',
        'MEDIUM' => 'info',
        'HIGH' => 'warning',
        'CRITICAL' => 'danger'
    ];
    return $colors[$riskLevel] ?? 'secondary';
}
?>

<?php include 'includes/header.php'; ?>

<!-- SEO Meta Tags -->
<meta name="description" content="<?php echo $meta_description; ?>">
<meta name="keywords" content="<?php echo $meta_keywords; ?>">
<link rel="canonical" href="<?php echo $canonical_url; ?>">

<!-- Open Graph Meta Tags -->
<meta property="og:title" content="<?php echo $page_title; ?>">
<meta property="og:description" content="<?php echo $meta_description; ?>">
<meta property="og:image" content="<?php echo $og_image; ?>">
<meta property="og:url" content="<?php echo $canonical_url; ?>">
<meta property="og:type" content="article">
<meta property="og:site_name" content="PK Live News">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo $page_title; ?>">
<meta name="twitter:description" content="<?php echo $meta_description; ?>">
<meta name="twitter:image" content="<?php echo $og_image; ?>">


<!-- Real-time Interactions CSS -->
<link rel="stylesheet" href="assets/css/realtime-interactions.css">
<!-- Comments CSS -->
<link rel="stylesheet" href="assets/css/comments.css">

<!-- News Detail Section -->
<section class="news-detail-section py-4">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item">
                    <a href="category.php?slug=<?php echo $news['category_slug']; ?>" class="text-decoration-none">
                        <?php echo htmlspecialchars($news['category_name'] ?? 'Category'); ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($news['title']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Main Article Content -->
            <div class="col-lg-8">
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="card border-0 shadow news-card">
                            <?php if ($news['video_url']): ?>
                            <div class="position-relative">
                                <?php echo generateVideoEmbed($news['video_url'], null, false); ?>
                                
                                
                                <!-- Views Badge on Top -->
                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-dark bg-opacity-75 text-white">
                                        <i class="fas fa-eye me-1"></i> <?php echo number_format($news['views']); ?>
                                    </span>
                                </div>
                                
                                <!-- News Status Badges -->
                                <div class="position-absolute top-0 end-0 m-2">
                                     <?php 
                                    // Calculate time status based on publication date
                                    $now = new DateTime();
                                    $post_date = new DateTime($news['published_at'] ?? $news['created_at']);
                                    $interval = $now->diff($post_date);
                                    $time_status = ($interval->days <= 1) ? 'new' : (($interval->days <= 7) ? 'recent' : 'old');
                                    ?>
                                    <?php if ($time_status === 'new'): ?>
                                        <span class="badge bg-secondary animate-pulse">
                                            <i class="fas fa-sparkles me-1"></i>NEW
                                        </span>
                                  <?php elseif ($time_status === 'recent'): ?>
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
                                   <?php 
                                    // Calculate time status based on publication date
                                    $now = new DateTime();
                                    $post_date = new DateTime($news['published_at'] ?? $news['created_at']);
                                    $interval = $now->diff($post_date);
                                    $time_status = ($interval->days <= 1) ? 'new' : (($interval->days <= 7) ? 'recent' : 'old');
                                    ?>
                                    <?php if ($time_status === 'new'): ?>
                                        <span class="badge bg-secondary animate-pulse">
                                            <i class="fas fa-sparkles me-1"></i>NEW
                                        </span>
                                    <?php elseif ($time_status === 'recent'): ?>
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
                                    <?php 
                                    // Calculate time status based on publication date
                                    $post_time = !empty($news['published_at']) ? $news['published_at'] : $news['created_at'];
                                    $now = new DateTime();
                                    $post_date = new DateTime($post_time);
                                    $interval = $now->diff($post_date);
                                    $time_status = ($interval->days <= 1) ? 'new' : (($interval->days <= 7) ? 'recent' : 'old');

                                    if ($time_status === 'new'): 
                                    ?>
                                        <span class="badge bg-secondary animate-pulse">
                                            <i class="fas fa-sparkles me-1"></i>NEW
                                        </span>
                                    <?php elseif ($time_status === 'recent'): ?>
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
                                        $sentiment_icons = ['positive' => '???', 'negative' => '???', 'neutral' => '???'];
                                        $color = $sentiment_colors[$news['sentiment_label']] ?? 'secondary';
                                        $icon = $sentiment_icons[$news['sentiment_label']] ?? '???';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?> ms-1" title="Sentiment: <?php echo $news['sentiment_label']; ?> (Score: <?php echo $news['sentiment_score']; ?>)">
                                            <?php echo $icon; ?> <?php echo ucfirst($news['sentiment_label']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h5 class="card-title">
                                    <?php echo display_news_title($news); ?>
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
                                <div class="card-text text-muted">
                                    <?php echo format_news_content($news['content'] ?? ''); ?>
                                </div>
                                
                                <!-- Share Buttons -->
                                <div class="share-section mt-4">
                                    <div class="share-buttons">
                                        <a href="#" onclick="shareOnFacebook('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo display_news_title($news); ?>')" class="share-btn share-facebook">
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                        <a href="#" onclick="shareOnTwitter('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo display_news_title($news); ?>')" class="share-btn share-twitter">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                        <a href="#" onclick="shareOnWhatsApp('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo display_news_title($news); ?>')" class="share-btn share-whatsapp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                        <a href="#" onclick="shareOnLinkedIn('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo display_news_title($news); ?>')" class="share-btn share-linkedin">
                                            <i class="fab fa-linkedin-in"></i>
                                        </a>
                                        <a href="#" onclick="shareOnTelegram('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo display_news_title($news); ?>')" class="share-btn share-telegram">
                                            <i class="fab fa-telegram-plane"></i>
                                        </a>
                                        <a href="#" onclick="shareViaEmail('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo display_news_title($news); ?>')" class="share-btn share-email">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                        <button onclick="copyToClipboard('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>')" class="share-btn bg-secondary">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Tags -->
                                <?php
                                $tags = ['politics', 'breaking news', 'pakistan']; // Example tags
                                if (!empty($tags)):
                                ?>
                                    <div class="tags-section mt-4">
                                        <div class="tag-cloud">
                                            <?php foreach ($tags as $tag): ?>
                                                <a href="search.php?q=<?php echo urlencode($tag); ?>" class="badge bg-light text-dark text-decoration-none me-2 mb-2">
                                                    #<?php echo htmlspecialchars($tag); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <section class="comments-section mt-5">
                    <h4 class="mb-4">
                        Comments (<span class="comments-count"><?php echo mysqli_num_rows($comments_result); ?></span>)
                    </h4>

                    <!-- Comment Form -->
                    <div class="comment-form">
                        <h5 class="mb-3">Leave a Comment</h5>
                        
                        <?php if ($comment_success): ?>
                            <div class="alert alert-success"><?php echo $comment_success; ?></div>
                        <?php endif; ?>

                        <?php if ($comment_error): ?>
                            <div class="alert alert-danger"><?php echo $comment_error; ?></div>
                        <?php endif; ?>

                        <form id="main-comment-form" data-news-id="<?php echo $news['id']; ?>">
                            <?php if (is_logged_in()): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-user-circle me-2"></i>
                                    Commenting as <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Logged in user'); ?>
                                    <?php if (is_admin()): ?>
                                        <i class="fas fa-star text-warning ms-1" title="Admin User"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comment *</label>
                                <textarea class="form-control" id="comment" name="comment" rows="4" required placeholder="Write your comment here..."><?php echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>
                            </div>
                            <button type="submit" name="submit_comment" class="btn btn-danger">
                                <i class="fas fa-paper-plane me-2"></i>Post Comment
                            </button>
                        </form>
                    </div>

                    <!-- Comments List -->
                    <div class="comments-list mt-5">
                        <?php if (mysqli_num_rows($comments_result) > 0): ?>
                            <?php 
                            // Reset result pointer
                            mysqli_data_seek($comments_result, 0);
                            while ($comment = mysqli_fetch_assoc($comments_result)): 
                            ?>
                                <div class="comment-item mb-4 p-3 border rounded" id="comment-<?php echo $comment['id']; ?>">
                                    <div class="d-flex">
                                        <div class="comment-avatar me-3">
                                            <?php if ($comment['user_id']): ?>
                                                <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%;">
                                                    <?php echo strtoupper(substr($comment['name'], 0, 1)); ?>
                                                </div>
                                            <?php else: ?>
                                                <i class="fas fa-user-circle fa-3x text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="comment-author fw-bold">
                                                        <?php echo htmlspecialchars($comment['name']); ?>
                                                        <?php if ($comment['user_id'] && is_user_admin($comment['user_id'])): ?>
                                                            <span class="badge bg-warning text-dark ms-1">
                                                                <i class="fas fa-star"></i> Admin
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if ($comment['user_id'] && is_user_editor($comment['user_id'])): ?>
                                                            <span class="badge bg-info ms-1">
                                                                <i class="fas fa-edit"></i> Editor
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="comment-date text-muted small">
                                                        <span class="realtime-date" data-date="<?php echo $comment['created_at']; ?>">
                                                            <?php echo format_date_realtime($comment['created_at']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="comment-actions">
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">
                                                        <i class="fas fa-reply"></i> Reply
                                                    </button>
                                                    <?php if (is_admin()): ?>
                                                        <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteComment(<?php echo $comment['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="comment-content mt-2">
                                                <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                            </div>
                                            
                                            <!-- Reply Form (Hidden by default) -->
                                            <div class="reply-form mt-3" id="reply-form-<?php echo $comment['id']; ?>" style="display: none;">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title mb-3">Reply to <?php echo htmlspecialchars($comment['name']); ?></h6>
                                                        <form class="reply-form-data" data-news-id="<?php echo $news['id']; ?>" data-parent-id="<?php echo $comment['id']; ?>">
                                                            <div class="mb-2">
                                                                <textarea class="form-control form-control-sm" name="reply_comment" rows="3" placeholder="Write your reply..." required></textarea>
                                                            </div>
                                                            <div class="d-flex gap-2">
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-paper-plane"></i> Post Reply
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">
                                                                    Cancel
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Replies Section -->
                                            <div class="replies-section mt-3" id="replies-<?php echo $comment['id']; ?>">
                                                <?php
                                                // Get replies for this comment
                                                $replies_query = "SELECT * FROM comments WHERE parent_id = ? AND status = 'approved' ORDER BY created_at ASC";
                                                $replies_stmt = mysqli_prepare($conn, $replies_query);
                                                mysqli_stmt_bind_param($replies_stmt, 'i', $comment['id']);
                                                mysqli_stmt_execute($replies_stmt);
                                                $replies_result = mysqli_stmt_get_result($replies_stmt);
                                                
                                                if (mysqli_num_rows($replies_result) > 0):
                                                    while ($reply = mysqli_fetch_assoc($replies_result)):
                                                ?>
                                                    <div class="reply-item mb-2 p-2 bg-light rounded ms-4">
                                                        <div class="d-flex">
                                                            <div class="reply-avatar me-2">
                                                                <div class="avatar-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; border-radius: 50%; font-size: 12px;">
                                                                    <?php echo strtoupper(substr($reply['name'], 0, 1)); ?>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div>
                                                                        <div class="reply-author fw-bold small">
                                                                            <?php echo htmlspecialchars($reply['name']); ?>
                                                                            <?php if ($reply['user_id'] && is_user_admin($reply['user_id'])): ?>
                                                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">
                                                                                    <i class="fas fa-star"></i> Admin
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <div class="reply-date text-muted small">
                                                                            <span class="realtime-date" data-date="<?php echo $reply['created_at']; ?>">
                                                                                <?php echo format_date_realtime($reply['created_at']); ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <?php if (is_admin()): ?>
                                                                        <button class="btn btn-sm btn-outline-danger" style="font-size: 10px;" onclick="deleteComment(<?php echo $reply['id']; ?>)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="reply-content mt-1 small">
                                                                    <?php echo nl2br(htmlspecialchars($reply['comment'])); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php 
                                                    endwhile; 
                                                endif;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No comments yet. Be the first to comment!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Weather Widget -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-cloud-sun me-2"></i>Current Weather</h3>
                    <?php if ($weatherData): ?>
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
                                    <i class="fas fa-search me-1"></i>Full Forecast
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

                <!-- Related News -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-newspaper me-2"></i>Related News</h3>
                    <?php if (!empty($related_result) && count($related_result) > 0): ?>
                        <?php foreach ($related_result as $related): ?>
                            <div class="mb-3">
                                <div class="card border-0 shadow-sm">
                                    <div class="row g-0">
                                        <div class="col-md-4">
                                            <div class="image-container">
                                                <?php if ($related['image']): ?>
                                                    <img src="<?php echo htmlspecialchars($related['image']); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($related['title']); ?>" style="height: 80px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center rounded-start" style="height: 80px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body p-2">
                                                <h6 class="card-title">
                                                    <a href="news.php?slug=<?php echo $related['slug']; ?>" class="text-decoration-none text-dark">
                                                        <?php echo htmlspecialchars(substr($related['title'], 0, 50)) . '...'; ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo format_date_realtime($related['real_post_time']); ?>
                                                </small>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <i class="fas fa-eye me-1"></i><?php echo number_format($related['views'] ?? 0); ?> views
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No related news found</p>
                    <?php endif; ?>
                </div>

                <!-- Advertisement -->
                <?php
                require_once 'includes/ads_functions.php';
                displayAdWidget('sidebar');
                ?>

                <!-- Newsletter -->
                <div class="sidebar-widget">
                    <h3><i class="fas fa-envelope me-2"></i>Newsletter</h3>
                    <p>Subscribe to our newsletter for the latest news updates.</p>
                    <form class="newsletter-form">
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your email address" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Comment system functions
function toggleReplyForm(commentId) {
    const replyForm = document.getElementById(`reply-form-${commentId}`);
    if (replyForm.style.display === 'none') {
        replyForm.style.display = 'block';
        // Focus on the textarea
        replyForm.querySelector('textarea').focus();
    } else {
        replyForm.style.display = 'none';
    }
}

function deleteComment(commentId) {
    if (confirm('Are you sure you want to delete this comment?')) {
        fetch('/api/delete-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the comment from DOM
                const commentElement = document.getElementById(`comment-${commentId}`);
                if (commentElement) {
                    commentElement.remove();
                    showNotification('Comment deleted successfully', 'success');
                    // Update comment count
                    updateCommentCount();
                }
            } else {
                showNotification(data.message || 'Error deleting comment', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Network error. Please try again.', 'error');
        });
    }
}

function updateCommentCount() {
    // Count visible comments
    const visibleComments = document.querySelectorAll('.comment-item').length;
    const commentCountElement = document.querySelector('.comments-count');
    if (commentCountElement) {
        commentCountElement.textContent = visibleComments;
    }
}

// Handle main comment form submission
document.addEventListener('DOMContentLoaded', function() {
    const mainCommentForm = document.getElementById('main-comment-form');
    if (mainCommentForm) {
        mainCommentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
            
            // Get form data
            const newsId = this.dataset.newsId;
            const comment = this.querySelector('textarea[name="comment"]').value;
            
            // Debug logging
            console.log('Comment submission:', {
                newsId: newsId,
                comment: comment,
                newsIdType: typeof newsId,
                commentLength: comment ? comment.length : 0
            });
            
            // Validate data
            if (!newsId || !comment || comment.trim() === '') {
                showNotification('Please write a comment before submitting', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                return;
            }
            
            // Prepare API data
            const apiData = {
                news_id: parseInt(newsId),
                comment: comment.trim()
            };
            
            console.log('Sending API data:', apiData);
            
            // Try fetch API first
            fetch('api/submit-comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(apiData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Clear form
                    this.reset();
                    // Reload page to show new comment
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'Error posting comment', 'error');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                showNotification('Network error. Trying alternative method...', 'warning');
                
                // Fallback: try traditional form submission
                submitCommentFallback(newsId, comment.trim(), submitBtn, originalText);
            })
            .finally(() => {
                // Only reset button if not using fallback
                if (!submitBtn.dataset.fallback) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });
    }
});

// Fallback comment submission method
function submitCommentFallback(newsId, comment, submitBtn, originalText) {
    console.log('Using fallback submission method');
    
    // Create a hidden form for fallback submission
    const fallbackForm = document.createElement('form');
    fallbackForm.method = 'POST';
    fallbackForm.action = 'api/submit-comment.php';
    fallbackForm.style.display = 'none';
    
    // Add form fields
    const newsIdField = document.createElement('input');
    newsIdField.type = 'hidden';
    newsIdField.name = 'news_id';
    newsIdField.value = newsId;
    
    const commentField = document.createElement('input');
    commentField.type = 'hidden';
    commentField.name = 'comment';
    commentField.value = comment;
    
    fallbackForm.appendChild(newsIdField);
    fallbackForm.appendChild(commentField);
    
    // Add to page and submit
    document.body.appendChild(fallbackForm);
    
    // Mark as fallback
    submitBtn.dataset.fallback = 'true';
    
    // Submit the form
    fallbackForm.submit();
    
    // Show notification
    showNotification('Submitting comment...', 'info');
    
    // Clean up after a delay
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        delete submitBtn.dataset.fallback;
        if (document.body.contains(fallbackForm)) {
            document.body.removeChild(fallbackForm);
        }
    }, 3000);
}

// Handle reply form submissions
document.addEventListener('DOMContentLoaded', function() {
    const replyForms = document.querySelectorAll('.reply-form-data');
    replyForms.forEach(form => {
        let isSubmitting = false;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Prevent double submission
            if (isSubmitting) {
                return;
            }
            isSubmitting = true;
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
            
            // Get form data
            const newsId = this.dataset.newsId;
            const parentId = this.dataset.parentId;
            const comment = this.querySelector('textarea[name="reply_comment"]').value;
            
            // Validate data
            if (!newsId || !parentId || !comment || comment.trim() === '') {
                showNotification('Please write a reply before submitting', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                return;
            }
            
            // Prepare API data
            const apiData = {
                news_id: parseInt(newsId),
                comment: comment.trim(),
                parent_id: parseInt(parentId)
            };
            
            fetch('api/submit-comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(apiData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Clear form and hide it
                    this.reset();
                    toggleReplyForm(parseInt(parentId));
                    // Reload page to show new reply
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'Error posting reply', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Network error. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                isSubmitting = false;
            });
        });
    });
});

// Font size controls are already implemented in main.js

// Newsletter form submission
document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;
    
    // Here you would normally send this to your backend
    showNotification('Thank you for subscribing! Please check your email.', 'success');
    this.reset();
});

// Real-time Interaction System
const newsId = <?php echo $news['id']; ?>;
let isLiked = false;

// Initialize interaction stats
function initInteractions() {
    updateInteractionStats();
    // Update stats every 30 seconds
    setInterval(updateInteractionStats, 30000);
}

// Toggle like/unlike
function toggleLike(newsId) {
    const btn = document.getElementById(`likeBtn-${newsId}`);
    const icon = btn.querySelector('i');
    
    // Show loading state
    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin me-2';
    
    fetch('api/news_interactions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=like&news_id=${newsId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatsDisplay(data);
            isLiked = data.liked;
            
            // Update button state
            if (isLiked) {
                btn.classList.remove('btn-outline-danger');
                btn.classList.add('btn-danger');
                icon.className = 'fas fa-heart me-2';
                showNotification('Article liked!', 'success');
            } else {
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-outline-danger');
                icon.className = 'fas fa-heart me-2';
                showNotification('Article unliked', 'info');
            }
        } else {
            showNotification(data.message || 'Error updating like', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Network error. Please try again.', 'error');
    })
    .finally(() => {
        btn.disabled = false;
    });
}

// Track share
function trackShare(newsId, platform) {
    fetch('api/news_interactions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=share&news_id=${newsId}&platform=${platform}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatsDisplay(data);
            showNotification('Article shared!', 'success');
        }
    })
    .catch(error => {
        console.error('Error tracking share:', error);
    });
}

// Update interaction stats
function updateInteractionStats() {
    fetch('api/news_interactions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get_stats&news_id=${newsId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatsDisplay(data);
            isLiked = data.user_liked || false;
            updateLikeButton();
        }
    })
    .catch(error => {
        console.error('Error updating stats:', error);
    });
}

// Update stats display
function updateStatsDisplay(data) {
    const likeCount = document.querySelector('.like-count');
    const viewCount = document.querySelector('.view-count');
    const shareCount = document.querySelector('.share-count');
    const commentCount = document.querySelector('.comment-count');
    
    if (likeCount) likeCount.textContent = numberFormat(data.likes || 0);
    if (viewCount) viewCount.textContent = numberFormat(data.views || 0);
    if (shareCount) shareCount.textContent = numberFormat(data.shares || 0);
    if (commentCount) commentCount.textContent = numberFormat(data.comments || 0);
}

// Update like button state
function updateLikeButton() {
    const btn = document.getElementById(`likeBtn-${newsId}`);
    if (isLiked) {
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-danger');
    } else {
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-outline-danger');
    }
}

// Format numbers
function numberFormat(num) {
    return new Intl.NumberFormat().format(num);
}

// Enhanced share functions with tracking
function shareOnFacebook(url, title) {
    trackShare(newsId, 'facebook');
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter(url, title) {
    trackShare(newsId, 'twitter');
    window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank', 'width=600,height=400');
}

function shareOnWhatsApp(url, title) {
    trackShare(newsId, 'whatsapp');
    window.open(`https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`, '_blank');
}

function shareOnLinkedIn(url, title) {
    trackShare(newsId, 'linkedin');
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
}

function shareOnTelegram(url, title) {
    trackShare(newsId, 'telegram');
    window.open(`https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank');
}

function shareViaEmail(url, title) {
    trackShare(newsId, 'email');
    window.location.href = `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(title + '\n\n' + url)}`;
}

function copyToClipboard(url) {
    trackShare(newsId, 'copy');
    navigator.clipboard.writeText(url).then(() => {
        showNotification('Link copied to clipboard!', 'success');
    }).catch(() => {
        showNotification('Failed to copy link', 'error');
    });
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Video sharing functions
function shareVideoOnFacebook(url, title) {
    trackShare(newsId, 'facebook');
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
}

function shareVideoOnTwitter(url, title) {
    trackShare(newsId, 'twitter');
    window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank', 'width=600,height=400');
}

function shareVideoOnWhatsApp(url, title) {
    trackShare(newsId, 'whatsapp');
    window.open(`https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`, '_blank');
}

function shareVideoOnTelegram(url, title) {
    trackShare(newsId, 'telegram');
    window.open(`https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank');
}

function copyVideoUrl(url) {
    trackShare(newsId, 'copy');
    navigator.clipboard.writeText(url).then(() => {
        showNotification('Video link copied to clipboard!', 'success');
    }).catch(() => {
        showNotification('Failed to copy video link', 'error');
    });
}

// Fix HTML entity encoding issues
function fixHTMLEntities() {
    const content = document.querySelector('.news-detail-content');
    if (!content) return;
    
    // Get the HTML content
    let html = content.innerHTML;
    
    // Fix common HTML entity issues
    html = html.replace(/#039;/g, "'");
    html = html.replace(/&amp;#039;/g, "'");
    html = html.replace(/&#039;/g, "'");
    html = html.replace(/&quot;/g, '"');
    html = html.replace(/&amp;/g, '&');
    html = html.replace(/&lt;/g, '<');
    html = html.replace(/&gt;/g, '>');
    html = html.replace(/&nbsp;/g, ' ');
    
    // Update the content
    content.innerHTML = html;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initInteractions();
    updateRealTimeDates(); // From main.js
    fixHTMLEntities(); // Fix encoding issues
});

// Update real-time dates every minute
setInterval(updateRealTimeDates, 60000);

</script>
