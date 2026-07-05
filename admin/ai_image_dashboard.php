<?php
/**
 * AI Image Dashboard
 * Overview of AI image generation statistics and recent activity
 */

// Get statistics
$statsQuery = "SELECT 
    COUNT(*) as total_news,
    SUM(CASE WHEN image_type = 'ai' THEN 1 ELSE 0 END) as ai_images,
    SUM(CASE WHEN ai_image_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN ai_image_status = 'generating' THEN 1 ELSE 0 END) as generating,
    SUM(CASE WHEN ai_image_status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN ai_image_status = 'failed' THEN 1 ELSE 0 END) as failed,
    SUM(CASE WHEN ai_image_status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN image_type = 'rss' AND image IS NULL THEN 1 ELSE 0 END) as missing_rss_images
    FROM news";
    
$stats = mysqli_fetch_assoc(mysqli_query($conn, $statsQuery));

// Get recent AI image generations
$recentQuery = "SELECT n.*, c.name as category_name 
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                WHERE n.image_type = 'ai' 
                ORDER BY n.image_generated_at DESC 
                LIMIT 10";
$recentImages = mysqli_query($conn, $recentQuery);

// Get provider statistics
$providerQuery = "SELECT image_provider, COUNT(*) as count 
                  FROM news 
                  WHERE image_type = 'ai' AND image_provider IS NOT NULL 
                  GROUP BY image_provider";
$providerStats = mysqli_query($conn, $providerQuery);

// Get daily generation trends (last 7 days)
$trendQuery = "SELECT DATE(image_generated_at) as date, COUNT(*) as count 
               FROM news 
               WHERE image_generated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
               GROUP BY DATE(image_generated_at) 
               ORDER BY date DESC";
$dailyTrends = mysqli_query($conn, $trendQuery);
?>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total AI Images
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['ai_images']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-image fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Generation
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['pending']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Completed
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['completed']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Failed
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['failed']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="?action=queue&filter=pending" class="btn btn-warning btn-block">
                            <i class="fas fa-clock"></i> Process Pending (<?php echo $stats['pending']; ?>)
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="?action=queue&filter=failed" class="btn btn-danger btn-block">
                            <i class="fas fa-exclamation-triangle"></i> Review Failed (<?php echo $stats['failed']; ?>)
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="?action=queue&filter=missing_rss" class="btn btn-info btn-block">
                            <i class="fas fa-image"></i> Generate for RSS (<?php echo $stats['missing_rss_images']; ?>)
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-success btn-block" onclick="bulkGenerate()">
                            <i class="fas fa-magic"></i> Bulk Generate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent AI Images -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent AI Generated Images</h6>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($recentImages) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="recentImagesTable">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Provider</th>
                                    <th>Status</th>
                                    <th>Generated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($image = mysqli_fetch_assoc($recentImages)): ?>
                                    <tr>
                                        <td>
                                            <?php if ($image['image']): ?>
                                                <img src="../<?php echo htmlspecialchars($image['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($image['title']); ?>" 
                                                     class="img-thumbnail" style="max-width: 80px; max-height: 60px;">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="../news.php?id=<?php echo $image['id']; ?>" target="_blank">
                                                <?php echo htmlspecialchars(substr($image['title'], 0, 50)); ?>...
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($image['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($image['image_provider'] ?? 'Unknown'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'secondary',
                                                'generating' => 'warning',
                                                'completed' => 'success',
                                                'failed' => 'danger',
                                                'approved' => 'primary',
                                                'rejected' => 'dark'
                                            ];
                                            $status = $image['ai_image_status'] ?? 'pending';
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass[$status]; ?>">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($image['image_generated_at']): ?>
                                                <?php echo date('M j, H:i', strtotime($image['image_generated_at'])); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?action=edit&news_id=<?php echo $image['id']; ?>" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-success" 
                                                        onclick="regenerateImage(<?php echo $image['id']; ?>)" 
                                                        title="Regenerate">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                                <?php if ($status === 'completed'): ?>
                                                    <button class="btn btn-outline-info" 
                                                            onclick="approveImage(<?php echo $image['id']; ?>)" 
                                                            title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No AI generated images yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Provider Statistics -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Provider Usage</h6>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($providerStats) > 0): ?>
                    <?php while ($provider = mysqli_fetch_assoc($providerStats)): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><?php echo htmlspecialchars($provider['image_provider']); ?></span>
                                <span class="badge bg-primary"><?php echo $provider['count']; ?></span>
                            </div>
                            <div class="progress mt-1">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo ($provider['count'] / $stats['ai_images']) * 100; ?>%"
                                     aria-valuenow="<?php echo $provider['count']; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="<?php echo $stats['ai_images']; ?>">
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No provider data available.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Daily Trends -->
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">7-Day Trend</h6>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($dailyTrends) > 0): ?>
                    <?php while ($trend = mysqli_fetch_assoc($dailyTrends)): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?php echo date('M j', strtotime($trend['date'])); ?></span>
                            <span class="badge bg-info"><?php echo $trend['count']; ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No trend data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for interactions -->
<script>
function regenerateImage(newsId) {
    if (confirm('Are you sure you want to regenerate this image?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="form_type" value="regenerate_image">
            <input type="hidden" name="news_id" value="${newsId}">
            <input type="hidden" name="provider" value="openai">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function approveImage(newsId) {
    if (confirm('Approve this AI generated image?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="form_type" value="approve_image">
            <input type="hidden" name="news_id" value="${newsId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function bulkGenerate() {
    const count = prompt('How many images would you like to generate? (Max 50)', '10');
    if (count && !isNaN(count) && count > 0) {
        window.location.href = `?action=queue&bulk_generate=${Math.min(count, 50)}`;
    }
}

// Initialize data table
$(document).ready(function() {
    $('#recentImagesTable').DataTable({
        'pageLength': 10,
        'order': [[5, 'desc']],
        'responsive': true
    });
});
</script>
