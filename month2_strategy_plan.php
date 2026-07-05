<?php
require_once 'config/database.php';

echo "<h2>Month 2 Data-Driven Growth Strategy</h2>";

// Create strategy tracking table
$create_strategy_table = "CREATE TABLE IF NOT EXISTS month2_strategy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    initiative VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    priority ENUM('high', 'medium', 'low') DEFAULT 'medium',
    target_metric VARCHAR(100) NOT NULL,
    target_value DECIMAL(10,2) DEFAULT 0.00,
    current_value DECIMAL(10,2) DEFAULT 0.00,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('planned', 'in_progress', 'completed', 'on_hold') DEFAULT 'planned',
    start_date DATE,
    end_date DATE,
    assigned_to VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $create_strategy_table)) {
    echo "<p class='text-success'>✅ Strategy tracking table created</p>";
}

// Month 2 Strategic Initiatives
$strategic_initiatives = [
    [
        'initiative' => 'Optimize Ad Placements Based on A/B Test Results',
        'category' => 'Revenue Optimization',
        'priority' => 'high',
        'target_metric' => 'RPM Increase',
        'target_value' => 25.00,
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+14 days')),
        'assigned_to' => 'Ad Optimization Team',
        'notes' => 'Implement winning variations from sidebar and header tests'
    ],
    [
        'initiative' => 'Launch Multi-Network Ad Testing',
        'category' => 'Revenue Optimization',
        'priority' => 'high',
        'target_metric' => 'Revenue Growth',
        'target_value' => 40.00,
        'start_date' => date('Y-m-d', strtotime('+3 days')),
        'end_date' => date('Y-m-d', strtotime('+21 days')),
        'assigned_to' => 'Monetization Team',
        'notes' => 'Test Media.net and PropellerAds against AdSense'
    ],
    [
        'initiative' => 'Expand Affiliate Marketing Integration',
        'category' => 'Revenue Diversification',
        'priority' => 'medium',
        'target_metric' => 'Affiliate Revenue',
        'target_value' => 500.00,
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+30 days')),
        'assigned_to' => 'Content Team',
        'notes' => 'Add affiliate links to all new articles and top 10 existing articles'
    ],
    [
        'initiative' => 'Convert 3 Local Business Ad Deals',
        'category' => 'Direct Sales',
        'priority' => 'high',
        'target_metric' => 'Deals Closed',
        'target_value' => 3.00,
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+21 days')),
        'assigned_to' => 'Sales Team',
        'notes' => 'Focus on electronics, restaurants, and service businesses'
    ],
    [
        'initiative' => 'Increase Content Production to 25 Articles',
        'category' => 'Content Strategy',
        'priority' => 'medium',
        'target_metric' => 'Articles Published',
        'target_value' => 25.00,
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+30 days')),
        'assigned_to' => 'Editorial Team',
        'notes' => 'Focus on breaking news, business, and technology topics'
    ],
    [
        'initiative' => 'Implement Advanced SEO Optimization',
        'category' => 'Traffic Growth',
        'priority' => 'medium',
        'target_metric' => 'Organic Traffic Growth',
        'target_value' => 50.00,
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+30 days')),
        'assigned_to' => 'SEO Team',
        'notes' => 'Optimize meta tags, improve page speed, build backlinks'
    ],
    [
        'initiative' => 'Launch Email Newsletter Campaign',
        'category' => 'Audience Building',
        'priority' => 'medium',
        'target_metric' => 'Subscribers',
        'target_value' => 1000.00,
        'start_date' => date('Y-m-d', strtotime('+7 days')),
        'end_date' => date('Y-m-d', strtotime('+30 days')),
        'assigned_to' => 'Marketing Team',
        'notes' => 'Weekly newsletter with top stories and exclusive content'
    ],
    [
        'initiative' => 'Enhance Social Media Automation',
        'category' => 'Audience Building',
        'priority' => 'low',
        'target_metric' => 'Social Engagement',
        'target_value' => 25.00,
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+30 days')),
        'assigned_to' => 'Social Media Team',
        'notes' => 'Increase posting frequency, test different content types'
    ]
];

// Insert strategic initiatives
foreach ($strategic_initiatives as $initiative) {
    $name = mysqli_real_escape_string($conn, $initiative['initiative']);
    $category = mysqli_real_escape_string($conn, $initiative['category']);
    $priority = $initiative['priority'];
    $target_metric = mysqli_real_escape_string($conn, $initiative['target_metric']);
    $target_value = $initiative['target_value'];
    $start_date = $initiative['start_date'];
    $end_date = $initiative['end_date'];
    $assigned_to = mysqli_real_escape_string($conn, $initiative['assigned_to']);
    $notes = mysqli_real_escape_string($conn, $initiative['notes']);
    
    $check_query = "SELECT id FROM month2_strategy WHERE initiative = '$name'";
    if (mysqli_num_rows(mysqli_query($conn, $check_query)) == 0) {
        $insert_query = "INSERT INTO month2_strategy (initiative, category, priority, target_metric, target_value, start_date, end_date, assigned_to, notes) 
                         VALUES ('$name', '$category', '$priority', '$target_metric', $target_value, '$start_date', '$end_date', '$assigned_to', '$notes')";
        mysqli_query($conn, $insert_query);
    }
}

