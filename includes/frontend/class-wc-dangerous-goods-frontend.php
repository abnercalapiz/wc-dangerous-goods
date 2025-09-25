<?php
/**
 * Frontend functionality
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
 * Frontend class
 */
class WC_Dangerous_Goods_Frontend {
    
    /**
     * Cart handler
     *
     * @var WC_Dangerous_Goods_Cart
     */
    private $cart;
    
    /**
     * Product display handler
     *
     * @var WC_Dangerous_Goods_Product_Display
     */
    private $product_display;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
        $this->init_hooks();
    }
    
    /**
     * Initialize frontend components
     */
    private function init() {
        $this->cart = new WC_Dangerous_Goods_Cart();
        $this->product_display = new WC_Dangerous_Goods_Product_Display();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Frontend scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on WooCommerce pages
        if (!is_woocommerce() && !is_cart() && !is_checkout()) {
            return;
        }
        
        // Enqueue frontend styles
        wp_enqueue_style(
            'wc-dangerous-goods-frontend',
            WC_DANGEROUS_GOODS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WC_DANGEROUS_GOODS_VERSION
        );
        
        // Enqueue frontend scripts if needed
        if (is_product() || is_shop() || is_product_category()) {
            wp_enqueue_script(
                'wc-dangerous-goods-frontend',
                WC_DANGEROUS_GOODS_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                WC_DANGEROUS_GOODS_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script(
                'wc-dangerous-goods-frontend',
                'wc_dangerous_goods_frontend',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('wc_dangerous_goods_frontend'),
                    'strings' => array(
                        'dangerous_goods_notice' => __('This product contains dangerous goods', 'wc-dangerous-goods')
                    )
                )
            );
        }
    }
}