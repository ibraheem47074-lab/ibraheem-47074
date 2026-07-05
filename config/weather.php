<?php
// Weather API Configuration
define('WEATHER_API_KEY', 'b1b15e88fa797225412429c1c50c122a1'); // OpenWeatherMap API key
define('WEATHER_API_URL', 'https://api.openweathermap.org/data/2.5/weather');
define('WEATHER_FORECAST_URL', 'https://api.openweathermap.org/data/2.5/forecast');
define('WEATHER_ONECALL_URL', 'https://api.openweathermap.org/data/3.0/onecall');
define('WEATHER_CACHE_DURATION', 1800); // 30 minutes cache

// Open-Meteo API (Free, no API key required - fallback)
define('OPEN_METEO_URL', 'https://api.open-meteo.com/v1/forecast');
define('OPEN_METEO_GEO_URL', 'https://geocoding-api.open-meteo.com/v1/search');

// Weather data cache file
define('WEATHER_CACHE_FILE', __DIR__ . '/../cache/weather_cache.json');

/**
 * Check if API key is properly configured
 * @return bool True if API key is set and not placeholder
 */
function isApiKeyConfigured() {
    $apiKey = WEATHER_API_KEY;
    return !empty($apiKey) &&
           $apiKey !== 'YOUR_OPENWEATHERMAP_API_KEY' &&
           $apiKey !== 'api.openweathermap.org/data/2.5/forecast?lat={lat}&lon={lon}&appid={API key}';
}

/**
 * Weather Input Criteria & Validation
 */

/**
 * Validate city name input
 * @param string $city City name to validate
 * @return array ['valid' => bool, 'error' => string, 'sanitized' => string]
 */
function validateCityInput($city) {
    $result = ['valid' => false, 'error' => '', 'sanitized' => ''];

    // Check if empty
    if (empty($city) || trim($city) === '') {
        $result['error'] = 'City name is required';
        return $result;
    }

    $sanitized = trim($city);

    // Check minimum length
    if (strlen($sanitized) < 2) {
        $result['error'] = 'City name must be at least 2 characters long';
        return $result;
    }

    // Check maximum length
    if (strlen($sanitized) > 100) {
        $result['error'] = 'City name must not exceed 100 characters';
        return $result;
    }

    // Check for valid characters (letters, spaces, hyphens, apostrophes, periods)
    if (!preg_match('/^[a-zA-Z\s\-\'\.]+$/u', $sanitized)) {
        // Allow non-ASCII for international city names
        if (!preg_match('/^[\p{L}\s\-\'\.]+$/u', $sanitized)) {
            $result['error'] = 'City name contains invalid characters';
            return $result;
        }
    }

    // Check for common SQL injection patterns
    $forbiddenPatterns = ['/drop\s+table/i', '/delete\s+from/i', '/insert\s+into/i', '/union\s+select/i', '/exec\s*\(/i', '/system\s*\(/i'];
    foreach ($forbiddenPatterns as $pattern) {
        if (preg_match($pattern, $sanitized)) {
            $result['error'] = 'Invalid city name format';
            return $result;
        }
    }

    // Check against whitelist of valid Pakistani cities (optional strict mode)
    $validPakistaniCities = [
        'islamabad', 'karachi', 'lahore', 'peshawar', 'quetta', 'rawalpindi',
        'faisalabad', 'multan', 'gujranwala', 'sialkot', 'hyderabad', 'sukkur',
        'bahawalpur', 'sargodha', 'sialkot', 'gujrat', 'sheikhupura',
        'jhang', 'sahiwal', ' Abbottabad', 'mardan', 'mingora'
    ];

    $result['valid'] = true;
    $result['sanitized'] = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
    return $result;
}

/**
 * Validate coordinates input
 * @param float $lat Latitude
 * @param float $lon Longitude
 * @return array ['valid' => bool, 'error' => string]
 */
