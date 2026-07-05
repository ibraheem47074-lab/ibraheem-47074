<?php
require_once 'config/database.php';

$page_title = 'Deployment Feedback';

// Get deployment information
$deployment_id = $_GET['deployment_id'] ?? 0;
$deployment = null;

if ($deployment_id > 0) {
    $query = "SELECT * FROM live_deployments WHERE id = ? AND status = 'live'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $deployment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $deployment = mysqli_fetch_assoc($result);
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deployment_id = (int)$_POST['deployment_id'];
    $feedback_type = clean_input($_POST['feedback_type']);
    $rating = (int)$_POST['rating'];
    $feedback_text = clean_input($_POST['feedback_text']);
    
    $video_quality = clean_input($_POST['video_quality']) ?? null;
    $audio_quality = clean_input($_POST['audio_quality']) ?? null;
    $stream_stability = clean_input($_POST['stream_stability']) ?? null;
    $loading_speed = clean_input($_POST['loading_speed']) ?? null;
    
    // Detect device information
    $device_type = detectDeviceType();
    $browser = getBrowser();
    $connection_type = detectConnectionType();
    
    // Get location information (simplified)
    $country = $_POST['country'] ?? null;
    $city = $_POST['city'] ?? null;
    
    $watch_duration = (int)$_POST['watch_duration'] ?? 0;
    
    // Submit feedback via API
    $api_url = 'api/feedback-api.php';
    $data = [
        'action' => 'submit_feedback',
        'deployment_id' => $deployment_id,
        'user_id' => is_logged_in() ? $_SESSION['user_id'] : null,
        'feedback_type' => $feedback_type,
        'rating' => $rating,
        'feedback_text' => $feedback_text,
        'video_quality' => $video_quality,
        'audio_quality' => $audio_quality,
        'stream_stability' => $stream_stability,
        'loading_speed' => $loading_speed,
        'device_type' => $device_type,
        'browser' => $browser,
        'connection_type' => $connection_type,
        'country' => $country,
        'city' => $city,
        'watch_duration' => $watch_duration
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($api_url, false, $context);
    $response = json_decode($result, true);
    
    if ($response['success']) {
        $success_message = 'Thank you for your feedback! Your response has been submitted successfully.';
        // Clear form
        $_POST = [];
    } else {
        $error_message = 'Failed to submit feedback. Please try again.';
    }
}

// Helper functions
function detectDeviceType() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $user_agent)) {
        if (preg_match('/iPad/', $user_agent)) {
            return 'tablet';
        }
        return 'mobile';
    }
    
    return 'desktop';
}

function getBrowser() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (preg_match('/Chrome/', $user_agent)) return 'Chrome';
    if (preg_match('/Firefox/', $user_agent)) return 'Firefox';
    if (preg_match('/Safari/', $user_agent)) return 'Safari';
    if (preg_match('/Edge/', $user_agent)) return 'Edge';
    
    return 'Unknown';
}

