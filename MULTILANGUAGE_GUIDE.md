# Multi-Language Support for PK Live News

This guide explains how to use and manage the multi-language functionality added to PK Live News.

## Features

### 🌍 Supported Languages
- **English** (🇺🇸) - Default language
- **Urdu** (🇵🇰) - اردو
- **Hindi** (🇮🇳) - हिन्दी
- **Chinese** (🇨🇳) - 中文
- **Pashto** (🇦🇫) - پښتو

### 🎯 Key Features
- **Language Switcher**: Users can easily switch between languages
- **Multilingual News**: Articles can have content in multiple languages
- **SEO Friendly**: Automatic hreflang tags for better search engine optimization
- **Admin Management**: Complete control panel for language management
- **Smart Detection**: Auto-detects user's browser language preference
- **Fallback System**: Gracefully falls back to English if translation is missing

## Setup Instructions

### 1. Database Setup
Run the setup script to initialize the database:
```
http://your-site.com/setup_multilang.php
```

Or manually execute the SQL:
```sql
-- Run database_update_multilang.sql
```

### 2. File Structure
The following files are created/updated:
- `includes/language_functions.php` - Core language functions
- `components/language_switcher.php` - User-facing language switcher
- `admin/manage_languages.php` - Language management panel
- `admin/add_news_multilang.php` - Multilingual news editor
- `database_update_multilang.sql` - Database structure

## User Guide

### For Website Visitors

#### Language Switcher
1. Look for the language switcher in the top header
2. Click the dropdown to see available languages
3. Select your preferred language
4. The page will reload with content in your chosen language

#### Automatic Detection
- The system automatically detects your browser language
- If your language is available, it will be selected automatically
- You can always override this by using the language switcher

### For Administrators

#### Managing Languages
1. Go to **Admin Panel → Languages**
2. View all available languages
3. Add new languages or edit existing ones
4. Enable/disable languages as needed
5. Set sort order for language display

#### Language Settings
In the Languages admin panel, you can configure:
- **Default Language**: Primary language for the website
- **Language Switcher**: Enable/disable the user-facing switcher
- **Country Flags**: Show/hide flag icons
- **Auto-Detection**: Enable browser language detection
- **Multilingual SEO**: Enable hreflang tags for search engines

#### Creating Multilingual News
1. Go to **Admin Panel → Multilingual News**
2. Select the primary language for the article
3. Enter the main content (title, summary, content)
4. Optionally add translations for other languages
5. Publish the article

#### Translating Existing News
1. Edit an existing news article
2. Use the multilingual editor to add translations
3. Save to make content available in multiple languages

## Technical Details

### Database Structure

#### Languages Table
```sql
languages
├── id (Primary Key)
├── code (Language code: en, ur, hi, zh, ps)
├── name (English name: English, Urdu, Hindi)
├── native_name (Native name: English, اردو, हिन्दी)
├── flag_icon (🇺🇸, 🇵🇰, 🇮🇳, 🇨🇳, 🇦🇫)
├── is_active (Enable/disable language)
└── sort_order (Display order)
```

#### Enhanced News Table
```sql
news
├── language_code (Primary language of article)
├── title_ur, title_hi, title_zh, title_ps (Translated titles)
├── content_ur, content_hi, content_zh, content_ps (Translated content)
├── summary_ur, summary_hi, summary_zh, summary_ps (Translated summaries)
└── ... (existing columns)
```

### Key Functions

#### Language Functions (`includes/language_functions.php`)
- `get_current_language()` - Returns user's current language
- `get_active_languages()` - Gets all enabled languages
- `get_news_title()` - Returns translated news title
- `get_news_content()` - Returns translated news content
- `get_language_url()` - Generates URL for specific language
- `generate_hreflang_tags()` - Creates SEO-friendly hreflang tags

#### Language Detection Priority
1. **URL Parameter** (`?lang=ur`)
2. **Session Storage** (Previous selection)
3. **User Preference** (Saved in database)
4. **Browser Detection** (Auto-detect)
5. **Default Setting** (Fallback to English)

### URL Structure
Language is controlled via URL parameter:
```
https://your-site.com/news.php?slug=example&lang=ur
```

## Customization

### Adding New Languages
1. Go to **Admin Panel → Languages**
2. Click "Add Language"
3. Enter language details:
   - **Code**: 2-letter ISO code (e.g., `fr` for French)
   - **Name**: English name (e.g., "French")
   - **Native Name**: Name in native language (e.g., "Français")
   - **Flag Icon**: Country flag emoji (e.g., "🇫🇷")
4. Set sort order and enable the language

### Custom Styling
Language switcher styles are in `components/language_switcher.php`:
```css
.language-switcher .dropdown-toggle
.language-switcher .dropdown-menu
.language-switcher .dropdown-item
```

### RTL Support
The system includes basic RTL support for languages like Urdu and Arabic:
```css
[dir="rtl"] .language-switcher .dropdown-menu {
    right: auto;
    left: 0;
}
```

## Troubleshooting

### Common Issues

#### Language Not Switching
- Check if language is enabled in admin panel
- Verify language switcher is enabled in settings
- Clear browser cache and cookies

#### Missing Translations
- Add translations using the multilingual news editor
- The system falls back to English if translation is missing
- Check database for proper language columns

#### SEO Issues
- Enable "Multilingual SEO" in language settings
- Verify hreflang tags are generated in page source
- Submit sitemaps for each language version

#### Database Errors
- Run `setup_multilang.php` to verify database setup
- Check if all language columns exist in news table
- Verify languages table has required data

### Debug Mode
Add this to your PHP file to debug language issues:
```php
$current_lang = get_current_language();
echo "Current Language: " . $current_lang;
echo "Available Languages: ";
print_r(get_active_languages());
```

## API Usage

### Getting Translated Content
```php
// Get translated title
$title = get_news_title($news_item);

// Get translated content
$content = get_news_content($news_item);

// Get translated content for specific language
$urdu_title = get_news_title($news_item, 'ur');
```

### Language URLs
```php
// Generate URL for specific language
$urdu_url = get_language_url('ur');

// Generate hreflang tags
echo generate_hreflang_tags();
```

## Performance Considerations

- **Database Indexing**: Language columns are indexed for better performance
- **Caching**: Language preferences are stored in sessions
- **Minimal Queries**: Only loads required language data
- **Efficient Fallbacks**: Graceful degradation if translations are missing

## Security

- **Input Sanitization**: All language inputs are properly sanitized
- **SQL Injection Prevention**: Uses prepared statements
- **XSS Protection**: Language codes are validated before use
- **Access Control**: Admin functions require proper permissions

## Future Enhancements

Potential improvements for future versions:
- **Translation Management System**: Professional translation workflow
- **Language-Specific URLs**: Clean URLs without query parameters
- **Auto-Translation**: Integration with translation APIs
- **Content Localization**: Date/time formatting, currency, etc.
- **Language Analytics**: Track language usage statistics

## Support

For issues or questions:
1. Check this guide first
2. Review the admin panel settings
3. Verify database setup with `setup_multilang.php`
4. Test with different languages and browsers

---

**Version**: 1.0  
**Last Updated**: 2026-03-19  
**Compatible with**: PK Live News System
