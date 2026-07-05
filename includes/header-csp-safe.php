<?php
// CSP-Safe Header with Local Bootstrap Fallback
// Fix path for includes when called from admin directory
$basePath = dirname(__DIR__) . '/';
require_once $basePath . 'config/database.php';
require_once $basePath . 'config/helpers.php';
require_once $basePath . 'includes/language_functions.php';

// Initialize language system
$current_lang = get_current_language();
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PK Live News</title>
    
    <!-- Multi-language SEO -->
    <?php if (get_site_setting('multilingual_seo', '1') == '1'): ?>
        <?php echo generate_hreflang_tags(); ?>
    <?php endif; ?>
    
    <!-- Local Bootstrap CSS (CSP-Safe) -->
    <link href="<?php echo SITE_URL; ?>assets/css/bootstrap-local.css" rel="stylesheet">
    
    <!-- Fallback to CDN if local fails -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-cdn-fallback">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">
    <!-- Live TV CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/live-tv.css" rel="stylesheet">
    <!-- Heat Map CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/heatmap.css" rel="stylesheet">
    <!-- Weather CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/weather.css" rel="stylesheet">
    <!-- Image Lightbox CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/image-lightbox.css" rel="stylesheet">
    <!-- Video Lightbox CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/video-lightbox.css" rel="stylesheet">
    <!-- Affiliate Products CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/affiliate-products.css" rel="stylesheet">
    
    <!-- Custom CSS for Dropdowns and Notifications Z-Index Fix -->
    <style>
        /* Navigation Bar */
        .navbar {
            z-index: 1000 !important;
        }
        
        /* All Dropdown Menus */
        .dropdown-menu {
            z-index: 1050 !important;
        }
        
        /* Notifications Dropdown */
        .notifications-dropdown .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Search Dropdown */
        .search-dropdown .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* User Dropdown */
        .user-dropdown .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Language Switcher Dropdown */
        .language-switcher .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Notification Alerts */
        .alert.position-fixed {
            z-index: 1060 !important;
        }
        
        /* Modal Backdrop */
        .modal-backdrop {
            z-index: 1040 !important;
        }
        
        /* Modal */
        .modal {
            z-index: 1055 !important;
        }
        
        /* Toast Notifications */
        .toast {
            z-index: 1065 !important;
        }
        
        /* Ensure dropdowns appear above navigation */
        .nav-item .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Fix for any overlapping elements */
        .dropdown.show .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Bootstrap Loading Indicator */
        .bootstrap-loading {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: 4px;
            color: #6c757d;
            font-size: 14px;
        }
        
        /* Hide CDN fallback if local loads successfully */
        .bootstrap-local-loaded #bootstrap-cdn-fallback {
            display: none;
        }
    </style>
    
    <!-- Bootstrap Loading Detection Script -->
    <script>
        // Detect if local Bootstrap loaded successfully
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                // Check if local Bootstrap styles are applied
                const testElement = document.createElement('div');
                testElement.className = 'container';
                testElement.style.display = 'none';
                document.body.appendChild(testElement);
                
                const styles = window.getComputedStyle(testElement);
                const hasBootstrap = styles.paddingRight && styles.paddingRight !== '0px';
                
                document.body.removeChild(testElement);
                
                if (hasBootstrap) {
                    document.body.classList.add('bootstrap-local-loaded');
                    console.log('Local Bootstrap CSS loaded successfully');
                } else {
                    console.log('Local Bootstrap CSS failed to load, using CDN fallback');
                    // Remove local Bootstrap to prevent conflicts
                    const localLink = document.querySelector('link[href*="bootstrap-local.css"]');
                    if (localLink) {
                        localLink.disabled = true;
                    }
                }
            }, 1000);
        });
        
        // Log CSP violations for debugging
        document.addEventListener('securitypolicyviolation', function(e) {
            console.error('CSP Violation:', {
                blockedURI: e.blockedURI,
                violatedDirective: e.violatedDirective,
                sourceFile: e.sourceFile,
                lineNumber: e.lineNumber
            });
        });
    </script>
</head>
<body>
    <!-- Bootstrap Loading Indicator (will be hidden when Bootstrap loads) -->
    <div id="bootstrap-indicator" class="bootstrap-loading" style="position: fixed; top: 0; left: 0; right: 0; z-index: 9999; text-align: center;">
        <i class="fas fa-spinner fa-spin"></i> Loading styles...
    </div>

    <script>
        // Hide loading indicator after a short delay
        setTimeout(function() {
            const indicator = document.getElementById('bootstrap-indicator');
            if (indicator) {
                indicator.style.display = 'none';
            }
        }, 2000);
    </script>
