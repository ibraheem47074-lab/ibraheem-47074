# Weather Widget Usage Guide

## Overview
The weather widget can be embedded on any page of the PK Live News website to display current weather information.

## Basic Usage
```php
<?php include 'components/weather_widget.php'; ?>
```

## Advanced Options
You can customize the widget by passing GET parameters:

```php
<?php 
$_GET['widget_city'] = 'Karachi';
$_GET['widget_units'] = 'metric';
$_GET['show_details'] = true;
include 'components/weather_widget.php'; 
?>
```

## Parameters

- `widget_city`: City name (default: Islamabad)
- `widget_units`: Units system - 'metric' or 'imperial' (default: metric)
- `show_details`: Show detailed weather info - true/false (default: true)

## Examples

### 1. Basic Weather Widget
```php
<?php include 'components/weather_widget.php'; ?>
```

### 2. Weather for Karachi
```php
<?php 
$_GET['widget_city'] = 'Karachi';
include 'components/weather_widget.php'; 
?>
```

### 3. Fahrenheit Units
```php
<?php 
$_GET['widget_units'] = 'imperial';
include 'components/weather_widget.php'; 
?>
```

### 4. Simple Widget (No Details)
```php
<?php 
$_GET['show_details'] = false;
include 'components/weather_widget.php'; 
?>
```

### 5. Custom City with Fahrenheit
```php
<?php 
$_GET['widget_city'] = 'Lahore';
$_GET['widget_units'] = 'imperial';
include 'components/weather_widget.php'; 
?>
```

## Integration in Homepage
To add weather to the homepage, edit the appropriate section in `index.php`:

```php
<div class="col-md-4">
    <?php include 'components/weather_widget.php'; ?>
</div>
```

## Styling
The widget uses the weather.css styles and is fully responsive. It includes:
- Gradient backgrounds
- Hover effects
- Weather icons
- Responsive design for mobile devices
- Dark mode support

## API Configuration
The widget requires the OpenWeatherMap API key to be configured in `config/weather.php`. The API key is already set up and working.

## Supported Cities
The widget works with any city worldwide, but has special handling for Pakistani cities:
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

## Error Handling
If the weather service is not configured or the API fails, the widget will display:
- A warning icon
- Error message
- Link to the full weather page

## Cache
Weather data is cached for 30 minutes to improve performance and reduce API calls.
