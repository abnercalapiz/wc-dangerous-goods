<?php
/**
 * Cart functionality
 *
 * @package WC_Dangerous_Goods
 * @subpackage Frontend
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cart handler class
 */
class WC_Dangerous_Goods_Cart {
    
    /**
     * Cache for dangerous goods check
     *
     * @var bool|null
     */
    private $has_dangerous_goods = null;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Cart fees
        add_action('woocommerce_cart_calculate_fees', array($this, 'add_dangerous_goods_fee'));
        
        // Cart notices
        add_action('woocommerce_before_cart_totals', array($this, 'display_cart_notice'));
        add_action('woocommerce_review_order_before_payment', array($this, 'display_checkout_notice'));
        
        // Cart item data
        add_filter('woocommerce_get_item_data', array($this, 'add_cart_item_data'), 10, 2);
        
        // Clear cache on cart update
        add_action('woocommerce_cart_updated', array($this, 'clear_cache'));
        add_action('woocommerce_add_to_cart', array($this, 'clear_cache'));
        add_action('woocommerce_cart_item_removed', array($this, 'clear_cache'));
    }
    
    /**
     * Add dangerous goods fee to cart
     */
    public function add_dangerous_goods_fee() {
        // Only run on frontend
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        // Check if cart has dangerous goods
        if (!$this->cart_has_dangerous_goods()) {
            return;
        }
        
        // Get settings
        $settings = WC_Dangerous_Goods::get_settings();
        $fee_amount = floatval($settings['fee_amount']);
        $fee_label = $settings['fee_label'];
        
        // Add fee (WooCommerce ensures it's only added once)
        WC()->cart->add_fee($fee_label, $fee_amount);
    }
    
    /**
     * Display notice in cart
     */
    public function display_cart_notice() {
        if (!$this->cart_has_dangerous_goods()) {
            return;
        }
        
        // Get template
        wc_get_template(
            'notices/cart-warning.php',
            array(
                'fee_amount' => $this->get_fee_amount()
            ),
            '',
            WC_DANGEROUS_GOODS_PLUGIN_DIR . 'templates/'
        );
    }
    
    /**
     * Display notice in checkout
     */
    public function display_checkout_notice() {
        if (!$this->cart_has_dangerous_goods()) {
            return;
        }
        
        // Get template
        wc_get_template(
            'notices/checkout-warning.php',
            array(
                'fee_amount' => $this->get_fee_amount()
            ),
            '',
            WC_DANGEROUS_GOODS_PLUGIN_DIR . 'templates/'
        );
    }
    
    /**
     * Add dangerous goods info to cart item data
     *
     * @param array $item_data Cart item data
     * @param array $cart_item Cart item
     * @return array
     */
    public function add_cart_item_data($item_data, $cart_item) {
        $product_id = $cart_item['variation_id'] ?: $cart_item['product_id'];
        
        if (WC_Dangerous_Goods::is_dangerous_good($product_id)) {
            $item_data[] = array(
                'key' => __('Warning', 'wc-dangerous-goods'),
                'value' => __('Contains Dangerous Goods', 'wc-dangerous-goods'),
                'display' => '<span class="wc-dangerous-goods-cart-item-warning">' . 
                           esc_html__('Contains Dangerous Goods', 'wc-dangerous-goods') . 
                           '</span>'
            );
        }
        
        return $item_data;
    }
    
    /**
     * Check if cart has dangerous goods
     *
     * @return bool
     */
    private function cart_has_dangerous_goods() {
        // Return cached value if available
        if (null !== $this->has_dangerous_goods) {
            return $this->has_dangerous_goods;
        }
        
        // Check cart
        if (!WC()->cart || WC()->cart->is_empty()) {
            $this->has_dangerous_goods = false;
            return false;
        }
        
        // Check each cart item
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['variation_id'] ?: $cart_item['product_id'];
            
            if (WC_Dangerous_Goods::is_dangerous_good($product_id)) {
                $this->has_dangerous_goods = true;
                return true;
            }
        }
        
        $this->has_dangerous_goods = false;
        return false;
    }
    
    /**
     * Get fee amount
     *
     * @return float
     */
    private function get_fee_amount() {
        $settings = WC_Dangerous_Goods::get_settings();
        return floatval($settings['fee_amount']);
    }
    
    /**
     * Clear cache
     */
    public function clear_cache() {
        $this->has_dangerous_goods = null;
    }
}