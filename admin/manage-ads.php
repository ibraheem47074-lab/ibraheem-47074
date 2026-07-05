<?php
require_once '../config/database.php';
require_once '../includes/ad-functions.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: ../login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_ad'])) {
        $title = clean_input($_POST['title']);
        $ad_code = $_POST['ad_code'];
        $position = $_POST['position'];
        $size = clean_input($_POST['size']);
        $page_type = $_POST['page_type'];
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $device_type = $_POST['device_type'];
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        
        $sql = "INSERT INTO advertisements (title, code, position, size, page_type, category_id, device_type, status, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $status = 'active';
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssissss", $title, $ad_code, $position, $size, $page_type, $category_id, $device_type, $status, $start_date, $end_date);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Advertisement added successfully!";
        } else {
            $error = "Error adding advertisement: " . mysqli_error($conn);
        }
    }
    
    if (isset($_POST['update_ad'])) {
        $ad_id = (int)$_POST['ad_id'];
        $title = clean_input($_POST['title']);
        $ad_code = $_POST['ad_code'];
        $position = $_POST['position'];
        $size = clean_input($_POST['size']);
        $page_type = $_POST['page_type'];
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $device_type = $_POST['device_type'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        
        $sql = "UPDATE advertisements SET title = ?, code = ?, position = ?, size = ?, 
                page_type = ?, category_id = ?, device_type = ?, status = ?, start_date = ?, end_date = ? WHERE id = ?";
        $status = $is_active ? 'active' : 'inactive';
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssissssi", $title, $ad_code, $position, $size, 
                              $page_type, $category_id, $device_type, $status, $start_date, $end_date, $ad_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Advertisement updated successfully!";
        } else {
            $error = "Error updating advertisement: " . mysqli_error($conn);
        }
    }
    
    if (isset($_POST['delete_ad'])) {
        $ad_id = (int)$_POST['ad_id'];
        
        $sql = "DELETE FROM advertisements WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $ad_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Advertisement deleted successfully!";
        } else {
            $error = "Error deleting advertisement: " . mysqli_error($conn);
        }
    }
}

// Handle status toggle
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $ad_id = (int)$_GET['id'];
    
    $sql = "UPDATE advertisements SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $ad_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Advertisement status updated successfully!";
    } else {
        $error = "Error updating advertisement status!";
    }
}

