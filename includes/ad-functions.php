<?php
// Advertisement Management Functions

function get_active_ads($position = null, $page_type = null, $category_id = null, $device_type = null, $limit = 1) {
    global $conn;
    
    $sql = "SELECT * FROM advertisements WHERE status = 'active'";
    $params = [];
    $types = "";
    
    if ($position) {
        $sql .= " AND position = ?";
        $params[] = $position;
        $types .= "s";
    }
    
    if ($page_type && $page_type !== 'all') {
        $sql .= " AND (page_type = ? OR page_type = 'all')";
        $params[] = $page_type;
        $types .= "s";
    }
    
    if ($category_id) {
        $sql .= " AND (category_id = ? OR category_id IS NULL)";
        $params[] = $category_id;
        $types .= "i";
    }
    
    if ($device_type && $device_type !== 'all') {
        $sql .= " AND (device_type = ? OR device_type = 'all')";
        $params[] = $device_type;
        $types .= "s";
    }
    
    // Check date constraints
    $sql .= " AND (start_date IS NULL OR start_date <= CURDATE())";
    $sql .= " AND (end_date IS NULL OR end_date >= CURDATE())";
    
    $sql .= " ORDER BY RAND()";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= "i";
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $ads = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ads[] = $row;
    }
    
    return $ads;
}

function get_ad_render_code($ad) {
    $code = trim($ad['code'] ?? $ad['ad_code'] ?? '');
    if ($code !== '') {
        return $code;
    }

    $image = trim($ad['image'] ?? '');
    if ($image === '') {
        return '';
    }

    $img_src = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
    $alt = htmlspecialchars($ad['title'] ?? 'Advertisement', ENT_QUOTES, 'UTF-8');
    $redirect = trim($ad['redirect_url'] ?? '');

    if ($redirect !== '') {
        $href = htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8');
        return '<a href="' . $href . '" target="_blank" rel="noopener sponsored"><img src="' . $img_src . '" alt="' . $alt . '" style="max-width:100%;height:auto;"></a>';
    }

    return '<img src="' . $img_src . '" alt="' . $alt . '" style="max-width:100%;height:auto;">';
}

function display_ad($position, $class = '', $page_type = null) {
    $ads = get_active_ads($position, $page_type, null, null, 1);
    
    if (empty($ads)) {
        return '';
    }
    
    $ad = $ads[0];
    $ad_code = get_ad_render_code($ad);
    if ($ad_code === '') {
        return '';
    }
    
    // Track impression
    track_ad_impression($ad['id']);
    
    // Update impression count
    update_ad_impressions($ad['id']);
    
    // Generate click tracking URL
    $ad_code = add_click_tracking($ad_code, $ad['id']);
    
    // Add responsive wrapper
    $wrapper_class = "ad-wrapper ad-{$position}";
    if ($class) {
        $wrapper_class .= " {$class}";
    }
    
    $html = "<div class='{$wrapper_class}' data-ad-id='{$ad['id']}'>";
    $html .= $ad_code;
    $html .= "</div>";
    
    return $html;
}

function track_ad_impression($ad_id) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $page_url = $_SERVER['REQUEST_URI'];
    
    $sql = "INSERT INTO ad_impressions (ad_id, ip_address, user_agent, page_url) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $ad_id, $ip_address, $user_agent, $page_url);
    mysqli_stmt_execute($stmt);
}

function track_ad_click($ad_id) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $page_url = $_SERVER['REQUEST_URI'];
    
    $sql = "INSERT INTO ad_clicks (ad_id, ip_address, user_agent, page_url) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $ad_id, $ip_address, $user_agent, $page_url);
    mysqli_stmt_execute($stmt);
    
    // Clicks are tracked in the ad_clicks table
    // No need to update advertisements table directly
}

function update_ad_impressions($ad_id) {
    // Impressions are tracked in the ad_impressions table
    // This function is kept for compatibility but doesn't need to update the advertisements table
    return true;
}

