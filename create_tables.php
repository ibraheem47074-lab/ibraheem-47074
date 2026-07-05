<?php
// Simple database setup script
require_once 'config/database.php';

echo "<h2>Creating Database Tables...</h2>";

// Create channels table
$sql1 = "CREATE TABLE IF NOT EXISTS channels (
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
    PRIMARY KEY (id),
    KEY category (category),
    KEY status (status),
    KEY is_featured (is_featured)
)";

if (mysqli_query($conn, $sql1)) {
    echo "<p style='color: green;'>✓ Channels table created</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating channels table: " . mysqli_error($conn) . "</p>";
}

// Create live_chat table
$sql2 = "CREATE TABLE IF NOT EXISTS live_chat (
    id int(11) NOT NULL AUTO_INCREMENT,
    channel_id int(11) NOT NULL,
    username varchar(100) NOT NULL,
    message text NOT NULL,
    timestamp timestamp DEFAULT CURRENT_TIMESTAMP,
    is_deleted tinyint(1) DEFAULT 0,
    PRIMARY KEY (id),
    KEY channel_id (channel_id)
)";

if (mysqli_query($conn, $sql2)) {
    echo "<p style='color: green;'>✓ Live chat table created</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating live chat table: " . mysqli_error($conn) . "</p>";
}

// Create channel_schedule table
$sql3 = "CREATE TABLE IF NOT EXISTS channel_schedule (
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
)";

if (mysqli_query($conn, $sql3)) {
    echo "<p style='color: green;'>✓ Channel schedule table created</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating channel schedule table: " . mysqli_error($conn) . "</p>";
}

// Insert sample data
$insert_sql = "INSERT IGNORE INTO channels (name, category, stream_url, stream_type, description, status, is_featured) VALUES
('PK News Live', 'news', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', '24/7 breaking news and current affairs coverage', 'live', 1),
('Sports Central', 'sports', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'Live sports coverage and analysis', 'live', 1),
('Entertainment Tonight', 'entertainment', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'Celebrity news and entertainment updates', 'offline', 0)";

if (mysqli_query($conn, $insert_sql)) {
    echo "<p style='color: green;'>✓ Sample channels added</p>";
} else {
    echo "<p style='color: red;'>✗ Error adding sample channels: " . mysqli_error($conn) . "</p>";
}

echo "<hr>";
echo "<h3>Setup Complete!</h3>";
echo "<p><a href='live.php'>Go to Live TV Page</a></p>";
echo "<p><a href='admin/manage-channels.php'>Manage Channels</a></p>";
?>
