<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

// Test like functionality
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create test news article if not exists
$test_news_id = 37;
$check_news = "SELECT id FROM news WHERE id = ?";
$stmt = mysqli_prepare($conn, $check_news);
mysqli_stmt_bind_param($stmt, 'i', $test_news_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    // Insert test news article
    $insert_news = "INSERT INTO news (title, content, slug, status, created_at) VALUES (?, ?, ?, ?, NOW())";
    $title = "Test Article for Like System";
    $content = "This is a test article to verify the like functionality works correctly.";
    $slug = "test-article-like-system";
    $status = "published";
    
    $stmt = mysqli_prepare($conn, $insert_news);
    mysqli_stmt_bind_param($stmt, "ssss", $title, $content, $slug, $status);
    mysqli_stmt_execute($stmt);
    $test_news_id = mysqli_insert_id($conn);
}

// Clear existing likes for this test
$delete_likes = "DELETE FROM post_likes WHERE news_id = ?";
$stmt = mysqli_prepare($conn, $delete_likes);
mysqli_stmt_bind_param($stmt, 'i', $test_news_id);
mysqli_stmt_execute($stmt);

// Clear session likes
if (isset($_SESSION)) {
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'guest_likes_') === 0) {
            unset($_SESSION[$key]);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Like System Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .facebook-action-btn {
            border: none;
            background: none;
            padding: 8px 12px;
            color: #65676b;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .facebook-action-btn:hover {
            background: #f2f3f5;
            border-radius: 6px;
        }
        
        .facebook-action-btn.liked {
            color: #1877f2;
        }
        
        .like-summary {
            padding: 8px 0;
        }
        
        .reaction-icon {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: -8px;
            border: 2px solid white;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Like System Test</h1>
        <p>Testing the Facebook-style like button functionality</p>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Test Article</h5>
                <p class="card-text">This is a test article to verify the like functionality works correctly.</p>
                
                <div class="facebook-social-actions mt-3">
                    <!-- Like Count Display -->
                    <div class="like-summary mb-2" id="like-summary-<?php echo $test_news_id; ?>" style="display: none;">
                        <div class="d-flex align-items-center">
                            <div class="like-reactions me-2">
                                <span class="reaction-icon" style="background: #1877f2;">
                                    <i class="fas fa-thumbs-up" style="color: white;"></i>
                                </span>
                            </div>
                            <span class="like-text small text-muted">
                                <span class="likes-count-display">0</span>
                                people like this
                            </span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="facebook-action-buttons">
                        <div class="d-flex border-top border-bottom">
                            <!-- Like Button -->
                            <button class="facebook-action-btn like-btn flex-fill" onclick="toggleLike(<?php echo $test_news_id; ?>, this)" data-news-id="<?php echo $test_news_id; ?>">
                                <i class="fa-thumbs-up far"></i>
                                <span class="btn-text">Like</span>
                            </button>
                            
                            <!-- Comment Button -->
                            <button class="facebook-action-btn comment-btn flex-fill" disabled>
                                <i class="far fa-comment"></i>
                                <span class="btn-text">Comment</span>
                            </button>
                            
                            <!-- Share Button -->
                            <button class="facebook-action-btn share-btn flex-fill" disabled>
                                <i class="fas fa-share"></i>
                                <span class="btn-text">Share</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <h3>Test Instructions:</h3>
            <ol>
                <li>Click the Like button multiple times to test toggle functionality</li>
                <li>Check if the count updates correctly</li>
                <li>Verify the button state changes (Like ↔ Liked)</li>
                <li>Check browser console for any errors</li>
            </ol>
            
            <div class="mt-3">
                <h4>Debug Info:</h4>
                <div id="debug-info" class="alert alert-info">
                    <strong>News ID:</strong> <?php echo $test_news_id; ?><br>
                    <strong>Session ID:</strong> <?php echo session_id(); ?><br>
                    <strong>User Type:</strong> <?php echo isset($_SESSION['user_id']) ? 'Registered' : 'Guest'; ?><br>
                    <strong>Current Likes Count:</strong> <span id="current-likes">0</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Copy the toggleLike function from main.js
        function toggleLike(newsId, button) {
            console.log('toggleLike called for newsId:', newsId);
            
            // Validate inputs
            if (!button) {
                console.error('Button element not found');
                return;
            }
            
            const isLiked = button.classList.contains('liked');
            const likeSummary = document.getElementById('like-summary-' + newsId);
            const likesCountDisplay = button.querySelector('.likes-count-display') || 
                                    (likeSummary && likeSummary.querySelector('.likes-count-display'));
            
            // Use HTTP for development to avoid SSL issues
            const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            const baseUrl = isLocalhost ? 
                'http://localhost/PK-LIVE%20NEWS' : 
                window.location.origin;
            
            console.log('Sending request to:', baseUrl + '/api/toggle_like.php');
            console.log('Current state - isLiked:', isLiked);
            
            fetch(baseUrl + '/api/toggle_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'news_id=' + newsId
            })
            .then(response => {
                console.log('Response received:', response);
                return response.json();
            })
            .then(data => {
                console.log('Like response:', data);
                if (data.success) {
                    // Update button state
                    const icon = button.querySelector('i');
                    const btnText = button.querySelector('.btn-text');
                    
                    if (icon && btnText) {
                        if (isLiked) {
                            button.classList.remove('liked');
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            btnText.textContent = 'Like';
                        } else {
                            button.classList.add('liked');
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            btnText.textContent = 'Liked';
                        }
                    }
                    
                    // Update like count display
                    if (likesCountDisplay) {
                        likesCountDisplay.textContent = data.likes_count;
                    }
                    
                    // Update debug info
                    document.getElementById('current-likes').textContent = data.likes_count;
                    
                    // Update like summary visibility
                    if (likeSummary) {
                        if (data.likes_count > 0) {
                            likeSummary.style.display = 'block';
                            const likeText = likeSummary.querySelector('.like-text');
                            if (likeText) {
                                likeText.innerHTML = '<span class="likes-count-display">' + data.likes_count + '</span> ' + 
                                                  (data.likes_count == 1 ? 'person likes this' : 'people like this');
                            }
                        } else {
                            likeSummary.style.display = 'none';
                        }
                    }
                } else {
                    console.error('Server error:', data.message);
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error toggling like:', error);
                alert('Network error: ' + error.message);
            });
        }
        
        // Update debug info on load
        document.addEventListener('DOMContentLoaded', function() {
            // Check initial like count
            const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            const baseUrl = isLocalhost ? 
                'http://localhost/PK-LIVE%20NEWS' : 
                window.location.origin;
            
            fetch(baseUrl + '/api/toggle_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'news_id=<?php echo $test_news_id; ?>&action=get_count'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('current-likes').textContent = data.likes_count || 0;
                }
            })
            .catch(error => {
                console.log('Could not get initial count:', error);
            });
        });
    </script>
</body>
</html>
