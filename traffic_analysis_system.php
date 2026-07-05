<?php
require_once 'config/database.php';

echo "<h2>Advanced Traffic Pattern Analysis</h2>";

// Create detailed traffic analytics table
$create_traffic_table = "CREATE TABLE IF NOT EXISTS traffic_analytics (
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

if (mysqli_query($conn, $create_traffic_table)) {
    echo "<p class='text-success'>✅ Advanced traffic analytics table created</p>";
}

// Sample traffic data generation (simulated for demo)
$sample_traffic_data = [];
$current_date = date('Y-m-d');
$cities = ['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan', 'Peshawar', 'Quetta'];
$pages = ['/', '/index.php', '/news.php', '/category.php', '/live.php', '/contact.php'];
$referrers = ['Direct', 'Google', 'Facebook', 'Twitter', 'LinkedIn', 'Instagram'];

for ($day = 0; $day < 7; $day++) {
    $date = date('Y-m-d', strtotime("-$day days"));
    
    for ($hour = 0; $hour < 24; $hour++) {
        foreach ($pages as $page) {
            $page_views = rand(50, 500);
            $unique_visitors = rand(20, 200);
            $session_duration = rand(60, 600);
            $bounce_rate = rand(20, 70);
            
            $sample_traffic_data[] = [
                'date' => $date,
                'hour' => $hour,
                'page_views' => $page_views,
                'unique_visitors' => $unique_visitors,
                'page_url' => $page,
                'referrer' => $referrers[array_rand($referrers)],
                'country' => 'Pakistan',
                'city' => $cities[array_rand($cities)],
                'device_type' => ['desktop', 'mobile', 'tablet'][array_rand(['desktop', 'mobile', 'tablet'])],
                'browser' => ['Chrome', 'Firefox', 'Safari', 'Edge'][array_rand(['Chrome', 'Firefox', 'Safari', 'Edge'])],
                'session_duration' => $session_duration,
                'bounce_rate' => $bounce_rate
            ];
        }
    }
}

// Insert sample data
foreach ($sample_traffic_data as $data) {
    $date = $data['date'];
    $hour = $data['hour'];
    $page_views = $data['page_views'];
    $unique_visitors = $data['unique_visitors'];
    $page_url = mysqli_real_escape_string($conn, $data['page_url']);
    $referrer = mysqli_real_escape_string($conn, $data['referrer']);
    $country = mysqli_real_escape_string($conn, $data['country']);
    $city = mysqli_real_escape_string($conn, $data['city']);
    $device_type = $data['device_type'];
    $browser = mysqli_real_escape_string($conn, $data['browser']);
    $session_duration = $data['session_duration'];
    $bounce_rate = $data['bounce_rate'];
    
    $insert_query = "INSERT INTO traffic_analytics (date, hour, page_views, unique_visitors, page_url, referrer, country, city, device_type, browser, session_duration, bounce_rate) 
                     VALUES ('$date', $hour, $page_views, $unique_visitors, '$page_url', '$referrer', '$country', '$city', '$device_type', '$browser', $session_duration, $bounce_rate)
                     ON DUPLICATE KEY UPDATE page_views = VALUES(page_views), unique_visitors = VALUES(unique_visitors)";
    
    mysqli_query($conn, $insert_query);
}

echo "<p class='text-success'>✅ Generated 7 days of sample traffic data</p>";

// Traffic pattern analysis dashboard
echo "<div class='analytics-dashboard'>";

// Summary statistics
echo "<div class='summary-cards mb-4'>";
echo "<div class='row'>";

$total_views_query = "SELECT SUM(page_views) as total_views, SUM(unique_visitors) as total_visitors FROM traffic_analytics WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$total_stats = mysqli_fetch_assoc(mysqli_query($conn, $total_views_query));

echo "<div class='col-md-3'>";
echo "<div class='stat-card bg-primary'>";
echo "<h4>" . number_format($total_stats['total_views']) . "</h4>";
echo "<p>Total Page Views (7 days)</p>";
echo "</div>";
echo "</div>";

echo "<div class='col-md-3'>";
echo "<div class='stat-card bg-success'>";
echo "<h4>" . number_format($total_stats['total_visitors']) . "</h4>";
echo "<p>Unique Visitors (7 days)</p>";
echo "</div>";
echo "</div>";

$avg_session_query = "SELECT AVG(session_duration) as avg_duration FROM traffic_analytics WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$avg_session = mysqli_fetch_assoc(mysqli_query($conn, $avg_session_query));

echo "<div class='col-md-3'>";
echo "<div class='stat-card bg-info'>";
echo "<h4>" . round($avg_session['avg_duration']) . "s</h4>";
echo "<p>Avg Session Duration</p>";
echo "</div>";
echo "</div>";

$bounce_rate_query = "SELECT AVG(bounce_rate) as avg_bounce FROM traffic_analytics WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$bounce_rate = mysqli_fetch_assoc(mysqli_query($conn, $bounce_rate_query));

echo "<div class='col-md-3'>";
echo "<div class='stat-card bg-warning'>";
echo "<h4>" . round($bounce_rate['avg_bounce']) . "%</h4>";
echo "<p>Avg Bounce Rate</p>";
echo "</div>";
echo "</div>";

echo "</div>";
echo "</div>";

// Hourly traffic pattern
echo "<div class='chart-section mb-4'>";
echo "<h3>📈 Hourly Traffic Pattern</h3>";
echo "<canvas id='hourlyChart' width='400' height='150'></canvas>";
echo "</div>";

// Top pages
echo "<div class='top-pages mb-4'>";
echo "<h3>🔝 Top Performing Pages</h3>";

