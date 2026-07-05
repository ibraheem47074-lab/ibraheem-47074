// PK Live News Main JavaScript

// Load configuration
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functionality
    initBreakingNews();
    initLiveStream();
    initPolls();
    initComments();
    initSearch();
    initLazyLoading();
    initNotifications();
});

// Notifications Functionality
function initNotifications() {
    // Initialize notification system
    console.log('Notifications initialized');
    
    // Check for notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        // Request permission for notifications
        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                console.log('Notification permission granted');
            }
        });
    }
}

// Breaking News Ticker
function initBreakingNews() {
    const ticker = document.querySelector('.breaking-news-scroll');
    if (ticker) {
        // Auto-refresh breaking news every 30 seconds
        setInterval(function() {
            fetchBreakingNews();
        }, 30000);
    }
}

function fetchBreakingNews() {
    // Use CONFIG baseUrl to ensure consistent HTTP/HTTPS handling
    const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const baseUrl = isLocalhost ? 
        'http://localhost/PK-LIVE%20NEWS' : 
        window.location.origin;
    
    console.log('Fetching breaking news from:', baseUrl + '/api/breaking-news.php');
    fetch(baseUrl + '/api/breaking-news.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            // Check if response is actually JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success && Array.isArray(data.data)) {
                updateBreakingNewsTicker(data.data);
            } else if (data && data.error) {
                console.warn('API error:', data.error);
            } else {
                console.warn('Invalid breaking news data format:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching breaking news:', error);
            // Show user-friendly error message
            const ticker = document.querySelector('.breaking-news-scroll marquee');
            if (ticker) {
                ticker.innerHTML = '<span class="text-warning">Unable to load breaking news</span>';
            }
        });
}

function updateBreakingNewsTicker(news) {
    const ticker = document.querySelector('.breaking-news-scroll marquee');
    if (ticker && news.length > 0) {
        ticker.innerHTML = news.map(item => 
            `<a href="news.php?slug=${item.slug}" class="text-white text-decoration-none me-4">${item.title}</a>`
        ).join('');
    }
}

// Live Stream Functionality
function initLiveStream() {
    const liveStatus = document.querySelector('.live-status');
    if (liveStatus) {
        checkLiveStatus();
        // Check live status every 10 seconds
        setInterval(checkLiveStatus, 10000);
    }
}

function checkLiveStatus() {
    const baseUrl = (typeof CONFIG !== 'undefined' && CONFIG.isDevelopment) ? 
        'http://localhost/PK-LIVE%20NEWS' : 
        (window.location.origin);
    
    fetch(baseUrl + '/api/live-status.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && typeof data === 'object') {
                updateLiveStatus(data);
            } else {
                console.warn('Invalid live status data format:', data);
                updateLiveStatus({ is_live: false, viewers: 0 });
            }
        })
        .catch(error => {
            console.error('Error checking live status:', error);
            // Set default offline status on error
            updateLiveStatus({ is_live: false, viewers: 0 });
        });
}

function updateLiveStatus(status) {
    const indicators = document.querySelectorAll('.live-indicator');
    const onAirElements = document.querySelectorAll('.on-air-indicator');
    
    if (status.is_live) {
        indicators.forEach(indicator => {
            indicator.style.backgroundColor = '#00ff00';
            indicator.style.display = 'inline-block';
        });
        onAirElements.forEach(element => {
            element.style.display = 'inline-block';
        });
    } else {
        indicators.forEach(indicator => {
            indicator.style.backgroundColor = '#ff0000';
            indicator.style.display = 'none';
        });
        onAirElements.forEach(element => {
            element.style.display = 'none';
        });
    }
    
    // Update viewer count
    const viewerCount = document.querySelector('.viewer-count');
    if (viewerCount) {
        viewerCount.textContent = status.viewers || 0;
    }
}

// Poll System
function initPolls() {
    const pollForms = document.querySelectorAll('.poll-form');
    pollForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitPoll(this);
        });
    });
}

