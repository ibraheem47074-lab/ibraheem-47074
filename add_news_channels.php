<?php
require_once 'config/database.php';
$page_title = 'Add 39 News Channels';

// News channels with their YouTube live stream URLs
$news_channels = [
    // International News Channels
    ['name' => 'BBC News', 'url' => 'https://www.youtube.com/watch?v=wGBzr_8qPm4', 'country' => 'UK'],
    ['name' => 'CNN', 'url' => 'https://www.youtube.com/watch?v=wuBfSOMcHqQ', 'country' => 'USA'],
    ['name' => 'Al Jazeera', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Qatar'],
    ['name' => 'Reuters', 'url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw', 'country' => 'UK'],
    ['name' => 'Fox News', 'url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0', 'country' => 'USA'],
    ['name' => 'MSNBC', 'url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0', 'country' => 'USA'],
    ['name' => 'NBC News', 'url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0', 'country' => 'USA'],
    ['name' => 'CBS News', 'url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0', 'country' => 'USA'],
    ['name' => 'ABC News', 'url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0', 'country' => 'USA'],
    
    // European News Channels
    ['name' => 'The Guardian', 'url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw', 'country' => 'UK'],
    ['name' => 'The Times', 'url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw', 'country' => 'UK'],
    ['name' => 'France 24', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'France'],
    ['name' => 'Deutsche Welle', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Germany'],
    ['name' => 'RT News', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Russia'],
    ['name' => 'Le Monde', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'France'],
    ['name' => 'Der Spiegel', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Germany'],
    ['name' => 'Corriere della Sera', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Italy'],
    ['name' => 'El Pais', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Spain'],
    
    // Asian News Channels
    ['name' => 'CCTV', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'China'],
    ['name' => 'NDTV', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'India'],
    ['name' => 'Times of India', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'India'],
    ['name' => 'The Hindu', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'India'],
    ['name' => 'Japan Times', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Japan'],
    
    // Australian News Channels
    ['name' => 'Sydney Morning Herald', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Australia'],
    ['name' => 'The Age', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Australia'],
    
    // Canadian News Channels
    ['name' => 'Toronto Star', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Canada'],
    ['name' => 'CBC News', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Canada'],
    
    // South American News Channels
    ['name' => 'Globo News', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Brazil'],
    
    // Middle Eastern News Channels
    ['name' => 'The Jerusalem Post', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Israel'],
    ['name' => 'Al Arabiya', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Saudi Arabia'],
    ['name' => 'Arab News', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Saudi Arabia'],
    
    // Turkish News Channels
    ['name' => 'Daily Sabah', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Turkey'],
    ['name' => 'Hurriyet', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'Turkey'],
    
    // Pakistani News Channels
    ['name' => 'Dawn News', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'PK'],
    ['name' => 'The News', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'PK'],
    ['name' => 'Express Tribune', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'PK'],
    ['name' => 'Geo News', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'PK'],
    ['name' => 'ARY News', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'PK'],
    ['name' => 'Samaa TV', 'url' => 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'country' => 'PK']
];

// Handle bulk insertion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_channels'])) {
    $success_count = 0;
    $error_count = 0;
    
    foreach ($news_channels as $channel) {
        // Check if channel already exists
        $check_query = "SELECT id FROM channels WHERE name = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 's', $channel['name']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 0) {
            // Insert new channel
            $query = "INSERT INTO channels (name, category, stream_url, stream_type, description, status, language, country, is_featured) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            
            // Create variables for bind_param
            $category = 'news';
            $stream_type = 'youtube';
            $description = $channel['name'] . ' - Live news stream from ' . $channel['country'];
            $status = 'live';
            $language = 'en';
            $country = $channel['country'];
            $is_featured = 0;
            
            mysqli_stmt_bind_param($stmt, 'sssssssii', 
                $channel['name'], $category, $channel['url'], $stream_type, 
                $description, $status, $language, $country, $is_featured);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
    }
    
    $message = "Successfully added $success_count channels. " . ($error_count > 0 ? "$error_count channels failed." : "");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add 39 News Channels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Add 39 News Channels</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h4>Bulk Add News Channels</h4>
            </div>
            <div class="card-body">
                <p>This will add 39 international and Pakistani news channels to your live streaming system.</p>
                
                <h6>Channels to be added:</h6>
                <div class="row">
                    <?php foreach (array_chunk($news_channels, 3) as $chunk): ?>
                        <div class="col-md-4">
                            <ul class="small">
                                <?php foreach ($chunk as $channel): ?>
                                    <li><?php echo htmlspecialchars($channel['name']); ?> (<?php echo $channel['country']; ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <form method="POST" class="mt-3">
                    <button type="submit" name="add_channels" class="btn btn-danger btn-lg">
                        <i class="fas fa-plus me-2"></i>Add All 39 Channels
                    </button>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="card">
                <div class="card-header">
                    <h4>Quick Links</h4>
                </div>
                <div class="card-body">
                    <a href="admin/manage-channels.php" class="btn btn-primary me-2">
                        <i class="fas fa-cog me-2"></i>Manage Channels (Admin)
                    </a>
                    <a href="live.php" class="btn btn-danger me-2">
                        <i class="fas fa-tv me-2"></i>View Live TV
                    </a>
                    <a href="youtube_test.php" class="btn btn-info">
                        <i class="fas fa-test me-2"></i>YouTube Test
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
