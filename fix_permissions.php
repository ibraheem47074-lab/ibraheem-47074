<?php
/**
 * File Permissions Fix Script for Hostinger
 * 
 * This script sets correct file and directory permissions for Hostinger hosting.
 * Run this script after uploading files to ensure proper security and functionality.
 * 
 * IMPORTANT: Delete this script after successful setup!
 */

// Prevent direct access in production
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied. Add ?confirm=yes to run this script.');
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PK Live News - Fix Permissions</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; }
        .container { max-width: 800px; margin: 50px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 20px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { color: #2c3e50; margin-bottom: 10px; }
        .header p { color: #7f8c8d; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .permission-item { padding: 10px; margin: 5px 0; border-radius: 4px; font-family: monospace; font-size: 12px; }
        .perm-success { background: #d4edda; color: #155724; }
        .perm-error { background: #f8d7da; color: #721c24; }
        .perm-warning { background: #fff3cd; color: #856404; }
        .summary { margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 6px; }
        .btn { background: #3498db; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PK Live News - Fix Permissions</h1>
            <p>Setting up correct file and directory permissions for Hostinger</p>
        </div>

        <?php
        $results = [];
        $success_count = 0;
        $error_count = 0;
        $warning_count = 0;

        // Function to recursively set permissions
        function set_permissions_recursive($path, $dir_perm, $file_perm) {
            global $results, $success_count, $error_count, $warning_count;
            
            if (!file_exists($path)) {
                $results[] = [
                    'path' => $path,
                    'type' => 'missing',
                    'status' => 'error',
                    'message' => 'Path does not exist'
                ];
                $error_count++;
                return false;
            }
            
            if (is_dir($path)) {
                // Set directory permissions
                if (chmod($path, $dir_perm)) {
                    $results[] = [
                        'path' => $path,
                        'type' => 'directory',
                        'status' => 'success',
                        'message' => sprintf('Directory permissions set to %o', $dir_perm)
                    ];
                    $success_count++;
                } else {
                    $results[] = [
                        'path' => $path,
                        'type' => 'directory',
                        'status' => 'error',
                        'message' => sprintf('Failed to set directory permissions to %o', $dir_perm)
                    ];
                    $error_count++;
                }
                
                // Recursively process subdirectories and files
                $items = scandir($path);
                foreach ($items as $item) {
                    if ($item != '.' && $item != '..') {
                        $full_path = $path . '/' . $item;
                        set_permissions_recursive($full_path, $dir_perm, $file_perm);
                    }
                }
            } else {
                // Set file permissions
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                
                // Different permissions for different file types
                if (in_array($ext, ['php', 'phtml', 'php3', 'php4', 'php5'])) {
                    $target_perm = 0644;
                } elseif (in_array($ext, ['sh', 'cgi'])) {
                    $target_perm = 0755;
                } else {
                    $target_perm = $file_perm;
                }
                
                if (chmod($path, $target_perm)) {
                    $results[] = [
                        'path' => $path,
                        'type' => 'file',
                        'status' => 'success',
                        'message' => sprintf('File permissions set to %o', $target_perm)
                    ];
                    $success_count++;
                } else {
                    $results[] = [
                        'path' => $path,
                        'type' => 'file',
                        'status' => 'error',
                        'message' => sprintf('Failed to set file permissions to %o', $target_perm)
                    ];
                    $error_count++;
                }
            }
        }

        // Set permissions for main directories
        $directories = [
            'uploads' => ['dir' => 0755, 'file' => 0644],
            'uploads/ads' => ['dir' => 0755, 'file' => 0644],
            'uploads/avatars' => ['dir' => 0755, 'file' => 0644],
            'uploads/categories' => ['dir' => 0755, 'file' => 0644],
            'uploads/channels' => ['dir' => 0755, 'file' => 0644],
            'cache' => ['dir' => 0755, 'file' => 0644],
            'logs' => ['dir' => 0755, 'file' => 0644],
            'assets' => ['dir' => 0755, 'file' => 0644],
            'assets/css' => ['dir' => 0755, 'file' => 0644],
            'assets/js' => ['dir' => 0755, 'file' => 0644],
            'assets/images' => ['dir' => 0755, 'file' => 0644],
            'admin' => ['dir' => 0755, 'file' => 0644],
            'api' => ['dir' => 0755, 'file' => 0644],
            'includes' => ['dir' => 0755, 'file' => 0644],
            'components' => ['dir' => 0755, 'file' => 0644],
            'config' => ['dir' => 0755, 'file' => 0644]
        ];

        foreach ($directories as $dir => $perms) {
            set_permissions_recursive($dir, $perms['dir'], $perms['file']);
        }

        // Set permissions for important files
        $important_files = [
            '.htaccess' => 0644,
            'index.php' => 0644,
            'admin.php' => 0644,
            '.env' => 0644,
            'config/database.php' => 0644,
            'config/env.php' => 0644,
            'config/settings.php' => 0644
        ];

        foreach ($important_files as $file => $perm) {
            if (file_exists($file)) {
                if (chmod($file, $perm)) {
                    $results[] = [
                        'path' => $file,
                        'type' => 'file',
                        'status' => 'success',
                        'message' => sprintf('Important file permissions set to %o', $perm)
                    ];
                    $success_count++;
                } else {
                    $results[] = [
                        'path' => $file,
                        'type' => 'file',
                        'status' => 'error',
                        'message' => sprintf('Failed to set important file permissions to %o', $perm)
                    ];
                    $error_count++;
                }
            } else {
                $results[] = [
                    'path' => $file,
                    'type' => 'file',
                    'status' => 'warning',
                    'message' => 'Important file does not exist'
                ];
                $warning_count++;
            }
        }

        // Check if .htaccess.hostinger exists and suggest using it
        if (file_exists('.htaccess.hostinger')) {
            $results[] = [
                'path' => '.htaccess.hostinger',
                'type' => 'file',
                'status' => 'info',
                'message' => 'Production .htaccess file found. Consider copying it to .htaccess'
            ];
        }

        // Display results
        echo '<div class="alert alert-info">';
        echo '<h3>Permission Fix Results:</h3>';
        echo '<p>Success: ' . $success_count . ' | Errors: ' . $error_count . ' | Warnings: ' . $warning_count . '</p>';
        echo '</div>';

        if ($error_count > 0) {
            echo '<div class="alert alert-warning">';
            echo '<strong>Note:</strong> Some permissions could not be set automatically. This may be due to server restrictions. ';
            echo 'You may need to set permissions manually via FTP or File Manager.';
            echo '</div>';
        }

        echo '<h3>Detailed Results:</h3>';
        foreach ($results as $result) {
            $class = 'perm-' . $result['status'];
            echo '<div class="permission-item ' . $class . '">';
            echo '[' . strtoupper($result['status']) . '] ' . $result['path'] . ' - ' . $result['message'];
            echo '</div>';
        }

        // Summary
        echo '<div class="summary">';
        echo '<h3>Summary:</h3>';
        echo '<ul>';
        echo '<li><strong>Directories:</strong> Should be set to 755 (rwxr-xr-x)</li>';
        echo '<li><strong>PHP Files:</strong> Should be set to 644 (rw-r--r--)</li>';
        echo '<li><strong>Upload Directories:</strong> Should be set to 755 (rwxr-xr-x)</li>';
        echo '<li><strong>Configuration Files:</strong> Should be set to 644 (rw-r--r--)</li>';
        echo '</ul>';
        echo '</div>';

        // Next steps
        echo '<div class="alert alert-info">';
        echo '<h3>Next Steps:</h3>';
        echo '<ol>';
        echo '<li>If there were errors, set permissions manually via FTP/File Manager</li>';
        echo '<li>Test your website functionality</li>';
        echo '<li>Delete this script for security</li>';
        echo '</ol>';
        echo '</div>';

        // Action buttons
        echo '<div style="text-align: center; margin-top: 30px;">';
        echo '<a href="/" class="btn">Test Website</a> ';
        echo '<a href="hostinger_setup.php" class="btn">Run Setup Wizard</a> ';
        echo '<a href="?confirm=yes&delete=yes" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this script?\')">Delete This Script</a>';
        echo '</div>';

        // Handle script deletion
        if (isset($_GET['delete']) && $_GET['delete'] === 'yes') {
            if (unlink(__FILE__)) {
                echo '<div class="alert alert-success">✓ Script deleted successfully!</div>';
                echo '<script>setTimeout(function() { window.location.href = "/"; }, 2000);</script>';
            } else {
                echo '<div class="alert alert-error">✗ Failed to delete script. Please delete manually.</div>';
            }
        }
        ?>

    </div>
</body>
</html>
