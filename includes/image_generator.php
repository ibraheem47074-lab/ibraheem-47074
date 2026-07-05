<?php
/**
 * AI Image Generator Service for PK Live News
 * Generates images based on news article content using various AI services
 */

class ImageGenerator {
    private $apiKey;
    private $apiProvider;
    private $imageDir;
    private $defaultStyle;
    
    public function __construct($provider = 'openai', $apiKey = '') {
        $this->apiProvider = $provider;
        $this->apiKey = $apiKey ?: $this->getApiKey($provider);
        $this->imageDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/generated/';
        $this->defaultStyle = 'realistic journalistic news photo';
        
        // Create directory if it doesn't exist
        if (!file_exists($this->imageDir)) {
            mkdir($this->imageDir, 0755, true);
        }
    }
    
    /**
     * Generate image for news article
     */
    public function generateImageForNews($title, $content = '', $category = '', $style = '') {
        try {
            // Create prompt from article content
            $prompt = $this->createPrompt($title, $content, $category, $style);
            
            // Generate image based on provider
            $imageUrl = $this->generateImage($prompt);
            
            // Download and save image
            $localPath = $this->downloadAndSaveImage($imageUrl, $title);
            
            return [
                'success' => true,
                'image_url' => $imageUrl,
                'local_path' => $localPath,
                'prompt' => $prompt
            ];
            
        } catch (Exception $e) {
            error_log("Image generation failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create descriptive prompt for image generation
     */
    private function createPrompt($title, $content, $category, $style = '') {
        // Extract key information from title and content
        $keywords = $this->extractKeywords($title, $content);
        
        // Determine the type of image needed based on category
        $categoryHints = $this->getCategoryHints($category);
        
        // Build the prompt
        $prompt = "Create a {$this->defaultStyle}";
        
        if (!empty($style)) {
            $prompt = "Create a $style journalistic image";
        }
        
        $prompt .= " of: " . $title;
        
        if (!empty($keywords)) {
            $prompt .= ". Key elements: " . implode(', ', array_slice($keywords, 0, 5));
        }
        
        if (!empty($categoryHints)) {
            $prompt .= ". Setting: " . $categoryHints;
        }
        
        // Add quality and style instructions
        $prompt .= ". High quality, professional news photography, well-composed, good lighting, realistic, no text or watermarks.";
        
        return $prompt;
    }
    
    /**
     * Extract keywords from content
     */
    private function extractKeywords($title, $content) {
        $text = $title . ' ' . $content;
        
        // Remove common words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'said', 'says', 'according', 'report', 'news', 'breaking', 'latest', 'update'];
        
        // Extract words that are likely to be visual
        $words = preg_split('/\s+/', strtolower($text));
        $keywords = [];
        
        foreach ($words as $word) {
            $word = preg_replace('/[^a-z]/', '', $word);
            
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                // Prioritize visual words
                if ($this->isVisualWord($word)) {
                    $keywords[] = $word;
                }
            }
        }
        
        return array_unique($keywords);
    }
    
    /**
     * Check if a word is likely to be visual
     */
    private function isVisualWord($word) {
        $visualWords = [
            'person', 'people', 'man', 'woman', 'child', 'building', 'car', 'vehicle', 'road', 'street',
            'city', 'town', 'house', 'home', 'office', 'school', 'hospital', 'market', 'shop', 'store',
            'police', 'army', 'soldier', 'fire', 'accident', 'incident', 'protest', 'meeting', 'conference',
            'sports', 'player', 'team', 'game', 'match', 'stadium', 'field', 'ball', 'weather', 'rain', 'snow',
            'government', 'politician', 'minister', 'president', 'prime', 'election', 'vote', 'campaign',
            'economy', 'business', 'company', 'industry', 'factory', 'technology', 'computer', 'phone',
            'health', 'doctor', 'nurse', 'patient', 'medicine', 'hospital', 'disease', 'treatment',
            'education', 'student', 'teacher', 'university', 'college', 'school', 'book', 'study',
            'entertainment', 'movie', 'music', 'concert', 'show', 'actor', 'singer', 'artist',
            'food', 'restaurant', 'cooking', 'kitchen', 'market', 'farm', 'animal', 'nature', 'tree', 'flower'
        ];
        
        return in_array($word, $visualWords);
    }
    
    /**
     * Get category-specific hints for image generation
     */
    private function getCategoryHints($category) {
        $hints = [
            'politics' => 'government building, press conference, political meeting, official setting',
            'sports' => 'sports stadium, playing field, action shot, athletic competition',
            'business' => 'office building, business meeting, financial district, corporate setting',
            'technology' => 'modern office, computer lab, tech devices, innovation center',
            'health' => 'hospital, medical facility, doctor with patient, health clinic',
            'education' => 'school, university, classroom, library, educational setting',
            'entertainment' => 'concert hall, movie theater, stage, entertainment venue',
            'crime' => 'police scene, investigation area, law enforcement, crime scene',
            'weather' => 'outdoor scene, natural environment, weather conditions',
            'international' => 'landmark, international setting, global context, foreign location',
            'pakistan' => 'Pakistani setting, local landmark, familiar Pakistani scene'
        ];
        
        return $hints[strtolower($category)] ?? 'general news scene, realistic setting';
    }
    
    /**
     * Generate image using AI provider
     */
    private function generateImage($prompt) {
        switch ($this->apiProvider) {
            case 'openai':
                return $this->generateWithOpenAI($prompt);
            case 'stability':
                return $this->generateWithStabilityAI($prompt);
            case 'replicate':
                return $this->generateWithReplicate($prompt);
            default:
                // Fallback to placeholder image service
                return $this->generatePlaceholderImage($prompt);
        }
    }
    
    /**
     * Generate with OpenAI DALL-E
     */
    private function generateWithOpenAI($prompt) {
        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key not configured');
        }
        
        $data = [
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'quality' => 'standard',
            'response_format' => 'url'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/images/generations');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('OpenAI API error: ' . $response);
        }
        
        $result = json_decode($response, true);
        return $result['data'][0]['url'];
    }
    
    /**
     * Generate with Stability AI
     */
    private function generateWithStabilityAI($prompt) {
        if (empty($this->apiKey)) {
            throw new Exception('Stability AI API key not configured');
        }
        
        $data = [
            'prompt' => $prompt,
            'width' => 1024,
            'height' => 1024,
            'samples' => 1,
            'steps' => 30,
            'cfg_scale' => 7,
            'style_preset' => 'photographic'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Stability AI API error: ' . $response);
        }
        
        $result = json_decode($response, true);
        // Process base64 image and return URL
        return $this->processBase64Image($result['artifacts'][0]['base64']);
    }
    
    /**
     * Generate with Replicate
     */
    private function generateWithReplicate($prompt) {
        if (empty($this->apiKey)) {
            throw new Exception('Replicate API key not configured');
        }
        
        $data = [
            'version' => 'ac732df83cea7fff18b8472768c88ad041fa750ff7682a21affe81863cbe77e4',
            'input' => [
                'prompt' => $prompt,
                'width' => 1024,
                'height' => 1024,
                'num_outputs' => 1,
                'num_inference_steps' => 30,
                'guidance_scale' => 7.5
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.replicate.com/v1/predictions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Token ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 201) {
            throw new Exception('Replicate API error: ' . $response);
        }
        
        $result = json_decode($response, true);
        $predictionUrl = $result['urls']['get'];
        
        // Poll for completion
        return $this->pollReplicatePrediction($predictionUrl);
    }
    
    /**
     * Poll Replicate prediction for completion
     */
    private function pollReplicatePrediction($url) {
        $maxAttempts = 30;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Token ' . $this->apiKey
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($result['status'] === 'succeeded') {
                return $result['output'][0];
            } elseif ($result['status'] === 'failed') {
                throw new Exception('Replicate prediction failed');
            }
            
            sleep(2);
            $attempt++;
        }
        
        throw new Exception('Replicate prediction timed out');
    }
    
    /**
     * Generate placeholder image (fallback)
     */
    private function generatePlaceholderImage($prompt) {
        // Use a service like Lorem Picsum or create a custom placeholder
        $seed = md5($prompt);
        return "https://picsum.photos/seed/$seed/1024/1024.jpg";
    }
    
    /**
     * Download and save image locally
     */
    private function downloadAndSaveImage($imageUrl, $title) {
        $filename = $this->generateFilename($title);
        $filepath = $this->imageDir . $filename;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to download image: HTTP ' . $httpCode);
        }
        
        // Validate image data
        if (!$this->isValidImage($imageData)) {
            throw new Exception('Downloaded file is not a valid image');
        }
        
        if (file_put_contents($filepath, $imageData) === false) {
            throw new Exception('Failed to save image to: ' . $filepath);
        }
        
        return '/assets/images/generated/' . $filename;
    }
    
