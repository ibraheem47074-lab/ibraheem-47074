<?php
echo "<h2>Fixing Database Connection...</h2>";

// Test 1: Connect to MySQL server without database
echo "<h3>Step 1: Testing MySQL Server Connection</h3>";
$conn = @mysqli_connect('localhost', 'root', '');

if (!$conn) {
    echo "❌ Cannot connect to MySQL server<br>";
    echo "Error: " . mysqli_connect_error() . "<br>";
    
    // Try with common passwords
    $passwords = ['', 'root', 'password', 'admin', 'mysql'];
    foreach ($passwords as $pass) {
        $test_conn = @mysqli_connect('localhost', 'root', $pass);
        if ($test_conn) {
            echo "✅ Found working password: '$pass'<br>";
            echo "<script>alert('MySQL password is: $pass');</script>";
            break;
        }
    }
    die("Please check MySQL credentials");
} else {
    echo "✅ Connected to MySQL server successfully<br>";
}

// Test 2: Check if database exists
echo "<h3>Step 2: Checking Database</h3>";
$result = mysqli_query($conn, "SHOW DATABASES LIKE 'pk_live_news'");
if ($result && $result->num_rows > 0) {
    echo "✅ Database 'pk_live_news' exists<br>";
} else {
    echo "❌ Database 'pk_live_news' does not exist. Creating it...<br>";
    
    $sql = "CREATE DATABASE IF NOT EXISTS pk_live_news CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Database created successfully<br>";
    } else {
        echo "❌ Error creating database: " . mysqli_error($conn) . "<br>";
    }
}

// Test 3: Test full connection with database
echo "<h3>Step 3: Testing Full Database Connection</h3>";
mysqli_close($conn);
$conn = @mysqli_connect('localhost', 'root', '', 'pk_live_news');

if (!$conn) {
    echo "❌ Cannot connect to database: " . mysqli_connect_error() . "<br>";
} else {
    echo "✅ Successfully connected to database 'pk_live_news'<br>";
}

// Test 4: Check if users table exists
echo "<h3>Step 4: Checking Users Table</h3>";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if ($result && $result->num_rows > 0) {
    echo "✅ Users table exists<br>";
    
    // Check if admin user exists
    $admin_check = mysqli_query($conn, "SELECT id FROM users WHERE email = 'admin@pklivenews.com'");
    if ($admin_check && mysqli_num_rows($admin_check) > 0) {
        echo "✅ Admin user exists<br>";
    } else {
        echo "❌ Admin user missing. Creating...<br>";
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`) VALUES ('Admin', 'admin@pklivenews.com', '$admin_password', 'admin', 'active')";
        if (mysqli_query($conn, $insert_admin)) {
            echo "✅ Admin user created<br>";
        }
    }
} else {
    echo "❌ Users table missing. Creating...<br>";
    
    $users_sql = "
    CREATE TABLE `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `password` varchar(255) NOT NULL,
        `role` enum('admin','editor','reporter') DEFAULT 'reporter',
        `status` enum('active','blocked') DEFAULT 'active',
        `bio` text DEFAULT NULL,
        `image` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if (mysqli_query($conn, $users_sql)) {
        echo "✅ Users table created<br>";
        
        // Create admin user
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`) VALUES ('Admin', 'admin@pklivenews.com', '$admin_password', 'admin', 'active')";
        if (mysqli_query($conn, $insert_admin)) {
            echo "✅ Admin user created<br>";
        }
    }
}

mysqli_close($conn);

echo "<h3>✅ Database Setup Complete!</h3>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li><a href='admin/login.php'>Go to Admin Login</a></li>";
echo "<li>Login with: admin@pklivenews.com / admin123</li>";
echo "</ul>";
?>
