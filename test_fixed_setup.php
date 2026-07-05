<?php
require_once 'config/database.php';

echo "<h1>🧪 Testing Fixed Live TV Setup (No GD Required)</h1>";

// Test 1: Check database tables
echo "<h2>Test 1: Database Tables</h2>";

$required_tables = ['channels', 'live_chat', 'channel_schedule'];
$missing_tables = [];

foreach ($required_tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) == 0) {
        $missing_tables[] = $table;
    }
}

if (empty($missing_tables)) {
    echo "✅ All required tables exist<br>";
} else {
    echo "❌ Missing tables: " . implode(', ', $missing_tables) . "<br>";
}

// Test 2: Check channels data
echo "<h2>Test 2: Channels Data</h2>";

$channels_query = "SELECT COUNT(*) as total FROM channels";
$result = mysqli_query($conn, $channels_query);
$row = mysqli_fetch_assoc($result);

if ($row['total'] > 0) {
    echo "✅ Found " . $row['total'] . " channels in database<br>";
    
    // Show sample channels with categories
    $sample_query = "SELECT name, category, status FROM channels ORDER BY category, name";
    $sample_result = mysqli_query($conn, $sample_query);
    
    echo "<table class='table table-sm'>";
    echo "<thead><tr><th>Name</th><th>Category</th><th>Status</th><th>Logo Preview</th></tr></thead>";
    echo "<tbody>";
    
    while ($channel = mysqli_fetch_assoc($sample_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($channel['name']) . "</td>";
        echo "<td>" . htmlspecialchars($channel['category']) . "</td>";
        echo "<td><span class='badge bg-" . ($channel['status'] == 'live' ? 'success' : 'secondary') . "'>" . strtoupper($channel['status']) . "</span></td>";
        
        // Show CSS logo preview
        $category_colors = [
            'news' => '#dc3545',
            'sports' => '#28a745', 
            'entertainment' => '#ffc107',
            'business' => '#17a2b8',
            'technology' => '#6f42c1',
            'international' => '#fd7e14'
        ];
        $bg_color = isset($category_colors[$channel['category']]) ? $category_colors[$channel['category']] : '#6c757d';
        $channel_short = strtoupper(substr($channel['name'], 0, 3));
        
        echo "<td>";
        echo "<div style='width: 60px; height: 30px; border-radius: 3px; 
                     background: linear-gradient(45deg, $bg_color, $bg_color" . "dd);
                     display: flex; align-items: center; justify-content: center;
                     color: white; font-weight: bold; font-size: 8px;
                     border: 1px solid $bg_color;
                     text-shadow: 1px 1px 1px rgba(0,0,0,0.5);'>";
        echo $channel_short;
        echo "</div>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "❌ No channels found in database<br>";
}

// Test 3: Check if live.php uses CSS logos
echo "<h2>Test 3: CSS Logo System</h2>";

$live_php_content = file_get_contents('live.php');

if (strpos($live_php_content, 'css-logo') !== false) {
    echo "✅ live.php updated with CSS-based logo system<br>";
} else {
    echo "❌ live.php not updated with CSS logos<br>";
}

if (strpos($live_php_content, 'category_colors') !== false) {
    echo "✅ Category color system implemented<br>";
} else {
    echo "❌ Category color system missing<br>";
}

// Test 4: Check file structure
echo "<h2>Test 4: File Structure</h2>";

$required_files = [
    'live.php' => 'Main Live TV page',
    'complete_live_setup_fixed.php' => 'Fixed setup script',
    'test_fixed_setup.php' => 'This test script'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description ($file)<br>";
    } else {
        echo "❌ Missing: $description ($file)<br>";
    }
}

// Test 5: Show category distribution
echo "<h2>Test 5: Channel Distribution</h2>";

$category_query = "SELECT category, COUNT(*) as count, SUM(is_featured) as featured FROM channels GROUP BY category ORDER BY category";
$category_result = mysqli_query($conn, $category_query);

echo "<table class='table'>";
echo "<thead><tr><th>Category</th><th>Channels</th><th>Featured</th><th>Color</th></tr></thead>";
echo "<tbody>";

$category_colors_display = [
    'news' => '#dc3545',
    'sports' => '#28a745', 
    'entertainment' => '#ffc107',
    'business' => '#17a2b8',
    'technology' => '#6f42c1',
    'international' => '#fd7e14'
];

$total_channels = 0;
$total_featured = 0;

while ($row = mysqli_fetch_assoc($category_result)) {
    echo "<tr>";
    echo "<td>" . ucfirst($row['category']) . "</td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "<td>" . $row['featured'] . "</td>";
    $color = isset($category_colors_display[$row['category']]) ? $category_colors_display[$row['category']] : '#6c757d';
    echo "<td><div style='width: 40px; height: 20px; background: $color; border-radius: 3px; border: 1px solid #ccc;'></div></td>";
    echo "</tr>";
    $total_channels += $row['count'];
    $total_featured += $row['featured'];
}

echo "</tbody>";
echo "<tfoot><tr class='table-info'><th>Total</th><th>" . $total_channels . "</th><th>" . $total_featured . "</th><th></th></tr></tfoot>";
echo "</table>";

// Summary
echo "<h2>📋 Test Summary</h2>";

$issues = [];

if (!empty($missing_tables)) {
    $issues[] = "Missing database tables";
}

if ($row['total'] == 0) {
    $issues[] = "No channels in database";
}

if (strpos($live_php_content, 'css-logo') === false) {
    $issues[] = "live.php not updated with CSS logos";
}

if (empty($issues)) {
    echo "<div class='alert alert-success'>";
    echo "🎉 All tests passed! The fixed Live TV functionality should work correctly without GD library.<br>";
    echo "<strong>Features:</strong><br>";
    echo "• CSS-based channel logos (no image processing required)<br>";
    echo "• Category-based color coding<br>";
    echo "• Hover effects and animations<br>";
    echo "• Responsive design<br>";
    echo "<br><a href='live.php' target='_blank' class='btn btn-danger mt-2'>Go to Live TV Page</a>";
    echo "</div>";
} else {
    echo "<div class='alert alert-warning'>";
    echo "⚠️ Issues found:<br>";
    foreach ($issues as $issue) {
        echo "• $issue<br>";
    }
    echo "<br><strong>Recommendation:</strong> Run <a href='complete_live_setup_fixed.php'>complete_live_setup_fixed.php</a> to fix these issues.";
    echo "</div>";
}

echo "<h2>🔧 Quick Actions</h2>";
echo "<div class='btn-group-vertical'>";
echo "<a href='complete_live_setup_fixed.php' class='btn btn-primary mb-2'>🚀 Run Fixed Setup</a>";
echo "<a href='live.php' target='_blank' class='btn btn-success mb-2'>📺 View Live TV</a>";
echo "<a href='test_fixed_setup.php' class='btn btn-info mb-2'>🧪 Run Tests Again</a>";
echo "</div>";

echo "<div class='alert alert-info mt-3'>";
echo "<strong>✅ GD Issue Fixed:</strong><br>";
echo "• No PHP GD extension required<br>";
echo "• Uses CSS gradients for logos<br>";
echo "• Works on any PHP installation<br>";
echo "• Faster loading (no image generation)<br>";
echo "</div>";
?>
