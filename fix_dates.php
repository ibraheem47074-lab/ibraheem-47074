<?php
require_once 'config/database.php';

echo '<h2>Fixing Invalid News Dates</h2>';

// Find all articles with invalid published_at dates
$fix_query = 'SELECT id, title, created_at, published_at
              FROM news 
              WHERE status = "published" AND 
              (published_at IS NULL OR published_at = "0000-00-00 00:00:00" OR published_at = "1970-01-01 00:00:00")';

$result = mysqli_query($conn, $fix_query);
$fixed_count = 0;

echo '<h3>Articles to Fix:</h3>';
echo '<table border="1" cellpadding="5">';
echo '<tr><th>ID</th><th>Title</th><th>Current Published At</th><th>Will Use Created At</th><th>Action</th></tr>';

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . $row['id'] . '</td>';
    echo '<td>' . htmlspecialchars(substr($row['title'], 0, 50)) . '...</td>';
    echo '<td style="color: red;">' . ($row['published_at'] ?? 'NULL') . '</td>';
    echo '<td style="color: green;">' . $row['created_at'] . '</td>';
    
    // Update the published_at with created_at if created_at is valid
    if (!empty($row['created_at']) && $row['created_at'] !== '0000-00-00 00:00:00' && $row['created_at'] !== '1970-01-01 00:00:00') {
        $update_query = "UPDATE news SET published_at = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'si', $row['created_at'], $row['id']);
        
        if (mysqli_stmt_execute($update_stmt)) {
            echo '<td style="color: green;">✓ Fixed</td>';
            $fixed_count++;
        } else {
            echo '<td style="color: red;">✗ Error: ' . mysqli_error($conn) . '</td>';
        }
        mysqli_stmt_close($update_stmt);
    } else {
        echo '<td style="color: orange;">⚠ Skipped (invalid created_at)</td>';
    }
    
    echo '</tr>';
}
echo '</table>';

echo '<h3>Fix Summary</h3>';
echo '<p><strong>Total articles processed:</strong> ' . mysqli_num_rows($result) . '</p>';
echo '<p><strong>Articles fixed:</strong> <span style="color: green;">' . $fixed_count . '</span></p>';

if ($fixed_count > 0) {
    echo '<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>✓ Success!</strong> ' . $fixed_count . ' articles have been updated with proper dates.';
    echo '<br><a href="index.php">View Homepage</a> | <a href="check_dates.php">Verify Fix</a>';
    echo '</div>';
} else {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>⚠ No articles were fixed.</strong> All articles may already have valid dates.';
    echo '</div>';
}
?>
