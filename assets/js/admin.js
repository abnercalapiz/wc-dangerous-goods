/**
 * Admin JavaScript
 *
 * @package WC_Dangerous_Goods
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Settings page enhancements
        if ($('.wc-dangerous-goods-settings').length) {
            // Live preview update
            $('#fee_amount, #fee_label').on('input', function() {
                updatePreview();
            });
            
            function updatePreview() {
                var amount = $('#fee_amount').val() || 0;
                var label = $('#fee_label').val() || wc_dangerous_goods_admin.strings.default_label;
                
                $('.fee-preview .label').text(label + ':');
                // Note: We can't format currency in JS easily, so just show the number
                $('.fee-preview .amount').text('$' + parseFloat(amount).toFixed(2));
            }
        }
    });
})(jQuery);