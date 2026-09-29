# Hooks and filters

This is the **public extension surface** of Product Reviews Importer. Anything not listed here is
internal and may change without notice between releases. Functions in the
`Product_Reviews_Importer` namespace are private; the filters below are the supported
integration points.

## Compatibility

- A filter's name and arguments don't change within a major version. New arguments are only ever
  added at the end.
- Any change that could break an integration is listed in `CHANGELOG.md`.

## Filters

### `product_reviews_importer_csv_field_definitions`

Filter the CSV columns the importer recognises. The result drives header validation, row mapping,
and the column lists and sample CSV on the Import and Help tabs.

**Parameters:**
- `array $fields` — Field definitions keyed by CSV column name. Each definition has:
  - `required` (`bool`) — Upload fails validation if the column is missing
  - `description` (`string`) — Shown on the Import and Help tabs
  - `map_to` (`string`) — Key the value is stored under in the normalised review data
  - `sample` (`string`) — Value used in the sample CSV

**Returns:** `array` — The modified field definitions.

```php
add_filter( 'product_reviews_importer_csv_field_definitions', function( $fields ) {
    // Make Author Email required.
    $fields['Author Email']['required'] = true;

    // Accept "Rating" as the star rating column instead of "Review Stars".
    $fields['Rating'] = $fields['Review Stars'];
    unset( $fields['Review Stars'] );

    return $fields;
} );
```

The importer acts only on the built-in `map_to` keys: `product_sku`, `author_name`,
`author_email`, `review_text`, `review_stars`, `author_ip` and `review_date`. A column mapped to
any other key is accepted and validated, but its value isn't stored.

The definitions are built once per request, so add the filter before the Import Reviews page
renders or an upload is processed — from a plugin, or your theme's `functions.php`.

---

### `product_reviews_importer_updater_enabled`

Filter whether the plugin checks GitHub for new releases.

**Parameters:**
- `bool $enabled` — Default `true`.

**Returns:** `bool` — `false` stops update checks and hides GitHub releases from the plugin
details dialog.

```php
// Pin the plugin to its installed version, e.g. on a staging site.
add_filter( 'product_reviews_importer_updater_enabled', '__return_false' );
```
