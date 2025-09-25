<?php
/**
 * Settings management class
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
 * Settings class
 */
class WC_Dangerous_Goods_Settings {
    
    /**
     * Settings option name
     *
     * @var string
     */
    private $option_name = 'wc_dangerous_goods_settings';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'handle_settings_submit'));
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Security check
        if (!current_user_can('manage_woocommerce')) {
            wp_die(
                esc_html__('You do not have sufficient permissions to access this page.', 'wc-dangerous-goods'),
                esc_html__('Unauthorized Access', 'wc-dangerous-goods'),
                array('response' => 403)
            );
        }
        
        // Get current settings
        $settings = WC_Dangerous_Goods::get_settings();
        
        // Include template
        include WC_DANGEROUS_GOODS_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }
    
    /**
     * Handle settings form submission
     */
    public function handle_settings_submit() {
        // Check if form was submitted
        if (!isset($_POST['wc_dangerous_goods_save_settings'])) {
            return;
        }
        
        // Verify nonce
        if (!isset($_POST['wc_dangerous_goods_settings_nonce']) || 
            !wp_verify_nonce($_POST['wc_dangerous_goods_settings_nonce'], 'wc_dangerous_goods_save_settings')) {
            wp_die(
                esc_html__('Security check failed. Please try again.', 'wc-dangerous-goods'),
                esc_html__('Security Error', 'wc-dangerous-goods'),
                array('response' => 403)
            );
        }
        
        // Check capabilities
        if (!current_user_can('manage_woocommerce')) {
            wp_die(
                esc_html__('You do not have sufficient permissions to save these settings.', 'wc-dangerous-goods'),
                esc_html__('Unauthorized Access', 'wc-dangerous-goods'),
                array('response' => 403)
            );
        }
        
        // Validate and sanitize inputs
        $fee_amount = isset($_POST['fee_amount']) ? floatval($_POST['fee_amount']) : 20;
        $fee_amount = max(0, $fee_amount); // Ensure non-negative
        $fee_amount = round($fee_amount, 2); // Round to 2 decimal places
        
        $fee_label = isset($_POST['fee_label']) ? sanitize_text_field($_POST['fee_label']) : '';
        if (empty($fee_label)) {
            $fee_label = __('Dangerous Goods Fee', 'wc-dangerous-goods');
        }
        
        // Save settings
        $settings = array(
            'fee_amount' => $fee_amount,
            'fee_label' => $fee_label
        );
        
        update_option($this->option_name, $settings);
        
        // Redirect with success message
        wp_redirect(add_query_arg(
            array(
                'page' => 'wc-dangerous-goods-settings',
                'settings-updated' => 'true'
            ),
            admin_url('admin.php')
        ));
        exit;
    }
    
    /**
     * Get default settings
     *
     * @return array
     */
    public static function get_defaults() {
        return array(
            'fee_amount' => 20,
            'fee_label' => __('Dangerous Goods Fee', 'wc-dangerous-goods')
        );
    }
}