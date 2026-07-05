<?php
// Comprehensive Connection Test System for PK Live News
// This file tests all critical connections and configurations

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize test results array
$test_results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [],
    'tests' => [],
    'summary' => [
        'total_tests' => 0,
        'passed_tests' => 0,
        'failed_tests' => 0,
        'warnings' => 0
    ]
];

// Get server information
$test_results['server_info'] = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
    'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'Yes' : 'No',
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size')
];

// Function to run a test and add results
function runTest($test_name, $test_function, $category = 'general') {
    global $test_results;
    
    $test_results['summary']['total_tests']++;
    
    try {
        $result = $test_function();
        $test_results['tests'][] = [
            'name' => $test_name,
            'category' => $category,
            'status' => $result['status'] ?? 'passed',
            'message' => $result['message'] ?? 'Test completed successfully',
            'details' => $result['details'] ?? [],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($result['status'] === 'passed') {
            $test_results['summary']['passed_tests']++;
        } elseif ($result['status'] === 'failed') {
            $test_results['summary']['failed_tests']++;
        } elseif ($result['status'] === 'warning') {
            $test_results['summary']['warnings']++;
        }
        
    } catch (Exception $e) {
        $test_results['tests'][] = [
            'name' => $test_name,
            'category' => $category,
            'status' => 'failed',
            'message' => 'Test failed with exception: ' . $e->getMessage(),
            'details' => ['exception' => $e->getMessage()],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $test_results['summary']['failed_tests']++;
    }
}

// Test 1: Database Connection
runTest('Database Connection', function() {
    $config_file = __DIR__ . '/config/database.php';
    
    if (!file_exists($config_file)) {
        return [
            'status' => 'failed',
            'message' => 'Database configuration file not found',
            'details' => ['file_path' => $config_file]
        ];
    }
    
    try {
        require_once $config_file;
        
        if (!isset($conn) || !$conn) {
            return [
                'status' => 'failed',
                'message' => 'Database connection failed',
                'details' => ['error' => mysqli_connect_error() ?? 'Unknown error']
            ];
        }
        
        // Test basic query
        $result = mysqli_query($conn, "SELECT 1 as test");
        if (!$result) {
            return [
                'status' => 'failed',
                'message' => 'Database query test failed',
                'details' => ['error' => mysqli_error($conn)]
            ];
        }
        
        // Check database version and charset
        $version_result = mysqli_query($conn, "SELECT VERSION() as version");
        $version = mysqli_fetch_assoc($version_result)['version'] ?? 'Unknown';
        
        $charset_result = mysqli_query($conn, "SHOW VARIABLES LIKE 'character_set_database'");
        $charset = mysqli_fetch_assoc($charset_result)['Value'] ?? 'Unknown';
        
        // Check important tables
        $tables_result = mysqli_query($conn, "SHOW TABLES");
        $tables = [];
        while ($row = mysqli_fetch_row($tables_result)) {
            $tables[] = $row[0];
        }
        
        $important_tables = ['articles', 'users', 'categories', 'settings', 'site_settings'];
        $missing_tables = array_diff($important_tables, $tables);
        
        return [
            'status' => 'passed',
            'message' => 'Database connection successful',
            'details' => [
                'version' => $version,
                'charset' => $charset,
                'total_tables' => count($tables),
                'important_tables' => $important_tables,
                'missing_tables' => $missing_tables,
                'connection_info' => [
                    'host' => DB_HOST ?? 'Unknown',
                    'database' => DB_NAME ?? 'Unknown',
                    'user' => DB_USER ?? 'Unknown'
                ]
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'failed',
            'message' => 'Database connection error: ' . $e->getMessage(),
            'details' => ['exception' => $e->getMessage()]
        ];
    }
}, 'database');

// Test 2: File System Permissions
runTest('File System Permissions', function() {
    $critical_paths = [
        'uploads/' => 'writable',
        'cache/' => 'writable',
        'logs/' => 'writable',
        'config/' => 'readable',
        'admin/' => 'readable',
        'api/' => 'readable',
        'assets/' => 'readable'
    ];
    
    $results = [];
    $issues = [];
    
    foreach ($critical_paths as $path => $required_permission) {
        $full_path = __DIR__ . '/' . $path;
        
        if (!file_exists($full_path)) {
            $results[$path] = [
                'exists' => false,
                'readable' => false,
                'writable' => false,
                'status' => 'failed',
                'message' => 'Directory does not exist'
            ];
            $issues[] = "Directory $path does not exist";
            continue;
        }
        
        $is_readable = is_readable($full_path);
        $is_writable = is_writable($full_path);
        
        $status = 'passed';
        $message = 'Permissions OK';
        
        if ($required_permission === 'writable' && !$is_writable) {
            $status = 'failed';
            $message = 'Directory is not writable';
            $issues[] = "Directory $path is not writable";
        } elseif ($required_permission === 'readable' && !$is_readable) {
            $status = 'failed';
            $message = 'Directory is not readable';
            $issues[] = "Directory $path is not readable";
        }
        
        $results[$path] = [
            'exists' => true,
            'readable' => $is_readable,
            'writable' => $is_writable,
            'status' => $status,
            'message' => $message,
            'permissions' => substr(sprintf('%o', fileperms($full_path)), -4)
        ];
    }
    
    $overall_status = empty($issues) ? 'passed' : 'failed';
    
    return [
        'status' => $overall_status,
        'message' => $overall_status === 'passed' ? 'All file permissions are correct' : 'Some permission issues found',
        'details' => [
            'paths' => $results,
            'issues' => $issues
        ]
    ];
}, 'filesystem');

// Test 3: .htaccess Configuration
runTest('.htaccess Configuration', function() {
    $htaccess_file = __DIR__ . '/.htaccess';
    
    if (!file_exists($htaccess_file)) {
        return [
            'status' => 'warning',
            'message' => '.htaccess file not found',
            'details' => ['file_path' => $htaccess_file, 'recommendation' => 'Create .htaccess file for proper URL rewriting and security']
        ];
    }
    
    $content = file_get_contents($htaccess_file);
    $checks = [
        'rewrite_engine' => strpos($content, 'RewriteEngine On') !== false,
        'security_headers' => strpos($content, 'X-Content-Type-Options') !== false,
        'file_protection' => strpos($content, 'Deny from all') !== false,
        'php_settings' => strpos($content, 'php_value') !== false || strpos($content, 'php_flag') !== false
    ];
    
    $missing_features = [];
    foreach ($checks as $feature => $exists) {
        if (!$exists) {
            $missing_features[] = $feature;
        }
    }
    
    $status = empty($missing_features) ? 'passed' : 'warning';
    
    return [
        'status' => $status,
        'message' => $status === 'passed' ? '.htaccess file is properly configured' : '.htaccess file could be improved',
        'details' => [
            'file_exists' => true,
            'file_size' => strlen($content),
            'features' => $checks,
            'missing_features' => $missing_features,
            'recommendations' => $missing_features
        ]
    ];
}, 'server');

// Test 4: API Endpoints
runTest('API Endpoints', function() {
    $api_endpoints = [
        'breaking-news.php' => 'api/breaking-news.php',
        'countries_with_news.php' => 'api/countries_with_news.php',
        'weather.php' => 'api/weather.php',
        'clear_old_bookmarks.php' => 'api/clear_old_bookmarks.php'
    ];
    
    $results = [];
    $working_endpoints = 0;
    
    foreach ($api_endpoints as $name => $path) {
        $full_path = __DIR__ . '/' . $path;
        
        if (!file_exists($full_path)) {
            $results[$name] = [
                'exists' => false,
                'accessible' => false,
                'status' => 'failed',
                'message' => 'API file not found'
            ];
            continue;
        }
        
        // Check if file is accessible (basic syntax check)
        $content = file_get_contents($full_path);
        $has_syntax = !strpos($content, '<?php') === false || strpos($content, '<?php') !== false;
        
        $results[$name] = [
            'exists' => true,
            'accessible' => $has_syntax,
            'status' => $has_syntax ? 'passed' : 'warning',
            'message' => $has_syntax ? 'API file found and appears valid' : 'API file may have syntax issues'
        ];
        
        if ($has_syntax) {
            $working_endpoints++;
        }
    }
    
    $status = $working_endpoints === count($api_endpoints) ? 'passed' : 'warning';
    
    return [
        'status' => $status,
        'message' => "$working_endpoints out of " . count($api_endpoints) . " API endpoints are available",
        'details' => [
            'endpoints' => $results,
            'working_count' => $working_endpoints,
            'total_count' => count($api_endpoints)
        ]
    ];
}, 'api');

// Test 5: RSS Feed Configuration
runTest('RSS Feed Configuration', function() {
    $rss_files = [
        'rss.php' => __DIR__ . '/rss.php',
        'rss_import.php' => __DIR__ . '/admin/rss_import.php'
    ];
    
    $results = [];
    $rss_working = 0;
    
    foreach ($rss_files as $name => $path) {
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $has_xml = strpos($content, '<?xml') !== false || strpos($content, 'simplexml_load_file') !== false;
            
            $results[$name] = [
                'exists' => true,
                'has_xml_support' => $has_xml,
                'status' => $has_xml ? 'passed' : 'warning'
            ];
            
            if ($has_xml) {
                $rss_working++;
            }
        } else {
            $results[$name] = [
                'exists' => false,
                'has_xml_support' => false,
                'status' => 'failed'
            ];
        }
    }
    
    // Check if simplexml extension is available
    $simplexml_available = extension_loaded('simplexml');
    
    $status = ($rss_working > 0 && $simplexml_available) ? 'passed' : 'warning';
    
    return [
        'status' => $status,
        'message' => $status === 'passed' ? 'RSS feed system is available' : 'RSS feed system may have issues',
        'details' => [
            'files' => $results,
            'simplexml_extension' => $simplexml_available,
            'working_files' => $rss_working
        ]
    ];
}, 'rss');

// Test 6: Weather API Integration
runTest('Weather API Integration', function() {
    $weather_file = __DIR__ . '/api/weather.php';
    
    if (!file_exists($weather_file)) {
        return [
            'status' => 'warning',
            'message' => 'Weather API file not found',
            'details' => ['file_path' => $weather_file]
        ];
    }
    
    $content = file_get_contents($weather_file);
    
    // Check for API key configuration
    $has_api_key = strpos($content, 'api_key') !== false || strpos($content, 'API_KEY') !== false;
    $has_curl = strpos($content, 'curl_init') !== false || strpos($content, 'file_get_contents') !== false || 
               strpos($content, 'weather.php') !== false; // Check if weather config is included
    
    // Check if curl extension is available
    $curl_available = extension_loaded('curl');
    
    $status = ($has_api_key && $has_curl && $curl_available) ? 'passed' : 'warning';
    
    return [
        'status' => $status,
        'message' => $status === 'passed' ? 'Weather API integration is properly configured' : 'Weather API integration may need configuration',
        'details' => [
            'file_exists' => true,
            'has_api_key_config' => $has_api_key,
            'has_http_client' => $has_curl,
            'curl_extension' => $curl_available
        ]
    ];
}, 'external');

// Test 7: Live Streaming Configuration
runTest('Live Streaming Configuration', function() {
    $streaming_files = [
        'live-streaming.php' => __DIR__ . '/admin/live-streaming.php',
        'live_tv.php' => __DIR__ . '/live_tv.php'
    ];
    
    $results = [];
    $streaming_working = 0;
    
    foreach ($streaming_files as $name => $path) {
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $has_streaming = strpos($content, 'iframe') !== false || strpos($content, 'stream') !== false;
            
            $results[$name] = [
                'exists' => true,
                'has_streaming_support' => $has_streaming,
                'status' => $has_streaming ? 'passed' : 'warning'
            ];
            
            if ($has_streaming) {
                $streaming_working++;
            }
        } else {
            $results[$name] = [
                'exists' => false,
                'has_streaming_support' => false,
                'status' => 'warning'
            ];
        }
    }
    
    $status = $streaming_working > 0 ? 'passed' : 'warning';
    
    return [
        'status' => $status,
        'message' => $status === 'passed' ? 'Live streaming configuration found' : 'Live streaming may not be fully configured',
        'details' => [
            'files' => $results,
            'working_files' => $streaming_working
        ]
    ];
}, 'streaming');

// Test 8: Affiliate System
runTest('Affiliate System', function() {
    $affiliate_files = [
        'affiliate-products.php' => __DIR__ . '/admin/affiliate-products.php',
        'affiliate-click.php' => __DIR__ . '/affiliate-click.php',
        'affiliate-links-system.php' => __DIR__ . '/affiliate-links-system.php'
    ];
    
    $results = [];
    $affiliate_working = 0;
    
    foreach ($affiliate_files as $name => $path) {
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $has_affiliate = strpos($content, 'affiliate') !== false || strpos($content, 'product') !== false;
            
            $results[$name] = [
                'exists' => true,
                'has_affiliate_code' => $has_affiliate,
                'status' => $has_affiliate ? 'passed' : 'warning'
            ];
            
            if ($has_affiliate) {
                $affiliate_working++;
            }
        } else {
            $results[$name] = [
                'exists' => false,
                'has_affiliate_code' => false,
                'status' => 'warning'
            ];
        }
    }
    
    $status = $affiliate_working > 0 ? 'passed' : 'warning';
    
    return [
        'status' => $status,
        'message' => $status === 'passed' ? 'Affiliate system is configured' : 'Affiliate system may not be fully configured',
        'details' => [
            'files' => $results,
            'working_files' => $affiliate_working
        ]
    ];
}, 'affiliate');

