<?php
require_once 'config/database.php';

echo "<h2>Affiliate Links Integration System</h2>";

// Create affiliate links table
$create_affiliate_table = "CREATE TABLE IF NOT EXISTS affiliate_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    affiliate_network VARCHAR(100) NOT NULL,
    original_url TEXT NOT NULL,
    affiliate_url TEXT NOT NULL,
    category VARCHAR(100),
    commission_rate DECIMAL(5,2) DEFAULT 0.00,
    clicks INT DEFAULT 0,
    conversions INT DEFAULT 0,
    earnings DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $create_affiliate_table)) {
    echo "<p class='text-success'>✅ Affiliate links table created</p>";
}

// Create affiliate analytics table
$create_analytics_table = "CREATE TABLE IF NOT EXISTS affiliate_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    affiliate_id INT NOT NULL,
    date DATE NOT NULL,
    clicks INT DEFAULT 0,
    conversions INT DEFAULT 0,
    earnings DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (affiliate_id) REFERENCES affiliate_links(id) ON DELETE CASCADE,
    UNIQUE KEY unique_affiliate_date (affiliate_id, date)
)";

if (mysqli_query($conn, $create_analytics_table)) {
    echo "<p class='text-success'>✅ Affiliate analytics table created</p>";
}

// Sample affiliate products for Pakistani market
$affiliate_products = [
    [
        'name' => 'Daraz Online Shopping - Electronics',
        'network' => 'Daraz Affiliate',
        'original_url' => 'https://www.daraz.pk/electronics',
        'affiliate_url' => 'https://s.click.aliexpress.com/e/daraz_electronics',
        'category' => 'E-commerce',
        'commission_rate' => 5.50
    ],
    [
        'name' => 'Amazon Pakistan - Tech Gadgets',
        'network' => 'Amazon Associates',
        'original_url' => 'https://www.amazon.com/tech-gadgets',
        'affiliate_url' => 'https://amzn.to/3xyz123-tech-gadgets',
        'category' => 'Technology',
        'commission_rate' => 4.00
    ],
    [
        'name' => 'Hostinger Pakistan - Web Hosting',
        'network' => 'Hostinger Affiliate',
        'original_url' => 'https://www.hostinger.com/pk',
        'affiliate_url' => 'https://hostinger.com/aff?ref=pklivenews',
        'category' => 'Web Services',
        'commission_rate' => 60.00
    ],
    [
        'name' => 'GetYourGuide - Pakistan Tours',
        'network' => 'GetYourGuide',
        'original_url' => 'https://www.getyourguide.com/pakistan',
        'affiliate_url' => 'https://www.getyourguide.com/pakistan?partner=pklivenews',
        'category' => 'Travel',
        'commission_rate' => 8.00
    ],
    [
        'name' => 'Jazz Cash - Digital Wallet',
        'network' => 'Jazz Affiliate',
        'original_url' => 'https://jazzcash.com.pk',
        'affiliate_url' => 'https://jazzcash.com.pk?ref=pklivenews',
        'category' => 'Finance',
        'commission_rate' => 25.00
    ],
    [
        'name' => 'Foodpanda Pakistan - Food Delivery',
        'network' => 'Foodpanda Partner',
        'original_url' => 'https://www.foodpanda.pk',
        'affiliate_url' => 'https://www.foodpanda.pk?ref=pklivenews',
        'category' => 'Food Delivery',
        'commission_rate' => 15.00
    ],
    [
        'name' => 'Careem Pakistan - Ride Hailing',
        'network' => 'Careem Partner',
        'original_url' => 'https://www.careem.com/pakistan',
        'affiliate_url' => 'https://www.careem.com/pakistan?ref=pklivenews',
        'category' => 'Transportation',
        'commission_rate' => 50.00
    ],
    [
        'name' => 'Telemart - Electronics Store',
        'network' => 'Telemart Affiliate',
        'original_url' => 'https://telemart.pk',
        'affiliate_url' => 'https://telemart.pk?ref=pklivenews',
        'category' => 'Electronics',
        'commission_rate' => 3.50
    ]
];

