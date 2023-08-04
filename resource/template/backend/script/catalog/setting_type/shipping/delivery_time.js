(function ($) {
    'use strict';

    window.catalogInitSettingDeliveryTime = function () {
        let container = $(this);

        let frm_name = container.attr('data-name');

        let btn_add_group = $('#add_group_delivery_time');

        let json = fetchJSONTag(container, 'quantity-rate-table');

        let box = $('<div />');

        container.parent().parent().parent().find('div[class="frm-line e20"]').remove();

        btn_add_group.css({
            height: '40px',
            fontWeight: '600',
            paddingTop: '3px'
        });

        let group_id = 0;

        function init(item) {
            item.select2({
                dropdownAutoWidth: true,
                width: '100%',
                theme: 'default select2-container--custom'
            });
        }

        function __addRow(data) {
            box.prependTo(container);

            let key = $.makeUniqid();
            let selected_location_data = data ? data.location_data : '*';
            let delivery_configs = data ? data.delivery_configs : {};

            let row = $('<div />').addClass('block box-item').attr('data-content', 1).appendTo(box);

            let box_ = $('<div />')
                .css({margin : '5px 0'})
                .attr('data-key', frm_name + '[' + key + '][location_data]')
                .attr('data-value', selected_location_data)
                .prependTo(row);

            group_id++;
            $('<div />').text('Group #' + group_id).addClass('display--inline-block').css({
                fontWeight : '700'
            }).appendTo(box_);

            $('<div />').text('Delete Group #' + group_id)
                .addClass('btn-remove fright display--inline-block')
                .attr('id',  'btn-remove-group' + key)
                .appendTo(box_)
                .click(function () {
                    row.remove();
                    line.remove();

                    if (box.find('.box-item').length < 1) {

                        box.parent().append($('<input />').attr({type: 'hidden', name: frm_name, value: ''}));
                        box.html('').detach();
                    } else {
                        box.find('div[data-content="1"]').trigger('reorder');
                    }
                    $(this).remove();
                });

            $('<div />').addClass('title').text('Location:').css({marginTop : '10px'}).appendTo(box_);
            initSelectGroupLocation(box_);

            if (delivery_configs) {
                $.each(delivery_configs, function (k, v) {
                    __addDeliveryConfig(box_, key, v);
                });
            }

            $('<div />').append($.renderIcon('plus', 'mr5'))
                .addClass('btn btn-small btn-secondary-add mt15 box-sizing-content')
                .append($('<span>').text('Add Delivery Configuration'))
                .appendTo(row)
                .click(function () {
                    __addDeliveryConfig(box_, key);
                });



            let line = $('<div/>').addClass('frm-line mt50 e20').appendTo(box);

            box.find('div[data-content="1"]').trigger('reorder');

            return row;
        }

        function __addDeliveryConfig(row, key, delivery_configs) {
            let key_config = $.makeUniqid();
            let selected_product_types = delivery_configs ? delivery_configs.product_types : {};
            let processing_value = delivery_configs ? delivery_configs.processing : 0;
            let estimate_value = delivery_configs ? delivery_configs.estimate : 0;

            let table_heading = $('<h4 />').css({
                background: '#ECEEF6',
                border: '1px dashed #E5E7F2',
                borderRadius: '4px',
                fontSize: '14px',
                height: '35px',
                lineHeight: '35px',
                color: '#282364',
                padding: '0 10px',
                margin: '10px 0 -11px'
            }).text('Delivery Configuration').appendTo($('<div />').attr('data-fee-config', '1').appendTo(row));

            $('<div />').addClass('close-btn')
                .appendTo(table_heading)
                .click(function () {
                    column_data.remove();
                    table_heading.remove();

                    if (row.find('div[data-fee-config="1"]').length === 1) {
                        $('#btn-remove-group' + key).trigger('click');
                    }
                });

            let column_data = $('<div />').css({
                marginTop: '10px',
                border: '1px solid #e0e0e0',
            }).appendTo(row);

            let product_type_el = $('<div />').addClass('pt10 pr20 pl15').appendTo(column_data);
            $('<div />').text('Product Type:').appendTo(product_type_el);

            let product_type_selector = $('<select />').attr({
                name: frm_name + '[' + key + '][delivery_configs][' + key_config + '][product_types][]',
                multiple:'multiple'
            }).appendTo(product_type_el);

            let opt_all_product_type = $('<option />').attr('value', '*').text('All Product Types').appendTo(product_type_selector);
            $.each(json.product_types, function (key, name) {
                let option = $('<option />').attr('value', key).text(name).appendTo(product_type_selector);

                $.each(selected_product_types, function (_key, product_type) {
                    if (product_type === '*') {
                        opt_all_product_type.attr('selected', 'selected');
                    }

                    if (key == product_type) {
                        option.attr('selected', 'selected');
                    }
                })
            });

            init(product_type_selector);

            let time_setting = $('<div />').addClass('pr15 pl15 pb20 time-block').appendTo(column_data);

            let processing_setting = $('<div />').addClass('display--inline-block mr20').css({
                width: '31%'
            }).appendTo(time_setting);
            $('<div />').addClass('title').text('Processing:').css({marginTop : '10px'}).appendTo(processing_setting);

            let processing = $('<input />').addClass('styled-input processing').attr({
                name: frm_name + '[' + key + '][delivery_configs][' + key_config + '][processing]',
                value: processing_value
            }).appendTo(processing_setting);

            let estimate_setting = $('<div />').addClass('display--inline-block mr20').css({
                width: '31%'
            }).appendTo(time_setting)
            $('<div />').addClass('title').text('Estimate:').css({marginTop : '10px'}).appendTo(estimate_setting);

            let estimate = $('<input />').addClass('styled-input estimate').attr({
                name: frm_name + '[' + key + '][delivery_configs][' + key_config + '][estimate]',
                value: estimate_value
            }).appendTo(estimate_setting);

            let express_setting = $('<div />').addClass('display--inline-block').css({
                width: '31%',
                verticalAlign: 'top',
                marginTop : '10px'
            }).appendTo(time_setting)


            $('<span />').addClass('express_setting').text(_formatDate(parseInt(processing_value) + parseInt(estimate_value))).css({marginTop : '10px'}).appendTo(express_setting);

            $.each({processing, estimate}, function (key, data) {
                data.change(function () {
                    if (isNaN($(this).val())) {
                        alert('The ' + key + ' must be a number.');
                        $(this).attr({'value': ''});
                        return
                    }

                    if ($(this).val() < 1) {
                        alert('The ' + key + ' must be greater than 0');
                        $(this).attr({'value': ''});
                    }

                    const time_block = $(this).parents('.time-block')
                    const processing_val = time_block.find('.processing').val()
                    const estimate_val = time_block.find('.estimate').val()
                    time_block.find('.express_setting').text(_formatDate(parseInt(processing_val) + parseInt(estimate_val)))
                });
            });
        }

        if (json.delivery_time_settings) {
            $.each(json.delivery_time_settings, function (key, value) {
                __addRow(value);
            });
        }

        btn_add_group.click(function () {
            __addRow();
        });
    };

    function _formatDate(days) {
        const date = new Date(window.fetchEstimateTimeExceptWeekendDays(days));
        return `Estimated date when the customer is expected to receive the order if it was placed today: ${date.format('d/m/Y')}`
    }

})(jQuery);
