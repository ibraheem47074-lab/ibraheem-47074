<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix AdSense Issues</title>
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
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">🔧 Database Structure Fixes</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        echo "<h4 class='mb-4'>Fixing Database Structure</h4>";
                        
                        // 1. Find and display duplicate titles
                        echo "<div class='mb-3'>";
                        echo "<strong>1. Checking for duplicate article titles...</strong><br>";
                        $duplicate_query = "SELECT title, COUNT(*) as count, GROUP_CONCAT(id) as ids FROM news WHERE status = 'published' GROUP BY title HAVING count > 1";
                        $result = mysqli_query($conn, $duplicate_query);
                        
                        if (mysqli_num_rows($result) > 0) {
                            echo "<span class='text-danger'>Found " . mysqli_num_rows($result) . " duplicate titles:</span><br>";
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "  - '{$row['title']}' appears {$row['count']} times (IDs: {$row['ids']})<br>";
                            }
                        } else {
                            echo "<span class='text-success'>✅ No duplicate titles found.</span>";
                        }
                        echo "</div>";

                        // 2. Check if source column exists
                        echo "<div class='mb-3'>";
                        echo "<strong>2. Checking for source column...</strong><br>";
                        $source_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source'");
                        if (mysqli_num_rows($source_check) > 0) {
                            echo "<span class='text-success'>✅ Source column exists.</span>";
                        } else {
                            echo "<span class='text-warning'>❌ Source column missing. Adding it...</span><br>";
                            $add_source = "ALTER TABLE news ADD COLUMN source VARCHAR(100) DEFAULT 'PK-LIVE' AFTER content";
                            if (mysqli_query($conn, $add_source)) {
                                echo "<span class='text-success'>✅ Source column added successfully.</span>";
                            } else {
                                echo "<span class='text-danger'>❌ Error adding source column: " . mysqli_error($conn) . "</span>";
                            }
                        }
                        echo "</div>";

                        // 3. Check if image_url column exists
                        echo "<div class='mb-3'>";
                        echo "<strong>3. Checking for image_url column...</strong><br>";
                        $image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_url'");
                        if (mysqli_num_rows($image_check) > 0) {
                            echo "<span class='text-success'>✅ image_url column exists.</span>";
                        } else {
                            echo "<span class='text-warning'>❌ image_url column missing. Adding it...</span><br>";
                            $image_col_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image'");
                            if (mysqli_num_rows($image_col_check) > 0) {
                                $add_image_url = "ALTER TABLE news ADD COLUMN image_url VARCHAR(500) AFTER image";
                                if (mysqli_query($conn, $add_image_url)) {
                                    echo "<span class='text-success'>✅ image_url column added. Copying data from image column...</span><br>";
                                    $copy_data = "UPDATE news SET image_url = image WHERE image IS NOT NULL AND image != ''";
                                    if (mysqli_query($conn, $copy_data)) {
                                        echo "<span class='text-success'>✅ Data copied successfully.</span>";
                                    }
                                } else {
                                    echo "<span class='text-danger'>❌ Error adding image_url column: " . mysqli_error($conn) . "</span>";
                                }
                            } else {
                                $add_image_url = "ALTER TABLE news ADD COLUMN image_url VARCHAR(500) AFTER content";
                                if (mysqli_query($conn, $add_image_url)) {
                                    echo "<span class='text-success'>✅ image_url column added successfully.</span>";
                                } else {
                                    echo "<span class='text-danger'>❌ Error adding image_url column: " . mysqli_error($conn) . "</span>";
                                }
                            }
                        }
                        echo "</div>";

                        // 4. Check articles with images after fix
                        echo "<div class='mb-3'>";
                        echo "<strong>4. Checking articles with images...</strong><br>";
                        $image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_url'");
                        if (mysqli_num_rows($image_check) > 0) {
                            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND image_url IS NOT NULL AND image_url != ''");
                            $row = mysqli_fetch_assoc($result);
                            $image_count = $row['total'];
                            
                            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published'");
                            $row = mysqli_fetch_assoc($result);
                            $total_count = $row['total'];
                            
                            $percentage = $total_count > 0 ? round(($image_count / $total_count) * 100, 1) : 0;
                            echo "Articles with images: <strong>{$image_count} ({$percentage}%)</strong>";
                        }
                        echo "</div>";

                        // 5. Update source for existing articles
                        echo "<div class='mb-3'>";
                        echo "<strong>5. Updating source for existing articles...</strong><br>";
                        $update_source = "UPDATE news SET source = 'PK-LIVE' WHERE source IS NULL OR source = ''";
                        if (mysqli_query($conn, $update_source)) {
                            $affected = mysqli_affected_rows($conn);
                            echo "<span class='text-success'>✅ Updated {$affected} articles with source.</span>";
                        }
                        echo "</div>";

                        // 6. Check average article length
                        echo "<div class='mb-3'>";
                        echo "<strong>6. Checking average article length...</strong><br>";
                        $content_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'content'");
                        if (mysqli_num_rows($content_check) > 0) {
                            $result = mysqli_query($conn, "SELECT AVG(LENGTH(content)) as avg_length FROM news WHERE status = 'published'");
                            $row = mysqli_fetch_assoc($result);
                            $avg_length = round($row['avg_length']);
                            $avg_words = round($avg_length / 5);
                            echo "Current average: <strong>~{$avg_words} words ({$avg_length} chars)</strong><br>";
                            echo "Target: <strong>300+ words</strong>";
                            if ($avg_words < 300) {
                                $needed = 300 - $avg_words;
                                echo "<br><span class='text-warning'>Need to add ~{$needed} more words per article on average.</span>";
                            }
                        }
                        echo "</div>";

                        echo "<div class='alert alert-info mt-4'>";
                        echo "<h5>Next Steps:</h5>";
                        echo "<ol>";
                        echo "<li><a href='fix_duplicate_titles_web.php'>Fix duplicate titles</a></li>";
                        echo "<li><a href='remove_articles_without_images_web.php'>Remove articles without images</a></li>";
                        echo "<li><a href='expand_short_articles_web.php'>Expand short articles to 300+ words</a></li>";
                        echo "<li><a href='adsense_check_web.php'>Run AdSense check to verify improvements</a></li>";
                        echo "</ol>";
                        echo "</div>";
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
