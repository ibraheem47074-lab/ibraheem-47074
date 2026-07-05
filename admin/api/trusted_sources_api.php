<?php
/**
 * Trusted Sources API
 * Handles AJAX requests for trusted sources management
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
        case 'get_source':
            $sourceId = $_GET['id'] ?? 0;
            $sql = "SELECT * FROM trusted_sources WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $sourceId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($source = $result->fetch_assoc()) {
                $response = ['success' => true, 'source' => $source];
            } else {
                $response['message'] = 'Source not found';
            }
            break;

        case 'toggle_status':
            $sourceId = $data['source_id'] ?? 0;
            $isActive = $data['active'] ?? false;
            
            $sql = "UPDATE trusted_sources SET active = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $isActive, $sourceId);
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Source status updated successfully'];
            } else {
                $response['message'] = 'Error updating source status: ' . $conn->error;
            }
            break;

        case 'blacklist':
            $sourceId = $data['source_id'] ?? 0;
            
            $sql = "UPDATE trusted_sources SET blacklisted = 1, active = 0 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $sourceId);
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Source blacklisted successfully'];
            } else {
                $response['message'] = 'Error blacklisting source: ' . $conn->error;
            }
            break;

        case 'verify_source':
            $sourceId = $data['source_id'] ?? 0;
            
            $sql = "UPDATE trusted_sources SET verified = 1, verification_date = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $sourceId);
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Source verified successfully'];
            } else {
                $response['message'] = 'Error verifying source: ' . $conn->error;
            }
            break;

        case 'update_scores':
            $sourceId = $data['source_id'] ?? 0;
            $trustScore = $data['trust_score'] ?? 50;
            $reliabilityScore = $data['reliability_score'] ?? 50;
            $accuracyScore = $data['accuracy_score'] ?? 50;
            
            $sql = "UPDATE trusted_sources SET 
                    trust_score = ?, reliability_score = ?, accuracy_score = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dddi", $trustScore, $reliabilityScore, $accuracyScore, $sourceId);
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Scores updated successfully'];
            } else {
                $response['message'] = 'Error updating scores: ' . $conn->error;
            }
            break;

        case 'delete_source':
            $sourceId = $data['source_id'] ?? 0;
            
            // Check if source is used in any news articles
            $checkSql = "SELECT COUNT(*) as count FROM news WHERE source_url LIKE ?";
            $stmt = $conn->prepare($checkSql);
            $domain = '%' . $sourceId . '%'; // This is a simplified check
            $stmt->bind_param("s", $domain);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                $response['message'] = 'Cannot delete source: It is referenced by news articles';
            } else {
                $sql = "DELETE FROM trusted_sources WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $sourceId);
                
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Source deleted successfully'];
                } else {
                    $response['message'] = 'Error deleting source: ' . $conn->error;
                }
            }
            break;

        case 'get_statistics':
            $sql = "SELECT 
                COUNT(*) as total_sources,
                SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_sources,
                SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_sources,
                SUM(CASE WHEN blacklisted = 1 THEN 1 ELSE 0 END) as blacklisted_sources,
                AVG(trust_score) as avg_trust_score,
                COUNT(CASE WHEN credibility_tier = 'TIER_1' THEN 1 END) as tier1_count,
                COUNT(CASE WHEN credibility_tier = 'TIER_2' THEN 1 END) as tier2_count,
                COUNT(CASE WHEN credibility_tier = 'TIER_3' THEN 1 END) as tier3_count,
                COUNT(CASE WHEN credibility_tier = 'TIER_4' THEN 1 END) as tier4_count,
                COUNT(CASE WHEN credibility_tier = 'TIER_5' THEN 1 END) as tier5_count
                FROM trusted_sources";
            
            $result = $conn->query($sql);
            $stats = $result->fetch_assoc();
            
            $response = ['success' => true, 'statistics' => $stats];
            break;

        case 'search_sources':
            $query = $data['query'] ?? '';
            $limit = $data['limit'] ?? 20;
            
            $sql = "SELECT * FROM trusted_sources 
                    WHERE (source_name LIKE ? OR domain_name LIKE ? OR source_url LIKE ?)
                    AND active = 1
                    ORDER BY trust_score DESC
                    LIMIT ?";
            
            $searchParam = "%$query%";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $searchParam, $searchParam, $searchParam, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $sources = [];
            while ($row = $result->fetch_assoc()) {
                $sources[] = $row;
            }
            
            $response = ['success' => true, 'sources' => $sources];
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>
