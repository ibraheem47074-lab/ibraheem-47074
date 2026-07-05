<?php
require_once 'config/database.php';
require_once 'config/weather.php';
require_once 'includes/language_functions.php';

$page_title = 'Weather - PK Live News';
$current_lang = get_current_language();

$weatherData = null;
$forecastData = null;
$error = '';
$city = isset($_GET['city']) ? clean_input($_GET['city']) : 'Islamabad';
$units = isset($_GET['units']) ? clean_input($_GET['units']) : 'metric';

if (!empty($city)) {
    // Check if API key is configured
    if (!isApiKeyConfigured()) {
        $error = 'Weather service is not configured. Please contact administrator to set up weather API key.';
    } else {
        $weatherData = getWeatherData($city, $units);
        if ($weatherData) {
            $weatherData = formatWeatherData($weatherData);
            $forecastData = getWeatherForecast($city, $units);
        } else {
            $error = 'Weather data not found for "' . htmlspecialchars($city) . '". Please check the city name and try again.';
        }
    }
}

// Get default cities for quick access
$defaultCities = getDefaultWeatherCities();
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <!-- Weather Search Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h1 class="card-title h3 mb-4">
                        <i class="fas fa-cloud-sun text-primary me-2"></i>
                        Weather Information
                    </h1>
                    
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="city" class="form-label">City Name</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($city); ?>" 
                                   placeholder="Enter city name..." required>
                        </div>
                        <div class="col-md-3">
                            <label for="units" class="form-label">Units</label>
                            <select class="form-select" id="units" name="units">
                                <option value="metric" <?php echo $units === 'metric' ? 'selected' : ''; ?>>
                                    Celsius (°C)
                                </option>
                                <option value="imperial" <?php echo $units === 'imperial' ? 'selected' : ''; ?>>
                                    Fahrenheit (°F)
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Get Weather
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="button" id="location-btn" class="btn btn-info" onclick="getLocationWeather()">
                                <i class="fas fa-location-crosshairs me-2"></i>Use My Location
                            </button>
                        </div>
                    </form>
                    
                    <!-- Quick Access Cities -->
                    <div class="mt-3">
                        <small class="text-muted">Quick access:</small>
                        <div class="mt-2">
                            <?php foreach ($defaultCities as $cityName => $country): ?>
                                <a href="?city=<?php echo urlencode($cityName); ?>&units=<?php echo $units; ?>" 
                                   class="btn btn-sm btn-outline-secondary me-2 mb-2">
                                    <?php echo htmlspecialchars($cityName); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!isApiKeyConfigured()): ?>
        <div class="alert alert-warning" role="alert">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Weather Service Setup Required</h5>
            <p class="mb-3">The weather feature requires an API key to function properly.</p>
            <div class="row">
                <div class="col-md-6">
                    <h6>Quick Setup:</h6>
                    <ol>
                        <li>Get a free API key from <a href="https://openweathermap.org/api" target="_blank">OpenWeatherMap</a></li>
                        <li>Edit the file <code>config/weather.php</code></li>
                        <li>Replace <code>YOUR_OPENWEATHERMAP_API_KEY</code> with your actual API key</li>
                        <li>Refresh this page</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6>Alternative:</h6>
                    <p>Run the test script to diagnose issues:</p>
                    <a href="test_weather.php" class="btn btn-primary">
                        <i class="fas fa-vial me-1"></i>Test Weather System
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($weatherData): ?>
        <!-- Current Weather Display -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6 text-center">
                                <div class="mb-3">
                                    <?php echo getWeatherIcon($weatherData['icon'], 'large'); ?>
                                </div>
                                <h2 class="display-4 fw-bold">
                                    <?php echo formatTemperature($weatherData['temperature'], $units); ?>
                                </h2>
                                <p class="lead text-muted">
                                    Feels like <?php echo formatTemperature($weatherData['feels_like'], $units); ?>
                                </p>
                                <p class="h5 text-capitalize">
                                    <?php echo htmlspecialchars($weatherData['description']); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h4 class="mb-3">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <?php echo htmlspecialchars($weatherData['city']); ?>, 
                                    <?php echo htmlspecialchars($weatherData['country']); ?>
                                </h4>
                                
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <small class="text-muted">Humidity</small>
                                        <p class="mb-0 fw-bold">
                                            <i class="fas fa-tint text-info me-1"></i>
                                            <?php echo $weatherData['humidity']; ?>%
                                        </p>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <small class="text-muted">Wind Speed</small>
                                        <p class="mb-0 fw-bold">
                                            <i class="fas fa-wind text-primary me-1"></i>
                                            <?php echo $weatherData['wind_speed']; ?> 
                                            <?php echo $units === 'imperial' ? 'mph' : 'm/s'; ?>
                                        </p>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <small class="text-muted">Pressure</small>
                                        <p class="mb-0 fw-bold">
                                            <i class="fas fa-compress text-secondary me-1"></i>
                                            <?php echo $weatherData['pressure']; ?> hPa
                                        </p>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <small class="text-muted">Visibility</small>
                                        <p class="mb-0 fw-bold">
                                            <i class="fas fa-eye text-success me-1"></i>
                                            <?php echo $weatherData['visibility'] ? $weatherData['visibility'] . ' km' : 'N/A'; ?>
                                        </p>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <small class="text-muted">Sunrise</small>
                                        <p class="mb-0 fw-bold">
                                            <i class="fas fa-sun text-warning me-1"></i>
                                            <?php echo $weatherData['sunrise']; ?>
                                        </p>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <small class="text-muted">Sunset</small>
                                        <p class="mb-0 fw-bold">
                                            <i class="fas fa-moon text-info me-1"></i>
                                            <?php echo $weatherData['sunset']; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Weather News Integration -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-newspaper text-primary me-2"></i>
                            Weather-Related News
                        </h5>
                        <p class="text-muted small">
                            Stay updated with weather-related news and updates
                        </p>
                        <a href="index.php?search=weather" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-search me-1"></i>Search Weather News
                        </a>
                        
                        <hr>
                        
                        <h6 class="text-muted mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-home me-1"></i>Back to Home
                            </a>
                            <button onclick="shareWeather()" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-share-alt me-1"></i>Share Weather
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5-Day Forecast -->
        <?php if ($forecastData && isset($forecastData['list']) && !empty($forecastData['list'])): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-week text-primary me-2" id="forecast-icon"></i>
                                    <span id="forecast-title">24-Hour Forecast</span>
                                </h5>
                                <div class="forecast-toggle">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="daily-btn" onclick="showDailyForecast()">
                                        <i class="fas fa-calendar me-1"></i>Daily
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm active" id="hourly-btn" onclick="showHourlyForecast()">
                                        <i class="fas fa-clock me-1"></i>Hourly
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Daily Forecast (Hidden by default) -->
                            <div id="daily-forecast" style="display: none;">
                                <div class="row">
                                    <?php 
                                    $dailyForecasts = formatForecastData($forecastData);
                                    if (!empty($dailyForecasts)) {
                                        foreach ($dailyForecasts as $date => $forecast): 
                                            $summary = getDailyForecastSummary($forecastData, $date);
                                            // Skip if summary is null or invalid
                                            if (!$summary || !isset($summary['temp_avg'])) continue;
                                    ?>
                                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                                            <div class="text-center p-3 border rounded weather-forecast-item">
                                                <small class="text-muted d-block mb-2">
                                                    <?php echo date('D', strtotime($date)); ?>
                                                    <?php if ($summary['temp_min'] && $summary['temp_max']): ?>
                                                        <br><span class="badge bg-secondary"><?php echo round($summary['temp_min']); ?>°</span> - <span class="badge bg-secondary"><?php echo round($summary['temp_max']); ?>°</span>
                                                    <?php endif; ?>
                                                </small>
                                                <div class="mb-2">
                                                    <?php echo getWeatherIcon($summary['weather_icon'], 'small'); ?>
                                                </div>
                                                <p class="mb-0 fw-bold">
                                                    <?php echo formatTemperature($summary['temp_avg'], $units); ?>
                                                </p>
                                                <small class="text-muted">
                                                    <?php echo ucfirst($summary['weather_desc']); ?>
                                                </small>
                                                <?php if ($summary['pop'] > 30): ?>
                                                    <div class="mt-1">
                                                        <small class="text-info">
                                                            <i class="fas fa-tint me-1"></i><?php echo round($summary['pop']); ?>%
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php 
                                        endforeach; 
                                    } // End if (!empty($dailyForecasts))
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Hourly Forecast -->
                            <div id="hourly-forecast">
                                <div class="hourly-forecast-container">
                                    <div class="hourly-forecast-scroll">
                                        <?php 
                                        // Get next 24 hours of forecast data
                                        $hourlyForecasts = array_slice($forecastData['list'], 0, 8); // 8 intervals = 24 hours (3-hour intervals)
                                        foreach ($hourlyForecasts as $hourly): 
                                            $time = date('H:i', $hourly['dt']);
                                            $temp = round($hourly['main']['temp']);
                                            $icon = $hourly['weather'][0]['icon'];
                                            $desc = $hourly['weather'][0]['description'];
                                            $humidity = $hourly['main']['humidity'];
                                            $windSpeed = $hourly['wind']['speed'];
                                            $pop = isset($hourly['pop']) ? $hourly['pop'] * 100 : 0;
                                        ?>
                                            <div class="hourly-forecast-item">
                                                <div class="text-center">
                                                    <div class="hourly-time"><?php echo $time; ?></div>
                                                    <div class="hourly-icon">
                                                        <?php echo getWeatherIcon($icon, 'medium'); ?>
                                                    </div>
                                                    <div class="hourly-temp"><?php echo $temp; ?>°</div>
                                                    <div class="hourly-desc"><?php echo $desc; ?></div>
                                                    <div class="hourly-details">
                                                        <div class="hourly-detail-item humidity">
                                                            <i class="fas fa-tint"></i>
                                                            <span><?php echo $humidity; ?>%</span>
                                                        </div>
                                                        <div class="hourly-detail-item wind">
                                                            <i class="fas fa-wind"></i>
                                                            <span><?php echo $windSpeed; ?> <?php echo $units === 'imperial' ? 'mph' : 'm/s'; ?></span>
                                                        </div>
                                                        <?php if ($pop > 20): ?>
                                                        <div class="hourly-detail-item precipitation">
                                                            <i class="fas fa-cloud-rain"></i>
                                                            <span><?php echo round($pop); ?>%</span>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function shareWeather() {
    <?php if ($weatherData): ?>
        const weatherText = `Weather in <?php echo htmlspecialchars($weatherData['city']); ?>: <?php echo $weatherData['temperature']; ?>°<?php echo $units === 'metric' ? 'C' : 'F'; ?>, <?php echo htmlspecialchars($weatherData['description']); ?>`;
        const url = window.location.href;
        
        if (navigator.share) {
            navigator.share({
                title: 'Weather Information',
                text: weatherText,
                url: url
            });
        } else {
            // Fallback - copy to clipboard
            const textToCopy = `${weatherText} - ${url}`;
            navigator.clipboard.writeText(textToCopy).then(() => {
                alert('Weather information copied to clipboard!');
            });
        }
    <?php endif; ?>
}

