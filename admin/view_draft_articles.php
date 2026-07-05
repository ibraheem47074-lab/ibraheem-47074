<?php
/**
 * View and manage RSS imported draft articles
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin-header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filter options
$status = isset($_GET['status']) ? $_GET['status'] : 'draft';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$whereConditions = ["news_type = 'rss_import'"];
$params = [];
$types = '';

if ($status) {
    $whereConditions[] = "n.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($category > 0) {
    $whereConditions[] = "n.category_id = ?";
    $params[] = $category;
    $types .= 'i';
}

if ($search) {
    $whereConditions[] = "(n.title LIKE ? OR n.content LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM news n WHERE $whereClause";
$countStmt = mysqli_prepare($conn, $countQuery);
if ($params) {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}
mysqli_stmt_execute($countStmt);
$totalResult = mysqli_stmt_get_result($countStmt);
$total = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($total / $perPage);

// Get articles
$query = "SELECT n.*, c.name as category_name 
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          WHERE $whereClause 
          ORDER BY n.created_at DESC 
          LIMIT ? OFFSET ?";

$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$articles = mysqli_stmt_get_result($stmt);

// Get categories for filter
$catQuery = "SELECT id, name FROM categories ORDER BY name";
$categories = mysqli_query($conn, $catQuery);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>RSS Imported Articles</h2>
        <div>
            <a href="rss_import.php" class="btn btn-primary">Run RSS Import</a>
            <a href="view_draft_articles.php?status=published" class="btn btn-success">View Published</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" <?= $status == 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $status == 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="0">All Categories</option>
                        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Search title or content...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="view_draft_articles.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Articles</h5>
                    <h3><?= number_format($total) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Drafts</h5>
                    <?php 
                    $draftQuery = "SELECT COUNT(*) as count FROM news WHERE news_type = 'rss_import' AND status = 'draft'";
                    $draftResult = mysqli_query($conn, $draftQuery);
                    $draftCount = mysqli_fetch_assoc($draftResult)['count'];
                    ?>
                    <h3><?= number_format($draftCount) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Published</h5>
                    <?php 
                    $pubQuery = "SELECT COUNT(*) as count FROM news WHERE news_type = 'rss_import' AND status = 'published'";
                    $pubResult = mysqli_query($conn, $pubQuery);
                    $pubCount = mysqli_fetch_assoc($pubResult)['count'];
                    ?>
                    <h3><?= number_format($pubCount) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">This Week</h5>
                    <?php 
                    $weekQuery = "SELECT COUNT(*) as count FROM news WHERE news_type = 'rss_import' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    $weekResult = mysqli_query($conn, $weekQuery);
                    $weekCount = mysqli_fetch_assoc($weekResult)['count'];
                    ?>
                    <h3><?= number_format($weekCount) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Articles Table -->
    <div class="card">
        <div class="card-body">
            <?php if (mysqli_num_rows($articles) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Author</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($article = mysqli_fetch_assoc($articles)): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($article['title']) ?></strong>
                                        <?php if ($article['image']): ?>
                                            <br><small class="text-muted">📷 Image: <?= $article['image_type'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($article['category_name'] ?? 'Uncategorized') ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'draft' => 'secondary',
                                            'published' => 'success',
                                            'pending' => 'warning'
                                        ];
                                        $badgeClass = $statusClass[$article['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($article['status']) ?></span>
                                    </td>
                                    <td>System Import</td>
                                    <td>
                                        <small><?= date('M j, Y H:i', strtotime($article['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-primary">Edit</a>
                                            <a href="../article.php?id=<?= $article['id'] ?>" target="_blank" class="btn btn-outline-info">View</a>
                                            <?php if ($article['status'] === 'draft'): ?>
                                                <button type="button" class="btn btn-outline-success" onclick="publishArticle(<?= $article['id'] ?>)">Publish</button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteArticle(<?= $article['id'] ?>)">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <h4>No RSS imported articles found</h4>
                    <p class="text-muted">Try running the RSS import or adjust your filters.</p>
                    <a href="rss_import.php" class="btn btn-primary">Run RSS Import Now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function publishArticle(id) {
    if (confirm('Are you sure you want to publish this article?')) {
        fetch('ajax/publish_article.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}

function deleteArticle(id) {
    if (confirm('Are you sure you want to delete this article? This cannot be undone.')) {
        fetch('ajax/delete_article.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
