<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // General Settings
    if (isset($_POST['update_general'])) {
        $site_name = clean_input($_POST['site_name']);
        $site_description = clean_input($_POST['site_description']);
        $admin_email = clean_input($_POST['admin_email']);
        $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
        
        // Update or insert settings
        $settings = [
            'site_name' => $site_name,
            'site_description' => $site_description,
            'admin_email' => $admin_email,
            'maintenance_mode' => $maintenance_mode
        ];
        
        foreach ($settings as $key => $value) {
            $query = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sss', $key, $value, $value);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "General settings updated successfully!";
    }
    
    // Upload Settings
    if (isset($_POST['update_upload'])) {
        $max_file_size = (int)$_POST['max_file_size'];
        $allowed_extensions = implode(',', array_map('trim', explode(',', $_POST['allowed_extensions'])));
        $upload_path = clean_input($_POST['upload_path']);
        
        $settings = [
            'max_file_size' => $max_file_size,
            'allowed_extensions' => $allowed_extensions,
            'upload_path' => $upload_path
        ];
        
        foreach ($settings as $key => $value) {
            $query = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sss', $key, $value, $value);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "Upload settings updated successfully!";
    }
    
    // Email Settings
    if (isset($_POST['update_email'])) {
        $smtp_host = clean_input($_POST['smtp_host']);
        $smtp_port = (int)$_POST['smtp_port'];
        $smtp_username = clean_input($_POST['smtp_username']);
        $smtp_password = clean_input($_POST['smtp_password']);
        $smtp_encryption = clean_input($_POST['smtp_encryption']);
        
        $settings = [
            'smtp_host' => $smtp_host,
            'smtp_port' => $smtp_port,
            'smtp_username' => $smtp_username,
            'smtp_password' => $smtp_password,
            'smtp_encryption' => $smtp_encryption
        ];
        
        foreach ($settings as $key => $value) {
            $query = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sss', $key, $value, $value);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "Email settings updated successfully!";
    }
    
    // Security Settings
    if (isset($_POST['update_security'])) {
        $session_timeout = (int)$_POST['session_timeout'];
        $max_login_attempts = (int)$_POST['max_login_attempts'];
        $enable_captcha = isset($_POST['enable_captcha']) ? 1 : 0;
        $force_https = isset($_POST['force_https']) ? 1 : 0;
        
        $settings = [
            'session_timeout' => $session_timeout,
            'max_login_attempts' => $max_login_attempts,
            'enable_captcha' => $enable_captcha,
            'force_https' => $force_https
        ];
        
        foreach ($settings as $key => $value) {
            $query = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sss', $key, $value, $value);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "Security settings updated successfully!";
    }
    
    // Header Footer Settings
    if (isset($_POST['update_header_footer'])) {
        $header_content = $_POST['header_content'];
        $footer_content = $_POST['footer_content'];
        $custom_css = $_POST['custom_css'];
        
        $settings = [
            'header_content' => $header_content,
            'footer_content' => $footer_content,
            'custom_css' => $custom_css
        ];
        
        foreach ($settings as $key => $value) {
            $query = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sss', $key, $value, $value);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "Header and Footer settings updated successfully!";
    }
    
    // Cache Settings
    if (isset($_POST['update_cache'])) {
        $enable_cache = isset($_POST['enable_cache']) ? 1 : 0;
        $cache_duration = (int)$_POST['cache_duration'];
        
        $settings = [
            'enable_cache' => $enable_cache,
            'cache_duration' => $cache_duration
        ];
        
        foreach ($settings as $key => $value) {
            $query = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sss', $key, $value, $value);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "Cache settings updated successfully!";
    }
}

// Create system_settings table if it doesn't exist
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'system_settings'");
if (mysqli_num_rows($table_check) == 0) {
    mysqli_query($conn, "CREATE TABLE system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by INT,
        FOREIGN KEY (updated_by) REFERENCES users(id)
    )");
}

// Get current settings
function get_setting($key) {
    global $conn;
    $result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT setting_value FROM system_settings WHERE setting_key = '$key'"));
    return $result ? $result['setting_value'] : null;
}

$settings = [
    'site_name' => get_setting('site_name') ?: SITE_NAME,
    'site_description' => get_setting('site_description') ?: 'PK Live News - Your trusted source for breaking news',
    'admin_email' => get_setting('admin_email') ?: ADMIN_EMAIL,
    'maintenance_mode' => get_setting('maintenance_mode') ?: 0,
    'max_file_size' => get_setting('max_file_size') ?: 5242880,
    'allowed_extensions' => get_setting('allowed_extensions') ?: 'jpg,jpeg,png,gif,mp4,mov,avi',
    'upload_path' => get_setting('upload_path') ?: 'uploads/',
    'smtp_host' => get_setting('smtp_host'),
    'smtp_port' => get_setting('smtp_port') ?: 587,
    'smtp_username' => get_setting('smtp_username'),
    'smtp_password' => get_setting('smtp_password'),
    'smtp_encryption' => get_setting('smtp_encryption') ?: 'tls',
    'session_timeout' => get_setting('session_timeout') ?: 3600,
    'max_login_attempts' => get_setting('max_login_attempts') ?: 5,
    'enable_captcha' => get_setting('enable_captcha') ?: 0,
    'force_https' => get_setting('force_https') ?: 0,
    'header_content' => get_setting('header_content') ?: '',
    'footer_content' => get_setting('footer_content') ?: '',
    'custom_css' => get_setting('custom_css') ?: '',
    'enable_cache' => get_setting('enable_cache') ?: 1,
    'cache_duration' => get_setting('cache_duration') ?: 3600
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .settings-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border-left: 4px solid #667eea;
        }
        .settings-section {
            margin-bottom: 2rem;
        }
        .settings-section h5 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        .form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
        }
        .alert-maintenance {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
        }
        <?php echo $settings['custom_css']; ?>
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-cogs me-3"></i>System Settings</h2>
                <p class="text-muted">Configure and manage your PK Live News system settings.</p>
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

        <?php if ($settings['maintenance_mode']): ?>
            <div class="alert alert-maintenance alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Maintenance Mode Active:</strong> Your website is currently in maintenance mode. Only administrators can access the site.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- General Settings -->
        <div class="settings-card">
            <h4><i class="fas fa-globe me-2"></i>General Settings</h4>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin Email</label>
                            <input type="email" name="admin_email" class="form-control" value="<?php echo htmlspecialchars($settings['admin_email']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Site Description</label>
                            <textarea name="site_description" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenance_mode">
                                    <strong>Maintenance Mode</strong>
                                    <br>
                                    <small class="text-muted">Enable to restrict site access to administrators only</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" name="update_general" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update General Settings
                </button>
            </form>
        </div>

        <!-- Upload Settings -->
        <div class="settings-card">
            <h4><i class="fas fa-upload me-2"></i>Upload Settings</h4>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Maximum File Size (bytes)</label>
                            <input type="number" name="max_file_size" class="form-control" value="<?php echo $settings['max_file_size']; ?>" min="0">
                            <small class="text-muted">Current: <?php echo round($settings['max_file_size'] / 1024 / 1024, 2); ?> MB</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload Path</label>
                            <input type="text" name="upload_path" class="form-control" value="<?php echo htmlspecialchars($settings['upload_path']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Allowed Extensions</label>
                            <input type="text" name="allowed_extensions" class="form-control" value="<?php echo htmlspecialchars($settings['allowed_extensions']); ?>" required>
                            <small class="text-muted">Comma-separated list (e.g., jpg,png,gif,mp4)</small>
                        </div>
                    </div>
                </div>
                <button type="submit" name="update_upload" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Upload Settings
                </button>
            </form>
        </div>

        <!-- Email Settings -->
        <div class="settings-card">
            <h4><i class="fas fa-envelope me-2"></i>Email Settings</h4>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_host']); ?>" placeholder="smtp.gmail.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-control" value="<?php echo $settings['smtp_port']; ?>" placeholder="587">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">SMTP Username</label>
                            <input type="text" name="smtp_username" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_username']); ?>" placeholder="your-email@gmail.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">SMTP Password</label>
                            <input type="password" name="smtp_password" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_password']); ?>" placeholder="Your app password">
                            <small class="text-muted">Use app-specific password for Gmail</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Encryption</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="none" <?php echo $settings['smtp_encryption'] === 'none' ? 'selected' : ''; ?>>None</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" name="update_email" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Email Settings
                </button>
            </form>
        </div>

        <!-- Security Settings -->
        <div class="settings-card">
            <h4><i class="fas fa-shield-alt me-2"></i>Security Settings</h4>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Session Timeout (seconds)</label>
                            <input type="number" name="session_timeout" class="form-control" value="<?php echo $settings['session_timeout']; ?>" min="300">
                            <small class="text-muted">How long until users are logged out automatically</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Login Attempts</label>
                            <input type="number" name="max_login_attempts" class="form-control" value="<?php echo $settings['max_login_attempts']; ?>" min="1" max="10">
                            <small class="text-muted">Maximum failed login attempts before lockout</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="enable_captcha" id="enable_captcha" <?php echo $settings['enable_captcha'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="enable_captcha">
                                    <strong>Enable CAPTCHA</strong>
                                    <br>
                                    <small class="text-muted">Add CAPTCHA to login and registration forms</small>
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="force_https" id="force_https" <?php echo $settings['force_https'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="force_https">
                                    <strong>Force HTTPS</strong>
                                    <br>
                                    <small class="text-muted">Redirect all HTTP requests to HTTPS</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" name="update_security" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Security Settings
                </button>
            </form>
        </div>

        <!-- Header & Footer Settings -->
        <div class="settings-card">
            <h4><i class="fas fa-code me-2"></i>Header & Footer Settings</h4>
            <p class="text-muted">Customize the header and footer content that appears on your website. Only administrators can see and modify these settings.</p>
            
            <form method="POST">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-header me-1"></i>
                                <strong>Custom Header Content</strong>
                                <small class="text-muted d-block">HTML code that will be added to the header section</small>
                            </label>
                            <textarea name="header_content" class="form-control" rows="8" placeholder="Enter custom header HTML code..."><?php echo htmlspecialchars($settings['header_content']); ?></textarea>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                You can use HTML tags like &lt;div&gt;, &lt;span&gt;, &lt;img&gt;, &lt;script&gt;, etc.
                                This content will be inserted into the website header.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-footer me-1"></i>
                                <strong>Custom Footer Content</strong>
                                <small class="text-muted d-block">HTML code that will be added to the footer section</small>
                            </label>
                            <textarea name="footer_content" class="form-control" rows="8" placeholder="Enter custom footer HTML code..."><?php echo htmlspecialchars($settings['footer_content']); ?></textarea>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                You can use HTML tags like &lt;div&gt;, &lt;span&gt;, &lt;p&gt;, &lt;a&gt;, &lt;script&gt;, etc.
                                This content will be inserted into the website footer.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-palette me-1"></i>
                                <strong>Custom CSS</strong>
                                <small class="text-muted d-block">Additional CSS styles for header and footer</small>
                            </label>
                            <textarea name="custom_css" class="form-control" rows="6" placeholder="Enter custom CSS code..."><?php echo htmlspecialchars($settings['custom_css']); ?></textarea>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Add custom CSS to style your header content.
                                Use selectors like .custom-header, etc.
                            </small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Pro Tips:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Use the class <code>custom-header</code> to target your header content</li>
                                <li>Changes will be reflected immediately after saving</li>
                                <li>Only administrators can access these settings</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <button type="submit" name="update_header_footer" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Header & Footer Settings
                </button>
                <button type="button" class="btn btn-secondary ms-2" onclick="previewHeaderFooter()">
                    <i class="fas fa-eye me-2"></i>Preview Changes
                </button>
            </form>
        </div>

        <!-- Cache Settings -->
        <div class="settings-card">
            <h4><i class="fas fa-memory me-2"></i>Cache Settings</h4>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="enable_cache" id="enable_cache" <?php echo $settings['enable_cache'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="enable_cache">
                                    <strong>Enable Caching</strong>
                                    <br>
                                    <small class="text-muted">Improve performance by caching static content</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Cache Duration (seconds)</label>
                            <input type="number" name="cache_duration" class="form-control" value="<?php echo $settings['cache_duration']; ?>" min="60">
                            <small class="text-muted">How long to cache content (<?php echo round($settings['cache_duration'] / 60); ?> minutes)</small>
                        </div>
                    </div>
                </div>
                <button type="submit" name="update_cache" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Cache Settings
                </button>
            </form>
        </div>

        <!-- System Information -->
        <div class="settings-card">
            <h4><i class="fas fa-info-circle me-2"></i>System Information</h4>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>PHP Version:</strong></td>
                            <td><?php echo PHP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <td><strong>MySQL Version:</strong></td>
                            <td><?php echo mysqli_get_server_info($conn); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Web Server:</strong></td>
                            <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Memory Limit:</strong></td>
                            <td><?php echo ini_get('memory_limit'); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Upload Max Size:</strong></td>
                            <td><?php echo ini_get('upload_max_filesize'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Post Max Size:</strong></td>
                            <td><?php echo ini_get('post_max_size'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Max Execution Time:</strong></td>
                            <td><?php echo ini_get('max_execution_time'); ?>s</td>
                        </tr>
                        <tr>
                            <td><strong>Timezone:</strong></td>
                            <td><?php echo date_default_timezone_get(); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Preview Header and Footer functionality
    function previewHeaderFooter() {
        const headerContent = document.querySelector('textarea[name="header_content"]').value;
        const footerContent = document.querySelector('textarea[name="footer_content"]').value;
        const customCSS = document.querySelector('textarea[name="custom_css"]').value;
        
        // Create preview modal
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-eye me-2"></i>Header & Footer Preview
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <style>${customCSS}</style>
                        
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Header Preview:</h6>
                            <div class="border rounded p-3 bg-light">
                                <div class="custom-header">${headerContent || '<em class="text-muted">No custom header content</em>'}</div>
                            </div>
                        </div>
                        
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This is a preview of how your header and footer will look. The actual appearance may vary depending on your theme and other CSS styles.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Remove modal from DOM when hidden
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }
    
    // Auto-save functionality (optional)
    let autoSaveTimer;
    document.querySelectorAll('textarea[name="header_content"], textarea[name="footer_content"], textarea[name="custom_css"]').forEach(textarea => {
        textarea.addEventListener('input', () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                console.log('Auto-save functionality can be implemented here');
            }, 30000); // Auto-save after 30 seconds of inactivity
        });
    });
    </script>
</body>
</html>
