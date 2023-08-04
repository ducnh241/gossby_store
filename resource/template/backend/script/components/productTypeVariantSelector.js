/*
* Product Type Variant Selector
* */
window.initProductTypeVariantSelector = function() {
    const $container = $(this);
    const data = $container.data('init');
    const inputName = $container.data('inputName') || 'selected_product_type_variant';
    const typeCount = Object.keys(data).length;
    let selected = $container.data('selected') || {};

    // re-format selected
    if (selected === '*' || Array.isArray(selected) && selected.includes('*')) {
        selected = Object.values(data).reduce((carry, productType) => ({ ...carry, [productType.id]: '*' }), {});
    } else {
        for (const [key, variants] of Object.entries(selected)) {
            if (Array.isArray(variants) && variants.includes('*')) {
                selected[key] = '*';
            }
        }
    }

    $container
        .on('click', '.pd-type__toggle', function() {
            const $this = $(this);
            const textContent = $this.hasClass('active') ? 'Collapse' : 'Expand';

            $this.toggleClass('active').text(textContent);
            $container.find('.pd-type__body').slideToggle(300);
        })
        .on('click', '.pd-type__open-modal', renderProductTypeVariantModal)
        .on('click', '.pd-type__item-title', function() {
            $(this).toggleClass('active').siblings('.pd-type__variants').slideToggle('fast');
        })
        .on('click', '.pd-type__item-remove', function() {
            const typeId = $(this).data('id');
            $(this).closest('.pd-type__item').remove();

            delete selected[typeId];
            handleUpdate();
        })
        .on('click', '.pd-type__variant-remove', function() {
            const $this = $(this);
            const typeId = $this.data('typeId');
            const variantId = $this.data('id');

            const selectedVariants = selected[typeId];

            if (!selectedVariants) {
                return false;
            }
            else if (selectedVariants === '*') {
                $(this).closest('.pd-type__variant').remove();
                selected[typeId] = data[typeId].variants.reduce((carry, variant) => {
                    if (variant.id !== variantId) carry.push(variant.id);
                    return carry;
                }, []);
            }
            else if (selectedVariants?.length === 1) {
                $(this).closest('.pd-type__item').remove();
                delete selected[typeId];
            }
            else {
                $(this).closest('.pd-type__variant').remove();
                selected[typeId] = selectedVariants.filter(id => id !== variantId);
            }

            handleUpdate();
        })
        .on('force-update', function() {
            $container.trigger('update', [selected]);
        })
        .append(`
<div class="pd-type">
    <div class="pd-type__header">
        <label class="title pd-type__title mb0">Product Type <span class="pd-type__state">(All Selected)</span></label>
        <span class="pd-type__toggle">Collapse</span>
        <button type="button" class="pd-type__open-modal" type="button">Modify Selection</button>
    </div>
    <div class="pd-type__body">
        <div class="pd-type__list custom-scrollbar">
            <div class="pd-type__item">
                <div class="pd-type__item-title">
                    <span>Wrapped Canvas</span>
                    <span class="pd-type__item-caret">${$.renderIcon('angle-down-solid').outerHTML}</span>
                    <button class="pd-type__item-remove btn-close" type="button" />
                </div>
                <div class="pd-type__variants">
                    <div class="pd-type__variant">Two Tones Mug Black 11oz <button class="pd-type__variant-remove btn-close" type="button" /></div>
                </div>
            </div>
        </div>
    </div>
    <input class="pd-type__hidden-input" type="hidden" name="${inputName}" value="" />
</div>
        `);

    renderSelectedVariants();
    handleUpdate();

    function renderProductTypeVariantModal(e) {
        e?.preventDefault();
        const modalKey = 'productTypeVariantModal';

        $.unwrapContent(modalKey);

        const isSelectedAll = Object.keys(selected).length === typeCount && Object.values(selected).every(val => val === '*')

        const $modal = $(`
<div class="osc-modal md-type-selector">
    <div class="md-type-selector__header">
        <div class="md-type-selector__title">Select Product Type</div>
        <button class="md-type-selector__close btn-close" type="button"></button>
    </div>
    <div class="pd-type-selector">
        <div class="pd-type-selector__form">
            <span>${$.renderIcon('search').outerHTML}</span>
            <input class="pd-type-selector__input" type="text" placeholder="Search for product type" />
        </div>
        <div class="pd-type-selector__header">
            <span>Product Types</span>
            <button type="button" class="pd-type-selector__select-all ${isSelectedAll ? 'active' : ''}">${isSelectedAll ? 'Deselect all' : 'Select all'}</button>
        </div>
        <div class="pd-type-selector__list custom-scrollbar" />
    </div>
    <div class="md-type-selector__footer">
        <button type="button" class="md-type-selector__submit">Apply</button>
    </div>
</div>
    `).css({ width: 1200 });

        $modal
            .on('click', '.styled-checkbox', function(e) {
                e.stopPropagation();
            })
            .on('click', '.md-type-selector__close', function() {
                $.unwrapContent(modalKey);
            })
            .on('click', '.md-type-selector__submit', function() {
                let result = {};

                $modal.find('.pd-type-selector__type-checkbox:checked').each(function() {
                    const id = $(this).data('id');
                    result[id] = '*';
                });

                $modal.find('.pd-type-selector__type-checkbox.indeterminate').each(function() {
                    const id = $(this).data('id');
                    const variantIds = [];

                    $(this).closest('.pd-type-selector__type').find('.pd-type-selector__variant-checkbox:checked').each(function() {
                        const id = $(this).data('id');
                        variantIds.push(id);
                    });

                    result[id] = variantIds;
                });

                selected = result;
                renderSelectedVariants();
                handleUpdate();
                $.unwrapContent(modalKey);
            })
            .on('click', '.pd-type-selector__select-all', function() {
                const $el = $(this);
                const isSelected = $el.hasClass('active');
                const $checkboxes = $modal.find('.pd-type-selector__type-checkbox');

                $el.toggleClass('active');

                if (isSelected) {
                    $el.text('Select all');
                    $checkboxes.prop('checked', false).trigger('change');
                }
                else {
                    $el.text('Deselect all');
                    $checkboxes.prop('checked', true).trigger('change');
                }
            })
            .on('click', '.pd-type-selector__type-toggle, .pd-type-selector__type-label', function() {
                $(this)
                    .closest('.pd-type-selector__type').toggleClass('is-expanded')
                    .find('.pd-type-selector__variants').slideToggle('fast');
            })
            .on('input', '.pd-type-selector__input', function() {
                let keyword = $(this).val().toLowerCase();

                if (!keyword) {
                    $('.pd-type-selector__type, .pd-type-selector__variant').removeClass('hide');
                    return;
                }

                $('.pd-type-selector__type').each(function() {
                    const $type = $(this);
                    let shouldShowType = $type.data('title').toLowerCase().includes(keyword);

                    $type.find('.pd-type-selector__variant').each(function() {
                        const $variant = $(this);
                        if ($variant.data('title').toLowerCase().includes(keyword)) {
                            shouldShowType = true;
                            $variant.removeClass('hide');
                        }
                        else {
                            $variant.addClass('hide');
                        }
                    });

                    if (shouldShowType) {
                        $type.removeClass('hide');
                    }
                    else {
                        $type.addClass('hide');
                    }
                });
            })
            .on('change', '.pd-type-selector__type-checkbox', function() {
                const $el = $(this);
                const checked = $el.prop('checked');
                const $type = $el.closest('.pd-type-selector__type');
                const $variantCheckbox = $type.find('.pd-type-selector__variant-checkbox');
                const $count = $type.find('.pd-type-selector__type-count');

                $variantCheckbox.prop('checked', checked);
                $el.removeClass('indeterminate');

                if (checked) {
                    const length = $variantCheckbox.length;
                    $count.html(`(<span class="text-primary">${length}</span>/${length})`);
                }
                else {
                    $count.empty();
                }
            })
            .on('change', '.pd-type-selector__variant-checkbox', function() {
                const $el = $(this);
                const $type = $el.closest('.pd-type-selector__type');
                const $typeCheckbox = $type.find('.pd-type-selector__type-checkbox');
                const $count = $type.find('.pd-type-selector__type-count');
                const checkedLength = $type.find('.pd-type-selector__variant-checkbox:checked').length;
                const uncheckedLength = $type.find('.pd-type-selector__variant-checkbox:not(:checked)').length;

                if (checkedLength && !uncheckedLength) {
                    $count.html(`(<span class="text-primary">${checkedLength}</span>/${checkedLength + uncheckedLength})`);
                    $typeCheckbox.removeClass('indeterminate').prop('checked', true);
                }
                else if (uncheckedLength && !checkedLength) {
                    $count.empty();
                    $typeCheckbox.removeClass('indeterminate').prop('checked', false);
                }
                else {
                    $count.html(`(<span class="text-primary">${checkedLength}</span>/${checkedLength + uncheckedLength})`);
                    $typeCheckbox.addClass('indeterminate').prop('checked', false);
                }
            });

        $.wrapContent($modal, { key: modalKey });

        $modal.moveToCenter().css('top', '50px');

        renderList();

        function renderList() {
            const $list = $modal.find('.pd-type-selector__list');
            if (!$list.length) return false;

            for (const [, productType] of Object.entries(data)) {
                const isIndeterminate = Array.isArray(selected[productType.id]) && selected[productType.id].length < data[productType.id].variants.length;

                $list.append(`
<div class="pd-type-selector__type" data-title="${productType.title}">
    <div class="pd-type-selector__type-header">
        <span class="pd-type-selector__type-toggle mr15">${$.renderIcon('icon-polygon').outerHTML}</span>
        <div class="pd-type-selector__type-label">
            <div class="styled-checkbox">
                <input class="pd-type-selector__type-checkbox ${isIndeterminate ? 'indeterminate': ''}" type="checkbox" data-id="${productType.id}" ${selected[productType.id] === '*' ? 'checked' : ''} />
                <ins>${$.renderIcon('check-solid').outerHTML}</ins>
            </div>
            <span>${productType.title}</span>&nbsp;
            <span class="pd-type-selector__type-count">
                ${ (function() {
                    let variantCount = productType.variants.length;

                    if (selected[productType.id] === '*') {
                        return `(<span class="text-primary">${variantCount}</span>/${variantCount})`;
                    }
                    else if (Array.isArray(selected[productType.id])) {
                        return `(<span class="text-primary">${selected[productType.id].length}</span>/${variantCount})`;
                    }

                    return '';
                })() }
            </span>
        </div>
    </div>
    <div class="pd-type-selector__variants">
        ${ productType.variants.map(variant => `
            <div class="pd-type-selector__variant" data-title="${variant.title}">
                <div class="styled-checkbox">
                    <input class="pd-type-selector__variant-checkbox" type="checkbox" data-id="${variant.id}" ${ selected[productType.id] === '*' || selected[productType.id]?.includes(variant.id) ? 'checked' : ''} />
                    <ins>${$.renderIcon('check-solid').outerHTML}</ins>
                </div>
                <span class="ml-2">${variant.title}</span>
            </div>
            `).join('')
                }
    </div>
</div>
            `);
            }
        }
    }

    function renderSelectedVariants() {
        const $list = $container.find('.pd-type__list');

        $list.empty();

        for (const [typeId, variantIds] of Object.entries(selected)) {
            const productType = data[typeId];
            const variants = variantIds === '*' ? productType.variants : productType.variants.filter(variant => variantIds.includes(variant.id));

            $list.append(`
<div class="pd-type__item">
    <div class="pd-type__item-title">
        <span>${productType.title}</span>
        <span class="pd-type__item-caret">${$.renderIcon('angle-down-solid').outerHTML}</span>
        <button type="button" class="pd-type__item-remove btn-close" data-id="${productType.id}" />
    </div>
    <div class="pd-type__variants">
        ${ variants.map(variant => `<div class="pd-type__variant">${variant.title} <button type="button" class="pd-type__variant-remove btn-close" data-id="${variant.id}" data-type-id="${productType.id}" /></div>`).join('') }
    </div>
</div>
        `);
        }
    }

    function handleUpdate() {
        let tmp = JSON.parse(JSON.stringify(selected));
        const selectedTypeCount = Object.keys(tmp).length;

        const $body = $container.find('.pd-type__body');
        const $state = $container.find('.pd-type__state');
        const $toggle = $container.find('.pd-type__toggle');

        if (!selectedTypeCount) {
            $body.addClass('hide');
            $state.text('');
            $toggle.hide();

            tmp = '';
        }
        else if (selectedTypeCount === typeCount && Object.values(selected).every(val => val === '*')) {
            $body.removeClass('hide');
            $state.text('(All Selected)');
            $toggle.show();

            tmp = '*';
        }
        else {
            $body.removeClass('hide');
            $state.text('(' + selectedTypeCount + ')');
            $toggle.show();
        }

        $container.find('.pd-type__hidden-input').val(typeof tmp === 'object' ? JSON.stringify(tmp) : tmp);
        $container.trigger('update', [selected]);
    }
}
