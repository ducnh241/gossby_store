(function ($) {
    'use strict';

    const $form = $('.form-container');
    const $productTypeSelect = $('#productTypeSelect');
    const $versionNav = $('.js-version-nav');
    const $dataInput = $('.js-version-data');
    const $typeInput = $('.js-addon-type-input');

    const errors = {};
    const config = {
        viewMode: 0,
        requireOptionImage: 0,
        enableAbTest: 0,
        max_version_index: 0,
        versionLabelMapping: {
            'is_hide': 'Hide this version',
            'service_title': 'Add-on Section Title',
            'enable_same_price': 'Options in this group have the same price',
            'group_price': 'Group Price',
            'show_message': 'Add Message Field',
            'placeholder': 'Placeholder',
            'description': 'Option Description',
            'display_area': 'Display Area',
            'auto_select': 'Auto Select',
        },
        versionValueMapping: {
            'is_hide': {
                [0]: 'No',
                [1]: 'Yes',
            },
            'enable_same_price': {
                [0]: 'No',
                [1]: 'Yes',
            },
            'show_message': {
                [0]: 'No',
                [1]: 'Yes',
            },
            'auto_select': {
                [0]: 'No',
                [1]: 'Yes',
            },
            'display_area': {
                [0]: 'Cart Only',
                [1]: 'Product Page + Cart',
            }
        },
        optionLabelMapping: {
            'title': 'Title',
            'price': 'Price',
        },
    };
    const addon_data = collectAddonData();

    // for debugging
    window.addon_data = addon_data;
    window.config = config;
    window.errors = errors;

    $typeInput.each(function () {
        if ($(this).prop('readonly') && !$(this).prop('checked')) {
            $(this).closest('.form-inline').css({
                pointerEvents: 'none',
                opacity: '0.5',
            });
        }
    });

    $form
        .on('submit', onFormSubmit)
        .on('input', 'input[type="number"]', function (e) {
            if (this.value.search(/[e+-]/gi) !== -1) {
                this.value = this.value.replaceAll(/[e+-]/gi, '');
            }

            let trimLength = this.value.split('.')?.[1]?.length || 0;

            if (trimLength > 2) {
                this.value = this.value.slice(0, 2 - trimLength);
            }

            if (!this.maxLength) return;

            if (this.value.length > this.maxLength) {
                this.value = this.value.slice(0, this.maxLength)
            }
        })
        .on('keypress', 'input[type="number"]', function (e) {
            if ([43, 45, 95, 69, 101, 187, 188, 189, 190].includes(e.keyCode)) {
                e.preventDefault();
            }
        })
        .on('blur', 'input[type="text"], textarea', function() {
            this.value = this.value.replaceAll(/^\s*|\s*$/g, '');
        })
        .on('uploader-update', '.js-image-uploader, .js-video-uploader', function() {
            const versionId = $('.js-version').data('version-id');
            validateMockupData(versionId);
            compareCurrentWithDefault();
        })
        .on('uploader-update', '.js-option-image-uploader', function(event, data) {
            const $el = $(this);
            const $option = $el.closest('.js-option');
            const $version = $option.closest('.js-version');

            const optionId = $option.data('option-id');
            const versionId = $version.data('version-id');

            addon_data.versions[versionId].options[optionId].image = data?.input_value || '';
            addon_data.versions[versionId].options[optionId].image_url = data?.image_url || '';

            validateOption(versionId, optionId, 'image');
            compareCurrentWithDefault();
        })
        .on('update', '.js-addon-product-type', onUpdateAddonProductType)
        .on('change', '.js-variant-product-type', onChangeVariantProductType)
        .on('change', '.js-addon-type-input', onChangeAddonType)
        .on('change', '.js-ab-test-enable', onChangeAbTestStatus)
        .on('change', '.js-version-input', onChangeVersionInput)
        .on('change', '.js-option-input', onChangeOptionInput)
        .on('change', '.js-extend-toggle', onToggleExtendField)
        .on('click', '.js-show-versions-diff', onShowVersionDiff)
        .on('click', '.js-version-add', () => {
            if (config.viewMode) return false;

            addVersion();

            const viewHeight = $(window).height();
            const scrollTop = $(window).scrollTop();
            const offsetTop = $('.addon-tabs').offset().top - 86;

            if (offsetTop < scrollTop || offsetTop > (scrollTop + viewHeight)) {
                $(window).scrollTop(offsetTop);
            }
        })
        .on('click', '.js-version-delete', () => deleteVersion())
        .on('click', '.js-version-tab', onClickVersionTab)
        .on('click', '.js-option-add', () => addOption())
        .on('click', '.js-option-delete', function() {
            const optionId = $(this).closest('.js-option').data('option-id');
            const versionId = $(this).closest('.js-version').data('version-id');
            deleteOption(versionId, optionId);
        });

    onChangeAddonType();
    onChangeAbTestStatus();
    checkViewMode();

    function collectAddonData() {
        const data = {
            versions: {}
        };

        try {
            data.versions = JSON.parse($dataInput.val());

            if (!data.versions || Array.isArray(data.versions) && !data.versions.length) {
                data.versions = {};
            }
        } catch (error) {
            data.versions = {};
        }

        for (const [, version] of Object.entries(data.versions)) {
            Object.assign(version, version.data);

            if (version.is_default_version) {
                data.default_version = version;
            }

            delete version.data;
        }

        return data;
    }

    function onFormSubmit(e) {
        if (!validateAddon()) {
            e.preventDefault();

            if (Object.keys(errors).length) {
                alert('Your form have some invalid fields.');

                $(window).scrollTop(
                    $('.addon-tabs').offset().top - 86
                );
            }

            return false;
        }

        $('button[type="submit"]').on('click', function(e) {
            e.preventDefault();
        });

        ['field', 'label', 'version_by_types', 'last_type', 'min_option_price'].forEach(key => {
            delete addon_data[key];
        });

        $('.js-version-data').val(JSON.stringify(addon_data.versions));

        ['display_area', 'option_image'].forEach(name => {
            $(`input[name="${name}"]`).attr('disabled', true);
        });
    }

    function onChangeAddonType() {
        let $currentTypeInput = $('.js-addon-type-input:checked');

        if (!$currentTypeInput.length) {
            $currentTypeInput = $typeInput.eq(0);
            $currentTypeInput.prop('checked', true);
        }

        let typeConfig = $currentTypeInput.data('config');

        if (typeof typeConfig === 'object') {
            Object.assign(config, typeConfig);
        }

        const newAddonType = parseInt($currentTypeInput.val());

        if (!addon_data.version_by_types) addon_data.version_by_types = {};

        addon_data.last_type = addon_data.type;
        addon_data.type = newAddonType;

        if (addon_data.last_type && addon_data.last_type !== addon_data.type) {
            addon_data.version_by_types[addon_data.last_type] = addon_data.versions;
            addon_data.versions = addon_data.version_by_types[addon_data.type] || {};
        }

        addon_data.default_version = Object.values(addon_data.versions).find(version => version.is_default_version);

        config.max_version_index = Object.values(addon_data.versions).reduce((carry, version) => {
            return Math.max(
                carry,
                Number(version.title.replace(/\D/g, '')),
                (version.max_version_index || 0)
            );
        }, 0);

        $productTypeSelect.select2({ placeholder: "Search for product type" });

        Object.keys(errors).forEach(key => delete errors[key]);

        $('.js-version').remove();
        $('.js-version-tab').remove();

        toggleFields();
        onChangeVariantProductType();
        renderAllVersions();
        updateOptionList();

        $('.js-addon-product-type').trigger('force-update');
        $('.addon-tabs__nav').trigger('update');
    }

    function onChangeAbTestStatus() {
        const enable = parseInt($('.js-ab-test-enable').val());
        const $abTestTime = $('.js-ab-test-time');
        const $addVersionBtn = $('.js-version-add');
        const $version = $('.js-version');

        if (!enable) {
            let hasError = false;

            Object.values(addon_data.versions).filter(version => !version.is_default_version).forEach(version => {
                validateVersion(version);

                if (errors[version.id]) hasError = true;
            });

            if (hasError) {
                alert('Complete not-default versions before disable A/B test');

                $('.js-ab-test-enable').trigger('click');

                return false;
            }
        }

        config.enableAbTest = enable;

        if (enable) {
            $abTestTime.removeClass('hidden').find('input').attr('disabled', false);
            $addVersionBtn.removeClass('hidden');

            $version.removeClass('is-disabled');
        }
        else {
            $abTestTime.addClass('hidden').find('input').attr('disabled', true);
            $addVersionBtn.addClass('hidden');

            if (!addon_data.current_version.is_default_version) {
                $version.addClass('is-disabled');
            }
        }

        toggleVersionFields();
    }

    function onChangeVersionInput() {
        const $input = $(this);
        const inputName = $input.data('name');
        const inputType = $input.attr('type');
        let inputValue = $input.val();

        const $version = $input.closest('.js-version');
        const versionId = $version.data('version-id');

        if (inputType === 'checkbox') {
            inputValue = this.checked ? 1 : 0;
        }

        if (inputName === 'is_default_version') {
            if (addon_data.versions[versionId].is_hide) {
                $input.prop('checked', false);
                alert('Can not set hidden version as default.');
            }
            else {
                Object.values(addon_data.versions).forEach(version => {
                    version.is_default_version = 0;
                });

                inputValue = 1;

                addon_data.default_version = addon_data.versions[versionId];
            }
        }
        else if (inputName === 'is_hide' && inputValue === 1 && addon_data.versions[versionId].is_default_version) {
            $input.prop('checked', false);
            alert('Can not hide default version.');
            return false;
        }
        else if (inputName === 'enable_same_price') {
            const $priceInput = $('.js-option-input[data-name="price"]');

            if (inputValue) {
                $priceInput.closest('.form-input').addClass('hidden');
            }
            else {
                $priceInput.closest('.form-input').removeClass('hidden');
            }
        }
        else if (inputName === 'group_price') {
            inputValue = inputValue === '' ? inputValue : Number(inputValue);
        }
        else if (inputName === 'display_area') {
            inputValue = Number(inputValue);
        }

        addon_data.versions[versionId][inputName] = inputValue;

        validateVersion(addon_data.current_version, inputName);
        compareCurrentWithDefault();
    }

    function onChangeOptionInput() {
        const $input = $(this);
        const inputName = $input.data('name');
        const inputType = $input.attr('type');
        let inputValue = $input.val();

        const $option = $input.closest('.js-option');
        const optionId = $option.data('option-id');

        const $version = $option.closest('.js-version');
        const versionId = $version.data('version-id');
        const version = addon_data.versions[versionId];

        if (inputType === 'checkbox') {
            inputValue = this.checked ? 1 : 0;
        }

        if (inputName === 'price') {
            inputValue = Number(inputValue);
        }
        else if (inputName === 'is_default') {
            inputValue = this.checked ? 1 : 0;

            if (inputValue) {
                for (const[, option] of Object.entries(version.options)) {
                    option.is_default = 0;
                }
            }
        }

        version.options[optionId][inputName] = inputValue;

        validateOption(versionId, optionId, inputName);
        compareCurrentWithDefault();
    }

    function onToggleExtendField() {
        const $parent = $(this).closest('.js-extend-parent');
        const extendBox = $parent.find('.js-extend-field');

        if (this.checked) {
            extendBox.removeClass('hidden');
        } else {
            extendBox.addClass('hidden');
        }
    }

    function onClickVersionTab() {
        if ($(this).hasClass('active')) return false;

        const versionId = $(this).data('versionId');

        addVersion(addon_data.versions[versionId]);
    }

    function onUpdateAddonProductType(e, selected) {
        if (selected === '*' || Object.keys(selected || {}).length) {
            $form.find('.set-active-time').removeClass('hidden').find('input').prop('disabled', false).prop('required', true);
        }
        else {
            $form.find('.set-active-time').addClass('hidden').find('input').prop('disabled', true);
        }
    }

    function onChangeVariantProductType() {
        if (!$productTypeSelect.length || $productTypeSelect.prop('disabled')) {
            addon_data.min_option_price = 0;
            addon_data.min_option_price_message = '';
            return;
        }

        let value = $productTypeSelect.val();

        const [, max_exist_type_price, title] = value.split('/') || 0;

        addon_data.min_option_price = max_exist_type_price / 100 || 0
        addon_data.min_option_price_message = `Add-on variant price for "${title}" must be greater than or equal $${addon_data.min_option_price}`;

        for (const [versionId, version] of Object.entries(addon_data.versions)) {
            for (const [optionId, option] of Object.entries(version.options)) {
                if (option.price) {
                    validateOption(versionId, optionId, 'price');
                }
            }
        }
    }

    function onShowVersionDiff() {
        const modalKey = 'modal_version_differences';

        const $modal = $(`
<div class="osc-modal diff-modal">
    <div class="diff-modal__header">Version Differences</div>
    <button type="button" class="diff-modal__close"></button>
    <div class="diff-modal__body">
    </div>
</div>
        `).width(722);

        $modal.on('click', '.diff-modal__close', function() {
            $.unwrapContent(modalKey);
        });

        $.unwrapContent(modalKey);
        $.wrapContent($modal, { key: modalKey });

        $modal.moveToCenter().css('top', '50px');

        const $body = $modal.find('.diff-modal__body');

        if (addon_data.default_version) {
            renderDiffVersion(addon_data.default_version, true);
        }

        Object.values(addon_data.versions).filter(version => !version.is_default_version).forEach(version => renderDiffVersion(version));

        function renderDiffVersion(version, isDefault = false) {
            let hasChange = false;
            version = isDefault ? version : getDifferentVersion(version);

            const $version = $(`
<div class="diff-version${isDefault ? ' diff-version--default' : ''}">
    <div class="diff-version__title">${isDefault ? 'Default Version: ' : ''}${version.title}</div>
</div>
            `).appendTo($body);

            let versionInfo = [];

            for (const [fieldName, fieldLabel] of Object.entries(config.versionLabelMapping)) {
                let value = config.versionValueMapping[fieldName]?.[version[fieldName]] || version[fieldName];
                if (version.hasOwnProperty(fieldName)) {
                    versionInfo.push(`${fieldLabel}: <strong>${value}</strong>`);
                }
            }

            if (versionInfo.length) {
                hasChange = true;
                $(`<div class="diff-version__info">${versionInfo.join('<br /> ')}</div>`).appendTo($version);
            }

            if (Object.keys(version.options).length) {
                const $optionList = $(`<div class="diff-version__options"></div>`);
                let hasChangedOption = false;

                Object.values(version.options).forEach((option, index) => {
                    if (option.status === 'unchanged') {
                        return false;
                    }

                    hasChange = true;
                    hasChangedOption = true;

                    const $option = $(`
                    <div class="diff-option">
                        <div class="diff-option__title">${config.label} ${('0' + (index + 1)).slice(-2)}${option.is_default ? ' (default)': ''}${option.status === 'add' ? ' (Added)' : ''}</div>
                    </div>
                `).appendTo($optionList);

                    const optionInfo = [];

                    for (const [fieldName, fieldLabel] of Object.entries(config.optionLabelMapping)) {
                        if (option.hasOwnProperty(fieldName)) {
                            optionInfo.push(`${fieldLabel}: <strong>${option[fieldName]}</strong>`);
                        }
                    }

                    if (optionInfo.length) {
                        $(`<div class="diff-option__info">${optionInfo.join(', ')}</div>`).appendTo($option);
                    }

                    if (option.image_url) {
                        $(`<div class="diff-option-image">
                            <img src="${option.image_url}" alt="" class="diff-option-image__frame">
                            <div class="diff-option-image__filename">${option.image}</div>
                        </div>`).appendTo($option);
                    }
                    else if (!isDefault && option.hasOwnProperty('image_url')) {
                        $(`<div class="diff-option-image">
                            <div class="diff-option-image__filename">Image has been deleted</div>
                        </div>`).appendTo($option);
                    }
                });

                if (hasChangedOption) {
                    $optionList.appendTo($version);
                }
            }

            if (version.images?.length) {
                hasChange = true;

                $('<div class="diff-version__mockup-label">Images</div>').appendTo($version);
                const $images = $('<div class="diff-version__mockups" />').appendTo($version);

                version.images.forEach(image => {
                    $(`
                        <div class="diff-mockup">
                            <div class="diff-mockup__frame">
                                <img src="${image.url}" alt="">
                            </div>
                            ${image.status?.length ? `
                                <div class="diff-mockup__status">${image.status.join('<br />')}</div>
                            ` : ''}
                        </div>
                    `).appendTo($images);
                });
            }

            if (version.videos?.length) {
                hasChange = true;

                $('<div class="diff-version__mockup-label">Videos</div>').appendTo($version);
                const $videos = $('<div class="diff-version__mockups" />').appendTo($version);

                version.videos.forEach(video => {
                    $(`
                        <div class="diff-mockup">
                            <div class="diff-mockup__frame">
                              <video src="${video.url}" ${video.thumbnail ? `poster="${video.thumbnail}"` : ''}></video>
                            </div>
                            ${video.status?.length ? `
                                <div class="diff-mockup__status">${video.status.join('<br />')}</div>
                            ` : ''}
                        </div>
                    `).appendTo($videos);
                });
            }

            if (!hasChange) {
                $version.append("<div>There's no change from this version</div>");
            }
        }
    }

    function renderAllVersions() {
        if (Object.values(addon_data.versions || {}).length) {
            Object.values(addon_data.versions || {}).forEach(version => addVersion(version, !version.is_default_version));
        }
        else {
            addVersion({ is_default_version: 1 });
        }
    }

    function addVersion(version, skip_render = false) {
        const defaultVersion = addon_data.default_version
            ? copyDefaultVersion()
            : {
                id: $.makeUniqid(),
                title: '',
                service_title: '',
                group_price: '',
                placeholder: '',
                description: '',
                display_area: 1,
                is_default_version: 0,
                is_hide: 0,
                enable_same_price: 0,
                auto_select: 0,
                images: [],
                videos: [],
                options: {},
            }

        version = { ...defaultVersion, ...version };

        if (!addon_data.versions[version.id]) {
            config.max_version_index++;

            version.title = 'Version ' + ('0' + config.max_version_index).slice(-2);

            if (!Object.keys(addon_data.versions).length) {
                version.is_default_version = 1;
            }

            addon_data.versions[version.id] = version;

            for (const [, version] of Object.entries(addon_data.versions)) {
                version.max_version_index = config.max_version_index;
            }
        }

        if (version.is_default_version) {
            addon_data.default_version = addon_data.versions[version.id];
        }

        if (! $(`.js-version-tab[data-version-id="${version.id}"]`).length) {
            $('.js-version-nav .js-version-add').before(`
    <div class="addon-tabs__btn js-version-tab" data-version-id="${version.id}">${version.title}</div>
            `);
        }

        if (skip_render) return true;

        renderVersion(version);
    }

    function copyDefaultVersion() {
        const copyVersion = {
        ...(JSON.parse(JSON.stringify(addon_data.default_version))),
            id: $.makeUniqid(),
            is_default_version: 0,
            options: {},
        }

        Object.values(addon_data.default_version.options).forEach((defaultOption) => {
            const newOptionId = $.makeUniqid();

            copyVersion.options[newOptionId] = {
                ...JSON.parse(JSON.stringify(defaultOption)),
                id: newOptionId,
            }
        });

        return copyVersion;
    }

    function renderVersion(version) {
        const $versionContainer = $('.js-version-container');
        let versionTemplate = $('#version-template').html();

        addon_data.current_version = addon_data.versions[version.id];

        $('.js-version').remove();
        $('.js-version-tab.active').removeClass('active');
        $(`.js-version-tab[data-version-id="${version.id}"]`).addClass('active');

        Object.keys(version).forEach(key => {
            let value = version[key];

            versionTemplate = versionTemplate.replaceAll(`{{${key}}}`, value)
        });

        const $version = $versionContainer.append(versionTemplate).children(':last-child');

        removeOtherFields($version);

        ['is_default_version', 'is_hide', 'enable_same_price', 'show_message', 'auto_select'].forEach(input_name => {
            if (version[input_name]) {
                $version.find(`input[data-name="${input_name}"]`).prop('checked', true).trigger('change');
            }
        });

        $version.find('.js-image-uploader').data('images', Object.values(version['images'] || {}));
        $version.find('.js-video-uploader').data('videos', Object.values(version['videos'] || {}));
        $version.find(`input[data-name="display_area"][value="${version.display_area}"]`).prop('checked', true);

        $versionNav.trigger('update');

        insertCallbackFunction($version);

        if (Object.values(version.options).length) {
            Object.values(version.options).forEach(option => {
                addOption(option);
            });
        }
        else {
            addOption();
        }

        toggleVersionFields();

        if (errors[version.id]) {
            validateVersion(version);
        }

        if (!config.enableAbTest && !addon_data.current_version.is_default_version) {
            $version.addClass('is-disabled');
        }

        if (version.enable_same_price) {
            const $samePriceField = $version.find('.same-price-enable');

            $samePriceField.removeClass('hidden');

            if (config.enableAbTest || addon_data.current_version.is_default_version) {
                $samePriceField.find('input').attr('disabled', false);
            }
            else {
                $samePriceField.find('input').attr('disabled', true);
            }
        }

        $version.find('.js-extend-toggle').trigger('change');

        compareCurrentWithDefault();
        checkViewMode();
    }

    function compareCurrentWithDefault() {
        const $version = $('.js-version');
        const defaultVersion = addon_data.default_version;
        const currentVersion = addon_data.current_version;

        if (!currentVersion || !defaultVersion) return false;

        const diffVersion = getDifferentVersion(currentVersion);
        const diffOptions = Object.values(diffVersion.options);

        Object.keys(currentVersion).forEach(fieldName => {
            if (diffVersion[fieldName] !== undefined) {
                $version.find(`.js-version-input[data-name="${fieldName}"]`).closest('.form-input').addClass('is-different');
            }
            else {
                $version.find(`.js-version-input[data-name="${fieldName}"]`).closest('.form-input').removeClass('is-different');
            }
        });

        Object.values(currentVersion.options).forEach((option, index) => {
            const optionId = option.id;
            const $option = $(`.js-option[data-option-id="${optionId}"]`);
            const diffOption = diffOptions[index];

            if (!diffOption || diffOption.status === 'unchanged') {
                $option.removeClass('is-different');
                $option.find('.is-different').removeClass('is-different');
            }
            else if (diffOption.status === 'add') {
                $option.addClass('is-different');
            }
            else {
                $option.removeClass('is-different');

                Object.keys(option).forEach(fieldName => {
                    const $input = $option.find(`.js-option-input[data-name="${fieldName}"]`);

                    if (!$input.length) return false;

                    if (diffOption[fieldName === 'image' ? 'image_url' : fieldName] !== undefined) {
                        $input.closest('.form-input').addClass('is-different');
                    }
                    else {
                        $input.closest('.form-input').removeClass('is-different');
                    }
                });
            }
        });
    }

    function getDifferentVersion(version) {
        const diff = {
            title: version.title,
            options: {},
        };

        version = JSON.parse(JSON.stringify(version));
        const options = Object.values(version.options);

        const defaultVersion = addon_data.default_version;

        if (!version || !defaultVersion) return false;

        // Check version fields differences
        const compareFields = ["is_hide","service_title","group_price","show_message","placeholder","description","enable_same_price","display_area","auto_select"];

        compareFields.forEach(fieldName => {
            if (version[fieldName] !== defaultVersion[fieldName]) diff[fieldName] = version[fieldName];
        });

        // Check image differences'
        const versionImages = (version.images || []).reduce((carry, image) => {
            carry[image.id || image.fileId] = image;
            return carry;
        }, {}) || {};

        const diffImages = [];

        (defaultVersion.images || []).forEach(defaultImage => {
            const versionImage = versionImages[defaultImage.id || defaultImage.fileId];
            const status = [];

            if (!versionImage) {
                return diffImages.push({
                    ...defaultImage,
                    status: ['Image Deleted'],
                });
            }

            versionImage.compared = 1;

            if (versionImage.url !== defaultImage.url) {
                status.push('Image URL Updated');
            }

            if (versionImage.position != defaultImage.position) {
                status.push('Position Updated');
            }

            if (status.length) {
                diffImages.push({
                    ...versionImage,
                    status,
                });
            }
        });

        Object.values(versionImages).forEach(image => {
            if (!image.compared) {
                diffImages.push({
                    ...image,
                    status: ['Image Added'],
                });
            }
        });

        if (diffImages.length) {
            diff.images = diffImages;
        }

        // Check video differences
        const versionVideos = (version.videos || []).reduce((carry, video) => {
            carry[video.id || video.fileId] = video;
            return carry;
        }, {}) || {};

        const diffVideos = [];

        (defaultVersion.videos || []).forEach(defaultVideo => {
            const versionVideo = versionVideos[defaultVideo.id || defaultVideo.fileId];
            const status = [];

            if (!versionVideo) {
                return diffVideos.push({
                    ...defaultVideo,
                    status: ['Video Deleted'],
                });
            }

            versionVideo.compared = 1;

            if (versionVideo.url !== defaultVideo.url) {
                status.push('Video URL Updated');
            }

            if (versionVideo.thumbnail !== defaultVideo.thumbnail) {
                status.push('Thumbnail Updated');
            }

            if (versionVideo.position !== defaultVideo.position) {
                status.push('Position Updated');
            }

            if (status.length) {
                diffVideos.push({
                    ...versionVideo,
                    status,
                });
            }
        });

        Object.values(versionVideos).forEach(video => {
            if (!video.compared) {
                diffVideos.push({
                    ...video,
                    status: ['Video Added'],
                });
            }
        });

        if (diffVideos.length) {
            diff.videos = diffVideos;
        }

        // Check option differences
        let counter = 0;

        Object.values(defaultVersion.options).forEach(defaultOption => {
            const option = options[counter];

            if (!diff.options) diff.options = [];

            if (!option) {
                diff.options[defaultOption.id] = {
                    ...defaultOption,
                    status: 'delete',
                }
            }
            else {
                let diffOption = {};

                ['title', 'price', 'image', 'image_url', 'is_default'].forEach(fieldName => {
                    if (option[fieldName] !== defaultOption[fieldName]) {
                        diffOption[fieldName] = option[fieldName];
                    }
                });

                if (Object.keys(diffOption).length) {
                    diff.options[option.id] = {
                        ...diffOption,
                        status: 'changed',
                    };
                }
                else {
                    diff.options[option.id] = {
                        status: 'unchanged',
                    };
                }
            }

            counter++;
        });

        options.slice(counter).forEach(option => {
            diff.options[option.id] = {
                ...option,
                status: 'add',
            }
        });

        return diff;
    }

    function deleteVersion() {
        const $version = $('.js-version');
        const versionId = $version.data('version-id');

        if (!versionId) return false;

        if (addon_data.versions[versionId]?.is_default_version) {
            alert('Can not delete default version');
            return false;
        }

        delete addon_data.versions[versionId];
        delete errors[versionId];

        $version.remove();
        $(`.js-version-tab[data-version-id="${versionId}"]`).remove();

        $versionNav.find('.js-version-tab').trigger('click');
    }

    function toggleFields() {
        if (!config.field) return;

        $('.field.hidden').removeClass('hidden').find('input:disabled, textarea:disabled, select:disabled').attr('disabled', false);
        $(`.field:not(.${config.field})`).addClass('hidden').find('input, textarea, select').attr('disabled', true);
    }

    function toggleVersionFields() {
        const $version = $('.js-version');

        let disabled = !config.enableAbTest;

        if (addon_data.current_version?.is_default_version) {
            disabled = false;
        }

        $version.find('input, textarea, select, .js-version-delete, .js-option-add, .js-option-delete').attr('disabled', disabled);
    }

    function removeOtherFields($el) {
        if (config.field) {
            $el.find(`.field:not(.${config.field})`).remove();
        }
    }

    function addOption(option = {}) {
        let optionTemplate = $('#option-template').html();

        const defaultOption = {
            id: $.makeUniqid(),
            title: '',
            price: '',
            placeholder: '',
            description: '',
            image: '',
            image_url: '',
            is_default: 0,
        }

        option = { ...defaultOption, ...option };

        const $version = $('.js-version');
        const $list = $version.find('.js-option-list');

        const versionId = $version.data('version-id');
        const version = addon_data.versions[versionId];

        if (version.enable_same_price) {
            option.price = version.group_price;
        }

        if (!version.options[option.id]) {
            version.options[option.id] = option;
        }

        Object.keys(option).forEach(key => {
            optionTemplate = optionTemplate.replaceAll(`{{${key}}}`, option[key])
        });

        const $option = $list.append(optionTemplate).children(':last-child');

        if (option.is_default) {
            $option.find('.js-option-set-default').trigger('click');
        }

        if (version.enable_same_price) {
            $option.find('.js-option-input[data-name="price"]').closest('.form-input').addClass('hidden');
        }

        updateOptionList();
        removeOtherFields($option);
        insertCallbackFunction($option);
        compareCurrentWithDefault();
    }

    function deleteOption(versionId, optionId) {
        delete addon_data.versions[versionId]?.options[optionId];

        $(`.js-option[data-option-id="${optionId}"]`).remove();

        updateOptionList();
    }

    function updateOptionList() {
        $('.js-option').each(function(index) {
            $(this).find('.form-option-label').html(config.label + ' ' + ('0' + (index + 1)).slice(-2));
        });

        if (Object.values(addon_data.current_version?.options || {}).findIndex(option => option.is_default) < 0) {
            const $firstOption = $('.js-option').eq(0);
            const optionId = $firstOption.data('option-id');

            for (const [, option] of Object.entries(addon_data.current_version.options)) {
                if (option.id === optionId) {
                    option.is_default = 1;
                }
            }

            $firstOption.find('.js-option-set-default').trigger('click');
        }
    }

    function validateAddon() {
        try {
            const abTestEnable = parseInt($('#ab-test-enable').val());
            const addonProductType = $('input[name="auto_apply_for_product_type_variants"]').val();

            let activeTime = $('.js-active-time').val();

            if (abTestEnable && addonProductType && activeTime) {
                let abTestTime = $('#ab_test_time').val();

                if (!abTestTime) {
                    throw new Error('A/B Test Time is required');
                }

                abTestTime = abTestTime.split(' - ').map(dateString => {
                    const [day, month, year] = dateString.split('/');
                    return new Date(`${year}-${month}-${day}`).getTime();
                });

                activeTime = activeTime.split(' - ').map(dateString => {
                    const [day, month, year] = dateString.split('/');
                    return new Date(`${year}-${month}-${day}`).getTime();
                });

                if (abTestTime[0] < activeTime[0] || abTestTime[1] > activeTime[1]) {
                    throw new Error('A/B Test Time must be inside Active Time');
                }
            }

            if (config.enableAbTest && Object.keys(addon_data.versions).length < 2) {
                throw new Error('A/B Test require at least 2 versions');
            }

            Object.values(addon_data.versions).forEach(version => validateVersion(version));
        } catch (error) {
            alert(error.message);
            return false;
        }

        return !Object.keys(errors).length;
    }

    function validateVersion(version, fieldName) {
        if (!version) return true;

        const versionId = version.id;

        // variant ko có group price
        let requiredFields = ['service_title', 'group_price'];

        requiredFields = fieldName
            ? requiredFields.includes(fieldName)
                ? [fieldName]
                : []
            : requiredFields;

        if (fieldName === 'enable_same_price') {
            validateVersion(version, 'group_price');

            Object.values(version.options).forEach(option => {
                validateOption(version.id, option.id, 'price');
            });
        }

        requiredFields.forEach(fieldName => {
            if (
                (version.enable_same_price || fieldName !== 'group_price') &&
                !version[fieldName] &&
                version[fieldName] !== 0
            ) {
                if (!errors[versionId]) errors[versionId] = {};
                errors[versionId][fieldName] = 'Please fill out this field.';
            }
            else {
                if (errors[versionId]?.[fieldName]) delete errors[versionId][fieldName];
                if (!Object.keys(errors[versionId] || {}).length) delete errors[versionId];
            }

            if (versionId !== addon_data.current_version?.id) return;

            const $version = $('.js-version');

            if (errors[versionId]?.[fieldName]) {
                $version.find(`.js-version-input[data-name="${fieldName}"]`)
                    .closest('.form-input').addClass('invalid-input')
                    .find('.form-input-error').text(errors[versionId][fieldName]);
            }
            else {
                $version.find(`.js-version-input[data-name="${fieldName}"]`)
                    .closest('.form-input').removeClass('invalid-input')
                    .find('.form-input-error').text('');
            }
        });

        if (!fieldName) {
            validateMockupData(versionId);

            Object.values(version.options).forEach(option => validateOption(versionId, option.id));
        }

        if (errors[versionId]) {
            $(`.js-version-tab[data-version-id="${versionId}"]`).addClass('has-error');
        }
        else {
            $(`.js-version-tab[data-version-id="${versionId}"]`).removeClass('has-error');
        }
    }

    function validateMockupData(versionId) {
        if (versionId !== addon_data.current_version.id) return false;

        const version = addon_data.versions[versionId];

        if (typeof window.__videoUploader__getData === 'function') {
            let videos = window.__videoUploader__getData();

            if (videos) {
                let missingThumbnail = false;

                videos = Object.entries(videos).map(([video_id, video]) => {
                    if (!video.thumbnail) {
                        missingThumbnail = true;
                    }
                    return {
                        id: video_id,
                        fileId: video.fileId,
                        url: video.url,
                        thumbnail: video.thumbnail,
                        position: Number(video.position) || 0,
                        duration: Number(video.duration) || 0,
                    }
                });

                if (missingThumbnail) {
                    $('.js-video-uploader').closest('.form-input').addClass('invalid-input').find('.form-input-error').text('Thumbnails must be uploaded for all videos.');
                }
                else {
                    $('.js-video-uploader').closest('.form-input').removeClass('invalid-input').find('.form-input-error').text('');
                }

                version.videos = videos;
            }
            else {
                $('.js-video-uploader').closest('.form-input').removeClass('invalid-input').find('.form-input-error').text('');
                version.videos = [];
            }
        }

        if (typeof window.__imageUploader__getData === 'function') {
            let images = window.__imageUploader__getData();

            if (images) {
                version.images = Object.values(images);
            }
            else {
                version.images = [];
            }
        }
    }

    function validateOption(versionId, optionId, fieldName) {
        const option = addon_data.versions[versionId]?.options[optionId];

        if (!option) return true;

        let requiredFields = ['title', 'price'];

        if (config.requireOptionImage) {
            requiredFields.push('image');
        }

        requiredFields = fieldName
            ? requiredFields.includes(fieldName)
                ? [fieldName]
                : []
            : requiredFields;

        requiredFields.forEach(fieldName => {
            if (!errors[versionId]) errors[versionId] = {};
            if (!errors[versionId]?.options) errors[versionId].options = {};
            if (!errors[versionId]?.options?.[optionId]) errors[versionId].options[optionId] = {};

            if (
                (fieldName !== 'price' || !addon_data.versions[versionId].enable_same_price) &&
                !option[fieldName] && option[fieldName] !== 0
            ) {
                errors[versionId].options[optionId][fieldName] = 'Please fill out this field';
            }
            else if (
                fieldName === 'price' &&
                addon_data.min_option_price &&
                option.price < addon_data.min_option_price
            ) {
                errors[versionId].options[optionId][fieldName] = addon_data.min_option_price_message;
            }
            else {
                if (errors[versionId]?.options?.[optionId]?.[fieldName]) delete errors[versionId].options[optionId][fieldName];
            }

            if (errors[versionId]?.options?.[optionId] && !Object.keys(errors[versionId].options[optionId]).length) delete errors[versionId].options[optionId];
            if (errors[versionId]?.options && !Object.keys(errors[versionId].options).length) delete errors[versionId].options;
            if (errors[versionId] && !Object.keys(errors[versionId]).length) delete errors[versionId];

            if (versionId !== addon_data.current_version?.id) return;

            const $option = $(`.js-option[data-option-id="${option.id}"]`);

            if (errors[versionId]?.options?.[optionId]?.[fieldName]) {
                $option.find(`.js-option-input[data-name="${fieldName}"]`)
                    .closest('.form-input').addClass('invalid-input')
                    .find('.form-input-error').text(errors[versionId].options[optionId][fieldName]);
            }
            else {
                $option.find(`.js-option-input[data-name="${fieldName}"]`)
                    .closest('.form-input').removeClass('invalid-input')
                    .find('.form-input-error').text('');
            }
        });

        if (errors[versionId]) {
            $(`.js-version-tab[data-version-id="${versionId}"]`).addClass('has-error');
        }
        else {
            $(`.js-version-tab[data-version-id="${versionId}"]`).removeClass('has-error');
        }
    }

    function insertCallbackFunction($component) {
        $component.find('[data-insert-cb]').each(function () {
            const cb = $(this).data('insert-cb');
            if (typeof window[cb] === 'function') {
                window[cb].bind(this);
            }
        });
    }

    function checkViewMode() {
        if (!config.viewMode) return false;

        $form.find('input, textarea, select').prop('readonly', true);
        $form.on('click', 'input[type="checkbox"],input[type="radio"]',function(e) {
            e.preventDefault();
            return false;
        });
    }
})(jQuery);

