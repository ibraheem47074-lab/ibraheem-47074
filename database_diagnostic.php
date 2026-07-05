<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PK-News Database Diagnostic Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #007bff; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗄️ PK-News Database Diagnostic Tool</h1>
            <p>Comprehensive database testing and monitoring system</p>
        </div>

        <?php
        // Database configuration
        $configs = [
            'live' => [
                'host' => 'localhost',
                'user' => 'u129650532_ibraheem',
                'pass' => 'Khan47074$',
                'dbname' => 'u129650532_ibraheem',
                'name' => 'Live Server Database'
            ],
            'local' => [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '',
                'dbname' => 'pk_live_news',
                'name' => 'Local Development Database'
            ]
        ];

        function testConnection($config) {
            echo "<div class='section'>";
            echo "<h3>🔌 Testing Connection: {$config['name']}</h3>";
            echo "<div class='code'>";
            echo "Host: {$config['host']}<br>";
            echo "Database: {$config['dbname']}<br>";
            echo "User: {$config['user']}<br>";
            echo "</div>";
            
            try {
                $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['dbname']);
                
                if ($conn->connect_error) {
                    echo "<span class='error'>❌ Connection Failed: " . $conn->connect_error . "</span>";
                    return null;
                }
                
                echo "<span class='success'>✅ Connection Successful!</span><br>";
                echo "<span class='info'>Server Info: " . $conn->server_info . "</span><br>";
                echo "<span class='info'>MySQL Version: " . $conn->server_version . "</span><br>";
                
                return $conn;
            } catch (Exception $e) {
                echo "<span class='error'>❌ Exception: " . $e->getMessage() . "</span>";
                return null;
            }
        }

        function analyzeTables($conn) {
            echo "<div class='section'>";
            echo "<h3>📊 Database Tables Analysis</h3>";
            
            // Get all tables
            $tables_result = $conn->query("SHOW TABLES");
            $tables = [];
            
            if ($tables_result) {
                while ($row = $tables_result->fetch_row()) {
                    $tables[] = $row[0];
                }
            }
            
            echo "<p><strong>Total Tables: " . count($tables) . "</strong></p>";
            
            if (empty($tables)) {
                echo "<span class='warning'>⚠️ No tables found in database</span>";
                return;
            }
            
            echo "<table>";
            echo "<tr><th>Table Name</th><th>Rows</th><th>Size</th><th>Engine</th><th>Collation</th><th>Status</th></tr>";
            
            foreach ($tables as $table) {
                // Get table status with error handling
                $status_result = $conn->query("SHOW TABLE STATUS LIKE '$table'");
                $status = $status_result ? $status_result->fetch_assoc() : null;
                if (!$status) {
                    $status = [
                        'Data_length' => 0,
                        'Index_length' => 0,
                        'Engine' => 'Unknown',
                        'Collation' => 'Unknown'
                    ];
                }
                
                // Get row count with error handling
                $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
                $row_count = 0;
                if ($count_result && $count = $count_result->fetch_assoc()) {
                    $row_count = (int)$count['count'];
                }
                $size = $status['Data_length'] + $status['Index_length'];
                $size_formatted = number_format($size / 1024, 2) . ' KB';
                
                echo "<tr>";
                echo "<td><strong>{$table}</strong></td>";
                echo "<td>{$row_count}</td>";
                echo "<td>{$size_formatted}</td>";
                echo "<td>{$status['Engine']}</td>";
                echo "<td>{$status['Collation']}</td>";
                echo "<td>" . ($row_count > 0 ? '<span class="success">✅ Has Data</span>' : '<span class="warning">⚠️ Empty</span>') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }

        function analyzeTableStructure($conn, $table) {
            echo "<div class='section'>";
            echo "<h3>🔍 Table Structure: $table</h3>";
            
            $columns_result = $conn->query("DESCRIBE `$table`");
            
            echo "<table>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            while ($column = $columns_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td><strong>{$column['Field']}</strong></td>";
                echo "<td>{$column['Type']}</td>";
                echo "<td>{$column['Null']}</td>";
                echo "<td>{$column['Key']}</td>";
                echo "<td>{$column['Default']}</td>";
                echo "<td>{$column['Extra']}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            
            // Show indexes
            $indexes_result = $conn->query("SHOW INDEX FROM `$table`");
            if ($indexes_result && $indexes_result->num_rows > 0) {
                echo "<h4>🔑 Indexes</h4>";
                echo "<table>";
                echo "<tr><th>Table</th><th>Non_unique</th><th>Key_name</th><th>Column_name</th><th>Index_type</th></tr>";
                
                while ($index = $indexes_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$index['Table']}</td>";
                    echo "<td>{$index['Non_unique']}</td>";
                    echo "<td><strong>{$index['Key_name']}</strong></td>";
                    echo "<td>{$index['Column_name']}</td>";
                    echo "<td>{$index['Index_type']}</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            }
        }

        function testDataIntegrity($conn) {
            echo "<div class='section'>";
            echo "<h3>🔒 Data Integrity Checks</h3>";
            
            // Check for common critical tables
            $critical_tables = ['users', 'news', 'categories', 'settings'];
            $found_critical = [];
            
            $tables_result = $conn->query("SHOW TABLES");
            $all_tables = [];
            
            if ($tables_result) {
                while ($row = $tables_result->fetch_row()) {
                    $all_tables[] = $row[0];
                }
            }
            
            foreach ($critical_tables as $table) {
                if (in_array($table, $all_tables)) {
                    $found_critical[] = $table;
                }
            }
            
            if (!empty($found_critical)) {
                echo "<h4>📋 Critical Tables Found:</h4>";
                foreach ($found_critical as $table) {
                    echo "<div class='grid'>";
                    
                    // Row count with error handling
                    $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
                    $record_count = 0;
                    if ($count_result && $count = $count_result->fetch_assoc()) {
                        $record_count = (int)$count['count'];
                    }
                    
                    // Sample data
                    $sample_result = $conn->query("SELECT * FROM `$table` LIMIT 3");
                    
                    echo "<div>";
                    echo "<strong>📊 Table: $table</strong><br>";
                    echo "Total Records: <strong>{$record_count}</strong><br>";
                    
                    if ($sample_result && $sample_result->num_rows > 0) {
                        echo "<details><summary>Sample Data</summary>";
                        echo "<table>";
                        
                        // Get column names
                        $columns = [];
                        while ($field = $sample_result->fetch_field()) {
                            $columns[] = $field->name;
                        }
                        
                        echo "<tr>";
                        foreach ($columns as $col) {
                            echo "<th>$col</th>";
                        }
                        echo "</tr>";
                        
                        // Reset pointer and show data
                        $sample_result->data_seek(0);
                        while ($row = $sample_result->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($row as $value) {
                                echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . "</td>";
                            }
                            echo "</tr>";
                        }
                        
                        echo "</table>";
                        echo "</details>";
                    }
                    
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<span class='warning'>⚠️ No critical tables found</span>";
            }
        }

        function countPublishedArticles($conn) {
            echo "<div class='section'>";
            echo "<h3>📰 Published Articles Analysis</h3>";
            
            // Check if news table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'news'");
            if (!$table_check || $table_check->num_rows == 0) {
                echo "<span class='warning'>⚠️ News table not found</span>";
                return;
            }
            
            // Count all articles by status
            $status_counts = [
                'published' => 0,
                'pending' => 0,
                'draft' => 0,
                'other' => 0
            ];
            
            $status_query = $conn->query("SELECT status, COUNT(*) as count FROM news GROUP BY status");
            if ($status_query) {
                while ($row = $status_query->fetch_assoc()) {
                    $status = strtolower($row['status']);
                    if (isset($status_counts[$status])) {
                        $status_counts[$status] = (int)$row['count'];
                    } else {
                        $status_counts['other'] += (int)$row['count'];
                    }
                }
            }
            
            echo "<div class='grid'>";
            echo "<div>";
            echo "<h4>📊 Article Status Breakdown</h4>";
            echo "<table>";
            echo "<tr><th>Status</th><th>Count</th><th>Percentage</th></tr>";
            
            $total = array_sum($status_counts);
            foreach ($status_counts as $status => $count) {
                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                $status_display = ucfirst($status);
                $color_class = $status === 'published' ? 'success' : ($status === 'pending' ? 'warning' : 'info');
                echo "<tr>";
                echo "<td><span class='$color_class'>$status_display</span></td>";
                echo "<td><strong>$count</strong></td>";
                echo "<td>$percentage%</td>";
                echo "</tr>";
            }
            
            echo "<tr><td><strong>Total</strong></td><td><strong>$total</strong></td><td>100%</td></tr>";
            echo "</table>";
            echo "</div>";
            
            // Recent published articles
            echo "<div>";
            echo "<h4>🕐 Recent Published Articles</h4>";
            $recent_query = $conn->query("SELECT id, title, created_at, published_at FROM news WHERE status = 'published' ORDER BY published_at DESC LIMIT 5");
            if ($recent_query && $recent_query->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Title</th><th>Published</th></tr>";
                while ($article = $recent_query->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$article['id']}</td>";
                    echo "<td>" . htmlspecialchars(substr($article['title'], 0, 50)) . "</td>";
                    echo "<td>{$article['published_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<span class='warning'>⚠️ No published articles found</span>";
            }
            echo "</div>";
            
            echo "</div>";
        }

        function testCRUDOperations($conn) {
            echo "<div class='section'>";
            echo "<h3>🧪 Database Operations Test</h3>";
            
            // Create a test table
            $test_table = 'diagnostic_test_' . time();
            
            try {
                // CREATE TABLE
                $create_sql = "CREATE TABLE `$test_table` (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    test_name VARCHAR(100) NOT NULL,
                    test_value TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                if ($conn->query($create_sql)) {
                    echo "<span class='success'>✅ CREATE TABLE: Success</span><br>";
                } else {
                    echo "<span class='error'>❌ CREATE TABLE: Failed</span><br>";
                    return;
                }
                
                // INSERT
                $insert_sql = "INSERT INTO `$test_table` (test_name, test_value) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $test_name = 'Diagnostic Test';
                $test_value = 'This is a test record for database operations';
                
                if ($stmt->bind_param("ss", $test_name, $test_value) && $stmt->execute()) {
                    echo "<span class='success'>✅ INSERT: Success</span><br>";
                } else {
                    echo "<span class='error'>❌ INSERT: Failed</span><br>";
                }
                
                // SELECT
                $select_sql = "SELECT * FROM `$test_table` WHERE test_name = ?";
                $stmt = $conn->prepare($select_sql);
                
                if ($stmt->bind_param("s", $test_name) && $stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        echo "<span class='success'>✅ SELECT: Success ({$result->num_rows} records found)</span><br>";
                    } else {
                        echo "<span class='warning'>⚠️ SELECT: No records found</span><br>";
                    }
                } else {
                    echo "<span class='error'>❌ SELECT: Failed</span><br>";
                }
                
                // UPDATE
                $update_sql = "UPDATE `$test_table` SET test_value = ? WHERE test_name = ?";
                $stmt = $conn->prepare($update_sql);
                $updated_value = 'Updated test value';
                
                if ($stmt->bind_param("ss", $updated_value, $test_name) && $stmt->execute()) {
                    echo "<span class='success'>✅ UPDATE: Success ({$stmt->affected_rows} rows affected)</span><br>";
                } else {
                    echo "<span class='error'>❌ UPDATE: Failed</span><br>";
                }
                
                // DELETE
                $delete_sql = "DELETE FROM `$test_table` WHERE test_name = ?";
                $stmt = $conn->prepare($delete_sql);
                
                if ($stmt->bind_param("s", $test_name) && $stmt->execute()) {
                    echo "<span class='success'>✅ DELETE: Success ({$stmt->affected_rows} rows deleted)</span><br>";
                } else {
                    echo "<span class='error'>❌ DELETE: Failed</span><br>";
                }
                
                // DROP TABLE
                if ($conn->query("DROP TABLE `$test_table`")) {
                    echo "<span class='success'>✅ DROP TABLE: Success</span><br>";
                } else {
                    echo "<span class='error'>❌ DROP TABLE: Failed</span><br>";
                }
                
            } catch (Exception $e) {
                echo "<span class='error'>❌ CRUD Test Exception: " . $e->getMessage() . "</span>";
            }
        }

        function showDatabaseInfo($conn) {
            echo "<div class='section'>";
            echo "<h3>ℹ️ Database Information</h3>";
            
            // Server variables
            $variables = [
                'version' => 'SELECT VERSION() as version',
                'character_set' => 'SELECT @@character_set_database as charset',
                'collation' => 'SELECT @@collation_database as collation',
                'max_connections' => 'SELECT @@max_connections as max_connections',
                'timezone' => 'SELECT @@time_zone as timezone'
            ];
            
            echo "<table>";
            echo "<tr><th>Property</th><th>Value</th></tr>";
            
            foreach ($variables as $name => $sql) {
                $result = $conn->query($sql);
                if ($result && $row = $result->fetch_assoc()) {
                    $value = reset($row);
                    echo "<tr><td><strong>$name</strong></td><td>$value</td></tr>";
                }
            }
            
            echo "</table>";
        }

        // Main execution
        $active_connection = null;

        foreach ($configs as $key => $config) {
            $connection = testConnection($config);
            if ($connection) {
                $active_connection = $connection;
                break;
            }
        }

        if ($active_connection) {
            echo "<div class='section'>";
            echo "<h2>🎯 Database Analysis Results</h2>";
            echo "</div>";
            
            analyzeTables($active_connection);
            showDatabaseInfo($active_connection);
            testDataIntegrity($active_connection);
            countPublishedArticles($active_connection);
            testCRUDOperations($active_connection);
            
            // Detailed table structure for first few tables
            $tables_result = $active_connection->query("SHOW TABLES");
            $table_count = 0;
            if ($tables_result) {
                while (($row = $tables_result->fetch_row()) && $table_count < 3) {
                    if (!empty($row[0])) {
                        analyzeTableStructure($active_connection, $row[0]);
                        $table_count++;
                    }
                }
            }
            
            $active_connection->close();
        } else {
            echo "<div class='section'>";
            echo "<span class='error'>❌ No database connections could be established. Please check your credentials.</span>";
            echo "</div>";
        }

        echo "<div class='section'>";
        echo "<h3>🔧 Quick Actions</h3>";
        echo "<button class='btn' onclick='location.reload()'>🔄 Refresh Tests</button>";
        echo "<button class='btn' onclick='window.print()'>🖨️ Print Report</button>";
        echo "</div>";
        ?>

        <div class='section'>
            <p><small>Generated on: <?php echo date('Y-m-d H:i:s'); ?></small></p>
        </div>
    </div>
</body>
</html>
