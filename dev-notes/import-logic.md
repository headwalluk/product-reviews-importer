# Product Reviews Importer - Import Logic

**Last Updated:** 16 January 2026  
**Purpose:** Detailed specification for review import processing logic

---

## Overview

This document covers the core logic for importing reviews into WooCommerce products as WordPress comments. This logic is source-agnostic and works for CSV, API, or any other import source.

---

## Normalized Review Data Format

All import sources must convert their data to this format before passing to the core importer:

```php
array(
    // REQUIRED FIELDS
    'product_sku'   => 'ABC123',           // Product SKU (used to find product)
    'author_name'   => 'John Doe',         // Reviewer name (trimmed)
    'author_email'  => 'john@example.com', // Reviewer email (will be sanitized)
    'review_text'   => 'Great product!',   // Review content (multi-line supported)
    'review_stars'  => 5,                  // Integer 1-5
    
    // OPTIONAL FIELDS
    'author_ip'     => '123.45.67.89',     // IP address (defaults to server IP if invalid/empty)
    'review_date'   => '2026-01-15 14:30:00 GMT', // Y-m-d H:i:s T format (defaults to current time if invalid/empty)
)
```

---

## Required Fields

### 1. product_sku (string)
- Must match an existing WooCommerce product
- If SKU belongs to a variation, the parent product ID is used (reviews go on parent)
- If product not found, import fails with error

### 2. author_name (string)
- Cannot be empty
- Whitespace trimmed
- No special validation required

### 3. author_email (string)
- Must be valid email format
- Sanitized with `sanitize_email()`
- Validated with `is_email()`
- Used with product_id to detect duplicates

### 4. review_text (string)
- Cannot be empty
- Minimum length validated (from settings, default 10 chars)
- Maximum length: 65,535 chars (WordPress comment field limit)
- Sanitized with `wp_kses()` - only `<br>` and `<p>` tags allowed
- Plain text line breaks converted to `<br>` tags

### 5. review_stars (integer)
- Must be integer 1-5 (inclusive)
- Validated with `validate_star_rating()` helper
- Invalid values cause import to fail

---

## Optional Fields

### 1. author_ip (string)
- If empty or invalid: Use default IP from settings (server IP)
- Validated with `filter_var( $ip, FILTER_VALIDATE_IP )`

### 2. review_date (string)
- Expected format: `Y-m-d H:i:s T` (e.g., "2026-01-15 14:30:00 GMT")
- If empty or invalid: Use current date/time
- Parsed with `DateTime` class, formatted as `Y-m-d H:i:s` for WordPress (no timezone in storage)

---

## Import Processing Flow

### Step 1: Validate Required Fields

```php
$required = array( 'product_sku', 'author_name', 'author_email', 'review_text', 'review_stars' );

foreach ( $required as $field ) {
    if ( empty( $review_data[ $field ] ) ) {
        return new WP_Error( 'missing_field', "Missing required field: {$field}" );
    }
}
```

### Step 2: Validate Star Rating

```php
$rating = validate_star_rating( $review_data['review_stars'] );

if ( false === $rating ) {
    return new WP_Error( 'invalid_rating', 'Star rating must be 1-5' );
}
```

### Step 3: Get Product ID

```php
$product_id = get_review_product_id( $review_data['product_sku'] );

if ( ! $product_id ) {
    return new WP_Error( 'product_not_found', 'Product not found: ' . $review_data['product_sku'] );
}
```

**Product Matching Logic:**

```php
function get_review_product_id( string $sku ) {
    $product_id = wc_get_product_id_by_sku( $sku );
    
    if ( ! $product_id ) {
        return false;
    }
    
    $product = wc_get_product( $product_id );
    
    if ( ! $product ) {
        return false;
    }
    
    // If variation, reviews go on parent product
    if ( $product->is_type( 'variation' ) ) {
        return $product->get_parent_id();
    }
    
    // For all other product types (simple, grouped, etc.), use product_id directly
    return $product_id;
}
```

**Key Points:**
- Only one level of parent checking needed (variation → parent)
- Grouped products: Reviews go on individual products, NOT the group
- External/affiliate products: Reviews go on the product itself

