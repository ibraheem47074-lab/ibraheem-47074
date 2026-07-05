<?php
/**
 * RSS News Demo - Simple Version (No Database Required)
 * This file demonstrates the RSS parsing functionality
 */

require_once 'includes/enhanced_rss_parser.php';

$page_title = 'RSS News Demo - Simple';
$demo_mode = true;

// Initialize parser
$parser = new EnhancedRSSParser();

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
    ]
];

// Handle form submissions
$message = '';
$message_type = '';
$articles = [];

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
    </style>
</head>
<body>
    <!-- Header -->
    <header class="demo-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold">
                        <i class="fas fa-rss me-3"></i>RSS Parser Demo
                    </h1>
                    <p class="lead mb-0">
                        Test RSS parsing with image extraction (No Database Required)
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="badge bg-white text-dark p-3">
                        <i class="fas fa-code me-2"></i>Simple Demo
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

        <!-- Interactive Demo -->
        <section class="mb-5">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-play-circle me-2"></i>Test RSS Feed
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="feed_url" class="form-label">RSS Feed URL</label>
                                    <input type="url" class="form-control" id="feed_url" name="feed_url" 
                                           value="http://feeds.bbci.co.uk/news/world/rss.xml" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="max_articles" class="form-label">Max Articles</label>
                                    <input type="number" class="form-control" id="max_articles" name="max_articles" 
                                           value="3" min="1" max="10">
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="validate_feed" class="btn btn-outline-primary">
                                        <i class="fas fa-check-circle me-2"></i>Validate Feed
                                    </button>
                                    <button type="submit" name="parse_feed" class="btn btn-success">
                                        <i class="fas fa-code me-2"></i>Parse Articles
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
                                <i class="fas fa-info-circle me-2"></i>Quick Test Feeds
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($demo_feeds as $feed): ?>
                                <div class="mb-3">
                                    <h6><?php echo htmlspecialchars($feed['name']); ?></h6>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="testFeed('<?php echo htmlspecialchars($feed['url']); ?>')">
                                        <i class="fas fa-play me-1"></i>Test This Feed
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Parsed Articles Display -->
        <?php if (!empty($articles)): ?>
            <section class="mb-5">
                <h3 class="mb-4">
                    <i class="fas fa-newspaper me-2"></i>Parsed Articles
                </h3>
                <div class="row">
                    <?php foreach ($articles as $article): ?>
                        <div class="col-md-6 mb-4">
                            <div class="article-demo">
                                <?php if (!empty($article['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                         class="mb-3">
                                    <div class="alert alert-success py-2">
                                        <small>
                                            <i class="fas fa-check-circle me-1"></i>
                                            ✅ Image successfully extracted
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning py-2">
                                        <small>
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            ⚠️ No image found
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <h5><?php echo htmlspecialchars($article['title']); ?></h5>
                                
                                <div class="text-muted small mb-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M j, Y H:i', strtotime($article['published_date'])); ?>
                                </div>
                                
                                <p><?php echo substr(strip_tags($article['content']), 0, 200) . '...'; ?></p>
                                
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

        <!-- Features -->
        <section>
            <h3 class="mb-4">
                <i class="fas fa-star me-2"></i>Features Demonstrated
            </h3>
            <div class="row">
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card h-100 text-center p-3">
                        <div class="text-primary mb-2">
                            <i class="fas fa-images fa-2x"></i>
                        </div>
                        <h6>Image Extraction</h6>
                        <p class="small">Extracts images from multiple RSS formats</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card h-100 text-center p-3">
                        <div class="text-success mb-2">
                            <i class="fas fa-check fa-2x"></i>
                        </div>
                        <h6>Feed Validation</h6>
                        <p class="small">Validates RSS feed formats and structure</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card h-100 text-center p-3">
                        <div class="text-danger mb-2">
                            <i class="fas fa-code fa-2x"></i>
                        </div>
                        <h6>Content Parsing</h6>
                        <p class="small">Extracts title, content, dates, and links</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card h-100 text-center p-3">
                        <div class="text-info mb-2">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                        <h6>Multi-Format Support</h6>
                        <p class="small">Supports RSS 2.0, RSS 1.0, and Atom feeds</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                <i class="fas fa-code me-2"></i>
                RSS Parser Demo - PK Live News
            </p>
            <small class="text-muted">
                Enhanced RSS parsing with image extraction
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
    </script>
</body>
</html>
