/**
 * Image Lightbox functionality for viewing full-size images
 * Supports keyboard navigation and touch gestures
 */

class ImageLightbox {
    constructor() {
        this.currentIndex = 0;
        this.images = [];
        this.modal = null;
        this.modalImage = null;
        this.caption = null;
        this.loading = null;
        this.isOpen = false;
        
        this.init();
    }

    init() {
        this.createModal();
        this.attachEventListeners();
        this.scanForImages();
    }

    createModal() {
        // Create modal HTML
        const modalHTML = `
            <div class="image-lightbox-modal" id="imageLightbox" style="display: none;">
                <span class="lightbox-close" id="lightboxClose">&times;</span>
                <span class="lightbox-prev" id="lightboxPrev" style="display: none;">&#10094;</span>
                <span class="lightbox-next" id="lightboxNext" style="display: none;">&#10095;</span>
                <div class="lightbox-content">
                    <div class="lightbox-loading" id="lightboxLoading" style="display: none;">
                        <div class="lightbox-spinner"></div>
                        <div>Loading...</div>
                    </div>
                    <img class="lightbox-image" id="lightboxImage" src="" alt="" style="display: none;">
                    <div class="lightbox-caption" id="lightboxCaption"></div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Get modal elements
        this.modal = document.getElementById('imageLightbox');
        this.modalImage = document.getElementById('lightboxImage');
        this.caption = document.getElementById('lightboxCaption');
        this.loading = document.getElementById('lightboxLoading');
        this.closeBtn = document.getElementById('lightboxClose');
        this.prevBtn = document.getElementById('lightboxPrev');
        this.nextBtn = document.getElementById('lightboxNext');
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
        
        // Touch gestures for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        this.modal.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        this.modal.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe(touchStartX, touchEndX);
        });
        
        // Image load events
        this.modalImage.addEventListener('load', () => {
            this.loading.style.display = 'none';
            this.modalImage.style.display = 'block';
        });
        
        this.modalImage.addEventListener('error', () => {
            this.loading.style.display = 'none';
            this.caption.textContent = 'Error loading image';
        });
    }

    handleSwipe(startX, endX) {
        const swipeThreshold = 50;
        const diff = startX - endX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                this.navigate(1); // Swipe left, go to next
            } else {
                this.navigate(-1); // Swipe right, go to previous
            }
        }
    }

    scanForImages() {
        // Find all images with lightbox capability
        const imageContainers = document.querySelectorAll('.image-container, .news-detail-image-container');
        const cardImages = document.querySelectorAll('.card-img-top');
        
        // Process image containers
        imageContainers.forEach((container, index) => {
            const img = container.querySelector('img');
            if (!img) return;
            
            // Skip if already has a button
            if (container.querySelector('.full-size-btn')) return;
            
            // Also make image clickable
            img.style.cursor = 'pointer';
            img.addEventListener('click', (e) => {
                e.preventDefault();
                this.open(img.src, img.alt || '', index);
            });
        });
        
        // Process standalone card images
        cardImages.forEach((img, index) => {
            // Skip if already in a container or has button
            if (img.closest('.image-container') || img.closest('.news-detail-image-container')) return;
            
            // Skip if inside a video thumbnail (video thumbnails have their own click handler)
            if (img.closest('.video-thumbnail')) return;
            
            // Create wrapper container
            const wrapper = document.createElement('div');
            wrapper.className = 'image-container';
            wrapper.style.position = 'relative';
            
            // Wrap the image
            img.parentNode.insertBefore(wrapper, img);
            wrapper.appendChild(img);
            
            // Full size button removed - images remain clickable
            
            // Make image clickable
            img.style.cursor = 'pointer';
            img.addEventListener('click', (e) => {
                e.preventDefault();
                this.open(img.src, img.alt || '', index);
            });
        });
    }

    open(imageSrc, caption = '', index = 0) {
        this.currentIndex = index;
        this.isOpen = true;
        
        // Show modal
        this.modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Show loading
        this.loading.style.display = 'block';
        this.modalImage.style.display = 'none';
        
        // Set image source
        this.modalImage.src = imageSrc;
        this.caption.textContent = caption;
        
        // Update navigation buttons
        this.updateNavigation();
    }

    close() {
        this.isOpen = false;
        this.modal.classList.remove('show');
        document.body.style.overflow = '';
        
        // Clear image
        this.modalImage.src = '';
        this.caption.textContent = '';
    }

    navigate(direction) {
        // Find all clickable images on the page
        const allContainers = Array.from(document.querySelectorAll('.image-container img, .news-detail-image-container img'));
        
        if (allContainers.length === 0) return;
        
        // Calculate new index
        let newIndex = this.currentIndex + direction;
        
        // Wrap around
        if (newIndex < 0) {
            newIndex = allContainers.length - 1;
        } else if (newIndex >= allContainers.length) {
            newIndex = 0;
        }
        
        // Get new image
        const newImage = allContainers[newIndex];
        if (newImage) {
            this.currentIndex = newIndex;
            this.open(newImage.src, newImage.alt || '', newIndex);
        }
    }

    updateNavigation() {
        // Find all clickable images
        const allImages = document.querySelectorAll('.image-container img, .news-detail-image-container img');
        
        // Hide navigation if only one image
        if (allImages.length <= 1) {
            this.prevBtn.style.display = 'none';
            this.nextBtn.style.display = 'none';
        } else {
            this.prevBtn.style.display = 'flex';
            this.nextBtn.style.display = 'flex';
        }
    }

    // Public method to refresh image scanning (useful for dynamic content)
    refresh() {
        this.scanForImages();
    }
}

// Initialize lightbox when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.imageLightbox = new ImageLightbox();
});

// Also initialize for dynamic content
document.addEventListener('ContentLoaded', () => {
    if (window.imageLightbox) {
        window.imageLightbox.refresh();
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ImageLightbox;
}
