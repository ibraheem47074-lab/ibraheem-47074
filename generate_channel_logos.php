<?php
require_once 'config/database.php';

echo "<h2>Generating Channel Logos</h2>";

// Create channels directory
$channels_dir = 'uploads/channels/';
if (!file_exists($channels_dir)) {
    mkdir($channels_dir, 0755, true);
    echo "Created channels directory<br>";
}

// Get all channels from database
$channels_query = "SELECT name, category FROM channels ORDER BY name";
$channels_result = mysqli_query($conn, $channels_query);

$channel_colors = [
    'news' => ['#dc3545', '#ff6b6b'],
    'sports' => ['#28a745', '#5cb85c'],
    'entertainment' => ['#ffc107', '#ffdb4d'],
    'business' => ['#17a2b8', '#5bc0de'],
    'technology' => ['#6f42c1', '#9b59b6'],
    'international' => ['#fd7e14', '#ff922b']
];

while ($channel = mysqli_fetch_assoc($channels_result)) {
    $channel_name = $channel['name'];
    $category = $channel['category'];
    $logo_filename = $channels_dir . strtolower(str_replace(' ', '-', $channel_name)) . '-logo.png';
    
    if (!file_exists($logo_filename)) {
        // Create a simple logo using GD
        $width = 200;
        $height = 100;
        $image = imagecreatetruecolor($width, $height);
        
        // Get colors for this category
        $colors = isset($channel_colors[$category]) ? $channel_colors[$category] : ['#6c757d', '#adb5bd'];
        $primary_color = hexToRgb($colors[0]);
        $secondary_color = hexToRgb($colors[1]);
        
        // Create gradient background
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = $primary_color['r'] + ($secondary_color['r'] - $primary_color['r']) * $ratio;
            $g = $primary_color['g'] + ($secondary_color['g'] - $primary_color['g']) * $ratio;
            $b = $primary_color['b'] + ($secondary_color['b'] - $primary_color['b']) * $ratio;
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }
        
        // Add text
        $text_color = imagecolorallocate($image, 255, 255, 255);
        $font_size = 4; // Built-in font size
        $text = strtoupper($channel_name);
        
        // Calculate text position to center it
        $text_width = imagefontwidth($font_size) * strlen($text);
        $text_height = imagefontheight($font_size);
        $x = ($width - $text_width) / 2;
        $y = ($height - $text_height) / 2;
        
        // Add text with shadow
        $shadow_color = imagecolorallocate($image, 0, 0, 0);
        imagestring($image, $font_size, $x + 1, $y + 1, $text, $shadow_color);
        imagestring($image, $font_size, $x, $y, $text, $text_color);
        
        // Save the image
        imagepng($image, $logo_filename);
        imagedestroy($image);
        
        echo "✓ Generated logo for: " . htmlspecialchars($channel_name) . " (" . $category . ")<br>";
    } else {
        echo "ℹ Logo already exists: " . htmlspecialchars($channel_name) . "<br>";
    }
}

echo "<h3>Channel logos generation complete!</h3>";
echo "<p><a href='live.php'>Go to Live TV Page</a></p>";

function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return ['r' => $r, 'g' => $g, 'b' => $b];
}
?>
