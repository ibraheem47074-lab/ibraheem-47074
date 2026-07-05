<?php
// PK Live News - Admin Account Recovery Tool
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin Account Recovery - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .recovery-container { max-width: 800px; margin: 0 auto; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .table th { background: #007bff; color: white; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        .alert { margin: 20px 0; }
        .password-field { font-family: monospace; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='recovery-container'>
        <div class='card'>
            <div class='card-header bg-warning text-dark'>
                <h3 class='mb-0'>🔑 Admin Account Recovery</h3>
            </div>
            <div class='card-body'>";

// View existing admin accounts
$view_accounts = isset($_POST['view_accounts']);
$update_password = isset($_POST['update_password']);
$create_new = isset($_POST['create_new']);

if ($view_accounts) {
    echo "<h4>📋 Existing Admin Accounts</h4>";
    
    $query = "SELECT id, name, email, role, status, created_at, last_login FROM users WHERE role IN ('admin', 'editor') ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<div class='table-responsive'>
            <table class='table table-striped'>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";
        
        while ($user = mysqli_fetch_assoc($result)) {
            $status_badge = $user['status'] === 'active' ? '<span class=\"badge bg-success\">Active</span>' : '<span class=\"badge bg-danger\">Inactive</span>';
            $role_badge = $user['role'] === 'admin' ? '<span class=\"badge bg-primary\">Admin</span>' : '<span class=\"badge bg-info\">Editor</span>';
            
            echo "<tr>
                <td>{$user['id']}</td>
                <td>" . htmlspecialchars($user['name']) . "</td>
                <td>" . htmlspecialchars($user['email']) . "</td>
                <td>$role_badge</td>
                <td>$status_badge</td>
                <td>" . date('M d, Y', strtotime($user['created_at'])) . "</td>
                <td>" . ($user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never') . "</td>
                <td>
                    <button class='btn btn-sm btn-primary' onclick='resetPassword({$user['id']}, \"" . htmlspecialchars($user['email']) . "\")'>Reset Password</button>
                </td>
            </tr>";
        }
        
        echo "</tbody>
            </table>
        </div>";
    } else {
        echo "<div class='alert alert-info'>No admin accounts found. You need to create one first.</div>";
    }
    
    echo "<br><a href='admin_recovery.php' class='btn btn-secondary'>← Back to Recovery Options</a>";
    
} elseif ($update_password) {
    $user_id = (int)$_POST['user_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        echo "<div class='alert alert-danger'>Please fill in all password fields</div>";
    } elseif ($new_password !== $confirm_password) {
        echo "<div class='alert alert-danger'>Passwords do not match</div>";
    } elseif (strlen($new_password) < 6) {
        echo "<div class='alert alert-danger'>Password must be at least 6 characters long</div>";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'si', $hashed_password, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            echo "<div class='alert alert-success'>
                <h5>✅ Password Updated Successfully!</h5>
                <p>User ID: $user_id</p>
                <p>New password has been set.</p>
                <hr>
                <h6>🔗 Login Links:</h6>
                <p><strong>Admin Login:</strong> <a href='admin/login.php' target='_blank'>admin/login.php</a></p>
                <p><strong>Main Website:</strong> <a href='index.php' target='_blank'>index.php</a></p>
            </div>";
        } else {
            echo "<div class='alert alert-danger'>Error updating password: " . mysqli_error($conn) . "</div>";
        }
    }
    
    echo "<br><a href='admin_recovery.php' class='btn btn-secondary'>← Back to Recovery</a>";
    
} elseif ($create_new) {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $role = clean_input($_POST['role']);
    
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        echo "<div class='alert alert-danger'>Please fill in all fields</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='alert alert-danger'>Please enter a valid email address</div>";
    } elseif (strlen($password) < 6) {
        echo "<div class='alert alert-danger'>Password must be at least 6 characters long</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'ssss', $name, $email, $hashed_password, $role);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            echo "<div class='alert alert-success'>
                <h5>✅ Admin Account Created Successfully!</h5>
                <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Role:</strong> " . htmlspecialchars($role) . "</p>
                <p><strong>Password:</strong> [Hidden for security]</p>
                <hr>
                <h6>🔗 Login Information:</h6>
                <p><strong>Admin Login:</strong> <a href='admin/login.php' target='_blank'>admin/login.php</a></p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Password:</strong> (What you set above)</p>
            </div>";
        } else {
            echo "<div class='alert alert-danger'>Error creating account: " . mysqli_error($conn) . "</div>";
        }
    }
    
    echo "<br><a href='admin_recovery.php' class='btn btn-secondary'>← Back to Recovery</a>";
    
} else {
    // Main recovery options
    echo "<div class='row'>
        <div class='col-md-6 mb-3'>
            <div class='card h-100'>
                <div class='card-header bg-info text-white'>
                    <h5 class='mb-0'>👥 View Existing Accounts</h5>
                </div>
                <div class='card-body'>
                    <p>View all admin and editor accounts with their details.</p>
                    <form method='post'>
                        <input type='hidden' name='view_accounts' value='1'>
                        <button type='submit' class='btn btn-info w-100'>View Accounts</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class='col-md-6 mb-3'>
            <div class='card h-100'>
                <div class='card-header bg-success text-white'>
                    <h5 class='mb-0'>➕ Create New Admin</h5>
                </div>
                <div class='card-body'>
                    <p>Create a new admin account quickly.</p>
                    <form method='post'>
                        <input type='hidden' name='create_new' value='1'>
                        <div class='mb-2'>
                            <label class='form-label'>Name</label>
                            <input type='text' class='form-control' name='name' value='Admin' required>
                        </div>
                        <div class='mb-2'>
                            <label class='form-label'>Email</label>
                            <input type='email' class='form-control' name='email' value='ibraheem@pk-news.com' required>
                        </div>
                        <div class='mb-2'>
                            <label class='form-label'>Password</label>
                            <input type='password' class='form-control' name='password' required minlength='6'>
                        </div>
                        <div class='mb-2'>
                            <label class='form-label'>Role</label>
                            <select class='form-select' name='role' required>
                                <option value='admin'>Admin</option>
                                <option value='editor'>Editor</option>
                                <option value='reporter'>Reporter</option>
                            </select>
                        </div>
                        <button type='submit' class='btn btn-success w-100'>Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <hr>
    
    <div class='card'>
        <div class='card-header bg-primary text-white'>
            <h5 class='mb-0'>🔧 Quick Links</h5>
        </div>
        <div class='card-body'>
            <div class='row'>
                <div class='col-md-6'>
                    <h6>Admin Access</h6>
                    <ul class='list-unstyled'>
                        <li><a href='admin/login.php' target='_blank' class='btn btn-outline-primary btn-sm mb-2'>🔐 Admin Login</a></li>
                        <li><a href='setup_admin.php' target='_blank' class='btn btn-outline-secondary btn-sm mb-2'>⚙️ Setup Admin</a></li>
                    </ul>
                </div>
                <div class='col-md-6'>
                    <h6>Website Access</h6>
                    <ul class='list-unstyled'>
                        <li><a href='index.php' target='_blank' class='btn btn-outline-info btn-sm mb-2'>🌐 Main Website</a></li>
                        <li><a href='env_test.php' target='_blank' class='btn btn-outline-warning btn-sm mb-2'>🔍 Test Environment</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>";
}

echo "
            </div>
        </div>
    </div>
    
    <script>
        function resetPassword(userId, email) {
            const newPassword = prompt('Enter new password for ' + email + ':');
            const confirmPassword = prompt('Confirm new password:');
            
            if (newPassword && confirmPassword) {
                if (newPassword !== confirmPassword) {
                    alert('Passwords do not match!');
                    return;
                }
                
                if (newPassword.length < 6) {
                    alert('Password must be at least 6 characters long!');
                    return;
                }
                
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = `
                    <input type='hidden' name='update_password' value='1'>
                    <input type='hidden' name='user_id' value='\${userId}'>
                    <input type='hidden' name='new_password' value='\${newPassword}'>
                    <input type='hidden' name='confirm_password' value='\${confirmPassword}'>
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
