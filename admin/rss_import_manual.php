<?php
/**
 * Manual RSS Import with Real-time Feedback
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auto_news_importer.php';
require_once __DIR__ . '/../includes/admin-header.php';

// Handle manual import
if (isset($_POST['import'])) {
    $maxArticles = isset($_POST['max_articles']) ? (int)$_POST['max_articles'] : 5;
    $selectedSources = isset($_POST['sources']) ? $_POST['sources'] : [];
    
    echo "<div class='container-fluid'>";
    echo "<h2>Running RSS Import...</h2>";
    
    try {
        $importer = new AutoNewsImporter($conn);
        $importer->setMaxArticlesPerFeed($maxArticles);
        $importer->setDownloadImages(true);
        
        if (empty($selectedSources)) {
            // Import from all sources
            $results = $importer->importFromAllSources();
        } else {
            // Import from selected sources
            $results = [
                'total_feeds' => 0,
                'successful_feeds' => 0,
                'total_articles' => 0,
                'imported_articles' => 0,
                'duplicate_articles' => 0,
                'error_feeds' => 0,
                'details' => []
            ];
            
            foreach ($selectedSources as $sourceId) {
                $sourceQuery = "SELECT * FROM news_sources WHERE id = ? AND type = 'rss' AND status = 'active'";
                $stmt = mysqli_prepare($conn, $sourceQuery);
                mysqli_stmt_bind_param($stmt, 'i', $sourceId);
                mysqli_stmt_execute($stmt);
                $source = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                
                if ($source) {
                    $results['total_feeds']++;
                    try {
                        $feedResult = $importer->importFromSource($source);
                        $results['successful_feeds']++;
                        $results['total_articles'] += $feedResult['total_articles'];
                        $results['imported_articles'] += $feedResult['imported_articles'];
                        $results['duplicate_articles'] += $feedResult['duplicate_articles'];
                        $results['details'][] = $feedResult;
                    } catch (Exception $e) {
                        $results['error_feeds']++;
                        $results['details'][] = [
                            'source_name' => $source['name'],
                            'source_url' => $source['url'],
                            'error' => $e->getMessage(),
                            'total_articles' => 0,
                            'imported_articles' => 0,
                            'duplicate_articles' => 0
                        ];
                    }
                }
            }
        }
        
        // Display results
        echo "<div class='row'>";
        echo "<div class='col-md-12'>";
        echo "<div class='card'>";
        echo "<div class='card-header'><h3>Import Results</h3></div>";
        echo "<div class='card-body'>";
        
        echo "<div class='row mb-3'>";
        echo "<div class='col-md-3'><strong>Total Feeds:</strong> {$results['total_feeds']}</div>";
        echo "<div class='col-md-3'><strong>Successful:</strong> {$results['successful_feeds']}</div>";
        echo "<div class='col-md-3'><strong>Failed:</strong> {$results['error_feeds']}</div>";
        echo "<div class='col-md-3'><strong>Articles Imported:</strong> {$results['imported_articles']}</div>";
        echo "</div>";
        
        echo "<h4>Feed Details:</h4>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Source</th><th>Status</th><th>Articles Found</th><th>Imported</th><th>Duplicates</th><th>Details</th></tr></thead>";
        echo "<tbody>";
        
        foreach ($results['details'] as $detail) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($detail['source_name']) . "</strong><br><small>" . htmlspecialchars(substr($detail['source_url'], 0, 50)) . "...</small></td>";
            
            if (isset($detail['error'])) {
                echo "<td><span class='badge bg-danger'>Error</span></td>";
                echo "<td>-</td>";
                echo "<td>-</td>";
                echo "<td>-</td>";
                echo "<td class='text-danger'>" . htmlspecialchars($detail['error']) . "</td>";
            } else {
                echo "<td><span class='badge bg-success'>Success</span></td>";
                echo "<td>{$detail['total_articles']}</td>";
                echo "<td>{$detail['imported_articles']}</td>";
                echo "<td>{$detail['duplicate_articles']}</td>";
                echo "<td>";
                if ($detail['imported_articles'] > 0) {
                    echo "<small class='text-success'>✓ Articles imported successfully</small>";
                } else {
                    echo "<small class='text-warning'>No new articles found</small>";
                }
                echo "</td>";
            }
            
            echo "</tr>";
        }
        
        echo "</tbody></table></div>";
        
        echo "<div class='mt-3'>";
        echo "<a href='view_draft_articles.php' class='btn btn-primary'>View Draft Articles</a>";
        echo "<a href='rss_import_manual.php' class='btn btn-secondary'>Import More</a>";
        echo "</div>";
        
        echo "</div></div></div></div>";
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'><strong>Error:</strong> " . $e->getMessage() . "</div>";
    }
    
    echo "</div>";
    require_once __DIR__ . '/../includes/admin-footer.php';
    exit;
}

// Get RSS sources
$sourcesQuery = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active' ORDER BY name ASC";
$sources = mysqli_query($conn, $sourcesQuery);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manual RSS Import</h2>
        <div>
            <a href="view_draft_articles.php" class="btn btn-info">View Draft Articles</a>
            <a href="update_rss_feeds.php" class="btn btn-warning">Update RSS Feeds</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Import Settings</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Maximum Articles per Feed</label>
                            <select name="max_articles" class="form-select">
                                <option value="3">3 articles</option>
                                <option value="5" selected>5 articles</option>
                                <option value="10">10 articles</option>
                                <option value="20">20 articles</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Select RSS Sources</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleAllSources()">
                                <label class="form-check-label" for="selectAll">
                                    <strong>Select All Sources</strong>
                                </label>
                            </div>
                            <hr>
                            
                            <?php if (mysqli_num_rows($sources) > 0): ?>
                                <?php while ($source = mysqli_fetch_assoc($sources)): ?>
                                    <div class="form-check">
                                        <input class="form-check-input source-checkbox" type="checkbox" name="sources[]" value="<?= $source['id'] ?>" id="source_<?= $source['id'] ?>" checked>
                                        <label class="form-check-label" for="source_<?= $source['id'] ?>">
                                            <strong><?= htmlspecialchars($source['name']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($source['url'], 0, 60)) ?>...</small>
                                        </label>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted">No RSS sources found. <a href="update_rss_feeds.php">Update RSS feeds</a> first.</p>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" name="import" class="btn btn-primary">
                            <i class="fas fa-download"></i> Start Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Import Statistics</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get import stats
                    $statsQuery = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as drafts,
                        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as today
                        FROM news WHERE news_type = 'rss_import'";
                    $statsResult = mysqli_query($conn, $statsQuery);
                    $stats = mysqli_fetch_assoc($statsResult);
                    
                    // Ensure values are not null
                    $total = intval($stats['total'] ?? 0);
                    $drafts = intval($stats['drafts'] ?? 0);
                    $published = intval($stats['published'] ?? 0);
                    $today = intval($stats['today'] ?? 0);
                    ?>
                    
                    <div class="mb-3">
                        <strong>Total RSS Articles:</strong><br>
                        <span class="badge bg-primary"><?= number_format($total) ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Draft Articles:</strong><br>
                        <span class="badge bg-warning"><?= number_format($drafts) ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Published Articles:</strong><br>
                        <span class="badge bg-success"><?= number_format($published) ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Imported Today:</strong><br>
                        <span class="badge bg-info"><?= number_format($today) ?></span>
                    </div>
                    
                    <a href="view_draft_articles.php" class="btn btn-sm btn-outline-primary w-100 mb-2">Manage Articles</a>
                    <a href="test_rss_feeds.php" class="btn btn-sm btn-outline-secondary w-100">Test RSS Feeds</a>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="cron_import_news.php?cron_key=pk_live_news_2024_cron" class="btn btn-sm btn-outline-success w-100 mb-2" target="_blank">
                        Run Cron Import
                    </a>
                    <a href="update_rss_feeds.php" class="btn btn-sm btn-outline-warning w-100 mb-2">
                        Update RSS URLs
                    </a>
                    <a href="check_news_sources.php" class="btn btn-sm btn-outline-info w-100">
                        Check Sources
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAllSources() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.source-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// Update select all checkbox when individual checkboxes change
document.querySelectorAll('.source-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.source-checkbox');
        const checkedBoxes = document.querySelectorAll('.source-checkbox:checked');
        const selectAll = document.getElementById('selectAll');
        
        selectAll.checked = allCheckboxes.length === checkedBoxes.length;
    });
});
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