function submitPoll(form) {
    const formData = new FormData(form);
    const pollId = formData.get('poll_id');
    const optionId = formData.get('option_id');
    
    const baseUrl = (typeof CONFIG !== 'undefined' && CONFIG.isDevelopment) ? 
        'http://localhost/PK-LIVE%20NEWS' : 
        (window.location.origin);
    
    fetch(baseUrl + '/api/vote-poll.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updatePollResults(pollId, data.results);
            showNotification('Thank you for voting!', 'success');
        } else {
            showNotification(data.message || 'Error voting', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting poll:', error);
        showNotification('Error submitting vote', 'error');
    });
}

function updatePollResults(pollId, results) {
    const pollContainer = document.querySelector(`#poll-${pollId}`);
    if (pollContainer) {
        results.forEach(result => {
            const optionBar = pollContainer.querySelector(`#option-${result.option_id} .poll-fill`);
            const optionText = pollContainer.querySelector(`#option-${result.option_id} .poll-text`);
            
            if (optionBar && optionText) {
                optionBar.style.width = result.percentage + '%';
                optionText.textContent = `${result.option_text} (${result.percentage}%, ${result.votes} votes)`;
            }
        });
        
        // Disable form after voting
        const form = pollContainer.querySelector('.poll-form');
        if (form) {
            form.style.display = 'none';
        }
    }
}

// Comments System
function initComments() {
    const commentForms = document.querySelectorAll('.comment-form');
    commentForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitComment(this);
        });
    });
}

function submitComment(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Get news_id from form data attribute
    const newsId = form.dataset.newsId;
    
    if (!newsId) {
        showNotification('Error: News ID not found', 'error');
        return;
    }
    
    // Prepare data object
    const data = {
        news_id: parseInt(newsId),
        comment: formData.get('comment')
    };
    
    // Validate comment
    if (!data.comment || data.comment.trim() === '') {
        showNotification('Please enter a comment', 'error');
        return;
    }
    
    // Show loading state
    submitBtn.innerHTML = '<span class="loading-spinner"></span> Posting...';
    submitBtn.disabled = true;
    
    const baseUrl = (typeof CONFIG !== 'undefined' && CONFIG.isDevelopment) ? 
        'http://localhost/PK-LIVE%20NEWS' : 
        (window.location.origin);
    
    fetch(baseUrl + '/api/submit-comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addCommentToList(data.comment);
            form.reset();
            showNotification('Comment posted successfully!', 'success');
        } else {
            showNotification(data.message || 'Error posting comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error submitting comment:', error);
        showNotification('Error posting comment', 'error');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function addCommentToList(comment) {
    const commentsList = document.querySelector('.comments-list');
    if (commentsList) {
        const commentHtml = `
            <div class="comment-item">
                <div class="comment-author">${comment.name}</div>
                <div class="comment-date">${formatDate(comment.created_at)}</div>
                <div class="comment-content">${comment.comment}</div>
            </div>
        `;
        commentsList.insertAdjacentHTML('afterbegin', commentHtml);
    }
}

// Search Functionality
function initSearch() {
    const searchInput = document.querySelector('input[name="q"]');
    const searchResults = document.querySelector('.search-results');
    
    if (searchInput && searchResults) {
        var searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 3) {
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 500);
            } else {
                searchResults.style.display = 'none';
            }
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }
}

