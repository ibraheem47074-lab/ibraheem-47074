<?php
require_once '../config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user information with advanced details
function getUserProfileForView($user_id) {
    global $conn;
    
    $query = "SELECT u.*, 
                     (SELECT AVG(rating) FROM user_ratings WHERE rated_user_id = u.id) as average_rating,
                     (SELECT COUNT(*) FROM user_ratings WHERE rated_user_id = u.id) as total_ratings,
                     (SELECT COUNT(*) FROM user_achievements WHERE user_id = u.id) as total_achievements,
                     (SELECT COUNT(*) FROM news WHERE author_id = u.id AND status = 'published') as published_articles,
                     (SELECT COUNT(*) FROM news WHERE author_id = u.id AND status = 'draft') as draft_articles
              FROM users u 
              WHERE u.id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

// Get user achievements
function getUserAchievementsForProfile($user_id) {
    global $conn;
    
    $query = "SELECT * FROM user_achievements WHERE user_id = ? ORDER BY earned_at DESC LIMIT 5";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get user's recent articles
function getUserRecentArticles($user_id, $limit = 5) {
    global $conn;
    
    $query = "SELECT id, title, slug, category, image, published_at, status, view_count 
              FROM news 
              WHERE author_id = ? 
              ORDER BY published_at DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get user work schedule
function getUserScheduleForProfile($user_id) {
    global $conn;
    
    $query = "SELECT * FROM user_work_schedule WHERE user_id = ? AND is_available = 1 ORDER BY FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Increment profile views
function incrementProfileViews($user_id) {
    global $conn;
    
    $update_query = "UPDATE users SET profile_views = profile_views + 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
}

// Get user data
$user = getUserProfileForView($user_id);
$achievements = getUserAchievementsForProfile($user_id);
$recent_articles = getUserRecentArticles($user_id);
$schedule = getUserScheduleForProfile($user_id);

// Increment profile views (only if not viewing own profile)
if (!isset($_GET['preview'])) {
    incrementProfileViews($user_id);
}

// Parse social links
$social_links = [];
if (!empty($user['social_links'])) {
    $lines = explode("\n", $user['social_links']);
    foreach ($lines as $line) {
        if (strpos($line, 'http') !== false) {
            $social_links[] = trim($line);
        }
    }
}

// Parse skills
$skills = [];
if (!empty($user['skills'])) {
    $skills = array_map('trim', explode(',', $user['skills']));
    $skills = array_filter($skills);
}

// Get role-specific information
$role_info = [
    'admin' => [
        'icon' => 'fas fa-user-shield',
        'color' => 'danger',
        'description' => 'System Administrator with full access'
    ],
    'editor' => [
        'icon' => 'fas fa-user-edit',
        'color' => 'primary',
        'description' => 'Content Editor with publishing rights'
    ],
    'reporter' => [
        'icon' => 'fas fa-user-pen',
        'color' => 'info',
        'description' => 'News Reporter and content creator'
    ]
];

$current_role = $role_info[$user['role']] ?? $role_info['reporter'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo htmlspecialchars($user['name']); ?> - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            position: relative;
            overflow: hidden;
        }
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            object-fit: cover;
        }
        .verification-badge {
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .premium-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            border: none;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .skill-tag {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 0.25rem;
            display: inline-block;
            border: 1px solid #dee2e6;
        }
        .achievement-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            height: 100%;
        }
        .article-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .article-card:hover {
            transform: translateY(-5px);
        }
        .article-image {
            height: 200px;
            object-fit: cover;
        }
        .rating-stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .social-link {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            color: white;
            margin: 0 0.5rem;
            transition: background 0.3s;
        }
        .social-link:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 2rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #667eea;
        }
        .timeline-dot {
            position: absolute;
            left: -6px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #667eea;
            border: 3px solid white;
        }
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
            </style>
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="<?php echo !empty($user['image']) ? '../uploads/avatars/' . $user['image'] : 'https://via.placeholder.com/150'; ?>" 
                         alt="Profile" class="profile-avatar">
                    <div class="mt-3">
                        <?php if ($user['verification_status'] === 'verified'): ?>
                            <span class="verification-badge">
                                <i class="fas fa-check-circle"></i> Verified
                            </span>
                        <?php elseif ($user['verification_status'] === 'premium'): ?>
                            <span class="premium-badge">
                                <i class="fas fa-crown"></i> Premium
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h1 class="mb-3"><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p class="lead mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="mb-3">
                        <span class="badge bg-<?php echo $current_role['color']; ?> fs-6">
                            <i class="<?php echo $current_role['icon']; ?>"></i>
                            <?php echo ucfirst($user['role'] ?? ''); ?>
                        </span>
                        <span class="badge bg-info fs-6">
                            <i class="fas fa-building"></i>
                            <?php echo ucfirst($user['department'] ?? ''); ?>
                        </span>
                        <span class="badge bg-warning fs-6">
                            <i class="fas fa-chart-line"></i>
                            <?php echo ucfirst($user['experience_level'] ?? ''); ?>
                        </span>
                    </div>
                    <?php if (!empty($user['bio'])): ?>
                        <p class="mb-3"><?php echo htmlspecialchars($user['bio']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($user['specialization'])): ?>
                        <p class="mb-3">
                            <strong>Specialization:</strong> <?php echo htmlspecialchars($user['specialization']); ?>
                        </p>
                    <?php endif; ?>
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($user['total_ratings'] > 0): ?>
                            <div class="rating-stars">
                                <?php 
                                $rating = round($user['average_rating'], 1);
                                for ($i = 1; $i <= 5; $i++):
                                    if ($i <= $rating):
                                        echo '<i class="fas fa-star"></i>';
                                    else:
                                        echo '<i class="far fa-star"></i>';
                                    endif;
                                endfor;
                                ?>
                                <span class="ms-2"><?php echo $rating; ?> (<?php echo $user['total_ratings']; ?> reviews)</span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($social_links)): ?>
                            <?php foreach (array_slice($social_links, 0, 3) as $link): ?>
                                <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" class="social-link">
                                    <i class="fas fa-link"></i>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="info-card">
                        <h6>Member Since</h6>
                        <p class="mb-0"><?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div class="info-card">
                        <h6>Last Login</h6>
                        <p class="mb-0"><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></p>
                    </div>
                    <a href="advanced-profile.php" class="btn btn-light mt-3">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <!-- Statistics Section -->
        <div class="row mb-5">
            <div class="col-md-2 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $user['published_articles']; ?></div>
                    <div class="stat-label">Published</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $user['draft_articles']; ?></div>
                    <div class="stat-label">Drafts</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $user['profile_views']; ?></div>
                    <div class="stat-label">Profile Views</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $user['total_achievements']; ?></div>
                    <div class="stat-label">Achievements</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $user['login_count']; ?></div>
                    <div class="stat-label">Total Logins</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo round($user['average_rating'] ?? 0, 1); ?></div>
                    <div class="stat-label">Rating</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Skills Section -->
            <div class="col-lg-4 mb-4">
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-tools"></i> Skills & Expertise</h5>
                    <?php if (!empty($skills)): ?>
                        <div class="mb-3">
                            <?php foreach ($skills as $skill): ?>
                                <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No skills added yet</p>
                    <?php endif; ?>
                    
                    <h6 class="mt-4">Experience Level</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><?php echo ucfirst($user['experience_level'] ?? ''); ?></span>
                        <span class="text-muted">
                            <?php 
                            $experience_levels = ['junior', 'intermediate', 'senior', 'expert', 'lead'];
                            if (in_array($user['experience_level'] ?? '', $experience_levels)) {
                                echo ucfirst($user['experience_level']);
                            } else {
                                echo 'Not specified';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Achievements Section -->
            <div class="col-lg-4 mb-4">
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-trophy"></i> Recent Achievements</h5>
                    <?php if (!empty($achievements)): ?>
                        <?php foreach ($achievements as $achievement): ?>
                            <div class="achievement-card mb-3">
                                <i class="<?php echo $achievement['achievement_icon'] ?? 'fas fa-award'; ?> fa-2x mb-2"></i>
                                <h6><?php echo htmlspecialchars($achievement['achievement_title']); ?></h6>
                                <p class="mb-1 small"><?php echo htmlspecialchars($achievement['achievement_description']); ?></p>
                                <small><?php echo date('M d, Y', strtotime($achievement['earned_at'])); ?></small>
                                <?php if ($achievement['points'] > 0): ?>
                                    <span class="badge bg-light text-dark"><?php echo $achievement['points']; ?> pts</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No achievements yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Work Schedule Section -->
            <div class="col-lg-4 mb-4">
                <div class="info-card">
                    <h5 class="mb-3"><i class="fas fa-calendar-alt"></i> Work Schedule</h5>
                    <?php if (!empty($schedule)): ?>
                        <?php 
                        $day_names = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 
                                       'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
                        foreach ($schedule as $day):
                        ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold"><?php echo $day_names[$day['day_of_week']]; ?></span>
                                <span class="text-muted">
                                    <?php echo date('h:i A', strtotime($day['start_time'])); ?> - 
                                    <?php echo date('h:i A', strtotime($day['end_time'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No work schedule set</p>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> 
                            <?php echo htmlspecialchars($user['working_hours'] ?? 'Working hours not specified'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Articles Section -->
        <div class="row">
            <div class="col-12">
                <div class="info-card">
                    <h5 class="mb-4"><i class="fas fa-newspaper"></i> Recent Articles</h5>
                    <?php if (!empty($recent_articles)): ?>
                        <div class="row">
                            <?php foreach ($recent_articles as $article): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card article-card">
                                        <?php if (!empty($article['image'])): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($article['image']); ?>" 
                                                 class="card-img-top article-image" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-info"><?php echo htmlspecialchars($article['category']); ?></span>
                                                <span class="badge bg-<?php echo $article['status'] === 'published' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($article['status'] ?? ''); ?>
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-eye"></i> <?php echo number_format($article['view_count'] ?? 0); ?> views
                                                </small>
                                                <small class="text-muted ms-3">
                                                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($article['published_at'])); ?>
                                                </small>
                                            </div>
                                            <a href="../news.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" 
                                               class="btn btn-sm btn-outline-primary mt-2">
                                                Read More
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No articles published yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate experience bar on page load
        window.addEventListener('load', function() {
            const progressBars = document.querySelectorAll('.experience-progress');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html>
