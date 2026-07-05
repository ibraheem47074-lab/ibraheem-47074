<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get current user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    redirect('login.php');
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_preferences') {
        // Update user preferences
        $theme = clean_input($_POST['theme'] ?? 'light');
        $language = clean_input($_POST['language'] ?? 'en');
        $timezone = clean_input($_POST['timezone'] ?? 'UTC');
        $date_format = clean_input($_POST['date_format'] ?? 'Y-m-d');
        $time_format = clean_input($_POST['time_format'] ?? '24');
        
        $update_query = "UPDATE users SET theme = ?, language = ?, timezone = ?, date_format = ?, time_format = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'sssssi', $theme, $language, $timezone, $date_format, $time_format, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success = "Preferences updated successfully!";
        } else {
            $error = "Error updating preferences: " . mysqli_error($conn);
        }
    } elseif ($action === 'update_notifications') {
        // Update notification settings
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
        $newsletter_subscription = isset($_POST['newsletter_subscription']) ? 1 : 0;
        $comment_notifications = isset($_POST['comment_notifications']) ? 1 : 0;
        $like_notifications = isset($_POST['like_notifications']) ? 1 : 0;
        $follow_notifications = isset($_POST['follow_notifications']) ? 1 : 0;
        
        $update_query = "UPDATE users SET 
                         email_notifications = ?, 
                         push_notifications = ?, 
                         newsletter_subscription = ?, 
                         comment_notifications = ?, 
                         like_notifications = ?, 
                         follow_notifications = ? 
                         WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'iiiiiii', $email_notifications, $push_notifications, 
                               $newsletter_subscription, $comment_notifications, $like_notifications, 
                               $follow_notifications, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success = "Notification settings updated successfully!";
        } else {
            $error = "Error updating notification settings: " . mysqli_error($conn);
        }
    } elseif ($action === 'update_privacy') {
        // Update privacy settings
        $profile_public = isset($_POST['profile_public']) ? 1 : 0;
        $show_activity = isset($_POST['show_activity']) ? 1 : 0;
        $show_email = isset($_POST['show_email']) ? 1 : 0;
        $allow_messages = isset($_POST['allow_messages']) ? 1 : 0;
        $show_bookmarks = isset($_POST['show_bookmarks']) ? 1 : 0;
        $show_reading_history = isset($_POST['show_reading_history']) ? 1 : 0;
        
        $update_query = "UPDATE users SET 
                         profile_public = ?, 
                         show_activity = ?, 
                         show_email = ?, 
                         allow_messages = ?, 
                         show_bookmarks = ?, 
                         show_reading_history = ? 
                         WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'iiiiiii', $profile_public, $show_activity, 
                               $show_email, $allow_messages, $show_bookmarks, $show_reading_history, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success = "Privacy settings updated successfully!";
        } else {
            $error = "Error updating privacy settings: " . mysqli_error($conn);
        }
    } elseif ($action === 'update_content') {
        // Update content preferences
        $preferred_categories = implode(',', $_POST['preferred_categories'] ?? []);
        $content_language = clean_input($_POST['content_language'] ?? 'en');
        $auto_play_videos = isset($_POST['auto_play_videos']) ? 1 : 0;
        $show_mature_content = isset($_POST['show_mature_content']) ? 1 : 0;
        $content_filter_level = clean_input($_POST['content_filter_level'] ?? 'medium');
        
        $update_query = "UPDATE users SET 
                         preferred_categories = ?, 
                         content_language = ?, 
                         auto_play_videos = ?, 
                         show_mature_content = ?, 
                         content_filter_level = ? 
                         WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'ssissi', $preferred_categories, $content_language, 
                               $auto_play_videos, $show_mature_content, $content_filter_level, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success = "Content preferences updated successfully!";
        } else {
            $error = "Error updating content preferences: " . mysqli_error($conn);
        }
    } elseif ($action === 'delete_account') {
        $password = $_POST['delete_password'] ?? '';
        $confirmation = $_POST['delete_confirmation'] ?? '';
        
        if (empty($password)) {
            $error = "Password is required to delete account";
        } elseif (!password_verify($password, $user['password'])) {
            $error = "Incorrect password";
        } elseif ($confirmation !== 'DELETE') {
            $error = "Please type DELETE exactly to confirm";
        } else {
            // Start transaction
            mysqli_begin_transaction($conn);
            
            try {
                // Delete user's bookmarks
                mysqli_query($conn, "DELETE FROM bookmarks WHERE user_id = $user_id");
                
                // Delete user's comments
                mysqli_query($conn, "DELETE FROM comments WHERE user_id = $user_id");
                
                // Delete user's bookmark folders
                mysqli_query($conn, "DELETE FROM bookmark_folders WHERE user_id = $user_id");
                
                // Delete user's notifications
                mysqli_query($conn, "DELETE FROM notifications WHERE user_id = $user_id");
                
                // Delete user account
                mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
                
                mysqli_commit($conn);
                
                // Destroy session and redirect
                session_destroy();
                redirect('index.php?message=account_deleted');
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Error deleting account: " . $e->getMessage();
            }
        }
    }
    
    // Refresh user data after update
    if (empty($error)) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
    }
}

