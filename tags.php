<?php
require_once 'config/database.php';
$page_title = 'Tags';

// Check if tags table exists
$tags_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tags'");
if (mysqli_num_rows($tags_table_check) === 0) {
    redirect('install_tags_simple.php');
}

// Get tag filter
$tag_filter = isset($_GET['tag']) ? clean_input($_GET['tag']) : '';

// Build query
$where_clause = '';
$params = [];
$types = '';

if (!empty($tag_filter)) {
    $where_clause = "WHERE t.slug = ?";
    $params[] = $tag_filter;
    $types .= 's';
}

// Get tags with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

if (!empty($tag_filter)) {
    // Get news for specific tag
    $query = "SELECT n.*, c.name as category_name, u.name as author_name,
              t.name as tag_name, t.color as tag_color, t.slug as tag_slug
              FROM news n
              LEFT JOIN categories c ON n.category_id = c.id
              LEFT JOIN users u ON n.author_id = u.id
              LEFT JOIN news_tags nt ON n.id = nt.news_id
              LEFT JOIN tags t ON nt.tag_id = t.id
              $where_clause
              AND n.status = 'published'
              ORDER BY n.published_at DESC 
              LIMIT $per_page OFFSET $offset";
    
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $query);
    }
    
    // Get tag info
    $tag_info_query = "SELECT * FROM tags WHERE slug = ? LIMIT 1";
    $tag_stmt = mysqli_prepare($conn, $tag_info_query);
    mysqli_stmt_bind_param($tag_stmt, 's', $tag_filter);
    mysqli_stmt_execute($tag_stmt);
    $tag_info = mysqli_fetch_assoc(mysqli_stmt_get_result($tag_stmt));
    
} else {
    // Get all tags (excluding CNN and ARY tags)
    $query = "SELECT t.*, COUNT(nt.news_id) as usage_count 
              FROM tags t 
              LEFT JOIN news_tags nt ON t.id = nt.tag_id 
              LEFT JOIN news n ON nt.news_id = n.id AND n.status = 'published'
              WHERE t.status = 'active'
              AND (t.name NOT LIKE '%CNN%' AND t.slug NOT LIKE '%cnn%')
              AND (t.name NOT LIKE '%ARY%' AND t.slug NOT LIKE '%ary%')
              GROUP BY t.id 
              ORDER BY usage_count DESC, t.name ASC 
              LIMIT $per_page OFFSET $offset";
    $result = mysqli_query($conn, $query);
}

// Get total count for pagination
if (!empty($tag_filter)) {
    $count_query = "SELECT COUNT(*) as total FROM news n
                   LEFT JOIN news_tags nt ON n.id = nt.news_id
                   LEFT JOIN tags t ON nt.tag_id = t.id
                   $where_clause
                   AND n.status = 'published'";
    
    if (!empty($params)) {
        $count_stmt = mysqli_prepare($conn, $count_query);
        mysqli_stmt_bind_param($count_stmt, $types, ...$params);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
    } else {
        $count_result = mysqli_query($conn, $count_query);
    }
} else {
    $count_query = "SELECT COUNT(*) as total FROM tags WHERE status = 'active' AND (name NOT LIKE '%CNN%' AND slug NOT LIKE '%cnn%') AND (name NOT LIKE '%ARY%' AND slug NOT LIKE '%ary%')";
    $count_result = mysqli_query($conn, $count_query);
}

$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $per_page);

// Get trending tags (excluding CNN and ARY tags)
$trending_query = "SELECT t.*, COUNT(nt.news_id) as recent_mentions 
                 FROM tags t 
                 LEFT JOIN news_tags nt ON t.id = nt.tag_id 
                 LEFT JOIN news n ON nt.news_id = n.id 
                 WHERE t.status = 'active' 
                   AND n.status = 'published' 
                   AND (t.name NOT LIKE '%CNN%' AND t.slug NOT LIKE '%cnn%')
                   AND (t.name NOT LIKE '%ARY%' AND t.slug NOT LIKE '%ary%')
                   AND n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 GROUP BY t.id 
                 HAVING recent_mentions > 0
                 ORDER BY recent_mentions DESC, t.name ASC 
                 LIMIT 15";
