<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$page_title = 'Deployment Feedback Management';
$error = '';
$success = '';

// Handle feedback actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_feedback_status':
            $feedback_id = (int)$_POST['feedback_id'];
            $status = clean_input($_POST['status']);
            $priority = clean_input($_POST['priority']);
            $assigned_to = (int)$_POST['assigned_to'];
            $admin_notes = clean_input($_POST['admin_notes']);
            
            $query = "UPDATE deployment_feedback 
                     SET status = ?, priority = ?, assigned_to = ?, admin_notes = ?, updated_at = NOW()
                     WHERE id = ?";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssisi", $status, $priority, $assigned_to, $admin_notes, $feedback_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Feedback updated successfully!';
                
                // Create notification for assigned user
                if ($assigned_to > 0) {
                    $notification_query = "INSERT INTO feedback_notifications 
                                        (feedback_id, user_id, notification_type, message)
                                        VALUES (?, ?, 'status_changed', ?)";
                    $notification_stmt = mysqli_prepare($conn, $notification_query);
                    $message = "Feedback #$feedback_id has been assigned to you with status: $status";
                    mysqli_stmt_bind_param($notification_stmt, "iis", $feedback_id, $assigned_to, $message);
                    mysqli_stmt_execute($notification_stmt);
                }
            } else {
                $error = 'Error updating feedback: ' . mysqli_error($conn);
            }
            break;
            
        case 'add_response':
            $feedback_id = (int)$_POST['feedback_id'];
            $response_text = clean_input($_POST['response_text']);
            $response_type = clean_input($_POST['response_type']);
            $is_public = isset($_POST['is_public']) ? 1 : 0;
            
            $query = "INSERT INTO feedback_responses 
                     (feedback_id, responder_id, response_text, response_type, is_public)
                     VALUES (?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "iisii", $feedback_id, $_SESSION['user_id'], $response_text, $response_type, $is_public);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Response added successfully!';
                
                // Create notification for feedback submitter
                $feedback_query = "SELECT user_id FROM deployment_feedback WHERE id = ?";
                $feedback_stmt = mysqli_prepare($conn, $feedback_query);
                mysqli_stmt_bind_param($feedback_stmt, "i", $feedback_id);
                mysqli_stmt_execute($feedback_stmt);
                $feedback_result = mysqli_stmt_get_result($feedback_stmt);
                $feedback_data = mysqli_fetch_assoc($feedback_result);
                
                if ($feedback_data['user_id']) {
                    $notification_query = "INSERT INTO feedback_notifications 
                                        (feedback_id, user_id, notification_type, message)
                                        VALUES (?, ?, 'response_added', ?)";
                    $notification_stmt = mysqli_prepare($conn, $notification_query);
                    $message = "A response has been added to your feedback #$feedback_id";
                    mysqli_stmt_bind_param($notification_stmt, "iis", $feedback_id, $feedback_data['user_id'], $message);
                    mysqli_stmt_execute($notification_stmt);
                }
            } else {
                $error = 'Error adding response: ' . mysqli_error($conn);
            }
            break;
            
        case 'add_tag':
            $feedback_id = (int)$_POST['feedback_id'];
            $tag_name = clean_input($_POST['tag_name']);
            
            // Check if tag exists
            $tag_query = "SELECT id FROM feedback_tags WHERE tag_name = ?";
            $tag_stmt = mysqli_prepare($conn, $tag_query);
            mysqli_stmt_bind_param($tag_stmt, "s", $tag_name);
            mysqli_stmt_execute($tag_stmt);
            $tag_result = mysqli_stmt_get_result($tag_stmt);
            
            if ($tag_data = mysqli_fetch_assoc($tag_result)) {
                $tag_id = $tag_data['id'];
            } else {
                // Create new tag
                $insert_tag_query = "INSERT INTO feedback_tags (tag_name) VALUES (?)";
                $insert_tag_stmt = mysqli_prepare($conn, $insert_tag_query);
                mysqli_stmt_bind_param($insert_tag_stmt, "s", $tag_name);
                mysqli_stmt_execute($insert_tag_stmt);
                $tag_id = mysqli_insert_id($conn);
            }
            
            // Add tag relation
            $relation_query = "INSERT IGNORE INTO feedback_tag_relations (feedback_id, tag_id) VALUES (?, ?)";
            $relation_stmt = mysqli_prepare($conn, $relation_query);
            mysqli_stmt_bind_param($relation_stmt, "ii", $feedback_id, $tag_id);
            
            if (mysqli_stmt_execute($relation_stmt)) {
                $success = 'Tag added successfully!';
                // Update tag usage count
                $update_query = "UPDATE feedback_tags SET usage_count = usage_count + 1 WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "i", $tag_id);
                mysqli_stmt_execute($update_stmt);
            } else {
                $error = 'Error adding tag: ' . mysqli_error($conn);
            }
            break;
            
        case 'delete_feedback':
            $feedback_id = (int)$_POST['feedback_id'];
            $query = "DELETE FROM deployment_feedback WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $feedback_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Feedback deleted successfully!';
            } else {
                $error = 'Error deleting feedback: ' . mysqli_error($conn);
            }
            break;
    }
}

