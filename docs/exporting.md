# Exporting reviews

The **Export** tab (**WooCommerce → Import Reviews → Export**) downloads every approved product
review in the CSV format Walmart uses for review syndication. The file is generated on request
and streamed straight to the browser; nothing is stored on the server.

## Columns

| Column | Content |
|--------|---------|
| `Walmart Item ID` | Left blank — fill in with your Walmart Item IDs after export |
| `SKU` | The product's WooCommerce SKU |
| `Review Title` | Left blank — WooCommerce reviews have no title |
| `Review Body` | The review text, with HTML removed |
| `Review Rating` | Star rating, 1 to 5 |
| `Review Created Date` | `MM/DD/YYYY` |
| `Review User Name` | The reviewer's name as it appears on your site |
| `URL link` | The product's permalink |
| `Incentivized Review` | Always `No` — change it by hand where it applies |

The column headers are Walmart's and are never translated, whatever the site language.

## Which reviews are included

Only approved reviews are exported. A review whose product has since been deleted is left out.

The file starts with a UTF-8 byte-order mark so Excel opens accented characters correctly, and
is named `walmart-review-syndication-YYYY-MM-DD.csv`.
