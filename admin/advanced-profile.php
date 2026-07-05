<?php
require_once '../config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Safe htmlspecialchars function to handle null values
function safe_htmlspecialchars($value) {
    return htmlspecialchars($value ?? '');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user information with advanced details
function getAdvancedUserProfile($user_id) {
    global $conn;
    
    $query = "SELECT u.*, 
                     (SELECT AVG(rating) FROM user_ratings WHERE rated_user_id = u.id) as average_rating,
                     (SELECT COUNT(*) FROM user_ratings WHERE rated_user_id = u.id) as total_ratings,
                     (SELECT COUNT(*) FROM user_achievements WHERE user_id = u.id) as total_achievements
              FROM users u 
              WHERE u.id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

// Get user permissions
function getUserPermissions($user_id) {
    global $conn;
    
    $query = "SELECT permission FROM user_permissions WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $permissions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $permissions[] = $row['permission'];
    }
    
    return $permissions;
}

// Get user achievements
function getUserAchievements($user_id) {
    global $conn;
    
    $query = "SELECT * FROM user_achievements WHERE user_id = ? ORDER BY earned_at DESC LIMIT 10";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get user work schedule
function getUserWorkSchedule($user_id) {
    global $conn;
    
    $query = "SELECT * FROM user_work_schedule WHERE user_id = ? ORDER BY FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get recent activity
function getUserRecentActivity($user_id, $limit = 10) {
    global $conn;
    
    $query = "SELECT * FROM user_activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_basic_profile'])) {
        $name = clean_input($_POST['name'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        $bio = clean_input($_POST['bio'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $department = clean_input($_POST['department'] ?? '');
        $specialization = clean_input($_POST['specialization'] ?? '');
        $experience_level = clean_input($_POST['experience_level'] ?? '');
        $skills = clean_input($_POST['skills'] ?? '');
        $social_links = clean_input($_POST['social_links'] ?? '');
        $preferred_language = clean_input($_POST['preferred_language'] ?? '');
        $timezone = clean_input($_POST['timezone'] ?? '');
        $working_hours = clean_input($_POST['working_hours'] ?? '');
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format!";
        } else {
            // Check if email is already taken by another user
            $email_check = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = mysqli_prepare($conn, $email_check);
            mysqli_stmt_bind_param($stmt, 'si', $email, $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $error = "Email is already taken by another user!";
            } else {
                // Update profile
                $update_query = "UPDATE users SET name = ?, email = ?, bio = ?, phone = ?, 
                                department = ?, specialization = ?, experience_level = ?, 
                                skills = ?, social_links = ?, preferred_language = ?, 
                                timezone = ?, working_hours = ? WHERE id = ?";
                
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, 'ssssssssssssi', $name, $email, $bio, $phone, 
                                    $department, $specialization, $experience_level, $skills, 
                                    $social_links, $preferred_language, $timezone, $working_hours, $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Profile updated successfully!";
                    // Update session
                    $_SESSION['user_name'] = $name;
                    
                    // Log activity
                    logUserActivity($user_id, 'profile_updated', 'Updated basic profile information');
                } else {
                    $error = "Failed to update profile!";
                }
            }
        }
    }
    
    if (isset($_POST['update_notification_preferences'])) {
        $notification_preferences = json_encode($_POST['notifications'] ?? []);
        
        $update_query = "UPDATE users SET notification_preferences = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $notification_preferences, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Notification preferences updated successfully!";
            logUserActivity($user_id, 'notification_preferences_updated', 'Updated notification settings');
        } else {
            $error = "Failed to update notification preferences!";
        }
    }
    
    if (isset($_POST['update_work_schedule'])) {
        // Delete existing schedule
        $delete_query = "DELETE FROM user_work_schedule WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        
        // Insert new schedule
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            $is_available = isset($_POST['schedule'][$day]['available']) ? 1 : 0;
            $start_time = !empty($_POST['schedule'][$day]['start']) ? $_POST['schedule'][$day]['start'] : null;
            $end_time = !empty($_POST['schedule'][$day]['end']) ? $_POST['schedule'][$day]['end'] : null;
            $notes = clean_input($_POST['schedule'][$day]['notes'] ?? '');
            
            if ($is_available) {
                $insert_query = "INSERT INTO user_work_schedule (user_id, day_of_week, start_time, end_time, is_available, notes) 
                                VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($stmt, 'isssis', $user_id, $day, $start_time, $end_time, $is_available, $notes);
                mysqli_stmt_execute($stmt);
            }
        }
        
        $success = "Work schedule updated successfully!";
        logUserActivity($user_id, 'work_schedule_updated', 'Updated work schedule');
    }
}

// Log user activity
function logUserActivity($user_id, $action, $details = null) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $query = "INSERT INTO user_activity_log (user_id, action, details, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'issss', $user_id, $action, $details, $ip_address, $user_agent);
    mysqli_stmt_execute($stmt);
}

// Get user data
$user = getAdvancedUserProfile($user_id);
$permissions = getUserPermissions($user_id);
$achievements = getUserAchievements($user_id);
$schedule = getUserWorkSchedule($user_id);
$recent_activity = getUserRecentActivity($user_id);

// Parse notification preferences
$notification_preferences = json_decode($user['notification_preferences'] ?? '{}', true);

// Get available departments and experience levels
$departments = ['editorial', 'reporting', 'technical', 'management', 'marketing', 'multimedia'];
$experience_levels = ['junior', 'intermediate', 'senior', 'expert', 'lead'];
$languages = ['en' => 'English', 'ur' => 'Urdu', 'ar' => 'Arabic'];
$timezones = [
    'Asia/Karachi' => 'Pakistan Time (PKT)',
    'Asia/Dubai' => 'Gulf Standard Time',
    'Asia/Tehran' => 'Iran Standard Time',
    'UTC' => 'Coordinated Universal Time'
];

// Update last login
$update_login = "UPDATE users SET last_login = NOW(), login_count = login_count + 1 WHERE id = ?";
$stmt = mysqli_prepare($conn, $update_login);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Profile - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .verification-badge {
            background: #28a745;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
        }
        .skill-tag {
            background: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.85rem;
            margin: 0.125rem;
            display: inline-block;
        }
        .achievement-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .rating-stars {
            color: #ffc107;
        }
        .activity-item {
            border-left: 3px solid #007bff;
            padding-left: 1rem;
            margin-bottom: 1rem;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .profile-stats {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>

    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <img src="<?php echo !empty($user['image']) ? '../uploads/avatars/' . $user['image'] : 'https://via.placeholder.com/120'; ?>" 
                         alt="Profile" class="profile-avatar">
                </div>
                <div class="col-md-6">
                    <h2><?php echo safe_htmlspecialchars($user['name']); ?></h2>
                    <p class="mb-2"><?php echo safe_htmlspecialchars($user['email']); ?></p>
                    <div class="mb-2">
                        <span class="badge bg-primary"><?php echo ucfirst($user['role'] ?? ''); ?></span>
                        <span class="badge bg-info"><?php echo ucfirst($user['department'] ?? ''); ?></span>
                        <span class="badge bg-warning"><?php echo ucfirst($user['experience_level'] ?? ''); ?></span>
                        <?php if ($user['verification_status'] === 'verified'): ?>
                            <span class="verification-badge"><i class="fas fa-check-circle"></i> Verified</span>
                        <?php elseif ($user['verification_status'] === 'premium'): ?>
                            <span class="verification-badge" style="background: #ffc107;"><i class="fas fa-crown"></i> Premium</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($user['bio'])): ?>
                        <p><?php echo safe_htmlspecialchars($user['bio']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-end">
                    <div class="rating-stars">
                        <?php 
                        $rating = round($user['average_rating'] ?? 0, 1);
                        for ($i = 1; $i <= 5; $i++):
                            if ($i <= $rating):
                                echo '<i class="fas fa-star"></i>';
                            else:
                                echo '<i class="far fa-star"></i>';
                            endif;
                        endfor;
                        ?>
                        <span class="ms-2"><?php echo $rating; ?> (<?php echo $user['total_ratings'] ?? 0; ?> reviews)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Profile Statistics -->
        <div class="profile-stats">
            <div class="row">
                <div class="col-md-2 col-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $user['articles_published']; ?></div>
                        <div class="stat-label">Articles</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $user['profile_views']; ?></div>
                        <div class="stat-label">Profile Views</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $user['total_achievements']; ?></div>
                        <div class="stat-label">Achievements</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $user['login_count']; ?></div>
                        <div class="stat-label">Logins</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($permissions); ?></div>
                        <div class="stat-label">Permissions</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $user['total_ratings']; ?></div>
                        <div class="stat-label">Ratings</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Tabs -->
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                    <i class="fas fa-user"></i> Basic Info
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="professional-tab" data-bs-toggle="tab" data-bs-target="#professional" type="button" role="tab">
                    <i class="fas fa-briefcase"></i> Professional
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="achievements-tab" data-bs-toggle="tab" data-bs-target="#achievements" type="button" role="tab">
                    <i class="fas fa-trophy"></i> Achievements
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                    <i class="fas fa-calendar"></i> Schedule
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                    <i class="fas fa-bell"></i> Notifications
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                    <i class="fas fa-history"></i> Activity
                </button>
            </li>
        </ul>

        <div class="tab-content" id="profileTabContent">
            <!-- Basic Information Tab -->
            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-user"></i> Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" value="<?php echo safe_htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo safe_htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" name="phone" class="form-control" value="<?php echo safe_htmlspecialchars($user['phone']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Preferred Language</label>
                                        <select name="preferred_language" class="form-select">
                                            <?php foreach ($languages as $code => $name): ?>
                                                <option value="<?php echo $code; ?>" <?php echo $user['preferred_language'] === $code ? 'selected' : ''; ?>>
                                                    <?php echo $name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" class="form-control" rows="4"><?php echo safe_htmlspecialchars($user['bio']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Social Links</label>
                                <textarea name="social_links" class="form-control" rows="3" placeholder="LinkedIn, Twitter, Facebook links..."><?php echo safe_htmlspecialchars($user['social_links']); ?></textarea>
                            </div>
                            <button type="submit" name="update_basic_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Basic Info
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Professional Information Tab -->
            <div class="tab-pane fade" id="professional" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-briefcase"></i> Professional Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Department</label>
                                        <select name="department" class="form-select" required>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo $dept; ?>" <?php echo $user['department'] === $dept ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($dept); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Experience Level</label>
                                        <select name="experience_level" class="form-select" required>
                                            <?php foreach ($experience_levels as $level): ?>
                                                <option value="<?php echo $level; ?>" <?php echo $user['experience_level'] === $level ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($level); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Specialization</label>
                                        <input type="text" name="specialization" class="form-control" value="<?php echo safe_htmlspecialchars($user['specialization']); ?>" placeholder="e.g., Political News, Sports, Technology">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Skills</label>
                                        <textarea name="skills" class="form-control" rows="3" placeholder="e.g., Writing, Editing, Photography, Video Production"><?php echo safe_htmlspecialchars($user['skills']); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Working Hours</label>
                                        <input type="text" name="working_hours" class="form-control" value="<?php echo safe_htmlspecialchars($user['working_hours']); ?>" placeholder="e.g., 9:00 AM - 5:00 PM">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Timezone</label>
                                <select name="timezone" class="form-select">
                                    <?php foreach ($timezones as $tz => $name): ?>
                                        <option value="<?php echo $tz; ?>" <?php echo $user['timezone'] === $tz ? 'selected' : ''; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="update_basic_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Professional Info
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Skills Display -->
                <?php if (!empty($user['skills'])): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5><i class="fas fa-tools"></i> Your Skills</h5>
                        </div>
                        <div class="card-body">
                            <?php 
                            $skills_array = explode(',', $user['skills']);
                            foreach ($skills_array as $skill):
                                $skill = trim($skill);
                                if (!empty($skill)):
                            ?>
                                <span class="skill-tag"><?php echo safe_htmlspecialchars($skill); ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Achievements Tab -->
            <div class="tab-pane fade" id="achievements" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-trophy"></i> Your Achievements</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($achievements)): ?>
                            <div class="row">
                                <?php foreach ($achievements as $achievement): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="achievement-card">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="<?php echo $achievement['achievement_icon'] ?? 'fas fa-award'; ?> fa-2x"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1"><?php echo safe_htmlspecialchars($achievement['achievement_title']); ?></h6>
                                                    <small><?php echo safe_htmlspecialchars($achievement['achievement_description']); ?></small>
                                                    <div class="mt-1">
                                                        <small><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($achievement['earned_at'])); ?></small>
                                                        <?php if ($achievement['points'] > 0): ?>
                                                            <span class="badge bg-warning ms-2"><?php echo $achievement['points']; ?> points</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No achievements yet. Keep up the great work!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Work Schedule Tab -->
            <div class="tab-pane fade" id="schedule" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar"></i> Work Schedule</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Available</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                        $day_labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                        
                                        foreach ($days as $index => $day):
                                            $day_schedule = array_filter($schedule, function($s) use ($day) {
                                                return $s['day_of_week'] === $day;
                                            });
                                            $day_schedule = reset($day_schedule);
                                            $is_available = $day_schedule && isset($day_schedule['is_available']) ? $day_schedule['is_available'] : false;
                                        ?>
                                        <tr>
                                            <td><?php echo $day_labels[$index]; ?></td>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="schedule[<?php echo $day; ?>][available]" 
                                                           value="1" <?php echo $is_available ? 'checked' : ''; ?>>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="time" name="schedule[<?php echo $day; ?>][start]" class="form-control" 
                                                       value="<?php echo ($day_schedule && isset($day_schedule['start_time'])) ? $day_schedule['start_time'] : ''; ?>">
                                            </td>
                                            <td>
                                                <input type="time" name="schedule[<?php echo $day; ?>][end]" class="form-control" 
                                                       value="<?php echo ($day_schedule && isset($day_schedule['end_time'])) ? $day_schedule['end_time'] : ''; ?>">
                                            </td>
                                            <td>
                                                <input type="text" name="schedule[<?php echo $day; ?>][notes]" class="form-control" 
                                                       value="<?php echo ($day_schedule && isset($day_schedule['notes'])) ? safe_htmlspecialchars($day_schedule['notes']) : ''; ?>" placeholder="Notes...">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" name="update_work_schedule" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Schedule
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div class="tab-pane fade" id="notifications" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-bell"></i> Notification Preferences</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Email Notifications</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notifications[email_new_article]" 
                                               value="1" <?php echo $notification_preferences['email_new_article'] ?? 'checked'; ?>>
                                        <label class="form-check-label">New article published</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notifications[email_comments]" 
                                               value="1" <?php echo $notification_preferences['email_comments'] ?? 'checked'; ?>>
                                        <label class="form-check-label">New comments on articles</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notifications[email_mentions]" 
                                               value="1" <?php echo $notification_preferences['email_mentions'] ?? 'checked'; ?>>
                                        <label class="form-check-label">Mentions in articles</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notifications[email_system]" 
                                               value="1" <?php echo $notification_preferences['email_system'] ?? 'checked'; ?>>
                                        <label class="form-check-label">System notifications</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>In-App Notifications</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notifications[app_breaking_news]" 
                                               value="1" <?php echo $notification_preferences['app_breaking_news'] ?? 'checked'; ?>>
                                        <label class="form-check-label">Breaking news alerts</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notifications[app_deadlines]" 
                                               value="1" <?php echo $notification_preferences['app_deadlines'] ?? 'checked'; ?>>
                                        <label class="form-check-label">Deadline reminders</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notifications[app_assignments]" 
                                               value="1" <?php echo $notification_preferences['app_assignments'] ?? 'checked'; ?>>
                                        <label class="form-check-label">New assignments</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="notifications[app_reviews]" 
                                               value="1" <?php echo $notification_preferences['app_reviews'] ?? 'checked'; ?>>
                                        <label class="form-check-label">Content reviews</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="update_notification_preferences" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Preferences
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Activity Tab -->
            <div class="tab-pane fade" id="activity" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_activity)): ?>
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo ucfirst(str_replace('_', ' ', $activity['action'] ?? '')); ?></h6>
                                            <?php if (!empty($activity['details'])): ?>
                                                <p class="text-muted mb-1"><?php echo safe_htmlspecialchars($activity['details']); ?></p>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                                                <?php if (!empty($activity['ip_address'])): ?>
                                                    <span class="ms-2"><i class="fas fa-globe"></i> <?php echo safe_htmlspecialchars($activity['ip_address']); ?></span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
