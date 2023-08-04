(function ($) {
    'use strict';

    function _productPostFrm__addCollection(id, title, auto_flag) {
        var item = $('<div />').addClass('product-collection').text(title).prependTo($('.product-collections'));

        if (!auto_flag) {
            $('<input />').attr({type: 'hidden', name: 'collection_ids[]', value: id}).appendTo(item);

            $('<ins />').click(function () {
                $(this).closest('.product-collection').trigger('remove');
            }).appendTo(item);

            item.attr('data-collection-id', id).bind('remove', function () {
                this.parentNode.removeChild(this);
            });
        } else {
            $('<span />').text('auto').appendTo(item);
        }

        return item;
    }

    window.productPostFrm__collectionCheck = function (item, collection) {
        item.find('.image').remove();

        var checker = $('<div />').addClass('product-collection-checker').prependTo(item);

        var collection_item = $('.product-collections').find('[data-collection-id="' + collection.id + '"]');

        if (collection_item[0]) {
            collection_item.unbind('.collectionUpdate').bind('remove.collectionUpdate', function () {
                checker.html('');
            });

            checker.append($.renderIcon('check-solid'));
        }
    };

    window.productPostFrm__collectionUpdate = function (item, collection) {
        var collection_item = $('.product-collections').find('[data-collection-id="' + collection.id + '"]');

        var checker = item.find('.product-collection-checker');

        if (collection_item[0]) {
            collection_item.trigger('remove');
        } else {
            collection_item = _productPostFrm__addCollection(collection.id, collection.title);

            collection_item.bind('remove.collectionUpdate', function () {
                checker.html('');
            });

            checker.html('').append($.renderIcon('check-solid'));
        }
    };

    window.productPostFrm__initCollections = function () {
        var collections = JSON.parse(this.getAttribute('data-collections'));

        collections.forEach(function (collection) {
            _productPostFrm__addCollection(collection.id, collection.title, collection.auto);
        });
    };

    function _productPostFrm_renderImageAltEditor(item, url, alt) {
        $.unwrapContent('productPostFrmImageAltEditor');

        var modal = $('<div />').addClass('osc-modal').width(550);

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html('Edit image alt text').appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('productPostFrmImageAltEditor');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

        var cell = $('<div />').appendTo(row).css('max-width', '100px');

        $('<div />').addClass('product-image-preview').css('background-image', url ? ('url(' + url + ')') : 'initial').appendTo(cell);

        $('<div />').addClass('separate').appendTo(row);

        cell = $('<div />').appendTo(row);

        $('<label />').attr('for', '').html('Image alt text').appendTo(cell);

        var alt_input = $('<input />').attr('type', 'text').addClass('styled-input').appendTo($('<div />').appendTo(cell)).val(alt);

        $('<div />').addClass('input-desc').text('Write a brief description of this image to improve search engine optimization (SEO) and accessibility for visually impaired customers.').appendTo(cell);

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-secondary').html('Close').click(function () {
            $.unwrapContent('productPostFrmImageAltEditor');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html('Update').click(function () {
            item.trigger('update', [{alt: alt_input.val()}]);
            $.unwrapContent('productPostFrmImageAltEditor');
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'productPostFrmImageAltEditor'});

        modal.moveToCenter().css('top', '100px');
    }

    function _productPostFrm_renderImage(image) {
        var data = {id: 0, url: '', alt: ''};

        var image_list = $('.product-images');

        var item = $('<div />').addClass('product-image').appendTo(image_list).bind('update', function (e, new_data) {
            if (new_data !== null && typeof new_data === 'object') {
                $.extend(data, new_data);
            }

            item.css('background-image', data.url ? ('url(' + data.url + ')') : 'initial');

            item.find('input[type="hidden"]').remove();

            if (data.id) {
                item.append($('<input />').attr({type: 'hidden', name: 'images[' + data.id + ']', value: data.alt}).attr('data-id', data.id));
            }
        });

        initItemReorder(item, '.product-images', '.product-image', 'product-post-frm-img-reorder-helper', function (helper) {
            helper.html('');
        });

        var control_bars = $('<div />').addClass('controls').appendTo(item);

        $($.renderIcon('pencil')).mousedown(function (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();

            _productPostFrm_renderImageAltEditor(item, data.url, data.alt);
        }).appendTo(control_bars);

        $($.renderIcon('trash-alt-regular')).mousedown(function (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();

            item.remove();
        }).appendTo(control_bars);

        item.trigger('update', [image]);

        return item;
    }

    window.productPostFrm__initImages = function () {
        var images = JSON.parse(this.getAttribute('data-images'));
        if (images.length > 0) {
            $.each(images, function (k, image) {
                _productPostFrm_renderImage({
                    id: image.id,
                    alt: image.alt,
                    url: image.url
                });
            });
        }
        var request_images = JSON.parse(this.getAttribute('data-request'));

        if (request_images.length > 0) {
            var image_list = $(this).closest('form').find('.product-images');

            $.each(request_images, function (file_id, data) {
                var item = $('<div />').addClass('product-image').appendTo(image_list);

                item.css('background-image', data.url ? ('url(' + data.url + ')') : 'initial');

                item.append($('<input />').attr({type: 'hidden', name: 'images[' + file_id + ']', value: data.alt}).attr('data-id', file_id));

                var control_bars = $('<div />').addClass('controls').appendTo(item);

                $($.renderIcon('pencil')).mousedown(function (e) {
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    _productPostFrm_renderImageAltEditor(item, data.url, data.alt);
                }).appendTo(control_bars);

                $($.renderIcon('trash-alt-regular')).mousedown(function (e) {
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    item.remove();
                }).appendTo(control_bars);
            });

        }
    };

    window.initProductImgUploader = function () {
        var image_list = $(this).closest('form').find('.product-images');
        var max_filesize = 10*1024*1024;

        $(this).osc_uploader({
            max_files: -1,
            max_connections: 5,
            process_url: this.getAttribute('data-process-url'),
            btn_content: 'Upload image',
            dragdrop_content: 'Drop here to upload',
            max_filesize: max_filesize,
            image_mode: true,
            xhrFields: {withCredentials: true},
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-OSC-Cross-Request': 'OK'
            }
        }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
            var item = _productPostFrm_renderImage().attr('file-id', file_id).attr('data-uploader-step', 'queue');

            $('<div />').addClass('uploader-progress-bar').appendTo(item).append($('<div />'));
            $('<div />').addClass('step').appendTo(item);

            var reader = new FileReader();
            reader.onload = function (e) {
                var item = image_list.find('> [file-id="' + file_id + '"]');

                if (!item[0]) {
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
                        item.trigger('update', [{url: URL.createObjectURL(blob)}]);
                    });
                };

                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
            var item = image_list.find('> [file-id="' + file_id + '"]');

            if (!item[0]) {
                return;
            }

            if (parseInt(uploaded_percent) === 100) {
                item.attr('data-uploader-step', 'process');
            } else {
                item.attr('data-uploader-step', 'upload');
                item.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
            }

        }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
            var item = image_list.find('> [file-id="' + file_id + '"]');

            if (!item[0]) {
                return;
            }

            eval('response = ' + response);

            if (response.result !== 'OK') {
                alert(response.message);
                item.remove();
                return;
            }

            item.trigger('update', [{id: response.data.file, url: response.data.url}]);

            item.removeAttr('file-id').removeAttr('data-uploader-step');

            item.find('.uploader-progress-bar').remove();
            item.find('.step').remove();
        }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
            if (error_code === 'maxSizeError' || file_id === 'sizeError') {
                alert('File size exceeds the maximum limit of 10MB, please upload a smaller file.');
            } else if (file_id === 'typeError') {
                alert('Sorry! The image file format you uploaded is not supported.');
            } else {
                alert('Upload error, please try again');
            }

            var item = image_list.find('> [file-id="' + file_id + '"]');
            if (!item[0]) {
                return;

            }
            item.remove();
        });
    };

    function _renderProductOptionEditor(item) {
        $.unwrapContent('productOptionEditor');

        var modal = $('<div />').addClass('osc-modal').width(400);

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html('Edit option').appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('productOptionEditor');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

        var cell = $('<div />').appendTo(row);

        $('<label />').attr('for', '').html('Option title').appendTo(cell);

        var title_input = $('<input />').attr('type', 'text').addClass('styled-input').appendTo($('<div />').appendTo(cell));

        row = $('<div />').addClass('frm-grid').appendTo(modal_body);

        cell = $('<div />').appendTo(row);

        $('<label />').attr('for', '').html('Option type').appendTo(cell);

        var type_selector = $('<select />');
        $('<div />').addClass('styled-select').append(type_selector).append($('<ins />')).appendTo(cell);

        $.each(PRODUCT_OPTION_TYPES, function (key, title) {
            $('<option />').attr({value: key}).text(title).appendTo(type_selector);
        });

        $('<div />').addClass('frm-heading__title').html('Option values').appendTo($('<div />').addClass('frm-heading__main').appendTo($('<div />').addClass('frm-heading mb10').appendTo(modal_body)));

        var value_list = $('<div />').addClass('product-option-value-list').appendTo(modal_body);

        row = $('<div />').addClass('frm-grid').appendTo(modal_body);

        cell = $('<div />').appendTo(row);

        $('<label />').attr('for', '').html('New option value').appendTo(cell);

        var _renderValue = function (option_value) {
            var item = $('<div />').addClass('option-value').attr('data-value', option_value).text(option_value).append($('<div />').addClass('icon')).append($('<div />').attr('data-skipdrag', 1).addClass('remove-btn').click(function () {
                this.parentNode.parentNode.removeChild(this.parentNode);
            })).appendTo(value_list);

            initItemReorder(item, '.product-option-value-list', '.option-value', 'product-option-value-reorder-helper');
        };

        $('<input />').attr('type', 'text').addClass('styled-input').appendTo($('<div />').appendTo(cell)).bind('keydown blur', function (e) {
            if (e.keyCode && e.keyCode !== 13 && e.keyCode !== 9 && e.keyCode !== 188) {
                return;
            }

            e.preventDefault();

            var option_value = $(this).val().replace(/ {2,}/g, ' ');

            if (!option_value) {
                return;
            }

            var matched = false;

            value_list.find('.option-value').each(function () {
                if (this.getAttribute('data-value') === option_value) {
                    matched = true;
                    return false;
                }
            });

            if (!matched) {
                _renderValue(option_value);
            } else {
                alert('You\'ve already used this option value');
            }
            $(this).val('');
        });

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-secondary').html('Close').click(function () {
            $.unwrapContent('productOptionEditor');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html('Update').click(function () {
            var values = [];
            if (value_list.find('.option-value').length < 1) {
                alert('Option value must not be empty');
                return;
            }
            value_list.find('.option-value').each(function () {
                values.push(this.getAttribute('data-value'));
            });

            var title = title_input.val().trim();

            if (_renderProductOption(item, title, values, type_selector.val()) !== false) {
                $.unwrapContent('productOptionEditor');
            }
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'productOptionEditor'});

        if (item) {
            title_input.val(item.attr('data-title'));

            type_selector.find('option[value="' + item.attr('data-type') + '"]').attr('selected', 'selected');

            item.find('[data-value]').each(function () {
                _renderValue(this.getAttribute('data-value'));
            });
        }

        modal.moveToCenter().css('top', '100px');
    }

    function getOptionList() {
        let option_list = $('.product-option-list'), options = {};

        for (var i = 1; i <= 3; i++) {
            var option = option_list.find('.product-option[data-index=\'' + i + '\']');

            options[i] = [];

            if (option[0]) {
                option.find('[data-value]').each(function () {
                    options[i].push(this.getAttribute('data-value'));
                });
            } else {
                options[i].push('');
            }

            if (options[i].length < 1) {
                alert('Option:' + option.attr('data-title') + ' must not be empty.');
                return;
            }
        }

        return options;
    }

    function updateProductVariantPosition(options = {}) {
        let variant_list = $('.product-variant-list'), position = 0, product_variant_position = [];

        options = Object.keys(options).length > 0 ? options : getOptionList();

        options[1].forEach(function (x) {
            options[2].forEach(function (y) {
                options[3].forEach(function (z) {
                    let product_variant = variant_list.find('.product-variant[data-option1="' + x.replaceAll('"', '\t&quot;') + '"][data-option2="' + y.replaceAll('"', '\t&quot;') + '"][data-option3="' + z.replaceAll('"', '\t&quot;') + '"]');

                    let variant_id = product_variant.attr('data-variant');

                    position++;

                    if (variant_id) {
                        product_variant_position.push({
                            'variant_id': variant_id,
                            'position': position,
                            'ele': product_variant,
                            'option1': x,
                            'option2': y,
                            'option3': z,
                        });
                    }
                });
            });
        });

        product_variant_position.sort((a, b) => parseFloat(a.position) - parseFloat(b.position));

        product_variant_position.forEach(function (variant, index) {
            variant.ele.find('input[name="variants[' + variant.variant_id + '][position]"]').val(variant.position);
            variant.ele.attr('data-position', variant.position);
            variant_list.append(variant.ele);

            variant.ele.trigger('update', [{
                position: variant.position
            }]);
        });
    }

    function _renderProductOption(option, title, values, type, option_index) {
        if (option) {
            option.html('');
            option_index = option.attr('data-index');

            $('.product-variant-list .product-variant').each(function () {
                if (values.indexOf($(this).attr('data-option' + option_index).replaceAll('\t&quot;', '"')) < 0) {
                    $(this).trigger('delete');
                }
            });
        } else {
            if (typeof option_index === 'undefined') {
                option_index = 0;

                $.each([1, 2, 3], function (k, v) {
                    if (!$('.product-option-list .product-option[data-index="' + v + '"]')[0]) {
                        option_index = v;
                        return false;
                    }
                });
            } else {
                option_index = parseInt(option_index);

                if (isNaN(option_index) || option_index > 3 || $('.product-option-list .product-option[data-index="' + option_index + '"]')[0]) {
                    return;
                }
            }

            if (option_index < 1) {
                return;
            }

            var variant_update_data = {};

            variant_update_data['option' + option_index] = values[0];

            $('.product-variant-list .product-variant').each(function () {
                $(this).trigger('update', [variant_update_data]);
            });

            option = $('<div />').attr('data-index', option_index).addClass('product-option').bind('reordered', function () {
                $('.product-option-list .product-option').each(function (idx) {
                    $(this).find('input[name="options[option' + this.getAttribute('data-index') + '][position]"').val(idx + 1);
                });
            });
        }

        if (typeof type !== 'string' && typeof type !== 'number') {
            type = '';
        }

        option.attr('data-title', title).attr('data-type', type);

        $('<input />').attr({type: 'hidden', name: 'options[option' + option_index + '][title]', value: title}).appendTo(option);
        $('<input />').attr({type: 'hidden', name: 'options[option' + option_index + '][type]', value: type}).appendTo(option);
        $('<input />').attr({type: 'hidden', name: 'options[option' + option_index + '][position]', value: 0}).appendTo(option);

        var title_container = $('<div />').addClass('title').text(title).append($('<div />').addClass('icon')).appendTo(option);

        var control_bars = $('<div />').addClass('control-bars').attr('data-skipdrag', 1).appendTo(title_container);

        $($.renderIcon('pencil')).click(function (e) {
            _renderProductOptionEditor(option);
        }).appendTo(control_bars);

        $($.renderIcon('trash-alt-regular')).click(function (e) {
            option.remove();
            _toggleVariantForm();
            $('#bulk_edit_variant').trigger('update');
            $('#bulk_delete_variant').trigger('update');
            var variant_update_data = {};

            variant_update_data['option' + option_index] = '';

            $('.product-variant-list .product-variant').each(function () {
                $(this).trigger('update', [variant_update_data]);
            });

            updateProductVariantPosition();
        }).appendTo(control_bars);

        var list = $('<ul />').appendTo(option);

        values.forEach(function (value) {
            var li = $('<li />').attr('data-value', value).text(value).appendTo(list);
            li.append($('<input />').attr({type: 'hidden', name: 'options[option' + option_index + '][values][]', value: value}));
        });

        if (!option.parent()[0]) {
            $('.product-option-list').append(option);
        }

        option.trigger('reordered');

        _toggleVariantForm();

        initItemReorder(option, '.product-option-list', '.product-option', 'product-option-reorder-helper');

        updateProductVariantPosition();
    }

    window.initProductAddOptionBtn = function () {
        $(this).click(function () {
            _renderProductOptionEditor();
        });
    };

    var PRODUCT_OPTION_TYPES = {};

    window.productPostFrm__initOptions = function () {
        var options = JSON.parse(this.getAttribute('data-options'));
        PRODUCT_OPTION_TYPES = JSON.parse(this.getAttribute('data-types'));

        if (options !== null) {
            $.each(options, function (option_key, option) {
                if (!option) {
                    return;
                }

                _renderProductOption(null, option.title, option.values, option.type, option_key.replace(/^.+[^\d](\d+)$/i, '$1'));
            });
        }
    };

    function _toggleVariantForm() {
        $('#make_variant_btn').trigger('update')
        $('#bulk_edit_variant').trigger('update');
        $('#bulk_delete_variant').trigger('update');
        var variant_list = $('.product-variant-list').closest('.block');
        var default_variant_frm = $('#postfrm-default-variant');

        if ($('.product-option-list .product-option')[0]) {
            variant_list.show();
            default_variant_frm.hide();

            _updateProductVariantHeaderV1();
        } else {
            variant_list.hide();
            default_variant_frm.show();
        }
    }

    window.initBulkEditVariant = function () {
        $(this).click(function () {
            var variants = catalogProductEditorGetSelectedVariants();

            if (variants.length > 0) {
                _renderVariantEditor(variants);
            }
        });
    };

    window.catalogProductEditorGetSelectedVariants = function () {
        var data = {variants: []};

        $('.product-variant-list .product-variant').each(function () {
            $(this).trigger('checkEdit', [data]);
        });

        return data.variants;
    };

    window.initBulkDeleteVariant = function () {
        $(this).click(function () {
            var data = {variants: []};

            $('.product-variant-list .product-variant').each(function () {
                $(this).trigger('checkEdit', [data]);
            });

            data.variants.forEach(function (variant) {
                variant.item.trigger('delete');
            });
        });
    };

    function _getProductImageList(image_id) {
        var images = [];

        if (image_id) {
            image_id += '';
        }

        $('.product-images .product-image').each(function () {
            var id = $(this).find('input[type="hidden"]').attr('data-id');

            if (!id || (image_id && id !== image_id)) {
                return;
            }

            images.push({
                id: id,
                url: $(this).css('background-image').replace(/^\s*url\s*\(\s*["']?/i, '').replace(/['"]?\s*\)\s*$/i, '')
            });

            if (id === image_id) {
                return false;
            }
        });

        return image_id ? (images.length === 1 ? images[0] : null) : images;
    }

    function _renderProductVariantImgSelector(variant) {
        let images = _getProductImageList();
        let videos = window.__videoUploader__getData();

        videos = Object.entries(typeof videos === 'object' ? videos : {}).map(([videoId, video]) => ({ ...video, id: videoId }));

        if (images.length < 1 && videos.length < 1) {
            return;
        }

        $.unwrapContent('productVariantImgSelector');

        var modal = $('<div />').addClass('osc-modal').width(600);

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html('Image Selector').appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('productVariantImgSelector');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        var selected_ids = variant.data.image_id !== '' ? variant.data.image_id.split(',') : [];
        var selected_video_ids = variant.data?.video_id
            ? Array.isArray(variant.data?.video_id)
                ? variant.data?.video_id
                : variant.data?.video_id?.split(',')
            : [];
        var selected_video_positions = variant.data?.video_position
            ? Array.isArray(variant.data?.video_position)
                ? variant.data?.video_position
                : variant.data?.video_position?.split(',')
            : [];

        var image_ids = images.map(function (image) {
            return image.id
        });

        selected_ids = selected_ids.filter(selected_id => image_ids.indexOf(selected_id) !== -1)

        var selector_list = $('<div />').addClass('product-image-selector').bind('update', function () {
            selector_list.find('.selector-item').removeClass('selected').removeAttr('data-position').each(function (i) {
                if (selected_ids.indexOf(this.getAttribute('data-image')) >= 0) {
                    $(this).addClass('selected').attr('data-position', selected_ids.indexOf(this.getAttribute('data-image')) + 1);
                }

                let videoId = $(this).data('video-id');
                let videoPosition = selected_video_ids.indexOf(videoId + '') + 1;

                if (videoPosition > 0) {
                    $(this).addClass('selected').attr('data-position', videoPosition);
                    $(this).parent().find('input[name="video_position"]').show().val(selected_video_positions[selected_video_ids.indexOf(videoId + '')] || '');
                }
            });
        }).appendTo(modal_body);

        $('<div class="product-image-selector-title">Select images to variant(s) <span class="js-unselect-all-image">Unselect all images</span></div>')
            .on('click', function(e) {
                e.stopPropagation();
                selected_ids = [];
                selector_list.trigger('update');
            })
            .appendTo(selector_list);

        images.forEach(function (image) {
            $('<div />').attr('data-image', image.id).addClass('selector-item').css('background-image', 'url("' + image.url + '")').appendTo($('<div />').addClass('selector-item-wrap').appendTo(selector_list)).click(function () {
                var image_id = image.id + '';

                if (selected_ids.indexOf(image_id) >= 0) {
                    selected_ids.splice(selected_ids.indexOf(image_id), 1);
                } else {
                    selected_ids.push(image_id);
                }

                selector_list.trigger('update');
            });
        });

        if (videos.length) {
            $('<div class="product-image-selector-title">Select video to variant(s)</div>').appendTo(selector_list);
        }

        videos.forEach(function(video) {
            const videoId = video.id;

            $(`<div class="selector-item selector-video" data-video-id="${videoId}" >
                <video src="${video.url}" poster="${video.thumbnail ||''}"></video>
                <span class="selector-video-icon"></span>
            </div>`)
                .appendTo(
                    $('<div />')
                        .addClass('selector-item-wrap')
                        .appendTo(selector_list))
                .after(
                    $('<input />')
                        .addClass('styled-input mt5')
                        .attr({
                            'type': 'text',
                            'name': 'video_position',
                            'data-video-id': `${video.id}`,
                            'placeholder': 'Position...',
                        })
                        .css({
                            height: '18px',
                            display: 'none'
                        })
                )
                .click(function () {
                        const position_input = $(this).parent().find('input[name="video_position"]');
                        const indexOfVideo = selected_video_ids.indexOf(videoId);
                        if (indexOfVideo >= 0) {
                            selected_video_ids = selected_video_ids.filter(id => id !== videoId);
                            selected_video_positions.splice(indexOfVideo, 1);
                            position_input.hide();
                        } else {
                            selected_video_ids.push(videoId + '');
                            position_input.show();
                        }

                        selector_list.trigger('update');
                    }
                );
        });

        selector_list.trigger('update');

        var selected_variants = catalogProductEditorGetSelectedVariants();

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-secondary').html('Close').click(function () {
            $.unwrapContent('productVariantImgSelector');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html('Save update').click(function () {
            const elem_position = $('.product-image-selector input[name="video_position"]');
            for (let i = 0; i < elem_position.length; i++) {
                const elem = $(elem_position[i]);
                selected_video_positions[selected_video_ids.indexOf(elem.attr('data-video-id'))] = elem.val();
            }

            for (const position of selected_video_positions) {
                if (!position || Number(position) != position) {
                    alert('Position video is invalid !');
                    return;
                }
            }
            $.unwrapContent('productVariantImgSelector');

            variant.item.trigger('update', [{
                image_id: selected_ids.join(','),
                video_id: selected_video_ids,
                video_position: selected_video_positions
            }]);

            if (selected_variants.length > 1) {
                selected_variants.forEach(function (variant) {
                    variant.item.trigger('update', [{
                        image_id: selected_ids.join(','),
                        video_id: selected_video_ids,
                        video_position: selected_video_positions
                    }]);
                });
            }
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'productVariantImgSelector'});

        modal.moveToCenter().css('top', '100px');
    }

    function _renderProductVariant(_data) {
        var data = {
            id: 'new.' + $.makeUniqid(),
            option1: '',
            option2: '',
            option3: '',
            shipping_price: 0,
            shipping_plus_price: 0,
            image_id: '',
            price: $('#input-price').val(),
            compare_at_price: $('#input-compare_at_price').val(),
            cost: $('#input-cost').val(),
            sku: '',
            track_quantity: parseInt($('#input-track_quantity').val()) === 1 ? 1 : 0,
            quantity: $('#input-quantity').val(),
            overselling: $('#input-overselling')[0].checked ? 1 : 0,
            weight: $('#input-weight').val(),
            weight_unit: $('#input-weight_unit').val(),
            require_shipping: $('#input-require_shipping')[0].checked ? 1 : 0,
            keep_flat: $('#input-keep_flat')[0].checked ? 1 : 0,
            require_packing: $('#input-require_packing')[0].checked ? 1 : 0,
            dimension_width: $('#input-dimension_width').val(),
            dimension_height: $('#input-dimension_height').val(),
            dimension_length: $('#input-dimension_length').val()
        };

        if (Array.isArray(_data.image_id)) {
            _data.image_id = _data.image_id.join(',');
        }

        $.extend(data, _data);

        data.id += '';

        var default_data = data.id.indexOf('new.') === 0 ? null : {id: data.id, option1: data.option1.replaceAll('"', '\t&quot;'), option2: data.option2.replaceAll('"', '\t&quot;'), option3: data.option3.replaceAll('"', '\t&quot;')};

        var variant_list = $('.product-variant-list');

        if (variant_list.find('.product-variant[data-option1="' + data.option1.replaceAll('"', '\t&quot;') + '"][data-option2="' + data.option2.replaceAll('"', '\t&quot;') + '"][data-option3="' + data.option3.replaceAll('"', '\t&quot;') + '"]')[0]) {
            return;
        }

        var variant_item = $('<div />').addClass('product-variant').attr('data-variant', data.id).appendTo(variant_list);

        variant_item.bind('update', function (e, _data) {
            if (_data !== null && typeof _data === 'object') {
                $.extend(data, _data);

                if (default_data !== null) {
                    if (data.option1.replaceAll('"', '\t&quot;') !== default_data.option1.replaceAll('"', '\t&quot;') || data.option2.replaceAll('"', '\t&quot;') !== default_data.option2.replaceAll('"', '\t&quot;') || data.option3.replaceAll('"', '\t&quot;') !== default_data.option3.replaceAll('"', '\t&quot;')) {
                        data.id = 'new.' + $.makeUniqid();
                    } else {
                        data.id = default_data.id;
                    }
                }
            }

            if (data.option1 === '' && data.option2 === '' && data.option3 === '') {
                variant_item.trigger('delete');
            } else {
                variant_item.find('input[type="hidden"]').remove();

                for (var k in data) {
                    if (k === 'id') {
                        continue;
                    }

                    $('<input />').attr({type: 'hidden', name: 'variants[' + data.id + '][' + k + ']', value: data[k]}).appendTo(variant_item);
                }

                if (typeof data.image_id === 'number') {
                    data.image_id = data.image_id + '';
                } else if (typeof data.image_id !== 'string') {
                    data.image_id = '';
                }

                var image = _getProductImageList(data.image_id.split(',')[0]);

                if (!image) {
                    data.image_id = '';
                    variant_item.find('.variant-image div').css('background-image', '');
                } else {
                    variant_item.find('.variant-image div').css('background-image', 'url("' + image.url + '")');
                }

                variant_item.find('.variant-sku span').text(data.sku);
                variant_item.find('.variant-price span').text(data.price);

                variant_item.find('.variant-option').remove();

                for (var i = 1; i <= 3; i++) {
                    variant_item.attr('data-option' + i, data['option' + i].replaceAll('"', '\t&quot;'));

                    if (data['option' + i] !== '') {
                        $('<div />').addClass('variant-option option' + i).append($('<span />').addClass('text-wrap').append($('<span />').attr('data-vidx', i).text(data['option' + i]).click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();

                            var option_idx = this.getAttribute('data-vidx');

                            var option_value = data['option' + option_idx];

                            var collection = variant_list.find('.product-variant[data-option' + option_idx + '="' + option_value + '"]');

                            var checked = false;

                            collection.each(function () {
                                if (!$(this).find('input[type="checkbox"]')[0].checked) {
                                    checked = true;
                                    return false;
                                }
                            });

                            variant_list.find('.product-variant').removeClass('selected').each(function () {
                                $(this).find('input[type="checkbox"]')[0].checked = false;
                            });

                            if (checked) {
                                collection.addClass('selected').each(function () {
                                    $(this).find('input[type="checkbox"]')[0].checked = true;
                                });
                            }
                        }))).insertBefore(variant_item.find('.variant-sku'));
                    }
                }

                if (variant_list.find('.product-variant[data-option1="' + data.option1.replaceAll('"', '\t&quot;') + '"][data-option2="' + data.option2.replaceAll('"', '\t&quot;') + '"][data-option3="' + data.option3.replaceAll('"', '\t&quot;') + '"]')[0] !== variant_item[0]) {
                    variant_item.trigger('delete');
                }
            }

            _updateProductVariantHeader();
        }).bind('delete', function () {
            variant_item.remove();
            _updateProductVariantHeader();
        }).bind('checkEdit', function (e, _data) {
            if (checkbox[0].checked) {
                _data.variants.push({item: variant_item, data: data});
            }
        });

        var checkbox = $('<input />').attr({type: 'checkbox'});

        checkbox.click(function (e) {
            e.stopPropagation();
            checkbox.trigger('update', [e.shiftKey]);
        }).bind('update', function (e, shift_key) {
            if (this.checked) {
                variant_item.addClass('selected');
            } else {
                variant_item.removeClass('selected');
            }

            if (!this.checked) {
                variant_list.removeAttr('data-last-click');
                return;
            }

            if (shift_key) {
                var last_click_id = variant_list.attr('data-last-click');

                if (last_click_id && last_click_id !== data.id) {
                    var last_click_variant_item = variant_list.find('.product-variant[data-variant="' + last_click_id + '"]');

                    if (last_click_variant_item[0]) {
                        var selector = (last_click_variant_item[0].compareDocumentPosition(variant_item[0]) & Node.DOCUMENT_POSITION_FOLLOWING) ? 'prev' : 'next';

                        var _variant_item = variant_item[selector]();

                        while (_variant_item.attr('data-variant') !== last_click_id) {
                            _variant_item.addClass('selected').find('input[type="checkbox"]')[0].checked = true;

                            var _variant_item = _variant_item[selector]();
                        }

                        return;
                    }
                }
            }

            variant_list.attr('data-last-click', data.id);
        });

        setCheckboxSelectAll(checkbox[0], 'variant');

        $('<div />').addClass('styled-checkbox').append(checkbox).append($('<ins />').append($.renderIcon('check-solid'))).appendTo($('<div />').addClass('variant-selector').appendTo(variant_item));
        $('<div />').appendTo($('<div />').addClass('variant-image').appendTo(variant_item)).click(function (e) {
            e.stopPropagation();
            _renderProductVariantImgSelector({item: variant_item, data: data});
        });

        $('<div />').addClass('variant-sku').append($('<span />').addClass('text-wrap').text(data.sku)).appendTo(variant_item);
        $('<div />').addClass('variant-price').append($('<span />').addClass('text-wrap').text(data.price)).appendTo(variant_item);
        var control_bars = $('<div />').addClass('variant-actions').appendTo(variant_item);

        $('<div />').addClass('btn btn-small btn-icon').append($.renderIcon('pencil')).appendTo(control_bars).click(function (e) {
            e.stopImmediatePropagation();
            _renderVariantEditor([{item: variant_item, data: data}]);
        });
        $('<div />').addClass('btn btn-small btn-icon ml5').append($.renderIcon('trash-alt-regular')).appendTo(control_bars).click(function (e) {
            e.stopImmediatePropagation();
            variant_item.trigger('delete');
        });

        variant_item.click(function (e) {
            checkbox[0].checked = !checkbox[0].checked;
            checkbox.trigger('update', [e.shiftKey]);
        });

        variant_item.trigger('update', [{}]);
    }

    function _updateProductVariantHeader() {
        var variant_list = $('.product-variant-list');

        if (!variant_list.find('.product-variant')[0]) {
            variant_list.html('');
            return;
        }

        var header = variant_list.find('.product-variant-header');

        if (!header[0]) {
            header = $('<div />').addClass('product-variant-header').prependTo(variant_list);
        } else {
            header.html('');
        }

        var checkbox = $('<input />').attr({type: 'checkbox'});

        setCheckboxSelectAll(checkbox[0], 'variant', true, function (checked) {
            variant_list.find('> .product-variant').removeClass('selected').each(function () {
                if ($(this).find('> .variant-selector input')[0].checked) {
                    $(this).addClass('selected');
                }
            });
        });

        $('<div />').addClass('styled-checkbox').append(checkbox).append($('<ins />').append($.renderIcon('check-solid'))).appendTo($('<div />').addClass('variant-selector').appendTo(header));
        $('<div />').addClass('variant-image').html('&nbsp;').appendTo(header);

        var option_list = $('.product-option-list');

        for (var i = 1; i <= 3; i++) {
            var option = option_list.find('.product-option[data-index="' + i + '"]');

            if (!option[0]) {
                continue;
            }

            $('<div />').addClass('variant-option').html(option.attr('data-title')).appendTo(header);
        }

        $('<div />').addClass('variant-sku').html('SKU').appendTo(header);
        $('<div />').addClass('variant-price').html('Price').appendTo(header);
        $('<div />').addClass('variant-actions').html('&nbsp;').appendTo(header);
    }

    window.productPostFrm__initOptionsV1 = function () {
        var options = JSON.parse(this.getAttribute('data-options'));
        PRODUCT_OPTION_TYPES = JSON.parse(this.getAttribute('data-types'));

        if (options !== null) {
            $.each(options, function (option_key, option) {
                if (!option) {
                    return;
                }

                _renderProductOption(null, option.title, option.values, option.type, option_key.replace(/^.+[^\d](\d+)$/i, '$1'));
            });
        }
    };

    window.initProductAddOptionBtnV1 = function () {
        $(this).click(function () {
            $('.product-option-list .product-option').length;
            if ($('.product-option-list .product-option').length >= 3) {
                alert('No more than 3 options are allowed.');
                return;
            }

            _renderProductOptionEditor();
        });
    };

    function _updateProductVariantHeaderV1() {
        var variant_list = $('.product-variant-list');

        if (!variant_list.find('.product-variant')[0]) {
            variant_list.html('');
            return;
        }

        var header = variant_list.find('.product-variant-header');

        if (!header[0]) {
            header = $('<div />').addClass('product-variant-header').prependTo(variant_list);
        } else {
            header.html('');
        }

        var checkbox = $('<input />').attr({type: 'checkbox'});

        setCheckboxSelectAll(checkbox[0], 'variant', true, function (checked) {
            $('#bulk_edit_variant').trigger('update');
            $('#bulk_delete_variant').trigger('update');
            variant_list.find('> .product-variant').removeClass('selected').each(function () {
                if ($(this).find('> .variant-selector input')[0].checked) {
                    $(this).addClass('selected');
                }
            });
        });

        $('<div />').addClass('styled-checkbox').append(checkbox).append($('<ins />').append($.renderIcon('check-solid'))).appendTo($('<div />').addClass('variant-selector').appendTo(header));
        $('<div />').addClass('variant-image').html('&nbsp;').appendTo(header);

        var option_list = $('.product-option-list');

        for (var i = 1; i <= 3; i++) {
            var option = option_list.find('.product-option[data-index="' + i + '"]');

            if (!option[0]) {
                continue;
            }

            $('<div />').addClass('variant-option').html(option.attr('data-title')).appendTo(header);
        }

        $('<div />').addClass('variant-price').html('Price').appendTo(header);
        $('<div />').addClass('variant-compare-at-price').html('Compare Price').appendTo(header);
        $('<div />').addClass('variant-actions').html('&nbsp;').appendTo(header);
    }

    window.initBulkEditVariantV1 = function () {
        $(this).unbind('update').bind("update", function() {
            var data = {variants: []};
            $('.product-variant-list .product-variant').each(function () {
                $(this).trigger('checkEdit', [data]);
            });
            if ( data.variants.length < 1) {
                $(this).addClass('hide-btn');
                return;
            }

            $(this).removeClass('hide-btn');
        });

        $(this).click(function () {
            var data = {variants: []};
            $('.product-variant-list .product-variant').each(function () {
                $(this).trigger('checkEdit', [data]);
            });

            if ( data.variants.length < 1) {
                return;
            }
            _renderVariantEditorV1(data.variants);
        });
        $(this).trigger("update");
    };

    window.initBulkDeleteVariantV1 = function () {
        $(this).unbind('update').bind("update", function() {
            let data = {variants: []};

            $('.product-variant-list .product-variant').each(function () {
                $(this).trigger('checkEdit', [data]);
            });

            if (data.variants.length < 1) {
                $(this).addClass('hide-btn');
                return;
            }

            $(this).removeClass('hide-btn');
        });

        $(this).click(function () {
            let data = {variants: []};

            $('.product-variant-list .product-variant').each(function () {
                $(this).trigger('checkEdit', [data]);
            });

            data.variants.forEach(function (variant) {
                variant.item.trigger('delete');
            });

            $('#bulk_edit_variant').trigger('update');
            $('#bulk_delete_variant').trigger('update');
        });

        $(this).trigger("update");
    };

    window.initBtnSubmitProduct = function () {
        $(this).click(function (e) {
            let btn = $(this);
            let form = btn.parents('form');
            let type = btn.data('type');
            let requiredTagTitle = [];
            let error = [];

            if (type === 'beta_product') {
                let title = form.find('input[name="title"]').val();
                if (title.length < 1 || title.length > 255) {
                    error.push('Product title must not be empty and no more than 255 characters\n');
                }
                if (form.find('select[name="vendor"]').val() === 0) {
                    error.push('Vendor must not be empty\n');
                }
                if (form.find('.product-variant-list > .product-variant').length < 1) {
                    error.push('Variant must not be empty\n');
                }
            }

            $('.tag-children.required').each(function () {
                const $el = $(this);
                if ($el.find(':checkbox').length && !$el.find(':checkbox:checked').length) {
                    let title = $el.parent().find('.tag-title').attr('title');
                    requiredTagTitle.push(title);
                }
            });
            if (requiredTagTitle.length) {
                error.push(`Product Tags [${requiredTagTitle.join(', ')}] is required!`);
            }

            if (window.__videoUploader__getData && typeof window.__videoUploader__getData === 'function') {
                let videos = window.__videoUploader__getData();

                if (videos) {
                    let missingThumbnail = false;

                    videos = Object.entries(videos).map(([video_id, video]) => {
                        if (!video.thumbnail) {
                            missingThumbnail = true;
                        }
                        return {
                            video_id: Number(video_id),
                            file_id: video.fileId,
                            url: video.url,
                            thumbnail: video.thumbnail,
                            variant_ids: Object.keys(video.variantIds || {}).map(n => Number(n)),
                            duration: Number(video.duration) || 0,
                        }
                    });

                    if (missingThumbnail) {
                        error.push("Thumbnails must be uploaded for all videos.");
                    }

                    $('<input type="hidden" name="videos" />').val(JSON.stringify(videos)).appendTo(form);
                }
            }

            if (error.length > 0) {
                alert(error.join("\n"));
                e.preventDefault();
                e.stopPropagation();
                return;
            }

            btn.attr('disabled', 'disabled');
            if (btn.hasAttr('data-action')) {
                $('<input type="hidden" />').attr('name', btn.attr('data-action')).attr('value', 1).appendTo(form);
            }

            form.submit();
        });
    };

    function matchCustom(params, data) {
        if ($.trim(params.term) === '') {
            return data;
        }

        if (typeof data.text === 'undefined') {
            return null;
        }

        if (data.text.indexOf(params.term) > -1) {
            var modifiedData = $.extend({}, data, true);
            modifiedData.text += ' (matched)';
            return modifiedData;
        }

        return null;
    }


    function _renderVariantEditorV1(variants) {
        let is_continue = false, is_status_alert = false, variants_reset = [];

        let variant_config = [], variant_tmp = [];

        var headers = {
            price: {title: 'Price'},
            compare_at_price: {title: 'Compared price'},
            shipping_price: {title: 'Shipping price'},
            shipping_plus_price: {title: 'Shipping plus price'},
            design_id: {title: 'Personalized Info'}
        };

        var modal = $('<div />').addClass('osc-modal product-variant-editor');

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html('Edit variants').appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('productVariantEditor');
        }).appendTo(header);

        var __setupResizeHandler = function (cell, min_width) {
            min_width = parseInt(min_width);

            if (isNaN(min_width) || min_width < 30) {
                min_width = 30;
            }

            $('<div />').addClass('spreadsheet__cell-resize-handler').appendTo(cell).mousedown(function (e) {
                e.preventDefault();

                var rect = cell[0].getBoundingClientRect();
                var click_coord = rect.width - (e.pageX - (rect.left + $(window).scrollLeft()));

                $(document.body).addClass('spreedsheet--resizing');

                var spreadsheet_parent = cell.closest('.spreadsheet').parent();

                $(document).bind('mousemove.sheetResize', function (e) {
                    e.preventDefault();

                    var rect = cell[0].getBoundingClientRect();
                    var container_rect = spreadsheet_parent[0].getBoundingClientRect();

                    var width = Math.min((container_rect.left + container_rect.width) - rect.left - 10, Math.max(min_width, e.pageX - (rect.left + $(window).scrollLeft()) + click_coord));

                    width += 'px';

                    cell.css({width: width, minWidth: width});
                }).bind('mouseup.sheetResize', function () {
                    e.preventDefault();

                    $(document).unbind('.sheetResize');

                    $(document.body).removeClass('spreedsheet--resizing');
                });
            });
        };

        var modal_body = $('<div />').addClass('body post-frm').css('overflow','scroll').css('max-height','700px').appendTo(modal);

        var spreadsheet = $('<div />').addClass('spreadsheet').appendTo($('<div />').addClass('spreadsheet-wrap').appendTo(modal_body));

        var sheet_header = $('<div />').addClass('spreadsheet__row spreadsheet__hover-highlight-disable').appendTo(spreadsheet);

        __setupResizeHandler($('<div />').text('Title').addClass('spreadsheet__cell spreadsheet__highlight cell--title').appendTo(sheet_header));

        for (var k in headers) {
            __setupResizeHandler($('<div />').text(headers[k].title).addClass('spreadsheet__cell spreadsheet__highlight cell--' + k).appendTo(sheet_header), headers[k].min_width);
        }

        variants.forEach(function (variant) {
            var row = $('<div />').addClass('spreadsheet__row').appendTo(spreadsheet);

            var title_cell = $('<div />').addClass('spreadsheet__cell spreadsheet__highlight cell--title').appendTo(row);

            for (var i = 1; i <= 3; i++) {
                if (variant.data['option' + i] !== '') {
                    $('<span />').addClass('option' + i).text(variant.data['option' + i]).appendTo(title_cell);
                }
            }

            for (var k in headers) {
                var cell = $('<div />').addClass('spreadsheet__cell cell--' + k).appendTo(row);

                if (['design_id'].indexOf(k) >= 0) {
                    var select_personalized_info = $('<select />').attr('name', 'design_id[]').addClass('select2 select--design_id').attr('multiple','multiple').appendTo(cell);

                    let formatState = function (state) {
                        if (!state.id) {
                            return state.text;
                        }

                        let $state = $(
                            '<span/>'
                        );

                        $('<a/>')
                            .text(state.element.text)
                            .attr({
                                'href': $.base_url + "/personalizedDesign/backend/post/id/" + parseInt(state.element.value) + "/type/default/hash/" + OSC_HASH,
                                'target': '_blank'
                            }).appendTo($state);

                        return $state;
                    }

                    select_personalized_info.select2({
                        matcher: matchCustom,
                        minimumInputLength: 3,
                        ajax: {
                            url: '/personalizedDesign/backend/loadPersonalizedBySemitest/hash/'+ OSC_HASH,
                            type: 'POST',
                            dataType: 'json',
                            data: function (params) {
                                return {
                                    keywords: params.term,
                                    page: params.page
                                };
                            },
                            processResults: function (data, params) {
                                params.page = params.page || 1;
                                return {
                                    pagination: {
                                        more: data.data.size <= data.data.total
                                    },
                                    results: data.data.items
                                };
                            },
                            cache: true
                        },
                        placeholder: variant.data['design_id'] && variant.data['design_id'].length ? 'Loading...' : '',
                        templateSelection: formatState
                    }).on("change", function(e) {
                        var data_selected = [];
                        if (select_personalized_info.select2("data") != null) {
                            $.each(select_personalized_info.select2("data"), function (k, v) {
                                data_selected.push(v);
                            });
                        }
                        select_personalized_info.attr('data-selected', JSON.stringify(data_selected));
                    }).on('select2:open', function (e) {
                        const evt = "scroll.select2";
                        $(e.target).parents().off(evt);
                        $(window).off(evt);
                    });

                    if (variant.data['design_id'] && variant.data['design_id'].length) {
                        $.ajax({
                            type: 'POST',
                            url: '/personalizedDesign/backend/loadPersonalizedBySemitest/hash/'+ OSC_HASH,
                            data: {ids: variant.data['design_id']},
                            success: function (response) {
                                if (response.result !== 'OK') {
                                    alert(response.message);
                                    return;
                                }
                                if (response.data.total > 0) {
                                    $.each(response.data.items, function (k, v) {
                                        if ($(this).find("option[value='" + v.id + "']").length < 1) {
                                            var newOption = new Option(v.text, v.id, false, true);
                                            select_personalized_info.append(newOption).trigger('change');
                                        }
                                    });
                                }
                            }
                        })
                    }

                    $('<button />').addClass('btn btn-icon apply_all_cell').attr('data-key', k).attr('title', 'Apply All Variant').attr('style', 'text-align: right; float: right').html($.renderIcon('clone')).click(function () {

                        var key = $(this).attr('data-key');
                        var current_select = $(this).parent().find('.select--' + $(this).attr('data-key'));
                        var box = $(this).parent().parent().parent().find('.select--' + key).not(current_select);

                        var data_selected = current_select.attr('data-selected');
                        var data_json = [];
                        if (data_selected != null) {
                            data_json = JSON.parse(data_selected);
                        }
                        if (data_json.length > 0) {
                                box.each(function(){
                                    var item  = $(this);
                                    item.find("option").remove();
                                    item.attr('data-selected', data_selected)
                                    $.each(data_json, function (k, v) {
                                        if (item.find("option[value='" + v.id + "']").length) {
                                            return;
                                        }
                                        var newOption = new Option(v.text, v.id, false, true);
                                        item.append(newOption);
                                    });
                                    item.trigger('change')
                            });
                        } else {
                            box.each(function(){
                                $(this).val(null).trigger('change');
                            });
                        }
                    }).appendTo(cell);
                } else {
                    let value = variant.data[k];
                    if (['shipping_price', 'shipping_plus_price'].indexOf(k) >= 0) {
                        if (!('meta_data' in variant.data) ||
                                variant.data.meta_data == null ||
                                !('semitest_config' in variant.data.meta_data) ||
                                variant.data.meta_data.semitest_config == null ||
                                !(k in variant.data.meta_data['semitest_config']) ||
                                variant.data.meta_data['semitest_config'][k] == null
                        ) {
                            value = '';
                        } else {
                            value = variant.data.meta_data['semitest_config'][k];
                        }
                    }

                    let _input = $('<input />')
                        .attr('type', 'text')
                        .attr('maxlength', '7')
                        .addClass('styled-input product-variant__input input--' + k)
                        .val((parseFloat(value) > 0) ? parseFloat(value) : '')
                        .focus(function () {
                            this.select();
                        }).keyup(function (e) {
                            const rg = /^\d{1,4}(\.\d{1,2})?$/g;    // pattern for price
                            const nn = /[^0-9]+/g;                  // pattern for non number

                            let _value = e.target.value;
                            let _validate = rg.test(_value);

                            if (!_validate) {
                                let _value_array = _value.split('.');
                                let _array_length = _value_array.length;
                                if (_array_length === 1) {
                                    _value = _value.replace(nn, '').substring(0, 4);
                                    _input.val((_value));
                                }
                                if (_array_length >= 2) {
                                    _value_array = _value_array.slice(0, 2);
                                    _value_array[0] = _value_array[0].replace(nn, '').substring(0, 4);
                                    _value_array[1] = _value_array[1].replace(nn, '').substring(0, 2);
                                    _input.val(_value_array.join('.'));
                                }
                            }
                        });

                    _input.appendTo(cell);

                    $('<button />').addClass('btn btn-icon apply_all_cell').addClass('apply_all_cell').attr('data-key', k).attr('title', 'Apply All Variant').attr('style', 'text-align: right; float: right').html($.renderIcon('clone')).click(function () {
                        var value = $(this).parent().find('.input--' + $(this).attr('data-key')).val();
                        $(this).parent().parent().parent().find('.input--' + $(this).attr('data-key')).val(value);
                    }).appendTo(cell);
                }
            }

            variant.row = row;
        });

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        let cancel_button = $('<button />').addClass('btn btn-secondary').html('Close').click(function () {
            $.unwrapContent('productVariantEditor');
        }).appendTo(action_bar);

        let back_button = $('<button />').addClass('btn btn-secondary').html('Back').prependTo(action_bar).hide();

        var btn_continue = $('<button />').addClass('btn btn-primary ml10 btn-continue').text('Set Up Print Template').appendTo(action_bar);

        let __renderSetUpContinue = function () {
            is_continue = true;
            spreadsheet.parent().hide();
            btn_continue.hide();
            cancel_button.hide();

            back_button.click(function () {
                is_continue = false;
                variant_tmp = [];
                spreadsheet.parent().show();
                print_template_beta_sheet.remove();
                cancel_button.show();
                btn_continue.show();
                $(this).hide();
            }).show();

            let print_template_beta_sheet = $('<div />').addClass('print_container').appendTo(modal_body);
            let design_panel = $('<div />').addClass('design-panel show-helper scene-opacity').appendTo(print_template_beta_sheet);
            let product_variant_panel = $('<div />').addClass('product-variant-panel').appendTo(print_template_beta_sheet);
            let panel_title = $('<div />').addClass('panel-title').html('Variants').appendTo(product_variant_panel);
            let item_list = $('<div />').addClass('item_list').appendTo(product_variant_panel);


            let design_main_scene = $('<div />').addClass('design-main-scene').appendTo(design_panel);
            let design_tab = $('<div />').addClass('design-tabs').appendTo(design_main_scene);
            let design_data_design = $('<div />').addClass('activated').hide().appendTo(design_tab);

            let design_viewer = $('<div />').addClass('design-viewer').appendTo(design_main_scene);

            let design_scene = $('<div />').addClass('design-scene').appendTo(design_viewer);

            let uploader_panel = $('<div />').addClass('uploader-panel').appendTo(design_main_scene);

            let uploader_design = $('<div />').addClass('uploader-design btn btn-primary').appendTo(uploader_panel);

            let frame = $('<div />').addClass('frame').appendTo(design_scene);

            let print_image = $('<img />').appendTo(frame);

            let _zoom_ratio = 0,
                design_scene_position = {};

            let _this = this;

            let item_active = 0;

            let print_template_beta = [],
                _RATIO = 0,
                __DIMENSION = {},
                image_url = '',
                current_design_key = '',
                current_config_key = -1,
                current_variant = 0,
                design_id_source = [];

            let list_variant_config = null;

            this.renderDesignSence = function () {
                let is_hold_handler = false;

                let __this_design_sence = this;

                let design_ids = [], design_seleted = [], is_browse = false;

                uploader_design.html('Browse a design file').addClass('disabled')
                    .click(async function () {
                        if (is_browse == false) {
                            alert('No Print Template selected');
                            return;
                        }

                        $.unwrapContent('productBetaImageSelector');

                        let modal = $('<div />').addClass('osc-modal').width(1000),
                            header = $('<header />').appendTo(modal);

                        $('<div />').addClass('title').html('Choose Personalized Design').appendTo($('<div />').addClass('main-group').appendTo(header));

                        $('<div />').addClass('close-btn').click(function () {
                            $.unwrapContent('productBetaImageSelector');
                        }).appendTo(header);

                        let modal_body = $('<div />').addClass('body post-frm').appendTo(modal),
                            personalized_design_frm = $('<div />').addClass('campaign-image-selector').appendTo(modal_body),
                            library_tabs = $('<div />').addClass('library-tabs').appendTo(personalized_design_frm),
                            tab_content_container = $('<div />').appendTo(personalized_design_frm);

                        let design_label = $('<label />').attr({
                            'for': 'select-design',
                            'class': 'required'
                        }).text('Personalized Design').appendTo(modal_body);
                        let design_select = $('<select />').addClass('styled-select').attr({
                            'name': 'design',
                            'id': 'select-design',
                            'multiple': 'multiple',
                            'size': 20,
                            'required': true
                        });

                        $('<div />').addClass('select-design').append(design_label).append(design_select).appendTo(modal_body);

                        design_select.select2({
                            // matcher: matchCustom,
                            // minimumInputLength: 3,
                            ajax: {
                                url: '/personalizedDesign/backend/loadPersonalizedBySemitest/hash/' + OSC_HASH,
                                type: 'POST',
                                dataType: 'json',
                                data: function (params) {
                                    return {
                                        page: params.page,
                                        ids: design_ids
                                    };
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        pagination: {
                                            more: data.data.size <= data.data.total
                                        },
                                        results: data.data.items
                                    };
                                },
                                cache: true
                            },
                            placeholder: design_seleted && design_seleted.length ? 'Loading...' : '',
                        });

                        if (design_seleted && design_seleted.length) {
                            $.ajax({
                                type: 'POST',
                                url: '/personalizedDesign/backend/loadPersonalizedBySemitest/hash/' + OSC_HASH,
                                data: {ids: design_seleted},
                                success: function (response) {
                                    if (response.result !== 'OK') {
                                        alert(response.message);
                                        return;
                                    }
                                    if (response.data.total > 0) {
                                        $.each(response.data.items, function (k, v) {
                                            if ($(this).find("option[value='" + v.id + "']").length < 1) {
                                                var newOption = new Option(v.text, v.id, false, true);
                                                design_select.append(newOption).trigger('change');
                                            }
                                        });
                                    }
                                }
                            })
                        }

                        let action_bar = $('<div />').addClass('action-bar').appendTo(modal);

                        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                            $.unwrapContent('productBetaImageSelector');
                        }).appendTo(action_bar);

                        let button_submit = $('<button />').addClass('btn btn-primary ml10').attr('type', 'button').html('Save').appendTo(action_bar).click(function () {
                            let design_id_values = design_select.val() ? design_select.val() : [];
                            let __design_ids = [];

                            if (design_id_values.length >= design_seleted.length) {
                                __design_ids = design_id_values.filter(x => !design_seleted.includes(parseInt(x)));

                                __design_ids.forEach(function (design_id) {
                                    design_seleted.push(parseInt(design_id));
                                    $.ajax({
                                        type: 'post',
                                        url: $.base_url + '/personalizedDesign/backend/getSvgContent',
                                        data: {
                                            hash: OSC_HASH,
                                            design_id: design_id,
                                            options : ['render_design']
                                        },
                                        success: function (response) {
                                            if (response.result !== 'OK') {
                                                alert(response.message);
                                                return;
                                            }

                                            const _data = response.data;

                                            let svg = _data.svg_content;

                                            design_scene.trigger('update-preview', [{
                                                source: {
                                                    type: 'personalizedDesign',
                                                    design_id: design_id,
                                                    position: {"x": 0, "y": 0},
                                                    dimension: {
                                                        'width': 0,
                                                        'height': 0,
                                                    },
                                                    rotation: 0,
                                                    timestamp: (new Date()).getTime(),
                                                    // url: item_data.image_url,
                                                    svg_content: svg,
                                                    orig_size: {
                                                        width: _data.document.width,
                                                        height: _data.document.height,
                                                    }
                                                }
                                            }, 'add']);
                                        }
                                    });
                                })
                            } else {
                                let __design_id_values = [];

                                design_id_values.map(x => __design_id_values.push(parseInt(x)));

                                let __design_id_missing = design_seleted.filter(x => !__design_id_values.includes(parseInt(x)));

                                __design_id_missing.map(function (x) {
                                    __this_design_sence.deteleDesign(x);
                                })
                            }

                            $.unwrapContent('productBetaImageSelector');
                        });

                        $.wrapContent(modal, {key: 'productBetaImageSelector'});

                        modal.moveToCenter().css('top', '100px');
                    })
                    .bind('update-status', function (e, data) {
                        is_browse = data.is_browse;
                        is_browse ? uploader_design.removeClass('disabled') : uploader_design.addClass('disabled');
                    });

                frame.bind('ratio_change', function (e, old_radio, new_radio) {

                    let position = {
                        x: (design_scene.width() - (__DIMENSION.width * new_radio)) / 2,
                        y: (design_scene.height() - (__DIMENSION.height * new_radio)) / 2
                    }

                    frame.css({
                        'width': (__DIMENSION.width * new_radio) + 'px',
                        'height': (__DIMENSION.height * new_radio) + 'px',
                        'left': position.x,
                        'top': position.y
                    });

                    print_image.css({
                        'width': (__DIMENSION.width * new_radio) + 'px',
                    });

                    _RATIO = new_radio

                    frame.find('image').each(function (i, design) {
                        __triggerImgUpdate(design, true);
                    });
                });

                design_scene
                    .bind('renderDesignScene', function (e, data) {
                        let item = data.item,
                            variant = data.variant,
                            image_url = item.print_template_beta.img,
                            print_template_beta = item.print_template_beta,
                            dimension = print_template_beta.dimension,
                            dimension_width = dimension.width,
                            dimension_height = dimension.height,
                            dimension_max = Math.max(dimension.width, dimension.height),
                            ratio_sence = design_scene.width() / 2;

                        __DIMENSION = dimension;

                        if (dimension.width > ratio_sence && dimension.width == dimension_max) {
                            dimension_width = dimension.width / (dimension.width / ratio_sence);
                            dimension_height = dimension.height / (dimension.width / dimension_width);
                        }

                        if (dimension.height > ratio_sence && dimension.height == dimension_max) {
                            dimension_height = dimension.height > ratio_sence ? dimension.height / (dimension.height / ratio_sence) : dimension.height;
                            dimension_width = dimension.width / (dimension.height / dimension_height);
                        }

                        print_image.attr('src', image_url);

                        let position = {
                            x: (design_scene.width() - dimension_width) / 2,
                            y: (design_scene.height() - dimension_height) / 2
                        }

                        frame.css({
                            'width': dimension_width,
                            'height': dimension_height,
                            'left': position.x,
                            'top': position.y
                        });

                        print_image.css({
                            'width': dimension_width,
                            // 'height': dimension_height,
                            'left': 0,
                            'top': 0
                        });

                        _zoom_ratio = dimension_width / dimension.width;

                        _this.renderToolBarBottom();

                        design_ids = variant.data.design_id;

                        _RATIO = print_image[0].getBoundingClientRect().width / dimension.width

                        let source = item.segments.source;

                        design_scene.trigger('reset');
                        design_seleted = [];
                        if (source.length > 0) {

                            source.forEach(function (config, key) {
                                let design_id = config.design_id;

                                design_seleted.push(parseInt(design_id));

                                design_scene.trigger('update-preview', [{
                                    source: {
                                        type: 'personalizedDesign',
                                        design_id: design_id,
                                        position: config.position,
                                        dimension: config.dimension,
                                        rotation: config.rotation,
                                        timestamp: config.timestamp,
                                        // url: item_data.image_url,
                                        svg_content: config.svg_content,
                                        orig_size: config.orig_size,
                                    },
                                }]);
                            })
                        }
                    })
                    .bind('reset', function (e) {
                        $(this).find('.design_config').remove();
                    })
                    .bind('update-preview', function (e, data, type) {
                        __getAutoDesignDismension(data);

                        let config_key_design = 0;

                        let source = variant_config[current_variant.data['id']][current_config_key].print_template_config[current_design_key].segments.source;

                        if (design_id_source.includes(data.source.design_id)) return;

                        if (type == 'add') {
                            let design_id_dup = [];

                            source.forEach(function (source_design, key) {
                                if (data.source.design_id == source_design.design_id) {
                                    design_id_dup.push(data.source.design_id);
                                    return;
                                }
                            });

                            if (Object.keys(design_id_dup).length > 0) {
                                alert('design ' + design_id_dup.join(',') + ' already exists');
                                return;
                            }

                            config_key_design = Object.keys(source).length;

                            // source[config_key_design] = data.source;
                            source.push(data.source);

                            __updatePrintStatus(source);
                            list_variant_config.__renderPrintTemplateConfigByVariantID(current_variant, true);
                        } else {
                            source.forEach(function (source_design, key) {
                                if (data.source.design_id == source_design.design_id) {
                                    config_key_design = key;
                                    return;
                                }
                            });
                        }

                        let config_scene = $('<div />').addClass('design_config config_design_key_' + config_key_design).attr({
                            'data-key': config_key_design,
                            'data-design-id': data.source.design_id
                        }).appendTo(frame);

                        let scene = $('<div />').addClass('scene').appendTo(config_scene);

                        let design = $('<div />').addClass('image').html(data.source.svg_content).appendTo(scene);

                        design.find('svg')[0].setAttribute('preserveAspectRatio', 'none');

                        design.bind('updated', function (e, new_data) {
                            if (new_data) {
                                data = new_data;

                                if (typeof data.source.svg_content !== 'undefined') {
                                    design.html(typeof data.source.option_default_values === 'undefined' ? data.source.svg_content : data.source.option_default_values.svg_content);
                                    design.find('svg')[0].setAttribute('preserveAspectRatio', 'none');
                                } else {
                                    design.attr('src', data.source.url + '?t=' + (new Date()));
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
                        });

                        let helper = $('<div />').addClass('helper').appendTo(config_scene)
                            .bind('mousedown', function (e) {
                                e.stopPropagation();

                                tool_panel.activeDesign(helper);

                                var bounding_rect = __helperGetBoundingClientRectExceptRotation(helper);

                                var scroll_top = $(window).scrollTop();
                                var scroll_left = $(window).scrollLeft();

                                var anchor = {
                                    x: (e.pageX - scroll_left) - bounding_rect.x,
                                    y: (e.pageY - scroll_top) - bounding_rect.y
                                };

                                $(document).unbind('.product-helper').bind('mousemove.product-helper', function (e) {
                                    let bounding_rect = helper.parent().parent()[0].getBoundingClientRect();

                                    let scroll_top = $(window).scrollTop();
                                    let scroll_left = $(window).scrollLeft();

                                    data.source.position.x = (e.pageX - scroll_left - bounding_rect.x - anchor.x) / _RATIO;
                                    data.source.position.y = (e.pageY - scroll_top - bounding_rect.y - anchor.y) / _RATIO;
                                    data.source.timestamp = (new Date()).getTime();

                                    __triggerImgUpdate(design);
                                    __updateVariantConfig(data, helper);
                                });

                                $(document).bind('mouseup.product-helper', function () {
                                    $(document).unbind('.product-helper');
                                });

                                const keysdown = {};

                                //Handle Key down for move desgin
                                $(document).unbind("keydown.product-helper-keyboard")
                                    .bind("keydown.product-helper-keyboard", function (e) {
                                        if (e.target && ['input', 'select', 'textarea'].indexOf(e.target.nodeName.toLowerCase()) >= 0) {
                                            return;
                                        }

                                        const isHold = !!keysdown[e.keyCode];
                                        const value = (e.shiftKey ? 10 : (isHold ? 2 : 1)) / _RATIO;

                                        const __move = (moveX, moveY) => {
                                            data.source.position.x += moveX
                                            data.source.position.y += moveY
                                            __triggerImgUpdate(design)
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
                                    });

                                $(document).keyup(function (e) {
                                    // Remove this key from the map
                                    delete keysdown[e.keyCode];
                                });
                            });

                        __triggerImgUpdate(design);

                        design_viewer.on("click", function (event) {
                            if ($(event.target).closest(".helper").length === 0 && is_hold_handler == false) {
                                tool_panel.deactiveDesign();
                                $(document).unbind("keydown.product-helper-keyboard");
                            }
                        });

                        ['NW', 'N', 'NE', 'E', 'SE', 'S', 'SW', 'W'].forEach(function (handler_name) {
                            var handler = $('<div />').addClass('resize-handler').attr('data-handler', handler_name).appendTo(helper);

                            handler.mousedown(function (e) {
                                e.stopImmediatePropagation();

                                is_hold_handler = true;

                                var bounding_rect = __helperGetBoundingClientRectExceptRotation(helper);

                                var scroll_top = $(window).scrollTop();
                                var scroll_left = $(window).scrollLeft();

                                var anchor = __getPointExceptRotation({
                                    x: e.pageX - scroll_left,
                                    y: e.pageY - scroll_top
                                }, bounding_rect, data.source.rotation);

                                $(document).bind('mousemove.product-helper', function (e) {
                                    var scroll_top = $(window).scrollTop();
                                    var scroll_left = $(window).scrollLeft();

                                    var cursor_point = __getPointExceptRotation({
                                        x: e.pageX - scroll_left,
                                        y: e.pageY - scroll_top
                                    }, bounding_rect, data.source.rotation);

                                    let new_width = 0;
                                    let new_height = 0;

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

                                    var _bounding_rect = helper.parent().parent()[0].getBoundingClientRect();


                                    data.source.position.x = (bounding_pt.x - _bounding_rect.x) / _RATIO;
                                    data.source.position.y = (bounding_pt.y - _bounding_rect.y) / _RATIO;
                                    data.source.dimension.width = new_width / _RATIO;
                                    data.source.dimension.height = new_height / _RATIO;
                                    data.source.timestamp = (new Date()).getTime();
                                    __triggerImgUpdate(design);

                                    __updateVariantConfig(data, helper);
                                }).bind('mouseup.product-helper', function (e) {
                                    setTimeout(function () {
                                        is_hold_handler = false;
                                    }, 500);
                                    $(document).unbind('.product-helper');
                                });
                            });
                        });
                    })
                    .bind('handler', function () {
                        let design_scene_helper = $('<div />').addClass('design-sence-clone').css({
                            'position': 'absolute',
                            'width': design_scene.width(),
                            'height': design_scene.height(),
                            'top': 0,
                            'left': 0,
                            'z-index': 20,
                            'cursor': 'grab'
                        }).appendTo(design_viewer).bind('mousedown', function (e) {
                            design_scene_helper.css({'cursor': 'grabbing'});

                            var bounding_rect = design_scene[0].getBoundingClientRect();

                            var scroll_top = $(window).scrollTop();
                            var scroll_left = $(window).scrollLeft();

                            var anchor = {
                                x: (e.pageX - scroll_left) - bounding_rect.x,
                                y: (e.pageY - scroll_top) - bounding_rect.y
                            };

                            $(document).unbind('.scence-helper').bind('mousemove.scence-helper', function (e) {
                                let bounding_rect = design_scene.parent()[0].getBoundingClientRect();

                                let scroll_top = $(window).scrollTop();
                                let scroll_left = $(window).scrollLeft();

                                let design_sence_postion = {};
                                design_sence_postion.x = (e.pageX - scroll_left - bounding_rect.x - anchor.x);
                                design_sence_postion.y = (e.pageY - scroll_top - bounding_rect.y - anchor.y);


                                design_scene_position = {
                                    'top': design_sence_postion.y + 'px',
                                    'left': design_sence_postion.x + 'px',

                                }

                                design_scene.css(design_scene_position);
                            });

                            $(document).bind('mouseup.scence-helper', function () {
                                $(document).unbind('.scence-helper');
                            }).bind('mouseup', function (e) {
                                design_scene_helper.css({'cursor': 'grab'});
                            });
                        });
                    })
                    .bind('unHandler', function () {
                        $('.design-sence-clone').remove();
                    })

                let __getAutoDesignDismension = function (data) {
                    if (data.source.dimension.width == 0 && data.source.dimension.height == 0) {
                        let _max_origin_design = Math.max(data.source.orig_size.width, data.source.orig_size.height);

                        if (data.source.orig_size.width === _max_origin_design) {
                            let radio_design = _max_origin_design / print_image.width();
                            data.source.dimension.width = data.source.orig_size.width / radio_design;
                            data.source.dimension.height = data.source.orig_size.height / radio_design;
                        }

                        if (data.source.orig_size.height === _max_origin_design) {
                            let radio_design = _max_origin_design / print_image.height();
                            data.source.dimension.height = (data.source.orig_size.height / radio_design) / _RATIO;
                            data.source.dimension.width = (data.source.orig_size.width / radio_design) / _RATIO;
                        }
                    }
                }

                let __updatePrintStatus = function (source) {
                    let designIds = [];
                    source.forEach(function (source, key) {
                        designIds.push(source.design_id);
                    });

                    let designList = _renderDesignId(designIds);

                    let print_status = {
                        'active': {
                            _found: true,
                            _class: 'full-data',
                            _text: 'Ready!',
                            _icon: 'verified',
                            _icon_variant: 'list-verified'
                        },
                        'deactive': {
                            _found: false,
                            _class: 'enough-data',
                            _text: 'Lack of data',
                            _icon: 'warning',
                            _icon_variant: 'warning'

                        }
                    };
                    let item_row = $(".item-variant[id='variant-" + current_variant.data['id'] + "']");
                    let print_template = item_row.find($(".print-list-for-supplier[data-id='" + current_config_key + "']")).find(".printTemplate_" + current_design_key);

                    if (designIds.length < 1) {
                        print_template.addClass(print_status.deactive._class).removeClass(print_status.active._class);
                        print_template.find('span.print-template-status').remove();
                        print_template.find('span.design-ids').html(designList);
                        $('<span />').addClass('print-template-status').text(print_status.deactive._text).append($.renderIcon(print_status.deactive._icon)).appendTo(print_template);
                        $('#variant-' + current_variant.data['id']).find('.variant-status').html($.renderIcon(print_status.deactive._icon_variant));
                        return;
                    }

                    $('#variant-' + current_variant.data['id']).find('.variant-status').html($.renderIcon(print_status.active._icon_variant)).addClass('list-verified');
                    print_template.addClass(print_status.active._class).removeClass(print_status.deactive._class);
                    print_template.find('span.print-template-status').remove();
                    print_template.find('span.design-ids').html(designList);
                    $('<span />').addClass('print-template-status').text(print_status.active._text).append($.renderIcon(print_status.active._icon)).appendTo(print_template);

                }

                let __updateVariantConfig = function (data, helper) {
                    let current_key = helper.parent().attr('data-key');
                    variant_config[current_variant.data['id']][current_config_key].print_template_config[current_design_key].segments.source[current_key] = data.source;
                }

                let __triggerImgUpdate = function (design, is_dom = false) {
                    design = !is_dom ? design : $(design)
                    design.trigger('updated');
                }

                let __helperGetBoundingClientRectExceptRotation = function (helper) {
                    var _helper = helper.clone(true).html('').css({
                        transform: 'none',
                        opacity: 0
                    }).appendTo(helper.parent().parent());

                    var bounding_rect = _helper[0].getBoundingClientRect();

                    _helper.remove();

                    return bounding_rect;
                };

                let __pointGetDistance = function (p1, p2) {
                    return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
                };

                let __getPointExceptRotation = function (point, bounding_rect, rotation) {
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

                let __getVectorIntersectionPoint = function (vector1, vector2) {
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

                this.deteleDesign = function (design_id_selected) {
                    let source = __fetchDataVariantConfigCurrent().segments.source;

                    source.forEach(function (item, key) {
                        if (item['design_id'] == design_id_selected) {
                            source.splice(key, 1);
                            // delete source[key];
                            return;
                        }
                    });

                    design_seleted.splice(design_seleted.indexOf(parseInt(design_id_selected)), 1);
                    $(".design_config[data-design-id='" + design_id_selected + "']").remove();

                    __updatePrintStatus(source);
                }
            }

            let __DesignSence = new this.renderDesignSence();

            this.renderToolPanel = function () {
                let is_active = false,
                    _this = this,
                    design_id_selected = 0,
                    is_handler = false,
                    is_enable_handler = false;

                let tools_panel = $('<div />').addClass('tools-panel').appendTo(design_main_scene);

                let action_btn_vertical_center = $('<div />').addClass('action-btn disabled').attr({'data-action': 'vertical-center'}).html($.renderIcon('vertical-center')).appendTo(tools_panel).click(function () {
                    alignDesign('vertical_center');
                });

                let action_btn_horizontal_center = $('<div />').addClass('action-btn disabled').attr({'data-action': 'horizontal-center'}).html($.renderIcon('horizontal-center')).appendTo(tools_panel).click(function () {
                    alignDesign('horizontal_center');
                });

                let action_btn_opacity = $('<div />').addClass('action-btn disabled').attr({'data-action': 'opacity'}).html($.renderIcon('opacity')).appendTo(tools_panel).click(function () {
                    let scene_image = $(".design_config[data-design-id='" + design_id_selected + "']").find('.scene .image');

                    if (scene_image.hasClass('opacity')) {
                        scene_image.removeClass('opacity');
                    } else {
                        scene_image.addClass('opacity');
                    }
                });

                let action_btn_delete = $('<div />').addClass('action-btn disabled').attr({'data-action': 'delete'}).html($.renderIcon('trash-alt-regular')).appendTo(tools_panel).click(function () {
                    if (is_active) __DesignSence.deteleDesign(design_id_selected);
                });

                let action_btn_hand = $('<div />').addClass('action-btn disabled').attr({'data-action': 'handler'}).html($.renderIcon('hand-paper-regular')).appendTo(tools_panel).click(function () {
                    if (is_enable_handler) _this.changeHandler();
                });

                let alignDesign = function (type) {
                    let design_ele = $(".design_config[data-design-id='" + design_id_selected + "']");
                    let designs = design_ele.find('image');
                    let current_design_id = design_ele.attr('data-key');
                    let current_data = __fetchDataVariantConfigCurrent();
                    let print_template_beta = current_data.print_template_beta;
                    let new_data = current_data.segments.source[current_design_id];

                    switch (type) {
                        case 'vertical_center':
                            new_data.position.y = (print_template_beta.dimension.height - new_data.dimension.height) / 2;
                            break;
                        case 'horizontal_center':
                            new_data.position.x = (print_template_beta.dimension.width - new_data.dimension.width) / 2;
                            break;
                    }

                    designs.each((i, design) => $(design).trigger('updated'));
                }

                this.changeHandler = function (handler = 'toggle') {
                    switch (handler) {
                        case 'handler':
                            is_handler = true
                            break;
                        case 'unHandler':
                            is_handler = false
                            break;
                        case 'toggle':
                            is_handler = !is_handler;
                            break;
                    }

                    if (is_handler) {
                        action_btn_hand.addClass('activated');
                        design_scene.trigger('handler');
                    } else {
                        action_btn_hand.removeClass('activated');
                        design_scene.trigger('unHandler');
                    }
                }

                tools_panel.bind('updateActionStatus', function () {
                    let action_tools = [action_btn_opacity, action_btn_delete, action_btn_vertical_center, action_btn_horizontal_center];

                    if (is_active) {
                        action_tools.forEach(function (action, index) {
                            action.addClass('active').removeClass('disabled');
                        });
                    } else {
                        action_tools.forEach(function (action, index) {
                            action.addClass('disabled').removeClass('active');
                        });
                    }
                });

                this.activeHandler = function () {
                    is_enable_handler = true;
                    action_btn_hand.removeClass('disabled').addClass('active');
                }

                this.deActiveHandler = function () {
                    is_enable_handler = false;
                    action_btn_hand.removeClass('active').addClass('disabled');
                }

                this.activeDesign = function (helper) {
                    is_active = true;

                    let design_id_current = helper.parent().attr('data-design-id');

                    frame.find('.helper').removeClass('active');

                    design_id_selected = design_id_current;

                    if (design_id_selected != 0) {
                        $(helper).addClass('active');
                    }

                    tools_panel.trigger('updateActionStatus');
                }

                this.deactiveDesign = function () {
                    is_active = false;
                    design_id_selected = 0;
                    frame.find('.helper').removeClass('active');
                    tools_panel.trigger('updateActionStatus');
                }

                this.resetStatus = function () {
                    this.deactiveDesign();
                    _this.changeHandler('unHandler');
                    _this.activeHandler();
                }

                let _setupKeyboardControl = function () {
                    let holdCommand = null;

                    $(document).on('keydown', function (event) {
                        let command = null;

                        switch (event.keyCode) {
                            case 32: //Space
                                command = "hand";
                                break;
                            default:
                                break;
                        }
                        if (holdCommand === command) {
                            return;
                        }

                        holdCommand = command;

                        _this.changeHandler('handler');

                        // event.preventDefault();
                    });

                    $(document).on('keyup', function (event) {
                        switch (event.keyCode) {
                            case 32: //Space
                                if (holdCommand) {
                                    _this.changeHandler('unHandler');
                                }
                                break;
                            default:
                                break;
                        }
                        holdCommand = null;
                        // event.preventDefault();
                    });
                };

                _setupKeyboardControl();
            }

            let tool_panel = new _this.renderToolPanel();

            this.renderToolBarBottom = function () {
                let builder_bottombar = $('<div />').addClass('builder-bottombar').appendTo(design_viewer);
                let container = $('<div />').addClass('zoom-bar');
                $('<span />').text('Zoom').appendTo(container);
                let modifier = $('<div />').addClass('modifier').appendTo(container);
                $('<div />').addClass('minus').css('margin-right', 0).appendTo(modifier);
                let input = $('<input />').attr({type: 'text', value: _zoom_ratio * 100}).appendTo(modifier);
                $('<div />').addClass('plus').appendTo(modifier);
                builder_bottombar.html(container);

                modifier.find('.plus,.minus').bind('click', function () {
                    this.className.indexOf('plus') >= 0 ? _zoomIn() : _zoomOut();
                });

                input.bind('change', function () {
                    let percent = parseFloat($(this).val());

                    percent = Math.max(1, percent);

                    _zoom(percent);
                });

                let _zoom = function (percent) {
                    percent = parseFloat(percent);

                    if (isNaN(percent)) {
                        percent = 1;
                    }
                    const old_ratio = _zoom_ratio;

                    const new_ratio = percent / 100;

                    _zoom_ratio = new_ratio;

                    print_image.trigger('ratio_change', [old_ratio, new_ratio]);

                    input.val(percent);

                    return percent;
                }

                let _zoomIn = function () {
                    let percent = _zoom_ratio * 100;

                    percent *= 1.5;

                    percent = Math.max(1, percent);

                    _zoom(percent);
                };

                let _zoomOut = function () {
                    let percent = _zoom_ratio * 100;

                    percent /= 1.5;

                    percent = Math.max(1, percent);

                    _zoom(percent);
                };

            }

            this.renderListVariantConfig = function () {
                let _this = this;

                this.__renderPrintTemplateConfig = function () {
                    let __variant = variant_tmp && variant_tmp.length > 0 ? variant_tmp : variants;

                    item_active = __variant[0].data['id'];

                    __variant.forEach(function (variant, key) {
                        variant_config[variant.data.id] = variant.data.meta_data.variant_config ? _coppyData(variant.data.meta_data.variant_config) : null;
                        if (variants_reset.includes(variant.data.id)) {
                            variant_config[variant.data.id] = [];
                        }
                        _this.__renderPrintTemplateConfigByVariantID(variant)
                    });
                }
                //
                this.__renderPrintTemplateConfigByVariantID = function (variant, reload = false) {

                    let showConfig = function () {
                        if (item_title.hasClass("active")) {
                            item.trigger('collapse');
                        } else {
                            product_variant_panel.find('.item-variant').trigger('collapse');
                            item.trigger('expand');
                        }
                    }

                    let item = null;

                    if (reload) {
                        item = $(".item-variant[id='" + 'variant-' + variant.data['id'] + "']");
                        item.children().remove();
                    } else {
                        item = $('<div />').addClass('item-variant').attr('id', 'variant-' + variant.data['id']).appendTo(item_list);
                    }

                    let variant_block = $('<div />').addClass('variant-block').appendTo(item),
                        dragdrop = $('<span />').addClass('dragdrop').appendTo(variant_block),
                        item_row = $('<span />').addClass('item-row').appendTo(variant_block),
                        item_title = $('<div />').addClass('title').appendTo(item_row),
                        item_option = $('<div />').addClass('title-option').appendTo(item_title);

                    for (var i = 1; i <= 3; i++) {
                        if (variant.data['option' + i] !== '') {
                            $('<span />').addClass('option' + i).text(variant.data['option' + i]).appendTo(item_option);
                        }
                    }

                    let tool_variant = $('<div/>').addClass('tool-variant').appendTo(item_title);

                    let variant_status = $("<span />").addClass('variant-status btn').appendTo(tool_variant);

                    let coppy = $("<span />").addClass('coppy btn').append($.renderIcon('duplicate')).attr('data-id', variant.data['id']).appendTo(tool_variant).click(function (e) {
                        let __variant_id = $(this).attr('data-id');
                        let __variant = variant_tmp ? variant_tmp : variants;
                        let __message = 'Coppy sucess';
                        let copy_error = [], design_id_missing = [], __variant_title = '', variant_coppy = [],
                            key_number = 0, variant_id_sort = [];
                        Object.keys(variant_config).forEach(function (key) {
                            variant_coppy[key_number] = {
                                "variant_id": key,
                                "data": variant_config[key]
                            };

                            variant_id_sort[key] = key_number;

                            key_number++;
                        });

                        Object.keys(variant_coppy).forEach(function (key) {
                            if (key > variant_id_sort[__variant_id]) {
                                let __design_ids = [];

                                __variant.forEach(function (variant, key2) {
                                    if (variant.data.id == variant_coppy[key].variant_id) {
                                        __variant_title = '';
                                        for (var i = 1; i <= 3; i++) {
                                            if (variant.data['option' + i] !== '') {
                                                if (i > 1) __variant_title += '-';
                                                __variant_title += variant.data['option' + i];
                                            }
                                        }
                                        variant.data.design_id.map(x => __design_ids.push(parseInt(x)));
                                    }
                                });

                                //check variant missing design.
                                design_id_missing[__variant_title] = [];

                                variant_coppy[variant_id_sort[__variant_id]].data.forEach(function (_config) {
                                    Object.keys(_config.print_template_config).map(function (print_key) {
                                        _config.print_template_config[print_key].segments.source.forEach(function (source) {
                                            if (!__design_ids.includes(parseInt(source.design_id))) {
                                                if (!design_id_missing[__variant_title][source.design_id]) {
                                                    design_id_missing[__variant_title][source.design_id] = source.design_id;
                                                }
                                            }
                                        });
                                    });
                                });

                                if (Object.keys(design_id_missing[__variant_title]).length < 1) delete design_id_missing[__variant_title];

                                if (Object.keys(design_id_missing) < 1) {
                                    variant_config[variant_coppy[key].variant_id] = _coppyData(variant_coppy[variant_id_sort[__variant_id]].data);
                                    __variant.forEach(function (variant, key2) {
                                        if (variant.data.id == variant_coppy[key].variant_id) {
                                            _this.__renderPrintTemplateConfigByVariantID(variant, true);
                                            return;
                                        }
                                    });
                                }
                            }
                        });

                        Object.keys(design_id_missing).map(x => copy_error.push('Variant[' + x + '] missing design ids :' + Object.keys(design_id_missing[x]).join(',')));

                        if (copy_error.length > 0) {
                            __message = '';
                            copy_error.forEach(function (message) {
                                __message += message + '\n';
                            })
                        }

                        alert(__message);
                    });

                    let arrow = $("<span />").addClass('arrow').append($.renderIcon('angle-down-solid')).appendTo(tool_variant);

                    let variant_key = 'variant-key-' + variant.data['id']

                    var printTemplateDataList = $('<div />').addClass('print-template-data-list variant-key-' + variant_key).appendTo(item),
                        printTemplateContainer = $('<div />').addClass('print-template-container').appendTo(printTemplateDataList),
                        printTemplateHeading = $('<h4 />').addClass('print-template-heading').text('Print template config').appendTo(printTemplateContainer),
                        printTemplateGenerated = $('<div />').addClass('print-template-generated').attr('data-product-print', 1).appendTo(printTemplateContainer);
                    var printTemplateList = $('<div />').addClass('print-templates-list').attr('data-product-type', 1).appendTo(printTemplateGenerated);

                    arrow.click(function () {
                        showConfig();
                    });

                    item_option.click(function () {
                        showConfig();
                    });

                    item.bind("expand", function () {
                        item_active = variant.data['id'];
                        item_title.addClass('active');
                        printTemplateDataList.addClass('active');
                        arrow.addClass('hasShown');
                    }).bind("collapse", function () {
                        item_title.removeClass('active');
                        printTemplateDataList.removeClass('active');
                        arrow.removeClass('hasShown');
                    });

                    if (item_active == variant.data['id']) {
                        item.trigger('expand');
                    }

                    $('<span />').addClass('btn btn-choose-print-template')
                        .text('Add Print Template Config')
                        .prepend($('<ins />').addClass('arrow-plus'))
                        .attr('data-product-print', 1)
                        .appendTo(printTemplateHeading).click(function () {
                        new __setUpPrintTemplate(-1, variant);
                    });

                    if (!variant_config[variant.data.id] || variant_config[variant.data.id].length < 1) {
                        variant_status.html($.renderIcon('warning'));
                        // return;
                    }

                    let print_default_key = -1;

                    variant_config[variant.data.id].forEach(function (item, key) {
                        if (variant_config[variant.data.id][key].is_default) {
                            print_default_key = key;
                            return;
                        }
                    });

                    variant_config[variant.data.id].forEach(function (item, config_key) {
                        let print_template = item.print_template_config;
                        let print_supplier = item.supplier.join(',');
                        let printSupplier = $('<div />').addClass('print-list-for-supplier print-supplier-' + print_supplier).attr({
                            'data-id': config_key
                        }).appendTo(printTemplateList);

                        let config_title = item.title;
                        let print_supplier_group_supplier = $('<div />').addClass('print-supplier-group supplier-' + print_supplier).appendTo(printSupplier);
                        let print_config_title = $('<div />').addClass('print-config-title').html(config_title).appendTo(print_supplier_group_supplier);
                        let print_config_action = $('<div />').addClass('print-config-action').appendTo(print_supplier_group_supplier);

                        let is_check_config_default = config_key == 0 ? ((config_key == print_default_key || print_default_key == -1) ? true : false) : (item.is_default ? true : false);

                        if (is_check_config_default) {
                            let is_set_default = $('<span/>').addClass('label-default').html('Default');

                            let print_config_action_default = $('<div />').addClass('print-default').append(is_set_default).appendTo(print_config_action);
                        } else {
                            let is_set_default = $('<span/>').addClass('set-default').append($.renderIcon('set-default-icon'));
                            let print_config_action_default = $('<div />').addClass('print-default btn').attr('data-key', config_key).append(is_set_default).appendTo(print_config_action).click(function () {
                                __setPrintConfigDefault(variant, $(this).attr('data-key'));
                                _this.__renderPrintTemplateConfigByVariantID(variant, true);
                            });
                        }

                        let print_config_action_edit = $('<div />').addClass('btn btn-small btn-icon').append($.renderIcon('pencil')).appendTo(print_config_action).click(function () {
                            let PrintTemplate_From = new __setUpPrintTemplate(config_key, variant);
                        });

                        let print_config_action_delete = $('<div />').addClass('btn btn-small btn-icon').append($.renderIcon('trash-alt-regular')).appendTo(print_config_action).click(function () {
                            variant_config[variant.data.id].splice(config_key, 1);
                            _this.__renderPrintTemplateConfigByVariantID(variant, true);
                        });

                        for (let [key, item] of Object.entries(print_template)) {
                            let _found = true, _class = 'full-data', _text = 'Ready!', _icon = 'verified',
                                _icon_variant = 'list-verified';
                            let designIds = [];

                            let item_source = _coppyData(item.segments.source);

                            item_source.forEach(function (source, key) {
                                if (variant.data.design_id.includes(source.design_id.toString())) {
                                    designIds.push(source.design_id);
                                }
                            });

                            let designList = _renderDesignId(designIds);

                            let _key = 'printTemplate_' + key,
                                _id = item.print_template_image_id;

                            if (Object.keys(item.segments.source).length <= 0) {
                                _text = "Lack of data";
                                _icon = 'warning';
                                _found = false;
                                _class = 'enough-data';
                                _icon_variant = 'warning';
                            }

                            let itemRow = $('<div />').addClass('template-row ' + _class + ' ' + _key).attr({
                                'data-id': _id,
                                'data-key': _key,
                                'data-title': item.title
                            });

                            if (config_key == 0 && current_config_key == -1) current_config_key = 0;

                            if (current_design_key == key && config_key == current_config_key) itemRow.addClass('active');

                            $('<span />').addClass('print-info').html('<span class="print-template-title" title="' + item.title + '">' + item.title + '</span>').prepend($('<span />').addClass('design-ids').html(designList)).prepend($('<svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
                                '<path d="M0.76314 0.75L9.01314 5.51314L0.76314 10.2763L0.76314 0.75Z" fill="#2684FE"/>\n' +
                                '</svg>\n').addClass('triangle')).append($('<input />').addClass('print-template-config').attr({
                                type: 'hidden',
                                value: _key,
                                'data-title': item.title
                            })).appendTo(itemRow);

                            $('<span />').addClass('print-template-status').text(_text).append($.renderIcon(_icon)).appendTo(itemRow);

                            variant_status.html($.renderIcon(_icon_variant)).addClass('list-verified');

                            let image_url = '';

                            itemRow.click(function () {
                                current_design_key = key;
                                current_config_key = config_key;
                                current_variant = variant;

                                let _item = variant_config[variant.data.id][current_config_key].print_template_config[current_design_key];
                                design_scene.trigger('renderDesignScene', {item: _item, variant});
                                uploader_design.trigger('update-status', {is_browse: true});

                                tool_panel.resetStatus();

                                $('.template-row').removeClass('active');
                                $(this).addClass('active');
                                design_data_design.html(item.title).show();
                            }).appendTo(printSupplier);
                        }
                    });
                }

                function __setPrintConfigDefault(variant, config_key) {
                    variant_config[variant.data.id].forEach(function (item, key) {
                        if (key == config_key) {
                            variant_config[variant.data.id][config_key].is_default = true;
                        } else {
                            variant_config[variant.data.id][key].is_default = false;
                        }
                    });
                }

                let __setUpPrintTemplate = function (print_config_id, variant) {
                    let form_print_template = 'modal_print_template', _this_print = this;

                    let error = [];

                    let data_info = variant_config[variant.data.id] && variant_config[variant.data.id][print_config_id] ? variant_config[variant.data.id][print_config_id] : {};

                    let _type_setup = print_config_id < 0 ? 'create' : 'update';

                    $.unwrapContent(form_print_template);

                    let modal = $('<form />').addClass('osc-modal').width(1000);

                    let header = $('<header />').appendTo(modal);
                    $('<div />').addClass('title').html('Setup print template config for variant #' + variant.data['id']).appendTo($('<div />').addClass('main-group').appendTo(header));

                    $('<div />').addClass('close-btn').click(function () {
                        $.unwrapContent(form_print_template);
                    }).appendTo(header);

                    var modal_body = $('<div />').css('padding-top', 0).addClass('body post-frm').appendTo(modal);
                    $('<ul />').addClass('message-error').css('margin', '0px').appendTo(modal_body);

                    let print_list_selected = [], print_id_selected = [], supplier_label = null, supplier_select = null,
                        print_title = null, custom_shape = null, custom_shape_black_line_select = null;

                    let input_id_item = $('<input />').attr({
                        'name': 'print_id',
                        'id': 'input-id-item',
                        'value': 1,
                        'type': 'hidden'
                    });
                    $('<div />').addClass('frm-grid').append(input_id_item).appendTo(modal_body);

                    const STATE_CUSTOM_SHAPE = {
                        'ON' : 1,
                        'OFF' : 0
                    }

                    let custom_shape_default = {
                        'is_enable': STATE_CUSTOM_SHAPE['OFF'],
                        "black_line": {
                            "type": "vertical"
                        },
                        'red_line': {
                            "type": "single"
                        }
                    };

                    let print_template_config = {
                        'title': '',
                        'supplier': [],
                        'print_template_config': {},
                        'is_default': false,
                    };

                    this.renderTitle = async function () {
                        let print_label = $('<label />').attr({
                            'for': 'input-title',
                            'class': 'required'
                        }).text('Title').appendTo(modal_body);
                        print_title = $('<input />').addClass('styled-input').attr({
                            'name': 'title',
                            'id': 'input-title',
                            'value': data_info.title,
                            'required': true
                        }).change(function (e) {
                            // e.preventDefault();
                            print_template_config.title = $(this).val();
                        });

                        $('<div />').addClass('frm-grid').append(print_label).append(print_title).appendTo(modal_body);
                    }

                    this.renderSupplier = async function () {
                        let supplier_data = [];
                        let role_edit_supplier = false;
                        let sup_default = {'val' : 'print_way' , 'text' : 'Print Way'};

                        await $.ajax({
                            type: 'post',
                            url: $.base_url + '/catalog/backend_product/getSupplierForPrintConfigBeta',
                            data: {
                                'hash': OSC_HASH,
                            },
                        }).then(function (response) {
                            if (response.result !== 'OK') {
                                alert(response.message);
                                return;
                            }

                            response.data.supplier.forEach(function (supplier) {
                                supplier_data.push({val: supplier.data.ukey, text: supplier.data.title})
                            });

                            role_edit_supplier = response.data.role_edit_supplier ?? false;
                        });

                        supplier_label = $('<label />').attr({
                            'for': 'select-supplier',
                            'class': 'required'
                        }).text('Supplier').appendTo(modal_body);
                        supplier_select = $('<select />').addClass('styled-select').attr({
                            'name': 'supplier[]',
                            'id': 'select-supplier',
                            'multiple': 'multiple',
                            'size': 20,
                            'required': true
                        });

                        if (supplier_data.length < 1) {
                            console.error('Supplier Data is empty');
                            return;
                        }

                        sup_default = supplier_data.find(s => s.val === sup_default['val']) ? sup_default : supplier_data[Math.floor(Math.random() * supplier_data.length)];

                        $(supplier_data).each(function (key, item) {
                            const option_value = role_edit_supplier ? this.text : '****';

                            let option = $("<option>").attr('value', this.val).text(option_value);

                            if (data_info.supplier) {
                                data_info.supplier.forEach(function (sup, key) {
                                    if (item.val == sup) {
                                        option.attr('selected', 'selected')
                                    }
                                });
                            } else if (item.val == sup_default['val']) {
                                option.attr('selected', 'selected');
                            }

                            supplier_select.append(option);
                        });

                        $('<div />').addClass('select-print-template-config frm-grid select-supplier').append(supplier_label).append(supplier_select).appendTo(modal_body);

                        supplier_select.select2({
                            placeholder: "Please select supplier for print template"
                        }).change(function () {
                            print_template_config.supplier = $(this).val();
                        });

                        if (!role_edit_supplier) {
                            supplier_select.prop("disabled", true);
                        }
                    }

                    this.renderPrintTemplate = function () {
                        let print_select_label = $('<label />').attr({
                            'for': 'select-print-template',
                            'class': 'required'
                        }).text('Print template').appendTo(modal_body);

                        let print_data = [], page = 1, page_size = 5, data_id = 0, keywords = '';
                        let search_form_list = $('<div/>').addClass('search-form-list');
                        let search_form = $('<div/>').addClass('styled-search-form');
                        let $_this = this, is_search = false;
                        let search_input = $('<input/>').addClass('styled-input').attr({
                            'type': 'text',
                            'name': 'keywords',
                            'placeholder': 'Please enter keywords for search'
                        }).appendTo(search_form).keydown(function (e) {
                            if (e.key === 'Enter' || e.keyCode === 13) {
                                e.preventDefault();
                                keywords = search_input.val();

                                $_this.renderPrintTemplate();
                            }
                        });

                        search_form_list.append(search_form);

                        let print_list_scence = $('<div />').addClass('print-list-scene').addClass('review-images').appendTo(search_form_list);

                        let frm_print = $('<div />').addClass('frm-grid select-print-template').css({'display': 'block'}).append(print_select_label).append(search_form_list).appendTo(modal_body);

                        let print_selected_label = $('<label />').text('Selected').appendTo(frm_print).css({margin: "10px 5px 5px 5px"});

                        let print_list_selected_scence = $('<div />').addClass('print-list-scene-selected review-images').appendTo(frm_print);

                        if (data_info.print_template_config) {
                            Object.keys(data_info.print_template_config).map(function (key) {
                                let item = data_info.print_template_config[key];
                                let item_print_beta_id = parseInt(item.print_template_beta.id);
                                print_list_selected[item_print_beta_id] = data_info.print_template_config[key];
                                print_id_selected.push(item_print_beta_id);
                            });
                        }

                        this.renderPrintTemplate = async function () {
                            let search_form_list_loading = $('<div/>').addClass('search-form-list-loading').html($($.renderIcon('preloader'))).prependTo(search_form_list);
                            print_list_scence.children().remove();

                            let print_list = $('<div />').addClass('print-list').addClass('review-images').appendTo(print_list_scence);
                            print_data = (await this.getDataPrint()).data;
                            search_form_list_loading.remove();
                            if (print_data.items.length < 1) {
                                print_list_scence.html('<p style="text-align: center"> Print template not found</p>').addClass('not-found');
                                console.error('Can not get list print template beta');
                                return;
                            }

                            print_list_scence.removeClass('not-found');

                            print_data.items.map(item => {
                                data_id = item.id;
                                let image_item = $('<div />').addClass('image-item').appendTo(print_list);

                                image_item.attr({
                                    'data-id': data_id,
                                    'data-dimension': JSON.stringify(item.config.print_file.dimension),
                                    'data-img': item.config.print_file.print_file_url_thumb,
                                    'data-segments': print_list_selected[data_id] ? JSON.stringify({'source': print_list_selected[data_id].segments.source}) : null,
                                    'data-title': item.config.print_file.title
                                });

                                let thumb = $('<div />').addClass('thumb').appendTo(image_item).click(function () {
                                    let selected_id = parseInt(image_item.attr('data-id')),
                                        print_index_selected = print_id_selected.indexOf(selected_id);

                                    print_index_selected == -1 ? print_id_selected.push(selected_id) : print_id_selected.splice(print_index_selected, 1);

                                    if (print_index_selected == -1) {
                                        print_list_selected[image_item.attr('data-id')] = {
                                            'print_template_beta': {
                                                'dimension': JSON.parse(image_item.attr('data-dimension')),
                                                'id': image_item.attr('data-id'),
                                                'img': image_item.attr('data-img'),
                                            },
                                            'segments': image_item.attr('data-segments') ? JSON.parse(image_item.attr('data-segments')) : {'source': []},
                                            'title': image_item.attr('data-title')
                                        }
                                    } else {
                                        delete print_list_selected[image_item.attr('data-id')];
                                    }

                                    $('.image-item').find('.thumb').removeClass('selected');
                                    $('.image-item').find('.index').remove();

                                    print_id_selected.forEach(function (print_id, key) {
                                        let _thumb = $(".image-item[data-id='" + print_id + "']").find('.thumb');
                                        _thumb.addClass('selected');
                                        $('<div/>').addClass('index').html(key + 1).appendTo(_thumb);
                                    });

                                    $_this.renderPrintSelected();
                                });

                                let index_selected = print_id_selected.indexOf(data_id);

                                if (index_selected != -1) {
                                    thumb.addClass('selected');
                                    $('<div/>').addClass('index').html(index_selected + 1).appendTo(thumb);
                                }

                                let print_title = $('<div />').addClass('title').appendTo(image_item);

                                thumb.css('background-image', 'url(' + item.config.print_file.print_file_url_thumb + ')');
                                print_title.append(item.title);
                                image_item.attr('title', item.title);
                            });

                            $('.pagination-bar').remove();
                            let pagination = buildPager(print_data.current_page, print_data.total, print_data.page_size, null);
                            if (pagination) {
                                $('<div />').addClass('pagination-bar p10').append(pagination).appendTo(print_list_scence);

                                pagination.find('[data-page]:not(.current)').click(function (e) {
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                    page = this.getAttribute('data-page');
                                    $_this.renderPrintTemplate();
                                });
                            }
                        }

                        this.renderPrintSelected = function () {
                            print_list_selected_scence.children().remove();

                            if (Object.keys(print_id_selected).length < 1) {
                                print_list_selected_scence.html('No print template seleted').css('text-align', 'center');
                                return;
                            } else {
                                print_list_selected_scence.html('');
                            }

                            Object.keys(print_id_selected).map(function (key) {
                                let _print_id = print_id_selected[key];
                                let _print_item = print_list_selected[_print_id];
                                let _print_selected = $('<div />').addClass('print-list-selected').appendTo(print_list_selected_scence);
                                let _data_id = _print_item.print_template_beta.id;
                                let _image_item = $('<div />').addClass('image-item-selected').attr('data-id', _data_id).appendTo(_print_selected);
                                let thumb = $('<div />').addClass('thumb').appendTo(_image_item).click(function () {

                                });

                                let print_title = $('<div />').addClass('title').appendTo(_image_item);

                                thumb.css('background-image', 'url(' + _print_item.print_template_beta.img + ')');
                                print_title.append(_print_item.title);
                                _image_item.attr('title', _print_item.title);

                                const removeBtn = $('<span />').addClass("remove-item").appendTo(thumb);

                                removeBtn.click((e) => {
                                    e.stopPropagation();
                                    let _data_id = parseInt(_image_item.attr('data-id'));
                                    let index_selected_id = print_id_selected.indexOf(_data_id);
                                    print_id_selected.splice(index_selected_id, 1);
                                    delete print_list_selected[_data_id];
                                    $_this.renderPrintTemplate();
                                    $_this.renderPrintSelected();
                                })
                            });
                        }

                        this.getDataPrint = async function () {
                            return $.ajax({
                                type: 'post',
                                url: $.base_url + '/catalog/backend_PrintTemplateBeta/search',
                                data: {
                                    'keywords': keywords,
                                    'filter_field': 'all',
                                    'page': page,
                                    'page_size': page_size,
                                    'hash': OSC_HASH,
                                },
                            }).then(function (response) {
                                if (response.result !== 'OK') {
                                    alert(response.message);
                                    return;
                                }
                            });
                        }

                        this.renderPrintTemplate();
                        this.renderPrintSelected();
                    }

                    this.renderCustomShapeSetting = function () {
                        let custom_shape_enable_label = $('<label />').attr({
                            'for': 'enable_custom_shape',
                        }).text('Enable Custom Shape').appendTo(modal_body);

                        custom_shape = $('<input />').attr({
                            'type': 'checkbox',
                            'name': 'custom_shape',
                            'data-insert-cb': 'initSwitcher',
                        }).change(function () {
                            print_template_config.custom_shape.is_enable = parseInt($(this).val());
                        });

                        if (data_info.hasOwnProperty('custom_shape') && (data_info.custom_shape.hasOwnProperty('is_enable') && data_info.custom_shape.is_enable)) {
                            custom_shape.attr('checked', 'checked');
                        }

                        $('<div />').css({'margin-top': 10}).addClass('product-post-frm').append(custom_shape).append('<label class="label-inline ml10"> Render design according to custom shape processing flow </label>').appendTo(modal_body);

                        let custom_shape_black_line_type_label = $('<label />').attr({
                            'for': 'select-black-line-type'
                        }).text('Custom Shape Black Line Type').appendTo(modal_body);

                        custom_shape_black_line_select = $('<select />').addClass('styled-select').attr({
                            'name': 'black_line_type',
                            'id': 'select-black-line-type'
                        }).change(function () {
                            print_template_config.custom_shape.black_line.type = $(this).val();
                        });

                        let black_line_type_data = [
                            {
                                val: 'vertical',
                                text: 'Vertical'
                            },
                            {
                                val: 'horizontal',
                                text: 'Horizontal'
                            }
                        ];

                        $(black_line_type_data).each(function (key, item) {
                            let option = $("<option>").attr('value', this.val).text(this.text);

                            if (data_info.hasOwnProperty('custom_shape') && (data_info.custom_shape.hasOwnProperty('black_line') && data_info.custom_shape.black_line.type)) {
                                if (item.val == data_info.custom_shape.black_line.type) {
                                    option.attr('selected', 'selected')
                                }
                            }

                            custom_shape_black_line_select.append(option);
                        });

                        $('<div />').css({'margin-top': 10}).addClass('product-post-frm').css({'display': 'none'}).append(custom_shape_black_line_type_label).append(custom_shape_black_line_select).appendTo(modal_body);

                        custom_shape_black_line_select.select2({
                            placeholder: "Please select supplier for print template"
                        });
                    }

                    this.renderBodyModal = async function () {
                        await this.renderTitle();
                        await this.renderSupplier();
                        await this.renderPrintTemplate();
                        await this.renderCustomShapeSetting();
                    }

                    this.saveConfig = function () {
                        let supplier_dup = [];

                        let validate_list = {
                            'print_template_config': {
                                required: true,
                                text: 'Print template Beta'
                            },
                            'supplier': {
                                required: true,
                                text: 'Supplier'
                            },
                            'title': {
                                required: true,
                                text: 'Title'
                            },
                            'is_default': {
                                required: false,
                                text: 'Is Default'
                            },
                            'custom_shape' : {
                                required: false,
                                text: 'Custom Shape Status'
                            }
                        };

                        print_template_config.title = print_title.val().trim();

                        print_template_config.supplier = supplier_select.val();

                        Object.keys(print_id_selected).map(function (key) {
                            let _print_id = print_id_selected[key];
                            print_template_config.print_template_config['config_' + (parseInt(key) + 1)] = print_list_selected[_print_id]
                        });

                        Object.keys(print_template_config).forEach(function (item) {
                            if (validate_list[item].required && (!print_template_config[item] || (print_template_config[item] && Object.keys(print_template_config[item]).length <= 0))) error.push(validate_list[item].text + ' is empty');
                        });

                        if (print_template_config.supplier) {
                            print_template_config.supplier.forEach(function (supplier) {
                                if (variant_config[variant.data.id]) {
                                    variant_config[variant.data.id].forEach(function (config, key) {
                                        if (print_config_id == key) {
                                            return;
                                        }
                                        if (config.supplier.includes(supplier)) {
                                            supplier_dup.push(supplier)
                                        }
                                    });
                                }
                            });

                        }

                        if (supplier_dup.length > 0) {
                            error.push('Supplier [' + supplier_dup.join(" , ") + '] has been installed for this variant');
                        }

                        let custom_shape_value = parseInt(custom_shape.val());

                        if (custom_shape_value == STATE_CUSTOM_SHAPE['ON']) {
                            print_template_config.custom_shape = custom_shape_default;

                            print_template_config.custom_shape.is_enable = custom_shape_value;

                            print_template_config.custom_shape.black_line.type = custom_shape_black_line_select.val();
                        }

                        if (print_template_config.hasOwnProperty('custom_shape') && custom_shape_value == STATE_CUSTOM_SHAPE['OFF']) {
                            delete print_template_config.custom_shape;
                        }

                        $('.message-error').children().remove();

                        if (error.length > 0) {
                            let message_error = '';
                            error.forEach(function (message) {
                                message_error += '<li>' + message + '</li>';
                            })

                            $('.message-error').html(message_error);

                            $('.osc-wrap').animate({
                                scrollTop: 0
                            }, 300);
                            button_submit.removeAttr('disabled').find('svg').remove();
                            error = [];
                            return;
                        }

                        let _variant_config = variant_config;

                        if (variant_config[variant.data.id].length < 1) {
                            print_template_config.is_default = true;
                        }

                        if (_type_setup == 'create') {
                            if (!variant_config[variant.data.id]) {
                                variant_config[variant.data.id] = [print_template_config]
                            } else {
                                _variant_config[variant.data.id].push(print_template_config);
                                variant_config = _variant_config;
                            }
                        } else {
                            variant_config[variant.data.id][print_config_id] = print_template_config;
                        }

                        _this.__renderPrintTemplateConfigByVariantID(variant, true);

                        $.unwrapContent(form_print_template);
                    }

                    this.renderBodyModal();

                    let action_bar = $('<div />').addClass('action-bar').appendTo(modal);

                    $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                        $.unwrapContent(form_print_template);
                    }).appendTo(action_bar);

                    let button_submit = $('<button />').addClass('btn btn-primary ml10').attr('type', 'button').html('Save').appendTo(action_bar).click(function () {
                        _this_print.saveConfig();
                    });

                    $.wrapContent(modal, {key: form_print_template});
                    modal.moveToCenter().css('top', '100px');
                }

                this.__renderPrintTemplateConfig();
            }

            let _renderDesignId = function (designIds) {
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

            let __fetchDataVariantConfigCurrent = function () {
                return variant_config[current_variant.data['id']][current_config_key].print_template_config[current_design_key];
            }

            list_variant_config = new this.renderListVariantConfig();
        }

        btn_continue.click(function () {
            variant_tmp = __getVariantTmp();

            if (variant_tmp.status != 'OK') {
                alert(variant_tmp.message);
                return;
            }

            let variant_missing_design_id = verifyEditDesignID();

            if (variant_missing_design_id.length >= 1 && !is_continue && !is_status_alert) {
                showAlertDesignId("The design ids of the variants below have been changed, so the system will delete all related print template configs of these variants, you need to reset the print template config for the variants: " + variant_missing_design_id.join(), btn_continue, null, 'set_up_print');
                return;
            }

            variant_tmp = variant_tmp.data;

            new __renderSetUpContinue();
        });

        let icon_continue = $('<i/>').appendTo(btn_continue);

        $('<button />').addClass('btn btn-primary ml10').html('Update').click(function () {
            let variant_missing_design_id = verifyEditDesignID();

            if (variant_missing_design_id.length >= 1 && !is_continue) {
                showAlertDesignId("The design ids of the variants below have been changed, so the system will delete all related print template configs of these variants, you need to reset the print template config for the variants: " + variant_missing_design_id.join(), btn_continue, 'productVariantEditor');
                return;
            }

            saveVariants();
        }).appendTo(action_bar);

        let verifyEditDesignID = function () {
            let variant_missing_design_id = {};

            let __variants = _coppyData(variants);

            variants.forEach(function (variant, key) {
                if (!('meta_data' in variant.data)) {
                    return;
                }

                let show_alert = false;
                let new_design_ids = variant.row.find('.select--' + k).val();
                let old_design_ids = __variants[key].data['design_id'];
                let design_empty = !Array.isArray(new_design_ids) || new_design_ids.length < 0;
                let isset_variant_config = __variants[key].data.meta_data.variant_config && __variants[key].data.meta_data.variant_config.length >= 1;

                if (!isset_variant_config) {
                    return;
                }

                if (design_empty && old_design_ids.length > 0 && isset_variant_config) {
                    show_alert = true;
                }

                if (show_alert || !(new_design_ids.length == old_design_ids.length && new_design_ids.every((v, i) => old_design_ids.includes(v)))) {
                    let variant_title = '';
                    for (var i = 1; i <= 3; i++) {
                        if (__variants[key].data['option' + i] !== '') {
                            if (i > 1) {
                                variant_title += '-';
                            }
                            variant_title += __variants[key].data['option' + i];
                        }
                    }

                    variant_missing_design_id[variant.data.id] = variant_title;

                    if (!variants_reset[variants_reset.indexOf(variant.data.id)]) variants_reset.push(variant.data.id);
                }
            });

            return Object.values(variant_missing_design_id);
        }

        let saveVariants = function () {
            let error = [];

            variants.forEach(function (variant, key) {
                for (var k in headers) {
                    if (['design_id'].indexOf(k) >= 0) {
                        variant.data[k] = variant.row.find('.select--' + k).val();

                        if (variant.data[k] == null) {
                            variant.data[k] = [];
                        }

                    } else if (['shipping_price', 'shipping_plus_price'].indexOf(k) >= 0) {
                        var val = variant.row.find('.input--' + k).val();

                        variant.data[k] = val;

                        if (val != '' && (isNaN(Number(val)) || Number(val) < 0 || Number(val) > 10000)) {
                            error.push(headers[k]['title'])
                            continue;

                        }

                        if (!('meta_data' in variant.data) || variant.data.meta_data == null) {
                            variant.data.meta_data = {};
                        }

                        if (!('semitest_config' in variant.data.meta_data) || variant.data.meta_data.semitest_config == null) {
                            variant.data.meta_data.semitest_config = {};
                        }

                        if (!('variant_config' in variant.data.meta_data) || variant.data.meta_data.variant_config == null) {
                            variant.data.meta_data.variant_config = [];
                        }

                        variant.data['meta_data']['semitest_config'][k] = val;
                    } else {
                        var val = variant.row.find('.input--' + k).val();

                        if (val != '' && (isNaN(Number(val)) || Number(val) < 0 || Number(val) > 10000)) {
                            error.push(headers[k]['title'])
                            continue;
                        }

                        variant.data[k] = variant.row.find('.input--' + k).val();
                    }
                }

                if(is_continue) {
                    if(variant_config[variant.data.id]) variants[key].data.meta_data.variant_config = variant_config[variant.data.id];
                } else if (variants_reset.includes(variant.data.id)) {
                    variants[key].data.meta_data.variant_config = [];
                }

                variant.item.trigger('update', [variant.data]);
            });

            if (error.length > 0) {
                error  = error.filter((x, i, a) => a.indexOf(x) == i)
                alert(error.toString() + ' must be a number greater than 0 and less than 10.000');
                return;
            } else {
                $.unwrapContent('productVariantEditor');
            }
        }

        let showAlertDesignId = async function (content, btn_continue, key_edit_modal, type = "save_edit" ) {
            let __keyModal = 'alertModal';
            $.unwrapContent(__keyModal);

            let closeAlert = function () {
                $.unwrapContent(__keyModal);
            }

            is_status_alert = true;

            let modal = $('<div />').addClass('osc-modal').width(600),
                header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Warning').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                is_status_alert = false;
                closeAlert();
            }).appendTo(header);

            let modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            let message = $('<div />').html(content);
            message.appendTo(modal_body);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-primary ml10').html('Set up Print Template').click(function () {
                btn_continue.click();
                is_status_alert = false;
                closeAlert();
            }).css({'margin-right': "5px"}).appendTo(action_bar);

            if (type == 'save_edit') {
                $('<button />').addClass('btn btn-outline').attr('type', 'button').html('Save and Close').appendTo(action_bar).click(function () {
                    saveVariants();
                    is_status_alert = false;
                    closeAlert();
                    $.unwrapContent(key_edit_modal);
                });
            } else {
                $('<button />').addClass('btn btn-outline').attr('type', 'button').html('Cancel').appendTo(action_bar).click(function () {
                    is_status_alert = false;
                    closeAlert();
                });
            }

            $.wrapContent(modal, {key: __keyModal});

            modal.moveToCenter().css('top', '100px');
        }

        let __getVariantTmp = function() {
            let __error = [];
            let __variants = _coppyData(variants);

            variants.forEach(function (variant, key) {
                for (var k in headers) {
                    if (['design_id'].indexOf(k) >= 0) {
                        let __design_id = variant.row.find('.select--' + k).val();

                        if (__design_id == null || __design_id.length < 1) {
                            let __variant_title = '';

                            for (var i = 1; i <= 3; i++) {
                                if (variant.data['option' + i] !== '') {
                                    if (i > 1) __variant_title += '-';
                                    __variant_title += variant.data['option' + i];
                                }
                            }
                            __error.push('Variant [' + __variant_title + '] must have more than one design');
                        }

                        if (__variants[key].data[k] == null && __design_id == null) {
                            __variants[key].data[k] = [];
                        } else {
                            __variants[key].data[k] = __design_id
                        }
                    } else if (['shipping_price', 'shipping_plus_price'].indexOf(k) >= 0) {
                        var val = variant.row.find('.input--' + k).val();
                        __variants[key].data[k] = val;
                        if (val != '' && (isNaN(Number(val)) || Number(val) < 0 || Number(val) > 10000)) {
                            error.push(headers[k]['title'])
                            continue;

                        }
                        if (!('meta_data' in variant.data) || variant.data.meta_data == null) {
                            __variants[key].data.meta_data = {};
                        }

                        if (!('semitest_config' in __variants[key].data.meta_data) || variant.data.meta_data.semitest_config == null) {
                            __variants[key].data.meta_data.semitest_config = {};
                        }

                        if (!('variant_config' in __variants[key].data.meta_data) || variant.data.meta_data.variant_config == null) {
                            __variants[key].data.meta_data.variant_config = [];
                        }

                        __variants[key].data['meta_data']['semitest_config'][k] = val;
                    } else {
                        var val = variant.row.find('.input--' + k).val();

                        if (val != '' && (isNaN(Number(val)) || Number(val) < 0 || Number(val) > 10000)) {
                            error.push(headers[k]['title'])
                            continue;
                        }

                        __variants[key].data[k] = variant.row.find('.input--' + k).val();
                    }
                }
            });

            if (__error.length > 0) {
                let message = '';
                __error.forEach(function (error) {
                    message += error + '\n';
                })

                return {
                    'status': 'Error',
                    'data': [],
                    'message': message
                };
            }

            return {
                'status': 'OK',
                'data': __variants
            };
        }

        let _coppyData = function (data) {
            return JSON.parse(JSON.stringify(data));
        }

        // if (is_continue) {
        //     new __renderSetUpContinue();
        // }

        $.wrapContent(modal, {key: 'productVariantEditor', backdrop:'static'});
    }

    function _renderProductVariantV1(_data) {
        var data = {
            id: 'new.' + $.makeUniqid(),
            option1: '',
            option2: '',
            option3: '',
            image_id: '',
            video_id: [],
            shipping_price: 0,
            shipping_plus_price: 0,
            price: $('#input-price').val(),
            compare_at_price: $('#input-compare_at_price').val(),
            design_id: $('#select-design_id').val(),
            position : 0
        };

        if (Array.isArray(_data.image_id)) {
            _data.image_id = _data.image_id.join(',');
        }

        $.extend(data, _data);

        data.id += '';

        var default_data = data.id.indexOf('new.') === 0 ? null : {id: data.id, option1: data.option1.replaceAll('"', '\t&quot;'), option2: data.option2.replaceAll('"', '\t&quot;'), option3: data.option3.replaceAll('"', '\t&quot;')};

        var variant_list = $('.product-variant-list');

        if (variant_list.find('.product-variant[data-option1="' + data.option1.replaceAll('"', '\t&quot;') + '"][data-option2="' + data.option2.replaceAll('"', '\t&quot;') + '"][data-option3="' + data.option3.replaceAll('"', '\t&quot;') + '"]')[0]) {
            return;
        }

        var variant_item = $('<div />').addClass('product-variant').attr('data-variant', data.id).attr('data-position', data.position).appendTo(variant_list);

        variant_item.bind('update', function (e, _data) {
            if (_data !== null && typeof _data === 'object') {
                $.extend(data, _data);

                if (default_data !== null) {
                    if (data.option1.replaceAll('"', '\t&quot;') !== default_data.option1.replaceAll('"', '\t&quot;') || data.option2.replaceAll('"', '\t&quot;') !== default_data.option2.replaceAll('"', '\t&quot;') || data.option3.replaceAll('"', '\t&quot;') !== default_data.option3.replaceAll('"', '\t&quot;')) {
                        data.id = 'new.' + $.makeUniqid();
                    } else {
                        data.id = default_data.id;
                    }
                }
            }

            if (data.option1 === '' && data.option2 === '' && data.option3 === '') {
                variant_item.trigger('delete');
            } else {
                variant_item.find('input[type="hidden"]').remove();

                for (var k in data) {
                    if (k === 'id') {
                        continue;
                    } else if (k == 'design_id') {
                        if (data[k] != null && data[k].length > 0) {
                            for (const design_id of data[k]) {
                                $('<input />').attr({type: 'hidden', name: 'variants[' + data.id + '][' + k + '][]', value: design_id}).appendTo(variant_item);
                            }
                        } else {
                            $('<input />').attr({type: 'hidden', name: 'variants[' + data.id + '][' + k + ']]', value: '[]'}).appendTo(variant_item);
                                                    }
                    } else if(k =="meta_data") {
                        $('<input />').attr({type: 'hidden', name: 'variants[' + data.id + '][' + k + ']', value: JSON.stringify(data[k])}).appendTo(variant_item);
                    } else {
                        $('<input />').attr({type: 'hidden', name: 'variants[' + data.id + '][' + k + ']', value: data[k]}).appendTo(variant_item);
                    }
                }

                if (typeof data.image_id === 'number') {
                    data.image_id = data.image_id + '';
                } else if (typeof data.image_id !== 'string') {
                    data.image_id = '';
                }

                var image = _getProductImageList(data.image_id.split(',')[0]);
                const imageCount = data.image_id && data.image_id.split(',').length || 0;

                if (!image) {
                    data.image_id = '';
                    variant_item.find('.variant-image-item').css('background-image', '').empty();
                } else {
                    variant_item.find('.variant-image-item').css('background-image', 'url("' + image.url + '")').append(`<span class="variant-mockup-count">${imageCount}</span>`);
                }

                variant_item.find('.variant-video').remove();

                const videoData = window.__videoUploader__getData();
                const variantVideoIds = data?.video_id
                    ? Array.isArray(data.video_id)
                        ? data.video_id
                        : data.video_id.split(',')
                    : [];
                const video = variantVideoIds.length && videoData[variantVideoIds[0]] || null;
                const videoCount = variantVideoIds.length;

                if (video) {
                    variant_item.find('.variant-image').append(`<div class="variant-video"><video src="${video.url || ''}" poster="${video.thumbnail || ''}"></video><span class="variant-mockup-count">${videoCount}</span></div>`);
                }

                variant_item.find('.variant-price span').text(data.price);
                variant_item.find('.variant-compare-at-price span').text(data.compare_at_price);
                variant_item.find('.variant-option').remove();

                for (var i = 1; i <= 3; i++) {
                    variant_item.attr('data-option' + i, data['option' + i].replaceAll('"', '\t&quot;'));

                    if (data['option' + i] !== '') {
                        $('<div />').addClass('variant-option option' + i).append($('<span />').addClass('text-wrap').append($('<span />').attr('data-vidx', i).text(data['option' + i]).click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();

                            var option_idx = this.getAttribute('data-vidx');

                            var option_value = data['option' + option_idx];

                            var collection = variant_list.find('.product-variant[data-option' + option_idx + '="' + option_value.replaceAll('"', '\t&quot;') + '"]');

                            var checked = false;

                            collection.each(function () {
                                if (!$(this).find('input[type="checkbox"]')[0].checked) {
                                    checked = true;
                                    return false;
                                }
                            });

                            variant_list.find('.product-variant').removeClass('selected').each(function () {
                                $(this).find('input[type="checkbox"]')[0].checked = false;
                            });

                            if (checked) {
                                collection.addClass('selected').each(function () {
                                    $(this).find('input[type="checkbox"]')[0].checked = true;
                                });
                                $('#bulk_edit_variant').trigger('update');
                                $('#bulk_delete_variant').trigger('update');
                            }
                        }))).insertBefore(variant_item.find('.variant-price'));
                    }
                }

                if (variant_list.find('.product-variant[data-option1="' + data.option1.replaceAll('"', '\t&quot;') + '"][data-option2="' + data.option2.replaceAll('"', '\t&quot;') + '"][data-option3="' + data.option3.replaceAll('"', '\t&quot;') + '"]')[0] !== variant_item[0]) {
                    variant_item.trigger('delete');
                }
            }

            _updateProductVariantHeaderV1();
        }).bind('delete', function () {
            variant_item.remove();
            _updateProductVariantHeaderV1();
        }).bind('checkEdit', function (e, _data) {
            if (checkbox[0].checked) {
                _data.variants.push({item: variant_item, data: data});
            }
        });

        var checkbox = $('<input />').attr({type: 'checkbox'});

        checkbox.click(function (e) {
            e.stopPropagation();
            checkbox.trigger('update', [e.shiftKey]);
        }).bind('update', function (e, shift_key) {
            $('#bulk_edit_variant').trigger('update');
            $('#bulk_delete_variant').trigger('update');
            if (this.checked) {
                variant_item.addClass('selected');
            } else {
                variant_item.removeClass('selected');
            }

            if (!this.checked) {
                variant_list.removeAttr('data-last-click');
                return;
            }

            if (shift_key) {
                var last_click_id = variant_list.attr('data-last-click');

                if (last_click_id && last_click_id !== data.id) {
                    var last_click_variant_item = variant_list.find('.product-variant[data-variant="' + last_click_id + '"]');

                    if (last_click_variant_item[0]) {
                        var selector = (last_click_variant_item[0].compareDocumentPosition(variant_item[0]) & Node.DOCUMENT_POSITION_FOLLOWING) ? 'prev' : 'next';

                        var _variant_item = variant_item[selector]();

                        while (_variant_item.attr('data-variant') !== last_click_id) {
                            _variant_item.addClass('selected').find('input[type="checkbox"]')[0].checked = true;

                            var _variant_item = _variant_item[selector]();
                        }

                        return;
                    }
                }
            }

            variant_list.attr('data-last-click', data.id);
        });

        setCheckboxSelectAll(checkbox[0], 'variant');

        $('<div />').addClass('styled-checkbox').append(checkbox).append($('<ins />').append($.renderIcon('check-solid'))).appendTo($('<div />').addClass('variant-selector').appendTo(variant_item));
        $(`<div class="variant-image">
            <div class="variant-image-item"></div>
            <div class="variant-video"></div>
        </div>`)
            .on('click', 'div', function(e) {
                e.stopPropagation();
                _renderProductVariantImgSelector({item: variant_item, data: data});
            })
            .appendTo(variant_item);

        $('<div />').addClass('variant-price').append($('<span />').addClass('text-wrap').text(data.price)).appendTo(variant_item);
        $('<div />').addClass('variant-compare-at-price').append($('<span />').addClass('text-wrap').text(data.compare_at_price)).appendTo(variant_item);

        var control_bars = $('<div />').addClass('variant-actions').appendTo(variant_item);


        $('<div />').addClass('btn btn-small btn-icon').append($.renderIcon('pencil')).appendTo(control_bars).click(function (e) {
            e.stopImmediatePropagation();

            _renderVariantEditorV1([{item: variant_item, data: data}]);
        });

        // $('<div />').addClass('btn btn-small btn-icon ml5').append($.renderIcon('image-solid')).appendTo(control_bars).click(function (e) {
        //     e.stopImmediatePropagation();
        //
        //     // _renderVariantEditorV1([{item: variant_item, data: data}]);
        // });

        $('<div />').addClass('btn btn-small btn-icon ml5').append($.renderIcon('trash-alt-regular')).appendTo(control_bars).click(function (e) {
            e.stopImmediatePropagation();
            variant_item.trigger('delete');
        });

        $('.video-uploader')
            .on('uploader-update', function(e, videoData) {
                if (!data.video_id) return;

                const current_video_ids = Object.keys(videoData);
                let updateData = {};

                // Check video is removed
                let video_ids = [...data.video_id] || [];
                let video_positions = data.video_position;
                const variant_video_ids = data.video_id?.filter(variant_video_id => { 
                        const indexId = current_video_ids.indexOf(variant_video_id + '');
                        if (indexId == -1) {
                            const index = video_ids.indexOf();
                            video_ids.splice(index, 1);
                            video_positions.splice(index, 1);
                            return false;
                        }

                        return true;
                    }
                );

                if (variant_video_ids.length !== data.video_id?.length) {
                    updateData = {
                        video_id: variant_video_ids,
                        video_position: video_positions,
                    };
                }

                variant_item.trigger('update', [ updateData ]);
            });

        variant_item.click(function (e) {
            checkbox[0].checked = !checkbox[0].checked;
            checkbox.trigger('update', [e.shiftKey]);
        });

        variant_item.trigger('update', [{}]);
    }

    window.initProductMakeVariantBtnV1 = function () {
        var option_list = $('.product-option-list');
        $(this).unbind('update').bind("update", function() {
            const length = option_list.find('.product-option').length;

            if (!option_list[0] || length < 1) {
                $(this).addClass('hide-btn');
                return;
            }
            $(this).removeClass('hide-btn');
            $(this).click(function () {
                let options = getOptionList();

                options[1].forEach(function (x) {
                    options[2].forEach(function (y) {
                        options[3].forEach(function (z) {
                            _renderProductVariantV1({
                                option1: x,
                                option2: y,
                                option3: z
                            });
                        });
                    });
                });

                updateProductVariantPosition(options);
            });
        })

        $(this).trigger("update");
    };

    function _renderVariantEditor(variants) {
        var headers = {
            sku: {title: 'SKU'},
            price: {title: 'Price'},
            compare_at_price: {title: 'Compared price'},
            cost: {title: 'Cost per item'},
            require_shipping: {title: 'Require shipping', min_width: 31},
            require_packing: {title: 'Require packing', min_width: 31},
            keep_flat: {title: 'Keep flat', min_width: 31},
            weight: {title: 'Weight', min_width: 100},
            dimension_width: {title: 'Width (cm)'},
            dimension_height: {title: 'Height (cm)'},
            dimension_length: {title: 'Length (cm)'},
            track_quantity: {title: 'Track quantity', min_width: 31},
            quantity: {title: 'Quantity'},
            overselling: {title: 'Allow overselling', min_width: 31}
        };

        var modal = $('<div />').addClass('osc-modal product-variant-editor');

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html('Edit variants').appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('productVariantEditor');
        }).appendTo(header);

        var __setupResizeHandler = function (cell, min_width) {
            min_width = parseInt(min_width);

            if (isNaN(min_width) || min_width < 30) {
                min_width = 30;
            }

            $('<div />').addClass('spreadsheet__cell-resize-handler').appendTo(cell).mousedown(function (e) {
                e.preventDefault();

                var rect = cell[0].getBoundingClientRect();
                var click_coord = rect.width - (e.pageX - (rect.left + $(window).scrollLeft()));

                $(document.body).addClass('spreedsheet--resizing');

                var spreadsheet_parent = cell.closest('.spreadsheet').parent();

                $(document).bind('mousemove.sheetResize', function (e) {
                    e.preventDefault();

                    var rect = cell[0].getBoundingClientRect();
                    var container_rect = spreadsheet_parent[0].getBoundingClientRect();

                    var width = Math.min((container_rect.left + container_rect.width) - rect.left - 10, Math.max(min_width, e.pageX - (rect.left + $(window).scrollLeft()) + click_coord));

                    width += 'px';

                    cell.css({width: width, minWidth: width});
                }).bind('mouseup.sheetResize', function () {
                    e.preventDefault();

                    $(document).unbind('.sheetResize');

                    $(document.body).removeClass('spreedsheet--resizing');
                });
            });
        };

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        var spreadsheet = $('<div />').addClass('spreadsheet').appendTo($('<div />').addClass('spreadsheet-wrap').appendTo(modal_body));

        var sheet_header = $('<div />').addClass('spreadsheet__row spreadsheet__hover-highlight-disable').appendTo(spreadsheet);

        __setupResizeHandler($('<div />').text('Title').addClass('spreadsheet__cell spreadsheet__highlight cell--title').appendTo(sheet_header));

        for (var k in headers) {
            __setupResizeHandler($('<div />').text(headers[k].title).addClass('spreadsheet__cell spreadsheet__highlight cell--' + k).appendTo(sheet_header), headers[k].min_width);
        }

        variants.forEach(function (variant) {
            var row = $('<div />').addClass('spreadsheet__row').appendTo(spreadsheet);

            var title_cell = $('<div />').addClass('spreadsheet__cell spreadsheet__highlight cell--title').appendTo(row);

            for (var i = 1; i <= 3; i++) {
                if (variant.data['option' + i] !== '') {
                    $('<span />').addClass('option' + i).text(variant.data['option' + i]).appendTo(title_cell);
                }
            }

            for (var k in headers) {
                var cell = $('<div />').addClass('spreadsheet__cell cell--' + k).appendTo(row);

                if (['track_quantity', 'overselling', 'require_shipping', 'keep_flat', 'require_packing'].indexOf(k) >= 0) {
                    var checkbox = $('<input />').attr({type: 'checkbox', value: ''});
                    $('<div />').addClass('styled-checkbox').append(checkbox).addClass('input--' + k).append($('<ins />').append($.renderIcon('check-solid'))).appendTo(cell);
                    checkbox[0].checked = variant.data[k];
                } else if (['quantity'].indexOf(k) >= 0) {
                    $('<input />').attr({type: 'text', disabled: 'disabled'}).addClass('styled-input product-variant__input input--' + k).val(variant.data[k]).focus(function () {
                        this.select();
                    }).appendTo(cell);
                } else {
                    $('<input />').attr('type', 'text').addClass('styled-input product-variant__input input--' + k).val(variant.data[k]).focus(function () {
                        this.select();
                    }).appendTo(cell);

                    if (k === 'weight') {
                        var select = $('<select />').addClass('input--weight_unit');
                        $('<div />').addClass('styled-select').append(select).append($('<ins />')).appendTo(cell);

                        $.each(['g', 'kg', 'lb', 'oz'], function (_k, _v) {
                            $('<option />').attr({value: _v}).text(_v).appendTo(select);
                        });

                        select.val(variant.data.weight_unit);
                    }
                }
            }

            variant.row = row;
        });

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-secondary').html('Close').click(function () {
            $.unwrapContent('productVariantEditor');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html('Update').click(function () {
            $.unwrapContent('productVariantEditor');

            variants.forEach(function (variant) {
                for (var k in headers) {
                    if (['track_quantity', 'overselling', 'require_shipping', 'keep_flat', 'require_packing'].indexOf(k) >= 0) {
                        variant.data[k] = variant.row.find('.input--' + k + ' input[type="checkbox"]')[0].checked ? 1 : 0;
                    } else {
                        variant.data[k] = variant.row.find('.input--' + k).val();

                        if (k === 'weight') {
                            variant.data.weight_unit = variant.row.find('.input--weight_unit').val();
                        }
                    }
                }

                variant.item.trigger('update', [variant.data]);
            });
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'productVariantEditor'});
    }

    window.initProductMakeVariantBtn = function () {
        $(this).click(function () {
            var option_list = $('.product-option-list');

            if (!option_list[0]) {
                return;
            }

            var options = {};

            for (var i = 1; i <= 3; i++) {
                var option = option_list.find('.product-option[data-index=\'' + i + '\']');

                options[i] = [];

                if (option[0]) {
                    option.find('[data-value]').each(function () {
                        options[i].push(this.getAttribute('data-value'));
                    });
                } else {
                    options[i].push('');
                }
            }

            options[1].forEach(function (x) {
                options[2].forEach(function (y) {
                    options[3].forEach(function (z) {
                        _renderProductVariant({
                            option1: x,
                            option2: y,
                            option3: z
                        });
                    });
                });
            });
        });
    };

    window.productPostFrm__initVariantsV1 = function () {
        // get data edit
        var variants = JSON.parse(this.getAttribute('data-variants'));

        $.each(variants, function (k, variant) {
            _renderProductVariantV1(variant);
        });

        var data_request_variants = JSON.parse(this.getAttribute('data-request'));

        $.each(data_request_variants, function (k, variant) {
            _renderProductVariantV1(variant);
        });

        updateProductVariantPosition();
    };

    window.productPostFrm__initVariants = function () {
        var variants = JSON.parse(this.getAttribute('data-variants'));

        $.each(variants, function (k, variant) {
            _renderProductVariant(variant);
        });
    };

    function _productPostFrm_renderTag(tag) {
        return $('<div />').addClass('product-tag').attr('title', tag).text(tag).append($('<input />').attr({type: 'hidden', name: 'tags[]', value: tag})).append($('<ins />').click(function () {
            $(this).closest('.product-tag').remove();
        }));
    }

    function _productPostFrm_renderAsin(tag) {
        return $('<div />').addClass('product-tag').attr('title', tag).text(tag).append($('<input />').attr({type: 'hidden', name: 'asin[]', value: tag})).append($('<ins />').click(function () {
            $(this).closest('.product-tag').remove();
        }));
    }

    window.productPostFrm__addAsin = function (tag, input) {
        input.val('');

        tag = tag.trim();

        const container = input.closest('.frm-grid').find('.product-tags');

        const tags = {};

        container.find('input[type="hidden"]').each(function () {
            tags[this.value.toLowerCase()] = $(this).closest('.product-tags');
        });

        if (typeof tags[tag.toLowerCase()] !== 'undefined') {
            container.prepend(tags[tag.toLowerCase()]);
        } else {
            container.append(_productPostFrm_renderAsin(tag));
        }
    };

    window.productPostFrm__addTag = function (tag, input) {
        input.val('');

        tag = tag.trim();

        var container = input.closest('.frm-grid').find('.product-tags');

        var tags = {};

        container.find('input[type="hidden"]').each(function () {
            tags[this.value.toLowerCase()] = $(this).closest('.product-tag');
        });

        if (typeof tags[tag.toLowerCase()] !== 'undefined') {
            container.prepend(tags[tag.toLowerCase()]);
        } else {
            container.append(_productPostFrm_renderTag(tag));
        }
    };

    window.productPostFrm__initTags = function () {
        $(this).find('input[type="text"]').keydown(function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        });

        var tags = JSON.parse(this.getAttribute('data-tags'));

        if (tags === null) {
            return;
        }

        var container = $(this).find('.product-tags');

        $.each(tags, function (k, tag) {
            container.append(_productPostFrm_renderTag(tag));
        });
    };

    window.productPostFrm__initAsin = function () {
        $(this).find('input[type="text"]').keydown(function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        });

        const tags = JSON.parse(this.getAttribute('data-asin'));

        if (tags === null) {
            return;
        }

        const container = $(this).find('.product-tags');

        $.each(tags, function (k, tag) {
            container.append(_productPostFrm_renderAsin(tag));
        });
    };

    window.catalogCollectionPostFrm_initCollectMethodSwitch = function () {
        $(this).click(function () {
            $('#collection-config--' + (this.value === 'manual' ? 'manual' : 'auto'))[this.checked ? 'show' : 'hide']();
            $('#collection-config--' + (this.value === 'manual' ? 'auto' : 'manual'))[this.checked ? 'hide' : 'show']();
        });
    };

    var _catalogCollectionPostFrm_conditionAutocompleteData = {
        type: null,
        vendor: null,
        tag: null
    };

    function _catalogCollectionPostFrm_conditionAutocompleteDataLoader(field_key, callback) {
        if (!Array.isArray(_catalogCollectionPostFrm_conditionAutocompleteData[field_key])) {
            var source_url = $('#collection-config--auto').attr('data-' + field_key + '-autocomplete-source-url');

            if (!source_url) {
                _catalogCollectionPostFrm_conditionAutocompleteData[field_key] = [];
            } else {
                return $.ajax({
                    url: source_url,
                    success: function (response) {
                        if (response.result !== 'OK') {
                            _catalogCollectionPostFrm_conditionAutocompleteData[field_key] = [];
                        } else {
                            _catalogCollectionPostFrm_conditionAutocompleteData[field_key] = response.data;
                        }

                        callback(_catalogCollectionPostFrm_conditionAutocompleteData[field_key]);
                    }
                });
            }
        }

        return callback(_catalogCollectionPostFrm_conditionAutocompleteData[field_key]);
    }

    function catalogCollectionPostFrm_renderConditionFrm(condition) {
        var fields = {
            title: {title: 'Product title', skip_operators: ['greater_than', 'less_than', 'created_x_day_ago']},
            type: {title: 'Product type', skip_operators: ['greater_than', 'less_than', 'created_x_day_ago']},
            vendor: {title: 'Product vendor', skip_operators: ['greater_than', 'less_than', 'created_x_day_ago']},
            price: {title: 'Product price', skip_operators: ['starts_with', 'ends_with', 'contains', 'not_contains', 'created_x_day_ago']},
            compare_at_price: {title: 'Compare at price', skip_operators: ['starts_with', 'ends_with', 'contains', 'not_contains', 'created_x_day_ago']},
            topic: {title: 'Topic', skip_operators: ['greater_than', 'less_than', 'created_x_day_ago']},
            tag: {title: 'Product tag', skip_operators: ['greater_than', 'less_than', 'starts_with', 'ends_with', 'contains', 'not_contains', 'created_x_day_ago']},
            added_timestamp: {title: 'Created date', skip_operators: ['equals', 'not_equals', 'greater_than', 'less_than', 'starts_with', 'ends_with', 'contains', 'not_contains']}
        };

        var operators = {
            equals: 'is equal to',
            not_equals: 'is not equal to',
            greater_than: 'is greater than',
            less_than: 'is less than',
            starts_with: 'starts with',
            ends_with: 'ends with',
            contains: 'contains',
            not_contains: 'does not contains',
            created_x_day_ago: 'x day ago'
        };

        if (typeof condition === 'undefined') {
            condition = {field: '', operator: '', value: ''};
        }

        var condition_id = $.makeUniqid();

        var row = $('<div />').addClass('frm-grid frm-grid--middle').appendTo($('#collection-auto-conditions'));

        var col = $('<div />').appendTo(row);

        var field_select = $('<select />').attr('name', 'condition[conditions][' + condition_id + '][field]').appendTo($('<div />').addClass('styled-select').append($('<ins />')).appendTo(col)).change(function () {
            var field_key = this.options[this.selectedIndex].value;

            var selected_operator = condition.operator;

            if (operator_select.find('option')[0]) {
                selected_operator = operator_select.val();
            }

            if (typeof fields[field_key].skip_operators === 'undefined') {
                fields[field_key].skip_operators = [];
            }

            operator_select.html('');

            $.each(operators, function (operator, title) {
                if (fields[field_key].skip_operators.indexOf(operator) >= 0) {
                    return;
                }

                var option = $('<option />').attr('value', operator).text(title).appendTo(operator_select);

                if (selected_operator === operator) {
                    option.attr('selected', 'selected');
                }
            });

            var value = condition.value;

            var input = input_col.find('input[type="text"]');

            if (input[0]) {
                value = input.val();
            }

            input = $('<input />').attr({type: 'text', name: 'condition[conditions][' + condition_id + '][value]', value: value});

            input_col.html('');

            if (typeof _catalogCollectionPostFrm_conditionAutocompleteData[field_key] !== 'undefined') {
                $('<div />').addClass('styled-autocomplete-popover').appendTo(input_col).append(input).append($('<ins />').attr('data-autocomplete-popover-toggler', 1)).osc_autocompletePopover({
                    source_callback: function (callback) {
                        _catalogCollectionPostFrm_conditionAutocompleteDataLoader(field_key, callback);
                    }
                });
            } else {
                input.addClass('styled-input').appendTo($('<div />').appendTo(input_col));
            }
        });

        $.each(fields, function (field_key, field_data) {
            var option = $('<option />').attr('value', field_key).text(field_data.title).appendTo(field_select);

            if (field_key === condition.field) {
                option.attr('selected', 'selected');
            }
        });

        $('<div />').addClass('separate').appendTo(row);

        col = $('<div />').appendTo(row);

        var operator_select = $('<select />').attr('name', 'condition[conditions][' + condition_id + '][operator]').appendTo($('<div />').addClass('styled-select').append($('<ins />')).appendTo(col));

        $('<div />').addClass('separate').appendTo(row);

        var input_col = $('<div />').appendTo(row);

        field_select.trigger('change');

        catalogCollectionPostFrm_checkConditionRemoveBtn();
    }

    function catalogCollectionPostFrm_checkConditionRemoveBtn() {
        var list = $('#collection-auto-conditions');

        if (list[0].childNodes.length > 1) {
            list.find('> *').each(function () {
                var row = $(this);

                if (row.find('[data-condition-rmv="1"]')[0]) {
                    return;
                }

                $('<div />').addClass('separate').attr('data-condition-rmv', '1').appendTo(row);

                $('<div />').addClass('btn btn-icon btn-small').append($.renderIcon('trash-alt-regular')).click(function () {
                    row.remove();
                    catalogCollectionPostFrm_checkConditionRemoveBtn();
                }).appendTo($('<div />').css('max-width', '30px').attr('data-condition-rmv', '1').appendTo(row));

            });
        } else {
            list.find('[data-condition-rmv="1"]').remove();
        }
    }

    window.catalogCollectionPostFrm_initAddConditionBtn = function () {
        $(this).click(function () {
            catalogCollectionPostFrm_renderConditionFrm();
        });
    };

    window.catalogCollectionPostFrm_initAutoConditions = function () {
        var container = $('#collection-config--auto');

        var conditions = JSON.parse(this.getAttribute('data-conditions'));

        if (conditions !== null) {
            $.each(conditions, function (k, condition) {
                catalogCollectionPostFrm_renderConditionFrm(condition);
            });
        }

        $(document).ready(function () {
            if ($('#collection-auto-conditions')[0].childNodes.length < 1) {
                $('#collection-auto-add-condition-btn').trigger('click');
            }
        });
    };

    function description_length(){
        var inp_seo_description = document.getElementById('input-seo-description');
        if (inp_seo_description.value.length < 160){
            document.getElementById('warning_character_description').innerHTML = 'The current length ('+ inp_seo_description.value.length +').The ideal length of meta seo description is 160 -300';
            document.getElementById('warning_character_description').style.display = "block";
        }else if (inp_seo_description.value.length > 300){
            document.getElementById('warning_character_description').innerHTML = 'The current length ('+ inp_seo_description.value.length +').The ideal length of meta seo description is 160 -300';
            document.getElementById('warning_character_description').style.display = "block";
        }else if (inp_seo_description.value.length >= 160 && inp_seo_description.value.length <= 300) {
            document.getElementById('warning_character_description').innerHTML = '';
            document.getElementById('warning_character_description').style.display = "none";
        }
    }

    window.catalogCollectionPostFrm_initCollectProductManage = function () {
        var collection_id = parseInt(this.getAttribute('data-collection-id'));
        const frm_filter = $(this);

        var product_list_container = $(this).find('.product-list-wrap');
        var source_url = this.getAttribute('data-source-url');
        var add_to_collection_url = this.getAttribute('data-add-url');
        var remove_from_collection_url = this.getAttribute('data-remove-url');
        var update_collection_product_url = this.getAttribute('data-update-url');

        _loadCollectionProducts(collection_id, product_list_container, source_url, remove_from_collection_url);

        const browser = $('.item-browser').osc_ui_itemBrowser({
            browse_url: this.getAttribute('data-browse-url'),
            browse_params: {
                filter_field: 'all'
            },
            click_callback: function (item, product) {
                var checkbox = item.find('input[type="checkbox"]')[0];
                const newCheckedValue = $(checkbox).attr('check') == "true" ? false : true;
                setTimeout(function() {
                    checkbox.checked = newCheckedValue;
                }, 0)
                $(checkbox).attr('check', newCheckedValue);

                if (item[0].hasAttribute('data-update-marker')) {
                    item.removeAttr('data-update-marker');
                } else {
                    item.attr('data-update-marker', 1);
                }

                var product_list = item.closest('.item-list');

                var action_bar = product_list.next('.update-collection-product-action-bar');

                if (product_list.find('> [data-update-marker]')[0]) {
                    if (!action_bar[0]) {
                        var $this = this;

                        action_bar = $('<div />').addClass('update-collection-product-action-bar').insertAfter(product_list);

                        $('<div />').addClass('btn btn-outline').text('Cancel').click(function () {
                            product_list.find('> [data-update-marker]').each(function () {
                                this.removeAttribute('data-update-marker');

                                var checkbox = $(this).find('input[type="checkbox"]')[0];

                                checkbox.checked = !checkbox.checked;
                            });

                            action_bar.remove();

                            $this._removePopup();
                        }).appendTo(action_bar);

                        $('<div />').addClass('btn btn-primary btn-small ml10').text('Update').click(function () {
                            var remove_ids = [];
                            var add_ids = [];

                            product_list.find('> [data-update-marker]').each(function () {
                                this.removeAttribute('data-update-marker');

                                if ($(this).find('input[type="checkbox"]')[0].checked) {
                                    add_ids.push(this.getAttribute('product-id'));
                                } else {
                                    remove_ids.push(this.getAttribute('product-id'));
                                }
                            });

                            action_bar.remove();

                            $.ajax({
                                type: 'post',
                                url: update_collection_product_url,
                                data: {id: collection_id, remove: remove_ids, add: add_ids},
                                success: function (response) {
                                    if (response.result !== 'OK') {
                                        alert(response.message);
                                        return;
                                    }

                                    $this._browse($this._current_page, true);

                                    _loadCollectionProducts(collection_id, product_list_container, source_url, remove_from_collection_url);
                                }
                            });
                        }).appendTo(action_bar);

                        this._positionPopup();
                    }
                } else {
                    if (action_bar[0]) {
                        action_bar.remove();
                        this._positionPopup();
                    }
                }
            },
            item_render_callback: function (item, product) {
                item.attr('product-id', product.id).addClass('collection-manage-product-item');

                const is_checked = product.collection_ids && product.collection_ids.indexOf(collection_id) >= 0;
                let checkbox = $('<input />').attr({type: 'checkbox', tabIndex: -1, check: is_checked }).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo($('<div />').addClass('checker').prependTo(item)));

                if (is_checked) {
                    checkbox[0].checked = true;
                }

                checkbox.click(function (e) {
                    e.preventDefault();
                });
            },
        })

        let filter_field = [];
        const menu_field = $('<div />').addClass('filter-frm filter-frm-field');

        function __renderFilterField() {
            menu_field.html('');

            menu_field.appendTo(document.body);
            const selected_field = $('#selected_field').val()
            const default_field = $.cookie(default_search_field_key)

            filter_field.forEach(function (item) {
                const container = $('<div />').addClass('filter-element' + (item.key === selected_field ? ' selected' : '') + (item.key === default_field ? ' default' : '')).appendTo(menu_field);
                const btn_default = $('<span />').addClass('btn-default').append($.renderIcon('set-default-icon')).click(function () {
                    $(this).hide();
                    $.cookie(default_search_field_key, item.key);
                    $('.filter-frm-field .filter-element').each(function () {
                        const $this = $(this)
                        if ($this.hasClass('default')) {
                            $this.removeClass('default');
                        }

                        if ($this.find('.title').attr('key') === item.key) {
                            $this.addClass('default');
                        }
                    })
                    $('title-' + item.key).trigger('click');
                    $('#selected_field').val(item.key);
                    $('#lbl_selected_field').text(item.value);
                })

                $('<div />').addClass('title').attr('id', 'title-' + item.key).attr('key', item.key).text(item.value)
                    .append($('<span />').addClass('label-selected').append($.renderIcon('select-icon')))
                    .append($('<span />').addClass('label-default').text('Default'))
                    .append(btn_default)
                    .appendTo(container)
                    .mouseover(function () {
                        $('.btn-default').each(function () {
                            $(this).hide()
                        })

                        if (!$(this).parent().hasClass('default')) {
                            $(this).find('.btn-default').show()
                        }
                    })
                    .mouseleave(function () {
                        $('.btn-default').each(function () {
                            $(this).hide()
                        })
                    })
                    .click(function () {
                        $('#selected_field').val(item.key);
                        $('#lbl_selected_field').text(item.value);
                        $('.filter-frm-field .filter-element').each(function () {
                            const $this = $(this)
                            if ($this.hasClass('selected')) {
                                $this.removeClass('selected');
                            }

                            if ($this.find('.title').attr('key') === item.key) {
                                $this.addClass('selected');
                            }
                        });

                        browser.osc_ui_itemBrowser('getInstance').setBrowseParams({filter_field: item.key})
                        browser.osc_ui_itemBrowser('getInstance')._browse(1, true)
                    });
            });
        }

        $.each(fetchJSONTag(frm_filter, 'filter-field'), function (key, value) {
            filter_field.push({key: key, value: value});
        });

        frm_filter.find('button.filter_field').osc_toggleMenu({
            menu: menu_field,
            divergent_y: 5,
            toggle_mode: 'bl',
            open_hook: function (params) {
                __renderFilterField();
            },
            close_hook: function () {
                menu_field.html('');
                menu_field.detach();
            }
        });
    };

    function _loadCollectionProducts(collection_id, container, url, remove_url, page) {
        container.find('.product-list').remove();
        container.find('.pagination-bar').remove();

        $('<div />').addClass('loading').text('Loading...').prepend($.renderIcon('preloader')).appendTo(container);

        $.ajax({
            type: 'post',
            url: url,
            data: {id: collection_id, page: page},
            success: function (response) {
                container.find('.loading').remove();

                if (response.result !== 'OK') {
                    alert(response.message);
                    return;
                }

                if (response.data.products.length < 1) {
                    return;
                }

                var list = $('<div />').addClass('product-list').appendTo(container);

                $.each(response.data.products, function (k, product) {
                    var item = $('<div />').addClass('product-item').appendTo(list);

                    $('<div />').addClass('product-image-preview').css('background-image', 'url(' + product.image_url + ')').appendTo(item);
                    $('<div />').addClass('product-name').text(product.title).appendTo(item);
                    $('<div />').addClass('remove-btn').appendTo(item).click(function () {
                        var btn = $(this);

                        if (btn.attr('disabled') === 'disabled') {
                            return;
                        }

                        btn.attr('disabled', 'disabled');

                        $.ajax({
                            type: 'post',
                            url: remove_url,
                            data: {id: product.id, collection_id: collection_id},
                            success: function (response) {
                                btn.removeAttr('disabled');

                                if (response.result !== 'OK') {
                                    alert(response.message);
                                    return;
                                }

                                _loadCollectionProducts(collection_id, container, url, remove_url, page);
                            }
                        });
                    });
                });

                var pagination = buildPager(response.data.current_page, response.data.collection_length, response.data.page_size, {section: 4, small: true});

                if (pagination) {
                    $('<div />').addClass('pagination-bar p10').append(pagination).appendTo(container);
                    pagination.find('[data-page]:not(.current)').click(function (e) {
                        e.preventDefault();
                        _loadCollectionProducts(collection_id, container, url, remove_url, this.getAttribute('data-page'));
                    });
                }
            }
        });
    }

    window.initCatalogEditEmailOrder = function () {
        var self = $(this);
        $(this).blur(function () {
            if ($(this).val().trim() === '' || !$.validator.validEmail($(this).val().trim())) {
                self.closest('.minified-input').addClass('error').append($('<span />').addClass('error__message').text('Email is incorrect format'));
                return;
            } else {
                self.closest('.minified-input').removeClass('error').find('.error__message').remove();
            }
        });
    };

    window.catalogEditVariantFrmInit = function () {
        var form = $(this);
        var container = form.closest('[data-product-section]');

        var cart_form_data = JSON.parse(form.closest('[data-product-section]').find('script[data-json="cart-form-data"]')[0].innerHTML);

        var option_keys = [];

        form.find('[data-option]').each(function () {
            option_keys.push(this.getAttribute('data-option'));
        });

        form.bind('update', function (e, updated_key) {
            var option_selected = {};

            form.trigger('collectOptionsSelected', [option_selected]);

            var keys = [];

            if (/^option[1-3]$/.test(updated_key)) {
                keys.push(updated_key);
            }

            option_keys.forEach(function (option_key) {
                if (option_key === updated_key) {
                    return;
                }

                keys.push(option_key);
            });

            var variants = $.extend([], cart_form_data.variants);

            var selected_variant = null;

            keys.forEach(function (option_key) {
                var buff = [];

                variants.forEach(function (variant) {
                    if (variant[option_key] === option_selected[option_key]) {
                        buff.push(variant);
                    }
                });

                if (buff.length < 1) {
                    option_selected[option_key] = variants[0][option_key];

                    variants.forEach(function (variant) {
                        if (variant[option_key] === option_selected[option_key]) {
                            buff.push(variant);
                        }
                    });
                }

                variants = buff;
            });

            selected_variant = variants[0];

            var variants = $.extend([], cart_form_data.variants);

            option_keys.forEach(function (option_key) {
                var option_values = [];

                var buff = [];

                variants.forEach(function (variant) {
                    if (option_values.indexOf(variant[option_key]) < 0) {
                        option_values.push(variant[option_key]);
                    }

                    if (variant[option_key] === option_selected[option_key]) {
                        buff.push(variant);
                    }
                });

                form.find('[data-option="' + option_key + '"]').trigger('setter', [option_values, option_selected[option_key]]);

                variants = buff;
            });

            form.find('input[name="variant_id"]').val(selected_variant.id);

            container.find('.product-detail__price').html(catalogProductRenderPrice(selected_variant, true));

            var selected_img_id = container.find('.product-gallery__thumbs [data-id].selected').attr('data-id');

            if (selected_variant.images.length > 0) {
                container.find('.product-gallery__thumbs [data-id]').attr('disabled', 'disabled');
                container.find('.product-gallery__images [data-id]').attr('disabled', 'disabled');

                selected_variant.images.forEach(function (image) {
                    container.find('.product-gallery__images [data-id="' + image.id + '"]').removeAttr('disabled');
                    container.find('.product-gallery__thumbs [data-id="' + image.id + '"]').removeAttr('disabled');
                });
            } else {
                container.find('.product-gallery__images [data-id]').removeAttr('disabled');
                container.find('.product-gallery__thumbs [data-id]').removeAttr('disabled');
            }

            if (selected_img_id && container.find('.product-gallery__thumbs [data-id="' + selected_img_id + '"]:not([disabled])')[0]) {
                container.find('.product-gallery__thumbs [data-id="' + selected_img_id + '"]').trigger('click');
            } else {
                container.find('.product-gallery__thumbs [data-id]:not([disabled])').first().trigger('click');
            }

            var variant_url = '';
            var matches = window.location.href.match(/^(.+[?\/&])variant([=\/])\d+([&\/#?].*)?$/i);

            if (matches) {
                variant_url = matches[1] + 'variant' + matches[2] + selected_variant.id + (typeof matches[3] === 'undefined' ? '' : matches[3]);
            } else {
                if (window.location.href.indexOf('?') < 0) {
                    variant_url = window.location.href.split('#', 2);
                    variant_url[0] += '?variant=' + selected_variant.id;
                    variant_url = variant_url.join('#');
                } else {
                    variant_url = window.location.href.split('?', 2);
                    variant_url[1] = 'variant=' + selected_variant.id + (variant_url[1].length > 0 ? '&' : '') + variant_url[1];
                    variant_url = variant_url.join('?');
                }
            }

            $.dynamicUrl.pushState({url: variant_url}, true);
        });

        $(window).ready(function () {
            form.trigger('update');
        });
    };

    window.catalogEditVariantFrmInitOptClothingSizeSelector = function () {
        var container = $(this);

        var selector = container.find('select');

        var preview_data = JSON.parse(container.closest('.catalog-cart-frm').find('script[data-json="cart-form-data"]')[0].innerHTML);

        selector.change(function () {
            selector.closest('form').trigger('update', [this.getAttribute('data-option')]);
        }).bind('setter', function (e, option_availables, selected_value) {
            selector.html('');

            option_availables.forEach(function (option_value) {
                $('<option/>').attr('value', option_value).text(typeof preview_data[option_value] !== 'undefined' ? preview_data[option_value] : option_value).appendTo(selector);
            });

            selector.val(selected_value);
        });

        selector.closest('form').bind('collectOptionsSelected', function (e, options) {
            return options[selector.attr('data-option')] = selector.val();
        });
    };

    window.catalogEditVariantFrmInitOptProductTypeSelector = function () {
        var container = $(this);

        var selector = container.find('select');

        var preview_list = container.find('.selector-preview');

        var preview_data = JSON.parse(container.find('script[data-json="option-images"]')[0].innerHTML);

        var __preview_click_handler = function () {
            selector.val(this.getAttribute('data-value')).trigger('change');
        };

        selector.change(function () {
            selector.closest('form').trigger('update', [this.getAttribute('data-option')]);
        }).bind('setter', function (e, option_availables, selected_value) {
            selector.html('');
            preview_list.html('');

            option_availables.forEach(function (option_value) {
                $('<option/>').attr('value', option_value).text(option_value + (typeof preview_data[option_value] !== 'undefined' ? (' - ' + preview_data[option_value]['price']) : '')).appendTo(selector);

                var preview = $('<div />').attr('data-value', option_value).click(__preview_click_handler).appendTo(preview_list);

                if (typeof preview_data[option_value] !== 'undefined' && preview_data[option_value]['image']) {
                    preview.css('background-image', 'url(' + preview_data[option_value]['image'] + ')');
                }

                if (selected_value === option_value) {
                    preview.addClass('selected');
                }
            });

            selector.val(selected_value);
        });

        selector.closest('form').bind('collectOptionsSelected', function (e, options) {
            return options[selector.attr('data-option')] = selector.val();
        });

        preview_list.find('> div').click(__preview_click_handler);
    };

    window.catalogEditVariantFrmInitOptPosterSizeSelector = function () {
        var container = $(this);

        var selector = container.find('select');

        var preview_data = JSON.parse(container.find('script[data-json="option-images"]')[0].innerHTML);

        selector.change(function () {
            selector.closest('form').trigger('update', [this.getAttribute('data-option')]);
        }).bind('setter', function (e, option_availables, selected_value) {
            selector.html('');

            option_availables.forEach(function (option_value) {
                $('<option/>').attr('value', option_value).text(typeof preview_data[option_value] !== 'undefined' ? preview_data[option_value] : option_value).appendTo(selector);
            });

            selector.val(selected_value);
        });

        selector.closest('form').bind('collectOptionsSelected', function (e, options) {
            return options[selector.attr('data-option')] = selector.val();
        });
    };

    window.catalogEditVariantFrmInitOptSelector = function () {
        var selector = $(this);

        selector.change(function () {
            selector.closest('form').trigger('update', [this.getAttribute('data-option')]);
        }).bind('setter', function (e, option_availables, selected_value) {
            selector.html('');

            option_availables.forEach(function (option_value) {
                $('<option/>').attr('value', option_value).text(option_value).appendTo(selector);
            });

            selector.val(selected_value);
        });

        selector.closest('form').bind('collectOptionsSelected', function (e, options) {
            return options[selector.attr('data-option')] = selector.val();
        });
    };

    window.catalogProductRenderPrice = function (variant, display_saving) {
        var container = $('<span />').addClass('product-price');

        if (!variant.available) {
            $('<span />').addClass('product-price__sold-out').text('Sold out').appendTo(container);
        } else {
            if (variant.compare_at_price > variant.price) {
                $('<span />').addClass('product-price__money product-price__original-price').html(catalogFormatPriceByInteger(variant.compare_at_price)).appendTo(container);
            }

            $('<span />').addClass('product-price__money').html(catalogFormatPriceByInteger(variant.price)).appendTo(container);

            if (display_saving && variant.compare_at_price > variant.price) {
                var saving_price = variant.compare_at_price - variant.price;
                var saving_percent = $.round((saving_price * 100.0) / variant.compare_at_price, 2);

                $('<span />').addClass('product-price__saving').html('You save ' + saving_percent + '% (' + catalogFormatPriceByInteger(saving_price) + ')').appendTo(container);
            }
        }

        return container[0];
    };

    window.catalogInitProductSelector = function (config) {
        var selector_elm = $(this);

        config.attributes_extend = [
            {
                key: 'total_variant',
                position: 0,
                render: function (item, is_selected_item) {
                    var browser = this;

                    var attr_item = $('<div />').addClass('catalog-product-selector-variant-toggler');

                    if (!is_selected_item && item.total_variant > 0) {
                        attr_item.append($.renderIcon('chevron-down-light')).click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            e.stopPropagation();

                            if (attr_item.hasClass('toggled')) {
                                attr_item.removeClass('toggled').parent().parent().find('.catalog-product-selector-variant[data-item="' + item.id + '"]').remove();
                                return;
                            }

                            attr_item.addClass('toggled');

                            if (this.getAttribute('disabled') === 'disabled') {
                                return;
                            }

                            this.setAttribute('disabled', 'disabled');

                            $.ajax({
                                url: OSC_CATALOG_VARIANT_SELECTOR_BROWSE_URL,
                                data: {product: item.id},
                                success: function (response) {
                                    attr_item.removeAttr('disabled');

                                    if (response.result !== 'OK') {
                                        alert(response.message);
                                        return;
                                    }

                                    response.data.variants.forEach(function (variant) {
                                        var node = $('<a />').attr('href', 'javascript: void(0)').addClass('item catalog-product-selector-variant').attr('data-item', item.id).attr('data-variant', variant.id).insertAfter(attr_item.parent());

                                        var checkbox = $('<input />').attr({type: 'checkbox', tabIndex: -1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo($('<div />').addClass('checker').prependTo(node)));

                                        checkbox[0].checked = selector_elm.find('.osc-item-selector__selected-list > [data-variant="' + variant.id + '"]')[0] ? true : false;

                                        checkbox.mousedown(function (e) {
                                            e.preventDefault();
                                        });

                                        var options = $('<div />').addClass('variant-options').appendTo(node);

                                        $.each(item.options, function (option_key) {
                                            if (variant[option_key]) {
                                                $('<span />').addClass(option_key).text(variant[option_key]).appendTo(options);
                                            }
                                        });

                                        browser._initItemEvent(node, $.extend({variant: variant}, item));
                                    });
                                },
                                error: function () {
                                    alert('Variant load error, please try again');
                                    attr_item.removeAttr('disabled');
                                }
                            });
                        });
                    }

                    return attr_item;
                }
            },
            {
                key: 'total_variant',
                position: 5,
                render: function (item, is_selected_item) {
                    if (!is_selected_item && item.total_variant > 0) {
                        return $('<div />').addClass('catalog-product-selector-variant-counter').text(item.total_variant + ' variants').click(function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            e.stopPropagation();

                            $(this).parent().find('.catalog-product-selector-variant-toggler').trigger('click');
                        });
                    }
                }
            }
        ];

        config.click_handler = function (list_item, item, browser) {
            var $this = this;

            if (list_item) {
                var checkbox = list_item.find('input[type="checkbox"]')[0];

                if (!list_item.attr('data-variant')) {
                    list_item.parent().find('[data-item="' + item.id + '"][data-variant] input[type="checkbox"]').each(function () {
                        this.checked = false;
                    });

                    if (!checkbox.checked) {
                        this._selected_list.find('> [data-item="' + item.id + '"]').remove();
                    } else {
                        this._selected_list.find('> [data-item="' + item.id + '"][data-variant]').remove();
                        this._renderSelectedItem(list_item, item, browser);
                    }

                    return;
                }

                if (!checkbox.checked) {
                    this._selected_list.find('> [data-item="' + item.id + '"][data-variant="' + item.variant.id + '"]').remove();

                    if (!this._selected_list.find('> [data-item="' + item.id + '"][data-variant]')[0]) {
                        list_item.prevAll('[data-item="' + item.id + '"]').last().find('input[type="checkbox"]').removeAttr('data-indeterminate');
                        this._selected_list.find('> [data-item="' + item.id + '"]').remove();
                    }

                    return;
                }

                list_item.prevAll('[data-item="' + item.id + '"]').last().find('input[type="checkbox"]').attr('data-indeterminate', 1)[0].checked = false;
            } else if (!item.variant) {
                this._selected_list.find('> [data-item="' + item.id + '"][data-variant]').remove();
                this._renderSelectedItem(list_item, item, browser);
                return;
            }

            if (this._selected_list.find('> [data-item="' + item.id + '"][data-variant="' + item.variant.id + '"]')[0]) {
                return;
            }

            this._renderSelectedItem(list_item, item, browser).find('.remove-btn').unbind('.removeVariant').bind('click.removeVariant', function () {
                $this._selected_list.find('> [data-item="' + item.id + '"]').remove();
            });

            var node = $('<div />').addClass('catalog-selected-item catalog-product-selector-variant').attr('data-item', item.id).attr('data-variant', item.variant.id).insertAfter(this._selected_list.find('> [data-item="' + item.id + '"]').last());

            if (typeof this.input_name === 'string') {
                $('<input />').attr({type: 'hidden', name: this.input_name, value: item.id + ':' + item.variant.id}).appendTo(node);
            }

            var options = $('<div />').addClass('variant-options').appendTo(node);

            $.each(item.options, function (option_key) {
                if (item.variant[option_key]) {
                    $('<span />').addClass(option_key).text(item.variant[option_key]).appendTo(options);
                }
            });

            $('<div />').addClass('remove-btn').appendTo(node).click(function () {
                node.remove();

                if (!$this._selected_list.find('> [data-item="' + item.id + '"][data-variant]')[0]) {
                    $this._selected_list.find('> [data-item="' + item.id + '"]').remove();
                }
            });
        };

        config.selected_checker = function (item) {
            if (typeof item.variant !== 'undefined') {
                return this._selected_list.find('> [data-item="' + item.id + '"][data-variant="' + item.variant.id + '"]')[0] ? true : false;
            }

            if (this._selected_list.find('[data-item="' + item.id + '"][data-variant]')[0]) {
                return null;
            }

            return this._selected_list.find('> [data-item="' + item.id + '"]')[0] ? true : false;
        };

        selector_elm.osc_itemSelector(config);
    };

    function getSrefName(sref_id) {
        if (sref_id < 1) {
            return ''
        }
        let result = ''
        $.ajax({
            async: false,
            url: $.base_url + '/user/backend_member/getMemberById/hash/' + OSC_HASH,
            data: {id: sref_id},
            success: function (response) {
                if (response.result == 'OK') {
                    result = response.data.username
                } else {
                    result = response.message
                }
            }
        })
        return  result
    }

    window.catalogProductSetSref = function () {
        var btn = $(this)

        $(this).click(function () {
            const sref_source = this.getAttribute("data-sref-source")
            const sref_dest = this.getAttribute("data-sref-dest")

            $.unwrapContent('catalogProductSetSref')

            let submit_btn = null
            let reset_btn = null

            let modal = $('<div />').addClass('osc-modal').width(390)

            let header = $('<header />').appendTo(modal)

            $('<div />').addClass('title').html('Set Sref Source Dest').appendTo($('<div />').addClass('main-group').appendTo(header))

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogProductSetSref')
            }).appendTo(header)

            let modal_body = $('<div />').addClass('body post-frm').appendTo(modal)

            let box_sref_source = $('<div />').addClass('mb10 mt10').appendTo(modal_body)

            $('<div />').text('Sref source').appendTo(box_sref_source)

            let value_sref_source = $('<input />').addClass('styled-input mb10 mt10').attr({type: 'number', 'name' : 'sref_source', 'value': sref_source}).bind('change', function (e) {
                e.preventDefault()

                if ($(this).val() < 1 || sref_source == $(this).val()) {
                    return
                }
                let sref_name = getSrefName($(this).val())

                $('.sref-source-name').remove()

                if (sref_name != '') {
                    $('<p />').addClass('sref-source-name').text(sref_name).appendTo(box_sref_source)
                }
            }).appendTo(box_sref_source)

            if (sref_source > 0) {
                let sref_name = getSrefName(sref_source)

                if (sref_name != '') {
                    $('<p />').addClass('sref-source-name').text(sref_name).appendTo(box_sref_source)
                }
            }

            let box_sref_dest = $('<div />').addClass('mb10 mt10').appendTo(modal_body)

            $('<div />').text('Sref dest').appendTo(box_sref_dest)

            let value_sref_dest = $('<input />').addClass('styled-input mb10 mt10').attr({type: 'number', 'name' : 'sref_dest', 'value': sref_dest}).bind('change', function (e) {
                e.preventDefault()

                if ($(this).val() < 1) {
                    return
                }
                let sref_name = getSrefName($(this).val())

                $('.sref-dest-name').remove()

                if (sref_name != '') {
                    $('<p />').addClass('sref-dest-name').text(sref_name).appendTo(box_sref_dest)
                }
            }).appendTo(box_sref_dest)

            if (sref_dest > 0) {
                let sref_name = getSrefName(sref_dest)

                if (sref_name != '') {
                    $('<p />').addClass('sref-dest-name').text(sref_name).appendTo(box_sref_dest)
                }
            }
            let action_bar = $('<div />').addClass('action-bar').appendTo(modal)
            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogProductSetSref')
            }).appendTo(action_bar)
            reset_btn = $('<button />').addClass('btn btn-primary ml10').text('Reset').click(function () {
                if (reset_btn.attr('disabled') === 'disabled') {
                    return
                }

                this.setAttribute('disabled', 'disabled')
                this.setAttribute('data-state', 'submitting')

                reset_btn.prepend($($.renderIcon('preloader')).addClass('mr15'))

                $.ajax({
                    url: btn.attr('data-sref-url'),
                    data: {sref_source: 0, sref_dest : 0},
                    success: function (response) {
                        submit_btn.removeAttr('disabled')
                        submit_btn.find('svg').remove()
                        if (response.result !== 'OK') {
                            alert(response.message)
                            return
                        }
                        alert(response.data.message)
                        window.location.reload(true)
                    }
                })
            }).appendTo(action_bar)
            submit_btn = $('<button />').addClass('btn btn-secondary ml10').text('Save').click(function () {
                if (submit_btn.attr('disabled') === 'disabled') {
                    return
                }

                this.setAttribute('disabled', 'disabled')
                this.setAttribute('data-state', 'submitting')

                submit_btn.prepend($($.renderIcon('preloader')).addClass('mr15'))

                $.ajax({
                    url: btn.attr('data-sref-url'),
                    data: {sref_source: value_sref_source.val(), sref_dest : value_sref_dest.val()},
                    success: function (response) {
                        submit_btn.removeAttr('disabled')
                        submit_btn.find('svg').remove()
                        if (response.result !== 'OK') {
                            alert(response.message)
                            return
                        }
                        alert(response.data.message)
                        window.location.reload(true)
                    }
                })
            }).appendTo(action_bar)
            $.wrapContent(modal, {key: 'catalogProductSetSref'})

            modal.moveToCenter().css('top', '100px')
        })
    }

    window.cleanVNMask = (text) => {

        if (!text) {
            return '';
        }
        const searchs = [
            ['á', 'à', 'ả', 'ạ', 'ã', 'â', 'ấ', 'ầ', 'ẩ', 'ậ', 'ẫ', 'ă', 'ắ', 'ằ', 'ẳ', 'ặ', 'ẵ'],
            ['á', 'À', 'Ả', 'Ạ', 'Ã', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ậ', 'Ẫ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ặ', 'Ẵ'],
            ['ó', 'ò', 'ỏ', 'ọ', 'õ', 'ô', 'ố', 'ồ', 'ổ', 'ộ', 'ỗ', 'ơ', 'ớ', 'ờ', 'ở', 'ợ', 'ỡ'],
            ['Ó', 'Ò', 'Ỏ', 'Ọ', 'Õ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ộ', 'Ỗ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ợ', 'Ỡ'],
            ['é', 'è', 'ẻ', 'ẹ', 'ẽ', 'ê', 'ế', 'ề', 'ể', 'ệ', 'ễ'],
            ['É', 'È', 'Ẻ', 'Ẹ', 'Ẽ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ệ', 'Ễ'],
            ['ú', 'ù', 'ủ', 'ụ', 'ũ', 'ư', 'ứ', 'ừ', 'ử', 'ự', 'ữ'],
            ['Ú', 'Ù', 'Ủ', 'Ụ', 'Ũ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ự', 'Ữ'],
            ['í', 'ì', 'ỉ', 'ị', 'ĩ'],
            ['í', 'Ì', 'Ỉ', 'Ị', 'Ĩ'],
            ['ý', 'ỳ', 'ỷ', 'ỵ', 'ỹ'],
            ['Ý', 'Ỳ', 'Ỷ', 'Ỵ', 'Ỹ'],
            ['đ'],
            ['Đ'],
            ['ä', 'Ä'],
            ['ö', 'Ö'],
            ['ü', 'Ü'],
            ['ß']
        ]

        const replaces = ['a', 'A', 'o', 'O', 'e', 'E', 'u', 'U', 'i', 'I', 'y', 'Y', 'd', 'D', 'ae', 'oe', 'ue', 'ss'];

        searchs.forEach((search, key) => {
            text = text.replace(new RegExp(search.join('|'), 'g'), replaces[key])
        })

        return text
    }

})(jQuery);
