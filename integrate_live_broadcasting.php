<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Complete Live Streaming - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <style>
        .main-header {
            background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .stream-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .stream-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border-color: #ff0000;
        }
        .live-badge {
            background: #ff0000;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        .broadcast-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
        }
        .control-panel {
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class='bg-dark text-white'>
    <div class='container-fluid py-4'>
        
        <!-- Main Header -->
        <div class='main-header text-center'>
            <h1 class='display-4 mb-3'>
                <i class='fas fa-broadcast-tower me-3'></i>PK Live Broadcasting
            </h1>
            <p class='lead mb-4'>Stream live from anywhere - Real-time broadcasting platform</p>
            
            <div class='row justify-content-center'>
                <div class='col-md-8'>
                    <div class='d-flex justify-content-center flex-wrap gap-3'>
                        <a href='#broadcast' class='btn btn-light btn-lg'>
                            <i class='fas fa-video me-2'></i>Start Broadcasting
                        </a>
                        <a href='#watch' class='btn btn-outline-light btn-lg'>
                            <i class='fas fa-tv me-2'></i>Watch Streams
                        </a>
                        <a href='live.php' class='btn btn-outline-light btn-lg'>
                            <i class='fas fa-newspaper me-2'></i>News Channels
                        </a>
                    </div>
                </div>
            </div>
        </div>";

        // Live Broadcasting Section
        echo "<section id='broadcast' class='broadcast-section mb-5'>
            <div class='row'>
                <div class='col-lg-8'>
                    <div class='text-center mb-4'>
                        <h2><i class='fas fa-satellite-dish me-3'></i>Start Your Live Broadcast</h2>
                        <p>Stream from camera, screen, or upload video file</p>
                    </div>
                    
                    <div class='bg-black rounded p-4 mb-4' style='min-height: 400px;'>
                        <video id='broadcastVideo' class='w-100' controls autoplay muted style='border-radius: 10px; max-height: 400px; background: #000;'>
                            <source src='' type='video/mp4'>
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    
                    <div class='row text-center'>
                        <div class='col-md-3 col-6 mb-3'>
                            <div class='bg-secondary rounded p-3'>
                                <i class='fas fa-eye fa-2x mb-2'></i>
                                <h5 id='viewerCount'>0</h5>
                                <small>Viewers</small>
                            </div>
                        </div>
                        <div class='col-md-3 col-6 mb-3'>
                            <div class='bg-secondary rounded p-3'>
                                <i class='fas fa-clock fa-2x mb-2'></i>
                                <h5 id='streamDuration'>00:00</h5>
                                <small>Duration</small>
                            </div>
                        </div>
                        <div class='col-md-3 col-6 mb-3'>
                            <div class='bg-secondary rounded p-3'>
                                <i class='fas fa-signal fa-2x mb-2'></i>
                                <h5>HD</h5>
                                <small>Quality</small>
                            </div>
                        </div>
                        <div class='col-md-3 col-6 mb-3'>
                            <div class='bg-secondary rounded p-3'>
                                <i class='fas fa-microphone fa-2x mb-2'></i>
                                <h5>ON</h5>
                                <small>Audio</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class='col-lg-4'>
                    <div class='control-panel p-4'>
                        <h4 class='mb-4'><i class='fas fa-sliders-h me-2'></i>Broadcast Controls</h4>
                        
                        <div class='mb-4'>
                            <label class='form-label'><i class='fas fa-camera me-2'></i>Camera Stream</label>
                            <button onclick='startCameraBroadcast()' class='btn btn-success w-100 mb-2'>
                                <i class='fas fa-video me-2'></i>Start Camera
                            </button>
                        </div>
                        
                        <div class='mb-4'>
                            <label class='form-label'><i class='fas fa-desktop me-2'></i>Screen Share</label>
                            <button onclick='startScreenBroadcast()' class='btn btn-primary w-100 mb-2'>
                                <i class='fas fa-desktop me-2'></i>Share Screen
                            </button>
                        </div>
                        
                        <div class='mb-4'>
                            <label class='form-label'><i class='fas fa-file-video me-2'></i>Video File</label>
                            <input type='file' id='videoFile' accept='video/*' class='form-control mb-2'>
                            <button onclick='startFileBroadcast()' class='btn btn-warning w-100'>
                                <i class='fas fa-play me-2'></i>Play File
                            </button>
                        </div>
                        
                        <div class='mb-4'>
                            <label class='form-label'><i class='fas fa-link me-2'></i>External URL</label>
                            <input type='text' id='externalUrl' placeholder='Enter stream URL...' class='form-control mb-2'>
                            <button onclick='loadExternalBroadcast()' class='btn btn-info w-100'>
                                <i class='fas fa-external-link-alt me-2'></i>Load URL
                            </button>
                        </div>
                        
                        <div class='text-center'>
                            <button onclick='toggleFullscreen()' class='btn btn-outline-light'>
                                <i class='fas fa-expand me-2'></i>Fullscreen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>";

        // Live Streams Gallery
        echo "<section id='watch' class='mb-5'>
            <div class='text-center mb-4'>
                <h2><i class='fas fa-tv me-3'></i>Watch Live Streams</h2>
                <p>Join ongoing live broadcasts from around the world</p>
            </div>
            
            <div class='row' id='liveStreams'>";
            
            // Sample live streams with different sources
            $live_streams = [
                [
                    'name' => 'Main News Room',
                    'category' => 'News',
                    'viewers' => 5432,
                    'source' => 'camera',
                    'status' => 'live'
                ],
                [
                    'name' => 'Sports Commentary Live',
                    'category' => 'Sports', 
                    'viewers' => 3210,
                    'source' => 'screen',
                    'status' => 'live'
                ],
                [
                    'name' => 'Entertainment Show',
                    'category' => 'Entertainment',
                    'viewers' => 8765,
                    'source' => 'file',
                    'status' => 'live'
                ],
                [
                    'name' => 'Business Analysis',
                    'category' => 'Business',
                    'viewers' => 2341,
                    'source' => 'external',
                    'status' => 'live'
                ],
                [
                    'name' => 'Tech Review Stream',
                    'category' => 'Technology',
                    'viewers' => 4532,
                    'source' => 'camera',
                    'status' => 'live'
                ],
                [
                    'name' => 'International Report',
                    'category' => 'International',
                    'viewers' => 6789,
                    'source' => 'screen',
                    'status' => 'live'
                ],
                [
                    'name' => 'Music Concert Live',
                    'category' => 'Entertainment',
                    'viewers' => 9876,
                    'source' => 'external',
                    'status' => 'live'
                ],
                [
                    'name' => 'Gaming Stream',
                    'category' => 'Entertainment',
                    'viewers' => 7654,
                    'source' => 'screen',
                    'status' => 'live'
                ]
            ];
            
            foreach ($live_streams as $index => $stream) {
                $icon = $stream['source'] === 'camera' ? 'fa-camera' : 
                        ($stream['source'] === 'screen' ? 'fa-desktop' : 
                        ($stream['source'] === 'file' ? 'fa-file-video' : 'fa-globe'));
                
                echo "<div class='col-lg-3 col-md-4 col-sm-6 mb-4'>
                    <div class='card bg-secondary text-white h-100 stream-card' onclick='joinLiveStream({$index})'>
                        <div class='card-body'>
                            <div class='d-flex justify-content-between align-items-center mb-3'>
                                <h6 class='card-title mb-0'>{$stream['name']}</h6>
                                <span class='live-badge'>LIVE</span>
                            </div>
                            <div class='bg-dark rounded mb-3 d-flex align-items-center justify-content-center' style='height: 120px;'>
                                <i class='fas {$icon} fa-2x text-muted'></i>
                            </div>
                            <div class='d-flex justify-content-between align-items-center mb-2'>
                                <small class='text-muted'>{$stream['category']}</small>
                                <small><i class='fas fa-eye me-1'></i>{$stream['viewers']}</small>
                            </div>
                            <div class='progress mb-2' style='height: 4px;'>
                                <div class='progress-bar bg-danger' style='width: 100%'></div>
                            </div>
                            <small class='text-success'><i class='fas fa-circle me-1'></i>Streaming now</small>
                        </div>
                    </div>
                </div>";
            }
            
            echo "</div>
        </section>";
        ?>
        <script>
            let broadcastStream = null;
            let viewerCount = 0;
            let streamSeconds = 0;
            
            // Simulate viewer count increase
            setInterval(function() {
                viewerCount += Math.floor(Math.random() * 5);
                document.getElementById('viewerCount').textContent = viewerCount.toLocaleString();
            }, 2000);
            
            // Update stream duration
            setInterval(function() {
                streamSeconds++;
                var minutes = Math.floor(streamSeconds / 60);
                var seconds = streamSeconds % 60;
                document.getElementById('streamDuration').textContent = 
                    minutes + ':' + String(seconds).padStart(2, '0');
            }, 1000);
            
            function startCameraBroadcast() {
                navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                    .then(function(stream) {
                        var video = document.getElementById('broadcastVideo');
                        video.srcObject = stream;
                        broadcastStream = stream;
                        showNotification('Camera broadcast started!', 'success');
                    })
                    .catch(function(err) {
                        showNotification('Camera access denied', 'error');
                    });
            }
            
            function startScreenBroadcast() {
                navigator.mediaDevices.getDisplayMedia({ video: true, audio: true })
                    .then(function(stream) {
                        var video = document.getElementById('broadcastVideo');
                        video.srcObject = stream;
                        showNotification('Screen broadcast started!', 'success');
                    })
                    .catch(function(err) {
                        showNotification('Screen share failed', 'error');
                    });
            }
            
            function startFileBroadcast() {
                var file = document.getElementById('videoFile').files[0];
                if (file) {
                    var video = document.getElementById('broadcastVideo');
                    video.src = URL.createObjectURL(file);
                    video.play();
                    showNotification('File broadcast started!', 'success');
                }
            }
            
            function loadExternalBroadcast() {
                var url = document.getElementById('externalUrl').value;
                if (url) {
                    var video = document.getElementById('broadcastVideo');
                    video.src = url;
                    video.play();
                    showNotification('External stream loaded!', 'success');
                }
            }
            
            function toggleFullscreen() {
                var video = document.getElementById('broadcastVideo');
                if (video.requestFullscreen) {
                    video.requestFullscreen();
                }
            }
            
            function joinLiveStream(index) {
                var streams = <?php echo json_encode($live_streams); ?>;
                var stream = streams[index];
                showNotification('Joining: ' + stream.name, 'info');
                // In real implementation, this would switch to the selected stream
            }
            
            function showNotification(message, type) {
                var notification = document.createElement('div');
                notification.className = 'alert alert-' + (type == 'error' ? 'danger' : 'success') + ' position-fixed top-0 end-0 m-3';
                notification.style.zIndex = '9999';
                var iconClass = type == 'error' ? 'exclamation-circle' : 'check-circle';
                notification.innerHTML = '<i class=\"fas fa-' + iconClass + ' me-2\"></i>' + message;
                document.body.appendChild(notification);
                
                setTimeout(function() {
                    notification.remove();
                }, 3000);
            }
            
            // Initialize
            window.addEventListener('load', function() {
                showNotification('Live Broadcasting System Ready!', 'success');
            });
        </script>
    </div>
</body>
</html>
