-- Create content_patterns table for AI fake news detection
CREATE TABLE IF NOT EXISTS `content_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern_name` varchar(255) NOT NULL COMMENT 'Name of the detection pattern',
  `pattern_type` enum('clickbait','sensationalism','misinformation','propaganda','satire','opinion','factual','unknown') DEFAULT 'unknown' COMMENT 'Type of content pattern',
  `pattern_regex` text DEFAULT NULL COMMENT 'Regular expression pattern for detection',
  `pattern_keywords` json DEFAULT NULL COMMENT 'Keywords associated with this pattern',
  `confidence_weight` decimal(3,2) DEFAULT '0.50' COMMENT 'Weight for confidence calculation (0.00-1.00)',
  `description` text DEFAULT NULL COMMENT 'Description of what this pattern detects',
  `severity_level` enum('low','medium','high','critical') DEFAULT 'medium' COMMENT 'Severity level of detected content',
  `category` varchar(100) DEFAULT NULL COMMENT 'Category of the pattern',
  `language` varchar(10) DEFAULT 'en' COMMENT 'Language the pattern applies to',
  `active` tinyint(1) DEFAULT '1' COMMENT 'Whether the pattern is active',
  `detection_count` int(11) DEFAULT '0' COMMENT 'Number of times this pattern has matched',
  `false_positive_count` int(11) DEFAULT '0' COMMENT 'Number of false positives reported',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_pattern_name` (`pattern_name`),
  KEY `idx_pattern_type` (`pattern_type`),
  KEY `idx_active` (`active`),
  KEY `idx_confidence_weight` (`confidence_weight`),
  KEY `idx_severity_level` (`severity_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default content patterns for fake news detection
INSERT IGNORE INTO `content_patterns` 
(`pattern_name`, `pattern_type`, `pattern_regex`, `pattern_keywords`, `confidence_weight`, `description`, `severity_level`, `category`, `language`, `active`) VALUES

-- Clickbait Patterns
('clickbait_numbers', 'clickbait', '\b\d+\s+(ways|reasons|things|facts|secrets|tips|tricks)\b.+?\b(you\s+(won\'t|will\s+not)|shocking|unbelievable|mind-blowing|amazing)\b', 
'["ways", "reasons", "things", "facts", "secrets", "tips", "tricks", "shocking", "unbelievable", "mind-blowing", "amazing"]', 
0.75, 'Detects clickbait headlines with numbered lists and sensational words', 'medium', 'Headline Analysis', 'en', 1),

('clickbait_urgency', 'clickbait', '\b(you\s+won\'t|will\s+not|never)\s+(believe|guess|imagine|expect)\b.+\b(happened|occurred|took\s+place)\b', 
'["won\'t believe", "will not believe", "never believe", "won\'t guess", "never guess", "won\'t imagine"]', 
0.80, 'Detects clickbait using urgency and disbelief phrases', 'medium', 'Headline Analysis', 'en', 1),

('clickbait_curiosity_gap', 'clickbait', '\b(this|the)\s+(one|secret|trick|reason|thing)\b.+\b(will\s+change|changes)\s+(your|the)\s+(life|world|mind)\b', 
'["this one", "the one", "this secret", "the secret", "this trick", "the trick"]', 
0.70, 'Detects clickbait creating curiosity gaps', 'medium', 'Headline Analysis', 'en', 1),

-- Sensationalism Patterns
('sensational_emotional', 'sensationalism', '\b(shocking|horrifying|terrifying|heartbreaking|devastating|outrageous|scandalous|disgusting)\b.+\b(revealed|exposed|uncovered|discovered)\b', 
'["shocking", "horrifying", "terrifying", "heartbreaking", "devastating", "outrageous", "scandalous", "disgusting", "revealed", "exposed", "uncovered"]', 
0.85, 'Detects sensational emotional language', 'high', 'Content Analysis', 'en', 1),

('sensational_exaggeration', 'sensationalism', '\b(literally|absolutely|totally|completely|entirely|utterly)\s+(shocking|unbelievable|incredible|amazing|stunning|mind-blowing)\b', 
'["literally shocking", "absolutely unbelievable", "totally incredible", "completely amazing", "entirely stunning", "utterly mind-blowing"]', 
0.75, 'Detects exaggerated sensational claims', 'medium', 'Content Analysis', 'en', 1),

('sensational_urgency', 'sensationalism', '\b(breaking|urgent|emergency|critical|immediate|instant)\s+(news|alert|report|update|announcement)\b', 
'["breaking news", "urgent alert", "emergency report", "critical update", "immediate announcement"]', 
0.65, 'Detects sensational urgency language', 'medium', 'Content Analysis', 'en', 1),

