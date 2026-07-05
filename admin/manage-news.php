<?php
require_once '../config/database.php';

// Check if user is logged in and is admin or editor
if (!is_logged_in() || (!is_admin() && !is_editor())) {
    redirect('login.php');
}

// Handle delete action - FIXED: Only delete specific article
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $news_id = (int)$_GET['delete'];
    
    // Add ownership check for editors (admin can delete all)
    if (!is_admin()) {
        $check_query = "SELECT author_id FROM news WHERE id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'i', $news_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $news_author = mysqli_fetch_assoc($result);
        
        if ($news_author['author_id'] != $_SESSION['user_id']) {
            $error = "You can only delete your own news articles!";
            header('Location: manage-news.php?error=' . urlencode($error));
            exit;
        }
    }
    
    // Get news to delete files
    $news_query = "SELECT image, video_path FROM news WHERE id = ?";
    $stmt = mysqli_prepare($conn, $news_query);
    mysqli_stmt_bind_param($stmt, 'i', $news_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $news = mysqli_fetch_assoc($result);
    
    // Delete news
    $delete_query = "DELETE FROM news WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $news_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Delete image file if exists
        if ($news && $news['image'] && file_exists('../' . $news['image'])) {
            unlink('../' . $news['image']);
        }
        
        // Delete video file if exists
        if ($news && $news['video_path'] && file_exists('../' . $news['video_path'])) {
            unlink('../' . $news['video_path']);
        }
        
        // Delete related comments using prepared statement
        $delete_comments = "DELETE FROM comments WHERE news_id = ?";
        $comments_stmt = mysqli_prepare($conn, $delete_comments);
        mysqli_stmt_bind_param($comments_stmt, 'i', $news_id);
        mysqli_stmt_execute($comments_stmt);
        mysqli_stmt_close($comments_stmt);
        
        $success = "News article deleted successfully!";
    } else {
        $error = "Error deleting news article!";
    }
}

