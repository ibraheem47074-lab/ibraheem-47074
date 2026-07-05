<?php
require_once 'config/database.php';

echo "PK Live News - Check Tables for Polls and Ads\n";
echo "============================================\n\n";

// Check polls table
echo "1. Checking Polls Table:\n";
echo "-------------------------\n";

$polls_check = mysqli_query($conn, "SHOW TABLES LIKE 'polls'");
if (mysqli_num_rows($polls_check) > 0) {
    echo "✅ Polls table exists\n";
    
    // Check if there are active polls
    $poll_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM polls WHERE status = 'active'");
    $count_result = mysqli_fetch_assoc($poll_count);
    echo "   Active polls: " . $count_result['count'] . "\n";
    
    // Check poll_options table
    $options_check = mysqli_query($conn, "SHOW TABLES LIKE 'poll_options'");
    if (mysqli_num_rows($options_check) > 0) {
        echo "✅ Poll_options table exists\n";
    } else {
        echo "❌ Poll_options table missing\n";
    }
} else {
    echo "❌ Polls table missing\n";
}

// Check advertisements table
echo "\n2. Checking Advertisements Table:\n";
echo "--------------------------------\n";

$ads_check = mysqli_query($conn, "SHOW TABLES LIKE 'advertisements'");
if (mysqli_num_rows($ads_check) > 0) {
    echo "✅ Advertisements table exists\n";
    
    // Check if there are active ads
    $ad_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM advertisements WHERE is_active = 1");
    $ad_result = mysqli_fetch_assoc($ad_count);
    echo "   Active ads: " . $ad_result['count'] . "\n";
    
    // Show table structure
    echo "\n   Table structure:\n";
    $structure = mysqli_query($conn, "DESCRIBE advertisements");
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "   - {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "❌ Advertisements table missing\n";
    
    // Check if ads table exists (alternative name)
    $ads_alt_check = mysqli_query($conn, "SHOW TABLES LIKE 'ads'");
    if (mysqli_num_rows($ads_alt_check) > 0) {
        echo "✅ 'ads' table exists (alternative name)\n";
    } else {
        echo "❌ No ads/advertisements table found\n";
    }
}

// Check if functions exist
echo "\n3. Checking Functions:\n";
echo "---------------------\n";

if (file_exists('includes/ads_functions.php')) {
    echo "✅ ads_functions.php exists\n";
} else {
    echo "❌ ads_functions.php missing\n";
}

if (file_exists('includes/ad-functions.php')) {
    echo "✅ ad-functions.php exists\n";
} else {
    echo "❌ ad-functions.php missing\n";
}

echo "\n=== Recommendations ===\n";
echo "If polls/ads are not showing:\n";
echo "1. Check if tables exist and have data\n";
echo "2. Verify functions are included correctly\n";
echo "3. Check database connection and permissions\n";
echo "4. Look for PHP errors in logs\n";
?>
