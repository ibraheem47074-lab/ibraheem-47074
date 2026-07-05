<?php
/**
 * Sentiment Analysis Library for PK Live News
 * Uses dictionary-based approach for sentiment scoring
 */

class SentimentAnalyzer {
    private $positiveWords = [
        'good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'awesome', 'positive',
        'happy', 'joy', 'joyful', 'delighted', 'pleased', 'satisfied', 'thrilled', 'excited',
        'love', 'liked', 'like', 'enjoy', 'enjoyed', 'appreciate', 'appreciated', 'grateful',
        'success', 'successful', 'achieve', 'achieved', 'accomplish', 'accomplished', 'win', 'won',
        'beautiful', 'brilliant', 'outstanding', 'superb', 'perfect', 'best', 'better', 'improved',
        'hope', 'optimistic', 'confidence', 'confident', 'proud', 'celebrate', 'celebrated',
        'peace', 'peaceful', 'harmony', 'prosperity', 'prosperous', 'growth', 'progress', 'development',
        'breakthrough', 'innovation', 'innovative', 'advancement', 'milestone', 'achievement'
    ];
    
    private $negativeWords = [
        'bad', 'terrible', 'awful', 'horrible', 'disgusting', 'disappointing', 'negative',
        'sad', 'angry', 'angry', 'furious', 'upset', 'frustrated', 'annoyed', 'worried', 'concerned',
        'hate', 'hated', 'dislike', 'disliked', 'against', 'oppose', 'opposed', 'reject', 'rejected',
        'fail', 'failed', 'failure', 'lose', 'lost', 'loss', 'defeat', 'defeated', 'disaster',
        'ugly', 'worst', 'worse', 'poor', 'inadequate', 'insufficient', 'incompetent', 'useless',
        'fear', 'afraid', 'scared', 'terrified', 'panic', 'anxious', 'depressed', 'stress', 'stressful',
        'war', 'conflict', 'violence', 'violent', 'crime', 'criminal', 'illegal', 'corruption', 'corrupt',
        'crisis', 'emergency', 'danger', 'dangerous', 'threat', 'threatened', 'attack', 'attacked'
    ];
    
    private $intensifiers = [
        'very' => 1.5,
        'extremely' => 2.0,
        'really' => 1.3,
        'quite' => 1.2,
        'too' => 1.4,
        'so' => 1.3,
        'absolutely' => 1.8,
        'completely' => 1.6,
        'totally' => 1.6,
        'highly' => 1.7
    ];
    
    private $negators = [
        'not', 'no', 'never', 'none', 'nothing', 'neither', 'nowhere', 'hardly', 'rarely', 'seldom'
    ];
    
    public function analyze($text) {
        if (empty($text)) {
            return ['score' => 0, 'label' => 'neutral'];
        }
        
        // Clean and tokenize text
        $text = strtolower($text);
        $text = preg_replace('/[^a-zA-Z\s]/', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $score = 0;
        $wordCount = count($words);
        $negateNext = false;
        $intensity = 1.0;
        
        for ($i = 0; $i < $wordCount; $i++) {
            $word = $words[$i];
            
            // Check for negators
            if (in_array($word, $this->negators)) {
                $negateNext = true;
                continue;
            }
            
            // Check for intensifiers
            if (isset($this->intensifiers[$word])) {
                $intensity = $this->intensifiers[$word];
                continue;
            }
            
            // Calculate word score
            $wordScore = 0;
            if (in_array($word, $this->positiveWords)) {
                $wordScore = 1;
            } elseif (in_array($word, $this->negativeWords)) {
                $wordScore = -1;
            }
            
            // Apply negation and intensity
            if ($negateNext) {
                $wordScore *= -1;
                $negateNext = false;
            }
            
            $score += $wordScore * $intensity;
            
            // Reset intensity after use
            $intensity = 1.0;
        }
        
        // Normalize score
        if ($wordCount > 0) {
            $score = $score / sqrt($wordCount);
        }
        
        // Clamp score between -1 and 1
        $score = max(-1, min(1, $score));
        
        // Determine label
        $label = 'neutral';
        if ($score > 0.1) {
            $label = 'positive';
        } elseif ($score < -0.1) {
            $label = 'negative';
        }
        
        return [
            'score' => round($score, 2),
            'label' => $label,
            'word_count' => $wordCount,
            'positive_words' => $this->countWords($words, $this->positiveWords),
            'negative_words' => $this->countWords($words, $this->negativeWords)
        ];
    }
    
    private function countWords($textWords, $sentimentWords) {
        $count = 0;
        foreach ($textWords as $word) {
            if (in_array($word, $sentimentWords)) {
                $count++;
            }
        }
        return $count;
    }
    
    public function getSentimentIcon($label) {
        switch ($label) {
            case 'positive':
                return '😊';
            case 'negative':
                return '😔';
            default:
                return '😐';
        }
    }
    
    public function getSentimentColor($score) {
        if ($score > 0.1) {
            return '#28a745'; // Green
        } elseif ($score < -0.1) {
            return '#dc3545'; // Red
        } else {
            return '#6c757d'; // Gray
        }
    }
    
    public function getSentimentBadge($label) {
        switch ($label) {
            case 'positive':
                return 'success';
            case 'negative':
                return 'danger';
            default:
                return 'secondary';
        }
    }
}

// Global function for easy access
function analyze_sentiment($text) {
    static $analyzer = null;
    if ($analyzer === null) {
        $analyzer = new SentimentAnalyzer();
    }
    return $analyzer->analyze($text);
}
?>
