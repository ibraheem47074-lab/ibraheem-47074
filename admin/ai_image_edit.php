<?php
/**
 * AI Image Edit Interface
 * Manual editing and management of AI-generated images
 */

// Get news article
$newsId = $_GET['news_id'] ?? null;
$retry = $_GET['retry'] ?? false;

if (!$newsId) {
    header('Location: ?action=dashboard');
    exit();
}

// Get news details
$query = "SELECT n.*, c.name as category_name 
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          WHERE n.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $newsId);
mysqli_stmt_execute($stmt);
$news = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$news) {
    echo "News article not found.";
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['form_type']) {
        case 'generate_image':
            $provider = $_POST['provider'] ?? 'openai';
            $customPrompt = $_POST['custom_prompt'] ?? '';
            
            // Update status to generating
            mysqli_query($conn, "UPDATE news SET ai_image_status = 'generating' WHERE id = $newsId");
            
            // Generate image
            if (!empty($customPrompt)) {
                $result = $aiGenerator->generateImageFromCustomPrompt($newsId, $customPrompt, $provider);
            } else {
                $result = $aiGenerator->generateImageForNews($newsId, $news['title'], $news['category_name'], $provider);
            }
            
            if ($result['success']) {
                $message = "AI image generated successfully!";
                logAIImageGeneration($newsId, $provider, $result['prompt'], 'completed');
            } else {
                $error = "Failed to generate image: " . $result['error'];
                mysqli_query($conn, "UPDATE news SET ai_image_status = 'failed', ai_image_error = '" . mysqli_real_escape_string($conn, $result['error']) . "' WHERE id = $newsId");
                logAIImageGeneration($newsId, $provider, $result['prompt'] ?? '', 'failed', $result['error']);
            }
            
            // Refresh news data
            $result = mysqli_query($conn, "SELECT * FROM news WHERE id = $newsId");
            $news = mysqli_fetch_assoc($result);
            break;
            
        case 'upload_image':
            if (isset($_FILES['manual_image']) && $_FILES['manual_image']['error'] === UPLOAD_ERR_OK) {
                $uploadPath = uploadManualImage($_FILES['manual_image'], $newsId);
                if ($uploadPath) {
                    mysqli_query($conn, "UPDATE news SET image = '$uploadPath', image_type = 'manual', ai_image_status = 'approved' WHERE id = $newsId");
                    $message = "Image uploaded successfully!";
                    $news['image'] = $uploadPath;
                    $news['image_type'] = 'manual';
                } else {
                    $error = "Failed to upload image.";
                }
            }
            break;
            
        case 'update_prompt':
            $promptText = $_POST['prompt_text'] ?? '';
            $promptData = json_decode($news['image_prompt'] ?? '{}', true);
            $promptData['manual_prompt'] = $promptText;
            
            $updateQuery = "UPDATE news SET image_prompt = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, 'si', json_encode($promptData), $newsId);
            mysqli_stmt_execute($stmt);
            
            $message = "Prompt updated successfully!";
            $news['image_prompt'] = json_encode($promptData);
            break;
            
        case 'approve_image':
            mysqli_query($conn, "UPDATE news SET ai_image_status = 'approved' WHERE id = $newsId");
            $message = "Image approved successfully!";
            $news['ai_image_status'] = 'approved';
            break;
            
        case 'reject_image':
            $reason = $_POST['rejection_reason'] ?? '';
            mysqli_query($conn, "UPDATE news SET ai_image_status = 'rejected', ai_image_error = '" . mysqli_real_escape_string($conn, $reason) . "' WHERE id = $newsId");
            $message = "Image rejected!";
            $news['ai_image_status'] = 'rejected';
            break;
    }
}

// Auto-retry if requested
if ($retry && $news['ai_image_status'] === 'failed') {
    $provider = $news['image_provider'] ?? 'openai';
    $result = $aiGenerator->generateImageForNews($newsId, $news['title'], $news['category_name'], $provider);
    
    if ($result['success']) {
        $message = "Image regenerated successfully!";
        $result = mysqli_query($conn, "SELECT * FROM news WHERE id = $newsId");
        $news = mysqli_fetch_assoc($result);
    }
}

/**
 * Upload manual image
 */
