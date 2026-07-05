<!-- Auto Slideshow Section -->
<section class="auto-slideshow py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="slideshow-container position-relative">
                    <div id="articleSlideshow" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                        <!-- Carousel Indicators -->
                        <div class="carousel-indicators">
                            <?php
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
                                $index = 0;
                                while ($article = mysqli_fetch_assoc($recent_result)) {
                                    $active_class = $index === 0 ? 'active' : '';
                                    echo '<button type="button" data-bs-target="#articleSlideshow" data-bs-slide-to="' . $index . '" class="' . $active_class . '" aria-current="true" aria-label="Slide ' . ($index + 1) . '"></button>';
                                    $index++;
                                }
                            } else {
                                echo '<button type="button" data-bs-target="#articleSlideshow" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>';
                            }
                            ?>
                        </div>
                        
                        <div class="carousel-inner">
                            <?php
                            // Get recent articles with images/videos
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
                                $index = 0;
                                while ($article = mysqli_fetch_assoc($recent_result)) {
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
                                            <img src="' . htmlspecialchars($thumbnail_url) . '" class="d-block w-100 carousel-image" alt="' . htmlspecialchars($article['title']) . '">
                                            <div class="video-play-button">
                                                <i class="fas fa-play"></i>
                                            </div>
                                            <div class="slideshow-overlay">
                                                <h5>' . htmlspecialchars($article['title']) . '</h5>
                                                <p class="mb-0">' . date('M j, Y', strtotime($article['published_at'])) . '</p>
                                            </div>
                                        </div>';
                                    } elseif (!empty($article['image'])) {
                                        // Image content
                                        $media_content = '
                                        <div class="position-relative image-thumbnail">
                                            <img src="' . htmlspecialchars($article['image']) . '" class="d-block w-100 carousel-image" alt="' . htmlspecialchars($article['title']) . '">
                                            <div class="slideshow-overlay">
                                                <h5>' . htmlspecialchars($article['title']) . '</h5>
                                                <p class="mb-0">' . date('M j, Y', strtotime($article['published_at'])) . '</p>
                                            </div>
                                        </div>';
                                    } else {
                                        // Skip articles without media
                                        $index++;
                                        continue;
                                    }
                                    
                                    echo '
                                    <div class="carousel-item ' . $active_class . '">
                                        ' . $media_content . '
                                    </div>';
                                    $index++;
                                }
                            } else {
                                // No articles with media found
                                echo '
                                <div class="carousel-item active">
                                    <div class="text-center py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
                                        <h3><i class="fas fa-newspaper me-2"></i>No Recent Articles</h3>
                                        <p>Articles with images or videos will appear here</p>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                        
                        <!-- Carousel Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#articleSlideshow" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true">&lsaquo;</span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#articleSlideshow" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true">&rsaquo;</span>
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
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    margin-bottom: 1.5rem;
    padding: 1rem;
}

.slideshow-container {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.carousel-item {
    height: 400px;
    min-height: 400px;
    position: relative;
}

.video-thumbnail, .image-thumbnail {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.carousel-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.video-play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50px;
    height: 50px;
    background: rgba(0,0,0,0.7);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.video-play-button:hover {
    background: rgba(0,0,0,0.9);
    transform: translate(-50%, -50%) scale(1.1);
}

.slideshow-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0) 100%);
    color: white;
    padding: 15px;
    text-align: left;
}

.slideshow-overlay h5 {
    font-size: 1rem;
    font-weight: bold;
    margin-bottom: 3px;
    line-height: 1.2;
}

.slideshow-overlay p {
    font-size: 0.8rem;
    margin: 0;
    opacity: 0.8;
}

.carousel-control-prev,
.carousel-control-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.3);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    transition: all 0.3s ease;
    z-index: 10;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    background: rgba(255,255,255,0.5);
    width: 45px;
    height: 45px;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    display: inline-block;
    width: 16px;
    height: 16px;
    background: no-repeat center center;
    background-size: 100% 100%;
}

