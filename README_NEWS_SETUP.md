# PK Live News - Complete Database Setup Guide

## 📁 SQL Files Overview

This directory contains comprehensive SQL files for setting up, testing, and maintaining the PK Live News database system.

### 🗄️ SQL Files Description

#### 1. `install_news_complete.sql`
**Purpose**: Complete database installation with all tables, sample data, and advanced features
**Features**:
- Creates all required tables (users, categories, news)
- Inserts sample categories and admin user
- Creates views, stored procedures, and triggers
- Sets up indexes for performance
- Includes sample news articles for testing

**Usage**:
```bash
mysql -u root -p pk_live_news < install_news_complete.sql
```

#### 2. `test_news_queries.sql`
**Purpose**: Comprehensive testing of all database operations
**Features**:
- Tests table creation and structure
- Verifies data integrity
- Tests CRUD operations
- Performance testing queries
- Stored procedure testing

**Usage**:
```bash
mysql -u root -p pk_live_news < test_news_queries.sql
```

#### 3. `backup_news_schema.sql`
**Purpose**: Database backup, maintenance, and troubleshooting
**Features**:
- Data export queries
- Integrity checks
- Performance optimization
- Statistics and reports
- Emergency fixes
- Cleanup procedures

**Usage**:
```bash
mysql -u root -p pk_live_news < backup_news_schema.sql
```

#### 4. `news_migration_tools.sql`
**Purpose**: Database migrations, schema updates, and data transfers
**Features**:
- Version control tracking
- Schema migrations
- Data migration tools
- Performance optimization
- Foreign key setup
- Duplicate data cleanup

**Usage**:
```bash
mysql -u root -p pk_live_news < news_migration_tools.sql
```

## 🚀 Quick Setup Instructions

### Option 1: Complete Setup (Recommended)
```bash
# Run the complete installation
mysql -u root -p pk_live_news < install_news_complete.sql

# Verify installation
mysql -u root -p pk_live_news < test_news_queries.sql
```

### Option 2: Web-Based Setup
1. Open `setup_news_database.php` in your browser
2. Follow the on-screen instructions
3. The script will automatically create tables and insert sample data

### Option 3: Manual Setup
1. Import `install_news_complete.sql`
2. Run `news_migration_tools.sql` for optimizations
3. Use `test_news_queries.sql` to verify everything works

## 📊 Database Structure

### Core Tables

#### `users`
- User management and authentication
- Roles: admin, editor, reporter
- Status: active, blocked

#### `categories`
- News categorization
- Hierarchical structure support
- Status management

#### `news`
- Main news articles table
- Full-text search capabilities
- View tracking
- Publishing workflow

### Advanced Features

#### Views
- `news_with_details`: Complete news information
- `news_summary`: Optimized for frontend display

#### Stored Procedures
- `GetLatestNews(limit)`: Get recent news
- `GetNewsByCategory(slug, limit)`: Category-specific news
- `IncrementNewsViews(id)`: View counter

#### Triggers
- Automatic slug generation
- Timestamp updates
- Data validation

## 🔧 Maintenance Tasks

### Regular Maintenance
```sql
-- Run weekly
OPTIMIZE TABLE news, categories, users;

-- Run monthly
UPDATE news SET views = 0 WHERE views < 0;
DELETE FROM news WHERE status = 'draft' AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### Backup Procedures
```sql
-- Full backup
mysqldump -u root -p pk_live_news > backup_$(date +%Y%m%d).sql

-- Data only backup
mysqldump -u root -p --no-create-info pk_live_news > data_$(date +%Y%m%d).sql
```

### Performance Monitoring
```sql
-- Check table sizes
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'pk_live_news';

-- Check slow queries
SHOW VARIABLES LIKE 'slow_query_log';
```

## 🐛 Troubleshooting

### Common Issues

#### 1. "Column count doesn't match value count"
**Solution**: Run `news_migration_tools.sql` to fix schema mismatches

#### 2. Foreign key constraint failures
**Solution**: Check data integrity with `backup_news_schema.sql`

#### 3. Performance issues
**Solution**: Run optimization queries from `backup_news_schema.sql`

### Error Codes
- **1001**: Database connection failed
- **1002**: Table creation error
- **1003**: Data insertion error
- **1004**: Foreign key constraint error

## 📈 Performance Optimization

### Indexes
- `idx_status`: News status filtering
- `idx_category`: Category-based queries
- `idx_published`: Date-based sorting
- `idx_slug`: URL slug lookups

### Query Optimization
- Use views for complex joins
- Implement stored procedures for repeated operations
- Enable query caching

### Caching Strategy
- Category lists: Cache for 1 hour
- News summaries: Cache for 15 minutes
- Full articles: Cache for 30 minutes

## 🔐 Security Considerations

### Database Security
- Use prepared statements (implemented in PHP)
- Regular backups
- Access control
- Input validation

### Data Protection
- Password hashing (bcrypt)
- SQL injection prevention
- XSS protection
- CSRF tokens

## 📱 Integration Notes

### PHP Integration
- All PHP files use mysqli with prepared statements
- Session management implemented
- File upload security
- Error handling and logging

### API Endpoints
- `/api/news.php` - News listing
- `/api/news-detail.php` - Single article
- `/api/categories.php` - Category list
- `/api/search.php` - Search functionality

## 🔄 Version History

### v1.0.0
- Initial database structure
- Basic CRUD operations
- User authentication

### v1.1.0
- Added views tracking
- Video URL support
- Performance indexes

### v1.2.0
- Foreign key constraints
- Data validation
- Migration tools

### v1.3.0
- Stored procedures
- Triggers
- Views for performance

### v1.4.0
- Migration framework
- Advanced maintenance tools
- Comprehensive testing

## 📞 Support

For issues with the database setup:
1. Check the error logs
2. Run the test queries
3. Verify file permissions
4. Check MySQL configuration

## 🎯 Next Steps

After setting up the database:
1. Test the admin panel functionality
2. Verify news article creation
3. Test image uploads
4. Check search functionality
5. Monitor performance

---

**Note**: Always backup your database before running migration scripts or making structural changes.
