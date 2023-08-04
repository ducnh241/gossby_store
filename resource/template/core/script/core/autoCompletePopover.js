(function ($) {
    'use strict';

    var OSC_AUTOCOMPLETEPOPOVER_POPUP = null;

    function OSC_AutocompletePopover(node, options) {
        this._initialize = function (node, config) {
            if (typeof config !== 'object') {
                config = {};
            } else {
                for (var x in config) {
                    if (x.substring(0, 1) === '_') {
                        delete(config[x]);
                    }
                }
            }

            $.extend(this, config);

            this.max_height = parseInt(this.max_height);

            if (isNaN(this.max_height)) {
                this.max_height = 0;
            }

            if (typeof this.select_callback === 'string') {
                eval('this.select_callback = ' + this.select_callback);
            }

            if (typeof this.source_callback === 'string') {
                eval('this.source_callback = ' + this.source_callback);
            }

            this._id = $.makeUniqid();

            this._container = $(node);

            if (this._container[0].nodeName.toLowerCase() === 'input' && this._container.attr('type') === 'text') {
                this._input = this._container;
            } else {
                this._input = this._container.find('input[type="text"]');
            }

            this._input.attr({autocomplete: 'off'});

            this._container.attr('data-autocomplete-popover', this._id);

            this._initEvent();
        };

        this._positionPopup = function () {
            if (OSC_AUTOCOMPLETEPOPOVER_POPUP === null || OSC_AUTOCOMPLETEPOPOVER_POPUP.attr('data-id') !== this._id) {
                return;
            }

            var margin = 10;

            var win_height = $(window).height();

            var rect = this._container[0].getBoundingClientRect();

            var css_data = {width: parseInt(rect.width) + 'px', left: parseInt(rect.x + $(window).scrollLeft()) + 'px'};

            var top_spacing = parseInt(rect.y);
            var bottom_spacing = parseInt(win_height - (rect.y + rect.height));

            var max_height = Math.max(top_spacing, bottom_spacing) - (margin * 2);

            max_height = Math.min(this.max_height > 0 ? this.max_height : max_height, max_height);

            css_data.maxHeight = max_height + 'px';

            if (max_height < OSC_AUTOCOMPLETEPOPOVER_POPUP.find('> :first-child')[0].getBoundingClientRect().height && bottom_spacing < max_height && top_spacing > bottom_spacing) {// - rect.y + margin
                css_data.bottom = parseInt(win_height - rect.y + margin) + 'px';
            } else {
                css_data.top = parseInt(rect.y + rect.height + margin) + 'px';
            }

            OSC_AUTOCOMPLETEPOPOVER_POPUP.removeAttr('style').css(css_data).swapZIndex();
        };

        this._checkRenderPopup = function () {
            if (!this._popupIsRendered()) {
                this._renderPopup();
            }
        };

        this._removePopup = function () {
            OSC_AUTOCOMPLETEPOPOVER_POPUP.remove();
            OSC_AUTOCOMPLETEPOPOVER_POPUP = null;

            $(window).unbind('.autocompletepopover');
            $(document).unbind('.autocompletepopover');
        };

        this._renderPopup = function (filter) {
            var $this = this;

            if (OSC_AUTOCOMPLETEPOPOVER_POPUP === null) {
                OSC_AUTOCOMPLETEPOPOVER_POPUP = $('<div />').addClass('autocompletepopover-popup').appendTo(document.body);

                $(window).unbind('.autocompletepopover').bind('resize.autocompletepopover', function (e) {
                    $this._positionPopup();
                }).bind('scroll.autocompletepopover', function (e) {
                    $this._positionPopup(e);
                });

                $(document).unbind('.autocompletepopover').bind('mousedown.autocompletepopover', function (e) {
                    if (e.target && ($(e.target).hasClass('autocompletepopover-popup') || e.target.hasAttribute('data-autocomplete-popover') || $(e.target).closest('.autocompletepopover-popup')[0] || $(e.target).closest('[data-autocomplete-popover]')[0])) {
                        return;
                    }

                    $this._removePopup();
                });
            }

            OSC_AUTOCOMPLETEPOPOVER_POPUP.attr('data-id', this._id).html('');

            if (this.data === null) {
                $('<div />').addClass('loading').text('Loading...').prepend($.renderIcon('preloader')).appendTo(OSC_AUTOCOMPLETEPOPOVER_POPUP);
            } else {
                this._filterAndRenderList(filter);
            }

            this._positionPopup();
        };

        this._filterAndRenderList = function (filter) {
            var $this = this;

            this._data_index = -1;

            OSC_AUTOCOMPLETEPOPOVER_POPUP.html('');

            var list = $('<div />').addClass('item-list').appendTo(OSC_AUTOCOMPLETEPOPOVER_POPUP);

            var filter_value = filter ? this._input.val().trim().toLowerCase() : '';

            var matched = false;

            this.data.forEach(function (item, idx) {
                if (filter_value !== '') {
                    var title = item.title.toLowerCase();

                    var string_pos = title.indexOf(filter_value);

                    if (string_pos < 0 || (string_pos > 0 && title.substr(string_pos - 1, 1) !== ' ')) {
                        return;
                    }

                    if (title === filter_value) {
                        matched = true;
                    }
                }

                let item_div =  $('<div />').addClass('item').attr('data-idx', idx);
                item.id ? item_div.attr('data-id', item.id) : null;
                item_div.text(item.title).appendTo(list);
            });

            if (!matched && filter_value !== '') {
                $('<div />').addClass('item item-add').attr('data-idx', -1).text(this._input.val().trim()).prepend($('<span />').text('Add')).prepend($.renderIcon('plus')).prependTo(list);
            }

            OSC_AUTOCOMPLETEPOPOVER_POPUP.find('.item').click(function () {
                $this._data_index = this.getAttribute('data-idx');
                $this._setValueBySelectedItem();
            });

            if (!OSC_AUTOCOMPLETEPOPOVER_POPUP.find('.item')[0]) {
                this._removePopup();
            }
        };

        this._selectItem = function (flag) {
            this._checkRenderPopup();

            if (!this._popupIsRendered()) {
                return;
            }

            if (typeof flag === 'undefined') {
                this._data_index = OSC_AUTOCOMPLETEPOPOVER_POPUP.find('.item').first().attr('data-idx');
            } else {
                var item = this._getSelectedNode();

                if (!item) {
                    this._data_index = OSC_AUTOCOMPLETEPOPOVER_POPUP.find('.item').first().attr('data-idx');
                } else if (flag) {

                    if (item[0].nextSibling) {
                        this._data_index = item[0].nextSibling.getAttribute('data-idx');
                    }
                } else {
                    if (item[0].previousSibling) {
                        this._data_index = item[0].previousSibling.getAttribute('data-idx');
                    }
                }
            }

            OSC_AUTOCOMPLETEPOPOVER_POPUP.find('.item').removeClass('selected').filter('[data-idx="' + this._data_index + '"]').addClass('selected');

            var node = this._getSelectedNode();

            if (node) {
                var node_rect = node[0].getBoundingClientRect();
                var popup_rect = OSC_AUTOCOMPLETEPOPOVER_POPUP[0].getBoundingClientRect();

                if ((node_rect.y + node_rect.height) > (popup_rect.y + popup_rect.height)) {
                    OSC_AUTOCOMPLETEPOPOVER_POPUP.scrollTop(node.position().top - popup_rect.height + node_rect.height);
                } else if (node_rect.y < popup_rect.y) {
                    OSC_AUTOCOMPLETEPOPOVER_POPUP.scrollTop(node.position().top);
                }
            }
        };

        this._setValueBySelectedItem = function () {
            var selected_item = this._getSelectedItem();

            if (selected_item) {
                this._input.val(selected_item.value).attr('select_item_id', selected_item.id ? selected_item.id : 0);
            } else {
                this._input.attr('select_item_id', 0)
            }

            if (typeof this.select_callback === 'function') {
                this.select_callback(this._input.val(), this._input, this._input.attr('select_item_id'));
            }

            this._removePopup();
        };

        this._getSelectedNode = function () {
            if (!this._popupIsRendered()) {
                return null;
            }

            var node = OSC_AUTOCOMPLETEPOPOVER_POPUP.find('.item[data-idx="' + this._data_index + '"]');

            return node[0] ? node : null;
        };

        this._getSelectedItem = function () {
            var node = this._getSelectedNode();

            return (node && parseInt(node.attr('data-idx')) >= 0) ? this.data[node.attr('data-idx')] : null;
        };

        this._loadData = function () {
            var $this = this;

            if (this.source_loading) {
                return;
            }

            this.source_loading = true;

            var __callback = function (data) {
                $this.source_loading = false;

                if (!Array.isArray(data)) {
                    if (data !== null && typeof data === 'object') {
                        var buff = [];

                        $.each(data, function (k, v) {
                            buff.push([k, v + '']);
                        });

                        data = buff;
                    } else {
                        data = [];
                    }
                }

                $this.data = [];

                if(data[0]['type']) {
                    data = data[1]['data'];

                    data.forEach(function (item) {
                        item = [item.id, item.value];
                        $this.data.push({value: item[1], title: item[1], id: item[0]});
                    });
                } else {
                    data.forEach(function (item) {
                        if (Array.isArray(item)) {

                            if (typeof item[0] === 'undefined') {
                                return;
                            }

                            item = [item[0], (typeof item[1] !== 'undefined') ? item[1] : item[0]];
                        } else if (item !== null && typeof item === 'object') {
                            if (typeof item.value === 'undefined') {
                                return;
                            }

                            item = [item.value, (typeof item.title !== 'undefined') ? item.title : item.value];
                        } else {
                            item = item + '';

                            if (item === '') {
                                return;
                            }

                            item = [item, item];
                        }

                        $this.data.push({value: item[0], title: item[1]});
                    });
                }

                if (OSC_AUTOCOMPLETEPOPOVER_POPUP !== null && OSC_AUTOCOMPLETEPOPOVER_POPUP.attr('data-id') === $this._id) {
                    $this._renderPopup();
                }
            };

            if (this.source_url) {
                $.ajax({
                    url: this.source_url,
                    success: function (response) {
                        if (response.result !== 'OK') {
                            $this.source_loading = false;
                            return;
                        }

                        __callback(response.data);
                    }
                });
            } else if (typeof this.source_callback === 'function') {
                this.source_callback.apply(this, [__callback]);
            } else {
                __callback([]);
            }
        };

        this._popupIsRendered = function () {
            return OSC_AUTOCOMPLETEPOPOVER_POPUP !== null && OSC_AUTOCOMPLETEPOPOVER_POPUP.attr('data-id') === this._id;
        };

        this._initEvent = function () {
            var $this = this;

            this._container.find('[data-autocomplete-popover-toggler]').each(function () {
                $(this).unbind('.autocompletepopover').bind('click.autocompletepopover', function () {
                    $this._input[0].focus();

                    if ($this._popupIsRendered()) {
                        $this._removePopup();
                    } else {
                        $this._renderPopup();
                    }
                });
            });

            this._input.unbind('.autocompletepopover').bind('focus.autocompletepopover', function () {
                if ($this.data === null) {
                    $this._loadData();
                }
            }).bind('keyup.autocompletepopover', function (e) {
                if ([13, 38, 40].indexOf(e.keyCode) >= 0) {
                    return;
                }

                $this._renderPopup(true);
                $this._selectItem();
            }).bind('keydown.autocompletepopover', function (e) {
                if (e.keyCode === 13) { //ENTER
                    if ($this._popupIsRendered()) {
                        e.preventDefault();

                        $this._setValueBySelectedItem();
                    }
                } else if (e.keyCode === 40) { //DOWN
                    $this._selectItem(true);
                    return;
                } else if (e.keyCode === 38) { //UP
                    $this._selectItem(false);
                    return;
                } else {
                    $this._data_index = -1;
                }
            });
        };

        this.setData = function (data) {
            this.data = data;
        };

        this.data = null;
        this.source_callback = null;
        this.source_url = null;
        this.source_loading = false;
        this.select_callback = null;
        this.max_height = null;
        this._id = null;
        this._data_index = -1;
        this._container = null;
        this._input = null;

        this._initialize(node, options);
    }

    $.fn.osc_autocompletePopover = function () {
        var func = null;

        if (arguments.length > 0 && typeof arguments[0] === 'string') {
            func = arguments[0];
        }

        if (func) {
            var opts = [];

            for (var x = 1; x < arguments.length; x++) {
                opts.push(arguments[x]);
            }
        } else {
            opts = arguments[0];
        }

        return this.each(function () {
            if (func) {
                var instance = $(this).data('osc-autocompletePopover');
                instance[func].apply(instance, opts);
            } else {
                $(this).data('osc-autocompletePopover', new OSC_AutocompletePopover(this, opts));
            }
        });
    };

    window.initAutoCompletePopover = function () {
        var options = {};

        try {
            options = JSON.parse(this.getAttribute('data-autocompletepopover-config'));
        } catch (e) {
        }

        $(this).osc_autocompletePopover(options);
    };

})(jQuery);