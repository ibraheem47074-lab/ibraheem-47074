<?php
require_once 'config/database.php';

echo "<h2>Professional Advertising Rate Card</h2>";

// Create rate card settings table
$create_rates_table = "CREATE TABLE IF NOT EXISTS advertising_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_type VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    dimensions VARCHAR(50),
    duration VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'PKR',
    description TEXT,
    features TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $create_rates_table)) {
    echo "<p class='text-success'>✅ Advertising rates table created</p>";
}

// Professional advertising packages
$advertising_packages = [
    [
        'type' => 'Sidebar Banner',
        'position' => 'Right Sidebar',
        'dimensions' => '300x250px',
        'duration' => 'Monthly',
        'price' => 15000.00,
        'currency' => 'PKR',
        'description' => 'Medium rectangle banner in right sidebar, visible on all pages',
        'features' => 'Unlimited impressions,Click tracking,Monthly performance report,Free banner design'
    ],
    [
        'type' => 'Header Banner',
        'position' => 'Top Header',
        'dimensions' => '728x90px',
        'duration' => 'Monthly',
        'price' => 25000.00,
        'currency' => 'PKR',
        'description' => 'Leaderboard banner at top of website, maximum visibility',
        'features' => 'Premium placement,Unlimited impressions,Advanced analytics,Priority support'
    ],
    [
        'type' => 'Footer Banner',
        'position' => 'Bottom Footer',
        'dimensions' => '970x90px',
        'duration' => 'Monthly',
        'price' => 20000.00,
        'currency' => 'PKR',
        'description' => 'Wide banner at bottom of all pages',
        'features' => 'Full width display,High visibility,Monthly analytics,Free design service'
    ],
    [
        'type' => 'In-Article Ad',
        'position' => 'Within Content',
        'dimensions' => 'Responsive',
        'duration' => 'Monthly',
        'price' => 30000.00,
        'currency' => 'PKR',
        'description' => 'Responsive ad within article content, high engagement',
        'features' => 'Native integration,High CTR,Content targeting,A/B testing'
    ],
    [
        'type' => 'Sponsored Article',
        'position' => 'News Feed',
        'dimensions' => 'Article Format',
        'duration' => 'Per Article',
        'price' => 35000.00,
        'currency' => 'PKR',
        'description' => 'Branded article promoting your business/product',
        'features' => 'Professional writing,SEO optimization,Social media promotion,Permanent placement'
    ],
    [
        'type' => 'Business Spotlight',
        'position' => 'Homepage Featured',
        'dimensions' => 'Featured Section',
        'duration' => 'Monthly',
        'price' => 40000.00,
        'currency' => 'PKR',
        'description' => 'Featured business section on homepage',
        'features' => 'Homepage placement,Logo display,Company profile,Direct link to website'
    ],
    [
        'type' => 'Live Stream Sponsor',
        'position' => 'Video Content',
        'dimensions' => 'Video Overlay',
        'duration' => 'Per Stream',
        'price' => 50000.00,
        'currency' => 'PKR',
        'description' => 'Sponsorship of live streaming sessions',
        'features' => 'Brand mentions,Logo placement,Product integration,Audience engagement'
    ],
    [
        'type' => 'Newsletter Ad',
        'position' => 'Email Newsletter',
        'dimensions' => 'Email Format',
        'duration' => 'Per Campaign',
        'price' => 12000.00,
        'currency' => 'PKR',
        'description' => 'Advertisement in weekly newsletter to subscribers',
        'features' => 'Direct email,High open rates,Click tracking,Audience targeting'
    ]
];

// Clear existing rates and insert new ones
mysqli_query($conn, "DELETE FROM advertising_rates");

foreach ($advertising_packages as $package) {
    $type = mysqli_real_escape_string($conn, $package['type']);
    $position = mysqli_real_escape_string($conn, $package['position']);
    $dimensions = mysqli_real_escape_string($conn, $package['dimensions']);
    $duration = mysqli_real_escape_string($conn, $package['duration']);
    $price = $package['price'];
    $currency = mysqli_real_escape_string($conn, $package['currency']);
    $description = mysqli_real_escape_string($conn, $package['description']);
    $features = mysqli_real_escape_string($conn, $package['features']);
    
    $insert_query = "INSERT INTO advertising_rates (ad_type, position, dimensions, duration, price, currency, description, features) 
                     VALUES ('$type', '$position', '$dimensions', '$duration', $price, '$currency', '$description', '$features')";
    mysqli_query($conn, $insert_query);
}

