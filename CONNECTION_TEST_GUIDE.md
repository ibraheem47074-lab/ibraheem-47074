# PK Live News - Connection Test System Guide

## Overview

The PK Live News Connection Test System is a comprehensive diagnostic tool designed to verify all critical connections and configurations for your news website. This system provides detailed insights into your server health, database connectivity, API functionality, and overall system performance.

## Files Created

### 1. `connection_test_system.php`
**Purpose**: Core testing engine that performs all connection tests
- Tests database connectivity and configuration
- Verifies file system permissions
- Checks API endpoints and external services
- Validates RSS feed functionality
- Tests live streaming connections
- Verifies weather API integration
- Checks affiliate system connections
- Analyzes security configurations
- Tests PHP extensions and dependencies

**Usage**: Access via URL or include in other scripts
```php
// Direct access
http://your-domain.com/connection_test_system.php

// Include in PHP script
$results = json_decode(file_get_contents('connection_test_system.php'), true);
```

### 2. `connection_test_dashboard.php`
**Purpose**: Web-based dashboard for interactive testing
- Modern, responsive interface
- Real-time test execution
- Visual test results with status indicators
- Categorized testing options
- Detailed error reporting
- Mobile-friendly design

**Features**:
- Run all tests at once
- Individual test categories (Database, File System, etc.)
- Clear results visualization
- Server information display
- Export capabilities

### 3. `generate_connection_report.php`
**Purpose**: Comprehensive HTML report generator
- Professional report format
- Executive summary
- Detailed technical information
- Recommendations based on results
- Print-friendly layout
- PDF-ready formatting

**Usage**:
```php
// Generate and display report
http://your-domain.com/generate_connection_report.php
```

## Test Categories

### 1. Database Connection Tests
- **Connection Status**: Verifies MySQL/MariaDB connectivity
- **Database Version**: Checks database server version
- **Character Set**: Validates UTF-8 MB4 configuration
- **Table Structure**: Verifies essential tables exist
- **Query Performance**: Tests basic query execution

### 2. File System Tests
- **Directory Permissions**: Checks read/write permissions
- **Upload Directories**: Verifies upload folder accessibility
- **Cache Directories**: Tests cache folder permissions
- **Log Directories**: Validates log folder accessibility
- **Configuration Files**: Checks config file readability

### 3. Web Server Configuration
- **.htaccess Status**: Verifies Apache configuration
- **URL Rewriting**: Tests mod_rewrite functionality
- **Security Headers**: Checks security header implementation
- **File Protection**: Verifies sensitive file protection
- **Error Pages**: Tests custom error page configuration

### 4. API Endpoint Tests
- **Breaking News API**: Tests news endpoint functionality
- **Weather API**: Verifies weather service integration
- **Country API**: Tests geographic data endpoints
- **Bookmark API**: Checks bookmark functionality
- **General API Health**: Overall API system status

### 5. RSS Feed Tests
- **RSS Generation**: Tests RSS feed creation
- **XML Parsing**: Verifies SimpleXML functionality
- **Feed Import**: Tests RSS import capabilities
- **Feed Validation**: Checks feed format compliance

### 6. Live Streaming Tests
- **Stream Configuration**: Verifies streaming setup
- **Channel Access**: Tests live channel connectivity
- **Video Playback**: Checks video streaming functionality
- **Stream Health**: Monitors streaming service status

### 7. Weather API Tests
- **API Key Configuration**: Verifies weather API setup
- **HTTP Client**: Tests cURL functionality
- **Data Retrieval**: Checks weather data fetching
- **API Response**: Validates weather data format

### 8. Affiliate System Tests
- **Product Database**: Tests affiliate product storage
- **Link Tracking**: Verifies affiliate link functionality
- **Click Tracking**: Tests click monitoring system
- **Commission System**: Checks affiliate commission tracking

### 9. PHP Extension Tests
- **Database Extensions**: Tests mysqli/PDO availability
- **HTTP Extensions**: Verifies cURL functionality
- **XML Extensions**: Tests SimpleXML availability
- **Image Extensions**: Checks GD library status
- **Session Extensions**: Verifies session management

