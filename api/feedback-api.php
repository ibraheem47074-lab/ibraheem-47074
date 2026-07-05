<?php
require_once '../config/database.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_feedback':
            $deployment_id = (int)($_GET['deployment_id'] ?? 0);
            
            $query = "SELECT f.*, d.name as deployment_name, u.name as user_name
                      FROM feedback f 
                      LEFT JOIN deployments d ON f.deployment_id = d.id
                      LEFT JOIN users u ON f.user_id = u.id
                      WHERE f.deployment_id = ? 
                      ORDER BY f.created_at DESC";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $deployment_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $feedback_data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $feedback_data[] = [
                    'id' => $row['id'],
                    'deployment_name' => $row['deployment_name'],
                    'user_name' => $row['user_name'],
                    'rating' => $row['rating'],
                    'feedback_type' => $row['feedback_type'],
                    'status' => $row['status'],
                    'priority' => $row['priority'],
                    'feedback_text' => $row['feedback_text'],
                    'created_at' => $row['created_at']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $feedback_data
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'submit_feedback':
            $deployment_id = $data['deployment_id'] ?? 0;
            $user_id = $data['user_id'] ?? null;
            $feedback_type = $data['feedback_type'] ?? 'general';
            $rating = $data['rating'] ?? 5;
            $feedback_text = $data['feedback_text'] ?? '';
            $priority = $data['priority'] ?? 'medium';
            
            // Insert feedback
            $query = "INSERT INTO feedback (deployment_id, user_id, feedback_type, rating, feedback_text, priority, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'iissss', $deployment_id, $user_id, $feedback_type, $rating, $feedback_text, $priority);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Feedback submitted successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error submitting feedback'
                ]);
            }
            break;
            
        case 'export_feedback':
            $deployment_id = $data['deployment_id'] ?? 0;
            $format = $data['format'] ?? 'csv';
            
            // Get feedback data
            $query = "SELECT f.*, d.name as deployment_name, u.name as user_name
                      FROM feedback f 
                      LEFT JOIN deployments d ON f.deployment_id = d.id
                      LEFT JOIN users u ON f.user_id = u.id
                      WHERE f.deployment_id = ? 
                      ORDER BY f.created_at DESC";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $deployment_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $feedback_data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $feedback_data[] = $row;
            }
            
            if ($format === 'csv') {
                $filename = 'feedback_export_' . date('Y-m-d') . '.csv';
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                
                $output = fopen('php://output', 'w');
                
                // CSV header
                fputcsv($output, [
                    'ID', 'Deployment', 'User', 'Rating', 'Type', 'Status', 'Priority', 'Feedback Text', 'Created At'
                ]);
                
                // CSV data
                foreach ($feedback_data as $row) {
                    fputcsv($output, [
                        $row['id'],
                        $row['deployment_name'],
                        $row['user_name'],
                        $row['rating'],
                        $row['feedback_type'],
                        $row['status'],
                        $row['priority'],
                        substr($row['feedback_text'] ?? '', 0, 100),
                        $row['created_at']
                    ]);
                }
                
                fclose($output);
                readfile('php://output');
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Export completed'
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
}
?>
