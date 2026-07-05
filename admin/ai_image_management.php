<?php
/**
 * AI Image Management Admin Panel
 * Complete control system for AI-generated images
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/ai_image_generator.php';
require_once __DIR__ . '/../includes/smart_prompt_generator.php';

// Check admin permissions
if (!is_admin()) {
    header('Location: ../login.php');
    exit();
}

// Initialize classes
$aiGenerator = new AIImageGenerator($conn);
$promptGenerator = new SmartPromptGenerator($conn);

// Handle actions
$action = $_GET['action'] ?? 'dashboard';
$newsId = $_GET['news_id'] ?? null;
$message = '';
$error = '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['form_type']) {
        case 'generate_image':
            $newsId = $_POST['news_id'];
            $provider = $_POST['provider'] ?? 'openai';
            $customPrompt = $_POST['custom_prompt'] ?? '';
            
            if ($newsId) {
                // Get news details
                $newsQuery = "SELECT n.*, c.name as category_name FROM news n LEFT JOIN categories c ON n.category_id = c.id WHERE n.id = ?";
                $stmt = mysqli_prepare($conn, $newsQuery);
                mysqli_stmt_bind_param($stmt, 'i', $newsId);
                mysqli_stmt_execute($stmt);
                $news = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                
                if ($news) {
                    // Update status to generating
                    mysqli_query($conn, "UPDATE news SET ai_image_status = 'generating' WHERE id = $newsId");
                    
                    // Generate image
                    if (!empty($customPrompt)) {
                        $result = $aiGenerator->generateImageWithCustomPrompt($newsId, $customPrompt, $provider);
                    } else {
                        $result = $aiGenerator->generateImageForNews($newsId, $news['title'], $news['category_name'], $provider);
                    }
                    
                    if ($result['success']) {
                        $message = "AI image generated successfully!";
                        // Log the generation
                        logAIImageGeneration($newsId, $provider, $result['prompt'], 'completed');
                    } else {
                        $error = "Failed to generate image: " . $result['error'];
                        // Update status to failed
                        mysqli_query($conn, "UPDATE news SET ai_image_status = 'failed', ai_image_error = '" . mysqli_real_escape_string($conn, $result['error']) . "' WHERE id = $newsId");
                        logAIImageGeneration($newsId, $provider, $result['prompt'] ?? '', 'failed', $result['error']);
                    }
                }
            }
            break;
            
        case 'approve_image':
            $newsId = $_POST['news_id'];
            mysqli_query($conn, "UPDATE news SET ai_image_status = 'approved' WHERE id = $newsId");
            $message = "Image approved successfully!";
            break;
            
        case 'reject_image':
            $newsId = $_POST['news_id'];
            $reason = $_POST['rejection_reason'] ?? '';
            mysqli_query($conn, "UPDATE news SET ai_image_status = 'rejected', ai_image_error = '" . mysqli_real_escape_string($conn, $reason) . "' WHERE id = $newsId");
            $message = "Image rejected!";
            break;
            
        case 'regenerate_image':
            $newsId = $_POST['news_id'];
            $provider = $_POST['provider'] ?? 'openai';
            
            // Get news details
            $newsQuery = "SELECT n.*, c.name as category_name FROM news n LEFT JOIN categories c ON n.category_id = c.id WHERE n.id = ?";
            $stmt = mysqli_prepare($conn, $newsQuery);
            mysqli_stmt_bind_param($stmt, 'i', $newsId);
            mysqli_stmt_execute($stmt);
            $news = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            
            if ($news) {
                mysqli_query($conn, "UPDATE news SET ai_image_status = 'generating' WHERE id = $newsId");
                $result = $aiGenerator->generateImageForNews($newsId, $news['title'], $news['category_name'], $provider);
                
                if ($result['success']) {
                    $message = "Image regenerated successfully!";
                } else {
                    $error = "Failed to regenerate image: " . $result['error'];
                    mysqli_query($conn, "UPDATE news SET ai_image_status = 'failed', ai_image_error = '" . mysqli_real_escape_string($conn, $result['error']) . "' WHERE id = $newsId");
                }
            }
            break;
            
        case 'update_settings':
            updateAISettings($_POST);
            $message = "AI settings updated successfully!";
            break;
    }
}

/**
 * Log AI image generation
 */
