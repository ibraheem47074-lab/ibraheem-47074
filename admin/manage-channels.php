<?php
require_once '../config/database.php';
$page_title = 'Manage Live Channels';

// Check if user is admin
if (!is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Handle channel actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = clean_input($_POST['name']);
                $category = clean_input($_POST['category']);
                $stream_url = clean_input($_POST['stream_url']);
                $stream_type = clean_input($_POST['stream_type']);
                $description = clean_input($_POST['description']);
                $status = clean_input($_POST['status']);
                $language = clean_input($_POST['language']);
                $country = clean_input($_POST['country']);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                
                $query = "INSERT INTO channels (name, category, stream_url, stream_type, description, status, language, country, is_featured) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ssssssssi', $name, $category, $stream_url, $stream_type, $description, $status, $language, $country, $is_featured);
                mysqli_stmt_execute($stmt);
                
                header('Location: manage-channels.php?success=Channel added successfully');
                exit;
                
            case 'edit':
                $id = intval($_POST['id']);
                $name = clean_input($_POST['name']);
                $category = clean_input($_POST['category']);
                $stream_url = clean_input($_POST['stream_url']);
                $stream_type = clean_input($_POST['stream_type']);
                $description = clean_input($_POST['description']);
                $status = clean_input($_POST['status']);
                $language = clean_input($_POST['language']);
                $country = clean_input($_POST['country']);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                
                $query = "UPDATE channels SET name = ?, category = ?, stream_url = ?, stream_type = ?, description = ?, status = ?, language = ?, country = ?, is_featured = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ssssssssii', $name, $category, $stream_url, $stream_type, $description, $status, $language, $country, $is_featured, $id);
                mysqli_stmt_execute($stmt);
                
                header('Location: manage-channels.php?success=Channel updated successfully');
                exit;
                
            case 'delete':
                $id = intval($_POST['id']);
                $query = "DELETE FROM channels WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                
                header('Location: manage-channels.php?success=Channel deleted successfully');
                exit;
                
            case 'toggle_status':
                $id = intval($_POST['id']);
                $new_status = clean_input($_POST['status']);
                
                $query = "UPDATE channels SET status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'si', $new_status, $id);
                mysqli_stmt_execute($stmt);
                
                header('Location: manage-channels.php?success=Channel status updated');
                exit;
        }
    }
}

// Get channels
$channels_query = "SELECT * FROM channels ORDER BY sort_order ASC, is_featured DESC, name ASC";
$channels_result = mysqli_query($conn, $channels_query);

// Get channel for editing
$edit_channel = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $query = "SELECT * FROM channels WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_channel = mysqli_fetch_assoc($result);
}
?>

