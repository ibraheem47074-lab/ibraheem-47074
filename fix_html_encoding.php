<?php
/**
 * Fix HTML Encoding Issues for News Titles
 * This script will fix the &#039; encoding issue in news titles and content
 */

require_once 'config/database.php';

echo "<h2>HTML Encoding Fix Tool</h2>";

// Function to decode HTML entities back to proper characters
function fix_html_encoding($text) {
    if (empty($text)) {
        return $text;
    }
    
    // Decode HTML entities back to proper characters
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    // Clean up any double-encoded entities
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    return $text;
}

// Check if fix should be applied
if (isset($_POST['apply_fix'])) {
    
    // Fix news titles
    $news_query = "SELECT id, title, content FROM news WHERE title LIKE '%&#039;%' OR content LIKE '%&#039;%'";
    $result = mysqli_query($conn, $news_query);
    
    $fixed_count = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $title = fix_html_encoding($row['title']);
        $content = fix_html_encoding($row['content']);
        
        $update_query = "UPDATE news SET title = ?, content = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $fixed_count++;
            echo "<p style='color: green;'>✅ Fixed news ID: $id</p>";
        } else {
            echo "<p style='color: red;'>❌ Error fixing news ID: $id</p>";
        }
    }
    
    echo "<h3 style='color: blue;'>Total records fixed: $fixed_count</h3>";
    
    // Also fix any other tables that might have encoding issues
    $tables_to_check = ['categories', 'comments', 'editions'];
    
    foreach ($tables_to_check as $table) {
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($table_check) > 0) {
            // Get text columns from the table
            $columns_query = "SHOW COLUMNS FROM $table WHERE Type LIKE '%text%' OR Type LIKE '%varchar%'";
            $columns_result = mysqli_query($conn, $columns_query);
            
            $text_columns = [];
            while ($col = mysqli_fetch_assoc($columns_result)) {
                $text_columns[] = $col['Field'];
            }
            
            if (!empty($text_columns)) {
                $where_conditions = [];
                foreach ($text_columns as $col) {
                    $where_conditions[] = "$col LIKE '%&#039;%'";
                }
                
                $check_query = "SELECT * FROM $table WHERE " . implode(' OR ', $where_conditions);
                $check_result = mysqli_query($conn, $check_query);
                
                $table_fixed = 0;
                while ($row = mysqli_fetch_assoc($check_result)) {
                    $update_parts = [];
                    $update_values = [];
                    $types = '';
                    
                    foreach ($text_columns as $col) {
                        if (strpos($row[$col], '&#039;') !== false) {
                            $fixed_value = fix_html_encoding($row[$col]);
                            $update_parts[] = "$col = ?";
                            $update_values[] = $fixed_value;
                            $types .= 's';
                        }
                    }
                    
                    if (!empty($update_parts)) {
                        $update_values[] = $row['id'];
                        $types .= 'i';
                        
                        $update_query = "UPDATE $table SET " . implode(', ', $update_parts) . " WHERE id = ?";
                        $stmt = mysqli_prepare($conn, $update_query);
                        mysqli_stmt_bind_param($stmt, $types, ...$update_values);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            $table_fixed++;
                        }
                    }
                }
                
                if ($table_fixed > 0) {
                    echo "<p style='color: green;'>✅ Fixed $table_fixed records in $table table</p>";
                }
            }
        }
    }
    
    echo "<h3 style='color: blue; margin-top: 20px;'>✅ HTML Encoding Fix Complete!</h3>";
    echo "<p><a href='index.php'>← Back to Home</a></p>";
    
} else {
    // Show current encoding issues
    echo "<h3>Current Encoding Issues Found:</h3>";
    
    $news_query = "SELECT id, title FROM news WHERE title LIKE '%&#039;%' OR content LIKE '%&#039;%' LIMIT 10";
    $result = mysqli_query($conn, $news_query);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Current Title (with encoding issues)</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<br>";
        echo "<form method='post' style='text-align: center;'>";
        echo "<input type='hidden' name='apply_fix' value='1'>";
        echo "<input type='submit' value='🔧 Fix All HTML Encoding Issues' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
        echo "</form>";
        
        echo "<br><p style='color: orange;'><strong>⚠️ Warning:</strong> This will fix HTML encoding issues across all news articles and related tables.</p>";
        
    } else {
        echo "<p style='color: green;'>✅ No HTML encoding issues found!</p>";
    }
    
    echo "<hr>";
    
    // Show how to prevent future issues
    echo "<h3>🛠️ Prevention Tips:</h3>";
    echo "<ul>";
    echo "<li><strong>For Display:</strong> Use <code>htmlspecialchars(\$text, ENT_QUOTES, 'UTF-8')</code> when outputting to HTML</li>";
    echo "<li><strong>For Storage:</strong> Store raw text in database (don't encode before saving)</li>";
    echo "<li><strong>For Input:</strong> Use <code>mysqli_real_escape_string()</code> for SQL safety</li>";
    echo "<li><strong>Alternative:</strong> Use <code>htmlentities(\$text, ENT_QUOTES, 'UTF-8')</code> for full encoding</li>";
    echo "</ul>";
    
    echo "<h3>📝 Example Fix Code:</h3>";
    echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
    echo "// Instead of this (causes double encoding):";
    echo "echo htmlspecialchars(\$title);";
    echo "";
    echo "// Use this (proper display):";
    echo "echo htmlspecialchars(\$title, ENT_QUOTES, 'UTF-8');";
    echo "";
    echo "// Or if you need to fix existing data:";
    echo "\$fixed_title = html_entity_decode(\$title, ENT_QUOTES, 'UTF-8');";
    echo "echo htmlspecialchars(\$fixed_title, ENT_QUOTES, 'UTF-8');";
    echo "</pre>";
}

?>
