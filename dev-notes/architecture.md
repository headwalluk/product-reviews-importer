# Product Reviews Importer - Architecture

**Last Updated:** 16 January 2026  
**Purpose:** Component design and interaction patterns for building pluggable import sources

---

## Design Philosophy

**Core Principle:** Separation of import logic from data sources and orchestration.

- **Import Engine** - Source-agnostic review processing (product matching, deduplication, comment creation)
- **Source Adapters** - Pluggable data sources (CSV, Amazon API, etc.) with common interface
- **Orchestrators** - Transport layer (AJAX for manual imports, Cron for automated syncs)

This design allows adding new import sources without modifying core logic.

---

## Component Overview

```
┌─────────────────────────────────────────────────────────────────┐
│ UI Layer (Admin Templates + AJAX/Cron)                         │
├─────────────────────────────────────────────────────────────────┤
│ • admin-templates/main-page.php (tabbed interface)             │
│ • AJAX Handlers (orchestrate manual imports)                   │
│ • Cron Handlers (orchestrate automated syncs)                  │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────┐
│ Source Adapter Layer (implements Review_Import_Source)         │
├─────────────────────────────────────────────────────────────────┤
│ • CSV_Importer (file parsing, streaming, batching)             │
│ • Amazon_Importer (future - API calls, pagination)             │
│ • [Other sources as needed]                                    │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────────┐
│ Core Engine (Review_Importer)                                  │
├─────────────────────────────────────────────────────────────────┤
│ • import_reviews(array $reviews): array                        │
│ • Product matching by SKU (handles variations)                 │
│ • Duplicate detection (product_id + author_email)              │
│ • User account creation (if enabled)                           │
│ • Comment creation with review type and rating meta            │
└─────────────────────────────────────────────────────────────────┘
```

---

## Core Classes

### 1. Review_Importer (Core Engine)

**Purpose:** Process review data regardless of source.

**Location:** `includes/class-review-importer.php`

**Key Methods:**

```php
/**
 * Import a batch of reviews.
 *
 * @param array $reviews Array of review data (normalized format).
 * @return array Results with success/error counts and details.
 */
public function import_reviews( array $reviews ): array;

/**
 * Import a single review.
 *
 * @param array $review_data Single review data (normalized format).
 * @return int|WP_Error Comment ID on success, WP_Error on failure.
 */
public function import_review( array $review_data );
```

**Normalized Review Format:**

All source adapters must convert their data to this format before passing to Review_Importer:

```php
array(
    'product_sku'   => 'ABC123',        // Required - used to find product
    'author_name'   => 'John Doe',      // Required
    'author_email'  => 'john@example.com', // Optional (recommended)
    'author_ip'     => '123.45.67.89',  // Optional - defaults to server IP
    'review_date'   => '2026-01-15 14:30:00 GMT', // Required - Y-m-d H:i:s T format
    'review_text'   => 'Great product!', // Required
    'review_stars'  => 5,               // Required - integer 1-5
    'verified'      => false,           // Optional - boolean, defaults to false
)
```

**Responsibilities:**
- Validate review data structure
- Match product by SKU (use parent ID for variations)
- Check for duplicates (product_id + author_email)
- Create or find user account (if setting enabled)
- Sanitize review text (`wp_kses` with `<br>`, `<p>` only)
- Create/update WordPress comment with `comment_type=review`
- Add rating metadata
- Return success/error results

**Does NOT:**
- Know about CSV files, APIs, or data sources
- Handle UI, progress bars, or AJAX
- Deal with file uploads or API authentication

---

### 2. Source Adapters (Pluggable)

**Purpose:** Convert source-specific data to normalized review format.

**Interface Pattern:**

```php
interface Review_Import_Source {
    /**
     * Get source name.
     */
    public function get_name(): string;
    
    /**
     * Get source description.
     */
    public function get_description(): string;
    
    /**
     * Get total number of reviews available.
     */
    public function get_total_count(): int;
    
    /**
     * Get a batch of reviews (normalized format).
     *
     * @param int $offset Starting position.
     * @param int $limit  Number of reviews to fetch.
     * @return array Array of normalized review data.
     */
    public function get_batch( int $offset, int $limit ): array;
    
    /**
     * Validate source configuration/data.
     *
     * @return array Validation results (errors, warnings).
     */
    public function validate(): array;
}
```

#### CSV_Importer

**Location:** `includes/class-csv-importer.php`