echo "<p class='text-success'>✅ Added " . count($strategic_initiatives) . " strategic initiatives</p>";

// Strategy Dashboard
echo "<div class='strategy-dashboard'>";

// Executive Summary
echo "<div class='executive-summary mb-4'>";
echo "<h3>📊 Month 2 Executive Summary</h3>";

echo "<div class='summary-grid'>";
echo "<div class='summary-card revenue'>";
echo "<h4>💰 Revenue Target</h4>";
echo "<p class='target'>Rs. 75,000</p>";
echo "<p class='description'>Month 2 Goal</p>";
echo "</div>";

echo "<div class='summary-card traffic'>";
echo "<h4>👥 Traffic Target</h4>";
echo "<p class='target'>75,000</p>";
echo "<p class='description'>Monthly Visitors</p>";
echo "</div>";

echo "<div class='summary-card content'>";
echo "<h4>📝 Content Target</h4>";
echo "<p class='target'>25</p>";
echo "<p class='description'>New Articles</p>";
echo "</div>";

echo "<div class='summary-card conversion'>";
echo "<h4>🎯 Conversion Target</h4>";
echo "<p class='target'>3.5%</p>";
echo "<p class='description'>Overall Rate</p>";
echo "</div>";
echo "</div>";
echo "</div>";

// Strategic Initiatives Tracking
echo "<div class='initiatives-tracking mb-4'>";
echo "<h3>🎯 Strategic Initiatives Tracking</h3>";

$initiatives_query = "SELECT * FROM month2_strategy ORDER BY priority DESC, target_value DESC";
$initiatives_result = mysqli_query($conn, $initiatives_query);

echo "<div class='initiatives-grid'>";
while ($initiative = mysqli_fetch_assoc($initiatives_result)) {
    $progress = $initiative['target_value'] > 0 ? round(($initiative['current_value'] / $initiative['target_value']) * 100, 1) : 0;
    $priority_color = $initiative['priority'] === 'high' ? '#dc3545' : ($initiative['priority'] === 'medium' ? '#ffc107' : '#28a745');
    $status_color = $initiative['status'] === 'completed' ? '#28a745' : ($initiative['status'] === 'in_progress' ? '#007bff' : '#6c757d');
    
    echo "<div class='initiative-card'>";
    echo "<div class='initiative-header'>";
    echo "<h4>" . htmlspecialchars($initiative['initiative']) . "</h4>";
    echo "<span class='priority-badge' style='background: $priority_color;'>" . ucfirst($initiative['priority']) . "</span>";
    echo "</div>";
    
    echo "<div class='initiative-body'>";
    echo "<div class='metrics'>";
    echo "<div class='metric'>";
    echo "<span class='label'>Target:</span>";
    echo "<span class='value'>" . $initiative['target_metric'] . ": " . $initiative['target_value'] . "</span>";
    echo "</div>";
    echo "<div class='metric'>";
    echo "<span class='label'>Current:</span>";
    echo "<span class='value'>" . round($initiative['current_value'], 2) . "</span>";
    echo "</div>";
    echo "<div class='metric'>";
    echo "<span class='label'>Progress:</span>";
    echo "<span class='value'>" . $progress . "%</span>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='progress-bar-container'>";
    echo "<div class='progress-bar' style='width: $progress%; background: $status_color;'></div>";
    echo "</div>";
    
    echo "<div class='details'>";
    echo "<p><strong>Category:</strong> " . htmlspecialchars($initiative['category']) . "</p>";
    echo "<p><strong>Assigned:</strong> " . htmlspecialchars($initiative['assigned_to']) . "</p>";
    echo "<p><strong>Timeline:</strong> " . date('M d', strtotime($initiative['start_date'])) . " - " . date('M d', strtotime($initiative['end_date'])) . "</p>";
    echo "<p><strong>Status:</strong> <span class='status-badge' style='background: $status_color;'>" . ucfirst(str_replace('_', ' ', $initiative['status'])) . "</span></p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}
echo "</div>";
echo "</div>";

// Revenue Projections
echo "<div class='revenue-projections mb-4'>";
echo "<h3>💰 Revenue Projections</h3>";

echo "<div class='projections-chart'>";
echo "<canvas id='revenueChart' width='400' height='200'></canvas>";
echo "</div>";

echo "<div class='projections-summary'>";
echo "<div class='projection-item'>";
echo "<h4>Conservative</h4>";
echo "<p>Rs. 50,000</p>";
echo "<small>Based on current trends</small>";
echo "</div>";
echo "<div class='projection-item'>";
echo "<h4>Moderate</h4>";
echo "<p>Rs. 75,000</p>";
echo "<small>With optimization</small>";
echo "</div>";
echo "<div class='projection-item'>";
echo "<h4>Aggressive</h4>";
echo "<p>Rs. 120,000</p>";
echo "<small>With all initiatives</small>";
echo "</div>";
echo "</div>";
echo "</div>";

// Action Plan Timeline
echo "<div class='action-timeline mb-4'>";
echo "<h3>📅 Action Plan Timeline</h3>";

echo "<div class='timeline'>";
echo "<div class='timeline-item'>";
echo "<div class='timeline-date'>Week 1</div>";
echo "<div class='timeline-content'>";
echo "<h4>Foundation</h4>";
echo "<ul>";
echo "<li>Implement A/B test winners</li>";
echo "<li>Launch multi-network testing</li>";
echo "<li>Start affiliate integration</li>";
echo "<li>Begin business outreach follow-ups</li>";
echo "</ul>";
echo "</div>";
echo "</div>";

echo "<div class='timeline-item'>";
echo "<div class='timeline-date'>Week 2</div>";
echo "<div class='timeline-content'>";
echo "<h4>Optimization</h4>";
echo "<ul>";
echo "<li>Analyze test results</li>";
echo "<li>Optimize underperforming areas</li>";
echo "<li>Scale successful initiatives</li>";
echo "<li>Launch email newsletter</li>";
echo "</ul>";
echo "</div>";
echo "</div>";

echo "<div class='timeline-item'>";
echo "<div class='timeline-date'>Week 3</div>";
echo "<div class='timeline-content'>";
echo "<h4>Growth</h4>";
echo "<ul>";
echo "<li>Expand successful ad networks</li>";
echo "<li>Increase content production</li>";
echo "<li>Close business deals</li>";
echo "<li>Enhance SEO implementation</li>";
echo "</ul>";
echo "</div>";
echo "</div>";

echo "<div class='timeline-item'>";
echo "<div class='timeline-date'>Week 4</div>";
echo "<div class='timeline-content'>";
echo "<h4>Scaling</h4>";
echo "<ul>";
echo "<li>Review Month 2 performance</li>";
echo "<li>Plan Month 3 strategy</li>";
echo "<li>Scale revenue streams</li>";
echo "<li>Prepare for growth</li>";
echo "</ul>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "</div>";

// JavaScript for charts
echo "<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>";
echo "<script>";
echo "// Revenue projection chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [{
            label: 'Conservative',
            data: [10000, 12500, 15000, 12500],
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.4
        }, {
            label: 'Moderate',
            data: [12000, 18000, 25000, 20000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.4
        }, {
            label: 'Aggressive',
            data: [15000, 25000, 40000, 40000],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Month 2 Revenue Projections (PKR)'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Revenue (PKR)'
                }
            }
        }
    }
});";
echo "</script>";

