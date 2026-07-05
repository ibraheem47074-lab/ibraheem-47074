<?php
require_once 'config/database.php';

echo "=== Removing Articles Without Images ===\n\n";

// Check which image column to use
$image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_url'");
$use_image_url = mysqli_num_rows($image_check) > 0;

if (!$use_image_url) {
    $image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image'");
    $use_image = mysqli_num_rows($image_check) > 0;
}

// Find articles without images
if ($use_image_url) {
    $query = "SELECT id, title, image_url FROM news WHERE status = 'published' AND (image_url IS NULL OR image_url = '' OR image_url = 'uploads/')";
} elseif ($use_image) {
    $query = "SELECT id, title, image FROM news WHERE status = 'published' AND (image IS NULL OR image = '' OR image = 'uploads/')";
} else {
    echo "❌ No image column found in news table.\n";
    exit;
}

$result = mysqli_query($conn, $query);
$articles_without_images = [];

while ($row = mysqli_fetch_assoc($result)) {
    $articles_without_images[] = [
        'id' => $row['id'],
        'title' => $row['title']
    ];
}

if (count($articles_without_images) > 0) {
    echo "Found " . count($articles_without_images) . " articles without images:\n\n";
    
    foreach ($articles_without_images as $article) {
        echo "ID: {$article['id']}\n";
        echo "Title: {$article['title']}\n";
        echo "---\n";
    }
    
    echo "\n⚠️ WARNING: This will DELETE these articles permanently!\n";
    echo "Type 'DELETE' to confirm deletion: ";
    
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    
    if (trim($line) === 'DELETE') {
        echo "\nDeleting articles...\n";
        
        $deleted_count = 0;
        foreach ($articles_without_images as $article) {
            $delete_query = "DELETE FROM news WHERE id = {$article['id']}";
            if (mysqli_query($conn, $delete_query)) {
                $deleted_count++;
                echo "✅ Deleted ID {$article['id']}: {$article['title']}\n";
            } else {
                echo "❌ Failed to delete ID {$article['id']}: " . mysqli_error($conn) . "\n";
            }
        }
        
        echo "\n✅ Successfully deleted {$deleted_count} articles without images.\n";
    } else {
        echo "\n❌ Deletion cancelled.\n";
    }
    
} else {
    echo "✅ All published articles have images!\n";
}

echo "\n=== Removal Process Complete ===\n";
