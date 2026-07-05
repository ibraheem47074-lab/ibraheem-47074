<?php
include 'config/database.php';

// Read the SQL file
$sql_file = 'create_events_tables.sql';
$sql = file_get_contents($sql_file);

// Split the SQL file into individual queries
$queries = explode(';', $sql);

$success_count = 0;
$error_count = 0;

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query) && !preg_match('/^--/', $query)) {
        try {
            if (mysqli_query($conn, $query)) {
                $success_count++;
                echo "Query executed successfully: " . substr($query, 0, 50) . "...\n";
            } else {
                $error_count++;
                echo "Error executing query: " . mysqli_error($conn) . "\n";
                echo "Query: " . $query . "\n\n";
            }
        } catch (Exception $e) {
            $error_count++;
            echo "Exception: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nSummary:\n";
echo "Successful queries: $success_count\n";
echo "Failed queries: $error_count\n";

if ($success_count > 0) {
    echo "\nEvents table has been created successfully!\n";
} else {
    echo "\nFailed to create events table.\n";
}

mysqli_close($conn);
?>
