<?php
// Clean database setup
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pk_live_news';

echo "<h2>Clean Database Setup</h2>";

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
    
    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Split statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strlen($statement) < 10) continue;
        
        // Skip foreign key constraints in CREATE TABLE
        if (preg_match('/^CREATE TABLE/i', $statement)) {
            // Remove all foreign key constraints more comprehensively
            $statement = preg_replace('/,\s*CONSTRAINT\s+`[^`]*`\s*FOREIGN\s+KEY\s*\([^)]*\)\s*REFERENCES\s+[^)]*\s*(ON\s+DELETE\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\s*(ON\s+UPDATE\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\)/i', '', $statement);
            $statement = preg_replace('/,\s*CONSTRAINT\s+[^,)]*\s*FOREIGN\s+KEY\s*\([^)]*\)\s*REFERENCES\s+[^)]*\s*(ON\s+DELETE\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\s*(ON\s+UPDATE\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\)/i', '', $statement);
            $statement = preg_replace('/,\s*FOREIGN\s+KEY\s*\([^)]*\)\s*REFERENCES\s+[^)]*\s*(ON\s+DELETE\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\s*(ON\s+UPDATE\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION))?\)/i', '', $statement);
            
            // Remove any remaining ON DELETE/UPDATE fragments
            $statement = preg_replace('/,\s*ON\s+(DELETE|UPDATE)\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION)\s*\)/i', ')', $statement);
            
            // Clean up trailing commas before closing parenthesis
            $statement = preg_replace('/,\s*\)/', ')', $statement);
            $statement = preg_replace('/,\s*,\s*\)/', ')', $statement);
            
            // Remove any remaining trailing commas before closing parenthesis
            $statement = preg_replace('/,\s*\)\s*ENGINE/i', ') ENGINE', $statement);
        }
        
        if (mysqli_query($conn, $statement)) {
            $success_count++;
        } else {
            $error_count++;
            $error = mysqli_error($conn);
            // Skip expected errors about foreign keys
            if (strpos($error, 'errno: 150') === false && strpos($error, 'errno: 1005') === false) {
                echo "<p style='color:orange'>Warning: $error</p>";
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
?>
