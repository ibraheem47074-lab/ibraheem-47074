<?php
/**
 * Clean Published Posts - Remove Unwanted HTML Entity Encodings
 * This script removes &#039;, &quot;, and other HTML entities from published posts
 */

require_once 'config/database.php';

echo "<h1>Clean Published Posts - Remove HTML Entity Encodings</h1>";
echo "<p>This tool will clean up unwanted HTML entity encodings like &#039;, &quot;, etc. from published posts.</p>";

// Comprehensive function to clean HTML entity encodings
function clean_html_entities($text) {
    if (empty($text)) {
        return $text;
    }
    
    // Multiple decode passes to handle double encoding
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Specific replacements for stubborn entities
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
    
    // Clean up any remaining numeric entities
    $text = preg_replace('/&#(\d+);/', '', $text);
    $text = preg_replace('/&#x([0-9a-fA-F]+);/', '', $text);
    
    return $text;
}

// Function to check if text contains HTML entities
function contains_html_entities($text) {
    if (empty($text)) {
        return false;
    }
    
    // Check for common HTML entity patterns
    $patterns = [
        '/&#\d+;/',           // Numeric entities like &#039;
        '/&#x[0-9a-fA-F]+;/', // Hexadecimal entities like &#x27;
        '/&[a-zA-Z]+;/',      // Named entities like &quot;
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text)) {
            return true;
        }
    }
    
    return false;
}

// Function to get all text columns from a table
function get_text_columns($conn, $table) {
    $columns_query = "SHOW COLUMNS FROM $table";
    $result = mysqli_query($conn, $columns_query);
    $text_columns = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $column_name = $row['Field'];
        $column_type = strtolower($row['Type']);
        
        // Check if it's a text-based column
        if (strpos($column_type, 'text') !== false || 
            strpos($column_type, 'varchar') !== false || 
            strpos($column_type, 'char') !== false) {
            $text_columns[] = $column_name;
        }
    }
    
    return $text_columns;
}

// Process cleaning if requested
if (isset($_POST['clean_posts'])) {
    $tables_to_clean = ['news', 'articles', 'pages'];
    $total_fixed = 0;
    
    foreach ($tables_to_clean as $table) {
        // Check if table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($table_check) == 0) {
            echo "<p style='color: orange;'>Table '$table' does not exist, skipping...</p>";
            continue;
        }
        
        echo "<h3>Cleaning table: $table</h3>";
        
        // Get text columns
        $text_columns = get_text_columns($conn, $table);
        
        if (empty($text_columns)) {
            echo "<p style='color: orange;'>No text columns found in table '$table'</p>";
            continue;
        }
        
        // Find rows with HTML entities
        $where_conditions = [];
        foreach ($text_columns as $col) {
            $where_conditions[] = "$col LIKE '%&#%' OR $col LIKE '&%'";
        }
        
        $check_query = "SELECT * FROM $table WHERE " . implode(' OR ', $where_conditions);
        $result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($result) == 0) {
            echo "<p style='color: green;'>No HTML entities found in table '$table'</p>";
            continue;
        }
        
        $table_fixed = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
            $update_parts = [];
            $update_values = [];
            $types = '';
            
            foreach ($text_columns as $col) {
                if (contains_html_entities($row[$col])) {
                    $cleaned_value = clean_html_entities($row[$col]);
                    $update_parts[] = "$col = ?";
                    $update_values[] = $cleaned_value;
                    $types .= 's';
                }
            }
            
            if (!empty($update_parts)) {
                $update_values[] = $id;
                $types .= 'i';
                
                $update_query = "UPDATE $table SET " . implode(', ', $update_parts) . " WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, $types, ...$update_values);
                
                if (mysqli_stmt_execute($stmt)) {
                    $table_fixed++;
                    $total_fixed++;
                    echo "<p style='color: green;'>Fixed $table ID: $id</p>";
                } else {
                    echo "<p style='color: red;'>Error fixing $table ID: $id - " . mysqli_error($conn) . "</p>";
                }
            }
        }
        
        echo "<p style='color: blue;'>Fixed $table_fixed records in table '$table'</p>";
    }
    
    echo "<h2 style='color: green;'>Total records fixed: $total_fixed</h2>";
    echo "<p><a href='?'>Refresh to see results</a></p>";
}

