<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Articles Without Images</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <!-- Navigation Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title mb-3">🔧 AdSense Fix Tools</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="fix_adsense_issues_web.php" class="btn btn-primary">1. Database Fixes</a>
                            <a href="remove_articles_without_images_web.php" class="btn btn-warning">2. Remove No-Image Articles</a>
                            <a href="remove_short_articles_web.php" class="btn btn-danger">3. Remove Short Articles</a>
                            <a href="fix_duplicate_titles_web.php" class="btn btn-info">4. Fix Duplicate Titles</a>
                            <a href="adsense_check_web.php" class="btn btn-success">5. Verify All Fixes</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h2 class="mb-0">⚠️ Remove Articles Without Images</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        // Check which image column to use
                        $image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_url'");
                        $use_image_url = mysqli_num_rows($image_check) > 0;
                        
                        if (!$use_image_url) {
                            $image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image'");
                            $use_image = mysqli_num_rows($image_check) > 0;
                        }
                        
                        // Handle deletion request
                        if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'YES') {
                            if ($use_image_url) {
                                $delete_query = "DELETE FROM news WHERE status = 'published' AND (image_url IS NULL OR image_url = '' OR image_url = 'uploads/')";
                            } elseif ($use_image) {
                                $delete_query = "DELETE FROM news WHERE status = 'published' AND (image IS NULL OR image = '' OR image = 'uploads/')";
                            }
                            
                            if (mysqli_query($conn, $delete_query)) {
                                $deleted_count = mysqli_affected_rows($conn);
                                echo "<div class='alert alert-success'>";
                                echo "<h4>✅ Successfully Deleted {$deleted_count} Articles</h4>";
                                echo "<p>All articles without images have been removed.</p>";
                                echo "<a href='adsense_check_web.php' class='btn btn-primary'>Run AdSense Check</a>";
                                echo "</div>";
                                exit;
                            } else {
                                echo "<div class='alert alert-danger'>";
                                echo "Error deleting articles: " . mysqli_error($conn);
                                echo "</div>";
                            }
                        }
                        
                        // Find articles without images
                        if ($use_image_url) {
                            $query = "SELECT id, title, image_url FROM news WHERE status = 'published' AND (image_url IS NULL OR image_url = '' OR image_url = 'uploads/')";
                        } elseif ($use_image) {
                            $query = "SELECT id, title, image FROM news WHERE status = 'published' AND (image IS NULL OR image = '' OR image = 'uploads/')";
                        } else {
                            echo "<div class='alert alert-danger'>";
                            echo "❌ No image column found in news table.";
                            echo "</div>";
                            exit;
                        }
                        
                        $result = mysqli_query($conn, $query);
                        $articles_without_images = [];
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            $articles_without_images[] = [
                                'id' => $row['id'],
                                'title' => $row['title']
                            ];
                        }
                        
                        if (count($articles_without_images) > 0) {
                            echo "<div class='alert alert-warning'>";
                            echo "<h4>Found " . count($articles_without_images) . " Articles Without Images</h4>";
                            echo "<p>These articles will be permanently deleted:</p>";
                            echo "</div>";
                            
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-striped'>";
                            echo "<thead><tr><th>ID</th><th>Title</th></tr></thead>";
                            echo "<tbody>";
                            
                            foreach ($articles_without_images as $article) {
                                echo "<tr>";
                                echo "<td>{$article['id']}</td>";
                                echo "<td>" . htmlspecialchars($article['title']) . "</td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table></div>";
                            
                            echo "<div class='alert alert-danger mt-4'>";
                            echo "<h5>⚠️ WARNING: This action cannot be undone!</h5>";
                            echo "<p>All " . count($articles_without_images) . " articles above will be permanently deleted.</p>";
                            echo "</div>";
                            
                            echo "<form method='POST' class='mt-3'>";
                            echo "<input type='hidden' name='confirm_delete' value='YES'>";
                            echo "<button type='submit' class='btn btn-danger btn-lg' onclick='return confirm(\"Are you absolutely sure you want to delete these articles?\")'>";
                            echo "🗑️ Delete All Articles Without Images";
                            echo "</button>";
                            echo "</form>";
                            
                        } else {
                            echo "<div class='alert alert-success'>";
                            echo "<h4>✅ All Published Articles Have Images!</h4>";
                            echo "<p>No articles need to be removed.</p>";
                            echo "<a href='adsense_check_web.php' class='btn btn-primary'>Run AdSense Check</a>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
