<?php
/**
 * AI Image Settings
 * Configure AI providers, prompts, and generation settings
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin-header.php';

// Check admin permissions
if (!is_admin()) {
    header('Location: ../login.php');
    exit();
}

// Get current settings
function getCurrentSettings($conn) {
    // First check if ai_settings table exists
    $tableCheck = "SHOW TABLES LIKE 'ai_settings'";
    $result = mysqli_query($conn, $tableCheck);
    
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
        'replicate_api_key' => '',
        'ai_prompt_template' => 'Professional news photograph of: {title}. Scene: {category_context}. Style: professional news photography, high quality, realistic, photojournalistic style, clear and detailed',
        'ai_negative_prompt_template' => 'cartoon, anime, illustration, text, watermark, signature, inappropriate content'
    ];
    
    if (mysqli_num_rows($result) === 0) {
        // Table doesn't exist, return defaults
        return $defaults;
    }
    
    $settings = [];
    foreach ($defaults as $key => $default) {
        $query = "SELECT setting_value FROM ai_settings WHERE setting_key = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $key);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $settings[$key] = $row['setting_value'];
        } else {
            $settings[$key] = $default;
        }
    }
    
    return $settings;
}

// Page header
$pageTitle = 'AI Image Settings';
include 'includes/admin-header.php';

$settings = getCurrentSettings($conn);

// Test provider connection
if (isset($_GET['test_provider']) && in_array($_GET['test_provider'], ['openai', 'stability', 'replicate'])) {
    require_once __DIR__ . '/../includes/ai_image_generator.php';
    $provider = $_GET['test_provider'];
    $aiGenerator = new AIImageGenerator($conn);
    $testResult = $aiGenerator->testProvider($provider);
    
    if ($testResult['success']) {
        $message = "✅ {$provider} connection test successful!";
    } else {
        $error = "❌ {$provider} connection test failed: " . $testResult['error'];
    }
}
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">AI Image Generation Settings</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="form_type" value="update_settings">
                    
                    <!-- General Settings -->
                    <div class="mb-4">
                        <h6 class="text-primary">General Settings</h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enabled" name="enabled" 
                                           value="true" <?php echo $settings['ai_image_generation_enabled'] === 'true' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enabled">
                                        Enable AI Image Generation
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto_generate_rss" name="auto_generate_rss" 
                                           value="true" <?php echo $settings['ai_auto_generate_for_rss'] === 'true' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="auto_generate_rss">
                                        Auto-generate for RSS articles
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="default_provider" class="form-label">Default Provider</label>
                                <select class="form-select" id="default_provider" name="default_provider">
                                    <option value="openai" <?php echo $settings['ai_default_provider'] === 'openai' ? 'selected' : ''; ?>>OpenAI DALL-E</option>
                                    <option value="stability" <?php echo $settings['ai_default_provider'] === 'stability' ? 'selected' : ''; ?>>Stability AI</option>
                                    <option value="replicate" <?php echo $settings['ai_default_provider'] === 'replicate' ? 'selected' : ''; ?>>Replicate</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="image_quality" class="form-label">Image Quality</label>
                                <select class="form-select" id="image_quality" name="image_quality">
                                    <option value="standard" <?php echo $settings['ai_image_quality'] === 'standard' ? 'selected' : ''; ?>>Standard</option>
                                    <option value="high" <?php echo $settings['ai_image_quality'] === 'high' ? 'selected' : ''; ?>>High</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="image_style" class="form-label">Image Style</label>
                                <select class="form-select" id="image_style" name="image_style">
                                    <option value="realistic" <?php echo $settings['ai_image_style'] === 'realistic' ? 'selected' : ''; ?>>Realistic</option>
                                    <option value="dramatic" <?php echo $settings['ai_image_style'] === 'dramatic' ? 'selected' : ''; ?>>Dramatic</option>
                                    <option value="artistic" <?php echo $settings['ai_image_style'] === 'artistic' ? 'selected' : ''; ?>>Artistic</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="watermark_enabled" name="watermark_enabled" 
                                           value="true" <?php echo $settings['ai_watermark_enabled'] === 'true' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="watermark_enabled">
                                        Add AI Watermark
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="max_attempts" class="form-label">Max Generation Attempts</label>
                                <input type="number" class="form-control" id="max_attempts" name="max_attempts" 
                                       value="<?php echo $settings['ai_max_generation_attempts']; ?>" min="1" max="10">
                            </div>
                            <div class="col-md-6">
                                <label for="generation_timeout" class="form-label">Generation Timeout (seconds)</label>
                                <input type="number" class="form-control" id="generation_timeout" name="generation_timeout" 
                                       value="<?php echo $settings['ai_generation_timeout']; ?>" min="30" max="300">
                            </div>
                        </div>
                    </div>
                    
                    <!-- API Keys -->
                    <div class="mb-4">
                        <h6 class="text-primary">API Keys</h6>
                        
                        <div class="mb-3">
                            <label for="openai_api_key" class="form-label">
                                OpenAI API Key 
                                <a href="?action=settings&test_provider=openai" class="btn btn-sm btn-outline-info ms-2">Test Connection</a>
                            </label>
                            <input type="password" class="form-control" id="openai_api_key" name="openai_api_key" 
                                   value="<?php echo htmlspecialchars($settings['openai_api_key']); ?>" 
                                   placeholder="sk-...">
                        </div>
                        
                        <div class="mb-3">
                            <label for="stability_api_key" class="form-label">
                                Stability AI API Key 
                                <a href="?action=settings&test_provider=stability" class="btn btn-sm btn-outline-info ms-2">Test Connection</a>
                            </label>
                            <input type="password" class="form-control" id="stability_api_key" name="stability_api_key" 
                                   value="<?php echo htmlspecialchars($settings['stability_api_key']); ?>" 
                                   placeholder="Your Stability AI API key">
                        </div>
                        
                        <div class="mb-3">
                            <label for="replicate_api_key" class="form-label">
                                Replicate API Key 
                                <a href="?action=settings&test_provider=replicate" class="btn btn-sm btn-outline-info ms-2">Test Connection</a>
                            </label>
                            <input type="password" class="form-control" id="replicate_api_key" name="replicate_api_key" 
                                   value="<?php echo htmlspecialchars($settings['replicate_api_key']); ?>" 
                                   placeholder="r8_...">
                        </div>
                    </div>
                    
                    <!-- Prompt Templates -->
                    <div class="mb-4">
                        <h6 class="text-primary">Prompt Templates</h6>
                        
                        <div class="mb-3">
                            <label for="prompt_template" class="form-label">Positive Prompt Template</label>
                            <textarea class="form-control" id="prompt_template" name="prompt_template" rows="4"><?php echo htmlspecialchars($settings['ai_prompt_template']); ?></textarea>
                            <div class="form-text">
                                Available variables: {title}, {category}, {category_context}, {entities}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="negative_prompt_template" class="form-label">Negative Prompt Template</label>
                            <textarea class="form-control" id="negative_prompt_template" name="negative_prompt_template" rows="3"><?php echo htmlspecialchars($settings['ai_negative_prompt_template']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="reset" class="btn btn-secondary">Reset to Defaults</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Provider Status -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Provider Status</h6>
            </div>
            <div class="card-body">
                <?php
                $providers = ['openai', 'stability', 'replicate'];
                foreach ($providers as $provider):
                    $hasKey = !empty($settings[$provider . '_api_key']);
                ?>
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo ucfirst($provider); ?></strong>
                                <br>
                                <small class="text-muted">
                                    <?php 
                                    switch($provider) {
                                        case 'openai': echo 'DALL-E 3'; break;
                                        case 'stability': echo 'Stable Diffusion XL'; break;
                                        case 'replicate': echo 'Multiple Models'; break;
                                    }
                                    ?>
                                </small>
                            </div>
                            <div>
                                <?php if ($hasKey): ?>
                                    <span class="badge bg-success">Configured</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Not Configured</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($hasKey): ?>
                            <div class="mt-2">
                                <a href="?action=settings&test_provider=<?php echo $provider; ?>" class="btn btn-sm btn-outline-primary">
                                    Test Connection
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Usage Statistics -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Usage Statistics</h6>
            </div>
            <div class="card-body">
                <?php
                $usageQuery = "SELECT 
                    COUNT(*) as total_generated,
                    COUNT(CASE WHEN DATE(image_generated_at) = CURDATE() THEN 1 END) as today,
                    COUNT(CASE WHEN DATE(image_generated_at) >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as this_week,
                    COUNT(CASE WHEN DATE(image_generated_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as this_month
                    FROM news WHERE image_type = 'ai'";
                $usage = mysqli_fetch_assoc(mysqli_query($conn, $usageQuery));
                ?>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Generated</span>
                        <strong><?php echo number_format($usage['total_generated']); ?></strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Today</span>
                        <strong><?php echo number_format($usage['today']); ?></strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>This Week</span>
                        <strong><?php echo number_format($usage['this_week']); ?></strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>This Month</span>
                        <strong><?php echo number_format($usage['this_month']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Test -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Test</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">Test AI image generation with a sample prompt.</p>
                
                <form method="POST" onsubmit="return confirm('This will generate a test image and may use API credits. Continue?')">
                    <input type="hidden" name="form_type" value="test_generation">
                    
                    <div class="mb-3">
                        <label for="test_provider" class="form-label">Provider</label>
                        <select class="form-select" id="test_provider" name="test_provider">
                            <option value="openai">OpenAI DALL-E</option>
                            <option value="stability">Stability AI</option>
                            <option value="replicate">Replicate</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="test_prompt" class="form-label">Test Prompt</label>
                        <textarea class="form-control" id="test_prompt" name="test_prompt" rows="2">Professional news photograph of a press conference with politicians speaking</textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-play"></i> Generate Test Image
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-save settings
let saveTimeout;
const autoSave = () => {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        const form = document.querySelector('form');
        const formData = new FormData(form);
        
        // Don't auto-save sensitive data
        if (formData.get('openai_api_key') || formData.get('stability_api_key') || formData.get('replicate_api_key')) {
            return;
        }
        
        // Auto-save non-sensitive settings
        fetch('', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (response.ok) {
                // Show subtle success indicator
                const indicator = document.createElement('div');
                indicator.className = 'position-fixed top-0 end-0 p-3';
                indicator.style.zIndex = '11';
                indicator.innerHTML = '<div class="toast show"><div class="toast-body">Settings auto-saved</div></div>';
                document.body.appendChild(indicator);
                setTimeout(() => indicator.remove(), 2000);
            }
        });
    }, 2000);
};

// Add change listeners to non-sensitive fields
document.querySelectorAll('input:not([type="password"]), select, textarea').forEach(field => {
    field.addEventListener('change', autoSave);
});

// Show/hide password fields
document.querySelectorAll('input[type="password"]').forEach(field => {
    const wrapper = document.createElement('div');
    wrapper.className = 'input-group';
    field.parentNode.insertBefore(wrapper, field);
    wrapper.appendChild(field);
    
    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'btn btn-outline-secondary';
    toggle.innerHTML = '<i class="fas fa-eye"></i>';
    toggle.style.borderLeft = 'none';
    wrapper.appendChild(toggle);
    
    toggle.addEventListener('click', () => {
        const type = field.type === 'password' ? 'text' : 'password';
        field.type = type;
        toggle.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
});
<?php include 'includes/admin-footer.php'; ?>
