<?php
require_once 'config/database.php';

echo "<h1>Fix Poll Votes Table</h1>";

// Check if poll_votes table exists
$check_table_query = "SHOW TABLES LIKE 'poll_votes'";
$table_result = mysqli_query($conn, $check_table_query);

if (mysqli_num_rows($table_result) > 0) {
    echo "<p style='color: green;'>poll_votes table exists</p>";
    
    // Check current table structure
    echo "<h3>Current Table Structure:</h3>";
    $structure_query = "DESCRIBE poll_votes";
    $structure_result = mysqli_query($conn, $structure_query);
    
    $has_voted_at = false;
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    while ($row = mysqli_fetch_assoc($structure_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? '') . "</td>";
        echo "</tr>";
        
        if ($row['Field'] === 'voted_at') {
            $has_voted_at = true;
        }
    }
    echo "</table>";
    
    if ($has_voted_at) {
        echo "<p style='color: green; font-weight: bold;'>voted_at column already exists!</p>";
        
        // Show sample data
        echo "<h3>Sample Data:</h3>";
        $data_query = "SELECT * FROM poll_votes LIMIT 5";
        $data_result = mysqli_query($conn, $data_query);
        
        if (mysqli_num_rows($data_result) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Poll ID</th><th>Option ID</th><th>User ID</th><th>IP Address</th><th>Voted At</th></tr>";
            
            while ($row = mysqli_fetch_assoc($data_result)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['poll_id'] . "</td>";
                echo "<td>" . $row['option_id'] . "</td>";
                echo "<td>" . ($row['user_id'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($row['ip_address'] ?? '') . "</td>";
                echo "<td>" . ($row['voted_at'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No data in poll_votes table.</p>";
        }
        
    } else {
        echo "<p style='color: orange;'>voted_at column is missing. Adding it now...</p>";
        
        // Add the missing voted_at column
        $alter_query = "ALTER TABLE poll_votes ADD COLUMN voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER ip_address";
        
        if (mysqli_query($conn, $alter_query)) {
            echo "<p style='color: green; font-weight: bold;'>voted_at column added successfully!</p>";
            
            // Update existing records to have current timestamp
            $update_query = "UPDATE poll_votes SET voted_at = created_at WHERE voted_at IS NULL";
            if (mysqli_query($conn, $update_query)) {
                echo "<p style='color: green;'>Updated existing records with current timestamps.</p>";
            }
            
            // If no created_at column, set all to NOW()
            $check_created_query = "SHOW COLUMNS FROM poll_votes LIKE 'created_at'";
            $created_result = mysqli_query($conn, $check_created_query);
            if (mysqli_num_rows($created_result) === 0) {
                $update_now_query = "UPDATE poll_votes SET voted_at = NOW() WHERE voted_at IS NULL";
                mysqli_query($conn, $update_now_query);
            }
            
        } else {
            echo "<p style='color: red;'>Error adding voted_at column: " . mysqli_error($conn) . "</p>";
        }
        
        // Show updated structure
        echo "<h3>Updated Table Structure:</h3>";
        $new_structure_query = "DESCRIBE poll_votes";
        $new_structure_result = mysqli_query($conn, $new_structure_query);
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        while ($row = mysqli_fetch_assoc($new_structure_result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} else {
    echo "<p style='color: red;'>poll_votes table doesn't exist. Creating it...</p>";
    
    // Create the complete table
    $create_query = "CREATE TABLE poll_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        poll_id INT NOT NULL,
        option_id INT NOT NULL,
        user_id INT,
        ip_address VARCHAR(45),
        voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (poll_id),
        INDEX (option_id),
        INDEX (voted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($conn, $create_query)) {
        echo "<p style='color: green; font-weight: bold;'>poll_votes table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating table: " . mysqli_error($conn) . "</p>";
    }
}

mysqli_close($conn);

echo "<p><a href='admin/view-poll-results.php'>Go to Poll Results Page</a></p>";
?>
