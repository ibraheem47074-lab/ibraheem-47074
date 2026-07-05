/**
 * Header UI Interaction Fixes
 * Resolves conflicts between profile, bell, and search dropdowns
 */

// Global variable for search timeout
let searchTimeout;

document.addEventListener('DOMContentLoaded', function() {
    // Fix dropdown conflicts
    fixDropdownConflicts();
    
    // Fix search functionality
    fixSearchInteraction();
    
    // Fix notification system
    fixNotificationInteraction();
    
    // Fix user menu interaction
    fixUserMenuInteraction();
});

/**
 * Fix dropdown conflicts - ensure only one dropdown is open at a time
 */
function fixDropdownConflicts() {
    // Get all dropdown toggles in header (expanded selector to catch all header dropdowns)
    const dropdownToggles = document.querySelectorAll('.header-right [data-bs-toggle="dropdown"], .header-icon-wrapper [data-bs-toggle="dropdown"], .search-dropdown [data-bs-toggle="dropdown"]');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            const currentDropdown = this.closest('.dropdown');
            
            // Close all other dropdowns before opening this one
            setTimeout(() => {
                // Target all header dropdowns, not just .header-right
                document.querySelectorAll('.header-right .dropdown.show, .header-icon-wrapper.dropdown.show, .search-dropdown.dropdown.show').forEach(dropdown => {
                    if (dropdown !== currentDropdown) {
                        const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown.querySelector('[data-bs-toggle="dropdown"]'));
                        if (dropdownInstance) {
                            dropdownInstance.hide();
                        }
                    }
                });
            }, 10);
        });
    });
    
    // Prevent dropdown from closing when clicking inside
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Close all dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown.show').forEach(dropdown => {
                const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown.querySelector('[data-bs-toggle="dropdown"]'));
                if (dropdownInstance) {
                    dropdownInstance.hide();
                }
            });
        }
    });
    
    // Additional Bootstrap dropdown event listeners for better coordination
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(toggle => {
        toggle.addEventListener('show.bs.dropdown', function(e) {
            // Close all other dropdowns before showing this one
            document.querySelectorAll('.dropdown.show').forEach(dropdown => {
                if (dropdown !== e.target.closest('.dropdown')) {
                    const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown.querySelector('[data-bs-toggle="dropdown"]'));
                    if (dropdownInstance) {
                        dropdownInstance.hide();
                    }
                }
            });
        });
    });
}

/**
 * Fix search functionality - prevent conflicts with other dropdowns
 */
function fixSearchInteraction() {
    const searchDropdown = document.getElementById('searchDropdown');
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    
    if (searchDropdown && searchInput) {
        // Prevent search dropdown from interfering with other dropdowns
        searchDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close other dropdowns
            closeOtherDropdowns(this.closest('.dropdown'));
        });
        
        // Handle search input without closing dropdown
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                e.stopPropagation();
                handleSearchInput(this.value);
            });
            
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Prevent search results clicks from closing dropdown
        if (searchResults) {
            searchResults.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }
}

/**
 * Fix notification system - prevent conflicts and ensure proper loading
 */
function fixNotificationInteraction() {
    const notificationBtn = document.getElementById('notificationDropdownBtn');
    const notificationDropdown = document.getElementById('notificationDropdownMenu');
    
    if (notificationBtn && notificationDropdown) {
        // Remove existing onclick and add proper event listener
        notificationBtn.removeAttribute('onclick');
        
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close other dropdowns
            closeOtherDropdowns(this.closest('.dropdown'));
            
            // Load notifications if not already loaded
            if (!this.dataset.loaded) {
                loadNotifications();
                this.dataset.loaded = 'true';
            }
        });
        
        // Prevent notification dropdown clicks from closing it
        notificationDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

/**
 * Fix user menu interaction
 */
function fixUserMenuInteraction() {
    const userDropdown = document.getElementById('userDropdown');
    
    if (userDropdown) {
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close other dropdowns
            closeOtherDropdowns(this.closest('.dropdown'));
        });
    }
}

/**
 * Close all dropdowns except the specified one
 */
function closeOtherDropdowns(exceptDropdown) {
    document.querySelectorAll('.header-right .dropdown.show').forEach(dropdown => {
        if (dropdown !== exceptDropdown) {
            const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown.querySelector('[data-bs-toggle="dropdown"]'));
            if (dropdownInstance) {
                dropdownInstance.hide();
            }
        }
    });
}

/**
 * Handle search input with debouncing
 */
function handleSearchInput(query) {
    clearTimeout(searchTimeout);
    const searchResults = document.getElementById('searchResults');
    
    if (query.length >= 3) {
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 500);
    } else {
        if (searchResults) {
            searchResults.style.display = 'none';
        }
    }
}

/**
 * Perform search request
 */
