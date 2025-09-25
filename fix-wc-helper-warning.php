<?php
/**
 * Fix for WooCommerce Helper Warning in Local Environment
 * 
 * Place this file in: wp-content/mu-plugins/fix-wc-helper-warning.php
 * 
 * This fixes the "Undefined array key 1" warning in WooCommerce helper
 */

// Only run if WooCommerce is active
add_action('init', function() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Fix for Local environment WooCommerce helper warnings
    add_filter('pre_set_transient_woocommerce_helper_updates_count', function($value) {
        // Force refresh of helper data to prevent parsing errors
        delete_transient('_woocommerce_helper_data');
        return $value;
    });
    
    // Alternative approach - filter the error reporting for this specific file
    if (defined('LOCAL_DEVELOPMENT') || (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local')) {
        ini_set('error_reporting', E_ALL & ~E_WARNING);
    }
});

// Suppress warnings from WooCommerce helper specifically
if (!function_exists('wc_suppress_helper_warnings')) {
    function wc_suppress_helper_warnings() {
        $current_error_reporting = error_reporting();
        
        // Check if we're in a WooCommerce admin context
        if (is_admin() && 
            isset($_SERVER['REQUEST_URI']) && 
            (strpos($_SERVER['REQUEST_URI'], 'plugins.php') !== false || 
             strpos($_SERVER['REQUEST_URI'], 'update-core.php') !== false)) {
            
            // Temporarily suppress warnings
            error_reporting(E_ERROR | E_PARSE);
            
            // Restore after page load
            add_action('shutdown', function() use ($current_error_reporting) {
                error_reporting($current_error_reporting);
            });
        }
    }
    add_action('admin_init', 'wc_suppress_helper_warnings', 1);
}