<?php
require_once 'config/database.php';

header('Content-Type: text/plain');

echo "=== ADSENSE APPROVAL DIAGNOSTIC REPORT ===\n\n";

// Check published articles count
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published'");
$row = mysqli_fetch_assoc($result);
echo "1. Published Articles: " . $row['total'] . "\n";

// Check categories
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM categories");
$row = mysqli_fetch_assoc($result);
echo "\n2. Total Categories: " . $row['total'] . "\n";

// Check for duplicate titles
$result = mysqli_query($conn, "SELECT title, COUNT(*) as count FROM news WHERE status = 'published' GROUP BY title HAVING count > 1");
$duplicates = mysqli_num_rows($result);
echo "\n3. Duplicate Article Titles: " . $duplicates . "\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "- You have " . $row['total'] . " published articles - EXCELLENT!\n";
echo "- Need at least 30-50 high-quality original articles (you have " . $row['total'] . ")\n";
echo "- Articles should be 500+ words each\n";
echo "- All articles should have images\n";
echo "- Regular publishing schedule (2-3 articles/week)\n";
?>
