<?php
// PK Live News - Convert External Articles to Original Articles
// This script converts external articles (with source_url) to original internal articles

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin-header.php';

$message = '';
$message_type = '';
$converted_count = 0;

// Handle conversion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['convert_article'])) {
    $article_id = intval($_POST['article_id']);
    
    if ($article_id > 0) {
        // Get the article
        $query = "SELECT * FROM news WHERE id = ? AND source_url IS NOT NULL AND source_url != ''";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $article_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($article = mysqli_fetch_assoc($result)) {
            // Remove copyright attribution and read more links
            $clean_content = $article['content'];
            
            // Remove source attribution pattern
            $clean_content = preg_replace('/<p><em><strong>Source:.*?<\/em><\/p>/i', '', $clean_content);
            
            // Remove read more pattern
            $clean_content = preg_replace('/<p><strong><a href=".*?" target="_blank" rel="noopener">Read full story on.*?<\/a><\/strong><\/p>/i', '', $clean_content);
            
            // Clean up extra whitespace
            $clean_content = preg_replace('/\n\n+/', "\n\n", $clean_content);
            $clean_content = trim($clean_content);
            
            // Update the article
            $update_query = "UPDATE news SET 
                            news_type = 'internal',
                            content = ?,
                            status = 'published',
                            source_url = NULL
                            WHERE id = ?";
            
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'si', $clean_content, $article_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $message = 'Article successfully converted to original article!';
                $message_type = 'success';
                $converted_count = 1;
            } else {
                $message = 'Error converting article: ' . mysqli_error($conn);
                $message_type = 'danger';
            }
            
            mysqli_stmt_close($update_stmt);
        } else {
            $message = 'Article not found or not an external article';
            $message_type = 'danger';
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Handle bulk conversion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['convert_all'])) {
    $category_filter = isset($_POST['category_id']) && !empty($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $status_filter = isset($_POST['status_filter']) ? $_POST['status_filter'] : 'all';
    
    // Build query
    $where_clause = "source_url IS NOT NULL AND source_url != ''";
    $params = [];
    $types = '';
    
    if ($category_filter > 0) {
        $where_clause .= " AND category_id = ?";
        $params[] = $category_filter;
        $types .= 'i';
    }
    
    if ($status_filter !== 'all') {
        $where_clause .= " AND status = ?";
        $params[] = $status_filter;
        $types .= 's';
    }
    
    // Get articles to convert
    $query = "SELECT id, content FROM news WHERE $where_clause";
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $query);
    }
    
    $converted = 0;
    $errors = 0;
    
    while ($article = mysqli_fetch_assoc($result)) {
        // Clean content
        $clean_content = $article['content'];
        $clean_content = preg_replace('/<p><em><strong>Source:.*?<\/em><\/p>/i', '', $clean_content);
        $clean_content = preg_replace('/<p><strong><a href=".*?" target="_blank" rel="noopener">Read full story on.*?<\/a><\/strong><\/p>/i', '', $clean_content);
        $clean_content = preg_replace('/\n\n+/', "\n\n", $clean_content);
        $clean_content = trim($clean_content);
        
        // Update
        $update_query = "UPDATE news SET news_type = 'internal', content = ?, status = 'published', source_url = NULL WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'si', $clean_content, $article['id']);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $converted++;
        } else {
            $errors++;
        }
        
        mysqli_stmt_close($update_stmt);
    }
    
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    
    $message = "Bulk conversion complete: $converted external articles converted to original, $errors errors";
    $message_type = $errors > 0 ? 'warning' : 'success';
    $converted_count = $converted;
}

// Get external articles
$external_query = "SELECT n.*, c.name as category_name, u.name as author_name 
              FROM news n 
              LEFT JOIN categories c ON n.category_id = c.id 
              LEFT JOIN users u ON n.author_id = u.id 
              WHERE n.source_url IS NOT NULL AND n.source_url != '' 
              ORDER BY n.created_at DESC 
              LIMIT 50";
$external_result = mysqli_query($conn, $external_query);

// Get categories for filter
$categories_query = "SELECT id, name FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-exchange-alt me-2"></i>Convert External to Original Articles</h2>
            <p class="text-muted">Convert external articles (with source URLs) to original internal articles for AdSense compliance</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bolt me-2"></i>Bulk Convert All External Articles</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Convert all external articles (with source URLs) to original published articles. This will:</p>
                    <ul class="small">
                        <li>Remove source attribution and "Read more" links</li>
                        <li>Clear source URL to make it original content</li>
                        <li>Keep current status (or change if filtered)</li>
                        <li>Make content AdSense-compliant</li>
                    </ul>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Filter by Category (Optional)</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                <?php 
                                mysqli_data_seek($categories_result, 0);
                                while ($category = mysqli_fetch_assoc($categories_result)): 
                                ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status_filter" class="form-label">Filter by Status (Optional)</label>
                            <select class="form-select" id="status_filter" name="status_filter">
                                <option value="all">All Statuses</option>
                                <option value="draft">Draft Only</option>
                                <option value="published">Published Only</option>
                            </select>
                        </div>
                        <button type="submit" name="convert_all" class="btn btn-warning" onclick="return confirm('Are you sure you want to convert all external articles? This action cannot be undone.');">
                            <i class="fas fa-exchange-alt me-1"></i>Convert All External Articles
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle me-2"></i>Conversion Info</h5>
                </div>
                <div class="card-body">
                    <h6>What happens during conversion:</h6>
                    <ul class="small">
                        <li><strong>Content:</strong> Removes copyright attribution and links</li>
                        <li><strong>Source URL:</strong> Cleared to make it original content</li>
                        <li><strong>Status:</strong> Remains unchanged (unless filtered)</li>
                        <li><strong>AdSense:</strong> Makes content compliant with original content requirements</li>
                    </ul>
                    <hr>
                    <h6>Why convert?</h6>
                    <ul class="small">
                        <li>Makes external content your own for AdSense</li>
                        <li>Removes external source attribution</li>
                        <li>Treats as original content (no source URL)</li>
                        <li>Improves AdSense approval chances</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>External Articles (<?php echo mysqli_num_rows($external_result); ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($article = mysqli_fetch_assoc($external_result)): ?>
                                    <tr>
                                        <td><?php echo $article['id']; ?></td>
                                        <td>
                                            <a href="../article.php?id=<?php echo $article['id']; ?>" target="_blank">
                                                <?php echo htmlspecialchars(substr($article['title'], 0, 50)); ?>...
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($article['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td><?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $article['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($article['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y H:i', strtotime($article['created_at'])); ?></td>
                                        <td>
                                            <?php if (!empty($article['source_url'])): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                                    <button type="submit" name="convert_article" class="btn btn-sm btn-primary" title="Convert to Original">
                                                        <i class="fas fa-exchange-alt"></i> Convert
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">Already original</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
