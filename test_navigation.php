<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Test authentication
echo "<h1>Navigation Test</h1>";
echo "<p>Session Status: " . session_status() . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "</p>";
echo "<p>Is Logged In: " . (is_logged_in() ? 'Yes' : 'No') . "</p>";

// Test header include
echo "<hr>";
echo "<h2>Testing Header Include</h2>";
ob_start();
include __DIR__ . '/includes/header.php';
$header_output = ob_get_clean();
echo "<div>Header included successfully</div>";

// Test navigation links
echo "<hr>";
echo "<h2>Testing Navigation Links</h2>";
echo "<p><a href='profile.php'>Profile Link</a></p>";
echo "<p><a href='bookmarks.php'>Bookmarks Link</a></p>";
echo "<p><a href='settings.php'>Settings Link</a></p>";
?>
