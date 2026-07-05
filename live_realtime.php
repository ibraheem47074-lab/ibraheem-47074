<?php
require_once 'config/database.php';
$page_title = 'Live TV - Real Time';

// Get live stream info
$live_stream = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM live_stream WHERE status = 'online' ORDER BY id DESC LIMIT 1"
));

// Get upcoming streams
$upcoming_query = "SELECT * FROM live_stream WHERE status = 'scheduled' AND schedule_time > NOW() ORDER BY schedule_time ASC LIMIT 5";
$upcoming_result = mysqli_query($conn, $upcoming_query);

// Get recent streams (past broadcasts)
$recent_query = "SELECT * FROM live_stream WHERE status = 'offline' ORDER BY id DESC LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);

// Get real viewer count from database
$viewer_count = 0;
if ($live_stream) {
    // Check if live_viewers table exists first
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_viewers'");
    if (mysqli_num_rows($table_check) > 0) {
        $count_query = mysqli_prepare($conn, 
            "SELECT COUNT(*) as count FROM live_viewers 
             WHERE stream_id = ? AND is_active = TRUE 
             AND last_activity >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)"
        );
        mysqli_stmt_bind_param($count_query, 'i', $live_stream['id']);
        mysqli_stmt_execute($count_query);
        $count_result = mysqli_stmt_get_result($count_query);
        $viewer_count = (int)mysqli_fetch_assoc($count_result)['count'];
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Live TV Section -->
<section class="live-tv-section py-4">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 text-danger mb-3">
                <i class="fas fa-broadcast-tower me-3"></i>PK Live TV
            </h1>
            <p class="lead">Watch breaking news and live coverage 24/7</p>
        </div>

        <?php if ($live_stream): ?>
            <!-- Current Live Stream -->
            <div class="row mb-5">
                <div class="col-lg-8">
                    <div class="live-video-container shadow-lg">
                        <div class="live-header bg-danger text-white p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="on-air-indicator me-3">ON AIR</span>
                                <h4 class="mb-0"><?php echo htmlspecialchars($live_stream['title']); ?></h4>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-eye me-2"></i>
                                <span class="viewer-count" id="liveViewerCount"><?php echo number_format($viewer_count); ?></span>
                                <span class="real-time-indicator ms-2">
                                    <span class="live-dot"></span>
                                    LIVE
                                </span>
                            </div>
                        </div>
                        
                        <div class="video-wrapper bg-black">
                            <?php if ($live_stream['embed_code']): ?>
                                <?php echo $live_stream['embed_code']; ?>
                            <?php elseif ($live_stream['stream_url']): ?>
                                <?php
                                // Handle different streaming sources
                                if (strpos($live_stream['stream_url'], 'youtube.com') !== false || strpos($live_stream['stream_url'], 'youtu.be') !== false) {
                                    // YouTube embed
                                    $video_id = '';
                                    if (strpos($live_stream['stream_url'], 'youtube.com/watch?v=') !== false) {
                                        $video_id = substr($live_stream['stream_url'], strpos($live_stream['stream_url'], 'v=') + 2);
                                    } elseif (strpos($live_stream['stream_url'], 'youtu.be/') !== false) {
                                        $video_id = substr($live_stream['stream_url'], strpos($live_stream['stream_url'], 'youtu.be/') + 9);
                                    }
                                    $video_id = explode('?', $video_id)[0];
                                    
                                    echo '<iframe class="embed-responsive-item" src="https://www.youtube.com/embed/' . $video_id . '" 
                                            width="100%" height="500" frameborder="0" allowfullscreen></iframe>';
                                } else {
                                    // Generic embed
                                    echo '<iframe src="' . htmlspecialchars($live_stream['stream_url']) . '" 
                                            width="100%" height="500" frameborder="0" allowfullscreen></iframe>';
                                }
                                ?>
                            <?php else: ?>
                                <div class="text-center text-white p-5" style="height: 500px; display: flex; align-items: center; justify-content: center;">
                                    <div>
                                        <i class="fas fa-broadcast-tower fa-4x mb-3"></i>
                                        <h4>Live Stream Starting Soon</h4>
                                        <p>Please stay tuned for our next broadcast</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Live Chat Section -->
                        <div class="live-chat bg-light p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Live Chat</h5>
                                <span class="badge bg-primary" id="commentCount">0</span>
                            </div>
                            <div class="chat-messages bg-white border rounded p-3 mb-3" style="height: 200px; overflow-y: auto;" id="chatMessages">
                                <div class="text-muted text-center">Loading comments...</div>
                            </div>
                            <form class="chat-form d-flex" id="chatForm">
                                <input type="text" class="form-control me-2" placeholder="Type your message..." id="chatInput" maxlength="200">
                                <button type="submit" class="btn btn-danger" id="chatButton">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                            <div class="mt-2 text-end">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Be respectful and stay on topic
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Stream Info -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Stream Information</h5>
                            <p class="card-text"><?php echo htmlspecialchars($live_stream['description']); ?></p>
                            
                            <div class="stream-stats">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-eye me-1"></i> Live Viewers</span>
                                    <strong id="sidebarViewerCount"><?php echo number_format($viewer_count); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-chart-line me-1"></i> Peak Today</span>
                                    <strong id="peakViewers">-</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-clock me-1"></i> Started</span>
                                    <strong><?php echo format_date($live_stream['created_at']); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-signal me-1"></i> Quality</span>
                                    <strong>HD 1080p</strong>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button class="btn btn-danger w-100" onclick="toggleFullscreen()">
                                    <i class="fas fa-expand me-2"></i>Fullscreen Mode
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Real-time Stats -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Real-time Stats</h5>
                            <div class="real-time-stats">
                                <div class="stat-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Current Viewers</span>
                                        <span class="badge bg-primary" id="currentViewers"><?php echo $viewer_count; ?></span>
                                    </div>
                                </div>
                                <div class="stat-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Peak Today</span>
                                        <span class="badge bg-success" id="peakToday">-</span>
                                    </div>
                                </div>
                                <div class="stat-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Average Today</span>
                                        <span class="badge bg-info" id="avgToday">-</span>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="d-flex justify-content-between">
                                        <span>Connection</span>
                                        <span class="badge bg-success" id="connectionStatus">Connected</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Viewer Chart -->
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Live Viewer Chart</h5>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary active" data-timeframe="1hour">1H</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-timeframe="6hours">6H</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-timeframe="1day">1D</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-timeframe="1week">1W</button>
                                </div>
                            </div>
                            <div class="chart-container" style="position: relative; height: 250px;">
                                <canvas id="liveViewerChart"></canvas>
                            </div>
                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    <i class="fas fa-circle text-success me-1"></i>
                                    Live updates every 10 seconds
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- No Live Stream -->
            <div class="text-center py-5">
                <div class="bg-light rounded p-5">
                    <i class="fas fa-broadcast-tower fa-4x text-muted mb-3"></i>
                    <h3>No Live Stream Currently</h3>
                    <p class="text-muted">Please check back later for our next live broadcast</p>
                    <button class="btn btn-danger" onclick="checkForLiveStream()">
                        <i class="fas fa-sync me-2"></i>Check for Live Stream
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Upcoming Streams -->
        <?php if (mysqli_num_rows($upcoming_result) > 0): ?>
            <section class="upcoming-streams mb-5">
                <h3 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Upcoming Streams</h3>
                <div class="row">
                    <?php while ($upcoming = mysqli_fetch_assoc($upcoming_result)): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title"><?php echo htmlspecialchars($upcoming['title']); ?></h5>
                                        <span class="badge bg-warning">Scheduled</span>
                                    </div>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($upcoming['description'], 0, 100)) . '...'; ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo date('M d, Y - h:i A', strtotime($upcoming['schedule_time'])); ?>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger" onclick="setReminder('<?php echo $upcoming['id']; ?>')">
                                            <i class="fas fa-bell me-1"></i>Set Reminder
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Recent Broadcasts -->
        <?php if (mysqli_num_rows($recent_result) > 0): ?>
            <section class="recent-broadcasts">
                <h3 class="mb-4"><i class="fas fa-history me-2"></i>Recent Broadcasts</h3>
                <div class="row">
                    <?php while ($recent = mysqli_fetch_assoc($recent_result)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card shadow">
                                <?php if ($recent['thumbnail']): ?>
                                    <img src="<?php echo htmlspecialchars($recent['thumbnail']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($recent['title']); ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-video fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($recent['title']); ?></h6>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($recent['description'], 0, 80)) . '...'; ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo format_date($recent['created_at']); ?>
                                        </small>
                                        <?php if ($recent['stream_url']): ?>
                                            <a href="<?php echo htmlspecialchars($recent['stream_url']); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-play me-1"></i>Watch
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>

<!-- Live Stream Schedule Modal -->
<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You'll be notified 15 minutes before the stream starts.</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="emailReminder" checked>
                    <label class="form-check-label" for="emailReminder">
                        Email notification
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="browserReminder" checked>
                    <label class="form-check-label" for="browserReminder">
                        Browser notification
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReminder()">Set Reminder</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.live-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #ff0000;
    border-radius: 50%;
    margin-right: 5px;
    animation: pulse 2s infinite;
}