**Purpose:** Parse CSV files, provide batched access to review data.

**Key Methods:**

```php
/**
 * Constructor.
 *
 * @param string $file_path Absolute path to CSV file.
 */
public function __construct( string $file_path );

/**
 * Parse CSV headers and validate structure.
 */
public function parse_headers(): array;

/**
 * Get batch of reviews starting at offset.
 */
public function get_batch( int $offset, int $limit ): array;

/**
 * Get total review count in CSV.
 */
public function get_total_count(): int;

/**
 * Validate CSV structure and data.
 */
public function validate(): array;
```

**Responsibilities:**
- Read CSV file (with streaming for large files)
- Detect/validate column headers
- Map CSV columns to normalized format
- Handle encoding (UTF-8, BOM detection)
- Handle multi-line fields (quoted text)
- Validate data types (email format, star range, etc.)
- Return validation errors before import starts

**Does NOT:**
- Create WordPress comments
- Match products or check duplicates
- Handle file uploads (that's AJAX layer)

---

#### Amazon_Importer (Future)

**Location:** `includes/class-amazon-importer.php`

**Purpose:** Fetch reviews from Amazon API, normalize to review format.

**Key Methods:**

```php
/**
 * Constructor.
 *
 * @param string $api_key    Amazon API credentials.
 * @param string $product_id Amazon product identifier.
 */
public function __construct( string $api_key, string $product_id );

/**
 * Get batch of reviews from Amazon API.
 */
public function get_batch( int $offset, int $limit ): array;
```

**Responsibilities:**
- Authenticate with Amazon API
- Fetch reviews with pagination
- Map Amazon review fields to normalized format
- Handle API rate limiting
- Return normalized review data

**Does NOT:**
- Know about WooCommerce products or WordPress comments
- Handle cron scheduling (that's orchestration layer)

---

### 3. Orchestration Layer

**Purpose:** Coordinate import process, handle transport concerns.

#### AJAX Orchestrator (Manual Imports)

**Location:** `includes/class-admin-hooks.php` (AJAX handlers)

**Flow:**

```
1. User uploads CSV file
   ↓
2. AJAX: Upload handler
   - Store file in temp location
   - Create CSV_Importer instance
   - Validate CSV structure
   - Store file path in transient
   - Return validation results
   ↓
3. User confirms import
   ↓
4. AJAX: Import batch handler (called repeatedly)
   - Retrieve file path from transient
   - Create CSV_Importer($file_path)
   - Get next batch: $csv->get_batch($offset, BATCH_SIZE)
   - Create Review_Importer instance
   - Process batch: $importer->import_reviews($reviews)
   - Update progress transient
   - Return results to browser
   ↓
5. Browser updates progress bar, requests next batch
   ↓
6. Repeat step 4 until complete
   ↓
7. Display final results, cleanup transients
```

**Responsibilities:**
- Handle file uploads (security validation)
- Store temporary state in transients
- Orchestrate batched processing
- Track progress
- Return JSON responses to browser
- Cleanup temp files after import

**Does NOT:**
- Parse CSV or validate review data (that's CSV_Importer)
- Create comments (that's Review_Importer)

---

#### Cron Orchestrator (Automated Imports)

**Location:** `includes/class-cron-handler.php` (future)

**Flow:**

```
1. WP-Cron triggers scheduled import
   ↓
2. Create source adapter (e.g., Amazon_Importer)
   ↓
3. Create Review_Importer instance
   ↓
4. Loop through batches:
   - Get batch from source
   - Process with Review_Importer
   - Log results
   - Continue until no more reviews
   ↓
5. Update last sync time in options
   ↓
6. Send admin notification (optional)
```

**Responsibilities:**
- Schedule imports via WP-Cron
- Loop through all batches automatically (no user waiting)
- Log import results
- Send admin notifications
- Track last sync time

**Does NOT:**
- Know about specific APIs or file formats (that's source adapters)
- Create comments (that's Review_Importer)

---

## Data Flow Diagram

### CSV Import (Manual via AJAX)

```
User Action: Upload CSV file
│
├─> AJAX Handler: ajax_upload_csv()
│   ├─> Validate file type/size
│   ├─> Move to temp directory
│   ├─> Create CSV_Importer($file_path)
│   ├─> Call $csv->validate()
│   ├─> Store file path in transient
│   └─> Return validation results (JSON)
│
User Action: Confirm import
│
├─> AJAX Handler: ajax_import_batch()
│   ├─> Get file path from transient
│   ├─> Create CSV_Importer($file_path)
│   ├─> Get batch: $csv->get_batch($offset, BATCH_SIZE)
│   ├─> Create Review_Importer()
│   ├─> Import batch: $importer->import_reviews($batch)
│   │   ├─> For each review:
│   │   │   ├─> Match product by SKU
│   │   │   ├─> Check for duplicate
│   │   │   ├─> Get/create user account
│   │   │   ├─> Create/update comment
│   │   │   └─> Add rating metadata
│   │   └─> Return results array
│   ├─> Update progress transient
│   └─> Return results (JSON)
│
Browser: Update progress bar, request next batch
│
└─> Repeat ajax_import_batch() until complete
```

### Amazon Import (Future - Automated via Cron)

```
WP-Cron: Trigger scheduled import
│
├─> Cron Handler: sync_amazon_reviews()
│   ├─> Create Amazon_Importer($api_key, $product_id)
│   ├─> Create Review_Importer()
│   ├─> Loop:
│   │   ├─> Get batch: $amazon->get_batch($offset, BATCH_SIZE)
│   │   ├─> Import batch: $importer->import_reviews($batch)
│   │   └─> Increment offset
│   ├─> Update last_sync option
│   └─> Send admin notification
```

---

## File Structure

```
product-reviews-importer/
├── includes/
│   ├── class-plugin.php              # Main plugin orchestration
│   ├── class-settings.php            # WordPress Settings API
│   ├── class-admin-hooks.php         # Admin UI + AJAX handlers
│   ├── class-review-importer.php     # Core import engine ⭐
│   ├── class-csv-importer.php        # CSV source adapter ⭐
│   ├── class-amazon-importer.php     # Future: Amazon source adapter
│   └── class-cron-handler.php        # Future: Cron orchestration
├── admin-templates/
│   ├── main-page.php                 # Tabbed admin interface
│   ├── tab-import.php                # Import tab content
│   ├── tab-settings.php              # Settings tab content
│   └── tab-help.php                  # Help/documentation tab
├── assets/
│   └── admin/
│       ├── admin.css                 # Admin UI styling
│       └── admin.js                  # Tab navigation + AJAX
├── constants.php                     # All magic values
├── functions-private.php             # Helper functions
└── product-reviews-importer.php      # Main plugin file
```

---

## Adding a New Import Source

**Example: Amazon Reviews Importer**

### Step 1: Create Source Adapter

```php
// includes/class-amazon-importer.php
namespace Product_Reviews_Importer;

class Amazon_Importer implements Review_Import_Source {
    
    private string $api_key;
    private string $product_id;
    
    public function __construct( string $api_key, string $product_id ) {
        $this->api_key    = $api_key;
        $this->product_id = $product_id;
    }
    
    public function get_name(): string {
        return __( 'Amazon Reviews', 'product-reviews-importer' );
    }
    
    public function get_description(): string {
        return __( 'Import reviews from Amazon products.', 'product-reviews-importer' );
    }
    
    public function get_total_count(): int {
        // Call Amazon API to get total review count
        return $this->fetch_total_from_api();
    }
    
    public function get_batch( int $offset, int $limit ): array {
        // Fetch reviews from Amazon API with pagination
        $amazon_reviews = $this->call_amazon_api( $offset, $limit );
        
        // Convert to normalized format
        $normalized = array();
        foreach ( $amazon_reviews as $review ) {
            $normalized[] = array(
                'product_sku'   => $this->map_amazon_sku( $review['asin'] ),
                'author_name'   => $review['author_name'],
                'author_email'  => $review['author_email'] ?? '',
                'author_ip'     => '',
                'review_date'   => $this->convert_amazon_date( $review['date'] ),
                'review_text'   => $review['review_text'],
                'review_stars'  => $review['rating'],
                'verified'      => $review['verified_purchase'] ?? false,
            );
        }
        
        return $normalized;
    }
    
    public function validate(): array {
        // Validate API credentials, check connectivity
        if ( ! $this->test_api_connection() ) {
            return array(
                'errors' => array( 'Invalid API credentials' ),
            );
        }
        
        return array( 'errors' => array() );
    }
}
```

### Step 2: Add Settings (if needed)

```php
// In class-settings.php
const OPT_AMAZON_API_KEY = 'pri_amazon_api_key';

register_setting(
    'product_reviews_importer',
    OPT_AMAZON_API_KEY,
    array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    )
);
```

### Step 3: Add UI Tab (optional)

```php
// admin-templates/tab-amazon-settings.php
<h2><?php esc_html_e( 'Amazon Reviews Settings', 'product-reviews-importer' ); ?></h2>
<!-- API key field, product mapping, etc. -->
```

### Step 4: Add Cron Handler (if automated)

```php
// In class-cron-handler.php
public function sync_amazon_reviews(): void {
    $api_key    = get_option( OPT_AMAZON_API_KEY );
    $product_id = get_option( OPT_AMAZON_PRODUCT_ID );
    
    $source   = new Amazon_Importer( $api_key, $product_id );
    $importer = new Review_Importer();
    
    $offset = 0;
    $total  = $source->get_total_count();
    
    while ( $offset < $total ) {
        $reviews = $source->get_batch( $offset, BATCH_SIZE );
        $result  = $importer->import_reviews( $reviews );
        
        // Log results
        error_log( sprintf( 'Amazon sync: %d imported, %d errors', $result['success'], count( $result['errors'] ) ) );
        
        $offset += BATCH_SIZE;
    }
    
    update_option( 'pri_amazon_last_sync', current_time( 'mysql' ) );
}

// Schedule it
add_action( 'pri_sync_amazon_reviews', array( $this, 'sync_amazon_reviews' ) );
```

### Step 5: No Changes Needed to Core

**Review_Importer doesn't change** - it already handles the normalized format.

---

## Key Architectural Decisions

### 1. Why Normalized Format?

**Benefit:** Core import logic is 100% source-agnostic.

- Adding Amazon importer doesn't require touching Review_Importer
- Can test core logic with hardcoded arrays (no CSV or API needed)
- Source adapters handle all the messy format conversion

### 2. Why Interface for Sources?

**Benefit:** Enforces consistency, enables polymorphism.

- All sources implement `get_batch()`, `get_total_count()`, `validate()`
- Orchestrators can work with any source without knowing details
- Easy to mock for testing

### 3. Why Separate Orchestrators?

**Benefit:** Different transports have different concerns.

- AJAX: Progress tracking, user feedback, browser timeouts
- Cron: Logging, error emails, scheduling
- Core: Doesn't care about transport

### 4. Why Batch Processing?

**Benefit:** Works for all sources, prevents resource exhaustion.

- CSV: Large files don't hit memory limits
- API: Pagination is natural pattern
- Both: Prevents PHP timeouts, enables progress tracking

---

## Testing Strategy

### Unit Tests

**Review_Importer:**
- Test with hardcoded review arrays (no CSV needed)
- Mock WooCommerce products
- Assert comments created correctly
- Assert duplicates handled correctly

**CSV_Importer:**
- Test with sample CSV files
- Assert normalization works
- Assert validation catches errors

### Integration Tests

**AJAX Flow:**
- Upload test CSV via AJAX
- Assert batches process correctly
- Assert progress tracking works

**End-to-End:**
- Import real CSV
- Assert products have reviews
- Assert ratings display correctly

---

## Common Pitfalls to Avoid

❌ **Don't:** Put CSV parsing logic in Review_Importer  
✅ **Do:** Keep CSV_Importer separate

❌ **Don't:** Make Review_Importer aware of AJAX or progress bars  
✅ **Do:** Keep orchestration separate

❌ **Don't:** Hard-code Amazon API logic in Review_Importer  
✅ **Do:** Create separate Amazon_Importer adapter

❌ **Don't:** Put UI concerns in source adapters  
✅ **Do:** Source adapters just return data

❌ **Don't:** Make sources handle duplicate detection  
✅ **Do:** That's Review_Importer's job

---

## Summary

This architecture provides:

✅ **Separation of concerns** - Import logic, sources, and orchestration are independent  
✅ **Extensibility** - Add sources without touching core  
✅ **Testability** - Each component can be tested in isolation  
✅ **Flexibility** - Works for manual AJAX imports and automated cron syncs  
✅ **Maintainability** - Clear boundaries, well-documented patterns  

When adding a new import source in 6 months, you only need to:
1. Create new source adapter implementing Review_Import_Source
2. Add source-specific settings (if needed)
3. Add UI (if manual) or cron handler (if automated)
4. **Don't touch Review_Importer** - it already handles normalized data

---

**Last Updated:** 16 January 2026
