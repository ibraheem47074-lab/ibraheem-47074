<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Applying Layout & Styling Fixes...</h1>";

// Create improved CSS file
$improved_css = "
/* PK Live News - Improved Layout & Styling */

/* ===== RESET & BASE STYLES ===== */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f8f9fa;
    overflow-x: hidden;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* ===== HEADER STYLES ===== */
.main-header {
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.header-right .btn {
    height: 40px;
    min-width: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

/* ===== NAVIGATION ===== */
.navbar {
    background: linear-gradient(135deg, #212529 0%, #343a40 100%) !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 0;
}

.navbar-nav {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

.navbar-nav .nav-link {
    font-weight: 500;
    padding: 12px 15px !important;
    border-radius: 8px;
    margin: 0 2px;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.navbar-nav .nav-link:hover {
    background: rgba(220, 53, 69, 0.1);
    transform: translateY(-2px);
    color: #dc3545 !important;
}

/* ===== BREAKING NEWS TICKER ===== */
.breaking-news-ticker {
    background: #dc3545;
    color: white;
    padding: 8px 0;
    overflow: hidden;
}

.breaking-label {
    background: #000;
    color: #fff;
    padding: 4px 12px;
    font-weight: bold;
    margin-right: 15px;
}

.breaking-news-scroll {
    display: inline-block;
    animation: scroll-left 20s linear infinite;
}

@keyframes scroll-left {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}

/* ===== NEWS CARDS ===== */
.news-card {
    background: #fff;
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    overflow: hidden;
    margin-bottom: 25px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.news-card .card-img-top {
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.news-card:hover .card-img-top {
    transform: scale(1.05);
}

.news-card .card-body {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.news-card .card-title {
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 10px;
    color: #212529;
}

.news-card .card-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}

.news-card .card-title a:hover {
    color: #dc3545;
}

.news-card .card-text {
    color: #6c757d;
    font-size: 0.95rem;
    line-height: 1.6;
    flex: 1;
}

/* ===== NEWS META ===== */
.news-meta {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.news-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.news-actions .btn {
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 15px;
    transition: all 0.3s ease;
}

/* ===== FEATURED NEWS ===== */
.featured-news {
    border: 3px solid #dc3545;
    transform: scale(1.02);
}

.featured-news .card-title {
    font-size: 1.3rem;
    color: #dc3545;
}

/* ===== SIDEBAR WIDGETS ===== */
.sidebar-widget {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.sidebar-widget h3 {
    color: #dc3545;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #dc3545;
}

.trending-item {
    display: flex;
    align-items: flex-start;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
    transition: all 0.3s ease;
}

.trending-item:hover {
    background: #f8f9fa;
    padding-left: 10px;
}

.trending-item:last-child {
    border-bottom: none;
}

.trending-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #dc3545;
    margin-right: 15px;
    min-width: 30px;
    text-align: center;
}

/* ===== WEATHER WIDGET ===== */
.advanced-weather-widget {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.weather-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.weather-temp {
    font-size: 2.5rem;
    font-weight: 700;
}

.weather-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 15px;
}

.detail-item {
    background: rgba(255,255,255,0.1);
    padding: 8px 12px;
    border-radius: 8px;
    text-align: center;
    backdrop-filter: blur(5px);
}

/* ===== PRODUCT CARDS ===== */
.product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.product-image {
    height: 200px;
    object-fit: cover;
    width: 100%;
}

.product-info {
    padding: 15px;
}

.product-title {
    font-weight: 600;
    margin-bottom: 8px;
    color: #212529;
}

.product-price {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.current-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #dc3545;
}

.original-price {
    text-decoration: line-through;
    color: #6c757d;
}

.discount-badge {
    background: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* ===== POLL WIDGET ===== */
.poll-widget {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.poll-question {
    font-weight: 600;
    margin-bottom: 15px;
    color: #212529;
}

.poll-option {
    margin-bottom: 10px;
}

.poll-bar {
    background: #e9ecef;
    border-radius: 20px;
    overflow: hidden;
    height: 30px;
    position: relative;
}

.poll-fill {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    transition: width 0.5s ease;
}

/* ===== FOOTER ===== */
.footer {
    background: #212529;
    color: #fff;
    padding: 40px 0 20px;
    margin-top: 50px;
}

.footer h5 {
    color: #dc3545;
    font-weight: 700;
    margin-bottom: 20px;
}

.footer a {
    color: #adb5bd;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer a:hover {
    color: #dc3545;
}

.footer-bottom {
    background: #000;
    padding: 15px 0;
    margin-top: 30px;
    border-top: 1px solid #495057;
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 768px) {
    .container {
        padding: 0 10px;
    }
    
    .header-right {
        gap: 5px;
    }
    
    .header-right .btn {
        height: 35px;
        min-width: 35px;
        padding: 6px 8px;
        font-size: 0.85rem;
    }
    
    .navbar-nav .nav-link {
        padding: 10px 12px !important;
        font-size: 0.9rem;
    }
    
    .news-card {
        margin-bottom: 20px;
    }
    
    .news-card .card-img-top {
        height: 150px;
    }
    
    .weather-temp {
        font-size: 2rem;
    }
    
    .weather-details {
        grid-template-columns: 1fr;
    }
    
    .product-card {
        margin-bottom: 15px;
    }
    
    .news-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .news-actions {
        width: 100%;
        justify-content: flex-start;
    }
}

@media (max-width: 576px) {
    .main-header h1 {
        font-size: 1.5rem;
    }
    
    .breaking-news-ticker {
        font-size: 0.85rem;
    }
    
    .news-card .card-body {
        padding: 15px;
    }
    
    .sidebar-widget {
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .weather-main {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
}

/* ===== LOADING STATES ===== */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #dc3545;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ===== BADGES ===== */
.badge {
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
}

.badge.bg-danger {
    background: #dc3545 !important;
}

.badge.bg-success {
    background: #28a745 !important;
}

.badge.bg-warning {
    background: #ffc107 !important;
    color: #212529 !important;
}

/* ===== BUTTONS ===== */
.btn {
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    padding: 8px 16px;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

/* ===== UTILITIES ===== */
.text-decoration-none:hover {
    text-decoration: underline !important;
}

.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
}

.shadow {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.rounded {
    border-radius: 8px !important;
}

.rounded-lg {
    border-radius: 12px !important;
}

/* ===== ANIMATIONS ===== */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse {
    animation: pulse 2s infinite;
}
";

// Write the improved CSS to file
file_put_contents('assets/css/style_improved.css', $improved_css);

echo "<h2>✓ Layout fixes applied!</h2>";

// Update the header to use improved CSS
$header_content = file_get_contents('includes/header.php');

// Replace the CSS link to use improved version
$header_content = str_replace(
    '<link rel="stylesheet" href="assets/css/style.css">',
    '<link rel="stylesheet" href="assets/css/style_improved.css">',
    $header_content
);

// Backup original header
file_put_contents('includes/header_backup.php', file_get_contents('includes/header.php'));

// Update header
file_put_contents('includes/header.php', $header_content);

echo "<p style='color: green;'>✓ Updated header with improved CSS</p>";

echo "<h2>What Was Fixed:</h2>";
echo "<ul>";
echo "<li>✅ News card alignment and spacing</li>";
echo "<li>✅ Navigation menu responsiveness</li>";
echo "<li>✅ Sidebar widget positioning</li>";
echo "<li>✅ Product card layout</li>";
echo "<li>✅ Weather widget styling</li>";
echo "<li>✅ Mobile responsive design</li>";
echo "<li>✅ Button and badge styling</li>";
echo "<li>✅ Loading states and animations</li>";
echo "</ul>";

echo "<hr>";
echo "<p style='color: green; font-weight: bold;'>✓ Layout and styling fixes complete!</p>";
echo "<p><a href='index.php' class='btn btn-primary btn-lg'>View Improved Website</a></p>";
echo "<p><a href='fix_layout_styling.php' class='btn btn-secondary'>Back to Diagnostic</a></p>";
?>
