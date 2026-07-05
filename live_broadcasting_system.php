<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Live Broadcasting System - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <script src='https://cdn.jsdelivr.net/npm/webtorrent@latest/webtorrent.min.js'></script>
    <style>
        .broadcast-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
        }
        .live-indicator {
            animation: pulse 2s infinite;
            background: #ff0000;
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: bold;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        .stream-box {
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        .viewer-counter {
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            padding: 10px;
        }
    </style>
</head>
<body class='bg-dark text-white'>
    <div class='container-fluid py-4'>
        <div class='text-center mb-5'>
            <h1 class='display-4'>
                <i class='fas fa-broadcast-tower me-3'></i>Live Broadcasting System
            </h1>
            <p class='lead'>Stream live from anywhere - Real-time broadcasting platform</p>
        </div>";

        // Live Broadcasting Section
        echo "<div class='row mb-5'>
            <div class='col-lg-8'>
                <div class='broadcast-container'>
                    <div class='d-flex justify-content-between align-items-center mb-4'>
                        <h3><i class='fas fa-video me-2'></i>Live Stream</h3>
                        <div class='live-indicator'>
                            <i class='fas fa-circle me-2'></i>LIVE NOW
                        </div>
                    </div>
                    
                    <div class='stream-box p-4 mb-4'>
                        <video id='liveVideo' class='w-100' controls autoplay muted style='border-radius: 10px; max-height: 500px;'>
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    
                    <div class='row'>
                        <div class='col-md-6'>
                            <div class='viewer-counter text-center'>
                                <i class='fas fa-eye me-2'></i>
                                <span id='viewerCount'>1,234</span> Viewers
                            </div>
                        </div>
                        <div class='col-md-6'>
                            <div class='text-center'>
                                <i class='fas fa-clock me-2'></i>
                                <span id='streamTime'>00:00:00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class='col-lg-4'>
                <div class='card bg-secondary text-white'>
                    <div class='card-header bg-danger'>
                        <h5 class='mb-0'><i class='fas fa-sliders-h me-2'></i>Broadcast Controls</h5>
                    </div>
                    <div class='card-body'>
                        <div class='mb-4'>
                            <label class='form-label'><i class='fas fa-video me-2'></i>Start Your Stream</label>
                            <input type='file' id='videoFile' accept='video/*' class='form-control mb-2'>
                            <button onclick='startLocalStream()' class='btn btn-success w-100 mb-2'>
                                <i class='fas fa-play me-2'></i>Start Local Stream
                            </button>
                            <button onclick='startCameraStream()' class='btn btn-primary w-100 mb-2'>
                                <i class='fas fa-camera me-2'></i>Start Camera Stream
                            </button>
                            <button onclick='startScreenShare()' class='btn btn-warning w-100'>
                                <i class='fas fa-desktop me-2'></i>Share Screen
                            </button>
                        </div>
                        
                        <div class='mb-4'>
                            <label class='form-label'><i class='fas fa-link me-2'></i>External Stream URL</label>
                            <input type='text' id='streamUrl' placeholder='Enter stream URL...' class='form-control mb-2'>
                            <button onclick='loadExternalStream()' class='btn btn-info w-100'>
                                <i class='fas fa-external-link-alt me-2'></i>Load External Stream
                            </button>
                        </div>
                        
                        <div class='mb-4'>
                            <label class='form-label'><i class='fas fa-globe me-2'></i>Stream Quality</label>
                            <select id='quality' class='form-select'>
                                <option value='auto'>Auto</option>
                                <option value='1080p'>1080p HD</option>
                                <option value='720p'>720p</option>
                                <option value='480p'>480p</option>
                            </select>
                        </div>
                        
                        <div class='text-center'>
                            <button onclick='goFullscreen()' class='btn btn-outline-light'>
                                <i class='fas fa-expand me-2'></i>Fullscreen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>";

        // Multiple Streams Section
        echo "<div class='row mb-5'>
            <div class='col-12'>
                <div class='card bg-dark text-white'>
                    <div class='card-header bg-primary'>
                        <h4 class='mb-0'><i class='fas fa-th me-2'></i>Live Streams Gallery</h4>
                    </div>
                    <div class='card-body'>
                        <div class='row' id='streamsGallery'>";
                        
                        // Sample live streams
                        $sample_streams = [
                            ['name' => 'Main News Room', 'viewers' => 5432, 'category' => 'News'],
                            ['name' => 'Sports Commentary', 'viewers' => 3210, 'category' => 'Sports'],
                            ['name' => 'Entertainment Show', 'viewers' => 8765, 'category' => 'Entertainment'],
                            ['name' => 'Business Analysis', 'viewers' => 2341, 'category' => 'Business'],
                            ['name' => 'Tech Review', 'viewers' => 4532, 'category' => 'Technology'],
                            ['name' => 'International Report', 'viewers' => 6789, 'category' => 'International']
                        ];
                        
                        foreach ($sample_streams as $stream) {
                            echo "<div class='col-md-6 col-lg-4 mb-4'>
                                <div class='card bg-secondary text-white h-100'>
                                    <div class='card-body'>
                                        <div class='d-flex justify-content-between align-items-center mb-3'>
                                            <h6 class='card-title mb-0'>{$stream['name']}</h6>
                                            <span class='badge bg-danger'>LIVE</span>
                                        </div>
                                        <div class='stream-placeholder bg-dark rounded mb-3' style='height: 150px; display: flex; align-items: center; justify-content: center;'>
                                            <i class='fas fa-video fa-2x text-muted'></i>
                                        </div>
                                        <div class='d-flex justify-content-between align-items-center'>
                                            <small class='text-muted'>{$stream['category']}</small>
                                            <small><i class='fas fa-eye me-1'></i>{$stream['viewers']}</small>
                                        </div>
                                        <button onclick='joinStream(\"{$stream['name']}\")' class='btn btn-sm btn-outline-light w-100 mt-2'>
                                            <i class='fas fa-play me-1'></i>Join Stream
                                        </button>
                                    </div>
                                </div>
                            </div>";
                        }
                        
                        echo "</div>
                    </div>
                </div>
            </div>
        </div>";

        echo "<script>
            // Live Broadcasting JavaScript
            let localStream = null;
            let viewerCount = 1234;
            
            // Update viewer count
            setInterval(() => {
                viewerCount += Math.floor(Math.random() * 10) - 5;
                document.getElementById('viewerCount').textContent = viewerCount.toLocaleString();
            }, 3000);
            
            // Update stream time
            let seconds = 0;
            setInterval(() => {
                seconds++;
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const secs = seconds % 60;
                document.getElementById('streamTime').textContent = 
                    String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(secs).padStart(2, '0');
            }, 1000);
            
            function startLocalStream() {
                const file = document.getElementById('videoFile').files[0];
                if (file) {
                    const video = document.getElementById('liveVideo');
                    video.src = URL.createObjectURL(file);
                    video.play();
                    showNotification('Local stream started!', 'success');
                }
            }
            
            function startCameraStream() {
                navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                    .then(stream => {
                        const video = document.getElementById('liveVideo');
                        video.srcObject = stream;
                        localStream = stream;
                        showNotification('Camera stream started!', 'success');
                    })
                    .catch(err => {
                        showNotification('Camera access denied: ' + err.message, 'error');
                    });
            }
            
            function startScreenShare() {
                navigator.mediaDevices.getDisplayMedia({ video: true })
                    .then(stream => {
                        const video = document.getElementById('liveVideo');
                        video.srcObject = stream;
                        showNotification('Screen sharing started!', 'success');
                    })
                    .catch(err => {
                        showNotification('Screen share failed: ' + err.message, 'error');
                    });
            }
            
            function loadExternalStream() {
                const url = document.getElementById('streamUrl').value;
                if (url) {
                    const video = document.getElementById('liveVideo');
                    video.src = url;
                    video.play();
                    showNotification('External stream loaded!', 'success');
                }
            }
            
            function goFullscreen() {
                const video = document.getElementById('liveVideo');
                if (video.requestFullscreen) {
                    video.requestFullscreen();
                }
            }
            
            function joinStream(streamName) {
                showNotification('Joining stream: ' + streamName, 'info');
                // In real implementation, this would switch to the selected stream
            }
            
            function showNotification(message, type) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-' + (type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info') + ' position-fixed top-0 end-0 m-3';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = 
                    '<i class=\"fas fa-' + (type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle') + ' me-2\"></i>' +
                    message;
                document.body.appendChild(alertDiv);
                
                setTimeout(() => {
                    alertDiv.remove();
                }, 3000);
            }
            
            // Auto-start with a demo stream
            window.addEventListener('load', () => {
                showNotification('Live Broadcasting System Ready!', 'success');
            });
        </script>
    </div>
</body>
</html>";
?>