function performSearch(query) {
    const baseUrl = (typeof CONFIG !== 'undefined' && CONFIG.isDevelopment) ? 
        'http://localhost/PK-LIVE%20NEWS' : 
        (window.location.origin);
    
    fetch(`${baseUrl}/api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && Array.isArray(data)) {
                displaySearchResults(data);
            } else {
                console.warn('Invalid search results format:', data);
                displaySearchResults([]);
            }
        })
        .catch(error => {
            console.error('Error searching:', error);
            // Show error message to user
            const searchResults = document.querySelector('.search-results');
            if (searchResults) {
                searchResults.innerHTML = '<div class="p-3 text-danger">Search unavailable. Please try again.</div>';
                searchResults.style.display = 'block';
            }
        });
}

function displaySearchResults(results) {
    const searchResults = document.querySelector('.search-results');
    if (searchResults) {
        if (results.length > 0) {
            const resultsHtml = results.map(result => `
                <div class="search-result-item">
                    <h6><a href="news.php?slug=${result.slug}">${highlightSearchTerm(result.title, searchQuery)}</a></h6>
                    <p class="text-muted">${result.excerpt}</p>
                </div>
            `).join('');
            searchResults.innerHTML = resultsHtml;
            searchResults.style.display = 'block';
        } else {
            searchResults.innerHTML = '<div class="p-3 text-muted">No results found</div>';
            searchResults.style.display = 'block';
        }
    }
}

function highlightSearchTerm(text, term) {
    const regex = new RegExp(`(${term})`, 'gi');
    return text.replace(regex, '<span class="search-highlight">$1</span>');
}

// Lazy Loading for Images
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification-toast position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-2" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Share Functions
function toggleShareMenu(newsId) {
    const menu = document.getElementById(`share-menu-${newsId}`);
    if (menu) {
        menu.classList.toggle('d-none');
        
        // Position the menu below the share button
        const shareBtn = menu.parentElement.querySelector('.action-btn[onclick*="toggleShareMenu"]');
        if (shareBtn && menu.classList.contains('d-none') === false) {
            const rect = shareBtn.getBoundingClientRect();
            const parentRect = menu.parentElement.getBoundingClientRect();
            menu.style.top = (rect.bottom - parentRect.top + 5) + 'px';
            menu.style.left = (rect.left - parentRect.left) + 'px';
        }
    }
}

// Close share menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-btn[onclick*="toggleShareMenu"]') && !e.target.closest('.share-menu')) {
        document.querySelectorAll('.share-menu').forEach(menu => {
            menu.classList.add('d-none');
        });
    }
});

function shareOnFacebook(url, title) {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter(url, title) {
    window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank', 'width=600,height=400');
}

function shareOnWhatsApp(url, title) {
    window.open(`https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`, '_blank');
}

function shareOnLinkedIn(url, title) {
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
}

function shareOnTelegram(url, title) {
    window.open(`https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank');
}

function shareViaEmail(url, title) {
    const subject = encodeURIComponent(title);
    const body = encodeURIComponent(`Check out this article: ${title}\n\n${url}`);
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
}

function shareNews(url, title) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).catch(err => console.log('Share canceled'));
    } else {
        copyToClipboard(url);
        showToast('Link copied to clipboard!', 'success');
    }
}

// Utility Functions
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) { // Less than 1 minute
        return 'Just now';
    } else if (diff < 3600000) { // Less than 1 hour
        return Math.floor(diff / 60000) + ' minutes ago';
    } else if (diff < 86400000) { // Less than 1 day
        return Math.floor(diff / 3600000) + ' hours ago';
    } else if (diff < 604800000) { // Less than 1 week
        return Math.floor(diff / 86400000) + ' days ago';
    } else {
        return date.toLocaleDateString();
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Link copied to clipboard!', 'success');
    }).catch(() => {
        showNotification('Failed to copy link', 'error');
    });
}

// Print Article
function printArticle() {
    window.print();
}

// Font Size Controls
function changeFontSize(action) {
    const content = document.querySelector('.news-detail-content');
    if (content) {
        const currentSize = parseFloat(window.getComputedStyle(content).fontSize);
        let newSize;
        
        switch(action) {
            case 'increase':
                newSize = currentSize + 2;
                break;
            case 'decrease':
                newSize = currentSize - 2;
                break;
            case 'reset':
                newSize = 16; // Default size
                break;
        }
        
        content.style.fontSize = newSize + 'px';
        localStorage.setItem('articleFontSize', newSize);
    }
}

// Load saved font size
document.addEventListener('DOMContentLoaded', function() {
    const savedFontSize = localStorage.getItem('articleFontSize');
    if (savedFontSize) {
        const content = document.querySelector('.news-detail-content');
        if (content) {
            content.style.fontSize = savedFontSize + 'px';
        }
    }
});

