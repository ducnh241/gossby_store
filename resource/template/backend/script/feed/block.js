window.initBlockListBtn = function () {
    let btn_bulk_block = $(this)
    btn_bulk_block.click(function () {
        $.unwrapContent('feedBulkBlock');
        let modal_form = $('<form />').attr('id', 'feed-bulk-block').addClass('osc-modal');

        let header = $('<header />').appendTo(modal_form);

        let import_btn = null;

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('feedBulkBlock');
        }).appendTo(header);

        $('<div />').addClass('title').html('Bulk Block Content by XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));
        let modal_body = $('<div />').addClass('body post-frm').appendTo(modal_form);

        // Add input select file

        let file_input = $('<input />').attr({type: 'hidden'}).appendTo(modal_body);

        let uploader = $('<div />').appendTo(modal_body);
        let preview = $('<div />').appendTo(modal_body);

        uploader.osc_uploader({
            max_files: 1,
            process_url: btn_bulk_block.attr('data-bulk-block-url'),
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
                import_btn.removeAttr('disabled');
                file_input.val(response.data.file);
            }

            if (response.result === 'ERROR') {
                file_input.val('');
            }
        }).bind('uploader_upload_error uploader_cancel', function (e, file_id, error_code, error_message) {
            uploader.show();
            file_input.val('');
            import_btn.attr('disabled', 'disabled');
        });

        initFileUploadHandler(uploader, preview);

        let sample_file = $.base_url + '/resource/template/core/sample_xlsx/bulk_block_sample.xlsx';
        $('<div />').addClass('mt10').html(`Download a <a href="${ sample_file }" style="text-decoration: underline">sample XLSX template</a> to see an example of the format require`).appendTo(modal_body);

        let action_bar = $('<div />').addClass('action-bar').appendTo(modal_form);

        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('feedBulkBlock');
        }).appendTo(action_bar);

        import_btn = $('<button />').addClass('btn btn-primary ml10').attr('disabled', 'disabled').html('Bulk Block').click(function () {
            if (import_btn.attr('disabled') === 'disabled') {
                return;
            }

            let file = file_input.val();
            if (!file) {
                alert('Please upload file to import');
                return;
            }

            this.setAttribute('disabled', 'disabled');
            this.setAttribute('data-state', 'submitting');

            import_btn.prepend($($.renderIcon('preloader')).addClass('mr15'));

            $.ajax({
                url: btn_bulk_block.attr('data-process-bulk-block-url'),
                data: {
                    file
                },
                success: function (response) {
                    import_btn.removeAttr('disabled');
                    import_btn.removeAttr('data-state');
                    import_btn.find('svg').remove();

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    alert(response.data.message);
                    window.location.reload(true);
                }
            });
        }).appendTo(action_bar);

        $.wrapContent(modal_form, {key: 'feedBulkBlock'});
        modal_form.moveToCenter().css('top', '100px');
    })
}

window.initBulkBlockProgressDeleteBtn = function () {
    let btn_delete_progress = $(this)

    btn_delete_progress.click(function () {
        if (this.getAttribute('disabled') === 'disabled') {
            return;
        }

        let queue_ids = [];

        $('input[name="queue_id"]:checked').each(function () {
            queue_ids.push(this.value);
        });

        if (queue_ids.length < 1) {
            return;
        }

        if (this.getAttribute('data-confirm')) {
            if (!window.confirm(this.getAttribute('data-confirm'))) {
                return;
            }
        }

        this.setAttribute('disabled', 'disabled');
        let _this = this;
        $.ajax({
            url: this.getAttribute('data-link'),
            data: {queue_ids},
            success: function (response) {
                _this.removeAttribute('disabled');
                if (response.result !== 'OK') {
                    alert(response.message);
                    return;
                }

                alert(response.data.message);
                window.location.reload();
            }
        });
    })
}

window.initBlockBulkDeleteBtn = function () {
    let btn_delete_progress = $(this)

    const category = btn_delete_progress.attr('data-category')

    btn_delete_progress.click(function () {
        let country_codes = [];

        if (this.getAttribute('disabled') === 'disabled') {
            return;
        }

        $('input[name="country_code"]:checked').each(function () {
            country_codes.push(`"${this.value}"`);
        });

        if (country_codes.length < 1) {
            return;
        }

        if (this.getAttribute('data-confirm')) {
            if (!window.confirm(this.getAttribute('data-confirm'))) {
                return;
            }
        }

        this.setAttribute('disabled', 'disabled');
        let _this = this;
        $.ajax({
            type: 'POST',
            url: this.getAttribute('data-process-url'),
            data: {country_codes, category},
            success: function (response) {
                _this.removeAttribute('disabled');

                if (response.result !== 'OK') {
                    alert(response.message);
                    return;
                }
                window.location.reload(true);
            }
        });
    })
}

window.initBulkLogDeleteBtn = function () {
    let btn_delete_progress = $(this)

    btn_delete_progress.click(function () {
        if (this.getAttribute('disabled') === 'disabled') {
            return;
        }

        let queue_ids = [];

        $('input[name="queue_id"]:checked').each(function () {
            queue_ids.push(this.value);
        });

        if (queue_ids.length < 1) {
            return;
        }

        if (this.getAttribute('data-confirm')) {
            if (!window.confirm(this.getAttribute('data-confirm'))) {
                return;
            }
        }

        this.setAttribute('disabled', 'disabled');
        let _this = this;
        $.ajax({
            url: this.getAttribute('data-link'),
            data: {queue_ids},
            success: function (response) {
                _this.removeAttribute('disabled');

                if (response.result !== 'OK') {
                    alert(response.message);
                    return;
                }

                alert(response.data.message);

                window.location.reload();
            }
        });
    })
}
