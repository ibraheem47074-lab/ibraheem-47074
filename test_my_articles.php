<?php
require_once 'config/database.php';

echo "<h2>Testing my-articles.php Fix</h2>";

// Test the query structure that was causing issues
$user_id = 1; // Test user ID
$filter_status = 'published'; // Test filter
$search = ''; // Empty search

// Build WHERE clause as per the fixed code
$where_conditions = ["n.author_id = $user_id"];
$params = [];
$types = '';

if (!empty($filter_status)) {
    $where_conditions[] = "n.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(n.title LIKE ? OR n.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Test the query
$test_query = "SELECT n.*, c.name as category_name 
              FROM news n 
              LEFT JOIN categories c ON n.category_id = c.id 
              $where_clause 
              ORDER BY n.created_at DESC 
              LIMIT 10";

echo "<h3>Generated Query:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars($test_query);
echo "</pre>";

echo "<h3>Parameters:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
print_r($params);
echo "</pre>";

// Test execution
$stmt = mysqli_prepare($conn, $test_query);
if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $count = mysqli_num_rows($result);
        echo "<p class='text-success'>✓ Query executed successfully! Found $count articles.</p>";
        
        if ($count > 0) {
            echo "<h4>Sample Results:</h4>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Category</th></tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "...</td>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td>" . ($row['category_name'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p class='text-danger'>✗ Query execution failed: " . mysqli_stmt_error($stmt) . "</p>";
    }
} else {
    echo "<p class='text-danger'>✗ Query preparation failed: " . mysqli_error($conn) . "</p>";
}

echo "<h3>Test Complete!</h3>";
echo "<p><a href='admin/my-articles.php'>Test my-articles.php</a></p>";
?>

<style>
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
body { font-family: Arial, sans-serif; padding: 20px; }
</style>