// Keyboard Shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    }
});

// Theme Toggle
function toggleTheme() {
    document.body.classList.toggle('dark-mode');
    const isDarkMode = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDarkMode);
    
    // Update theme toggle button
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        const icon = themeToggle.querySelector('i');
        if (icon) {
            icon.className = isDarkMode ? 'fas fa-sun' : 'fas fa-moon';
        }
    }
}

// Load saved theme
document.addEventListener('DOMContentLoaded', function() {
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
        const themeToggle = document.querySelector('.theme-toggle i');
        if (themeToggle) {
            themeToggle.className = 'fas fa-sun';
        }
    }
});

// Smooth Scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const href = this.getAttribute('href');
        // Validate href before using as selector
        if (href && href !== '#' && href.length > 1) {
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// Auto-refresh for live content
function initAutoRefresh() {
    const liveElements = document.querySelectorAll('[data-auto-refresh]');
    liveElements.forEach(element => {
        const interval = element.dataset.autoRefresh;
        setInterval(() => {
            refreshElement(element);
        }, interval);
    });
}

function refreshElement(element) {
    const url = element.dataset.refreshUrl;
    if (url) {
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                if (html && html.trim()) {
                    element.innerHTML = html;
                } else {
                    console.warn('Empty response for element refresh');
                }
            })
            .catch(error => {
                console.error('Error refreshing element:', error);
                // Show error indicator
                element.innerHTML = '<div class="text-warning">Content unavailable</div>';
            });
    }
}

// Initialize auto-refresh
document.addEventListener('DOMContentLoaded', initAutoRefresh);

// Real-time date updates
function updateRealTimeDates() {
    const dateElements = document.querySelectorAll('.realtime-date');
    dateElements.forEach(element => {
        const date = element.dataset.date;
        if (date) {
            element.textContent = formatRealTimeDate(date);
        }
    });
}

function formatRealTimeDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + ' minutes ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + ' hours ago';
    if (diff < 604800000) return Math.floor(diff / 86400000) + ' days ago';
    return date.toLocaleDateString();
}

// Update real-time dates every minute
setInterval(updateRealTimeDates, 60000);
document.addEventListener('DOMContentLoaded', updateRealTimeDates);

// Facebook-Style Social Functions
function toggleLike(newsId, button) {
    console.log('toggleLike called for newsId:', newsId);
    
    // Validate inputs
    if (!button) {
        console.error('Button element not found');
        return;
    }
    
    const isLiked = button.classList.contains('liked');
    const likeSummary = document.getElementById('like-summary-' + newsId);
    const likesCountDisplay = button.querySelector('.likes-count-display') || 
                            button.querySelector('.likes-count') ||
                            (likeSummary && likeSummary.querySelector('.likes-count-display')) ||
                            (likeSummary && likeSummary.querySelector('.likes-count'));
    
    // Use HTTP for development to avoid SSL issues
    const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const baseUrl = isLocalhost ? 
        'http://localhost/PK-LIVE%20NEWS' : 
        window.location.origin;
    
    fetch(baseUrl + '/api/toggle_like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'news_id=' + newsId
    })
    .then(response => {
        // Check if response is actually JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        return response.json();
    })
    .then(data => {
        console.log('Like response:', data);
        if (data.success) {
            // Update button state
            const icon = button.querySelector('i');
            const btnText = button.querySelector('.btn-text');
            
            if (icon && btnText) {
                if (isLiked) {
                    button.classList.remove('liked');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    btnText.textContent = 'Like';
                } else {
                    button.classList.add('liked');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    btnText.textContent = 'Liked';
                }
            }
            
            // Update like count display
            if (likesCountDisplay) {
                likesCountDisplay.textContent = data.likes_count;
            }
            
            // Update like summary visibility
            if (likeSummary) {
                if (data.likes_count > 0) {
                    likeSummary.style.display = 'block';
                    const likeText = likeSummary.querySelector('.like-text');
                    if (likeText) {
                        // Update both possible count display classes
                        likeText.innerHTML = '<span class="likes-count-display">' + data.likes_count + '</span> ' + 
                                          (data.likes_count == 1 ? 'person likes this' : 'people like this');
                    }
                } else {
                    likeSummary.style.display = 'none';
                }
            }
            
            // Also update any other count displays on the page for this news item
            const allCountDisplays = document.querySelectorAll(`[data-news-id="${newsId}"] .likes-count, [data-news-id="${newsId}"] .likes-count-display`);
            allCountDisplays.forEach(element => {
                element.textContent = data.likes_count;
            });
        }
    })
    .catch(error => {
        console.error('Error toggling like:', error);
        // Fallback: just toggle the UI without backend update
        const icon = button.querySelector('i');
        const btnText = button.querySelector('.btn-text');
        
        if (icon && btnText) {
            if (isLiked) {
                button.classList.remove('liked');
                icon.classList.remove('fas');
                icon.classList.add('far');
                btnText.textContent = 'Like';
            } else {
                button.classList.add('liked');
                icon.classList.remove('far');
                icon.classList.add('fas');
                btnText.textContent = 'Liked';
            }
        }
    });
}

