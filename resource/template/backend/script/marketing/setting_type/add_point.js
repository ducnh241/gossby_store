(function ($) {
    'use strict';
    window.marketingAddPoint = function () {
        let container = $(this),
            frm_name = container.attr('data-name'),
            json = fetchJSONTag(container, 'add_marketing_point'),
            box = $('<div />');

        function getProductTypeStatus() {
            let allRealProductSelected = false;
            let commonTypeSelected = false;
            let selectedState = {}

            container.find('.js-marketing-group').each(function() {
                const groupUkey = $(this).data('ukey');

                const $selector = $(this).find('.js-product-type-selector');

                $selector.find('option:selected').each(function() {
                    const value = $(this).attr('value');

                    if (value === 'ALL_REAL_PRODUCTS') allRealProductSelected = true;

                    selectedState[value] = groupUkey;
                });
            });

            const tmp = { ...selectedState };

            delete tmp['ALL_REAL_PRODUCTS'];
            delete tmp['ALL_BETA_PRODUCTS'];

            if (Object.keys(tmp).length) commonTypeSelected = true;

            return {
                allRealProductSelected,
                commonTypeSelected,
                selectedState,
            }
        }

        function getCollectionStatus() {
            const selected = {}

            container.find('.js-marketing-group').each(function() {
                const groupUkey = $(this).data('ukey');

                const $selector = $(this).find('.js-collection-selector');

                $selector.find('option:selected').each(function() {
                    const value = $(this).attr('value');

                    selected[value] = groupUkey;
                });
            });

            return selected;
        }

        function updateSelector() {
            container.find('.js-product-type-selector, .js-collection-selector').trigger('update');
        }

        function __addRow(ukey, config = null) {
            box.prependTo(container);
            let key = $.makeUniqid();
            if (!ukey) ukey = key;

            $("<div />").addClass('frm-line e20').appendTo(box);
            let row = $(`<div class="js-marketing-group block box-item" data-ukey="${ukey}" data-content="1" style="padding: 10px; margin: 5px 0" />`).appendTo(box);

            $('<div />').addClass('title').text('Name: ').appendTo(row);
            $('<input />').attr('type', 'hidden').attr('name', frm_name + '[' + ukey + '][ukey]').attr({'value': ukey}).addClass('styled-input').appendTo(row);
            $('<input />').attr('type', 'text').attr('name', frm_name + '[' + ukey + '][name]').attr({'value': config ? config.name : '', 'maxlength': 150, 'required': 'required'}).addClass('styled-input').appendTo(row);
            $('<div />').addClass('title mt15').text('Product types: ').appendTo(row);

            let product_type_selector = $(`<select
                class="js-product-type-selector"
                name="${frm_name + '[' + ukey + '][product_type][]'}"
                multiple="multiple"
            />`).appendTo(row);

            product_type_selector.customProductType({
                list: json.product_types,
                defaultValues: config?.product_type,
                getProductTypeStatus,
            });

            $('<div />').addClass('title mt15').text('Collection: ').appendTo(row);

            let collection_selector = $('<select />')
                .addClass('addCollection js-collection-selector')
                .attr({'name': frm_name + '[' + ukey + '][collection][]', 'multiple': 'multiple'})
                .prependTo($('<div />').addClass('styled-select styled-select--multiple').appendTo($('<div />').appendTo(row)));

            collection_selector
                .on('change', function() {
                    container.find('.js-collection-selector').not($(this)).trigger('update');
                })
                .on('update', function() {
                    const collectionStatus = getCollectionStatus();

                    $(this).empty();

                    Object.entries(json.collections).forEach(([key, value]) => {
                        if (collectionStatus[key] && collectionStatus[key] !== ukey) return;

                        const isSelected = collectionStatus[key] === ukey;

                        collection_selector.append(`
                            <option value="${key}" ${isSelected ? 'selected' : ''}>${value}</option>
                        `);
                    });

                    $(this).select2({
                        width: '100%',
                    })
                });

            Object.entries(json.collections).forEach(([key, value]) => {
                const isSelected = config?.collection?.includes(Number(key));

                collection_selector.append(`
                    <option value="${key}" ${isSelected ? 'selected' : ''}>${value}</option>
                `);
            });

            $('<div />').addClass('title mt15').text('Products: ').appendTo(row);
            let product_selector = $('<div />')
                .addClass('addproduct mt20 js-select-product')
                .attr({'data-browse-url': '/catalog/backend_product/browse'}).appendTo(row);

            $(product_selector).osc_itemSelector({
                multi_select: true,
                browse_url: PRODUCT_SELECTOR_BROWSE_URL,
                placeholder_text: 'Search for products',
                no_selected_text: 'No products selected',
                input_name: frm_name + '[' + ukey + '][product_ids][]',
                data: config?.products ?? [],
                alway_render_seleted_item: true,
                extend_params: () => {
                    const except = [];

                    container.find('.js-marketing-group').each(function() {
                        const dataUkey = $(this).data('ukey');

                        if (dataUkey === ukey) return;

                        $(this).find('.js-select-product .catalog-selected-item').each(function() {
                            const productId = $(this).data('item');

                            if (!productId) return;

                            except.push(productId);
                        });
                    });

                    return {
                        except,
                    };
                },
            });

            var column_data = $('<div style="padding: 15px 0 5px"/>').appendTo(row);
            var table_data = $('<table />').appendTo(column_data);

            var row_data = $('<tr />').appendTo(table_data);
            $('<th />').text('Day').appendTo(row_data);
            $('<th />').width('20%').text('Point').appendTo(row_data);
            $('<th />').width('20%').text('Sref(%)').appendTo(row_data);
            $('<th />').width('20%').text('Vendor(%)').appendTo(row_data);
            $('<th />').width('135px').html('Action').appendTo(row_data);

            function __addRowData(key, name, value, sref, vendor) {
                var row = $('<tr />').attr('data-content', 1).appendTo(table_data),
                    dataKey = $.makeUniqid();

                $('<input />')
                    .attr('type', 'number')
                    .attr({
                        'name': frm_name + '[' + key + '][' + dataKey + '][day]',
                        'rel': 'day',
                        'value': name,
                        'min': 1,
                        'step': 1,
                        'required': 'required'
                    })
                    .addClass('styled-input')
                    .appendTo($('<div />'))
                    .appendTo($('<td />').appendTo(row));
                $('<input />')
                    .attr('type', 'number')
                    .attr({
                        'name': frm_name + '[' + key + '][' + dataKey + '][point]',
                        'rel': 'point',
                        'value': value,
                        'min': 0,
                        'step': 0.01,
                        'placeholder': '0.00',
                        'required': 'required'
                    })
                    .addClass('styled-input')
                    .appendTo($('<div />'))
                    .appendTo($('<td />').appendTo(row));
                $('<input />')
                    .attr('type', 'number')
                    .attr({
                        'name': frm_name + '[' + key + '][' + dataKey + '][sref]',
                        'rel': 'sref',
                        'value': sref,
                        'min': 0,
                        'max': 100,
                        'step': 1,
                        'placeholder': '0',
                        'data-vendor': frm_name + '[' + key + '][' + dataKey + '][vendor]',
                        'required': 'required'
                    })
                    .addClass('styled-input')
                    .appendTo($('<div />'))
                    .appendTo($('<td />').appendTo(row));
                $('<input />')
                    .attr('type', 'number')
                    .attr({
                        'name': frm_name + '[' + key + '][' + dataKey + '][vendor]',
                        'rel': 'vendor',
                        'value': vendor,
                        'min': 0,
                        'max': 100,
                        'step': 1,
                        'placeholder': '0',
                        'data-sref': frm_name + '[' + key + '][' + dataKey + '][sref]',
                        'required': 'required'
                    })
                    .addClass('styled-input')
                    .appendTo($('<div />'))
                    .appendTo($('<td />').appendTo(row));

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

                    if (table_data.find('tr').length < 2) {
                        //table_data.html('').detach();
                    } else {
                        table_data.find('tr[data-content="1"]').trigger('reorder');
                    }
                });

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

                table_data.find('tr[data-content="1"]').trigger('reorder');

                return row;
            }

            if (config && config.value) {
                $.each(config.value, function ($date_key, $date_value) {
                    let $date = $date_value['day'],
                        $point = $date_value['point'],
                        $sref = $date_value['sref'],
                        $vendor = $date_value['vendor'];

                    __addRowData(ukey, $date, $point, $sref, $vendor);
                });
            }

            $('<div />').addClass('btn btn-small btn-secondary-add mr5 mt10 box-sizing-content').text('Add point').appendTo(column_data).click(function () {
                __addRowData(ukey, null, null, null, null);
            });

            $('<div />').text('Delete group').addClass('btn btn-small btn-danger mt10 box-sizing-content').appendTo(column_data).click(function () {
                row.remove();

                if (box.find('.box-item').length < 1) {
                    box.parent().append($('<input />').attr({type: 'hidden', name: frm_name, value: ''}));
                    box.html('');
                } else {
                    box.find('div[data-content="1"]').trigger('reorder');
                }

                updateSelector();
            });

            box.find('div[data-content="1"]').trigger('reorder');

            return row;
        }

        if (json.data) {
            $.each(json.data, function (key, value) {
                __addRow(key, value);
            });
            updateSelector();
        }

        $(document).on('change', '.marketingDefaultPoint', function () {
            $(".marketingDefaultPoint").not(this).prop('checked', false);
        });

        $(document).on('keyup', 'input[rel="point"]', function (e) {
            if (isNaN($(this).val()) || $(this).val() <= 0) {
                this.setCustomValidity('The number must greater than to 0.');
            } else {
                this.setCustomValidity('');
            }
        }).on('change', 'input[rel="point"]', function (e) {
            $(this).val(parseFloat(parseFloat($(this).val()).toFixed(2)));
            if (isNaN($(this).val()) || $(this).val() <= 0) {
                this.setCustomValidity('The number must greater than to 0.');
            } else {
                this.setCustomValidity('');
            }
        });

        $(document).on('change', 'input[rel="sref"]', function (e) {
            let _rel_vendor = $("input[name='" + $(this).attr('data-vendor') + "']");
            $(this).val(parseInt($(this).val()));
            _rel_vendor.val(100 - parseInt($(this).val()));
        });

        $(document).on('change', 'input[rel="vendor"]', function (e) {
            let _rel_sref = $("input[name='" + $(this).attr('data-sref') + "']");
            $(this).val(parseInt($(this).val()));
            _rel_sref.val(100 - parseInt($(this).val()))
        });

        $(document).on('change', 'input[rel="day"]', function (e) {
            $(this).val(parseInt($(this).val()));
        });

        $('<div style="float: right" />').addClass('btn btn-primary mt10').html($.renderIcon('icon-plus', 'mr5')).append('Add new group').appendTo(container).click(function () {
            container.find('input[name="' + frm_name + '"]').remove();
            __addRow();
            updateSelector();
        });
    };

    $.fn.customProductType = function(options) {
        const isMacOS = navigator.platform.indexOf('Mac') > -1;

        return this.each(function() {
            const list = options.list && typeof options.list === 'object'
                ? options.list
                : {};
            const defaultValues = options.defaultValues && Array.isArray(options.defaultValues)
                ? options.defaultValues
                : [];

            const selected = Object.keys(list).reduce((carry, key) => {
                carry[key] = !!defaultValues.includes(key);
                return carry;
            }, {});

            const $selector = $(this);
            const $container = $('<div class="m-product-type" />').insertBefore($selector);

            $selector.addClass('m-product-type__select').appendTo($container);

            const $box = $('<div class="m-product-type__box" />').appendTo($container);
            const $input = $('<input type="text" class="m-product-type__input" size="1" />').appendTo($box);
            const $dropdown = $('<div class="m-product-type__dropdown" />').appendTo($container);

            $container.append($selector);

            Object.entries(list).forEach(([key, text]) => {
                $(`<option value="${key} ${selected[key] ? 'selected' : ''}">${text}</option>`).appendTo($selector);
            });

            function toggleSelectOption(key) {
                selected[key] = !selected[key];

                $selector.val(Object.keys(selected).filter((key) => selected[key]));

                renderBoxOptions();
                renderSelectedOptions();
                renderDropdownOptions();
            }

            function renderBoxOptions() {
                $box.empty();

                Object.entries(list).forEach(([key, text]) => {
                    if (selected[key]) {
                        const $item = $(`
                            <div class="m-product-type__box-item">
                                <div class="m-product-type__box-remove"></div>
                                <div>${text}</div>
                            </div>
                        `);

                        $item
                            .on('click', '.m-product-type__box-remove', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                toggleSelectOption(key);
                                $input.focus();
                                $container.addClass('is-show-dropdown');
                            })
                            .appendTo($box);
                    }
                });

                $input.appendTo($box);
            }

            function renderSelectedOptions() {
                $selector.empty();

                Object.entries(list).forEach(([key, text]) => {
                    $(`<option value="${key}" ${selected[key] ? 'selected' : ''}>${text}</option>`).appendTo($selector);
                });

                $selector.trigger('changer');
            }

            function renderDropdownOptions() {
                let allRealProductSelected = false;
                let commonTypeSelected = false;
                let selectedState = {};

                $dropdown.empty();

                const filterKey = $input.val().toLowerCase();

                const filteredItems = {};

                if (options.getProductTypeStatus && typeof options.getProductTypeStatus === 'function') {
                    const status = options.getProductTypeStatus();

                    allRealProductSelected = status.allRealProductSelected;
                    commonTypeSelected = status.commonTypeSelected;
                    selectedState = status.selectedState;
                }

                Object.entries(list).forEach(([key, text]) => {
                    const lowerText = text.toLowerCase();

                    if (
                        filterKey && !lowerText.includes(filterKey) ||
                        commonTypeSelected && key === 'ALL_REAL_PRODUCTS' ||
                        !selected[key] && selectedState[key] ||
                        allRealProductSelected && key !== 'ALL_REAL_PRODUCTS' && key !== 'ALL_BETA_PRODUCTS' ||
                        allRealProductSelected && key === 'ALL_REAL_PRODUCTS' && !selected['ALL_REAL_PRODUCTS']
                    ) return;

                    filteredItems[key] = text;
                });

                if (Object.keys(filteredItems).length) {
                    Object.entries(filteredItems).forEach(([key, text]) => {
                        const $dropdownItem = $(`<div class="m-product-type__dropdown-item ${selected[key] ? 'is-selected' : ''}">${text}</div>`)
                            .on('click', function(e) {
                                const isCtrlPressed = isMacOS && e.metaKey || !isMacOS && e.ctrlKey;

                                toggleSelectOption(key);

                                if (!isCtrlPressed) {
                                    $container.removeClass('is-show-dropdown');
                                    $container.find('.m-product-type__input').val('').trigger('input').focus();
                                }
                            });

                        $dropdownItem.appendTo($dropdown);
                    });
                } else {
                    $('<div class="m-product-type__dropdown-no-result">No results found</div>').appendTo($dropdown);
                }
            }

            renderBoxOptions();
            renderSelectedOptions();
            renderDropdownOptions();

            $('body').on('click', function() {
                $container.removeClass('is-show-dropdown');
                $container.find('.m-product-type__input').val('').trigger('input');
            });

            $box.on('click', function (e) {
                e.stopPropagation();
                $input.focus();
                $container.addClass('is-show-dropdown');
                renderDropdownOptions();
            });

            $dropdown.on('click', function(e) {
                e.stopPropagation();
            });

            $container.on('input', '.m-product-type__input', function() {
                const value = $(this).val();

                $(this).css({
                    'flex': '1 0 ' + value.length * 3 + 'px',
                });

                renderDropdownOptions();
            });

            return this;
        });
    }
})(jQuery);
