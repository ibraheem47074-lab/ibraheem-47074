<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = clean_input($_POST['question']);
    $poll_duration = (int)$_POST['poll_duration'];
    $options = array_filter($_POST['options']);
    $status = clean_input($_POST['status']);
    
    // Validation
    if (empty($question)) {
        $error = 'Poll question is required';
    } elseif (count($options ?? []) < 2) {
        $error = 'At least 2 options are required';
    } elseif (count($options ?? []) > 10) {
        $error = 'Maximum 10 options allowed';
    } elseif (empty($options[0]) || empty($options[1])) {
        $error = 'First 2 options cannot be empty';
    } else {
        // Calculate expiration time
        $ends_at = null;
        if ($poll_duration > 0) {
            $ends_at = date('Y-m-d H:i:s', strtotime("+$poll_duration days"));
        }
        
        // Insert poll
        $query = "INSERT INTO polls (question, status, ends_at) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sss', $question, $status, $ends_at);
        
        if (mysqli_stmt_execute($stmt)) {
            $poll_id = mysqli_insert_id($conn);
            
            // Insert options
            $option_query = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
            $option_stmt = mysqli_prepare($conn, $option_query);
            
            foreach ($options as $option) {
                if (!empty(trim($option))) {
                    mysqli_stmt_bind_param($option_stmt, 'is', $poll_id, $option);
                    mysqli_stmt_execute($option_stmt);
                }
            }
            
            $success = "Poll created successfully with " . count(array_filter($options)) . " options!";
            $_POST = array();
        } else {
            $error = "Error creating poll: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Poll - PK Live News Admin</title>
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
        .option-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        .option-item:hover {
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
        .remove-option {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #dc3545;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .option-item:hover .remove-option {
            opacity: 1;
        }
        .poll-preview {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .form-label {
            font-weight: 600;
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
                        <h1 class="h3 mb-0">Add Poll</h1>
                        <small>Create engaging polls for your audience</small>
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

                <!-- Add Poll Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="pollForm">
                            <!-- Poll Question -->
                            <div class="mb-4">
                                <label for="question" class="form-label">Poll Question *</label>
                                <input type="text" class="form-control" id="question" name="question" 
                                       value="<?php echo isset($_POST['question']) ? htmlspecialchars($_POST['question']) : ''; ?>" 
                                       placeholder="What would you like to ask your audience?" 
                                       required maxlength="255">
                                <small class="text-muted">Maximum 255 characters</small>
                            </div>

                            <!-- Poll Options -->
                            <div class="mb-4">
                                <label class="form-label">Poll Options *</label>
                                <small class="text-muted mb-2">Add at least 2 options (maximum 10)</small>
                                
                                <div id="optionsContainer">
                                    <?php 
                                    $options = isset($_POST['options']) ? $_POST['options'] : ['', '', '', '', ''];
                                    foreach ($options as $index => $option): 
                                    ?>
                                        <div class="option-item position-relative mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="option-number"><?php echo $index + 1; ?></div>
                                                <input type="text" class="form-control" name="options[]" 
                                                       value="<?php echo htmlspecialchars($option); ?>" 
                                                       placeholder="Enter option <?php echo $index + 1; ?>" 
                                                       required>
                                                <i class="fas fa-times remove-option" onclick="removeOption(this)"></i>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOption()">
                                    <i class="fas fa-plus me-2"></i>Add Option
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Poll Duration -->
                                    <div class="mb-4">
                                        <label for="poll_duration" class="form-label">Poll Duration</label>
                                        <select class="form-select" id="poll_duration" name="poll_duration">
                                            <option value="0" <?php echo isset($_POST['poll_duration']) && $_POST['poll_duration'] == 0 ? 'selected' : ''; ?>>Never expires</option>
                                            <option value="1" <?php echo isset($_POST['poll_duration']) && $_POST['poll_duration'] == 1 ? 'selected' : ''; ?>>1 day</option>
                                            <option value="3" <?php echo isset($_POST['poll_duration']) && $_POST['poll_duration'] == 3 ? 'selected' : ''; ?>>3 days</option>
                                            <option value="7" <?php echo isset($_POST['poll_duration']) && $_POST['poll_duration'] == 7 ? 'selected' : ''; ?>>1 week</option>
                                            <option value="14" <?php echo isset($_POST['poll_duration']) && $_POST['poll_duration'] == 14 ? 'selected' : ''; ?>>2 weeks</option>
                                            <option value="30" <?php echo isset($_POST['poll_duration']) && $_POST['poll_duration'] == 30 ? 'selected' : ''; ?>>1 month</option>
                                        </select>
                                        <small class="text-muted">Poll will automatically close after this period</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <!-- Status -->
                                    <div class="mb-4">
                                        <label for="status" class="form-label">Initial Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo isset($_POST['status']) && $_POST['status'] == 'active' ? 'selected' : ''; ?>>Active (Start Immediately)</option>
                                            <option value="inactive" <?php echo isset($_POST['status']) && $_POST['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive (Draft)</option>
                                        </select>
                                        <small class="text-muted">Choose whether poll starts immediately or as draft</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Poll Preview -->
                            <div class="poll-preview">
                                <h6 class="mb-3">
                                    <i class="fas fa-eye me-2"></i>Poll Preview
                                </h6>
                                <div id="pollPreview">
                                    <p class="text-muted mb-2">Your poll will appear like this to users:</p>
                                    <div class="mb-3">
                                        <strong id="previewQuestion"><?php echo isset($_POST['question']) ? htmlspecialchars($_POST['question']) : 'Your poll question...'; ?></strong>
                                    </div>
                                    <div id="previewOptions">
                                        <?php 
                                        $preview_options = isset($_POST['options']) ? array_filter($_POST['options']) : ['Option 1', 'Option 2'];
                                        foreach ($preview_options as $index => $option): 
                                        ?>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-secondary me-2"><?php echo chr(65 + $index); ?></span>
                                                <span><?php echo htmlspecialchars($option) ?: 'Option ' . ($index + 1); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        <span id="previewDuration">
                                            <?php 
                                            if (isset($_POST['poll_duration']) && $_POST['poll_duration'] > 0) {
                                                echo 'Closes after ' . $_POST['poll_duration'] . ' days';
                                            } else {
                                                echo 'Never expires';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Poll
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="fas fa-times me-2"></i>Clear Form
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="saveAsDraft()">
                                    <i class="fas fa-save me-2"></i>Save as Draft
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Poll Tips -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>Poll Creation Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-question-circle me-2 text-primary"></i>Good Questions</h6>
                                <ul class="small">
                                    <li>Keep questions clear and concise</li>
                                    <li>Avoid yes/no questions</li>
                                    <li>Make it relevant to current events</li>
                                    <li>Use neutral language</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-list-ul me-2 text-success"></i>Option Guidelines</h6>
                                <ul class="small">
                                    <li>Provide 2-6 clear options</li>
                                    <li>Make options mutually exclusive</li>
                                    <li>Keep options similar in length</li>
                                    <li>Avoid "All of the above"</li>
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
        let optionCount = <?php echo count(isset($_POST['options']) ? array_filter($_POST['options']) : ['', '', '', '', '']); ?>;

        // Add new option
        function addOption() {
            if (optionCount >= 10) {
                alert('Maximum 10 options allowed');
                return;
            }
            
            const container = document.getElementById('optionsContainer');
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-item position-relative mb-3';
            optionDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="option-number">${optionCount + 1}</div>
                    <input type="text" class="form-control" name="options[]" 
                           placeholder="Enter option ${optionCount + 1}" required>
                    <i class="fas fa-times remove-option" onclick="removeOption(this)"></i>
                </div>
            `;
            
            container.appendChild(optionDiv);
            optionCount++;
            updatePreview();
        }

        // Remove option
        function removeOption(element) {
            const optionItem = element.closest('.option-item');
            optionItem.remove();
            
            // Renumber remaining options
            const options = document.querySelectorAll('.option-item');
            options.forEach((option, index) => {
                option.querySelector('.option-number').textContent = index + 1;
                option.querySelector('input').placeholder = `Enter option ${index + 1}`;
            });
            
            optionCount = options.length;
            updatePreview();
        }

        // Update preview
        function updatePreview() {
            const question = document.getElementById('question').value;
            const options = Array.from(document.querySelectorAll('input[name="options[]"]'))
                .map(input => input.value)
                .filter(value => value.trim() !== '');
            
            // Update preview question
            document.getElementById('previewQuestion').textContent = question || 'Your poll question...';
            
            // Update preview options
            const previewOptions = document.getElementById('previewOptions');
            if (options.length > 0) {
                previewOptions.innerHTML = options.map((option, index) => `
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-secondary me-2">${String.fromCharCode(65 + index)}</span>
                        <span>${option || 'Option ' + (index + 1)}</span>
                    </div>
                `).join('');
            } else {
                previewOptions.innerHTML = '<p class="text-muted">Add at least 2 options to see preview</p>';
            }
            
            // Update duration preview
            const duration = document.getElementById('poll_duration').value;
            const durationText = duration > 0 ? `Closes after ${duration} days` : 'Never expires';
            document.getElementById('previewDuration').textContent = durationText;
        }

        // Reset form
        function resetForm() {
            document.getElementById('pollForm').reset();
            
            // Reset to 4 default options
            const container = document.getElementById('optionsContainer');
            container.innerHTML = '';
            optionCount = 4;
            
            for (let i = 0; i < 4; i++) {
                addOption();
            }
            
            updatePreview();
        }

        // Save as draft
        function saveAsDraft() {
            document.getElementById('status').value = 'inactive';
            document.getElementById('pollForm').submit();
        }

        // Auto-update preview on input changes
        document.getElementById('question').addEventListener('input', updatePreview);
        document.getElementById('poll_duration').addEventListener('change', updatePreview);

        // Add event listeners to existing options
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[name="options[]"]').forEach(input => {
                input.addEventListener('input', updatePreview);
            });
            
            updatePreview();
        });

        // Form validation
        document.getElementById('pollForm').addEventListener('submit', function(e) {
            const options = Array.from(document.querySelectorAll('input[name="options[]"]'))
                .map(input => input.value.trim())
                .filter(value => value !== '');
            
            if (options.length < 2) {
                e.preventDefault();
                alert('At least 2 options are required for a poll');
                return false;
            }
            
            if (options.length > 10) {
                e.preventDefault();
                alert('Maximum 10 options allowed');
                return false;
            }
            
            return true;
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 's':
                        e.preventDefault();
                        document.getElementById('pollForm').submit();
                        break;
                    case 'd':
                        e.preventDefault();
                        resetForm();
                        break;
                    case 'a':
                        e.preventDefault();
                        addOption();
                        break;
                }
            }
        });
    </script>
</body>
</html>
