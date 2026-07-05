<?php
/**
 * Analysis Details API
 * Provides detailed analysis information for AI Fake News Detection
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/database.php';

$response = ['success' => false, 'message' => ''];

try {
    $newsId = $_GET['news_id'] ?? 0;
    
    if ($newsId <= 0) {
        $response['message'] = 'Invalid news ID';
        echo json_encode($response);
        exit;
    }
    
    // Get analysis details
    $sql = "SELECT nca.*, n.title, n.content, n.source_url, n.source_name
            FROM news_credibility_analysis nca
            JOIN news n ON nca.news_id = n.id
            WHERE nca.news_id = ?
            ORDER BY nca.analysis_date DESC
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $newsId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($analysis = $result->fetch_assoc()) {
        // Get alerts for this article
        $alertsSql = "SELECT * FROM fake_news_alerts 
                      WHERE news_id = ? AND status = 'ACTIVE'
                      ORDER BY created_at DESC";
        $alertsStmt = $conn->prepare($alertsSql);
        $alertsStmt->bind_param("i", $newsId);
        $alertsStmt->execute();
        $alerts = $alertsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get fact check references
        $refsSql = "SELECT * FROM fact_check_references 
                   WHERE news_id = ?
                   ORDER BY relevance_score DESC";
        $refsStmt = $conn->prepare($refsSql);
        $refsStmt->bind_param("i", $newsId);
        $refsStmt->execute();
        $references = $refsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Generate HTML response
        $html = generateAnalysisDetailsHTML($analysis, $alerts, $references);
        
        $response = [
            'success' => true,
            'html' => $html,
            'analysis' => $analysis,
            'alerts' => $alerts,
            'references' => $references
        ];
    } else {
        $response['message'] = 'Analysis not found';
    }
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);

function generateAnalysisDetailsHTML($analysis, $alerts, $references) {
    $html = '
    <div class="row">
        <div class="col-md-6">
            <h6><i class="fas fa-newspaper"></i> Article Information</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Title:</strong></td>
                    <td>' . htmlspecialchars($analysis['title']) . '</td>
                </tr>
                <tr>
                    <td><strong>Source:</strong></td>
                    <td>' . htmlspecialchars($analysis['source_name'] ?: 'Unknown') . '</td>
                </tr>
                <tr>
                    <td><strong>Source URL:</strong></td>
                    <td><a href="' . htmlspecialchars($analysis['source_url']) . '" target="_blank">' . 
                         htmlspecialchars(parse_url($analysis['source_url'], PHP_URL_HOST) ?: 'N/A') . '</a></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6><i class="fas fa-chart-line"></i> Analysis Summary</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Overall Score:</strong></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                <div class="progress-bar bg-' . getScoreColor($analysis['credibility_score']) . '" 
                                     style="width: ' . $analysis['credibility_score'] . '%"></div>
                            </div>
                            <strong>' . number_format($analysis['credibility_score'], 1) . '%</strong>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><strong>Risk Level:</strong></td>
                    <td><span class="badge bg-' . getRiskColor($analysis['risk_level']) . '">' . 
                         $analysis['risk_level'] . '</span></td>
                </tr>
                <tr>
                    <td><strong>Category:</strong></td>
                    <td><span class="badge bg-' . getCategoryColor($analysis['content_category']) . '">' . 
                         $analysis['content_category'] . '</span></td>
                </tr>
                <tr>
                    <td><strong>Confidence:</strong></td>
                    <td>' . number_format($analysis['confidence_level'], 1) . '%</td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <h6><i class="fas fa-microscope"></i> Content Analysis</h6>
            <div class="analysis-metrics">
                <div class="metric-item">
                    <span>Title Credibility:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getScoreColor($analysis['title_credibility']) . '" 
                             style="width: ' . $analysis['title_credibility'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['title_credibility'], 1) . '%</small>
                </div>
                <div class="metric-item">
                    <span>Content Credibility:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getScoreColor($analysis['content_credibility']) . '" 
                             style="width: ' . $analysis['content_credibility'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['content_credibility'], 1) . '%</small>
                </div>
                <div class="metric-item">
                    <span>Source Credibility:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getScoreColor($analysis['source_credibility']) . '" 
                             style="width: ' . $analysis['source_credibility'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['source_credibility'], 1) . '%</small>
                </div>
                <div class="metric-item">
                    <span>Factual Accuracy:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getScoreColor($analysis['factual_accuracy']) . '" 
                             style="width: ' . $analysis['factual_accuracy'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['factual_accuracy'], 1) . '%</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <h6><i class="fas fa-exclamation-triangle"></i> Risk Indicators</h6>
            <div class="analysis-metrics">
                <div class="metric-item">
                    <span>Sensationalism:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getRiskIndicatorColor($analysis['sensationalism_score']) . '" 
                             style="width: ' . $analysis['sensationalism_score'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['sensationalism_score'], 1) . '%</small>
                </div>
                <div class="metric-item">
                    <span>Emotional Manipulation:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getRiskIndicatorColor($analysis['emotional_manipulation']) . '" 
                             style="width: ' . $analysis['emotional_manipulation'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['emotional_manipulation'], 1) . '%</small>
                </div>
                <div class="metric-item">
                    <span>Clickbait Score:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getRiskIndicatorColor($analysis['clickbait_score']) . '" 
                             style="width: ' . $analysis['clickbait_score'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['clickbait_score'], 1) . '%</small>
                </div>
                <div class="metric-item">
                    <span>Propaganda Indicators:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getRiskIndicatorColor($analysis['propaganda_indicators']) . '" 
                             style="width: ' . $analysis['propaganda_indicators'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['propaganda_indicators'], 1) . '%</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <h6><i class="fas fa-cogs"></i> Technical Analysis</h6>
            <div class="analysis-metrics">
                <div class="metric-item">
                    <span>Grammar Score:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getScoreColor($analysis['grammar_score']) . '" 
                             style="width: ' . $analysis['grammar_score'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['grammar_score'], 1) . '%</small>
                </div>
                <div class="metric-item">
                    <span>Readability Score:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getScoreColor($analysis['readability_score']) . '" 
                             style="width: ' . $analysis['readability_score'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['readability_score'], 1) . '%</small>
                </div>
                <div class="metric-item">
                    <span>Factual Density:</span>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar bg-' . getScoreColor($analysis['factual_density']) . '" 
                             style="width: ' . $analysis['factual_density'] . '%"></div>
                    </div>
                    <small>' . number_format($analysis['factual_density'], 1) . '%</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <h6><i class="fas fa-shield-alt"></i> Source Verification</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Source Verified:</strong></td>
                    <td>' . ($analysis['source_verified'] ? 
                        '<span class="badge bg-success">Verified</span>' : 
                        '<span class="badge bg-warning">Unverified</span>') . '</td>
                </tr>
                <tr>
                    <td><strong>Reputation Score:</strong></td>
                    <td>' . number_format($analysis['source_reputation_score'], 1) . '%</td>
                </tr>
                <tr>
                    <td><strong>Cross References:</strong></td>
                    <td>' . $analysis['cross_reference_count'] . '</td>
                </tr>
            </table>
        </div>
    </div>';
    
    if (!empty($alerts)) {
        $html .= '
        <div class="row mt-3">
            <div class="col-12">
                <h6><i class="fas fa-bell"></i> Active Alerts</h6>
                <div class="alert-list">';
        
        foreach ($alerts as $alert) {
            $html .= '
                <div class="alert alert-' . getAlertSeverityColor($alert['severity']) . ' alert-sm">
                    <strong>' . $alert['alert_type'] . '</strong> - ' . $alert['message'] . '
                    <br><small>Created: ' . date('M j, Y H:i', strtotime($alert['created_at'])) . '</small>
                </div>';
        }
        
        $html .= '
                </div>
            </div>
        </div>';
    }
    
    if (!empty($references)) {
        $html .= '
        <div class="row mt-3">
            <div class="col-12">
                <h6><i class="fas fa-link"></i> Fact Check References</h6>
                <div class="reference-list">';
        
        foreach ($references as $ref) {
            $html .= '
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong><a href="' . htmlspecialchars($ref['reference_url']) . '" target="_blank">' . 
                                 htmlspecialchars($ref['reference_title']) . '</a></strong>
                            <br><small>Source: ' . htmlspecialchars($ref['reference_source']) . '</small>
                        </div>
                        <div>
                            <span class="badge bg-' . getReferenceStatusColor($ref['verification_status']) . '">' . 
                                 $ref['verification_status'] . '</span>
                            <br><small>Relevance: ' . number_format($ref['relevance_score'], 1) . '%</small>
                        </div>
                    </div>
                </div>';
        }
        
        $html .= '
                </div>
            </div>
        </div>';
    }
    
    $html .= '
    <div class="row mt-3">
        <div class="col-12">
            <h6><i class="fas fa-info-circle"></i> Analysis Information</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Analysis Date:</strong></td>
                    <td>' . date('M j, Y H:i:s', strtotime($analysis['analysis_date'])) . '</td>
                </tr>
                <tr>
                    <td><strong>Analysis Method:</strong></td>
                    <td>' . htmlspecialchars($analysis['analysis_method']) . '</td>
                </tr>
                <tr>
                    <td><strong>AI Model Version:</strong></td>
                    <td>' . htmlspecialchars($analysis['ai_model_version']) . '</td>
                </tr>
                <tr>
                    <td><strong>Processing Time:</strong></td>
                    <td>' . $analysis['processing_time_ms'] . 'ms</td>
                </tr>
                <tr>
                    <td><strong>Requires Review:</strong></td>
                    <td>' . ($analysis['requires_review'] ? 
                        '<span class="badge bg-warning">Yes</span>' : 
                        '<span class="badge bg-success">No</span>') . '</td>
                </tr>
                <tr>
                    <td><strong>Auto Flagged:</strong></td>
                    <td>' . ($analysis['auto_flagged'] ? 
                        '<span class="badge bg-danger">Yes</span>' : 
                        '<span class="badge bg-success">No</span>') . '</td>
                </tr>
            </table>
        </div>
    </div>';
    
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

function getRiskIndicatorColor($score) {
    if ($score >= 70) return 'danger';
    if ($score >= 40) return 'warning';
    return 'info';
}

function getAlertSeverityColor($severity) {
    $colors = [
        'INFO' => 'info',
        'WARNING' => 'warning',
        'CRITICAL' => 'danger'
    ];
    return $colors[$severity] ?? 'secondary';
}

function getReferenceStatusColor($status) {
    $colors = [
        'VERIFIED' => 'success',
        'DISPUTED' => 'warning',
        'DEBUNKED' => 'danger',
        'UNVERIFIED' => 'secondary'
    ];
    return $colors[$status] ?? 'secondary';
}
?>
