<?php
require_once 'config/database.php';
require_once 'includes/affiliate-functions.php';

// Get product ID and redirect URL
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$redirect_url = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '';

if ($product_id > 0 && !empty($redirect_url)) {
    // Track the click
    track_affiliate_click($product_id);
    
    // Redirect to affiliate URL
    header("Location: " . $redirect_url);
    exit();
} else {
    // Invalid parameters, redirect to home
    header("Location: " . SITE_URL);
    exit();
}
?>
