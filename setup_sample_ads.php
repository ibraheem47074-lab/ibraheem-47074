<?php
require_once 'config/database.php';

echo "<h2>Setting Up Sample Advertisements</h2>";

// Check if table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'advertisements'");
$table_exists = mysqli_num_rows($table_check) > 0;

if ($table_exists) {
    echo "<p class='text-info'>ℹ Advertisements table already exists. Checking structure...</p>";
    
    // Check if redirect_url column exists
    $column_check = mysqli_query($conn, "SHOW COLUMNS FROM advertisements LIKE 'redirect_url'");
    $column_exists = mysqli_num_rows($column_check) > 0;
    
    if (!$column_exists) {
        // Add missing column
        $alter_table = "ALTER TABLE advertisements ADD COLUMN redirect_url VARCHAR(500) AFTER image";
        if (mysqli_query($conn, $alter_table)) {
            echo "<p class='text-success'>✓ Added redirect_url column</p>";
        } else {
            echo "<p class='text-danger'>✗ Error adding column: " . mysqli_error($conn) . "</p>";
        }
    }
} else {
    echo "<p class='text-info'>ℹ Creating new advertisements table...</p>";
    
    // Create advertisements table
    $create_table = "CREATE TABLE advertisements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        position ENUM('header', 'sidebar', 'footer', 'all') DEFAULT 'sidebar',
        image VARCHAR(500),
        redirect_url VARCHAR(500),
        code TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        start_date DATE DEFAULT NULL,
        end_date DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if (mysqli_query($conn, $create_table)) {
        echo "<p class='text-success'>✓ Advertisements table created successfully</p>";
    } else {
        echo "<p class='text-danger'>✗ Error creating table: " . mysqli_error($conn) . "</p>";
    }
}

// Sample ads data
$sample_ads = [
    [
        'title' => 'Sample Business Ad - Sidebar',
        'position' => 'sidebar',
        'image' => 'uploads/ads/69adaaa0ab59c.jpg',
        'redirect_url' => 'https://example-business.com',
        'status' => 'active'
    ],
    [
        'title' => 'Tech Store Promotion',
        'position' => 'header',
        'image' => 'uploads/ads/69adaaa0ab59c.jpg',
        'redirect_url' => 'https://techstore.example',
        'status' => 'active'
    ],
    [
        'title' => 'Local Services Ad',
        'position' => 'footer',
        'image' => 'uploads/ads/69adaaa0ab59c.jpg',
        'redirect_url' => 'https://localservices.example',
        'status' => 'active'
    ],
    [
        'title' => 'Restaurant Special Offer',
        'position' => 'sidebar',
        'image' => 'uploads/ads/69adaaa0ab59c.jpg',
        'redirect_url' => 'https://restaurant.example',
        'status' => 'active'
    ],
    [
        'title' => 'E-commerce Banner',
        'position' => 'all',
        'image' => 'uploads/ads/69adaaa0ab59c.jpg',
        'redirect_url' => 'https://shop.example',
        'status' => 'active'
    ]
];

// Insert sample ads
foreach ($sample_ads as $ad) {
    $title = mysqli_real_escape_string($conn, $ad['title']);
    $position = $ad['position'];
    $image = $ad['image'];
    $redirect_url = $ad['redirect_url'];
    $status = $ad['status'];
    
    $insert_query = "INSERT INTO advertisements (title, position, image, redirect_url, status, start_date, end_date) 
                     VALUES ('$title', '$position', '$image', '$redirect_url', '$status', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY))";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "<p class='text-success'>✓ Added: {$ad['title']}</p>";
    } else {
        echo "<p class='text-danger'>✗ Error adding {$ad['title']}: " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3 class='mt-4'>Setup Complete!</h3>";
echo "<p>You can now manage these sample ads in the <a href='admin/manage-ads.php'>Admin Panel</a></p>";
echo "<p><a href='index.php' class='btn btn-primary'>View Website</a> | <a href='admin/manage-ads.php' class='btn btn-secondary'>Manage Ads</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
.btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; }
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
</style>
