<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/ad-functions.php';

/**
 * Get active advertisements for display
 * @param string $position - Position of the ad (sidebar, header, footer, etc.)
 * @return array|null - Array of active advertisements or null
 */
function getActiveAds($position = 'sidebar') {
    global $conn;
    
    // Check if table exists first
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'advertisements'");
    if (mysqli_num_rows($table_check) == 0) {
        return null; // Table doesn't exist
    }
    
    $ads_query = "SELECT * FROM advertisements 
                  WHERE status = 'active' 
                  AND position = ?
                  AND (start_date IS NULL OR start_date <= CURDATE())
                  AND (end_date IS NULL OR end_date >= CURDATE())
                  ORDER BY RAND() 
                  LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $ads_query);
    if (!$stmt) {
        return null; // Query preparation failed
    }
    
    mysqli_stmt_bind_param($stmt, 's', $position);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

/**
 * Display advertisement widget
 * @param string $position - Position of the ad
 */
function displayAdWidget($position = 'sidebar') {
    $ad = getActiveAds($position);
    
    if ($ad) {
        $ad_code = get_ad_render_code($ad);
        if ($ad_code === '') {
            return;
        }

        // Track impression
        track_ad_impression($ad['id']);
        update_ad_impressions($ad['id']);
        
        // Add click tracking to ad code
        $ad_code = add_click_tracking($ad_code, $ad['id']);
        
        echo '<div class="sidebar-widget ad-wrapper ad-' . $position . '" data-ad-id="' . $ad['id'] . '">';
        echo '    <div class="text-center">';
        echo '        <small class="text-muted d-block mb-2">Advertisement</small>';
        echo        $ad_code;
        echo '    </div>';
        echo '</div>';
    }
}

/**
 * Display header advertisement
 */
function displayHeaderAd() {
    $ad = getActiveAds('header');
    
    if ($ad) {
        $ad_code = get_ad_render_code($ad);
        if ($ad_code === '') {
            return;
        }

        track_ad_impression($ad['id']);
        update_ad_impressions($ad['id']);
        $ad_code = add_click_tracking($ad_code, $ad['id']);
        
        echo '<div class="header-ad-container ad-wrapper ad-header" data-ad-id="' . $ad['id'] . '">';
        echo '    <small class="text-muted d-block text-center mb-2">Advertisement</small>';
        echo    $ad_code;
        echo '</div>';
    }
}

/**
 * Display footer advertisement
 */
function displayFooterAd() {
    $ad = getActiveAds('footer');
    
    if ($ad) {
        $ad_code = get_ad_render_code($ad);
        if ($ad_code === '') {
            return;
        }

        track_ad_impression($ad['id']);
        update_ad_impressions($ad['id']);
        $ad_code = add_click_tracking($ad_code, $ad['id']);
        
        echo '<div class="footer-ad-container ad-wrapper ad-footer" data-ad-id="' . $ad['id'] . '">';
        echo '    <small class="text-muted d-block text-center mb-2">Advertisement</small>';
        echo    $ad_code;
        echo '</div>';
    }
}

/**
 * Display popup advertisement
 */
function displayPopupAd() {
    $ad = getActiveAds('popup');
    if (!$ad) {
        return;
    }

    $ad_code = get_ad_render_code($ad);
    if ($ad_code === '') {
        return;
    }

    track_ad_impression($ad['id']);
    update_ad_impressions($ad['id']);
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
        }, popupDelay);
    }

    function closePopupAd() {
        document.getElementById('popup-ad-overlay').style.display = 'none';
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
?>
