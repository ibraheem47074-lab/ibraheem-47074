# PK Live News - Professional News Website

A comprehensive news website with live broadcasting, admin panel, and modern features built for FYP (Final Year Project).

## 🚀 Features

### 📰 News Management
- **Complete CRUD Operations**: Add, edit, delete news articles
- **Category-based Organization**: Politics, Sports, International, Technology, Entertainment, Business
- **Breaking News System**: Highlight important stories with special badges
- **Rich Text Editor**: TinyMCE integration for content creation
- **Image & Video Support**: Upload images and embed videos
- **Scheduled Publishing**: Set future publication dates
- **View Tracking**: Monitor article popularity

### 📡 Live Broadcasting
- **YouTube Live Integration**: Easy streaming setup
- **Live Chat System**: Real-time viewer interaction
- **Schedule Management**: Plan upcoming broadcasts
- **Viewer Count Display**: Live audience statistics
- **ON AIR Indicators**: Visual status indicators
- **Past Broadcasts**: Archive of previous streams

### 🗳️ Interactive Features
- **Live Polling System**: Real-time voting with results
- **Comments Section**: User engagement with moderation
- **Share Functionality**: Social media integration
- **Newsletter Subscription**: Email updates
- **Search System**: Advanced news search
- **Dark Mode**: User preference support

### 🏗️ Admin Panel
- **Dashboard Statistics**: Overview of site metrics
- **User Management**: Role-based access control
- **Category Management**: Dynamic category system
- **Comment Moderation**: Approve/reject user comments
- **Advertisement Control**: Manage banner and popup ads
- **Settings Management**: Site configuration

### 🎨 Modern UI/UX
- **Responsive Design**: Mobile-first approach
- **Bootstrap 5**: Modern framework
- **Font Awesome Icons**: Professional iconography
- **Breaking News Ticker**: Animated headlines
- **Loading Animations**: Smooth user experience
- **Print Support**: Article printing optimization

## 🛠️ Technology Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with animations
- **JavaScript (ES6+)** - Interactive features
- **Bootstrap 5** - Responsive framework
- **Font Awesome 6** - Icon library
- **TinyMCE** - Rich text editor

### Backend
- **PHP 8+** - Server-side logic
- **MySQL** - Database management
- **RESTful APIs** - Modern data handling

### Features
- **AJAX** - Dynamic content loading
- **JSON APIs** - Structured data exchange
- **Session Management** - Secure authentication
- **File Uploads** - Image management
- **SEO Friendly** - Clean URLs and meta tags

## 📋 System Requirements

### Server Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled
- File upload permissions

### Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## 🚀 Installation Guide

### 1. Database Setup

1. **Create Database**
   ```sql
   CREATE DATABASE pk_live_news;
   ```

2. **Import Schema**
   ```bash
   mysql -u username -p pk_live_news < database.sql
   ```

3. **Update Configuration**
   Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'pk_live_news');
   ```

### 2. File Setup

1. **Extract Files** to your web directory
2. **Set Permissions**:
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/news/
   chmod 755 uploads/categories/
   ```

3. **Configure Site URL** in `config/database.php`:
   ```php
   define('SITE_URL', 'http://your-domain.com/');
   ```

### 3. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 🔐 Default Login

### Admin Panel
- **URL**: `http://your-domain.com/admin/`
- **Email**: `admin@pklivenews.com`
- **Password**: `admin123`

⚠️ **Important**: Change the default password after first login!

## 📁 Project Structure

```
PK-LIVE NEWS/
├── admin/                  # Admin panel files
│   ├── login.php          # Admin login
│   ├── dashboard.php      # Admin dashboard
│   ├── manage-news.php    # News management
│   ├── add-news.php       # Add news article
│   └── logout.php         # Admin logout
├── api/                   # API endpoints
│   ├── load-news.php      # Load more news
│   ├── vote-poll.php      # Poll voting
│   └── submit-comment.php # Comment submission
├── assets/                # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Image assets
├── config/                # Configuration files
│   └── database.php      # Database connection
├── includes/             # Reusable components
│   ├── header.php        # Page header
│   └── footer.php        # Page footer
├── uploads/              # User uploads
│   ├── news/             # News images
│   └── categories/       # Category images
├── index.php             # Homepage
├── news.php              # News detail page
├── live.php              # Live TV page
├── category.php          # Category page
├── search.php            # Search results
├── contact.php           # Contact page
├── database.sql          # Database schema
└── README.md             # This file
```

## 🎯 Usage Guide

### Adding News Articles

1. **Login to Admin Panel**
2. **Navigate to "Manage News"**
3. **Click "Add News"**
4. **Fill in Details**:
   - Title (required)
   - Content (required)
   - Category (required)
   - Featured Image (optional)
   - Video URL (optional)
   - Excerpt (optional)
5. **Set Status**: Draft, Published, or Featured
6. **Click "Save News Article"**

### Setting Up Live Streaming

