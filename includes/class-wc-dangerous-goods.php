<?php
/**
 * Main plugin class
 *
 * @package WC_Dangerous_Goods
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main WC_Dangerous_Goods Class
 */
class WC_Dangerous_Goods {
    
    /**
     * Single instance of the class
     *
     * @var WC_Dangerous_Goods
     */
    private static $instance = null;
    
    /**
     * Plugin version
     *
     * @var string
     */
    public $version;
    
    /**
     * Admin class instance
     *
     * @var WC_Dangerous_Goods_Admin
     */
    public $admin;
    
    /**
     * Frontend class instance
     *
     * @var WC_Dangerous_Goods_Frontend
     */
    public $frontend;
    
    /**
     * REST API class instance
     *
     * @var WC_Dangerous_Goods_REST_API
     */
    public $rest_api;
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->version = WC_DANGEROUS_GOODS_VERSION;
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Get single instance of the class
     *
     * @return WC_Dangerous_Goods
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Load compatibility fixes first
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/class-wc-dangerous-goods-compatibility.php';
        
        // Load helper classes
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/helpers/class-wc-dangerous-goods-helper.php';
        
        // Load admin classes
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/admin/class-wc-dangerous-goods-admin.php';
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/admin/class-wc-dangerous-goods-settings.php';
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/admin/class-wc-dangerous-goods-product-meta.php';
        
        // Load frontend classes
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/frontend/class-wc-dangerous-goods-frontend.php';
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/frontend/class-wc-dangerous-goods-cart.php';
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/frontend/class-wc-dangerous-goods-product-display.php';
        
        // Load REST API class
        require_once WC_DANGEROUS_GOODS_PLUGIN_DIR . 'includes/class-wc-dangerous-goods-rest-api.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize classes based on context
        if (is_admin()) {
            $this->admin = new WC_Dangerous_Goods_Admin();
        }
        
        if (!is_admin() || defined('DOING_AJAX')) {
            $this->frontend = new WC_Dangerous_Goods_Frontend();
        }
        
        // Initialize REST API
        $this->rest_api = new WC_Dangerous_Goods_REST_API();
        
        // Add plugin action links
        add_filter('plugin_action_links_' . WC_DANGEROUS_GOODS_PLUGIN_BASENAME, array($this, 'add_action_links'));
    }
    
    /**
     * Run the plugin
     */
    public function run() {
        // Plugin is initialized through hooks
    }
    
    /**
     * Add action links to plugin page
     *
     * @param array $links Existing links
     * @return array Modified links
     */
    public function add_action_links($links) {
        $action_links = array(
            '<a href="' . admin_url('admin.php?page=wc-dangerous-goods-settings') . '">' . 
            esc_html__('Settings', 'wc-dangerous-goods') . '</a>'
        );
        
        return array_merge($action_links, $links);
    }
    
    /**
     * Get plugin settings
     *
     * @return array
     */
    public static function get_settings() {
        return get_option('wc_dangerous_goods_settings', array(
            'fee_amount' => 20,
            'fee_label' => __('Dangerous Goods Fee', 'wc-dangerous-goods')
        ));
    }
    
    /**
     * Check if product is dangerous good
     *
     * @param int|WC_Product $product Product ID or object
     * @return bool
     */
    public static function is_dangerous_good($product) {
        if (is_numeric($product)) {
            $product = wc_get_product($product);
        }
        
        if (!$product) {
            return false;
        }
        
        // Check variation
        if ($product->is_type('variation')) {
            $dangerous_goods = $product->get_meta('_dangerous_goods');
            return 'yes' === $dangerous_goods;
        }
        
        // Check simple product
        if ($product->is_type('simple')) {
            $dangerous_goods = $product->get_meta('_dangerous_goods');
            return 'yes' === $dangerous_goods;
        }
        
        // Check variable product (any variation is dangerous)
        if ($product->is_type('variable')) {
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                $variation_obj = wc_get_product($variation['variation_id']);
                if ($variation_obj && 'yes' === $variation_obj->get_meta('_dangerous_goods')) {
                    return true;
                }
            }
        }
        
        return false;
    }
}