<?php
require_once '../config/database.php';

// Check if user is logged in and is admin or editor
if (!is_logged_in() || (!is_admin() && !is_editor())) {
    redirect('login.php');
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $poll_id = $_GET['delete'];
    
    // Delete poll and related votes/options
    $delete_query = "DELETE FROM polls WHERE id = $poll_id";
    
    if (mysqli_query($conn, $delete_query)) {
        $success = "Poll deleted successfully!";
    } else {
        $error = "Error deleting poll!";
    }
}

// Handle status change
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $poll_id = $_GET['id'];
    $status = $_GET['status'];
    
    if (in_array($status, ['active', 'inactive'])) {
        $update_query = "UPDATE polls SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $poll_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Poll status updated successfully!";
        } else {
            $error = "Error updating poll status!";
        }
    }
}

// Check if polls table exists, create it if not
$polls_check = mysqli_query($conn, "SHOW TABLES LIKE 'polls'");
if (mysqli_num_rows($polls_check) == 0) {
    $create_polls_sql = "CREATE TABLE `polls` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `question` varchar(255) NOT NULL,
        `status` enum('active','inactive','closed') DEFAULT 'active',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `description` text DEFAULT NULL,
        `created_by` int(11) DEFAULT NULL,
        `ends_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_polls_status` (`status`),
        KEY `idx_polls_created` (`created_at`),
        KEY `created_by` (`created_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $create_polls_sql);
}

// Check if poll_options table exists, create it if not
$options_check = mysqli_query($conn, "SHOW TABLES LIKE 'poll_options'");
if (mysqli_num_rows($options_check) == 0) {
    $create_options_sql = "CREATE TABLE `poll_options` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `poll_id` int(11) NOT NULL,
        `option_text` varchar(255) NOT NULL,
        `votes` int(11) DEFAULT 0,
        `order_position` int(11) DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `poll_id` (`poll_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $create_options_sql);
}

// Check if poll_votes table exists, create it if not
$votes_check = mysqli_query($conn, "SHOW TABLES LIKE 'poll_votes'");
if (mysqli_num_rows($votes_check) == 0) {
    $create_votes_sql = "CREATE TABLE `poll_votes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `poll_id` int(11) NOT NULL,
        `option_id` int(11) NOT NULL,
        `ip_address` varchar(45) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_vote` (`poll_id`,`ip_address`),
        KEY `option_id` (`option_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $create_votes_sql);
}

// Get all polls with vote counts
$polls_query = "SELECT p.*, 
                 (SELECT COUNT(*) FROM poll_votes WHERE poll_id = p.id) as total_votes,
                 (SELECT COUNT(*) FROM poll_options WHERE poll_id = p.id) as options_count
                 FROM polls p 
                 ORDER BY p.created_at DESC";
