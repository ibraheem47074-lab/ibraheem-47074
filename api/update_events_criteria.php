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

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get user's events criteria
        $query = "SELECT * FROM events_criteria WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $criteria = mysqli_fetch_assoc($result);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'criteria' => $criteria]);
        break;
        
    case 'POST':
        // Update or insert events criteria
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        
        if (!$data) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }
        
        // Check if events_criteria table exists, create if not
        $create_table_query = "CREATE TABLE IF NOT EXISTS events_criteria (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            preferred_categories VARCHAR(500) DEFAULT NULL,
            preferred_types VARCHAR(500) DEFAULT NULL,
            min_priority ENUM('low','medium','high','urgent') DEFAULT 'low',
            notification_advance_days INT DEFAULT 7,
            notification_advance_hours INT DEFAULT 2,
            email_notifications BOOLEAN DEFAULT 1,
            push_notifications BOOLEAN DEFAULT 1,
            show_past_events BOOLEAN DEFAULT 0,
            show_cancelled_events BOOLEAN DEFAULT 0,
            max_events_per_day INT DEFAULT 10,
            auto_register BOOLEAN DEFAULT 0,
            only_free_events BOOLEAN DEFAULT 0,
            location_filter VARCHAR(255) DEFAULT NULL,
            organizer_filter VARCHAR(500) DEFAULT NULL,
            tags_filter VARCHAR(500) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user (user_id)
        )";
        
        mysqli_query($conn, $create_table_query);
        
        // Update or insert events criteria
        $update_query = "INSERT INTO events_criteria 
            (user_id, preferred_categories, preferred_types, min_priority, 
             notification_advance_days, notification_advance_hours, email_notifications, 
             push_notifications, show_past_events, show_cancelled_events, 
             max_events_per_day, auto_register, only_free_events, location_filter, 
             organizer_filter, tags_filter)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            preferred_categories = VALUES(preferred_categories),
            preferred_types = VALUES(preferred_types),
            min_priority = VALUES(min_priority),
            notification_advance_days = VALUES(notification_advance_days),
            notification_advance_hours = VALUES(notification_advance_hours),
            email_notifications = VALUES(email_notifications),
            push_notifications = VALUES(push_notifications),
            show_past_events = VALUES(show_past_events),
            show_cancelled_events = VALUES(show_cancelled_events),
            max_events_per_day = VALUES(max_events_per_day),
            auto_register = VALUES(auto_register),
            only_free_events = VALUES(only_free_events),
            location_filter = VALUES(location_filter),
            organizer_filter = VALUES(organizer_filter),
            tags_filter = VALUES(tags_filter),
            updated_at = CURRENT_TIMESTAMP";
        
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'isssiiiiiiiiisss', 
            $user_id,
            $data['preferred_categories'],
            $data['preferred_types'],
            $data['min_priority'],
            $data['notification_advance_days'],
            $data['notification_advance_hours'],
            $data['email_notifications'],
            $data['push_notifications'],
            $data['show_past_events'],
            $data['show_cancelled_events'],
            $data['max_events_per_day'],
            $data['auto_register'],
            $data['only_free_events'],
            $data['location_filter'],
            $data['organizer_filter'],
            $data['tags_filter']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Events criteria updated successfully']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        }
        break;
        
    case 'DELETE':
        // Reset events criteria to defaults
        $delete_query = "DELETE FROM events_criteria WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Events criteria reset to defaults']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        }
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