function performSearch(query) {
    const searchResults = document.getElementById('searchResults');
    
    if (searchResults) {
        // Show loading state
        searchResults.innerHTML = '<div class="p-3 text-center"><div class="spinner-border spinner-border-sm"></div> Searching...</div>';
        searchResults.style.display = 'block';
        
        fetch(`./api/search.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data && Array.isArray(data)) {
                    displaySearchResults(data);
                } else {
                    displaySearchResults([]);
                }
            })
            .catch(error => {
                console.error('Error searching:', error);
                if (searchResults) {
                    searchResults.innerHTML = '<div class="p-3 text-danger">Search unavailable</div>';
                    searchResults.style.display = 'block';
                }
            });
    }
}

/**
 * Display search results
 */
function displaySearchResults(results) {
    const searchResults = document.getElementById('searchResults');
    if (searchResults) {
        if (results.length > 0) {
            const resultsHtml = results.map(result => `
                <div class="search-result-item p-2 border-bottom">
                    <h6 class="mb-1">
                        <a href="news.php?slug=${result.slug}" class="text-decoration-none">
                            ${result.title}
                        </a>
                    </h6>
                    <p class="mb-0 small text-muted">${result.excerpt || ''}</p>
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

/**
 * Enhanced notification loading with error handling
 */
function loadNotifications() {
    const notificationList = document.getElementById('notificationList');
    const noNotifications = document.getElementById('noNotifications');
    
    if (!notificationList) return;
    
    // Show loading state
    notificationList.innerHTML = `
        <div class="text-center p-3">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    fetch('./api/notifications.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data && typeof data === 'object') {
                updateNotificationUI(data);
            } else {
                showNotificationError();
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showNotificationError();
        });
}

/**
 * Update notification UI
 */
function updateNotificationUI(data) {
    const notificationList = document.getElementById('notificationList');
    const noNotifications = document.getElementById('noNotifications');
    const notificationBadge = document.getElementById('notificationCount');
    
    if (!notificationList) return;
    
    // Update badge
    if (notificationBadge) {
        const unreadCount = data.unread_count || 0;
        if (unreadCount > 0) {
            notificationBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            notificationBadge.style.display = 'inline-block';
        } else {
            notificationBadge.style.display = 'none';
        }
    }
    
    // Update notification list
    if (data.notifications && data.notifications.length > 0) {
        let html = '';
        data.notifications.forEach(notification => {
            const priorityClass = getPriorityClass(notification.priority);
            html += `
                <li>
                    <a href="${notification.url || '#'}" class="dropdown-item notification-item ${priorityClass}" 
                       onclick="markNotificationRead(${notification.id}, event)">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${notification.title}</h6>
                                <p class="mb-1 small text-muted">${notification.message}</p>
                                <small class="text-muted">${notification.time_ago}</small>
                            </div>
                            <div class="ms-2">
                                ${getPriorityIcon(notification.priority)}
                            </div>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
            `;
        });
        notificationList.innerHTML = html;
        if (noNotifications) {
            noNotifications.classList.add('d-none');
        }
    } else {
        notificationList.innerHTML = '';
        if (noNotifications) {
            noNotifications.classList.remove('d-none');
        }
    }
}

/**
 * Show notification error
 */
function showNotificationError() {
    const notificationList = document.getElementById('notificationList');
    if (notificationList) {
        notificationList.innerHTML = `
            <li>
                <div class="dropdown-item text-center text-muted">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load notifications
                </div>
            </li>
        `;
    }
}

/**
 * Get priority class for styling
 */
function getPriorityClass(priority) {
    switch(priority) {
        case 'urgent': return 'border-start border-4 border-danger';
        case 'high': return 'border-start border-4 border-warning';
        case 'medium': return 'border-start border-4 border-info';
        default: return '';
    }
}

/**
 * Get priority icon
 */
function getPriorityIcon(priority) {
    switch(priority) {
        case 'urgent': return '<i class="fas fa-exclamation-circle text-danger"></i>';
        case 'high': return '<i class="fas fa-exclamation-triangle text-warning"></i>';
        case 'medium': return '<i class="fas fa-info-circle text-info"></i>';
        default: return '<i class="fas fa-bell text-muted"></i>';
    }
}

/**
 * Mark notification as read
 */
function markNotificationRead(notificationId, event) {
    if (notificationId) {
        fetch('./api/notifications.php?action=mark_read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `notification_id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update badge
                const badge = document.getElementById('notificationCount');
                if (badge && badge.style.display !== 'none') {
                    const currentCount = parseInt(badge.textContent) || 0;
                    if (currentCount <= 1) {
                        badge.style.display = 'none';
                    } else {
                        badge.textContent = currentCount - 1;
                    }
                }
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsRead() {
    fetch('./api/notifications.php?action=mark_all_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide badge
            const badge = document.getElementById('notificationCount');
            if (badge) {
                badge.style.display = 'none';
            }
            // Reload notifications
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking all notifications as read:', error));
}
