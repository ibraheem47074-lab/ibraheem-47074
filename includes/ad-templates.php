<?php
require_once 'ad-functions.php';

// Header Banner Ad Template
function render_header_ad() {
    echo display_ad('header', 'header-banner');
}

// Sidebar Ad Template
function render_sidebar_ad() {
    echo display_ad('sidebar', 'sidebar-rectangle');
}

// Footer Ad Template
function render_footer_ad() {
    echo display_ad('footer', 'footer-banner');
}

// Popup Ad Template
function render_popup_ad() {
    $ads = get_active_ads('popup', null, null, null, 1);
    
    if (empty($ads)) {
        return;
    }
    
    $ad = $ads[0];
    track_ad_impression($ad['id']);
    update_ad_impressions($ad['id']);
    $ad_code = get_ad_render_code($ad);
    if ($ad_code === '') {
        return;
    }
    $ad_code = add_click_tracking($ad_code, $ad['id']);
    
    ?>
    <!-- Popup Ad -->
    <div id="popup-ad-overlay" class="popup-overlay" style="display:none;">
        <div class="popup-ad-container">
            <div class="popup-ad-header">
                <span class="popup-ad-title">Advertisement</span>
                <button class="popup-ad-close" onclick="closePopupAd()">&times;</button>
            </div>
            <div class="popup-ad-content" data-ad-id="<?php echo $ad['id']; ?>">
                <?php echo $ad_code; ?>
            </div>
        </div>
    </div>

    <script>
    // Popup Ad Management
    let popupShown = false;
    let popupDelay = 5000; // Show after 5 seconds
    let popupFrequency = 86400000; // Show once per day (24 hours in milliseconds)

    function showPopupAd() {
        if (popupShown) return;
        
        const lastShown = localStorage.getItem('popupAdLastShown');
        const now = Date.now();
        
        if (lastShown && (now - parseInt(lastShown)) < popupFrequency) {
            return; // Don't show if shown within last 24 hours
        }
        
        setTimeout(() => {
            document.getElementById('popup-ad-overlay').style.display = 'flex';
            localStorage.setItem('popupAdLastShown', now.toString());
            popupShown = true;
            
            // Track impression
            trackPopupAdImpression();
        }, popupDelay);
    }

    function closePopupAd() {
        document.getElementById('popup-ad-overlay').style.display = 'none';
    }

    function trackPopupAdImpression() {
        // Impression already tracked in PHP
        console.log('Popup ad impression tracked');
    }

    // Show popup when page loads
    document.addEventListener('DOMContentLoaded', function() {
        showPopupAd();
    });

    // Close popup when clicking outside
    document.addEventListener('click', function(event) {
        const popup = document.getElementById('popup-ad-overlay');
        if (event.target === popup) {
            closePopupAd();
        }
    });

    // Close popup with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePopupAd();
        }
    });
    </script>

    <style>
    /* Popup Ad Styles */
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeIn 0.3s ease-in-out;
    }

    .popup-ad-container {
        background: white;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.3s ease-out;
    }

    .popup-ad-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
    }

    .popup-ad-title {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .popup-ad-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
    }

    .popup-ad-close:hover {
        background: #e9ecef;
        color: #333;
    }

    .popup-ad-content {
        padding: 20px;
        text-align: center;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Responsive popup */
    @media (max-width: 768px) {
        .popup-ad-container {
            width: 95%;
            margin: 20px;
        }
        
        .popup-ad-content {
            padding: 15px;
        }
    }
    </style>
    <?php
}

// Multiple Ads for Footer
function render_footer_ads() {
    $ads = get_active_ads('footer', null, null, null, 3); // Get up to 3 footer ads
    
    if (empty($ads)) {
        return;
    }
    
    echo '<div class="footer-ads-container">';
    
    foreach ($ads as $ad) {
        $ad_code = get_ad_render_code($ad);
        if ($ad_code === '') {
            continue;
        }

        track_ad_impression($ad['id']);
        update_ad_impressions($ad['id']);
        $ad_code = add_click_tracking($ad_code, $ad['id']);
        
        echo "<div class='footer-ad-item' data-ad-id='{$ad['id']}'>";
        echo $ad_code;
        echo "</div>";
    }
    
    echo '</div>';
}

// Ad CSS Styles
function render_ad_styles() {
    ?>
    <style>
    /* General Ad Styles */
    .ad-wrapper {
        margin: 10px 0;
        text-align: center;
        clear: both;
    }

    .ad-wrapper img {
        max-width: 100%;
        height: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    /* Header Banner Ad */
    .header-banner {
        width: 100%;
        max-width: 728px;
        margin: 0 auto 20px auto;
        overflow: hidden;
    }

    .header-banner img {
        width: 100%;
        height: 90px;
        object-fit: cover;
    }

    /* Sidebar Rectangle Ad */
    .sidebar-rectangle {
        width: 100%;
        max-width: 300px;
        margin: 20px 0;
        position: sticky;
        top: 20px;
    }

    .sidebar-rectangle img {
        width: 100%;
        height: 250px;
        object-fit: cover;
    }

    /* Footer Banner Ad */
    .footer-banner {
        width: 100%;
        max-width: 728px;
        margin: 20px auto;
    }

    .footer-banner img {
        width: 100%;
        height: 90px;
        object-fit: cover;
    }

    /* Multiple Footer Ads */
    .footer-ads-container {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
        margin: 20px 0;
    }

    .footer-ad-item {
        flex: 1;
        min-width: 200px;
        max-width: 300px;
    }

    .footer-ad-item img {
        width: 100%;
        height: auto;
        max-height: 250px;
        object-fit: cover;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .header-banner,
        .footer-banner {
            max-width: 100%;
        }
        
        .sidebar-rectangle {
            max-width: 100%;
            position: relative;
            top: 0;
        }
        
        .footer-ads-container {
            flex-direction: column;
            align-items: center;
        }
        
        .footer-ad-item {
            max-width: 100%;
        }
    }

    /* Ad Label */
    .ad-wrapper::before {
        content: "Advertisement";
        display: block;
        font-size: 11px;
        color: #666;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    </style>
    <?php
}
?>
