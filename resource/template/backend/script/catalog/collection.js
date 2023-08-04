(function ($) {
    'use strict';
    window.onChangeSortOptions = function () {
        $(this).change(function () {
            if ($(this).val() === 'solds') {
                $('.date-ranger').show()
            } else {
                $('.date-ranger').hide()
            }
        })
        $(this).trigger('change')
    }
    window.initDateType = function () {
        $(this).change(function () {
            const _date_type = $(this).val()
            if (_date_type == 'relative') {
                $('#date_type_relative').show()
                $('#date_type_absolute').hide()
            } else {
                $('#date_type_absolute').show()
                $('#date_type_relative').hide()
            }
        })
        if ($(this).is(":checked")) {
            $(this).trigger('change')
        }
    }
})(jQuery);