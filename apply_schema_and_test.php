<?php
/**
 * Apply Comment Schema Updates and Run Tests
 * This script applies the SQL schema updates and runs comprehensive tests
 */

require_once 'config/database.php';

echo "<h1>Apply Schema Updates & Run Comprehensive Tests</h1>";

// Function to execute SQL from file
function execute_sql_file($conn, $file_path) {
    if (!file_exists($file_path)) {
        return ['success' => false, 'message' => 'SQL file not found: ' . $file_path];
    }
    
    $sql = file_get_contents($file_path);
    if (!$sql) {
        return ['success' => false, 'message' => 'Failed to read SQL file'];
    }
    
    // Split SQL into individual statements
    $statements = explode(';', $sql);
    $executed = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }
        
        // Skip comments and non-SQL statements
        if (preg_match('/^(--|\/\*|CREATE|DROP|ALTER|INSERT|DELIMITER)/', $statement)) {
            try {
                if (mysqli_multi_query($conn, $statement)) {
                    do {
                        if ($result = mysqli_store_result($conn)) {
                            mysqli_free_result($result);
                        }
                    } while (mysqli_next_result($conn));
                    $executed++;
                } else {
                    $errors[] = mysqli_error($conn);
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
    
    return [
        'success' => empty($errors),
        'message' => "Executed $executed statements",
        'errors' => $errors
    ];
}

// Apply schema updates if requested
if (isset($_POST['apply_schema'])) {
    echo "<h2>Applying Schema Updates</h2>";
    
    $schema_file = __DIR__ . '/update_comments_schema.sql';
    $result = execute_sql_file($conn, $schema_file);
    
    if ($result['success']) {
        echo "<p style='color: green;'>â Schema updates applied successfully!</p>";
        echo "<p>" . $result['message'] . "</p>";
    } else {
        echo "<p style='color: red;'>â Schema updates failed!</p>";
        echo "<p>" . $result['message'] . "</p>";
        
        if (!empty($result['errors'])) {
            echo "<h4>Errors:</h4>";
            echo "<ul>";
            foreach ($result['errors'] as $error) {
                echo "<li style='color: red;'>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        }
    }
    
    echo "<hr>";
}

// Check current schema status
echo "<h2>Current Schema Status</h2>";

$tables_to_check = ['comments', 'comment_likes', 'comment_reports'];
foreach ($tables_to_check as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    $exists = mysqli_num_rows($check) > 0;
    
    echo "<p><strong>$table:</strong> ";
    if ($exists) {
        echo "<span style='color: green;'>â Exists</span>";
        
        // Get table info
        $info_query = "SELECT COUNT(*) as row_count FROM $table";
        $info_result = mysqli_query($conn, $info_query);
        $row_count = mysqli_fetch_assoc($info_result)['row_count'];
        echo " ($row_count rows)";
    } else {
        echo "<span style='color: red;'>â Missing</span>";
    }
    echo "</p>";
}

// Check for stored procedures
$procedure_check = mysqli_query($conn, "SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'GetCommentStats'");
$procedure_exists = mysqli_num_rows($procedure_check) > 0;
echo "<p><strong>GetCommentStats Procedure:</strong> " . ($procedure_exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";

// Check for views
$view_check = mysqli_query($conn, "SHOW TABLES LIKE 'approved_comments_view'");
$view_exists = mysqli_num_rows($view_check) > 0;
echo "<p><strong>approved_comments_view:</strong> " . ($view_exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";

// Run comprehensive tests if requested
if (isset($_POST['run_tests'])) {
    echo "<h2>Running Comprehensive Tests</h2>";
    
    // Include the test suite
    include_once 'test_comments_comprehensive.php';
}

// Action buttons
echo "<div style='margin-top: 30px;'>";
echo "<form method='post' style='display: inline-block; margin-right: 10px;'>";
echo "<input type='submit' name='apply_schema' value='Apply Schema Updates' style='background: #dc3545; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;' onclick='return confirm(\"This will drop and recreate the comments tables. Are you sure?\")'>";
echo "</form>";

echo "<form method='post' style='display: inline-block; margin-right: 10px;'>";
echo "<input type='submit' name='run_tests' value='Run Comprehensive Tests' style='background: #007bff; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>";
echo "</form>";

echo "<a href='test_comments_comprehensive.php' style='display: inline-block; margin-right: 10px;'>";
echo "<button style='background: #28a745; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>View Test Suite</button>";
echo "</a>";

echo "<a href='fix_comment_system.php' style='display: inline-block;'>";
echo "<button style='background: #ffc107; color: black; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; font-weight: bold;'>System Diagnostics</button>";
echo "</a>";
echo "</div>";

// Schema information
echo "<h2>Schema Information</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
echo "<h3>What the Schema Update Includes:</h3>";
echo "<ul>";
echo "<li><strong>Optimized comments table:</strong> Full structure with all necessary columns</li>";
echo "<li><strong>comment_likes table:</strong> For like/dislike functionality</li>";
echo "<li><strong>comment_reports table:</strong> For comment moderation and reporting</li>";
echo "<li><strong>Foreign key constraints:</strong> Data integrity between tables</li>";
echo "<li><strong>Indexes:</strong> Optimized for performance</li>";
echo "<li><strong>Stored procedures:</strong> GetCommentStats for statistics</li>";
echo "<li><strong>Views:</strong> approved_comments_view for easy access to approved comments</li>";
echo "<li><strong>Triggers:</strong> Automatic like count updates</li>";
echo "</ul>";
echo "</div>";

// Testing information
echo "<h2>Testing Information</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; border: 1px solid #2196f3; border-radius: 5px;'>";
echo "<h3>What the Comprehensive Tests Cover:</h3>";
echo "<ul>";
echo "<li><strong>Database Schema:</strong> Verifies all tables and columns exist</li>";
echo "<li><strong>Comment Submission:</strong> Tests API endpoint for posting comments</li>";
echo "<li><strong>Comment Retrieval:</strong> Tests fetching comments for news articles</li>";
echo "<li><strong>Threaded Comments:</strong> Tests reply functionality</li>";
echo "<li><strong>Comment Moderation:</strong> Tests approval/rejection workflow</li>";
echo "<li><strong>Comment Statistics:</strong> Tests stored procedures and stats</li>";
echo "<li><strong>Comment Likes:</strong> Tests like/dislike system</li>";
echo "</ul>";
echo "</div>";

echo "<p><small>This tool provides a complete solution for updating and testing the comment system.</small></p>";
?>
