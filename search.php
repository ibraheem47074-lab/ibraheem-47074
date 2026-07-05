<?php
require_once 'config/database.php';

// Get search query
$query = isset($_GET['q']) ? clean_input($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if (empty($query)) {
    redirect('index.php');
}

$page_title = "Search Results for: " . htmlspecialchars($query);

// Helper function to highlight search terms
function highlightSearchTerm($text, $term) {
    if (empty($term)) return $text;
    
    $words = explode(' ', $term);
    foreach ($words as $word) {
        if (strlen($word) > 2) {
            $text = preg_replace('/(' . preg_quote($word, '/') . ')/i', '<span class="search-highlight">$1</span>', $text);
        }
    }
    return $text;
}

// Pagination
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search news
$search_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                 (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                 (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count,
                 (CASE WHEN n.title LIKE ? THEN 3 
                       WHEN n.excerpt LIKE ? THEN 2 
                       WHEN n.content LIKE ? THEN 1 
                       ELSE 0 END) as relevance
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 LEFT JOIN users u ON n.author_id = u.id 
                 WHERE n.status = 'published' AND n.published_at <= NOW() 
                 AND (n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)
                 ORDER BY relevance DESC, n.published_at DESC 
                 LIMIT $per_page OFFSET $offset";

$search_term = "%$query%";
$stmt = mysqli_prepare($conn, $search_query);
mysqli_stmt_bind_param($stmt, 'ssssss', $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get total results for pagination
$count_query = "SELECT COUNT(*) as total FROM news 
                WHERE status = 'published' AND published_at <= NOW() 
                AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
$count_stmt = mysqli_prepare($conn, $count_query);
mysqli_stmt_bind_param($count_stmt, 'sss', $search_term, $search_term, $search_term);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);
?>

<?php include 'includes/header.php'; ?>

<!-- Search Results Section -->
<section class="search-results-section py-4">
    <div class="container">
        <!-- Search Header -->
        <div class="search-header mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-2">Search Results</h1>
                    <p class="text-muted">
                        Found <?php echo number_format($total_records); ?> results for 
                        <strong>"<?php echo htmlspecialchars($query); ?>"</strong>
                    </p>
                </div>
                <div class="col-md-4">
                    <form method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control me-2" 
                               value="<?php echo htmlspecialchars($query); ?>" placeholder="Search again...">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Search Filters -->
        <div class="search-filters mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="form-label">Sort by:</label>
                            <select class="form-select" onchange="sortSearchResults(this.value)">
                                <option value="relevance">Relevance</option>
                                <option value="latest">Latest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="popular">Most Popular</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category:</label>
                            <select class="form-select" onchange="filterByCategory(this.value)">
                                <option value="">All Categories</option>
                                <?php
                                $categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' AND slug NOT IN ('bbc-world', 'cnn-international') ORDER BY name ASC");
                                while ($cat = mysqli_fetch_assoc($categories)):
                                ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range:</label>
                            <select class="form-select" onchange="filterByDate(this.value)">
                                <option value="">Any Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Results per page:</label>
                            <select class="form-select" onchange="changePerPage(this.value)">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div class="search-results">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="row g-4" id="searchResults">
                    <?php while ($news = mysqli_fetch_assoc($result)): ?>
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
                                        <?php 
                                        $time_status = '';
                                        $news_date = new DateTime($news['published_at'] ?? $news['created_at']);
                                        $now = new DateTime();
                                        $interval = $now->diff($news_date);
                                        
                                        if ($interval->days == 0) {
                                            if ($interval->h < 1) {
                                                $time_status = 'new';
                                            } else {
                                                $time_status = 'recent';
                                            }
                                        }
                                        ?>
                                        <?php if ($time_status === 'new'): ?>
                                            <span class="badge bg-secondary animate-pulse">
                                                <i class="fas fa-sparkles me-1"></i>NEW
                                            </span>
                                        <?php elseif ($time_status === 'recent'): ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-clock me-1"></i>Recent
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-2">
                                        <span class="badge bg-info"><?php echo htmlspecialchars($news['category_name'] ?? 'Category'); ?></span>
                                        <?php if (!empty($news['sentiment_label'])): ?>
                                            <?php 
                                            $sentiment_colors = ['positive' => 'success', 'negative' => 'danger', 'neutral' => 'secondary'];
                                            $sentiment_icons = ['positive' => '??', 'negative' => '??', 'neutral' => '??'];
                                            $color = $sentiment_colors[$news['sentiment_label']] ?? 'secondary';
                                            $icon = $sentiment_icons[$news['sentiment_label']] ?? '??';
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
                    <nav aria-label="Search results pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?q=' . urlencode($query) . '&page=1">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?q=' . urlencode($query) . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                            }
                            ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>No results found</h4>
                    <p class="text-muted">We couldn't find any results for "<strong><?php echo htmlspecialchars($query); ?></strong>"</p>
                    <div class="suggestions mt-4">
                        <h5>Suggestions:</h5>
                        <ul class="list-unstyled">
                            <li>• Check your spelling</li>
                            <li>• Try more general keywords</li>
                            <li>• Try different keywords</li>
                            <li>• Browse our categories</li>
                        </ul>
                        <div class="mt-3">
                            <a href="index.php" class="btn btn-danger me-2">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                            <a href="category.php" class="btn btn-outline-danger">
                                <i class="fas fa-tags me-2"></i>Browse Categories
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Popular Searches -->
        <div class="popular-searches mt-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Popular Searches</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <?php
                        // Get popular search terms (you could store these in a separate table)
                        $popular_terms = ['politics', 'sports', 'technology', 'breaking news', 'pakistan', 'international', 'business', 'entertainment'];
                        foreach ($popular_terms as $term):
                        ?>
                            <a href="?q=<?php echo urlencode($term); ?>" class="badge bg-light text-dark text-decoration-none p-2">
                                <?php echo htmlspecialchars($term); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Highlight search term in text
function highlightSearchTerm(text, term) {
    if (!term) return text;
    
    const regex = new RegExp(`(${term})`, 'gi');
    return text.replace(regex, '<span class="search-highlight">$1</span>');
}

// Sort search results
function sortSearchResults(sortBy) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('sort', sortBy);
    
    showLoading();
    
    // In a real application, you would fetch sorted results from your API
    setTimeout(() => {
        location.href = currentUrl.toString();
    }, 500);
}

// Filter by category
function filterByCategory(categoryId) {
    const currentUrl = new URL(window.location);
    if (categoryId) {
        currentUrl.searchParams.set('category', categoryId);
    } else {
        currentUrl.searchParams.delete('category');
    }
    
    showLoading();
    setTimeout(() => {
        location.href = currentUrl.toString();
    }, 500);
}

// Filter by date
function filterByDate(dateRange) {
    const currentUrl = new URL(window.location);
    if (dateRange) {
        currentUrl.searchParams.set('date', dateRange);
    } else {
        currentUrl.searchParams.delete('date');
    }
    
    showLoading();
    setTimeout(() => {
        location.href = currentUrl.toString();
    }, 500);
}

// Change results per page
function changePerPage(perPage) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('per_page', perPage);
    currentUrl.searchParams.set('page', '1'); // Reset to first page
    
    showLoading();
    setTimeout(() => {
        location.href = currentUrl.toString();
    }, 500);
}

// Show loading state
function showLoading() {
    const resultsContainer = document.getElementById('searchResults');
    if (resultsContainer) {
        resultsContainer.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Updating search results...</p>
            </div>
        `;
    }
}

// Track search analytics (for future enhancement)
function trackSearch(query, resultsCount) {
    // In a real application, you would send this data to your analytics
    console.log('Search tracked:', { query, resultsCount, timestamp: new Date() });
}

// Track current search
document.addEventListener('DOMContentLoaded', function() {
    const query = '<?php echo htmlspecialchars($query); ?>';
    const resultsCount = <?php echo $total_records; ?>;
    
    if (query) {
        trackSearch(query, resultsCount);
    }
});

// Auto-complete search suggestions (for future enhancement)
function initAutoComplete() {
    const searchInput = document.querySelector('input[name="q"]');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            }
        });
    }
}

function fetchSuggestions(query) {
    // In a real application, you would fetch suggestions from your API
    console.log('Fetching suggestions for:', query);
}

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
                        <input type="text" class="form-control" id="quickCommentName" placeholder="Your name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" id="quickCommentEmail" placeholder="Your email" required>
                    </div>
                `}
                <div class="mb-3">
                    <textarea class="form-control" id="quickCommentText" rows="3" placeholder="Your comment" required></textarea>
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

// Initialize auto-complete
document.addEventListener('DOMContentLoaded', initAutoComplete);
</script>
