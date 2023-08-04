(function ($) {
    'use strict';
    window.catalogInitSettingTipMaximum = function () {
        $(this).keyup(function () {
            const $this = $(this)
            $this.val($this.val().replace(/[^0-9\.]/g, ''))
        })
    };
})(jQuery);
