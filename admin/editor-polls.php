<?php
require_once '../config/database.php';

// Check if user is logged in and is editor
if (!is_logged_in() || !is_editor()) {
    redirect('login.php');
}

// Check if polls table exists
$polls_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'polls'")) > 0;
$poll_options_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'poll_options'")) > 0;
$poll_votes_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'poll_votes'")) > 0;

// Create polls tables if they don't exist
if (!$polls_table_exists) {
    mysqli_query($conn, "CREATE TABLE polls (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('active', 'inactive', 'closed') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ends_at TIMESTAMP NULL
    )");
    $polls_table_exists = true;
}

if (!$poll_options_table_exists) {
    mysqli_query($conn, "CREATE TABLE poll_options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        poll_id INT NOT NULL,
        option_text VARCHAR(255) NOT NULL,
        votes INT DEFAULT 0,
        FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
    )");
    $poll_options_table_exists = true;
}

if (!$poll_votes_table_exists) {
    mysqli_query($conn, "CREATE TABLE poll_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        poll_id INT NOT NULL,
        option_id INT NOT NULL,
        user_id INT,
        ip_address VARCHAR(45),
        voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
        FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        UNIQUE KEY unique_poll_vote (poll_id, COALESCE(user_id, 0), ip_address)
    )");
    $poll_votes_table_exists = true;
}

// Handle poll actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_poll'])) {
        $question = clean_input($_POST['question']);
        $description = clean_input($_POST['description']);
        $status = clean_input($_POST['status']);
        $ends_at = !empty($_POST['ends_at']) ? $_POST['ends_at'] : null;
        $options = array_filter($_POST['options'], function($opt) { return !empty(trim($opt)); });
        
        if (!empty($question) && count($options) >= 2) {
            // Create poll
            $query = "INSERT INTO polls (question, description, status, ends_at) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ssss', $question, $description, $status, $ends_at);
            
            if (mysqli_stmt_execute($stmt)) {
                $poll_id = mysqli_insert_id($conn);
                
                // Add options
                foreach ($options as $index => $option_text) {
                    $option_query = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
                    $option_stmt = mysqli_prepare($conn, $option_query);
                    mysqli_stmt_bind_param($option_stmt, 'is', $poll_id, $option_text);
                    mysqli_stmt_execute($option_stmt);
                }
                
                $success = "Poll created successfully!";
            } else {
                $error = "Failed to create poll!";
            }
        } else {
            $error = "Question and at least 2 options are required!";
        }
    }
    
    if (isset($_POST['update_poll'])) {
        $poll_id = (int)$_POST['poll_id'];
        $question = clean_input($_POST['question']);
        $description = clean_input($_POST['description']);
        $status = clean_input($_POST['status']);
        $ends_at = !empty($_POST['ends_at']) ? $_POST['ends_at'] : null;
        
        $query = "UPDATE polls SET question = ?, description = ?, status = ?, ends_at = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssi', $question, $description, $status, $ends_at, $poll_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Poll updated successfully!";
        } else {
            $error = "Failed to update poll!";
        }
    }
    
    if (isset($_POST['add_option'])) {
        $poll_id = (int)$_POST['poll_id'];
        $option_text = clean_input($_POST['option_text']);
        
        if (!empty($option_text)) {
            $query = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'is', $poll_id, $option_text);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Option added successfully!";
            } else {
                $error = "Failed to add option!";
            }
        }
    }
    
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $poll_id = (int)$_GET['id'];
        
        switch ($_GET['action']) {
            case 'delete':
                mysqli_query($conn, "DELETE FROM polls WHERE id = $poll_id");
                $success = "Poll deleted successfully!";
                break;
                
            case 'toggle_status':
                $current_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM polls WHERE id = $poll_id"))['status'];
                $new_status = $current_status === 'active' ? 'inactive' : 'active';
                mysqli_query($conn, "UPDATE polls SET status = '$new_status' WHERE id = $poll_id");
                $success = "Poll status updated!";
                break;
        }
    }
}

// Get polls with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filtering
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';

