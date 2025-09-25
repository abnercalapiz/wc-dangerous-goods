<?php
/**
 * Product meta management
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
 * Product meta class
 */
class WC_Dangerous_Goods_Product_Meta {
    
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
        // Simple products
        add_action('woocommerce_product_options_shipping', array($this, 'add_simple_product_field'));
        add_action('woocommerce_process_product_meta', array($this, 'save_simple_product_field'));
        
        // Variable products
        add_action('woocommerce_variation_options_pricing', array($this, 'add_variation_field'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'save_variation_field'), 10, 2);
    }
    
    /**
     * Add dangerous goods field to simple products
     */
    public function add_simple_product_field() {
        global $post;
        
        echo '<div class="options_group wc-dangerous-goods-options">';
        
        woocommerce_wp_checkbox(array(
            'id' => '_dangerous_goods',
            'label' => __('Dangerous Goods', 'wc-dangerous-goods'),
            'description' => __('Check this if the product contains dangerous goods', 'wc-dangerous-goods'),
            'desc_tip' => true,
            'value' => get_post_meta($post->ID, '_dangerous_goods', true)
        ));
        
        echo '</div>';
    }
    
    /**
     * Save dangerous goods field for simple products
     *
     * @param int $post_id Product ID
     */
    public function save_simple_product_field($post_id) {
        // Security checks
        if (!$this->can_save_product_meta($post_id)) {
            return;
        }
        
        // Sanitize and save
        $dangerous_goods = isset($_POST['_dangerous_goods']) ? 'yes' : 'no';
        update_post_meta($post_id, '_dangerous_goods', $dangerous_goods);
    }
    
    /**
     * Add dangerous goods field to variations
     *
     * @param int $loop Position in the loop
     * @param array $variation_data Variation data
     * @param object $variation Post object
     */
    public function add_variation_field($loop, $variation_data, $variation) {
        $dangerous_goods = get_post_meta($variation->ID, '_dangerous_goods', true);
        ?>
        <label class="tips" data-tip="<?php esc_attr_e('Check if this variation contains dangerous goods', 'wc-dangerous-goods'); ?>">
            <?php esc_html_e('Dangerous Goods', 'wc-dangerous-goods'); ?>
            <input type="checkbox" class="checkbox variable_dangerous_goods" 
                   name="_dangerous_goods[<?php echo esc_attr($variation->ID); ?>]" 
                   <?php checked($dangerous_goods, 'yes'); ?> />
        </label>
        <?php
    }
    
    /**
     * Save dangerous goods field for variations
     *
     * @param int $variation_id Variation ID
     * @param int $i Loop position
     */
    public function save_variation_field($variation_id, $i) {
        // Security checks - WooCommerce handles nonce verification for variations
        if (!current_user_can('edit_products')) {
            return;
        }
        
        // Check if variation can be edited
        $parent_id = wp_get_post_parent_id($variation_id);
        if (!$parent_id || !current_user_can('edit_post', $parent_id)) {
            return;
        }
        
        // Sanitize and save
        $dangerous_goods = isset($_POST['_dangerous_goods'][$variation_id]) ? 'yes' : 'no';
        update_post_meta($variation_id, '_dangerous_goods', $dangerous_goods);
    }
    
    /**
     * Check if product meta can be saved
     *
     * @param int $post_id Product ID
     * @return bool
     */
    private function can_save_product_meta($post_id) {
        // Check nonce - WooCommerce uses 'woocommerce_save_data'
        if (!isset($_POST['woocommerce_meta_nonce']) || 
            !wp_verify_nonce($_POST['woocommerce_meta_nonce'], 'woocommerce_save_data')) {
            return false;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }
        
        // Check permissions
        if (!current_user_can('edit_product', $post_id)) {
            return false;
        }
        
        // Check post type
        if ('product' !== get_post_type($post_id)) {
            return false;
        }
        
        return true;
    }
}