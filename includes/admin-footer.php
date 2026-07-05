<?php
// Admin Footer Include File
// This file contains the HTML footer for admin pages
// It's included by admin pages that need the footer structure

// Fix path for includes when called from admin directory
$basePath = dirname(__DIR__) . '/';
require_once $basePath . 'config/database.php';
require_once $basePath . 'config/helpers.php';
require_once $basePath . 'includes/language_functions.php';
?>
</main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>

    
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>Powered by <a href="https://www.pknews.com" target="_blank" class="text-light">PK Live News</a></p>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
