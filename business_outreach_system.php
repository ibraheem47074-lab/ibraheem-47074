<?php
require_once 'config/database.php';

echo "<h2>Local Business Advertising Outreach</h2>";

// Create business outreach tracking table
$create_outreach_table = "CREATE TABLE IF NOT EXISTS business_outreach (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    business_type VARCHAR(100),
    status ENUM('prospect', 'contacted', 'interested', 'negotiating', 'closed', 'not_interested') DEFAULT 'prospect',
    last_contact_date DATE,
    follow_up_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $create_outreach_table)) {
    echo "<p class='text-success'>✅ Business outreach tracking table created</p>";
}

// Sample local businesses to contact
$local_businesses = [
    [
        'name' => 'Karachi Electronics Market',
        'contact' => 'Ahmed Hassan',
        'email' => 'info@karachielectronics.com',
        'phone' => '+92-21-34567890',
        'type' => 'Electronics Retail',
        'notes' => 'Large electronics retailer with multiple locations in Karachi'
    ],
    [
        'name' => 'Lahore Food Street Restaurant',
        'contact' => 'Muhammad Ali',
        'email' => 'manager@lahorefoodstreet.com',
        'phone' => '+92-42-37654321',
        'type' => 'Restaurant/Food',
        'notes' => 'Popular restaurant chain in Lahore, expanding to other cities'
    ],
    [
        'name' => 'Pak Tech Solutions',
        'contact' => 'Sara Khan',
        'email' => 'sales@paktechsolutions.com',
        'phone' => '+92-51-23456789',
        'type' => 'Technology Services',
        'notes' => 'IT services company targeting corporate clients'
    ],
    [
        'name' => 'Islamabad Fashion Boutique',
        'contact' => 'Fatima Sheikh',
        'email' => 'contact@islamabadboutique.com',
        'phone' => '+92-51-87654321',
        'type' => 'Fashion/Retail',
        'notes' => 'High-end fashion boutique with online presence'
    ],
    [
        'name' => 'Peshawar Auto Dealers',
        'contact' => 'Khalid Rahman',
        'email' => 'info@peshawarauto.com',
        'phone' => '+92-91-98765432',
        'type' => 'Automotive',
        'notes' => 'Car dealership with new and used vehicles'
    ]
];

// Insert sample businesses
foreach ($local_businesses as $business) {
    $name = mysqli_real_escape_string($conn, $business['name']);
    $contact = mysqli_real_escape_string($conn, $business['contact']);
    $email = mysqli_real_escape_string($conn, $business['email']);
    $phone = mysqli_real_escape_string($conn, $business['phone']);
    $type = mysqli_real_escape_string($conn, $business['type']);
    $notes = mysqli_real_escape_string($conn, $business['notes']);
    
    $check_query = "SELECT id FROM business_outreach WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $insert_query = "INSERT INTO business_outreach (business_name, contact_person, email, phone, business_type, notes) 
                         VALUES ('$name', '$contact', '$email', '$phone', '$type', '$notes')";
        mysqli_query($conn, $insert_query);
    }
}

echo "<p class='text-success'>✅ Added " . count($local_businesses) . " sample businesses to contact list</p>";

// Email templates
$email_templates = [
    'initial_contact' => [
        'subject' => 'Advertising Opportunity with PK Live News - Reach Thousands of Pakistani Readers',
        'body' => 'Dear [Contact Person],

I hope this email finds you well. My name is [Your Name] from PK Live News, one of Pakistan\'s fastest-growing news platforms.

I\'m reaching out because I believe [Business Name] could greatly benefit from advertising on our platform. Here\'s why:

📈 **Our Reach:**
- 50,000+ monthly readers across Pakistan
- 75% of our audience from major cities (Karachi, Lahore, Islamabad)
- Strong engagement with local news and business content

🎯 **Target Audience:**
- Educated professionals (25-55 age group)
- High purchasing power
- Actively seeking local products and services

💰 **Advertising Options:**
- Sidebar Banner Ads: Starting from Rs. 15,000/month
- Featured Business Spotlights: Rs. 25,000/month
- Sponsored Articles: Rs. 35,000/article
- Special packages available for long-term partnerships

I\'d love to schedule a brief 15-minute call to discuss how we can help [Business Name] reach more customers in your target market.

Would you be available sometime next week?

Best regards,
[Your Name]
Advertising Manager
PK Live News
📱: +92-XXX-XXXXXXX
🌐: www.pklivenews.com

---
P.S. I\'d be happy to send you our media kit with detailed audience demographics and pricing information.'
    ],
    
    'follow_up' => [
        'subject' => 'Following Up - Advertising Opportunity with PK Live News',
        'body' => 'Dear [Contact Person],

I hope you\'re having a great week. I\'m following up on my previous email regarding advertising opportunities with PK Live News.

I wanted to share a quick success story: Similar businesses in [Business Type] have seen up to 40% increase in customer inquiries after advertising with us.

🚀 **Limited Time Offer:**
- 20% discount on your first month
- Free banner design worth Rs. 10,000
- Performance guarantee - if you don\'t see results, we\'ll extend your campaign free

Are you available for a quick call this week? I can show you exactly how our platform can help [Business Name] grow.

Looking forward to connecting!

Best regards,
[Your Name]
Advertising Manager
PK Live News'
    ],
    
    'special_offer' => [
        'subject' => 'Exclusive Ramadan Advertising Package - PK Live News',
        'body' => 'Dear [Contact Person],

Ramadan is just around the corner, and I wanted to extend an exclusive advertising opportunity to [Business Name].

🌙 **Ramadan Special Package:**
- 4 weeks of premium advertising
- Featured in our Ramadan Business Directory
- Social media promotion included
- Special Ramadan content sponsorship

**Total Value: Rs. 100,000**
**Special Price: Rs. 75,000** (25% discount)

This package is perfect for reaching Pakistani families during their highest spending period of the year.

Slots are limited to only 10 businesses per category to ensure maximum visibility for our partners.

Would you like to reserve your spot before it\'s too late?

Ramadan Mubarak!
[Your Name]
Advertising Manager
PK Live News'
    ]
];

