(function ($) {
    window.catalogInitDiscountCodeGenerator = function () {
        $(this).click(function () {
            var code = '';

            var map = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

            for (var i = 0; i < 10; i++) {
                code += map.charAt(Math.floor(Math.random() * map.length));
            }

            $('#input-discount_code').val(code);
        });
    };

    window.catalogInitDiscountTypeSelector = function () {
        var last_type = this.options[this.selectedIndex].value;
        var cached = {};
        var frm_cached = {};

        $('[data-discount-types]').each(function () {
            var frm = $(this);

            this.getAttribute('data-discount-types').split(',').map(function (type) {
                return type.trim();
            }).filter(function (type) {
                return type !== '';
            }).forEach(function (type) {
                if (typeof frm_cached[type] === 'undefined') {
                    frm_cached[type] = [];
                }

                frm_cached[type].push(frm);
            });
        });

        $(this).change(function () {
            cached[last_type] = $('#input-discount_value').val();

            if (typeof frm_cached[last_type] !== 'undefined') {
                frm_cached[last_type].forEach(function (frm) {
                    frm.hide();

                    frm.find('[name=prerequisite_type]').attr('disabled', 'disabled')
                });
            }

            last_type = this.options[this.selectedIndex].value;

            if (['free_shipping', 'bxgy'].indexOf(last_type) >= 0) {
                $('#input-discount_value').parent().parent().css('visibility', 'hidden');
            } else {
                var wrap = $('#input-discount_value').val(typeof cached[last_type] === 'undefined' ? '' : cached[last_type]).parent();

                wrap.parent().css('visibility', 'visible');

                wrap.find('.styled-input-icon').remove();

                var icon = $('<div />').addClass('styled-input-icon').append($('<div />').text(last_type === 'fixed_amount' ? '$' : '%'));

                wrap[last_type !== 'fixed_amount' ? 'append' : 'prepend'](icon);
            }

            if (typeof frm_cached[last_type] !== 'undefined') {
                frm_cached[last_type].forEach(function (frm) {
                    frm.show();
                    frm.find('[name=prerequisite_type]').removeAttr('disabled')

                    var radio_inputs = frm.find('.apply_to').find('input[type="radio"]');
                    if (last_type === 'percent') {
                        if (!radio_inputs.filter('[checked="checked"]')[0]) {
                            radio_inputs.first().attr('checked', 'checked');
                        }
                    } else if (last_type === 'fixed_amount') {
                        var checkbox_inputs = frm.find('.apply_to').find('input[type="checkbox"]');

                        if (!checkbox_inputs.filter('[checked="checked"]')[0]) {
                            radio_inputs.first().attr('checked', 'checked');
                        }
                    }
                });
            }
        }).trigger('change');
    };

    window.catalogInitDiscountCodeApplyTypeChange = function () {
        var cached = {};

        var radio_inputs = $(this).find('input[type="radio"]');

        if (validate_by_last_type('percent')) {
            if (!radio_inputs.filter('[checked="checked"]')[0]) {
                radio_inputs.first().attr('checked', 'checked');
            }
        }

        radio_inputs.click(function () {
            if (!validate_by_last_type('percent')) {
                return;
            }
            var selector = radio_inputs.closest('.block').find('.catalog-item-selector');

            if (selector[0]) {
                selector.detach();
            }

            if (this.value === 'none') {
                return;
            }

            if (typeof cached[this.value] !== 'undefined') {
                cached[this.value].insertAfter($(this).closest('.frm-grid'));
            } else {
                cached[this.value] = $('<div />').css({
                    marginTop: '15px',
                    marginBottom: '-20px',
                    borderBottomLeftRadius: '3px',
                    borderBottomRightRadius: '3px'
                }).insertAfter($(this).closest('.frm-grid'));

                var json_tag = $('script[data-json="' + (this.value === 'collection' ? 'prerequisite_collection' : 'prerequisite_product') + '"]')[0];

                var default_data = json_tag ? JSON.parse(json_tag.innerHTML) : [];

                if (this.value === 'collection') {
                    cached[this.value].osc_itemSelector({
                        browse_url: OSC_CATALOG_COLLECTION_SELECTOR_BROWSE_URL,
                        placeholder_text: 'Search for collections',
                        no_selected_text: 'Don\'t have any collection selected',
                        filter_in_result: true,
                        input_name: 'prerequisite_collection_id[]',
                        data: default_data
                    });
                } else if (this.value === 'product') {
                    catalogInitProductSelector.apply(cached[this.value], [{browse_url: OSC_CATALOG_PRODUCT_SELECTOR_BROWSE_URL, input_name: 'prerequisite_product_id[]', data: default_data}]);
                }
            }
        });

        radio_inputs.filter('[checked="checked"]').trigger('click');
    };

    function validate_by_last_type(type = 'percent') {
        const last_type = $('#input-discount_type').val()
        return last_type === type
    }

    window.catalogInitDiscountCodeApplyTypeChangeFixedAmount = function () {
        var cached = {};

        var radio_inputs = $(this).find('input[type="radio"]');
        var checkbox_inputs = $(this).find('input[type="checkbox"]');

        radio_inputs.click(function () {
            if (!validate_by_last_type('fixed_amount')) {
                return;
            }
            checkbox_inputs.prop('checked', false)
            if (this.value === 'entire_order') {
                $('.catalog-item-selector').remove()
            }
        })

        if (validate_by_last_type('fixed_amount')) {
            if (!checkbox_inputs.filter('[checked="checked"]')[0]) {
                radio_inputs.first().attr('checked', 'checked');
            }
        }

        checkbox_inputs.change(function () {
            if (!validate_by_last_type('fixed_amount')) {
                return;
            }

            radio_inputs.prop('checked', false);

            var ischecked= $(this).is(':checked');

            if(!ischecked) {
                $(this).closest('.condition-type-checker').next('.catalog-item-selector').remove()
                return;
            }

            if (this.value === 'none') {
                return;
            }
            if (typeof cached[this.value] !== 'undefined') {
                cached[this.value].insertAfter($(this).closest('.condition-type-checker'));
            } else {
                cached[this.value] = $('<div />').css({
                    marginTop: '15px',
                    marginBottom: '20px',
                    borderBottomLeftRadius: '3px',
                    borderBottomRightRadius: '3px'
                }).insertAfter($(this).closest('.condition-type-checker'));

                var json_tag = $('script[data-json="' + (this.value === 'collection' ? 'prerequisite_collection' : 'prerequisite_product') + '"]')[0];

                var default_data = json_tag ? JSON.parse(json_tag.innerHTML) : [];

                if (this.value === 'collection') {
                    cached[this.value].osc_itemSelector({
                        browse_url: OSC_CATALOG_COLLECTION_SELECTOR_BROWSE_URL,
                        placeholder_text: 'Search for collections',
                        no_selected_text: 'Don\'t have any collection selected',
                        filter_in_result: true,
                        input_name: 'prerequisite_collection_id[]',
                        data: default_data,
                    });
                } else if (this.value === 'product') {
                    catalogInitProductSelector.apply(cached[this.value], [{
                        browse_url: OSC_CATALOG_PRODUCT_SELECTOR_BROWSE_URL,
                        input_name: 'prerequisite_product_id[]',
                        data: default_data,
                    }]);
                }
            }
        });

        checkbox_inputs.filter('[checked="checked"]').trigger('change');
    };

    window.catalogInitDiscountCodeConditionChange = function () {
        var cached = {};

        var radio_inputs = $(this).find('input[type="radio"]');

        if (!radio_inputs.filter('[checked="checked"]')[0]) {
            radio_inputs.first().attr('checked', 'checked');
        }

        radio_inputs.click(function () {
            var condition_value_wrap = radio_inputs.closest('.block').find('#input-prerequisite_subtotal, #input-prerequisite_quantity, #input-prerequisite_shipping').closest('.styled-input-wrap');

            if (condition_value_wrap[0]) {
                condition_value_wrap.parent().detach();
            }

            if (this.value === 'none') {
                return;
            }

            if (typeof cached[this.value] !== 'undefined') {
                cached[this.value].insertAfter($(this).closest('.condition-type-checker'));
            } else if (this.value === 'shipping') {
                var json_tag = $('script[data-json="prerequisite_shipping"]')[0];

                var default_data = json_tag ? JSON.parse(json_tag.innerHTML) : [];

                const shipping_selected = this.dataset.shipping_selected
                let shipping_option_el = `<div class="styled-select styled-input-wrap"><select name="prerequisite_shipping" id="input-prerequisite_shipping">`
                default_data.forEach(function (shipping) {
                    shipping_option_el += `<option ${shipping_selected && shipping_selected === shipping.shipping_key ? 'selected' : ''} value="${shipping.shipping_key}" >${shipping.shipping_name}</option>`
                    shipping_option_el += `\n`
                })
                shipping_option_el += `</select><ins></ins></div>`

                condition_value_wrap = shipping_option_el

                cached[this.value] = $('<div />').addClass('mb10').css({width: '250px', marginLeft: '25px'}).append(condition_value_wrap).insertAfter($(this).closest('.condition-type-checker'));
            } else {
                condition_value_wrap = $('<div />').addClass('styled-input-wrap');
                condition_value_wrap.append($('<input />').addClass('styled-input').attr({type: 'text', name: 'prerequisite_' + this.value, id: 'input-prerequisite_' + this.value, value: this.getAttribute('data-' + this.value)}));

                if (this.value === 'subtotal') {
                    condition_value_wrap.append($('<div />').addClass('styled-input-icon').append($('<div />').text('$')));
                }

                cached[this.value] = $('<div />').addClass('mb10').css({width: '150px', marginLeft: '25px'}).append(condition_value_wrap).insertAfter($(this).closest('.condition-type-checker'));
            }
        });

        radio_inputs.filter('[checked="checked"]').trigger('click');
    };

    window.catalogInitDiscountCodeCustomerLimitChange = function () {
        var cached = {};

        var radio_inputs = $(this).find('input[type="radio"]');

        if (!radio_inputs.filter('[checked="checked"]')[0]) {
            radio_inputs.first().attr('checked', 'checked');
        }

        radio_inputs.click(function () {
            var selector = radio_inputs.closest('.block').find('.catalog-item-selector');

            if (selector[0]) {
                selector.detach();
            }

            if (this.value === 'none') {
                return;
            }

            if (typeof cached[this.value] !== 'undefined') {
                cached[this.value].insertAfter($(this).closest('.frm-grid'));
            } else {
                cached[this.value] = $('<div />').css({
                    marginTop: '15px',
                    marginBottom: '-20px',
                    borderBottomLeftRadius: '3px',
                    borderBottomRightRadius: '3px'
                }).insertAfter($(this).closest('.frm-grid'));

                var json_tag = $('script[data-json="' + (this.value === 'group' ? 'prerequisite_customer_group' : 'prerequisite_customer') + '"]')[0];

                var default_data = json_tag ? JSON.parse(json_tag.innerHTML) : [];

                if (this.value === 'group') {
                    cached[this.value].osc_itemSelector({
                        browse_url: OSC_CATALOG_CUSTOMER_GROUP_SELECTOR_BROWSE_URL,
                        placeholder_text: 'Search for customer groups',
                        no_selected_text: 'Don\'t have any customer group selected',
                        filter_in_result: true,
                        input_name: 'prerequisite_customer_group[]',
                        data: default_data
                    });
                } else {
                    cached[this.value].osc_itemSelector({
                        browse_url: OSC_CATALOG_CUSTOMER_SELECTOR_BROWSE_URL,
                        placeholder_text: 'Search for customers',
                        no_selected_text: 'Don\'t have any customer selected',
                        filter_in_result: true,
                        input_name: 'prerequisite_customer_id[]',
                        attributes_extend: [{key: 'email', class: 'catalog-customer-selector-email', position: 4}],
                        data: default_data
                    });
                }
            }
        });

        radio_inputs.filter('[checked="checked"]').trigger('click');
    };

    window.catalogInitDiscountCodeUsageLimitSwitcherChange = function () {
        var cached = $(this).closest('.styled-checkbox').parent().next('.styled-input-wrap');

        $(this).bind('click render', function () {
            if (!this.checked) {
                if (cached[0]) {
                    cached.detach();
                }
            } else {
                if (cached[0]) {
                    cached.insertAfter($(this).closest('.styled-checkbox').parent());
                } else {
                    cached = $('<div />').addClass('styled-input-wrap mb10').css({width: '150px', marginLeft: '25px'}).insertAfter($(this).closest('.styled-checkbox').parent());
                    cached.append($('<input />').addClass('styled-input').attr({type: 'text', name: 'usage_limit', id: 'input-usage_limit', value: this.getAttribute('data-limit')}));
                }
            }
        }).filter('[checked="checked"]').trigger('render');
    };

    window.catalogInitDiscountCodeMaxItemAllowChange = function () {
        var cached = $(this).closest('.styled-checkbox').parent().next('.styled-input-wrap');

        $(this).bind('click render', function () {
            if (!this.checked) {
                if (cached[0]) {
                    cached.detach();
                }
            } else {
                if (cached[0]) {
                    cached.insertAfter($(this).closest('.styled-checkbox').parent());
                } else {
                    cached = $('<div />').addClass('styled-input-wrap mb10').css({width: '150px', marginLeft: '25px'}).insertAfter($(this).closest('.styled-checkbox').parent());
                    cached.append($('<input />').addClass('styled-input').attr({type: 'text', name: 'max_item_allow', value: this.getAttribute('data-limit')}));
                }
            }
        }).filter('[checked="checked"]').trigger('render');
    };

    window.catalogInitDiscountCodeFreeShippingLimit = function () {
        var country_selector_cached = $(this).find('.osc-item-selector');

        var radio_inputs = $(this).find('input[type="radio"]');

        if (!radio_inputs.filter('[checked="checked"]')[0]) {
            radio_inputs.first().attr('checked', 'checked');
        }

        radio_inputs.click(function () {
            if (this.value === '0') {
                if (country_selector_cached[0]) {
                    country_selector_cached.detach();
                }

                return;
            }

            if (country_selector_cached[0]) {
                country_selector_cached.insertAfter($(this).closest('.frm-grid'));
            } else {
                var json_tag = $('script[data-json="prerequisite_country"]')[0];

                var default_data = json_tag ? JSON.parse(json_tag.innerHTML) : [];

                country_selector_cached = $('<div />').css({marginTop: '15px', marginBottom: '-20px'}).insertAfter($(this).closest('.frm-grid'));
                country_selector_cached.osc_itemSelector({browse_url: OSC_COUNTRY_BROWSE_URL, input_name: 'prerequisite_country_code[]', no_selected_text: 'Don\'t have any country selected', data: default_data, filter_in_result: true});
            }
        });

        radio_inputs.filter('[checked="checked"]').trigger('click');

        var prerequisite_shipping_rate_cached = $(this).find('#input-prerequisite_shipping_rate').closest('.styled-checkbox').parent().next('.styled-input-wrap');

        $(this).find('#free_shipping_limit_rate').bind('click render', function () {
            if (!this.checked) {
                if (prerequisite_shipping_rate_cached[0]) {
                    prerequisite_shipping_rate_cached.detach();
                }

                return;
            }

            if (prerequisite_shipping_rate_cached[0]) {
                prerequisite_shipping_rate_cached.insertAfter($(this).closest('.styled-checkbox').parent());
            } else {
                prerequisite_shipping_rate_cached = $('<div />').addClass('styled-input-wrap mt5').css({width: '150px', marginLeft: '25px'}).insertAfter($(this).closest('.styled-checkbox').parent());
                prerequisite_shipping_rate_cached.append($('<div />').addClass('styled-input-icon').append($('<div />').text('$')));
                prerequisite_shipping_rate_cached.append($('<input />').addClass('styled-input').attr({type: 'text', name: 'prerequisite_shipping_rate', id: 'input-prerequisite_shipping_rate', value: this.getAttribute('data-limit')}));
            }
        }).filter('[checked="checked"]').trigger('render');
    };

    window.catalogInitDiscountCodeBuyXGetY = function () {
        var allocation_limit_input = $(this).find('#input-bxgy_allocation_limit');

        $('#input-bxgy-allocation_limit-switcher').bind('click render', function () {
            if (!this.checked) {
                if (allocation_limit_input[0]) {
                    allocation_limit_input.detach();
                }

                return;
            }

            if (allocation_limit_input[0]) {
                allocation_limit_input.insertAfter($(this).closest('.styled-checkbox').parent());
            } else {
                allocation_limit_input = $('<div />').addClass('styled-input-wrap mt5').css({width: '150px', marginLeft: '25px'}).insertAfter($(this).closest('.styled-checkbox').parent());
                allocation_limit_input.append($('<input />').addClass('styled-input').attr({type: 'text', name: 'bxgy_allocation_limit', id: 'input-bxgy_allocation_limit', value: this.getAttribute('data-limit')}));
            }
        }).filter('[checked="checked"]').trigger('render');

        var bxgy_discount_percent_input = $(this).find('#input-bxgy_discount_rate');

        var discount_types = $(this).find('input[name="bxgy-discount_type"]').click(function () {
            if (this.value === 'free') {
                if (bxgy_discount_percent_input[0]) {
                    bxgy_discount_percent_input.detach();
                }

                return;
            }

            if (bxgy_discount_percent_input[0]) {
                bxgy_discount_percent_input.insertAfter($(this).closest('.styled-radio').parent());
            } else {
                bxgy_discount_percent_input = $('<div />').addClass('styled-input-wrap mt5').css({width: '150px', marginLeft: '25px'}).insertAfter($(this).closest('.styled-radio').parent());
                bxgy_discount_percent_input.append($('<input />').addClass('styled-input').attr({type: 'text', name: 'bxgy_discount_rate', id: 'input-bxgy_discount_rate', value: this.getAttribute('data-rate')}));
                bxgy_discount_percent_input.append($('<div />').addClass('styled-input-icon').append($('<div />').text('%')));
            }
        });

        if (discount_types.filter('[checked="checked"]')[0]) {
            discount_types.filter('[checked="checked"]').trigger('click');
        } else {
            discount_types.first().trigger('click');
        }

        $.each(['prerequisite', 'entitled'], function (k, key) {
            var cached = {};

            $('#input-bxgy-' + key + '_type').change(function () {
                var type = this.options[this.selectedIndex].value;

                $.each(cached, function (k, selector) {
                    selector.detach();
                });

                if (typeof cached[type] !== 'undefined') {
                    cached[type].insertAfter($(this).closest('.frm-grid'));
                } else {
                    cached[type] = $('<div />').addClass('mt15').insertAfter($(this).closest('.frm-grid'));

                    var json_tag = $('script[data-json="bxgy_' + key + '_' + type + '"]')[0];

                    var default_data = json_tag ? JSON.parse(json_tag.innerHTML) : [];

                    if (type === 'collection') {
                        cached[type].osc_itemSelector({
                            browse_url: OSC_CATALOG_COLLECTION_SELECTOR_BROWSE_URL,
                            placeholder_text: 'Search for collections',
                            no_selected_text: 'Don\'t have any collection selected',
                            filter_in_result: true,
                            input_name: key + '_collection_id[]',
                            data: default_data
                        });
                    } else {
                        catalogInitProductSelector.apply(cached[type], [{browse_url: OSC_CATALOG_PRODUCT_SELECTOR_BROWSE_URL, input_name: key + '_product_id[]', data: default_data}]);
                    }
                }
            }).trigger('change');
        });
    };
})(jQuery);