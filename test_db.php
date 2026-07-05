<?php
// Test database connection and create tables
echo "<h2>Database Test & Setup</h2>";

try {
    require_once 'config/database.php';
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    echo "<p>Database: " . DB_NAME . "</p>";
    echo "<p>Host: " . DB_HOST . "</p>";
    
    // Check if channels table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'channels'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✓ Channels table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Channels table does not exist - Creating now...</p>";
        
        // Create channels table
        $sql = "CREATE TABLE channels (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            category enum('news','sports','entertainment','business','technology','international') NOT NULL DEFAULT 'news',
            stream_url text,
            stream_type enum('youtube','hls','rtmp','iframe') NOT NULL DEFAULT 'youtube',
            thumbnail varchar(500),
            description text,
            status enum('live','offline','scheduled') NOT NULL DEFAULT 'offline',
            viewer_count int(11) DEFAULT 0,
            language varchar(10) DEFAULT 'en',
            country varchar(50) DEFAULT 'PK',
            sort_order int(11) DEFAULT 0,
            is_featured tinyint(1) DEFAULT 0,
            schedule_time datetime NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (mysqli_query($conn, $sql)) {
            echo "<p style='color: green;'>✓ Channels table created successfully</p>";
            
            // Insert sample data
            $insert_sql = "INSERT INTO channels (name, category, stream_url, stream_type, description, status, is_featured) VALUES 
                ('PK News Live', 'news', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', '24/7 breaking news and current affairs coverage', 'live', 1),
                ('Sports Central', 'sports', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'Live sports coverage and analysis', 'live', 1),
                ('Entertainment Tonight', 'entertainment', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'Celebrity news and entertainment updates', 'offline', 0)";
            
            if (mysqli_query($conn, $insert_sql)) {
                echo "<p style='color: green;'>✓ Sample data inserted</p>";
            } else {
                echo "<p style='color: red;'>✗ Error inserting data: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Error creating table: " . mysqli_error($conn) . "</p>";
        }
    }
    
    // Create live_chat table
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'live_chat'");
    if (mysqli_num_rows($result) == 0) {
        echo "<p>Creating live_chat table...</p>";
        $sql = "CREATE TABLE live_chat (
            id int(11) NOT NULL AUTO_INCREMENT,
            channel_id int(11) NOT NULL,
            username varchar(100) NOT NULL,
            message text NOT NULL,
            timestamp timestamp DEFAULT CURRENT_TIMESTAMP,
            is_deleted tinyint(1) DEFAULT 0,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (mysqli_query($conn, $sql)) {
            echo "<p style='color: green;'>✓ Live chat table created</p>";
        } else {
            echo "<p style='color: red;'>✗ Error creating live_chat table: " . mysqli_error($conn) . "</p>";
        }
    }
    
    // Create channel_schedule table
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'channel_schedule'");
    if (mysqli_num_rows($result) == 0) {
        echo "<p>Creating channel_schedule table...</p>";
        $sql = "CREATE TABLE channel_schedule (
            id int(11) NOT NULL AUTO_INCREMENT,
            channel_id int(11) NOT NULL,
            program_title varchar(255) NOT NULL,
            description text,
            start_time datetime NOT NULL,
            end_time datetime NOT NULL,
            is_recurring tinyint(1) DEFAULT 0,
            recurring_days varchar(20) NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY channel_id (channel_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (mysqli_query($conn, $sql)) {
            echo "<p style='color: green;'>✓ Channel schedule table created</p>";
            
            // Insert sample schedule
            $schedule_sql = "INSERT INTO channel_schedule (channel_id, program_title, description, start_time, end_time, is_recurring, recurring_days) VALUES 
                (1, 'Morning News Bulletin', 'Start your day with comprehensive news coverage', '2026-03-19 07:00:00', '2026-03-19 09:00:00', 1, '1,2,3,4,5'),
                (1, 'Breaking News Live', 'Real-time coverage of developing stories', '2026-03-19 14:00:00', '2026-03-19 16:00:00', 1, '1,2,3,4,5'),
                (2, 'Sports Highlights', 'Daily sports recap and analysis', '2026-03-19 22:00:00', '2026-03-19 23:00:00', 1, '1,2,3,4,5,6,7')";
            
            if (mysqli_query($conn, $schedule_sql)) {
                echo "<p style='color: green;'>✓ Sample schedule data added</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Sample schedule data not added: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Error creating channel_schedule table: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Channel schedule table already exists</p>";
    }
    
    echo "<hr>";
    echo "<h3>Test Complete!</h3>";
    echo "<p><a href='live.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Live TV</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}
?>
