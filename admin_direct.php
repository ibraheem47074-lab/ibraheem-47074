<?php
session_start();
require_once '../config/database.php';
require_once '../includes/sentiment_analysis.php';

// Check if user is logged in and is admin or editor
if (!is_logged_in() || (!is_admin() && !is_editor())) {
    redirect('login.php');
}

// Include the actual add-news.php content
include 'add-news.php';
?>
