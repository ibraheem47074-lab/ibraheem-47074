<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle backup creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    $backup_name = 'pk_live_news_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = '../backups/' . $backup_name;
    
    // Create backups directory if it doesn't exist
    if (!is_dir('../backups/')) {
        mkdir('../backups/', 0755, true);
    }
    
    // Create database backup
    $backup_success = create_database_backup($conn, $backup_path);
    
    if ($backup_success) {
        $success = "Database backup created successfully: $backup_name";
    } else {
        $error = "Failed to create database backup!";
    }
}

// Handle backup restoration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_backup'])) {
    $backup_file = clean_input($_POST['backup_file']);
    $backup_path = '../backups/' . $backup_file;
    
    if (file_exists($backup_path)) {
        $restore_success = restore_database_backup($conn, $backup_path);
        
        if ($restore_success) {
            $success = "Database restored successfully from: $backup_file";
        } else {
            $error = "Failed to restore database from backup!";
        }
    } else {
        $error = "Backup file not found: $backup_file";
    }
}

// Handle backup deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_backup'])) {
    $backup_file = clean_input($_POST['backup_file']);
    $backup_path = '../backups/' . $backup_file;
    
    if (file_exists($backup_path) && unlink($backup_path)) {
        $success = "Backup deleted successfully: $backup_file";
    } else {
        $error = "Failed to delete backup file: $backup_file";
    }
}

// Get existing backups
$backups = [];
if (is_dir('../backups/')) {
    $files = scandir('../backups/');
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $file_path = '../backups/' . $file;
            $backups[] = [
                'name' => $file,
                'size' => filesize($file_path),
                'created' => filemtime($file_path),
                'formatted_size' => format_bytes(filesize($file_path))
            ];
        }
    }
    
    // Sort backups by creation date (newest first)
    usort($backups, function($a, $b) {
        return $b['created'] - $a['created'];
    });
}

// Helper functions are already included in config/database.php

function create_database_backup($conn, $backup_path) {
    try {
        // Get all tables
        $tables = [];
        $result = mysqli_query($conn, "SHOW TABLES");
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }
        
        $backup_content = "-- PK Live News Database Backup\n";
        $backup_content .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $backup_content .= "-- MySQL Version: " . mysqli_get_server_info($conn) . "\n\n";
        
        // Add table structures and data
        foreach ($tables as $table) {
            $backup_content .= "-- Table structure for `$table`\n";
            
            // Get table structure
            $result = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
            if ($result && $row = mysqli_fetch_assoc($result)) {
                // Check all possible key variations that MySQL might return
                $create_table_sql = '';
                $available_keys = array_keys($row);
                
                // Look for the Create Table key in available keys
                foreach ($available_keys as $key) {
                    if (stripos($key, 'Create Table') !== false) {
                        $create_table_sql = $row[$key];
                        break;
                    }
                }
                
                if (empty($create_table_sql)) {
                    // Debug: print the actual keys to see what's available
                    $backup_content .= "-- Debug: Available keys: " . implode(', ', $available_keys) . "\n";
                    $backup_content .= "-- Error: Could not find Create Table key for `$table`\n\n";
                    continue;
                }
                $backup_content .= $create_table_sql . ";\n\n";
            } else {
                $backup_content .= "-- Error: Could not get table structure for `$table`\n\n";
            }
            
            // Get table data
            $result = mysqli_query($conn, "SELECT * FROM `$table`");
            $num_fields = mysqli_num_fields($result);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $backup_content .= "INSERT INTO `$table` VALUES (";
                
                $values = [];
                for ($i = 0; $i < $num_fields; $i++) {
                    $value = $row[mysqli_fetch_field_direct($result, $i)->name];
                    
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
                    }
                }
                
                $backup_content .= implode(', ', $values) . ";\n";
            }
            
            $backup_content .= "\n\n";
        }
        
        // Write backup to file
        return file_put_contents($backup_path, $backup_content) !== false;
        
    } catch (Exception $e) {
        error_log("Backup creation failed: " . $e->getMessage());
        return false;
    }
}

