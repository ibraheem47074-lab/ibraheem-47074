<?php
// Test icon accessibility
$iconPath = 'assets/images/icons/icon-144x144.png';
$fullPath = __DIR__ . '/' . $iconPath;

echo "<h2>Icon Accessibility Test</h2>";

echo "<p>Icon path: " . $iconPath . "</p>";
echo "<p>Full path: " . $fullPath . "</p>";

if (file_exists($fullPath)) {
    echo "<p style='color: green;'>✓ File exists</p>";
    
    $fileSize = filesize($fullPath);
    echo "<p>File size: " . $fileSize . " bytes</p>";
    
    // Check if it's a valid image
    $imageInfo = getimagesize($fullPath);
    if ($imageInfo) {
        echo "<p style='color: green;'>✓ Valid image detected</p>";
        echo "<p>Image type: " . $imageInfo['mime'] . "</p>";
        echo "<p>Dimensions: " . $imageInfo[0] . "x" . $imageInfo[1] . "</p>";
        
        // Test if web server can serve it
        echo "<p>Testing web access:</p>";
        echo "<img src='" . $iconPath . "' alt='Test icon' style='border: 1px solid #ccc;'>";
        echo "<br><a href='" . $iconPath . "' target='_blank'>Open icon in new tab</a>";
    } else {
        echo "<p style='color: red;'>✗ Not a valid image file</p>";
    }
} else {
    echo "<p style='color: red;'>✗ File does not exist</p>";
}

// Test URL encoding
echo "<h2>URL Encoding Test</h2>";
$encodedPath = rawurlencode($iconPath);
echo "<p>Original: " . $iconPath . "</p>";
echo "<p>Encoded: " . $encodedPath . "</p>";

?>