<?php include '../includes/admin-header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Manage Live Channels</h2>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addChannelModal">
            <i class="fas fa-plus me-2"></i>Add New Channel
        </button>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Channels Table -->
    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-tv me-2"></i>Live Channels</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Channel</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Viewers</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($channel = mysqli_fetch_assoc($channels_result)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <?php if ($channel['thumbnail']): ?>
                                                <img src="<?php echo htmlspecialchars($channel['thumbnail']); ?>" 
                                                     alt="<?php echo htmlspecialchars($channel['name']); ?>" 
                                                     class="rounded" style="width: 40px; height: 30px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 30px;">
                                                    <i class="fas fa-tv text-muted small"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($channel['name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($channel['language']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst($channel['category']); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo strtoupper($channel['stream_type']); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $channel['status'] === 'live' ? 'danger' : ($channel['status'] === 'scheduled' ? 'warning' : 'secondary'); ?>">
                                        <?php echo strtoupper($channel['status']); ?>
                                    </span>
                                    <?php if ($channel['status'] === 'live'): ?>
                                        <span class="text-danger small ms-1"><i class="fas fa-circle"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="fas fa-eye me-1"></i>
                                    <?php echo number_format($channel['viewer_count']); ?>
                                </td>
                                <td>
                                    <?php if ($channel['is_featured']): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php else: ?>
                                        <i class="far fa-star text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" onclick="editChannel(<?php echo $channel['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-<?php echo $channel['status'] === 'live' ? 'secondary' : 'success'; ?>" 
                                                onclick="toggleStatus(<?php echo $channel['id']; ?>, '<?php echo $channel['status'] === 'live' ? 'offline' : 'live'; ?>')">
                                            <i class="fas fa-<?php echo $channel['status'] === 'live' ? 'stop' : 'play'; ?>"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="deleteChannel(<?php echo $channel['id']; ?>)">
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

<!-- Add Channel Modal -->
<div class="modal fade" id="addChannelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Channel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="manage-channels.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Channel Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" required>
                                    <option value="news">News</option>
                                    <option value="sports">Sports</option>
                                    <option value="entertainment">Entertainment</option>
                                    <option value="business">Business</option>
                                    <option value="technology">Technology</option>
                                    <option value="international">International</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stream Type</label>
                                <select class="form-select" name="stream_type" required>
                                    <option value="youtube">YouTube</option>
                                    <option value="hls">HLS</option>
                                    <option value="rtmp">RTMP</option>
                                    <option value="iframe">iFrame</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="offline">Offline</option>
                                    <option value="live">Live</option>
                                    <option value="scheduled">Scheduled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stream URL</label>
                        <input type="url" class="form-control" name="stream_url" placeholder="https://youtube.com/watch?v=...">
                        <div class="form-text">YouTube URL, HLS stream URL, or embed URL</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Language</label>
                                <input type="text" class="form-control" name="language" value="en" maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" name="country" value="PK" maxlength="50">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                                    <label class="form-check-label" for="is_featured">
                                        Featured Channel
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Add Channel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Channel Modal -->
<?php if ($edit_channel): ?>
<div class="modal fade" id="editChannelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Channel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="manage-channels.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $edit_channel['id']; ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Channel Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($edit_channel['name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" required>
                                    <option value="news" <?php echo $edit_channel['category'] === 'news' ? 'selected' : ''; ?>>News</option>
                                    <option value="sports" <?php echo $edit_channel['category'] === 'sports' ? 'selected' : ''; ?>>Sports</option>
                                    <option value="entertainment" <?php echo $edit_channel['category'] === 'entertainment' ? 'selected' : ''; ?>>Entertainment</option>
                                    <option value="business" <?php echo $edit_channel['category'] === 'business' ? 'selected' : ''; ?>>Business</option>
                                    <option value="technology" <?php echo $edit_channel['category'] === 'technology' ? 'selected' : ''; ?>>Technology</option>
                                    <option value="international" <?php echo $edit_channel['category'] === 'international' ? 'selected' : ''; ?>>International</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stream Type</label>
                                <select class="form-select" name="stream_type" required>
                                    <option value="youtube" <?php echo $edit_channel['stream_type'] === 'youtube' ? 'selected' : ''; ?>>YouTube</option>
                                    <option value="hls" <?php echo $edit_channel['stream_type'] === 'hls' ? 'selected' : ''; ?>>HLS</option>
                                    <option value="rtmp" <?php echo $edit_channel['stream_type'] === 'rtmp' ? 'selected' : ''; ?>>RTMP</option>
                                    <option value="iframe" <?php echo $edit_channel['stream_type'] === 'iframe' ? 'selected' : ''; ?>>iFrame</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="offline" <?php echo $edit_channel['status'] === 'offline' ? 'selected' : ''; ?>>Offline</option>
                                    <option value="live" <?php echo $edit_channel['status'] === 'live' ? 'selected' : ''; ?>>Live</option>
                                    <option value="scheduled" <?php echo $edit_channel['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stream URL</label>
                        <input type="url" class="form-control" name="stream_url" value="<?php echo htmlspecialchars($edit_channel['stream_url']); ?>" placeholder="https://youtube.com/watch?v=...">
                        <div class="form-text">YouTube URL, HLS stream URL, or embed URL</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($edit_channel['description']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Language</label>
                                <input type="text" class="form-control" name="language" value="<?php echo htmlspecialchars($edit_channel['language']); ?>" maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" name="country" value="<?php echo htmlspecialchars($edit_channel['country']); ?>" maxlength="50">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="edit_featured" <?php echo $edit_channel['is_featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="edit_featured">
                                        Featured Channel
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Update Channel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($edit_channel): ?>
        const editModal = new bootstrap.Modal(document.getElementById('editChannelModal'));
        editModal.show();
    <?php endif; ?>
});

function editChannel(id) {
    window.location.href = 'manage-channels.php?edit=' + id;
}

function toggleStatus(id, newStatus) {
    if (confirm('Are you sure you want to change the channel status to ' + newStatus + '?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'manage-channels.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'toggle_status';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = newStatus;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        form.appendChild(statusInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteChannel(id) {
    if (confirm('Are you sure you want to delete this channel? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'manage-channels.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/admin-footer.php'; ?>
