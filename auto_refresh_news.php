<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

header('Content-Type: application/json');

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    // Step 1: Run RSS scraper
    $scraper_result = run_rss_scraper();
    
    // Step 2: Run auto publisher
    $publisher_result = run_auto_publisher();
    
    // Combine results
    $response['success'] = true;
    $response['message'] = 'News refresh completed successfully';
    $response['data'] = [
        'scraper' => $scraper_result,
        'publisher' => $publisher_result,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error during news refresh: ' . $e->getMessage();
    error_log('Auto refresh error: ' . $e->getMessage());
}

echo json_encode($response);

function run_rss_scraper() {
    global $conn;
    
    // Get active RSS sources
    $sources_query = "SELECT * FROM news_sources WHERE is_active = 1 ORDER BY priority DESC";
    $sources_result = mysqli_query($conn, $sources_query);
    
    if (!$sources_result) {
        throw new Exception('Failed to fetch RSS sources');
    }
    
    $scraped_count = 0;
    $sources_processed = 0;
    
    while ($source = mysqli_fetch_assoc($sources_result)) {
        $sources_processed++;
        
        // Fetch RSS feed
        $rss_content = fetch_rss_feed($source['rss_url']);
        
        if ($rss_content) {
            // Parse and store articles
            $articles = parse_rss_feed($rss_content, $source);
            
            foreach ($articles as $article) {
                // Check for duplicates
                if (!article_exists($article['title'], $article['link'])) {
                    // Insert as draft
                    insert_scraped_article($article, $source);
                    $scraped_count++;
                }
            }
        }
    }
    
    return [
        'sources_processed' => $sources_processed,
        'articles_scraped' => $scraped_count
    ];
}

function run_auto_publisher() {
    global $conn;
    
    // Get quality draft articles
    $drafts_query = "SELECT * FROM news WHERE status = 'draft' AND source = 'rss' ORDER BY created_at DESC LIMIT 20";
    $drafts_result = mysqli_query($conn, $drafts_query);
    
    if (!$drafts_result) {
        throw new Exception('Failed to fetch draft articles');
    }
    
    $published_count = 0;
    
    while ($article = mysqli_fetch_assoc($drafts_result)) {
        // Quality check
        if (is_article_quality_good($article)) {
            // Publish article
            $publish_query = "UPDATE news SET status = 'published', published_at = NOW() WHERE id = " . $article['id'];
            
            if (mysqli_query($conn, $publish_query)) {
                $published_count++;
            }
        }
    }
    
    return [
        'drafts_processed' => mysqli_num_rows($drafts_result),
        'articles_published' => $published_count
    ];
}

function fetch_rss_feed($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PK Live News RSS Reader 1.0'
        ]
    ]);
    
    $content = @file_get_contents($url, false, $context);
    
    if ($content === false) {
        error_log("Failed to fetch RSS feed: $url");
        return false;
    }
    
    return $content;
}

function parse_rss_feed($content, $source) {
    $articles = [];
    
    try {
        $xml = simplexml_load_string($content);
        
        if ($xml === false) {
            return $articles;
        }
        
        // Handle both RSS and Atom formats
        $items = [];
        
        if (isset($xml->channel->item)) {
            // RSS format
            $items = $xml->channel->item;
        } elseif (isset($xml->entry)) {
            // Atom format
            $items = $xml->entry;
        }
        
        foreach ($items as $item) {
            $article = [
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'description' => (string) $item->description,
                'content' => (string) ($item->content ?? $item->description),
                'pub_date' => (string) ($item->pubDate ?? $item->published ?? date('Y-m-d H:i:s')),
                'category' => 'General', // Default category
                'source_name' => $source['name']
            ];
            
            // Clean and validate
            if (strlen($article['title']) >= 20 && strlen($article['content']) >= 200) {
                $articles[] = $article;
            }
        }
        
    } catch (Exception $e) {
        error_log('RSS parsing error: ' . $e->getMessage());
    }
    
    return $articles;
}

function article_exists($title, $link) {
    global $conn;
    
    $title = mysqli_real_escape_string($conn, $title);
    $link = mysqli_real_escape_string($conn, $link);
    
    $check_query = "SELECT id FROM news WHERE title = '$title' OR source_url = '$link' LIMIT 1";
    $result = mysqli_query($conn, $check_query);
    
    return mysqli_num_rows($result) > 0;
}

function insert_scraped_article($article, $source) {
    global $conn;
    
    $title = mysqli_real_escape_string($conn, $article['title']);
    $content = mysqli_real_escape_string($conn, $article['content']);
    $description = mysqli_real_escape_string($conn, substr($article['description'], 0, 500));
    $source_url = mysqli_real_escape_string($conn, $article['link']);
    $category = mysqli_real_escape_string($conn, $article['category']);
    $source_name = mysqli_real_escape_string($conn, $article['source_name']);
    $pub_date = mysqli_real_escape_string($conn, $article['pub_date']);
    
    // Generate slug
    $slug = generate_slug($title);
    
    // Try to extract image
    $image_url = extract_first_image($article['content']);
    
    $insert_query = "INSERT INTO news (
        title, slug, content, excerpt, source_url, category_id, 
        source, source_name, published_at, created_at, status, image_url
    ) VALUES (
        '$title', '$slug', '$content', '$description', '$source_url',
        1, 'rss', '$source_name', '$pub_date', NOW(), 'draft', '$image_url'
    )";
    
    return mysqli_query($conn, $insert_query);
}

function is_article_quality_good($article) {
    // Basic quality checks
    if (strlen($article['title']) < 20) return false;
    if (strlen($article['content']) < 200) return false;
    
    // Check for spam indicators
    $spam_keywords = ['click here', 'buy now', 'free money', 'urgent', 'act now'];
    $content_lower = strtolower($article['content'] . ' ' . $article['title']);
    
    foreach ($spam_keywords as $keyword) {
        if (strpos($content_lower, $keyword) !== false) {
            return false;
        }
    }
    
    return true;
}

function generate_slug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/\s+/', '-', $slug);
    $slug = trim($slug, '-');
    
    return $slug . '-' . time();
}

function extract_first_image($content) {
    // Simple image extraction
    if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches)) {
        return $matches[1];
    }
    
    return '';
}
?>
