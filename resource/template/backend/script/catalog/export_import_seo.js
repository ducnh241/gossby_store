window.initCatalogProductExportDataSEOBtn = function () {
    var btn = $(this);

    $(this).click(function () {
        $.unwrapContent('catalogProductExportFrm');
        let type = btn.attr('data-export-type') ? btn.attr('data-export-type') : 'product';
        let type_selected_id = btn.attr('data-type-selected-id') ? btn.attr('data-type-selected-id') : 'product_id';
        let data_search = btn.attr('data-search') ? btn.attr('data-search') : 0;

        var modal = $('<div />').addClass('osc-modal').width(350);

        var header = $('<header />').appendTo(modal);

        let text = ( type == 'product' ? 'Products' : 'Collections');

        $('<div />').addClass('title').html('Export '+ text +' to XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('catalogProductExportFrm');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        $('<div />').text('Export').appendTo(modal_body);

        var row = $('<div />').addClass('mt10').appendTo(modal_body);

        $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'all', id: 'export_condition__all', checked: 'checked'})).append($('<ins />')).appendTo(row);
        $('<label />').attr('for', 'export_condition__all').addClass('label-inline').text('All products').appendTo(row);

        row = $('<div />').addClass('mt5').appendTo(modal_body);

        if (data_search) {
            $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'search', id: 'export_condition__search'})).append($('<ins />')).appendTo(row);
            $('<label />').attr('for', 'export_condition__search').addClass('label-inline').text('Current search').appendTo(row);
        }


        row = $('<div />').addClass('mt5').appendTo(modal_body);

        $('<div />').addClass('styled-radio mr10').append($('<input />').attr({type: 'radio', name: 'export_condition', value: 'selected', id: 'export_condition__selected'})).append($('<ins />')).appendTo(row);
        $('<label />').attr('for', 'export_condition__selected').addClass('label-inline').text('Selected products').appendTo(row);

        $('<div />').addClass('mt10').text('Export as Colums').appendTo(modal_body);

        var column_list = $('<div />').addClass('mt10 column-order-export-list product-option-value-list').appendTo(modal_body);

        _renderTemplateSelector('seo_content', [], column_list, type);

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);


        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('catalogProductExportFrm');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html('Export '+ text).click(function () {
            var post_data = {
                export_condition: null
            };

            $('input[name="export_condition"]').each(function () {
                if (this.checked) {
                    post_data.export_condition = this.value;
                    return false;
                }
            });

            if (post_data.export_condition === 'selected') {
                post_data.selected_ids = [];

                $('input[name='+type_selected_id+']:checked').each(function () {
                    post_data.selected_ids.push(this.value);
                });
            }

            post_data.columns = [];

            $('input.checkbox_column').each(function () {
                if (this.checked) {
                    let column_item_key = $(this).attr("key");
                    let column_item_value = $(this).attr("value");

                    post_data.columns.push({[column_item_key]:column_item_value});
                }
            });

            $.ajax({
                url: btn.attr('data-export-url'),
                data: post_data,
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    $.unwrapContent('catalogProductExportFrm');

                    window.location = response.data.url;
                }
            });
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'catalogProductExportFrm'});

        modal.moveToCenter().css('top', '100px');


        if (!$('input[name='+type_selected_id+']:checked')[0]) {
            $('#export_condition__selected').attr('disabled', 'disabled');
        } else {
            $('#export_condition__selected')[0].checked = true;
        }

        if (btn.attr('data-search') !== '1') {
            $('#export_condition__search').attr('disabled', 'disabled');
        }
    });
};

window.initCatalogProductImportDataSEOBtn = function () {
    var btn = $(this);

    $(this).click(function () {
        $.unwrapContent('catalogProductImportFrm');
        let type = btn.attr('data-export-type') ? btn.attr('data-export-type') : 'product';
        let text = ( type == 'product' ? 'Products' : 'Collections');

        var import_btn = null;

        var modal = $('<div />').addClass('osc-modal').width(390);

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html('Import SEO Data Content by XLSX file').appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('catalogProductImportFrm');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);
        var file_input = $('<input />').attr({type: 'hidden'}).appendTo(modal_body);

        var uploader = $('<div />').appendTo(modal_body);
        var preview = $('<div />').appendTo(modal_body);

        uploader.osc_uploader({
            max_files: 1,
            process_url: btn.attr('data-import-url'),
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

        $('<div />').addClass('mt10').html('Download a <a href="#">sample XLSX template</a> to see an example of the format require').appendTo(modal_body);

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('catalogProductImportFrm');
        }).appendTo(action_bar);

        import_btn = $('<button />').addClass('btn btn-primary ml10').attr('disabled', 'disabled').html('Import ' + text).click(function () {
            if (import_btn.attr('disabled') === 'disabled') {
                return;
            }

            var file = file_input.val();

            if (!file) {
                alert('Please upload file to import');
                return;
            }

            this.setAttribute('disabled', 'disabled');
            this.setAttribute('data-state', 'submitting');

            import_btn.prepend($($.renderIcon('preloader')).addClass('mr15'));

            $.ajax({
                url: btn.attr('data-importprocess-url'),
                data: {file: file},
                success: function (response) {
                    import_btn.removeAttr('disabled');
                    import_btn.removeAttr('data-state');

                    import_btn.find('svg').remove();

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    window.location.reload(true);
                }
            });
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'catalogProductImportFrm'});

        modal.moveToCenter().css('top', '100px');
    });
};

function _renderTemplateSelector(template_name, list_key = [], column_list, type= "product") {
    var column = null;

    if (template_name == 'seo_content') {

        if (type == 'product') {
            list_key = [
                ["product_id", "Product ID"],
                ["url", "URL"],
                ["topic", "Topic"],
                ["title", "Title"],
                ["description", "Description"],
                ["meta_title", "Meta Title"],
                ["meta_slug", "Meta Slug"],
                ["meta_description", "Meta Description"],
                ["seo_tag_collection_id", "SEO Tag Collection ID"],
                ["seo_tag_collection_title", "SEO Tag Collection Title"],
                ["seo_status", "SEO Status"]
            ];
        }

        if (type == 'collection') {
            list_key = [
                ["collection_id", "Collection ID"],
                ["url", "URL"],
                ["title", "Title"],
                ["custom_title", "Custom Title"],
                ["description", "Description"],
                ["meta_title", "Meta Title"],
                ["meta_slug", "Meta Slug"],
                ["meta_keywords", "Meta KeyWords"],
                ["meta_description", "Meta Description"]
            ];
        }
    }

    column_list.html('');

    $.each(list_key, function (index, value) {
        column = $('<div />').addClass('mt10 column-value option-value').css({'cursor':'pointer', 'padding':'12px'}).appendTo(column_list);
        $('<div />').addClass('styled-checkbox mr10').append($('<input />').addClass("checkbox_column").attr({type: 'checkbox', name: value[1], value: value[1], id: value[0], key: value[0], checked: "checked"})).append($('<ins />').append($.renderIcon('check-solid'))).appendTo(column);
        $('<label />').attr('for', value[0]).addClass('label-inline').text(value[1]).appendTo(column);
    });
}

