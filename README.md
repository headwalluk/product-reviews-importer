# Product Reviews Importer

A WordPress plugin for importing and exporting WooCommerce product reviews.

**Version:** 1.2.0
**Author:** Paul Faulkner
**Website:** https://headwall-hosting.com/

---

## Features

**Import:**
- CSV import with native PHP parsing and UTF-8 BOM detection
- AJAX upload with secure file validation and progress tracking
- Batch processing (50 rows per batch) prevents timeouts on large files
- Real-time progress bar with percentage updates
- Detailed row-level error reporting
- Automatic product matching via SKU (supports variations)
- Smart duplicate handling — updates existing reviews (product + email)
- Optional WordPress user account creation for new reviewers
- Multi-line review text with line break preservation
- Memory-efficient streaming for large CSV files

**Export:**
- Walmart review syndication CSV export
- All approved reviews in Walmart's required column format
- Dates formatted as MM/DD/YYYY per Walmart specification
- Product URLs and reviewer names included automatically
- UTF-8 BOM for Excel compatibility

**General:**
- WooCommerce HPOS compatible
- Settings link on Plugins page
- Translation ready
- Extensible via filter hooks

---

## Requirements

- WordPress 6.0+
- WooCommerce 7.0+
- PHP 8.0+

---

## Installation

1. Upload to `/wp-content/plugins/product-reviews-importer/`
2. Activate the plugin through WordPress admin
3. Navigate to **WooCommerce > Import Reviews**

---

## CSV Import Format

| Column | Required | Description |
|--------|----------|-------------|
| SKU | Yes | Product SKU |
| Author Name | Yes | Reviewer's name |
| Author Email | No (recommended) | Enables duplicate detection and user account linking |
| Review Text | Yes | Review content (multi-line supported in quotes) |
| Review Stars | Yes | Star rating (1-5) |
| Author IP | No | IP address (defaults to server IP if blank) |
| Review Date | No | Date in `Y-m-d H:i:s T` format (defaults to current time) |

### Example CSV

```csv
"SKU","Author Name","Author Email","Review Text","Review Stars","Author IP","Review Date"
"ABC123","John Doe","john.doe@example.com","The product is great - recommended","5","123.123.123.123","2026-01-01 09:00:00 GMT"
"ABC123","Jane Doe","jane.doe@example.com","Terrible product, I hate it","1","","2026-01-02 09:00:00 CET"
```

---

## How It Works

### Product Matching

- Products are matched by SKU
- If the SKU belongs to a variation, the review is added to the parent product
- Reviews for non-existent products are skipped and logged as errors

### Duplicate Detection

Reviews are uniquely identified by **product ID + author email** (when email is provided):
- If both match an existing review: updates review text and star rating only
- Without email: each import creates a new review (no duplicate detection)

### User Account Creation

Configurable in Settings:
- **Enabled:** Creates users in "Customer" role for new email addresses
- **Disabled:** Reviews are added as guest comments (user_id = 0)
- Existing users are always linked by email

---

## Settings

Navigate to **WooCommerce > Import Reviews > Settings** to configure:

- **Create user accounts:** Enable/disable user account creation for new reviewers
- **Minimum review length:** Minimum character count for review text
- **Default IP address:** IP used when Author IP column is blank
- **Auto-approve reviews:** Automatically approve imported reviews
- **Mark as verified purchase:** Mark imported reviews as verified purchases

---

## License

GPL v2 or later

---

## Support

For issues and feature requests, please contact [Paul Faulkner](https://headwall-hosting.com/).
