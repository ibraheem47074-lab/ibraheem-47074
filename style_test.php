<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PK Live News - Style Test</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Test CSS Loading -->
    <style>
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #007bff;
            border-radius: 8px;
        }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
    </style>
    
    <!-- Custom CSS Files -->
    <link href="assets/css/style.css" rel="stylesheet" onerror="this.onerror=null;document.getElementById('style-status').innerHTML='<span class=\'status-error\'>❌ style.css FAILED to load</span>'">
    <link href="assets/css/live-tv.css" rel="stylesheet" onerror="this.onerror=null;document.getElementById('live-tv-status').innerHTML='<span class=\'status-error\'>❌ live-tv.css FAILED to load</span>'">
    <link href="assets/css/heatmap.css" rel="stylesheet" onerror="this.onerror=null;document.getElementById('heatmap-status').innerHTML='<span class=\'status-error\'>❌ heatmap.css FAILED to load</span>'">
    <link href="assets/css/weather.css" rel="stylesheet" onerror="this.onerror=null;document.getElementById('weather-status').innerHTML='<span class=\'status-error\'>❌ weather.css FAILED to load</span>'">
    <link href="assets/css/image-lightbox.css" rel="stylesheet" onerror="this.onerror=null;document.getElementById('image-lightbox-status').innerHTML='<span class=\'status-error\'>❌ image-lightbox.css FAILED to load</span>'">
    <link href="assets/css/video-lightbox.css" rel="stylesheet" onerror="this.onerror=null;document.getElementById('video-lightbox-status').innerHTML='<span class=\'status-error\'>❌ video-lightbox.css FAILED to load</span>'">
    <link href="assets/css/affiliate-products.css" rel="stylesheet" onerror="this.onerror=null;document.getElementById('affiliate-status').innerHTML='<span class=\'status-error\'>❌ affiliate-products.css FAILED to load</span>'">
    
    <script>
        // Test CSS loading
        window.onload = function() {
            var cssFiles = [
                'assets/css/style.css',
                'assets/css/live-tv.css',
                'assets/css/heatmap.css',
                'assets/css/weather.css',
                'assets/css/image-lightbox.css',
                'assets/css/video-lightbox.css',
                'assets/css/affiliate-products.css'
            ];
            
            cssFiles.forEach(function(file, index) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = file;
                link.onload = function() {
                    var statusId = file.replace('assets/css/', '').replace('.css', '-status');
                    var statusEl = document.getElementById(statusId);
                    if (statusEl && statusEl.innerHTML === '') {
                        statusEl.innerHTML = '<span class="status-ok">✅ ' + file + ' loaded successfully</span>';
                    }
                };
                link.onerror = function() {
                    var statusId = file.replace('assets/css/', '').replace('.css', '-status');
                    var statusEl = document.getElementById(statusId);
                    if (statusEl) {
                        statusEl.innerHTML = '<span class="status-error">❌ ' + file + ' FAILED to load</span>';
                    }
                };
                document.head.appendChild(link);
            });
        };
    </script>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">PK Live News - CSS Style Test</h1>
        
        <div class="test-section">
            <h3>CSS Files Loading Status</h3>
            <div id="style-status"></div>
            <div id="live-tv-status"></div>
            <div id="heatmap-status"></div>
            <div id="weather-status"></div>
            <div id="image-lightbox-status"></div>
            <div id="video-lightbox-status"></div>
            <div id="affiliate-status"></div>
        </div>
        
        <div class="test-section">
            <h3>Bootstrap Components Test</h3>
            <button class="btn btn-primary me-2">Primary Button</button>
            <button class="btn btn-success me-2">Success Button</button>
            <button class="btn btn-warning me-2">Warning Button</button>
            <button class="btn btn-danger">Danger Button</button>
            
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                Bootstrap is working correctly if you see styled buttons and this alert.
            </div>
        </div>
        
        <div class="test-section">
            <h3>Font Awesome Icons Test</h3>
            <i class="fas fa-home fa-2x text-primary me-3"></i>
            <i class="fas fa-newspaper fa-2x text-success me-3"></i>
            <i class="fas fa-tv fa-2x text-warning me-3"></i>
            <i class="fas fa-globe fa-2x text-info me-3"></i>
            <i class="fas fa-user fa-2x text-danger"></i>
            
            <p class="mt-3">If you see colored icons above, Font Awesome is working.</p>
        </div>
        
        <div class="test-section">
            <h3>Custom CSS Test</h3>
            <div class="notification-btn">
                <i class="fas fa-bell"></i> Notification Button
                <span class="notification-badge">3</span>
            </div>
            
            <div class="notification-dropdown mt-3">
                <div class="notification-item">
                    Test notification item
                </div>
            </div>
            
            <p class="mt-3">If you see styled notification elements, custom CSS is working.</p>
        </div>
        
        <div class="test-section">
            <h3>File Paths Test</h3>
            <p><strong>Current URL:</strong> <span id="current-url"></span></p>
            <p><strong>Base Path:</strong> <span id="base-path"></span></p>
            <p><strong>CSS Path Test:</strong> <a href="assets/css/style.css" target="_blank">assets/css/style.css</a></p>
            
            <script>
                document.getElementById('current-url').textContent = window.location.href;
                document.getElementById('base-path').textContent = window.location.pathname;
            </script>
        </div>
        
        <div class="test-section">
            <h3>Network Test</h3>
            <p>Check browser console (F12) for any CSS loading errors.</p>
            <p>Open Network tab and reload page to see if CSS files are loading properly.</p>
        </div>
        
        <div class="test-section">
            <h3>Recommended Fixes</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>If CSS files are not loading:</h5>
                    <ul>
                        <li>Check file permissions (should be 644)</li>
                        <li>Verify file paths are correct</li>
                        <li>Check .htaccess URL rewriting</li>
                        <li>Ensure assets directory exists</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>If styling is broken:</h5>
                    <ul>
                        <li>Clear browser cache</li>
                        <li>Check for CSS syntax errors</li>
                        <li>Verify Bootstrap is loading</li>
                        <li>Test with different browsers</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