function validateCoordinates($lat, $lon) {
    $result = ['valid' => false, 'error' => ''];

    // Validate latitude range
    if (!is_numeric($lat) || $lat < -90 || $lat > 90) {
        $result['error'] = 'Invalid latitude. Must be between -90 and 90';
        return $result;
    }

    // Validate longitude range
    if (!is_numeric($lon) || $lon < -180 || $lon > 180) {
        $result['error'] = 'Invalid longitude. Must be between -180 and 180';
        return $result;
    }

    // Check for reasonable precision (max 6 decimal places)
    $latStr = (string)$lat;
    $lonStr = (string)$lon;
    if (strpos($latStr, '.') !== false && strlen(substr($latStr, strpos($latStr, '.') + 1)) > 6) {
        $result['error'] = 'Latitude precision too high (max 6 decimal places)';
        return $result;
    }
    if (strpos($lonStr, '.') !== false && strlen(substr($lonStr, strpos($lonStr, '.') + 1)) > 6) {
        $result['error'] = 'Longitude precision too high (max 6 decimal places)';
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate units input
 * @param string $units Units value
 * @return array ['valid' => bool, 'error' => string, 'sanitized' => string]
 */
function validateUnitsInput($units) {
    $result = ['valid' => false, 'error' => '', 'sanitized' => 'metric'];

    $allowedUnits = ['metric', 'imperial', 'kelvin'];
    $sanitized = strtolower(trim($units));

    if (!in_array($sanitized, $allowedUnits)) {
        $result['error'] = 'Invalid units. Must be metric, imperial, or kelvin';
        $result['sanitized'] = 'metric'; // Default fallback
        return $result;
    }

    $result['valid'] = true;
    $result['sanitized'] = $sanitized;
    return $result;
}

/**
 * Validate date input for weather history/forecast
 * @param string $date Date string
 * @param int $maxDaysBack Maximum days back allowed
 * @param int $maxDaysForward Maximum days forward allowed
 * @return array ['valid' => bool, 'error' => string, 'timestamp' => int]
 */
function validateWeatherDate($date, $maxDaysBack = 5, $maxDaysForward = 16) {
    $result = ['valid' => false, 'error' => '', 'timestamp' => 0];

    if (empty($date)) {
        $result['error'] = 'Date is required';
        return $result;
    }

    // Validate date format
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        $result['error'] = 'Invalid date format. Use YYYY-MM-DD';
        return $result;
    }

    $requestedDate = new DateTime($date);
    $today = new DateTime();
    $today->setTime(0, 0, 0);

    // Check if date is in valid range
    $minDate = clone $today;
    $minDate->modify("-{$maxDaysBack} days");

    $maxDate = clone $today;
    $maxDate->modify("+{$maxDaysForward} days");

    if ($requestedDate < $minDate) {
        $result['error'] = "Date too far in the past. Maximum {$maxDaysBack} days back allowed";
        return $result;
    }

    if ($requestedDate > $maxDate) {
        $result['error'] = "Date too far in the future. Maximum {$maxDaysForward} days forward allowed";
        return $result;
    }

    $result['valid'] = true;
    $result['timestamp'] = $timestamp;
    return $result;
}

/**
 * Comprehensive weather input validation
 * @param array $inputs Input data (city, lat, lon, units, date)
 * @return array ['valid' => bool, 'errors' => array, 'sanitized' => array]
 */
function validateWeatherInputs($inputs) {
    $result = ['valid' => true, 'errors' => [], 'sanitized' => []];

    // Validate city if provided
    if (isset($inputs['city'])) {
        $cityValidation = validateCityInput($inputs['city']);
        if (!$cityValidation['valid']) {
            $result['valid'] = false;
            $result['errors'][] = $cityValidation['error'];
        } else {
            $result['sanitized']['city'] = $cityValidation['sanitized'];
        }
    }

    // Validate coordinates if provided (instead of or with city)
    if (isset($inputs['lat']) && isset($inputs['lon'])) {
        $coordValidation = validateCoordinates($inputs['lat'], $inputs['lon']);
        if (!$coordValidation['valid']) {
            $result['valid'] = false;
            $result['errors'][] = $coordValidation['error'];
        } else {
            $result['sanitized']['lat'] = (float)$inputs['lat'];
            $result['sanitized']['lon'] = (float)$inputs['lon'];
        }
    }

    // Validate units
    if (isset($inputs['units'])) {
        $unitsValidation = validateUnitsInput($inputs['units']);
        if (!$unitsValidation['valid']) {
            $result['valid'] = false;
            $result['errors'][] = $unitsValidation['error'];
        }
        $result['sanitized']['units'] = $unitsValidation['sanitized'];
    } else {
        $result['sanitized']['units'] = 'metric';
    }

    // Validate date if provided
    if (isset($inputs['date'])) {
        $dateValidation = validateWeatherDate($inputs['date']);
        if (!$dateValidation['valid']) {
            $result['valid'] = false;
            $result['errors'][] = $dateValidation['error'];
        }
        $result['sanitized']['date'] = $inputs['date'];
    }

    return $result;
}

/**
 * Get weather data for a city
 * @param string $city City name
 * @param string $units Units: metric (Celsius) or imperial (Fahrenheit)
 * @return array|false Weather data or false on error
 */
function getWeatherData($city, $units = 'metric') {
    // Validate inputs first
    $validation = validateWeatherInputs(['city' => $city, 'units' => $units]);
    if (!$validation['valid']) {
        error_log('Weather input validation failed: ' . implode(', ', $validation['errors']));
        return false;
    }

    // Use sanitized values
    $city = $validation['sanitized']['city'] ?? $city;
    $units = $validation['sanitized']['units'] ?? 'metric';

    // Check if API key is set
    if (!isApiKeyConfigured()) {
        error_log('Weather API key not configured');
        return false;
    }
    
    $cacheKey = md5($city . $units);
    $cacheData = getWeatherCache();
    
    // Check if we have cached data that's still valid
    if (isset($cacheData[$cacheKey]) && (time() - $cacheData[$cacheKey]['timestamp']) < WEATHER_CACHE_DURATION) {
        return $cacheData[$cacheKey]['data'];
    }
    
    // Try different city name variations for Pakistani cities
    $cityVariations = [$city];
    if (strtolower($city) === 'peshawar') {
        $cityVariations = ['Peshawar', 'Peshawar, PK', 'Peshawar, Pakistan'];
    } elseif (strtolower($city) === 'islamabad') {
        $cityVariations = ['Islamabad', 'Islamabad, PK', 'Islamabad, Pakistan'];
    } elseif (strtolower($city) === 'karachi') {
        $cityVariations = ['Karachi', 'Karachi, PK', 'Karachi, Pakistan'];
    } elseif (strtolower($city) === 'lahore') {
        $cityVariations = ['Lahore', 'Lahore, PK', 'Lahore, Pakistan'];
    }
    
    // Try each variation
    foreach ($cityVariations as $cityName) {
        $url = WEATHER_API_URL . "?q=" . urlencode($cityName) . "&appid=" . WEATHER_API_KEY . "&units=" . $units;
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'PK Live News Weather Widget'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            
            if (json_last_error() === JSON_ERROR_NONE && (!isset($data['cod']) || $data['cod'] == 200)) {
                // Cache the successful data
                $cacheData[$cacheKey] = [
                    'data' => $data,
                    'timestamp' => time()
                ];
                saveWeatherCache($cacheData);
                
                return $data;
            }
        }
    }
    
    error_log('Weather API failed for city: ' . $city);
    
    // Try Open-Meteo as fallback
    return getWeatherDataFromOpenMeteo($city, $units);
}

/**
 * Get city coordinates from Open-Meteo Geocoding API
 * @param string $city City name
 * @return array|false Coordinates [lat, lon] or false on error
 */
function getCityCoordinatesFromOpenMeteo($city) {
    $url = OPEN_METEO_GEO_URL . "?name=" . urlencode($city) . "&count=1&language=en&format=json";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PK Live News Weather Widget'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE && isset($data['results']) && !empty($data['results'])) {
            $result = $data['results'][0];
            return [
                'lat' => $result['latitude'],
                'lon' => $result['longitude'],
                'name' => $result['name'],
                'country' => $result['country_code'] ?? ''
            ];
        }
    }
    
    return false;
}

/**
 * Get weather data from Open-Meteo API (fallback)
 * @param string $city City name
 * @param string $units Units: metric (Celsius) or imperial (Fahrenheit)
 * @return array|false Weather data or false on error
 */
