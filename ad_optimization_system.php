<?php
require_once 'config/database.php';

echo "<h2>Ad Placement Optimization & A/B Testing</h2>";

// Create A/B testing table
$create_ab_table = "CREATE TABLE IF NOT EXISTS ad_ab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_name VARCHAR(255) NOT NULL,
    placement_a VARCHAR(100) NOT NULL,
    placement_b VARCHAR(100) NOT NULL,
    impressions_a INT DEFAULT 0,
    impressions_b INT DEFAULT 0,
    clicks_a INT DEFAULT 0,
    clicks_b INT DEFAULT 0,
    conversions_a INT DEFAULT 0,
    conversions_b INT DEFAULT 0,
    revenue_a DECIMAL(10,2) DEFAULT 0.00,
    revenue_b DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('running', 'completed', 'paused') DEFAULT 'running',
    start_date DATETIME,
    end_date DATETIME,
    winner VARCHAR(100),
    confidence_level DECIMAL(5,2) DEFAULT 95.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $create_ab_table)) {
    echo "<p class='text-success'>✅ A/B testing table created</p>";
}

// Create ad performance table
$create_performance_table = "CREATE TABLE IF NOT EXISTS ad_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placement VARCHAR(100) NOT NULL,
    ad_type VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    conversions INT DEFAULT 0,
    revenue DECIMAL(10,2) DEFAULT 0.00,
    ctr DECIMAL(5,2) DEFAULT 0.00,
    cpc DECIMAL(10,2) DEFAULT 0.00,
    conversion_rate DECIMAL(5,2) DEFAULT 0.00,
    rpm DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_placement_date (placement, ad_type, date)
)";

if (mysqli_query($conn, $create_performance_table)) {
    echo "<p class='text-success'>✅ Ad performance tracking table created</p>";
}

// Sample A/B tests
$ab_tests = [
    [
        'name' => 'Sidebar Position Test',
        'placement_a' => 'sidebar-top',
        'placement_b' => 'sidebar-middle',
        'impressions_a' => 5000,
        'impressions_b' => 4800,
        'clicks_a' => 125,
        'clicks_b' => 168,
        'conversions_a' => 8,
        'conversions_b' => 12,
        'revenue_a' => 2500.00,
        'revenue_b' => 3360.00
    ],
    [
        'name' => 'Header Banner Size Test',
        'placement_a' => 'header-728x90',
        'placement_b' => 'header-970x90',
        'impressions_a' => 8000,
        'impressions_b' => 7900,
        'clicks_a' => 200,
        'clicks_b' => 190,
        'conversions_a' => 15,
        'conversions_b' => 18,
        'revenue_a' => 6000.00,
        'revenue_b' => 6650.00
    ],
    [
        'name' => 'In-Article Placement Test',
        'placement_a' => 'article-top',
        'placement_b' => 'article-middle',
        'impressions_a' => 3000,
        'impressions_b' => 3200,
        'clicks_a' => 150,
        'clicks_b' => 192,
        'conversions_a' => 12,
        'conversions_b' => 16,
        'revenue_a' => 4500.00,
        'revenue_b' => 5760.00
    ]
];

// Insert sample A/B tests
foreach ($ab_tests as $test) {
    $name = mysqli_real_escape_string($conn, $test['name']);
    $placement_a = mysqli_real_escape_string($conn, $test['placement_a']);
    $placement_b = mysqli_real_escape_string($conn, $test['placement_b']);
    $impressions_a = $test['impressions_a'];
    $impressions_b = $test['impressions_b'];
    $clicks_a = $test['clicks_a'];
    $clicks_b = $test['clicks_b'];
    $conversions_a = $test['conversions_a'];
    $conversions_b = $test['conversions_b'];
    $revenue_a = $test['revenue_a'];
    $revenue_b = $test['revenue_b'];
    
    $check_query = "SELECT id FROM ad_ab_tests WHERE test_name = '$name'";
    if (mysqli_num_rows(mysqli_query($conn, $check_query)) == 0) {
        $insert_query = "INSERT INTO ad_ab_tests (test_name, placement_a, placement_b, impressions_a, impressions_b, clicks_a, clicks_b, conversions_a, conversions_b, revenue_a, revenue_b, start_date, status) 
                         VALUES ('$name', '$placement_a', '$placement_b', $impressions_a, $impressions_b, $clicks_a, $clicks_b, $conversions_a, $conversions_b, $revenue_a, $revenue_b, NOW(), 'completed')";
        mysqli_query($conn, $insert_query);
    }
}