// Toggle between daily and hourly forecast
function showDailyForecast() {
    const dailyForecast = document.getElementById('daily-forecast');
    const hourlyForecast = document.getElementById('hourly-forecast');
    const dailyBtn = document.getElementById('daily-btn');
    const hourlyBtn = document.getElementById('hourly-btn');
    const forecastIcon = document.getElementById('forecast-icon');
    const forecastTitle = document.getElementById('forecast-title');
    
    // Show daily, hide hourly
    dailyForecast.style.display = 'block';
    hourlyForecast.style.display = 'none';
    
    // Update buttons
    dailyBtn.classList.add('active');
    hourlyBtn.classList.remove('active');
    
    // Update title and icon
    forecastIcon.className = 'fas fa-clock text-primary me-2';
    forecastTitle.textContent = '24-Hour Forecast';
}

function showHourlyForecast() {
    const dailyForecast = document.getElementById('daily-forecast');
    const hourlyForecast = document.getElementById('hourly-forecast');
    const dailyBtn = document.getElementById('daily-btn');
    const hourlyBtn = document.getElementById('hourly-btn');
    const forecastIcon = document.getElementById('forecast-icon');
    const forecastTitle = document.getElementById('forecast-title');
    
    // Show hourly, hide daily
    dailyForecast.style.display = 'none';
    hourlyForecast.style.display = 'block';
    
    // Update buttons
    dailyBtn.classList.remove('active');
    hourlyBtn.classList.add('active');
    
    // Update title and icon
    forecastIcon.className = 'fas fa-clock text-primary me-2';
    forecastTitle.textContent = '24-Hour Forecast';
}

