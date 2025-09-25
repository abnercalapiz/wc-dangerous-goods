<?php
/**
 * Cart warning notice template
 *
 * @package WC_Dangerous_Goods
 * @since 1.0.0
 *
 * @var float $fee_amount Fee amount
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="woocommerce-info wc-dangerous-goods-notice wc-dangerous-goods-cart-notice">
    <strong>
        <span class="icon">⚠️</span>
        <?php esc_html_e('Dangerous Goods Detected', 'wc-dangerous-goods'); ?>
    </strong>
    <?php
    printf(
        /* translators: %s: fee amount */
        esc_html__('Your cart contains dangerous goods. A %s handling fee has been added below.', 'wc-dangerous-goods'),
        wp_kses_post(wc_price($fee_amount))
    );
    ?>
</div>