(function ($) {
    'use strict';

    window.initDateRange = function () {
        const _this = $(this)

        _this.on('click', function () {

            activeRangeItem(_this)
            $('.js-result-table .wrap-loader').show();
            $('.js-result-table > div').addClass('render');
            const data = {
                'id': $('#range').data('id'), 'range': _this.data('range')
            }
            getTrackingRange(data)
        })

        if (_this.data('range') === 'today') {
            _this.trigger('click')
        }
    }

    window.initCustomDateRange = function () {
        let node = $(this);

        let splitted = [moment().format('DD/MM/YYYY')]

        $(this).daterangepicker({
            popupAttrs: {'data-menu-elm': 1},
            alwaysShowCalendars: true,
            startDate: moment(splitted[0], "DD/MM/YYYY"),
            endDate: moment(splitted.length > 1 ? splitted[1] : splitted[0], "DD/MM/YYYY"),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }).bind('apply.daterangepicker', function (e, picker) {
            let value = '';
            if (parseInt(picker.startDate.format('YYYYMMDD')) === parseInt(picker.endDate.format('YYYYMMDD'))) {
                value = picker.startDate.format('DD/MM/YYYY');
            } else {
                value = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
            }

            const data = {
                'id': $('#range').data('id'), 'range': value
            }
            activeRangeItem(node)
            $('.js-result-table .wrap-loader').show();
            $('.js-result-table > div').addClass('render');
            getTrackingRange(data)
        });
    };

    $('#input_active_date').on('change', function () {

        let begin_time = $(this).val().split('/')
        $('#input_deactive_date').removeData('osc-ui-frm-datePicker').OSC_UI_DatePicker({
            'date_format': 'DD/MM/YYYY', 'min_date': `${begin_time[1]}/${begin_time[0]}/${begin_time[2]}`
        });
    })

    function activeRangeItem(element) {
        $('.range-item').removeClass('active')
        element.parent().addClass('active')
    }

    function getTrackingRange(data) {
        $.ajax({
            type: 'POST',
            url: $.base_url + '/addon/backend_report/getTrackingRange/hash/' + OSC_HASH,
            data,
            success: function (response) {
                if (response.result === 'OK') {
                    console.log(response.data.html);
                    $('.js-result-table').html(response.data.html)
                } else {
                    alert(response.message.replace(/<br\s*[\/]?>/gi, "\n"));
                }
            }
        })
    }

})(jQuery);