// Get user's location and show weather
function getLocationWeather() {
    if (navigator.geolocation) {
        const button = document.getElementById('location-btn');
        if (button) {
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Getting location...';
            button.disabled = true;
        }
        
        navigator.geolocation.getCurrentPosition(
            position => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                // Fetch weather for user's location
                fetch('api/weather_location.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `lat=${lat}&lon=${lon}&units=<?php echo $units; ?>`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateWeatherDisplay(data.weather);
                        if (button) {
                            button.innerHTML = '<i class="fas fa-location-crosshairs me-1"></i>Use My Location';
                            button.disabled = false;
                        }
                    } else {
                        alert('Could not get weather for your location: ' + data.error);
                        if (button) {
                            button.innerHTML = '<i class="fas fa-location-crosshairs me-1"></i>Use My Location';
                            button.disabled = false;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error getting weather for your location');
                    if (button) {
                        button.innerHTML = '<i class="fas fa-location-crosshairs me-1"></i>Use My Location';
                        button.disabled = false;
                    }
                });
            },
            error => {
                console.error('Geolocation error:', error);
                alert('Could not get your location. Please check your browser settings.');
                if (button) {
                    button.innerHTML = '<i class="fas fa-location-crosshairs me-1"></i>Use My Location';
                    button.disabled = false;
                }
            }
        );
    } else {
        alert('Geolocation is not supported by your browser');
    }
}

