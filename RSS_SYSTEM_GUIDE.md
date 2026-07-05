# Enhanced RSS News System - Setup Guide

## Overview

This enhanced RSS system provides comprehensive news aggregation with advanced image extraction, copyright compliance, and automatic import capabilities.

## Features

✅ **Multi-Format Image Extraction**
- Support for `<media:content>`
- Support for `<media:thumbnail>`
- Support for `<enclosure>`
- Support for HTML description images
- Automatic URL resolution

✅ **Copyright Compliance**
- Automatic source attribution
- Content summarization for protected sources
- "Read More" links to original articles
- Configurable content display rules

✅ **Automatic Import System**
- Scheduled cron job support
- Duplicate detection
- Sentiment analysis integration
- Image downloading and storage

✅ **Enhanced Display System**
- Responsive news cards
- Source identification
- Time-based status badges
- Social sharing integration

## Installation

### 1. File Structure

Upload these files to your PK Live News installation:

```
includes/
├── enhanced_rss_parser.php      # Core RSS parsing with image extraction
├── auto_news_importer.php       # Automatic import system
├── news_display_manager.php     # Copyright-compliant display
└── web_scraper.php              # Updated with enhanced RSS support

cron_import_news.php             # Cron job script
rss_demo.php                     # Demo and testing interface
```

### 2. Database Requirements

Ensure your `news` table has these columns:

```sql
-- Add these columns if they don't exist
ALTER TABLE news 
ADD COLUMN IF NOT EXISTS source_url VARCHAR(500) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS news_type ENUM('internal', 'external', 'rss_import', 'scraped') DEFAULT 'internal',
ADD COLUMN IF NOT EXISTS sentiment_score DECIMAL(3,2) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS sentiment_label ENUM('positive', 'negative', 'neutral') DEFAULT NULL,
ADD COLUMN IF NOT EXISTS summary_only TINYINT(1) DEFAULT 0;
```

### 3. RSS Sources Table

Create or update the `news_sources` table:

```sql
CREATE TABLE IF NOT EXISTS news_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(500) NOT NULL UNIQUE,
    category_id INT,
    type ENUM('rss', 'website') DEFAULT 'rss',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_scraped TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Insert sample RSS sources
INSERT INTO news_sources (name, url, category_id, type) VALUES
('BBC News - World', 'http://feeds.bbci.co.uk/news/world/rss.xml', 1, 'rss'),
('CNN - World', 'http://rss.cnn.com/rss/edition_world.rss', 1, 'rss'),
('Reuters - World', 'https://www.reuters.com/world/rss.xml', 1, 'rss'),
('Al Jazeera', 'https://www.aljazeera.com/xml/rss/all.xml', 1, 'rss');
```

## Configuration

### 1. Basic Usage

```php
// Parse RSS feed with image extraction
require_once 'includes/enhanced_rss_parser.php';

$parser = new EnhancedRSSParser();
$articles = $parser->parseRSS('https://example.com/rss.xml');

foreach ($articles as $article) {
    echo "Title: " . $article['title'] . "\n";
    echo "Image: " . $article['image'] . "\n";
    echo "Content: " . substr($article['content'], 0, 100) . "...\n";
}
```

### 2. Automatic Import

```php
// Import news from all RSS sources
require_once 'includes/auto_news_importer.php';

$importer = new AutoNewsImporter($conn);
$results = $importer->importFromAllSources(10);

echo "Imported: " . $results['imported_articles'] . "\n";
echo "Duplicates: " . $results['duplicate_articles'] . "\n";
```

### 3. Copyright-Compliant Display

```php
// Display news with proper attribution
require_once 'includes/news_display_manager.php';

$displayManager = new NewsDisplayManager($conn);
$news = $displayManager->getNewsForDisplay(20);

foreach ($news as $item) {
    $displayManager->renderNewsCard($item, 'medium');
}
```

## Cron Job Setup

### Option 1: Command Line Cron

Add to your crontab:

```bash
# Edit crontab
crontab -e

# Add this line to run every 15 minutes
*/15 * * * * /usr/bin/php /path/to/your/site/cron_import_news.php
```

### Option 2: Web-Based Cron

Use a web cron service:

```bash
# URL to call
https://yoursite.com/cron_import_news.php?cron_key=pk_live_news_2024_cron
```

### Option 3: WordPress Cron (if using WordPress)

