<?php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if user exists
        $query = "SELECT id, name, email FROM users WHERE email = ? AND status = 'active'";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $update_query = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'ssi', $token, $expires, $user['id']);
            
            if (mysqli_stmt_execute($update_stmt)) {
                // Build reset link
                $reset_link = SITE_URL . 'reset-password.php?token=' . $token;
                
                // For now, show the link (in production, send email)
                $success = "Password reset link generated. Click the link below to reset your password:<br><br>
                           <a href='{$reset_link}' class='btn btn-primary'>Reset Password</a><br><br>
                           <small class='text-muted'>Link expires in 1 hour</small>";
                
                // Log the reset attempt
                log_activity($user['id'], 'password_reset_requested', 'Password reset requested for: ' . $email);
            } else {
                $error = 'Failed to generate reset token. Please try again.';
            }
        } else {
            // Don't reveal if email exists (security)
            $success = "If an account exists with this email, you will receive a password reset link.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - PK Live News</title>
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
        .forgot-container {
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
    <div class="forgot-container">
        <div class="text-center mb-4">
            <h3><i class="fas fa-newspaper me-2 text-primary"></i>PK Live News</h3>
            <p class="text-muted">Forgot Password</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success; ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required autofocus>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-reset">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                    <a href="login.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
