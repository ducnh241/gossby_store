(function ($) {
    'use strict';

    window.catalogInitSettingGoogleProductFeedTable = function () {
        var container = $(this);

        var frm_name = container.attr('data-name');

        var json = fetchJSONTag(container, 'google-product-feed-table');

        var table = $('<table />');

        function __addRow(product_type, feed_value) {
            let google_product_cat_id = feed_value.google_product_cat_id
            let shipping_label = feed_value.shipping_label
            let commerce_tax_category = feed_value.commerce_tax_category

            if (table.find('tr').length < 1) {
                container.find("input[type='hidden']").remove();
                table.prependTo(container);

                var row = $('<tr />').appendTo(table);
                $('<th />').width('35%').text('Product Type').appendTo(row);
                $('<th />').width('20%').text('Google Product Feed').appendTo(row);
                $('<th />').width('20%').text('Shipping Label').appendTo(row);
                $('<th />').width('25%').text('FB Tax Category').appendTo(row);
            }

            var key = $.makeUniqid();

            var row = $('<tr />').attr('data-content', 1).appendTo(table);

            var product_type_selector = $('<select />').attr('name', frm_name + '[' + key + '][product_type]').prependTo($('<div />').addClass('styled-select').append($('<ins />')).appendTo($('<td />').appendTo(row)));

            $.each(json.product_types, function (key, name) {
                if (key === product_type) {
                    var option = $('<option />').attr('value', key).text(name).appendTo(product_type_selector);
                    option.attr('selected', 'selected');
                }
            });

            $('<input />')
                .attr('maxlength', '15')
                .attr('type', 'number')
                .attr('name', frm_name + '[' + key + '][google_product_cat_id]')
                .attr('id', 'google_product_cat_id')
                .attr('value', google_product_cat_id ? google_product_cat_id : '')
                .addClass('styled-input')
                .appendTo($('<td />').appendTo(row));

            $('<input />')
                .attr('type', 'text')
                .attr('maxlength', '50')
                .attr('name', frm_name + '[' + key + '][shipping_label]')
                .attr('id', 'shipping_label')
                .attr('value', shipping_label ? shipping_label : '')
                .addClass('styled-input')
                .blur(function () {
                    const val = $(this).val()
                    const patt = /[^a-zA-Z0-9-|_'"()![\] ]/gi;
                    $(this).val(val.replace(patt, ''))
                })
                .appendTo($('<td />').appendTo(row));

            $('<input />')
                .attr('type', 'text')
                .attr('maxlength', '50')
                .attr('name', frm_name + '[' + key + '][commerce_tax_category]')
                .attr('id', 'commerce_tax_category')
                .attr('value', commerce_tax_category ? commerce_tax_category : '')
                .addClass('styled-input')
                .appendTo($('<td />').appendTo(row));
            return row;
        }

        if (json.data) {
            $.each(json.data, function (product_type, feed_value) {
                __addRow(product_type, feed_value);
            });
        }
    };
})(jQuery);
