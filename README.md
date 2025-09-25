# WooCommerce Dangerous Goods Fee

A WooCommerce plugin that adds a "Dangerous Goods" checkbox to products and automatically applies a handling fee when dangerous goods items are added to the cart.

**Version:** 1.0.2  
**Requires:** WordPress 5.0+, WooCommerce 3.0+  
**License:** GPL v2 or later

## Description

This plugin allows you to mark products as containing dangerous goods and automatically charge a handling fee when customers purchase these items. It's perfect for stores that need to comply with shipping regulations for hazardous materials.

## Features

- **Product Marking**: Add a "Dangerous Goods" checkbox to both simple and variable products
- **Automatic Fee Calculation**: Automatically adds a handling fee when dangerous goods are in the cart
- **Customizable Settings**: Configure fee amount and label through the admin interface
- **Visual Indicators**: Clear warnings on product pages and cart/checkout pages
- **Flexible Integration**: Works with both simple and variable products
- **Professional Styling**: Eye-catching visual alerts with warning icons
- **REST API Support**: Full integration with WooCommerce REST API for orders and products

## Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher

## Installation

1. Upload the `wc-dangerous-goods` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to WooCommerce > Dangerous Goods to configure settings

## Configuration

### Setting Up the Fee

1. Go to **WooCommerce > Dangerous Goods** in your WordPress admin
2. Set your desired handling fee amount (default: $20)
3. Customize the fee label that customers will see (default: "Dangerous Goods Handling Fee")
4. Click "Save Changes"

### Marking Products as Dangerous Goods

#### For Simple Products:
1. Edit any WooCommerce product
2. Navigate to the **Product Data > Shipping** tab
3. Check the **"Dangerous Goods"** checkbox
4. Update the product

#### For Variable Products:
1. Edit any WooCommerce variable product
2. Go to the **Variations** tab
3. For each variation that contains dangerous goods, check the **"Dangerous Goods"** checkbox
4. Save variations

## How It Works

1. **Product Level**: When editing products, you can mark them as containing dangerous goods
2. **Cart Detection**: The plugin automatically detects when dangerous goods are added to the cart
3. **Fee Application**: A single flat fee is applied once, regardless of how many dangerous goods items are in the cart
4. **Customer Notification**: Clear warnings are displayed on:
   - Product pages (for items marked as dangerous goods)
   - Cart page (when dangerous goods are present)
   - Checkout page (with fee breakdown)

## Visual Indicators

The plugin provides clear visual feedback:

- **Product Pages**: Red warning box with danger icon
- **Cart/Checkout**: Yellow warning banner with highlighted fee
- **Fee Display**: Special styling with warning icon and border

## Customization

### Fee Amount and Label

You can customize both the fee amount and label through the settings page or using filters:

```php
// Customize fee amount
add_filter('wc_dangerous_goods_fee_amount', function($amount) {
    return 25; // Set to $25
});

// Customize fee label
add_filter('wc_dangerous_goods_fee_label', function($label) {
    return 'Hazardous Materials Surcharge';
});
```

### Styling

The plugin includes default styling, but you can override it with custom CSS:

```css
/* Override dangerous goods notice colors */
.dangerous-goods-notice {
    background: #your-color !important;
    border-color: #your-border-color !important;
}

/* Override fee styling */
.cart_totals .fee,
.woocommerce-checkout-review-order-table .fee {
    background-color: #your-bg-color !important;
    border-color: #your-border-color !important;
}
```

## Frequently Asked Questions

### Q: Does this plugin charge per item or a flat fee?
A: The plugin charges a single flat fee when any dangerous goods items are in the cart, not per item.

### Q: Can I have different fees for different products?
A: Currently, the plugin supports a single flat fee for all dangerous goods. For variable fees, custom development would be required.

### Q: Will the fee be applied multiple times for multiple dangerous goods?
A: No, the fee is applied only once per order, regardless of how many dangerous goods items are in the cart.

### Q: Can I translate the plugin?
A: Yes, the plugin is translation-ready. Use the text domain `wc-dangerous-goods` for translations.

### Q: Is the dangerous goods status saved with orders?
A: Yes! As of version 1.0.1, dangerous goods information is saved with each order line item and accessible via REST API.

## Troubleshooting

### Compatibility Warning
If you see a compatibility warning after installation, update the plugin headers in `wc-dangerous-goods.php`:
- Change `WC tested up to:` to match your WooCommerce version

### Fee Not Appearing
1. Clear your cart and re-add products
2. Check that products are properly marked as dangerous goods
3. Ensure WooCommerce is up to date
4. Try deactivating and reactivating the plugin

### Settings Not Saving
1. Check for JavaScript errors in the browser console
2. Ensure you have proper permissions (manage_woocommerce capability)
3. Check for conflicts with other plugins