function restore_database_backup($conn, $backup_path) {
    try {
        // Read backup file
        $backup_content = file_get_contents($backup_path);
        
        if ($backup_content === false) {
            return false;
        }
        
        // Split into individual queries
        $queries = explode(";\n", $backup_content);
        
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        foreach ($queries as $query) {
            $query = trim($query);
            
            // Skip empty lines and comments
            if (empty($query) || strpos($query, '--') === 0) {
                continue;
            }
            
            // Execute query
            if (!mysqli_query($conn, $query)) {
                mysqli_rollback($conn);
                return false;
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        return true;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Backup restoration failed: " . $e->getMessage());
        return false;
    }
}

function format_bytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup & Restore - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .backup-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        .backup-item {
            border-left: 4px solid #007bff;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 0 8px 8px 0;
        }
        .backup-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-backup {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        .warning-box {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-database me-3"></i>Database Backup & Restore</h2>
                <p class="text-muted">Create, manage, and restore database backups for your PK Live News website.</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Warning Box -->
        <div class="warning-box">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Important Notice</h5>
            <ul class="mb-0">
                <li>Always create a backup before making major changes to your website</li>
                <li>Restoring a backup will overwrite all current data</li>
                <li>Store backups in a secure location outside your web server</li>
                <li>Test backups regularly to ensure they can be restored successfully</li>
            </ul>
        </div>

        <div class="row">
            <!-- Create Backup -->
            <div class="col-lg-6">
                <div class="backup-card">
                    <h5><i class="fas fa-plus-circle me-2"></i>Create New Backup</h5>
                    <p class="text-muted">Generate a complete backup of your database including all tables and data.</p>
                    
                    <form method="POST" class="mt-3">
                        <div class="mb-3">
                            <label class="form-label">Backup Options:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_data" checked>
                                <label class="form-check-label" for="include_data">
                                    Include table data
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_structure" checked>
                                <label class="form-check-label" for="include_structure">
                                    Include table structure
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="compress_backup">
                                <label class="form-check-label" for="compress_backup">
                                    Compress backup (if available)
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" name="create_backup" class="btn btn-primary btn-backup w-100" onclick="return confirm('Create a new database backup? This may take a few moments.')">
                            <i class="fas fa-download me-2"></i>Create Backup Now
                        </button>
                    </form>
                </div>

                <!-- Database Statistics -->
                <div class="backup-card">
                    <h5><i class="fas fa-chart-bar me-2"></i>Database Statistics</h5>
                    <?php
                    $db_stats = [
                        'total_tables' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()"))['count'],
                        'total_size' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = DATABASE()"))['size'],
                        'news_count' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news"))['count'],
                        'users_count' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'],
                        'comments_count' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments"))['count']
                    ];
                    ?>
                    <table class="table table-sm">
                        <tr>
                            <td>Total Tables:</td>
                            <td><strong><?php echo $db_stats['total_tables']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Database Size:</td>
                            <td><strong><?php echo format_bytes($db_stats['total_size']); ?></strong></td>
                        </tr>
                        <tr>
                            <td>News Articles:</td>
                            <td><strong><?php echo $db_stats['news_count']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Users:</td>
                            <td><strong><?php echo $db_stats['users_count']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Comments:</td>
                            <td><strong><?php echo $db_stats['comments_count']; ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Existing Backups -->
            <div class="col-lg-6">
                <div class="backup-card">
                    <h5><i class="fas fa-list me-2"></i>Existing Backups</h5>
                    <p class="text-muted">Manage your existing database backups.</p>
                    
                    <?php if (!empty($backups)): ?>
                        <div class="mt-3">
                            <?php foreach ($backups as $backup): ?>
                                <div class="backup-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($backup['name']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y H:i', $backup['created']); ?>
                                                <i class="fas fa-file ms-3 me-1"></i><?php echo $backup['formatted_size']; ?>
                                            </small>
                                        </div>
                                        <div class="backup-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                                <button type="submit" name="restore_backup" 
                                                        class="btn btn-sm btn-success" 
                                                        onclick="return confirm('Are you sure you want to restore this backup? This will overwrite all current data!')">
                                                    <i class="fas fa-undo"></i> Restore
                                                </button>
                                                <button type="submit" name="delete_backup" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Delete this backup permanently?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                            <a href="../backups/<?php echo htmlspecialchars($backup['name']); ?>" 
                                               class="btn btn-sm btn-primary" download>
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>No backups found. Create your first backup above.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Import Backup -->
                <div class="backup-card">
                    <h5><i class="fas fa-upload me-2"></i>Import Backup</h5>
                    <p class="text-muted">Upload a backup file from your computer.</p>
                    
                    <form method="POST" enctype="multipart/form-data" class="mt-3">
                        <div class="mb-3">
                            <label for="backup_file" class="form-label">Select Backup File (.sql):</label>
                            <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql" required>
                            <small class="form-text text-muted">Maximum file size: 50MB</small>
                        </div>
                        
                        <button type="submit" name="import_backup" class="btn btn-warning btn-backup w-100" onclick="return confirm('Import and restore this backup? This will overwrite all current data!')">
                            <i class="fas fa-upload me-2"></i>Import & Restore Backup
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Schedule Backups -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="backup-card">
                    <h5><i class="fas fa-clock me-2"></i>Scheduled Backups</h5>
                    <p class="text-muted">Set up automatic backups to run at regular intervals.</p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Scheduled backups require cron job configuration. Contact your hosting provider for assistance.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Example Cron Commands:</h6>
                            <div class="bg-light p-3 rounded">
                                <code>
                                    # Daily backup at 2:00 AM<br>
                                    0 2 * * * php /path/to/your/admin/backup_cron.php<br><br>
                                    # Weekly backup on Sunday at 3:00 AM<br>
                                    0 3 * * 0 php /path/to/your/admin/backup_cron.php<br><br>
                                    # Monthly backup on 1st at 4:00 AM<br>
                                    0 4 1 * * php /path/to/your/admin/backup_cron.php
                                </code>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Backup Settings:</h6>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Backup Frequency:</label>
                                    <select class="form-select" name="backup_frequency">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="manual">Manual Only</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keep Backups For:</label>
                                    <select class="form-select" name="backup_retention">
                                        <option value="7">7 Days</option>
                                        <option value="30">30 Days</option>
                                        <option value="90">90 Days</option>
                                        <option value="365">1 Year</option>
                                    </select>
                                </div>
                                <button type="submit" name="save_backup_settings" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload validation
        document.getElementById('backup_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file extension
                if (!file.name.toLowerCase().endsWith('.sql')) {
                    alert('Please select a valid SQL backup file.');
                    e.target.value = '';
                    return;
                }
                
                // Check file size (50MB max)
                const maxSize = 50 * 1024 * 1024; // 50MB in bytes
                if (file.size > maxSize) {
                    alert('File size exceeds 50MB limit.');
                    e.target.value = '';
                    return;
                }
            }
        });
        
        // Auto-refresh backup list
        function refreshBackupList() {
            // This could be implemented with AJAX to refresh the backup list
            console.log('Refreshing backup list...');
        }
        
        // Refresh every 30 seconds
        setInterval(refreshBackupList, 30000);
    </script>
</body>
</html>
