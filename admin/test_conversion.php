<?php
require_once 'config/database.php';
 
// Check current status
$result = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(CASE WHEN source_url IS NOT NULL AND source_url != '' THEN 1 ELSE 0 END) as external, SUM(CASE WHEN source_url IS NULL OR source_url = '' THEN 1 ELSE 0 END) as internal FROM news WHERE status = 'published'");
$row = mysqli_fetch_assoc($result);
 
echo "Current Published Articles Status:\n";
echo "Total Published: " . $row['total'] . "\n";
echo "External (with source_url): " . $row['external'] . "\n";
echo "Internal (no source_url): " . $row['internal'] . "\n\n";
 
// Check all articles
$result = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(CASE WHEN source_url IS NOT NULL AND source_url != '' THEN 1 ELSE 0 END) as external, SUM(CASE WHEN source_url IS NULL OR source_url = '' THEN 1 ELSE 0 END) as internal FROM news");
$row = mysqli_fetch_assoc($result);
 
echo "All Articles Status:\n";
echo "Total: " . $row['total'] . "\n";
echo "External (with source_url): " . $row['external'] . "\n";
echo "Internal (no source_url): " . $row['internal'] . "\n";