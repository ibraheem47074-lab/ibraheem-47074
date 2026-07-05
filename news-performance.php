<?php
$page_title = 'News Source Performance Dashboard';
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom Styles -->
<style>
    .performance-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .chart-container {
        position: relative;
        height: 350px;
        margin: 20px 0;
    }
    
    .rank-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: bold;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .rank-1 { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
    .rank-2 { background: linear-gradient(135deg, #C0C0C0 0%, #808080 100%); }
    .rank-3 { background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%); }
    
    .loading-spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .best-performer {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        text-align: center;
        margin-top: 2rem;
    }
    
    .world-map-container {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .world-map-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 500"><rect fill="%23ffffff" opacity="0.1" x="100" y="100" width="800" height="300" rx="20"/><text x="500" y="250" font-family="Arial" font-size="24" fill="%23ffffff" text-anchor="middle" opacity="0.3">World Map</text></svg>');
        background-size: cover;
        opacity: 0.3;
    }
    
    .region-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .region-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }
    
    .region-card:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }
    
    .live-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: #ff4444;
        border-radius: 50%;
        animation: pulse 2s infinite;
        margin-right: 5px;
    }
    
    @keyframes pulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
        100% { opacity: 1; transform: scale(1); }
    }
    
    .trending-badge {
        background: linear-gradient(135deg, #ff6b6b 0%, #ff4444 100%);
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .live-coverage-badge {
        background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .country-flag {
        font-size: 1.2rem;
        margin-right: 5px;
    }
    
    .real-time-update {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    @media (max-width: 768px) {
        .performance-stats {
            grid-template-columns: 1fr;
        }
        .region-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<!-- Performance Dashboard Section -->
<section class="performance-dashboard py-4">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-5 text-success mb-3">
                <i class="fas fa-globe me-2"></i>
                Global News Performance Dashboard
            </h1>
            <p class="lead text-muted">Real-time performance metrics from news sources worldwide</p>
            <div class="d-flex justify-content-center align-items-center gap-3 mt-3">
                <div class="real-time-update">
                    <span class="live-indicator"></span>
                    <span>Live Data</span>
                </div>
                <button onclick="refreshData()" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-1"></i> Refresh Data
                </button>
                <button onclick="toggleAutoRefresh()" id="autoRefreshBtn" class="btn btn-success">
                    <i class="fas fa-play me-1"></i> Auto Refresh: ON
                </button>
            </div>
        </div>
        
        <!-- World Map Section -->
        <div class="world-map-container mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-3">
                        <i class="fas fa-map-marked-alt me-2"></i>
                        Global News Coverage
                    </h3>
                    <p class="mb-0">Real-time data from <span id="totalSources">0</span> news sources across <span id="totalRegions">0</span> regions</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-white">
                        <small>Last Updated:</small><br>
                        <strong id="lastUpdated">--:--:--</strong>
                    </div>
                </div>
            </div>
            <div class="region-stats" id="regionStats">
                <!-- Region stats will be populated by JavaScript -->
            </div>
        </div>
        
        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="text-center py-4 hidden">
            <div class="loading-spinner"></div>
            <p class="mt-3 text-muted">Loading performance data...</p>
        </div>
        
        <!-- Error Message -->
        <div id="errorMessage" class="alert alert-danger hidden" role="alert">
            <strong>Error:</strong> <span id="errorText"></span>
        </div>
        
        <!-- Summary Statistics -->
        <div id="summaryStats" class="performance-stats">
            <!-- Stats will be populated by JavaScript -->
        </div>
        
        <!-- Trending Sources Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-fire text-danger me-2"></i>
                                    Trending News Sources Worldwide
                                    <span class="badge bg-danger ms-2">
                                        <span class="live-indicator"></span>
                                        LIVE
                                    </span>
                                </h5>
                                <small class="text-muted">Top performing sources with highest engagement in real-time</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <select class="form-select form-select-sm" id="trendingFilter" onchange="updateTrendingDisplay()">
                                    <option value="all">All Categories</option>
                                    <option value="international">International</option>
                                    <option value="business">Business</option>
                                    <option value="asia">Asia</option>
                                    <option value="americas">Americas</option>
                                    <option value="europe">Europe</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Trending Summary Stats -->
                        <div class="row mb-3" id="trendingSummary">
                            <!-- Will be populated by JavaScript -->
                        </div>
                        
                        <!-- Trending Sources Grid -->
                        <div id="trendingSources" class="row">
                            <!-- Trending sources will be populated by JavaScript -->
                        </div>
                        
                        <!-- Trending Chart -->
                        <div class="mt-4">
                            <h6 class="mb-3">
                                <i class="fas fa-chart-line text-success me-2"></i>
                                Trending Performance Timeline
                            </h6>
                            <div class="chart-container" style="height: 200px;">
                                <canvas id="trendingChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Live Coverage Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-broadcast-tower text-red me-2"></i>
                            Sources with Live Coverage
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="liveSources" class="row">
                            <!-- Live sources will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Top Performers Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-trophy text-warning me-2"></i>
                            Top 10 News Sources by Performance
                        </h5>
                        <div class="chart-container">
                            <canvas id="topPerformersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Article Distribution Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-chart-pie text-info me-2"></i>
                            Articles Distribution by Source
                        </h5>
                        <div class="chart-container">
                            <canvas id="articlesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Second Row of Charts -->
        <div class="row mb-4">
            <!-- Category Performance Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-tags text-primary me-2"></i>
                            Performance by Category
                        </h5>
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Engagement Metrics Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-comments text-success me-2"></i>
                            Engagement Metrics Comparison
                        </h5>
                        <div class="chart-container">
                            <canvas id="engagementChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Rankings Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list-ol text-primary me-2"></i>
                            Complete Rankings
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search news sources...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>News Source</th>
                                <th>Category</th>
                                <th class="text-center">Articles</th>
                                <th class="text-center">Views</th>
                                <th class="text-center">Likes</th>
                                <th class="text-center">Shares</th>
                                <th class="text-center">Engagement</th>
                                <th class="text-center">Performance Score</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="rankingsTableBody">
                            <!-- Table rows will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Best Performer Highlight -->
        <div id="bestPerformer" class="best-performer">
            <!-- Will be populated by JavaScript -->
        </div>
    </div>
</section>
    
    <!-- Footer -->
    <?php include 'includes/admin-footer.php'; ?>

    <!-- JavaScript -->
    <script>
        let performanceData = null;
        let charts = {};
        let autoRefreshInterval = null;
        let autoRefreshEnabled = true;
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadPerformanceData();
            startAutoRefresh();
        });
        
        // Auto refresh functionality
        function startAutoRefresh() {
            if (autoRefreshEnabled && !autoRefreshInterval) {
                autoRefreshInterval = setInterval(() => {
                    loadPerformanceData();
                }, 30000); // Refresh every 30 seconds
            }
        }
        
        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }
        
        function toggleAutoRefresh() {
            const btn = document.getElementById('autoRefreshBtn');
            autoRefreshEnabled = !autoRefreshEnabled;
            
            if (autoRefreshEnabled) {
                startAutoRefresh();
                btn.innerHTML = '<i class="fas fa-pause me-1"></i> Auto Refresh: ON';
                btn.className = 'btn btn-success';
            } else {
                stopAutoRefresh();
                btn.innerHTML = '<i class="fas fa-play me-1"></i> Auto Refresh: OFF';
                btn.className = 'btn btn-secondary';
            }
        }
        
        // Load performance data
        async function loadPerformanceData() {
            showLoading(true);
            hideError();
            
            try {
                let apiPath;
                if (window.location.pathname.includes('PK-LIVE%20NEWS')) {
                    apiPath = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/api/news-performance.php');
                } else {
                    const currentPath = window.location.pathname.replace('PK-LIVE NEWS', 'PK-LIVE%20NEWS');
                    apiPath = window.location.origin + currentPath.replace(/\/[^\/]*$/, '/api/news-performance.php');
                }
                
                const response = await fetch(apiPath);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    performanceData = data.data;
                    updateDashboard();
                    updateLastUpdated();
                } else {
                    showError('Failed to load performance data: ' + data.error);
                }
            } catch (error) {
                console.error('API Error:', error);
                try {
                    const response = await fetch('./api/news-performance.php');
                    const data = await response.json();
                    
                    if (data.success) {
                        performanceData = data.data;
                        updateDashboard();
                        updateLastUpdated();
                    } else {
                        showError('Failed to load performance data: ' + data.error);
                    }
                } catch (fallbackError) {
                    showError('Network error: ' + error.message);
                }
            } finally {
                showLoading(false);
            }
        }
        
        // Update dashboard with global data
        function updateDashboard() {
            updateSummaryStats();
            updateRegionStats();
            updateTrendingSources();
            updateLiveSources();
            createTopPerformersChart();
            createArticlesChart();
            createCategoryChart();
            createEngagementChart();
            updateRankingsTable();
        }
        
        // Update summary statistics
        function updateSummaryStats() {
            const stats = performanceData.total_stats;
            const container = document.getElementById('summaryStats');
            
            container.innerHTML = `
                <div class="stat-card">
                    <i class="fas fa-globe-americas fa-2x mb-2"></i>
                    <h3>${stats.total_sources}</h3>
                    <p>Global Sources</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-newspaper fa-2x mb-2"></i>
                    <h3>${stats.total_articles.toLocaleString()}</h3>
                    <p>Total Articles</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-eye fa-2x mb-2"></i>
                    <h3>${(stats.total_views / 1000000).toFixed(1)}M</h3>
                    <p>Total Views</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-heart fa-2x mb-2"></i>
                    <h3>${(stats.total_likes / 1000).toFixed(1)}K</h3>
                    <p>Total Likes</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-share fa-2x mb-2"></i>
                    <h3>${(stats.total_shares / 1000).toFixed(1)}K</h3>
                    <p>Total Shares</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-comments fa-2x mb-2"></i>
                    <h3>${(stats.total_comments / 1000).toFixed(1)}K</h3>
                    <p>Total Comments</p>
                </div>
            `;
        }
        
        // Update region statistics
        function updateRegionStats() {
            const regions = performanceData.regions;
            const container = document.getElementById('regionStats');
            const totalSources = document.getElementById('totalSources');
            const totalRegions = document.getElementById('totalRegions');
            
            totalSources.textContent = performanceData.total_stats.total_sources;
            totalRegions.textContent = regions.length;
            
            container.innerHTML = regions.map(region => `
                <div class="region-card">
                    <h5>${region.name}</h5>
                    <div class="country-flag">${getRegionFlag(region.name)}</div>
                    <p class="mb-0"><strong>${region.source_count}</strong> sources</p>
                    <p class="mb-0"><strong>${region.countries_covered}</strong> countries</p>
                    <p class="mb-0"><strong>${(region.total_views / 1000000).toFixed(1)}M</strong> views</p>
                </div>
            `).join('');
        }
        
        // Get region flag emoji
        function getRegionFlag(region) {
            const flags = {
                'Europe': '🇪🇺',
                'Americas': '🌎',
                'Asia': '🌏',
                'Middle East': '🌍',
                'Africa': '🌍'
            };
            return flags[region] || '🌍';
        }
        
        // Update trending sources with enhanced data
        function updateTrendingSources() {
            const trending = performanceData.trending_sources.slice(0, 12);
            const container = document.getElementById('trendingSources');
            const filter = document.getElementById('trendingFilter').value;
            
            // Filter trending sources based on selection
            let filteredTrending = trending;
            if (filter !== 'all') {
                filteredTrending = trending.filter(source => 
                    source.category.toLowerCase() === filter.toLowerCase() ||
                    source.region.toLowerCase() === filter.toLowerCase()
                );
            }
            
            // Update trending summary stats
            updateTrendingSummary(filteredTrending);
            
            container.innerHTML = filteredTrending.map(source => `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border-0 shadow-sm h-100 trending-card" data-category="${source.category}" data-region="${source.region}">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div>
                                    <h6 class="card-title mb-1">${source.name}</h6>
                                    <span class="trending-badge animate-pulse">TRENDING #${source.rank}</span>
                                </div>
                                <div class="text-end">
                                    <div class="country-flag">${getCountryFlag(source.country)}</div>
                                    <small class="text-muted d-block">${source.country}</small>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <span class="badge bg-light text-dark me-1">${source.category}</span>
                                <span class="badge bg-info me-1">${source.region}</span>
                                ${source.live_coverage ? '<span class="badge bg-danger"><span class="live-indicator"></span>LIVE</span>' : ''}
                            </div>
                            
                            <!-- Performance Metrics -->
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-newspaper text-primary me-1" style="font-size: 0.8rem;"></i>
                                        <span class="small">${source.published_articles.toLocaleString()}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-eye text-success me-1" style="font-size: 0.8rem;"></i>
                                        <span class="small">${(source.total_views / 1000).toFixed(0)}K</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-heart text-danger me-1" style="font-size: 0.8rem;"></i>
                                        <span class="small">${(source.total_likes / 1000).toFixed(1)}K</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-comments text-info me-1" style="font-size: 0.8rem;"></i>
                                        <span class="small">${(source.total_comments / 1000).toFixed(1)}K</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Engagement Score Bar -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Engagement Score</small>
                                    <small class="fw-bold text-success">${source.avg_engagement}/10</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: ${source.avg_engagement * 10}%"></div>
                                </div>
                            </div>
                            
                            <!-- Performance Score -->
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Performance Score</small>
                                <span class="badge bg-gradient" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);">
                                    ${source.performance_score.toFixed(0)}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Create trending performance chart
            createTrendingChart(filteredTrending);
        }
        
        // Update trending summary statistics
        function updateTrendingSummary(trendingSources) {
            const container = document.getElementById('trendingSummary');
            
            const totalArticles = trendingSources.reduce((sum, source) => sum + source.published_articles, 0);
            const totalViews = trendingSources.reduce((sum, source) => sum + source.total_views, 0);
            const totalEngagement = trendingSources.reduce((sum, source) => sum + source.avg_engagement, 0) / trendingSources.length;
            const topRegion = getTopRegion(trendingSources);
            
            container.innerHTML = `
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="text-danger mb-0">${trendingSources.length}</h5>
                        <small class="text-muted">Trending Sources</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="text-primary mb-0">${totalArticles.toLocaleString()}</h5>
                        <small class="text-muted">Total Articles</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="text-success mb-0">${(totalViews / 1000000).toFixed(1)}M</h5>
                        <small class="text-muted">Total Views</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h5 class="text-info mb-0">${topRegion}</h5>
                        <small class="text-muted">Top Region</small>
                    </div>
                </div>
            `;
        }
        
        // Get top region from trending sources
        function getTopRegion(sources) {
            const regionCounts = {};
            sources.forEach(source => {
                regionCounts[source.region] = (regionCounts[source.region] || 0) + 1;
            });
            return Object.keys(regionCounts).reduce((a, b) => regionCounts[a] > regionCounts[b] ? a : b);
        }
        
        // Get country flag emoji
        function getCountryFlag(country) {
            const flags = {
                'UK': '🇬🇧',
                'USA': '🇺🇸',
                'Qatar': '🇶🇦',
                'India': '🇮🇳',
                'Pakistan': '🇵🇰',
                'France': '🇫🇷',
                'Germany': '🇩🇪',
                'Russia': '🇷🇺',
                'China': '🇨🇳'
            };
            return flags[country] || '🌍';
        }
        
        // Create trending performance chart
        function createTrendingChart(trendingSources) {
            const ctx = document.getElementById('trendingChart').getContext('2d');
            
            if (charts.trending) {
                charts.trending.destroy();
            }
            
            const topTrending = trendingSources.slice(0, 6);
            
            charts.trending = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: topTrending.map(s => s.name),
                    datasets: [
                        {
                            label: 'Views (K)',
                            data: topTrending.map(s => s.total_views / 1000),
                            borderColor: '#36A2EB',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Engagement Score',
                            data: topTrending.map(s => s.avg_engagement * 100),
                            borderColor: '#FF6384',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
        
        // Update trending display based on filter
        function updateTrendingDisplay() {
            updateTrendingSources();
        }
        
        // Update live coverage sources
        function updateLiveSources() {
            const live = performanceData.live_sources.slice(0, 6);
            const container = document.getElementById('liveSources');
            
            container.innerHTML = live.map(source => `
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="card-title mb-0 me-2">${source.name}</h6>
                                <span class="live-coverage-badge">
                                    <span class="live-indicator"></span>LIVE
                                </span>
                            </div>
                            <p class="text-muted small mb-1">${source.country} • ${source.category}</p>
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-info">${(source.total_views / 1000).toFixed(0)}K views</span>
                                <span class="badge bg-warning">${source.total_comments} comments</span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        // Update last updated time
        function updateLastUpdated() {
            const lastUpdated = document.getElementById('lastUpdated');
            const now = new Date();
            lastUpdated.textContent = now.toLocaleTimeString();
        }
        
        // Show/hide loading indicator
        function showLoading(show) {
            const indicator = document.getElementById('loadingIndicator');
            if (show) {
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.add('hidden');
            }
        }
        
        // Show error message
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            errorText.textContent = message;
            errorDiv.classList.remove('hidden');
        }
        
        // Hide error message
        function hideError() {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.classList.add('hidden');
        }
        
        // Refresh data manually
        function refreshData() {
            loadPerformanceData();
        }
        
        // Update all dashboard components
        function updateDashboard() {
            updateSummaryStats();
            createTopPerformersChart();
            createArticlesChart();
            createCategoryChart();
            createEngagementChart();
            updateRankingsTable();
            highlightBestPerformer();
        }
        
        // Update summary statistics
        function updateSummaryStats() {
            const stats = performanceData.total_stats;
            const container = document.getElementById('summaryStats');
            
            container.innerHTML = `
                <div class="stat-card">
                    <div class="stat-number h4 mb-1">${stats.total_sources}</div>
                    <div class="stat-label">Total Sources</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number h4 mb-1">${stats.active_sources}</div>
                    <div class="stat-label">Active Sources</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number h4 mb-1">${stats.total_articles.toLocaleString()}</div>
                    <div class="stat-label">Total Articles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number h4 mb-1">${stats.total_views.toLocaleString()}</div>
                    <div class="stat-label">Total Views</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number h4 mb-1">${stats.total_likes.toLocaleString()}</div>
                    <div class="stat-label">Total Likes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number h4 mb-1">${stats.total_shares.toLocaleString()}</div>
                    <div class="stat-label">Total Shares</div>
                </div>
            `;
        }
        
        // Create top performers chart
        function createTopPerformersChart() {
            const ctx = document.getElementById('topPerformersChart').getContext('2d');
            const topPerformers = performanceData.top_performers.slice(0, 10);
            
            if (charts.topPerformers) {
                charts.topPerformers.destroy();
            }
            
            charts.topPerformers = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: topPerformers.map(s => s.name),
                    datasets: [{
                        label: 'Performance Score',
                        data: topPerformers.map(s => s.performance_score),
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Create articles distribution chart
        function createArticlesChart() {
            const ctx = document.getElementById('articlesChart').getContext('2d');
            const topSources = performanceData.sources.slice(0, 8);
            
            if (charts.articles) {
                charts.articles.destroy();
            }
            
            charts.articles = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: topSources.map(s => s.name),
                    datasets: [{
                        data: topSources.map(s => s.published_articles),
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                            '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }
        
        // Create category performance chart
        function createCategoryChart() {
            const ctx = document.getElementById('categoryChart').getContext('2d');
            const categories = performanceData.categories;
            
            if (charts.category) {
                charts.category.destroy();
            }
            
            charts.category = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: categories.map(c => c.name),
                    datasets: [{
                        data: categories.map(c => c.article_count),
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                            '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }
        
        // Create engagement metrics chart
        function createEngagementChart() {
            const ctx = document.getElementById('engagementChart').getContext('2d');
            const topSources = performanceData.sources.slice(0, 6);
            
            if (charts.engagement) {
                charts.engagement.destroy();
            }
            
            charts.engagement = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Views', 'Likes', 'Shares', 'Comments', 'Engagement Score'],
                    datasets: topSources.map((source, index) => ({
                        label: source.name,
                        data: [
                            source.total_views / 1000, // Scale down for visibility
                            source.total_likes,
                            source.total_shares,
                            source.total_comments,
                            source.avg_engagement * 100
                        ],
                        backgroundColor: `rgba(${index * 40}, ${100 + index * 20}, ${200 + index * 10}, 0.2)`,
                        borderColor: `rgba(${index * 40}, ${100 + index * 20}, ${200 + index * 10}, 1)`,
                        borderWidth: 2
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }
        
        // Update rankings table
        function updateRankingsTable() {
            const tbody = document.getElementById('rankingsTableBody');
            const sources = performanceData.sources;
            
            tbody.innerHTML = sources.map(source => {
                const rankClass = source.rank <= 3 ? `rank-${source.rank}` : '';
                const statusBadge = source.status === 'active' 
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
                
                return `
                    <tr>
                        <td>
                            <div class="rank-badge ${rankClass}">${source.rank}</div>
                        </td>
                        <td class="fw-medium">${source.name}</td>
                        <td>${source.category}</td>
                        <td class="text-center">${source.published_articles.toLocaleString()}</td>
                        <td class="text-center">${source.total_views.toLocaleString()}</td>
                        <td class="text-center">${source.total_likes.toLocaleString()}</td>
                        <td class="text-center">${source.total_shares.toLocaleString()}</td>
                        <td class="text-center">${source.avg_engagement}</td>
                        <td class="text-center fw-bold">${source.performance_score.toFixed(1)}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            }).join('');
        }
        
        // Highlight best performer
        function highlightBestPerformer() {
            const best = performanceData.sources[0];
            const container = document.getElementById('bestPerformer');
            
            container.innerHTML = `
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <div class="display-1 mb-3">🏆</div>
                        <h4 class="mb-0">Best Performer</h4>
                    </div>
                    <div class="col-md-6">
                        <h3 class="mb-3">${best.name}</h3>
                        <p class="lead mb-0">Ranked #${best.rank} Worldwide</p>
                    </div>
                    <div class="col-md-3">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="h5 mb-1">${best.published_articles.toLocaleString()}</div>
                                <small>Articles</small>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="h5 mb-1">${best.total_views.toLocaleString()}</div>
                                <small>Views</small>
                            </div>
                            <div class="col-6">
                                <div class="h5 mb-1">${best.performance_score.toFixed(1)}</div>
                                <small>Score</small>
                            </div>
                            <div class="col-6">
                                <div class="h5 mb-1">#${best.rank}</div>
                                <small>Rank</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Refresh data
        function refreshData() {
            loadPerformanceData();
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#rankingsTableBody tr');
            
            rows.forEach(row => {
                const sourceName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const categoryName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                if (sourceName.includes(searchTerm) || categoryName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Utility functions
        function showLoading(show) {
            const element = document.getElementById('loadingIndicator');
            if (show) {
                element.classList.remove('hidden');
            } else {
                element.classList.add('hidden');
            }
        }
        
        function showError(message) {
            document.getElementById('errorText').textContent = message;
            document.getElementById('errorMessage').classList.remove('hidden');
        }
        
        function hideError() {
            document.getElementById('errorMessage').classList.add('hidden');
        }
    </script>
</body>
</html>
