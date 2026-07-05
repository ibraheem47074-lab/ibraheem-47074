<?php
/**
 * AI Fake News Detection System
 * Advanced credibility analysis and fake news detection for PK Live News
 */

class AIFakeNewsDetector {
    private $db;
    private $aiModels = [];
    private $trustedSources = [];
    private $contentPatterns = [];
    
    public function __construct($database) {
        $this->db = $database;
        $this->loadTrustedSources();
        $this->loadContentPatterns();
        $this->initializeAIModels();
    }
    
    /**
     * Main analysis function - analyzes news article for credibility
     */
    public function analyzeArticle($newsId) {
        try {
            // Get article data
            $article = $this->getArticleData($newsId);
            if (!$article) {
                throw new Exception("Article not found");
            }
            
            // Perform comprehensive analysis
            $analysis = [
                'news_id' => $newsId,
                'analysis_date' => date('Y-m-d H:i:s'),
                'analysis_method' => 'AI_MULTIMODEL',
                'ai_model_version' => 'v2.1',
                'processing_time_ms' => 0
            ];
            
            $startTime = microtime(true);
            
            // Content analysis components
            $analysis['title_credibility'] = $this->analyzeTitle($article['title']);
            $analysis['content_credibility'] = $this->analyzeContent($article['content']);
            $analysis['source_credibility'] = $this->analyzeSource($article['source_url'], $article['source_name']);
            $analysis['factual_accuracy'] = $this->analyzeFactualAccuracy($article);
            
            // Risk indicators
            $analysis['sensationalism_score'] = $this->detectSensationalism($article);
            $analysis['emotional_manipulation'] = $this->detectEmotionalManipulation($article);
            $analysis['clickbait_score'] = $this->detectClickbait($article['title']);
            $analysis['propaganda_indicators'] = $this->detectPropaganda($article);
            
            // Technical analysis
            $analysis['grammar_score'] = $this->analyzeGrammar($article['content']);
            $analysis['readability_score'] = $this->analyzeReadability($article['content']);
            $analysis['factual_density'] = $this->calculateFactualDensity($article['content']);
            
            // Source verification
            $sourceVerification = $this->verifySource($article['source_url']);
            $analysis['source_verified'] = $sourceVerification['verified'];
            $analysis['source_reputation_score'] = $sourceVerification['reputation_score'];
            $analysis['cross_reference_count'] = $this->countCrossReferences($article);
            
            // Calculate overall scores
            $analysis['credibility_score'] = $this->calculateOverallCredibility($analysis);
            $analysis['confidence_level'] = $this->calculateConfidence($analysis);
            $analysis['risk_level'] = $this->determineRiskLevel($analysis);
            $analysis['content_category'] = $this->categorizeContent($analysis);
            $analysis['requires_review'] = $this->requiresReview($analysis);
            $analysis['auto_flagged'] = $this->shouldAutoFlag($analysis);
            
            $analysis['processing_time_ms'] = round((microtime(true) - $startTime) * 1000);
            
            // Save analysis to database
            $this->saveAnalysis($analysis);
            
            // Create alerts if necessary
            $this->createAlerts($newsId, $analysis);
            
            // Update news article with credibility score
            $this->updateNewsCredibility($newsId, $analysis);
            
            return $analysis;
            
        } catch (Exception $e) {
            error_log("AI Analysis Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Analyze article title for credibility indicators
     */
    private function analyzeTitle($title) {
        $score = 100.00; // Start with perfect score
        
        // Check for clickbait patterns
        $clickbaitPatterns = [
            '/you won\'t believe/i',
            '/what happens next/i',
            '/the truth about/i',
            '/secret revealed/i',
            '/shocking/i',
            '/unbelievable/i',
            '/mind-blowing/i'
        ];
        
        foreach ($clickbaitPatterns as $pattern) {
            if (preg_match($pattern, $title)) {
                $score -= 15;
            }
        }
        
        // Check for excessive punctuation
        $exclamationCount = substr_count($title, '!') + substr_count($title, '?');
        if ($exclamationCount > 2) {
            $score -= 10;
        }
        
        // Check for ALL CAPS
        if (strtoupper($title) === $title && strlen($title) > 10) {
            $score -= 8;
        }
        
        // Check title length (very short or very long titles can be suspicious)
        $titleLength = strlen($title);
        if ($titleLength < 10 || $titleLength > 150) {
            $score -= 5;
        }
        
        return max(0, min(100, $score));
    }
    
    /**
     * Analyze article content for credibility
     */
    private function analyzeContent($content) {
        $score = 100.00;
        
        // Check content length
        $wordCount = str_word_count($content);
        if ($wordCount < 50) {
            $score -= 20; // Very short content is suspicious
        } elseif ($wordCount < 200) {
            $score -= 10;
        }
        
        // Check for citations and references
        $citationPatterns = [
            '/according to/i',
            '/said/i',
            '/reported/i',
            '/stated/i',
            '/https?:\/\/[^\s]+/',
            '/\(\d{4}\)/', // Year references
            '/\[\d+\]/' // Numbered references
        ];
        
        $citationCount = 0;
        foreach ($citationPatterns as $pattern) {
            $citationCount += preg_match_all($pattern, $content);
        }
        
        if ($citationCount == 0) {
            $score -= 15; // No citations
        } elseif ($citationCount < 3) {
            $score -= 5;
        }
        
        // Check for balanced reporting (both sides mentioned)
        $balanceIndicators = [
            '/however/i',
            '/on the other hand/i',
            '/critics say/i',
            '/supporters argue/i',
            '/opponents claim/i'
        ];
        
        $balanceCount = 0;
        foreach ($balanceIndicators as $indicator) {
            $balanceCount += preg_match_all($indicator, $content);
        }
        
        if ($balanceCount == 0 && $wordCount > 200) {
            $score -= 10; // No balanced reporting for longer articles
        }
        
        return max(0, min(100, $score));
    }
    
    /**
     * Analyze source credibility
     */
    private function analyzeSource($sourceUrl, $sourceName) {
        $score = 50.00; // Default neutral score
        
        if (empty($sourceUrl)) {
            return 30.00; // Low score for no source
        }
        
        $domain = parse_url($sourceUrl, PHP_URL_HOST);
        if ($domain) {
            $domain = strtolower($domain);
            
            // Check against trusted sources
            if (isset($this->trustedSources[$domain])) {
                $source = $this->trustedSources[$domain];
                $score = $source['trust_score'];
            } else {
                // Analyze domain characteristics
                $score = $this->analyzeDomainCharacteristics($domain);
            }
        }
        
        return max(0, min(100, $score));
    }
    
    /**
     * Analyze domain characteristics for credibility
     */
    private function analyzeDomainCharacteristics($domain) {
        $score = 50.00;
        
        // Check for suspicious indicators
        $suspiciousPatterns = [
            '/.*\.tk$/', // Free domains
            '/.*\.ml$/', 
            '/.*\.ga$/',
            '/.*fake.*news.*/i',
            '/.*satire.*/i',
            '/.*conspiracy.*/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $domain)) {
                $score -= 30;
            }
        }
        
        // Check for established news domains
        $establishedPatterns = [
            '/.*\.com$/',
            '/.*\.org$/',
            '/.*\.net$/',
            '/.*gov\..*/',
            '/.*\.edu$/'
        ];
        
        foreach ($establishedPatterns as $pattern) {
            if (preg_match($pattern, $domain)) {
                $score += 10;
            }
        }
        
        return max(0, min(100, $score));
    }
    
    /**
     * Analyze factual accuracy using various heuristics
     */
    private function analyzeFactualAccuracy($article) {
        $score = 75.00; // Default score
        
        $content = strtolower($article['content']);
        
        // Check for specific numbers and data
        $specificDataCount = preg_match_all('/\b\d+%|\b\d+\s*(million|billion|thousand)|\$\d+\b/', $content);
        if ($specificDataCount > 0) {
            $score += min(10, $specificDataCount * 2);
        }
        
        // Check for quotes
        $quoteCount = preg_match_all('/"[^"]+"/', $content);
        if ($quoteCount > 0) {
            $score += min(8, $quoteCount * 2);
        }
        
        // Check for dates and times
        $dateCount = preg_match_all('/\b(january|february|march|april|may|june|july|august|september|october|november|december)\s+\d{1,2},?\s+\d{4}\b/', $content);
        if ($dateCount > 0) {
            $score += min(5, $dateCount * 2);
        }
        
        // Penalize for vague statements
        $vaguePatterns = [
            '/some people say/i',
            '/experts believe/i',
            '/sources say/i',
            '/it is said that/i',
            '/apparently/i'
        ];
        
        foreach ($vaguePatterns as $pattern) {
            $score -= preg_match_all($pattern, $content) * 3;
        }
        
        return max(0, min(100, $score));
    }
    
    /**
     * Detect sensationalism in content
     */
    private function detectSensationalism($article) {
        $score = 0;
        $content = strtolower($article['title'] . ' ' . $article['content']);
        
        $sensationalWords = [
            'shocking', 'unbelievable', 'incredible', 'amazing', 'miracle',
            'breakthrough', 'revolutionary', 'game-changing', 'mind-blowing',
            'extraordinary', 'stunning', 'jaw-dropping', 'breathtaking'
        ];
        
        foreach ($sensationalWords as $word) {
            $score += substr_count($content, $word) * 5;
        }
        
        // Check for excessive superlatives
        $superlativeCount = preg_match_all('/\b(best|worst|greatest|biggest|smallest|most|least)\b/i', $content);
        $score += $superlativeCount * 3;
        
        return min(100, $score);
    }
    
    /**
     * Detect emotional manipulation
     */
    private function detectEmotionalManipulation($article) {
        $score = 0;
        $content = strtolower($article['title'] . ' ' . $article['content']);
        
        $emotionalWords = [
            'heartbreaking', 'terrifying', 'outrageous', 'disgusting', 'horrifying',
            'devastating', 'tragic', 'alarming', 'disturbing', 'shocking'
        ];
        
        foreach ($emotionalWords as $word) {
            $score += substr_count($content, $word) * 8;
        }
        
        // Check for fear-based language
        $fearPatterns = [
            '/danger.*imminent/i',
            '/threat.*serious/i',
            '/warning.*urgent/i',
            '/crisis.*deepening/i'
        ];
        
        foreach ($fearPatterns as $pattern) {
            $score += preg_match_all($pattern, $content) * 10;
        }
        
        return min(100, $score);
    }
    
    /**
     * Detect clickbait patterns
     */
    private function detectClickbait($title) {
        $score = 0;
        $title = strtolower($title);
        
        $clickbaitPatterns = [
            '/you won\'t believe/',
            '/what happens next/',
            '/the truth about/',
            '/secret revealed/',
            '/doctors hate/',
            '/number \d+ will shock you/',
            '/this will change your life/',
            '/why you should/',
            '/the real reason/'
        ];
        
        foreach ($clickbaitPatterns as $pattern) {
            if (preg_match($pattern, $title)) {
                $score += 25;
            }
        }
        
        // Check for listicles without numbers
        if (preg_match('/ways|tips|tricks|reasons|facts/', $title) && !preg_match('/\d+/', $title)) {
            $score += 15;
        }
        
        return min(100, $score);
    }
    
    /**
     * Detect propaganda indicators
     */
    private function detectPropaganda($article) {
        $score = 0;
        $content = strtolower($article['title'] . ' ' . $article['content']);
        
        $propagandaPatterns = [
            '/they don\'t want you to know/',
            '/hidden truth/',
            '/cover up/',
            '/conspiracy/',
            '/mainstream media won\'t tell/',
            '/wake up/',
            '/sheeple/',
            '/agenda/'
        ];
        
        foreach ($propagandaPatterns as $pattern) {
            $score += preg_match_all($pattern, $content) * 15;
        }
        
        // Check for us vs them language
        $usVsThemPatterns = [
            '/we.*they/',
            '/our.*their/',
            '/us.*them/'
        ];
        
        foreach ($usVsThemPatterns as $pattern) {
            $score += preg_match_all($pattern, $content) * 8;
        }
        
        return min(100, $score);
    }
    
    /**
     * Analyze grammar quality
     */
    private function analyzeGrammar($content) {
        $score = 100.00;
        
        // Basic grammar checks
        $sentences = preg_split('/[.!?]+/', $content);
        $sentenceCount = count(array_filter($sentences, 'trim'));
        
        if ($sentenceCount == 0) {
            return 0;
        }
        
        // Check for sentence length variation
        $sentenceLengths = array_map('strlen', array_filter($sentences, 'trim'));
        $avgLength = array_sum($sentenceLengths) / count($sentenceLengths);
        
        if ($avgLength > 200) {
            $score -= 15; // Sentences too long
        } elseif ($avgLength < 10) {
            $score -= 15; // Sentences too short
        }
        
        // Check for repeated words
        $words = str_word_count(strtolower($content), 1);
        $wordCounts = array_count_values($words);
        
        foreach ($wordCounts as $word => $count) {
            if ($count > 10 && strlen($word) > 3) {
                $score -= min(20, $count - 10);
            }
        }
        
        // Check for proper capitalization
        $capitalizationErrors = 0;
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (!empty($sentence) && !preg_match('/^[A-Z]/', $sentence)) {
                $capitalizationErrors++;
            }
        }
        
        $score -= min(25, $capitalizationErrors * 5);
        
        return max(0, min(100, $score));
    }
    
    /**
     * Analyze readability
     */
    private function analyzeReadability($content) {
        $wordCount = str_word_count($content);
        $sentenceCount = preg_match_all('/[.!?]+/', $content);
        $syllableCount = $this->countSyllables($content);
        
        if ($wordCount == 0 || $sentenceCount == 0) {
            return 50;
        }
        
        // Flesch Reading Ease Score
        $avgSentenceLength = $wordCount / $sentenceCount;
        $avgSyllablesPerWord = $syllableCount / $wordCount;
        
        $fleschScore = 206.835 - (1.015 * $avgSentenceLength) - (84.6 * $avgSyllablesPerWord);
        
        // Convert to 0-100 scale (higher is more readable)
        $readabilityScore = max(0, min(100, $fleschScore));
        
        return $readabilityScore;
    }
    
    /**
     * Count syllables in text (simplified)
     */
    private function countSyllables($text) {
        $words = str_word_count($text, 1);
        $syllableCount = 0;
        
        foreach ($words as $word) {
            $syllableCount += max(1, $this->countWordSyllables($word));
        }
        
        return $syllableCount;
    }
    
    /**
     * Count syllables in a single word (simplified algorithm)
     */
    private function countWordSyllables($word) {
        $word = strtolower($word);
        $syllables = 0;
        
        // Remove silent 'e' at the end
        if (substr($word, -1) == 'e') {
            $word = substr($word, 0, -1);
        }
        
        // Count vowel groups
        $vowelGroups = preg_match_all('/[aeiouy]+/', $word);
        $syllables = $vowelGroups;
        
        return max(1, $syllables);
    }
    
    /**
     * Calculate factual density
     */
    private function calculateFactualDensity($content) {
        $wordCount = str_word_count($content);
        if ($wordCount == 0) {
            return 0;
        }
        
        $factualElements = 0;
        
        // Count numbers, dates, proper nouns (simplified)
        $factualElements += preg_match_all('/\b\d+\b/', $content); // Numbers
        $factualElements += preg_match_all('/\b[A-Z][a-z]+\b/', $content); // Proper nouns
        $factualElements += preg_match_all('/\b\d{4}\b/', $content); // Years
        $factualElements += preg_match_all('/\b\$\d+\b/', $content); // Money
        $factualElements += preg_match_all('/\b\d+%\b/', $content); // Percentages
        
        $density = ($factualElements / $wordCount) * 100;
        
        return min(100, $density);
    }
    
    /**
     * Verify source against trusted sources database
     */
    private function verifySource($sourceUrl) {
        if (empty($sourceUrl)) {
            return ['verified' => false, 'reputation_score' => 0];
        }
        
        $domain = parse_url($sourceUrl, PHP_URL_HOST);
        if (!$domain) {
            return ['verified' => false, 'reputation_score' => 0];
        }
        
        $domain = strtolower($domain);
        
        if (isset($this->trustedSources[$domain])) {
            $source = $this->trustedSources[$domain];
            return [
                'verified' => $source['verified'],
                'reputation_score' => $source['reputation_score']
            ];
        }
        
        return ['verified' => false, 'reputation_score' => 50];
    }
    
    /**
     * Count cross-references to other sources
     */
    private function countCrossReferences($article) {
        $content = $article['title'] . ' ' . $article['content'];
        
        // Count external links
        $linkCount = preg_match_all('/https?:\/\/[^\s]+/', $content);
        
        // Count mentions of other news sources
        $sourceMentions = preg_match_all('/\b(reuters|ap|bbc|cnn|fox|al jazeera|dawn|geo|ary)\b/i', $content);
        
        return $linkCount + $sourceMentions;
    }
    
    /**
     * Calculate overall credibility score
     */
    private function calculateOverallCredibility($analysis) {
        $weights = [
            'title_credibility' => 0.15,
            'content_credibility' => 0.25,
            'source_credibility' => 0.20,
            'factual_accuracy' => 0.15,
            'grammar_score' => 0.10,
            'readability_score' => 0.05,
            'factual_density' => 0.10
        ];
        
        $score = 0;
        foreach ($weights as $factor => $weight) {
            if (isset($analysis[$factor])) {
                $score += $analysis[$factor] * $weight;
            }
        }
        
        // Apply penalties for high risk indicators
        $penalties = 0;
        $penalties += $analysis['sensationalism_score'] * 0.1;
        $penalties += $analysis['emotional_manipulation'] * 0.15;
        $penalties += $analysis['clickbait_score'] * 0.2;
        $penalties += $analysis['propaganda_indicators'] * 0.25;
        
        $finalScore = max(0, min(100, $score - $penalties));
        
        return round($finalScore, 2);
    }
    
    /**
     * Calculate confidence level in the analysis
     */
    private function calculateConfidence($analysis) {
        $confidence = 75.00; // Base confidence
        
        // Increase confidence if source is verified
        if ($analysis['source_verified']) {
            $confidence += 10;
        }
        
        // Increase confidence with more content
        if (isset($analysis['content_credibility']) && $analysis['content_credibility'] > 50) {
            $confidence += 5;
        }
        
        // Decrease confidence for very short articles
        if (isset($analysis['factual_density']) && $analysis['factual_density'] < 5) {
            $confidence -= 15;
        }
        
        return max(0, min(100, $confidence));
    }
    
    /**
     * Determine risk level
     */
    private function determineRiskLevel($analysis) {
        $credibilityScore = $analysis['credibility_score'];
        
        if ($credibilityScore >= 80) {
            return 'LOW';
        } elseif ($credibilityScore >= 60) {
            return 'MEDIUM';
        } elseif ($credibilityScore >= 40) {
            return 'HIGH';
        } else {
            return 'CRITICAL';
        }
    }
    
    /**
     * Categorize content
     */
    private function categorizeContent($analysis) {
        $credibilityScore = $analysis['credibility_score'];
        $riskLevel = $analysis['risk_level'];
        
        if ($credibilityScore >= 85 && $analysis['source_verified']) {
            return 'VERIFIED';
        } elseif ($credibilityScore >= 70) {
            return 'LIKELY_TRUE';
        } elseif ($credibilityScore >= 50) {
            return 'UNVERIFIED';
        } elseif ($credibilityScore >= 30) {
            return 'LIKELY_FALSE';
        } else {
            return 'FALSE';
        }
    }
    
    /**
     * Determine if article requires review
     */
    private function requiresReview($analysis) {
        $triggers = [
            $analysis['credibility_score'] < 60,
            $analysis['risk_level'] === 'HIGH' || $analysis['risk_level'] === 'CRITICAL',
            $analysis['propaganda_indicators'] > 50,
            $analysis['clickbait_score'] > 60,
            $analysis['emotional_manipulation'] > 60,
            !$analysis['source_verified'] && $analysis['credibility_score'] < 70
        ];
        
        return in_array(true, $triggers);
    }
    
    /**
     * Determine if article should be auto-flagged
     */
    private function shouldAutoFlag($analysis) {
        return $analysis['credibility_score'] < 40 || 
               $analysis['propaganda_indicators'] > 70 ||
               $analysis['risk_level'] === 'CRITICAL';
    }
    
    /**
     * Save analysis to database
     */
    private function saveAnalysis($analysis) {
        $sql = "INSERT INTO news_credibility_analysis (
            news_id, analysis_date, credibility_score, confidence_level,
            title_credibility, content_credibility, source_credibility, factual_accuracy,
            sensationalism_score, emotional_manipulation, clickbait_score, propaganda_indicators,
            grammar_score, readability_score, factual_density,
            source_verified, source_reputation_score, cross_reference_count,
            analysis_method, processing_time_ms, ai_model_version,
            risk_level, content_category, requires_review, auto_flagged
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bind_param(
            "isdddddddddddddddissdssii",
            $analysis['news_id'],
            $analysis['analysis_date'],
            $analysis['credibility_score'],
            $analysis['confidence_level'],
            $analysis['title_credibility'],
            $analysis['content_credibility'],
            $analysis['source_credibility'],
            $analysis['factual_accuracy'],
            $analysis['sensationalism_score'],
            $analysis['emotional_manipulation'],
            $analysis['clickbait_score'],
            $analysis['propaganda_indicators'],
            $analysis['grammar_score'],
            $analysis['readability_score'],
            $analysis['factual_density'],
            $analysis['source_verified'],
            $analysis['source_reputation_score'],
            $analysis['cross_reference_count'],
            $analysis['analysis_method'],
            $analysis['processing_time_ms'],
            $analysis['ai_model_version'],
            $analysis['risk_level'],
            $analysis['content_category'],
            $analysis['requires_review'],
            $analysis['auto_flagged']
        );
        
        return $stmt->execute();
    }
    
    /**
     * Create alerts for flagged content
     */
    private function createAlerts($newsId, $analysis) {
        $alerts = [];
        
        if ($analysis['credibility_score'] < 40) {
            $alerts[] = [
                'type' => 'LOW_CREDIBILITY',
                'severity' => 'CRITICAL',
                'message' => 'Article has very low credibility score: ' . $analysis['credibility_score']
            ];
        }
        
        if (!$analysis['source_verified']) {
            $alerts[] = [
                'type' => 'SOURCE_UNVERIFIED',
                'severity' => 'WARNING',
                'message' => 'Article source could not be verified'
            ];
        }
        
        if ($analysis['sensationalism_score'] > 60) {
            $alerts[] = [
                'type' => 'SENSATIONALISM',
                'severity' => 'WARNING',
                'message' => 'Article contains high levels of sensationalism'
            ];
        }
        
        if ($analysis['propaganda_indicators'] > 50) {
            $alerts[] = [
                'type' => 'PROPAGANDA',
                'severity' => 'CRITICAL',
                'message' => 'Article shows strong propaganda indicators'
            ];
        }
        
        if ($analysis['clickbait_score'] > 70) {
            $alerts[] = [
                'type' => 'CLICKBAIT',
                'severity' => 'WARNING',
                'message' => 'Article title appears to be clickbait'
            ];
        }
        
        if ($analysis['emotional_manipulation'] > 60) {
            $alerts[] = [
                'type' => 'MANIPULATION',
                'severity' => 'WARNING',
                'message' => 'Article contains emotionally manipulative content'
            ];
        }
        
        foreach ($alerts as $alert) {
            $this->saveAlert($newsId, $alert);
        }
    }
    
    /**
     * Save alert to database
     */
    private function saveAlert($newsId, $alert) {
        $sql = "INSERT INTO fake_news_alerts (
            news_id, alert_type, severity, message, status
        ) VALUES (?, ?, ?, ?, 'ACTIVE')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isss", $newsId, $alert['type'], $alert['severity'], $alert['message']);
        return $stmt->execute();
    }
    
