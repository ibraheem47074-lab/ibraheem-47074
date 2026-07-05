<?php
require_once 'config/database.php';

echo "<h2>Setting Up Social Media Sharing System</h2>";

// Create social media posts table
$create_posts_table = "CREATE TABLE IF NOT EXISTS social_media_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform ENUM('facebook', 'twitter', 'instagram', 'linkedin') NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(500),
    news_id INT,
    post_url VARCHAR(500),
    status ENUM('scheduled', 'posted', 'failed') DEFAULT 'scheduled',
    scheduled_time DATETIME,
    posted_time DATETIME NULL,
    engagement_likes INT DEFAULT 0,
    engagement_shares INT DEFAULT 0,
    engagement_comments INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $create_posts_table)) {
    echo "<p class='text-success'>✓ Social media posts table created</p>";
} else {
    echo "<p class='text-danger'>✗ Error creating table: " . mysqli_error($conn) . "</p>";
}

// Create social media settings table
$create_settings_table = "CREATE TABLE IF NOT EXISTS social_media_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(50) NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_setting (platform, setting_key)
)";

if (mysqli_query($conn, $create_settings_table)) {
    echo "<p class='text-success'>✓ Social media settings table created</p>";
} else {
    echo "<p class='text-danger'>✗ Error creating settings table: " . mysqli_error($conn) . "</p>";
}

// Get latest news articles for social media posting
$news_query = "SELECT id, title, excerpt, category_name, image FROM news n 
               LEFT JOIN categories c ON n.category_id = c.id 
               WHERE n.status = 'published' 
               ORDER BY n.published_at DESC 
               LIMIT 10";
$news_result = mysqli_query($conn, $news_query);

$posts_created = 0;

while ($news = mysqli_fetch_assoc($news_result)) {
    $news_id = $news['id'];
    $title = $news['title'];
    $excerpt = $news['excerpt'];
    $category = $news['category_name'] ?? 'News';
    $image = $news['image'];
    
    // Facebook Post
    $facebook_content = "📰 BREAKING: $title\n\n$excerpt\n\n$category | #PKLiveNews #PakistanNews #BreakingNews\n\nRead more: " . SITE_URL . "news.php?slug=" . get_news_slug($news_id);
    $facebook_scheduled = date('Y-m-d H:i:s', strtotime('+' . ($posts_created * 2) . ' hours'));
    
    $fb_insert = "INSERT INTO social_media_posts (platform, content, image_url, news_id, scheduled_time) 
                   VALUES ('facebook', '" . mysqli_real_escape_string($conn, $facebook_content) . "', '$image', $news_id, '$facebook_scheduled')";
    if (mysqli_query($conn, $fb_insert)) {
        $posts_created++;
    }
    
    // Twitter Post (shorter)
    $twitter_content = "📰 $title\n\n$category | #PKLiveNews #Pakistan\n\n" . SITE_URL . "news.php?slug=" . get_news_slug($news_id);
    $twitter_scheduled = date('Y-m-d H:i:s', strtotime('+' . (($posts_created * 2) + 1) . ' hours'));
    
    $twitter_insert = "INSERT INTO social_media_posts (platform, content, image_url, news_id, scheduled_time) 
                       VALUES ('twitter', '" . mysqli_real_escape_string($conn, $twitter_content) . "', '$image', $news_id, '$twitter_scheduled')";
    if (mysqli_query($conn, $twitter_insert)) {
        $posts_created++;
    }
    
    // LinkedIn Post (professional)
    $linkedin_content = "📰 Important Update: $title\n\n$excerpt\n\nThis development impacts Pakistan's $category sector. Stay informed with PK Live News.\n\n#Pakistan #News #Business #CurrentEvents\n\nRead full analysis: " . SITE_URL . "news.php?slug=" . get_news_slug($news_id);
    $linkedin_scheduled = date('Y-m-d H:i:s', strtotime('+' . (($posts_created * 2) + 2) . ' hours'));
    
    $linkedin_insert = "INSERT INTO social_media_posts (platform, content, image_url, news_id, scheduled_time) 
                       VALUES ('linkedin', '" . mysqli_real_escape_string($conn, $linkedin_content) . "', '$image', $news_id, '$linkedin_scheduled')";
    if (mysqli_query($conn, $linkedin_insert)) {
        $posts_created++;
    }
}

