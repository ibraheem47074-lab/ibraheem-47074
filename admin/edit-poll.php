<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$poll_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if ($poll_id === 0) {
    redirect('manage-polls.php');
}

// Get poll data
$query = "SELECT * FROM polls WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $poll_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$poll = mysqli_fetch_assoc($result);

if (!$poll) {
    redirect('manage-polls.php');
}

// Get poll options
$options_query = "SELECT * FROM poll_options WHERE poll_id = ? ORDER BY id ASC";
$options_stmt = mysqli_prepare($conn, $options_query);
mysqli_stmt_bind_param($options_stmt, 'i', $poll_id);
mysqli_stmt_execute($options_stmt);
$options_result = mysqli_stmt_get_result($options_stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = clean_input($_POST['question']);
    $status = clean_input($_POST['status']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $allow_multiple = isset($_POST['allow_multiple']) ? 1 : 0;
    
    // Validate dates
    if (!empty($start_date) && !empty($end_date)) {
        if (strtotime($start_date) > strtotime($end_date)) {
            $error = "End date must be after start date";
        }
    }
    
    if (empty($error)) {
        // Update poll
        $query = "UPDATE polls SET question = ?, status = ?, start_date = ?, 
                  end_date = ?, allow_multiple = ? WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssssi', $question, $status, $start_date, 
                               $end_date, $allow_multiple, $poll_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Poll updated successfully!";
            
            // Update poll options
            if (isset($_POST['options']) && is_array($_POST['options'])) {
                // Delete existing options
                $delete_query = "DELETE FROM poll_options WHERE poll_id = ?";
                $delete_stmt = mysqli_prepare($conn, $delete_query);
                mysqli_stmt_bind_param($delete_stmt, 'i', $poll_id);
                mysqli_stmt_execute($delete_stmt);
                
                // Insert new options
                $insert_query = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
                $insert_stmt = mysqli_prepare($conn, $insert_query);
                
                foreach ($_POST['options'] as $option) {
                    $option_text = clean_input($option);
                    if (!empty($option_text)) {
                        mysqli_stmt_bind_param($insert_stmt, 'is', $poll_id, $option_text);
                        mysqli_stmt_execute($insert_stmt);
                    }
                }
            }
            
            // Refresh poll data
            $poll = array_merge($poll, $_POST);
        } else {
            $error = "Error updating poll: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Poll - PK Live News Admin</title>
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
        .form-label {
            font-weight: 600;
        }
        .poll-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .poll-option {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        .poll-option:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .option-number {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        .add-option-btn {
            border: 2px dashed #667eea;
            background: transparent;
            color: #667eea;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .add-option-btn:hover {
            background: #667eea;
            color: white;
        }
        .remove-option-btn {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .remove-option-btn:hover {
            background: #c82333;
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
                        <h1 class="h3 mb-0">Edit Poll</h1>
                        <small>Update poll question and options</small>
                    </div>
                    <div>
                        <a href="manage-polls.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Polls
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

                <!-- Edit Poll Form -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" id="pollForm">
                                    <!-- Poll Question -->
                                    <div class="mb-4">
                                        <label for="question" class="form-label">Poll Question *</label>
                                        <textarea class="form-control" id="question" name="question" rows="3" 
                                                  placeholder="Enter your poll question" 
                                                  required><?php echo htmlspecialchars($poll['question']); ?></textarea>
                                        <small class="text-muted">The question that users will vote on</small>
                                    </div>

                                    <!-- Poll Options -->
                                    <div class="mb-4">
                                        <label class="form-label">Poll Options</label>
                                        <div id="pollOptions">
                                            <?php 
                                            $option_num = 1;
                                            while ($option = mysqli_fetch_assoc($options_result)): 
                                            ?>
                                                <div class="poll-option d-flex align-items-center" data-option-id="<?php echo $option['id']; ?>">
                                                    <div class="option-number"><?php echo $option_num++; ?></div>
                                                    <input type="text" class="form-control" name="options[]" 
                                                           value="<?php echo htmlspecialchars($option['option_text']); ?>" 
                                                           placeholder="Option text">
                                                    <button type="button" class="btn btn-sm btn-danger remove-option-btn ms-2" 
                                                            onclick="removeOption(this)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                        
                                        <button type="button" class="add-option-btn" onclick="addOption()">
                                            <i class="fas fa-plus me-2"></i>Add Option
                                        </button>
                                    </div>

                                    <!-- Poll Settings -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo ($poll['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($poll['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                <option value="expired" <?php echo ($poll['status'] == 'expired') ? 'selected' : ''; ?>>Expired</option>
                                            </select>
                                            <small class="text-muted">Choose poll status</small>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="allow_multiple" class="form-label">Multiple Selection</label>
                                            <select class="form-select" id="allow_multiple" name="allow_multiple">
                                                <option value="0" <?php echo (!$poll['allow_multiple']) ? 'selected' : ''; ?>>Single Choice</option>
                                                <option value="1" <?php echo ($poll['allow_multiple']) ? 'selected' : ''; ?>>Multiple Choices</option>
                                            </select>
                                            <small class="text-muted">Allow users to select multiple options</small>
                                        </div>
                                    </div>

                                    <!-- Date Settings -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                                   value="<?php echo htmlspecialchars($poll['start_date']); ?>">
                                            <small class="text-muted">When poll becomes active</small>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                                   value="<?php echo htmlspecialchars($poll['end_date']); ?>">
                                            <small class="text-muted">When poll expires</small>
                                        </div>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Poll
                                        </button>
                                        <a href="manage-polls.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Poll Preview -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-eye me-2"></i>Poll Preview
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="poll-preview">
                                    <h5><?php echo htmlspecialchars($poll['question'] ?: 'Your poll question will appear here'); ?></h5>
                                    <div id="previewOptions">
                                        <?php 
                                        mysqli_data_seek($options_result, 0);
                                        while ($option = mysqli_fetch_assoc($options_result)): 
                                        ?>
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="option-number"><?php echo $option_num++; ?></div>
                                                <div class="flex-grow-1">
                                                    <input type="<?php echo $poll['allow_multiple'] ? 'checkbox' : 'radio'; ?>" 
                                                           name="preview_vote" disabled>
                                                    <label><?php echo htmlspecialchars($option['option_text']); ?></label>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <small class="text-muted">This is how users will see your poll</small>
                                </div>
                            </div>
                        </div>

                        <!-- Poll Statistics -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Poll Statistics
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php
                                // Get vote statistics
                                $votes_query = "SELECT COUNT(*) as total_votes FROM poll_votes WHERE poll_id = ?";
                                $votes_stmt = mysqli_prepare($conn, $votes_query);
                                mysqli_stmt_bind_param($votes_stmt, 'i', $poll_id);
                                mysqli_stmt_execute($votes_stmt);
                                $votes_result = mysqli_stmt_get_result($votes_stmt);
                                $total_votes = mysqli_fetch_assoc($votes_result)['total_votes'];
                                
                                // Get option vote counts
                                $option_votes_query = "SELECT po.option_text, COUNT(pv.id) as vote_count 
                                                     FROM poll_options po 
                                                     LEFT JOIN poll_votes pv ON po.id = pv.option_id 
                                                     WHERE po.poll_id = ? 
                                                     GROUP BY po.id, po.option_text 
                                                     ORDER BY vote_count DESC";
                                $option_votes_stmt = mysqli_prepare($conn, $option_votes_query);
                                mysqli_stmt_bind_param($option_votes_stmt, 'i', $poll_id);
                                mysqli_stmt_execute($option_votes_stmt);
                                $option_votes_result = mysqli_stmt_get_result($option_votes_stmt);
                                ?>
                                <div class="mb-3">
                                    <strong>Total Votes:</strong> <?php echo number_format($total_votes ?? 0); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Options:</strong> <?php echo mysqli_num_rows($options_result); ?>
                                </div>
                                <div>
                                    <small class="text-muted">Vote distribution:</small>
                                    <?php while ($vote_data = mysqli_fetch_assoc($option_votes_result)): ?>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><?php echo htmlspecialchars($vote_data['option_text']); ?></span>
                                                <span class="badge bg-primary"><?php echo $vote_data['vote_count']; ?> votes</span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
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
        let optionCount = <?php echo mysqli_num_rows($options_result); ?>;

        // Add new option
        function addOption() {
            optionCount++;
            const optionsContainer = document.getElementById('pollOptions');
            const newOption = document.createElement('div');
            newOption.className = 'poll-option d-flex align-items-center';
            newOption.innerHTML = `
                <div class="option-number">${optionCount}</div>
                <input type="text" class="form-control" name="options[]" placeholder="Option text">
                <button type="button" class="btn btn-sm btn-danger remove-option-btn ms-2" onclick="removeOption(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            optionsContainer.appendChild(newOption);
            updatePreview();
        }

        // Remove option
        function removeOption(button) {
            if (confirm('Are you sure you want to remove this option?')) {
                button.closest('.poll-option').remove();
                updatePreview();
            }
        }

        // Update preview
        function updatePreview() {
            const question = document.getElementById('question').value;
            const previewOptions = document.getElementById('previewOptions');
            const options = document.querySelectorAll('input[name="options[]"]');
            const allowMultiple = document.getElementById('allow_multiple').value;
            
            let previewHTML = '';
            if (question) {
                previewHTML = `<h5>${question}</h5>`;
            } else {
                previewHTML = '<h5>Your poll question will appear here</h5>';
            }
            
            options.forEach((option, index) => {
                if (option.value.trim()) {
                    previewHTML += `
                        <div class="d-flex align-items-center mb-2">
                            <div class="option-number">${index + 1}</div>
                            <div class="flex-grow-1">
                                <input type="${allowMultiple === '1' ? 'checkbox' : 'radio'}" name="preview_vote" disabled>
                                <label>${option.value}</label>
                            </div>
                        </div>
                    `;
                }
            });
            
            previewOptions.innerHTML = previewHTML;
        }

        // Form validation
        document.getElementById('pollForm').addEventListener('submit', function(e) {
            const question = document.getElementById('question').value.trim();
            const options = document.querySelectorAll('input[name="options[]"]');
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (!question) {
                e.preventDefault();
                alert('Please enter poll question');
                return false;
            }
            
            // Check if at least 2 options
            let validOptions = 0;
            options.forEach(option => {
                if (option.value.trim()) {
                    validOptions++;
                }
            });
            
            if (validOptions < 2) {
                e.preventDefault();
                alert('Please add at least 2 poll options');
                return false;
            }
            
            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                e.preventDefault();
                alert('End date must be after start date');
                return false;
            }
            
            return true;
        });

        // Auto-save draft
        let autoSaveTimer;
        function autoSave() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                const formData = new FormData(document.getElementById('pollForm'));
                formData.append('auto_save', '1');
                
                fetch('edit-poll.php?id=<?php echo $poll_id; ?>', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                  .then(data => console.log('Auto-saved'))
                  .catch(error => console.error('Auto-save failed:', error));
            }, 30000); // Auto-save after 30 seconds of inactivity
        }

        // Listen for changes
        document.getElementById('question').addEventListener('input', updatePreview);
        document.getElementById('allow_multiple').addEventListener('change', updatePreview);
        
        // Listen for option changes
        document.getElementById('pollOptions').addEventListener('input', function(e) {
            if (e.target.classList.contains('form-control')) {
                updatePreview();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 's':
                        e.preventDefault();
                        document.getElementById('pollForm').submit();
                        break;
                    case 'a':
                        e.preventDefault();
                        addOption();
                        break;
                    case 'd':
                        e.preventDefault();
                        if (confirm('Add new option?')) {
                            addOption();
                        }
                        break;
                }
            }
        });
    </script>
</body>
</html>
