(function ($) {
    'use strict';

    window.catalogItemCustomizeInitExportDataBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('catalogItemCustomizeDesignExportDataFrm');

            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Export design data to XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogItemCustomizeDesignExportDataFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            $('<div />').text('Export').appendTo(modal_body);

            var row = $('<div />').addClass('mt10').appendTo(modal_body);

            $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'all', id: 'export_condition__all', checked: 'checked'})).append($('<ins />')).appendTo(row);
            $('<label />').attr('for', 'export_condition__all').addClass('label-inline').text('All designs').appendTo(row);

            row = $('<div />').addClass('mt5').appendTo(modal_body);

            $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'search', id: 'export_condition__search'})).append($('<ins />')).appendTo(row);
            $('<label />').attr('for', 'export_condition__search').addClass('label-inline').text('Current search').appendTo(row);

//            row = $('<div />').addClass('mt5').appendTo(modal_body);
//
//            $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'selected', id: 'export_condition__selected'})).append($('<ins />')).appendTo(row);
//            $('<label />').attr('for', 'export_condition__selected').addClass('label-inline').text('Selected designs').appendTo(row);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogItemCustomizeDesignExportDataFrm');
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml10').html('Export data').click(function () {
                var submit_btn = this;

                if (this.getAttribute('disabled') === 'disabled') {
                    return;
                }

                this.setAttribute('disabled', 'disabled');

                var post_data = {condition: null};

                $('input[name="export_condition"]').each(function () {
                    if (this.checked) {
                        post_data.condition = this.value;
                        return false;
                    }
                });