// Test 9: Required PHP Extensions
runTest('Required PHP Extensions', function() {
    $required_extensions = [
        'mysqli' => 'Database connectivity',
        'curl' => 'HTTP requests and API calls',
        'json' => 'JSON handling',
        'mbstring' => 'Multi-byte string handling',
        'gd' => 'Image processing',
        'simplexml' => 'XML parsing for RSS',
        'session' => 'Session management',
        'fileinfo' => 'File type detection'
    ];
    
    $results = [];
    $missing_extensions = [];
    
    foreach ($required_extensions as $ext => $description) {
        $loaded = extension_loaded($ext);
        $results[$ext] = [
            'loaded' => $loaded,
            'description' => $description,
            'status' => $loaded ? 'passed' : 'failed'
        ];
        
        if (!$loaded) {
            $missing_extensions[] = $ext;
        }
    }
    
    $status = empty($missing_extensions) ? 'passed' : 'warning';
    
    return [
        'status' => $status,
        'message' => $status === 'passed' ? 'All required extensions are loaded' : 'Some extensions are missing',
        'details' => [
            'extensions' => $results,
            'missing_extensions' => $missing_extensions,
            'recommendation' => $missing_extensions ? 'Install missing PHP extensions: ' . implode(', ', $missing_extensions) : 'All extensions are available'
        ]
    ];
}, 'php');