// Get feedback with filters
$status_filter = $_GET['status'] ?? 'all';
$type_filter = $_GET['type'] ?? 'all';
$rating_filter = $_GET['rating'] ?? 'all';
$priority_filter = $_GET['priority'] ?? 'all';

$where_conditions = [];
$params = [];
$types = '';

if ($status_filter !== 'all') {
    $where_conditions[] = "df.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($type_filter !== 'all') {
    $where_conditions[] = "df.feedback_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

if ($rating_filter !== 'all') {
    $where_conditions[] = "df.rating = ?";
    $params[] = $rating_filter;
    $types .= 'i';
}

if ($priority_filter !== 'all') {
    $where_conditions[] = "df.priority = ?";
    $params[] = $priority_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$query = "SELECT df.*, ld.deployment_name, ld.title as deployment_title,
          u.name as user_name, u.email as user_email,
          assigned.name as assigned_name,
          (SELECT COUNT(*) FROM feedback_responses fr WHERE fr.feedback_id = df.id) as response_count,
          GROUP_CONCAT(ft.tag_name) as tags
          FROM deployment_feedback df 
          LEFT JOIN live_deployments ld ON df.deployment_id = ld.id 
          LEFT JOIN users u ON df.user_id = u.id 
          LEFT JOIN users assigned ON df.assigned_to = assigned.id
          LEFT JOIN feedback_tag_relations ftr ON df.id = ftr.feedback_id
          LEFT JOIN feedback_tags ft ON ftr.tag_id = ft.id
          $where_clause
          GROUP BY df.id
          ORDER BY df.created_at DESC";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_feedback,
    AVG(rating) as avg_rating,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_feedback,
    SUM(CASE WHEN priority = 'critical' THEN 1 ELSE 0 END) as critical_feedback,
    SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as low_ratings
    FROM deployment_feedback";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Get users for assignment
$users_query = "SELECT id, name, email FROM users WHERE role IN ('admin', 'editor') ORDER BY name";
$users_result = mysqli_query($conn, $users_query);

// Get categories
$categories_result = mysqli_query($conn, "SELECT * FROM feedback_categories WHERE is_active = TRUE ORDER BY category_name");

// Get popular tags
$tags_query = "SELECT * FROM feedback_tags ORDER BY usage_count DESC, tag_name ASC LIMIT 20";
$tags_result = mysqli_query($conn, $tags_query);
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-comments me-2"></i>Deployment Feedback Management</h1>
            <p class="text-muted">Manage and respond to user feedback for live deployments</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($stats['total_feedback'] ?? 0); ?></h4>
                            <p class="mb-0">Total Feedback</p>
                        </div>
                        <i class="fas fa-comment-dots fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($stats['pending_feedback'] ?? 0); ?></h4>
                            <p class="mb-0">Pending</p>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo round($stats['avg_rating'] ?? 0, 1); ?></h4>
                            <p class="mb-0">Avg Rating</p>
                        </div>
                        <i class="fas fa-star fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($stats['critical_feedback'] ?? 0); ?></h4>
                            <p class="mb-0">Critical</p>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status Filter</label>
                    <select class="form-select" id="statusFilter" onchange="filterFeedback()">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="reviewed" <?php echo $status_filter === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                        <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="dismissed" <?php echo $status_filter === 'dismissed' ? 'selected' : ''; ?>>Dismissed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="typeFilter" class="form-label">Type Filter</label>
                    <select class="form-select" id="typeFilter" onchange="filterFeedback()">
                        <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="quality" <?php echo $type_filter === 'quality' ? 'selected' : ''; ?>>Quality</option>
                        <option value="performance" <?php echo $type_filter === 'performance' ? 'selected' : ''; ?>>Performance</option>
                        <option value="content" <?php echo $type_filter === 'content' ? 'selected' : ''; ?>>Content</option>
                        <option value="technical" <?php echo $type_filter === 'technical' ? 'selected' : ''; ?>>Technical</option>
                        <option value="general" <?php echo $type_filter === 'general' ? 'selected' : ''; ?>>General</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="ratingFilter" class="form-label">Rating Filter</label>
                    <select class="form-select" id="ratingFilter" onchange="filterFeedback()">
                        <option value="all" <?php echo $rating_filter === 'all' ? 'selected' : ''; ?>>All Ratings</option>
                        <option value="5" <?php echo $rating_filter === '5' ? 'selected' : ''; ?>>5 Stars</option>
                        <option value="4" <?php echo $rating_filter === '4' ? 'selected' : ''; ?>>4 Stars</option>
                        <option value="3" <?php echo $rating_filter === '3' ? 'selected' : ''; ?>>3 Stars</option>
                        <option value="2" <?php echo $rating_filter === '2' ? 'selected' : ''; ?>>2 Stars</option>
                        <option value="1" <?php echo $rating_filter === '1' ? 'selected' : ''; ?>>1 Star</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="priorityFilter" class="form-label">Priority Filter</label>
                    <select class="form-select" id="priorityFilter" onchange="filterFeedback()">
                        <option value="all" <?php echo $priority_filter === 'all' ? 'selected' : ''; ?>>All Priorities</option>
                        <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="critical" <?php echo $priority_filter === 'critical' ? 'selected' : ''; ?>>Critical</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Feedback Items</h5>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" onclick="exportFeedback()">
                    <i class="fas fa-download"></i> Export
                </button>
                <button class="btn btn-outline-success" onclick="bulkAssign()">
                    <i class="fas fa-user-plus"></i> Bulk Assign
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Feedback</th>
                            <th>User</th>
                            <th>Deployment</th>
                            <th>Rating</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Tags</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($feedback = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="feedback-checkbox" value="<?php echo $feedback['id']; ?>">
                                </td>
                                <td>
                                    <div class="feedback-preview">
                                        <div class="rating-stars mb-1">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars(substr($feedback['feedback_text'], 0, 100)) . '...'; ?></p>
                                        <small class="text-muted"><?php echo time_ago($feedback['created_at']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($feedback['user_name']): ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($feedback['user_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($feedback['user_email']); ?></small>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Anonymous</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($feedback['deployment_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($feedback['deployment_title']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo getRatingColor($feedback['rating']); ?>">
                                        <?php echo $feedback['rating']; ?>★
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo getFeedbackTypeColor($feedback['feedback_type']); ?>">
                                        <?php echo ucfirst($feedback['feedback_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusColor($feedback['status']); ?>">
                                        <?php echo ucfirst($feedback['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo getPriorityColor($feedback['priority']); ?>">
                                        <?php echo ucfirst($feedback['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($feedback['tags']): ?>
                                        <div class="tag-list">
                                            <?php foreach (explode(',', $feedback['tags']) as $tag): ?>
                                                <span class="badge bg-secondary me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewFeedback(<?php echo $feedback['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="respondToFeedback(<?php echo $feedback['id']; ?>)">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="editFeedback(<?php echo $feedback['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteFeedback(<?php echo $feedback['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Details Modal -->
<div class="modal fade" id="feedbackDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Feedback Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="feedbackDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Response</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_response">
                    <input type="hidden" id="response_feedback_id" name="feedback_id">
                    
                    <div class="mb-3">
                        <label for="response_type" class="form-label">Response Type</label>
                        <select class="form-select" id="response_type" name="response_type">
                            <option value="comment">Comment</option>
                            <option value="resolution">Resolution</option>
                            <option value="request_info">Request Information</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="response_text" class="form-label">Response</label>
                        <textarea class="form-control" id="response_text" name="response_text" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_public" name="is_public">
                            <label class="form-check-label" for="is_public">
                                Make response visible to user
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Response</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Feedback Modal -->
<div class="modal fade" id="editFeedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_feedback_status">
                    <input type="hidden" id="edit_feedback_id" name="feedback_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status">
                                    <option value="pending">Pending</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="dismissed">Dismissed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_priority" class="form-label">Priority</label>
                                <select class="form-select" id="edit_priority" name="priority">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign To</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">Unassigned</option>
                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">Admin Notes</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_tag" class="form-label">Add Tag</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="add_tag" placeholder="Enter tag name">
                            <button class="btn btn-outline-secondary" type="button" onclick="addTagToFeedback()">Add Tag</button>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Popular tags: </small>
                            <?php while ($tag = mysqli_fetch_assoc($tags_result)): ?>
                                <span class="badge bg-secondary me-1" style="cursor: pointer;" onclick="setTagValue('<?php echo htmlspecialchars($tag['tag_name']); ?>')">
                                    <?php echo htmlspecialchars($tag['tag_name']); ?>
                                </span>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Filter feedback
function filterFeedback() {
    const status = document.getElementById('statusFilter').value;
    const type = document.getElementById('typeFilter').value;
    const rating = document.getElementById('ratingFilter').value;
    const priority = document.getElementById('priorityFilter').value;
    
    window.location.href = `?status=${status}&type=${type}&rating=${rating}&priority=${priority}`;
}

// Toggle select all checkboxes
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.feedback-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// View feedback details
function viewFeedback(feedbackId) {
    fetch(`api/feedback-api.php?action=get_details&feedback_id=${feedbackId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = new bootstrap.Modal(document.getElementById('feedbackDetailsModal'));
                const content = document.getElementById('feedbackDetailsContent');
                
                const feedback = data.feedback;
                const responses = data.responses;
                
                let responsesHtml = '';
                responses.forEach(response => {
                    responsesHtml += `
                        <div class="alert alert-info">
                            <strong>${response.responder_name}</strong> - ${response.response_type}
                            <br>
                            ${response.response_text}
                            <br>
                            <small class="text-muted">${response.created_at}</small>
                        </div>
                    `;
                });
                
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Feedback Content</h6>
                            <div class="rating-stars mb-2">
                                ${generateStars(feedback.rating)}
                            </div>
                            <p>${feedback.feedback_text}</p>
                            
                            <h6 class="mt-3">Technical Details</h6>
                            <table class="table table-sm">
                                <tr><td>Video Quality:</td><td>${feedback.video_quality || 'N/A'}</td></tr>
                                <tr><td>Audio Quality:</td><td>${feedback.audio_quality || 'N/A'}</td></tr>
                                <tr><td>Stream Stability:</td><td>${feedback.stream_stability || 'N/A'}</td></tr>
                                <tr><td>Loading Speed:</td><td>${feedback.loading_speed || 'N/A'}</td></tr>
                            </table>
                            
                            <h6 class="mt-3">Device Information</h6>
                            <table class="table table-sm">
                                <tr><td>Device Type:</td><td>${feedback.device_type}</td></tr>
                                <tr><td>Browser:</td><td>${feedback.browser || 'N/A'}</td></tr>
                                <tr><td>Connection:</td><td>${feedback.connection_type}</td></tr>
                                <tr><td>Watch Duration:</td><td>${feedback.watch_duration}s</td></tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h6>Metadata</h6>
                            <table class="table table-sm">
                                <tr><td>Type:</td><td>${feedback.feedback_type}</td></tr>
                                <tr><td>Status:</td><td>${feedback.status}</td></tr>
                                <tr><td>Priority:</td><td>${feedback.priority}</td></tr>
                                <tr><td>Created:</td><td>${feedback.created_at}</td></tr>
                                <tr><td>IP:</td><td>${feedback.ip_address}</td></tr>
                            </table>
                            
                            <h6 class="mt-3">Location</h6>
                            <table class="table table-sm">
                                <tr><td>Country:</td><td>${feedback.country || 'N/A'}</td></tr>
                                <tr><td>City:</td><td>${feedback.city || 'N/A'}</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Responses (${responses.length})</h6>
                        ${responsesHtml || '<p class="text-muted">No responses yet</p>'}
                    </div>
                `;
                
                modal.show();
            }
        })
        .catch(error => console.error('Error loading feedback details:', error));
}

// Respond to feedback
function respondToFeedback(feedbackId) {
    document.getElementById('response_feedback_id').value = feedbackId;
    const modal = new bootstrap.Modal(document.getElementById('responseModal'));
    modal.show();
}

// Edit feedback
function editFeedback(feedbackId) {
    // Load feedback data and populate form
    fetch(`api/feedback-api.php?action=get_details&feedback_id=${feedbackId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const feedback = data.feedback;
                document.getElementById('edit_feedback_id').value = feedback.id;
                document.getElementById('edit_status').value = feedback.status;
                document.getElementById('edit_priority').value = feedback.priority;
                document.getElementById('assigned_to').value = feedback.assigned_to || '';
                document.getElementById('admin_notes').value = feedback.admin_notes || '';
                
                const modal = new bootstrap.Modal(document.getElementById('editFeedbackModal'));
                modal.show();
            }
        })
        .catch(error => console.error('Error loading feedback:', error));
}

// Delete feedback
function deleteFeedback(feedbackId) {
    if (confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete_feedback"><input type="hidden" name="feedback_id" value="' + feedbackId + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

// Export feedback
function exportFeedback() {
    const selectedIds = Array.from(document.querySelectorAll('.feedback-checkbox:checked')).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select feedback items to export');
        return;
    }
    
    window.location.href = `api/feedback-api.php?action=export&ids=${selectedIds.join(',')}`;
}

// Bulk assign
function bulkAssign() {
    const selectedIds = Array.from(document.querySelectorAll('.feedback-checkbox:checked')).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select feedback items to assign');
        return;
    }
    
    // Show assignment modal (simplified for this example)
    const assignTo = prompt('Enter user ID to assign to:');
    if (assignTo) {
        // Implement bulk assignment
        alert(`Assigned ${selectedIds.length} feedback items to user ${assignTo}`);
    }
}

// Add tag to feedback
function addTagToFeedback() {
    const tagName = document.getElementById('add_tag').value;
    const feedbackId = document.getElementById('edit_feedback_id').value;
    
    if (!tagName || !feedbackId) {
        alert('Please enter a tag name');
        return;
    }
    
    fetch('api/feedback-api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add_tag',
            feedback_id: feedbackId,
            tag_name: tagName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tag added successfully');
            location.reload();
        } else {
            alert('Error adding tag: ' + data.message);
        }
    })
    .catch(error => console.error('Error adding tag:', error));
}

// Set tag value from popular tags
function setTagValue(tagName) {
    document.getElementById('add_tag').value = tagName;
}

// Helper function to generate star rating HTML
function generateStars(rating) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
        html += `<i class="fas fa-star ${i <= rating ? 'text-warning' : 'text-muted'}"></i>`;
    }
    return html;
}

</script>

<?php
// Helper functions
function getRatingColor($rating) {
    if ($rating >= 4) return 'success';
    if ($rating >= 3) return 'warning';
    return 'danger';
}

function getFeedbackTypeColor($type) {
    $colors = [
        'quality' => 'primary',
        'performance' => 'warning',
        'content' => 'info',
        'technical' => 'danger',
        'general' => 'secondary'
    ];
    return $colors[$type] ?? 'secondary';
}

function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'reviewed' => 'info',
        'resolved' => 'success',
        'dismissed' => 'secondary'
    ];
    return $colors[$status] ?? 'secondary';
}

function getPriorityColor($priority) {
    $colors = [
        'low' => 'success',
        'medium' => 'warning',
        'high' => 'danger',
        'critical' => 'dark'
    ];
    return $colors[$priority] ?? 'secondary';
}

// Time ago function
function time_ago($datetime) {
    $date = new DateTime($datetime);
    $now = new DateTime();
    $diff = $now->getTimestamp() - $date->getTimestamp();
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}

?>
