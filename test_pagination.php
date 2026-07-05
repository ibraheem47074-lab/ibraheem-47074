<?php
require_once 'config/database.php';

// Test pagination functionality
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND published_at <= NOW()";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Get paginated news
$news_query = "SELECT n.*, c.name as category_name
               FROM news n 
               LEFT JOIN categories c ON n.category_id = c.id 
               WHERE n.status = 'published' AND n.published_at <= NOW() 
               ORDER BY n.published_at DESC 
               LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $news_query);
mysqli_stmt_bind_param($stmt, 'ii', $per_page, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagination Test - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4"><i class="fas fa-list me-2"></i>Pagination Test</h1>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Test Results:</strong> Showing <?php echo ($offset + 1) . '-' . min($offset + $per_page, $total_records); ?> of <?php echo $total_records; ?> total articles
        </div>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row g-4">
                <?php while ($news = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-4">
                        <div class="card border-0 shadow">
                            <?php if ($news['image']): ?>
                                <img src="<?php echo htmlspecialchars($news['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($news['title']); ?>" style="height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars(substr($news['title'], 0, 50)) . '...'; ?></h6>
                                <p class="card-text small text-muted"><?php echo date('M j, Y', strtotime($news['published_at'])); ?></p>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($news['category_name'] ?? 'Uncategorized'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Pagination" class="mt-5">
                    <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                            }
                            ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        
                        <div class="d-flex align-items-center gap-2">
                            <label for="pageSelect" class="form-label mb-0 me-2">Go to page:</label>
                            <select id="pageSelect" class="form-select form-select-sm" onchange="window.location.href='?page=' + this.value">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == $page ? 'selected' : ''; ?>>
                                        Page <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No news articles found.
            </div>
        <?php endif; ?>
        
        <div class="mt-5">
            <h3>Pagination Features Implemented:</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-home text-primary me-2"></i>Index Page</h5>
                    <ul>
                        <li>15 posts per page</li>
                        <li>Full pagination controls</li>
                        <li>Page navigation dropdown</li>
                        <li>Posts counter display</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5><i class="fas fa-tags text-success me-2"></i>Other Pages</h5>
                    <ul>
                        <li>Category: 12 posts per page</li>
                        <li>Search: 10 posts per page</li>
                        <li>Tags: 20 posts per page</li>
                        <li>Editions: 12 posts per page</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="index.php" class="btn btn-primary me-2">
                    <i class="fas fa-home me-1"></i>Go to Homepage
                </a>
                <a href="category.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-tags me-1"></i>Browse Categories
                </a>
                <a href="search.php?q=test" class="btn btn-outline-success">
                    <i class="fas fa-search me-1"></i>Test Search
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
