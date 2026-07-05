<?php
// Simple weather API test
require_once 'config/weather.php';

echo "<h2>Weather API Status</h2>";

// Check if API key is configured
if (isApiKeyConfigured()) {
    echo "<p style='color: green;'>✅ API Key is configured</p>";
    
    // Test with Islamabad
    $weather = getWeatherData('Islamabad', 'metric');
    if ($weather) {
        echo "<p style='color: green;'>✅ API Connection successful!</p>";
        echo "<p>Weather in Islamabad: " . $weather['main']['temp'] . "°C, " . $weather['weather'][0]['description'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ API Connection failed</p>";
    }
} else {
    echo "<p style='color: red;'>❌ API Key not configured</p>";
}
?>
