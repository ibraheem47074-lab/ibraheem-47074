<?php
/**
 * Fix Missing Database Tables for AI Fake News Detector
 */

require_once 'config/database.php';

echo "<h1>Creating Missing Database Tables</h1>\n";

// Create content_patterns table
echo "<h3>Creating content_patterns table...</h3>\n";

$contentPatternsSQL = "CREATE TABLE IF NOT EXISTS content_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pattern_name VARCHAR(100) NOT NULL,
    pattern_type ENUM('clickbait', 'sensationalism', 'propaganda', 'misinformation', 'satire', 'opinion') NOT NULL,
    pattern_regex TEXT NULL,
    pattern_keywords TEXT NULL,
    confidence_weight DECIMAL(3,2) DEFAULT 1.00,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $contentPatternsSQL)) {
    echo "<p style='color: green;'>✓ content_patterns table created successfully</p>\n";
} else {
    echo "<p style='color: red;'>✗ Error creating content_patterns table: " . mysqli_error($conn) . "</p>\n";
}

// Check if trusted_sources table exists
echo "<h3>Checking trusted_sources table...</h3>\n";

$checkTrusted = mysqli_query($conn, "SHOW TABLES LIKE 'trusted_sources'");
if (mysqli_num_rows($checkTrusted) > 0) {
    echo "<p style='color: green;'>✓ trusted_sources table exists</p>\n";
} else {
    echo "<p style='color: orange;'>⚠ trusted_sources table missing - creating it...</p>\n";
    
    $trustedSourcesSQL = "CREATE TABLE IF NOT EXISTS trusted_sources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        source_name VARCHAR(100) NOT NULL,
        source_url VARCHAR(500) NOT NULL,
        domain_name VARCHAR(100) NOT NULL,
        source_type ENUM('NEWS_MEDIA', 'GOVERNMENT', 'ACADEMIC', 'FACT_CHECK', 'OFFICIAL', 'SOCIAL_MEDIA', 'BLOG', 'UNKNOWN') DEFAULT 'NEWS_MEDIA',
        credibility_tier ENUM('TIER_1', 'TIER_2', 'TIER_3', 'TIER_4', 'TIER_5') DEFAULT 'TIER_3',
        trust_score DECIMAL(5,2) DEFAULT 50.00,
        reliability_score DECIMAL(5,2) DEFAULT 50.00,
        accuracy_score DECIMAL(5,2) DEFAULT 50.00,
        verified TINYINT(1) DEFAULT 0,
        verification_date TIMESTAMP NULL,
        country VARCHAR(2) DEFAULT 'US',
        language VARCHAR(5) DEFAULT 'en',
        active TINYINT(1) DEFAULT 1,
        blacklisted TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_domain_name (domain_name),
        INDEX idx_trust_score (trust_score),
        INDEX idx_active (active),
        INDEX idx_verified (verified)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (mysqli_query($conn, $trustedSourcesSQL)) {
        echo "<p style='color: green;'>✓ trusted_sources table created successfully</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error creating trusted_sources table: " . mysqli_error($conn) . "</p>\n";
    }
}

// Insert default content patterns
echo "<h3>Inserting default content patterns...</h3>\n";

$defaultPatterns = [
    [
        'pattern_name' => 'Clickbait Headlines',
        'pattern_type' => 'clickbait',
        'pattern_regex' => '/\b(you won\'t believe|shocking|unbelievable|incredible|amazing|stunning|mind-blowing|jaw-dropping)\b/i',
        'pattern_keywords' => 'clickbait, shocking, unbelievable, amazing, incredible',
        'confidence_weight' => 0.8
    ],
    [
        'pattern_name' => 'Sensational Language',
        'pattern_type' => 'sensationalism',
        'pattern_regex' => '/\b(disaster|catastrophe|devastating|horrifying|terrifying|nightmare|emergency|crisis)\b/i',
        'pattern_keywords' => 'disaster, catastrophe, devastating, horrifying, terrifying',
        'confidence_weight' => 0.7
    ],
    [
        'pattern_name' => 'Conspiracy Indicators',
        'pattern_type' => 'misinformation',
        'pattern_regex' => '/\b(conspiracy|cover-up|hidden truth|they don\'t want you to know|secret|exposed)\b/i',
        'pattern_keywords' => 'conspiracy, cover-up, hidden truth, secret, exposed',
        'confidence_weight' => 0.9
    ],
    [
        'pattern_name' => 'Opinion Markers',
        'pattern_type' => 'opinion',
        'pattern_regex' => '/\b(i think|in my opinion|believe|feel|seems like|perhaps|maybe)\b/i',
        'pattern_keywords' => 'opinion, think, believe, feel, seems, perhaps',
        'confidence_weight' => 0.6
    ],
    [
        'pattern_name' => 'Unverified Claims',
        'pattern_type' => 'misinformation',
        'pattern_regex' => '/\b(sources say|according to sources|reports claim|allegedly|rumor has it)\b/i',
        'pattern_keywords' => 'sources say, reports claim, allegedly, rumor, unverified',
        'confidence_weight' => 0.7
    ]
];

