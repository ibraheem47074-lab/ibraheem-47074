// Affiliate Products JavaScript

// Track affiliate click
function trackAffiliateClick(productId) {
    // Click is already tracked server-side via affiliate-click.php
    // This function is for additional client-side tracking if needed
    console.log('Affiliate product clicked:', productId);
    
    // You can add Google Analytics or other tracking here
    if (typeof gtag !== 'undefined') {
        gtag('event', 'affiliate_click', {
            'event_category': 'ecommerce',
            'event_label': 'product_' + productId,
            'value': 1
        });
    }
}

// Lazy load product images
function initLazyLoading() {
    const images = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
}

// Product carousel/slider
function initProductCarousel() {
    const carousels = document.querySelectorAll('.products-carousel');
    
    carousels.forEach(carousel => {
        const container = carousel.querySelector('.products-grid');
        const prevBtn = carousel.querySelector('.carousel-prev');
        const nextBtn = carousel.querySelector('.carousel-next');
        const scrollAmount = 300;
        
        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', () => {
                container.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            });
            
            nextBtn.addEventListener('click', () => {
                container.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            });
        }
    });
}

// Product comparison functionality
function initProductComparison() {
    const compareBtns = document.querySelectorAll('.compare-btn');
    let selectedProducts = [];
    
    compareBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = btn.dataset.productId;
            const productName = btn.dataset.productName;
            
            if (selectedProducts.length >= 3) {
                alert('You can compare up to 3 products at a time.');
                return;
            }
            
            if (selectedProducts.find(p => p.id === productId)) {
                alert('This product is already in comparison.');
                return;
            }
            
            selectedProducts.push({ id: productId, name: productName });
            updateComparisonModal();
        });
    });
}

function updateComparisonModal() {
    const modal = document.getElementById('comparisonModal');
    if (!modal) return;
    
    const productList = modal.querySelector('.comparison-products');
    productList.innerHTML = '';
    
    selectedProducts.forEach(product => {
        const item = document.createElement('div');
        item.className = 'comparison-product-item';
        item.innerHTML = `
            <span>${product.name}</span>
            <button class="remove-comparison" data-id="${product.id}">×</button>
        `;
        productList.appendChild(item);
    });
    
    // Add remove functionality
    modal.querySelectorAll('.remove-comparison').forEach(btn => {
        btn.addEventListener('click', () => {
            const productId = btn.dataset.id;
            selectedProducts = selectedProducts.filter(p => p.id !== productId);
            updateComparisonModal();
        });
    });
    
    modal.style.display = selectedProducts.length > 0 ? 'block' : 'none';
}

// Category filter
function initCategoryFilter() {
    const categoryPills = document.querySelectorAll('.category-pill');
    
    categoryPills.forEach(pill => {
        pill.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Remove active class from all pills
            categoryPills.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked pill
            pill.classList.add('active');
            
            // Filter products (this would typically make an AJAX call)
            const category = pill.dataset.category;
            filterProducts(category);
        });
    });
}

function filterProducts(category) {
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        if (category === 'all' || product.dataset.category === category) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

// Search functionality
function initProductSearch() {
    const searchInput = document.getElementById('productSearch');
    if (!searchInput) return;
    
    var searchTimeout;
    
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value;
        
        searchTimeout = setTimeout(() => {
            searchProducts(query);
        }, 300);
    });
}