// Insert sample affiliate products
foreach ($affiliate_products as $product) {
    $name = mysqli_real_escape_string($conn, $product['name']);
    $network = mysqli_real_escape_string($conn, $product['network']);
    $original_url = mysqli_real_escape_string($conn, $product['original_url']);
    $affiliate_url = mysqli_real_escape_string($conn, $product['affiliate_url']);
    $category = mysqli_real_escape_string($conn, $product['category']);
    $commission_rate = $product['commission_rate'];
    
    $check_query = "SELECT id FROM affiliate_links WHERE product_name = '$name'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $insert_query = "INSERT INTO affiliate_links (product_name, affiliate_network, original_url, affiliate_url, category, commission_rate) 
                         VALUES ('$name', '$network', '$original_url', '$affiliate_url', '$category', $commission_rate)";
        mysqli_query($conn, $insert_query);
    }
}

echo "<p class='text-success'>✅ Added " . count($affiliate_products) . " affiliate products</p>";

// Affiliate link management dashboard
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-success text-white'>";
echo "<h4>💰 Affiliate Products Dashboard</h4>";
echo "</div>";
echo "<div class='card-body'>";

$affiliate_query = "SELECT * FROM affiliate_links ORDER BY created_at DESC";
$affiliate_result = mysqli_query($conn, $affiliate_query);

