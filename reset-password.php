<?php
require_once 'config/database.php';

$error = '';
$success = '';
$token_valid = false;
$user_id = null;

// Validate token
if (isset($_GET['token'])) {
    $token = clean_input($_GET['token']);
    
    $query = "SELECT id, reset_token_expires FROM users WHERE reset_token = ? AND reset_token_expires > NOW() AND status = 'active'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        $token_valid = true;
        $user_id = $user['id'];
    } else {
        $error = 'Invalid or expired reset token. Please request a new password reset.';
    }
} else {
    $error = 'No reset token provided.';
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $update_query = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'si', $hashed_password, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success = 'Password reset successful! You can now login with your new password.';
            log_activity($user_id, 'password_reset_complete', 'Password successfully reset');
        } else {
            $error = 'Failed to reset password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PK Live News</title>
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
        .reset-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
            padding: 40px;
        }
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="text-center mb-4">
            <h3><i class="fas fa-newspaper me-2 text-primary"></i>PK Live News</h3>
            <p class="text-muted">Reset Password</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
            <div class="text-center">
                <a href="forgot-password.php" class="btn btn-outline-primary">
                    <i class="fas fa-redo me-2"></i>Request New Reset Link
                </a>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success; ?>
            </div>
            <div class="text-center">
                <a href="admin/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                </a>
            </div>
        <?php elseif ($token_valid): ?>
            <p class="text-muted mb-4">Enter your new password below.</p>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" 
                               required minlength="6" autofocus>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               required minlength="6">
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-reset">
                        <i class="fas fa-save me-2"></i>Reset Password
                    </button>
                    <a href="admin/login.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
