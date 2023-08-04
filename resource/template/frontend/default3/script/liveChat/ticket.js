window.initLiveChatAttachment = function () {
    var body = $(this);
    var uploader = $('<div />').addClass('btn btn-primary btn-small mb5').appendTo(body);
    var preview = $('<div />').appendTo(body);
    var max_filesize = 20*1024*1024;
    uploader.osc_uploader({
        max_files: 5,
        max_filesize: max_filesize,
        process_url: body.attr('data-upload-url'),
        btn_content: 'Attach images',
        dragdrop_content: 'Drop here to upload',
        extensions: ['png', 'jpg', 'gif', 'jpeg'],
        xhrFields: {withCredentials: true},
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-OSC-Cross-Request': 'OK'
        }
    }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
        $('.error-message').remove();
        uploader.hide();
    }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
        try {
            response = JSON.parse(response);
        } catch (e) {
        }

        if (response.result === 'OK') {
            var file_input = $('<input />').addClass('file-id-' + file_id).attr({type: 'hidden',name: 'attachments[]'}).appendTo(body);
            file_input.val(response.data);
        }
    }).bind('uploader_cancel', function (e, file_id, error_code, error_message) {
        $(".file-id-"+file_id).remove();
        uploader.show();
    }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
        var message = ''
        switch (file_id) {
            case 'sizeError':
                message = 'File size limit exceeded. The maximum file size is 20 MB.';
                break;
            case 'typeError':
                message = 'Sorry! The image file format you uploaded is not supported.';
                break
            default:
                message = 'Upload error, please try again';
                break;
        }
        $('<span />').addClass('error-message').html(message).appendTo(body);

        removeErrorMessage();
    });

    if (body.find('.error-message')) {
        removeErrorMessage();
    }

    initFileUploadHandler(uploader, preview);
};

window.btnCreateTicket = function () {
    var btn = $(this);

    var frm = btn.closest('form');

    frm.submit(function (e) {
        if (btn.attr('disabled') === 'disabled') {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return;
        }

        btn.attr('disabled', 'disabled');
        btn.text('Creating...');
    });
};

window.removeErrorMessage = function() {
    setTimeout(function() {
        $('.error-message').remove();
    }, 5000);
}


