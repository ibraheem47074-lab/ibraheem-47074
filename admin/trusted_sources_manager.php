<?php
/**
 * Trusted Sources Management System
 * Manages and verifies trusted news sources for AI Fake News Detection
 */

require_once '../config/database.php';
require_once '../includes/admin-header.php';

// Initialize database connection
$detector = new AIFakeNewsDetector($conn);

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_source'])) {
        // Add new trusted source
        $sql = "INSERT INTO trusted_sources (
            source_name, source_url, domain_name, source_type, credibility_tier,
            trust_score, reliability_score, accuracy_score, verified, country, language
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        $stmt->bind_param(
            "sssssdddsis",
            $_POST['source_name'],
            $_POST['source_url'],
            $_POST['domain_name'],
            $_POST['source_type'],
            $_POST['credibility_tier'],
            $_POST['trust_score'],
            $_POST['reliability_score'],
            $_POST['accuracy_score'],
            $_POST['verified'],
            $_POST['country'],
            $_POST['language']
        );
        
        if ($stmt->execute()) {
            $message = "Trusted source added successfully!";
            $messageType = "success";
        } else {
            $message = "Error adding trusted source: " . $conn->error;
            $messageType = "error";
        }
    } elseif (isset($_POST['update_source'])) {
        // Update existing source
        $sql = "UPDATE trusted_sources SET
            source_name = ?, source_url = ?, source_type = ?, credibility_tier = ?,
            trust_score = ?, reliability_score = ?, accuracy_score = ?, verified = ?,
            country = ?, language = ?, active = ?
            WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssdddissisi",
            $_POST['source_name'],
            $_POST['source_url'],
            $_POST['source_type'],
            $_POST['credibility_tier'],
            $_POST['trust_score'],
            $_POST['reliability_score'],
            $_POST['accuracy_score'],
            $_POST['verified'],
            $_POST['country'],
            $_POST['language'],
            $_POST['active'],
            $_POST['source_id']
        );
        
        if ($stmt->execute()) {
            $message = "Trusted source updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating trusted source: " . $conn->error;
            $messageType = "error";
        }
    } elseif (isset($_POST['bulk_verify'])) {
        // Bulk verify sources
        $sourceIds = $_POST['source_ids'] ?? [];
        
        if (!empty($sourceIds)) {
            $placeholders = str_repeat('?,', count($sourceIds) - 1) . '?';
            $sql = "UPDATE trusted_sources SET verified = 1, verification_date = NOW() 
                    WHERE id IN ($placeholders)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(str_repeat('i', count($sourceIds)), ...$sourceIds);
            
            if ($stmt->execute()) {
                $message = count($sourceIds) . " sources verified successfully!";
                $messageType = "success";
            } else {
                $message = "Error verifying sources: " . $conn->error;
                $messageType = "error";
            }
        }
    }
}

// Get trusted sources with filtering
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if ($filter !== 'all') {
    switch ($filter) {
        case 'verified':
            $whereClause .= " AND verified = 1";
            break;
        case 'unverified':
            $whereClause .= " AND verified = 0";
            break;
        case 'active':
            $whereClause .= " AND active = 1";
            break;
        case 'blacklisted':
            $whereClause .= " AND blacklisted = 1";
            break;
        case 'high_trust':
            $whereClause .= " AND trust_score >= 80";
            break;
        case 'low_trust':
            $whereClause .= " AND trust_score < 50";
            break;
    }
}

if (!empty($search)) {
    $whereClause .= " AND (source_name LIKE ? OR domain_name LIKE ? OR source_url LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    $types .= "sss";
}

$sql = "SELECT * FROM trusted_sources $whereClause ORDER BY trust_score DESC, source_name ASC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$sources = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total_sources,
    SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_sources,
    SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_sources,
    SUM(CASE WHEN blacklisted = 1 THEN 1 ELSE 0 END) as blacklisted_sources,
    AVG(trust_score) as avg_trust_score
    FROM trusted_sources";

