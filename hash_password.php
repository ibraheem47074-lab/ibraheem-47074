<?php
// Hash password for admin123
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);
echo $hashed;
?>
