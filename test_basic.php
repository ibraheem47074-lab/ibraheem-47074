<!DOCTYPE html>
<html>
<head>
    <title>Basic Test Page</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        input { padding: 8px; margin: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 PK Live News - Basic System Test</h1>
        
        <div class="test-section">
            <h3>✅ HTML & CSS Test</h3>
            <p class="success">HTML and CSS are working correctly!</p>
        </div>

        <div class="test-section">
            <h3>📅 Current Time</h3>
            <p><?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <div class="test-section">
            <h3>🌐 Server Information</h3>
            <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
            <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
            <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></p>
        </div>

        <div class="test-section">
            <h3>📁 File System Test</h3>
            <?php
            $test_files = ['config/database.php', 'index.php', 'simple_setup.php'];
            foreach ($test_files as $file) {
                $exists = file_exists($file);
                echo "<p class='" . ($exists ? 'success' : 'error') . "'>";
                echo "$file: " . ($exists ? "EXISTS" : "MISSING");
                echo "</p>";
            }
            ?>
        </div>

        <div class="test-section">
            <h3>🗄️ Database Connection Test</h3>
            <?php
            $db_test = false;
            try {
                $conn = @mysqli_connect('localhost', 'root', '');
                if ($conn) {
                    echo "<p class='success'>✓ MySQL connection successful</p>";
                    
                    // Test database creation
                    $db_name = 'pk_live_news';
                    if (@mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$db_name`")) {
                        echo "<p class='success'>✓ Database '$db_name' ready</p>";
                        $db_test = true;
                    } else {
                        echo "<p class='error'>✗ Cannot create database</p>";
                    }
                    mysqli_close($conn);
                } else {
                    echo "<p class='error'>✗ MySQL connection failed</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>✗ Database error: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>

        <div class="test-section">
            <h3>🔗 Interactive Test</h3>
            <form method="GET">
                <input type="text" name="test_input" placeholder="Type something and submit">
                <button type="submit">Test GET</button>
            </form>
            
            <?php if (isset($_GET['test_input'])): ?>
                <p class="info">GET received: <?php echo htmlspecialchars($_GET['test_input']); ?></p>
            <?php endif; ?>
            
            <form method="POST">
                <input type="text" name="post_input" placeholder="Type something and submit">
                <button type="submit">Test POST</button>
            </form>
            
            <?php if (isset($_POST['post_input'])): ?>
                <p class="info">POST received: <?php echo htmlspecialchars($_POST['post_input']); ?></p>
            <?php endif; ?>
        </div>

        <div class="test-section">
            <h3>🌍 Language Test</h3>
            <p><strong>Browser Language:</strong> <?php echo $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown'; ?></p>
            <p><strong>Current URL:</strong> <?php echo $_SERVER['REQUEST_URI']; ?></p>
            
            <form method="GET">
                <input type="hidden" name="lang" value="en">
                <button type="submit">🇺🇸 English</button>
            </form>
            
            <form method="GET" style="display: inline;">
                <input type="hidden" name="lang" value="ur">
                <button type="submit">🇵🇰 اردو</button>
            </form>
            
            <form method="GET" style="display: inline;">
                <input type="hidden" name="lang" value="hi">
                <button type="submit">🇮🇳 हिन्दी</button>
            </form>
            
            <?php if (isset($_GET['lang'])): ?>
                <p class="info">Language selected: <?php echo htmlspecialchars($_GET['lang']); ?></p>
            <?php endif; ?>
        </div>

        <?php if ($db_test): ?>
        <div class="test-section">
            <h3>🚀 Next Steps</h3>
            <p class="success">✅ Database is ready! You can now:</p>
            <ul>
                <li><a href="simple_setup.php">Run Multi-Language Setup</a></li>
                <li><a href="index_minimal.php">Test Multi-Language System</a></li>
                <li><a href="admin/">Access Admin Panel</a></li>
            </ul>
        </div>
        <?php else: ?>
        <div class="test-section">
            <h3>⚠️ Database Issues</h3>
            <p class="error">Database connection failed. Please check:</p>
            <ul>
                <li>MySQL service is running</li>
                <li>Username: root, Password: (empty)</li>
                <li>Port 3306 is accessible</li>
                <li>No firewall blocking MySQL</li>
            </ul>
        </div>
        <?php endif; ?>

        <div class="test-section">
            <h3>📞 Troubleshooting</h3>
            <p><strong>If this page doesn't load:</strong></p>
            <ol>
                <li>Check web server is running (Apache/Nginx)</li>
                <li>Verify PHP is installed and working</li>
                <li>Try accessing via: http://localhost/pk-live-news/test_basic.php</li>
                <li>Check Windows Event Viewer for errors</li>
                <li>Temporarily rename .htaccess file if it exists</li>
            </ol>
        </div>
    </div>
</body>
</html>