    /**
     * Generate filename from title
     */
    private function generateFilename($title) {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        $slug = substr($slug, 0, 50); // Limit length
        
        $timestamp = date('Y-m-d_H-i-s');
        $random = mt_rand(1000, 9999);
        
        return "{$slug}_{$timestamp}_{$random}.jpg";
    }
    
    /**
     * Validate image data
     */
    private function isValidImage($data) {
        $imageInfo = @getimagesizefromstring($data);
        return $imageInfo !== false;
    }
    
    /**
     * Process base64 image data
     */
    private function processBase64Image($base64Data) {
        $imageData = base64_decode($base64Data);
        $tempFile = tempnam(sys_get_temp_dir(), 'ai_image_');
        file_put_contents($tempFile, $imageData);
        
        // In a real implementation, you'd upload this to your server or CDN
        // For now, return a placeholder
        unlink($tempFile);
        return "https://picsum.photos/seed/" . md5($base64Data) . "/1024/1024.jpg";
    }
    
    /**
     * Get API key from configuration
     */
    private function getApiKey($provider) {
        // In a real implementation, these would come from environment variables or config
        switch ($provider) {
            case 'openai':
                return $_ENV['OPENAI_API_KEY'] ?? '';
            case 'stability':
                return $_ENV['STABILITY_API_KEY'] ?? '';
            case 'replicate':
                return $_ENV['REPLICATE_API_KEY'] ?? '';
            default:
                return '';
        }
    }
    
    /**
     * Generate multiple image options for selection
     */
    public function generateImageOptions($title, $content = '', $category = '', $count = 3) {
        $options = [];
        
        for ($i = 0; $i < $count; $i++) {
            $result = $this->generateImageForNews($title, $content, $category);
            if ($result['success']) {
                $options[] = $result;
            }
        }
        
        return $options;
    }
    
    /**
     * Batch generate images for multiple articles
     */
    public function batchGenerateImages($articles) {
        $results = [];
        
        foreach ($articles as $article) {
            $result = $this->generateImageForNews(
                $article['title'],
                $article['content'] ?? '',
                $article['category'] ?? ''
            );
            
            $results[] = [
                'article_id' => $article['id'],
                'result' => $result
            ];
            
            // Add delay to avoid rate limiting
            sleep(1);
        }
        
        return $results;
    }
}

// Helper function for easy access
function generate_news_image($title, $content = '', $category = '', $provider = 'openai') {
    static $generator = null;
    
    if ($generator === null) {
        $generator = new ImageGenerator($provider);
    }
    
    return $generator->generateImageForNews($title, $content, $category);
}
?>
