<?php
require_once 'config/database.php';

echo "<h1>Setting up User Analytics Table</h1>";

// Read and execute the SQL file
$sql_file = 'create_user_analytics_table.sql';
if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                if (mysqli_query($conn, $statement)) {
                    $success_count++;
                    echo "<p style='color: green;'>Success: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
                } else {
                    $error_count++;
                    echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
                    echo "<p style='color: orange;'>Statement: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
                }
            } catch (Exception $e) {
                $error_count++;
                echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<h2>Setup Complete</h2>";
    echo "<p>Successful statements: $success_count</p>";
    echo "<p>Failed statements: $error_count</p>";
    
    // Verify both tables were created
    $tables = ['user_analytics', 'page_views'];
    $all_created = true;
    
    foreach ($tables as $table) {
        $check_query = "SHOW TABLES LIKE '$table'";
        $result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($result) > 0) {
            echo "<p style='color: green; font-weight: bold;'>$table table created successfully!</p>";
            
            // Show table structure
            echo "<h3>$table Table Structure:</h3>";
            $structure_query = "DESCRIBE $table";
            $structure_result = mysqli_query($conn, $structure_query);
            
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
            }
            echo "</table>";
            
            // Show sample data
            echo "<h3>$table Sample Data:</h3>";
            $data_query = "SELECT * FROM $table LIMIT 5";
            $data_result = mysqli_query($conn, $data_query);
            
            if (mysqli_num_rows($data_result) > 0) {
                echo "<table border='1' cellpadding='5'>";
                
                // Dynamic headers based on table
                if ($table === 'user_analytics') {
                    echo "<tr><th>ID</th><th>User ID</th><th>Action</th><th>Page URL</th><th>IP Address</th><th>Created At</th></tr>";
                    while ($row = mysqli_fetch_assoc($data_result)) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['user_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['action'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['page_url'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['ip_address'] ?? '') . "</td>";
                        echo "<td>" . $row['created_at'] . "</td>";
                        echo "</tr>";
                    }
                } else { // page_views
                    echo "<tr><th>ID</th><th>Page URL</th><th>Page Type</th><th>Page Title</th><th>IP Address</th><th>Created At</th></tr>";
                    while ($row = mysqli_fetch_assoc($data_result)) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['page_url'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['page_type'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['page_title'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['ip_address'] ?? '') . "</td>";
                        echo "<td>" . $row['created_at'] . "</td>";
                        echo "</tr>";
                    }
                }
                echo "</table>";
            } else {
                echo "<p>No sample data found.</p>";
            }
            
        } else {
            echo "<p style='color: red; font-weight: bold;'>Failed to create $table table!</p>";
            $all_created = false;
        }
    }
    
    if ($all_created) {
        echo "<p style='color: green; font-size: 16px; font-weight: bold;'>All analytics tables created successfully! The analytics page should now work.</p>";
    }
    
} else {
    echo "<p style='color: red;'>SQL file not found: $sql_file</p>";
}

mysqli_close($conn);

echo "<p><a href='admin/analytics.php'>Go to Analytics Page</a></p>";
?>
