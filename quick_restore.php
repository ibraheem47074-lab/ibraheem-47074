<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Quick Database Restore</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
<div class='container mt-5'>
    <h2 class='text-center mb-4'>Quick Database Restore</h2>";

try {
    $conn = mysqli_connect($host, $user, $pass, $dbname);
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    echo "<div class='alert alert-info'>Connected to database: $dbname</div>";

    // Check backup file
    $backup_file = __DIR__ . '/backups/pk_live_news_backup_2026-04-05_20-19-55.sql';
    if (!file_exists($backup_file)) {
        echo "<div class='alert alert-danger'>
                <h4>â Backup file not found!</h4>
                <p>Expected backup file: $backup_file</p>
              </div>";
        exit;
    }

    echo "<div class='alert alert-success'>
            <h4>â Backup file found</h4>
            <p>Size: " . number_format(filesize($backup_file)) . " bytes</p>
          </div>";

    // Get current tables
    $result = mysqli_query($conn, "SHOW TABLES");
    $current_tables = [];
    while ($row = mysqli_fetch_row($result)) {
        $current_tables[] = $row[0];
    }

    echo "<div class='alert alert-info'>
            <h4>Current Tables: " . count($current_tables) . "</h4>
          </div>";

    // Read backup file
    $sql_content = file_get_contents($backup_file);
    
    // Remove comments
    $sql_content = preg_replace('/--.*$/m', '', $sql_content);
    $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content);
    
    // Extract CREATE TABLE statements
    preg_match_all('/CREATE\s+TABLE\s+`([^`]+)`\s*\([^)]+\)\s*ENGINE/i', $sql_content, $matches);
    $backup_tables = $matches[1];

    echo "<div class='alert alert-info'>
            <h4>Tables in Backup: " . count($backup_tables) . "</h4>
          </div>";

    // Find missing tables
    $missing_tables = array_diff($backup_tables, $current_tables);
    
    if (empty($missing_tables)) {
        echo "<div class='alert alert-success'>
                <h4>â All tables already exist!</h4>
                <p>No restoration needed.</p>
              </div>";
    } else {
        echo "<div class='alert alert-warning'>
                <h4>Missing Tables: " . count($missing_tables) . "</h4>
                <ul>";
        foreach ($missing_tables as $table) {
            echo "<li><strong>$table</strong></li>";
        }
        echo "</ul>
              </div>";

        // Disable foreign key checks
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

        // Process SQL statements
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

        $success_count = 0;
        $error_count = 0;
        $tables_created = 0;

        foreach ($statements as $statement) {
            if (empty($statement) || strlen($statement) < 10) continue;
            
            // Only process CREATE TABLE statements for missing tables
            if (preg_match('/^CREATE\s+TABLE\s+`([^`]+)`/i', $statement, $matches)) {
                $table_name = $matches[1];
                
                if (in_array($table_name, $missing_tables)) {
                    // Remove foreign key constraints
                    $statement = preg_replace('/,\s*CONSTRAINT\s+`[^`]*`\s*FOREIGN\s+KEY\s*\([^)]*\)\s*REFERENCES\s+[^)]*\s*(ON\s+(DELETE|UPDATE)\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION)\s*)*/i', '', $statement);
                    $statement = preg_replace('/,\s*FOREIGN\s+KEY\s*\([^)]*\)\s*REFERENCES\s+[^)]*\s*(ON\s+(DELETE|UPDATE)\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION)\s*)*/i', '', $statement);
                    $statement = preg_replace('/,\s*ON\s+(DELETE|UPDATE)\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION)/i', '', $statement);
                    $statement = preg_replace('/,\s*\)/', ')', $statement);
                    $statement = preg_replace('/,\s*\)\s*ENGINE/i', ') ENGINE', $statement);
                    
                    if (mysqli_query($conn, $statement)) {
                        $success_count++;
                        $tables_created++;
                        echo "<div class='alert alert-success'>â Created table: $table_name</div>";
                    } else {
                        $error_count++;
                        echo "<div class='alert alert-danger'>â Error creating table $table_name: " . mysqli_error($conn) . "</div>";
                    }
                }
            }
        }

        // Re-enable foreign key checks
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

        echo "<div class='alert alert-primary'>
                <h4>Restore Summary</h4>
                <p>Tables created: $tables_created</p>
                <p>Statements executed: $success_count</p>
                <p>Errors: $error_count</p>
              </div>";
    }

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            <h4>â Error</h4>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}

echo "<div class='text-center mt-4'>
        <a href='check_database_status.php' class='btn btn-primary btn-lg me-2'>Check Database Status</a>
        <a href='admin/website_control.php' class='btn btn-success btn-lg me-2'>Admin Control Panel</a>
        <a href='index.php' class='btn btn-secondary btn-lg'>Back to Home</a>
      </div>";

echo "</div></body></html>";
?>
