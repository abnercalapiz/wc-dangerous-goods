<?php
/**
 * Uninstall script
 *
 * @package WC_Dangerous_Goods
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('wc_dangerous_goods_settings');
delete_option('wc_dangerous_goods_version');

// Delete product meta (optional - commented out by default to preserve data)
// Uncomment the following lines if you want to remove all dangerous goods meta on uninstall

/*
global $wpdb;

// Delete post meta
$wpdb->delete(
    $wpdb->postmeta,
    array('meta_key' => '_dangerous_goods'),
    array('%s')
);

// Clear any cached data
wp_cache_flush();
*/