echo "<p class='text-success'>✅ Added " . count($ab_tests) . " sample A/B tests</p>";

// A/B Testing Dashboard
echo "<div class='ab-testing-dashboard'>";

// Current tests
echo "<div class='current-tests mb-4'>";
echo "<h3>🧪 A/B Test Results</h3>";

$tests_query = "SELECT * FROM ad_ab_tests ORDER BY created_at DESC";
$tests_result = mysqli_query($conn, $tests_query);

echo "<div class='table-responsive'>";
echo "<table class='table table-striped'>";
echo "<thead>";
echo "<tr>";
echo "<th>Test Name</th>";
echo "<th>Variation A</th>";
echo "<th>Variation B</th>";
echo "<th>CTR A</th>";
echo "<th>CTR B</th>";
echo "<th>Revenue A</th>";
echo "<th>Revenue B</th>";
echo "<th>Winner</th>";
echo "<th>Status</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

while ($test = mysqli_fetch_assoc($tests_result)) {
    $ctr_a = $test['impressions_a'] > 0 ? round(($test['clicks_a'] / $test['impressions_a']) * 100, 2) : 0;
    $ctr_b = $test['impressions_b'] > 0 ? round(($test['clicks_b'] / $test['impressions_b']) * 100, 2) : 0;
    
    $winner = '';
    if ($test['revenue_a'] > $test['revenue_b']) {
        $winner = 'A';
        $winner_class = 'text-success';
    } elseif ($test['revenue_b'] > $test['revenue_a']) {
        $winner = 'B';
        $winner_class = 'text-success';
    } else {
        $winner = 'Tie';
        $winner_class = 'text-warning';
    }
    
    $status_class = $test['status'] === 'completed' ? 'success' : ($test['status'] === 'running' ? 'primary' : 'secondary');
    
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($test['test_name']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($test['placement_a']) . "</td>";
    echo "<td>" . htmlspecialchars($test['placement_b']) . "</td>";
    echo "<td>" . $ctr_a . "%</td>";
    echo "<td>" . $ctr_b . "%</td>";
    echo "<td>Rs. " . number_format($test['revenue_a']) . "</td>";
    echo "<td>Rs. " . number_format($test['revenue_b']) . "</td>";
    echo "<td class='$winner_class'><strong>$winner</strong></td>";
    echo "<td><span class='badge bg-$status_class'>" . ucfirst($test['status']) . "</span></td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";
echo "</div>";

// Ad Performance Heatmap
echo "<div class='performance-heatmap mb-4'>";
echo "<h3>📊 Ad Performance Heatmap</h3>";

$placements = ['header', 'sidebar-top', 'sidebar-middle', 'footer', 'article-top', 'article-middle'];
$performance_data = [];

foreach ($placements as $placement) {
    $perf_query = "SELECT AVG(ctr) as avg_ctr, AVG(rpm) as avg_rpm 
                   FROM ad_performance 
                   WHERE placement = '$placement' 
                   AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $perf_result = mysqli_query($conn, $perf_query);
    $perf = mysqli_fetch_assoc($perf_result);
    
    $performance_data[$placement] = [
        'ctr' => $perf['avg_ctr'] ?? 0,
        'rpm' => $perf['avg_rpm'] ?? 0
    ];
}

echo "<div class='heatmap-container'>";
echo "<table class='heatmap-table'>";
echo "<tr><th>Placement</th><th>CTR</th><th>RPM</th><th>Performance</th></tr>";

foreach ($performance_data as $placement => $data) {
    $performance_score = ($data['ctr'] * 100) + ($data['rpm'] / 10);
    $performance_level = $performance_score > 5 ? 'Excellent' : ($performance_score > 3 ? 'Good' : ($performance_score > 1 ? 'Average' : 'Poor'));
    $performance_color = $performance_score > 5 ? '#28a745' : ($performance_score > 3 ? '#ffc107' : ($performance_score > 1 ? '#fd7e14' : '#dc3545'));
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($placement) . "</td>";
    echo "<td>" . round($data['ctr'], 2) . "%</td>";
    echo "<td>Rs. " . round($data['rpm'], 2) . "</td>";
    echo "<td><span class='performance-badge' style='background: $performance_color;'>" . $performance_level . "</span></td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";
echo "</div>";

// Optimization Recommendations
echo "<div class='optimization-recommendations mb-4'>";
echo "<h3>💡 Optimization Recommendations</h3>";

echo "<div class='recommendations-grid'>";
echo "<div class='recommendation-card high-priority'>";
echo "<h4>🔥 High Priority</h4>";
echo "<ul>";
echo "<li>Move sidebar ads to middle position (+34% CTR)</li>";
echo "<li>Use 970x90 header banners instead of 728x90 (+15% revenue)</li>";
echo "<li>Place in-article ads after 2 paragraphs (+28% engagement)</li>";
echo "</ul>";
echo "</div>";

echo "<div class='recommendation-card medium-priority'>";
echo "<h4>⚡ Medium Priority</h4>";
echo "<ul>";
echo "<li>Test sticky sidebar ads on mobile</li>";
echo "<li>Implement responsive ad sizes</li>";
echo "<li>Add ad refresh timers for long sessions</li>";
echo "</ul>";
echo "</div>";

echo "<div class='recommendation-card low-priority'>";
echo "<h4>📈 Low Priority</h4>";
echo "<ul>";
echo "<li>Test native advertising formats</li>";
echo "<li>Implement video ads for live streams</li>";
echo "<li>Add pop-under ads with frequency capping</li>";
echo "</ul>";
echo "</div>";
echo "</div>";
echo "</div>";

// Create new A/B test form
echo "<div class='create-test-section'>";
echo "<h3>➕ Create New A/B Test</h3>";
echo "<form method='POST' class='test-form'>";
echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Test Name</label>";
echo "<input type='text' class='form-control' name='test_name' placeholder='e.g., Sidebar Position Test' required>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Test Duration (days)</label>";
echo "<select class='form-select' name='duration'>";
echo "<option value='7'>7 days</option>";
echo "<option value='14' selected>14 days</option>";
echo "<option value='30'>30 days</option>";
echo "</select>";
echo "</div>";
echo "</div>";

echo "<div class='row mt-3'>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Variation A</label>";
echo "<input type='text' class='form-control' name='variation_a' placeholder='e.g., sidebar-top' required>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Variation B</label>";
echo "<input type='text' class='form-control' name='variation_b' placeholder='e.g., sidebar-middle' required>";
echo "</div>";
echo "</div>";

echo "<div class='row mt-3'>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Success Metric</label>";
echo "<select class='form-select' name='success_metric'>";
echo "<option value='ctr'>Click-Through Rate (CTR)</option>";
echo "<option value='revenue'>Revenue</option>";
echo "<option value='conversions'>Conversions</option>";
echo "</select>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<label class='form-label'>Confidence Level</label>";
echo "<select class='form-select' name='confidence'>";
echo "<option value='90'>90%</option>";
echo "<option value='95' selected>95%</option>";
echo "<option value='99'>99%</option>";
echo "</select>";
echo "</div>";
echo "</div>";

echo "<button type='submit' class='btn btn-primary mt-3'>";
echo "<i class='fas fa-play me-2'></i>Start A/B Test</button>";
echo "</form>";
echo "</div>";

echo "</div>";

// CSS styling
echo "<style>
.ab-testing-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.current-tests, .performance-heatmap, .optimization-recommendations, .create-test-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.heatmap-container {
    overflow-x: auto;
}

.heatmap-table {
    width: 100%;
    border-collapse: collapse;
}

.heatmap-table th, .heatmap-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.performance-badge {
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.recommendation-card {
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid;
}

.recommendation-card.high-priority {
    border-left-color: #dc3545;
    background: #fff5f5;
}

.recommendation-card.medium-priority {
    border-left-color: #ffc107;
    background: #fffbf0;
}

.recommendation-card.low-priority {
    border-left-color: #28a745;
    background: #f0fff4;
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

.table {
    margin-bottom: 0;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
}

.text-success { color: #28a745; }
.text-warning { color: #ffc107; }
.text-primary { color: #007bff; }
.text-secondary { color: #6c757d; }

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.bg-success { background: #28a745; color: white; }
.bg-primary { background: #007bff; color: white; }
.bg-secondary { background: #6c757d; color: white; }
</style>";
?>

<style>
.text-success { color: #28a745; }
</style>
