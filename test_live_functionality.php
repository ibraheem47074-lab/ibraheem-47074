<?php
require_once 'config/database.php';

echo "<h1>🧪 Testing Live TV Functionality</h1>";

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
    
    // Show sample channels
    $sample_query = "SELECT name, category, status, stream_url FROM channels LIMIT 5";
    $sample_result = mysqli_query($conn, $sample_query);
    
    echo "<table class='table table-sm'>";
    echo "<thead><tr><th>Name</th><th>Category</th><th>Status</th><th>Stream URL</th></tr></thead>";
    echo "<tbody>";
    
    while ($channel = mysqli_fetch_assoc($sample_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($channel['name']) . "</td>";
        echo "<td>" . htmlspecialchars($channel['category']) . "</td>";
        echo "<td><span class='badge bg-" . ($channel['status'] == 'live' ? 'success' : 'secondary') . "'>" . strtoupper($channel['status']) . "</span></td>";
        echo "<td><small>" . htmlspecialchars(substr($channel['stream_url'], 0, 50)) . "...</small></td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "❌ No channels found in database<br>";
}

// Test 3: Check logo files
echo "<h2>Test 3: Channel Logos</h2>";

$channels_query = "SELECT name FROM channels ORDER BY name";
$result = mysqli_query($conn, $channels_query);

$logo_count = 0;
$total_channels = mysqli_num_rows($result);

while ($channel = mysqli_fetch_assoc($result)) {
    $logo_path = 'uploads/channels/' . strtolower(str_replace(' ', '-', $channel['name'])) . '-logo.png';
    if (file_exists($logo_path)) {
        $logo_count++;
    }
}

echo "✅ Found logos for $logo_count out of $total_channels channels<br>";

if ($logo_count < $total_channels) {
    echo "⚠️ Some channels are missing logos. Run the complete setup script.<br>";
}

// Test 4: Check streaming URLs
echo "<h2>Test 4: Streaming URLs</h2>";

$channels_query = "SELECT name, stream_url, stream_type FROM channels WHERE status = 'live' LIMIT 3";
$result = mysqli_query($conn, $channels_query);

echo "<table class='table'>";
echo "<thead><tr><th>Channel</th><th>Stream Type</th><th>URL Test</th></tr></thead>";
echo "<tbody>";

while ($channel = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($channel['name']) . "</td>";
    echo "<td>" . htmlspecialchars($channel['stream_type']) . "</td>";
    
    // Basic URL validation
    $url = $channel['stream_url'];
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        echo "<td><span class='text-success'>✅ Valid URL</span></td>";
    } else {
        echo "<td><span class='text-danger'>❌ Invalid URL</span></td>";
    }
    echo "</tr>";
}
echo "</tbody></table>";

// Test 5: Check file structure
echo "<h2>Test 5: File Structure</h2>";

$required_files = [
    'live.php' => 'Main Live TV page',
    'includes/header.php' => 'Header file',
    'includes/footer.php' => 'Footer file',
    'api/get_channel.php' => 'Channel API',
    'api/get_chat.php' => 'Chat API',
    'assets/css/style.css' => 'Stylesheet'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description ($file)<br>";
    } else {
        echo "❌ Missing: $description ($file)<br>";
    }
}

// Test 6: Check directories
echo "<h2>Test 6: Directory Structure</h2>";

$required_dirs = [
    'uploads/channels/' => 'Channel logos directory',
    'assets/css/' => 'CSS directory',
    'api/' => 'API directory'
];

foreach ($required_dirs as $dir => $description) {
    if (is_dir($dir)) {
        echo "✅ $description ($dir)<br>";
    } else {
        echo "❌ Missing directory: $description ($dir)<br>";
    }
}

// Summary
echo "<h2>📋 Test Summary</h2>";

$issues = [];

if (!empty($missing_tables)) {
    $issues[] = "Missing database tables";
}

if ($row['total'] == 0) {
    $issues[] = "No channels in database";
}

if ($logo_count < $total_channels) {
    $issues[] = "Missing channel logos";
}

if (empty($issues)) {
    echo "<div class='alert alert-success'>";
    echo "🎉 All tests passed! The Live TV functionality should work correctly.<br>";
    echo "<a href='live.php' target='_blank' class='btn btn-danger mt-2'>Go to Live TV Page</a>";
    echo "</div>";
} else {
    echo "<div class='alert alert-warning'>";
    echo "⚠️ Issues found:<br>";
    foreach ($issues as $issue) {
        echo "• $issue<br>";
    }
    echo "<br><strong>Recommendation:</strong> Run <a href='complete_live_setup.php'>complete_live_setup.php</a> to fix these issues.";
    echo "</div>";
}

echo "<h2>🔧 Quick Actions</h2>";
echo "<div class='btn-group-vertical'>";
echo "<a href='complete_live_setup.php' class='btn btn-primary mb-2'>🚀 Run Complete Setup</a>";
echo "<a href='generate_channel_logos.php' class='btn btn-info mb-2'>🎨 Generate Logos Only</a>";
echo "<a href='update_channel_streams.php' class='btn btn-warning mb-2'>📡 Update Streaming Links</a>";
echo "<a href='live.php' target='_blank' class='btn btn-success mb-2'>📺 View Live TV</a>";
echo "</div>";
?>
