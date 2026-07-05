<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expand Short Articles</title>
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
                    <div class="card-header bg-secondary text-white">
                        <h2 class="mb-0">📝 Expand Short Articles</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        echo "<h4 class='mb-4'>Articles Under 300 Words</h4>";
                        
                        // Find articles with less than 300 words
                        $query = "SELECT id, title, content FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 50";
                        $result = mysqli_query($conn, $query);
                        
                        $short_articles = [];
                        while ($row = mysqli_fetch_assoc($result)) {
                            $word_count = str_word_count(strip_tags($row['content']));
                            if ($word_count < 300) {
                                $short_articles[] = [
                                    'id' => $row['id'],
                                    'title' => $row['title'],
                                    'words' => $word_count,
                                    'needed' => 300 - $word_count
                                ];
                            }
                        }
                        
                        if (count($short_articles) > 0) {
                            echo "<div class='alert alert-warning'>";
                            echo "<h4>Found " . count($short_articles) . " Articles Under 300 Words</h4>";
                            echo "<p>These articles need to be expanded to meet AdSense requirements.</p>";
                            echo "</div>";
                            
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-striped'>";
                            echo "<thead><tr><th>ID</th><th>Title</th><th>Current Words</th><th>Needed</th><th>Action</th></tr></thead>";
                            echo "<tbody>";
                            
                            foreach ($short_articles as $article) {
                                echo "<tr>";
                                echo "<td>{$article['id']}</td>";
                                echo "<td>" . htmlspecialchars($article['title']) . "</td>";
                                echo "<td><span class='badge bg-danger'>{$article['words']}</span></td>";
                                echo "<td><span class='badge bg-warning'>+{$article['needed']}</span></td>";
                                echo "<td><a href='admin/edit_news.php?id={$article['id']}' target='_blank' class='btn btn-sm btn-primary'>Edit</a></td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table></div>";
                            
                            echo "<div class='alert alert-info mt-4'>";
                            echo "<h5>How to Expand Articles:</h5>";
                            echo "<ol>";
                            echo "<li>Click the 'Edit' button for each article above</li>";
                            echo "<li>Add relevant details, quotes, or analysis</li>";
                            echo "<li>Include background information and context</li>";
                            echo "<li>Expand on key points with examples</li>";
                            echo "<li>Target 300+ words per article</li>";
                            echo "<li>Ensure content is original and valuable</li>";
                            echo "</ol>";
                            echo "</div>";
                            
                        } else {
                            echo "<div class='alert alert-success'>";
                            echo "<h4>✅ All Articles Are 300+ Words!</h4>";
                            echo "<p>No articles need expansion.</p>";
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
