<?php
// Weather API Endpoint for PK Live News
// Provides RESTful API access to weather data

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include required files
require_once __DIR__ . '/../config/weather.php';

// Initialize response array
$response = [
    'success' => false,
    'data' => null,
    'error' => null,
    'timestamp' => date('Y-m-d H:i:s'),
    'api_version' => '1.0'
];

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetRequest();
            break;
        case 'POST':
            handlePostRequest();
            break;
        default:
            $response['error'] = 'Method not allowed';
            http_response_code(405);
            break;
    }
} catch (Exception $e) {
    $response['error'] = 'Internal server error: ' . $e->getMessage();
    http_response_code(500);
}

// Output JSON response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

/**
 * Handle GET requests
 */
function handleGetRequest() {
    global $response;
    
    $action = $_GET['action'] ?? 'current';
    
    switch ($action) {
        case 'current':
            getCurrentWeather();
            break;
        case 'forecast':
            getWeatherForecastAPI();
            break;
        case 'coordinates':
            getWeatherByCoordinatesAPI();
            break;
        case 'cities':
            getDefaultCitiesAPI();
            break;
        case 'status':
            getApiStatus();
            break;
        default:
            $response['error'] = 'Invalid action. Use: current, forecast, coordinates, cities, or status';
            http_response_code(400);
            break;
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest() {
    global $response;
    
    $action = $_POST['action'] ?? 'current';
    
    switch ($action) {
        case 'coordinates':
            getWeatherByCoordinatesPostAPI();
            break;
        case 'location':
            getUserLocationWeatherAPI();
            break;
        default:
            $response['error'] = 'Invalid action. Use: coordinates or location';
            http_response_code(400);
            break;
    }
}

/**
 * Get current weather for a city
 */
function getCurrentWeather() {
    global $response;
    
    $city = $_GET['city'] ?? 'Islamabad';
    $units = $_GET['units'] ?? 'metric';
    
    // Validate inputs
    if (empty($city)) {
        $response['error'] = 'City parameter is required';
        http_response_code(400);
        return;
    }
    
    // Check if API key is configured
    if (!isApiKeyConfigured()) {
        $response['error'] = 'Weather API key not configured';
        http_response_code(503);
        return;
    }
    
    // Get weather data
    $weatherData = getWeatherData($city, $units);
    
    if ($weatherData) {
        $formattedData = formatWeatherData($weatherData);
        $response['success'] = true;
        $response['data'] = [
            'current' => $formattedData,
            'source' => 'OpenWeatherMap',
            'units' => $units,
            'cache_info' => [
                'cached' => true,
                'cache_duration' => WEATHER_CACHE_DURATION
            ]
        ];
        http_response_code(200);
    } else {
        $response['error'] = 'Weather data not found for city: ' . htmlspecialchars($city);
        http_response_code(404);
    }
}

/**
 * Get weather forecast for a city
 */
function getWeatherForecastAPI() {
    global $response;
    
    $city = $_GET['city'] ?? 'Islamabad';
    $units = $_GET['units'] ?? 'metric';
    $days = isset($_GET['days']) ? min(5, max(1, intval($_GET['days']))) : 5;
    
    // Validate inputs
    if (empty($city)) {
        $response['error'] = 'City parameter is required';
        http_response_code(400);
        return;
    }
    
    // Check if API key is configured
    if (!isApiKeyConfigured()) {
        $response['error'] = 'Weather API key not configured';
        http_response_code(503);
        return;
    }
    
    // Get forecast data
    $forecastData = getWeatherForecast($city, $units);
    
    if ($forecastData) {
        $formattedForecast = formatForecastData($forecastData);
        $limitedForecast = array_slice($formattedForecast, 0, $days);
        
        $response['success'] = true;
        $response['data'] = [
            'forecast' => $limitedForecast,
            'city' => $city,
            'units' => $units,
            'days_requested' => $days,
            'days_returned' => count($limitedForecast),
            'source' => 'OpenWeatherMap'
        ];
        http_response_code(200);
    } else {
        $response['error'] = 'Forecast data not found for city: ' . htmlspecialchars($city);
        http_response_code(404);
    }
}

/**
 * Get weather by coordinates (GET)
 */
function getWeatherByCoordinatesAPI() {
    global $response;
    
    $lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
    $lon = isset($_GET['lon']) ? floatval($_GET['lon']) : null;
    $units = $_GET['units'] ?? 'metric';
    
    // Validate coordinates
    if ($lat === null || $lon === null) {
        $response['error'] = 'Latitude and longitude parameters are required';
        http_response_code(400);
        return;
    }
    
    // Check if API key is configured
    if (!isApiKeyConfigured()) {
        $response['error'] = 'Weather API key not configured';
        http_response_code(503);
        return;
    }
    
    // Get weather data
    $weatherData = getWeatherByCoordinates($lat, $lon, $units);
    
    if ($weatherData) {
        $formattedData = formatWeatherData($weatherData);
        $response['success'] = true;
        $response['data'] = [
            'current' => $formattedData,
            'coordinates' => ['lat' => $lat, 'lon' => $lon],
            'units' => $units,
            'source' => 'OpenWeatherMap'
        ];
        http_response_code(200);
    } else {
        $response['error'] = 'Weather data not found for coordinates: ' . $lat . ', ' . $lon;
        http_response_code(404);
    }
}

/**
 * Get weather by coordinates (POST)
 */
function getWeatherByCoordinatesPostAPI() {
    global $response;
    
    $lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
    $lon = isset($_POST['lon']) ? floatval($_POST['lon']) : null;
    $units = $_POST['units'] ?? 'metric';
    
    // Validate coordinates
    if ($lat === null || $lon === null) {
        $response['error'] = 'Latitude and longitude parameters are required';
        http_response_code(400);
        return;
    }
    
    // Check if API key is configured
    if (!isApiKeyConfigured()) {
        $response['error'] = 'Weather API key not configured';
        http_response_code(503);
        return;
    }
    
    // Get weather data
    $weatherData = getWeatherByCoordinates($lat, $lon, $units);
    
    if ($weatherData) {
        $formattedData = formatWeatherData($weatherData);
        $response['success'] = true;
        $response['data'] = [
            'current' => $formattedData,
            'coordinates' => ['lat' => $lat, 'lon' => $lon],
            'units' => $units,
            'source' => 'OpenWeatherMap'
        ];
        http_response_code(200);
    } else {
        $response['error'] = 'Weather data not found for coordinates: ' . $lat . ', ' . $lon;
        http_response_code(404);
    }
}

/**
 * Get user location weather (for geolocation API)
 */
function getUserLocationWeatherAPI() {
    global $response;
    
    $lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
    $lon = isset($_POST['lon']) ? floatval($_POST['lon']) : null;
    $units = $_POST['units'] ?? 'metric';
    
    // Validate coordinates
    if ($lat === null || $lon === null) {
        $response['error'] = 'Latitude and longitude parameters are required';
        http_response_code(400);
        return;
    }
    
    // Check if API key is configured
    if (!isApiKeyConfigured()) {
        $response['error'] = 'Weather API key not configured';
        http_response_code(503);
        return;
    }
    
    // Get weather data
    $weatherData = getWeatherByCoordinates($lat, $lon, $units);
    
    if ($weatherData) {
        $formattedData = formatWeatherData($weatherData);
        $response['success'] = true;
        $response['data'] = [
            'weather' => $formattedData,
            'coordinates' => ['lat' => $lat, 'lon' => $lon],
            'units' => $units,
            'source' => 'OpenWeatherMap'
        ];
        http_response_code(200);
    } else {
        $response['error'] = 'Weather data not found for your location';
        http_response_code(404);
    }
}

/**
 * Get default cities list
 */
function getDefaultCitiesAPI() {
    global $response;
    
    $cities = getDefaultWeatherCities();
    
    $response['success'] = true;
    $response['data'] = [
        'cities' => $cities,
        'count' => count($cities),
        'default_city' => 'Islamabad'
    ];
    http_response_code(200);
}

/**
 * Get API status
 */
function getApiStatus() {
    global $response;
    
    $status = [
        'api' => 'online',
        'api_key_configured' => isApiKeyConfigured(),
        'cache_enabled' => true,
        'cache_duration' => WEATHER_CACHE_DURATION,
        'supported_units' => ['metric', 'imperial'],
        'supported_actions' => ['current', 'forecast', 'coordinates', 'cities', 'status'],
        'version' => '1.0',
        'source' => 'OpenWeatherMap'
    ];
    
    // Test cache directory
    $cacheWritable = is_writable(dirname(WEATHER_CACHE_FILE));
    $status['cache_writable'] = $cacheWritable;
    
    $response['success'] = true;
    $response['data'] = $status;
    http_response_code(200);
}

/**
 * Format weather data for API response
 */
function formatWeatherDataForAPI($weatherData) {
    if (!$weatherData) {
        return null;
    }
    
    return [
        'city' => $weatherData['name'] ?? 'Unknown',
        'country' => $weatherData['sys']['country'] ?? 'Unknown',
        'temperature' => [
            'current' => round($weatherData['main']['temp']),
            'feels_like' => round($weatherData['main']['feels_like']),
            'min' => round($weatherData['main']['temp_min']),
            'max' => round($weatherData['main']['temp_max'])
        ],
        'weather' => [
            'main' => $weatherData['weather'][0]['main'] ?? 'Unknown',
            'description' => ucfirst($weatherData['weather'][0]['description'] ?? 'Unknown'),
            'icon' => $weatherData['weather'][0]['icon'] ?? '01d'
        ],
        'details' => [
            'humidity' => $weatherData['main']['humidity'] ?? 0,
            'pressure' => $weatherData['main']['pressure'] ?? 0,
            'wind_speed' => $weatherData['wind']['speed'] ?? 0,
            'wind_direction' => $weatherData['wind']['deg'] ?? 0,
            'visibility' => isset($weatherData['visibility']) ? $weatherData['visibility'] / 1000 : null
        ],
        'sun' => [
            'sunrise' => isset($weatherData['sys']['sunrise']) ? date('H:i', $weatherData['sys']['sunrise']) : null,
            'sunset' => isset($weatherData['sys']['sunset']) ? date('H:i', $weatherData['sys']['sunset']) : null
        ],
        'timestamp' => $weatherData['dt'] ?? time()
    ];
}
?>
