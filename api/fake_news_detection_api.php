<?php
/**
 * Fake News Detection API
 * Handles AJAX requests for fake news detection functionality
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/ai_fake_news_detector.php';

// Initialize detector
$detector = new AIFakeNewsDetector($conn);

$response = ['success' => false, 'message' => ''];

try {
    $action = $_GET['action'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        case 'get_score':
            $newsId = $_GET['news_id'] ?? 0;
            $analysis = $detector->getCredibilityReport($newsId);
            
            if ($analysis) {
                $response = [
                    'success' => true,
                    'credibility_score' => $analysis['credibility_score'],
                    'risk_level' => $analysis['risk_level'],
                    'content_category' => $analysis['content_category'],
                    'source_verified' => $analysis['source_verified'],
                    'confidence_level' => $analysis['confidence_level']
                ];
            } else {
                $response['message'] = 'Analysis not found';
            }
            break;

        case 'get_details':
            $newsId = $_GET['news_id'] ?? 0;
            
            // Get detailed analysis
            $sql = "SELECT nca.*, n.title, n.content, n.source_url, n.source_name
                    FROM news_credibility_analysis nca
                    JOIN news n ON nca.news_id = n.id
                    WHERE nca.news_id = ?
                    ORDER BY nca.analysis_date DESC
                    LIMIT 1";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $newsId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($analysis = $result->fetch_assoc()) {
                // Get alerts
                $alertsSql = "SELECT * FROM fake_news_alerts 
                              WHERE news_id = ? AND status = 'ACTIVE'
                              ORDER BY created_at DESC";
                $alertsStmt = mysqli_prepare($conn, $alertsSql);
                mysqli_stmt_bind_param($alertsStmt, "i", $newsId);
                mysqli_stmt_execute($alertsStmt);
                $alerts = $alertsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                // Generate HTML
                $html = generateAnalysisHTML($analysis, $alerts);
                
                $response = [
                    'success' => true,
                    'html' => $html,
                    'analysis' => $analysis
                ];
            } else {
                $response['message'] = 'Analysis not found';
            }
            break;

        case 'analyze':
            $newsId = $_GET['news_id'] ?? 0;
            $result = $detector->analyzeArticle($newsId);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Article analyzed successfully',
                    'credibility_score' => $result['credibility_score'],
                    'risk_level' => $result['risk_level']
                ];
            } else {
                $response['message'] = 'Error analyzing article';
            }
            break;

        case 'submit_report':
            $newsId = $data['news_id'] ?? 0;
            $reportReason = $data['report_reason'] ?? '';
            $reportDetails = $data['report_details'] ?? '';
            $evidenceUrls = $data['evidence_urls'] ?? '';
            
            if (empty($newsId) || empty($reportReason)) {
                $response['message'] = 'News ID and report reason are required';
                break;
            }
            
            // Get user IP
            $reporterIp = $_SERVER['REMOTE_ADDR'] ?? '';
            
            // Parse evidence URLs
            $evidenceArray = [];
            if (!empty($evidenceUrls)) {
                $lines = explode("\n", trim($evidenceUrls));
                foreach ($lines as $line) {
                    $url = trim($line);
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        $evidenceArray[] = $url;
                    }
                }
            }
            
            $evidenceJson = json_encode($evidenceArray);
            
            // Insert report
            $sql = "INSERT INTO user_fake_news_reports 
                    (news_id, reporter_ip, report_reason, report_details, evidence_urls) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "issss", $newsId, $reporterIp, $reportReason, $reportDetails, $evidenceJson);
            
            if (mysqli_stmt_execute($stmt)) {
                $response = [
                    'success' => true,
                    'message' => 'Report submitted successfully'
                ];
                
                // Check if this creates a pattern
                checkReportPatterns($newsId, $conn);
            } else {
                $response['message'] = 'Error submitting report: ' . mysqli_error($conn);
            }
            break;

        case 'get_user_reports':
            $newsId = $_GET['news_id'] ?? 0;
            
            $sql = "SELECT * FROM user_fake_news_reports 
                    WHERE news_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 10";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $newsId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $reports = [];
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
            }
            
            $response = [
                'success' => true,
                'reports' => $reports,
                'count' => count($reports)
            ];
            break;

        case 'get_trusted_sources':
            $domain = $_GET['domain'] ?? '';
            
            if (empty($domain)) {
                $response['message'] = 'Domain is required';
                break;
            }
            
            $sql = "SELECT * FROM trusted_sources 
                    WHERE domain_name = ? AND active = 1";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $domain);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($source = $result->fetch_assoc()) {
                $response = [
                    'success' => true,
                    'source' => $source
                ];
            } else {
                $response['message'] = 'Source not found in trusted database';
            }
            break;

        case 'get_article_stats':
            $newsId = $_GET['news_id'] ?? 0;
            
            $sql = "SELECT 
                nca.credibility_score,
                nca.risk_level,
                nca.content_category,
                nca.source_verified,
                COUNT(fna.id) as alert_count,
                COUNT(ufr.id) as report_count
                FROM news n
                LEFT JOIN news_credibility_analysis nca ON n.id = nca.news_id
                LEFT JOIN fake_news_alerts fna ON n.id = fna.news_id AND fna.status = 'ACTIVE'
                LEFT JOIN user_fake_news_reports ufr ON n.id = ufr.news_id
                WHERE n.id = ?
                GROUP BY n.id";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $newsId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($stats = $result->fetch_assoc()) {
                $response = [
                    'success' => true,
                    'stats' => $stats
                ];
            } else {
                $response['message'] = 'Article not found';
            }
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);

function generateAnalysisHTML($analysis, $alerts) {
    $html = '<div class="row">';
    $html .= '<div class="col-md-6">';
    $html .= '<h6><i class="fas fa-chart-line"></i> Credibility Analysis</h6>';
    $html .= '<table class="table table-sm">';
    $html .= '<tr><td>Overall Score:</td><td><strong>' . number_format($analysis['credibility_score'], 1) . '%</strong></td></tr>';
    $html .= '<tr><td>Risk Level:</td><td><span class="badge bg-' . getRiskColor($analysis['risk_level']) . '">' . $analysis['risk_level'] . '</span></td></tr>';
    $html .= '<tr><td>Category:</td><td><span class="badge bg-' . getCategoryColor($analysis['content_category']) . '">' . $analysis['content_category'] . '</span></td></tr>';
    $html .= '<tr><td>Confidence:</td><td>' . number_format($analysis['confidence_level'], 1) . '%</td></tr>';
    $html .= '<tr><td>Source Verified:</td><td>' . ($analysis['source_verified'] ? '<i class="fas fa-check text-success"></i> Yes' : '<i class="fas fa-times text-danger"></i> No') . '</td></tr>';
    $html .= '</table>';
    $html .= '</div>';
    
    $html .= '<div class="col-md-6">';
    $html .= '<h6><i class="fas fa-microscope"></i> Content Analysis</h6>';
    $html .= '<div class="analysis-metrics">';
    $html .= '<div class="metric-item"><span>Title Credibility:</span><div class="progress mt-1"><div class="progress-bar bg-' . getScoreColor($analysis['title_credibility']) . '" style="width: ' . $analysis['title_credibility'] . '%"></div></div><small>' . number_format($analysis['title_credibility'], 1) . '%</small></div>';
    $html .= '<div class="metric-item"><span>Content Credibility:</span><div class="progress mt-1"><div class="progress-bar bg-' . getScoreColor($analysis['content_credibility']) . '" style="width: ' . $analysis['content_credibility'] . '%"></div></div><small>' . number_format($analysis['content_credibility'], 1) . '%</small></div>';
    $html .= '<div class="metric-item"><span>Source Credibility:</span><div class="progress mt-1"><div class="progress-bar bg-' . getScoreColor($analysis['source_credibility']) . '" style="width: ' . $analysis['source_credibility'] . '%"></div></div><small>' . number_format($analysis['source_credibility'], 1) . '%</small></div>';
    $html .= '<div class="metric-item"><span>Factual Accuracy:</span><div class="progress mt-1"><div class="progress-bar bg-' . getScoreColor($analysis['factual_accuracy']) . '" style="width: ' . $analysis['factual_accuracy'] . '%"></div></div><small>' . number_format($analysis['factual_accuracy'], 1) . '%</small></div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    if (!empty($alerts)) {
        $html .= '<div class="row mt-3">';
        $html .= '<div class="col-12">';
        $html .= '<h6><i class="fas fa-exclamation-triangle"></i> Active Alerts</h6>';
        foreach ($alerts as $alert) {
            $html .= '<div class="alert alert-' . getAlertSeverityColor($alert['severity']) . ' alert-sm">';
            $html .= '<strong>' . $alert['alert_type'] . '</strong> - ' . $alert['message'];
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
    }
    
    return $html;
}

function getScoreColor($score) {
    if ($score >= 80) return 'success';
    if ($score >= 60) return 'info';
    if ($score >= 40) return 'warning';
    return 'danger';
}

function getRiskColor($level) {
    $colors = [
        'LOW' => 'success',
        'MEDIUM' => 'info',
        'HIGH' => 'warning',
        'CRITICAL' => 'danger'
    ];
    return $colors[$level] ?? 'secondary';
}

function getCategoryColor($category) {
    $colors = [
        'VERIFIED' => 'success',
        'LIKELY_TRUE' => 'info',
        'UNVERIFIED' => 'primary',
        'LIKELY_FALSE' => 'warning',
        'FALSE' => 'danger'
    ];
    return $colors[$category] ?? 'secondary';
}

function getAlertSeverityColor($severity) {
    $colors = [
        'INFO' => 'info',
        'WARNING' => 'warning',
        'CRITICAL' => 'danger'
    ];
    return $colors[$severity] ?? 'secondary';
}

function checkReportPatterns($newsId, $conn) {
    // Check if this article has multiple reports
    $sql = "SELECT COUNT(*) as report_count FROM user_fake_news_reports WHERE news_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $newsId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = $result->fetch_assoc()['report_count'];
    
    // If more than 3 reports, create an alert
    if ($count >= 3) {
        $sql = "INSERT INTO fake_news_alerts (news_id, alert_type, severity, message, status) 
                VALUES (?, 'USER_REPORTS', 'WARNING', ?, 'ACTIVE')";
        
        $message = "Article has received {$count} user reports. Review recommended.";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $newsId, $message);
        mysqli_stmt_execute($stmt);
    }
}
?>