echo "<p class='text-success'>✅ Added " . count($advertising_packages) . " advertising packages</p>";

// Professional rate card display
echo "<div class='rate-card-container'>";
echo "<div class='header-section text-center mb-5'>";
echo "<h1 class='main-title'>PK Live News</h1>";
echo "<h2 class='subtitle'>Advertising Rate Card 2024</h2>";
echo "<p class='description'>Reach thousands of engaged Pakistani readers with targeted advertising solutions</p>";
echo "</div>";

// Audience demographics
echo "<div class='audience-section mb-5'>";
echo "<h3 class='section-title'>🎯 Our Audience</h3>";
echo "<div class='row'>";
echo "<div class='col-md-3'>";
echo "<div class='stat-card'>";
echo "<h4>50,000+</h4>";
echo "<p>Monthly Readers</p>";
echo "</div>";
echo "</div>";
echo "<div class='col-md-3'>";
echo "<div class='stat-card'>";
echo "<h4>75%</h4>";
echo "<p>From Major Cities</p>";
echo "</div>";
echo "</div>";
echo "<div class='col-md-3'>";
echo "<div class='stat-card'>";
echo "<h4>25-55</h4>";
echo "<p>Age Group</p>";
echo "</div>";
echo "</div>";
echo "<div class='col-md-3'>";
echo "<div class='stat-card'>";
echo "<h4>60%</h4>";
echo "<p>College Educated</p>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

// Advertising packages
echo "<div class='packages-section'>";
echo "<h3 class='section-title'>💰 Advertising Packages</h3>";

$rates_query = "SELECT * FROM advertising_rates WHERE status = 'active' ORDER BY price ASC";
$rates_result = mysqli_query($conn, $rates_query);

