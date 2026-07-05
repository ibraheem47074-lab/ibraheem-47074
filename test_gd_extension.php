<?php
// Test script to verify GD extension is working
echo "<h2>GD Extension Test</h2>";

if (extension_loaded('gd')) {
    echo "<p style='color: green;'>GD extension is loaded successfully!</p>";
    
    // Show GD info
    if (function_exists('gd_info')) {
        $gd_info = gd_info();
        echo "<h3>GD Information:</h3>";
        echo "<ul>";
        foreach ($gd_info as $key => $value) {
            echo "<li><strong>$key:</strong> " . ($value ? 'Yes' : 'No') . "</li>";
        }
        echo "</ul>";
    }
    
    // Test creating a simple image
    try {
        $image = imagecreatetruecolor(100, 100);
        $bg_color = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $bg_color);
        imagestring($image, 5, 10, 40, "GD Test", $text_color);
        
        // Output the image
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error creating image: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>GD extension is NOT loaded!</p>";
    
    // Show all loaded extensions for debugging
    echo "<h3>Loaded Extensions:</h3>";
    $extensions = get_loaded_extensions();
    echo "<ul>";
    foreach ($extensions as $ext) {
        echo "<li>$ext</li>";
    }
    echo "</ul>";
}
?>
