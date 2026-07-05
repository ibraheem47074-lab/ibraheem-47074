<?php
require_once '../config/database.php';

echo "Testing authentication functions:<br>";
echo "is_logged_in(): " . (is_logged_in() ? 'true' : 'false') . "<br>";
echo "is_admin(): " . (is_admin() ? 'true' : 'false') . "<br>";
echo "is_editor(): " . (is_editor() ? 'true' : 'false') . "<br>";
echo "is_reporter(): " . (is_reporter() ? 'true' : 'false') . "<br>";

if (isset($_SESSION['user_id'])) {
    echo "Session user_id: " . $_SESSION['user_id'] . "<br>";
} else {
    echo "No user_id in session<br>";
}

if (isset($_SESSION['user_role'])) {
    echo "Session user_role: " . $_SESSION['user_role'] . "<br>";
} else {
    echo "No user_role in session<br>";
}

echo "<br><a href='login.php'>Go to Login</a>";
?>