    /**
     * Update news article with credibility information
     */
    private function updateNewsCredibility($newsId, $analysis) {
        $sql = "UPDATE news SET 
            credibility_score = ?, 
            credibility_status = ?, 
            last_credibility_check = ?
            WHERE id = ?";
        
        $status = $analysis['requires_review'] ? 'REVIEW_REQUIRED' : 'CHECKED';
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("dssi", $analysis['credibility_score'], $status, $analysis['analysis_date'], $newsId);
        return $stmt->execute();
    }
    
    /**
     * Get article data for analysis
     */
    private function getArticleData($newsId) {
        $sql = "SELECT id, title, content, source_url, source_name FROM news WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $newsId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Load trusted sources from database
     */
    private function loadTrustedSources() {
        // Check if trusted_sources table exists
        $table_check = $this->db->query("SHOW TABLES LIKE 'trusted_sources'");
        if ($table_check && $table_check->num_rows == 0) {
            // Create the table if it doesn't exist
            $create_table_sql = "CREATE TABLE `trusted_sources` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `domain_name` varchar(255) NOT NULL,
                `source_name` varchar(255) DEFAULT NULL,
                `trust_score` decimal(3,2) DEFAULT '0.50',
                `reputation_score` decimal(3,2) DEFAULT '0.50',
                `verified` tinyint(1) DEFAULT '0',
                `fact_check_rating` enum('high','medium','low','unknown') DEFAULT 'unknown',
                `bias_rating` enum('left','center-left','center','center-right','right','unknown') DEFAULT 'unknown',
                `country` varchar(100) DEFAULT NULL,
                `language` varchar(10) DEFAULT 'en',
                `category` varchar(100) DEFAULT NULL,
                `description` text DEFAULT NULL,
                `contact_info` varchar(500) DEFAULT NULL,
                `social_media_links` json DEFAULT NULL,
                `alexa_rank` int(11) DEFAULT NULL,
                `monthly_visitors` int(11) DEFAULT NULL,
                `founded_year` int(4) DEFAULT NULL,
                `owner` varchar(255) DEFAULT NULL,
                `active` tinyint(1) DEFAULT '1',
                `last_verified` timestamp NULL DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_domain` (`domain_name`),
                KEY `idx_trust_score` (`trust_score`),
                KEY `idx_verified` (`verified`),
                KEY `idx_active` (`active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->db->query($create_table_sql);
            
            // Insert some basic trusted sources
            $default_sources = [
                ['reuters.com', 'Reuters', 0.95, 0.92, 1, 'high', 'center', 'United Kingdom'],
                ['ap.org', 'Associated Press', 0.94, 0.91, 1, 'high', 'center', 'United States'],
                ['bbc.com', 'BBC News', 0.92, 0.89, 1, 'high', 'center-left', 'United Kingdom'],
                ['dawn.com', 'Dawn', 0.75, 0.72, 1, 'medium', 'center', 'Pakistan'],
                ['geo.tv', 'Geo News', 0.70, 0.67, 1, 'medium', 'center-right', 'Pakistan']
            ];
            
            foreach ($default_sources as $source) {
                $insert_sql = "INSERT IGNORE INTO trusted_sources 
                    (domain_name, source_name, trust_score, reputation_score, verified, fact_check_rating, bias_rating, country, active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt = $this->db->prepare($insert_sql);
                $stmt->bind_param('ssddisss', $source[0], $source[1], $source[2], $source[3], $source[4], $source[5], $source[6], $source[7]);
                $stmt->execute();
            }
        }
        
        $sql = "SELECT domain_name, trust_score, verified, reputation_score 
                FROM trusted_sources 
                WHERE active = 1";
        $result = $this->db->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $this->trustedSources[$row['domain_name']] = [
                    'trust_score' => $row['trust_score'],
                    'verified' => $row['verified'],
                    'reputation_score' => $row['reputation_score']
                ];
            }
        }
    }
    
