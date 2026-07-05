# Complete Guide: Host Your PK Live News Website on Hostinger

## Table of Contents
1. [Pre-Deployment Preparation](#pre-deployment-preparation)
2. [Hostinger Account Setup](#hostinger-account-setup)
3. [Database Setup](#database-setup)
4. [File Upload](#file-upload)
5. [Configuration](#configuration)
6. [Testing & Launch](#testing--launch)
7. [Troubleshooting](#troubleshooting)

---

## Pre-Deployment Preparation

### Step 1: Export Your Local Database
1. Open XAMPP Control Panel
2. Start Apache and MySQL services
3. Open browser and go to `http://localhost/phpmyadmin/`
4. Select your database: `pk_live_news`
5. Click **Export** tab
6. Select **Quick** export method
7. Choose **SQL** format
8. Click **Go** and save the file as `pk_live_news_backup.sql`

### Step 2: Prepare Your Website Files
1. Remove development files (optional but recommended):
   - `prepare_for_hostinger.php`
   - `access_fix.php`
   - `fix_all_issues.php`
   - Any test files

2. Create deployment package:
   - Run `prepare_for_hostinger.php` in your browser
   - Download the generated zip file
   - Download the `.env` template

### Step 3: Create Production Environment File
Create a new file named `.env` with this content:
```env
# Database Configuration
DB_HOST=localhost
DB_USER=your_hostinger_db_user
DB_PASS=your_hostinger_db_password
DB_NAME=your_hostinger_db_name

# Site Configuration
SITE_URL=https://your-domain.com/
SITE_NAME=PK Live News
APP_ENV=production

# Email Configuration
ADMIN_EMAIL=admin@your-domain.com
SUPPORT_EMAIL=support@your-domain.com
```

---

## Hostinger Account Setup

### Step 4: Choose and Purchase Hosting Plan
1. Go to [Hostinger.com](https://www.hostinger.com/)
2. Choose a plan (Premium or Business recommended for news websites)
3. Select domain name or use existing one
4. Complete registration and payment

### Step 5: Access cPanel
1. Check your email for Hostinger login credentials
2. Log into Hostinger Members Area
3. Click **Hosting** > **Manage** next to your domain
4. You'll be redirected to cPanel

---

## Database Setup

### Step 6: Create MySQL Database
1. In cPanel, find **MySQL Databases** under **Databases**
2. **Create New Database:**
   - Database name: `pklivenews_main`
   - Click **Create Database**
3. **Create Database User:**
   - Username: `pklivenews_user`
   - Password: Generate strong password (save it!)
   - Click **Create User**
4. **Add User to Database:**
   - Select user and database
   - Check **ALL PRIVILEGES**
   - Click **Make Changes**

### Step 7: Import Database
1. In cPanel, go to **phpMyAdmin**
2. Select your new database from left sidebar
3. Click **Import** tab
4. Choose your `pk_live_news_backup.sql` file
5. Click **Go** (wait for import to complete)

---

## File Upload

### Step 8: Upload Website Files
**Method A: Using File Manager (Recommended)**
1. In cPanel, open **File Manager**
2. Navigate to `public_html` directory
3. Click **Upload** button
4. Upload your website zip file
5. Right-click the zip file > **Extract**
6. Delete the zip file after extraction

**Method B: Using FTP**
1. Download FileZilla or use Hostinger's built-in FTP
2. Connect using FTP credentials from cPanel
3. Navigate to `public_html`
4. Upload all files and folders

### Step 9: Set File Permissions
In File Manager:
1. Select all folders > Right-click > **Change Permissions**
2. Set permissions to **755**
3. Select all PHP files > Right-click > **Change Permissions**
4. Set permissions to **644**
5. Select `uploads` folder > Set to **755**

---

## Configuration

### Step 10: Configure Environment File
1. In File Manager, edit the `.env` file
2. Update with your actual database details:
```env
DB_HOST=localhost
DB_USER=pklivenews_user
DB_PASS=your_actual_password
DB_NAME=pklivenews_main
SITE_URL=https://your-domain.com/
```
3. Save the file

### Step 11: Configure .htaccess for HTTPS
1. Edit `.htaccess` file in `public_html`
2. Add these lines at the top (if not present):
```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Step 12: Enable SSL Certificate
1. In cPanel, go to **SSL** > **Let's Encrypt SSL**
2. Select your domain
3. Click **Issue**
4. Wait for certificate to be issued

---

## Testing & Launch

### Step 13: Test Your Website
1. Open browser and visit `https://your-domain.com/`
2. Check if homepage loads correctly
3. Test admin panel: `https://your-domain.com/admin/login.php`
   - Default login: `admin` / `admin123`
4. Test news articles and categories
5. Test file upload functionality

### Step 14: Final Checks
- [ ] All pages load without errors
- [ ] Images display correctly
- [ ] Admin panel works
- [ ] File uploads work
- [ ] Contact forms (if any) work
- [ ] Mobile responsiveness looks good

---

## Troubleshooting

### Common Issues and Solutions

#### 1. "500 Internal Server Error"
**Causes:**
- File permissions incorrect
- .htaccess syntax error
- PHP version incompatibility

**Solutions:**
1. Check file permissions (755 for folders, 644 for files)
2. Rename `.htaccess` to test if it's the issue
3. Check PHP version in cPanel > Select PHP Version

#### 2. "Database Connection Failed"
**Causes:**
- Wrong database credentials
- Database not created
- User not added to database

**Solutions:**
1. Verify `.env` file credentials
2. Check database exists in phpMyAdmin
3. Ensure user has all privileges

#### 3. "White Screen" or "Blank Page"
**Causes:**
- PHP syntax error
- Memory limit exceeded
- Fatal error in code

**Solutions:**
1. Check error logs in cPanel > Error Log
2. Temporarily enable error display:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

#### 4. "File Upload Not Working"
**Causes:**
- Upload folder permissions
- PHP upload limits
- Disk space full

**Solutions:**
1. Set uploads folder to 755 permissions
2. Increase upload limits in `.htaccess`:
   ```apache
   php_value upload_max_filesize 20M
   php_value post_max_size 20M
   ```

#### 5. "Images Not Displaying"
**Causes:**
- Broken image paths
- Missing image files
- Permission issues

**Solutions:**
1. Check image files exist in uploads folder
2. Verify image paths in database
3. Check folder permissions

---

## Post-Launch Optimization

### Step 15: Performance Optimization
1. **Enable Caching** in cPanel > Caching
2. **Optimize Images** before uploading
3. **Enable Gzip** compression (already in .htaccess)
4. **Set up CDN** (optional, in Hostinger settings)

### Step 16: Security Enhancements
1. **Change default admin password**
2. **Enable 2FA** for admin panel
3. **Set up backups** in cPanel > Backup
4. **Install SSL certificate** (already done)

### Step 17: Regular Maintenance
- **Weekly**: Check for updates, backup database
- **Monthly**: Review logs, optimize database
- **Quarterly**: Security audit, performance review

---

## Quick Reference Commands

### Database Export (Local)
```bash
mysqldump -u root -p pk_live_news > backup.sql
```

### File Permissions (via SSH)
```bash
chmod -R 755 public_html/
chmod -R 644 public_html/*.php
```

### Database Import (Hostinger)
Use phpMyAdmin web interface (recommended)

---

## Support Resources

- **Hostinger Knowledge Base**: [hostinger.com/tutorials](https://www.hostinger.com/tutorials)
- **Live Chat**: Available in Hostinger dashboard
- **Email Support**: support@hostinger.com
- **Phone Support**: Check your hosting plan details

---

## Estimated Timeline

| Step | Time Required |
|------|---------------|
| Preparation | 30 minutes |
| Account Setup | 15 minutes |
| Database Setup | 20 minutes |
| File Upload | 30 minutes |
| Configuration | 15 minutes |
| Testing | 20 minutes |
| **Total** | **~2 hours** |

---

## Success Checklist

Before going live, ensure all items are checked:

- [ ] Database exported and imported successfully
- [ ] All files uploaded to public_html
- [ ] File permissions set correctly
- [ ] .env file configured with correct credentials
- [ ] SSL certificate installed and working
- [ ] HTTPS redirect working
- [ ] Homepage loads without errors
- [ ] Admin panel accessible and functional
- [ ] File uploads working
- [ ] Images displaying correctly
- [ ] Mobile site working
- [ ] Contact forms working (if applicable)
- [ ] Backup system configured

---

## Congratulations! 

Your PK Live News website is now live on Hostinger! 

**Next Steps:**
1. Share your website with the world
2. Monitor performance and uptime
3. Regularly update content
4. Keep backups current
5. Monitor security

For any issues, refer to the troubleshooting section or contact Hostinger support.
