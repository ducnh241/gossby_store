(function ($) {
    'use strict';

    function OSC_Item_Selector(container, config) {
        this._initialize = function (config) {
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

            var $this = this;

            if (this._container instanceof HTMLElement) {
                this._container = $(this._container);
            }

            this._container.html('').addClass('catalog-item-selector osc-item-selector');

            var browser = $('<div />').addClass('item-browser').appendTo($('<div />').addClass('catalog-item-browser osc-item-selector__browser').appendTo(this._container));

            $('<ins />').append($.renderIcon('search')).appendTo(browser);
            $('<input />').attr({type: 'text', placeholder: this.placeholder_text}).appendTo(browser);
            $('<div />').attr('data-browser-toggler', 1).text('Browse').appendTo(browser);

            this._selected_list = $('<div />').addClass('catalog-selected-list osc-item-selector__selected-list').attr('data-no-selected', this.no_selected_text).appendTo(this._container);

            $.each(['selected_item_render_callback', 'selected_checker', 'click_callback', 'click_handler'], function (k, f) {
                if (typeof $this[f] === 'string') {
                    eval('$this[f] = ' + $this[f]);
                }
            });

            browser.osc_ui_itemBrowser({
                browse_url: this.browse_url,
                extend_params: this.extend_params,
                filter_in_result: this.filter_in_result,
                attributes: this.attributes,
                attributes_extend: this.attributes_extend,
                click_callback: function (list_item, item) {
                    $this._itemClick(list_item, item, this);
                },
                item_render_callback: function (list_item, item) {
                    list_item.attr('data-item', item.id).addClass('catalog-browsed-item osc-item-selector__selected-item');

                    var checkbox = $('<input />').attr({type: 'checkbox', tabIndex: -1}).prependTo($('<div />').addClass('styled-checkbox').append($('<ins />').append($.renderIcon('check-solid'))).appendTo($('<div />').addClass('checker').prependTo(list_item)));

                    var is_selected = $this._itemIsSelected(list_item, item);

                    checkbox[0].checked = is_selected ? true : false;

                    if (is_selected === null) {
                        checkbox.attr('data-indeterminate', 1);
                    }

                    checkbox.mousedown(function (e) {
                        e.preventDefault();
                    });
                }
            });

            if (Array.isArray(this.data)) {
                this.data.forEach(function (item) {
                    $this._itemClick(null, item, browser.osc_ui_itemBrowser('getInstance'));
                });
            }
        };

        this._itemClick = function (list_item, item, browser) {
            if (list_item) {
                var checkbox = list_item.find('input[type="checkbox"]').removeAttr('data-indeterminate')[0];
                checkbox.checked = !this._itemIsSelected(list_item, item);
            }

            if (typeof this.click_handler === 'function') {
                this.click_handler.apply(this, [list_item, item, browser]);
                return;
            }

            if (!this.multi_select) {
                this._selected_list.find('> [data-item]').each(function () {
                    if (this.getAttribute('data-item') !== (item.id + '')) {
                        $(this).trigger('remove');
                    }
                });
            }

            if (list_item) {
                if (!checkbox.checked) {
                    this._selected_list.find('> [data-item="' + item.id + '"]').trigger('remove');
                    return;
                }
            }

            var selected_list_item = this._renderSelectedItem(list_item, item, browser);

            if (typeof this.click_callback === 'function') {
                this.click_callback.apply(this, [checkbox.checked, selected_list_item, list_item, item, browser]);
                return;
            }
        };

        this._itemIsSelected = function (list_item, item) {
            if (typeof this.selected_checker === 'function') {
                return this.selected_checker.apply(this, [item]);
            }

            return this._selected_list.find('> [data-item="' + item.id + '"]')[0] ? true : false;
        };

        this._renderSelectedItem = function (list_item, item, browser) {
            if (this._selected_list.find('> [data-item="' + item.id + '"]')[0]) {
                return this._selected_list.find('> [data-item="' + item.id + '"]').first();
            }

            var $this = this;

            var node = $('<div />').addClass('catalog-selected-item').attr('data-item', item.id).appendTo(this._selected_list);

            node.bind('remove', function () {
                node.remove();

                var browser_rendered_items = browser.getRenderedItems();

                if (browser_rendered_items) {
                    var browser_item = browser_rendered_items.filter('[data-item="' + node.attr('data-item') + '"]');

                    if (browser_item[0]) {
                        browser_item.find('input')[0].checked = false;
                    }
                }

                if (typeof $this.click_callback === 'function') {
                    $this.click_callback.apply($this, [false, node, list_item, item, browser]);
                    return;
                }
            });

            if (typeof this.input_name === 'string') {
                $('<input />').attr({type: 'hidden', name: this.input_name, value: item.id}).appendTo(node);
            }

            if (typeof this.selected_item_render_callback === 'function') {
                this.selected_item_render_callback(node, item);
                if (!this.alway_render_seleted_item) {
                    return;
                }
            }

            var $this = this;

            browser.attributes.forEach(function (config) {
                if (typeof item[config.key] === 'undefined' && !config.force_render) {
                    return;
                }

                if (typeof config.render === 'function') {
                    var attr_item = config.render.apply($this, [item, true]);

                    if ((attr_item instanceof HTMLElement) || (attr_item instanceof jQuery)) {
                        node.append(attr_item);
                    }
                } else {
                    var attr_item = $('<div />').appendTo(node);

                    if (typeof config.class === 'string') {
                        attr_item.addClass(config.class);
                    }

                    if (config.css && typeof config.css === 'object') {
                        attr_item.css(config.css);
                    }

                    if (typeof item[config.key] === 'undefined') {
                        return;
                    }

                    if (typeof config.type === 'undefined') {
                        config.type = 'text';
                    }

                    if (config.type === 'image-background') {
                        attr_item.css('background-image', 'url(' + item[config.key] + ')');
                    } else if (config.type === 'icon') {
                        attr_item.append($.renderIcon(item[config.key]));
                    } else {
                        attr_item.text(item[config.key]);
                    }
                }
            });

            $('<div />').addClass('remove-btn').appendTo(node).click(function () {
                node.trigger('remove');
            });

            return node;
        };

        this.multi_select = true;
        this.alway_render_seleted_item = false;
        this.input_name = 'selected_item[]';
        this.filter_in_result = false;
        this.placeholder_text = 'Search for items';
        this.no_selected_text = 'Don\'t have any item selected';
        this.attributes = null;
        this.attributes_extend = null;
        this.browse_url = '';
        this.extend_params = null;
        this.selected_item_render_callback = null;
        this.selected_checker = null;
        this.click_callback = null;
        this.click_handler = null;
        this.data = [];
        this._container = container;
        this._selected_list = null;

        this._initialize(config);
    }

    $.fn.osc_itemSelector = function () {
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
                var instance = $(this).data('osc-itemSelector');
                instance[func].apply(instance, opts);
            } else {
                $(this).data('osc-itemSelector', new OSC_Item_Selector(this, opts));
            }
        });
    };

    window.initItemSelector = function () {
        var options = {};

        try {
            options = JSON.parse(this.getAttribute('data-selector-config'));
        } catch (e) {
        }

        $(this).osc_itemSelector(options);
    };
})(jQuery);