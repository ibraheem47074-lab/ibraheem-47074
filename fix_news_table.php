<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix News Table</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
<div class='container mt-5'>
    <h2 class='text-center mb-4'>Fix News Table</h2>";

try {
    // Use the existing connection from database.php
    if (!isset($conn)) {
        throw new Exception("Database connection not available");
    }

    echo "<div class='alert alert-info'>Connected to database successfully</div>";

    // Check if news table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'news'");
    if (mysqli_num_rows($result) > 0) {
        echo "<div class='alert alert-success'>
                <h4>â News table already exists!</h4>
              </div>";
        
        // Show table structure
        $structure = mysqli_query($conn, "DESCRIBE news");
        echo "<div class='table-responsive'>
                <table class='table table-striped'>
                    <thead class='table-dark'>
                        <tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>
                    </thead>
                    <tbody>";
        while ($row = mysqli_fetch_assoc($structure)) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['Field']) . "</td>
                    <td>" . htmlspecialchars($row['Type']) . "</td>
                    <td>" . htmlspecialchars($row['Null']) . "</td>
                    <td>" . htmlspecialchars($row['Key']) . "</td>
                  </tr>";
        }
        echo "</tbody></table></div>";
        
        // Show record count
        $count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
        $record_count = mysqli_fetch_assoc($count)['count'];
        echo "<div class='alert alert-info'>
                <h4>News Records: " . number_format($record_count) . "</h4>
              </div>";
        
    } else {
        echo "<div class='alert alert-danger'>
                <h4>â News table does not exist!</h4>
                <p>Creating news table now...</p>
              </div>";

        // Create news table
        $create_sql = "
        CREATE TABLE `news` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `content` text NOT NULL,
            `summary` text DEFAULT NULL,
            `category` varchar(50) DEFAULT NULL,
            `author` varchar(100) DEFAULT NULL,
            `image` varchar(255) DEFAULT NULL,
            `thumbnail` varchar(255) DEFAULT NULL,
            `video_url` varchar(255) DEFAULT NULL,
            `tags` text DEFAULT NULL,
            `status` enum('published','draft','pending') DEFAULT 'published',
            `featured` tinyint(1) DEFAULT 0,
            `breaking_news` tinyint(1) DEFAULT 0,
            `views` int(11) DEFAULT 0,
            `likes` int(11) DEFAULT 0,
            `comments_count` int(11) DEFAULT 0,
            `published_date` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_category` (`category`),
            KEY `idx_status` (`status`),
            KEY `idx_featured` (`featured`),
            KEY `idx_published_date` (`published_date`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (mysqli_query($conn, $create_sql)) {
            echo "<div class='alert alert-success'>
                    <h4>â News table created successfully!</h4>
                  </div>";
            
            // Add some sample data
            $sample_data = [
                [
                    'title' => 'Breaking News: Major Development in Local Politics',
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                    'summary' => 'A major political development has occurred that could change the landscape of local governance.',
                    'category' => 'politics',
                    'author' => 'Admin',
                    'status' => 'published',
                    'featured' => 1,
                    'breaking_news' => 1,
                    'published_date' => date('Y-m-d H:i:s')
                ],
                [
                    'title' => 'Sports Update: Local Team Wins Championship',
                    'content' => 'The local sports team has achieved a remarkable victory in the championship finals.',
                    'summary' => 'Celebrations erupt as local team brings home the championship trophy.',
                    'category' => 'sports',
                    'author' => 'Sports Reporter',
                    'status' => 'published',
                    'featured' => 0,
                    'breaking_news' => 0,
                    'published_date' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                ],
                [
                    'title' => 'Technology News: New Innovation Announced',
                    'content' => 'A groundbreaking technology has been announced that promises to revolutionize the industry.',
                    'summary' => 'Tech companies unveil new innovation that could change how we work and live.',
                    'category' => 'technology',
                    'author' => 'Tech Editor',
                    'status' => 'published',
                    'featured' => 0,
                    'breaking_news' => 0,
                    'published_date' => date('Y-m-d H:i:s', strtotime('-2 hours'))
                ]
            ];

            foreach ($sample_data as $news) {
                $insert_sql = "INSERT INTO news (title, content, summary, category, author, status, featured, breaking_news, published_date) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($stmt, 'sssssiiss', 
                    $news['title'], $news['content'], $news['summary'], 
                    $news['category'], $news['author'], $news['status'], 
                    $news['featured'], $news['breaking_news'], $news['published_date']
                );
                mysqli_stmt_execute($stmt);
            }

            echo "<div class='alert alert-info'>
                    <h4>â Added 3 sample news articles</h4>
                  </div>";
            
        } else {
            echo "<div class='alert alert-danger'>
                    <h4>â Error creating news table</h4>
                    <p>" . mysqli_error($conn) . "</p>
                  </div>";
        }
    }

    // Test the query that was failing
    echo "<div class='alert alert-info'>
            <h4>Testing the query that was failing...</h4>
          </div>";
    
    $test_query = "SELECT COUNT(*) as count FROM news";
    $result = mysqli_query($conn, $test_query);
    
    if ($result) {
        $count = mysqli_fetch_assoc($result)['count'];
        echo "<div class='alert alert-success'>
                <h4>â Query successful!</h4>
                <p>Total news records: " . number_format($count) . "</p>
              </div>";
    } else {
        echo "<div class='alert alert-danger'>
                <h4>â Query still failing</h4>
                <p>" . mysqli_error($conn) . "</p>
              </div>";
    }

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            <h4>â Error</h4>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}

echo "<div class='text-center mt-4'>
        <a href='admin/website_control.php' class='btn btn-success btn-lg me-2'>Test Admin Control Panel</a>
        <a href='check_database_status.php' class='btn btn-primary btn-lg me-2'>Check Database Status</a>
        <a href='index.php' class='btn btn-secondary btn-lg'>Back to Home</a>
      </div>";

echo "</div></body></html>";
?>
