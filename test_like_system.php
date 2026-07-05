<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Test the like system
echo "<h2>Like System Test</h2>";

// Test 1: Check if post_likes table exists and has data
echo "<h3>1. Table Status</h3>";
$result = mysqli_query($conn, 'SHOW TABLES LIKE "post_likes"');
if(mysqli_num_rows($result) > 0) {
    echo "✅ post_likes table exists<br>";
    
    $count = mysqli_query($conn, 'SELECT COUNT(*) as count FROM post_likes');
    $row = mysqli_fetch_assoc($count);
    echo "📊 Total records: " . $row['count'] . "<br>";
    
    // Show sample data
    $sample = mysqli_query($conn, 'SELECT * FROM post_likes LIMIT 5');
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>News ID</th><th>User ID</th><th>IP Address</th><th>User Agent</th></tr>";
    while($row = mysqli_fetch_assoc($sample)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['news_id'] . "</td>";
        echo "<td>" . ($row['user_id'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['ip_address'] . "</td>";
        echo "<td>" . substr($row['user_agent'] ?? '', 0, 30) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ post_likes table does not exist<br>";
}

// Test 2: Test the query used in index.php
echo "<h3>2. Index.php Query Test</h3>";
$test_query = "SELECT n.*, (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count FROM news n LIMIT 3";
$test_result = mysqli_query($conn, $test_query);

if($test_result) {
    echo "✅ Query executed successfully<br>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>News ID</th><th>Title</th><th>Likes Count</th></tr>";
    while($row = mysqli_fetch_assoc($test_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . substr($row['title'] ?? 'No title', 0, 40) . "...</td>";
        echo "<td>" . $row['likes_count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Query failed: " . mysqli_error($conn) . "<br>";
}

// Test 3: Create sample data if table is empty
echo "<h3>3. Sample Data Creation</h3>";
$count = mysqli_query($conn, 'SELECT COUNT(*) as count FROM post_likes');
$row = mysqli_fetch_assoc($count);
if($row['count'] == 0) {
    echo "Creating sample data...<br>";
    
    // Get some news IDs
    $news_result = mysqli_query($conn, 'SELECT id FROM news LIMIT 5');
    $news_ids = [];
    while($row = mysqli_fetch_assoc($news_result)) {
        $news_ids[] = $row['id'];
    }
    
    // Insert sample likes
    foreach($news_ids as $news_id) {
        $guest_identifier = 'guest_test_' . uniqid();
        $insert_query = "INSERT INTO post_likes (news_id, user_id, ip_address, user_agent) VALUES (?, NULL, '127.0.0.1', ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'is', $news_id, $guest_identifier);
        mysqli_stmt_execute($stmt);
    }
    
    echo "✅ Sample data created<br>";
} else {
    echo "ℹ️ Table already has data<br>";
}

// Test 4: Test API endpoint
echo "<h3>4. API Endpoint Test</h3>";
echo "<p>To test the API endpoint, you can use JavaScript in browser console:</p>";
echo "<pre>
fetch('api/toggle-like.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ news_id: 1 })
})
.then(response => response.json())
.then(data => console.log(data));
</pre>";

mysqli_close($conn);
?>
