<?php
/**
 * Error handler for WooCommerce compatibility warnings
 * 
 * This file can be included at the top of wp-config.php to suppress
 * the WooCommerce helper warnings in Local environment
 */

// Custom error handler for specific WooCommerce warnings
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Check if it's the specific WooCommerce helper warning
    if ($errno === E_WARNING && 
        strpos($errfile, 'woocommerce/includes/admin/helper/class-wc-helper.php') !== false &&
        strpos($errstr, 'Undefined array key 1') !== false) {
        // Suppress this specific warning
        return true;
    }
    
    // Let PHP handle all other errors normally
    return false;
}, E_WARNING);