function getWeatherDataFromOpenMeteo($city, $units = 'metric') {
    $coords = getCityCoordinatesFromOpenMeteo($city);
    
    if (!$coords) {
        return false;
    }
    
    $cacheKey = md5('openmeteo_' . $city . $units);
    $cacheData = getWeatherCache();
    
    // Check if we have cached data that's still valid
    if (isset($cacheData[$cacheKey]) && (time() - $cacheData[$cacheKey]['timestamp']) < WEATHER_CACHE_DURATION) {
        return $cacheData[$cacheKey]['data'];
    }
    
    // Build Open-Meteo API URL
    $tempUnit = $units === 'imperial' ? 'fahrenheit' : 'celsius';
    $windSpeedUnit = $units === 'imperial' ? 'mph' : 'kmh';
    
    $url = OPEN_METEO_URL . "?latitude=" . $coords['lat'] . "&longitude=" . $coords['lon'] .
           "&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,pressure_msl" .
           "&timezone=auto" .
           "&temperature_unit=" . $tempUnit .
           "&wind_speed_unit=" . $windSpeedUnit;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PK Live News Weather Widget'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE && isset($data['current'])) {
            // Convert Open-Meteo data to OpenWeatherMap format
            $formattedData = [
                'name' => $coords['name'],
                'sys' => [
                    'country' => $coords['country'],
                    'sunrise' => isset($data['daily']) && isset($data['daily']['sunrise']) ? $data['daily']['sunrise'][0] : time(),
                    'sunset' => isset($data['daily']) && isset($data['daily']['sunset']) ? $data['daily']['sunset'][0] : time()
                ],
                'main' => [
                    'temp' => $data['current']['temperature_2m'],
                    'feels_like' => $data['current']['temperature_2m'], // Open-Meteo doesn't provide feels_like
                    'humidity' => $data['current']['relative_humidity_2m'],
                    'pressure' => $data['current']['pressure_msl'] ?? 1013
                ],
                'weather' => [
                    [
                        'main' => getWeatherDescriptionFromCode($data['current']['weather_code']),
                        'description' => getWeatherDescriptionFromCode($data['current']['weather_code']),
                        'icon' => getWeatherIconFromCode($data['current']['weather_code'])
                    ]
                ],
                'wind' => [
                    'speed' => $data['current']['wind_speed_10m'],
                    'deg' => 0
                ],
                'visibility' => 10000 // Default visibility
            ];
            
            // Cache the successful data
            $cacheData[$cacheKey] = [
                'data' => $formattedData,
                'timestamp' => time()
            ];
            saveWeatherCache($cacheData);
            
            return $formattedData;
        }
    }
    
    error_log('Open-Meteo API failed for city: ' . $city);
    return false;
}

/**
 * Get weather forecast from Open-Meteo API (fallback)
 * @param string $city City name
 * @param string $units Units: metric (Celsius) or imperial (Fahrenheit)
 * @return array|false Forecast data or false on error
 */
function getWeatherForecastFromOpenMeteo($city, $units = 'metric') {
    $coords = getCityCoordinatesFromOpenMeteo($city);
    
    if (!$coords) {
        return false;
    }
    
    $cacheKey = md5('openmeteo_forecast_' . $city . $units);
    $cacheData = getWeatherCache();
    
    // Check if we have cached data that's still valid
    if (isset($cacheData[$cacheKey]) && (time() - $cacheData[$cacheKey]['timestamp']) < WEATHER_CACHE_DURATION) {
        return $cacheData[$cacheKey]['data'];
    }
    
    // Build Open-Meteo API URL for hourly and daily forecast
    $tempUnit = $units === 'imperial' ? 'fahrenheit' : 'celsius';
    $windSpeedUnit = $units === 'imperial' ? 'mph' : 'kmh';
    
    $url = OPEN_METEO_URL . "?latitude=" . $coords['lat'] . "&longitude=" . $coords['lon'] .
           "&hourly=temperature_2m,weather_code,relative_humidity_2m,wind_speed_10m" .
           "&daily=weather_code,temperature_2m_max,temperature_2m_min" .
           "&timezone=auto" .
           "&temperature_unit=" . $tempUnit .
           "&wind_speed_unit=" . $windSpeedUnit .
           "&forecast_days=5";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PK Live News Weather Widget'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE && isset($data['hourly'])) {
            // Debug: Log if daily data is missing
            if (!isset($data['daily'])) {
                error_log('Open-Meteo API response missing daily data. Response: ' . json_encode($data));
            }
            // Convert Open-Meteo hourly data to OpenWeatherMap format
            $forecastList = [];
            $hourly = $data['hourly'];
            
            for ($i = 0; $i < count($hourly['time']); $i++) {
                $forecastList[] = [
                    'dt' => strtotime($hourly['time'][$i]),
                    'main' => [
                        'temp' => $hourly['temperature_2m'][$i],
                        'humidity' => $hourly['relative_humidity_2m'][$i]
                    ],
                    'weather' => [
                        [
                            'main' => getWeatherDescriptionFromCode($hourly['weather_code'][$i]),
                            'description' => getWeatherDescriptionFromCode($hourly['weather_code'][$i]),
                            'icon' => getWeatherIconFromCode($hourly['weather_code'][$i])
                        ]
                    ],
                    'wind' => [
                        'speed' => $hourly['wind_speed_10m'][$i]
                    ]
                ];
            }
            
            // Convert Open-Meteo daily data to OpenWeatherMap format
            $dailyList = [];
            if (isset($data['daily'])) {
                $daily = $data['daily'];
                for ($i = 0; $i < count($daily['time']); $i++) {
                    $dailyList[] = [
                        'dt' => strtotime($daily['time'][$i]),
                        'temp' => [
                            'min' => $daily['temperature_2m_min'][$i],
                            'max' => $daily['temperature_2m_max'][$i]
                        ],
                        'weather' => [
                            [
                                'main' => getWeatherDescriptionFromCode($daily['weather_code'][$i]),
                                'description' => getWeatherDescriptionFromCode($daily['weather_code'][$i]),
                                'icon' => getWeatherIconFromCode($daily['weather_code'][$i])
                            ]
                        ],
                        'sunrise' => 0,
                        'sunset' => 0
                    ];
                }
            }
            
            $formattedData = [
                'list' => $forecastList,
                'daily' => $dailyList,
                'city' => [
                    'name' => $coords['name'],
                    'country' => $coords['country']
                ]
            ];
            
            // Cache the successful data
            $cacheData[$cacheKey] = [
                'data' => $formattedData,
                'timestamp' => time()
            ];
            saveWeatherCache($cacheData);
            
            return $formattedData;
        }
    }
    
    return false;
}

/**
 * Get weather description from Open-Meteo weather code
 * @param int $code Weather code
 * @return string Weather description
 */
function getWeatherDescriptionFromCode($code) {
    $codes = [
        0 => 'Clear sky',
        1 => 'Mainly clear',
        2 => 'Partly cloudy',
        3 => 'Overcast',
        45 => 'Fog',
        48 => 'Depositing rime fog',
        51 => 'Light drizzle',
        53 => 'Moderate drizzle',
        55 => 'Dense drizzle',
        61 => 'Slight rain',
        63 => 'Moderate rain',
        65 => 'Heavy rain',
        71 => 'Slight snow',
        73 => 'Moderate snow',
        75 => 'Heavy snow',
        77 => 'Snow grains',
        80 => 'Slight rain showers',
        81 => 'Moderate rain showers',
        82 => 'Violent rain showers',
        85 => 'Slight snow showers',
        86 => 'Heavy snow showers',
        95 => 'Thunderstorm',
        96 => 'Thunderstorm with slight hail',
        99 => 'Thunderstorm with heavy hail'
    ];
    
    return $codes[$code] ?? 'Unknown';
}

/**
 * Get weather icon code from Open-Meteo weather code
 * @param int $code Weather code
 * @param bool $isDay Whether it's daytime
 * @return string Icon code compatible with OpenWeatherMap icons
 */