$statsResult = $conn->query($statsSql);
$stats = $statsResult->fetch_assoc();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-shield-alt"></i> Trusted Sources Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSourceModal">
            <i class="fas fa-plus"></i> Add Trusted Source
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Sources</h5>
                    <h3><?= number_format($stats['total_sources']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Verified</h5>
                    <h3><?= number_format($stats['verified_sources']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Active</h5>
                    <h3><?= number_format($stats['active_sources']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Blacklisted</h5>
                    <h3><?= number_format($stats['blacklisted_sources']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Avg Trust Score</h5>
                    <h3><?= number_format($stats['avg_trust_score'], 1) ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Filter</label>
                    <select name="filter" class="form-select">
                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Sources</option>
                        <option value="verified" <?= $filter === 'verified' ? 'selected' : '' ?>>Verified Only</option>
                        <option value="unverified" <?= $filter === 'unverified' ? 'selected' : '' ?>>Unverified Only</option>
                        <option value="active" <?= $filter === 'active' ? 'selected' : '' ?>>Active Only</option>
                        <option value="blacklisted" <?= $filter === 'blacklisted' ? 'selected' : '' ?>>Blacklisted</option>
                        <option value="high_trust" <?= $filter === 'high_trust' ? 'selected' : '' ?>>High Trust (80%+)</option>
                        <option value="low_trust" <?= $filter === 'low_trust' ? 'selected' : '' ?>>Low Trust (<50%)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search by name, domain, or URL...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary flex-fill">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="trusted_sources_manager.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sources Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Trusted Sources</h5>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-success" id="bulkVerifyBtn" disabled>
                    <i class="fas fa-check"></i> Bulk Verify
                </button>
                <button class="btn btn-sm btn-outline-danger" id="bulkBlacklistBtn" disabled>
                    <i class="fas fa-ban"></i> Bulk Blacklist
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="sourcesTable">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Source Name</th>
                            <th>Domain</th>
                            <th>Type</th>
                            <th>Tier</th>
                            <th>Trust Score</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sources as $source): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="source-checkbox form-check-input" 
                                           value="<?= $source['id'] ?>" data-name="<?= htmlspecialchars($source['source_name']) ?>">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($source['source_name']) ?></strong>
                                            <?php if ($source['verified']): ?>
                                                <span class="badge bg-success ms-2">Verified</span>
                                            <?php endif; ?>
                                            <?php if ($source['blacklisted']): ?>
                                                <span class="badge bg-danger ms-2">Blacklisted</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?= htmlspecialchars($source['source_url']) ?>" target="_blank" class="text-decoration-none">
                                        <?= htmlspecialchars($source['domain_name']) ?>
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-<?= getSourceTypeColor($source['source_type']) ?>">
                                        <?= htmlspecialchars($source['source_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= getCredibilityTierColor($source['credibility_tier']) ?>">
                                        <?= htmlspecialchars($source['credibility_tier']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                            <div class="progress-bar bg-<?= getTrustScoreColor($source['trust_score']) ?>" 
                                                 style="width: <?= $source['trust_score'] ?>%"></div>
                                        </div>
                                        <span class="text-muted small"><?= number_format($source['trust_score'], 1) ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" 
                                               <?= $source['active'] ? 'checked' : '' ?>
                                               onchange="toggleSourceStatus(<?= $source['id'] ?>, this.checked)">
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editSource(<?= $source['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="viewSourceDetails(<?= $source['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if (!$source['blacklisted']): ?>
                                            <button class="btn btn-outline-warning" onclick="blacklistSource(<?= $source['id'] ?>)">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- Add Source Modal -->
<div class="modal fade" id="addSourceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Trusted Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Source Name *</label>
                                <input type="text" name="source_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Source URL *</label>
                                <input type="url" name="source_url" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Domain Name *</label>
                                <input type="text" name="domain_name" class="form-control" required 
                                       placeholder="example.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Source Type</label>
                                <select name="source_type" class="form-select">
                                    <option value="NEWS_MEDIA">News Media</option>
                                    <option value="GOVERNMENT">Government</option>
                                    <option value="ACADEMIC">Academic</option>
                                    <option value="FACT_CHECK">Fact Check</option>
                                    <option value="OFFICIAL">Official</option>
                                    <option value="SOCIAL_MEDIA">Social Media</option>
                                    <option value="BLOG">Blog</option>
                                    <option value="UNKNOWN">Unknown</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Credibility Tier</label>
                                <select name="credibility_tier" class="form-select">
                                    <option value="TIER_1">Tier 1 (Highest)</option>
                                    <option value="TIER_2">Tier 2</option>
                                    <option value="TIER_3" selected>Tier 3</option>
                                    <option value="TIER_4">Tier 4</option>
                                    <option value="TIER_5">Tier 5 (Lowest)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Trust Score (%)</label>
                                <input type="number" name="trust_score" class="form-control" 
                                       min="0" max="100" step="0.1" value="50">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Verified</label>
                                <select name="verified" class="form-select">
                                    <option value="0">Not Verified</option>
                                    <option value="1">Verified</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Reliability Score (%)</label>
                                <input type="number" name="reliability_score" class="form-control" 
                                       min="0" max="100" step="0.1" value="50">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Accuracy Score (%)</label>
                                <input type="number" name="accuracy_score" class="form-control" 
                                       min="0" max="100" step="0.1" value="50">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Language</label>
                                <select name="language" class="form-select">
                                    <option value="en">English</option>
                                    <option value="ur">Urdu</option>
                                    <option value="ar">Arabic</option>
                                    <option value="hi">Hindi</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Country Code</label>
                                <input type="text" name="country" class="form-control" 
                                       placeholder="PK, US, GB, etc." maxlength="2">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_source" class="btn btn-primary">Add Source</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Source Modal (populated via JavaScript) -->
<div class="modal fade" id="editSourceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Trusted Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editSourceForm">
                <input type="hidden" name="source_id" id="editSourceId">
                <div class="modal-body">
                    <!-- Form content will be populated via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_source" class="btn btn-primary">Update Source</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Helper functions
function getSourceTypeColor($type) {
    $colors = [
        'NEWS_MEDIA' => 'primary',
        'GOVERNMENT' => 'success',
        'ACADEMIC' => 'info',
        'FACT_CHECK' => 'warning',
        'OFFICIAL' => 'secondary',
        'SOCIAL_MEDIA' => 'danger',
        'BLOG' => 'dark',
        'UNKNOWN' => 'secondary'
    ];
    return $colors[$type] ?? 'secondary';
}

function getCredibilityTierColor($tier) {
    $colors = [
        'TIER_1' => 'success',
        'TIER_2' => 'info',
        'TIER_3' => 'primary',
        'TIER_4' => 'warning',
        'TIER_5' => 'danger'
    ];
    return $colors[$tier] ?? 'secondary';
}

function getTrustScoreColor($score) {
    if ($score >= 80) return 'success';
    if ($score >= 60) return 'info';
    if ($score >= 40) return 'warning';
    return 'danger';
}
?>

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.source-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    updateBulkButtons();
});

// Update bulk buttons state
function updateBulkButtons() {
    const checkedBoxes = document.querySelectorAll('.source-checkbox:checked');
    const bulkVerifyBtn = document.getElementById('bulkVerifyBtn');
    const bulkBlacklistBtn = document.getElementById('bulkBlacklistBtn');
    
    bulkVerifyBtn.disabled = checkedBoxes.length === 0;
    bulkBlacklistBtn.disabled = checkedBoxes.length === 0;
}

// Individual checkbox change
document.querySelectorAll('.source-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkButtons);
});

// Bulk verify
document.getElementById('bulkVerifyBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.source-checkbox:checked');
    const sourceIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (confirm(`Are you sure you want to verify ${sourceIds.length} sources?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="bulk_verify" value="1">
            ${sourceIds.map(id => `<input type="hidden" name="source_ids[]" value="${id}">`).join('')}
        `;
        document.body.appendChild(form);
        form.submit();
    }
});

// Bulk blacklist
document.getElementById('bulkBlacklistBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.source-checkbox:checked');
    const sourceIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (confirm(`Are you sure you want to blacklist ${sourceIds.length} sources? This action cannot be undone.`)) {
        // Implement bulk blacklist functionality
        console.log('Bulk blacklist:', sourceIds);
    }
});

// Toggle source status
function toggleSourceStatus(sourceId, isActive) {
    fetch('api/trusted_sources_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'toggle_status',
            source_id: sourceId,
            active: isActive
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating source status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating source status');
    });
}

// Edit source
function editSource(sourceId) {
    fetch(`api/trusted_sources_api.php?action=get_source&id=${sourceId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const source = data.source;
            const modalBody = document.querySelector('#editSourceModal .modal-body');
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Source Name *</label>
                            <input type="text" name="source_name" class="form-control" value="${source.source_name}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Source URL *</label>
                            <input type="url" name="source_url" class="form-control" value="${source.source_url}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Source Type</label>
                            <select name="source_type" class="form-select">
                                <option value="NEWS_MEDIA" ${source.source_type === 'NEWS_MEDIA' ? 'selected' : ''}>News Media</option>
                                <option value="GOVERNMENT" ${source.source_type === 'GOVERNMENT' ? 'selected' : ''}>Government</option>
                                <option value="ACADEMIC" ${source.source_type === 'ACADEMIC' ? 'selected' : ''}>Academic</option>
                                <option value="FACT_CHECK" ${source.source_type === 'FACT_CHECK' ? 'selected' : ''}>Fact Check</option>
                                <option value="OFFICIAL" ${source.source_type === 'OFFICIAL' ? 'selected' : ''}>Official</option>
                                <option value="SOCIAL_MEDIA" ${source.source_type === 'SOCIAL_MEDIA' ? 'selected' : ''}>Social Media</option>
                                <option value="BLOG" ${source.source_type === 'BLOG' ? 'selected' : ''}>Blog</option>
                                <option value="UNKNOWN" ${source.source_type === 'UNKNOWN' ? 'selected' : ''}>Unknown</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Credibility Tier</label>
                            <select name="credibility_tier" class="form-select">
                                <option value="TIER_1" ${source.credibility_tier === 'TIER_1' ? 'selected' : ''}>Tier 1 (Highest)</option>
                                <option value="TIER_2" ${source.credibility_tier === 'TIER_2' ? 'selected' : ''}>Tier 2</option>
                                <option value="TIER_3" ${source.credibility_tier === 'TIER_3' ? 'selected' : ''}>Tier 3</option>
                                <option value="TIER_4" ${source.credibility_tier === 'TIER_4' ? 'selected' : ''}>Tier 4</option>
                                <option value="TIER_5" ${source.credibility_tier === 'TIER_5' ? 'selected' : ''}>Tier 5 (Lowest)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Trust Score (%)</label>
                            <input type="number" name="trust_score" class="form-control" min="0" max="100" step="0.1" value="${source.trust_score}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Reliability Score (%)</label>
                            <input type="number" name="reliability_score" class="form-control" min="0" max="100" step="0.1" value="${source.reliability_score}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Accuracy Score (%)</label>
                            <input type="number" name="accuracy_score" class="form-control" min="0" max="100" step="0.1" value="${source.accuracy_score}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Verified</label>
                            <select name="verified" class="form-select">
                                <option value="0" ${!source.verified ? 'selected' : ''}>Not Verified</option>
                                <option value="1" ${source.verified ? 'selected' : ''}>Verified</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Country Code</label>
                            <input type="text" name="country" class="form-control" value="${source.country || ''}" maxlength="2">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Language</label>
                            <select name="language" class="form-select">
                                <option value="en" ${source.language === 'en' ? 'selected' : ''}>English</option>
                                <option value="ur" ${source.language === 'ur' ? 'selected' : ''}>Urdu</option>
                                <option value="ar" ${source.language === 'ar' ? 'selected' : ''}>Arabic</option>
                                <option value="hi" ${source.language === 'hi' ? 'selected' : ''}>Hindi</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Active Status</label>
                            <select name="active" class="form-select">
                                <option value="1" ${source.active ? 'selected' : ''}>Active</option>
                                <option value="0" ${!source.active ? 'selected' : ''}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('editSourceId').value = sourceId;
            new bootstrap.Modal(document.getElementById('editSourceModal')).show();
        } else {
            alert('Error loading source data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading source data');
    });
}

// View source details
function viewSourceDetails(sourceId) {
    // Implement source details view
    console.log('View details for source:', sourceId);
}

// Blacklist source
function blacklistSource(sourceId) {
    if (confirm('Are you sure you want to blacklist this source? This will prevent it from being used in credibility analysis.')) {
        fetch('api/trusted_sources_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'blacklist',
                source_id: sourceId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error blacklisting source: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error blacklisting source');
        });
    }
}

// Initialize DataTable
$(document).ready(function() {
    $('#sourcesTable').DataTable({
        pageLength: 25,
        responsive: true,
        order: [[5, 'desc']] // Sort by trust score descending
    });
});
</script>

<?php require_once '../includes/admin-footer.php'; ?>
