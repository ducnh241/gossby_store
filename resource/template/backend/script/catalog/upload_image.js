(function ($) {
    'use strict';

    function _frm_renderImage(image) {
        let data = {
            id: 0,
            url: '',
            filename: '',
            width: 0,
            height: 0,
            extension: ''
        };

        const image_list = $('.review-images');

        const item = $('<div />').addClass('review-image').appendTo(image_list).bind('update', function (e, new_data) {
            if (new_data !== null && typeof new_data === 'object') {
                $.extend(data, new_data);
            }

            item.css('background-image', data.url ? ('url(' + data.url + ')') : 'initial');
            item.attr({data: data.url})

            item.find('input[type="hidden"]').remove();

            if (data.url) {
                item.append($('<input />').attr({type: 'hidden', name: 'images[' + data.id + ']', value: data.id}));
            }
        });

        initItemReorder(item, '.review-images', '.review-image', 'product-post-frm-img-reorder-helper', function (helper) {
            helper.html('');
        });

        const control_bars = $('<div />').addClass('controls').appendTo(item);

        $($.renderIcon('trash-alt-regular')).mousedown(function (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();

            item.remove();
        }).appendTo(control_bars);

        item.trigger('update', [image]);

        return item;
    }

    window.initImgUploader = function () {
        const image_list = $(this).closest('form').find('.review-images');

        $(this).osc_uploader({
            max_files: -1,
            max_connections: 5,
            process_url: this.getAttribute('data-process-url'),
            btn_content: 'Upload image',
            dragdrop_content: 'Drop here to upload',
            image_mode: true,
            xhrFields: {withCredentials: true},
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-OSC-Cross-Request': 'OK'
            }
        }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
            const item = _frm_renderImage().attr('file-id', file_id).attr('data-uploader-step', 'queue');

            $('<div />').addClass('uploader-progress-bar').appendTo(item).append($('<div />'));
            $('<div />').addClass('step').appendTo(item);
        }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
            const item = image_list.find('> [file-id="' + file_id + '"]');

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
            const item = image_list.find('> [file-id="' + file_id + '"]');

            if (!item[0]) {
                return;
            }

            eval('response = ' + response);

            if (response.result !== 'OK') {
                alert(response.message);
                item.remove();
                return;
            }

            item.trigger('update', [{
                id: response.data.file,
                url: response.data.url,
                filename: response.data.file,
                width: response.data.width,
                height: response.data.height,
                extension: response.data.extension,
            }]);

            item.removeAttr('file-id').removeAttr('data-uploader-step');

            item.find('.uploader-progress-bar').remove();
            item.find('.step').remove();
        }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
            const item = image_list.find('> [file-id="' + file_id + '"]');

            if (!item[0]) {
                return;
            }

            alert('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại');
            item.remove();
        });
    };

    window.copyImageToClipboard = function () {
        let images = []
        $('.review-images .review-image').each(function () {
            images.push($(this).attr('data'))
        })

        const $tempElement = $('<textarea />')
        $("body").append($tempElement);
        $tempElement.val(images.join("\r\n"));
        $tempElement.select();
        document.execCommand("Copy");
        $tempElement.remove();

        alert('Copied to clipboard')
    }
})(jQuery);