function add_click_tracking($ad_code, $ad_id) {
    // Add click tracking to links in ad code
    if ($ad_code === null) {
        return '';
    }
    
    $ad_code = preg_replace_callback(
        '/<a\s+([^>]*?)href=["\']([^"\']+)["\']([^>]*?)>/i',
        function($matches) use ($ad_id) {
            $url = $matches[2];
            $tracked_url = "track-ad-click.php?ad_id={$ad_id}&redirect=" . urlencode($url);
            return "<a {$matches[1]}href=\"{$tracked_url}\"{$matches[3]}>";
        },
        $ad_code
    );
    
    return $ad_code;
}

function get_ad_statistics($ad_id) {
    global $conn;
    
    // Check if ad_impressions table exists, create it if not
    $impressions_check = mysqli_query($conn, "SHOW TABLES LIKE 'ad_impressions'");
    if (mysqli_num_rows($impressions_check) == 0) {
        $create_impressions_sql = "CREATE TABLE `ad_impressions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ad_id` int(11) NOT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `page_url` varchar(500) DEFAULT NULL,
            `impression_time` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `ad_id` (`ad_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        mysqli_query($conn, $create_impressions_sql);
    }
    
    // Check if ad_clicks table exists, create it if not
    $clicks_check = mysqli_query($conn, "SHOW TABLES LIKE 'ad_clicks'");
    if (mysqli_num_rows($clicks_check) == 0) {
        $create_clicks_sql = "CREATE TABLE `ad_clicks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ad_id` int(11) NOT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `page_url` varchar(500) DEFAULT NULL,
            `click_time` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `ad_id` (`ad_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        mysqli_query($conn, $create_clicks_sql);
    }
    
    $sql = "SELECT 
                a.*,
                COUNT(DISTINCT i.id) as total_impressions,
                COUNT(DISTINCT c.id) as total_clicks,
                COUNT(DISTINCT i.ip_address) as unique_impressions,
                COUNT(DISTINCT c.ip_address) as unique_clicks
            FROM advertisements a
            LEFT JOIN ad_impressions i ON a.id = i.ad_id
            LEFT JOIN ad_clicks c ON a.id = c.ad_id
            WHERE a.id = ?
            GROUP BY a.id";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $ad_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

// Helper functions for displaying ads in specific sections
function display_live_ads() {
    $html = '';
    
    // Live header ad
    $header_ads = get_active_ads('live_header', 'live', null, null, 1);
    if (!empty($header_ads)) {
        $html .= display_ad_with_wrapper($header_ads[0], 'live-header-ad');
    }
    
    // Live sidebar ad
    $sidebar_ads = get_active_ads('live_sidebar', 'live', null, null, 1);
    if (!empty($sidebar_ads)) {
        $html .= display_ad_with_wrapper($sidebar_ads[0], 'live-sidebar-ad');
    }
    
    // Live footer ad
    $footer_ads = get_active_ads('live_footer', 'live', null, null, 1);
    if (!empty($footer_ads)) {
        $html .= display_ad_with_wrapper($footer_ads[0], 'live-footer-ad');
    }
    
    return $html;
}

function display_performance_ads() {
    $html = '';
    
    // Performance header ad
    $header_ads = get_active_ads('performance_header', 'performance', null, null, 1);
    if (!empty($header_ads)) {
        $html .= display_ad_with_wrapper($header_ads[0], 'performance-header-ad');
    }
    
    // Performance sidebar ad
    $sidebar_ads = get_active_ads('performance_sidebar', 'performance', null, null, 1);
    if (!empty($sidebar_ads)) {
        $html .= display_ad_with_wrapper($sidebar_ads[0], 'performance-sidebar-ad');
    }
    
    // Performance inline ad
    $inline_ads = get_active_ads('performance_inline', 'performance', null, null, 1);
    if (!empty($inline_ads)) {
        $html .= display_ad_with_wrapper($inline_ads[0], 'performance-inline-ad');
    }
    
    return $html;
}

function display_contact_ads() {
    $html = '';
    
    // Contact header ad
    $header_ads = get_active_ads('contact_header', 'contact', null, null, 1);
    if (!empty($header_ads)) {
        $html .= display_ad_with_wrapper($header_ads[0], 'contact-header-ad');
    }
    
    // Contact sidebar ad
    $sidebar_ads = get_active_ads('contact_sidebar', 'contact', null, null, 1);
    if (!empty($sidebar_ads)) {
        $html .= display_ad_with_wrapper($sidebar_ads[0], 'contact-sidebar-ad');
    }
    
    return $html;
}

function display_category_ads($category_id) {
    $html = '';
    
    // Category header ad
    $header_ads = get_active_ads('category_header', 'category', $category_id, null, 1);
    if (!empty($header_ads)) {
        $html .= display_ad_with_wrapper($header_ads[0], 'category-header-ad');
    }
    
    // Category sidebar ad
    $sidebar_ads = get_active_ads('category_sidebar', 'category', $category_id, null, 1);
    if (!empty($sidebar_ads)) {
        $html .= display_ad_with_wrapper($sidebar_ads[0], 'category-sidebar-ad');
    }
    
    // Category inline ad
    $inline_ads = get_active_ads('category_inline', 'category', $category_id, null, 1);
    if (!empty($inline_ads)) {
        $html .= display_ad_with_wrapper($inline_ads[0], 'category-inline-ad');
    }
    
    return $html;
}

function display_home_ads() {
    $html = '';
    
    // Home hero ad
    $hero_ads = get_active_ads('home_hero', 'home', null, null, 1);
    if (!empty($hero_ads)) {
        $html .= display_ad_with_wrapper($hero_ads[0], 'home-hero-ad');
    }
    
    // Home featured ad
    $featured_ads = get_active_ads('home_featured', 'home', null, null, 1);
    if (!empty($featured_ads)) {
        $html .= display_ad_with_wrapper($featured_ads[0], 'home-featured-ad');
    }
    
    // Home sidebar ad
    $sidebar_ads = get_active_ads('home_sidebar', 'home', null, null, 1);
    if (!empty($sidebar_ads)) {
        $html .= display_ad_with_wrapper($sidebar_ads[0], 'home-sidebar-ad');
    }
    
    return $html;
}

function display_ad_with_wrapper($ad, $wrapper_class) {
    if (empty($ad)) {
        return '';
    }
    
    // Track impression
    track_ad_impression($ad['id']);
    
    // Update impression count
    update_ad_impressions($ad['id']);
    
    $ad_code = get_ad_render_code($ad);
    if ($ad_code === '') {
        return '';
    }

    // Generate click tracking URL
    $ad_code = add_click_tracking($ad_code, $ad['id']);
    
    // Add responsive wrapper
    $html = "<div class='ad-wrapper {$wrapper_class}' data-ad-id='{$ad['id']}'>";
    $html .= $ad_code;
    $html .= "</div>";
    
    return $html;
}

function get_ad_performance_report($days = 30) {
    global $conn;
    
    $sql = "SELECT 
                DATE(i.impression_time) as date,
                COUNT(DISTINCT i.id) as impressions,
                COUNT(DISTINCT c.id) as clicks,
                ROUND((COUNT(DISTINCT c.id) / COUNT(DISTINCT i.id)) * 100, 2) as ctr
            FROM ad_impressions i
            LEFT JOIN ad_clicks c ON i.ad_id = c.ad_id 
                AND DATE(i.impression_time) = DATE(c.click_time)
            WHERE i.impression_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(i.impression_time)
            ORDER BY date DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $days);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $report = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $report[] = $row;
    }
    
    return $report;
}
?>
