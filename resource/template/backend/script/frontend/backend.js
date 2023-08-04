(function ($) {
    'use strict';
    window.initSelect2Form = function () {
        $(this).select2({
            width: '100%'
        });
    };    
    window.initBackendHomepageSectionBannerUpload = function () {
        var container = $(this).closest('.section-image-uploader'),
            preview = container.find('.preview'),
            update_value = container.find('.update-value');

        var uploader_container = $(this);

        var __initRemoveBtn = function () {
            uploader_container.find('.image-uploader').hide();
            uploader_container.find('.remove-btn').remove();

            $('<div />').addClass('btn btn-danger remove-btn').appendTo(uploader_container).text('Remove banner image').click(function () {
                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                preview.removeAttr('data-image-src');
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();
                preview.css('background-image', 'initial');
                preview.find('input').remove();

                preview.find('svg').removeAttr('style');

                __initUploader();
            });
        };

        var __initUploader = function () {
            uploader_container.find('.remove-btn').hide();
            uploader_container.find('.image-uploader').remove();

            var uploader = $('<div />').addClass('image-uploader').appendTo(uploader_container);

            uploader.osc_uploader({
                max_files: 1,
                process_url: uploader_container.attr('data-process-url'),
                btn_content: $('<div />').addClass('btn btn-primary').text('Upload image'),
                dragdrop_content: 'Drop here to upload',
                image_mode: true,
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.hide();
                preview.find('svg').hide();

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
                    preview.find('svg').removeAttr('style');
                    preview.css('background-image', preview.attr('data-image-src') !== '' ? ('url(' + preview.attr('data-image-src') + ')') : 'initial');

                    alert(response.message);

                    __initUploader();

                    return;
                }

                preview.css('background-image', 'url(' + response.data.url + ')');
                preview.css({'width': '200px','height': '200px','background-size': 'contain'});

                preview.attr('data-image-src', response.data.url);
                preview.find('input').remove();

                $('<input />').attr({type: 'hidden', name: 'image', value: response.data.file}).appendTo(preview);
                update_value.val(response.data.file);
            }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                __initUploader();

                preview.find('svg').removeAttr('style');
                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();
                preview.css('background-image', preview.attr('data-image-src') !== '' ? ('url(' + preview.attr('data-image-src') + ')') : 'initial');

                alert('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại');
            });
        };

        if (preview.attr('data-image-src') !== '') {
            __initRemoveBtn();
        } else {
            __initUploader();
        }
    };

    window.initCollapseSection = function () {
        var item = $(this);
        item.on('click', function () {
           $(this).toggleClass('expanded');
           item.next().toggleClass('expanded');
        });
    };

    window.initRemoveSection = function() {
        var item = $(this);
        item.on('click', function (e) {
            e.preventDefault();
            var _section = item.closest("form");

            $.ajax({
                url: item.data('url'),
                data: {id: item.data('id')},
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    console.log(response);
                    alert(response.data);

                    //window.location.reload();
                }
            });

            _section.remove();
            return;
        });
    };

    window.initRemoveBanner = function() {
        var item = $(this);
        item.on('click', function (e) {
            e.preventDefault();
            var _section = item.closest("form"),
                _this_item = item.closest('.js-banner-item'),
                _num_item =  parseInt(_section.find("input[name='number_items']").val());

            _this_item.remove();

            if(_num_item > 0) {
                _section.find("input[name='number_items']").val(_num_item-1);
            }
            });
        };

    window.initAddNewBanner = function() {
        var item = $(this);
        item.on('click', function (e) {
            e.preventDefault();
            var _section = item.closest("form"),
                _action_bar = _section.find('.action-bar');

            $.ajax({
                url: item.data('url'),
                data: {},
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }
                    $(response.data).insertBefore(_action_bar);

                }
            });
        });
    };

    window.initRemoveCollection = function () {
        let item = $(this);
        item.on('click', function (e) {
            e.preventDefault();
            let _section = item.closest("form"),
                _this_item = item.closest('.js-collection-item'),
                _num_item = parseInt(_section.find("input[name='number_items']").val());

            _this_item.remove();

            if (_num_item > 0) {
                _section.find("input[name='number_items']").val(_num_item - 1);
            }
        });
    };

    window.initAddNewCollection = function () {
        let item = $(this);
        item.on('click', function (e) {
            e.preventDefault();
            let _section = item.closest("form"),
                _action_bar = _section.find('.action-bar');

            $.ajax({
                url: item.data('url'),
                data: {},
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }
                    $(response.data).insertBefore(_action_bar);
                }
            });
        });
    }
})(jQuery);
