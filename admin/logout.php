<?php
require_once '../config/database.php';

// Destroy all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to login page
redirect('login.php');
?>
