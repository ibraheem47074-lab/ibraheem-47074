<?php
/**
 * Smart Prompt Generator for AI Image Generation
 * Creates context-aware prompts based on news categories and content
 */

require_once __DIR__ . '/../config/database.php';

class SmartPromptGenerator {
    private $conn;
    private $categoryPrompts;
    private $keywordMappings;
    private $styleTemplates;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->initializeCategoryPrompts();
        $this->initializeKeywordMappings();
        $this->initializeStyleTemplates();
    }
    
    /**
     * Initialize category-specific prompt templates
     */
    private function initializeCategoryPrompts() {
        $this->categoryPrompts = [
            'Politics' => [
                'scene' => 'press conference, government building, parliamentary session, political rally, official meeting',
                'style' => 'formal, professional, dignified, serious tone',
                'elements' => 'politicians, microphones, flags, official podium, press credentials',
                'lighting' => 'professional lighting, clear visibility, formal setting'
            ],
            'War' => [
                'scene' => 'conflict zone, military operation, strategic location, battlefield, command center',
                'style' => 'photojournalistic, dramatic, impactful, respectful',
                'elements' => 'military personnel, equipment, strategic positions, official briefings',
                'lighting' => 'dramatic lighting, natural light, authentic war photography style'
            ],
            'Business' => [
                'scene' => 'modern office, stock exchange, business meeting, corporate headquarters, financial district',
                'style' => 'professional, clean, corporate, sophisticated',
                'elements' => 'business professionals, charts, computers, office environment, financial data',
                'lighting' => 'bright, professional office lighting, clean and clear'
            ],
            'Technology' => [
                'scene' => 'tech laboratory, modern data center, innovation hub, startup office, research facility',
                'style' => 'modern, futuristic, clean, innovative',
                'elements' => 'computers, servers, digital devices, scientists, engineers, cutting-edge technology',
                'lighting' => 'modern lighting, blue/white tech ambiance, clean and bright'
            ],
            'Sports' => [
                'scene' => 'stadium, sports arena, playing field, athletic competition, sports venue',
                'style' => 'dynamic, action-oriented, energetic, vibrant',
                'elements' => 'athletes, sports equipment, spectators, action shots, competitive moments',
                'lighting' => 'bright stadium lighting, dynamic action lighting, natural outdoor light'
            ],
            'Entertainment' => [
                'scene' => 'red carpet event, concert venue, movie set, theater, entertainment venue',
                'style' => 'glamorous, vibrant, exciting, celebrity-focused',
                'elements' => 'celebrities, fans, cameras, stage, entertainment equipment',
                'lighting' => 'dramatic stage lighting, glamorous ambiance, bright and colorful'
            ],
            'Health' => [
                'scene' => 'modern hospital, medical facility, research laboratory, healthcare setting',
                'style' => 'clean, professional, medical, trustworthy',
                'elements' => 'medical professionals, patients, medical equipment, clean environment',
                'lighting' => 'bright, clean medical lighting, professional and sterile appearance'
            ],
            'Science' => [
                'scene' => 'research laboratory, scientific facility, university, research institute',
                'style' => 'scientific, academic, professional, innovative',
                'elements' => 'scientists, laboratory equipment, research materials, scientific instruments',
                'lighting' => 'clean laboratory lighting, professional scientific environment'
            ],
            'Pakistan' => [
                'scene' => 'Pakistani landmarks, local streets, cultural sites, Pakistani context',
                'style' => 'culturally authentic, local context, realistic Pakistani setting',
                'elements' => 'Pakistani people, local architecture, cultural elements, national symbols',
                'lighting' => 'natural Pakistani lighting, authentic local atmosphere'
            ],
            'International' => [
                'scene' => 'global landmarks, international settings, world capitals, global context',
                'style' => 'international, worldly, global perspective',
                'elements' => 'diverse people, international symbols, global landmarks, cultural diversity',
                'lighting' => 'natural international lighting, authentic global atmosphere'
            ]
        ];
    }
    
    /**
     * Initialize keyword to category mappings
     */
    private function initializeKeywordMappings() {
        $this->keywordMappings = [
            // Politics keywords
            'election' => 'Politics',
            'government' => 'Politics',
            'parliament' => 'Politics',
            'president' => 'Politics',
            'prime minister' => 'Politics',
            'policy' => 'Politics',
            'political' => 'Politics',
            'vote' => 'Politics',
            'campaign' => 'Politics',
            'minister' => 'Politics',
            
            // War/Conflict keywords
            'war' => 'War',
            'conflict' => 'War',
            'attack' => 'War',
            'military' => 'War',
            'troops' => 'War',
            'battle' => 'War',
            'strike' => 'War',
            'defense' => 'War',
            'security' => 'War',
            'terrorism' => 'War',
            
            // Business keywords
            'economy' => 'Business',
            'market' => 'Business',
            'stock' => 'Business',
            'financial' => 'Business',
            'company' => 'Business',
            'business' => 'Business',
            'trade' => 'Business',
            'investment' => 'Business',
            'revenue' => 'Business',
            'profit' => 'Business',
            
            // Technology keywords
            'technology' => 'Technology',
            'software' => 'Technology',
            'internet' => 'Technology',
            'digital' => 'Technology',
            'computer' => 'Technology',
            'smartphone' => 'Technology',
            'AI' => 'Technology',
            'data' => 'Technology',
            'cyber' => 'Technology',
            'innovation' => 'Technology',
            
            // Sports keywords
            'sport' => 'Sports',
            'game' => 'Sports',
            'match' => 'Sports',
            'player' => 'Sports',
            'team' => 'Sports',
            'championship' => 'Sports',
            'tournament' => 'Sports',
            'athlete' => 'Sports',
            'football' => 'Sports',
            'cricket' => 'Sports',
            
            // Entertainment keywords
            'movie' => 'Entertainment',
            'music' => 'Entertainment',
            'concert' => 'Entertainment',
            'celebrity' => 'Entertainment',
            'actor' => 'Entertainment',
            'singer' => 'Entertainment',
            'film' => 'Entertainment',
            'entertainment' => 'Entertainment',
            'hollywood' => 'Entertainment',
            'bollywood' => 'Entertainment',
            
            // Health keywords
            'health' => 'Health',
            'medical' => 'Health',
            'hospital' => 'Health',
            'disease' => 'Health',
            'treatment' => 'Health',
            'medicine' => 'Health',
            'doctor' => 'Health',
            'patient' => 'Health',
            'healthcare' => 'Health',
            'covid' => 'Health',
            
            // Science keywords
            'science' => 'Science',
            'research' => 'Science',
            'study' => 'Science',
            'scientist' => 'Science',
            'university' => 'Science',
            'discovery' => 'Science',
            'experiment' => 'Science',
            'space' => 'Science',
            'climate' => 'Science',
            'environment' => 'Science'
        ];
    }
    
    /**
     * Initialize style templates
     */
    private function initializeStyleTemplates() {
        $this->styleTemplates = [
            'breaking_news' => [
                'style' => 'urgent, dramatic, high-impact, immediate',
                'composition' => 'center-focused, tight framing, dramatic angle',
                'mood' => 'serious, important, attention-grabbing'
            ],
            'feature_story' => [
                'style' => 'artistic, thoughtful, well-composed, narrative',
                'composition' => 'balanced, rule of thirds, natural lighting',
                'mood' => 'engaging, storytelling, professional'
            ],
            'analysis' => [
                'style' => 'professional, clean, informative, analytical',
                'composition' => 'structured, clear, organized',
                'mood' => 'authoritative, trustworthy, educational'
            ],
            'human_interest' => [
                'style' => 'emotional, authentic, relatable, intimate',
                'composition' => 'natural, candid, close-up when appropriate',
                'mood' => 'empathetic, human, touching'
            ]
        ];
    }
    
    /**
     * Generate smart prompt for news article
     */
    public function generatePrompt($title, $content = '', $categoryId = null, $style = 'realistic') {
        // Detect category from title/content if not provided
        $detectedCategory = $this->detectCategory($title, $content, $categoryId);
        
        // Get category-specific elements
        $categoryData = $this->getCategoryData($detectedCategory);
        
        // Extract key entities and concepts
        $entities = $this->extractEntities($title, $content);
        
        // Determine story type
        $storyType = $this->determineStoryType($title, $content);
        
        // Generate base prompt
        $basePrompt = $this->generateBasePrompt($title, $categoryData, $entities, $storyType);
        
        // Add style and quality modifiers
        $styleModifiers = $this->getStyleModifiers($style, $storyType);
        
        // Generate negative prompts
        $negativePrompts = $this->generateNegativePrompts($detectedCategory);
        
        return [
            'prompt' => $basePrompt,
            'negative_prompt' => $negativePrompts,
            'category' => $detectedCategory,
            'story_type' => $storyType,
            'entities' => $entities,
            'style' => $style,
            'quality' => 'high',
            'metadata' => [
                'category_data' => $categoryData,
                'style_modifiers' => $styleModifiers,
                'confidence' => $this->calculateConfidence($detectedCategory, $entities)
            ]
        ];
    }
    
    /**
     * Detect category from title and content
     */
    private function detectCategory($title, $content, $categoryId = null) {
        // If category ID is provided, get category name from database
        if ($categoryId) {
            $query = "SELECT name FROM categories WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $categoryId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                return $row['name'];
            }
        }
        
        // Detect from keywords
        $text = strtolower($title . ' ' . $content);
        $categoryScores = [];
        
        foreach ($this->keywordMappings as $keyword => $category) {
            if (strpos($text, $keyword) !== false) {
                $categoryScores[$category] = ($categoryScores[$category] ?? 0) + 1;
            }
        }
        
        if (!empty($categoryScores)) {
            arsort($categoryScores);
            return array_key_first($categoryScores);
        }
        
        // Default to International if no category detected
        return 'International';
    }
    
    /**
     * Get category-specific data
     */
    private function getCategoryData($category) {
        return $this->categoryPrompts[$category] ?? $this->categoryPrompts['International'];
    }
    
    /**
     * Extract key entities from text
     */
    private function extractEntities($title, $content) {
        $text = $title . ' ' . $content;
        $entities = [];
        
        // Extract people names (simple pattern matching)
        preg_match_all('/\b[A-Z][a-z]+ [A-Z][a-z]+\b/', $text, $people);
        if (!empty($people[0])) {
            $entities['people'] = array_unique($people[0]);
        }
        
        // Extract organizations (simple pattern)
        preg_match_all('/\b[A-Z][A-Z]+\b/', $text, $organizations);
        if (!empty($organizations[0])) {
            $entities['organizations'] = array_unique($organizations[0]);
        }
        
        // Extract locations (simple pattern)
        preg_match_all('/\b[A-Z][a-z]+(?: [A-Z][a-z]+)*\b/', $text, $locations);
        if (!empty($locations[0])) {
            $entities['locations'] = array_unique($locations[0]);
        }
        
        // Extract key concepts/themes
        $concepts = $this->extractConcepts($text);
        if (!empty($concepts)) {
            $entities['concepts'] = $concepts;
        }
        
        return $entities;
    }
    
    /**
     * Extract key concepts from text
     */
    private function extractConcepts($text) {
        $concepts = [];
        $conceptKeywords = [
            'innovation', 'crisis', 'celebration', 'protest', 'disaster', 'achievement',
            'controversy', 'breakthrough', 'scandal', 'victory', 'tragedy', 'success',
            'failure', 'discovery', 'launch', 'agreement', 'conflict', 'cooperation'
        ];
        
        foreach ($conceptKeywords as $concept) {
            if (strpos(strtolower($text), $concept) !== false) {
                $concepts[] = $concept;
            }
        }
        
        return $concepts;
    }
    
    /**
     * Determine story type
     */
    private function determineStoryType($title, $content) {
        $text = strtolower($title . ' ' . $content);
        
        // Breaking news indicators
        $breakingKeywords = ['breaking', 'urgent', 'developing', 'just in', 'alert', 'emergency'];
        foreach ($breakingKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return 'breaking_news';
            }
        }
        
        // Human interest indicators
        $humanInterestKeywords = ['story', 'journey', 'struggle', 'triumph', 'family', 'community'];
        foreach ($humanInterestKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return 'human_interest';
            }
        }
        
        // Analysis indicators
        $analysisKeywords = ['analysis', 'report', 'study', 'research', 'findings', 'investigation'];
        foreach ($analysisKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return 'analysis';
            }
        }
        
        return 'feature_story';
    }
    
    /**
     * Generate base prompt
     */
    private function generateBasePrompt($title, $categoryData, $entities, $storyType) {
        $prompt = "Professional news photograph depicting: " . $title;
        
        // Add scene description
        if (!empty($categoryData['scene'])) {
            $prompt .= ". Setting: " . $categoryData['scene'];
        }
        
        // Add relevant entities
        if (!empty($entities['people'])) {
            $prompt .= ". Featuring: " . implode(', ', array_slice($entities['people'], 0, 3));
        }
        
        if (!empty($entities['locations'])) {
            $prompt .= ". Location: " . implode(', ', array_slice($entities['locations'], 0, 2));
        }
        
        // Add style and mood
        if (!empty($categoryData['style'])) {
            $prompt .= ". Style: " . $categoryData['style'];
        }
        
        if (!empty($categoryData['elements'])) {
            $prompt .= ". Elements: " . $categoryData['elements'];
        }
        
        // Add story type specific elements
        $storyTypeData = $this->styleTemplates[$storyType] ?? $this->styleTemplates['feature_story'];
        if (!empty($storyTypeData['composition'])) {
            $prompt .= ". Composition: " . $storyTypeData['composition'];
        }
        
        // Add lighting information
        if (!empty($categoryData['lighting'])) {
            $prompt .= ". Lighting: " . $categoryData['lighting'];
        }
        
        // Add quality and technical specifications
        $prompt .= ". Technical: high resolution, professional news photography, photojournalistic quality, publication-ready";
        
        return $prompt;
    }
    
    /**
     * Get style modifiers
     */
    private function getStyleModifiers($style, $storyType) {
        $modifiers = [];
        
        switch ($style) {
            case 'realistic':
                $modifiers[] = 'photorealistic';
                $modifiers[] = 'natural colors';
                $modifiers[] = 'authentic';
                break;
            case 'dramatic':
                $modifiers[] = 'high contrast';
                $modifiers[] = 'dramatic lighting';
                $modifiers[] = 'impactful';
                break;
            case 'artistic':
                $modifiers[] = 'artistic composition';
                $modifiers[] = 'creative angle';
                $modifiers[] = 'visually striking';
                break;
        }
        
        // Add story type modifiers
        if ($storyType === 'breaking_news') {
            $modifiers[] = 'immediate';
            $modifiers[] = 'urgent';
        }
        
        return $modifiers;
    }
    
    /**
     * Generate negative prompts
     */
    private function generateNegativePrompts($category) {
        $baseNegative = [
            'cartoon', 'anime', 'illustration', 'drawing', 'painting',
            'text', 'watermark', 'signature', 'logo', 'branding',
            'blurry', 'low quality', 'pixelated', 'compressed',
            'inappropriate content', 'offensive material'
        ];
        
        // Category-specific negative prompts
        $categorySpecific = [];
        
        if ($category === 'Politics') {
            $categorySpecific[] = 'biased representation';
            $categorySpecific[] = 'propaganda style';
        } elseif ($category === 'War') {
            $categorySpecific[] = 'excessive violence';
            $categorySpecific[] = 'graphic content';
        } elseif ($category === 'Health') {
            $categorySpecific[] = 'medical inaccuracies';
            $categorySpecific[] = 'misleading health information';
        }
        
        return array_merge($baseNegative, $categorySpecific);
    }
    
    /**
     * Calculate confidence score for the generated prompt
     */
    private function calculateConfidence($category, $entities) {
        $confidence = 0.5; // Base confidence
        
        // Increase confidence if category is well-detected
        if (isset($this->categoryPrompts[$category])) {
            $confidence += 0.2;
        }
        
        // Increase confidence if entities were extracted
        if (!empty($entities['people'])) {
            $confidence += 0.1;
        }
        if (!empty($entities['locations'])) {
            $confidence += 0.1;
        }
        if (!empty($entities['concepts'])) {
            $confidence += 0.1;
        }
        
        return min($confidence, 1.0);
    }
    
    /**
     * Get available categories
     */
    public function getAvailableCategories() {
        return array_keys($this->categoryPrompts);
    }
    
    /**
     * Add custom category prompt
     */
    public function addCategoryPrompt($category, $promptData) {
        $this->categoryPrompts[$category] = $promptData;
    }
    
    /**
     * Update keyword mapping
     */
    public function addKeywordMapping($keyword, $category) {
        $this->keywordMappings[strtolower($keyword)] = $category;
    }
}
?>
