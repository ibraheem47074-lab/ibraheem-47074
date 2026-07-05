# Affiliate Marketing System Setup Guide

## 🎯 Overview

This comprehensive affiliate marketing system integrates seamlessly with your PK Live News website, allowing you to earn commissions by promoting products from Amazon, AliExpress, and other affiliate networks.

## 📋 Table of Contents

1. [Database Setup](#database-setup)
2. [Configuration](#configuration)
3. [Admin Panel Usage](#admin-panel-usage)
4. [Frontend Integration](#frontend-integration)
5. [Smart Recommendations](#smart-recommendations)
6. [Analytics & Tracking](#analytics--tracking)
7. [Best Practices](#best-practices)
8. [Troubleshooting](#troubleshooting)

## 🗄️ Database Setup

### Step 1: Execute SQL Script

Run the following SQL script in your PHPMyAdmin or MySQL console:

```sql
-- Execute: database_update_affiliate_products.sql
```

This will create the following tables:
- `affiliate_products` - Product information
- `affiliate_categories` - Product categories
- `affiliate_clicks` - Click tracking
- `news_product_relations` - News-product relationships

### Step 2: Verify Tables

Ensure all tables are created with proper relationships:

```sql
SHOW TABLES LIKE 'affiliate_%';
DESCRIBE affiliate_products;
DESCRIBE affiliate_categories;
```

## ⚙️ Configuration

### Step 1: Include Required Files

Add these includes to your main files:

```php
// In config/database.php (already included)
require_once 'includes/affiliate-functions.php';
require_once 'includes/news-products.php';
```

### Step 2: Add CSS and JS

Include in your HTML head:

```html
<link rel="stylesheet" href="assets/css/affiliate-products.css">
<script src="assets/js/affiliate-products.js"></script>
```

### Step 3: Update Navigation

Add links to your navigation menu:

```html
<a href="products.php" class="nav-link">
    <i class="fas fa-shopping-cart"></i> Products
</a>
```

## 🎛️ Admin Panel Usage

### Access Admin Panels

1. **Products Management**: `/admin/manage-affiliate-products.php`
2. **Categories Management**: `/admin/manage-affiliate-categories.php`
3. **Analytics Dashboard**: `/admin/affiliate-analytics.php`

### Adding Products

1. Go to **Add Affiliate Product**
2. Fill in product details:
   - Title and descriptions
   - Pricing information
   - Affiliate URL (from Amazon/AliExpress)
   - Product images
   - Category assignment
3. Set as featured if desired
4. Save product

### Managing Categories

1. Go to **Manage Categories**
2. Create hierarchical categories
3. Assign Font Awesome icons
4. Set sort order for display

### Product-News Integration

When editing news articles:
1. Scroll to "Related Products" section
2. Select products to display
3. Choose display position (sidebar, bottom, inline)
4. Enable auto-recommendations for smart suggestions

## 🌐 Frontend Integration

### Homepage Integration

Add to your homepage (`index.php`):

```php
<?php
require_once 'includes/news-products.php';

// Display product sections
echo display_homepage_products_section();
?>
```

### News Article Integration

Add to your news detail page (`news.php`):

```php
<?php
// Get news details first
$news = get_news_by_slug($slug);

// Display related products
echo display_news_products($news['id'], $news['title'], $news['content'], 'sidebar');
?>
```

### Product Pages

The system includes a complete product browsing page at `products.php` with:
- Category filtering
- Search functionality
- Price range filtering
- Sorting options
- Pagination

## 🧠 Smart Recommendations

### Automatic Product Matching

The system automatically suggests products based on:

1. **Keyword Analysis**: Extracts product-related keywords from news content
2. **Category Matching**: Matches news categories with product categories
3. **Brand Recognition**: Identifies brand mentions in articles
4. **Trending Products**: Shows popular products related to news topics

### Keyword Extraction

Common product keywords detected:
- Electronics: phone, laptop, camera, tablet, etc.
- Brands: Apple, Samsung, Nike, etc.
- Categories: gaming, fashion, sports, etc.

### Manual Override

Admins can manually override automatic suggestions by:
- Selecting specific products for news articles
- Setting display preferences
- Managing featured products

## 📊 Analytics & Tracking

### Click Tracking

All affiliate clicks are automatically tracked:
- IP address and user agent
- Referrer information
- Timestamp
- Conversion tracking

### Analytics Dashboard

Access comprehensive analytics at `/admin/affiliate-analytics.php`:

**Key Metrics:**
- Total clicks and conversions
- Conversion rates
- Top performing products
- Category performance
- Network performance (Amazon vs AliExpress)

**Reports Available:**
- Daily performance charts
- Product comparison tables
- Revenue estimates
- Trend analysis

### Revenue Calculation

The system estimates revenue based on:
- Typical commission rates (5% for Amazon, variable for AliExpress)
- Product prices
- Conversion data

## 💰 Best Practices

### 1. Product Selection

**Do:**
- Choose products relevant to your news content
- Focus on high-quality, well-reviewed products
- Update product information regularly
- Test affiliate links before publishing

**Don't:**
- Promote unrelated products
- Use fake reviews or misleading information
- Overload articles with too many products
- Ignore affiliate disclosure requirements

### 2. Content Integration

**Effective Strategies:**
- "Best [Product] for [Use Case]" articles
- Product reviews and comparisons
- "How to Choose" guides
- Trending product roundups

**Example Integration:**
```
📰 News: "New iPhone 15 Released"
💰 Products: 
- Buy iPhone 15 Pro (affiliate link)
- Compare with Samsung Galaxy
- Best iPhone accessories
```

### 3. SEO Optimization

- Use descriptive product titles
- Include relevant keywords
- Optimize product images
- Create category pages
- Implement proper URL structure

### 4. User Experience

- Ensure fast page loading
- Make affiliate links clearly visible
- Provide honest product information
- Include affiliate disclosure
- Mobile-responsive design

## 🔧 Troubleshooting

### Common Issues

#### 1. Products Not Displaying

**Check:**
- Database tables exist and are populated
- Product status is "active"
- Categories are properly configured
- CSS files are included

**Solution:**
```sql
-- Check products
SELECT * FROM affiliate_products WHERE status = 'active';

-- Check categories
SELECT * FROM affiliate_categories WHERE status = 'active';
```

#### 2. Affiliate Links Not Working

**Check:**
- `affiliate-click.php` exists and is accessible
- Product URLs are valid
- Tracking is enabled

**Solution:**
```php
// Test tracking function
track_affiliate_click(1); // Test with product ID 1
```

#### 3. Analytics Not Recording

**Check:**
- Click tracking is implemented
- Database permissions are correct
- JavaScript is loading properly

**Solution:**
```javascript
// Test tracking function
console.log(trackAffiliateClick(1));
```

#### 4. Smart Recommendations Not Working

**Check:**
- News content is being passed correctly
- Keyword extraction is working
- Products have relevant tags/keywords

**Solution:**
```php
// Test keyword extraction
$keywords = extract_product_keywords($news_title . ' ' . $news_content);
print_r($keywords);
```

### Debug Mode

Enable debug mode by adding to your config:

```php
define('AFFILIATE_DEBUG', true);
```

This will add logging to help identify issues.

## 📱 Mobile Optimization

The system is fully responsive:
- Product cards adapt to screen size
- Touch-friendly buttons
- Optimized images
- Fast loading on mobile

## 🔒 Security Considerations

1. **Input Validation**: All inputs are sanitized
2. **SQL Injection Prevention**: Prepared statements used
3. **XSS Protection**: Output is properly escaped
4. **CSRF Protection**: Form tokens implemented

## 🚀 Performance Optimization

1. **Image Optimization**: Lazy loading implemented
2. **Database Indexing**: Proper indexes on frequently queried columns
3. **Caching**: Consider implementing Redis/Memcached
4. **CDN**: Use CDN for product images

## 📞 Support

For issues or questions:
1. Check this documentation first
2. Review error logs
3. Test with debug mode enabled
4. Verify database connections

## 🔄 Updates & Maintenance

### Regular Tasks

1. **Weekly**: Update product prices and availability
2. **Monthly**: Review analytics and performance
3. **Quarterly**: Audit affiliate links and disclosures
4. **Annually**: Review and update commission rates

### Database Maintenance

```sql
-- Clean old click data (older than 1 year)
DELETE FROM affiliate_clicks WHERE click_date < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Optimize tables
OPTIMIZE TABLE affiliate_products;
OPTIMIZE TABLE affiliate_clicks;
```

---

## 🎉 Congratulations!

You now have a fully functional affiliate marketing system integrated with your news website. Start adding products and watch your revenue grow!

**Next Steps:**
1. Set up your Amazon Associates account
2. Join AliExpress affiliate program
3. Add your first products
4. Monitor analytics
5. Optimize based on performance

Happy earning! 💰
