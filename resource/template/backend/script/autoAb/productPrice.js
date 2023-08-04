(function ($) {
    'use strict';

    window.initSelectGroupCountry = function () {
        $(this).select2({
            width: '100%'
        });
    };

    window.initSelectProduct = function () {
        let config_type = $(this).attr('data-config-type');

        $(this).select2({
            width: '100%',
            ajax: {
                url: $.base_url + '/autoAb/backend_productPrice/getListProducts/config_type/' + config_type + '/hash/' + OSC_HASH,
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

    window.initSettingProductPriceCondition = function () {
        let container = $(this);

        let json = fetchJSONTag(container, 'setting-product-price-condition');

        let box = $('<div />');

        function __initOptionCondition(selector, condition) {
            let opt_campaign = $('<option />').attr('value', 0).text('Variant of campaign').appendTo(selector);
            let opt_product_type = $('<option />').attr('value', 1).text('Variants have product type variant').appendTo(selector);

            if (condition.option == 0) {
                opt_campaign.attr('selected', 'selected');
            } else {
                opt_product_type.attr('selected', 'selected');
            }
        }

        function __addRow(condition_id, condition) {
            box.prependTo(container);

            let key = condition_id ? condition_id : $.makeUniqid();
            let product_type_ids = condition ? condition.product_type_ids : {};
            let product_type_variant_ids = condition ? condition.product_type_variant_ids : {};
            let is_customize = condition ? condition.is_customize : 0;
            let base_cost_configs = condition ? condition.base_cost_configs : {};
            let condition_start = condition ? condition.condition_start : {};
            let condition_end = condition ? condition.condition_end : {};
            let prices = condition ? condition.price_range : {};

            console.log(base_cost_configs)

            let row = $('<div style="padding: 20px; margin: 5px 0" />')
                .addClass('block box-item')
                .attr('data-content', 1)
                .appendTo(box);

            $('<div />').addClass('display--inline_block').text('Product Type: ').width('150px').appendTo(row);
            let product_type_selector_el = $('<div />').addClass('select2 product-type-select ml10 display--inline_block')
                .css({
                    'width': '50%',
                    'vertical-align': 'middle'
                })
                .appendTo(row);
            let product_type_selector = $('<select />')
                .attr('name', 'conditions[' + key +'][product_type_ids][]')
                .attr('multiple', 'multiple')
                .attr('id', 'product-type-selector-' + key)
                .prependTo(product_type_selector_el).select2({
                    width: '100%'
                }).change(function () {
                    __initProductTypeVariantOpts(
                        json.product_type_variants,
                        product_type_selector,
                        product_type_variant_selector,
                        is_customize,
                        product_type_variant_ids
                    );
                });

            $.each(json.product_types, function (key, value) {
                let product_type_id = value.id;
                let name = value.title;
                let option = $('<option />').addClass('product_type_opts')
                    .attr('value', product_type_id)
                    .text(name)
                    .appendTo(product_type_selector);

                $.each(product_type_ids, function (k, val) {
                    if (val && parseInt(val) === product_type_id) {
                        option.attr('selected', 'selected');
                    }
                });
            });

            let switch_btn = $('<input/>').attr({
                type: 'checkbox',
                id: 'custom_ptv_btn_' + key
            }).addClass('mt10 mb10').css({
                marginLeft: '30px'
            }).appendTo(row).click(function () {
                __initCustomProductTypeVariant(switch_btn, product_type_variant_el);
            });

            $('<label/>').addClass('label-inline mt10 mb10')
                .attr('for', 'custom_ptv_btn_' + key)
                .text('Select All Product Type Variants')
                .appendTo(row);

            let product_type_variant_el = $('<div/>').appendTo(row);
            $('<div />').addClass('mt20 display--inline_block')
                .text('Product Type Variant: ').width('150px')
                .appendTo(product_type_variant_el);
            let product_type_variant_selector = $('<select />')
                .attr('name', 'conditions[' + key +'][product_type_variant_ids][]')
                .attr('multiple', 'multiple')
                .prependTo(
                    $('<div />').addClass('select2 product-type-variant-select ml10 display--inline_block')
                        .css({
                            'width': '690px',
                            'vertical-align': 'middle'
                        })
                        .appendTo(product_type_variant_el)
                ).select2({
                    width: '100%'
                });

            if (is_customize === 0) {
                switch_btn.trigger('click');
            }
            __initCustomProductTypeVariant(switch_btn, product_type_variant_el);

            __initProductTypeVariantOpts(
                json.product_type_variants,
                product_type_selector,
                product_type_variant_selector,
                is_customize,
                product_type_variant_ids
            );

            $('<br />').appendTo(row);
            $('<div />').text('Base Cost Configs: ').width('120px').appendTo(row);
            let base_cost_configs_el = $('<div />').addClass('mt5 ml10').appendTo(row);
            $('<div />').addClass('display--inline_block').text('Quantity: ').width('220px').appendTo(base_cost_configs_el);
            $('<div />').addClass('display--inline_block ml5').text('Price ($): ').width('220px').appendTo(base_cost_configs_el);

            $('<div/>').addClass('btn btn-small btn-primary mb10 ml10')
                .text('Add new base cost')
                .appendTo(row)
                .click(function () {
                    __addBaseCost(key, base_cost_configs_el);
                });

            if (base_cost_configs) {
                $.each(base_cost_configs, function (base_cost_quantity, base_cost_value) {
                    __addBaseCost(key, base_cost_configs_el, base_cost_quantity, base_cost_value);
                });
            }

            if (prices && (prices.length === 0 || typeof prices.length === 'undefined')) {
                __addBaseCost(key, base_cost_configs_el);
            }

            $('<br />').appendTo(row);
            $('<div />').addClass('mt20 display--inline_block').text('Start When: ').width('150px').appendTo(row);
            let condition_start_selector = $('<select />')
                .attr('name', 'conditions[' + key +'][condition_start][option]')
                .prependTo(
                    $('<div />').addClass('styled-select ml10 display--inline_block')
                        .css({
                            'width': '40%',
                            'vertical-align': 'middle'
                        })
                        .append($('<ins />'))
                        .appendTo(row)
                );

            __initOptionCondition(condition_start_selector, condition_start);

            $('<div />').addClass('ml50 display--inline_block').text('Reach: ').width('64px').appendTo(row);

            $('<input />').attr({
                    'type': 'text',
                    'name': 'conditions[' + key +'][condition_start][reach]',
                    'value': condition_start.reach ? condition_start.reach : 0
                })
                .addClass('styled-input')
                .width('220px')
                .appendTo($('<div />').addClass('styled-input-wrap ml10 display--inline_block').appendTo(row))
                .keydown(function (e) {
                    if (['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '.', 'x', 'c', 'v', 'arrowleft', 'arrowright', 'backspace', 'shift', 'delete', 'f5', 'tab'].indexOf(e.key.toLowerCase()) < 0 || e.shiftKey) {
                        e.preventDefault();
                    }
                });

            $('<div />').addClass('mt20 display--inline_block').text('End When: ').width('150px').appendTo(row);
            let condition_end_selector = $('<select />')
                .attr('name', 'conditions[' + key +'][condition_end][option]')
                .prependTo(
                    $('<div />').addClass('styled-select ml10 display--inline_block')
                        .css({
                            'width': '40%',
                            'vertical-align': 'middle'
                        })
                        .append($('<ins />'))
                        .appendTo(row)
                );

            __initOptionCondition(condition_end_selector, condition_end);

            $('<div />').addClass('ml50 display--inline_block').text('Reach: ').width('64px').appendTo(row);
            $('<input />').attr({
                    'type': 'text',
                    'name': 'conditions[' + key +'][condition_end][reach]',
                    'value': condition_end.reach ? condition_end.reach : 0
                })
                .addClass('styled-input')
                .width('220px')
                .appendTo($('<div />').addClass('styled-input-wrap ml10 display--inline_block').appendTo(row))
                .keydown(function (e) {
                    if (['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '.', 'x', 'c', 'v', 'arrowleft', 'arrowright', 'backspace', 'shift', 'delete', 'f5', 'tab'].indexOf(e.key.toLowerCase()) < 0 || e.shiftKey) {
                        e.preventDefault();
                    }
                });

            $('<div />').addClass('mt10').text('Price Range: ').width('85px').appendTo(row);
            let price_range = $('<div />').addClass('display--inline_block').appendTo(row);

            $('<br/>').appendTo(row);
            $('<div/>').addClass('btn btn-small btn-primary')
                .text('Add new price range')
                .appendTo(row)
                .click(function () {
                    __addPriceRange(key, price_range);
                });

            if (prices) {
                $.each(prices, function (price_key, value) {
                    __addPriceRange(key, price_range, value);
                });
            }

            if (prices && (prices.length === 0 || typeof prices.length === 'undefined')) {
                __addPriceRange(key, price_range);
                __addPriceRange(key, price_range);
            }

            $('<div />').addClass('fright pt20')
                .css({
                    'font-weight': 'bold',
                    'color': '#EB5757',
                    'cursor': 'pointer'
                })
                .text('Remove')
                .appendTo(row)
                .click(function () {
                    row.remove();
                });
        }

        function __addPriceRange(condition_key, price_range, price) {
            let input_wrap = $('<div />').addClass('styled-input-wrap')
                .appendTo(price_range)
                .append($('<div />').addClass('styled-input-icon').append($('<div />').text('$')));

            let input = $('<input />').attr({
                    'type': 'text',
                    'name': 'conditions[' + condition_key +'][price_range][]',
                    'value': price ? price : 0
                })
                .addClass('styled-input mt5 mb5')
                .width('220px')
                .appendTo(input_wrap)
                .keydown(function (e) {
                    if (['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '.', 'x', 'c', 'v', 'arrowleft', 'arrowright', 'backspace', 'shift', 'delete', 'f5', 'tab'].indexOf(e.key.toLowerCase()) < 0 || e.shiftKey) {
                        e.preventDefault();
                    }
                });

            $('<div />').append($.renderIcon('trash-alt-regular'))
                .addClass('btn btn-small btn-icon ml5')
                .insertAfter(input)
                .click(function () {
                    input_wrap.remove();
                });
        }

        function __addBaseCost(condition_key, base_cost_configs_el, base_cost_quantity, base_cost_value) {
            let base_cost_config = $('<div />').appendTo(base_cost_configs_el);
            let base_cost_config_key = $.makeUniqid();

            let qty_input = $('<input />').attr({
                'type': 'number',
                'name': 'conditions[' + condition_key + '][base_cost_configs][' + base_cost_config_key + '][quantity]',
                'value': base_cost_quantity ? base_cost_quantity : 1
            })
                .addClass('styled-input mt5 mb5 display--inline_block')
                .width('220px')
                .appendTo(base_cost_config)
                .keydown(function (e) {
                    if (['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'arrowleft', 'arrowright', 'backspace', 'shift', 'delete', 'f5', 'tab'].indexOf(e.key.toLowerCase()) < 0 || e.shiftKey) {
                        e.preventDefault();
                    }
                });

            let base_cost_input = $('<input />').attr({
                'type': 'text',
                'name': 'conditions[' + condition_key + '][base_cost_configs][' + base_cost_config_key + '][value]',
                'value': base_cost_value ? base_cost_value : 0
            })
            .addClass('styled-input ml5 mt5 mb5')
            .width('220px')
            .appendTo(base_cost_config)
            .keydown(function (e) {
                if (['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '.', 'x', 'c', 'v', 'arrowleft', 'arrowright', 'backspace', 'shift', 'delete', 'f5', 'tab'].indexOf(e.key.toLowerCase()) < 0 || e.shiftKey) {
                    e.preventDefault();
                }
            });

            $('<div />').append($.renderIcon('trash-alt-regular'))
                .addClass('btn btn-small btn-icon ml5')
                .appendTo(base_cost_config)
                .click(function () {
                    base_cost_config.remove();
                });
        }

        function __initCustomProductTypeVariant(switch_btn, element) {
            if (switch_btn.is(":checked")) {
                element.children().children().prop("disabled", true);
            } else {
                element.children().children().prop("disabled", false);
            }
        }

        function __initProductTypeVariantOpts(
            product_type_variants,
            product_type_selector,
            product_type_variant_selector,
            is_customize,
            product_type_variant_ids
        ) {
            product_type_variant_selector.children().remove();
            $.each(product_type_variants, function (key, value) {
                let product_type_variant_id = value.id;
                let product_variant_title = value.title;
                let product_type_id = value.product_type_id;

                if (product_type_selector.val() === null ||
                    product_type_selector.val().includes(product_type_id.toString())
                ) {
                    let option = $('<option />').attr('value', product_type_variant_id)
                        .attr('data-product-type-id', product_type_id)
                        .addClass('product-type-variant-opt')
                        .text(product_variant_title)
                        .appendTo(product_type_variant_selector);

                    $.each(product_type_variant_ids, function (k, val) {
                        if (val && is_customize === 1 && parseInt(val) === product_type_variant_id) {
                            option.attr('selected', 'selected');
                        }
                    });
                }
            });
        }

        $('<div/>').addClass('btn btn-primary mt10')
            .text('Add new config')
            .appendTo(container)
            .click(function () {
                __addRow();
            });

        if (json.data_conditions) {
            $.each(json.data_conditions, function (key, value) {
                __addRow(key, value);
            });
        }
    };

    window.initPriceRangeInput = function () {
        function validateInputPrice()  {
            const input = this;
            var ponto = input.value.split('.').length;
            var slash = input.value.split('-').length;
            if (ponto > 2) input.value=input.value.substr(0,(input.value.length)-1);
            if(slash > 2) input.value=input.value.substr(0,(input.value.length)-1);
    
            input.value = input.value.replace(/[^0-9.-]/,'');
            if (ponto == 2) input.value=input.value.substr(0,(input.value.indexOf('.')+3));
    
            if(input.value == '.') input.value = "";
            if(input.value.indexOf('-')) input.value.substr(0,(input.value.length)-1);
        }
        $(this).find('input').on('input', validateInputPrice);
        $(this).find('svg').click(function(){
            $(this).parent().remove();
        });
        const renderPriceRangeInput = container => {
            const wrapper = $('<div>').addClass('d-flex items-center pb20').insertBefore(container)
            $('<input>').addClass('styled-input mr-2').on('input', validateInputPrice).appendTo(wrapper);
            $($.renderIcon('trash-alt-regular')).click(function(){
                $(this).parent().remove();
            }).appendTo(wrapper);
        }
        const addPriceRangeButton = $('<div>').addClass('btn mr-2').text('Add new price range').appendTo(this);
        if ($('.submit-form-product-price-ab').attr('data-id') != 0 && $(this).attr('data-config-status') == 1) {
            const editRangeButton = $('<div>').addClass('btn').css('background', 'red').text('Stop').appendTo(this);
            editRangeButton.click(function(){
                if ($('select#condition_type').val() == 0) {
                    $.ajax({
                        type: "POST",
                        url: $.base_url + '/autoAb/backend_productPrice/stopAbTestPrice/hash/' + OSC_HASH,
                        data: {
                            config_id: $('.submit-form-product-price-ab').attr('data-id'),
                            stop_ab_options: 1,
                        },
                        success: function (response) {
                            if (response.result !== 'OK') {
                                alert(response.message);
                                return;
                            }

                            alert('The AB test has been stopped successfully ');

                            window.location.reload();
                        }
                    });
                } else stopAbTestPriceModal($(this));
            })
        }
        addPriceRangeButton.click(function(){
            renderPriceRangeInput($(this));
        })
    }

    window.initStopABTestPrice = function () {
        $(this).click(function() {
            if ($(this).attr('data-condition-type') == 0) {
                $.ajax({
                    type: "POST",
                    url: $.base_url + '/autoAb/backend_productPrice/stopAbTestPrice/hash/' + OSC_HASH,
                    data: {
                        config_id: $(this).attr('data-config-id'),
                        stop_ab_options: 1,
                    },
                    success: function (response) {
                        if (response.result !== 'OK') {
                            alert(response.message);
                            return;
                        }

                        alert('The AB test has been stopped successfully ');

                        window.location.reload();
                    }
                });
            } else stopAbTestPriceModal($(this));
        });
    }

    const stopAbOptions = [{
        id: 'donothing',
        value: 1,
        title: 'Do nothing',
    },
    {
        id: 'bestresult',
        value: 2,
        title: 'Apply the best result',
    },
    {
        id: 'manually',
        value: 3,
        title: 'Choose price range manually',
    }]

    window.stopAbTestPriceModal = function(element) {
        $.unwrapContent('stopAbTestPriceModalModalFrm');
        var modal = $('<div />').addClass('osc-modal').width(600);
        var header = $('<header />').appendTo(modal);


        $('<div />').addClass('title').css({
            'font-size': '20px',
            'color': '#3a3a3a',
        }).html('Stop AB Test Price').appendTo(header);

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('stopAbTestPriceModalModalFrm');
        }).appendTo(header);
        const content = $('<div>').css('padding', '16px 20px').appendTo(modal);
        const handleOptionChange = value => {
            if (value == 3) renderManuallyOptions();
            else $('.manually-container').empty();
        }
        const renderRadio = option => {
            const input_container = $('<div>').addClass('flex items-center').appendTo(content)
            $('<input>').attr({
                type: 'radio',
                name: 'stop_ab_options',
                value: option.value,
                id: option.id,
            }).appendTo(input_container).change(() => handleOptionChange(option.value));
            $('<label>').attr('for', option.id).text(option.title).appendTo(input_container);
        }

        stopAbOptions.forEach(option => renderRadio(option));
        const manually_container = $('<div>').addClass('manually-container').appendTo(content);
        function renderManuallyOptions() {
            manually_container.empty();
            let price_range = [];
            $('.price-range-input-container').find('input').each(function(){
                price_range.push($(this).val());
            });

            /* Show options in list screen */
            if (element.hasAttr('data-price-range')) {
                price_range = JSON.parse(element.attr('data-price-range'));
            }

            price_range.filter(p => p).forEach(p => {
                const input_container = $('<div>').addClass('flex items-center').css('margin-left', '16px').appendTo(manually_container)
                $('<input>').attr({
                    type: 'radio',
                    name: 'manually_options',
                    value: p,
                    id: `manually_options_${p}`,
                }).appendTo(input_container);
                $('<label>').attr('for', `manually_options_${p}`).text(p).appendTo(input_container);
            })
        }

        const submitButton = $('<div>').addClass('btn btn-primary').css({
            'float': 'right',
            'margin-bottom': '16px',
        }).text('Submit').appendTo(content).click(function(){
            const data = {
                config_id: $('.submit-form-product-price-ab').attr('data-id') || element.attr('data-config-id'),
                stop_ab_options: $('input[name=stop_ab_options]:checked').val(),
                manually_options: $('input[name=manually_options]:checked').val(),
            }

            if ($('input[name=stop_ab_options]:checked').val() === undefined) {
                alert('option must be selected');
                return;
            }

            if ($('input[name=stop_ab_options]:checked').val() == 3) {
                if ($('input[name=manually_options]:checked').val() === undefined) {
                    alert('A price range must be selected');
                    return;
                }
            }

            $.ajax({
                type: "POST",
                url: $.base_url + '/autoAb/backend_productPrice/stopAbTestPrice/hash/' + OSC_HASH,
                data,
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    alert('The AB test has been stopped successfully ');

                    window.location.reload();
                }
            });
        });
        $.wrapContent(modal, {key: 'stopAbTestPriceModalModalFrm'});

        modal.moveToCenter().css('top', '100px');
    }

    window.initProductTypeVariantModal = function() {
        const renderRow = (productTypeName, country, quantity, baseCost, container, isBold) => {
            const row = $('<div>').addClass(`d-flex ${isBold && 'pb20 pt20'} row`).appendTo(container);
            $('<div>').append($(isBold ? '<strong>' : '<p>').text(productTypeName)).addClass('w-50').appendTo(row);
            $('<div>').append($(isBold ? '<strong>' : '<p>').text(country)).addClass('w-20 text-center').appendTo(row);
            $('<div>').append($(isBold ? '<strong>' : '<p>').text(quantity)).addClass('w-20 text-center').appendTo(row);
            $('<div>').append($(isBold ? '<strong>' : '<p>').text(baseCost)).addClass('w-10 text-center').appendTo(row);
        }
        $(this).click(function() {
            let variant_data = [];
            $('#select-product-type-variants-component')[0].dataCallback().forEach(p => {
                let variants = [];
                p.variants.forEach(v => {
                    if(v.check) variants.push(v.id)
                })
                variant_data.push({
                    product_type_id: p.id,
                    variants,
                })
            })
            $.ajax({
                type: 'GET',
                url: $.base_url + '/autoAb/backend_productPrice/getBaseCostConfig/hash/' + OSC_HASH,
                data: {
                    location_data: $('select#input-group_country').val(),
                    variant_data,
                },
                success: function (response) {
                    $.unwrapContent('productTypeVariantModalFrm');
                    var modal = $('<div />').addClass('osc-modal product-type-variants-modal').width(1200);
                    
                    const searchDiv = $('<div>').addClass('d-flex search-container').appendTo(modal);
                    $($.renderIcon('search')).appendTo(searchDiv);
                    const input = $('<input>').attr('placeholder', 'Search').appendTo(searchDiv);
                    
                    renderRow('Product type', 'Country', 'Quantity', 'Base cost ($)', modal, true);
                    const list = $('<div>').addClass('product-type-variants-modal__list').appendTo(modal);
                    response.data.data.map(p => renderRow(p.title, p.country_name, p.quantity, p.base_cost, list));
                    input.on('input', debounce(function(e){
                        const search = response.data.data.filter(p => p.title.toLowerCase().includes(e.target.value.trim().toLowerCase()));
                        list.empty();
                        search.map(p => renderRow(p.title, p.country_name, p.quantity, p.base_cost, list));
                    }, 500))
                    $.wrapContent(modal, {key: 'productTypeVariantModalFrm'});
            
                    modal.moveToCenter().css('top', '100px');
                }
            })
        })
    }

    const editVariantSuccessModal = () => {
        $.unwrapContent('editVariantSuccessModalFrm');
        var modal = $('<div />').addClass('osc-modal edit-variants-success-modal').width(320);
        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('editVariantSuccessModalFrm');
        }).appendTo(header);

        $($.renderIcon('edit-success')).appendTo(modal);
        $('<p>').text('Your update has been saved!').appendTo(modal);
        $.wrapContent(modal, {key: 'editVariantSuccessModalFrm', close_callback: function () {
            window.location.href = $.base_url + '/autoAb/backend_productPrice/list/hash/' + OSC_HASH;
        }});
        modal.moveToCenter().css('top', '100px');
    }

    window.initSubmitFormProductPriceAbtest = function () {
        $(this).click(function() {
            let submitBtn = $(this);
            submitBtn.attr('disabled', 'disabled');
            const id = $(this).attr('data-id');
            const data_semitest = $(this).attr('data-semitest');
            let is_semitest_config = false;
            if (typeof data_semitest !== 'undefined' && data_semitest !== false) {
                is_semitest_config = true;
            }

            const title = $('input#campain_title').val();
            const location_data = $('select#input-group_country').val();
            const fee = $('input#input-fees').val();
            const condition_type = $('select#condition_type').val();
            const begin_at = $('input#begin_at').val();
            const finish_at = $('input#finish_at').val();
            const fixed_product_ids = $('select#input-product-ids').val();
            let price_range = [];
            $('.price-range-input-container').find('input').each(function(){
                price_range.push($(this).val());
            })
            const variant_data = is_semitest_config ? [] : $('#select-product-type-variants-component')[0].dataCallback().map(p => {
                if (p.selectAll) return {
                    product_type_id: p.id,
                    variants: [],
                }
                return {
                    product_type_id: p.id,
                    variants: p.variants.map(v => v.id)
                }
            });
            const data = {
                id,
                title,
                location_data,
                fee,
                condition_type,
                begin_at,
                finish_at,
                price_range: price_range.filter(p => p),
                variant_data,
                fixed_product_ids,
                is_semitest_config: is_semitest_config ? 1 : 0,
            }
            $.ajax({
                type: 'POST',
                url: $.base_url + '/autoAb/backend_productPrice/post/hash/' + OSC_HASH,
                data,
                success: function (response) {
                    if (response.result == 'OK') {
                        editVariantSuccessModal();
                    } else {
                        alert(response.message.replace(/<br\s*[\/]?>/gi, "\n"));
                        submitBtn.removeAttr('disabled');
                    }
                }
            });
        })
    }

    function debounce(callback, delay) {
        var timeout
        return function() {
          var args = arguments
          clearTimeout(timeout)
          timeout = setTimeout(function() {
            callback.apply(this, args)
          }.bind(this), delay)
        }
    }
})(jQuery);
