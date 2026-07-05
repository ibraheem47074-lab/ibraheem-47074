<?php
/**
 * RSS News Demo - Showcase Enhanced RSS Functionality
 * This file demonstrates the complete RSS system with image extraction
 */

require_once 'config/database.php';
require_once 'includes/enhanced_rss_parser.php';
require_once 'includes/auto_news_importer.php';
require_once 'includes/news_display_manager.php';

$page_title = 'RSS News Demo';
$demo_mode = true;

// Initialize components
$parser = new EnhancedRSSParser();
$displayManager = new NewsDisplayManager($conn);

// Demo RSS feeds
$demo_feeds = [
    [
        'name' => 'BBC News - World',
        'url' => 'http://feeds.bbci.co.uk/news/world/rss.xml',
        'category' => 'International'
    ],
    [
        'name' => 'CNN - World',
        'url' => 'http://rss.cnn.com/rss/edition_world.rss',
        'category' => 'International'
    ],
    [
        'name' => 'Reuters - World',
        'url' => 'https://www.reuters.com/world/rss.xml',
        'category' => 'International'
    ],
    [
        'name' => 'Al Jazeera',
        'url' => 'https://www.aljazeera.com/xml/rss/all.xml',
        'category' => 'International'
    ]
];

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['validate_feed'])) {
            $feedUrl = $_POST['feed_url'];
            $validation = $parser->validateFeed($feedUrl);
            
            if ($validation['valid']) {
                $message = "✅ RSS Feed is valid!<br>";
                $message .= "Title: {$validation['title']}<br>";
                $message .= "Format: {$validation['format']}<br>";
                $message .= "Articles: {$validation['items_count']}";
                $message_type = 'success';
            } else {
                $message = "❌ RSS Feed validation failed: {$validation['error']}";
                $message_type = 'danger';
            }
        }
        
        if (isset($_POST['parse_feed'])) {
            $feedUrl = $_POST['feed_url'];
            $maxArticles = (int)$_POST['max_articles'];
            
            $articles = $parser->parseRSS($feedUrl);
            $articles = array_slice($articles, 0, $maxArticles);
            
            $message = "✅ Successfully parsed " . count($articles) . " articles from RSS feed";
            $message_type = 'success';
            
            // Store articles for display
            $_SESSION['demo_articles'] = $articles;
        }
        
        if (isset($_POST['import_feed'])) {
            // Simulate import (in demo mode, don't actually save to database)
            $feedUrl = $_POST['feed_url'];
            $maxArticles = (int)$_POST['max_articles'];
            
            $articles = $parser->parseRSS($feedUrl);
            $articles = array_slice($articles, 0, $maxArticles);
            
            $message = "✅ Demo import completed! " . count($articles) . " articles ready for import<br>";
            $message .= "<small>In production mode, these would be saved to your database with proper copyright attribution.</small>";
            $message_type = 'success';
            
            // Store articles for display
            $_SESSION['demo_articles'] = $articles;
        }
        
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .demo-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        .feature-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .code-block {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 1rem;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .article-demo {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }
        .article-demo img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="demo-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold">
                        <i class="fas fa-rss me-3"></i>Enhanced RSS News System
                    </h1>
                    <p class="lead mb-0">
                        Advanced RSS parsing with image extraction, copyright compliance, and automatic import
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="badge bg-white text-dark p-3">
                        <i class="fas fa-code me-2"></i>PK Live News Demo
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container my-5">
        <!-- Alert Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Features Overview -->
        <section class="mb-5">
            <h2 class="text-center mb-4">
                <i class="fas fa-star me-2"></i>Key Features
            </h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="text-primary mb-3">
                            <i class="fas fa-images fa-3x"></i>
                        </div>
                        <h5>Multi-Format Image Extraction</h5>
                        <p class="text-muted">Supports media:content, media:thumbnail, enclosure, and HTML description images</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="text-success mb-3">
                            <i class="fas fa-balance-scale fa-3x"></i>
                        </div>
                        <h5>Copyright Compliance</h5>
                        <p class="text-muted">Automatic source attribution and content summarization for protected sources</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="text-danger mb-3">
                            <i class="fas fa-sync fa-3x"></i>
                        </div>
                        <h5>Automatic Import</h5>
                        <p class="text-muted">Scheduled cron jobs for automatic news fetching and database updates</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="text-info mb-3">
                            <i class="fas fa-chart-line fa-3x"></i>
                        </div>
                        <h5>Sentiment Analysis</h5>
                        <p class="text-muted">Built-in sentiment analysis for all imported news articles</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Interactive Demo -->
        <section class="mb-5">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-play-circle me-2"></i>Try RSS Feed
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="feed_url" class="form-label">RSS Feed URL</label>
                                    <input type="url" class="form-control" id="feed_url" name="feed_url" 
                                           value="http://feeds.bbci.co.uk/news/world/rss.xml" required>
                                    <div class="form-text">
                                        Try: BBC World, CNN World, Reuters, or Al Jazeera feeds
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="max_articles" class="form-label">Max Articles</label>
                                    <input type="number" class="form-control" id="max_articles" name="max_articles" 
                                           value="5" min="1" max="20">
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="validate_feed" class="btn btn-outline-primary">
                                        <i class="fas fa-check-circle me-2"></i>Validate Feed
                                    </button>
                                    <button type="submit" name="parse_feed" class="btn btn-success">
                                        <i class="fas fa-code me-2"></i>Parse Articles
                                    </button>
                                    <button type="submit" name="import_feed" class="btn btn-danger">
                                        <i class="fas fa-download me-2"></i>Simulate Import
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Supported Image Formats
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>1. Media Content</h6>
                            <div class="code-block">
                                &lt;media:content url="image.jpg" type="image/jpeg"/&gt;
                            </div>
                            
                            <h6>2. Media Thumbnail</h6>
                            <div class="code-block">
                                &lt;media:thumbnail url="thumb.jpg"/&gt;
                            </div>
                            
                            <h6>3. Enclosure</h6>
                            <div class="code-block">
                                &lt;enclosure url="image.jpg" type="image/jpeg"/&gt;
                            </div>
                            
                            <h6>4. HTML Description</h6>
                            <div class="code-block">
                                &lt;description&gt;&lt;img src="image.jpg"/&gt;&lt;/description&gt;
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Demo Articles Display -->
        <?php if (isset($_SESSION['demo_articles']) && !empty($_SESSION['demo_articles'])): ?>
            <section class="mb-5">
                <h3 class="mb-4">
                    <i class="fas fa-newspaper me-2"></i>Parsed Articles Demo
                </h3>
                <div class="row">
                    <?php foreach ($_SESSION['demo_articles'] as $article): ?>
                        <div class="col-md-6 mb-4">
                            <div class="article-demo">
                                <?php if (!empty($article['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                         class="mb-3">
                                <?php endif; ?>
                                
                                <h5><?php echo htmlspecialchars($article['title']); ?></h5>
                                
                                <div class="text-muted small mb-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M j, Y H:i', strtotime($article['published_date'])); ?>
                                </div>
                                
                                <p><?php echo substr(strip_tags($article['content']), 0, 200) . '...'; ?></p>
                                
                                <?php if (!empty($article['image'])): ?>
                                    <div class="alert alert-success py-2">
                                        <small>
                                            <i class="fas fa-check-circle me-1"></i>
                                            ✅ Image successfully extracted from RSS feed
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($article['link'])): ?>
                                    <a href="<?php echo htmlspecialchars($article['link']); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>Read Original
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Implementation Guide -->
        <section class="mb-5">
            <h3 class="mb-4">
                <i class="fas fa-book me-2"></i>Implementation Guide
            </h3>
            
            <div class="accordion" id="implementationGuide">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#step1">
                            <i class="fas fa-1 me-2"></i>Step 1: Basic RSS Parsing
                        </button>
                    </h2>
                    <div id="step1" class="accordion-collapse collapse show" data-bs-parent="#implementationGuide">
                        <div class="accordion-body">
                            <div class="code-block">
require_once 'includes/enhanced_rss_parser.php';

$parser = new EnhancedRSSParser();
$articles = $parser->parseRSS('https://example.com/rss.xml');

foreach ($articles as $article) {
    echo "Title: " . $article['title'] . "\n";
    echo "Image: " . $article['image'] . "\n";
    echo "Content: " . substr($article['content'], 0, 100) . "...\n";
}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step2">
                            <i class="fas fa-2 me-2"></i>Step 2: Automatic Import
                        </button>
                    </h2>
                    <div id="step2" class="accordion-collapse collapse" data-bs-parent="#implementationGuide">
                        <div class="accordion-body">
                            <div class="code-block">
require_once 'includes/auto_news_importer.php';

$importer = new AutoNewsImporter($conn);
$results = $importer->importFromAllSources(10);

echo "Imported: " . $results['imported_articles'] . "\n";
echo "Duplicates: " . $results['duplicate_articles'] . "\n";
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step3">
                            <i class="fas fa-3 me-2"></i>Step 3: Cron Job Setup
                        </button>
                    </h2>
                    <div id="step3" class="accordion-collapse collapse" data-bs-parent="#implementationGuide">
                        <div class="accordion-body">
                            <p>Add this to your crontab to run every 15 minutes:</p>
                            <div class="code-block">
*/15 * * * * /usr/bin/php /path/to/your/site/cron_import_news.php

# Or via web cron:
*/15 * * * * curl -s "https://yoursite.com/cron_import_news.php?cron_key=pk_live_news_2024_cron"
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step4">
                            <i class="fas fa-4 me-2"></i>Step 4: Copyright-Compliant Display
                        </button>
                    </h2>
                    <div id="step4" class="accordion-collapse collapse" data-bs-parent="#implementationGuide">
                        <div class="accordion-body">
                            <div class="code-block">
require_once 'includes/news_display_manager.php';

$displayManager = new NewsDisplayManager($conn);
$news = $displayManager->getNewsForDisplay(20);

foreach ($news as $item) {
    $displayManager->renderNewsCard($item, 'medium');
}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Demo Feeds -->
        <section>
            <h3 class="mb-4">
                <i class="fas fa-list me-2"></i>Pre-configured Demo Feeds
            </h3>
            <div class="row">
                <?php foreach ($demo_feeds as $feed): ?>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($feed['name']); ?></h6>
                                <p class="card-text">
                                    <small class="text-muted"><?php echo htmlspecialchars($feed['category']); ?></small>
                                </p>
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="testFeed('<?php echo htmlspecialchars($feed['url']); ?>')">
                                    <i class="fas fa-play me-1"></i>Test Feed
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                <i class="fas fa-code me-2"></i>
                Enhanced RSS News System - PK Live News Demo
            </p>
            <small class="text-muted">
                Features: Multi-format image extraction • Copyright compliance • Automatic import • Sentiment analysis
            </small>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testFeed(feedUrl) {
            document.getElementById('feed_url').value = feedUrl;
            document.getElementById('feed_url').focus();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // Auto-refresh simulation
        let refreshCount = 0;
        setInterval(() => {
            refreshCount++;
            if (refreshCount % 60 === 0) { // Every minute
                console.log('Auto-refresh check completed');
            }
        }, 1000);
    </script>
</body>
</html>

<?php
// Clear demo articles session
if (isset($_SESSION['demo_articles'])) {
    unset($_SESSION['demo_articles']);
}
?>
