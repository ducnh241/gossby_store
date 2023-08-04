(function ($) {
    'use strict';
   let page = 1;
   let page_size = 10;

    window.initUploadPrintTemplateBeta = function () {
        let btn = $(this);

        $(this).click(async function () {
            $.unwrapContent('PrintTemplateBetaUpload');
            let data_id = btn.attr('data-id');
            let data_upload_url = btn.attr('data-upload-url');
            let data_url_post = btn.attr('data-url-post');
            let data_url_get = btn.attr('data-url-get');
            var modal_form = $('<form />').attr('id', 'edit-product').addClass('osc-modal').width(750);
            var header = $('<header />').appendTo(modal_form);

            if(data_id == 0) {$('#print_search').val("");}

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('PrintTemplateBetaUpload');
            }).appendTo(header);

            let data_info = await _getInfoEdit(data_id, data_url_get);

            let upload_modal_title = (data_id == 0) ? 'Upload Print Template Beta' : ('Edit Print Template Beta #' + data_id)

            $('<ul />').addClass('message-error').css('margin', '0px 19px 0px 19px').appendTo(modal_form);
            $('<div />').addClass('title').html(upload_modal_title).appendTo($('<div />').addClass('main-group').appendTo(header));
            var modal_body = $('<div />').css('padding-top', 0).addClass('body post-frm').appendTo(modal_form);
            let input_id_item = $('<input />').attr({'name':'print_id','id': 'input-id-item', 'value': data_id, 'type': 'hidden'});
            $('<div />').addClass('frm-grid').append(input_id_item).appendTo(modal_body);


            const date_info_length = Object.keys(data_info).length;

            let print_label = $('<label />').attr({
                'for': 'input-title',
                'class': 'required'
            }).text('Title').appendTo(modal_body);
            let print_title = $('<input />').addClass('styled-input').attr({
                'name': 'title',
                'id': 'input-title',
                'value': data_info.title,
                'required': true
            });
            $('<div />').addClass('frm-grid').append(print_label).append(print_title).appendTo(modal_body);

            let des_title = $('<div />').addClass('frm-input-title').text('Description').appendTo(modal_body);
            var desc_container = $('<div />').addClass('frm-heading');
            let desc = $('<textarea \>').attr({
                'name': 'description',
                'data-insert-cb': 'initEditor',
                'id': 'input-description'
            }).val(data_info.description);

            $('<div />').addClass('frm-heading__main').append(desc_container.append(des_title)).appendTo(modal_body);
            $('<div />').addClass('frm-grid').append(desc).appendTo(modal_body);

            let label_print_template_beta = $('<div />').append('<label for="input-print-template" class="required">Print Template Beta</label>').css('margin-top',20).appendTo(modal_body);

            let print_template_beta_grid = $('<div />').attr({
                'data-insert-cb': 'initPostFrintTemplateImgImageUploader',
                'data-upload-url': data_upload_url,
                'data-input': 'print_template',
                'data-image': date_info_length > 0 ? data_info.config.print_file.print_file_link : "",
                'data-value': date_info_length > 0 ? data_info.config.print_file.print_file_url : "",
                'data-img-width': date_info_length > 0 ? data_info.config.print_file.dimension.width : "",
                'data-img-height': date_info_length > 0 ? data_info.config.print_file.dimension.height : "",
                'print_url_thumb': date_info_length > 0 ? data_info.config.print_file.print_file_url_thumb : "",
                'data-id': data_id
            });
            label_print_template_beta.append(print_template_beta_grid);
            $('<div />').addClass('frm-grid').addClass('product-post-frm').append(print_template_beta_grid).appendTo(modal_body);


            let dpi_title = $('<label />').attr({
                'for': 'input-dpi',
            }).text('DPI').appendTo(modal_body);
            let dpi = $('<input />').addClass('styled-input').attr({
                'name': 'dpi',
                'type' : 'number',
                'id': 'input-dpi',
                'value': date_info_length > 0 ? data_info.config.print_file.dpi : ""
            });

            $('<div />').addClass('frm-grid').append(dpi_title).append(dpi).appendTo(modal_body);

            let rotate_title = $('<label />').attr({
                'for': 'input-dpi',
            }).text('Rotate').appendTo(modal_body);
            let rotate = $('<input />').addClass('styled-input').attr({
                'name': 'rotate',
                'type' : 'number',
                'id': 'input-rotate',
                'value': date_info_length > 0 ? data_info.config.print_file.rotate : 0
            });

            $('<div />').addClass('frm-grid').append(rotate_title).append(rotate).appendTo(modal_body);

            var action_bar = $('<div />').addClass('action-bar').appendTo(modal_form);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('PrintTemplateBetaUpload');
            }).appendTo(action_bar);

            let button_submit = $('<button />').addClass('btn btn-primary ml10').attr('type', 'submit').html('Save').appendTo(action_bar)

            button_submit.click(async function () {
                await _postEdit(data_id, modal_form, data_url_post, button_submit);
            });

            $.wrapContent(modal_form, {key: 'PrintTemplateBetaUpload'});

            modal_form.moveToCenter().css('top', '100px');
        });
    }

    async function _getInfoEdit(id, url_get) {
        let result = [];

        await $.ajax({
            url: url_get,
            data: $.param({'id': id})
        }).success(function (data) {
            if (data.result == 'OK') {
                result = data.data;
            } else {
                console.error(data, 'error');
            }
        }).error(function (error) {
            console.error(data, 'error2');
            console.error(error);
        });

        return result;
    }

    function _postEdit(data_id, modal_form, url_post, button_submit) {
        let i = 0;
        let form_data = [];

        modal_form.submit(function (event) {
            event.preventDefault();
            button_submit.attr('disabled', 'disabled').prepend($($.renderIcon('preloader')).addClass('mr15'));
            if (i >= 1) return;
            form_data = modal_form.serialize();
            $.ajax({
                url: url_post,
                data: form_data,
                type: "POST",
            }).success(function (data) {
                if (data.result == 'OK') {
                    if (data_id == 0) {
                        page = 1;
                    }
                    initRenderPrintList();
                    $.unwrapContent('PrintTemplateBetaUpload');
                } else {
                    $('.message-error').html('<li>' + data.message + '</li>');
                    $('.osc-wrap').animate({
                        scrollTop: 0
                    }, 300);
                    button_submit.removeAttr('disabled').find('svg').remove();
                }
            }).error(function (error) {
                console.error(error);
            });
            i++;
        });
    }

    // function _deletePrintTemplateBeta(data_id, data_delete_url) {
    //     if (!confirm('Are you sure you want to delete print template beta #data_id ?')) {
    //             return;
    //     }
    //
    //     $.ajax({
    //         url: data_delete_url,
    //         data: {'print_id' : data_id},
    //         type: "POST",
    //     }).success(function (data) {
    //         if (data.result == 'OK') {
    //             initRenderPrintList();
    //             $.unwrapContent('PrintTemplateBetaUpload');
    //         } else {
    //             alert( data.message);
    //         }
    //     }).error(function (error) {
    //         console.error(error);
    //     });
    // }

    window.initRenderPrintList = function () {
        let image_list_data = null;

        let data_url = $('#data-url')
        let data_upload_url = data_url.attr('data-upload-url');
        let data_url_search = data_url.attr('data-url-search');
        let data_id = 0;
        let data_url_post = data_url.attr('data-url-post');
        let data_url_get = data_url.attr('data-url-get');
        let data_permission_edit = data_url.attr('data-permission-edit');
        let image_list = $('.image-list-scene');

        $('<div />').addClass('print-list-loading').appendTo(image_list).html($($.renderIcon('preloader')));

        _renderPrintList();

        function _renderPrintList () {
            page = parseInt(page);

            let form_data = $('.form-search form').serializeArray();
            form_data.push({name: "page", value: page});
            form_data.push({name: "page_size", value: page_size});
            form_data.push({name: "hash", value: OSC_HASH});

            $.ajax({
                type: 'post',
                url: data_url_search,
                data: $.param(form_data),
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    image_list_data = response.data;

                    _renderImageList();
                }
            });
        }

        function _renderImageList() {
            image_list.children().remove();

            if(image_list_data.items.length < 1) {
                console.error('Can not get list print template beta');
                return;
            }

            let print_list = $('<div />').addClass('print-list').addClass('review-images').appendTo(image_list);
            image_list_data.items.map(item => {
                data_id = item.id;

                let image_item = $('<div />').addClass('image-item').appendTo(print_list);
                let thumb = $('<div />').addClass('thumb').appendTo(image_item);
                let print_title = $('<div />').addClass('title').appendTo(image_item);
                let control_bars = $('<div />').addClass('controls').appendTo(thumb);

                thumb.css('background-image', 'url(' + item.config.print_file.print_file_url_thumb + ')');
                print_title.append(item.title);
                image_item.attr('title',item.title);

                if(data_permission_edit) {
                    $($.renderIcon('pencil')).attr({
                        "data-insert-cb" : initUploadPrintTemplateBeta,
                        "data-url-post" : data_url_post,
                        "data-url-get" : data_url_get,
                        "data-id" : data_id,
                        "data-upload-url" : data_upload_url
                    }).appendTo(control_bars);
                }

                // $($.renderIcon('trash-alt-regular')).attr({
                //     "data-id" : data_id
                // }).appendTo(control_bars).click(function () {
                //     let data_id = $(this).attr('data-id');
                //     let data_url = $('#data-url')
                //     let data_delete_url = data_url.attr('data-delete-url');
                //     _deletePrintTemplateBeta(data_id, data_delete_url);
                // });
            })

            var pagination = buildPager(image_list_data.current_page, image_list_data.total, image_list_data.page_size, null);

            if (pagination) {
                $('<div />').addClass('pagination-bar p10').append(pagination).appendTo(image_list);

                pagination.find('[data-page]:not(.current)').click(function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    page = this.getAttribute('data-page');
                    initRenderPrintList();
                });
            }
        }
    }

    window.initPostFrintTemplateImgImageUploader = function () {
        let container = $(this);
        let max_filesize = 30*1024*1024;

        container.addClass('print-template-image-uploader');

        let preview = $('<div />').addClass('preview').appendTo(container);
        let image_url = container.attr('data-image');
        let input_name = container.attr('data-input');
        let data_id = container.attr('data-id');
        if (image_url !== '') {
            preview.find('svg').hide();

            preview.css('background-image', 'url(' + image_url + ')');

            $('<input />').attr({
                type: 'hidden',
                name: input_name,
                value: container.attr('data-value')
            }).appendTo(preview);
        }

        $('<input />').attr({
            type: 'hidden',
            name: 'print_url_thumb',
            value: container.attr('print_url_thumb')
        }).appendTo(preview);

         $('<input />').attr({
            type: 'hidden',
            name: 'print_dimension[width]',
            value: container.attr('data-img-width')
        }).appendTo(preview);

         $('<input />').attr({
            type: 'hidden',
            name: 'print_dimension[height]',
            value: container.attr('data-img-height')
        }).appendTo(preview);

        var uploader_container = $('<div />').addClass('mt10 btn btn-primary p0').appendTo(container);

        var __initRemoveBtn = function () {
            uploader_container.find('.image-uploader').hide();
            uploader_container.find('.remove-btn').remove();

            if(data_id != 0) {
                uploader_container.css('display', 'none')
                return;
            }

            let button_remove = $('<div />').addClass('btn btn-danger remove-btn').appendTo(uploader_container).text('Remove image').click(function () {
                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                image_url = '';
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
                process_url: container.attr('data-upload-url'),
                btn_content: 'Upload image',
                dragdrop_content: 'Drop here to upload',
                max_filesize: max_filesize,
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
                    preview.css('background-image', image_url !== '' ? ('url(' + image_url + ')') : 'initial');
                    alert(response.message);

                    __initUploader();

                    return;
                }

                preview.css('background-image', 'url(' + response.data.url + ')');

                image_url = response.data.url;
                preview.find('input').remove();

                $('<input />').attr({type: 'hidden', name: input_name, value: response.data.file}).appendTo(preview);

                $('<input />').attr({
                    type: 'hidden',
                    name: 'print_url_thumb',
                    value: response.data.file_thumb
                }).appendTo(preview);

               $('<input />').attr({
                    type: 'hidden',
                    name: 'print_dimension[width]',
                    value: response.data.width
                }).appendTo(preview);

               $('<input />').attr({
                    type: 'hidden',
                    name: 'print_dimension[height]',
                    value: response.data.height
                }).appendTo(preview);

            }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                switch (file_id) {
                    case 'sizeError':
                        alert('File size limit exceeded. The maximum file size is 10 MB.');
                        break;
                    case 'typeError':
                        alert('Sorry! The image file format you uploaded is not supported.');
                        break
                    default:
                        alert('Upload error, please try again');
                        break;
                }

                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                __initUploader();

                preview.find('svg').removeAttr('style');
                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();
                preview.css('background-image', image_url !== '' ? ('url(' + image_url + ')') : 'initial');

                alert('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại');
            });
        };

        if (image_url !== '') {
            __initRemoveBtn();
        } else {
            __initUploader();
        }
    };

    $('form').on("submit", function(e) { // on requires jquery 1.7+
        e.preventDefault();
        initRenderPrintList();
        // initRenderPrintList();
    });

    window.searchPrintTemplateBeta = function () {
        let _form = $(this);
        _form.on("submit", function(e) { // on requires jquery 1.7+
            e.preventDefault();
            initRenderPrintList();
        });
    }
})(jQuery);