1. **Create YouTube Live Stream**
2. **Copy Stream URL/Embed Code**
3. **Go to Admin → Live Stream**
4. **Add Stream Details**:
   - Title
   - Stream URL
   - Embed Code
   - Description
5. **Set Status to "Online"**

### Managing Categories

1. **Admin → Categories**
2. **Add/Edit/Delete Categories**
3. **Set Category Status**
4. **Upload Category Images**

### User Management

1. **Admin → Users**
2. **Add New Users**:
   - Name, Email, Password
   - Role (Admin, Editor, Reporter)
3. **Manage User Status**
4. **Assign Permissions**

## 🔧 Configuration Options

### Site Settings (config/database.php)
```php
// Site Configuration
define('SITE_URL', 'http://your-domain.com/');
define('SITE_NAME', 'PK Live News');
define('ADMIN_EMAIL', 'admin@pklivenews.com');

// File Upload Configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi']);
```

### Email Configuration
Add to `config/database.php` for email functionality:
```php
// Email Settings
define('SMTP_HOST', 'your-smtp-host.com');
define('SMTP_USER', 'your-email@domain.com');
define('SMTP_PASS', 'your-email-password');
define('SMTP_PORT', 587);
```

## 🎨 Customization

### Changing Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary-color: #dc3545;    /* Red */
    --secondary-color: #667eea; /* Purple */
    --success-color: #28a745;   /* Green */
    --warning-color: #ffc107;   /* Yellow */
}
```

### Logo Replacement
1. Replace `assets/images/logo.png`
2. Update `includes/header.php` logo reference

### Font Changes
Edit `assets/css/style.css`:
```css
body {
    font-family: 'Your Font', sans-serif;
}
```

## 📊 Database Schema

### Main Tables
- **users** - User accounts and roles
- **news** - News articles and content
- **categories** - News categories
- **comments** - User comments
- **live_stream** - Live broadcast data
- **polls** - Poll questions and options
- **advertisements** - Ad management

### Relationships
- News → Categories (Many-to-One)
- News → Users (Many-to-One)
- Comments → News (Many-to-One)
- Poll Options → Polls (Many-to-One)

## 🚀 Performance Optimization

### Caching
- Enable browser caching with `.htaccess`
- Use CDN for static assets
- Implement database query caching

### Image Optimization
- Compress uploaded images
- Use WebP format support
- Implement lazy loading

### Database Optimization
- Add indexes for frequently queried columns
- Optimize JOIN operations
- Use prepared statements

## 🔒 Security Features

### Implemented
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization
- **CSRF Protection**: Form tokens
- **File Upload Security**: Type and size validation
- **Session Security**: Secure session management

### Recommendations
- Implement HTTPS/SSL
- Add rate limiting
- Use security headers
- Regular security updates

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials
   - Verify MySQL server is running
   - Confirm database exists

2. **File Upload Issues**
   - Check folder permissions (755)
   - Verify upload_max_filesize in php.ini
   - Ensure temp folder is writable

3. **404 Errors**
   - Check mod_rewrite is enabled
   - Verify .htaccess configuration
   - Check file permissions

4. **Live Stream Not Working**
   - Verify YouTube stream URL
   - Check embed code format
   - Ensure stream is public

### Debug Mode
Enable error reporting in `config/database.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📱 Mobile Responsiveness

The website is fully responsive and works on:
- **Smartphones** (320px+)
- **Tablets** (768px+)
- **Desktops** (1024px+)
- **Large Screens** (1440px+)

## 🌐 SEO Features

- **Clean URLs**: SEO-friendly structure
- **Meta Tags**: Dynamic title and description
- **Open Graph**: Social media sharing
- **Structured Data**: Schema.org markup
- **Sitemap**: Auto-generated XML sitemap
- **Robots.txt**: Search engine instructions

## 🔄 Updates & Maintenance

### Regular Tasks
- **Database Backups**: Daily backups recommended
- **Log Review**: Check error logs weekly
- **Security Updates**: Keep PHP/MySQL updated
- **Content Moderation**: Review comments and reports

### Version Updates
1. Backup current version
2. Update files
3. Run database migrations
4. Test functionality
5. Clear caches

## 📞 Support

### Documentation
- **Code Comments**: Inline documentation
- **Database Schema**: Detailed in `database.sql`
- **API Documentation**: Comments in API files

### Common Questions
- **How to add custom fields?** Modify database and forms
- **How to change theme?** Edit CSS files
- **How to add languages?** Implement language system

## 📜 License

This project is developed for educational purposes (FYP Project). Feel free to modify and use according to your requirements.

## 🙏 Acknowledgments

- **Bootstrap Team** - UI Framework
- **Font Awesome** - Icon Library
- **TinyMCE** - Rich Text Editor
- **PHP Community** - Backend Development
- **MySQL Team** - Database System

---

**Developed for FYP Project | Professional News Website with Live Broadcasting**
