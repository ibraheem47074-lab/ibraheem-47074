<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fix Website Content</h1>";

require_once 'config/database.php';

// Remove poor quality content
echo "<h2>Cleaning up poor content...</h2>";

// Remove articles with meaningless titles or content
$cleanup_queries = [
    "DELETE FROM news WHERE title LIKE 'bkuc' OR content LIKE 'bkuc%'",
    "DELETE FROM news WHERE LENGTH(title) < 5 OR LENGTH(content) < 50",
    "DELETE FROM news WHERE title = '' OR content = '' OR title IS NULL OR content IS NULL"
];

foreach ($cleanup_queries as $query) {
    $result = mysqli_query($conn, $query);
    if ($result) {
        $affected = mysqli_affected_rows($conn);
        echo "<p style='color: green;'>✓ Cleaned up $affected poor quality articles</p>";
    }
}

// Add some sample news content
echo "<h2>Adding sample news content...</h2>";

$sample_news = [
    [
        'title' => 'Breaking: Major Technology Breakthrough Announced',
        'content' => 'Scientists have announced a major breakthrough in quantum computing that could revolutionize how we process information. The new technology promises to solve complex problems in minutes that would currently take years to compute. Researchers at leading universities worldwide are hailing this as a significant step forward in computational science.',
        'category_id' => 1,
        'status' => 'published',
        'is_breaking' => 1
    ],
    [
        'title' => 'Global Markets Rally on Economic Recovery Signs',
        'content' => 'Stock markets around the world showed strong gains today as investors reacted positively to signs of economic recovery. Major indices in Asia, Europe, and North America all posted significant increases, with technology and energy sectors leading the rally. Economic analysts suggest this trend may continue as consumer confidence improves.',
        'category_id' => 2,
        'status' => 'published',
        'is_breaking' => 0
    ],
    [
        'title' => 'New Health Study Reveals Surprising Benefits of Mediterranean Diet',
        'content' => 'A comprehensive new study published in a leading medical journal has revealed additional health benefits of the Mediterranean diet. Researchers found that following this dietary pattern not only reduces heart disease risk but may also improve cognitive function and longevity. The study followed over 10,000 participants for more than a decade.',
        'category_id' => 3,
        'status' => 'published',
        'is_breaking' => 0
    ],
    [
        'title' => 'Climate Summit Reaches Historic Agreement on Emissions',
        'content' => 'World leaders have reached a historic agreement at the global climate summit, committing to unprecedented reductions in carbon emissions. The deal includes binding targets for developed nations and significant financial support for developing countries to transition to renewable energy sources. Environmental groups are calling this a crucial step in addressing climate change.',
        'category_id' => 4,
        'status' => 'published',
        'is_breaking' => 1
    ],
    [
        'title' => 'Sports Championship Ends in Thrilling Overtime Victory',
        'content' => 'In what fans are calling one of the most exciting championships in recent memory, the home team secured a stunning victory in overtime. The final moments of the game had spectators on the edge of their seats as the score remained tied until the last possible second. This victory marks the team\'s first championship win in over a decade.',
        'category_id' => 5,
        'status' => 'published',
        'is_breaking' => 0
    ]
];

foreach ($sample_news as $news) {
    $slug = create_slug($news['title']);
    $query = "INSERT INTO news (title, slug, content, category_id, status, is_breaking, created_at, published_at, views) 
              VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'sssiiii', 
        $news['title'], 
        $slug, 
        $news['content'], 
        $news['category_id'], 
        $news['status'], 
        $news['is_breaking'],
        rand(100, 1000)
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✓ Added: " . htmlspecialchars($news['title']) . "</p>";
    }
}

// Update some articles to be featured
echo "<h2>Setting featured articles...</h2>";
mysqli_query($conn, "UPDATE news SET status = 'featured' WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
echo "<p style='color: green;'>✓ Updated featured articles</p>";

// Show current status
echo "<h2>Current Database Status:</h2>";
$total_query = "SELECT COUNT(*) as total FROM news";
$total_result = mysqli_query($conn, $total_query);
$total = mysqli_fetch_assoc($total_result)['total'];

$published_query = "SELECT COUNT(*) as total FROM news WHERE status = 'published'";
$published_result = mysqli_query($conn, $published_query);
$published = mysqli_fetch_assoc($published_result)['total'];

echo "<p>Total articles: $total</p>";
echo "<p>Published articles: $published</p>";

echo "<hr>";
echo "<p style='color: green; font-weight: bold;'>✓ Content fix complete!</p>";
echo "<p><a href='index.php'>View your improved website</a></p>";
echo "<p><a href='simple_test.php'>Test again</a></p>";
?>
