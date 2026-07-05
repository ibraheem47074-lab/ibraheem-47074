<?php
echo "<h1>PK-LIVE NEWS Access Fix</h1>";

echo "<h2>🚨 Issue Identified</h2>";
echo "<p>The URL <code>http://localhost/PK-LIVE%20NEWS/admin/add-news.php</code> cannot be accessed because:</p>";
echo "<ul>";
echo "<li><strong>URL Rewriting Conflict:</strong> The .htaccess rewrite rules interfere with URLs containing %20 (spaces)</li>";
echo "<li><strong>Apache Configuration:</strong> Direct file access may be blocked</li>";
echo "<li><strong>Directory Permissions:</strong> Admin directory access restrictions</li>";
echo "</ul>";

echo "<h2>✅ Solutions (Try in order)</h2>";

echo "<h3>Solution 1: Use Direct Access File (Recommended)</h3>";
echo "<p>Access the admin panel through this bypass file:</p>";
echo "<p><a href='admin_direct.php' style='font-size: 18px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>📝 Open Admin Panel</a></p>";

echo "<h3>Solution 2: Use Alternative URLs</h3>";
echo "<p>Try these alternative URLs:</p>";
echo "<ul>";
echo "<li><a href='http://127.0.0.1/PK-LIVE%20NEWS/admin/add-news.php'>http://127.0.0.1/PK-LIVE%20NEWS/admin/add-news.php</a></li>";
echo "<li><a href='http://localhost/PK-LIVE%20NEWS/admin/'>http://localhost/PK-LIVE%20NEWS/admin/</a> (then click add-news.php)</li>";
echo "<li><a href='http://localhost/PK-LIVE%20NEWS/admin_direct.php'>http://localhost/PK-LIVE%20NEWS/admin_direct.php</a> (direct bypass)</li>";
echo "</ul>";

echo "<h3>Solution 3: Fix URL Encoding</h3>";
echo "<p>Replace spaces in URL with proper encoding:</p>";
echo "<ul>";
echo "<li><a href='http://localhost/PK-LIVE%20NEWS/admin/add-news.php'>Current (with %20)</a></li>";
echo "<li><a href='http://localhost/PK-LIVE%20NEWS/admin/add-news.php'>Try without %20</a></li>";
echo "</ul>";

echo "<h3>Solution 4: Check Apache Status</h3>";
echo "<p>Ensure Apache is running properly:</p>";
echo "<ol>";
echo "<li>Open XAMPP Control Panel</li>";
echo "<li>Check Apache status (should be 'Running')</li>";
echo "<li>If not running, click 'Start' next to Apache</li>";
echo "</ol>";

echo "<h3>Solution 5: Temporary Disable .htaccess</h3>";
echo "<p>If all else fails, temporarily rename .htaccess:</p>";
echo "<code>rename .htaccess .htaccess.backup</code>";

echo "<hr>";
echo "<p><strong>🎯 Most Likely Solution:</strong> Use the direct access file above (Solution 1) as it bypasses all URL rewriting issues.</p>";
?>
