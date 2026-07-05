<?php
require_once 'config/database.php';

echo "<h1>Multi-Language Database Setup</h1>";

// Check database connection
if (!$conn) {
    die("<div class='alert alert-danger'>Database connection failed!</div>");
}

// Read the SQL file
$sql_file = 'database_update_multilang.sql';
if (!file_exists($sql_file)) {
    die("<div class='alert alert-danger'>SQL file not found: $sql_file</div>");
}

$sql = file_get_contents($sql_file);
echo "<h2>Executing SQL Setup...</h2>";

// Split SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;
$errors = [];

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    try {
        if (mysqli_query($conn, $statement)) {
            $success_count++;
            echo "<div style='color: green;'>✓ " . htmlspecialchars(substr($statement, 0, 80)) . "...</div>";
        } else {
            $error = mysqli_error($conn);
            if (strpos($error, 'already exists') !== false || strpos($error, 'Duplicate') !== false) {
                echo "<div style='color: orange;'>⚠ " . htmlspecialchars(substr($statement, 0, 80)) . "... (already exists)</div>";
                $success_count++;
            } else {
                $error_count++;
                $errors[] = $error;
                echo "<div style='color: red;'>✗ " . htmlspecialchars($error) . "</div>";
            }
        }
    } catch (Exception $e) {
        $error_count++;
        $errors[] = $e->getMessage();
        echo "<div style='color: red;'>✗ " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

echo "<h2>Setup Results</h2>";
echo "<div style='background: #e8f5e8; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
echo "<strong>✓ Success:</strong> $success_count statements executed<br>";
if ($error_count > 0) {
    echo "<strong>✗ Errors:</strong> $error_count statements failed<br>";
}
echo "</div>";

// Verify tables were created
echo "<h2>Verification</h2>";

$tables_to_check = [
    'languages' => 'Languages table',
    'user_language_preferences' => 'User language preferences table',
    'site_settings' => 'Site settings table'
];

foreach ($tables_to_check as $table => $description) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<div style='color: green;'>✓ $description exists</div>";
        
        // Show table info for languages
        if ($table === 'languages') {
            $count_query = "SELECT COUNT(*) as count FROM $table";
            $count_result = mysqli_query($conn, $count_query);
            $row = mysqli_fetch_assoc($count_result);
            echo "<div style='color: blue; margin-left: 20px;'>→ $row[count] languages found</div>";
        }
    } else {
        echo "<div style='color: red;'>✗ $description missing</div>";
    }
}

// Check if news table has language columns
echo "<h2>News Table Language Columns</h2>";
$news_columns = ['language_code', 'title_ur', 'title_hi', 'title_zh', 'title_ps', 'content_ur', 'content_hi', 'content_zh', 'content_ps'];

foreach ($news_columns as $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE '$column'");
    if (mysqli_num_rows($result) > 0) {
        echo "<div style='color: green;'>✓ Column $column exists</div>";
    } else {
        echo "<div style='color: red;'>✗ Column $column missing</div>";
    }
}

if ($error_count === 0) {
    echo "<h2 style='color: green;'>🎉 Setup Complete!</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
    echo "<strong>Multi-language system is now ready!</strong><br><br>";
    echo "Next steps:<br>";
    echo "1. <a href='index.php'>Visit your website</a> to test the language switcher<br>";
    echo "2. <a href='admin/manage_languages.php'>Manage languages in admin panel</a><br>";
    echo "3. <a href='admin/add_news_multilang.php'>Create multilingual news</a><br>";
    echo "</div>";
} else {
    echo "<h2 style='color: red;'>⚠ Setup Issues Found</h2>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
    echo "<strong>Please check the errors above and fix them manually.</strong><br>";
    echo "You may need to run the SQL commands directly in your database management tool.";
    echo "</div>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
div { margin: 5px 0; padding: 5px; }
</style>";
?>
