<?php
// Solar Irradiance API Configuration Setup
// This script helps you set up your OpenWeatherMap API key

require_once 'config/weather.php';

echo "<h1>Solar Irradiance API Configuration Setup</h1>";

// Check current API key status
echo "<h2>Current Configuration Status</h2>";

if (isApiKeyConfigured()) {
    echo "<p style='color: green;'>✓ API key is configured</p>";
    echo "<p>API Key: " . substr(WEATHER_API_KEY, 0, 8) . "..." . substr(WEATHER_API_KEY, -4) . "</p>";
} else {
    echo "<p style='color: red;'>✗ API key is not configured</p>";
    echo "<p>Current value: " . WEATHER_API_KEY . "</p>";
}

echo "<h2>How to Configure Your API Key</h2>";
echo "<ol>";
echo "<li>Sign up or log in to <a href='https://openweathermap.org/api' target='_blank'>OpenWeatherMap</a></li>";
echo "<li>Navigate to the 'API keys' tab in your account</li>";
echo "<li>Copy your API key</li>";
echo "<li>Update the WEATHER_API_KEY constant in config/weather.php</li>";
echo "</ol>";

echo "<h2>Important Notes for Solar Irradiance API</h2>";
echo "<ul>";
echo "<li style='color: orange;'><strong>Premium Service:</strong> The Solar Irradiance API is part of OpenWeatherMap's Energy API, which requires a paid subscription</li>";
echo "<li><strong>Free Tier Limitation:</strong> The free API tier does not include access to solar irradiance data</li>";
echo "<li><strong>Required Subscription:</strong> You need an 'Energy' or 'Professional' subscription to access this endpoint</li>";
echo "</ul>";

echo "<h2>API Configuration Example</h2>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo "// In config/weather.php, replace line 3:
define('WEATHER_API_KEY', 'YOUR_OPENWEATHERMAP_API_KEY');

// With your actual API key:
define('WEATHER_API_KEY', 'abcd1234efgh5678ijkl9012mnop3456');
</pre>";

echo "<h2>Test Your Configuration</h2>";
echo "<p>After configuring your API key, you can test it with this simple script:</p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo "<?php
require_once 'config/weather.php';

// Test basic weather API (free tier)
\$weatherData = getWeatherData('Islamabad');
if (\$weatherData) {
    echo '✓ Basic weather API working';
} else {
    echo '✗ Basic weather API failed';
}

// Test solar irradiance API (requires paid subscription)
\$solarData = getSolarIrradianceByCity('Islamabad', date('Y-m-d'), '1h');
if (\$solarData) {
    echo '✓ Solar irradiance API working';
} else {
    echo '✗ Solar irradiance API failed (may require paid subscription)';
}
?>
</pre>";

echo "<h2>Alternative Solutions</h2>";
echo "<p>If you don't have access to the paid Solar Irradiance API, consider these alternatives:</p>";
echo "<ul>";
echo "<li><strong>Free Weather Data:</strong> Use the regular weather API which provides basic sun position data</li>";
echo "<li><strong>Third-party APIs:</strong> Consider other solar data providers like PVWatts, Solcast, or NREL</li>";
echo "<li><strong>Mock Data:</strong> Use simulated solar data for development and testing</li>";
echo "</ul>";

// Show current API key validation
echo "<h2>Configuration Validation</h2>";
$apiKey = WEATHER_API_KEY;
echo "<p><strong>API Key Format Check:</strong></p>";
echo "<ul>";

if (strlen($apiKey) === 32) {
    echo "<li style='color: green;'>✓ Correct length (32 characters)</li>";
} else {
    echo "<li style='color: red;'>✗ Incorrect length. Expected 32 characters, got " . strlen($apiKey) . "</li>";
}

if (ctype_alnum($apiKey)) {
    echo "<li style='color: green;'>✓ Correct format (alphanumeric only)</li>";
} else {
    echo "<li style='color: red;'>✗ Invalid format. Should be alphanumeric only</li>";
}

if ($apiKey === 'YOUR_OPENWEATHERMAP_API_KEY') {
    echo "<li style='color: red;'>✗ Still using placeholder value</li>";
} else {
    echo "<li style='color: green;'>✓ Not using placeholder value</li>";
}
echo "</ul>";

echo "<h2>Next Steps</h2>";
echo "<p>1. Get your API key from OpenWeatherMap</p>";
echo "<p>2. Update the configuration file</p>";
echo "<p>3. Ensure you have a paid subscription for the Energy API</p>";
echo "<p>4. Test again with the solar_irradiance_test.php file</p>";

?>
