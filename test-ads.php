<?php
require_once 'config/database.php';
require_once 'includes/ads_functions.php';

// Test the functions
echo "<h1>Testing Ad Functions</h1>";

// Test getActiveAds
echo "<h2>Testing getActiveAds('sidebar')</h2>";
$ad = getActiveAds('sidebar');
if ($ad) {
    echo "<pre>";
    print_r($ad);
    echo "</pre>";
} else {
    echo "No active ads found for sidebar position.<br>";
}

// Test displayAdWidget
echo "<h2>Testing displayAdWidget('header')</h2>";
displayAdWidget('header');

echo "<h2>Testing displayAdWidget('footer')</h2>";
displayAdWidget('footer');
?>
