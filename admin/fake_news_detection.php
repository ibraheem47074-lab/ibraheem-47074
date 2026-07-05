<?php
/**
 * AI Fake News Detection Admin Interface
 * Comprehensive management interface for fake news detection system
 */

require_once '../config/database.php';
require_once '../includes/ai_fake_news_detector.php';

// Initialize detector with existing database connection
$detector = new AIFakeNewsDetector($conn);

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['analyze_article'])) {
        $newsId = $_POST['news_id'];
        $result = $detector->analyzeArticle($newsId);
        
        if ($result) {
            $message = "Article analyzed successfully! Credibility Score: " . $result['credibility_score'] . "%";
            $messageType = "success";
        } else {
            $message = "Error analyzing article";
            $messageType = "error";
        }
    } elseif (isset($_POST['batch_analyze'])) {
        $results = $detector->batchAnalyze();
        $analyzedCount = count(array_filter($results, function($r) { return $r !== false; }));
        
        $message = "Batch analysis completed. {$analyzedCount} articles analyzed.";
        $messageType = $analyzedCount > 0 ? "success" : "error";
    } elseif (isset($_POST['review_article'])) {
        $newsId = $_POST['news_id'];
        $manualScore = $_POST['manual_score'];
        $reviewNotes = $_POST['review_notes'];
        
        $sql = "UPDATE news_credibility_analysis 
                SET manual_review_score = ?, requires_review = 0 
                WHERE news_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $manualScore, $newsId);
        
        if ($stmt->execute()) {
            $message = "Article review completed successfully";
            $messageType = "success";
        } else {
            $message = "Error updating article review: " . $conn->error;
            $messageType = "error";
        }
    }
}

// Get dashboard statistics
$statsSql = "SELECT 
    COUNT(*) as total_articles,
    SUM(CASE WHEN credibility_score IS NOT NULL THEN 1 ELSE 0 END) as analyzed_articles,
    SUM(CASE WHEN credibility_score >= 80 THEN 1 ELSE 0 END) as high_credibility,
    SUM(CASE WHEN credibility_score < 40 THEN 1 ELSE 0 END) as low_credibility,
    SUM(CASE WHEN credibility_status = 'REVIEW_REQUIRED' THEN 1 ELSE 0 END) as pending_review,
    AVG(credibility_score) as avg_credibility
    FROM news";

$statsResult = $conn->query($statsSql);
$stats = $statsResult->fetch_assoc();

// Get recent alerts
$alertsSql = "SELECT fna.*, n.title 
              FROM fake_news_alerts fna
              JOIN news n ON fna.news_id = n.id
              WHERE fna.status = 'ACTIVE'
              ORDER BY fna.created_at DESC
              LIMIT 10";

$alertsResult = $conn->query($alertsSql);
$alerts = $alertsResult->fetch_all(MYSQLI_ASSOC);

// Get high-risk articles
$highRiskArticles = $detector->getHighRiskArticles(10);

// Get recent analyses
$recentAnalysesSql = "SELECT nca.*, n.title, n.url_slug
                      FROM news_credibility_analysis nca
                      JOIN news n ON nca.news_id = n.id
                      ORDER BY nca.analysis_date DESC
                      LIMIT 15";

