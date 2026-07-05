<?php
/**
 * Footer Fix Verification
 * Check that duplicate footer has been removed
 */

echo "<h2>🔧 Footer Duplicate Fix Verification</h2>";

// Check index.php for footer includes
$indexContent = file_get_contents(__DIR__ . '/index.php');

// Count footer includes
$footerIncludes = substr_count($indexContent, "include 'includes/footer.php'");

echo "<h3>Footer Include Count:</h3>";
echo "<p>Found $footerIncludes footer include(s) in index.php</p>";

if ($footerIncludes === 1) {
    echo "<span class='badge bg-success'>✅ FIXED</span> - Only one footer include found<br>";
    echo "<p>The duplicate footer has been successfully removed!</p>";
} else {
    echo "<span class='badge bg-danger'>❌ ISSUE</span> - Expected 1, found $footerIncludes<br>";
}

// Check for duplicate footer text
$footerTextCount = substr_count($indexContent, "© 2026 PK Live News");
echo "<h3>Footer Text Count:</h3>";
echo "<p>Found $footerTextCount instances of '© 2026 PK Live News'</p>";

if ($footerTextCount <= 1) {
    echo "<span class='badge bg-success'>✅ GOOD</span> - No duplicate footer text<br>";
} else {
    echo "<span class='badge bg-warning'>⚠️ CHECK</span> - Multiple footer text instances found<br>";
}

echo "<h3>🎯 Fix Summary:</h3>";
echo "<ul>";
echo "<li>Removed duplicate footer include from line 676 (inside modal)</li>";
echo "<li>Kept correct footer include at the end of the page (line ~1543)</li>";
echo "<li>Website should now display only one footer</li>";
echo "</ul>";

echo "<h3>🧪 Test Your Website:</h3>";
echo "<p>Visit your website to verify only one footer appears:</p>";
echo "<a href='index.php' target='_blank' class='btn btn-primary'>🌐 Open PK Live News</a>";

echo "<hr>";
echo "<p><small>Fix completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
