# Hostinger Website Builder Setup Guide

## Quick Start for Hostinger Website Builder

### Step 1: Prepare Your Local Files

1. **Export your database** from XAMPP:
   - Open phpMyAdmin in XAMPP
   - Select `pk_live_news` database
   - Click **Export** → **Custom** → **Go**
   - Save as `pk_live_news.sql`

2. **Create a zip file** of your project:
   - Go to your project folder: `D:\Xampp\htdocs\PK-LIVE NEWS`
   - Exclude: `backups/`, `logs/`, `.git/`
   - Zip everything else

### Step 2: Hostinger Website Builder Setup

#### Option A: Using Hostinger Website Builder

1. **Log into Hostinger**
2. **Go to Website Builder** → **Get Started**
3. **Choose "Import Website"** option
4. **Upload your zip file** or connect via FTP

#### Option B: Using Hostinger Hosting (Recommended)

1. **Get Hostinger Hosting** (Premium or Business plan)
2. **Log into cPanel**
3. **Go to File Manager**

### Step 3: Upload Files

1. **Navigate to `public_html/`**
2. **Upload your zip file**
3. **Extract the zip file**
4. **Move files to root** if needed

### Step 4: Database Setup

1. **In Hostinger cPanel**:
   - Go to **MySQL Databases**
   - Create database: `pklivenews_main`
   - Create database user with strong password
   - Add user to database with all privileges

2. **Import your database**:
   - Go to **phpMyAdmin**
   - Select your new database
   - Click **Import** → upload `pk_live_news.sql`

### Step 5: Configure Environment

Create `.env` file in your root directory:

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

### Step 6: File Permissions

Set these permissions in File Manager:
- **Folders**: 755
- **PHP files**: 644  
- **Upload folders**: 755

### Step 7: SSL and Security

1. **Enable SSL** in Hostinger panel
2. **Update .htaccess** for production:
```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
</IfModule>

# Hide PHP errors in production
<IfModule mod_php.c>
    php_flag display_errors Off
    php_value error_reporting 0
</IfModule>
```

### Step 8: Test Everything

1. **Visit your domain**
2. **Test admin login**: `your-domain.com/admin/`
3. **Create a test news article**
4. **Test file uploads**
5. **Check all pages work**

## Quick Checklist

- [ ] Files uploaded to public_html
- [ ] Database created and imported
- [ ] .env file configured
- [ ] File permissions set (755/644)
- [ ] SSL certificate enabled
- [ ] Admin login working
- [ ] News creation working
- [ ] File uploads working

## Default Admin Login

If you need to create admin:
- **Username**: `admin`
- **Password**: `admin123`
- **Access**: `your-domain.com/admin/login.php`

## Troubleshooting

### 500 Internal Server Error
- Check .htaccess file
- Verify file permissions
- Check PHP error logs

### Database Connection Error
- Verify .env credentials
- Check database exists
- Confirm user permissions

### File Upload Issues
- Set upload folder permissions to 755
- Check PHP upload limits
- Verify disk space

### White Screen
- Temporarily enable error display
- Check PHP error logs
- Verify syntax in .env file

## Support

If you need help:
1. **Hostinger Knowledge Base**: search for "website builder"
2. **Hostinger Support**: 24/7 live chat
3. **Check error logs** in cPanel

## Next Steps

After successful deployment:
1. Set up regular backups
2. Configure email settings
3. Set up SSL certificate
4. Optimize performance
5. Set up monitoring

---

**Your PK Live News website will be live at: `https://your-domain.com`**

**Admin Panel: `https://your-domain.com/admin/`**
