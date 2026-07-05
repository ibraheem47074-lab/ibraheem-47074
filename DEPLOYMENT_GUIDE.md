# PK Live News - Deployment Configuration Guide

## 🚀 Quick Deployment Steps

### 1. Environment Setup

1. **Copy Environment File**
   ```bash
   cp .env.example .env
   ```

2. **Update .env for Production**
   ```env
   DB_HOST=your-production-host
   DB_USER=your-db-user
   DB_PASS=your-secure-password
   DB_NAME=pk_live_news
   
   SITE_URL=https://yourwebsite.com/
   SITE_NAME=PK Live News
   APP_ENV=production
   ```

### 2. Database Configuration

1. **Import Database**
   ```bash
   mysql -u username -p database_name < import.sql
   ```

2. **Update Database Credentials** in `.env`

### 3. File Permissions

```bash
# Upload folders
chmod 755 uploads/
chmod 755 uploads/ads/
chmod 755 uploads/avatars/
chmod 755 uploads/categories/
chmod 755 uploads/editions/

# Cache and logs
chmod 755 cache/
chmod 755 logs/

# Config files (read-only for web server)
chmod 644 config/database.php
chmod 644 .env
```

### 4. Server Configuration

1. **Apache Setup**
   - Copy `.htaccess.production` to `.htaccess`
   - Enable mod_rewrite
   - Configure SSL certificate

2. **PHP Settings** (in .htaccess or php.ini)
   ```ini
   display_errors = Off
   error_log = /path/to/logs/php_errors.log
   max_execution_time = 30
   memory_limit = 256M
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

### 5. Security Configuration

1. **Update .htaccess.production** with your domain
2. **Configure SSL** (HTTPS already forced in .htaccess)
3. **Set up security headers** (already included)
4. **Protect sensitive files** (already configured)

## 📋 Settings Management

### Admin Panel Access

1. **Log in to Admin Panel**
   - URL: `https://yourwebsite.com/admin/`
   - Use admin credentials

2. **Configure Site Settings**
   - Navigate to: Admin → Site Settings
   - Update:
     - Site name and description
     - Contact information
     - Feature toggles (maintenance mode, ads, comments)
     - SEO settings
     - Language preferences

### Dynamic Settings Available

| Setting | Description | Type |
|---------|-------------|------|
| `site_name` | Website name | Text |
| `maintenance_mode` | Enable/disable site | Boolean |
| `posts_per_page` | News items per page | Number |
| `show_trending_news` | Display trending section | Boolean |
| `show_ads` | Display advertisements | Boolean |
| `enable_comments` | Allow user comments | Boolean |
| `default_language` | Site language (en/ur) | Text |
| `seo_meta_description` | SEO meta description | Text |
| `cache_duration` | Cache time in seconds | Number |

## 🔧 Environment Variables

### Development (.env.development)
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=pk_live_news_dev
SITE_URL=http://localhost/pk-live-news/
APP_ENV=development
```

### Production (.env.production)
```env
DB_HOST=live-server.com
DB_USER=production_user
DB_PASS=secure_password
DB_NAME=pk_live_news
SITE_URL=https://pklivenews.com/
APP_ENV=production
```

## 🛠️ Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check .env credentials
   - Verify database exists
   - Check user permissions

2. **File Upload Errors**
   - Check folder permissions (755)
   - Verify upload_max_filesize in php.ini

3. **404 Errors**
   - Ensure .htaccess is properly configured
   - Check mod_rewrite is enabled

4. **Maintenance Mode Stuck**
   - Access admin panel
   - Disable maintenance mode in settings

### Debug Mode

To enable debugging in production (temporary):
```php
// In config/database.php
define('APP_ENV', 'development');
```

Remember to set back to 'production' when done!

## 📊 Performance Optimization

### Caching
- Configure cache duration in admin settings
- Enable browser caching (already in .htaccess)
- Use CDN for static assets

### Database Optimization
- Regular database cleanup
- Optimize queries
- Use indexes properly

## 🔒 Security Checklist

- [ ] Environment variables configured
- [ ] Database credentials secured
- [ ] File permissions set correctly
- [ ] SSL certificate installed
- [ ] Security headers enabled
- [ ] Error reporting disabled in production
- [ ] Admin panel secured with strong password
- [ ] Regular backups configured

## 📞 Support

For deployment issues:
1. Check this guide first
2. Review error logs in `logs/` directory
3. Contact hosting provider if server issues
4. Check database connection and permissions

---

**Note**: This deployment system separates development and production configurations, making it easy to manage different environments without code changes.
