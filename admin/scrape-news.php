<?php
require_once '../config/database.php';
require_once '../includes/web_scraper.php';
require_once '../includes/sentiment_analysis.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';
$scraped_articles = [];

// Function to fix invalid dates
function fixInvalidDate($date) {
    if (empty($date)) {
        return date('Y-m-d H:i:s');
    }
    
    // Check for common invalid date formats
    $invalid_patterns = [
        '0000-00-00',
        '1970-01-01',
        '1969-12-31',
        '0000-00-00 00:00:00',
        '1970-01-01 00:00:00',
        '1969-12-31 23:59:59'
    ];
    
    // Check if date is invalid
    if (in_array(substr($date, 0, 10), $invalid_patterns) || 
        in_array($date, $invalid_patterns) ||
        strtotime($date) === false ||
        strtotime($date) < 0) {
        
        // Return current date as fallback
        return date('Y-m-d H:i:s');
    }
    
    // Try to parse and validate the date
    $parsed_date = date('Y-m-d H:i:s', strtotime($date));
    if ($parsed_date === '1970-01-01 00:00:00' || $parsed_date === '1969-12-31 23:59:59') {
        return date('Y-m-d H:i:s');
    }
    
    return $parsed_date;
}

// Function to automatically detect category based on source URL
function autoDetectCategory($source_url) {
    global $conn;
    
    if (empty($source_url)) {
        return null;
    }
    
    // Check if URL matches any existing news source
    $source_query = "SELECT category_id FROM news_sources WHERE ? LIKE CONCAT('%', url, '%') AND status = 'active' LIMIT 1";
    $stmt = mysqli_prepare($conn, $source_query);
    mysqli_stmt_bind_param($stmt, 's', $source_url);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($source = mysqli_fetch_assoc($result)) {
        return $source['category_id'];
    }
    
    // Fallback: detect category based on URL patterns
    $url_patterns = [
        // Pakistani News Sources
        'arynews.tv' => 'Politics',
        'geo.tv' => 'Politics', 
        'dawn.com' => 'Politics',
        'bbc.com/news/world/asia' => 'International',
        'cnn.com' => 'International',
        'reuters.com/world/asia-pacific' => 'International',
        'tribune.com.pk' => 'Politics',
        'express.pk' => 'Politics',
        'samaa.tv' => 'Politics',
        'bolnews.com' => 'Politics',
        '92news.tv' => 'Politics',
        'urduPoint.com' => 'Politics',
        
        // International
        'aljazeera.com' => 'International',
        'theguardian.com/world' => 'International',
        'nytimes.com' => 'International',
        
        // Sports
        'espn.com' => 'Sports',
        'cricbuzz.com' => 'Sports',
        'pcb.com.pk' => 'Sports',
        
        // Business
        'bloomberg.com' => 'Business',
        'reuters.com/business' => 'Business',
        'dawn.com/business' => 'Business',
        
        // Technology
        'techcrunch.com' => 'Technology',
        'theverge.com' => 'Technology',
        'wired.com' => 'Technology',
        
        // Entertainment
        'hollywoodreporter.com' => 'Entertainment',
        'variety.com' => 'Entertainment',
    ];
    
    foreach ($url_patterns as $pattern => $category_name) {
        if (strpos($source_url, $pattern) !== false) {
            // Get category ID by name
            $cat_query = "SELECT id FROM categories WHERE name = ? AND status = 'active' LIMIT 1";
            $stmt = mysqli_prepare($conn, $cat_query);
            mysqli_stmt_bind_param($stmt, 's', $category_name);
            mysqli_stmt_execute($stmt);
            $cat_result = mysqli_stmt_get_result($stmt);
            
            if ($category = mysqli_fetch_assoc($cat_result)) {
                return $category['id'];
            }
        }
    }
    
    return null; // Default category or null
}

