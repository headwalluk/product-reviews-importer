# Product Reviews Importer - Project Tracker

**Last Updated:** 16 January 2026  
**Current Version:** 0.1.0  
**Status:** Initial Development

---

## Current Sprint: Foundation & Infrastructure

### ✅ Completed

**Phase 0: Project Setup**
- [x] Requirements document created
- [x] File structure established
- [x] Main plugin file created with WooCommerce dependency
- [x] Constants file with all magic values
- [x] Private functions file (namespaced helpers)
- [x] Plugin class with lazy loading
- [x] Settings class with WordPress Settings API
- [x] Admin Hooks class for admin functionality
- [x] HPOS compatibility declared
- [x] Translation ready (text domain configured)
- [x] README.md and readme.txt created
- [x] CHANGELOG.md initialized
- [x] PHPCS configuration (phpcs.xml)
- [x] Code standards compliance verified
- [x] Plugin successfully activated

**Infrastructure:**
- Namespace: `Product_Reviews_Importer`
- Function prefix: `pri_` (root namespace functions)
- Text domain: `product-reviews-importer`
- Admin page: WooCommerce > Reviews Importer

**Settings Configured:**
- Create user accounts for new reviewers (boolean, default: false)
- Minimum review length (integer, default: 10)
- Default IP address (string, defaults to server IP)

---

## Next Up: Admin Interface & CSV Import

### 🎯 Immediate Tasks

**Admin Templates:**
- [ ] Create `admin-templates/main-page.php` (tabbed interface)
- [ ] Create import tab template
- [ ] Create settings tab template
- [ ] Create help tab template

**Assets:**
- [ ] Create `assets/admin/admin.css`
- [ ] Create `assets/admin/admin.js` (tab navigation)

**CSV Importer:**
- [ ] Create `includes/class-csv-importer.php`
- [ ] CSV parsing and validation
- [ ] Field mapping interface
- [ ] Preview functionality
- [ ] Batch processing implementation
- [ ] Error handling and logging

**Review Import Logic:**
- [ ] Create `includes/class-review-importer.php`
- [ ] Product matching by SKU (handle variations)
- [ ] Duplicate detection (product_id + author_email)
- [ ] User account creation logic
- [ ] Review sanitization and creation
- [ ] Rating metadata handling

---

## Future Phases

### Phase 2: Testing & Refinement
- [ ] Test with small CSV files (< 10 rows)
- [ ] Test with large CSV files (> 1000 rows)
- [ ] Test error scenarios (missing products, invalid data)
- [ ] Test duplicate handling
- [ ] Test user account creation
- [ ] Performance testing

### Phase 3: Additional Features
- [ ] Import history tracking UI
- [ ] Export reviews to CSV
- [ ] Google Reviews integration
- [ ] Additional import sources

---

## Code Standards Checklist

✅ All files pass PHPCS  
✅ WordPress Coding Standards followed  
✅ PHP 8.0+ type hints used  
✅ No `declare(strict_types=1)`  
✅ All magic values in constants.php  
✅ Proper doc comments on all functions/classes  
✅ Security: nonces, capability checks, sanitization  
✅ HPOS compatible  

---

## File Structure

```
product-reviews-importer/
├── product-reviews-importer.php   ✅ Main plugin file
├── constants.php                   ✅ Plugin constants
├── functions-private.php           ✅ Namespaced helper functions
├── phpcs.xml                       ✅ Code standards config
├── readme.txt                      ✅ WordPress.org format
├── README.md                       ✅ GitHub format
├── CHANGELOG.md                    ✅ Version history
├── includes/
│   ├── class-plugin.php            ✅ Main plugin class
│   ├── class-settings.php          ✅ Settings management
│   ├── class-admin-hooks.php       ✅ Admin functionality
│   ├── class-csv-importer.php      ⏳ Next
│   └── class-review-importer.php   ⏳ Next
├── admin-templates/
│   └── main-page.php               ⏳ Next
├── assets/
│   └── admin/
│       ├── admin.css               ⏳ Next
│       └── admin.js                ⏳ Next
├── languages/                      📁 Empty (translation files)
└── dev-notes/
    ├── 00-project-tracker.md       ✅ This file
    ├── 01-requirements.md          ✅ Requirements document
    ├── patterns/                   ✅ Pattern references
    └── workflows/                  ✅ Workflow guides
```

---

## Notes

- Plugin is activatable and functional (basic infrastructure)
- No fatal errors or warnings
- Settings registration working
- Admin menu item appears under WooCommerce
- All code passes WordPress Coding Standards
- Ready to build admin interface and import logic

---

## Questions / Decisions Needed

None at this time - all initial decisions documented in requirements.md

**Version:** VERSION IN HERE
**Last Updated:** DATE IN HERE

---

## Overview

PROJECT SUMMARY

---

## Active TODO Items

---

## Milestones

---

## Technical Debt

---

## Notes for Development

