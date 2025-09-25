<?php
/**
 * Helper functions
 *
 * @package WC_Dangerous_Goods
 * @subpackage Helpers
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper class
 */
class WC_Dangerous_Goods_Helper {
    
    /**
     * Format price for display
     *
     * @param float $price Price to format
     * @return string Formatted price
     */
    public static function format_price($price) {
        return wc_price($price);
    }
    
    /**
     * Get all products marked as dangerous goods
     *
     * @param array $args Query arguments
     * @return array Product IDs
     */
    public static function get_dangerous_products($args = array()) {
        $defaults = array(
            'post_type' => array('product', 'product_variation'),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_dangerous_goods',
                    'value' => 'yes',
                    'compare' => '='
                )
            )
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $query = new WP_Query($args);
        
        return $query->posts;
    }
    
    /**
     * Check if dangerous goods fee is applied to order
     *
     * @param WC_Order $order Order object
     * @return bool
     */
    public static function order_has_dangerous_goods_fee($order) {
        if (!$order) {
            return false;
        }
        
        $fees = $order->get_fees();
        $settings = WC_Dangerous_Goods::get_settings();
        $fee_label = $settings['fee_label'];
        
        foreach ($fees as $fee) {
            if ($fee->get_name() === $fee_label) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get dangerous goods from order
     *
     * @param WC_Order $order Order object
     * @return array Product names
     */
    public static function get_order_dangerous_goods($order) {
        if (!$order) {
            return array();
        }
        
        $dangerous_products = array();
        
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if ($product && WC_Dangerous_Goods::is_dangerous_good($product)) {
                $dangerous_products[] = $product->get_name();
            }
        }
        
        return array_unique($dangerous_products);
    }
    
    /**
     * Log plugin events
     *
     * @param string $message Log message
     * @param string $level Log level (info, warning, error)
     */
    public static function log($message, $level = 'info') {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $logger = wc_get_logger();
        $context = array('source' => 'wc-dangerous-goods');
        
        switch ($level) {
            case 'error':
                $logger->error($message, $context);
                break;
            case 'warning':
                $logger->warning($message, $context);
                break;
            default:
                $logger->info($message, $context);
        }
    }
}