## REST API Integration

### Order Details API

The plugin extends WooCommerce REST API to include dangerous goods information in order details:

#### GET `/wp-json/wc/v3/orders/{id}`

**Order-level fields:**
```json
{
  "id": 123,
  "has_dangerous_goods": true,
  "dangerous_goods_summary": {
    "has_dangerous_goods": true,
    "dangerous_goods_fee": {
      "name": "Dangerous Goods Fee",
      "amount": 20,
      "tax": 2,
      "total": 22
    },
    "dangerous_goods_items": [456, 789]
  }
}
```

**Line item fields:**
```json
{
  "line_items": [{
    "product_id": 456,
    "dangerous_goods": true,
    "meta_data": [{
      "key": "dangerous_goods",
      "value": "yes",
      "display_key": "Dangerous Goods",
      "display_value": "Yes"
    }]
  }]
}
```

### Product API

#### GET `/wp-json/wc/v3/products/{id}`

Products and variations include a `dangerous_goods` boolean field:
```json
{
  "id": 456,
  "name": "Lithium Battery",
  "dangerous_goods": true
}
```

### Using the REST API

**Example: Get orders with dangerous goods**
```bash
curl -X GET https://yoursite.com/wp-json/wc/v3/orders \
  -u consumer_key:consumer_secret
```

**Example: Update product dangerous goods status**
```bash
curl -X PUT https://yoursite.com/wp-json/wc/v3/products/456 \
  -u consumer_key:consumer_secret \
  -H "Content-Type: application/json" \
  -d '{"dangerous_goods": true}'
```

## Developer Information

### Hooks and Filters

The plugin provides several hooks for developers:

**Filters:**
- `wc_dangerous_goods_fee_amount` - Modify the fee amount
- `wc_dangerous_goods_fee_label` - Modify the fee label

**Actions:**
- Standard WooCommerce product meta hooks for extending functionality

### Database Storage

The plugin stores data as post meta:
- Meta key: `_dangerous_goods`
- Meta value: `yes` or `no`
- Applies to both products and variations

### Plugin Structure

```
wc-dangerous-goods/
├── wc-dangerous-goods.php    # Main plugin file
├── README.md                  # Documentation
└── languages/                 # Translation files (if added)
```

## Support

For support, please contact:
- Website: https://www.jezweb.com.au/
- Plugin Issues: Contact through the website

## Security Update (Version 1.0.0)

**IMPORTANT**: This version includes critical security fixes and improvements.

### Migration Instructions

1. **Backup your site** before updating
2. Deactivate the current plugin
3. Replace the old `wc-dangerous-goods.php` with `wc-dangerous-goods-new.php`
4. Rename `wc-dangerous-goods-new.php` to `wc-dangerous-goods.php`
5. Reactivate the plugin
6. Check your settings at WooCommerce > Dangerous Goods

### What's New in Version 1.0.0

**Security Improvements:**
- ✅ CSRF protection with nonces on all forms
- ✅ Capability checks for all admin actions
- ✅ XSS prevention with proper output escaping
- ✅ Secure file access controls

**Architecture Improvements:**
- ✅ Separated admin and frontend code
- ✅ Proper file organization
- ✅ External CSS files (no more inline styles)
- ✅ Template system for customization
- ✅ Improved WooCommerce compatibility

**Performance Improvements:**
- ✅ Cached dangerous goods checks
- ✅ Optimized database queries
- ✅ Conditional asset loading

## Changelog

### Version 1.0.2 (2025-09-25)
- **Code Quality**: Removed development files and improved code formatting
- **Documentation**: Added comprehensive REST API documentation
- **Standards**: Improved WordPress coding standards compliance
- **Maintenance**: Added @since tags to API functions

### Version 1.0.1 (2025-09-25)
- **REST API**: Full integration with WooCommerce REST API
- **Order Meta**: Dangerous goods information now saved with order line items
- **API Features**: Added `dangerous_goods` field to products and line items
- **Fee Label**: Changed default from "Dangerous Goods Handling Fee" to "Dangerous Goods Fee"
- **Documentation**: Added REST API guide and testing tools

### Version 1.0.0 (Updated)
- **Security**: Fixed critical CSRF vulnerability in settings
- **Security**: Added capability checks to all admin functions
- **Security**: Fixed XSS vulnerabilities with proper escaping
- **Architecture**: Complete code restructuring
- **Performance**: Added caching for cart checks
- **Compatibility**: Updated for WooCommerce 9.4+
- Basic dangerous goods marking functionality
- Automatic fee calculation
- Admin settings interface
- Visual warnings and indicators


## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by [Jezweb](https://www.jezweb.com.au/)

---

**Note**: Always test the plugin in a staging environment before deploying to production. Ensure compliance with your local regulations regarding dangerous goods shipping and handling.