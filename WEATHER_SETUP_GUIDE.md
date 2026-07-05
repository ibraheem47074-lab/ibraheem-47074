# Weather Feature Setup Guide

## ✅ SETUP COMPLETE

The weather feature is now fully configured and ready to use!

## Overview
PK Live News includes a comprehensive weather feature that provides real-time weather information, forecasts, and weather-related news integration.

## Features Included
- **Weather Widget**: Displays current weather in sidebar (homepage and news pages)
- **Weather Search Page**: Full weather details with 5-day forecast at `weather.php`
- **Header Weather Search**: Quick weather search from navigation
- **Weather Caching**: 30-minute cache for performance
- **Responsive Design**: Mobile-friendly weather widgets
- **Multiple Units**: Support for Celsius and Fahrenheit
- **Default Cities**: Quick access to major Pakistani cities
- **Location-based Weather**: GPS-based weather detection
- **Weather Widget Component**: Reusable widget for any page

## Current Status
✅ **API Key**: Configured and active  
✅ **Cache Directory**: Set up and writable  
✅ **Weather Page**: Fully functional at `weather.php`  
✅ **API Endpoints**: All weather APIs working  
✅ **CSS Styling**: Complete responsive design  
✅ **Widget Component**: Ready for integration  

## Access Points

### Main Weather Page
Visit: `http://localhost/PK-LIVE%20NEWS/weather.php`

### Weather Widget
Include on any page:
```php
<?php include 'components/weather_widget.php'; ?>
```

### Test Page
Check status: `http://localhost/PK-LIVE%20NEWS/test_weather.php`

## Usage Examples

### Basic Weather Widget
```php
<?php include 'components/weather_widget.php'; ?>
```

### Custom City Weather
```php
<?php 
$_GET['widget_city'] = 'Karachi';
include 'components/weather_widget.php'; 
?>
```

### Fahrenheit Units
```php
<?php 
$_GET['widget_units'] = 'imperial';
include 'components/weather_widget.php'; 
?>
```

## Supported Cities
The weather system works with any city worldwide, with special handling for:
- Islamabad
- Karachi  
- Lahore
- Peshawar
- Quetta
- Rawalpindi
- Faisalabad
- Multan
- Gujranwala
- Sialkot

## Features Available

### Weather Search Page (`weather.php`)
- City weather search
- Current conditions display
- 5-day weather forecast
- GPS location detection
- Unit conversion (C/F)
- Quick access city buttons
- Weather-related news integration

### Weather Widget (`components/weather_widget.php`)
- Compact weather display
- Customizable city and units
- Responsive design
- Error handling
- Link to full weather page

### API Integration
- OpenWeatherMap API configured
- 30-minute caching system
- Error handling and fallbacks
- Coordinate-based weather lookup
- Forecast data retrieval

## Troubleshooting

### If Weather Shows "Not Configured"
1. Check API key in `config/weather.php`
2. Verify internet connection
3. Test with `test_weather.php`

### If Weather Data Not Found
1. Check city name spelling
2. Try alternative city names
3. Use GPS location feature

### Cache Issues
Clear cache by deleting `cache/weather_cache.json`

## Documentation
- **Widget Usage**: See `WEATHER_WIDGET_USAGE.md`
- **API Details**: See `config/weather.php` comments
- **Test Results**: Run `test_weather.php`

## Next Steps
The weather system is ready to use! You can:
1. Add weather widgets to any page
2. Customize the widget display
3. Integrate with news categories
4. Add weather alerts (future feature)

1. Visit your website homepage
2. Check the sidebar for the weather widget
3. Click the "Weather" link in navigation
4. Try searching for weather in the header search dropdown

## API Usage Limits

**Free Plan (OpenWeatherMap):**
- 60 calls per minute
- 1,000 calls per day
- Current weather data
- 5-day weather forecast

**For production use, consider upgrading to a paid plan.**

## Customization Options

### Change Default City
Edit `config/weather.php`:

```php
function getUserLocationCity() {
    // Change 'Islamabad' to your preferred default city
    return 'Karachi'; // or any other city
}
```

### Modify Cache Duration
Edit `config/weather.php`:

```php
// Change cache duration (in seconds)
define('WEATHER_CACHE_DURATION', 1800); // 30 minutes

// Examples:
define('WEATHER_CACHE_DURATION', 900);  // 15 minutes
define('WEATHER_CACHE_DURATION', 3600); // 1 hour
```

### Add More Default Cities
Edit `config/weather.php`:

```php
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
        'Sialkot' => 'PK',
        // Add more cities here:
        'New York' => 'US',
        'London' => 'GB',
        'Dubai' => 'AE'
    ];
}
```

## File Structure

```
PK-LIVE NEWS/
├── config/
│   └── weather.php              # Weather API configuration and functions
├── assets/
│   └── css/
│       └── weather.css          # Weather widget styles
├── weather.php                  # Weather search page
├── index.php                   # Homepage (with weather widget)
├── news.php                    # News pages (with weather widget)
├── includes/
│   └── header.php              # Header (with weather search)
└── cache/
    └── weather_cache.json      # Weather data cache (auto-generated)
```

## Troubleshooting

### Weather Not Showing
1. Check if API key is correctly set in `config/weather.php`
2. Verify cache directory permissions
3. Check PHP error logs for API connection issues
4. Test API key manually: `https://api.openweathermap.org/data/2.5/weather?q=Islamabad&appid=YOUR_API_KEY&units=metric`

### Cache Issues
1. Delete `cache/weather_cache.json` to clear cache
2. Ensure cache directory is writable
3. Check file permissions

### API Rate Limiting
1. Wait for rate limit to reset (free plan: 60 calls/minute)
2. Consider increasing cache duration
3. Upgrade to paid API plan for higher limits

## Advanced Features

### Weather-Based News Integration
You can extend the system to show weather-related news based on current conditions:

```php
// Example: Show rain-related news when it's raining
if ($weatherData['description'] == 'rain') {
    // Query for flood, rain, weather-related news
    $weatherNewsQuery = "SELECT * FROM news WHERE title LIKE '%rain%' OR title LIKE '%weather%' OR title LIKE '%flood%'";
}
```

### Geolocation Support
The system is ready for geolocation integration. You can add JavaScript to detect user location:

```javascript
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(position => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        // Use coordinates to get weather
    });
}
```

### Multilingual Weather
The system includes Urdu translation support:

```php
// Use Urdu descriptions for multilingual sites
$urduDescription = getWeatherDescriptionUrdu($weatherData['description']);
```

## Security Considerations

1. **API Key Security**: Never expose your API key in client-side JavaScript
2. **Input Validation**: All city names are properly sanitized
3. **Rate Limiting**: Built-in caching prevents excessive API calls
4. **Error Handling**: Graceful fallback when API is unavailable

## Performance Optimization

1. **Caching**: 30-minute cache reduces API calls
2. **Lazy Loading**: Weather widgets load after main content
3. **Minimal Data**: Only fetch necessary weather fields
4. **Compression**: CSS and JavaScript are minified

## Support

For issues related to:
- **API Keys**: Contact OpenWeatherMap support
- **Code Issues**: Check the implementation in `config/weather.php`
- **Styling Issues**: Modify `assets/css/weather.css`
- **Cache Issues**: Check cache directory permissions

## Future Enhancements

Potential features to add:
- Weather alerts and warnings
- Historical weather data
- Weather maps integration
- Extended 14-day forecasts
- Weather-based content recommendations
- Mobile app integration
- Weather API fallbacks (multiple providers)

---

**Note**: This weather feature is fully integrated with your existing PK Live News system and maintains the same design language and user experience standards.