    /**
     * Load content patterns for detection
     */
    private function loadContentPatterns() {
        // Check if content_patterns table exists
        $table_check = $this->db->query("SHOW TABLES LIKE 'content_patterns'");
        if ($table_check && $table_check->num_rows == 0) {
            // Create the table if it doesn't exist
            $create_table_sql = "CREATE TABLE `content_patterns` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `pattern_name` varchar(255) NOT NULL,
                `pattern_type` enum('sensationalism','bias','misinformation','clickbait','propaganda') NOT NULL,
                `pattern_regex` text DEFAULT NULL,
                `pattern_keywords` json DEFAULT NULL,
                `confidence_weight` decimal(3,2) DEFAULT '0.50',
                `description` text DEFAULT NULL,
                `active` tinyint(1) DEFAULT '1',
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `idx_pattern_type` (`pattern_type`),
                KEY `idx_active` (`active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->db->query($create_table_sql);
            
            // Insert some basic content patterns
            $default_patterns = [
                ['Breaking News Alert', 'sensationalism', NULL, '["breaking", "urgent", "alert", "shocking"]', 0.70, 'Detects sensational breaking news language'],
                ['Clickbait Headlines', 'clickbait', NULL, '["you won\'t believe", "shocking", "revealed", "secret"]', 0.75, 'Detects clickbait headline patterns'],
                ['Conspiracy Language', 'misinformation', NULL, '["conspiracy", "cover up", "hidden truth", "they don\'t want you to know"]', 0.80, 'Detects conspiracy theory language'],
                ['Emotional Manipulation', 'bias', NULL, '["outrageous", "disgusting", "horrifying", "unbelievable"]', 0.65, 'Detects emotionally manipulative language'],
                ['Unverified Claims', 'misinformation', NULL, '["sources say", "rumors suggest", "allegedly", "reportedly"]', 0.60, 'Detects unverified claim indicators']
            ];
            
            foreach ($default_patterns as $pattern) {
                $insert_sql = "INSERT IGNORE INTO content_patterns 
                    (pattern_name, pattern_type, pattern_regex, pattern_keywords, confidence_weight, description, active) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)";
                $stmt = $this->db->prepare($insert_sql);
                $keywords_json = json_encode(explode(', ', str_replace(['[', ']', '"'], '', $pattern[3])));
                $stmt->bind_param('ssssds', $pattern[0], $pattern[1], $pattern[2], $keywords_json, $pattern[4], $pattern[5]);
                $stmt->execute();
            }
        }
        
        $sql = "SELECT pattern_name, pattern_type, pattern_regex, pattern_keywords, confidence_weight 
                FROM content_patterns 
                WHERE active = 1";
        $result = $this->db->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $this->contentPatterns[] = $row;
            }
        }
    }
    
    /**
     * Initialize AI models (placeholder for future ML integration)
     */
    private function initializeAIModels() {
        // This would initialize actual ML models in a production environment
        $this->aiModels = [
            'text_classification' => 'bert-base-uncased-finetuned-fake-news',
            'sentiment_analysis' => 'roberta-base-sentiment',
            'propaganda_detection' => 'custom-propaganda-model-v2'
        ];
    }
    
    /**
     * Batch analyze multiple articles
     */
    public function batchAnalyze($newsIds = []) {
        if (empty($newsIds)) {
            // Get articles that haven't been analyzed
            $sql = "SELECT id FROM news WHERE credibility_status = 'PENDING' LIMIT 50";
            $result = $this->db->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                $newsIds[] = $row['id'];
            }
        }
        
        $results = [];
        foreach ($newsIds as $newsId) {
            $results[$newsId] = $this->analyzeArticle($newsId);
        }
        
        return $results;
    }
    
    /**
     * Get credibility report for an article
     */
    public function getCredibilityReport($newsId) {
        $sql = "SELECT * FROM news_credibility_analysis 
                WHERE news_id = ? 
                ORDER BY analysis_date DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $newsId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get high-risk articles for review
     */
    public function getHighRiskArticles($limit = 20) {
        $sql = "SELECT n.*, nca.credibility_score, nca.risk_level, nca.requires_review
                FROM news n
                JOIN news_credibility_analysis nca ON n.id = nca.news_id
                WHERE nca.risk_level IN ('HIGH', 'CRITICAL') OR nca.requires_review = 1
                ORDER BY nca.analysis_date DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $articles = [];
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        
        return $articles;
    }
}
?>
