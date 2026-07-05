<?php
require_once 'config/database.php';
$page_title = 'News Editions';

// Get filter parameters
$edition_type = isset($_GET['type']) ? clean_input($_GET['type']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Check if news_editions table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_editions'");
if (mysqli_num_rows($table_check) === 0) {
    // Table doesn't exist, redirect to installation
    header('Location: install_now.php');
    exit();
}

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($edition_type)) {
    $where_conditions[] = "ne.edition_type = ?";
    $params[] = $edition_type;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(n.title LIKE ? OR ne.edition_name LIKE ? OR ne.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'sss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get editions with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$query = "SELECT ne.*, n.title as news_title, n.slug as news_slug, n.image as news_image,
          n.excerpt as news_excerpt, c.name as category_name, c.slug as category_slug,
          ec.name as edition_category_name, ec.color as edition_color, ec.icon as edition_icon,
          u.name as author_name
          FROM news_editions ne
          LEFT JOIN news n ON ne.news_id = n.id
          LEFT JOIN categories c ON n.category_id = c.id
          LEFT JOIN edition_categories ec ON ne.edition_type = ec.slug
          LEFT JOIN users u ON n.author_id = u.id
          $where_clause
          ORDER BY ne.priority DESC, ne.published_at DESC 
          LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM news_editions ne
                LEFT JOIN news n ON ne.news_id = n.id
                $where_clause";

if (!empty($params)) {
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
} else {
    $count_result = mysqli_query($conn, $count_query);
}

$total_editions = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_editions / $per_page);

// Get edition categories for filter
$edition_categories = mysqli_query($conn, "SELECT * FROM edition_categories WHERE status = 'active' ORDER BY name ASC");
?>

<?php include 'includes/header.php'; ?>

<!-- Page Header -->
<section class="page-header bg-danger text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 fw-bold">
                    <i class="fas fa-layer-group me-3"></i>News Editions
                </h1>
                <p class="lead">Explore different editions and special coverage of our news articles</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="edition-stats">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h3 class="fw-bold"><?php echo number_format($total_editions); ?></h3>
                                <small>Total Editions</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h3 class="fw-bold"><?php echo mysqli_num_rows($result); ?></h3>
                                <small>Active Now</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filters Section -->
<section class="filters-section py-4 bg-light">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="type" class="form-label">Edition Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <?php while ($category = mysqli_fetch_assoc($edition_categories)): ?>
                                <option value="<?php echo $category['slug']; ?>" <?php echo $edition_type === $category['slug'] ? 'selected' : ''; ?>>
                                    <i class="fas <?php echo $category['icon']; ?> me-2"></i>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search Editions</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by title, edition name, or content...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="container py-5">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row g-4">
            <?php while ($edition = mysqli_fetch_assoc($result)): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow edition-card h-100">
                        <div class="position-relative image-container">
                            <?php if ($edition['news_image']): ?>
                                <img src="<?php echo htmlspecialchars($edition['news_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($edition['news_title']); ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="edition-badge-top" style="background-color: <?php echo $edition['edition_color']; ?>; color: white; position: absolute; top: 10px; right: 10px; padding: 8px 12px; border-radius: 20px; font-size: 0.8em; font-weight: 600;">
                                <i class="fas <?php echo $edition['edition_icon']; ?> me-1"></i>
                                <?php echo htmlspecialchars($edition['edition_category_name']); ?>
                            </div>
                            <?php if ($edition['priority'] > 0): ?>
                                <div class="priority-badge" style="position: absolute; top: 10px; left: 10px;">
                                    <span class="badge bg-warning">
                                        <i class="fas fa-star me-1"></i>Priority <?php echo $edition['priority']; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <span class="badge bg-info"><?php echo htmlspecialchars($edition['category_name']); ?></span>
                            </div>
                            <h5 class="card-title edition-title">
                                <?php echo htmlspecialchars($edition['edition_name']); ?>
                            </h5>
                            <p class="card-text news-title">
                                <a href="news.php?slug=<?php echo $edition['news_slug']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($edition['news_title']); ?>
                                </a>
                            </p>
                            <?php if (!empty($edition['content'])): ?>
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo htmlspecialchars(substr(strip_tags($edition['content']), 0, 120)) . '...'; ?>
                                </p>
                            <?php endif; ?>
                            <div class="edition-meta mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($edition['author_name']); ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i> <?php echo format_date($edition['published_at']); ?>
                                    </small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="news.php?slug=<?php echo $edition['news_slug']; ?>" class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-book-open me-2"></i>Read Full Article
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Editions pagination" class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&type=<?php echo $edition_type; ?>&search=<?php echo urlencode($search); ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo $edition_type; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&type=<?php echo $edition_type; ?>&search=<?php echo urlencode($search); ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-layer-group fa-4x text-muted mb-4"></i>
            <h3>No editions found</h3>
            <p class="text-muted">No news editions match your criteria.</p>
            <a href="index.php" class="btn btn-danger">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Edition Categories Section -->
<section class="edition-categories py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-4">Browse by Edition Type</h3>
        <div class="row">
            <?php 
            mysqli_data_seek($edition_categories, 0);
            while ($category = mysqli_fetch_assoc($edition_categories)): 
                // Count editions for this category
                $count_query = "SELECT COUNT(*) as count FROM news_editions WHERE edition_type = ? AND status = 'published'";
                $count_stmt = mysqli_prepare($conn, $count_query);
                mysqli_stmt_bind_param($count_stmt, 's', $category['slug']);
                mysqli_stmt_execute($count_stmt);
                $count_result = mysqli_stmt_get_result($count_stmt);
                $count = mysqli_fetch_assoc($count_result)['count'];
            ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card border-0 shadow-sm category-card h-100">
                        <div class="card-body text-center">
                            <div class="category-icon mb-3">
                                <i class="fas <?php echo $category['icon']; ?> fa-3x" style="color: <?php echo $category['color']; ?>;"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="text-muted small"><?php echo htmlspecialchars($category['description']); ?></p>
                            <div class="category-stats">
                                <span class="badge bg-secondary"><?php echo $count; ?> editions</span>
                            </div>
                            <a href="?type=<?php echo $category['slug']; ?>" class="btn btn-outline-danger btn-sm mt-3">
                                View Editions
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
.edition-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.edition-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.edition-badge-top {
    backdrop-filter: blur(10px);
    background-color: rgba(0,0,0,0.7);
}

.priority-badge {
    backdrop-filter: blur(10px);
}

.category-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.category-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.stat-item {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
}

.breaking-editions-slider {
    overflow: hidden;
}

.breaking-edition-item {
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

<script>
// Auto-rotate breaking news editions
document.addEventListener('DOMContentLoaded', function() {
    const breakingItems = document.querySelectorAll('.breaking-edition-item');
    if (breakingItems.length > 1) {
        let currentIndex = 0;
        
        setInterval(() => {
            breakingItems[currentIndex].style.display = 'none';
            currentIndex = (currentIndex + 1) % breakingItems.length;
            breakingItems[currentIndex].style.display = 'block';
        }, 5000);
        
        // Hide all except the first one
        for (let i = 1; i < breakingItems.length; i++) {
            breakingItems[i].style.display = 'none';
        }
    }
});
</script>
