(function ($) {
    'use strict';

    window.initCollapseSection = function () {
        let item = $(this);
        item.on('click', function () {
            $(this).toggleClass('expanded');
            item.next().toggleClass('expanded');
        });
    };

    window.initFormRepeater = function () {
        let selector = $(this),
            items = selector.data('items'),
            template = selector.data('template');
        selector.on('click', function () {
            let key = $.makeUniqid();
            let _template = $(template).text().replaceAll('{key}', key);
            $(items).append($(_template));
        });
    }

    window.initAddNewBannerItem = function () {
        let item = $(this);
        item.on('click', function () {
            let _number_items = item.parent().find('.item-banner-slider').length;
            let _template = $('#item-banner-slider-template').text().replace('{key}', _number_items);
        });
    }

    window.initVideoUploader = function () {
        const $container = $(this);
        const processUrl = $container.data('processUrl') || '/core/backend/uploadMedia';
        let initData = $container.data('video');
        let initConfig = $container.data('initConfig');
        let videoData = {};
        let maxFiles = Number($container.data('max_files')) || 1;
        let maxFileSize = Number($container.data('maxSize')) || 0; // max file size in MB
        let useThumbnail = Number($container.data('use_thumbnail')) || 0;

        const selectAble = $container.data('selectable');
        let selectedVideos = [];

        if (typeof initConfig === 'string') {
            initConfig = JSON.parse(initConfig);
        }

        if (typeof initData === 'string' && initData !== '') {
            initData = JSON.parse(initData);
        }

        Object.keys(initData).forEach(key => {
            videoData[(initData[key].id || initData[key].fileId) + ''] = {
                ...initData[key],
            }
        });

        const $uploader = $('<div class="file-uploader" />').css('width', '100%')
            .osc_uploader({
                max_files: maxFiles,
                max_connections: 1,
                process_url: $.base_url + processUrl + '/hash/' + OSC_HASH,
                btn_content: $('<div />').addClass('video-uploader-btn').html('<div class="icon-plus"></div><span>Upload vides</span>'),
                dragdrop_content: 'Drop here to upload video',
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

                    const $videoElm = __renderVideo(video);

                    videoData[file_id] = {
                        ...video,
                        elm: $videoElm,
                    };

                    $videoElm.trigger('item-update', [{state: 'uploading'}]).insertBefore($uploader);
                }
                reader.readAsDataURL(file);
            })
            .bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
            })
            .bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                let res;
                try {
                    res = JSON.parse(response)
                } catch (error) {
                    console.error(response);
                    return;
                }

                if (!videoData[file_id]) return;

                const {elm} = videoData[file_id];

                delete videoData[file_id].elm;

                elm.trigger('item-update', [{state: '', url: res.data}]);
            })
            .bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                if (maxFileSize && error_code === 'maxSizeError') {
                    alert(`Video file is too large, try uploading another file under ${maxFileSize}MB.`);
                }

                if (!videoData[file_id]) return;

                const {elm} = videoData[file_id];

                delete videoData[file_id].elm;

                elm.trigger('item-update', [{state: 'remove'}]);

                alert('Upload failed, please try again.');
            })
            .appendTo($container);

        const $hiddenInput = $('<input type="hidden" />').attr({
            'name': $container.data('name'),
            'value': JSON.stringify($container.data('video'))
        }).appendTo($container);

        __renderVideoItems();

        $container
            .on('uploader-update-items', function (e, updateVideos = {}) {
                Object.entries(updateVideos).forEach(([videoId, video]) => {
                    $container.find(`#${videoId}`).trigger('item-update', [{...video, triggerEvents: []}])
                });

                if (Object.keys(updateVideos)?.length) {
                    __updateVideoData(['uploader-update']);
                }
            });


        function __renderVideoItems() {
            $container.find('.video-item').remove();

            Object.values(videoData)
                .forEach(video => {
                    let $videoElm = __renderVideo(video);
                    $videoElm.insertBefore($uploader);
                    if (maxFiles === 1) {
                        $uploader.hide();
                    }
                });
        }

        function __renderVideo(video) {
            const videoId = video.fileId || video.id;

            const $videoElm =
                $(`<div id="${videoId}" class="video-item ${video.thumbnail ? 'has-thumbnail' : ''}">
                <div class="video-frame">
                    <video src="${video.url}" poster="${video.thumbnail || ''}"></video>
                </div>
                <div class="uploader-progress-bar"><div></div></div>
                <span class="btn-uploading-thumbnail">Uploading</span>
                <span class="btn-remove-video"></span>
                <span class="btn-remove-thumbnail">Remove thumbnail</span>
                <span class="uploader-error"></span>
                <span class="btn-preview-video"></span>
            </div>`)
                    .on('item-update', function (e, updateData = {}) {
                        if (!videoData[videoId] || typeof updateData !== 'object') {
                            return console.error('Update video error: ', {videoData, id: videoId, updateData,});
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

                        const $video = $videoElm.find('video');

                        if (video.url !== $video.attr('src')) {
                            $video.attr('src', video.url);
                        }

                        if ((video.thumbnail || '') !== $video.attr('poster')) {
                            $video.attr('poster', video.thumbnail);
                        }

                        if (video.thumbnail) {
                            $videoElm.addClass('has-thumbnail');
                        } else {
                            $videoElm.removeClass('has-thumbnail');
                        }

                        if (video.state === 'remove') {
                            $videoElm.remove();
                            delete videoData[videoId];
                            if (maxFiles === 1) {
                                $uploader.show();
                            }
                        }

                        __updateVideoData(triggerEvents);
                    })
                    .on('click', function (e) {
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
                                $videoElm.trigger('item-update', [{
                                    selected: false,
                                    triggerEvents: ['uploader-selected-change']
                                }]);
                            } else if (!currentSelected || currentSelected !== videoId) {
                                selectedVideos = [videoId];
                                $videoElm.trigger('item-update', [{
                                    selected: true,
                                    triggerEvents: ['uploader-selected-change']
                                }]);

                                if (currentSelected) {
                                    $container.find(`#${currentSelected}`).trigger('item-update', [{
                                        selected: false,
                                        triggerEvents: ['uploader-selected-change']
                                    }]);
                                }
                            }
                        } else if (selectAble === 'multiple') {
                            if (video.selected) {
                                selectedVideos = selectedVideos.filter(id => id !== videoId);
                            } else {
                                selectedVideos.push(videoId)
                            }

                            $videoElm.trigger('item-update', [{
                                selected: !video.selected,
                                triggerEvents: ['uploader-selected-change']
                            }]);
                        }
                    })
                    .on('click', '.btn-remove-video', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        if (!confirm('Do you want to remove this video?')) return false;

                        $videoElm.trigger('item-update', [{state: 'remove'}]);
                    })
                    .on('click', '.btn-remove-thumbnail', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        if (!confirm('Do you want to remove Thumbnail of this video?')) return false;

                        $videoElm.trigger('item-update', [{thumbnail: ''}]);
                        $videoElm.find('video')[0]?.load();
                    })
                    .on('click', '.btn-preview-video', function (e) {
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

                        $modal.on('click', '.preview-video-close', function (e) {
                            e.preventDefault();
                            $.unwrapContent('modalPreviewVideo');
                        });

                        $.wrapContent($modal, {key: 'modalPreviewVideo'});

                        $modal.moveToCenter().css({
                            top: '100px',
                            margin: '0 auto',
                        });
                    });

            $videoElm.find('video')[0].onloadedmetadata = function () {
                const duration = Math.round(this.duration);

                if (videoData[videoId]?.duration == duration) return false;

                $videoElm.trigger('item-update', [{duration}]);
            }

            if (useThumbnail === 1) {
                $('<div class="thumbnail-uploader" />')
                    .osc_uploader({
                        max_files: -1,
                        max_connections: 6,
                        process_url: $.base_url + processUrl + '/hash/' + OSC_HASH,
                        btn_content: $('<span class="thumbnail-uploader-text">Add thumbnail</span>'),
                        dragdrop_content: 'Drop here to upload thumbnail',
                        image_mode: true,
                        xhrFields: {withCredentials: true},
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-OSC-Cross-Request': 'OK'
                        }
                    })
                    .on('click', function (e) {
                        e.stopPropagation();
                    })
                    .bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                        const reader = new FileReader();
                        reader.onload = () => {
                            $videoElm.trigger('item-update', [{state: 'uploading'}]);
                        }
                        reader.readAsDataURL(file);
                    })
                    .bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
                    })
                    .bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                        let res;
                        try {
                            res = JSON.parse(response)
                        } catch (error) {
                            console.error(response);
                        }

                        $videoElm.trigger('item-update', [{state: '', thumbnail: (res?.data || '')}]);
                    })
                    .bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                        $videoElm.trigger('item-update', [{state: 'error-thumbnail'}]);

                        alert('Upload failed, please try again.');
                    })
                    .appendTo($videoElm);
            } else {
                $videoElm.find('.video-frame').css('height', '100%');
            }

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
                } else if (eventName === 'uploader-selected-change') {
                    $container.trigger('uploader-selected-change', [window.__videoUploader__getSelectedItems()]);
                }
            });
        }

        window.__videoUploader__setData = function (data) {
            if (!data || typeof data !== 'object') return false;

            videoData = {
                ...videoData,
                ...data,
            }

            for (const [, video] of Object.entries(videoData)) {
                let $videoElm = __renderVideo(video);
                $videoElm.insertBefore($uploader);
            }
        }

        window.__videoUploader__getSelectedItems = function () {
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

        window.__videoUploader__getData = function () {
            return JSON.parse(JSON.stringify(videoData));
        }
    }


})(jQuery);