//                if (post_data.condition === 'selected') {
//                    post_data.selected_ids = [];
//
//                    $('input[name="design_id"]:checked').each(function () {
//                        post_data.selected_ids.push(this.value);
//                    });
//                }

                $.ajax({
                    url: btn.attr('data-export-url'),
                    data: post_data,
                    success: function (response) {
                        submit_btn.removeAttribute('disabled');

                        if (response.result !== 'OK') {
                            alert(response.message);
                            return;
                        }

                        $.unwrapContent('catalogItemCustomizeDesignExportDataFrm');

                        if (typeof response.data.url !== 'undefined') {
                            window.location = response.data.url;
                        } else {
                            alert(response.data.message);
                        }
                    }
                });
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'catalogItemCustomizeDesignExportDataFrm'});

            modal.moveToCenter().css('top', '100px');

            if (!$('input[name="product_id"]:checked')[0]) {
                $('#export_condition__selected').attr('disabled', 'disabled');
            } else {
                $('#export_condition__selected')[0].checked = true;
            }

            if (btn.attr('data-search') !== '1') {
                $('#export_condition__search').attr('disabled', 'disabled');
            }
        });
    };

    window.catalogItemCustomizeInitImagePreview = function () {
        $('<div />').addClass('preview').css('background-image', 'url(' + this.getAttribute('data-image') + ')').appendTo(this);
    };

    window.catalogItemCustomizeInitToggleCustomizeInfoBtn = function () {
        var container = $(this).closest('.customize-design-item').find('.customize-info');

        $(this).click(function () {
            container.toggleClass('active');
        })
    };

    window.catalogItemCustomizeInitDesignUploader = function () {
        var container = $(this);

        var __renderPreview = function (url) {
            container.find('.preview:not(.main)').remove();

            var preview = $('<div />').addClass('preview').appendTo(container);

            if (url) {
                preview.css('background-image', 'url(' + url + ')');
            }

            return preview;
        };

        var uploader = $('<div />').appendTo(container);

        uploader.osc_uploader({
            max_files: 1,
            process_url: container.attr('data-upload-url'),
            btn_content: 'Browse your image',
            dragdrop_content: 'Drop here to upload',
            image_mode: true,
            xhrFields: {withCredentials: true},
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-OSC-Cross-Request': 'OK'
            }
        }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
            uploader.hide();

            var preview = __renderPreview('');

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
            var preview = container.find('.preview:not(.main)');

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
            pointer.success = false;

            var preview = container.find('.preview:not(.main)');

            if (preview.attr('file-id') !== file_id) {
                return;
            }

            preview.remove();

            eval('response = ' + response);

            if (response.result !== 'OK') {
                alert(response.message);

                uploader.show();

                return;
            }

            container.closest('.customize-design-item').before(response.data.item).remove();
        }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
            var preview = container.find('.preview:not(.main)');

            if (preview.attr('file-id') !== file_id) {
                return;
            }

            preview.remove();

            uploader.show();

            alert('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại');
        });

        __renderPreview(container.attr('data-image')).addClass('main');
    };

    window.catalogItemCustomizeInitTakeDesignBtn = function () {
        $(this).click(function (e) {
            e.preventDefault();

            if (this.getAttribute('disabled') === 'disabled') {
                return;
            }

            this.setAttribute('disabled', 'disabled');

            var node = this;

            $.ajax({
                url: this.getAttribute('href'),
                success: function (response) {
                    node.removeAttribute('disabled');

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    $(node).closest('.customize-design-item').before(response.data.item).remove();
                }
            });
        });
    };

    window.catalogItemCustomizeInitBrowser = function () {
        var browser = $(this);

        function __add(id, title) {
            var item = $('<div />').addClass('customize-item').text(title).appendTo(browser.parent());

            $('<input />').attr({type: 'hidden', name: 'customize_id', value: id}).appendTo(item);

            $('<ins />').click(function () {
                $(this).closest('.customize-item').trigger('remove');
            }).appendTo(item);

            item.attr('data-id', id).bind('remove', function () {
                item.remove();
            });

            return item;
        }

        function __check(item, collection) {
            item.find('.image').remove();

            var checker = $('<div />').addClass('customize-item-checker').prependTo(item);

            var customize_item = $('.customize-item[data-id="' + collection.id + '"]');

            if (customize_item[0]) {
                customize_item.unbind('.customizeItemUpdate').bind('remove.customizeItemUpdate', function () {
                    checker.html('');
                });

                checker.append($.renderIcon('check-solid'));
            }
        }

        function __update(item, customize_type) {
            var customize_item = $('.customize-item');

            var checker = item.find('.customize-item-checker');

            customize_item.trigger('remove');

            if (!customize_item[0] || parseInt(customize_item.attr('data-id')) !== customize_type.id) {
                customize_item = __add(customize_type.id, customize_type.title);

                customize_item.bind('remove.customizeItemUpdate', function () {
                    checker.html('');
                });

                checker.html('').append($.renderIcon('check-solid'));
            }
        }

        if (browser.attr('data-item')) {
            var customize_item = JSON.parse(browser.attr('data-item'));
            __add(customize_item.id, customize_item.title);
        }

        browser.osc_ui_itemBrowser({
            focus_browse: true,
            click_callback: __update,
            item_render_callback: __check,
            browse_url: browser.attr('data-browse-url')
        });
    };

    window.catalogItemCustomizeInitUploadDesignsBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('uploadDesignsFrm');

            var fulfill_btn = null;

            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Get Tracking Code orders by XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('uploadDesignsFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            var uploader = $('<div />').appendTo(modal_body);
            var preview = $('<div />').appendTo(modal_body);

            uploader.osc_uploader({
                max_files: 1000,
                process_url: btn.attr('data-uploads-url'),
                btn_content: 'Choose multipe jpeg/png files',
                dragdrop_content: 'Drop here to upload',
                extensions: ['png', 'jpg'],
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.hide();
            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    return;
                }

            }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
                uploader.show();
            });

            initFileUploadHandler(uploader, preview);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('uploadDesignsFrm');
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'uploadDesignsFrm'});

            modal.moveToCenter().css('top', '100px');
        });
    };
})(jQuery);