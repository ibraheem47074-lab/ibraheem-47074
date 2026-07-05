<?php
require_once '../config/database.php';

// Check if user is admin
if (!is_admin()) {
    redirect('login.php');
}

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $setting_key = str_replace('setting_', '', $key);
            
            // Determine setting type
            $type = 'text';
            if (in_array($setting_key, ['posts_per_page', 'cache_duration'])) {
                $type = 'number';
            } elseif (in_array($setting_key, ['maintenance_mode', 'show_trending_news', 'show_ads', 'enable_comments', 'enable_rss'])) {
                $type = 'boolean';
                $value = $value === 'on' ? 'on' : 'off';
            } elseif (in_array($setting_key, ['social_media_links'])) {
                $type = 'json';
                $value = json_encode($value);
            }
            
            SettingsManager::set($setting_key, $value, $type);
        }
    }
    
    $message = 'Settings updated successfully!';
}

// Get all settings
$settings = SettingsManager::getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="container mt-4">
        <h2>Site Settings</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <h4>General Settings</h4>
                    
                    <div class="mb-3">
                        <label for="setting_site_name" class="form-label">Site Name</label>
                        <input type="text" class="form-control" id="setting_site_name" 
                               name="setting_site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="setting_site_description" class="form-label">Site Description</label>
                        <textarea class="form-control" id="setting_site_description" 
                                  name="setting_site_description" rows="3"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="setting_contact_email" class="form-label">Contact Email</label>
                        <input type="email" class="form-control" id="setting_contact_email" 
                               name="setting_contact_email" value="<?php echo htmlspecialchars($settings['contact_email']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="setting_posts_per_page" class="form-label">Posts Per Page</label>
                        <input type="number" class="form-control" id="setting_posts_per_page" 
                               name="setting_posts_per_page" value="<?php echo htmlspecialchars($settings['posts_per_page']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="setting_cache_duration" class="form-label">Cache Duration (seconds)</label>
                        <input type="number" class="form-control" id="setting_cache_duration" 
                               name="setting_cache_duration" value="<?php echo htmlspecialchars($settings['cache_duration']); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h4>Feature Settings</h4>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="setting_maintenance_mode" 
                               name="setting_maintenance_mode" <?php echo $settings['maintenance_mode'] === 'on' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="setting_maintenance_mode">
                            Maintenance Mode
                        </label>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="setting_show_trending_news" 
                               name="setting_show_trending_news" <?php echo $settings['show_trending_news'] === 'on' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="setting_show_trending_news">
                            Show Trending News
                        </label>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="setting_show_ads" 
                               name="setting_show_ads" <?php echo $settings['show_ads'] === 'on' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="setting_show_ads">
                            Show Advertisements
                        </label>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="setting_enable_comments" 
                               name="setting_enable_comments" <?php echo $settings['enable_comments'] === 'on' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="setting_enable_comments">
                            Enable Comments
                        </label>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="setting_enable_rss" 
                               name="setting_enable_rss" <?php echo $settings['enable_rss'] === 'on' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="setting_enable_rss">
                            Enable RSS Feeds
                        </label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="setting_default_language" class="form-label">Default Language</label>
                        <select class="form-control" id="setting_default_language" name="setting_default_language">
                            <option value="en" <?php echo $settings['default_language'] === 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="ur" <?php echo $settings['default_language'] === 'ur' ? 'selected' : ''; ?>>Urdu</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h4>SEO Settings</h4>
                    
                    <div class="mb-3">
                        <label for="setting_seo_meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="setting_seo_meta_description" 
                                  name="setting_seo_meta_description" rows="2"><?php echo htmlspecialchars($settings['seo_meta_description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="setting_seo_keywords" class="form-label">SEO Keywords (comma-separated)</label>
                        <input type="text" class="form-control" id="setting_seo_keywords" 
                               name="setting_seo_keywords" value="<?php echo htmlspecialchars($settings['seo_keywords']); ?>">
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
    
    <?php include 'includes/admin-footer.php'; ?>
</body>
</html>
