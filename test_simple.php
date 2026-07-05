<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PK-LIVE NEWS System Test</h1>";

echo "<h2>1. PHP Status</h2>";
echo "✓ PHP is working (Version: " . PHP_VERSION . ")<br>";

echo "<h2>2. Database Connection</h2>";
try {
    $conn = new mysqli('localhost', 'root', '', 'pk_live_news');
    if ($conn->connect_error) {
        echo "✗ Database Connection Failed<br>";
        echo "<strong>Error:</strong> " . $conn->connect_error . "<br>";
        echo "<strong>Error Code:</strong> " . $conn->connect_errno . "<br>";
        echo "<div style='background: #ffebee; padding: 10px; border: 1px solid #f44336; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>🚨 SOLUTION REQUIRED</h3>";
        echo "<p><strong>MySQL service is not running!</strong></p>";
        echo "<ol>";
        echo "<li>Open XAMPP Control Panel</li>";
        echo "<li>Click 'Start' next to MySQL</li>";
        echo "<li>Wait for status to show 'Running'</li>";
        echo "<li>Refresh this page</li>";
        echo "</ol>";
        echo "<p>After starting MySQL, the page should load normally.</p>";
        echo "</div>";
    } else {
        echo "✓ Database Connected Successfully<br>";
        echo "<strong>Host:</strong> localhost<br>";
        echo "<strong>Database:</strong> pk_live_news<br>";
        
        // Test query
        $result = $conn->query("SELECT COUNT(*) as count FROM news");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "✓ News Table Accessible<br>";
            echo "<strong>Total Articles:</strong> " . $row['count'] . "<br>";
        }
        $conn->close();
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "<br>";
}

echo "<h2>3. File Structure</h2>";
$required_files = [
    'config/database.php',
    'admin/add-news.php',
    'index.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✓ $file<br>";
    } else {
        echo "✗ $file (MISSING)<br>";
    }
}

echo "<h2>4. Session Status</h2>";
echo "Session Status: " . session_status() . "<br>";

echo "<h2>5. Upload Directories</h2>";
$dirs = ['uploads', 'uploads/news', 'uploads/news/images', 'uploads/news/videos'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "✓ $dir<br>";
    } else {
        echo "✗ $dir (MISSING)<br>";
    }
}

echo "<hr>";
echo "<p><small>This test page helps diagnose why you're seeing a white page.</small></p>";
echo "<p><small><strong>If database connection fails above, start MySQL service in XAMPP.</strong></small></p>";
?>
