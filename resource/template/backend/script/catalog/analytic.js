window.initAnalyticCustomDate = function () {
    let splitted;

    if (this.getAttribute('data-begin') !== '') {
        splitted = [this.getAttribute('data-begin'), this.getAttribute('data-end')];
    } else {
        splitted = [moment().format('DD/MM/YYYY')];
    }

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
        let value = picker.startDate.format('DD/MM/YYYY') + '-' + picker.endDate.format('DD/MM/YYYY');

        $('input[name=date_range]').val(value);
    });
};