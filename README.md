# HTH Sample Plugin - WordPress Plugin Development Tutorial

A comprehensive WordPress plugin developed for educational purposes, demonstrating various WordPress development concepts through practical examples.
You can follow the video tutorial on [youtube](https://www.youtube.com/playlist?list=PLBVvocIbiZ9mbk7D8oAg7UYAPQ_Zyjfpb) for this repo

## 📋 Overview

This plugin serves as a complete tutorial for WordPress plugin development, covering essential topics from basic hooks to advanced database operations. Each chapter focuses on a specific aspect of WordPress development.

## 🎯 Learning Objectives

After working through this plugin, you will understand:
- WordPress hooks (actions and filters)
- Creating custom shortcodes
- Building custom post types
- REST API integration
- Widget development
- Script and style enqueuing
- Database operations with custom tables

## 📁 Plugin Structure

```
hth-sample-plugin/
├── hth-sample-plugin.php          # Main plugin file
├── README.md                      # This documentation
└── chapters/
    ├── actions.php                # Chapter 1: Actions and Filters
    ├── shortcodes.php             # Chapter 2: Shortcodes
    ├── custom-post-types.php      # Chapter 3: Custom Post Types
    ├── rest-api.php               # Chapter 4: REST API
    ├── enqueue-scripts.php        # Chapter 5: Scripts and Styles
    ├── widgets.php                # Chapter 6: Widgets
    └── database-operations.php    # Chapter 7: Database Operations
```

## 🚀 Installation

1. Download or clone this repository
2. Upload the `hth-sample-plugin` folder to your WordPress `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. The plugin will automatically create a custom database table upon activation

## 📚 Chapter Breakdown

### Chapter 1: Actions and Filters (Hooks)
**File:** `chapters/actions.php`

Learn about WordPress hooks system:
- **Actions**: Execute code at specific points
- **Filters**: Modify data before it's displayed or saved

**Key Functions:**
- Hook into WordPress lifecycle events
- Create custom actions and filters
- Modify existing WordPress behavior

### Chapter 2: Shortcodes
**File:** `chapters/shortcodes.php`

Create custom shortcodes for dynamic content:
- Simple shortcodes
- Shortcodes with attributes
- Self-closing and enclosing shortcodes

**Usage Examples:**
```
[hth_shortcode]
[hth_shortcode attribute="value"]
[hth_shortcode]Content here[/hth_shortcode]
```

### Chapter 3: Custom Post Types
**File:** `chapters/custom-post-types.php`

Build custom content types:
- Register custom post types
- Set up custom fields
- Configure post type capabilities and features

**Features:**
- Custom post type registration
- Admin interface integration
- Custom post type queries

### Chapter 4: REST API
**File:** `chapters/rest-api.php`

Extend WordPress REST API:
- Create custom endpoints
- Handle GET, POST, PUT, DELETE requests
- Implement authentication and permissions

**API Endpoints:**
- Custom data retrieval
- CRUD operations via REST
- JSON response formatting

### Chapter 5: Scripts and Styles
**File:** `chapters/enqueue-scripts.php`

Properly load assets:
- Enqueue CSS and JavaScript files
- Handle dependencies
- Conditional loading (admin vs frontend)

**Best Practices:**
- Use `wp_enqueue_script()` and `wp_enqueue_style()`
- Proper dependency management
- Version control for cache busting

### Chapter 6: Widgets
**File:** `chapters/widgets.php`

Develop custom widgets:
- Widget class structure
- Admin widget form
- Frontend widget display
- Widget configuration options

**Widget Features:**
- Customizable widget options
- Dynamic content display
- Integration with WordPress widget system

### Chapter 7: Database Operations
**File:** `chapters/database-operations.php`

Work with custom database tables:
- Create custom tables
- CRUD operations (Create, Read, Update, Delete)
- Data sanitization and validation
- Admin interface for data management

**Database Features:**
- Custom table creation with `dbDelta()`
- Secure data insertion with `$wpdb->insert()`
- Data retrieval with `$wpdb->get_results()`
- Admin menu for data management

## 🔧 Key Features

### Custom Database Table
The plugin creates a custom table `wp_hth_custom_table` with the following structure:
- `id` (mediumint): Primary key, auto-increment
- `name` (tinytext): Name field
- `value` (text): Value field

### Admin Interface
- **Custom Data Menu**: Access via WordPress admin sidebar
- **Data Display**: View all custom table data in a formatted table
- **Dashboard Icon**: Uses `dashicons-database` for visual consistency

### Activation/Deactivation Hooks
- **Activation**: Creates custom table and inserts sample data
- **Deactivation**: Removes custom table and all data

## 🛠️ Usage Examples

### Using Shortcodes
```php
// In your posts or pages
[hth_shortcode]

// With attributes
[hth_shortcode attr="value"]
```

### Accessing Custom Data
```php
// Get all custom data
$data = hth_get_custom_data();

// Insert new data
hth_insert_custom_data('Name', 'Value');

// Update existing data
hth_update_custom_data(1, 'New Name', 'New Value');

// Delete data
hth_delete_custom_data(1);
```

### REST API Usage
```javascript
// Example API calls (replace with actual endpoints)
fetch('/wp-json/hth/v1/custom-data')
  .then(response => response.json())
  .then(data => console.log(data));
```

## 🔒 Security Features

- **Data Sanitization**: All input data is sanitized using WordPress functions
- **Nonce Verification**: Forms include nonce fields for security
- **Capability Checks**: Admin functions require appropriate user permissions
- **SQL Injection Prevention**: Uses prepared statements and WordPress database methods

## 📝 Development Notes

### Best Practices Demonstrated
1. **File Organization**: Modular structure with separate files for each topic
2. **Naming Conventions**: All functions prefixed with `hth_` to prevent conflicts
3. **WordPress Standards**: Follows WordPress coding standards and practices
4. **Documentation**: Comprehensive inline comments explaining functionality

### Database Operations
- Uses WordPress `$wpdb` class for all database operations
- Implements proper error handling and data validation
- Follows WordPress database naming conventions

### Hook System
- Demonstrates both actions and filters
- Shows proper hook timing and priority usage
- Includes examples of removing hooks

## 🎥 Tutorial Video

This plugin was created as a companion to a YouTube tutorial series. Each chapter corresponds to a specific video lesson, making it easy to follow along with the tutorial content.

## 🤝 Contributing

This is an educational project. Feel free to:
- Suggest improvements
- Add additional examples
- Report issues or bugs
- Contribute documentation

## 📄 License

This plugin is provided for educational purposes. Use it as a learning resource and foundation for your own WordPress plugin development.

## 👨‍💻 Author

**Jacques Artgraven**  
Website: [hackthehologram.com](https://hackthehologram.com)

## 🔍 Troubleshooting

### Common Issues

1. **Plugin Not Activating**
   - Check file permissions
   - Verify WordPress version compatibility
   - Check for PHP errors in debug log

2. **Database Table Not Created**
   - Ensure proper file permissions
   - Check WordPress database user permissions
   - Verify activation hook is properly registered

3. **Admin Menu Not Showing**
   - Verify user has `manage_options` capability
   - Check for plugin conflicts
   - Ensure proper action hook registration

### Debug Information
Enable WordPress debug mode to see detailed error messages:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📚 Further Reading

- [WordPress Plugin Developer Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Database API](https://developer.wordpress.org/apis/handbook/database/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WordPress Shortcode API](https://developer.wordpress.org/plugins/shortcodes/)

---

*This documentation is part of the HTH Education WordPress plugin development tutorial series.*
