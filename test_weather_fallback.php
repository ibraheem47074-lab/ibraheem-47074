<?php
// Test Weather Fallback API
require_once 'config/weather.php';

echo "<h1>Weather Fallback API Test</h1>";

// Test 1: Get weather data for Islamabad (should use Open-Meteo fallback)
echo "<h2>Test 1: Weather Data for Islamabad (Fallback API)</h2>";
$weatherData = getWeatherData('Islamabad', 'metric');

if ($weatherData) {
    $formatted = formatWeatherData($weatherData);
    echo "<h3>Success!</h3>";
    echo "<pre>" . print_r($formatted, true) . "</pre>";
} else {
    echo "<p style='color: red;'>Failed to get weather data for Islamabad</p>";
}

// Test 2: Get forecast data for Islamabad
echo "<h2>Test 2: Forecast Data for Islamabad (Fallback API)</h2>";
$forecastData = getWeatherForecast('Islamabad', 'metric');

if ($forecastData) {
    echo "<h3>Success!</h3>";
    
    // Test hourly data
    echo "<h4>Hourly Forecast (Next 8 hours):</h4>";
    if (isset($forecastData['list'])) {
        echo "<pre>";
        foreach (array_slice($forecastData['list'], 0, 8) as $hour) {
            echo date('H:i', $hour['dt']) . " - " . $hour['weather'][0]['description'] . 
                 " (" . round($hour['main']['temp']) . "°C)\n";
        }
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>No hourly data available</p>";
    }
    
    // Test daily data
    echo "<h4>Daily Forecast (Next 5 days):</h4>";
    if (isset($forecastData['daily']) && !empty($forecastData['daily'])) {
        echo "<pre>";
        foreach ($forecastData['daily'] as $day) {
            echo date('Y-m-d', $day['dt']) . " - " . $day['weather'][0]['description'] . 
                 " (Min: " . round($day['temp']['min']) . "°C, Max: " . round($day['temp']['max']) . "°C)\n";
        }
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>No daily data available</p>";
    }
    
    // Test formatForecastData function
    echo "<h4>Formatted Daily Forecast:</h4>";
    $formattedDaily = formatForecastData($forecastData);
    echo "<pre>";
    foreach ($formattedDaily as $day) {
        if (isset($day['temp']['min'])) {
            echo date('Y-m-d', $day['dt']) . " - " . $day['weather'][0]['description'] . 
                 " (Min: " . round($day['temp']['min']) . "°C, Max: " . round($day['temp']['max']) . "°C)\n";
        } else {
            echo date('Y-m-d', $day['dt']) . " - " . $day['weather'][0]['description'] . 
                 " (" . round($day['main']['temp']) . "°C)\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color: red;'>Failed to get forecast data for Islamabad</p>";
}

// Test 3: Get weather data for Karachi
echo "<h2>Test 3: Weather Data for Karachi (Fallback API)</h2>";
$karachiWeather = getWeatherData('Karachi', 'metric');

if ($karachiWeather) {
    $formatted = formatWeatherData($karachiWeather);
    echo "<h3>Success!</h3>";
    echo "<ul>";
    echo "<li>City: " . $formatted['city'] . ", " . $formatted['country'] . "</li>";
    echo "<li>Temperature: " . formatTemperature($formatted['temperature'], 'metric') . "</li>";
    echo "<li>Description: " . $formatted['description'] . "</li>";
    echo "<li>Humidity: " . $formatted['humidity'] . "%</li>";
    echo "<li>Wind: " . $formatted['wind_speed'] . " m/s</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>Failed to get weather data for Karachi</p>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If all tests passed, the fallback API is working correctly.</p>";
