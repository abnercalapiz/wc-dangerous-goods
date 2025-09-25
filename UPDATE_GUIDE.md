# Update Guide - WooCommerce Dangerous Goods v1.0 (Security Update)

## Overview

This is a critical security and architecture update to version 1.0. While the functionality remains the same, the underlying code has been completely restructured to fix critical security vulnerabilities and improve maintainability.

## Critical Security Fixes

1. **CSRF Protection**: All forms now include WordPress nonces
2. **Authorization**: All admin functions verify user capabilities
3. **XSS Prevention**: All output is properly escaped
4. **File Security**: Direct file access is blocked

## Migration Steps

### Step 1: Backup Your Site
```bash
# Create a full backup of your WordPress site
# Including database and files
```

### Step 2: Test in Staging (Recommended)
1. Set up a staging copy of your site
2. Perform the update on staging first
3. Test all functionality
4. Verify settings are preserved

### Step 3: Update the Plugin

#### Option A: Simple Update (Recommended)
1. Download the new version
2. In WordPress admin, go to Plugins
3. Deactivate "WooCommerce Dangerous Goods Fee"
4. Delete the old plugin
5. Upload and activate the new version
6. Verify settings at WooCommerce > Dangerous Goods

#### Option B: Manual File Replacement
1. Via FTP/SFTP, navigate to `/wp-content/plugins/wc-dangerous-goods/`
2. Backup the current plugin folder
3. Delete all files in the plugin folder
4. Upload all new files maintaining the directory structure
5. In WordPress admin, deactivate and reactivate the plugin

### Step 4: Verify Settings
1. Go to WooCommerce > Dangerous Goods
2. Confirm your fee amount and label are correct
3. Test by adding a dangerous goods product to cart

### Step 5: Clear Caches
1. Clear any page caching plugins
2. Clear browser cache
3. Clear any CDN caches

## What's Preserved During Migration

✅ All product dangerous goods settings
✅ Fee amount and label settings
✅ Existing orders with dangerous goods fees
✅ Product variations settings

## New File Structure

```
wc-dangerous-goods/
├── wc-dangerous-goods.php       # Main file (updated)
├── includes/                    # New - Separated classes
│   ├── admin/                   # Admin functionality
│   ├── frontend/                # Frontend functionality
│   └── helpers/                 # Helper functions
├── assets/                      # New - External assets
│   ├── css/                     # Stylesheets
│   └── js/                      # JavaScript
├── templates/                   # New - Customizable templates
└── uninstall.php               # New - Clean uninstall
```

## Customization Changes

If you've customized the plugin:

### CSS Customizations
- Old: Inline styles in `<head>`
- New: External CSS files in `assets/css/`
- Action: Move customizations to theme or use plugin filters

### Template Customizations
- Old: No template system
- New: Templates in `templates/` directory
- Action: Copy templates to your theme to customize

### Hook Changes
- All hooks remain the same
- New hooks added for templates
- No breaking changes

## Troubleshooting

### Issue: Settings Lost After Update
- Solution: Check database for `wc_dangerous_goods_settings` option
- The structure remains the same

### Issue: Styles Not Loading
- Solution: Clear all caches
- Check browser console for 404 errors
- Verify file permissions (644 for files, 755 for folders)

### Issue: Products Not Showing as Dangerous
- Solution: Re-save affected products
- Meta key `_dangerous_goods` remains unchanged

### Issue: Compatibility Warning Persists
- Solution: Ensure you're running the new version
- Check that old files are completely removed

## Developer Notes

### API Changes
- No public API changes
- Internal structure completely refactored
- Better hooks for extensibility

### Database Changes
- No database schema changes
- Options structure unchanged
- Meta keys unchanged

### Filter/Action Changes
- Existing filters preserved
- New filters added for templates
- See inline documentation

## Support

If you encounter issues during migration:

1. Check error logs
2. Disable other plugins to test conflicts
3. Contact support with:
   - WordPress version
   - WooCommerce version
   - PHP version
   - Error messages

## Rollback Procedure

If you need to rollback:

1. Deactivate the new version
2. Delete the new plugin files
3. Restore the backed-up old version
4. Reactivate the plugin

Note: We strongly recommend NOT rolling back due to security vulnerabilities in v1.0.

---

**Important**: Older versions may contain security vulnerabilities. Update to this secure version as soon as possible.