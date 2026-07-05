<?php
/**
 * AI Image Logs
 * View and analyze AI image generation history
 */

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$provider = $_GET['provider'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// Build query conditions
$whereConditions = [];
$params = [];
$types = "";

if ($filter !== 'all') {
    switch ($filter) {
        case 'completed':
            $whereConditions[] = "l.status = 'completed'";
            break;
        case 'failed':
            $whereConditions[] = "l.status = 'failed'";
            break;
        case 'pending':
            $whereConditions[] = "l.status = 'pending'";
            break;
    }
}

if ($provider) {
    $whereConditions[] = "l.provider = ?";
    $params[] = $provider;
    $types .= "s";
}

if ($status) {
    $whereConditions[] = "l.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($date_from) {
    $whereConditions[] = "DATE(l.created_at) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if ($date_to) {
    $whereConditions[] = "DATE(l.created_at) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM ai_image_logs l $whereClause";
$stmt = mysqli_prepare($conn, $countQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$totalPages = ceil($total / $limit);

// Get logs
$query = "SELECT l.*, n.title as news_title, c.name as category_name
          FROM ai_image_logs l
          LEFT JOIN news n ON l.news_id = n.id
          LEFT JOIN categories c ON n.category_id = c.id
          $whereClause
          ORDER BY l.created_at DESC
          LIMIT $limit OFFSET $offset";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get statistics
$statsQuery = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    AVG(generation_time) as avg_time,
    COUNT(DISTINCT provider) as providers_used
    FROM ai_image_logs $whereClause";

$stmt = mysqli_prepare($conn, $statsQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get provider breakdown
$providerQuery = "SELECT provider, COUNT(*) as count, 
                   COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                   COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed
                   FROM ai_image_logs 
                   GROUP BY provider 
                   ORDER BY count DESC";
$providerStats = mysqli_query($conn, $providerQuery);
?>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="action" value="logs">
            
            <div class="col-md-3">
                <label for="filter" class="form-label">Status Filter</label>
                <select class="form-select" id="filter" name="filter">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="completed" <?php echo $filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="failed" <?php echo $filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="provider" class="form-label">Provider</label>
                <select class="form-select" id="provider" name="provider">
                    <option value="">All Providers</option>
                    <option value="openai" <?php echo $provider === 'openai' ? 'selected' : ''; ?>>OpenAI</option>
                    <option value="stability" <?php echo $provider === 'stability' ? 'selected' : ''; ?>>Stability AI</option>
                    <option value="replicate" <?php echo $provider === 'replicate' ? 'selected' : ''; ?>>Replicate</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
            </div>
            
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                    <a href="?action=logs" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['completed']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Failed</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['failed']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['pending']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Time</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['avg_time'] ? number_format($stats['avg_time'], 1) . 's' : 'N/A'; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-stopwatch fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Providers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['providers_used']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-server fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Logs Table -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Generation Logs</h6>
                <div>
                    <button class="btn btn-sm btn-outline-success" onclick="exportLogs()">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="clearLogs()">
                        <i class="fas fa-trash"></i> Clear Old Logs
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="logsTable">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>News</th>
                                    <th>Provider</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                    <th>Prompt</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($log = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo date('M j, H:i:s', strtotime($log['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 150px;">
                                                <?php if ($log['news_title']): ?>
                                                    <a href="../news.php?id=<?php echo $log['news_id']; ?>" target="_blank">
                                                        <?php echo htmlspecialchars(substr($log['news_title'], 0, 30)); ?>...
                                                    </a>
                                                    <?php if ($log['category_name']): ?>
                                                        <br><small class="text-muted"><?php echo $log['category_name']; ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">News ID: <?php echo $log['news_id']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($log['provider']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'secondary',
                                                'generating' => 'warning',
                                                'completed' => 'success',
                                                'failed' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass[$log['status']]; ?>">
                                                <?php echo ucfirst($log['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($log['generation_time']): ?>
                                                <?php echo number_format($log['generation_time'], 2); ?>s
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px; cursor: pointer;" 
                                                 onclick="showPrompt(<?php echo $log['id']; ?>)">
                                                <?php
                                                $prompt = json_decode($log['prompt'], true);
                                                if (is_array($prompt)) {
                                                    echo htmlspecialchars(substr($prompt['prompt'] ?? '', 0, 50));
                                                } else {
                                                    echo htmlspecialchars(substr($log['prompt'], 0, 50));
                                                }
                                                ?>...
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($log['image_path']): ?>
                                                    <a href="../<?php echo htmlspecialchars($log['image_path']); ?>" 
                                                       target="_blank" class="btn btn-outline-primary" title="View Image">
                                                        <i class="fas fa-image"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button class="btn btn-outline-info" 
                                                        onclick="showLogDetails(<?php echo $log['id']; ?>)" 
                                                        title="Details">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                                <?php if ($log['status'] === 'failed'): ?>
                                                    <button class="btn btn-outline-warning" 
                                                            onclick="retryGeneration(<?php echo $log['news_id']; ?>)" 
                                                            title="Retry">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Logs pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?action=logs&filter=<?php echo $filter; ?>&provider=<?php echo $provider; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&page=<?php echo $page - 1; ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?action=logs&filter=<?php echo $filter; ?>&provider=<?php echo $provider; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?action=logs&filter=<?php echo $filter; ?>&provider=<?php echo $provider; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&page=<?php echo $page + 1; ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No logs found</h5>
                        <p class="text-muted">No generation logs match the current filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Provider Statistics -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Provider Performance</h6>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($providerStats) > 0): ?>
                    <?php while ($provider = mysqli_fetch_assoc($providerStats)): ?>
                        <div class="mb-4">
                            <h6 class="text-primary"><?php echo htmlspecialchars($provider['provider']); ?></h6>
                            
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Total</span>
                                    <span class="badge bg-primary"><?php echo $provider['count']; ?></span>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Success Rate</span>
                                    <span class="badge bg-success">
                                        <?php 
                                        $rate = $provider['count'] > 0 ? ($provider['completed'] / $provider['count']) * 100 : 0;
                                        echo number_format($rate, 1); ?>%
                                    </span>
                                </div>
                            </div>
                            
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $rate; ?>%"></div>
                                <div class="progress-bar bg-danger" style="width: <?php echo 100 - $rate; ?>%"></div>
                            </div>
                            
                            <div class="mt-2 text-muted small">
                                <?php echo $provider['completed']; ?> successful, <?php echo $provider['failed']; ?> failed
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No provider data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Log Details -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="logDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showPrompt(logId) {
    // Fetch and display full prompt
    fetch(`api/get_log_prompt.php?id=${logId}`)
        .then(response => response.json())
        .then(data => {
            alert('Full Prompt:\n\n' + data.prompt);
        })
        .catch(error => {
            console.error('Error fetching prompt:', error);
            alert('Error fetching prompt details');
        });
}

function showLogDetails(logId) {
    fetch(`api/get_log_details.php?id=${logId}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('logDetailsContent');
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Log ID:</strong> ${data.id}<br>
                        <strong>News ID:</strong> <a href="../news.php?id=${data.news_id}" target="_blank">${data.news_id}</a><br>
                        <strong>Provider:</strong> ${data.provider}<br>
                        <strong>Status:</strong> <span class="badge bg-${getStatusClass(data.status)}">${data.status}</span><br>
                        <strong>Created:</strong> ${new Date(data.created_at).toLocaleString()}<br>
                        <strong>Generation Time:</strong> ${data.generation_time ? data.generation_time + 's' : 'N/A'}
                    </div>
                    <div class="col-md-6">
                        <strong>Image Path:</strong> ${data.image_path || 'N/A'}<br>
                        <strong>Image URL:</strong> ${data.image_url ? '<a href="' + data.image_url + '" target="_blank">View</a>' : 'N/A'}<br>
                        <?php if ($data['image_path']): ?>
                        <strong>Preview:</strong><br>
                        <img src="../${data.image_path}" style="max-width: 100%; max-height: 200px;" class="img-thumbnail mt-2">
                        <?php endif; ?>
                    </div>
                </div>
                <hr>
                <strong>Prompt:</strong><br>
                <pre class="bg-light p-2 rounded">${data.prompt}</pre>
                ${data.error_message ? '<hr><strong>Error:</strong><br><div class="alert alert-danger">' + data.error_message + '</div>' : ''}
                ${data.metadata ? '<hr><strong>Metadata:</strong><br><pre>' + JSON.stringify(JSON.parse(data.metadata), null, 2) + '</pre>' : ''}
            `;
            new bootstrap.Modal(document.getElementById('logDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error fetching log details:', error);
            alert('Error fetching log details');
        });
}

function retryGeneration(newsId) {
    if (confirm('Retry image generation for this article?')) {
        window.location.href = `?action=edit&news_id=${newsId}&retry=true`;
    }
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.open('?' + params.toString(), '_blank');
}

function clearLogs() {
    const days = prompt('Clear logs older than how many days?', '30');
    if (days && !isNaN(days) && days > 0) {
        if (confirm(`Delete all logs older than ${days} days? This action cannot be undone.`)) {
            fetch('api/clear_logs.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ days: parseInt(days) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`${data.deleted} logs deleted successfully.`);
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error clearing logs:', error);
                alert('Error clearing logs');
            });
        }
    }
}

function getStatusClass(status) {
    const classes = {
        'pending': 'secondary',
        'generating': 'warning',
        'completed': 'success',
        'failed': 'danger'
    };
    return classes[status] || 'secondary';
}

// Initialize data table
$(document).ready(function() {
    $('#logsTable').DataTable({
        'pageLength': 25,
        'order': [[0, 'desc']],
        'responsive': true
    });
});
</script>
