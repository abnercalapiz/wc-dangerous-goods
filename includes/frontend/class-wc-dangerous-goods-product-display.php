<?php
/**
 * Product display functionality
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
 * Product display class
 */
class WC_Dangerous_Goods_Product_Display {
    
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
        // Product page notice - DISABLED
        // add_action('woocommerce_single_product_summary', array($this, 'display_product_notice'), 25);
        
        // Shop loop notice
        add_action('woocommerce_after_shop_loop_item_title', array($this, 'display_shop_loop_notice'), 15);
    }
    
    /**
     * Display notice on product page
     */
    public function display_product_notice() {
        global $product;
        
        if (!$product || !WC_Dangerous_Goods::is_dangerous_good($product)) {
            return;
        }
        
        // Get template
        wc_get_template(
            'notices/product-warning.php',
            array(
                'product' => $product,
                'fee_amount' => $this->get_fee_amount()
            ),
            '',
            WC_DANGEROUS_GOODS_PLUGIN_DIR . 'templates/'
        );
    }
    
    /**
     * Display notice in shop loop
     */
    public function display_shop_loop_notice() {
        global $product;
        
        if (!$product || !WC_Dangerous_Goods::is_dangerous_good($product)) {
            return;
        }
        
        echo '<div class="wc-dangerous-goods-shop-notice">';
        echo '<span class="wc-dangerous-goods-icon">⚠️</span> ';
        echo esc_html__('Dangerous Goods', 'wc-dangerous-goods');
        echo '</div>';
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
}