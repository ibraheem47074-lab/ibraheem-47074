<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

class MobileNotifications {
    private $conn;
    private $fcmServerKey; // Replace with your FCM server key
    
    public function __construct($conn) {
        $this->conn = $conn;
        // In production, store this securely in environment variables
        $this->fcmServerKey = 'YOUR_FCM_SERVER_KEY_HERE';
    }
    
    // Register mobile device token
    public function registerDevice() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['token']) || !isset($data['platform'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing token or platform']);
            return;
        }
        
        $token = $data['token'];
        $platform = $data['platform']; // android, ios
        $device_id = $data['device_id'] ?? null;
        $app_version = $data['app_version'] ?? null;
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        // Check if token already exists
        $check_query = "SELECT id FROM mobile_devices WHERE token = ?";
        $stmt = mysqli_prepare($this->conn, $check_query);
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            // Update existing device
            $device = mysqli_fetch_assoc($result);
            $device_id_db = $device['id'];
            
            $update_query = "UPDATE mobile_devices SET platform = ?, device_id = ?, app_version = ?, 
                            user_id = ?, is_active = TRUE, last_seen = CURRENT_TIMESTAMP 
                            WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $update_query);
            mysqli_stmt_bind_param($stmt, 'ssssi', $platform, $device_id, $app_version, $user_id, $device_id_db);
        } else {
            // Insert new device
            $insert_query = "INSERT INTO mobile_devices (token, platform, device_id, app_version, user_id) 
                            VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($this->conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'ssssi', $token, $platform, $device_id, $app_version, $user_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Device registered successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to register device']);
        }
    }
    
    // Unregister mobile device
    public function unregisterDevice() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['token'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing token']);
            return;
        }
        
        $token = $data['token'];
        $query = "UPDATE mobile_devices SET is_active = FALSE WHERE token = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $token);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Device unregistered successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to unregister device']);
        }
    }
    
    // Send push notification to mobile devices
    public function sendMobileNotification($alert_id, $news_data) {
        // Get alert details
        $alert_query = "SELECT * FROM breaking_news_alerts WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $alert_query);
        mysqli_stmt_bind_param($stmt, 'i', $alert_id);
        mysqli_stmt_execute($stmt);
        $alert = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        if (!$alert) {
            return false;
        }
        
        // Get target devices
        $devices = $this->getTargetDevices($alert);
        
        if (empty($devices)) {
            return true; // No devices to send to
        }
        
        // Prepare notification payload
        $notification = [
            'title' => $alert['title'],
            'body' => $alert['message'],
            'sound' => 'default',
            'badge' => 1,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'data' => [
                'alert_id' => $alert_id,
                'news_id' => $alert['news_id'],
                'type' => 'breaking_news',
                'url' => SITE_URL . 'news.php?slug=' . $news_data['slug'],
                'image_url' => $news_data['image'] ?? ''
            ]
        ];
        
        // Group devices by platform for optimized sending
        $android_devices = array_filter($devices, fn($d) => $d['platform'] === 'android');
        $ios_devices = array_filter($devices, fn($d) => $d['platform'] === 'ios');
        
        $success_count = 0;
        $failure_count = 0;
        
        // Send to Android devices
        if (!empty($android_devices)) {
            $result = $this->sendFCMNotification($android_devices, $notification, 'android');
            $success_count += $result['success'];
            $failure_count += $result['failure'];
        }
        
        // Send to iOS devices
        if (!empty($ios_devices)) {
            $ios_notification = $notification;
            $ios_notification['notification'] = [
                'title' => $notification['title'],
                'body' => $notification['body'],
                'sound' => 'default',
                'badge' => 1
            ];
            
            $result = $this->sendFCMNotification($ios_devices, $ios_notification, 'ios');
            $success_count += $result['success'];
            $failure_count += $result['failure'];
        }
        
        // Log mobile delivery
        $this->logMobileDelivery($alert_id, $success_count, $failure_count);
        
        return $failure_count === 0;
    }
    
    // Get target mobile devices based on alert criteria
    private function getTargetDevices($alert) {
        $query = "SELECT d.*, np.breaking_news, np.min_priority, np.quiet_hours_start, np.quiet_hours_end 
                 FROM mobile_devices d 
                 LEFT JOIN notification_preferences np ON d.id = np.device_id 
                 WHERE d.is_active = TRUE";
        
        $params = [];
        $types = '';
        
        // Filter by target audience
        if ($alert['target_audience'] === 'registered') {
            $query .= " AND d.user_id IS NOT NULL";
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
    
    // Send notification via Firebase Cloud Messaging
    private function sendFCMNotification($devices, $notification, $platform) {
        $device_tokens = [];
        foreach ($devices as $device) {
            $device_tokens[] = $device['token'];
        }
        
        // FCM payload
        $payload = [
            'registration_ids' => $device_tokens,
            'notification' => $notification['notification'] ?? [
                'title' => $notification['title'],
                'body' => $notification['body'],
                'sound' => 'default',
                'badge' => 1
            ],
            'data' => $notification['data'],
            'priority' => 'high',
            'content_available' => true,
            'mutable_content' => true
        ];
        
        // Platform-specific adjustments
        if ($platform === 'android') {
            $payload['android'] = [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'breaking_news',
                    'icon' => '@mipmap/ic_notification',
                    'color' => '#dc3545',
                    'sound' => 'breaking_news'
                ]
            ];
        } elseif ($platform === 'ios') {
            $payload['apns'] = [
                'headers' => [
                    'apns-priority' => '10'
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $notification['title'],
                            'body' => $notification['body']
                        ],
                        'sound' => 'default',
                        'badge' => 1,
                        'category' => 'BREAKING_NEWS'
                    ]
                ]
            ];
        }
        
        // Send to FCM
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . $this->fcmServerKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            return [
                'success' => $result['success'] ?? 0,
                'failure' => $result['failure'] ?? 0,
                'results' => $result['results'] ?? []
            ];
        } else {
            return [
                'success' => 0,
                'failure' => count($device_tokens),
                'error' => 'FCM request failed: ' . $http_code
            ];
        }
    }
    
    // Log mobile delivery results
    private function logMobileDelivery($alert_id, $success_count, $failure_count) {
        $devices_sent = $success_count + $failure_count;
        $query = "INSERT INTO mobile_delivery_log (alert_id, devices_sent, devices_delivered, devices_failed, sent_at) 
                 VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'iiii', $alert_id, $devices_sent, $success_count, $failure_count);
        mysqli_stmt_execute($stmt);
    }
    
    // Get mobile notification statistics
    public function getMobileStats() {
        if (!is_admin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
            return;
        }
        
        $query = "SELECT 
                 COUNT(*) as total_devices,
                 SUM(CASE WHEN platform = 'android' THEN 1 ELSE 0 END) as android_devices,
                 SUM(CASE WHEN platform = 'ios' THEN 1 ELSE 0 END) as ios_devices,
                 SUM(CASE WHEN is_active = TRUE THEN 1 ELSE 0 END) as active_devices
                 FROM mobile_devices";
        
        $result = mysqli_query($this->conn, $query);
        $device_stats = mysqli_fetch_assoc($result);
        
        // Get delivery stats
        $delivery_query = "SELECT 
                          COUNT(*) as total_campaigns,
                          SUM(devices_sent) as total_sent,
                          SUM(devices_delivered) as total_delivered,
                          SUM(devices_failed) as total_failed
                          FROM mobile_delivery_log";
        
        $delivery_result = mysqli_query($this->conn, $delivery_query);
        $delivery_stats = mysqli_fetch_assoc($delivery_result);
        
        $stats = array_merge($device_stats, $delivery_stats);
        
        if ($stats['total_sent'] > 0) {
            $stats['delivery_rate'] = round(($stats['total_delivered'] / $stats['total_sent']) * 100, 2);
        } else {
            $stats['delivery_rate'] = 0;
        }
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    }
}

// Handle API requests
$mobile = new MobileNotifications($conn);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'POST':
        switch ($action) {
            case 'register':
                $mobile->registerDevice();
                break;
            case 'unregister':
                $mobile->unregisterDevice();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action not found']);
        }
        break;
    case 'GET':
        switch ($action) {
            case 'stats':
                $mobile->getMobileStats();
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
