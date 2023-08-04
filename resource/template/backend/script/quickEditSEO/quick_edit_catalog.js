window.initQuickEditInfo = function () {
    let btn = $(this);

    $(this).click(async function () {
        $.unwrapContent('catalogProductEdit');
        let type = btn.attr('data-type') ? btn.attr('data-type') : 'product';
        let data_id = btn.attr('data-id');
        let data_url_tags = btn.attr('data-url-tags');
        let product_topic = btn.attr('data-url-topic');
        let data_upload_url = btn.attr('data-upload-url');
        let data_meta_image_value = btn.attr('data-meta-image-value');
        let data_url_post = btn.attr('data-url-post');
        let data_url_get = btn.attr('data-url-get');
        var modal_form = $('<form />').attr('id', 'edit-product').addClass('osc-modal').width(750);
        var header = $('<header />').appendTo(modal_form);

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('catalogProductEdit');
        }).appendTo(header);

        let data_info_seo = await _getInfoEdit(data_id, data_url_get);

        $('<ul />').addClass('message-error').css('margin', '0px 19px 0px 19px').appendTo(modal_form);
        $('<div />').addClass('title').html('Quick Edit #' + data_id).appendTo($('<div />').addClass('main-group').appendTo(header));
        var modal_body = $('<div />').css('padding-top', 0).addClass('body post-frm').appendTo(modal_form);
        let input_id_item = $('<input />').attr({'id': 'input-id-item', 'value': data_id, 'type': 'hidden'});
        $('<div />').addClass('frm-grid').append(input_id_item).appendTo(modal_body);
        let input_seo_slug = $('<input />').attr({
            'id': 'input-id-seo_slug',
            'value': data_info_seo.slug,
            'type': 'hidden'
        });
        $('<div />').addClass('frm-grid').append(input_seo_slug).appendTo(modal_body);
        $('<input />').attr({'type': 'hidden', 'name': 'id', 'value': data_id}).appendTo(modal_body);

        let quotes_title = $('<label />').attr({
            'for': 'input-title',
            'class': 'required'
        }).text(type.toLowerCase() == 'product' ? 'Quote' : 'Title').appendTo(modal_body);
        let quotes = $('<input />').addClass('styled-input').attr({
            'name': 'title',
            'id': 'input-title',
            'value': data_info_seo.title,
            'required': true
        });
        $('<div />').addClass('frm-grid').append(quotes_title).append(quotes).appendTo(modal_body);

        if (type == "collection") {
            let short_title_label = $('<label />').attr('for', 'input-title').text('Custom title').appendTo(modal_body);
            let short_title_input = $('<input />').addClass('styled-input').attr({
                'name': 'custom_title',
                'id': 'input-short-title'
            }).val(data_info_seo.custom_title);
            $('<div />').addClass('frm-grid').append(short_title_label).append(short_title_input).appendTo(modal_body);

            let index_label = $('<label />').attr('for', 'allow_index').text('Allow index').appendTo(modal_body);
            let index_switcher = $('<input />').attr({
                'name': 'allow_index',
                'id': 'allow_index',
                'type': 'checkbox',
                'data-insert-cb': 'initSwitcher'
            });
            if (data_info_seo.allow_index == 1) {
                index_switcher.attr('checked', 'checked');
            }

            let index_div = $('<div />').addClass('md5 collection-post-form').append(index_label).append(index_switcher);
            $('<div />').addClass('frm-grid').append(index_div).appendTo(modal_body);
        }

        let div_topic = $('<div />')
            .addClass('styled-autocomplete-popover')
            .attr({'data-insert-cb': 'initAutoCompletePopover', 'data-autocompletepopover-config': product_topic});
        let topic = $('<input />')
            .attr({
                'type': 'text',
                'id': 'input-vendor',
                'name': 'topic',
                'value': data_info_seo.topic,
                'autocomplete': 'off',
                'required': true
            });
        let autocomplete_ins = $('<ins />').attr('data-autocomplete-popover-toggler', '1');
        div_topic.append(topic).append(autocomplete_ins);
        let topic_grid = $('<div />').append(div_topic);
        if (product_topic) {
            let label_topic = $('<label />').attr({
                'for': 'input-title',
                'class': 'required'
            }).text('Topic').appendTo(modal_body);
            $('<div />').addClass('frm-grid').addClass('product-post-frm').append($('<div />').append(label_topic).append(topic_grid)).appendTo(modal_body);
        }

        let des_title = $('<div />').addClass('frm-input-title').text('Description').appendTo(modal_body);
        var desc_container = $('<div />').addClass('frm-heading');
        let desc = $('<textarea \>').attr({
            'name': 'description',
            'data-insert-cb': 'initEditor',
            'id': 'input-description'
        }).val(data_info_seo.description);
        $('<div />').addClass('frm-heading__main').append(desc_container.append(des_title)).appendTo(modal_body);
        $('<div />').addClass('frm-grid').append(desc).appendTo(modal_body);

        if (data_url_tags) {
            let label_tags = $('<label />').attr('for', 'input-title').text('SEO Tags').appendTo(modal_body);
            let seo_tags = $('<input />')
                .addClass("styled-input")
                .attr('id', 'input-tags')
                .attr('type', 'text')
                .attr('data-insert-cb', 'initAutoCompletePopover')
                .attr('data-autocompletepopover-config', data_url_tags);
            let seo_tag_grid = $('<div />').attr('data-insert-cb', 'productPostFrm__initSEOTags')
                .attr('data-tags', JSON.stringify(Array.isArray(data_info_seo.seo_tags) ? data_info_seo.seo_tags : []))
                .append(label_tags)
                .append(seo_tags)
                .append('<div class="product-tags"></div>');
            $('<div />').addClass('frm-grid').addClass('product-post-frm').append(seo_tag_grid).appendTo(modal_body);
        }

        let label_meta_title = $('<div />').css("display", "flex").append('<label for="input-seo-title">Meta Title</label> <span id="auto_fill_auto_slug" onclick="generateSlug()"> Auto Fill</span>').appendTo(modal_body);
        let input_meta_title = $('<input />')
            .addClass("styled-input")
            .attr({
                'id': 'input-seo-title',
                'name': 'seo_title',
                'onkeyup': 'title_generate_slug()',
                'type': 'text',
                'value': data_info_seo.meta_tags.title
            });
        let warnning_character_title = $('<label />').attr('id', 'warnning_character_title').css('color', '#FFC107');
        let meta_title_grid = $('<div />')
            .append(label_meta_title)
            .append(input_meta_title)
            .append(warnning_character_title);
        $('<div />').addClass('frm-grid').addClass('product-post-frm').append(meta_title_grid).appendTo(modal_body);

        let label_meta_slug = $('<div />').css("display", "flex").append('<label for="input-seo-title">Meta Slug</label>').appendTo(modal_body);
        let input_meta_slug = $('<input />')
            .addClass("styled-input")
            .attr({
                'id': 'input-seo-slug',
                'name': 'seo_slug',
                'autocomplete': 'off',
                'type': 'text',
                'value': data_info_seo.slug
            });
        let meta_slug_grid = $('<div />')
            .append(label_meta_slug)
            .append(input_meta_slug)
        $('<div />').addClass('frm-grid').addClass('product-post-frm').append(meta_slug_grid).appendTo(modal_body);

        let label_meta_des = $('<div />').append('<label for="input-seo-title">Meta Description</label>').appendTo(modal_body);
        let warning_character_description = $('<label />').attr('id', 'warning_character_description').css('color', '#FFC107');
        let input_meta_des = $('<textarea />')
            .addClass("styled-textarea")
            .attr({
                'id': 'input-seo-description',
                'name': 'seo_description',
                'onkeyup': 'description_length()',
                'rows': '5',
                'value': data_info_seo.meta_tags.description
            });
        let meta_des_grid = $('<div />')
            .append(label_meta_des)
            .append(input_meta_des)
            .append(warning_character_description)
        $('<div />').addClass('frm-grid').addClass('product-post-frm').append(meta_des_grid).appendTo(modal_body);

        let label_meta_image = $('<div />').append('<label for="input-seo-title">Meta Image</label>').appendTo(modal_body);
        let meta_image_grid = $('<div />').attr({
            'data-insert-cb': 'initPostFrmMetaImageUploader',
            'data-upload-url': data_upload_url,
            'data-input': 'seo_image',
            'data-image': data_info_seo.meta_tags.meta_image_url,
            'data-value': data_meta_image_value
        });
        label_meta_image.append(meta_image_grid);
        $('<div />').addClass('frm-grid').addClass('product-post-frm').append(meta_image_grid).appendTo(modal_body);

        if (type == "product") {
            let label_seo_status = $('<div />').append("<label class=\"input-seo-status\">SEO status</strong>").appendTo(modal_body);
            let label_seo_grid = $('<input />').attr({
                'type': 'checkbox',
                'name': 'seo_status',
                'data-insert-cb': 'initSwitcher',
            });

            if (data_info_seo.seo_status) {
                label_seo_grid.attr('checked', 'checked');
            }

            $('<div />').css({'margin-top': 10}).addClass('product-post-frm').append(label_seo_status).append(label_seo_grid).append('<label class="label-inline ml10">Optimizing product SEO</label>').appendTo(modal_body);
        }

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal_form);

        $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
            $.unwrapContent('catalogProductEdit');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').attr('type', 'submit').html('Save').appendTo(action_bar).on('click', async function (e) {
            await _postEdit(data_id, modal_form, data_url_post, $(this));
        });

        $.wrapContent(modal_form, {key: 'catalogProductEdit'});

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
            console.error(data.data.message);
        }
    }).error(function (error) {
        console.error(error);
    });

    return result;
}

