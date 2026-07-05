<?php
require_once 'config/database.php';

echo "<h2>Multi-Ad Type Testing & Network Comparison</h2>";

// Create ad network comparison table
$create_network_table = "CREATE TABLE IF NOT EXISTS ad_network_comparison (
    id INT AUTO_INCREMENT PRIMARY KEY,
    network_name VARCHAR(100) NOT NULL,
    ad_type VARCHAR(100) NOT NULL,
    placement VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    conversions INT DEFAULT 0,
    revenue DECIMAL(10,2) DEFAULT 0.00,
    ctr DECIMAL(5,2) DEFAULT 0.00,
    cpc DECIMAL(10,2) DEFAULT 0.00,
    rpm DECIMAL(10,2) DEFAULT 0.00,
    fill_rate DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_network_placement_date (network_name, ad_type, placement, date)
)";

if (mysqli_query($conn, $create_network_table)) {
    echo "<p class='text-success'>✅ Ad network comparison table created</p>";
}

// Sample ad networks and types
$ad_networks = [
    [
        'network' => 'Google AdSense',
        'types' => ['Display', 'Native', 'Video', 'Link Units'],
        'strengths' => ['High fill rate', 'Good targeting', 'Reliable payments'],
        'weaknesses' => ['Lower CPC', 'Strict policies'],
        'avg_cpc' => 0.50
    ],
    [
        'network' => 'Media.net',
        'types' => ['Display', 'Native', 'Video'],
        'strengths' => ['Higher CPC', 'Good for Asia', 'Contextual targeting'],
        'weaknesses' => ['Lower fill rate', 'Slower payments'],
        'avg_cpc' => 0.75
    ],
    [
        'network' => 'PropellerAds',
        'types' => ['Push Notifications', 'Native', 'Popunder', 'Direct Click'],
        'strengths' => ['High CPC', 'Multiple formats', 'Fast approval'],
        'weaknesses' => ['Can be aggressive', 'User experience concerns'],
        'avg_cpc' => 1.20
    ],
    [
        'network' => 'Adsterra',
        'types' => ['Display', 'Native', 'Social Bar', 'Video'],
        'strengths' => ['Good for Pakistan', 'Weekly payments', 'Multiple formats'],
        'weaknesses' => ['Moderate fill rate', 'Limited reporting'],
        'avg_cpc' => 0.85
    ],
    [
        'network' => 'Amazon Associates',
        'types' => ['Native Shopping', 'Display Ads'],
        'strengths' => ['High commission', 'Trusted brand', 'Product targeting'],
        'weaknesses' => ['Only for products', 'Lower volume'],
        'avg_cpc' => 0.00 // Commission-based
    ]
];

// Sample performance data for comparison
$sample_performance = [];
foreach ($ad_networks as $network) {
    foreach ($network['types'] as $type) {
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $impressions = rand(1000, 10000);
            $clicks = rand(10, 200);
            $conversions = rand(1, 20);
            
            if ($network['network'] === 'Amazon Associates') {
                $revenue = $conversions * rand(50, 200); // Commission-based
                $cpc = 0;
            } else {
                $cpc = $network['avg_cpc'] * (rand(80, 120) / 100); // Variation
                $revenue = $clicks * $cpc;
                $conversions = rand(1, 10);
            }
            
            $sample_performance[] = [
                'network' => $network['network'],
                'type' => $type,
                'placement' => 'sidebar',
                'date' => $date,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'conversions' => $conversions,
                'revenue' => $revenue,
                'cpc' => $cpc
            ];
        }
    }
}

// Insert sample performance data
foreach ($sample_performance as $data) {
    $network = mysqli_real_escape_string($conn, $data['network']);
    $type = mysqli_real_escape_string($conn, $data['type']);
    $placement = mysqli_real_escape_string($conn, $data['placement']);
    $date = $data['date'];
    $impressions = $data['impressions'];
    $clicks = $data['clicks'];
    $conversions = $data['conversions'];
    $revenue = $data['revenue'];
    $cpc = $data['cpc'];
    $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
    $rpm = $impressions > 0 ? round(($revenue / $impressions) * 1000, 2) : 0;
    
    $insert_query = "INSERT INTO ad_network_comparison (network_name, ad_type, placement, date, impressions, clicks, conversions, revenue, ctr, cpc, rpm) 
                     VALUES ('$network', '$type', '$placement', '$date', $impressions, $clicks, $conversions, $revenue, $ctr, $cpc, $rpm)
                     ON DUPLICATE KEY UPDATE impressions = VALUES(impressions), clicks = VALUES(clicks), revenue = VALUES(revenue)";
    
    mysqli_query($conn, $insert_query);
}

echo "<p class='text-success'>✅ Added performance data for " . count($ad_networks) . " ad networks</p>";

