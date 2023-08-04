(function ($) {
    'use strict';

    window.personalizedDesignInitImportBtn = function () {
        var btn = $(this);

        var uploader = $('<div />').addClass('uploader').html(btn.html());
        var progress = $('<div />').addClass('file-uploading').html(btn.html()).append($('<div />').addClass('uploader-progress-bar').append($('<div />')));

        btn.html(uploader);

        uploader.osc_uploader({
            max_files: 1,
            process_url: btn.attr('data-process-url'),
            btn_content: uploader.html(),
            dragdrop_content: 'Drop here to upload',
            extensions: ['json'],
            xhrFields: {withCredentials: true},
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-OSC-Cross-Request': 'OK'
            }
        }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
            uploader.detach();
            progress.appendTo(btn);

            progress.removeAttr('data-uploader-step');
            progress.removeClass('file-uploading--uploading');
            progress.find('.uploader-progress-bar > div').width(0);
        }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
            progress.addClass('file-uploading--uploading');

            if (parseInt(uploaded_percent) === 100) {
                progress.attr('data-uploader-step', 'process');
            } else {
                progress.attr('data-uploader-step', 'upload');
                progress.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
            }
        }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
            pointer.success = false;

            progress.detach();
            uploader.appendTo(btn);

            try {
                response = JSON.parse(response);
            } catch (e) {
                return;
            }

            if (response.result !== 'OK') {
                alert(response.message);
            } else {
                alert('Personalized design has been added to import queue');
            }
        }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
            progress.detach();
            uploader.appendTo(btn);
        });
    };

    window.personalizedDesignInitBrowser = function () {
        var browser = $(this);

        function __add(id, title) {
            var item = $('<div />').addClass('personalized-design').text(title).appendTo(browser.parent());

            $('<input />').attr({type: 'hidden', name: 'personalized_design_id[]', value: id}).appendTo(item);

            $('<ins />').click(function () {
                $(this).closest('.personalized-design').trigger('remove');
            }).appendTo(item);

            item.attr('data-id', id).bind('remove', function () {
                item.remove();
            });

            return item;
        }

        function __check(item, collection) {
            item.find('.image').remove();

            var checker = $('<div />').addClass('personalized-design-checker').prependTo(item);

            var design_item = $('.personalized-design[data-id="' + collection.id + '"]');

            if (design_item[0]) {
                design_item.unbind('.personalizedDesignUpdate').bind('remove.personalizedDesignUpdate', function () {
                    checker.html('');
                });

                checker.append($.renderIcon('check-solid'));
            }
        }

        function __update(item, customize_type) {
            var design_item = $('.personalized-design');

            var checker = item.find('.personalized-design-checker');

            design_item.trigger('remove');

            if (!design_item[0] || parseInt(design_item.attr('data-id')) !== customize_type.id) {
                design_item = __add(customize_type.id, customize_type.title);

                design_item.bind('remove.personalizedDesignUpdate', function () {
                    checker.html('');
                });

                checker.html('').append($.renderIcon('check-solid'));
            }
        }

        if (browser.attr('data-item')) {
            var design_item = JSON.parse(browser.attr('data-item'));
            __add(design_item.id, design_item.title);
        }

        browser.osc_ui_itemBrowser({
            focus_browse: true,
            click_callback: __update,
            item_render_callback: __check,
            browse_url: browser.attr('data-browse-url')
        });
    };

})(jQuery);