(function ($) {
    'use strict';

    function _renderBulkFrm(frm_title, action_title, btn_action_title, option_frm_callback, process_callback, selector_name, item_name) {
        if (typeof selector_name === 'undefined') {
            selector_name = 'record_id';
        }

        if (typeof item_name === 'undefined') {
            item_name = 'orders';
        }

        $.unwrapContent('postOfficeMarketingBulkFrm');

        var modal = $('<div />').addClass('osc-modal').width(350);

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html(frm_title).appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('postOfficeMarketingBulkFrm');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        if (typeof option_frm_callback === 'function') {
            option_frm_callback(modal_body);
        }

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-outline ').html('Cancel').click(function () {
            $.unwrapContent('postOfficeMarketingBulkFrm');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html(btn_action_title).click(function () {

            process_callback(function (response) {
                alert(response.message);
            }, function (response) {
                $.unwrapContent('postOfficeMarketingBulkFrm');
            });
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'postOfficeMarketingBulkFrm'});

        modal.moveToCenter().css('top', '100px');

    }
    
    window.initPostOfficeMarketing = function () {
        var btn = $(this);

        $(this).click(function () {
            _renderBulkFrm('Send email marketing', 'Send email to', 'Send emails', function (modal_body) {

                var row = $('<div />').addClass('mt10').appendTo(modal_body);

                var cell = $('<div />').appendTo(row);
                $('<label />').text('Email title').appendTo(cell);
                $('<input />').attr({type: 'text', id: 'marketing-email-title'}).addClass('styled-input').appendTo($('<div />').appendTo(cell));

                row = $('<div />').addClass('mt10').appendTo(modal_body);

                var cell = $('<div />').appendTo(row);
                $('<label />').text('Sender Email').appendTo(cell);
                $('<input />').attr({type: 'text', id: 'marketing-sender-email'}).addClass('styled-input').appendTo($('<div />').appendTo(cell));

                row = $('<div />').addClass('mt10').appendTo(modal_body);

                var cell = $('<div />').appendTo(row);
                $('<label />').text('Sender name').appendTo(cell);
                $('<input />').attr({type: 'text', id: 'marketing-sender-name'}).addClass('styled-input').appendTo($('<div />').appendTo(cell));

                row = $('<div />').addClass('mt10').appendTo(modal_body);

                cell = $('<div />').appendTo(row);

                var uploader = $('<div />').appendTo(cell);
                var preview = $('<div />').appendTo(cell);

                uploader.osc_uploader({
                    max_files: -1,
                    process_url: btn.attr('data-upload-url'),
                    btn_content: 'Browse a list email',
                    dragdrop_content: 'Drop here to upload',
                    extensions: ['xlsx'],
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
                        $('<input />').attr({type: 'hidden', value: response.data.file}).attr({'id': 'marketing-file', 'data-file' : file_id}).appendTo(preview);
                    }
                }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
                    preview.find('input[data-file="' + file_id + '"]').remove();
                });

                initFileUploadHandler(uploader, preview);
            }, function (error_callback, success_callback) {
                var post_data = {};

                post_data.subject = $('#marketing-email-title').val().trim();

                if (!post_data.subject) {
                    alert('Email title is empty');
                    return;
                }

                post_data.sender_email = $('#marketing-sender-email').val().trim();

                if (!post_data.sender_email) {
                    alert('Sender mail is empty');
                    return;
                }

                post_data.sender_name = $('#marketing-sender-name').val().trim();

                if (!post_data.sender_name) {
                    alert('Sender name is empty');
                    return;
                }
                post_data.file = $('#marketing-file').val().trim();

                $.ajax({
                    url: btn.attr('data-url'),
                    data: post_data,
                    success: function (response) {
                        if (response.result !== 'OK') {
                            error_callback(response);
                            return;
                        }

                        alert(response.data);

                        success_callback(response);
                    }
                });
            });
        });
    };
})(jQuery);