### Step 4: Sanitize Email

```php
$author_email = sanitize_email( $review_data['author_email'] );

if ( ! is_email( $author_email ) ) {
    return new WP_Error( 'invalid_email', 'Invalid email: ' . $review_data['author_email'] );
}
```

### Step 5: Sanitize Review Text

```php
$review_text = sanitize_review_text( $review_data['review_text'] );

$min_length = $this->settings->get_min_review_length();

if ( strlen( $review_text ) < $min_length ) {
    return new WP_Error( 
        'review_too_short', 
        sprintf( 'Review text too short (minimum %d characters)', $min_length ) 
    );
}
```

**Sanitization Function:**

```php
function sanitize_review_text( string $text ): string {
    // Convert plain text line breaks to <br> tags
    $text = nl2br( $text );
    
    // Allow only <br> and <p> tags (no attributes)
    return wp_kses( $text, ALLOWED_REVIEW_TAGS );
}
```

### Step 6: Process Optional Fields

```php
// Author IP
$author_ip = '';
if ( ! empty( $review_data['author_ip'] ) && filter_var( $review_data['author_ip'], FILTER_VALIDATE_IP ) ) {
    $author_ip = $review_data['author_ip'];
} else {
    $author_ip = $this->settings->get_default_ip_address();
}

// Review Date
$review_date = $this->parse_review_date( $review_data['review_date'] ?? '' );
```

**Date Parsing Function:**

```php
private function parse_review_date( string $date_string ): string {
    if ( empty( $date_string ) ) {
        $now = new \DateTime( 'now', wp_timezone() );
        return $now->format( 'Y-m-d H:i:s' );
    }
    
    try {
        $date = new \DateTime( $date_string );
        // WordPress stores dates without timezone - format as Y-m-d H:i:s only
        return $date->format( 'Y-m-d H:i:s' );
    } catch ( \Exception $e ) {
        // Invalid date format, use current time
        $now = new \DateTime( 'now', wp_timezone() );
        return $now->format( 'Y-m-d H:i:s' );
    }
}
```

**Important:** WordPress stores comment dates as `Y-m-d H:i:s` (no timezone). The database uses the site's configured timezone.

### Step 7: Get or Create User Account

```php
// Check for existing user by email
$user_id = get_user_id_by_email( $author_email );

// If no user found and setting enabled, create new user
if ( ! $user_id && $this->settings->get_create_user_accounts() ) {
    $user_id = $this->create_customer_user( $author_email, $review_data['author_name'] );
}

// If user_id is still 0, review will be a guest review
```

**User Creation Logic:**

```php
private function create_customer_user( string $email, string $name ): int {
    // Check if email already exists (double-check)
    if ( email_exists( $email ) ) {
        $user = get_user_by( 'email', $email );
        return $user ? $user->ID : 0;
    }
    
    // Create username from email (everything before @)
    $username = sanitize_user( current( explode( '@', $email ) ), true );
    
    // Ensure username is unique
    if ( username_exists( $username ) ) {
        $username = $username . '_' . wp_rand( 100, 999 );
    }
    
    // Create user with random password
    $user_id = wp_create_user( $username, wp_generate_password(), $email );
    
    if ( is_wp_error( $user_id ) ) {
        // User creation failed, log error and return 0 (guest)
        error_log( 'Failed to create user: ' . $user_id->get_error_message() );
        return 0;
    }
    
    // Set user role to Customer
    $user = new \WP_User( $user_id );
    $user->set_role( 'customer' );
    
    // Set display name
    wp_update_user( array(
        'ID'           => $user_id,
        'display_name' => $name,
    ) );
    
    return $user_id;
}
```

**Future Enhancement:** Add setting to choose user role(s) for newly created users (currently defaults to 'customer').

### Step 8: Check for Existing Review

```php
$existing = $this->find_existing_review( $product_id, $author_email );
```

**Find Existing Review Function:**

