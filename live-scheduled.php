<?php
require_once 'config/database.php';

// Get current live stream (including auto-started scheduled streams)
$live_query = "SELECT * FROM live_stream WHERE status = 'online' ORDER BY id DESC LIMIT 1";
$live_result = mysqli_query($conn, $live_query);
$live_stream = mysqli_fetch_assoc($live_result);

// Get upcoming scheduled streams
$scheduled_query = "SELECT * FROM live_stream 
                   WHERE status = 'scheduled' AND schedule_time > NOW() 
                   ORDER BY schedule_time ASC LIMIT 5";
$scheduled_result = mysqli_query($conn, $scheduled_query);

// Get recent broadcasts
$recent_query = "SELECT * FROM live_stream 
                 WHERE status = 'offline' 
                 ORDER BY updated_at DESC LIMIT 3";
$recent_result = mysqli_query($conn, $recent_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live TV - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .live-indicator {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        .live-online {
            background-color: #00ff00;
        }
        .live-scheduled {
            background-color: #ffa500;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .countdown-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin: 10px 0;
        }
        .stream-card {
            border-left: 4px solid #dc3545;
            transition: all 0.3s ease;
        }
        .stream-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .live-now {
            border-left-color: #28a745;
        }
        .scheduled-soon {
            border-left-color: #ffc107;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Live Streaming Section -->
    <section class="live-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="display-4 mb-3">
                    <?php if ($live_stream): ?>
                        <span class="live-indicator live-online"></span>
                        LIVE NOW
                    <?php else: ?>
                        <span class="live-indicator live-scheduled"></span>
                        SCHEDULED STREAMING
                    <?php endif; ?>
                </h1>
                <p class="lead">Watch PK Live News 24/7 broadcasts</p>
            </div>

            <?php if ($live_stream): ?>
                <!-- Current Live Stream -->
                <div class="row mb-5">
                    <div class="col-lg-8">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h4 class="mb-0">
                                    <span class="live-indicator live-online"></span>
                                    <?php echo htmlspecialchars($live_stream['title']); ?>
                                </h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="ratio ratio-16x9">
                                    <?php if ($live_stream['embed_code']): ?>
                                        <?php echo $live_stream['embed_code']; ?>
                                    <?php elseif ($live_stream['stream_url']): ?>
                                        <?php if (strpos($live_stream['stream_url'], 'youtube.com') !== false): ?>
                                            <iframe src="<?php echo $live_stream['stream_url']; ?>" 
                                                    frameborder="0" allowfullscreen></iframe>
                                        <?php else: ?>
                                            <video id="liveVideo" controls autoplay muted>
                                                <source src="<?php echo $live_stream['stream_url']; ?>" type="application/x-mpegURL">
                                                Your browser does not support the video tag.
                                            </video>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                            <div class="text-center">
                                                <i class="fas fa-broadcast-tower fa-3x text-muted mb-3"></i>
                                                <h5>Stream Starting Soon</h5>
                                                <p class="text-muted">Please wait while we connect to the stream...</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Live Stats -->
                        <div class="card mb-3">
                            <div class="card-body text-center">
                                <h6 class="card-title">Live Viewers</h6>
                                <h3 class="text-success" id="viewerCount">1,234</h3>
                                <small class="text-muted">Watching now</small>
                            </div>
                        </div>
                        
                        <!-- Stream Info -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Stream Information</h6>
                                <p class="mb-2">
                                    <i class="fas fa-clock me-2"></i>
                                    Started: <?php echo date('h:i A', strtotime($live_stream['updated_at'])); ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-hourglass-half me-2"></i>
                                    Duration: <span id="streamDuration">0:00:00</span>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <?php echo htmlspecialchars(substr($live_stream['description'], 0, 100)) . '...'; ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Share Stream -->
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Share Stream</h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" onclick="shareStream('facebook')">
                                        <i class="fab fa-facebook-f me-2"></i>Facebook
                                    </button>
                                    <button class="btn btn-info" onclick="shareStream('twitter')">
                                        <i class="fab fa-twitter me-2"></i>Twitter
                                    </button>
                                    <button class="btn btn-success" onclick="shareStream('whatsapp')">
                                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- No Live Stream -->
                <div class="text-center mb-5">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-satellite-dish fa-4x text-muted mb-4"></i>
                            <h4>No Live Stream Currently</h4>
                            <p class="text-muted">Check our scheduled streams below or come back later</p>
                            
                            <?php 
                            $next_stream = mysqli_fetch_assoc(mysqli_query($conn, 
                                "SELECT * FROM live_stream WHERE status = 'scheduled' ORDER BY schedule_time ASC LIMIT 1"));
                            if ($next_stream): 
                            ?>
                                <div class="countdown-box">
                                    <h6>Next Stream In:</h6>
                                    <div id="nextStreamCountdown">Loading...</div>
                                    <p class="mb-0 mt-2">
                                        <strong><?php echo htmlspecialchars($next_stream['title']); ?></strong><br>
                                        <small><?php echo date('M d, Y - h:i A', strtotime($next_stream['schedule_time'])); ?></small>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Upcoming Scheduled Streams -->
            <?php if (mysqli_num_rows($scheduled_result) > 0): ?>
                <div class="row mb-5">
                    <div class="col-12">
                        <h3 class="mb-4">
                            <i class="fas fa-calendar-alt me-2"></i>Upcoming Streams
                        </h3>
                        
                        <div class="row">
                            <?php while ($stream = mysqli_fetch_assoc($scheduled_result)): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card stream-card scheduled-soon">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title"><?php echo htmlspecialchars($stream['title']); ?></h6>
                                                <span class="badge bg-warning">Scheduled</span>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <i class="fas fa-clock me-2"></i>
                                                <?php echo date('M d, Y - h:i A', strtotime($stream['schedule_time'])); ?>
                                            </div>
                                            
                                            <div class="countdown-box" id="countdown-<?php echo $stream['id']; ?>">
                                                Loading countdown...
                                            </div>
                                            
                                            <p class="card-text small">
                                                <?php echo htmlspecialchars(substr($stream['description'], 0, 80)) . '...'; ?>
                                            </p>
                                            
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-outline-primary" onclick="setReminder(<?php echo $stream['id']; ?>)">
                                                    <i class="fas fa-bell me-1"></i>Remind Me
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="viewDetails(<?php echo $stream['id']; ?>)">
                                                    <i class="fas fa-info-circle me-1"></i>Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Broadcasts -->
            <?php if (mysqli_num_rows($recent_result) > 0): ?>
                <div class="row">
                    <div class="col-12">
                        <h3 class="mb-4">
                            <i class="fas fa-history me-2"></i>Recent Broadcasts
                        </h3>
                        
                        <div class="row">
                            <?php while ($stream = mysqli_fetch_assoc($recent_result)): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card">
                                        <?php if ($stream['thumbnail']): ?>
                                            <img src="<?php echo htmlspecialchars($stream['thumbnail']); ?>" 
                                                 class="card-img-top" alt="<?php echo htmlspecialchars($stream['title']); ?>">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($stream['title']); ?></h6>
                                            <p class="card-text small">
                                                <?php echo htmlspecialchars(substr($stream['description'], 0, 60)) . '...'; ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M d, Y', strtotime($stream['updated_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Simulate live viewer count
        function updateViewerCount() {
            const viewerElement = document.getElementById('viewerCount');
            if (viewerElement) {
                const currentCount = parseInt(viewerElement.textContent.replace(',', ''));
                const change = Math.floor(Math.random() * 101) - 50; // Random change between -50 and +50
                const newCount = Math.max(100, currentCount + change);
                viewerElement.textContent = newCount.toLocaleString();
            }
        }

        // Update viewer count every 5 seconds
        setInterval(updateViewerCount, 5000);

        // Stream duration timer
        let streamStartTime = <?php echo $live_stream ? strtotime($live_stream['updated_at']) * 1000 : 'Date.now()'; ?>;
        
        function updateStreamDuration() {
            const now = Date.now();
            const duration = now - streamStartTime;
            const hours = Math.floor(duration / (1000 * 60 * 60));
            const minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((duration % (1000 * 60)) / 1000);
            
            const durationElement = document.getElementById('streamDuration');
            if (durationElement) {
                durationElement.textContent = `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
        }

        // Update duration every second
        setInterval(updateStreamDuration, 1000);

        // Countdown timers for scheduled streams
        function updateCountdowns() {
            <?php 
            mysqli_data_seek($scheduled_result, 0);
            while ($stream = mysqli_fetch_assoc($scheduled_result)): 
            ?>
                const streamTime<?php echo $stream['id']; ?> = new Date('<?php echo $stream['schedule_time']; ?>').getTime();
                const now = new Date().getTime();
                const distance<?php echo $stream['id']; ?> = streamTime<?php echo $stream['id']; ?> - now;
                
                const countdownEl<?php echo $stream['id']; ?> = document.getElementById('countdown-<?php echo $stream['id']; ?>');
                
                if (distance<?php echo $stream['id']; ?> < 0) {
                    countdownEl<?php echo $stream['id']; ?>.innerHTML = '<i class="fas fa-play me-2"></i>Starting Soon!';
                } else {
                    const days<?php echo $stream['id']; ?> = Math.floor(distance<?php echo $stream['id']; ?> / (1000 * 60 * 60 * 24));
                    const hours<?php echo $stream['id']; ?> = Math.floor((distance<?php echo $stream['id']; ?> % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes<?php echo $stream['id']; ?> = Math.floor((distance<?php echo $stream['id']; ?> % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds<?php echo $stream['id']; ?> = Math.floor((distance<?php echo $stream['id']; ?> % (1000 * 60)) / 1000);
                    
                    countdownEl<?php echo $stream['id']; ?>.innerHTML = 
                        '<i class="fas fa-clock me-2"></i>' + 
                        (days<?php echo $stream['id']; ?> > 0 ? days<?php echo $stream['id']; ?> + 'd ' : '') +
                        hours<?php echo $stream['id']; ?> + 'h ' + 
                        minutes<?php echo $stream['id']; ?> + 'm ' + 
                        seconds<?php echo $stream['id']; ?> + 's';
                }
            <?php endwhile; ?>
        }

        // Update countdowns every second
        setInterval(updateCountdowns, 1000);
        
        // Initial updates
        updateCountdowns();
        updateStreamDuration();

        // Next stream countdown
        <?php if (isset($next_stream)): ?>
        function updateNextStreamCountdown() {
            const nextStreamTime = new Date('<?php echo $next_stream['schedule_time']; ?>').getTime();
            const now = new Date().getTime();
            const distance = nextStreamTime - now;
            
            const countdownEl = document.getElementById('nextStreamCountdown');
            
            if (distance < 0) {
                countdownEl.innerHTML = 'Starting Now!';
                // Auto-refresh page to show live stream
                setTimeout(() => location.reload(), 5000);
            } else {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                countdownEl.innerHTML = 
                    (days > 0 ? days + 'd ' : '') +
                    hours + 'h ' + 
                    minutes + 'm ' + 
                    seconds + 's';
            }
        }

        setInterval(updateNextStreamCountdown, 1000);
        updateNextStreamCountdown();
        <?php endif; ?>

        // Share functions
        function shareStream(platform) {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('Watch PK Live News: ' + (document.title || 'Live Stream'));
            
            let shareUrl = '';
            
            switch(platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${text}&url=${url}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${text}%20${url}`;
                    break;
            }
            
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }

        // Reminder function
        function setReminder(streamId) {
            const streamTitle = document.querySelector(`#countdown-${streamId}`).closest('.card-body').querySelector('.card-title').textContent;
            
            if ('Notification' in window && 'serviceWorker' in navigator) {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        // Store reminder in localStorage
                        const reminders = JSON.parse(localStorage.getItem('streamReminders') || '[]');
                        reminders.push({ id: streamId, title: streamTitle, time: new Date().toISOString() });
                        localStorage.setItem('streamReminders', JSON.stringify(reminders));
                        
                        alert('Reminder set! We will notify you when the stream starts.');
                    }
                });
            } else {
                alert('Browser does not support notifications. Please check back manually.');
            }
        }

        // View details
        function viewDetails(streamId) {
            // Show stream details in modal or navigate to details page
            alert('Stream details feature coming soon!');
        }

        // Auto-refresh for live streams
        setInterval(() => {
            fetch('admin/check-scheduled-streams.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.started_streams.length > 0) {
                        // A scheduled stream has started, refresh the page
                        location.reload();
                    }
                })
                .catch(error => console.log('Error checking streams:', error));
        }, 30000); // Check every 30 seconds
    </script>
</body>
</html>
