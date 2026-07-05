<?php
require_once '../config/database.php';
require_once '../includes/ad-functions.php';

// Check if user is admin
if (!is_admin()) {
    http_response_code(403);
    exit('Access denied');
}

$ad_id = $_GET['id'] ?? 0;

if ($ad_id <= 0) {
    exit('Invalid advertisement ID');
}

// Get advertisement details
$sql = "SELECT * FROM advertisements WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $ad_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($ad = mysqli_fetch_assoc($result)) {
    $stats = get_ad_statistics($ad_id);
    $ctr = ($stats['total_impressions'] ?? 0) > 0 ? 
           round(($stats['total_clicks'] / $stats['total_impressions']) * 100, 2) : 0;
    
    echo '<div class="ad-details">';
    echo '<h5>' . htmlspecialchars($ad['title']) . '</h5>';
    echo '<div class="row mb-3">';
    echo '<div class="col-md-6"><strong>Position:</strong> ' . ucfirst($ad['position']) . '</div>';
    echo '<div class="col-md-6"><strong>Size:</strong> ' . htmlspecialchars($ad['size'] ?? 'N/A') . '</div>';
    echo '</div>';
    
    echo '<div class="row mb-3">';
    echo '<div class="col-md-6"><strong>Status:</strong> ';
    echo '<span class="badge bg-' . (($ad['is_active'] ?? 0) ? 'success' : 'danger') . '">';
    echo ($ad['is_active'] ?? 0) ? 'Active' : 'Inactive';
    echo '</span></div>';
    echo '<div class="col-md-6"><strong>Created:</strong> ' . date('M d, Y', strtotime($ad['created_at'])) . '</div>';
    echo '</div>';
    
    if ($ad['start_date'] || $ad['end_date']) {
        echo '<div class="row mb-3">';
        echo '<div class="col-md-6"><strong>Start Date:</strong> ' . ($ad['start_date'] ? date('M d, Y', strtotime($ad['start_date'])) : 'N/A') . '</div>';
        echo '<div class="col-md-6"><strong>End Date:</strong> ' . ($ad['end_date'] ? date('M d, Y', strtotime($ad['end_date'])) : 'N/A') . '</div>';
        echo '</div>';
    }
    
    echo '<div class="ad-stats mb-3">';
    echo '<div class="ad-stat">';
    echo '<div class="h5">' . number_format($stats['total_impressions'] ?? 0) . '</div>';
    echo '<small>Impressions</small>';
    echo '</div>';
    echo '<div class="ad-stat">';
    echo '<div class="h5">' . number_format($stats['total_clicks'] ?? 0) . '</div>';
    echo '<small>Clicks</small>';
    echo '</div>';
    echo '<div class="ad-stat">';
    echo '<div class="h5">' . $ctr . '%</div>';
    echo '<small>CTR</small>';
    echo '</div>';
    echo '<div class="ad-stat">';
    echo '<div class="h5">' . number_format($stats['unique_impressions'] ?? 0) . '</div>';
    echo '<small>Unique Impressions</small>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="mb-3">';
    echo '<strong>Ad Code:</strong>';
    echo '<div class="ad-preview mt-2">';
    echo $ad['ad_code'] ?? 'No ad code available';
    echo '</div>';
    echo '</div>';
    
    if ($ad['max_impressions'] ?? null) {
        $progress = min(($stats['total_impressions'] ?? 0) / $ad['max_impressions'] * 100, 100);
        echo '<div class="mb-3">';
        echo '<strong>Impression Progress:</strong>';
        echo '<div class="progress mt-2">';
        echo '<div class="progress-bar" role="progressbar" style="width: ' . $progress . '%">';
        echo number_format($stats['total_impressions'] ?? 0) . ' / ' . number_format($ad['max_impressions']);
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
} else {
    echo 'Advertisement not found';
}
?>
