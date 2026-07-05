<?php
require_once 'config/database.php';

echo "<h2>Creating Traffic Analytics Table</h2>";

// Create traffic analytics table
$create_table = "CREATE TABLE IF NOT EXISTS traffic_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    hour TINYINT NOT NULL,
    page_views INT DEFAULT 0,
    unique_visitors INT DEFAULT 0,
    page_url VARCHAR(500),
    referrer VARCHAR(500),
    user_agent TEXT,
    country VARCHAR(100),
    city VARCHAR(100),
    device_type ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop',
    browser VARCHAR(100),
    session_duration INT DEFAULT 0,
    bounce_rate DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_datetime (date, hour, page_url)
)";

if (mysqli_query($conn, $create_table)) {
    echo "<p class='text-success'>✅ Traffic analytics table created successfully</p>";
} else {
    echo "<p class='text-danger'>✗ Error creating table: " . mysqli_error($conn) . "</p>";
}

// Insert sample data for today
$today = date('Y-m-d');
$sample_data = [];

for ($hour = 0; $hour < 24; $hour++) {
    $sample_data[] = [
        'date' => $today,
        'hour' => $hour,
        'page_views' => rand(50, 500),
        'unique_visitors' => rand(20, 200),
        'page_url' => '/',
        'referrer' => 'Direct',
        'country' => 'Pakistan',
        'city' => 'Karachi',
        'device_type' => ['desktop', 'mobile', 'tablet'][array_rand(['desktop', 'mobile', 'tablet'])],
        'browser' => 'Chrome',
        'session_duration' => rand(60, 600),
        'bounce_rate' => rand(20, 70)
    ];
}

// Insert sample data
foreach ($sample_data as $data) {
    $date = $data['date'];
    $hour = $data['hour'];
    $page_views = $data['page_views'];
    $unique_visitors = $data['unique_visitors'];
    $page_url = $data['page_url'];
    $referrer = $data['referrer'];
    $country = $data['country'];
    $city = $data['city'];
    $device_type = $data['device_type'];
    $browser = $data['browser'];
    $session_duration = $data['session_duration'];
    $bounce_rate = $data['bounce_rate'];
    
    $insert_query = "INSERT INTO traffic_analytics (date, hour, page_views, unique_visitors, page_url, referrer, country, city, device_type, browser, session_duration, bounce_rate) 
                     VALUES ('$date', $hour, $page_views, $unique_visitors, '$page_url', '$referrer', '$country', '$city', '$device_type', '$browser', $session_duration, $bounce_rate)
                     ON DUPLICATE KEY UPDATE page_views = VALUES(page_views), unique_visitors = VALUES(unique_visitors)";
    
    mysqli_query($conn, $insert_query);
}

echo "<p class='text-success'>✅ Sample traffic data inserted for today</p>";

echo "<h3>Table Status</h3>";

// Verify table exists and show structure
$check_query = "SHOW TABLES LIKE 'traffic_analytics'";
$table_exists = mysqli_num_rows(mysqli_query($conn, $check_query)) > 0;

if ($table_exists) {
    echo "<p class='text-success'>✅ Table 'traffic_analytics' exists and is ready</p>";
    
    // Show table structure
    $structure_query = "DESCRIBE traffic_analytics";
    $structure_result = mysqli_query($conn, $structure_query);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    
    while ($row = mysqli_fetch_assoc($structure_result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='text-danger'>✗ Table 'traffic_analytics' does not exist</p>";
}

echo "<div class='mt-4'>";
echo "<a href='seo_tools.php' class='btn btn-primary'>Go to SEO Tools</a>";
echo "<a href='index.php' class='btn btn-secondary'>Go to Homepage</a>";
echo "</div>";

?>

<style>
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
body { font-family: Arial, sans-serif; padding: 20px; }
table { border: 1px solid #ddd; }
th, td { padding: 8px; text-align: left; }
th { background: #f5f5f5; }
.btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; }
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
</style>
