<?php
/**
 * User Analytics Tracker
 * Tracks user activity for analytics dashboard
 */

class UserAnalyticsTracker {
    private $conn;
    private $sessionId;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->sessionId = session_id() ?: $this->generateSessionId();
    }
    
    /**
     * Track user activity
     */
    public function trackActivity($action, $pageUrl = null, $userId = null) {
        try {
            $query = "INSERT INTO user_analytics (user_id, session_id, action, page_url, ip_address, user_agent, referrer) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($this->conn, $query);
            
            $userId = $userId ?: ($_SESSION['user_id'] ?? null);
            $pageUrl = $pageUrl ?: $_SERVER['REQUEST_URI'] ?? '';
            $ipAddress = $this->getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            
            mysqli_stmt_bind_param($stmt, 'issssss', 
                $userId, 
                $this->sessionId, 
                $action, 
                $pageUrl, 
                $ipAddress, 
                $userAgent, 
                $referrer
            );
            
            return mysqli_stmt_execute($stmt);
            
        } catch (Exception $e) {
            error_log("Analytics tracking error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track page view
     */
    public function trackPageView($userId = null) {
        $this->trackActivity('page_view', null, $userId);
    }
    
    /**
     * Track search action
     */
    public function trackSearch($query, $userId = null) {
        $pageUrl = $_SERVER['REQUEST_URI'] ?? '';
        if ($query) {
            $pageUrl .= (strpos($pageUrl, '?') !== false ? '&' : '?') . 'q=' . urlencode($query);
        }
        $this->trackActivity('search', $pageUrl, $userId);
    }
    
    /**
     * Track login
     */
    public function trackLogin($userId) {
        $this->trackActivity('login', '/login.php', $userId);
    }
    
    /**
     * Track logout
     */
    public function trackLogout($userId) {
        $this->trackActivity('logout', '/logout.php', $userId);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Generate session ID if session not started
     */
    private function generateSessionId() {
        return uniqid('analytics_', true);
    }
    
    /**
     * Clean old analytics data (older than 90 days)
     */
    public function cleanOldData() {
        try {
            $query = "DELETE FROM user_analytics WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)";
            return mysqli_query($this->conn, $query);
        } catch (Exception $e) {
            error_log("Analytics cleanup error: " . $e->getMessage());
            return false;
        }
    }
}

// Global function for easy tracking
function track_user_activity($conn, $action, $pageUrl = null, $userId = null) {
    static $tracker = null;
    if ($tracker === null) {
        $tracker = new UserAnalyticsTracker($conn);
    }
    return $tracker->trackActivity($action, $pageUrl, $userId);
}
?>
