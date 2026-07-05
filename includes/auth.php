<?php
// Authentication functions - moved to config/database.php
// This file is kept for backward compatibility
require_once '../config/database.php';

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
