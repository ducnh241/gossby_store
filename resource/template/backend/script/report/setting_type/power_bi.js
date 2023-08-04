(function ($) {
    'use strict';
    window.powerBiAddPoint = function () {
        function _orderStatus() {
            let stt = 0;
            $('.block.box-item').each(function () {
                ++stt;
                $(this).find('.title_report').html(`Report #${stt}`)
            })
        }
        const container = $(this),
            frm_name = container.attr('data-name'),
            json = fetchJSONTag(container, 'power_bi_point'),
            box = $('<div />');

        function _initViewersSelect() {
            $( ".addViewer").each(function(){
                const _self = this;

                const _selectValue = _getViewerselect(),
                    _selected = _selectValue.selected,
                    _avaible = _selectValue.avaible;

                $(_self).find("option:not(:selected)").each(function(){
                    const _val = $(this).val();
                    if(_selected.includes(_val)) {
                        $(this).remove();
                    }
                });

                $.each(_avaible, function (key, name) {
                    if($(_self).find("option[value='"+name+"']").length === 0) {
                        $('<option />').attr('value', key).text(name).appendTo(_self);
                    }
                });

            });

        }

        function _getViewerselect(row) {
            let _array = [],
                _selected = [],
                _avaible = {};

            $.each(json.members, function (key, name) {
                _avaible[key] = name;
            });

            _array.selected = _selected;
            if (row) {
                row.find( ".addViewer option:selected" ).each(function() {
                    _selected.push($(this).val());
                    delete _avaible[$(this).val()]
                });
                _array.avaible = _avaible
            } else {
                _array.avaible = {}
            }

            return _array;
        }

        function __addRow(ukey_config, config) {
            box.prependTo(container);
            const key = $.makeUniqid();
            const ukey = ukey_config ? ukey_config : key;

            config = config ? config : null;
            $("<div />").addClass('frm-line e20').appendTo(box);
            const row = $('<div style="padding: 10px; margin: 5px 0" />').addClass('block box-item').attr('data-content', 1).appendTo(box);
            const action_bar = $('<div />').addClass('frm-grid frm-grid--separate').appendTo(row);
            $('<div />').addClass('title bold mb25 title_report').text('').appendTo(action_bar);
            _orderStatus();
            $('<button />').addClass('btn btn-danger btn-small').html('Delete').click(function () {
                $(row).prev('.frm-line').remove();
                $(row).remove();
                _orderStatus();
            }).appendTo(action_bar);
            $('<div />').addClass('title').text('Report name').appendTo(row);
            $('<input />').attr('type', 'hidden').attr('name', frm_name + '[' + key + '][ukey]').attr('value', ukey).addClass('styled-input').appendTo(row);
            $('<input />').attr('type', 'text').prop('required',true).attr('name', frm_name + '[' + key + '][name]').attr('value', config?config.name:'').addClass('styled-input').appendTo(row);

            $('<div />').addClass('title mt15').text('Place your Power Bi Url').appendTo(row);
            $('<input />').attr('type', 'url').prop('required',true).attr('name', frm_name + '[' + key + '][url]').attr('value', config?config.url:'').addClass('styled-input').appendTo(row);

            $('<div />').addClass('title mt15').text('Viewer ').appendTo(row);
            const member_selector = $('<select />').addClass('addViewer').attr({'name': frm_name + '[' + key + '][viewer][]','multiple':'multiple'}).prependTo($('<div />').addClass('styled-select styled-select--multiple').appendTo($('<div />').appendTo(row)));


            const _selectValue = _getViewerselect(row),
                _selected = _selectValue.selected,
                _avaible = _selectValue.avaible;

            if(config && config.viewer) {
                $.each(json.members, function (key, name) {
                    const option = $('<option />').attr('value', key).text(name).appendTo(member_selector);
                    if (config.viewer.includes(key)) {
                        option.attr('selected', 'selected');
                    } else {
                        if(_selected.includes(option.val())) {
                            option.remove();
                        }
                    }
                });
            } else {
                $.each(_avaible, function (key, name) {
                    $('<option />').attr('value', key).text(name).appendTo(member_selector);
                });
            }

            member_selector.select2({
                width: '100%'
            });
        }

        if (json.data) {
            $.each(json.data, function (key,value) {
                __addRow(key,value);
            });
        }

        $(document).on('change', '.addViewer', function (e) {
            _initViewersSelect();
        })

        $('<div style="float: right" />').addClass('btn btn-primary mt10').html($.renderIcon('icon-plus', 'mr5')).append('Add New Report').appendTo(container).click(function () {
            __addRow();
        });
        _initViewersSelect();
    };
})(jQuery);