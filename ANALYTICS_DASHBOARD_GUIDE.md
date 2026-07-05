# News Data Analytics Dashboard - Implementation Guide

## Overview

A comprehensive analytics dashboard for PK Live News admin panel that provides detailed insights into news performance, user engagement, geographic distribution, and trending topics.

## Features Implemented

### 1. **Analytics Dashboard Page** (`admin/analytics-dashboard.php`)

- **Overview Cards**: Total views, unique articles, countries reached, engagement rate
- **Date Range Selection**: Last 24 hours, 7 days, 30 days, 90 days
- **Real-time Refresh**: Auto-refresh every 30 seconds
- **Export Functionality**: CSV export of all analytics data

### 2. **Most Read News Analytics**

- **Top Performing Articles**: Ranked by views and heat score
- **Category Classification**: Color-coded by news category
- **Geographic Reach**: Number of countries where articles were read
- **Heat Score Integration**: Shows engagement intensity
- **Direct Links**: Click to view articles on live site

### 3. **Category Performance Analytics**

- **Article Count**: Number of articles per category
- **Total Views**: Cumulative views by category
- **Average Engagement**: Views per article ratio
- **Color Coding**: Visual category identification

### 4. **Geographic Distribution**

- **Country Breakdown**: Views by geographic location
- **City Coverage**: Detailed location analytics
- **Heat Mapping**: Visual representation of engagement hotspots
- **Regional Insights**: Performance by geographic area

### 5. **Sentiment Analysis**

- **Overall Sentiment**: Positive, negative, neutral breakdown
- **Source Analysis**: Sentiment by news source
- **Trending Topics**: Most discussed subjects
- **Temporal Analysis**: Sentiment changes over time

### 6. **Real-time Activity Monitoring**

- **Hourly Trends**: Publishing patterns throughout the day
- **Daily Comparisons**: Performance across different days
- **Activity Levels**: High, medium, low engagement indicators
- **Peak Times**: Optimal publishing schedules

## Database Schema

### Core Tables

```sql
-- Analytics main table
CREATE TABLE `news_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `view_count` int(11) DEFAULT 0,
  `share_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `location_country` varchar(100),
  `location_city` varchar(100),
  `hour_of_day` int(2),
  `day_of_week` int(1),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE
);
```

## API Endpoints

### 1. Analytics Data API (`api/analytics-data.php`)

**Endpoints:**

- `GET ?action=analytics_data&date_range=7d` - Main analytics data
- `GET ?action=most_read&date_range=30d` - Top articles
- `GET ?action=category_stats&date_range=7d` - Category performance
- `GET ?action=geographic&date_range=30d` - Location data
- `GET ?action=sentiment&date_range=24h` - Sentiment analysis
- `GET ?action=hourly_activity&date_range=7d` - Hourly trends

**Response Format:**
```json
{
  "success": true,
  "data": {
    "total_views": 15000,
    "unique_articles": 250,
    "countries_reached": 45,
    "engagement_rate": 8.5
  }
}
```

### 2. Analytics Stats API (`api/analytics-stats.php`)

**Endpoints:**

- `GET ?stats=1` - Real-time statistics
- `GET ?refresh=1` - Force data refresh

**Response Format:**
```json
{
  "total_published": 1250,
  "today": 45,
  "this_hour": 8
}
```

### 3. Export Analytics API (`api/export-analytics.php`)

**Endpoints:**

- `GET ?format=csv&date_range=30d` - CSV export
- `GET ?format=json&date_range=90d` - JSON export

**Export Fields:**

- Article ID, Title, Category, Views, Shares, Comments
- Location data, Timestamps, Engagement metrics

## Frontend Implementation

### JavaScript Functions

```javascript
// Main analytics dashboard initialization
function initAnalyticsDashboard() {
    loadAnalyticsData('7d');
    setupDateRangeHandlers();
    startAutoRefresh();
}

// Load analytics data with date range
function loadAnalyticsData(dateRange) {
    fetch(`api/analytics-data.php?action=analytics_data&date_range=${dateRange}`)
        .then(response => response.json())
        .then(data => updateDashboardUI(data));
}

// Real-time updates
function startAutoRefresh() {
    setInterval(() => {
        updateRealTimeStats();
        refreshAnalyticsData();
    }, 30000); // 30 seconds
}
```

### UI Components

- **Overview Cards**: Responsive grid layout with animated counters
- **Chart Containers**: Canvas elements for Chart.js integration
- **Data Tables**: Sortable, filterable data displays
- **Export Buttons**: CSV/JSON download functionality

## Configuration

### Date Range Options

```php
$date_ranges = [
    '1d' => ['start' => 'today 00:00:00', 'end' => 'today 23:59:59'],
    '7d' => ['start' => '-7 days', 'end' => 'today 23:59:59'],
    '30d' => ['start' => '-30 days', 'end' => 'today 23:59:59'],
    '90d' => ['start' => '-90 days', 'end' => 'today 23:59:59']
];
```

### Performance Metrics

```php
function calculateEngagementRate($views, $interactions) {
    if ($views == 0) return 0;
    return ($interactions / $views) * 100;
}

function getHeatScore($views, $shares, $comments) {
    $weights = [
        'views' => 0.4,
        'shares' => 0.3,
        'comments' => 0.3
    ];
    
    return ($views * $weights['views']) + 
           ($shares * $weights['shares']) + 
           ($comments * $weights['comments']);
}
```

## Security Considerations

- **Admin Authentication**: All endpoints require admin login
- **Input Validation**: Sanitize all date ranges and parameters
- **Rate Limiting**: Prevent abuse of analytics endpoints
- **Data Privacy**: No personal user data exposure

## Performance Optimization

- **Database Indexing**: Proper indexes on analytics tables
- **Caching Strategy**: Redis/Memcached for frequent queries
- **Lazy Loading**: Progressive data loading for large datasets
- **CDN Integration**: Static assets served via CDN

## Troubleshooting

### Common Issues

1. **Data Not Loading**
   - Check database connections
   - Verify API endpoint URLs
   - Ensure admin authentication

2. **Charts Not Displaying**
   - Verify Chart.js library loading
   - Check canvas element existence
   - Validate data format

3. **Export Not Working**
   - Check file permissions
   - Verify headers configuration
   - Test with different date ranges

### Debug Mode

Enable debug mode by adding `?debug=1` to analytics URLs:

```php
if (isset($_GET['debug'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
```

## Future Enhancements

### Planned Features

1. **Real-time WebSocket Updates**
2. **Advanced Filtering Options**
3. **Custom Date Range Picker**
4. **Mobile-Optimized Views**
5. **Automated Report Generation**

### Integration Points

- **User Management System**: Link analytics to user accounts
- **Content Management System**: Track content performance
- **Notification System**: Alert on significant events
- **Backup System**: Regular analytics data backup