function detectConnectionType() {
    // This is a simplified detection - in production, you'd use Network Information API
    return 'unknown';
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-comments me-2"></i>Deployment Feedback</h4>
                    <p class="text-muted mb-0">Help us improve our live streaming experience</p>
                </div>
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($deployment): ?>
                        <div class="mb-4">
                            <h5>Feedback for: <?php echo htmlspecialchars($deployment['deployment_name']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($deployment['title']); ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="feedbackForm">
                        <input type="hidden" name="deployment_id" value="<?php echo $deployment_id; ?>">
                        <input type="hidden" name="watch_duration" id="watchDuration" value="0">
                        
                        <!-- Overall Rating -->
                        <div class="mb-4">
                            <label class="form-label">Overall Rating *</label>
                            <div class="rating-container">
                                <div class="star-rating" id="starRating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="far fa-star star" data-rating="<?php echo $i; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="form-text text-muted">Click to rate your experience</small>
                                <input type="hidden" name="rating" id="rating" value="5" required>
                            </div>
                        </div>

                        <!-- Feedback Type -->
                        <div class="mb-3">
                            <label for="feedback_type" class="form-label">Feedback Type *</label>
                            <select class="form-select" name="feedback_type" required>
                                <option value="general">General Feedback</option>
                                <option value="quality">Video/Audio Quality</option>
                                <option value="performance">Stream Performance</option>
                                <option value="content">Content Quality</option>
                                <option value="technical">Technical Issues</option>
                            </select>
                        </div>

                        <!-- Technical Quality Assessment -->
                        <div class="mb-4">
                            <h6>Technical Quality Assessment</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="video_quality" class="form-label">Video Quality</label>
                                        <select class="form-select" name="video_quality">
                                            <option value="">Not applicable</option>
                                            <option value="excellent">Excellent</option>
                                            <option value="good">Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="poor">Poor</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="audio_quality" class="form-label">Audio Quality</label>
                                        <select class="form-select" name="audio_quality">
                                            <option value="">Not applicable</option>
                                            <option value="excellent">Excellent</option>
                                            <option value="good">Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="poor">Poor</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="stream_stability" class="form-label">Stream Stability</label>
                                        <select class="form-select" name="stream_stability">
                                            <option value="">Not applicable</option>
                                            <option value="stable">Stable</option>
                                            <option value="occasional_buffering">Occasional Buffering</option>
                                            <option value="frequent_buffering">Frequent Buffering</option>
                                            <option value="unwatchable">Unwatchable</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="loading_speed" class="form-label">Loading Speed</label>
                                        <select class="form-select" name="loading_speed">
                                            <option value="">Not applicable</option>
                                            <option value="instant">Instant</option>
                                            <option value="fast">Fast</option>
                                            <option value="moderate">Moderate</option>
                                            <option value="slow">Slow</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Text -->
                        <div class="mb-4">
                            <label for="feedback_text" class="form-label">Your Feedback *</label>
                            <textarea class="form-control" name="feedback_text" rows="4" 
                                      placeholder="Please share your detailed feedback..." required></textarea>
                            <small class="form-text text-muted">Be as specific as possible to help us improve</small>
                        </div>

                        <!-- Optional Location Info -->
                        <div class="mb-4">
                            <h6>Location Information (Optional)</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" class="form-control" name="country" placeholder="Your country">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" name="city" placeholder="Your city">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Feedback Buttons -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6>Quick Feedback</h6>
                    <p class="text-muted small">Click any of these to submit quick feedback</p>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-success btn-sm" onclick="quickFeedback('praise', 5)">
                            <i class="fas fa-thumbs-up me-1"></i>Great Stream!
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="quickFeedback('buffering', 3)">
                            <i class="fas fa-wifi me-1"></i>Buffering Issues
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="quickFeedback('quality', 2)">
                            <i class="fas fa-video me-1"></i>Poor Quality
                        </button>
                        <button class="btn="btn-outline-info btn-sm" onclick="quickFeedback('audio', 3)">
                            <i class="fas fa-volume-up me-1"></i>Audio Problems
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.star-rating {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
}

.star-rating .star {
    transition: color 0.2s;
}

.star-rating .star:hover,
.star-rating .star.active {
    color: #ffc107;
}

.rating-container {
    text-align: center;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
</style>

<script>
// Star rating functionality
let currentRating = 5;

document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            currentRating = parseInt(this.dataset.rating);
            updateStars(currentRating);
            document.getElementById('rating').value = currentRating;
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            updateStars(rating);
        });
    });
    
    document.getElementById('starRating').addEventListener('mouseleave', function() {
        updateStars(currentRating);
    });
    
    // Initialize with 5 stars
    updateStars(5);
    
    // Track watch duration
    startWatchTimer();
});

function updateStars(rating) {
    const stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('far');
            star.classList.add('fas', 'active');
        } else {
            star.classList.remove('fas', 'active');
            star.classList.add('far');
        }
    });
}

// Quick feedback function
function quickFeedback(type, rating) {
    document.getElementById('feedback_type').value = 'general';
    document.getElementById('rating').value = rating;
    updateStars(rating);
    
    let feedbackText = '';
    switch(type) {
        case 'praise':
            feedbackText = 'Great stream quality and smooth playback!';
            break;
        case 'buffering':
            feedbackText = 'Experiencing buffering issues during the stream.';
            break;
        case 'quality':
            feedbackText = 'Video quality could be improved.';
            break;
        case 'audio':
            feedbackText = 'Having audio problems with the stream.';
            break;
    }
    
    document.getElementById('feedback_text').value = feedbackText;
    
    // Scroll to form
    document.getElementById('feedbackForm').scrollIntoView({ behavior: 'smooth' });
}

// Watch timer
let watchStartTime = Date.now();

function startWatchTimer() {
    setInterval(function() {
        const duration = Math.floor((Date.now() - watchStartTime) / 1000);
        document.getElementById('watchDuration').value = duration;
    }, 1000);
}

// Form validation
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    const rating = document.getElementById('rating').value;
    const feedbackText = document.getElementById('feedback_text').value;
    
    if (!rating || rating < 1 || rating > 5) {
        e.preventDefault();
        alert('Please provide a rating');
        return;
    }
    
    if (!feedbackText || feedbackText.trim().length < 10) {
        e.preventDefault();
        alert('Please provide detailed feedback (at least 10 characters)');
        return;
    }
});

// Get user location (optional - using browser geolocation)
function getUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            // This would typically use a geocoding service to get country/city
            // For now, we'll leave it as optional user input
            console.log('Location available:', position.coords);
        });
    }
}

// Auto-detect some technical details
function detectConnectionType() {
    if ('connection' in navigator) {
        const connection = navigator.connection;
        console.log('Connection type:', connection.effectiveType);
        console.log('Downlink:', connection.downlink);
    }
}

// Initialize detection
document.addEventListener('DOMContentLoaded', function() {
    detectConnectionType();
    getUserLocation();
});
</script>

<?php include 'includes/footer.php'; ?>