function searchProducts(query) {
    const products = document.querySelectorAll('.product-card');
    const lowerQuery = query.toLowerCase();
    
    products.forEach(product => {
        const title = product.querySelector('.product-title').textContent.toLowerCase();
        const description = product.querySelector('.product-description').textContent.toLowerCase();
        const brand = product.querySelector('.product-brand');
        const brandText = brand ? brand.textContent.toLowerCase() : '';
        
        if (title.includes(lowerQuery) || description.includes(lowerQuery) || brandText.includes(lowerQuery)) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

// Price range filter
function initPriceFilter() {
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    
    if (!minPrice || !maxPrice) return;
    
    [minPrice, maxPrice].forEach(input => {
        input.addEventListener('change', filterByPrice);
    });
}

function filterByPrice() {
    const minPrice = parseFloat(document.getElementById('minPrice').value) || 0;
    const maxPrice = parseFloat(document.getElementById('maxPrice').value) || Infinity;
    
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        const priceText = product.querySelector('.current-price').textContent;
        const price = parseFloat(priceText.replace(/[^0-9.]/g, ''));
        
        if (price >= minPrice && price <= maxPrice) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

// Sort functionality
function initProductSort() {
    const sortSelect = document.getElementById('productSort');
    if (!sortSelect) return;
    
    sortSelect.addEventListener('change', (e) => {
        const sortBy = e.target.value;
        sortProducts(sortBy);
    });
}

function sortProducts(sortBy) {
    const container = document.querySelector('.products-grid');
    if (!container) return;
    
    const products = Array.from(container.querySelectorAll('.product-card'));
    
    products.sort((a, b) => {
        switch (sortBy) {
            case 'price-low':
                const priceA = parseFloat(a.querySelector('.current-price').textContent.replace(/[^0-9.]/g, ''));
                const priceB = parseFloat(b.querySelector('.current-price').textContent.replace(/[^0-9.]/g, ''));
                return priceA - priceB;
                
            case 'price-high':
                const priceA2 = parseFloat(a.querySelector('.current-price').textContent.replace(/[^0-9.]/g, ''));
                const priceB2 = parseFloat(b.querySelector('.current-price').textContent.replace(/[^0-9.]/g, ''));
                return priceB2 - priceA2;
                
            case 'rating':
                const ratingA = parseFloat(a.querySelector('.rating-stars')?.textContent?.length || 0);
                const ratingB = parseFloat(b.querySelector('.rating-stars')?.textContent?.length || 0);
                return ratingB - ratingA;
                
            case 'name':
                const nameA = a.querySelector('.product-title').textContent;
                const nameB = b.querySelector('.product-title').textContent;
                return nameA.localeCompare(nameB);
                
            default:
                return 0;
        }
    });
    
    // Re-append sorted products
    products.forEach(product => container.appendChild(product));
}

// Initialize all functions when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initLazyLoading();
    initProductCarousel();
    initProductComparison();
    initCategoryFilter();
    initProductSearch();
    initPriceFilter();
    initProductSort();
});

// Add to wishlist functionality
function addToWishlist(productId) {
    let wishlist = JSON.parse(localStorage.getItem('affiliateWishlist') || '[]');
    
    if (!wishlist.includes(productId)) {
        wishlist.push(productId);
        localStorage.setItem('affiliateWishlist', JSON.stringify(wishlist));
        
        // Update UI
        const btn = document.querySelector(`[data-wishlist-btn="${productId}"]`);
        if (btn) {
            btn.classList.add('in-wishlist');
            btn.textContent = 'In Wishlist';
        }
        
        showNotification('Product added to wishlist!');
    } else {
        showNotification('Product is already in your wishlist.');
    }
}

function removeFromWishlist(productId) {
    let wishlist = JSON.parse(localStorage.getItem('affiliateWishlist') || '[]');
    wishlist = wishlist.filter(id => id !== productId);
    localStorage.setItem('affiliateWishlist', JSON.stringify(wishlist));
    
    // Update UI
    const btn = document.querySelector(`[data-wishlist-btn="${productId}"]`);
    if (btn) {
        btn.classList.remove('in-wishlist');
        btn.textContent = 'Add to Wishlist';
    }
    
    showNotification('Product removed from wishlist.');
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'affiliate-notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Export functions for global access
window.trackAffiliateClick = trackAffiliateClick;
window.addToWishlist = addToWishlist;
window.removeFromWishlist = removeFromWishlist;
