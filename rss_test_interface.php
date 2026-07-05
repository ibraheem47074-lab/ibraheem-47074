<?php
/**
 * RSS Test Interface - Complete RSS System Testing
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>RSS Test Interface - PK Live News</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .status-success { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .status-info { color: #17a2b8; }
        .feed-card { margin-bottom: 15px; border-left: 4px solid #007cba; }
        .feed-success { border-left-color: #28a745; }
        .feed-error { border-left-color: #dc3545; }
        .feed-warning { border-left-color: #ffc107; }
        .code-block { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; }
        .test-section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .progress-section { margin: 15px 0; }
        .nav-pills .nav-link.active { background-color: #007cba; }
        .nav-pills .nav-link { color: #007cba; }
        .nav-pills .nav-link:hover { background-color: #e9ecef; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-rss"></i> RSS Test Interface
                    <small class="text-muted">- PK Live News</small>
                </h1>
                
                <!-- Navigation Tabs -->
                <ul class="nav nav-pills mb-4" id="rssTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="dashboard-tab" data-bs-toggle="pill" data-bs-target="#dashboard" type="button" role="tab">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="feeds-tab" data-bs-toggle="pill" data-bs-target="#feeds" type="button" role="tab">
                            <i class="fas fa-list"></i> Test Feeds
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="import-tab" data-bs-toggle="pill" data-bs-target="#import" type="button" role="tab">
                            <i class="fas fa-download"></i> Test Import
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="debug-tab" data-bs-toggle="pill" data-bs-target="#debug" type="button" role="tab">
                            <i class="fas fa-bug"></i> Debug Tools
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="fix-tab" data-bs-toggle="pill" data-bs-target="#fix" type="button" role="tab">
                            <i class="fas fa-tools"></i> Quick Fix
                        </button>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="rssTabContent">
                    
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                        <div class="test-section">
                            <h3><i class="fas fa-heartbeat"></i> RSS System Health Check</h3>
                            
                            <?php
                            try {
                                require_once 'config/database.php';
                                echo '<div class="alert alert-success"><i class="fas fa-database"></i> <strong>Database:</strong> Connected successfully</div>';
                                
                                // Check tables
                                $tables = ['news', 'categories', 'news_sources'];
                                foreach ($tables as $table) {
                                    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
                                    if (mysqli_num_rows($result) > 0) {
                                        echo '<div class="alert alert-success"><i class="fas fa-table"></i> <strong>Table:</strong> ' . $table . ' exists</div>';
                                    } else {
                                        echo '<div class="alert alert-danger"><i class="fas fa-times"></i> <strong>Table:</strong> ' . $table . ' missing</div>';
                                    }
                                }
                                
                                // Count RSS sources
                                $sources_query = "SELECT COUNT(*) as count, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active FROM news_sources WHERE type = 'rss'";
                                $result = mysqli_query($conn, $sources_query);
                                $row = mysqli_fetch_assoc($result);
                                echo '<div class="alert alert-info"><i class="fas fa-rss"></i> <strong>RSS Sources:</strong> ' . $row['count'] . ' total, ' . $row['active'] . ' active</div>';
                                
                                // Count recent articles
                                $recent_query = "SELECT COUNT(*) as count FROM news WHERE news_type = 'rss_import' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                                $result = mysqli_query($conn, $recent_query);
                                $row = mysqli_fetch_assoc($result);
                                echo '<div class="alert alert-info"><i class="fas fa-newspaper"></i> <strong>Recent Imports:</strong> ' . $row['count'] . ' articles in last 24 hours</div>';
                                
                            } catch (Exception $e) {
                                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            ?>
                            
                            <div class="mt-3">
                                <button class="btn btn-primary" onclick="location.reload()">
                                    <i class="fas fa-sync"></i> Refresh Dashboard
                                </button>
                                <a href="admin/manage-sources.php" class="btn btn-outline-primary">
                                    <i class="fas fa-cog"></i> Manage Sources
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Test Feeds Tab -->
                    <div class="tab-pane fade" id="feeds" role="tabpanel">
                        <div class="test-section">
                            <h3><i class="fas fa-rss"></i> RSS Feed Testing</h3>
                            
                            <div class="progress-section">
                                <button class="btn btn-success" onclick="testAllFeeds()">
                                    <i class="fas fa-play"></i> Test All Feeds
                                </button>
                                <button class="btn btn-warning" onclick="testQuickFeeds()">
                                    <i class="fas fa-bolt"></i> Quick Test (5 feeds)
                                </button>
                                <div id="feedProgress" class="mt-2" style="display: none;">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="feedResults" class="mt-4">
                                <div class="text-center text-muted">
                                    <i class="fas fa-arrow-up"></i> Click "Test All Feeds" to start testing
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Test Import Tab -->
                    <div class="tab-pane fade" id="import" role="tabpanel">
                        <div class="test-section">
                            <h3><i class="fas fa-download"></i> RSS Import Testing</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Import Settings</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Articles per feed:</label>
                                        <select class="form-select" id="articlesPerFeed">
                                            <option value="1">1 (Quick test)</option>
                                            <option value="3">3 (Normal)</option>
                                            <option value="5">5 (Extended)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Download images:</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="downloadImages" checked>
                                            <label class="form-check-label" for="downloadImages">
                                                Enable image download
                                            </label>
                                        </div>
                                    </div>
                                    <button class="btn btn-success" onclick="testImport()">
                                        <i class="fas fa-download"></i> Run Import Test
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <h5>Import Results</h5>
                                    <div id="importResults">
                                        <div class="text-muted">Import results will appear here...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Debug Tools Tab -->
                    <div class="tab-pane fade" id="debug" role="tabpanel">
                        <div class="test-section">
                            <h3><i class="fas fa-bug"></i> Debug Tools</h3>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <h5>Connectivity Test</h5>
                                    <p>Test server internet connection and DNS resolution</p>
                                    <a href="connectivity_test.php" class="btn btn-outline-primary" target="_blank">
                                        <i class="fas fa-network-wired"></i> Run Connectivity Test
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <h5>Detailed RSS Debug</h5>
                                    <p>Deep analysis of RSS feed issues</p>
                                    <a href="rss_debug_detailed.php" class="btn btn-outline-primary" target="_blank">
                                        <i class="fas fa-search"></i> Detailed Debug
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <h5>System Test</h5>
                                    <p>Complete RSS system validation</p>
                                    <a href="rss_fix_test.php" class="btn btn-outline-primary" target="_blank">
                                        <i class="fas fa-stethoscope"></i> System Test
                                    </a>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h5>Manual Feed Test</h5>
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="manualFeedUrl" placeholder="Enter RSS feed URL...">
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary" onclick="testManualFeed()">
                                        <i class="fas fa-search"></i> Test Feed
                                    </button>
                                </div>
                            </div>
                            <div id="manualFeedResult" class="mt-3"></div>
                        </div>
                    </div>
                    
                    <!-- Quick Fix Tab -->
                    <div class="tab-pane fade" id="fix" role="tabpanel">
                        <div class="test-section">
                            <h3><i class="fas fa-tools"></i> Quick Fix Tools</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Emergency RSS Fix</h5>
                                    <p>Fix SSL, DNS, and XML format issues automatically</p>
                                    <a href="rss_emergency_fix.php?fix=now" class="btn btn-danger">
                                        <i class="fas fa-ambulance"></i> Apply Emergency Fix
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <h5>Setup RSS Sources</h5>
                                    <p>Add working RSS feeds to your database</p>
                                    <a href="setup_rss_sources.php" class="btn btn-warning">
                                        <i class="fas fa-plus"></i> Setup RSS Sources
                                    </a>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h5>Common Issues & Solutions</h5>
                            <div class="accordion" id="issuesAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sslIssue">
                                            <i class="fas fa-lock text-warning"></i> SSL Certificate Issues
                                        </button>
                                    </h2>
                                    <div id="sslIssue" class="accordion-collapse collapse show" data-bs-parent="#issuesAccordion">
                                        <div class="accordion-body">
                                            <p><strong>Problem:</strong> OpenSSL SSL_connect: SSL_ERROR_SYSCALL</p>
                                            <p><strong>Solution:</strong> Use HTTP versions or disable SSL verification</p>
                                            <a href="rss_emergency_fix.php?fix=now" class="btn btn-sm btn-outline-primary">Auto Fix</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dnsIssue">
                                            <i class="fas fa-globe text-danger"></i> DNS Resolution Issues
                                        </button>
                                    </h2>
                                    <div id="dnsIssue" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                                        <div class="accordion-body">
                                            <p><strong>Problem:</strong> Could not resolve host</p>
                                            <p><strong>Solution:</strong> Use alternative domains or check DNS settings</p>
                                            <a href="connectivity_test.php" class="btn btn-sm btn-outline-primary">Test DNS</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#xmlIssue">
                                            <i class="fas fa-code text-info"></i> XML Format Issues
                                        </button>
                                    </h2>
                                    <div id="xmlIssue" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                                        <div class="accordion-body">
                                            <p><strong>Problem:</strong> Invalid XML format</p>
                                            <p><strong>Solution:</strong> Enhanced parser with better error handling</p>
                                            <a href="rss_debug_detailed.php" class="btn btn-sm btn-outline-primary">Debug XML</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        // Test all feeds
        function testAllFeeds() {
            const resultsDiv = document.getElementById('feedResults');
            const progressDiv = document.getElementById('feedProgress');
            const progressBar = progressDiv.querySelector('.progress-bar');
            
            resultsDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Testing feeds...</div>';
            progressDiv.style.display = 'block';
            
            fetch('rss_test_interface.php?action=test_all_feeds')
                .then(response => response.json())
                .then(data => {
                    progressBar.style.width = '100%';
                    setTimeout(() => {
                        progressDiv.style.display = 'none';
                        displayFeedResults(data);
                    }, 500);
                })
                .catch(error => {
                    resultsDiv.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                    progressDiv.style.display = 'none';
                });
        }
        
        // Test quick feeds
        function testQuickFeeds() {
            const resultsDiv = document.getElementById('feedResults');
            resultsDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Quick testing...</div>';
            
            fetch('rss_test_interface.php?action=test_quick_feeds')
                .then(response => response.json())
                .then(data => {
                    displayFeedResults(data);
                })
                .catch(error => {
                    resultsDiv.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                });
        }
        
        // Display feed results
        function displayFeedResults(data) {
            const resultsDiv = document.getElementById('feedResults');
            let html = '';
            
            data.feeds.forEach(feed => {
                const statusClass = feed.valid ? 'feed-success' : 'feed-error';
                const icon = feed.valid ? 'fas fa-check-circle' : 'fas fa-times-circle';
                const status = feed.valid ? 'Working' : 'Failed';
                const error = feed.error ? '<div class="text-danger small">' + feed.error + '</div>' : '';
                
                html += `
                    <div class="card feed-card ${statusClass}">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="${icon}"></i> ${feed.name}
                                <span class="badge bg-${feed.valid ? 'success' : 'danger'} float-end">${status}</span>
                            </h5>
                            <p class="card-text">
                                <strong>URL:</strong> <code class="code-block">${feed.url}</code><br>
                                <strong>Items:</strong> ${feed.items || 'N/A'}
                            </p>
                            ${error}
                        </div>
                    </div>
                `;
            });
            
            resultsDiv.innerHTML = html;
        }
        
        // Test import
        function testImport() {
            const resultsDiv = document.getElementById('importResults');
            const articlesPerFeed = document.getElementById('articlesPerFeed').value;
            const downloadImages = document.getElementById('downloadImages').checked;
            
            resultsDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Running import test...</div>';
            
            fetch('rss_test_interface.php?action=test_import', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    articlesPerFeed: articlesPerFeed,
                    downloadImages: downloadImages
                })
            })
            .then(response => response.json())
            .then(data => {
                let html = `
                    <div class="alert alert-${data.success ? 'success' : 'danger'}">
                        <h6><i class="fas fa-${data.success ? 'check' : 'times'}-circle"></i> Import ${data.success ? 'Success' : 'Failed'}</h6>
                        <strong>Feeds:</strong> ${data.total_feeds}<br>
                        <strong>Successful:</strong> ${data.successful_feeds}<br>
                        <strong>Articles Imported:</strong> ${data.imported_articles}<br>
                        <strong>Duplicates:</strong> ${data.duplicate_articles}
                    </div>
                `;
                
                if (data.details && data.details.length > 0) {
                    html += '<h6>Feed Details:</h6>';
                    data.details.forEach(detail => {
                        const status = detail.error ? 'danger' : 'success';
                        html += `<div class="alert alert-${status} alert-sm">
                            <strong>${detail.source_name}:</strong> ${detail.error || detail.imported_articles + ' imported'}
                        </div>`;
                    });
                }
                
                resultsDiv.innerHTML = html;
            })
            .catch(error => {
                resultsDiv.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
            });
        }
        
        // Test manual feed
        function testManualFeed() {
            const url = document.getElementById('manualFeedUrl').value;
            const resultDiv = document.getElementById('manualFeedResult');
            
            if (!url) {
                resultDiv.innerHTML = '<div class="alert alert-warning">Please enter a URL</div>';
                return;
            }
            
            resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Testing...</div>';
            
            fetch('rss_test_interface.php?action=test_manual_feed', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({url: url})
            })
            .then(response => response.json())
            .then(data => {
                const alertClass = data.valid ? 'success' : 'danger';
                const icon = data.valid ? 'check' : 'times';
                let html = `
                    <div class="alert alert-${alertClass}">
                        <h6><i class="fas fa-${icon}-circle"></i> Feed ${data.valid ? 'Valid' : 'Invalid'}</h6>
                        <strong>Items:</strong> ${data.items || 'N/A'}<br>
                        <strong>Format:</strong> ${data.format || 'N/A'}
                    </div>
                `;
                
                if (data.error) {
                    html += '<div class="alert alert-danger"><strong>Error:</strong> ' + data.error + '</div>';
                }
                
                resultDiv.innerHTML = html;
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
            });
        }
    </script>
    
    <?php
    // Handle AJAX requests
    if (isset($_GET['action'])) {
        header('Content-Type: application/json');
        
        try {
            require_once 'config/database.php';
            require_once 'includes/enhanced_rss_parser.php';
            
            $response = ['success' => false];
            
            switch ($_GET['action']) {
                case 'test_all_feeds':
                    $feeds = [
                        ['name' => 'BBC News', 'url' => 'https://feeds.bbci.co.uk/news/rss.xml'],
                        ['name' => 'CNN', 'url' => 'http://rss.cnn.com/rss/edition.rss'],
                        ['name' => 'Reuters', 'url' => 'https://feeds.reuters.com/reuters/topNews'],
                        ['name' => 'Google News', 'url' => 'https://news.google.com/rss'],
                        ['name' => 'Yahoo News', 'url' => 'https://news.yahoo.com/rss']
                    ];
                    
                    $parser = new EnhancedRSSParser();
                    $results = [];
                    
                    foreach ($feeds as $feed) {
                        try {
                            $validation = $parser->validateFeed($feed['url']);
                            $results[] = [
                                'name' => $feed['name'],
                                'url' => $feed['url'],
                                'valid' => $validation['valid'],
                                'items' => $validation['items_count'] ?? 0,
                                'error' => $validation['valid'] ? null : $validation['error']
                            ];
                        } catch (Exception $e) {
                            $results[] = [
                                'name' => $feed['name'],
                                'url' => $feed['url'],
                                'valid' => false,
                                'items' => 0,
                                'error' => $e->getMessage()
                            ];
                        }
                    }
                    
                    $response['feeds'] = $results;
                    echo json_encode($response);
                    exit;
                    
                case 'test_quick_feeds':
                    $quick_feeds = [
                        ['name' => 'Google News', 'url' => 'https://news.google.com/rss'],
                        ['name' => 'Yahoo News', 'url' => 'https://news.yahoo.com/rss']
                    ];
                    
                    $parser = new EnhancedRSSParser();
                    $results = [];
                    
                    foreach ($quick_feeds as $feed) {
                        try {
                            $validation = $parser->validateFeed($feed['url']);
                            $results[] = [
                                'name' => $feed['name'],
                                'url' => $feed['url'],
                                'valid' => $validation['valid'],
                                'items' => $validation['items_count'] ?? 0,
                                'error' => $validation['valid'] ? null : $validation['error']
                            ];
                        } catch (Exception $e) {
                            $results[] = [
                                'name' => $feed['name'],
                                'url' => $feed['url'],
                                'valid' => false,
                                'items' => 0,
                                'error' => $e->getMessage()
                            ];
                        }
                    }
                    
                    $response['feeds'] = $results;
                    echo json_encode($response);
                    exit;
                    
                case 'test_manual_feed':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $url = $input['url'] ?? '';
                    
                    if ($url) {
                        $parser = new EnhancedRSSParser();
                        try {
                            $validation = $parser->validateFeed($url);
                            $response = [
                                'valid' => $validation['valid'],
                                'items' => $validation['items_count'] ?? 0,
                                'format' => $validation['format'] ?? 'Unknown',
                                'error' => $validation['valid'] ? null : $validation['error']
                            ];
                        } catch (Exception $e) {
                            $response = [
                                'valid' => false,
                                'items' => 0,
                                'format' => 'Unknown',
                                'error' => $e->getMessage()
                            ];
                        }
                    }
                    
                    echo json_encode($response);
                    exit;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    // Handle import test
    if (isset($_GET['action']) && $_GET['action'] == 'test_import') {
        header('Content-Type: application/json');
        
        try {
            require_once 'config/database.php';
            require_once 'includes/auto_news_importer.php';
            
            $input = json_decode(file_get_contents('php://input'), true);
            $articlesPerFeed = (int)($input['articlesPerFeed'] ?? 1);
            $downloadImages = (bool)($input['downloadImages'] ?? true);
            
            $importer = new AutoNewsImporter($conn);
            $importer->setMaxArticlesPerFeed($articlesPerFeed);
            $importer->setDownloadImages($downloadImages);
            
            $results = $importer->importFromAllSources();
            
            echo json_encode([
                'success' => true,
                'total_feeds' => $results['total_feeds'],
                'successful_feeds' => $results['successful_feeds'],
                'imported_articles' => $results['imported_articles'],
                'duplicate_articles' => $results['duplicate_articles'],
                'details' => $results['details'] ?? []
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    ?>
</body>
</html>
