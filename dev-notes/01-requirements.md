# Product Reviews Importer - Requirements Document

**Version:** 1.0.0  
**Last Updated:** 16 January 2026  
**Developer:** Paul Faulkner (https://headwall-hosting.com/)  
**Plugin Slug:** `product-reviews-importer`

---

## Project Overview

A WordPress plugin that imports product reviews from multiple sources into WooCommerce products. Reviews are stored as WordPress comments with `comment_type=review` and include star rating metadata.

The plugin is designed with extensibility in mind - multiple import sources can be added over time through a common import interface.

---

## Core Functionality

### Phase 1: CSV Importer (Initial Release)

**Import Source:** CSV file upload

**Supported Fields:**
- SKU (required - used to match product)
- Author Name (required)
- Author Email (required)
- Author IP (optional - defaults to server IP if blank)
- Review Date (required - format: `Y-m-d H:i:s T`)
- Review Text (required - multi-line supported)
- Review Stars (required - 1-5)

**CSV Format:**
- All fields must be quoted
- Review Text can span multiple lines
- Variable products: Use parent product ID for review

**Import Process:**
1. User uploads CSV file via admin interface
2. System validates CSV structure and data
3. Preview shows mapping and potential issues
4. User confirms import
5. System processes reviews in batches
6. Results summary displayed (success/error counts)

### Phase 2+: Future Import Sources

**Planned Sources:**
- Google Reviews (via Google Place ID)
- Other review platforms (TBD)

Each source will implement a common import interface for consistency.

---

## Technical Requirements

### WordPress & WooCommerce

- **Minimum WordPress Version:** 6.0
- **Minimum WooCommerce Version:** 7.0
- **Minimum PHP Version:** 8.0
- **WooCommerce Dependency:** Plugin requires WooCommerce to be active

### Data Storage

**Review Storage:**
- Use WordPress `wp_comments` table
- Set `comment_type` to `review`
- Link to product via `comment_post_ID`

**Review Meta (wp_commentmeta):**
- `rating` - Star rating (1-5)
- `verified` - Boolean for verified purchase status

**Import History:**
- Track import batches (optional logging table or post meta)
- Record source, timestamp, counts, errors

### HPOS Compatibility

- Declare compatibility with WooCommerce High-Performance Order Storage
- Use WC_Product CRUD methods (not direct post meta access)

---

## User Interface

### Admin Page Location

**Option 1:** Tools menu  
`/wp-admin/tools.php?page=product-reviews-importer`

**Option 2:** WooCommerce menu  
`/wp-admin/admin.php?page=wc-product-reviews-importer`

**Recommended:** WooCommerce menu (more contextual for users)

### Page Structure

**Tab-based interface:**
1. **Import Reviews** - Main import interface
2. **Import History** - Log of past imports
3. **Settings** - Configuration options
4. **Help** - Documentation and troubleshooting

### Import Interface (CSV)

**Step 1: Upload File**
- File upload field
- Accept `.csv` file types only
- Max file size validation

**Step 2: Map Fields**
- Auto-detect common column headers
- Manual mapping for unmapped fields
- Preview first 5 rows with mapping applied

**Step 3: Validation Preview**
- Show potential issues (missing products, invalid emails, etc.)
- Option to skip invalid rows or halt on error
- Estimated import count

**Step 4: Import**
- Progress indicator
- Real-time status updates (optional via AJAX)
- Batch processing to avoid timeouts

**Step 5: Results**
- Success count
- Error count with details
- Option to download error log

---

## Data Validation & Error Handling

### Required Field Validation

**Product Identifier:**
- Must resolve to existing WooCommerce product
- Support SKU, Product ID, or Product Name matching
- Error if product not found (with option to skip)

**Reviewer Email:**
- Valid email format required
- Duplicate check (optional setting: allow/prevent duplicate reviews from same email on same product)

**Star Rating:**
- Must be numeric 1-5
- Error if outside range

**Review Content:**
- Minimum length (optional setting, default: 10 characters)
- Maximum length: WordPress comment field limit (65,535 chars)

### Data Sanitization

- **Review Text:** Use `wp_kses()` with very limited tags to preserve line breaks/paragraphs only
  - Allowed tags: `<br>`, `<p>` (no attributes)
  - Convert plain text line breaks to HTML
- **Email addresses:** Sanitize with `sanitize_email()`
- **Date formats:** Parse `Y-m-d H:i:s T` format
- **IP addresses:** Validate format, default to server IP if blank
- Remove excess whitespace from Author Name

### Error Handling Strategies

**Skip Invalid Rows:**
- Log error, continue processing
- Report all errors at end

**Halt on First Error:**
- Stop processing immediately
- Allow user to fix and retry

**Validation-Only Mode:**
- Check all rows without importing
- Report all issues for user review

---

## Import Processing

### Batch Processing

- Process reviews in batches (e.g., 50-100 per batch)
- Avoid PHP timeout issues on large imports
- Use WordPress Transients for progress tracking
- AJAX-based progress updates (optional)

### Duplicate Detection

**Unique Key:** `product_id` + `author_email`

**Logic:**
- A user cannot leave multiple reviews for a single product
- Use SKU to determine product_id
- If variable product, use parent product ID
- If review exists (matching product_id + author_email):
  - **Update** existing review content and star rating only
  - Do not update author name, email, date, or IP
- If review does not exist:
  - **Create** new review

**Priority:** Data integrity over speed (manual, infrequent imports)

### Performance Considerations

- Use `wp_insert_comment()` for review creation
- Batch insert where possible
- Consider memory limits for large CSV files
- Implement file streaming for very large files

---

## Security

### Nonce Verification

- All form submissions require nonce
- AJAX endpoints verify nonce

### Capability Checks

- Require `manage_woocommerce` capability (or `manage_options`)
- Check on page access and AJAX handlers

### File Upload Security

- Validate file type (CSV only)
- Sanitize filename
- Store uploaded files in secure location
- Delete after processing (optional retention setting)

### Input Sanitization

- Sanitize all user inputs
- Escape all outputs
- Use `wp_kses()` for allowed HTML in reviews

---

## Settings

### Import Settings

- **Create user accounts when importing reviews from new email addresses** (yes/no)
  - If **yes**: Create WordPress user in `Customer` role, link user_id to comment
  - If **no**: Set comment user_id to `0` (guest comment)
  - Always check for existing user by email first
- Minimum review content length (default: 10 characters)
- Default IP address (server IP when Author IP is blank)

### Email Notifications (Optional)

- Notify admin on import completion
- Notify product authors of new reviews (optional)

### Cleanup Settings

- Auto-delete uploaded CSV files after import
- Retention period for import logs

---

## Extensibility

### Import Source Interface

```php
interface Review_Import_Source {
    public function get_name(): string;
    public function get_description(): string;
    public function render_import_form(): void;
    public function process_import( array $data ): array;
    public function validate_data( array $data ): array;
}
```

**Future Sources:**
- CSV_Import_Source (Phase 1)
- Google_Import_Source (Phase 2)
- [Other sources as needed]

### Hooks & Filters

**Actions:**
- `pri_before_import` - Before import starts
- `pri_after_import` - After import completes
- `pri_review_imported` - After each review imported

**Filters:**
- `pri_csv_columns` - Modify recognized CSV columns
- `pri_review_data` - Filter review data before insert
- `pri_import_capability` - Change required capability

---

## Data Flow

### CSV Import Flow

1. **Upload:** User uploads CSV file
2. **Parse:** Read CSV, detect columns
3. **Map:** User maps CSV columns to review fields
4. **Validate:** Check all rows for errors
5. **Preview:** Show validation results, get confirmation
6. **Process:** Import reviews in batches
7. **Report:** Display success/error summary

### Review Creation Flow

1. Match product by SKU (use parent ID if variable product)
2. Validate reviewer email
3. Check for duplicate (product_id + author_email)
4. Determine user_id and author name:
   - Check if email matches existing WordPress user
   - If match: use existing user_id **and WordPress user's display_name**
   - If no match and "Create accounts" enabled: create Customer user, use new user_id and CSV author name
   - If no match and "Create accounts" disabled: user_id = 0, use CSV author name
5. Sanitize review text (wp_kses with br/p tags only)
6. If duplicate exists:
   - Update comment_content and rating meta only
   - Do NOT update author name, email, date, or IP
7. If new review:
   - Create comment with `comment_type=review`
   - Set user_id, author name (from WordPress user if exists, else CSV), email, IP, date
   - Add rating meta and verified status
8. WooCommerce automatically updates product rating average

**Author Name Priority:**
- Existing WordPress user: Use user's `display_name` (ignores CSV Author Name)
- New user created: Use CSV Author Name as display_name
- Guest comment: Use CSV Author Name

---

## Error Messages

### User-Facing Errors

- "Product not found: {SKU/ID/Name}"
- "Invalid email address: {email}"
- "Invalid star rating: {value} (must be 1-5)"
- "Review content too short (minimum {n} characters)"
- "Duplicate review detected for {email} on {product}"

### Admin Notices

- "Import completed: {n} reviews imported, {n} errors"
- "CSV file uploaded successfully"
- "Invalid CSV format - missing required columns"

---

## Success Criteria

### Phase 1 (CSV Importer)

- [ ] User can upload CSV file
- [ ] System validates CSV structure and data
- [ ] User can map CSV columns to review fields
- [ ] User sees preview of import with validation results
- [ ] Reviews are imported into WooCommerce products
- [ ] Star ratings display correctly on products
- [ ] Error handling prevents data corruption
- [ ] Import history is logged
- [ ] Admin can configure import settings

### Future Phases

- [ ] Multiple import sources supported
- [ ] Import source interface allows easy addition of new sources
- [ ] Each source follows consistent UX pattern

---

## Testing Checklist

### CSV Import Testing

- [ ] Small CSV (< 10 rows)
- [ ] Large CSV (> 1000 rows)
- [ ] Missing required fields
- [ ] Invalid email addresses
- [ ] Invalid star ratings
- [ ] Non-existent products
- [ ] Duplicate reviews
- [ ] Special characters in content
- [ ] Different date formats
- [ ] Empty optional fields

### Integration Testing

- [ ] WooCommerce product rating average updates
- [ ] Review stars display on product page
- [ ] WordPress admin reviews list shows imported reviews
- [ ] HPOS compatibility verified
- [ ] Works with different themes

### Security Testing

- [ ] Nonce verification on all actions
- [ ] Capability checks enforced
- [ ] File upload validates type
- [ ] XSS prevention in review content
- [ ] SQL injection prevention

---

## Future Enhancements

### Potential Features

- Export reviews to CSV
- Bulk edit imported reviews
- Import product review replies
- Import review images/attachments
- Schedule automated imports (cron)
- API endpoint for programmatic imports
- Import from URL (remote CSV)
- Multi-site support

### Google Reviews Integration

- Require Google Place ID
- Fetch reviews via Google Places API
- Map Google review fields to WooCommerce
- Handle rate limiting
- Store sync status to avoid re-importing

---

## Notes

- Follow WordPress Coding Standards (verified with PHPCS)
- Use namespaces: `Product_Reviews_Importer`
- Prefix functions: `pri_`
- Text domain: `product-reviews-importer`
- All dates stored in `Y-m-d H:i:s T` format
- HPOS compatible - no direct `get_post_meta()` for products/orders
- Translation ready

---

## Decisions Made

1. ✅ **HTML in review content:** Use `wp_kses()` with `<br>` and `<p>` tags only to preserve line breaks
2. ✅ **Duplicate detection:** product_id + author_email (update content and rating only)
3. ✅ **Import history:** Not necessary - unique key handles create/update
4. ✅ **Review replies:** Not supported
5. ✅ **User accounts:** Setting to create Customer users or use guest comments (user_id=0)

## Sample CSV Data

```csv
"SKU","Author Name","Author Email","Author IP","Review Date","Review Text","Review Stars"
"ABC","John Doe","john.doe@example.com","123.123.123.123","2026-01-01 09:00:00 GMT","The product is great - recommended","5"
"ABC","Jane Doe","jane.doe@example.com","","2026-01-02 09:00:00 CET","Terrible product, I hate it","1"
```

**Notes:**
- All fields quoted
- Review Text can span multiple lines
- Blank IP defaults to server IP

---

**Next Steps:**
1. Review and approve requirements
2. Create plugin file structure
3. Implement base plugin class
4. Build CSV importer
5. Create admin interface
6. Testing and refinement
