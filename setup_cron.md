# Automated Web Scraping Setup Guide

This comprehensive guide will help you set up automated web scraping for your PK Live News system using cron jobs.

## System Overview

Your PK Live News system already includes:
- ✅ **Web Scraper Class** (`includes/web_scraper.php`) - Advanced content extraction
- ✅ **Admin Interface** - Manage sources and manual scraping
- ✅ **Database Schema** - News sources table with scheduling
- ✅ **Automated Script** (`auto_scrape.php`) - Ready for cron execution
- ✅ **Sentiment Analysis** - Automatic sentiment analysis on scraped content
- ✅ **Image Processing** - Download and process article images
- ✅ **Duplicate Detection** - Prevent duplicate articles

## Prerequisites

1. **PHP CLI**: Ensure PHP command line interface is installed
2. **Database Setup**: News sources table must be installed
3. **Web Server**: Apache/Nginx with PHP support
4. **cURL Extension**: Must be enabled in PHP
5. **Memory**: Recommended 256MB+ for scraping multiple sources

### Linux/Mac Setup

Add this to your crontab by running `crontab -e`:

```bash
# Run automated scraping every 30 minutes
*/30 * * * * /usr/bin/php /path/to/your/website/auto_scrape.php >> /path/to/logs/scraping.log 2>&1
```

### Windows Setup

Use Windows Task Scheduler:

1. Open Task Scheduler
2. Create Basic Task
3. Set trigger to repeat every 30 minutes
4. Action: Start a program
   - Program: `php.exe`
   - Arguments: `C:\path\to\your\website\auto_scrape.php`
   - Start in: `C:\path\to\your\website\`

## Configuration Options

### Scraping Frequency

Edit the frequency in the cron command:
- Every 15 minutes: `*/15 * * * *`
- Every 30 minutes: `*/30 * * * *`
- Every hour: `0 * * * *`
- Every 6 hours: `0 */6 * * *`

### Logging

The script outputs detailed logs including:
- Sources processed
- Articles imported
- Errors encountered
- Execution time

### Email Notifications

Uncomment the mail() function in `auto_scrape.php` to receive email notifications when new articles are scraped.

## Security Considerations

1. **Access Control**: The script should be placed outside web-accessible directory
2. **Rate Limiting**: Built-in delays between sources to respect website policies
3. **Duplicate Detection**: Automatic duplicate prevention based on title and content
4. **Manual Review**: Scraped articles are saved as draft for admin review

## Monitoring

Check the log file regularly:
```bash
tail -f /path/to/logs/scraping.log
```

## Troubleshooting

### Common Issues

1. **cURL not enabled**: Ensure PHP cURL extension is installed
2. **Memory limits**: Increase `memory_limit` in php.ini if needed
3. **Time limits**: Script has built-in 5-minute limit
4. **Permission issues**: Ensure write permissions for uploads directory

### Testing

Run manually first:
```bash
php auto_scrape.php
```

## Sample Log Output

```
[2024-03-08 10:30:00] Starting automated scraping...
Found 3 sources to scrape

Processing: BBC News (http://feeds.bbci.co.uk/news/rss.xml)
  + Imported: Breaking: Major announcement... (positive)
  + Imported: Technology: New developments... (neutral)
  - Duplicate: Sports: Latest results...
  Imported 2 articles from BBC News

Processing: TechCrunch (https://techcrunch.com/feed/)
  + Imported: Startup raises funding... (positive)
  Imported 1 articles from TechCrunch

=== Scraping Summary ===
Sources processed: 2/3
Articles imported: 3
Errors: 0
Completed: 2024-03-08 10:35:12
```

## Performance Tips

1. **Limit Sources**: Start with 3-5 reliable sources
2. **Frequency**: Don't scrape more often than needed
3. **Server Load**: Monitor server resources
4. **Storage**: Regularly clean up old images and logs

## API Alternative

For more reliable scraping, consider using news APIs:
- NewsAPI.org
- Guardian API
- New York Times API
- Reuters API

These provide structured data and are more reliable than web scraping.
