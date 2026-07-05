<?php
require_once 'config/database.php';

echo "=== Fixing Duplicate Article Titles ===\n\n";

// Find duplicates
$duplicate_query = "SELECT title, COUNT(*) as count, GROUP_CONCAT(id ORDER BY id) as ids FROM news WHERE status = 'published' GROUP BY title HAVING count > 1";
$result = mysqli_query($conn, $duplicate_query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ids = explode(',', $row['ids']);
        $title = $row['title'];
        $count = $row['count'];
        
        echo "Found duplicate: '{$title}' ({$count} copies, IDs: " . implode(', ', $ids) . ")\n";
        
        // Keep the first one, add suffix to others
        $keep_id = $ids[0];
        for ($i = 1; $i < count($ids); $i++) {
            $update_id = $ids[$i];
            $new_title = $title . " (Copy " . ($i) . ")";
            
            $update = "UPDATE news SET title = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, 'si', $new_title, $update_id);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "  ✅ Updated ID {$update_id} to: '{$new_title}'\n";
            } else {
                echo "  ❌ Error updating ID {$update_id}: " . mysqli_error($conn) . "\n";
            }
        }
        echo "\n";
    }
} else {
    echo "No duplicate titles found.\n";
}

echo "=== Duplicate Title Fix Complete ===\n";
