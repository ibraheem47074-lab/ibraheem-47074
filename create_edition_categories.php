<?php
require_once 'config/database.php';

// Read and execute the SQL file
$sql_file = 'create_edition_categories_table.sql';
$sql_content = file_get_contents($sql_file);

if ($sql_content === false) {
    die("Error: Could not read SQL file: $sql_file");
}

// Split the SQL content into individual statements
$statements = explode(';', $sql_content);

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement) && !preg_match('/^--/', $statement)) {
        try {
            if (mysqli_query($conn, $statement)) {
                echo "Successfully executed: " . substr($statement, 0, 50) . "...\n";
            } else {
                echo "Error executing statement: " . mysqli_error($conn) . "\n";
                echo "Statement: " . $statement . "\n";
            }
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
        }
    }
}

echo "Edition categories table creation completed.\n";
?>
