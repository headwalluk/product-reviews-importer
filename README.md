# Product Reviews Importer

A WordPress plugin for importing product reviews from multiple sources into WooCommerce.

**Version:** 0.4.0  
**Author:** Paul Faulkner  
**Website:** https://headwall-hosting.com/

---

## Features

- **CSV Import:** Native PHP parsing with UTF-8 BOM detection
- **AJAX Upload:** Secure file upload with validation and progress tracking
- **Batch Processing:** Handles large files without timeouts (10 rows per batch)
- **Real-time Progress:** Live progress bar with percentage updates
- **Error Reporting:** Detailed row-level error feedback for failed imports
- **Smart Product Matching:** Automatic product lookup via SKU (supports variations)
- **Duplicate Handling:** Updates existing reviews (product + email unique key)
- **User Management:** Creates WordPress accounts for new reviewers (optional)
- **Multi-line Support:** Preserves line breaks in review text
- **Memory Efficient:** Streams large CSV files without loading into memory
- **Author Name Intelligence:** Uses WordPress user's display_name when email matches
- **Public IP Detection:** Fetches server's public IP (cached, secure fallback)
- **HPOS Compatible:** Full WooCommerce High-Performance Order Storage support

---

## Current Status

**Version 0.4.0** - CSV import with AJAX orchestration complete

✅ **Completed:**
- Core import engine with duplicate detection
- CSV parser with validation and batch reading
- Settings management (5 configurable options)
- Admin interface with tabbed navigation
- AJAX file upload with security validation
- Batch processing with progress tracking
- Real-time error reporting with row numbers
- All templates code-first (WordPress standards compliant)

🚧 **Pending Testing:**
- New user account creation (setting enabled)
- Large CSV files (1000+ rows)
- Client's actual CSV data

📋 **Planned Features:**
- Google Reviews import via Place ID
- Export reviews to CSV
- Import history tracking

---

## Requirements

- WordPress 6.0 or higher
- WooCommerce 7.0 or higher
- PHP 8.0 or higher

---

## Installation

1. Upload to `/wp-content/plugins/product-reviews-importer/`
2. Activate the plugin through WordPress admin
3. Navigate to **WooCommerce > Reviews Importer**

---

## CSV Format

Your CSV file should have the following columns (all fields quoted):

| Column | Required | Description |
|--------|----------|-------------|
| SKU | Yes | Product SKU |
| Author Name | Yes | Reviewer's name |
| Author Email | Yes | Reviewer's email address |
| Author IP | No | IP address (defaults to server IP if blank) |
| Review Date | Yes | Date in `Y-m-d H:i:s T` format |
| Review Text | Yes | Review content (multi-line supported) |
| Review Stars | Yes | Star rating (1-5) |

### Example CSV

```csv
"SKU","Author Name","Author Email","Author IP","Review Date","Review Text","Review Stars"
"ABC123","John Doe","john.doe@example.com","123.123.123.123","2026-01-01 09:00:00 GMT","The product is great - recommended","5"
"ABC123","Jane Doe","jane.doe@example.com","","2026-01-02 09:00:00 CET","Terrible product, I hate it","1"
```

---

## How It Works

### Product Matching

- Products are matched by SKU
- If the SKU belongs to a variable product, the review is added to the parent product
- Reviews for non-existent products are skipped and logged as errors

### Duplicate Detection

Reviews are uniquely identified by **product ID + author email**.

- If a review already exists: Updates review text and star rating only
- If new review: Creates a new comment with all provided details

### User Account Creation

Configure in settings whether to create WordPress user accounts:

- **Enabled:** Creates users in "Customer" role for new email addresses
- **Disabled:** Reviews are added as guest comments (user_id = 0)
- Existing users are always linked by email

### Review Text Formatting

- Review text can span multiple lines in CSV
- Plain text line breaks are converted to `<br>` tags
- Only `<br>` and `<p>` HTML tags are allowed (sanitized with `wp_kses()`)

---

## Settings

Navigate to **WooCommerce > Reviews Importer > Settings** to configure:

- **Create user accounts:** Enable/disable user account creation for new reviewers
- **Minimum review length:** Minimum character count for review text
- **Default IP address:** IP used when Author IP column is blank

---

## Development

### File Structure

```
product-reviews-importer/
├── product-reviews-importer.php   # Main plugin file
├── constants.php                   # Plugin constants
├── functions.php                   # Helper functions
├── includes/                       # Core classes
│   ├── class-plugin.php
│   ├── class-settings.php
│   ├── class-admin-hooks.php
│   └── class-csv-importer.php
├── admin-templates/                # Admin template files
├── assets/                         # CSS/JS files
├── languages/                      # Translation files
└── dev-notes/                      # Development documentation
```

### Coding Standards

This plugin follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

Run PHPCS to check code quality:

```bash
phpcs
phpcbf  # Auto-fix issues
```

---

## Future Development

Planned features for future releases:

- Google Reviews import (via Place ID)
- Additional import sources
- Export reviews to CSV
- Scheduled/automated imports
- Import history tracking UI

---

## License

GPL v2 or later

---

## Support

For issues and feature requests, please contact [Paul Faulkner](https://headwall-hosting.com/).