function getWeatherIconFromCode($code, $isDay = true) {
    $daySuffix = $isDay ? 'd' : 'n';
    
    // Map Open-Meteo codes to OpenWeatherMap icon codes
    $iconMap = [
        0 => '01' . $daySuffix, // Clear sky
        1 => '02' . $daySuffix, // Mainly clear
        2 => '02' . $daySuffix, // Partly cloudy
        3 => '04' . $daySuffix, // Overcast
        45 => '50' . $daySuffix, // Fog
        48 => '50' . $daySuffix, // Depositing rime fog
        51 => '10' . $daySuffix, // Light drizzle
        53 => '10' . $daySuffix, // Moderate drizzle
        55 => '10' . $daySuffix, // Dense drizzle
        61 => '10' . $daySuffix, // Slight rain
        63 => '10' . $daySuffix, // Moderate rain
        65 => '10' . $daySuffix, // Heavy rain
        71 => '13' . $daySuffix, // Slight snow
        73 => '13' . $daySuffix, // Moderate snow
        75 => '13' . $daySuffix, // Heavy snow
        77 => '13' . $daySuffix, // Snow grains
        80 => '10' . $daySuffix, // Slight rain showers
        81 => '10' . $daySuffix, // Moderate rain showers
        82 => '10' . $daySuffix, // Violent rain showers
        85 => '13' . $daySuffix, // Slight snow showers
        86 => '13' . $daySuffix, // Heavy snow showers
        95 => '11' . $daySuffix, // Thunderstorm
        96 => '11' . $daySuffix, // Thunderstorm with slight hail
        99 => '11' . $daySuffix  // Thunderstorm with heavy hail
    ];
    
    return $iconMap[$code] ?? '01' . $daySuffix;
}

/**
 * Format forecast data for better display
 * @param array $forecastData Raw forecast data from API
 * @return array Formatted forecast data
 */
function formatForecastData($forecastData) {
    if (!isset($forecastData['list'])) {
        return [];
    }
    
    // If daily data is available (from Open-Meteo), use it directly
    if (isset($forecastData['daily']) && !empty($forecastData['daily'])) {
        return $forecastData['daily'];
    }
    
    // Otherwise, extract daily forecasts from hourly data (OpenWeatherMap format)
    $dailyForecasts = [];
    $processedDates = [];
    
    foreach ($forecastData['list'] as $item) {
        $date = date('Y-m-d', $item['dt']);
        $hour = date('H', $item['dt']);
        
        // For each day, pick the most relevant time (midday for current day, evening for future days)
        if (!isset($processedDates[$date])) {
            $dailyForecasts[$date] = $item;
            $processedDates[$date] = true;
        } else {
            // For future days, prefer evening forecasts (more representative)
            $currentHour = date('H');
            $itemHour = $hour;
            
            // If this is evening (18:00 or later) and we don't have a better one, use it
            if ($itemHour >= 18 && $itemHour > date('H', $dailyForecasts[$date]['dt'])) {
                $dailyForecasts[$date] = $item;
            }
        }
    }
    
    return array_slice($dailyForecasts, 0, 5);
}

/**
 * Get daily summary from forecast data
 * @param array $forecastData Raw forecast data
 * @param string $date Date in Y-m-d format
 * @return array Daily summary
 */
function getDailyForecastSummary($forecastData, $date) {
    if (!isset($forecastData['list'])) {
        return null;
    }
    
    $dayItems = [];
    foreach ($forecastData['list'] as $item) {
        $itemDate = date('Y-m-d', $item['dt']);
        if ($itemDate === $date) {
            $dayItems[] = $item;
        }
    }
    
    if (empty($dayItems)) {
        return null;
    }
    
    // Calculate daily averages and extremes
    $temps = array_column($dayItems, 'main');
    $tempData = array_column($temps, 'temp');
    $humidity = array_column($temps, 'humidity');
    
    // Prevent division by zero
    $tempCount = count($tempData);
    $humidityCount = count($humidity);
    $windData = array_column($dayItems, 'wind');
    $windCount = count($windData);
    
    return [
        'date' => $date,
        'temp_min' => $tempCount > 0 ? min($tempData) : null,
        'temp_max' => $tempCount > 0 ? max($tempData) : null,
        'temp_avg' => $tempCount > 0 ? array_sum($tempData) / $tempCount : null,
        'humidity_avg' => $humidityCount > 0 ? array_sum($humidity) / $humidityCount : null,
        'weather_main' => $dayItems[0]['weather'][0]['main'] ?? 'Unknown',
        'weather_desc' => $dayItems[0]['weather'][0]['description'] ?? 'Unknown',
        'weather_icon' => $dayItems[0]['weather'][0]['icon'] ?? '01d',
        'pop' => max(array_column($dayItems, 'pop')) * 100, // Max precipitation probability
        'wind_speed' => $windCount > 0 ? array_sum(array_column($windData, 'speed')) / $windCount : 0,
        'items_count' => count($dayItems)
    ];
}

/**
 * Get weather data using coordinates
 * @param float $lat Latitude
 * @param float $lon Longitude
 * @param string $units Units: metric (Celsius) or imperial (Fahrenheit)
 * @return array|false Weather data or false on error
 */
function getWeatherByCoordinates($lat, $lon, $units = 'metric') {
    // Check if API key is set
    if (WEATHER_API_KEY === 'YOUR_OPENWEATHERMAP_API_KEY') {
        error_log('Weather API key not configured');
        return false;
    }
    
    $cacheKey = md5($lat . $lon . $units);
    $cacheData = getWeatherCache();
    
    // Check if we have cached data that's still valid
    if (isset($cacheData[$cacheKey]) && (time() - $cacheData[$cacheKey]['timestamp']) < WEATHER_CACHE_DURATION) {
        return $cacheData[$cacheKey]['data'];
    }
    
    // Fetch fresh data from API using coordinates
    $url = WEATHER_API_URL . "?lat=" . $lat . "&lon=" . $lon . "&appid=" . WEATHER_API_KEY . "&units=" . $units;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PK Live News Weather Widget'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE && (!isset($data['cod']) || $data['cod'] == 200)) {
            // Cache the successful data
            $cacheData[$cacheKey] = [
                'data' => $data,
                'timestamp' => time()
            ];
            saveWeatherCache($cacheData);
            
            return $data;
        }
    }
    
    error_log('Weather API failed for coordinates: ' . $lat . ',' . $lon);
    return false;
}

/**
 * Get weather forecast using coordinates
 * @param float $lat Latitude
 * @param float $lon Longitude
 * @param string $units Units: metric (Celsius) or imperial (Fahrenheit)
 * @return array|false Forecast data or false on error
 */