echo "<div class='card mt-4'>";
echo "<div class='card-header bg-primary text-white'>";
echo "<h4>📧 Email Templates</h4>";
echo "</div>";
echo "<div class='card-body'>";

foreach ($email_templates as $template_name => $template) {
    echo "<div class='template-section mb-4'>";
    echo "<h5>" . ucfirst(str_replace('_', ' ', $template_name)) . "</h5>";
    echo "<div class='template-preview'>";
    echo "<strong>Subject:</strong> " . htmlspecialchars($template['subject']) . "<br><br>";
    echo "<strong>Body:</strong><br>";
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;'>" . htmlspecialchars($template['body']) . "</pre>";
    echo "</div>";
    echo "</div>";
}

echo "</div>";
echo "</div>";

// Outreach tracking dashboard
echo "<div class='card mt-4'>";
echo "<div class='card-header bg-success text-white'>";
echo "<h4>📊 Business Outreach Dashboard</h4>";
echo "</div>";
echo "<div class='card-body'>";

// Get outreach statistics
$stats_query = "SELECT 
    COUNT(*) as total_businesses,
    SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted,
    SUM(CASE WHEN status = 'interested' THEN 1 ELSE 0 END) as interested,
    SUM(CASE WHEN status = 'negotiating' THEN 1 ELSE 0 END) as negotiating,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
    FROM business_outreach";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

echo "<div class='row text-center'>";
echo "<div class='col-md-2'>";
echo "<h3>{$stats['total_businesses']}</h3>";
echo "<small>Total Businesses</small>";
echo "</div>";
echo "<div class='col-md-2'>";
echo "<h3>{$stats['contacted']}</h3>";
echo "<small>Contacted</small>";
echo "</div>";
echo "<div class='col-md-2'>";
echo "<h3>{$stats['interested']}</h3>";
echo "<small>Interested</small>";
echo "</div>";
echo "<div class='col-md-2'>";
echo "<h3>{$stats['negotiating']}</h3>";
echo "<small>Negotiating</small>";
echo "</div>";
echo "<div class='col-md-2'>";
echo "<h3>{$stats['closed']}</h3>";
echo "<small>Closed Deals</small>";
echo "</div>";
echo "<div class='col-md-2'>";
echo "<h3>" . round(($stats['closed'] / max($stats['total_businesses'], 1)) * 100, 1) . "%</h3>";
echo "<small>Success Rate</small>";
echo "</div>";
echo "</div>";

echo "</div>";
echo "</div>";

// Action items
echo "<div class='alert alert-warning mt-4'>";
echo "<h5>🎯 Your Action Plan:</h5>";
echo "<ol>";
echo "<li><strong>Today:</strong> Send initial contact emails to all 5 businesses</li>";
echo "<li><strong>Tomorrow:</strong> Follow up with businesses that opened emails</li>";
echo "<li><strong>Week 2:</strong> Schedule calls with interested businesses</li>";
echo "<li><strong>Week 3:</strong> Send proposals and negotiate terms</li>";
echo "<li><strong>Week 4:</strong> Close first advertising deals</li>";
echo "</ol>";
echo "</div>";

echo "<div class='mt-4'>";
echo "<a href='business_outreach_manager.php' class='btn btn-primary btn-lg me-2'>";
echo "<i class='fas fa-users me-2'></i>Manage Business Outreach</a>";
echo "<a href='advertising_rate_card.php' class='btn btn-secondary btn-lg'>";
echo "<i class='fas fa-file-invoice me-2'></i>View Rate Card</a>";
echo "</div>";
?>

<style>
.template-preview { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
.template-section { margin-bottom: 20px; }
.card { margin-bottom: 20px; }
.card-header { padding: 15px; }
.btn { padding: 12px 24px; text-decoration: none; border-radius: 5px; }
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.alert { padding: 15px; border-radius: 5px; }
.alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
</style>
