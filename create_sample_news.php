<?php
require_once 'config/database.php';

echo "<h2>Creating Sample News Articles</h2>";

if (isset($conn) && $conn) {
    echo "<p style='color: green;'>✅ Database connected</p>";
    
    // Check if news table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news'");
    if (mysqli_num_rows($table_check) == 0) {
        echo "<p style='color: red;'>❌ News table doesn't exist. Creating it...</p>";
        
        // Create news table
        $create_table = "CREATE TABLE `news` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(500) NOT NULL,
            `slug` varchar(500) NOT NULL,
            `content` text NOT NULL,
            `excerpt` text,
            `image` varchar(500),
            `video_url` varchar(500),
            `category_id` int(11) DEFAULT NULL,
            `author_id` int(11) DEFAULT NULL,
            `status` enum('draft','published','featured','archived') DEFAULT 'draft',
            `is_breaking` tinyint(1) DEFAULT 0,
            `views` int(11) DEFAULT 0,
            `likes_count` int(11) DEFAULT 0,
            `comment_count` int(11) DEFAULT 0,
            `published_at` datetime DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `status` (`status`),
            KEY `published_at` (`published_at`),
            KEY `category_id` (`category_id`),
            KEY `author_id` (`author_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (mysqli_query($conn, $create_table)) {
            echo "<p style='color: green;'>✅ News table created</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating news table: " . mysqli_error($conn) . "</p>";
        }
    }
    
    // Check if categories table exists and has data
    $cat_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories");
    $cat_count = mysqli_fetch_assoc($cat_check)['count'];
    
    if ($cat_count == 0) {
        echo "<p style='color: orange;'>⚠️ No categories found. Creating sample categories...</p>";
        
        $categories = [
            ['Politics', 'politics', 'Political news and updates'],
            ['Sports', 'sports', 'Sports news and events'],
            ['Technology', 'technology', 'Technology and tech news'],
            ['Business', 'business', 'Business and economy'],
            ['Entertainment', 'entertainment', 'Entertainment news'],
            ['International', 'international', 'International news']
        ];
        
        foreach ($categories as $cat) {
            $slug = uniqid();
            $insert = "INSERT INTO categories (name, slug, description, status, created_at) VALUES (?, ?, ?, 'active', NOW())";
            $stmt = mysqli_prepare($conn, $insert);
            mysqli_stmt_bind_param($stmt, 'sss', $cat[0], $slug, $cat[2]);
            mysqli_stmt_execute($stmt);
        }
        echo "<p style='color: green;'>✅ Sample categories created</p>";
    }
    
    // Check if users table exists and has admin user
    $user_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $user_count = mysqli_fetch_assoc($user_check)['count'];
    
    if ($user_count == 0) {
        echo "<p style='color: orange;'>⚠️ No admin user found. Creating admin user...</p>";
        
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'admin', 'active', NOW())";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $password);
        $name = 'Admin User';
        $email = 'admin@pklivenews.com';
        mysqli_stmt_execute($stmt);
        echo "<p style='color: green;'>✅ Admin user created (admin@pklivenews.com / admin123)</p>";
    }
    
    // Get category and user IDs
    $category_result = mysqli_query($conn, "SELECT id, name FROM categories LIMIT 1");
    $category = mysqli_fetch_assoc($category_result);
    $category_id = $category['id'];
    
    $user_result = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $user = mysqli_fetch_assoc($user_result);
    $author_id = $user['id'];
    
    // Create sample news articles
    echo "<h3>Creating Sample News Articles</h3>";
    
    $sample_news = [
        [
            'title' => 'Breaking: Major Technology Breakthrough Announced',
            'content' => '<p>Scientists have announced a major breakthrough in technology that could change the way we live and work. The discovery, made after years of research, promises to revolutionize multiple industries.</p><p>Experts say this development could have far-reaching implications for the future of artificial intelligence and renewable energy.</p>',
            'excerpt' => 'Scientists announce major technology breakthrough with far-reaching implications.',
            'status' => 'featured',
            'is_breaking' => 1
        ],
        [
            'title' => 'Local Sports Team Wins Championship',
            'content' => '<p>The local sports team has won the championship in an exciting match that kept fans on the edge of their seats. The team showed exceptional skill and determination throughout the season.</p><p>Captain John Smith led the team to victory with a stunning performance in the final match.</p>',
            'excerpt' => 'Local team celebrates championship victory after thrilling final match.',
            'status' => 'published',
            'is_breaking' => 0
        ],
        [
            'title' => 'New Business Initiative Boosts Local Economy',
            'content' => '<p>A new business initiative has been launched to boost the local economy and create jobs for residents. The program, supported by local government and private investors, aims to stimulate economic growth.</p><p>Business leaders have expressed optimism about the initiative\'s potential to create sustainable employment opportunities.</p>',
            'excerpt' => 'New business initiative promises economic growth and job creation.',
            'status' => 'published',
            'is_breaking' => 0
        ],
        [
            'title' => 'Entertainment Industry Adapts to Digital Age',
            'content' => '<p>The entertainment industry is rapidly adapting to the digital age with new technologies and platforms emerging. Streaming services and digital content creation are reshaping how we consume entertainment.</p><p>Industry experts discuss the challenges and opportunities presented by this digital transformation.</p>',
            'excerpt' => 'Entertainment industry embraces digital transformation with new technologies.',
            'status' => 'published',
            'is_breaking' => 0
        ],
        [
            'title' => 'International Summit Addresses Global Challenges',
            'content' => '<p>World leaders gathered at an international summit to address pressing global challenges including climate change, economic inequality, and international cooperation.</p><p>The summit resulted in several agreements and commitments to work together on these critical issues.</p>',
            'excerpt' => 'World leaders commit to cooperation on global challenges at international summit.',
            'status' => 'published',
            'is_breaking' => 0
        ]
    ];
    
    $created_count = 0;
    foreach ($sample_news as $news) {
        // Generate unique slug
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $news['title'])) . '-' . time() . rand(100, 999);
        
        $insert = "INSERT INTO news (title, slug, content, excerpt, category_id, author_id, status, is_breaking, published_at, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, 'ssssiisi', 
            $news['title'], 
            $slug, 
            $news['content'], 
            $news['excerpt'], 
            $category_id, 
            $author_id, 
            $news['status'], 
            $news['is_breaking']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $created_count++;
            echo "<p style='color: green;'>✅ Created: " . htmlspecialchars($news['title']) . "</p>";
        }
    }
    
    echo "<h3>Summary</h3>";
    echo "<p><strong>Sample news articles created:</strong> $created_count</p>";
    
    // Verify creation
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status IN ('published', 'featured')");
    $total = mysqli_fetch_assoc($result)['count'];
    echo "<p><strong>Total published/featured articles:</strong> $total</p>";
    
    echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Homepage</a></p>";
    
} else {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
}
?>
