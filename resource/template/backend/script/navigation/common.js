(function ($) {
    'use strict';

    window.navPoster__initAddItemBtn = function () {
        $(this).click(function () {
            _navPoster__renderItemEditor(null, $(this).parent().find('> .item-list')[0]);
        });
    };

    function _navPoster__initItemDragger(item) {
        var container = item.closest('.navigation-items');
        var item_container = item.closest('.nav-item-container');

        item.find('.dragger').unbind('.dragger').bind('mousedown.dragger', function (e) {
            if (e.which !== 1) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            container.find('.nav-item-container').removeClass('reordering');

            $('.nav-item-dragging').remove();

            var helper = item.clone()
                    .removeAttr('class')
                    .addClass('nav-item-dragging')
                    .css({
                        marginLeft: ((item[0].getBoundingClientRect().x + $(window).scrollLeft()) - e.pageX) + 'px',
                        marginTop: ((item[0].getBoundingClientRect().y + $(window).scrollTop()) - e.pageY) + 'px'
                    }).appendTo(document.body);

            helper.find('> :not(.dragger):not(.title)').remove();

            helper.swapZIndex();

            item_container.addClass('reordering');

            $(document.body).addClass('dragging');

            $(document).unbind('.dragger').bind('mousemove.dragger', function (e) {
                var scroll_top = $(window).scrollTop();
                var scroll_left = $(window).scrollLeft();

                helper.css({top: (e.pageY - scroll_top) + 'px', left: (e.pageX - scroll_left) + 'px'}).css({});

                var container_rect = container[0].getBoundingClientRect();

                if (e.pageX < (container_rect.x + scroll_left) || e.pageX > (container_rect.x + container_rect.width + scroll_left) || e.pageY < (container_rect.y + scroll_top) || e.pageY > (container_rect.y + container_rect.height + scroll_top)) {
                    return;
                }

                var matched = false;

                container.find('.nav-item').each(function () {
                    if (this === item[0] || ((item[0].compareDocumentPosition(this) & Node.DOCUMENT_POSITION_CONTAINED_BY) === Node.DOCUMENT_POSITION_CONTAINED_BY)) {
                        return;
                    }

                    var rect = this.getBoundingClientRect();

                    if (e.pageX >= (rect.x + scroll_left) && e.pageX <= (rect.x + scroll_left + rect.width) && e.pageY >= (rect.y + scroll_top) && e.pageY <= (rect.y + rect.height + scroll_top)) {
                        matched = true;

                        var sibling_container = $(this).closest('.nav-item-container');

                        if (e.pageY < (rect.y + scroll_top + (rect.height / 2))) {
                            item_container.insertBefore(sibling_container);
                        } else {
                            if (e.pageX > ($(this).find('.toggler')[0].getBoundingClientRect().x + scroll_left)) {
                                sibling_container.addClass('toggled');
                                sibling_container.find('> .item-list').prepend(item_container);
                            } else {
                                sibling_container.removeClass('toggled');
                                item_container.insertAfter(sibling_container);
                            }
                        }
                    }

                    if (e.pageY < (rect.y + scroll_top)) {
                        return false;
                    }
                });

                if (!matched) {
                    var main_list = container.find('> .item-list');
                    var main_list_rect = main_list[0].getBoundingClientRect();

                    if (e.pageY <= main_list_rect.y + scroll_top) {
                        main_list.prepend(item_container);
                    } else if (e.pageY >= main_list_rect.y + main_list_rect.height + scroll_top) {
                        main_list.append(item_container);
                    }
                }
            }).bind('mouseup.itemReorder', function (e) {
                $(document).unbind('.dragger');
                $(document.body).removeClass('dragging');
                helper.remove();
                item_container.removeClass('reordering');
                item.trigger('update', [{}]);
            });
        });
    }

    function _navPoster__renderItem(item_list, item_data) {
        if (typeof item_data.id === 'undefined') {
            item_data.id = $.makeUniqid();
        }

        var container = $('<div />').addClass('nav-item-container').appendTo(item_list);

        var item = $('<div />').addClass('nav-item').appendTo(container);

        $('<div />').addClass('dragger').appendTo(item);
        $('<div />').addClass('toggler').append($.renderIcon('caret-down')).appendTo(item).click(function () {
            container.toggleClass('toggled');
        });
        $('<div />').addClass('title').appendTo(item);

        $('<div />').addClass('control').append($('<span />').append($.renderIcon('plus')).click(function () {
            _navPoster__renderItemEditor(null, container.find('> .item-list')[0]);
        })).append($('<span />').append($.renderIcon('pencil')).addClass('ml10').click(function () {
            _navPoster__renderItemEditor(item);
        })).append($('<span />').append($.renderIcon('trash-alt-regular')).addClass('ml10').click(function () {
            container.remove();
        })).appendTo(item);

        $('<div />').addClass('item-list').appendTo(container);

        item.bind('update', function (e, data) {
            $.extend(item_data, data);

            var parent = container.parent().closest('.nav-item-container').find('> .nav-item');

            item_data.parent_id = parent[0] ? parent.find('input[data-field="id"]').val() : '';

            item.find('input[data-field]').remove();

            $.each(item_data, function (k, v) {
                $('<input />').attr({type: 'hidden', 'data-field': k, name: 'items[' + item_data.id + '][' + k + ']', value: v}).appendTo(item);
            });

            item.find('.title').text(item_data.title);
        }).trigger('update', [{}]);

        _navPoster__initItemDragger(item);

        return container;
    }

    window.navPoster__initItems = function () {
        var _render = function (items, list) {
            if (!items) {
                return;
            }

            $.each(items, function (k, item_data) {
                var children = item_data.children;

                delete item_data.children;

                _render(children, _navPoster__renderItem(list, item_data).find('> .item-list'));
            });
        };

        _render(JSON.parse(this.getAttribute('data-items')), $(this));
    };

    function _navPoster__renderItemEditor(item, item_list) {
        $.unwrapContent('navItemEditor');

        var modal = $('<div />').addClass('osc-modal').width(400);

        var header = $('<header />').appendTo(modal);

        $('<div />').addClass('title').html(item ? 'Edit item' : 'Add new item').appendTo($('<div />').addClass('main-group').appendTo(header));

        $('<div />').addClass('close-btn').click(function () {
            $.unwrapContent('navItemEditor');
        }).appendTo(header);

        var modal_body = $('<div />').addClass('body post-frm').appendTo(modal);

        // create title input
        var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

        var cell = $('<div />').appendTo(row);

        var id = $.makeUniqid();

        $('<label />').attr('for', id + '-title').html('Item title').appendTo(cell);

        var title_input = $('<input />').attr({type: 'text', id: id + '-title'}).addClass('styled-input').appendTo($('<div />').appendTo(cell));

        // create custom class input
        var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

        var cell = $('<div />').appendTo(row);

        var id = $.makeUniqid();

        $('<label />').attr('for', id + '-class').html('Add custom class').appendTo(cell);

        var custom_class_input = $('<input />').attr({type: 'text', id: id + '-class', placeholder: 'Add "special-navigation" for special button'}).addClass('styled-input').appendTo($('<div />').appendTo(cell));

        // create item url input
        var row = $('<div />').addClass('frm-grid').appendTo(modal_body);

        var cell = $('<div />').appendTo(row);

        $('<label />').attr('for', id + '-search').html('Item URL').appendTo(cell);

        var url_container = $('<div />').addClass().appendTo(cell);

        var url_search_input = $('<input />').attr({type: 'text', id: id + '-search'}).addClass('styled-input small').appendTo(url_container);

        let label_image_input = $('<div />').addClass('mt15').append('<label>Item image</label>').appendTo(modal_body);

        let navigation_image_value = '';
        let navigation_data_image = '';
        if (item) {
            navigation_image_value = item.find('input[data-field="image"]').val();
            navigation_data_image = navigation_image_value ? $.getImgStorageUrl(navigation_image_value) : '';
        }
        var item_image_frm = $('<div />').attr({
            'data-insert-cb': 'initPostFrmSidebarImageUploader',
            'data-upload-url': $.base_url + '/navigation/backend/uploadImage/hash/' + OSC_HASH,
            'data-input': 'image',
            'data-image': navigation_data_image,
            'data-value': navigation_image_value
        });
        label_image_input.append(item_image_frm);
        $('<div />').addClass('frm-grid frm-image-uploader').append(item_image_frm).appendTo(modal_body);

        var action_bar = $('<div />').addClass('action-bar').appendTo(modal);

        $('<button />').addClass('btn btn-outline').html('Close').click(function () {
            $.unwrapContent('navItemEditor');
        }).appendTo(action_bar);

        $('<button />').addClass('btn btn-primary ml10').html(item ? 'Apply changes' : 'Add').click(function () {
            var title = title_input.val().trim();
            var custom_class = custom_class_input.val().trim();
            let image = item_image_frm.find("input[name='image']").val();

            if (!title) {
                alert('You need enter title for the item');
                return;
            }

            if (!url_container.attr('data-item')) {
                alert('You need select a link for the item');
                return;
            }

            var item_data = JSON.parse(url_container.attr('data-item'));

            item_data.title = title;
            item_data.custom_class = custom_class;
            item_data.image = typeof image !== 'undefined' ? image : '';

            $.unwrapContent('navItemEditor');

            if (item) {
                item.trigger('update', [item_data]);
            } else {
                _navPoster__renderItem(item_list, item_data);
            }
        }).appendTo(action_bar);

        $.wrapContent(modal, {key: 'navItemEditor'});

        modal.moveToCenter().css('top', '100px');

        var __applyItem = function (data) {
            url_search_input.hide();

            if (!data.source_icon || !data.source_title) {
                data.source_icon = 'external-link-alt-regular';
                data.source_title = data.url;
            }

            url_container.attr('data-item', JSON.stringify(data));

            $('<div />').text(data.source_title).prepend($.renderIcon(data.source_icon)).append($('<ins />').click(function () {
                __removeItem();
            })).addClass('nav-item-preview').appendTo(url_container);

            if (title_input.val().trim() === '') {
                title_input.val(data.source_title.trim());
            }

            title_input.focus().select();
        };

        var __removeItem = function () {
            url_container.removeAttr('data-item').find('> .nav-item-preview').remove();
            url_search_input.val('').show().focus();
        };

        if (item) {
            title_input.val(item.find('input[data-field="title"]').val());
            custom_class_input.val(item.find('input[data-field="custom_class"]').val());

            var item_data = {};

            $.each(['source_icon', 'source_title', 'url', 'image'], function (k, name) {
                item_data[name] = item.find('input[data-field="' + name + '"]').val();
            });

            __applyItem(item_data);
        }

        url_search_input.osc_ui_itemBrowser({
            focus_browse: true,
            browse_url: $('.navigation-items').attr('data-browse-url'),
            page_section: 2,
            process_custom_link: function (data) {
                if (this.nav_item_type) {
                    return;
                }

                var value = this._input.val().trim();

                if (this._browsed_data) {
                    for (var i = this._browsed_data.items.length - 1; i >= 0; i--) {
                        if (this._browsed_data.items[i].custom_link_flag) {
                            this._browsed_data.items.splice(i, 1);
                        }
                    }
                }

                if ((/^((ht|f)tps?:\/\/|\/|\#|javascript:).+$/i).test(value)) {
                    var item = {
                        icon: 'external-link-alt-regular',
                        title: value,
                        url: value,
                        custom_link_flag: true
                    };

                    if (data) {
                        data.items.unshift(item);
                    } else if (this._browsed_data) {
                        this._browsed_data.items.unshift(item);
                    }
                }
            },

            keywords_cleaner: function (keywords) {
                if (this.nav_item_type) {
                    return keywords;
                }

                this.process_custom_link();

                return '';
            },
            click_callback: function (item, item_data) {
                if (!this.nav_item_type) {
                    this.nav_item_type = {
                        icon: item_data.icon,
                        title: item_data.title,
                        root_item: item_data.root_item
                    };

                    if (typeof item_data.url !== 'string') {
                        this.browse_url = item_data.browse_url;
                        this._browse(1, true);
                    }
                }

                if (typeof item_data.url === 'string') {
                    __applyItem({
                        url: item_data.url,
                        source_icon: this.nav_item_type.icon,
                        source_title: item_data.title
                    });

                    this.nav_item_type = null;
                    this.browse_url = $('.navigation-items').attr('data-browse-url');
                    this._browsed_data = null;

                    return this._removePopup();
                }
            },
            items_render_callback: function (list) {
                if (!this.nav_item_type) {
                    return;
                }

                var $this = this;

                $('<div />').addClass('nav-item-back-to-main-btn').text('Back').prepend($.renderIcon('angle-left-solid')).insertBefore(list).click(function () {
                    $this.nav_item_type = null;
                    $this.browse_url = $('.navigation-items').attr('data-browse-url');

                    $this._browse(1, true);
                });
            },
            after_response_callback: function (data) {
                if (!this.nav_item_type) {
                    this.process_custom_link(data);
                } else if (this.nav_item_type.root_item) {
                    data.items.unshift(this.nav_item_type.root_item);
                }
            }
        });
    }

})(jQuery);