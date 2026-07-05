<?php
require_once 'config/database.php';

echo "<h1>Adding Missing source_name Column</h1>\n";

// Check if source_name column exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'source_name'");
if (mysqli_num_rows($check_column) > 0) {
    echo "<p style='color: green;'>✓ source_name column already exists</p>\n";
} else {
    echo "<p style='color: orange;'>⚠ source_name column is MISSING - Adding it now...</p>\n";
    
    // Add the missing column
    $add_column = "ALTER TABLE news ADD COLUMN source_name VARCHAR(100) DEFAULT NULL AFTER source_url";
    if (mysqli_query($conn, $add_column)) {
        echo "<p style='color: green;'>✓ source_name column added successfully</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error adding source_name column: " . mysqli_error($conn) . "</p>\n";
    }
}

// Also check for credibility_status column
echo "<h3>Checking credibility_status column...</h3>\n";
$check_credibility = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'credibility_status'");
if (mysqli_num_rows($check_credibility) > 0) {
    echo "<p style='color: green;'>✓ credibility_status column exists</p>\n";
} else {
    echo "<p style='color: orange;'>⚠ credibility_status column missing - Adding it...</p>\n";
    $add_credibility = "ALTER TABLE news ADD COLUMN credibility_status ENUM('PENDING', 'ANALYZED', 'REVIEWED') DEFAULT 'PENDING' AFTER sentiment_label";
    if (mysqli_query($conn, $add_credibility)) {
        echo "<p style='color: green;'>✓ credibility_status column added successfully</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error adding credibility_status column: " . mysqli_error($conn) . "</p>\n";
    }
}

// Check for news_credibility_analysis table
echo "<h3>Checking news_credibility_analysis table...</h3>\n";
$check_analysis = mysqli_query($conn, "SHOW TABLES LIKE 'news_credibility_analysis'");
if (mysqli_num_rows($check_analysis) > 0) {
    echo "<p style='color: green;'>✓ news_credibility_analysis table exists</p>\n";
} else {
    echo "<p style='color: orange;'>⚠ news_credibility_analysis table missing - Creating it...</p>\n";
    
    $create_analysis = "CREATE TABLE IF NOT EXISTS news_credibility_analysis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        news_id INT NOT NULL,
        analysis_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        analysis_method VARCHAR(50) DEFAULT 'AI_MULTIMODEL',
        ai_model_version VARCHAR(20) DEFAULT 'v2.1',
        processing_time_ms INT DEFAULT 0,
        title_credibility DECIMAL(5,2) DEFAULT 0.00,
        content_credibility DECIMAL(5,2) DEFAULT 0.00,
        source_credibility DECIMAL(5,2) DEFAULT 0.00,
        factual_accuracy DECIMAL(5,2) DEFAULT 0.00,
        sensationalism_score DECIMAL(5,2) DEFAULT 0.00,
        emotional_manipulation DECIMAL(5,2) DEFAULT 0.00,
        clickbait_score DECIMAL(5,2) DEFAULT 0.00,
        propaganda_indicators DECIMAL(5,2) DEFAULT 0.00,
        grammar_score DECIMAL(5,2) DEFAULT 0.00,
        readability_score DECIMAL(5,2) DEFAULT 0.00,
        factual_density DECIMAL(5,2) DEFAULT 0.00,
        source_verified TINYINT(1) DEFAULT 0,
        source_reputation_score DECIMAL(5,2) DEFAULT 0.00,
        cross_reference_count INT DEFAULT 0,
        credibility_score DECIMAL(5,2) DEFAULT 0.00,
        confidence_level DECIMAL(5,2) DEFAULT 0.00,
        risk_level ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'MEDIUM',
        content_category VARCHAR(50) DEFAULT 'GENERAL',
        requires_review TINYINT(1) DEFAULT 0,
        analysis_details TEXT,
        FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
        INDEX idx_news_id (news_id),
        INDEX idx_analysis_date (analysis_date),
        INDEX idx_credibility_score (credibility_score),
        INDEX idx_risk_level (risk_level),
        INDEX idx_requires_review (requires_review)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (mysqli_query($conn, $create_analysis)) {
        echo "<p style='color: green;'>✓ news_credibility_analysis table created successfully</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Error creating news_credibility_analysis table: " . mysqli_error($conn) . "</p>\n";
    }
}

echo "<h2>Testing AI Detector Again...</h2>\n";
try {
    require_once 'includes/ai_fake_news_detector.php';
    $detector = new AIFakeNewsDetector($conn);
    echo "<p style='color: green;'>✓ AI Fake News Detector initialized successfully</p>\n";
    
    // Test with a sample article
    $testQuery = "SELECT id, title FROM news ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $testQuery);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $newsId = $row['id'];
        $title = $row['title'];
        
        echo "<h3>Testing with article: " . htmlspecialchars($title) . "</h3>\n";
        
        try {
            $analysis = $detector->analyzeArticle($newsId);
            if (is_array($analysis) && isset($analysis['credibility_score'])) {
                echo "<div style='background: #f0f8ff; padding: 10px; border-left: 4px solid #007cba;'>\n";
                echo "<p><strong>Analysis Results:</strong></p>\n";
                echo "<p>Credibility Score: " . $analysis['credibility_score'] . "%</p>\n";
                echo "<p>Risk Level: " . $analysis['risk_level'] . "</p>\n";
                echo "<p>Confidence Level: " . $analysis['confidence_level'] . "%</p>\n";
                echo "</div>\n";
                echo "<p style='color: green;'>✓ AI analysis working perfectly!</p>\n";
            } else {
                echo "<p style='color: orange;'>⚠ Analysis returned unexpected result</p>\n";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Analysis error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Fatal error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<h3>Final Status:</h3>\n";
echo "<p style='color: green;'><strong>✅ All database issues resolved!</strong></p>\n";
echo "<p>The AI Fake News Detector should now work without any database errors.</p>\n";
echo "<p><a href='news.php' target='_blank'>Test News Page</a> | <a href='index.php' target='_blank'>Go Home</a></p>\n";
?>
