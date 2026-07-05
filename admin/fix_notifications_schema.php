<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

try {
    // Check if notifications table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
    
    if (mysqli_num_rows($table_check) > 0) {
        // Check if user_id column is NOT NULL
        $column_check = mysqli_query($conn, "SHOW COLUMNS FROM notifications LIKE 'user_id'");
        $column_info = mysqli_fetch_assoc($column_check);
        
        if ($column_info['Null'] === 'NO') {
            // Modify the column to allow NULL values
            $alter_query = "ALTER TABLE notifications MODIFY COLUMN user_id int(11) DEFAULT NULL";
            
            if (mysqli_query($conn, $alter_query)) {
                $success = "Database schema updated successfully! user_id column now allows NULL values for 'All Users' notifications.";
            } else {
                $error = "Error updating database schema: " . mysqli_error($conn);
            }
        } else {
            $success = "Database schema is already correct! user_id column allows NULL values.";
        }
    } else {
        $error = "Notifications table does not exist. Please run the installation first.";
    }
    
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Notifications Schema - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .fix-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Fix Notifications Schema</h1>
                        <small>PK Live News - Database Schema Update</small>
                    </div>
                    <div>
                        <a href="manage-notifications.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Notifications
                        </a>
                    </div>
                </div>

                <!-- Result -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card fix-card">
                            <div class="card-body p-5">
                                <?php if ($success): ?>
                                    <div class="text-center">
                                        <div class="feature-icon bg-success text-white mx-auto mb-4">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <h2 class="text-success mb-3">Schema Updated!</h2>
                                        <p class="lead mb-4"><?php echo $success; ?></p>
                                        
                                        <div class="alert alert-info">
                                            <h5><i class="fas fa-info-circle me-2"></i>What was fixed?</h5>
                                            <p class="mb-2">The <code>user_id</code> column in the <code>notifications</code> table was updated to allow NULL values.</p>
                                            <p class="mb-0">This enables sending notifications to "All Users" by setting user_id to NULL.</p>
                                        </div>
                                        
                                        <div class="d-flex gap-3 justify-content-center mt-4">
                                            <a href="manage-notifications.php" class="btn btn-primary btn-lg">
                                                <i class="fas fa-bell me-2"></i>Manage Notifications
                                            </a>
                                            <a href="admin-dashboard.php" class="btn btn-outline-primary btn-lg">
                                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center">
                                        <div class="feature-icon bg-danger text-white mx-auto mb-4">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <h2 class="text-danger mb-3">Schema Update Failed</h2>
                                        <p class="lead mb-4"><?php echo $error; ?></p>
                                        
                                        <div class="d-flex gap-3 justify-content-center mt-4">
                                            <a href="install_notifications_simple.php" class="btn btn-warning btn-lg">
                                                <i class="fas fa-database me-2"></i>Run Installation
                                            </a>
                                            <a href="admin-dashboard.php" class="btn btn-outline-primary btn-lg">
                                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