// Show current status
echo "<h2>Current HTML Entity Issues</h2>";

$tables_to_check = ['news', 'articles', 'pages'];
$total_issues = 0;

foreach ($tables_to_check as $table) {
    // Check if table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($table_check) == 0) {
        continue;
    }
    
    echo "<h3>Table: $table</h3>";
    
    // Get text columns
    $text_columns = get_text_columns($conn, $table);
    
    if (empty($text_columns)) {
        echo "<p style='color: orange;'>No text columns found</p>";
        continue;
    }
    
    // Find rows with HTML entities
    $where_conditions = [];
    foreach ($text_columns as $col) {
        $where_conditions[] = "$col LIKE '%&#%' OR $col LIKE '&%'";
    }
    
    $check_query = "SELECT id, " . implode(', ', $text_columns) . " FROM $table WHERE " . implode(' OR ', $where_conditions) . " LIMIT 10";
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) == 0) {
        echo "<p style='color: green;'>No HTML entities found</p>";
        continue;
    }
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th>";
    foreach ($text_columns as $col) {
        echo "<th>$col</th>";
    }
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        
        foreach ($text_columns as $col) {
            $value = $row[$col];
            if (contains_html_entities($value)) {
                echo "<td style='background: #ffe6e6; max-width: 300px; word-wrap: break-word;'>" . 
                     htmlspecialchars(substr($value, 0, 100)) . "...</td>";
                $total_issues++;
            } else {
                echo "<td style='max-width: 300px; word-wrap: break-word;'>" . 
                     htmlspecialchars(substr($value, 0, 100)) . "...</td>";
            }
        }
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Show total count for this table
    $count_query = "SELECT COUNT(*) as count FROM $table WHERE " . implode(' OR ', $where_conditions);
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    echo "<p><strong>Total records with HTML entities in $table: " . $count_row['count'] . "</strong></p>";
}

echo "<h2>Total issues found: $total_issues</h2>";

// Show cleaning form
echo "<form method='post' style='margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd;'>";
echo "<h3>Clean All HTML Entities</h3>";
echo "<p style='color: red;'><strong>Warning:</strong> This will permanently remove HTML entity encodings from all published posts. Make sure you have a backup!</p>";
echo "<input type='submit' name='clean_posts' value='Clean All Posts' style='background: #dc3545; color: white; padding: 10px 20px; border: none; cursor: pointer; font-size: 16px;' onclick='return confirm(\"Are you sure you want to clean all HTML entities? This action cannot be undone.\")'>";
echo "</form>";

// Show sample fixes
echo "<h3>Sample Fixes That Will Be Applied:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th>Before</th><th>After</th></tr>";

$sample_fixes = [
    "It&#039;s a beautiful day" => "It's a beautiful day",
    "He said &quot;Hello&quot;" => 'He said "Hello"',
    "Don&#039;t worry" => "Don't worry",
    "&amp;quot;test&quot;" => '"test"',
    "The &lt;title&gt; tag" => "The <title> tag",
];

foreach ($sample_fixes as $before => $after) {
    echo "<tr><td style='background: #ffe6e6;'>" . htmlspecialchars($before) . "</td><td style='background: #e6ffe6;'>" . htmlspecialchars($after) . "</td></tr>";
}

echo "</table>";

echo "<p><small>Note: This tool handles common HTML entity encodings including &#039; (apostrophe), &quot; (quotes), &lt; (less than), &gt; (greater than), and &amp; (ampersand).</small></p>";
?>
