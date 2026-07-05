<?php
require_once 'config/database.php';

header('Content-Type: text/plain');

echo "Checking news articles in database...\n";

$query = "SELECT id, title, slug, status FROM news ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo "Query failed: " . mysqli_error($conn) . "\n";
    exit;
}

$count = mysqli_num_rows($result);
echo "Found $count news articles:\n\n";

while ($row = mysqli_fetch_assoc($result)) {
    echo "ID: {$row['id']} - {$row['title']} (Status: {$row['status']})\n";
    echo "  Slug: {$row['slug']}\n\n";
}

if ($count === 0) {
    echo "No news articles found. Checking if news table exists...\n";
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news'");
    if (mysqli_num_rows($table_check) === 0) {
        echo "News table does not exist!\n";
    } else {
        echo "News table exists but is empty.\n";
    }
}
?>
