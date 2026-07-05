<?php
// Comprehensive Connection Test Report Generator
// This file generates detailed HTML and PDF reports for connection tests

// Set headers for HTML response
header('Content-Type: text/html; charset=UTF-8');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get test results from connection test system
function getTestResults() {
    $test_url = __DIR__ . '/connection_test_system.php';
    
    if (!file_exists($test_url)) {
        return [
            'error' => 'Connection test system not found',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    // Capture output from connection test system
    ob_start();
    include $test_url;
    $output = ob_get_clean();
    
    // Parse JSON output
    $results = json_decode($output, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'error' => 'Failed to parse test results: ' . json_last_error_msg(),
            'raw_output' => $output,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    return $results;
}

// Generate HTML report
function generateHTMLReport($results) {
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PK Live News - Connection Test Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            padding: 40px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin: -20px -20px 40px -20px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            color: #2c3e50;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #667eea;
        }

        .summary-card.success {
            border-left-color: #28a745;
        }

        .summary-card.warning {
            border-left-color: #ffc107;
        }

        .summary-card.danger {
            border-left-color: #dc3545;
        }

        .summary-card h3 {
            font-size: 2.5em;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .summary-card p {
            color: #6c757d;
            font-weight: 600;
        }

        .server-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .info-label {
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            color: #6c757d;
        }

        .test-item {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }

        .test-item.success {
            border-left-color: #28a745;
        }

        .test-item.warning {
            border-left-color: #ffc107;
        }

        .test-item.danger {
            border-left-color: #dc3545;
        }

        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .test-title {
            font-size: 1.4em;
            font-weight: 600;
            color: #2c3e50;
        }

        .test-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .test-status.passed {
            background: #d4edda;
            color: #155724;
        }

        .test-status.warning {
            background: #fff3cd;
            color: #856404;
        }

        .test-status.failed {
            background: #f8d7da;
            color: #721c24;
        }

        .test-message {
            color: #6c757d;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .test-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
        }

        .test-details h4 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 1.2em;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 10px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
        }

        .detail-value {
            color: #6c757d;
            word-break: break-all;
        }

        .recommendations {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .recommendations h4 {
            color: #0066cc;
            margin-bottom: 15px;
        }

        .recommendations ul {
            margin-left: 20px;
        }

        .recommendations li {
            margin-bottom: 8px;
            color: #495057;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .print-button:hover {
            background: #5a6fd8;
        }

        @media print {
            .print-button {
                display: none;
            }
            
            .container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            
            .header {
                margin: 0;
            }
            
            .section {
                page-break-inside: avoid;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header {
                margin: -10px -10px 20px -10px;
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .summary-grid {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .test-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>';

    // Add header
    $html .= '<div class="container">
        <div class="header">
            <h1>PK Live News</h1>
            <p>Comprehensive Connection Test Report</p>
            <p style="font-size: 0.9em; margin-top: 10px;">Generated on ' . date('Y-m-d H:i:s') . '</p>
        </div>';

    // Add summary section
    if (isset($results['summary'])) {
        $html .= '<div class="section">
            <h2>Executive Summary</h2>
            <div class="summary-grid">
                <div class="summary-card">
                    <h3>' . ($results['summary']['total_tests'] ?? 0) . '</h3>
                    <p>Total Tests</p>
                </div>
                <div class="summary-card success">
                    <h3>' . ($results['summary']['passed_tests'] ?? 0) . '</h3>
                    <p>Passed</p>
                </div>
                <div class="summary-card warning">
                    <h3>' . ($results['summary']['warnings'] ?? 0) . '</h3>
                    <p>Warnings</p>
                </div>
                <div class="summary-card danger">
                    <h3>' . ($results['summary']['failed_tests'] ?? 0) . '</h3>
                    <p>Failed</p>
                </div>
            </div>';

        // Add success rate
        if (isset($results['summary']['success_rate'])) {
            $html .= '<div style="text-align: center; margin: 20px 0;">
                <h3 style="color: #2c3e50;">Overall Success Rate: ' . $results['summary']['success_rate'] . '%</h3>
                <div style="width: 100%; height: 30px; background: #e9ecef; border-radius: 15px; overflow: hidden; margin-top: 10px;">
                    <div style="width: ' . $results['summary']['success_rate'] . '%; height: 100%; background: linear-gradient(90deg, #28a745, #20c997);"></div>
                </div>
            </div>';
        }

        $html .= '</div>';
    }

    // Add server information
    if (isset($results['server_info'])) {
        $html .= '<div class="section">
            <h2>Server Information</h2>
            <div class="server-info">
                <div class="info-grid">';

        foreach ($results['server_info'] as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));
            $html .= '<div class="info-item">
                <span class="info-label">' . $label . '</span>
                <span class="info-value">' . htmlspecialchars($value) . '</span>
            </div>';
        }

        $html .= '</div></div></div>';
    }

    // Add detailed test results
    if (isset($results['tests']) && is_array($results['tests'])) {
        $html .= '<div class="section">
            <h2>Detailed Test Results</h2>';

        foreach ($results['tests'] as $test) {
            $status = $test['status'] ?? 'unknown';
            $html .= '<div class="test-item ' . $status . '">
                <div class="test-header">
                    <div class="test-title">' . htmlspecialchars($test['name'] ?? 'Unknown Test') . '</div>
                    <div class="test-status ' . $status . '">' . $status . '</div>
                </div>
                <div class="test-message">' . htmlspecialchars($test['message'] ?? 'No message available') . '</div>';

            if (isset($test['details']) && !empty($test['details'])) {
                $html .= '<div class="test-details">
                    <h4>Technical Details</h4>
                    <div class="detail-grid">';

                foreach ($test['details'] as $key => $value) {
                    if (is_array($value)) {
                        $html .= '<div class="detail-item">
                            <span class="detail-label">' . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . '</span>
                            <span class="detail-value">' . htmlspecialchars(json_encode($value)) . '</span>
                        </div>';
                    } else {
                        $html .= '<div class="detail-item">
                            <span class="detail-label">' . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . '</span>
                            <span class="detail-value">' . htmlspecialchars($value) . '</span>
                        </div>';
                    }
                }

                $html .= '</div></div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
    }

    // Add recommendations section
    $html .= '<div class="section">
        <h2>Recommendations</h2>
        <div class="recommendations">
            <h4>Based on the test results, here are our recommendations:</h4>
            <ul>';

    // Generate recommendations based on test results
    if (isset($results['summary'])) {
        if ($results['summary']['failed_tests'] > 0) {
            $html .= '<li><strong>Priority 1:</strong> Address all failed tests immediately as they may affect system functionality</li>';
        }
        if ($results['summary']['warnings'] > 0) {
            $html .= '<li><strong>Priority 2:</strong> Review and resolve warnings to improve system security and performance</li>';
        }
        if ($results['summary']['success_rate'] < 100) {
            $html .= '<li><strong>Priority 3:</strong> Aim for 100% test success rate for optimal system health</li>';
        }
    }

    $html .= '<li>Regularly run connection tests to monitor system health</li>
            <li>Keep all system components and dependencies updated</li>
            <li>Monitor logs for any recurring issues or patterns</li>
            <li>Implement automated monitoring for critical services</li>
            <li>Document and track all configuration changes</li>
            </ul>
        </div>
    </div>';

    // Add footer
    $html .= '<div class="footer">
        <p>PK Live News Connection Test Report</p>
        <p>Generated on ' . date('Y-m-d H:i:s') . ' | Report ID: ' . uniqid() . '</p>
    </div>';

    $html .= '</div>
    <button class="print-button" onclick="window.print()">Print Report</button>
    </body>
    </html>';

    return $html;
}

// Main execution
$results = getTestResults();

if (isset($results['error'])) {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error - Connection Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f8f9fa; }
        .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; border: 1px solid #f5c6cb; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <h1>Connection Test Report Error</h1>
        <div class="error">
            <strong>Error:</strong> ' . htmlspecialchars($results['error']) . '
        </div>';
    
    if (isset($results['raw_output'])) {
        echo '<h3>Raw Output:</h3>
        <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;">' . htmlspecialchars($results['raw_output']) . '</pre>';
    }
    
    echo '</div></body></html>';
} else {
    echo generateHTMLReport($results);
}
?>
