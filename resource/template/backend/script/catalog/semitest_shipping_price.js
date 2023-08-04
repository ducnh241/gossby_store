(function ($) {
    'use strict';

    window.catalogInitSemitestShippingPrice = function () {
        var container = $(this);

        var frm_name = container.attr('data-name');

        var json = fetchJSONTag(container, 'quantity-rate-table');

        var box = $('<div />').prependTo(container);

        var row = $('<div />').css({padding : '10px' , margin: '20px 0 5px 0' , border: '1px solid rgb(241, 241, 241)'}).addClass('block box-item').attr('data-content', 1).appendTo(box);

        var column_data = $('<div />').css({marginTop: '10px'}).appendTo(row);
        var table_data = $('<table />').css({border:'1px solid #f1f1f1', width: '100%'}).appendTo(column_data);

        var row_data = $('<tr />').css({'background': '#e4e4e4'}).appendTo(table_data);
        $('<th />').width('20%').text('Quantity').appendTo(row_data);
        $('<th />').width('20%').text('Price').appendTo(row_data);
        $('<th />').width('20%').text('Processing').appendTo(row_data);
        $('<th />').width('20%').text('Estimate').appendTo(row_data);
        $('<th />').width('20%').text('Action').appendTo(row_data);

        function __addRow(key, quantity, price, estimate, processing) {
            if (key == null) {
                key =  $.makeUniqid();
            }
            var row = $('<tr />').attr('data-content-td', 1).appendTo(table_data);

            var quantity = $('<input />').attr({'name': frm_name + '[' + key + '][quantity]' , 'value' : quantity , 'placeholder' : 'Quantity'}).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row));

            var price = $('<input />').attr({
                'name': frm_name + '[' + key + '][price]',
                'value': price,
                'placeholder': 'Price'
            }).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row));

            var processing = $('<input />').attr({
                'name': frm_name + '[' + key + '][processing]',
                'value': processing,
                'placeholder': 'Processing'
            }).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row));

            var estimate = $('<input />').attr({
                'name': frm_name + '[' + key + '][estimate]',
                'value': estimate,
                'placeholder': 'Estimate'
            }).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row));

            $.each({quantity, price, estimate,processing}, function (key, data) {
                data.change(function () {
                    if (isNaN($(this).val())) {
                        alert('input is number');
                        $(this).attr({'value': ''});
                        return
                    }

                    if ($(this).val() < 1) {
                        alert('input is more than 0');
                        $(this).attr({'value': ''});
                    }

                });
            });

            var control_bar = $('<td />').appendTo(row);

            var up_btn = $('<div />').append($.renderIcon('chevron-up-light')).addClass('btn btn-small btn-icon').appendTo(control_bar).click(function () {
                if (up_btn.attr('disabled') === 'disabled') {
                    return;
                }

                row.insertBefore(row.prev('tr[data-content-td="1"]'));
                row.trigger('reorder');
                row.next('tr[data-content-td="1"]').trigger('reorder');
            });

            var down_btn = $('<div />').append($.renderIcon('chevron-down-light')).addClass('btn btn-small btn-icon ml5').appendTo(control_bar).click(function () {
                if (down_btn.attr('disabled') === 'disabled') {
                    return;
                }

                row.insertAfter(row.next('tr[data-content-td="1"]'));
                row.trigger('reorder');
                row.prev('tr[data-content-td="1"]').trigger('reorder');
            });


            row.bind('reorder', function (e) {
                e.stopImmediatePropagation();

                if (!row.prev('tr[data-content-td="1"]')[0]) {
                    up_btn.attr('disabled', 'disabled');
                } else {
                    up_btn.removeAttr('disabled');
                }

                if (!row.next('tr[data-content-td="1"]')[0]) {
                    down_btn.attr('disabled', 'disabled');
                } else {
                    down_btn.removeAttr('disabled');
                }
            });

            $('<div />').append($.renderIcon('trash-alt-regular')).addClass('btn btn-small btn-primary btn-icon ml5').appendTo(control_bar).click(function () {
                row.remove();

                if (table_data.find('tr').length < 2) {
                    table_data.parent().append($('<input />').attr({type: 'hidden', name: frm_name, value: ''}));
                } else {
                    table_data.find('tr[data-content-td="1"]').trigger('reorder-td');
                }
            });

            table_data.find('tr[data-content-td="1"]').trigger('reorder-td');


            box.find('div[data-content="1"]').trigger('reorder');

            return row;
        }

        if (json.data) {
            $.each(json.data, function (key, value) {
                __addRow(key,value.quantity, value.price, value.estimate, value.processing);
            });
        }

        $('<div />').addClass('btn btn-primary mt10').text('Add row').appendTo(container).click(function () {
            __addRow();
        });
    };
})(jQuery);