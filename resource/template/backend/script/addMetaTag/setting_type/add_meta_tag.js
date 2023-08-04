(function ($) {
    'use strict';

    window.catalogInitSettingAddMetaTag = function () {
        var container = $(this);
        var frm_name = container.attr('data-name');
        var json = fetchJSONTag(container, 'add_meta_tag_v2');
        var table = $('<table />');

        function __addRow(name,value) {
            if (table.find('tr').length < 1) {
                container.find("input[type='hidden']").remove();
                table.prependTo(container);
                var row = $('<tr />').appendTo(table);
                $('<th />').text('Type').appendTo(row);
                $('<th />').width('35%').text('Value').appendTo(row);
                $('<th />').width('135px').html('&nbsp;').appendTo(row);
            }

            var row = $('<tr />').attr('data-content', 1).appendTo(table);

            var key = $.makeUniqid();

            $('<input />').attr({'name': frm_name + '[' + key + '][name]','value' : name}).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row));

            $('<input />').attr({'name': frm_name + '[' + key + '][value]','value' : value}).addClass('styled-input').appendTo($('<div />')).appendTo($('<td />').appendTo(row));

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

            $('<div />').append($.renderIcon('trash-alt-regular')).addClass('btn btn-small btn-icon ml5').appendTo(control_bar).click(function () {
                row.remove();

                if (table.find('tr').length < 2) {
                    table.parent().append($('<input />').attr({type: 'hidden', name: frm_name, value: ''}));
                    table.html('').detach();
                } else {
                    table.find('tr[data-content="1"]').trigger('reorder');
                }
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

            return row;
        }

        if (json.data) {
            $.each(json.data, function ($name, $value) {
                __addRow($name, $value);
            });
        }


        $('<div />').addClass('btn btn-primary mt10').text('Add new row').appendTo(container).click(function () {
            __addRow();
        });
    };
})(jQuery);