# Advertisement Management System Guide

## Overview
Complete advertisement management system for PK Live News with support for multiple ad positions, tracking, and analytics.

## Features Implemented

### Ad Positions
- **Header Banner** (728x90px) - Full width, high visibility
- **Sidebar Rectangle** (300x250px) - Right sidebar, good engagement
- **Footer Banner** (728x90px) - Bottom of every page
- **Popup Modal** (400x300px) - Timed popup with frequency control

### Key Features
- Ad impression and click tracking
- Date-based scheduling (start/end dates)
- Maximum impression limits
- Performance analytics (CTR, unique impressions)
- Responsive design for all screen sizes
- Admin interface for managing ads

## Setup Instructions

### 1. Run Database Setup
```bash
# Access in browser
http://your-domain.com/setup_ad_system.php
```

This creates:
- `advertisements` table - stores ad configurations
- `ad_impressions` table - tracks all impressions
- `ad_clicks` table - tracks all clicks
- Sample ads for testing

### 2. Admin Interface
Access at: `http://your-domain.com/admin/manage-ads.php`

Features:
- Add/Edit/Delete advertisements
- View ad performance statistics
- Track impressions and clicks
- Set ad schedules and limits

### 3. Integration in Your Pages

#### Basic Integration
```php
<?php
require_once 'includes/ad-templates.php';
?>
```

#### Header Banner (Top of page)
```php
<?php render_header_ad(); ?>
```

#### Sidebar Rectangle (In sidebar)
```php
<?php render_sidebar_ad(); ?>
```

#### Footer Banner (Bottom of page)
```php
<?php render_footer_ad(); ?>
```

#### Popup Ad (Automatic timing)
```php
<?php render_popup_ad(); ?>
```

#### Multiple Footer Ads
```php
<?php render_footer_ads(); ?>
```

#### Include Ad Styles
```php
<?php render_ad_styles(); ?>
```

## Ad Management

### Adding New Ads
1. Go to Admin → Manage Advertisements
2. Click "Add New Advertisement"
3. Fill in:
   - **Title**: Descriptive name
   - **Position**: Where ad will appear
   - **Size**: Ad dimensions (e.g., 728x90)
   - **Ad Code**: HTML code for the ad
   - **Start/End Dates**: Optional scheduling
   - **Max Impressions**: Optional limit

### Ad Code Examples

#### Image Banner
```html
<a href="https://your-affiliate-link.com" target="_blank">
    <img src="https://your-image-url.com/banner.jpg" alt="Advertisement" style="width:100%; height:90px;">
</a>
```

#### Google AdSense
```html
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Header Banner -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-XXXXXXXXXX"
     data-ad-slot="XXXXXXXXXX"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>
```

#### Custom HTML Ad
```html
<div style="background:#FF6B6B;color:white;padding:20px;text-align:center;border-radius:5px;">
    <h3>Special Offer!</h3>
    <p>Get 20% off today</p>
    <a href="#" style="background:white;color:#FF6B6B;padding:10px 20px;text-decoration:none;border-radius:3px;">Shop Now</a>
</div>
```

## Popup Ad Configuration

### Default Settings
- **Delay**: 5 seconds after page load
- **Frequency**: Once per 24 hours per user
- **Close Options**: X button, click outside, Escape key

### Customizing Popup Timing
Edit `includes/ad-templates.php`:
```javascript
let popupDelay = 5000; // Show after 5 seconds (in milliseconds)
let popupFrequency = 86400000; // Show once per day (24 hours in milliseconds)
```

## Performance Tracking

### Metrics Tracked
- **Total Impressions**: How many times ad was shown
- **Unique Impressions**: How many unique users saw ad
- **Total Clicks**: How many times ad was clicked
- **Unique Clicks**: How many unique users clicked
- **CTR**: Click-through rate percentage

### Viewing Statistics
1. Admin → Manage Advertisements
2. Click eye icon on any ad
3. View detailed performance metrics

## Responsive Design

All ad placements are fully responsive:
- Header/Sidebar/Footer ads scale appropriately
- Mobile-optimized popup ads
- Automatic size adjustments for different screen sizes

## Security Features

- SQL injection protection with prepared statements
- XSS protection with output escaping
- Click tracking prevents direct access
- Admin authentication required

## File Structure

```
├── setup_ad_system.php          # Database setup
├── track-ad-click.php           # Click tracking handler
├── admin/
│   ├── manage-ads.php          # Admin interface
│   └── get-ad-details.php      # AJAX ad details
├── includes/
│   ├── ad-functions.php        # Core ad functions
│   └── ad-templates.php         # Display templates
└── ad-integration-example.php  # Integration example
```

## Best Practices

### Ad Placement
- Header ads get highest visibility
- Sidebar ads have good engagement
- Footer ads work well for branding
- Use popup ads sparingly (max 1 per session)

### Performance
- Optimize ad images for web
- Use async loading for JavaScript ads
- Monitor CTR to optimize placements
- Rotate ads to prevent banner blindness

### User Experience
- Don't overwhelm with too many ads
- Ensure ads don't interfere with content
- Use responsive ad sizes
- Provide clear ad labels

## Troubleshooting

### Ads Not Showing
1. Check if ads are active in admin
2. Verify ad scheduling dates
3. Check impression limits
4. Ensure ad templates are included

### Click Tracking Not Working
1. Verify `track-ad-click.php` exists
2. Check ad code has proper links
3. Ensure JavaScript is enabled
4. Check browser console for errors

### Responsive Issues
1. Include `render_ad_styles()` in head
2. Check CSS conflicts
3. Test on different screen sizes
4. Verify Bootstrap is loaded

## Hostinger Deployment

### Database Configuration
Update `.env` file with Hostinger credentials:
```
DB_HOST=localhost
DB_USER=your_hostinger_db_user
DB_PASS=your_hostinger_db_password
DB_NAME=your_hostinger_db_name
```

### File Permissions
Set appropriate permissions:
- PHP files: 644
- Directories: 755
- Upload directory: 755

### Testing
1. Upload all files to Hostinger
2. Run setup script
3. Test ad integration
4. Verify tracking works

## Support

For issues or questions:
1. Check this guide first
2. Verify file permissions
3. Test with sample ads
4. Check browser console
5. Review admin interface logs
