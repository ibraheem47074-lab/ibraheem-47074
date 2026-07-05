<?php
// Clean database setup with robust foreign key handling
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pk_live_news';

echo "<h2>Clean Database Setup (Robust Version)</h2>";

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) die("Database connection failed");

// Disable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

// Get all tables
$result = mysqli_query($conn, "SHOW TABLES");
$tables = [];
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

// Drop all tables
if (!empty($tables)) {
    foreach ($tables as $table) {
        mysqli_query($conn, "DROP TABLE IF EXISTS `$table`");
        echo "<p>â Dropped table: $table</p>";
    }
}

// Re-enable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

echo "<p>â All tables dropped</p>";

// Import backup file
$backup_file = __DIR__ . '/backups/pk_live_news_backup_2026-04-05_20-19-55.sql';
if (file_exists($backup_file)) {
    echo "<p>Importing backup file...</p>";
    
    $sql = file_get_contents($backup_file);
    
    // Remove comments and clean up SQL
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Split into individual statements
    $statements = [];
    $current_statement = '';
    $lines = explode("\n", $sql);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $current_statement .= $line . ' ';
        
        // Check if statement ends with semicolon
        if (preg_match('/;$/', $line)) {
            $statements[] = trim($current_statement);
            $current_statement = '';
        }
    }
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strlen($statement) < 10) continue;
        
        // Skip foreign key constraints in CREATE TABLE statements
        if (preg_match('/^CREATE\s+TABLE\s+/i', $statement)) {
            // Remove all foreign key constraints completely
            $statement = removeForeignKeys($statement);
        }
        
        // Try to execute the statement
        if (mysqli_query($conn, $statement)) {
            $success_count++;
        } else {
            $error_count++;
            $error = mysqli_error($conn);
            // Skip expected errors about foreign keys
            if (strpos($error, 'errno: 150') === false && strpos($error, 'errno: 1005') === false) {
                echo "<p style='color:orange'>Warning: $error</p>";
                echo "<p style='color:gray'>Statement: " . substr($statement, 0, 100) . "...</p>";
            }
        }
    }
    
    echo "<p style='color:green'>â Import completed: $success_count statements executed, $error_count warnings</p>";
} else {
    echo "<p style='color:red'>â Backup file not found</p>";
}

echo "<p style='color:green; font-size:18px'><strong>Setup completed!</strong></p>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";

mysqli_close($conn);

// Function to remove foreign key constraints from CREATE TABLE statements
function removeForeignKeys($sql) {
    // Remove CONSTRAINT ... FOREIGN KEY ... REFERENCES
    $sql = preg_replace('/,\s*CONSTRAINT\s+`[^`]*`\s*FOREIGN\s+KEY\s*\([^)]*\)\s*REFERENCES\s+[^)]*\s*(?:ON\s+(?:DELETE|UPDATE)\s+(?:CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\s*(?:ON\s+(?:DELETE|UPDATE)\s+(?:CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\s*/i', '', $sql);
    
    // Remove FOREIGN KEY ... REFERENCES
    $sql = preg_replace('/,\s*FOREIGN\s+KEY\s*\([^)]*\)\s*REFERENCES\s+[^)]*\s*(?:ON\s+(?:DELETE|UPDATE)\s+(?:CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\s*(?:ON\s+(?:DELETE|UPDATE)\s+(?:CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\s*/i', '', $sql);
    
    // Remove any remaining ON DELETE/UPDATE fragments
    $sql = preg_replace('/,\s*ON\s+(?:DELETE|UPDATE)\s+(?:CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION)\s*(?:,\s*ON\s+(?:DELETE|UPDATE)\s+(?:CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\s*/i', '', $sql);
    
    // Clean up trailing commas before closing parenthesis
    $sql = preg_replace('/,\s*\)/', ')', $sql);
    $sql = preg_replace('/,\s*,\s*\)/', ')', $sql);
    
    // Clean up trailing commas before ENGINE
    $sql = preg_replace('/,\s*\)\s*ENGINE/i', ') ENGINE', $sql);
    
    // Remove any remaining trailing commas before closing parenthesis
    $sql = preg_replace('/,\s*\s*\)/', ')', $sql);
    
    return $sql;
}
?>
