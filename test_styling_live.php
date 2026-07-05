<?php
// Live Styling Test Script for pk-news.com
// This script tests CSS, JavaScript, and responsive design

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PK-News.com - Live Styling Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .test-item { margin: 10px 0; padding: 10px; background: #f5f5f5; border-radius: 3px; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
        .responsive-test { border: 2px dashed #ccc; padding: 20px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>PK-News.com Live Styling Test</h1>
    <p>This test checks CSS, JavaScript, and responsive design on pk-news.com</p>

    <div class="test-section">
        <h2>CSS Loading Test</h2>
        
        <div class="test-item">
            <h3>Bootstrap CSS</h3>
            <?php
            $bootstrap_css = file_exists('assets/css/bootstrap-local.css');
            echo $bootstrap_css ? 
                '<span class="pass">PASS: Bootstrap CSS file exists</span>' : 
                '<span class="fail">FAIL: Bootstrap CSS missing</span>';
            ?>
        </div>

        <div class="test-item">
            <h3>Main CSS Files</h3>
            <?php
            $css_files = [
                'assets/css/style.css',
                'assets/css/responsive.css',
                'assets/css/android-optimizations.css'
            ];
            
            foreach ($css_files as $css) {
                $exists = file_exists($css);
                echo $exists ? 
                    "<span class='pass'>PASS: $css</span><br>" : 
                    "<span class='fail'>FAIL: $css missing</span><br>";
            }
            ?>
        </div>

        <div class="test-item">
            <h3>Font Awesome Icons</h3>
            <?php
            $fa_css = file_exists('assets/css/fontawesome-all.css') || 
                     file_exists('assets/css/all.css') ||
                     strpos(file_get_contents('includes/header.php'), 'fontawesome') !== false;
            echo $fa_css ? 
                '<span class="pass">PASS: Font Awesome loaded</span>' : 
                '<span class="warning">WARNING: Font Awesome not found</span>';
            ?>
        </div>
    </div>

    <div class="test-section">
        <h2>JavaScript Test</h2>
        
        <div class="test-item">
            <h3>jQuery</h3>
            <?php
            $jquery_exists = strpos(file_get_contents('includes/footer.php'), 'jquery') !== false;
            echo $jquery_exists ? 
                '<span class="pass">PASS: jQuery loaded</span>' : 
                '<span class="fail">FAIL: jQuery missing</span>';
            ?>
        </div>

        <div class="test-item">
            <h3>Bootstrap JS</h3>
            <?php
            $bootstrap_js = file_exists('assets/js/bootstrap.bundle.min.js');
            echo $bootstrap_js ? 
                '<span class="pass">PASS: Bootstrap JS exists</span>' : 
                '<span class="warning">WARNING: Bootstrap JS may be CDN</span>';
            ?>
        </div>

        <div class="test-item">
            <h3>Custom JS Files</h3>
            <?php
            $js_files = [
                'assets/js/config.js',
                'assets/js/main.js',
                'assets/js/news-interactions.js'
            ];
            
            foreach ($js_files as $js) {
                $exists = file_exists($js);
                echo $exists ? 
                    "<span class='pass'>PASS: $js</span><br>" : 
                    "<span class='warning'>WARNING: $js missing</span><br>";
            }
            ?>
        </div>
    </div>

    <div class="test-section">
        <h2>Responsive Design Test</h2>
        
        <div class="responsive-test">
            <h3>Viewport Meta Tag</h3>
            <?php
            $header_content = file_get_contents('includes/header.php');
            $viewport_exists = strpos($header_content, 'viewport') !== false;
            echo $viewport_exists ? 
                '<span class="pass">PASS: Viewport meta tag present</span>' : 
                '<span class="fail">FAIL: Viewport meta tag missing</span>';
            ?>
        </div>

        <div class="responsive-test">
            <h3>Media Queries in CSS</h3>
            <?php
            $css_content = '';
            if (file_exists('assets/css/style.css')) {
                $css_content = file_get_contents('assets/css/style.css');
            }
            $media_queries = strpos($css_content, '@media') !== false;
            echo $media_queries ? 
                '<span class="pass">PASS: Media queries found</span>' : 
                '<span class="warning">WARNING: No media queries detected</span>';
            ?>
        </div>

        <div class="responsive-test">
            <h3>Mobile Navigation</h3>
            <?php
            $mobile_nav = strpos($header_content, 'navbar-toggler') !== false || 
                         strpos($header_content, 'mobile') !== false;
            echo $mobile_nav ? 
                '<span class="pass">PASS: Mobile navigation elements found</span>' : 
                '<span class="warning">WARNING: Mobile navigation not detected</span>';
            ?>
        </div>
    </div>

    <div class="test-section">
        <h2>Image Optimization Test</h2>
        
        <div class="test-item">
            <h3>Uploads Directory</h3>
            <?php
            $uploads_writable = is_dir('uploads') && is_writable('uploads');
            echo $uploads_writable ? 
                '<span class="pass">PASS: Uploads directory writable</span>' : 
                '<span class="fail">FAIL: Uploads directory not writable</span>';
            ?>
        </div>

        <div class="test-item">
            <h3>Image Subdirectories</h3>
            <?php
            $img_dirs = ['uploads/news', 'uploads/thumbnails', 'uploads/categories'];
            foreach ($img_dirs as $dir) {
                $exists = is_dir($dir);
                echo $exists ? 
                    "<span class='pass'>PASS: $dir</span><br>" : 
                    "<span class='warning'>WARNING: $dir missing</span><br>";
            }
            ?>
        </div>
    </div>

    <div class="test-section">
        <h2>Performance Test</h2>
        
        <div class="test-item">
            <h3>File Compression (.htaccess)</h3>
            <?php
            $htaccess_content = file_get_contents('.htaccess');
            $compression = strpos($htaccess_content, 'mod_deflate') !== false;
            echo $compression ? 
                '<span class="pass">PASS: Gzip compression enabled</span>' : 
                '<span class="warning">WARNING: No compression detected</span>';
            ?>
        </div>

        <div class="test-item">
            <h3>Browser Caching</h3>
            <?php
            $caching = strpos($htaccess_content, 'mod_expires') !== false;
            echo $caching ? 
                '<span class="pass">PASS: Browser caching enabled</span>' : 
                '<span class="warning">WARNING: No caching headers</span>';
            ?>
        </div>
    </div>

    <div class="test-section">
        <h2>Manual Testing Checklist</h2>
        
        <h3>Visual Tests (Perform in Browser)</h3>
        <div class="test-item">
            <ol>
                <li>Visit <a href="https://pk-news.com" target="_blank">https://pk-news.com</a></li>
                <li>Check homepage layout and styling</li>
                <li>Test responsive design (resize browser)</li>
                <li>Test mobile view (dev tools mobile simulation)</li>
                <li>Check navigation menu functionality</li>
                <li>Test article pages and content display</li>
                <li>Check admin panel styling</li>
                <li>Test form styling and validation</li>
            </ol>
        </div>

        <h3>Interactive Elements</h3>
        <div class="test-item">
            <ul>
                <li>[ ] Dropdown menus work</li>
                <li>[ ] Modal windows open/close</li>
                <li>[ ] Form validation displays correctly</li>
                <li>[ ] Loading animations show</li>
                <li>[ ] Image lightbox functions</li>
                <li>[ ] Video player works</li>
                <li>[ ] Social sharing buttons</li>
                <li>[ ] Search functionality</li>
            </ul>
        </div>

        <h3>Performance Tests</h3>
        <div class="test-item">
            <ul>
                <li>[ ] Page loads within 3 seconds</li>
                <li>[ ] Images load progressively</li>
                <li>[ ] No JavaScript errors in console</li>
                <li>[ ] CSS loads without flash of unstyled content</li>
                <li>[ ] Smooth scrolling works</li>
                <li>[ ] Hover effects and transitions work</li>
            </ul>
        </div>
    </div>

    <div class="test-section">
        <h2>Next Steps</h2>
        <p><strong>After deploying to pk-news.com:</strong></p>
        <ol>
            <li>Run this test script: <code>https://pk-news.com/test_styling_live.php</code></li>
            <li>Complete the manual testing checklist above</li>
            <li>Test on real mobile devices</li>
            <li>Check performance with tools like GTmetrix or PageSpeed Insights</li>
            <li>Fix any styling issues found during testing</li>
        </ol>
    </div>

    <div class="test-section">
        <h2>Common Issues & Solutions</h2>
        <div class="test-item">
            <ul>
                <li><strong>CSS not loading:</strong> Check file paths and permissions</li>
                <li><strong>JavaScript errors:</strong> Check browser console</li>
                <li><strong>Responsive issues:</strong> Test viewport meta tag and media queries</li>
                <li><strong>Slow loading:</strong> Enable compression and caching</li>
                <li><strong>Mobile issues:</strong> Test touch events and viewport scaling</li>
            </ul>
        </div>
    </div>

</body>
</html>