// Get categories for content preferences
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);

// Get available timezones
$timezones = [
    'UTC' => 'UTC (Coordinated Universal Time)',
    'America/New_York' => 'Eastern Time (ET)',
    'America/Chicago' => 'Central Time (CT)',
    'America/Denver' => 'Mountain Time (MT)',
    'America/Los_Angeles' => 'Pacific Time (PT)',
    'Europe/London' => 'London (GMT/BST)',
    'Europe/Paris' => 'Paris (CET/CEST)',
    'Asia/Karachi' => 'Karachi (PKT)',
    'Asia/Dubai' => 'Dubai (GST)',
    'Asia/Tokyo' => 'Tokyo (JST)',
    'Asia/Shanghai' => 'Shanghai (CST)',
    'Australia/Sydney' => 'Sydney (AEST/AEDT)'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .settings-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
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
            border-radius: 10px 10px 0 0;
        }
        .settings-tabs .nav-link:hover {
            color: #28a745;
            border-bottom-color: #28a745;
            background-color: #f8f9fa;
        }
        .settings-tabs .nav-link.active {
            color: #28a745;
            background-color: #f8f9fa;
            border-bottom-color: #28a745;
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
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
        }
        .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
        .criteria-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }
        .criteria-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
        }
        .danger-zone {
            background: #fff5f5;
            border: 2px solid #feb2b2;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }
        .danger-zone h5 {
            color: #c53030;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <!-- Header -->
        <div class="settings-header text-center">
            <h1><i class="fas fa-cog me-3"></i>Settings</h1>
            <p class="mb-0">Manage your account preferences and criteria</p>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Settings Tabs -->
        <div class="setting-card">
            <ul class="nav nav-tabs settings-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#preferences" role="tab">
                        <i class="fas fa-palette me-2"></i>Preferences
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#notifications" role="tab">
                        <i class="fas fa-bell me-2"></i>Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#privacy" role="tab">
                        <i class="fas fa-shield-alt me-2"></i>Privacy
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#content" role="tab">
                        <i class="fas fa-newspaper me-2"></i>Content
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#criteria" role="tab">
                        <i class="fas fa-list-check me-2"></i>Criteria
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#events-criteria" role="tab">
                        <i class="fas fa-calendar-alt me-2"></i>Events
                    </a>
                </li>
            </ul>

            <div class="tab-content mt-4">
                <!-- Preferences Tab -->
                <div class="tab-pane active" id="preferences">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_preferences">
                        
                        <h5 class="mb-4">
                            <i class="fas fa-palette me-2"></i>Appearance & Display
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Theme</label>
                                    <select class="form-select" name="theme">
                                        <option value="light" <?php echo ($user['theme'] ?? 'light') == 'light' ? 'selected' : ''; ?>>
                                            <i class="fas fa-sun me-2"></i>Light
                                        </option>
                                        <option value="dark" <?php echo ($user['theme'] ?? 'light') == 'dark' ? 'selected' : ''; ?>>
                                            <i class="fas fa-moon me-2"></i>Dark
                                        </option>
                                        <option value="auto" <?php echo ($user['theme'] ?? 'light') == 'auto' ? 'selected' : ''; ?>>
                                            <i class="fas fa-adjust me-2"></i>Auto (System)
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Language</label>
                                    <select class="form-select" name="language">
                                        <option value="en" <?php echo ($user['language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="ur" <?php echo ($user['language'] ?? 'en') == 'ur' ? 'selected' : ''; ?>>اردو</option>
                                        <option value="hi" <?php echo ($user['language'] ?? 'en') == 'hi' ? 'selected' : ''; ?>>हिन्दी</option>
                                        <option value="ps" <?php echo ($user['language'] ?? 'en') == 'ps' ? 'selected' : ''; ?>>پښتو</option>
                                        <option value="zh" <?php echo ($user['language'] ?? 'en') == 'zh' ? 'selected' : ''; ?>>中文</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Timezone</label>
                                    <select class="form-select" name="timezone">
                                        <?php foreach ($timezones as $tz => $label): ?>
                                            <option value="<?php echo $tz; ?>" 
                                                    <?php echo ($user['timezone'] ?? 'UTC') == $tz ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Date Format</label>
                                    <select class="form-select" name="date_format">
                                        <option value="Y-m-d" <?php echo ($user['date_format'] ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : ''; ?>>2024-01-15</option>
                                        <option value="m/d/Y" <?php echo ($user['date_format'] ?? 'Y-m-d') == 'm/d/Y' ? 'selected' : ''; ?>>01/15/2024</option>
                                        <option value="d/m/Y" <?php echo ($user['date_format'] ?? 'Y-m-d') == 'd/m/Y' ? 'selected' : ''; ?>>15/01/2024</option>
                                        <option value="M j, Y" <?php echo ($user['date_format'] ?? 'Y-m-d') == 'M j, Y' ? 'selected' : ''; ?>>Jan 15, 2024</option>
                                        <option value="F j, Y" <?php echo ($user['date_format'] ?? 'Y-m-d') == 'F j, Y' ? 'selected' : ''; ?>>January 15, 2024</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Time Format</label>
                                    <select class="form-select" name="time_format">
                                        <option value="24" <?php echo ($user['time_format'] ?? '24') == '24' ? 'selected' : ''; ?>>24-hour (14:30)</option>
                                        <option value="12" <?php echo ($user['time_format'] ?? '24') == '12' ? 'selected' : ''; ?>>12-hour (2:30 PM)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Preferences
                        </button>
                    </form>
                </div>

                <!-- Notifications Tab -->
                <div class="tab-pane" id="notifications">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_notifications">
                        
                        <h5 class="mb-4">
                            <i class="fas fa-bell me-2"></i>Notification Preferences
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Email Notifications</h6>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="email_notifications" 
                                           <?php echo ($user['email_notifications'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        General Email Notifications
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="newsletter_subscription" 
                                           <?php echo ($user['newsletter_subscription'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Newsletter Subscription
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="comment_notifications" 
                                           <?php echo ($user['comment_notifications'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Comment Notifications
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="like_notifications" 
                                           <?php echo ($user['like_notifications'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Like Notifications
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="follow_notifications" 
                                           <?php echo ($user['follow_notifications'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Follow Notifications
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-3">Push Notifications</h6>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="push_notifications" 
                                           <?php echo ($user['push_notifications'] ?? false) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Enable Push Notifications
                                    </label>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Push notifications work when you're using our website or mobile app.
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Notification Settings
                        </button>
                    </form>
                </div>

                <!-- Privacy Tab -->
                <div class="tab-pane" id="privacy">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_privacy">
                        
                        <h5 class="mb-4">
                            <i class="fas fa-shield-alt me-2"></i>Privacy Settings
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Profile Visibility</h6>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="profile_public" 
                                           <?php echo ($user['profile_public'] ?? false) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Public Profile
                                    </label>
                                    <small class="form-text text-muted">Anyone can view your profile</small>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_activity" 
                                           <?php echo ($user['show_activity'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Show Activity Status
                                    </label>
                                    <small class="form-text text-muted">Show when you're online</small>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_email" 
                                           <?php echo ($user['show_email'] ?? false) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Show Email Address
                                    </label>
                                    <small class="form-text text-muted">Display email on public profile</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-3">Content Sharing</h6>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="allow_messages" 
                                           <?php echo ($user['allow_messages'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Allow Messages
                                    </label>
                                    <small class="form-text text-muted">Others can send you messages</small>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_bookmarks" 
                                           <?php echo ($user['show_bookmarks'] ?? false) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Show Bookmarks
                                    </label>
                                    <small class="form-text text-muted">Others can see your bookmarks</small>
                                </div>
                                
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="show_reading_history" 
                                           <?php echo ($user['show_reading_history'] ?? false) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Show Reading History
                                    </label>
                                    <small class="form-text text-muted">Others can see your reading activity</small>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Privacy Settings
                        </button>
                    </form>
                </div>

                <!-- Content Tab -->
                <div class="tab-pane" id="content">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_content">
                        
                        <h5 class="mb-4">
                            <i class="fas fa-newspaper me-2"></i>Content Preferences
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Preferred Categories</label>
                                    <select class="form-select" name="preferred_categories[]" multiple>
                                        <?php 
                                        $preferred_categories = explode(',', $user['preferred_categories'] ?? '');
                                        mysqli_data_seek($categories_result, 0);
                                        while ($category = mysqli_fetch_assoc($categories_result)): 
                                        ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo in_array($category['id'], $preferred_categories) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <small class="form-text text-muted">Select your preferred news categories</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Content Language</label>
                                    <select class="form-select" name="content_language">
                                        <option value="en" <?php echo ($user['content_language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="ur" <?php echo ($user['content_language'] ?? 'en') == 'ur' ? 'selected' : ''; ?>>اردو</option>
                                        <option value="hi" <?php echo ($user['content_language'] ?? 'en') == 'hi' ? 'selected' : ''; ?>>हिन्दी</option>
                                        <option value="ps" <?php echo ($user['content_language'] ?? 'en') == 'ps' ? 'selected' : ''; ?>>پښتو</option>
                                        <option value="zh" <?php echo ($user['content_language'] ?? 'en') == 'zh' ? 'selected' : ''; ?>>中文</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-3">Content Filters</h6>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="auto_play_videos" 
                                           <?php echo ($user['auto_play_videos'] ?? false) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Auto-play Videos
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="show_mature_content" 
                                           <?php echo ($user['show_mature_content'] ?? false) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Show Mature Content
                                    </label>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Content Filter Level</label>
                                    <select class="form-select" name="content_filter_level">
                                        <option value="strict" <?php echo ($user['content_filter_level'] ?? 'medium') == 'strict' ? 'selected' : ''; ?>>Strict</option>
                                        <option value="medium" <?php echo ($user['content_filter_level'] ?? 'medium') == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="lenient" <?php echo ($user['content_filter_level'] ?? 'medium') == 'lenient' ? 'selected' : ''; ?>>Lenient</option>
                                        <option value="none" <?php echo ($user['content_filter_level'] ?? 'medium') == 'none' ? 'selected' : ''; ?>>None</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Content Preferences
                        </button>
                    </form>
                </div>

                <!-- Criteria Tab -->
                <div class="tab-pane" id="criteria">
                    <div class="criteria-section">
                        <h5 class="mb-4">
                            <i class="fas fa-list-check me-2"></i>Advanced Criteria Settings
                        </h5>
                        
                        <div class="criteria-item">
                            <h6><i class="fas fa-brain me-2"></i>Smart Recommendations</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="smart_recommendations">
                                <label class="form-check-label" for="smart_recommendations">
                                    Enable AI-powered article recommendations
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="learning_algorithm">
                                <label class="form-check-label" for="learning_algorithm">
                                    Use learning algorithm for better suggestions
                                </label>
                            </div>
                        </div>
                        
                        <div class="criteria-item">
                            <h6><i class="fas fa-chart-line me-2"></i>Reading Analytics</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="track_reading_time">
                                <label class="form-check-label" for="track_reading_time">
                                    Track reading time and speed
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="generate_reports">
                                <label class="form-check-label" for="generate_reports">
                                    Generate weekly reading reports
                                </label>
                            </div>
                        </div>
                        
                        <div class="criteria-item">
                            <h6><i class="fas fa-filter me-2"></i>Advanced Filtering</h6>
                            <div class="mb-3">
                                <label class="form-label">Minimum Article Quality Score</label>
                                <input type="range" class="form-range" min="0" max="100" value="70" id="quality_score">
                                <small class="form-text text-muted">Filter out articles below this quality score</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="filter_duplicates">
                                <label class="form-check-label" for="filter_duplicates">
                                    Automatically filter duplicate articles
                                </label>
                            </div>
                        </div>
                        
                        <div class="criteria-item">
                            <h6><i class="fas fa-clock me-2"></i>Reading Schedule</h6>
                            <div class="mb-3">
                                <label class="form-label">Preferred Reading Times</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="time" class="form-control mb-2" id="morning_time" value="08:00">
                                        <small class="form-text">Morning</small>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="time" class="form-control mb-2" id="evening_time" value="18:00">
                                        <small class="form-text">Evening</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="reading_reminders">
                                <label class="form-check-label" for="reading_reminders">
                                    Send reading reminders at preferred times
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" onclick="saveAdvancedCriteria()">
                                <i class="fas fa-save me-2"></i>Save Advanced Criteria
                            </button>
                            <button class="btn btn-outline-secondary" onclick="resetAdvancedCriteria()">
                                <i class="fas fa-undo me-2"></i>Reset to Default
                            </button>
                            <button class="btn btn-outline-info" onclick="exportCriteria()">
                                <i class="fas fa-download me-2"></i>Export Criteria
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Events Criteria Tab -->
                <div class="tab-pane" id="events-criteria">
                    <div class="criteria-section">
                        <h5 class="mb-4">
                            <i class="fas fa-calendar-alt me-2"></i>Upcoming Events Criteria
                        </h5>
                        
                        <div class="criteria-item">
                            <h6><i class="fas fa-filter me-2"></i>Event Preferences</h6>
                            <div class="mb-3">
                                <label class="form-label">Preferred Categories</label>
                                <input type="text" class="form-control" id="preferred_categories" placeholder="e.g., technology, sports, politics">
                                <small class="form-text text-muted">Comma-separated categories you're interested in</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Preferred Event Types</label>
                                <select class="form-select" id="preferred_types" multiple>
                                    <option value="conference">Conference</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="webinar">Webinar</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="social">Social</option>
                                    <option value="sports">Sports</option>
                                    <option value="political">Political</option>
                                    <option value="other">Other</option>
                                </select>
                                <small class="form-text text-muted">Select types of events you prefer</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Minimum Priority</label>
                                <select class="form-select" id="min_priority">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="criteria-item">
                            <h6><i class="fas fa-bell me-2"></i>Event Notifications</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Notify me (days before)</label>
                                        <input type="number" class="form-control" id="notification_advance_days" value="7" min="0" max="30">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Notify me (hours before)</label>
                                        <input type="number" class="form-control" id="notification_advance_hours" value="2" min="0" max="24">
                                    </div>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="email_notifications" checked>
                                <label class="form-check-label" for="email_notifications">
                                    Email notifications for events
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="push_notifications" checked>
                                <label class="form-check-label" for="push_notifications">
                                    Push notifications for events
                                </label>
                            </div>
                        </div>
                        
                        <div class="criteria-item">
                            <h6><i class="fas fa-eye me-2"></i>Display Options</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="show_past_events">
                                <label class="form-check-label" for="show_past_events">
                                    Show past events
                                </label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="show_cancelled_events">
                                <label class="form-check-label" for="show_cancelled_events">
                                    Show cancelled events
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Maximum events per day</label>
                                <input type="number" class="form-control" id="max_events_per_day" value="10" min="1" max="50">
                            </div>
                        </div>
                        
                        <div class="criteria-item">
                            <h6><i class="fas fa-cog me-2"></i>Advanced Options</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="auto_register">
                                <label class="form-check-label" for="auto_register">
                                    Auto-register for matching events
                                </label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="only_free_events">
                                <label class="form-check-label" for="only_free_events">
                                    Only show free events
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location Filter</label>
                                <input type="text" class="form-control" id="location_filter" placeholder="e.g., Karachi, Lahore, Islamabad">
                                <small class="form-text text-muted">Preferred event locations</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Organizer Filter</label>
                                <input type="text" class="form-control" id="organizer_filter" placeholder="e.g., Tech Association, Sports Federation">
                                <small class="form-text text-muted">Preferred organizers (comma-separated)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tags Filter</label>
                                <input type="text" class="form-control" id="tags_filter" placeholder="e.g., technology, innovation, conference">
                                <small class="form-text text-muted">Preferred tags (comma-separated)</small>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" onclick="saveEventsCriteria()">
                                <i class="fas fa-save me-2"></i>Save Events Criteria
                            </button>
                            <button class="btn btn-outline-secondary" onclick="resetEventsCriteria()">
                                <i class="fas fa-undo me-2"></i>Reset to Default
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="danger-zone">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h5>
            <p class="text-muted mb-4">These actions are irreversible. Please be careful.</p>
            
            <div class="row">
                <div class="col-md-6">
                    <button class="btn btn-outline-warning" onclick="exportUserData()">
                        <i class="fas fa-download me-2"></i>Export All My Data
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="fas fa-trash me-2"></i>Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete_account">
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone. All your data including bookmarks, comments, and profile information will be permanently deleted.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Enter your password to confirm</label>
                            <input type="password" class="form-control" name="delete_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Type "DELETE" to confirm</label>
                            <input type="text" class="form-control" name="delete_confirmation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete My Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Save advanced criteria
        function saveAdvancedCriteria() {
            const criteria = {
                smart_recommendations: document.getElementById('smart_recommendations').checked,
                learning_algorithm: document.getElementById('learning_algorithm').checked,
                track_reading_time: document.getElementById('track_reading_time').checked,
                generate_reports: document.getElementById('generate_reports').checked,
                quality_score: document.getElementById('quality_score').value,
                filter_duplicates: document.getElementById('filter_duplicates').checked,
                morning_time: document.getElementById('morning_time').value,
                evening_time: document.getElementById('evening_time').value,
                reading_reminders: document.getElementById('reading_reminders').checked
            };
            
            fetch('api/update_advanced_criteria.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(criteria)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Advanced criteria updated successfully!');
                } else {
                    alert('Error updating criteria: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating criteria. Please try again.');
            });
        }
        
        // Reset advanced criteria
        function resetAdvancedCriteria() {
            if (confirm('Are you sure you want to reset all advanced criteria to default values?')) {
                document.getElementById('smart_recommendations').checked = true;
                document.getElementById('learning_algorithm').checked = true;
                document.getElementById('track_reading_time').checked = false;
                document.getElementById('generate_reports').checked = true;
                document.getElementById('quality_score').value = 70;
                document.getElementById('filter_duplicates').checked = true;
                document.getElementById('morning_time').value = '08:00';
                document.getElementById('evening_time').value = '18:00';
                document.getElementById('reading_reminders').checked = false;
            }
        }
        
        // Export criteria
        function exportCriteria() {
            window.open('api/export_criteria.php', '_blank');
        }
        
        // Export user data
        function exportUserData() {
            window.open('api/export_user_data.php', '_blank');
        }
        
        // Save events criteria
        function saveEventsCriteria() {
            const selectedTypes = Array.from(document.getElementById('preferred_types').selectedOptions)
                .map(option => option.value);
            
            const criteria = {
                preferred_categories: document.getElementById('preferred_categories').value,
                preferred_types: selectedTypes.join(','),
                min_priority: document.getElementById('min_priority').value,
                notification_advance_days: parseInt(document.getElementById('notification_advance_days').value),
                notification_advance_hours: parseInt(document.getElementById('notification_advance_hours').value),
                email_notifications: document.getElementById('email_notifications').checked,
                push_notifications: document.getElementById('push_notifications').checked,
                show_past_events: document.getElementById('show_past_events').checked,
                show_cancelled_events: document.getElementById('show_cancelled_events').checked,
                max_events_per_day: parseInt(document.getElementById('max_events_per_day').value),
                auto_register: document.getElementById('auto_register').checked,
                only_free_events: document.getElementById('only_free_events').checked,
                location_filter: document.getElementById('location_filter').value,
                organizer_filter: document.getElementById('organizer_filter').value,
                tags_filter: document.getElementById('tags_filter').value
            };
            
            fetch('api/update_events_criteria.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(criteria)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Events criteria updated successfully!');
                } else {
                    alert('Error updating events criteria: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating events criteria. Please try again.');
            });
        }
        
        // Reset events criteria
        function resetEventsCriteria() {
            if (confirm('Are you sure you want to reset all events criteria to default values?')) {
                document.getElementById('preferred_categories').value = '';
                document.getElementById('preferred_types').selectedIndex = -1;
                document.getElementById('min_priority').value = 'low';
                document.getElementById('notification_advance_days').value = 7;
                document.getElementById('notification_advance_hours').value = 2;
                document.getElementById('email_notifications').checked = true;
                document.getElementById('push_notifications').checked = true;
                document.getElementById('show_past_events').checked = false;
                document.getElementById('show_cancelled_events').checked = false;
                document.getElementById('max_events_per_day').value = 10;
                document.getElementById('auto_register').checked = false;
                document.getElementById('only_free_events').checked = false;
                document.getElementById('location_filter').value = '';
                document.getElementById('organizer_filter').value = '';
                document.getElementById('tags_filter').value = '';
            }
        }
        
        // Load events criteria on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch('api/update_events_criteria.php', {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.criteria) {
                    const criteria = data.criteria;
                    document.getElementById('preferred_categories').value = criteria.preferred_categories || '';
                    document.getElementById('min_priority').value = criteria.min_priority || 'low';
                    document.getElementById('notification_advance_days').value = criteria.notification_advance_days || 7;
                    document.getElementById('notification_advance_hours').value = criteria.notification_advance_hours || 2;
                    document.getElementById('email_notifications').checked = criteria.email_notifications === 1;
                    document.getElementById('push_notifications').checked = criteria.push_notifications === 1;
                    document.getElementById('show_past_events').checked = criteria.show_past_events === 1;
                    document.getElementById('show_cancelled_events').checked = criteria.show_cancelled_events === 1;
                    document.getElementById('max_events_per_day').value = criteria.max_events_per_day || 10;
                    document.getElementById('auto_register').checked = criteria.auto_register === 1;
                    document.getElementById('only_free_events').checked = criteria.only_free_events === 1;
                    document.getElementById('location_filter').value = criteria.location_filter || '';
                    document.getElementById('organizer_filter').value = criteria.organizer_filter || '';
                    document.getElementById('tags_filter').value = criteria.tags_filter || '';
                    
                    // Set preferred types
                    if (criteria.preferred_types) {
                        const types = criteria.preferred_types.split(',');
                        const select = document.getElementById('preferred_types');
                        Array.from(select.options).forEach(option => {
                            option.selected = types.includes(option.value);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error loading events criteria:', error);
            });
        });
    </script>
</body>
</html>