// Handle manual scraping
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scrape'])) {
    $source_id = (int)$_POST['source_id'];
    $max_articles = (int)$_POST['max_articles'];
    $publish_scraped = isset($_POST['publish_scraped']) ? 'published' : 'draft';
    
    try {
        // Get source details
        $source_query = "SELECT * FROM news_sources WHERE id = ? AND status = 'active'";
        $stmt = mysqli_prepare($conn, $source_query);
        mysqli_stmt_bind_param($stmt, 'i', $source_id);
        mysqli_stmt_execute($stmt);
        $source_result = mysqli_stmt_get_result($stmt);
        
        if ($source_result->num_rows === 0) {
            throw new Exception('News source not found or inactive');
        }
        
        $source = mysqli_fetch_assoc($source_result);
        $scraper = new WebScraper();
        
        if ($source['type'] === 'rss') {
            $articles = $scraper->scrapeRSS($source['url']);
        } else {
            // For website scraping, you'd need to implement specific selectors
            throw new Exception('Website scraping requires custom selectors');
        }
        
        if (empty($articles)) {
            throw new Exception('No articles found in the RSS feed');
        }
        
        $imported = 0;
        $duplicates = 0;
        $errors = 0;
        
        foreach (array_slice($articles, 0, $max_articles) as $article) {
            try {
                // Check for duplicates
                if ($scraper->isDuplicate($article['title'], $article['content'])) {
                    $duplicates++;
                    continue;
                }
                
                // Perform sentiment analysis
                $sentiment = analyze_sentiment($article['title'] . ' ' . $article['content']);
                
                // Generate slug
                $slug = slugify($article['title']);
                
                // Check if slug already exists
                $slug_check = mysqli_query($conn, "SELECT id FROM news WHERE slug = '$slug'");
                if (mysqli_num_rows($slug_check) > 0) {
                    $slug .= '-' . time();
                }
                
                // Download image if exists
                $image_path = '';
                if (!empty($article['image'])) {
                    $image_path = downloadImage($article['image']);
                }
                
                // Insert into database with controlled publish status
                $insert = "INSERT INTO news (title, slug, content, excerpt, image, category_id, 
                          author_id, status, sentiment_score, sentiment_label, published_at, source_url, news_type) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scraped')";
                
                $stmt = mysqli_prepare($conn, $insert);
                $author_id = $_SESSION['user_id'];
                $published_at = fixInvalidDate($article['published_date']);
                
                mysqli_stmt_bind_param($stmt, 'sssssiidssss', 
                    $article['title'], $slug, $article['content'], $article['excerpt'], 
                    $image_path, $source['category_id'], $author_id, 
                    $publish_scraped, $sentiment['score'], $sentiment['label'], $published_at, $article['link']
                );
                
                if (mysqli_stmt_execute($stmt)) {
                    $imported++;
                    $scraped_articles[] = [
                        'title' => $article['title'],
                        'status' => $publish_scraped === 'published' ? 'published' : 'draft',
                        'sentiment' => $sentiment['label'],
                        'source' => $source['name']
                    ];
                }
                
            } catch (Exception $e) {
                $errors++;
                $scraped_articles[] = [
                    'title' => $article['title'] ?? 'Unknown',
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // Update last scraped timestamp
        $update = "UPDATE news_sources SET last_scraped = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, 'i', $source_id);
        mysqli_stmt_execute($stmt);
        
        $status_text = $publish_scraped === 'published' ? 'published to index page' : 'saved as draft';
        $success = "Successfully scraped and imported $imported articles! Any articles with invalid dates have been automatically fixed with current timestamps. Duplicates: $duplicates, Errors: $errors. Articles have been $status_text.";
        
    } catch (Exception $e) {
        $error = "Scraping failed: " . $e->getMessage();
    }
}

// Handle external news submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_external_news'])) {
    $title = clean_input($_POST['title']);
    $content = clean_input($_POST['content']);
    $excerpt = clean_input($_POST['excerpt']);
    $source_url = clean_input($_POST['source_url']);
    $category_id = (int)$_POST['category_id'];
    $image_url = clean_input($_POST['image_url']);
    $publish_now = isset($_POST['publish_now']) ? 'published' : 'draft';
    
    // Auto-detect category if not manually selected
    if ($category_id === 0 && !empty($source_url)) {
        $auto_category = autoDetectCategory($source_url);
        if ($auto_category) {
            $category_id = $auto_category;
        }
    }
    
    try {
        // Validate required fields
        if (empty($title) || empty($content)) {
            throw new Exception('Title and content are required');
        }
        
        // Generate excerpt if not provided
        if (empty($excerpt)) {
            $excerpt = substr(strip_tags($content), 0, 200) . '...';
        }
        
        // Generate slug
        $slug = slugify($title);
        
        // Check if slug already exists
        $slug_check = mysqli_query($conn, "SELECT id FROM news WHERE slug = '$slug'");
        if (mysqli_num_rows($slug_check) > 0) {
            $slug .= '-' . time();
        }
        
        // Download image if URL provided
        $image_path = '';
        if (!empty($image_url)) {
            $image_path = downloadImage($image_url);
        }
        
        // Perform sentiment analysis
        $sentiment = analyze_sentiment($title . ' ' . $content);
        
        // Insert into database
        $insert = "INSERT INTO news (title, slug, content, excerpt, image, category_id, 
                  author_id, status, sentiment_score, sentiment_label, published_at, source_url, news_type) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'external')";
        
        $stmt = mysqli_prepare($conn, $insert);
        $author_id = $_SESSION['user_id'];
        $published_at = fixInvalidDate(date('Y-m-d H:i:s'));
        
        mysqli_stmt_bind_param($stmt, 'sssssiidsss', 
            $title, $slug, $content, $excerpt, 
            $image_path, $category_id, $author_id, 
            $publish_now, $sentiment['score'], $sentiment['label'], $published_at, $source_url
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $news_id = mysqli_insert_id($conn);
            $success = "External news article successfully submitted! " . 
                      ($publish_now === 'published' ? 'It is now live on the index page.' : 'It is saved as draft.');
        } else {
            throw new Exception('Failed to save the article');
        }
        
    } catch (Exception $e) {
        $error = "Submission failed: " . $e->getMessage();
    }
}

