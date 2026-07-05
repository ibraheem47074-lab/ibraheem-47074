<?php
// PK Live News - Admin Account Setup
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup Admin Account - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .setup-container { max-width: 600px; margin: 0 auto; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-primary { background: #007bff; border: none; }
        .alert { margin: 20px 0; }
    </style>
</head>
<body>
    <div class='setup-container'>
        <div class='card'>
            <div class='card-header bg-primary text-white'>
                <h3 class='mb-0'>🔐 Admin Account Setup</h3>
            </div>
            <div class='card-body'>";

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_email = clean_input($_POST['admin_email']);
    $admin_password = $_POST['admin_password'];
    $admin_name = clean_input($_POST['admin_name']);
    
    if (empty($admin_email) || empty($admin_password) || empty($admin_name)) {
        $message = "<div class='alert alert-danger'>Please fill in all fields</div>";
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger'>Please enter a valid email address</div>";
    } elseif (strlen($admin_password) < 6) {
        $message = "<div class='alert alert-danger'>Password must be at least 6 characters long</div>";
    } else {
        // Check if admin already exists
        $check_query = "SELECT id FROM users WHERE email = ? OR role = 'admin'";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 's', $admin_email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "<div class='alert alert-warning'>Admin account already exists. You can update existing admin or use different email.</div>";
        } else {
            // Create admin account
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'admin', 'active', NOW())";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, 'sss', $admin_name, $admin_email, $hashed_password);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $success = true;
                $message = "<div class='alert alert-success'>
                    <h5>✅ Admin Account Created Successfully!</h5>
                    <p><strong>Email:</strong> $admin_email</p>
                    <p><strong>Name:</strong> $admin_name</p>
                    <p><strong>Password:</strong> [Hidden for security]</p>
                    <hr>
                    <h6>📝 Login Information:</h6>
                    <p>URL: <a href='admin/login.php' target='_blank'>admin/login.php</a></p>
                    <p>Email: $admin_email</p>
                    <p>Password: (What you set above)</p>
                    <hr>
                    <p class='mb-0'><strong>⚠️ Important:</strong> Save these credentials securely and delete this file after setup.</p>
                </div>";
            } else {
                $message = "<div class='alert alert-danger'>Error creating admin account: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

if (!$success) {
    echo $message;
    
    echo "<form method='post' class='needs-validation' novalidate>
        <div class='mb-3'>
            <label for='admin_name' class='form-label'>Admin Name *</label>
            <input type='text' class='form-control' id='admin_name' name='admin_name' required 
                   value='" . (isset($_POST['admin_name']) ? htmlspecialchars($_POST['admin_name']) : 'Admin') . "'>
            <div class='form-text'>Enter the full name for the admin account</div>
        </div>
        
        <div class='mb-3'>
            <label for='admin_email' class='form-label'>Admin Email *</label>
            <input type='email' class='form-control' id='admin_email' name='admin_email' required 
                   value='" . (isset($_POST['admin_email']) ? htmlspecialchars($_POST['admin_email']) : 'ibraheem@pk-news.com') . "'>
            <div class='form-text'>This will be your login email</div>
        </div>
        
        <div class='mb-3'>
            <label for='admin_password' class='form-label'>Admin Password *</label>
            <input type='password' class='form-control' id='admin_password' name='admin_password' required minlength='6'>
            <div class='form-text'>Minimum 6 characters. Use a strong password.</div>
        </div>
        
        <div class='mb-3'>
            <label for='confirm_password' class='form-label'>Confirm Password *</label>
            <input type='password' class='form-control' id='confirm_password' name='confirm_password' required minlength='6'>
            <div class='form-text'>Re-enter your password to confirm</div>
        </div>
        
        <div class='d-grid gap-2'>
            <button type='submit' class='btn btn-primary btn-lg'>Create Admin Account</button>
        </div>
    </form>";
} else {
    echo $message;
    
    echo "<div class='text-center mt-4'>
        <a href='admin/login.php' class='btn btn-success btn-lg'>Go to Admin Login</a>
        <br><br>
        <a href='index.php' class='btn btn-outline-secondary'>Go to Website</a>
    </div>";
}

echo "
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    <script>
        // Password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('admin_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match. Please re-enter both passwords.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return false;
            }
        });
    </script>
</body>
</html>";
?>