function getWeatherForecastByCoordinates($lat, $lon, $units = 'metric') {
    // Check if API key is set
    if (!isApiKeyConfigured()) {
        error_log('Weather API key not configured');
        return false;
    }
    
    $cacheKey = md5($lat . $lon . $units . '_forecast');
    $cacheData = getWeatherCache();
    
    // Check if we have cached data that's still valid
    if (isset($cacheData[$cacheKey]) && (time() - $cacheData[$cacheKey]['timestamp']) < WEATHER_CACHE_DURATION) {
        return $cacheData[$cacheKey]['data'];
    }
    
    // Fetch fresh data from API using coordinates
    $url = WEATHER_FORECAST_URL . "?lat=" . $lat . "&lon=" . $lon . "&appid=" . WEATHER_API_KEY . "&units=" . $units;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PK Live News Weather Widget'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE && (!isset($data['cod']) || $data['cod'] == 200)) {
            // Cache the successful data
            $cacheData[$cacheKey] = [
                'data' => $data,
                'timestamp' => time()
            ];
            saveWeatherCache($cacheData);
            
            return $data;
        }
    }
    
    error_log('Weather forecast API failed for coordinates: ' . $lat . ',' . $lon);
    return false;
}

/**
 * Get user's location-based weather using browser geolocation (JavaScript will call this)
 * @param string $units Units: metric (Celsius) or imperial (Fahrenheit)
 * @return array|false Weather data or false on error
 */
function getUserLocationWeather($units = 'metric') {
    // This function is called via AJAX from JavaScript
    if (isset($_POST['lat']) && isset($_POST['lon'])) {
        $lat = floatval($_POST['lat']);
        $lon = floatval($_POST['lon']);
        return getWeatherByCoordinates($lat, $lon, $units);
    }
    return false;
}

/**
 * Get weather forecast for a city
 * @param string $city City name
 * @param string $units Units: metric (Celsius) or imperial (Fahrenheit)
 * @return array|false Forecast data or false on error
 */
function getWeatherForecast($city, $units = 'metric') {
    $cacheKey = md5($city . $units . '_forecast');
    $cacheData = getWeatherCache();
    
    // Check if we have cached data that's still valid
    if (isset($cacheData[$cacheKey]) && (time() - $cacheData[$cacheKey]['timestamp']) < WEATHER_CACHE_DURATION) {
        return $cacheData[$cacheKey]['data'];
    }
    
    // Fetch fresh data from API
    $url = WEATHER_FORECAST_URL . "?q=" . urlencode($city) . "&appid=" . WEATHER_API_KEY . "&units=" . $units;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PK Live News Weather Widget'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        // Try Open-Meteo as fallback
        return getWeatherForecastFromOpenMeteo($city, $units);
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Try Open-Meteo as fallback
        return getWeatherForecastFromOpenMeteo($city, $units);
    }
    
    if (isset($data['cod']) && $data['cod'] != 200) {
        // Try Open-Meteo as fallback
        return getWeatherForecastFromOpenMeteo($city, $units);
    }
    
    // Cache the data
    $cacheData[$cacheKey] = [
        'data' => $data,
        'timestamp' => time()
    ];
    saveWeatherCache($cacheData);
    
    return $data;
}

/**
 * Format weather data for display
 * @param array $weatherData Raw weather data from API
 * @return array Formatted weather data
 */
function formatWeatherData($weatherData) {
    if (!$weatherData) {
        return null;
    }
    
    return [
        'city' => $weatherData['name'],
        'country' => $weatherData['sys']['country'],
        'temperature' => round($weatherData['main']['temp']),
        'feels_like' => round($weatherData['main']['feels_like']),
        'description' => ucfirst($weatherData['weather'][0]['description']),
        'icon' => $weatherData['weather'][0]['icon'],
        'humidity' => $weatherData['main']['humidity'],
        'pressure' => $weatherData['main']['pressure'],
        'wind_speed' => $weatherData['wind']['speed'],
        'wind_direction' => $weatherData['wind']['deg'] ?? 0,
        'visibility' => isset($weatherData['visibility']) ? $weatherData['visibility'] / 1000 : null,
        'sunrise' => date('h:i A', $weatherData['sys']['sunrise']),
        'sunset' => date('h:i A', $weatherData['sys']['sunset']),
        'units' => isset($weatherData['main']['temp']) && $weatherData['main']['temp'] > 50 ? 'imperial' : 'metric'
    ];
}

/**
 * Get weather icon HTML
 * @param string $iconCode Weather icon code from API
 * @param string $size Icon size: small, medium, large
 * @return string HTML for weather icon
 */
function getWeatherIcon($iconCode, $size = 'medium') {
    $sizeMap = [
        'small' => '2x',
        'medium' => '3x',
        'large' => '4x'
    ];
    
    $iconSize = $sizeMap[$size] ?? '2x';
    
    // Map OpenWeatherMap icons to Font Awesome icons
    $iconMap = [
        '01d' => 'fas fa-sun', // clear sky day
        '01n' => 'fas fa-moon', // clear sky night
        '02d' => 'fas fa-cloud-sun', // few clouds day
        '02n' => 'fas fa-cloud-moon', // few clouds night
        '03d' => 'fas fa-cloud', // scattered clouds
        '03n' => 'fas fa-cloud', // scattered clouds
        '04d' => 'fas fa-cloud', // broken clouds
        '04n' => 'fas fa-cloud', // broken clouds
        '09d' => 'fas fa-cloud-showers-heavy', // shower rain
        '09n' => 'fas fa-cloud-showers-heavy', // shower rain
        '10d' => 'fas fa-cloud-sun-rain', // rain day
        '10n' => 'fas fa-cloud-moon-rain', // rain night
        '11d' => 'fas fa-bolt', // thunderstorm
        '11n' => 'fas fa-bolt', // thunderstorm
        '13d' => 'fas fa-snowflake', // snow
        '13n' => 'fas fa-snowflake', // snow
        '50d' => 'fas fa-smog', // mist
        '50n' => 'fas fa-smog' // mist
    ];
    
    $iconClass = $iconMap[$iconCode] ?? 'fas fa-question';
    $color = getWeatherIconColor($iconCode);
    
    return "<i class='{$iconClass} fa-{$iconSize}' style='color: {$color};'></i>";
}

/**
 * Get color for weather icon
 * @param string $iconCode Weather icon code
 * @return string Color hex code
 */
function getWeatherIconColor($iconCode) {
    $colorMap = [
        '01d' => '#FFD700', // gold
        '01n' => '#4A5568', // gray
        '02d' => '#87CEEB', // sky blue
        '02n' => '#2D3748', // dark gray
        '03d' => '#718096', // medium gray
        '03n' => '#718096', // medium gray
        '04d' => '#4A5568', // gray
        '04n' => '#4A5568', // gray
        '09d' => '#3182CE', // blue
        '09n' => '#3182CE', // blue
        '10d' => '#2B6CB0', // dark blue
        '10n' => '#2B6CB0', // dark blue
        '11d' => '#805AD5', // purple
        '11n' => '#805AD5', // purple
        '13d' => '#E2E8F0', // light gray
        '13n' => '#E2E8F0', // light gray
        '50d' => '#A0AEC0', // light gray
        '50n' => '#A0AEC0' // light gray
    ];
    
    return $colorMap[$iconCode] ?? '#718096';
}

