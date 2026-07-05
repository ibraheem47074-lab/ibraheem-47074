<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Stream - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .live-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .live-header {
            text-align: center;
            color: white;
            margin-bottom: 2rem;
        }
        
        .live-badge {
            display: inline-flex;
            align-items: center;
            background: #ff0000;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: bold;
            animation: pulse 2s infinite;
            margin-bottom: 1rem;
        }
        
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        .video-container {
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
        }
        
        .video-js {
            width: 100%;
            height: 70vh;
        }
        
        .stream-info {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #ffd700;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }
        
        .controls-bar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            color: white;
        }
        
        .btn-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .quality-selector {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .quality-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .quality-btn:hover, .quality-btn.active {
            background: rgba(255, 255, 255, 0.3);
            border-color: #ffd700;
        }
        
        .video-js .vjs-big-play-button {
            background: rgba(102, 126, 234, 0.9);
            border: none;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            font-size: 3rem;
        }
        
        .video-js .vjs-control-bar {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
        }
        
        .video-js .vjs-live-display {
            background: #ff0000;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="live-container">
        <div class="live-header">
            <div class="live-badge">
                <i class="fas fa-circle me-2"></i>LIVE NOW
            </div>
            <h1 class="mb-0">PK Live News Stream</h1>
            <p class="lead">Breaking News Coverage</p>
        </div>

        <!-- Video Player -->
        <div class="video-container">
            <video-js 
                id="livePlayer" 
                controls 
                preload="auto" 
                poster="https://via.placeholder.com/1280x720/1e3c72/ffffff?text=LIVE+STREAM"
                data-setup='{"fluid": true, "liveui": true, "autoplay": true, "muted": true}'>
                <source src="http://localhost:8080/war.m3u8" type="application/x-mpegURL">
            </video-js>
        </div>

        <!-- Stream Information -->
        <div class="stream-info">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-3">Breaking News Live Coverage</h2>
                    <p class="mb-2">Stay tuned for the latest updates and breaking news from across the region.</p>
                    <div class="d-flex gap-4 flex-wrap">
                        <div>
                            <i class="fas fa-calendar me-2"></i>
                            <?php echo date('F j, Y'); ?>
                        </div>
                        <div>
                            <i class="fas fa-clock me-2"></i>
                            <span id="streamTime">Started: <?php echo date('g:i A'); ?></span>
                        </div>
                        <div>
                            <i class="fas fa-globe me-2"></i>
                            Global Coverage
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <i class="fas fa-eye"></i>
                            <h3 id="viewerCount">1,234</h3>
                            <small>Watching</small>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <h3 id="totalViews">15.6K</h3>
                            <small>Total Views</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls-bar">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-3">Stream Quality</h5>
                    <div class="quality-selector">
                        <button class="quality-btn active" onclick="changeQuality('auto')">Auto</button>
                        <button class="quality-btn" onclick="changeQuality('1080p')">1080p HD</button>
                        <button class="quality-btn" onclick="changeQuality('720p')">720p</button>
                        <button class="quality-btn" onclick="changeQuality('480p')">480p</button>
                        <button class="quality-btn" onclick="changeQuality('360p')">360p</button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-custom me-2" onclick="toggleFullscreen()">
                        <i class="fas fa-expand me-2"></i>Fullscreen
                    </button>
                    <button class="btn btn-custom" onclick="shareStream()">
                        <i class="fas fa-share me-2"></i>Share
                    </button>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex gap-3 flex-wrap">
                        <button class="btn btn-outline-light btn-sm" onclick="togglePictureInPicture()">
                            <i class="fas fa-clone me-1"></i>Picture-in-Picture
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="takeScreenshot()">
                            <i class="fas fa-camera me-1"></i>Screenshot
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="reportIssue()">
                            <i class="fas fa-exclamation-triangle me-1"></i>Report Issue
                        </button>
                        <button class="btn btn-outline-light btn-sm" onclick="refreshStream()">
                            <i class="fas fa-sync me-1"></i>Refresh Stream
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Video.js player
        let player;
        
        document.addEventListener('DOMContentLoaded', function() {
            const videoElement = document.getElementById('livePlayer');
            
            player = videojs('livePlayer', {
                controls: true,
                fluid: true,
                liveui: true,
                autoplay: true,
                muted: true,
                preload: 'auto',
                html5: {
                    hlsjsConfig: {
                        enableWorker: true,
                        lowLatencyMode: true,
                        maxMaxBufferLength: 6,
                        maxBufferSize: 6000000,
                        maxBufferLength: 5
                    }
                },
                techOrder: ['html5'],
                plugins: {
                    hotkeys: {
                        volumeStep: 0.1,
                        seekStep: 5,
                        enableModifiersForNumbers: false
                    }
                }
            });

            // Player events
            player.ready(function() {
                console.log('Live stream player ready');
                updateStreamTime();
            });

            player.on('playing', function() {
                console.log('Stream is playing');
                startViewerCountUpdate();
            });

            player.on('error', function(error) {
                console.error('Stream error:', error);
                showStreamError();
                // Auto-retry after 5 seconds
                setTimeout(reconnectStream, 5000);
            });

            player.on('stalled', function() {
                console.log('Stream stalled, attempting to reconnect...');
                setTimeout(reconnectStream, 3000);
            });

            player.on('waiting', function() {
                console.log('Buffering...');
            });

            player.on('canplay', function() {
                console.log('Stream can play');
            });

            // Update time every second
            setInterval(updateStreamTime, 1000);
        });

        // Stream control functions
        function reconnectStream() {
            if (player) {
                console.log('Reconnecting to stream...');
                player.load();
                player.play();
            }
        }

        function changeQuality(quality) {
            // Update UI
            document.querySelectorAll('.quality-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            console.log('Changing quality to:', quality);
            // In real implementation, this would switch to different stream URLs
            if (quality === 'auto') {
                player.src({
                    src: 'http://localhost:8080/war.m3u8',
                    type: 'application/x-mpegURL'
                });
            }
        }

        function toggleFullscreen() {
            if (player) {
                if (player.isFullscreen()) {
                    player.exitFullscreen();
                } else {
                    player.requestFullscreen();
                }
            }
        }

        function togglePictureInPicture() {
            if (player && document.pictureInPictureEnabled) {
                if (document.pictureInPictureElement) {
                    document.exitPictureInPicture();
                } else {
                    player.requestPictureInPicture();
                }
            }
        }

        function takeScreenshot() {
            if (player) {
                const canvas = document.createElement('canvas');
                canvas.width = player.videoWidth();
                canvas.height = player.videoHeight();
                const ctx = canvas.getContext('2d');
                ctx.drawImage(player.tech().el(), 0, 0, canvas.width, canvas.height);
                
                // Download screenshot
                const link = document.createElement('a');
                link.download = 'live-stream-screenshot-' + Date.now() + '.png';
                link.href = canvas.toDataURL();
                link.click();
            }
        }

        function shareStream() {
            if (navigator.share) {
                navigator.share({
                    title: 'PK Live News Stream',
                    text: 'Watch the latest breaking news live!',
                    url: window.location.href
                });
            } else {
                // Fallback - copy to clipboard
                navigator.clipboard.writeText(window.location.href);
                alert('Stream link copied to clipboard!');
            }
        }

        function reportIssue() {
            const issue = prompt('Please describe the issue you\'re experiencing:');
            if (issue) {
                console.log('Issue reported:', issue);
                alert('Thank you for reporting the issue. We\'ll look into it.');
            }
        }

        function refreshStream() {
            if (player) {
                player.src({
                    src: 'http://localhost:8080/war.m3u8',
                    type: 'application/x-mpegURL'
                });
                player.load();
                player.play();
            }
        }

        function showStreamError() {
            // Show error overlay
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-warning position-absolute top-50 start-50 translate-middle';
            errorDiv.style.zIndex = '1000';
            errorDiv.innerHTML = `
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Stream Connection Issue</h5>
                <p>Attempting to reconnect...</p>
            `;
            document.querySelector('.video-container').appendChild(errorDiv);
            
            // Remove after 3 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 3000);
        }

        // Utility functions
        function updateStreamTime() {
            const startTime = new Date('<?php echo date('Y-m-d H:i:s'); ?>');
            const now = new Date();
            const diff = now - startTime;
            const hours = Math.floor(diff / 3600000);
            const minutes = Math.floor((diff % 3600000) / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            
            const timeString = `Duration: ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            document.getElementById('streamTime').textContent = timeString;
        }

        function startViewerCountUpdate() {
            setInterval(() => {
                const currentCount = parseInt(document.getElementById('viewerCount').textContent.replace(',', ''));
                const change = Math.floor(Math.random() * 21) - 10; // Random change between -10 and +10
                const newCount = Math.max(100, currentCount + change);
                document.getElementById('viewerCount').textContent = newCount.toLocaleString();
            }, 5000);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (player) {
                switch(e.key) {
                    case ' ':
                        e.preventDefault();
                        if (player.paused()) {
                            player.play();
                        } else {
                            player.pause();
                        }
                        break;
                    case 'f':
                        if (!e.ctrlKey && !e.metaKey) {
                            e.preventDefault();
                            toggleFullscreen();
                        }
                        break;
                    case 'm':
                        if (!e.ctrlKey && !e.metaKey) {
                            e.preventDefault();
                            player.muted(!player.muted());
                        }
                        break;
                }
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (player) {
                player.dispose();
            }
        });
    </script>
</body>
</html>
