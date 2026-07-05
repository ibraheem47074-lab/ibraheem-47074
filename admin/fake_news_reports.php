<?php
/**
 * Fake News Reports Management
 * Admin interface for managing user-reported fake news content
 */

require_once '../config/database.php';
require_once '../includes/ai_fake_news_detector.php';
require_once '../includes/admin-header.php';

// Initialize detector with existing database connection
$detector = new AIFakeNewsDetector($conn);

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['review_report'])) {
        $reportId = $_POST['report_id'];
        $action = $_POST['review_action'];
        $adminNotes = $_POST['admin_notes'] ?? '';
        
        $status = $action === 'validate' ? 'VALIDATED' : 'DISMISSED';
        
        $sql = "UPDATE user_fake_news_reports 
                SET report_status = ?, admin_notes = ?, reviewed_at = NOW() 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $adminNotes, $reportId);
        
        if ($stmt->execute()) {
            $message = "Report reviewed successfully";
            $messageType = "success";
        } else {
            $message = "Error reviewing report: " . $conn->error;
            $messageType = "error";
        }
    } elseif (isset($_POST['bulk_review'])) {
        $reportIds = $_POST['report_ids'] ?? [];
        $action = $_POST['bulk_action'];
        
        if (!empty($reportIds)) {
            $status = $action === 'validate' ? 'VALIDATED' : 'DISMISSED';
            $placeholders = str_repeat('?,', count($reportIds) - 1) . '?';
            
            $sql = "UPDATE user_fake_news_reports 
                    SET report_status = ?, reviewed_at = NOW() 
                    WHERE id IN ($placeholders)";
            
            $stmt = $conn->prepare($sql);
            $params = array_merge([$status], $reportIds);
            $types = str_repeat('i', count($params));
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $message = count($reportIds) . " reports reviewed successfully";
                $messageType = "success";
            } else {
                $message = "Error reviewing reports: " . $conn->error;
                $messageType = "error";
            }
        }
    }
}

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$reason = $_GET['reason'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Build query
$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if ($status !== 'all') {
    $whereClause .= " AND ufr.report_status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($reason)) {
    $whereClause .= " AND ufr.report_reason = ?";
    $params[] = $reason;
    $types .= "s";
}

if (!empty($dateFrom)) {
    $whereClause .= " AND ufr.created_at >= ?";
    $params[] = $dateFrom . ' 00:00:00';
    $types .= "s";
}

if (!empty($dateTo)) {
    $whereClause .= " AND ufr.created_at <= ?";
    $params[] = $dateTo . ' 23:59:59';
    $types .= "s";
}

// Get reports with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$sql = "SELECT ufr.*, n.title, n.url_slug, n.credibility_score, n.credibility_status,
        COUNT(*) OVER() as total_count
        FROM user_fake_news_reports ufr
        JOIN news n ON ufr.news_id = n.id
        $whereClause
        ORDER BY ufr.created_at DESC
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate pagination
$totalCount = !empty($reports) ? $reports[0]['total_count'] : 0;
$totalPages = ceil($totalCount / $limit);

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total_reports,
    SUM(CASE WHEN report_status = 'PENDING' THEN 1 ELSE 0 END) as pending_reports,
    SUM(CASE WHEN report_status = 'VALIDATED' THEN 1 ELSE 0 END) as validated_reports,
    SUM(CASE WHEN report_status = 'DISMISSED' THEN 1 ELSE 0 END) as dismissed_reports,
    SUM(CASE WHEN report_reason = 'FALSE_INFORMATION' THEN 1 ELSE 0 END) as false_info_reports,
    SUM(CASE WHEN report_reason = 'MISLEADING' THEN 1 ELSE 0 END) as misleading_reports,
    SUM(CASE WHEN report_reason = 'BIASED' THEN 1 ELSE 0 END) as biased_reports
    FROM user_fake_news_reports";

$statsResult = $conn->query($statsSql);
$stats = $statsResult ? $statsResult->fetch_assoc() : [];

// Ensure all stats have default values
$stats = array_merge([
    'total_reports' => 0,
    'pending_reports' => 0,
    'validated_reports' => 0,
    'dismissed_reports' => 0,
    'false_info_reports' => 0,
    'misleading_reports' => 0,
    'biased_reports' => 0
], $stats ?: []);

// Get recent trends
$trendsSql = "SELECT DATE(created_at) as report_date, COUNT(*) as report_count
              FROM user_fake_news_reports 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              GROUP BY DATE(created_at)
              ORDER BY report_date DESC
              LIMIT 7";

$trendsResult = $conn->query($trendsSql);
$trends = $trendsResult ? $trendsResult->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-flag"></i> Fake News Reports</h1>
        <div class="btn-group">
            <button class="btn btn-success" onclick="exportReports()">
                <i class="fas fa-download"></i> Export
            </button>
            <a href="fake_news_detection.php" class="btn btn-primary">
                <i class="fas fa-shield-alt"></i> Detection Dashboard
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Reports</h5>
                    <h3><?= number_format($stats['total_reports'] ?? 0) ?></h3>
                    <small>All time</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h3><?= number_format($stats['pending_reports'] ?? 0) ?></h3>
                    <small>Need review</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Validated</h5>
                    <h3><?= number_format($stats['validated_reports'] ?? 0) ?></h3>
                    <small>Confirmed issues</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Dismissed</h5>
                    <h3><?= number_format($stats['dismissed_reports'] ?? 0) ?></h3>
                    <small>False reports</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">False Info</h5>
                    <h3><?= number_format($stats['false_info_reports'] ?? 0) ?></h3>
                    <small>Most serious</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Recent Trend</h5>
                    <h3><?= !empty($trends) ? $trends[0]['report_count'] : 0 ?></h3>
                    <small>Last 24h</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="PENDING" <?= $status === 'PENDING' ? 'selected' : '' ?>>Pending</option>
                        <option value="VALIDATED" <?= $status === 'VALIDATED' ? 'selected' : '' ?>>Validated</option>
                        <option value="DISMISSED" <?= $status === 'DISMISSED' ? 'selected' : '' ?>>Dismissed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Reason</label>
                    <select name="reason" class="form-select">
                        <option value="">All Reasons</option>
                        <option value="MISLEADING" <?= $reason === 'MISLEADING' ? 'selected' : '' ?>>Misleading</option>
                        <option value="FALSE_INFORMATION" <?= $reason === 'FALSE_INFORMATION' ? 'selected' : '' ?>>False Information</option>
                        <option value="BIASED" <?= $reason === 'BIASED' ? 'selected' : '' ?>>Biased</option>
                        <option value="CLICKBAIT" <?= $reason === 'CLICKBAIT' ? 'selected' : '' ?>>Clickbait</option>
                        <option value="SPAM" <?= $reason === 'SPAM' ? 'selected' : '' ?>>Spam</option>
                        <option value="OTHER" <?= $reason === 'OTHER' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary flex-fill">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="fake_news_reports.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">User Reports</h5>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Showing <?= count($reports) ?> of <?= $totalCount ?> reports</span>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-success" id="bulkValidateBtn" disabled>
                        <i class="fas fa-check"></i> Validate
                    </button>
                    <button class="btn btn-sm btn-outline-danger" id="bulkDismissBtn" disabled>
                        <i class="fas fa-times"></i> Dismiss
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="reportsTable">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Article</th>
                            <th>Reason</th>
                            <th>Details</th>
                            <th>Reporter IP</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="report-checkbox form-check-input" 
                                           value="<?= $report['id'] ?>" 
                                           <?= $report['report_status'] !== 'PENDING' ? 'disabled' : '' ?>>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars(substr($report['title'], 0, 60)) ?>...</strong>
                                        <br>
                                        <small class="text-muted">
                                            <a href="../news.php?slug=<?= $report['url_slug'] ?>" target="_blank">
                                                View Article <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </small>
                                        <?php if ($report['credibility_score']): ?>
                                            <br>
                                            <span class="badge bg-<?= getCredibilityColor($report['credibility_score']) ?>">
                                                <?= number_format($report['credibility_score'] ?? 0, 0) ?>% Credibility
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= getReasonColor($report['report_reason']) ?>">
                                        <?= htmlspecialchars($report['report_reason']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="max-width: 200px;">
                                        <small><?= htmlspecialchars(substr($report['report_details'] ?? '', 0, 100)) ?>...</small>
                                        <?php if (!empty($report['evidence_urls'])): ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-link"></i> 
                                                <?= count(json_decode($report['evidence_urls'] ?: '[]', true)) ?> evidence URLs
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($report['reporter_ip']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= getStatusColor($report['report_status']) ?>">
                                        <?= htmlspecialchars($report['report_status']) ?>
                                    </span>
                                    <?php if ($report['report_status'] === 'VALIDATED'): ?>
                                        <br>
                                        <small class="text-muted">Validated</small>
                                    <?php elseif ($report['report_status'] === 'DISMISSED'): ?>
                                        <br>
                                        <small class="text-muted">Dismissed</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= date('M j, Y H:i', strtotime($report['created_at'])) ?></small>
                                    <?php if ($report['reviewed_at']): ?>
                                        <br>
                                        <small class="text-muted">Reviewed: <?= date('M j, H:i', strtotime($report['reviewed_at'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewReportDetails(<?= $report['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($report['report_status'] === 'PENDING'): ?>
                                            <button class="btn btn-outline-success" onclick="reviewReport(<?= $report['id'] ?>, 'validate')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="reviewReport(<?= $report['id'] ?>, 'dismiss')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Reports pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&reason=<?= urlencode($reason) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&reason=<?= urlencode($reason) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&reason=<?= urlencode($reason) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Report Details Modal -->
<div class="modal fade" id="reportDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reportDetailsContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Review Report Modal -->
<div class="modal fade" id="reviewReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="reviewForm">
                <input type="hidden" name="review_report" value="1">
                <input type="hidden" name="report_id" id="reviewReportId">
                <input type="hidden" name="review_action" id="reviewAction">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" class="form-control" rows="4" 
                                  placeholder="Add your review notes and reasoning..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="reviewSubmitBtn">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Helper functions
function getCredibilityColor($score) {
    if ($score >= 80) return 'success';
    if ($score >= 60) return 'info';
    if ($score >= 40) return 'warning';
    return 'danger';
}

function getReasonColor($reason) {
    $colors = [
        'MISLEADING' => 'warning',
        'FALSE_INFORMATION' => 'danger',
        'BIASED' => 'info',
        'CLICKBAIT' => 'secondary',
        'SPAM' => 'dark',
        'OTHER' => 'light'
    ];
    return $colors[$reason] ?? 'secondary';
}

function getStatusColor($status) {
    $colors = [
        'PENDING' => 'warning',
        'VALIDATED' => 'success',
        'DISMISSED' => 'danger',
        'REVIEWING' => 'info'
    ];
    return $colors[$status] ?? 'secondary';
}
?>

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.report-checkbox:not(:disabled)');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    updateBulkButtons();
});

// Update bulk buttons state
function updateBulkButtons() {
    const checkedBoxes = document.querySelectorAll('.report-checkbox:checked');
    const bulkValidateBtn = document.getElementById('bulkValidateBtn');
    const bulkDismissBtn = document.getElementById('bulkDismissBtn');
    
    bulkValidateBtn.disabled = checkedBoxes.length === 0;
    bulkDismissBtn.disabled = checkedBoxes.length === 0;
}

// Individual checkbox change
document.querySelectorAll('.report-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkButtons);
});

// Bulk actions
document.getElementById('bulkValidateBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.report-checkbox:checked');
    const reportIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (confirm(`Validate ${reportIds.length} reports? This will mark them as confirmed issues.`)) {
        bulkReviewReports(reportIds, 'validate');
    }
});

document.getElementById('bulkDismissBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.report-checkbox:checked');
    const reportIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (confirm(`Dismiss ${reportIds.length} reports? This will mark them as false reports.`)) {
        bulkReviewReports(reportIds, 'dismiss');
    }
});

function bulkReviewReports(reportIds, action) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="bulk_review" value="1">
        <input type="hidden" name="bulk_action" value="${action}">
        ${reportIds.map(id => `<input type="hidden" name="report_ids[]" value="${id}">`).join('')}
    `;
    document.body.appendChild(form);
    form.submit();
}

// View report details
function viewReportDetails(reportId) {
    fetch(`api/reports_api.php?action=get_details&id=${reportId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('reportDetailsContent').innerHTML = data.html;
            new bootstrap.Modal(document.getElementById('reportDetailsModal')).show();
        } else {
            alert('Error loading report details: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading report details');
    });
}

// Review single report
function reviewReport(reportId, action) {
    document.getElementById('reviewReportId').value = reportId;
    document.getElementById('reviewAction').value = action;
    
    const submitBtn = document.getElementById('reviewSubmitBtn');
    submitBtn.className = action === 'validate' ? 'btn btn-success' : 'btn btn-danger';
    submitBtn.textContent = action === 'validate' ? 'Validate Report' : 'Dismiss Report';
    
    new bootstrap.Modal(document.getElementById('reviewReportModal')).show();
}

// Export reports
function exportReports() {
    const url = new URL(window.location);
    url.searchParams.set('export', '1');
    window.open(url.toString(), '_blank');
}

// Initialize DataTable
$(document).ready(function() {
    $('#reportsTable').DataTable({
        pageLength: 25,
        responsive: true,
        order: [[6, 'desc']] // Sort by date descending
    });
});
</script>

<?php require_once '../includes/admin-footer.php'; ?>
