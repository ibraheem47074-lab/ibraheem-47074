<?php
/**
 * Fix RSS Import Database Issues
 */

require_once 'config/database.php';

header('Content-Type: text/plain');

echo "PK Live News - RSS Database Fix\n";
echo "===============================\n\n";

// Check if news_type column exists
echo "1. Checking news_type column...\n";
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'news_type'");
if (mysqli_num_rows($check_column) > 0) {
    echo "✓ news_type column already exists\n";
} else {
    echo "✗ news_type column missing - adding it...\n";
    $add_column = "ALTER TABLE news ADD COLUMN news_type VARCHAR(50) DEFAULT 'manual' AFTER status";
    if (mysqli_query($conn, $add_column)) {
        echo "✓ news_type column added successfully\n";
    } else {
        echo "✗ Error adding news_type column: " . mysqli_error($conn) . "\n";
    }
}

// Check if slug column exists (for URL generation)
echo "\n2. Checking slug column...\n";
$check_slug = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'slug'");
if (mysqli_num_rows($check_slug) > 0) {
    echo "✓ slug column already exists\n";
} else {
    echo "✗ slug column missing - adding it...\n";
    $add_slug = "ALTER TABLE news ADD COLUMN slug VARCHAR(255) UNIQUE AFTER title";
    if (mysqli_query($conn, $add_slug)) {
        echo "✓ slug column added successfully\n";
        
        // Generate slugs for existing articles
        echo "Generating slugs for existing articles...\n";
        $update_query = "UPDATE news SET slug = LOWER(REPLACE(REPLACE(REPLACE(title, ' ', '-'), '.', ''), '--', '-')) WHERE slug IS NULL OR slug = ''";
        if (mysqli_query($conn, $update_query)) {
            $affected = mysqli_affected_rows($conn);
            echo "✓ Generated slugs for $affected articles\n";
        }
    } else {
        echo "✗ Error adding slug column: " . mysqli_error($conn) . "\n";
    }
}

// Check if sentiment_score column exists
echo "\n3. Checking sentiment_score column...\n";
$check_sentiment = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'sentiment_score'");
if (mysqli_num_rows($check_sentiment) > 0) {
    echo "✓ sentiment_score column already exists\n";
} else {
    echo "✗ sentiment_score column missing - adding it...\n";
    $add_sentiment = "ALTER TABLE news ADD COLUMN sentiment_score DECIMAL(3,2) DEFAULT 0.00 AFTER content";
    if (mysqli_query($conn, $add_sentiment)) {
        echo "✓ sentiment_score column added successfully\n";
    } else {
        echo "✗ Error adding sentiment_score column: " . mysqli_error($conn) . "\n";
    }
}

// Check if sentiment_label column exists
echo "\n4. Checking sentiment_label column...\n";
$check_sentiment_label = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'sentiment_label'");
if (mysqli_num_rows($check_sentiment_label) > 0) {
    echo "✓ sentiment_label column already exists\n";
} else {
    echo "✗ sentiment_label column missing - adding it...\n";
    $add_sentiment_label = "ALTER TABLE news ADD COLUMN sentiment_label VARCHAR(20) DEFAULT 'neutral' AFTER sentiment_score";
    if (mysqli_query($conn, $add_sentiment_label)) {
        echo "✓ sentiment_label column added successfully\n";
    } else {
        echo "✗ Error adding sentiment_label column: " . mysqli_error($conn) . "\n";
    }
}

// Check if source_url column exists
echo "\n5. Checking source_url column...\n";
$check_source_url = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source_url'");
if (mysqli_num_rows($check_source_url) > 0) {
    echo "✓ source_url column already exists\n";
} else {
    echo "✗ source_url column missing - adding it...\n";
    $add_source_url = "ALTER TABLE news ADD COLUMN source_url TEXT AFTER image";
    if (mysqli_query($conn, $add_source_url)) {
        echo "✓ source_url column added successfully\n";
    } else {
        echo "✗ Error adding source_url column: " . mysqli_error($conn) . "\n";
    }
}

// Test RSS import with a simple feed
echo "\n6. Testing RSS feed access...\n";
$test_feeds = [
    'https://feeds.bbci.co.uk/news/rss.xml',
    'https://rss.cnn.com/rss/edition.rss'
];

foreach ($test_feeds as $feed) {
    echo "Testing: $feed\n";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PK-Live-News-RSS-Importer/1.0'
        ]
    ]);
    
    $xml_content = @file_get_contents($feed, false, $context);
    if ($xml_content) {
        $xml = @simplexml_load_string($xml_content);
        if ($xml) {
            echo "✓ Feed is accessible and valid\n";
        } else {
            echo "✗ Feed accessible but XML is invalid\n";
        }
    } else {
        echo "✗ Feed not accessible\n";
    }
}

echo "\n7. Checking RSS sources in database...\n";
$sources_query = "SELECT name, rss_url, status FROM news_sources WHERE type = 'rss' ORDER BY name";
$sources_result = mysqli_query($conn, $sources_query);
if ($sources_result) {
    $total = mysqli_num_rows($sources_result);
    $active = 0;
    echo "Total RSS sources: $total\n";
    
    while ($source = mysqli_fetch_assoc($sources_result)) {
        echo "- {$source['name']} ({$source['status']})\n";
        if ($source['status'] === 'active') $active++;
    }
    echo "Active sources: $active\n";
} else {
    echo "Error fetching sources: " . mysqli_error($conn) . "\n";
}

echo "\nDatabase fix completed!\n";
echo "You can now test RSS import by visiting:\n";
echo "cron_import_news.php?cron_key=pk_live_news_2024_cron\n";
?>
