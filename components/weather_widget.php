<?php
/**
 * Weather Widget Component
 * Can be included on any page to show current weather
 */

require_once '../config/weather.php';

// Get weather data for default city or specified city
$city = isset($_GET['widget_city']) ? clean_input($_GET['widget_city']) : 'Islamabad';
$units = isset($_GET['widget_units']) ? clean_input($_GET['widget_units']) : 'metric';
$showDetails = isset($_GET['show_details']) ? $_GET['show_details'] : true;

$weatherData = null;
$error = '';

// Validate inputs before processing
$validation = validateWeatherInputs(['city' => $city, 'units' => $units]);

if (!$validation['valid']) {
    $error = 'Invalid input: ' . implode(', ', $validation['errors']);
} elseif (isApiKeyConfigured()) {
    $city = $validation['sanitized']['city'] ?? 'Islamabad';
    $units = $validation['sanitized']['units'] ?? 'metric';

    $weatherData = getWeatherData($city, $units);
    if ($weatherData) {
        $weatherData = formatWeatherData($weatherData);
    } else {
        $error = 'Weather data not available';
    }
} else {
    $error = 'Weather service not configured';
}

if ($weatherData):
?>
<div class="weather-widget">
    <div class="card">
        <div class="card-body">
            <h3 class="mb-3">
                <i class="fas fa-cloud-sun me-2"></i>
                Weather in <?php echo htmlspecialchars($weatherData['city']); ?>
            </h3>
            
            <div class="text-center mb-3">
                <?php echo getWeatherIcon($weatherData['icon'], 'large'); ?>
            </div>
            
            <h4 class="text-center mb-2">
                <?php echo formatTemperature($weatherData['temperature'], $units); ?>
            </h4>
            
            <p class="text-center text-capitalize mb-3">
                <?php echo htmlspecialchars($weatherData['description']); ?>
            </p>
            
            <?php if ($showDetails): ?>
            <div class="weather-details-grid">
                <div class="weather-detail-item">
                    <small>Feels Like</small>
                    <p><?php echo formatTemperature($weatherData['feels_like'], $units); ?></p>
                </div>
                <div class="weather-detail-item">
                    <small>Humidity</small>
                    <p><?php echo $weatherData['humidity']; ?>%</p>
                </div>
                <div class="weather-detail-item">
                    <small>Wind</small>
                    <p><?php echo $weatherData['wind_speed']; ?> <?php echo $units === 'imperial' ? 'mph' : 'm/s'; ?></p>
                </div>
                <div class="weather-detail-item">
                    <small>Pressure</small>
                    <p><?php echo $weatherData['pressure']; ?> hPa</p>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="text-center mt-3">
                <a href="../weather.php?city=<?php echo urlencode($weatherData['city']); ?>&units=<?php echo $units; ?>" 
                   class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>View Details
                </a>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="weather-widget">
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-exclamation-triangle weather-icon-large mb-3" style="color: #ffc107;"></i>
            <h5>Weather Information</h5>
            <p class="text-muted"><?php echo $error; ?></p>
            <a href="../weather.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-cloud me-1"></i>Check Weather
            </a>
        </div>
    </div>
</div>
<?php endif; ?>
