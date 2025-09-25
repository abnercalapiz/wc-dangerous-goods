<?php
/**
 * Product warning notice template
 *
 * @package WC_Dangerous_Goods
 * @since 1.0.0
 *
 * @var WC_Product $product Product object
 * @var float $fee_amount Fee amount
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wc-dangerous-goods-notice wc-dangerous-goods-product-notice">
    <div class="notice-content">
        <strong>
            <span class="icon">⚠️</span>
            <?php esc_html_e('Dangerous Goods', 'wc-dangerous-goods'); ?>
        </strong>
        <?php
        printf(
            /* translators: %s: fee amount */
            esc_html__('This product contains dangerous goods. A %s handling fee will be added at checkout.', 'wc-dangerous-goods'),
            wp_kses_post(wc_price($fee_amount))
        );
        ?>
    </div>
</div>