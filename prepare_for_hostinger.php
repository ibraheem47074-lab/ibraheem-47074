<?php
echo "<h1>PK Live News - Hostinger Deployment Prep</h1>";

// 1. Export database
echo "<h2>Step 1: Database Export</h2>";
echo "<p>Go to XAMPP phpMyAdmin and export the 'pk_live_news' database</p>";
echo "<p><a href='http://localhost/phpmyadmin/' target='_blank'>Open phpMyAdmin</a></p>";

// 2. Create deployment package
echo "<h2>Step 2: Create Deployment Package</h2>";

$exclude_dirs = ['backups', 'logs', '.git'];
$exclude_files = ['.gitignore', 'prepare_for_hostinger.php'];

$zip = new ZipArchive();
$zip_filename = 'pk_live_news_hostinger.zip';

if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    
    // Add all files and directories
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator('.'),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        $file_path = $file->getPathname();
        
        // Skip current directory and excluded items
        if ($file_path == '.' || in_array(basename($file_path), array_merge($exclude_dirs, $exclude_files))) {
            continue;
        }
        
        // Skip directories in exclude list
        foreach ($exclude_dirs as $exclude_dir) {
            if (strpos($file_path, $exclude_dir . '/') !== false) {
                continue 2;
            }
        }
        
        if (is_dir($file_path)) {
            $zip->addEmptyDir($file_path);
        } else {
            $zip->addFile($file_path, $file_path);
        }
    }
    
    $zip->close();
    
    echo "<p style='color: green;'>✓ Deployment package created: <strong>$zip_filename</strong></p>";
    echo "<p>Size: " . number_format(filesize($zip_filename) / 1024 / 1024, 2) . " MB</p>";
    echo "<p><a href='$zip_filename' download>Download Package</a></p>";
    
} else {
    echo "<p style='color: red;'>✗ Failed to create zip file</p>";
}

// 3. Create .env template
echo "<h2>Step 3: Environment Configuration</h2>";

$env_template = "# Database Configuration
DB_HOST=localhost
DB_USER=your_hostinger_db_user
DB_PASS=your_hostinger_db_password
DB_NAME=your_hostinger_db_name

# Site Configuration  
SITE_URL=https://your-domain.com/
SITE_NAME=PK Live News
APP_ENV=production

# Email Configuration
ADMIN_EMAIL=admin@your-domain.com
SUPPORT_EMAIL=support@your-domain.com

# File Upload Configuration
UPLOAD_PATH=uploads/
MAX_FILE_SIZE=5242880";

file_put_contents('env_template.txt', $env_template);
echo "<p>✓ Environment template created: <strong>env_template.txt</strong></p>";
echo "<p><a href='env_template.txt' download>Download Template</a></p>";

// 4. Create production .htaccess
echo "<h2>Step 4: Production .htaccess</h2>";

$production_htaccess = "# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Enable URL rewriting
RewriteEngine On

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection '1; mode=block'
</IfModule>

# PHP settings (production)
<IfModule mod_php.c>
    php_flag display_errors Off
    php_value error_reporting 0
    php_value memory_limit 256M
    php_value max_execution_time 120
    php_value upload_max_filesize 20M
    php_value post_max_size 20M
</IfModule>

# URL routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Protect sensitive files
<Files ~ \"^\\.\">
    Order allow,deny
    Deny from all
</Files>

<Files ~ \"^(config|database|env)\\.php$\">
    Order allow,deny
    Deny from all
</Files>";

file_put_contents('htaccess_production.txt', $production_htaccess);
echo "<p>✓ Production .htaccess created: <strong>htaccess_production.txt</strong></p>";
echo "<p><a href='htaccess_production.txt' download>Download .htaccess</a></p>";

// 5. Next steps
echo "<h2>Next Steps for Hostinger</h2>";
echo "<ol>";
echo "<li><strong>Download</strong> the zip file above</li>";
echo "<li><strong>Sign up</strong> for Hostinger hosting</li>";
echo "<li><strong>Upload</strong> files to public_html via File Manager</li>";
echo "<li><strong>Create database</strong> in Hostinger cPanel</li>";
echo "<li><strong>Import</strong> your database from phpMyAdmin</li>";
echo "<li><strong>Configure</strong> .env file with database details</li>";
echo "<li><strong>Test</strong> your website</li>";
echo "</ol>";

echo "<h2>Hostinger Links</h2>";
echo "<p><a href='https://www.hostinger.com/' target='_blank'>Hostinger Website</a></p>";
echo "<p><a href='https://www.hostinger.com/tutorials/how-to-upload-website-to-hostinger' target='_blank'>Upload Tutorial</a></p>";

echo "<h2>Admin Login</h2>";
echo "<p><strong>Default:</strong> admin / admin123</p>";
echo "<p><strong>URL:</strong> https://your-domain.com/admin/login.php</p>";
?>
