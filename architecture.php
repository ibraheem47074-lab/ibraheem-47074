<?php
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$page_title = 'System Architecture';

// Get system statistics for architecture overview with error handling
$system_stats = [];

// Helper function to safely get count
function safeGetCount($conn, $query, $default = 0) {
    try {
        $result = mysqli_query($conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? $row['count'] : $default;
        }
    } catch (Exception $e) {
        // Log error if needed
    }
    return $default;
}

$system_stats['total_news'] = safeGetCount($conn, "SELECT COUNT(*) as count FROM news");
$system_stats['total_categories'] = safeGetCount($conn, "SELECT COUNT(*) as count FROM categories");
$system_stats['total_users'] = safeGetCount($conn, "SELECT COUNT(*) as count FROM users");
$system_stats['live_streams'] = safeGetCount($conn, "SELECT COUNT(*) as count FROM live_stream WHERE status = 'online'");
$system_stats['total_deployments'] = safeGetCount($conn, "SELECT COUNT(*) as count FROM live_deployments");

// Get database table information
$tables_query = "SHOW TABLES";
$tables_result = mysqli_query($conn, $tables_query);
$tables = [];
while ($table = mysqli_fetch_row($tables_result)) {
    $tables[] = $table[0];
}
?>

<?php include 'includes/header.php'; ?>

