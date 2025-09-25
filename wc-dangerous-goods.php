<?php
/**
 * Plugin Name: WooCommerce Dangerous Goods Fee
 * Plugin URI: https://www.jezweb.com.au/
 * Description: Adds a "Dangerous Goods" checkbox to WooCommerce products and charges a flat $20 handling fee when dangerous goods are in the cart.
 * Version: 1.0.1
 * Author: Jezweb
 * Author URI: https://www.jezweb.com.au/
 * Text Domain: wc-dangerous-goods
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.7
 * WC requires at least: 5.0
 * WC tested up to: 10.2
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_DANGEROUS_GOODS_VERSION', '1.0.1');
define('WC_DANGEROUS_GOODS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_DANGEROUS_GOODS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_DANGEROUS_GOODS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Check if WooCommerce is active
 */
function wc_dangerous_goods_check_woocommerce() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        // Only show notice if we're in admin
        if (is_admin()) {
            add_action('admin_notices', 'wc_dangerous_goods_woocommerce_missing_notice');
        }
        return false;
    }
    return true;
}

/**
 * Display admin notice if WooCommerce is not active
 */
function wc_dangerous_goods_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php esc_html_e('WooCommerce Dangerous Goods Fee', 'wc-dangerous-goods'); ?></strong> 
            <?php esc_html_e('requires WooCommerce to be installed and activated.', 'wc-dangerous-goods'); ?>
        </p>
    </div>
    <?php
}

/**
 * Load plugin text domain
 */
function wc_dangerous_goods_load_textdomain() {
    load_plugin_textdomain(
        'wc-dangerous-goods',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('plugins_loaded', 'wc_dangerous_goods_load_textdomain');

/**
 * Initialize the plugin
 */
function wc_dangerous_goods_init() {
    // Check if WooCommerce is active
    if (!wc_dangerous_goods_check_woocommerce()) {
        return;
    }
    
    // Load required files
    require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/class-wc-dangerous-goods.php';
    
    // Initialize main plugin class
    $plugin = WC_Dangerous_Goods::get_instance();
    $plugin->run();
}
add_action('plugins_loaded', 'wc_dangerous_goods_init', 15);

/**
 * Plugin activation hook
 */
function wc_dangerous_goods_activate() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('WooCommerce Dangerous Goods Fee requires WooCommerce to be installed and activated.', 'wc-dangerous-goods'),
            esc_html__('Plugin Activation Error', 'wc-dangerous-goods'),
            array('back_link' => true)
        );
    }
    
    // Add default options
    add_option('wc_dangerous_goods_settings', array(
        'fee_amount' => 20,
        'fee_label' => __('Dangerous Goods Fee', 'wc-dangerous-goods')
    ));
    
    // Create database version
    add_option('wc_dangerous_goods_version', WC_DANGEROUS_GOODS_VERSION);
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wc_dangerous_goods_activate');

/**
 * Plugin deactivation hook
 */
function wc_dangerous_goods_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wc_dangerous_goods_deactivate');

/**
 * Declare HPOS (High Performance Order Storage) compatibility
 */
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        try {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
        } catch (Exception $e) {
            // Silently fail if there's an issue with compatibility declaration
            error_log('WC Dangerous Goods: Failed to declare compatibility - ' . $e->getMessage());
        }
    }
});