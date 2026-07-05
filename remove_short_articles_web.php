<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Short Articles</title>
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
                        <h2 class="mb-0">⚠️ Remove Articles Under 300 Words</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        // Handle deletion request
                        if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'YES') {
                            $delete_query = "DELETE FROM news WHERE status = 'published' AND LENGTH(content) < 1500";
                            
                            if (mysqli_query($conn, $delete_query)) {
                                $deleted_count = mysqli_affected_rows($conn);
                                echo "<div class='alert alert-success'>";
                                echo "<h4>✅ Successfully Deleted {$deleted_count} Short Articles</h4>";
                                echo "<p>All articles under 300 words have been removed.</p>";
                                echo "<a href='adsense_check_web.php' class='btn btn-primary'>Run AdSense Check</a>";
                                echo "</div>";
                                exit;
                            } else {
                                echo "<div class='alert alert-danger'>";
                                echo "Error deleting articles: " . mysqli_error($conn);
                                echo "</div>";
                            }
                        }
                        
                        // Find articles with less than 300 words
                        echo "<h4 class='mb-4'>Articles Under 300 Words</h4>";
                        
                        $query = "SELECT id, title, content FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 100";
                        $result = mysqli_query($conn, $query);
                        
                        $short_articles = [];
                        while ($row = mysqli_fetch_assoc($result)) {
                            $word_count = str_word_count(strip_tags($row['content']));
                            if ($word_count < 300) {
                                $short_articles[] = [
                                    'id' => $row['id'],
                                    'title' => $row['title'],
                                    'words' => $word_count
                                ];
                            }
                        }
                        
                        if (count($short_articles) > 0) {
                            echo "<div class='alert alert-warning'>";
                            echo "<h4>Found " . count($short_articles) . " Articles Under 300 Words</h4>";
                            echo "<p>These articles will be permanently deleted:</p>";
                            echo "</div>";
                            
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-striped'>";
                            echo "<thead><tr><th>ID</th><th>Title</th><th>Word Count</th></tr></thead>";
                            echo "<tbody>";
                            
                            foreach ($short_articles as $article) {
                                echo "<tr>";
                                echo "<td>{$article['id']}</td>";
                                echo "<td>" . htmlspecialchars($article['title']) . "</td>";
                                echo "<td><span class='badge bg-danger'>{$article['words']} words</span></td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table></div>";
                            
                            echo "<div class='alert alert-danger mt-4'>";
                            echo "<h5>⚠️ WARNING: This action cannot be undone!</h5>";
                            echo "<p>All " . count($short_articles) . " articles above will be permanently deleted.</p>";
                            echo "<p><strong>Alternative:</strong> You can expand these articles instead of deleting them.</p>";
                            echo "</div>";
                            
                            echo "<div class='d-flex gap-2 mt-3'>";
                            echo "<form method='POST' class='flex-grow-1'>";
                            echo "<input type='hidden' name='confirm_delete' value='YES'>";
                            echo "<button type='submit' class='btn btn-danger btn-lg w-100' onclick='return confirm(\"Are you absolutely sure you want to delete these articles?\")'>";
                            echo "🗑️ Delete All Short Articles";
                            echo "</button>";
                            echo "</form>";
                            echo "<a href='expand_short_articles_web.php' class='btn btn-secondary btn-lg'>Expand Instead</a>";
                            echo "</div>";
                            
                        } else {
                            echo "<div class='alert alert-success'>";
                            echo "<h4>✅ All Articles Are 300+ Words!</h4>";
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
