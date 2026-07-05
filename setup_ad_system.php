<?php
require_once 'config/database.php';

// Create advertisements table
$sql = "CREATE TABLE IF NOT EXISTS advertisements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    ad_code TEXT NOT NULL,
    position ENUM('header', 'sidebar', 'footer', 'popup') NOT NULL,
    size VARCHAR(50) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    max_impressions INT DEFAULT NULL,
    current_impressions INT DEFAULT 0,
    click_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Advertisements table created successfully!<br>";
} else {
    echo "Error creating advertisements table: " . mysqli_error($conn) . "<br>";
}

// Create ad impressions table for tracking
$sql = "CREATE TABLE IF NOT EXISTS ad_impressions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    page_url VARCHAR(500),
    impression_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ad_id) REFERENCES advertisements(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Ad impressions table created successfully!<br>";
} else {
    echo "Error creating ad impressions table: " . mysqli_error($conn) . "<br>";
}

// Create ad clicks table for tracking
$sql = "CREATE TABLE IF NOT EXISTS ad_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    page_url VARCHAR(500),
    click_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ad_id) REFERENCES advertisements(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Ad clicks table created successfully!<br>";
} else {
    echo "Error creating ad clicks table: " . mysqli_error($conn) . "<br>";
}

// Insert sample ads
$sample_ads = [
    [
        'title' => 'Header Banner Ad',
        'ad_code' => '<a href="#" target="_blank"><img src="https://via.placeholder.com/728x90/FF6B6B/FFFFFF?text=Header+Banner+728x90" alt="Header Ad" style="width:100%; max-width:728px; height:90px;"></a>',
        'position' => 'header',
        'size' => '728x90'
    ],
    [
        'title' => 'Sidebar Rectangle Ad',
        'ad_code' => '<a href="#" target="_blank"><img src="https://via.placeholder.com/300x250/4ECDC4/FFFFFF?text=Sidebar+Ad+300x250" alt="Sidebar Ad" style="width:100%; max-width:300px; height:250px;"></a>',
        'position' => 'sidebar',
        'size' => '300x250'
    ],
    [
        'title' => 'Footer Banner Ad',
        'ad_code' => '<a href="#" target="_blank"><img src="https://via.placeholder.com/728x90/95E77E/333333?text=Footer+Banner+728x90" alt="Footer Ad" style="width:100%; max-width:728px; height:90px;"></a>',
        'position' => 'footer',
        'size' => '728x90'
    ],
    [
        'title' => 'Popup Modal Ad',
        'ad_code' => '<div style="text-align:center; padding:20px;"><h3>Special Offer!</h3><p>Get 20% off on premium subscription</p><a href="#" target="_blank" style="background:#FF6B6B;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">Learn More</a></div>',
        'position' => 'popup',
        'size' => '400x300'
    ]
];

foreach ($sample_ads as $ad) {
    $sql = "INSERT INTO advertisements (title, ad_code, position, size) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $ad['title'], $ad['ad_code'], $ad['position'], $ad['size']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Sample ad '{$ad['title']}' inserted successfully!<br>";
    } else {
        echo "Error inserting sample ad: " . mysqli_error($conn) . "<br>";
    }
}

echo "<br><strong>Ad system setup complete!</strong><br>";
echo "<a href='admin/manage-ads.php'>Go to Ad Management</a>";
?>
