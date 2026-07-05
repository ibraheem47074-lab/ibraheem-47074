<?php
require_once 'config/database.php';
require_once 'includes/ad-functions.php';

$ad_id = $_GET['ad_id'] ?? 0;
$redirect_url = $_GET['redirect'] ?? 'index.php';

if ($ad_id > 0) {
    track_ad_click($ad_id);
}

header("Location: " . $redirect_url);
exit();
?>
