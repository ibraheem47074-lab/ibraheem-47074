<?php
/**
 * Reports API
 * Handles AJAX requests for fake news reports management
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

$response = ['success' => false, 'message' => ''];

try {
    $action = $_GET['action'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        case 'get_details':
            $reportId = $_GET['id'] ?? 0;
            
            $sql = "SELECT ufr.*, n.title, n.content, n.url_slug, n.source_url, n.source_name,
                    n.credibility_score, n.risk_level, n.content_category
                    FROM user_fake_news_reports ufr
                    JOIN news n ON ufr.news_id = n.id
                    WHERE ufr.id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $reportId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($report = $result->fetch_assoc()) {
                $html = generateReportDetailsHTML($report);
                
                $response = [
                    'success' => true,
                    'html' => $html,
                    'report' => $report
                ];
            } else {
                $response['message'] = 'Report not found';
            }
            break;

        case 'get_statistics':
            $timeframe = $_GET['timeframe'] ?? '30';
            
            $sql = "SELECT 
                DATE(created_at) as report_date,
                COUNT(*) as report_count,
                SUM(CASE WHEN report_reason = 'FALSE_INFORMATION' THEN 1 ELSE 0 END) as false_info_count,
                SUM(CASE WHEN report_reason = 'MISLEADING' THEN 1 ELSE 0 END) as misleading_count,
                SUM(CASE WHEN report_status = 'VALIDATED' THEN 1 ELSE 0 END) as validated_count
                FROM user_fake_news_reports 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY report_date DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $timeframe);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $statistics = [];
            while ($row = $result->fetch_assoc()) {
                $statistics[] = $row;
            }
            
            $response = [
                'success' => true,
                'statistics' => $statistics
            ];
            break;

        case 'get_top_reported_articles':
            $limit = $_GET['limit'] ?? 10;
            
            $sql = "SELECT n.id, n.title, n.url_slug, COUNT(ufr.id) as report_count,
                    AVG(n.credibility_score) as avg_credibility,
                    SUM(CASE WHEN ufr.report_status = 'VALIDATED' THEN 1 ELSE 0 END) as validated_count
                    FROM news n
                    JOIN user_fake_news_reports ufr ON n.id = ufr.news_id
                    WHERE ufr.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY n.id, n.title, n.url_slug
                    HAVING report_count > 0
                    ORDER BY report_count DESC
                    LIMIT ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $articles = [];
            while ($row = $result->fetch_assoc()) {
                $articles[] = $row;
            }
            
            $response = [
                'success' => true,
                'articles' => $articles
            ];
            break;

        case 'export_reports':
            $format = $_GET['format'] ?? 'csv';
            $status = $_GET['status'] ?? 'all';
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            
            $whereClause = "WHERE 1=1";
            $params = [];
            $types = "";
            
            if ($status !== 'all') {
                $whereClause .= " AND report_status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if (!empty($dateFrom)) {
                $whereClause .= " AND created_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
                $types .= "s";
            }
            
            if (!empty($dateTo)) {
                $whereClause .= " AND created_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
                $types .= "s";
            }
            
            $sql = "SELECT ufr.*, n.title, n.url_slug
                    FROM user_fake_news_reports ufr
                    JOIN news n ON ufr.news_id = n.id
                    $whereClause
                    ORDER BY ufr.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($format === 'csv') {
                $csv = "ID,Article Title,Report Reason,Report Details,Reporter IP,Status,Created At,Reviewed At\n";
                
                while ($row = $result->fetch_assoc()) {
                    $csv .= sprintf(
                        "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                        $row['id'],
                        str_replace('"', '""', $row['title']),
                        $row['report_reason'],
                        str_replace('"', '""', $row['report_details'] ?? ''),
                        $row['reporter_ip'],
                        $row['report_status'],
                        $row['created_at'],
                        $row['reviewed_at'] ?? ''
                    );
                }
                
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="fake_news_reports_' . date('Y-m-d') . '.csv"');
                echo $csv;
                exit;
            } else {
                // JSON export
                $reports = [];
                while ($row = $result->fetch_assoc()) {
                    $reports[] = $row;
                }
                
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="fake_news_reports_' . date('Y-m-d') . '.json"');
                echo json_encode($reports, JSON_PRETTY_PRINT);
                exit;
            }
            break;

        case 'update_report':
            $reportId = $data['report_id'] ?? 0;
            $status = $data['status'] ?? '';
            $adminNotes = $data['admin_notes'] ?? '';
            
            if (empty($reportId) || empty($status)) {
                $response['message'] = 'Report ID and status are required';
                break;
            }
            
            $sql = "UPDATE user_fake_news_reports 
                    SET report_status = ?, admin_notes = ?, reviewed_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $status, $adminNotes, $reportId);
            
            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'Report updated successfully'
                ];
                
                // Check if this affects article credibility
                updateArticleCredibilityFromReports($reportId, $conn);
            } else {
                $response['message'] = 'Error updating report: ' . $conn->error;
            }
            break;

        case 'delete_report':
            $reportId = $data['report_id'] ?? 0;
            
            $sql = "DELETE FROM user_fake_news_reports WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $reportId);
            
            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'Report deleted successfully'
                ];
            } else {
                $response['message'] = 'Error deleting report: ' . $conn->error;
            }
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);

function generateReportDetailsHTML($report) {
    $evidenceUrls = json_decode($report['evidence_urls'] ?: '[]', true);
    
    $html = '<div class="row">';
    $html .= '<div class="col-md-6">';
    $html .= '<h6><i class="fas fa-newspaper"></i> Article Information</h6>';
    $html .= '<table class="table table-sm">';
    $html .= '<tr><td>Title:</td><td><strong>' . htmlspecialchars($report['title']) . '</strong></td></tr>';
    $html .= '<tr><td>URL:</td><td><a href="../news.php?slug=' . $report['url_slug'] . '" target="_blank">View Article</a></td></tr>';
    $html .= '<tr><td>Source:</td><td>' . htmlspecialchars($report['source_name'] ?: 'Unknown') . '</td></tr>';
    if ($report['credibility_score']) {
        $html .= '<tr><td>Credibility:</td><td><span class="badge bg-' . getCredibilityColor($report['credibility_score']) . '">' . number_format($report['credibility_score'], 0) . '%</span></td></tr>';
    }
    $html .= '</table>';
    $html .= '</div>';
    
    $html .= '<div class="col-md-6">';
    $html .= '<h6><i class="fas fa-flag"></i> Report Information</h6>';
    $html .= '<table class="table table-sm">';
    $html .= '<tr><td>Reason:</td><td><span class="badge bg-' . getReasonColor($report['report_reason']) . '">' . $report['report_reason'] . '</span></td></tr>';
    $html .= '<tr><td>Reporter IP:</td><td>' . htmlspecialchars($report['reporter_ip']) . '</td></tr>';
    $html .= '<tr><td>Status:</td><td><span class="badge bg-' . getStatusColor($report['report_status']) . '">' . $report['report_status'] . '</span></td></tr>';
    $html .= '<tr><td>Created:</td><td>' . date('M j, Y H:i', strtotime($report['created_at'])) . '</td></tr>';
    if ($report['reviewed_at']) {
        $html .= '<tr><td>Reviewed:</td><td>' . date('M j, Y H:i', strtotime($report['reviewed_at'])) . '</td></tr>';
    }
    $html .= '</table>';
    $html .= '</div>';
    $html .= '</div>';
    
    if (!empty($report['report_details'])) {
        $html .= '<div class="row mt-3">';
        $html .= '<div class="col-12">';
        $html .= '<h6><i class="fas fa-comment"></i> Report Details</h6>';
        $html .= '<div class="alert alert-info">' . htmlspecialchars($report['report_details']) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    if (!empty($evidenceUrls)) {
        $html .= '<div class="row mt-3">';
        $html .= '<div class="col-12">';
        $html .= '<h6><i class="fas fa-link"></i> Evidence URLs</h6>';
        $html .= '<ul class="list-unstyled">';
        foreach ($evidenceUrls as $url) {
            $html .= '<li><a href="' . htmlspecialchars($url) . '" target="_blank">' . htmlspecialchars($url) . '</a></li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    if (!empty($report['admin_notes'])) {
        $html .= '<div class="row mt-3">';
        $html .= '<div class="col-12">';
        $html .= '<h6><i class="fas fa-sticky-note"></i> Admin Notes</h6>';
        $html .= '<div class="alert alert-warning">' . htmlspecialchars($report['admin_notes']) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    return $html;
}

function getCredibilityColor($score) {
    if ($score >= 80) return 'success';
    if ($score >= 60) return 'info';
    if ($score >= 40) return 'warning';
    return 'danger';
}

function getReasonColor($reason) {
    $colors = [
        'MISLEADING' => 'warning',
        'FALSE_INFORMATION' => 'danger',
        'BIASED' => 'info',
        'CLICKBAIT' => 'secondary',
        'SPAM' => 'dark',
        'OTHER' => 'light'
    ];
    return $colors[$reason] ?? 'secondary';
}

function getStatusColor($status) {
    $colors = [
        'PENDING' => 'warning',
        'VALIDATED' => 'success',
        'DISMISSED' => 'danger',
        'REVIEWING' => 'info'
    ];
    return $colors[$status] ?? 'secondary';
}

function updateArticleCredibilityFromReports($reportId, $db) {
    // Get the news ID and check report patterns
    $sql = "SELECT news_id FROM user_fake_news_reports WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $reportId);
    $stmt->execute();
    $result = $stmt->get_result();
    $newsId = $result->fetch_assoc()['news_id'];
    
    // Count validated reports for this article
    $sql = "SELECT COUNT(*) as validated_count FROM user_fake_news_reports 
            WHERE news_id = ? AND report_status = 'VALIDATED'";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $newsId);
    $stmt->execute();
    $result = $stmt->get_result();
    $validatedCount = $result->fetch_assoc()['validated_count'];
    
    // If multiple validated reports, create an alert
    if ($validatedCount >= 2) {
        $sql = "INSERT INTO fake_news_alerts (news_id, alert_type, severity, message, status) 
                VALUES (?, 'MULTIPLE_REPORTS', 'WARNING', ?, 'ACTIVE')
                ON DUPLICATE KEY UPDATE message = VALUES(message)";
        
        $message = "Article has {$validatedCount} validated user reports. Strongly recommended for review.";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("is", $newsId, $message);
        $stmt->execute();
    }
}
?>
