# PK Live News - Hostinger Deployment Checklist

## Pre-Deployment Checklist

### 1. Environment Configuration
- [x] `.env.production` file created with Hostinger settings
- [x] Database credentials configured for Hostinger
- [x] Site URL set to `https://pk-news.com`
- [x] Production environment variables set

### 2. Code Updates
- [x] Hardcoded localhost references removed
- [x] Dynamic SITE_URL usage implemented
- [x] Service worker registration updated for HTTPS only
- [x] API endpoints use dynamic URLs

### 3. Security Configuration
- [x] `.htaccess` security headers configured
- [x] Error display disabled for production
- [x] Sensitive files protected
- [x] CSP policies implemented

### 4. File Structure
- [x] Upload directories created and writable
- [x] Logs directory ready
- [x] Cache directory configured
- [x] Backup systems in place

### 5. Database
- [x] Database backups available
- [x] Connection testing implemented
- [x] Error handling for database issues

## Deployment Steps

### Step 1: File Upload
1. **Exclude from upload:**
   - Local test files (`test_*.php`, `fix_*.php`, `diagnostic_*.php`)
   - Local database backups (keep latest only)
   - Development logs (clear before upload)
   - `.env` local file

2. **Upload to Hostinger:**
   - Compress project files (exclude above)
   - Upload to `public_html/` directory
   - Extract and organize

### Step 2: Environment Setup
1. **Configure environment:**
   - Rename `.env.production` to `.env`
   - Update database credentials with Hostinger values
   - Set `APP_ENV=production`

2. **Set permissions:**
   - Directories: 755
   - PHP files: 644
   - Upload folders: 755

### Step 3: Database Setup
1. **Create database on Hostinger:**
   - Database name: `u129650532_pk_live_news`
   - User: `u129650532_ibraheem`
   - Import latest SQL backup

2. **Test connection:**
   - Run `hosting_check.php` to verify
   - Check database connectivity
   - Verify all tables exist

### Step 4: SSL Configuration
1. **Enable SSL:**
   - Install SSL certificate through Hostinger panel
   - Force HTTPS redirect
   - Update any remaining HTTP links

### Step 5: Final Testing
1. **Functionality tests:**
   - [ ] Homepage loads correctly
   - [ ] News articles display
   - [ ] Login system works
   - [ ] File uploads functional
   - [ ] API endpoints responding
   - [ ] Admin panel accessible

2. **Performance checks:**
   - [ ] Page load speed acceptable
   - [ ] Images optimizing correctly
   - [ ] Caching working
   - [ ] Mobile responsive

## Post-Deployment Tasks

### 1. Monitoring Setup
- [ ] Error logging configured
- [ ] Analytics tracking enabled
- [ ] Backup schedule set
- [ ] Performance monitoring

### 2. Security Hardening
- [ ] Regular security updates
- [ ] Malware scanning
- [ ] Firewall rules review
- [ ] User access audit

### 3. Maintenance
- [ ] Regular database backups
- [ ] Log rotation
- [ ] Cache clearing
- [ ] Content updates

## Critical Issues Found

### 1. Database Connection Fixed
- **Issue:** Access denied for production database credentials in local environment
- **Status:** FIXED - Updated env.php fallback values
- **Action:** Local development now uses root/empty password, production uses Hostinger credentials

### 2. Database Column Errors
- **Issue:** Unknown column 'image' errors in header.php
- **Status:** Needs database schema update
- **Action:** Run migration scripts to add missing columns

### 3. Error Log Size
- **Issue:** Large error logs (787KB+)
- **Status:** Should be cleared before deployment
- **Action:** Clear logs and monitor for new errors

## Hostinger Compatibility

### Supported Features
- PHP 8.0+ (recommended 8.1)
- MySQL 5.7+ / MariaDB 10.3+
- SSL certificates
- .htaccess support
- Cron jobs
- File uploads up to 50MB

### Resource Limits
- Memory: 512MB configured
- Execution time: 300 seconds
- Upload size: 50MB
- Database connections: Adequate for current load

## Emergency Rollback Plan

### If Deployment Fails:
1. Restore from backup immediately
2. Check error logs for specific issues
3. Fix issues locally before re-deploying
4. Test thoroughly before going live again

### Contact Information
- Hostinger Support: Available 24/7
- Emergency rollback: Use file manager backup
- Database restore: Through phpMyAdmin

## Final Verification

Run `hosting_check.php` on Hostinger to verify:
- [ ] PHP version compatible
- [ ] All extensions loaded
- [ ] Database connection working
- [ ] File permissions correct
- [ ] Security headers active
- [ ] SSL certificate valid

---

**Status:** Ready for deployment with minor database fixes needed
**Last Updated:** April 30, 2026
**Next Action:** Deploy to Hostinger and run verification checks
