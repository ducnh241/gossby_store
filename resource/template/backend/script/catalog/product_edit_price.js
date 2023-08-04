let data, product_id;

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

const editProductTypeVariantModal = (dataRender, isAddNewPlusPrice = false) => {
    $.unwrapContent('editProductTypeVariantFrm');
    if (!dataRender.productTypes.length) return $.unwrapContent('editProductTypeVariantFrm');
    let product_variant_ids = [];
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
                product_variant_ids.push(variant.product_variant_id);
                const div = $('<div>').appendTo(list);
                const childDiv = $('<div>').appendTo(div);
                $('<span>').addClass('mr-2').html(`&#8226`).appendTo(childDiv);
                $('<span>').text(variant.title).appendTo(childDiv);
                if(!isAddNewPlusPrice) {
                    $($.renderIcon('times')).click(function(){
                        productType.variants = productType.variants.filter(v => v.id !== variant.id);
                        product_variant_ids = product_variant_ids.filter(v => v.id !== variant.product_variant_id);
                        div.remove();
                        if (!productType.variants.length) {
                            dataRender.productTypes = dataRender.productTypes.filter(p => p.id !== productType.id);
                            item.remove();
                            if (!dataRender.productTypes.length) return $.unwrapContent('editProductTypeVariantFrm');
                        } else {
                            list.css('height', `${productType.variants.length * 32}px`);
                        }
                        // editProductTypeVariantModal(dataRender)
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
    $('<strong/>').addClass('d-block').text('Plus price').appendTo(rightSide);
    const plusPriceInput = $('<input/>').val(dataRender.plusPrice).on('input', validateInputPrice).appendTo(rightSide);

    const btnGroup = $('<div/>').addClass('mt-3 d-flex edit-product-type-variant-modal__action-buttons').appendTo(rightSide);
    $('<button>').addClass('btn btn-outline').text('Cancel').click(() => $.unwrapContent('editProductTypeVariantFrm')).appendTo(btnGroup);
    $('<button>').addClass('btn btn-primary').click(function(){
        const button = $(this);
        button.text('Saving...')
        console.log(product_variant_ids, product_variant_ids.length)
        $.ajax({
            type: 'POST',
            url: $.base_url + `/catalog/backend_product/postFixedCampaignPrice/hash/` + OSC_HASH,
            data: {
                product_variant_ids,
                price: priceInput.val(),
                compare_at_price: comparePriceInput.val(),
                plus_price: plusPriceInput.val(),
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
                                        variant.plusPrice = plusPriceInput.val();
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
                                    variant.plusPrice = plusPriceInput.val();
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
            const data = result.length == 1 && result[0].variants.length == 1 ? {
                price: result[0].variants[0].price,
                comparePrice:result[0].variants[0].comparePrice,
                plusPrice: result[0].variants[0].plusPrice,
                productTypes: result
            } : {
                price: '',
                comparePrice: '',
                plusPrice: '',
                productTypes: result
            }
            editProductTypeVariantModal(data);
        } else $(this).parent().removeClass('show')
    })
}

window.initDeleteAllSelectedVariants = function () {
    const product_id = this.getAttribute('data-product-id');
    $(this).click(function(){
        const flag = confirm('Are you sure you want to reset this product type variants?');
        if (flag) {
            $(this).addClass('delete');
            const that = $(this);
            let product_variant_ids = [];
            data = data.map(p => {
                const newVariants = p.variants.filter(v => !v.check);
                const selectVariants = p.variants.filter(v => v.check).map(v => v.product_variant_id);
                product_variant_ids = [...product_variant_ids, ...selectVariants];
                if (newVariants.length) return {
                    ...p,
                    variants: newVariants,
                }
                return null;
            }).filter(p => p);
            $.ajax({
                type: "POST",
                url: $.base_url + '/catalog/backend_product/deleteFixedCampaignPrice/hash/' + OSC_HASH,
                data: {product_variant_ids},
                success: function(response){
                    if(response.result == 'OK') {
                        alert('Reset product type variant successfully!')
                        $.ajax({
                            type: "GET",
                            url: $.base_url + '/catalog/backend_product/listFixedCampaignPrice/hash/' + OSC_HASH,
                            data: { product_id },
                            success: function(response){
                                data = Object.keys(response.data.data).map(key => {
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
                                renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
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

const plusPriceConfigBtn = (container, plusPrice, productType, variant) => {
    container.empty();
    $('<div/>').addClass('show-base-cost-config').text(plusPrice).click(function(){
        editProductTypeVariantModal({
            price: variant.price || '',
            comparePrice: variant.comparePrice || '',
            plusPrice: variant.plusPrice || '',
            productTypes: [{
                ...productType,
                variants: [variant],
            }]
        });
    }).appendTo(container);//here
}

const addNewPlusPriceConfig = (container, data) => {
    const div = $('<div/>').addClass('add-new-base-cost-config').click(() => editProductTypeVariantModal(data, true)).appendTo(container);
    $('<span>').text('Add').appendTo(div);
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

        //DELETE PRODUCT TYPE
        $($.renderIcon('trash-alt-regular')).click(function(e){
            e.stopPropagation();
            const flag = confirm('Are you sure you want to reset the price this product type to default?');
            if (flag) {
                $.ajax({
                    type: "POST",
                    url: $.base_url + '/catalog/backend_product/deleteFixedCampaignPrice/hash/' + OSC_HASH,
                    data: {
                        product_variant_ids: productType.variants.map(v => v.product_variant_id),
                    },
                    success: function(response){
                        if (response.result == 'OK') {
                            $.ajax({
                                type: "GET",
                                url: $.base_url + '/catalog/backend_product/listFixedCampaignPrice/hash/' + OSC_HASH,
                                data: { product_id },
                                success: function(response){
                                    alert(`Product type ${productType.product_type_name} has been reset successfully`);
                                    data = Object.keys(response.data.data).map(key => {
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
                                    renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
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
        //END DELETE PRODUCT TYPE
        
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
            $("<div class='w-10 my-col d-flex price-col price'>").append($("<div class='original-price'/>").append($("<span class='mr-1'/>").text(variant.price))).appendTo(variantContainer);
            $("<div class='w-10 my-col d-flex price-col compare-price'>").append($("<div class='original-price'/>").append($("<span class='mr-1'/>").text(variant.comparePrice))).appendTo(variantContainer);
            const plusPriceCol = $("<div class='w-10 my-col d-flex baseCostCol'>").appendTo(variantContainer);
            if (variant.plusPrice > 0) plusPriceConfigBtn(plusPriceCol, variant.plusPrice, productType, variant);
            else addNewPlusPriceConfig(plusPriceCol, {
                price: variant.price, 
                comparePrice: variant.comparePrice, 
                plusPrice: variant.plusPrice,
                productTypes: [
                    {
                        ...productType,
                        variants: [variant]
                    }
                ]});
            const last_col = $("<div class='w-10 my-col last-col d-flex'/>").appendTo(variantContainer);
        
            last_col.append($('<span/>').addClass('d-block ml-3').append($.renderIcon('trash-alt-regular')).click(function(){
                const flag = confirm('Are you sure you want to reset the price of this variant to default?');
                if (flag) {
                    $.ajax({
                        type: "POST",
                        url: $.base_url + '/catalog/backend_product/deleteFixedCampaignPrice/hash/' + OSC_HASH,
                        data: {
                            product_variant_ids: [variant.product_variant_id]
                        },
                        success: function(response){
                            if (response.result == 'OK') {
                                $.ajax({
                                    type: "GET",
                                    url: $.base_url + '/catalog/backend_product/listFixedCampaignPrice/hash/' + OSC_HASH,
                                    data: { product_id},
                                    success: function(response){
                                        alert(`Product variant ${variant.title} has been reset successfully`);
                                        data = Object.keys(response.data.data).map(key => {
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
                                        renderProductTypeVariantPriceList($('.product-type-variants-container'), data);
                                    }
                                });
                            }
                        }
                    });
                }
            }));
        })
    })
}

window.initProductTypeVariantPriceList = function () {
    const container = $(this);
    product_id = this.getAttribute('data-product-id');
    $.ajax({
        type: "GET",
        url: $.base_url + '/catalog/backend_product/listFixedCampaignPrice/hash/' + OSC_HASH,
        data: { product_id },
        success: function(response){
            data = Object.keys(response.data.data).map(key => {
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
                    open: false,
                    indeterminate: false,
                    variants: variants
                }
            });
            renderProductTypeVariantPriceList(container, data);
        }
    });
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