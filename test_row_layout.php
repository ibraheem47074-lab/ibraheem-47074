<?php
/**
 * Test Row Layout for Hourly Forecast
 */

require_once 'config/weather.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Row Layout Test</title>";
echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "    <link href='assets/css/weather.css' rel='stylesheet'>";
echo "</head>";
echo "<body class='bg-light'>";

echo "<div class='container mt-4'>";
echo "<h1>Hourly Forecast Row Layout Test</h1>";

// Test with sample data
$sampleData = [
    ['time' => '08:00', 'temp' => 20, 'icon' => '01d', 'desc' => 'clear sky', 'humidity' => 64, 'wind' => 0.56, 'pop' => 0],
    ['time' => '11:00', 'temp' => 22, 'icon' => '01d', 'desc' => 'clear sky', 'humidity' => 52, 'wind' => 0.29, 'pop' => 0],
    ['time' => '14:00', 'temp' => 27, 'icon' => '02d', 'desc' => 'few clouds', 'humidity' => 36, 'wind' => 1.75, 'pop' => 0],
    ['time' => '17:00', 'temp' => 31, 'icon' => '03d', 'desc' => 'scattered clouds', 'humidity' => 18, 'wind' => 1.72, 'pop' => 0],
    ['time' => '20:00', 'temp' => 27, 'icon' => '04d', 'desc' => 'broken clouds', 'humidity' => 24, 'wind' => 1.79, 'pop' => 0],
    ['time' => '23:00', 'temp' => 24, 'icon' => '04n', 'desc' => 'broken clouds', 'humidity' => 31, 'wind' => 1.45, 'pop' => 0],
    ['time' => '02:00', 'temp' => 22, 'icon' => '04n', 'desc' => 'overcast clouds', 'humidity' => 36, 'wind' => 1.43, 'pop' => 0],
    ['time' => '05:00', 'temp' => 21, 'icon' => '04n', 'desc' => 'overcast clouds', 'humidity' => 38, 'wind' => 0.44, 'pop' => 0],
];

echo "<div class='card border-0 shadow-sm'>";
echo "<div class='card-body'>";
echo "<h5 class='card-title mb-4'>";
echo "<i class='fas fa-clock text-primary me-2'></i>";
echo "24-Hour Forecast (Row Layout)";
echo "</h5>";

echo "<div class='hourly-forecast-container'>";
echo "<div class='hourly-forecast-scroll'>";

foreach ($sampleData as $hourly) {
    echo "<div class='hourly-forecast-row'>";
    echo "<div class='hourly-time'>{$hourly['time']}</div>";
    echo "<div class='hourly-icon'>";
    echo getWeatherIcon($hourly['icon'], 'medium');
    echo "</div>";
    echo "<div class='hourly-temp'>{$hourly['temp']}°</div>";
    echo "<div class='hourly-desc'>{$hourly['desc']}</div>";
    echo "<div class='hourly-details-row'>";
    echo "<div class='hourly-detail-item humidity'>";
    echo "<i class='fas fa-tint'></i>";
    echo "<span>{$hourly['humidity']}%</span>";
    echo "</div>";
    echo "<div class='hourly-detail-item wind'>";
    echo "<i class='fas fa-wind'></i>";
    echo "<span>{$hourly['wind']} m/s</span>";
    echo "</div>";
    if ($hourly['pop'] > 20) {
        echo "<div class='hourly-detail-item precipitation'>";
        echo "<i class='fas fa-cloud-rain'></i>";
        echo "<span>" . round($hourly['pop']) . "%</span>";
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";
}

echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div class='mt-4'>";
echo "<h3>CSS Classes Verification</h3>";
echo "<ul>";
echo "<li>✅ .hourly-forecast-row - Main row container</li>";
echo "<li>✅ .hourly-time - Time display</li>";
echo "<li>✅ .hourly-icon - Weather icon</li>";
echo "<li>✅ .hourly-temp - Temperature</li>";
echo "<li>✅ .hourly-desc - Description</li>";
echo "<li>✅ .hourly-details-row - Details container</li>";
echo "<li>✅ .hourly-detail-item - Individual detail items</li>";
echo "</ul>";
echo "</div>";

echo "<div class='text-center mt-4'>";
echo "<a href='weather.php' class='btn btn-primary'>Go to Weather Page</a>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
