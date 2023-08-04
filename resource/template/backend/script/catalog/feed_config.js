(function ($) {
    'use strict';
    const customSelect = (data, selected, name) => {
        const selector = $(`<select class='select2-product' multiple='multiple' name='${name}' />`);
        Object.keys(data.data).forEach(key => {
            var option = $('<option />').attr('value', key).text(data.data[key]).appendTo(selector);
            if (selected && selected.includes(key)) {
                option.attr('selected', 'selected');
            }
        })
        return selector;
    }


    const renderBaseFeed = ({uniqueID, frm_name, countries, collections, json_data, container}) => {
        const renderChannels = json_data?.data[uniqueID]?.social_channel ? json_data.data[uniqueID]?.social_channel.map(item =>
            `<div class='social-tag ${item.toLowerCase()}'>${item}<input type='hidden' value='${item}' name='${frm_name}[${uniqueID}][social_channel][]'><span>&#10005;</span></div>`
        ).join('') : null;
        const removeBlock = $(`<span style='position:absolute;font-weight:bold;top:-15px;cursor:pointer;'>&#10005;</span>`);
        const p20 = $("<div class='p20'>");
        const block = $(`<div class="block mb-10" unique-id='${uniqueID}'>`).append(p20);
        $('<div class="frm-grid frm-grid--separate">')
            .append($('<div class="setting-item">')
                .append($('<div class="d-flex justify-content-between mb-10">')
                    .append("<div class='d-flex'><div class='title font-bold'>Country </div><span>&nbsp;(Leave blank if country-specific rendering is not needed)</span></div>")
                    .append($('<div>').append(`<strong class='unique-id'>ID: ${uniqueID}</strong>`).append(removeBlock)))
                .append($('<div class="select_country">')
                    .append(customSelect(countries, json_data?.data[uniqueID]?.country, `${frm_name}[${uniqueID}][country][]`)))).appendTo(p20);
        $('<div class="frm-grid frm-grid--separate">')
            .append($('<div class="setting-item">')
                .append('<div class="title">Collection</div>')
                .append($('<div class="select_collection">'))
                .append(customSelect(collections, json_data?.data[uniqueID]?.collection_id, `${frm_name}[${uniqueID}][collection_id][]`))).appendTo(p20);
        p20.append(`<div class="frm-grid frm-grid--separate">
            <div class="setting-item">
                <input type="checkbox" name='${frm_name}[${uniqueID}][skip_ab_test]' value='${json_data?.data[uniqueID]?.skip_ab_test === '1' ? 1 : 0}' ${json_data?.data[uniqueID]?.skip_ab_test === '1' ? 'checked' : ''}
                    data-insert-cb="initSwitcher">
                <label class="label-inline ml-10">
                    <strong>Disable A/B test pricing for feed display</strong>
                </label>
            </div>
        </div>
        <div class="frm-grid frm-grid--separate catalog--ab_test">
            <div class="setting-item">
                <div class="title">Social Channels</div>
                <div class='d-flex'>
                    ${renderChannels ? renderChannels : ''}
                    <div>
                        ${json_data?.data[uniqueID]?.social_channel?.length !== 3 ? "<div class='catalog--add_social_channel'>+</div>" : "<div style='opacity:0' class='catalog--add_social_channel'>+</div>"}
                    </div>
                </div>
            </div>
        </div>`);
        block.insertBefore($('.catalog--add_new_block'));
        removeBlock.click(function () {
            block.remove();
            if (!container.find('.block').length) container.append(`<input type='hidden' name='${frm_name}' value ='' class='empty_input'>`)
        })
        if (renderChannels) $(block).find('.social-tag span').click(function () {
            $(this).parent().parent().find('.catalog--add_social_channel').attr('style', 'opacity: 1 !important');
            $(this).parent().remove();
        })
        $(block).find('.select2-product').select2().on('select2:select', function (e) {
            var data = e.params.data;
            if (data.id === '*') {
                $(this).val('*');
                $(this).trigger('change');
            }
        });
    }
    window.initCatalogFeed = function () {
        const json_data = fetchJSONTag($(this), 'json_catalog_product_feed');
        const container = $(this);
        const countries = fetchJSONTag($(this), 'json_catalog_countries');
        //countries.data['*'] = 'All country';
        const collections = fetchJSONTag($(this), 'json_catalog_collections');
        collections.data['*'] = 'All collection';
        const frm_name = $(this).data('name');

        if (!Array.isArray(json_data.data)) {
            Object.keys(json_data.data).forEach(uniqueID => renderBaseFeed({
                uniqueID,
                frm_name,
                countries,
                collections,
                json_data,
                container
            }));
        }

        $(this).find('.catalog--add_new_block').click(function (e) {
            e.preventDefault();
            container.find('.empty_input').remove();
            const uniqueID = $.makeUniqid();
            renderBaseFeed({uniqueID, frm_name, countries, collections, data: null, container});
        })

        $('body').click(function (e) {
            if ($(document).find('.catalog--list_social_channel').length && !$(e.target).is('.catalog--add_social_channel')) $('.catalog--list_social_channel').remove();
        })

        $(document).on('click', '.catalog--add_social_channel', function () {
            const that = $(this);
            const list_social_channel = $('<div>').addClass('catalog--list_social_channel');
            let listChannel = ['Facebook', 'Google', 'Pinterest', 'Klaviyo', 'Bing'];
            $(this).parent().parent().find('.social-tag').each(function () {
                const index = listChannel.indexOf($(this).text().slice(0, -1));
                if (index >= 0) listChannel.splice(index, 1);
            })
            if (listChannel.length) {
                listChannel.forEach(item => {
                    $('<div>').addClass('catalog--social_channel').text(item).click(function () {
                        if (listChannel.length == 1) that.css('opacity', 0);
                        const block = $(this).closest('.block');
                        const uniqueId = block.attr('unique-id');
                        const delete_social = $('<span>&#10005;</span>').click(function () {
                            $(this).parent().remove();
                            that.attr('style', 'opacity: 1 !important');
                        })
                        let check = true;
                        block.find('.social-tag').each(function () {
                            if ($(this).hasClass(item.toLowerCase())) check = false;
                        })
                        if (check) $('<div>').addClass(`social-tag ${item.toLowerCase()}`).text($(this).text()).append(`<input type='hidden' name='${frm_name}[${uniqueId}][social_channel][]' value='${$(this).text()}'>`).append(delete_social)
                            .insertBefore($(this).parent().parent());
                        $(this).parent().remove();
                    }).appendTo(list_social_channel);
                })
            }
            if (!$(this).next().hasClass('catalog--list_social_channel')) {
                if (listChannel.length) list_social_channel.insertAfter(this);
            } else {
                $(this).next().remove();
            }
        })
    }

    window.showListUrlFeed = function () {
        $(this).on('click', function (e) {
            e.preventDefault();
            $(this).toggleClass('active');
            if ($(this).hasClass('active')){
                $(this).text('Hide list URL Feed');
            }else{
                $(this).text('Show list URL Feed');
            }
            $('.js-wrap-url-feed').toggleClass('active');
        })
    }

})(jQuery);