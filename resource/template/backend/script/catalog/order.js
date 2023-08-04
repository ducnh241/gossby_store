(function ($) {
    'use strict';

    function _parseAmount(value, max_value) {
        if (typeof value === 'string') {
            if (/([^\.0-9]|\..*\.)/.test(value)) {
                return 0;
            }

            value = value.trim();

            var dot = value[value.length - 1] === '.' ? '.' : '';
        }

        value = parseFloat(value);

        if (isNaN(value)) {
            return 0;
        }

        value = $.round(value, 2);

        if (max_value && value > max_value) {
            value = max_value;
        }

        return value + dot;
    }

    function _renderBulkFrm(frm_title, action_title, btn_action_title, in_search, option_frm_callback, process_callback, selector_name, item_name) {
        if (typeof selector_name === 'undefined') {
            selector_name = 'order_id';
        }

        if (typeof item_name === 'undefined') {
            item_name = 'orders';
        }

        $.unwrapContent('catalogOrderBulkFrm');

        var modal = $('<div />').addClass('osc-modal').width(350);

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html(frm_title).appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('catalogOrderBulkFrm');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        $('<div />').text(action_title).appendTo(modal_body);

        var row = $('<div />').addClass('mt10').appendTo(modal_body);

        $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'bulk_condition', value: 'all', id: 'bulk_condition__all', checked: 'checked'})).append($('<ins />')).appendTo(row);
        $('<label />').attr('for', 'bulk_condition__all').addClass('label-inline').text('All ' + item_name).appendTo(row);

        row = $('<div />').addClass('mt5').appendTo(modal_body);

        $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'bulk_condition', value: 'search', id: 'bulk_condition__search'})).append($('<ins />')).appendTo(row);
        $('<label />').attr('for', 'bulk_condition__search').addClass('label-inline').text('Current search').appendTo(row);

        row = $('<div />').addClass('mt5').appendTo(modal_body);

        $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'bulk_condition', value: 'selected', id: 'bulk_condition__selected'})).append($('<ins />')).appendTo(row);
        $('<label />').attr('for', 'bulk_condition__selected').addClass('label-inline').text('Selected ' + item_name).appendTo(row);

        if (typeof option_frm_callback === 'function') {
            option_frm_callback(modal_body);
        }

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('catalogOrderBulkFrm');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html(btn_action_title).click(function () {
            var condition = null;

            $('input[name="bulk_condition"]').each(function () {
                if (this.checked) {
                    condition = this.value;
                    return false;
                }
            });

            if (condition === 'selected') {
                condition = [];

                $('input[name="' + selector_name + '"]:checked').each(function () {
                    condition.push(this.value);
                });
            }

            process_callback(condition, function (response) {
                alert(response.message);
            }, function (response) {
                $.unwrapContent('catalogOrderBulkFrm');
            });
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'catalogOrderBulkFrm'});

        modal.moveToCenter().css('top', '100px');

        if (!$('input[name="' + selector_name + '"]:checked')[0]) {
            if (in_search) {
                $('#bulk_condition__search')[0].checked = true;
            } else {
                $('#bulk_condition__selected').attr('disabled', 'disabled');
            }
        } else {
            $('#bulk_condition__selected')[0].checked = true;
        }

        if (!in_search) {
            $('#bulk_condition__search').attr('disabled', 'disabled');
        }
    }

    window.initCatalogOrderDetailSendMailBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('catalogOrderDetailBulkFrm');

            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Send email about order info to customer').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogOrderDetailBulkFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            var row = $('<div />').addClass('mt10').appendTo(modal_body);

            var cell = $('<div />').appendTo(row);
            $('<label />').text('Email title').appendTo(cell);
            $('<input />').attr({type: 'text', id: 'order-email-title'}).addClass('styled-input').appendTo($('<div />').appendTo(cell));

            row = $('<div />').addClass('mt10').appendTo(modal_body);

            cell = $('<div />').appendTo(row);
            $('<label />').text('Email message').appendTo(cell);
            $('<textarea />').attr({type: 'text', id: 'order-email-content'}).addClass('styled-textarea').appendTo($('<div />').appendTo(cell));

            row = $('<div />').addClass('mt10').appendTo(modal_body);

            cell = $('<div />').appendTo(row);

            var uploader = $('<div />').appendTo(cell);
            var preview = $('<div />').appendTo(cell);

            uploader.osc_uploader({
                max_files: -1,
                process_url: btn.attr('data-upload-url'),
                btn_content: 'Browse a attachment file',
                dragdrop_content: 'Drop here to upload',
                extensions: ['csv', 'xlsx', 'gif', 'png', 'jpg', 'zip', 'rar', 'txt', 'html'],
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    return;
                }

                if (response.result === 'OK') {
                    $('<input />').attr({type: 'hidden', value: response.data.file}).attr('data-attachment', file_id).appendTo(preview);
                }
            }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
                preview.find('input[data-attachment="' + file_id + '"]').remove();
            });

            initFileUploadHandler(uploader, preview);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogOrderDetailBulkFrm');
            }).appendTo(action_bar);

            var  send_email_btn = $('<button />').addClass('btn btn-primary ml10').html('Send email').click(function () {
                if (send_email_btn.attr('disabled') === 'disabled') {
                    return;
                }
                var post_data = {
                    condition: 'detail'
                };

                post_data.order_id = parseInt(btn.attr('data-order-id'));

                if (post_data.order_id < 1) {
                    alert('Data order is incorrect');
                    return;
                }

                post_data.email_title = $('#order-email-title').val().trim();

                if (!post_data.email_title) {
                    alert('Email title is empty');
                    return;
                }

                post_data.email_content = $('#order-email-content').val().trim();

                if (!post_data.email_content) {
                    alert('Email content is empty');
                    return;
                }

                post_data.attachment = [];

                $('input[data-attachment]').each(function () {
                    post_data.attachment.push(this.value);
                });

                $.ajax({
                    url: btn.attr('data-url'),
                    data: post_data,
                    success: function (response) {
                        if (response.result !== 'OK') {
                            alert(response.message);
                            return;
                        }
                        alert(response.data.message);
                        $.unwrapContent('catalogOrderDetailBulkFrm');
                        window.location.reload();
                    }
                });
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'catalogOrderDetailBulkFrm'});

            modal.moveToCenter().css('top', '100px');
        });
    };

    window.initCatalogOrderBulkGetTrackingCodeBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            $.unwrapContent('catalogOrderBulkGetTrackingCodeFrm');

            var fulfill_btn = null;

            var modal = $('<div />').addClass('osc-modal').width(350);

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Get Tracking Code orders by XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogOrderBulkGetTrackingCodeFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);
            var file_input = $('<input />').attr({type: 'hidden'}).appendTo(modal_body);

            var uploader = $('<div />').appendTo(modal_body);
            var preview = $('<div />').appendTo(modal_body);

            uploader.osc_uploader({
                max_files: 1,
                process_url: btn.attr('data-get-tracking-code-upload-url'),
                btn_content: $('<div />').addClass('btn btn-primary').text('Browse a XLSX file'),
                dragdrop_content: 'Drop here to upload',
                extensions: ['xlsx'],
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.hide();
                file_input.val('');
            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    return;
                }

                if (response.result === 'OK') {
                    fulfill_btn.removeAttr('disabled');
                    file_input.val(response.data.file);
                }
            }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
                uploader.show();
                file_input.val('');
                fulfill_btn.attr('disabled', 'disabled');
            });

            initFileUploadHandler(uploader, preview);

            $('<div />').addClass('mt10').html('Download a <a href="#">sample XLSX template</a> to see an example of the format require').appendTo(modal_body);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogOrderBulkGetTrackingCodeFrm');
            }).appendTo(action_bar);



            fulfill_btn = $('<button />').addClass('btn btn-primary ml10').attr('disabled', 'disabled').html('Send to Get Tracking').click(function () {
                if (fulfill_btn.attr('disabled') === 'disabled') {
                    return;
                }

                var file = file_input.val();

                if (!file) {
                    alert('Please upload file to fulfill');
                    return;
                }

                this.setAttribute('disabled', 'disabled');
                this.setAttribute('data-state', 'submitting');

                fulfill_btn.prepend($($.renderIcon('preloader')).addClass('mr15'));

                $.ajax({
                    url: btn.attr('data-get-tracking-code-url'),
                    data: {file: file},
                    success: function (response) {
                        fulfill_btn.removeAttr('disabled');
                        fulfill_btn.removeAttr('data-state');

                        fulfill_btn.find('svg').remove();

                        if (response.result !== 'OK') {
                            alert(response.message);
                            return;
                        }

                        alert(response.data.message);

                        window.location.reload(true);
                    }
                });
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'catalogOrderBulkGetTrackingCodeFrm'});

            modal.moveToCenter().css('top', '100px');
        });
    };

    window.initCatalogOrderDownloadReportBtn = function () {
        $(this).click(function () {
            var btn = $(this);

            $.unwrapContent('catalogOrderDownloadReportFrm');

            var fulfill_btn = null;

            var modal = $('<div />').addClass('osc-modal').width(350);

            $.wrapContent(modal, {key: 'catalogOrderDownloadReportFrm'});

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Download report form').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogOrderDownloadReportFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);


            $('<div />').text('Date range').appendTo(modal_body);

            var row = $('<div />').addClass('mt10').appendTo(modal_body);

            var daterange_picker = $('<div />').addClass('styled-date-time-input').appendTo(row);
            var input = $('<input />').attr({type: 'text'}).appendTo($('<div />').addClass('date-input').append($.renderIcon('calendar-alt')).appendTo(daterange_picker));

            var splitted = [moment().format('DD/MM/YYYY')];

            daterange_picker.daterangepicker({
                parentEl: modal.parent(),
                alwaysShowCalendars: true,
                startDate: moment(splitted[0], "DD/MM/YYYY"),
                endDate: moment(splitted.length > 1 ? splitted[1] : splitted[0], "DD/MM/YYYY"),
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

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogOrderDownloadReportFrm');
            }).appendTo(action_bar);

            fulfill_btn = $('<button />').addClass('btn btn-primary ml10').html('Download').click(function () {
                var date_range = input.val();

                $.unwrapContent('catalogOrderDownloadReportFrm');

                var download_url = btn.attr('data-url');

                if (date_range) {
                    download_url += (download_url.indexOf('?') >= 0 ? '&' : '?') + 'date=' + date_range;
                }

                window.location = download_url;
            }).appendTo(action_bar);

            modal.moveToCenter().css('top', '100px');
        });
    };

    window.initCatalogOrderBulkQueueDeleteBtn = function () {
        var btn = $(this);

        $(this).click(function () {
            _renderBulkFrm('Delete Order Bulk Queues', 'Delete', 'Delete queues', btn.attr('data-search') === '1', function (modal_body) {
            }, function (condition, error_callback, success_callback) {
                $.ajax({
                    url: btn.attr('data-process-url'),
                    data: {condition: condition},
                    success: function (response) {
                        if (response.result !== 'OK') {
                            error_callback(response);
                            return;
                        }

                        alert(response.data.message);

                        success_callback(response);

                        window.location.reload();
                    }
                });
            }, 'queue_id', 'queues');
        });
    };

    window.initCatalogOrderBulkQueueGetInfo = function () {
        $(this).click(function () {
            $.unwrapContent('catalogOrderBulkQueueInfoFrm');

            var modal = $('<div />').addClass('osc-modal').css('width', 'calc(100% - 100px)');

            var header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html(this.getAttribute('data-title')).appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogOrderBulkQueueInfoFrm');
            }).appendTo(header);

            var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            $('<iframe />').attr('src', this.getAttribute('data-link')).css({width: '100%', height: 'calc(100vh - 300px)', border: '0'}).appendTo(modal_body);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogOrderBulkQueueInfoFrm');
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'catalogOrderBulkQueueInfoFrm'});

            modal.moveToCenter().css('top', '50px');
        })
    };

    window.initProcessOrderBulkActionBtn = function () {
        $(this).click(function () {
            if (this.getAttribute('disabled') === 'disabled') {
                return;
            }

            var ids = [];

            $('input[name="queue_id"]:checked').each(function () {
                ids.push(this.value);
            });

            if (ids.length < 1) {
                return;
            }

            if (this.getAttribute('data-confirm')) {
                if (!window.confirm(this.getAttribute('data-confirm'))) {
                    return;
                }
            }

            this.setAttribute('disabled', 'disabled');

            var btn = this;

            $.ajax({
                url: this.getAttribute('data-link'),
                data: {id: ids},
                success: function (response) {
                    btn.removeAttribute('disabled');

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    alert(response.data.message);

                    window.location.reload();
                }
            });
        });
    };
})(jQuery);