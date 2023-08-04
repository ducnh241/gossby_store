(function ($) {
    'use strict';

    window.initSettingType__Color = function () {
        var picker = $(this).addClass('setting-color');

        var color = picker.find('input').val();

        if (!color) {
            picker.addClass('no-color');
        } else {
            picker.removeClass('no-color').css('background-color', color);
        }

        picker.osc_colorPicker({
            swatch_name: 'setting',
            callback: function (color) {
                if (color) {
                    picker.removeClass('no-color').css('background-color', color);
                } else {
                    picker.addClass('no-color');
                }

                picker.find('input').val(color);
            }
        });
    };

    window.initSettingType__Image = function () {
        var container = $(this);
        let max_filesize = container.data('max-file-size') * 1024 * 1024 || 0;

        container.addClass('setting-image-uploader');

        var preview = $('<div />').addClass('preview').appendTo(container);

        var image_url = container.attr('data-image');
        var input_name = container.attr('data-input');

        $('<input />').attr({
            type: 'hidden',
            name: input_name,
            value: image_url !== '' ? container.attr('data-value') : ''
        }).appendTo(preview);

        if (image_url !== '') {
            preview.css('background-image', 'url(' + image_url + ')');
        }

        var uploader_container = $('<div />').addClass('mt10 btn btn-primary p0 control-list').appendTo(container);

        var __initRemoveBtn = function () {
            uploader_container.find('.image-uploader').hide();
            uploader_container.find('.remove-btn').remove();

            $('<div />').addClass('btn btn-danger remove-btn').appendTo(uploader_container).text('Remove image').click(function () {
                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                image_url = '';
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();
                preview.css('background-image', 'initial');
                preview.find('input').attr('value', '');

                __initUploader();
            });
        };

        var __initUploader = function () {
            uploader_container.find('.remove-btn').hide();
            uploader_container.find('.image-uploader').remove();

            var uploader = $('<div />').addClass('image-uploader').appendTo(uploader_container);

            uploader.osc_uploader({
                max_files: 1,
                process_url: container.attr('data-upload-url'),
                btn_content: 'Upload image',
                dragdrop_content: 'Drop here to upload',
                extensions: 'png,gif,jpg,svg,ico,jpeg',
                max_filesize: max_filesize,
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.hide();

                __initRemoveBtn();

                preview.attr('file-id', file_id).attr('data-uploader-step', 'queue');

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
                            preview.css('background-image', 'url(' + URL.createObjectURL(blob) + ')');
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
                    preview.css('background-image', image_url !== '' ? ('url(' + image_url + ')') : 'initial');
                    alert(response.message);

                    __initUploader();

                    return;
                }

                preview.css('background-image', 'url(' + response.data.url + ')');

                image_url = response.data.url;

                var data = preview.find('input').attr('value');

                if (data) {
                    data = JSON.parse(data);
                }

                if (data === null || typeof data !== 'object') {
                    data = {};
                }

                if (typeof data.alt === 'undefined') {
                    data.alt = '';
                }

                data.file = response.data.file;

                preview.find('input').attr('value', JSON.stringify(data));
            }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                if (error_code === 'maxSizeError' || file_id === 'sizeError') {
                    alert('File size exceeds the maximum limit of 10MB, please upload a smaller file.');
                } else if (file_id === 'typeError') {
                    alert('Sorry! The image file format you uploaded is not supported.');
                } else {
                    alert('Upload error, please try again');
                }

                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                __initUploader();

                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();
                preview.css('background-image', image_url !== '' ? ('url(' + image_url + ')') : 'initial');

                alert('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại');
            });
        };

        if (image_url !== '') {
            __initRemoveBtn();
        } else {
            __initUploader();
        }
    };
})(jQuery);