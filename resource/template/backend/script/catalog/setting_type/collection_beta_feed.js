(function ($) {
    'use strict';

    window.initSettingCollectionBetaFeed = function () {
        let collection_ids_selected = [];
        var container = $(this);

        var frm_name = container.attr('data-name');
        var json = fetchJSONTag(container, 'collection-beta-feed');

        var table = $('<table />');
        if (json.data) {
            if (Object.keys(json.data).length) {
                $.each(json.data, function (key_uniqid, collection) {
                    collection_ids_selected.push(parseInt(collection.collection_id))
                });
            }
        }

        function __addRow(key_uniqid, collection) {
            table.parent().find("input[name='" + frm_name + "']").remove();
            if (table.find('tr').length < 1) {
                container.find("input[type='hidden']").remove();
                table.prependTo(container);

                var row = $('<tr />').appendTo(table);
                $('<th />').width('20%').text('Catalog Collection').appendTo(row);
                $('<th />').width('20%').text('Google Product Feed').appendTo(row);
                $('<th />').width('20%').text('Shipping Label').appendTo(row);
                $('<th />').width('30%').text('Bypass minimum sold').appendTo(row);
                $('<th />').width('120px').html('&nbsp;').appendTo(row);
            }

            var key = key_uniqid || $.makeUniqid();

            var row = $('<tr />').appendTo(table);

            var collection_selector = $('<select />')
                .attr('name', frm_name + '[' + key + '][collection_id]')
                .prependTo($('<div />')
                .addClass('form_user_group_select')
                .appendTo($('<td style="width: 250px;max-width: 250px;" />')
                .appendTo(row)));

            collection_selector.select2({
                width: '100%'
            })
            $.each(json.catalog_collections, function (c_id, c_name) {
                c_id = parseInt(c_id)
                if (collection) {
                    if (collection_ids_selected.indexOf(c_id) === -1 || c_id == collection.collection_id) {
                        let option_el = $('<option />')
                        .attr('value', c_id)
                        .text(c_name)
                        .appendTo(collection_selector);
                        if (c_id == collection.collection_id) {
                            option_el.attr('selected', 'selected');
                            collection_selector.data('pre_c_id', c_id)
                            collection_selector.data('pre_c_name', c_name)
                        }
                    }
                } else {
                    if (collection_ids_selected.indexOf(c_id) === -1) {
                        $('<option />')
                            .attr('value', c_id)
                            .text(c_name)
                            .appendTo(collection_selector);
                    }
                }
            });

            $('<input />').attr('maxlength', '15')
                .attr('type', 'text')
                .attr('name', frm_name + '[' + key + '][google_cat_id]')
                .attr('value', collection ? collection.google_cat_id : '' )
                .addClass('styled-input')
                .appendTo($('<td />')
                    .appendTo(row));

            $('<input />').attr('type', 'text')
                .attr('name', frm_name + '[' + key + '][shipping_label]')
                .attr('value', collection ? collection.shipping_label : '' )
                .addClass('styled-input')
                .appendTo($('<td />')
                    .appendTo(row));
            $('<td />').html(`<div class="setting-item" style="text-align: center">
                    <input type="checkbox" name='${frm_name}[${key}][bypass_mqs]' value='${collection && collection.bypass_mqs == 1 ? 1 : 0}' ${collection && collection.bypass_mqs == 1 ? 'checked' : ''} 
                        data-insert-cb="initSwitcher">
            </div>`).appendTo(row);
            var control_bar = $('<td />').appendTo(row);

            $('<div />').append($.renderIcon('trash-alt-regular')).addClass('btn btn-small btn-icon ml5').appendTo(control_bar).click(function () {
                row.remove();

                if (table.find('tr').length < 2) {
                    table.parent().append($('<input />').attr({"type": "hidden", "name": frm_name, "value": ""}));
                }

                const c_id = parseInt(collection_selector.find(':selected').val());
                const c_name = collection_selector.find(':selected').text()
                if (c_id) {
                    collection_ids_selected.splice( collection_ids_selected.indexOf(c_id), 1 );

                    const option_el = $(`<option value="${c_id}">${c_name}</option>`);

                    $.each($('.form_user_group_select select'), function (index, _select) {
                        let _opt_vals = []
                        $.each($(_select).find(' > option'), function (i, _opt) {
                            _opt_vals.push(parseInt($(_opt).val()))
                        })
                        if (_opt_vals.indexOf(c_id) === -1) {
                            $('.form_user_group_select select').find(' > option:first').after(option_el)
                        }
                    })
                }
            });
            return row;
        }

        if (json.data) {
            if (Object.keys(json.data).length) {
                $.each(json.data, function (key_uniqid, collection) {
                    __addRow(key_uniqid, collection);
                });
            } else {
                __addRow();
            }
        } else {
            __addRow();
        }

        $('<div />').addClass('btn btn-secondary-add mt10').text('Add new collection').appendTo(container).click(function () {
            __addRow();
        });
    };
})(jQuery);