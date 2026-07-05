<?php
// PK Live News - Hosting Manager
// This file manages hosting configuration and provides tools for deployment

class HostingManager {
    private $config;
    private $errors = [];
    private $success = [];
    
    public function __construct() {
        $this->loadConfig();
    }
    
    private function loadConfig() {
        try {
            require_once 'config/env.php';
            require_once 'config/database.php';
            
            $this->config = [
                'db_host' => DB_HOST,
                'db_user' => DB_USER,
                'db_name' => DB_NAME,
                'site_url' => SITE_URL,
                'app_env' => APP_ENV,
                'upload_path' => UPLOAD_PATH,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ];
        } catch (Exception $e) {
            $this->errors[] = "Failed to load configuration: " . $e->getMessage();
        }
    }
    
    public function checkDatabaseConnection() {
        global $conn;
        
        if (!$conn) {
            $this->errors[] = "Database connection not established";
            return false;
        }
        
        if (!$conn->ping()) {
            $this->errors[] = "Database connection failed: Unable to ping server";
            return false;
        }
        
        $this->success[] = "Database connection successful";
        return true;
    }
    
    public function checkFilePermissions() {
        $required_dirs = ['uploads', 'logs', 'cache', 'backups'];
        $issues = [];
        
        foreach ($required_dirs as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    $issues[] = "Cannot create directory: $dir";
                    continue;
                }
                $this->success[] = "Created directory: $dir";
            }
            
            if (!is_writable($dir)) {
                $issues[] = "Directory not writable: $dir";
            } else {
                $this->success[] = "Directory writable: $dir";
            }
        }
        
        if (!empty($issues)) {
            $this->errors = array_merge($this->errors, $issues);
            return false;
        }
        
        return true;
    }
    
    public function checkHtaccess() {
        if (!file_exists('.htaccess')) {
            $this->errors[] = ".htaccess file not found";
            return false;
        }
        
        $this->success[] = ".htaccess file found";
        return true;
    }
    
    public function checkEnvironment() {
        if (!file_exists('.env')) {
            $this->errors[] = ".env file not found";
            return false;
        }
        
        $this->success[] = ".env file found";
        return true;
    }
    
    public function generateBackup() {
        $backup_dir = 'backups';
        $timestamp = date('Y-m-d_H-i-s');
        $backup_file = $backup_dir . '/pk_live_news_backup_' . $timestamp . '.sql';
        
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // Create database backup
        global $conn;
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        $sql = "-- PK Live News Database Backup\n";
        $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . DB_NAME . "\n\n";
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW CREATE TABLE `$table`");
            $row = $result->fetch_row();
            $sql .= $row[1] . ";\n\n";
            
            $result = $conn->query("SELECT * FROM `$table`");
            while ($row = $result->fetch_assoc()) {
                $values = array_map(function($value) use ($conn) {
                    return $value === null ? 'NULL' : "'" . $conn->real_escape_string($value) . "'";
                }, $row);
                $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }
        
        if (file_put_contents($backup_file, $sql)) {
            $this->success[] = "Backup created: $backup_file";
            return $backup_file;
        } else {
            $this->errors[] = "Failed to create backup";
            return false;
        }
    }
    
    public function optimizeDatabase() {
        global $conn;
        
        $result = $conn->query("SHOW TABLES");
        $optimized = 0;
        
        while ($row = $result->fetch_row()) {
            $table = $row[0];
            if ($conn->query("OPTIMIZE TABLE `$table`")) {
                $optimized++;
            }
        }
        
        $this->success[] = "Optimized $optimized database tables";
        return $optimized;
    }
    
    public function clearCache() {
        $cache_dir = 'cache';
        $cleared = 0;
        
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (unlink($file)) {
                        $cleared++;
                    }
                }
            }
        }
        
        $this->success[] = "Cleared $cleared cache files";
        return $cleared;
    }
    
    public function getSystemInfo() {
        return [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
            'https' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'Yes' : 'No',
            'memory_usage' => memory_get_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_input_vars' => ini_get('max_input_vars'),
            'display_errors' => ini_get('display_errors') ? 'On' : 'Off'
        ];
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getSuccess() {
        return $this->success;
    }
    
    public function getConfig() {
        return $this->config;
    }
}

