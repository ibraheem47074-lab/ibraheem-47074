<?php
require_once 'config/database.php';

// Test query to get news with videos
$test_query = "SELECT n.*, c.name as category_name 
               FROM news n 
               LEFT JOIN categories c ON n.category_id = c.id 
               WHERE n.video_url IS NOT NULL AND n.video_url != '' 
               AND n.status = 'published' 
               ORDER BY n.published_at DESC LIMIT 5";

$result = mysqli_query($conn, $test_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Features Test - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/image-lightbox.css" rel="stylesheet">
    <link href="assets/css/video-lightbox.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4"><i class="fas fa-video me-2"></i>Video Features Test</h1>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row g-4">
                <?php while ($news = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow">
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
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($news['title']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($news['excerpt'] ?? '', 0, 100)) . '...'; ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($news['published_at'])); ?>
                                    <span class="badge bg-danger ms-2"><?php echo htmlspecialchars($news['category_name']); ?></span>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No news with videos found. <a href="admin/add-news.php">Add a news article with a video</a> to test.
            </div>
        <?php endif; ?>
        
        <div class="mt-5">
            <h3>Test Instructions:</h3>
            <ul>
                <li>Click on any video thumbnail to open the video lightbox</li>
                <li>Use arrow keys or navigation buttons to browse through videos</li>
                <li>Press ESC or click the X button to close the lightbox</li>
                <li>Test video sharing functionality on individual news pages</li>
                <li>Check that video thumbnails display correctly on index and category pages</li>
            </ul>
            
            <div class="mt-3">
                <a href="index.php" class="btn btn-primary me-2">
                    <i class="fas fa-home me-1"></i>Go to Homepage
                </a>
                <a href="category.php" class="btn btn-outline-primary">
                    <i class="fas fa-tags me-1"></i>Browse Categories
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/image-lightbox.js"></script>
    <script src="assets/js/video-lightbox.js"></script>
</body>
</html>
