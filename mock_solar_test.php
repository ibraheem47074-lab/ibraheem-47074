<?php
// Mock Solar Irradiance Data Generator
// This provides sample solar data for testing when the paid API is not available

require_once 'config/weather.php';

/**
 * Generate mock solar irradiance data for testing
 * @param float $lat Latitude
 * @param float $lon Longitude
 * @param string $date Date in YYYY-MM-DD format
 * @param string $interval Time interval
 * @return array Mock solar irradiance data
 */
function generateMockSolarIrradianceData($lat, $lon, $date, $interval = '1h') {
    // Generate realistic mock data based on latitude and date
    $dateObj = new DateTime($date);
    $dayOfYear = $dateObj->format('z');
    
    // Calculate approximate sunrise/sunset times
    $sunrise = new DateTime($date . ' 06:00:00');
    $sunset = new DateTime($date . ' 18:30:00');
    
    // Adjust based on latitude (simplified)
    $latAdjustment = ($lat / 90) * 2; // Hours
    $sunrise->modify('+'. $latAdjustment .' hours');
    $sunset->modify('-'. $latAdjustment .' hours');
    
    $mockData = [
        'lat' => $lat,
        'lon' => $lon,
        'date' => $date,
        'interval' => $interval,
        'tz' => '+05:00',
        'sunrise' => $sunrise->format('Y-m-d\TH:i:s'),
        'sunset' => $sunset->format('Y-m-d\TH:i:s'),
        'intervals' => []
    ];
    
    // Generate interval data
    $intervals = [];
    $current = clone $sunrise;
    
    while ($current < $sunset) {
        $end = clone $current;
        
        switch ($interval) {
            case '15m':
                $end->add(new DateInterval('PT15M'));
                break;
            case '1h':
                $end->add(new DateInterval('PT1H'));
                break;
            case '1d':
                $end = clone $sunset;
                break;
        }
        
        if ($end > $sunset) {
            $end = clone $sunset;
        }
        
        // Calculate solar elevation angle (simplified)
        $hour = $current->format('H') + $current->format('i') / 60;
        $solarElevation = sin(($hour - 6) * M_PI / 12) * 90;
        $solarElevation = max(0, $solarElevation);
        
        // Generate realistic irradiance values
        $baseIrradiance = $solarElevation / 90 * 1000; // Max 1000 W/m²
        
        // Add some randomness for cloud cover
        $cloudFactor = 0.7 + (rand(0, 60) / 100); // 70% to 130%
        
        $ghiClear = round($baseIrradiance);
        $ghiCloudy = round($baseIrradiance * $cloudFactor);
        
        $dniClear = round($baseIrradiance * 0.9);
        $dniCloudy = round($baseIrradiance * $cloudFactor * 0.8);
        
        $dhiClear = round($baseIrradiance * 0.1);
        $dhiCloudy = round($baseIrradiance * $cloudFactor * 0.2);
        
        $intervalDuration = ($end->getTimestamp() - $current->getTimestamp()) / 3600; // in hours
        
        $intervals[] = [
            'start' => $current->format('H:i'),
            'end' => $end->format('H:i'),
            'avg_irradiance' => [
                'clear sky' => [
                    'ghi' => $ghiClear,
                    'dni' => $dniClear,
                    'dhi' => $dhiClear
                ],
                'cloud sky' => [
                    'ghi' => $ghiCloudy,
                    'dni' => $dniCloudy,
                    'dhi' => $dhiCloudy
                ]
            ],
            'max_irradiance' => [
                'clear sky' => [
                    'ghi' => $ghiClear + rand(0, 50),
                    'dni' => $dniClear + rand(0, 40),
                    'dhi' => $dhiClear + rand(0, 10)
                ],
                'cloud sky' => [
                    'ghi' => $ghiCloudy + rand(0, 50),
                    'dni' => $dniCloudy + rand(0, 40),
                    'dhi' => $dhiCloudy + rand(0, 10)
                ]
            ],
            'irradiation' => [
                'clear sky' => [
                    'ghi' => round($ghiClear * $intervalDuration),
                    'dni' => round($dniClear * $intervalDuration),
                    'dhi' => round($dhiClear * $intervalDuration)
                ],
                'cloud sky' => [
                    'ghi' => round($ghiCloudy * $intervalDuration),
                    'dni' => round($dniCloudy * $intervalDuration),
                    'dhi' => round($dhiCloudy * $intervalDuration)
                ]
            ]
        ];
        
        $current = clone $end;
    }
    
    $mockData['intervals'] = $intervals;
    return $mockData;
}