$polls_result = mysqli_query($conn, $polls_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Polls - PK Live News Admin</title>
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
        .poll-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        .poll-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .vote-count {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
                            <a class="nav-link active" href="manage-polls.php">
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
                        <h1 class="h3 mb-0">Manage Polls</h1>
                        <small>Create and manage user polls</small>
                    </div>
                    <div>
                        <a href="add-poll.php" class="btn btn-light">
                            <i class="fas fa-plus me-2"></i>Add Poll
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
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

                <!-- Polls List -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Polls (<?php echo mysqli_num_rows($polls_result); ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Question</th>
                                        <th>Options</th>
                                        <th>Votes</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Expires</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($poll = mysqli_fetch_assoc($polls_result)): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($poll['question']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php 
                                                        $options_query = "SELECT option_text FROM poll_options WHERE poll_id = " . $poll['id'] . " LIMIT 3";
                                                        $options_result = mysqli_query($conn, $options_query);
                                                        $options = [];
                                                        while ($option = mysqli_fetch_assoc($options_result)) {
                                                            $options[] = htmlspecialchars($option['option_text']);
                                                        }
                                                        echo implode(', ', array_slice($options, 0, 2));
                                                        if (mysqli_num_rows($options_result) > 2) {
                                                            echo '...';
                                                        }
                                                        ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $poll['options_count']; ?> options</span>
                                            </td>
                                            <td>
                                                <div class="vote-count">
                                                    <?php echo number_format($poll['total_votes'] ?? 0); ?>
                                                    <small>votes</small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = $poll['status'] == 'active' ? 'bg-success' : 'bg-secondary';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($poll['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($poll['created_at'])); ?></td>
                                            <td>
                                                <?php 
                                                if ($poll['ends_at']) {
                                                    $is_expired = strtotime($poll['ends_at']) < time();
                                                    echo date('M d, Y', strtotime($poll['ends_at']));
                                                    if ($is_expired) {
                                                        echo ' <span class="badge bg-danger">Expired</span>';
                                                    }
                                                } else {
                                                    echo '<span class="text-muted">Never</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons d-flex gap-1">
                                                    <a href="edit-poll.php?id=<?php echo $poll['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if ($poll['status'] != 'active'): ?>
                                                        <a href="?id=<?php echo $poll['id']; ?>&status=active" class="btn btn-sm btn-outline-success" title="Activate">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($poll['status'] != 'inactive'): ?>
                                                        <a href="?id=<?php echo $poll['id']; ?>&status=inactive" class="btn btn-sm btn-outline-warning" title="Deactivate">
                                                            <i class="fas fa-pause"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="view-poll-results.php?id=<?php echo $poll['id']; ?>" class="btn btn-sm btn-outline-info" title="View Results">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                    
                                                    <a href="?delete=<?php echo $poll['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this poll? All votes will be lost.')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (mysqli_num_rows($polls_result) === 0): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-poll fa-3x text-muted mb-3"></i>
                                <h5>No polls found</h5>
                                <p class="text-muted">Start by creating your first poll</p>
                                <a href="add-poll.php" class="btn btn-danger">
                                    <i class="fas fa-plus me-2"></i>Create First Poll
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Poll Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-primary"><?php echo mysqli_num_rows($polls_result); ?></h3>
                                <p class="mb-0">Total Polls</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php
                                $active_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM polls WHERE status = 'active'")->fetch_assoc()['count'];
                                ?>
                                <h3 class="text-success"><?php echo $active_count; ?></h3>
                                <p class="mb-0">Active Polls</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php
                                $total_votes = mysqli_query($conn, "SELECT COUNT(*) as count FROM poll_votes")->fetch_assoc()['count'];
                                ?>
                                <h3 class="text-info"><?php echo number_format($total_votes ?? 0); ?></h3>
                                <p class="mb-0">Total Votes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php
                                $expired_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM polls WHERE ends_at < NOW()")->fetch_assoc()['count'];
                                ?>
                                <h3 class="text-warning"><?php echo $expired_count; ?></h3>
                                <p class="mb-0">Expired Polls</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Poll Activity -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Recent Poll Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Poll</th>
                                        <th>Option</th>
                                        <th>Votes</th>
                                        <th>Last Vote</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent_votes_query = "SELECT pv.*, po.option_text, p.question 
                                                        FROM poll_votes pv 
                                                        JOIN poll_options po ON pv.option_id = po.id 
                                                        JOIN polls p ON pv.poll_id = p.id 
                                                        ORDER BY pv.created_at DESC LIMIT 10";
                                    $recent_votes_result = mysqli_query($conn, $recent_votes_query);
                                    ?>
                                    <?php while ($vote = mysqli_fetch_assoc($recent_votes_result)): ?>
                                        <tr>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($vote['question'], 0, 50)) . '...'; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($vote['option_text']); ?></td>
                                            <td>
                                                <?php
                                                $option_votes = mysqli_query($conn, "SELECT COUNT(*) as count FROM poll_votes WHERE option_id = " . $vote['option_id'])->fetch_assoc()['count'];
                                                echo $option_votes;
                                                ?>
                                            </td>
                                            <td><?php echo date('h:i A', strtotime($vote['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh poll data
        let refreshInterval;
        
        function startAutoRefresh() {
            refreshInterval = setInterval(() => {
                // Refresh poll statistics every 30 seconds
                location.reload();
            }, 30000);
        }
        
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
        
        // Start auto-refresh when page loads
        startAutoRefresh();
        
        // Stop auto-refresh when user interacts with page
        document.addEventListener('click', () => {
            stopAutoRefresh();
            // Restart auto-refresh after 5 minutes of inactivity
            setTimeout(startAutoRefresh, 300000);
        });
        
        // Confirm delete actions
        document.querySelectorAll('[onclick*="delete"]').forEach(element => {
            element.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this poll? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
        
        // Export poll data
        function exportPollData() {
            const polls = [];
            document.querySelectorAll('tbody tr').forEach(row => {
                const pollData = {
                    question: row.querySelector('td:first-child strong').textContent,
                    votes: row.querySelector('.vote-count').textContent,
                    status: row.querySelector('.badge').textContent
                };
                polls.push(pollData);
            });
            
            const dataStr = JSON.stringify(polls, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
            
            const exportFileDefaultName = 'polls_' + new Date().toISOString().slice(0,10) + '.json';
            
            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();
        }
        
        // Add export button to header
        document.addEventListener('DOMContentLoaded', function() {
            const headerActions = document.querySelector('.admin-header .d-flex');
            if (headerActions) {
                const exportBtn = document.createElement('button');
                exportBtn.className = 'btn btn-outline-light ms-2';
                exportBtn.innerHTML = '<i class="fas fa-download me-2"></i>Export';
                exportBtn.onclick = exportPollData;
                headerActions.appendChild(exportBtn);
            }
        });
    </script>
</body>
</html>
