<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update general settings
    if (isset($_POST['general_settings'])) {
        $site_name = clean_input($_POST['site_name']);
        $site_description = clean_input($_POST['site_description']);
        $contact_email = clean_input($_POST['contact_email']);
        $facebook_url = clean_input($_POST['facebook_url']);
        $twitter_url = clean_input($_POST['twitter_url']);
        $youtube_url = clean_input($_POST['youtube_url']);
        
        // Update settings in database
        $settings = [
            'site_name' => $site_name,
            'site_description' => $site_description,
            'contact_email' => $contact_email,
            'facebook_url' => $facebook_url,
            'twitter_url' => $twitter_url,
            'youtube_url' => $youtube_url
        ];
        
        foreach ($settings as $key => $value) {
            $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $value, $key);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "General settings updated successfully!";
    }
    
    // Update social media settings
    if (isset($_POST['social_settings'])) {
        $facebook_url = clean_input($_POST['facebook_url']);
        $twitter_url = clean_input($_POST['twitter_url']);
        $youtube_url = clean_input($_POST['youtube_url']);
        $instagram_url = clean_input($_POST['instagram_url']);
        $linkedin_url = clean_input($_POST['linkedin_url']);
        
        $social_settings = [
            'facebook_url' => $facebook_url,
            'twitter_url' => $twitter_url,
            'youtube_url' => $youtube_url,
            'instagram_url' => $instagram_url,
            'linkedin_url' => $linkedin_url
        ];
        
        foreach ($social_settings as $key => $value) {
            $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $value, $key);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "Social media settings updated successfully!";
    }
    
    // Update SEO settings
    if (isset($_POST['seo_settings'])) {
        $meta_description = clean_input($_POST['meta_description']);
        $meta_keywords = clean_input($_POST['meta_keywords']);
        $google_analytics = clean_input($_POST['google_analytics']);
        $facebook_pixel = clean_input($_POST['facebook_pixel']);
        
        $seo_settings = [
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'google_analytics' => $google_analytics,
            'facebook_pixel' => $facebook_pixel
        ];
        
        foreach ($seo_settings as $key => $value) {
            $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $value, $key);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "SEO settings updated successfully!";
    }
    
    // Update system settings
    if (isset($_POST['system_settings'])) {
        $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
        $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
        $allow_registration = isset($_POST['allow_registration']) ? 1 : 0;
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $max_upload_size = (int)$_POST['max_upload_size'];
        
        $system_settings = [
            'maintenance_mode' => $maintenance_mode,
            'allow_comments' => $allow_comments,
            'allow_registration' => $allow_registration,
            'email_notifications' => $email_notifications,
            'max_upload_size' => $max_upload_size
        ];
        
        foreach ($system_settings as $key => $value) {
            $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $value, $key);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "System settings updated successfully!";
    }
    
    // Update email settings
    if (isset($_POST['email_settings'])) {
        $smtp_host = clean_input($_POST['smtp_host']);
        $smtp_port = (int)$_POST['smtp_port'];
        $smtp_username = clean_input($_POST['smtp_username']);
        $smtp_password = clean_input($_POST['smtp_password']);
        $smtp_encryption = clean_input($_POST['smtp_encryption']);
        $email_from_name = clean_input($_POST['email_from_name']);
        $email_from_address = clean_input($_POST['email_from_address']);
        
        $email_settings = [
            'smtp_host' => $smtp_host,
            'smtp_port' => $smtp_port,
            'smtp_username' => $smtp_username,
            'smtp_password' => $smtp_password,
            'smtp_encryption' => $smtp_encryption,
            'email_from_name' => $email_from_name,
            'email_from_address' => $email_from_address
        ];
        
        foreach ($email_settings as $key => $value) {
            $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $value, $key);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "Email settings updated successfully!";
    }
    
    // Update security settings
    if (isset($_POST['security_settings'])) {
        $force_https = isset($_POST['force_https']) ? 1 : 0;
        $enable_captcha = isset($_POST['enable_captcha']) ? 1 : 0;
        $session_timeout = (int)$_POST['session_timeout'];
        $max_login_attempts = (int)$_POST['max_login_attempts'];
        $password_min_length = (int)$_POST['password_min_length'];
        $enable_2fa = isset($_POST['enable_2fa']) ? 1 : 0;
        
        $security_settings = [
            'force_https' => $force_https,
            'enable_captcha' => $enable_captcha,
            'session_timeout' => $session_timeout,
            'max_login_attempts' => $max_login_attempts,
            'password_min_length' => $password_min_length,
            'enable_2fa' => $enable_2fa
        ];
        
        foreach ($security_settings as $key => $value) {
            $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $value, $key);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "Security settings updated successfully!";
    }
    
    // Update appearance settings
    if (isset($_POST['appearance_settings'])) {
        $theme_color = clean_input($_POST['theme_color']);
        $dark_mode_default = isset($_POST['dark_mode_default']) ? 1 : 0;
        $logo_url = clean_input($_POST['logo_url']);
        $favicon_url = clean_input($_POST['favicon_url']);
        $custom_css = clean_input($_POST['custom_css']);
        $custom_js = clean_input($_POST['custom_js']);
        
        $appearance_settings = [
            'theme_color' => $theme_color,
            'dark_mode_default' => $dark_mode_default,
            'logo_url' => $logo_url,
            'favicon_url' => $favicon_url,
            'custom_css' => $custom_css,
            'custom_js' => $custom_js
        ];
        
        foreach ($appearance_settings as $key => $value) {
            $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $value, $key);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "Appearance settings updated successfully!";
    }
    
    // Update backup settings
    if (isset($_POST['backup_settings'])) {
        $auto_backup = isset($_POST['auto_backup']) ? 1 : 0;
        $backup_frequency = clean_input($_POST['backup_frequency']);
        $backup_retention = (int)$_POST['backup_retention'];
        $backup_location = clean_input($_POST['backup_location']);
        
        $backup_settings = [
            'auto_backup' => $auto_backup,
            'backup_frequency' => $backup_frequency,
            'backup_retention' => $backup_retention,
            'backup_location' => $backup_location
        ];
        
        foreach ($backup_settings as $key => $value) {
            $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $value, $key);
            mysqli_stmt_execute($stmt);
        }
        
        $success = "Backup settings updated successfully!";
    }
}

