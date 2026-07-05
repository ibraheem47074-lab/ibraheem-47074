# Automatic News Display Setup Guide

## Overview
This system automatically displays news from RSS feeds on your index page. The setup includes:

1. **RSS Source Management** - Manages multiple news sources with different categories
2. **Automated Scraping** - Scrapes RSS feeds at regular intervals
3. **Auto-Publishing** - Automatically publishes quality articles
4. **Real-time Display** - Shows scraped news on the index page with visual indicators

## Files Created

### Core Scripts
- `setup_rss_sources.php` - Sets up RSS news sources in the database
- `enhanced_rss_scraper.php` - Enhanced RSS feed scraper
- `auto_publish_news.php` - Auto-publishes quality scraped articles
- `auto_refresh_news.php` - Combined scraping and publishing endpoint

### Modified Files
- `index.php` - Enhanced to display scraped news with visual indicators

## Setup Instructions

### Step 1: Set Up RSS Sources
Run this script to populate your news sources table:

```bash
php setup_rss_sources.php
```

This will add the following RSS sources:
- **Pakistan Politics**: BBC News Pakistan, Dawn News Pakistan, Geo News Pakistan, ARY News Pakistan, The Express Tribune
- **International News**: BBC News, CNN, Reuters, Al Jazeera  
- **Pakistan Sports**: Dawn News Sports, Geo News Sports, ARY News Sports

### Step 2: Test RSS Scraping
Test the RSS scraper:

```bash
php enhanced_rss_scraper.php
```

### Step 3: Test Auto-Publishing
Test the auto-publishing system:

```bash
php auto_publish_news.php
```

### Step 4: Test Combined System
Test the complete system:

```bash
php auto_refresh_news.php
```

## Features

### Visual Indicators on Index Page
- **Green "External" Badge**: Shows articles from RSS feeds
- **Refresh Button**: Manual refresh to get latest news
- **Auto-refresh**: Automatically checks for new articles every 5 minutes
- **Notifications**: Shows alerts when new articles are available

### Article Quality Control
- **Minimum Length**: Articles must have at least 20 characters in title and 200 in content
- **Duplicate Detection**: Prevents duplicate articles
- **Sentiment Analysis**: Filters out extremely negative content
- **Source Verification**: Prioritizes reputable news sources

### Categories Supported
- **Politics**: Pakistan political news from major sources
- **World**: International news from global sources  
- **Sports**: Pakistan sports news coverage

## Automation

### Manual Refresh
Users can click the "Refresh News" button on the index page to immediately scrape and publish new articles.

### Automatic Refresh
The system automatically:
1. Checks for new articles every 5 minutes
2. Shows notifications when new content is available
3. Updates the news display without page reload

### Cron Job Setup
For fully automated operation, set up a cron job:

```bash
# Run every 15 minutes
*/15 * * * * php /path/to/auto_refresh_news.php

# Or run scraper and publisher separately
*/30 * * * * php /path/to/enhanced_rss_scraper.php
*/45 * * * * php /path/to/auto_publish_news.php
```

## Database Tables Used

### news_sources
Stores RSS feed information including:
- Source name and URL
- Category assignment
- Scraping frequency
- Last scraped timestamp
- Error tracking

### news (enhanced)
Enhanced to support:
- Source URL tracking
- News type identification (internal/external)
- Sentiment analysis results
- Auto-publishing workflow

## Configuration Options

### Scraping Frequency
- **Pakistan Sources**: Every 60 minutes
- **International Sources**: Every 30 minutes
- **Sports Sources**: Every 60 minutes

### Publishing Criteria
- Title length: ≥ 20 characters
- Content length: ≥ 200 characters
- Image requirement: Optional for longer articles
- Sentiment threshold: > -0.7 (filters extreme negative content)

### Rate Limiting
- Maximum 3 articles per source per refresh
- 2-second delay between sources
- 15-second timeout for RSS requests

## Troubleshooting

### Common Issues

1. **No articles appearing**
   - Check if RSS sources are set up: `php setup_rss_sources.php`
   - Test scraper: `php enhanced_rss_scraper.php`
   - Check database connection

2. **Articles not publishing**
   - Run auto-publish: `php auto_publish_news.php`
   - Check article quality criteria
   - Review error logs

3. **RSS feed errors**
   - Verify RSS URLs are accessible
   - Check network connectivity
   - Review source error counts in database

### Debug Mode
Add error reporting to debug issues:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Performance Considerations

### Optimization
- Limits sources per refresh to prevent timeouts
- Implements respectful delays between requests
- Caches results to reduce database load
- Uses efficient queries with proper indexing

### Scalability
- Supports unlimited RSS sources
- Handles high-frequency scraping
- Manages large article volumes
- Maintains performance with proper indexing

## Security Features

### Input Validation
- Sanitizes all RSS content
- Validates URLs and images
- Prevents XSS attacks
- Escapes HTML entities

### Access Control
- Requires database authentication
- Limits request frequency
- Monitors for abuse
- Logs all activities

## Future Enhancements

### Planned Features
- Machine learning for article categorization
- Social media integration
- Email notifications for breaking news
- Mobile app integration
- Advanced analytics dashboard

### Customization Options
- Custom RSS source management
- Adjustable quality criteria
- Customizable display layouts
- Multi-language support
- Theme customization

This system provides a complete solution for automatically displaying news from RSS feeds on your website, with robust quality control, user-friendly features, and reliable automation.
