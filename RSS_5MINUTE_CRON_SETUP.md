# RSS Auto-Import Setup - 5 Minute Intervals

## Overview
This guide shows how to set up automatic RSS news import that runs every 5 minutes and saves news as drafts for admin review.

## Features
- ✅ Runs automatically every 5 minutes
- ✅ Imports news from all configured RSS sources
- ✅ Saves all news as **draft** status (not published)
- ✅ Downloads images and generates AI images when needed
- ✅ Prevents duplicate articles
- ✅ Includes source attribution and copyright compliance
- ✅ Detailed logging for monitoring

## Cron Job Setup

### Method 1: Linux/Mac Cron (Recommended)

1. **Open crontab editor:**
   ```bash
   crontab -e
   ```

2. **Add the following line:**
   ```bash
   */5 * * * * /usr/bin/php /path/to/your/pk-live-news/cron_import_news.php >> /path/to/your/pk-live-news/logs/cron.log 2>&1
   ```

3. **Replace paths:**
   - `/path/to/your/pk-live-news/` → Your actual website path
   - Example: `/var/www/html/pk-live-news/`

4. **Save and exit** (Ctrl+X, then Y, then Enter in nano)

### Method 2: Windows Task Scheduler

1. **Open Task Scheduler**
2. **Create Basic Task**
3. **Name:** "PK Live News RSS Import"
4. **Trigger:** Daily, repeat every 5 minutes
5. **Action:** Start a program
   - Program: `php.exe` (full path to PHP)
   - Arguments: `cron_import_news.php`
   - Start in: Your website directory path

### Method 3: Web-based Cron (cPanel/Plesk)

1. **Access your hosting control panel**
2. **Find "Cron Jobs" section**
3. **Add new cron job:**
   - **Command:** `wget -O /dev/null http://your-domain.com/cron_import_news.php?cron_key=pk_live_news_2024_cron`
   - **Schedule:** Every 5 minutes
   - **Or use:** `curl http://your-domain.com/cron_import_news.php?cron_key=pk_live_news_2024_cron > /dev/null 2>&1`

## Security Key

The cron job uses a security key to prevent unauthorized access:
- **Key:** `pk_live_news_2024_cron`
- **URL:** `http://your-domain.com/cron_import_news.php?cron_key=pk_live_news_2024_cron`

## RSS Sources Configuration

Make sure you have RSS sources configured in your database:

```sql
-- Check existing RSS sources
SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active';

-- Add new RSS source example
INSERT INTO news_sources (name, url, type, category_id, status, created_at) 
VALUES ('BBC News', 'http://feeds.bbci.co.uk/news/rss.xml', 'rss', 1, 'active', NOW());
```

## Monitoring

### Log Files
- **Main log:** `logs/cron_import.log`
- **Error log:** PHP error log
- **Access log:** Web server access log

### Check Recent Activity
```bash
# View last 50 lines of cron log
tail -50 logs/cron_import.log

# View live updates
tail -f logs/cron_import.log
```

### Database Monitoring
```sql
-- Check recent draft articles
SELECT id, title, created_at, source_url 
FROM news 
WHERE status = 'draft' AND news_type = 'rss_import' 
ORDER BY created_at DESC 
LIMIT 10;

-- Check import statistics
SELECT 
    COUNT(*) as total_drafts,
    DATE(created_at) as import_date
FROM news 
WHERE status = 'draft' AND news_type = 'rss_import' 
GROUP BY DATE(created_at) 
ORDER BY import_date DESC;
```

## Admin Review Process

1. **Login to admin panel**
2. **Go to "Manage News"**
3. **Filter by status "Draft"**
4. **Review articles:**
   - Edit content if needed
   - Add better images
   - Adjust categories
   - Fix titles/excerpts
5. **Publish approved articles**

## Troubleshooting

### Common Issues

1. **Cron not running:**
   - Check cron service: `service cron status`
   - Verify PHP path: `which php`
   - Test manually: `php cron_import_news.php`

2. **Permission errors:**
   ```bash
   chmod 755 cron_import_news.php
   chmod 777 logs/
   chmod 666 logs/cron_import.log
   ```

3. **Database connection:**
   - Check database credentials in `config/database.php`
   - Test connection: `php -f config/database.php`

4. **RSS feed errors:**
   - Test RSS URLs manually in browser
   - Check feed validity: `https://validator.w3.org/feed/`
   - Update source status in database

### Debug Mode
Add debug parameter to test manually:
```bash
php cron_import_news.php debug
```

Or via web:
```
http://your-domain.com/cron_import_news.php?cron_key=pk_live_news_2024_cron&debug=1
```

## Performance Optimization

1. **Limit articles per feed:** Already set to 5 articles per feed
2. **Execution time:** 5 minutes limit
3. **Cleanup:** Old articles (30 days) auto-deleted
4. **Memory:** Monitor server memory usage

## Customization Options

### Change Import Frequency
Edit cron schedule:
- **Every 1 minute:** `* * * * *`
- **Every 5 minutes:** `*/5 * * * *`
- **Every 10 minutes:** `*/10 * * * *`
- **Every hour:** `0 * * * *`

### Change Articles Per Feed
Edit `cron_import_news.php` line 54:
```php
$importer->setMaxArticlesPerFeed(10); // Change from 5 to 10
```

### Change Cleanup Period
Edit `cron_import_news.php` line 98:
```php
$deleteQuery = "DELETE FROM news WHERE news_type = 'rss_import' AND created_at < DATE_SUB(NOW(), INTERVAL 60 DAY)";
```

## Email Notifications (Optional)

To receive error notifications, edit `cron_import_news.php` line 127:
```php
// Uncomment and configure
mail($to, $subject, $message);
```

## Support

For issues:
1. Check logs first
2. Test RSS feeds manually
3. Verify database connection
4. Check PHP error logs
5. Monitor server resources

---

**Note:** This system imports news as drafts to ensure quality control. Always review content before publishing to maintain editorial standards.
