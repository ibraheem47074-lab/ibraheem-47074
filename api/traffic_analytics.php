<?php
require_once "config/database.php";

// Get today's date
$today = date("Y-m-d");

// Get traffic data (simulated for demo)
$page_views = rand(1000, 3000);
$unique_visitors = rand(500, 1500);
$bounce_rate = rand(30, 60);
$avg_session_time = rand(120, 300);

// Check if today's data exists
$check_query = "SELECT id FROM seo_analytics WHERE date = \"$today\"";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    // Insert today's data
    $insert_query = "INSERT INTO seo_analytics (date, page_views, unique_visitors, bounce_rate, avg_session_time) 
                     VALUES (\"$today\", $page_views, $unique_visitors, $bounce_rate, $avg_session_time)";
    mysqli_query($conn, $insert_query);
} else {
    // Update today's data
    $update_query = "UPDATE seo_analytics 
                     SET page_views = $page_views, unique_visitors = $unique_visitors, 
                         bounce_rate = $bounce_rate, avg_session_time = $avg_session_time 
                     WHERE date = \"$today\"";
    mysqli_query($conn, $update_query);
}

// Get weekly data for charts
$weekly_query = "SELECT * FROM seo_analytics 
                 WHERE date >= DATE_SUB(\"$today\", INTERVAL 7 DAY) 
                 ORDER BY date ASC";
$weekly_result = mysqli_query($conn, $weekly_query);

$weekly_data = [];
while ($row = mysqli_fetch_assoc($weekly_result)) {
    $weekly_data[] = $row;
}

header("Content-Type: application/json");
echo json_encode([
    "today" => [
        "page_views" => $page_views,
        "unique_visitors" => $unique_visitors,
        "bounce_rate" => $bounce_rate,
        "avg_session_time" => $avg_session_time
    ],
    "weekly" => $weekly_data
]);
?>