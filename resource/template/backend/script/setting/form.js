(function ($) {
    const MAX_COLLECTION_ID_IN_CUSTOM_LABEL = 10
    $('#setting-form').submit(function () {
        const showTippingCheckboxSelector = $("input[type=checkbox][name='config[tip/enable]']");
        const tippingTableSelector = $("div[data-name='config[tip/table]']");
        const percentErrorMessageWrapperSelector = $('.percent-error-message-wrapper');
        const collectionBannerSelector = $("input[type=checkbox][name='config[catalog/collection_banner/enable]']");
        let hasFormError = false;

        if (percentErrorMessageWrapperSelector.length) {
            // remove percent error message
            percentErrorMessageWrapperSelector.remove();
        }

        // if screen has tipping checkbox and tipping checkbox is selected
        if (showTippingCheckboxSelector.length &&
            showTippingCheckboxSelector.length === 1 &&
            showTippingCheckboxSelector.val() === '1') {
            const tippingInputSelector = $("input[type=number][name='config[tip/table][]']");

            tippingInputSelector.map(function () {
                let tippingInputValue = $(this).val();

                if (!tippingInputValue) {
                    hasFormError = true;
                    return;
                }

                tippingInputValue = parseInt(tippingInputValue);

                if (tippingInputValue <= 0) {
                    hasFormError = true;
                }
            });

            if (hasFormError) {
                $('<div class="percent-error-message-wrapper">Percent must be existed and greater than 0</div>')
                    .appendTo(tippingTableSelector);

                return false;
            }
        }

        if (collectionBannerSelector.length &&
            collectionBannerSelector.length === 1 &&
            collectionBannerSelector.val() === '1') {
            if (!$("input[type=hidden][name='config[catalog/collection_banner/pc_image]']").val() || !$("input[type=hidden][name='config[catalog/collection_banner/mobile_image]']").val()) {
                alert('Please upload Collection images.')
                return false
            } 
        }

        let error_custom_label = getErrorCustomLabel(0) + getErrorCustomLabel(2) + getErrorCustomLabel(3) + getErrorCustomLabel(4)
        if (error_custom_label) {
            alert(error_custom_label)
            $('input[name="config[catalog/google_feed/custom_label_0]"]').focus()
            return false
        }
        return true;
    });
    const max_traffic = $('input[name="config[feed/low_impression/max_traffict]"]');
    const min_traffic = $('input[name="config[feed/low_impression/min_traffict]"]');
    if (max_traffic.length && min_traffic.length) {
        max_traffic.attr({
            min: min_traffic.val() || 1
        })
        min_traffic.on('change', function () {
            max_traffic.attr({
                min: min_traffic.val() || 1
            })
        })
    }
    window.initGetListTargetCollection = function () {
        const that = $(this)
        that.on('click', async function () {
            $.unwrapContent('GetTargetCollection');

            that.attr({disabled: true})
            let response = await getCollection()
            that.attr({disabled: false})
            if (response.result !== 'OK') {
                alert(response.message);
                return;
            }
            const collections = response.data


            let modal = $('<div />').addClass('osc-modal render-target-collection').width(700);

            let header = $('<header />').appendTo(modal);

            $('<div />').addClass('title').html('Render list custom label value for targeting in Google Ads').appendTo($('<div />').addClass('main-group').appendTo(header));

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('GetTargetCollection');
            }).appendTo(header);

            let modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

            // Select custom label
            const frm_custom_label = $('<div />').appendTo($('<div />').addClass('frm-grid').appendTo(modal_body));
            frm_custom_label.append($('<label />').text('Custom label'))

            const select = $('<select />').addClass('selection-options').attr({id: 'select-custom-label'}).prependTo($('<div />').addClass('styled-select').append($('<ins />')).appendTo(frm_custom_label));
            $('<option />').attr({'value': 0}).text('Custom label 0').appendTo(select);
            $('<option />').attr({'value': 2}).text('Custom label 2').appendTo(select);
            $('<option />').attr({'value': 3}).text('Custom label 3').appendTo(select);
            $('<option />').attr({'value': 4}).text('Custom label 4').appendTo(select);

            // Select collection id
            const box_collection_ids = $('<div />').addClass('frm-grid').appendTo(modal_body)
            renderCollectionIds(box_collection_ids, 0, collections)

            // List targeted collection
            const frm_targeted_collection = $('<div />').appendTo($('<div />').addClass('frm-grid').appendTo(modal_body));

            // Action bar
            let action_bar = $('<div />').addClass('action-bar').appendTo(modal);

            $('<button />').addClass('btn btn-outline').html('Close').click(function () {
                $.unwrapContent('GetTargetCollection');
            }).appendTo(action_bar);

            // Copy target
            const copy_to_clipboard = $('<button />').addClass('btn btn-primary ml10').html('Copy to Clipboard').attr({disabled: true}).click(function () {

                copyTargetCollectionToClipboard()
            }).appendTo(action_bar);

            select.on('change', function () {
                const custom_label_number = $(this).val()
                renderCollectionIds(box_collection_ids, custom_label_number, collections)
                frm_targeted_collection.empty()
                copy_to_clipboard.attr({disabled: true})
            })

            // Render target
            $('<button />').addClass('btn btn-secondary ml10').html('Render list targeted collection').click(() => {
                renderListTargetCollection(frm_targeted_collection, copy_to_clipboard)
            }).appendTo(action_bar);

            $.wrapContent(modal, {key: 'GetTargetCollection'});

            modal.moveToCenter().css('top', '100px');
        })
    }
    window.initFormatCustomLabel = function () {
        const that = $(this)
        that.on('change', function () {
            const value_array = that.val().split(',').filter(val => (parseInt(val) > 0 && parseInt(val) == val))
            let unique = [...new Set(value_array.map(item => item.trim()))]
            let collection_ids = unique.sort(function (a, b) {
                return parseInt(a) - parseInt(b)
            })
            that.val(collection_ids.join(','))
        })
    }

    function getCollection() {
        const collection_custom_label_0 = getInputCollectionIds(0)
        const collection_custom_label_2 = getInputCollectionIds(2)
        const collection_custom_label_3 = getInputCollectionIds(3)
        const collection_custom_label_4 = getInputCollectionIds(4)
        const all_collection_custom_label = collection_custom_label_0.concat(collection_custom_label_2, collection_custom_label_3, collection_custom_label_4)

        return $.ajax({
            type: 'POST',
            url: '/catalog/backend_collection/getListCollectionByIds/hash/'+ OSC_HASH,
            data: {collection_ids: all_collection_custom_label}
        })
    }

    function renderToBase(max_length) {
        let max = Math.pow(2, max_length);

        let range = [...Array(max).keys()];

        let result = []

        range.forEach((number, index) => {

            if (number > 0) {
                number = number.toString(2)
                while (number.length < max_length) {
                    number = '0' + number
                }
                result.push(number)
            }
        })

        return result;
    }

    function renderCollectionIds(box_collection_ids, custom_label, collections) {
        box_collection_ids.find('.collection-ids').remove()
        let collection_ids = getInputCollectionIds(custom_label)

        if (collection_ids.length < 1 || (collection_ids.length == 1 && !collection_ids[0])) {
            return
        }

        const frm_collection_ids = $('<div />').appendTo($('<div />').addClass('collection-ids').appendTo(box_collection_ids));
        frm_collection_ids.append($('<label />').text('Collection Ids'))

        collection_ids.forEach((id) => {
            $('<div />').addClass('styled-checkbox mt10 d-flex list-collection-ids')
              .append($('<div />').append($('<input />').addClass('styled-input cb-collection-id').attr({type: 'checkbox', name: 'test', value: id})).append($('<ins />').append($.renderIcon('check-solid'))))
              .append($('<label />').addClass('ml10').text(`${id} - ${collections[id] || 'Null'}`))
              .appendTo(frm_collection_ids)
        })

        return collection_ids
    }

    function getInputCollectionIds(custom_label) {
        let collection_ids = $(`input[name='config[catalog/google_feed/custom_label_${custom_label}]']`).val()
        collection_ids = collection_ids.split(',')

        return collection_ids
    }

    function renderListTargetCollection(frm_targeted_collection, copy_to_clipboard) {
        frm_targeted_collection.empty()
        frm_targeted_collection.find('.target').remove()

        let collection_id_selected = []
        let selected = $('.cb-collection-id:checked')
        selected.each(function () {
            collection_id_selected.push($(this).val().trim())
        })

        let all_collection_ids = $('.cb-collection-id')

        if (collection_id_selected.length) {
            copy_to_clipboard.attr({disabled: false})
            frm_targeted_collection.append($('<label />').text('List targeted collection'))

            let bit_render = renderToBase(all_collection_ids.length)
            let result = [];

            const el_target = $('<div />').addClass('target-collection').appendTo(frm_targeted_collection)

            bit_render.forEach((item) => {
                let targets = []
                let is_target_label_ads = false
                for (let i = 0; i < item.length; i++) {
                    if (item.charAt(i) == 1) { // 1 <=> collection is visible in string target render
                        const collection_id = all_collection_ids[i].value.trim()
                        targets.push(collection_id)
                        if (collection_id_selected.includes(collection_id)) {
                            is_target_label_ads = true
                        }
                    }
                }
                if (is_target_label_ads) {
                    result.push(targets)
                }
            })

            result = result.sort(function (a , b) {
                return a.length - b.length
            })

            result.forEach((item) => {
                $('<div />').html(item.join(',')).appendTo(el_target)
            })

        } else {
            alert('Please! Select Collection Ids')
            copy_to_clipboard.attr({disabled: true})
        }

    }

    function copyTargetCollectionToClipboard() {

        let target_collections = []
        $('.target-collection div').each(function () {
            target_collections.push($(this).text())
        })

        const tempElement = $('<textarea />')
        $('body').append(tempElement);
        tempElement.val(target_collections.join("\r\n"));
        tempElement.select();
        document.execCommand('Copy');
        tempElement.remove();

        alert('Copied to clipboard')
    }

    function getErrorCustomLabel(custom_label) {
        if (getInputCollectionIds(custom_label).length > MAX_COLLECTION_ID_IN_CUSTOM_LABEL) {
            $(`input[name='config[catalog/google_feed/custom_label_${custom_label}]']`).parent().parent().addClass('error')
            return `Total Collection ID in Custom Label ${custom_label} need less than or equal ${MAX_COLLECTION_ID_IN_CUSTOM_LABEL}` + "\n"
        } else {
            $(`input[name='config[catalog/google_feed/custom_label_${custom_label}]']`).parent().parent().removeClass('error')
        }

        return ''
    }
})(jQuery);
