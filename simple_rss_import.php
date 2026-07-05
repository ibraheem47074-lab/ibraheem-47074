<?php
require_once "config/database.php";

// Simple RSS import function
function importRSSFeed($source_id, $source_url, $source_name) {
    global $conn;
    
    echo "Importing from: $source_name\n";
    
    // Try to fetch RSS feed
    $context = stream_context_create([
        "http" => [
            "timeout" => 10,
            "user_agent" => "PK Live News RSS Reader"
        ]
    ]);
    
    $xml_content = @file_get_contents($source_url, false, $context);
    
    if ($xml_content === false) {
        echo "Failed to fetch RSS feed\n";
        return false;
    }
    
    // Parse XML
    $xml = simplexml_load_string($xml_content);
    
    if ($xml === false) {
        echo "Failed to parse RSS feed\n";
        return false;
    }
    
    $imported = 0;
    
    foreach ($xml->channel->item as $item) {
        $title = (string) $item->title;
        $link = (string) $item->link;
        $description = (string) $item->description;
        $pub_date = (string) $item->pubDate;
        
        // Skip if title is empty
        if (empty($title)) continue;
        
        // Create slug
        $slug = create_slug($title);
        
        // Check if already exists
        $check_query = "SELECT id FROM news WHERE slug = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $slug);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            continue; // Skip duplicates
        }
        
        // Parse publication date
        $published_at = date("Y-m-d H:i:s");
        if (!empty($pub_date)) {
            $timestamp = strtotime($pub_date);
            if ($timestamp !== false) {
                $published_at = date("Y-m-d H:i:s", $timestamp);
            }
        }
        
        // Insert news
        $insert_query = "INSERT INTO news (title, slug, content, excerpt, source_url, status, published_at, created_at) 
                         VALUES (?, ?, ?, ?, ?, 'published', ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "sssssss", $title, $slug, $description, $description, $link, $published_at, $published_at);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $imported++;
        }
    }
    
    echo "Imported $imported articles from $source_name\n";
    
    // Update last import time
    $update_query = "UPDATE rss_sources SET last_import = NOW() WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "i", $source_id);
    mysqli_stmt_execute($update_stmt);
    
    return $imported;
}

// Run import for active sources
$sources_query = "SELECT id, name, url FROM rss_sources WHERE status = 'active'";
$sources_result = mysqli_query($conn, $sources_query);

$total_imported = 0;
while ($source = mysqli_fetch_assoc($sources_result)) {
    $imported = importRSSFeed($source["id"], $source["url"], $source["name"]);
    if ($imported !== false) {
        $total_imported += $imported;
    }
}

echo "\nTotal imported: $total_imported articles\n";
?>