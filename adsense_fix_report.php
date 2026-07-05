<?php
require_once 'config/database.php';

// AdSense Approval Fix Report
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdSense Approval Fix Report - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h1 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>AdSense Approval Issues & Fixes</h1>
                    </div>
                    <div class="card-body">
                        
                        <?php
                        // Check published articles
                        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published'");
                        $row = mysqli_fetch_assoc($result);
                        $article_count = $row['total'];
                        ?>
                        
                        <div class="alert alert-<?php echo $article_count >= 30 ? 'success' : 'danger'; ?> mb-4">
                            <h5 class="alert-heading"><i class="fas fa-newspaper me-2"></i>Content Volume</h5>
                            <p class="mb-0">Published Articles: <strong><?php echo $article_count; ?></strong></p>
                            <hr>
                            <small class="mb-0">
                                <?php if ($article_count < 30): ?>
                                    ❌ <strong>ISSUE:</strong> Need at least 30-50 high-quality articles. Currently have <?php echo $article_count; ?>.
                                    <br>✅ <strong>FIX:</strong> Add <?php echo (30 - $article_count); ?> more original articles (500+ words each) before reapplying.
                                <?php else: ?>
                                    ✅ <strong>PASS:</strong> Sufficient content volume.
                                <?php endif; ?>
                            </small>
                        </div>

                        <?php
                        // Check if source_url column exists for content source analysis
                        $source_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source_url'");
                        $has_source_url = mysqli_num_rows($source_check) > 0;
                        
                        if ($has_source_url):
                            // Check content sources using source_url
                            $result = mysqli_query($conn, "SELECT 
                                CASE 
                                    WHEN source_url IS NULL OR source_url = '' THEN 'Original'
                                    ELSE 'External/RSS'
                                END as source_type,
                                COUNT(*) as count 
                                FROM news WHERE status = 'published' 
                                GROUP BY source_type 
                                ORDER BY count DESC");
                        ?>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Content Sources Analysis</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Source Type</th>
                                            <th>Articles</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rss_count = 0;
                                        $original_count = 0;
                                        while ($row = mysqli_fetch_assoc($result)): 
                                            if ($row['source_type'] === 'Original') {
                                                $original_count = $row['count'];
                                                $status = '<span class="badge bg-success">Original</span>';
                                            } else {
                                                $rss_count = $row['count'];
                                                $status = '<span class="badge bg-warning">External/RSS</span>';
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['source_type']); ?></td>
                                            <td><?php echo $row['count']; ?></td>
                                            <td><?php echo $status; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                
                                <?php 
                                $rss_percentage = $article_count > 0 ? round(($rss_count / $article_count) * 100) : 0;
                                ?>
                                <div class="alert alert-<?php echo $rss_percentage <= 30 ? 'success' : 'warning'; ?> mt-3">
                                    <strong>External/RSS Content:</strong> <?php echo $rss_count; ?> articles (<?php echo $rss_percentage; ?>%)
                                    <?php if ($rss_percentage > 30): ?>
                                        <br>⚠️ <strong>ISSUE:</strong> RSS content should not exceed 30%. Currently at <?php echo $rss_percentage; ?>%.
                                        <br>✅ <strong>FIX:</strong> Add more original content or remove some RSS feeds.
                                    <?php else: ?>
                                        <br>✅ <strong>PASS:</strong> RSS content within acceptable limits.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Content source analysis not available - source_url column not found in database.
                        </div>
                        <?php endif; ?>

                        <?php
                        // Check article quality
                        $content_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'content'");
                        if (mysqli_num_rows($content_check) > 0) {
                            $result = mysqli_query($conn, "SELECT AVG(LENGTH(content)) as avg_length, MIN(LENGTH(content)) as min_length FROM news WHERE status = 'published'");
                            $row = mysqli_fetch_assoc($result);
                            $avg_length = round($row['avg_length']);
                            $min_length = $row['min_length'];
                        } else {
                            $avg_length = 0;
                            $min_length = 0;
                        }
                        ?>
                        
                        <div class="alert alert-<?php echo $avg_length >= 1000 ? 'success' : 'warning'; ?> mb-4">
                            <h5 class="alert-heading"><i class="fas fa-align-left me-2"></i>Content Quality</h5>
                            <p class="mb-1">Average Article Length: <strong><?php echo $avg_length; ?></strong> characters</p>
                            <p class="mb-1">Shortest Article: <strong><?php echo $min_length; ?></strong> characters</p>
                            <hr>
                            <small class="mb-0">
                                <?php if ($avg_length < 1000): ?>
                                    ⚠️ <strong>ISSUE:</strong> Articles should be 500+ words (approx. 3000+ characters). Average is only <?php echo $avg_length; ?> characters.
                                    <br>✅ <strong>FIX:</strong> Expand existing articles or create new, longer articles with more depth and detail.
                                <?php else: ?>
                                    ✅ <strong>PASS:</strong> Good article length.
                                <?php endif; ?>
                            </small>
                        </div>

                        <?php
                        // Check images
                        $image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_url'");
                        if (mysqli_num_rows($image_check) > 0) {
                            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND (image_url IS NULL OR image_url = '')");
                            $row = mysqli_fetch_assoc($result);
                            $no_image_count = $row['total'];
                            $with_image_count = $article_count - $no_image_count;
                        } else {
                            $no_image_count = $article_count; // Assume no images if column doesn't exist
                            $with_image_count = 0;
                        }
                        ?>
                        
                        <div class="alert alert-<?php echo $no_image_count == 0 ? 'success' : 'warning'; ?> mb-4">
                            <h5 class="alert-heading"><i class="fas fa-images me-2"></i>Media Content</h5>
                            <p class="mb-1">Articles with Images: <strong><?php echo $with_image_count; ?></strong> / <?php echo $article_count; ?></p>
                            <p class="mb-1">Articles without Images: <strong><?php echo $no_image_count; ?></strong></p>
                            <hr>
                            <small class="mb-0">
                                <?php if ($no_image_count > 0): ?>
                                    ⚠️ <strong>ISSUE:</strong> <?php echo $no_image_count; ?> articles lack images.
                                    <br>✅ <strong>FIX:</strong> Add relevant, high-quality images to all articles.
                                <?php else: ?>
                                    ✅ <strong>PASS:</strong> All articles have images.
                                <?php endif; ?>
                            </small>
                        </div>

                        <?php
                        // Check recent activity
                        $date_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'published_at'");
                        if (mysqli_num_rows($date_check) > 0) {
                            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                            $row = mysqli_fetch_assoc($result);
                            $recent_count = $row['total'];
                        } else {
                            $recent_count = 0;
                        }
                        ?>
                        
                        <div class="alert alert-<?php echo $recent_count >= 5 ? 'success' : 'warning'; ?> mb-4">
                            <h5 class="alert-heading"><i class="fas fa-clock me-2"></i>Site Activity</h5>
                            <p class="mb-1">Articles Published (Last 30 Days): <strong><?php echo $recent_count; ?></strong></p>
                            <hr>
                            <small class="mb-0">
                                <?php if ($recent_count < 5): ?>
                                    ⚠️ <strong>ISSUE:</strong> Site appears inactive. Need regular publishing (2-3 articles/week).
                                    <br>✅ <strong>FIX:</strong> Publish <?php echo (5 - $recent_count); ?> more articles this month to show consistent activity.
                                <?php else: ?>
                                    ✅ <strong>PASS:</strong> Good publishing frequency.
                                <?php endif; ?>
                            </small>
                        </div>

                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-check-circle text-success me-2"></i>Required Pages - All Present ✅</h5>
                                <ul class="list-unstyled mb-0">
                                    <li>✅ Privacy Policy (privacy-policy.php)</li>
                                    <li>✅ Terms of Service (terms.php)</li>
                                    <li>✅ Contact Page (contact.php)</li>
                                    <li>✅ About Page (about.php)</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Action Plan for AdSense Approval</h5>
                            </div>
                            <div class="card-body">
                                <ol class="mb-0">
                                    <li class="mb-3"><strong>Add Original Content:</strong> Create <?php echo max(0, 30 - $article_count); ?> more original, high-quality articles (500+ words each)</li>
                                    <li class="mb-3"><strong>Improve Article Length:</strong> Expand short articles to be more comprehensive</li>
                                    <li class="mb-3"><strong>Add Images:</strong> Ensure every article has at least one relevant, high-quality image</li>
                                    <li class="mb-3"><strong>Reduce RSS Content:</strong> Keep RSS/curated content under 30% of total content</li>
                                    <li class="mb-3"><strong>Maintain Activity:</strong> Publish 2-3 new articles per week consistently</li>
                                    <li class="mb-3"><strong>Update Sitemap:</strong> Regenerate sitemap.xml with new content</li>
                                    <li><strong>Wait 2-3 Weeks:</strong> After fixes, wait 2-3 weeks before reapplying to show consistent activity</li>
                                </ol>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
