# PK Live News - Admin Account Setup Guide

## Overview

This guide helps you set up admin credentials for your PK Live News website.

## Quick Setup

### Method 1: Use Setup Script

1. Visit: `https://pk-news.com/setup_admin.php`
2. Fill in admin details:
   - **Name**: Admin (or your preferred name)
   - **Email**: ibraheem@pk-news.com (or your admin email)
   - **Password**: Choose a strong password (min 6 characters)
3. Click "Create Admin Account"
4. Save credentials securely
5. Delete `setup_admin.php` after setup for security

### Method 2: Manual Database Setup
If you prefer manual setup, run this SQL query:

```sql
INSERT INTO users (name, email, password, role, status, created_at) 
VALUES (
    'Admin', 
    'ibraheem@pk-news.com', 
    '$2y$10$YourHashedPasswordHere', 
    'admin', 
    'active', 
    NOW()
);
```

## Recommended Admin Credentials

Based on your configuration:

- **Email**: ibraheem@pk-news.com
- **Name**: Ibraheem (or your preferred name)
- **Password**: Choose a strong password

## Access Points

### Admin Login

- **URL**: `https://pk-news.com/admin/login.php`
- **Email**: Your admin email
- **Password**: Your chosen password

### Admin Dashboard

After login, you'll be redirected to:

- **URL**: `https://pk-news.com/admin/dashboard.php`

## Security Features

### Password Security

- Passwords are hashed using PHP's `password_hash()`
- Minimum 6 characters required
- Password confirmation during setup

### Session Management

- Secure session handling
- Role-based access control
- Automatic logout on inactivity

### Access Control

- Admin can access all features
- Editor and Reporter roles have limited access
- User status verification (active/inactive)

## Troubleshooting

### Login Issues

1. **Check credentials**: Ensure email and password are correct
2. **Database connection**: Verify database is accessible
3. **User status**: Ensure admin account is 'active'
4. **Clear cache**: Clear browser cache and cookies

### Setup Issues

1. **File permissions**: Ensure setup file is accessible
2. **Database errors**: Check database connection
3. **Email validation**: Use valid email format

## Security Best Practices

### After Setup

1. **Delete setup file**: Remove `setup_admin.php`
2. **Change passwords**: Update regularly
3. **Use strong passwords**: Mix letters, numbers, symbols
4. **Enable 2FA**: If available
5. **Monitor access**: Check login logs

### Production Security

1. **HTTPS only**: Ensure SSL is active
2. **Limit attempts**: Implement rate limiting
3. **Regular updates**: Keep software updated
4. **Backup regularly**: Database and file backups

## Admin Features

### Dashboard Access

- **News Management**: Create, edit, delete articles
- **User Management**: Manage user accounts
- **Category Management**: Organize content
- **Analytics**: View website statistics
- **Settings**: Configure website options

### User Roles

- **Admin**: Full access to all features
- **Editor**: Can edit and publish content
- **Reporter**: Can create and submit content

## Next Steps

1. **Set up admin account** using setup script
2. **Test login functionality**
3. **Explore admin dashboard**
4. **Configure website settings**
5. **Create sample content**
6. **Set up user roles** if needed

## Support

If you encounter issues:
1. Check error logs in admin panel
2. Verify database configuration
3. Test with different browsers
4. Contact hosting support if needed

## Important Notes

- The setup script creates a secure admin account
- Passwords are hashed for security
- Delete setup files after use
- Keep credentials secure
- Regular password updates recommended

Your PK Live News website is now ready for admin management!