function toggleInlineComments(newsId, button) {
    console.log('toggleInlineComments called for newsId:', newsId);
    const commentsSection = document.getElementById('inline-comments-' + newsId);
    
    if (!commentsSection) {
        console.error('Comments section not found for newsId:', newsId);
        return;
    }
    
    const isVisible = commentsSection.style.display !== 'none';
    
    if (!isVisible) {
        commentsSection.style.display = 'block';
        button.querySelector('i').classList.remove('far');
        button.querySelector('i').classList.add('fas');
        
        // Load comments if not already loaded
        const commentsContainer = commentsSection.querySelector('.comments-container');
        const commentsLoading = commentsSection.querySelector('.comments-loading');
        
        if (commentsContainer && commentsContainer.style.display === 'none') {
            loadComments(newsId);
        }
    } else {
        commentsSection.style.display = 'none';
        button.querySelector('i').classList.remove('fas');
        button.querySelector('i').classList.add('far');
    }
}

function loadComments(newsId) {
    const commentsSection = document.getElementById('inline-comments-' + newsId);
    const commentsContainer = commentsSection.querySelector('.comments-container');
    const commentsLoading = commentsSection.querySelector('.comments-loading');
    const commentsList = commentsSection.querySelector('.comments-list');
    
    // Use HTTP for development to avoid SSL issues
    const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const baseUrl = isLocalhost ? 
        'http://localhost/PK-LIVE%20NEWS' : 
        window.location.origin;
    
    fetch(baseUrl + '/api/get_comments.php?news_id=' + newsId)
    .then(response => response.json())
    .then(data => {
        commentsLoading.style.display = 'none';
        commentsContainer.style.display = 'block';
        
        commentsList.innerHTML = '';
        
        if (data.comments && data.comments.length > 0) {
            data.comments.forEach(comment => {
                const commentElement = createCommentElement(comment);
                commentsList.appendChild(commentElement);
            });
        } else {
            commentsList.innerHTML = '<p class="text-muted text-center">No comments yet. Be the first to comment!</p>';
        }
    })
    .catch(error => {
        console.error('Error loading comments:', error);
        commentsLoading.style.display = 'none';
        commentsContainer.style.display = 'block';
        commentsList.innerHTML = '<p class="text-muted text-center">Unable to load comments.</p>';
    });
}

function createCommentElement(comment) {
    const div = document.createElement('div');
    div.className = 'comment-item mb-3';
    div.innerHTML = `
        <div class="d-flex">
            <div class="user-avatar me-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                     style="width: 32px; height: 32px; background: #1877f2; color: white; font-weight: bold; font-size: 14px;">
                    ${comment.user_name ? comment.user_name.charAt(0).toUpperCase() : 'A'}
                </div>
            </div>
            <div class="flex-fill">
                <div class="comment-content bg-light rounded p-3">
                    <div class="comment-header mb-1">
                        <strong>${comment.user_name || 'Anonymous'}</strong>
                        <small class="text-muted ms-2">${formatCommentDate(comment.created_at)}</small>
                    </div>
                    <div class="comment-text">${comment.comment}</div>
                </div>
            </div>
        </div>
    `;
    return div;
}

function formatCommentDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (seconds < 60) return 'just now';
    if (minutes < 60) return minutes + ' min ago';
    if (hours < 24) return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
    if (days < 7) return days + ' day' + (days > 1 ? 's' : '') + ' ago';
    
    return date.toLocaleDateString();
}

function showShareOptions(newsId, url, title) {
    // Create a simple share modal or use native share if available
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: copy link to clipboard
        copyToClipboard(url);
        alert('Link copied to clipboard!');
    }
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            console.log('Text copied to clipboard');
        }).catch(err => console.error('Failed to copy text:', err));
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }
}

function cancelComment(newsId) {
    const commentsSection = document.getElementById('inline-comments-' + newsId);
    
    if (!commentsSection) {
        console.error('Comments section not found for newsId:', newsId);
        return;
    }
    
    const commentInput = commentsSection.querySelector('.comment-input');
    const commentActions = commentsSection.querySelector('.comment-actions');
    
    if (commentInput) {
        commentInput.value = '';
    }
    
    if (commentActions) {
        commentActions.style.display = 'none';
    }
}

// Initialize comment inputs
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for comment inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('comment-input')) {
            const commentActions = e.target.closest('.facebook-comment-form').querySelector('.comment-actions');
            const postBtn = commentActions.querySelector('.post-comment-btn');
            
            if (e.target.value.trim()) {
                commentActions.style.display = 'flex';
                postBtn.disabled = false;
            } else {
                commentActions.style.display = 'none';
                postBtn.disabled = true;
            }
        }
    });
    
    // Add event listeners for post comment buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('post-comment-btn')) {
            e.preventDefault();
            const commentForm = e.target.closest('.facebook-comment-form');
            const newsId = commentForm.closest('.facebook-comments-section').id.replace('inline-comments-', '');
            const commentInput = commentForm.querySelector('.comment-input');
            const comment = commentInput.value.trim();
            
            if (comment) {
                postComment(newsId, comment);
            }
        }
    });
});

function postComment(newsId, comment) {
    const commentsSection = document.getElementById('inline-comments-' + newsId);
    
    if (!commentsSection) {
        console.error('Comments section not found for newsId:', newsId);
        return;
    }
    
    const commentsList = commentsSection.querySelector('.comments-list');
    const commentInput = commentsSection.querySelector('.comment-input');
    const commentActions = commentsSection.querySelector('.comment-actions');
    
    if (!commentsList || !commentInput || !commentActions) {
        console.error('Required comment elements not found');
        return;
    }
    
    // Use HTTP for development to avoid SSL issues
    const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const baseUrl = isLocalhost ? 
        'http://localhost/PK-LIVE%20NEWS' : 
        window.location.origin;
    
    fetch(baseUrl + '/api/post_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'news_id=' + newsId + '&comment=' + encodeURIComponent(comment)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear input
            commentInput.value = '';
            commentActions.style.display = 'none';
            
            // Add new comment to the list
            const newComment = {
                id: data.comment_id,
                comment: comment,
                user_name: data.user_name || 'You',
                created_at: new Date().toISOString()
            };
            
            const commentElement = createCommentElement(newComment);
            commentsList.insertBefore(commentElement, commentsList.firstChild);
            
            // Update comment button count
            const commentBtn = document.querySelector(`[data-news-id="${newsId}"].comment-btn`);
            if (commentBtn) {
                const currentText = commentBtn.querySelector('.btn-text').textContent;
                const currentCount = parseInt(currentText.replace(/[^0-9]/g, '')) || 0;
                commentBtn.querySelector('.btn-text').textContent = 'Comment (' + (currentCount + 1) + ')';
            }
        } else {
            alert(data.message || 'Failed to post comment');
        }
    })
    .catch(error => {
        console.error('Error posting comment:', error);
        alert('Failed to post comment. Please try again.');
    });
}
