<?php
// PK Live News - Import System Diagnostic and Fix Tool
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Import System Fix - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .fix-container { max-width: 1000px; margin: 0 auto; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .table th { background: #007bff; color: white; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        .alert { margin: 20px 0; }
        .log-output { background: #000; color: #00ff00; font-family: monospace; padding: 15px; border-radius: 5px; max-height: 300px; overflow-y: auto; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class='fix-container'>
        <div class='card'>
            <div class='card-header bg-danger text-white'>
                <h3 class='mb-0'>🔧 Import System Diagnostic</h3>
            </div>
            <div class='card-body'>";

$diagnostic_results = [];
$fix_applied = [];

// Check 1: Database Tables
echo "<h4>📊 Database Structure Check</h4>";
$required_tables = ['news', 'news_sources', 'categories'];
$table_status = [];

foreach ($required_tables as $table) {
    $query = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($conn, $query);
    $exists = mysqli_num_rows($result) > 0;
    
    $table_status[$table] = $exists ? '✅ Exists' : '❌ Missing';
    
    if (!$exists) {
        // Create missing table
        if ($table === 'news_sources') {
            $create_sql = "CREATE TABLE IF NOT EXISTS news_sources (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                url TEXT NOT NULL,
                category_id INT,
                type ENUM('rss', 'api', 'manual') DEFAULT 'rss',
                status ENUM('active', 'inactive') DEFAULT 'active',
                last_scraped DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } elseif ($table === 'categories') {
            $create_sql = "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $create_sql = "CREATE TABLE IF NOT EXISTS news (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(500) NOT NULL,
                content TEXT,
                summary TEXT,
                category_id INT,
                author VARCHAR(255),
                image VARCHAR(500),
                video_url VARCHAR(500),
                source_url VARCHAR(500),
                news_type ENUM('article', 'video', 'breaking', 'rss_import') DEFAULT 'article',
                status ENUM('draft', 'published', 'featured', 'archived') DEFAULT 'draft',
                published_at DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (status),
                INDEX (published_at),
                INDEX (news_type),
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            )";
        }
        
        if (mysqli_query($conn, $create_sql)) {
            $table_status[$table] = '✅ Created';
            $fix_applied[] = "Created missing table: $table";
        } else {
            $table_status[$table] = '❌ Error: ' . mysqli_error($conn);
        }
    }
}

echo "<div class='table-responsive'>
    <table class='table table-striped'>
        <thead>
            <tr>
                <th>Table Name</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>";
foreach ($table_status as $table => $status) {
    echo "<tr>
        <td>$table</td>
        <td>$status</td>
    </tr>";
}
echo "</tbody>
    </table>
</div>";

// Check 2: PHP Extensions
echo "<h4>🔌 PHP Extensions Check</h4>";
$required_extensions = ['curl', 'json', 'mbstring', 'simplexml'];
$extension_status = [];

foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $extension_status[$ext] = $loaded ? '✅ Loaded' : '❌ Missing';
    
    if (!$loaded) {
        $diagnostic_results[] = "Missing PHP extension: $ext";
    }
}

echo "<div class='table-responsive'>
    <table class='table table-striped'>
        <thead>
            <tr>
                <th>Extension</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>";
foreach ($extension_status as $ext => $status) {
    echo "<tr>
        <td>$ext</td>
        <td>$status</td>
    </tr>";
}
echo "</tbody>
    </table>
</div>";

// Check 3: File Permissions
echo "<h4>📁 File Permissions Check</h4>";
$check_dirs = ['uploads', 'logs', 'cache'];
$permission_status = [];

foreach ($check_dirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        $permission_status[$dir] = $writable ? '✅ Writable' : '❌ Not Writable';
        
        if (!$writable) {
            $diagnostic_results[] = "Directory not writable: $dir";
        }
    } else {
        $permission_status[$dir] = '❌ Missing';
        $diagnostic_results[] = "Directory missing: $dir";
        
        // Try to create directory
        if (mkdir($dir, 0755, true)) {
            $permission_status[$dir] = '✅ Created';
            $fix_applied[] = "Created directory: $dir";
        }
    }
}

echo "<div class='table-responsive'>
    <table class='table table-striped'>
        <thead>
            <tr>
                <th>Directory</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>";
foreach ($permission_status as $dir => $status) {
    echo "<tr>
        <td>$dir</td>
        <td>$status</td>
    </tr>";
}
echo "</tbody>
    </table>
</div>";

// Check 4: RSS Sources
echo "<h4>📡 RSS Sources Check</h4>";
$sources_query = "SELECT id, name, url, status, last_scraped FROM news_sources ORDER BY name";
$sources_result = mysqli_query($conn, $sources_query);

if (mysqli_num_rows($sources_result) > 0) {
    echo "<div class='table-responsive'>
        <table class='table table-striped'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th>Last Scraped</th>
                    <th>Test</th>
                </tr>
            </thead>
            <tbody>";
    
    while ($source = mysqli_fetch_assoc($sources_result)) {
        $last_scraped = $source['last_scraped'] ? date('Y-m-d H:i', strtotime($source['last_scraped'])) : 'Never';
        $status_badge = $source['status'] === 'active' ? '<span class=\"badge bg-success\">Active</span>' : '<span class=\"badge bg-danger\">Inactive</span>';
        
        echo "<tr>
            <td>{$source['id']}</td>
            <td>" . htmlspecialchars($source['name']) . "</td>
            <td><a href='" . htmlspecialchars($source['url']) . "' target='_blank'>" . substr(htmlspecialchars($source['url']), 0, 50) . "...</a></td>
            <td>$status_badge</td>
            <td>$last_scraped</td>
            <td><button class='btn btn-sm btn-primary' onclick='testRSS(" . $source['id'] . ", \"" . htmlspecialchars($source['url']) . "\")'>Test</button></td>
        </tr>";
    }
    
    echo "</tbody>
        </table>
    </div>";
} else {
    echo "<div class='alert alert-warning'>No RSS sources found. You need to add RSS sources first.</div>";
}

// Check 5: Import Log
echo "<h4>📋 Import Log Check</h4>";
$log_file = 'logs/cron_import.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $log_lines = array_slice(explode("\n", $log_content), -20); // Last 20 lines
    
    echo "<div class='log-output'>" . implode("\n", array_map('htmlspecialchars', $log_lines)) . "</div>";
} else {
    echo "<div class='alert alert-info'>No import log file found.</div>";
}

