<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Get poll ID
$poll_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($poll_id === 0) {
    redirect('manage-polls.php');
}

// Get poll details
$poll_query = "SELECT p.*
               FROM polls p
               WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $poll_query);
mysqli_stmt_bind_param($stmt, 'i', $poll_id);
mysqli_stmt_execute($stmt);
$poll = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$poll) {
    redirect('manage-polls.php?error=not_found');
}

// Get poll options with vote counts
$options_query = "SELECT po.*, COUNT(pv.id) as vote_count,
                 COALESCE((COUNT(pv.id) * 100 / (SELECT COUNT(*) FROM poll_votes WHERE poll_id = ?)), 0) as percentage
                 FROM poll_options po
                 LEFT JOIN poll_votes pv ON po.id = pv.option_id
                 WHERE po.poll_id = ?
                 GROUP BY po.id
                 ORDER BY po.id ASC";
$stmt = mysqli_prepare($conn, $options_query);
mysqli_stmt_bind_param($stmt, 'ii', $poll_id, $poll_id);
mysqli_stmt_execute($stmt);
$options_result = mysqli_stmt_get_result($stmt);

// Get total votes
$total_votes_query = "SELECT COUNT(*) as total FROM poll_votes WHERE poll_id = ?";
$stmt = mysqli_prepare($conn, $total_votes_query);
mysqli_stmt_bind_param($stmt, 'i', $poll_id);
mysqli_stmt_execute($stmt);
$total_votes = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

// Get voting demographics
$demographics_query = "SELECT DATE(pv.voted_at) as vote_date, COUNT(*) as daily_votes,
                       COUNT(DISTINCT pv.ip_address) as unique_voters
                       FROM poll_votes pv
                       WHERE pv.poll_id = ?
                       GROUP BY DATE(pv.voted_at)
                       ORDER BY vote_date DESC
                       LIMIT 30";
$stmt = mysqli_prepare($conn, $demographics_query);
mysqli_stmt_bind_param($stmt, 'i', $poll_id);
mysqli_stmt_execute($stmt);
$demographics_result = mysqli_stmt_get_result($stmt);

// Get recent votes with user info
$recent_votes_query = "SELECT pv.*, po.option_text
                      FROM poll_votes pv
                      LEFT JOIN poll_options po ON pv.option_id = po.id
                      WHERE pv.poll_id = ?
                      ORDER BY pv.voted_at DESC
                      LIMIT 20";
$stmt = mysqli_prepare($conn, $recent_votes_query);
mysqli_stmt_bind_param($stmt, 'i', $poll_id);
mysqli_stmt_execute($stmt);
$recent_votes_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll Results - PK Live News Admin</title>
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
        .poll-option {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .poll-option:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .vote-bar {
            height: 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            padding: 0 15px;
            color: white;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 30px;
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.8rem;
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
                            <a class="nav-link" href="manage-editions.php">
                                <i class="fas fa-layer-group me-2"></i>News Editions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-tags.php">
                                <i class="fas fa-tags me-2"></i>Manage Tags
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-folder me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-bookmarks.php">
                                <i class="fas fa-bookmark me-2"></i>Bookmarks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
                                <i class="fas fa-chart-line me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage-polls.php">
                                <i class="fas fa-poll me-2"></i>Polls
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
                        <h1 class="h3 mb-0">Poll Results</h1>
                        <small>Detailed voting analysis and demographics</small>
                    </div>
                    <div>
                        <a href="manage-polls.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Polls
                        </a>
                    </div>
                </div>

                <!-- Poll Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-poll me-2"></i>Poll Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4><?php echo htmlspecialchars($poll['question']); ?></h4>
                                <div class="d-flex gap-3 mb-3">
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        <?php echo ucfirst($poll['status']); ?>
                                    </span>
                                </div>
                                <div class="text-muted">
                                    <small>
                                        <i class="fas fa-calendar me-1"></i>Created: <?php echo format_date($poll['created_at']); ?>
                                        <?php if ($poll['ends_at']): ?>
                                            <span class="ms-3"><i class="fas fa-clock me-1"></i>Expires: <?php echo format_date($poll['ends_at']); ?></span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h2 class="text-primary"><?php echo number_format($total_votes ?? 0); ?></h2>
                                    <p class="text-muted">Total Votes</p>
                                </div>
                                <div class="text-center mt-3">
                                    <button class="btn btn-primary" onclick="exportResults()">
                                        <i class="fas fa-download me-2"></i>Export Results
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Vote Results -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-bar me-2"></i>Vote Distribution</h5>
                            </div>
                            <div class="card-body">
                                <?php while ($option = mysqli_fetch_assoc($options_result)): ?>
                                    <div class="poll-option">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6><?php echo htmlspecialchars($option['option_text']); ?></h6>
                                            <span class="badge bg-primary"><?php echo number_format($option['vote_count'] ?? 0); ?> votes</span>
                                        </div>
                                        <div class="vote-bar" style="width: <?php echo $option['percentage']; ?>%;">
                                            <?php echo number_format($option['percentage'], 1 ?? 0); ?>%
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-pie me-2"></i>Visual Chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="pollChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Voting Timeline -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-calendar-alt me-2"></i>Voting Timeline</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Votes</th>
                                                <th>Unique Voters</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($demographic = mysqli_fetch_assoc($demographics_result)): ?>
                                                <tr>
                                                    <td><?php echo format_date($demographic['vote_date']); ?></td>
                                                    <td><?php echo number_format($demographic['daily_votes'] ?? 0); ?></td>
                                                    <td><?php echo number_format($demographic['unique_voters'] ?? 0); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Votes -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-history me-2"></i>Recent Votes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Voter</th>
                                                <th>Option</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($vote = mysqli_fetch_assoc($recent_votes_result)): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="user-avatar me-2">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold">Anonymous Voter</div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($vote['ip_address']); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($vote['option_text']); ?></span>
                                                    </td>
                                                    <td>
                                                        <small><?php echo format_date($vote['voted_at']); ?></small>
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
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Poll Chart
        const ctx = document.getElementById('pollChart').getContext('2d');
        const chartData = {
            labels: [
                <?php 
                mysqli_data_seek($options_result, 0);
                $labels = [];
                while ($option = mysqli_fetch_assoc($options_result)) {
                    $labels[] = "'" . addslashes($option['option_text']) . "'";
                }
                echo implode(',', $labels);
                ?>
            ],
            datasets: [{
                data: [
                    <?php 
                    mysqli_data_seek($options_result, 0);
                    $data = [];
                    while ($option = mysqli_fetch_assoc($options_result)) {
                        $data[] = $option['vote_count'];
                    }
                    echo implode(',', $data);
                    ?>
                ],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384',
                    '#36A2EB'
                ],
                borderWidth: 2
            }]
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Export Results
        function exportResults() {
            const data = {
                poll: {
                    question: '<?php echo addslashes($poll['question']); ?>',
                    total_votes: <?php echo $total_votes; ?>,
                    created_at: '<?php echo $poll['created_at']; ?>'
                },
                options: [
                    <?php 
                    mysqli_data_seek($options_result, 0);
                    while ($option = mysqli_fetch_assoc($options_result)) {
                        echo "{";
                        echo "option: '" . addslashes($option['option_text']) . "',";
                        echo "votes: " . $option['vote_count'] . ",";
                        echo "percentage: " . number_format($option['percentage'], 2 ?? 0);
                        echo "},";
                    }
                    ?>
                ]
            };

            const dataStr = JSON.stringify(data, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
            
            const exportFileDefaultName = 'poll_results_<?php echo $poll_id; ?>.json';
            
            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();
        }
    </script>
</body>
</html>
