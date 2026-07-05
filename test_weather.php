<?php
require_once 'config/weather.php';

echo "<h2>Weather System Test</h2>";

// Test 1: Check API Key
echo "<h3>1. API Key Check</h3>";
if (WEATHER_API_KEY === 'YOUR_OPENWEATHERMAP_API_KEY') {
    echo "<p style='color: red;'><strong>❌ API Key Not Set!</strong><br>";
    echo "Please edit config/weather.php and replace 'YOUR_OPENWEATHERMAP_API_KEY' with your actual OpenWeatherMap API key.</p>";
    echo "<p>Get your free API key from: <a href='https://openweathermap.org/api' target='_blank'>https://openweathermap.org/api</a></p>";
} else {
    echo "<p style='color: green;'><strong>✅ API Key is set</strong></p>";
}

// Test 2: Test API Connection
echo "<h3>2. API Connection Test</h3>";
if (WEATHER_API_KEY !== 'YOUR_OPENWEATHERMAP_API_KEY') {
    $testCity = 'Islamabad';
    $testUrl = WEATHER_API_URL . "?q=" . urlencode($testCity) . "&appid=" . WEATHER_API_KEY . "&units=metric";
    
    echo "<p>Testing URL: " . htmlspecialchars($testUrl) . "</p>";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PK Live News Weather Widget'
        ]
    ]);
    
    $response = @file_get_contents($testUrl, false, $context);
    
    if ($response === false) {
        echo "<p style='color: red;'><strong>❌ API Connection Failed!</strong><br>";
        echo "Possible reasons:<br>";
        echo "- Invalid API key<br>";
        echo "- Network connection issues<br>";
        echo "- API server down</p>";
    } else {
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<p style='color: red;'><strong>❌ Invalid JSON Response!</strong><br>";
            echo "Response: " . htmlspecialchars(substr($response, 0, 200)) . "...</p>";
        } elseif (isset($data['cod']) && $data['cod'] != 200) {
            echo "<p style='color: red;'><strong>❌ API Error!</strong><br>";
            echo "Error Code: " . $data['cod'] . "<br>";
            echo "Message: " . ($data['message'] ?? 'Unknown error') . "</p>";
        } else {
            echo "<p style='color: green;'><strong>✅ API Connection Successful!</strong><br>";
            echo "City: " . $data['name'] . "<br>";
            echo "Temperature: " . $data['main']['temp'] . "°C<br>";
            echo "Weather: " . $data['weather'][0]['description'] . "</p>";
        }
    }
}

// Test 3: Test Peshawar Specifically
echo "<h3>3. Peshawar Test</h3>";
if (WEATHER_API_KEY !== 'YOUR_OPENWEATHERMAP_API_KEY') {
    $peshawarData = getWeatherData('Peshawar', 'metric');
    
    if ($peshawarData) {
        echo "<p style='color: green;'><strong>✅ Peshawar Weather Found!</strong><br>";
        echo "Temperature: " . $peshawarData['main']['temp'] . "°C<br>";
        echo "Weather: " . $peshawarData['weather'][0]['description'] . "</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Peshawar Weather Not Found!</strong><br>";
        echo "Trying alternative spellings...</p>";
        
        // Try alternative spellings
        $alternatives = ['Peshawar', 'Peshawar, PK', 'Peshawar, Pakistan'];
        foreach ($alternatives as $city) {
            $testData = getWeatherData($city, 'metric');
            if ($testData) {
                echo "<p style='color: orange;'>✅ Found with: '$city'</p>";
                break;
            } else {
                echo "<p style='color: red;'>❌ Not found with: '$city'</p>";
            }
        }
    }
}

// Test 4: Cache Directory
echo "<h3>4. Cache Directory Check</h3>";
$cacheDir = dirname(WEATHER_CACHE_FILE);
if (!is_dir($cacheDir)) {
    echo "<p style='color: orange;'>⚠️ Cache directory doesn't exist. Creating...</p>";
    if (mkdir($cacheDir, 0755, true)) {
        echo "<p style='color: green;'>✅ Cache directory created</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create cache directory</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Cache directory exists</p>";
}

if (is_writable($cacheDir)) {
    echo "<p style='color: green;'>✅ Cache directory is writable</p>";
} else {
    echo "<p style='color: red;'>❌ Cache directory is not writable</p>";
}

// Test 5: Default Cities
echo "<h3>5. Default Cities Test</h3>";
$cities = getDefaultWeatherCities();
echo "<p>Testing default cities...</p>";
foreach ($cities as $city => $country) {
    $data = getWeatherData($city, 'metric');
    if ($data) {
        echo "<span style='color: green;'>✅ $city</span> | ";
    } else {
        echo "<span style='color: red;'>❌ $city</span> | ";
    }
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Get your free API key from <a href='https://openweathermap.org/api' target='_blank'>OpenWeatherMap</a></li>";
echo "<li>Edit config/weather.php</li>";
echo "<li>Replace 'YOUR_OPENWEATHERMAP_API_KEY' with your actual API key</li>";
echo "<li>Test again</li>";
echo "</ol>";
?>
