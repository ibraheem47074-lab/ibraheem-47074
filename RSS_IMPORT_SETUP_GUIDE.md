# RSS Auto-Import Setup Guide - Windows/XAMPP

## Overview
This guide sets up automatic RSS news import that runs every 5 minutes and saves news as drafts for admin review. The system works both online and offline - it will continue running every 5 minutes regardless of network status.

## Features
- ✅ Runs automatically every 5 minutes via Windows Task Scheduler
- ✅ Imports news from all configured RSS sources
- ✅ Saves all news as **draft** status (not published)
- ✅ Offline resilience - continues running even if network fails
- ✅ Fast timeouts to prevent hanging (5-8 seconds per feed)
- ✅ Prevents duplicate articles
- ✅ Detailed logging for monitoring
- ✅ Downloads images and generates AI images when needed

## Current Configuration
- **Import Frequency**: Every 5 minutes
- **Articles per Feed**: 5 articles
- **Status**: Draft (requires admin approval before publishing)
- **Timeout**: 8 seconds total, 5 seconds connection
- **Cleanup**: Deletes articles older than 30 days

## Setup Instructions

### Step 1: Run Windows Task Scheduler Setup
1. Right-click on `setup_windows_task_scheduler.bat`
2. Select **"Run as administrator"**
3. The script will automatically:
   - Detect your PHP installation
   - Create a scheduled task named "PK Live News RSS Import"
   - Configure it to run every 5 minutes
   - Set it to run with highest privileges

### Step 2: Test the Import System
1. Double-click `test_rss_import.bat`
2. This will run the import script once manually
3. Check the results in `logs/cron_import.log`

### Step 3: Verify Task Scheduler
1. Open **Task Scheduler** (press Win+R, type `taskschd.msc`)
2. Look for task named "PK Live News RSS Import"
3. Verify it shows "Ready" status
4. Check the "Triggers" tab - should show "Every 5 minutes"

### Step 4: Monitor the System
- **Log File**: `logs/cron_import.log`
- **Task Scheduler**: View task history in Task Scheduler
- **Database**: Check `news` table for imported articles with `status = 'draft'`

## Manual Import (Anytime)
You can manually trigger RSS import at any time:
- **Option 1**: Run `test_rss_import.bat`
- **Option 2**: Open browser: `http://localhost/pk-live-news/cron_import_news.php?cron_key=pk_live_news_2024_cron`
- **Option 3**: Run in Task Scheduler by right-clicking the task and selecting "Run"

## Offline/Online Behavior

### Online Mode
- System fetches RSS feeds from external sources
- Downloads images and processes content
- Saves articles as drafts in database
- Logs successful imports and any errors

### Offline Mode
- System continues running every 5 minutes
- Network timeouts occur quickly (5-8 seconds)
- Failed feeds are logged but don't stop the process
- System continues to next feed
- No articles imported (expected behavior offline)
- Logs show network errors for troubleshooting

## Admin Review Process

1. **Login to admin panel**
2. **Go to "Manage News"**
3. **Filter by status "Draft"**
4. **Review articles:**
   - Edit content if needed
   - Add better images
   - Adjust categories
   - Fix titles/excerpts
5. **Publish approved articles** (change status to "published")

## Troubleshooting

### Task Not Running
- Open Task Scheduler and check task status
- Ensure task is "Enabled" (right-click → Enable if disabled)
- Check task history for error messages
- Verify PHP path is correct in the task

### No Articles Being Imported
- Check `logs/cron_import.log` for errors
- Verify internet connectivity (run `connectivity_test.php` in browser)
- Check if RSS sources are configured in database
- Test RSS URLs manually in browser

### Network Connectivity Issues
- Run `connectivity_test.php` in browser to diagnose
- Check firewall settings
- Verify DNS configuration
- Test with different RSS feeds

### Permission Errors
- Ensure XAMPP has write permissions for `logs/` directory
- Ensure PHP has write permissions for `uploads/` directory

## Configuration Changes

### Change Import Frequency
Edit `setup_windows_task_scheduler.bat`, change line with `/MO 5` to desired minutes:
- Every 1 minute: `/MO 1`
- Every 10 minutes: `/MO 10`
- Every 30 minutes: `/MO 30`

### Change Articles Per Feed
Edit `cron_import_news.php` line 54:
```php
$importer->setMaxArticlesPerFeed(10); // Change from 5 to 10
```

### Change Cleanup Period
Edit `cron_import_news.php` line 99:
```php
$deleteQuery = "DELETE FROM news WHERE news_type = 'rss_import' AND created_at < DATE_SUB(NOW(), INTERVAL 60 DAY)";
```

## Database Monitoring

### Check Recent Drafts
```sql
SELECT id, title, created_at, source_url 
FROM news 
WHERE status = 'draft' AND news_type = 'rss_import' 
ORDER BY created_at DESC 
LIMIT 10;
```

### Check Import Statistics
```sql
SELECT 
    COUNT(*) as total_drafts,
    DATE(created_at) as import_date
FROM news 
WHERE status = 'draft' AND news_type = 'rss_import' 
GROUP BY DATE(created_at) 
ORDER BY import_date DESC;
```

## Security Notes
- The cron job uses a security key: `pk_live_news_2024_cron`
- Web-based access requires this key to prevent unauthorized execution
- Task runs as SYSTEM user with highest privileges
- Ensure your database credentials are secure

## Performance
- Maximum execution time: 3 minutes per run
- Fast timeouts prevent hanging
- Limits articles per feed to avoid overwhelming
- Old articles automatically cleaned up

## Support
For issues:
1. Check `logs/cron_import.log` first
2. Test RSS feeds manually in browser
3. Verify database connection
4. Check Task Scheduler history
5. Run `connectivity_test.php` for network diagnosis

---

**Note**: This system imports news as drafts to ensure quality control. Always review content before publishing to maintain editorial standards.
