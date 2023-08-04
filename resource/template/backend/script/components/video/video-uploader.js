window.initVideoUploader = function() {
    const $container = $(this);
    const processUrl = $container.data('processUrl') || '/core/backend/uploadMedia';
    const positionInput = $container.data('position') === 'input';
    let initData = $container.data('videos');
    let initConfig = $container.data('initConfig');
    let videoData = {};
    let maxFileSize = Number($container.data('maxSize')) || 0; // max file size in MB

    const selectAble = $container.data('selectable');
    let selectedVideos = [];

    if (typeof initConfig === 'string') {
        initConfig = JSON.parse(initConfig);
    }

    if (typeof initData === 'string') {
        initData = JSON.parse(initData);
    }

    initData?.forEach(item => {
        videoData[(item.id || item.fileId) + ''] = {
            ...item,
        }
    });

    $container.append(`<div style="flex: 0 0 100%; margin-bottom: 4px; color: #666;">${maxFileSize?`Video size must be less than ${maxFileSize}MB. `:''}Recommend using Frame Rate 30 fps, and Bitrate between 1000~1200 kb/s.</div>`);

    const $uploader = $('<div class="file-uploader" />')
        .osc_uploader({
            max_files: -1,
            max_connections: 5,
            process_url: $.base_url + processUrl + '/hash/' + OSC_HASH,
            btn_content: $('<div />').addClass('video-uploader-btn').html('<div class="icon-plus"></div><span>Add new videos</span>'),
            dragdrop_content: 'Drop here to upload videos',
            extensions: ['mp4'],
            max_filesize: maxFileSize * 1024 * 1024,
            request_params: {
                is_video: true,
            },
            xhrFields: {withCredentials: true},
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-OSC-Cross-Request': 'OK'
            }
        })
        .bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
            const reader = new FileReader();
            reader.onload = () => {
                const video = {
                    ...initConfig,
                    fileId: file_id,
                    url: reader.result,
                    state: 'uploading',
                }

                const $videoElm = __renderMockupVideo(video);

                videoData[file_id] = {
                    ...video,
                    elm: $videoElm,
                };

                $videoElm.trigger('item-update', [{ state: 'uploading' }]).insertBefore($uploader);
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

            if (!videoData[file_id]) return;

            const { elm } = videoData[file_id];

            delete videoData[file_id].elm;

            elm.trigger('item-update', [{ state: '', url: res.data }]);
        })
        .bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
            if (maxFileSize && error_code === 'maxSizeError') {
                alert(`Video file is too large, try uploading another file under ${maxFileSize}MB.`);
            }

            if (!videoData[file_id]) return;

            const { elm } = videoData[file_id];

            delete videoData[file_id].elm;

            elm.trigger('item-update', [{ state: 'remove' }]);

            alert('Upload failed, please try again.');
        })
        .appendTo($container);

    const $hiddenInput = $('<input type="hidden" />').appendTo($container);

    __renderAllMockupItems();

    $container
        .on('unselect-all-items', function() {
            selectedVideos = [];
            $container.find('.video-item').trigger('item-update', [{ selected: false, triggerEvents: [] }]);
            __updateVideoData(['uploader-selected-change']);
        })
        .on('uploader-update-items', function(e, updateVideos = {}) {
            Object.entries(updateVideos).forEach(([videoId, video]) => {
                $container.find(`#${videoId}`).trigger('item-update', [{ ...video, triggerEvents: [] }])
            });

            if (Object.keys(updateVideos)?.length) {
                __updateVideoData(['uploader-update']);
            }
        });

    function __renderAllMockupItems() {
        $container.find('.video-item').remove();

        Object.values(videoData)
            .forEach(video => {
                let $videoElm = __renderMockupVideo(video);
                $videoElm.insertBefore($uploader);
            });
    }

    function __renderMockupVideo(video) {
        const videoId = video.fileId || video.id;

        const $videoElm =
            $(`<div id="${videoId}" class="video-item ${video.thumbnail ? 'has-thumbnail' : ''}  ${positionInput ? 'position-input' : ''}">
                <div class="video-frame">
                    <video src="${video.url}" poster="${video.thumbnail || ''}"></video>
                </div>
                <div class="uploader-progress-bar"><div></div></div>
                <span class="video-tag ${(video.tag !== undefined || Object.keys(video?.variantIds || {}).length) ? '' : 'hide'}">${video.tag || Object.keys(video.variantIds || {}).length || ''}</span>
                <span class="btn-uploading-thumbnail">Uploading</span>
                <span class="btn-remove-video"></span>
                <span class="btn-remove-thumbnail">Remove thumbnail</span>
                <span class="uploader-error"></span>
                <span class="btn-preview-video"></span>
                <input class="video-position-input" value="${video.position || ''}" type="text" maxlength="10" placeholder="Position" />
            </div>`)
            .on('item-update', function (e, updateData = {}) {
                if (!videoData[videoId] || typeof updateData !== 'object') {
                    return console.error('Update video error: ', { videoData, id: videoId, updateData, });
                }

                const triggerEvents = updateData.triggerEvents || ['uploader-update'];

                delete updateData.triggerEvents;

                const video = {
                    ...videoData[videoId],
                    ...updateData,
                };

                videoData[videoId] = video;

                $videoElm
                    .removeClass('uploading error error-thumbnail selected')
                    .addClass(video.state || '')
                    .data('video-id', videoId)
                    .data('video-data', JSON.stringify(video));

                if (video.selected) {
                    $videoElm.addClass('selected');
                }

                $videoElm.find('.image-position-input').val(video.position || '');

                const $video = $videoElm.find('video');

                if (video.url !== $video.attr('src')) {
                    $video.attr('src', video.url);
                }

                if ((video.thumbnail || '') !== $video.attr('poster')) {
                    $video.attr('poster', video.thumbnail);
                }

                if (video.tag !== undefined) {
                    $videoElm.find('.video-tag').removeClass('hide').text(video.tag);
                }
                else if (video.variantIds) {
                    let count = Object.values(video.variantIds).filter(value => value).length
                    $videoElm.find('.video-tag').removeClass('hide').text(count);
                }
                else {
                    $videoElm.find('.video-tag').addClass('hide');
                }

                if (video.thumbnail) {
                    $videoElm.addClass('has-thumbnail');
                }
                else {
                    $videoElm.removeClass('has-thumbnail');
                }

                if (video.state === 'remove') {
                    $videoElm.remove();
                    delete videoData[videoId];
                }

                __updateVideoData(triggerEvents);
            })
            .on('click', function(e) {
                e.preventDefault();

                if (!selectAble) return false;

                for (const [, video] of Object.entries(videoData)) {
                    if (video.state === 'uploading') {
                        console.warn('Skip selecting, there is a loading video');
                        return false;
                    }
                }

                const video = videoData[videoId];

                if (selectAble === 'single') {
                    let currentSelected = selectedVideos[0];

                    if (videoId === currentSelected) {
                        selectedVideos = [];
                        $videoElm.trigger('item-update', [{ selected: false, triggerEvents: ['uploader-selected-change'] }]);
                    }
                    else if (!currentSelected || currentSelected !== videoId) {
                        selectedVideos = [videoId];
                        $videoElm.trigger('item-update', [{ selected: true, triggerEvents: ['uploader-selected-change'] }]);

                        if (currentSelected) {
                            $container.find(`#${currentSelected}`).trigger('item-update', [{ selected: false, triggerEvents: ['uploader-selected-change'] }]);
                        }
                    }
                } else if (selectAble === 'multiple') {
                    if (video.selected) {
                        selectedVideos = selectedVideos.filter(id => id !== videoId);
                    } else {
                        selectedVideos.push(videoId)
                    }

                    $videoElm.trigger('item-update', [{ selected: !video.selected, triggerEvents: ['uploader-selected-change'] }]);
                }
            })
            .on('click', '.btn-remove-video', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!confirm('Do you want to remove this video?')) return false;

                $videoElm.trigger('item-update', [{ state: 'remove' }]);
            })
            .on('click', '.btn-remove-thumbnail', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!confirm('Do you want to remove Thumbnail of this video?')) return false;

                $videoElm.trigger('item-update', [{ thumbnail: '' }]);
                $videoElm.find('video')[0]?.load();
            })
            .on('click', '.btn-preview-video', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const video = videoData[videoId];

                $.unwrapContent('modalPreviewVideo');

                const $modal = $(
                    `<div class='osc-modal preview-video-modal'>
                        <button type="button" class="preview-video-close">Close</button>
                        <div class="preview-video-frame">
                            <video src="${video.url}" poster="${video.thumbnail || ''}" controls autoplay muted playsinline></video>
                        </div>
                    </div>`
                ).width(700);

                $modal.on('click', '.preview-video-close', function(e) {
                    e.preventDefault();
                    $.unwrapContent('modalPreviewVideo');
                });

                $.wrapContent($modal, { key: 'modalPreviewVideo' });

                $modal.moveToCenter().css({
                    top: '100px',
                    margin: '0 auto',
                });
            })
            .on('input', '.video-position-input', function(e) {
                let val = $(this).val();
                val = val.replace(/[^0-9]+/g, '');
                $(this).val(val);
            })
            .on('blur', '.video-position-input', function(e) {
                let val = $(this).val();
                val = val.replace(/[^0-9]+/g, '');
                $(this).val(val);

                $videoElm.trigger('item-update', [{ position: Number(val), triggerEvents: [] }]);
            });

        $videoElm.find('video')[0].onloadedmetadata = function() {
            const duration = Math.round(this.duration);

            if (videoData[videoId]?.duration == duration) return false;

            $videoElm.trigger('item-update', [{ duration }]);
        }

        $('<div class="thumbnail-uploader" />')
            .osc_uploader({
                max_files: -1,
                max_connections: 5,
                process_url: $.base_url + processUrl + '/hash/' + OSC_HASH,
                btn_content: $('<span class="thumbnail-uploader-text">Add thumbnail</span>'),
                dragdrop_content: 'Drop here to upload videos',
                image_mode: true,
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            })
            .on('click', function(e) {
                e.stopPropagation();
            })
            .bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                const reader = new FileReader();
                reader.onload = () => {
                    $videoElm.trigger('item-update', [{ state: 'uploading' }]);
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
                }

                $videoElm.trigger('item-update', [{ state: '', thumbnail: (res?.data || '') }]);
            })
            .bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                $videoElm.trigger('item-update', [{ state: 'error-thumbnail' }]);

                alert('Upload failed, please try again.');
            })
            .appendTo($videoElm);

        return $videoElm;
    }

    function __updateVideoData(triggerEvents = []) {
        if (!triggerEvents?.length) return false;

        for (const [, video] of Object.entries(videoData)) {
            if (video.state === 'uploading') {
                return false;
            }
        }

        $hiddenInput.val(JSON.stringify(videoData));

        triggerEvents.forEach(eventName => {
            if (eventName === 'uploader-update') {
                $container.trigger('uploader-update', [window.__videoUploader__getData()]);
            }
            else if (eventName === 'uploader-selected-change') {
                $container.trigger('uploader-selected-change', [window.__videoUploader__getSelectedItems()]);
            }
        });
    }

    window.__videoUploader__setData = function(data) {
        if (!data || typeof data !== 'object') return false;

        videoData = {
            ...videoData,
            ...data,
        }

        for (const [, video] of Object.entries(videoData)) {
            let $videoElm = __renderMockupVideo(video);
            $videoElm.insertBefore($uploader);
        }
    }

    window.__videoUploader__getSelectedItems = function() {
        if (!selectAble || !selectedVideos.length) return null;

        const result = {};

        selectedVideos.forEach(videoId => {
            if (videoData[videoId]) {
                result[videoId] = videoData[videoId];
            }
        });

        if (selectAble === 'single') {
            return result[selectedVideos[0]];
        }

        return result;
    }

    window.__videoUploader__getData = function() {
        return JSON.parse(JSON.stringify(videoData));
    }
}
