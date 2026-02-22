/**
 * Color Picker für Fundgrube-Einstellungen (Farbschema)
 *
 * @package Fundgrube
 * @since 1.0.0
 */
(function($) {
    'use strict';

    $(function() {
        $('.fundgrube-color-picker').wpColorPicker({
            defaultColor: true,
            change: function() {
                $(this).trigger('change');
            }
        });
    });
})(jQuery);
