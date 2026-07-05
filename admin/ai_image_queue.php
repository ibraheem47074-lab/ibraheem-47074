<?php
/**
 * AI Image Queue Management
 * Handle pending, failed, and RSS articles needing AI images
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/ai_image_generator.php';
require_once __DIR__ . '/../includes/smart_prompt_generator.php';

// Check admin permissions
if (!is_admin()) {
    header('Location: ../login.php');
    exit();
}

// Initialize classes
$aiGenerator = new AIImageGenerator($conn);
$promptGenerator = new SmartPromptGenerator($conn);

// Page header
$pageTitle = 'AI Image Queue';
include 'includes/admin-header.php';

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query based on filter
$whereClause = "";
$params = [];
$types = "";

switch ($filter) {
    case 'pending':
        $whereClause = "WHERE n.ai_image_status = 'pending'";
        break;
    case 'generating':
        $whereClause = "WHERE n.ai_image_status = 'generating'";
        break;
    case 'failed':
        $whereClause = "WHERE n.ai_image_status = 'failed'";
        break;
    case 'completed':
        $whereClause = "WHERE n.ai_image_status = 'completed'";
        break;
    case 'missing_rss':
        $whereClause = "WHERE n.news_type = 'rss_import' AND (n.image IS NULL OR n.image = '')";
        break;
    case 'needs_approval':
        $whereClause = "WHERE n.ai_image_status = 'completed' AND n.image_type = 'ai'";
        break;
    default:
        $whereClause = "WHERE n.image_type IN ('ai', 'rss') OR n.news_type = 'rss_import'";
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM news n $whereClause";
$countResult = mysqli_query($conn, $countQuery);
$total = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($total / $limit);

// Get queue items
$query = "SELECT n.*, c.name as category_name 
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          $whereClause 
          ORDER BY n.created_at DESC 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

// Handle bulk generation
if (isset($_GET['bulk_generate']) && is_numeric($_GET['bulk_generate'])) {
    $bulkCount = min(intval($_GET['bulk_generate']), 50);
    
    // Get pending RSS articles without images
    $bulkQuery = "SELECT n.*, c.name as category_name 
                  FROM news n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  WHERE n.news_type = 'rss_import' 
                  AND (n.image IS NULL OR n.image = '') 
                  AND n.ai_image_status != 'generating'
                  LIMIT $bulkCount";
    
    $bulkResult = mysqli_query($conn, $bulkQuery);
    $processed = 0;
    
    while ($news = mysqli_fetch_assoc($bulkResult)) {
        // Update status to generating
        mysqli_query($conn, "UPDATE news SET ai_image_status = 'generating' WHERE id = " . $news['id']);
        
        // Generate image
        $imageResult = $aiGenerator->generateImageForNews($news['id'], $news['title'], $news['category_name']);
        
        if ($imageResult['success']) {
            $processed++;
        } else {
            mysqli_query($conn, "UPDATE news SET ai_image_status = 'failed', ai_image_error = '" . 
                             mysqli_real_escape_string($conn, $imageResult['error']) . "' WHERE id = " . $news['id']);
        }
    }
    
    $message = "Bulk generation completed: $processed/$bulkCount images generated successfully.";
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">AI Image Queue</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button class="btn btn-primary" onclick="processSelected()">
                <i class="fas fa-play"></i> Process Selected
            </button>
            <button class="btn btn-success" onclick="bulkGenerateDialog()">
                <i class="fas fa-magic"></i> Bulk Generate
            </button>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" 
           href="?action=queue&filter=all">
            All (<?php echo getTotalCount('all'); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'pending' ? 'active' : ''; ?>" 
           href="?action=queue&filter=pending">
            Pending (<?php echo getTotalCount('pending'); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'generating' ? 'active' : ''; ?>" 
           href="?action=queue&filter=generating">
            Generating (<?php echo getTotalCount('generating'); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'failed' ? 'active' : ''; ?>" 
           href="?action=queue&filter=failed">
            Failed (<?php echo getTotalCount('failed'); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'completed' ? 'active' : ''; ?>" 
           href="?action=queue&filter=completed">
            Completed (<?php echo getTotalCount('completed'); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'missing_rss' ? 'active' : ''; ?>" 
           href="?action=queue&filter=missing_rss">
            Missing RSS Images (<?php echo getTotalCount('missing_rss'); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter === 'needs_approval' ? 'active' : ''; ?>" 
           href="?action=queue&filter=needs_approval">
            Needs Approval (<?php echo getTotalCount('needs_approval'); ?>)
        </a>
    </li>
</ul>

<!-- Queue Items -->
<div class="card shadow">
    <div class="card-body">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <form id="queueForm" method="POST">
                <input type="hidden" name="form_type" value="bulk_process">
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleAll()">
                                </th>
                                <th>Preview</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_items[]" 
                                               value="<?php echo $item['id']; ?>" 
                                               class="item-checkbox"
                                               <?php echo $item['ai_image_status'] === 'generating' ? 'disabled' : ''; ?>>
                                    </td>
                                    <td>
                                        <?php if ($item['image']): ?>
                                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                 class="img-thumbnail" style="max-width: 60px; max-height: 45px;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 45px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;">
                                            <a href="../news.php?id=<?php echo $item['id']; ?>" target="_blank">
                                                <?php echo htmlspecialchars($item['title']); ?>
                                            </a>
                                        </div>
                                        <?php if ($item['ai_image_error']): ?>
                                            <small class="text-danger d-block">
                                                <?php echo htmlspecialchars(substr($item['ai_image_error'], 0, 50)); ?>...
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo getBadgeColor($item['image_type']); ?>">
                                            <?php echo ucfirst($item['image_type'] ?? 'manual'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'secondary',
                                            'generating' => 'warning',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'approved' => 'primary',
                                            'rejected' => 'dark'
                                        ];
                                        $status = $item['ai_image_status'] ?? 'pending';
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass[$status]; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo date('M j, H:i', strtotime($item['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($item['ai_image_status'] === 'pending' || $item['ai_image_status'] === 'failed'): ?>
                                                <button class="btn btn-outline-primary" 
                                                        onclick="generateImage(<?php echo $item['id']; ?>)" 
                                                        title="Generate">
                                                    <i class="fas fa-magic"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($item['ai_image_status'] === 'completed'): ?>
                                                <button class="btn btn-outline-success" 
                                                        onclick="approveImage(<?php echo $item['id']; ?>)" 
                                                        title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="rejectImage(<?php echo $item['id']; ?>)" 
                                                        title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-outline-info" 
                                                    onclick="editImage(<?php echo $item['id']; ?>)" 
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <button class="btn btn-outline-warning" 
                                                    onclick="regenerateImage(<?php echo $item['id']; ?>)" 
                                                    title="Regenerate">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </form>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Queue pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=queue&filter=<?php echo $filter; ?>&page=<?php echo $page - 1; ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?action=queue&filter=<?php echo $filter; ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=queue&filter=<?php echo $filter; ?>&page=<?php echo $page + 1; ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No items found</h5>
                <p class="text-muted">There are no items matching the current filter.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Action Modals -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="form_type" value="reject_image">
                <input type="hidden" name="news_id" id="reject_news_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason for rejection</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate AI Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="form_type" value="generate_image">
                <input type="hidden" name="news_id" id="generate_news_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="provider" class="form-label">AI Provider</label>
                        <select class="form-select" id="provider" name="provider">
                            <option value="openai">OpenAI DALL-E</option>
                            <option value="stability">Stability AI</option>
                            <option value="replicate">Replicate</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="custom_prompt" class="form-label">Custom Prompt (Optional)</label>
                        <textarea class="form-control" id="custom_prompt" name="custom_prompt" rows="3" 
                                  placeholder="Leave empty for automatic prompt generation"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Helper functions
function getTotalCount($filter) {
    global $conn;
    
    $whereClause = "";
    switch ($filter) {
        case 'pending':
            $whereClause = "WHERE ai_image_status = 'pending'";
            break;
        case 'generating':
            $whereClause = "WHERE ai_image_status = 'generating'";
            break;
        case 'failed':
            $whereClause = "WHERE ai_image_status = 'failed'";
            break;
        case 'completed':
            $whereClause = "WHERE ai_image_status = 'completed'";
            break;
        case 'missing_rss':
            $whereClause = "WHERE news_type = 'rss_import' AND (image IS NULL OR image = '')";
            break;
        case 'needs_approval':
            $whereClause = "WHERE ai_image_status = 'completed' AND image_type = 'ai'";
            break;
        default:
            $whereClause = "WHERE image_type IN ('ai', 'rss') OR news_type = 'rss_import'";
    }
    
    $query = "SELECT COUNT(*) as total FROM news $whereClause";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getBadgeColor($type) {
    $colors = [
        'ai' => 'primary',
        'rss' => 'info',
        'manual' => 'secondary',
        'template' => 'warning'
    ];
    return $colors[$type] ?? 'secondary';
}
?>

<script>
function toggleAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.item-checkbox:not(:disabled)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function processSelected() {
    const form = document.getElementById('queueForm');
    const selected = form.querySelectorAll('input[name="selected_items[]"]:checked');
    
    if (selected.length === 0) {
        alert('Please select at least one item to process.');
        return;
    }
    
    if (confirm(`Process ${selected.length} selected items?`)) {
        form.submit();
    }
}

function generateImage(newsId) {
    document.getElementById('generate_news_id').value = newsId;
    new bootstrap.Modal(document.getElementById('generateModal')).show();
}

function approveImage(newsId) {
    if (confirm('Approve this AI generated image?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="form_type" value="approve_image">
            <input type="hidden" name="news_id" value="${newsId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectImage(newsId) {
    document.getElementById('reject_news_id').value = newsId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function editImage(newsId) {
    window.location.href = `?action=edit&news_id=${newsId}`;
}

function regenerateImage(newsId) {
    if (confirm('Regenerate this image? Current image will be replaced.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="form_type" value="regenerate_image">
            <input type="hidden" name="news_id" value="${newsId}">
            <input type="hidden" name="provider" value="openai">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function bulkGenerateDialog() {
    const count = prompt('How many images would you like to generate? (Max 50)', '10');
    if (count && !isNaN(count) && count > 0) {
        window.location.href = `?action=queue&filter=${encodeURIComponent('<?php echo $filter; ?>')}&bulk_generate=${Math.min(count, 50)}`;
    }
}
</script>

<?php include 'includes/admin-footer.php'; ?>
