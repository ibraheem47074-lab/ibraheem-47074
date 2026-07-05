<?php
$page_title = 'News Source Performance Dashboard (Simple)';
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
    
    .best-performer {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        text-align: center;
        margin-top: 2rem;
    }
    
    @media (max-width: 768px) {
        .performance-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Performance Dashboard Section -->
<section class="performance-dashboard py-4">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="display-5 text-success mb-3">
                <i class="fas fa-chart-bar me-2"></i>
                News Source Performance Dashboard
            </h1>
            <p class="lead text-muted">Real-time performance metrics and rankings for news sources worldwide</p>
        </div>
        
        <!-- Summary Statistics -->
        <div class="performance-stats">
            <div class="stat-card">
                <div class="stat-number h4 mb-1">11</div>
                <div class="stat-label">Total Sources</div>
            </div>
            <div class="stat-card">
                <div class="stat-number h4 mb-1">11</div>
                <div class="stat-label">Active Sources</div>
            </div>
            <div class="stat-card">
                <div class="stat-number h4 mb-1">1,321</div>
                <div class="stat-label">Total Articles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number h4 mb-1">376,890</div>
                <div class="stat-label">Total Views</div>
            </div>
            <div class="stat-card">
                <div class="stat-number h4 mb-1">17,480</div>
                <div class="stat-label">Total Likes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number h4 mb-1">6,690</div>
                <div class="stat-label">Total Shares</div>
            </div>
        </div>
        
        <!-- Rankings Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list-ol text-primary me-2"></i>
                    Complete Rankings
                </h5>
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
                        <tbody>
                            <tr>
                                <td><div class="rank-badge rank-1">1</div></td>
                                <td class="fw-medium">Yahoo News</td>
                                <td>Politics</td>
                                <td class="text-center">156</td>
                                <td class="text-center">45,230</td>
                                <td class="text-center">2,340</td>
                                <td class="text-center">890</td>
                                <td class="text-center">7.2</td>
                                <td class="text-center fw-bold">1845.3</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="rank-badge rank-2">2</div></td>
                                <td class="fw-medium">CNN</td>
                                <td>Politics</td>
                                <td class="text-center">146</td>
                                <td class="text-center">39,800</td>
                                <td class="text-center">2,140</td>
                                <td class="text-center">780</td>
                                <td class="text-center">7.0</td>
                                <td class="text-center fw-bold">1785.4</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="rank-badge rank-3">3</div></td>
                                <td class="fw-medium">Fox News</td>
                                <td>Politics</td>
                                <td class="text-center">134</td>
                                <td class="text-center">41,200</td>
                                <td class="text-center">2,180</td>
                                <td class="text-center">820</td>
                                <td class="text-center">7.5</td>
                                <td class="text-center fw-bold">1728.9</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="rank-badge">4</div></td>
                                <td class="fw-medium">Google News</td>
                                <td>Politics</td>
                                <td class="text-center">142</td>
                                <td class="text-center">38,920</td>
                                <td class="text-center">1,980</td>
                                <td class="text-center">720</td>
                                <td class="text-center">6.8</td>
                                <td class="text-center fw-bold">1652.4</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="rank-badge">5</div></td>
                                <td class="fw-medium">BBC News</td>
                                <td>International</td>
                                <td class="text-center">123</td>
                                <td class="text-center">36,700</td>
                                <td class="text-center">1,890</td>
                                <td class="text-center">680</td>
                                <td class="text-center">6.7</td>
                                <td class="text-center fw-bold">1589.1</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="rank-badge">6</div></td>
                                <td class="fw-medium">CBS News</td>
                                <td>Politics</td>
                                <td class="text-center">128</td>
                                <td class="text-center">34,150</td>
                                <td class="text-center">1,650</td>
                                <td class="text-center">580</td>
                                <td class="text-center">6.5</td>
                                <td class="text-center fw-bold">1483.7</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="rank-badge">7</div></td>
                                <td class="fw-medium">Reuters</td>
                                <td>Business</td>
                                <td class="text-center">105</td>
                                <td class="text-center">31,200</td>
                                <td class="text-center">1,520</td>
                                <td class="text-center">560</td>
                                <td class="text-center">6.0</td>
                                <td class="text-center fw-bold">1368.2</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="rank-badge">8</div></td>
                                <td class="fw-medium">NPR News</td>
                                <td>Politics</td>
                                <td class="text-center">115</td>
                                <td class="text-center">29,840</td>
                                <td class="text-center">1,420</td>
                                <td class="text-center">490</td>
                                <td class="text-center">6.2</td>
                                <td class="text-center fw-bold">1324.8</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="rank-badge">9</div></td>
                                <td class="fw-medium">Al Jazeera</td>
                                <td>International</td>
                                <td class="text-center">92</td>
                                <td class="text-center">25,100</td>
                                <td class="text-center">1,140</td>
                                <td class="text-center">380</td>
                                <td class="text-center">5.5</td>
                                <td class="text-center fw-bold">1128.7</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="rank-badge">10</div></td>
                                <td class="fw-medium">The Guardian</td>
                                <td>Politics</td>
                                <td class="text-center">98</td>
                                <td class="text-center">27,650</td>
                                <td class="text-center">1,280</td>
                                <td class="text-center">420</td>
                                <td class="text-center">5.8</td>
                                <td class="text-center fw-bold">1204.6</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td><div class="rank-badge">11</div></td>
                                <td class="fw-medium">Associated Press</td>
                                <td>General</td>
                                <td class="text-center">87</td>
                                <td class="text-center">23,400</td>
                                <td class="text-center">980</td>
                                <td class="text-center">340</td>
                                <td class="text-center">5.2</td>
                                <td class="text-center fw-bold">1056.3</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Best Performer Highlight -->
        <div class="best-performer">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <div class="display-1 mb-3">🏆</div>
                    <h4 class="mb-0">Best Performer</h4>
                </div>
                <div class="col-md-6">
                    <h3 class="mb-3">Yahoo News</h3>
                    <p class="lead mb-0">Ranked #1 Worldwide</p>
                </div>
                <div class="col-md-3">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h5 mb-1">156</div>
                            <small>Articles</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h5 mb-1">45,230</div>
                            <small>Views</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-1">1845.3</div>
                            <small>Score</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-1">#1</div>
                            <small>Rank</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
    
    <!-- Footer -->
    <?php include 'includes/admin-footer.php'; ?>
</body>
</html>
