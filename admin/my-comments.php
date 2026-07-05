<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get comments on reporter's articles with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Get total comments count
$count_query = "SELECT COUNT(*) as total 
               FROM comments cm 
               LEFT JOIN news n ON cm.news_id = n.id 
               WHERE n.author_id = $user_id";
$total_comments = mysqli_fetch_assoc(mysqli_query($conn, $count_query))['total'];

// Get comments
$comments_query = "SELECT cm.*, n.title as news_title, n.slug as news_slug 
                   FROM comments cm 
                   LEFT JOIN news n ON cm.news_id = n.id 
                   WHERE n.author_id = $user_id 
                   ORDER BY cm.created_at DESC 
                   LIMIT $per_page OFFSET $offset";
$comments = mysqli_query($conn, $comments_query);

// Calculate pagination
$total_pages = ceil($total_comments / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Comments - PK Live News Reporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .reporter-header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }
        .comment-card {
            border-left: 4px solid #f39c12;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/reporter-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-comments me-3"></i>Comments on My Articles</h2>
                <p class="text-muted">View and manage comments on your news articles.</p>
            </div>
        </div>

        <!-- Comments List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5><i class="fas fa-comments me-2"></i>Comments List</h5>
                        <div>
                            <small class="text-muted"><?php echo $total_comments; ?> total comments</small>
                        </div>
                    </div>

                    <?php if (mysqli_num_rows($comments) > 0): ?>
                        <div class="comments-list">
                            <?php while ($comment = mysqli_fetch_assoc($comments)): ?>
                                <div class="card comment-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1">
                                                    <i class="fas fa-user me-2"></i>
                                                    <?php echo htmlspecialchars($comment['name']); ?>
                                                    <small class="text-muted">(&lt;<?php echo htmlspecialchars($comment['email']); ?>&gt;)</small>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div>
                                                <a href="../news.php?slug=<?php echo $comment['news_slug']; ?>#comment-<?php echo $comment['id']; ?>" 
                                                   class="btn btn-sm btn-outline-info" target="_blank">
                                                    <i class="fas fa-external-link-alt"></i> View on Site
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <strong>On article:</strong>
                                            <a href="../news.php?slug=<?php echo $comment['news_slug']; ?>" target="_blank">
                                                <?php echo htmlspecialchars($comment['news_title']); ?>
                                            </a>
                                        </div>
                                        
                                        <div class="comment-content">
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                        </div>
                                        
                                        <?php if (!empty($comment['website'])): ?>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-globe me-1"></i>
                                                    <a href="<?php echo htmlspecialchars($comment['website']); ?>" target="_blank">
                                                        <?php echo htmlspecialchars($comment['website']); ?>
                                                    </a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Comments pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5>No comments found</h5>
                            <p class="text-muted">No comments have been posted on your articles yet.</p>
                            <a href="my-articles.php" class="btn btn-primary">
                                <i class="fas fa-newspaper me-1"></i>View My Articles
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
