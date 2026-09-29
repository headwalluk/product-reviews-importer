# Configuration

Settings are on the **Settings** tab of **WooCommerce → Import Reviews**. They apply to future
imports only; reviews already imported are not changed.

| Setting | Default | Effect |
|---------|---------|--------|
| **Create User Accounts** | Off | Create a *Customer* account for each reviewer email with no existing user. Off: new reviewers become guest comments. See [importing → user accounts](importing.md#user-accounts) |
| **Minimum Review Length** | 10 characters | Rows with shorter review text are rejected |
| **Default IP Address** | Blank | IP recorded when a row has no valid `Author IP`. Blank: the server's public IP, looked up once from `icanhazip.com` and cached for seven days, or `127.0.0.1` if the lookup fails |
| **Auto-Approve Reviews** | On | Imported reviews are published straight away. Off: they wait in the moderation queue |
| **Mark as Verified Purchase** | Off | Flag imported reviews as verified purchases. Only turn this on when the reviews genuinely came from purchases, such as a migration from another WooCommerce store |
| **Delete Unfinished Uploads After** | 2 days | Age at which an uploaded CSV left behind by an unfinished import is deleted. 1 to 365. See [importing → uploaded files](importing.md#uploaded-files) |

## Updates

The plugin checks its GitHub repository for new releases and offers them as ordinary WordPress
plugin updates. Developers can turn this off; see
[hooks and filters](developers/hooks-and-filters.md#product_reviews_importer_updater_enabled).
