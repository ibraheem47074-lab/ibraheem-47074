<?php
require_once 'config/database.php';

echo "<h2>Updating Advertisement Positions Schema</h2>";

// Check if advertisements table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'advertisements'");
if (mysqli_num_rows($table_check) == 0) {
    echo "<p class='text-danger'>✗ Advertisements table does not exist. Please run setup first.</p>";
    exit;
}

// Get current position ENUM values
$position_query = "SHOW COLUMNS FROM advertisements LIKE 'position'";
$position_result = mysqli_query($conn, $position_query);
$position_row = mysqli_fetch_assoc($position_result);
$current_enum = $position_row['Type'];

echo "<p class='text-info'>ℹ Current position column: $current_enum</p>";

// Update the position ENUM to include new positions
$update_sql = "ALTER TABLE advertisements MODIFY COLUMN position ENUM(
    'header', 'sidebar', 'footer', 'all',
    'live_header', 'live_sidebar', 'live_footer', 'live_popup',
    'performance_header', 'performance_sidebar', 'performance_footer', 'performance_inline',
    'contact_header', 'contact_sidebar', 'contact_footer',
    'category_header', 'category_sidebar', 'category_footer', 'category_inline',
    'home_hero', 'home_featured', 'home_sidebar', 'home_footer',
    'news_inline', 'search_sidebar', 'profile_sidebar'
) DEFAULT 'sidebar'";

if (mysqli_query($conn, $update_sql)) {
    echo "<p class='text-success'>✓ Updated position column with new ad positions</p>";
} else {
    echo "<p class='text-danger'>✗ Error updating position column: " . mysqli_error($conn) . "</p>";
}