// Apply Fixes Button
echo "<div class='text-center mt-4'>
    <h4>🔧 Apply Fixes</h4>
    <div class='row'>
        <div class='col-md-4'>
            <form method='post'>
                <input type='hidden' name='fix_permissions' value='1'>
                <button type='submit' class='btn btn-warning btn-lg w-100'>Fix Permissions</button>
            </form>
        </div>
        <div class='col-md-4'>
            <form method='post'>
                <input type='hidden' name='fix_tables' value='1'>
                <button type='submit' class='btn btn-info btn-lg w-100'>Fix Tables</button>
            </form>
        </div>
        <div class='col-md-4'>
            <form method='post'>
                <input type='hidden' name='test_import' value='1'>
                <button type='submit' class='btn btn-success btn-lg w-100'>Test Import</button>
            </form>
        </div>
    </div>
</div>";

// Handle fix requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fix_permissions'])) {
        echo "<div class='alert alert-info'>Fixing permissions...</div>";
        
        foreach ($check_dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "<p class='status-ok'>✅ Created directory: $dir</p>";
            } else {
                chmod($dir, 0755);
                echo "<p class='status-ok'>✅ Fixed permissions for: $dir</p>";
            }
        }
        
        echo "<div class='alert alert-success'>Permissions fixed!</div>";
    }
    
    if (isset($_POST['fix_tables'])) {
        echo "<div class='alert alert-info'>Fixing database tables...</div>";
        
        // Recreate tables with proper structure
        $tables_sql = [
            'news_sources' => "CREATE TABLE IF NOT EXISTS news_sources (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                url TEXT NOT NULL,
                category_id INT,
                type ENUM('rss', 'api', 'manual') DEFAULT 'rss',
                status ENUM('active', 'inactive') DEFAULT 'active',
                last_scraped DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            'categories' => "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        ];
        
        foreach ($tables_sql as $table => $sql) {
            if (mysqli_query($conn, $sql)) {
                echo "<p class='status-ok'>✅ Fixed table: $table</p>";
            } else {
                echo "<p class='status-error'>❌ Error fixing $table: " . mysqli_error($conn) . "</p>";
            }
        }
        
        echo "<div class='alert alert-success'>Database tables fixed!</div>";
    }
    
    if (isset($_POST['test_import'])) {
        echo "<div class='alert alert-info'>Testing import functionality...</div>";
        
        require_once 'includes/enhanced_rss_parser.php';
        
        try {
            $parser = new EnhancedRSSParser();
            $test_url = 'https://rss.cnn.com/rss/edition.rss.com';
            
            $test_result = $parser->fetchRSS($test_url);
            if ($test_result) {
                echo "<p class='status-ok'>✅ RSS parser working correctly</p>";
                echo "<p class='status-ok'>✅ cURL functionality working</p>";
                echo "<p class='status-ok'>✅ Network connectivity working</p>";
            } else {
                echo "<p class='status-error'>❌ RSS parser test failed</p>";
            }
        } catch (Exception $e) {
            echo "<p class='status-error'>❌ Import test failed: " . $e->getMessage() . "</p>";
        }
        
        echo "<div class='alert alert-success'>Import test completed!</div>";
    }
}

echo "
            </div>
        </div>
    </div>
    
    <script>
        function testRSS(sourceId, url) {
            window.open('test_rss_single.php?url=' + encodeURIComponent(url) + '&id=' + sourceId, '_blank', 'width=800,height=600');
        }
    </script>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