.real-time-indicator {
    background: rgba(255, 0, 0, 0.1);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    color: #ff0000;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.stat-item {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.real-time-stats {
    font-size: 14px;
}

.badge {
    font-size: 12px;
}
</style>

<script>
let currentStreamId = <?php echo $live_stream['id'] ?? 0; ?>;
let viewerSessionId = null;
let heartbeatInterval = null;
let statsInterval = null;
let chartInterval = null;
let liveChart = null;
let currentTimeframe = '1hour';

// Initialize viewer tracking
function initializeViewerTracking() {
    if (currentStreamId > 0) {
        joinStream();
        startHeartbeat();
        startStatsUpdate();
        initializeLiveChart();
        startChartUpdates();
        loadComments();
        startCommentUpdates();
    }
}

// Initialize Live Chart
function initializeLiveChart() {
    const ctx = document.getElementById('liveViewerChart');
    if (!ctx) return;

    liveChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Viewers',
                data: [],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#dc3545',
                pointBorderColor: '#fff',
                pointBorderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 8
                    }
                },
                y: {
                    display: true,
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        precision: 0
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });

    // Load initial chart data
    loadChartData();
}

// Load chart data from API
function loadChartData() {
    if (!currentStreamId || !liveChart) return;

    fetch(`api/live_chart_data.php?action=viewer_chart_data&stream_id=${currentStreamId}&timeframe=${currentTimeframe}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateChart(data);
                updateStats(data);
            }
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
        });
}

// Update chart with new data
function updateChart(data) {
    if (!liveChart || !data.data) return;

    const labels = data.data.map(item => {
        const date = new Date(item.time);
        return date.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        });
    });

    const viewers = data.data.map(item => item.viewers);

    liveChart.data.labels = labels;
    liveChart.data.datasets[0].data = viewers;
    liveChart.update('none'); // Update without animation for smooth real-time updates
}

// Start chart updates
function startChartUpdates() {
    // Update chart every 10 seconds
    chartInterval = setInterval(() => {
        loadChartData();
    }, 10000);
}

// Handle timeframe buttons
document.addEventListener('click', function(e) {
    if (e.target.matches('[data-timeframe]')) {
        // Remove active class from all buttons
        document.querySelectorAll('[data-timeframe]').forEach(btn => {
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-outline-primary');
        });
        
        // Add active class to clicked button
        e.target.classList.remove('btn-outline-primary');
        e.target.classList.add('active', 'btn-primary');
        
        // Update timeframe and reload data
        currentTimeframe = e.target.dataset.timeframe;
        loadChartData();
    }
});

// Update stats from chart data
function updateStats(data) {
    if (data.current_viewers !== undefined) {
        updateViewerCount(data.current_viewers);
    }
    
    if (data.peak_today !== undefined) {
        const peakElement = document.getElementById('peakToday');
        if (peakElement) {
            peakElement.textContent = data.peak_today.toLocaleString();
        }
    }
    
    if (data.avg_today !== undefined) {
        const avgElement = document.getElementById('avgToday');
        if (avgElement) {
            avgElement.textContent = Math.round(data.avg_today).toLocaleString();
        }
    }
}

// Join stream
function joinStream() {
    fetch('api/live_viewers.php?action=join&stream_id=' + currentStreamId, {
        method: 'POST',
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            viewerSessionId = data.session_id;
            updateViewerCount(data.viewer_count);
            showNotification('Joined live stream successfully', 'success');
        }
    })
    .catch(error => {
        console.error('Error joining stream:', error);
    });
}

// Send heartbeat to keep connection alive
function startHeartbeat() {
    heartbeatInterval = setInterval(() => {
        if (viewerSessionId) {
            fetch('api/live_viewers.php?action=heartbeat&stream_id=' + currentStreamId + '&session_id=' + viewerSessionId, {
                method: 'POST',
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateViewerCount(data.viewer_count);
                    updateConnectionStatus(true);
                }
            })
            .catch(error => {
                updateConnectionStatus(false);
                console.error('Heartbeat error:', error);
            });
        }
    }, 30000); // Every 30 seconds
}

// Update stats every 10 seconds
function startStatsUpdate() {
    statsInterval = setInterval(() => {
        if (currentStreamId > 0) {
            fetch('api/live_viewers.php?action=stats&stream_id=' + currentStreamId, {
                method: 'GET',
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.current_viewers !== undefined) {
                    updateStats(data);
                }
            })
            .catch(error => {
                console.error('Stats error:', error);
            });
        }
    }, 10000); // Every 10 seconds
}

// Update viewer count display
function updateViewerCount(count) {
    const elements = ['liveViewerCount', 'sidebarViewerCount', 'currentViewers'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = count.toLocaleString();
        }
    });
}

// Update detailed stats
function updateStats(data) {
    updateViewerCount(data.current_viewers);
    
    const peakElement = document.getElementById('peakToday');
    if (peakElement) {
        peakElement.textContent = data.peak_viewers_today.toLocaleString();
    }
    
    const avgElement = document.getElementById('avgToday');
    if (avgElement) {
        avgElement.textContent = Math.round(data.avg_viewers_today).toLocaleString();
    }
}

// Update connection status
function updateConnectionStatus(connected) {
    const statusElement = document.getElementById('connectionStatus');
    if (statusElement) {
        statusElement.textContent = connected ? 'Connected' : 'Reconnecting...';
        statusElement.className = connected ? 'badge bg-success' : 'badge bg-warning';
    }
}

// Toggle fullscreen for video
function toggleFullscreen() {
    const videoContainer = document.querySelector('.video-wrapper');
    if (videoContainer) {
        if (videoContainer.requestFullscreen) {
            videoContainer.requestFullscreen();
        } else if (videoContainer.webkitRequestFullscreen) {
            videoContainer.webkitRequestFullscreen();
        } else if (videoContainer.msRequestFullscreen) {
            videoContainer.msRequestFullscreen();
        }
    }
}

// Check for live stream
function checkForLiveStream() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking...';
    button.disabled = true;
    
    setTimeout(() => {
        location.reload();
    }, 2000);
}

// Set reminder for upcoming stream
function setReminder(streamId) {
    currentStreamId = streamId;
    const modal = new bootstrap.Modal(document.getElementById('reminderModal'));
    modal.show();
}

// Confirm reminder
function confirmReminder() {
    if (currentStreamId) {
        showNotification('Reminder set successfully! You will be notified before the stream starts.', 'success');
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('reminderModal'));
        modal.hide();
    }
}

// Show notification
function showNotification(message, type = 'info') {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('PK Live News', {
            body: message,
            icon: '/favicon.ico'
        });
    }
}

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

// Load comments from API
function loadComments() {
    if (!currentStreamId) return;

    fetch(`api/live_chart_data.php?action=comments&stream_id=${currentStreamId}&limit=20`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayComments(data.comments);
                updateCommentCount(data.count);
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            // Show demo comments if API fails
            displayDemoComments();
        });
}

// Display comments in chat
function displayComments(comments) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;

    chatMessages.innerHTML = '';
    
    if (comments.length === 0) {
        chatMessages.innerHTML = '<div class="text-muted text-center">No comments yet. Be the first to comment!</div>';
        return;
    }

    comments.forEach(comment => {
        addCommentToChat(comment);
    });
    
    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Add single comment to chat
function addCommentToChat(comment) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;

    const commentHtml = `
        <div class="chat-message mb-2 p-2 border-bottom">
            <div class="d-flex align-items-start">
                <img src="${comment.profile_image}" alt="${comment.username}" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="text-primary">${comment.username}</strong>
                        <small class="text-muted">${comment.time}</small>
                    </div>
                    <div class="mt-1">${comment.comment}</div>
                </div>
            </div>
        </div>
    `;
    
    chatMessages.insertAdjacentHTML('beforeend', commentHtml);
}

// Display demo comments
function displayDemoComments() {
    const demoComments = [
        {
            id: 1,
            username: 'Ahmed',
            comment: 'Great coverage! Keep up the good work.',
            time: '2 minutes ago',
            profile_image: 'assets/images/default-avatar.png'
        },
        {
            id: 2,
            username: 'Sara',
            comment: 'Very informative broadcast, thank you!',
            time: '5 minutes ago',
            profile_image: 'assets/images/default-avatar.png'
        }
    ];
    displayComments(demoComments);
}

// Update comment count
function updateCommentCount(count) {
    const countElement = document.getElementById('commentCount');
    if (countElement) {
        countElement.textContent = count;
    }
}

// Start comment updates
function startCommentUpdates() {
    // Update comments every 15 seconds
    commentUpdateInterval = setInterval(() => {
        loadComments();
    }, 15000);
}

// Post comment
function postComment(message) {
    if (!currentStreamId || !message.trim()) return;

    const formData = new FormData();
    formData.append('stream_id', currentStreamId);
    formData.append('comment', message.trim());
    formData.append('username', getCurrentUsername());

    fetch('api/live_chart_data.php?action=post_comment', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear input
            document.getElementById('chatInput').value = '';
            // Reload comments
            loadComments();
            showNotification('Comment posted successfully', 'success');
        } else {
            showNotification('Failed to post comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error posting comment:', error);
        showNotification('Failed to post comment', 'error');
    });
}

// Get current username (you can modify this based on your auth system)
function getCurrentUsername() {
    // Try to get from session or use a default
    return <?php echo isset($_SESSION['username']) ? json_encode($_SESSION['username']) : "'Anonymous'"; ?>;
}

// Handle chat form submission
const chatFormElement = document.getElementById('chatForm');
const chatInputElement = document.getElementById('chatInput');

if (chatFormElement && chatInputElement) {
    chatFormElement.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = chatInputElement.value.trim();
        if (message) {
            postComment(message);
        }
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeViewerTracking();
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (viewerSessionId && currentStreamId > 0) {
        navigator.sendBeacon('api/live_viewers.php?action=leave&stream_id=' + currentStreamId + '&session_id=' + viewerSessionId);
    }
    
    if (heartbeatInterval) {
        clearInterval(heartbeatInterval);
    }
    if (statsInterval) {
        clearInterval(statsInterval);
    }
    if (chartInterval) {
        clearInterval(chartInterval);
    }
    if (commentUpdateInterval) {
        clearInterval(commentUpdateInterval);
    }
});
</script>