$trending_result = mysqli_query($conn, $trending_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .tag-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        .tag-item {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .tag-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            border-color: rgba(255,255,255,0.3);
        }
        .tag-item .count {
            background: rgba(255,255,255,0.2);
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.8em;
            margin-left: 8px;
        }
        .trending-badge {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
            margin-left: 8px;
        }
        .tag-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <?php if (!empty($tag_filter)): ?>
            <!-- Tag Header -->
            <div class="tag-header text-center mb-4">
                <span class="badge fs-3" style="background-color: <?php echo $tag_info['color']; ?>; color: white; padding: 15px 25px; border-radius: 30px;">
                    <i class="fas fa-tag me-2"></i>
                    <?php echo htmlspecialchars($tag_info['name'] ?? ''); ?>
                    <span class="trending-badge">
                        <i class="fas fa-fire me-1"></i>Trending
                    </span>
                </span>
                <div class="mt-3">
                    <h4><?php echo htmlspecialchars($tag_info['description'] ?? ''); ?></h4>
                    <p class="text-muted">
                        <i class="fas fa-newspaper me-2"></i>
                        <?php echo $total_items; ?> articles found
                    </p>
                </div>
            </div>

            <!-- News Articles for Tag -->
            <div class="row">
                <?php while ($news = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow h-100">
                            <?php if ($news['image']): ?>
                                <img src="<?php echo htmlspecialchars($news['image'] ?? ''); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($news['title'] ?? ''); ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="mb-2">
                                    <span class="badge bg-info"><?php echo htmlspecialchars($news['category_name'] ?? ''); ?></span>
                                    <span class="badge ms-1" style="background-color: <?php echo $news['tag_color']; ?>; color: white;">
                                        <?php echo htmlspecialchars($news['tag_name'] ?? ''); ?>
                                    </span>
                                </div>
                                <h5 class="card-title">
                                    <a href="news.php?slug=<?php echo $news['slug']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($news['title'] ?? ''); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars(substr(strip_tags($news['excerpt'] ?? ''), 0, 120)) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($news['author_name'] ?? ''); ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i> <?php echo format_date($news['published_at']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Tag pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?tag=<?php echo urlencode($tag_filter); ?>&page=<?php echo $page - 1; ?>">
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
                                <a class="page-link" href="?tag=<?php echo urlencode($tag_filter); ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?tag=<?php echo urlencode($tag_filter); ?>&page=<?php echo $page + 1; ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <!-- All Tags -->
            <div class="text-center mb-4">
                <h2><i class="fas fa-tags me-2"></i>Browse Tags</h2>
                <p class="text-muted">Discover news organized by popular tags</p>
            </div>

            <!-- Trending Tags -->
            <?php if (mysqli_num_rows($trending_result) > 0): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-fire me-2"></i>Trending Tags (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="tag-cloud">
                        <?php while ($tag = mysqli_fetch_assoc($trending_result)): ?>
                            <a href="?tag=<?php echo urlencode($tag['slug']); ?>" class="tag-item" 
                               style="background-color: <?php echo $tag['color']; ?>;">
                                <?php echo htmlspecialchars($tag['name'] ?? ''); ?>
                                <span class="count"><?php echo $tag['recent_mentions']; ?></span>
                                <span class="trending-badge">
                                    <i class="fas fa-arrow-up me-1"></i>Trending
                                </span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- All Tags -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-tags me-2"></i>All Tags</h5>
                </div>
                <div class="card-body">
                    <div class="tag-cloud">
                        <?php mysqli_data_seek($result, 0); ?>
                        <?php while ($tag = mysqli_fetch_assoc($result)): ?>
                            <a href="?tag=<?php echo urlencode($tag['slug']); ?>" class="tag-item" 
                               style="background-color: <?php echo $tag['color']; ?>;">
                                <?php echo htmlspecialchars($tag['name'] ?? ''); ?>
                                <span class="count"><?php echo $tag['usage_count']; ?></span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Back to All Tags -->
        <?php if (!empty($tag_filter)): ?>
        <div class="text-center mt-4">
            <a href="tags.php" class="btn btn-outline-danger">
                <i class="fas fa-arrow-left me-2"></i>Back to All Tags
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
