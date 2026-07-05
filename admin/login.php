<?php
require_once '../config/database.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('dashboard.php');
    } elseif (is_editor()) {
        redirect('editor-dashboard-enhanced.php');
    } elseif (is_reporter()) {
        redirect('reporter-dashboard-enhanced.php');
    } else {
        redirect('../index.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Check user credentials
        $query = "SELECT * FROM users WHERE email = ? AND status = 'active'";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to appropriate dashboard based on role
                if ($user['role'] === 'admin') {
                    redirect('dashboard.php');
                } elseif ($user['role'] === 'editor') {
                    redirect('editor-dashboard-enhanced.php');
                } elseif ($user['role'] === 'reporter') {
                    redirect('reporter-dashboard-enhanced.php');
                } else {
                    redirect('../index.php');
                }
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found or inactive';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-form {
            padding: 40px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h3><i class="fas fa-newspaper me-2"></i>PK Live News</h3>
            <p class="mb-0">Admin Panel</p>
        </div>
        
        <div class="login-form">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                            <i class="fas fa-eye" id="passwordToggle"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    Da Serf the Admin Dafara Dee...
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordToggle.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>