$(function() {
    const $nav = $('.addon-tabs__nav');
    const $list = $('.addon-tabs__list');

    $nav
        .on('update', function() {
            if ($nav.width() < $list.width()) {
                $nav.addClass('is-overflow');
            } else {
                $nav.removeClass('is-overflow');
                $list.css('left', 0);
            }
        })
        .trigger('update');

    $('.addon-tabs__prev').on('click', () => scrollAddonTabsNav(-2));
    $('.addon-tabs__next').on('click', () => scrollAddonTabsNav(2));

    function scrollAddonTabsNav(scrollStep = 0) {
        const navOffsetLeft = $nav.offset().left;
        const listOffsetLeft = $list.offset().left;

        const $btns = $list.find('.addon-tabs__btn');
        const btnCount = $btns.length;

        $list.find('.addon-tabs__btn').each(function(index) {
            const btnOffsetLeft = $(this).offset().left;

            if (btnOffsetLeft - navOffsetLeft >= 0) {
                index = index + scrollStep;

                if (index < 0) index = 0;
                else if (index > btnCount) index = btnCount;

                const targetOffsetLeft = $btns.eq(index)?.offset().left || 0;

                $list.css('left', listOffsetLeft - targetOffsetLeft);

                return false;
            }
        });
    }
});

window.initAddonImageUploader = function () {
    var container = $(this);
    var image_url = container.attr('data-image');
    var input_name = container.attr('data-input');
    var input_value = container.attr('data-value');
    var preview = $('<div />').addClass('preview').appendTo(container);
    var uploader_container = $('<div />').addClass('btn btn-outline p0 block').css('width', '100%').appendTo(container);
    var input_hidden = $('<input />').css({
        height: 0,
    }).attr({
        type: 'text',
        name: input_name,
        value: input_value,
        required: container.closest('.field').hasClass('field-group'),
    }).appendTo(
        $('<div>').css({
            height: 0,
            overflow: 'hidden',
        }).appendTo(container)
    );

    var __renderPreview = function (image_url, input_value) {
        preview.empty().addClass('show');
        $('<div>').addClass('thumbnail').css('background-image', 'url(' + image_url + ')').appendTo(preview);
        $('<div>').addClass('pr20').text(input_value).appendTo(preview);
        input_hidden.val(input_value);
        $('<a>')
            .addClass('btn btn-small btn-icon')
            .on('click', function (e) {
                e.preventDefault();
                preview.removeAttr('file-id')
                    .removeAttr('data-uploader-step')
                    .empty()
                    .removeClass('show');
                input_hidden.get(0).value = '';

                __initUploader();
                container.trigger('uploader-update', []);
            })
            .appendTo(preview)
            .append($.renderIcon('trash-can'));

        __initUploader();
        container.trigger('uploader-update', [{ image_url, input_value }]);
    };

    var __initUploader = function () {
        uploader_container.find('.image-uploader').remove();

        var uploader = $('<div />').addClass('image-uploader').appendTo(uploader_container);

        uploader.osc_uploader({
            max_files: 1,
            process_url: container.attr('data-upload-url'),
            btn_content: 'Upload image',
            dragdrop_content: 'Drop here to upload',
            image_mode: true,
            xhrFields: {withCredentials: true},
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-OSC-Cross-Request': 'OK'
            }
        }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
            preview.addClass('show').attr('file-id', file_id).attr('data-uploader-step', 'queue');
            $('<div />').addClass('uploader-progress-bar').appendTo(preview).append($('<div />'));
            $('<div />').addClass('step').appendTo(preview);

            var reader = new FileReader();
            reader.onload = function (e) {
                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                var img = document.createElement('img');

                img.onload = function () {
                    var canvas = document.createElement('canvas');

                    var MAX_WIDTH = 400;
                    var MAX_HEIGHT = 400;

                    var width = img.width;
                    var height = img.height;

                    if (width > height) {
                        if (width > MAX_WIDTH) {
                            height *= MAX_WIDTH / width;
                            width = MAX_WIDTH;
                        }
                    } else {
                        if (height > MAX_HEIGHT) {
                            width *= MAX_HEIGHT / height;
                            height = MAX_HEIGHT;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;

                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function (blob) {
                        // preview.css('background-image', 'url(' + URL.createObjectURL(blob) + ')');
                    });
                };

                img.src = e.target.result;
            };

            reader.readAsDataURL(file);
        }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
            if (preview.attr('file-id') !== file_id) {
                return;
            }

            if (parseInt(uploaded_percent) === 100) {
                preview.attr('data-uploader-step', 'process');
            } else {
                preview.attr('data-uploader-step', 'upload');
                preview.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
            }

        }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
            if (preview.attr('file-id') !== file_id) {
                return;
            }

            eval('response = ' + response);

            preview.removeAttr('file-id');
            preview.removeAttr('data-uploader-step');
            preview.find('.step').remove();
            preview.find('.uploader-progress-bar').remove();

            if (response.result !== 'OK') {
                alert(response.message);

                __initUploader();

                return;
            }

            __renderPreview(response.data.url, response.data.file);
        }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
            if (preview.attr('file-id') !== file_id) {
                return;
            }

            __initUploader();

            preview.removeAttr('file-id');
            preview.removeAttr('data-uploader-step');
            preview.find('.step').remove();
            preview.find('.uploader-progress-bar').remove();
            alert('Upload failed, please try again.');
        });
    };

    if (image_url !== '') {
        __renderPreview(image_url, input_value);
    } else {
        __initUploader();
    }
};