### 10. Security Configuration Tests
- **Error Display**: Checks error display settings
- **PHP Exposure**: Verifies PHP version hiding
- **File Uploads**: Tests upload security settings
- **URL Access**: Checks URL fopen security
- **Session Security**: Verifies session configuration

## Status Indicators

### Passed (Green)
- Component is functioning correctly
- No issues detected
- Optimal configuration

### Warning (Yellow)
- Component works but could be improved
- Non-critical issues found
- Recommendations available

### Failed (Red)
- Component is not functioning
- Critical issues detected
- Immediate attention required

## Usage Instructions

### Quick Start
1. Access the dashboard: `http://your-domain.com/connection_test_dashboard.php`
2. Click "Run All Tests" for comprehensive testing
3. Review results and address any issues found

### Advanced Usage
1. **Individual Testing**: Use category-specific buttons for focused testing
2. **Report Generation**: Access `generate_connection_report.php` for detailed reports
3. **API Integration**: Use `connection_test_system.php` for automated monitoring

### Scheduled Testing
Create a cron job for regular health checks:
```bash
# Daily connection test at 2 AM
0 2 * * * curl -s http://your-domain.com/connection_test_system.php > /var/log/connection_tests.log
```

## Troubleshooting

### Common Issues

#### Database Connection Failed
- Check database credentials in `config/database.php`
- Verify database server is running
- Confirm database exists and is accessible
- Check firewall settings

#### File Permission Errors
- Verify directory ownership
- Check PHP user permissions
- Ensure upload directories are writable
- Check SELinux/AppArmor settings

#### API Endpoint Failures
- Verify API files exist and are accessible
- Check required PHP extensions
- Test API endpoints individually
- Check server URL configuration

#### RSS Feed Issues
- Verify SimpleXML extension is installed
- Check feed URL accessibility
- Validate XML format
- Test feed parsing manually

#### Weather API Problems
- Verify API key configuration
- Check cURL extension availability
- Test API endpoint manually
- Verify API service status

### Performance Optimization

#### Database Optimization
- Use connection pooling
- Implement query caching
- Optimize database indexes
- Monitor query performance

#### File System Optimization
- Use appropriate caching
- Implement file compression
- Optimize upload handling
- Monitor disk space

#### API Optimization
- Implement API caching
- Use HTTP/2 if available
- Optimize response formats
- Monitor API response times

## Security Considerations

### Test System Security
- Restrict access to test files
- Use IP-based restrictions
- Implement authentication
- Regular security audits

### Data Protection
- Don't expose sensitive data in reports
- Use secure connections (HTTPS)
- Implement rate limiting
- Regular security updates

## Integration Options

### Monitoring Integration
- Integrate with monitoring systems
- Set up alert notifications
- Create automated responses
- Log all test results

### CI/CD Integration
- Add tests to deployment pipeline
- Automated testing on changes
- Rollback on test failures
- Performance regression testing

## Maintenance

### Regular Tasks
- Update test configurations
- Review test results
- Update security settings
- Monitor system performance

### Updates and Improvements
- Add new test categories
- Improve error reporting
- Update UI/UX elements
- Enhance security features

## Support and Documentation

### Getting Help
- Review test results carefully
- Check system logs
- Consult documentation
- Contact support if needed

### Documentation Updates
- Keep this guide updated
- Document new features
- Share best practices
- Update troubleshooting guides

## Conclusion

The PK Live News Connection Test System provides comprehensive monitoring and diagnostic capabilities for your news website. Regular use of this system ensures optimal performance, security, and reliability of all critical components.

For best results, run connection tests regularly, address issues promptly, and maintain up-to-date configurations. This proactive approach helps prevent downtime and ensures optimal user experience.

---

**Last Updated**: April 9, 2026
**Version**: 1.0
**Compatibility**: PHP 7.4+, MySQL 5.7+, Apache 2.4+