// Test 10: Security Configuration
runTest('Security Configuration', function() {
    $security_checks = [
        'display_errors' => [
            'current' => ini_get('display_errors'),
            'recommended' => 'Off (production)',
            'secure' => ini_get('display_errors') === '0' || ini_get('display_errors') === 'Off'
        ],
        'expose_php' => [
            'current' => ini_get('expose_php'),
            'recommended' => 'Off',
            'secure' => ini_get('expose_php') === '0' || ini_get('expose_php') === 'Off'
        ],
        'allow_url_fopen' => [
            'current' => ini_get('allow_url_fopen'),
            'recommended' => 'On (if needed)',
            'secure' => true // This is context-dependent
        ],
        'file_uploads' => [
            'current' => ini_get('file_uploads'),
            'recommended' => 'On (if needed)',
            'secure' => true // This is context-dependent
        ]
    ];
    
    $issues = [];
    foreach ($security_checks as $check => $data) {
        if (!$data['secure']) {
            $issues[] = $check;
        }
    }
    
    $status = empty($issues) ? 'passed' : 'warning';
    
    return [
        'status' => $status,
        'message' => $status === 'passed' ? 'Security configuration is good' : 'Some security settings could be improved',
        'details' => [
            'checks' => $security_checks,
            'issues' => $issues,
            'recommendations' => $issues
        ]
    ];
}, 'security');

// Calculate overall status
$overall_status = 'passed';
if ($test_results['summary']['failed_tests'] > 0) {
    $overall_status = 'failed';
} elseif ($test_results['summary']['warnings'] > 0) {
    $overall_status = 'warning';
}

$test_results['overall_status'] = $overall_status;
$test_results['summary']['success_rate'] = $test_results['summary']['total_tests'] > 0 ? 
    round(($test_results['summary']['passed_tests'] / $test_results['summary']['total_tests']) * 100, 2) : 0;

// Output JSON response
echo json_encode($test_results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
