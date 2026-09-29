# Importing reviews

Go to **WooCommerce → Import Reviews**, choose a CSV file on the **Import** tab, and click
**Upload and Validate**. Once the file passes validation, click **Start Import**. You need the
`manage_woocommerce` capability.

## CSV format

The first row must be a header row. Column names must match exactly. Quote every value; review
text may span several lines inside its quotes.

| Column | Required | Notes |
|--------|----------|-------|
| `SKU` | Yes | Matched against product and variation SKUs |
| `Author Name` | Yes | |
| `Author Email` | No, recommended | Enables duplicate detection and linking to a user account |
| `Review Text` | Yes | Must meet the *Minimum Review Length* setting. `<p>` and `<br>` are kept; other HTML is stripped |
| `Review Stars` | Yes | Whole number from 1 to 5 |
| `Author IP` | No | Falls back to the *Default IP Address* setting |
| `Review Date` | No | `Y-m-d H:i:s T`, e.g. `2026-01-15 14:30:00 GMT`. Defaults to the time of import |

```csv
"SKU","Author Name","Author Email","Review Text","Review Stars","Author IP","Review Date"
"ABC-123","John Doe","john.doe@example.com","Great product, highly recommend!","5","123.123.123.123","2026-01-15 14:30:00 GMT"
```

Files must be `.csv` and no larger than 10 MB. A UTF-8 byte-order mark is detected and skipped.

## How rows are processed

The file is imported in batches of 50 rows, each in its own request, so a large file doesn't hit
a PHP timeout. The progress bar advances after each batch. Invalid rows are skipped and listed,
with their row numbers, when the import finishes.

### Product matching

Each row's SKU is looked up among products and variations. A review for a variation SKU is
attached to the parent product. A row whose SKU matches nothing is skipped and reported.

### Duplicates

When a row has an author email, a review is identified by **product + author email**. If one
already exists, only its text and star rating are updated; the original author, date and IP
are kept.

Rows without an email are never matched, so importing the same file twice creates every
email-less review twice.

### User accounts

A row whose email belongs to an existing user is always linked to that user, and the review uses
the user's display name. For an email with no account:

- With **Create User Accounts** on, a user is created in the *Customer* role
- With it off, the review is added as a guest comment (`user_id` 0) under the CSV's *Author Name*

## Uploaded files

The uploaded CSV is stored in `wp-content/uploads/pri-temp/` while the import runs, and deleted
when the final batch completes. The upload session expires after one hour.