$insertedPatterns = 0;
foreach ($defaultPatterns as $pattern) {
    $checkExists = "SELECT id FROM content_patterns WHERE pattern_name = ?";
    $stmt = mysqli_prepare($conn, $checkExists);
    mysqli_stmt_bind_param($stmt, 's', $pattern['pattern_name']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $insertSQL = "INSERT INTO content_patterns (pattern_name, pattern_type, pattern_regex, pattern_keywords, confidence_weight) 
                    VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertSQL);
        mysqli_stmt_bind_param($stmt, 'sssds', 
            $pattern['pattern_name'], 
            $pattern['pattern_type'], 
            $pattern['pattern_regex'], 
            $pattern['pattern_keywords'], 
            $pattern['confidence_weight']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $insertedPatterns++;
        }
    }
}

echo "<p style='color: green;'>✓ Inserted $insertedPatterns default content patterns</p>\n";

// Insert default trusted sources
echo "<h3>Inserting default trusted sources...</h3>\n";

$defaultSources = [
    [
        'source_name' => 'BBC News',
        'source_url' => 'https://www.bbc.com/news',
        'domain_name' => 'bbc.com',
        'source_type' => 'NEWS_MEDIA',
        'credibility_tier' => 'TIER_1',
        'trust_score' => 90.00,
        'reliability_score' => 92.00,
        'accuracy_score' => 88.00,
        'verified' => 1,
        'country' => 'GB',
        'language' => 'en'
    ],
    [
        'source_name' => 'Reuters',
        'source_url' => 'https://www.reuters.com',
        'domain_name' => 'reuters.com',
        'source_type' => 'NEWS_MEDIA',
        'credibility_tier' => 'TIER_1',
        'trust_score' => 88.00,
        'reliability_score' => 90.00,
        'accuracy_score' => 85.00,
        'verified' => 1,
        'country' => 'US',
        'language' => 'en'
    ],
    [
        'source_name' => 'Associated Press',
        'source_url' => 'https://apnews.com',
        'domain_name' => 'apnews.com',
        'source_type' => 'NEWS_MEDIA',
        'credibility_tier' => 'TIER_1',
        'trust_score' => 87.00,
        'reliability_score' => 89.00,
        'accuracy_score' => 86.00,
        'verified' => 1,
        'country' => 'US',
        'language' => 'en'
    ],
    [
        'source_name' => 'Al Jazeera',
        'source_url' => 'https://www.aljazeera.com',
        'domain_name' => 'aljazeera.com',
        'source_type' => 'NEWS_MEDIA',
        'credibility_tier' => 'TIER_2',
        'trust_score' => 75.00,
        'reliability_score' => 78.00,
        'accuracy_score' => 72.00,
        'verified' => 1,
        'country' => 'QA',
        'language' => 'en'
    ],
    [
        'source_name' => 'CNN',
        'source_url' => 'https://www.cnn.com',
        'domain_name' => 'cnn.com',
        'source_type' => 'NEWS_MEDIA',
        'credibility_tier' => 'TIER_2',
        'trust_score' => 72.00,
        'reliability_score' => 75.00,
        'accuracy_score' => 70.00,
        'verified' => 1,
        'country' => 'US',
        'language' => 'en'
    ]
];

$insertedSources = 0;
foreach ($defaultSources as $source) {
    $checkExists = "SELECT id FROM trusted_sources WHERE domain_name = ?";
    $stmt = mysqli_prepare($conn, $checkExists);
    mysqli_stmt_bind_param($stmt, 's', $source['domain_name']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $insertSQL = "INSERT INTO trusted_sources (source_name, source_url, domain_name, source_type, credibility_tier, trust_score, reliability_score, accuracy_score, verified, country, language) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertSQL);
        mysqli_stmt_bind_param($stmt, 'sssssdddisss', 
            $source['source_name'], 
            $source['source_url'], 
            $source['domain_name'], 
            $source['source_type'], 
            $source['credibility_tier'], 
            $source['trust_score'], 
            $source['reliability_score'], 
            $source['accuracy_score'], 
            $source['verified'], 
            $source['country'], 
            $source['language']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $insertedSources++;
        }
    }
}

echo "<p style='color: green;'>✓ Inserted $insertedSources default trusted sources</p>\n";

// Test the AI Fake News Detector
echo "<h3>Testing AI Fake News Detector...</h3>\n";

try {
    require_once 'includes/ai_fake_news_detector.php';
    $detector = new AIFakeNewsDetector($conn);
    echo "<p style='color: green;'>✓ AI Fake News Detector initialized successfully</p>\n";
    
    // Test with a sample article
    $testResult = $detector->analyzeText("This is a test article about current events. It contains factual information and balanced reporting.");
    echo "<p style='color: green;'>✓ Text analysis working - Score: " . $testResult['overall_score'] . "%</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ AI Fake News Detector error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<h2>Database Setup Complete!</h2>\n";
echo "<p>The AI Fake News Detector should now work properly.</p>\n";
echo "<p><a href='news.php' target='_blank'>Test News Page</a> | <a href='index.php' target='_blank'>Go Home</a></p>\n";
?>