```php
private function find_existing_review( int $product_id, string $author_email ) {
    $comments = get_comments( array(
        'post_id'      => $product_id,
        'author_email' => $author_email,
        'type'         => 'review',
        'status'       => 'all',  // Include approved, pending, spam, trash
        'number'       => 1,
    ) );
    
    return ! empty( $comments ) ? $comments[0] : false;
}
```

**Important:** Search across ALL comment statuses to avoid creating duplicates of spam/trashed reviews.

### Step 9: Create or Update Review

**If Existing Review Found (Update):**

```php
if ( $existing ) {
    // Update only content and rating (preserve original date, author info)
    wp_update_comment( array(
        'comment_ID'      => $existing->comment_ID,
        'comment_content' => $review_text,
    ) );
    
    // Update rating metadata
    update_comment_meta( $existing->comment_ID, 'rating', $rating );
    
    return $existing->comment_ID;
}
```

**Key Points:**
- Only update `comment_content` and `rating` meta
- Preserve original `comment_date`, `comment_author`, `comment_author_email`, `comment_author_IP`
- Do NOT update `verified` meta (keep original verification status)

**If New Review (Create):**

```php
$comment_id = wp_insert_comment( array(
    'comment_post_ID'      => $product_id,
    'comment_author'       => trim( $review_data['author_name'] ),
    'comment_author_email' => $author_email,
    'comment_author_url'   => '',  // Never populate this for imported reviews
    'comment_content'      => $review_text,
    'comment_type'         => 'review',  // CRITICAL: Must be 'review' for WooCommerce
    'comment_parent'       => 0,
    'user_id'              => $user_id,  // 0 for guest reviews
    'comment_author_IP'    => $author_ip,
    'comment_agent'        => 'Product Reviews Importer',  // Identifies imported reviews
    'comment_date'         => $review_date,
    'comment_approved'     => $this->settings->get_auto_approve_reviews() ? 1 : 0,
) );

if ( ! $comment_id || is_wp_error( $comment_id ) ) {
    return new WP_Error( 'comment_insert_failed', 'Failed to create review' );
}

// Add rating metadata
update_comment_meta( $comment_id, 'rating', $rating );

// Set verified status from settings
$verified = $this->settings->get_reviews_are_verified() ? 1 : 0;
update_comment_meta( $comment_id, 'verified', $verified );

return $comment_id;
```

### Step 10: Automatic Rating Average Update

**No action required** - WooCommerce automatically recalculates the product's average rating when a review comment is created or updated.

---

## WordPress Comment Structure

```php
wp_insert_comment( array(
    'comment_post_ID'      => int,     // Product ID
    'comment_author'       => string,  // Reviewer name
    'comment_author_email' => string,  // Reviewer email (sanitized)
    'comment_author_url'   => string,  // Leave empty for imports
    'comment_content'      => string,  // Review text (sanitized)
    'comment_type'         => 'review', // MUST be 'review' for WooCommerce
    'comment_parent'       => 0,       // Always 0 (no threaded reviews)
    'user_id'              => int,     // WordPress user ID, or 0 for guest
    'comment_author_IP'    => string,  // IP address
    'comment_agent'        => string,  // User agent (we use plugin name)
    'comment_date'         => string,  // Y-m-d H:i:s format (no timezone)
    'comment_approved'     => int,     // 1 = approved, 0 = pending
) );
```

**Comment Meta:**

```php
update_comment_meta( $comment_id, 'rating', $rating );    // Integer 1-5
update_comment_meta( $comment_id, 'verified', $verified ); // Boolean (0 or 1)
```

---

## Required Settings

Add these to `constants.php` and `Settings` class:

### 1. Auto-Approve Reviews

```php
// In constants.php
const OPT_AUTO_APPROVE_REVIEWS = 'pri_auto_approve_reviews';
const DEF_AUTO_APPROVE_REVIEWS = true;  // Or false - your choice

// In class-settings.php
public function get_auto_approve_reviews(): bool {
    return (bool) filter_var(
        get_option( OPT_AUTO_APPROVE_REVIEWS, DEF_AUTO_APPROVE_REVIEWS ),
        FILTER_VALIDATE_BOOLEAN
    );
}
```

**Purpose:** Control whether imported reviews are automatically approved or set to pending moderation.

### 2. Reviews Are Verified

