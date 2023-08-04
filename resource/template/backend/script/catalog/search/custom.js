(function ($) {
    'use strict';

    window.initSearchFormCustom = function () {
        const frm = $(this);

        const menu = $('<div />').addClass('filter-frm');
        let filter_config = [];

        function __updateFilter(config, value) {
            var filtered_label_container = frm.find('.filtered-labels');

            if (typeof value === 'undefined' || value === 'any' || value === '' || value === null || value === false) {
                if (!filtered_label_container[0]) {
                    return;
                }

                filtered_label_container.find('[data-key="' + config.key + '"]').remove();

                if (filtered_label_container[0].childNodes.length < 1) {
                    filtered_label_container.remove();
                }

                return;
            }

            if (!filtered_label_container[0]) {
                filtered_label_container = $('<div />').addClass('filtered-labels').insertAfter(frm.find('.styled-search'));
            }

            var label = filtered_label_container.find('[data-key="' + config.key + '"]');

            if (!label[0]) {
                label = $('<span />').addClass('filtered-label').attr('data-key', config.key).appendTo(filtered_label_container);
            }

            label.html('');

            var value_display = '';

            if (Array.isArray(value)) {
                value_display = [];

                value.forEach(function (k) {
                    value_display.push(config.data[k]);
                });

                value_display = value_display.join(', ');
            } else if (['radio', 'select'].indexOf(config.type) >= 0) {
                value_display = config.title + ': ' + config.data[value];
            } else if (['range'].indexOf(config.type) >= 0) {
                value_display = '';

                if (!value.min) value_display += 'From 0';
                else value_display += 'From ' + value.min;

                if (!value.max) value_display += ' to ∞';
                else value_display += ' to ' + value.max;

                if (value.time) value_display += ' for ' + value.time;
            } else if (['operator'].indexOf(config.type) >= 0) {
                value_display = config.title + ': ' + value.type + ' ' + value.comparison + ' ' + value.value;
            } else {
                value_display = value;
            }

            if (Array.isArray(value)) {
                value.forEach(function (k) {
                    $('<input />').attr({
                        name: 'filter[' + config.key + '][]',
                        type: 'hidden',
                        value: k
                    }).appendTo(label);
                });
            } else if (['range'].indexOf(config.type) >= 0) {
                if (typeof value.min !== "undefined") {
                    $('<input />').attr({name: 'filter[' + config.key + '][min]', type: 'hidden', value: value.min}).appendTo(label);
                }
                if (typeof value.max !== "undefined") {
                    $('<input />').attr({name: 'filter[' + config.key + '][max]', type: 'hidden', value: value.max}).appendTo(label);
                }
                if (typeof value.time !== "undefined") {
                    $('<input />').attr({name: 'filter[' + config.key + '][time]', type: 'hidden', value: value.time}).appendTo(label);
                }
            } else if (['operator'].indexOf(config.type) >= 0) {
                $('<input />').attr({name: 'filter[' + config.key + ']', type: 'hidden', value: JSON.stringify(value)}).appendTo(label);
            } else {
                $('<input />').attr({name: 'filter[' + config.key + ']', type: 'hidden', value: value}).appendTo(label);
            }

            $('<span />').text((config.prefix ? (config.prefix + (config.operator ? ' ' + config.operator + ' ' : ": " )) : '') + value_display).appendTo(label);

            $('<ins />').appendTo(label).click(function () {
                label.remove();

                if (filtered_label_container[0].childNodes.length < 1) {
                    filtered_label_container.remove();
                }

                if (['range'].indexOf(config.type) >= 0) {
                    config.value = {min: null, max: null,time: null};
                } else if (Array.isArray(config.value)) {
                    config.value = [];
                } else {
                    config.value = '';
                }
            });

            config.value = value;
        }

        function __renderFilterFrm() {
            menu.html('');

            menu.appendTo(document.body);

            filter_config.forEach(function (config) {
                var container = $('<div />').addClass('filter-element').appendTo(menu);

                $('<div />').addClass('title').text(config.title).append($.renderIcon('angle-down-solid')).appendTo(container).click(function () {
                    if (container.hasClass('active')) {
                        container.removeClass('active');
                    } else {
                        container.siblings().removeClass('active');
                        container.addClass('active');
                    }
                });

                var input_container = $('<div />').addClass('filter-input').appendTo(container);

                if (config.type === 'select') {
                    if (!config.value) {
                        config.value = 'any';
                    }

                    var select = $('<select />').appendTo($('<div />').addClass('styled-select').appendTo(input_container).append($('<ins />')));

                    $.each(config.data, function (k, v) {
                        var option = $('<option />').attr('value', k).text(v).appendTo(select);

                        if (k === config.value) {
                            option.attr('selected', 'selected');
                        }
                    });

                    select.change(function () {
                        __updateFilter(config, select.val());
                    });
                }
                else if (config.type === 'checkbox') {
                    var name = $.makeUniqid();

                    if (!Array.isArray(config.value)) {
                        config.value = [];
                    }

                    $.each(config.data, function (k, v) {
                        var checkbox_container = $('<div />').addClass('mb5').appendTo(input_container);

                        var checkbox_elm = $('<div />').addClass('styled-checkbox').appendTo(checkbox_container);

                        var id = $.makeUniqid();

                        var input = $('<input />').attr({type: 'checkbox', name: name, value: k, id: id}).appendTo(checkbox_elm).click(function () {
                            var selected = [];

                            input_container.find('input[type="checkbox"]:checked').each(function () {
                                selected.push(this.value);
                            });

                            __updateFilter(config, selected.length < 1 ? null : selected);
                        });

                        if (config.value.indexOf(String(k)) >= 0) {
                            input.attr('checked', 'checked');
                        }

                        $('<ins />').append($.renderIcon('check-solid')).appendTo(checkbox_elm);

                        $('<label />').attr('for', id).addClass('ml5 label-inline').text(v).appendTo(checkbox_container);
                    });
                }
                else if (config.type === 'radio') {
                    if (!config.value) {
                        config.value = 'any';
                    }

                    var name = $.makeUniqid();

                    $.each(config.data, function (k, v) {
                        var radio_container = $('<div />').addClass('mb5').appendTo(input_container);

                        var radio_elm = $('<div />').addClass('styled-radio').appendTo(radio_container);

                        var id = $.makeUniqid();

                        var input = $('<input />').attr({type: 'radio', name: name, value: k, id: id}).appendTo(radio_elm).click(function () {
                            __updateFilter(config, this.value);
                        });

                        if (k === config.value) {
                            input.attr('checked', 'checked');
                        }

                        $('<ins />').appendTo(radio_elm);

                        $('<label />').attr('for', id).addClass('ml5 label-inline').text(v).appendTo(radio_container);
                    });
                }
                else if (config.type === 'daterange') {
                    var daterange_picker = $('<div />').addClass('styled-date-time-input').appendTo(input_container);
                    var input = $('<input />').attr({type: 'text', value: config.value}).appendTo($('<div />').addClass('date-input').append($.renderIcon('calendar-alt')).appendTo(daterange_picker));

                    if (config.value) {
                        var splitted = config.value.split(/\s*\-\s*/i);
                    } else {
                        var splitted = [moment().format('DD/MM/YYYY')];
                    }

                    input.change(function () {
                        __updateFilter(config, input.val());
                    });

                    daterange_picker.daterangepicker({
                        popupAttrs: {'data-menu-elm': 1},
                        alwaysShowCalendars: true,
                        startDate: moment(splitted[0], "DD/MM/YYYY"),
                        endDate: moment(splitted.length > 1 ? splitted[1] : splitted[0], "DD/MM/YYYY"),
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                        }
                    }).bind('apply.daterangepicker', function (e, picker) {
                        if (parseInt(picker.startDate.format('YYYYMMDD')) === parseInt(picker.endDate.format('YYYYMMDD'))) {
                            var value = picker.startDate.format('DD/MM/YYYY');
                        } else {
                            var value = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
                        }

                        __updateFilter(config, value);
                        input.val(value);

                        //input.val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                    });
                }
                else if (config.type === 'datetimerange') {
                    var daterange_picker = $('<div />').addClass('styled-date-time-input').appendTo(input_container);
                    var input = $('<input />').attr({type: 'text', value: config.value}).appendTo($('<div />').addClass('date-input').append($.renderIcon('calendar-alt')).appendTo(daterange_picker));

                    if (config.value) {
                        var splitted = config.value.split(/\s*\-\s*/i);
                    } else {
                        var splitted = [moment().format('DD/MM/YYYY HH:mm')];
                    }

                    daterange_picker.daterangepicker({
                        popupAttrs: {'data-menu-elm': 1},
                        alwaysShowCalendars: true,
                        timePicker: true,
                        startDate: moment(splitted[0], "DD/MM/YYYY HH:mm"),
                        endDate: moment(splitted.length > 1 ? splitted[1] : splitted[0], "DD/MM/YYYY HH:mm"),
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                        }
                    }).bind('apply.daterangepicker', function (e, picker) {
                        if (parseInt(picker.startDate.format('YYYYMMDDHHmm')) === parseInt(picker.endDate.format('YYYYMMDDHHmm'))) {
                            var value = picker.startDate.format('DD/MM/YYYY HH:mm');
                        } else {
                            var value = picker.startDate.format('DD/MM/YYYY HH:mm') + ' - ' + picker.endDate.format('DD/MM/YYYY HH:mm');
                        }

                        __updateFilter(config, value);
                        input.val(value);

                        //input.val(start.format('MMM D, YYYY HH:mm') + ' - ' + end.format('MMM D, YYYY HH:mm'));
                    });
                }
                else if(config.type === 'number') {
                    var input = $('<input />').addClass('styled-input').attr('type','number').val(config.value).appendTo($('<div />').appendTo(input_container).append($('<ins />')));
                    input.change(function () {
                        __updateFilter(config, input.val());
                    });
                }
                else if (config.type === 'range') {
                    if (typeof config.value === "undefined") {
                        config.value = {min: null, max: null, time: null};
                    }
                    const rangeContainer = $('<div />').addClass('range-text');
                    $('<div />')
                        .append($('<label />').attr('for', 'min').text('Min'))
                        .append($('<input/>').addClass('styled-input filter-sold-min').attr({id: 'min', name: 'min', type: 'number', min: 0, inputmode: 'numeric', value: config.value?.min}).on('change', function(){
                            if (this.value >= 0) {
                                config.value.min  = this.value;
                            }
                            __updateFilter(config, config.value);
                        })
                            .on('keyup', function() {
                                this.value=this.value.replace(/[^0-9]/g,'');
                            }))
                        .appendTo(rangeContainer);

                    $('<div />')
                        .append($('<label />').attr('for', 'max').text('Max'))
                        .append($('<input/>').addClass('styled-input').attr({id: 'max', name: 'max', type: 'number', min: 1, inputmode: 'numeric', value: config.value?.max}).on('change', function(){
                            if (this.value >= 0) {
                                config.value.max = this.value;
                            }
                            __updateFilter(config, config.value);
                        })
                            .on('keyup', function() {
                                this.value=this.value.replace(/[^0-9]/g,'');
                            }))
                        .appendTo(rangeContainer);

                    $('<div />').addClass('styled-range').append(rangeContainer).appendTo(input_container);

                    const sold_time = $('<div />').addClass('sold-in-time');

                    $('<label />').css('margin-top', '5px').attr('for', 'sold_in_time').text('Time').appendTo(sold_time);

                    var daterange_picker = $('<div />').addClass('styled-date-time-input').appendTo(sold_time);

                    var input = $('<input />').attr({id: 'sold_in_time', type: 'text', value: config.value?.time}).appendTo($('<div />').addClass('date-input').append($.renderIcon('calendar-alt')).appendTo(daterange_picker));

                    if (config.value?.time) {
                        var splitted = config.value.time.split(/\s*\-\s*/i);
                    } else {
                        var splitted = [moment().format('DD/MM/YYYY')];
                    }

                    input.change(function () {
                        if (this.value >= 0) {
                            config.value.time = this.value;
                        }
                        __updateFilter(config, config.value);
                    });

                    daterange_picker.daterangepicker({
                        popupAttrs: {'data-menu-elm': 1},
                        alwaysShowCalendars: true,
                        startDate: moment(splitted[0], "DD/MM/YYYY"),
                        endDate: moment(splitted.length > 1 ? splitted[1] : splitted[0], "DD/MM/YYYY"),
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                        }
                    }).bind('apply.daterangepicker', function (e, picker) {
                        var value = '';
                        if (parseInt(picker.startDate.format('YYYYMMDD')) === parseInt(picker.endDate.format('YYYYMMDD'))) {
                            value = picker.startDate.format('DD/MM/YYYY');
                        } else {
                            value = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
                        }
                        config.value.time = value;
                        __updateFilter(config, config.value);
                        input.val(value);
                    });

                    sold_time.appendTo(input_container);
                }
                else if (config.type === 'operator') {
                    console.log(config);
                    let name = $.makeUniqid(),
                        _filter_value = {
                            type: config?.value?.type ?? 'viewed_product',
                            comparison: config?.value?.comparison ?? 'GREATER_THAN',
                            value: config?.value?.value ?? 0
                        };

                    $('<label />').css({display: 'block', 'margin-bottom': '5px'}).text('Filter by').appendTo(input_container);
                    $.each(config.data.filter_type, function (k, v) {
                        const radio_container = $('<div />').addClass('mb5').appendTo(input_container);
                        const radio_elm = $('<div />').addClass('styled-radio').appendTo(radio_container);
                        const id = $.makeUniqid();
                        const input = $('<input />').attr({type: 'radio', name: 'filter_type_' + name, value: k, id: id}).appendTo(radio_elm);

                        if (k === config?.value?.type) {
                            input.attr('checked', 'checked');
                        }

                        input.change(function () {
                            _filter_value.type = input.val();
                        });

                        $('<ins />').appendTo(radio_elm);
                        $('<label />').attr('for', id).addClass('ml5 label-inline').text(v).appendTo(radio_container);
                    });

                    const operatorContainer = $('<div />').addClass('operator-form');
                    const _select_form = $('<div />').append($('<label />').attr('for', 'operator_select').text('Operator')).appendTo(operatorContainer);
                    const _option_selector = $('<select />').appendTo($('<div />').addClass('styled-select').appendTo(_select_form).append($('<ins />')));
                    $.each(config.data.comparison, function (k, v) {
                        var _option = $('<option />').attr({value: k}).text(v).appendTo(_option_selector);

                        if (k === config?.value?.comparison) {
                            _option.attr('selected', 'selected');
                        }
                    });

                    _option_selector.change(function () {
                        _filter_value.comparison = _option_selector.val();
                    });

                    const _input_value = $('<input/>').addClass('styled-input').attr({id: 'operator_value', name: 'operator_value', value: config?.value?.value, type: 'number', min: 0, inputmode: 'numeric'})
                        .on('keyup', function() {
                            this.value=this.value.replace(/[^0-9]/g,'');
                        }).on('change',function () {
                            _filter_value.value = _input_value.val();
                        });
                    $('<div />')
                        .append($('<label />').attr('for', 'operator_value').text('Value'))
                        .append(_input_value)
                        .appendTo(operatorContainer);

                    $('<div />').addClass('styled-operator mb5').append(operatorContainer).appendTo(input_container);

                    $('<button />').attr({type: 'button', name: 'update_filter'}).addClass('btn btn-outline').text('Update filter').click(function () {
                        console.log(_filter_value);
                        __updateFilter(config, _filter_value);
                    }).appendTo(input_container);
                }
            });
        }

        $.each(fetchJSONTag(frm, 'filter-config'), function (key, config) {
            config.key = key;

            if (['radio', 'select'].indexOf(config.type) >= 0) {
                const data = {'any': 'Any'};

                $.each(config.data, function (k, v) {
                    data[k] = v;
                });

                config.data = data;
            }

            filter_config.push(config);

            __updateFilter(config, config.value);
        });

        frm.find('button.filter_frm').osc_toggleMenu({
            menu: menu,
            divergent_y: 5,
            toggle_mode: 'bl',
            open_hook: function (params) {
                __renderFilterFrm();
            },
            close_hook: function () {
                menu.html('');
                menu.detach();
            }
        });

        const menu_field = $('<div />').addClass('filter-frm filter-frm-field');
        let filter_field = [];
        const default_search_field_key = $('#default_search_field_key').val();

        function __renderFilterField() {
            menu_field.html('');

            menu_field.appendTo(document.body);
            const selected_field = $('#selected_field').val()
            const default_field = $.cookie(default_search_field_key)

            filter_field.forEach(function (item) {
                const container = $('<div />').addClass('filter-element' + (item.key === selected_field ? ' selected' : '') + (item.key === default_field ? ' default' : '')).appendTo(menu_field);
                const btn_default = $('<span />').addClass('btn-default').append($.renderIcon('set-default-icon')).click(function () {
                    $(this).hide();
                    $.cookie(default_search_field_key, item.key);
                    $('.filter-frm-field .filter-element').each(function () {
                        const $this = $(this)
                        if ($this.hasClass('default')) {
                            $this.removeClass('default');
                        }

                        if ($this.find('.title').attr('key') === item.key) {
                            $this.addClass('default');
                        }
                    })
                    $('title-' + item.key).trigger('click');
                    $('#selected_field').val(item.key);
                    $('#lbl_selected_field').text(item.value);
                })

                $('<div />').addClass('title').attr('id', 'title-' + item.key).attr('key', item.key).text(item.value)
                    .append($('<span />').addClass('label-selected').append($.renderIcon('select-icon')))
                    .append($('<span />').addClass('label-default').text('Default'))
                    .append(btn_default)
                    .appendTo(container)
                    .mouseover(function () {
                        $('.btn-default').each(function () {
                            $(this).hide()
                        })

                        if (!$(this).parent().hasClass('default')) {
                            $(this).find('.btn-default').show()
                        }
                    })
                    .mouseleave(function () {
                        $('.btn-default').each(function () {
                            $(this).hide()
                        })
                    })
                    .click(function () {
                        $('#selected_field').val(item.key);
                        $('#lbl_selected_field').text(item.value);
                        $('.filter-frm-field .filter-element').each(function () {
                            const $this = $(this)
                            if ($this.hasClass('selected')) {
                                $this.removeClass('selected');
                            }

                            if ($this.find('.title').attr('key') === item.key) {
                                $this.addClass('selected');
                            }
                        })
                    });
            });
        }

        $.each(fetchJSONTag(frm, 'filter-field'), function (key, value) {
            filter_field.push({key: key, value: value});
        });

        frm.find('button.filter_field').osc_toggleMenu({
            menu: menu_field,
            divergent_y: 5,
            toggle_mode: 'bl',
            open_hook: function (params) {
                __renderFilterField();
            },
            close_hook: function () {
                menu_field.html('');
                menu_field.detach();
            }
        });
    };
})(jQuery);