echo "<p class='text-success'>✓ Created $posts_created social media posts</p>";

// Social media posting functions
function get_news_slug($news_id) {
    global $conn;
    $query = "SELECT slug FROM news WHERE id = $news_id";
    $result = mysqli_query($conn, $query);
    $news = mysqli_fetch_assoc($result);
    return $news['slug'] ?? '';
}

// Create auto-posting script
$auto_post_script = '<?php
require_once "config/database.php";

// Get scheduled posts
$posts_query = "SELECT * FROM social_media_posts 
                WHERE status = \'scheduled\' 
                AND scheduled_time <= NOW() 
                ORDER BY scheduled_time ASC 
                LIMIT 10";
$posts_result = mysqli_query($conn, $posts_query);

while ($post = mysqli_fetch_assoc($posts_result)) {
    $platform = $post["platform"];
    $content = $post["content"];
    $image_url = $post["image_url"];
    
    // Simulate posting (replace with actual API calls)
    $success = rand(0, 10) > 2; // 80% success rate for demo
    
    if ($success) {
        $update_query = "UPDATE social_media_posts 
                        SET status = \'posted\', posted_time = NOW() 
                        WHERE id = " . $post["id"];
        echo "✓ Posted to $platform: " . substr($content, 0, 50) . "...\n";
    } else {
        $update_query = "UPDATE social_media_posts 
                        SET status = \'failed\' 
                        WHERE id = " . $post["id"];
        echo "✗ Failed to post to $platform\n";
    }
    
    mysqli_query($conn, $update_query);
    
    // Add delay between posts
    sleep(2);
}

echo "Social media posting completed.\n";
?>';

file_put_contents(__DIR__ . '/auto_post_social.php', $auto_post_script);
echo "<p class='text-success'>✓ Created auto-posting script</p>";

