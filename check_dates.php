<?php
require_once 'config/database.php';

echo '<h2>Checking News Date Issues</h2>';

// Check for articles with invalid dates
$query = 'SELECT id, title, created_at, published_at, 
          CASE 
            WHEN published_at IS NULL OR published_at = "0000-00-00 00:00:00" OR published_at = "1970-01-01 00:00:00" THEN "invalid_published"
            WHEN created_at IS NULL OR created_at = "0000-00-00 00:00:00" OR created_at = "1970-01-01 00:00:00" THEN "invalid_created"
            ELSE "valid"
          END as date_status
          FROM news 
          WHERE status = "published" 
          ORDER BY id DESC LIMIT 10';

$result = mysqli_query($conn, $query);
echo '<table border="1" cellpadding="5">';
echo '<tr><th>ID</th><th>Title</th><th>Created At</th><th>Published At</th><th>Date Status</th></tr>';

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . $row['id'] . '</td>';
    echo '<td>' . htmlspecialchars(substr($row['title'], 0, 50)) . '...</td>';
    echo '<td>' . $row['created_at'] . '</td>';
    echo '<td>' . ($row['published_at'] ?? 'NULL') . '</td>';
    echo '<td style="color: ' . ($row['date_status'] == 'valid' ? 'green' : 'red') . '">' . $row['date_status'] . '</td>';
    echo '</tr>';
}
echo '</table>';

// Count articles with date issues
$invalid_query = 'SELECT COUNT(*) as invalid_count 
                FROM news 
                WHERE status = "published" AND 
                (published_at IS NULL OR published_at = "0000-00-00 00:00:00" OR published_at = "1970-01-01 00:00:00")';
$invalid_result = mysqli_query($conn, $invalid_query);
$invalid_count = mysqli_fetch_assoc($invalid_result)['invalid_count'];

echo '<h3>Summary</h3>';
echo '<p><strong>Total articles with invalid published dates:</strong> ' . $invalid_count . '</p>';

if ($invalid_count > 0) {
    echo '<p style="color: red;"><strong>Issue Found:</strong> Many articles have invalid published dates, causing "No date" display.</p>';
    echo '<p><strong>Solution:</strong> Update published_at dates or fix the date handling logic.</p>';
}
?>
