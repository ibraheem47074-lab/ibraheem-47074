/**
 * Video Lightbox functionality for viewing full-screen videos
 * Supports YouTube, Vimeo, and direct video files
 */

class VideoLightbox {
    constructor() {
        this.currentIndex = 0;
        this.videos = [];
        this.modal = null;
        this.videoContainer = null;
        this.caption = null;
        this.loading = null;
        this.isOpen = false;
        
        this.init();
    }

    init() {
        this.createModal();
        this.attachEventListeners();
        this.scanForVideos();
    }

    createModal() {
        // Create modal HTML
        const modalHTML = `
            <div class="video-lightbox-modal" id="videoLightbox" style="display: none;">
                <span class="video-lightbox-close" id="videoLightboxClose">&times;</span>
                <span class="video-lightbox-prev" id="videoLightboxPrev" style="display: none;">&#10094;</span>
                <span class="video-lightbox-next" id="videoLightboxNext" style="display: none;">&#10095;</span>
                <div class="video-lightbox-content">
                    <div class="video-lightbox-loading" id="videoLightboxLoading" style="display: none;">
                        <div class="video-lightbox-spinner"></div>
                        <div>Loading video...</div>
                    </div>
                    <div class="video-container-modal" id="videoContainerModal" style="display: none;">
                        <!-- Video will be loaded here -->
                    </div>
                    <div class="video-lightbox-caption" id="videoLightboxCaption"></div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Get modal elements
        this.modal = document.getElementById('videoLightbox');
        this.videoContainer = document.getElementById('videoContainerModal');
        this.caption = document.getElementById('videoLightboxCaption');
        this.loading = document.getElementById('videoLightboxLoading');
        this.closeBtn = document.getElementById('videoLightboxClose');
        this.prevBtn = document.getElementById('videoLightboxPrev');
        this.nextBtn = document.getElementById('videoLightboxNext');
    }

    attachEventListeners() {
        // Close button
        this.closeBtn.addEventListener('click', () => this.close());
        
        // Navigation buttons
        this.prevBtn.addEventListener('click', () => this.navigate(-1));
        this.nextBtn.addEventListener('click', () => this.navigate(1));
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!this.isOpen) return;
            
            switch(e.key) {
                case 'Escape':
                    this.close();
                    break;
                case 'ArrowLeft':
                    this.navigate(-1);
                    break;
                case 'ArrowRight':
                    this.navigate(1);
                    break;
            }
        });
        
        // Close on background click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
    }

    scanForVideos() {
        // Find all video elements and video thumbnails
        const videoElements = document.querySelectorAll('.video-thumbnail, [data-video-url], [data-video-path]');
        
        videoElements.forEach((element, index) => {
            // Skip if already processed
            if (element.hasAttribute('data-video-processed')) return;
            
            const videoUrl = element.getAttribute('data-video-url') || 
                           element.querySelector('[data-video-url]')?.getAttribute('data-video-url');
            const videoPath = element.getAttribute('data-video-path') || 
                             element.querySelector('[data-video-path]')?.getAttribute('data-video-path');
            
            const videoSource = videoUrl || videoPath;
            
            if (!videoSource) return;
            
            // Mark as processed
            element.setAttribute('data-video-processed', 'true');
            
            // Add click event
            element.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const title = element.getAttribute('data-video-title') || 
                             element.querySelector('img')?.alt || 
                             'Video';
                this.open(videoSource, title, index, videoPath ? 'uploaded' : 'external', element);
            });
        });
    }

    extractVideoId(url) {
        // YouTube
        if (url.includes('youtube.com/watch?v=')) {
            const match = url.match(/[?&]v=([^&]+)/);
            return match ? match[1] : null;
        }
        
        if (url.includes('youtu.be/')) {
            return url.split('youtu.be/')[1]?.split('?')[0];
        }
        
        // Vimeo
        if (url.includes('vimeo.com/')) {
            const match = url.match(/vimeo\.com\/(\d+)/);
            return match ? match[1] : null;
        }
        
        return null;
    }

    generateVideoEmbed(url, videoType = 'external', autoplay = true) {
        // Handle uploaded videos
        if (videoType === 'uploaded') {
            return `<video controls ${autoplay ? 'autoplay' : ''} style="width: 100%; height: 500px;">
                    <source src="${url}" type="video/mp4">
                    <source src="${url}" type="video/webm">
                    <source src="${url}" type="video/ogg">
                    Your browser does not support the video tag.
                    </video>`;
        }
        
        // Handle external videos (existing logic)
        const videoId = this.extractVideoId(url);
        
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            if (videoId) {
                const autoplayParam = autoplay ? '?autoplay=1&rel=0&enablejsapi=1' : '?enablejsapi=1';
                return `<iframe src="https://www.youtube.com/embed/${videoId}${autoplayParam}" 
                        allowfullscreen 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        style="border: none; width: 100%; height: 100%;">
                        </iframe>`;
            }
        }
        
        if (url.includes('vimeo.com')) {
            if (videoId) {
                const autoplayParam = autoplay ? '?autoplay=1' : '';
                return `<iframe src="https://player.vimeo.com/video/${videoId}${autoplayParam}" 
                        allowfullscreen 
                        allow="autoplay; fullscreen; picture-in-picture">
                        </iframe>`;
            }
        }
        
        // Direct video file
        if (url.match(/\.(mp4|webm|ogg)$/i)) {
            return `<video controls autoplay>
                    <source src="${url}" type="video/mp4">
                    Your browser does not support the video tag.
                    </video>`;
        }
        
        // Fallback for other URLs
        return `<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Unable to embed video. 
                <a href="${url}" target="_blank" class="alert-link">Watch in new tab</a>
                </div>`;
    }

    open(videoUrl, caption = '', index = 0, videoType = 'external', clickedElement = null) {
        this.currentIndex = index;
        this.isOpen = true;
        this.lastViewedElement = clickedElement; // Store the element that was clicked
        
        // Show modal
        this.modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Show loading
        this.loading.style.display = 'block';
        this.videoContainer.innerHTML = '';
        
        // Set caption
        this.caption.textContent = caption;
        
        // Generate and load video embed
        setTimeout(() => {
            const videoEmbed = this.generateVideoEmbed(videoUrl, videoType);
            this.videoContainer.innerHTML = videoEmbed;
            this.videoContainer.style.display = 'block';
            this.loading.style.display = 'none';
        }, 500);
        
        // Update navigation buttons
        this.updateNavigation();
    }

    close() {
        this.isOpen = false;
        this.modal.classList.remove('show');
        document.body.style.overflow = '';
        
        // Clear video to stop playback
        this.videoContainer.innerHTML = '';
        this.videoContainer.style.display = 'none';
        this.caption.textContent = '';
        
        // Restore scroll position to the last viewed video
        if (this.lastViewedElement) {
            setTimeout(() => {
                this.lastViewedElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 100);
        }
    }

    navigate(direction) {
        // Find all video elements (both external and uploaded)
        const allVideos = Array.from(document.querySelectorAll('.video-thumbnail, [data-video-url], [data-video-path]'));
        
        if (allVideos.length === 0) return;
        
        // Calculate new index
        let newIndex = this.currentIndex + direction;
        
        // Wrap around
        if (newIndex < 0) {
            newIndex = allVideos.length - 1;
        } else if (newIndex >= allVideos.length) {
            newIndex = 0;
        }
        
        // Get new video element
        const newVideoElement = allVideos[newIndex];
        const videoUrl = newVideoElement.getAttribute('data-video-url') || 
                         newVideoElement.getAttribute('data-video-path');
        const videoTitle = newVideoElement.getAttribute('data-video-title') || 
                          newVideoElement.querySelector('img')?.alt || 
                          'Video';
        const videoType = newVideoElement.getAttribute('data-video-path') ? 'uploaded' : 'external';
        
        // Open new video
        this.open(videoUrl, videoTitle, newIndex, videoType);
    }

    updateNavigation() {
        // Find all video elements (both external and uploaded)
        const allVideos = document.querySelectorAll('.video-thumbnail, [data-video-url], [data-video-path]');
        
        // Hide navigation if only one video
        if (allVideos.length <= 1) {
            this.prevBtn.style.display = 'none';
            this.nextBtn.style.display = 'none';
        } else {
            this.prevBtn.style.display = 'flex';
            this.nextBtn.style.display = 'flex';
        }
    }

    // Public method to refresh video scanning (useful for dynamic content)
    refresh() {
        this.scanForVideos();
    }

    // Method to create video thumbnail from video URL
    createVideoThumbnail(videoUrl, title = '', imageUrl = null) {
        const videoId = this.extractVideoId(videoUrl);
        let thumbnailUrl = imageUrl;
        
        if (!thumbnailUrl) {
            if (videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
                thumbnailUrl = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
            } else if (videoUrl.includes('vimeo.com')) {
                // Vimeo thumbnail requires API, use placeholder
                thumbnailUrl = 'https://via.placeholder.com/480x360/000000/ffffff?text=Video';
            } else {
                thumbnailUrl = 'https://via.placeholder.com/480x360/000000/ffffff?text=Video';
            }
        }
        
        return `
            <div class="video-thumbnail" data-video-url="${videoUrl}" data-video-title="${title}">
                <img src="${thumbnailUrl}" alt="${title}" loading="lazy">
                <div class="video-play-button">
                    <i class="fas fa-play"></i>
                </div>
                <div class="video-duration">LIVE</div>
                <div class="video-quality-badge">HD</div>
            </div>
        `;
    }
}

// Initialize video lightbox when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.videoLightbox = new VideoLightbox();
});

// Also initialize for dynamic content
document.addEventListener('ContentLoaded', () => {
    if (window.videoLightbox) {
        window.videoLightbox.refresh();
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VideoLightbox;
}
