<?php
session_start();
require_once '../config/database.php';

echo "=== Edit Permission Check for Article 589 ===\n\n";

$news_id = 589;

// Check if user is logged in
echo "1. Login Status:\n";
if (isset($_SESSION['user_id'])) {
    echo "   ✅ Logged in as User ID: " . $_SESSION['user_id'] . "\n";
    echo "   Role: " . ($_SESSION['user_role'] ?? 'not set') . "\n";
} else {
    echo "   ❌ Not logged in\n";
}

// Check if article exists
echo "\n2. Article Check:\n";
$query = "SELECT id, title, author_id, status FROM news WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $news_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$news = mysqli_fetch_assoc($result);

if ($news) {
    echo "   ✅ Article exists\n";
    echo "   Title: " . $news['title'] . "\n";
    echo "   Author ID: " . $news['author_id'] . "\n";
    echo "   Status: " . $news['status'] . "\n";
} else {
    echo "   ❌ Article 589 does not exist\n";
}

// Check permissions
echo "\n3. Permission Check:\n";
$can_edit_any = (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'editor'));
$is_reporter = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'reporter');

echo "   Can edit any (admin/editor): " . ($can_edit_any ? 'Yes' : 'No') . "\n";
echo "   Is reporter: " . ($is_reporter ? 'Yes' : 'No') . "\n";

if ($news) {
    if ($can_edit_any) {
        echo "   ✅ Permission granted (admin/editor)\n";
    } elseif ($is_reporter && isset($_SESSION['user_id']) && $news['author_id'] == $_SESSION['user_id']) {
        echo "   ✅ Permission granted (article author)\n";
    } else {
        echo "   ❌ Permission denied\n";
        if ($is_reporter) {
            echo "   Reason: You are a reporter but not the author of this article\n";
        } else {
            echo "   Reason: Insufficient permissions\n";
        }
    }
}

echo "\n=== Check Complete ===\n";
