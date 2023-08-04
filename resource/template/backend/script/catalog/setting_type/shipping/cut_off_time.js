(function ($) {
    'use strict';

    window.changeProductType = function () {
        $(this).on('change', function () {
            const input_product_ids_elem = $(this).closest('.cut-off-time-group').find('textarea[name*="[product_ids][]"]').closest('.frm-grid');
            if ($(this).val().includes('manual')) {
                input_product_ids_elem.show();
            } else {
                input_product_ids_elem.hide();
            }
        });
    }

    window.addCutOffTimeGroup = function () {
        const product_types = fetchJSONTag($('#cut-off-time-location'), 'product_types');
        $(this).on('click', function (e) {
            const key = $(".cut_off_time_key").val()
            const count = $(".group-stt").length + 1
            $.ajax({
                url: $.base_url + '/supplier/backend_location/renderCutOffTimeGroup/hash/' + OSC_HASH,
                type: 'POST',
                dataType: 'json',
                data: {
                    product_types,
                    key,
                    count,
                },
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    let groupLocation = $('#cut-off-time-post-frm');
                    groupLocation.append(response.data.html);
                    removeInputDefaultValue()
                }
            });
        });
    }

    window.addCutOffTime = function () {
        $(this).on('click', function () {
            const group_el = $(this).closest('.cut-off-time-group')
            const uniqid = group_el.find(".cut_off_time_uniqid").val()
            const items = group_el.find('.cut-off-time-items');
            const key = $(".cut_off_time_key").val()

            $.ajax({
                url: $.base_url + '/supplier/backend_location/renderCutOffTimeItem/hash/' + OSC_HASH,
                type: 'POST',
                data: {
                    uniqid,
                    key
                },
                dataType: 'json',
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    items.append(response.data.html);

                    if (group_el.find('select[name*="[product_types]"]').val().includes('manual')) {
                        group_el.find('textarea[name*="[product_ids][]"]').closest('.frm-grid').show();
                    }

                    removeInputDefaultValue();
                }
            });
        });
    }

    window.removeCutOffTimeGroup = function () {
        $(this).on('click', function () {
            $(this).closest('.cut-off-time-group').next().remove();
            $(this).closest('.cut-off-time-group').remove();
            addInputDefaultValue()
        });
    }

    window.removeCutOffTime = function () {
        $(this).on('click', function () {
            $(this).closest('.cut-off-time-block').remove();
            addInputDefaultValue()
        });
    }

    function removeInputDefaultValue() {
        const parent = $("#cut-off-time-location");
        const child_length = parent.find('.cut-off-time-block').length
        if (child_length > 0) {
            parent.find(".cut-off-time-default").remove();
        }
    }

    function addInputDefaultValue() {
        const parent = $("#cut-off-time-location");
        const child_length = parent.find('.cut-off-time-block').length
        if (child_length === 0) {
            parent.append($('<input />').attr({type: 'hidden', name: parent.data('name'), value: '', class: 'cut-off-time-default'}));
        }
    }
})(jQuery);