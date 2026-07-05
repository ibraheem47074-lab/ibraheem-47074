<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get JSON data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Check if advanced_settings table exists, create if not
$create_table_query = "CREATE TABLE IF NOT EXISTS advanced_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    smart_recommendations BOOLEAN DEFAULT TRUE,
    learning_algorithm BOOLEAN DEFAULT TRUE,
    track_reading_time BOOLEAN DEFAULT FALSE,
    generate_reports BOOLEAN DEFAULT TRUE,
    quality_score INT DEFAULT 70,
    filter_duplicates BOOLEAN DEFAULT TRUE,
    morning_time TIME DEFAULT '08:00:00',
    evening_time TIME DEFAULT '18:00:00',
    reading_reminders BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
)";

mysqli_query($conn, $create_table_query);

// Update or insert advanced settings
$update_query = "INSERT INTO advanced_settings 
    (user_id, smart_recommendations, learning_algorithm, track_reading_time, 
     generate_reports, quality_score, filter_duplicates, morning_time, 
     evening_time, reading_reminders)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    smart_recommendations = VALUES(smart_recommendations),
    learning_algorithm = VALUES(learning_algorithm),
    track_reading_time = VALUES(track_reading_time),
    generate_reports = VALUES(generate_reports),
    quality_score = VALUES(quality_score),
    filter_duplicates = VALUES(filter_duplicates),
    morning_time = VALUES(morning_time),
    evening_time = VALUES(evening_time),
    reading_reminders = VALUES(reading_reminders),
    updated_at = CURRENT_TIMESTAMP";

$stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($stmt, 'iiiiiisssi', 
    $user_id,
    $data['smart_recommendations'],
    $data['learning_algorithm'], 
    $data['track_reading_time'],
    $data['generate_reports'],
    $data['quality_score'],
    $data['filter_duplicates'],
    $data['morning_time'],
    $data['evening_time'],
    $data['reading_reminders']
);

if (mysqli_stmt_execute($stmt)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Advanced criteria updated successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>
