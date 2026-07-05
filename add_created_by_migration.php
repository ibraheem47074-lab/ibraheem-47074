<?php
// Database migration script to add created_by column to live_stream table
require_once 'config/database.php';

echo "Starting migration to add created_by column to live_stream table...\n";

// Check if column already exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM live_stream LIKE 'created_by'");
if (mysqli_num_rows($check_column) > 0) {
    echo "Column 'created_by' already exists in live_stream table.\n";
    exit;
}

// Add the column
$alter_table = "ALTER TABLE live_stream ADD COLUMN created_by INT";
if (mysqli_query($conn, $alter_table)) {
    echo "Successfully added created_by column to live_stream table.\n";
} else {
    echo "Error adding created_by column: " . mysqli_error($conn) . "\n";
    exit;
}

// Add foreign key constraint
$add_fk = "ALTER TABLE live_stream ADD CONSTRAINT fk_live_stream_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL";
if (mysqli_query($conn, $add_fk)) {
    echo "Successfully added foreign key constraint.\n";
} else {
    echo "Warning: Could not add foreign key constraint (may already exist or users table issue): " . mysqli_error($conn) . "\n";
}

echo "Migration completed successfully!\n";
?>