// Add category_id column for category-specific ads
$category_check = mysqli_query($conn, "SHOW COLUMNS FROM advertisements LIKE 'category_id'");
if (mysqli_num_rows($category_check) == 0) {
    $add_category_sql = "ALTER TABLE advertisements ADD COLUMN category_id INT NULL AFTER position";
    if (mysqli_query($conn, $add_category_sql)) {
        echo "<p class='text-success'>✓ Added category_id column for category-specific ads</p>";
        
        // Add foreign key constraint
        $add_fk_sql = "ALTER TABLE advertisements ADD CONSTRAINT fk_ad_category 
                       FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL";
        if (mysqli_query($conn, $add_fk_sql)) {
            echo "<p class='text-success'>✓ Added foreign key constraint for category_id</p>";
        } else {
            echo "<p class='text-warning'>⚠ Could not add foreign key (categories table may not exist): " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p class='text-danger'>✗ Error adding category_id column: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p class='text-info'>ℹ category_id column already exists</p>";
}

// Add page_type column for page-specific targeting
$page_type_check = mysqli_query($conn, "SHOW COLUMNS FROM advertisements LIKE 'page_type'");
if (mysqli_num_rows($page_type_check) == 0) {
    $add_page_type_sql = "ALTER TABLE advertisements ADD COLUMN page_type ENUM('all', 'home', 'category', 'news', 'live', 'contact', 'search', 'profile', 'performance') DEFAULT 'all' AFTER category_id";
    if (mysqli_query($conn, $add_page_type_sql)) {
        echo "<p class='text-success'>✓ Added page_type column for page-specific targeting</p>";
    } else {
        echo "<p class='text-danger'>✗ Error adding page_type column: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p class='text-info'>ℹ page_type column already exists</p>";
}

// Add device_type column for device-specific targeting
$device_check = mysqli_query($conn, "SHOW COLUMNS FROM advertisements LIKE 'device_type'");
if (mysqli_num_rows($device_check) == 0) {
    $add_device_sql = "ALTER TABLE advertisements ADD COLUMN device_type ENUM('all', 'desktop', 'mobile', 'tablet') DEFAULT 'all' AFTER page_type";
    if (mysqli_query($conn, $add_device_sql)) {
        echo "<p class='text-success'>✓ Added device_type column for device-specific targeting</p>";
    } else {
        echo "<p class='text-danger'>✗ Error adding device_type column: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p class='text-info'>ℹ device_type column already exists</p>";
}

// Show updated table structure
echo "<h3>Updated Table Structure:</h3>";
$structure_query = "DESCRIBE advertisements";
$structure_result = mysqli_query($conn, $structure_query);
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = mysqli_fetch_assoc($structure_result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Insert sample ads for new positions
echo "<h3>Adding Sample Ads for New Positions:</h3>";

$sample_ads = [
    [
        'title' => 'Live Stream Banner Ad',
        'position' => 'live_header',
        'page_type' => 'live',
        'code' => '<a href="https://example.com"><img src="uploads/ads/live-banner.jpg" alt="Live Stream Ad" style="width:100%;height:90px;"></a>',
        'status' => 'active'
    ],
    [
        'title' => 'Performance Analysis Widget',
        'position' => 'performance_sidebar',
        'page_type' => 'performance',
        'code' => '<div style="background:#f0f0f0;padding:10px;border:1px solid #ccc;"><h4>Performance Tools</h4><a href="https://tools.example.com">Try our Analytics</a></div>',
        'status' => 'active'
    ],
    [
        'title' => 'Contact Page Service Ad',
        'position' => 'contact_sidebar',
        'page_type' => 'contact',
        'code' => '<div style="background:#e8f4f8;padding:15px;border-radius:5px;"><h3>Professional Services</h3><p>Get expert help with your projects</p><a href="https://services.example.com" class="btn btn-primary">Learn More</a></div>',
        'status' => 'active'
    ],
    [
        'title' => 'Category Featured Ad',
        'position' => 'category_header',
        'page_type' => 'category',
        'code' => '<div style="background:linear-gradient(45deg,#ff6b6b,#4ecdc4);color:white;padding:20px;text-align:center;"><h2>Special Category Offer</h2><p>Exclusive deals for this category</p></div>',
        'status' => 'active'
    ],
    [
        'title' => 'Home Hero Banner',
        'position' => 'home_hero',
        'page_type' => 'home',
        'code' => '<div style="background:url(uploads/ads/hero-bg.jpg) center/cover;height:300px;display:flex;align-items:center;justify-content:center;"><div style="background:rgba(0,0,0,0.7);color:white;padding:30px;border-radius:10px;"><h1>Big Sale Event</h1><p>Limited time offers</p></div></div>',
        'status' => 'active'
    ],
    [
        'title' => 'News Inline Ad',
        'position' => 'news_inline',
        'page_type' => 'news',
        'code' => '<div style="border:1px solid #ddd;padding:10px;margin:10px 0;background:#f9f9f9;"><p><strong>Sponsored Content:</strong> Check out these amazing products!</p><a href="https://shop.example.com">Shop Now</a></div>',
        'status' => 'active'
    ]
];

foreach ($sample_ads as $ad) {
    $title = mysqli_real_escape_string($conn, $ad['title']);
    $position = $ad['position'];
    $page_type = $ad['page_type'];
    $code = mysqli_real_escape_string($conn, $ad['code']);
    $status = $ad['status'];
    
    $insert_query = "INSERT INTO advertisements (title, position, page_type, code, status, start_date, end_date) 
                     VALUES ('$title', '$position', '$page_type', '$code', '$status', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY))";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "<p class='text-success'>✓ Added: {$ad['title']} ({$ad['position']})</p>";
    } else {
        echo "<p class='text-danger'>✗ Error adding {$ad['title']}: " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3 class='mt-4'>Schema Update Complete!</h3>";
echo "<p>The advertisement system now supports:</p>";
echo "<ul>";
echo "<li><strong>Live Section Ads:</strong> live_header, live_sidebar, live_footer, live_popup</li>";
echo "<li><strong>Performance Section Ads:</strong> performance_header, performance_sidebar, performance_footer, performance_inline</li>";
echo "<li><strong>Contact Page Ads:</strong> contact_header, contact_sidebar, contact_footer</li>";
echo "<li><strong>Category-Specific Ads:</strong> category_header, category_sidebar, category_footer, category_inline</li>";
echo "<li><strong>Home Page Ads:</strong> home_hero, home_featured, home_sidebar, home_footer</li>";
echo "<li><strong>News Page Ads:</strong> news_inline</li>";
echo "<li><strong>Other Pages:</strong> search_sidebar, profile_sidebar</li>";
echo "</ul>";
echo "<p><a href='admin/manage-ads.php' class='btn btn-primary'>Manage Ads</a> | <a href='index.php' class='btn btn-secondary'>View Website</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
.text-info { color: #17a2b8; }
.text-warning { color: #ffc107; }
.btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; }
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
table { width: 100%; }
th, td { padding: 8px; text-align: left; }
</style>