/**
 * Get weather cache data
 * @return array Cache data
 */
function getWeatherCache() {
    if (!file_exists(WEATHER_CACHE_FILE)) {
        return [];
    }
    
    $cacheData = @json_decode(file_get_contents(WEATHER_CACHE_FILE), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }
    
    // Clean expired cache entries
    $currentTime = time();
    foreach ($cacheData as $key => $entry) {
        if (($currentTime - $entry['timestamp']) >= WEATHER_CACHE_DURATION) {
            unset($cacheData[$key]);
        }
    }
    
    return $cacheData;
}

/**
 * Save weather cache data
 * @param array $cacheData Cache data to save
 * @return bool Success status
 */
function saveWeatherCache($cacheData) {
    $cacheDir = dirname(WEATHER_CACHE_FILE);
    
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    return file_put_contents(WEATHER_CACHE_FILE, json_encode($cacheData)) !== false;
}

/**
 * Get default cities for weather display
 * @return array List of default cities
 */
function getDefaultWeatherCities() {
    return [
        'Islamabad' => 'PK',
        'Karachi' => 'PK',
        'Lahore' => 'PK',
        'Peshawar' => 'PK',
        'Quetta' => 'PK',
        'Rawalpindi' => 'PK',
        'Faisalabad' => 'PK',
        'Multan' => 'PK',
        'Gujranwala' => 'PK',
        'Sialkot' => 'PK'
    ];
}

/**
 * Get user's location-based city (placeholder for future implementation)
 * @return string|null City name or null if not available
 */
function getUserLocationCity() {
    // This is a placeholder - you can implement geolocation here
    // For now, return Islamabad as default
    return 'Islamabad';
}

/**
 * Format temperature with unit
 * @param float $temperature Temperature value
 * @param string $units Units: metric or imperial
 * @return string Formatted temperature
 */
function formatTemperature($temperature, $units = 'metric') {
    $unit = $units === 'imperial' ? '°F' : '°C';
    return round($temperature) . $unit;
}

/**
 * Get solar irradiance data for specific coordinates and date
 * @param float $lat Latitude (-90 to 90)
 * @param float $lon Longitude (-180 to 180)
 * @param string $date Date in YYYY-MM-DD format (from 1979-01-01 to +15 days from current date)
 * @param string $interval Time interval: '15m' (15 minutes), '1h' (1 hour, default), or '1d' (1 day)
 * @param string $timezone Optional timezone in ±XX:XX format
 * @return array|false Solar irradiance data or false on error
 */
function getSolarIrradianceData($lat, $lon, $date, $interval = '1h', $timezone = null) {
    // Check if API key is set
    if (!isApiKeyConfigured()) {
        error_log('Weather API key not configured for solar irradiance data');
        return false;
    }
    
    // Validate parameters
    if ($lat < -90 || $lat > 90) {
        error_log('Invalid latitude: ' . $lat . '. Must be between -90 and 90');
        return false;
    }
    
    if ($lon < -180 || $lon > 180) {
        error_log('Invalid longitude: ' . $lon . '. Must be between -180 and 180');
        return false;
    }
    
    // Validate date format
    if (!DateTime::createFromFormat('Y-m-d', $date)) {
        error_log('Invalid date format: ' . $date . '. Must be YYYY-MM-DD');
        return false;
    }
    
    // Validate interval
    $validIntervals = ['15m', '1h', '1d'];
    if (!in_array($interval, $validIntervals)) {
        error_log('Invalid interval: ' . $interval . '. Must be one of: ' . implode(', ', $validIntervals));
        return false;
    }
    
    // Check date range (from 1979-01-01 to +15 days from current date)
    $minDate = new DateTime('1979-01-01');
    $maxDate = new DateTime();
    $maxDate->add(new DateInterval('P15D'));
    $requestDate = new DateTime($date);
    
    if ($requestDate < $minDate || $requestDate > $maxDate) {
        error_log('Date out of range: ' . $date . '. Must be between 1979-01-01 and ' . $maxDate->format('Y-m-d'));
        return false;
    }
    
    $cacheKey = md5($lat . $lon . $date . $interval . ($timezone ?? ''));
    $cacheData = getWeatherCache();
    
    // Check if we have cached data that's still valid (use longer cache for solar data - 24 hours)
    $solarCacheDuration = 86400; // 24 hours
    if (isset($cacheData[$cacheKey]) && (time() - $cacheData[$cacheKey]['timestamp']) < $solarCacheDuration) {
        return $cacheData[$cacheKey]['data'];
    }
    
    // Build API URL
    $baseUrl = 'https://api.openweathermap.org/energy/2.0/solar/interval_data';
    $url = $baseUrl . "?lat=" . $lat . "&lon=" . $lon . "&date=" . $date . "&interval=" . $interval . "&appid=" . WEATHER_API_KEY;
    
    // Add timezone if specified
    if ($timezone !== null) {
        $url .= "&tz=" . urlencode($timezone);
    }
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'user_agent' => 'PK Live News Solar Irradiance Widget'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Check for API error response
            if (isset($data['code']) && isset($data['message'])) {
                error_log('Solar irradiance API error: ' . $data['message'] . ' (Code: ' . $data['code'] . ')');
                return false;
            }
            
            // Cache the successful data
            $cacheData[$cacheKey] = [
                'data' => $data,
                'timestamp' => time()
            ];
            saveWeatherCache($cacheData);
            
            return $data;
        } else {
            error_log('JSON decode error for solar irradiance data: ' . json_last_error_msg());
        }
    } else {
        error_log('Failed to fetch solar irradiance data for coordinates: ' . $lat . ',' . $lon . ' on ' . $date);
    }
    
    return false;
}

/**
 * Format solar irradiance data for better display
 * @param array $solarData Raw solar irradiance data from API
 * @return array Formatted solar irradiance data
 */