/**
 * Mock version of getSolarIrradianceData for testing
 */
function getMockSolarIrradianceData($lat, $lon, $date, $interval = '1h', $timezone = null) {
    // Simulate API delay
    usleep(100000); // 0.1 seconds
    
    return generateMockSolarIrradianceData($lat, $lon, $date, $interval);
}

/**
 * Mock version of getSolarIrradianceByCity for testing
 */
function getMockSolarIrradianceByCity($city, $date, $interval = '1h', $timezone = null) {
    // City coordinates for major Pakistani cities
    $cityCoordinates = [
        'Islamabad' => [33.6844, 73.0479],
        'Karachi' => [24.8607, 67.0011],
        'Lahore' => [31.5204, 74.3587],
        'Peshawar' => [34.0151, 71.5249],
        'Quetta' => [30.1798, 66.9750],
        'Rawalpindi' => [33.5651, 73.0169],
        'Faisalabad' => [31.4504, 73.1350],
        'Multan' => [30.1575, 71.5249],
        'Gujranwala' => [32.1877, 74.1945],
        'Sialkot' => [32.4945, 74.5229]
    ];
    
    if (!isset($cityCoordinates[$city])) {
        // Use Islamabad as default
        $coordinates = $cityCoordinates['Islamabad'];
    } else {
        $coordinates = $cityCoordinates[$city];
    }
    
    $mockData = generateMockSolarIrradianceData($coordinates[0], $coordinates[1], $date, $interval);
    return formatSolarIrradianceData($mockData);
}

// Test the mock data
echo "<h1>Mock Solar Irradiance Data Test</h1>";
echo "<p style='color: blue;'>This test uses mock data for demonstration purposes.</p>";

$today = date('Y-m-d');

// Test 1: Mock data for Islamabad
echo "<h2>Test 1: Mock Solar Irradiance for Islamabad (Today)</h2>";
$mockData1 = getMockSolarIrradianceByCity('Islamabad', $today, '1h');
if ($mockData1) {
    echo "<h3>Success!</h3>";
    echo "<pre>" . print_r($mockData1, true) . "</pre>";
} else {
    echo "<p style='color: red;'>Failed to generate mock data</p>";
}

// Test 2: Mock data for Karachi coordinates
echo "<h2>Test 2: Mock Solar Irradiance for Karachi Coordinates</h2>";
$mockData2 = getMockSolarIrradianceData(24.8607, 67.0011, $today, '1h');
if ($mockData2) {
    $formattedData2 = formatSolarIrradianceData($mockData2);
    echo "<h3>Success!</h3>";
    echo "<pre>" . print_r($formattedData2, true) . "</pre>";
} else {
    echo "<p style='color: red;'>Failed to generate mock data</p>";
}

// Test 3: Mock data with 15-minute intervals
echo "<h2>Test 3: Mock Solar Irradiance for Lahore (15-minute intervals)</h2>";
$mockData3 = getMockSolarIrradianceByCity('Lahore', $today, '15m');
if ($mockData3) {
    echo "<h3>Success!</h3>";
    echo "<p>Number of 15-minute intervals: " . count($mockData3['intervals']) . "</p>";
    echo "<pre>" . print_r(array_slice($mockData3, 0, 5), true) . "</pre>"; // Show first 5 intervals
} else {
    echo "<p style='color: red;'>Failed to generate mock data</p>";
}

echo "<h2>How to Use Mock Data</h2>";
echo "<p>To use mock data in your application, replace the real API calls with mock functions:</p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo "// Instead of:
\$solarData = getSolarIrradianceByCity('Islamabad', \$date, '1h');

// Use:
\$solarData = getMockSolarIrradianceByCity('Islamabad', \$date, '1h');
</pre>";

?>
