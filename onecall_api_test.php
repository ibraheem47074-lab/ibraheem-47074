<?php
// One Call API 3.0 Test
// This file demonstrates how to use the One Call API functions

require_once 'config/weather.php';

echo "<h1>One Call API 3.0 Test</h1>";

// Test 1: Get complete One Call data for Islamabad
echo "<h2>Test 1: Complete One Call Data for Islamabad</h2>";
$oneCallData = getOneCallWeatherByCity('Islamabad');

if ($oneCallData) {
    $formattedData = formatOneCallWeatherData($oneCallData);
    echo "<h3>Success!</h3>";
    echo "<pre>" . print_r($formattedData, true) . "</pre>";
} else {
    echo "<p style='color: red;'>Failed to get One Call data for Islamabad</p>";
}

// Test 2: Get One Call data excluding current, minutely, hourly, daily (as per your URL example)
echo "<h2>Test 2: One Call Data with Exclusions (current,minutely,hourly,daily)</h2>";
$excludeData = getOneCallWeatherByCity('Karachi', ['current', 'minutely', 'hourly', 'daily']);

if ($excludeData) {
    echo "<h3>Success!</h3>";
    echo "<p>Only alerts and location data should be returned:</p>";
    echo "<pre>" . print_r($excludeData, true) . "</pre>";
} else {
    echo "<p style='color: red;'>Failed to get One Call data with exclusions for Karachi</p>";
}

// Test 3: Get One Call data with only daily forecast
echo "<h2>Test 3: One Call Data - Daily Forecast Only</h2>";
$dailyOnly = getOneCallWeatherByCity('Lahore', ['current', 'minutely', 'hourly']);

if ($dailyOnly) {
    $formattedDaily = formatOneCallWeatherData($dailyOnly);
    echo "<h3>Success!</h3>";
    echo "<p>Daily forecast for Lahore:</p>";
    if (isset($formattedDaily['daily'])) {
        echo "<pre>";
        foreach ($formattedDaily['daily'] as $day) {
            echo "Date: " . $day['date'] . " - " . $day['weather']['description'] . 
                 " (Min: " . $day['temperature']['min'] . "°C, Max: " . $day['temperature']['max'] . "°C)\n";
        }
        echo "</pre>";
    }
} else {
    echo "<p style='color: red;'>Failed to get daily forecast for Lahore</p>";
}

// Test 4: Get One Call data with only hourly forecast
echo "<h2>Test 4: One Call Data - Hourly Forecast Only</h2>";
$hourlyOnly = getOneCallWeatherByCity('Peshawar', ['current', 'minutely', 'daily']);

if ($hourlyOnly) {
    $formattedHourly = formatOneCallWeatherData($hourlyOnly);
    echo "<h3>Success!</h3>";
    echo "<p>Next 6 hours forecast for Peshawar:</p>";
    if (isset($formattedHourly['hourly'])) {
        echo "<pre>";
        foreach (array_slice($formattedHourly['hourly'], 0, 6) as $hour) {
            echo $hour['datetime'] . " - " . $hour['weather']['description'] . 
                 " (" . $hour['temperature'] . "°C, POP: " . $hour['pop'] . "%)\n";
        }
        echo "</pre>";
    }
} else {
    echo "<p style='color: red;'>Failed to get hourly forecast for Peshawar</p>";
}

// Test 5: Get One Call data with specific coordinates
echo "<h2>Test 5: One Call Data for Specific Coordinates</h2>";
$coordsData = getOneCallWeatherData(33.6844, 73.0479, [], 'metric', 'en');

if ($coordsData) {
    echo "<h3>Success!</h3>";
    echo "<p>Current weather for Islamabad coordinates:</p>";
    if (isset($coordsData['current'])) {
        $current = $coordsData['current'];
        echo "<ul>";
        echo "<li>Temperature: " . $current['temp'] . "°C</li>";
        echo "<li>Feels like: " . $current['feels_like'] . "°C</li>";
        echo "<li>Humidity: " . $current['humidity'] . "%</li>";
        echo "<li>Weather: " . $current['weather'][0]['description'] . "</li>";
        echo "<li>UV Index: " . ($current['uvi'] ?? 'N/A') . "</li>";
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>Failed to get One Call data for coordinates</p>";
}

// Test 6: Error handling - invalid coordinates
echo "<h2>Test 6: Error Handling - Invalid Coordinates</h2>";
$invalidCoords = getOneCallWeatherData(91, 181);
if (!$invalidCoords) {
    echo "<p style='color: green;'>✓ Correctly handled invalid coordinates</p>";
} else {
    echo "<p style='color: red;'>✗ Should have failed with invalid coordinates</p>";
}

// Test 7: Error handling - invalid exclude parameters
echo "<h2>Test 7: Error Handling - Invalid Exclude Parameters</h2>";
$invalidExclude = getOneCallWeatherByCity('Islamabad', ['invalid', 'current', 'another_invalid']);
if ($invalidExclude) {
    echo "<p style='color: green;'>✓ Correctly filtered invalid exclude parameters</p>";
} else {
    echo "<p style='color: red;'>✗ Should have handled invalid exclude parameters</p>";
}

echo "<h2>Usage Examples</h2>";
echo "<h3>Basic Usage:</h3>";
echo "<pre>
// Get complete weather data
\$weather = getOneCallWeatherByCity('Islamabad');

// Get only daily forecast
\$daily = getOneCallWeatherByCity('Karachi', ['current', 'minutely', 'hourly']);

// Get only alerts (like your URL example)
\$alerts = getOneCallWeatherByCity('Lahore', ['current', 'minutely', 'hourly', 'daily']);

// Get data for specific coordinates
\$weather = getOneCallWeatherData(33.6844, 73.0479, [], 'metric', 'en');
</pre>";

echo "<h3>Formatted Data Usage:</h3>";
echo "<pre>
\$formatted = formatOneCallWeatherData(\$weather);

// Access current weather
echo \$formatted['current']['temperature']; // Current temp
echo \$formatted['current']['weather']['description']; // Weather description

// Access daily forecast
foreach (\$formatted['daily'] as \$day) {
    echo \$day['date'] . ': ' . \$day['temperature']['max'] . '°C';
}

// Access hourly forecast
foreach (\$formatted['hourly'] as \$hour) {
    echo \$hour['datetime'] . ': ' . \$hour['temperature'] . '°C';
}

// Access alerts
foreach (\$formatted['alerts'] as \$alert) {
    echo \$alert['event'] . ': ' . \$alert['description'];
}
</pre>";

echo "<h2>API Information</h2>";
echo "<p><strong>Base URL:</strong> " . WEATHER_ONECALL_URL . "</p>";
echo "<p><strong>Exclude Parameters:</strong> current, minutely, hourly, daily, alerts</p>";
echo "<p><strong>Units:</strong> metric (Celsius), imperial (Fahrenheit)</p>";
echo "<p><strong>Language:</strong> en, ur (Urdu), and many others</p>";

?>
