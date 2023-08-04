let data;
let dataAddMoreProduct;

window.initAddNewLocationModal = function () {
    $(this).click(function () {
        $.unwrapContent('addNewLocationFrm');

        let modal = $('<div/>').addClass('osc-modal add-new-location-modal');
        let header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html('Add New Location').appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('addNewLocationFrm');
        }).appendTo(header);

        $('<strong/>').addClass('d-block mt-3 mb-2').text('Select a location').appendTo(modal);
        let location_input_row = $('<div />').appendTo(modal);
        initSelectGroupLocation(location_input_row);

        $('<div/>').css('flex', 'auto').appendTo(modal);
        const btnDiv = $('<div/>').addClass('d-flex justify-content-end').appendTo(modal);
        $('<div/>').text('Cancel').addClass('btn btn-outline mr5').appendTo(btnDiv).click(function (){
            $.unwrapContent('addNewLocationFrm');
        });
        $('<div/>').text('Add').click(function () {
            const location = location_input_row.children('select').val();
            $.ajax({
                type: 'GET',
                url: $.base_url + '/catalog/backend_productTypeVariantPrice/getLocationName/hash/' + OSC_HASH,
                data: {
                    location,
                },
                success: function (response) {
                    localStorage.setItem('locationProductType', location);
                    $('.location-select').children('span').first().text(response.data.data);
                    data = [];
                    renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
                    toggleAddMoreButton(true);
                    $.ajax({
                        type: "GET",
                        url: $.base_url + '/catalog/backend_productTypeVariantPrice/getMoreProductOfLocation/hash/' + OSC_HASH,
                        success: function(response){
                            dataAddMoreProduct = Object.keys(response.data.data).map(key => {
                                const variants = response.data.data[key].variants.map(variant => {
                                    return {
                                        ...variant,
                                        check: false,
                                    }
                                })
                                return {
                                    id: key,
                                    product_type_name: response.data.data[key].product_type_name,
                                    check: false,
                                    indeterminate: false,
                                    variants: variants
                                }
                            });
                        }
                    });
                    $.unwrapContent('addNewLocationFrm');
                }
            });
        }).addClass('btn-add').appendTo(btnDiv);
        $.wrapContent(modal, {key: 'addNewLocationFrm', backdrop:'static'});

        modal.moveToCenter().css('top', '100px');
    })
};

//add class when sticky element stick
apply_stickies()

window.addEventListener('scroll', function() {
    apply_stickies()
})

function apply_stickies() {
    var _$stickies = [].slice.call(document.querySelectorAll('.product-type-variants__actions'))
    _$stickies.forEach(function(_$sticky) {
        if (CSS.supports && CSS.supports('position', 'sticky')) {
            apply_sticky_class(_$sticky)
        }
    })
}

function apply_sticky_class(_$sticky) {
    var currentOffset = Math.abs([].slice.call(document.querySelectorAll('body'))[0].getBoundingClientRect().top)
    var stickyOffset = parseInt($('.header').outerHeight(true))
    var isStuck = currentOffset > stickyOffset

    _$sticky.classList.toggle('is-sticky', isStuck)
}

const renderBaseConfigRow = (table, baseCost) => {
    if (baseCost && baseCost.length) {
        baseCost.forEach(bc => {
            const tr = $('<tr/>').addClass('base-cost-config-row').appendTo(table);
            $('<td/>').append($('<input/>').attr('placeholder','_').val(bc.quantity).on('input', validateInputPrice)).appendTo(tr);
            $('<td/>').append($('<input/>').attr('placeholder','_').val(bc.base_cost).on('input', validateInputPrice)).appendTo(tr);
            $('<td/>').addClass('text-right').append($('<i/>').append($.renderIcon('trash-alt-regular')).click(function(){
                tr.remove();
            })).appendTo(tr);
        })
    } else {
        const tr = $('<tr/>').addClass('base-cost-config-row').appendTo(table);
        $('<td/>').append($('<input/>').attr('placeholder','_').on('input', validateInputPrice)).appendTo(tr);
        $('<td/>').append($('<input/>').attr('placeholder','_').on('input', validateInputPrice)).appendTo(tr);
        $('<td/>').addClass('text-right').append($('<i/>').append($.renderIcon('trash-alt-regular')).click(function(){
            tr.remove();
        })).appendTo(tr);
    }
}

const editProductTypeVariantModal = (dataRender, isAddNewBasecost = false) => {
    $.unwrapContent('editProductTypeVariantFrm');
    if (!dataRender.productTypes.length) return $.unwrapContent('editProductTypeVariantFrm');
    let product_type_variant_ids = [];
    let modal = $('<div/>').addClass('osc-modal edit-product-type-variant-modal');
    const leftSide = $('<div/>').addClass('left-side').appendTo(modal);
    $('<strong/>').addClass('d-block mb-3').text('Product Type Variant').appendTo(leftSide);
    const listProductType = $('<div>').addClass('left-side__list-product').appendTo(leftSide);
    dataRender.productTypes?.forEach(productType => {
        if (productType.variants.length) {
            const item = $('<div>').addClass('left-side__list-product-item open').appendTo(listProductType);
            const header =  $('<div>').addClass('font-semibold header')
                .append($('<span>').html(productType.product_type_name))
                .append($.renderIcon('angle-down-solid'))
                .appendTo(item);
            const list = $('<div>').addClass('list-variants').appendTo(item);
            list.css('height', `${productType.variants.length * 32}px`)
            productType.variants.forEach(variant => {
                product_type_variant_ids.push(variant.id);
                const div = $('<div>').appendTo(list);
                const childDiv = $('<div>').appendTo(div);
                $('<span>').addClass('mr-2').html(`&#8226`).appendTo(childDiv);
                $('<span>').text(variant.title).appendTo(childDiv);
                if(!isAddNewBasecost) {
                    $($.renderIcon('times')).click(function(){
                        productType.variants = productType.variants.filter(v => v.id !== variant.id);
                        if (!productType.variants.length) dataRender = {
                            ...dataRender,
                            productTypes: dataRender.productTypes.filter(p => p.id !== productType.id)
                        }
                        editProductTypeVariantModal(dataRender)
                    }).appendTo(div)
                }
            })
            header.click(function(){
                item.toggleClass('open');
                item.hasClass('open') ? list.css('height', `${productType.variants.length * 32}px`) : list.css('height', '0px')
            })
        }
    })
    const rightSide = $('<div/>').addClass('right-side').appendTo(modal);
    $('<strong/>').addClass('d-block').text('Price').appendTo(rightSide);
    const priceInput =  $('<input/>').val(dataRender.price).on('input', validateInputPrice).appendTo(rightSide);
    $('<strong/>').addClass('d-block').text('Compare price').appendTo(rightSide);
    const comparePriceInput = $('<input/>').val(dataRender.comparePrice).on('input', validateInputPrice).appendTo(rightSide);
    const baseContainer = $('<div/>').addClass('add-new-base-config').appendTo(rightSide);
    const table = $('<table/>').appendTo(baseContainer);
    const tHead = $('<tr/>').appendTo(table);
    $('<th/>').append($('<strong/>').text('Quantity')).appendTo(tHead);
    $('<th/>').append($('<strong/>').text('Base cost')).appendTo(tHead);
    $('<th/>').appendTo(tHead);
    if (dataRender.productTypes.length == 1 && dataRender.productTypes[0].variants.length == 1) renderBaseConfigRow(table, dataRender.productTypes[0].variants[0].baseCostConfigs);
    else renderBaseConfigRow(table);
    const addNewBase = $('<div/>').addClass('d-flex add-new-base-config__btn').click(() => renderBaseConfigRow(table)).appendTo(baseContainer);
    $($.renderIcon('icon-add-more-product')).appendTo(addNewBase);
    $('<strong/>').text('Add new base cost config').appendTo(addNewBase);

    const btnGroup = $('<div/>').addClass('d-flex edit-product-type-variant-modal__action-buttons').appendTo(rightSide);
    $('<button>').addClass('btn btn-outline').text('Cancel').click(() => $.unwrapContent('editProductTypeVariantFrm')).appendTo(btnGroup);
    $('<button>').addClass('btn btn-primary').click(function(){
        const button = $(this);
        button.text('Saving...')
        const url = localStorage.getItem('locationProductType') == 'default' ? 'setDefaultPrice' : 'setLocationPrice';
        let base_cost_configs = [];
        table.find('.base-cost-config-row').each(function(){
            const quantity = $(this).find('input').first().val();
            const base_cost = $(this).find('input').last().val();
            if(quantity && base_cost) base_cost_configs.push({quantity, base_cost});
        })
        $.ajax({
            type: 'POST',
            url: $.base_url + `/catalog/backend_productTypeVariantPrice/${url}/hash/` + OSC_HASH,
            data: {
                product_type_variant_ids,
                price: priceInput.val(),
                compare_at_price: comparePriceInput.val(),
                base_cost_configs,
                location: localStorage.getItem('locationProductType'),
            },
            success: function (response) {
                if(response.result == 'OK') {
                    if (dataRender.productTypes.length == 1 && dataRender.productTypes[0].variants.length == 1) {
                        data.forEach(productType => {
                            if (productType.id == dataRender.productTypes[0].id){
                                productType.variants.forEach(variant =>{
                                    if(variant.id == dataRender.productTypes[0].variants[0].id) {
                                        variant.price = priceInput.val()
                                        variant.comparePrice = comparePriceInput.val()
                                        variant.baseCostConfigs = base_cost_configs;
                                    }
                                })
                            }
                        })
                    } else {
                        data.forEach(productType => {
                            productType.variants.forEach(variant =>{
                                if(variant.check) {
                                    variant.price = priceInput.val()
                                    variant.comparePrice = comparePriceInput.val()
                                    variant.baseCostConfigs = base_cost_configs;
                                }
                            })
                        })
                    }
                    renderProductTypeVariantPriceList($('.product-type-variants-container'), data)
                    editVariantSuccessModal();
                    $.unwrapContent('editProductTypeVariantFrm');
                } else {
                    alert(response.message);
                }
                button.text('Save');
            }
        })
    }).text('Save').appendTo(btnGroup);
    $.wrapContent(modal, {key: 'editProductTypeVariantFrm'});

    modal.moveToCenter().css('top', '100px');
}

