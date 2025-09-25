<?php
/**
 * Checkout warning notice template
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
<div class="wc-dangerous-goods-notice wc-dangerous-goods-checkout-notice">
    <p>
        <strong>
            <span class="icon">⚠️</span>
            <?php esc_html_e('Important', 'wc-dangerous-goods'); ?>
        </strong>
        <?php
        printf(
            /* translators: %s: fee amount */
            esc_html__('Your order contains dangerous goods. A %s handling fee has been applied to cover special shipping requirements.', 'wc-dangerous-goods'),
            wp_kses_post(wc_price($fee_amount))
        );
        ?>
    </p>
</div>