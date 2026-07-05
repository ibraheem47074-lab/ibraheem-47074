<?php
// Solar Irradiance API Test
// This file demonstrates how to use the solar irradiance functions

require_once 'config/weather.php';

// Test the solar irradiance API functions
echo "<h1>Solar Irradiance API Test</h1>";

// Test 1: Get solar irradiance data for Islamabad today
echo "<h2>Test 1: Solar Irradiance for Islamabad (Today)</h2>";
$today = date('Y-m-d');
$solarData = getSolarIrradianceByCity('Islamabad', $today, '1h');

if ($solarData) {
    echo "<h3>Success!</h3>";
    echo "<pre>" . print_r($solarData, true) . "</pre>";
} else {
    echo "<p style='color: red;'>Failed to get solar data for Islamabad</p>";
}

// Test 2: Get solar irradiance data for specific coordinates
echo "<h2>Test 2: Solar Irradiance for Karachi Coordinates</h2>";
$karachiLat = 24.8607;
$karachiLon = 67.0011;
$solarDataCoords = getSolarIrradianceData($karachiLat, $karachiLon, $today, '1h');

if ($solarDataCoords) {
    $formattedData = formatSolarIrradianceData($solarDataCoords);
    echo "<h3>Success!</h3>";
    echo "<pre>" . print_r($formattedData, true) . "</pre>";
} else {
    echo "<p style='color: red;'>Failed to get solar data for Karachi coordinates</p>";
}

// Test 3: Get solar irradiance data with 15-minute interval
echo "<h2>Test 3: Solar Irradiance for Lahore (15-minute intervals)</h2>";
$solarData15m = getSolarIrradianceByCity('Lahore', $today, '15m');

if ($solarData15m) {
    echo "<h3>Success!</h3>";
    echo "<pre>" . print_r($solarData15m, true) . "</pre>";
} else {
    echo "<p style='color: red;'>Failed to get 15-minute solar data for Lahore</p>";
}

// Test 4: Test with custom timezone
echo "<h2>Test 2: Solar Irradiance for Peshawar with Pakistan Timezone</h2>";
$solarDataTz = getSolarIrradianceByCity('Peshawar', $today, '1h', '+05:00');

if ($solarDataTz) {
    echo "<h3>Success!</h3>";
    echo "<pre>" . print_r($solarDataTz, true) . "</pre>";
} else {
    echo "<p style='color: red;'>Failed to get solar data for Peshawar with timezone</p>";
}

// Test 5: Error handling - invalid date
echo "<h2>Test 5: Error Handling - Invalid Date</h2>";
$invalidData = getSolarIrradianceByCity('Islamabad', '2025-13-45', '1h');
if (!$invalidData) {
    echo "<p style='color: green;'>✓ Correctly handled invalid date</p>";
} else {
    echo "<p style='color: red;'>✗ Should have failed with invalid date</p>";
}

// Test 6: Error handling - invalid coordinates
echo "<h2>Test 6: Error Handling - Invalid Coordinates</h2>";
$invalidCoords = getSolarIrradianceData(91, 181, $today, '1h');
if (!$invalidCoords) {
    echo "<p style='color: green;'>✓ Correctly handled invalid coordinates</p>";
} else {
    echo "<p style='color: red;'>✗ Should have failed with invalid coordinates</p>";
}

// Example usage in a real application
echo "<h2>Example Usage</h2>";
echo "<p>To use these functions in your application:</p>";
echo "<pre>
// Get solar irradiance for a city
\$solarData = getSolarIrradianceByCity('Islamabad', '2024-03-20', '1h');

// Get solar irradiance for specific coordinates
\$solarData = getSolarIrradianceData(33.6844, 73.0479, '2024-03-20', '1h');

// Format the data for display
\$formattedData = formatSolarIrradianceData(\$solarData);

// Access summary information
echo 'Total GHI (Clear Sky): ' . \$formattedData['summary']['total_ghi_clear'] . ' Wh/m²';
echo 'Peak Irradiance: ' . \$formattedData['summary']['peak_irradiance_clear'] . ' W/m²';
</pre>";

?>