```php
// In constants.php
const OPT_REVIEWS_ARE_VERIFIED = 'pri_reviews_are_verified';
const DEF_REVIEWS_ARE_VERIFIED = false;  // Imports default to NOT verified

// In class-settings.php
public function get_reviews_are_verified(): bool {
    return (bool) filter_var(
        get_option( OPT_REVIEWS_ARE_VERIFIED, DEF_REVIEWS_ARE_VERIFIED ),
        FILTER_VALIDATE_BOOLEAN
    );
}
```

**Purpose:** Control whether imported reviews are marked as "verified purchase". Defaults to false since imports didn't come from your store's orders.

**Use Case:** If importing from another WooCommerce store, admin may have verified purchases externally.

### Existing Settings (Already Implemented)

- ✅ `OPT_CREATE_USER_ACCOUNTS` - Create WordPress users for new reviewers
- ✅ `OPT_MIN_REVIEW_LENGTH` - Minimum review text length
- ✅ `OPT_DEFAULT_IP_ADDRESS` - Default IP when author IP is empty/invalid

---

## Helper Functions (Already Implemented)

These exist in `functions-private.php`:

- ✅ `is_woocommerce_active(): bool`
- ✅ `get_server_ip(): string`
- ✅ `sanitize_review_text( string $text ): string`
- ✅ `validate_star_rating( $rating )`
- ✅ `get_product_id_by_sku( string $sku )` - **Needs refinement** (see below)
- ✅ `get_user_id_by_email( string $email ): int`

### Refinement Needed: get_product_id_by_sku()

Current implementation uses `get_parent_id()` check. Update to use `is_type( 'variation' )`:

```php
function get_product_id_by_sku( string $sku ) {
    $product_id = wc_get_product_id_by_sku( $sku );
    
    if ( ! $product_id ) {
        return false;
    }
    
    $product = wc_get_product( $product_id );
    
    if ( ! $product ) {
        return false;
    }
    
    // If variation, reviews go on parent product
    if ( $product->is_type( 'variation' ) ) {
        return $product->get_parent_id();
    }
    
    return $product_id;
}
```

---

## Error Handling

All validation/processing errors return `WP_Error` objects:

```php
// Examples:
new WP_Error( 'missing_field', "Missing required field: {$field}" );
new WP_Error( 'invalid_rating', 'Star rating must be 1-5' );
new WP_Error( 'product_not_found', "Product not found: {$sku}" );
new WP_Error( 'invalid_email', "Invalid email: {$email}" );
new WP_Error( 'review_too_short', "Review text too short (minimum {$min_length} characters)" );
new WP_Error( 'comment_insert_failed', 'Failed to create review' );
```

The importer checks for errors:

```php
$result = $this->import_review( $review_data );

if ( is_wp_error( $result ) ) {
    // Log error, add to error array, continue processing
    $errors[] = array(
        'row'     => $row_number,
        'error'   => $result->get_error_message(),
        'data'    => $review_data,
    );
    continue;
}
```

---

## Batch Results Structure

The `import_reviews()` method returns structured results:

```php
array(
    'success' => 10,  // Number of successfully imported reviews
    'updated' => 3,   // Number of existing reviews updated
    'errors'  => array(  // Array of error details
        array(
            'row'     => 5,
            'error'   => 'Product not found: XYZ789',
            'data'    => array( 'product_sku' => 'XYZ789', ... ),
        ),
        array(
            'row'     => 8,
            'error'   => 'Invalid email: not-an-email',
            'data'    => array( 'author_email' => 'not-an-email', ... ),
        ),
    ),
)
```

---

## Edge Cases & Gotchas

### 1. Duplicate Reviews Across Different Statuses

```php
// Search ALL statuses to avoid creating duplicates
'status' => 'all',  // Includes approved, pending, spam, trash
```

**Why:** If a review was marked as spam, we don't want to create a new one. Update the spam review instead.

### 2. Variation Products

```php
// Always use parent product ID for variations
if ( $product->is_type( 'variation' ) ) {
    return $product->get_parent_id();
}
```

**Why:** WooCommerce displays reviews on the parent product page, not individual variations.

