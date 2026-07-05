<?php
// Direct test of the toggle-like API
echo "<h2>Direct API Test</h2>";

// Test 1: Check if required files exist
echo "<h3>1. File Check</h3>";
$required_files = [
    'config/database.php',
    'config/helpers.php',
    'api/toggle-like.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

// Test 2: Test API with cURL
echo "<h3>2. API cURL Test</h3>";
$api_url = 'http://localhost/PK-LIVE%20NEWS/api/toggle-like.php';

// Prepare test data
$post_data = json_encode(['news_id' => 1]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($post_data)
]);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code<br>";
if ($curl_error) {
    echo "cURL Error: $curl_error<br>";
} else {
    echo "API Response: " . htmlspecialchars($response) . "<br>";
    
    $response_data = json_decode($response, true);
    if ($response_data) {
        echo "✅ Valid JSON response<br>";
        echo "Success: " . ($response_data['success'] ? 'YES' : 'NO') . "<br>";
        if (isset($response_data['likes_count'])) {
            echo "Likes Count: " . $response_data['likes_count'] . "<br>";
        }
        if (isset($response_data['message'])) {
            echo "Message: " . $response_data['message'] . "<br>";
        }
    } else {
        echo "❌ Invalid JSON response<br>";
    }
}

// Test 3: Check database directly
echo "<h3>3. Database Check</h3>";
try {
    require_once 'config/database.php';
    
    // Check post_likes table
    $table_check = mysqli_query($conn, 'SHOW TABLES LIKE "post_likes"');
    if (mysqli_num_rows($table_check) > 0) {
        echo "✅ post_likes table exists<br>";
        
        // Count total likes
        $count_query = "SELECT COUNT(*) as count FROM post_likes";
        $count_result = mysqli_query($conn, $count_query);
        $count = mysqli_fetch_assoc($count_result)['count'];
        echo "Total likes: $count<br>";
        
        // Show likes per news
        $likes_per_news = mysqli_query($conn, 'SELECT news_id, COUNT(*) as count FROM post_likes GROUP BY news_id LIMIT 5');
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>News ID</th><th>Likes</th></tr>";
        while ($row = mysqli_fetch_assoc($likes_per_news)) {
            echo "<tr><td>" . $row['news_id'] . "</td><td>" . $row['count'] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "❌ post_likes table missing<br>";
    }
    
    // Test the index query
    $index_query = "SELECT n.*, COALESCE((SELECT COUNT(*) FROM post_likes WHERE news_id = n.id), 0) as likes_count FROM news n LIMIT 3";
    $index_result = mysqli_query($conn, $index_query);
    
    echo "<h4>Index Query Test:</h4>";
    if ($index_result) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>News ID</th><th>Title</th><th>Likes Count</th></tr>";
        while ($row = mysqli_fetch_assoc($index_result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . substr($row['title'] ?? 'No title', 0, 40) . "...</td>";
            echo "<td>" . $row['likes_count'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Index query failed: " . mysqli_error($conn) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<h3>4. JavaScript Debug Steps</h3>";
echo "<p>To debug in browser:</p>";
echo "<ol>";
echo "<li>Open index.php</li>";
echo "<li>Open F12 console</li>";
echo "<li>Click a like button</li>";
echo "<li>Check for console.log messages</li>";
echo "<li>Look at Network tab for API call</li>";
echo "<li>Verify API response in Network tab</li>";
echo "</ol>";
?>
