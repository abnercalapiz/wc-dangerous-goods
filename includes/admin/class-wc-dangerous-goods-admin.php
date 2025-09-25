<?php
/**
 * Admin functionality
 *
 * @package WC_Dangerous_Goods
 * @subpackage Admin
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin class
 */
class WC_Dangerous_Goods_Admin {
    
    /**
     * Settings instance
     *
     * @var WC_Dangerous_Goods_Settings
     */
    private $settings;
    
    /**
     * Product meta instance
     *
     * @var WC_Dangerous_Goods_Product_Meta
     */
    private $product_meta;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
        $this->init_hooks();
    }
    
    /**
     * Initialize admin components
     */
    private function init() {
        $this->settings = new WC_Dangerous_Goods_Settings();
        $this->product_meta = new WC_Dangerous_Goods_Product_Meta();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Dangerous Goods Settings', 'wc-dangerous-goods'),
            __('Dangerous Goods', 'wc-dangerous-goods'),
            'manage_woocommerce',
            'wc-dangerous-goods-settings',
            array($this->settings, 'render_settings_page')
        );
    }
    
    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our settings page and product edit pages
        $allowed_pages = array(
            'woocommerce_page_wc-dangerous-goods-settings',
            'post.php',
            'post-new.php'
        );
        
        if (!in_array($hook, $allowed_pages, true)) {
            return;
        }
        
        // Only load on product edit pages
        if (in_array($hook, array('post.php', 'post-new.php'), true)) {
            global $post;
            if (!$post || 'product' !== $post->post_type) {
                return;
            }
        }
        
        // Enqueue admin styles
        wp_enqueue_style(
            'wc-dangerous-goods-admin',
            WC_DANGEROUS_GOODS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WC_DANGEROUS_GOODS_VERSION
        );
        
        // Enqueue admin scripts
        wp_enqueue_script(
            'wc-dangerous-goods-admin',
            WC_DANGEROUS_GOODS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WC_DANGEROUS_GOODS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script(
            'wc-dangerous-goods-admin',
            'wc_dangerous_goods_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_dangerous_goods_admin'),
                'strings' => array(
                    'confirm_reset' => __('Are you sure you want to reset settings to defaults?', 'wc-dangerous-goods')
                )
            )
        );
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        // Display settings saved notice
        if (isset($_GET['page']) && 
            'wc-dangerous-goods-settings' === $_GET['page'] && 
            isset($_GET['settings-updated']) && 
            'true' === $_GET['settings-updated']) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Settings saved successfully!', 'wc-dangerous-goods'); ?></p>
            </div>
            <?php
        }
    }
}