$recentAnalysesResult = $conn->query($recentAnalysesSql);
$recentAnalyses = $recentAnalysesResult->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-shield-alt"></i> AI Fake News Detection</h1>
        <div class="btn-group">
            <button class="btn btn-success" onclick="batchAnalyze()">
                <i class="fas fa-robot"></i> Batch Analyze
            </button>
            <a href="trusted_sources_manager.php" class="btn btn-primary">
                <i class="fas fa-database"></i> Manage Sources
            </a>
            <a href="fake_news_reports.php" class="btn btn-info">
                <i class="fas fa-flag"></i> User Reports
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Dashboard Statistics -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Articles</h5>
                    <h3><?= number_format($stats['total_articles']) ?></h3>
                    <small>Analyzed: <?= number_format($stats['analyzed_articles']) ?></small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">High Credibility</h5>
                    <h3><?= number_format($stats['high_credibility']) ?></h3>
                    <small>80%+ Score</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Low Credibility</h5>
                    <h3><?= number_format($stats['low_credibility']) ?></h3>
                    <small><40% Score</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Pending Review</h5>
                    <h3><?= number_format($stats['pending_review']) ?></h3>
                    <small>Need Attention</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Alerts</h5>
                    <h3><?= count($alerts) ?></h3>
                    <small>Need Action</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Avg Credibility</h5>
                    <h3><?= number_format($stats['avg_credibility'], 1) ?>%</h3>
                    <small>Overall Score</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Alerts -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Active Alerts</h5>
                    <span class="badge bg-danger"><?= count($alerts) ?></span>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php if (empty($alerts)): ?>
                            <div class="p-3 text-center text-muted">
                                <i class="fas fa-check-circle fa-3x mb-2"></i>
                                <p>No active alerts</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($alerts as $alert): ?>
                                <div class="border-bottom p-3 alert-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <span class="badge bg-<?= getAlertSeverityColor($alert['severity']) ?> me-2">
                                                    <?= $alert['severity'] ?>
                                                </span>
                                                <?= htmlspecialchars($alert['alert_type']) ?>
                                            </h6>
                                            <p class="mb-1 small text-muted"><?= htmlspecialchars($alert['title']) ?></p>
                                            <p class="mb-0 small"><?= htmlspecialchars($alert['message']) ?></p>
                                        </div>
                                        <small class="text-muted"><?= timeAgo($alert['created_at']) ?></small>
                                    </div>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewArticle(<?= $alert['news_id'] ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="dismissAlert(<?= $alert['id'] ?>)">
                                            <i class="fas fa-check"></i> Dismiss
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- High Risk Articles -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> High Risk Articles</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshHighRisk()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($highRiskArticles)): ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-shield-alt fa-3x mb-2"></i>
                            <p>No high-risk articles found</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Article</th>
                                        <th>Credibility</th>
                                        <th>Risk Level</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($highRiskArticles as $article): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars(substr($article['title'], 0, 60)) ?>...</strong>
                                                    <br>
                                                    <small class="text-muted">ID: <?= $article['id'] ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px; width: 60px;">
                                                        <div class="progress-bar bg-<?= getCredibilityScoreColor($article['credibility_score']) ?>" 
                                                             style="width: <?= $article['credibility_score'] ?>%"></div>
                                                    </div>
                                                    <small><?= number_format($article['credibility_score'], 1) ?>%</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= getRiskLevelColor($article['risk_level']) ?>">
                                                    <?= $article['risk_level'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= getStatusColor($article['requires_review']) ?>">
                                                    <?= $article['requires_review'] ? 'Review Required' : 'Flagged' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="viewArticle(<?= $article['id'] ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-success" onclick="reviewArticle(<?= $article['id'] ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="analyzeArticle(<?= $article['id'] ?>)">
                                                        <i class="fas fa-robot"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Analyses -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent Analyses</h5>
                    <div class="btn-group">
                        <select class="form-select form-select-sm" id="analysisFilter" onchange="filterAnalyses()">
                            <option value="all">All Analyses</option>
                            <option value="high">High Credibility</option>
                            <option value="medium">Medium Credibility</option>
                            <option value="low">Low Credibility</option>
                            <option value="critical">Critical Risk</option>
                        </select>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshAnalyses()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="analysesTable">
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th>Credibility Score</th>
                                    <th>Risk Level</th>
                                    <th>Category</th>
                                    <th>Source Verified</th>
                                    <th>Analysis Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAnalyses as $analysis): ?>
                                    <tr class="analysis-row" data-score="<?= $analysis['credibility_score'] ?>">
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars(substr($analysis['title'], 0, 80)) ?>...</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <a href="../news.php?article=<?= $analysis['url_slug'] ?>" target="_blank">
                                                        View Article <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-<?= getCredibilityScoreColor($analysis['credibility_score']) ?>" 
                                                         style="width: <?= $analysis['credibility_score'] ?>%"></div>
                                                </div>
                                                <strong><?= number_format($analysis['credibility_score'], 1) ?>%</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= getRiskLevelColor($analysis['risk_level']) ?>">
                                                <?= $analysis['risk_level'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= getCategoryColor($analysis['content_category']) ?>">
                                                <?= $analysis['content_category'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($analysis['source_verified']): ?>
                                                <span class="badge bg-success">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Unverified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= date('M j, Y H:i', strtotime($analysis['analysis_date'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="viewAnalysisDetails(<?= $analysis['news_id'] ?>)">
                                                    <i class="fas fa-chart-line"></i> Details
                                                </button>
                                                <button class="btn btn-outline-info" onclick="reanalyzeArticle(<?= $analysis['news_id'] ?>)">
                                                    <i class="fas fa-redo"></i> Re-analyze
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analysis Details Modal -->
<div class="modal fade" id="analysisDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Analysis Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="analysisDetailsContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Review Article Modal -->
<div class="modal fade" id="reviewArticleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="reviewForm">
                <input type="hidden" name="review_article" value="1">
                <input type="hidden" name="news_id" id="reviewNewsId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Manual Credibility Score (0-100)</label>
                        <input type="number" name="manual_score" class="form-control" 
                               min="0" max="100" step="0.1" required>
                        <small class="form-text text-muted">Override the AI score with your manual assessment</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Review Notes</label>
                        <textarea name="review_notes" class="form-control" rows="4" 
                                  placeholder="Add your review notes and reasoning..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Helper functions
function getAlertSeverityColor($severity) {
    $colors = [
        'INFO' => 'info',
        'WARNING' => 'warning',
        'CRITICAL' => 'danger'
    ];
    return $colors[$severity] ?? 'secondary';
}

function getCredibilityScoreColor($score) {
    if ($score >= 80) return 'success';
    if ($score >= 60) return 'info';
    if ($score >= 40) return 'warning';
    return 'danger';
}

function getRiskLevelColor($level) {
    $colors = [
        'LOW' => 'success',
        'MEDIUM' => 'info',
        'HIGH' => 'warning',
        'CRITICAL' => 'danger'
    ];
    return $colors[$level] ?? 'secondary';
}

function getStatusColor($requiresReview) {
    return $requiresReview ? 'warning' : 'danger';
}

function getCategoryColor($category) {
    $colors = [
        'VERIFIED' => 'success',
        'LIKELY_TRUE' => 'info',
        'UNVERIFIED' => 'primary',
        'LIKELY_FALSE' => 'warning',
        'FALSE' => 'danger'
    ];
    return $colors[$category] ?? 'secondary';
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff/60) . ' min ago';
    if ($diff < 86400) return floor($diff/3600) . ' hours ago';
    return date('M j', $time);
}
?>

<script>
// Batch analyze
function batchAnalyze() {
    if (confirm('This will analyze all pending articles. Continue?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="batch_analyze" value="1">';
        document.body.appendChild(form);
        form.submit();
    }
}

// Analyze single article
function analyzeArticle(newsId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="analyze_article" value="1">
        <input type="hidden" name="news_id" value="${newsId}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Re-analyze article
function reanalyzeArticle(newsId) {
    if (confirm('Re-analyze this article with updated AI models?')) {
        analyzeArticle(newsId);
    }
}

// View article
function viewArticle(newsId) {
    window.open(`../news.php?id=${newsId}`, '_blank');
}

// Review article
function reviewArticle(newsId) {
    document.getElementById('reviewNewsId').value = newsId;
    new bootstrap.Modal(document.getElementById('reviewArticleModal')).show();
}

// View analysis details
function viewAnalysisDetails(newsId) {
    fetch(`api/analysis_details_api.php?news_id=${newsId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('analysisDetailsContent').innerHTML = data.html;
            new bootstrap.Modal(document.getElementById('analysisDetailsModal')).show();
        } else {
            alert('Error loading analysis details: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading analysis details');
    });
}

// Dismiss alert
function dismissAlert(alertId) {
    if (confirm('Dismiss this alert?')) {
        fetch('api/alerts_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'dismiss',
                alert_id: alertId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error dismissing alert: ' + data.message);
            }
        });
    }
}

// Filter analyses
function filterAnalyses() {
    const filter = document.getElementById('analysisFilter').value;
    const rows = document.querySelectorAll('.analysis-row');
    
    rows.forEach(row => {
        const score = parseFloat(row.dataset.score);
        let show = true;
        
        switch(filter) {
            case 'high':
                show = score >= 80;
                break;
            case 'medium':
                show = score >= 60 && score < 80;
                break;
            case 'low':
                show = score >= 40 && score < 60;
                break;
            case 'critical':
                show = score < 40;
                break;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

// Refresh functions
function refreshHighRisk() {
    location.reload();
}

function refreshAnalyses() {
    location.reload();
}

// Initialize DataTable
$(document).ready(function() {
    $('#analysesTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[5, 'desc']] // Sort by analysis date descending
    });
});
</script>

<?php require_once '../includes/admin-footer.php'; ?>
