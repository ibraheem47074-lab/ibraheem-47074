<?php
/**
 * admin/repair-database.php
 * Tool to fix ID 0 issues and restore AUTO_INCREMENT
 */
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    die("Access denied. Please login as administrator.");
}

$page_title = "Database Repair Tool";
// Use the appropriate header based on your context (usually admin-header.php or similar)
if (file_exists('includes/admin-header.php')) {
    include 'includes/admin-header.php';
} elseif (file_exists('../includes/admin-header.php')) {
    include '../includes/admin-header.php';
}
?>

<div class="container mt-5">
    <div class="card shadow border-danger">
        <div class="card-header bg-danger text-white">
            <h3 class="mb-0"><i class="fas fa-tools me-2"></i>Database ID Repair Tool</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-warning border-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Important Instructions</h5>
                <p>This tool will fix the "ID 0" issue by:</p>
                <ol>
                    <li>Finding all records that currently have an ID of <code>0</code>.</li>
                    <li>Assigning them new, unique, unique IDs starting from the highest existing ID + 1.</li>
                    <li>Fixing the database structure to ensure the <code>id</code> column is a <strong>PRIMARY KEY</strong> with <strong>AUTO_INCREMENT</strong> enabled.</li>
                </ol>
                <p class="mb-0"><strong>Please backup your database before running this!</strong></p>
            </div>

            <?php
            if (isset($_POST['run_repair'])) {
                // List of all major tables that use an 'id' column as a primary key
                $tables = [
                    'news', 'users', 'categories', 'advertisements', 'comments', 
                    'polls', 'poll_options', 'live_stream', 'events', 'tags',
                    'news_sources', 'notifications', 'news_likes', 'news_shares'
                ];

                foreach ($tables as $table) {
                    echo "<div class='mb-3 p-3 border rounded bg-light'>";
                    echo "<h6>Processing Table: <code>$table</code></h6>";
                    
                    // Check if table exists
                    $table_exists = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
                    if (mysqli_num_rows($table_exists) == 0) {
                        echo "<span class='text-muted small'><i class='fas fa-info-circle me-1'></i>Table does not exist. Skipping.</span>";
                        echo "</div>";
                        continue;
                    }

                    // 1. Assign unique IDs to rows with ID 0
                    $res = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table` WHERE id = 0");
                    $row = mysqli_fetch_assoc($res);
                    $zero_count = $row['count'];
                    
                    if ($zero_count > 0) {
                        echo "<div class='text-warning small mb-2'><i class='fas fa-exclamation-circle me-1'></i>Found $zero_count records with ID 0. Repairing...</div>";
                        
                        // Get the current max ID to start from
                        $max_res = mysqli_query($conn, "SELECT MAX(id) as max_id FROM `$table` ");
                        $max_row = mysqli_fetch_assoc($max_res);
                        $start_id = (int)$max_row['max_id'] + 1;
                        
                        // Fix each row with ID 0 individually
                        for ($i = 0; $i < $zero_count; $i++) {
                            $new_id = $start_id + $i;
                            // We use LIMIT 1 to update exactly one instance of ID 0 at a time
                            $update_sql = "UPDATE `$table` SET id = $new_id WHERE id = 0 LIMIT 1";
                            mysqli_query($conn, $update_sql);
                        }
                        echo "<div class='text-success small'><i class='fas fa-check me-1'></i>Fixed IDs for $zero_count records.</div>";
                    } else {
                        echo "<div class='text-success small'><i class='fas fa-check me-1'></i>No duplicate ID 0 records found.</div>";
                    }

                    // 2. Ensure schema has PRIMARY KEY and AUTO_INCREMENT
                    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE 'id'");
                    $col = mysqli_fetch_assoc($res);
                    
                    $is_pk = ($col['Key'] == 'PRI');
                    $is_ai = (strpos($col['Extra'], 'auto_increment') !== false);
                    
                    if (!$is_pk || !$is_ai) {
                        echo "<div class='text-info small mb-2'><i class='fas fa-cog me-1'></i>Updating table structure...</div>";
                        
                        try {
                            // If it's not a primary key, try adding it
                            if (!$is_pk) {
                                // First check if any other Primary Key exists (to avoid errors)
                                $pk_check = mysqli_query($conn, "SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
                                if (mysqli_num_rows($pk_check) == 0) {
                                    mysqli_query($conn, "ALTER TABLE `$table` ADD PRIMARY KEY (id)");
                                }
                            }
                            
                            // Set the AUTO_INCREMENT property
                            $sql = "ALTER TABLE `$table` MODIFY id INT NOT NULL AUTO_INCREMENT";
                            if (mysqli_query($conn, $sql)) {
                                echo "<div class='text-success small font-weight-bold'><i class='fas fa-magic me-1'></i>Table structure successfully restored!</div>";
                            } else {
                                echo "<div class='text-danger small'>Structure error: " . mysqli_error($conn) . "</div>";
                            }
                        } catch (Exception $e) {
                            echo "<div class='text-danger small'>Error: " . $e->getMessage() . "</div>";
                        }
                    } else {
                        echo "<div class='text-success small'><i class='fas fa-check-double me-1'></i>Table structure is correct.</div>";
                    }
                    echo "</div>";
                }
                echo "<div class='alert alert-success mt-4'><h4><i class='fas fa-check-circle me-2'></i>All Done!</h4><p>The database has been repaired. Your articles, users, and ads should now have unique IDs and you can manage them individually.</p></div>";
            }
            ?>

            <form method="POST">
                <div class="d-grid">
                    <button type="submit" name="run_repair" class="btn btn-danger btn-lg" onclick="return confirm('Did you backup your database? Click OK to start the repair.')">
                        <i class="fas fa-play me-2"></i>Run Database Repair Now
                    </button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center">
            <a href="dashboard.php" class="btn btn-link text-secondary">Return to Admin Dashboard</a>
        </div>
    </div>
</div>

<?php 
if (file_exists('includes/admin-footer.php')) {
    include 'includes/admin-footer.php';
} elseif (file_exists('../includes/admin-footer.php')) {
    include '../includes/admin-footer.php';
}
?>
