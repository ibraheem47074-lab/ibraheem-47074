<?php
require_once 'config/database.php';

// Simple time_ago function if not exists
if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } else {
            return floor($diff / 86400) . ' days ago';
        }
    }
}

// Test notification creation and retrieval
echo "<h2>Testing Notifications System</h2>";

// Check if tables exist
$tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
if (mysqli_num_rows($tables_check) == 0) {
    echo "<p style='color: red;'>❌ Notifications table does not exist. Please run installation first.</p>";
    exit;
}

// Create a test notification
$test_title = "Test Notification - " . date('Y-m-d H:i:s');
$test_message = "This is a test notification to verify the system is working correctly.";
$test_type = "info";

// Insert for user ID 1 (admin)
$insert_query = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param($stmt, 'isss', 1, $test_title, $test_message, $test_type);

if (mysqli_stmt_execute($stmt)) {
    $notification_id = mysqli_insert_id($conn);
    echo "<p style='color: green;'>✅ Test notification created successfully! ID: $notification_id</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating test notification: " . mysqli_error($conn) . "</p>";
}

// Insert an "All Users" notification
$all_users_title = "All Users Test - " . date('Y-m-d H:i:s');
$all_users_message = "This notification should appear for all users (user_id is NULL).";

$insert_all_query = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
$stmt_all = mysqli_prepare($conn, $insert_all_query);
$null_user_id = null;
mysqli_stmt_bind_param($stmt_all, 'isss', $null_user_id, $all_users_title, $all_users_message, $test_type);

if (mysqli_stmt_execute($stmt_all)) {
    $all_notification_id = mysqli_insert_id($conn);
    echo "<p style='color: green;'>✅ All Users notification created successfully! ID: $all_notification_id</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating all users notification: " . mysqli_error($conn) . "</p>";
}

// Test retrieval for user ID 1
echo "<h3>Testing Notification Retrieval</h3>";
$retrieve_query = "SELECT * FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0 ORDER BY created_at DESC LIMIT 10";
$retrieve_stmt = mysqli_prepare($conn, $retrieve_query);
mysqli_stmt_bind_param($retrieve_stmt, 'i', 1);
mysqli_stmt_execute($retrieve_stmt);
$result = mysqli_stmt_get_result($retrieve_stmt);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: green;'>✅ Successfully retrieved " . mysqli_num_rows($result) . " notifications:</p>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Title</th><th>Message</th><th>Type</th><th>Created</th><th>Time Ago</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . ($row['user_id'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['message'], 0, 50)) . "...</td>";
        echo "<td>" . $row['type'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . time_ago($row['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No notifications found for user ID 1</p>";
}

// Test API endpoint
echo "<h3>Testing API Endpoint</h3>";
$api_url = "http://$_SERVER[HTTP_HOST]/PK-LIVE%20NEWS/api/notifications.php";

// Use cURL to test the API
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . "=" . session_id());
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>API Response (HTTP $http_code):</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        if (isset($data['notifications'])) {
            echo "<p style='color: green;'>✅ API returned " . count($data['notifications']) . " notifications</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ API response format unexpected</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ API returned HTTP $http_code</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ cURL not available, cannot test API endpoint</p>";
}

echo "<p><a href='admin/manage-notifications.php'>Go to Manage Notifications</a> | <a href='index.php'>Go to Homepage</a></p>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Notifications - PK Live News</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; }
        th { background: #f0f0f0; font-weight: bold; }
        td, th { padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <!-- The PHP code above will output the test results -->
</body>
</html>