-- Misinformation Patterns
('misinformation_conspiracy', 'misinformation', '\b(they|the\s+government|the\s+media|big\s+pharma|the\s+elite)\s+(don\'t|do\s+not)\s+(want\s+you\s+to|want\s+us\s+to)\b', 
'["they don\'t want you to", "the government doesn\'t want", "the media won\'t tell", "big pharma hides", "the elite control"]', 
0.90, 'Detects conspiracy theory language patterns', 'high', 'Content Analysis', 'en', 1),

('misinformation_pseudoscience', 'misinformation', '\b(natural|herbal|ancient|traditional|alternative)\s+(cure|remedy|treatment|medicine|healing)\b.+\b(doctors|hospitals|big\s+pharma)\s+(hide|suppress|cover\s+up)\b', 
'["natural cure", "herbal remedy", "ancient treatment", "alternative medicine", "doctors hide", "big pharma suppress"]', 
0.85, 'Detects pseudoscientific medical claims', 'high', 'Health Content', 'en', 1),

('misinformation_false_authority', 'misinformation', '\b(scientists|experts|doctors|researchers)\s+(agree|confirm|prove|reveal|discover)\b.+\b(shocking|surprising|unexpected)\b', 
'["scientists agree", "experts confirm", "doctors prove", "researchers reveal", "shocking discovery"]', 
0.70, 'Detects false authority claims', 'medium', 'Content Analysis', 'en', 1),

-- Propaganda Patterns
('propaganda_us_vs_them', 'propaganda', '\b(we|our|us)\s+(vs|versus|against)\b.+\b(them|their|they)\b', 
'["we vs", "our vs", "us against", "we versus", "our versus"]', 
0.75, 'Detects us vs them propaganda language', 'high', 'Political Content', 'en', 1),

('propaganda_patriotic_appeal', 'propaganda', '\b(true|real|patriotic)\s+(americans|citizens|patriots)\b.+\b(stand|fight|defend|protect)\b', 
'["true americans", "real citizens", "patriotic americans", "stand up", "fight for", "defend our"]', 
0.80, 'Detects patriotic propaganda appeals', 'high', 'Political Content', 'en', 1),

('propaganda_fear_mongering', 'propaganda', '\b(dangerous|threatening|harmful|destructive|deadly)\s+(consequences|results|effects|outcomes)\b.+\b(if\s+we|unless\s+we)\b', 
'["dangerous consequences", "threatening results", "harmful effects", "deadly outcomes", "if we don\'t", "unless we"]', 
0.85, 'Detects fear-based propaganda', 'high', 'Political Content', 'en', 1),

-- Satire Detection Patterns
('satire_obvious', 'satire', '\b(satire|parody|spoof|humor|comedy|joke)\b', 
'["satire", "parody", "spoof", "humor", "comedy", "joke"]', 
0.60, 'Detects obvious satire indicators', 'low', 'Content Classification', 'en', 1),

('satire_exaggeration', 'satire', '\b(breaking:\s+local|world\s+leaders\s+shocked|scientists\s+baffled|experts\s+confused)\b', 
'["breaking: local", "world leaders shocked", "scientists baffled", "experts confused"]', 
0.70, 'Detects satirical exaggeration patterns', 'medium', 'Content Classification', 'en', 1),

-- Opinion vs Fact Patterns
('opinion_language', 'opinion', '\b(i\s+think|i\s+believe|in\s+my\s+opinion|it\s+seems|perhaps|maybe|possibly)\b', 
'["i think", "i believe", "in my opinion", "it seems", "perhaps", "maybe", "possibly"]', 
0.65, 'Detects opinion language vs factual statements', 'low', 'Content Classification', 'en', 1),

('factual_language', 'factual', '\b(according\s+to|research\s+shows|studies\s+indicate|data\s+reveals|statistics\s+show)\b', 
'["according to", "research shows", "studies indicate", "data reveals", "statistics show"]', 
0.50, 'Detects factual reporting language', 'low', 'Content Classification', 'en', 1),

-- Specific Fake News Patterns
('fake_news_miracle', 'misinformation', '\b(miracle|breakthrough|revolutionary|game-changing)\s+(cure|treatment|discovery|invention)\b', 
'["miracle cure", "breakthrough treatment", "revolutionary discovery", "game-changing invention"]', 
0.85, 'Detects miracle/breakthrough fake news claims', 'high', 'Health Content', 'en', 1),

('fake_news_celebrity_death', 'misinformation', '\b(shocking|tragic|unexpected)\s+(death|passing|demise)\b.+\b(at\s+age|aged)\s+\d+', 
'["shocking death", "tragic passing", "unexpected demise", "at age", "aged"]', 
0.80, 'Detects fake celebrity death hoaxes', 'high', 'Celebrity Content', 'en', 1),

('fake_news_political_scandal', 'misinformation', '\b(major|huge|massive)\s+(scandal|corruption|controversy)\b.+\b(exposed|revealed|leaked)\b', 
'["major scandal", "huge corruption", "massive controversy", "exposed", "revealed", "leaked"]', 
0.75, 'Detects fake political scandal claims', 'high', 'Political Content', 'en', 1);
