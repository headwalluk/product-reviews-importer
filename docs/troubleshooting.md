# Troubleshooting

## Import errors

Every skipped row is listed with its row number when the import finishes.

**Product not found**
: The SKU doesn't exactly match any product or variation. SKUs are case-sensitive; check for
  stray spaces.

**Invalid email**
: The `Author Email` value isn't a valid address. Leave the column empty rather than filling it
  with a placeholder.

**Invalid star rating**
: `Review Stars` must be a whole number from 1 to 5.

**Review text too short**
: The text is shorter than the *Minimum Review Length* setting.

**Missing required columns**
: The header row lacks one of `SKU`, `Author Name`, `Review Text` or `Review Stars`. Column names
  must match exactly.

## Upload errors

**File too large**
: The limit is 10 MB. Split the file and import each part.

**Upload session expired**
: More than an hour passed between upload and import. Upload the file again.

## Reviews were duplicated

Duplicate detection needs an email. Rows without `Author Email` create a new review every time
they are imported. See [importing → duplicates](importing.md#duplicates).

## The Export tab says there is nothing to export

Only approved reviews are exported. Check **Products → Reviews** for reviews still awaiting
moderation, and the *Auto-Approve Reviews* setting for future imports.
