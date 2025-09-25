<?php
/**
 * Update Fee Label Script
 * 
 * This script updates the fee label from "Fees" to "Dangerous Goods Fee"
 * in the plugin settings and existing orders.
 * 
 * Usage: Run this script once after plugin update
 */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

echo "WooCommerce Dangerous Goods - Fee Label Update Script\n";
echo "====================================================\n\n";

// Step 1: Update plugin settings
echo "Step 1: Updating plugin settings...\n";
$settings = get_option('wc_dangerous_goods_settings', array());
$old_label = isset($settings['fee_label']) ? $settings['fee_label'] : 'Dangerous Goods Handling Fee';

if ($old_label === 'Fees' || empty($settings['fee_label'])) {
    $settings['fee_label'] = 'Dangerous Goods Fee';
    update_option('wc_dangerous_goods_settings', $settings);
    echo "✓ Updated plugin settings from '$old_label' to 'Dangerous Goods Fee'\n";
} else {
    echo "✓ Plugin settings already set to: '$old_label'\n";
}

// Step 2: Update existing order fees
echo "\nStep 2: Updating existing order fees...\n";

global $wpdb;

// Get all orders with fees
$order_ids = $wpdb->get_col("
    SELECT DISTINCT p.ID 
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->prefix}woocommerce_order_items oi ON p.ID = oi.order_id
    WHERE p.post_type IN ('shop_order', 'shop_order_refund')
    AND oi.order_item_type = 'fee'
    AND oi.order_item_name = 'Fees'
");

if (!empty($order_ids)) {
    $updated_count = 0;
    
    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            continue;
        }
        
        // Check if order has dangerous goods
        $has_dangerous_goods = false;
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if ($product && WC_Dangerous_Goods::is_dangerous_good($product)) {
                $has_dangerous_goods = true;
                break;
            }
        }
        
        if ($has_dangerous_goods) {
            // Update fee name
            $fees = $order->get_fees();
            foreach ($fees as $fee) {
                if ($fee->get_name() === 'Fees') {
                    $wpdb->update(
                        $wpdb->prefix . 'woocommerce_order_items',
                        array('order_item_name' => 'Dangerous Goods Fee'),
                        array(
                            'order_item_id' => $fee->get_id(),
                            'order_item_name' => 'Fees',
                            'order_item_type' => 'fee'
                        ),
                        array('%s'),
                        array('%d', '%s', '%s')
                    );
                    $updated_count++;
                    echo "✓ Updated Order #{$order_id}\n";
                }
            }
        }
    }
    
    echo "\n✓ Updated $updated_count orders with 'Fees' to 'Dangerous Goods Fee'\n";
} else {
    echo "✓ No orders found with 'Fees' label\n";
}

// Step 3: Clear caches
echo "\nStep 3: Clearing caches...\n";
wp_cache_flush();
if (function_exists('wp_cache_clear_cache')) {
    wp_cache_clear_cache();
}
echo "✓ Caches cleared\n";

echo "\n====================================================\n";
echo "Update complete!\n";
echo "\nNext steps:\n";
echo "1. Test the REST API to verify the changes\n";
echo "2. Check a few orders in WooCommerce admin to confirm fee labels\n";
echo "3. Remove this script after successful update\n";