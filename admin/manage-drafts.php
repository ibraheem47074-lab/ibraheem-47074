<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'Manage Draft Posts';

// Handle bulk publish action
if (isset($_POST['bulk_publish']) && isset($_POST['draft_ids'])) {
    $draft_ids = $_POST['draft_ids'];
    $published_count = 0;
    
    foreach ($draft_ids as $draft_id) {
        $update_query = "UPDATE news SET status = 'published', published_at = NOW() WHERE id = ? AND status = 'draft'";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'i', $draft_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $published_count++;
        }
    }
    
    $_SESSION['success_message'] = "$published_count draft(s) published successfully!";
    header('Location: manage-drafts.php');
    exit();
}

// Handle single publish action
if (isset($_POST['publish_single']) && isset($_POST['draft_id'])) {
    $draft_id = $_POST['draft_id'];
    $update_query = "UPDATE news SET status = 'published', published_at = NOW() WHERE id = ? AND status = 'draft'";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'i', $draft_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Draft published successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to publish draft.";
    }
    
    header('Location: manage-drafts.php');
    exit();
}

// Handle delete action
if (isset($_POST['delete_draft']) && isset($_POST['draft_id'])) {
    $draft_id = $_POST['draft_id'];
    $delete_query = "DELETE FROM news WHERE id = ? AND status = 'draft'";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $draft_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Draft deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete draft.";
    }
    
    header('Location: manage-drafts.php');
    exit();
}

// Get all draft posts
$drafts_query = "SELECT n.*, c.name as category_name, u.name as author_name
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 LEFT JOIN users u ON n.author_id = u.id 
                 WHERE n.status = 'draft' 
                 ORDER BY n.created_at DESC";
$drafts_result = mysqli_query($conn, $drafts_query);

// Get draft count
$draft_count = mysqli_num_rows($drafts_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .draft-card {
            border-left: 4px solid #ffc107;
            transition: all 0.3s ease;
        }
        .draft-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .draft-card.selected {
            border-left-color: #28a745;
            background-color: #f8f9fa;
        }
        .bulk-actions {
            position: sticky;
            bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 1000;
        }
        .checkbox-wrapper {
            position: relative;
        }
        .draft-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .select-all-checkbox {
            width: 18px;
            height: 18px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-edit me-2"></i>Manage Draft Posts</h2>
                    <div>
                        <span class="badge bg-warning"><?php echo $draft_count; ?> Draft(s)</span>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($draft_count > 0): ?>
                    <!-- Select All Checkbox -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input select-all-checkbox" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">
                                <strong>Select All Drafts</strong>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Draft Posts List -->
                    <div class="row" id="draftsContainer">
                        <?php while ($draft = mysqli_fetch_assoc($drafts_result)): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card draft-card" data-draft-id="<?php echo $draft['id']; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="checkbox-wrapper">
                                                <input type="checkbox" class="form-check-input draft-checkbox" 
                                                       value="<?php echo $draft['id']; ?>" id="draft_<?php echo $draft['id']; ?>">
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="card-title"><?php echo htmlspecialchars($draft['title']); ?></h5>
                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($draft['author_name'] ?? 'Unknown'); ?>
                                                    <span class="ms-3"><i class="fas fa-folder me-1"></i> <?php echo htmlspecialchars($draft['category_name'] ?? 'Uncategorized'); ?></span>
                                                    <span class="ms-3"><i class="fas fa-calendar me-1"></i> Created: <?php echo date('M j, Y', strtotime($draft['created_at'])); ?></span>
                                                </p>
                                                <p class="card-text"><?php echo htmlspecialchars(substr($draft['excerpt'] ?? strip_tags($draft['content'] ?? ''), 0, 150)) . '...'; ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="draft_id" value="<?php echo $draft['id']; ?>">
                                                <button type="submit" name="publish_single" class="btn btn-success btn-sm">
                                                    <i class="fas fa-paper-plane me-1"></i>Publish
                                                </button>
                                            </form>
                                            <a href="edit-news.php?id=<?php echo $draft['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this draft?');">
                                                <input type="hidden" name="draft_id" value="<?php echo $draft['id']; ?>">
                                                <button type="submit" name="delete_draft" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Bulk Actions Bar -->
                    <div class="bulk-actions d-none" id="bulkActions">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span id="selectedCount">0</span> draft(s) selected
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="clearSelection()">
                                    <i class="fas fa-times me-1"></i>Clear Selection
                                </button>
                                <form method="POST" id="bulkPublishForm" style="display: inline;">
                                    <input type="hidden" name="draft_ids" id="selectedDrafts">
                                    <button type="submit" name="bulk_publish" class="btn btn-success">
                                        <i class="fas fa-paper-plane me-1"></i>Publish Selected
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Draft Posts</h4>
                        <p class="text-muted">You don't have any draft posts. Create your first draft!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.draft-checkbox');
            const isChecked = this.checked;
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const card = checkbox.closest('.draft-card');
                if (isChecked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });
            
            updateBulkActions();
        });
        
        // Individual checkbox functionality
        document.querySelectorAll('.draft-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const card = this.closest('.draft-card');
                if (this.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
                updateBulkActions();
                updateSelectAllCheckbox();
            });
        });
        
        // Update bulk actions visibility
        function updateBulkActions() {
            const selectedCheckboxes = document.querySelectorAll('.draft-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            const selectedDrafts = document.getElementById('selectedDrafts');
            
            if (selectedCheckboxes.length > 0) {
                bulkActions.classList.remove('d-none');
                selectedCount.textContent = selectedCheckboxes.length;
                
                // Update hidden input with selected IDs
                const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
                selectedDrafts.value = selectedIds.join(',');
            } else {
                bulkActions.classList.add('d-none');
            }
        }
        
        // Update select all checkbox state
        function updateSelectAllCheckbox() {
            const allCheckboxes = document.querySelectorAll('.draft-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.draft-checkbox:checked');
            const selectAllCheckbox = document.getElementById('selectAll');
            
            selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
            selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
        }
        
        // Clear selection
        function clearSelection() {
            document.querySelectorAll('.draft-checkbox').forEach(checkbox => {
                checkbox.checked = false;
                checkbox.closest('.draft-card').classList.remove('selected');
            });
            document.getElementById('selectAll').checked = false;
            updateBulkActions();
        }
        
        // Prevent form submission if no drafts selected
        document.getElementById('bulkPublishForm').addEventListener('submit', function(e) {
            const selectedDrafts = document.getElementById('selectedDrafts').value;
            if (!selectedDrafts) {
                e.preventDefault();
                alert('Please select at least one draft to publish.');
            }
        });
    </script>
</body>
</html>
