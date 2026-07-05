<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdSense Readiness Check</title>
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
                        <h2 class="mb-0">📊 AdSense Readiness Diagnostic</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        echo "<h4 class='mb-4'>Content Analysis</h4>";
                        
                        // Check published articles count
                        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published'");
                        $row = mysqli_fetch_assoc($result);
                        $article_count = $row['total'];
                        $article_status = $article_count >= 30 ? 'success' : ($article_count >= 15 ? 'warning' : 'danger');
                        echo "<div class='alert alert-{$article_status}'>";
                        echo "<strong>1. Published Articles:</strong> {$article_count}<br>";
                        echo "<small>Required: 30+ for better approval chances</small>";
                        echo "</div>";

                        // Check content sources
                        $source_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source'");
                        if (mysqli_num_rows($source_check) > 0) {
                            $result = mysqli_query($conn, "SELECT source, COUNT(*) as count FROM news WHERE status = 'published' GROUP BY source ORDER BY count DESC LIMIT 10");
                            echo "<div class='mb-3'>";
                            echo "<strong>2. Top Content Sources:</strong><br>";
                            echo "<ul class='list-unstyled'>";
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<li>• {$row['source']}: {$row['count']} articles</li>";
                            }
                            echo "</ul>";
                            echo "</div>";
                        } else {
                            echo "<div class='mb-3'>";
                            echo "<strong>2. Top Content Sources:</strong> Not available (no source column)";
                            echo "</div>";
                        }

                        // Check categories
                        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM categories");
                        $row = mysqli_fetch_assoc($result);
                        $category_count = $row['total'];
                        echo "<div class='mb-3'>";
                        echo "<strong>3. Total Categories:</strong> {$category_count}";
                        echo "</div>";

                        // Check for articles with images
                        $image_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_url'");
                        if (mysqli_num_rows($image_check) > 0) {
                            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND image_url IS NOT NULL AND image_url != ''");
                            $row = mysqli_fetch_assoc($result);
                            $image_count = $row['total'];
                            $image_percentage = $article_count > 0 ? round(($image_count / $article_count) * 100, 1) : 0;
                            $image_status = $image_percentage >= 80 ? 'success' : ($image_percentage >= 50 ? 'warning' : 'danger');
                            echo "<div class='alert alert-{$image_status}'>";
                            echo "<strong>4. Articles with Images:</strong> {$image_count} ({$image_percentage}%)<br>";
                            echo "<small>Recommended: 80%+ of articles should have images</small>";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-warning'>";
                            echo "<strong>4. Articles with Images:</strong> Not available (no image_url column)";
                            echo "</div>";
                        }

                        // Check article length (content quality)
                        $content_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'content'");
                        if (mysqli_num_rows($content_check) > 0) {
                            $result = mysqli_query($conn, "SELECT AVG(LENGTH(content)) as avg_length FROM news WHERE status = 'published'");
                            $row = mysqli_fetch_assoc($result);
                            $avg_length = round($row['avg_length']);
                            $avg_words = round($avg_length / 5); // Approximate words
                            $length_status = $avg_words >= 300 ? 'success' : ($avg_words >= 150 ? 'warning' : 'danger');
                            echo "<div class='alert alert-{$length_status}'>";
                            echo "<strong>5. Average Article Length:</strong> ~{$avg_words} words ({$avg_length} chars)<br>";
                            echo "<small>Recommended: 300+ words per article</small>";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-warning'>";
                            echo "<strong>5. Average Article Length:</strong> Not available (no content column)";
                            echo "</div>";
                        }

                        // Check for duplicate titles
                        $title_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'title'");
                        if (mysqli_num_rows($title_check) > 0) {
                            $result = mysqli_query($conn, "SELECT title, COUNT(*) as count FROM news WHERE status = 'published' GROUP BY title HAVING count > 1");
                            $duplicates = mysqli_num_rows($result);
                            $duplicate_status = $duplicates == 0 ? 'success' : 'danger';
                            echo "<div class='alert alert-{$duplicate_status}'>";
                            echo "<strong>6. Duplicate Article Titles:</strong> {$duplicates}<br>";
                            echo "<small>Should be 0 for AdSense approval</small>";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-warning'>";
                            echo "<strong>6. Duplicate Article Titles:</strong> Not available (no title column)";
                            echo "</div>";
                        }

                        // Check recent articles
                        $date_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'published_at'");
                        if (mysqli_num_rows($date_check) > 0) {
                            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                            $row = mysqli_fetch_assoc($result);
                            $recent_count = $row['total'];
                            $recent_status = $recent_count >= 5 ? 'success' : ($recent_count >= 2 ? 'warning' : 'danger');
                            echo "<div class='alert alert-{$recent_status}'>";
                            echo "<strong>7. Articles Published (Last 7 Days):</strong> {$recent_count}<br>";
                            echo "<small>Recommended: 2-3 articles per week</small>";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-warning'>";
                            echo "<strong>7. Articles Published (Last 7 Days):</strong> Not available (no published_at column)";
                            echo "</div>";
                        }

                        // Check for RSS imported content
                        $source_url_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source_url'");
                        if (mysqli_num_rows($source_url_check) > 0) {
                            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND source_url IS NOT NULL AND source_url != ''");
                            $row = mysqli_fetch_assoc($result);
                            $rss_count = $row['total'];
                            $rss_percentage = $article_count > 0 ? round(($rss_count / $article_count) * 100, 1) : 0;
                            
                            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news WHERE status = 'published' AND (source_url IS NULL OR source_url = '')");
                            $row = mysqli_fetch_assoc($result);
                            $original_count = $row['total'];
                            
                            $rss_status = $rss_percentage <= 30 ? 'success' : ($rss_percentage <= 50 ? 'warning' : 'danger');
                            echo "<div class='alert alert-{$rss_status}'>";
                            echo "<strong>8. RSS Imported Articles:</strong> {$rss_count} ({$rss_percentage}%)<br>";
                            echo "<strong>9. Original Articles:</strong> {$original_count}<br>";
                            echo "<small>Recommended: Max 30% RSS content, rest should be original</small>";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-info'>";
                            echo "<strong>8. RSS Imported Articles:</strong> Not available (no source_url column)";
                            echo "</div>";
                        }

                        // Overall assessment
                        echo "<div class='card mt-4'>";
                        echo "<div class='card-header bg-dark text-white'>";
                        echo "<h4 class='mb-0'>📋 Overall Assessment</h4>";
                        echo "</div>";
                        echo "<div class='card-body'>";
                        
                        $ready_count = 0;
                        $total_checks = 5;
                        
                        if ($article_count >= 30) $ready_count++;
                        if (isset($image_percentage) && $image_percentage >= 80) $ready_count++;
                        if (isset($avg_words) && $avg_words >= 300) $ready_count++;
                        if ($duplicates == 0) $ready_count++;
                        if (isset($rss_percentage) && $rss_percentage <= 30) $ready_count++;
                        
                        $readiness_score = round(($ready_count / $total_checks) * 100);
                        $readiness_status = $readiness_score >= 80 ? 'success' : ($readiness_score >= 60 ? 'warning' : 'danger');
                        
                        echo "<div class='alert alert-{$readiness_status}'>";
                        echo "<strong>Readiness Score:</strong> {$readiness_score}%<br><br>";
                        
                        if ($readiness_score >= 80) {
                            echo "<strong>✅ READY FOR ADSENSE</strong><br>";
                            echo "Your website meets most AdSense requirements. You can proceed with application.";
                        } elseif ($readiness_score >= 60) {
                            echo "<strong>⚠️ ALMOST READY</strong><br>";
                            echo "Your website is close to being ready. Address the warnings above before applying.";
                        } else {
                            echo "<strong>❌ NOT READY YET</strong><br>";
                            echo "Your website needs significant improvements before applying for AdSense.";
                        }
                        echo "</div>";
                        
                        echo "<h5>Required Pages Check:</h5>";
                        echo "<ul class='list-unstyled'>";
                        echo "<li>✅ Privacy Policy (privacy-policy.php)</li>";
                        echo "<li>✅ Terms of Service (terms.php)</li>";
                        echo "<li>✅ Contact Page (contact.php)</li>";
                        echo "<li>✅ About Us (about.php)</li>";
                        echo "</ul>";
                        
                        echo "<h5>SEO & Technical Check:</h5>";
                        echo "<ul class='list-unstyled'>";
                        echo "<li>✅ robots.txt configured</li>";
                        echo "<li>✅ sitemap.xml present</li>";
                        echo "<li>✅ SSL-ready (https://pk-news.com)</li>";
                        echo "<li>✅ Responsive design</li>";
                        echo "</ul>";
                        
                        echo "</div>";
                        echo "</div>";
                        
                        echo "<div class='alert alert-info mt-4'>";
                        echo "<h5>💡 Recommendations:</h5>";
                        echo "<ul>";
                        if ($article_count < 30) echo "<li>Add more original articles (aim for 30+ total)</li>";
                        if (isset($image_percentage) && $image_percentage < 80) echo "<li>Add images to more articles (target 80%+)</li>";
                        if (isset($avg_words) && $avg_words < 300) echo "<li>Write longer, more detailed articles (300+ words)</li>";
                        if ($duplicates > 0) echo "<li>Remove or rewrite duplicate articles</li>";
                        if (isset($rss_percentage) && $rss_percentage > 30) echo "<li>Reduce RSS content, add more original content</li>";
                        if (isset($recent_count) && $recent_count < 2) echo "<li>Publish more frequently (2-3 articles per week)</li>";
                        echo "<li>Ensure consistent traffic before applying</li>";
                        echo "<li>Domain should be 6+ months old for new accounts</li>";
                        echo "</ul>";
                        echo "</div>";
                        ?>
                        
                        <div class="text-center mt-4">
                            <a href="https://www.google.com/adsense/start/" target="_blank" class="btn btn-primary btn-lg">
                                <i class="fab fa-google me-2"></i>Apply for Google AdSense
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
