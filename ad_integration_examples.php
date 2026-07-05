<?php
require_once 'config/database.php';
require_once 'includes/ad-functions.php';

echo "<h2>Advertisement Integration Examples</h2>";

// Example 1: Display ads for Live Streaming page
echo "<h3>Live Streaming Page Ads</h3>";
echo "<div class='live-ads-demo'>";
echo display_live_ads();
echo "</div>";

// Example 2: Display ads for Performance Analysis page
echo "<h3>Performance Analysis Page Ads</h3>";
echo "<div class='performance-ads-demo'>";
echo display_performance_ads();
echo "</div>";

// Example 3: Display ads for Contact page
echo "<h3>Contact Page Ads</h3>";
echo "<div class='contact-ads-demo'>";
echo display_contact_ads();
echo "</div>";

// Example 4: Display ads for specific category (e.g., Sports)
echo "<h3>Category-Specific Ads (Sports)</h3>";
echo "<div class='category-ads-demo'>";
// Get sports category ID (assuming it exists)
$sports_query = "SELECT id FROM categories WHERE slug = 'sports' LIMIT 1";
$sports_result = mysqli_query($conn, $sports_query);
if ($sports_category = mysqli_fetch_assoc($sports_result)) {
    echo display_category_ads($sports_category['id']);
} else {
    echo "<p>No sports category found. Using category ID 1 as example.</p>";
    echo display_category_ads(1);
}
echo "</div>";

// Example 5: Display ads for Home page
echo "<h3>Home Page Ads</h3>";
echo "<div class='home-ads-demo'>";
echo display_home_ads();
echo "</div>";

// Example 6: Manual ad placement with targeting
echo "<h3>Manual Ad Placement Examples</h3>";

// Display a mobile-only ad for news pages
$mobile_news_ads = get_active_ads('news_inline', 'news', null, 'mobile', 1);
if (!empty($mobile_news_ads)) {
    echo "<div class='manual-ad-example'>";
    echo "<h4>Mobile News Inline Ad:</h4>";
    echo display_ad_with_wrapper($mobile_news_ads[0], 'mobile-news-ad');
    echo "</div>";
}

// Display a desktop-only ad for search results
$desktop_search_ads = get_active_ads('search_sidebar', 'search', null, 'desktop', 1);
if (!empty($desktop_search_ads)) {
    echo "<div class='manual-ad-example'>";
    echo "<h4>Desktop Search Sidebar Ad:</h4>";
    echo display_ad_with_wrapper($desktop_search_ads[0], 'desktop-search-ad');
    echo "</div>";
}

// Example 7: Multiple ads for same position
echo "<h3>Multiple Ads for Same Position</h3>";
$sidebar_ads = get_active_ads('sidebar', null, null, null, 3); // Get 3 sidebar ads
if (!empty($sidebar_ads)) {
    echo "<div class='multiple-ads-example'>";
    foreach ($sidebar_ads as $index => $ad) {
        echo "<div class='ad-slot'>";
        echo "<h4>Ad Slot " . ($index + 1) . ":</h4>";
        echo display_ad_with_wrapper($ad, 'sidebar-ad-' . ($index + 1));
        echo "</div>";
    }
    echo "</div>";
}

// Show current active ads by position
echo "<h3>Current Active Ads by Position</h3>";
$positions = [
    'header' => 'Header Banner',
    'sidebar' => 'Sidebar Rectangle',
    'footer' => 'Footer Banner',
    'live_header' => 'Live Header',
    'live_sidebar' => 'Live Sidebar',
    'performance_header' => 'Performance Header',
    'performance_sidebar' => 'Performance Sidebar',
    'contact_header' => 'Contact Header',
    'contact_sidebar' => 'Contact Sidebar',
    'category_header' => 'Category Header',
    'category_sidebar' => 'Category Sidebar',
    'home_hero' => 'Home Hero',
    'home_featured' => 'Home Featured',
    'home_sidebar' => 'Home Sidebar',
    'news_inline' => 'News Inline',
    'search_sidebar' => 'Search Sidebar',
    'profile_sidebar' => 'Profile Sidebar'
];

