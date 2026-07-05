<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Check if tags table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tags'");
if (mysqli_num_rows($table_check) === 0) {
    die('Tags table not found');
}

// Get all tags
$tags_query = "SELECT t.*, COUNT(nt.news_id) as usage_count 
               FROM tags t 
               LEFT JOIN news_tags nt ON t.id = nt.tag_id 
               GROUP BY t.id 
               ORDER BY t.name ASC";
$tags_result = mysqli_query($conn, $tags_query);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="tags_export_' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID',
    'Name',
    'Slug',
    'Color',
    'Description',
    'Status',
    'Usage Count',
    'Created At'
]);

// Add tag data
while ($tag = mysqli_fetch_assoc($tags_result)) {
    fputcsv($output, [
        $tag['id'],
        $tag['name'],
        $tag['slug'],
        $tag['color'],
        $tag['description'],
        $tag['status'] ?? 'active',
        $tag['usage_count'],
        $tag['created_at'] ?? ''
    ]);
}

// Close output stream
fclose($output);
exit;
?>
