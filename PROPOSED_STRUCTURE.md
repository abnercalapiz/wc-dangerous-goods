# Proposed Plugin Restructure - WooCommerce Dangerous Goods

## Recommended Directory Structure

```
wc-dangerous-goods/
├── wc-dangerous-goods.php          # Main bootstrap file (minimal code)
├── uninstall.php                   # Cleanup on uninstall
├── README.md                       # User documentation
├── CHANGELOG.md                    # Version history
├── LICENSE                         # GPL v2 license
│
├── includes/                       # Core plugin files
│   ├── class-wc-dangerous-goods-loader.php      # Hooks and filters loader
│   ├── class-wc-dangerous-goods.php             # Main plugin class
│   ├── class-wc-dangerous-goods-activator.php   # Activation logic
│   ├── class-wc-dangerous-goods-deactivator.php # Deactivation logic
│   │
│   ├── admin/                      # Admin-specific functionality
│   │   ├── class-wc-dangerous-goods-admin.php
│   │   ├── class-wc-dangerous-goods-settings.php
│   │   └── class-wc-dangerous-goods-product-meta.php
│   │
│   ├── frontend/                   # Frontend functionality
│   │   ├── class-wc-dangerous-goods-cart.php
│   │   ├── class-wc-dangerous-goods-product-display.php
│   │   └── class-wc-dangerous-goods-checkout.php
│   │
│   └── helpers/                    # Utility functions
│       ├── class-wc-dangerous-goods-helper.php
│       └── class-wc-dangerous-goods-validator.php
│
├── assets/                         # Static assets
│   ├── css/
│   │   ├── admin.css
│   │   ├── admin.min.css
│   │   ├── frontend.css
│   │   └── frontend.min.css
│   ├── js/
│   │   ├── admin.js
│   │   ├── admin.min.js
│   │   └── frontend.js
│   └── images/
│       └── warning-icon.svg
│
├── languages/                      # Internationalization
│   ├── wc-dangerous-goods.pot
│   └── README.md
│
├── templates/                      # Overridable templates
│   ├── notices/
│   │   ├── product-warning.php
│   │   └── cart-warning.php
│   └── admin/
│       └── settings-page.php
│
└── tests/                         # Unit tests
    ├── bootstrap.php
    ├── test-sample.php
    └── fixtures/
```

## Key File Contents

### 1. Main Bootstrap File (wc-dangerous-goods.php)
```php
<?php
/**
 * Plugin Name: WooCommerce Dangerous Goods Fee
 * Version: 2.0.0
 * [Other headers...]
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('WC_DANGEROUS_GOODS_VERSION', '2.0.0');
define('WC_DANGEROUS_GOODS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_DANGEROUS_GOODS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/class-wc-dangerous-goods-loader.php';

// Initialize plugin
function wc_dangerous_goods_init() {
    $plugin = new WC_Dangerous_Goods();
    $plugin->run();
}
add_action('plugins_loaded', 'wc_dangerous_goods_init', 10);
```

### 2. Main Plugin Class with Security Improvements
```php
<?php
namespace WC_Dangerous_Goods;

class WC_Dangerous_Goods {
    
    private static $instance = null;
    private $loader;
    private $version;
    
    private function __construct() {
        $this->version = WC_DANGEROUS_GOODS_VERSION;
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function load_dependencies() {
        // Check WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Load required files
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/helpers/class-wc-dangerous-goods-helper.php';
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/admin/class-wc-dangerous-goods-admin.php';
        // ... other requires
        
        $this->loader = new WC_Dangerous_Goods_Loader();
    }
}
```

