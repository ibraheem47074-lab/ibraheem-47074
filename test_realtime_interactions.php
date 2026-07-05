<?php
/**
 * Real-time Interactions Test and Demo
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>\n<html>\n<head>\n";
echo "<title>Real-time Interactions Demo</title>\n";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>\n";
echo "<link href='assets/css/realtime-interactions.css' rel='stylesheet'>\n";
echo "</head>\n<body>\n";

echo "<div class='container mt-5'>\n";
echo "<h1 class='text-center mb-5'>🚀 Real-time News Interactions Demo</h1>\n";

// Get a sample news article
$query = "SELECT id, title, slug, likes_count, share_count, comment_count, views FROM news ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && $news = mysqli_fetch_assoc($result)) {
    echo "<div class='row'>\n";
    echo "<div class='col-md-8 mx-auto'>\n";
    
    echo "<div class='card shadow-lg'>\n";
    echo "<div class='card-body'>\n";
    
    echo "<h3 class='card-title'>" . htmlspecialchars($news['title']) . "</h3>\n";
    echo "<p class='text-muted mb-4'>Testing real-time interactions for this article</p>\n";
    
    // Real-time interaction stats
    echo "<div class='interaction-stats my-4'>\n";
    echo "<div class='row text-center'>\n";
    echo "<div class='col-md-3 col-6 mb-3'>\n";
    echo "<div class='stat-item'>\n";
    echo "<button class='btn btn-outline-danger like-btn' onclick='toggleLike({$news['id']})' id='likeBtn-{$news['id']}'>\n";
    echo "<i class='fas fa-heart me-2'></i>\n";
    echo "<span class='like-count'>" . number_format($news['likes_count'] ?? 0) . "</span>\n";
    echo "</button>\n";
    echo "<small class='d-block text-muted mt-1'>Likes</small>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "<div class='col-md-3 col-6 mb-3'>\n";
    echo "<div class='stat-item'>\n";
    echo "<div class='stat-display'>\n";
    echo "<i class='fas fa-eye me-2 text-info'></i>\n";
    echo "<span class='view-count'>" . number_format($news['views'] ?? 0) . "</span>\n";
    echo "</div>\n";
    echo "<small class='d-block text-muted mt-1'>Views</small>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "<div class='col-md-3 col-6 mb-3'>\n";
    echo "<div class='stat-item'>\n";
    echo "<div class='stat-display'>\n";
    echo "<i class='fas fa-share me-2 text-success'></i>\n";
    echo "<span class='share-count'>" . number_format($news['share_count'] ?? 0) . "</span>\n";
    echo "</div>\n";
    echo "<small class='d-block text-muted mt-1'>Shares</small>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "<div class='col-md-3 col-6 mb-3'>\n";
    echo "<div class='stat-item'>\n";
    echo "<div class='stat-display'>\n";
    echo "<i class='fas fa-comments me-2 text-warning'></i>\n";
    echo "<span class='comment-count'>" . number_format($news['comment_count'] ?? 0) . "</span>\n";
    echo "</div>\n";
    echo "<small class='d-block text-muted mt-1'>Comments</small>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "<div class='real-time-indicator text-center mt-2'>\n";
    echo "<small class='text-muted'><i class='fas fa-sync-alt fa-spin me-1'></i> Updated in real-time</small>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    // Share buttons
    echo "<div class='share-section my-4'>\n";
    echo "<h5 class='mb-3'>Share this article</h5>\n";
    echo "<div class='share-buttons'>\n";
    echo "<a href='#' onclick='shareOnFacebook(\"#\", \"" . htmlspecialchars($news['title']) . "\")' class='share-btn share-facebook'>\n";
    echo "<i class='fab fa-facebook-f me-2'></i>Facebook\n";
    echo "</a>\n";
    echo "<a href='#' onclick='shareOnTwitter(\"#\", \"" . htmlspecialchars($news['title']) . "\")' class='share-btn share-twitter'>\n";
    echo "<i class='fab fa-twitter me-2'></i>Twitter\n";
    echo "</a>\n";
    echo "<a href='#' onclick='shareOnWhatsApp(\"#\", \"" . htmlspecialchars($news['title']) . "\")' class='share-btn share-whatsapp'>\n";
    echo "<i class='fab fa-whatsapp me-2'></i>WhatsApp\n";
    echo "</a>\n";
    echo "<button onclick='copyToClipboard(\"#\")' class='share-btn bg-secondary'>\n";
    echo "<i class='fas fa-link me-2'></i>Copy Link\n";
    echo "</button>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    // Test controls
    echo "<div class='test-controls my-4'>\n";
    echo "<h5 class='mb-3'>Test Controls</h5>\n";
    echo "<div class='row'>\n";
    echo "<div class='col-md-6'>\n";
    echo "<button class='btn btn-primary me-2' onclick='testStats()'>Test Stats Update</button>\n";
    echo "<button class='btn btn-info me-2' onclick='testLike()'>Test Like Toggle</button>\n";
    echo "<button class='btn btn-success me-2' onclick='testShare()'>Test Share</button>\n";
    echo "</div>\n";
    echo "<div class='col-md-6'>\n";
    echo "<button class='btn btn-warning me-2' onclick='simulateMultipleUsers()'>Simulate Activity</button>\n";
    echo "<button class='btn btn-secondary me-2' onclick='resetStats()'>Reset Stats</button>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    // Status display
    echo "<div class='status-display mt-4'>\n";
    echo "<h5>Status Log</h5>\n";
    echo "<div id='statusLog' class='border rounded p-3' style='height: 200px; overflow-y: auto; background: #f8f9fa;'>\n";
    echo "<small class='text-muted'>Ready for testing...</small>\n";
    echo "</div>\n";
    echo "</div>\n";
    
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    
} else {
    echo "<div class='alert alert-warning'>\n";
    echo "<h4>No News Articles Found</h4>\n";
    echo "<p>Please create some news articles first to test the real-time interactions.</p>\n";
    echo "<a href='admin/add-news.php' class='btn btn-primary'>Create News Article</a>\n";
    echo "</div>\n";
}

echo "</div>\n";
echo "</div>\n";

// JavaScript
echo "<script>\n";
echo "const newsId = " . ($news['id'] ?? 1) . ";\n";
echo "let isLiked = false;\n";
echo "let activityInterval = null;\n\n";

// Include the same JavaScript functions from news.php
echo file_get_contents('assets/js/main.js') . "\n\n";

// Test functions
echo "function addLog(message, type = 'info') {\n";
echo "    const log = document.getElementById('statusLog');\n";
echo "    const timestamp = new Date().toLocaleTimeString();\n";
echo "    const color = type === 'success' ? 'green' : type === 'error' ? 'red' : type === 'warning' ? 'orange' : 'blue';\n";
echo "    log.innerHTML += '<small style=\\'color: ' + color + '\\'>[' + timestamp + '] ' + message + '</small><br>';\n";
echo "    log.scrollTop = log.scrollHeight;\n";
echo "}\n\n";

echo "function testStats() {\n";
echo "    addLog('Testing stats update...', 'info');\n";
echo "    updateInteractionStats();\n";
echo "}\n\n";

echo "function testLike() {\n";
echo "    addLog('Testing like toggle...', 'info');\n";
echo "    toggleLike(newsId);\n";
echo "}\n\n";

echo "function testShare() {\n";
echo "    addLog('Testing share tracking...', 'info');\n";
echo "    trackShare(newsId, 'test');\n";
echo "}\n\n";

echo "function simulateMultipleUsers() {\n";
echo "    if (activityInterval) {\n";
echo "        clearInterval(activityInterval);\n";
echo "        activityInterval = null;\n";
echo "        addLog('Stopped activity simulation', 'warning');\n";
echo "        return;\n";
echo "    }\n";
echo "    \n";
echo "    addLog('Starting activity simulation...', 'success');\n";
echo "    activityInterval = setInterval(() => {\n";
echo "        const actions = ['like', 'share', 'stats'];\n";
echo "        const action = actions[Math.floor(Math.random() * actions.length)];\n";
echo "        \n";
echo "        switch(action) {\n";
echo "            case 'like':\n";
echo "                toggleLike(newsId);\n";
echo "                break;\n";
echo "            case 'share':\n";
echo "                trackShare(newsId, 'simulation');\n";
echo "                break;\n";
echo "            case 'stats':\n";
echo "                updateInteractionStats();\n";
echo "                break;\n";
echo "        }\n";
echo "    }, 2000);\n";
echo "}\n\n";

echo "function resetStats() {\n";
echo "    addLog('Resetting stats...', 'warning');\n";
echo "    fetch('api/news_interactions.php', {\n";
echo "        method: 'POST',\n";
echo "        headers: {'Content-Type': 'application/x-www-form-urlencoded'},\n";
echo "        body: 'action=reset_stats&news_id=' + newsId\n";
echo "    })\n";
echo "    .then(response => response.json())\n";
echo "    .then(data => {\n";
echo "        if (data.success) {\n";
echo "            updateStatsDisplay(data);\n";
echo "            addLog('Stats reset successfully', 'success');\n";
echo "        } else {\n";
echo "            addLog('Failed to reset stats: ' + data.message, 'error');\n";
echo "        }\n";
echo "    })\n";
echo "    .catch(error => {\n";
echo "        addLog('Error resetting stats: ' + error.message, 'error');\n";
echo "    });\n";
echo "}\n\n";

// Copy the interaction functions from news.php
$news_js_content = file_get_contents('news.php');
preg_match('/\/\/ Real-time Interaction System.*?\/\/ Update real-time dates every minute/s', $news_js_content, $matches);
if (isset($matches[0])) {
    echo $matches[0] . "\n";
}

echo "// Initialize on page load\n";
echo "document.addEventListener('DOMContentLoaded', function() {\n";
echo "    initInteractions();\n";
echo "    addLog('Real-time interactions initialized', 'success');\n";
echo "});\n";

echo "</script>\n";
echo "</body>\n</html>\n";
?>
