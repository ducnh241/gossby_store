(function ($) {
    const _searchByProductTypeAndVariant = (data, searchTxt) => {
        const result = [];
        data.forEach(productType => {
            if (productType.product_type_name.toLowerCase().includes(searchTxt.trim().toLowerCase())) {
                result.push(productType);
            } else {
                const variants = [];
                productType.variants.forEach(variant => {
                    if (variant.title.toLowerCase().includes(searchTxt.trim().toLowerCase())){
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

    let allProductTypeVariants = [];
    let realSelectedProductTypeVariants = [];
    let selectedProductTypeVariants = [];
    let defaultSelectedVariants = null;

    window.initSelectProductTypeVariantsComponent = function () {
        const that = $(this);
        $.ajax({
            type: 'GET',
            url: $.base_url + '/catalog/backend_productTypeVariant/getList/hash/' + OSC_HASH,
            success: function (response) {
                allProductTypeVariants = Object.keys(response.data.data).map(key => {
                    let selectedVariants = [];
                    let variants = response.data.data[key].variants.map(v => {
                        const flag = defaultSelectedVariants[response.data.data[key].product_type_id]?.find(v1 => v1 == v.id);
                        if (flag) {
                            selectedVariants.push({
                                ...v,
                                justAdd: false,
                                check: true,
                            });
                        }
                        return {
                            ...v,
                            justAdd: false,
                            check: flag ? true : false,
                        }
                    })
                    if (selectedVariants.length) {
                        realSelectedProductTypeVariants.push({
                            ...response.data.data[key],
                            id: response.data.data[key].product_type_id,
                            open: false,
                            variants: selectedVariants,
                        })
                    } else if (defaultSelectedVariants[response.data.data[key].product_type_id] && defaultSelectedVariants[response.data.data[key].product_type_id].length == 0) {
                        realSelectedProductTypeVariants.push({
                            ...response.data.data[key],
                            id: response.data.data[key].product_type_id,
                            open: false,
                        })
                    }
                    return {
                        ...response.data.data[key],
                        id: response.data.data[key].product_type_id,
                        open: false,
                        justAdd: false,
                        variants,
                    }
                })
                selectedProductTypeVariants = JSON.parse(JSON.stringify(realSelectedProductTypeVariants));
                renderProductTypeVariantsItem(that);
            }
        })
        defaultSelectedVariants = fetchJSONTag($(this), 'data').variant_data.reduce((acc, curr) => {
            return {
                ...acc,
                [curr.product_type_id]: curr.variants,
            }
        }, {})
        console.log(defaultSelectedVariants)
        
        this.dataCallback = function () {
            return realSelectedProductTypeVariants.map(p => {
                const productType = allProductTypeVariants.find(p1 => p1.id == p.id)
                if (productType.variants.length == p.variants.length) return {
                    ...p,
                    selectAll: true,
                }
                return {
                    ...p,
                    selectAll: false,
                }
            });
        }
    }

    function renderProductTypeVariantsItem(container){
        container.empty();
        const headerTitle = $('<div>').addClass('d-flex justify-content-between').appendTo(container);
        $('<strong>').html(
            realSelectedProductTypeVariants.length ? 
            `Product Type Variants <span class='text-blue'>(${realSelectedProductTypeVariants.reduce((acc, curr) => acc + curr.variants.length, 0)})</span>` :
            `Product Type Variants`
        ).appendTo(headerTitle)
        const headerTitleRight = $('<div>').addClass('d-flex items-center pointer').appendTo(headerTitle);
        const expandBtn = $('<span>').addClass('font-semibold hover-underline').text('Expand ');
        const collapseBtn = $('<span>').addClass('font-semibold hover-underline').text(' Collapse ');
        if (realSelectedProductTypeVariants.length) {
            $('<div>').addClass('mr-2').append(expandBtn)
            .append($('<span>').addClass('font-semibold').text('/'))
            .append(collapseBtn).appendTo(headerTitleRight);
        }
        $('<button>').attr('type', 'button').addClass('btn btn-primary').text(realSelectedProductTypeVariants.length ? 'Modify Variants' : 'Select Variants').click(function(){
            selectVariantsModal();
        }).appendTo(headerTitleRight)
        const list = $('<div>').addClass('select-product-type-variants-component__list').appendTo(container);
        realSelectedProductTypeVariants.sort(function(a, b) {
            var textA = a.product_type_name.toUpperCase();
            var textB = b.product_type_name.toUpperCase();
            return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
        }).forEach(productType => {
            let {product_type_name} = productType;
            const div = $('<div>').addClass(`product-type-variants-item ${productType.open && 'show'}`).appendTo(list);
            const header = $("<div class='d-flex product-type-variants-item__header'/>").appendTo(div);
            $('<div>').addClass('remove-item').click(function (){
                realSelectedProductTypeVariants = realSelectedProductTypeVariants.filter(p => p.id !== productType.id);
                allProductTypeVariants = allProductTypeVariants.map(p => {
                    if(p.id !== productType.id) return p;
                    return {
                        ...p,
                        variants: p.variants.map(v => ({
                            ...v,
                            check: false,
                        })),
                    }
                })
                selectedProductTypeVariants = selectedProductTypeVariants.filter(p => p.id !== productType.id);
                renderProductTypeVariantsItem(container);
            }).appendTo(header)
            $('<strong>').append($('<span>').addClass('mr-1').text(product_type_name)).append($("<span class='text-blue mr-2'/>").text(`(${productType.variants.length})`)).append($.renderIcon('angle-down-solid-thin')).appendTo(header);
            $("<div class='d-flex' />").append($("<span class='underline text-blue mr-3'/>").text('Add').click(function(e){
                e.stopPropagation();
                selectVariantsModal(product_type_name);
            })).appendTo(header);
            const listVariant = $('<div>').addClass(`product-type-variants-item__list`).appendTo(div);
            const wrapper = $('<div>').addClass('p-8').appendTo(listVariant);
            productType.variants.forEach(variant => $('<div>').addClass('product-type-variants-item__list-item d-flex')
                .append($('<span>').text(variant.title)).append($($.renderIcon('times')).click(function(e){
                    productType.variants = productType.variants.filter(v => v.id !== variant.id);
                    console.log(realSelectedProductTypeVariants)
                    if (productType.variants.length) {
                        selectedProductTypeVariants = selectedProductTypeVariants.map(p => {
                            if (p.id !== productType.id) return p;
                            return {
                                ...p,
                                variants: p.variants.filter(v => v.id !== variant.id)
                            }
                        })
                    } else {
                        realSelectedProductTypeVariants = realSelectedProductTypeVariants.filter(p => p.id !== productType.id)
                        selectedProductTypeVariants = selectedProductTypeVariants.filter(p => p.id !== productType.id);
                    }
                    allProductTypeVariants = allProductTypeVariants.map(p => {
                        if (p.id !== productType.id) return p;
                        return {
                            ...p,
                            variants: p.variants.map(v => {
                                if (v.id !== variant.id) return v;
                                return {
                                    ...v,
                                    check: false,
                                }
                            })
                        }
                    })
                    renderProductTypeVariantsItem($('.select-product-type-variants-component'))
                }))
            .appendTo(wrapper))
            productType.open ? listVariant.css('height', `${productType.variants.length * 32 + 16}px`) : listVariant.css('height', '0px');
            header.click(function(){
                div.toggleClass('show');
                if (div.hasClass('show')) {
                    productType.open = true;
                    listVariant.css('height', `${productType.variants.length * 32 + 16}px`)
                } else {
                    productType.open = false;
                    listVariant.css('height', '0px');
                }
            });
        });
        expandBtn.click(function(){
            realSelectedProductTypeVariants.forEach(p => p.open = true);
            list.children('.product-type-variants-item').each(function(){
                $(this).addClass('show');
                $(this).children('.product-type-variants-item__list').css('height', `${$(this).children('.product-type-variants-item__list').children('.p-8').children('.product-type-variants-item__list-item').length * 32 + 16}px`)
            })
        })
        collapseBtn.click(function(){
            realSelectedProductTypeVariants.forEach(p => p.open = false);
            list.children('.product-type-variants-item').each(function(){
                $(this).removeClass('show');
                $(this).children('.product-type-variants-item__list').css('height', `0px`)
            })
        })
    }
    
    function selectVariantsModal(searchText = ''){
        allProductTypeVariants = allProductTypeVariants.map(p => {
            const flagProductType = realSelectedProductTypeVariants.find(p1 => p1.id == p.id);
            if (flagProductType){
                let variants =  p.variants.map(v => {
                    if (flagProductType.variants.find(v1 => v1.id == v.id)) {
                        return {
                            ...v,
                            check: true,
                        }
                    }
                    return {
                        ...v,
                        check: false,
                    }
                });
                return {
                    ...p,
                    open: false,
                    variants,
                }
            } else {
                return {
                    ...p,
                    open: false,
                    variants: p.variants.map(v => ({
                        ...v,
                        check: false,
                    }))
                }
            }
        })
        selectedProductTypeVariants = JSON.parse(JSON.stringify(realSelectedProductTypeVariants));
        $.unwrapContent('selectVariantsModalFrm');
        var modal = $('<div />').addClass('osc-modal select-variants-modal').width(1200);
        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').css('font-size', '22px').html(realSelectedProductTypeVariants.length ? 'Modify Variants' : 'Select Variants').appendTo(header);

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('selectVariantsModalFrm');
        }).appendTo(header);
        const searchDiv = $('<div>').addClass('select-variants-modal__search').appendTo(modal);
        const inputSearch = $('<input>').attr('placeholder', 'Enter keyword to search').val(searchText).appendTo(searchDiv);;

        const contenDiv = $('<div>').addClass('d-flex select-variants-modal__content').appendTo(modal);
        const allVariants = $('<div>').addClass('select-variants-modal__content-left').appendTo(contenDiv);
        const expandBtn = $("<span class='pointer hover-underline'/>").css('font-size', '14px').text('Expand ');
        const collapseBtn = $("<span class='pointer hover-underline'/>").css('font-size', '14px').text(' Collapse');
        $('<div>')
        .addClass('d-flex justify-content-between select-variants-modal__content-left-header')
        .append($('<strong>').css('font-size','18px').text('Product Type Variants:'))
        .append($('<div>').append(expandBtn)
        .append($("<span class='mx-2' />").text('/'))
        .append(collapseBtn))
        .appendTo(allVariants);
        const leftList = $('<div>').addClass("select-variants-modal__content-left-list").appendTo(allVariants);
        const selectedVariants = $('<div>').addClass('select-variants-modal__content-left').appendTo(contenDiv);
        
        const rightList = $('<div>').addClass("select-variants-modal__content-left-list").appendTo(selectedVariants);
        const result = _searchByProductTypeAndVariant(allProductTypeVariants, searchText);
        const resultSelectedVariant = _searchByProductTypeAndVariant(selectedProductTypeVariants, searchText);
        renderSelectedProductTypeVariants(resultSelectedVariant, rightList, leftList, searchText);
        renderProductTypeVariantsItemModal(result, leftList, rightList, searchText)
        inputSearch.on('input',  debounce(function(e) {
            searchText = e.target.value;
            const result = _searchByProductTypeAndVariant(allProductTypeVariants, searchText);
            const resultSelectedVariant = _searchByProductTypeAndVariant(selectedProductTypeVariants, searchText);
            renderProductTypeVariantsItemModal(result, leftList, rightList, searchText);
            renderSelectedProductTypeVariants(resultSelectedVariant, rightList, leftList, searchText)
        }, 500))
        const footer = $('<div>').addClass('select-variants-modal__footer').appendTo(modal);
        $('<button>').addClass('btn btn-primary').text('Apply').click(function() {
            let updateSelectProductTypeVariants = [];
            selectedProductTypeVariants.forEach(p => {
                const flag = checkExist(realSelectedProductTypeVariants, p.id);
                if(flag) updateSelectProductTypeVariants.push({
                    ...p,
                    open: flag.open,
                })
                else updateSelectProductTypeVariants.push({
                    ...p,
                    open: false,
                })
            })
            realSelectedProductTypeVariants = JSON.parse(JSON.stringify(updateSelectProductTypeVariants))
            renderProductTypeVariantsItem($('.select-product-type-variants-component'));
            $.unwrapContent('selectVariantsModalFrm');
        }).appendTo(footer);
        let centerEl;
        expandBtn.click(function(){
            const result = _searchByProductTypeAndVariant(allProductTypeVariants, searchText);
            result.forEach(p => p.open = true);
            let inViewPort = [];
            leftList.children('.list-item').each(function(){
                const offSetTop = $(this).offset().top - leftList.offset().top;
                if (offSetTop >= -40 && offSetTop < leftList.height()) inViewPort.push($(this));
            })
            centerEl = inViewPort[parseInt(inViewPort.length/2)] ;
            const offsetTop = centerEl?.position().top - leftList.position().top;
            renderProductTypeVariantsItemModal(result, leftList, rightList, searchText);

            if (inViewPort.length) leftList[0].scrollTop =  leftList.children(`.product-type-id-${centerEl.attr('data-id')}`).offset().top + leftList.scrollTop() - leftList.offset().top - offsetTop;
            
        })
        collapseBtn.click(function(){
            const result = _searchByProductTypeAndVariant(allProductTypeVariants, searchText);
            result.forEach(p => p.open = false);
            let inViewPort = [];
            let flagCenter = null;
            let minDistance = Infinity;
            leftList.children('.list-item').each(function(){
                const offSetTop = $(this).offset().top - leftList.offset().top;
                const distance = Math.abs(offSetTop) + 40;
                if (distance < minDistance) {
                    minDistance = distance;
                    flagCenter = $(this);
                }
                if (offSetTop >= -40 && offSetTop < leftList.height()) inViewPort.push($(this));
            })
            centerEl = inViewPort.length ? inViewPort[parseInt(inViewPort.length/2)] : flagCenter;
            const offsetTop = centerEl.position().top - leftList.position().top;
            renderProductTypeVariantsItemModal(result, leftList, rightList, searchText);
            leftList[0].scrollTop = inViewPort.length ? 
                leftList.children(`.product-type-id-${centerEl.attr('data-id')}`).offset().top + leftList.scrollTop() - leftList.offset().top - offsetTop :
                leftList.children(`.product-type-id-${centerEl.attr('data-id')}`).offset().top + leftList.scrollTop() - leftList.offset().top;
        })
        $.wrapContent(modal, {key: 'selectVariantsModalFrm'});

        modal.moveToCenter().css('top', '100px');
    }

    function checkExist(arr, id){
        for (let index = 0; index < arr.length; index++) {
            if (arr[index].id == id) return arr[index];
        }
        return false;
    }

    function _onChangeVariantCheckbox(productType, variant, checkbox, selectedContainer, leftList, searchText) {
        let atLeastOne = false;
        let allCheck = true;
        let selectedVariants = [];
        const {product_type_name, variants} = productType;
        let isOpen = false;
        variants.forEach(v => {
            if (v.id === variant.id) {
                v.check = $(this).is(':checked');
            }
            if (v.check) {
                atLeastOne = true;
                if (v.id === variant.id) {
                    selectedVariants.push({
                        ...v,
                        justAdd: true,
                    })
                }else{
                    selectedVariants.push(v);
                }

            } else allCheck = false;
        })
        if (atLeastOne) checkbox.prop('checked', false).prop("indeterminate", true);
        else checkbox.prop('checked', false).prop("indeterminate", false);;
        if (allCheck) checkbox.prop('checked', true).prop("indeterminate", false);
        if (selectedVariants.length) {
            $(this).closest('.list-item').children('.list-item__header').find('strong').html(`${product_type_name} (<span class='text-blue'>${selectedVariants.length}</span>/${variants.length})`)
            let existProductType = false;
            selectedProductTypeVariants = selectedProductTypeVariants.map(p => {
                if (p.id == productType.id){
                    existProductType = true;
                    isOpen = p.open;
                    let newVariants = [...p.variants, ...selectedVariants]
                        .filter((v,i,a) => a.findIndex(t => (t.id === v.id)) === i)
                        .filter(v => !(variants.find(i => i.id == v.id) && !selectedVariants.find(i => i.id == v.id)))
                    return {
                        ...p,
                        variants: newVariants,
                    }
                }
                return p;
            })
            if (!existProductType) selectedProductTypeVariants.push({
                ...productType,
                variants: selectedVariants,
                open: false,
            })
        } else { 
            selectedProductTypeVariants = selectedProductTypeVariants.map(
                (p) => {
                  if (p.id == productType.id) {
                    let newVariants = p.variants.filter(v => !(variants.find(i => i.id == v.id)));
                    if (newVariants.length) {
                      return {
                        ...p,
                        variants: newVariants,
                      };
                    }
                    return null;
                  }
                  return p;
                }
            ).filter(v => v);
            $(this).closest('.list-item').children('.list-item__header').find('strong').text(`${product_type_name} (${variants.length})`)
        }
        if ($(this).is(':checked')) $(this).next().removeClass('text-666666')
        else $(this).next().addClass('text-666666')
        if ($(this).is(':checked') && !isOpen)  {
            selectedProductTypeVariants = selectedProductTypeVariants.map(p => {
                if (p.id == productType.id) return {
                    ...p,
                    justAdd: true,
                }
                return p;
            })
        }
        const resultSelectedVariant = _searchByProductTypeAndVariant(selectedProductTypeVariants, searchText)
        renderSelectedProductTypeVariants(resultSelectedVariant, selectedContainer, leftList, searchText)
        if ($(this).is(':checked')) {
            let justAddItem;
            if (isOpen) {
                justAddItem = selectedContainer.children(`.product-type-variant-id-${productType.id}`)
                .children('.list-item__list').children(`.variant-product-type-id-${variant.id}`);
            } else {
                justAddItem = selectedContainer.children(`.product-type-variant-id-${productType.id}`);
            }
            const offSetTop = justAddItem.offset().top - selectedContainer.offset().top;
            if (!(offSetTop >= -10 && offSetTop < selectedContainer.height() - 10 )) {
                selectedContainer.animate({
                    scrollTop: justAddItem.offset().top + selectedContainer.scrollTop() - selectedContainer.offset().top
                }, 500);
            }
        }
    }

    function renderProductTypeVariantsItemModal(productTypeVariant, container, selectedContainer, searchText) {
        container.empty();
        productTypeVariant.forEach(productType => {
            const {product_type_name, variants} = productType;
            let flagChecked = true;
            let flagIndeterminate = false;
            let selectVariants = [];
            variants.forEach(v => {
                if (v.check) {
                    flagIndeterminate = true;
                    selectVariants.push(v);
                }
                else flagChecked = false;
            });
            const item = $('<div>').attr('data-id', productType.id).addClass(`list-item product-type-id-${productType.id} ${productType.open && 'show'}`).appendTo(container);
            const header = $('<div>').addClass('list-item__header').appendTo(item);
            const checkbox = $('<input>').addClass('custom-checkbox').attr('type', 'checkbox').addClass('mr-2');
            const strong = $('<strong>').html(!selectVariants.length ? `${product_type_name} (${variants.length})` : `${product_type_name} (<span class="text-blue">${selectVariants.length}</span>/${variants.length})`)
            $('<div>').addClass('mr-2').append(checkbox).append(strong).appendTo(header);
            checkbox.click(function(e){
                e.stopPropagation();
            }).change(function(){
                productType.variants.forEach(v => v.check = $(this).is(':checked'));
                if ($(this).is(':checked')) {
                    strong.html(`${product_type_name} (<span class="text-blue">${variants.length}</span>/${variants.length})`);
                    let existProductType = false;
                    selectedProductTypeVariants = selectedProductTypeVariants.map(p => {
                        if (p.id == productType.id){
                            existProductType = true;
                            let newVariants = [...p.variants, ...variants]
                                .filter((v,i,a) => a.findIndex(t => (t.id === v.id)) === i)
                            return {
                                ...productType,
                                variants: newVariants,
                                justAdd: true,
                                open: p.open
                            };
                        } else return p;
                        
                    })
                    
                    if (!existProductType) selectedProductTypeVariants.push({
                        ...productType,
                        open: false,
                        justAdd: true,
                    })
                    $(this).closest('.list-item').children('.list-item__list').children('.list-item__list-item').children('p').removeClass('text-666666');
                } else {
                    strong.html(`${product_type_name} (${variants.length})`);
                    selectedProductTypeVariants = selectedProductTypeVariants.map(
                        (p) => {
                          if (p.id == productType.id) {
                            let newVariants = p.variants.filter(v => !(variants.find(i => i.id == v.id)));
                            if (newVariants.length) {
                              return {
                                ...p,
                                variants: newVariants,
                              };
                            }
                            return null;
                          }
                          return p;
                        }
                    ).filter(v => v);
                    $(this).closest('.list-item').children('.list-item__list').children('.list-item__list-item').children('p').addClass('text-666666');
                }
                const resultSelectedVariant = _searchByProductTypeAndVariant(selectedProductTypeVariants, searchText)
                renderSelectedProductTypeVariants(resultSelectedVariant, selectedContainer, container, searchText);
                if ($(this).is(':checked')) {
                    const justAddItem = selectedContainer.children(`.product-type-variant-id-${productType.id}`);
                    const offSetTop = justAddItem.offset().top - selectedContainer.offset().top;
                    if (!(offSetTop >= -40 && offSetTop < selectedContainer.height())) {
                        selectedContainer.animate({
                            scrollTop: justAddItem.offset().top + selectedContainer.scrollTop() - selectedContainer.offset().top
                        }, 500);
                    }
                }
                $(this).closest('.list-item').children('.list-item__list').children('.list-item__list-item').children('input').prop('checked', $(this).is(':checked'))
                $(this).closest('.list-item').children('.list-item__list').children('.list-item__list-item').children('p').prop('checked', $(this).is(':checked'))
            }).prop('indeterminate', flagChecked ? false : flagIndeterminate).prop('checked', flagChecked);
            $($.renderIcon('angle-down-solid-thin')).appendTo(header);
            const variantList = $('<div>').addClass('list-item__list').appendTo(item);
            productType.open ? variantList.css('height', `${variants.length * 35}px`) : variantList.css('height', '0px');
            variants.forEach(variant => {
                $('<div>').addClass('list-item__list-item').append(
                    $('<input>').addClass('custom-checkbox').attr('type', 'checkbox').addClass('mr-2').change(function(){
                        const __onChangeVariantCheckbox = _onChangeVariantCheckbox.bind(this)
                        __onChangeVariantCheckbox(productType, variant, checkbox, selectedContainer, container, searchText)
                    }).prop('checked', variant.check)
                ).append($(`<p>`).addClass(!variant.check && 'text-666666').text(`${variant.title}`)).appendTo(variantList);
            });
            header.click(function(){
                item.toggleClass('show');
                if (item.hasClass('show')) {
                    productType.open = true;
                    variantList.css('height', `${variants.length * 35}px`);
                } else {
                    productType.open = false;
                    variantList.css('height', '0px');
                }
            })
        })
    }

    function renderSelectedProductTypeVariants(selectedProductType, container, leftList, searchText){
        selectedProductType.sort(function(a, b) {
            var textA = a.product_type_name.toUpperCase();
            var textB = b.product_type_name.toUpperCase();
            return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
        });
        container.prev().remove();
        container.empty();
        let centerEl;
        const header = $('<div>')
            .addClass('d-flex justify-content-between select-variants-modal__content-left-header')
            .append($('<strong>').css('font-size', '18px').html(`Selected Variants: <span class='text-blue'></span>`))
            .append($('<div>').css('font-size', '14px').append($('<span>').addClass('pointer hover-underline').text('Expand ').click(function(){
                // selectedProductType.forEach(p => p.open = true);
                // container.children('.list-item').each(function(){
                //     $(this).addClass('show');
                //     $(this).children('.list-item__list').css('height', `${$(this).children('.list-item__list').children('.list-item__list-item').length * 35}px`)
                // })
                //const result = _searchByProductTypeAndVariant(allProductTypeVariants, searchText);
                selectedProductType.forEach(p => p.open = true);
                let inViewPort = [];
                container.children('.list-item').each(function(){
                    const offSetTop = $(this).offset().top - container.offset().top;
                    if (offSetTop >= -40 && offSetTop < container.height()) inViewPort.push($(this));
                })
                centerEl = inViewPort[parseInt(inViewPort.length/2)] ;
                const offsetTop = centerEl?.position().top - leftList.position().top;
                renderSelectedProductTypeVariants(selectedProductType, container, leftList, searchText);

                if (inViewPort.length) container[0].scrollTop =  container.children(`.product-type-variant-id-${centerEl.attr('data-id')}`).offset().top + container.scrollTop() - container.offset().top - offsetTop;
            
            }))
            .append($("<span class='mx-2' />").text('/')).append($('<span>').addClass('pointer hover-underline').text(' Collapse').click(function(){
                // selectedProductType.forEach(p => p.open = false);
                // container.children('.list-item').each(function(){
                //     $(this).removeClass('show');
                //     $(this).children('.list-item__list').css('height', `0px`)
                // })
                selectedProductType.forEach(p => p.open = false);
                let inViewPort = [];
                let flagCenter = null;
                let minDistance = Infinity;
                container.children('.list-item').each(function(){
                    const offSetTop = $(this).offset().top - container.offset().top;
                    const distance = Math.abs(offSetTop) + 40;
                    if (distance < minDistance) {
                        minDistance = distance;
                        flagCenter = $(this);
                    }
                    if (offSetTop >= -40 && offSetTop < container.height()) inViewPort.push($(this));
                })
                centerEl = inViewPort.length ? inViewPort[parseInt(inViewPort.length/2)] : flagCenter;
                const offsetTop = centerEl.position().top - container.position().top;
                renderSelectedProductTypeVariants(selectedProductType, container, leftList, searchText);
                container[0].scrollTop = inViewPort.length ? 
                container.children(`.product-type-variant-id-${centerEl.attr('data-id')}`).offset().top + container.scrollTop() - container.offset().top - offsetTop :
                container.children(`.product-type-variant-id-${centerEl.attr('data-id')}`).offset().top + container.scrollTop() - container.offset().top;
            }))).insertBefore(container);
        let countVariants = 0;
        selectedProductType.sort().forEach(productType => {
            const {product_type_name, variants} = productType;
            const item = $('<div>').attr('data-id', productType.id).addClass(`list-item ${productType.open && 'show'} product-type-variant-id-${productType.id}`).appendTo(container);
            const header = $('<div>').addClass(`list-item__header ${productType.justAdd ? 'bg-yellow' : ''}`).appendTo(item);
            setTimeout(() =>{
                header.removeClass('bg-yellow');
                productType.justAdd = false;
            },0)
            $('<div>').addClass('mr-2').append($('<strong>').html(`${product_type_name} <span class='text-blue'>(${variants.length})</span>`)).appendTo(header);
            $($('<i>').addClass('mr-2').append($.renderIcon('angle-down-solid-thin'))).appendTo(header);
            const variantList = $('<div>').addClass('list-item__list pl-0').appendTo(item);
            productType.open ? variantList.css('height', `${variants.length * 35}px`) : variantList.css('height', '0px');
            variants.forEach(variant => {
                countVariants += 1;
                const variantItem = $('<div>').addClass(`d-flex list-item__list-item list-item__list-item-right text-666666 variant-product-type-id-${variant.id} ${variant.justAdd ? 'bg-yellow' : ''}`)
                .append($('<div>').append($('<span>').addClass('mr-2').html('&#8226')).append($('<span>').text(variant.title)))
                .append($($.renderIcon('times')).click(function(){
                    const flag = productType.variants.filter(v => v.id !== variant.id);
                    if(flag.length) {
                        selectedProductTypeVariants = selectedProductTypeVariants.map(p => {
                            if(p.id !== productType.id) return p;
                            return {
                                ...p,
                                variants: p.variants.filter(v => v.id !== variant.id)
                            }
                        })
                    } else {
                        selectedProductTypeVariants = selectedProductTypeVariants.filter(p => p.id !== productType.id)
                    }
                    
                    allProductTypeVariants = allProductTypeVariants.map(p => {
                        if(p.id !== productType.id) return p;
                        return {
                            ...p,
                            variants: p.variants.map(v => {
                                if(v.id !== variant.id) return v;
                                return {
                                    ...v,
                                    check: false,
                                }
                            })
                        }
                    })
                    const result = _searchByProductTypeAndVariant(allProductTypeVariants, searchText);
                    const resultSelectedVariant = _searchByProductTypeAndVariant(selectedProductTypeVariants, searchText)
                    renderSelectedProductTypeVariants(resultSelectedVariant, container, leftList, searchText);
                    renderProductTypeVariantsItemModal(result, leftList, container, searchText)
                }))
                .appendTo(variantList)
                setTimeout(function () {
                    variantItem.removeClass('bg-yellow');
                    variant.justAdd = false;
                }, 0)
            })
            header.click(function(){
                item.toggleClass('show');
                if (item.hasClass('show')) {
                    productType.open = true;
                    variantList.css('height', `${variants.length * 35}px`);
                } else {
                    productType.open = false;
                    variantList.css('height', '0px');
                } 
            })
        });
        if(countVariants) header.find('span.text-blue').text(`(${countVariants})`)
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