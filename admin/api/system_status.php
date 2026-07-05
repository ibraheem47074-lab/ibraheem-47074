<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Get system status information
$status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'uptime' => get_server_uptime(),
    'memory_usage' => [
        'current' => memory_get_usage(true),
        'peak' => memory_get_peak_usage(true),
        'formatted_current' => format_bytes(memory_get_usage(true)),
        'formatted_peak' => format_bytes(memory_get_peak_usage(true))
    ],
    'disk_usage' => get_disk_usage('../'),
    'database_stats' => get_database_stats($conn),
    'active_sessions' => get_active_sessions(),
    'server_load' => get_server_load(),
    'php_info' => [
        'version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size')
    ],
    'mysql_info' => [
        'version' => mysqli_get_server_info($conn),
        'connections' => get_mysql_connections($conn)
    ],
    'recent_errors' => get_recent_errors(),
    'cache_status' => get_cache_status()
];

echo json_encode($status);

// Helper functions are already included in config/database.php

function format_bytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

function get_server_uptime() {
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        return $load[0] ?? 'Unknown';
    }
    return 'Unknown';
}

function get_disk_usage($path) {
    $total_space = disk_total_space($path);
    $free_space = disk_free_space($path);
    $used_space = $total_space - $free_space;
    
    return [
        'total' => $total_space,
        'used' => $used_space,
        'free' => $free_space,
        'percentage' => $total_space > 0 ? round(($used_space / $total_space) * 100, 2) : 0,
        'formatted_total' => format_bytes($total_space),
        'formatted_used' => format_bytes($used_space),
        'formatted_free' => format_bytes($free_space)
    ];
}

function get_database_stats($conn) {
    $stats = [];
    
    // Get table stats
    $tables_query = "SHOW TABLE STATUS";
    $result = mysqli_query($conn, $tables_query);
    $total_size = 0;
    $table_count = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $total_size += $row['Data_length'] + $row['Index_length'];
        $table_count++;
    }
    
    $stats['total_size'] = $total_size;
    $stats['formatted_size'] = format_bytes($total_size);
    $stats['table_count'] = $table_count;
    
    // Get row counts for main tables
    $main_tables = ['news', 'users', 'categories', 'comments'];
    $stats['row_counts'] = [];
    
    foreach ($main_tables as $table) {
        $count_query = "SELECT COUNT(*) as count FROM $table";
        $result = mysqli_query($conn, $count_query);
        $count = mysqli_fetch_assoc($result)['count'];
        $stats['row_counts'][$table] = $count;
    }
    
    return $stats;
}

function get_active_sessions() {
    $session_path = session_save_path();
    if ($session_path && is_dir($session_path)) {
        $files = glob($session_path . 'sess_*');
        return count($files);
    }
    return 0;
}

function get_server_load() {
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        return [
            '1_min' => $load[0] ?? 0,
            '5_min' => $load[1] ?? 0,
            '15_min' => $load[2] ?? 0
        ];
    }
    return ['1_min' => 0, '5_min' => 0, '15_min' => 0];
}

function get_mysql_connections($conn) {
    $result = mysqli_query($conn, "SHOW STATUS LIKE 'Threads_connected'");
    $row = mysqli_fetch_assoc($result);
    return $row['Value'] ?? 0;
}

function get_recent_errors() {
    $error_log = ini_get('error_log');
    $errors = [];
    
    if ($error_log && file_exists($error_log)) {
        $log_content = file_get_contents($error_log);
        $log_lines = explode("\n", $log_content);
        
        // Get last 10 error lines
        $recent_lines = array_slice($log_lines, -10);
        
        foreach ($recent_lines as $line) {
            if (!empty(trim($line)) && strpos($line, 'error') !== false) {
                $errors[] = trim($line);
            }
        }
    }
    
    return array_slice($errors, -5); // Return last 5 errors
}

function get_cache_status() {
    $cache_dir = '../cache/';
    $status = [
        'enabled' => is_dir($cache_dir),
        'file_count' => 0,
        'total_size' => 0
    ];
    
    if ($status['enabled']) {
        $files = glob($cache_dir . '*');
        $status['file_count'] = count($files);
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $status['total_size'] += filesize($file);
            }
        }
        
        $status['formatted_size'] = format_bytes($status['total_size']);
    }
    
    return $status;
}
?>
