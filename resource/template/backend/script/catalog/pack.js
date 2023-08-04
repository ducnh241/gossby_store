(function ($) {
    'use strict';

    window.initSettingPack = function () {
        $(this).on('click', function (e) {
            let product_type_id = $(this).attr('data-product-type');
            let product_pack_id = $(this).attr('data-product-pack');

            let json = fetchJSONTag($(this), 'pack-data');

            let id = json && json.id ? json.id : '';
            let title = json && json.title ? json.title : '';
            let quantity = json && json.quantity ? json.quantity : '';
            let discount_type = json && json.discount_type ? json.discount_type : 0;
            let discount_value = json && json.discount_value ? json.discount_value : 0;
            let marketing_point_rate = json && json.marketing_point_rate ? json.marketing_point_rate : 0;
            let note = json && json.note ? json.note : '';

            $.unwrapContent('catalogProductPackFrm');

            let modal = $('<div />').addClass('osc-modal').width(700);

            let header = $('<header />').appendTo(modal);

            let model_title = $.isEmptyObject(json) ? 'Add New Product Pack' : `#${id} Update ${title}`;
            $('<div />').addClass('title').text(model_title).appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('catalogProductPackFrm');
            }).appendTo(header);

            let modal_body = $('<div />').addClass('post-frm-grid').appendTo(modal);

            let pack_title = $('<div />').addClass('frm-grid').appendTo(modal_body);
            $('<label />').attr('for', 'input-pack_title')
                .html(`<b>${title}</b> (Quantity: ${quantity})`)
                .prependTo($('<div />').appendTo(pack_title));

            if (note) {
                let pack_note = $('<div />').addClass('frm-grid').appendTo(modal_body);
                $('<label />').attr('for', 'input-pack_note')
                    .text(`Note: ${note}`)
                    .prependTo($('<div />').appendTo(pack_note));
            }

            let pack_discount_type = $('<div />').addClass('frm-grid mt20').appendTo(modal_body);
            $('<label />').attr('for', 'input-pack_title')
                .text('Pack Discount Type')
                .prependTo($('<div />').appendTo(pack_discount_type));

            let discount_type_selector = $('<select />')
                .attr('name', 'discount_type')
                .prependTo($('<div />').addClass('styled-select mt10').append($('<ins />')).appendTo(pack_discount_type));
            $('<option />').attr('value', 0).text('Percentage (%)').appendTo(discount_type_selector);
            $('<option />').attr('value', 1).text('Amount ($)').appendTo(discount_type_selector);
            discount_type_selector.val(discount_type);

            let pack_discount_value = $('<div />').addClass('frm-grid mt20').appendTo(modal_body);
            $('<label />').attr('for', 'input-pack_title')
                .text('Pack Discount Value')
                .prependTo($('<div />').appendTo(pack_discount_value));
            let discount_value_input = $('<input />').attr('name', 'discount_value')
                .attr('type', 'text')
                .attr('value', discount_value)
                .addClass('styled-input')
                .prependTo($('<div />').addClass('mt10').appendTo(pack_discount_value));

            let pack_marketing_point_rate = $('<div />').addClass('frm-grid mt20').appendTo(modal_body);
            $('<label />').attr('for', 'input-pack_marketing_point_rate')
                .text('Pack Marketing Point Rate (%)')
                .prependTo($('<div />').appendTo(pack_marketing_point_rate));
            let marketing_point_rate_input = $('<input />').attr('name', 'marketing_point_rate')
                .attr('type', 'number')
                .attr('value', marketing_point_rate)
                .addClass('styled-input')
                .prependTo($('<div />').addClass('mt10').appendTo(pack_marketing_point_rate));

            let pack_shipping = $('<div />').addClass('frm-grid mt20 mb20').appendTo(modal_body);

            let action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Cancel').click(function () {
                $.unwrapContent('catalogProductPackFrm');
            }).appendTo(action_bar);

            $('<button />').addClass('btn btn-primary ml10')
                .html('Save')
                .appendTo(action_bar)
                .click(function () {
                    let post_data = {
                        product_type_id: product_type_id,
                        discount_type: discount_type_selector.val(),
                        discount_value: discount_value_input.val(),
                        marketing_point_rate: marketing_point_rate_input.val()
                    };

                    $.ajax({
                        url: $.base_url + '/catalog/backend_pack/post/id/' + product_pack_id + '/hash/' + OSC_HASH,
                        type: 'POST',
                        dataType: 'json',
                        data: post_data,
                        success: function (response) {
                            if (response.result !== 'OK') {
                                alert(response.message);
                                return;
                            }

                            $.unwrapContent('catalogProductPackFrm');

                            $('.no-result').remove();

                            let item = $(response.data.html);

                            if (parseInt(product_pack_id) === 0) {
                                item.appendTo($('#pack_list_tbl'));
                            } else {
                                $('#pack-' + product_pack_id).replaceWith(item);
                            }

                            let message = parseInt(product_pack_id) === 0 ?
                                'Product pack has been created successfully!' :
                                'Product pack has been updated successfully!';
                            alert(message);
                        }
                    });
                });

            $.wrapContent(modal, {key: 'catalogProductPackFrm'});

            modal.moveToCenter().css('top', '100px');
        });
    };

})(jQuery);
