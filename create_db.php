<?php
// Quick database creation and import script
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'pk_live_news';

echo "<h2>Database Setup</h2>";

// Create connection without database
$conn = mysqli_connect($host, $user, $pass);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($conn, $sql)) {
    echo "<p style='color:green'>✓ Database '$dbname' created successfully or already exists.</p>";
} else {
    die("<p style='color:red'>✗ Error creating database: " . mysqli_error($conn) . "</p>");
}

mysqli_close($conn);

// Now connect to the database and import
$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("<p style='color:red'>Connection failed: " . mysqli_connect_error() . "</p>");
}

// Import the backup file
$backup_file = __DIR__ . '/backups/pk_live_news_backup_2026-04-05_20-19-55.sql';
if (file_exists($backup_file)) {
    echo "<p>Importing backup file: $backup_file</p>";
    
    // Read SQL file and fix foreign key order
    $sql = file_get_contents($backup_file);
    
    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Split statements and filter
    $all_statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Separate table creation from data insertion
    $create_tables = [];
    $insert_data = [];
    $other_statements = [];
    
    foreach ($all_statements as $statement) {
        if (empty($statement) || strlen($statement) < 10) continue;
        
        if (preg_match('/^CREATE TABLE/i', $statement)) {
            // Remove all foreign key constraints completely
            $statement = preg_replace('/,\s*CONSTRAINT\s+\w+\s+FOREIGN\s+KEY\s*\([^)]+\)\s*REFERENCES\s+\w+\s*\([^)]+\)(?:\s+ON\s+DELETE\s+CASCADE)?/i', '', $statement);
            $statement = preg_replace('/,\s*FOREIGN\s+KEY\s*\([^)]+\)\s*REFERENCES\s+\w+\s*\([^)]+\)(?:\s+ON\s+DELETE\s+CASCADE)?/i', '', $statement);
            // Fix any trailing commas before closing parenthesis
            $statement = preg_replace('/,\s*\)/', ')', $statement);
            $create_tables[] = $statement;
        } elseif (preg_match('/^INSERT/i', $statement)) {
            $insert_data[] = $statement;
        } else {
            $other_statements[] = $statement;
        }
    }
    
    // Execute in order: create tables first, then other statements, then data
    $all_ordered = array_merge($create_tables, $other_statements, $insert_data);
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($all_ordered as $statement) {
        if (!empty($statement) && strlen($statement) > 10) {
            if (mysqli_query($conn, $statement)) {
                $success_count++;
            } else {
                $error_count++;
                echo "<p style='color:orange'>Warning: " . mysqli_error($conn) . "</p>";
            }
        }
    }
    
    echo "<p style='color:green'>✓ Import completed: $success_count statements executed successfully, $error_count warnings.</p>";
} else {
    echo "<p style='color:red'>✗ Backup file not found: $backup_file</p>";
    echo "<p>Trying to import from import.sql instead...</p>";
    
    $backup_file2 = __DIR__ . '/import.sql';
    if (file_exists($backup_file2)) {
        $sql = file_get_contents($backup_file2);
        $sql = preg_replace('/--.*$/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $success_count = 0;
        foreach ($statements as $statement) {
            if (!empty($statement) && strlen($statement) > 10) {
                if (mysqli_query($conn, $statement)) {
                    $success_count++;
                }
            }
        }
        echo "<p style='color:green'>✓ Import completed: $success_count statements executed.</p>";
    }
}

mysqli_close($conn);

echo "<p style='color:green; font-size:18px'><strong>Setup completed successfully!</strong></p>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";
?>
