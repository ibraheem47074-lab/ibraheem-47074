<?php
// Simple database connection test
echo "<h2>Database Connection Test</h2>";

// Test with local credentials first
echo "<h3>Testing Local Database</h3>";
try {
    $conn = new mysqli('localhost', 'root', '', 'pk_live_news');
    if ($conn->connect_error) {
        echo "<span style='color: red;'>Local DB Error: " . $conn->connect_error . "</span><br>";
    } else {
        echo "<span style='color: green;'>Local DB: Connected successfully</span><br>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>Local DB Exception: " . $e->getMessage() . "</span><br>";
}

// Test with production credentials (will fail locally)
echo "<h3>Testing Production Database (Expected to fail locally)</h3>";
try {
    $conn = new mysqli('localhost', 'u129650532_ibraheem', 'Khan47074$', 'u129650532_ibraheem');
    if ($conn->connect_error) {
        echo "<span style='color: orange;'>Production DB: " . $conn->connect_error . "</span><br>";
        echo "<span style='color: blue;'>This is expected in local environment</span><br>";
    } else {
        echo "<span style='color: green;'>Production DB: Connected successfully</span><br>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<span style='color: orange;'>Production DB Exception: " . $e->getMessage() . "</span><br>";
}

// Check environment variables
echo "<h3>Environment Variables</h3>";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "<br>";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'Not defined') . "<br>";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "<br>";
echo "APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'Not defined') . "<br>";
echo "SITE_URL: " . (defined('SITE_URL') ? SITE_URL : 'Not defined') . "<br>";
?>