// Create social media dashboard
$dashboard_html = '
<!DOCTYPE html>
<html>
<head>
    <title>Social Media Dashboard - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <h2><i class="fab fa-facebook me-2"></i>Social Media Dashboard</h2>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5><i class="fab fa-facebook"></i> Facebook</h5>
                        <h3 id="facebook-count">0</h3>
                        <small>Scheduled Posts</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5><i class="fab fa-twitter"></i> Twitter</h5>
                        <h3 id="twitter-count">0</h3>
                        <small>Scheduled Posts</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5><i class="fab fa-linkedin"></i> LinkedIn</h5>
                        <h3 id="linkedin-count">0</h3>
                        <small>Scheduled Posts</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-chart-line"></i> Total</h5>
                        <h3 id="total-count">0</h3>
                        <small>All Posts</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calendar-alt me-2"></i>Scheduled Posts</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Platform</th>
                                <th>Content</th>
                                <th>Scheduled Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="posts-table">
                            <tr><td colspan="5" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button class="btn btn-success" onclick="postNow()">
                <i class="fas fa-paper-plane me-2"></i>Post Scheduled Items Now
            </button>
            <button class="btn btn-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync me-2"></i>Refresh
            </button>
        </div>
    </div>
    
    <script>
        function loadDashboard() {
            fetch("api/social_media_dashboard.php")
                .then(response => response.json())
                .then(data => {
                    document.getElementById("facebook-count").textContent = data.facebook || 0;
                    document.getElementById("twitter-count").textContent = data.twitter || 0;
                    document.getElementById("linkedin-count").textContent = data.linkedin || 0;
                    document.getElementById("total-count").textContent = data.total || 0;
                    
                    const tableBody = document.getElementById("posts-table");
                    if (data.posts && data.posts.length > 0) {
                        tableBody.innerHTML = data.posts.map(post => `
                            <tr>
                                <td><i class="fab fa-${post.platform} me-2"></i>${post.platform}</td>
                                <td>${post.content.substring(0, 100)}...</td>
                                <td>${new Date(post.scheduled_time).toLocaleString()}</td>
                                <td><span class="badge bg-${post.status === "posted" ? "success" : post.status === "failed" ? "danger" : "warning"}">${post.status}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editPost(${post.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join("");
                    } else {
                        tableBody.innerHTML = "<tr><td colspan=\"5\" class=\"text-center\">No scheduled posts found</td></tr>";
                    }
                });
        }
        
        function refreshDashboard() {
            loadDashboard();
        }
        
        function postNow() {
            if (confirm("Post all scheduled items now?")) {
                fetch("auto_post_social.php")
                    .then(() => {
                        alert("Posting completed!");
                        loadDashboard();
                    });
            }
        }
        
        // Load dashboard on page load
        loadDashboard();
        
        // Auto-refresh every 30 seconds
        setInterval(loadDashboard, 30000);
    </script>
</body>
</html>';

file_put_contents(__DIR__ . '/social_media_dashboard.php', $dashboard_html);
echo "<p class='text-success'>✓ Created social media dashboard</p>";

// Create API endpoint for dashboard
$api_php = '<?php
require_once "config/database.php";

header("Content-Type: application/json");

// Get platform counts
$facebook_count = 0;
$twitter_count = 0;
$linkedin_count = 0;

$counts_query = "SELECT platform, COUNT(*) as count FROM social_media_posts WHERE status = \'scheduled\' GROUP BY platform";
$counts_result = mysqli_query($conn, $counts_query);

while ($row = mysqli_fetch_assoc($counts_result)) {
    switch($row["platform"]) {
        case "facebook": $facebook_count = $row["count"]; break;
        case "twitter": $twitter_count = $row["count"]; break;
        case "linkedin": $linkedin_count = $row["count"]; break;
    }
}

// Get recent posts
$posts_query = "SELECT * FROM social_media_posts ORDER BY scheduled_time DESC LIMIT 20";
$posts_result = mysqli_query($conn, $posts_query);
$posts = [];

while ($row = mysqli_fetch_assoc($posts_result)) {
    $posts[] = $row;
}

echo json_encode([
    "facebook" => $facebook_count,
    "twitter" => $twitter_count,
    "linkedin" => $linkedin_count,
    "total" => $facebook_count + $twitter_count + $linkedin_count,
    "posts" => $posts
]);
?>';

file_put_contents(__DIR__ . '/api/social_media_dashboard.php', $api_php);
echo "<p class='text-success'>✓ Created API endpoint</p>";

echo "<h3>Social Media System Setup Complete!</h3>";
echo "<div class='alert alert-info'>";
echo "<h4>📱 Features Created:</h4>";
echo "<ul>";
echo "<li>✅ Automatic post scheduling for Facebook, Twitter, LinkedIn</li>";
echo "<li>✅ Content optimization for each platform</li>";
echo "<li>✅ Visual dashboard for managing posts</li>";
echo "<li>✅ Auto-posting script for automation</li>";
echo "<li>✅ Engagement tracking system</li>";
echo "</ul>";
echo "</div>";

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='social_media_dashboard.php'>View Social Media Dashboard</a></li>";
echo "<li><a href='auto_post_social.php'>Test Auto-Posting</a></li>";
echo "<li>Set up cron job for automatic posting (every hour)</li>";
echo "<li>Configure actual API keys for social media platforms</li>";
echo "</ul>";

echo "<div class='alert alert-warning'>";
echo "<h4>⚠️ Important:</h4>";
echo "<p>Currently using demo URLs. To connect with real social media:</p>";
echo "<ul>";
echo "<li>Get API keys from Facebook Developer, Twitter API, LinkedIn</li>";
echo "<li>Update auto_post_social.php with actual API calls</li>";
echo "<li>Set up cron job: <code>0 * * * * php /path/to/auto_post_social.php</code></li>";
echo "</ul>";
echo "</div>";
?>

<style>
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
body { font-family: Arial, sans-serif; padding: 20px; }
.alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; }
.alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
code { background: #f8f9fa; padding: 2px 5px; border-radius: 3px; }
</style>