function formatSolarIrradianceData($solarData) {
    if (!$solarData || !isset($solarData['intervals'])) {
        return [];
    }
    
    $formatted = [
        'location' => [
            'lat' => $solarData['lat'],
            'lon' => $solarData['lon']
        ],
        'date' => $solarData['date'],
        'interval' => $solarData['interval'],
        'timezone' => $solarData['tz'] ?? null,
        'sunrise' => $solarData['sunrise'] ?? null,
        'sunset' => $solarData['sunset'] ?? null,
        'summary' => [
            'total_ghi_clear' => 0,
            'total_ghi_cloudy' => 0,
            'peak_irradiance_clear' => 0,
            'peak_irradiance_cloudy' => 0,
            'average_irradiance_clear' => 0,
            'average_irradiance_cloudy' => 0
        ],
        'intervals' => []
    ];
    
    $totalGhiClear = 0;
    $totalGhiCloudy = 0;
    $peakIrradianceClear = 0;
    $peakIrradianceCloudy = 0;
    $intervalCount = 0;
    
    foreach ($solarData['intervals'] as $interval) {
        $intervalData = [
            'start' => $interval['start'],
            'end' => $interval['end'],
            'avg_irradiance' => $interval['avg_irradiance'],
            'max_irradiance' => $interval['max_irradiance'],
            'irradiation' => $interval['irradiation']
        ];
        
        $formatted['intervals'][] = $intervalData;
        
        // Calculate totals and peaks
        $totalGhiClear += $interval['irradiation']['clear sky']['ghi'];
        $totalGhiCloudy += $interval['irradiation']['cloud sky']['ghi'];
        
        $peakIrradianceClear = max($peakIrradianceClear, $interval['max_irradiance']['clear sky']['ghi']);
        $peakIrradianceCloudy = max($peakIrradianceCloudy, $interval['max_irradiance']['cloud sky']['ghi']);
        
        $intervalCount++;
    }
    
    if ($intervalCount > 0) {
        $formatted['summary']['total_ghi_clear'] = round($totalGhiClear, 2);
        $formatted['summary']['total_ghi_cloudy'] = round($totalGhiCloudy, 2);
        $formatted['summary']['peak_irradiance_clear'] = round($peakIrradianceClear, 2);
        $formatted['summary']['peak_irradiance_cloudy'] = round($peakIrradianceCloudy, 2);
        $formatted['summary']['average_irradiance_clear'] = round($totalGhiClear / $intervalCount, 2);
        $formatted['summary']['average_irradiance_cloudy'] = round($totalGhiCloudy / $intervalCount, 2);
    }
    
    return $formatted;
}

/**
 * Get solar irradiance data for a city (using coordinates lookup)
 * @param string $city City name
 * @param string $date Date in YYYY-MM-DD format
 * @param string $interval Time interval: '15m', '1h', or '1d'
 * @param string $timezone Optional timezone in ±XX:XX format
 * @return array|false Formatted solar irradiance data or false on error
 */
function getSolarIrradianceByCity($city, $date, $interval = '1h', $timezone = null) {
    // First get city coordinates using weather API
    $weatherData = getWeatherData($city);
    
    if (!$weatherData || !isset($weatherData['coord'])) {
        error_log('Could not get coordinates for city: ' . $city);
        return false;
    }
    
    $lat = $weatherData['coord']['lat'];
    $lon = $weatherData['coord']['lon'];
    
    // Get solar irradiance data
    $solarData = getSolarIrradianceData($lat, $lon, $date, $interval, $timezone);
    
    if ($solarData) {
        return formatSolarIrradianceData($solarData);
    }
    
    return false;
}

/**
 * Get comprehensive weather data using One Call API 3.0
 * @param float $lat Latitude
 * @param float $lon Longitude
 * @param array $exclude Optional data blocks to exclude: ['current', 'minutely', 'hourly', 'daily', 'alerts']
 * @param string $units Units: metric (Celsius) or imperial (Fahrenheit)
 * @param string $lang Language code for weather descriptions
 * @return array|false One Call weather data or false on error
 */
function getOneCallWeatherData($lat, $lon, $exclude = [], $units = 'metric', $lang = 'en') {
    // Check if API key is set
    if (!isApiKeyConfigured()) {
        error_log('Weather API key not configured for One Call API');
        return false;
    }
    
    // Validate coordinates
    if ($lat < -90 || $lat > 90) {
        error_log('Invalid latitude: ' . $lat . '. Must be between -90 and 90');
        return false;
    }
    
    if ($lon < -180 || $lon > 180) {
        error_log('Invalid longitude: ' . $lon . '. Must be between -180 and 180');
        return false;
    }
    
    // Validate exclude parameters
    $validExcludes = ['current', 'minutely', 'hourly', 'daily', 'alerts'];
    $filteredExclude = array_intersect($exclude, $validExcludes);
    
    $cacheKey = md5($lat . $lon . implode(',', $filteredExclude) . $units . $lang . '_onecall');
    $cacheData = getWeatherCache();
    
    // Check if we have cached data that's still valid
    if (isset($cacheData[$cacheKey]) && (time() - $cacheData[$cacheKey]['timestamp']) < WEATHER_CACHE_DURATION) {
        return $cacheData[$cacheKey]['data'];
    }
    
    // Build API URL
    $url = WEATHER_ONECALL_URL . "?lat=" . $lat . "&lon=" . $lon . "&appid=" . WEATHER_API_KEY . "&units=" . $units . "&lang=" . $lang;
    
    // Add exclude parameter if not empty
    if (!empty($filteredExclude)) {
        $url .= "&exclude=" . implode(',', $filteredExclude);
    }
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PK Live News One Call Weather Widget'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Check for API error response
            if (isset($data['cod']) && $data['cod'] != 200) {
                error_log('One Call API error: ' . ($data['message'] ?? 'Unknown error') . ' (Code: ' . $data['cod'] . ')');
                return false;
            }
            
            // Cache the successful data
            $cacheData[$cacheKey] = [
                'data' => $data,
                'timestamp' => time()
            ];
            saveWeatherCache($cacheData);
            
            return $data;
        } else {
            error_log('JSON decode error for One Call API data: ' . json_last_error_msg());
        }
    } else {
        error_log('Failed to fetch One Call API data for coordinates: ' . $lat . ',' . $lon);
    }
    
    return false;
}

/**
 * Get One Call weather data for a city (using coordinates lookup)
 * @param string $city City name
 * @param array $exclude Optional data blocks to exclude
 * @param string $units Units: metric or imperial
 * @param string $lang Language code
 * @return array|false One Call weather data or false on error
 */
function getOneCallWeatherByCity($city, $exclude = [], $units = 'metric', $lang = 'en') {
    // First get city coordinates using weather API
    $weatherData = getWeatherData($city);
    
    if (!$weatherData || !isset($weatherData['coord'])) {
        error_log('Could not get coordinates for city: ' . $city);
        return false;
    }
    
    $lat = $weatherData['coord']['lat'];
    $lon = $weatherData['coord']['lon'];
    
    return getOneCallWeatherData($lat, $lon, $exclude, $units, $lang);
}

/**
 * Format One Call API data for better display
 * @param array $oneCallData Raw One Call API data
 * @return array Formatted One Call data
 */