### 3. Secure Admin Settings Class
```php
<?php
namespace WC_Dangerous_Goods\Admin;

class WC_Dangerous_Goods_Settings {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('wc_dangerous_goods_settings', $this->get_defaults());
    }
    
    public function render_settings_page() {
        // Security check
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wc-dangerous-goods'));
        }
        
        include WC_DANGEROUS_GOODS_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }
    
    public function process_settings() {
        // Verify nonce
        if (!isset($_POST['wc_dangerous_goods_nonce']) || 
            !wp_verify_nonce($_POST['wc_dangerous_goods_nonce'], 'wc_dangerous_goods_save_settings')) {
            wp_die(__('Security check failed', 'wc-dangerous-goods'));
        }
        
        // Check capabilities
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Unauthorized access', 'wc-dangerous-goods'));
        }
        
        // Validate and sanitize inputs
        $fee_amount = isset($_POST['fee_amount']) ? floatval($_POST['fee_amount']) : 20;
        $fee_amount = max(0, $fee_amount); // Ensure non-negative
        
        $fee_label = isset($_POST['fee_label']) ? sanitize_text_field($_POST['fee_label']) : '';
        
        // Save settings
        update_option('wc_dangerous_goods_settings', array(
            'fee_amount' => $fee_amount,
            'fee_label' => $fee_label
        ));
        
        // Redirect with success message
        wp_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
        exit;
    }
}
```

### 4. Secure Product Meta Handler
```php
<?php
namespace WC_Dangerous_Goods\Admin;

class WC_Dangerous_Goods_Product_Meta {
    
    public function save_product_meta($post_id) {
        // Verify nonce
        if (!isset($_POST['woocommerce_meta_nonce']) || 
            !wp_verify_nonce($_POST['woocommerce_meta_nonce'], 'woocommerce_save_data')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_product', $post_id)) {
            return;
        }
        
        // Sanitize and save
        $dangerous_goods = isset($_POST['_dangerous_goods']) ? 'yes' : 'no';
        update_post_meta($post_id, '_dangerous_goods', $dangerous_goods);
    }
}
```

### 5. Frontend Display with Proper Escaping
```php
<?php
namespace WC_Dangerous_Goods\Frontend;

class WC_Dangerous_Goods_Product_Display {
    
    public function display_product_notice() {
        global $product;
        
        if (!$product || !$this->is_dangerous_good($product)) {
            return;
        }
        
        $fee_amount = $this->get_fee_amount();
        
        wc_get_template(
            'notices/product-warning.php',
            array('fee_amount' => $fee_amount),
            '',
            WC_DANGEROUS_GOODS_PLUGIN_DIR . 'templates/'
        );
    }
    
    private function get_fee_amount() {
        $settings = get_option('wc_dangerous_goods_settings', array());
        return isset($settings['fee_amount']) ? floatval($settings['fee_amount']) : 20;
    }
}
```

### 6. Template File Example (templates/notices/product-warning.php)
```php
<?php
/**
 * Product warning notice template
 *
 * @var float $fee_amount
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wc-dangerous-goods-notice">
    <strong><?php echo esc_html__('⚠️ Dangerous Goods:', 'wc-dangerous-goods'); ?></strong>
    <?php
    printf(
        esc_html__('This product contains dangerous goods. A %s handling fee will be added at checkout.', 'wc-dangerous-goods'),
        wc_price($fee_amount)
    );
    ?>
</div>
```

## Security Improvements Summary

1. **CSRF Protection**: All forms include nonces
2. **Capability Checks**: Every action verifies user permissions
3. **Data Validation**: All inputs are validated and sanitized
4. **Escaping**: All output is properly escaped
5. **SQL Injection Prevention**: Using WordPress APIs only
6. **File Access**: Direct file access blocked
7. **XSS Prevention**: No raw HTML output

## Performance Improvements

1. **Caching**: Meta queries cached per request
2. **Lazy Loading**: Settings loaded only when needed
3. **Optimized Queries**: Single query for cart checks
4. **Asset Loading**: Conditional asset loading

## Testing Strategy

1. **Unit Tests**: PHPUnit for core functionality
2. **Integration Tests**: WooCommerce specific tests
3. **Security Tests**: Automated security scanning
4. **Performance Tests**: Load testing for large catalogs

## Migration Path

1. **Phase 1**: Security fixes in current structure
2. **Phase 2**: Create new file structure
3. **Phase 3**: Move code to new structure
4. **Phase 4**: Add new features
5. **Phase 5**: Deprecate old code

This restructured approach provides:
- Better code organization
- Enhanced security
- Improved performance
- Easier maintenance
- Better testability
- WordPress coding standards compliance