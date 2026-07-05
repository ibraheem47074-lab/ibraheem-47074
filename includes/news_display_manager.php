<?php
/**
 * Copyright-Compliant News Display System
 * Handles display of RSS imported news with proper attribution
 */

class NewsDisplayManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Get news items for display with copyright compliance
     */
    public function getNewsForDisplay($limit = 20, $offset = 0, $category = null) {
        $query = "SELECT n.*, c.name as category_name, c.color as category_color,
                 CASE 
                    WHEN n.source_url LIKE '%bbc%' THEN 'BBC News'
                    WHEN n.source_url LIKE '%cnn%' THEN 'CNN'
                    WHEN n.source_url LIKE '%arynews%' THEN 'ARY News'
                    WHEN n.source_url LIKE '%reuters%' THEN 'Reuters'
                    WHEN n.source_url LIKE '%aljazeera%' THEN 'Al Jazeera'
                    WHEN n.source_url LIKE '%fox%' THEN 'Fox News'
                    ELSE 'External Source'
                 END as source_name,
                 (SELECT COUNT(*) FROM comments WHERE news_id = n.id AND status = 'approved') as comment_count,
                 (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count
                 FROM news n 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 WHERE n.status = 'published' AND n.published_at <= NOW()";
        
        $params = [];
        $types = '';
        
        if ($category) {
            $query .= " AND c.slug = ?";
            $params[] = $category;
            $types .= 's';
        }
        
        $query .= " ORDER BY n.published_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = mysqli_prepare($this->conn, $query);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $news = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $news[] = $this->processNewsItem($row);
        }
        
        return $news;
    }
    
    /**
     * Process a news item for display
     */
    private function processNewsItem($item) {
        // Add display flags
        $item['is_external'] = !empty($item['source_url']) && $item['news_type'] === 'rss_import';
        $item['show_full_content'] = !$this->shouldShowSummaryOnly($item);
        $item['attribution_required'] = $item['is_external'];
        $item['content_preview'] = $this->getContentPreview($item);
        $item['source_icon'] = $this->getSourceIcon($item['source_name']);
        
        // Format time status
        $item['time_status'] = $this->getTimeStatus($item['published_at']);
        
        return $item;
    }
    
    /**
     * Check if content should be summary only (copyright protection)
     */
    private function shouldShowSummaryOnly($item) {
        $summaryOnlySources = ['BBC News', 'CNN', 'Reuters', 'Al Jazeera', 'Fox News'];
        
        // Show summary only for certain sources
        if (in_array($item['source_name'], $summaryOnlySources)) {
            return true;
        }
        
        // Show summary only if explicitly marked
        if (isset($item['summary_only']) && $item['summary_only'] == 1) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get content preview based on copyright rules
     */
    private function getContentPreview($item) {
        $content = $item['content'] ?? '';
        
        if ($this->shouldShowSummaryOnly($item)) {
            // Return only the first paragraph or excerpt
            $excerpt = $item['excerpt'] ?? '';
            if (!empty($excerpt)) {
                return $excerpt;
            }
            
            // Extract first paragraph
            if (preg_match('/<p>(.*?)<\/p>/i', $content, $matches)) {
                return strip_tags($matches[1]);
            }
            
            // Fallback to first 200 characters
            return substr(strip_tags($content), 0, 200) . '...';
        }
        
        return $content;
    }
    
    /**
     * Get source icon HTML
     */
    private function getSourceIcon($sourceName) {
        $sourceIcons = [
            'BBC News' => '<i class="fas fa-broadcast-tower text-primary" title="BBC News"></i>',
            'CNN' => '<i class="fas fa-satellite-dish text-danger" title="CNN"></i>',
            'ARY News' => '<i class="fas fa-tv text-success" title="ARY News"></i>',
            'Reuters' => '<i class="fas fa-newspaper text-info" title="Reuters"></i>',
            'Al Jazeera' => '<i class="fas fa-globe-africa text-warning" title="Al Jazeera"></i>',
            'Fox News' => '<i class="fas fa-fox text-orange" title="Fox News"></i>',
            'External Source' => '<i class="fas fa-external-link-alt text-secondary" title="External Source"></i>',
            'default' => '<i class="fas fa-rss text-secondary" title="News Source"></i>'
        ];
        
        return $sourceIcons[$sourceName] ?? $sourceIcons['default'];
    }
    
    /**
     * Get time status for news item
     */
    private function getTimeStatus($publishedAt) {
        $now = new DateTime();
        $published = new DateTime($publishedAt);
        $interval = $now->diff($published);
        
        if ($interval->h < 1) {
            return 'new';
        } elseif ($interval->h < 24) {
            return 'recent';
        } else {
            return 'older';
        }
    }
    
    /**
     * Render news card HTML
     */
    public function renderNewsCard($item, $size = 'medium') {
        $sizeClasses = [
            'small' => 'col-md-6 col-lg-4',
            'medium' => 'col-md-6',
            'large' => 'col-12'
        ];
        
        $imageHeight = [
            'small' => '150px',
            'medium' => '200px',
            'large' => '400px'
        ];
        
        $colClass = $sizeClasses[$size] ?? $sizeClasses['medium'];
        $imgHeight = $imageHeight[$size] ?? $imageHeight['medium'];
        
        ?>
        <div class="<?php echo $colClass; ?> mb-4">
            <div class="card border-0 shadow news-card h-100">
                <?php if ($item['image']): ?>
                <div class="position-relative">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                         style="height: <?php echo $imgHeight; ?>; object-fit: cover;">
                    
                    <!-- Views Badge -->
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="badge bg-dark bg-opacity-75 text-white">
                            <i class="fas fa-eye me-1"></i> <?php echo number_format($item['views'] ?? 0); ?>
                        </span>
                    </div>
                    
                    <!-- Time Status Badge -->
                    <div class="position-absolute top-0 end-0 m-2">
                        <?php if ($item['time_status'] === 'new'): ?>
                            <span class="badge bg-danger animate-pulse">
                                <i class="fas fa-sparkles me-1"></i>NEW
                            </span>
                        <?php elseif ($item['time_status'] === 'recent'): ?>
                            <span class="badge bg-warning">
                                <i class="fas fa-clock me-1"></i>Recent
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Date Overlay -->
                    <div class="position-absolute bottom-0 start-0 end-0 p-3">
                        <div class="date-overlay text-white rounded p-2">
                            <small><?php echo date('M j, H:i', strtotime($item['published_at'])); ?></small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card-body d-flex flex-column">
                    <!-- Category and Source Badges -->
                    <div class="mb-2">
                        <span class="badge bg-info"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></span>
                        
                        <?php if ($item['is_external'] && !empty($item['source_name'])): ?>
                            <span class="badge bg-dark ms-1">
                                <?php echo $item['source_icon']; ?>
                                <span class="ms-1"><?php echo htmlspecialchars($item['source_name']); ?></span>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($item['attribution_required']): ?>
                            <span class="badge bg-secondary ms-1" title="Content from external source">
                                <i class="fas fa-external-link-alt"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Title -->
                    <h5 class="card-title">
                        <a href="news.php?id=<?php echo $item['id']; ?>" 
                           class="text-decoration-none text-dark">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                    </h5>
                    
                    <!-- Content -->
                    <div class="card-text text-muted mb-3">
                        <?php if ($item['show_full_content']): ?>
                            <?php echo $item['content_preview']; ?>
                        <?php else: ?>
                            <p><?php echo $item['content_preview']; ?></p>
                            <div class="alert alert-info py-2">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    This is a summary. 
                                    <a href="<?php echo htmlspecialchars($item['source_url']); ?>" 
                                       target="_blank" rel="noopener" 
                                       class="text-decoration-none">
                                        Read full story on <?php echo htmlspecialchars($item['source_name']); ?>
                                    </a>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- News Actions - Facebook Style -->
                    <div class="news-actions mt-auto">
                        <div class="row g-0 border-top border-bottom">
                            <div class="col-4">
                                <button class="btn w-100 py-2 rounded-0 border-0 text-muted hover-bg-light action-btn" 
                                        onclick="toggleLike(<?php echo $item['id']; ?>, this)" 
                                        title="Like">
                                    <i class="fas fa-thumbs-up me-2"></i>Like
                                    <span class="likes-count d-block small text-muted"><?php echo $item['likes_count'] ?? 0; ?></span>
                                </button>
                            </div>
                            <div class="col-4 border-start">
                                <button class="btn w-100 py-2 rounded-0 border-0 text-muted hover-bg-light action-btn" 
                                        onclick="showCommentsModal(<?php echo $item['id']; ?>)" 
                                        title="Comment">
                                    <i class="fas fa-comment me-2"></i>Comment
                                    <span class="d-block small text-muted"><?php echo $item['comment_count'] ?? 0; ?></span>
                                </button>
                            </div>
                            <div class="col-4 border-start">
                                <button class="btn w-100 py-2 rounded-0 border-0 text-muted hover-bg-light action-btn" 
                                        onclick="toggleShareMenu(<?php echo $item['id']; ?>)" 
                                        title="Share">
                                    <i class="fas fa-share me-2"></i>Share
                                </button>
                            </div>
                        </div>
                        <!-- Share Dropdown Menu -->
                        <div id="share-menu-<?php echo $item['id']; ?>" class="share-menu d-none position-absolute bg-white border shadow rounded p-2" style="z-index: 1000;">
                            <button class="btn btn-sm btn-outline-primary w-100 mb-1" onclick="shareOnFacebook('<?php echo SITE_URL; ?>news.php?id=<?php echo $item['id']; ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                                <i class="fab fa-facebook-f me-2"></i>Facebook
                            </button>
                            <button class="btn btn-sm btn-outline-success w-100 mb-1" onclick="shareOnWhatsApp('<?php echo SITE_URL; ?>news.php?id=<?php echo $item['id']; ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </button>
                            <button class="btn btn-sm btn-outline-secondary w-100" onclick="copyToClipboard('<?php echo SITE_URL; ?>news.php?id=<?php echo $item['id']; ?>')">
                                <i class="fas fa-link me-2"></i>Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render featured news card (larger version)
     */
    public function renderFeaturedCard($item) {
        ?>
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-lg news-card featured-news">
                <div class="position-relative">
                    <?php if ($item['image']): ?>
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($item['title']); ?>" 
                             style="height: 400px; object-fit: cover;">
                        
                        <!-- Featured Badge -->
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-danger fs-6">
                                <i class="fas fa-star me-1"></i>FEATURED
                            </span>
                        </div>
                        
                        <!-- Views Badge -->
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-dark bg-opacity-75 text-white">
                                <i class="fas fa-eye me-1"></i> <?php echo number_format($item['views'] ?? 0); ?>
                            </span>
                        </div>
                        
                        <!-- Date Overlay -->
                        <div class="position-absolute bottom-0 start-0 end-0 p-3">
                            <div class="date-overlay text-white rounded p-2">
                                <small><?php echo date('M j, H:i', strtotime($item['published_at'])); ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-body p-4">
                    <!-- Category and Source -->
                    <div class="mb-3">
                        <span class="badge bg-danger fs-6"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></span>
                        
                        <?php if ($item['is_external'] && !empty($item['source_name'])): ?>
                            <span class="badge bg-dark ms-2">
                                <?php echo $item['source_icon']; ?>
                                <span class="ms-1"><?php echo htmlspecialchars($item['source_name']); ?></span>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($item['attribution_required']): ?>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Content courtesy of <?php echo htmlspecialchars($item['source_name']); ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Title -->
                    <h2 class="card-title mb-3">
                        <a href="news.php?id=<?php echo $item['id']; ?>" 
                           class="text-decoration-none text-dark">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                    </h2>
                    
                    <!-- Content -->
                    <div class="card-text mb-4">
                        <?php if ($item['show_full_content']): ?>
                            <?php echo $item['content_preview']; ?>
                        <?php else: ?>
                            <p><?php echo $item['content_preview']; ?></p>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Full Story:</strong> 
                                <a href="<?php echo htmlspecialchars($item['source_url']); ?>" 
                                   target="_blank" rel="noopener" 
                                   class="text-decoration-none">
                                    Read complete article on <?php echo htmlspecialchars($item['source_name']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Actions - Facebook Style -->
                    <div class="news-actions">
                        <div class="row g-0 border-top border-bottom">
                            <div class="col-4">
                                <button class="btn w-100 py-3 rounded-0 border-0 text-muted hover-bg-light action-btn" 
                                        onclick="toggleLike(<?php echo $item['id']; ?>, this)" 
                                        title="Like">
                                    <i class="fas fa-thumbs-up me-2"></i>Like
                                    <span class="likes-count d-block small text-muted"><?php echo $item['likes_count'] ?? 0; ?></span>
                                </button>
                            </div>
                            <div class="col-4 border-start">
                                <button class="btn w-100 py-3 rounded-0 border-0 text-muted hover-bg-light action-btn" 
                                        onclick="showCommentsModal(<?php echo $item['id']; ?>)" 
                                        title="Comment">
                                    <i class="fas fa-comment me-2"></i>Comment
                                    <span class="d-block small text-muted"><?php echo $item['comment_count'] ?? 0; ?></span>
                                </button>
                            </div>
                            <div class="col-4 border-start">
                                <button class="btn w-100 py-3 rounded-0 border-0 text-muted hover-bg-light action-btn" 
                                        onclick="toggleShareMenu(<?php echo $item['id']; ?>)" 
                                        title="Share">
                                    <i class="fas fa-share me-2"></i>Share
                                </button>
                            </div>
                        </div>
                        <!-- Share Dropdown Menu -->
                        <div id="share-menu-<?php echo $item['id']; ?>" class="share-menu d-none position-absolute bg-white border shadow rounded p-2" style="z-index: 1000;">
                            <button class="btn btn-sm btn-outline-primary w-100 mb-1" onclick="shareOnFacebook('<?php echo SITE_URL; ?>news.php?id=<?php echo $item['id']; ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                                <i class="fab fa-facebook-f me-2"></i>Facebook
                            </button>
                            <button class="btn btn-sm btn-outline-success w-100 mb-1" onclick="shareOnWhatsApp('<?php echo SITE_URL; ?>news.php?id=<?php echo $item['id']; ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </button>
                            <button class="btn btn-sm btn-outline-secondary w-100" onclick="copyToClipboard('<?php echo SITE_URL; ?>news.php?id=<?php echo $item['id']; ?>')">
                                <i class="fas fa-link me-2"></i>Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

// Global function for easy access
function render_news_card($item, $size = 'medium') {
    global $conn;
    static $displayManager = null;
    if ($displayManager === null) {
        $displayManager = new NewsDisplayManager($conn);
    }
    $displayManager->renderNewsCard($item, $size);
}

function get_news_for_display($limit = 20, $offset = 0, $category = null) {
    global $conn;
    static $displayManager = null;
    if ($displayManager === null) {
        $displayManager = new NewsDisplayManager($conn);
    }
    return $displayManager->getNewsForDisplay($limit, $offset, $category);
}
?>
