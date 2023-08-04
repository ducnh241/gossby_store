(function ($) {
    'use strict';

    var POPUP = null;

    function OSC_UI_ItemBrowser(node, options) {
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

            var $this = this;

            if (!Array.isArray(this.attributes)) {
                this.attributes = this._attributes;
            }

            if (Array.isArray(this.attributes_extend)) {
                this.attributes_extend.forEach(function (attr) {
                    $this.attributes.push(attr);
                });
            }

            this.attributes = this.attributes.map(function (config) {
                if (typeof config === 'string') {
                    config = {key: config};
                }

                return config;
            });
            this.attributes = this.attributes.filter(function (config) {
                return config && typeof config === 'object' && typeof config.key === 'string' && config.key !== '';
            });
            this.attributes.sort(function (a, b) {
                a.position = parseFloat(a.position);
                b.position = parseFloat(b.position);

                if (isNaN(a.position)) {
                    a.position = 1;
                }

                if (isNaN(b.position)) {
                    b.position = 1;
                }

                return a.position - b.position;
            });

            $.each({min_height: {min: 200, default: 200}, page_size: {min: 10, max: 100, default: 25}, page_section: {min: 1, max: 10, default: 3}}, function (name, data) {
                if (data === null) {
                    data = {default: 0};
                } else if (typeof data === 'number') {
                    data = {default: data};
                }

                $this[name] = parseInt($this[name]);

                if (isNaN($this[name])) {
                    $this[name] = data.default;
                } else if (typeof data.min !== 'undefined' && $this[name] < data.min) {
                    $this[name] = data.min;
                } else if (typeof data.max !== 'undefined' && $this[name] > data.max) {
                    $this[name] = data.max;
                }
            });

            $.each(['keywords_cleaner', 'item_render_callback', 'item_render', 'click_callback', 'items_render_callback', 'after_response_callback', 'filter_callback'], function (k, f) {
                if (typeof $this[f] === 'string') {
                    eval('$this[f] = ' + $this[f]);
                }
            });

            this._id = $.makeUniqid();

            this._container = $(node);

            if (this._container[0].nodeName.toLowerCase() === 'input' && this._container.attr('type') === 'text') {
                this._input = this._container;
            } else {
                this._input = this._container.find('input[type="text"]');
            }

            this._input.attr({autocomplete: 'off'});

            this._container.attr('data-item-browser', this._id);

            this._initEvent();
        };

        this._positionPopup = function () {
            if (POPUP === null || POPUP.attr('data-id') !== this._id) {
                return;
            }

            var margin = 10;

            var win_height = $(window).height();

            var rect = this._container[0].getBoundingClientRect();

            var css_data = {width: parseInt(rect.width) + 'px', left: parseInt(rect.x + $(window).scrollLeft()) + 'px'};

            var top_spacing = parseInt(rect.y) - (margin * 2);
            var bottom_spacing = parseInt(win_height - (rect.y + rect.height)) - (margin * 2);

            var height = POPUP[0].getBoundingClientRect().height;

            var item_list = POPUP.find('.item-list')[0];

            if (item_list) {
                var item_list_height = 0;

                for (var i = 0; i < item_list.childNodes.length; i++) {
                    item_list_height += item_list.childNodes[0].getBoundingClientRect().height;
                }

                if (height > this.max_height && height > bottom_spacing) {
                    height += item_list_height - item_list.getBoundingClientRect().height;

                    var max_height = Math.min(this.max_height, height, Math.max(bottom_spacing, this.min_height));

                    if (max_height > bottom_spacing && top_spacing > bottom_spacing) {
                        max_height = Math.min(this.max_height, height, Math.max(top_spacing, this.min_height));
                    }

                    height = max_height;

                    $(item_list).css('max-height', Math.ceil(height - POPUP[0].getBoundingClientRect().height + item_list.getBoundingClientRect().height) + 'px');
                }
            }

            if (height > bottom_spacing && top_spacing > bottom_spacing) {
                css_data.bottom = parseInt(win_height - rect.y + margin) + 'px';
            } else {
                css_data.top = parseInt(rect.y + rect.height + margin) + 'px';
            }

            POPUP.removeAttr('style').css(css_data).swapZIndex();
        };

        this._popupIsRendered = function () {
            return POPUP !== null && POPUP.attr('data-id') === this._id;
        };

        this._removePopup = function () {
            POPUP.remove();
            POPUP = null;

            $(window).unbind('.itemBrowser');
            $(document).unbind('.itemBrowser');
        };

        this._renderPopup = function () {
            if (this._popupIsRendered()) {
                return;
            }

            var $this = this;

            this._current_index = -1;

            if (POPUP === null) {
                POPUP = $('<div />').addClass('item-browser-popup').appendTo(document.body);

                $(window).unbind('.itemBrowser').bind('resize.itemBrowser', function (e) {
                    $this._positionPopup();
                }).bind('scroll.itemBrowser', function (e) {
                    $this._positionPopup(e);
                });

                $(document).unbind('.itemBrowser').bind('mousedown.itemBrowser', function (e) {
                    if (e.target && ($(e.target).hasClass('item-browser-popup') || e.target.hasAttribute('data-item-browser') || $(e.target).closest('.item-browser-popup')[0] || $(e.target).closest('[data-item-browser]')[0])) {
                        return;
                    }

                    $this._removePopup();
                });
            }

            POPUP.removeClass('small');

            if (this._container.hasClass('small')) {
                POPUP.addClass('small');
            }

            POPUP.attr('data-id', this._id);
        };

        this._renderLoading = function () {
            this._renderPopup();

            POPUP.html('').removeAttr('data-verify-key');

            $('<div />').addClass('loading').text('Loading...').prepend($.renderIcon('preloader')).appendTo(POPUP);
            this._positionPopup();
        };

        this._renderItems = function () {
            if (!this._browsed_data) {
                return this._browse();
            }

            var $this = this;

            this._renderPopup();

            this._current_page = this._browsed_data.current_page;

            var verify_key = $.md5(this._id + JSON.stringify(this._browsed_data) + ':' + ((this.filter_in_result && this._browsed_data.items.length > 0) ? this._input.val().trim().toLowerCase() : ''));

            if (POPUP.attr('data-verify-key') === verify_key) {
                return;
            }

            POPUP.html('').attr('data-verify-key', verify_key);

            this._current_index = -1;

            this._input.focus();

            if (this._browsed_data.items.length > 0) {
                var container = $('<div />').addClass('item-list-wrap').appendTo(POPUP);

                var pagination = buildPager(this._browsed_data.current_page, this._browsed_data.total, this._browsed_data.page_size, {section: this.page_section, small: true});

                var list = $('<div />').addClass('item-list').appendTo(container).keydown(function (e) {
                    if (e.keyCode === 9) { //Tab
                        $this._input.focus();
                    } else if (e.keyCode === 40) { //DOWN
                        $this._selectItem(true);
                    } else if (e.keyCode === 38) { //UP
                        $this._selectItem(false);
                    } else if (e.keyCode === 39) { //RIGHT
                        var next_page_btn = pagination.find('.current')[0].nextSibling;

                        if (next_page_btn) {
                            $(next_page_btn).findAll('[data-page]').trigger('click');
                        }
                    } else if (e.keyCode === 37) { //LEFT
                        var prev_page_btn = pagination.find('.current')[0].previousSibling;

                        if (prev_page_btn) {
                            $(prev_page_btn).findAll('[data-page]').trigger('click');
                        }
                    } else {
                        return;
                    }

                    e.preventDefault();
                });

                var filter_value = this.filter_in_result ? this._input.val().trim().toLowerCase() : '';

                this._browsed_data.items.forEach(function (item, index) {
                    if (filter_value !== '') {
                        if (typeof $this.filter_callback === 'function') {
                            if (!$this.filter_callback(item, filter_value)) {
                                return;
                            }
                        } else {
                            var title = item.title.toLowerCase();

                            var string_pos = title.indexOf(filter_value);

                            if (string_pos < 0 || (string_pos > 0 && title.substr(string_pos - 1, 1) !== ' ')) {
                                return;
                            }
                        }
                    }

                    if (typeof $this.item_render === 'function') {
                        var node = $this.item_render.apply($this, [item]);
                    } else {
                        var node = $('<a />').attr('href', 'javascript: void(0)').addClass('item');

                        $this.attributes.forEach(function (config) {
                            if (typeof item[config.key] === 'undefined' && !config.force_render) {
                                return;
                            }

                            if (typeof config.render === 'function') {
                                var attr_item = config.render.apply($this, [item]);

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
                    }

                    if (typeof $this.item_render_callback === 'function') {
                        $this.item_render_callback.apply($this, [node, item]);
                    }

                    node.appendTo(list);

                    $this._initItemEvent(node, item);
                });

                if (typeof this.items_render_callback === 'function') {
                    this.items_render_callback.apply(this, [list]);
                }

                if (pagination) {
                    $('<div />').addClass('pagination-bar p10').append(pagination).appendTo(container);
                    pagination.find('[data-page]:not(.current)').click(function (e) {
                        e.preventDefault();

                        $this._browse(this.getAttribute('data-page'));
                    });
                }
            }

            if (POPUP.find('.item-list > .item').length < 1) {
                $('<div />').addClass('no-result').text('No items were found to display').appendTo(POPUP);
            }

            this._positionPopup();
        };

        this.getRenderedItems = function() {
            return this._popupIsRendered() ? POPUP.find('.item-list > .item') : null;
        };

        this._initItemEvent = function (node, item) {
            var $this = this;

            node.hover(function () {
                var index = 0;

                var sibling = this.previousSibling;

                while (sibling) {
                    index++;
                    sibling = sibling.previousSibling;
                }

                $this._selectItem(index, true);
            }).click(function (e) {
                if (this.getAttribute('disabled') !== 'disabled' && typeof $this.click_callback === 'function') {
                    $this.click_callback.apply($this, [node, item]);
                }
            });
        };

        this._getSelectedNode = function () {
            if (!this._popupIsRendered()) {
                return null;
            }

            var list = POPUP.find('.item-list')[0];

            return (list && list.childNodes[this._current_index]) ? list.childNodes[this._current_index] : null;
        };

        this._selectItem = function (index, skip_check_scroll) {
            this._renderItems();

            if (!this._browsed_data) {
                return;
            }

            if (typeof index === 'boolean') {
                if (index) {
                    this._current_index++;
                } else {
                    if (this._current_index < 0) {
                        this._current_index = POPUP.find('.item-list > .item').length;
                    } else {
                        this._current_index--;
                    }
                }
            } else {
                this._current_index = parseInt(index);

                if (isNaN(this._current_index)) {
                    this._current_index = -1;
                }
            }

            this._current_index = Math.min(POPUP.find('.item-list > .item').length - 1, Math.max(0, this._current_index));

            var node = this._getSelectedNode();

            if (node) {
                if (!skip_check_scroll) {
                    var node_rect = node.getBoundingClientRect();
                    var list_rect = node.parentNode.getBoundingClientRect();

                    var node_coord_y = node.getBoundingClientRect().y - node.parentNode.getBoundingClientRect().y + $(node.parentNode).scrollTop();

                    if ((node_rect.y + node_rect.height) > (list_rect.y + list_rect.height)) {
                        $(node.parentNode).scrollTop(node_coord_y - list_rect.height + node_rect.height);
                    } else if (node_rect.y < list_rect.y) {
                        $(node.parentNode).scrollTop(node_coord_y);
                    }
                }

                $(node).focus();
            }
        };

        this._initEvent = function () {
            var $this = this;

            this._container.find('[data-browser-toggler]').each(function () {
                $(this).unbind('.itemBrowser').bind('click.itemBrowser', function () {
                    if ($this._popupIsRendered()) {
                        $this._removePopup();
                    } else {
                        $this._renderItems();
                        $this._input.focus();
                    }
                });
            });

            this._input.unbind('.itemBrowser').bind('focus.itemBrowser', function (e) {
                if ($this.focus_browse) {
                    $this._renderItems();
                }
            }).bind('keyup.itemBrowser', function (e) {
                clearTimeout($this._browse_timer);

                if ([13, 32].indexOf(e.keyCode) >= 0) { //ENTER & SPACE
                    $this._browse(null, e.keyCode === 13 && !$this.filter_in_result);
                } else {
                    $this._browse_timer = setTimeout(function () {
                        $this._browse();
                    }, 250);
                }
            }).bind('keydown.itemBrowser', function (e) {
                clearTimeout($this._browse_timer);

                if (e.keyCode === 13) {
                    e.preventDefault();
                } else if (e.keyCode === 40) { //DOWN
                    $this._selectItem(true);
                    return;
                } else if (e.keyCode === 38) { //UP
                    $this._selectItem(false);
                    return;
                }
            });
        };

        this._browse = function (page, force) {
            var $this = this;

            if (this.filter_in_result) {
                if (this._browsed_data && !force) {
                    page = parseInt(page);

                    if (isNaN(page) || page === this._current_page) {
                        return this._renderItems();
                    }
                }
            } else {
                var keywords = searchCleanKeywords(this._input.val());

                if (typeof this.keywords_cleaner === 'function') {
                    keywords = this.keywords_cleaner.apply(this, [keywords]);
                }

                if (keywords !== this._last_keywords || force) {
                    if (this._xhr) {
                        this._xhr.abort();
                    }
                } else {
                    if (this._xhr) {
                        return;
                    }

                    if (this._browsed_data) {
                        page = parseInt(page);

                        if (isNaN(page) || page === this._current_page) {
                            return this._renderItems();
                        }
                    }
                }
            }

            var backup_browsed_data = this._browsed_data;

            this._browsed_data = null;

            this._renderLoading();

            var post_data = {};

            if (this.browse_params !== null && typeof this.browse_params === 'object') {
                $.extend(post_data, this.browse_params);
            }

            $.extend(post_data, {keywords: keywords, page: page, page_size: this.page_size});

            if (this.extend_params) {
                let extend_params;

                if (typeof this.extend_params === 'object') {
                    extend_params = this.extend_params;
                } else if (typeof this.extend_params === 'function') {
                    extend_params = this.extend_params();
                }

                if (typeof extend_params === 'object') {
                    $.extend(post_data, extend_params);
                }
            }

            this._xhr = $.ajax({
                type: 'POST',
                url: this.browse_url,
                data: post_data,
                success: function (response) {
                    $this._xhr = null;

                    if (response.result !== 'OK') {
                        alert(response.message);
                    } else {
                        $this._last_keywords = keywords;

                        if (typeof $this.after_response_callback === 'function') {
                            keywords = $this.after_response_callback.apply($this, [response.data]);
                        }
                    }

                    $this._browsed_data = response.result !== 'OK' ? backup_browsed_data : response.data;

                    $this._renderItems();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    if (thrownError.trim().toLowerCase() !== 'abort') {
                        alert('ERROR [#' + xhr.status + ']: ' + thrownError);
                    }

                    $this._renderItems();
                }
            });
        };

        this.setBrowseParams = function (params) {
            this.browse_params = params
        }

        this.browse_url = null;
        this.max_height = 500;
        this.min_height = 300;
        this.page_size = 25;
        this.page_section = 4;
        this.keywords_cleaner = null;
        this.filter_in_result = false;
        this.click_callback = null;
        this.items_render_callback = null;
        this.item_render_callback = null;
        this.after_response_callback = null;
        this.filter_callback = null;
        this.item_render = null;
        this.focus_browse = false;
        this.browse_params = {};
        this.extend_params = null;
        this.attributes = null;
        this.attributes_extend = null;
        this._id = null;
        this._xhr = null;
        this._current_page = 1;
        this._last_keywords = '';
        this._browse_timer = null;
        this._browsed_data = null;
        this._current_index = -1;
        this._attributes = [
            {key: 'image', class: 'image', type: 'image-background', force_render: false, position: 1},
            {key: 'icon', class: 'icon', type: 'icon', force_render: false, position: 2},
            {key: 'title', class: 'title', force_render: false, position: 3}
        ];
        this._container = null;
        this._input = null;

        this._initialize(node, options);
    }

    $.fn.osc_ui_itemBrowser = function () {
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

        if (func) {
            var instance = $(this[0]).data('osc-ui-itemBrowser');

            if (instance) {
                switch (func.toLowerCase()) {
                    case 'getinstance':
                        return instance;
                }
            }
        }

        return this.each(function () {
            if (func) {
                var instance = $(this).data('osc-ui-itemBrowser');
                instance[func].apply(instance, opts);
            } else {
                $(this).data('osc-ui-itemBrowser', new OSC_UI_ItemBrowser(this, opts));
            }
        });
    };

    window.initItemBrowser = function () {
        var options = {};

        try {
            options = JSON.parse(this.getAttribute('data-browser-config'));
        } catch (e) {
        }

        $(this).osc_ui_itemBrowser(options);
    };
})(jQuery);