// Handle status change - FIXED: Only update specific article
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $news_id = (int)$_GET['id'];
    $status = clean_input($_GET['status']);
    
    if (in_array($status, ['draft', 'published', 'featured'])) {
        $update_query = "UPDATE news SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $news_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "News status updated successfully!";
        } else {
            $error = "Error updating news status!";
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search and filter
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$sentiment_filter = isset($_GET['sentiment']) ? clean_input($_GET['sentiment']) : '';
$media_filter = isset($_GET['media_type']) ? clean_input($_GET['media_type']) : '';
$date_from = isset($_GET['date_from']) ? clean_input($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? clean_input($_GET['date_to']) : '';

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['selected_news']) && is_array($_POST['selected_news'])) {
    $selected_news = array_map('intval', $_POST['selected_news']);
    $bulk_action = $_POST['bulk_action'];
    
    // Add ownership check for editors
    if (!is_admin()) {
        $placeholders = str_repeat('?', count($selected_news));
        $check_query = "SELECT id, author_id FROM news WHERE id IN ($placeholders)";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, str_repeat('i', count($selected_news)), ...$selected_news);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        $user_news = [];
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['author_id'] == $_SESSION['user_id']) {
                $user_news[] = $row['id'];
            }
        }
        
        $selected_news = $user_news;
        if (empty($selected_news)) {
            $error = "You can only manage your own news articles!";
            header('Location: manage-news.php?error=' . urlencode($error));
            exit;
        }
    }
    
    switch ($bulk_action) {
        case 'delete':
            if (empty($selected_news)) {
                $error = "No articles selected for deletion!";
                header('Location: manage-news.php?error=' . urlencode($error));
                exit;
            }
            $placeholders = str_repeat('?,', count($selected_news));
            $placeholders = rtrim($placeholders, ',');
            $delete_query = "DELETE FROM news WHERE id IN ($placeholders)";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, str_repeat('i', count($selected_news)), ...$selected_news);
            
            if (mysqli_stmt_execute($stmt)) {
                // Delete associated files
                foreach ($selected_news as $news_id) {
                    $file_query = "SELECT image, video_path FROM news WHERE id = ?";
                    $file_stmt = mysqli_prepare($conn, $file_query);
                    mysqli_stmt_bind_param($file_stmt, 'i', $news_id);
                    mysqli_stmt_execute($file_stmt);
                    $file_result = mysqli_stmt_get_result($file_stmt);
                    $news_files = mysqli_fetch_assoc($file_result);
                    
                    if ($news_files['image'] && file_exists('../' . $news_files['image'])) {
                        unlink('../' . $news_files['image']);
                    }
                    if ($news_files['video_path'] && file_exists('../' . $news_files['video_path'])) {
                        unlink('../' . $news_files['video_path']);
                    }
                }
                
                // Delete comments
                $comment_delete_query = "DELETE FROM comments WHERE news_id IN ($placeholders)";
                $comment_stmt = mysqli_prepare($conn, $comment_delete_query);
                mysqli_stmt_bind_param($comment_stmt, str_repeat('i', count($selected_news)), ...$selected_news);
                mysqli_stmt_execute($comment_stmt);
                
                $success = count($selected_news) . " news articles deleted successfully!";
            } else {
                $error = "Error deleting news articles!";
            }
            break;
            
        case 'publish':
            if (empty($selected_news)) {
                $error = "No articles selected for publishing!";
                header('Location: manage-news.php?error=' . urlencode($error));
                exit;
            }
            $placeholders = str_repeat('?,', count($selected_news));
            $placeholders = rtrim($placeholders, ',');
            $update_query = "UPDATE news SET status = 'published' WHERE id IN ($placeholders)";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, str_repeat('i', count($selected_news)), ...$selected_news);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = count($selected_news) . " news articles published successfully!";
            } else {
                $error = "Error publishing news articles!";
            }
            break;
            
        case 'draft':
            if (empty($selected_news)) {
                $error = "No articles selected for draft status!";
                header('Location: manage-news.php?error=' . urlencode($error));
                exit;
            }
            $placeholders = str_repeat('?,', count($selected_news));
            $placeholders = rtrim($placeholders, ',');
            $update_query = "UPDATE news SET status = 'draft' WHERE id IN ($placeholders)";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, str_repeat('i', count($selected_news)), ...$selected_news);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = count($selected_news) . " news articles moved to draft successfully!";
            } else {
                $error = "Error updating news articles!";
            }
            break;
    }
    
    header('Location: manage-news.php?success=' . urlencode($success));
    exit;
}

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(n.title LIKE ? OR n.content LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($category_filter > 0) {
    $where_conditions[] = "n.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

if (!empty($filter_status)) {
    $where_conditions[] = "n.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($sentiment_filter)) {
    $where_conditions[] = "n.sentiment_label = ?";
    $params[] = $sentiment_filter;
    $types .= 's';
}

if (!empty($media_filter)) {
    switch ($media_filter) {
        case 'image':
            $where_conditions[] = "n.image IS NOT NULL AND n.image != ''";
            break;
        case 'video':
            $where_conditions[] = "(n.video_path IS NOT NULL AND n.video_path != '' OR n.video_url IS NOT NULL AND n.video_url != '')";
            break;
        case 'both':
            $where_conditions[] = "n.image IS NOT NULL AND n.image != '' AND (n.video_path IS NOT NULL AND n.video_path != '' OR n.video_url IS NOT NULL AND n.video_url != '')";
            break;
        case 'text':
            $where_conditions[] = "n.image IS NULL OR n.image = '' AND n.video_path IS NULL AND n.video_path = '' AND n.video_url IS NULL OR n.video_url = ''";
            break;
    }
}

if (!empty($date_from)) {
    $where_conditions[] = "n.created_at >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "n.created_at <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total records
$count_query = "SELECT COUNT(*) as total FROM news n $where_clause";
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $count_query);
}
$total_records = mysqli_fetch_assoc($result)['total'];
$total_pages = ceil($total_records / $per_page);

// Get news
$news_query = "SELECT n.*, c.name as category_name, u.name as author_name 
               FROM news n 
               LEFT JOIN categories c ON n.category_id = c.id 
               LEFT JOIN users u ON n.author_id = u.id 
               $where_clause
               ORDER BY n.created_at DESC 
               LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $news_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $news_result = mysqli_stmt_get_result($stmt);
} else {
    $news_result = mysqli_query($conn, $news_query);
}

// Get categories for filter
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");

// Set page title for header
$page_title = 'Manage News';
?>