$top_pages_query = "SELECT page_url, SUM(page_views) as views, SUM(unique_visitors) as visitors 
                   FROM traffic_analytics 
                   WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                   GROUP BY page_url 
                   ORDER BY views DESC 
                   LIMIT 10";
$top_pages_result = mysqli_query($conn, $top_pages_query);

echo "<div class='table-responsive'>";
echo "<table class='table table-striped'>";
echo "<thead><tr><th>Page</th><th>Views</th><th>Visitors</th><th>Engagement</th></tr></thead>";
echo "<tbody>";

while ($page = mysqli_fetch_assoc($top_pages_result)) {
    $engagement = $page['visitors'] > 0 ? round(($page['views'] / $page['visitors']), 2) : 0;
    echo "<tr>";
    echo "<td>" . htmlspecialchars($page['page_url']) . "</td>";
    echo "<td>" . number_format($page['views']) . "</td>";
    echo "<td>" . number_format($page['visitors']) . "</td>";
    echo "<td>" . $engagement . "</td>";
    echo "</tr>";
}

echo "</tbody></table>";
echo "</div>";
echo "</div>";

// Device breakdown
echo "<div class='device-breakdown mb-4'>";
echo "<h3>📱 Device Breakdown</h3>";

$device_query = "SELECT device_type, SUM(page_views) as views, SUM(unique_visitors) as visitors 
                FROM traffic_analytics 
                WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                GROUP BY device_type";
$device_result = mysqli_query($conn, $device_query);

echo "<div class='row'>";
while ($device = mysqli_fetch_assoc($device_result)) {
    $percentage = ($device['views'] / $total_stats['total_views']) * 100;
    echo "<div class='col-md-4'>";
    echo "<div class='device-card'>";
    echo "<h4>" . ucfirst($device['device_type']) . "</h4>";
    echo "<p>" . number_format($device['views']) . " views (" . round($percentage, 1) . "%)</p>";
    echo "</div>";
    echo "</div>";
}
echo "</div>";
echo "</div>";

// Geographic distribution
echo "<div class='geo-distribution mb-4'>";
echo "<h3>🌍 Geographic Distribution</h3>";

$geo_query = "SELECT city, SUM(unique_visitors) as visitors 
              FROM traffic_analytics 
              WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
              GROUP BY city 
              ORDER BY visitors DESC 
              LIMIT 10";
$geo_result = mysqli_query($conn, $geo_query);

echo "<div class='row'>";
while ($city = mysqli_fetch_assoc($geo_result)) {
    echo "<div class='col-md-3 mb-2'>";
    echo "<div class='city-card'>";
    echo "<h5>" . htmlspecialchars($city['city']) . "</h5>";
    echo "<p>" . number_format($city['visitors']) . " visitors</p>";
    echo "</div>";
    echo "</div>";
}
echo "</div>";
echo "</div>";

// Referrer analysis
echo "<div class='referrer-analysis mb-4'>";
echo "<h3>🔗 Traffic Sources</h3>";

$referrer_query = "SELECT referrer, SUM(page_views) as views 
                  FROM traffic_analytics 
                  WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                  GROUP BY referrer 
                  ORDER BY views DESC";
$referrer_result = mysqli_query($conn, $referrer_query);

echo "<div class='table-responsive'>";
echo "<table class='table table-striped'>";
echo "<thead><tr><th>Source</th><th>Views</th><th>Percentage</th></tr></thead>";
echo "<tbody>";

while ($referrer = mysqli_fetch_assoc($referrer_result)) {
    $percentage = ($referrer['views'] / $total_stats['total_views']) * 100;
    echo "<tr>";
    echo "<td>" . htmlspecialchars($referrer['referrer']) . "</td>";
    echo "<td>" . number_format($referrer['views']) . "</td>";
    echo "<td>" . round($percentage, 1) . "%</td>";
    echo "</tr>";
}

echo "</tbody></table>";
echo "</div>";
echo "</div>";

echo "</div>";

// JavaScript for charts
echo "<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>";
echo "<script>";
echo "// Hourly traffic chart
const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
const hourlyChart = new Chart(hourlyCtx, {
    type: 'line',
    data: {
        labels: ['12AM', '1AM', '2AM', '3AM', '4AM', '5AM', '6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM'],
        datasets: [{
            label: 'Page Views',
            data: [120, 80, 60, 50, 70, 150, 300, 500, 800, 1200, 1500, 1800, 2000, 2200, 2100, 1900, 1700, 2000, 2500, 2800, 2400, 1800, 1200, 600],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Peak Hours: 9AM - 9PM'
            }
        }
    }
});";
echo "</script>";

// CSS styling
echo "<style>
.analytics-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
    margin-bottom: 20px;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card.bg-primary { border-top: 4px solid #007bff; }
.stat-card.bg-success { border-top: 4px solid #28a745; }
.stat-card.bg-info { border-top: 4px solid #17a2b8; }
.stat-card.bg-warning { border-top: 4px solid #ffc107; }

.stat-card h4 {
    font-size: 2em;
    margin-bottom: 5px;
    color: #333;
}

.stat-card p {
    color: #666;
    margin: 0;
    font-size: 0.9em;
}

.chart-section {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.top-pages, .device-breakdown, .geo-distribution, .referrer-analysis {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.device-card, .city-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 10px;
}

.device-card h4, .city-card h5 {
    margin-bottom: 5px;
    color: #333;
}

.device-card p, .city-card p {
    color: #666;
    margin: 0;
}

.table {
    margin-bottom: 0;
}

.table th {
    background: #f8f9fa;
    border-top: none;
}
</style>";
?>

<style>
.text-success { color: #28a745; }
</style>
