<?php
// Helper functions for PK Live News
// Note: Functions like format_date(), clean_input(), is_logged_in(), is_admin(), redirect() 
// are already defined in database.php to avoid conflicts

// create_slug function is already defined in database.php to avoid conflicts

// Format time for display
function format_time($date) {
    return date('h:i A', strtotime($date));
}

// Format datetime for display
function format_datetime($date) {
    return date('M d, Y h:i A', strtotime($date));
}

// Time ago function is already defined in database.php to avoid conflicts

// Generate random string
function generate_random_string($length = 10) {
    return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / 2))), 0, $length);
}

// Get file extension
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Check if image file
function is_image($filename) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    return in_array(get_file_extension($filename), $allowed_extensions);
}

// format_file_size function is already defined in database.php to avoid conflicts

// Truncate text
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

// Generate pagination links
function generate_pagination($current_page, $total_pages, $base_url) {
    $pagination = '';
    
    if ($total_pages > 1) {
        $pagination .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        if ($current_page > 1) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($current_page - 1) . '">Previous</a></li>';
        }
        
        // Page numbers
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active = ($i == $current_page) ? 'active' : '';
            $pagination .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . '&page=' . $i . '">' . $i . '</a></li>';
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($current_page + 1) . '">Next</a></li>';
        }
        
        $pagination .= '</ul></nav>';
    }
    
    return $pagination;
}

// Send email function
function send_email($to, $subject, $message, $headers = '') {
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Log activity
function log_activity($user_id, $action, $details = '') {
    global $conn;
    
    $query = "INSERT INTO activity_log (user_id, action, details, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'issss', $user_id, $action, $details, 
                           $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
    mysqli_stmt_execute($stmt);
}

// Get setting value
function get_setting($key, $default = '') {
    global $conn;
    
    static $settings = [];
    
    if (empty($settings)) {
        $query = "SELECT setting_key, setting_value FROM settings";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings[$key] ?? $default;
}

// Get system setting value (for system_settings table)
function get_system_setting($key, $default = '') {
    global $conn;
    
    static $system_settings = [];
    
    if (empty($system_settings)) {
        // Check if system_settings table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'system_settings'");
        if (mysqli_num_rows($table_check) == 0) {
            // Create the table if it doesn't exist
            $create_table_sql = "CREATE TABLE `system_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `setting_key` varchar(100) NOT NULL,
                `setting_value` text DEFAULT NULL,
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                `updated_by` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `setting_key` (`setting_key`),
                KEY `updated_by` (`updated_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            mysqli_query($conn, $create_table_sql);
            
            // Insert default settings
            $default_settings = [
                'site_name' => 'PK Live News',
                'site_description' => 'Latest news and updates from Pakistan',
                'footer_content' => 'Developed for FYP Project',
                'contact_email' => 'admin@pklivenews.com',
                'maintenance_mode' => '0',
                'theme_color' => '#007bff'
            ];
            
            foreach ($default_settings as $setting_key => $setting_value) {
                $insert_sql = "INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)";
                $stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($stmt, 'ss', $setting_key, $setting_value);
                mysqli_stmt_execute($stmt);
            }
        }
        
        $query = "SELECT setting_key, setting_value FROM system_settings";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $system_settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
    
    return $system_settings[$key] ?? $default;
}

// is_reporter and is_editor functions are already defined in database.php to avoid conflicts
?>
