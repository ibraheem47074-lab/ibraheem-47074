<?php
echo "<h1>PK Live News - Complete Issue Fixer</h1>";

// Fix 1: Check and create .env file
echo "<h2>1. Environment Configuration</h2>";
if (!file_exists('.env')) {
    echo "<div style='color: orange;'>⚠ .env file missing - Creating default configuration...</div>";
    $env_content = "# Environment Configuration\n";
    $env_content .= "DB_HOST=localhost\n";
    $env_content .= "DB_USER=root\n";
    $env_content .= "DB_PASS=\n";
    $env_content .= "DB_NAME=pk_live_news\n";
    $env_content .= "SITE_URL=http://localhost/pk-live-news/\n";
    $env_content .= "SITE_NAME=PK Live News\n";
    $env_content .= "APP_ENV=development\n";
    file_put_contents('.env', $env_content);
    echo "<div style='color: green;'>✓ .env file created successfully</div>";
} else {
    echo "<div style='color: green;'>✓ .env file exists</div>";
}

// Fix 2: Database connection and setup
echo "<h2>2. Database Setup</h2>";
try {
    // Connect without database first
    $conn = new mysqli('localhost', 'root', '');
    if ($conn->connect_error) {
        echo "<div style='color: red;'>✗ MySQL Connection Failed: " . $conn->connect_error . "</div>";
        echo "<div style='color: orange;'>⚠ Please ensure MySQL/XAMPP is running</div>";
    } else {
        echo "<div style='color: green;'>✓ MySQL connection successful</div>";
        
        // Create database if not exists
        $conn->query("CREATE DATABASE IF NOT EXISTS pk_live_news CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->select_db('pk_live_news');
        echo "<div style='color: green;'>✓ Database 'pk_live_news' ready</div>";
        
        // Check essential tables
        $tables = ['users', 'news', 'categories', 'settings'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows == 0) {
                echo "<div style='color: orange;'>⚠ Table '$table' missing - Creating...</div>";
                create_table($conn, $table);
            } else {
                echo "<div style='color: green;'>✓ Table '$table' exists</div>";
            }
        }
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Database Error: " . $e->getMessage() . "</div>";
}

// Fix 3: Check file permissions
echo "<h2>3. File Permissions</h2>";
$directories = ['uploads', 'cache', 'logs', 'backups'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "<div style='color: orange;'>⚠ Created directory: $dir</div>";
    } else {
        echo "<div style='color: green;'>✓ Directory exists: $dir</div>";
    }
}

// Fix 4: .htaccess configuration
echo "<h2>4. .htaccess Configuration</h2>";
if (!file_exists('.htaccess') || filesize('.htaccess') == 0) {
    $htaccess_content = "
# Enable URL rewriting
RewriteEngine On

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection '1; mode=block'
</IfModule>

# PHP settings
<IfModule mod_php.c>
    php_flag display_errors On
    php_value error_reporting E_ALL
    php_value memory_limit 512M
    php_value max_execution_time 300
    php_value upload_max_filesize 40M
    php_value post_max_size 40M
</IfModule>

# URL routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css 'access plus 1 month'
    ExpiresByType application/javascript 'access plus 1 month'
    ExpiresByType image/png 'access plus 1 month'
    ExpiresByType image/jpg 'access plus 1 month'
    ExpiresByType image/jpeg 'access plus 1 month'
    ExpiresByType image/gif 'access plus 1 month'
    ExpiresByType image/ico 'access plus 1 month'
    ExpiresByType image/icon 'access plus 1 month'
</IfModule>
";
    file_put_contents('.htaccess', $htaccess_content);
    echo "<div style='color: green;'>✓ .htaccess file created/updated</div>";
} else {
    echo "<div style='color: green;'>✓ .htaccess file exists</div>";
}

// Fix 5: Check essential files
echo "<h2>5. Essential Files Check</h2>";
$essential_files = [
    'config/database.php',
    'includes/header.php',
    'includes/footer.php',
    'includes/language_functions.php',
    'index.php'
];

foreach ($essential_files as $file) {
    if (file_exists($file)) {
        echo "<div style='color: green;'>✓ $file exists</div>";
    } else {
        echo "<div style='color: red;'>✗ $file missing</div>";
    }
}

echo "<h2>🎉 Fix Complete!</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 4px;'>";
echo "<strong>Next Steps:</strong><br>";
echo "1. Start XAMPP (Apache + MySQL)<br>";
echo "2. Visit: <a href='http://localhost/pk-live-news/'>http://localhost/pk-live-news/</a><br>";
echo "3. Run setup: <a href='simple_setup.php'>simple_setup.php</a><br>";
echo "4. Check status: <a href='diagnostic_simple.php'>diagnostic_simple.php</a><br>";
echo "</div>";

function create_table($conn, $table_name) {
    switch ($table_name) {
        case 'users':
            $sql = "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'editor', 'reporter', 'user') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            break;
        case 'news':
            $sql = "CREATE TABLE news (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE,
                content TEXT,
                excerpt TEXT,
                author_id INT,
                category_id INT,
                image VARCHAR(255),
                status ENUM('published', 'draft', 'archived') DEFAULT 'draft',
                featured BOOLEAN DEFAULT FALSE,
                view_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (author_id) REFERENCES users(id),
                FOREIGN KEY (category_id) REFERENCES categories(id)
            )";
            break;
        case 'categories':
            $sql = "CREATE TABLE categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) UNIQUE,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            break;
        case 'settings':
            $sql = "CREATE TABLE settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            break;
    }
    
    if ($conn->query($sql)) {
        echo "<div style='color: green;'>✓ Table '$table_name' created successfully</div>";
    } else {
        echo "<div style='color: red;'>✗ Failed to create table '$table_name': " . $conn->error . "</div>";
    }
}
?>