window.initEditProductTypeVariantModal = function () {
    $(this).click(function () {
        let result = [];
        data.forEach(productType => {
            let variants = [];
            productType.variants.forEach(variant => {
                if (variant.check){
                    variants.push(variant);
                }
            });
            if (variants.length) {
                result.push({
                    ...productType,
                    variants,
                })
            }
        });
        if (result.length) {
            editProductTypeVariantModal({
                price: result.length == 1 && result[0].variants.length == 1 ? result[0].variants[0].price : '',
                comparePrice: result.length == 1 && result[0].variants.length == 1 ? result[0].variants[0].comparePrice : '',
                productTypes: result
            });
        } else $(this).parent().removeClass('show')
    })
}

window.initDeleteAllSelectedVariants = function () {
    $(this).click(function(){
        const flag = confirm('Are you sure you want to delete this product type variants?');
        if (flag) {
            $(this).addClass('delete');
            const that = $(this);
            let product_type_variant_ids = [];
            data = data.map(p => {
                const newVariants = p.variants.filter(v => !v.check);
                const selectVariants = p.variants.filter(v => v.check).map(v => v.id);
                product_type_variant_ids = [...product_type_variant_ids, ...selectVariants];
                if (newVariants.length) return {
                    ...p,
                    variants: newVariants,
                }
                return null;
            }).filter(p => p);
            $.ajax({
                type: "POST",
                url: $.base_url + '/catalog/backend_productTypeVariantPrice/deleteManyPriceOfLocation/hash/' + OSC_HASH,
                data: {product_type_variant_ids, location: localStorage.getItem('locationProductType')},
                success: function(response){
                    if(response.result == 'OK') {
                        alert('Delete product type variant successfully!')
                        $.ajax({
                            type: "GET",
                            url: $.base_url + '/catalog/backend_productTypeVariantPrice/getListPrice/hash/' + OSC_HASH,
                            data: {location_data: localStorage.getItem('locationProductType')},
                            success: function(response){
                                toggleAddMoreButton(response.data.data.getMoreAble);
                                data = Object.keys(response.data.data.data).map(key => {
                                    const variants = response.data.data.data[key].variants.map(variant => {
                                        return {
                                            ...variant,
                                            check: false,
                                        }
                                    })
                                    return {
                                        id: key,
                                        product_type_name: response.data.data.data[key].product_type_name,
                                        check: false,
                                        indeterminate: false,
                                        getMoreAble: response.data.data.data[key].getMoreAble,
                                        variants: variants
                                    }
                                });
                                renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
                                $.ajax({
                                    type: "GET",
                                    url: $.base_url + '/catalog/backend_productTypeVariantPrice/getMoreProductOfLocation/hash/' + OSC_HASH,
                                    data: {product_type_ids: Object.keys(response.data.data.data)},
                                    success: function(response){
                                        dataAddMoreProduct = Object.keys(response.data.data).map(key => {
                                            const variants = response.data.data[key].variants.map(variant => {
                                                return {
                                                    ...variant,
                                                    check: false,
                                                }
                                            })
                                            return {
                                                id: key,
                                                product_type_name: response.data.data[key].product_type_name,
                                                check: false,
                                                indeterminate: false,
                                                variants: variants
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }else {
                        alert(response.message)
                    }
                    that.removeClass('delete');
                    that.parent().removeClass('show');
                }
            });
        }
    })
}

const editVariantSuccessModal = () => {
    $.unwrapContent('editVariantSuccessModalFrm');
    var modal = $('<div />').addClass('osc-modal edit-variants-success-modal').width(320);
    var header = $('<header />').appendTo(modal);

    $('<div />').addClass('close-btn').click(function () {
        $.unwrapContent('editVariantSuccessModalFrm');
    }).appendTo(header);
    $($.renderIcon('edit-variant-success')).appendTo(modal);
    $('<p>').text('Your update has been saved!').appendTo(modal);
    $.wrapContent(modal, {key: 'editVariantSuccessModalFrm'});
    modal.moveToCenter().css('top', '100px');
}

const renderListLocation = (container, data) => {
    data.forEach(location => {
        const listItem = $('<div/>').addClass('product-type-info-modal-list__item').appendTo(container);
        $('<div/>').text(location.name).click(function(){
            localStorage.setItem('locationProductType', location.locationData);
            $.ajax({
                type: "GET",
                url: $.base_url + '/catalog/backend_productTypeVariantPrice/getListPrice/hash/' + OSC_HASH,
                data: {location_data: location.locationData},
                success: function(response){
                    toggleAddMoreButton(response.data.data.getMoreAble);
                    data = Object.keys(response.data.data.data).map(key => {
                        const variants = response.data.data.data[key].variants.map(variant => {
                            return {
                                ...variant,
                                check: false,
                            }
                        })
                        return {
                            id: key,
                            product_type_name: response.data.data.data[key].product_type_name,
                            check: false,
                            indeterminate: false,
                            getMoreAble: response.data.data.data[key].getMoreAble,
                            variants: variants
                        }
                    });
                    renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
                    getDataLocation($('.location-select'));

                    $.unwrapContent('productTypeInfoFrm');
                    $.ajax({
                        type: "GET",
                        url: $.base_url + '/catalog/backend_productTypeVariantPrice/getMoreProductOfLocation/hash/' + OSC_HASH,
                        data: {product_type_ids: Object.keys(response.data.data.data)},
                        success: function(response){
                            dataAddMoreProduct = Object.keys(response.data.data).map(key => {
                                const variants = response.data.data[key].variants.map(variant => {
                                    return {
                                        ...variant,
                                        check: false,
                                    }
                                })
                                return {
                                    id: key,
                                    product_type_name: response.data.data[key].product_type_name,
                                    check: false,
                                    indeterminate: false,
                                    variants: variants
                                }
                            });
                        }
                    });
                }
            });
        }).addClass('w-50 location').appendTo(listItem);
        $('<div/>').text(location.price).addClass('w-25').appendTo(listItem);
        $('<div/>').text(location.comparePrice).addClass('w-25').appendTo(listItem);
    })
}

const searchByName = (data, searchTxt) => {
    const result = data.map(item => {
        if (item.name.toLowerCase().includes(searchTxt.toLowerCase().trim())) return item;
    }).filter(n => n);
    return result;
}

const searchByProductTypeAndVariant = (data, searchTxt) => {
    const result = [];
    data.forEach(productType => {
        if (productType.product_type_name.toLowerCase().includes(searchTxt.toLowerCase()) || productType.check) {
            result.push(productType);
        } else {
            const variants = [];
            productType.variants.forEach(variant => {
                if (variant.title.toLowerCase().includes(searchTxt.toLowerCase()) || variant.check){
                    variants.push(variant);
                }
            });
            if (variants.length) {
                result.push({
                    ...productType,
                    variants,
                })
            }

        }
    });
    return result;
}

window.initProductTypeInfoModal = function () {
    $(this).click(function () {
        const product_type_variant_id = $(this).attr('data-variant-id');
        $.unwrapContent('productTypeInfoFrm');

        $.ajax({
            type: 'GET',
            url: $.base_url + '/catalog/backend_productTypeVariantPrice/getLocationPriceInfo/hash/' + OSC_HASH,
            data: {
                product_type_variant_id,
            },
            success: function (response) {
                if(response.result == 'OK') {
                    const locationData = response.data.data.map(location => ({name: location.location, price: location.price, comparePrice: location.compare_at_price, locationData: location.location_data}));
                    let modal = $('<div/>').addClass('osc-modal product-type-info-modal');
                    let header = $('<header />').appendTo(modal);

                    $('<div />').addClass('title').html('Infomation').appendTo($('<div />').addClass('main-group').appendTo(header));
                    $('.product-search__input').clone().children('input').val('').attr('placeholder', 'Search location').on('input', debounce(function() {
                        const searchTxt = $(this).val().trim();
                        const result = searchByName(locationData, searchTxt);
                        const content = modal.children('.product-type-info-modal-content');
                        content.empty();
                        modal.children('.loader').removeClass('show');
                        renderListLocation(content, result);
                        if (!result.length) {
                            $('<strong/>').addClass('py-3 d-block mx-auto').css('text-align', 'center').text('Sorry, we couldn\'t find any results for "' + searchTxt + '"').appendTo(content);
                        }
                    }, 500)).on('input', function(){
                        modal.children('.product-type-info-modal-content').empty();
                        modal.children('.loader').addClass('show');
                    }).attr('class', 'search-location').appendTo(modal);
                    const contentHeader = $('<div/>').addClass('product-type-info-modal-list__header').appendTo(modal);
                    $('<strong/>').text('Location').addClass('w-50 d-block').appendTo(contentHeader);
                    $('<strong/>').text('Price').addClass('w-25 d-block').appendTo(contentHeader);
                    $('<strong/>').text('Compare Price').addClass('w-25 d-block').appendTo(contentHeader);
                    const content = $('<div/>').addClass('product-type-info-modal-content').appendTo(modal);

                    renderListLocation(content, locationData);
                    $('<div class="loader">Loading...</div>').appendTo(modal);
                    $('<div />').addClass('close-btn').click(function () {
                        $.unwrapContent('productTypeInfoFrm');
                    }).appendTo(header);
                    $.wrapContent(modal, {key: 'productTypeInfoFrm'});

                    modal.moveToCenter().css('top', '100px');
                }
            }
        });
    })
};

const renderProductTypeList = (container, data) => {
    data.forEach((productType, index) => {
        const item = $('<div/>').addClass('add-more-product-type-modal-item').appendTo(container);
        const itemHeader = $('<div/>').addClass('add-more-product-type-modal-item__header').appendTo(item).click(function() {
            $(item).toggleClass('expand');
        });
        let flagChecked = true;
        let flagIndeterminate = false;
        productType.variants.forEach(variant => {
            if (variant.check){
                flagIndeterminate = true;
            }else{
                flagChecked = false;
            }

        });
        $($.renderIcon('icon-polygon')).appendTo($('<span/>').appendTo(itemHeader));
        $('<input/>').addClass('custom-checkbox').attr('type', 'checkbox').click(function(e){
            e.stopPropagation();
        }).change(function(){
            productType.variants.forEach(variant => {
                variant.check = $(this).is(':checked');
            })
            $(this).parent().parent().children('.add-more-product-type-modal-item__list').find('input').prop('checked', $(this).is(':checked'));
            $(this).prop('checked', $(this).is(':checked'));
        }).prop('checked', flagChecked).prop('indeterminate', flagChecked ? false : flagIndeterminate).appendTo(itemHeader);
        $('<strong/>').text(productType.product_type_name).appendTo(itemHeader);
        productType.variants.forEach(variant => {
            const itemList = $('<div/>').addClass('add-more-product-type-modal-item__list').appendTo(item);
            const itemListItem = $('<div/>').addClass('product-type-variant-row').appendTo(itemList);
            $('<input/>').attr('type', 'checkbox').change(function(){
                let atLeastOne = false;
                let allCheck = true;
                for (let i = 0; i < productType.variants.length; i++) {
                    if (productType.variants[i].id === variant.id) {
                        productType.variants[i].check = $(this).is(':checked');
                        $(this).prop('checked', $(this).is(':checked'));
                    }
                    if (productType.variants[i].check) atLeastOne = true;
                    if (!productType.variants[i].check) allCheck = false;
                }
                if (atLeastOne) {
                    $(this).closest('.add-more-product-type-modal-item').children('.add-more-product-type-modal-item__header').children('input').prop('checked', false).prop("indeterminate", true);
                }
                if (allCheck) {
                    $(this).closest('.add-more-product-type-modal-item').children('.add-more-product-type-modal-item__header').children('input').prop('checked', true).prop("indeterminate", false);
                }
                if (!atLeastOne) {
                    $(this).closest('.add-more-product-type-modal-item').children('.add-more-product-type-modal-item__header').children('input').prop('checked', false).prop("indeterminate", false);
                }
            }).prop('checked', variant.check).appendTo(itemListItem);
            $('<strong/>').text(variant.title).appendTo(itemListItem);
        })
    })
}

window.initAddMoreProductTypeModal = function () {
    if (localStorage.getItem('locationProductType') == 'default') {
        $(this).addClass('d-none');
    }
    $(this).click(function () {
        $.unwrapContent('addMoreProductTypeFrm');

        let modal = $('<div/>').addClass('osc-modal add-more-product-type-modal');
        let header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html('Add More Product Type').appendTo($('<div />').addClass('main-group').appendTo(header));
        const contentModal = $('<div/>').addClass('modal-content').appendTo(modal);
        let price;
        let compare_at_price;
        const renderScene1 = () => {
            $('.product-search__input').clone().children('input').val('').attr('placeholder', 'Search product type').on('input', debounce(function() {
                const searchTxt = $(this).val().trim();
                const result = searchByProductTypeAndVariant(dataAddMoreProduct, searchTxt);
                const content = modal.children('.modal-content').children('.add-more-product-type-modal-content');
                content.empty();
                modal.children('.modal-content').children('.loader').removeClass('show');
                renderProductTypeList(content, result);
                if (!result.length) {
                    $('<strong/>').addClass('py-3 d-block mx-auto').css('text-align', 'center').text('Sorry, we couldn\'t find any results for "' + searchTxt + '"').appendTo(content);
                }
            }, 500)).on('input', function(){
                modal.children('.modal-content').children('.add-more-product-type-modal-content').empty();
                modal.children('.modal-content').children('.loader').addClass('show');
            }).attr('class', 'search-location').appendTo(contentModal);
            $('<strong/>').text('Product Type').addClass('d-block mt-3').appendTo(contentModal);
            const content = $('<div/>').addClass('add-more-product-type-modal-content').appendTo(contentModal);
            renderProductTypeList(content, dataAddMoreProduct);

            $('<div class="loader">Loading...</div>').appendTo(contentModal);

            const footer = $('<div/>').addClass('add-more-product-type-modal__footer').appendTo(contentModal);
            $('<div/>').addClass('btn btn-outline mr5').text('Cancel').click(function () {
                $.unwrapContent('addMoreProductTypeFrm');
            }).appendTo(footer);
            $('<div/>').addClass('btn-add').text('Next').click(function () {
                contentModal.empty();
                renderScene2();
            }).appendTo(footer);
        }

        const renderScene2 = () => {
            $('<strong/>').text('Price').addClass('d-block mt-2').appendTo(contentModal);
            $('<input/>').attr({
                'placeholder': 'Enter',
                type: 'number'
            }).val(price).change(function(){
                price = $(this).val();
            }).appendTo(contentModal);
            $('<strong/>').text('Compare Price').addClass('d-block mt-3').appendTo(contentModal);
            $('<input/>').attr({
                'placeholder': 'Enter',
                type: 'number'
            }).val(compare_at_price).change(function(){
                compare_at_price = $(this).val();
            }).appendTo(contentModal);
            const footer = $('<div/>').addClass('add-more-product-type-modal__footer').appendTo(contentModal);
            $('<div/>').addClass('btn btn-outline mr5').text('Back').click(function () {
                contentModal.empty();
                renderScene1();
            }).appendTo(footer);
            $('<div/>').addClass('btn-add').text('Done').click(function(){
                let product_type_variant_ids = [];
                dataAddMoreProduct.forEach(productType => {
                    productType.variants.forEach(variant => {
                        if(variant.check) product_type_variant_ids.push(variant.id);
                    })
                })
                $.ajax({
                    type: 'POST',
                    url: $.base_url + '/catalog/backend_productTypeVariantPrice/setLocationPrice/hash/' + OSC_HASH,
                    data: {
                        location: localStorage.getItem('locationProductType'),
                        price,compare_at_price,product_type_variant_ids

                    },
                    success: function (response) {
                        if (response.result == 'OK') {
                            $.ajax({
                                type: "GET",
                                url: $.base_url + '/catalog/backend_productTypeVariantPrice/getListPrice/hash/' + OSC_HASH,
                                data: {location_data: localStorage.getItem('locationProductType')},
                                success: function(response){
                                    toggleAddMoreButton(response.data.data.getMoreAble);
                                    data = Object.keys(response.data.data.data).map(key => {
                                        const variants = response.data.data.data[key].variants.map(variant => {
                                            return {
                                                ...variant,
                                                check: false,
                                            }
                                        })
                                        return {
                                            id: key,
                                            product_type_name: response.data.data.data[key].product_type_name,
                                            check: false,
                                            indeterminate: false,
                                            getMoreAble: response.data.data.data[key].getMoreAble,
                                            variants: variants
                                        }
                                    });
                                    renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
                                    $.ajax({
                                        type: "GET",
                                        url: $.base_url + '/catalog/backend_productTypeVariantPrice/getMoreProductOfLocation/hash/' + OSC_HASH,
                                        data: {product_type_ids: Object.keys(response.data.data.data)},
                                        success: function(response){
                                            dataAddMoreProduct = Object.keys(response.data.data).map(key => {
                                                const variants = response.data.data[key].variants.map(variant => {
                                                    return {
                                                        ...variant,
                                                        check: false,
                                                    }
                                                })
                                                return {
                                                    id: key,
                                                    product_type_name: response.data.data[key].product_type_name,
                                                    check: false,
                                                    indeterminate: false,
                                                    variants: variants
                                                }
                                            });
                                        }
                                    });
                                    getDataLocation($('.location-select'));
                                }
                            });
                            $.unwrapContent('addMoreProductTypeFrm');
                        }
                    }
                })
            }).appendTo(footer);
        }

        renderScene1();

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('addMoreProductTypeFrm');
        }).appendTo(header);
        $.wrapContent(modal, {key: 'addMoreProductTypeFrm'});

        modal.moveToCenter().css('top', '100px');
    })
};

function addMoreVariantModal(data) {
    return function(e) {
        const product_type_id = $(this).attr('data-product-type-id');
        let product_type_variant_ids;
        for (let i = 0; i < data.length; i++) {
            if (data[i].id == product_type_id) {
                product_type_variant_ids = data[i].variants.map(variant => variant.id);
                break;
            }
        }
        $.ajax({
            type: "GET",
            url: $.base_url + '/catalog/backend_productTypeVariantPrice/getMoreVariantOfLocation/hash/' + OSC_HASH,
            data: {product_type_id, product_type_variant_ids},
            success: function(response){
                let productTypeStatus = {
                    check: false,
                    indeterminate: false,
                }
                const dataVariant = response.data.data[product_type_id].variants.map(v => ({...v, name: v.title, check: false}));
                let price;
                let compare_at_price;

                $.unwrapContent('addMoreVariantFrm');

                let modal = $('<div/>').addClass('osc-modal add-more-variant-modal');
                let header = $('<header />').appendTo(modal);

                $('<div />').addClass('title').html('Add More Variant').appendTo($('<div />').addClass('main-group').appendTo(header));
                let content = $('<div/>').addClass('modal-content').appendTo(modal);

                const renderVariantList = (container, data) => {
                    data.forEach(variant => {
                        const itemList = $('<div/>').addClass('product-type-variant-row').appendTo(container);
                        $('<input/>').attr('type', 'checkbox').prop('checked', variant.check).change(function(){
                            let allCheck = true;
                            let atLeastOne = false;
                            for (let i = 0; i < dataVariant.length; i++) {
                                if (dataVariant[i].id === variant.id) {
                                    dataVariant[i].check = $(this).is(':checked');
                                }
                                if (!dataVariant[i].check) allCheck = false;
                                if (dataVariant[i].check) atLeastOne = true;
                            }
                            if (!$(this).is(':checked')) {
                                container.prev().children('input').prop('checked', false);
                            }
                            if (atLeastOne && !$(this).is(':checked')) {
                                container.prev().children('input').prop('checked', false).prop('indeterminate', true);
                                productTypeStatus = {
                                    check: false,
                                    indeterminate: true,
                                }
                            };
                            if (atLeastOne && $(this).is(':checked')) {
                                container.prev().children('input').prop('checked', false).prop('indeterminate', true);
                                productTypeStatus = {
                                    check: false,
                                    indeterminate: true,
                                }
                            };
                            if (allCheck) {
                                container.prev().children('input').prop('checked', true).prop('indeterminate', false);
                                productTypeStatus = {
                                    check: true,
                                    indeterminate: false,
                                }
                            }
                            if (!atLeastOne && !$(this).is(':checked')) {
                                container.prev().children('input').prop('checked', false).prop('indeterminate', false);
                                productTypeStatus = {
                                    check: false,
                                    indeterminate: false,
                                }
                            }

                        }).appendTo(itemList);
                        $('<strong/>').text(variant.name).appendTo(itemList);
                    })
                }

                const renderScene1 = () => {
                    $('.product-search__input').clone().children('input').val('').attr('placeholder', 'Search variant').on('input', debounce(function() {
                        const searchTxt = $(this).val().trim();
                        const result = searchByName(dataVariant, searchTxt);
                        const content = modal.children('.modal-content').children('.add-more-variant-modal__list');
                        content.empty();
                        modal.children('.modal-content').children('.loader').removeClass('show');
                        renderVariantList(content, result);
                        if (!result.length) {
                            $('<strong/>').addClass('py-3 d-block mx-auto').css('text-align', 'center').text('Sorry, we couldn\'t find any results for "' + searchTxt + '"').appendTo(content);
                        }
                    }, 500)).on('input', function(){
                        modal.children('.modal-content').children('.add-more-variant-modal__list').empty();
                        modal.children('.modal-content').children('.loader').addClass('show');
                    }).attr('class', 'search-location').appendTo(content);
                    $('<strong/>').text('Variant').addClass('d-block mt-3').appendTo(content);
                    const itemHeader = $('<div/>').addClass('add-more-variant-modal__header').appendTo(content);
                    $('<input/>').addClass('custom-checkbox').attr('type', 'checkbox').change(function(){
                        dataVariant.forEach(variant => {
                            variant.check = $(this).is(':checked');
                        })
                        productTypeStatus.check = $(this).is(':checked');
                        modal.children('.modal-content').children('.add-more-variant-modal__list').children('.product-type-variant-row').children('input').prop('checked', $(this).is(':checked'));
                    }).prop('checked', productTypeStatus.check).prop('indeterminate', productTypeStatus.indeterminate).appendTo(itemHeader);
                    $('<strong/>').text(response.data.data[product_type_id].product_type_name).appendTo(itemHeader);
                    const list = $('<div/>').addClass('add-more-variant-modal__list').appendTo(content);
                    renderVariantList(list, dataVariant);
                    $('<div class="loader">Loading...</div>').appendTo(content);
                    const footer = $('<div/>').addClass('add-more-product-type-modal__footer').appendTo(content);
                    $('<div/>').addClass('btn btn-outline mr5').text('Cancel').click(function () {
                        $.unwrapContent('addMoreVariantFrm');
                    }).appendTo(footer);
                    $('<div/>').addClass('btn-add').text('Next').click(function(){
                        content.empty();
                        renderScene2();
                    }).appendTo(footer);
                }

                const renderScene2 = () => {
                    $('<strong/>').text('Price').addClass('d-block mt-2').appendTo(content);
                    $('<input/>').attr({
                        'placeholder': 'Enter',
                        type: 'number'
                    }).val(price).change(function(){
                        price = $(this).val();
                    }).appendTo(content);
                    $('<strong/>').text('Compare Price').addClass('d-block mt-3').appendTo(content);
                    $('<input/>').attr({
                        'placeholder': 'Enter',
                        type: 'number'
                    }).val(compare_at_price).change(function(){
                        compare_at_price = $(this).val();
                    }).appendTo(content);
                    const footer = $('<div/>').addClass('add-more-product-type-modal__footer').appendTo(content);
                    $('<div/>').addClass('btn btn-outline mr5').text('Back').click(function () {
                        content.empty();
                        renderScene1();
                    }).appendTo(footer);
                    $('<div/>').addClass('btn-add').text('Done').click(function(){
                        const product_type_variant_ids = dataVariant.filter(variant => variant.check).map(v => v.id);
                        $.ajax({
                            type: 'POST',
                            url: $.base_url + '/catalog/backend_productTypeVariantPrice/setLocationPrice/hash/' + OSC_HASH,
                            data: {
                                location: localStorage.getItem('locationProductType'),
                                price,compare_at_price,product_type_variant_ids

                            },
                            success: function (response) {
                                if (response.result == 'OK') {
                                    $.ajax({
                                        type: "GET",
                                        url: $.base_url + '/catalog/backend_productTypeVariantPrice/getListPrice/hash/' + OSC_HASH,
                                        data: {location_data: localStorage.getItem('locationProductType')},
                                        success: function(response){
                                            toggleAddMoreButton(response.data.data.getMoreAble);
                                            data = Object.keys(response.data.data.data).map(key => {
                                                const variants = response.data.data.data[key].variants.map(variant => {
                                                    return {
                                                        ...variant,
                                                        check: false,
                                                    }
                                                })
                                                return {
                                                    id: key,
                                                    product_type_name: response.data.data.data[key].product_type_name,
                                                    check: false,
                                                    indeterminate: false,
                                                    getMoreAble: response.data.data.data[key].getMoreAble,
                                                    variants: variants
                                                }
                                            });
                                            renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
                                        }
                                    });
                                    $.unwrapContent('addMoreVariantFrm');
                                }
                            }
                        })
                    }).appendTo(footer);
                }

                renderScene1();

                $('<div />').addClass('close-btn').click(function () {
                    $.unwrapContent('addMoreVariantFrm');
                }).appendTo(header);
                $.wrapContent(modal, {key: 'addMoreVariantFrm'});

                modal.moveToCenter().css('top', '100px');
            }
        });
    };
}

function validateInputPrice()  {
    const input = this;
    var ponto = input.value.split('.').length;
    var slash = input.value.split('-').length;
    if (ponto > 2)
        input.value=input.value.substr(0,(input.value.length)-1);

    if(slash > 2)
        input.value=input.value.substr(0,(input.value.length)-1);

    input.value=input.value.replace(/[^0-9.]/,'');
    if(!Number(input.value)) input.value = ""

    if (ponto ==2) input.value=input.value.substr(0,(input.value.indexOf('.')+3));

    if(input.value == '.') input.value = "";
}

const baseCostConfigBtn = (container, baseCostConfigs, productType, variant) => {
    container.empty();
    const div = $('<div/>').addClass('show-base-cost-config').text(`LV ${baseCostConfigs.length}`).click(function(){
        editProductTypeVariantModal({
            price: variant.price || '',
            comparePrice: variant.comparePrice || '',
            productTypes: [{
                ...productType,
                variants: [variant],
            }]
        });
    }).appendTo(container);//here
    div.mouseover(() => baseCostConfigTooltip(div, baseCostConfigs));
    div.mouseleave(() => div.children('.base-cost-config-tooltip').remove());
}

const baseCostConfigTooltip = (container, baseCostConfigs) => {
    if (!container.find('.base-cost-config-tooltip').length) {
        const top = $(window).height() - container[0].getBoundingClientRect().top + 5;
        const div = $('<div>').addClass('base-cost-config-tooltip').css({
            bottom: top + 'px',
        }).appendTo(container);
        $('<div>').append("<span class='text-center'>Qty</span>").append("<span class='text-center'>Base</span>").appendTo(div);
        baseCostConfigs.forEach(item => $('<div>').append(`<span class='text-center'>${item.quantity}</span>`).append(`<span class='text-center'>${item.base_cost}</span>`).appendTo(div))
    }
}

const addNewBaseCostConfig = (container, data) => {
    const div = $('<div/>').addClass('add-new-base-cost-config').click(() => editProductTypeVariantModal(data, true)).appendTo(container);
    $('<span>').text('New').appendTo(div);
    $($.renderIcon('icon-plus')).appendTo(div);
}

const renderProductTypeVariantPriceList = (container, dataRender) => {
    container = container.children('.contain-list');
    container.empty();
    dataRender.forEach((productType, index) => {
        const item = $('<div/>').addClass(`price-management-container__item ${productType.open && 'expand'}`).appendTo(container);
        const header = $('<div/>').addClass('price-management-container__item-header').appendTo(item).click(function() {
            item.toggleClass('expand');
            item.hasClass('expand') ? productType.open = true : productType.open = false;
        });
        const div = $('<div/>').appendTo(header);
        $($.renderIcon('icon-polygon')).appendTo(div);
        let flagChecked = true;
        let flagIndeterminate = false;
        productType.variants.forEach(variant => {
            if (variant.check){
                flagIndeterminate = true;
            }else{
                flagChecked = false;
            }

        });
        const actionBar = $('.product-type-variants__actions');
        localStorage.getItem('locationProductType') == 'default' ? actionBar.children('.product-type-variants__actions-delete').addClass('d-none') : actionBar.children('.product-type-variants__actions-delete').removeClass('d-none')
        $("<input class='mr-2 custom-checkbox' type='checkbox' />").click(function(e){
            e.stopPropagation();
        }).on('change', function(){
            productType.variants.forEach(variant => {
                variant.check = $(this).is(':checked');
            });
            item.children('.price-management-container__list').children('.product-varitant-container').children('div:first-child').children('div').children('input').prop('checked', $(this).is(':checked'));
            if ($(this).is(':checked')) {
                actionBar.addClass('show');
            } else {
                if (!hasAtLeastOneChecked()) actionBar.removeClass('show');
            }
        }).prop('indeterminate', flagChecked ? false : flagIndeterminate).prop('checked', flagChecked).appendTo(div);
        $('<strong/>').text(productType.product_type_name).appendTo(div);
        if (localStorage.getItem('locationProductType') !== 'default'){
            $($.renderIcon('trash-alt-regular')).click(function(e){
                e.stopPropagation();
                const flag = confirm('Are you sure you want to delete this product type?');
                if (flag) {
                    $.ajax({
                        type: "GET",
                        url: $.base_url + '/catalog/backend_productTypeVariantPrice/deletePriceOfLocation/hash/' + OSC_HASH,
                        data: {
                            location: localStorage.getItem('locationProductType'),
                            product_type_id: productType.id,
                        },
                        success: function(response){
                            if (response.result == 'OK') {
                                $.ajax({
                                    type: "GET",
                                    url: $.base_url + '/catalog/backend_productTypeVariantPrice/getListPrice/hash/' + OSC_HASH,
                                    data: {location_data: localStorage.getItem('locationProductType')},
                                    success: function(response){
                                        alert(`Product type ${productType.product_type_name} has been deleted successfully`);
                                        toggleAddMoreButton(response.data.data.getMoreAble);
                                        data = Object.keys(response.data.data.data).map(key => {
                                            const variants = response.data.data.data[key].variants.map(variant => {
                                                return {
                                                    ...variant,
                                                    check: false,
                                                }
                                            })
                                            return {
                                                id: key,
                                                product_type_name: response.data.data.data[key].product_type_name,
                                                check: false,
                                                indeterminate: false,
                                                getMoreAble: response.data.data.data[key].getMoreAble,
                                                variants: variants
                                            }
                                        });
                                        renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
                                        $.ajax({
                                            type: "GET",
                                            url: $.base_url + '/catalog/backend_productTypeVariantPrice/getMoreProductOfLocation/hash/' + OSC_HASH,
                                            data: {product_type_ids: Object.keys(response.data.data.data)},
                                            success: function(response){
                                                dataAddMoreProduct = Object.keys(response.data.data).map(key => {
                                                    const variants = response.data.data[key].variants.map(variant => {
                                                        return {
                                                            ...variant,
                                                            check: false,
                                                        }
                                                    })
                                                    return {
                                                        id: key,
                                                        product_type_name: response.data.data[key].product_type_name,
                                                        check: false,
                                                        indeterminate: false,
                                                        variants: variants
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        },
                        error : function(xhr, ajaxOptions, thrownError) {
                            alert(thrownError);
                        }
                    });
                }
            }).appendTo(header);
        }
        const list = $('<div/>').addClass('price-management-container__list').appendTo(item);
        productType.variants.forEach(variant => {
            const variantContainer = $("<div class='product-varitant-container d-flex' />").appendTo(list);
            $("<div class='d-flex align-items-center ml-6 my-col'/>").append($("<input type='checkbox' class='mr-2'/>").change(function(){
                let atLeastOne = false;
                let allCheck = true;
                for (let i = 0; i < productType.variants.length; i++) {
                    if (productType.variants[i].id === variant.id) {
                        productType.variants[i].check = $(this).is(':checked');
                        $(this).prop('checked', $(this).is(':checked'));
                    }
                    if (productType.variants[i].check) atLeastOne = true;
                    if (!productType.variants[i].check) allCheck = false;
                }
                if (atLeastOne) {
                    $(this).closest('.price-management-container__item').children('.price-management-container__item-header').find('input').prop('checked', false).prop("indeterminate", true);
                }
                if (allCheck) {
                    $(this).closest('.price-management-container__item').children('.price-management-container__item-header').find('input').prop('checked', true).prop("indeterminate", false);
                }
                if (!atLeastOne) {
                    $(this).closest('.price-management-container__item').children('.price-management-container__item-header').find('input').prop('checked', false).prop("indeterminate", false);
                }
                if ($(this).is(':checked')) {
                    actionBar.addClass('show');
                } else {
                    if (!hasAtLeastOneChecked()) actionBar.removeClass('show');
                }
            }).prop('checked', variant.check)).append($('<label/>').text(variant.title)).appendTo($("<div class='w-60'/>").appendTo(variantContainer));
            $("<div class='w-10 my-col d-flex price-col price'>").append($("<div class='original-price'/>").append($("<span class='mr-1'/>").text(variant.best_price))).appendTo(variantContainer);
            $("<div class='w-10 my-col d-flex price-col price'>").append($("<div class='original-price'/>").append($("<span class='mr-1'/>").text(variant.price))).appendTo(variantContainer);
            $("<div class='w-10 my-col d-flex price-col compare-price'>").append($("<div class='original-price'/>").append($("<span class='mr-1'/>").text(variant.comparePrice))).appendTo(variantContainer);
            const baseCostCol = $("<div class='w-10 my-col d-flex baseCostCol'>").appendTo(variantContainer);
            if (variant.baseCostConfigs?.length) baseCostConfigBtn(baseCostCol, variant.baseCostConfigs, productType, variant);
            else addNewBaseCostConfig(baseCostCol, {price: variant.price, comparePrice: variant.comparePrice, productTypes: [
                    {
                        ...productType,
                        variants: [variant]
                    }
                ]});
            const last_col = $("<div class='w-10 my-col last-col d-flex'/>").appendTo(variantContainer);
            if(variant.hasInfo) {
                last_col.append($("<span class='d-block' data-insert-cb='initProductTypeInfoModal' />").attr('data-variant-id', variant.id).append($.renderIcon('icon-infor')));
            }
            if (localStorage.getItem('locationProductType') !== 'default') {
                last_col.append($('<span/>').addClass('d-block ml-3').append($.renderIcon('trash-alt-regular')).click(function(){
                    const flag = confirm('Are you sure you want to delete the variant?');
                    if (flag) {
                        $.ajax({
                            type: "GET",
                            url: $.base_url + '/catalog/backend_productTypeVariantPrice/deletePriceOfLocation/hash/' + OSC_HASH,
                            data: {
                                location: localStorage.getItem('locationProductType'),
                                product_type_variant_id: variant.id,
                            },
                            success: function(response){
                                if (response.result == 'OK') {
                                    $.ajax({
                                        type: "GET",
                                        url: $.base_url + '/catalog/backend_productTypeVariantPrice/getListPrice/hash/' + OSC_HASH,
                                        data: {location_data: localStorage.getItem('locationProductType')},
                                        success: function(response){
                                            alert(`Product variant ${variant.title} has been deleted successfully`);
                                            toggleAddMoreButton(response.data.data.getMoreAble);
                                            data = Object.keys(response.data.data.data).map(key => {
                                                const variants = response.data.data.data[key].variants.map(variant => {
                                                    return {
                                                        ...variant,
                                                        check: false,
                                                    }
                                                })
                                                return {
                                                    id: key,
                                                    product_type_name: response.data.data.data[key].product_type_name,
                                                    check: false,
                                                    indeterminate: false,
                                                    getMoreAble: response.data.data.data[key].getMoreAble,
                                                    variants: variants
                                                }
                                            });
                                            renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
                                        }
                                    });
                                }
                            }
                        });
                    }
                }));
            }
        })
        if (productType.getMoreAble) {
            $("<div class='add-more-variant'/>").append($("<button/>").click(addMoreVariantModal(data)).attr('data-product-type-id', productType.id).append($.renderIcon('icon-union')).append('Add More Variant')).appendTo(list);
        }
    })
}

window.initProductTypeVariantPriceList = function () {
    const container = $(this);
    $.ajax({
        type: "GET",
        url: $.base_url + '/catalog/backend_productTypeVariantPrice/getListPrice/hash/' + OSC_HASH,
        data: {location_data: localStorage.getItem('locationProductType') ? localStorage.getItem('locationProductType') : 'default'},
        success: function(response){
            if (!localStorage.getItem('locationProductType')) {
                localStorage.setItem('locationProductType', 'default')
            }
            toggleAddMoreButton(response.data.data.getMoreAble);
            data = Object.keys(response.data.data.data).map(key => {
                const variants = response.data.data.data[key].variants.map(variant => {
                    return {
                        ...variant,
                        check: false,
                    }
                })
                return {
                    id: key,
                    product_type_name: response.data.data.data[key].product_type_name,
                    check: false,
                    open: false,
                    indeterminate: false,
                    getMoreAble: response.data.data.data[key].getMoreAble,
                    variants: variants
                }
            });
            renderProductTypeVariantPriceList(container, data);
            $.ajax({
                type: "GET",
                url: $.base_url + '/catalog/backend_productTypeVariantPrice/getMoreProductOfLocation/hash/' + OSC_HASH,
                data: {product_type_ids: Object.keys(response.data.data.data)},
                success: function(response){
                    dataAddMoreProduct = Object.keys(response.data.data).map(key => {
                        const variants = response.data.data[key].variants.map(variant => {
                            return {
                                ...variant,
                                check: false,
                            }
                        })
                        return {
                            id: key,
                            product_type_name: response.data.data[key].product_type_name,
                            check: false,
                            indeterminate: false,
                            variants: variants
                        }
                    });
                }
            });
        }
    });
}

const renderListLocationDropdown = (container, locations) => {
    locations.forEach(location => $("<div class='location-select__dropdown-item' />").append(location.name).append($('<span/>').append($.renderIcon('trash-alt-regular')).click(function(e){
        e.stopPropagation();
        const flag = confirm("Are you sure you want to delete the location?");
        if (flag) {
            $.ajax({
                type: "GET",
                url: $.base_url + '/catalog/backend_productTypeVariantPrice/deleteAllPriceOfLocation/hash/' + OSC_HASH,
                data: {location: location.id},
                success: function(response){
                    if (response.result == 'OK') {
                        alert(`Location ${location.name} has been deleted successfully`);
                        getDataLocation($('.location-select'));
                        $('.location-select').removeClass('expand');
                        if (localStorage.getItem('locationProductType') !== 'default') {
                            localStorage.setItem('locationProductType', 'default');
                            $.ajax({
                                type: "GET",
                                url: $.base_url + '/catalog/backend_productTypeVariantPrice/getListPrice/hash/' + OSC_HASH,
                                data: {location_data: 'default'},
                                success: function(response){
                                    toggleAddMoreButton(response.data.data.getMoreAble);
                                    data = Object.keys(response.data.data.data).map(key => {
                                        const variants = response.data.data.data[key].variants.map(variant => {
                                            return {
                                                ...variant,
                                                check: false,
                                            }
                                        })
                                        return {
                                            id: key,
                                            product_type_name: response.data.data.data[key].product_type_name,
                                            check: false,
                                            indeterminate: false,
                                            getMoreAble: response.data.data.data[key].getMoreAble,
                                            variants: variants
                                        }
                                    });
                                    renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
                                    $.ajax({
                                        type: "GET",
                                        url: $.base_url + '/catalog/backend_productTypeVariantPrice/getMoreProductOfLocation/hash/' + OSC_HASH,
                                        data: {product_type_ids: Object.keys(response.data.data.data)},
                                        success: function(response){
                                            dataAddMoreProduct = Object.keys(response.data.data).map(key => {
                                                const variants = response.data.data[key].variants.map(variant => {
                                                    return {
                                                        ...variant,
                                                        check: false,
                                                    }
                                                })
                                                return {
                                                    id: key,
                                                    product_type_name: response.data.data[key].product_type_name,
                                                    check: false,
                                                    indeterminate: false,
                                                    variants: variants
                                                }
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    }
                }
            });
        }
    })).click(function(){
        $('.product-search__input').children('input').val('');
        container.parent().parent().removeClass('expand');
        $('.product-type-variants__actions').removeClass('show');
        $.ajax({
            type: "GET",
            url: $.base_url + '/catalog/backend_productTypeVariantPrice/getListPrice/hash/' + OSC_HASH,
            data: {location_data: location.id},
            success: function(response){
                localStorage.setItem('locationProductType', location.id);
                toggleAddMoreButton(response.data.data.getMoreAble);
                data = Object.keys(response.data.data.data).map(key => {
                    const variants = response.data.data.data[key].variants.map(variant => {
                        return {
                            ...variant,
                            check: false,
                        }
                    })
                    return {
                        id: key,
                        product_type_name: response.data.data.data[key].product_type_name,
                        check: false,
                        indeterminate: false,
                        getMoreAble: response.data.data.data[key].getMoreAble,
                        variants: variants
                    }
                });
                container.parent().parent().children('span:first-child').text(location.name);
                renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
                $.ajax({
                    type: "GET",
                    url: $.base_url + '/catalog/backend_productTypeVariantPrice/getMoreProductOfLocation/hash/' + OSC_HASH,
                    data: {product_type_ids: Object.keys(response.data.data.data)},
                    success: function(response){
                        dataAddMoreProduct = Object.keys(response.data.data).map(key => {
                            const variants = response.data.data[key].variants.map(variant => {
                                return {
                                    ...variant,
                                    check: false,
                                }
                            })
                            return {
                                id: key,
                                product_type_name: response.data.data[key].product_type_name,
                                check: false,
                                indeterminate: false,
                                variants: variants
                            }
                        });
                    }
                });
            }
        });
    }).appendTo(container));
}

const getDataLocation = (container) => {
    container.empty();
    $.ajax({
        type: "GET",
        url: $.base_url + '/catalog/backend_productTypeVariantPrice/getListLocationData/hash/' + OSC_HASH,
        success: function(response){
            let currentLocation;
            const dataLocationSelect = Object.keys(response.data.data).map(key => {
                if (key == localStorage.getItem('locationProductType')){
                    currentLocation = response.data.data[key];
                }
                return {
                    id: key,
                    name: response.data.data[key],
                }
            })
            $('<span/>').text(currentLocation ? currentLocation : 'Default Price').appendTo(container);
            $("<span class='svg' />").append($.renderIcon('angle-down-solid')).appendTo(container);
            const dropdown = $("<div class='location-select__dropdown' />").on('click', function(e) {
                e.stopPropagation();
            }).appendTo(container);
            $('<div/>').css('padding', '5px 11px').append($("<input type='text' class='px-2'/>").on('input', debounce(function() {
                const searchText = $(this).val().trim();
                const result = searchByName(dataLocationSelect, searchText);
                dropdown.children('.location-select__dropdown-list').empty();
                dropdown.children('.loader').removeClass('show');
                renderListLocationDropdown(dropdown.children('.location-select__dropdown-list'), result);
            }, 500)).on('input', function(){
                dropdown.children('.location-select__dropdown-list').empty();
                dropdown.children('.loader').addClass('show');
            }).css({
                'width': '100%',
                'border': '1px solid #E0E0E0',
                'border-radius': '3px',
                'height': '30px',
            })).appendTo(dropdown);
            const list = $("<div class='location-select__dropdown-list'/>").appendTo(dropdown);
            renderListLocationDropdown(list, dataLocationSelect);
            $('<div class="loader">Loading...</div>').appendTo(dropdown);
            $("<div class='add-new-location' />").append($("<button data-insert-cb='initAddNewLocationModal' />").append($.renderIcon('icon-union')).append('Add New Location')).appendTo(dropdown);
        }
    });
}
window.initCustomSelect = function () {
    const container = this;
    getDataLocation($(container));
}

window.debounce = function(callback, delay) {
    var timeout
    return function() {
        var args = arguments
        clearTimeout(timeout)
        timeout = setTimeout(function() {
            callback.apply(this, args)
        }.bind(this), delay)
    }
}

function toggleAddMoreButton(data){
    if (data){
        $('.add-more-product').removeClass('d-none');
    } else {
        $('.add-more-product').addClass('d-none');
    }
}

$(document).click(function(e) {
    if ($(e.target).closest("#custom-search").length == 0) {
        $('#custom-search').removeClass('show');
    }
    if ($(e.target).closest(".location-select").length == 0) {
        $('.location-select').removeClass('expand');
    }
});

$('.collapse-expand-btn div:last-child').click(function() {
    $('.product-type-variants-container').children('.contain-list').children('.price-management-container__item').addClass('expand');
})
$('.collapse-expand-btn div:first-child').click(function() {
    $('.product-type-variants-container').children('.contain-list').children('.price-management-container__item').removeClass('expand');
})

$('.product-search__input').children('input').on('input', debounce(function() {
    $('.no-result').remove();
    const container = $('.product-type-variants-container');
    const searchTxt = $(this).val().trim();
    container.children('.contain-list').empty();
    const result = searchByProductTypeAndVariant(data, searchTxt);
    console.log(result)
    $('.custom-product-type-variant').children('.loader').removeClass('show');
    renderProductTypeVariantPriceList(container, result);
    if (!result.length) {
        $('<div/>').addClass('price-management-container__item py-4 no-result').css('text-align', 'center').append($('<strong/>').text('Sorry, we couldn\'t find any results for "' + searchTxt + '"')).appendTo(container);
    }
}, 500)).on('input', function(){
    $('.custom-product-type-variant').children('.loader').addClass('show');
    $('.product-type-variants-container').children('.contain-list').empty();
}).keyup(function(e){
    if(e.keyCode == 13) {
        $('.no-result').remove();
        const container = $('.product-type-variants-container');
        const searchTxt = $(this).val().trim();
        container.children('.contain-list').empty();
        const result = searchByProductTypeAndVariant(data, searchTxt);
        $('.custom-product-type-variant').children('.loader').removeClass('show');
        renderProductTypeVariantPriceList(container, result);
        if (!result.length) {
            $('<div/>').addClass('price-management-container__item py-4 no-result').css('text-align', 'center').append($('<strong/>').text('Sorry, we couldn\'t find any results for "' + searchTxt + '"')).appendTo(container);
        }
    }

});
$('.location-select').click(function(){
    $(this).toggleClass('expand');
});

function hasAtLeastOneChecked(){
    let flag = false;
    for (let i = 0; i < data.length; i++) {
        if (!flag) {
            const productType = data[i];
            for (let j = 0; j < productType.variants.length; j++) {
                const variant = productType.variants[j];
                if (variant.check) {
                    flag = true;
                    break;
                }
            }
        } else break;
    }
    return flag;
}

window.initCustomSizeBoard = function () {
    const $size_guide = $('.size_guide');
    const $container = $('.size_guide_container');
    const $input = $('.size_guide_input');
    const $toggle = $('#size_guide_allow');
    const $inputRequire = $('.input-require');
    const inputValue = $input.val();

    if (!$container.length) {
        console.log("Container element .size_table_container not found!");
        return false;
    }

    $toggle.on('change', function() {
        const checked = $(this).val();

        if (checked == 1) {
            $size_guide.addClass('show');
            $inputRequire.attr('required', true);
            renderTable();
        } else {
            $size_guide.removeClass('show');
            $inputRequire.attr('required', false);
            $container.html('');
        }
    });

    let data = inputValue ? JSON.parse(inputValue) : [
        ['Size', 'Shirt Length', 'Chest Width'],
        ['', '', ''],
        ['', '', ''],
        ['', '', '']
    ];

    $container
        .on('click', '.add_row_above', function() {
            const rowIndex = $(this).data('index');
            const newRow = [...data[rowIndex]].map(item => '');
            const newData = [
                ...data.slice(0, rowIndex),
                newRow,
                ...data.slice(rowIndex)
            ]
            data = newData;
            renderTable();
        })
        .on('click', '.add_row_below', function() {
            const rowIndex = $(this).data('index');
            const newRow = [...data[rowIndex]].map(item => '');
            const newData = [
                ...data.slice(0, rowIndex + 1),
                newRow,
                ...data.slice(rowIndex + 1)
            ]
            data = newData;
            renderTable();
        })
        .on('click', '.move_row_up', function() {
            const rowIndex = $(this).data('index');
            const newData = [
                ...data.slice(0, rowIndex - 1),
                data[rowIndex],
                data[rowIndex - 1],
                ...data.slice(rowIndex + 1)
            ];
            data = newData;
            renderTable();
        })
        .on('click', '.move_row_down', function() {
            const rowIndex = $(this).data('index');
            const newData = [
                ...data.slice(0, rowIndex),
                data[rowIndex + 1],
                data[rowIndex],
                ...data.slice(rowIndex + 2)
            ];
            data = newData;
            renderTable();
        })
        .on('click', '.delete_row_btn', function() {
            if (data.length < 2) {
                alert('Cannot delete all row!');
                return false;
            }
            const rowIndex = $(this).data('index');
            data = [
                ...data.slice(0, rowIndex),
                ...data.slice(rowIndex + 1)
            ]
            renderTable();
        })
        .on('click', '.add_column_left', function() {
            const columnIndex = $(this).data('index');
            data = data.map((row) => [
                ...row.slice(0, columnIndex),
                '',
                ...row.slice(columnIndex)
            ]);
            renderTable();
        })
        .on('click', '.add_column_right', function() {
            const columnIndex = $(this).data('index');
            data = data.map((row) => [
                ...row.slice(0, columnIndex + 1),
                '',
                ...row.slice(columnIndex + 1)
            ]);
            renderTable();
        })
        .on('click', '.move_column_left', function() {
            const columnIndex = $(this).data('index');
            data = data.map((row) => [
                ...row.slice(0, columnIndex - 1),
                row[columnIndex],
                row[columnIndex - 1],
                ...row.slice(columnIndex + 1)
            ]);
            renderTable();
        })
        .on('click', '.move_column_right', function() {
            const columnIndex = $(this).data('index');
            data = data.map((row) => [
                ...row.slice(0, columnIndex),
                row[columnIndex + 1],
                row[columnIndex],
                ...row.slice(columnIndex + 2)
            ]);
            renderTable();
        })
        .on('click', '.delete_column_btn', function() {
            if (data[0].length === 1) {
                alert('Cannot delete all column!');
                return false;
            }
            const columnIndex = $(this).data('index');
            data = data.map((row, rowIndex) => [
                ...row.slice(0, columnIndex),
                ...row.slice(columnIndex + 1)
            ]);
            renderTable();
        })
        .on('change keyup', 'input', function() {
            const $el = $(this);
            const value = $el.val();
            const row = $el.data('row');
            const column = $el.data('column');
            data[row][column] = value;
            $input.val(JSON.stringify(data));
        });

    renderTable();

    function renderTable() {
        let tableHtml = `<table class="size_table">
    <thead>
        <tr>
            ${data[0].map((label, index) => `<th>
                <input type="text" value="${label}" data-row="0" data-column="${index}" required />
                <div class="dropdown">
                    <strong class="dropdown-toggle">⋮</strong>
                    <div class="dropdown-menu">
                        <button type="button" class="dropdown-item delete_column_btn" data-index="${index}">Delete Column</button>
                        <button type="button" class="dropdown-item add_column_left" data-index="${index}">Add Left</button>
                        <button type="button" class="dropdown-item add_column_right" data-index="${index}">Add Right</button>
                        ${data[0].length === index + 1
                            ? `<button class="dropdown-item add_row_below" data-index="0">Add Below</button>` 
                            : ''}
                        ${data[0].length !== index + 1
                            ? `<button type="button" class="dropdown-item move_column_right" data-index="${index}">Move Right</button>`
                            : ''}
                        ${index !== 0
                            ? `<button type="button" class="dropdown-item move_column_left" data-index="${index}">Move Left</button>`
                            : ''}
                    </div>
                </div>
            </th>`).join('')}
        </tr>
    </thead>
    <tbody>
    ${data.slice(1).map((row, rowIndex) => `
        <tr>
            ${row.map((column, columnIndex) => `<td>
                <input type="text" value="${column}" data-row="${rowIndex + 1}" data-column="${columnIndex}" required />
                ${ columnIndex === row.length - 1 ? `
                    <div class="dropdown">
                        <strong class="dropdown-toggle">⋮</strong>
                        <div class="dropdown-menu">
                            <button type="button" class="dropdown-item delete_row_btn" data-index="${rowIndex + 1}">Delete Row</button>
                            <button type="button" class="dropdown-item add_row_above" data-index="${rowIndex + 1}">Add Above</button>
                            <button type="button" class="dropdown-item add_row_below" data-index="${rowIndex + 1}">Add Below</button>
                            ${rowIndex > 0
                                ? `<button type="button" class="dropdown-item move_row_up" data-index="${rowIndex + 1}">Move Up</button>`
                                : ''}
                            ${rowIndex < (data.length - 2)
                                ? `<button type="button" class="dropdown-item move_row_down" data-index="${rowIndex + 1}">Move Down</button>`
                                : ''}
                        </div>
                    </div>
                ` : '' }
            </td>
            `).join('')}
        </tr>
    `).join('')}
    </tbody>
</table>`;

        $container.html(tableHtml);
        $input.val(JSON.stringify(data));
    }

    window.triggerChangeImageRequire = (value) => {
        $inputRequire.val(value);
    }
};