function _postEdit(data_id, modal_form, url_post, button_submit) {
    let i = 0;
    let form_data = [];

    modal_form.submit(function (event) {
        event.preventDefault();
        if (i >= 1) return;
        form_data = modal_form.serialize();
        button_submit.attr('disabled', 'disabled').prepend($($.renderIcon('preloader')).addClass('mr15'));
        $.ajax({
            url: url_post,
            data: form_data,
            type: "POST",
        }).success(function (data) {
            if (data.result == 'OK') {
                let seo_status_color = data.data.seo_status == 1 ? 'optimazed' : 'unoptimazed';
                button_submit.removeAttr('disabled');
                button_submit.find('svg').remove();
                $.unwrapContent('catalogProductEdit');
                let title_reload = data.data.topic ? data.data.topic + ' - ' + data.data.title : data.data.title;
                $('#title' + '-' + data_id).html(title_reload);
                $('#seo_status' + '-' + data_id).html('<span class="badge seo_status ' + seo_status_color + '"></span>');
                if ($("input[name='filter[status_seo]']").val() && $("input[name='filter[status_seo]']").val() != data.data.seo_status) {
                    $('#title' + '-' + data_id).parent().remove();
                }
                if (data.data.allow_index == 1) {
                    $("#allow_index").attr('checked','checked');
                    $("#index-badge-" + data.data.collection_id).removeClass('badge-danger').addClass("badge-success").text("Yes");
                } else {
                    $("#allow_index").removeAttr('checked');
                    $("#index-badge-" + data.data.collection_id).removeClass('badge-success').addClass("badge-danger").text("No");
                }
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

function _productPostFrm_renderSEOTag(tag, tag_key) {
    return $('<div />').addClass('product-tag').attr('title', tag).text(tag).append($('<input />').attr({
        type: 'hidden',
        name: 'tags[' + tag + '][collection_id]',
        value: tag_key
    })).append($('<input />').attr({type: 'hidden', name: 'tags[' + tag + '][collection_title]', value: tag}))
        .append($('<input />').attr({
            type: 'hidden',
            name: 'tags[' + tag + '][collection_slug]',
            value: _convertSlug(tag)
        }))
        .append($('<ins />').click(function () {
            $(this).closest('.product-tag').remove();
        }));
}

function _convertSlug(input) {
    return input ? input.toLowerCase().replace(/ /g, '-').replace(/[-]+/g, '-').replace(/[^\w-]+/g, '') : null;
}

window.productPostFrm__addSEOTag = function (tag, input, tag_key) {
    input.val('');
    tag = tag.trim();
    let tags = {};
    let container = input.closest('.frm-grid').find('.product-tags');

    container.find('input[type="hidden"]').each(function () {
        tags[this.value.toLowerCase()] = $(this).closest('.product-tag');
    });

    if (typeof tags[tag.toLowerCase()] !== 'undefined') {
        container.prepend(tags[tag.toLowerCase()]);
    } else {
        container.append(_productPostFrm_renderSEOTag(tag, tag_key));
    }
}

window.productPostFrm__initSEOTags = function () {
    $(this).find('input[type="text"]').keydown(function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
        }
    });

    let tags = JSON.parse(this.getAttribute('data-tags'));

    if (tags === null) {
        return;
    }

    var container = $(this).find('.product-tags');

    $.each(tags, function (k, tag) {
        if(!tag.collection_title && tag.collection_id == 0){
            return false;
        }

        container.append(_productPostFrm_renderSEOTag(tag.collection_title,tag.collection_id));
    });
};