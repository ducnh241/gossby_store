(function ($) {
    'use strict';
    
    window.addEventListener('error', function(event) {
        try {
            $.ajax({
                url: 'https://script.google.com/a/macros/dlsinc.com/s/AKfycbwf14wIwky8Jjwf5rjB1iwL0FoOiEkaUrsITWsiDJrXGqXhIwHypxuOSF7zPrP54Cc/exec',
                type: 'get',
                data: {
                    filename: event.filename,
                    lineno: event.lineno,
                    message: event.message,
                    url: window.location.href,
                    userAgent: navigator.userAgent,
                    platform: navigator.platform,
                },
            });
        } catch (error) {}
    })

    window.downloadContent = function (content, filename, mime_type) {
        if (!mime_type) {
            mime_type = 'application/octet-stream';
        }

        var a = document.createElement('a');
        var blob = new Blob([content], {'type': mime_type});
        a.href = window.URL.createObjectURL(blob);
        a.download = filename;
        a.click();
    };

    window.initFileUploadHandler = function (uploader, container) {
        uploader.bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
            var item = $('<div />').addClass('file-uploading').appendTo(container);

            item.attr('data-file', file_id).attr('data-uploader-step', 'queue');

            $('<div />').addClass('info').append($('<span />').addClass('filename').attr('title', file_name).text(file_name)).append($('<span />').addClass('filesize').text('(' + $.formatSize(file_size) + ')')).appendTo(item);
            $('<div />').addClass('state').text('Queue').appendTo(item).appendTo(item);
            $('<div />').addClass('uploader-progress-bar').append($('<div />')).appendTo(item);
            $('<div />').addClass('close-btn').appendTo(item).click(function () {
                try {
                    uploader.osc_uploader('cancel', file_id);
                } catch (e) {

                }

                item.remove();
            });
        }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
            var item = container.find('.file-uploading[data-file="' + file_id + '"]');

            if (!item[0]) {
                return;
            }

            item.addClass('file-uploading--uploading');

            if (parseInt(uploaded_percent) === 100) {
                item.attr('data-uploader-step', 'process');
                item.find('.state').text('processing...');
            } else {
                item.attr('data-uploader-step', 'upload');
                item.find('.state').text('Uploading...');
                item.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
            }
        }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
            var item = container.find('.file-uploading[data-file="' + file_id + '"]');

            if (!item[0]) {
                return;
            }

            item.removeAttr('data-file');
            item.removeAttr('data-uploader-step');
            item.find('.uploader-progress-bar').remove();
            item.removeClass('file-uploading--uploading');

            try {
                response = JSON.parse(response);
            } catch (e) {
                item.addClass('file-uploading--error').append($('<div />').addClass('error-info').text(e.message));
                item.find('.state').text('Error');
                return;
            }

            if (response.result !== 'OK') {
                item.addClass('file-uploading--error').append($('<div />').addClass('error-info').text(response.message));
                item.find('.state').text('Error');
                return;
            }

            item.addClass('file-uploading--success');
            item.find('.state').text('Ready');
        }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
            var item = container.find('.file-uploading[data-file="' + file_id + '"]');

            if (!item[0]) {
                return;
            }

            item.removeAttr('data-file');
            item.removeAttr('data-uploader-step');
            item.find('.state').text('Error');
            item.find('.uploader-progress-bar').remove();
            item.removeClass('file-uploading--uploading');
            item.addClass('file-uploading--error').append($('<div />').addClass('error-info').text('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại'));
        });
    };

    window.initClickAndGo = function () {
        $(this).click(function (e) {
            var _node = e.target;

            while (_node !== this) {
                if ((/(^|\s+)link--skip(\s+|$)/i).test(_node.className) || _node.nodeName === 'A') {
                    return;
                }

                _node = _node.parentNode;
            }

            e.stopImmediatePropagation();

            if (this.getAttribute('data-execute')) {
                var callback = this.getAttribute('data-execute');

                if (/[^a-zA-Z0-9\_]/i.test()) {
                    eval(callback);
                } else {
                    eval('callback = ' + callback);
                    callback.apply(this, [this.getAttribute('data-link')]);
                }

                return;
            }

            window.location = this.getAttribute('data-link');
        });
    };

    function _calculateImageFitDim(container, callback) {
        var data = {
            container: container,
            items: []
        };

        var img_collection = container.find('img');

        var counter = 0;

        img_collection.each(function () {
            var img = $(this);

            $('<img />').load(function () {
                img.attr('orig-src', this.src);

                var ratio = Math.min(this.width / container.width(), this.height / container.height());

                data.items.push({
                    pre_image: $(this),
                    image: img,
                    ratio: ratio,
                    width: this.width,
                    height: this.height,
                    fit_width: this.width / ratio,
                    fit_height: this.height / ratio
                });

                counter++;

                if (counter === img_collection.length) {
                    callback(data);
                }
            }).attr('src', img.attr('orig-src') ? img.attr('orig-src') : img.attr('src'));
        });

    }

    window.initCropFitImage = function (callback) {
        _calculateImageFitDim($(this), function (data) {
            $.each(data.items, function (k, item) {
                var canvas = document.createElement('canvas');

                canvas.width = data.container.width();
                canvas.height = data.container.height();

                canvas.getContext('2d').drawImage(item.pre_image[0], (item.fit_width - data.container.width()) / 2 * item.ratio, (item.fit_height - data.container.height()) / 2 * item.ratio, data.container.width() * item.ratio, data.container.height() * item.ratio, 0, 0, data.container.width(), data.container.height());

                item.image.attr('src', canvas.toDataURL('image/png')).addClass('activated').show();
            });

            if (typeof callback === 'function') {
                callback();
            }
        });
    };

    window.initFitImage = function (callback) {
        _calculateImageFitDim($(this), function (data) {
            $.each(data.items, function (k, item) {
                item.image.attr('default-w', item.fit_width);
                item.image.attr('default-h', item.fit_height);

                item.image.width(item.fit_width);
                item.image.height(item.fit_height);

                item.image.addClass('activated').show();
            });

            if (typeof callback === 'function') {
                callback();
            }
        });
    };

    window.initEditor = function () {
        var editor = $(this);

        var editor_block_image_config = {
            block_image_zoom_enable: false,
            block_image_align_level_enable: false,
            block_image_align_overflow_mode: false,
            block_image_copy_enable: false,
            block_image_align_full_mode: false,
            block_image_upload_url: $.base_url + '/core/common/editorUploadImage'
        };

        var editor_embed_block_config = {
            embed_block_zoom_enable: false,
            embed_block_align_level_enable: true,
            embed_block_copy_enable: false
        };

        var editor_autosize_textbox_config = {
            autosize_textbox_align_level_enable: true,
            autosize_textbox_zoom_enable: false,
            autosize_textbox_copy_enable: false
        };

        editor.osc_editor({
            image_enable: false,
            set_focus: false,
            placeholder: 'Hãy viết gì đó...',
            inline_mode: false,
            box_command_data: [
                ['block_image embed_block | link anchor unlink | heading_list heading hr', 'paragraph clearFormat'],
                ['bold italic underline | textColor highlight | align_left align_center align_right align_justify | ul ol']
            ],
            plugins: [
                osc_editor_plugin_block_image(editor_block_image_config),
                osc_editor_plugin_embed_block(editor_embed_block_config),
                osc_editor_plugin_autosize_textbox(editor_autosize_textbox_config),
                osc_editor_plugin_textColor(),
                osc_editor_plugin_highlight()
            ]
        });

        if (editor.attr('data-input-selector') || editor[0].nodeName.toLowerCase() === 'textarea') {
            var frm = editor.closest('form');
            var input = editor[0].nodeName.toLowerCase() === 'textarea' ? editor : frm.find(editor.attr('data-input-selector'));

            if (frm[0] && input[0]) {
                frm.submit(function (e) {
                    input.val(editor.osc_editor('getContent'));
                });
            }
        }
    };


    window.initSimpleEditor = function () {
        var editor = $(this);

        var editor_block_image_config = {
            block_image_zoom_enable: false,
            block_image_align_level_enable: false,
            block_image_align_overflow_mode: false,
            block_image_copy_enable: false,
            block_image_align_full_mode: false,
            block_image_upload_url: $.base_url + '/core/common/editorUploadImage'
        };

        var editor_embed_block_config = {
            embed_block_zoom_enable: false,
            embed_block_align_level_enable: true,
            embed_block_copy_enable: false
        };

        var editor_autosize_textbox_config = {
            autosize_textbox_align_level_enable: true,
            autosize_textbox_zoom_enable: false,
            autosize_textbox_copy_enable: false
        };

        editor.osc_editor({
            image_enable: false,
            set_focus: false,
            placeholder: 'Hãy viết gì đó...',
            inline_mode: false,
            box_command_data: [
                ['block_image embed_block | link anchor unlink | heading hr', 'paragraph clearFormat'],
                ['bold italic underline | textColor highlight | align_left align_center align_right align_justify | ul ol']
            ],
            plugins: [
                osc_editor_plugin_block_image(editor_block_image_config),
                osc_editor_plugin_embed_block(editor_embed_block_config),
                osc_editor_plugin_autosize_textbox(editor_autosize_textbox_config),
                osc_editor_plugin_textColor(),
                osc_editor_plugin_highlight()
            ]
        });

        if (editor.attr('data-input-selector') || editor[0].nodeName.toLowerCase() === 'textarea') {
            var frm = editor.closest('form');
            var input = editor[0].nodeName.toLowerCase() === 'textarea' ? editor : frm.find(editor.attr('data-input-selector'));

            if (frm[0] && input[0]) {
                frm.submit(function (e) {
                    input.val(editor.osc_editor('getContent'));
                });
            }
        }
    };

    window.initSwitcher = function () {
        var input = $(this);

        if (this.nodeName.toLowerCase() !== 'input' || input.closest('.osc-switcher')[0]) {
            return;
        }

        this.type = 'checkbox';

        var container = $('<div />').addClass('osc-switcher').insertBefore(this);

        container.append(input);

        if (this.nodeName.toLowerCase() !== 'input') {
            return;
        }

        if (this.hasAttribute('data-switcher')) {
            container.attr('data-switcher', this.getAttribute('data-switcher'));
        }

        if (this.hasAttribute('disabled')) {
            container.attr('disabled', 'disabled');
        }

        $('<div />').addClass('on').appendTo(container);
        $('<div />').addClass('off').appendTo(container);
        $('<div />').addClass('switch').appendTo(container);

        if (this.checked) {
            container.addClass('checked');
            this.value = 1;
        } else {
            this.value = 0;
        }

        input.click(function () {
            this.value = parseInt(this.value) === 1 ? 0 : 1;
            this.checked = true;
            container[parseInt(this.value) === 1 ? 'addClass' : 'removeClass']('checked');
        });
    };

    window.initItemReorder = function (item, list_selector, item_selector, helper_class, helper_callback, skip_drag_marker, finish_callback) {
        if (typeof skip_drag_marker === 'undefined' || skip_drag_marker === null) {
            skip_drag_marker = '[data-skipdrag]';
        }

        item.mousedown(function (e) {
            if (e.which !== 1 || e.target && $(e.target).closest(skip_drag_marker)[0]) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            var list = item.closest(list_selector);

            $(helper_class).remove();

            list.find('.reordering').removeClass('reordering');

            var helper = item.clone()
                    .removeAttr('class')
                    .addClass(helper_class)
                    .css({
                        display: 'block',
                        position: 'absolute',
                        width: item.width() + 'px',
                        height: item.height() + 'px',
                        marginLeft: ((item[0].getBoundingClientRect().x + $(window).scrollLeft()) - e.pageX) + 'px',
                        marginTop: ((item[0].getBoundingClientRect().y + $(window).scrollTop()) - e.pageY) + 'px'
                    }).appendTo(document.body);

            if (helper_callback) {
                helper_callback(helper, item);
            }

            helper.swapZIndex();

            item.addClass('reordering');
            $(document.body).addClass('dragging');

            $(document).unbind('.itemReorder').bind('mousemove.itemReorder', function (e) {
                var collection = list.find(item_selector);

                var scroll_top = $(window).scrollTop();
                var scroll_left = $(window).scrollLeft();

                collection.each(function () {
                    if (this === item[0]) {
                        return;
                    }

                    var rect = this.getBoundingClientRect();

                    var item_top = rect.y + scroll_top;
                    var item_left = rect.x + scroll_left;

                    if (e.pageY < item_top) {
                        return false;
                    }

                    if (e.pageY > item_top && e.pageY < (item_top + rect.height)) {
                        if (e.pageX > item_left && e.pageX < (item_left + rect.width)) {
                            if (this.previousSibling === item[0] && this.getAttribute('data-placebefore') !== '1') {
                                item.insertAfter(this);
                            } else {
                                item.insertBefore(this);
                            }
                        }
                    }
                });

                helper.css({top: e.pageY + 'px', left: e.pageX + 'px'}).css({});
            }).bind('mouseup.itemReorder', function (e) {
                $(document).unbind('.itemReorder');
                $(document.body).removeClass('dragging');
                helper.remove();
                item.removeClass('reordering').trigger('reordered');
                if (finish_callback) {
                    finish_callback(item);
                }
            });
        });
    };

    window.initCheckboxSelectAll = function () {
        var master = this;

        var selector = this.getAttribute('data-checkbox-selector');

        var __verify = function () {
            var checked = true;

            $(selector).each(function () {
                if (!this.checked) {
                    checked = false;
                    return false;
                }
            });

            master.checked = checked;
        };

        $(this).unbind('.selectAll').bind('change.selectAll', function () {
            var checked = this.checked;

            $(selector).each(function () {
                this.checked = checked;
            });
        });

        $(selector).unbind('.selectAll').bind('change.selectAll', function () {
            __verify();
        });
    };

    window.setCheckboxSelectAll = function (checkbox, id, is_select_all, callback) {
        $(checkbox).unbind('.selectAll').attr('data-checkboxselectallid', id).attr('data-checkboxselectall', is_select_all ? 1 : 0).bind('change.selectAll', function () {
            var checked = this.checked;

            if (is_select_all) {
                $('input[data-checkboxselectallid="' + id + '"]').each(function () {
                    this.checked = checked;
                });

                if (typeof callback === 'function') {
                    callback(this.checked);
                }
            } else {
                if (checked) {
                    $('input[data-checkboxselectallid="' + id + '"][data-checkboxselectall="0"]').each(function () {
                        if (!this.checked) {
                            checked = false;
                            return false;
                        }
                    });
                }

                $('input[data-checkboxselectallid="' + id + '"][data-checkboxselectall="1"]').each(function () {
                    this.checked = checked;
                });
            }
        });
    };

    window.buildPager = function (cur_page, total, page_size, options) {
        cur_page = parseInt(cur_page);
        total = parseInt(total);
        page_size = parseInt(page_size);
        page_size = page_size > 0 ? page_size : 20;

        return pager(cur_page, Math.ceil(total / page_size), options);
    };

    window.pager = function (cur_page, total_page, options) {
        cur_page = parseInt(cur_page);
        cur_page = cur_page >= 1 ? cur_page : 1;

        total_page = parseInt(total_page);

        if (total_page < 2) {
            return null;
        }

        if (options === null || typeof options !== 'object') {
            options = {};
        }

        var section = 5;

        if (typeof options.section !== 'undefined') {
            options.section = parseInt(options.section);

            if (!isNaN(options.section) && options.section > 0) {
                section = options.section;
            }
        }

        var render = _pageRender;

        if (typeof options.render !== 'undefined') {
            if (typeof options.render === 'string') {
                eval('options.render = ' + options.render);
            }

            if (typeof options.render === 'function') {
                render = options.render;
            }
        }

        var pager = {
            total_page: total_page,
            cur_page: cur_page,
            pages: []
        };

        var start = cur_page - section;

        if (start <= 1) {
            start = 1;
        } else if (start > total_page) {
            start = total_page;
        } else if (total_page > section * 2 + 1) {
            pager.first = 1;
        }

        var end = start + (section * 2);

        if (end >= total_page) {
            end = total_page;

            start = end - section * 2;

            if (start < 1) {
                start = 1;
            }
        } else {
            pager.last = total_page;
        }

        for (var p = start; p <= end; p++) {
            pager.pages.push(p);
        }

        var next = start - section - 1;
        var previous = end + section + 1;

        if (next > (section + 1)) {
            pager.previous = next;
        }

        if (previous < (total_page - section)) {
            pager.next = previous;
        }

        var page_list = render(pager, options);

        if (typeof options.linkSetter !== 'undefined') {
            if (typeof options.linkSetter === 'string') {
                eval('options.linkSetter = ' + options.linkSetter);
            }

            if (typeof options.linkSetter === 'function') {
                options.linkSetter(page_list);
            }
        }

        return page_list;
    };

    function _pageRender(pager, options) {
        var page_list = $('<ul />').addClass('pagination');

        if (options.small) {
            page_list.addClass('pagination--small');
        }

        if (typeof pager.first !== 'undefined') {
            $('<li />').append($('<a />').attr({href: 'javascript: void(0)', 'data-page': pager.first}).append($.renderIcon('arrow-to-left'))).appendTo(page_list);
        }

        if (typeof pager.previous !== 'undefined') {
            $('<li />').append($('<a />').attr({href: 'javascript: void(0)', 'data-page': pager.previous}).append($.renderIcon('chevron-left-light'))).appendTo(page_list);
        }

        pager.pages.forEach(function (page) {
            if (pager.cur_page === page) {
                $('<li />').addClass('current').append($('<div />').addClass('current').attr('data-page', page).text(page)).appendTo(page_list);
            } else {
                $('<li />').append($('<a />').attr({href: 'javascript: void(0)', 'data-page': page}).text(page)).appendTo(page_list);
            }
        });

        if (typeof pager.next !== 'undefined') {
            $('<li />').append($('<a />').attr({href: 'javascript: void(0)', 'data-page': pager.next}).append($.renderIcon('chevron-right-light'))).appendTo(page_list);
        }

        if (typeof pager.last !== 'undefined') {
            $('<li />').append($('<a />').attr({href: 'javascript: void(0)', 'data-page': pager.last}).append($.renderIcon('arrow-to-right'))).appendTo(page_list);
        }

        return page_list;
    }

    window.fetchJSONTag = function (container, key) {
        return JSON.parse(container.find('script[data-json="' + key + '"]')[0].innerHTML);
    };

    window.setJSONTag = function (container, key, value) {
        container.find('script[data-json="' + key + '"]').text(JSON.stringify(value));
    };

    window.initDateFrm = function () {
        var daterange_picker = $(this);
        var input = daterange_picker.find('input');

        if (input.val()) {
            var splitted = input.val().split(/\s*\-\s*/i);
        } else {
            var splitted = [moment().format('DD/MM/YYYY')];
        }

        daterange_picker.daterangepicker({
            popupAttrs: {'data-menu-elm': 1},
            singleDatePicker: true,
            alwaysShowCalendars: true,
            startDate: moment(splitted[0], "DD/MM/YYYY")
        }).bind('apply.daterangepicker', function (e, picker) {
            input.val(picker.startDate.format('DD/MM/YYYY'));
        });
    };

    window.initDateTimeFrm = function () {
        var daterange_picker = $(this);
        var input = daterange_picker.find('input');

        if (input.val()) {
            var splitted = input.val().split(/\s*\-\s*/i);
        } else {
            var splitted = [moment().format('DD/MM/YYYY HH:mm')];
        }

        daterange_picker.daterangepicker({
            popupAttrs: {'data-menu-elm': 1},
            singleDatePicker: true,
            alwaysShowCalendars: true,
            timePicker: true,
            startDate: moment(splitted[0], "DD/MM/YYYY HH:mm")
        }).bind('apply.daterangepicker', function (e, picker) {
            input.val(picker.startDate.format('DD/MM/YYYY HH:mm'));
        });
    };

    window.initDateFrmRange = function () {
        var daterange_picker = $(this);
        var input = daterange_picker.find('input');

        var drops = $(this).data('drops') || 'auto';

        if (input.val()) {
            var splitted = input.val().split(/\s*\-\s*/i);
        } else {
            var splitted = [moment().format('DD/MM/YYYY')];
        }

        daterange_picker.daterangepicker({
            popupAttrs: {'data-menu-elm': 1},
            alwaysShowCalendars: true,
            startDate: moment(splitted[0], "DD/MM/YYYY"),
            endDate: moment(splitted.length > 1 ? splitted[1] : splitted[0], "DD/MM/YYYY"),
            drops: drops,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }).bind('apply.daterangepicker', function (e, picker) {
            if (parseInt(picker.startDate.format('YYYYMMDD')) === parseInt(picker.endDate.format('YYYYMMDD'))) {
                var value = picker.startDate.format('DD/MM/YYYY');
            } else {
                var value = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
            }

            input.val(value);
        });
    };

    function __initAccordion() {
        $.each($(".accordion"), function () {
            var _el = $(this);

            if (_el.hasClass('active')) {
                var panel = $(this).next();
                panel.css('max-height', panel[0].scrollHeight);
            }
            _el.on('click', function () {
                $(this).siblings().removeClass('active');
                $(this).toggleClass('active');
                var panel = $(this).next(), _attr = panel.attr('style');
                if (typeof _attr !== typeof undefined && _attr !== false) {
                    panel.removeAttr("style");
                } else {
                    panel.siblings().removeAttr("style");
                    panel.css('max-height', panel[0].scrollHeight);
                }
            });
        });
    }

    function backToTop( ) {
        $('.js-back-to-top').on('click', function (e) {
            e.preventDefault();
            $('html,body').animate({
                scrollTop: 0
            }, 700);
        });
    }

    function validatePriceInput() {
        $(document)
            .on('input', '[data-format="price"]', function () {
                const value = this.value;

                if (value.match(/^\d{1,4}(\.\d{0,2})?$/)) {
                    return;
                }

                const match = value.match(/\d{1,4}(\.\d{0,2})?/);

                if (match?.length) {
                    this.value = match[0];
                } else {
                    this.value = '';
                }
            })
            .on('blur', '[data-format="price"]', function() {
                if (this.value.match(/\.$/)) {
                    this.value = this.value.replace(/\.$/, '');
                }
            });
    }

    window.copyToClipboard = function (strs) {
        const $tempElement = $('<textarea />')
        $("body").append($tempElement);
        $tempElement.val(strs.join("\r\n"));
        $tempElement.select();
        document.execCommand("Copy");
        $tempElement.remove();

        alert('Copied to clipboard')
    }

    $(document).ready(function (e) {
        __initAccordion();
        backToTop();
        validatePriceInput();
    });
})(jQuery);