// Check if advertisements table exists, create it if not
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'advertisements'");
if (mysqli_num_rows($table_check) == 0) {
    // Create advertisements table
    $create_table_sql = "CREATE TABLE IF NOT EXISTS advertisements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        position ENUM('header', 'sidebar', 'footer', 'all') DEFAULT 'sidebar',
        image VARCHAR(500),
        redirect_url VARCHAR(500),
        code TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        start_date DATE DEFAULT NULL,
        end_date DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    mysqli_query($conn, $create_table_sql);
    
    // Insert sample advertisements
    $insert_sql = "INSERT INTO advertisements (title, position, image, redirect_url, status, start_date, end_date) VALUES 
    ('Sample Business Ad - Sidebar', 'sidebar', 'uploads/ads/69adaaa0ab59c.jpg', 'https://example-business.com', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
    ('Tech Store Promotion', 'header', 'uploads/ads/69adaaa0ab59c.jpg', 'https://techstore.example', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
    ('Local Services Ad', 'footer', 'uploads/ads/69adaaa0ab59c.jpg', 'https://localservices.example', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
    ('Restaurant Special Offer', 'sidebar', 'uploads/ads/69adaaa0ab59c.jpg', 'https://restaurant.example', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
    ('E-commerce Banner', 'all', 'uploads/ads/69adaaa0ab59c.jpg', 'https://shop.example', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY))";
    
    mysqli_query($conn, $insert_sql);
}

// Get all advertisements
$sql = "SELECT * FROM advertisements ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$advertisements = [];
while ($row = mysqli_fetch_assoc($result)) {
    $advertisements[] = $row;
}

// Get ad for editing
$editing_ad = null;
if (isset($_GET['edit'])) {
    $ad_id = (int)$_GET['edit'];
    $sql = "SELECT * FROM advertisements WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $ad_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editing_ad = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Advertisements - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .ad-preview {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
        }
        .ad-stats {
            display: flex;
            gap: 15px;
            margin: 10px 0;
            flex-wrap: wrap;
        }
        .ad-stat {
            text-align: center;
            padding: 10px;
            background: #e9ecef;
            border-radius: 4px;
            min-width: 80px;
        }
        .code-editor {
            font-family: 'Courier New', monospace;
            min-height: 150px;
        }
        .ad-code-display {
            font-size: 12px;
            color: #666;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 3px;
            max-height: 100px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
        
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Advertisements</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdModal">
                        <i class="fas fa-plus"></i> Add New Advertisement
                    </button>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Advertisements List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Advertisements</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Position</th>
                                        <th>Page Type</th>
                                        <th>Category</th>
                                        <th>Device</th>
                                        <th>Size</th>
                                        <th>Status</th>
                                        <th>Impressions</th>
                                        <th>Clicks</th>
                                        <th>CTR</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($advertisements as $ad): ?>
                                        <?php 
                                        $stats = get_ad_statistics($ad['id']);
                                        $ctr = ($stats['total_impressions'] ?? 0) > 0 ? 
                                               round(($stats['total_clicks'] / $stats['total_impressions']) * 100, 2) : 0;
                                        ?>
                                        <tr>
                                            <td><?php echo $ad['id']; ?></td>
                                            <td><?php echo htmlspecialchars($ad['title']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    $position_colors = [
                                                        'header' => 'primary', 'sidebar' => 'info', 'footer' => 'success', 'all' => 'warning',
                                                        'live_header' => 'danger', 'live_sidebar' => 'danger', 'live_footer' => 'danger', 'live_popup' => 'danger',
                                                        'performance_header' => 'secondary', 'performance_sidebar' => 'secondary', 'performance_footer' => 'secondary', 'performance_inline' => 'secondary',
                                                        'contact_header' => 'info', 'contact_sidebar' => 'info', 'contact_footer' => 'info',
                                                        'category_header' => 'success', 'category_sidebar' => 'success', 'category_footer' => 'success', 'category_inline' => 'success',
                                                        'home_hero' => 'primary', 'home_featured' => 'primary', 'home_sidebar' => 'primary', 'home_footer' => 'primary',
                                                        'news_inline' => 'warning', 'search_sidebar' => 'dark', 'profile_sidebar' => 'dark'
                                                    ];
                                                    echo $position_colors[$ad['position']] ?? 'secondary'; 
                                                ?>">
                                                    <?php 
                                                    $position_labels = [
                                                        'header' => 'Header', 'sidebar' => 'Sidebar', 'footer' => 'Footer', 'all' => 'All Pages',
                                                        'live_header' => 'Live Header', 'live_sidebar' => 'Live Sidebar', 'live_footer' => 'Live Footer', 'live_popup' => 'Live Popup',
                                                        'performance_header' => 'Perf. Header', 'performance_sidebar' => 'Perf. Sidebar', 'performance_footer' => 'Perf. Footer', 'performance_inline' => 'Perf. Inline',
                                                        'contact_header' => 'Contact Header', 'contact_sidebar' => 'Contact Sidebar', 'contact_footer' => 'Contact Footer',
                                                        'category_header' => 'Cat. Header', 'category_sidebar' => 'Cat. Sidebar', 'category_footer' => 'Cat. Footer', 'category_inline' => 'Cat. Inline',
                                                        'home_hero' => 'Home Hero', 'home_featured' => 'Home Featured', 'home_sidebar' => 'Home Sidebar', 'home_footer' => 'Home Footer',
                                                        'news_inline' => 'News Inline', 'search_sidebar' => 'Search Sidebar', 'profile_sidebar' => 'Profile Sidebar'
                                                    ];
                                                    echo $position_labels[$ad['position']] ?? ucfirst($ad['position']); 
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php 
                                                    $page_type_labels = [
                                                        'all' => 'All Pages', 'home' => 'Home', 'category' => 'Category', 'news' => 'News',
                                                        'live' => 'Live', 'contact' => 'Contact', 'search' => 'Search', 'profile' => 'Profile', 'performance' => 'Performance'
                                                    ];
                                                    echo $page_type_labels[$ad['page_type']] ?? ucfirst($ad['page_type']); 
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($ad['category_id'])): 
                                                    $cat_query = "SELECT name FROM categories WHERE id = " . (int)$ad['category_id'];
                                                    $cat_result = mysqli_query($conn, $cat_query);
                                                    $category = mysqli_fetch_assoc($cat_result);
                                                    echo '<span class="badge bg-info">' . htmlspecialchars($category['name'] ?? 'Unknown') . '</span>';
                                                else: 
                                                    echo '<span class="text-muted">All</span>';
                                                endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $ad['device_type'] == 'all' ? 'secondary' : 
                                                         ($ad['device_type'] == 'desktop' ? 'primary' : 
                                                         ($ad['device_type'] == 'mobile' ? 'success' : 'warning')); 
                                                ?>">
                                                    <?php 
                                                    $device_labels = ['all' => 'All', 'desktop' => 'Desktop', 'mobile' => 'Mobile', 'tablet' => 'Tablet'];
                                                    echo $device_labels[$ad['device_type']] ?? ucfirst($ad['device_type']); 
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($ad['size'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($ad['status'] ?? 'inactive') === 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ($ad['status'] ?? 'inactive') === 'active' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($stats['total_impressions'] ?? 0); ?></td>
                                            <td><?php echo number_format($stats['total_clicks'] ?? 0); ?></td>
                                            <td><?php echo $ctr; ?>%</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewAd(<?php echo $ad['id']; ?>)"
                                                            title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning" 
                                                            onclick="editAd(<?php echo $ad['id']; ?>)"
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-<?php echo ($ad['status'] ?? 'inactive') === 'active' ? 'secondary' : 'success'; ?>" 
                                                            onclick="toggleStatus(<?php echo $ad['id']; ?>)"
                                                            title="<?php echo ($ad['status'] ?? 'inactive') === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="fas fa-<?php echo ($ad['status'] ?? 'inactive') === 'active' ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteAd(<?php echo $ad['id']; ?>)"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Advertisement Modal -->
    <div class="modal fade" id="addAdModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Advertisement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="position" class="form-label">Position</label>
                            <select class="form-select" id="position" name="position" required onchange="updatePositionDetails()">
                                <option value="">Select Position</option>
                                
                                <optgroup label="General Positions">
                                    <option value="header">Header Banner (728x90)</option>
                                    <option value="sidebar">Sidebar Rectangle (300x250)</option>
                                    <option value="footer">Footer Banner (728x90)</option>
                                    <option value="all">All Pages</option>
                                </optgroup>
                                
                                <optgroup label="Live Section Ads">
                                    <option value="live_header">Live Header (728x90)</option>
                                    <option value="live_sidebar">Live Sidebar (300x250)</option>
                                    <option value="live_footer">Live Footer (728x90)</option>
                                    <option value="live_popup">Live Popup (400x300)</option>
                                </optgroup>
                                
                                <optgroup label="Performance Section Ads">
                                    <option value="performance_header">Performance Header (728x90)</option>
                                    <option value="performance_sidebar">Performance Sidebar (300x250)</option>
                                    <option value="performance_footer">Performance Footer (728x90)</option>
                                    <option value="performance_inline">Performance Inline (468x60)</option>
                                </optgroup>
                                
                                <optgroup label="Contact Page Ads">
                                    <option value="contact_header">Contact Header (728x90)</option>
                                    <option value="contact_sidebar">Contact Sidebar (300x250)</option>
                                    <option value="contact_footer">Contact Footer (728x90)</option>
                                </optgroup>
                                
                                <optgroup label="Category-Specific Ads">
                                    <option value="category_header">Category Header (728x90)</option>
                                    <option value="category_sidebar">Category Sidebar (300x250)</option>
                                    <option value="category_footer">Category Footer (728x90)</option>
                                    <option value="category_inline">Category Inline (468x60)</option>
                                </optgroup>
                                
                                <optgroup label="Home Page Ads">
                                    <option value="home_hero">Home Hero Banner (1200x300)</option>
                                    <option value="home_featured">Home Featured (600x200)</option>
                                    <option value="home_sidebar">Home Sidebar (300x250)</option>
                                    <option value="home_footer">Home Footer (728x90)</option>
                                </optgroup>
                                
                                <optgroup label="Other Page Ads">
                                    <option value="news_inline">News Inline (468x60)</option>
                                    <option value="search_sidebar">Search Sidebar (300x250)</option>
                                    <option value="profile_sidebar">Profile Sidebar (300x250)</option>
                                </optgroup>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="page_type" class="form-label">Page Type</label>
                            <select class="form-select" id="page_type" name="page_type">
                                <option value="all">All Pages</option>
                                <option value="home">Home Page</option>
                                <option value="category">Category Pages</option>
                                <option value="news">News Articles</option>
                                <option value="live">Live Streaming</option>
                                <option value="contact">Contact Page</option>
                                <option value="search">Search Results</option>
                                <option value="profile">User Profile</option>
                                <option value="performance">Performance Analysis</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Specific Category (Optional)</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                <?php
                                $categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
                                $categories_result = mysqli_query($conn, $categories_query);
                                while ($cat = mysqli_fetch_assoc($categories_result)) {
                                    echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="device_type" class="form-label">Device Type</label>
                            <select class="form-select" id="device_type" name="device_type">
                                <option value="all">All Devices</option>
                                <option value="desktop">Desktop Only</option>
                                <option value="mobile">Mobile Only</option>
                                <option value="tablet">Tablet Only</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="size" class="form-label">Size</label>
                            <input type="text" class="form-control" id="size" name="size" 
                                   placeholder="e.g., 728x90, 300x250" readonly>
                            <small class="text-muted">Size is automatically set based on position</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ad_code" class="form-label">Ad Code (HTML)</label>
                            <textarea class="form-control code-editor" id="ad_code" name="ad_code" 
                                      rows="6" required placeholder="<a href='#'><img src='...' alt='ad'></a>"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_ad" class="btn btn-primary">Add Advertisement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Ad Modal -->
    <div class="modal fade" id="viewAdModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Advertisement Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="adDetails"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Position size mapping
        const positionSizes = {
            'header': '728x90',
            'sidebar': '300x250',
            'footer': '728x90',
            'all': '300x250',
            'live_header': '728x90',
            'live_sidebar': '300x250',
            'live_footer': '728x90',
            'live_popup': '400x300',
            'performance_header': '728x90',
            'performance_sidebar': '300x250',
            'performance_footer': '728x90',
            'performance_inline': '468x60',
            'contact_header': '728x90',
            'contact_sidebar': '300x250',
            'contact_footer': '728x90',
            'category_header': '728x90',
            'category_sidebar': '300x250',
            'category_footer': '728x90',
            'category_inline': '468x60',
            'home_hero': '1200x300',
            'home_featured': '600x200',
            'home_sidebar': '300x250',
            'home_footer': '728x90',
            'news_inline': '468x60',
            'search_sidebar': '300x250',
            'profile_sidebar': '300x250'
        };
        
        function updatePositionDetails() {
            const position = document.getElementById('position').value;
            const sizeInput = document.getElementById('size');
            const pageTypeSelect = document.getElementById('page_type');
            const categorySelect = document.getElementById('category_id');
            
            // Update size based on position
            if (position && positionSizes[position]) {
                sizeInput.value = positionSizes[position];
            } else {
                sizeInput.value = '';
            }
            
            // Auto-set page type based on position
            if (position) {
                if (position.startsWith('live_')) {
                    pageTypeSelect.value = 'live';
                } else if (position.startsWith('performance_')) {
                    pageTypeSelect.value = 'performance';
                } else if (position.startsWith('contact_')) {
                    pageTypeSelect.value = 'contact';
                } else if (position.startsWith('category_')) {
                    pageTypeSelect.value = 'category';
                    categorySelect.style.display = 'block';
                } else if (position.startsWith('home_')) {
                    pageTypeSelect.value = 'home';
                } else if (position === 'news_inline') {
                    pageTypeSelect.value = 'news';
                } else if (position === 'search_sidebar') {
                    pageTypeSelect.value = 'search';
                } else if (position === 'profile_sidebar') {
                    pageTypeSelect.value = 'profile';
                } else {
                    pageTypeSelect.value = 'all';
                }
            }
        }
        
        function viewAd(adId) {
            fetch('get-ad-details.php?id=' + adId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('adDetails').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('viewAdModal')).show();
                });
        }

        function editAd(adId) {
            window.location.href = '?edit=' + adId;
        }

        function toggleStatus(adId) {
            if (confirm('Are you sure you want to toggle this advertisement status?')) {
                window.location.href = '?toggle_status=1&id=' + adId;
            }
        }

        function deleteAd(adId) {
            if (confirm('Are you sure you want to delete this advertisement?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="ad_id" value="' + adId + '">' +
                                '<input type="hidden" name="delete_ad" value="1">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updatePositionDetails();
        });
    </script>
</body>
</html>
