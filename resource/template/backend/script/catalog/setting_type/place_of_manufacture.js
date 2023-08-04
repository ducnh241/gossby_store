(function ($) {
    'use strict';
    window.catalogInitSettingPlaceOfManufacture = function () {
        let container = $(this);
        let frm_name = container.attr('data-name');
        let json = fetchJSONTag(container, 'place_of_manufacture');
        let box = $('<div />').addClass('box-root');

        let table_item = $('<table />').appendTo(box);
        __addTableHead();

        if (json.data) {
            $.each(json.data, function (key_json, country_data) {
                __addRow(key_json, country_data);
            });
        }

        $(document).on('change', '.select-country-customer', function () {
            __initSelectCountryCustomer();
        });
        $(document).on('change', '.select-place-of-manufacture', function () {
            __initSelectCountryCustomer();
        });

        $('<div style="float: right" />').addClass('btn btn-primary mt10').html($.renderIcon('icon-plus', 'mr5') ).append('Add new group').appendTo(container).click(function () {
            __addRow();
        });

        __initSelectCountryCustomer();

        __getCountriesSelected();

        function __initSelect2(item) {
            item.select2();
        }

        function __addRow(key_json, country_data) {
            box.prependTo(container);
            if (key_json == undefined) {
                key_json = $.makeUniqid()
            }

            if (box.find('table').length === 0) {
                table_item = $('<table />').appendTo(box);
                __addTableHead();
            }

            let table_row_item = $('<tr />').addClass('table-row-item').appendTo(table_item);
            let col_country_customer = $('<div/>').appendTo($('<td />').appendTo(table_row_item));
            let col_place_of_manufacture = $('<div/>').appendTo($('<td />').appendTo(table_row_item));

            let country_customer = $('<select />').addClass('select-country-customer').attr('multiple', 'multiple').attr('name', frm_name + '[' + key_json + '][country_customer][]').prependTo($('<div />').appendTo(col_country_customer));
            let country_place_of_manufacture = $('<select />').addClass('select-place-of-manufacture').attr('name', frm_name + '[' + key_json + '][country_place_of_manufacture][]').prependTo($('<div />').appendTo(col_place_of_manufacture));
            $('<option />').attr('value', '').text('Please select an option').appendTo(country_place_of_manufacture);

            __initSelect2(country_customer);
            __initSelect2(country_place_of_manufacture);

            let data_selected = __getCountriesSelected(),
                country_customer_selected = data_selected.country_customer.selected,
                country_customer_available = data_selected.country_customer.available,
                place_of_manufacture_selected = data_selected.place_of_manufacture.selected,
                place_of_manufacture_available = data_selected.place_of_manufacture.available;

            //Render select country customer
            if (country_data && country_data.country_customer) {
                $.each(json.countries, function (key, name) {
                    let option_customer = $('<option />').attr('value', key).text(name.title).appendTo(country_customer);
                    if (country_data && country_data.country_customer.includes(key)) {
                        option_customer.attr('selected', 'selected');
                    } else {
                        if (country_customer_selected.includes(option_customer.val())) {
                            option_customer.remove();
                        }
                    }
                });
            } else {
                $.each(country_customer_available, function (key, name) {
                    let country_title_c = __getCountryTitle(name);
                    if (country_title_c) {
                        $('<option />').attr('value', name).text(country_title_c).appendTo(country_customer);
                    }
                });
            }

            //Render select country manufacture
            if (country_data && country_data.country_place_of_manufacture) {
                $.each(json.countries, function (key, name) {
                    let option_place_of_manufacture = $('<option />').attr('value', key).text(name.title).appendTo(country_place_of_manufacture);
                    if (country_data && country_data.country_place_of_manufacture.includes(key)) {
                        option_place_of_manufacture.attr('selected', 'selected');
                    } else {
                        if (place_of_manufacture_selected.includes(option_place_of_manufacture.val())) {
                            option_place_of_manufacture.remove();
                        }
                    }
                });
            } else {
                $.each(place_of_manufacture_available, function (key, name) {
                    let country_title_p = __getCountryTitle(name);
                    if (country_title_p) {
                        $('<option />').attr('value', name).text(country_title_p).appendTo(country_place_of_manufacture);
                    }
                });
            }

            let col_action = $('<div/>').appendTo($('<td />').appendTo(table_row_item));
            $('<div />').append($.renderIcon('trash-alt-regular')).addClass('btn btn-small btn-icon ml5').appendTo(col_action).click(function () {
                table_row_item.remove();
                if (table_item.find('.table-row-item').length < 1) {
                    table_item.parent().append($('<input />').attr({type: 'hidden', name: frm_name, value: ''}));
                    table_item.html('').detach('');
                }
            });

            return table_item;
        }

        function __addTableHead() {
            let table_head = $('<tr />').addClass('table-head').appendTo(table_item);
            $('<th />').text('Customer Country').appendTo(table_head);
            $('<th />').text('Place of manufacture').width('25%').appendTo(table_head);
            $('<th />').text('Action').width('5%').appendTo(table_head);
        }

        function __getCountriesSelected() {
            let countries_code = [];
            let country_customer = [];
            let place_of_manufacture = [];
            let country_customer_selected = [];
            let place_of_manufacture_selected = [];
            let data = [];

            $.each(json.countries, function (country_code, country_title) {
                if (country_code != '') {
                    countries_code.push(country_code);
                }
            });

            $('.select-country-customer option:selected').each(function () {
                country_customer_selected.push($(this).val());
            });
            let country_customer_available = countries_code.filter(item => !country_customer_selected.includes(item));

            $('.select-place-of-manufacture option:selected').each(function () {
                place_of_manufacture_selected.push($(this).val());
            });
            let place_of_manufacture_available = countries_code.filter(item => !place_of_manufacture_selected.includes(item));

            country_customer.selected = country_customer_selected;
            country_customer.available = country_customer_available;
            place_of_manufacture.selected = place_of_manufacture_selected;
            place_of_manufacture.available = place_of_manufacture_available;
            data.country_customer = country_customer;
            data.place_of_manufacture = place_of_manufacture;

            return data;
        }

        function __initSelectCountryCustomer() {
            let data = __getCountriesSelected(),
                country_customer_selected = data.country_customer.selected,
                country_customer_available = data.country_customer.available,
                place_of_manufacture_selected = data.place_of_manufacture.selected,
                place_of_manufacture_available = data.place_of_manufacture.available;

            $('.select-country-customer').each(function () {
                let _this = this;

                $(_this).find("option:not(:selected)").each(function () {
                    if (country_customer_selected.includes($(this).val())) {
                        $(this).remove();
                    }
                });

                $.each(country_customer_available, function (key, name) {
                    let country_title_c = __getCountryTitle(name);
                    if ($(_this).find("option[value='" + name + "']").length == 0 && country_title_c) {
                        $('<option />').attr('value', name).text(country_title_c).appendTo(_this);
                    }
                });
            });

            $('.select-place-of-manufacture').each(function () {
                let _this = this;

                $(_this).find("option:not(:selected)").each(function () {
                    if (place_of_manufacture_selected.includes($(this).val())) {
                        $(this).remove();
                    }
                });

                $.each(place_of_manufacture_available, function (key, name) {
                    let country_title_p = __getCountryTitle(name);
                    if ($(_this).find("option[value='" + name + "']").length == 0 && country_title_p) {
                        $('<option />').attr('value', name).text(country_title_p).appendTo(_this);
                    }
                });
            });
        }

        function __getCountryTitle(country_code) {
            return json.countries[country_code] ? json.countries[country_code].title : '';
        }
    };
})(jQuery);
