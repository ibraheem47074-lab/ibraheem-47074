<?php
// Example of how to integrate ads into your pages
require_once 'config/database.php';
require_once 'includes/ad-templates.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ad Integration Example - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php render_ad_styles(); // Include ad CSS styles ?>
</head>
<body>
    <!-- Header with Banner Ad -->
    <header class="bg-primary text-white py-3">
        <div class="container">
            <h1>PK Live News</h1>
            <?php render_header_ad(); // Header Banner Ad (728x90) ?>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="container mt-4">
        <div class="row">
            <!-- Main Content -->
            <main class="col-md-8">
                <article>
                    <h2>Sample News Article</h2>
                    <p>This is where your main news content would go. The article content appears here with full text and images.</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                </article>
            </main>

            <!-- Sidebar with Rectangle Ad -->
            <aside class="col-md-4">
                <div class="sidebar-section">
                    <h4>Advertisement</h4>
                    <?php render_sidebar_ad(); // Sidebar Rectangle Ad (300x250) ?>
                </div>
                
                <div class="sidebar-section mt-4">
                    <h4>Latest News</h4>
                    <ul class="list-unstyled">
                        <li><a href="#">Latest news item 1</a></li>
                        <li><a href="#">Latest news item 2</a></li>
                        <li><a href="#">Latest news item 3</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>

    <!-- Footer with Banner Ads -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php render_footer_ad(); // Footer Banner Ad (728x90) ?>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <p>Developed for FYP Project</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Popup Ad (automatically shows after 5 seconds) -->
    <?php render_popup_ad(); // Popup Ad with timing controls ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
