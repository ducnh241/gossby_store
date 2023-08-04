(function ($) {
    'use strict';
    var DATA_TABS = [],
        selected_keys = [],
        _allVariants = [];

    let selectedMockup = [];

    function decodeEntities(encodedString) {
        var textArea = document.createElement('textarea');
        textArea.innerHTML = encodedString;
        return textArea.value;
    }

    function _sortArrayByKey(array, key) {
        return array.sort(function (a, b) {
            var x = a[key];
            var y = b[key];
            return ((x < y) ? -1 : ((x > y) ? 1 : 0));
        });
    }

    function _renderProductTypeItems(container, tab_name, page, amazon_flag) {
        container.html('');
        $.ajax({
            type: 'POST',
            url: $.base_url + '/catalog/backend_campaign/getProductTypes/hash/' + OSC_HASH,
            data: {tab_name: tab_name, page: page, amazon_flag: amazon_flag},
            success: function (response) {
                if (response.result != 'OK') {
                    alert('Error! Please try again.');
                } else {
                    let _data = response.data;
                    $.each(_data, function (key, group) {
                        let _groupContainer = $('<div/>').addClass('group-item').appendTo(container);

                        $('<div />').addClass('group-title').text(key).appendTo(_groupContainer);

                        let _groupContent = $('<div />').addClass('group-content').appendTo(_groupContainer);
                        $.each(group, function (index, item) {
                            let _image = item.image ? item.image : 'catalog/campaign/type/icon/default.png';
                            $('<div />').addClass('item').append($('<div />').addClass('thumb').attr({
                                'data-type': item.ukey,
                                'data-id': item.id
                            }).append($('<img />').attr({src: $.base_url + '/resource/template/core/image/' + _image}))).append($('<div />').addClass('title').text(item.title)).appendTo(_groupContent).click(function () {
                                var key = item.ukey,
                                    idx = selected_keys.indexOf(key);
                                if (idx < 0) {
                                    selected_keys.push(key);
                                } else {
                                    selected_keys.splice(idx, 1);
                                }
                                container.trigger('setSelectedItem');
                            });
                        });
                        $('<div />').addClass('clear').appendTo(_groupContent);

                    });
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert('ERROR [#' + xhr.status + ']: ' + thrownError);
            }
        });
    }

    function _renderProductTypeSelector(callback, default_keys = [], amazon_flag = 'default') {
        DATA_TABS = fetchJSONTag($(document.body), 'product-type-tabs');
        $.unwrapContent('catalogCampaignProductTypeSelector');

        var modal = $('<div />').addClass('osc-modal').width(1220),
            header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html('Choose your product').appendTo($('<div />').addClass('main-group').appendTo(header));
        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('catalogCampaignProductTypeSelector');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        selected_keys = [].concat(default_keys);

        var container = $('<div />').addClass('campaign-product-type-selector').appendTo(modal_body);

        $('<div />').addClass('btn btn-primary btn-small apply-btn').text('Add selected product(s)').appendTo(container).click(function () {
            $.unwrapContent('catalogCampaignProductTypeSelector');
            callback(selected_keys);
        });

        var tab_container = $('<div />').addClass('tab-container').appendTo(container),
            item_container = $('<div />').addClass('item-container').appendTo(container).bind('setSelectedItem', function () {
                item_container.find('.item').each(function () {
                    var item = $(this),
                        key = item.find('.thumb').attr('data-type'),
                        counter = selected_keys.indexOf(key) + 1;
                    item.removeClass('selected').find('.index').remove();

                    if (counter > 0) {
                        item.addClass('selected').append($('<div />').addClass('index').text(counter));
                    }
                });
            });

        var current_tab_idx = -1;
        DATA_TABS.forEach(function (group, tabIdx) {
            var tab = $('<div />').attr({
                'data-tab-idx': tabIdx,
                'data-tab-key': group
            }).addClass('tab').text(group).appendTo(tab_container).click(function () {
                tab_container.find('> .tab').removeClass('activated');
                tab.addClass('activated');
                _renderProductTypeItems(item_container, $(this).data('tab-key'), 1, amazon_flag);
                setTimeout(function () {
                    item_container.trigger('setSelectedItem');
                }, 100);

            });
        });

        $.wrapContent(modal, {key: 'catalogCampaignProductTypeSelector'});
        modal.moveToCenter().css('top', '100px');

        if (current_tab_idx < 0) {
            current_tab_idx = 0;
        }

        $(tab_container.find('> .tab')[current_tab_idx]).trigger('click');
    }

    window.catalogCampaignAddNew = function () {
        const campaign_type = $(this).attr('campaign-type');

        $(this).click(function () {
            _renderProductTypeSelector(function (selected_keys) {
                if (!Array.isArray(selected_keys) || selected_keys.length < 1) {
                    return;
                }

                var frm = $('<form />').attr({
                    action: $.base_url + '/catalog/backend_campaign/post/hash/' + OSC_HASH + '/campaign_type/' + campaign_type,
                    method: 'post'
                }).appendTo(document.body);

                selected_keys.forEach(function (selected_key) {
                    $('<input />').attr({type: 'hidden', name: 'product_type[]', value: selected_key}).appendTo(frm);
                });

                frm.submit();
            },[], campaign_type);
        });
    };

    function _getIntersectionMockupVariantIds(mockups) {
        return mockups
            .map(mockup => mockup.variantIds || {})
            .reduce((carry, mockupVariantIds) => {
                return {
                    ...carry,
                    ...mockupVariantIds,
                }
            }, {});
    }

    function _getIndeterminateMockupVariantIds(productsData, mockups) {
        const intersectionVariantIds = _getIntersectionMockupVariantIds(mockups);
        let variantsindeterminate = [];
        let mockupVariantsChecked = {};

        $.each(productsData, (productTypekey, product) => {
            if (product.selected_all.length === 0) return;
            product.selected_all.forEach((mockupId, i) => {
                const _old = mockupVariantsChecked[mockupId]?mockupVariantsChecked[mockupId]:[];
                mockupVariantsChecked[mockupId] = [..._old, ...Object.keys(product.variants)];
            });
        });

        mockups.forEach((mockup, i) => {
            const itemId = (mockup.fileId || mockup.id).toString();
            if (Object.keys(mockupVariantsChecked).indexOf(itemId) > -1) {
                let difference = mockupVariantsChecked[itemId].filter(x => !Object.keys(intersectionVariantIds).includes(x));

                variantsindeterminate = [...variantsindeterminate, ...difference];
            }
        });

        return variantsindeterminate;
    }

    function _renderMappingMockupVariants(productsData, mockups, onSave) {
        const modalKey = 'catalogMappingMockupVariants';

        $.unwrapContent(modalKey);

        const intersectionVariantIds = _getIntersectionMockupVariantIds(mockups),
            indeterminateVariantIds = _getIndeterminateMockupVariantIds(productsData, mockups);

        const selectedVariants = {...intersectionVariantIds};
        const modal = $('<div />').addClass('osc-modal campaign-mapping-mockup-variants').width(940);
        const modalBody = $('<div />').addClass('body').appendTo(modal);

        $.wrapContent(modal, {key: modalKey});

        modal.moveToCenter().css('top', '100px');

        const container = $('<div />').addClass('container').appendTo(modalBody);
        const selectedMockupContainer = $('<div />').addClass("mockup-col")
            .append($('<div />').text("Selected Mockup").addClass('title')).appendTo(container);
        const selectedMockupList = $('<div />').addClass('mockup-list').appendTo(selectedMockupContainer);

        mockups.forEach((mockup, i) => {
            const itemId = (mockup.fileId || mockup.id);
            const itemElm = $('<div />').attr('data-id', itemId)
                .css('background-image', `url(${mockup.url})`)
                .addClass('mockup')
                .appendTo(selectedMockupList);

            const removeBtn = $('<span />').addClass("remove-item").appendTo(itemElm);
            removeBtn.click((e) => {
                e.stopPropagation();
                itemElm.remove();
                mockups.splice(i, 1);

                if(mockups.length === 0) {
                    selectedMockup = [];
                    $.unwrapContent(modalKey);
                } else {
                    let itemIdStr = itemId.toString(),
                        index = selectedMockup.indexOf(itemIdStr);

                    if (index !== -1) {
                        selectedMockup.splice(index, 1);
                    }
                    _renderMappingMockupVariants(productsData, mockups, onSave);
                }
                $('.mockup-panel').trigger('selectedMockup');
            })
        });

        const selectedVariantCount = $('<span />').addClass('checked-count');
        const variantsCol = $('<div />').addClass("variants-col")
            .append($('<div />').text("Add Product type variants ").addClass('title').append(selectedVariantCount))
            .appendTo(container);
        const __renderSelectedVariantCount = () => {
            let _counter = 0;

            $.each(productsData, function(productTypeKey, product) {
                const _allVariant = Object.keys(product.variants).map(n => Number(n));
                if (Object.keys(product.auto_options).length > 0) {
                    $.each(product.auto_options, (optionKey, optionData) => {
                        let _includeVariant = false;
                        optionData.variants.forEach((variantId, index) => {
                            if (_allVariant.indexOf(variantId) > -1) {
                                _includeVariant = true;
                                return;
                            }
                        });
                        if (_includeVariant === true) {
                            let checked = true;
                            const _selectedVariant = Object.keys(selectedVariants).map(n => Number(n));
                            optionData.variants.forEach((variantId, index) => {
                                if (_selectedVariant.indexOf(variantId) < 0 ) {
                                    checked = false;
                                    return;
                                }
                            });
                            if (checked) _counter++;
                        }
                    });
                } else {
                    $.each(product.variants, (variantId, variant) => {
                        const defaultChecked = !!selectedVariants[variantId];
                        if (defaultChecked) _counter++;
                    });
                }

            });

            //selectedVariantCount.html(`(${Object.keys(selectedVariants).length})`);
            selectedVariantCount.html('(' + _counter + ')');
        }
        __renderSelectedVariantCount();

        const onChangeCheckbox = function (e) {
            const checked = $(this).prop("checked"),
                container = $(this).parent();

            container.find('input[type="checkbox"]').prop({
                indeterminate: false,
                checked: checked,
            });

            // $(container).trigger('update', checked);

            function checkSiblings(el) {
                var parent = el.parent().parent(),
                    all = true;

                el.siblings().each(function () {
                    let returnValue = all = ($(this).children('input[type="checkbox"]').prop("checked") === checked);
                    return returnValue;
                });

                if (all && checked) {
                    parent.children('input[type="checkbox"]').prop({
                        indeterminate: false,
                        checked: checked,
                    }).trigger('change');

                    checkSiblings(parent);
                } else if (all && !checked) {
                    parent
                        .children('input[type="checkbox"]')
                        .prop("checked", checked);
                    parent
                        .children('input[type="checkbox"]')
                        .prop(
                            "indeterminate",
                            parent.find('input[type="checkbox"]:checked').length > 0
                        );
                    checkSiblings(parent);
                } else {
                    el.parents("li").children('input[type="checkbox"]').prop({
                        indeterminate: true,
                        checked: false,
                    });
                }
            }

            checkSiblings(container);
        };
        const __renderCheckbox = (name, defaultChecked, variantId) => {
            const checkbox = $('<input />').attr({
                type: 'checkbox',
                id: "variant_" + variantId,
                'data-variant': variantId,
                name
            }).addClass('custom-checkbox');

            checkbox.prop("checked", defaultChecked);
            checkbox.change(onChangeCheckbox);

            return checkbox;
        }
        const __renderVariantsByProductType = (product) => {
            const hasAutoOption = (Object.keys(product.auto_options).length > 0);
            const checkboxProductType = __renderCheckbox('product-type', false, product.key);

            let countAutoOptions = 0;

            checkboxProductType.change(function (){
                const productTypeKey = $(this).attr('data-variant'),
                    _parent = $(this).parent(),
                    _isChecked = this.checked;

                mockups.forEach((mockup, i) => {
                    const itemId = (mockup.fileId || mockup.id);
                    if (_isChecked) {
                        if (productsData[productTypeKey].selected_all.indexOf(itemId) < 0) {
                            productsData[productTypeKey].selected_all.push(itemId);
                        }
                    } else {
                        if (productsData[productTypeKey].selected_all.indexOf(itemId) >= 0) {
                            const _index = productsData[productTypeKey].selected_all.indexOf(itemId);
                            productsData[productTypeKey].selected_all.splice(_index, 1);
                        }
                    }

                    let _hasIncluded = false;
                    $.each(productsData, (productTypeKey, product) => {
                        const _index = product.selected_all.indexOf(itemId);
                        if (_index >  -1) {
                            _hasIncluded = true;
                            return;
                        }
                    });

                    if (_hasIncluded) {
                        selectedMockupList.find('.remove-item').hide();
                    } else {
                        selectedMockupList.find('.remove-item').show();
                    }

                });

                _parent.find('.toggle').trigger("click");

            });

            const wrapper = $('<li />').addClass('variants-col-product-type').append(checkboxProductType).attr("title", product.product_type_title?.toLowerCase());
            const label = $('<label />').addClass("product-type-title").text(product.product_type_title).attr('for', 'product-type').appendTo(wrapper);
            const countElm = $('<span />').appendTo(label);

            const ul = $('<ul />').appendTo(wrapper);

            let childLength = (hasAutoOption)?countAutoOptions:Object.keys(product.variants).length;
            let checkedLength = 0;

            const renderCount = () => {
                countElm.html(` (<span class="checked-count">${checkedLength}</span>/${childLength})`)
            }

            function onChangeVariant() {
                const checked = $(this).prop("checked");
                const variantId = Number(this.getAttribute("data-variant"));

                if (checked) {
                    if (!hasAutoOption) {
                        checkedLength++;
                    }
                    selectedVariants[variantId] = 1;
                } else {
                    if (!hasAutoOption) {
                        checkedLength--;
                    }
                    delete selectedVariants[variantId];
                }
                renderCount();
                __renderSelectedVariantCount();
            }

            function onChangeAutoCheckbox() {
                const checked = $(this).prop("checked"),
                    optionKey = this.getAttribute("data-variant");
                product.auto_options[optionKey].variants.forEach((variantId, index) => {
                    ul.find("#variant_" + variantId).prop("checked", checked).trigger('change');
                });

                if (checked) {
                    checkedLength++;
                } else {
                    checkedLength--;
                }
                renderCount();
                __renderSelectedVariantCount();

            }

            $.each(product.variants, (variantId, variant) => {
                const defaultChecked = !!selectedVariants[variantId];
                if (defaultChecked && !hasAutoOption) checkedLength++;

                const checkbox = __renderCheckbox(variantId, defaultChecked, variantId);
                $('<li />').attr("title", variant.title?.toLowerCase()).append(checkbox)
                    .append($('<label />').text(variant.title).attr('for', "variant_" + variantId))
                    .appendTo(ul);
                checkbox.bind('change', onChangeVariant);

            });

            /**
             * Auto select options
             */
            if (hasAutoOption) {
                ul.find('li').hide();

                const _allVariant = Object.keys(product.variants).map(n => Number(n));

                $.each(product.auto_options, (optionKey, optionData) => {
                    let _includeVariant = false;
                    optionData.variants.forEach((variantId, index) => {
                        if (_allVariant.indexOf(variantId) > -1) {
                            _includeVariant = true;
                            return;
                        }
                    });
                    if (_includeVariant === true) {
                        countAutoOptions++;

                        const checkbox = $('<input />').attr({
                            type: 'checkbox',
                            id: "auto_option_" + optionKey,
                            'data-variant': optionKey,
                            name: optionKey
                        }).addClass('custom-checkbox');

                        let defaultChecked = true;
                        const _selectedVariant = Object.keys(selectedVariants).map(n => Number(n));
                        optionData.variants.forEach((variantId, index) => {
                            if (_selectedVariant.indexOf(variantId) < 0 ) {
                                defaultChecked = false;
                                return;
                            }
                        });

                        if(defaultChecked) checkedLength++;

                        checkbox.prop("checked", defaultChecked);
                        checkbox.change(onChangeAutoCheckbox);
                        $('<li />').attr("title", optionData.title)
                            .append(checkbox)
                            .append($('<label />').text(optionData.title).attr('for', "auto_option_" + optionKey))
                            .appendTo(ul);
                    }
                });
                childLength = countAutoOptions;
            }

            if (checkedLength === childLength) checkboxProductType.prop("checked", true);
            else if (checkedLength > 0 && checkedLength < childLength) checkboxProductType.prop("indeterminate", true);

            renderCount();
            $('<span />').addClass('toggle').append($.renderIcon('angle-down-solid')).appendTo(label).click(function () {
                $(this).toggleClass("up")
                ul.toggle();
            })

            checkboxProductType.bind('click', function () {
                const checked = $(this).prop("checked");
                checkedLength = checked ? childLength : 0;
                if (checked) {
                    Object.keys(product.variants).forEach(key => {
                        selectedVariants[key] = 1;
                    })
                } else {
                    Object.keys(product.variants).forEach(key => {
                        delete selectedVariants[key];
                    })
                }
                __renderSelectedVariantCount();
                renderCount();
            })
            return wrapper;
        }


        const variantsContainer = $('<ul />');

        $.each(productsData, function(productTypeKey, product) {
            const item = __renderVariantsByProductType(product);
            variantsContainer.append(item);
        });

        const __renderSearchInput = () => {
            const filterLi = (node, searchValue) => {
                const title = node.attr('title') || '';
                const isMatchTitle = title.includes(searchValue);
                let matchCount = 0
                node.find('ul').children('li').each(function () {
                    const isMatch = filterLi($(this), searchValue);
                    if (isMatch) matchCount++;
                })
                const isMatchChild = matchCount > 0;
                const isMatch = isMatchTitle || isMatchChild;
                if (isMatch) node.show();
                else node.hide();

                return isMatch;
            }
            return $("<input />").addClass('styled-input').attr({
                'placeholder': 'Search...',
                id: 'variants_filter_input'
            }).appendTo(variantsContainer).on('input', function (e) {
                const searchValue = ($(this).val() || "").toLowerCase();

                variantsContainer.children('li').each(function () {
                    filterLi($(this), searchValue);
                })
            });

        }

        $('<div />').addClass("variants-container")
            .append($('<div />').addClass('indeterminate-alert').html('<svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
                '<path fill-rule="evenodd" clip-rule="evenodd" d="M12.4773 1.44209L19.746 14.0572C19.906 14.4338 19.976 14.7399 19.996 15.058C20.036 15.8012 19.776 16.5236 19.2661 17.0795C18.7562 17.6334 18.0663 17.9604 17.3164 18H2.6789C2.36896 17.9812 2.05901 17.9108 1.76906 17.8018C0.319302 17.2172 -0.380581 15.5723 0.20932 14.1464L7.52809 1.43317C7.77804 0.986278 8.15798 0.600818 8.6279 0.353094C9.98767 -0.40098 11.7174 0.0944694 12.4773 1.44209ZM10.8675 9.75573C10.8675 10.2314 10.4776 10.6287 9.99767 10.6287C9.51775 10.6287 9.11782 10.2314 9.11782 9.75573V6.95248C9.11782 6.47585 9.51775 6.09039 9.99767 6.09039C10.4776 6.09039 10.8675 6.47585 10.8675 6.95248V9.75573ZM9.99767 14.0176C9.51775 14.0176 9.11782 13.6202 9.11782 13.1456C9.11782 12.669 9.51775 12.2726 9.99767 12.2726C10.4776 12.2726 10.8675 12.6601 10.8675 13.1347C10.8675 13.6202 10.4776 14.0176 9.99767 14.0176Z" fill="#FF5656"/>\n' +
                '</svg> Mockup is contain some indeterminate variants. Please contact developer team to solve this case.'))
            .append(__renderSearchInput())
            .append(variantsContainer)
            .appendTo(variantsCol);

        const applyBtn = $("<div />").addClass('btn btn-primary btn-medium apply-btn').text('Apply').click(() => {
            // const savedMockup = {
            //     ...mockup,
            //     variantIds: selectedVariants
            // }
            const savedMockups = mockups.map((mockup) => {
                const removedIds = Object.keys(intersectionVariantIds).filter(key => !selectedVariants[key]);
                const addedIds = Object.keys(selectedVariants).filter(key => !intersectionVariantIds[key]);
                removedIds.forEach(id => {
                    delete mockup.variantIds[id];
                })

                addedIds.forEach(id => {
                    mockup.variantIds[id] = 1;
                })
                return mockup
            })

            onSave(savedMockups);

            $.unwrapContent(modalKey);
        });

        const cancelBtn = $("<div />").addClass('btn btn-medium btn-outline').text('Cancel').click(() => {
            $.unwrapContent(modalKey);
        })

        const __checkIndeterminateVariantIds = () => {
            if (indeterminateVariantIds.length > 0) {
                $('.indeterminate-alert').show();
                //applyBtn.unbind().attr("disabled", true);
                indeterminateVariantIds.forEach((productTypeVariant, i) => {
                    const _input = variantsCol.find('input[name="'+ productTypeVariant +'"]');

                    /** Disable this _input */
                    _input.addClass('indeterminate-variant').attr("disabled", true);

                    /** Disable the product type of _input */
                    _input.closest('.variants-col-product-type').find('input[name="product-type"]').unbind().addClass('indeterminate-variant').attr("disabled", true);

                    /** Disable all sibling inputs not checked of _input */
                    _input.closest('ul').find('input[type="checkbox"]').attr("disabled", true);

                    /** Disable all inputs has checked (contain this mockup) */
                    variantsCol.find('input[type="checkbox"]:checked').attr("disabled", true);
                });
            }
        }
        __checkIndeterminateVariantIds();

        $('<div />').addClass("bottom-wrapper").append(cancelBtn).append(applyBtn).appendTo(modalBody);
    }

    function _renderMappingVideoVariants(productsData, video, onSave) {
        const $modal = $(`
<div class="osc-modal modal-mapping-video-variants">
    <div class="body">
        <div class="container">
            <div class="video-col">
                <div class="video-item">
                    <video src="${video.url}" poster="${video.thumbnail || ''}" controls></video>
                </div>
            </div>
            <div class="variant-col">
                <div class="title">Add product type variants (<span class="total-checked-count">0</span>)</div>

                <input class="styled-input mb10" placeholder="Position default..." id="video_mockup_position" />

                <input class="styled-input input-search-variant" placeholder="Search..." id="variants_filter_input" />

                <ul class="variant-list"></ul>

                <div class="bottom-wrapper">
                    <div class="btn btn-medium btn-outline btn-cancel">Cancel</div>
                    <div class="btn btn-primary btn-medium btn-apply ml10">Apply</div>
                </div>
            </div>
        </div>
    </div>
</div>
        `);

        const videoId = (video.fileId || video.id);
        const modalKey = 'modalMappingVideoVariant';
        const selectedVariants = {...video.variantIds};

        const $variantList = $modal.find('.variant-list');
        const $selectedVariantCount = $modal.find('.total-checked-count');

        $.unwrapContent(modalKey);
        $.wrapContent($modal, { key: modalKey });

        $modal
            .moveToCenter()
            .css({
                width: 1200,
                top: 100,
                left: '50%',
                transform: 'translateX(-50%)',
            })
            .on('input', '.input-search-variant', function() {
                const searchValue = $(this).val().toLowerCase();

                $variantList.find('.product-type').each(function() {
                    const $product = $(this);
                    const title = $product.attr('title');
                    let showProductType = false;

                    $product.find('.product-type-variant').each(function() {
                        const $variant = $(this);
                        const variantTitle = $variant.attr('title');

                        if (variantTitle.toLowerCase().includes(searchValue.toLowerCase())) {
                            $variant.show();
                            showProductType = true;
                        } else {
                            $variant.hide();
                        }
                    });

                    if (title.toLowerCase().includes(searchValue.toLowerCase())) {
                        showProductType = true;
                    }

                    if (showProductType) {
                        $product.show();
                    } else {
                        $product.hide();
                    }
                });
            })
            .on('click', '.btn-apply', function() {
                const isNumberic = (str) => {
                    if (!str || Number(str) != str) {
                        return false;
                    }
                    return true;
                }
                let isValidPosition = true;
                const defaultPosition = $('#video_mockup_position').val();
                const mapObjectVariant = (carry, id) => {
                    const position = $($variantList.find(`input[name="position_variant_${id}"]`)[0])?.val();
                    if (!isNumberic(position) && !!position || position == '') {
                        isValidPosition = false;
                    }

                    return id 
                        ? { 
                            ...carry, 
                            [id]: position || defaultPosition
                        } : carry;
                }

                const variantIds = Array.from(
                        $variantList.find('.input-select-variant:checked')
                    )
                .map(input => $(input).data('variant-id'))
                .reduce(mapObjectVariant, {});

                if (Object.keys(variantIds).length && !isValidPosition && !isNumberic(defaultPosition)) {
                    alert('Position video is invalid !');
                    return;
                }
                onSave({
                    ...video,
                    variantIds,
                });

                $.unwrapContent(modalKey);
            })
            .on('click', '.btn-cancel', function() {
                $.unwrapContent(modalKey);
            });

        Object.values(productsData).forEach(product => {
            $variantList.append(
                __renderVariantsByProductType(product)
            );
        });

        __onVariantChecked();

        function __renderVariantsByProductType(product) {
            const variantLength = Object.values(product.variants)?.length || 0;
            const checkedVariantLength = Object.values(product.variants).filter(variant => (variant.video?.fileId || variant.video?.id) === videoId).length || 0;
            const $product = $(`
                <li class="product-type" title="${product.product_type_title}">
                    <input type="checkbox" data-variant="ceramic_mug" name="product-type" class="custom-checkbox input-select-product-type">
                    <label class="product-type-title">${product.product_type_title} <span> (<span class="checked-count">${checkedVariantLength}</span>/${variantLength}) </span>
                        <span class="product-type-toggle ml10">
                            <svg data-icon="osc-angle-down-solid" viewBox="0 0 310 200">
                                <use xlink:href="#osc-angle-down-solid"></use>
                            </svg>
                        </span>
                    </label>
                    <ul class="product-type-variants"></ul>
                </li>`)
                .on('click', '.product-type-toggle', function() {
                    $(this).closest('.product-type').toggleClass('active');
                })
                .on('change', '.input-select-product-type', function() {
                    let isChecked = $(this).prop('checked');

                    $product.find('.product-type-variant:not(.disabled) .input-select-variant').prop('checked', isChecked);

                    __onVariantChecked();
                });

            const $variantList = $product.find('.product-type-variants');

            for (const [variantId, variant] of Object.entries(product.variants)) {
                let variantVideoId = variant.video?.fileId || variant.video?.id;
                let disableFlag = variantVideoId && videoId !== variantVideoId;

                $(`<li class="product-type-variant ${disableFlag ? 'disabled' : ''}" title="${variant.title}">
                    <input type="checkbox" id="variant_${variantId}" data-variant-id="${variantId}" name="${variantId}" class="custom-checkbox input-select-variant" ${!!selectedVariants[variantId] ? 'checked' : ''} ${disableFlag ? 'disabled' : ''} >
                    <label for="variant_${variantId}">
                        ${variant.title}
                    </label>
                    <input type="text" class="styled-input" value="${video.variantIds[variantId] || ''}" name="position_variant_${variantId}" placeholder="Position..." style="float: right; width: 20%; height: 18px "/>
                </li>`)
                    .on('change', ':checkbox', __onVariantChecked)
                    .appendTo($variantList);
            }

            return $product;
        }

        function __onVariantChecked() {
            const totalSelected = $variantList.find('.input-select-variant:checked').length || 0;

            $selectedVariantCount.text(totalSelected);

            $variantList.find('.product-type').each(function() {
                const $product = $(this);
                const variantLength = $product.find('.input-select-variant').length || 0;
                const checkedVariantLength = $product.find('.input-select-variant:checked').length || 0;
                const $productInput = $product.find('.input-select-product-type');

                $product.find('.checked-count').text(checkedVariantLength);

                if (variantLength && variantLength === checkedVariantLength) {
                    $product.addClass('active');
                    $productInput.prop('checked', true);
                } else {
                    $product.removeClass('active');
                    $productInput.prop('checked', false);
                }
            });
        }
    }

    function _renderCampaignAddMockupsModal(originalData, campaignId) {
        const modalKey = 'catalogCampaignAddMockupsModal'
        $.unwrapContent(modalKey);

        const modal = $('<div />').addClass('osc-modal').width(1222),
            header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html('Add mockups').appendTo($('<div />').addClass('main-group').appendTo(header));
        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent(modalKey);
        }).appendTo(header);

        const modalBody = $('<div />').addClass('body post-frm').appendTo(modal);
        $.wrapContent(modal, {key: modalKey});
        modal.moveToCenter().css('top', '100px');

        const productsData = originalData;
        const getMockupsData = (products) => {
            const mockups = {};

            $.each(products, (key, product) => {
                $.each(product.variants, (variantId, variant) => {
                    variant?.images.forEach(image => {
                        if (!mockups[image.id]) mockups[image.id] = {
                            variantIds: {},
                            ...image
                        }
                        mockups[image.id].variantIds[variantId] = 1;
                    })
                })
            });
            return mockups;
        }

        let mockupsData = getMockupsData(productsData);

        const container = $('<div />').addClass('campaign-add-mockups').appendTo(modalBody);

        const productsContainer = $('<div />').appendTo(container);

        const __renderProductItem = (product, isActive) => {
            const lineElm = $('<div />').addClass("product-line-item");
            $('<div />').addClass("product-title").text(product.product_type_title).appendTo(lineElm);

            if (!isActive) return lineElm;

            lineElm.addClass('active');

            $("<div />").addClass("variants-title").append($("<b />").text('Option Variants')).appendTo(lineElm);

            const tableElm = $("<table />").addClass("variants-table grid grid-borderless").appendTo(lineElm);
            const tableBodyEm = $('<tbody />').appendTo(tableElm);
            const headerElm = $('<tr />').appendTo(tableBodyEm);

            $('<td />').text('Mockups').appendTo(headerElm);

            const optionKeys = Object.keys(product.option_title);
            optionKeys.forEach(key => {
                const value = product.option_title[key];
                $('<td />').text(value).appendTo(headerElm);
            })
            $.each(product.variants, (variantId, variant) => {
                const tr = $('<tr />').appendTo(tableBodyEm);
                const imagesColElm = $('<td />').appendTo(tr);
                const imageWrapper = $('<div />').addClass("d-flex").appendTo(imagesColElm);

                variant.images.forEach(image => {
                    $('<img />').addClass("mockup-image").attr("src", image.url).appendTo(imageWrapper)
                })
                optionKeys.forEach(key => {
                    const option = variant.option[key];
                    $('<td />').text(option ? option.value : '').appendTo(tr);
                })
            })

            return lineElm;
        }

        let activeIndex = 0;

        const __renderProducts = (products, activeIndex) => {
            productsContainer.empty();
            $.each(products, (index, product) => {
                const isActive = index === activeIndex;
                const productItemElm = __renderProductItem(product, isActive);
                if (!isActive) {
                    productItemElm.click(() => {
                        activeIndex = index;
                        __renderProducts(products, index)
                    });
                }
                productsContainer.append(productItemElm);
            });
        }

        __renderProducts(productsData, activeIndex);

        const onRemoveMockup = (mockup) => {
            $.each(productsData, (key, product) => {
                $.each(product.variants, (variantId, variant) => {
                    variant.images = variant.images?.filter(image => {
                        return mockup.fileId ? mockup.fileId !== image.fileId : mockup.id !== image.id;
                    })
                })
            });
            mockupsData = getMockupsData(productsData);
            __renderProducts(productsData, activeIndex)
        }
        const mockupsContainer = $('<div />').addClass("mockups-management-container").appendTo(container);
        $('<div />').addClass("title").text("Mockups Management").appendTo(mockupsContainer);
        const mockupsWrapper = $('<div />').addClass('mockups-wrapper').appendTo(mockupsContainer);

        const onSaveVariants = (mockups) => {
            const mockup = mockups[0]
            const id = mockup.fileId || mockup.id;
            if (!mockupsData[id]) return;
            mockupsData[id] = mockup;
            mockupsWrapper.find(`#${id}`).trigger('update', [mockup]);
            const variantIds = mockup.variantIds;

            $.each(productsData, function(key,product) {
                $.each(product.variants, (variantId, variant) => {
                    if (!variantIds[variantId]) {
                        variant.images = variant.images?.filter(image => {
                            const imageId = image.fileId || image.id;
                            return imageId != id;
                        })
                        return;
                    }
                    ;
                    const found = variant.images.find(image => {
                        const imageId = image.fileId || image.id;
                        return imageId === id;
                    })
                    if (!found) {
                        const image = {url: mockup.url};
                        if (mockup.fileId) image.fileId = mockup.fileId;
                        else image.id = mockup.id;
                        variant.images.push(image)
                    }
                })
            });

            __renderProducts(productsData, activeIndex);
        }

        const __renderMockupItem = (image) => {
            const itemElm = $('<div />').attr('id', image.fileId || image.id);
            const _render = (image) => {
                itemElm.empty();
                itemElm.removeClass();
                itemElm.addClass("mockup-item");

                if (image.uploading) {
                    itemElm.addClass("uploading")
                } else if (image.error) {
                    itemElm.addClass("error");
                } else {
                    itemElm.click(() => {
                        _renderMappingMockupVariants(productsData, [image], onSaveVariants)
                    })
                }
                itemElm.css('background-image', `url(${image.url})`);

                const removeBtn = $('<div />').addClass("remove-btn").append($.renderIcon('cross')).appendTo(itemElm);
                removeBtn.click((e) => {
                    e.stopPropagation();
                    itemElm.remove();
                    onRemoveMockup(image);
                })
                const variantCount = Object.keys(image.variantIds).length
                $('<tag />').text(variantCount).addClass("tag").appendTo(itemElm);
            }
            _render(image)
            itemElm.bind("update", function (e, image) {
                _render(image)
            })
            return itemElm;
        }

        const __renderMockupUploader = () => {
            const uploader = $('<div />').addClass('file-uploader').osc_uploader({
                max_files: -1,
                max_connections: 5,
                process_url: $.base_url + '/catalog/backend_campaign/uploadMockupCustomer/hash/' + OSC_HASH,
                btn_content: $('<div />').append($.renderIcon('plus')).append($('<b/>').text('Add New Mockup')).addClass('mockup-uploader'),
                dragdrop_content: 'Drop here to upload',
                image_mode: true,
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            })
            return uploader;
        }


        $.each(mockupsData, (imageId, image) => {
            const item = __renderMockupItem(image)
            mockupsWrapper.append(item)
        })


        const uploader = __renderMockupUploader();
        mockupsWrapper.append(uploader);
        const tempUploads = {};

        uploader.bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
            // uploader.attr('data-uploader-step', 'queue');
            const reader = new FileReader();
            reader.onload = () => {
                const image = {
                    fileId: file_id,
                    url: reader.result,
                    variantIds: {},
                    uploading: true
                }
                const item = __renderMockupItem(image);
                mockupsWrapper.prepend(item);
                image.item = item;
                tempUploads[file_id] = image;
            }
            reader.readAsDataURL(file);

        }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {

        }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
            let res;
            try {
                res = JSON.parse(response)
            } catch (error) {
                return;
            }
            const uploadImage = tempUploads[file_id];
            if (!uploadImage) return;

            delete tempUploads[file_id];

            uploadImage.url = res.data;
            uploadImage.uploading = false;
            const {item, ...image} = uploadImage;
            item.trigger("update", [image])
            mockupsData[file_id] = image;

        }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
            const uploadImage = tempUploads[file_id];
            if (!uploadImage) return;
            alert("Upload image failed! Error: ", error_message);
            delete tempUploads[file_id];
            uploadImage.error = true;

            const {item, ...image} = uploadImage;
            item.trigger("update", [image])
        });

        const applyBtn = $("<div />").addClass('btn btn-secondary btn-medium apply-btn').text('Apply').click(() => {
            if (applyBtn.attr('disabled') === 'disabled') {
                return;
            }
            applyBtn.attr('disabled', 'disabled');
            const images = [];
            for (const key in mockupsData) {
                const image = mockupsData[key];
                images.push({
                    image_id: image.id || null,
                    url: image.url,
                    variant_ids: image.variantIds ? Object.keys(image.variantIds).map(n => Number(n)) : []
                })
            }
            const data = {
                "campaign_id": campaignId,
                images
            }

            $.ajax({
                type: 'POST',
                url: '/catalog/backend_campaign/saveMockupCustomer/hash/' + OSC_HASH,
                data,
                success: function (response) {
                    alert('Apply mockups for variants successfully!')
                    $.unwrapContent(modalKey);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    applyBtn.removeAttr('disabled');
                    if (thrownError.trim().toLowerCase() !== 'abort') {
                        alert('ERROR [#' + xhr.status + ']: ' + thrownError);
                    }
                }
            });
        })

        const cancelBtn = $("<div />").addClass('btn btn-medium btn-outline').text('Cancel').click(() => {
            $.unwrapContent(modalKey);
        })

        $('<div />').addClass('modal-footer').append(cancelBtn).append(applyBtn).appendTo(container);
    }

    window.catalogCampaignAddMockups = function () {
        const productId = this.getAttribute("data-product-id");
        $(this).click(function () {
            $.ajax({
                type: 'GET',
                url: '/catalog/backend_campaign/loadMockup/hash/' + OSC_HASH,
                data: {campaign_id: productId},
                success: function (response) {
                    _renderCampaignAddMockupsModal(response.data, productId);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log("error")
                }
            });
        })
    }

    window.catalogCampaignConfig = function () {
        var products = null;
        var printTemplateArgs = {};
        let autoSelectOptions = [],
            manualSelectOption = [],
            VARIANTS_REORDER = {},
            is_reorder = 0,
            dataCustomMockups = {};

        var form = $(this);
        var product_panel = form.find('.product-panel');
        var design_panel = form.find('.design-panel');
        var design_tabs = design_panel.find('.design-tabs');
        var design_scene = design_panel.find('.design-scene');
        var image_selector = design_panel.find('.image-selector');
        var current_product_type = null;
        var current_print_template = null;
        var sameDesignApply = form.find('.apply-same-design');

        /**
         * Mockup var
         * @type {jQuery}
         */
        const mockupPanel = form.find('.mockup-panel'),
            mockupsWrapper = form.find('.mockups-wrapper'),
            actionGroup = mockupPanel.find('.actions'),
            guide = mockupPanel.find('.guide');

        new ResizeSensor(design_scene[0], function () {
            design_scene.trigger('resize');
        });

        form.submit(function (e) {
            e.preventDefault();
        });

        function makeAllVariant(paramArray) {
            function addVariant(curr, args) {
                var i, copy,
                    rest = args.slice(1),
                    last = !rest.length,
                    result = [];
                for (i = 0; i < args[0].length; i++) {
                    copy = curr.slice();
                    copy.push(args[0][i]);

                    if (last) {
                        result.push(copy);
                    } else {
                        result = result.concat(addTo(copy, rest));
                    }
                }
                return result;
            }

            return addVariant([], Array.prototype.slice.call(arguments));
        }

        function makeAllVariants(paramArray) {
            return paramArray.reduce((a, b) =>
                a.map(x => b.map(y => x.concat(y)))
                    .reduce((a, b) => a.concat(b), []), [[]]);
        }

        function _activeLabelOption(selector) {
            selector.on('click', function () {
                let _selectorBlock = $(this).closest('.selector-block');
                _selectorBlock.find('label').not($(this)).removeClass('active');

                if ($(this).hasClass('active')) {
                    $(this).removeClass('active');
                } else {
                    $(this).addClass('active');
                }
            });
        }

        function _renderInputType(optionConfig, level, item, selector) {
        }

        function _renderSelectType(optionConfig, level, item, selector) {
            let _item = $('<div/>').addClass('selector-item item-select').attr('data-option-key', item.key).appendTo(selector),
                _type = 'radio';

            if (_item.closest('.option-selector').hasClass('multi-option')) _type = 'checkbox';
            let _input = $('<input />').attr({
                'data-option-id': optionConfig.id,
                'data-option-key': optionConfig.key,
                'data-option-value-id': item.id,
                'data-option-value-key': optionConfig.id + ':' + item.id,
                'data-level' : level,
                'name': 'option' + level,
                'type': _type,
                'id': 'select-' + item.key,
                'value': item.key
            }).appendTo(_item).on('change', function () {
                _issetVariantValue($(this));
            });

            if (optionConfig.auto_select === 1) {
                _input.attr('data-auto-select', 1).prop("checked", true);
            }

            if (level > 0) {
                _input.prop("disabled", true);
            }

            let _label = $('<label />').attr('for', 'select-' + item.key).text(decodeEntities(item.title)).appendTo(_item);
            _activeLabelOption(_label);
        }

        function _renderRadioType(optionConfig, level, item, selector) {
            let _item = $('<div/>').addClass('selector-item item-radio').attr('data-option-key', item.key).appendTo(selector),
                _type = 'radio';

            if (_item.closest('.option-selector').hasClass('multi-option')) _type = 'checkbox';

            $('<input />').attr({
                'data-option-id': optionConfig.id,
                'data-option-key': optionConfig.key,
                'data-option-value-id': item.id,
                'data-option-value-key': optionConfig.id + ':' + item.id,
                'data-level' : level,
                'name': 'option' + level,
                'type': _type,
                'id': 'radio-' + item.key,
                'value': item.key
            }).appendTo(_item).on('change', function () {
                _issetVariantValue($(this));
            });

            if (optionConfig.auto_select === 1) {
                _input.prop("checked", true);
            }

            let _label = $('<label />').attr('for', 'radio-' + item.key).text(decodeEntities(item.title)).appendTo(_item);
            _activeLabelOption(_label);
        }

        function _renderColorType(optionConfig, level, item, selector) {
            let _item = $('<div/>').addClass('selector-item item-color ' + item.key).attr('data-option-key', item.key).appendTo(selector),
                _type = (optionConfig.multi_select === 1) ? 'checkbox' : 'radio',
                _multi = (optionConfig.multi_select === 1) ? 1 : 0;

            let _input = $('<input />').attr({
                'data-multi-select': _multi,
                'data-option-id': optionConfig.id,
                'data-option-key': optionConfig.key,
                'data-option-value-id': item.id,
                'data-option-value-key': optionConfig.id + ':' + item.id,
                'data-level' : level,
                'name': 'option' + level,
                'type': _type,
                'id': 'color-' + item.key,
                'value': item.key
            }).appendTo(_item).on('change', function () {
                _issetVariantValue($(this));
            });

            if (level > 0) {
                _input.prop("disabled", true);
            }

            let _label = $('<label/>').attr({
                'for': 'color-' + item.key,
                'data-tips': item.title
            }).css('background-color', item.meta_data.hex).appendTo(_item);
            _activeLabelOption(_label);
        }

        function _renderCheckboxType(optionConfig, level, item, selector) {
            let _item = $('<div/>').addClass('selector-item item-checkbox').attr('data-option-key', item.key).appendTo(selector);
            let _input = $('<input />').attr({
                'data-option-id': optionConfig.id,
                'data-option-key': optionConfig.key,
                'data-option-value-id': item.id,
                'data-option-value-key': optionConfig.id + ':' + item.id,
                'data-level' : level,
                'name': 'option' + level,
                'type': 'checkbox',
                'id': 'checkbox-' + item.key,
                'value': item.key
            }).appendTo(_item).on('change', function () {
                _issetVariantValue($(this));
            });

            if (level > 0) {
                _input.prop("disabled", true);
            }

            if (optionConfig.auto_select === 1) {
                _input.attr('data-auto-select', 1).prop("checked", true);
            }

            let _label = $('<label />').attr('for', 'checkbox-' + item.key).text(decodeEntities(item.title)).appendTo(_item);
            _activeLabelOption(_label);
        }

        function _renderButtonType(optionConfig, level, item, selector) {
            let _item = $('<div/>').addClass('selector-item item-button').attr('data-option-key', item.key).appendTo(selector),
                _type = 'radio';
            if (_item.closest('.option-selector').hasClass('multi-option')) _type = 'checkbox';
            let _input = $('<input />').attr({
                'data-option-id': optionConfig.id,
                'data-option-key': optionConfig.key,
                'data-option-value-id': item.id,
                'data-option-value-key': optionConfig.id + ':' + item.id,
                'data-level' : level,
                'name': 'option' + level,
                'type': _type,
                'id': 'button-' + item.key,
                'value': item.key
            }).appendTo(_item).on('change', function () {
                _issetVariantValue($(this));
            });

            if (level > 0) {
                _input.prop("disabled", true);
            }

            if (optionConfig.auto_select === 1) {
                _input.attr('data-auto-select', 1).prop("checked", true);
            }

            let _label = $('<label />').attr('for', 'button-' + item.key).text(decodeEntities(item.title)).appendTo(_item);
            _activeLabelOption(_label);
        }

        function compareArray(array1, array2) {
            let is_same = array1.length == array2.length && array1.every(function (element, index) {
                if (array2.indexOf(element) > -1) {
                    return element = array2[array2.indexOf(element)];
                }
            });
            return is_same;
        }

        function _tickOptions(checked = []) {
            /**
             * First: Tick all option was used to create variants from DB
             */
            let _optionLength = parseInt($("input[name='option-length']").val()),
                 option_root_level = $("input[name='option" + 0 + "']:checked").val();

            $('.option-selector-block').find('.item-added').remove();

            if (typeof products.product_types[current_product_type]?.product_variant !== "undefined") {
                let _currentProductTypeVariants = products.product_types[current_product_type]?.product_variant,
                    _currentOptions = [];
                if (_currentProductTypeVariants.length > 0) {
                    for (let i = 0; i < _currentProductTypeVariants.length; i++) {
                        for (let j = 0; j < _currentProductTypeVariants[i].option.length; j++) {
                            if (_currentOptions.indexOf(_currentProductTypeVariants[i].option[j]) === -1) {
                                if (_optionLength <= 2) {
                                    _currentOptions.push(_currentProductTypeVariants[i].option[j]);
                                } else {
                                    _currentOptions[_currentProductTypeVariants[i].option[j]] = _currentProductTypeVariants[i].option[0];
                                }
                            }
                        }
                    }
                }

                $('.option-selector-block').find('.selector-item').each(function () {
                    let _optionKey = $(this).data('option-key');
                    if (_optionLength <= 2) {
                        if (_currentOptions.indexOf(_optionKey) > -1) {
                            $('<span />').addClass('item-added').appendTo($(this));
                        }
                    } else {
                        if (_currentOptions[_optionKey] && _currentOptions[_optionKey] == option_root_level) {
                            $('<span />').addClass('item-added').appendTo($(this));
                        }
                    }
                });
            }
        }

        function _issetVariantValue(option = null) {
            let _isset = false,
                _optionLength = parseInt($("input[name='option-length']").val()),
                _product_type_id = $('input[name="product_type_id"]').val();

            let option_level =  parseInt(option.attr('data-level'));

            if(option_level == 0 && _optionLength > 2) {
                $("input:checked").not(option).prop("checked", false);
            }

            _tickOptions();

            function _activeOption(option) {
                option.parent().removeClass('item-hidden');
                option.removeAttr('disabled');
                option.closest('.selector-block').find('.item-not-allow').remove();
            }

            function _hiddenOption(option) {
                if (parseInt(option.attr('data-auto-select')) !== 1) {
                    option.closest('.selector-item').addClass('item-hidden');
                    option.attr('disabled', 'disabled');
                    $('<span />').addClass('item-not-allow').appendTo(option.parent());
                }
            }

            let _option = [],
                _variantCheck = [],
                _optionTocheck = [];
            /**
             * Get all options is in checked
             */
            for (let i = 0; i < _optionLength; i++) {
                let _tmp = $("input[name='option" + i + "']:checked").val();

                if (typeof _tmp !== 'undefined') {
                    _option.push(_tmp);
                    _variantCheck.push(_tmp);
                } else {
                    _optionTocheck.push(i);
                }
            }

            /**
             * Check valid variant, compare with variants get from DB
             */
            if (_optionTocheck.length > 0) {
                /**
                 * 1. Check option value can be add to variants with all option value has checked
                 */
                for (let i = 0; i < _optionLength; i++) {
                    let input_options = $("input[name='option" + i + "']");
                    input_options.each(function (index, option) {
                        let is_contain = false, level = parseInt($(option).attr('data-level')), option_data = [];

                        let __level = level == 0 ? level : 1;

                        for (let i = 0; i < __level; i++) {
                            let option_checked = $("input[name='option" + i + "']:checked");

                            if (!option_checked.attr('checked')) {
                                return;
                            }

                            if (option_checked.val()) {
                                option_data.push(option_checked.val());
                            }
                        }

                        for (let j = 0; j < _allVariants.length; j++) {
                            let __option_value = $(option).val();

                            if (!option_data[option_data.indexOf(__option_value)]) option_data.push(__option_value);

                            let option_level_data = _allVariants[j].option.slice(0, (level + 1));

                            if (compareArray(option_data, option_level_data)) {
                                is_contain = true;
                            }

                            if (is_contain) {
                                _activeOption($(option));
                            }
                        }
                    });
                }

                for (let i = 0; i < _optionTocheck.length; i++) {
                    $("input[name='option" + _optionTocheck[i] + "']").each(function () {
                        let _parent = $(this).closest('.selector-item');

                        _activeOption($(this));

                        let _key = $(this).val(),
                            _hasContain = false,
                            _hasCreated = false;

                        /**
                         * Check if all campaign variants has not contain options value, disable this option value
                         */
                        for (let j = 0; j < _allVariants.length; j++) {
                            if ((_variantCheck.every(function (val) {
                                return _allVariants[j].option.indexOf(val) >= 0;
                            })) && _allVariants[j].option.indexOf(_key) >= 0) {
                                _hasContain = true;
                            }
                        }

                        if (!_hasContain && parseInt($(this).attr('data-multi-select')) !== 1) {
                            _hiddenOption($(this));
                        }

                        /**
                         * Check each option value in this level not in product variants has created, un-tick this option value
                         */
                        if (typeof products.product_types[_product_type_id]?.product_variant !== "undefined") {
                            for (let j = 0; j < products.product_types[_product_type_id].product_variant.length; j++) {
                                if ((_variantCheck.every(function (val) {
                                    return products.product_types[_product_type_id].product_variant[j].option.indexOf(val) >= 0;
                                })) && products.product_types[_product_type_id].product_variant[j].option.indexOf(_key) >= 0) {
                                    _hasCreated = true;
                                }
                            }
                        }

                        if (_hasContain) {
                            if (!_hasCreated) {
                                _parent.find('.item-added').remove();
                            } else {
                                $('<span />').addClass('item-added').appendTo(_parent);
                            }
                        }
                    });
                }

                for (let i = 0; i < _optionLength; i++) {
                    let input_options = $("input[name='option" + i + "']");
                    if (option) {
                        input_options.each(function (index, input_option) {
                            let _option = $(input_option);
                            let _option_level = parseInt(_option.attr('data-level'));

                            if (_option_level > (option_level + 1)) {
                                _option.attr('disabled', 'disabled');
                            }

                            if (!option.attr('checked')) {
                                if (_option_level > option_level) {
                                    _option.attr('disabled', 'disabled');
                                    _option.prop('checked', false);
                                    $('<span />').addClass('item-not-allow').appendTo(_option.parent());
                                }
                            }
                        });
                    }
                }
            } else {
                /**
                 * 2. Check option value can be add to variants with each option value has checked
                 */
                for (let i = 0; i < _optionLength; i++) {
                    _activeOption($("input[name='option" + i + "']"));
                }

                for (let i = 0; i < _optionLength; i++) {
                    let _options = [];
                    for (let j = 0; j < _optionLength; j++) {
                        if (i !== j) {
                            _options.push($("input[name='option" + j + "']:checked").val());
                        }
                    }
                    $("input[name='option" + i + "']").not(':checked').each(function (index) {
                        let _val = $(this).val(),
                            _hasContain = false,
                            _thisValueChecked = Array.from(_options);

                        _thisValueChecked.push(_val);
                        for (let j = 0; j < _allVariants.length; j++) {
                            if (compareArray(_thisValueChecked, _allVariants[j].option)) {
                                _hasContain = true;
                            }
                        }
                        if (!_hasContain && parseInt($(this).attr('data-multi-select')) !== 1) {
                            _hiddenOption($(this));
                        }

                    });

                    if (typeof products.product_types[_product_type_id]?.product_variant !== "undefined") {
                        $("input[name='option" + i + "']:checked").each(function (index) {
                            let _parent = $(this).closest('.selector-item'),
                                _val = $(this).val(),
                                _hasContain = false,
                                _thisValueChecked = Array.from(_options);

                            _thisValueChecked.push(_val);
                            for (let j = 0; j < products.product_types[_product_type_id].product_variant.length; j++) {
                                if (compareArray(_thisValueChecked, products.product_types[_product_type_id].product_variant[j].option)) {
                                    _hasContain = true;
                                    break;
                                }
                            }

                            if (_hasContain) {
                                $('<span />').addClass('item-added').appendTo(_parent);
                            } else {
                                if (parseInt($(this).attr('data-multi-select')) !== 1) {
                                    _parent.find('.item-added').remove();
                                }
                            }
                        });
                    }


                }
            }

            /**
             * Push option value and check isset variant
             */
            if (typeof products.product_types[_product_type_id] !== 'undefined' && typeof products.product_types[_product_type_id].product_variant !== 'undefined') {
                for (let i = 0; i < products.product_types[_product_type_id].product_variant.length; i++) {
                    if (compareArray(_option, products.product_types[_product_type_id].product_variant[i].option)) {
                        _isset = true;
                    }
                }
            }

            if (_isset) {
                for (let i = 0; i < _optionLength; i++) {
                    let _parent = $("input[name='option" + i + "']:checked").parent();
                    $('<span />').addClass('item-added').appendTo(_parent);
                }
            }


        }

        function _changePrintTemplateStatus(printTemplateKey) {
            let _found = false,
                _class = 'enough-data',
                _text = 'Lack of data',
                _icon = 'warning';

            let designIds = [],
                designList = '';
            $.each(printTemplateArgs[printTemplateKey].config.segments, function (design_key) {
                if (typeof products.campaign_config.print_template_config[printTemplateKey].segments[design_key] !== 'undefined') {
                    _found = true;
                    _class = 'full-data';
                    _text = 'Ready!';
                    _icon = 'verified';
                }
            });

            $.each(products.campaign_config.print_template_config[printTemplateKey].segments, function (designKey, config) {
                designIds.push(config.source.design_id);
            });

            designList = _renderDesignId(designIds);

            product_panel.find(".template-row[data-key='" + printTemplateKey + "']").each(function () {
                $(this).removeClass('full-data').removeClass('enough-data').addClass(_class);
                $(this).find('.print-template-status').html(_text).append($.renderIcon(_icon));
                $(this).find('.design-ids').html(designList);
            });
        }

        function _renderDesignId(designIds) {
            let designList = '';

            if (designIds.length > 0) {
                designList = '[ <b>ID:';
                designIds.forEach(function (design_id, key) {
                    designList += ' <a id="design_id_config" title="Open personalized page [' + design_id + ']" href="' + $.base_url + '/personalizedDesign/backend/post/id/' + design_id + '/type/default/hash/' + OSC_HASH + '" target="_blank">' + design_id + '</a>';
                    if (key >= (designIds.length - 1)) {
                        return
                    }

                    designList += ', ';
                });
                designList += '</b> ]';
            }

            return designList;
        }

        function _initReorderOptions(data, container) {
            let containerTitle = container.closest('.variants-list').find('.variant-heading'),
                _hasReorderOption = false;

            if (typeof data.product_variant !== 'undefined' && data.product_variant !== null) {
                for (let i = 0; i < data.options.length; i++) {
                    if (data.options[i].is_reorder === 1) {
                        _hasReorderOption = true;
                        break;
                    }
                }
            }

            if (_hasReorderOption) {
                if (containerTitle.find('.btn-reorder').length < 1) {
                    setTimeout(function () {
                        let _btnAdd = containerTitle.find('.btn-create-variant');
                        $('<span />').addClass('btn btn-reorder').append('<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
                            '<path d="M19.1665 3.3335V8.3335H14.1665" stroke="#2684FE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>\n' +
                            '<path d="M17.0751 12.4998C16.5334 14.0331 15.508 15.3488 14.1535 16.2486C12.799 17.1484 11.1888 17.5836 9.56541 17.4887C7.94205 17.3937 6.39352 16.7737 5.15319 15.7221C3.91286 14.6705 3.04792 13.2442 2.6887 11.6582C2.32949 10.0723 2.49546 8.41251 3.16161 6.92907C3.82777 5.44564 4.95801 4.2189 6.38202 3.43371C7.80604 2.64853 9.44667 2.34743 11.0567 2.5758C12.6667 2.80417 14.1589 3.54963 15.3084 4.69985L19.1667 8.33318" stroke="#2684FE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>\n' +
                            '</svg> Reorder Variants\n').on('click', function () {
                            _bindReorderOption(data);
                        }).insertAfter(_btnAdd);

                    }, 100);
                }

                if (form.data('apply_reorder') === 1) {
                    let applyReorderOption = $('<div />').addClass('apply-reorder-bar');
                    $('<input />').attr({'type': 'checkbox','name': 'apply_reorder_' + data.id, 'id': 'apply_reorder_' + data.id, 'value': data.id})
                        .prop('checked', (products.campaign_config.apply_reorder.indexOf(data.id) > -1))
                        .prependTo($('<label />').attr('data-tip', 'Warning! This option will apply this re-order option of product type "' + data.name + '" to all product type in all campaign!').text('Apply reorder for all campaign')
                            .on('change', function() {
                                _saveApplyReorderToAll(data.id, $('#apply_reorder_' + data.id).is(':checked'));
                            }).appendTo(applyReorderOption));

                    if (container.closest('.variants-list').find('.apply-reorder-bar').length < 1) {
                        applyReorderOption.insertAfter(containerTitle);
                    } else {
                        $('#apply_reorder_' + data.id).prop('checked', (products.campaign_config.apply_reorder.indexOf(data.id) > -1));
                    }
                }

            }
        }

        /**
         * Update custom_mockup data
         */
        function _updateCustomMockupData(productTypeVariants) {
            $.ajax({
                type: 'GET',
                url: '/catalog/backend_campaign/loadMockup/hash/' + OSC_HASH,
                data: {
                    campaign_id: form.data('campaign-id'),
                    product_type_variants: productTypeVariants
                },
                success: function (response) {
                    if (response.result == 'OK') {
                        if (!products.custom_mockup) {
                            products.custom_mockup = response.data;
                        } else {
                            $.each(response.data, function(key, data){
                                $.each(data.variants, function(prodyctTypeVariantId, productTypeVariantValue) {
                                    if (typeof products.custom_mockup[key] !== 'undefined') {
                                        products.custom_mockup[key].variants[prodyctTypeVariantId] = productTypeVariantValue;
                                        products.custom_mockup[key].selected_all = data.selected_all;
                                        products.custom_mockup[key].auto_options = data.auto_options;
                                    }
                                });
                            });
                        }
                        return products.custom_mockup;
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log("error")
                }
            });
        }

        /**
         *
         * @param data: Array of product type and option, option values, product variants
         * @param container: selector
         * @private
         */
        function _generateVariantList(data, container, isPanel = false) {

            function _expandItems(itemLength, selector) {
                if (itemLength >= _maxItemsShow) {
                    selector.siblings('.item-collapse').remove();
                    selector.css({'max-height': (selector.find('.variant-row').first().outerHeight() * _maxItemsShow)});
                    let _text = 'Show More',
                        _class = '';
                    if (selector.hasClass('expanded')) {
                        _text = 'Show Less';
                        _class = 'expanded';
                    }
                    $('<div />').addClass('item-collapse ' + _class).append($('<span/>').text(_text)).append($.renderIcon('angle-down-solid')).on('click', function () {
                        selector.toggleClass('expanded');
                        $(this).toggleClass('expanded');
                        if ($(this).hasClass('expanded')) {
                            $(this).find('span').text('Show Less');
                        } else {
                            $(this).find('span').text('Show More');
                        }
                    }).insertAfter(selector);
                } else {
                    selector.siblings('.item-collapse').remove();
                    selector.removeClass('expanded');
                }
            }

            function _checkRemovePrintTemplate(product_type_variant) {
                let _productType = data.key;
                $.ajax({
                    type: 'POST',
                    url: $.base_url + '/catalog/backend_campaign/getPrintTemplate/hash/' + OSC_HASH,
                    data: {
                        product_type_id: _productType,
                        product_type_variant: product_type_variant
                    },
                    success: function (response) {
                        let _data = [];
                        if (response.result === 'OK') {
                            _data = response.data;
                        }

                        let _thisProductType = $(".product[data-key='" + _productType + "']"),
                            _printTemplates = [];

                        $.each(_data, function (key, config) {
                            _printTemplates.push('printTemplate_' + config.print_template_id);
                        });

                        product_panel.find('.product').not(_thisProductType).each(function () {
                            $(this).find(".template-row").each(function () {
                                if (_printTemplates.indexOf($(this).data('key')) === -1) {
                                    _printTemplates.push($(this).data('key'));
                                }
                            });
                        });

                        if (_printTemplates.length > 0 && !$.isEmptyObject(products.campaign_config.print_template_config)) {
                            $.each(printTemplateArgs || {}, function (key) {
                                if (_printTemplates.indexOf(key) === -1) {
                                    delete products.campaign_config.print_template_config[key];
                                    delete printTemplateArgs[key];
                                }
                            });
                        } else {
                            products.campaign_config.print_template_config = {};
                            printTemplateArgs = {};
                        }

                        _renderPrintTemplateFrm(_productType, _data);
                        _thisProductType.trigger('expand');
                    }
                });
            }

            /**
             * Create the temporary obj of option values title by key
             * @type {{}}
             */
            let optionValueTitle = {},
                colorPreviewOptions = {},
                colorHexData = {};
            let _maxItemsShow = 5;

            autoSelectOptions[data.key] = [],
            manualSelectOption[data.key] = [];

            $.each(data.options, function (level, config) {
                if (parseInt(config.auto_select) === 1) { /* 1: Auto select all variant in this option. Don't show this option in variants select box */
                    for (let i = 0; i < config.values.length; i++) {
                        autoSelectOptions[data.key].push(config.values[i].key);
                    }
                } else {
                    for (let i = 0; i < config.values.length; i++) {
                        manualSelectOption[data.key].push(config.values[i].key);
                    }
                }
            });

            container.siblings('.item-collapse').remove();
            container.removeClass('expanded');

            if (data.options.length > 0) {
                for (let i = 0; i < data.options.length; i++) {
                    if (data.options[i].values.length > 0) {
                        for (let j = 0; j < data.options[i].values.length; j++) {
                            optionValueTitle[data.options[i].values[j].key] = data.options[i].values[j].title.replace(/&quot;/g, '"');

                            if (data.options[i].type === 'color') {
                                colorHexData[data.options[i].values[j].key] = data.options[i].values[j].meta_data.hex;
                            }

                            if (data.options[i].type === 'color' && parseInt(data.options[i].auto_select) === 2) { /* auto_select = 0: Normal, 1: auto selected all when create variant (Ex: Auto select T-shirt size), 2: Main select,  (Apply for color) */
                                let _replaceKey = data.options[i].values[j].key;
                                colorPreviewOptions[_replaceKey] = _replaceKey.replace(data.options[i].key + '/', '');
                            }
                        }
                    }
                }
            }

            if (typeof data.product_variant !== 'undefined' && data.product_variant !== null) {
                container.html('');
                let _variantLabel = $('<div />').addClass('variant-row title').appendTo(container);
                let _key = data.key,
                    _replaceKey = '',
                    _column = data.options.length;

                if (isPanel === true) {
                    _initReorderOptions(data, container);
                }

                if (data.options.length > 0) {
                    if (isPanel) {
                        _column = _column + 1;
                        $('<span />').css('max-width', '196px').text('Mockup').appendTo(_variantLabel);
                    }
                    $.each(data.options, function (key, config) {
                        if (config.type === 'color' && parseInt(config.auto_select) === 2 && _replaceKey === '') {
                            _replaceKey = '{opt.' + config.key + '}';
                        }
                        $('<span />').css('width', 'calc((100% - ' + (data.options.length * 30 + 140) + 'px)/' + _column + ')').text(config.title).appendTo(_variantLabel);
                    });
                    $('<span />').text('').appendTo(_variantLabel);
                }

                let _rowKeys = [],
                    newProductTypeVariants = [];

                if (!products.custom_mockup) {
                    products.custom_mockup = {};
                }

                if (typeof products.custom_mockup[data.key] === "undefined") {
                    products.custom_mockup[data.key] = {
                        key:data.key,
                        product_type_title: data.name
                    };
                    products.custom_mockup[data.key].variants = {};
                }

                $.each(data.product_variant, function (key, value) {
                    let _optionManual = [],
                        _lineKeys = [],
                        _itemLineKey = '';

                    if (typeof products.custom_mockup[data.key] !== 'undefined') {
                        if (Object.keys(products.custom_mockup[data.key].variants).map(n => Number(n)).indexOf(value.id) < 0) {
                            newProductTypeVariants.push(value.id);
                        }
                    }

                    var itemRow = $('<div />').addClass('variant-row').attr('data-id', value['id']);

                    $('<input />').attr({
                        'type': 'hidden',
                        'value': value['id'],
                        'data-ukey': value['ukey'],
                        'name': 'product_type_variant[' + data.key + '][]'
                    }).appendTo(itemRow);

                    $('<span />').addClass('collumn-custom-mockup').attr({'data-variant-id': value['id'], 'id': 'custom_mockup_' + value['id']}).css('max-width', '196px').html(isPanel?'&nbsp;':'').appendTo(itemRow);

                    for (let i = 0; i < value.option.length; i++) {
                        let _thisCol = '';
                        if (autoSelectOptions[data.key].length > 0) {
                            if (autoSelectOptions[data.key].indexOf(value.option[i]) === -1) {
                                _optionManual.push(value.option[i]);
                                if (i === 0) {
                                    _itemLineKey += value.option[i];
                                } else {
                                    _itemLineKey += '_' + value.option[i];
                                }
                                _thisCol = $('<span />').css('width', 'calc((100% - ' + (value.option.length * 30 + 140) + 'px)/' + _column + ')').text(optionValueTitle[value.option[i]]).appendTo(itemRow);


                            } else {
                                _thisCol = $('<span />').css('width', 'calc((100% - ' + (value.option.length * 30 + 140) + 'px)/' + _column + ')').text('All').appendTo(itemRow);
                            }

                        } else {
                            if (i === 0) {
                                _itemLineKey += value.option[i];
                            } else {
                                _itemLineKey += '_' + value.option[i];
                            }
                            _thisCol = $('<span />').css('width', 'calc((100% - ' + (value.option.length * 30 + 140) + 'px)/' + _column + ')').text(optionValueTitle[value.option[i]]).appendTo(itemRow);
                        }

                        /**
                         * Replace color option from text to label
                         */
                        if (Object.keys(colorHexData).length > 0 && Object.keys(colorHexData).indexOf(value.option[i]) > -1) {
                            _thisCol.addClass('color-collumn').addClass(value.option[i]).html($('<label />').css('background-color', colorHexData[value.option[i]]));
                        }

                        /**
                         * Add preview design by colors
                         */
                        if (Object.keys(colorPreviewOptions).length > 0 && isPanel) {
                            if (typeof colorPreviewOptions[value.option[i]] !== 'undefined') {
                                let _replaceValue = colorPreviewOptions[value.option[i]];

                                _thisCol.addClass('preview-color').click(function () {
                                    $('.variant-row').removeClass('active');
                                    itemRow.addClass('active');
                                    design_scene.trigger('updatePreview', [{
                                        replaceKey: _replaceKey,
                                        replaceValue: _replaceValue
                                    }]);
                                });
                            }
                        }
                    }

                    if (_rowKeys.indexOf(_itemLineKey) === -1) {
                        _rowKeys.push(_itemLineKey);

                        $('<span />').append($.renderIcon('trash-alt-regular')).click(function () {
                            if (typeof products.product_types[_key].product_variant !== 'undefined') {
                                let _needRemove = [];
                                for (let i = 0; i < products.product_types[_key].product_variant.length; i++) {
                                    if (autoSelectOptions[data.key].length > 0) {
                                        /* Remove All variant by manual options */
                                        for (let j = 0; j < autoSelectOptions[data.key].length; j++) {
                                            let _tmpManual = Array.from(_optionManual);
                                            _tmpManual.push(autoSelectOptions[data.key][j]);

                                            if (compareArray(_tmpManual, products.product_types[_key].product_variant[i].option)) {
                                                _needRemove.push(i);
                                                // products.product_types[_key].product_variant.splice(i,1);
                                            }
                                        }
                                    } else {
                                        if (compareArray(value.option, products.product_types[_key].product_variant[i].option)) {
                                            _needRemove.push(i);
                                            //products.product_types[_key].product_variant.splice(i,1);
                                        }
                                    }
                                }

                                if (_needRemove.length > 0) {
                                    let _tmpVariants = [];
                                    for (let i = 0; i < products.product_types[_key].product_variant.length; i++) {
                                        if (_needRemove.indexOf(i) < 0) {
                                            _tmpVariants.push(products.product_types[_key].product_variant[i]);
                                        }
                                    }

                                    products.product_types[_key].product_variant = [];
                                    if (_tmpVariants.length > 0) {
                                        products.product_types[_key].product_variant = Array.from(_tmpVariants);
                                    }
                                }

                            }
                            itemRow.remove();

                            if (isPanel) {
                                const _index = _rowKeys.indexOf(_itemLineKey);
                                if (_index > -1) {
                                    _rowKeys.splice(_index, 1);
                                }
                                _expandItems(_rowKeys.length, container);
                            } else {
                                let _variantPanel = $('.variant-generated[data-product-type="' + _key + '"]');
                                _variantPanel.find('.variant-row[data-id="' + value['id'] + '"]').remove();
                            }

                            let _variantArgs = [];
                            if (products.product_types[_key].product_variant.length > 0) {
                                $.each(products.product_types[_key].product_variant, function (key, config) {
                                    _variantArgs.push(config.id);
                                });
                            }
                            _tickOptions();
                            _checkRemovePrintTemplate(_variantArgs);

                        }).appendTo(itemRow);

                        itemRow.appendTo(container);

                    }
                });

                if (newProductTypeVariants.length > 0) {
                    _updateCustomMockupData(newProductTypeVariants);
                }

                if (isPanel) {
                    if (autoSelectOptions[data.key].length > 0) {
                        _expandItems(_rowKeys.length, container);
                    } else {
                        _expandItems(data.product_variant.length, container);
                    }
                }
            }
        }

        function _renderPrintTemplateFrm(product_type, data) {
            var container = $('.print-templates[data-product-type="' + product_type + '"]'),
                templateList = container.find('.template-list');
            if (templateList.length == 0) {
                templateList = $('<div />').addClass('template-list').appendTo(container);
            } else {
                templateList.html('');
            }
            if (data.length > 0) {
                for (let i = 0; i < data.length; i++) {
                    let _id = data[i].print_template_id,
                        _key = 'printTemplate_' + _id;
                    if (typeof printTemplateArgs[_key] === 'undefined') {
                        printTemplateArgs[_key] = data[i];
                    }

                    let _found = true, _class = 'full-data', _text = 'Ready!', _icon = 'verified';
                    let designIds = [];
                    let designList = '';
                    if (products.campaign_config.print_template_config === null || typeof products.campaign_config.print_template_config[_key] === "undefined" || (typeof products.campaign_config.print_template_config[_key].segments === "undefined")) {
                        $.each(printTemplateArgs[_key].config.segments, function (design_key) {
                            _found = false;
                            _class = 'enough-data';
                            _text = 'Lack of data';
                            _icon = 'warning';
                        });
                    } else {
                        $.each(products.campaign_config.print_template_config[_key].segments, function (designKey, config) {
                            designIds.push(config.source.design_id);
                        });
                    }

                    designList = _renderDesignId(designIds);

                    let itemRow = $('<div />').addClass('template-row ' + _class).attr({
                        'data-id': _id,
                        'data-key': _key,
                        'data-title': data[i].title
                    });
                    $('<span />').html('<span class="print-template-title" title="' + data[i].title + '">' + data[i].title + '</span>').prepend($('<span />').addClass('design-ids').html(designList)).prepend($('<svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
                        '<path d="M0.76314 0.75L9.01314 5.51314L0.76314 10.2763L0.76314 0.75Z" fill="#2684FE"/>\n' +
                        '</svg>\n').addClass('triangle')).append($('<input />').addClass('print-template-config').attr({
                        type: 'hidden',
                        value: _key,
                        'data-title': data[i].title
                    })).appendTo(itemRow);
                    $('<span />').addClass('print-template-status').text(_text).append($.renderIcon(_icon)).appendTo(itemRow);

                    itemRow.click(function () {
                        $('.template-row').removeClass('active');
                        $(this).addClass('active');
                        let _printTemplateKey = $(this).find('input.print-template-config').val(),
                            _printTemplateTitle = $(this).find('input.print-template-config').data('title');

                        //Call render design form
                        current_print_template = _printTemplateKey;
                        _postFrmDesignSceneRender(product_type, _printTemplateTitle);
                    }).appendTo(templateList);
                }
            } else {
                _cleanDesignScene();
                templateList.html('<div class="template-row"><span style="text-align: left;">No print template!</span></div>');
            }
        }

        function _renderPrintTemplateList(productType) {
            var _variants = $("input[name='product_type_variant[" + productType + "][]']").serializeObject();

            if (Object.keys(_variants).length > 0) {
                let _productTypeVariants = [];

                if (typeof products.product_types?.[productType] !== "undefined") {
                    const data = products.product_types[productType];

                    $.each(data?.product_variant, function (key, value) {
                        if (_productTypeVariants.indexOf(value.id) < 0) {
                            _productTypeVariants.push(value.id);
                        }
                    });
                }

                $.ajax({
                    type: 'POST',
                    url: $.base_url + '/catalog/backend_campaign/getPrintTemplate/hash/' + OSC_HASH,
                    data: {
                        product_type_id: productType,
                        product_type_variant: _productTypeVariants
                    },
                    success: function (response) {
                        if (response.result != 'OK') {
                            alert('Error! Please try again.');
                        } else {
                            _renderPrintTemplateFrm(productType, response.data);
                        }
                    }
                });
            }

        }

        function _convertSegment(designSource, from_print_template, to_print_template) {
            if (Object.keys(from_print_template.config.segments).length !== Object.keys(to_print_template.config.segments).length || typeof to_print_template.config.segments[designSource.selected_design] === "undefined") {
                return;
            }
            let _fromSegmentConfig = JSON.parse(JSON.stringify(from_print_template.config.segments[designSource.selected_design])),
                _toSegmentConfig = JSON.parse(JSON.stringify(to_print_template.config.segments[designSource.selected_design])),
                newSegmentSource = JSON.parse(JSON.stringify(designSource.segments));

            if (!_fromSegmentConfig.builder_config.safe_box) {
                _fromSegmentConfig.safe_box = {
                    dimension: _fromSegmentConfig.dimension,
                    position: {x: 0, y: 0}
                }
            } else {
                let from_position_x = _fromSegmentConfig.builder_config.safe_box.position.x - _fromSegmentConfig.builder_config.segment_place_config.position.x,
                    from_position_y = _fromSegmentConfig.builder_config.safe_box.position.y - _fromSegmentConfig.builder_config.segment_place_config.position.y;

                let from_ratio_x = _fromSegmentConfig.dimension.width / _fromSegmentConfig.builder_config.segment_place_config.dimension.width,
                    from_ratio_y = _fromSegmentConfig.dimension.height / _fromSegmentConfig.builder_config.segment_place_config.dimension.height;

                let from_ratio = (from_ratio_x > from_ratio_y) ? from_ratio_y : from_ratio_x;
                _fromSegmentConfig.safe_box = {
                    position: {
                        x: from_position_x * from_ratio,
                        y: from_position_y * from_ratio
                    },
                    dimension: {
                        width: _fromSegmentConfig.builder_config.safe_box.dimension.width * from_ratio,
                        height: _fromSegmentConfig.builder_config.safe_box.dimension.height * from_ratio
                    }
                }
            }

            if (!_toSegmentConfig.builder_config.safe_box) {
                _toSegmentConfig.safe_box = {
                    dimension: _toSegmentConfig.dimension,
                    position: {x: 0, y: 0}
                }
            } else {
                let to_position_x = _toSegmentConfig.builder_config.safe_box.position.x - _toSegmentConfig.builder_config.segment_place_config.position.x,
                    to_position_y = _toSegmentConfig.builder_config.safe_box.position.y - _toSegmentConfig.builder_config.segment_place_config.position.y;

                let to_ratio_x = _toSegmentConfig.dimension.width / _toSegmentConfig.builder_config.segment_place_config.dimension.width,
                    to_ratio_y = _toSegmentConfig.dimension.height / _toSegmentConfig.builder_config.segment_place_config.dimension.height;

                let to_ratio = (to_ratio_x > to_ratio_y) ? to_ratio_y : to_ratio_x;
                _toSegmentConfig.safe_box = {
                    position: {
                        x: to_position_x * to_ratio,
                        y: to_position_y * to_ratio
                    },
                    dimension: {
                        width: _toSegmentConfig.builder_config.safe_box.dimension.width * to_ratio,
                        height: _toSegmentConfig.builder_config.safe_box.dimension.height * to_ratio
                    }
                }
            }

            let new_ratio_x = _toSegmentConfig.safe_box.dimension.width / _fromSegmentConfig.safe_box.dimension.width,
                new_ratio_y = _toSegmentConfig.safe_box.dimension.height / _fromSegmentConfig.safe_box.dimension.height;

            let new_ratio = (new_ratio_x > new_ratio_y) ? new_ratio_y : new_ratio_x;

            let new_dimension_w = _fromSegmentConfig.dimension.width * new_ratio,
                new_dimension_h = _fromSegmentConfig.dimension.height * new_ratio,
                new_safe_box_dimension_w = _fromSegmentConfig.safe_box.dimension.width * new_ratio,
                new_safe_box_dimension_h = _fromSegmentConfig.safe_box.dimension.height * new_ratio,
                new_safe_box_position_x = _fromSegmentConfig.safe_box.position.x * new_ratio,
                new_safe_box_position_y = _fromSegmentConfig.safe_box.position.y * new_ratio;

            newSegmentSource[designSource.selected_design].source.dimension.width = newSegmentSource[designSource.selected_design].source.dimension.width * new_ratio;
            newSegmentSource[designSource.selected_design].source.dimension.height = newSegmentSource[designSource.selected_design].source.dimension.height * new_ratio;
            newSegmentSource[designSource.selected_design].source.position.x = newSegmentSource[designSource.selected_design].source.position.x * new_ratio;
            newSegmentSource[designSource.selected_design].source.position.y = newSegmentSource[designSource.selected_design].source.position.y * new_ratio;

            newSegmentSource[designSource.selected_design].source.position.x = newSegmentSource[designSource.selected_design].source.position.x + _toSegmentConfig.safe_box.position.x - new_safe_box_position_x + (_toSegmentConfig.safe_box.dimension.width - new_safe_box_dimension_w) / 2;
            newSegmentSource[designSource.selected_design].source.position.y = newSegmentSource[designSource.selected_design].source.position.y + _toSegmentConfig.safe_box.position.y - new_safe_box_position_y + (_toSegmentConfig.safe_box.dimension.height - new_safe_box_dimension_h) / 2;
            newSegmentSource[designSource.selected_design].source.timestamp = (new Date()).getTime();

            return newSegmentSource;

        }

        function insertVariants(productTypeKey) {
            let _variants = $("input[name='product_type_variant[" + productTypeKey + "][]']", '.campaign-variants-selector').serializeObject();
            let container = $('.variant-generated[data-product-type="' + productTypeKey + '"]');

            if (Object.keys(_variants).length < 1) {
                alert('Please add at least one variant !');
                return false;
            }

            _generateVariantList(products.product_types[productTypeKey], container, true);
            _fillCustomMockup();

            $.unwrapContent('catalogCampaignVariantSelector');

            let _productTypeVariants = [];

            if (typeof products.product_types?.[productTypeKey] !== "undefined") {
                const data = products.product_types[productTypeKey];

                $.each(data?.product_variant, function (key, value) {
                    if (_productTypeVariants.indexOf(value.id) < 0) {
                        _productTypeVariants.push(value.id);
                    }
                });
            }

            $.ajax({
                type: 'POST',
                url: $.base_url + '/catalog/backend_campaign/getPrintTemplate/hash/' + OSC_HASH,
                data: {
                    product_type_id: productTypeKey,
                    product_type_variant: _productTypeVariants
                },
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert('Error! Please try again.');
                    } else {
                        _renderPrintTemplateFrm(productTypeKey, response.data);
                        container.trigger('expand');
                    }
                }
            });
        }

        function _modifiedTimestamp(data) {
            if (!data) {
                data = _fetchCurrentDesign();
                if (data === null) {
                    return;
                }
            }

            data.source.timestamp = (new Date()).getTime();
        }

        window._addVariants = function () {
            $(this).click(function () {
                let _productType = $('input[name="product_type_id"]').val(),
                    _productTypeId = $('input[name="product_type_id"]').data('id'),
                    _variantList = $('.variant-items'),
                    _optionLength = parseInt($("input[name='option-length']").val());

                /**
                 * Create the temporary obj of option values title by key
                 * @type {{}}
                 */
                let optionValueTitle = {},
                    colorHexData = {};
                if (products.product_types[_productType].options.length > 0) {
                    for (let i = 0; i < products.product_types[_productType].options.length; i++) {
                        if (products.product_types[_productType].options[i].values.length > 0) {
                            for (let j = 0; j < products.product_types[_productType].options[i].values.length; j++) {

                                if (products.product_types[_productType].options[i].type === 'color') {
                                    colorHexData[products.product_types[_productType].options[i].values[j].key] = products.product_types[_productType].options[i].values[j].meta_data.hex;
                                }

                                optionValueTitle[products.product_types[_productType].options[i].values[j].key] = products.product_types[_productType].options[i].values[j].title.replace(/&quot;/g, '"');
                            }
                        }
                    }
                }

                /**
                 * Get list options not LAST option
                 */
                let _lastOpt = [];
                $(".option-selector.multi-option").find("input[name='option" + (_optionLength - 1) + "']:checked").each(function () {
                    _lastOpt.push({
                        key: $(this).data('option-value-key'),
                        value: $(this).val()
                    });
                });

                let _variantDataList = [];
                if (_optionLength > 1) {
                    /** Temp for create variant list when last option multi select */
                    let _opts = [];
                    for (let i = 0; i < (_optionLength - 1); i++) {
                        $("input[name='option" + i + "']:checked").each(function () {
                            if (typeof $(this).val() !== 'undefined') {
                                _opts.push({
                                    key: $(this).data('option-value-key'),
                                    value: $(this).val()
                                });
                            }
                        });
                    }

                    for (let i = 0; i < _opts.length; i++) {
                        if (_optionLength > 2) {
                            for (let j = 0; j < _lastOpt.length; j++) {
                                _variantDataList.push([..._opts, _lastOpt[j]]);
                            }
                        } else {
                            for (let j = 0; j < _lastOpt.length; j++) {
                                _variantDataList.push([_opts[i], _lastOpt[j]]);
                            }
                        }
                    }
                } else {
                    for (let i = 0; i < _lastOpt.length; i++) {
                        _variantDataList.push([_lastOpt[i]]);
                    }
                }

                /**
                 * Render variant list
                 */
                let _rowKeys = [],
                    _productTypeKey = products.product_types[_productType].key;

                for (let i = 0; i < _variantDataList.length; i++) {
                    let _option = [],
                        _optionManual = [],
                        variantOption = '',
                        _itemLineKey = '',
                        _lineKeys = [];

                    let itemRow = $('<div />').addClass('variant-row'), variantKey = _productTypeId + '/';

                    for (let j = 0; j < _variantDataList[i].length; j++) {
                        /** Temp list for compare with the variant has added */
                        _option.push(_variantDataList[i][j].value);
                        let _thisCol = '';

                        if (autoSelectOptions[_productTypeKey].length > 0) {
                            if (autoSelectOptions[_productTypeKey].indexOf(_variantDataList[i][j].value) === -1) {
                                _optionManual.push(_variantDataList[i][j].value);
                                if (j === 0) {
                                    _itemLineKey += _variantDataList[i][j].key;
                                } else {
                                    _itemLineKey += '_' + _variantDataList[i][j].key;
                                }
                            }
                        } else {
                            if (j === 0) {
                                _itemLineKey += _variantDataList[i][j].key;
                            } else {
                                _itemLineKey += '_' + _variantDataList[i][j].key;
                            }
                        }

                        if (j === 0) {
                            variantKey += _variantDataList[i][j].key;
                            variantOption = _variantDataList[i][j].value;
                        } else {
                            variantKey += '_' + _variantDataList[i][j].key;
                            variantOption += ',' + _variantDataList[i][j].value;
                        }

                        if (autoSelectOptions[_productTypeKey].length > 0) {
                            if (_lineKeys.indexOf(_itemLineKey) === -1) {
                                _lineKeys.push(_itemLineKey);
                                _thisCol = $('<span />').css('width', 'calc((100% - ' + (_optionLength * 30 + 110) + 'px)/' + _optionLength + ')').text(optionValueTitle[_variantDataList[i][j].value]).appendTo(itemRow);
                            } else {
                                _thisCol = $('<span />').css('width', 'calc((100% - ' + (_optionLength * 30 + 110) + 'px)/' + _optionLength + ')').text('All').appendTo(itemRow);
                            }
                        } else {
                            _thisCol = $('<span />').css('width', 'calc((100% - ' + (_optionLength * 30 + 110) + 'px)/' + _optionLength + ')').text(optionValueTitle[_variantDataList[i][j].value]).appendTo(itemRow);
                        }

                        /**
                         * Replace color option from text to label
                         */
                        if (Object.keys(colorHexData).length > 0 && Object.keys(colorHexData).indexOf(_variantDataList[i][j].value) > -1) {
                            _thisCol.addClass('color-collumn').addClass(_variantDataList[i][j].value).html($('<label />').css('background-color', colorHexData[_variantDataList[i][j].value]));
                        }
                    }

                    let _invalid = false;
                    if (typeof products.product_types[_productType].product_variant !== 'undefined') {
                        for (let i = 0; i < products.product_types[_productType].product_variant.length; i++) {
                            if (compareArray(_option, products.product_types[_productType].product_variant[i].option)) {
                                _invalid = true;
                                break;
                            }
                        }
                    }

                    let _optionId = 0;
                    for (let i = 0; i < products.product_types[_productType].product_type_variant.length; i++) {
                        if (compareArray(_option, products.product_types[_productType].product_type_variant[i].option)) {
                            _optionId = products.product_types[_productType].product_type_variant[i].id;
                        }
                    }

                    if (_optionId === 0) _invalid = true; /** _option not in  product_type_variant, don't create variant */

                    if (!_invalid) {
                        if (_option.length < (_optionLength - 1)) {
                            alert('Variant not valid, please select enough options !');
                            return false;
                        } else {
                            let item = {};
                            if (typeof products.product_types[_productType].product_variant === 'undefined') {
                                products.product_types[_productType].product_variant = [];
                            }
                            item['ukey'] = variantKey;
                            item['option'] = Array.from(_option);
                            item['id'] = _optionId;

                            products.product_types[_productType].product_variant.push(item);

                            _modifiedTimestamp();

                            $('<span />').append($.renderIcon('trash-alt-regular')).click(function () {
                                itemRow.trigger('delete');
                            }).appendTo(itemRow);
                            $('<input />').attr({
                                'type': 'hidden',
                                'name': 'product_type_variant[' + _productType + '][]',
                                'value': item['id'],
                                'data-ukey': item['ukey'],
                                'data-option': variantOption
                            }).prependTo(itemRow);

                            if (_itemLineKey !== '' && _rowKeys.indexOf(_itemLineKey) === -1) {
                                _rowKeys.push(_itemLineKey);
                                itemRow.appendTo(_variantList);

                                _tickOptions();

                                itemRow.bind('update', function (e, _data) {
                                    _tickOptions();
                                }).bind('delete', function () {
                                    if (typeof products.product_types[_productType].product_variant !== 'undefined') {
                                        for (let i = 0; i < products.product_types[_productType].product_variant.length; i++) {
                                            if (autoSelectOptions[_productTypeKey].length > 0) {
                                                /* Remove All variant by manual options */
                                                for (let j = 0; j < autoSelectOptions[_productTypeKey].length; j++) {
                                                    let _tmpManual = Array.from(_optionManual);
                                                    _tmpManual.push(autoSelectOptions[_productTypeKey][j]);
                                                    if (compareArray(_tmpManual, products.product_types[_productType].product_variant[i].option)) {
                                                        products.product_types[_productType].product_variant.splice(i, 1);
                                                    }
                                                }
                                            } else {
                                                if (compareArray(_option, products.product_types[_productType].product_variant[i].option)) {
                                                    products.product_types[_productType].product_variant.splice(i, 1);
                                                }
                                            }
                                        }
                                    }
                                    itemRow.remove();
                                    _tickOptions();
                                });
                            }
                        }
                    }
                }
            });
        }

        function _makeAllVariants(productTypeKey, container) {
            products.product_types[productTypeKey].product_variant = Array.from(products.product_types[productTypeKey].product_type_variant);
            _tickOptions();
            _generateVariantList(products.product_types[productTypeKey], container);
            _fillCustomMockup();

            _modifiedTimestamp();
        }

        function _renderOptionType(level, config, selector) {
            let selectorBlock = $('<div />').addClass('selector-block').attr({
                'data-level': level,
                'data-option-key': config.key,
                'data-auto-select': config.auto_select
            }).appendTo(selector);
            if (parseInt(config.is_reorder) === 1) {
                selectorBlock.attr('data-reorder', 1).addClass('reorder');
            }
            $.each(config.values, function (index, item) {
                switch (config.type) {
                    case 'input':
                        _renderInputType(config, level, item, selectorBlock);
                        break;
                    case 'color':
                        _renderColorType(config, level, item, selectorBlock);
                        break;
                    case 'checkbox':
                        _renderCheckboxType(config, level, item, selectorBlock);
                        break;
                    case 'select':
                        _renderSelectType(config, level, item, selectorBlock);
                        break;
                    case 'button':
                        _renderButtonType(config, level, item, selectorBlock);
                        break;
                    default:
                        return;
                }
            });
        }

        function _saveApplyReorderToAll(productTypeId, saveType) {
            let _index = products.campaign_config.apply_reorder.indexOf(productTypeId);
            if (saveType) {
                if (_index > -1) {
                    return;
                } else {
                    products.campaign_config.apply_reorder.push(productTypeId);
                }
            } else {
                if (_index > -1) {
                    products.campaign_config.apply_reorder.splice(_index, 1);
                } else {
                    return;
                }
            }
        }

        function _saveReorderOptions(productTypeKey, container, forceMakeReorder = false) {
            let _allVariantsReorder = [],
                _allOptions = [],
                _checkedOptions = [];

            if (typeof VARIANTS_REORDER[productTypeKey] === "undefined" || forceMakeReorder !== false) {
                VARIANTS_REORDER[productTypeKey] = [];
            }

            /**
             * Get current order in  this product type
             */
            let _currentOptionsOrder = [];
            if (VARIANTS_REORDER[productTypeKey].length > 0) {
                for (let i = 0; i < VARIANTS_REORDER[productTypeKey].length; i++) {
                    _currentOptionsOrder.push(VARIANTS_REORDER[productTypeKey][i].option);
                }
            }

            if (VARIANTS_REORDER[productTypeKey].length === 0) {
                /**
                 * Empty VARIANTS_REORDER -> Make all options
                 */
                container.find('.selector-block').each(function () {
                    let _thisLevel = $(this).data('level'),
                        _thisOptionSelector = $(this);

                    _allOptions[_thisLevel] = [];

                    _thisOptionSelector.find('.selector-item').each(function () {
                        _allOptions[_thisLevel].push($(this).attr('data-option-key'));
                    });

                });

                _allVariantsReorder = makeAllVariants(_allOptions);
            } else {
                /**
                 * If all option has un-checked, not auto save when drag drop option
                 */
                let _hasChecked = false;
                container.find('.selector-block').each(function () {
                    let _thisLevel = $(this).data('level');
                    let _optionChecked = $(this).find("input[name='option" + _thisLevel + "']:checked");
                    if (_optionChecked.length > 0) {
                        _hasChecked = true;
                    }
                });

                if (_hasChecked === false) {
                    /** Not auto save */
                    return false;
                } else {
                    /**
                     * If an options can be re-order has any checked option, only make variants with the checked option
                     */
                    container.find('.selector-block').each(function () {
                        let _thisLevel = $(this).data('level'),
                            _canCheck = ($(this).data('reorder') === 1 && $(this).data('auto-select') !== 1);
                        _allOptions[_thisLevel] = [];

                        let _thisOptionSelector = $(this),
                            _optionKey = _thisOptionSelector.data('option-key');

                        /**
                         * First: re-order current data by this option order
                         * @type {*|jQuery|HTMLElement}
                         * @private
                         */
                        if (_canCheck) {
                            let _currentOrder = [],
                                _optionTree = [];
                            _thisOptionSelector.find("input[name='option" + _thisLevel + "']").each(
                                function () {
                                    _currentOrder.push($(this).val());
                                }
                            );
                            if (_currentOrder.length > 0) {
                                for (let i = 0; i < _currentOrder.length; i++) {
                                    for (let j = 0; j < _currentOptionsOrder.length; j++) {
                                        if (_currentOptionsOrder[j].indexOf(_currentOrder[i]) !== -1) {
                                            _optionTree.push(_currentOptionsOrder[j]);
                                        }
                                    }
                                }

                                _currentOptionsOrder = [];
                                for (let i = 0; i < _optionTree.length; i++) {
                                    _currentOptionsOrder.push(_optionTree[i]);
                                }
                            }
                        }


                        _checkedOptions[_thisLevel] = [];
                        _thisOptionSelector.find("input[name='option" + _thisLevel + "']:checked").each(
                            function () {
                                _checkedOptions[_thisLevel].push($(this).val());
                            }
                        );

                        _thisOptionSelector.find('.selector-item').each(function () {
                            if (_canCheck && _checkedOptions[_thisLevel].length > 0) {
                                let _checkedValue = $(this).find("input[name='option" + _thisLevel + "']:checked").val();
                                if (_checkedValue) {
                                    _allOptions[_thisLevel].push(_checkedValue);
                                }
                            } else {
                                _allOptions[_thisLevel].push($(this).attr('data-option-key'));
                            }
                        });
                    });
                }

                _allVariantsReorder = makeAllVariants(_allOptions);
                let _tmpOptions = [],
                    _replace = 0;
                for (let i = 0; i < _currentOptionsOrder.length; i++) {
                    let _contain = false;
                    for (let j = 0; j < _allVariantsReorder.length; j++) {
                        if (compareArray(_currentOptionsOrder[i], _allVariantsReorder[j])) {
                            _contain = true;
                        }
                    }

                    if (_contain === false) {
                        _tmpOptions.push(_currentOptionsOrder[i]);
                    } else {
                        _tmpOptions.push(_allVariantsReorder[_replace]);

                        _replace++;
                    }
                }
                _allVariantsReorder = [];
                _allVariantsReorder = Array.from(_tmpOptions);
            }

            for (let i = 0; i < _allVariantsReorder.length; i++) {
                for (let j = 0; j < products.product_types[productTypeKey].product_variant.length; j++) {
                    if (compareArray(products.product_types[productTypeKey].product_variant[j].option, _allVariantsReorder[i])) {
                        products.product_types[productTypeKey].product_variant[j]['position'] = i + 1;
                        if (VARIANTS_REORDER[productTypeKey].indexOf(products.product_types[productTypeKey].product_variant[j]) < 0) {
                            VARIANTS_REORDER[productTypeKey].push(products.product_types[productTypeKey].product_variant[j]);
                        }
                    }
                }
            }

            /**
             * Re-order this result by position
             */

            _sortArrayByKey(VARIANTS_REORDER[productTypeKey], 'position');
        }

        function _saveAllReorderData(productTypeKey) {
            /**
             * if the form don't have any checked option, save all current order
             */
            let container = $('.option-selector-block');
            let _hasChecked = false;
            container.find('.selector-block').each(function () {
                let _thisLevel = $(this).data('level');
                let _optionChecked = $(this).find("input[name='option" + _thisLevel + "']:checked");
                if (_optionChecked.length > 0) {
                    _hasChecked = true;
                }
            });

            if (_hasChecked === false) {
                _saveReorderOptions(productTypeKey, container, true);
            }


            if (typeof VARIANTS_REORDER[productTypeKey] === "undefined" && typeof products?.product_types[productTypeKey]?.product_variant !== "undefined") {
                VARIANTS_REORDER[productTypeKey] = [];
                for (let i = 0; i < products.product_types[productTypeKey].product_variant.length; i++) {
                    VARIANTS_REORDER[productTypeKey].push(products.product_types[productTypeKey].product_variant[i]);
                }
                _renderVariantSelectFrm(products.product_types[productTypeKey]);
                return false;
            }

            if (VARIANTS_REORDER[productTypeKey].length < 1) {
                //alert('Neither any option is reordered');
                return false;
            }

            /**
             * Validate variants in VARIANTS_REORDER
             */
            let _allProductTypeVariants = [];
            for (let i = 0; i < products.product_types[productTypeKey].product_variant.length; i++) {
                _allProductTypeVariants.push(products.product_types[productTypeKey].product_variant[i].id);
            }

            let _allReorderVariants = [];
            for (let i = 0; i < VARIANTS_REORDER[productTypeKey].length; i++) {
                _allReorderVariants.push(VARIANTS_REORDER[productTypeKey][i].id);
            }

            let _needRemove = [];
            for (let i = 0; i < _allReorderVariants.length; i++) {
                if (_allProductTypeVariants.indexOf(_allReorderVariants[i]) < 0) {
                    _needRemove.push(_allReorderVariants[i]);
                }
            }

            if (_needRemove.length > 0) {
                let _tmpVariants = [];
                for (let i = 0; i < VARIANTS_REORDER[productTypeKey].length; i++) {
                    if (_needRemove.indexOf(VARIANTS_REORDER[productTypeKey][i].id) < 0) {
                        _tmpVariants.push(VARIANTS_REORDER[productTypeKey][i]);
                    }
                }
                if (_tmpVariants.length > 0) {
                    VARIANTS_REORDER[productTypeKey] = [];
                    VARIANTS_REORDER[productTypeKey] = Array.from(_tmpVariants);
                }
            }

            products.product_types[productTypeKey].product_variant = [];
            products.product_types[productTypeKey].product_variant = Array.from(VARIANTS_REORDER[productTypeKey]);

            insertVariants(productTypeKey);
        }

        function _initReorderItem(item, list_selector, item_selector, helper_class, helper_callback, finish_callback) {
            item.mousedown(function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                let currentX = e.pageX,
                    currentY = e.pageY;

                let item = $(this),
                    list = item.closest(list_selector);

                $(helper_class).remove();

                list.find('.reordering').removeClass('reordering');

                let helper = item.clone()
                    .removeAttr('class')
                    .addClass(helper_class)
                    .css({
                        display: 'block',
                        position: 'absolute',
                        width: item.width() + 'px',
                        height: item.height() + 'px',
                        marginLeft: ((item[0].getBoundingClientRect().x + $(window).scrollLeft()) - e.pageX) + 'px',
                        marginTop: ((item[0].getBoundingClientRect().y + $(window).scrollTop()) - e.pageY) + 'px'
                    }).appendTo(document.body);

                if (helper_callback) {
                    helper_callback(helper, item);
                }

                let _isMove = false;
                $(document).unbind('.itemReorder').bind('mousemove.itemReorder', function (e) {
                    let x = Math.abs(e.pageX - currentX),
                        y = Math.abs(e.pageY - currentY);

                    if (x >= 10 || y >= 10) {
                        _isMove = true;
                    }

                    if (_isMove) {
                        helper.swapZIndex();
                        item.addClass('reordering');
                        $(document.body).addClass('dragging');

                        let collection = list.find(item_selector);

                        var scroll_top = $(window).scrollTop();
                        var scroll_left = $(window).scrollLeft();

                        collection.each(function () {
                            if (this === item[0]) {
                                return;
                            }

                            var rect = this.getBoundingClientRect();

                            var item_top = rect.y + scroll_top;
                            var item_left = rect.x + scroll_left;

                            if (e.pageY < item_top) {
                                return false;
                            }

                            if (e.pageY > item_top && e.pageY < (item_top + rect.height)) {
                                if (e.pageX > item_left && e.pageX < (item_left + rect.width)) {
                                    if (this.previousSibling === item[0] && this.getAttribute('data-placebefore') !== '1') {
                                        item.insertAfter(this);
                                    } else {
                                        item.insertBefore(this);
                                    }
                                }
                            }
                        });

                        helper.css({top: e.pageY + 'px', left: e.pageX + 'px'}).css({});
                    }
                }).bind('mouseup.itemReorder', function (e) {
                    $(document).unbind('.itemReorder');
                    helper.remove();
                    if (_isMove) {
                        $(document.body).removeClass('dragging');
                        item.removeClass('reordering').trigger('reordered');
                        if (finish_callback) {
                            finish_callback(item);
                        }
                    }
                });
            });
        }

        function _initReorderOtherOptions(productTypeKey, _thisOptionSelector) {
            if (VARIANTS_REORDER[productTypeKey].length === 0) {
                return;
            }

            let _thisLevel = _thisOptionSelector.data('level'),
                _variantsContain = [],
                _allCheckedOptions = [],
                container = _thisOptionSelector.closest('.option-selector-block');

            _allCheckedOptions[_thisLevel] = [];

            _thisOptionSelector.find('.selector-item').each(function () {
                let _checkedValue = $(this).find("input[name='option" + _thisLevel + "']:checked").val();
                if (_checkedValue) {
                    _allCheckedOptions[_thisLevel].push(_checkedValue);
                }
            });

            for (let i = 0; i < VARIANTS_REORDER[productTypeKey].length; i++) {
                for (let j = 0; j < _allCheckedOptions[_thisLevel].length; j++) {
                    if (VARIANTS_REORDER[productTypeKey][i].option.indexOf(_allCheckedOptions[_thisLevel][j]) !== -1) {
                        _variantsContain.push(VARIANTS_REORDER[productTypeKey][i].option);
                    }
                }
            }

            let __options = {};
            if (_variantsContain.length > 0) {
                _variantsContain.map(function (variation) {
                    Object.keys(variation).map(function (attribute) {
                        if (!__options[attribute]) {
                            __options[attribute] = [];
                        }

                        if (variation[attribute] && __options[attribute].indexOf(variation[attribute]) === -1) {
                            __options[attribute].push(variation[attribute]);
                        }
                    });
                });

                /**
                 * Make list items order by __options
                 * @type {*[]}
                 */

                for (let i = 0; i < Object.keys(__options).length; i++) {
                    if (i !== _thisLevel) {
                        let _avairibleItems = [];
                        let _thisOptionSelectorBlock = $('.selector-block[data-level="' + i + '"]');
                        for (let j = 0; j < __options[i].length; j++) {
                            let _thisItem = _thisOptionSelectorBlock.find('.selector-item[data-option-key="' + __options[i][j] + '"]');
                            _avairibleItems.push(_thisItem);
                            _thisItem.remove();
                        }

                        let _itemsNotIn = [];
                        _thisOptionSelectorBlock.find('.selector-item').each(function () {
                            _itemsNotIn.push($(this).removeClass('move'));
                        });
                        /**
                         * Re-builder items in option selector
                         */
                        _thisOptionSelectorBlock.html('');
                        for (let j = 0; j < _avairibleItems.length; j++) {
                            _thisOptionSelectorBlock.append(_avairibleItems[j]);
                        }

                        /**
                         * Init drag drop to reorder options
                         */
                        _thisOptionSelectorBlock.find('.selector-item').each(function () {
                            $(this).addClass('move').find('input').prop('checked', false);
                            let _label = $(this).find('label');
                            _label.css({width: _label.outerWidth() + 0.5, height: _label.outerHeight()});
                            let _class = $(this).attr('class');

                            _initReorderItem($(this), '.selector-block', '.selector-item', 'option-reorder-helper',
                                function (helper) {
                                    helper.addClass(_class);
                                    helper.find('input').remove();
                                    $('.btn-save-reorder').text('Save');
                                },
                                function () {
                                    _saveReorderOptions(productTypeKey, container);
                                });
                        });

                        if (_itemsNotIn.length > 0) {
                            for (let j = 0; j < _itemsNotIn.length; j++) {
                                _thisOptionSelectorBlock.append(_itemsNotIn[j]);
                            }
                        }
                    }

                }


            }
        }

        function _bindReorderOption(productTypeData) {
            if (typeof productTypeData?.product_variant === "undefined") {
                alert('Please add variants for this product type before reorder the options!');
                $('.reorder-enable').trigger('click');
                return false;
            }

            _renderVariantSelectFrm(productTypeData);


            /**
             * Push OPTION_REORDER default from products.product_types[productTypeKey].product_variant
             * @type {*[]}
             * @private
             */

            let container = $('.campaign-variants-selector'),
                _optionVariants = [];

            $.each(productTypeData.product_variant, function (index, data) {
                _optionVariants.push(data.option);
            });

            if (typeof VARIANTS_REORDER[productTypeData.key] === "undefined") {
                VARIANTS_REORDER[productTypeData.key] = Array.from(productTypeData.product_variant);
            }

            let __options = {}; /** Array of options has created in variant list  */
            if (_optionVariants.length > 0) {
                _optionVariants.map(function (variation) {
                    Object.keys(variation).map(function (attribute) {
                        if (!__options[attribute]) {
                            __options[attribute] = [];
                        }

                        if (variation[attribute] && __options[attribute].indexOf(variation[attribute]) === -1) {
                            __options[attribute].push(variation[attribute]);
                        }
                    });
                });
            }

            if (productTypeData.options.length > 0) {
                let hasReorderOpts = false;
                let _avairibleItems = [];

                container.find('.selector-block').each(function () {
                    let _thisLevel = $(this).data('level');

                    let _thisOptionSelector = $(this),
                        _optionKey = _thisOptionSelector.data('option-key');


                    /**
                     * Make list items order by __options
                     * @type {*[]}
                     */
                    _avairibleItems[_thisLevel] = [];
                    for (let i = 0; i < __options[_thisLevel].length; i++) {
                        let _thisItem = _thisOptionSelector.find('.selector-item[data-option-key="' + __options[_thisLevel][i] + '"]');
                        _avairibleItems[_thisLevel].push(_thisItem);
                        _thisItem.remove();
                    }

                    /**
                     * Re-builder items in option selector
                     */
                    _thisOptionSelector.html('');
                    for (let i = 0; i < _avairibleItems[_thisLevel].length; i++) {
                        _thisOptionSelector.append(_avairibleItems[_thisLevel][i]);
                    }

                    _thisOptionSelector.find('.item-added').remove();

                    /**
                     * Only select one option (Not support multi checkbox)
                     */
                    if ($(this).data('reorder') === 1) {
                        hasReorderOpts = true;

                        _thisOptionSelector.closest('.option-selector').show();

                        if (_thisOptionSelector.closest('.option-selector').hasClass('multi-option')) {
                            _thisOptionSelector.find('input').each(function () {
                                $(this).on('change', function () {
                                    if ($(this).is(':checked')) {
                                        _thisOptionSelector.find('input').not($(this)).prop('checked', false);
                                        _initReorderOtherOptions(productTypeData.key, _thisOptionSelector);
                                    }
                                });
                            });
                        }

                        /**
                         * Init drag drop to reorder options
                         */
                        _thisOptionSelector.find('.selector-item').each(function () {
                            $(this).addClass('move').find('input').prop('checked', false);
                            let _label = $(this).find('label');
                            _label.css({width: _label.outerWidth() + 0.5, height: _label.outerHeight()});
                            let _class = $(this).attr('class');

                            _initReorderItem($(this), '.selector-block', '.selector-item', 'option-reorder-helper',
                                function (helper) {
                                    helper.addClass(_class);
                                    helper.find('input').remove();
                                },
                                function () {
                                    _saveReorderOptions(productTypeData.key, container);
                                });
                        });
                    }
                });

                /**
                 * init actions
                 */
                if (hasReorderOpts) {
                    container.find('.btn-group').hide();
                    container.find('.variants-list').hide();

                    let optionSelectorBlock = container.find('.option-selector-block'),
                        _buttonGroup = $('<div />').addClass('btn-group save-reorder-options').appendTo(optionSelectorBlock);

                    if (form.data('apply_reorder') === 1) {
                        let applyReorderOption = $('<div />').addClass('apply-for-all').appendTo(optionSelectorBlock);
                        $('<input />').attr({'type': 'checkbox','name': 'reorder_form_apply_reorder_' + productTypeData.id, 'id': 'reorder_form_apply_reorder_' + productTypeData.id, 'value': productTypeData.id})
                            .prependTo($('<label />').attr('data-tip', 'Warning! This option will apply this re-order option of product type "' + productTypeData.name + '" to all product type in all campaign!').text('Apply reorder for all campaign').appendTo(applyReorderOption))
                            .on('change', function() {
                                _saveApplyReorderToAll(productTypeData.id, $('#reorder_form_apply_reorder_' + productTypeData.id).is(':checked'));
                            }).prop('checked', (products.campaign_config.apply_reorder.indexOf(productTypeData.id) > -1));
                    }

                    $('<button />').addClass('btn btn-outline').text('Reset').click(function () {
                        delete VARIANTS_REORDER[productTypeData.key];
                        _bindReorderOption(productTypeData);
                    }).appendTo(_buttonGroup);

                    $('<button />').addClass('btn btn-secondary btn-save-reorder').text('Save').click(function () {
                        _saveAllReorderData(productTypeData.key);
                    }).appendTo(_buttonGroup);

                }
            }
        }

        function _renderVariantSelectFrm(data) {
            _allVariants = data.product_type_variant;
            current_product_type = data.key;

            if (_allVariants.length < 1) {
                alert('This product type not include any variant');
                product_panel.find('.product[data-key="' + data.key + '"]').trigger('delete');
                return false;
            }

            $.unwrapContent('catalogCampaignVariantSelector');

            let modal = $('<div />').addClass('osc-modal').width(1000),
                header = $('<header />').appendTo(modal),
                headTitle = $('<div />').addClass('title').html('Select Options').appendTo($('<div />').addClass('main-group').appendTo(header));
            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogCampaignVariantSelector');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal),
                container = $('<div />').addClass('campaign-variants-selector').appendTo(modal_body),
                optionSelector = $('<div/>').addClass('option-selector-block').appendTo(container),
                variantsList = $('<div/>').addClass('variants-list').appendTo(container);

            let variantTitle = $('<div />').addClass('title container-title').html('Variants').appendTo(variantsList);

            var _variantItems = $('<div />').addClass('variant-items').appendTo(variantsList),
                _variantLabel = $('<div />').addClass('variant-row title').appendTo(_variantItems);

            if (data.options.length > 0) {
                autoSelectOptions[data.key] = [],
                    manualSelectOption[data.key] = [];

                $.each(data.options, function (level, config) {
                    if (parseInt(config.auto_select) === 1) { /* 1: Auto select all variant in this option. Don't show this option in variants select box */
                        for (let i = 0; i < config.values.length; i++) {
                            autoSelectOptions[data.key].push(config.values[i].key);
                        }
                    } else {
                        for (let i = 0; i < config.values.length; i++) {
                            manualSelectOption[data.key].push(config.values[i].key);
                        }
                    }

                });

                $('<input />').attr({
                    'name': 'option-length',
                    'type': 'hidden',
                    'value': data.options.length
                }).appendTo(optionSelector);
                $('<input />').attr({
                    'name': 'product_type_id',
                    'type': 'hidden',
                    'value': data.key,
                    'data-id': data.id
                }).appendTo(optionSelector);

                var _numberVariants = 1;

                let _optionReorder = [];
                $.each(data.options, function (level, config) {
                    config.multi_select = 0;

                    $('<span />').css('width', 'calc((100% - ' + (data.options.length * 30 + 110) + 'px)/' + data.options.length + ')').text(config.title).appendTo(_variantLabel);

                    _numberVariants = _numberVariants * config.values.length;
                    $("<input />").attr({
                        'type': 'hidden',
                        'id': 'total_' + config.key + '_options',
                        'value': config.values.length
                    }).appendTo(optionSelector);

                    if ((manualSelectOption[data.key].length > 0 && autoSelectOptions[data.key].length > 0) || parseInt(config.auto_select) === 1) {
                        config.multi_select = 1;
                    }

                    let _multiOptionClass = '';
                    if (data.options.length === parseInt(level + 1) || config.multi_select === 1) _multiOptionClass = 'multi-option';
                    var _optionTypeSelector = $('<div />').addClass('option-selector ' + _multiOptionClass).appendTo(optionSelector);

                    $('<label />').text(config.title + ':').attr({
                        'data-id': config.id,
                        'data-key': config.key
                    }).appendTo(_optionTypeSelector);

                    _renderOptionType(level, config, _optionTypeSelector);
                    if (parseInt(config.auto_select) === 1) {
                        _optionTypeSelector.hide();
                    }
                });
                $('<span />').text('').appendTo(_variantLabel);

                var _buttonGroup = $('<div />').addClass('btn-group save-variants-group').appendTo(optionSelector);
                $("<input />").attr({
                    'type': 'hidden',
                    'id': 'total_variants',
                    'value': _numberVariants
                }).appendTo(_buttonGroup);
                $('<button />').addClass('btn btn-secondary').attr({
                    'data-insert-cb': '_addVariants',
                    'data-product-type': data.key
                }).text('Add Variants').appendTo(_buttonGroup);
                $('<button />').addClass('btn btn-primary').text('Make all Variants').click(function () {
                    _makeAllVariants(data.key, _variantItems);
                }).appendTo(_buttonGroup);

                var _buttonSaveGroup = $('<div />').addClass('btn-group').insertAfter(variantsList);
                $('<button />').addClass('btn btn-outline').attr({'data-product-type': data.key}).text('Cancel').click(function () {

                    $.unwrapContent('catalogCampaignVariantSelector');
                }).appendTo(_buttonSaveGroup);
                $('<button />').addClass('btn btn-secondary').attr({'data-product-type': data.key}).text('Save').click(function () {
                    let productTypeKey = $(this).data('product-type');
                    insertVariants(productTypeKey);
                })
                    .appendTo(_buttonSaveGroup);

                _generateVariantList(data, _variantItems);
                setTimeout(function () {
                    _tickOptions();
                }, 100);

            }

            $.wrapContent(modal, {key: 'catalogCampaignVariantSelector'});
            modal.moveToCenter().css('top', '100px');
        }

        function _fetchCurrentDesign(force_make_design) {
            var campaignConfig = {};
            if (current_print_template) {
                if (typeof products.campaign_config.print_template_config[current_print_template] !== 'undefined') {
                    campaignConfig = products.campaign_config.print_template_config[current_print_template];
                } else {
                    if (typeof printTemplateArgs[current_print_template] !== 'undefined') {
                        $.each(printTemplateArgs[current_print_template].config.segments, function (key, config) {
                            products.campaign_config.print_template_config[current_print_template].segments[key] = {};
                        });
                    }
                }
            }

            if (typeof campaignConfig.segments !== 'undefined' && typeof campaignConfig.segments[campaignConfig.selected_design] !== 'undefined') {
                return campaignConfig.segments[campaignConfig.selected_design];
            } else if (force_make_design) {
                if (typeof campaignConfig.segments === 'undefined') {
                    campaignConfig.segments = {};
                }

                if (typeof campaignConfig.segments[campaignConfig.selected_design] === 'undefined') {
                    campaignConfig.segments[campaignConfig.selected_design] = {};
                }

                return campaignConfig.segments[campaignConfig.selected_design];
            }

            return null;
        }

        function _cleanDesignScene() {
            design_scene.html('')
                .removeAttr('data-type')
                .removeAttr('data-print-template-id')
                .removeAttr('data-design')
                .removeAttr('style');

            design_tabs.html('');
            sameDesignApply.html('');
        }

        function _activeProductTypePanel(itemKey) {
            let product = null,
                itemLists = product_panel.find('.items-list');
            if (!itemKey) {
                product = itemLists.find('.product').first();
            } else {
                product = itemLists.find('.product').last();
            }
            itemLists.find('.product').each(function () {
                $(this).trigger('collapse');
            });
            product.trigger('expand');
        }

        function _applySameDesign() {
            if (products.campaign_config.print_template_config[current_print_template].apply_other_face == 1) {
                design_tabs.hide();
            } else {
                design_tabs.show();
            }
        }

        function _showAllCustomMockup(mockupData, productTypeId) {
            const modalKey = 'showAllCustomMockupModal',
                images = mockupData.variants[productTypeId].images,
                videos = mockupData.variants[productTypeId].videos || [];

            const __renderMockupItem = (image) => {
                if (!image) return false;
                const itemElm = $('<div />').attr('id', image.fileId || image.id);
                const _render = (image) => {
                    itemElm.empty();
                    itemElm.removeClass();
                    itemElm.addClass("mockup-item");

                    if (image.isVideo) {
                        itemElm.addClass('is-video').html(`<video src="${image.url}" poster="${image.thumbnail || ''}" />`);
                    } else {
                        itemElm.css('background-image', `url(${image.url})`);
                    }

                    const removeBtn = $('<span />').addClass("remove-item").appendTo(itemElm);
                    removeBtn.click((e) => {
                        e.stopPropagation();
                        if (image.isVideo) {
                            __onRemoveMockupVideo(image);
                        } else {
                            __onRemoveMockupImage(image);
                        }
                        itemElm.remove();
                        let count = mockupsWrapper.find('.mockup-item').length;
                        header.find('.show-all-mockup-count').text('(' + count + ')');
                        if (count === 0) {
                            $.unwrapContent(modalKey);
                        }
                    })
                    // const variantCount = Object.keys(image.variantIds).length
                    // $('<tag />').text(variantCount).addClass("tag").appendTo(itemElm);
                }
                _render(image)
                itemElm.bind("update", function (e, image) {
                    _render(image)
                })
                return itemElm;
            }

            const __onRemoveMockupImage = (mockup) => {
                const mockupId = mockup.fileId ? mockup.fileId:mockup.id;

                mockupData.variants[productTypeId].images = mockupData.variants[productTypeId].images.filter(image => {
                    return mockupId !== image.id;
                });

                let _onremove = [parseInt(productTypeId)];
                if (Object.keys(products.custom_mockup[mockupData.key].auto_options).length > 0) {
                    $.each(products.custom_mockup[mockupData.key].auto_options, (key, data) => {
                        if (data.variants.indexOf(parseInt(productTypeId)) > -1) {
                            _onremove = [..._onremove, ...data.variants];
                            return;
                        }
                    });
                }

                /**
                 * Remove mockup from selected all for this product type
                 */
                if (products.custom_mockup[mockupData.key].selected_all.length > 0) {
                    products.custom_mockup[mockupData.key].selected_all = products.custom_mockup[mockupData.key].selected_all.filter(item => {
                        return item !== mockupId;
                    });
                }

                const _unique = _onremove.filter((v, i, a) => a.indexOf(v) === i)

                $.each(products.custom_mockup[mockupData.key].variants, (variantId, variant) => {
                    if (_unique.indexOf(parseInt(variantId)) > -1) {
                        variant.images = variant.images.filter(image => {
                            const imageId = image.fileId || image.id;
                            const hasInclude = (imageId !== mockupId);
                            if (hasInclude) {
                                delete dataCustomMockups[mockupId].variantIds[variantId];
                            }
                            return hasInclude;
                        });
                    }

                    if(variant.images.length === 0) {
                        delete dataCustomMockups[mockupId].variantIds[variantId];
                    }
                });

                $('.mockups-wrapper').find(`#${mockupId}`).trigger('update', [dataCustomMockups[mockupId]]);

                _postFrmProductPanelRender();
            }

            const __onRemoveMockupVideo = (video) => {
                const videoId = video.fileId || video.id;
                const videoData = window.__videoUploader__getData();

                if (!videoData[videoId]) {
                    alert('Video is not exist! VideoId: ' + videoId);
                }

                const variantIds = { ...videoData[videoId].variantIds };

                delete variantIds[productTypeId];

                $('.video-uploader').find(`#${videoId}`).trigger('item-update', [{ variantIds }]);
            }

            $.unwrapContent(modalKey);

            const modal = $('<div />').addClass('osc-modal').width(688),
                header = $('<header />').appendTo(modal);

            const itemCount = (images.length || 0) + (videos.length || 0);

            $('<div />').addClass('title').html('Show All Mockup <b class="show-all-mockup-count">(' + itemCount + ')</b>').appendTo($('<div />').addClass('main-group').appendTo(header));
            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent(modalKey);
            }).appendTo(header);

            const modalBody = $('<div />').addClass('body post-frm').appendTo(modal),
                mockupsWrapper = $('<div />').addClass('mockups-wrapper').appendTo(modalBody);
            $.wrapContent(modal, {key: modalKey});
            modal.moveToCenter().css('top', '100px');


            videos.forEach(video => {
                const videoItem = __renderMockupItem({ ...video, isVideo: true });
                mockupsWrapper.append(videoItem);
            });

            for (let i = 0; i < images.length; i ++) {
                const item = __renderMockupItem(images[i]);
                mockupsWrapper.append(item);
            }


        }

        function _fillCustomMockup(selector) {
            if (!products.custom_mockup) {
                return;
            }

            const showNum = 3;
            $.each(products.custom_mockup, (index, mockupData) => {
                $.each(mockupData.variants, (productTypeVariantId, variantData) => {
                    let image = variantData.images?.[0];
                    let video = variantData.videos?.[0];

                    let imageCount = variantData.images?.length || 0;
                    let videoCount = variantData.videos?.length || 0;

                    if (video) {
                        $(`<div class="mockup-image mockup-video">
                            <video src="${video.url}" poster="${video.thumbnail || ''}" />
                        </div>`)
                            .on('click', function(){
                                _showAllCustomMockup(mockupData, productTypeVariantId);
                            })
                            .append(`<span class="mockup-image-count">${videoCount}</span>`)
                            .appendTo($('#custom_mockup_' + productTypeVariantId));
                    }

                    if (image) {
                        $('<div />')
                            .addClass("mockup-image")
                            .css('background-image', `url(${image.url})`)
                            .on('click', function(){
                                _showAllCustomMockup(mockupData, productTypeVariantId);
                            })
                            .append(`<span class="mockup-image-count">${imageCount}</span>`)
                            .appendTo($('#custom_mockup_' + productTypeVariantId));
                    }
                })
            });
        }

        function _getMockupsData(products) {
            const mockups = {};

            $.each(products, (productTypeKey, product) => {
                const hasAutoOption = (Object.keys(product.auto_options).length > 0);
                $.each(product.variants, (variantId, variant) => {
                    variant?.images.forEach(image => {
                        const imageId = (image.id || image.fileId)
                        if (!mockups[imageId]) mockups[imageId] = {
                            variantIds: {},
                            hasAutoOption: hasAutoOption,
                            ...image
                        }
                        mockups[imageId].variantIds[variantId] = 1;
                    })
                });
            });
            return mockups;
        }

        function _getMockupVideos(products) {
            const mockupVideos = {};

            if (!products || typeof products !== 'object') return {};

            for (const [, product] of Object.entries(products)) {
                for (const [variantId, variant] of Object.entries(product.variants)) {
                    if (!variant.videos) continue;

                    variant.videos?.forEach(video => {
                        const videoId = video.id || video.fileId;

                        if (!mockupVideos[videoId]) {
                            mockupVideos[videoId] = {
                                variantIds: {},
                                ...video,
                            };
                        }

                        mockupVideos[videoId].variantIds[variantId] = video.variantIds?.[variantId] || video.position || 1;
                    });
                }
            }

            return mockupVideos;
        }

        function _onDeleteProductType (productTypeKey) {

            delete products.custom_mockup[productTypeKey];
            dataCustomMockups = _getMockupsData(products.custom_mockup);

            $.each(dataCustomMockups,(key, mockup) => {
                const id = mockup.fileId || mockup.id;
                mockupsWrapper.find(`#${id}`).trigger('update', [mockup]);
                const variantIds = mockup.variantIds;
                $.each(products.custom_mockup, (productTypeKey, product) => {
                    $.each(product.variants, (variantId, variant) => {
                        if (!variantIds[variantId]) {
                            variant.images = variant.images?.filter(image => {
                                const imageId = image.fileId || image.id;
                                return imageId != id;
                            });
                            return;
                        }
                        ;
                        const found = variant.images.find(image => {
                            const imageId = image.fileId || image.id;
                            return imageId === id;
                        })
                        if (!found) {
                            const image = {url: mockup.url};
                            if (mockup.fileId) image.fileId = mockup.fileId;
                            else image.id = mockup.id;
                            variant.images.push(image)
                        }
                    })
                });
            });
        }

        function _calcVariantsOfMockup(mockup) {
            let _counter = [];
            const _mockupVariant = Object.keys(mockup.variantIds).map(n => Number(n));
            $.each(products.custom_mockup, function(productTypeKey, product) {
                if (Object.keys(product.auto_options).length > 0) {
                    $.each(product.auto_options, (optionKey, optionData) => {
                        let _includeVariant = false;
                        optionData.variants.forEach((variantId, index) => {
                            const _variantIndex = _mockupVariant.indexOf(variantId);
                            if (_variantIndex > -1) {
                                _includeVariant = true;
                                _mockupVariant.splice(_variantIndex, 1);
                                return;
                            }
                        });
                        if (_includeVariant === true) {
                            if (_counter.indexOf(optionKey) < 0) {
                                _counter.push(optionKey);
                            }
                        }
                    });
                }
            });

            _counter = [..._counter, ..._mockupVariant];

            return _counter.length;
        }

        function _postFrmProductPanelRender(activeProductType) {
            let list = product_panel.find('.product-list');
            list.html('');

            let itemList = $('<div />').addClass('items-list').appendTo(list);
            $.each(products.product_types, function (key, productType) {
                let item = $('<div />').attr('data-key', productType.key).addClass('product product-type-reorder-helper').appendTo(itemList),
                    item_block = $("<div />").addClass('product-block').appendTo(item);

                let reOrder = $('<span />').addClass('dragdrop').appendTo(item_block);
                $('<span />').addClass('remove-item').on('click', function (e) {
                    let _thisProductType = $(this).closest('.product'),
                        _ele = $(this),
                        _printTemplateIncluded = [],
                        _otherIds = [];

                    _thisProductType.find(".template-row").each(function () {
                        _printTemplateIncluded.push($(this).data('key'));
                    });

                    product_panel.find('.product').not(_thisProductType).each(function () {
                        $(this).find(".template-row").each(function () {
                            _otherIds.push($(this).data('key'));
                        });
                    });

                    if (_printTemplateIncluded.length > 0) {
                        if (_otherIds.length > 0) {
                            let _removeIds = [];
                            for (let i = 0; i < _printTemplateIncluded.length; i++) {
                                if (_otherIds.indexOf(_printTemplateIncluded[i]) === -1) {
                                    _removeIds.push(_printTemplateIncluded[i]);
                                }
                            }

                            if (_removeIds.length > 0) {
                                for (let i = 0; i < _removeIds.length; i++) {
                                    delete products.campaign_config.print_template_config[_removeIds[i]];
                                    delete printTemplateArgs[_removeIds[i]];
                                }
                            }

                        } else {
                            for (let i = 0; i < _printTemplateIncluded.length; i++) {
                                delete products.campaign_config.print_template_config[_printTemplateIncluded[i]];
                                delete printTemplateArgs[_printTemplateIncluded[i]];
                            }
                        }
                    }

                    delete products.product_types[key];
                    _thisProductType.remove();

                    _onDeleteProductType(key);

                    if (_thisProductType.hasClass('active') || Object.keys(printTemplateArgs).length < 1) {
                        _cleanDesignScene();
                    }

                    _activeProductTypePanel(false);

                }).appendTo(item_block);

                let itemRow = $('<div />').addClass('item-row').appendTo(item_block);

                let campaignDataList = $('<div />').addClass('campaign-data-list').appendTo(item_block),
                    variantContainer = $('<div />').addClass('variants-list').appendTo(campaignDataList),
                    variantHeading = $('<h4 />').addClass('variant-heading').text('Options Variants').appendTo(variantContainer),
                    variantsGenerated = $('<div />').addClass('variant-generated').attr('data-product-type', productType.key).appendTo(variantContainer),
                    printTemplate = $('<div />').addClass('print-templates').attr('data-product-type', productType.key).appendTo(campaignDataList);

                let arrow = $('<span />').addClass('arrow').append($.renderIcon('angle-down-solid')).on('click', function () {
                        //item.trigger('collapse');
                    }),
                    title = $('<div />').addClass('title').text(productType.name).append(arrow).on('click', function () {
                        if ($(this).hasClass("active")) {
                            item.trigger('collapse');
                        } else {
                            product_panel.find('.product').trigger('collapse');
                            item.trigger('expand');
                        }
                    }).appendTo(itemRow);

                _generateVariantList(productType, variantsGenerated, true);
                _renderPrintTemplateList(productType.key);

                $('<h4 />').addClass('heading').text('Print Templates').appendTo(printTemplate);

                $('<span />').addClass('btn btn-create-variant')
                    .text('Add Variants')
                    .prepend($('<ins />').addClass('arrow-plus'))
                    .attr('data-product-type', productType.key)
                    .appendTo(variantHeading).click(function () {
                    _renderVariantSelectFrm(productType);
                });

                item.bind('expand', function () {
                    $(this).addClass('active');
                    arrow.addClass('hasShown');
                    title.addClass('active');
                    setTimeout(function () {
                        printTemplate.find('.template-row').first().trigger('click');
                    }, 100);
                    // $('html, body').animate({
                    //     scrollTop: $(printTemplate).offset().top - 70
                    // }, 100);
                }).bind('collapse', function () {
                    $(this).removeClass('active');
                    arrow.removeClass('hasShown');
                    title.removeClass('active');
                });

                _initReorderProductType(item_block);
            });

            _fillCustomMockup();

            if (activeProductType) {
                _activeProductTypePanel(true);
            } else {
                _activeProductTypePanel(false);
            }

            $('<div />').addClass('btn btn-large btn-primary btn--block mt10 btn__add-new-prt').html('<ins class="arrow-plus"></ins> Add New Product').appendTo(list).click(function () {
                var selected_keys = [];

                $.each(products.product_types, function (key) {
                    selected_keys.push(key);
                });

                let campaign_type = $(".campaign-type").attr('value');

                _renderProductTypeSelector(function (selected_keys) {
                    if (!Array.isArray(selected_keys) || selected_keys.length < 1) {
                        return;
                    }

                    function __removePrintTemplates(productTypeArr) {
                        let _using = [],
                            _notUse = [];
                        product_panel.find('.product').each(function () {
                            let _prt = $(this).data('key');
                            $(this).find(".template-row").each(function () {
                                if (productTypeArr.indexOf(_prt) > -1) {
                                    _using.push($(this).data('key'));
                                } else {
                                    _notUse.push($(this).data('key'));
                                }
                            });
                        });

                        if (_notUse.length > 0) {
                            for (let i = 0; i < _notUse.length; i++) {
                                if (_using.length === 0 || _using.indexOf(_notUse[i]) < 0) {
                                    delete products.campaign_config.print_template_config[_notUse[i]];
                                    delete printTemplateArgs[_notUse[i]];
                                }
                            }
                        }
                    }

                    $.each(products.product_types, function (key) {
                        if (selected_keys.indexOf(key) < 0) {
                            delete products.product_types[key];
                        }
                    });

                    var buff = {};
                    var newKeys = [];

                    selected_keys.forEach(function (selected_key) {
                        if (typeof products.product_types[selected_key] === 'undefined') {
                            newKeys.push(selected_key);
                        }

                        buff[selected_key] = products.product_types[selected_key];
                    });

                    if (newKeys.length > 0) {
                        $.ajax({
                            url: $.base_url + '/catalog/backend_campaign/getProductTypeConfig/hash/' + OSC_HASH,
                            type: 'post',
                            data: {product_type: newKeys},
                            success: function (response) {
                                if (response.result !== 'OK') {
                                    alert('Product Type not valid');
                                    return false;
                                } else {
                                    $.each(response.data, function (key, value) {
                                        buff[key] = value;
                                    });
                                    products.product_types = buff;
                                    __removePrintTemplates(Object.keys(products.product_types));
                                    _postFrmProductPanelRender(true);
                                }
                            }
                        });
                    } else {
                        products.product_types = buff;
                        __removePrintTemplates(Object.keys(products.product_types));
                        _postFrmProductPanelRender();
                    }
                }, selected_keys, campaign_type);
            });

            function _initReorderProductType(item) {
                let container = item.closest('.product-list');

                item.find('.dragdrop').unbind('.product-drag').bind('mousedown.dragdrop', function (e) {
                    if (e.which !== 1) {
                        return;
                    }

                    container.find('.product').removeClass('reordering');

                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    $('.product-dragging').remove();

                    let item_container = item.closest('.product');
                    let helper = item_container.clone()
                        .removeAttr('class')
                        .addClass('product-dragging')
                        .css({
                            position: 'fixed',
                            width: item_container.outerWidth(),
                            marginLeft: ((item_container[0].getBoundingClientRect().x + $(window).scrollLeft()) - e.pageX) + 'px',
                            marginTop: ((item_container[0].getBoundingClientRect().y + $(window).scrollTop()) - e.pageY) + 'px'
                        }).appendTo(document.body);

                    helper.find('.remove-item').remove();
                    helper.find('.campaign-data-list').remove();

                    helper.swapZIndex();

                    item_container.addClass('reordering');

                    $(document.body).addClass('dragging');

                    $(document).unbind('.dragdrop').bind('mousemove.dragdrop', function (e) {
                        var scroll_top = $(window).scrollTop();
                        var scroll_left = $(window).scrollLeft();

                        helper.css({top: (e.pageY - scroll_top) + 'px', left: (e.pageX - scroll_left) + 'px'}).css({});

                        var container_rect = container[0].getBoundingClientRect();

                        if (e.pageY < (container_rect.y + scroll_top) || e.pageY > (container_rect.y + container_rect.height + scroll_top)) {
                            return;
                        }

                        var matched = false;

                        container.find('.product-block').each(function () {
                            if (this === item[0] || ((item[0].compareDocumentPosition(this) & Node.DOCUMENT_POSITION_CONTAINED_BY) === Node.DOCUMENT_POSITION_CONTAINED_BY)) {
                                return;
                            }

                            var rect = this.getBoundingClientRect();

                            if (e.pageY >= (rect.y + scroll_top) && e.pageY <= (rect.y + rect.height + scroll_top)) {
                                matched = true;
                                var sibling_container = $(this).closest('.product');

                                if (e.pageY < (rect.y + scroll_top + (rect.height / 3))) {
                                    item_container.insertBefore(sibling_container);
                                } else {
                                    if (e.pageX > ($(this).find('.item-row')[0].getBoundingClientRect().x + scroll_left)) {
                                        sibling_container.find('> .items-list').prepend(item_container);
                                    } else {
                                        item_container.insertAfter(sibling_container);
                                    }
                                }
                            }

                            if (e.pageY < (rect.y + scroll_top)) {
                                return false;
                            }
                        });

                        if (!matched) {
                            var main_list = container.find('> .items-list');
                            var main_list_rect = main_list[0].getBoundingClientRect();

                            if (e.pageY <= main_list_rect.y + scroll_top) {
                                main_list.prepend(item_container);
                            } else if (e.pageY >= main_list_rect.y + main_list_rect.height + scroll_top) {
                                main_list.append(item_container);
                            }
                        }
                    }).bind('mouseup.itemReorder', function (e) {
                        $(document).unbind('.dragdrop');
                        $(document.body).removeClass('dragging');
                        helper.remove();
                        item_container.removeClass('reordering');

                        let reorder_keys = [];
                        $('div.product').each(function () {
                            reorder_keys.push($(this).attr('data-key'));
                        });
                        $.each(products.product_types, function (key) {
                            if (reorder_keys.indexOf(key) < 0) {
                                delete products.product_types[key];
                            }
                        });
                        let buff = {};
                        reorder_keys.forEach(function (selected_key) {
                            buff[selected_key] = typeof products.product_types[selected_key] === 'undefined' ? {} : products.product_types[selected_key];
                        });
                        products.product_types = buff;
                        if (typeof products.campaign_config.is_reorder !== 'undefined') {
                            products.campaign_config.is_reorder = 1;
                        }
                    });
                });
            }

        }

        function _postFrmDesignSceneRender(productType, printTemplateTitle) {
            current_product_type = productType;
            let printTemplateData = printTemplateArgs[current_print_template];

            product_panel.find('.product-list > .product').removeClass('activated').filter('[data-type="' + current_product_type + '"]').addClass('activated');

            _cleanDesignScene();

            $.each(printTemplateData.config.segments, function (key, design_config) {
                if (products.campaign_config.print_template_config === null) {
                    products.campaign_config.print_template_config = {};
                    products.campaign_config.print_template_config[current_print_template] = {
                        selected_design: key,
                        print_template_id: printTemplateData.print_template_id,
                        title: printTemplateTitle
                    };
                } else {
                    if (typeof products.campaign_config.print_template_config[current_print_template] == 'undefined') {
                        products.campaign_config.print_template_config[current_print_template] = {};
                        products.campaign_config.print_template_config[current_print_template] = {
                            selected_design: key,
                            print_template_id: printTemplateData.print_template_id,
                            title: printTemplateTitle
                        };
                    }
                }

                $('<div />').attr('data-design-key', key).text(typeof design_config.title === 'undefined' ? '' : design_config.title).appendTo(design_tabs).click(function () {
                    design_tabs.find('> *').removeClass('activated');
                    $(this).addClass('activated');
                    products.campaign_config.print_template_config[current_print_template].selected_design = key;

                    var mockupUrl = '';
                    if (design_config.builder_config.layers.length > 0) {
                        for (var i = 0; i < design_config.builder_config.layers.length; i++) {
                            if (design_config.builder_config.layers[i] !== 'main') {
                                mockupUrl = design_config.builder_config.layers[i];
                                break;
                            }
                        }
                    }

                    design_scene.html('').attr({
                        'data-type': current_product_type,
                        'data-print-template-id': current_print_template,
                        'data-design': key
                    }).css('background-image', 'url("' + $.base_url + '/resource/template/core/image/' + mockupUrl + '")');

                    var _RATIO = 0;
                    var frame = $('<div />').addClass('frame').appendTo(design_scene);
                    var scene = $('<div />').addClass('scene').appendTo(frame);

                    let previewFrame = null;
                    $.each(printTemplateArgs[current_print_template].config.preview_config, function (preview_index, preview_data) {
                        $.each(preview_data.config, function (designKey) {
                            if (designKey === key) {
                                if (typeof preview_data.layer[2] !== "undefined") {
                                    let path = mockupUrl.substring(0,mockupUrl.lastIndexOf('/'));
                                    previewFrame = path + '/' + 'frame.png';
                                }
                            }
                        });
                    });

                    if (previewFrame) {
                        $('<img />').addClass('preview-frame').attr('src', $.base_url + '/resource/template/core/image/' + previewFrame).appendTo(design_scene);
                    }

                    let safeBoxRatio = design_scene[0].getBoundingClientRect().width / design_config.builder_config.dimension.width;

                    if (typeof design_config.builder_config.safe_box !== 'undefined' && design_config.builder_config.safe_box !== null) {
                        const safeBox = $('<span />').addClass('safe-box').appendTo(design_scene);
                        safeBox.css({
                            display: design_panel.hasClass('show-helper')?'block':'none',
                            width: (design_config.builder_config.safe_box.dimension.width * safeBoxRatio) + 'px',
                            height: (design_config.builder_config.safe_box.dimension.height * safeBoxRatio) + 'px',
                            top: (design_config.builder_config.safe_box.position.y * safeBoxRatio) + 'px',
                            left: (design_config.builder_config.safe_box.position.x * safeBoxRatio) + 'px'
                        });
                    }

                    design_scene.unbind().bind('resize', function () {
                        var mockup_ratio = design_scene[0].getBoundingClientRect().width / design_config.builder_config.dimension.width;

                        scene.parent().css({
                            width: (design_config.builder_config.segment_place_config.dimension.width * mockup_ratio) + 'px',
                            height: (design_config.builder_config.segment_place_config.dimension.height * mockup_ratio) + 'px',
                            top: (design_config.builder_config.segment_place_config.position.y * mockup_ratio) + 'px',
                            left: (design_config.builder_config.segment_place_config.position.x * mockup_ratio) + 'px'
                        });

                        _RATIO = scene[0].getBoundingClientRect().width / design_config.dimension.width;

                        design_scene.trigger('image_updated');
                    }).bind('image_updated', function (e, data) {
                        scene.html('');
                        design_scene.find('.helper').remove();

                        design_panel.find('[data-action="personalized-opt-selector"]').hide();
                        if (typeof data !== 'undefined') {
                            let new_dim = $.calculateNewDim(data.width, data.height, design_config.dimension.width, design_config.dimension.height, true);

                            data.source.dimension = {width: new_dim.w, height: new_dim.h};
                            data.source.orig_size = {width: data.width, height: data.height};
                            data.source.position = {x: 0, y: 0};
                            data.source.rotation = 0;

                            data.source.timestamp = (new Date()).getTime();

                            delete data.width;
                            delete data.height;

                            var _data = _fetchCurrentDesign(true);

                            $.each(_data, function (k) {
                                delete _data[k];
                            });

                            $.extend(_data, data);
                        } else {
                            data = _fetchCurrentDesign();

                            if (!data) {
                                return;
                            }
                        }

                        if (typeof data.source.svg_content !== 'undefined') {
                            var img = $('<div />').html((typeof data.source.option_default_values === 'undefined') ? data.source.svg_content : data.source.option_default_values.svg_content).appendTo(scene);
                            img.find('svg')[0].setAttribute('preserveAspectRatio', 'none');
                            design_panel.find('[data-action="personalized-opt-selector"]').show();
                        } else {
                            var img = $('<img />').attr('src', data.source.url + '?t=' + (new Date()).getTime()).appendTo(scene);
                        }

                        _changePrintTemplateStatus(current_print_template);

                        img.addClass('image');

                        var __pointGetDistance = function (p1, p2) {
                            return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
                        };

                        var __getPointExceptRotation = function (point, bounding_rect, rotation) {
                            if (typeof rotation === 'undefined') {
                                rotation = 0;
                            } else {
                                rotation = parseFloat(rotation);

                                if (isNaN(rotation)) {
                                    rotation = 0;
                                }
                            }

                            if (rotation !== 0) {
                                var center_pt = {
                                    x: bounding_rect.x + bounding_rect.width / 2,
                                    y: bounding_rect.y + bounding_rect.height / 2
                                };

                                var degress = Math.atan2(point.y - center_pt.y, point.x - center_pt.x) * 180 / Math.PI;

                                degress -= rotation;

                                var distance = __pointGetDistance(center_pt, point);

                                var radian = degress * Math.PI / 180;

                                point.x = center_pt.x + distance * Math.cos(radian);
                                point.y = center_pt.y + distance * Math.sin(radian);
                            }

                            return point;
                        };

                        var __getPointApplyRotation = function (point, bounding_rect, rotation) {
                            if (typeof rotation === 'undefined') {
                                rotation = 0;
                            } else {
                                rotation = parseFloat(rotation);

                                if (isNaN(rotation)) {
                                    rotation = 0;
                                }
                            }

                            if (rotation !== 0) {
                                var center_pt = {
                                    x: bounding_rect.x + bounding_rect.width / 2,
                                    y: bounding_rect.y + bounding_rect.height / 2
                                };

                                var degress = Math.atan2(point.y - center_pt.y, point.x - center_pt.x) * 180 / Math.PI;

                                degress += rotation;

                                var distance = __pointGetDistance(center_pt, point);

                                var radian = degress * Math.PI / 180;

                                point.x = center_pt.x + distance * Math.cos(radian);
                                point.y = center_pt.y + distance * Math.sin(radian);
                            }

                            return point;
                        };

                        var __getVectorIntersectionPoint = function (vector1, vector2) {
                            var denominator, a, b, numerator1, numerator2, result = {
                                x: null,
                                y: null,
                                onLine1: false,
                                onLine2: false
                            };

                            denominator = ((vector2.point2.y - vector2.point1.y) * (vector1.point2.x - vector1.point1.x)) - ((vector2.point2.x - vector2.point1.x) * (vector1.point2.y - vector1.point1.y));

                            if (denominator === 0) {
                                return result;
                            }

                            a = vector1.point1.y - vector2.point1.y;
                            b = vector1.point1.x - vector2.point1.x;

                            numerator1 = ((vector2.point2.x - vector2.point1.x) * a) - ((vector2.point2.y - vector2.point1.y) * b);
                            numerator2 = ((vector1.point2.x - vector1.point1.x) * a) - ((vector1.point2.y - vector1.point1.y) * b);

                            a = numerator1 / denominator;
                            b = numerator2 / denominator;

                            result.x = vector1.point1.x + (a * (vector1.point2.x - vector1.point1.x));
                            result.y = vector1.point1.y + (a * (vector1.point2.y - vector1.point1.y));

                            if (a > 0 && a < 1) {
                                result.onLine1 = true;
                            }

                            if (b > 0 && b < 1) {
                                result.onLine2 = true;
                            }

                            return result;
                        };

                        const __triggerImgUpdate = () => {
                            data.source.timestamp = (new Date()).getTime();
                            img.trigger('updated');
                        }

                        var helper = $('<div />').addClass('helper')
                            .appendTo(design_scene.find('.frame'))
                            .bind('mousedown', function (e) {
                                var bounding_rect = __helperGetBoundingClientRectExceptRotation();

                                var scroll_top = $(window).scrollTop();
                                var scroll_left = $(window).scrollLeft();

                                var anchor = {
                                    x: (e.pageX - scroll_left) - bounding_rect.x,
                                    y: (e.pageY - scroll_top) - bounding_rect.y
                                };

                                $(document).unbind('.campaign-helper').bind('mousemove.campaign-helper', function (e) {
                                    var bounding_rect = helper.parent()[0].getBoundingClientRect();

                                    var scroll_top = $(window).scrollTop();
                                    var scroll_left = $(window).scrollLeft();

                                    data.source.position.x = (e.pageX - scroll_left - bounding_rect.x - anchor.x) / _RATIO;
                                    data.source.position.y = (e.pageY - scroll_top - bounding_rect.y - anchor.y) / _RATIO;

                                    __triggerImgUpdate()
                                });

                                $(document).bind('mouseup.campaign-helper', function () {
                                    $(document).unbind('.campaign-helper');
                                });
                            })

                        const keysdown = {};

                        //Handle Key down for move desgin
                        $(document).unbind("keydown.campaign-helper-keyboard")
                            .bind("keydown.campaign-helper-keyboard", function (e) {
                                if (e.target && ['input', 'select', 'textarea'].indexOf(e.target.nodeName.toLowerCase()) >= 0) {
                                    return;
                                }
                                const isHold = !!keysdown[e.keyCode];
                                const value = (e.shiftKey ? 10 : (isHold ? 2 : 1)) / _RATIO;
                                const __move = (moveX, moveY) => {
                                    data.source.position.x += moveX
                                    data.source.position.y += moveY
                                    __triggerImgUpdate()
                                    e.preventDefault()
                                }
                                let notControlKey = false;
                                switch (e.key) {
                                    case "ArrowLeft":
                                        __move(-value, 0)
                                        break;
                                    case "ArrowRight":
                                        __move(value, 0)
                                        break;
                                    case "ArrowUp":
                                        __move(0, -value)
                                        break;
                                    case "ArrowDown":
                                        __move(0, value)
                                        break;
                                    default:
                                        notControlKey = true;
                                        break;
                                }
                                if (!notControlKey) {
                                    keysdown[e.keyCode] = true;
                                }
                            })
                        $(document).keyup(function (e) {
                            // Remove this key from the map
                            delete keysdown[e.keyCode];
                        });


                        var __helperGetBoundingClientRectExceptRotation = function () {
                            var _helper = helper.clone(true).html('').css({
                                transform: 'none',
                                opacity: 0
                            }).appendTo(helper.parent());

                            var bounding_rect = _helper[0].getBoundingClientRect();

                            _helper.remove();

                            return bounding_rect;
                        };

                        ['NW', 'N', 'NE', 'E', 'SE', 'S', 'SW', 'W'].forEach(function (handler_name) {
                            var handler = $('<div />').addClass('resize-handler').attr('data-handler', handler_name).appendTo(helper);

                            handler.mousedown(function (e) {
                                e.stopImmediatePropagation();

                                var bounding_rect = __helperGetBoundingClientRectExceptRotation();

                                var scroll_top = $(window).scrollTop();
                                var scroll_left = $(window).scrollLeft();

                                var anchor = __getPointExceptRotation({
                                    x: e.pageX - scroll_left,
                                    y: e.pageY - scroll_top
                                }, bounding_rect, data.source.rotation);

                                $(document).bind('mousemove.campaign-helper', function (e) {
                                    var scroll_top = $(window).scrollTop();
                                    var scroll_left = $(window).scrollLeft();

                                    var cursor_point = __getPointExceptRotation({
                                        x: e.pageX - scroll_left,
                                        y: e.pageY - scroll_top
                                    }, bounding_rect, data.source.rotation);

                                    var new_width = 0;
                                    var new_height = 0;

                                    if (handler_name.match(/W/)) {
                                        new_width = bounding_rect.width + (anchor.x - cursor_point.x);
                                    } else if (handler_name.match(/E/)) {
                                        new_width = bounding_rect.width + (cursor_point.x - anchor.x);
                                    } else {
                                        new_width = data.source.dimension.width * _RATIO;
                                    }

                                    if ((e.shiftKey || 1 === 1) && handler_name.length === 2) {
                                        new_height = bounding_rect.height * (new_width / bounding_rect.width);
                                    } else if (handler_name.match(/N/)) {
                                        new_height = bounding_rect.height + (anchor.y - cursor_point.y);
                                    } else if (handler_name.match(/S/)) {
                                        new_height = bounding_rect.height + (cursor_point.y - anchor.y);
                                    } else {
                                        new_height = data.source.dimension.height * _RATIO;
                                    }

                                    if (new_width <= 0) {
                                        new_width = 1;
                                    }

                                    if (new_height <= 0) {
                                        new_height = 1;
                                    }

                                    if ((e.shiftKey || 1 === 1) && handler_name.length > 1) {
                                        var _ratio = bounding_rect.width / bounding_rect.height;

                                        if (new_width / new_height > _ratio) {
                                            new_width = new_height * _ratio;
                                        } else {
                                            new_height = new_width / _ratio;
                                        }
                                    }

                                    var bounding_pt = {x: bounding_rect.x, y: bounding_rect.y};

                                    bounding_pt = __getPointApplyRotation(bounding_pt, bounding_rect, data.source.rotation);

                                    var radian = data.source.rotation * Math.PI / 180;

                                    if (handler_name.match(/W/)) {
                                        bounding_pt.x = bounding_pt.x + (bounding_rect.width - new_width) * Math.cos(radian);
                                        bounding_pt.y = bounding_pt.y + (bounding_rect.width - new_width) * Math.sin(radian);
                                    }

                                    if (handler_name.match(/N/)) {
                                        bounding_pt.x = bounding_pt.x + (bounding_rect.height - new_height) * Math.cos((data.source.rotation + 90) * Math.PI / 180);
                                        bounding_pt.y = bounding_pt.y + (bounding_rect.height - new_height) * Math.sin((data.source.rotation + 90) * Math.PI / 180);
                                    }

                                    var vector1 = {
                                        point1: bounding_pt,
                                        point2: {}
                                    };

                                    var vector2 = {
                                        point1: {},
                                        point2: {}
                                    };

                                    vector2.point1.x = vector1.point1.x + new_height * Math.cos((data.source.rotation + 90) * Math.PI / 180);
                                    vector2.point1.y = vector1.point1.y + new_height * Math.sin((data.source.rotation + 90) * Math.PI / 180);

                                    vector1.point2.x = vector2.point1.x + new_width * Math.cos(radian);
                                    vector1.point2.y = vector2.point1.y + new_width * Math.sin(radian);

                                    vector2.point2.x = vector1.point1.x + new_width * Math.cos(radian);
                                    vector2.point2.y = vector1.point1.y + new_width * Math.sin(radian);

                                    var intersect = __getVectorIntersectionPoint(vector1, vector2);

                                    var degress = (Math.atan2(bounding_pt.y - intersect.y, bounding_pt.x - intersect.x) * 180 / Math.PI) - data.source.rotation;

                                    var distance = __pointGetDistance(intersect, bounding_pt);

                                    var radian = degress * Math.PI / 180;

                                    bounding_pt.x = intersect.x + distance * Math.cos(radian);
                                    bounding_pt.y = intersect.y + distance * Math.sin(radian);

                                    var _bounding_rect = helper.parent()[0].getBoundingClientRect();

                                    data.source.position.x = (bounding_pt.x - _bounding_rect.x) / _RATIO;
                                    data.source.position.y = (bounding_pt.y - _bounding_rect.y) / _RATIO;
                                    data.source.dimension.width = new_width / _RATIO;
                                    data.source.dimension.height = new_height / _RATIO;

                                    __triggerImgUpdate()
                                }).bind('mouseup.campaign-helper', function (e) {
                                    $(document).unbind('.campaign-helper');
                                });
                            });
                        });

                        img.bind('updated', function (e, new_data) {
                            if (new_data) {
                                data = new_data;

                                if (typeof data.source.svg_content !== 'undefined') {
                                    img.html(typeof data.source.option_default_values === 'undefined' ? data.source.svg_content : data.source.option_default_values.svg_content);
                                    img.find('svg')[0].setAttribute('preserveAspectRatio', 'none');
                                } else {
                                    img.attr('src', data.source.url + '?t=' + (new Date()));
                                }
                            }
                            $(this).css({
                                width: (data.source.dimension.width * _RATIO) + 'px',
                                height: (data.source.dimension.height * _RATIO) + 'px',
                                top: (data.source.position.y * _RATIO) + 'px',
                                left: (data.source.position.x * _RATIO) + 'px',
                                transform: 'rotate(' + data.source.rotation + 'deg)'
                            });

                            helper.css({
                                width: (data.source.dimension.width * _RATIO) + 'px',
                                height: (data.source.dimension.height * _RATIO) + 'px',
                                top: (data.source.position.y * _RATIO) + 'px',
                                left: (data.source.position.x * _RATIO) + 'px',
                                transform: 'rotate(' + data.source.rotation + 'deg)'
                            });
                        }).trigger('updated');
                    }).bind('updatePreview', function (e, updateData) {
                        if (typeof updateData.replaceKey !== 'undefined' && typeof updateData.replaceValue !== 'undefined') {
                            let _replaceConfig = {};
                            let _hasPreviewFrame = false;

                            if (printTemplateArgs[current_print_template].config.hasOwnProperty('campaign_builder') && Object.keys(printTemplateArgs[current_print_template].config.campaign_builder).length > 0) {
                                $.each(printTemplateArgs[current_print_template].config.campaign_builder, function (key, data) {
                                    if (data.layer[0] !== 'main') {
                                        let url = printTemplateArgs[current_print_template].config.campaign_builder[key].layer[0].replace(updateData.replaceKey, updateData.replaceValue);

                                        printTemplateData.config.segments[key].builder_config.layers[0] = url;

                                        _replaceConfig[key] = {
                                            'background': $.base_url + '/resource/template/core/image/' + url
                                        };

                                        if (typeof printTemplateArgs[current_print_template].config.campaign_builder[key].layer[2] !== "undefined") {
                                            _hasPreviewFrame = true;
                                            _replaceConfig[key].frame = $.base_url + '/resource/template/core/image/' + printTemplateArgs[current_print_template].config.campaign_builder[key].layer[2].replace(updateData.replaceKey, updateData.replaceValue);
                                        }
                                    }
                                });
                            } else {
                                $.each(printTemplateArgs[current_print_template].config.preview_config, function (key, data) {
                                    if (data.layer[0] !== 'main') {
                                        $.each(data.config, function (designKey) {
                                            console.log(designKey, 'designKey');
                                            let url = printTemplateArgs[current_print_template].config.preview_config[key].layer[0].replace(updateData.replaceKey, updateData.replaceValue);

                                            let layer_url = url.replace($.base_url + '/resource/template/core/image/', '').trim();

                                            printTemplateData.config.segments[designKey].builder_config.layers[0] = layer_url;

                                            _replaceConfig[designKey] = {
                                                'background': url
                                            };

                                            if (typeof printTemplateArgs[current_print_template].config.preview_config[key].layer[2] !== "undefined") {
                                                _hasPreviewFrame = true;
                                                _replaceConfig[designKey].frame = printTemplateArgs[current_print_template].config.preview_config[key].layer[2].replace(updateData.replaceKey, updateData.replaceValue);
                                            }
                                        });
                                    }

                                });
                            }

                            let _mockupUrl = _replaceConfig[products.campaign_config.print_template_config[current_print_template].selected_design]['background'];

                            if (_hasPreviewFrame) {
                                design_scene.find('.preview-frame').remove();
                                $('<img />').addClass('preview-frame').attr('url', _replaceConfig[products.campaign_config.print_template_config[current_print_template].selected_design]['frame']).appendTo(design_scene);
                            }

                            design_scene.css('background-image', 'url("' + _mockupUrl + '")');
                        }
                    }).bind('reset', function(){
                        $(this).removeClass('overflow');
                        $(this).find('.scene').removeClass('no-blend');
                        $(this).find('.safe-box').show();
                        $(this).find('.preview-frame').removeClass('active');

                        design_panel.find('[data-action="helper-toggler"]').removeClass('activated');
                        design_panel.find('[data-action="opacity-toggler"]').removeClass('activated');
                        design_panel.addClass('show-helper');
                    });
                    design_scene.trigger('reset');
                    design_scene.trigger('resize');
                });
            });

            if (design_tabs[0].childNodes.length < 2) {
                design_tabs.hide();
            } else {
                design_tabs.show();
            }

            design_tabs.find('> [data-design-key="' + products.campaign_config.print_template_config[current_print_template].selected_design + '"]').trigger('click');

            if (Object.keys(printTemplateData).length > 1) {
                if (products.campaign_config.print_template_config[current_print_template].apply_other_face === null) products.campaign_config.print_template_config[current_print_template].apply_other_face = 0;
                $("<input />").attr({
                    type: 'checkbox',
                    'data-type': current_product_type,
                    name: 'apply_other_face',
                    value: 1,
                    id: 'apply_other_face_' + current_print_template
                }).prop('checked', (products.campaign_config.print_template_config[current_print_template].apply_other_face) ? true : false).appendTo(sameDesignApply).change(function () {
                    if ($(this).is(':checked')) {
                        products.campaign_config.print_template_config[current_print_template].apply_other_face = 1;
                    } else {
                        products.campaign_config.print_template_config[current_print_template].apply_other_face = 0;
                    }
                    _applySameDesign();
                });
                $('<label/ >').attr('for', 'apply_other_face_' + current_print_template).text('Apply design for other face').appendTo(sameDesignApply);

                let product_type_disable_same_design_apply = ['gildan_g500_classic_tee', 'next_level_nl3600_premium_short_sleeve', 'bella_canvas_3001c_unisex_jersey_short_sleeve', 'youth_t_shirt', 'hoodie'];

                if (Object.keys(printTemplateData.config.segments).length < 2 || product_type_disable_same_design_apply.includes(current_product_type) === true) {
                    sameDesignApply.hide();
                } else {
                    sameDesignApply.show();
                }
            }
            _applySameDesign();
        }

        function _postFrmMockupPanelRender() {
            dataCustomMockups = (products.custom_mockup)?_getMockupsData(products.custom_mockup):{};

            actionGroup.find('.js-next').unbind().on('click', function () {
                if (selectedMockup.length === 0) {
                    alert("Please select one or more mockup to continue!");
                    return;
                }
                let hasVariant = false;
                $.each(products.product_types, (productTypeKey, product) => {
                    if (typeof product?.product_variant !== "undefined") {
                        if (product.product_variant.length > 0) {
                            hasVariant = true;
                            return;
                        }
                    }
                });
                if (!hasVariant) {
                    alert("This campaign don't have any variant!");
                    return;
                }

                const mockups = selectedMockup.map(id => dataCustomMockups[id]).filter(data => !!data);
                _renderMappingMockupVariants(products.custom_mockup, mockups, __onSaveVariantsMockup);
            });

            actionGroup.find('.js-cancel').unbind().on('click', function () {
                selectedMockup = [];
                mockupPanel.find('.mockup-item').each(function () {
                    $(this).removeClass('selected').find('.checked').remove();
                });
                mockupPanel.trigger('selectedMockupAction');
            });

            mockupPanel.bind('selectedMockupAction', function () {
                actionGroup.hide();
                if (selectedMockup.length > 0) {
                    guide.hide();
                    actionGroup.show();
                } else {
                    guide.show();
                }
            }).bind('selectedMockup', function() {
                $(this).find('.mockup-item').each(function () {
                    let item = $(this),
                        id = item.attr('id'),
                        counter = selectedMockup.indexOf(id) + 1;

                    item.removeClass('selected').find('.checked').remove();
                    mockupPanel.find('.manage-action-group').html();

                    if (counter > 0) {
                        item.addClass('selected').append($('<span />').addClass('checked'));
                    }
                });
            });

            const __onRemoveMockup = (mockup) => {
                const mockupId = (mockup.fileId || mockup.id);
                $.each(products.custom_mockup, (key, product) => {
                    $.each(product.variants, (variantId, variant) => {
                        variant.images = variant.images?.filter(image => {
                            return mockup.fileId ? mockup.fileId !== image.fileId : mockup.id !== image.id;
                        })
                    });
                    /**
                     * Remove mockup from selected all for this product type
                     */
                    if (product.selected_all.length > 0) {
                        product.selected_all = product.selected_all.filter(item => {
                            return item !== mockupId;
                        });
                    }
                });
                _postFrmProductPanelRender();
                mockupPanel.trigger('selectedMockup');
            }

            const __onSaveVariantsMockup = (mockups) => {
                $.each(mockups,(key, mockup) => {
                    const id = mockup.fileId || mockup.id;
                    if (!dataCustomMockups[id]) return;
                    dataCustomMockups[id] = mockup;
                    const variantCount = _calcVariantsOfMockup(mockup);
                    if (variantCount > 0) {
                        mockup.hasAutoOption = true;
                    } else {
                        mockup.hasAutoOption = false;
                    }
                    mockupsWrapper.find(`#${id}`).trigger('update', [mockup]);
                    const variantIds = mockup.variantIds;
                    $.each(products.custom_mockup, (productTypeKey, product) => {
                        $.each(product.variants, (variantId, variant) => {
                            if (!variantIds[variantId]) {
                                variant.images = variant.images?.filter(image => {
                                    const imageId = image.fileId || image.id;
                                    return imageId != id;
                                });
                                return;
                            }
                            ;
                            const found = variant.images.find(image => {
                                const imageId = image.fileId || image.id;
                                return imageId === id;
                            })
                            if (!found) {
                                const image = {url: mockup.url};
                                if (mockup.fileId) image.fileId = mockup.fileId;
                                else image.id = mockup.id;
                                variant.images.push(image)
                            }
                        })
                    });
                });
                selectedMockup = [];
                //Re-Render variants list
                _postFrmProductPanelRender();
                mockupPanel.trigger('selectedMockup');
                mockupPanel.trigger('selectedMockupAction');
            }

            const __renderMockupItem = (image) => {
                const itemElm = $('<div />').attr('id', image.fileId || image.id);
                const _render = (image) => {
                    itemElm.empty();
                    itemElm.removeClass();
                    itemElm.addClass("mockup-item");

                    if (image.uploading) {
                        itemElm.addClass("uploading");
                        $('<div />').addClass('uploader-progress-bar').appendTo(itemElm).append($('<div />'));
                    } else if (image.error) {
                        itemElm.addClass("error");
                        $('<div />').addClass('uploader-error').appendTo(itemElm);
                    } else {
                        itemElm.find('.uploader-progress-bar').remove();
                    }

                    itemElm.css('background-image', `url(${image.url})`).unbind('click').click(() => {

                        let id = itemElm.attr('id'),
                            idx = selectedMockup.indexOf(id);

                        itemElm.removeClass('selected').find('.checked').remove();
                        if (idx < 0) {
                            selectedMockup.push(id);
                            itemElm.addClass('selected').append($('<span />').addClass('checked'));
                        } else {
                            selectedMockup.splice(idx, 1);
                        }

                        mockupPanel.trigger('selectedMockupAction');
                    });

                    const removeBtn = $('<span />').addClass("remove-item").appendTo(itemElm);
                    removeBtn.click((e) => {
                        e.stopPropagation();
                        itemElm.remove();
                        __onRemoveMockup(image);
                    });
                    if (!image.uploading && !image.error) {
                        let variantCount = Object.keys(image.variantIds).length;
                        if (image.hasAutoOption) {
                            variantCount = _calcVariantsOfMockup(image);
                        }
                        $('<tag />').text(variantCount).addClass("tag").appendTo(itemElm);
                    }
                }
                _render(image)
                itemElm.bind("update", function (e, image) {
                    _render(image)
                })
                return itemElm;
            }

            const __renderMockupUploader = () => {
                const uploader = $('<div />').addClass('file-uploader').osc_uploader({
                    max_files: -1,
                    max_connections: 5,
                    process_url: $.base_url + '/catalog/backend_campaign/uploadMockupCustomer/hash/' + OSC_HASH,
                    btn_content: $('<div />').addClass('mockup-uploader').html('<div class="icon-plus"></div><span>Add new mockup</span>'),
                    dragdrop_content: 'Drop here to upload',
                    image_mode: true,
                    xhrFields: {withCredentials: true},
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-OSC-Cross-Request': 'OK'
                    }
                })
                return uploader;
            }

            $.each(dataCustomMockups, (imageId, image) => {
                const item = __renderMockupItem(image)
                mockupsWrapper.append(item)
            });

            const uploader = __renderMockupUploader();
            mockupsWrapper.append(uploader);
            const tempUploads = {};

            uploader.bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.attr('data-upload-id', file_id);

                const reader = new FileReader();
                reader.onload = () => {
                    const image = {
                        fileId: file_id,
                        url: reader.result,
                        variantIds: {},
                        uploading: true
                    }
                    const item = __renderMockupItem(image);
                    item.insertBefore(uploader);
                    //mockupsWrapper.prepend(item);
                    image.item = item;
                    tempUploads[file_id] = image;
                }
                reader.readAsDataURL(file);
            })
            .bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
                if (uploader.attr('data-upload-id') !== file_id) {
                    return;
                }
            })
            .bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                uploader.removeAttr('data-upload-id');

                let res;
                try {
                    res = JSON.parse(response)
                } catch (error) {
                    console.log(response);
                    return;
                }
                const uploadImage = tempUploads[file_id];
                if (!uploadImage) return;

                delete tempUploads[file_id];

                uploadImage.url = res.data;
                uploadImage.uploading = false;
                const {item, ...image} = uploadImage;
                item.trigger("update", [image])
                dataCustomMockups[file_id] = image;
            })
            .bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                const uploadImage = tempUploads[file_id];
                if (!uploadImage) return;
                alert("Upload image failed! Error: ", error_message);
                delete tempUploads[file_id];
                uploadImage.error = true;

                const {item, ...image} = uploadImage;
                item.trigger("update", [image])
            });
        }

        function _postFrmVideoPanelRender() {
            const $videoPanel = form.find('.video-panel');
            const $videoUploader = form.find('.video-uploader');
            const $actionGroup = $videoPanel.find('.actions');
            const $guide = $videoPanel.find('.guide');
            let mockupVideos = _getMockupVideos(products.custom_mockup);

            if (
                typeof window.__videoUploader__getSelectedItems !== 'function' ||
                typeof window.__videoUploader__setData !== 'function'
            ) {
                console.log('Video uploader not found!');
                return false;
            }

            window.__videoUploader__setData(mockupVideos);

            $videoUploader
                .on('uploader-selected-change', function(e, selectedVideo) {
                    if (selectedVideo) {
                        $guide.hide();
                        $actionGroup.show();
                    } else {
                        $guide.show();
                        $actionGroup.hide();
                    }
                })
                .on('uploader-update', function(e, data) {
                    const mockupVideos = _getMockupVideos(products.custom_mockup);

                    Object.keys(mockupVideos).forEach(videoId => {
                        if (!data[videoId]) {
                            __onRemoveVideo(mockupVideos[videoId]);
                        }

                        if ( JSON.stringify(mockupVideos[videoId]) !== JSON.stringify(data[videoId]) ) {
                            __onSaveVariantsVideo(data[videoId], false);
                        }
                    });
                });

            $actionGroup.find('.js-next').unbind().on('click', function () {
                const selectedVideo = window.__videoUploader__getSelectedItems();

                if (!selectedVideo) {
                    alert("Please select one video to continue!");
                    return;
                }

                let hasVariant = false;

                for (const [, product] of Object.entries(products.product_types)) {
                    if (product?.product_variant?.length) {
                        hasVariant = true;
                        break;
                    }
                }

                if (!hasVariant) {
                    alert("This campaign don't have any variant!");
                    return;
                }

                _renderMappingVideoVariants(products.custom_mockup, selectedVideo, __onSaveVariantsVideo);
            });

            $actionGroup.find('.js-cancel').unbind().on('click', function () {
                $videoUploader.trigger('unselect-all-items');
            });

            function __onRemoveVideo(removedVideo) {
                const removedVideoId = removedVideo.fileId || removedVideo.id;

                for (const [, product] of Object.entries(products.custom_mockup)) {
                    for (const [, variant] of Object.entries(product.variants)) {
                        if (variant.videos) {
                            variant.videos = variant.videos.filter(variantVideo => removedVideoId !== (variantVideo?.fileId || variantVideo?.id));
                        }
                    }
                }

                _postFrmProductPanelRender();
            }

            function __onSaveVariantsVideo(video, shouldUpdateElm = true) {
                if (!video) return false;
                const videoId = video.fileId || video.id;

                for (const [, product] of Object.entries(products.custom_mockup)) {
                    for (const [variantId, variant] of Object.entries(product.variants)) {
                        if (video.variantIds[variantId]) {
                            let addVideoFlag = true;

                            // check update or add
                            variant.videos = (variant.videos || []).map(variantVideo => {
                                if (videoId == (variantVideo.fileId || variantVideo.id)) {
                                    addVideoFlag = false;
                                    return video;
                                }
                                return variantVideo;
                            });

                            if (addVideoFlag) {
                                variant.videos.push(video);
                            }
                        } else {
                            // remove video from variant
                            variant.videos = variant.videos?.filter(variantVideo => videoId != (variantVideo.fileId || variantVideo.id));
                        }
                    }
                }

                if (shouldUpdateElm) {
                    $videoUploader.trigger('uploader-update-items', [{
                        [videoId]: video,
                    }]);
                }

                //Re-Render variants list
                _postFrmProductPanelRender();
            }
        }

        $(document).ready(function () {
            products = fetchJSONTag(form, 'campaign-config');

            if (typeof products.campaign_config?.apply_reorder === "undefined") {
                products.campaign_config.apply_reorder = [];
            }

            _postFrmProductPanelRender();
            _postFrmMockupPanelRender();
            _postFrmVideoPanelRender();

            design_panel.find('[data-action="horizontal-center"]').click(function () {
                let data = _fetchCurrentDesign();

                if (data === null) {
                    return;
                }

                data.source.position.x = (printTemplateArgs[current_print_template].config.segments[products.campaign_config.print_template_config[current_print_template].selected_design].dimension.width - data.source.dimension.width) / 2;

                _modifiedTimestamp(data);

                design_scene.find('.frame .scene .image').trigger('updated', [data]);
            });

            design_panel.find('[data-action="vertical-center"]').click(function () {
                let data = _fetchCurrentDesign();

                if (data === null) {
                    return;
                }
                data.source.position.y = (printTemplateArgs[current_print_template].config.segments[products.campaign_config.print_template_config[current_print_template].selected_design].dimension.height - data.source.dimension.height) / 2;

                _modifiedTimestamp(data);

                design_scene.find('.frame .scene .image').trigger('updated', [data]);
            });

            design_panel.find('[data-action="delete"]').click(function () {
                if (typeof printTemplateArgs[current_print_template] === 'undefined' || typeof products.campaign_config.print_template_config[current_print_template].selected_design === 'undefined') {
                    return null;
                }

                var product = products.campaign_config.print_template_config[current_print_template];

                if (typeof product.segments !== 'undefined' && typeof product.selected_design !== 'undefined') {
                    delete product.segments[product.selected_design];
                }
                _changePrintTemplateStatus(current_print_template);
                design_scene.trigger('image_updated');
            });

            design_panel.find('[data-action="personalized-opt-selector"]').click(function () {
                var data = _fetchCurrentDesign();
                sessionStorage.setItem('_source', JSON.stringify(data.source));
                if (data === null || data.source.type !== 'personalizedDesign') {
                    return;
                }

                $.unwrapContent('personalizeBuilder_personalizePanel');
                var modal = $('<div />').addClass('osc-modal').width(800).height(656);

                var header = $('<header />').appendTo(modal);
                $('<div />').addClass('title').html('Select options for mockup').appendTo($('<div />').addClass('main-group').appendTo(header));

                $('<div />').addClass('close-btn').click(function () {
                    $.unwrapContent('personalizeBuilder_personalizePanel');
                }).appendTo(header);

                $('<iframe/>')
                    .css({'width': '100%', height: '600px', border: 'none'})
                    .attr('src', $.frontend_base_url + '/catalog/frontend/iframePersonalizedForm?id=' + data.source.design_id + '&storage_base_url=' + $.storage_base_url)
                    .attr('id', 'iframePersonalizedForm')
                    .appendTo(modal);

                $.wrapContent(modal, {key: 'personalizeBuilder_personalizePanel'});
                modal.moveToCenter().css('top', '100px');
            });

            design_panel.find('[data-action="bulk-apply"]').click(function () {
                if (typeof printTemplateArgs[current_print_template] === 'undefined') {
                    return null;
                }

                let product = products.campaign_config.print_template_config[current_print_template],
                    _apply_other_face = product.apply_other_face;

                if (typeof product.segments === "undefined" || typeof product.segments[product.selected_design].source === "undefined") {
                    alert('Please add design before copy to another product!');
                    return null;
                }

                if (Object.keys(product).length < 1) {
                    alert("Design source has empty!");
                    return false;
                }

                let _printTemplatesToApply = JSON.parse(JSON.stringify(printTemplateArgs));
                delete _printTemplatesToApply[current_print_template];

                $.each(_printTemplatesToApply, function (key, data) {
                    if (typeof data.config.segments[product.selected_design] === "undefined" || Object.keys(printTemplateArgs[current_print_template].config.segments).length !== Object.keys(data.config.segments).length) {
                        delete _printTemplatesToApply[key];
                    }
                });


                if (Object.keys(_printTemplatesToApply).length < 1) {
                    alert('No print template to copy design!');
                    return;
                }

                $.each(_printTemplatesToApply, function (key, config) {
                    let _newDesignData = _convertSegment(product, printTemplateArgs[current_print_template], config);

                    if (typeof products.campaign_config.print_template_config[key] === 'undefined') {
                        products.campaign_config.print_template_config[key] = {
                            print_template_id: config.print_template_id,
                            segments: {},
                            selected_design: product.selected_design,
                            title: config.title
                        }
                        products.campaign_config.print_template_config[key].segments[product.selected_design] = _newDesignData[product.selected_design];
                    } else {
                        if (typeof products.campaign_config.print_template_config[key].segments === "undefined") {
                            products.campaign_config.print_template_config[key].segments = {};
                            products.campaign_config.print_template_config[key].segments[product.selected_design] = {};
                        }
                        products.campaign_config.print_template_config[key].segments[product.selected_design] = _newDesignData[product.selected_design];
                        products.campaign_config.print_template_config[key].selected_design = product.selected_design;
                    }

                    products.campaign_config.print_template_config[key].apply_other_face = _apply_other_face;
                    _changePrintTemplateStatus(key);
                });
            });

            design_panel.find('[data-action="helper-toggler"]').click(function () {
                design_panel.toggleClass('show-helper');
                $(this)[design_panel.hasClass('show-helper') ? 'removeClass' : 'addClass']('activated');

                design_scene.toggleClass('overflow');
                design_scene.find('.scene')[design_panel.hasClass('show-helper') ? 'removeClass' : 'addClass']('no-blend');
                design_scene.find('.safe-box')[design_panel.hasClass('show-helper') ? 'show' : 'hide']();
                design_scene.find('.preview-frame')[design_panel.hasClass('show-helper') ? 'removeClass' : 'addClass']('active');
            }).trigger('click');

            design_panel.find('[data-action="opacity-toggler"]').click(function () {
                design_panel.toggleClass('scene-opacity');
                $(this)[design_panel.hasClass('scene-opacity') ? 'removeClass' : 'addClass']('activated');
                design_scene.find('.scene')[design_panel.hasClass('scene-opacity') ? 'removeClass' : 'addClass']('opacity');
            }).trigger('click');

            $(document).keydown(function (e) {
                if (e.target && ['input', 'select', 'textarea'].indexOf(e.target.nodeName.toLowerCase()) >= 0) {
                    return;
                }

                if (typeof printTemplateArgs[current_print_template] === 'undefined' || typeof products.campaign_config.print_template_config[current_print_template].selected_design === 'undefined') {
                    return null;
                }

                if ((e.key === 'c' || e.keyCode === 67) && e.ctrlKey) {
                    var data = null;
                    var product = products.campaign_config.print_template_config[current_print_template];

                    if (typeof product.segments !== 'undefined' && typeof product.segments[product.selected_design] !== 'undefined') {
                        data = product.segments[product.selected_design];
                    } else {
                        return;
                    }

                    data = JSON.stringify(data);

                    try {
                        localStorage.setItem('campaign_design_clipboard', data);
                        alert('The design "' + printTemplateArgs[current_print_template].segments[product.selected_design].source.design_id + '" of product "' + printTemplateArgs[current_print_template].title + '" is now on the clipboard');
                    } catch (e) {
                        alert('Unable to copy: ' + e);
                    }
                } else if ((e.key === 'v' || e.keyCode === 86) && e.ctrlKey) {
                    try {
                        var data = localStorage.getItem('campaign_design_clipboard');

                        if (!data) {
                            alert('Please copy a design before paste');
                            return;
                        }
                    } catch (e) {
                        alert('Unable to get copied data: ' + e);
                        return;
                    }

                    try {
                        data = JSON.parse(data);
                    } catch (e) {
                        alert('Unable to parse copied data: ' + e);
                        return;
                    }

                    if (typeof data !== 'object' || data === null) {
                        return;
                    }

                    var _data = _fetchCurrentDesign(true);

                    $.extend(_data, data);

                    design_scene.trigger('image_updated');
                }
            });

            var __image_selector__personalizedDesign = {
                image_list_data: null,

                searchKeyword: function (keywords, scene, campaign_type) {
                    keywords = keywords ? searchCleanKeywords(keywords) : keywords;
                    let keywords_hash = keywords ? $.md5(keywords) : null;
                    if (
                        __image_selector__personalizedDesign.image_list_data &&
                        __image_selector__personalizedDesign.image_list_data.keywords_hash === keywords_hash
                    ) {
                        return;
                    }
                    __image_selector__personalizedDesign.renderImageList(scene, keywords, 1, campaign_type);
                },

                render: function (tab_content_container, campaign_type = null) {
                                    
                    let _container = $('<div />').addClass('input').append($('<ins />'));
                    _container.appendTo($('<div />').addClass('filter-frm').appendTo(tab_content_container));

                    const scene = $('<div />').addClass('image-list-scene').appendTo(tab_content_container);
                    
                    let _input = $('<input />')
                        .attr({ placeholder: 'Enter keywords to search...' })
                        .keyup(function (e) {
                            if (e.keyCode !== 13) {
                                return;
                            }
                            __image_selector__personalizedDesign.searchKeyword(this.value, scene, campaign_type)

                        });
                    _input.appendTo(_container);

                    let _button = $('<div />')
                        .append('<svg style="margin-right: 8px" data-icon="osc-search" viewBox="0 0 15 9" width="15px" data-insert-cb="configOSCIcon"><use xlink:href="#osc-search"></use></svg>')
                        .append('<span>Search</span>')
                        .click(function() {
                            const keywords = _input[0].value ? _input[0].value : null;
                            __image_selector__personalizedDesign.searchKeyword(keywords, scene, campaign_type)
                            } 
                        )
                        .css({'display': 'flex'});
                    _button.appendTo(_container);

                    __image_selector__personalizedDesign.renderImageList(scene, null, 1, campaign_type);
                },

                renderImageList: function (scene, keywords, page, campaign_type = null) {
                    page = parseInt(page);
                    let loading = $('<div style="text-align: center; margin-top: 16px" />').text('Loading ...');
                    scene.html(loading);
                    var keywords_hash = keywords ? $.md5(keywords) : null;

                    if (this.image_list_data === null || Math.max(this.image_list_data.current_page, 1) !== page || this.image_list_data.keywords_hash !== keywords_hash) {
                        if (this.image_list_data === true) {
                            return;
                        }

                        this.image_list_data = true;
                        $.ajax({
                            type: 'post',
                            url: $.base_url + '/personalizedDesign/backend/browse',
                            data: {
                                page: page,
                                page_size: 5,
                                hash: OSC_HASH,
                                keywords: keywords ? keywords : '',
                                _2d: 1,
                                campaign_type: campaign_type
                            },
                            success: function (response) {
                                if (response.result !== 'OK') {
                                    alert(response.message);
                                    return;
                                }

                                __image_selector__personalizedDesign.image_list_data = response.data;
                                __image_selector__personalizedDesign.image_list_data.keywords_hash = keywords_hash;
                                __image_selector__personalizedDesign.renderImageList(scene, keywords, page, campaign_type);
                            }
                        });
                        return;
                    }

                    var image_list = $('<div />').addClass('image-list').appendTo(scene);
                    let count = 0;
                    this.image_list_data.items.forEach(function (item_data) {
                        var thumb = $('<div />').addClass('thumb');
                        $('<div />').addClass('image-item').appendTo(image_list).append(thumb).append($('<div />').addClass('title').attr('title', item_data.title).text(item_data.title)).click(function () {
                            if (item_data.is_uploading_s3) {
                                alert('Is uploading this Design to S3. Can\'t using this Design now.');
                            } else {
                                // call api get svg_content, document
                                $.ajax({
                                    type: 'post',
                                    url: $.base_url + '/personalizedDesign/backend/getSvgContent',
                                    data: {
                                        hash: OSC_HASH,
                                        design_id: item_data.id,
                                    },
                                    success: function (response) {
                                        if (response.result !== 'OK') {
                                            alert(response.message);
                                            return;
                                        }

                                        const _data = response.data;
                                        design_scene.trigger('image_updated', [{
                                            source: {
                                                type: 'personalizedDesign',
                                                design_id: item_data.id,
                                                position: {"x": 0, "y": 0},
                                                dimension: {},
                                                rotation: 0,
                                                timestamp: (new Date()).getTime(),
                                                url: item_data.image_url,
                                                svg_content: _data.svg_content
                                            },
                                            width: _data.document.width,
                                            height: _data.document.height,
                                        }]);
            
                                        $.unwrapContent('campaignImageSelector');
                                    }
                                });
                            }
                        });

                        thumb.css('background-image', 'url(' + item_data.image_url + ')');
                        count++;
                    });

                    var pagination = buildPager(this.image_list_data.current_page, this.image_list_data.total, this.image_list_data.page_size, {
                        section: 4,
                        small: false
                    });

                    if (pagination) {
                        $('<div />').addClass('pagination-bar p10').append(pagination).appendTo(scene);

                        pagination.find('[data-page]:not(.current)').click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            __image_selector__personalizedDesign.renderImageList(scene, keywords, this.getAttribute('data-page'), campaign_type);
                        });
                    }
                    loading.hide();
                    if (count === 0) {
                        scene.html('<div style="text-align: center; margin-top: 16px">No data to display</div>')
                    } 
                }
            };

            var __image_selector__upload = {
                image_list_data: null,

                searchKeyword: function (keywords, scene) {
                    keywords = keywords ? searchCleanKeywords(keywords) : keywords;
                    let keywords_hash = keywords ? $.md5(keywords) : null;
                    if (__image_selector__upload.image_list_data && __image_selector__upload.image_list_data.keywords_hash === keywords_hash) {
                        return;
                    }
                    __image_selector__upload.renderImageList(scene, keywords, 1);
                },

                render: function (tab_content_container, campaign_type = null) {

                    var filter_input = $('<input />')
                        .attr({ placeholder: 'Enter keywords to search...' })
                        .insertBefore(
                            $('<div />')
                                .append('<svg style="margin-right: 8px" data-icon="osc-search" viewBox="0 0 15 9" width="15px" data-insert-cb="configOSCIcon"><use xlink:href="#osc-search"></use></svg>')
                                .append('<span>Search</span>')
                                .css({'display': 'flex'})
                                .click(function () {
                                    const keywords = filter_input[0].value ? filter_input[0].value : null;
                                    __image_selector__upload.searchKeyword(keywords, scene, 1);
                                })
                                .appendTo(
                                    $('<div />')
                                        .addClass('input')
                                        .append($('<ins />'))
                                        .appendTo($('<div />').addClass('filter-frm').appendTo(tab_content_container))
                                )
                        )
                        .keyup(function (e) {
                            if (e.keyCode !== 13) {
                                return;
                            }
                            $(this).trigger('filter');
                        })
                        .bind('filter', function () {
                            __image_selector__upload.searchKeyword(this.value, scene, 1);
                        });

                    var top_bar = $('<div />').addClass('top-bar').appendTo(tab_content_container);

                    var uploader = $('<div />').addClass('file-uploader').appendTo(top_bar).osc_uploader({
                        max_files: 1,
                        max_connections: 1,
                        process_url: $.base_url + '/catalog/backend_campaign/imageLibUpload/hash/' + OSC_HASH,
                        btn_content: $('<div />').addClass('btn btn-primary uploader-content'),
                        dragdrop_content: 'Drop here to upload',
                        image_mode: true,
                        xhrFields: {withCredentials: true},
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-OSC-Cross-Request': 'OK'
                        }
                    }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                        uploader.attr('data-uploader-step', 'queue');

                        $('<div />').addClass('uploader-progress-bar').appendTo(uploader).append($('<div />'));
                        $('<div />').addClass('step').appendTo(uploader);

                        uploader.attr('data-upload-id', file_id);
                    }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
                        if (uploader.attr('data-upload-id') !== file_id) {
                            return;
                        }

                        if (parseInt(uploaded_percent) === 100) {
                            uploader.attr('data-uploader-step', 'process');
                        } else {
                            uploader.attr('data-uploader-step', 'upload');
                            uploader.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
                        }
                    }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                        if (uploader.attr('data-upload-id') !== file_id) {
                            return;
                        }

                        uploader.removeAttr('data-upload-id');
                        uploader.removeAttr('data-uploader-step');
                        uploader.find('.uploader-progress-bar').remove();
                        uploader.find('.step').remove();

                        eval('response = ' + response);

                        if (response.result !== 'OK') {
                            alert(response.message);
                            return;
                        }

                        __image_selector__upload.image_list_data = null;

                        filter_input.val('"' + response.data.name + '"').trigger('filter');

                        pointer.success = false;
                    }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                        if (uploader.attr('data-upload-id') !== file_id) {
                            return;
                        }

                        uploader.removeAttr('data-upload-id');
                        uploader.removeAttr('data-uploader-step');
                        uploader.find('.uploader-progress-bar').remove();
                        uploader.find('.step').remove();
                    });

                    var scene = $('<div />').addClass('image-list-scene').appendTo(tab_content_container);

                    __image_selector__upload.renderImageList(scene, null, 1);
                },

                renderImageList: function (scene, keywords, page) {
                    page = parseInt(page);
                    let loading = $('<div style="text-align: center; margin-top: 16px" />').text('Loading ...');
                    scene.html(loading);
                    var keywords_hash = keywords ? $.md5(keywords) : null;

                    if (this.image_list_data === null || Math.max(this.image_list_data.current_page, 1) !== page || this.image_list_data.keywords_hash !== keywords_hash) {
                        if (this.image_list_data === true) {
                            return;
                        }

                        this.image_list_data = true;

                        $.ajax({
                            type: 'post',
                            url: $.base_url + '/catalog/backend_campaign/imageLibBrowse',
                            data: {page: page, page_size: 5, hash: OSC_HASH, keywords: keywords ? keywords : ''},
                            success: function (response) {
                                if (response.result !== 'OK') {
                                    alert(response.message);
                                    return;
                                }

                                __image_selector__upload.image_list_data = response.data;

                                __image_selector__upload.image_list_data.keywords_hash = keywords_hash;

                                __image_selector__upload.renderImageList(scene, keywords, page);
                            }
                        });

                        return;
                    }

                    var image_list = $('<div />').addClass('image-list').appendTo(scene);
                    let count = 0;
                    this.image_list_data.items.forEach(function (item_data) {
                        var thumb = $('<div />').addClass('thumb');

                        $('<div />').addClass('image-item').appendTo(image_list).append(thumb).append($('<div />').addClass('title').attr('title', item_data.title).text(item_data.name)).click(function () {
                            design_scene.trigger('image_updated', [{
                                source: {
                                    type: 'image',
                                    url: item_data.image_url,
                                    image_id: item_data.item_id,
                                    timestamp: (new Date()).getTime(),
                                    position: {
                                        "x": 0,
                                        "y": 0
                                    },
                                    dimension: {},
                                    rotation: 0
                                },
                                width: item_data.width,
                                height: item_data.height
                            }]);

                            $.unwrapContent('campaignImageSelector');
                        });

                        thumb.css('background-image', 'url(' + item_data.image_url + ')');
                        count++;
                    });

                    var pagination = buildPager(this.image_list_data.current_page, this.image_list_data.total, this.image_list_data.page_size, {
                        section: 4,
                        small: false
                    });

                    if (pagination) {
                        $('<div />').addClass('pagination-bar p10').append(pagination).appendTo(scene);

                        pagination.find('[data-page]:not(.current)').click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();

                            __image_selector__upload.renderImageList(scene, keywords, this.getAttribute('data-page'));
                        });
                    }

                    loading.hide();
                    if (count === 0) {
                        scene.html('<div style="text-align: center; margin-top: 16px">No data to display</div>')
                    } 
                }
            };

            image_selector.click(function (e) {
                e.stopImmediatePropagation();

                if (!current_print_template || current_print_template < 0) {
                    alert("No print template selected!");
                    return false;
                }

                $.unwrapContent('campaignImageSelector');

                const campaign_type = $(this).attr('campaign-type');

                var modal = $('<div />').addClass('osc-modal').width(1000);

                var header = $('<header />').appendTo(modal);

                $('<div />').addClass('title').html('&nbsp;').appendTo($('<div />').addClass('main-group').appendTo(header));

                $('<div />').addClass('close-btn').click(function () {
                    $.unwrapContent('campaignImageSelector');
                }).appendTo(header);

                var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

                var personalized_design_frm = $('<div />').addClass('campaign-image-selector').appendTo(modal_body);

                var library_tabs = $('<div />').addClass('library-tabs').appendTo(personalized_design_frm);
                var tab_content_container = $('<div />').appendTo(personalized_design_frm);

                $.each({
                    personalizedDesign: 'Personalized Design',
                    upload: 'Uploaded Image'
                }, function (tab_key, tab_title) {
                    // if (campaign_type == 'amazon' && tab_key == 'upload') {
                    //     return;
                    // }

                    $('<div />').attr('data-lib-key', tab_key).text(tab_title).appendTo(library_tabs).click(function () {
                        library_tabs.find('> *').removeClass('activated');
                        $(this).addClass('activated');

                        tab_content_container.html('');

                        eval('__image_selector__' + tab_key + '.render(tab_content_container, campaign_type)');
                    });
                });

                library_tabs.find('> div:first-child').trigger('click');

                $.wrapContent(modal, {key: 'campaignImageSelector'});

                modal.moveToCenter().css('top', '100px');
            });

            form.unbind().submit(function (e) {
                if (typeof products.campaign_config?.is_reorder !== "undefined") {
                    is_reorder = products.campaign_config.is_reorder;
                }

                var errors = [];
                var buff = {campaign_config: {print_template_config: [], is_reorder: is_reorder}, product_types: {}, custom_mockup: {}};

                if (Object.keys(VARIANTS_REORDER).length > 0) {
                    buff.campaign_config.is_reorder = 1;
                }

                if (typeof products.campaign_config?.apply_reorder !== "undefined") {
                    buff.campaign_config.apply_reorder = JSON.parse(JSON.stringify(products.campaign_config.apply_reorder));
                }

                if ($.isEmptyObject(products.product_types)) {
                    alert('Please add product for this campaign!');
                    e.preventDefault();
                    return false;
                }

                /**
                 * Validate product data
                 */
                $.each(products.product_types, function (productKey, productData) {
                    let _productData = JSON.parse(JSON.stringify(productData));

                    if (typeof productData.product_variant === 'undefined' || productData.product_variant === 'undefined' || productData.product_variant === null || productData.product_variant.length === 0) {
                        errors.push('Please add option variant for product ' + productData.name);
                    }

                    if (errors.length < 1) {
                        delete _productData.options;
                        delete _productData.product_type_variant;

                        /**
                         * Set position for new variant has not position
                         */
                        let _noPosition = [],
                            _lastPosition = 0;
                        for (let i = 0; i < _productData.product_variant.length; i++) {
                            if (typeof _productData.product_variant[i]?.position === "undefined" || _productData.product_variant[i].position === 0) {
                                _noPosition.push(_productData.product_variant[i]);
                                delete _productData.product_variant[i];
                            } else {
                                _lastPosition = _productData.product_variant[i].position;
                            }
                        }

                        if (_noPosition.length > 0) {
                            for (let i = 0; i < _noPosition.length; i++) {
                                _lastPosition++;
                                _noPosition[i].position = _lastPosition;

                                _productData.product_variant.push(_noPosition[i]);
                            }
                        }

                        buff.product_types[productKey] = $.extend({}, _productData);
                    }
                });

                if (products.campaign_config.print_template_config == null) {
                    alert('Please add print template for each product!');
                    e.preventDefault();
                    return false;
                }

                /**
                 * Validate campaign config
                 */

                $.each(printTemplateArgs, function (templateKey, templateConfig) {
                    if (typeof products.campaign_config.print_template_config[templateKey] === "undefined" || typeof products.campaign_config.print_template_config[templateKey].segments === 'undefined' || products.campaign_config.print_template_config[templateKey].segments === null) {
                        errors.push('Please set design for product ' + templateConfig.title);
                    } else {
                        var found = false;
                        $.each(templateConfig.config.segments, function (design_key) {
                            if (typeof products.campaign_config.print_template_config[templateKey].segments[design_key] !== 'undefined' && products.campaign_config.print_template_config[templateKey].segments[design_key] !== null) {
                                found = true;
                                return false;
                            }
                        });

                        if (!found) {
                            errors.push('Please set design for product ' + templateConfig.title);
                        }
                    }
                });

                if (errors.length < 1) {
                    $.each(products.campaign_config.print_template_config, function (templateKey, templateConfig) {
                        if (!templateConfig.apply_other_face || templateConfig.apply_other_face <= 0) {
                            templateConfig.apply_other_face = 0;
                        }

                        let _printTemplateData = JSON.parse(JSON.stringify(templateConfig)),
                            _printTemplateFormated = {};
                        $.each(printTemplateArgs[templateKey].config.segments, function (design_key, value) {
                            // Apply other face
                            if (_printTemplateData.apply_other_face && _printTemplateData.apply_other_face === 1) {
                                let newPositionX = value.dimension.width - _printTemplateData.segments[_printTemplateData.selected_design].source.position.x - _printTemplateData.segments[_printTemplateData.selected_design].source.dimension.width;

                                if (design_key !== _printTemplateData.selected_design) {
                                    delete _printTemplateData.segments[design_key];

                                    let designConfigOtherFace = JSON.parse(JSON.stringify(_printTemplateData.segments[_printTemplateData.selected_design]));
                                    designConfigOtherFace.source.position.x = newPositionX;

                                    _printTemplateData.segments[design_key] = designConfigOtherFace;
                                }
                            }

                            if (typeof _printTemplateData.segments?.[design_key] !== "undefined") {
                                _printTemplateFormated[design_key] = _printTemplateData.segments?.[design_key];
                            }
                        });

                        delete _printTemplateData.segments;
                        _printTemplateData.segments = JSON.parse(JSON.stringify(_printTemplateFormated));

                        $.each(_printTemplateData.segments, function (key, value) {
                            /**
                             * Clean data. Not save svg_content and url of design
                             */
                            if (_printTemplateData.segments[key].source.type === 'personalizedDesign') {
                                delete _printTemplateData.segments[key].source.svg_content;
                                delete _printTemplateData.segments[key].source.url;

                                if (typeof _printTemplateData.segments[key].source['option_default_values'] !== "undefined") {
                                    delete _printTemplateData.segments[key].source['option_default_values'].svg_content;
                                }
                            }

                            if (_printTemplateData.segments[key].source.type === 'image') {
                                delete _printTemplateData.segments[key].source.url;
                            }

                        });

                        buff.campaign_config.print_template_config.push(_printTemplateData);
                    });
                }

                const mockupImages = [];
                dataCustomMockups = _getMockupsData(products.custom_mockup);
                for (const key in dataCustomMockups) {
                    const image = dataCustomMockups[key];
                    mockupImages.push({
                        image_id: image.id || null,
                        url: image.url,
                        variant_ids: image.variantIds ? Object.keys(image.variantIds).map(n => Number(n)) : []
                    })
                }
                buff.custom_mockup = mockupImages;

                const mockupVideos = _getMockupVideos(products.custom_mockup);
                let missingThumbnail = false;

                buff.mockup_videos = Object.entries(mockupVideos).map(([video_id, video]) => {
                    if (!video.thumbnail) {
                        missingThumbnail = true;
                    }
                    return {
                        video_id: Number(video_id),
                        url: video.url,
                        thumbnail: video.thumbnail,
                        variant_ids_position: video.variantIds || {},
                        duration: video.duration || 0,
                    }
                });

                if (missingThumbnail) {
                    errors.push("Thumbnails must be uploaded for all videos.");
                }

                if (errors.length > 0) {
                    alert(errors.join("\n"));
                    e.preventDefault();
                    return false;
                }

                $('<input />').attr({
                    type: 'hidden',
                    name: 'campaign_data',
                    value: JSON.stringify(buff)
                }).appendTo(form);
            });

            window.updateDesignDefaultValue = function(designData) {
                Object.keys(designData).forEach((key) => {
                    const value = designData[key];
                    if (typeof value === 'object') designData[key] = JSON.stringify(value);
                });
                try {
                    let data = _fetchCurrentDesign();        
                    $.ajax({
                        url: $.base_url + '/catalog/backend_campaign/getSvgData/hash/' + OSC_HASH,
                        type: 'post',
                        data: {id: data.source.design_id, config: designData},
                        success: function (response) {
                            if (response.result !== 'OK') {
                                alert(response.message);
                                return;
                            }
                            $.unwrapContent('personalizeBuilder_personalizePanel', true);
                            _modifiedTimestamp(data);
                            data.source.option_default_values = {
                                options: designData,
                                svg_content: response.data.svg
                            };
                            design_scene.find('.frame .scene .image').trigger('updated', [data]);
                        }
                    });
                } catch (error) {
                    console.log(error);
                    alert(error.message);
                }
            };
            window.closeModal = function() {
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            };
            window.removeDesignDefaultValue = function() {
                sessionStorage.removeItem('_source');
                try {
                    let data = _fetchCurrentDesign();
                    delete data.source.option_default_values;
                    design_scene.find('.frame .scene .image').trigger('updated', [data]);
                } catch (error) {
                    console.log(error);
                    alert(error.message);
                }
                $.unwrapContent('personalizeBuilder_personalizePanel', true);
            };

            window.getDesignDefaultValue = function() {
                const iframe = document.getElementById('iframePersonalizedForm');
                if(!iframe) return;
                let data = _fetchCurrentDesign();
                let option_default_values = data?.source?.option_default_values || {};

                iframe.contentWindow.postMessage({
                    action: 'getDesignDefaultValue',
                    data: option_default_values
                }, '*');
            }
        });
    };

    if (window.addEventListener) {
        window.addEventListener("message", onMessage, false);        
    } 
    else if (window.attachEvent) {
        window.attachEvent("onmessage", onMessage, false);
    }

    function onMessage(event) {
        const data = event.data;
        if (typeof(window[data.func]) == "function") {
            window[data.func].call(null, data.data);
        }
    }

})(jQuery);