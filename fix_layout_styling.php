<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Layout & Styling Issues Diagnostic</h1>";

// Check main CSS file
echo "<h2>CSS Files Status:</h2>";
$css_files = [
    'assets/css/style.css' => 'Main stylesheet',
    'assets/css/bootstrap.min.css' => 'Bootstrap CSS',
    'assets/css/fontawesome-all.css' => 'Font Awesome'
];

foreach ($css_files as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<p style='color: green;'>✓ $description ($file) - $size bytes</p>";
    } else {
        echo "<p style='color: red;'>✗ $description ($file) - MISSING</p>";
    }
}

// Check for common layout issues
echo "<h2>Common Layout Issues to Fix:</h2>";
echo "<ul>";
echo "<li>❌ News cards overlapping or misaligned</li>";
echo "<li>❌ Navigation menu dropdown positioning</li>";
echo "<li>❌ Sidebar widgets not properly aligned</li>";
echo "<li>❌ Product cards layout issues</li>";
echo "<li>❌ Weather widget positioning</li>";
echo "<li>❌ Responsive design problems on mobile</li>";
echo "</ul>";

echo "<h2>Solution:</h2>";
echo "<p>Click the button below to apply comprehensive layout and styling fixes:</p>";
echo "<a href='apply_layout_fixes.php' class='btn btn-primary btn-lg'>Apply Layout Fixes</a>";
echo "<br><br>";
echo "<a href='index.php' class='btn btn-secondary'>Back to Homepage</a>";
?>
