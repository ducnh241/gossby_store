/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
(function ($) {
    var boxed_toggler = $('.topbar .action .boxed-toggler');
    var sidebar_toggler = $('.topbar .action .sidebar-toggler');

    var doc = $(document);
    var win = $(window);
    var body = $(document.body);

    function _shortcut(e) {
        var key = String.fromCharCode(e.which).toUpperCase();

        if (e.ctrlKey) {
            if (e.altKey) {
                switch (String.fromCharCode(e.which).toLowerCase()) {
                    case 'x':
                        boxed_toggler.trigger('click');
                        break;
                    case 'z':
                        sidebar_toggler.trigger('click');
                        break;
                }
            }
        }
    }

    var _HASH_EXTEND_FLAG = true;
    var _HAS_EXPIRED = false;
    var _HASH_CLEAR_EXTEND_FLAG_TIMER = null;
    const _SESSION_EXPIRED_MSG = "Your session has been expired. Please backup your work and data. Then refresh page to continue your work"
    var interValExtendHash = null

    function _extendHastTimeout() {
        if (!_HASH_EXTEND_FLAG || _HAS_EXPIRED) {
            return;
        }
        $.ajax({
            url: $.base_url + '/backend/ignoreHash/extendHashTimeout',
            data: {hash: OSC_HASH}
        }).success(function (data) {
            if (data.result == 'ERROR') {
                alert(_SESSION_EXPIRED_MSG);
                _HAS_EXPIRED = true;
                _HASH_EXTEND_FLAG = false;
            }
        }).error(function () {
            alert(_SESSION_EXPIRED_MSG);
            _HAS_EXPIRED = true;
            _HASH_EXTEND_FLAG = false;
        })
    }

    $(document).bind('keydown.extendHashTimeout mousemove.extendHashTimeout', function () {
        clearTimeout(_HASH_CLEAR_EXTEND_FLAG_TIMER);

        if (_HASH_EXTEND_FLAG === false) {
            _HASH_EXTEND_FLAG = true;
            clearInterval(interValExtendHash)
            _extendHastTimeout()
            interValExtendHash = setInterval(_extendHastTimeout, 900000)
        }

        _HASH_CLEAR_EXTEND_FLAG_TIMER = setTimeout(function () {
            // $(document).unbind('.extendHashTimeout');
            _HASH_EXTEND_FLAG = false;
        }, 3600000);
    }).trigger('mousemove');

    // _extendHastTimeout();
    interValExtendHash = setInterval(_extendHastTimeout, 900000)

    doc.ready(function () {
        boxed_toggler.click(function (e) {
            body.toggleClass('boxed');

            if (body.hasClass('boxed')) {
                $.cookie("BACKEND_BOXED_MODE", 1, {path: '/' + $.base, domain: '.' + $.domain});
                boxed_toggler.addClass('active');
            } else {
                $.cookie("BACKEND_BOXED_MODE", 0, {path: '/' + $.base, domain: '.' + $.domain});
                boxed_toggler.removeClass('active');
            }
        });

        sidebar_toggler.click(function (e) {
            body.toggleClass('sidebar-toggled');

            if (body.hasClass('sidebar-toggled')) {
                $.cookie("BACKEND_SIDEBAR_TOGGLED", 1, {path: '/' + $.base, domain: '.' + $.domain});
                sidebar_toggler.addClass('active');
            } else {
                $.cookie("BACKEND_SIDEBAR_TOGGLED", 0, {path: '/' + $.base, domain: '.' + $.domain});
                sidebar_toggler.removeClass('active');
            }
        });

        doc.bind('keydown', _shortcut);

        $('.sidebar').on('click', '.toggler', function (e) {
            e.stopPropagation();
            e.preventDefault();

            var li = $(this).closest('li');

            if (li.hasClass('active')) {
                li.removeClass('active');
            } else {
                li.parent().find('> li').removeClass('active');
                li.addClass('active');
            }

            if ($(document.body).hasClass('sidebar-toggled')) {
                li.find('> ul')[li.hasClass('active') ? 'slideDown' : 'slideUp']();
            }
        });

        setInterval(function () {
            var date = new Date().toLocaleString("en-US", {timeZone: OSC_TIMEZONE});
            date = new Date(date);

            $('.topbar .clock .time').html(date.format('h:i:s A'));
            $('.topbar .clock .date').html(date.format('D, F j, Y'));
        }, 1000);
    });

    window.initCopyLink = function () {
        $(this).click(function () {
            var draft = $('<input />').attr({type: 'text', value: $(this).attr('href')}).css({position: 'fixed', top: 0, left: 0, opacity: 0}).appendTo(document.body);
            draft[0].focus();
            draft[0].select();
            document.execCommand('copy');
            draft.remove();
        });
    };

    window.initAjaxAction = function () {
        $(this).click(function () {
            var btn = $(this);

            if (btn.attr('act-locked') === 'locked' || (btn.attr('confirm-message') && !window.confirm(btn.attr('confirm-message')))) {
                return;
            }

            btn.attr('act-locked', 'locked');

            $.ajax({
                url: btn.attr('uri'),
                type: 'get',
                success: function (response) {
                    btn.removeAttr('act-locked');

                    var callback = null;

                    if (response.result !== 'OK') {
                        if (btn.attr('err-callback')) {
                            eval('callback = ' + btn.attr('err-callback'));
                            callback(response);
                        } else {
                            alert(response.data.message);
                        }
                        return;
                    }

                    if (btn.attr('success-callback')) {
                        eval('callback = ' + btn.attr('success-callback'));
                        callback.apply(btn[0], [response]);
                    }
                }
            });
        });
    };

    window.initSearchForm = function () {
        var frm = $(this);

        var menu = $('<div />').addClass('filter-frm');

        var filter_config = [];

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
                } else if (config.type === 'checkbox') {
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
                } else if (config.type === 'radio') {
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
                } else if (config.type === 'daterange') {
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
                } else if (config.type === 'datetimerange') {
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
                } else if(config.type === 'number') {
                    var input = $('<input />').addClass('styled-input').attr('type','number').val(config.value).appendTo($('<div />').appendTo(input_container).append($('<ins />')));
                    input.change(function () {
                        __updateFilter(config, input.val());
                    });
                } else if (config.type === 'range') {
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
            });
        }

        $.each(fetchJSONTag(frm, 'filter-config'), function (key, config) {
            config.key = key;

            if (['radio', 'select'].indexOf(config.type) >= 0) {
                var data = {'any': 'Any'};

                $.each(config.data, function (k, v) {
                    data[k] = v;
                });

                config.data = data;
            }

            filter_config.push(config);

            __updateFilter(config, config.value);
        });

        frm.find('button.filter').osc_toggleMenu({
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
    };

    window.initDateRanger = function () {
        var input = $(this).find('input');

        const range_selected = input.val()

        var daterange_picker = $(this).find('.styled-date-time-input');

        if (range_selected) {
            var splitted = range_selected.split(/\s*\-\s*/i);
        } else {
            var splitted = [moment().format('DD/MM/YYYY')];
        }
        daterange_picker.daterangepicker({
            popupAttrs: {'data-menu-elm': 1},
            alwaysShowCalendars: true,
            startDate: moment(splitted[0], "DD/MM/YYYY"),
            endDate: moment(splitted.length > 1 ? splitted[1] : splitted[0], "DD/MM/YYYY"),
            ranges: {
                // 'Today': [moment(), moment()],
                // 'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 14 Days': [moment().subtract(13, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'Last 3 Months': [moment().subtract(3, 'months').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last 12 Months': [moment().subtract(12, 'months').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Quarter to date': [moment().subtract(1, 'quarters').startOf('quarters'), moment().subtract(1, 'quarters').endOf('quarters')],
                'All time': [],
            },
            maxDate: moment().format('MM/DD/YYYY')
        }).bind('apply.daterangepicker', function (e, picker) {
            if (parseInt(picker.startDate.format('YYYYMMDD')) === parseInt(picker.endDate.format('YYYYMMDD'))) { // all time
                var value = null
            } else {
                var value = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
            }

            input.val(value);
        });
    };

    window.initPostFrmSidebarImageUploader = function () {
        var container = $(this);

        container.addClass('collection-image-uploader');

        var preview = $('<div />').addClass('preview').appendTo(container).append($.renderIcon('collection-shoes'));

        var image_url = container.attr('data-image');
        var input_name = container.attr('data-input');
        var input_value = container.attr('data-value');
        var s3_dir_url = container.attr('data-s3-dir');
        var trigger_change = container.attr('data-trigger-change') || '';
        if (input_name == 'avatar') {
            preview.css('border-radius', '50%');
            preview.css('background-size', 'cover');
        }

        var __renderImage = function(input_value) {
            if (image_url !== '') {
                preview.find('svg').hide();

                preview.css('background-image', 'url(' + image_url + ')');

                let hiddenInput = preview.find(`input[name="${input_name}"]`);

                if (!hiddenInput.length) {
                    hiddenInput = $('<input />').attr({ type: 'hidden', name: input_name }).appendTo(preview);
                }

                hiddenInput.val(input_value);
            }
        }

        __renderImage(input_value);

        container.bind('change_image', function(e, img_path) {
            if (!img_path) {
                uploader_container.find('.remove-btn').trigger('click');
                return;
            }

            image_url = s3_dir_url + '/' + img_path;
            __renderImage(img_path);
            __initRemoveBtn();
        });

        var uploader_container = $('<div />').addClass('mt10 btn btn-primary p0').appendTo(container);

        var __initRemoveBtn = function () {
            uploader_container.find('.image-uploader').hide();
            uploader_container.find('.remove-btn').remove();

            $('<div />').addClass('btn btn-danger remove-btn').appendTo(uploader_container).text('Remove image').click(function () {
                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                image_url = '';
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();
                preview.css('background-image', 'initial');
                preview.find('input').remove();

                preview.find('svg').removeAttr('style');

                if (typeof window[trigger_change] === 'function') {
                    window[trigger_change](image_url);
                }

                __initUploader();
            });
        };

        var __initUploader = function () {
            uploader_container.find('.remove-btn').hide();
            uploader_container.find('.image-uploader').remove();

            var uploader = $('<div />').addClass('image-uploader').appendTo(uploader_container);

            uploader.osc_uploader({
                max_files: 1,
                process_url: container.attr('data-upload-url'),
                btn_content: 'Upload image',
                dragdrop_content: 'Drop here to upload',
                image_mode: true,
                xhrFields: {withCredentials: true},
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-OSC-Cross-Request': 'OK'
                }
            }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                uploader.hide();
                preview.find('svg').hide();

                __initRemoveBtn();

                preview.attr('file-id', file_id).attr('data-uploader-step', 'queue');

                $('<div />').addClass('uploader-progress-bar').appendTo(preview).append($('<div />'));
                $('<div />').addClass('step').appendTo(preview);

                var reader = new FileReader();
                reader.onload = function (e) {
                    if (preview.attr('file-id') !== file_id) {
                        return;
                    }

                    var img = document.createElement('img');

                    img.onload = function () {
                        var canvas = document.createElement('canvas');

                        var MAX_WIDTH = 400;
                        var MAX_HEIGHT = 400;

                        var width = img.width;
                        var height = img.height;

                        if (width > height) {
                            if (width > MAX_WIDTH) {
                                height *= MAX_WIDTH / width;
                                width = MAX_WIDTH;
                            }
                        } else {
                            if (height > MAX_HEIGHT) {
                                width *= MAX_HEIGHT / height;
                                height = MAX_HEIGHT;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;

                        var ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(function (blob) {
                            preview.css('background-image', 'url(' + URL.createObjectURL(blob) + ')');
                        });
                    };

                    img.src = e.target.result;
                };

                reader.readAsDataURL(file);
            }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                if (parseInt(uploaded_percent) === 100) {
                    preview.attr('data-uploader-step', 'process');
                } else {
                    preview.attr('data-uploader-step', 'upload');
                    preview.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
                }

            }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                eval('response = ' + response);

                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();

                if (response.result !== 'OK') {
                    preview.find('svg').removeAttr('style');
                    preview.css('background-image', image_url !== '' ? ('url(' + image_url + ')') : 'initial');
                    alert(response.message);

                    __initUploader();

                    return;
                }

                preview.css('background-image', 'url(' + response.data.url + ')');

                image_url = response.data.url;
                preview.find('input').remove();

                $('<input />').attr({type: 'hidden', name: input_name, value: response.data.file}).appendTo(preview);

                if (typeof window[trigger_change] === 'function') {
                    window[trigger_change](image_url);
                }
            }).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
                if (preview.attr('file-id') !== file_id) {
                    return;
                }

                __initUploader();

                preview.find('svg').removeAttr('style');
                preview.removeAttr('file-id');
                preview.removeAttr('data-uploader-step');
                preview.find('.step').remove();
                preview.find('.uploader-progress-bar').remove();
                preview.css('background-image', image_url !== '' ? ('url(' + image_url + ')') : 'initial');

                alert('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại');
            });
        };

        if (image_url !== '') {
            __initRemoveBtn();
        } else {
            __initUploader();
        }
    };

    window.fetchEstimateTimeExceptWeekendDays = function (days) {
        days = parseInt(days);

        if (days <= 0) {
            days = 0;
        }

        var date = new Date().toLocaleString("en-US", {timeZone: OSC_TIMEZONE});
        let current_timestamp = new Date(date).getTime();

        let count_weekend = 0;
        let count_weekend_in_plus_days = 0;
        let timestamp = 0;
        let timestamp_plus_day = 0;
        const one_day = 60 * 60 * 24 * 1000;

        for (let i = 1; i <= days; i++) {
            timestamp = current_timestamp + one_day * i;

            if (isWeekend(timestamp)) {
                count_weekend++;
            }
        }

        for (let i = 1; i <= count_weekend; i++) {
            timestamp_plus_day = current_timestamp + one_day * days + one_day * i;

            if (isWeekend(timestamp_plus_day)) {
                count_weekend_in_plus_days++;
            }
        }

        return current_timestamp + one_day * days + one_day * count_weekend + one_day * count_weekend_in_plus_days;
    }

    function isWeekend(timestamp) {
        //Sunday is 0, Monday is 1, Saturday is 6 and so on.
        const day = new Date(timestamp).getDay();
        return day === 0 || day === 6;
    }

    window.initQuickLook = function () {
        $(this).click(async function () {
            $.unwrapContent('quickLookPopup');
            let modal_form = $('<form />').attr('id', 'thumbnail-preview').addClass('osc-modal').css({
                'width': 540, 'height': 583, 'border-radius': '8px'
            });
            let header = $('<header />').appendTo(modal_form);
            $('<div />').addClass('title').html('Quick Look').appendTo($('<div />').addClass('main-group').appendTo(header));
            let bg = $(this).data('image')
            let modal_body = $('<div />').css({
                'padding-top': 0, 'width': 500, 'height': 500
            }).addClass('body post-frm').appendTo(modal_form);
            $('<img />').css({
                'width': 500, 'border-radius': '4px', 'height': 500, 'object-fit': 'contain'
            }).attr('src', bg).appendTo(modal_body)

            $('<div />').addClass('close-btn').click(function () {
                $.unwrapContent('quickLookPopup');
            }).appendTo(header);

            $.wrapContent(modal_form, {key: 'quickLookPopup'});
            modal_form.moveToCenter().css('top', '100px');
        });
    }

})(jQuery);