// Build WHERE clause
$where_conditions = [];
if (!empty($filter_status)) {
    $where_conditions[] = "status = '$filter_status'";
}
$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total polls count
$total_polls = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM polls $where_clause"))['count'];

// Get polls
$polls_query = "SELECT p.*, 
                (SELECT COUNT(*) FROM poll_votes WHERE poll_id = p.id) as total_votes,
                (SELECT COUNT(*) FROM poll_options WHERE poll_id = p.id) as options_count
                FROM polls p 
                $where_clause 
                ORDER BY p.created_at DESC 
                LIMIT $per_page OFFSET $offset";
$polls = mysqli_query($conn, $polls_query);

// Calculate pagination
$total_pages = ceil($total_polls / $per_page);

// Get statistics
$active_polls = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM polls WHERE status = 'active'"))['count'];
$total_votes_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM poll_votes"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Polls - PK Live News Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .editor-header {
            background: linear-gradient(135deg, #4834d4 0%, #686de0 100%);
            color: white;
        }
        .poll-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-active { background-color: #d4edda; color: #155724; }
        .status-inactive { background-color: #f8d7da; color: #721c24; }
        .status-closed { background-color: #fff3cd; color: #856404; }
        .poll-card {
            border-left: 4px solid #4834d4;
            transition: all 0.3s ease;
        }
        .poll-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .option-bar {
            background: linear-gradient(45deg, #667eea, #764ba2);
            height: 8px;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/editor-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-poll me-3"></i>Manage Polls</h2>
                <p class="text-muted">Create and manage polls to engage your audience.</p>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="text-uppercase text-muted mb-1">Total Polls</h5>
                                <h3 class="font-weight-bold"><?php echo $total_polls; ?></h3>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-poll fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="text-uppercase text-muted mb-1">Active Polls</h5>
                                <h3 class="font-weight-bold"><?php echo $active_polls; ?></h3>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="text-uppercase text-muted mb-1">Total Votes</h5>
                                <h3 class="font-weight-bold"><?php echo $total_votes_all; ?></h3>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-vote-yea fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="text-uppercase text-muted mb-1">Avg Votes/Poll</h5>
                                <h3 class="font-weight-bold"><?php echo $total_polls > 0 ? round($total_votes_all / $total_polls, 1) : 0; ?></h3>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-bar fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Poll Button -->
        <div class="row mb-4">
            <div class="col-12">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPollModal">
                    <i class="fas fa-plus me-2"></i>Create New Poll
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="closed" <?php echo $filter_status === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Filter
                                    </button>
                                    <a href="editor-polls.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">&nbsp;</label>
                                <div class="text-end">
                                    <small class="text-muted"><?php echo $total_polls; ?> polls found</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Polls List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Polls List</h5>
                        <div>
                            <a href="editor-dashboard.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>

                    <?php if (mysqli_num_rows($polls) > 0): ?>
                        <div class="card-body">
                            <?php while ($poll = mysqli_fetch_assoc($polls)): ?>
                                <div class="poll-card card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h5 class="card-title mb-0">
                                                        <?php echo htmlspecialchars($poll['question']); ?>
                                                    </h5>
                                                    <span class="poll-status status-<?php echo $poll['status']; ?>">
                                                        <?php echo ucfirst($poll['status']); ?>
                                                    </span>
                                                </div>
                                                
                                                <?php if (!empty($poll['description'])): ?>
                                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($poll['description']); ?></p>
                                                <?php endif; ?>
                                                
                                                <div class="row text-center mb-3">
                                                    <div class="col-3">
                                                        <small class="text-muted">Options</small>
                                                        <div class="fw-bold"><?php echo $poll['options_count']; ?></div>
                                                    </div>
                                                    <div class="col-3">
                                                        <small class="text-muted">Votes</small>
                                                        <div class="fw-bold"><?php echo $poll['total_votes']; ?></div>
                                                    </div>
                                                    <div class="col-3">
                                                        <small class="text-muted">Created</small>
                                                        <div class="fw-bold"><?php echo date('M d', strtotime($poll['created_at'])); ?></div>
                                                    </div>
                                                    <div class="col-3">
                                                        <small class="text-muted">Status</small>
                                                        <div class="fw-bold"><?php echo ucfirst($poll['status']); ?></div>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($poll['total_votes'] > 0): ?>
                                                    <div class="mb-2">
                                                        <small class="text-muted">Top Options:</small>
                                                        <?php
                                                        $options_query = "SELECT option_text, votes FROM poll_options WHERE poll_id = {$poll['id']} ORDER BY votes DESC LIMIT 3";
                                                        $options = mysqli_query($conn, $options_query);
                                                        while ($option = mysqli_fetch_assoc($options)) {
                                                            $percentage = $poll['total_votes'] > 0 ? round(($option['votes'] / $poll['total_votes']) * 100, 1) : 0;
                                                            echo "<div class='d-flex justify-content-between align-items-center mb-1'>";
                                                            echo "<span class='small'>" . htmlspecialchars($option['option_text']) . "</span>";
                                                            echo "<span class='small text-muted'>{$option['votes']} votes ({$percentage}%)</span>";
                                                            echo "</div>";
                                                        }
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($poll['ends_at'])): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        Ends: <?php echo date('M d, Y H:i', strtotime($poll['ends_at'])); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="d-flex flex-column align-items-end">
                                                    <div class="btn-group mb-2">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="editPoll(<?php echo $poll['id']; ?>)">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-<?php echo $poll['status'] === 'active' ? 'warning' : 'success'; ?>" 
                                                                onclick="toggleStatus(<?php echo $poll['id']; ?>)">
                                                            <i class="fas fa-<?php echo $poll['status'] === 'active' ? 'pause' : 'play'; ?>"></i> 
                                                            <?php echo $poll['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewResults(<?php echo $poll['id']; ?>)">
                                                            <i class="fas fa-chart-bar"></i> Results
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deletePoll(<?php echo $poll['id']; ?>)">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Polls pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($filter_status); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-poll fa-3x text-muted mb-3"></i>
                            <h5>No polls found</h5>
                            <p class="text-muted">Start by creating your first poll!</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPollModal">
                                <i class="fas fa-plus me-2"></i>Create Your First Poll
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Poll Modal -->
    <div class="modal fade" id="createPollModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Poll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Question *</label>
                            <input type="text" name="question" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Options *</label>
                            <div id="optionsContainer">
                                <div class="input-group mb-2">
                                    <input type="text" name="options[]" class="form-control" placeholder="Option 1" required>
                                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">×</button>
                                </div>
                                <div class="input-group mb-2">
                                    <input type="text" name="options[]" class="form-control" placeholder="Option 2" required>
                                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">×</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addOption()">
                                <i class="fas fa-plus me-1"></i>Add Option
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date (Optional)</label>
                                <input type="datetime-local" name="ends_at" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_poll" class="btn btn-primary">Create Poll</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addOption() {
            const container = document.getElementById('optionsContainer');
            const optionCount = container.children.length + 1;
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" name="options[]" class="form-control" placeholder="Option ${optionCount}">
                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">×</button>
            `;
            container.appendChild(div);
        }
        
        function removeOption(button) {
            const container = document.getElementById('optionsContainer');
            if (container.children.length > 2) {
                button.parentElement.remove();
            } else {
                alert('You must have at least 2 options!');
            }
        }
        
        function editPoll(pollId) {
            // Implementation for editing poll
            window.location.href = 'edit-poll.php?id=' + pollId;
        }
        
        function toggleStatus(pollId) {
            if (confirm('Toggle poll status?')) {
                window.location.href = 'editor-polls.php?action=toggle_status&id=' + pollId;
            }
        }
        
        function viewResults(pollId) {
            // Implementation for viewing detailed results
            window.location.href = 'poll-results.php?id=' + pollId;
        }
        
        function deletePoll(pollId) {
            if (confirm('Delete this poll and all its votes? This action cannot be undone!')) {
                window.location.href = 'editor-polls.php?action=delete&id=' + pollId;
            }
        }
    </script>
</body>
</html>
