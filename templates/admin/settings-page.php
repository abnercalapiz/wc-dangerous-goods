<?php
/**
 * Admin settings page template
 *
 * @package WC_Dangerous_Goods
 * @since 1.0.0
 *
 * @var array $settings Current plugin settings
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap wc-dangerous-goods-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wc-dangerous-goods-settings')); ?>">
        <?php wp_nonce_field('wc_dangerous_goods_save_settings', 'wc_dangerous_goods_settings_nonce'); ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="fee_amount">
                            <?php esc_html_e('Handling Fee Amount', 'wc-dangerous-goods'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" 
                               id="fee_amount" 
                               name="fee_amount" 
                               value="<?php echo esc_attr($settings['fee_amount']); ?>"
                               step="0.01"
                               min="0"
                               class="regular-text"
                               required />
                        <p class="description">
                            <?php esc_html_e('Enter the fee amount to charge when dangerous goods are in the cart. Enter 0 to disable the fee.', 'wc-dangerous-goods'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="fee_label">
                            <?php esc_html_e('Fee Label', 'wc-dangerous-goods'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="fee_label" 
                               name="fee_label" 
                               value="<?php echo esc_attr($settings['fee_label']); ?>"
                               class="regular-text"
                               required />
                        <p class="description">
                            <?php esc_html_e('Enter the label/name for the fee that will appear in cart and checkout.', 'wc-dangerous-goods'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <input type="submit" 
                   name="wc_dangerous_goods_save_settings" 
                   id="submit" 
                   class="button button-primary" 
                   value="<?php esc_attr_e('Save Settings', 'wc-dangerous-goods'); ?>">
        </p>
    </form>
    
    <div class="wc-dangerous-goods-info">
        <h2><?php esc_html_e('Preview', 'wc-dangerous-goods'); ?></h2>
        <div class="preview-box">
            <p><strong><?php esc_html_e('Current settings will show as:', 'wc-dangerous-goods'); ?></strong></p>
            <div class="fee-preview">
                <span class="icon">⚠️</span>
                <span class="label"><?php echo esc_html($settings['fee_label']); ?>:</span>
                <span class="amount"><?php echo wp_kses_post(wc_price($settings['fee_amount'])); ?></span>
            </div>
        </div>
        
        <h2><?php esc_html_e('How to Use', 'wc-dangerous-goods'); ?></h2>
        <ol>
            <li><?php esc_html_e('Edit any WooCommerce product (simple or variable)', 'wc-dangerous-goods'); ?></li>
            <li><?php esc_html_e('For simple products: Check the "Dangerous Goods" checkbox in the Shipping tab', 'wc-dangerous-goods'); ?></li>
            <li><?php esc_html_e('For variable products: Check the "Dangerous Goods" checkbox for individual variations', 'wc-dangerous-goods'); ?></li>
            <li><?php esc_html_e('When customers add these products to cart, the handling fee will be automatically added', 'wc-dangerous-goods'); ?></li>
        </ol>
        
        <h2><?php esc_html_e('Examples of Fee Labels', 'wc-dangerous-goods'); ?></h2>
        <ul>
            <li><?php esc_html_e('Dangerous Goods Handling Fee', 'wc-dangerous-goods'); ?></li>
            <li><?php esc_html_e('Hazardous Materials Surcharge', 'wc-dangerous-goods'); ?></li>
            <li><?php esc_html_e('Special Handling Fee', 'wc-dangerous-goods'); ?></li>
            <li><?php esc_html_e('DG Processing Fee', 'wc-dangerous-goods'); ?></li>
            <li><?php esc_html_e('Safety Compliance Fee', 'wc-dangerous-goods'); ?></li>
        </ul>
    </div>
</div>