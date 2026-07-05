<?php
/**
 * Hostinger Database Setup Script
 * 
 * This script helps configure the database for Hostinger hosting.
 * Run this script after uploading files to Hostinger.
 * 
 * IMPORTANT: Delete this script after successful setup!
 */

// Prevent direct access in production
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['step'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Error reporting for setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PK Live News - Hostinger Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; }
        .container { max-width: 800px; margin: 50px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 20px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { color: #2c3e50; margin-bottom: 10px; }
        .header p { color: #7f8c8d; }
        .step { display: none; }
        .step.active { display: block; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #2c3e50; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 2px solid #e1e8ed; border-radius: 6px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #3498db; }
        .btn { background: #3498db; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .btn:hover { background: #2980b9; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .progress { height: 6px; background: #e1e8ed; border-radius: 3px; margin-bottom: 30px; overflow: hidden; }
        .progress-bar { height: 100%; background: #3498db; transition: width 0.3s ease; }
        .test-result { padding: 10px; border-radius: 4px; margin: 10px 0; }
        .test-success { background: #d4edda; color: #155724; }
        .test-error { background: #f8d7da; color: #721c24; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 12px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PK Live News - Hostinger Setup</h1>
            <p>Configure your website for Hostinger hosting</p>
        </div>

        <div class="progress">
            <div class="progress-bar" id="progressBar" style="width: 20%;"></div>
        </div>

        <?php
        $step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
        
        if ($step === 1) {
            ?>
            <div class="step active">
                <h2>Step 1: Database Configuration</h2>
                <p>Please enter your Hostinger database details. You can find these in your Hostinger cPanel under MySQL Databases.</p>
                
                <?php if (isset($_POST['test_connection'])): ?>
                    <?php
                    $db_host = $_POST['db_host'] ?? '';
                    $db_user = $_POST['db_user'] ?? '';
                    $db_pass = $_POST['db_pass'] ?? '';
                    $db_name = $_POST['db_name'] ?? '';
                    
                    $connection = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
                    
                    if ($connection) {
                        echo '<div class="alert alert-success">✓ Database connection successful!</div>';
                        mysqli_close($connection);
                    } else {
                        echo '<div class="alert alert-error">✗ Database connection failed: ' . mysqli_connect_error() . '</div>';
                    }
                    ?>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="<?php echo $_POST['db_host'] ?? 'localhost'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_user">Database User</label>
                        <input type="text" id="db_user" name="db_user" value="<?php echo $_POST['db_user'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass" value="<?php echo $_POST['db_pass'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" value="<?php echo $_POST['db_name'] ?? ''; ?>" required>
                    </div>
                    
                    <button type="submit" name="test_connection" class="btn">Test Connection</button>
                    <?php if (isset($_POST['test_connection']) && $connection): ?>
                        <button type="submit" name="next_step" class="btn btn-success" style="margin-left: 10px;">Next Step →</button>
                    <?php endif; ?>
                </form>
            </div>
            <?php
        } elseif ($step === 2) {
            ?>
            <div class="step active">
                <h2>Step 2: Site Configuration</h2>
                <p>Configure your website settings for production.</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="site_url">Site URL</label>
                        <input type="url" id="site_url" name="site_url" value="<?php echo $_POST['site_url'] ?? ('https://' . $_SERVER['HTTP_HOST'] . '/'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" value="<?php echo $_POST['site_name'] ?? 'PK Live News'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" id="admin_email" name="admin_email" value="<?php echo $_POST['admin_email'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="support_email">Support Email</label>
                        <input type="email" id="support_email" name="support_email" value="<?php echo $_POST['support_email'] ?? ''; ?>" required>
                    </div>
                    
                    <button type="submit" name="create_env" class="btn">Create .env File</button>
                </form>
                
                <?php if (isset($_POST['create_env'])): ?>
                    <?php
                    $env_content = "# Database Configuration\n";
                    $env_content .= "DB_HOST=" . ($_POST['db_host'] ?? '') . "\n";
                    $env_content .= "DB_USER=" . ($_POST['db_user'] ?? '') . "\n";
                    $env_content .= "DB_PASS=" . ($_POST['db_pass'] ?? '') . "\n";
                    $env_content .= "DB_NAME=" . ($_POST['db_name'] ?? '') . "\n\n";
                    
                    $env_content .= "# Site Configuration\n";
                    $env_content .= "SITE_URL=" . ($_POST['site_url'] ?? '') . "\n";
                    $env_content .= "SITE_NAME=" . ($_POST['site_name'] ?? '') . "\n";
                    $env_content .= "APP_ENV=production\n\n";
                    
                    $env_content .= "# Email Configuration\n";
                    $env_content .= "SMTP_HOST=smtp.hostinger.com\n";
                    $env_content .= "SMTP_USER=" . ($_POST['admin_email'] ?? '') . "\n";
                    $env_content .= "SMTP_PASS=your_email_password\n";
                    $env_content .= "SMTP_PORT=587\n";
                    $env_content .= "SMTP_SECURE=tls\n\n";
                    
                    $env_content .= "# File Upload Configuration\n";
                    $env_content .= "UPLOAD_PATH=uploads/\n";
                    $env_content .= "MAX_FILE_SIZE=5242880\n\n";
                    
                    $env_content .= "# Security\n";
                    $env_content .= "ADMIN_EMAIL=" . ($_POST['admin_email'] ?? '') . "\n";
                    $env_content .= "SUPPORT_EMAIL=" . ($_POST['support_email'] ?? '') . "\n";
                    
                    if (file_put_contents('.env', $env_content)) {
                        echo '<div class="alert alert-success">✓ .env file created successfully!</div>';
                        echo '<div class="alert alert-warning">⚠ Remember to update your email password in the .env file!</div>';
                        echo '<a href="?step=3" class="btn btn-success">Next Step →</a>';
                    } else {
                        echo '<div class="alert alert-error">✗ Failed to create .env file. Please check file permissions.</div>';
                    }
                    ?>
                <?php endif; ?>
            </div>
            <?php
        } elseif ($step === 3) {
            ?>
            <div class="step active">
                <h2>Step 3: Database Import</h2>
                <p>Import your database backup file.</p>
                
                <?php
                if (isset($_FILES['sql_file']) && $_FILES['sql_file']['error'] === UPLOAD_ERR_OK) {
                    $sql_file = $_FILES['sql_file']['tmp_name'];
                    $sql_content = file_get_contents($sql_file);
                    
                    if ($sql_content) {
                        // Read .env file for database credentials
                        $env_file = '.env';
                        if (file_exists($env_file)) {
                            $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            $db_config = [];
                            
                            foreach ($env_lines as $line) {
                                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                                    list($key, $value) = explode('=', $line, 2);
                                    $db_config[trim($key)] = trim($value);
                                }
                            }
                            
                            $conn = @mysqli_connect($db_config['DB_HOST'], $db_config['DB_USER'], $db_config['DB_PASS'], $db_config['DB_NAME']);
                            
                            if ($conn) {
                                // Split SQL file into individual queries
                                $queries = explode(';', $sql_content);
                                $success = 0;
                                $failed = 0;
                                
                                foreach ($queries as $query) {
                                    $query = trim($query);
                                    if (!empty($query) && !preg_match('/^--/', $query)) {
                                        if (mysqli_query($conn, $query)) {
                                            $success++;
                                        } else {
                                            $failed++;
                                        }
                                    }
                                }
                                
                                echo '<div class="alert alert-success">✓ Database import completed!</div>';
                                echo '<p>Successful queries: ' . $success . '</p>';
                                if ($failed > 0) {
                                    echo '<p>Failed queries: ' . $failed . '</p>';
                                }
                                
                                mysqli_close($conn);
                            } else {
                                echo '<div class="alert alert-error">✗ Database connection failed: ' . mysqli_connect_error() . '</div>';
                            }
                        } else {
                            echo '<div class="alert alert-error">✗ .env file not found. Please complete step 2 first.</div>';
                        }
                    } else {
                        echo '<div class="alert alert-error">✗ Failed to read SQL file.</div>';
                    }
                }
                ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="sql_file">SQL Backup File</label>
                        <input type="file" id="sql_file" name="sql_file" accept=".sql" required>
                    </div>
                    
                    <button type="submit" class="btn">Import Database</button>
                    <?php if (isset($_FILES['sql_file']) && $_FILES['sql_file']['error'] === UPLOAD_ERR_OK): ?>
                        <a href="?step=4" class="btn btn-success" style="margin-left: 10px;">Next Step →</a>
                    <?php endif; ?>
                </form>
            </div>
            <?php
        } elseif ($step === 4) {
            ?>
            <div class="step active">
                <h2>Step 4: File Permissions</h2>
                <p>Set correct file permissions for security and functionality.</p>
                
                <?php
                $directories = ['uploads', 'uploads/ads', 'uploads/avatars', 'uploads/categories', 'uploads/channels', 'cache', 'logs'];
                $permissions_set = true;
                
                foreach ($directories as $dir) {
                    if (is_dir($dir)) {
                        if (!chmod($dir, 0755)) {
                            $permissions_set = false;
                        }
                    }
                }
                
                if ($permissions_set) {
                    echo '<div class="alert alert-success">✓ Directory permissions set successfully!</div>';
                } else {
                    echo '<div class="alert alert-warning">⚠ Some directory permissions could not be set. Please set them manually via FTP/File Manager.</div>';
                }
                ?>
                
                <div class="alert alert-info">
                    <strong>Manual Permission Settings:</strong><br>
                    • All folders: 755<br>
                    • All PHP files: 644<br>
                    • Upload directories: 755
                </div>
                
                <a href="?step=5" class="btn btn-success">Next Step →</a>
            </div>
            <?php
        } elseif ($step === 5) {
            ?>
            <div class="step active">
                <h2>Step 5: Final Configuration</h2>
                <p>Complete the setup process.</p>
                
                <?php
                $checks = [];
                
                // Check .env file
                $checks['env'] = file_exists('.env');
                
                // Check database connection
                if (file_exists('.env')) {
                    $env_lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $db_config = [];
                    
                    foreach ($env_lines as $line) {
                        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                            list($key, $value) = explode('=', $line, 2);
                            $db_config[trim($key)] = trim($value);
                        }
                    }
                    
                    $conn = @mysqli_connect($db_config['DB_HOST'], $db_config['DB_USER'], $db_config['DB_PASS'], $db_config['DB_NAME']);
                    $checks['database'] = $conn ? true : false;
                    if ($conn) mysqli_close($conn);
                } else {
                    $checks['database'] = false;
                }
                
                // Check upload directories
                $checks['uploads'] = is_dir('uploads') && is_writable('uploads');
                
                // Check .htaccess
                $checks['htaccess'] = file_exists('.htaccess');
                
                $all_checks_pass = true;
                foreach ($checks as $check) {
                    if (!$check) $all_checks_pass = false;
                }
                
                if ($all_checks_pass) {
                    echo '<div class="alert alert-success">✓ All checks passed! Your website is ready.</div>';
                } else {
                    echo '<div class="alert alert-warning">⚠ Some checks failed. Please review the issues below.</div>';
                }
                ?>
                
                <h3>System Checks:</h3>
                <div class="test-result <?php echo $checks['env'] ? 'test-success' : 'test-error'; ?>">
                    <?php echo $checks['env'] ? '✓' : '✗'; ?> .env file exists
                </div>
                <div class="test-result <?php echo $checks['database'] ? 'test-success' : 'test-error'; ?>">
                    <?php echo $checks['database'] ? '✓' : '✗'; ?> Database connection
                </div>
                <div class="test-result <?php echo $checks['uploads'] ? 'test-success' : 'test-error'; ?>">
                    <?php echo $checks['uploads'] ? '✓' : '✗'; ?> Upload directories writable
                </div>
                <div class="test-result <?php echo $checks['htaccess'] ? 'test-success' : 'test-error'; ?>">
                    <?php echo $checks['htaccess'] ? '✓' : '✗'; ?> .htaccess file exists
                </div>
                
                <?php if ($all_checks_pass): ?>
                    <div class="alert alert-warning">
                        <strong>IMPORTANT:</strong> Delete this setup script (hostinger_setup.php) for security reasons.
                    </div>
                    
                    <h3>Next Steps:</h3>
                    <ol>
                        <li>Delete this setup script</li>
                        <li>Test your website at <a href="<?php echo $db_config['SITE_URL'] ?? '/'; ?>" target="_blank"><?php echo $db_config['SITE_URL'] ?? '/'; ?></a></li>
                        <li>Login to admin panel</li>
                        <li>Configure your email settings in .env</li>
                        <li>Set up cron jobs if needed</li>
                    </ol>
                    
                    <a href="<?php echo $db_config['SITE_URL'] ?? '/'; ?>" class="btn btn-success" target="_blank">Visit Your Website</a>
                <?php endif; ?>
            </div>
            <?php
        }
        ?>
    </div>

    <script>
        // Update progress bar
        const step = <?php echo $step; ?>;
        const progressBar = document.getElementById('progressBar');
        progressBar.style.width = (step * 20) + '%';
        
        // Handle form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Processing...';
                }
            });
        });
    </script>
</body>
</html>
