(function ($) {
    'use strict'

    window.catalogInitSettingQuantityRateTable = function () {
        let group_id = 0

        let container = $(this)

        let frm_name = container.attr('data-name')

        let btn_add_group = $('#add_group_quantity')

        let json = fetchJSONTag(container, 'quantity-rate-table')
        let box = $('<div />')

        container.parent().parent().parent().find('div[class="frm-line e20"]').remove()

        function init(item) {
            item.select2({
                dropdownAutoWidth: true,
                width: '100%',
                theme: 'default select2-container--custom'
            })
        }

        function __addRow(data) {
            box.prependTo(container)

            let key = $.makeUniqid()
            let fee_configs = data ? data.fee_configs : {}
            let selected_location_data = data ? data.location_data : '*'

            let row = $('<div />').addClass('block box-item').attr('data-content', 1).appendTo(box)

            let box_ = $('<div />')
                .css({margin: '5px 0'})
                .attr('data-key', frm_name + '[' + key + '][location_data]')
                .attr('data-value', selected_location_data)
                .prependTo(row)

            group_id++
            $('<div />').text('Group #' + group_id).addClass('display--inline-block').css({
                fontWeight: '700'
            }).appendTo(box_)

            $('<div />').text('Delete Group #' + group_id)
                .addClass('btn-remove fright display--inline-block')
                .attr('id', 'btn-remove-group' + key)
                .appendTo(box_)
                .click(function () {
                    row.remove()
                    line.remove()

                    if (box.find('.box-item').length < 1) {

                        box.parent().append($('<input />').attr({type: 'hidden', name: frm_name, value: ''}))
                        box.html('').detach()
                    } else {
                        box.find('div[data-content="1"]').trigger('reorder')
                    }
                    $(this).remove()
                })

            $('<div />').addClass('title').text('Location:').css({marginTop: '10px'}).appendTo(box_)
            initSelectGroupLocation(box_)

            if (fee_configs) {
                $.each(fee_configs, function (k, v) {
                    __addFeeConfig(box_, key, v)
                })
            }

            $('<div />').append($.renderIcon('plus', 'mr5'))
                .addClass('btn btn-small btn-secondary-add mt15 box-sizing-content')
                .append($('<span>').text('Add Fee Configuration'))
                .appendTo(row)
                .click(function () {
                    __addFeeConfig(box_, key, null, false)
                })

            let line = $('<div/>').addClass('frm-line e20').appendTo(box)

            box.find('div[data-content="1"]').trigger('reorder')

            return row
        }

        function __addFeeConfig(row, key, fee_configs, flag_hide = true) {
            let key_config = $.makeUniqid()
            let selected_product_types = fee_configs ? fee_configs.product_types : {}
            let selected_ship_fee_el = (fee_configs && typeof fee_configs.shipping_configs_type !== 'undefined') ? fee_configs.shipping_configs_type : 0

            let column_data = $('<div />').css({
                'margin-top': '10px',
                'display': 'flex',
                'flex-wrap': 'wrap',
                'background': 'rgba(236, 238, 246, 0.5)',
                'border': '1px solid #e0e0e0',
            }).appendTo(row)

            let product_type_el = $('<div />').addClass('pt10 pr20 pl15').css({'width': '100%'}).appendTo(column_data).show()
            $('<div />').addClass('close-btn').css({right: '17px'}).appendTo($('<div />').css({height: '22px'}).appendTo(product_type_el))
                .click(function () {
                    column_data.remove()
                })
            $('<div />').text('Product Type:').css('font-weight', 'bold').appendTo(product_type_el)

            let product_type_selector = $('<select />').attr({
                name: frm_name + '[' + key + '][fee_configs][' + key_config + '][product_types][]',
                multiple: 'multiple'
            }).appendTo(product_type_el)

            let opt_all_product_type = $('<option />').attr('value', '*').text('All Product Types').appendTo(product_type_selector)
            $.each(json.product_types, function (key, name) {
                let option = $('<option />').attr('value', key).text(name).appendTo(product_type_selector)
                $.each(selected_product_types, function (_key, product_type) {
                    if (product_type === '*') {
                        opt_all_product_type.attr('selected', 'selected')
                    }

                    if (key == product_type) {
                        option.attr('selected', 'selected')
                    }
                })
            })

            init(product_type_selector)
            let div_ship_type = $('<div />').addClass('col-md-12 col-sm-12 col-lg-12')
                .css({
                    padding: 0
                })
                .appendTo(column_data).show()
            let ship_fee_el_left = $('<div />').addClass('pt10 pr20 pl15 col-md-6 col-lg-6 col-sm-6')
                .css({
                    'float': 'left',
                    'font-weight': 'bold',
                })
                .text('Shipping Fee:')
                .appendTo(div_ship_type).show()
            let ship_fee_el_right = $('<div />').addClass('pt10 pr20 pl15 col-md-6 col-lg-6 col-sm-6')
                .css({
                    'float': 'right'
                })
                .appendTo(div_ship_type).show()
            let sw_bt = $('<div />').addClass('switch6')
                .appendTo(ship_fee_el_right)
            let label_s6_light = $('<label />').addClass('switch6-light').appendTo(sw_bt)
            let input_type_fee = $('<input />')
                .attr({
                    'name': frm_name + '[' + key + '][fee_configs][' + key_config + '][type_fee_ship]',
                    'data-value': selected_ship_fee_el,
                    'type': 'checkbox'
                }).appendTo(label_s6_light)
            let parent_span_str_fee = $('<span />').appendTo(label_s6_light)
            let span_str_fee_static = $('<span />').text('Static Fee').appendTo(parent_span_str_fee)
            let span_str_fee_dynamic = $('<span />').text('Dynamic Fee').appendTo(parent_span_str_fee)
            let a_str_fee = $('<a />').addClass('btn btn-primary').appendTo(label_s6_light)
            input_type_fee.change(function () {
                if ($(this).prop("checked") == true) {
                    span_str_fee_static.removeClass().addClass('no_select')
                    span_str_fee_dynamic.removeClass().addClass('select_now')
                    str_dyn_fee.show()
                    str_sta_fee.hide()
                    input_type_fee.val(1)
                    table_data_static.hide()
                    product_type_el.show()
                    table_data_dynamic.show()
                    str_struct_fee.show()
                    btn_add_row.hide()
                } else {
                    span_str_fee_static.removeClass().addClass('select_now')
                    span_str_fee_dynamic.removeClass().addClass('no_select')
                    str_dyn_fee.hide()
                    str_sta_fee.show()
                    input_type_fee.val(0)
                    table_data_static.show()
                    product_type_el.show()
                    table_data_dynamic.hide()
                    str_struct_fee.hide()
                    btn_add_row.show()
                }
            })
            if (selected_ship_fee_el == 1) {
                span_str_fee_static.addClass('no_select')
                span_str_fee_dynamic.addClass('select_now')
                input_type_fee.prop("checked", true)
                input_type_fee.val(1)
                // btn_add_row.hide();
            } else {
                span_str_fee_static.addClass('select_now')
                span_str_fee_dynamic.addClass('no_select')
                input_type_fee.prop("checked", false)
                input_type_fee.val(0)
                // btn_add_row.show();
            }
            let div_table_fee = $('<div />').addClass('col-md-12 col-sm-12 col-lg-12')
                .css({
                    'border-radius': '4px',
                    'background': 'white',
                    'margin': '20px',
                    'flex': 'auto',
                    'border': '1px solid #E0E0E0',
                    'padding': 20
                })
                .appendTo(column_data).show()
            let div_show = $('<div />')
                .css({
                    'width': '74px',
                    'font-style': 'normal',
                    'font-weight': 'normal',
                    'font-size': '13px',
                    'display': 'flex',
                    'height': '33px',
                    'border': '1px solid #E0E0E0',
                    'cursor': 'pointer',
                    'box-sizing': 'border-box',
                    'border-radius': '4px',
                    'float': 'right',
                    'align-items': 'center',
                    'justify-content': 'center',
                    'position': 'relative',
                    'padding-left': '20px',
                })
                .css({'float': 'right'}).append($('<span>').css({
                    'position': 'absolute',
                    'left': '8px',
                }).text('Hide')).appendTo(div_table_fee)
            let btn_show = $('<div />').prepend($($.renderIcon('chevron-down-light')).css('width', '15px'))
                .addClass('btn btn-small').attr('style', 'left:-8px; padding-left: 56px;width: 100%;position:absolute;')
                .appendTo(div_show)
                .click(function () {
                    if (selected_ship_fee_el == 1) {
                        // $('#sta_fee_span').addClass('no_select');
                        // $('#dyn_fee_span').addClass('select_now');
                        table_data_static.hide()
                        table_data_dynamic.show()
                        str_struct_fee.show()
                        btn_add_row.hide()
                        div_show.children('span').text('Hide')
                    } else {
                        // $('#sta_fee_span').addClass('no_select');
                        // $('#dyn_fee_span').addClass('select_now');
                        table_data_static.show()
                        table_data_dynamic.hide()
                        str_struct_fee.hide()
                        btn_add_row.show()
                        div_show.children('span').text('Hide')
                    }
                    $(this).hide()
                    btn_hide.show()
                }).hide()

            let btn_hide = $('<div />').prepend($($.renderIcon('chevron-up-light')).css('width', '15px'))
                .addClass('btn btn-small').attr('style', 'left: -8px;padding-left: 52px;width: 100%;position:absolute;')
                .css({
                    color: '#828282'
                })
                .appendTo(div_show)
                .click(function () {
                    table_data_static.hide()
                    table_data_dynamic.hide()
                    str_struct_fee.hide()
                    btn_add_row.hide()
                    $(this).hide()
                    btn_show.show()
                    div_show.children('span').text('Show')
                    btn_add_row.hide()
                })
                .show()

            let str_dyn_fee = $('<p />').text("Dynamic Fee").show().css({
                'margin-top': 0,
                'font-size': '14px',
                'color': 'blue'
            }).appendTo(div_table_fee)
            let str_sta_fee = $('<p />').text("Static Fee").show().css({
                'margin-top': 0,
                'font-size': '14px',
                'color': 'blue'
            }).appendTo(div_table_fee)

            let table_data_static = $('<table />').css({
                'border-spacing': '5px'
            }).css({}).appendTo(div_table_fee)
            let row_data_static = $('<tr />').appendTo(table_data_static)
            $('<th />').width('45%').text('Quantity:').css({
                color: '#7386A6',
                'font-weight': 'normal'
            }).addClass('text_left').appendTo(row_data_static)
            $('<th />').width('45%').text('Price:').css({
                color: '#7386A6',
                'font-weight': 'normal'
            }).addClass('text_left').appendTo(row_data_static)
            $('<th />').width('5%').html('&nbsp;').appendTo(row_data_static)


            let str_struct_fee = $('<p />').text("Shipping Fee = Base + (Quantity - 1 ) * Plus").attr('id', 'struct_fee').show().css({'font-size': '14px'}).appendTo(div_table_fee)
            let table_data_dynamic = $('<table />').css({
                'border-spacing': '5px'
            }).css({'width': '100%'}).appendTo(div_table_fee)
            let row_data_dynamic = $('<tr />').appendTo(table_data_dynamic)
            $('<th />').width('50%').text('Base').css({
                color: '#7386A6',
                'font-weight': 'normal'
            }).addClass('text_left').appendTo(row_data_dynamic)
            $('<th />').width('50%').text('Plus').css({
                color: '#7386A6',
                'font-weight': 'normal'
            }).addClass('text_left').appendTo(row_data_dynamic)


            if (selected_ship_fee_el == 1) {
                table_data_static.hide()
                str_struct_fee.show()
                table_data_dynamic.show()
                str_dyn_fee.show()
                str_sta_fee.hide()
                // this.btn_add_row.hide();
            } else {
                str_dyn_fee.hide()
                str_sta_fee.show()
                table_data_static.show()
                str_struct_fee.hide()
                table_data_dynamic.hide()
                // this.btn_add_row.show();
            }

            function __addRowData(quantity_value, price_value) {
                let row = $('<tr />').addClass('custom-row').attr('data-content-td', 1).appendTo(table_data_static)

                let quantity = $('<input />').attr({
                    'name': frm_name + '[' + key + '][fee_configs][' + key_config + '][quantity][]',
                    'value': quantity_value,
                    'placeholder': 'Quantity'
                }).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row))

                let price = $('<input />').attr({
                    'name': frm_name + '[' + key + '][fee_configs][' + key_config + '][price][]',
                    'value': price_value,
                    'placeholder': 'Price'
                }).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row))

                $.each({quantity, price}, function (key, data) {
                    data.change(function () {
                        if (isNaN($(this).val())) {
                            alert('The ' + key + ' must be a number.');
                            $(this).attr({'value': ''})
                            return
                        }

                        if ($(this).val() < 1) {
                            alert('The ' + key + ' must be greater than 0');
                            $(this).attr({'value': ''})
                        }

                    })
                })

                let control_bar = $('<td />').appendTo(row)

                $('<div />').append($.renderIcon('trash-alt-regular'))
                    .addClass('btn btn-small btn-icon ml5')
                    .appendTo(control_bar)
                    .click(function () {
                        row.remove()

                        if (table_data_static.find('tr').length < 2) {
                            table_data_static.parent().append($('<input />').attr({
                                type: 'hidden',
                                name: frm_name,
                                value: ''
                            }))
                        } else {
                            table_data_static.find('tr[data-content-td="1"]').trigger('reorder-td')
                        }
                    })

                table_data_static.find('tr[data-content-td="1"]').trigger('reorder-td')

                return row
            }

            if (fee_configs) {
                $.each(fee_configs.shipping_configs, function (key, data) {
                    __addRowData(key, data.price)
                })
            }

            function __addRowData_dynamic(base, plus) {
                let row = $('<tr />').attr('data-content-dynamic-td', 1).appendTo(table_data_dynamic)

                let base_input = $('<input />').attr({
                    'name': frm_name + '[' + key + '][fee_configs][' + key_config + '][base]',
                    'value': base,
                    'placeholder': 'Base Fee'
                }).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row))

                let plus_input = $('<input />').attr({
                    'name': frm_name + '[' + key + '][fee_configs][' + key_config + '][plus]',
                    'value': plus,
                    'placeholder': 'Plus Fee'
                }).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row))

                $.each({base_input, plus_input}, function (key, data) {
                    data.change(function () {
                        if (isNaN($(this).val())) {
                            alert('The ' + key + ' must be a number.');
                            $(this).attr({'value': ''})
                            return
                        }

                        if ($(this).val() < 1) {
                            alert('The ' + key + ' must be greater than 0');
                            $(this).attr({'value': ''})
                        }

                    })
                })
                return row
            }

            if (fee_configs) {
                if (fee_configs.shipping_configs_dynamic) {
                    if (typeof fee_configs.shipping_configs_dynamic.base == "undefined" || typeof fee_configs.shipping_configs_dynamic.plus
                        == "undefined") {
                        __addRowData_dynamic('', '')
                    } else {
                        __addRowData_dynamic(fee_configs.shipping_configs_dynamic.base, fee_configs.shipping_configs_dynamic.plus)
                    }
                } else {
                    __addRowData_dynamic('', '')
                }
            } else {
                __addRowData_dynamic('', '')
            }
            let btn_add_row = $('<div />')
                .addClass('btn btn-small btn-outline-default mb15').css({
                    'width': '100%',
                    fontWeight: '600',
                    'border': '1px solid #E0E0E0',
                    'height': '35px',
                    'line-height': '35px',
                    'margin-top': '10px',
                })
                .append($('<span>').text('Add Row').addClass('ml10'))
                .appendTo(column_data)
                .click(function () {
                    __addRowData()
                }).show()
            $('<tr>').appendTo(table_data_static).append($('<td>').attr('colspan', 2).append(btn_add_row))
        }

        if (json.shipping_settings) {
            $.each(json.shipping_settings, function (key, value) {
                __addRow(value)
            })
        }

        btn_add_group.click(function () {
            __addRow()
        })
    }

})(jQuery)