<div class="container-fluid py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-sitemap me-2"></i>System Architecture</h1>
                    <p class="text-muted">Interactive overview of PK Live News platform architecture</p>
                </div>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="toggleView()">
                        <i class="fas fa-exchange-alt me-1"></i>Switch View
                    </button>
                    <button class="btn btn-outline-success" onclick="exportDiagram()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Architecture Overview Stats -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-newspaper fa-2x mb-2"></i>
                    <h4><?php echo number_format($system_stats['total_news']); ?></h4>
                    <small>News Articles</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-tags fa-2x mb-2"></i>
                    <h4><?php echo number_format($system_stats['total_categories']); ?></h4>
                    <small>Categories</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h4><?php echo number_format($system_stats['total_users']); ?></h4>
                    <small>Users</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-broadcast-tower fa-2x mb-2"></i>
                    <h4><?php echo number_format($system_stats['live_streams']); ?></h4>
                    <small>Live Streams</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-server fa-2x mb-2"></i>
                    <h4><?php echo number_format($system_stats['total_deployments']); ?></h4>
                    <small>Deployments</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-database fa-2x mb-2"></i>
                    <h4><?php echo count($tables); ?></h4>
                    <small>DB Tables</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Architecture Diagram -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-project-diagram me-2"></i>
                        <span id="viewTitle">System Architecture Overview</span>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- View Toggle Buttons -->
                    <div class="btn-group mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="showView('overview')">
                            Overview
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="showView('frontend')">
                            Frontend
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="showView('backend')">
                            Backend
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="showView('database')">
                            Database
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="showView('infrastructure')">
                            Infrastructure
                        </button>
                    </div>

                    <!-- Architecture Diagram Container -->
                    <div id="architectureDiagram" class="architecture-container">
                        <!-- Overview View -->
                        <div id="overviewView" class="architecture-view">
                            <div class="diagram-wrapper">
                                <svg width="100%" height="600" viewBox="0 0 1200 600" class="architecture-svg">
                                    <!-- Frontend Layer -->
                                    <g id="frontend-layer">
                                        <rect x="50" y="50" width="1100" height="120" fill="#e3f2fd" stroke="#1976d2" stroke-width="2" rx="10"/>
                                        <text x="600" y="80" text-anchor="middle" font-size="18" font-weight="bold" fill="#1976d2">Frontend Layer</text>
                                        
                                        <!-- Frontend Components -->
                                        <rect x="100" y="100" width="200" height="50" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                        <text x="200" y="130" text-anchor="middle" font-size="14">User Interface</text>
                                        
                                        <rect x="350" y="100" width="200" height="50" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                        <text x="450" y="130" text-anchor="middle" font-size="14">Admin Dashboard</text>
                                        
                                        <rect x="600" y="100" width="200" height="50" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                        <text x="700" y="130" text-anchor="middle" font-size="14">Live Streaming</text>
                                        
                                        <rect x="850" y="100" width="200" height="50" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                        <text x="950" y="130" text-anchor="middle" font-size="14">Mobile Responsive</text>
                                    </g>

                                    <!-- API Layer -->
                                    <g id="api-layer">
                                        <rect x="50" y="200" width="1100" height="100" fill="#f3e5f5" stroke="#7b1fa2" stroke-width="2" rx="10"/>
                                        <text x="600" y="230" text-anchor="middle" font-size="18" font-weight="bold" fill="#7b1fa2">API Layer</text>
                                        
                                        <!-- API Components -->
                                        <rect x="150" y="250" width="180" height="40" fill="#ffffff" stroke="#7b1fa2" rx="5"/>
                                        <text x="240" y="275" text-anchor="middle" font-size="13">REST API</text>
                                        
                                        <rect x="370" y="250" width="180" height="40" fill="#ffffff" stroke="#7b1fa2" rx="5"/>
                                        <text x="460" y="275" text-anchor="middle" font-size="13">Authentication</text>
                                        
                                        <rect x="590" y="250" width="180" height="40" fill="#ffffff" stroke="#7b1fa2" rx="5"/>
                                        <text x="680" y="275" text-anchor="middle" font-size="13">Live Streaming API</text>
                                        
                                        <rect x="810" y="250" width="180" height="40" fill="#ffffff" stroke="#7b1fa2" rx="5"/>
                                        <text x="900" y="275" text-anchor="middle" font-size="13">Analytics API</text>
                                    </g>

                                    <!-- Business Logic Layer -->
                                    <g id="business-layer">
                                        <rect x="50" y="330" width="1100" height="100" fill="#e8f5e8" stroke="#388e3c" stroke-width="2" rx="10"/>
                                        <text x="600" y="360" text-anchor="middle" font-size="18" font-weight="bold" fill="#388e3c">Business Logic Layer</text>
                                        
                                        <!-- Business Components -->
                                        <rect x="100" y="380" width="160" height="40" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                        <text x="180" y="405" text-anchor="middle" font-size="13">News Management</text>
                                        
                                        <rect x="290" y="380" width="160" height="40" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                        <text x="370" y="405" text-anchor="middle" font-size="13">User Management</text>
                                        
                                        <rect x="480" y="380" width="160" height="40" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                        <text x="560" y="405" text-anchor="middle" font-size="13">Content Engine</text>
                                        
                                        <rect x="670" y="380" width="160" height="40" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                        <text x="750" y="405" text-anchor="middle" font-size="13">Analytics</text>
                                        
                                        <rect x="860" y="380" width="160" height="40" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                        <text x="940" y="405" text-anchor="middle" font-size="13">Deployment</text>
                                    </g>

                                    <!-- Data Layer -->
                                    <g id="data-layer">
                                        <rect x="50" y="460" width="1100" height="120" fill="#fff3e0" stroke="#f57c00" stroke-width="2" rx="10"/>
                                        <text x="600" y="490" text-anchor="middle" font-size="18" font-weight="bold" fill="#f57c00">Data Layer</text>
                                        
                                        <!-- Data Components -->
                                        <rect x="200" y="510" width="200" height="50" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                        <text x="300" y="540" text-anchor="middle" font-size="14">MySQL Database</text>
                                        
                                        <rect x="450" y="510" width="200" height="50" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                        <text x="550" y="540" text-anchor="middle" font-size="14">File Storage</text>
                                        
                                        <rect x="700" y="510" width="200" height="50" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                        <text x="800" y="540" text-anchor="middle" font-size="14">Cache Layer</text>
                                    </g>

                                    <!-- Connection Lines -->
                                    <g id="connections" stroke="#666" stroke-width="1" fill="none" stroke-dasharray="5,5">
                                        <line x1="600" y1="170" x2="600" y2="200"/>
                                        <line x1="600" y1="300" x2="600" y2="330"/>
                                        <line x1="600" y1="430" x2="600" y2="460"/>
                                    </g>
                                </svg>
                            </div>
                        </div>

                        <!-- Frontend View (Hidden by default) -->
                        <div id="frontendView" class="architecture-view" style="display: none;">
                            <div class="diagram-wrapper">
                                <svg width="100%" height="500" viewBox="0 0 1000 500" class="architecture-svg">
                                    <text x="500" y="30" text-anchor="middle" font-size="20" font-weight="bold" fill="#1976d2">Frontend Architecture</text>
                                    
                                    <!-- Main Components -->
                                    <rect x="50" y="60" width="900" height="400" fill="#e3f2fd" stroke="#1976d2" stroke-width="2" rx="10"/>
                                    
                                    <!-- UI Components -->
                                    <rect x="100" y="100" width="250" height="80" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                    <text x="225" y="130" text-anchor="middle" font-size="14" font-weight="bold">UI Components</text>
                                    <text x="225" y="150" text-anchor="middle" font-size="12">Bootstrap, FontAwesome</text>
                                    <text x="225" y="165" text-anchor="middle" font-size="12">Custom CSS</text>
                                    
                                    <!-- JavaScript Framework -->
                                    <rect x="400" y="100" width="250" height="80" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                    <text x="525" y="130" text-anchor="middle" font-size="14" font-weight="bold">JavaScript</text>
                                    <text x="525" y="150" text-anchor="middle" font-size="12">Vanilla JS, AJAX</text>
                                    <text x="525" y="165" text-anchor="middle" font-size="12">Real-time Updates</text>
                                    
                                    <!-- Media Handling -->
                                    <rect x="700" y="100" width="200" height="80" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                    <text x="800" y="130" text-anchor="middle" font-size="14" font-weight="bold">Media</text>
                                    <text x="800" y="150" text-anchor="middle" font-size="12">Image Upload</text>
                                    <text x="800" y="165" text-anchor="middle" font-size="12">Video Streaming</text>
                                    
                                    <!-- Pages -->
                                    <rect x="100" y="220" width="180" height="60" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                    <text x="190" y="245" text-anchor="middle" font-size="13">News Pages</text>
                                    <text x="190" y="265" text-anchor="middle" font-size="11">index.php, news.php</text>
                                    
                                    <rect x="320" y="220" width="180" height="60" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                    <text x="410" y="245" text-anchor="middle" font-size="13">Admin Pages</text>
                                    <text x="410" y="265" text-anchor="middle" font-size="11">admin/*.php</text>
                                    
                                    <rect x="540" y="220" width="180" height="60" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                    <text x="630" y="245" text-anchor="middle" font-size="13">Live Pages</text>
                                    <text x="630" y="265" text-anchor="middle" font-size="11">live.php, live-*.php</text>
                                    
                                    <rect x="760" y="220" width="180" height="60" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                    <text x="850" y="245" text-anchor="middle" font-size="13">Utility Pages</text>
                                    <text x="850" y="265" text-anchor="middle" font-size="11">login, search, etc.</text>
                                    
                                    <!-- Features -->
                                    <rect x="150" y="320" width="700" height="100" fill="#ffffff" stroke="#1976d2" rx="5"/>
                                    <text x="500" y="345" text-anchor="middle" font-size="14" font-weight="bold">Key Features</text>
                                    <text x="250" y="370" text-anchor="middle" font-size="12">• Responsive Design</text>
                                    <text x="500" y="370" text-anchor="middle" font-size="12">• Real-time Updates</text>
                                    <text x="750" y="370" text-anchor="middle" font-size="12">• Live Streaming</text>
                                    <text x="250" y="395" text-anchor="middle" font-size="12">• AJAX Navigation</text>
                                    <text x="500" y="395" text-anchor="middle" font-size="12">• Image Gallery</text>
                                    <text x="750" y="395" text-anchor="middle" font-size="12">• Admin Dashboard</text>
                                </svg>
                            </div>
                        </div>

                        <!-- Backend View (Hidden by default) -->
                        <div id="backendView" class="architecture-view" style="display: none;">
                            <div class="diagram-wrapper">
                                <svg width="100%" height="500" viewBox="0 0 1000 500" class="architecture-svg">
                                    <text x="500" y="30" text-anchor="middle" font-size="20" font-weight="bold" fill="#388e3c">Backend Architecture</text>
                                    
                                    <rect x="50" y="60" width="900" height="400" fill="#e8f5e8" stroke="#388e3c" stroke-width="2" rx="10"/>
                                    
                                    <!-- Core PHP -->
                                    <rect x="100" y="100" width="200" height="80" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                    <text x="200" y="125" text-anchor="middle" font-size="14" font-weight="bold">Core PHP</text>
                                    <text x="200" y="145" text-anchor="middle" font-size="12">Configuration</text>
                                    <text x="200" y="160" text-anchor="middle" font-size="12">Database Connection</text>
                                    <text x="200" y="175" text-anchor="middle" font-size="12">Helper Functions</text>
                                    
                                    <!-- API Endpoints -->
                                    <rect x="350" y="100" width="200" height="80" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                    <text x="450" y="125" text-anchor="middle" font-size="14" font-weight="bold">API Endpoints</text>
                                    <text x="450" y="145" text-anchor="middle" font-size="12">REST API</text>
                                    <text x="450" y="160" text-anchor="middle" font-size="12">JSON Responses</text>
                                    <text x="450" y="175" text-anchor="middle" font-size="12">Error Handling</text>
                                    
                                    <!-- Authentication -->
                                    <rect x="600" y="100" width="200" height="80" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                    <text x="700" y="125" text-anchor="middle" font-size="14" font-weight="bold">Authentication</text>
                                    <text x="700" y="145" text-anchor="middle" font-size="12">Session Management</text>
                                    <text x="700" y="160" text-anchor="middle" font-size="12">Role-based Access</text>
                                    <text x="700" y="175" text-anchor="middle" font-size="12">Security</text>
                                    
                                    <!-- Business Logic -->
                                    <rect x="100" y="220" width="700" height="100" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                    <text x="450" y="245" text-anchor="middle" font-size="14" font-weight="bold">Business Logic Modules</text>
                                    <text x="200" y="270" text-anchor="middle" font-size="12">News Management</text>
                                    <text x="400" y="270" text-anchor="middle" font-size="12">User Management</text>
                                    <text x="600" y="270" text-anchor="middle" font-size="12">Analytics</text>
                                    <text x="200" y="295" text-anchor="middle" font-size="12">Content Processing</text>
                                    <text x="400" y="295" text-anchor="middle" font-size="12">Live Streaming</text>
                                    <text x="600" y="295" text-anchor="middle" font-size="12">Deployment</text>
                                    
                                    <!-- Services -->
                                    <rect x="150" y="350" width="180" height="80" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                    <text x="240" y="375" text-anchor="middle" font-size="13" font-weight="bold">Services</text>
                                    <text x="240" y="395" text-anchor="middle" font-size="12">Email Service</text>
                                    <text x="240" y="410" text-anchor="middle" font-size="12">File Upload</text>
                                    <text x="240" y="425" text-anchor="middle" font-size="12">Image Processing</text>
                                    
                                    <rect x="370" y="350" width="180" height="80" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                    <text x="460" y="375" text-anchor="middle" font-size="13" font-weight="bold">Background Jobs</text>
                                    <text x="460" y="395" text-anchor="middle" font-size="12">Content Scraping</text>
                                    <text x="460" y="410" text-anchor="middle" font-size="12">Analytics Processing</text>
                                    <text x="460" y="425" text-anchor="middle" font-size="12">Cache Cleanup</text>
                                    
                                    <rect x="590" y="350" width="180" height="80" fill="#ffffff" stroke="#388e3c" rx="5"/>
                                    <text x="680" y="375" text-anchor="middle" font-size="13" font-weight="bold">Monitoring</text>
                                    <text x="680" y="395" text-anchor="middle" font-size="12">Error Logging</text>
                                    <text x="680" y="410" text-anchor="middle" font-size="12">Performance</text>
                                    <text x="680" y="425" text-anchor="middle" font-size="12">Health Checks</text>
                                </svg>
                            </div>
                        </div>

                        <!-- Database View (Hidden by default) -->
                        <div id="databaseView" class="architecture-view" style="display: none;">
                            <div class="diagram-wrapper">
                                <svg width="100%" height="600" viewBox="0 0 1200 600" class="architecture-svg">
                                    <text x="600" y="30" text-anchor="middle" font-size="20" font-weight="bold" fill="#f57c00">Database Architecture</text>
                                    
                                    <rect x="50" y="60" width="1100" height="520" fill="#fff3e0" stroke="#f57c00" stroke-width="2" rx="10"/>
                                    
                                    <!-- Core Tables -->
                                    <g id="core-tables">
                                        <rect x="100" y="100" width="200" height="120" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                        <text x="200" y="125" text-anchor="middle" font-size="14" font-weight="bold">News Tables</text>
                                        <text x="200" y="145" text-anchor="middle" font-size="11">news</text>
                                        <text x="200" y="160" text-anchor="middle" font-size="11">categories</text>
                                        <text x="200" y="175" text-anchor="middle" font-size="11">news_editions</text>
                                        <text x="200" y="190" text-anchor="middle" font-size="11">tags</text>
                                        <text x="200" y="205" text-anchor="middle" font-size="11">news_tags</text>
                                        
                                        <rect x="350" y="100" width="200" height="120" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                        <text x="450" y="125" text-anchor="middle" font-size="14" font-weight="bold">User Tables</text>
                                        <text x="450" y="145" text-anchor="middle" font-size="11">users</text>
                                        <text x="450" y="160" text-anchor="middle" font-size="11">user_roles</text>
                                        <text x="450" y="175" text-anchor="middle" font-size="11">user_profiles</text>
                                        <text x="450" y="190" text-anchor="middle" font-size="11">bookmarks</text>
                                        <text x="450" y="205" text-anchor="middle" font-size="11">notifications</text>
                                        
                                        <rect x="600" y="100" width="200" height="120" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                        <text x="700" y="125" text-anchor="middle" font-size="14" font-weight="bold">Live Tables</text>
                                        <text x="700" y="145" text-anchor="middle" font-size="11">live_stream</text>
                                        <text x="700" y="160" text-anchor="middle" font-size="11">live_deployments</text>
                                        <text x="700" y="175" text-anchor="middle" font-size="11">deployment_stats</text>
                                        <text x="700" y="190" text-anchor="middle" font-size="11">deployment_alerts</text>
                                        <text x="700" y="205" text-anchor="middle" font-size="11">live_viewers</text>
                                        
                                        <rect x="850" y="100" width="200" height="120" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                        <text x="950" y="125" text-anchor="middle" font-size="14" font-weight="bold">Analytics Tables</text>
                                        <text x="950" y="145" text-anchor="middle" font-size="11">analytics</text>
                                        <text x="950" y="160" text-anchor="middle" font-size="11">page_views</text>
                                        <text x="950" y="175" text-anchor="middle" font-size="11">user_activity</text>
                                        <text x="950" y="190" text-anchor="middle" font-size="11">popular_content</text>
                                        <text x="950" y="205" text-anchor="middle" font-size="11">search_logs</text>
                                    </g>
                                    
                                    <!-- Supporting Tables -->
                                    <g id="supporting-tables">
                                        <rect x="100" y="250" width="200" height="100" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                        <text x="200" y="275" text-anchor="middle" font-size="14" font-weight="bold">Content Tables</text>
                                        <text x="200" y="295" text-anchor="middle" font-size="11">polls</text>
                                        <text x="200" y="310" text-anchor="middle" font-size="11">poll_options</text>
                                        <text x="200" y="325" text-anchor="middle" font-size="11">comments</text>
                                        <text x="200" y="340" text-anchor="middle" font-size="11">ads</text>
                                        
                                        <rect x="350" y="250" width="200" height="100" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                        <text x="450" y="275" text-anchor="middle" font-size="14" font-weight="bold">System Tables</text>
                                        <text x="450" y="295" text-anchor="middle" font-size="11">settings</text>
                                        <text x="450" y="310" text-anchor="middle" font-size="11">logs</text>
                                        <text x="450" y="325" text-anchor="middle" font-size="11">backup</text>
                                        <text x="450" y="340" text-anchor="middle" font-size="11">maintenance</text>
                                        
                                        <rect x="600" y="250" width="200" height="100" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                        <text x="700" y="275" text-anchor="middle" font-size="14" font-weight="bold">Media Tables</text>
                                        <text x="700" y="295" text-anchor="middle" font-size="11">media_files</text>
                                        <text x="700" y="310" text-anchor="middle" font-size="11">thumbnails</text>
                                        <text x="700" y="325" text-anchor="middle" font-size="11">media_metadata</text>
                                        <text x="700" y="340" text-anchor="middle" font-size="11">media_stats</text>
                                    </g>
                                    
                                    <!-- Database Features -->
                                    <rect x="100" y="380" width="950" height="170" fill="#ffffff" stroke="#f57c00" rx="5"/>
                                    <text x="575" y="405" text-anchor="middle" font-size="14" font-weight="bold">Database Features</text>
                                    
                                    <text x="200" y="430" text-anchor="middle" font-size="12">• MySQL 8.0+</text>
                                    <text x="200" y="450" text-anchor="middle" font-size="12">• InnoDB Engine</text>
                                    <text x="200" y="470" text-anchor="middle" font-size="12">• Foreign Keys</text>
                                    <text x="200" y="490" text-anchor="middle" font-size="12">• Indexes</text>
                                    <text x="200" y="510" text-anchor="middle" font-size="12">• Triggers</text>
                                    <text x="200" y="530" text-anchor="middle" font-size="12">• Stored Procedures</text>
                                    
                                    <text x="450" y="430" text-anchor="middle" font-size="12">• JSON Support</text>
                                    <text x="450" y="450" text-anchor="middle" font-size="12">• Full-text Search</text>
                                    <text x="450" y="470" text-anchor="middle" font-size="12">• Backup System</text>
                                    <text x="450" y="490" text-anchor="middle" font-size="12">• Migration Scripts</text>
                                    <text x="450" y="510" text-anchor="middle" font-size="12">• Performance Monitoring</text>
                                    <text x="450" y="530" text-anchor="middle" font-size="12">• Query Optimization</text>
                                    
                                    <text x="700" y="430" text-anchor="middle" font-size="12">• Connection Pooling</text>
                                    <text x="700" y="450" text-anchor="middle" font-size="12">• Caching Layer</text>
                                    <text x="700" y="470" text-anchor="middle" font-size="12">• Replication Ready</text>
                                    <text x="700" y="490" text-anchor="middle" font-size="12">• Data Validation</text>
                                    <text x="700" y="510" text-anchor="middle" font-size="12">• Security Constraints</text>
                                    <text x="700" y="530" text-anchor="middle" font-size="12">• Audit Logging</text>
                                    
                                    <text x="950" y="430" text-anchor="middle" font-size="12">• <?php echo count($tables); ?> Tables</text>
                                    <text x="950" y="450" text-anchor="middle" font-size="12">• Normalized</text>
                                    <text x="950" y="470" text-anchor="middle" font-size="12">• Scalable</text>
                                    <text x="950" y="490" text-anchor="middle" font-size="12">• Documented</text>
                                    <text x="950" y="510" text-anchor="middle" font-size="12">• Maintained</text>
                                    <text x="950" y="530" text-anchor="middle" font-size="12">• Optimized</text>
                                </svg>
                            </div>
                        </div>

                        <!-- Infrastructure View (Hidden by default) -->
                        <div id="infrastructureView" class="architecture-view" style="display: none;">
                            <div class="diagram-wrapper">
                                <svg width="100%" height="500" viewBox="0 0 1000 500" class="architecture-svg">
                                    <text x="500" y="30" text-anchor="middle" font-size="20" font-weight="bold" fill="#d32f2f">Infrastructure Architecture</text>
                                    
                                    <rect x="50" y="60" width="900" height="400" fill="#ffebee" stroke="#d32f2f" stroke-width="2" rx="10"/>
                                    
                                    <!-- Web Server -->
                                    <rect x="100" y="100" width="250" height="100" fill="#ffffff" stroke="#d32f2f" rx="5"/>
                                    <text x="225" y="125" text-anchor="middle" font-size="14" font-weight="bold">Web Server</text>
                                    <text x="225" y="145" text-anchor="middle" font-size="12">Apache/Nginx</text>
                                    <text x="225" y="160" text-anchor="middle" font-size="12">PHP 8.0+</text>
                                    <text x="225" y="175" text-anchor="middle" font-size="12">HTTPS/SSL</text>
                                    <text x="225" y="190" text-anchor="middle" font-size="12">URL Rewriting</text>
                                    
                                    <!-- Application Server -->
                                    <rect x="400" y="100" width="250" height="100" fill="#ffffff" stroke="#d32f2f" rx="5"/>
                                    <text x="525" y="125" text-anchor="middle" font-size="14" font-weight="bold">Application Server</text>
                                    <text x="525" y="145" text-anchor="middle" font-size="12">PHP-FPM</text>
                                    <text x="525" y="160" text-anchor="middle" font-size="12">Session Storage</text>
                                    <text x="525" y="175" text-anchor="middle" font-size="12">Error Handling</text>
                                    <text x="525" y="190" text-anchor="middle" font-size="12">Logging</text>
                                    
                                    <!-- Database Server -->
                                    <rect x="700" y="100" width="200" height="100" fill="#ffffff" stroke="#d32f2f" rx="5"/>
                                    <text x="800" y="125" text-anchor="middle" font-size="14" font-weight="bold">Database Server</text>
                                    <text x="800" y="145" text-anchor="middle" font-size="12">MySQL 8.0</text>
                                    <text x="800" y="160" text-anchor="middle" font-size="12">Optimized</text>
                                    <text x="800" y="175" text-anchor="middle" font-size="12">Backups</text>
                                    <text x="800" y="190" text-anchor="middle" font-size="12">Monitoring</text>
                                    
                                    <!-- Storage -->
                                    <rect x="100" y="230" width="200" height="80" fill="#ffffff" stroke="#d32f2f" rx="5"/>
                                    <text x="200" y="255" text-anchor="middle" font-size="14" font-weight="bold">File Storage</text>
                                    <text x="200" y="275" text-anchor="middle" font-size="12">Local Storage</text>
                                    <text x="200" y="290" text-anchor="middle" font-size="12">Uploads Directory</text>
                                    <text x="200" y="305" text-anchor="middle" font-size="12">Media Files</text>
                                    
                                    <!-- Cache -->
                                    <rect x="350" y="230" width="200" height="80" fill="#ffffff" stroke="#d32f2f" rx="5"/>
                                    <text x="450" y="255" text-anchor="middle" font-size="14" font-weight="bold">Cache Layer</text>
                                    <text x="450" y="275" text-anchor="middle" font-size="12">PHP Cache</text>
                                    <text x="450" y="290" text-anchor="middle" font-size="12">Browser Cache</text>
                                    <text x="450" y="305" text-anchor="middle" font-size="12">Database Cache</text>
                                    
                                    <!-- Security -->
                                    <rect x="600" y="230" width="200" height="80" fill="#ffffff" stroke="#d32f2f" rx="5"/>
                                    <text x="700" y="255" text-anchor="middle" font-size="14" font-weight="bold">Security</text>
                                    <text x="700" y="275" text-anchor="middle" font-size="12">Firewall</text>
                                    <text x="700" y="290" text-anchor="middle" font-size="12">SSL/TLS</text>
                                    <text x="700" y="305" text-anchor="middle" font-size="12">Input Validation</text>
                                    
                                    <!-- Monitoring -->
                                    <rect x="850" y="230" width="150" height="80" fill="#ffffff" stroke="#d32f2f" rx="5"/>
                                    <text x="925" y="255" text-anchor="middle" font-size="14" font-weight="bold">Monitoring</text>
                                    <text x="925" y="275" text-anchor="middle" font-size="12">Logs</text>
                                    <text x="925" y="290" text-anchor="middle" font-size="12">Analytics</text>
                                    <text x="925" y="305" text-anchor="middle" font-size="12">Health</text>
                                    
                                    <!-- Deployment -->
                                    <rect x="250" y="340" width="500" height="90" fill="#ffffff" stroke="#d32f2f" rx="5"/>
                                    <text x="500" y="365" text-anchor="middle" font-size="14" font-weight="bold">Deployment & DevOps</text>
                                    <text x="350" y="385" text-anchor="middle" font-size="12">• Manual Deployment</text>
                                    <text x="350" y="405" text-anchor="middle" font-size="12">• Backup Scripts</text>
                                    <text x="350" y="420" text-anchor="middle" font-size="12">• Migration Tools</text>
                                    <text x="650" y="385" text-anchor="middle" font-size="12">• Environment Config</text>
                                    <text x="650" y="405" text-anchor="middle" font-size="12">• Error Tracking</text>
                                    <text x="650" y="420" text-anchor="middle" font-size="12">• Performance Monitoring</text>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Component Details Panel -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <span id="componentTitle">Component Details</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="componentDetails">
                                        <p class="text-muted">Click on any component in the diagram to view detailed information about its functionality, technologies used, and connections to other components.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Specifications -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Technical Stack</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Frontend:</strong>
                            <ul class="list-unstyled ms-3">
                                <li>• HTML5, CSS3, JavaScript</li>
                                <li>• Bootstrap 5</li>
                                <li>• FontAwesome Icons</li>
                                <li>• Chart.js</li>
                                <li>• AJAX/Fetch API</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <strong>Backend:</strong>
                            <ul class="list-unstyled ms-3">
                                <li>• PHP 8.0+</li>
                                <li>• MySQL 8.0</li>
                                <li>• RESTful API</li>
                                <li>• Session Management</li>
                                <li>• File Upload System</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Features</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Authentication:</strong>
                            <ul class="list-unstyled ms-3">
                                <li>• Session-based Auth</li>
                                <li>• Role-based Access</li>
                                <li>• Password Hashing</li>
                                <li>• Login Attempts Limit</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <strong>Data Protection:</strong>
                            <ul class="list-unstyled ms-3">
                                <li>• Input Validation</li>
                                <li>• SQL Injection Prevention</li>
                                <li>• XSS Protection</li>
                                <li>• CSRF Protection</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.architecture-container {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    min-height: 600px;
}

.architecture-view {
    animation: fadeIn 0.3s ease-in;
}

.diagram-wrapper {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.architecture-svg {
    max-width: 100%;
    height: auto;
}

.architecture-svg rect {
    transition: all 0.3s ease;
    cursor: pointer;
}

.architecture-svg rect:hover {
    filter: brightness(0.95);
    transform: translateY(-2px);
}

.architecture-svg text {
    pointer-events: none;
    user-select: none;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.btn-group .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.component-highlight {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
</style>

<script>
let currentView = 'overview';

function showView(viewName) {
    // Hide all views
    document.querySelectorAll('.architecture-view').forEach(view => {
        view.style.display = 'none';
    });
    
    // Show selected view
    document.getElementById(viewName + 'View').style.display = 'block';
    
    // Update button states
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Update title
    const titles = {
        'overview': 'System Architecture Overview',
        'frontend': 'Frontend Architecture',
        'backend': 'Backend Architecture',
        'database': 'Database Architecture',
        'infrastructure': 'Infrastructure Architecture'
    };
    document.getElementById('viewTitle').textContent = titles[viewName];
    
    currentView = viewName;
}

function toggleView() {
    const views = ['overview', 'frontend', 'backend', 'database', 'infrastructure'];
    const currentIndex = views.indexOf(currentView);
    const nextIndex = (currentIndex + 1) % views.length;
    const nextView = views[nextIndex];
    
    // Simulate button click
    const buttons = document.querySelectorAll('.btn-group .btn');
    buttons[nextIndex].click();
}

function exportDiagram() {
    // Create a temporary link element
    const link = document.createElement('a');
    link.download = 'pk-live-news-architecture.svg';
    
    // Get the SVG content
    const svg = document.querySelector('.architecture-svg');
    const svgData = new XMLSerializer().serializeToString(svg);
    
    // Create blob and download
    const blob = new Blob([svgData], { type: 'image/svg+xml' });
    link.href = URL.createObjectURL(blob);
    link.click();
}

// Component interaction
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to SVG rectangles
    const svgRects = document.querySelectorAll('.architecture-svg rect[rx="5"]');
    
    const componentInfo = {
        'User Interface': {
            title: 'User Interface Layer',
            description: 'The frontend user interface built with responsive design principles.',
            technologies: ['HTML5', 'CSS3', 'Bootstrap 5', 'JavaScript'],
            features: ['Responsive Design', 'Mobile-first', 'Accessibility', 'SEO Optimized']
        },
        'Admin Dashboard': {
            title: 'Admin Dashboard',
            description: 'Comprehensive admin interface for content management and system monitoring.',
            technologies: ['PHP', 'MySQL', 'Chart.js', 'AJAX'],
            features: ['Content Management', 'User Management', 'Analytics', 'Live Monitoring']
        },
        'Live Streaming': {
            title: 'Live Streaming Platform',
            description: 'Real-time video streaming with viewer analytics and engagement tools.',
            technologies: ['WebRTC', 'WebSocket', 'FFmpeg', 'HLS'],
            features: ['Real-time Streaming', 'Viewer Count', 'Chat System', 'Recording']
        },
        'MySQL Database': {
            title: 'MySQL Database',
            description: 'Primary data storage solution with optimized performance and reliability.',
            technologies: ['MySQL 8.0', 'InnoDB', 'JSON Support', 'Full-text Search'],
            features: ['ACID Compliance', 'Foreign Keys', 'Indexes', 'Backup System']
        }
    };
    
    svgRects.forEach(rect => {
        rect.addEventListener('click', function() {
            // Get component name from adjacent text
            const texts = this.parentNode.querySelectorAll('text');
            let componentName = '';
            
            for (let text of texts) {
                const rectBounds = this.getBoundingClientRect();
                const textBounds = text.getBoundingClientRect();
                
                if (Math.abs(rectBounds.top - textBounds.top) < 50) {
                    componentName = text.textContent;
                    break;
                }
            }
            
            // Update component details
            const info = componentInfo[componentName];
            if (info) {
                document.getElementById('componentTitle').textContent = info.title;
                document.getElementById('componentDetails').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Description</h6>
                            <p>${info.description}</p>
                            
                            <h6>Technologies</h6>
                            <div class="d-flex flex-wrap gap-2">
                                ${info.technologies.map(tech => `<span class="badge bg-primary">${tech}</span>`).join('')}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Key Features</h6>
                            <ul class="list-unstyled">
                                ${info.features.map(feature => `<li>• ${feature}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                `;
                
                // Add highlight animation
                this.classList.add('component-highlight');
                setTimeout(() => {
                    this.classList.remove('component-highlight');
                }, 2000);
            }
        });
    });
    
    // Add hover effects
    svgRects.forEach(rect => {
        rect.addEventListener('mouseenter', function() {
            this.style.cursor = 'pointer';
            this.style.filter = 'brightness(0.9)';
        });
        
        rect.addEventListener('mouseleave', function() {
            this.style.filter = '';
        });
    });
});

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowRight') {
        toggleView();
    } else if (e.key === 'ArrowLeft') {
        const views = ['overview', 'frontend', 'backend', 'database', 'infrastructure'];
        const currentIndex = views.indexOf(currentView);
        const prevIndex = (currentIndex - 1 + views.length) % views.length;
        const prevView = views[prevIndex];
        
        const buttons = document.querySelectorAll('.btn-group .btn');
        buttons[prevIndex].click();
    }
});
</script>
