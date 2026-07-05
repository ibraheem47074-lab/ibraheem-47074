<?php
require_once 'config/database.php';
require_once 'config/settings.php';

// Create backup directory
$backup_dir = 'backups/articles_' . date('Y-m-d_H-i-s');
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

echo "Starting backup process...\n";
echo "Backup directory: $backup_dir\n";

// Get all articles with their media
$articles_query = "SELECT * FROM news ORDER BY id DESC";
$result = $conn->query($articles_query);

if ($result->num_rows > 0) {
    $articles_data = [];
    $media_files = [];
    
    while ($row = $result->fetch_assoc()) {
        $article_id = $row['id'];
        
        // Add article data
        $articles_data[] = $row;
        
        // Copy image if exists
        if (!empty($row['image_url']) && file_exists($row['image_url'])) {
            $image_path = $row['image_url'];
            $image_name = basename($image_path);
            $new_image_path = $backup_dir . '/images/' . $article_id . '_' . $image_name;
            
            // Create images directory
            if (!is_dir($backup_dir . '/images')) {
                mkdir($backup_dir . '/images', 0755, true);
            }
            
            if (copy($image_path, $new_image_path)) {
                $media_files[] = [
                    'article_id' => $article_id,
                    'type' => 'image',
                    'original_path' => $image_path,
                    'backup_path' => $new_image_path
                ];
                echo "Copied image: $image_path -> $new_image_path\n";
            }
        }
        
        // Copy video if exists
        if (!empty($row['video_url']) && file_exists($row['video_url'])) {
            $video_path = $row['video_url'];
            $video_name = basename($video_path);
            $new_video_path = $backup_dir . '/videos/' . $article_id . '_' . $video_name;
            
            // Create videos directory
            if (!is_dir($backup_dir . '/videos')) {
                mkdir($backup_dir . '/videos', 0755, true);
            }
            
            if (copy($video_path, $new_video_path)) {
                $media_files[] = [
                    'article_id' => $article_id,
                    'type' => 'video',
                    'original_path' => $video_path,
                    'backup_path' => $new_video_path
                ];
                echo "Copied video: $video_path -> $new_video_path\n";
            }
        }
    }
    
    // Save articles data to JSON
    file_put_contents($backup_dir . '/articles_data.json', json_encode($articles_data, JSON_PRETTY_PRINT));
    
    // Save media files mapping
    file_put_contents($backup_dir . '/media_files.json', json_encode($media_files, JSON_PRETTY_PRINT));
    
    // Save SQL backup
    $sql_backup = "-- Articles Backup - " . date('Y-m-d H:i:s') . "\n";
    $sql_backup .= "-- Total articles: " . count($articles_data) . "\n\n";
    
    foreach ($articles_data as $article) {
        $columns = array_keys($article);
        $values = array_map(function($val) {
            return $val === null ? 'NULL' : "'" . addslashes($val) . "'";
        }, array_values($article));
        
        $sql_backup .= "INSERT INTO news (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $values) . ");\n";
    }
    
    file_put_contents($backup_dir . '/articles_backup.sql', $sql_backup);
    
    echo "\n=== BACKUP COMPLETED ===\n";
    echo "Total articles backed up: " . count($articles_data) . "\n";
    echo "Total media files copied: " . count($media_files) . "\n";
    echo "Backup location: $backup_dir\n";
    echo "Files created:\n";
    echo "- articles_data.json (article data)\n";
    echo "- articles_backup.sql (SQL backup)\n";
    echo "- media_files.json (media mapping)\n";
    echo "- images/ (copied images)\n";
    echo "- videos/ (copied videos)\n";
    
} else {
    echo "No articles found to backup.\n";
}

$conn->close();
?>