// Handle form submissions
$action = $_GET['action'] ?? 'dashboard';
$manager = new HostingManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'backup':
            $manager->generateBackup();
            break;
        case 'optimize':
            $manager->optimizeDatabase();
            break;
        case 'clearcache':
            $manager->clearCache();
            break;
        case 'check':
            $manager->checkDatabaseConnection();
            $manager->checkFilePermissions();
            $manager->checkHtaccess();
            $manager->checkEnvironment();
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PK Live News - Hosting Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        .header h1 { font-size: 2rem; margin-bottom: 10px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .nav { background: white; border-radius: 8px; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .nav a { display: inline-block; padding: 10px 20px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; transition: background 0.3s; }
        .nav a:hover { background: #0056b3; }
        .nav a.active { background: #28a745; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card h2 { color: #333; margin-bottom: 15px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; transition: background 0.3s; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.8; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .stat { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat h3 { font-size: 2rem; margin-bottom: 5px; }
        .stat p { opacity: 0.9; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .table tr:hover { background: #f8f9fa; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .progress { width: 100%; height: 20px; background: #e9ecef; border-radius: 10px; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #28a745, #20c997); transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PK Live News - Hosting Manager</h1>
        <p>Manage your website hosting configuration and performance</p>
    </div>
    
    <div class="container">
        <div class="nav">
            <a href="?action=dashboard" class="<?php echo $action == 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
            <a href="?action=system" class="<?php echo $action == 'system' ? 'active' : ''; ?>">System Info</a>
            <a href="?action=config" class="<?php echo $action == 'config' ? 'active' : ''; ?>">Configuration</a>
            <a href="?action=maintenance" class="<?php echo $action == 'maintenance' ? 'active' : ''; ?>">Maintenance</a>
            <a href="?action=tools" class="<?php echo $action == 'tools' ? 'active' : ''; ?>">Tools</a>
        </div>
        
        <?php
        $errors = $manager->getErrors();
        $success = $manager->getSuccess();
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<div class='alert alert-error'>$error</div>";
            }
        }
        
        if (!empty($success)) {
            foreach ($success as $msg) {
                echo "<div class='alert alert-success'>$msg</div>";
            }
        }
        ?>
        
        <?php if ($action == 'dashboard'): ?>
            <div class="card">
                <h2>Dashboard Overview</h2>
                <div class="grid">
                    <div class="stat">
                        <h3><?php echo $manager->checkDatabaseConnection() ? 'Connected' : 'Error'; ?></h3>
                        <p>Database Status</p>
                    </div>
                    <div class="stat">
                        <h3><?php echo $manager->checkFilePermissions() ? 'OK' : 'Error'; ?></h3>
                        <p>File Permissions</p>
                    </div>
                    <div class="stat">
                        <h3><?php echo $manager->checkEnvironment() ? 'OK' : 'Error'; ?></h3>
                        <p>Environment</p>
                    </div>
                    <div class="stat">
                        <h3><?php echo $manager->checkHtaccess() ? 'OK' : 'Error'; ?></h3>
                        <p>.htaccess</p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h2>Quick Actions</h2>
                <form method="post" action="?action=check">
                    <button type="submit" class="btn btn-primary">Run System Check</button>
                </form>
                <br>
                <form method="post" action="?action=backup">
                    <button type="submit" class="btn btn-success">Create Backup</button>
                </form>
            </div>
        
        <?php elseif ($action == 'system'): ?>
            <div class="card">
                <h2>System Information</h2>
                <?php
                $info = $manager->getSystemInfo();
                echo "<table class='table'>";
                foreach ($info as $key => $value) {
                    echo "<tr><th>" . ucwords(str_replace('_', ' ', $key)) . "</th><td>$value</td></tr>";
                }
                echo "</table>";
                ?>
            </div>
        
        <?php elseif ($action == 'config'): ?>
            <div class="card">
                <h2>Configuration Status</h2>
                <?php
                $config = $manager->getConfig();
                if ($config) {
                    echo "<table class='table'>";
                    foreach ($config as $key => $value) {
                        echo "<tr><th>" . ucwords(str_replace('_', ' ', $key)) . "</th><td>$value</td></tr>";
                    }
                    echo "</table>";
                }
                ?>
            </div>
        
        <?php elseif ($action == 'maintenance'): ?>
            <div class="card">
                <h2>Maintenance Tools</h2>
                <form method="post" action="?action=optimize">
                    <button type="submit" class="btn btn-warning">Optimize Database</button>
                </form>
                <br>
                <form method="post" action="?action=clearcache">
                    <button type="submit" class="btn btn-warning">Clear Cache</button>
                </form>
                <br>
                <form method="post" action="?action=backup">
                    <button type="submit" class="btn btn-success">Create Backup</button>
                </form>
            </div>
        
        <?php elseif ($action == 'tools'): ?>
            <div class="card">
                <h2>Hosting Tools</h2>
                <p><a href="hosting_test.php" target="_blank" class="btn btn-primary">Run Hosting Test</a></p>
                <br>
                <p><a href="access_fix.php" class="btn btn-warning">Fix File Permissions</a></p>
                <br>
                <p><a href="connection_test.php" class="btn btn-primary">Test Database Connection</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
