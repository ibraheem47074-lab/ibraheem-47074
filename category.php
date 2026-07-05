<?php
require_once 'config/database.php';

// Get category slug from URL
$slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: index.php');
    exit();
}

// Get category information
$query = "SELECT * FROM categories WHERE slug = ? AND status = 'active'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header('Location: index.php');
    exit();
}

$category = mysqli_fetch_assoc($result);

// Additional check to ensure category exists
if (!$category) {
    header('Location: index.php');
    exit();
}

// Ensure category is never null for the rest of the script
$category = $category ?: ['name' => 'Category', 'description' => '', 'id' => 0];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get news in this category with all fields like index page
$news_query = "SELECT n.*, c.name as category_name, c.color as category_color, c.icon as category_icon,
               u.name as author_name,
               (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
               (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count,
               CASE 
                   WHEN n.published_at IS NOT NULL AND n.published_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'new'
                   WHEN n.published_at IS NOT NULL AND n.published_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'recent'
                   ELSE ''
               END as time_status,
               CASE
                   WHEN n.source_url LIKE '%bbc.co.uk%' OR n.source_url LIKE '%bbc.com%' THEN 'BBC News'
                   WHEN n.source_url LIKE '%cnn.com%' THEN 'CNN'
                   WHEN n.source_url LIKE '%arynews.tv%' OR n.source_url LIKE '%arydigital.tv%' THEN 'ARY News'
                   WHEN n.source_url LIKE '%reuters.com%' THEN 'Reuters'
                   WHEN n.source_url LIKE '%aljazeera.com%' THEN 'Al Jazeera'
                   WHEN n.source_url LIKE '%foxnews.com%' THEN 'Fox News'
                   WHEN n.source_url LIKE '%apnews.com%' OR n.source_url LIKE '%ap.org%' THEN 'Associated Press'
                   WHEN n.source_url LIKE '%bloomberg.com%' THEN 'Bloomberg'
                   WHEN n.source_url LIKE '%theguardian.com%' THEN 'The Guardian'
                   WHEN n.source_url LIKE '%washingtonpost.com%' THEN 'Washington Post'
                   WHEN n.source_url LIKE '%nytimes.com%' THEN 'New York Times'
                   WHEN n.source_url LIKE '%nbcnews.com%' THEN 'NBC News'
                   WHEN n.source_url LIKE '%cbsnews.com%' THEN 'CBS News'
                   WHEN n.source_url LIKE '%abcnews.go.com%' THEN 'ABC News'
                   WHEN n.source_url LIKE '%cnbc.com%' THEN 'CNBC'
                   WHEN n.source_url LIKE '%wsj.com%' THEN 'Wall Street Journal'
                   WHEN n.source_url LIKE '%usatoday.com%' THEN 'USA Today'
                   WHEN n.source_url LIKE '%npr.org%' THEN 'NPR'
                   WHEN n.source_url LIKE '%pbs.org%' THEN 'PBS'
                   WHEN n.source_url LIKE '%news.sky.com%' THEN 'Sky News'
                   WHEN n.source_url LIKE '%euronews.com%' THEN 'EuroNews'
                   WHEN n.source_url LIKE '%dw.com%' THEN 'Deutsche Welle'
                   WHEN n.source_url LIKE '%france24.com%' THEN 'France 24'
                   WHEN n.source_url LIKE '%rt.com%' THEN 'RT'
                   WHEN n.source_url LIKE '%cgtn.com%' THEN 'CGTN'
                   WHEN n.source_url LIKE '%ndtv.com%' THEN 'NDTV'
                   WHEN n.source_url LIKE '%timesofindia.indiatimes.com%' THEN 'Times of India'
                   WHEN n.source_url LIKE '%hindustantimes.com%' THEN 'Hindustan Times'
                   WHEN n.source_url LIKE '%dawn.com%' THEN 'Dawn'
                   WHEN n.source_url LIKE '%geo.tv%' THEN 'Geo News'
                   WHEN n.source_url LIKE '%tribune.com.pk%' THEN 'Express Tribune'
                   ELSE 'PK Live News'
               END as source_name,
               COALESCE(n.published_at, n.created_at) as real_post_time
               FROM news n 
               LEFT JOIN categories c ON n.category_id = c.id 
               LEFT JOIN users u ON n.author_id = u.id 
               WHERE n.category_id = ? AND n.status = 'published' AND (n.published_at <= NOW() OR n.published_at IS NULL) 
               ORDER BY real_post_time DESC 
               LIMIT ? OFFSET ?";

$category_id = $category['id'] ?? 0;
$news_stmt = mysqli_prepare($conn, $news_query);
mysqli_stmt_bind_param($news_stmt, 'iii', $category_id, $per_page, $offset);
mysqli_stmt_execute($news_stmt);
$news_result = mysqli_stmt_get_result($news_stmt);

// Get total news count for pagination
$count_query = "SELECT COUNT(*) as total FROM news WHERE category_id = ? AND status = 'published'";
$count_stmt = mysqli_prepare($conn, $count_query);
mysqli_stmt_bind_param($count_stmt, 'i', $category_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Get other categories for sidebar (excluding BBC and CNN categories)
$other_categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' AND id != " . ($category['id'] ?? 0) . " AND slug NOT IN ('bbc-world', 'cnn-international') ORDER BY name ASC");

$page_title = htmlspecialchars($category['name'] ?? 'Category') . ' News';

// Final category validation before including header
if (!$category || !isset($category['id']) || $category['id'] === 0) {
    header('Location: index.php');
    exit();
}

include 'includes/header.php';
?>

<!-- Recent Articles Slider -->
<section class="category-slideshow py-4 mb-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4 text-white"><i class="fas fa-clock me-2"></i>Recent <?php echo htmlspecialchars($category['name'] ?? 'Category'); ?> Articles</h3>
                <div class="slideshow-container position-relative">
                    <div id="categoryArticleSlideshow" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                        <div class="carousel-inner">
                            <?php
                            // Get recent articles from this category with images/videos
                            $recent_query = "SELECT n.*, c.name as category_name 
                                           FROM news n 
                                           LEFT JOIN categories c ON n.category_id = c.id 
                                           WHERE n.category_id = ? AND n.status = 'published' 
                                           AND (n.image IS NOT NULL AND n.image != '' 
                                                OR n.video_url IS NOT NULL AND n.video_url != '' 
                                                OR n.video_path IS NOT NULL AND n.video_path != '')
                                           AND n.published_at <= NOW()
                                           ORDER BY n.published_at DESC 
                                           LIMIT 8";
                            
                            $recent_stmt = mysqli_prepare($conn, $recent_query);
                            mysqli_stmt_bind_param($recent_stmt, 'i', $category_id);
                            mysqli_stmt_execute($recent_stmt);
                            $recent_result = mysqli_stmt_get_result($recent_stmt);
                            
                            if ($recent_result && mysqli_num_rows($recent_result) > 0) {
                                $index = 0;
                                while ($article = mysqli_fetch_assoc($recent_result)) {
                                    $active_class = $index === 0 ? 'active' : '';
                                    $media_content = '';
                                    
                                    // Determine media type and generate content
                                    if (!empty($article['video_url'])) {
                                        // Video content
                                        $video_id = '';
                                        $thumbnail_url = '';
                                        
                                        if (strpos($article['video_url'], 'youtube.com') !== false || strpos($article['video_url'], 'youtu.be') !== false) {
                                            if (strpos($article['video_url'], 'youtube.com/watch?v=') !== false) {
                                                $video_id = substr($article['video_url'], strpos($article['video_url'], 'v=') + 2);
                                            } elseif (strpos($article['video_url'], 'youtu.be/') !== false) {
                                                $video_id = substr($article['video_url'], strpos($article['video_url'], 'youtu.be/') + 9);
                                            }
                                            $video_id = explode('?', $video_id)[0];
                                            $thumbnail_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
                                        } else {
                                            $thumbnail_url = $article['image'] ?? 'https://via.placeholder.com/800x450/000000/ffffff?text=Video';
                                        }
                                        
                                        $media_content = '
                                        <div class="position-relative video-thumbnail" data-video-url="' . htmlspecialchars($article['video_url']) . '" data-video-title="' . htmlspecialchars($article['title']) . '">
                                            <img src="' . htmlspecialchars($thumbnail_url) . '" class="d-block w-100" alt="' . htmlspecialchars($article['title']) . '" style="height: 400px; object-fit: cover;">
                                            <div class="video-play-button">
                                                <i class="fas fa-play"></i>
                                            </div>
                                            <div class="slideshow-overlay">
                                                <h5>' . htmlspecialchars($article['title']) . '</h5>
                                                <p class="mb-0">' . date('M j, Y', strtotime($article['published_at'])) . '</p>
                                            </div>
                                        </div>';
                                    } elseif (!empty($article['image'])) {
                                        // Image content
                                        $media_content = '
                                        <div class="position-relative image-thumbnail">
                                            <img src="' . htmlspecialchars($article['image']) . '" class="d-block w-100" alt="' . htmlspecialchars($article['title']) . '" style="height: 400px; object-fit: cover;">
                                            <div class="slideshow-overlay">
                                                <h5>' . htmlspecialchars($article['title']) . '</h5>
                                                <p class="mb-0">' . date('M j, Y', strtotime($article['published_at'])) . '</p>
                                            </div>
                                        </div>';
                                    } else {
                                        // Skip articles without media
                                        $index++;
                                        continue;
                                    }
                                    
                                    echo '
                                    <div class="carousel-item ' . $active_class . '">
                                        ' . $media_content . '
                                    </div>';
                                    $index++;
                                }
                            } else {
                                // No articles with media found
                                echo '
                                <div class="carousel-item active">
                                    <div class="text-center py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
                                        <h3><i class="fas fa-newspaper me-2"></i>No Recent Articles</h3>
                                        <p>Articles with images or videos will appear here</p>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                        
                        <!-- Carousel Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#categoryArticleSlideshow" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true">&lsaquo;</span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#categoryArticleSlideshow" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true">&rsaquo;</span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Content -->
<div class="container py-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Category News Grid -->
            <section class="category-news">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3><i class="fas fa-newspaper me-2"></i>Latest <?php echo htmlspecialchars($category['name'] ?? 'Category'); ?> News</h3>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" onchange="sortNews(this.value)">
                            <option value="latest">Latest First</option>
                            <option value="popular">Most Popular</option>
                            <option value="oldest">Oldest First</option>
                        </select>
                    </div>
                </div>

                <?php if (mysqli_num_rows($news_result) > 0): ?>
                    <div class="row g-4" id="newsGrid">
                        <?php while ($news = mysqli_fetch_assoc($news_result)): ?>
                            <div class="col-md-6">
                                <div class="card border-0 shadow news-card h-100">
                                    <div class="position-relative image-container">
                                        <?php if ($news['video_url']): ?>
                                            <div class="position-relative video-thumbnail" data-video-url="<?php echo htmlspecialchars($news['video_url']); ?>" data-video-title="<?php echo htmlspecialchars($news['title']); ?>">
                                                <?php 
                                                // Generate video thumbnail
                                                $videoId = '';
                                                $thumbnailUrl = '';
                                                if (strpos($news['video_url'], 'youtube.com') !== false || strpos($news['video_url'], 'youtu.be') !== false) {
                                                    if (strpos($news['video_url'], 'youtube.com/watch?v=') !== false) {
                                                        $videoId = substr($news['video_url'], strpos($news['video_url'], 'v=') + 2);
                                                    } elseif (strpos($news['video_url'], 'youtu.be/') !== false) {
                                                        $videoId = substr($news['video_url'], strpos($news['video_url'], 'youtu.be/') + 9);
                                                    }
                                                    $videoId = explode('?', $videoId)[0];
                                                    $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
                                                } else {
                                                    $thumbnailUrl = $news['image'] ?? 'https://via.placeholder.com/400x225/000000/ffffff?text=Video';
                                                }
                                                ?>
                                                <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($news['title']); ?>" style="height: 200px; object-fit: cover;">
                                                
                                                <!-- Video Play Button -->
                                                <div class="video-play-button">
                                                    <i class="fas fa-play"></i>
                                                </div>
                                                
                                                <!-- Video Badge -->
                                                <div class="video-quality-badge">VIDEO</div>
                                            </div>
                                        <?php elseif ($news['image']): ?>
                                            <img src="<?php echo htmlspecialchars($news['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($news['title']); ?>" style="height: 200px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($news['is_breaking']): ?>
                                            <span class="breaking-badge">BREAKING</span>
                                        <?php endif; ?>
                                        
                                        <!-- Views Badge on Top -->
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-dark bg-opacity-75 text-white">
                                                <i class="fas fa-eye me-1"></i> <?php echo number_format($news['views']); ?>
                                            </span>
                                        </div>
                                        
                                        <!-- News Status Badges -->
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <?php if ($news['time_status'] === 'new'): ?>
                                                <span class="badge bg-secondary animate-pulse">
                                                    <i class="fas fa-sparkles me-1"></i>NEW
                                                </span>
                                            <?php elseif ($news['time_status'] === 'recent'): ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-clock me-1"></i>Recent
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <div class="mb-2">
                                            <span class="badge bg-danger"><?php echo htmlspecialchars($category['name'] ?? 'Category'); ?></span>
                                            <?php if (!empty($news['sentiment_label'])): ?>
                                                <?php 
                                                $sentiment_colors = ['positive' => 'success', 'negative' => 'danger', 'neutral' => 'secondary'];
                                                $sentiment_icons = ['positive' => '😊', 'negative' => '😔', 'neutral' => '😐'];
                                                $color = $sentiment_colors[$news['sentiment_label']] ?? 'secondary';
                                                $icon = $sentiment_icons[$news['sentiment_label']] ?? '😐';
                                                ?>
                                                <span class="badge bg-<?php echo $color; ?> ms-1" title="Sentiment: <?php echo $news['sentiment_label']; ?> (Score: <?php echo $news['sentiment_score']; ?>)">
                                                    <?php echo $icon; ?> <?php echo ucfirst($news['sentiment_label']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="card-title">
                                            <a href="news.php?slug=<?php echo $news['slug']; ?>" class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($news['title']); ?>
                                            </a>
                                        </h5>
                                        <p class="card-text text-muted flex-grow-1">
                                            <?php echo htmlspecialchars(substr($news['excerpt'] ?? '', 0, 120)) . '...'; ?>
                                        </p>
                                        <div class="news-meta mt-auto">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar ms-3 me-1"></i> <?php echo format_date($news['published_at']); ?>
                                                <i class="fas fa-eye ms-3 me-1"></i> <?php echo number_format($news['views']); ?>
                                            </small>
                                        </div>
                                        <div class="news-actions mt-3">
                                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-xs btn-outline-danger like-btn" onclick="toggleLike(<?php echo $news['id']; ?>, this)" title="Like">
                                                        <i class="fas fa-heart"></i> <span class="likes-count"><?php echo $news['likes_count']; ?></span>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-primary" onclick="shareOnFacebook('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo htmlspecialchars($news['title']); ?>')" title="Share on Facebook">
                                                        <i class="fab fa-facebook-f"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-info" onclick="shareOnTwitter('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo htmlspecialchars($news['title']); ?>')" title="Share on Twitter">
                                                        <i class="fab fa-twitter"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-success" onclick="shareOnWhatsApp('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>', '<?php echo htmlspecialchars($news['title']); ?>')" title="Share on WhatsApp">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-secondary" onclick="copyToClipboard('<?php echo SITE_URL; ?>news.php?slug=<?php echo $news['slug']; ?>')" title="Copy Link">
                                                        <i class="fas fa-link"></i>
                                                    </button>
                                                </div>
                                                <button class="btn btn-xs btn-danger" onclick="showCommentsModal(<?php echo $news['id']; ?>)" title="View Comments">
                                                    <i class="fas fa-comments me-1"></i><?php echo $news['comment_count']; ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Category news pagination" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?slug=<?php echo $slug; ?>&page=<?php echo $page - 1; ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?slug=' . $slug . '&page=1">1</a></li>';
                                    if ($start_page > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?slug=<?php echo $slug; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?slug=' . $slug . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                }
                                ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?slug=<?php echo $slug; ?>&page=<?php echo $page + 1; ?>">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <h4>No news found in this category</h4>
                        <p class="text-muted">Check back later for latest updates in <?php echo htmlspecialchars($category['name'] ?? 'this category'); ?>.</p>
                        <a href="index.php" class="btn btn-danger mt-3">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Other Categories -->
            <div class="sidebar-widget">
                <h3><i class="fas fa-tags me-2"></i>Other Categories</h3>
                <div class="category-list">
                    <?php while ($other_cat = mysqli_fetch_assoc($other_categories)): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="category.php?slug=<?php echo $other_cat['slug']; ?>" class="text-decoration-none text-dark">
                                <i class="fas fa-chevron-right me-2 text-danger"></i>
                                <?php echo htmlspecialchars($other_cat['name']); ?>
                            </a>
                            <span class="badge bg-secondary">
                                <?php 
                                $count_query = "SELECT COUNT(*) as count FROM news WHERE category_id = " . $other_cat['id'] . " AND status = 'published'";
                                $count_result = mysqli_query($conn, $count_query);
                                $count = mysqli_fetch_assoc($count_result)['count'];
                                echo $count;
                                ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Popular in this Category -->
            <div class="sidebar-widget">
                <h3><i class="fas fa-fire me-2"></i>Popular in <?php echo htmlspecialchars($category['name'] ?? 'Category'); ?></h3>
                <?php
                $popular_query = "SELECT * FROM news 
                                WHERE category_id = ? AND status = 'published' 
                                ORDER BY views DESC LIMIT 5";
                $popular_stmt = mysqli_prepare($conn, $popular_query);
                mysqli_stmt_bind_param($popular_stmt, 'i', $category_id);
                mysqli_stmt_execute($popular_stmt);
                $popular_result = mysqli_stmt_get_result($popular_stmt);
                
                if (mysqli_num_rows($popular_result) > 0):
                ?>
                    <?php $popular_num = 1; ?>
                    <?php while ($popular = mysqli_fetch_assoc($popular_result)): ?>
                        <div class="trending-item">
                            <div class="trending-number"><?php echo $popular_num++; ?></div>
                            <div class="trending-content">
                                <h6>
                                    <a href="news.php?slug=<?php echo $popular['slug']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars(substr($popular['title'], 0, 60)) . '...'; ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <?php echo number_format($popular['views']); ?> views
                                </small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No popular articles yet</p>
                <?php endif; ?>
            </div>

            <!-- Category Statistics -->
            <div class="sidebar-widget">
                <h3><i class="fas fa-chart-bar me-2"></i>Category Stats</h3>
                <div class="category-stats">
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Articles</span>
                            <strong><?php echo number_format($total_records); ?></strong>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>This Week</span>
                            <strong>
                                <?php
                                $week_query = "SELECT COUNT(*) as count FROM news 
                                             WHERE category_id = ? AND status = 'published' 
                                             AND published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                                $week_stmt = mysqli_prepare($conn, $week_query);
                                mysqli_stmt_bind_param($week_stmt, 'i', $category_id);
                                mysqli_stmt_execute($week_stmt);
                                $week_result = mysqli_stmt_get_result($week_stmt);
                                $week_count = mysqli_fetch_assoc($week_result)['count'];
                                echo $week_count;
                                ?>
                            </strong>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="d-flex justify-content-between">
                            <span>Total Views</span>
                            <strong>
                                <?php
                                $views_query = "SELECT SUM(views) as total_views FROM news 
                                              WHERE category_id = ? AND status = 'published'";
                                $views_stmt = mysqli_prepare($conn, $views_query);
                                mysqli_stmt_bind_param($views_stmt, 'i', $category_id);
                                mysqli_stmt_execute($views_stmt);
                                $views_result = mysqli_stmt_get_result($views_stmt);
                                $total_views = mysqli_fetch_assoc($views_result)['total_views'];
                                echo number_format($total_views ?: 0);
                                ?>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related News -->
            <div class="sidebar-widget">
                <h3><i class="fas fa-newspaper me-2"></i>Related News</h3>
                <?php
                // Get related news from same category (excluding current page news)
                $related_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                                 (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                                 (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
                                 FROM news n 
                                 LEFT JOIN categories c ON n.category_id = c.id 
                                 LEFT JOIN users u ON n.author_id = u.id 
                                 WHERE n.category_id = ? AND n.status = 'published' 
                                 AND n.published_at <= NOW()
                                 ORDER BY n.published_at DESC 
                                 LIMIT 4";
                $related_stmt = mysqli_prepare($conn, $related_query);
                mysqli_stmt_bind_param($related_stmt, 'i', $category_id);
                mysqli_stmt_execute($related_stmt);
                $related_result = mysqli_stmt_get_result($related_stmt);
                
                if (mysqli_num_rows($related_result) > 0):
                ?>
                    <?php while ($related = mysqli_fetch_assoc($related_result)): ?>
                        <div class="mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <div class="image-container">
                                            <?php if ($related['video_url']): ?>
                                                <div class="position-relative video-thumbnail" data-video-url="<?php echo htmlspecialchars($related['video_url']); ?>" data-video-title="<?php echo htmlspecialchars($related['title']); ?>">
                                                    <?php 
                                                    // Generate video thumbnail
                                                    $videoId = '';
                                                    $thumbnailUrl = '';
                                                    if (strpos($related['video_url'], 'youtube.com') !== false || strpos($related['video_url'], 'youtu.be') !== false) {
                                                        if (strpos($related['video_url'], 'youtube.com/watch?v=') !== false) {
                                                            $videoId = substr($related['video_url'], strpos($related['video_url'], 'v=') + 2);
                                                        } elseif (strpos($related['video_url'], 'youtu.be/') !== false) {
                                                            $videoId = substr($related['video_url'], strpos($related['video_url'], 'youtu.be/') + 9);
                                                        }
                                                        $videoId = explode('?', $videoId)[0];
                                                        $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
                                                    } else {
                                                        $thumbnailUrl = $related['image'] ?? 'https://via.placeholder.com/400x225/000000/ffffff?text=Video';
                                                    }
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($related['title']); ?>" style="height: auto; object-fit: cover; cursor: pointer;">
                                                    
                                                    <!-- Video Play Button -->
                                                    <div class="video-play-button">
                                                        <i class="fas fa-play"></i>
                                                    </div>
                                                </div>
                                            <?php elseif ($related['image']): ?>
                                                <img src="<?php echo htmlspecialchars($related['image']); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($related['title']); ?>" style="height: auto; object-fit: cover; cursor: pointer;">
                                            <?php else: ?>
                                                <div class="img-fluid rounded-start bg-light d-flex align-items-center justify-content-center" style="height: 80px;">
                                                    <i class="fas fa-image fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body p-2">
                                            <h6 class="card-title">
                                                <a href="news.php?slug=<?php echo $related['slug']; ?>" class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars(substr($related['title'], 0, 50)) . (strlen($related['title']) > 50 ? '...' : ''); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('M j, Y \a\t g:i A', strtotime($related['published_at'])); ?>
                                            </small>
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-eye me-1"></i><?php echo number_format($related['views']); ?> views
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No related news found</p>
                <?php endif; ?>
            </div>

            <!-- Advertisement -->
            <?php
            require_once 'includes/ads_functions.php';
            displayAdWidget('sidebar');
            ?>
        </div>
    </div>
</div>

/* Related News Sidebar Widget Styles */
.sidebar-widget .card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.sidebar-widget .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.sidebar-widget .img-fluid {
    width: 100%;
    height: auto;
    object-fit: cover;
}

.sidebar-widget .video-thumbnail {
    position: relative;
    overflow: hidden;
}

.sidebar-widget .video-play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 30px;
    height: 30px;
    background: rgba(0,0,0,0.7);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.sidebar-widget .video-play-button:hover {
    background: rgba(0,0,0,0.9);
    transform: translate(-50%, -50%) scale(1.1);
}

.sidebar-widget .card-title {
    font-size: 0.9rem;
    font-weight: 600;
    line-height: 1.3;
    margin-bottom: 0.5rem;
}

.sidebar-widget .card-title a {
    color: #333;
    text-decoration: none;
}

.sidebar-widget .card-title a:hover {
    color: #dc3545;
}

/* List view styles */
.list-view {
    display: flex !important;
    flex-direction: column;
    gap: 1rem;
}

.list-view .col-md-6 {
    width: 100% !important;
    max-width: 100% !important;
    flex: 0 0 100% !important;
}

.list-view .news-card {
    display: flex;
    flex-direction: row;
    height: auto !important;
}

.list-view .news-card .image-container {
    width: 300px;
    height: 200px;
    flex-shrink: 0;
}

.list-view .news-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}

@media (max-width: 768px) {
    .list-view .news-card {
        flex-direction: column;
    }
    
    .list-view .news-card .image-container {
        width: 100%;
        height: 200px;
    }
}

<!-- Category Slideshow CSS -->
<style>
.category-slideshow {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.category-slideshow h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.category-slideshow .slideshow-container {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.category-slideshow .carousel-item {
    height: 250px;
    position: relative;
}

.category-slideshow .video-thumbnail, 
.category-slideshow .image-thumbnail {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.category-slideshow .video-play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50px;
    height: 50px;
    background: rgba(0,0,0,0.7);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.category-slideshow .video-play-button:hover {
    background: rgba(0,0,0,0.9);
    transform: translate(-50%, -50%) scale(1.1);
}

.category-slideshow .slideshow-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0) 100%);
    color: white;
    padding: 15px;
    text-align: left;
}

.category-slideshow .slideshow-overlay h5 {
    font-size: 1rem;
    font-weight: bold;
    margin-bottom: 3px;
    line-height: 1.2;
}

.category-slideshow .slideshow-overlay p {
    font-size: 0.8rem;
    margin: 0;
    opacity: 0.8;
}

.category-slideshow .carousel-control-prev,
.category-slideshow .carousel-control-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.3);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    transition: all 0.3s ease;
    z-index: 10;
}

.category-slideshow .carousel-control-prev:hover,
.category-slideshow .carousel-control-next:hover {
    background: rgba(255,255,255,0.5);
    width: 45px;
    height: 45px;
}

.category-slideshow .carousel-control-prev-icon,
.category-slideshow .carousel-control-next-icon {
    display: inline-block;
    width: 16px;
    height: 16px;
    background: no-repeat center center;
    background-size: 100% 100%;
}

/* Mobile/Android Optimizations */
@media (max-width: 768px) {
    .category-slideshow {
        padding: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .category-slideshow h3 {
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
    }
    
    .category-slideshow .carousel-item {
        height: 200px;
    }
    
    .category-slideshow .slideshow-overlay {
        padding: 10px;
    }
    
    .category-slideshow .slideshow-overlay h5 {
        font-size: 0.9rem;
        line-height: 1.1;
    }
    
    .category-slideshow .slideshow-overlay p {
        font-size: 0.7rem;
    }
    
    .category-slideshow .video-play-button {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }
    
    .category-slideshow .carousel-control-prev,
    .category-slideshow .carousel-control-next {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }
    
    .category-slideshow .carousel-control-prev:hover,
    .category-slideshow .carousel-control-next:hover {
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 480px) {
    .category-slideshow {
        padding: 0.5rem;
        border-radius: 10px;
    }
    
    .category-slideshow .slideshow-container {
        border-radius: 10px;
    }
    
    .category-slideshow h3 {
        font-size: 1.1rem;
    }
    
    .category-slideshow .carousel-item {
        height: 180px;
    }
    
    .category-slideshow .slideshow-overlay h5 {
        font-size: 0.85rem;
    }
    
    .category-slideshow .slideshow-overlay p {
        font-size: 0.65rem;
    }
    
    .category-slideshow .carousel-control-prev,
    .category-slideshow .carousel-control-next {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
}

/* Touch-friendly for mobile */
.category-slideshow .carousel {
    touch-action: pan-y;
}

.category-slideshow .carousel-control-prev,
.category-slideshow .carousel-control-next {
    touch-action: manipulation;
}

/* Prevent text selection on mobile */
.category-slideshow .slideshow-overlay {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
</style>

<?php include 'includes/footer.php'; ?>

<script>
// Sort news functionality
function sortNews(sortBy) {
    const newsGrid = document.getElementById('newsGrid');
    const currentUrl = new URL(window.location);
    
    currentUrl.searchParams.set('sort', sortBy);
    
    // Show loading state
    newsGrid.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-danger" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Sorting news...</p>
        </div>
    `;
    
    // Fetch sorted news
    fetch(`api/category-news.php?slug=${currentUrl.searchParams.get('slug')}&sort=${sortBy}`)
        .then(response => response.json())
        .then(data => {
            if (data.news) {
                updateNewsGrid(data.news);
            } else {
                newsGrid.innerHTML = '<div class="col-12 text-center py-5"><p>No news found</p></div>';
            }
        })
        .catch(error => {
            console.error('Error sorting news:', error);
            location.reload(); // Fallback to page reload
        });
}

function updateNewsGrid(news) {
    const newsGrid = document.getElementById('newsGrid');
    let newsHtml = '';
    
    news.forEach(item => {
        newsHtml += `
            <div class="col-md-6">
                <div class="card border-0 shadow news-card h-100">
                    <div class="position-relative">
                        ${item.image ? 
                            `<img src="${item.image}" class="card-img-top" alt="${item.title}" style="height: 200px; object-fit: cover;">` :
                            `<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-2x text-muted"></i>
                            </div>`
                        }
                        ${item.is_breaking ? '<span class="breaking-badge">BREAKING</span>' : ''}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-danger">${item.category_name}</span>
                            ${item.sentiment_label ? 
                                `<span class="badge bg-${item.sentiment_label === 'positive' ? 'success' : item.sentiment_label === 'negative' ? 'danger' : 'secondary'} ms-1" title="Sentiment: ${item.sentiment_label} (Score: ${item.sentiment_score})">
                                    ${item.sentiment_label === 'positive' ? '😊' : item.sentiment_label === 'negative' ? '😔' : '😐'} ${item.sentiment_label.charAt(0).toUpperCase() + item.sentiment_label.slice(1)}
                                </span>` : ''
                            }
                        </div>
                        <h5 class="card-title">
                            <a href="news.php?slug=${item.slug}" class="text-decoration-none text-dark">
                                ${item.title}
                            </a>
                        </h5>
                        <p class="card-text text-muted flex-grow-1">
                            ${item.excerpt.substring(0, 120)}...
                        </p>
                        <div class="news-meta mt-auto">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i> ${item.author_name}
                                <i class="fas fa-calendar ms-3 me-1"></i> ${formatDate(item.published_at)}
                                <i class="fas fa-eye ms-3 me-1"></i> ${item.views}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    newsGrid.innerHTML = newsHtml;
}

// Format date for JavaScript
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

// Auto-refresh category stats every 30 seconds
setInterval(() => {
    const statsWidget = document.querySelector('.category-stats');
    if (statsWidget) {
        // In a real application, you would fetch updated stats from your API
        // For demo purposes, we'll just show a subtle animation
        statsWidget.style.opacity = '0.5';
        setTimeout(() => {
            statsWidget.style.opacity = '1';
        }, 500);
    }
}, 30000);

// Initialize category slideshow
document.addEventListener("DOMContentLoaded", function() {
    const categoryCarousel = document.getElementById("categoryArticleSlideshow");
    if (categoryCarousel) {
        // Check if mobile device
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        const carouselOptions = {
            interval: isMobile ? 5000 : 4000, // Slower on mobile
            ride: "carousel",
            touch: true, // Enable touch swiping
            pause: "hover" // Pause on hover
        };
        
        // On mobile, add additional settings
        if (isMobile) {
            carouselOptions.wrap = true; // Allow wrapping
        }
        
        const carousel = new bootstrap.Carousel(categoryCarousel, carouselOptions);
        
        // Add touch event listeners for better mobile experience
        if (isMobile) {
            let touchStartX = 0;
            let touchEndX = 0;
            
            categoryCarousel.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, false);
            
            categoryCarousel.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, false);
            
            function handleSwipe() {
                const swipeThreshold = 50;
                const diff = touchStartX - touchEndX;
                
                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        carousel.next(); // Swipe left, go to next
                    } else {
                        carousel.prev(); // Swipe right, go to previous
                    }
                }
            }
        }
    }
});

// Comments Modal Functions
function showCommentsModal(newsId) {
    // Check if modal exists, if not create it
    if (!document.getElementById('commentsModal')) {
        createCommentsModal();
    }
    
    const modalElement = document.getElementById('commentsModal');
    const modalInstance = new bootstrap.Modal(modalElement);
    const loadingDiv = document.getElementById('commentsLoading');
    const contentDiv = document.getElementById('commentsContent');
    const viewFullBtn = document.getElementById('viewFullArticleBtn');
    
    // Show loading state
    loadingDiv.style.display = 'block';
    contentDiv.style.display = 'none';
    
    // Set the view full article button
    viewFullBtn.onclick = function() {
        window.location.href = `news.php?id=${newsId}`;
    };
    
    // Fetch comments
    fetch(`api/get-comments.php?news_id=${newsId}`)
        .then(response => response.json())
        .then(data => {
            loadingDiv.style.display = 'none';
            contentDiv.style.display = 'block';
            
            if (data.success) {
                displayComments(data.comments, data.news);
            } else {
                contentDiv.innerHTML = '<div class="alert alert-danger">Error loading comments. Please try again.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            loadingDiv.style.display = 'none';
            contentDiv.style.display = 'block';
            contentDiv.innerHTML = '<div class="alert alert-danger">Error loading comments. Please try again.</div>';
        });
    
    modalInstance.show();
}

function createCommentsModal() {
    const modalHtml = `
        <div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="commentsModalLabel">
                            <i class="fas fa-comments me-2"></i>Comments
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="commentsLoading" class="text-center py-4">
                            <div class="spinner-border text-danger" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading comments...</p>
                        </div>
                        <div id="commentsContent" style="display: none;">
                            <!-- Comments will be loaded here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-danger" id="viewFullArticleBtn">
                            <i class="fas fa-external-link-alt me-2"></i>View Full Article
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function displayComments(comments, news) {
    const contentDiv = document.getElementById('commentsContent');
    
    let html = `
        <div class="news-summary mb-4">
            <h6>${news.title}</h6>
            <small class="text-muted">${news.category_name} • ${formatDate(news.published_at)}</small>
        </div>
        <div class="comments-section">
    `;
    
    if (comments.length === 0) {
        html += '<p class="text-muted text-center py-3">No comments yet. Be the first to comment!</p>';
    } else {
        comments.forEach(comment => {
            html += `
                <div class="comment-item border-bottom pb-3 mb-3">
                    <div class="d-flex">
                        <div class="comment-avatar me-3">
                            <i class="fas fa-user-circle fa-2x text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="comment-author fw-bold">
                                        ${comment.name}
                                    </div>
                                    <div class="comment-date text-muted small">${formatDate(comment.created_at)}</div>
                                </div>
                            </div>
                            <div class="comment-content mt-2">
                                ${comment.comment.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    // Check if user is logged in (PHP variable passed to JavaScript)
    const isLoggedIn = <?php echo is_logged_in() ? 'true' : 'false'; ?>;
    const userName = <?php echo isset($_SESSION['user_name']) ? '"' . addslashes($_SESSION['user_name']) . '"' : '"Logged in user"'; ?>;
    const isAdmin = <?php echo is_admin() ? 'true' : 'false'; ?>;
    
    html += `
        </div>
        <div class="add-comment-section mt-4">
            <h6>Leave a Comment</h6>
            <form id="quickCommentForm">
                ${isLoggedIn ? `
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-user-circle me-2"></i>
                        Commenting as ${userName}
                        ${isAdmin ? '<i class="fas fa-star text-warning ms-1" title="Admin User"></i>' : ''}
                    </div>
                ` : `
                    <div class="mb-3">
                        <input type="text" class="form-control" id="quickCommentName" name="quick_comment_name" placeholder="Your name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" id="quickCommentEmail" name="quick_comment_email" placeholder="Your email" required>
                    </div>
                `}
                <div class="mb-3">
                    <textarea class="form-control" id="quickCommentText" name="quick_comment_text" rows="3" placeholder="Your comment" required></textarea>
                </div>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-paper-plane me-2"></i>Post Comment
                </button>
            </form>
        </div>
    `;
    
    contentDiv.innerHTML = html;
    
    // Add form submit handler
    document.getElementById('quickCommentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitQuickComment(news.id);
    });
}

function submitQuickComment(newsId) {
    const comment = document.getElementById('quickCommentText').value;
    
    if (!comment.trim()) {
        showNotification('Please enter a comment', 'error');
        return;
    }
    
    // Prepare data object
    const data = {
        news_id: newsId,
        comment: comment
    };
    
    // Only add name and email for guest users (when fields exist)
    const nameField = document.getElementById('quickCommentName');
    const emailField = document.getElementById('quickCommentEmail');
    if (nameField && emailField) {
        data.name = nameField.value;
        data.email = emailField.value;
    }
    
    fetch('api/submit-comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Comment submitted successfully! It will be visible after approval.', 'success');
            document.getElementById('quickCommentForm').reset();
            // Reload comments
            showCommentsModal(newsId);
        } else {
            showNotification(data.message || 'Error submitting comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting comment:', error);
        showNotification('Error submitting comment', 'error');
    });
}

// Toggle like function
function toggleLike(newsId, button) {
    const likesCountSpan = button.querySelector('.likes-count');
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span class="likes-count">...</span>';
    
    fetch('api/toggle-like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            news_id: newsId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update likes count
            likesCountSpan.textContent = data.likes_count;
            
            // Update button state
            if (data.action === 'liked') {
                button.classList.remove('btn-outline-danger');
                button.classList.add('btn-danger');
                button.classList.add('liked');
                showNotification('Post liked!', 'success');
            } else {
                button.classList.remove('btn-danger');
                button.classList.remove('liked');
                button.classList.add('btn-outline-danger');
                showNotification('Post unliked', 'info');
            }
        } else {
            showNotification(data.message || 'Error updating like', 'error');
            // Restore original content
            button.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error toggling like:', error);
        showNotification('Error updating like', 'error');
        // Restore original content
        button.innerHTML = originalContent;
    })
    .finally(() => {
        button.disabled = false;
    });
}

// Show notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(notification);
    
    // Auto-remove notification after 3 seconds
    setTimeout(() => {
        if (notification && notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>
