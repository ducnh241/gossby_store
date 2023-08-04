window.initImageUploader = function() {
    const $container = $(this);
    const processUrl = $container.data('processUrl') || '/core/backend/uploadMedia';
    const positionInput = $container.data('position') === 'input';
    let initData = $container.data('images');
    let initConfig = $container.data('initConfig');
    let imageData = {};
    let maxFileSize = Number($container.data('maxSize')) || 0; // max file size in MB

    const selectAble = $container.data('selectable');
    let selectedImages = [];

    if (typeof initConfig === 'string') {
        initConfig = JSON.parse(initConfig);
    }

    if (typeof initData === 'string') {
        initData = JSON.parse(initData);
    }

    initData?.forEach(item => {
        imageData[(item.id || item.fileId) + ''] = {
            ...item,
        }
    });

    if (maxFileSize) {
        $container.append(`<div style="flex: 0 0 100%; margin-bottom: 4px; color: #666;">Image size must be less than ${maxFileSize}MB</div>`);
    }

    const $uploader = $('<div class="file-uploader" />')
        .osc_uploader({
            max_files: -1,
            max_connections: 5,
            process_url: $.base_url + processUrl + '/hash/' + OSC_HASH,
            btn_content: $('<div />').addClass('image-uploader-btn').html('<div class="icon-plus"></div><span>Add new images</span>'),
            dragdrop_content: 'Drop here to upload images',
            image_mode: true,
            max_filesize: maxFileSize * 1024 * 1024,
            xhrFields: {withCredentials: true},
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-OSC-Cross-Request': 'OK'
            }
        })
        .bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
            const reader = new FileReader();
            reader.onload = () => {
                const image = {
                    ...initConfig,
                    fileId: file_id,
                    url: reader.result,
                    state: 'uploading',
                }

                const $imageElm = __renderMockupImage(image);

                imageData[file_id] = {
                    ...image,
                    elm: $imageElm,
                };

                $imageElm.trigger('item-update', [{ state: 'uploading' }]).insertBefore($uploader);
            }
            reader.readAsDataURL(file);
        })
        .bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {})
        .bind('uploader_upload_complete', function (e, file_id, response, pointer) {
            let res;
            try {
                res = JSON.parse(response)
            } catch (error) {
                console.error(response);
                return;
            }

            if (!imageData[file_id]) return;

            const { elm } = imageData[file_id];

            delete imageData[file_id].elm;

            elm.trigger('item-update', [{ state: '', url: res.data }]);
        })
        .bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
            if (maxFileSize && error_code === 'maxSizeError') {
                alert(`Image file is too large, try uploading another file under ${maxFileSize}MB`);
            }

            if (!imageData[file_id]) return;

            const { elm } = imageData[file_id];

            delete imageData[file_id].elm;

            elm.trigger('item-update', [{ state: 'remove' }]);

            alert('Upload failed, please try again.');
        })
        .appendTo($container);

    const $hiddenInput = $('<input type="hidden" />').appendTo($container);

    __renderAllMockupItems();

    $container
        .on('unselect-all-items', function() {
            selectedImages = [];
            $container.find('.image-item').trigger('item-update', [{ selected: false, triggerEvents: [] }]);
            __updateImageData(['uploader-selected-change']);
        })
        .on('uploader-update-items', function(e, updateImages = {}) {
            Object.entries(updateImages).forEach(([imageId, image]) => {
                $container.find(`#${imageId}`).trigger('item-update', [{ ...image, triggerEvents: [] }])
            });

            if (Object.keys(updateImages)?.length) {
                __updateImageData(['uploader-update']);
            }
        });

    function __renderAllMockupItems() {
        $container.find('.image-item').remove();

        Object.values(imageData)
            .forEach(image => {
                let $imageElm = __renderMockupImage(image);
                $imageElm.insertBefore($uploader);
            });
    }

    function __renderMockupImage(image) {
        const imageId = image.fileId || image.id;

        const $imageElm =
            $(`<div id="${imageId}" class="image-item ${positionInput ? 'position-input' : ''}" style="background: url(${image.url}) center/contain no-repeat">
                <div class="uploader-progress-bar"><div></div></div>
                <span class="image-tag ${(image.tag !== undefined || Object.keys(image?.variantIds || {}).length) ? '' : 'hide'}">${image.tag || Object.keys(image.variantIds || {}).length || ''}</span>
                <span class="btn-remove-image"></span>
                <span class="uploader-error"></span>
                <input class="image-position-input" value="${image.position || ''}" type="text" maxlength="10" placeholder="Position" />
            </div>`)
                .on('item-update', function (e, updateData = {}) {
                    if (!imageData[imageId] || typeof updateData !== 'object') {
                        return console.error('Update image error: ', { imageData, id: imageId, updateData, });
                    }

                    const triggerEvents = updateData.triggerEvents || ['uploader-update'];

                    delete updateData.triggerEvents;

                    const image = {
                        ...imageData[imageId],
                        ...updateData,
                    };

                    imageData[imageId] = image;

                    $imageElm
                        .removeClass('uploading error error-thumbnail selected')
                        .addClass(image.state || '')
                        .data('image-id', imageId)
                        .data('image-data', JSON.stringify(image));

                    if (image.selected) {
                        $imageElm.addClass('selected');
                    }

                    $imageElm.find('.image-position-input').val(image.position || '');

                    const $image = $imageElm.find('image');

                    if (image.url !== $image.attr('src')) {
                        $image.attr('src', image.url);
                    }

                    if (image.tag !== undefined) {
                        $imageElm.find('.image-tag').removeClass('hide').text(image.tag);
                    }
                    else if (image.variantIds) {
                        let count = Object.values(image.variantIds).filter(value => value).length
                        $imageElm.find('.image-tag').removeClass('hide').text(count);
                    }
                    else {
                        $imageElm.find('.image-tag').addClass('hide');
                    }

                    if (image.state === 'remove') {
                        $imageElm.remove();
                        delete imageData[imageId];
                    }

                    __updateImageData(triggerEvents);
                })
                .on('click', function(e) {
                    e.preventDefault();

                    if (!selectAble) return false;

                    for (const [, image] of Object.entries(imageData)) {
                        if (image.state === 'uploading') {
                            console.warn('Skip selecting, there is a loading image');
                            return false;
                        }
                    }

                    const image = imageData[imageId];

                    if (selectAble === 'single') {
                        let currentSelected = selectedImages[0];

                        if (imageId === currentSelected) {
                            selectedImages = [];
                            $imageElm.trigger('item-update', [{ selected: false, triggerEvents: ['uploader-selected-change'] }]);
                        }
                        else if (!currentSelected || currentSelected !== imageId) {
                            selectedImages = [imageId];
                            $imageElm.trigger('item-update', [{ selected: true, triggerEvents: ['uploader-selected-change'] }]);

                            if (currentSelected) {
                                $container.find(`#${currentSelected}`).trigger('item-update', [{ selected: false, triggerEvents: ['uploader-selected-change'] }]);
                            }
                        }
                    } else if (selectAble === 'multiple') {
                        if (image.selected) {
                            selectedImages = selectedImages.filter(id => id !== imageId);
                        } else {
                            selectedImages.push(imageId)
                        }

                        $imageElm.trigger('item-update', [{ selected: !image.selected, triggerEvents: ['uploader-selected-change'] }]);
                    }
                })
                .on('click', '.btn-remove-image', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (!confirm('Do you want to remove this image?')) return false;

                    $imageElm.trigger('item-update', [{ state: 'remove' }]);
                })
                .on('input', '.image-position-input', function(e) {
                    let val = $(this).val();
                    val = val.replace(/[^0-9]+/g, '');
                    $(this).val(val);
                })
                .on('blur', '.image-position-input', function(e) {
                    let val = $(this).val();
                    val = val.replace(/[^0-9]+/g, '');
                    $(this).val(val);

                    $imageElm.trigger('item-update', [{ position: Number(val), triggerEvents: [] }]);
                });

        return $imageElm;
    }

    function __updateImageData(triggerEvents = []) {
        if (!triggerEvents?.length) return false;

        for (const [, image] of Object.entries(imageData)) {
            if (image.state === 'uploading') {
                return false;
            }
        }

        $hiddenInput.val(JSON.stringify(imageData));

        triggerEvents.forEach(eventName => {
            if (eventName === 'uploader-update') {
                $container.trigger('uploader-update', [window.__imageUploader__getData()]);
            }
            else if (eventName === 'uploader-selected-change') {
                $container.trigger('uploader-selected-change', [window.__imageUploader__getSelectedItems()]);
            }
        });
    }

    window.__imageUploader__setData = function(data) {
        if (!data || typeof data !== 'object') return false;

        imageData = {
            ...imageData,
            ...data,
        }

        for (const [, image] of Object.entries(imageData)) {
            let $imageElm = __renderMockupImage(image);
            $imageElm.insertBefore($uploader);
        }
    }

    window.__imageUploader__getSelectedItems = function() {
        if (!selectAble || !selectedImages.length) return null;

        const result = {};

        selectedImages.forEach(imageId => {
            if (imageData[imageId]) {
                result[imageId] = imageData[imageId];
            }
        });

        if (selectAble === 'single') {
            return result[selectedImages[0]];
        }

        return result;
    }

    window.__imageUploader__getData = function() {
        return JSON.parse(JSON.stringify(imageData));
    }
}
