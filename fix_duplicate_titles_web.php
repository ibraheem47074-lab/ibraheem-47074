<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Duplicate Titles</title>
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
                    <div class="card-header bg-info text-white">
                        <h2 class="mb-0">🔧 Fix Duplicate Article Titles</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        // Handle fix request
                        if (isset($_POST['fix_duplicates']) && $_POST['fix_duplicates'] === 'YES') {
                            $duplicate_query = "SELECT title, COUNT(*) as count, GROUP_CONCAT(id ORDER BY id) as ids FROM news WHERE status = 'published' GROUP BY title HAVING count > 1";
                            $result = mysqli_query($conn, $duplicate_query);
                            
                            $fixed_count = 0;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $ids = explode(',', $row['ids']);
                                $title = $row['title'];
                                
                                // Keep the first one, add suffix to others
                                for ($i = 1; $i < count($ids); $i++) {
                                    $update_id = $ids[$i];
                                    $new_title = $title . " (Copy " . ($i) . ")";
                                    
                                    $update = "UPDATE news SET title = ? WHERE id = ?";
                                    $stmt = mysqli_prepare($conn, $update);
                                    mysqli_stmt_bind_param($stmt, 'si', $new_title, $update_id);
                                    
                                    if (mysqli_stmt_execute($stmt)) {
                                        $fixed_count++;
                                    }
                                }
                            }
                            
                            echo "<div class='alert alert-success'>";
                            echo "<h4>✅ Successfully Fixed {$fixed_count} Duplicate Titles</h4>";
                            echo "<p>All duplicate titles have been updated with unique suffixes.</p>";
                            echo "<a href='adsense_check_web.php' class='btn btn-primary'>Run AdSense Check</a>";
                            echo "</div>";
                            exit;
                        }
                        
                        // Find duplicates
                        echo "<h4 class='mb-4'>Checking for Duplicate Titles</h4>";
                        
                        $duplicate_query = "SELECT title, COUNT(*) as count, GROUP_CONCAT(id ORDER BY id) as ids FROM news WHERE status = 'published' GROUP BY title HAVING count > 1";
                        $result = mysqli_query($conn, $duplicate_query);
                        
                        if (mysqli_num_rows($result) > 0) {
                            echo "<div class='alert alert-warning'>";
                            echo "<h4>Found " . mysqli_num_rows($result) . " Duplicate Titles</h4>";
                            echo "</div>";
                            
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-striped'>";
                            echo "<thead><tr><th>Title</th><th>Count</th><th>Article IDs</th></tr></thead>";
                            echo "<tbody>";
                            
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td>{$row['count']}</td>";
                                echo "<td>{$row['ids']}</td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table></div>";
                            
                            echo "<div class='alert alert-info mt-4'>";
                            echo "<h5>What this will do:</h5>";
                            echo "<ul>";
                            echo "<li>Keep the first article with each title unchanged</li>";
                            echo "<li>Add '(Copy 1)', '(Copy 2)' suffixes to duplicate articles</li>";
                            echo "<li>Make all titles unique for AdSense approval</li>";
                            echo "</ul>";
                            echo "</div>";
                            
                            echo "<form method='POST' class='mt-3'>";
                            echo "<input type='hidden' name='fix_duplicates' value='YES'>";
                            echo "<button type='submit' class='btn btn-info btn-lg' onclick='return confirm(\"This will modify duplicate titles. Continue?\")'>";
                            echo "🔧 Fix All Duplicate Titles";
                            echo "</button>";
                            echo "</form>";
                            
                        } else {
                            echo "<div class='alert alert-success'>";
                            echo "<h4>✅ No Duplicate Titles Found!</h4>";
                            echo "<p>All article titles are unique.</p>";
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