window.initDateRangePicker = function() {
    const $input = $(this);
    const drops = $(this).data('drops') || 'auto';
    const options = {
        alwaysShowCalendars: true,
        autoUpdateInput: false,
        drops: drops,
    };

    const $html = $(`
        <div class="n-daterange">
            <div class="n-daterange__start">Start date</div>
            <div class="n-daterange__end">End date</div>
        </div>`)
        .insertAfter($input)
        .append($.renderIcon('calendar-alt'))
        .append($input);

    const initDates = $input.val().split(/\s*\-\s*/i); // format: '18/10/2022 - 29/11/2022'

    if (initDates[0] && initDates[1]) {
        options.startDate = moment(initDates[0], "DD/MM/YYYY");
        options.endDate = moment(initDates[1], "DD/MM/YYYY");

        $html.addClass('active');
        $html.find('.n-daterange__start').html(initDates[0]);
        $html.find('.n-daterange__end').html(initDates[1]);
    }

    $input
        .daterangepicker(options)
        .on('apply.daterangepicker', function (e, picker) {
            if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
                $html.removeClass('active');
                $html.find('.n-daterange__start').html('Start date');
                $html.find('.n-daterange__end').html('End date');

                $input.val('')
                return false;
            }

            const startDate = picker.startDate.format('DD/MM/YYYY');
            const endDate = picker.endDate.format('DD/MM/YYYY');
            const value = startDate + ' - ' + endDate;

            if (picker.endDate.isBefore(moment())) {
                alert('The end date cannot be earlier than the current date');
                e.preventDefault();
                return false;
            }

            $html.addClass('active');
            $html.find('.n-daterange__start').html(startDate);
            $html.find('.n-daterange__end').html(endDate);

            $input.val(value);
        });
}
