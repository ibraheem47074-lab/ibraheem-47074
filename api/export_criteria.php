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

// Get user's profile data
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user_data = mysqli_fetch_assoc($user_result);

// Get bookmark settings
$bookmark_query = "SELECT * FROM bookmark_settings WHERE user_id = ?";
$bookmark_stmt = mysqli_prepare($conn, $bookmark_query);
mysqli_stmt_bind_param($bookmark_stmt, 'i', $user_id);
mysqli_stmt_execute($bookmark_stmt);
$bookmark_result = mysqli_stmt_get_result($bookmark_stmt);
$bookmark_settings = mysqli_fetch_assoc($bookmark_result);

// Get advanced settings
$advanced_query = "SELECT * FROM advanced_settings WHERE user_id = ?";
$advanced_stmt = mysqli_prepare($conn, $advanced_query);
mysqli_stmt_bind_param($advanced_stmt, 'i', $user_id);
mysqli_stmt_execute($advanced_stmt);
$advanced_result = mysqli_stmt_get_result($advanced_stmt);
$advanced_settings = mysqli_fetch_assoc($advanced_result);

// Get events settings
$events_query = "SELECT * FROM events_criteria WHERE user_id = ?";
$events_stmt = mysqli_prepare($conn, $events_query);
mysqli_stmt_bind_param($events_stmt, 'i', $user_id);
mysqli_stmt_execute($events_stmt);
$events_result = mysqli_stmt_get_result($events_stmt);
$events_settings = mysqli_fetch_assoc($events_result);

// Create export data
$export_data = [
    'export_date' => date('Y-m-d H:i:s'),
    'user_id' => $user_id,
    'user_name' => $user_data['name'],
    'email' => $user_data['email'],
    'profile_criteria' => [
        'email_notifications' => $user_data['email_notifications'] ?? false,
        'push_notifications' => $user_data['push_notifications'] ?? false,
        'newsletter_subscription' => $user_data['newsletter_subscription'] ?? false,
        'profile_public' => $user_data['profile_public'] ?? false,
        'show_activity' => $user_data['show_activity'] ?? false,
        'preferred_categories' => $user_data['preferred_categories'] ?? '',
        'language_preference' => $user_data['language_preference'] ?? 'en',
        'theme' => $user_data['theme'] ?? 'light',
        'timezone' => $user_data['timezone'] ?? 'UTC'
    ],
    'bookmark_criteria' => $bookmark_settings ?: [
        'auto_bookmark_read' => false,
        'auto_bookmark_liked' => false,
        'auto_bookmark_commented' => false,
        'notify_bookmark_reminder' => true,
        'notify_bookmark_full' => false,
        'notify_bookmark_digest' => false,
        'public_bookmarks' => false,
        'share_bookmarks' => false
    ],
    'advanced_criteria' => $advanced_settings ?: [
        'smart_recommendations' => true,
        'learning_algorithm' => true,
        'track_reading_time' => false,
        'generate_reports' => true,
        'quality_score' => 70,
        'filter_duplicates' => true,
        'morning_time' => '08:00:00',
        'evening_time' => '18:00:00',
        'reading_reminders' => false
    ],
    'events_criteria' => $events_settings ?: [
        'preferred_categories' => '',
        'preferred_types' => '',
        'min_priority' => 'low',
        'notification_advance_days' => 7,
        'notification_advance_hours' => 2,
        'email_notifications' => true,
        'push_notifications' => true,
        'show_past_events' => false,
        'show_cancelled_events' => false,
        'max_events_per_day' => 10,
        'auto_register' => false,
        'only_free_events' => false,
        'location_filter' => '',
        'organizer_filter' => '',
        'tags_filter' => ''
    ]
];

// Create JSON file
$filename = 'criteria_export_' . date('Y-m-d_H-i-s') . '.json';
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo json_encode($export_data, JSON_PRETTY_PRINT);
?>
