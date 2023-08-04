(function ($) {
    'use strict';

    window.initSubmitFormProductAbtest = function () {
        const that = $(this)
        that.click(function () {
            if (this.getAttribute('disabled') === 'disabled') {
                return;
            }

            const id = $(this).attr('data-id');

            const status = $('input#ab_product_status').val();

            const title = $('input#campaign_title').val();

            const begin_at = $('input#input_active_date').val();

            const finish_at = $('input#input_deactive_date').val();

            const products = $('select#input-product-ids').val();

            if (title == '' || title == undefined) {
                alert('Title not found');
                return;
            }

            if (begin_at == '' || begin_at == undefined) {
                alert('Begin time not found');
                return;
            }

            if (finish_at == '' || finish_at == undefined) {
                alert('Finish time not found');
                return;
            }

            if (finish_at < begin_at) {
                alert('Finish time need greater than or equal Begin time');
                return;
            }

            if (products == null || products == undefined) {
                alert('Product not found');
                return;
            }

            if (products.length < 2) {
                alert('Product need greater than or equal 2');
                return;
            }

            const default_product_id = $('#default-product').data('default_product_id')

            this.setAttribute('disabled', 'disabled');

            const data = {
                id,
                status,
                title,
                begin_at,
                finish_at,
                products,
                default_product_id
            }

            $.ajax({
                type: 'POST',
                url: $.base_url + '/autoAb/backend_abProduct/post/hash/' + OSC_HASH,
                data,
                success: function (response) {
                    if (response.result == 'OK') {
                        if (status == 1) { // status In Process
                            alert(`Campaign URL: ${response.data.hub_url}`)
                        } else {
                            alert(response.data.message);
                        }
                        window.location = response.data.redirect;
                    } else {
                        alert(response.message.replace(/<br\s*[\/]?>/gi, "\n"));
                    }
                    that.setAttribute('disabled', false);
                }
            })
        })
    }

    window.initSelectAbProduct = function () {
        $(this).select2({
            width: '100%',
            ajax: {
                url: $.base_url + '/autoAb/backend_abProduct/getListProducts/hash/' + OSC_HASH,
                dataType: 'json',
                type: 'GET',
                data: function (params) {
                    // Query parameters will be ?search=[term]&page=[page]
                    return {
                        keyword: params.term,
                        // page: params.page || 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data.data.result, function (item) {
                            return {
                                text: item.product_id + ' - ' + item.title,
                                id: item.product_id
                            }
                        })
                    };
                }
            }
        });
    };

    window.initDateRange = function () {
        const that = $(this)

        that.on('click', function () {

            activeRangeItem(that)
            $('.result-table table').addClass('render')
            const data = {
                'id': $('#range').data('id'),
                'range': that.data('range')
            }
            if (data.range == 'alltime') {
                $('.distribution-table').removeClass('d-none')
            } else {
                $('.distribution-table').addClass('d-none')
            }
            getTrackingRange(data)
        })

        if (that.data('range') == 'today') {
            that.trigger('click')
        }
    }

    window.initSetDefaultProduct = function () {
        const btn_set_default_product = $(this)

        btn_set_default_product.click(function () {
            $.unwrapContent('setDefaultProduct');

            const product_options = $('#input-product-ids option:selected')
            if (product_options.length < 2) {
                alert('Please! Select at least 2 options product')
                return
            }

            let modal_form = $('<form />').attr('id', 'set-default-product').addClass('osc-modal').width(500);

            let header = $('<header />').appendTo(modal_form);

            let save_default_product = null;

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('setDefaultProduct');
            }).appendTo(header);

            $('<div />').addClass('title').html('Set Product Default').appendTo($('<div />').addClass('main-group').appendTo(header));
            let modal_body = $('<div />').addClass('body post-frm').appendTo(modal_form);

            product_options.each(function () {
                $('<div />').addClass('mt5').css({'display': 'flex'})
                    .append($('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', checked: $('#default-product').attr('data-default_product_id') == $(this).val(), name: 'product_default_input', id: `product_default_input${$(this).val()}`, value: $(this).val(), 'data-title': $(this).text()})).append($('<ins />')))
                    .append($('<label />').addClass('label-inline').attr({for: `product_default_input${$(this).val()}`}).html($(this).text()))
                    .appendTo(modal_body)

            })

            let action_bar = $('<div />').addClass('action-bar').appendTo(modal_form);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('setDefaultProduct');
            }).appendTo(action_bar);

            save_default_product = $('<button />').addClass('btn btn-primary ml10').html('Save').click(function () {

                $('#default-product').html($('input[name=product_default_input]:checked')?.data('title')).attr({'data-default_product_id': $('input[name=product_default_input]:checked')?.val()})

                $.unwrapContent('setDefaultProduct');

            }).appendTo(action_bar);

            $.wrapContent(modal_form, {key: 'setDefaultProduct'});
            modal_form.moveToCenter().css('top', '100px');
        })
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
                'id': $('#range').data('id'),
                'range': value
            }
            $('.distribution-table').addClass('d-none')
            activeRangeItem(node)
            $('.result-table table').addClass('render')
            getTrackingRange(data)
        });
    };

    $('#input_active_date').on('change', function () {

        let begin_time = $(this).val().split('/')
        $('#input_deactive_date').removeData('osc-ui-frm-datePicker').OSC_UI_DatePicker({
            'date_format': 'DD/MM/YYYY',
            'min_date': `${begin_time[1]}/${begin_time[0]}/${begin_time[2]}`
        });
    })

    function activeRangeItem(element) {
        $('.range-item').removeClass('active')
        element.parent().addClass('active')
    }

    function renderResultTable(data) {
        $('.result-table table tbody').remove()

        const product_titles = fetchJSONTag($('#range'), "product-titles") || []

        let tb_body = $('<tbody />')
        $('<tr />').append($('<th />').html('Product ID'))
            .append($('<th />').html('Name'))
            .append($('<th />').html('Unique visitor'))
            .append($('<th />').html('Page view'))
            .append($('<th />').html('Total order'))
            .append($('<th />').html('Total sale'))
            .append($('<th />').html('Revenue'))
            .append($('<th />').html('AOV'))
            .append($('<th />').html('CR'))
            .appendTo(tb_body)

        if (data.length < 1) {
            $('<tr />').html('No data').appendTo(tb_body)
        }

        data.forEach(function (item) {

            const total_order = parseInt(item.total_order)
            const unique_visitor = parseInt(item.unique_visitor)

            if (product_titles[item.product_id]) {
                $('<tr />').append($('<td />').html(item.product_id))
                    .append($('<td />').css({'text-align': 'left', 'width': '350px'}).html(product_titles[item.product_id]))
                    .append($('<td />').html(item.unique_visitor))
                    .append($('<td />').html(item.page_view))
                    .append($('<td />').html(total_order))
                    .append($('<td />').html(item.quantity))
                    .append($('<td />').html(`$${$.round(parseInt(item.revenue) / 100, 2)}`))
                    .append($('<td />').html(total_order ? $.round(item.quantity/total_order, 2) : '-'))
                    .append($('<td />').html((total_order && unique_visitor) ? $.round((total_order/unique_visitor) * 100.0, 2) + '%' : '-'))
                    .appendTo(tb_body)
            }
        })

        $('.result-table table').append(tb_body)
        $('.result-table table').removeClass('render')
    }

    function getTrackingRange(data) {
        $.ajax({
            type: 'POST',
            url: $.base_url + '/autoAb/backend_abProduct/getTrackingRange/hash/' + OSC_HASH,
            data,
            success: function (response) {
                if (response.result == 'OK') {
                    renderResultTable(response.data.data)
                } else {
                    alert(response.message.replace(/<br\s*[\/]?>/gi, "\n"));
                }
            }
        })
    }

})(jQuery);