function logAIImageGeneration($newsId, $provider, $prompt, $status, $error = null) {
    global $conn;
    
    $query = "INSERT INTO ai_image_logs (news_id, provider, prompt, status, error_message, generation_time) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    $promptJson = json_encode($prompt);
    $generationTime = microtime(true); // You should calculate this properly
    mysqli_stmt_bind_param($stmt, 'issssd', $newsId, $provider, $promptJson, $status, $error, $generationTime);
    mysqli_stmt_execute($stmt);
}

/**
 * Update AI settings
 */
function updateAISettings($data) {
    global $conn;
    
    $settings = [
        'ai_image_generation_enabled' => $data['enabled'] ?? 'false',
        'ai_default_provider' => $data['default_provider'] ?? 'openai',
        'ai_image_quality' => $data['image_quality'] ?? 'standard',
        'ai_image_style' => $data['image_style'] ?? 'realistic',
        'ai_auto_generate_for_rss' => $data['auto_generate_rss'] ?? 'false',
        'ai_watermark_enabled' => $data['watermark_enabled'] ?? 'true',
        'openai_api_key' => $data['openai_api_key'] ?? '',
        'stability_api_key' => $data['stability_api_key'] ?? '',
        'replicate_api_key' => $data['replicate_api_key'] ?? ''
    ];
    
    foreach ($settings as $key => $value) {
        $query = "INSERT INTO ai_settings (setting_key, setting_value) VALUES (?, ?) 
                  ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
        mysqli_stmt_execute($stmt);
    }
}

/**
 * Get AI setting value
 */
function getAISetting($key) {
    global $conn;
    
    // First check if ai_settings table exists
    $tableCheck = "SHOW TABLES LIKE 'ai_settings'";
    $result = mysqli_query($conn, $tableCheck);
    
    if (mysqli_num_rows($result) === 0) {
        // Table doesn't exist, return default values
        $defaults = [
            'ai_image_generation_enabled' => 'true',
            'ai_default_provider' => 'openai',
            'ai_image_quality' => 'standard',
            'ai_image_style' => 'realistic',
            'ai_auto_generate_for_rss' => 'true',
            'ai_watermark_enabled' => 'true',
            'ai_max_generation_attempts' => '3',
            'ai_generation_timeout' => '60',
            'openai_api_key' => '',
            'stability_api_key' => '',
            'replicate_api_key' => ''
        ];
        return $defaults[$key] ?? '';
    }
    
    $query = "SELECT setting_value FROM ai_settings WHERE setting_key = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $key);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['setting_value'];
    }
    
    return '';
}

// Page header
$pageTitle = 'AI Image Management';
include 'includes/admin-header.php';

?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">AI Image Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="?action=dashboard" class="btn btn-outline-secondary <?php echo $action === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="?action=queue" class="btn btn-outline-secondary <?php echo $action === 'queue' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Queue
                </a>
                <a href="?action=settings" class="btn btn-outline-secondary <?php echo $action === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="?action=logs" class="btn btn-outline-secondary <?php echo $action === 'logs' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i> Logs
                </a>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
    switch ($action) {
        case 'dashboard':
            include 'ai_image_dashboard.php';
            break;
        case 'queue':
            include 'ai_image_queue.php';
            break;
        case 'settings':
            include 'ai_image_settings.php';
            break;
        case 'logs':
            include 'ai_image_logs.php';
            break;
        case 'edit':
            include 'ai_image_edit.php';
            break;
        default:
            include 'ai_image_dashboard.php';
    }
    ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
