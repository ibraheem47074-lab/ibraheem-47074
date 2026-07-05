<?php
/**
 * Test RSS feed accessibility and content
 */
require_once __DIR__ . '/config/database.php';

// Test RSS feeds directly
$feeds = [
    'Al Jazeera' => 'https://www.aljazeera.com/xml/rss/all.xml',
    'BBC News' => 'http://feeds.bbci.co.uk/news/rss.xml',
    'BBC Urdu' => 'https://www.bbc.com/urdu/rss.xml',
    'Bloomberg' => 'https://www.bloomberg.com/feed/',
    'CBS News' => 'https://www.cbsnews.com/rss/',
    'CNN News' => 'http://rss.cnn.com/rss/edition.rss',
    'Fox News' => 'https://www.foxnews.com/about/rss',
    'Pakistan Today' => 'https://www.pakistantoday.com.pk/feed/',
    'Reuters News' => 'https://www.reuters.com/rssFeed/worldNews',
    'The News International' => 'https://www.thenews.com.pk/rss/',
    'Times of India' => 'https://timesofindia.indiatimes.com/rssfeeds/54829791.cms'
];

echo "<h2>RSS Feed Test Results</h2>";

foreach ($feeds as $name => $url) {
    echo "<h3>$name</h3>";
    echo "<p><strong>URL:</strong> $url</p>";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => [
            'Accept: application/rss+xml,application/xml,text/xml,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5'
        ]
    ]);
    
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<p><strong>HTTP Status:</strong> $httpCode</p>";
    
    if ($error) {
        echo "<p><strong>Error:</strong> $error</p>";
    } elseif ($httpCode == 200) {
        echo "<p><strong>✓ Feed accessible</strong></p>";
        
        // Check if it's valid XML
        $xml = @simplexml_load_string($data);
        if ($xml) {
            echo "<p><strong>✓ Valid XML/RSS</strong></p>";
            
            // Count items
            if (isset($xml->channel->item)) {
                $itemCount = count($xml->channel->item);
                echo "<p><strong>Articles found:</strong> $itemCount</p>";
                
                // Show first article title
                if ($itemCount > 0) {
                    $firstTitle = (string) $xml->channel->item[0]->title;
                    echo "<p><strong>First article:</strong> " . htmlspecialchars(substr($firstTitle, 0, 100)) . "...</p>";
                }
            }
        } else {
            echo "<p><strong>✗ Invalid XML/RSS format</strong></p>";
            echo "<p><strong>Response preview:</strong> " . htmlspecialchars(substr($data, 0, 200)) . "...</p>";
        }
    } else {
        echo "<p><strong>✗ HTTP Error: $httpCode</strong></p>";
        echo "<p><strong>Response preview:</strong> " . htmlspecialchars(substr($data, 0, 200)) . "...</p>";
    }
    
    echo "<hr>";
}
?>
