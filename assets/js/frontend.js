/**
 * Frontend JavaScript
 *
 * @package WC_Dangerous_Goods
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Variable product handling
        if ($('.variations_form').length) {
            $('.variations_form').on('found_variation', function(event, variation) {
                // Could be extended to show/hide dangerous goods notice based on variation
                // This is a placeholder for future functionality
            });
        }
    });
})(jQuery);