function formatOneCallWeatherData($oneCallData) {
    if (!$oneCallData) {
        return [];
    }
    
    $formatted = [
        'location' => [
            'lat' => $oneCallData['lat'],
            'lon' => $oneCallData['lon'],
            'timezone' => $oneCallData['timezone'] ?? null,
            'timezone_offset' => $oneCallData['timezone_offset'] ?? 0
        ]
    ];
    
    // Current weather
    if (isset($oneCallData['current'])) {
        $current = $oneCallData['current'];
        $formatted['current'] = [
            'datetime' => date('Y-m-d H:i:s', $current['dt']),
            'sunrise' => isset($current['sunrise']) ? date('H:i', $current['sunrise']) : null,
            'sunset' => isset($current['sunset']) ? date('H:i', $current['sunset']) : null,
            'temperature' => round($current['temp']),
            'feels_like' => round($current['feels_like']),
            'pressure' => $current['pressure'],
            'humidity' => $current['humidity'],
            'dew_point' => $current['dew_point'] ?? null,
            'uvi' => $current['uvi'] ?? null,
            'clouds' => $current['clouds'] ?? null,
            'visibility' => isset($current['visibility']) ? $current['visibility'] / 1000 : null,
            'wind_speed' => $current['wind_speed'],
            'wind_direction' => $current['wind_deg'] ?? 0,
            'wind_gust' => $current['wind_gust'] ?? null,
            'weather' => [
                'main' => $current['weather'][0]['main'] ?? 'Unknown',
                'description' => ucfirst($current['weather'][0]['description'] ?? 'Unknown'),
                'icon' => $current['weather'][0]['icon'] ?? '01d'
            ]
        ];
    }
    
    // Minutely forecast (if available)
    if (isset($oneCallData['minutely'])) {
        $formatted['minutely'] = array_map(function($item) {
            return [
                'datetime' => date('Y-m-d H:i', $item['dt']),
                'precipitation' => $item['precipitation'] ?? 0
            ];
        }, $oneCallData['minutely']);
    }
    
    // Hourly forecast
    if (isset($oneCallData['hourly'])) {
        $formatted['hourly'] = array_map(function($item) {
            return [
                'datetime' => date('Y-m-d H:i', $item['dt']),
                'temperature' => round($item['temp']),
                'feels_like' => round($item['feels_like']),
                'pressure' => $item['pressure'],
                'humidity' => $item['humidity'],
                'dew_point' => $item['dew_point'] ?? null,
                'uvi' => $item['uvi'] ?? null,
                'clouds' => $item['clouds'] ?? null,
                'visibility' => isset($item['visibility']) ? $item['visibility'] / 1000 : null,
                'wind_speed' => $item['wind_speed'],
                'wind_direction' => $item['wind_deg'] ?? 0,
                'wind_gust' => $item['wind_gust'] ?? null,
                'pop' => ($item['pop'] ?? 0) * 100, // Precipitation probability
                'weather' => [
                    'main' => $item['weather'][0]['main'] ?? 'Unknown',
                    'description' => ucfirst($item['weather'][0]['description'] ?? 'Unknown'),
                    'icon' => $item['weather'][0]['icon'] ?? '01d'
                ]
            ];
        }, array_slice($oneCallData['hourly'], 0, 24)); // Limit to 24 hours
    }
    
    // Daily forecast
    if (isset($oneCallData['daily'])) {
        $formatted['daily'] = array_map(function($item) {
            return [
                'date' => date('Y-m-d', $item['dt']),
                'datetime' => date('Y-m-d H:i:s', $item['dt']),
                'sunrise' => date('H:i', $item['sunrise']),
                'sunset' => date('H:i', $item['sunset']),
                'moonrise' => isset($item['moonrise']) ? date('H:i', $item['moonrise']) : null,
                'moonset' => isset($item['moonset']) ? date('H:i', $item['moonset']) : null,
                'moon_phase' => $item['moon_phase'] ?? null,
                'temperature' => [
                    'day' => round($item['temp']['day']),
                    'min' => round($item['temp']['min']),
                    'max' => round($item['temp']['max']),
                    'night' => round($item['temp']['night']),
                    'eve' => round($item['temp']['eve']),
                    'morn' => round($item['temp']['morn'])
                ],
                'feels_like' => [
                    'day' => round($item['feels_like']['day']),
                    'night' => round($item['feels_like']['night']),
                    'eve' => round($item['feels_like']['eve']),
                    'morn' => round($item['feels_like']['morn'])
                ],
                'pressure' => $item['pressure'],
                'humidity' => $item['humidity'],
                'dew_point' => $item['dew_point'] ?? null,
                'wind_speed' => $item['wind_speed'],
                'wind_direction' => $item['wind_deg'] ?? 0,
                'wind_gust' => $item['wind_gust'] ?? null,
                'clouds' => $item['clouds'] ?? null,
                'pop' => ($item['pop'] ?? 0) * 100, // Precipitation probability
                'uvi' => $item['uvi'] ?? null,
                'weather' => [
                    'main' => $item['weather'][0]['main'] ?? 'Unknown',
                    'description' => ucfirst($item['weather'][0]['description'] ?? 'Unknown'),
                    'icon' => $item['weather'][0]['icon'] ?? '01d'
                ]
            ];
        }, array_slice($oneCallData['daily'], 0, 7)); // Limit to 7 days
    }
    
    // Weather alerts
    if (isset($oneCallData['alerts'])) {
        $formatted['alerts'] = array_map(function($alert) {
            return [
                'sender_name' => $alert['sender_name'] ?? 'Unknown',
                'event' => $alert['event'] ?? 'Unknown',
                'start' => date('Y-m-d H:i:s', $alert['start']),
                'end' => date('Y-m-d H:i:s', $alert['end']),
                'description' => $alert['description'] ?? '',
                'tags' => $alert['tags'] ?? []
            ];
        }, $oneCallData['alerts']);
    }
    
    return $formatted;
}

/**
 * Get weather description in Urdu (for multilingual support)
 * @param string $description English weather description
 * @return string Urdu weather description
 */
function getWeatherDescriptionUrdu($description) {
    $translations = [
        'clear sky' => 'صاف آسمان',
        'few clouds' => 'چھوٹے بادل',
        'scattered clouds' => 'بکھرے ہوئے بادل',
        'broken clouds' => 'ٹوٹے ہوئے بادل',
        'shower rain' => 'بارش کا شوگر',
        'rain' => 'بارش',
        'thunderstorm' => 'طوفان',
        'snow' => 'برف',
        'mist' => 'دھند',
        'fog' => 'کہر',
        'haze' => 'غبار',
        'dust' => 'دھول',
        'sand' => 'ریت',
        'ash' => 'راکھ',
        'squall' => 'طوفانی ہوا',
        'tornado' => 'طوفان'
    ];
    
    return $translations[strtolower($description)] ?? $description;
}
?>