/* Mobile/Android Optimizations */
@media (max-width: 768px) {
    .auto-slideshow {
        padding: 0.5rem;
        margin-bottom: 1rem;
    }

    .carousel-item {
        height: 250px;
        min-height: 250px;
    }

    .carousel-image {
        height: 250px;
        min-height: 250px;
    }

    .slideshow-overlay {
        padding: 12px;
        background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.7) 50%, rgba(0,0,0,0) 100%);
    }

    .slideshow-overlay h5 {
        font-size: 0.95rem;
        line-height: 1.3;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .slideshow-overlay p {
        font-size: 0.75rem;
        opacity: 0.9;
    }

    .video-play-button {
        width: 45px;
        height: 45px;
        font-size: 16px;
        background: rgba(0,0,0,0.8);
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 38px;
        height: 38px;
        font-size: 14px;
        background: rgba(255,255,255,0.25);
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        width: 42px;
        height: 42px;
        background: rgba(255,255,255,0.4);
    }

    .carousel-indicators {
        bottom: 10px;
        gap: 8px;
    }

    .carousel-indicators button {
        width: 10px;
        height: 10px;
        border: 2px solid rgba(255,255,255,0.8);
        background: rgba(255,255,255,0.4);
    }

    .carousel-indicators button.active {
        background: white;
        transform: scale(1.3);
        border-color: white;
    }
}

@media (max-width: 480px) {
    .auto-slideshow {
        padding: 0.25rem;
        margin-bottom: 0.75rem;
    }

    .slideshow-container {
        border-radius: 12px;
    }

    .carousel-item {
        height: 220px;
        min-height: 220px;
    }

    .carousel-image {
        height: 220px;
        min-height: 220px;
    }

    .slideshow-overlay {
        padding: 10px;
        background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.6) 50%, rgba(0,0,0,0) 100%);
    }

    .slideshow-overlay h5 {
        font-size: 0.9rem;
        line-height: 1.2;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .slideshow-overlay p {
        font-size: 0.7rem;
        opacity: 0.85;
    }

    .video-play-button {
        width: 40px;
        height: 40px;
        font-size: 14px;
        background: rgba(0,0,0,0.85);
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 32px;
        height: 32px;
        font-size: 12px;
        background: rgba(255,255,255,0.2);
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.35);
    }

    .carousel-indicators {
        bottom: 8px;
        gap: 6px;
    }

    .carousel-indicators button {
        width: 8px;
        height: 8px;
        border: 2px solid rgba(255,255,255,0.7);
        background: rgba(255,255,255,0.3);
    }

    .carousel-indicators button.active {
        background: white;
        transform: scale(1.4);
        border-color: white;
    }
}

/* Touch-friendly for mobile */
.auto-slideshow .carousel {
    touch-action: pan-y;
}

.auto-slideshow .carousel-control-prev,
.auto-slideshow .carousel-control-next {
    touch-action: manipulation;
}

/* Android-specific optimizations */
@media screen and (-webkit-min-device-pixel-ratio: 0) {
    .carousel-image {
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
    }
}

/* Ensure consistent aspect ratio across all devices */
.carousel-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

/* Prevent image distortion on Android */
@supports (-webkit-touch-callout: none) {
    .carousel-image {
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
    }
}

/* Carousel Indicators Styling */
.carousel-indicators {
    position: absolute;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 15;
    display: flex;
    gap: 8px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.carousel-indicators button {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    border: none;
    opacity: 0.7;
    transition: all 0.3s ease;
    cursor: pointer;
    margin: 0;
    padding: 0;
    display: block;
    text-indent: -999px;
    overflow: hidden;
    box-sizing: border-box;
}

.carousel-indicators button:hover {
    background: rgba(255, 255, 255, 0.8);
    opacity: 1;
    transform: scale(1.2);
}

.carousel-indicators button.active {
    background: white;
    opacity: 1;
    transform: scale(1.3);
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
}

/* Prevent text selection on mobile */
.auto-slideshow .slideshow-overlay {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
</style>

<!-- Slideshow JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const articleCarousel = document.getElementById("articleSlideshow");
    if (articleCarousel) {
        // Check if mobile device
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        const carouselOptions = {
            interval: isMobile ? 5000 : 4000, // Slower on mobile
            ride: "carousel",
            touch: true, // Enable touch swiping
            pause: "hover" // Pause on hover
        };
        
        // On mobile, add additional settings
        if (isMobile) {
            carouselOptions.wrap = true; // Allow wrapping
        }
        
        const carousel = new bootstrap.Carousel(articleCarousel, carouselOptions);
        
        // Add touch event listeners for better mobile experience
        if (isMobile) {
            let touchStartX = 0;
            let touchEndX = 0;
            
            articleCarousel.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, false);
            
            articleCarousel.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, false);
            
            function handleSwipe() {
                const swipeThreshold = 50;
                const diff = touchStartX - touchEndX;
                
                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        carousel.next(); // Swipe left, go to next
                    } else {
                        carousel.prev(); // Swipe right, go to previous
                    }
                }
            }
        }
    }
});
</script>
