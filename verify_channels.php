<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Channel Verification</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
<div class='container mt-5'>
    <h2 class='text-center mb-4'>Current Live Channels in Database</h2>";

// Check if channels table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'channels'");
if ($table_check && mysqli_num_rows($table_check) > 0) {
    
    // Get all channels
    $channels = mysqli_query($conn, 'SELECT id, name, category, status, is_featured, language, country, viewer_count FROM channels ORDER BY sort_order ASC');
    
    if ($channels && mysqli_num_rows($channels) > 0) {
        echo "<div class='table-responsive'>
                <table class='table table-striped table-hover'>
                    <thead class='table-dark'>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Language</th>
                            <th>Country</th>
                            <th>Viewers</th>
                        </tr>
                    </thead>
                    <tbody>";
        
        while ($channel = mysqli_fetch_assoc($channels)) {
            $status_badge = $channel['status'] === 'live' ? 'bg-danger' : ($channel['status'] === 'scheduled' ? 'bg-warning' : 'bg-secondary');
            $featured_badge = $channel['is_featured'] ? 'bg-success' : 'bg-light text-dark';
            
            echo "<tr>
                    <td>" . $channel['id'] . "</td>
                    <td><strong>" . htmlspecialchars($channel['name']) . "</strong></td>
                    <td>" . ucfirst($channel['category']) . "</td>
                    <td><span class='badge $status_badge'>" . strtoupper($channel['status']) . "</span></td>
                    <td><span class='badge $featured_badge'>" . ($channel['is_featured'] ? 'YES' : 'NO') . "</span></td>
                    <td>" . strtoupper($channel['language']) . "</td>
                    <td>" . $channel['country'] . "</td>
                    <td>" . number_format($channel['viewer_count']) . "</td>
                </tr>";
        }
        
        echo "</tbody></table></div>";
        
        // Show statistics
        $total_channels = mysqli_num_rows($channels);
        mysqli_data_seek($channels, 0);
        
        $live_count = 0;
        $featured_count = 0;
        $news_count = 0;
        $sports_count = 0;
        $entertainment_count = 0;
        
        while ($channel = mysqli_fetch_assoc($channels)) {
            if ($channel['status'] === 'live') $live_count++;
            if ($channel['is_featured']) $featured_count++;
            if ($channel['category'] === 'news') $news_count++;
            if ($channel['category'] === 'sports') $sports_count++;
            if ($channel['category'] === 'entertainment') $entertainment_count++;
        }
        
        echo "<div class='row mt-4'>
                <div class='col-md-3'>
                    <div class='card bg-primary text-white'>
                        <div class='card-body text-center'>
                            <h4>$total_channels</h4>
                            <p>Total Channels</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-3'>
                    <div class='card bg-danger text-white'>
                        <div class='card-body text-center'>
                            <h4>$live_count</h4>
                            <p>Live Now</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-3'>
                    <div class='card bg-success text-white'>
                        <div class='card-body text-center'>
                            <h4>$featured_count</h4>
                            <p>Featured</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-3'>
                    <div class='card bg-info text-white'>
                        <div class='card-body text-center'>
                            <h4>$news_count</h4>
                            <p>News Channels</p>
                        </div>
                    </div>
                </div>
              </div>";
        
        echo "<div class='row mt-3'>
                <div class='col-md-4'>
                    <div class='card bg-success text-white'>
                        <div class='card-body text-center'>
                            <h4>$sports_count</h4>
                            <p>Sports Channels</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='card bg-warning text-white'>
                        <div class='card-body text-center'>
                            <h4>$entertainment_count</h4>
                            <p>Entertainment</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='card bg-secondary text-white'>
                        <div class='card-body text-center'>
                            <h4>" . ($total_channels - $news_count - $sports_count - $entertainment_count) . "</h4>
                            <p>Other Categories</p>
                        </div>
                    </div>
                </div>
              </div>";
        
    } else {
        echo "<div class='alert alert-warning'>No channels found in the database.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Channels table does not exist!</div>";
}

echo "<div class='text-center mt-4'>
        <a href='live.php' class='btn btn-danger btn-lg me-2'>View Live TV Page</a>
        <a href='add_channels_now.php' class='btn btn-primary btn-lg me-2'>Add More Channels</a>
        <a href='index.php' class='btn btn-secondary btn-lg'>Back to Home</a>
      </div>";

echo "</div></body></html>";
?>
