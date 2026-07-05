<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

class BreakingNewsAlerts {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Subscribe to push notifications
    public function subscribe() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['endpoint']) || !isset($data['keys']['p256dh']) || !isset($data['keys']['auth'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required subscription data']);
            return;
        }
        
        $endpoint = $data['endpoint'];
        $p256dh_key = $data['keys']['p256dh'];
        $auth_key = $data['keys']['auth'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $device_type = $this->detectDeviceType($user_agent);
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        // Check if subscription already exists
        $check_query = "SELECT id FROM push_subscriptions WHERE endpoint = ?";
        $stmt = mysqli_prepare($this->conn, $check_query);
        mysqli_stmt_bind_param($stmt, 's', $endpoint);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            // Update existing subscription
            $subscription = mysqli_fetch_assoc($result);
            $subscription_id = $subscription['id'];
            
            $update_query = "UPDATE push_subscriptions SET p256dh_key = ?, auth_key = ?, user_agent = ?, 
                            ip_address = ?, device_type = ?, user_id = ?, is_active = TRUE, updated_at = CURRENT_TIMESTAMP 
                            WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $update_query);
            mysqli_stmt_bind_param($stmt, 'ssssisi', $p256dh_key, $auth_key, $user_agent, $ip_address, 
                                  $device_type, $user_id, $subscription_id);
        } else {
            // Insert new subscription
            $insert_query = "INSERT INTO push_subscriptions (endpoint, p256dh_key, auth_key, user_agent, 
                            ip_address, device_type, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($this->conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'ssssssi', $endpoint, $p256dh_key, $auth_key, $user_agent, 
                                  $ip_address, $device_type, $user_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            // Create default notification preferences
            if (!$subscription_id) {
                $subscription_id = mysqli_insert_id($this->conn);
            }
            
            $prefs_check = "SELECT id FROM notification_preferences WHERE subscription_id = ?";
            $prefs_stmt = mysqli_prepare($this->conn, $prefs_check);
            mysqli_stmt_bind_param($prefs_stmt, 'i', $subscription_id);
            mysqli_stmt_execute($prefs_stmt);
            $prefs_result = mysqli_stmt_get_result($prefs_stmt);
            
            if (mysqli_num_rows($prefs_result) === 0) {
                $insert_prefs = "INSERT INTO notification_preferences (subscription_id, breaking_news, category_alerts, min_priority) 
                               VALUES (?, TRUE, TRUE, 'medium')";
                $prefs_stmt = mysqli_prepare($this->conn, $insert_prefs);
                mysqli_stmt_bind_param($prefs_stmt, 'i', $subscription_id);
                mysqli_stmt_execute($prefs_stmt);
            }
            
            echo json_encode(['success' => true, 'message' => 'Subscription saved successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save subscription']);
        }
    }
    
    // Unsubscribe from push notifications
    public function unsubscribe() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['endpoint'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing endpoint']);
            return;
        }
        
        $endpoint = $data['endpoint'];
        $query = "UPDATE push_subscriptions SET is_active = FALSE WHERE endpoint = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $endpoint);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Unsubscribed successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to unsubscribe']);
        }
    }
    
    // Create breaking news alert
    public function createAlert() {
        if (!is_admin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['news_id']) || !isset($data['title']) || !isset($data['message'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        $news_id = $data['news_id'];
        $title = $data['title'];
        $message = $data['message'];
        $alert_type = $data['alert_type'] ?? 'breaking';
        $priority = $data['priority'] ?? 'high';
        $target_audience = $data['target_audience'] ?? 'all';
        $send_push = $data['send_push'] ?? true;
        $send_email = $data['send_email'] ?? false;
        $send_sms = $data['send_sms'] ?? false;
        $scheduled_at = $data['scheduled_at'] ?? null;
        
        $query = "INSERT INTO breaking_news_alerts (news_id, title, message, alert_type, priority, target_audience, 
                 send_push, send_email, send_sms, scheduled_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'isssssiiis', $news_id, $title, $message, $alert_type, $priority, 
                              $target_audience, $send_push, $send_email, $send_sms, $scheduled_at);
        
        if (mysqli_stmt_execute($stmt)) {
            $alert_id = mysqli_insert_id($this->conn);
            
            // Add category associations if provided
            if (isset($data['category_ids']) && is_array($data['category_ids'])) {
                foreach ($data['category_ids'] as $category_id) {
                    $cat_query = "INSERT INTO alert_categories (alert_id, category_id) VALUES (?, ?)";
                    $cat_stmt = mysqli_prepare($this->conn, $cat_query);
                    mysqli_stmt_bind_param($cat_stmt, 'ii', $alert_id, $category_id);
                    mysqli_stmt_execute($cat_stmt);
                }
            }
            
            // If no schedule, send immediately
            if (!$scheduled_at) {
                $this->sendAlert($alert_id);
            }
            
            echo json_encode(['success' => true, 'alert_id' => $alert_id, 'message' => 'Alert created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create alert']);
        }
    }
    
    // Send alert to subscribers
    public function sendAlert($alert_id) {
        // Get alert details
        $alert_query = "SELECT * FROM breaking_news_alerts WHERE id = ? AND status = 'pending'";
        $stmt = mysqli_prepare($this->conn, $alert_query);
        mysqli_stmt_bind_param($stmt, 'i', $alert_id);
        mysqli_stmt_execute($stmt);
        $alert = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        if (!$alert) {
            return false;
        }
        
        // Update alert status to sending
        $update_query = "UPDATE breaking_news_alerts SET status = 'sending' WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'i', $alert_id);
        mysqli_stmt_execute($stmt);
        
        // Get target subscriptions
        $subscriptions = $this->getTargetSubscriptions($alert);
        $total_sent = 0;
        $total_delivered = 0;
        $total_failed = 0;
        
        foreach ($subscriptions as $subscription) {
            $delivery_id = $this->logDelivery($alert_id, $subscription['id'], $subscription['user_id']);
            
            if ($alert['send_push']) {
                $result = $this->sendPushNotification($subscription, $alert);
                if ($result['success']) {
                    $total_delivered++;
                    $this->updateDeliveryStatus($delivery_id, 'delivered', null, json_encode($result));
                } else {
                    $total_failed++;
                    $this->updateDeliveryStatus($delivery_id, 'failed', $result['error'], null);
                }
                $total_sent++;
            }
        }
        
        // Update alert status
        $status = ($total_failed === 0) ? 'completed' : 'completed';
        $final_query = "UPDATE breaking_news_alerts SET status = ?, total_sent = ?, total_delivered = ?, 
                       total_failed = ?, sent_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $final_query);
        mysqli_stmt_bind_param($stmt, 'siiii', $status, $total_sent, $total_delivered, $total_failed, $alert_id);
        mysqli_stmt_execute($stmt);
        
        return true;
    }
    
    // Get target subscriptions based on alert criteria
    private function getTargetSubscriptions($alert) {
        $query = "SELECT s.*, np.breaking_news, np.min_priority, np.quiet_hours_start, np.quiet_hours_end 
                 FROM push_subscriptions s 
                 LEFT JOIN notification_preferences np ON s.id = np.subscription_id 
                 WHERE s.is_active = TRUE";
        
        $params = [];
        $types = '';
        
        // Filter by target audience
        if ($alert['target_audience'] === 'registered') {
            $query .= " AND s.user_id IS NOT NULL";
        } elseif ($alert['target_audience'] === 'subscribers') {
            $query .= " AND np.breaking_news = TRUE";
        }
        
        // Filter by priority preferences
        $priority_order = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
        $alert_priority = $priority_order[$alert['priority']] ?? 2;
        
        $query .= " AND (np.min_priority IS NULL OR 
                 CASE np.min_priority 
                     WHEN 'low' THEN 1 <= ?
                     WHEN 'medium' THEN 2 <= ?
                     WHEN 'high' THEN 3 <= ?
                     WHEN 'critical' THEN 4 <= ?
                 END)";
        
        for ($i = 0; $i < 4; $i++) {
            $params[] = $alert_priority;
            $types .= 'i';
        }
        
        // Exclude quiet hours (optional - can be disabled for breaking news)
        if ($alert['priority'] !== 'critical') {
            $current_time = date('H:i:s');
            $query .= " AND (np.quiet_hours_start IS NULL OR np.quiet_hours_end IS NULL OR 
                     NOT (np.quiet_hours_start <= ? AND np.quiet_hours_end >= ?))";
            $params[] = $current_time;
            $params[] = $current_time;
            $types .= 'ss';
        }
        
        $stmt = mysqli_prepare($this->conn, $query);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        
        return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    }
    
    // Send push notification using Web Push Protocol
    private function sendPushNotification($subscription, $alert) {
        // This would require a Web Push library like minishlink/web-push
        // For now, we'll simulate the response
        
        $payload = [
            'title' => $alert['title'],
            'body' => $alert['message'],
            'icon' => SITE_URL . 'assets/images/breaking-news-icon.png',
            'badge' => SITE_URL . 'assets/images/badge.png',
            'tag' => 'breaking-news-' . $alert['id'],
            'data' => [
                'news_id' => $alert['news_id'],
                'alert_id' => $alert['id'],
                'url' => SITE_URL . 'news.php?slug=' . $this->getNewsSlug($alert['news_id'])
            ],
            'actions' => [
                [
                    'action' => 'view',
                    'title' => 'View News'
                ],
                [
                    'action' => 'dismiss',
                    'title' => 'Dismiss'
                ]
            ]
        ];
        
        // Simulate successful push notification
        // In real implementation, you would use:
        // use Minishlink\WebPush\WebPush;
        // use Minishlink\WebPush\Subscription;
        
        return [
            'success' => true,
            'message' => 'Push notification sent successfully'
        ];
    }
    
    // Get news slug from news ID
    private function getNewsSlug($news_id) {
        $query = "SELECT slug FROM news WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        return $result['slug'] ?? '';
    }
    
    // Log delivery attempt
    private function logDelivery($alert_id, $subscription_id, $user_id) {
        $query = "INSERT INTO alert_delivery_log (alert_id, subscription_id, user_id) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'iii', $alert_id, $subscription_id, $user_id);
        mysqli_stmt_execute($stmt);
        return mysqli_insert_id($this->conn);
    }
    
    // Update delivery status
    private function updateDeliveryStatus($delivery_id, $status, $error_message, $response_data) {
        $query = "UPDATE alert_delivery_log SET delivery_status = ?, error_message = ?, response_data = ?, 
                 delivered_at = CASE WHEN ? IN ('delivered', 'clicked') THEN CURRENT_TIMESTAMP ELSE delivered_at END 
                 WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssi', $status, $error_message, $response_data, $status, $delivery_id);
        mysqli_stmt_execute($stmt);
    }
    
    // Detect device type from user agent
    private function detectDeviceType($user_agent) {
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $user_agent)) {
            return preg_match('/iPad/', $user_agent) ? 'tablet' : 'mobile';
        }
        return 'desktop';
    }
    
    // Get alert details
    public function getAlertDetails() {
        if (!is_admin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $alert_id = (int)($_GET['id'] ?? 0);
        
        if ($alert_id === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Alert ID is required']);
            return;
        }
        
        $query = "SELECT a.*, n.title as news_title, n.slug as news_slug 
                 FROM breaking_news_alerts a 
                 LEFT JOIN news n ON a.news_id = n.id 
                 WHERE a.id = ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $alert_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $alert = mysqli_fetch_assoc($result);
            echo json_encode(['success' => true, 'alert' => $alert]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Alert not found']);
        }
    }
    
    // Cancel alert
    public function cancelAlert() {
        if (!is_admin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $alert_id = (int)($_GET['id'] ?? 0);
        
        if ($alert_id === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Alert ID is required']);
            return;
        }
        
        $query = "UPDATE breaking_news_alerts SET status = 'cancelled' WHERE id = ? AND status = 'pending'";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $alert_id);
        
        if (mysqli_stmt_execute($stmt) && mysqli_affected_rows($this->conn) > 0) {
            echo json_encode(['success' => true, 'message' => 'Alert cancelled successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Alert not found or cannot be cancelled']);
        }
    }
    
    // Get alert statistics
    public function getAlertStats() {
        if (!is_admin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $query = "SELECT 
                 COUNT(*) as total_alerts,
                 SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                 SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                 SUM(total_sent) as total_sent,
                 SUM(total_delivered) as total_delivered,
                 SUM(total_failed) as total_failed
                 FROM breaking_news_alerts";
        
        $result = mysqli_query($this->conn, $query);
        $stats = mysqli_fetch_assoc($result);
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    }
    
    // Get recent alerts
    public function getRecentAlerts() {
        if (!is_admin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $query = "SELECT a.*, n.title as news_title, n.slug as news_slug 
                 FROM breaking_news_alerts a 
                 LEFT JOIN news n ON a.news_id = n.id 
                 ORDER BY a.created_at DESC LIMIT 10";
        
        $result = mysqli_query($this->conn, $query);
        $alerts = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'alerts' => $alerts]);
    }
}

// Handle API requests
$alerts = new BreakingNewsAlerts($conn);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        switch ($action) {
            case 'stats':
                $alerts->getAlertStats();
                break;
            case 'recent':
                $alerts->getRecentAlerts();
                break;
            case 'details':
                $alerts->getAlertDetails();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action not found']);
        }
        break;
    case 'POST':
        switch ($action) {
            case 'subscribe':
                $alerts->subscribe();
                break;
            case 'unsubscribe':
                $alerts->unsubscribe();
                break;
            case 'create':
                $alerts->createAlert();
                break;
            case 'cancel':
                $alerts->cancelAlert();
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
