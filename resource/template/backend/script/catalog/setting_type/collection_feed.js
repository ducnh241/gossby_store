(function ($) {
    'use strict';

    window.catalogInitSettingCollectionFeedTable = function () {
        let collection_ids_selected = [];
        var container = $(this);

        var frm_name = container.attr('data-name');

        var json = fetchJSONTag(container, 'collection-feed-table');

        var table = $('<table />');
        if (json.data) {
            if (Object.keys(json.data).length) {
                $.each(json.data, function (key_uniqid, collection) {
                    collection_ids_selected.push(parseInt(collection.collection_id))
                });
            }
        }

        function __addRow(key_uniqid, collection) {
            if (table.find('tr').length < 1) {
                container.find("input[type='hidden']").remove();
                table.prependTo(container);

                var row = $('<tr />').appendTo(table);
                $('<th />').text('Catalog Collection').appendTo(row);
                $('<th />').width('25%').text('Title').appendTo(row);
                $('<th />').text('Position').appendTo(row);
                $('<th />').width('135px').html('&nbsp;').appendTo(row);
            }

            var key = key_uniqid || $.makeUniqid();

            var row = $('<tr />').attr('data-content', 1).appendTo(table);

            let input_collection_name_hidden_el = $('<input />').attr('type', 'hidden').attr('name', frm_name + '[' + key + '][collection_name]').appendTo(row)
            var product_type_name_selector = $('<select />')
                .change(function () {
                    const pre_catalog_collection_id = $(this).data('pre_catalog_collection_id');
                    const pre_catalog_collection_name = $(this).data('pre_catalog_collection_name');

                    const catalog_collection_id = parseInt($(this).find(':selected').val())
                    const catalog_collection_name = $(this).find(':selected').text()
                    product_type_name_selector.data('pre_catalog_collection_id', catalog_collection_id)
                    product_type_name_selector.data('pre_catalog_collection_name', catalog_collection_name)
                    input_collection_name_hidden_el.val(catalog_collection_name)
                    if (catalog_collection_id) {
                        collection_ids_selected.push(catalog_collection_id)

                        $.each($('.form_user_group_select select').not(this), function (index, _select) {
                            $.each($(_select).find(' > option'), function (i, _opt) {
                                if ($(_opt).val() == catalog_collection_id) {
                                    $(_opt).remove()
                                }
                            })
                        })
                    }

                    if (pre_catalog_collection_id) {
                        const option_el = $(`<option value="${pre_catalog_collection_id}">${pre_catalog_collection_name}</option>`);
                        $('.form_user_group_select select').not(this).find(' > option:first').after(option_el)
                        collection_ids_selected.splice( collection_ids_selected.indexOf(pre_catalog_collection_id), 1 );
                    }
                })
                .attr('name', frm_name + '[' + key + '][collection_id]')
                .prependTo($('<div />')
                .addClass('form_user_group_select')
                .appendTo($('<td style="width: 250px;max-width: 250px;" />')
                .appendTo(row)));

            product_type_name_selector.select2({
                width: '100%'
            })
            $.each(json.catalog_collections, function (catalog_collection_id, catalog_collection_name) {
                catalog_collection_id = parseInt(catalog_collection_id)
                if (collection) {
                    if (collection_ids_selected.indexOf(catalog_collection_id) === -1 || catalog_collection_id == collection.collection_id) {
                        let option_el = $('<option />')
                        .attr('value', catalog_collection_id)
                        .text(catalog_collection_name)
                        .appendTo(product_type_name_selector);
                        if (catalog_collection_id == collection.collection_id) {
                            option_el.attr('selected', 'selected');
                            input_collection_name_hidden_el.val(catalog_collection_name)
                            product_type_name_selector.data('pre_catalog_collection_id', catalog_collection_id)
                            product_type_name_selector.data('pre_catalog_collection_name', catalog_collection_name)
                        }
                    }
                } else {
                    if (collection_ids_selected.indexOf(catalog_collection_id) === -1) {
                        $('<option />')
                            .attr('value', catalog_collection_id)
                            .text(catalog_collection_name)
                            .appendTo(product_type_name_selector);
                    }
                }
            });

            $('<input />').attr('maxlength', '15')
                .attr('type', 'text')
                .attr('name', frm_name + '[' + key + '][title]')
                .attr('id', 'name')
                .attr('maxlength', '255')
                .prop('required',true)
                .attr('value', collection ? collection.title : '' )
                .addClass('styled-input')
                .keyup(function () {
                    let title = $(this).val()
                    const patt = /[^a-zA-Z0-9-|_'"()![\] ]/gi;
                    title = title.replace(patt, '');
                    $(this).val(title)
                })
                .appendTo($('<td />')
                .appendTo(row));
            $('<td />').html(`<div class="setting-item">
                    <input type="checkbox" name='${frm_name}[${key}][prefix]' value='${collection && collection.prefix ? 1 : 0}' ${collection && collection.prefix ? 'checked' : ''} 
                        data-insert-cb="initSwitcher">
            </div>`).appendTo(row);
            var control_bar = $('<td />').appendTo(row);

            var up_btn = $('<div />').append($.renderIcon('chevron-up-light')).addClass('btn btn-small btn-icon').appendTo(control_bar).click(function () {
                if (up_btn.attr('disabled') === 'disabled') {
                    return;
                }

                row.insertBefore(row.prev('tr[data-content="1"]'));
                row.trigger('reorder');
                row.next('tr[data-content="1"]').trigger('reorder');
            });

            var down_btn = $('<div />').append($.renderIcon('chevron-down-light')).addClass('btn btn-small btn-icon ml5').appendTo(control_bar).click(function () {
                if (down_btn.attr('disabled') === 'disabled') {
                    return;
                }

                row.insertAfter(row.next('tr[data-content="1"]'));
                row.trigger('reorder');
                row.prev('tr[data-content="1"]').trigger('reorder');
            });

            row.bind('reorder', function (e) {
                e.stopImmediatePropagation();

                if (!row.prev('tr[data-content="1"]')[0]) {
                    up_btn.attr('disabled', 'disabled');
                } else {
                    up_btn.removeAttr('disabled');
                }

                if (!row.next('tr[data-content="1"]')[0]) {
                    down_btn.attr('disabled', 'disabled');
                } else {
                    down_btn.removeAttr('disabled');
                }
            });

            table.find('tr[data-content="1"]').trigger('reorder');

            $('<div />').append($.renderIcon('trash-alt-regular')).addClass('btn btn-small btn-icon ml5').appendTo(control_bar).click(function () {
                row.remove();

                if (table.find('tr').length < 2) {
                    table.parent().append($('<input />').attr({"type" : "hidden", "name": frm_name ,"value":""}));
                    table.html('').detach();
                } else {
                    table.find('tr[data-content="1"]').trigger('reorder');
                }
                const catalog_collection_id = parseInt(product_type_name_selector.find(':selected').val());
                const catalog_collection_name = product_type_name_selector.find(':selected').text()
                if (catalog_collection_id) {
                    collection_ids_selected.splice( collection_ids_selected.indexOf(catalog_collection_id), 1 );

                    const option_el = $(`<option value="${catalog_collection_id}">${catalog_collection_name}</option>`);

                    $.each($('.form_user_group_select select'), function (index, _select) {
                        let _opt_vals = []
                        $.each($(_select).find(' > option'), function (i, _opt) {
                            _opt_vals.push(parseInt($(_opt).val()))
                        })
                        if (_opt_vals.indexOf(catalog_collection_id) === -1) {
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

        $('<div />').addClass('btn btn-secondary-add mt10').text('Add new row').appendTo(container).click(function () {
            __addRow();
        });
    };
})(jQuery);