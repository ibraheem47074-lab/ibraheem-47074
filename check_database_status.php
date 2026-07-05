<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Status Check</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
<div class='container mt-5'>
    <h2 class='text-center mb-4'>Database Status Check</h2>";

try {
    // Use the existing connection from database.php
    if (!isset($conn)) {
        throw new Exception("Database connection not available");
    }

    echo "<div class='alert alert-info'>Connected to database successfully</div>";

    // Get all tables
    $result = mysqli_query($conn, "SHOW TABLES");
    $tables = [];
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }

    if (empty($tables)) {
        echo "<div class='alert alert-danger'>
                <h4>â No tables found in database!</h4>
                <p>The database appears to be empty. You need to restore from backup.</p>
              </div>";
        
        // Check if backup file exists
        $backup_file = __DIR__ . '/backups/pk_live_news_backup_2026-04-05_20-19-55.sql';
        if (file_exists($backup_file)) {
            echo "<div class='alert alert-warning'>
                    <h4>Backup file found</h4>
                    <p>Backup file exists at: $backup_file</p>
                    <p>Size: " . number_format(filesize($backup_file)) . " bytes</p>
                    <a href='clean_setup.php' class='btn btn-danger'>Run Clean Setup (Restore from Backup)</a>
                  </div>";
        } else {
            echo "<div class='alert alert-danger'>
                    <h4>â Backup file not found!</h4>
                    <p>No backup file found at expected location.</p>
                  </div>";
        }
    } else {
        echo "<div class='alert alert-success'>
                <h4>â Found " . count($tables) . " tables</h4>
              </div>";

        echo "<div class='table-responsive'>
                <table class='table table-striped'>
                    <thead class='table-dark'>
                        <tr>
                            <th>Table Name</th>
                            <th>Records</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>";

        $critical_tables = ['news', 'articles', 'channels', 'users', 'categories', 'admin'];
        
        foreach ($tables as $table) {
            $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table`");
            $count = mysqli_fetch_assoc($count_result)['count'];
            
            $status_class = 'table-success';
            $status_text = 'OK';
            
            if ($count == 0) {
                $status_class = 'table-warning';
                $status_text = 'Empty';
            }
            
            if (in_array($table, $critical_tables)) {
                echo "<tr class='table-primary'>";
                echo "<td><strong>" . htmlspecialchars($table) . "</strong> â¸</td>";
                echo "<td>" . number_format($count) . "</td>";
                echo "<td><span class='badge bg-success'>$status_text</span></td>";
                echo "</tr>";
            } else {
                echo "<tr class='$status_class'>";
                echo "<td>" . htmlspecialchars($table) . "</td>";
                echo "<td>" . number_format($count) . "</td>";
                echo "<td><span class='badge bg-info'>$status_text</span></td>";
                echo "</tr>";
            }
        }

        echo "</tbody></table></div>";

        // Check for missing critical tables
        $missing_critical = array_diff($critical_tables, $tables);
        if (!empty($missing_critical)) {
            echo "<div class='alert alert-danger'>
                    <h4>â Missing Critical Tables:</h4>
                    <ul>";
            foreach ($missing_critical as $missing) {
                echo "<li><strong>$missing</strong> - This table is required for the website to function</li>";
            }
            echo "</ul>
                    <p>You need to restore the database from backup.</p>
                    <a href='clean_setup.php' class='btn btn-danger'>Run Clean Setup (Restore from Backup)</a>
                  </div>";
        }
    }

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            <h4>â Database Error</h4>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}

echo "<div class='text-center mt-4'>
        <a href='index.php' class='btn btn-primary btn-lg me-2'>Back to Home</a>
        <a href='admin/' class='btn btn-secondary btn-lg me-2'>Admin Panel</a>
        <a href='live.php' class='btn btn-success btn-lg'>Live TV</a>
      </div>";

echo "</div></body></html>";
?>
