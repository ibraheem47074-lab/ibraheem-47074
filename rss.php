<?php
// PK Live News RSS Feed Generator
// Generates RSS feeds for news articles

// Set headers for RSS XML output
header('Content-Type: application/rss+xml; charset=utf-8');
header('Cache-Control: public, max-age=300'); // Cache for 5 minutes

// Include required files
require_once 'config/database.php';
require_once 'config/settings.php';

// Get feed parameters
$feed_type = $_GET['type'] ?? 'latest';
$category = isset($_GET['category']) ? intval($_GET['category']) : null;
$limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 20;

// Initialize RSS feed
$rss = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"></rss>');
$rss->addAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
$rss->addAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
$rss->addAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');

// Create channel
$channel = $rss->addChild('channel');

// Add channel information
$channel->addChild('title', htmlspecialchars('PK Live News - Latest News'));
$channel->addChild('link', htmlspecialchars(SITE_URL));
$channel->addChild('description', htmlspecialchars('Latest news and updates from Pakistan'));
$channel->addChild('language', 'en-us');
$channel->addChild('lastBuildDate', date('r'));
$channel->addChild('generator', 'PK Live News RSS Generator v1.0');
$channel->addChild('webMaster', 'admin@pklivenews.com');

// Add channel image
$image = $channel->addChild('image');
$image->addChild('url', htmlspecialchars(SITE_URL . 'assets/images/logo.png'));
$image->addChild('title', htmlspecialchars('PK Live News'));
$image->addChild('link', htmlspecialchars(SITE_URL));

try {
    // Build query based on feed type
    $query = "SELECT n.*, c.name as category_name, c.slug as category_slug 
              FROM news n 
              LEFT JOIN categories c ON n.category_id = c.id 
              WHERE n.status = 'published' AND n.news_type IN ('internal', 'rss_import')";
    
    $params = [];
    $types = '';
    
    // Add category filter if specified
    if ($category && $category > 0) {
        $query .= " AND n.category_id = ?";
        $params[] = $category;
        $types .= 'i';
    }
    
    // Add ordering based on feed type
    switch ($feed_type) {
        case 'popular':
            $query .= " ORDER BY n.views DESC, n.created_at DESC";
            break;
        case 'trending':
            $query .= " ORDER BY n.views DESC, n.created_at DESC";
            break;
        case 'breaking':
            $query .= " AND (n.urgency = 'high' OR n.title LIKE '%breaking%' OR n.title LIKE '%urgent%') ORDER BY n.created_at DESC";
            break;
        default:
            $query .= " ORDER BY n.created_at DESC";
            break;
    }
    
    $query .= " LIMIT ?";
    $params[] = $limit;
    $types .= 'i';
    
    // Prepare and execute query
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Add news items to RSS
        while ($news = mysqli_fetch_assoc($result)) {
            $item = $channel->addChild('item');
            
            // Title
            $item->addChild('title', htmlspecialchars($news['title']));
            
            // Link
            $link = SITE_URL . 'news/' . $news['id'] . '/' . create_slug($news['title']);
            $item->addChild('link', htmlspecialchars($link));
            
            // Description
            $description = $news['summary'] ?? substr(strip_tags($news['content'] ?? ''), 0, 300) . '...';
            $item->addChild('description', htmlspecialchars($description));
            
            // Content (full content)
            if (!empty($news['content'])) {
                $content = $item->addChild('content:encoded', htmlspecialchars($news['content']));
                $content->addAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
            }
            
            // Publication date
            $pubDate = !empty($news['created_at']) ? date('r', strtotime($news['created_at'])) : date('r');
            $item->addChild('pubDate', $pubDate);
            
            // GUID
            $guid = $item->addChild('guid', htmlspecialchars($link));
            $guid->addAttribute('isPermaLink', 'true');
            
            // Category
            if (!empty($news['category_name'])) {
                $category_item = $item->addChild('category', htmlspecialchars($news['category_name']));
                $category_item->addAttribute('domain', htmlspecialchars(SITE_URL . 'category/' . $news['category_slug']));
            }
            
            // Author
            if (!empty($news['author'])) {
                $author = $item->addChild('dc:creator', htmlspecialchars($news['author']));
                $author->addAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
            }
            
            // Media (images)
            if (!empty($news['image'])) {
                $media = $item->addChild('media:content');
                $media->addAttribute('url', htmlspecialchars(SITE_URL . $news['image']));
                $media->addAttribute('type', 'image/jpeg');
                $media->addAttribute('medium', 'image');
                $media->addAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');
                
                // Add media thumbnail
                $thumbnail = $item->addChild('media:thumbnail');
                $thumbnail->addAttribute('url', htmlspecialchars(SITE_URL . $news['image']));
                $thumbnail->addAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');
            }
            
            // Source attribution for RSS imports
            if ($news['news_type'] === 'rss_import' && !empty($news['source_url'])) {
                $source = $item->addChild('source', htmlspecialchars('Source: ' . parse_url($news['source_url'], PHP_URL_HOST)));
                $source->addAttribute('url', htmlspecialchars($news['source_url']));
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
} catch (Exception $e) {
    // Add error item to RSS feed
    $item = $channel->addChild('item');
    $item->addChild('title', 'RSS Feed Error');
    $item->addChild('description', 'Unable to generate RSS feed: ' . htmlspecialchars($e->getMessage()));
    $item->addChild('pubDate', date('r'));
}

// Output RSS XML
echo $rss->asXML();
?>
