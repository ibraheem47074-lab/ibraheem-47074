// Interactive News Map JavaScript
class NewsMap {
    constructor() {
        this.selectedCountry = '';
        this.selectedRegion = '';
        this.newsData = [];
        this.countriesData = [];
        this.init();
    }

    init() {
        this.loadCountriesData();
        this.setupEventListeners();
        this.setupMapInteractions();
        this.initializeAutoRefresh();
    }

    loadCountriesData() {
        fetch('./api/countries_with_news.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    this.countriesData = data.countries || [];
                    this.updateCountryCards();
                    this.updateStatistics(data.statistics);
                    this.updateRegions(data.regions);
                } else {
                    console.warn('Invalid countries data format:', data);
                    this.countriesData = [];
                }
            })
            .catch(error => {
                console.error('Error loading countries data:', error);
                this.countriesData = [];
                // Show error message to user
                const container = document.getElementById('news-container');
                if (container) {
                    container.innerHTML = '<div class="alert alert-warning">Unable to load countries data. Please refresh the page.</div>';
                }
            });
    }

    setupEventListeners() {
        // Country card clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.country-card')) {
                const card = e.target.closest('.country-card');
                const countryCode = card.dataset.country;
                this.selectCountry(countryCode);
            }
        });

        // Region filter clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.region-pill')) {
                e.preventDefault();
                const pill = e.target.closest('.region-pill');
                const region = pill.getAttribute('href').includes('region=') ? 
                    pill.getAttribute('href').split('region=')[1] : '';
                this.selectRegion(region);
            }
        });

        // SVG country path clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('country-path')) {
                const countryCode = e.target.dataset.country;
                if (countryCode) {
                    this.selectCountry(countryCode);
                }
            }
        });

        // Search functionality
        const searchInput = document.querySelector('.country-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterCountries(e.target.value);
            });
        }
    }

    setupMapInteractions() {
        // Add hover effects with tooltips
        const countryPaths = document.querySelectorAll('.country-path');
        countryPaths.forEach(path => {
            const countryCode = path.dataset.country;
            if (countryCode) {
                this.updateCountryTooltip(path, countryCode);
                
                // Add hover animation
                path.addEventListener('mouseenter', () => {
                    path.style.transform = 'scale(1.05)';
                    path.style.filter = 'brightness(1.2)';
                });

                path.addEventListener('mouseleave', () => {
                    path.style.transform = 'scale(1)';
                    path.style.filter = 'brightness(1)';
                });
            }
        });

        // Highlight active country
        this.highlightActiveCountry();
    }

    updateCountryTooltip(path, countryCode) {
        const country = this.countriesData.find(c => c.code === countryCode);
        if (country) {
            path.title = `${country.name} - ${country.news_count} articles`;
            
            // Add data attributes for styling
            path.setAttribute('data-news-count', country.news_count);
            
            // Color intensity based on news count
            const intensity = Math.min(country.news_count / 100, 1);
            const opacity = 0.3 + (intensity * 0.7);
            path.style.opacity = opacity;
        }
    }

    selectCountry(countryCode) {
        this.selectedCountry = countryCode;
        this.selectedRegion = '';
        
        // Update URL
        const url = new URL(window.location);
        url.searchParams.set('country', countryCode);
        url.searchParams.delete('region');
        window.history.pushState({}, '', url);
        
        // Update UI
        this.updateActiveStates();
        this.loadNews();
        this.highlightActiveCountry();
        
        // Smooth scroll to news section
        setTimeout(() => {
            document.querySelector('.news-list').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }, 100);
    }

    selectRegion(region) {
        this.selectedRegion = region;
        this.selectedCountry = '';
        
        // Update URL
        const url = new URL(window.location);
        if (region) {
            url.searchParams.set('region', region);
        } else {
            url.searchParams.delete('region');
        }
        url.searchParams.delete('country');
        window.history.pushState({}, '', url);
        
        // Update UI
        this.updateActiveStates();
        this.loadNews();
        
        // Smooth scroll to news section
        setTimeout(() => {
            document.querySelector('.news-list').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }, 100);
    }

    loadNews() {
        const newsContainer = document.getElementById('news-container');
        const spinner = document.querySelector('.loading-spinner');
        
        // Show loading
        spinner.style.display = 'block';
        newsContainer.style.opacity = '0.5';
        
        // Build API URL
        let apiUrl = 'api/news_by_location.php?';
        if (this.selectedCountry) {
            apiUrl += `country=${this.selectedCountry}`;
        } else if (this.selectedRegion) {
            apiUrl += `region=${this.selectedRegion}`;
        }
        
        fetch('./' + apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    this.renderNews(data.news || []);
                    this.updatePagination(data.pagination);
                } else {
                    this.showError('Failed to load news: ' + (data?.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error loading news:', error);
                this.showError('Error loading news. Please check your connection and try again.');
            })
            .finally(() => {
                spinner.style.display = 'none';
                newsContainer.style.opacity = '1';
            });
    }

    renderNews(news) {
        const container = document.getElementById('news-container');
        
        if (news.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No news found for the selected location.</p>
                    <a href="news_map.php" class="btn btn-danger">View All Countries</a>
                </div>
            `;
            return;
        }
        
        container.innerHTML = news.map(article => `
            <div class="news-item">
                ${article.image ? 
                    `<img src="${article.image}" alt="${article.title}" class="news-image">` :
                    `<div class="news-image bg-light d-flex align-items-center justify-content-center rounded">
                        <i class="fas fa-image text-muted"></i>
                    </div>`
                }
                <div class="news-content">
                    <a href="${article.url}" class="news-title">${article.title}</a>
                    <div class="news-meta">
                        ${article.country.flag ? `<span class="me-2">${article.country.flag}</span>` : ''}
                        ${article.country.name ? `<span class="me-2">${article.country.name}</span>` : ''}
                        <span class="me-2">
                            <i class="fas fa-calendar me-1"></i>
                            ${this.formatDate(article.published_at)}
                        </span>
                        <span class="me-2">
                            <i class="fas fa-eye me-1"></i>
                            ${this.formatNumber(article.views)}
                        </span>
                        <span class="me-2">
                            <i class="fas fa-comments me-1"></i>
                            ${article.comments}
                        </span>
                        <span class="me-2">
                            <i class="fas fa-heart me-1"></i>
                            ${article.likes}
                        </span>
                    </div>
                </div>
            </div>
        `).join('');
    }

    updatePagination(pagination) {
        // This can be implemented to add pagination controls
        console.log('Pagination:', pagination);
    }

    updateActiveStates() {
        // Update country cards
        document.querySelectorAll('.country-card').forEach(card => {
            card.classList.remove('active');
            if (card.dataset.country === this.selectedCountry) {
                card.classList.add('active');
            }
        });
        
        // Update region pills
        document.querySelectorAll('.region-pill').forEach(pill => {
            pill.classList.remove('active');
            const href = pill.getAttribute('href');
            const isActive = this.selectedRegion && href.includes(`region=${this.selectedRegion}`);
            const isAllActive = !this.selectedRegion && href.includes('news_map.php') && !href.includes('region=');
            
            if (isActive || isAllActive) {
                pill.classList.add('active');
            }
        });
    }

    highlightActiveCountry() {
        // Remove all active classes from map
        document.querySelectorAll('.country-path').forEach(path => {
            path.classList.remove('active');
        });
        
        // Add active class to selected country
        if (this.selectedCountry) {
            const activePath = document.querySelector(`[data-country="${this.selectedCountry}"]`);
            if (activePath) {
                activePath.classList.add('active');
            }
        }
    }

    updateCountryCards() {
        // Update country cards with latest data
        this.countriesData.forEach(country => {
            const card = document.querySelector(`[data-country="${country.code}"]`);
            if (card) {
                const statsElement = card.querySelector('.country-stats');
                if (statsElement) {
                    statsElement.textContent = `${this.formatNumber(country.news_count)} articles`;
                }
            }
        });
    }

    updateStatistics(stats) {
        // Update statistics cards
        const statsElements = {
            'total_countries': document.querySelector('.stat-card:nth-child(1) .stat-number'),
            'total_news': document.querySelector('.stat-card:nth-child(2) .stat-number'),
            'total_regions': document.querySelector('.stat-card:nth-child(3) .stat-number')
        };
        
        Object.keys(statsElements).forEach(key => {
            const element = statsElements[key];
            if (element && stats[key]) {
                element.textContent = this.formatNumber(stats[key]);
            }
        });
    }

    updateRegions(regions) {
        // Update region pills with latest counts
        regions.forEach(region => {
            const pill = document.querySelector(`.region-pill[href*="region=${region.name}"]`);
            if (pill) {
                const badge = pill.querySelector('.badge');
                if (badge) {
                    badge.textContent = region.countries_count;
                }
            }
        });
    }

    filterCountries(searchTerm) {
        const cards = document.querySelectorAll('.country-card');
        const term = searchTerm.toLowerCase();
        
        cards.forEach(card => {
            const countryName = card.querySelector('.country-name').textContent.toLowerCase();
            const countryCode = card.dataset.country.toLowerCase();
            
            if (countryName.includes(term) || countryCode.includes(term)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    initializeAutoRefresh() {
        // Auto-refresh news every 5 minutes
        setInterval(() => {
            if (this.selectedCountry || this.selectedRegion) {
                this.loadNews();
            }
            this.loadCountriesData(); // Refresh statistics too
        }, 300000); // 5 minutes
    }

    showError(message) {
        const container = document.getElementById('news-container');
        container.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Just now';
        if (diff < 3600000) return `${Math.floor(diff / 60000)} minutes ago`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)} hours ago`;
        if (diff < 604800000) return `${Math.floor(diff / 86400000)} days ago`;
        
        return date.toLocaleDateString();
    }

    formatNumber(num) {
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
        return num.toString();
    }
}

// Initialize the map when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.newsMap = new NewsMap();
});

// Handle browser back/forward buttons
window.addEventListener('popstate', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const country = urlParams.get('country');
    const region = urlParams.get('region');
    
    if (country) {
        window.newsMap.selectCountry(country);
    } else if (region) {
        window.newsMap.selectRegion(region);
    } else {
        window.location.reload();
    }
});

// Add keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        // Clear filters on Escape
        window.location.href = 'news_map.php';
    }
});

// Add touch gesture support for mobile
let touchStartX = 0;
let touchEndX = 0;

document.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
});

document.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
        // Navigate between countries based on swipe
        if (diff > 0) {
            // Swipe left - next country
            console.log('Swipe left - next country');
        } else {
            // Swipe right - previous country
            console.log('Swipe right - previous country');
        }
    }
}