foreach ($positions as $position => $label) {
    $ads = get_active_ads($position, null, null, null, 2);
    if (!empty($ads)) {
        echo "<div class='position-ads'>";
        echo "<h4>{$label} (Position: {$position})</h4>";
        foreach ($ads as $ad) {
            echo "<div class='ad-info'>";
            echo "<strong>Title:</strong> " . htmlspecialchars($ad['title']) . "<br>";
            echo "<strong>Page Type:</strong> " . htmlspecialchars($ad['page_type']) . "<br>";
            echo "<strong>Device:</strong> " . htmlspecialchars($ad['device_type']) . "<br>";
            echo "<strong>Size:</strong> " . htmlspecialchars($ad['size']) . "<br>";
            echo "<strong>Status:</strong> " . htmlspecialchars($ad['status']);
            echo "</div>";
        }
        echo "</div>";
    }
}

echo "<h3>Integration Instructions</h3>";
echo "<div class='integration-guide'>";
echo "<h4>How to integrate ads in your pages:</h4>";
echo "<ol>";
echo "<li><strong>For Live Streaming pages:</strong> Use <code>display_live_ads()</code></li>";
echo "<li><strong>For Performance Analysis pages:</strong> Use <code>display_performance_ads()</code></li>";
echo "<li><strong>For Contact pages:</strong> Use <code>display_contact_ads()</code></li>";
echo "<li><strong>For Category pages:</strong> Use <code>display_category_ads(\$category_id)</code></li>";
echo "<li><strong>For Home page:</strong> Use <code>display_home_ads()</code></li>";
echo "<li><strong>For custom placement:</strong> Use <code>get_active_ads(\$position, \$page_type, \$category_id, \$device_type, \$limit)</code> and <code>display_ad_with_wrapper(\$ad, \$class)</code></li>";
echo "</ol>";

echo "<h4>Available Positions:</h4>";
echo "<ul>";
echo "<li><strong>General:</strong> header, sidebar, footer, all</li>";
echo "<li><strong>Live Section:</strong> live_header, live_sidebar, live_footer, live_popup</li>";
echo "<li><strong>Performance Section:</strong> performance_header, performance_sidebar, performance_footer, performance_inline</li>";
echo "<li><strong>Contact Section:</strong> contact_header, contact_sidebar, contact_footer</li>";
echo "<li><strong>Category-Specific:</strong> category_header, category_sidebar, category_footer, category_inline</li>";
echo "<li><strong>Home Page:</strong> home_hero, home_featured, home_sidebar, home_footer</li>";
echo "<li><strong>Other Pages:</strong> news_inline, search_sidebar, profile_sidebar</li>";
echo "</ul>";

echo "<h4>Targeting Options:</h4>";
echo "<ul>";
echo "<li><strong>Page Types:</strong> all, home, category, news, live, contact, search, profile, performance</li>";
echo "<li><strong>Device Types:</strong> all, desktop, mobile, tablet</li>";
echo "<li><strong>Category-Specific:</strong> Set category_id for targeted category ads</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='admin/manage-ads.php' class='btn btn-primary'>Manage Ads</a> | <a href='index.php' class='btn btn-secondary'>View Website</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.live-ads-demo, .performance-ads-demo, .contact-ads-demo, .category-ads-demo, .home-ads-demo { 
    border: 2px solid #007bff; padding: 15px; margin: 10px 0; border-radius: 5px; 
}
.manual-ad-example { border: 2px solid #28a745; padding: 15px; margin: 10px 0; border-radius: 5px; }
.multiple-ads-example { border: 2px solid #ffc107; padding: 15px; margin: 10px 0; border-radius: 5px; }
.position-ads { border: 1px solid #6c757d; padding: 10px; margin: 10px 0; border-radius: 5px; }
.ad-info { background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 3px; }
.ad-slot { border: 1px dashed #007bff; padding: 10px; margin: 5px 0; border-radius: 3px; }
.integration-guide { background: #e9ecef; padding: 20px; border-radius: 5px; margin: 20px 0; }
code { background: #f1f1f1; padding: 2px 5px; border-radius: 3px; font-family: 'Courier New', monospace; }
.btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; }
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
h3 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
h4 { color: #495057; }
</style>
