<?php
/**
 * Compatibility fixes for WooCommerce
 *
 * @package WC_Dangerous_Goods
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compatibility class
 */
class WC_Dangerous_Goods_Compatibility {
    
    /**
     * Initialize compatibility fixes
     */
    public static function init() {
        // Fix for WooCommerce Helper parsing issues
        add_filter('woocommerce_helper_get_local_woo_plugins', array(__CLASS__, 'fix_helper_parsing'), 10, 1);
        
        // Alternative approach - filter the plugin data
        add_filter('all_plugins', array(__CLASS__, 'fix_plugin_data'), 999);
    }
    
    /**
     * Fix WooCommerce helper parsing issues
     *
     * @param array $plugins List of plugins
     * @return array
     */
    public static function fix_helper_parsing($plugins) {
        // Remove our plugin from WooCommerce helper parsing if it causes issues
        if (isset($plugins[plugin_basename(WC_DANGEROUS_GOODS_PLUGIN_DIR . 'wc-dangerous-goods.php')])) {
            // Ensure our plugin data is properly formatted
            $our_plugin = &$plugins[plugin_basename(WC_DANGEROUS_GOODS_PLUGIN_DIR . 'wc-dangerous-goods.php')];
            
            // Ensure all expected fields exist
            if (is_array($our_plugin)) {
                $defaults = array(
                    'Name' => 'WooCommerce Dangerous Goods Fee',
                    'Version' => '1.0.0',
                    'WC tested up to' => '10.2',
                    'Woo' => '',
                );
                
                $our_plugin = wp_parse_args($our_plugin, $defaults);
            }
        }
        
        return $plugins;
    }
    
    /**
     * Fix plugin data to prevent parsing errors
     *
     * @param array $plugins All plugins
     * @return array
     */
    public static function fix_plugin_data($plugins) {
        $plugin_file = 'wc-dangerous-goods/wc-dangerous-goods.php';
        
        if (isset($plugins[$plugin_file])) {
            // Ensure WC compatibility headers don't cause parsing issues
            if (empty($plugins[$plugin_file]['WC tested up to'])) {
                $plugins[$plugin_file]['WC tested up to'] = '10.2';
            }
            
            // Remove any problematic headers that might cause parsing issues
            unset($plugins[$plugin_file]['Woo']);
        }
        
        return $plugins;
    }
}

// Initialize compatibility fixes
WC_Dangerous_Goods_Compatibility::init();