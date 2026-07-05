<?php
require_once 'config/database.php';

echo "<h2>Adding Sample Affiliate Products...</h2>";

// Sample products data - Limited to 4 products as requested
$sample_products = [
    [
        'title' => 'iPhone 15 Pro Max',
        'slug' => 'iphone-15-pro-max',
        'description' => 'The latest iPhone with A17 Pro chip, titanium design, and advanced camera system. Features include 6.7-inch Super Retina XDR display, 5G connectivity, and all-day battery life.',
        'short_description' => 'Apple iPhone 15 Pro Max with advanced camera system',
        'price' => 1199.00,
        'original_price' => 1299.00,
        'currency' => 'USD',
        'image_url' => 'https://via.placeholder.com/400x400/000000/FFFFFF?text=iPhone+15+Pro+Max',
        'affiliate_url' => 'https://amazon.com/dp/B0CHX2TJQX',
        'affiliate_network' => 'amazon',
        'category_id' => 2, // Mobile Phones
        'rating' => 4.8,
        'review_count' => 15420,
        'availability' => 'in_stock',
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro Max',
        'tags' => 'smartphone, apple, iphone, 5g, camera',
        'featured' => 1,
        'status' => 'active'
    ],
    [
        'title' => 'Samsung Galaxy S24 Ultra',
        'slug' => 'samsung-galaxy-s24-ultra',
        'description' => 'Samsung\'s flagship smartphone with S Pen, 200MP camera, and AI features. Includes 6.8-inch Dynamic AMOLED 2X display, Snapdragon 8 Gen 3 processor, and 5000mAh battery.',
        'short_description' => 'Samsung Galaxy S24 Ultra with S Pen and AI features',
        'price' => 1099.00,
        'original_price' => 1299.00,
        'currency' => 'USD',
        'image_url' => 'https://via.placeholder.com/400x400/1f77b4/FFFFFF?text=Galaxy+S24+Ultra',
        'affiliate_url' => 'https://amazon.com/dp/B0BPXVQ44W',
        'affiliate_network' => 'amazon',
        'category_id' => 2, // Mobile Phones
        'rating' => 4.7,
        'review_count' => 12350,
        'availability' => 'in_stock',
        'brand' => 'Samsung',
        'model' => 'Galaxy S24 Ultra',
        'tags' => 'smartphone, samsung, android, 5g, s-pen',
        'featured' => 1,
        'status' => 'active'
    ],
    [
        'title' => 'MacBook Pro 14-inch',
        'slug' => 'macbook-pro-14-inch',
        'description' => 'Apple MacBook Pro with M3 Pro chip, 14.2-inch Liquid Retina XDR display, and 18-hour battery life. Perfect for professionals and content creators.',
        'short_description' => 'Apple MacBook Pro 14" with M3 Pro chip',
        'price' => 1999.00,
        'original_price' => 2199.00,
        'currency' => 'USD',
        'image_url' => 'https://via.placeholder.com/400x400/666666/FFFFFF?text=MacBook+Pro+14',
        'affiliate_url' => 'https://amazon.com/dp/B0CHX1Q1JX',
        'affiliate_network' => 'amazon',
        'category_id' => 3, // Laptops
        'rating' => 4.9,
        'review_count' => 8765,
        'availability' => 'in_stock',
        'brand' => 'Apple',
        'model' => 'MacBook Pro 14"',
        'tags' => 'laptop, apple, macbook, m3-pro, professional',
        'featured' => 1,
        'status' => 'active'
    ],
    [
        'title' => 'Sony WH-1000XM5 Headphones',
        'slug' => 'sony-wh-1000xm5-headphones',
        'description' => 'Industry-leading noise canceling headphones with exceptional sound quality, 30-hour battery life, and multipoint connectivity.',
        'short_description' => 'Sony WH-1000XM5 wireless noise canceling headphones',
        'price' => 349.00,
        'original_price' => 399.00,
        'currency' => 'USD',
        'image_url' => 'https://via.placeholder.com/400x400/000000/FFFFFF?text=Sony+WH-1000XM5',
        'affiliate_url' => 'https://amazon.com/dp/B097TYWD5K',
        'affiliate_network' => 'amazon',
        'category_id' => 6, // Audio
        'rating' => 4.6,
        'review_count' => 23450,
        'availability' => 'in_stock',
        'brand' => 'Sony',
        'model' => 'WH-1000XM5',
        'tags' => 'headphones, sony, wireless, noise-canceling, bluetooth',
        'featured' => 0,
        'status' => 'active'
    ]
];

$success_count = 0;
$error_count = 0;

foreach ($sample_products as $product) {
    // Check if product already exists
    $check_query = "SELECT id FROM affiliate_products WHERE slug = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, 's', $product['slug']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: orange;'>⚠️ Product '{$product['title']}' already exists</p>";
        continue;
    }
    
    // Insert product
    $insert_query = "INSERT INTO affiliate_products (
        title, slug, description, short_description, price, original_price, 
        currency, image_url, affiliate_url, affiliate_network, category_id, 
        rating, review_count, availability, brand, model, tags, featured, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "sssddsssssiissssiss", 
        $product['title'], $product['slug'], $product['description'], $product['short_description'], 
        $product['price'], $product['original_price'], $product['currency'], $product['image_url'], 
        $product['affiliate_url'], $product['affiliate_network'], $product['category_id'], 
        $product['rating'], $product['review_count'], $product['availability'], $product['brand'], 
        $product['model'], $product['tags'], $product['featured'], $product['status']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✅ Added: {$product['title']}</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>❌ Error adding '{$product['title']}': " . mysqli_error($conn) . "</p>";
        $error_count++;
    }
}

echo "<h3>Summary:</h3>";
echo "<p><strong>Successfully added:</strong> $success_count products</p>";
echo "<p><strong>Errors:</strong> $error_count products</p>";

if ($success_count > 0) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎉 Sample Products Added Successfully!</h4>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li><a href='products.php' target='_blank'>View Products Page</a></li>";
    echo "<li><a href='admin/manage-affiliate-products.php' target='_blank'>Manage Products in Admin</a></li>";
    echo "<li><a href='index.php' target='_blank'>See Products on Homepage</a></li>";
    echo "</ul>";
    echo "</div>";
}

echo "<p><a href='index.php'>← Back to Home</a></p>";
?>
