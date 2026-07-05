<?php
// Clean database setup - Simple version that avoids foreign key issues
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pk_live_news';

echo "<h2>Clean Database Setup (Simple Version)</h2>";

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) die("Database connection failed");

// Disable foreign key checks completely
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($conn, "SET UNIQUE_CHECKS = 0");
mysqli_query($conn, "SET AUTOCOMMIT = 0");

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

echo "<p>â All tables dropped</p>";

// Import backup file with simple approach
$backup_file = __DIR__ . '/backups/pk_live_news_backup_2026-04-05_20-19-55.sql';
if (file_exists($backup_file)) {
    echo "<p>Importing backup file...</p>";
    
    // Read and process the SQL file
    $sql_content = file_get_contents($backup_file);
    
    // Remove comments completely
    $sql_content = preg_replace('/--.*$/m', '', $sql_content);
    $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content);
    
    // Split into statements more reliably
    $statements = [];
    $current = '';
    $in_string = false;
    $string_char = '';
    $escape_next = false;
    
    for ($i = 0; $i < strlen($sql_content); $i++) {
        $char = $sql_content[$i];
        
        if ($escape_next) {
            $current .= $char;
            $escape_next = false;
            continue;
        }
        
        if ($char === '\\') {
            $current .= $char;
            $escape_next = true;
            continue;
        }
        
        if (($char === "'" || $char === '"') && !$escape_next) {
            if ($in_string && $string_char === $char) {
                $in_string = false;
                $string_char = '';
            } elseif (!$in_string) {
                $in_string = true;
                $string_char = $char;
            }
        }
        
        if ($char === ';' && !$in_string) {
            $statement = trim($current);
            if (!empty($statement)) {
                $statements[] = $statement;
            }
            $current = '';
        } else {
            $current .= $char;
        }
    }
    
    // Add the last statement if exists
    $statement = trim($current);
    if (!empty($statement)) {
        $statements[] = $statement;
    }
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strlen($statement) < 10) continue;
        
        // For CREATE TABLE statements, remove foreign key constraints completely
        if (preg_match('/^CREATE\s+TABLE\s+/i', $statement)) {
            // Remove all foreign key constraints by finding and removing them
            $statement = preg_replace('/,\s*CONSTRAINT\s+`[^`]*`\s*FOREIGN\s+KEY\s*\([^)]*\)\s*REFERENCES\s+[^)]*\s*(ON\s+(DELETE|UPDATE)\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION)\s*)*/i', '', $statement);
            $statement = preg_replace('/,\s*FOREIGN\s+KEY\s*\([^)]*\)\s*REFERENCES\s+[^)]*\s*(ON\s+(DELETE|UPDATE)\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION)\s*)*/i', '', $statement);
            
            // Clean up any remaining ON DELETE/UPDATE fragments
            $statement = preg_replace('/,\s*ON\s+(DELETE|UPDATE)\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION)/i', '', $statement);
            
            // Clean up trailing commas
            $statement = preg_replace('/,\s*\)/', ')', $statement);
            $statement = preg_replace('/,\s*\)\s*ENGINE/i', ') ENGINE', $statement);
        }
        
        // Execute the statement
        if (mysqli_query($conn, $statement)) {
            $success_count++;
        } else {
            $error_count++;
            $error = mysqli_error($conn);
            
            // Skip foreign key related errors
            if (strpos($error, 'errno: 150') === false && 
                strpos($error, 'errno: 1005') === false && 
                strpos($error, 'errno: 121') === false) {
                echo "<p style='color:orange'>Warning: $error</p>";
                echo "<p style='color:gray'>Statement: " . substr($statement, 0, 100) . "...</p>";
            }
        }
    }
    
    echo "<p style='color:green'>â Import completed: $success_count statements executed, $error_count warnings</p>";
} else {
    echo "<p style='color:red'>â Backup file not found</p>";
}

// Re-enable constraints
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
mysqli_query($conn, "SET UNIQUE_CHECKS = 1");
mysqli_query($conn, "COMMIT");

echo "<p style='color:green; font-size:18px'><strong>Setup completed!</strong></p>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";

mysqli_close($conn);
?>