<?php include 'includes/editor-header.php'; ?>
<!-- Add custom styles for news management -->
    <style>
        .news-image-thumb {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2">Manage News Articles</h1>
            <p class="text-muted">Edit, publish, or manage your news content</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="fix-invalid-dates.php" class="btn btn-warning" title="Fix Invalid Dates">
                    <i class="fas fa-calendar-alt me-1"></i>Fix Invalid Dates
                </a>
            </div>
        </div>
    </div>

                <!-- Alerts -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Search news..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="draft" <?php echo $filter_status == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo $filter_status == 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="featured" <?php echo $filter_status == 'featured' ? 'selected' : ''; ?>>Featured</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="media_type">
                                    <option value="">All Media</option>
                                    <option value="text" <?php echo $media_filter == 'text' ? 'selected' : ''; ?>>📝 Text Only</option>
                                    <option value="image" <?php echo $media_filter == 'image' ? 'selected' : ''; ?>>🖼️ Image</option>
                                    <option value="video" <?php echo $media_filter == 'video' ? 'selected' : ''; ?>>🎥 Video</option>
                                    <option value="both" <?php echo $media_filter == 'both' ? 'selected' : ''; ?>>🖼️🎥 Both</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="date_from" placeholder="From Date" value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- News Table -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-column flex-md-row">
                        <h5 class="mb-0 mb-md-0">News Articles (<?php echo $total_records; ?> total)</h5>
                        <div class="d-flex gap-2 flex-wrap justify-content-center justify-content-md-start">
                            <!-- Add Article Button -->
                            <a href="add-news.php" class="btn btn-primary btn-sm" title="Add New Article">
                                <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline">Add Article</span>
                            </a>
                            <!-- Bulk Actions -->
                            <div class="dropdown me-2">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Bulk Actions">
                                    <i class="fas fa-tasks me-1"></i><span class="d-none d-sm-inline">Bulk Actions</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="selectAll()">Select All</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="deselectAll()">Deselect All</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="bulkDelete()">Delete Selected</a></li>
                                    <li><a class="dropdown-item text-success" href="#" onclick="bulkPublish()">Publish Selected</a></li>
                                    <li><a class="dropdown-item text-warning" href="#" onclick="bulkDraft()">Move to Draft</a></li>
                                </ul>
                            </div>
                            <a href="manage-sources.php" class="btn btn-outline-info btn-sm" title="Manage News Sources">
                                <i class="fas fa-rss me-1"></i><span class="d-none d-sm-inline">News Sources</span>
                            </a>
                            <a href="scrape-news.php" class="btn btn-outline-warning btn-sm" title="Scrape News">
                                <i class="fas fa-spider me-1"></i><span class="d-none d-sm-inline">Scrape News</span>
                            </a>
                            <a href="../rss_test_interface.php" class="btn btn-outline-success btn-sm" target="_blank" title="RSS Management">
                                <i class="fas fa-rss me-1"></i><span class="d-none d-sm-inline">RSS Management</span>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAllCheckbox" name="select_all" onchange="toggleSelectAll()"></th>
                                        <th>Media</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Sentiment</th>
                                        <th>Views</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($news = mysqli_fetch_assoc($news_result)): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_news[]" value="<?php echo $news['id']; ?>" class="form-check-input news-checkbox">
                                            </td>
                                            <td>
                                                <?php
                                                $media_type = 'text';
                                                if (!empty($news['image']) && !empty($news['video_path']) && !empty($news['video_url'])) {
                                                    $media_type = 'both';
                                                } elseif (!empty($news['image'])) {
                                                    $media_type = 'image';
                                                } elseif (!empty($news['video_path']) || !empty($news['video_url'])) {
                                                    $media_type = 'video';
                                                }
                                                
                                                $media_icons = [
                                                    'text' => '📝',
                                                    'image' => '🖼️',
                                                    'video' => '🎥',
                                                    'both' => '🖼️🎥'
                                                ];
                                                
                                                echo '<span title="Media Type: ' . ucfirst($media_type) . '">' . $media_icons[$media_type] . '</span>';
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($news['image']): ?>
                                                    <img src="../<?php echo htmlspecialchars($news['image']); ?>" alt="News Image" class="news-image-thumb">
                                                <?php else: ?>
                                                    <div class="news-image-thumb bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($news['title']); ?></strong>
                                                    <?php if ($news['is_breaking'] ?? false): ?>
                                                        <span class="badge bg-danger ms-2">Breaking</span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted"><?php echo htmlspecialchars(substr($news['excerpt'] ?? '', 0, 100)) . '...'; ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($news['category_name'] ?? ''); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($news['author_name'] ?? ''); ?></td>
                                            <td>
                                                <?php
                                                $status_class = $news['status'] == 'published' ? 'bg-success' : 
                                                               ($news['status'] == 'draft' ? 'bg-warning' : 'bg-info');
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($news['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($news['sentiment_label'])): ?>
                                                    <?php 
                                                    $sentiment_colors = ['positive' => 'success', 'negative' => 'danger', 'neutral' => 'secondary'];
                                                    $sentiment_icons = ['positive' => '😊', 'negative' => '😔', 'neutral' => '😐'];
                                                    $color = $sentiment_colors[$news['sentiment_label']] ?? 'secondary';
                                                    $icon = $sentiment_icons[$news['sentiment_label']] ?? '😐';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>" title="Score: <?php echo $news['sentiment_score']; ?>">
                                                        <?php echo $icon; ?> <?php echo ucfirst($news['sentiment_label']); ?>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted"><?php echo $news['sentiment_score']; ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo number_format($news['views'] ?? 0); ?></td>
                                            <td>
                                                <?php 
                                                // Fix invalid date display
                                                $created_at = $news['created_at'] ?? '';
                                                if (empty($created_at) || $created_at === '0000-00-00 00:00:00' || strtotime($created_at) === false) {
                                                    echo '<span class="text-muted">Invalid Date</span>';
                                                } else {
                                                    echo date('M d, Y', strtotime($created_at));
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons d-flex gap-1">
                                                    <a href="../news.php?slug=<?php echo $news['slug']; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-news.php?id=<?php echo $news['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($news['status'] == 'published'): ?>
                                                        <a href="?id=<?php echo $news['id']; ?>&status=draft" class="btn btn-sm btn-outline-warning" title="Unpublish">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="?id=<?php echo $news['id']; ?>&status=published" class="btn btn-sm btn-outline-success" title="Publish">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if ($news['status'] != 'featured'): ?>
                                                        <a href="?id=<?php echo $news['id']; ?>&status=featured" class="btn btn-sm btn-outline-info" title="Feature">
                                                            <i class="fas fa-star"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="?delete=<?php echo $news['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this news article?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="News pagination">
                                <ul class="pagination justify-content-center">
                                    <?php 
                                    $query_params = http_build_query([
                                        'search' => $search,
                                        'category' => $category_filter,
                                        'status' => $filter_status,
                                        'sentiment' => $sentiment_filter
                                    ]);
                                    ?>
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $query_params; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo $query_params; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $query_params; ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <style>
        /* Mobile/Tablet Optimizations for News Management Table */
        @media (max-width: 991.98px) {
            /* Header adjustments */
            .card-header {
                flex-direction: column !important;
                gap: 1rem;
                align-items: stretch !important;
            }
            
            .card-header h5 {
                text-align: center;
                margin-bottom: 0;
            }
            
            .card-header .d-flex {
                justify-content: center !important;
                flex-wrap: wrap !important;
                gap: 0.5rem !important;
            }
            
            .card-header .btn {
                min-width: 44px;
                min-height: 44px;
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
            }
            
            /* Table responsive improvements */
            .table-responsive {
                border-radius: 0.5rem;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            .table {
                font-size: 0.875rem;
                margin-bottom: 0;
            }
            
            .table th {
                font-weight: 600;
                padding: 0.75rem 0.5rem;
                white-space: nowrap;
                border-top: none;
            }
            
            .table td {
                padding: 0.75rem 0.5rem;
                vertical-align: middle;
            }
            
            /* Hide less important columns on mobile */
            .table th:nth-child(2),
            .table td:nth-child(2),
            .table th:nth-child(5),
            .table td:nth-child(5),
            .table th:nth-child(8),
            .table td:nth-child(8),
            .table th:nth-child(9),
            .table td:nth-child(9) {
                display: none;
            }
            
            /* Image column adjustments */
            .news-image-thumb {
                width: 40px;
                height: 40px;
                object-fit: cover;
                border-radius: 0.375rem;
            }
            
            /* Title column adjustments */
            .table th:nth-child(4),
            .table td:nth-child(4) {
                min-width: 200px;
            }
            
            .table td:nth-child(4) strong {
                font-size: 0.9rem;
                line-height: 1.3;
                display: block;
                margin-bottom: 0.25rem;
            }
            
            .table td:nth-child(4) small {
                font-size: 0.75rem;
                line-height: 1.2;
            }
            
            /* Status and author columns */
            .badge {
                font-size: 0.7rem;
                padding: 0.25rem 0.5rem;
            }
            
            /* Action buttons - make them touch-friendly */
            .action-buttons {
                flex-wrap: wrap;
                gap: 0.25rem !important;
                justify-content: center;
            }
            
            .action-buttons .btn {
                min-width: 36px;
                min-height: 36px;
                padding: 0.375rem;
                font-size: 0.75rem;
                border-radius: 0.375rem;
                touch-action: manipulation;
            }
            
            /* Checkbox adjustments */
            .form-check-input {
                width: 20px;
                height: 20px;
                min-width: 20px;
                min-height: 20px;
            }
            
            /* Date column adjustments */
            .table th:nth-child(10),
            .table td:nth-child(10) {
                font-size: 0.8rem;
                white-space: nowrap;
            }
        }
        
        /* Small mobile devices */
        @media (max-width: 576px) {
            /* Even more aggressive hiding */
            .table th:nth-child(6),
            .table td:nth-child(6) {
                display: none;
            }
            
            /* Make title column primary */
            .table th:nth-child(4),
            .table td:nth-child(4) {
                min-width: 150px;
            }
            
            .table td:nth-child(4) strong {
                font-size: 0.85rem;
            }
            
            .table td:nth-child(4) small {
                display: none;
            }
            
            /* Smaller action buttons */
            .action-buttons .btn {
                min-width: 32px;
                min-height: 32px;
                padding: 0.25rem;
                font-size: 0.7rem;
            }
            
            /* Compact table */
            .table th,
            .table td {
                padding: 0.5rem 0.25rem;
            }
            
            .news-image-thumb {
                width: 35px;
                height: 35px;
            }
        }
        
        /* Touch device improvements */
        @media (hover: none) and (pointer: coarse) {
            .action-buttons .btn:active {
                transform: scale(0.95);
                transition: transform 0.1s ease;
            }
            
            .card-header .btn:active {
                transform: scale(0.95);
                transition: transform 0.1s ease;
            }
            
            .table tbody tr {
                transition: background-color 0.2s ease;
            }
            
            .table tbody tr:active {
                background-color: rgba(0,0,0,0.05);
            }
        }
        
        /* Pagination mobile adjustments */
        @media (max-width: 768px) {
            .pagination {
                flex-wrap: wrap;
                gap: 0.25rem;
                justify-content: center;
            }
            
            .page-link {
                min-width: 44px;
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
                border-radius: 0.375rem;
            }
        }
        
        /* Card improvements for mobile */
        @media (max-width: 991.98px) {
            .card {
                border-radius: 0.75rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                margin-bottom: 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
        }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bulk action functions
        function selectAll() {
            const checkboxes = document.querySelectorAll('input[name="selected_news[]"]');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }
        
        function deselectAll() {
            const checkboxes = document.querySelectorAll('input[name="selected_news[]"]');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectAllCheckbox.checked = false;
        }
        
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('input[name="selected_news[]"]');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = !selectAllCheckbox.checked;
            });
        }
        
        function getSelectedIds() {
            const checkboxes = document.querySelectorAll('input[name="selected_news[]"]:checked');
            return Array.from(checkboxes).map(cb => cb.value);
        }
        
        function bulkDelete() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                alert('Please select at least one news article to delete.');
                return;
            }
            
            if (confirm('Are you sure you want to delete ' + selectedIds.length + ' news articles?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'bulk_action';
                hiddenInput.value = 'delete';
                form.appendChild(hiddenInput);
                
                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_news[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function bulkPublish() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                alert('Please select at least one news article to publish.');
                return;
            }
            
            if (confirm('Are you sure you want to publish ' + selectedIds.length + ' news articles?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'bulk_action';
                hiddenInput.value = 'publish';
                form.appendChild(hiddenInput);
                
                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_news[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function bulkDraft() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                alert('Please select at least one news article to move to draft.');
                return;
            }
            
            if (confirm('Are you sure you want to move ' + selectedIds.length + ' news articles to draft?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'bulk_action';
                hiddenInput.value = 'draft';
                form.appendChild(hiddenInput);
                
                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_news[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
