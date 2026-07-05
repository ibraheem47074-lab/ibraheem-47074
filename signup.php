<?php
require_once 'config/database.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = clean_input($_POST['phone']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'Email address already exists';
        } else {
            // Insert new user - only use fields that exist
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Check if phone column exists
            $phone_column_exists = false;
            $columns_query = "DESCRIBE users";
            $columns_result = mysqli_query($conn, $columns_query);
            while ($column = mysqli_fetch_assoc($columns_result)) {
                if ($column['Field'] === 'phone') {
                    $phone_column_exists = true;
                    break;
                }
            }
            
            if ($phone_column_exists) {
                $insert_query = "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')";
                $stmt = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $hashed_password, $phone);
            } else {
                $insert_query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
                $stmt = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hashed_password);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Account created successfully! You can now login.';
                // Clear form
                $name = $email = $phone = '';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$page_title = 'Sign Up';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-header bg-danger text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Join PK Live News</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                       placeholder="Enter your full name" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       placeholder="Enter your email" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                       placeholder="Enter your phone number">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter password (min 6 chars)" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="passwordToggle"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Confirm your password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye" id="confirmPasswordToggle"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-danger">Terms of Service</a> and <a href="#" class="text-danger">Privacy Policy</a>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0">Already have an account? <a href="login.php" class="text-danger">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleId = fieldId === 'password' ? 'passwordToggle' : 'confirmPasswordToggle';
    const passwordToggle = document.getElementById(toggleId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordToggle.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        passwordToggle.className = 'fas fa-eye';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