function uploadManualImage($file, $newsId) {
    global $conn;
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'manual_' . $newsId . '_' . uniqid() . '.' . $extension;
    $uploadPath = 'uploads/news/' . $filename;
    $fullPath = __DIR__ . '/../' . $uploadPath;
    
    $uploadDir = dirname($fullPath);
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        return $uploadPath;
    }
    
    return false;
}

/**
 * Get prompt data
 */
function getPromptData($promptJson) {
    $data = json_decode($promptJson, true);
    if (!$data) {
        return ['prompt' => '', 'negative_prompt' => ''];
    }
    
    return [
        'prompt' => $data['prompt'] ?? $data['manual_prompt'] ?? '',
        'negative_prompt' => $data['negative_prompt'] ?? '',
        'category' => $data['category'] ?? '',
        'confidence' => $data['confidence'] ?? 0
    ];
}
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Edit AI Image</h6>
                <div>
                    <a href="?action=queue" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Queue
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Article Info -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h5><?php echo htmlspecialchars($news['title']); ?></h5>
                        <p class="text-muted">
                            Category: <?php echo htmlspecialchars($news['category_name'] ?? 'Uncategorized'); ?> | 
                            Type: <?php echo ucfirst($news['news_type']); ?> |
                            Status: <span class="badge bg-<?php echo getStatusBadgeClass($news['ai_image_status']); ?>">
                                <?php echo ucfirst($news['ai_image_status'] ?? 'pending'); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if ($news['image']): ?>
                            <small class="text-success">✓ Image available</small>
                        <?php else: ?>
                            <small class="text-warning">⚠ No image</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Current Image -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6>Current Image</h6>
                        <div class="border rounded p-3 bg-light">
                            <?php if ($news['image']): ?>
                                <img src="../<?php echo htmlspecialchars($news['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                     class="img-fluid mb-2" style="max-height: 400px;">
                                <div class="text-muted small">
                                    Path: <?php echo htmlspecialchars($news['image']); ?><br>
                                    Type: <?php echo ucfirst($news['image_type'] ?? 'manual'); ?><br>
                                    <?php if ($news['image_provider']): ?>
                                    Provider: <?php echo htmlspecialchars($news['image_provider']); ?><br>
                                    <?php endif; ?>
                                    <?php if ($news['image_generated_at']): ?>
                                    Generated: <?php echo date('M j, Y H:i', strtotime($news['image_generated_at'])); ?>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No image available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Generate New Image -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6>Generate New AI Image</h6>
                        <form method="POST">
                            <input type="hidden" name="form_type" value="generate_image">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="provider" class="form-label">AI Provider</label>
                                    <select class="form-select" id="provider" name="provider">
                                        <option value="openai">OpenAI DALL-E</option>
                                        <option value="stability">Stability AI</option>
                                        <option value="replicate">Replicate</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="custom_prompt" class="form-label">Custom Prompt (Optional)</label>
                                    <textarea class="form-control" id="custom_prompt" name="custom_prompt" rows="2" 
                                              placeholder="Leave empty for automatic prompt generation"></textarea>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-magic"></i> Generate Image
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="previewPrompt()">
                                    <i class="fas fa-eye"></i> Preview Prompt
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Upload Manual Image -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6>Upload Manual Image</h6>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="upload_image">
                            
                            <div class="input-group">
                                <input type="file" class="form-control" id="manual_image" name="manual_image" 
                                       accept="image/*" required>
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                            <div class="form-text">Max size: 5MB. Allowed: JPG, PNG, GIF, WebP</div>
                        </form>
                    </div>
                </div>
                
                <!-- Prompt Editor -->
                <?php if ($news['image_prompt']): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h6>Edit Prompt</h6>
                        <form method="POST">
                            <input type="hidden" name="form_type" value="update_prompt">
                            
                            <div class="mb-3">
                                <label for="prompt_text" class="form-label">Prompt Text</label>
                                <textarea class="form-control" id="prompt_text" name="prompt_text" rows="4"><?php 
                                    $promptData = getPromptData($news['image_prompt']);
                                    echo htmlspecialchars($promptData['prompt']);
                                ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-save"></i> Update Prompt
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Actions -->
                <?php if ($news['ai_image_status'] === 'completed'): ?>
                <div class="row">
                    <div class="col-12">
                        <h6>Review Actions</h6>
                        <div class="btn-group">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="form_type" value="approve_image">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Approve Image
                                </button>
                            </form>
                            
                            <button class="btn btn-outline-danger" onclick="showRejectModal()">
                                <i class="fas fa-times"></i> Reject Image
                            </button>
                            
                            <button class="btn btn-outline-warning" onclick="regenerateImage()">
                                <i class="fas fa-redo"></i> Regenerate
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Prompt Information -->
        <?php if ($news['image_prompt']): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Prompt Information</h6>
            </div>
            <div class="card-body">
                <?php 
                $promptData = getPromptData($news['image_prompt']);
                ?>
                <div class="mb-3">
                    <strong>Main Prompt:</strong>
                    <p class="text-muted small"><?php echo htmlspecialchars($promptData['prompt']); ?></p>
                </div>
                
                <?php if ($promptData['negative_prompt']): ?>
                <div class="mb-3">
                    <strong>Negative Prompt:</strong>
                    <p class="text-muted small"><?php echo htmlspecialchars($promptData['negative_prompt']); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($promptData['category']): ?>
                <div class="mb-3">
                    <strong>Category:</strong> <?php echo htmlspecialchars($promptData['category']); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($promptData['confidence']): ?>
                <div class="mb-3">
                    <strong>Confidence:</strong> <?php echo number_format($promptData['confidence'] * 100, 1); ?>%
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Generation History -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Generation History</h6>
            </div>
            <div class="card-body">
                <?php
                $historyQuery = "SELECT * FROM ai_image_logs WHERE news_id = ? ORDER BY created_at DESC LIMIT 5";
                $stmt = mysqli_prepare($conn, $historyQuery);
                mysqli_stmt_bind_param($stmt, 'i', $newsId);
                mysqli_stmt_execute($stmt);
                $history = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($history) > 0):
                ?>
                    <?php while ($log = mysqli_fetch_assoc($history)): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <small><strong><?php echo ucfirst($log['provider']); ?></strong></small>
                                <small class="text-muted"><?php echo date('M j, H:i', strtotime($log['created_at'])); ?></small>
                            </div>
                            <div>
                                <span class="badge bg-<?php echo getStatusBadgeClass($log['status']); ?>">
                                    <?php echo ucfirst($log['status']); ?>
                                </span>
                                <?php if ($log['generation_time']): ?>
                                    <small class="text-muted">(<?php echo $log['generation_time']; ?>s)</small>
                                <?php endif; ?>
                            </div>
                            <?php if ($log['error_message']): ?>
                                <small class="text-danger"><?php echo htmlspecialchars(substr($log['error_message'], 0, 50)); ?>...</small>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted small">No generation history available.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="copyPrompt()">
                        <i class="fas fa-copy"></i> Copy Prompt
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="downloadImage()">
                        <i class="fas fa-download"></i> Download Image
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="optimizeImage()">
                        <i class="fas fa-compress"></i> Optimize Image
                    </button>
                    <a href="../news.php?id=<?php echo $newsId; ?>" target="_blank" 
                       class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-external-link-alt"></i> View Article
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="form_type" value="reject_image">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason for rejection</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewPrompt() {
    const title = "<?php echo addslashes($news['title']); ?>";
    const category = "<?php echo addslashes($news['category_name'] ?? ''); ?>";
    const customPrompt = document.getElementById('custom_prompt').value;
    
    let prompt = customPrompt || `Professional news photograph of: ${title}. Category: ${category}. Style: professional news photography, high quality, realistic`;
    
    alert('Generated Prompt:\n\n' + prompt);
}

function showRejectModal() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function regenerateImage() {
    if (confirm('Regenerate the image? Current image will be replaced.')) {
        window.location.href = '?action=edit&news_id=<?php echo $newsId; ?>&retry=true';
    }
}

function copyPrompt() {
    const promptText = document.getElementById('prompt_text').value;
    if (promptText) {
        navigator.clipboard.writeText(promptText);
        alert('Prompt copied to clipboard!');
    }
}

function downloadImage() {
    <?php if ($news['image']): ?>
    window.open('../<?php echo $news['image']; ?>', '_blank');
    <?php else: ?>
    alert('No image available to download.');
    <?php endif; ?>
}

function optimizeImage() {
    alert('Image optimization feature coming soon!');
}

function getStatusBadgeClass(status) {
    const classes = {
        'pending': 'secondary',
        'generating': 'warning',
        'completed': 'success',
        'failed': 'danger',
        'approved': 'primary',
        'rejected': 'dark'
    };
    return classes[status] || 'secondary';
}
</script>
