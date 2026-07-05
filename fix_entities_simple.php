<?php
/**
 * Simple Fix for HTML Entity Encodings
 * Removes &#039;, &quot;, and other HTML entities from published posts
 */

require_once 'config/database.php';

echo "<h1>Simple Fix - Remove HTML Entity Encodings</h1>";

// Simple cleaning function
function clean_html_entities($text) {
    if (empty($text)) {
        return $text;
    }
    
    // Multiple decode passes
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Specific replacements
    $replacements = [
        '&#039;' => "'",
        '&#x27;' => "'",
        '&apos;' => "'",
        '&#39;' => "'",
        '&amp;#039;' => "'",
        '&amp;#39;' => "'",
        '&quot;' => '"',
        '&amp;quot;' => '"',
        '&#34;' => '"',
        '&amp;#34;' => '"',
        '&#x22;' => '"',
        '&amp;#x22;' => '"',
        '&lt;' => '<',
        '&amp;lt;' => '<',
        '&gt;' => '>',
        '&amp;gt;' => '>',
        '&amp;' => '&',
        '&nbsp;' => ' ',
        '&amp;nbsp;' => ' ',
    ];
    
    foreach ($replacements as $encoded => $decoded) {
        $text = str_replace($encoded, $decoded, $text);
    }
    
    // Clean up remaining numeric entities
    $text = preg_replace('/&#(\d+);/', '', $text);
    $text = preg_replace('/&#x([0-9a-fA-F]+);/', '', $text);
    
    return $text;
}

// Apply fix if requested
if (isset($_POST['apply_fix'])) {
    echo "<h3>Applying Fix...</h3>";
    
    // Fix news table
    $news_query = "SELECT id, title, content, excerpt FROM news WHERE title LIKE '%&#%' OR content LIKE '%&#%' OR excerpt LIKE '%&%'";
    $result = mysqli_query($conn, $news_query);
    
    $fixed_count = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $title = clean_html_entities($row['title']);
        $content = clean_html_entities($row['content']);
        $excerpt = clean_html_entities($row['excerpt']);
        
        $update_query = "UPDATE news SET title = ?, content = ?, excerpt = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sssi", $title, $content, $excerpt, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $fixed_count++;
            echo "<p style='color: green;'>Fixed news ID: $id</p>";
        } else {
            echo "<p style='color: red;'>Error fixing news ID: $id</p>";
        }
    }
    
    echo "<h3 style='color: blue;'>Total news articles fixed: $fixed_count</h3>";
    echo "<p><a href='?'>Refresh to see results</a></p>";
}

// Show current issues
echo "<h2>Current HTML Entity Issues</h2>";

$news_query = "SELECT id, title FROM news WHERE title LIKE '%&#%' OR content LIKE '%&#%' LIMIT 10";
$result = mysqli_query($conn, $news_query);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Title (Sample)</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td style='background: #ffe6e6; max-width: 300px; word-wrap: break-word;'>" . 
             htmlspecialchars(substr($row['title'], 0, 100)) . "...</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: green;'>No HTML entity issues found!</p>";
}

// Show total count
$count_query = "SELECT COUNT(*) as count FROM news WHERE title LIKE '%&#%' OR content LIKE '%&%'";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
echo "<p><strong>Total articles with HTML entities: " . $count_row['count'] . "</strong></p>";

// Fix form
echo "<form method='post' style='margin-top: 30px; padding: 20px; background: #f0f8ff; border: 1px solid #0066cc;'>";
echo "<h3>Fix All HTML Entities</h3>";
echo "<p style='color: #0066cc;'><strong>This will remove all HTML entity encodings like &#039;, &quot;, etc.</strong></p>";
echo "<input type='submit' name='apply_fix' value='Fix Now' style='background: #0066cc; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>";
echo "</form>";

// Show examples
echo "<h3>Examples of What Will Be Fixed:</h3>";
echo "<div style='background: #f9f9f9; padding: 15px; border: 1px solid #ddd;'>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th>Problem</th><th>Solution</th></tr>";

$examples = [
    "It&#039;s amazing" => "It's amazing",
    "Don&#039;t worry" => "Don't worry", 
    "He said &quot;Hello&quot;" => 'He said "Hello"',
    "The &lt;title&gt; tag" => "The <title> tag",
    "More &amp; more" => "More & more",
];

foreach ($examples as $problem => $solution) {
    echo "<tr><td style='background: #ffe6e6; color: red; font-weight: bold;'>" . htmlspecialchars($problem) . "</td><td style='background: #e6ffe6; color: green; font-weight: bold;'>" . htmlspecialchars($solution) . "</td></tr>";
}

echo "</table>";
echo "</div>";

echo "<p style='margin-top: 20px;'><small>This tool specifically targets HTML entity encoding issues in published posts.</small></p>";
?>
