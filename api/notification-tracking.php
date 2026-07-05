<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

class NotificationTracking {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function trackEvent() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['type']) || !isset($data['notification_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required tracking data']);
            return;
        }
        
        $type = $data['type']; // delivery, click, dismiss
        $notification_id = $data['notification_id'];
        $timestamp = $data['timestamp'] ?? time();
        $user_agent = $data['user_agent'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Find the delivery log entry
        $query = "SELECT id, user_id, subscription_id FROM alert_delivery_log 
                 WHERE alert_id = ? AND delivery_status IN ('pending', 'sent', 'delivered', 'clicked') 
                 ORDER BY created_at DESC LIMIT 1";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $notification_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $log_entry = mysqli_fetch_assoc($result);
            $delivery_id = $log_entry['id'];
            
            // Update the delivery log based on event type
            $response_json = json_encode($data);
            switch ($type) {
                case 'delivery':
                    $this->updateDeliveryStatus($delivery_id, 'delivered', null, $response_json);
                    break;
                case 'click':
                    $this->updateDeliveryStatus($delivery_id, 'clicked', null, $response_json);
                    break;
                case 'dismiss':
                    // Keep as delivered but log the dismiss
                    $this->updateDeliveryStatus($delivery_id, 'delivered', null, $response_json);
                    break;
            }
            
            echo json_encode(['success' => true, 'message' => 'Tracking data recorded']);
        } else {
            // Create a new log entry if not found
            $insert_query = "INSERT INTO alert_delivery_log (alert_id, subscription_id, user_id, delivery_status, response_data, delivered_at) 
                           VALUES (?, NULL, NULL, ?, ?, FROM_UNIXTIME(?))";
            $stmt = mysqli_prepare($this->conn, $insert_query);
            $status = $type === 'delivery' ? 'delivered' : 'delivered';
            $response_json = json_encode($data);
            mysqli_stmt_bind_param($stmt, 'issi', $notification_id, $status, $response_json, $timestamp);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Tracking data recorded']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to record tracking data']);
            }
        }
    }
    
    private function updateDeliveryStatus($delivery_id, $status, $error_message, $response_data) {
        $query = "UPDATE alert_delivery_log SET delivery_status = ?, error_message = ?, response_data = ?";
        
        if ($status === 'clicked') {
            $query .= ", clicked_at = CURRENT_TIMESTAMP";
        } elseif ($status === 'delivered') {
            $query .= ", delivered_at = CURRENT_TIMESTAMP";
        }
        
        $query .= " WHERE id = ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssi', $status, $error_message, $response_data, $delivery_id);
        return mysqli_stmt_execute($stmt);
    }
    
    public function getTrackingStats() {
        if (!is_admin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $alert_id = $_GET['alert_id'] ?? null;
        
        if ($alert_id) {
            // Stats for specific alert
            $query = "SELECT 
                     COUNT(*) as total,
                     SUM(CASE WHEN delivery_status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                     SUM(CASE WHEN delivery_status = 'clicked' THEN 1 ELSE 0 END) as clicked,
                     SUM(CASE WHEN delivery_status = 'failed' THEN 1 ELSE 0 END) as failed
                     FROM alert_delivery_log WHERE alert_id = ?";
            
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $alert_id);
            mysqli_stmt_execute($stmt);
            $stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            
            // Calculate rates
            $stats['delivery_rate'] = $stats['total'] > 0 ? round(($stats['delivered'] / $stats['total']) * 100, 2) : 0;
            $stats['click_rate'] = $stats['delivered'] > 0 ? round(($stats['clicked'] / $stats['delivered']) * 100, 2) : 0;
            
        } else {
            // Overall stats
            $query = "SELECT 
                     COUNT(DISTINCT adl.alert_id) as total_alerts,
                     COUNT(adl.id) as total_deliveries,
                     SUM(CASE WHEN adl.delivery_status = 'delivered' THEN 1 ELSE 0 END) as total_delivered,
                     SUM(CASE WHEN adl.delivery_status = 'clicked' THEN 1 ELSE 0 END) as total_clicked,
                     SUM(CASE WHEN adl.delivery_status = 'failed' THEN 1 ELSE 0 END) as total_failed
                     FROM alert_delivery_log adl";
            
            $result = mysqli_query($this->conn, $query);
            $stats = mysqli_fetch_assoc($result);
            
            $stats['overall_delivery_rate'] = $stats['total_deliveries'] > 0 ? 
                round(($stats['total_delivered'] / $stats['total_deliveries']) * 100, 2) : 0;
            $stats['overall_click_rate'] = $stats['total_delivered'] > 0 ? 
                round(($stats['total_clicked'] / $stats['total_delivered']) * 100, 2) : 0;
        }
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    }
}

// Handle API requests
$tracking = new NotificationTracking($conn);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'POST':
        $tracking->trackEvent();
        break;
    case 'GET':
        switch ($action) {
            case 'stats':
                $tracking->getTrackingStats();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action not found']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
