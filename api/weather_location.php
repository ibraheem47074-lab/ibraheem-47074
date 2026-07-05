<?php
require_once '../config/weather.php';

header('Content-Type: application/json');

// Handle POST requests for location-based weather
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
    $lon = isset($_POST['lon']) ? floatval($_POST['lon']) : null;
    $units = isset($_POST['units']) ? $_POST['units'] : 'metric';
    
    if ($lat && $lon) {
        $weatherData = getWeatherByCoordinates($lat, $lon, $units);
        $forecastData = getWeatherForecastByCoordinates($lat, $lon, $units);
        
        if ($weatherData) {
            $response = [
                'success' => true,
                'weather' => formatWeatherData($weatherData),
                'forecast' => $forecastData
            ];
            echo json_encode($response);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Weather data not found for your location'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid coordinates provided'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?>