```php
// Add to your theme's functions.php
add_action('wp', 'setup_rss_cron');

function setup_rss_cron() {
    if (!wp_next_scheduled('rss_import_event')) {
        wp_schedule_event(time(), '15_minutes', 'rss_import_event');
    }
}

add_action('rss_import_event', 'run_rss_import');

function run_rss_import() {
    include_once('/path/to/your/site/cron_import_news.php');
}
```

## Copyright Compliance Settings

### Protected Sources

These sources automatically show summary only:

- BBC News
- CNN
- Reuters
- Al Jazeera
- Fox News

### Custom Configuration

```php
// Modify in auto_news_importer.php
$summaryOnlySources = [
    'BBC News' => true,
    'CNN' => true,
    'Your Custom Source' => true
];
```

### Content Processing

The system automatically:

1. **Adds source attribution** at the end of each article
2. **Includes "Read More" links** to original content
3. **Limits content length** for protected sources
4. **Maintains proper HTML formatting**

## Image Handling

### Supported Formats

- JPEG, PNG, GIF, WebP, SVG, BMP
- Automatic MIME type detection
- Error handling for invalid images

### Storage Options

```php
// Configure in AutoNewsImporter
$importer->setDownloadImages(true);  // Download images locally
$importer->setDownloadImages(false); // Keep remote URLs
```

### Image Processing

- Automatic filename generation
- Directory creation
- Error logging
- Fallback handling

## Error Handling

### Logging

All errors are logged to:
```
/logs/cron_import.log
```

### Common Issues

1. **cURL Errors**: Check if cURL is enabled
2. **XML Parsing**: Verify RSS feed validity
3. **Image Downloads**: Check URL accessibility
4. **Database**: Ensure proper table structure

### Debug Mode

Enable debugging:

```php
// In enhanced_rss_parser.php
$parser = new EnhancedRSSParser();
$validation = $parser->validateFeed($feedUrl);
print_r($validation);
```

## Performance Optimization

### Caching

```php
// Add caching for RSS feeds
$cacheKey = 'rss_feed_' . md5($feedUrl);
$cachedData = apcu_fetch($cacheKey);

if ($cachedData === false) {
    $articles = $parser->parseRSS($feedUrl);
    apcu_store($cacheKey, $articles, 900); // 15 minutes
}
```

### Rate Limiting

```php
// Configure import limits
$importer->setMaxArticlesPerFeed(5);  // Limit per feed
$importer->setDownloadImages(true);   // Control image downloads
```

## Security

### Cron Key Protection

The cron script uses a security key:

```php
// Change this in cron_import_news.php
define('CRON_KEY', 'your_custom_secure_key');
```

### Input Validation

All RSS content is properly sanitized:
- HTML tag stripping for excerpts
- SQL prepared statements
- URL validation
- Image MIME type checking

## Testing

### Demo Interface

Visit `rss_demo.php` to:
- Test RSS feeds
- Validate feed formats
- Preview parsed articles
- Check image extraction

### Unit Testing

```php
// Basic test
function testRSSParsing() {
    $parser = new EnhancedRSSParser();
    $articles = $parser->parseRSS('http://feeds.bbci.co.uk/news/world/rss.xml');
    
    assert(count($articles) > 0, "Articles should be parsed");
    assert(!empty($articles[0]['title']), "Title should be extracted");
    assert(!empty($articles[0]['image']), "Image should be extracted");
}
```

## Troubleshooting

### Common Problems

1. **No images extracted**
   - Check RSS feed format
   - Verify image URLs are accessible
   - Check media namespace declarations

2. **Duplicate content**
   - Review duplicate detection logic
   - Check title and content matching

3. **Cron job not running**
   - Verify cron syntax
   - Check file permissions
   - Review error logs

4. **Memory issues**
   - Increase PHP memory limit
   - Reduce articles per feed
   - Enable caching

### Support

For issues:
1. Check error logs
2. Test with demo interface
3. Verify RSS feed validity
4. Review system requirements

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- cURL extension
- DOM extension
- fileinfo extension
- 256MB+ RAM recommended

## Updates

To update the system:
1. Backup current files
2. Replace updated files
3. Run database migrations if needed
4. Test with demo interface

## License

This system is part of PK Live News and follows the same license terms.

---

**Quick Start**: Visit `rss_demo.php` to test the system immediately!
