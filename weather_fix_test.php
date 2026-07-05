<?php
/**
 * Test file to verify the division by zero fix is working
 */

require_once 'config/weather.php';

echo "<h2>Weather Division by Zero Fix Test</h2>";

// Test 1: Check if API is configured
echo "<h3>API Configuration</h3>";
if (isApiKeyConfigured()) {
    echo "<p style='color: green;'>✅ API Key is configured</p>";
    
    // Test 2: Get weather data
    echo "<h3>Weather Data Test</h3>";
    $weatherData = getWeatherData('Islamabad', 'metric');
    
    if ($weatherData) {
        echo "<p style='color: green;'>✅ Weather data retrieved successfully</p>";
        
        // Test 3: Get forecast data
        echo "<h3>Forecast Data Test</h3>";
        $forecastData = getWeatherForecast('Islamabad', 'metric');
        
        if ($forecastData && isset($forecastData['list'])) {
            echo "<p style='color: green;'>✅ Forecast data retrieved successfully</p>";
            
            // Test 4: Test the fixed function
            echo "<h3>getDailyForecastSummary Test</h3>";
            $dailyForecasts = formatForecastData($forecastData);
            
            if (!empty($dailyForecasts)) {
                echo "<p style='color: green;'>✅ Daily forecasts formatted successfully</p>";
                
                foreach ($dailyForecasts as $date => $forecast) {
                    echo "<h4>Testing date: $date</h4>";
                    $summary = getDailyForecastSummary($forecastData, $date);
                    
                    if ($summary) {
                        echo "<p style='color: green;'>✅ Summary generated successfully for $date</p>";
                        echo "<ul>";
                        echo "<li>Temp Min: " . ($summary['temp_min'] ?? 'N/A') . "</li>";
                        echo "<li>Temp Max: " . ($summary['temp_max'] ?? 'N/A') . "</li>";
                        echo "<li>Temp Avg: " . ($summary['temp_avg'] ?? 'N/A') . "</li>";
                        echo "<li>Humidity Avg: " . ($summary['humidity_avg'] ?? 'N/A') . "</li>";
                        echo "<li>Wind Speed: " . ($summary['wind_speed'] ?? 'N/A') . "</li>";
                        echo "</ul>";
                    } else {
                        echo "<p style='color: orange;'>⚠️ No summary data for $date (this is normal)</p>";
                    }
                }
            } else {
                echo "<p style='color: orange;'>⚠️ No daily forecasts available</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ No forecast data available</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to retrieve weather data</p>";
    }
} else {
    echo "<p style='color: red;'>❌ API Key not configured</p>";
}

echo "<hr>";
echo "<h3>Test Summary</h3>";
echo "<p>If you see this message without any 'Division by zero' errors, the fix is working!</p>";
echo "<a href='weather.php' class='btn btn-primary'>Go to Weather Page</a>";
?>