// Get active news sources (check if table exists first)
$sources_result = null;
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
if (mysqli_num_rows($table_check) > 0) {
    $sources_query = "SELECT ns.*, c.name as category_name 
                      FROM news_sources ns 
                      LEFT JOIN categories c ON ns.category_id = c.id 
                      WHERE ns.status = 'active' 
                      ORDER BY ns.name ASC";
    $sources_result = mysqli_query($conn, $sources_query);
}

// Get all categories for external news form
$categories_result = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");

// Get recent scraped articles (check if source_url column exists)
$recent_result = null;
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source_url'");
if (mysqli_num_rows($column_check) > 0) {
    $recent_query = "SELECT * FROM news WHERE source_url IS NOT NULL 
                     ORDER BY created_at DESC LIMIT 10";
    $recent_result = mysqli_query($conn, $recent_query);
}

// Helper function to download images
function downloadImage($url) {
    global $conn;
    
    try {
        $scraper = new WebScraper();
        $imageData = $scraper->fetch($url);
        
        // Check if it's actually an image
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);
        
        if (strpos($mimeType, 'image/') !== 0) {
            return '';
        }
        
        // Generate filename
        $extension = explode('/', $mimeType)[1];
        $filename = uniqid() . '.' . $extension;
        $upload_path = 'uploads/news/' . $filename;
        $full_path = '../' . $upload_path;
        
        // Ensure directory exists
        $upload_dir = dirname($full_path);
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Save file
        if (file_put_contents($full_path, $imageData)) {
            return $upload_path;
        }
        
    } catch (Exception $e) {
        error_log("Image download failed: " . $e->getMessage());
    }
    
    return '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scrape News - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .admin-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .admin-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .admin-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .scrape-progress {
            display: none;
        }
        .article-item {
            border-left: 4px solid #dee2e6;
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        .article-item.imported {
            border-left-color: #28a745;
        }
        .article-item.duplicate {
            border-left-color: #ffc107;
        }
        .article-item.error {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK Live News</h4>
                        <small>Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-editions.php">
                                <i class="fas fa-layer-group me-2"></i>News Editions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="sentiment-dashboard.php">
                                <i class="fas fa-brain me-2"></i>Sentiment Analysis
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../architecture.php">
                                <i class="fas fa-sitemap me-2"></i>System Architecture
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-polls.php">
                                <i class="fas fa-poll me-2"></i>Polls
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-ads.php">
                                <i class="fas fa-ad me-2"></i>Advertisements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-main-content">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Scrape News</h1>
                        <small>Import news articles from external sources</small>
                    </div>
                    <div>
                        <a href="manage-sources.php" class="btn btn-outline-primary">
                            <i class="fas fa-cog me-2"></i>Manage Sources
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Scrape Form -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-spider me-2"></i>Scrape Articles</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="scrapeForm">
                                    <div class="mb-3">
                                        <label for="source_id" class="form-label">News Source</label>
                                        <select class="form-select" id="source_id" name="source_id" required>
                                            <option value="">Select a news source</option>
                                            <?php while ($source = mysqli_fetch_assoc($sources_result)): ?>
                                                <option value="<?php echo $source['id']; ?>">
                                                    <?php echo htmlspecialchars($source['name']); ?> 
                                                    (<?php echo htmlspecialchars($source['category_name'] ?? 'Uncategorized'); ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="max_articles" class="form-label">Maximum Articles</label>
                                        <input type="number" class="form-control" id="max_articles" name="max_articles" 
                                               value="10" min="1" max="50">
                                        <small class="text-muted">Maximum number of articles to scrape (1-50)</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="publish_scraped" name="publish_scraped">
                                            <label class="form-check-label" for="publish_scraped">
                                                <strong>Publish scraped articles immediately</strong>
                                                <br><small class="text-muted">Check to publish directly to index page, uncheck to save as draft for review</small>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="scrape-progress">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            <span>Scraping in progress...</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="scrape" class="btn btn-primary w-100">
                                        <i class="fas fa-spider me-2"></i>Start Scraping
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- External News Submission -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Submit External News</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="externalNewsForm">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Article Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required
                                               placeholder="Enter article title">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Article Content *</label>
                                        <textarea class="form-control" id="content" name="content" rows="4" required
                                                  placeholder="Enter article content"></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">Excerpt/Summary</label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="2"
                                                  placeholder="Brief summary (optional, will auto-generate if empty)"></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="source_url" class="form-label">Source URL</label>
                                        <input type="url" class="form-control" id="source_url" name="source_url"
                                               placeholder="https://example.com/article" onchange="autoDetectCategory()">
                                        <small class="text-muted">Enter the original article URL. Category will be auto-detected.</small>
                                        <div id="categoryDetection" class="mt-2" style="display: none;">
                                            <span class="badge bg-info">Auto-detected category: <span id="detectedCategory"></span></span>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category_id" class="form-label">Category *</label>
                                                <select class="form-select" id="category_id" name="category_id" required>
                                                    <option value="0">Auto-detect from URL</option>
                                                    <?php 
                                                    // Reset categories result pointer if needed
                                                    if ($categories_result) {
                                                        mysqli_data_seek($categories_result, 0);
                                                        while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                                            <option value="<?php echo $category['id']; ?>">
                                                                <?php echo htmlspecialchars($category['name']); ?>
                                                            </option>
                                                        <?php endwhile; 
                                                    } ?>
                                                </select>
                                                <small class="text-muted">Select manually or let us auto-detect from the source URL</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="image_url" class="form-label">Image URL</label>
                                                <input type="url" class="form-control" id="image_url" name="image_url"
                                                       placeholder="https://example.com/image.jpg">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="publish_now" name="publish_now">
                                            <label class="form-check-label" for="publish_now">
                                                <strong>Publish immediately to index page</strong>
                                                <br><small class="text-muted">Uncheck to save as draft</small>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="submit_external_news" class="btn btn-success w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Submit External News
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Section -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Scraping Statistics</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $stats_query = "SELECT 
                                    COUNT(*) as total_scraped,
                                    COUNT(CASE WHEN source_url IS NOT NULL THEN 1 END) as from_sources,
                                    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today
                                    FROM news";
                                $stats = mysqli_query($conn, $stats_query)->fetch_assoc();
                                ?>
                                
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="border-end">
                                            <h4 class="text-primary"><?php echo $stats['total_scraped']; ?></h4>
                                            <small class="text-muted">Total Scraped</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border-end">
                                            <h4 class="text-success"><?php echo $stats['from_sources']; ?></h4>
                                            <small class="text-muted">From Sources</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-info"><?php echo $stats['today']; ?></h4>
                                        <small class="text-muted">Today</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Recent Scraped Articles -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Recently Scraped</h6>
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                <?php if ($recent_result && $recent_result->num_rows > 0): ?>
                                    <?php while ($article = mysqli_fetch_assoc($recent_result)): ?>
                                        <div class="small mb-2">
                                            <strong><?php echo htmlspecialchars(substr($article['title'], 0, 50)); ?>...</strong><br>
                                            <small class="text-muted">
                                                <?php echo date('M d, H:i', strtotime($article['created_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">No scraped articles yet</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Scraping Results -->
                <?php if (!empty($scraped_articles)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Scraping Results</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($scraped_articles as $article): ?>
                                <div class="article-item <?php echo $article['status']; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                                            <?php if (isset($article['source'])): ?>
                                                <span class="badge bg-info ms-2"><?php echo htmlspecialchars($article['source']); ?></span>
                                            <?php endif; ?>
                                            <?php if (isset($article['sentiment'])): ?>
                                                <span class="badge bg-secondary ms-2"><?php echo $article['sentiment']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-<?php 
                                            echo $article['status'] === 'published' ? 'success' : 
                                                 ($article['status'] === 'draft' ? 'primary' : 
                                                 ($article['status'] === 'error' ? 'danger' : 'warning')); ?>">
                                            <?php 
                                            echo $article['status'] === 'published' ? 'Published' : 
                                                 ($article['status'] === 'draft' ? 'Draft' : 
                                                 ucfirst($article['status'])); ?>
                                        </span>
                                    </div>
                                    <?php if (isset($article['error'])): ?>
                                        <small class="text-danger"><?php echo $article['error']; ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Draft articles can be reviewed and published from the "Manage News" section.
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('scrapeForm').addEventListener('submit', function(e) {
            const progressDiv = document.querySelector('.scrape-progress');
            const button = this.querySelector('button[type="submit"]');
            
            progressDiv.style.display = 'block';
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Scraping...';
        });

        // External news form validation and submission
        document.getElementById('externalNewsForm').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            // Basic validation
            if (!title || !content) {
                e.preventDefault();
                alert('Please fill in both title and content fields.');
                return;
            }
            
            if (title.length < 5) {
                e.preventDefault();
                alert('Title must be at least 5 characters long.');
                return;
            }
            
            if (content.length < 20) {
                e.preventDefault();
                alert('Content must be at least 20 characters long.');
                return;
            }
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        });

        // Auto-generate excerpt from content
        document.getElementById('content').addEventListener('input', function() {
            const excerpt = document.getElementById('excerpt');
            if (!excerpt.value.trim()) {
                const content = this.value.trim();
                if (content.length > 0) {
                    const plainText = content.replace(/<[^>]*>/g, ''); // Remove HTML tags
                    const autoExcerpt = plainText.substring(0, 200) + (plainText.length > 200 ? '...' : '');
                    excerpt.placeholder = 'Auto-generated: ' + autoExcerpt.substring(0, 100) + '...';
                }
            }
        });

        // Character counter for title
        document.getElementById('title').addEventListener('input', function() {
            const maxLength = 200;
            const currentLength = this.value.length;
            if (currentLength > maxLength) {
                this.value = this.value.substring(0, maxLength);
            }
        });

        // URL validation helper
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        // Validate URLs on blur
        // Auto-detect category function
        function autoDetectCategory() {
            const sourceUrl = document.getElementById('source_url').value;
            const categorySelect = document.getElementById('category_id');
            const detectionDiv = document.getElementById('categoryDetection');
            const detectedCategorySpan = document.getElementById('detectedCategory');
            
            if (!sourceUrl) {
                detectionDiv.style.display = 'none';
                return;
            }
            
            // URL patterns for category detection (same as PHP function)
            const urlPatterns = {
                'arynews.tv': 'Politics',
                'geo.tv': 'Politics', 
                'dawn.com': 'Politics',
                'bbc.com/news/world/asia': 'International',
                'cnn.com': 'International',
                'reuters.com/world/asia-pacific': 'International',
                'tribune.com.pk': 'Politics',
                'express.pk': 'Politics',
                'samaa.tv': 'Politics',
                'bolnews.com': 'Politics',
                '92news.tv': 'Politics',
                'urduPoint.com': 'Politics',
                'aljazeera.com': 'International',
                'theguardian.com/world': 'International',
                'nytimes.com': 'International',
                'espn.com': 'Sports',
                'cricbuzz.com': 'Sports',
                'pcb.com.pk': 'Sports',
                'bloomberg.com': 'Business',
                'reuters.com/business': 'Business',
                'dawn.com/business': 'Business',
                'techcrunch.com': 'Technology',
                'theverge.com': 'Technology',
                'wired.com': 'Technology',
                'hollywoodreporter.com': 'Entertainment',
                'variety.com': 'Entertainment'
            };
            
            let detectedCategory = null;
            for (const [pattern, category] of Object.entries(urlPatterns)) {
                if (sourceUrl.includes(pattern)) {
                    detectedCategory = category;
                    break;
                }
            }
            
            if (detectedCategory) {
                // Find the category option and select it
                for (let option of categorySelect.options) {
                    if (option.text === detectedCategory) {
                        categorySelect.value = option.value;
                        detectedCategorySpan.textContent = detectedCategory;
                        detectionDiv.style.display = 'block';
                        detectionDiv.className = 'mt-2';
                        break;
                    }
                }
            } else {
                detectionDiv.style.display = 'none';
            }
        }

        document.getElementById('source_url').addEventListener('blur', function() {
            if (this.value && !isValidUrl(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('image_url').addEventListener('blur', function() {
            if (this.value && !isValidUrl(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