// Update weather display with new data
function updateWeatherDisplay(weatherData) {
    // Update current weather display
    const tempElement = document.querySelector('.weather-current-display .display-4');
    const cityElement = document.querySelector('.weather-current-display h5');
    const descElement = document.querySelector('.weather-current-display .lead');
    const iconElement = document.querySelector('.weather-current-display .weather-icon');
    
    if (tempElement) tempElement.textContent = weatherData.temperature + '°C';
    if (cityElement) cityElement.innerHTML = `<i class="fas fa-map-marker-alt text-danger me-1"></i>${weatherData.city}`;
    if (descElement) descElement.textContent = weatherData.description;
    if (iconElement) iconElement.innerHTML = getWeatherIconHTML(weatherData.icon);
    
    // Update URL to reflect new location
    const newUrl = `weather.php?city=${encodeURIComponent(weatherData.city)}&units=<?php echo $units; ?>`;
    window.history.pushState({}, '', newUrl);
    
    // Update form field
    const cityInput = document.getElementById('city');
    if (cityInput) cityInput.value = weatherData.city;
}

// Get weather icon HTML (same function as in PHP but for JavaScript)
function getWeatherIconHTML(iconCode) {
    const iconMap = {};
    iconMap['01d'] = '<i class="fas fa-sun weather-icon-large" style="color: #FFD700;"></i>';
    iconMap['01n'] = '<i class="fas fa-moon weather-icon-large" style="color: #4A5568;"></i>';
    iconMap['02d'] = '<i class="fas fa-cloud-sun weather-icon-large" style="color: #87CEEB;"></i>';
    iconMap['02n'] = '<i class="fas fa-cloud-moon weather-icon-large" style="color: #2D3748;"></i>';
    iconMap['03d'] = '<i class="fas fa-cloud weather-icon-large" style="color: #718096;"></i>';
    iconMap['03n'] = '<i class="fas fa-cloud weather-icon-large" style="color: #718096;"></i>';
    iconMap['04d'] = '<i class="fas fa-cloud weather-icon-large" style="color: #4A5568;"></i>';
    iconMap['04n'] = '<i class="fas fa-cloud weather-icon-large" style="color: #4A5568;"></i>';
    iconMap['09d'] = '<i class="fas fa-cloud-showers-heavy weather-icon-large" style="color: #3182CE;"></i>';
    iconMap['09n'] = '<i class="fas fa-cloud-showers-heavy weather-icon-large" style="color: #3182CE;"></i>';
    iconMap['10d'] = '<i class="fas fa-cloud-sun-rain weather-icon-large" style="color: #2B6CB0;"></i>';
    iconMap['10n'] = '<i class="fas fa-cloud-moon-rain weather-icon-large" style="color: #2B6CB0;"></i>';
    iconMap['11d'] = '<i class="fas fa-bolt weather-icon-large" style="color: #805AD5;"></i>';
    iconMap['11n'] = '<i class="fas fa-bolt weather-icon-large" style="color: #805AD5;"></i>';
    iconMap['13d'] = '<i class="fas fa-snowflake weather-icon-large" style="color: #E2E8F0;"></i>';
    iconMap['13n'] = '<i class="fas fa-snowflake weather-icon-large" style="color: #E2E8F0;"></i>';
    iconMap['50d'] = '<i class="fas fa-smog weather-icon-large" style="color: #A0AEC0;"></i>';
    iconMap['50n'] = '<i class="fas fa-smog weather-icon-large" style="color: #A0AEC0;"></i>';
    
    return iconMap[iconCode] || '<i class="fas fa-question weather-icon-large" style="color: #718096;"></i>';
}

// Auto-refresh weather every 30 minutes
setTimeout(() => {
    window.location.reload();
}, 30 * 60 * 1000);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add location button if not present
    const searchForm = document.querySelector('form[method="GET"]');
    if (searchForm && !document.getElementById('location-btn')) {
        const locationBtn = document.createElement('button');
        locationBtn.type = 'button';
        locationBtn.id = 'location-btn';
        locationBtn.className = 'btn btn-info mt-2';
        locationBtn.innerHTML = '<i class="fas fa-location-crosshairs me-1"></i>Use My Location';
        locationBtn.onclick = getLocationWeather;
        searchForm.appendChild(locationBtn);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
