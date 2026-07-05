<?php
// PK Live News RSS Import Management
// Administrative interface for RSS feed imports

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin-header.php';

// Initialize variables
$message = '';
$message_type = '';
$import_results = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_source'])) {
        // Add new RSS source
        $name = clean_input($_POST['source_name']);
        $url = clean_input($_POST['source_url']);
        $category_id = intval($_POST['category_id']);
        
        if (!empty($name) && !empty($url) && $category_id > 0) {
            // Validate URL
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $query = "INSERT INTO news_sources (name, url, category_id, type, status) VALUES (?, ?, ?, 'rss', 'active')";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ssi', $name, $url, $category_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = 'RSS source added successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Error adding RSS source: ' . mysqli_error($conn);
                    $message_type = 'danger';
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = 'Please enter a valid URL';
                $message_type = 'danger';
            }
        } else {
            $message = 'Please fill in all required fields';
            $message_type = 'danger';
        }
    } elseif (isset($_POST['import_news'])) {
        // Import news from RSS sources
        require_once __DIR__ . '/../includes/enhanced_rss_parser.php';
        
        try {
            $parser = new EnhancedRSSParser();
            $imported = 0;
            $duplicates = 0;
            $errors = [];
            
            // Get all active RSS sources
            $query = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active'";
            $result = mysqli_query($conn, $query);
            
            while ($source = mysqli_fetch_assoc($result)) {
                try {
                    $rss_url = !empty($source['rss_url']) ? $source['rss_url'] : $source['url'];
                    $articles = $parser->parseRSS($rss_url);
                    
                    foreach ($articles as $article) {
                        // Check for duplicates
                        $check_query = "SELECT id FROM news WHERE title = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                        $check_stmt = mysqli_prepare($conn, $check_query);
                        mysqli_stmt_bind_param($check_stmt, 's', $article['title']);
                        mysqli_stmt_execute($check_stmt);
                        $check_result = mysqli_stmt_get_result($check_stmt);
                        
                        if (mysqli_num_rows($check_result) === 0) {
                            // Insert new article
                            $insert_query = "INSERT INTO news (title, content, summary, category_id, author, image, source_url, news_type, status, created_at) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, 'rss_import', 'published', NOW())";
                            $insert_stmt = mysqli_prepare($conn, $insert_query);
                            
                            $summary = substr(strip_tags($article['content']), 0, 300);
                            $author = $article['author'] ?? 'RSS Import';
                            $image = $article['image'] ?? '';
                            
                            mysqli_stmt_bind_param($insert_stmt, 'sssssss', 
                                $article['title'], 
                                $article['content'], 
                                $summary, 
                                $source['category_id'], 
                                $author, 
                                $image, 
                                $article['link']
                            );
                            
                            if (mysqli_stmt_execute($insert_stmt)) {
                                $imported++;
                            }
                            mysqli_stmt_close($insert_stmt);
                        } else {
                            $duplicates++;
                        }
                        mysqli_stmt_close($check_stmt);
                    }
                    
                    // Update last scraped time
                    $update_query = "UPDATE news_sources SET last_scraped = NOW() WHERE id = ?";
                    $update_stmt = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($update_stmt, 'i', $source['id']);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                    
                } catch (Exception $e) {
                    $errors[] = 'Error importing from ' . $source['name'] . ': ' . $e->getMessage();
                }
            }
            
            $import_results = [
                'imported' => $imported,
                'duplicates' => $duplicates,
                'errors' => $errors
            ];
            
            $message = "Import completed: {$imported} articles imported, {$duplicates} duplicates found";
            $message_type = count($errors) > 0 ? 'warning' : 'success';
            
        } catch (Exception $e) {
            $message = 'Import error: ' . $e->getMessage();
            $message_type = 'danger';
        }
    } elseif (isset($_POST['delete_source'])) {
        // Delete RSS source
        $source_id = intval($_POST['source_id']);
        
        if ($source_id > 0) {
            $query = "DELETE FROM news_sources WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $source_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = 'RSS source deleted successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error deleting RSS source';
                $message_type = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['toggle_status'])) {
        // Toggle RSS source status
        $source_id = intval($_POST['source_id']);
        $new_status = $_POST['new_status'] === 'active' ? 'active' : 'inactive';
        
        if ($source_id > 0) {
            $query = "UPDATE news_sources SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'si', $new_status, $source_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = 'RSS source status updated!';
                $message_type = 'success';
            } else {
                $message = 'Error updating RSS source status';
                $message_type = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Get RSS sources
$sources_query = "SELECT ns.*, c.name as category_name FROM news_sources ns 
                 LEFT JOIN categories c ON ns.category_id = c.id 
                 WHERE ns.type = 'rss' 
                 ORDER BY ns.name";
$sources_result = mysqli_query($conn, $sources_query);

// Get categories for dropdown
$categories_query = "SELECT id, name FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-rss me-2"></i>RSS Import Management</h2>
            <p class="text-muted">Manage RSS news sources and import articles</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($import_results): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar me-2"></i>Import Results</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h3 class="text-success"><?php echo $import_results['imported']; ?></h3>
                                    <p class="text-muted">Articles Imported</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h3 class="text-warning"><?php echo $import_results['duplicates']; ?></h3>
                                    <p class="text-muted">Duplicates Found</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h3 class="text-danger"><?php echo count($import_results['errors']); ?></h3>
                                    <p class="text-muted">Errors</p>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($import_results['errors'])): ?>
                            <div class="mt-3">
                                <h6>Errors:</h6>
                                <ul class="list-unstyled">
                                    <?php foreach ($import_results['errors'] as $error): ?>
                                        <li class="text-danger small"><i class="fas fa-exclamation-triangle me-1"></i><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>RSS Sources</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>URL</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Last Scraped</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($source = mysqli_fetch_assoc($sources_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($source['name']); ?></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($source['url']); ?>" target="_blank" class="text-truncate d-block" style="max-width: 200px;">
                                                <?php echo htmlspecialchars($source['url']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($source['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $source['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($source['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $source['last_scraped'] ? date('M j, Y H:i', strtotime($source['last_scraped'])) : 'Never'; ?>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="source_id" value="<?php echo $source['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $source['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" name="toggle_status" class="btn btn-sm btn-<?php echo $source['status'] === 'active' ? 'warning' : 'success'; ?>">
                                                    <i class="fas fa-<?php echo $source['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this RSS source?');">
                                                <input type="hidden" name="source_id" value="<?php echo $source['id']; ?>">
                                                <button type="submit" name="delete_source" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-plus me-2"></i>Add RSS Source</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="source_name" class="form-label">Source Name</label>
                            <input type="text" class="form-control" id="source_name" name="source_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="source_url" class="form-label">RSS URL</label>
                            <input type="url" class="form-control" id="source_url" name="source_url" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" name="add_source" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add Source
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5><i class="fas fa-download me-2"></i>Import News</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Import the latest articles from all active RSS sources.</p>
                    <form method="POST">
                        <button type="submit" name="import_news" class="btn btn-success w-100">
                            <i class="fas fa-download me-1"></i>Import All News
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle me-2"></i>RSS Feeds</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Your RSS feeds are available at:</p>
                    <ul class="list-unstyled small">
                        <li><a href="../rss.php" target="_blank">Latest News</a></li>
                        <li><a href="../rss.php?type=breaking" target="_blank">Breaking News</a></li>
                        <li><a href="../rss.php?type=popular" target="_blank">Popular News</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
