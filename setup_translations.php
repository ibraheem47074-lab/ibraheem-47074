<?php
/**
 * Translation System Setup Script
 * Run this file to create the translations table and populate it with initial data
 */

require_once 'config/database.php';

echo "<h2>Translation System Setup</h2>";
echo "<p>Setting up translations table and initial data...</p>";

$success_count = 0;
$error_count = 0;

// Create translations table
$sql = "CREATE TABLE IF NOT EXISTS `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translation_key` varchar(255) NOT NULL COMMENT 'Unique key for the text to translate',
  `language_code` varchar(10) NOT NULL COMMENT 'Language code (en, ur, hi, etc.)',
  `translated_text` text NOT NULL COMMENT 'The translated text',
  `context` varchar(255) DEFAULT NULL COMMENT 'Optional context for translators',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_translation` (`translation_key`, `language_code`),
  KEY `translation_key` (`translation_key`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    $success_count++;
    echo "<p style='color: green;'>✓ Translations table created successfully</p>";
} else {
    $error_count++;
    $error = mysqli_error($conn);
    if (strpos($error, 'already exists') === false) {
        echo "<p style='color: red;'>✗ Error creating table: $error</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Table already exists (skipped)</p>";
        $success_count++;
    }
}

// Insert English translations
$english_translations = [
    "('home', 'en', 'Home', 'Navigation')",
    "('latest_news', 'en', 'Latest News', 'Section title')",
    "('trending_news', 'en', 'Trending News', 'Section title')",
    "('categories', 'en', 'Categories', 'Section title')",
    "('breaking_news', 'en', 'Breaking News', 'Section title')",
    "('featured_news', 'en', 'Featured News', 'Section title')",
    "('read_more', 'en', 'Read More', 'Button text')",
    "('views', 'en', 'Views', 'Counter label')",
    "('comments', 'en', 'Comments', 'Counter label')",
    "('likes', 'en', 'Likes', 'Counter label')",
    "('share', 'en', 'Share', 'Button text')",
    "('published', 'en', 'Published', 'Status label')",
    "('author', 'en', 'Author', 'Label')",
    "('date', 'en', 'Date', 'Label')",
    "('category', 'en', 'Category', 'Label')",
    "('search', 'en', 'Search', 'Search placeholder')",
    "('search_placeholder', 'en', 'Search news...', 'Search input placeholder')",
    "('login', 'en', 'Login', 'Button text')",
    "('register', 'en', 'Register', 'Button text')",
    "('logout', 'en', 'Logout', 'Button text')",
    "('dashboard', 'en', 'Dashboard', 'Navigation')",
    "('settings', 'en', 'Settings', 'Navigation')",
    "('profile', 'en', 'Profile', 'Navigation')",
    "('admin_panel', 'en', 'Admin Panel', 'Navigation')",
    "('welcome_back', 'en', 'Welcome back', 'Greeting')",
    "('all_latest_news', 'en', 'All Latest News', 'Section title')",
    "('refresh_now', 'en', 'Refresh Now', 'Button text')",
    "('news_statistics', 'en', 'Loading news statistics...', 'Loading text')",
    "('last_updated', 'en', 'Last updated', 'Label')",
    "('just_now', 'en', 'Just now', 'Time ago')",
    "('minutes_ago', 'en', 'minutes ago', 'Time ago')",
    "('hours_ago', 'en', 'hours ago', 'Time ago')",
    "('days_ago', 'en', 'days ago', 'Time ago')",
    "('yesterday', 'en', 'Yesterday', 'Date')",
    "('today', 'en', 'Today', 'Date')",
    "('no_news_found', 'en', 'No news found', 'Empty state')",
    "('load_more', 'en', 'Load More', 'Button text')",
    "('submit', 'en', 'Submit', 'Button text')",
    "('cancel', 'en', 'Cancel', 'Button text')",
    "('save', 'en', 'Save', 'Button text')",
    "('edit', 'en', 'Edit', 'Button text')",
    "('delete', 'en', 'Delete', 'Button text')",
    "('confirm', 'en', 'Confirm', 'Button text')",
    "('back', 'en', 'Back', 'Navigation')",
    "('next', 'en', 'Next', 'Navigation')",
    "('previous', 'en', 'Previous', 'Navigation')",
    "('page', 'en', 'Page', 'Pagination')",
    "('of', 'en', 'of', 'Pagination')",
    "('results', 'en', 'results', 'Search results')",
    "('filter_by', 'en', 'Filter by', 'Filter label')",
    "('sort_by', 'en', 'Sort by', 'Sort label')",
    "('newest_first', 'en', 'Newest First', 'Sort option')",
    "('oldest_first', 'en', 'Oldest First', 'Sort option')",
    "('most_viewed', 'en', 'Most Viewed', 'Sort option')",
    "('most_liked', 'en', 'Most Liked', 'Sort option')",
    "('language', 'en', 'Language', 'Label')",
    "('select_language', 'en', 'Select Language', 'Dropdown label')",
    "('change_language', 'en', 'Change Language', 'Button text')",
    "('weather', 'en', 'Weather', 'Section title')",
    "('temperature', 'en', 'Temperature', 'Label')",
    "('humidity', 'en', 'Humidity', 'Label')",
    "('wind', 'en', 'Wind', 'Label')",
    "('events', 'en', 'Events', 'Section title')",
    "('upcoming_events', 'en', 'Upcoming Events', 'Section title')",
    "('view_all', 'en', 'View All', 'Button text')",
    "('live_stream', 'en', 'Live Stream', 'Section title')",
    "('watch_now', 'en', 'Watch Now', 'Button text')",
    "('poll', 'en', 'Poll', 'Section title')",
    "('vote', 'en', 'Vote', 'Button text')",
    "('total_votes', 'en', 'Total Votes', 'Label')",
    "('newsletter', 'en', 'Newsletter', 'Section title')",
    "('subscribe', 'en', 'Subscribe', 'Button text')",
    "('email_address', 'en', 'Email Address', 'Label')",
    "('follow_us', 'en', 'Follow Us', 'Social media')",
    "('contact_us', 'en', 'Contact Us', 'Footer')",
    "('about_us', 'en', 'About Us', 'Footer')",
    "('privacy_policy', 'en', 'Privacy Policy', 'Footer')",
    "('terms_of_service', 'en', 'Terms of Service', 'Footer')",
    "('copyright', 'en', 'Copyright', 'Footer')",
    "('all_rights_reserved', 'en', 'All Rights Reserved', 'Footer')",
    "('new', 'en', 'NEW', 'Badge')",
    "('recent', 'en', 'Recent', 'Badge')",
    "('breaking', 'en', 'BREAKING', 'Badge')",
    "('like', 'en', 'Like', 'Button')",
    "('comment', 'en', 'Comment', 'Button')",
    "('person_likes_this', 'en', 'person likes this', 'Social')",
    "('people_like_this', 'en', 'people like this', 'Social')",
    "('loading', 'en', 'Loading...', 'Status')",
    "('loading_comments', 'en', 'Loading comments...', 'Status')",
    "('write_a_comment', 'en', 'Write a comment...', 'Placeholder')",
    "('post', 'en', 'Post', 'Button')",
    "('to_like_or_comment', 'en', 'to like or comment', 'Action')",
    "('news_editions', 'en', 'News Editions', 'Section title')",
    "('view_all_editions', 'en', 'View All Editions', 'Button')"
];

$sql = "INSERT INTO `translations` (`translation_key`, `language_code`, `translated_text`, `context`) VALUES " . implode(',', $english_translations) . " ON DUPLICATE KEY UPDATE translated_text = VALUES(translated_text)";

if (mysqli_query($conn, $sql)) {
    $success_count++;
    echo "<p style='color: green;'>✓ English translations inserted successfully</p>";
} else {
    $error_count++;
    echo "<p style='color: red;'>✗ Error inserting English translations: " . mysqli_error($conn) . "</p>";
}

// Insert Urdu translations
$urdu_translations = [
    "('home', 'ur', 'ہوم', 'Navigation')",
    "('latest_news', 'ur', 'تازہ ترین خبریں', 'Section title')",
    "('trending_news', 'ur', 'رجحان خبریں', 'Section title')",
    "('categories', 'ur', 'زمرے', 'Section title')",
    "('breaking_news', 'ur', 'بریکنگ نیوز', 'Section title')",
    "('featured_news', 'ur', 'خصوصی خبریں', 'Section title')",
    "('read_more', 'ur', 'مزید پڑھیں', 'Button text')",
    "('views', 'ur', 'مناظر', 'Counter label')",
    "('comments', 'ur', 'تبصرے', 'Counter label')",
    "('likes', 'ur', 'پسند', 'Counter label')",
    "('share', 'ur', 'شیئر کریں', 'Button text')",
    "('published', 'ur', 'شائع شدہ', 'Status label')",
    "('author', 'ur', 'مصنف', 'Label')",
    "('date', 'ur', 'تاریخ', 'Label')",
    "('category', 'ur', 'زمرہ', 'Label')",
    "('search', 'ur', 'تلاش', 'Search placeholder')",
    "('search_placeholder', 'ur', 'خبریں تلاش کریں...', 'Search input placeholder')",
    "('login', 'ur', 'لاگ ان', 'Button text')",
    "('register', 'ur', 'رجسٹر', 'Button text')",
    "('logout', 'ur', 'لاگ آؤٹ', 'Button text')",
    "('dashboard', 'ur', 'ڈیش بورڈ', 'Navigation')",
    "('settings', 'ur', 'ترتیبات', 'Navigation')",
    "('profile', 'ur', 'پروفائل', 'Navigation')",
    "('admin_panel', 'ur', 'ایڈمن پینل', 'Navigation')",
    "('welcome_back', 'ur', 'خوش آمدید', 'Greeting')",
    "('all_latest_news', 'ur', 'تمام تازہ ترین خبریں', 'Section title')",
    "('refresh_now', 'ur', 'ابھی ریفریش کریں', 'Button text')",
    "('news_statistics', 'ur', 'خبریں لوڈ ہو رہی ہیں...', 'Loading text')",
    "('last_updated', 'ur', 'آخری اپ ڈیٹ', 'Label')",
    "('just_now', 'ur', 'ابھی', 'Time ago')",
    "('minutes_ago', 'ur', 'منٹ پہلے', 'Time ago')",
    "('hours_ago', 'ur', 'گھنٹے پہلے', 'Time ago')",
    "('days_ago', 'ur', 'دن پہلے', 'Time ago')",
    "('yesterday', 'ur', 'کل', 'Date')",
    "('today', 'ur', 'آج', 'Date')",
    "('no_news_found', 'ur', 'کوئی خبر نہیں ملی', 'Empty state')",
    "('load_more', 'ur', 'مزید لوڈ کریں', 'Button text')",
    "('submit', 'ur', 'جمع کروائیں', 'Button text')",
    "('cancel', 'ur', 'منسوخ کریں', 'Button text')",
    "('save', 'ur', 'محفوظ کریں', 'Button text')",
    "('edit', 'ur', 'ترمیم', 'Button text')",
    "('delete', 'ur', 'حذف کریں', 'Button text')",
    "('confirm', 'ur', 'تصدیق', 'Button text')",
    "('back', 'ur', 'واپس', 'Navigation')",
    "('next', 'ur', 'اگلا', 'Navigation')",
    "('previous', 'ur', 'پچھلا', 'Navigation')",
    "('page', 'ur', 'صفحہ', 'Pagination')",
    "('of', 'ur', 'کا', 'Pagination')",
    "('results', 'ur', 'نتائج', 'Search results')",
    "('filter_by', 'ur', 'فلٹر کریں', 'Filter label')",
    "('sort_by', 'ur', 'ترتیب دیں', 'Sort label')",
    "('newest_first', 'ur', 'نئے سب سے پہلے', 'Sort option')",
    "('oldest_first', 'ur', 'پرانے سب سے پہلے', 'Sort option')",
    "('most_viewed', 'ur', 'سب سے زیادہ دیکھا گیا', 'Sort option')",
    "('most_liked', 'ur', 'سب سے زیادہ پسند کیا گیا', 'Sort option')",
    "('language', 'ur', 'زبان', 'Label')",
    "('select_language', 'ur', 'زبان منتخب کریں', 'Dropdown label')",
    "('change_language', 'ur', 'زبان تبدیل کریں', 'Button text')",
    "('weather', 'ur', 'موسم', 'Section title')",
    "('temperature', 'ur', 'درجہ حرارت', 'Label')",
    "('humidity', 'ur', 'نمی', 'Label')",
    "('wind', 'ur', 'ہوا', 'Label')",
    "('events', 'ur', 'تقریبات', 'Section title')",
    "('upcoming_events', 'ur', 'آنے والی تقریبات', 'Section title')",
    "('view_all', 'ur', 'سب دیکھیں', 'Button text')",
    "('live_stream', 'ur', 'لائیو اسٹریم', 'Section title')",
    "('watch_now', 'ur', 'ابھی دیکھیں', 'Button text')",
    "('poll', 'ur', 'پول', 'Section title')",
    "('vote', 'ur', 'ووٹ', 'Button text')",
    "('total_votes', 'ur', 'کل ووٹ', 'Label')",
    "('newsletter', 'ur', 'نیوز لیٹر', 'Section title')",
    "('subscribe', 'ur', 'سبسکرائب کریں', 'Button text')",
    "('email_address', 'ur', 'ای میل ایڈریس', 'Label')",
    "('follow_us', 'ur', 'ہمارے ساتھ جڑیں', 'Social media')",
    "('contact_us', 'ur', 'ہم سے رابطہ کریں', 'Footer')",
    "('about_us', 'ur', 'ہمارے بارے میں', 'Footer')",
    "('privacy_policy', 'ur', 'پرائیویسی پالیسی', 'Footer')",
    "('terms_of_service', 'ur', 'سروس کی شرائط', 'Footer')",
    "('copyright', 'ur', 'کاپی رائٹ', 'Footer')",
    "('all_rights_reserved', 'ur', 'تمام حقوق محفوظ ہیں', 'Footer')",
    "('new', 'ur', 'نیا', 'Badge')",
    "('recent', 'ur', 'حالیہ', 'Badge')",
    "('breaking', 'ur', 'بریکنگ', 'Badge')",
    "('like', 'ur', 'پسند', 'Button')",
    "('comment', 'ur', 'تبصرہ', 'Button')",
    "('person_likes_this', 'ur', 'شخص کو پسند ہے', 'Social')",
    "('people_like_this', 'ur', 'لوگوں کو پسند ہے', 'Social')",
    "('loading', 'ur', 'لوڈ ہو رہا ہے...', 'Status')",
    "('loading_comments', 'ur', 'تبصرے لوڈ ہو رہے ہیں...', 'Status')",
    "('write_a_comment', 'ur', 'تبصرہ لکھیں...', 'Placeholder')",
    "('post', 'ur', 'پوسٹ', 'Button')",
    "('to_like_or_comment', 'ur', 'پسند کرنے یا تبصرہ کرنے کے لیے', 'Action')",
    "('news_editions', 'ur', 'خبروں کے ایڈیشن', 'Section title')",
    "('view_all_editions', 'ur', 'تمام ایڈیشن دیکھیں', 'Button')"
];

$sql = "INSERT INTO `translations` (`translation_key`, `language_code`, `translated_text`, `context`) VALUES " . implode(',', $urdu_translations) . " ON DUPLICATE KEY UPDATE translated_text = VALUES(translated_text)";

if (mysqli_query($conn, $sql)) {
    $success_count++;
    echo "<p style='color: green;'>✓ Urdu translations inserted successfully</p>";
} else {
    $error_count++;
    echo "<p style='color: red;'>✗ Error inserting Urdu translations: " . mysqli_error($conn) . "</p>";
}

echo "<h3>Setup Complete!</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

echo "<p><a href='index.php'>Go to Homepage</a></p>";
echo "<p><a href='index.php?lang=ur'>Test Urdu Translation</a></p>";
?>
