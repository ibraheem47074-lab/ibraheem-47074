<?php
// Database Setup Script for Live Streaming System
// Run this file to create all necessary tables

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Setup - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>PK Live News - Database Setup</h1>
        <div class='card'>
            <div class='card-body'>
                <h2>Creating Live Streaming Tables...</h2>";

// Function to execute SQL and show result
function executeSQL($sql, $successMessage, $errorMessage) {
    global $conn;
    try {
        if (mysqli_query($conn, $sql)) {
            echo "<p class='success'>✓ " . $successMessage . "</p>";
            return true;
        } else {
            echo "<p class='error'>✗ " . $errorMessage . ": " . mysqli_error($conn) . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ " . $errorMessage . ": " . $e->getMessage() . "</p>";
        return false;
    }
}

// Create channels table
$channels_sql = "
CREATE TABLE IF NOT EXISTS `channels` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `category` enum('news','sports','entertainment','business','technology','international') NOT NULL DEFAULT 'news',
    `stream_url` text,
    `stream_type` enum('youtube','hls','rtmp','iframe') NOT NULL DEFAULT 'youtube',
    `thumbnail` varchar(500),
    `description` text,
    `status` enum('live','offline','scheduled') NOT NULL DEFAULT 'offline',
    `viewer_count` int(11) DEFAULT 0,
    `language` varchar(10) DEFAULT 'en',
    `country` varchar(50) DEFAULT 'PK',
    `sort_order` int(11) DEFAULT 0,
    `is_featured` tinyint(1) DEFAULT 0,
    `schedule_time` datetime NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `category` (`category`),
    KEY `status` (`status`),
    KEY `is_featured` (`is_featured`),
    KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

executeSQL($channels_sql, "Channels table created successfully", "Error creating channels table");

// Create live_chat table
$chat_sql = "
CREATE TABLE IF NOT EXISTS `live_chat` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `channel_id` int(11) NOT NULL,
    `username` varchar(100) NOT NULL,
    `message` text NOT NULL,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
    `is_deleted` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `channel_id` (`channel_id`),
    KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

executeSQL($chat_sql, "Live chat table created successfully", "Error creating live chat table");

// Create channel_schedule table
$schedule_sql = "
CREATE TABLE IF NOT EXISTS `channel_schedule` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `channel_id` int(11) NOT NULL,
    `program_title` varchar(255) NOT NULL,
    `description` text,
    `start_time` datetime NOT NULL,
    `end_time` datetime NOT NULL,
    `is_recurring` tinyint(1) DEFAULT 0,
    `recurring_days` varchar(20) NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `channel_id` (`channel_id`),
    KEY `start_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

executeSQL($schedule_sql, "Channel schedule table created successfully", "Error creating channel schedule table");

// Insert sample channels if they don't exist
$sample_channels = [
    ['PK News Live', 'news', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/pk-news-live.jpg', '24/7 breaking news and current affairs coverage', 'live', 1],
    ['Sports Central', 'sports', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/sports-central.jpg', 'Live sports coverage and analysis', 'live', 1],
    ['Entertainment Tonight', 'entertainment', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/entertainment-tonight.jpg', 'Celebrity news and entertainment updates', 'offline', 0],
    ['Business Daily', 'business', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/business-daily.jpg', 'Market updates and business news', 'scheduled', 0],
    ['Tech Talk', 'technology', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/tech-talk.jpg', 'Latest technology news and reviews', 'offline', 0],
    ['World Report', 'international', 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 'uploads/channels/world-report.jpg', 'International news and analysis', 'live', 0]
];

foreach ($sample_channels as $channel) {
    $check_sql = "SELECT id FROM channels WHERE name = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, 's', $channel[0]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $insert_sql = "INSERT INTO channels (name, category, stream_url, stream_type, thumbnail, description, status, is_featured) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, 'ssssssii', $channel[0], $channel[1], $channel[2], $channel[3], $channel[4], $channel[5], $channel[6], $channel[7]);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p class='success'>✓ Added sample channel: " . htmlspecialchars($channel[0]) . "</p>";
        } else {
            echo "<p class='error'>✗ Error adding channel " . htmlspecialchars($channel[0]) . ": " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p class='info'>ℹ Channel already exists: " . htmlspecialchars($channel[0]) . "</p>";
    }
}

// Insert sample schedule
$sample_schedule = [
    [1, 'Morning News Bulletin', 'Start your day with comprehensive news coverage', '2026-03-19 07:00:00', '2026-03-19 09:00:00', 1, '1,2,3,4,5'],
    [1, 'Breaking News Live', 'Real-time coverage of developing stories', '2026-03-19 14:00:00', '2026-03-19 16:00:00', 1, '1,2,3,4,5'],
    [1, 'Evening News Wrap', 'Complete roundup of the day\'s events', '2026-03-19 20:00:00', '2026-03-19 21:00:00', 1, '1,2,3,4,5'],
    [2, 'Live Cricket Match', 'Coverage of today\'s cricket match', '2026-03-19 15:00:00', '2026-03-19 19:00:00', 0, null],
    [2, 'Sports Highlights', 'Daily sports recap and analysis', '2026-03-19 22:00:00', '2026-03-19 23:00:00', 1, '1,2,3,4,5,6,7']
];

foreach ($sample_schedule as $schedule) {
    $insert_sql = "INSERT INTO channel_schedule (channel_id, program_title, description, start_time, end_time, is_recurring, recurring_days) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, 'issssii', $schedule[0], $schedule[1], $schedule[2], $schedule[3], $schedule[4], $schedule[5], $schedule[6]);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p class='success'>✓ Added schedule: " . htmlspecialchars($schedule[1]) . "</p>";
    } else {
        echo "<p class='error'>✗ Error adding schedule: " . mysqli_error($conn) . "</p>";
    }
}

echo "
                <hr>
                <h3>Setup Complete!</h3>
                <div class='alert alert-success'>
                    <strong>Success!</strong> Your live streaming database has been set up successfully.
                </div>
                <div class='mt-3'>
                    <a href='live.php' class='btn btn-danger me-2'>
                        <i class='fas fa-tv me-2'></i>Go to Live TV
                    </a>
                    <a href='admin/manage-channels.php' class='btn btn-primary me-2'>
                        <i class='fas fa-cog me-2'></i>Manage Channels
                    </a>
                    <a href='index.php' class='btn btn-secondary'>
                        <i class='fas fa-home me-2'></i>Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?>
