<?php
/**
 * AI Image Generator for News Articles
 * Supports multiple AI providers: OpenAI DALL-E, Stable Diffusion, etc.
 */

class AIImageGenerator {
    private $conn;
    private $providers;
    private $defaultProvider = 'openai';
    private $imageQuality = 'standard'; // standard, high
    private $imageStyle = 'realistic'; // realistic, cartoon, infographic
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->initializeProviders();
    }
    
    /**
     * Initialize AI providers configuration
     */
    private function initializeProviders() {
        $this->providers = [
            'openai' => [
                'api_key' => $this->getSetting('openai_api_key') ?: '',
                'model' => 'dall-e-3',
                'size' => '1024x1024',
                'quality' => 'standard',
                'endpoint' => 'https://api.openai.com/v1/images/generations'
            ],
            'stability' => [
                'api_key' => $this->getSetting('stability_api_key') ?: '',
                'model' => 'stable-diffusion-xl',
                'width' => 1024,
                'height' => 1024,
                'endpoint' => 'https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image'
            ],
            'replicate' => [
                'api_key' => $this->getSetting('replicate_api_key') ?: '',
                'model' => 'stability-ai/stable-diffusion',
                'endpoint' => 'https://api.replicate.com/v1/predictions'
            ]
        ];
    }
    
    /**
     * Generate image with custom prompt
     */
    public function generateImageWithCustomPrompt($newsId, $customPrompt, $provider = null) {
        $provider = $provider ?: $this->defaultProvider;
        
        try {
            // Create custom prompt data
            $promptData = [
                'prompt' => $customPrompt,
                'negative_prompt' => 'cartoon, anime, illustration, text, watermark, signature, inappropriate content',
                'quality' => $this->imageQuality,
                'style' => $this->imageStyle
            ];
            
            // Generate image
            $imageResult = $this->generateImage($promptData, $provider);
            
            if ($imageResult['success']) {
                // Download and save image
                $imagePath = $this->downloadAndSaveImage($imageResult['image_url'], $newsId);
                
                if ($imagePath) {
                    // Update database
                    $this->updateNewsImage($newsId, $imagePath, $provider, $promptData, $imageResult['image_url']);
                    
                    return [
                        'success' => true,
                        'image_path' => $imagePath,
                        'provider' => $provider,
                        'prompt' => $promptData,
                        'original_url' => $imageResult['image_url']
                    ];
                }
            }
            
            return ['success' => false, 'error' => $imageResult['error'] ?? 'Unknown error'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generate image for news article
     */
    public function generateImageForNews($newsId, $title, $category = null, $provider = null) {
        $provider = $provider ?: $this->defaultProvider;
        
        try {
            // Generate smart prompt
            $prompt = $this->generateSmartPrompt($title, $category);
            
            // Generate image
            $imageResult = $this->generateImage($prompt, $provider);
            
            if ($imageResult['success']) {
                // Download and save image
                $imagePath = $this->downloadAndSaveImage($imageResult['image_url'], $newsId);
                
                if ($imagePath) {
                    // Update database
                    $this->updateNewsImage($newsId, $imagePath, $provider, $prompt, $imageResult['image_url']);
                    
                    return [
                        'success' => true,
                        'image_path' => $imagePath,
                        'provider' => $provider,
                        'prompt' => $prompt,
                        'original_url' => $imageResult['image_url']
                    ];
                }
            }
            
            return ['success' => false, 'error' => $imageResult['error'] ?? 'Unknown error'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generate smart prompt based on title and category
     */
    private function generateSmartPrompt($title, $category = null) {
        $basePrompt = "Professional news photograph of: " . $title;
        
        // Category-specific enhancements
        $categoryPrompts = [
            'Politics' => "press conference, government building, formal setting, professional photography",
            'War' => "conflict zone, military action, dramatic lighting, photojournalistic style",
            'Business' => "office building, stock market, professional business setting, corporate environment",
            'Technology' => "modern technology, digital devices, innovation, clean tech environment",
            'Sports' => "sports action, stadium, athletic competition, dynamic movement",
            'Entertainment' => "celebrity, red carpet, entertainment venue, glamorous setting",
            'Health' => "medical facility, healthcare setting, professional medical environment",
            'Science' => "laboratory, research setting, scientific equipment, innovation",
            'Pakistan' => "Pakistani context, local setting, culturally relevant scene",
            'International' => "global perspective, international setting, worldly context"
        ];
        
        if ($category && isset($categoryPrompts[$category])) {
            $basePrompt .= ". Scene: " . $categoryPrompts[$category];
        }
        
        // Style and quality modifiers
        $styleModifiers = [
            'professional news photography',
            'high quality',
            'realistic',
            'photojournalistic style',
            'clear and detailed',
            'appropriate for news publication'
        ];
        
        $basePrompt .= ". Style: " . implode(', ', $styleModifiers);
        
        // Add negative prompts to avoid inappropriate content
        $negativePrompts = [
            'cartoon',
            'anime',
            'illustration',
            'text',
            'watermark',
            'signature',
            'inappropriate content',
            'violence unless news-related',
            'graphic content unless necessary'
        ];
        
        return [
            'prompt' => $basePrompt,
            'negative_prompt' => implode(', ', $negativePrompts),
            'quality' => $this->imageQuality,
            'style' => $this->imageStyle
        ];
    }
    
    /**
     * Generate image using specified provider
     */
    private function generateImage($promptData, $provider) {
        switch ($provider) {
            case 'openai':
                return $this->generateWithOpenAI($promptData);
            case 'stability':
                return $this->generateWithStability($promptData);
            case 'replicate':
                return $this->generateWithReplicate($promptData);
            default:
                return ['success' => false, 'error' => 'Unsupported provider'];
        }
    }
    
    /**
     * Generate image with OpenAI DALL-E
     */
    private function generateWithOpenAI($promptData) {
        $config = $this->providers['openai'];
        
        if (empty($config['api_key'])) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }
        
        $payload = [
            'model' => $config['model'],
            'prompt' => $promptData['prompt'],
            'n' => 1,
            'size' => $config['size'],
            'quality' => $config['quality'],
            'style' => 'natural'
        ];
        
        $headers = [
            'Authorization: Bearer ' . $config['api_key'],
            'Content-Type: application/json'
        ];
        
        $response = $this->makeAPICall($config['endpoint'], $payload, $headers);
        
        if ($response['success']) {
            $data = json_decode($response['body'], true);
            if (isset($data['data'][0]['url'])) {
                return [
                    'success' => true,
                    'image_url' => $data['data'][0]['url'],
                    'revised_prompt' => $data['data'][0]['revised_prompt'] ?? null
                ];
            }
        }
        
        return ['success' => false, 'error' => $response['error'] ?? 'OpenAI API error'];
    }
    
    /**
     * Generate image with Stability AI
     */
    private function generateWithStability($promptData) {
        $config = $this->providers['stability'];
        
        if (empty($config['api_key'])) {
            return ['success' => false, 'error' => 'Stability AI API key not configured'];
        }
        
        $payload = [
            'text_prompts' => [
                [
                    'text' => $promptData['prompt'],
                    'weight' => 1
                ],
                [
                    'text' => $promptData['negative_prompt'],
                    'weight' => -1
                ]
            ],
            'cfg_scale' => 7,
            'height' => $config['height'],
            'width' => $config['width'],
            'samples' => 1,
            'steps' => 30
        ];
        
        $headers = [
            'Authorization: Bearer ' . $config['api_key'],
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        $response = $this->makeAPICall($config['endpoint'], $payload, $headers);
        
        if ($response['success']) {
            $data = json_decode($response['body'], true);
            if (isset($data['artifacts'][0]['base64'])) {
                // Save base64 image to temporary file and return URL
                $tempPath = $this->saveBase64Image($data['artifacts'][0]['base64']);
                return [
                    'success' => true,
                    'image_url' => $tempPath,
                    'base64' => $data['artifacts'][0]['base64']
                ];
            }
        }
        
        return ['success' => false, 'error' => $response['error'] ?? 'Stability AI error'];
    }
    
    /**
     * Generate image with Replicate
     */
    private function generateWithReplicate($promptData) {
        $config = $this->providers['replicate'];
        
        if (empty($config['api_key'])) {
            return ['success' => false, 'error' => 'Replicate API key not configured'];
        }
        
        $payload = [
            'version' => $config['model'],
            'input' => [
                'prompt' => $promptData['prompt'],
                'negative_prompt' => $promptData['negative_prompt'],
                'width' => 1024,
                'height' => 1024,
                'num_outputs' => 1,
                'num_inference_steps' => 30,
                'guidance_scale' => 7
            ]
        ];
        
        $headers = [
            'Authorization: Token ' . $config['api_key'],
            'Content-Type: application/json'
        ];
        
        // Create prediction
        $response = $this->makeAPICall($config['endpoint'], $payload, $headers);
        
        if ($response['success']) {
            $data = json_decode($response['body'], true);
            if (isset($data['urls']['get'])) {
                // Poll for result
                return $this->pollReplicateResult($data['urls']['get'], $config['api_key']);
            }
        }
        
        return ['success' => false, 'error' => $response['error'] ?? 'Replicate API error'];
    }
    
    /**
     * Poll Replicate for generation result
     */
    private function pollReplicateResult($getUrl, $apiKey, $maxAttempts = 30) {
        $headers = [
            'Authorization: Token ' . $apiKey,
            'Content-Type: application/json'
        ];
        
        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = $this->makeAPICall($getUrl, [], $headers, 'GET');
            
            if ($response['success']) {
                $data = json_decode($response['body'], true);
                
                if ($data['status'] === 'succeeded') {
                    return [
                        'success' => true,
                        'image_url' => $data['output'][0],
                        'completed' => true
                    ];
                } elseif ($data['status'] === 'failed') {
                    return ['success' => false, 'error' => 'Generation failed'];
                }
            }
            
            sleep(2); // Wait 2 seconds before polling again
        }
        
        return ['success' => false, 'error' => 'Generation timeout'];
    }
    
    /**
     * Make API call
     */
    private function makeAPICall($url, $payload = [], $headers = [], $method = 'POST') {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => $headers
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($payload)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            }
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'error' => $error];
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'body' => $response];
        }
        
        return ['success' => false, 'error' => "HTTP {$httpCode}: {$response}"];
    }
    
    /**
     * Download and save image
     */
    private function downloadAndSaveImage($imageUrl, $newsId) {
        try {
            $imageData = null;
            
            // Handle base64 images
            if (filter_var($imageUrl, FILTER_VALIDATE_URL) === false) {
                // Assume it's a local temporary file or base64
                if (file_exists($imageUrl)) {
                    $imageData = file_get_contents($imageUrl);
                    unlink($imageUrl); // Clean up temp file
                }
            } else {
                // Download from URL
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $imageUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_USERAGENT => 'PK Live News AI Image Generator'
                ]);
                $imageData = curl_exec($ch);
                curl_close($ch);
            }
            
            if (!$imageData) {
                return false;
            }
            
            // Validate image
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);
            
            if (strpos($mimeType, 'image/') !== 0) {
                return false;
            }
            
            // Generate filename
            $extension = explode('/', $mimeType)[1];
            $filename = 'ai_' . $newsId . '_' . uniqid() . '.' . $extension;
            $uploadPath = 'uploads/news/' . $filename;
            $fullPath = __DIR__ . '/../' . $uploadPath;
            
            // Ensure directory exists
            $uploadDir = dirname($fullPath);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Save file
            if (file_put_contents($fullPath, $imageData)) {
                // Add AI watermark
                $this->addAIWatermark($fullPath);
                return $uploadPath;
            }
            
        } catch (Exception $e) {
            error_log("Image download failed: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Add AI watermark to image
     */
    private function addAIWatermark($imagePath) {
        try {
            $imageInfo = getimagesize($imagePath);
            if (!$imageInfo) return;
            
            $image = null;
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($imagePath);
                    break;
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($imagePath);
                    break;
                case IMAGETYPE_WEBP:
                    $image = imagecreatefromwebp($imagePath);
                    break;
            }
            
            if (!$image) return;
            
            // Add semi-transparent text
            $textColor = imagecolorallocatealpha($image, 255, 255, 255, 60);
            $font = 3; // Built-in font
            $text = "AI Generated";
            $x = imagesx($image) - strlen($text) * imagefontwidth($font) - 10;
            $y = imagesy($image) - 10;
            
            imagestring($image, $font, $x, $y, $text, $textColor);
            
            // Save image
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    imagejpeg($image, $imagePath, 90);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($image, $imagePath, 9);
                    break;
                case IMAGETYPE_WEBP:
                    imagewebp($image, $imagePath, 90);
                    break;
            }
            
            imagedestroy($image);
            
        } catch (Exception $e) {
            error_log("Watermark failed: " . $e->getMessage());
        }
    }
    
    /**
     * Update news record with AI image information
     */
    private function updateNewsImage($newsId, $imagePath, $provider, $prompt, $originalUrl) {
        $query = "UPDATE news SET 
                    image = ?, 
                    image_generated_at = NOW(), 
                    image_provider = ?, 
                    image_prompt = ?,
                    updated_at = NOW()
                  WHERE id = ?";
        
        $stmt = mysqli_prepare($this->conn, $query);
        $promptJson = json_encode($prompt);
        mysqli_stmt_bind_param($stmt, 'ssssi', $imagePath, $provider, $promptJson, $originalUrl, $newsId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Get setting from database
     */
    private function getSetting($key) {
        // First check if ai_settings table exists
        $tableCheck = "SHOW TABLES LIKE 'ai_settings'";
        $result = mysqli_query($this->conn, $tableCheck);
        
        if (mysqli_num_rows($result) === 0) {
            // Table doesn't exist, return default values
            return $this->getDefaultSetting($key);
        }
        
        $query = "SELECT setting_value FROM ai_settings WHERE setting_key = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $key);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return $row['setting_value'];
        }
        
        return $this->getDefaultSetting($key);
    }
    
    /**
     * Get default setting value
     */
    private function getDefaultSetting($key) {
        $defaults = [
            'openai_api_key' => '',
            'stability_api_key' => '',
            'replicate_api_key' => '',
            'ai_image_generation_enabled' => 'true',
            'ai_default_provider' => 'openai',
            'ai_image_quality' => 'standard',
            'ai_image_style' => 'realistic',
            'ai_auto_generate_for_rss' => 'true',
            'ai_watermark_enabled' => 'true',
            'ai_max_generation_attempts' => '3',
            'ai_generation_timeout' => '60'
        ];
        
        return $defaults[$key] ?? '';
    }
    
    /**
     * Get default provider
     */
    public function getDefaultProvider() {
        return $this->defaultProvider;
    }
    
    /**
     * Save base64 image to temporary file
     */
    private function saveBase64Image($base64Data) {
        $imageData = base64_decode($base64Data);
        $tempFile = tempnam(sys_get_temp_dir(), 'ai_image_');
        file_put_contents($tempFile, $imageData);
        return $tempFile;
    }
    
    /**
     * Set configuration
     */
    public function setDefaultProvider($provider) {
        if (isset($this->providers[$provider])) {
            $this->defaultProvider = $provider;
        }
    }
    
    public function setImageQuality($quality) {
        $this->imageQuality = $quality;
    }
    
    public function setImageStyle($style) {
        $this->imageStyle = $style;
    }
    
    /**
     * Get available providers
     */
    public function getAvailableProviders() {
        return array_keys($this->providers);
    }
    
    /**
     * Test provider connection
     */
    public function testProvider($provider) {
        if (!isset($this->providers[$provider])) {
            return ['success' => false, 'error' => 'Provider not found'];
        }
        
        $config = $this->providers[$provider];
        
        if (empty($config['api_key'])) {
            return ['success' => false, 'error' => 'API key not configured'];
        }
        
        // Test with a simple prompt
        $testPrompt = $this->generateSmartPrompt("Test news image", "Technology");
        $result = $this->generateImage($testPrompt, $provider);
        
        return $result;
    }
}
?>