if (mysqli_num_rows($affiliate_result) > 0) {
    echo "<div class='table-responsive'>";
    echo "<table class='table table-striped'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Product</th>";
    echo "<th>Network</th>";
    echo "<th>Category</th>";
    echo "<th>Commission</th>";
    echo "<th>Clicks</th>";
    echo "<th>Conversions</th>";
    echo "<th>Earnings</th>";
    echo "<th>Status</th>";
    echo "<th>Actions</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    $total_earnings = 0;
    while ($affiliate = mysqli_fetch_assoc($affiliate_result)) {
        $status_class = $affiliate['status'] === 'active' ? 'success' : 'secondary';
        $total_earnings += $affiliate['earnings'];
        
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($affiliate['product_name']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($affiliate['affiliate_network']) . "</td>";
        echo "<td>" . htmlspecialchars($affiliate['category']) . "</td>";
        echo "<td>" . $affiliate['commission_rate'] . "%</td>";
        echo "<td>" . number_format($affiliate['clicks']) . "</td>";
        echo "<td>" . $affiliate['conversions'] . "</td>";
        echo "<td>Rs. " . number_format($affiliate['earnings'], 2) . "</td>";
        echo "<td><span class='badge bg-$status_class'>" . ucfirst($affiliate['status']) . "</span></td>";
        echo "<td>";
        echo "<button class='btn btn-sm btn-outline-primary' onclick='copyAffiliateLink(\"{$affiliate['affiliate_url']}\")'>";
        echo "<i class='fas fa-copy'></i>";
        echo "</button>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    
    echo "<div class='row mt-3'>";
    echo "<div class='col-md-12'>";
    echo "<div class='alert alert-info'>";
    echo "<h5>📊 Total Affiliate Earnings: Rs. " . number_format($total_earnings, 2) . "</h5>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<p class='text-muted'>No affiliate products added yet.</p>";
}

echo "</div>";
echo "</div>";

// Affiliate integration guide
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-info text-white'>";
echo "<h4>🔗 How to Integrate Affiliate Links</h4>";
echo "</div>";
echo "<div class='card-body'>";

echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<h5>📝 In Articles</h5>";
echo "<p>Include affiliate links naturally within your news content:</p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "// In your article content\n";
echo "For the latest smartphones, check out <a href='[AFFILIATE_URL]' class='affiliate-link'>Daraz Electronics</a> for great deals.";
echo "</pre>";
echo "</div>";

echo "<div class='col-md-6'>";
echo "<h5>🛍️ Product Reviews</h5>";
echo "<p>Create dedicated product review sections:</p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "// Product review template\n";
echo "echo '<div class=\\\"product-review\\\">';\n";
echo "echo '<h3>Product Name</h3>';\n";
echo "echo '<p>Review content...</p>';\n";
echo "echo '<a href=\\\"[AFFILIATE_URL]\\\" class=\\\"btn btn-primary\\\">Buy Now</a>';\n";
echo "echo '</div>';\n";
echo "</pre>";
echo "</div>";
echo "</div>";

echo "<div class='col-md-6'>";
echo "<h5>📱 Banner Ads</h5>";
echo "<p>Create affiliate banner sections:</p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "// Affiliate banner\necho '<div class=\"affiliate-banner\">';\necho '<a href=\"[AFFILIATE_URL]\" target=\"_blank\">';\necho '<img src=\"banner-image.jpg\" alt=\"Product\">';\necho '</a>';\necho '</div>';";
echo "</pre>";
echo "</div>";

echo "<div class='col-md-6'>";
echo "<h5>📧 Email Marketing</h5>";
echo "<p>Include in newsletters:</p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "// Newsletter template\necho '<p>Special Deal: <a href=\"[AFFILIATE_URL]\">Product Name</a></p>';";
echo "</pre>";
echo "</div>";
echo "</div>";

echo "</div>";
echo "</div>";

// Click tracking function
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-warning text-dark'>";
echo "<h4>📈 Click Tracking Setup</h4>";
echo "</div>";
echo "<div class='card-body'>";

$tracking_script = '<?php
// Track affiliate clicks
function trackAffiliateClick($affiliate_id) {
    global $conn;
    
    // Update click count
    $update_query = "UPDATE affiliate_links SET clicks = clicks + 1 WHERE id = $affiliate_id";
    mysqli_query($conn, $update_query);
    
    // Update daily analytics
    $today = date("Y-m-d");
    $analytics_query = "INSERT INTO affiliate_analytics (affiliate_id, date, clicks) 
                        VALUES ($affiliate_id, \"$today\", 1) 
                        ON DUPLICATE KEY UPDATE clicks = clicks + 1";
    mysqli_query($conn, $analytics_query);
    
    // Get affiliate URL and redirect
    $url_query = "SELECT affiliate_url FROM affiliate_links WHERE id = $affiliate_id";
    $result = mysqli_query($conn, $url_query);
    $affiliate = mysqli_fetch_assoc($result);
    
    if ($affiliate) {
        header("Location: " . $affiliate["affiliate_url"]);
        exit();
    }
}

// Usage: trackAffiliateClick(1);
?>';

file_put_contents(__DIR__ . '/includes/affiliate_tracking.php', $tracking_script);
echo "<p class='text-success'>✅ Created affiliate tracking script</p>";

echo "<h5>Usage Example:</h5>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars("// Link to affiliate product\n<a href='go.php?affiliate_id=1' class='affiliate-link'>Buy Product</a>\n\n// go.php file content:\n<?php\nrequire_once 'includes/affiliate_tracking.php';\ntrackAffiliateClick(\$_GET['affiliate_id']);\n?>");
echo "</pre>";

echo "</div>";
echo "</div>";

echo "<div class='alert alert-success mt-4'>";
echo "<h5>💡 Top Affiliate Programs for Pakistan:</h5>";
echo "<ul>";
echo "<li><strong>Daraz:</strong> 5-10% commission on electronics, fashion, home goods</li>";
echo "<li><strong>Amazon:</strong> 4-8% commission on international products</li>";
echo "<li><strong>Hostinger:</strong> 60% commission on web hosting</li>";
echo "<li><strong>Foodpanda:</strong> 15% commission on food delivery</li>";
echo "<li><strong>Careem:</strong> 50% commission on first ride</li>";
echo "<li><strong>Jazz Cash:</strong> 25% commission on digital wallet signups</li>";
echo "<li><strong>GetYourGuide:</strong> 8% commission on tours and activities</li>";
echo "<li><strong>Telemart:</strong> 3-5% commission on electronics</li>";
echo "</ul>";
echo "</div>";

echo "<div class='mt-4'>";
echo "<a href='affiliate_manager.php' class='btn btn-primary btn-lg me-2'>";
echo "<i class='fas fa-link me-2'></i>Manage Affiliate Links</a>";
echo "<a href='advertising_rate_card.php' class='btn btn-secondary btn-lg'>";
echo "<i class='fas fa-file-invoice me-2'></i>View Rate Card</a>";
echo "</div>";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Affiliate Links System</title>
    <style>
        .table { margin-bottom: 0; }
        .btn { margin: 0; }
        .card { margin-bottom: 20px; }
        .card-header { padding: 15px; }
        .badge { font-size: 0.8em; }
        pre { white-space: pre-wrap; }
        .alert { padding: 15px; border-radius: 5px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; }
        .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <!-- page content here -->
</body>
</html>
