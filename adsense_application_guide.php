<?php
require_once 'config/database.php';

echo "<h2>Google AdSense Application Guide</h2>";

// Check AdSense requirements
$requirements = [
    'Domain Age' => ['status' => 'pending', 'description' => 'Domain must be 6+ months old for new accounts'],
    'Content Quality' => ['status' => 'ready', 'description' => '12+ professional articles created'],
    'Privacy Policy' => ['status' => 'ready', 'description' => 'Privacy policy page exists'],
    'Contact Information' => ['status' => 'ready', 'description' => 'Contact page with business emails'],
    'Website Navigation' => ['status' => 'ready', 'description' => 'Clear navigation and user-friendly design'],
    'Original Content' => ['status' => 'ready', 'description' => 'Unique, high-quality news content'],
    'Traffic' => ['status' => 'pending', 'description' => 'Need consistent traffic (100+ daily visitors)']
];

echo "<div class='requirements-check'>";
foreach ($requirements as $requirement => $details) {
    $status_icon = $details['status'] === 'ready' ? '✅' : ($details['status'] === 'pending' ? '⏳' : '❌');
    $status_class = $details['status'] === 'ready' ? 'text-success' : ($details['status'] === 'pending' ? 'text-warning' : 'text-danger');
    
    echo "<div class='requirement-item mb-3'>";
    echo "<h5>$status_icon $requirement <span class='badge bg-$status_class'>{$details['status']}</span></h5>";
    echo "<p class='text-muted'>{$details['description']}</p>";
    echo "</div>";
}
echo "</div>";

// Create AdSense readiness checklist
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-primary text-white'>";
echo "<h4>📋 AdSense Readiness Checklist</h4>";
echo "</div>";
echo "<div class='card-body'>";
echo "<ol>";
echo "<li><strong>Domain Age:</strong> If your domain is less than 6 months old, focus on building content and traffic first</li>";
echo "<li><strong>Content Quality:</strong> Ensure all articles are original, well-written, and provide value to readers</li>";
echo "<li><strong>Website Design:</strong> Mobile-responsive, fast loading, and professional appearance</li>";
echo "<li><strong>Navigation:</strong> Clear menu structure, working links, and easy user experience</li>";
echo "<li><strong>Legal Pages:</strong> Privacy Policy, Terms of Service, and Contact information</li>";
echo "<li><strong>Traffic:</strong> Aim for 100+ daily visitors before applying</li>";
echo "</ol>";
echo "</div>";
echo "</div>";

// AdSense application steps
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-success text-white'>";
echo "<h4>🚀 Application Steps</h4>";
echo "</div>";
echo "<div class='card-body'>";
echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<h5>Step 1: Preparation</h5>";
echo "<ul>";
echo "<li>✅ Create Google Account</li>";
echo "<li>✅ Verify website ownership</li>";
echo "<li>✅ Set up Google Analytics</li>";
echo "<li>✅ Improve website speed</li>";
echo "<li>✅ Add more content (20+ articles)</li>";
echo "</ul>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<h5>Step 2: Application</h5>";
echo "<ul>";
echo "<li>Visit <a href='https://www.google.com/adsense/start/' target='_blank'>Google AdSense</a></li>";
echo "<li>Sign in with your Google account</li>";
echo "<li>Submit your website URL</li>";
echo "<li>Provide payment information</li>";
echo "<li>Wait for review (2-7 days)</li>";
echo "</ul>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

// Create AdSense ad placement guide
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-info text-white'>";
echo "<h4>📱 Recommended Ad Placements</h4>";
echo "</div>";
echo "<div class='card-body'>";
echo "<div class='row'>";
echo "<div class='col-md-4'>";
echo "<h6>Header Banner</h6>";
echo "<p>728x90 leaderboard above navigation</p>";
echo "<small class='text-muted'>High visibility, good CTR</small>";
echo "</div>";
echo "<div class='col-md-4'>";
echo "<h6>Sidebar Rectangle</h6>";
echo "<p>300x250 medium rectangle</p>";
echo "<small class='text-muted'>Stable income, good engagement</small>";
echo "</div>";
echo "<div class='col-md-4'>";
echo "<h6>In-Article Ads</h6>";
echo "<p>Responsive ads within content</p>";
echo "<small class='text-muted'>High engagement, native feel</small>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

// Create AdSense tracking system
$create_adsense_table = "CREATE TABLE IF NOT EXISTS adsense_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    page_views INT DEFAULT 0,
    ad_impressions INT DEFAULT 0,
    ad_clicks INT DEFAULT 0,
    earnings DECIMAL(10,2) DEFAULT 0.00,
    ctr DECIMAL(5,2) DEFAULT 0.00,
    cpc DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (date)
)";

if (mysqli_query($conn, $create_adsense_table)) {
    echo "<p class='text-success'>✅ AdSense analytics table created</p>";
}

echo "<div class='alert alert-info mt-4'>";
echo "<h5>💡 Pro Tips for AdSense Approval:</h5>";
echo "<ul>";
echo "<li>Wait until you have 50+ high-quality articles</li>";
echo "<li>Ensure your site loads in under 3 seconds</li>";
echo "<li>Make sure all images are optimized and have alt tags</li>";
echo "<li>Remove any broken links or 404 errors</li>";
echo "<li>Have consistent daily traffic for at least 2 weeks</li>";
echo "<li>Don't place ads on empty or under-construction pages</li>";
echo "</ul>";
echo "</div>";

echo "<div class='mt-4'>";
echo "<a href='https://www.google.com/adsense/start/' target='_blank' class='btn btn-primary btn-lg me-2'>";
echo "<i class='fab fa-google me-2'></i>Apply for Google AdSense</a>";
echo "<a href='advertising_rate_card.php' class='btn btn-secondary btn-lg'>";
echo "<i class='fas fa-file-invoice me-2'></i>View Rate Card</a>";
echo "</div>";
?>

<style>
.requirements-check { max-width: 600px; }
.requirement-item { padding: 10px; border-left: 4px solid #007bff; margin-left: 10px; }
.text-success { color: #28a745; }
.text-warning { color: #ffc107; }
.text-danger { color: #dc3545; }
.card { margin-bottom: 20px; }
.card-header { padding: 15px; }
.btn { padding: 12px 24px; text-decoration: none; border-radius: 5px; }
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.alert { padding: 15px; border-radius: 5px; }
.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; }
</style>
