(function ($) {
    'use strict';
    window.catalogInitSettingPlusMinusPriceTable = function () {
        var container = $(this);

        var frm_name = container.attr('data-name');

        var json = fetchJSONTag(container, 'plus-minus-price-table');

        var box = $('<div />');

        function init(item) {
            item.select2({
                dropdownAutoWidth : true
            });
        }
        var country_exist = [];

        function checkCountryExist(box) {
            $.each(box.find($(".select2-country option:selected")), function(){
                country_exist.push($(this).val());
            });
            country_exist = country_exist.filter(function(itm, i, a) {
                return i == a.indexOf(itm);
            });

            return country_exist;
        }

        function __addRow(country, data, country_exist) {
            box.prependTo(container);

            var key = $.makeUniqid();
            var hash_id = data ? data.hash_id : key;
            var blocked_product_type = data ? data.blocked_product_type : null;

            data = data ? data.product_type : null;

            var row = $('<div style="padding: 10px; margin: 5px 0" />').addClass('block box-item').attr('data-content', 1).appendTo(box);

            $('<div />').addClass('title').text('Hash: ' + hash_id).appendTo(row);
            $('<input />').attr('type', 'hidden').attr('name', frm_name + '[' + key + '][hash_id]').attr('value', hash_id).addClass('styled-input').appendTo(row);
            $('<div />').addClass('title').text('Country: ').appendTo(row);

            var country_selector = $('<select />').addClass('select2-country').attr('multiple','multiple').attr('name', frm_name + '[' + key + '][country][]').on('change',  function (e) {
                country_exist = checkCountryExist(box);
            }).prependTo($('<div />').appendTo(row));

            $('<div />').addClass('title mt5').text('Product type block render feed: ').appendTo(row);
            var block_product_type_selector = $('<select />')
                .addClass('select2-country')
                .attr('multiple','multiple')
                .attr('name', frm_name + '[' + key + '][blocked_product_type][]')
                .prependTo($('<div />').appendTo(row));

            init(block_product_type_selector);

            $.each(json.product_types, function (key, name) {
                if (key != '*') {
                    var option = $('<option />').attr('value', key).text(name).appendTo(block_product_type_selector);
                }

                if (blocked_product_type && blocked_product_type.includes(key) === true) {
                    option.attr('selected', 'selected');
                }
            });

            init(country_selector);

            if (!country_exist) {
                country_exist = checkCountryExist(box);
            }
            var country_arr = [];
            if (country) {
                country_arr = country.split("_");
            }
            var product_type_exist = [];

            function checkProductTypeExist(table) {
                $.each(table.find($(".product-type-select option:selected")), function(){
                    product_type_exist.push($(this).val());
                });
                product_type_exist = product_type_exist.filter(function(itm, i, a) {
                    return i == a.indexOf(itm);
                });

                return product_type_exist;
            }
            $.each(json.countries, function (key, name) {
                if (country_exist && ((!country && country_exist.includes(key) === true) || (country_arr && country_arr.includes(key) !== true && country_exist.includes(key) === true))) {
                    return;
                }

                var option = $('<option />').attr('value', key).text(name).appendTo(country_selector);

                if (country && country_arr.includes(key) === true) {
                    option.attr('selected', 'selected');
                }
            });

            var column_data = $('<div style="padding: 5px 0"/>').appendTo(row);
            var table_data = $('<table />').appendTo(column_data);

            var row_data = $('<tr />').appendTo(table_data);
            $('<th />').text('Product type').appendTo(row_data);
            $('<th />').width('25%').text('Value').appendTo(row_data);
            $('<th />').width('25%').text('Type price').appendTo(row_data);
            $('<th />').width('5%').text('Action').appendTo(row_data);

            function __addRowData(product_type, data_row, product_type_exist = []) {
                var key_data = $.makeUniqid();

                var row = $('<tr />').attr('data-content-td', 1).appendTo(table_data);

                var product_type_selector = $('<select />').attr('name', frm_name + '[' + key + '][' + key_data + '][product_type]').prependTo($('<div />').addClass('styled-select product-type-select').on('change', function (e) {
                    product_type_exist = checkProductTypeExist(row);
                }).append($('<ins />')).appendTo($('<td />').appendTo(row)));

                if (!product_type_exist) {
                    product_type_exist = checkProductTypeExist(row);
                }

                $.each(json.product_types, function (key, name) {
                    if (product_type_exist && ((!product_type && product_type_exist.includes(key) === true) || (product_type && product_type !== key && product_type_exist.includes(key) === true))) {
                        return;
                    }

                    var option = $('<option />').attr('value', key).text(name).appendTo(product_type_selector);

                    if (key === product_type) {
                        option.attr('selected', 'selected');
                    }
                });

                $('<input />').attr('maxlength', '15').attr('type', 'text').attr('name', frm_name + '[' + key + '][' + key_data + '][price]').attr('value', data_row ? data_row['price'] : '' ).addClass('styled-input').appendTo($('<td />').appendTo(row));

                var price_type_selector = $('<select />').attr('name', frm_name + '[' + key + '][' + key_data + '][price_type]').prependTo($('<div />').addClass('styled-select').append($('<ins />')).appendTo($('<td />').appendTo(row)));

                $.each(json.price_types, function (key, name) {
                    var option = $('<option />').attr('value', key).text(name).appendTo(price_type_selector);

                    if (data_row && key === data_row['price_type']) {
                        option.attr('selected', 'selected');
                    }
                });

                var control_bar = $('<td />').appendTo(row);

                $('<div />').append($.renderIcon('trash-alt-regular')).addClass('btn btn-small btn-icon ml5').appendTo(control_bar).click(function () {
                    row.remove();

                    if (table_data.find('tr').length < 2) {
                        table_data.parent().append($('<input />').attr({type: 'hidden', name: frm_name, value: ''}));
                    } else {
                        table_data.find('tr[data-content-td="1"]').trigger('reorder-td');
                    }
                });

                table_data.find('tr[data-content-td="1"]').trigger('reorder-td');

                return row;
            }

            if (data) {
                $.each(data, function (product_type, data) {
                    product_type_exist.push(product_type);
                });

                $.each(data, function (product_type, data) {
                    __addRowData(product_type, data, product_type_exist);
                });
            }

            $('<div />').addClass('btn btn-small btn-primary mt10').text('Add product type').appendTo(column_data).click(function () {
                __addRowData(null, null, product_type_exist);
            });

            $('<div />').text('Delete group').addClass('btn btn-small btn-danger mt10').appendTo(column_data).click(function () {
                row.remove();

                if (box.find('.box-item').length < 1) {
                    box.parent().append($('<input />').attr({type: 'hidden', name: frm_name, value: ''}));
                    box.html('').detach();
                } else {
                    box.find('div[data-content="1"]').trigger('reorder');
                }
            });

            box.find('div[data-content="1"]').trigger('reorder');

            return row;
        }

        if (json.data) {
            $.each(json.data, function (country, country_data) {
                $.each(country.split("_"), function (code, country_code) {
                    country_exist.push(country_code);
                })
            });
            $.each(json.data, function (country, country_data) {
                __addRow(country, country_data, country_exist);
            });
        }

        $('<div style="float: right" />').addClass('btn btn-primary mt10').text('Add new group country').appendTo(container).click(function () {
            __addRow();
        });
    };
})(jQuery);
