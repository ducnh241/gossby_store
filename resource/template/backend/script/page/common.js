(function ($) {
    'use strict';
    window.pageInitAdditionalData = function () {
        var container = $(this);

        var frm_name = container.attr('data-name');

        var json = fetchJSONTag(container, 'additional_data');

        var box = $('<div />').addClass('additional_data');

        function __addRow(key_json, data = null) {
            box.prependTo(container);
            if (key_json == undefined) {
                key_json = $.makeUniqid()
            }

            let row = $('<div />').addClass('post-frm box-item mb10').appendTo(box);
            $('<label />').text('Title').appendTo(row);

            $('<input />').attr({
                'name': frm_name + '[' + key_json + '][title]',
                'value': data ? data.title : ''
            }).addClass('styled-input mb15').appendTo(row);

            $('<label />').text('Content').appendTo(row);

            $('<textarea />').attr({
                'name': frm_name + '[' + key_json + '][content]',
                'data-insert-cb': 'initEditor',
                'value': data ? data.content : ''
            }).addClass('styled-textarea').appendTo(row);

            let item_image = $('<div />').addClass('additional-image').appendTo(row);
            $('<label />').text('Image').appendTo(item_image);
            $('<div />').attr({
                'data-insert-cb': 'initPostFrmSidebarImageUploader',
                'data-upload-url': $.base_url + '/page/backend/uploadImage/hash/' + OSC_HASH,
                'name': frm_name + '[' + key_json + '][image]',
                'data-input': frm_name + '[' + key_json + '][image]',
                'data-image': data ? $.base_url + '/storage/' + data.image : '',
                'data-value': data ? data.image : '',
            }).addClass('collection-image-uploader-custom').appendTo(item_image);

            $('<div />').text('Delete item').addClass('btn btn-small btn-danger mt10 btn-remove-item').appendTo(row).click(function () {
                row.remove();
                if (box.find('.box-item').length < 1) {
                    box.html('').detach();
                }
            });

            return row;
        }

        if (json.data) {
            $.each(json.data, function (key_json, additional_data) {
                __addRow(key_json, additional_data);
            });
        }

        $('<div />').addClass('btn btn-primary mt10').text('Add new data').appendTo(container).click(function () {
            __addRow();
        });
    };

    window.initPageType = function () {
        $(this).find("input[type='radio']").click(function () {
            let page_type = this.value;
            if (page_type == 'terms_of_service' || page_type == 'about_us') {
                $('#additional_data_box').show();
                if (page_type == 'about_us') {
                    $('.additional-image').show()
                }else{
                    $('.additional-image').hide();
                }
            } else {
                $('#additional_data_box').hide();
                $('.additional-image').hide();
            }
        });
        $(this).find("input[type='radio']:checked").trigger('click');
    }

})(jQuery);
