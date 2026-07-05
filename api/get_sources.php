<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Get current sources
$query = "SELECT id, name, url, rss_url, type, status, created_at FROM news_sources ORDER BY created_at DESC LIMIT 10";
$result = mysqli_query($conn, $query);

$sources = [];
while ($row = mysqli_fetch_assoc($result)) {
    $sources[] = $row;
}

echo json_encode([
    'success' => true,
    'sources' => $sources,
    'total' => count($sources)
]);

mysqli_close($conn);
?>
