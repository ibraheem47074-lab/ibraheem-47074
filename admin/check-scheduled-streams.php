<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$response = [
    'started_streams' => [],
    'message' => ''
];

try {
    // Get scheduled streams that should start now
    $current_time = date('Y-m-d H:i:s');
    
    $query = "SELECT * FROM live_stream 
              WHERE status = 'scheduled' AND schedule_time <= ? AND auto_start = 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $current_time);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $started_streams = [];
    
    while ($stream = mysqli_fetch_assoc($result)) {
        // Update stream status to online
        $update_query = "UPDATE live_stream SET status = 'online' WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'i', $stream['id']);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $started_streams[] = [
                'id' => $stream['id'],
                'title' => $stream['title'],
                'started_at' => $current_time
            ];
        }
    }
    
    // Check for streams that should end
    $end_query = "SELECT * FROM live_stream 
                 WHERE status = 'online' AND end_time <= ?";
    $end_stmt = mysqli_prepare($conn, $end_query);
    mysqli_stmt_bind_param($end_stmt, 's', $current_time);
    mysqli_stmt_execute($end_stmt);
    $end_result = mysqli_stmt_get_result($end_stmt);
    
    $ended_streams = [];
    
    while ($stream = mysqli_fetch_assoc($end_result)) {
        // Update stream status to offline
        $end_update_query = "UPDATE live_stream SET status = 'offline' WHERE id = ?";
        $end_update_stmt = mysqli_prepare($conn, $end_update_query);
        mysqli_stmt_bind_param($end_update_stmt, 'i', $stream['id']);
        
        if (mysqli_stmt_execute($end_update_stmt)) {
            $ended_streams[] = [
                'id' => $stream['id'],
                'title' => $stream['title'],
                'ended_at' => $current_time
            ];
        }
    }
    
    $response = [
        'success' => true,
        'started_streams' => $started_streams,
        'ended_streams' => $ended_streams,
        'message' => 'Checked scheduled streams',
        'current_time' => $current_time
    ];
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ];
}

echo json_encode($response);
?>
