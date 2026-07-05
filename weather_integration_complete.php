<?php
/**
 * Weather Integration Test and Demo
 * This file demonstrates all weather features working together
 */

require_once 'config/database.php';
require_once 'config/weather.php';
require_once 'includes/language_functions.php';

$page_title = 'Weather Integration Demo';
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
echo "<h1 class='mb-4'><i class='fas fa-cloud-sun text-primary me-2'></i>Weather Integration Complete</h1>";

// Test 1: API Configuration
echo "<div class='row mb-4'>";
echo "<div class='col-12'>";
echo "<div class='card'>";
echo "<div class='card-header bg-success text-white'>";
echo "<h5 class='mb-0'><i class='fas fa-check me-2'></i>API Configuration Test</h5>";
echo "</div>";
echo "<div class='card-body'>";

if (isApiKeyConfigured()) {
    echo "<div class='alert alert-success'>";
    echo "<i class='fas fa-check-circle me-2'></i>";
    echo "<strong>✅ API Key is configured and active</strong>";
    echo "</div>";
    
    // Test with Islamabad
    $testWeather = getWeatherData('Islamabad', 'metric');
    if ($testWeather) {
        $formattedWeather = formatWeatherData($testWeather);
        echo "<div class='alert alert-success'>";
        echo "<i class='fas fa-check-circle me-2'></i>";
        echo "<strong>✅ API Connection successful!</strong><br>";
        echo "Islamabad Weather: " . $formattedWeather['temperature'] . "°C, " . $formattedWeather['description'];
        echo "</div>";
    } else {
        echo "<div class='alert alert-warning'>";
        echo "<i class='fas fa-exclamation-triangle me-2'></i>";
        echo "<strong>⚠️ API Key configured but connection failed</strong>";
        echo "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>";
    echo "<i class='fas fa-times-circle me-2'></i>";
    echo "<strong>❌ API Key not configured</strong>";
    echo "</div>";
}

echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

// Test 2: Weather Widget Demo
echo "<div class='row mb-4'>";
echo "<div class='col-md-6'>";
echo "<div class='card'>";
echo "<div class='card-header bg-info text-white'>";
echo "<h5 class='mb-0'><i class='fas fa-box me-2'></i>Weather Widget Demo</h5>";
echo "</div>";
echo "<div class='card-body'>";

// Include weather widget
$_GET['widget_city'] = 'Karachi';
$_GET['widget_units'] = 'metric';
include 'components/weather_widget.php';

echo "</div>";
echo "</div>";
echo "</div>";

// Test 3: Multiple Cities
echo "<div class='col-md-6'>";
echo "<div class='card'>";
echo "<div class='card-header bg-primary text-white'>";
echo "<h5 class='mb-0'><i class='fas fa-globe me-2'></i>Multiple Cities Test</h5>";
echo "</div>";
echo "<div class='card-body'>";

$cities = ['Islamabad', 'Karachi', 'Lahore', 'Peshawar'];
foreach ($cities as $city) {
    $weather = getWeatherData($city, 'metric');
    if ($weather) {
        $formatted = formatWeatherData($weather);
        echo "<div class='d-flex justify-content-between align-items-center mb-2'>";
        echo "<span><i class='fas fa-map-marker-alt text-danger me-1'></i>{$city}</span>";
        echo "<span class='badge bg-primary'>{$formatted['temperature']}°C</span>";
        echo "</div>";
    } else {
        echo "<div class='d-flex justify-content-between align-items-center mb-2'>";
        echo "<span><i class='fas fa-map-marker-alt text-muted me-1'></i>{$city}</span>";
        echo "<span class='badge bg-secondary'>N/A</span>";
        echo "</div>";
    }
}

echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

// Test 4: Integration Points
echo "<div class='row mb-4'>";
echo "<div class='col-12'>";
echo "<div class='card'>";
echo "<div class='card-header bg-dark text-white'>";
echo "<h5 class='mb-0'><i class='fas fa-link me-2'></i>Integration Points</h5>";
echo "</div>";
echo "<div class='card-body'>";

echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<h6><i class='fas fa-check text-success me-2'></i>Completed Integrations:</h6>";
echo "<ul class='list-unstyled'>";
echo "<li><i class='fas fa-check text-success me-2'></i>Main Weather Page (weather.php)</li>";
echo "<li><i class='fas fa-check text-success me-2'></i>Homepage Sidebar Widget</li>";
echo "<li><i class='fas fa-check text-success me-2'></i>Navigation Menu Link</li>";
echo "<li><i class='fas fa-check text-success me-2'></i>Weather Widget Component</li>";
echo "<li><i class='fas fa-check text-success me-2'></i>API Configuration</li>";
echo "<li><i class='fas fa-check text-success me-2'></i>CSS Styling</li>";
echo "</ul>";
echo "</div>";

echo "<div class='col-md-6'>";
echo "<h6><i class='fas fa-tools text-warning me-2'></i>Available Components:</h6>";
echo "<ul class='list-unstyled'>";
echo "<li><i class='fas fa-code text-primary me-2'></i>weather.php - Main weather page</li>";
echo "<li><i class='fas fa-code text-primary me-2'></i>components/weather_widget.php - Widget</li>";
echo "<li><i class='fas fa-code text-primary me-2'></i>config/weather.php - API config</li>";
echo "<li><i class='fas fa-code text-primary me-2'></i>api/weather_location.php - GPS API</li>";
echo "<li><i class='fas fa-code text-primary me-2'></i>assets/css/weather.css - Styling</li>";
echo "</ul>";
echo "</div>";
echo "</div>";

echo "</div>";
echo "</div>";
echo "</div>";

// Test 5: Usage Examples
echo "<div class='row mb-4'>";
echo "<div class='col-12'>";
echo "<div class='card'>";
echo "<div class='card-header bg-secondary text-white'>";
echo "<h5 class='mb-0'><i class='fas fa-code me-2'></i>Usage Examples</h5>";
echo "</div>";
echo "<div class='card-body'>";

echo "<h6>Basic Widget:</h6>";
echo "<code class='d-block p-2 bg-light rounded'><?php include 'components/weather_widget.php'; ?></code>";

echo "<h6 class='mt-3'>Custom City:</h6>";
echo "<code class='d-block p-2 bg-light rounded'><?php \$_GET['widget_city'] = 'Karachi'; include 'components/weather_widget.php'; ?></code>";

echo "<h6 class='mt-3'>Fahrenheit Units:</h6>";
echo "<code class='d-block p-2 bg-light rounded'><?php \$_GET['widget_units'] = 'imperial'; include 'components/weather_widget.php'; ?></code>";

echo "</div>";
echo "</div>";
echo "</div>";

echo "</div>";

echo "<div class='text-center mt-4 mb-5'>";
echo "<a href='weather.php' class='btn btn-primary me-2'><i class='fas fa-cloud-sun me-1'></i>Main Weather Page</a>";
echo "<a href='index.php' class='btn btn-outline-primary me-2'><i class='fas fa-home me-1'></i>Homepage</a>";
echo "<a href='test_weather.php' class='btn btn-outline-secondary'><i class='fas fa-vial me-1'></i>Test Page</a>";
echo "</div>";

echo "</body>";
echo "</html>";
?>
