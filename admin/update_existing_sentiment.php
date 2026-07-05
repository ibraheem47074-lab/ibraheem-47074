<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$page_title = 'Update Existing Sentiment';

// Handle sentiment update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_sentiment'])) {
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 50;
    $batch_size = isset($_POST['batch_size']) ? (int)$_POST['batch_size'] : 10;
    
    // Get articles without sentiment analysis
    $query = "SELECT id, title, content FROM news 
              WHERE (sentiment_score IS NULL OR sentiment_score = 0) 
              AND status = 'published' 
              ORDER BY created_at DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $updated_count = 0;
    $error_count = 0;
    
    while ($article = mysqli_fetch_assoc($result)) {
        // Simple sentiment analysis (you can integrate with AI service here)
        $sentiment = analyzeSentiment($article['title'] . ' ' . $article['content']);
        
        // Update the article with sentiment data
        $update_query = "UPDATE news SET 
                        sentiment_score = ?, 
                        sentiment_label = ? 
                        WHERE id = ?";
        
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'dsi', $sentiment['score'], $sentiment['label'], $article['id']);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $updated_count++;
        } else {
            $error_count++;
        }
    }
    
    $success = "Sentiment analysis completed! Updated: $updated_count articles, Errors: $error_count";
}

// Simple sentiment analysis function
function analyzeSentiment($text) {
    // Basic sentiment keywords (you can enhance this with AI)
    $positive_words = ['good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'positive', 'success', 'happy', 'joy'];
    $negative_words = ['bad', 'terrible', 'awful', 'horrible', 'negative', 'failure', 'sad', 'angry', 'poor', 'worst'];
    
    $text = strtolower($text);
    $positive_count = 0;
    $negative_count = 0;
    
    foreach ($positive_words as $word) {
        $positive_count += substr_count($text, $word);
    }
    
    foreach ($negative_words as $word) {
        $negative_count += substr_count($text, $word);
    }
    
    $total_words = $positive_count + $negative_count;
    
    if ($total_words == 0) {
        return ['score' => 0.00, 'label' => 'neutral'];
    }
    
    $score = (($positive_count - $negative_count) / $total_words) * 100;
    $score = max(-100, min(100, $score)); // Clamp between -100 and 100
    
    if ($score > 10) {
        $label = 'positive';
    } elseif ($score < -10) {
        $label = 'negative';
    } else {
        $label = 'neutral';
    }
    
    return ['score' => number_format($score, 2), 'label' => $label];
}

// Get statistics
$stats_query = "SELECT 
                 COUNT(*) as total_articles,
                 SUM(CASE WHEN sentiment_score IS NULL OR sentiment_score = 0 THEN 1 ELSE 0 END) as without_sentiment,
                 SUM(CASE WHEN sentiment_score > 0 THEN 1 ELSE 0 END) as positive_count,
                 SUM(CASE WHEN sentiment_score < 0 THEN 1 ELSE 0 END) as negative_count,
                 SUM(CASE WHEN sentiment_score = 0 THEN 1 ELSE 0 END) as neutral_count
               FROM news WHERE status = 'published'";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-brain me-2"></i>Update Existing Sentiment</h2>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Articles</h5>
                                <h3><?php echo number_format($stats['total_articles']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Without Sentiment</h5>
                                <h3><?php echo number_format($stats['without_sentiment']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Positive</h5>
                                <h3><?php echo number_format($stats['positive_count']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Negative</h5>
                                <h3><?php echo number_format($stats['negative_count']); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Update Form -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-cogs me-2"></i>Sentiment Analysis Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="limit" class="form-label">Number of Articles to Process</label>
                                        <input type="number" class="form-control" id="limit" name="limit" value="50" min="1" max="1000">
                                        <div class="form-text">Maximum number of articles to analyze in this batch</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="batch_size" class="form-label">Batch Size</label>
                                        <input type="number" class="form-control" id="batch_size" name="batch_size" value="10" min="1" max="50">
                                        <div class="form-text">Number of articles to process at once</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> This will analyze articles that don't have sentiment data using a basic keyword-based approach. 
                                For better results, consider integrating with an AI sentiment analysis service.
                            </div>
                            
                            <button type="submit" name="update_sentiment" class="btn btn-primary">
                                <i class="fas fa-play me-2"></i>Start Sentiment Analysis
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Progress Info -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line me-2"></i>Analysis Progress</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3">
                            <?php 
                            $progress = $stats['total_articles'] > 0 ? 
                                (($stats['total_articles'] - $stats['without_sentiment']) / $stats['total_articles']) * 100 : 0;
                            ?>
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%">
                                <?php echo round($progress, 1); ?>% Complete
                            </div>
                        </div>
                        <p class="text-muted">
                            <strong><?php echo number_format($stats['total_articles'] - $stats['without_sentiment']); ?></strong> of 
                            <strong><?php echo number_format($stats['total_articles']); ?></strong> articles have been analyzed.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