echo "<div class='row'>";
while ($rate = mysqli_fetch_assoc($rates_result)) {
    $featured_class = $rate['price'] >= 30000 ? 'featured' : '';
    
    echo "<div class='col-md-6 col-lg-4 mb-4'>";
    echo "<div class='package-card $featured_class'>";
    
    if ($featured_class) {
        echo "<div class='featured-badge'>Most Popular</div>";
    }
    
    echo "<div class='package-header'>";
    echo "<h4>" . htmlspecialchars($rate['ad_type']) . "</h4>";
    echo "<p class='position'>" . htmlspecialchars($rate['position']) . "</p>";
    echo "</div>";
    
    echo "<div class='package-price'>";
    echo "<h2>Rs. " . number_format($rate['price']) . "</h2>";
    echo "<p class='duration'>" . htmlspecialchars($rate['duration']) . "</p>";
    echo "</div>";
    
    echo "<div class='package-details'>";
    echo "<p><strong>Dimensions:</strong> " . htmlspecialchars($rate['dimensions']) . "</p>";
    echo "<p><strong>Description:</strong> " . htmlspecialchars($rate['description']) . "</p>";
    echo "</div>";
    
    echo "<div class='package-features'>";
    echo "<h5>Features:</h5>";
    echo "<ul>";
    $features = explode(',', $rate['features']);
    foreach ($features as $feature) {
        echo "<li>✓ " . htmlspecialchars(trim($feature)) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='package-action'>";
    echo "<button class='btn btn-primary btn-block' onclick='contactForAd(\"" . htmlspecialchars($rate['ad_type']) . "\")'>";
    echo "Get Started";
    echo "</button>";
    echo "</div>";
    
    echo "</div>";
    echo "</div>";
}

echo "</div>";
echo "</div>";

// Special packages
echo "<div class='special-packages-section mt-5'>";
echo "<h3 class='section-title'>🌟 Special Packages</h3>";
echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<div class='special-card'>";
echo "<h4>Startup Package</h4>";
echo "<p class='price'>Rs. 75,000 <span>/month</span></p>";
echo "<ul>";
echo "<li>✓ Sidebar Banner</li>";
echo "<li>✓ Newsletter Ad</li>";
echo "<li>✓ Basic Analytics</li>";
echo "<li>✓ 3-Month Commitment</li>";
echo "</ul>";
echo "<button class='btn btn-success'>Choose Startup</button>";
echo "</div>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<div class='special-card premium'>";
echo "<h4>Premium Package</h4>";
echo "<p class='price'>Rs. 150,000 <span>/month</span></p>";
echo "<ul>";
echo "<li>✓ Header + Sidebar Banners</li>";
echo "<li>✓ Sponsored Article (1/month)</li>";
echo "<li>✓ Live Stream Sponsorship</li>";
echo "<li>✓ Advanced Analytics</li>";
echo "<li>✓ Priority Support</li>";
echo "</ul>";
echo "<button class='btn btn-warning'>Choose Premium</button>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

// Contact information
echo "<div class='contact-section mt-5'>";
echo "<div class='contact-card'>";
echo "<h3>📞 Ready to Advertise?</h3>";
echo "<p>Contact our advertising team to get started:</p>";
echo "<div class='contact-info'>";
echo "<p><strong>Email:</strong> <a href='mailto:ads@pklivenews.com'>ads@pklivenews.com</a></p>";
echo "<p><strong>Phone:</strong> +92-XXX-XXXXXXX</p>";
echo "<p><strong>WhatsApp:</strong> +92-XXX-XXXXXXX</p>";
echo "</div>";
echo "<div class='contact-actions'>";
echo "<button class='btn btn-primary btn-lg me-2' onclick='window.location.href=\"mailto:ads@pklivenews.com\"'>";
echo "<i class='fas fa-envelope me-2'></i>Email Us</button>";
echo "<button class='btn btn-success btn-lg' onclick='window.location.href=\"contact.php\"'>";
echo "<i class='fas fa-phone me-2'></i>Contact Form</button>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "</div>";

// CSS styling
echo "<style>
.rate-card-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.header-section {
    background: linear-gradient(135deg, #dc3545 0%, #764ba2 100%);
    color: white;
    padding: 60px 20px;
    border-radius: 15px;
    margin-bottom: 40px;
}

.main-title {
    font-size: 3em;
    font-weight: bold;
    margin-bottom: 10px;
}

.subtitle {
    font-size: 1.8em;
    margin-bottom: 20px;
}

.description {
    font-size: 1.2em;
    opacity: 0.9;
}

.section-title {
    font-size: 2em;
    color: #333;
    margin-bottom: 30px;
    text-align: center;
}

.audience-section {
    background: #f8f9fa;
    padding: 40px;
    border-radius: 15px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.stat-card h4 {
    font-size: 2.5em;
    color: #dc3545;
    margin-bottom: 5px;
}

.stat-card p {
    font-size: 1.1em;
    color: #666;
    margin: 0;
}

.package-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    height: 100%;
    position: relative;
    transition: transform 0.3s ease;
}

.package-card:hover {
    transform: translateY(-5px);
}

.package-card.featured {
    border: 3px solid #dc3545;
}

.featured-badge {
    position: absolute;
    top: -15px;
    right: 20px;
    background: #dc3545;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9em;
}

.package-header {
    text-align: center;
    margin-bottom: 20px;
}

.package-header h4 {
    font-size: 1.5em;
    color: #333;
    margin-bottom: 5px;
}

.position {
    color: #666;
    font-style: italic;
}

.package-price {
    text-align: center;
    margin-bottom: 20px;
}

.package-price h2 {
    font-size: 2.5em;
    color: #dc3545;
    margin-bottom: 5px;
}

.duration {
    color: #666;
    font-size: 1.1em;
}

.package-details {
    margin-bottom: 20px;
}

.package-features ul {
    list-style: none;
    padding: 0;
}

.package-features li {
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

.package-action {
    margin-top: 20px;
}

.special-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.special-card.premium {
    border: 3px solid #ffc107;
}

.special-card h4 {
    font-size: 1.8em;
    margin-bottom: 20px;
}

.special-card .price {
    font-size: 2em;
    color: #dc3545;
    margin-bottom: 20px;
}

.special-card .price span {
    font-size: 0.6em;
    color: #666;
}

.contact-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 50px;
    border-radius: 15px;
    text-align: center;
}

.contact-card {
    max-width: 600px;
    margin: 0 auto;
}

.contact-card h3 {
    font-size: 2em;
    margin-bottom: 20px;
}

.contact-info {
    margin-bottom: 30px;
}

.contact-info p {
    font-size: 1.2em;
    margin-bottom: 10px;
}

.contact-info a {
    color: white;
    text-decoration: none;
}

.btn {
    padding: 12px 30px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: bold;
    margin: 5px;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-warning {
    background: #ffc107;
    color: #333;
}

.btn-block {
    display: block;
    width: 100%;
}
</style>";

echo "<script>
function contactForAd(adType) {
    window.location.href = 'mailto:ads@pklivenews.com?subject=Interested in ' + encodeURIComponent(adType) + ' Advertising';
}
</script>";
?>

<style>
.text-success { color: #28a745; }
</style>
