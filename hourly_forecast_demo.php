<?php
/**
 * Hourly Forecast Demo
 * Demonstrates the new hourly weather data feature
 */

require_once 'config/weather.php';
require_once 'includes/language_functions.php';

$page_title = 'Hourly Weather Forecast Demo';
$current_lang = get_current_language();

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>{$page_title}</title>";
echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "    <link href='assets/css/weather.css' rel='stylesheet'>";
echo "</head>";
echo "<body class='bg-light'>";

echo "<div class='container mt-4'>";
echo "<h1 class='mb-4'><i class='fas fa-clock text-primary me-2'></i>Hourly Weather Forecast Demo</h1>";

// Get weather data for Islamabad
$city = 'Islamabad';
$units = 'metric';

if (isApiKeyConfigured()) {
    $weatherData = getWeatherData($city, $units);
    if ($weatherData) {
        $weatherData = formatWeatherData($weatherData);
        $forecastData = getWeatherForecast($city, $units);
        
        echo "<div class='alert alert-success'>";
        echo "<i class='fas fa-check-circle me-2'></i>";
        echo "<strong>✅ Weather data retrieved for {$weatherData['city']}</strong>";
        echo "</div>";
        
        if ($forecastData && isset($forecastData['list']) && !empty($forecastData['list'])) {
            echo "<div class='card border-0 shadow-sm mb-4'>";
            echo "<div class='card-body'>";
            echo "<h5 class='card-title mb-4'>";
            echo "<i class='fas fa-calendar-week text-primary me-2'></i>";
            echo "5-Day Forecast";
            echo "</h5>";
            
            // Show daily forecasts
            $dailyForecasts = formatForecastData($forecastData);
            if (!empty($dailyForecasts)) {
                echo "<div class='row'>";
                foreach ($dailyForecasts as $date => $forecast) {
                    $summary = getDailyForecastSummary($forecastData, $date);
                    if (!$summary || !isset($summary['temp_avg'])) continue;
                    
                    echo "<div class='col-md-2 col-sm-4 col-6 mb-3'>";
                    echo "<div class='text-center p-3 border rounded weather-forecast-item'>";
                    echo "<small class='text-muted d-block mb-2'>" . date('D', strtotime($date)) . "</small>";
                    echo "<div class='mb-2'>" . getWeatherIcon($summary['weather_icon'], 'small') . "</div>";
                    echo "<h6>" . round($summary['temp_avg']) . "°</h6>";
                    echo "<small class='text-muted'>" . ucfirst($summary['weather_desc']) . "</small>";
                    echo "</div>";
                    echo "</div>";
                }
                echo "</div>";
            }
            echo "</div>";
            echo "</div>";
            
            // Show hourly forecasts
            echo "<div class='card border-0 shadow-sm'>";
            echo "<div class='card-body'>";
            echo "<h5 class='card-title mb-4'>";
            echo "<i class='fas fa-clock text-primary me-2'></i>";
            echo "24-Hour Forecast (Next 8 intervals - 3 hours each)";
            echo "</h5>";
            
            echo "<div class='hourly-forecast-container'>";
            echo "<div class='hourly-forecast-scroll'>";
            
            // Get next 24 hours of forecast data
            $hourlyForecasts = array_slice($forecastData['list'], 0, 8);
            foreach ($hourlyForecasts as $hourly) {
                $time = date('H:i', $hourly['dt']);
                $temp = round($hourly['main']['temp']);
                $icon = $hourly['weather'][0]['icon'];
                $desc = $hourly['weather'][0]['description'];
                $humidity = $hourly['main']['humidity'];
                $windSpeed = $hourly['wind']['speed'];
                $pop = isset($hourly['pop']) ? $hourly['pop'] * 100 : 0;
                
                echo "<div class='hourly-forecast-item'>";
                echo "<div class='text-center'>";
                echo "<div class='hourly-time'>{$time}</div>";
                echo "<div class='hourly-icon'>" . getWeatherIcon($icon, 'medium') . "</div>";
                echo "<div class='hourly-temp'>{$temp}°</div>";
                echo "<div class='hourly-desc'>{$desc}</div>";
                echo "<div class='hourly-details'>";
                echo "<div class='hourly-detail-item humidity'>";
                echo "<i class='fas fa-tint'></i>";
                echo "<span>{$humidity}%</span>";
                echo "</div>";
                echo "<div class='hourly-detail-item wind'>";
                echo "<i class='fas fa-wind'></i>";
                echo "<span>{$windSpeed} m/s</span>";
                echo "</div>";
                if ($pop > 20) {
                    echo "<div class='hourly-detail-item precipitation'>";
                    echo "<i class='fas fa-cloud-rain'></i>";
                    echo "<span>" . round($pop) . "%</span>";
                    echo "</div>";
                }
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
            
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
            // Show forecast toggle demo
            echo "<div class='card border-0 shadow-sm'>";
            echo "<div class='card-body'>";
            echo "<h5 class='card-title mb-4'>";
            echo "<i class='fas fa-toggle-on text-primary me-2'></i>";
            echo "Interactive Toggle Demo";
            echo "</h5>";
            echo "<p class='text-muted'>The main weather page now includes a toggle to switch between Daily and Hourly forecasts!</p>";
            echo "<div class='forecast-toggle'>";
            echo "<button type='button' class='btn btn-outline-primary btn-sm active'>";
            echo "<i class='fas fa-calendar me-1'></i>Daily";
            echo "</button>";
            echo "<button type='button' class='btn btn-outline-primary btn-sm'>";
            echo "<i class='fas fa-clock me-1'></i>Hourly";
            echo "</button>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
        } else {
            echo "<div class='alert alert-warning'>";
            echo "<i class='fas fa-exclamation-triangle me-2'></i>";
            echo "No forecast data available";
            echo "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<i class='fas fa-times-circle me-2'></i>";
        echo "Failed to retrieve weather data";
        echo "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>";
    echo "<i class='fas fa-times-circle me-2'></i>";
    echo "Weather API not configured";
    echo "</div>";
}

echo "<div class='text-center mt-4'>";
echo "<a href='weather.php' class='btn btn-primary me-2'><i class='fas fa-cloud-sun me-1'></i>Main Weather Page</a>";
echo "<a href='index.php' class='btn btn-outline-primary me-2'><i class='fas fa-home me-1'></i>Homepage</a>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
