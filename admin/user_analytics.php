<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Get user statistics
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users = mysqli_query($conn, $total_users_query)->fetch_assoc()['total'];

$active_users_query = "SELECT COUNT(*) as count FROM users WHERE status = 'active'";
$active_users = mysqli_query($conn, $active_users_query)->fetch_assoc()['count'];

$admin_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'admin'";
$admin_count = mysqli_query($conn, $admin_count_query)->fetch_assoc()['count'];

$editor_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'editor'";
$editor_count = mysqli_query($conn, $editor_count_query)->fetch_assoc()['count'];

$reporter_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'reporter'";
$reporter_count = mysqli_query($conn, $reporter_count_query)->fetch_assoc()['count'];

// Get user registration trends (last 30 days)
$registration_trends = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $query = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = '$date'";
    $count = mysqli_query($conn, $query)->fetch_assoc()['count'];
    $registration_trends[] = [
        'date' => date('M j', strtotime($date)),
        'count' => $count
    ];
}

// Get user activity by role
$user_activity_query = "SELECT 
    u.role,
    COUNT(*) as user_count,
    (SELECT COUNT(*) FROM news WHERE author_id = u.id) as total_articles,
    (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as total_comments
FROM users u 
GROUP BY u.role";
$activity_result = mysqli_query($conn, $user_activity_query);

// Get top contributors
$top_contributors_query = "SELECT 
    u.id,
    u.name,
    u.email,
    u.role,
    u.created_at,
    (SELECT COUNT(*) FROM news WHERE author_id = u.id) as article_count,
    (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count
FROM users u 
WHERE u.status = 'active'
ORDER BY article_count DESC, comment_count DESC 
LIMIT 10";
$top_contributors = mysqli_query($conn, $top_contributors_query);

// Get recent user registrations
$recent_users_query = "SELECT u.*, 
    (SELECT COUNT(*) FROM news WHERE author_id = u.id) as news_count
FROM users u 
ORDER BY u.created_at DESC 
LIMIT 10";
$recent_users = mysqli_query($conn, $recent_users_query);

// Get user login activity (if analytics table exists)
$user_login_activity = [];
$analytics_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'user_analytics'")->num_rows > 0;

if ($analytics_table_exists) {
    $login_activity_query = "SELECT 
        DATE(created_at) as date,
        COUNT(DISTINCT user_id) as active_users,
        COUNT(*) as total_actions
    FROM user_analytics 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC";
    $login_result = mysqli_query($conn, $login_activity_query);
    
    while ($row = mysqli_fetch_assoc($login_result)) {
        $user_login_activity[] = $row;
    }
}

// Get user statistics by month
$monthly_stats = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $query = "SELECT 
        COUNT(*) as new_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as new_admins,
        SUM(CASE WHEN role = 'editor' THEN 1 ELSE 0 END) as new_editors,
        SUM(CASE WHEN role = 'reporter' THEN 1 ELSE 0 END) as new_reporters
    FROM users 
    WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $result = mysqli_query($conn, $query)->fetch_assoc();
    
    $monthly_stats[] = [
        'month' => date('M Y', strtotime($month)),
        'new_users' => (int)$result['new_users'],
        'new_admins' => (int)$result['new_admins'],
        'new_editors' => (int)$result['new_editors'],
        'new_reporters' => (int)$result['new_reporters']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Analytics - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .activity-high { background-color: #10b981; }
        .activity-medium { background-color: #f59e0b; }
        .activity-low { background-color: #ef4444; }
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
                            <a class="nav-link active" href="user_analytics.php">
                                <i class="fas fa-chart-line me-2"></i>User Analytics
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
                            <a class="nav-link" href="settings.php">
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
                        <h1 class="h3 mb-0">User Analytics Dashboard</h1>
                        <small>Comprehensive user statistics and activity analysis</small>
                    </div>
                    <div>
                        <button class="btn btn-light" onclick="exportData()">
                            <i class="fas fa-download me-2"></i>Export Data
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-primary me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $total_users; ?></h3>
                                    <p class="mb-0 text-muted">Total Users</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-success me-3">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $active_users; ?></h3>
                                    <p class="mb-0 text-muted">Active Users</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-danger me-3">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $admin_count; ?></h3>
                                    <p class="mb-0 text-muted">Admins</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-warning me-3">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $editor_count; ?></h3>
                                    <p class="mb-0 text-muted">Editors</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">User Registration Trends (Last 30 Days)</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="registrationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">User Distribution by Role</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="roleChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Statistics -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Monthly User Registration Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 400px;">
                                    <canvas id="monthlyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Activity Table -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">User Activity by Role</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Role</th>
                                                <th>Users</th>
                                                <th>Articles</th>
                                                <th>Comments</th>
                                                <th>Activity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($activity = mysqli_fetch_assoc($activity_result)): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-<?php echo $activity['role'] == 'admin' ? 'danger' : ($activity['role'] == 'editor' ? 'warning' : 'info'); ?> role-badge">
                                                            <?php echo ucfirst($activity['role']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $activity['user_count']; ?></td>
                                                    <td><?php echo $activity['total_articles']; ?></td>
                                                    <td><?php echo $activity['total_comments']; ?></td>
                                                    <td>
                                                        <?php 
                                                        $avg_activity = ($activity['total_articles'] + $activity['total_comments']) / max($activity['user_count'], 1);
                                                        $activity_class = $avg_activity > 10 ? 'activity-high' : ($avg_activity > 3 ? 'activity-medium' : 'activity-low');
                                                        ?>
                                                        <span class="activity-dot <?php echo $activity_class; ?>"></span>
                                                        <?php echo number_format($avg_activity, 1); ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Recent User Registrations</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Role</th>
                                                <th>Articles</th>
                                                <th>Joined</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = mysqli_fetch_assoc($recent_users)): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="user-avatar me-2">
                                                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold small"><?php echo htmlspecialchars($user['name']); ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars(substr($user['email'], 0, 20)); ?>...</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'editor' ? 'warning' : 'info'); ?> role-badge">
                                                            <?php echo ucfirst($user['role']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $user['news_count']; ?></span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo date('M d', strtotime($user['created_at'])); ?></small>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Contributors -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Top Contributors</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>User</th>
                                                <th>Role</th>
                                                <th>Articles</th>
                                                <th>Comments</th>
                                                <th>Total Activity</th>
                                                <th>Member Since</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $rank = 1; while ($contributor = mysqli_fetch_assoc($top_contributors)): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">#<?php echo $rank++; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="user-avatar me-3">
                                                                <?php echo strtoupper(substr($contributor['name'], 0, 1)); ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold"><?php echo htmlspecialchars($contributor['name']); ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($contributor['email']); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $contributor['role'] == 'admin' ? 'danger' : ($contributor['role'] == 'editor' ? 'warning' : 'info'); ?> role-badge">
                                                            <?php echo ucfirst($contributor['role']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $contributor['article_count']; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo $contributor['comment_count']; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <?php 
                                                            $total_activity = $contributor['article_count'] + $contributor['comment_count'];
                                                            $max_activity = 50; // Adjust based on your data
                                                            $percentage = min(($total_activity / $max_activity) * 100, 100);
                                                            ?>
                                                            <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%">
                                                                <?php echo $total_activity; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($contributor['created_at'])); ?></small>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Login Activity (if analytics data available) -->
                <?php if (!empty($user_login_activity)): ?>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">User Activity (Last 7 Days)</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="activityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Registration Trends Chart
        const registrationCtx = document.getElementById('registrationChart').getContext('2d');
        const registrationChart = new Chart(registrationCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($registration_trends, 'date')); ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?php echo json_encode(array_column($registration_trends, 'count')); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Role Distribution Chart
        const roleCtx = document.getElementById('roleChart').getContext('2d');
        const roleChart = new Chart(roleCtx, {
            type: 'doughnut',
            data: {
                labels: ['Admins', 'Editors', 'Reporters'],
                datasets: [{
                    data: [<?php echo $admin_count; ?>, <?php echo $editor_count; ?>, <?php echo $reporter_count; ?>],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 205, 86)',
                        'rgb(54, 162, 235)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Monthly Statistics Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($monthly_stats, 'month')); ?>,
                datasets: [
                    {
                        label: 'Total Users',
                        data: <?php echo json_encode(array_column($monthly_stats, 'new_users')); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)'
                    },
                    {
                        label: 'Admins',
                        data: <?php echo json_encode(array_column($monthly_stats, 'new_admins')); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.8)'
                    },
                    {
                        label: 'Editors',
                        data: <?php echo json_encode(array_column($monthly_stats, 'new_editors')); ?>,
                        backgroundColor: 'rgba(255, 205, 86, 0.8)'
                    },
                    {
                        label: 'Reporters',
                        data: <?php echo json_encode(array_column($monthly_stats, 'new_reporters')); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        <?php if (!empty($user_login_activity)): ?>
        // User Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityChart = new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_reverse(array_column($user_login_activity, 'date'))); ?>,
                datasets: [{
                    label: 'Active Users',
                    data: <?php echo json_encode(array_reverse(array_column($user_login_activity, 'active_users'))); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Total Actions',
                    data: <?php echo json_encode(array_reverse(array_column($user_login_activity, 'total_actions'))); ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>

        // Export Data Function
        function exportData() {
            const data = {
                total_users: <?php echo $total_users; ?>,
                active_users: <?php echo $active_users; ?>,
                admin_count: <?php echo $admin_count; ?>,
                editor_count: <?php echo $editor_count; ?>,
                reporter_count: <?php echo $reporter_count; ?>,
                registration_trends: <?php echo json_encode($registration_trends); ?>,
                monthly_stats: <?php echo json_encode($monthly_stats); ?>
            };
            
            const dataStr = JSON.stringify(data, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
            
            const exportFileDefaultName = 'user_analytics_' + new Date().toISOString().split('T')[0] + '.json';
            
            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();
        }
    </script>
</body>
</html>
