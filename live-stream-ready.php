<?php
require_once 'config/database.php';
$page_title = 'Live Stream Ready';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ready to Go Live! - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .ready-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .ready-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.6s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .status-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .checklist {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }
        
        .checklist li {
            background: rgba(255, 255, 255, 0.1);
            margin: 0.5rem 0;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .checklist li:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        
        .checklist .check-icon {
            color: #28a745;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .pro-tips {
            background: rgba(255, 193, 7, 0.1);
            border-left: 4px solid #ffc107;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn-live {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-live:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        
        .btn-test {
            background: linear-gradient(45deg, #17a2b8, #138496);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }
        
        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
        }
        
        .stream-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .status-ready { background: #28a745; }
        .status-testing { background: #ffc107; }
        .status-live { background: #dc3545; }
    </style>
</head>
<body>
    <div class="ready-container">
        <div class="ready-card text-center">
            <div class="status-icon">
                <i class="fas fa-broadcast-tower"></i>
            </div>
            
            <h1 class="mb-4">Ready to Go Live!</h1>
            <p class="lead mb-4">Your custom stream is configured and ready to broadcast!</p>
            
            <div class="stream-status status-ready mb-4">
                <i class="fas fa-check-circle me-2"></i>
                Stream Configuration Complete
            </div>
            
            <h3 class="mb-3">Final Checklist</h3>
            <ul class="checklist">
                <li>
                    <i class="fas fa-check-circle check-icon"></i>
                    <span>RTMP server configured</span>
                </li>
                <li>
                    <i class="fas fa-check-circle check-icon"></i>
                    <span>OBS settings optimized</span>
                </li>
                <li>
                    <i class="fas fa-check-circle check-icon"></i>
                    <span>Player embed code ready</span>
                </li>
                <li>
                    <i class="fas fa-check-circle check-icon"></i>
                    <span>Stream monitoring active</span>
                </li>
            </ul>
            
            <div class="pro-tips">
                <h4><i class="fas fa-lightbulb me-2"></i>Pro Tips</h4>
                <ul class="text-start">
                    <li><strong>Test stream quality</strong> before going live</li>
                    <li><strong>Monitor viewer engagement</strong> during broadcast</li>
                    <li><strong>Have backup internet</strong> ready</li>
                    <li><strong>Record streams</strong> for later use</li>
                </ul>
            </div>
            
            <div class="action-buttons">
                <a href="live.php" class="btn btn-live text-white text-decoration-none">
                    <i class="fas fa-video me-2"></i>Start Live Stream
                </a>
                <a href="admin/live-stream-control.php" class="btn btn-test text-white text-decoration-none">
                    <i class="fas fa-cog me-2"></i>Stream Settings
                </a>
                <a href="index.php" class="btn btn-outline-light text-decoration-none">
                    <i class="fas fa-home me-2"></i>Back to Homepage
                </a>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Make sure your stream key and server URL are correctly configured in the admin panel
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
