<?php
// Database Connection Diagnostic Script
echo "<h2>Database Connection Diagnostic</h2>";

// Test 1: Check if MySQL extension is loaded
echo "<h3>1. MySQL Extension Check</h3>";
if (extension_loaded('mysqli')) {
    echo "✅ MySQLi extension is loaded<br>";
} else {
    echo "❌ MySQLi extension is NOT loaded<br>";
}

// Test 2: Test connection to MySQL server (without database)
echo "<h3>2. MySQL Server Connection Test</h3>";
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $conn_test = new mysqli($host, $user, $pass);
    if ($conn_test->connect_error) {
        echo "❌ Cannot connect to MySQL server: " . $conn_test->connect_error . "<br>";
        echo "Error code: " . $conn_test->connect_errno . "<br>";
    } else {
        echo "✅ Connected to MySQL server successfully<br>";
        echo "MySQL Server Info: " . $conn_test->server_info . "<br>";
        echo "MySQL Host Info: " . $conn_test->host_info . "<br>";
        $conn_test->close();
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
}

// Test 3: Test different ports
echo "<h3>3. Port Testing</h3>";
$ports = [3306, 3307, 3308];
foreach ($ports as $port) {
    $socket = @fsockopen($host, $port, $errno, $errstr, 2);
    if ($socket) {
        echo "✅ Port $port is open<br>";
        fclose($socket);
    } else {
        echo "❌ Port $port is closed or not accessible: $errstr<br>";
    }
}

// Test 4: Check if database exists
echo "<h3>4. Database Check</h3>";
try {
    $conn_test = new mysqli($host, $user, $pass);
    if (!$conn_test->connect_error) {
        $result = $conn_test->query("SHOW DATABASES LIKE 'pk_live_news'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Database 'pk_live_news' exists<br>";
        } else {
            echo "❌ Database 'pk_live_news' does not exist<br>";
            echo "Available databases:<br>";
            $dbs = $conn_test->query("SHOW DATABASES");
            while ($db = $dbs->fetch_array()) {
                if ($db[0] !== 'information_schema' && $db[0] !== 'mysql' && $db[0] !== 'performance_schema') {
                    echo "- " . $db[0] . "<br>";
                }
            }
        }
        $conn_test->close();
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
}

// Test 5: PHP Info
echo "<h3>5. PHP Configuration</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "MySQLi Client Version: " . mysqli_get_client_info() . "<br>";

echo "<h3>Solutions to Try:</h3>";
echo "<ol>";
echo "<li><strong>Start XAMPP MySQL Service:</strong> Open XAMPP Control Panel and start MySQL</li>";
echo "<li><strong>Check MySQL Port:</strong> MySQL might be running on a different port (3307, 3308)</li>";
echo "<li><strong>Reset MySQL Password:</strong> If password is set, update config/database.php</li>";
echo "<li><strong>Create Database:</strong> Run create_database.php to create the required database</li>";
echo "<li><strong>Check Firewall:</strong> Ensure Windows Firewall isn't blocking MySQL</li>";
echo "</ol>";
?>