// CSS styling
echo "<style>
.strategy-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.executive-summary, .initiatives-tracking, .revenue-projections, .action-timeline {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.summary-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
}

.summary-card h4 {
    margin-bottom: 10px;
    font-size: 1.1em;
}

.summary-card .target {
    font-size: 2em;
    font-weight: bold;
    margin-bottom: 5px;
}

.summary-card .description {
    opacity: 0.9;
    margin: 0;
}

.initiatives-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.initiative-card {
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
    border-left: 4px solid #007bff;
}

.initiative-header {
    background: #e9ecef;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.initiative-header h4 {
    margin: 0;
    color: #333;
    font-size: 1.1em;
}

.priority-badge, .status-badge {
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.initiative-body {
    padding: 20px;
}

.metrics {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.metric {
    text-align: center;
    flex: 1;
}

.metric .label {
    display: block;
    font-size: 0.8em;
    color: #666;
    margin-bottom: 5px;
}

.metric .value {
    font-size: 1.1em;
    font-weight: bold;
    color: #333;
}

.progress-bar-container {
    background: #e9ecef;
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 15px;
}

.progress-bar {
    height: 100%;
    transition: width 0.3s ease;
}

.projections-chart {
    margin-bottom: 20px;
}

.projections-summary {
    display: flex;
    justify-content: space-around;
    gap: 20px;
}

.projection-item {
    text-align: center;
    flex: 1;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.projection-item h4 {
    margin-bottom: 10px;
    color: #333;
}

.projection-item p {
    font-size: 1.5em;
    font-weight: bold;
    color: #007bff;
    margin: 0;
}

.projection-item small {
    color: #666;
    font-size: 0.8em;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #007bff;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-date {
    position: absolute;
    left: -30px;
    background: #007bff;
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8em;
    font-weight: bold;
    width: 60px;
    text-align: center;
}

.timeline-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid #007bff;
}

.timeline-content h4 {
    margin-bottom: 10px;
    color: #333;
}

.timeline-content ul {
    margin: 0;
    padding-left: 20px;
}

.timeline-content li {
    margin-bottom: 5px;
    color: #555;
}
</style>";
?>

<style>
.text-success { color: #28a745; }
</style>
