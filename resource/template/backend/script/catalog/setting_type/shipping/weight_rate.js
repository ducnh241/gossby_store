(function ($) {
    'use strict';

    var PROVINCE_LOADED = null;

    window.catalogInitSettingWeightRateTable = function () {
        var container = $(this);

        var frm_name = container.attr('data-name');

        var json = fetchJSONTag(container, 'weight_rate_table');

        var table = $('<table />').css({border:'1px solid #f1f1f1'});

        function __addRow(country,province,weight,price,estimate,processing) {
            if (table.find('tr').length < 1) {
                container.find("input[type='hidden']").remove();

                table.prependTo(container);

                var row = $('<tr />').css({'background': '#e4e4e4'}).appendTo(table);

                $('<th />').width('16%').text('Country').appendTo(row);
                $('<th />').width('17%').text('Province').appendTo(row);
                $('<th />').width('7%').text('Weight').appendTo(row);
                $('<th />').width('10%').text('Price').appendTo(row);
                $('<th />').width('7%').text('Process').appendTo(row);
                $('<th />').width('7%').text('Estimate').appendTo(row);
                $('<th />').width('15%').html('&nbsp;').appendTo(row);
            }

            var key = $.makeUniqid();

            var row = $('<tr />').attr('data-content', 1).appendTo(table);

            var country_selector = $('<select />').attr('name', frm_name + '[' + key + '][country]').prependTo($('<div />').addClass('styled-select').append($('<ins />')).appendTo($('<td />').appendTo(row)));

            $.each(json.countries, function (_key, name) {
                var option = $('<option />').attr('value', _key).text(name).appendTo(country_selector);

                if (_key === country) {
                    option.attr('selected', 'selected');
                }

                if (_key === country) {
                    renderProvinceInput(table,frm_name,key,row,province,country);
                }
            });

            table.find('select[name="' + frm_name+ '[' + key + '][country]').change(function (e, skip_rerender_province) {
                if(! skip_rerender_province) {
                    renderProvinceInput(table,frm_name,key,row,table.find('[name='+ frm_name + '[' + key + '][province]').val(),$(this).val());
                }
            });

            var province_container = $('<div>').addClass("province_container_" + key).appendTo($('<div />')).appendTo($('<td />').appendTo(row));

            if (table.find('select[name="' + frm_name+ '[' + key + '][country]').val() == '*'){
                $('<input />').attr({
                    type: 'text',
                    class: 'styled-input',
                    readonly : true,
                    disabled: 'disabled',
                    name: frm_name + '[' + key + '][province]',
                }).css({
                    cursor: 'no-drop'
                }).appendTo(province_container);

            }else {
                $('<input />').attr({'name': frm_name + '[' + key + '][province]' , 'value' : province , 'placeholder' : 'Province'}).addClass('styled-input').appendTo(province_container);
            }

            var _weight = $('<input />').attr({'name': frm_name + '[' + key + '][weight]' , 'value' : weight , 'placeholder' : 'Weight'}).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row));

            var _price = $('<input />').attr({'name': frm_name + '[' + key + '][price]' , 'value' : price ,  'placeholder' : 'Price'}).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row));

            var process = $('<input />').attr({'name': frm_name + '[' + key + '][processing]' , 'value' : processing ,  'placeholder' : 'Processing'}).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row));

            var _estimate = $('<input />').attr({'name': frm_name + '[' + key + '][estimate]' , 'value' : estimate ,  'placeholder' : 'Estimate'}).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row));

            $.each({_weight, _price , _estimate ,process}, function (key,data) {
                data.change(function() {
                    if (isNaN($(this).val())){
                        alert('input is number');
                        $(this).attr({'value' : ''});
                        return
                    }

                    if(key != 'weight' && $(this).val() < 1){
                        alert('input is more than 0');
                        $(this).attr({'value' : ''});
                    }

                });
            });

            var control_bar = $('<td />').appendTo(row);

            var up_btn = $('<div />').append($.renderIcon('chevron-up-light')).addClass('btn btn-small btn-icon').appendTo(control_bar).click(function () {
                if (up_btn.attr('disabled') === 'disabled') {
                    return;
                }

                row.insertBefore(row.prev('tr[data-content="1"]'));
                row.trigger('reorder');
                row.next('tr[data-content="1"]').trigger('reorder');
            });

            var down_btn = $('<div />').append($.renderIcon('chevron-down-light')).addClass('btn btn-small btn-icon ml5').appendTo(control_bar).click(function () {
                if (down_btn.attr('disabled') === 'disabled') {
                    return;
                }

                row.insertAfter(row.next('tr[data-content="1"]'));
                row.trigger('reorder');
                row.prev('tr[data-content="1"]').trigger('reorder');
            });

            $('<div />').append($.renderIcon('trash-alt-regular')).addClass('btn btn-small btn-icon ml5').appendTo(control_bar).click(function () {
                row.remove();

                if (table.find('tr').length < 2) {
                    table.parent().append($('<input />').attr({type: 'hidden', name: frm_name, value: ''}));
                    table.html('').detach();
                } else {
                    table.find('tr[data-content="1"]').trigger('reorder');
                }
            });
            //
            // $('<div />').append($.renderIcon('clone')).addClass('btn btn-small btn-icon ml5').appendTo(control_bar).click(function () {
            //     var clone = __addRow(country_selector.val());
            //     clone.insertAfter(row);
            //
            //     table.find('tr[data-content="1"]').trigger('reorder');
            // });

            row.bind('reorder', function (e) {
                e.stopImmediatePropagation();

                if (!row.prev('tr[data-content="1"]')[0]) {
                    up_btn.attr('disabled', 'disabled');
                } else {
                    up_btn.removeAttr('disabled');
                }

                if (!row.next('tr[data-content="1"]')[0]) {
                    down_btn.attr('disabled', 'disabled');
                } else {
                    down_btn.removeAttr('disabled');
                }
            });

            table.find('tr[data-content="1"]').trigger('reorder');

            return row;
        }


        loadProvinces(json.provinces)

        if (json.data) {
            $.each(json.data, function (country, country_data) {
                $.each(country_data,function (province,province_data){
                    $.each(province_data,function (weight,weight_data){
                        __addRow(country,province,weight,weight_data['price'],weight_data['estimate'],weight_data['processing']);
                    });
                });
            });
        }

        $('<div />').addClass('btn btn-primary mt10').text('Add new row').appendTo(container).click(function () {
            __addRow();
        });
    };

    function renderProvinceInput(table ,frm_name ,key ,row ,pro,country_code) {
        setTimeout(function () {
            var province_container = table.find(".province_container_" + key);

            province_container.html('');

            province_container.removeClass('styled-select');

            if (country_code == '*'){
                $('<input />').attr({
                    type: 'text',
                    class: 'styled-input',
                    readonly : true,
                    disabled: 'disabled',
                    name: frm_name + '[' + key + '][province]',
                }).css({
                   cursor: 'no-drop'
                }).appendTo(province_container);

                return;
            }

            if (typeof PROVINCE_LOADED[country_code] === 'undefined') {
                $('<input />').attr({
                    type: 'text',
                    placeholder: 'Province',
                    class: 'styled-input',
                    name: frm_name + '[' + key + '][province]',
                    value: pro
                }).appendTo(province_container);

            } else {
                province_container.addClass('styled-select');
                var select = $('<select />').attr({
                    name: frm_name + '[' + key + '][province]',
                }).appendTo(province_container);

                $('<ins>').appendTo(province_container);

                $('<option />').attr('value', 'N/A').text('All Province').appendTo(select);

                PROVINCE_LOADED[country_code].forEach(function (province) {
                    var option = $('<option />').attr('value', province.id).text(province.title).appendTo(select);

                    if (province.id === pro) {
                        option.attr('selected', 'selected');
                    }
                });
            }

        }, 50);
    }

    function loadProvinces(provinces) {
        PROVINCE_LOADED = provinces;

        for (var x in PROVINCE_LOADED) {
            var titles = [];
            var map = {};

            PROVINCE_LOADED[x].forEach(function (province, y) {
                var province_title = typeof province === 'string' ? province : province.title;

                if (province_title.substring(0, 12) === 'Armed Forces') {
                    province_title = 'ZZZZZZZZZ' + province_title;
                }

                map[province_title] = y;

                titles.push(province_title);
            });

            titles.sort();

            var buff = [];

            titles.forEach(function (title) {
                buff.push(PROVINCE_LOADED[x][map[title]]);
            });

            PROVINCE_LOADED[x] = buff;
        }
    }

})(jQuery);