// Ad Network Comparison Dashboard
echo "<div class='ad-testing-dashboard'>";

// Network Performance Comparison
echo "<div class='network-comparison mb-4'>";
echo "<h3>📊 Ad Network Performance Comparison</h3>";

$comparison_query = "SELECT network_name, ad_type, AVG(impressions) as avg_impressions, AVG(clicks) as avg_clicks, AVG(revenue) as avg_revenue, AVG(ctr) as avg_ctr, AVG(rpm) as avg_rpm 
                    FROM ad_network_comparison 
                    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                    GROUP BY network_name, ad_type 
                    ORDER BY avg_revenue DESC";
$comparison_result = mysqli_query($conn, $comparison_query);

echo "<div class='table-responsive'>";
echo "<table class='table table-striped'>";
echo "<thead>";
echo "<tr>";
echo "<th>Network</th>";
echo "<th>Ad Type</th>";
echo "<th>Avg Impressions</th>";
echo "<th>Avg CTR</th>";
echo "<th>Avg RPM</th>";
echo "<th>Avg Revenue/Day</th>";
echo "<th>Performance</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

while ($row = mysqli_fetch_assoc($comparison_result)) {
    $performance_score = ($row['avg_ctr'] * 10) + ($row['avg_rpm'] / 10);
    $performance_level = $performance_score > 5 ? 'Excellent' : ($performance_score > 3 ? 'Good' : ($performance_score > 1 ? 'Average' : 'Poor'));
    $performance_color = $performance_score > 5 ? '#28a745' : ($performance_score > 3 ? '#ffc107' : ($performance_score > 1 ? '#fd7e14' : '#dc3545'));
    
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($row['network_name']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($row['ad_type']) . "</td>";
    echo "<td>" . number_format($row['avg_impressions']) . "</td>";
    echo "<td>" . round($row['avg_ctr'], 2) . "%</td>";
    echo "<td>Rs. " . round($row['avg_rpm'], 2) . "</td>";
    echo "<td>Rs. " . round($row['avg_revenue'], 2) . "</td>";
    echo "<td><span class='performance-badge' style='background: $performance_color;'>$performance_level</span></td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";
echo "</div>";

// Ad Type Performance Analysis
echo "<div class='ad-type-analysis mb-4'>";
echo "<h3>🎯 Ad Type Performance Analysis</h3>";

$type_query = "SELECT ad_type, AVG(impressions) as avg_impressions, AVG(clicks) as avg_clicks, AVG(revenue) as avg_revenue, AVG(ctr) as avg_ctr, AVG(rpm) as avg_rpm 
               FROM ad_network_comparison 
               WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
               GROUP BY ad_type 
               ORDER BY avg_revenue DESC";
$type_result = mysqli_query($conn, $type_query);

echo "<div class='type-cards'>";
while ($row = mysqli_fetch_assoc($type_result)) {
    echo "<div class='type-card'>";
    echo "<h4>" . htmlspecialchars($row['ad_type']) . "</h4>";
    echo "<div class='type-stats'>";
    echo "<div class='stat'>";
    echo "<span class='label'>CTR:</span>";
    echo "<span class='value'>" . round($row['avg_ctr'], 2) . "%</span>";
    echo "</div>";
    echo "<div class='stat'>";
    echo "<span class='label'>RPM:</span>";
    echo "<span class='value'>Rs. " . round($row['avg_rpm'], 2) . "</span>";
    echo "</div>";
    echo "<div class='stat'>";
    echo "<span class='label'>Revenue:</span>";
    echo "<span class='value'>Rs. " . round($row['avg_revenue'], 2) . "</span>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}
echo "</div>";
echo "</div>";

// Testing Recommendations
echo "<div class='testing-recommendations mb-4'>";
echo "<h3>🧪 Testing Recommendations</h3>";

echo "<div class='recommendations-grid'>";
echo "<div class='recommendation-card'>";
echo "<h4>🚀 Immediate Tests</h4>";
echo "<ul>";
echo "<li><strong>Display Ads:</strong> Test Media.net vs AdSense</li>";
echo "<li><strong>Native Ads:</strong> Test PropellerAds native vs AdSense</li>";
echo "<li><strong>Video Ads:</strong> Test in-stream video placement</li>";
echo "<li><strong>Push Notifications:</strong> Test with 10% of traffic</li>";
echo "</ul>";
echo "</div>";

echo "<div class='recommendation-card'>";
echo "<h4>📈 Advanced Tests</h4>";
echo "<ul>";
echo "<li><strong>Header Bidding:</strong> Implement multiple networks</li>";
echo "<li><strong>Refresh Optimization:</strong> Test 30s vs 60s refresh</li>";
echo "<li><strong>Device Targeting:</strong> Test mobile-specific ads</li>";
echo "<li><strong>Time-based Targeting:</strong> Test peak hour optimization</li>";
echo "</ul>";
echo "</div>";

echo "<div class='recommendation-card'>";
echo "<h4>⚡ Quick Wins</h4>";
echo "<ul>";
echo "<li><strong>Lazy Loading:</strong> Load ads after scroll</li>";
echo "<li><strong>Ad Density:</strong> Test 1 vs 2 ads per page</li>";
echo "<li><strong>Color Optimization:</strong> Test ad color schemes</li>";
echo "<li><strong>Size Testing:</strong> Test 300x250 vs 336x280</li>";
echo "</ul>";
echo "</div>";
echo "</div>";
echo "</div>";

// Multi-Variate Test Setup
echo "<div class='multivariate-test-section'>";
echo "<h3>🔬 Multi-Variate Test Setup</h3>";
echo "<form method='POST' class='test-form'>";
echo "<div class='row'>";
echo "<div class='col-md-4'>";
echo "<label class='form-label'>Test Name</label>";
echo "<input type='text' class='form-control' name='test_name' placeholder='e.g., Network Comparison Test' required>";
echo "</div>";
echo "<div class='col-md-4'>";
echo "<label class='form-label'>Network A</label>";
echo "<select class='form-select' name='network_a'>";
echo "<option value='adsense'>Google AdSense</option>";
echo "<option value='media.net'>Media.net</option>";
echo "<option value='propellerads'>PropellerAds</option>";
echo "<option value='adsterra'>Adsterra</option>";
echo "</select>";
echo "</div>";
echo "<div class='col-md-4'>";
echo "<label class='form-label'>Network B</label>";
echo "<select class='form-select' name='network_b'>";
echo "<option value='media.net'>Media.net</option>";
echo "<option value='propellerads'>PropellerAds</option>";
echo "<option value='adsterra'>Adsterra</option>";
echo "<option value='adsense'>Google AdSense</option>";
echo "</select>";
echo "</div>";
echo "</div>";

echo "<div class='row mt-3'>";
echo "<div class='col-md-4'>";
echo "<label class='form-label'>Traffic Split</label>";
echo "<select class='form-select' name='traffic_split'>";
echo "<option value='50/50'>50% / 50%</option>";
echo "<option value='70/30'>70% / 30%</option>";
echo "<option value='80/20'>80% / 20%</option>";
echo "</select>";
echo "</div>";
echo "<div class='col-md-4'>";
echo "<label class='form-label'>Duration (days)</label>";
echo "<select class='form-select' name='duration'>";
echo "<option value='7'>7 days</option>";
echo "<option value='14' selected>14 days</option>";
echo "<option value='30'>30 days</option>";
echo "</select>";
echo "</div>";
echo "<div class='col-md-4'>";
echo "<label class='form-label'>Success Metric</label>";
echo "<select class='form-select' name='success_metric'>";
echo "<option value='revenue'>Total Revenue</option>";
echo "<option value='rpm'>RPM (Revenue per Mille)</option>";
echo "<option value='ctr'>Click-Through Rate</option>";
echo "</select>";
echo "</div>";
echo "</div>";

echo "<button type='submit' class='btn btn-primary mt-3'>";
echo "<i class='fas fa-play me-2'></i>Start Multi-Variate Test</button>";
echo "</form>";
echo "</div>";

echo "</div>";

// CSS styling
echo "<style>
.ad-testing-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.network-comparison, .ad-type-analysis, .testing-recommendations, .multivariate-test-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.type-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.type-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid #007bff;
}

.type-card h4 {
    margin-bottom: 15px;
    color: #333;
}

.type-stats {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.stat {
    text-align: center;
    flex: 1;
    min-width: 80px;
}

.stat .label {
    display: block;
    font-size: 0.8em;
    color: #666;
    margin-bottom: 5px;
}

.stat .value {
    font-size: 1.2em;
    font-weight: bold;
    color: #333;
}

.recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.recommendation-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid #007bff;
}

.recommendation-card h4 {
    margin-bottom: 15px;
    color: #333;
}

.recommendation-card ul {
    margin: 0;
    padding-left: 20px;
}

.recommendation-card li {
    margin-bottom: 8px;
    color: #555;
}

.test-form {
    margin-top: 20px;
}

.test-form .form-control, .test-form .form-select {
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 10px;
}

.test-form .form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.btn {
    padding: 12px 30px;
    border-radius: 25px;
    border: none;
    font-weight: bold;
    cursor: pointer;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.performance-badge {
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.table {
    margin-bottom: 0;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
}
</style>";
?>

<style>
.text-success { color: #28a745; }
</style>
