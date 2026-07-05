<?php
require_once 'config/database.php';

echo "PK Live News - Auto Slideshow Fix\n";
echo "=================================\n\n";

// Step 1: Get recent articles with images/videos
echo "1. Getting Recent Articles with Media\n";
echo "------------------------------------\n";

$recent_query = "SELECT n.*, c.name as category_name 
               FROM news n 
               LEFT JOIN categories c ON n.category_id = c.id 
               WHERE n.status = 'published' 
               AND (n.image IS NOT NULL AND n.image != '' 
                    OR n.video_url IS NOT NULL AND n.video_url != '' 
                    OR n.video_path IS NOT NULL AND n.video_path != '')
               AND n.published_at <= NOW()
               ORDER BY n.published_at DESC 
               LIMIT 10";

$recent_result = mysqli_query($conn, $recent_query);

if ($recent_result && mysqli_num_rows($recent_result) > 0) {
    echo "✅ Found " . mysqli_num_rows($recent_result) . " recent articles with media\n";
    
    $articles = [];
    while ($article = mysqli_fetch_assoc($recent_result)) {
        $articles[] = $article;
    }
} else {
    echo "❌ No recent articles with media found\n";
    $articles = [];
}

// Step 2: Create slideshow HTML
echo "\n2. Creating Slideshow HTML\n";
echo "---------------------------\n";

$slideshow_html = '
<!-- Auto Slideshow Section -->
<section class="auto-slideshow py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="slideshow-container position-relative">
                    <div id="articleSlideshow" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                        <div class="carousel-inner">';

if (!empty($articles)) {
    foreach ($articles as $index => $article) {
        $active_class = $index === 0 ? 'active' : '';
        $media_content = '';
        
        // Determine media type and generate content
        if (!empty($article['video_url'])) {
            // Video content
            $video_id = '';
            $thumbnail_url = '';
            
            if (strpos($article['video_url'], 'youtube.com') !== false || strpos($article['video_url'], 'youtu.be') !== false) {
                if (strpos($article['video_url'], 'youtube.com/watch?v=') !== false) {
                    $video_id = substr($article['video_url'], strpos($article['video_url'], 'v=') + 2);
                } elseif (strpos($article['video_url'], 'youtu.be/') !== false) {
                    $video_id = substr($article['video_url'], strpos($article['video_url'], 'youtu.be/') + 9);
                }
                $video_id = explode('?', $video_id)[0];
                $thumbnail_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
            } else {
                $thumbnail_url = $article['image'] ?? 'https://via.placeholder.com/800x450/000000/ffffff?text=Video';
            }
            
            $media_content = '
                            <div class="position-relative video-thumbnail" data-video-url="' . htmlspecialchars($article['video_url']) . '" data-video-title="' . htmlspecialchars($article['title']) . '">
                                <img src="' . htmlspecialchars($thumbnail_url) . '" class="d-block w-100" alt="' . htmlspecialchars($article['title']) . '" style="height: 400px; object-fit: cover;">
                                <div class="video-play-button">
                                    <i class="fas fa-play"></i>
                                </div>
                                <div class="video-quality-badge">VIDEO</div>
                                <div class="slideshow-overlay">
                                    <h5>' . htmlspecialchars($article['title']) . '</h5>
                                    <p class="mb-0">' . date('M j, Y', strtotime($article['published_at'])) . '</p>
                                </div>
                            </div>';
        } elseif (!empty($article['image'])) {
            // Image content
            $media_content = '
                            <div class="position-relative image-thumbnail">
                                <img src="' . htmlspecialchars($article['image']) . '" class="d-block w-100" alt="' . htmlspecialchars($article['title']) . '" style="height: 400px; object-fit: cover;">
                                <div class="slideshow-overlay">
                                    <h5>' . htmlspecialchars($article['title']) . '</h5>
                                    <p class="mb-0">' . date('M j, Y', strtotime($article['published_at'])) . '</p>
                                </div>
                            </div>';
        } else {
            // Skip articles without media
            continue;
        }
        
        $slideshow_html .= '
                            <div class="carousel-item ' . $active_class . '">
                                ' . $media_content . '
                            </div>';
    }
}

$slideshow_html .= '
                        </div>
                        
                        <!-- Carousel Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#articleSlideshow" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#articleSlideshow" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Slideshow CSS -->
<style>
.auto-slideshow {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    margin-bottom: 2rem;
}

.slideshow-container {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.carousel-item {
    height: 400px;
    position: relative;
}

.video-thumbnail, .image-thumbnail {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.video-play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    background: rgba(0,0,0,0.7);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.video-play-button:hover {
    background: rgba(0,0,0,0.9);
    transform: translate(-50%, -50%) scale(1.1);
}

.video-quality-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #ff4444;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.slideshow-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
    color: white;
    padding: 20px;
    text-align: left;
}

.slideshow-overlay h5 {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.slideshow-overlay p {
    font-size: 0.9rem;
    margin: 0;
}

.carousel-control-prev,
.carousel-control-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.3);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    transition: all 0.3s ease;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    background: rgba(255,255,255,0.5);
    width: 60px;
    height: 60px;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    display: inline-block;
    width: 20px;
    height: 20px;
    background: no-repeat center center;
    background-size: 100% 100%;
}
</style>

<!-- Slideshow JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const carousel = new bootstrap.Carousel(document.getElementById("articleSlideshow"), {
        interval: 4000, // 4 seconds
        ride: "carousel"
    });
});
</script>';

echo "✅ Slideshow HTML generated\n";
echo "✅ Auto-rotation set to 4 seconds\n";

// Step 3: Save the slideshow to a file
echo "\n3. Saving Slideshow Component\n";
echo "-----------------------------\n";

if (file_put_contents('slideshow_component.php', $slideshow_html)) {
    echo "✅ Slideshow component saved to slideshow_component.php\n";
} else {
    echo "❌ Failed to save slideshow component\n";
}

echo "\n=== Instructions ===\n";
echo "1. Open index.php\n";
echo "2. Replace the hero section with: <?php include 'slideshow_component.php'; ?>\n";
echo "3. Place this after the opening container and before the existing hero section\n";
echo "4. The slideshow will auto-rotate every 4 seconds\n";
echo "5. Only articles with images or videos will be included\n";
?>