### 3. Guest Reviews (user_id = 0)

```php
'user_id' => $user_id,  // Can be 0 for guest reviews
```

**Why:** WordPress allows comments with `user_id = 0`. These are guest reviews (no WordPress account).

### 4. Username Conflicts

```php
// Ensure username is unique
if ( username_exists( $username ) ) {
    $username = $username . '_' . wp_rand( 100, 999 );
}
```

**Why:** Multiple people might have same email prefix (e.g., john@domain1.com, john@domain2.com).

### 5. Review Text Sanitization

```php
// Convert line breaks BEFORE wp_kses
$text = nl2br( $text );
return wp_kses( $text, ALLOWED_REVIEW_TAGS );
```

**Why:** `nl2br()` adds `<br>` tags, which must be allowed by `wp_kses()`.

### 6. Date Format for WordPress

```php
// WordPress expects Y-m-d H:i:s (no timezone)
return $date->format( 'Y-m-d H:i:s' );
```

**Why:** WordPress comment dates don't include timezone in database. The site's timezone setting is used for display.

### 7. comment_type MUST Be 'review'

```php
'comment_type' => 'review',  // NOT empty string!
```

**Why:** WooCommerce filters by `comment_type=review` to show reviews separately from regular comments.

### 8. WooCommerce Rating Average Auto-Update

**No manual action required** - WooCommerce hooks into `wp_insert_comment` and `wp_update_comment` to automatically recalculate product rating averages.

---

## Future Enhancements

### 1. User Role Selection

**Current:** New users are assigned 'customer' role.

**Future:** Add setting to let admin choose role(s) for newly created users.

```php
// Future setting
const OPT_NEW_USER_ROLE = 'pri_new_user_role';
const DEF_NEW_USER_ROLE = 'customer';
```

**Use Case:** Some stores might want imported reviewers to have 'subscriber' role instead.

### 2. Import Source Tracking

**Future:** Add comment meta to track import source.

```php
update_comment_meta( $comment_id, 'import_source', 'csv' );
update_comment_meta( $comment_id, 'import_date', current_time( 'mysql' ) );
```

**Use Case:** Helps identify which reviews came from which import batch.

### 3. Review Images/Attachments

**Future:** Support importing review images.

**Challenge:** WooCommerce doesn't natively support review images. Would need plugin like "YITH WooCommerce Advanced Reviews" or custom implementation.

---

## Testing Scenarios

### Valid Data
- ✅ Simple product with all fields
- ✅ Variation product (should use parent ID)
- ✅ Guest review (user_id = 0)
- ✅ Existing user match by email
- ✅ New user creation (when enabled)
- ✅ Duplicate review (should update)
- ✅ Multi-line review text

### Invalid Data
- ❌ Missing required fields
- ❌ Invalid SKU (product not found)
- ❌ Invalid email format
- ❌ Invalid star rating (0, 6, "abc")
- ❌ Review text too short
- ❌ Invalid date format
- ❌ Invalid IP address format

### Edge Cases
- Empty optional fields (IP, date)
- Duplicate review in spam status
- Username already exists
- User creation fails
- Very long review text (< 65,535 chars)
- Special characters in review text
- HTML in review text (should be stripped except br/p)

---

## Summary

**Key Points:**

1. ✅ Reviews are WordPress comments with `comment_type='review'`
2. ✅ Duplicate key: `product_id` + `author_email`
3. ✅ Updates only change content and rating
4. ✅ Variation products → use parent product ID
5. ✅ Guest reviews: `user_id = 0`
6. ✅ New users default to 'customer' role
7. ✅ Imported reviews default to NOT verified
8. ✅ Auto-approve setting controls moderation
9. ✅ WooCommerce auto-updates product rating average

**Required Settings (New):**
- `OPT_AUTO_APPROVE_REVIEWS` - Auto-approve imported reviews
- `OPT_REVIEWS_ARE_VERIFIED` - Mark imported reviews as verified purchase

**Helper Function Refinements:**
- `get_product_id_by_sku()` - Use `is_type('variation')` check

---

**Last Updated:** 16 January 2026