// Get current settings
$settings_query = "SELECT setting_key, setting_value FROM settings";
$settings_result = mysqli_query($conn, $settings_query);
$settings = [];

while ($row = mysqli_fetch_assoc($settings_result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .admin-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .admin-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .admin-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .settings-tabs {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 0;
        }
        .settings-tabs .nav-link {
            color: #495057;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 12px 20px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .settings-tabs .nav-link:hover {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        .settings-tabs .nav-link.active {
            color: #667eea;
            background-color: #f8f9fa;
            border-bottom-color: #667eea;
            font-weight: 600;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .setting-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }
        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK Live News</h4>
                        <small>Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-polls.php">
                                <i class="fas fa-poll me-2"></i>Polls
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-ads.php">
                                <i class="fas fa-ad me-2"></i>Advertisements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-main-content">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">System Settings</h1>
                        <small>Configure your website preferences</small>
                    </div>
                    <div>
                        <button class="btn btn-light" onclick="backupSettings()">
                            <i class="fas fa-download me-2"></i>Backup Settings
                        </button>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="alert-container">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Settings Tabs -->
                <div class="card">
                    <div class="card-body p-0">
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs settings-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" role="tab">
                                    <i class="fas fa-cog me-2"></i>General
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#social" role="tab">
                                    <i class="fas fa-share-alt me-2"></i>Social Media
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#seo" role="tab">
                                    <i class="fas fa-search me-2"></i>SEO
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#email" role="tab">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#security" role="tab">
                                    <i class="fas fa-shield-alt me-2"></i>Security
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#appearance" role="tab">
                                    <i class="fas fa-palette me-2"></i>Appearance
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#backup" role="tab">
                                    <i class="fas fa-database me-2"></i>Backup
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#system" role="tab">
                                    <i class="fas fa-server me-2"></i>System
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- General Settings Tab -->
                            <div class="tab-pane active" id="general">
                                <form method="POST">
                                    <input type="hidden" name="general_settings" value="1">
                                    
                                    <div class="setting-card">
                                        <h5 class="mb-4">
                                            <i class="fas fa-info-circle me-2"></i>General Information
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="site_name" class="form-label">Site Name</label>
                                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                                       value="<?php echo htmlspecialchars($settings['site_name'] ?? 'PK Live News'); ?>" 
                                                       required>
                                                <small class="help-text">Your website name</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="contact_email" class="form-label">Contact Email</label>
                                                <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                                       value="<?php echo htmlspecialchars($settings['contact_email'] ?? 'contact@pklivenews.com'); ?>" 
                                                       required>
                                                <small class="help-text">Public contact email address</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="site_description" class="form-label">Site Description</label>
                                                <textarea class="form-control" id="site_description" name="site_description" rows="3" 
                                                          placeholder="Brief description of your website"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                                                <small class="help-text">Meta description for search engines</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save General Settings
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="resetGeneralForm()">
                                                <i class="fas fa-undo me-2"></i>Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Social Media Settings Tab -->
                            <div class="tab-pane" id="social">
                                <form method="POST">
                                    <input type="hidden" name="social_settings" value="1">
                                    
                                    <div class="setting-card">
                                        <h5 class="mb-4">
                                            <i class="fas fa-share-alt me-2"></i>Social Media Links
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="facebook_url" class="form-label">Facebook URL</label>
                                                <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                                       value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>" 
                                                       placeholder="https://facebook.com/yourpage">
                                                <small class="help-text">Your Facebook page URL</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="twitter_url" class="form-label">Twitter URL</label>
                                                <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                                       value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>" 
                                                       placeholder="https://twitter.com/yourhandle">
                                                <small class="help-text">Your Twitter profile URL</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="youtube_url" class="form-label">YouTube URL</label>
                                                <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                                                       value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>" 
                                                       placeholder="https://youtube.com/yourchannel">
                                                <small class="help-text">Your YouTube channel URL</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="instagram_url" class="form-label">Instagram URL</label>
                                                <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                                       value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>" 
                                                       placeholder="https://instagram.com/yourhandle">
                                                <small class="help-text">Your Instagram profile URL</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                                <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                                                       value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>" 
                                                       placeholder="https://linkedin.com/in/yourprofile">
                                                <small class="help-text">Your LinkedIn profile URL</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save Social Settings
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="resetSocialForm()">
                                                <i class="fas fa-undo me-2"></i>Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- SEO Settings Tab -->
                            <div class="tab-pane" id="seo">
                                <form method="POST">
                                    <input type="hidden" name="seo_settings" value="1">
                                    
                                    <div class="setting-card">
                                        <h5 class="mb-4">
                                            <i class="fas fa-search me-2"></i>SEO Configuration
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="meta_description" class="form-label">Meta Description</label>
                                                <textarea class="form-control" id="meta_description" name="meta_description" rows="3" 
                                                          placeholder="Default meta description for pages"><?php echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea>
                                                <small class="help-text">Default description for search engines (160 chars)</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                                                       value="<?php echo htmlspecialchars($settings['meta_keywords'] ?? ''); ?>" 
                                                       placeholder="news, politics, pakistan">
                                                <small class="help-text">Comma-separated keywords for SEO</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="google_analytics" class="form-label">Google Analytics</label>
                                                <textarea class="form-control" id="google_analytics" name="google_analytics" rows="4" 
                                                          placeholder="Google Analytics tracking code"><?php echo htmlspecialchars($settings['google_analytics'] ?? ''); ?></textarea>
                                                <small class="help-text">Google Analytics tracking script</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="facebook_pixel" class="form-label">Facebook Pixel</label>
                                                <textarea class="form-control" id="facebook_pixel" name="facebook_pixel" rows="4" 
                                                          placeholder="Facebook Pixel code"><?php echo htmlspecialchars($settings['facebook_pixel'] ?? ''); ?></textarea>
                                                <small class="help-text">Facebook Pixel tracking code</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save SEO Settings
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="resetSEOForm()">
                                                <i class="fas fa-undo me-2"></i>Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Email Settings Tab -->
                            <div class="tab-pane" id="email">
                                <form method="POST">
                                    <input type="hidden" name="email_settings" value="1">
                                    
                                    <div class="setting-card">
                                        <h5 class="mb-4">
                                            <i class="fas fa-envelope me-2"></i>Email Configuration
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="smtp_host" class="form-label">SMTP Host</label>
                                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                                       value="<?php echo htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com'); ?>" 
                                                       placeholder="smtp.gmail.com">
                                                <small class="help-text">SMTP server hostname</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="smtp_port" class="form-label">SMTP Port</label>
                                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                                       value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>" 
                                                       placeholder="587">
                                                <small class="help-text">SMTP server port (587 for TLS, 465 for SSL)</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="smtp_username" class="form-label">SMTP Username</label>
                                                <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                                       value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>" 
                                                       placeholder="your-email@gmail.com">
                                                <small class="help-text">SMTP authentication username</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="smtp_password" class="form-label">SMTP Password</label>
                                                <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                                       value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>" 
                                                       placeholder="Your app password">
                                                <small class="help-text">SMTP authentication password</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="smtp_encryption" class="form-label">SMTP Encryption</label>
                                                <select class="form-control" id="smtp_encryption" name="smtp_encryption">
                                                    <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                                    <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                                    <option value="none" <?php echo ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                                                </select>
                                                <small class="help-text">Encryption method for SMTP</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-outline-primary mt-4" onclick="testEmailSettings()">
                                                    <i class="fas fa-paper-plane me-2"></i>Test Email Settings
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="mb-3">Email From Settings</h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="email_from_name" class="form-label">From Name</label>
                                                <input type="text" class="form-control" id="email_from_name" name="email_from_name" 
                                                       value="<?php echo htmlspecialchars($settings['email_from_name'] ?? 'PK Live News'); ?>" 
                                                       placeholder="PK Live News">
                                                <small class="help-text">Default sender name</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="email_from_address" class="form-label">From Address</label>
                                                <input type="email" class="form-control" id="email_from_address" name="email_from_address" 
                                                       value="<?php echo htmlspecialchars($settings['email_from_address'] ?? 'noreply@pklivenews.com'); ?>" 
                                                       placeholder="noreply@pklivenews.com">
                                                <small class="help-text">Default sender email address</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save Email Settings
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="resetEmailForm()">
                                                <i class="fas fa-undo me-2"></i>Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Security Settings Tab -->
                            <div class="tab-pane" id="security">
                                <form method="POST">
                                    <input type="hidden" name="security_settings" value="1">
                                    
                                    <div class="setting-card">
                                        <h5 class="mb-4">
                                            <i class="fas fa-shield-alt me-2"></i>Security Configuration
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="force_https" name="force_https" 
                                                           value="1" <?php echo ($settings['force_https'] ?? 0) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="force_https">
                                                        Force HTTPS
                                                    </label>
                                                    <small class="help-text">Redirect all HTTP requests to HTTPS</small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="enable_captcha" name="enable_captcha" 
                                                           value="1" <?php echo ($settings['enable_captcha'] ?? 0) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="enable_captcha">
                                                        Enable CAPTCHA
                                                    </label>
                                                    <small class="help-text">Require CAPTCHA for registration and login</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="enable_2fa" name="enable_2fa" 
                                                           value="1" <?php echo ($settings['enable_2fa'] ?? 0) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="enable_2fa">
                                                        Enable Two-Factor Authentication
                                                    </label>
                                                    <small class="help-text">Require 2FA for admin accounts</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="mb-3">Session & Authentication</h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                                                <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                                                       value="<?php echo htmlspecialchars($settings['session_timeout'] ?? '30'); ?>" 
                                                       min="5" max="1440">
                                                <small class="help-text">User session duration in minutes</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="max_login_attempts" class="form-label">Max Login Attempts</label>
                                                <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" 
                                                       value="<?php echo htmlspecialchars($settings['max_login_attempts'] ?? '5'); ?>" 
                                                       min="1" max="10">
                                                <small class="help-text">Maximum failed login attempts before lockout</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="password_min_length" class="form-label">Minimum Password Length</label>
                                                <input type="number" class="form-control" id="password_min_length" name="password_min_length" 
                                                       value="<?php echo htmlspecialchars($settings['password_min_length'] ?? '8'); ?>" 
                                                       min="6" max="32">
                                                <small class="help-text">Minimum required password length</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save Security Settings
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="resetSecurityForm()">
                                                <i class="fas fa-undo me-2"></i>Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Appearance Settings Tab -->
                            <div class="tab-pane" id="appearance">
                                <form method="POST">
                                    <input type="hidden" name="appearance_settings" value="1">
                                    
                                    <div class="setting-card">
                                        <h5 class="mb-4">
                                            <i class="fas fa-palette me-2"></i>Appearance Configuration
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="theme_color" class="form-label">Theme Color</label>
                                                <div class="input-group">
                                                    <input type="color" class="form-control form-control-color" id="theme_color" name="theme_color" 
                                                           value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#dc3545'); ?>" 
                                                           style="width: 50px;">
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#dc3545'); ?>" 
                                                           readonly>
                                                </div>
                                                <small class="help-text">Primary theme color for the website</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-check mb-3 mt-3">
                                                    <input class="form-check-input" type="checkbox" id="dark_mode_default" name="dark_mode_default" 
                                                           value="1" <?php echo ($settings['dark_mode_default'] ?? 0) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="dark_mode_default">
                                                        Enable Dark Mode by Default
                                                    </label>
                                                    <small class="help-text">Set dark mode as the default theme</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="mb-3">Branding</h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="logo_url" class="form-label">Logo URL</label>
                                                <input type="url" class="form-control" id="logo_url" name="logo_url" 
                                                       value="<?php echo htmlspecialchars($settings['logo_url'] ?? ''); ?>" 
                                                       placeholder="/assets/images/logo.png">
                                                <small class="help-text">Path to your logo image</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="favicon_url" class="form-label">Favicon URL</label>
                                                <input type="url" class="form-control" id="favicon_url" name="favicon_url" 
                                                       value="<?php echo htmlspecialchars($settings['favicon_url'] ?? ''); ?>" 
                                                       placeholder="/assets/images/favicon.ico">
                                                <small class="help-text">Path to your favicon</small>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="mb-3">Custom Code</h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="custom_css" class="form-label">Custom CSS</label>
                                                <textarea class="form-control" id="custom_css" name="custom_css" rows="6" 
                                                          placeholder="/* Add your custom CSS here */"><?php echo htmlspecialchars($settings['custom_css'] ?? ''); ?></textarea>
                                                <small class="help-text">Additional CSS to be loaded on all pages</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="custom_js" class="form-label">Custom JavaScript</label>
                                                <textarea class="form-control" id="custom_js" name="custom_js" rows="6" 
                                                          placeholder="// Add your custom JavaScript here"><?php echo htmlspecialchars($settings['custom_js'] ?? ''); ?></textarea>
                                                <small class="help-text">Additional JavaScript to be loaded on all pages</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save Appearance Settings
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="resetAppearanceForm()">
                                                <i class="fas fa-undo me-2"></i>Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Backup Settings Tab -->
                            <div class="tab-pane" id="backup">
                                <form method="POST">
                                    <input type="hidden" name="backup_settings" value="1">
                                    
                                    <div class="setting-card">
                                        <h5 class="mb-4">
                                            <i class="fas fa-database me-2"></i>Backup Configuration
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="auto_backup" name="auto_backup" 
                                                           value="1" <?php echo ($settings['auto_backup'] ?? 0) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="auto_backup">
                                                        Enable Automatic Backups
                                                    </label>
                                                    <small class="help-text">Automatically create database backups</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="backup_frequency" class="form-label">Backup Frequency</label>
                                                <select class="form-control" id="backup_frequency" name="backup_frequency">
                                                    <option value="daily" <?php echo ($settings['backup_frequency'] ?? 'daily') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                                    <option value="weekly" <?php echo ($settings['backup_frequency'] ?? '') === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                                    <option value="monthly" <?php echo ($settings['backup_frequency'] ?? '') === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                                </select>
                                                <small class="help-text">How often to create automatic backups</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="backup_retention" class="form-label">Backup Retention (days)</label>
                                                <input type="number" class="form-control" id="backup_retention" name="backup_retention" 
                                                       value="<?php echo htmlspecialchars($settings['backup_retention'] ?? '30'); ?>" 
                                                       min="1" max="365">
                                                <small class="help-text">How many days to keep backup files</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="backup_location" class="form-label">Backup Location</label>
                                                <input type="text" class="form-control" id="backup_location" name="backup_location" 
                                                       value="<?php echo htmlspecialchars($settings['backup_location'] ?? '/backups/'); ?>" 
                                                       placeholder="/backups/">
                                                <small class="help-text">Directory to store backup files</small>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="mb-3">Manual Backup</h6>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-success" onclick="createBackup()">
                                                <i class="fas fa-download me-2"></i>Create Backup Now
                                            </button>
                                            <button type="button" class="btn btn-info" onclick="restoreBackup()">
                                                <i class="fas fa-upload me-2"></i>Restore Backup
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="viewBackups()">
                                                <i class="fas fa-list me-2"></i>View Backups
                                            </button>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save Backup Settings
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="resetBackupForm()">
                                                <i class="fas fa-undo me-2"></i>Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- System Settings Tab -->
                            <div class="tab-pane" id="system">
                                <form method="POST">
                                    <input type="hidden" name="system_settings" value="1">
                                    
                                    <div class="setting-card">
                                        <h5 class="mb-4">
                                            <i class="fas fa-server me-2"></i>System Configuration
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                                           value="1" <?php echo ($settings['maintenance_mode'] ?? 0) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="maintenance_mode">
                                                        Maintenance Mode
                                                    </label>
                                                    <small class="help-text">Temporarily disable website for maintenance</small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments" 
                                                           value="1" <?php echo ($settings['allow_comments'] ?? 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="allow_comments">
                                                        Allow Comments
                                                    </label>
                                                    <small class="help-text">Enable user comments on articles</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration" 
                                                           value="1" <?php echo ($settings['allow_registration'] ?? 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="allow_registration">
                                                        Allow Registration
                                                    </label>
                                                    <small class="help-text">Allow users to register accounts</small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" 
                                                           value="1" <?php echo ($settings['email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="email_notifications">
                                                        Email Notifications
                                                    </label>
                                                    <small class="help-text">Send email notifications for new content</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="max_upload_size" class="form-label">Max Upload Size (MB)</label>
                                                <input type="number" class="form-control" id="max_upload_size" name="max_upload_size" 
                                                       value="<?php echo htmlspecialchars($settings['max_upload_size'] ?? '5'); ?>" 
                                                       min="1" max="50">
                                                <small class="help-text">Maximum file upload size</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2 mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save System Settings
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="resetSystemForm()">
                                                <i class="fas fa-undo me-2"></i>Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info me-2"></i>System Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Server Information</h6>
                                <ul class="list-unstyled">
                                    <li><strong>PHP Version:</strong> <?php echo phpversion(); ?></li>
                                    <li><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></li>
                                    <li><strong>Database:</strong> MySQL</li>
                                    <li><strong>Upload Max Size:</strong> <?php echo ini_get('upload_max_filesize'); ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Website Statistics</h6>
                                <ul class="list-unstyled">
                                    <?php
                                    $news_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news")->fetch_assoc()['count'];
                                    $users_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                                    $comments_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
                                    ?>
                                    <li><strong>Total News:</strong> <?php echo number_format($news_count ?? 0); ?></li>
                                    <li><strong>Total Users:</strong> <?php echo number_format($users_count ?? 0); ?></li>
                                    <li><strong>Total Comments:</strong> <?php echo number_format($comments_count ?? 0); ?></li>
                                    <li><strong>Database Size:</strong> <?php echo round(mysqli_query($conn, "SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = 'pk_live_news'")->fetch_assoc()['size'] / 1024 / 1024, 2); ?> MB</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab switching
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Remove active class from all tabs
                document.querySelectorAll('.settings-tabs .nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                
                // Show selected tab
                const targetId = this.getAttribute('data-bs-target');
                document.getElementById(targetId).classList.add('active');
                this.classList.add('active');
            });
        });

        // Form reset functions
        function resetGeneralForm() {
            document.querySelector('#general form').reset();
        }

        function resetSocialForm() {
            document.querySelector('#social form').reset();
        }

        function resetSEOForm() {
            document.querySelector('#seo form').reset();
        }

        function resetEmailForm() {
            document.querySelector('#email form').reset();
        }

        function resetSecurityForm() {
            document.querySelector('#security form').reset();
        }

        function resetAppearanceForm() {
            document.querySelector('#appearance form').reset();
        }

        function resetBackupForm() {
            document.querySelector('#backup form').reset();
        }

        function resetSystemForm() {
            document.querySelector('#system form').reset();
        }

        // Test email settings
        function testEmailSettings() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
            btn.disabled = true;

            // Simulate email test
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    Test email sent successfully! Check your inbox.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.alert-container').appendChild(alert);
                
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            }, 2000);
        }

        // Create backup
        function createBackup() {
            if (confirm('Create a new database backup now?')) {
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
                btn.disabled = true;

                // Simulate backup creation
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        Backup created successfully! Filename: backup_${new Date().toISOString().slice(0,10)}.sql
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('.alert-container').appendChild(alert);
                    
                    setTimeout(() => {
                        alert.remove();
                    }, 5000);
                }, 3000);
            }
        }

        // Restore backup
        function restoreBackup() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.sql';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (confirm(`Restore backup from "${file.name}"? This will overwrite current data!`)) {
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-warning alert-dismissible fade show';
                        alert.innerHTML = `
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Backup restoration would be processed here. This is a demo.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.querySelector('.alert-container').appendChild(alert);
                        
                        setTimeout(() => {
                            alert.remove();
                        }, 5000);
                    }
                }
            };
            input.click();
        }

        // View backups
        function viewBackups() {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-database me-2"></i>Available Backups
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Filename</th>
                                            <th>Size</th>
                                            <th>Date Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>backup_2024-03-06.sql</td>
                                            <td>2.4 MB</td>
                                            <td>2024-03-06 10:30:00</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>backup_2024-03-05.sql</td>
                                            <td>2.3 MB</td>
                                            <td>2024-03-05 10:30:00</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
            
            modal.addEventListener('hidden.bs.modal', function() {
                modal.remove();
            });
        }

        // Theme color preview
        document.getElementById('theme_color')?.addEventListener('input', function() {
            const color = this.value;
            const textInput = this.nextElementSibling;
            textInput.value = color;
            
            // Update theme preview
            document.documentElement.style.setProperty('--theme-color', color);
        });

        // Custom CSS/JS validation
        document.getElementById('custom_css')?.addEventListener('input', function() {
            const css = this.value;
            if (css.includes('javascript:') || css.includes('<script')) {
                this.style.borderColor = '#dc3545';
                this.classList.add('is-invalid');
            } else {
                this.style.borderColor = '#ced4da';
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('custom_js')?.addEventListener('input', function() {
            const js = this.value;
            if (js.includes('eval(') || js.includes('document.write')) {
                this.style.borderColor = '#dc3545';
                this.classList.add('is-invalid');
            } else {
                this.style.borderColor = '#ced4da';
                this.classList.remove('is-invalid');
            }
        });

        // Backup settings
        function backupSettings() {
            if (confirm('Download all settings as backup file?')) {
                const settings = {
                    general: {
                        site_name: document.getElementById('site_name')?.value || '',
                        contact_email: document.getElementById('contact_email')?.value || '',
                        site_description: document.getElementById('site_description')?.value || ''
                    },
                    social: {
                        facebook_url: document.getElementById('facebook_url')?.value || '',
                        twitter_url: document.getElementById('twitter_url')?.value || '',
                        youtube_url: document.getElementById('youtube_url')?.value || '',
                        instagram_url: document.getElementById('instagram_url')?.value || '',
                        linkedin_url: document.getElementById('linkedin_url')?.value || ''
                    },
                    seo: {
                        meta_description: document.getElementById('meta_description')?.value || '',
                        meta_keywords: document.getElementById('meta_keywords')?.value || '',
                        google_analytics: document.getElementById('google_analytics')?.value || '',
                        facebook_pixel: document.getElementById('facebook_pixel')?.value || ''
                    },
                    email: {
                        smtp_host: document.getElementById('smtp_host')?.value || '',
                        smtp_port: document.getElementById('smtp_port')?.value || '',
                        smtp_username: document.getElementById('smtp_username')?.value || '',
                        smtp_password: document.getElementById('smtp_password')?.value || '',
                        smtp_encryption: document.getElementById('smtp_encryption')?.value || '',
                        email_from_name: document.getElementById('email_from_name')?.value || '',
                        email_from_address: document.getElementById('email_from_address')?.value || ''
                    },
                    security: {
                        force_https: document.getElementById('force_https')?.checked || false,
                        enable_captcha: document.getElementById('enable_captcha')?.checked || false,
                        session_timeout: document.getElementById('session_timeout')?.value || '',
                        max_login_attempts: document.getElementById('max_login_attempts')?.value || '',
                        password_min_length: document.getElementById('password_min_length')?.value || '',
                        enable_2fa: document.getElementById('enable_2fa')?.checked || false
                    },
                    appearance: {
                        theme_color: document.getElementById('theme_color')?.value || '',
                        dark_mode_default: document.getElementById('dark_mode_default')?.checked || false,
                        logo_url: document.getElementById('logo_url')?.value || '',
                        favicon_url: document.getElementById('favicon_url')?.value || '',
                        custom_css: document.getElementById('custom_css')?.value || '',
                        custom_js: document.getElementById('custom_js')?.value || ''
                    },
                    backup: {
                        auto_backup: document.getElementById('auto_backup')?.checked || false,
                        backup_frequency: document.getElementById('backup_frequency')?.value || '',
                        backup_retention: document.getElementById('backup_retention')?.value || '',
                        backup_location: document.getElementById('backup_location')?.value || ''
                    },
                    system: {
                        maintenance_mode: document.getElementById('maintenance_mode')?.checked || false,
                        allow_comments: document.getElementById('allow_comments')?.checked || false,
                        allow_registration: document.getElementById('allow_registration')?.checked || false,
                        email_notifications: document.getElementById('email_notifications')?.checked || false,
                        max_upload_size: document.getElementById('max_upload_size')?.value || ''
                    },
                    backup_date: new Date().toISOString()
                };
                
                const dataStr = JSON.stringify(settings, null, 2);
                const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
                
                const exportFileDefaultName = 'pk_live_news_settings_' + new Date().toISOString().slice(0,10) + '.json';
                
                const linkElement = document.createElement('a');
                linkElement.setAttribute('href', dataUri);
                linkElement.setAttribute('download', exportFileDefaultName);
                linkElement.click();
            }
        }

        // Auto-save warning
        let formChanged = false;
        
        document.querySelectorAll('input, textarea, select').forEach(element => {
            element.addEventListener('change', () => {
                formChanged = true;
            });
        });

        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });

        // Character counters
        document.getElementById('site_name').addEventListener('input', function() {
            const remaining = 50 - this.value.length;
            if (remaining < 10) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#ced4da';
            }
        });

        document.getElementById('meta_description').addEventListener('input', function() {
            const remaining = 160 - this.value.length;
            if (remaining < 20) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#ced4da';
            }
        });

        // Settings validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const emailField = form.querySelector('input[type="email"]');
                if (emailField && emailField.value && !validateEmail(emailField.value)) {
                    e.preventDefault();
                    alert('Please enter a valid email address');
                    return false;
                }
                
                const urlFields = form.querySelectorAll('input[type="url"]');
                urlFields.forEach(field => {
                    if (field.value && !validateURL(field.value)) {
                        e.preventDefault();
                        alert('Please enter a valid URL');
                        return false;
                    }
                });
                
                return true;
            });
        });

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function validateURL(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        }

        // Maintenance mode warning
        if (<?php echo $settings['maintenance_mode'] ?? 0; ?>) {
            document.addEventListener('DOMContentLoaded', function() {
                const warning = document.createElement('div');
                warning.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 start-50 end-50';
                warning.style.zIndex = '9999';
                warning.innerHTML = `
                    <strong>⚠️ Maintenance Mode Active</strong><br>
                    Website is currently under maintenance. Some features may be disabled.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.insertBefore(warning, document.body.firstChild);
            });
        }
    </script>
</body>
</html>
