(function ($) {
    'use strict';
    window.catalogInitSettingTip = function () {
        let container = $(this);
        let json = fetchJSONTag(container, 'tip');
        let box = $('<div />').addClass('box-root');

        let table_item = $('<table />').appendTo(box);
        __addTableHead();

        if (json.data) {
            $.each(json.data, function (index, tip_percent) {
                __addRow(tip_percent);
            });
        }

        function __addRow(tip_percent) {
            box.prependTo(container);

            if (box.find('table').length === 0) {
                table_item = $('<table />').appendTo(box);
                __addTableHead();
            }

            let table_row_item = $('<tr />').addClass('table-row-item').appendTo(table_item);
            $('<input type="number" name="config[tip/table][]" min="1" max="100" step="1"/>')
                .addClass('styled-input')
                .val(tip_percent)
                .keyup(function () {
                    let inputValue = $(this).val().replace(/[^0-9]/g, '');
                    inputValue = parseInt(inputValue);

                    if (inputValue <= 0) {
                         inputValue = '';
                    }

                    $(this).val(inputValue);
                })
                .appendTo($('<td />').appendTo(table_row_item));
            return table_item;
        }

        function __addTableHead() {
            let table_head = $('<tr />').addClass('table-head').appendTo(table_item);
            $('<th />').text('Percent').appendTo(table_head);
        }
    };
})(jQuery);
