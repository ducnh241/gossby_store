(function ($) {
    'use strict';

    if (!window.OSC_EDITOR_ROOT_PATH) {
        var scripts = document.getElementsByTagName('script');
        var last_script = scripts[scripts.length - 1];

        var js_src = last_script.src;

        if (!js_src || !(/\/+editor\.js(\?.*)?$/i).test(js_src)) {
            console.error(new Error('Cannot detect editor script folder'));
            return;
        }

        window.OSC_EDITOR_ROOT_PATH = js_src.replace(/^(.+)\/+editor\.js(\?.*)?$/i, '$1');

        var res_ver_matches = js_src.match(/(\?|&)v=(\d+)([&#]|$)/);

        if (Array.isArray(res_ver_matches) && res_ver_matches[2]) {
            window.OSC_EDITOR_VERSION = res_ver_matches[2];
        }
    }

    var OSC_EDITOR_ICON_MAP = null;

    function OSC_Editor(node, config) {
        this._initialize = function (node, config) {
            if (OSC_EDITOR_ICON_MAP === null) {
                OSC_EDITOR_ICON_MAP = {};

                $.ajax({
                    type: 'get',
                    url: window.OSC_EDITOR_ROOT_PATH + '/sprites.svg' + (window.OSC_EDITOR_VERSION ? ('?v=' + window.OSC_EDITOR_VERSION) : ''),
                    success: function (response) {
                        var symbols = response.documentElement.getElementsByTagName('symbol');

                        for (var i = 0; i < symbols.length; i++) {
                            var symbol = symbols[i];
                            OSC_EDITOR_ICON_MAP[symbol.getAttribute('id')] = symbol.getAttribute('viewBox');
                            symbol.setAttribute('id', 'osc-editor-' + symbol.getAttribute('id'));
                        }

                        var res_container = document.createElement("div");
                        res_container.innerHTML = new XMLSerializer().serializeToString(response.documentElement);
                        document.body.insertBefore(res_container, document.body.firstChild);

                        var icons = document.body.querySelectorAll('svg[data-icon^="osc-editor-"]');

                        if (icons.length > 0) {
                            for (var i = 0; i < icons.length; i++) {
                                var icon = icons[i];

                                var name = icon.getAttribute('data-icon').replace(/^osc-editor-/, '');

                                if (typeof OSC_EDITOR_ICON_MAP[name] !== 'undefined') {
                                    icon.setAttribute('viewBox', OSC_EDITOR_ICON_MAP[name]);
                                }
                            }
                        }
                    }
                });
            }

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

            this._editor = $(node);

            this._editor.data('osc-editor', this);

            this._editbox = $('<div />').addClass('osc-editor clearfix').insertBefore(node);

            this._editbox.append(node);

            this._editarea = node.nodeName.toLowerCase() === 'textarea' ? $('<div />').appendTo(this._editbox) : this._editor;

            this._editarea.addClass('edit-area osc-editor-style');

            if (node.nodeName.toLowerCase() === 'textarea') {
                this._textarea = this._editor;

                if (!this.value) {
                    this.value = this._textarea.val();
                }
            } else if (!this.value) {
                this.value = this._editarea.html();
            }

            this.scroller_modal =  $('.osc-wrap');

            this.scroller = this.scroller_modal.length > 0 ? this.scroller_modal : $(this.scroller ? this.scroller : window);

            this._element_packers.hr = {
                callback: this._hrPackElement
            };

            if (Array.isArray(this.plugins)) {
                for (var x = 0; x < this.plugins.length; x++) {
                    this._applyPlugin(this.plugins[x]);
                }
            }

            var pairs = {block: '_allowed_block_element_names', textBlock: '_text_block_element_names'};

            for (var k in pairs) {
                for (var i = 0; i < this[pairs[k]].length; i++) {
                    var node_name = this[pairs[k]][i];

                    if (typeof this._node_allowed_style[k] !== 'undefined') {
                        if (typeof this._node_allowed_style[node_name] === 'undefined') {
                            this._node_allowed_style[node_name] = [];
                        }

                        this._node_allowed_style[node_name] = this._node_allowed_style[node_name].concat(this._node_allowed_style[k]).unique();
                    }

                    if (typeof this._node_allowed_attrs[k] !== 'undefined') {
                        if (typeof this._node_allowed_attrs[node_name] === 'undefined') {
                            this._node_allowed_attrs[node_name] = [];
                        }

                        this._node_allowed_attrs[node_name] = this._node_allowed_attrs[node_name].concat(this._node_allowed_attrs[k]).unique();
                    }
                }
            }

            this._render();

            this.setup();

            if (this.value) {
                this.setContent(this.value);
            }

            if (this.set_focus) {
                this._checkFocus();
            }
        };

        this.focus = function () {
            this._checkFocus();
        };

        this._applyPlugin = function (plugin) {
            if (typeof plugin.extends === 'object' && plugin.extends !== null) {
                $.extend(this, plugin.extends);
            }

            if (typeof plugin.initialize === 'function') {
                plugin.initialize.apply(this);
            }
        };

        this._render = function () {
            this._editarea.attr('contentEditable', 'true');
            this._editarea.attr('spellcheck', 'false');

            if (this.inline_mode) {
                this._inlineMode_renderControl();
            } else {
                this._editbox.attr('box-mode', 1);
                this._boxMode_renderControl();
            }
        };

        this._inline_popupbar = null;
        this._inline_sidebar = null;
        this.inline_sidebar_block_command_data = 'ul ol | quote code | heading';
        this.inline_sidebar_element_command_data = 'hr block_image textbox';
        this.inline_popupbar_command_data = 'bold italic underline | anchor link | align_left align_center align_right | textColor highlight';

        this._inlineMode_tryDisplayPopupbar = function () {
            var selection = window.getSelection();

            if (selection.isCollapsed) {
                this._inline_popupbar.hide();
                return;
            }

            var start_node = this._getSelectionNode();
            var end_node = this._getSelectionNode(true);

            if ((start_node && !start_node.isContentEditable) || (end_node && !end_node.isContentEditable)) {
                this._inline_popupbar.hide();
                return;
            }

            var range = selection.getRangeAt(0);

            var boundary = $.extend({}, range.getBoundingClientRect());
            var rects = range.getClientRects();

            if (rects.length > 1) {
                var boundary_by_rect = {top: null, left: null, bottom: null, right: null};

                var full_box_matched_counter = 0;

                for (var x = 0; x < rects.length; x++) {
                    var rect = rects[x];

                    if (rect.right === boundary.right && rect.left === boundary.left) {
                        full_box_matched_counter++;

                        if (full_box_matched_counter === 2) {
                            break;
                        }

                        continue;
                    }

                    if (boundary_by_rect.top === null || boundary_by_rect.top > rect.top) {
                        boundary_by_rect.top = rect.top;
                    }

                    if (boundary_by_rect.bottom === null || boundary_by_rect.bottom < rect.bottom) {
                        boundary_by_rect.bottom = rect.bottom;
                    }

                    if (boundary_by_rect.left === null || boundary_by_rect.left > rect.left) {
                        boundary_by_rect.left = rect.left;
                    }

                    if (boundary_by_rect.right === null || boundary_by_rect.right < rect.right) {
                        boundary_by_rect.right = rect.right;
                    }
                }

                if (full_box_matched_counter < 2) {
                    boundary = boundary_by_rect;
                }
            }

            var scroller_boundary = this._getScrollerBoundary();

            if (boundary.left < scroller_boundary.left && boundary.right > scroller_boundary.left) {
                boundary.left = scroller_boundary.left;
            }

            if (boundary.right > scroller_boundary.right && boundary.left < scroller_boundary.right) {
                boundary.right = scroller_boundary.right;
            }

            this._inline_popupbar.show();

            var popupbar_width = this._inline_popupbar.outerWidth();
            var popupbar_height = this._inline_popupbar.outerHeight();

            var popupbar_boundary = {
                top: boundary.top - popupbar_height,
                left: boundary.left + ((boundary.right - boundary.left - popupbar_width) / 2)
            };

            if (popupbar_boundary.top < scroller_boundary.top && boundary.bottom < scroller_boundary.bottom) {
                popupbar_boundary.top = boundary.bottom;
                this._inline_popupbar.addClass('bottom');
            } else {
                this._inline_popupbar.removeClass('bottom');
            }

            this._inline_popupbar.offset({
                top: $(window).scrollTop() + popupbar_boundary.top,
                left: $(window).scrollLeft() + popupbar_boundary.left
            });
        };

        this._inlineMode_renderPopupbar = function () {
            var cmds = this._processCommandBarData(this.inline_popupbar_command_data, 'popupbar');

            if (cmds.length < 1) {
                return;
            }

            this._inline_popupbar = $('<div />').addClass('osc-editor-popupbar').appendTo(this._editbox);

            $('<div />').addClass('arrow').appendTo(this._inline_popupbar);

            var popupbar_ul = $('<ul />').addClass('clearfix').appendTo(this._inline_popupbar);

            for (var x = 0; x < cmds.length; x++) {
                popupbar_ul.append(cmds[x]);
            }

            this._setupCommand(popupbar_ul[0]);
        };

        this._inlineMode_tryDisplaySidebar = function () {
            var range = this._getRange().cloneRange();
            var boundary = {left: 0, top: 0, right: 0, bottom: 0};

            if (range.getClientRects) {
                range.collapse(this._selectionIsBackward());
                var rects = range.getClientRects();

                if (rects.length > 0) {
                    $.extend(boundary, rects[0]);
                }
            }

            if (boundary.top === 0 && boundary.bottom === 0) {
                var span = document.createElement('span');

                if (span.getClientRects) {
                    span.appendChild(document.createTextNode('\u200b'));

                    range.insertNode(span);

                    $.extend(boundary, span.getClientRects()[0]);

                    var span_parent = span.parentNode;

                    span_parent.removeChild(span);

                    span_parent.normalize();
                }
            }

            var scroller_boundary = this._getScrollerBoundary();

            if (boundary.top < scroller_boundary.top && boundary.bottom > scroller_boundary.top) {
                boundary.top = scroller_boundary.top;
            }

            if (boundary.bottom > scroller_boundary.bottom && boundary.top < scroller_boundary.bottom) {
                boundary.bottom = scroller_boundary.bottom;
            }

            var editarea_boundary = this._editarea[0].getBoundingClientRect();

            this._inline_sidebar.show().offset({
                top: $(window).scrollTop() + boundary.top + ((boundary.bottom - boundary.top - this._inline_sidebar.outerHeight()) / 2),
                left: $(window).scrollLeft() + editarea_boundary.left - this._inline_sidebar.outerWidth()
            });
        };

        this._inlineMode_renderSidebar = function () {
            var sidebar_ul = $('<ul />');

            var cmds = this._processCommandBarData(this.inline_sidebar_element_command_data, 'sidebar_element');

            if (cmds.length > 0) {
                var sidebar_li = $('<li />').append(this._renderIcon('plus')).appendTo(sidebar_ul);

                var sidebar_sub_ul = $('<ul />').appendTo($('<div />').append($('<ins />')).appendTo(sidebar_li));

                for (var x = 0; x < cmds.length; x++) {
                    sidebar_sub_ul.append(cmds[x]);
                }

                this._setupCommand(sidebar_li[0]);
            }

            cmds = this._processCommandBarData(this.inline_sidebar_block_command_data, 'sidebar_block');

            if (cmds.length > 0) {
                var sidebar_li = $('<li />').append(this._renderIcon('bars')).appendTo(sidebar_ul);

                var sidebar_sub_ul = $('<ul />').appendTo($('<div />').append($('<ins />')).appendTo(sidebar_li));

                for (var x = 0; x < cmds.length; x++) {
                    sidebar_sub_ul.append(cmds[x]);
                }

                this._setupCommand(sidebar_li[0]);
            }

            if (sidebar_ul.children().length < 1) {
                return null;
            }

            this._inline_sidebar = $('<div />').addClass('osc-editor-sidebar').append(sidebar_ul).appendTo(this._editbox);
        };

        this._inlineMode_renderControl = function () {
            this._inlineMode_renderPopupbar();
            this._inlineMode_renderSidebar();

            this._preContext(this._inline_popupbar ? this._inline_popupbar[0] : null, this._inline_sidebar ? this._inline_sidebar[0] : null);

            var self = this;

            var clear_timer = null;
            var render_timer = null;

            this._editarea.unbind('.oscEditorInline').bind('blur.oscEditorInline', function () {
                clearTimeout(clear_timer);
                clearTimeout(render_timer);

                clear_timer = setTimeout(function () {
                    if (self._inline_popupbar) {
                        self._inline_popupbar.hide();
                    }

                    if (self._inline_sidebar) {
                        self._inline_sidebar.hide();
                    }
                }, 200);
            });

            this._context_callback.inline = function () {
                clearTimeout(clear_timer);
                clearTimeout(render_timer);

                render_timer = setTimeout(function () {
                    if (self._inline_popupbar) {
                        self._inlineMode_tryDisplayPopupbar();
                    }

                    if (self._inline_sidebar) {
                        self._inlineMode_tryDisplaySidebar();
                    }
                }, 50);
            };
        };

        this._boxMode_renderControl = function () {
            var self = this;

            this._editarea.bind('getEditareaViewportBoundary.editor', function (e, viewport_boundary) {
                self._boxMode_RecalculateViewportBoundary(viewport_boundary);
            });

            this.scroller.scroll(function () {
                self._boxMode_setBarsPosition();
            });
            $(window).resize(function () {
                self._boxMode_setBarsPosition();
            });

            this._box_topbar = $('<div />').addClass('osc-editor-topbar');
            this._box_bottombar = $('<div />').addClass('osc-editor-bottombar');

            if (this.box_pathbar_enable) {
                var path_list = document.createElement('ul');
                path_list.setAttribute('editor-context', 'nodePath');

                this._box_pathbar = document.createElement('div');
                this._box_pathbar.className = 'osc-editor-node-path';
                this._box_pathbar.appendChild(path_list);

                this._box_bottombar.append(this._box_pathbar);
            }

            var command_bar = $('<div />').addClass('osc-editor-commandbar').appendTo(this._box_topbar);

            if (typeof this.box_command_data === 'string') {
                this.box_command_data = [this.box_command_data];
            }

            for (var x = 0; x < this.box_command_data.length; x++) {
                if (typeof this.box_command_data[x] === 'string') {
                    this.box_command_data[x] = [this.box_command_data[x]];
                }

                var ul = $('<ul />').addClass('clearfix');

                for (var y = 0; y < this.box_command_data[x].length; y++) {
                    var cmds = this._processCommandBarData(this.box_command_data[x][y]);

                    if (cmds.length > 0) {
                        if (y > 0) {
                            cmds.reverse();
                        }

                        for (var z = 0; z < cmds.length; z++) {
                            if (y > 0) {
                                cmds[z].className = (cmds[z].className + ' align-right').trim();
                            }

                            ul.append(cmds[z]);
                        }
                    }
                }

                if (ul.children().length > 0) {
                    ul.appendTo(command_bar);
                }
            }

            if (command_bar[0].childNodes.length < 1) {
                command_bar.remove();
            }

            this._setupCommand(command_bar[0]);
            this._preContext(command_bar[0], this._box_pathbar);

            if (this._box_topbar[0].childNodes.length < 1) {
                this._box_topbar = null;
            } else {
                this._box_topbar.appendTo(this._editbox);
                this._editbox.css('padding-top', this._box_topbar.height() + 'px');
            }

            if (this._box_bottombar[0].childNodes.length < 1) {
                this._box_bottombar = null;
            } else {
                this._box_bottombar.appendTo(this._editbox);
                this._editbox.css('padding-bottom', this._box_bottombar.height() + 'px');
            }

            this.box_min_height = parseInt(this.box_min_height);

            if (this.box_min_height < 25 || isNaN(this.box_min_height)) {
                this.box_min_height = 25;
            }

            this._editarea.css('min-height', this.box_min_height + 'px');

            this.box_max_height = parseInt(this.box_max_height);

            if (this.box_max_height < 0 || isNaN(this.box_max_height)) {
                this.box_max_height = 0;
            }

            if (this.box_max_height > 0) {
                this._editarea.css('max-height', this.box_max_height + 'px');
            }

            this._boxMode_setBarsPosition();
        };

        this._boxMode_RecalculateViewportBoundary = function (viewport_boundary) {
            if (this._box_topbar && parseInt(this._box_topbar.css('top')) !== 0) {
                viewport_boundary.top += this._box_topbar.height();
            }

            if (this._box_bottombar && parseInt(this._box_bottombar.css('bottom')) !== 0) {
                viewport_boundary.bottom -= this._box_bottombar.height();
            }
        };

        this._boxMode_setBarsPosition = function () {
            var scroller_boundary = this._getScrollerBoundary();

            var editarea_boundary = {};

            $.extend(editarea_boundary, this._editbox[0].getBoundingClientRect());

            if (this._box_topbar) {
                if (editarea_boundary.top -50 >= scroller_boundary.top) {
                    this._box_topbar.removeAttr('style');
                } else {
                    let top_bar_height = this.scroller_modal.length > 0 ? 0 : $('.topbar').height();
                    this._box_topbar.offset({
                        top: $(window).scrollTop() + top_bar_height + Math.min(scroller_boundary.top, this._editarea[0].getBoundingClientRect().bottom - 100),
                        left: $(window).scrollLeft() + editarea_boundary.left
                    });
                }
            }

            if (this._box_bottombar) {
                if (editarea_boundary.bottom <= scroller_boundary.bottom) {
                    this._box_bottombar.removeAttr('style');
                } else {
                    this._box_bottombar.css('bottom', 'initial').offset({
                        top: $(window).scrollTop() + Math.max(scroller_boundary.bottom - this._box_bottombar.height(), this._editarea[0].getBoundingClientRect().top + 100),
                        left: $(window).scrollLeft() + editarea_boundary.left
                    });
                }
            }
        };

        this._processCommandBarData = function (data) {
            data = data + '';
            data = data.replace(',', ' ');
            data = data.split(' ');

            var cmd_nodes = [];

            for (var x = 0; x < data.length; x++) {
                var cmd_key = data[x];
                cmd_key = cmd_key.trim();

                var li = document.createElement('li');

                if (cmd_key === '|') {
                    li.className = 'separate';
                    cmd_nodes.push(li);
                    continue;
                }

                cmd_key = cmd_key.replace(/[^a-zA-Z0-9\_]/i, '');

                if (!cmd_key || typeof this._commands[cmd_key] === 'undefined') {
                    continue;
                }

                var cmd_node = this._renderCommandBtn(this._commands[cmd_key]);

                if (cmd_node) {
                    li.appendChild(cmd_node);
                    cmd_nodes.push(li);
                }
            }

            return cmd_nodes;
        };

        this._renderCommandBtn = function (cmd) {
            if (cmd.renderer) {
                if (typeof cmd.renderer === 'function') {
                    return cmd.renderer.apply(this, cmd);
                }

                if (typeof this[cmd.renderer] !== 'undefined') {
                    return this[cmd.renderer](cmd);
                }

                return null;
            }

            var cmd_node = document.createElement('div');
            cmd_node.className = 'osc-editor-cmd';

            var cmd_content = document.createElement('div');

            cmd_node.appendChild(cmd_content);

            if (cmd.label) {
                var label = document.createElement('span');
                label.innerHTML = cmd.label;
                cmd_content.appendChild(label);
            }

            if (cmd.icon) {
                cmd_content.appendChild(this._renderIcon(cmd.icon));
            }

            if (cmd.sub) {
                cmd_node.className = cmd_node.className + ' osc-editor-cmd-menu';

                var cmd_sub_toggler = document.createElement('div');
                cmd_sub_toggler.className = 'osc-editor-cmd-menu-toggler';

                cmd_node.appendChild(cmd_sub_toggler);

                cmd_sub_toggler.appendChild(this._renderIcon('caret-down'));
                cmd_sub_toggler.appendChild(this._renderCommandBtnSubList(cmd.sub));

                this._setupCommandBtn(cmd_content, cmd);
            } else {
                this._setupCommandBtn(cmd_node, cmd);
            }

            return cmd_node;
        };

        this._renderIcon = function (name) {
            var viewBox = '0 0 512 512';

            if (OSC_EDITOR_ICON_MAP !== null && typeof OSC_EDITOR_ICON_MAP[name] !== 'undefined') {
                viewBox = OSC_EDITOR_ICON_MAP[name];
            }

            var icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            icon.setAttribute('data-icon', 'osc-editor-' + name);
            icon.setAttribute('viewBox', viewBox);
            var use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
            use.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", '#osc-editor-' + name);
            icon.appendChild(use);

            return icon;
        };

        this._renderCommandBtnSubList = function (cmds) {
            var ul = document.createElement('ul');

            for (var x = 0; x < cmds.length; x++) {
                var cmd = cmds[x];

                var li = document.createElement('li');

                ul.appendChild(li);

                if (typeof cmd === 'string') {
                    li.className = 'separate';
                    continue;
                }

                var cmd_node = document.createElement('div');
                cmd_node.className = 'osc-editor-cmd';

                li.appendChild(cmd_node);

                if (cmd.label) {
                    var label = document.createElement('span');
                    label.innerHTML = cmd.label;
                    cmd_node.appendChild(label);
                }

                if (cmd.icon) {
                    cmd_node.appendChild(this._renderIcon(cmd.icon));
                }

                if (cmd.sub) {
                    li.appendChild(this._renderIcon('caret-right'));
                    li.appendChild(this._renderCommandBtnSubList(cmd.sub));
                }

                this._setupCommandBtn(cmd_node, cmd);
            }

            return ul;
        };

        this._setupCommandBtn = function (btn, cmd) {
            if (cmd.cmd) {
                btn.setAttribute('editor-cmd', cmd.cmd);

                if (cmd.cmd_arg) {
                    btn.setAttribute('editor-cmd-arg', JSON.stringify(cmd.cmd_arg));
                }
            }

            if (cmd.context) {
                btn.setAttribute('editor-context', cmd.context);

                if (cmd.context_arg) {
                    btn.setAttribute('editor-context-arg', JSON.stringify(cmd.context_arg));
                }
            }

            if (cmd.initialize) {
                if (typeof cmd.initialize === 'function') {
                    cmd.initialize.apply(this, [btn, cmd]);
                } else if (typeof this[cmd.initialize] !== 'undefined') {
                    this[cmd.initialize].apply(this, [btn, cmd]);
                }
            }
        };

        this._command_locked = false;

        this._setupCommand = function () {
            var self = this;

            for (var i = 0; i < arguments.length; i++) {
                if (!arguments[i] || !arguments[i].nodeType) {
                    continue;
                }

                var descendant_nodes = arguments[i].getElementsByTagName('*');

                for (var k = 0; k < descendant_nodes.length; k++) {
                    if (!descendant_nodes[k].hasAttribute('editor-cmd')) {
                        continue;
                    }

                    $(descendant_nodes[k]).unbind('.editorCommand').bind('click.editorCommand', function () {
                        var cmd_arg = $(this).attr('editor-cmd-arg');

                        if (cmd_arg) {
                            eval('cmd_arg = ' + cmd_arg);
                        }

                        self._execCommand($(this).attr('editor-cmd'), cmd_arg);
                    });
                }
            }
        };

        this._preContext = function () {
            this._context_data = {};

            for (var i = 0; i < arguments.length; i++) {
                if (!arguments[i] || !arguments[i].nodeType) {
                    continue;
                }

                var descendant_nodes = arguments[i].getElementsByTagName('*');

                for (var k = 0; k < descendant_nodes.length; k++) {
                    var descendant_node = descendant_nodes[k];

                    if (!descendant_node.hasAttribute('editor-context')) {
                        continue;
                    }

                    var context = descendant_node.getAttribute('editor-context');

                    var context_arg = descendant_node.getAttribute('editor-context-arg');

                    var context_hash = $.md5(context + '::' + (context_arg ? context_arg : ''));

                    if (typeof this._context_data[context_hash] !== 'undefined') {
                        this._context_data[context_hash].nodes.push(descendant_node);
                        continue;
                    }

                    if (context_arg) {
                        eval('context_arg = ' + context_arg);
                    } else {
                        context_arg = [];
                    }

                    var context_callback = '_context' + context.substring(0, 1).toUpperCase() + context.substring(1);

                    if (!this[context_callback]) {
                        context_callback = null;
                    }

                    var state_callback = '_contextState' + context.substring(0, 1).toUpperCase() + context.substring(1);

                    if (!this[state_callback]) {
                        state_callback = null;
                    }

                    this._context_data[context_hash] = {
                        context: context,
                        context_callback: context_callback,
                        context_arg: context_arg,
                        state_callback: state_callback,
                        nodes: [descendant_node]
                    };
                }
            }
        };

        this.setup = function () {
            this._setEditorEvents();

            var first_node = this._editarea.children().first();

            if (!first_node[0] || !this._nodeIsBlock(first_node[0])) {
                this._editarea.prepend($('<p />').append(first_node[0] ? first_node : $('<br />')));
            }
        };

        this._setEditorEvents = function () {
            var self = this;

            this._editbox.unbind('.oscEditor').bind('click.oscEditor', function () {
                self._checkFocus();
            });

            this._editarea.unbind('.oscEditor');

            this._editarea.bind('focus.oscEditor', function (e) {
                self._winFocusListener(e);
            });
            this._editarea.bind('blur.oscEditor', function (e) {
                self._winBlurListener(e);
            });
            this._editarea.bind('mousedown.oscEditor', function (e) {
                var uniqid = $.makeUniqid();

                $(document).bind('mouseup.editor' + uniqid, function (e) {
                    $(document).unbind('.editor' + uniqid);
                    self._docMouseupListener(e);
                });
            });
            this._editarea.bind('keydown.oscEditor', function (e) {
                self._docKeydownListener(e);
            });
            this._editarea.bind('keyup.oscEditor', function (e) {
                self._docKeyupListener(e);
            });

            this._editarea.bind('paste.oscEditor', function (e) {
                self._bodyPasteListener(e);
            });
        };

        this._docKeydownListener = function (e) {
            if (['input', 'textarea', 'select'].indexOf(e.target.nodeName.toLowerCase()) !== -1 || !e.target.isContentEditable) {
                return;
            }

            if ((e.keyCode === 90 || e.keyCode === 89) && e.ctrlKey) {
                this._history_skip_add_flag = true;

                if (e.keyCode === 90) {
                    this._historyUndo();
                } else {
                    this._historyRedo();
                }

                e.preventDefault();

                return;
            }

            if (this._history_idx !== this._history_data.length - 1 && this._keypressIsPrintable(e.keyCode)) {
                this._history_data = this._history_data.slice(0, this._history_idx + 1);
                this._history_idx = this._history_data.length - 1;
            }

            var node = this._getSelectionNode();

            this._resolveOutsideBlockContent();

            node = this._getSelectionNode();

            if (e.keyCode === 13) {
                this._processPressEnterKey(e);
            } else if (e.keyCode === 9 && this._nodeIsInList(node)) {
                this._processPressTabInList(e);
            } else if (e.keyCode === 86 && (e.ctrlKey || e.metaKey)) {
                var self = this;

                this._disable_restore_selection = true;

                this._commandInsertHTML(this._makeCaretMarker('paste-event'));

                var wrap = $('<div />').css({position: 'fixed', width: '1px', height: '1px', overflow: 'hidden', opacity: 0, top: '50%', left: '50%'}).prop('contentEditable', false).appendTo(this._editarea);

                var container = $('<div />').prop('contentEditable', true).html('x').appendTo(wrap);

                this._selectNode(container[0], false, true);

                container.bind('paste', function (e) {
                    e.stopPropagation();

                    setTimeout(function () {
                        wrap.remove();
                        self._moveCaretToMarker('paste-event');
                        self._processPastedContent(container.html());
                        self._disable_restore_selection = false;
                    }, 0);
                });
            } else if (e.keyCode === 8 || e.keyCode === 46) {
                if (window.getSelection().isCollapsed) {
                    if (e.keyCode === 8) {
                        if (this._caretInStartOfBlock()) {
                            var block = this._nodeGetBlock(this._getSelectionNode());

                            if (!block.previousSibling && block.nextSibling && !block.nextSibling.isContentEditable && this._nodeIsEmptyBlock(block)) {
                                e.preventDefault();

                                this._selectNextBlock(block.nextSibling);

                                block.parentNode.removeChild(block);
                            } else if (block.previousSibling && !block.previousSibling.isContentEditable) {
                                e.preventDefault();
                                block.parentNode.removeChild(block.previousSibling);
                            }
                        }
                    } else if (this._caretInEndOfBlock()) {
                        var block = this._nodeGetBlock(this._getSelectionNode());

                        if (block.nextSibling) {
                            if (!block.nextSibling.isContentEditable) {
                                e.preventDefault();
                                block.parentNode.removeChild(block.nextSibling);
                            } else if (this._nodeIsEmptyBlock(block)) {
                                e.preventDefault();

                                var sibling_block = block.nextSibling;

                                block.parentNode.removeChild(block);

                                this._selectNode(sibling_block, true, true, true);
                            }
                        }
                    }
                }
            }

            this._setContext();
        };

        this._keypressIsPrintable = function (keycode) {
            return (keycode > 47 && keycode < 58) || // number keys
                    (keycode > 64 && keycode < 91) || // letter keys
                    (keycode > 95 && keycode < 112) || // numpad keys
                    (keycode > 185 && keycode < 193) || // ;=,-./` (in order)
                    (keycode > 218 && keycode < 223) || // [\]' (in order)
                    keycode === 32 || // spacebar
                    keycode === 13 || // enter
                    keycode === 8 || //backspace
                    keycode === 46; //delete                    
        };

        this._docKeyupListener = function (e) {
            if (['input', 'textarea', 'select'].indexOf(e.target.nodeName.toLowerCase()) !== -1 || !e.target.isContentEditable) {
                return;
            }

            this._setContext();

            if (!this._history_skip_add_flag) {
                if ([13, 37, 38, 39, 40].indexOf(e.keyCode) >= 0) { //Keys: Left + right + up + down arrow, enter                    
                    this._historyAdd(e.keyCode !== 13);
                }
            } else {
                this._history_skip_add_flag = false;
            }
        };

        this._history_data = [];
        this._history_idx = -1;
        this._history_skip_add_flag = false;

        this._historyAdd = function (skip_if_in_undo) {
            var content = this.getContent();

            if (this._history_idx >= 0 && content === this._history_data[this._history_idx].content) {
                return;
            }

            if (this._history_idx !== this._history_data.length - 1) {
                if (skip_if_in_undo) {
                    return;
                }

                this._history_data = this._history_data.slice(0, this._history_idx + 1);
                this._history_idx = this._history_data.length - 1;
            }

            this._history_data.push({
                content: content,
                selection: this._saveSelectionRange(this._editarea[0])
            });

            this._history_idx++;
        };

        this._historyUndo = function () {
            if (this._history_idx === 0) {
                return;
            }

            this._historyAdd(true);

            this._history_idx--;

            this._historyRestore();
        };

        this._historyRedo = function () {
            if (this._history_idx === this._history_data.length - 1) {
                return;
            }

            this._history_idx++;

            this._historyRestore();
        };

        this._historyRestore = function () {
            this._editarea.html(this._history_data[this._history_idx].content);
            this._normalize(this._editarea[0]);
            this._setSelectionRange(this._history_data[this._history_idx].selection);
        };

        this._docMouseupListener = function (e) {
            if (e.target && !e.target.isContentEditable && window.getSelection().isCollapsed && this._nodeIsAncestor(e.target, this._editarea[0])) {
                var node = e.target;

                while (node.parentNode && !node.parentNode.isContentEditable) {
                    node = node.parentNode;
                }

                if (!node.previousSibling || !node.previousSibling.isContentEditable || ['ul', 'ol', 'table'].indexOf(node.previousSibling.nodeName.toLowerCase()) >= 0) {
                    var node_boundary = e.target.getBoundingClientRect();

                    if (e.pageY < $(window).scrollTop() + (node_boundary.top + (node_boundary.bottom - node_boundary.top) / 2)) {
                        var new_block = document.createElement(this._getDefaultBlockNodeName(node.parentNode));

                        new_block.appendChild(document.createElement('br'));

                        node.parentNode.insertBefore(new_block, node);

                        this._selectNode(new_block, false, true, false);
                    }
                }
            }

            this._setContext();

            this._historyAdd(true);
        };

        this._winFocusListener = function (e) {
            if (['input', 'textarea', 'select'].indexOf(e.target.nodeName.toLowerCase()) !== -1) {
                return;
            }

            this._focused = true;

            this._editor.trigger('editorFocus');

            for (var x = 0; x < this._editarea[0].childNodes.length; x++) {
                if ((this._editarea[0].childNodes[x].nodeType === Node.ELEMENT_NODE) && this._editarea[0].childNodes[x].hasAttribute('placeholder')) {
                    this._editarea[0].childNodes[x].removeAttribute('placeholder');
                }
            }
        };

        this._winBlurListener = function () {
            if (!this._focused) {
                return;
            }

            this._focused = false;

            this._editor.trigger('editorBlur');

            if (this.placeholder && this._editarea[0].childNodes.length === 1 && ['div', 'p'].indexOf(this._editarea[0].firstChild.nodeName.toLowerCase()) && this._nodeIsEmptyBlock(this._editarea[0].firstChild)) {
                this._editarea[0].firstChild.setAttribute('placeholder', this.placeholder);
            }
        };

        this._bodyPasteListener = function (e) {
            if (['input', 'textarea', 'select'].indexOf(e.target.nodeName.toLowerCase()) !== -1 || !e.target.isContentEditable) {
                return;
            }

            e.preventDefault();

            var pasted_content = '';

            if (window.clipboardData && window.clipboardData.getData) {
                pasted_content = window.clipboardData.getData('Text');
            } else if (e.originalEvent.clipboardData && e.originalEvent.clipboardData.getData) {
                pasted_content = this._getPastedData(e, 'text/html');

                if (pasted_content === false) {
                    pasted_content = this._getPastedData(e, 'text/plain');
                    pasted_content = pasted_content.replace('\n', '<br />');
                }
            } else {
                return;
            }

            this._processPastedContent(pasted_content);
        };

        this._getPastedData = function (e, type) {
            for (var i = 0; i < e.originalEvent.clipboardData.types.length; i++) {
                if (e.originalEvent.clipboardData.types[i] === type) {
                    return e.originalEvent.clipboardData.getData(type);
                }
            }

            return false;
        };

        this._processPastedContent = function (pasted_content) {
            var trigger_data = {content: pasted_content};

            this._editor.trigger('processPasteContent', [trigger_data]);

            if (typeof trigger_data.content !== 'string') {
                return;
            }

            pasted_content = trigger_data.content;

            this._historyAdd();

            var wrap = document.createElement('div');

            wrap.innerHTML = pasted_content;

            var block_container = this._nodeGetBlock(this._getSelectionNode());

            if (block_container && block_container.nodeName.toLowerCase() === 'p') {
                this._commandConvertBlock('div');
            }

            this._commandInsertHTML('<span id="mrk-pasted-content-flag">x</span>');

            var flag = document.getElementById('mrk-pasted-content-flag');

            block_container = this._getEndNode(flag);

            if (!this._nodeIsBlock(block_container)) {
                this._resolveOutsideBlockContent(block_container, 'div');
                block_container = this._getEndNode(flag);
            }

            if (wrap.querySelector(this._block_element_names.join(','))) {
                var link_node = this._getParentNode(flag, 'a');

                if (link_node) {
                    while (link_node.firstChild) {
                        link_node.parentNode.insertBefore(link_node.firstChild, link_node);
                    }

                    link_node.parentNode.removeChild(link_node);
                }
            }

            while (wrap.firstChild) {
                flag.parentNode.insertBefore(wrap.firstChild, flag);
            }

            this._selectNode(flag, true, false, true);

            flag.parentNode.removeChild(flag);

            this._normalize(block_container);
        };

        this._processPressEnterKey = function (e) {
            var node = this._getSelectionNode();

            if (this._nodeIsInList(node)) {
                this._processPressEnterInList(e);
                return;
            }

            if (e.shiftKey || e.ctrlKey) {
                return;
            }

            e.preventDefault();

            if (!window.getSelection().isCollapsed) {
                this._execStandardCommand('delete');
            }

            var block = this._nodeGetBlock(this._getSelectionNode(true));
            var root_node = this._nodeGetBlockContainer(this._getSelectionNode(true));

            var new_block = document.createElement(block && ['blockquote', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'].indexOf(block.nodeName.toLowerCase()) < 0 ? block.nodeName.toLowerCase() : this._getDefaultBlockNodeName(root_node));

            if (block) {
                new_block.appendChild(this._extractContentFromCaret());

                this._nodeInsertAfter(new_block, block);

                if (block.childNodes.length < 1 || (block.childNodes.length === 1 && block.firstChild.nodeType === Node.TEXT_NODE && block.firstChild.length === 0)) {
                    block.appendChild(document.createElement('br'));
                }
            } else {
                root_node.appendChild(new_block);
            }

            this._cleanUselessNode(new_block);

            if (new_block.childNodes.length < 1 || (new_block.childNodes.length === 1 && new_block.firstChild.nodeType === Node.TEXT_NODE && new_block.firstChild.length === 0)) {
                new_block.appendChild(document.createElement('br'));
            }

            new_block.insertBefore(this._makeCaretMarker('process-enter', true), new_block.firstChild);

            this._moveCaretToMarker('process-enter');
        };

        this._processPressEnterInList = function (e) {
            e.preventDefault();

            if (!window.getSelection().isCollapsed) {
                this._execStandardCommand('delete');
            }

            var block = this._nodeGetBlock(this._getSelectionNode());
            var list_item = this._nodeGetParentListItem(block);

            if (e.shiftKey) {
                var caret_pos_flag = this._caretGetPositionFlag();

                var new_block = document.createElement(this._getDefaultBlockNodeName(list_item));
                new_block.appendChild(document.createElement('br'));

                if (caret_pos_flag === 0) {
                    this._splitBlock(new_block);
                } else if (caret_pos_flag === -1) {
                    list_item.insertBefore(new_block, block);
                } else {
                    this._nodeInsertAfter(new_block, block);
                }

                if (caret_pos_flag !== -1) {
                    this._selectNode(new_block, true, true, true);
                }

                return;
            } else if (list_item.childNodes.length > 1 || !this._nodeIsEmptyBlock(block)) {
                var caret_pos_flag = this._caretGetPositionFlag();

                if (caret_pos_flag === 0) {
                    block = this._splitBlock(new_block);
                } else if (caret_pos_flag === 1) {
                    if (!block.nextSibling) {
                        var new_block = document.createElement(this._getDefaultBlockNodeName(list_item));
                        new_block.appendChild(document.createElement('br'));

                        list_item.appendChild(new_block);
                    }

                    block = block.nextSibling;
                }

                this._nodeInsertAfter(list_item.cloneNode(), list_item);

                var fragment = document.createDocumentFragment();

                while (block.nextSibling) {
                    fragment.appendChild(block.nextSibling);
                }

                list_item.nextSibling.appendChild(block);
                list_item.nextSibling.appendChild(fragment);

                if (list_item.childNodes.length < 1) {
                    list_item.appendChild(document.createElement(this._getDefaultBlockNodeName(list_item)));
                    list_item.firstChild.appendChild(document.createElement('br'));
                }

                this._selectNode(block, true, true, true);

                return;
            }

            this._processOutdentInList();

            this._selectNode(block, true, true, true);
        };

        this._processPressTabInList = function (e) {
            e.preventDefault();
            this[e.shiftKey ? '_processOutdentInList' : '_processIndentInList']();
        };

        this._processIndentInList = function () {
            var start_li = this._getParentNode(this._getSelectionNode(), 'li');
            var end_li = this._getParentNode(this._getSelectionNode(true), 'li');

            if (start_li === end_li) {
                var saved_selection = this._saveSelectionRange(start_li);
                this._indentListItem(start_li);
                this._setSelectionRange(saved_selection);

                return;
            }

            if (!start_li || !end_li) {
                return;
            }

            var list = this._getParentNode(this._getSelectionCommonAncestor(), 'ul,ol');

            if (!list) {
                return;
            }

            var saved_selection = this._saveSelectionRange(list);

            var uniqid = $.makeUniqid();

            start_li.setAttribute('li-marker', uniqid);
            end_li.setAttribute('li-marker', uniqid);

            var li_collection = [].slice.call(list.getElementsByTagName('li'));

            var found_counter = 0;

            for (var x = 0; x < li_collection.length; x++) {
                var li = li_collection[x];

                if (li.getAttribute('li-marker') === uniqid) {
                    li.removeAttribute('li-marker');
                    found_counter++;
                }

                if (found_counter < 1) {
                    continue;
                }

                this._indentListItem(li);

                if (found_counter === 2) {
                    break;
                }
            }

            this._setSelectionRange(saved_selection);
        };

        this._processOutdentInList = function (deep_flag) {
            var start_li = this._getParentNode(this._getSelectionNode(), 'li');
            var end_li = this._getParentNode(this._getSelectionNode(true), 'li');

            var saved_selection = this._saveSelectionRange(this._editarea[0]);

            if (start_li === end_li) {
                this._outdentListItem(start_li, deep_flag);
                this._setSelectionRange(saved_selection);
                return;
            }

            if (!start_li || !end_li) {
                return;
            }

            var list = this._getParentNode(this._getSelectionCommonAncestor(), 'ul,ol');

            if (!list) {
                return;
            }

            var uniqid = $.makeUniqid();

            start_li.setAttribute('li-marker', uniqid);
            end_li.setAttribute('li-marker', uniqid);

            var li_collection = [].slice.call(list.getElementsByTagName('li')).reverse();

            var found_counter = 0;

            for (var x = 0; x < li_collection.length; x++) {
                var li = li_collection[x];

                if (li.getAttribute('li-marker') === uniqid) {
                    li.removeAttribute('li-marker');
                    found_counter++;
                }

                if (found_counter < 1) {
                    continue;
                }

                this._outdentListItem(li, deep_flag);

                if (found_counter === 2) {
                    break;
                }
            }

            this._setSelectionRange(saved_selection);
        };

        this._indentListItem = function (node) {
            if (node.nodeName.toLowerCase() !== 'li') {
                node = this._nodeGetParentListItem(node);

                if (!node) {
                    return;
                }
            }

            if (!node.previousSibling) {
                return;
            }

            if (!node.previousSibling.lastChild || ['ul', 'ol'].indexOf(node.previousSibling.lastChild.nodeName.toLowerCase()) < 0) {
                node.previousSibling.appendChild(node.parentNode.cloneNode());
            }

            node.previousSibling.lastChild.appendChild(node);

            if (node.lastChild && ['ol', 'ul'].indexOf(node.lastChild.nodeName.toLowerCase()) >= 0) {
                while (node.lastChild.firstChild) {
                    node.parentNode.appendChild(node.lastChild.firstChild);
                }

                node.removeChild(node.lastChild);
            }
        };

        this._outdentListItem = function (node, deep_flag) {
            if (node.nodeName.toLowerCase() !== 'li') {
                node = this._nodeGetParentListItem(node);

                if (!node) {
                    return;
                }
            }

            var list = node.parentNode;

            if (list.parentNode.nodeName.toLowerCase() === 'li') {
                if (node.nextSibling) {
                    if (!node.lastChild || ['ul', 'ol'].indexOf(node.lastChild.nodeName.toLowerCase()) < 0) {
                        node.appendChild(list.cloneNode());
                    }

                    while (node.nextSibling) {
                        node.lastChild.appendChild(node.nextSibling);
                    }
                }

                this._nodeInsertAfter(node, list.parentNode);
            } else {
                if (node.nextSibling) {
                    if (!node.lastChild || ['ul', 'ol'].indexOf(node.lastChild.nodeName.toLowerCase()) < 0) {
                        this._nodeInsertAfter(list.cloneNode(), list);

                        while (node.nextSibling) {
                            list.nextSibling.appendChild(node.nextSibling);
                        }
                    } else {
                        while (node.nextSibling) {
                            node.lastChild.appendChild(node.nextSibling);
                        }
                    }
                }

                var flag_node = list;

                while (node.firstChild) {
                    if (['ul', 'ol'].indexOf(node.firstChild.nodeName.toLowerCase()) >= 0 && node.firstChild.nodeName.toLowerCase() === flag_node.nodeName.toLowerCase()) {
                        while (node.firstChild.firstChild) {
                            flag_node.appendChild(node.firstChild.firstChild);
                        }

                        node.removeChild(node.firstChild);

                        continue;
                    }

                    this._nodeInsertAfter(node.firstChild, flag_node);

                    flag_node = flag_node.nextSibling;
                }

                list.removeChild(node);
            }

            if (list.childNodes.length < 1) {
                list.parentNode.removeChild(list);
            }

            if (deep_flag && node.parentNode) {
                this._outdentListItem(node, deep_flag);
            }
        };

        this._checkFocus = function () {
            if (!this._focused) {
                this._restoreSelection();
                this.set_focus && this._editarea[0].focus();
            }

            var start_node = this._getSelectionNode(false, true);

            if (!start_node || start_node === this._editarea[0]) {
                var node = this._editarea[0].firstChild;

                while (!node.isContentEditable && node.nextSibling) {
                    node = node.nextSibling;
                }

                if (node.isContentEditable) {
                    this._selectNode(node, true, false, true, true);
                }
            }
        };

        this._cleanAttributes = function (parent_node) {
            var allowed_attrs_data = {};
            var allowed_style_data = {};

            var nodes = parent_node.getElementsByTagName('*');

            for (var i = 0; i < nodes.length; i++) {
                if (this._nodeIsEditorElement(nodes[i])) {
                    continue;
                }

                var node_name = nodes[i].nodeName.toLowerCase();

                if (typeof allowed_attrs_data[node_name] === 'undefined') {
                    var allowed_attrs = [];

                    if (typeof this._node_allowed_attrs[node_name] !== 'undefined') {
                        allowed_attrs = allowed_attrs.concat(this._node_allowed_attrs[node_name]);
                    }

                    if (typeof this._node_allowed_attrs.all !== 'undefined') {
                        allowed_attrs = allowed_attrs.concat(this._node_allowed_attrs.all);
                    }

                    allowed_attrs_data[node_name] = allowed_attrs;
                }

                if (typeof allowed_style_data[node_name] === 'undefined') {
                    var allowed_style = [];

                    if (typeof this._node_allowed_style[node_name] !== 'undefined') {
                        allowed_style = allowed_style.concat(this._node_allowed_style[node_name]);
                    }

                    if (typeof this._node_allowed_style.all !== 'undefined') {
                        allowed_style = allowed_style.concat(this._node_allowed_style.all);
                    }

                    allowed_style_data[node_name] = allowed_style;
                }

                this._nodeCleanAttributes(nodes[i], allowed_attrs_data[node_name]);
                this._nodeCleanStyle(nodes[i], allowed_style_data[node_name]);
            }
        };

        this._nodeCleanAttributes = function (node, allowed_attrs) {
            var attrs = [];

            for (var i = 0; i < node.attributes.length; i++) {
                attrs.push(node.attributes[i].name.toLowerCase());
            }

            if (typeof allowed_attrs !== 'object' || allowed_attrs === null) {
                allowed_attrs = {};
            }

            for (var i = 0; i < attrs.length; i++) {
                if (allowed_attrs.indexOf(attrs[i]) < 0) {
                    node.removeAttribute(attrs[i]);
                }
            }
        };

        this._nodeCleanStyle = function (node, allowed_style) {
            var new_style = {};

            if (typeof allowed_style === 'object' && allowed_style !== null && node.style.cssText) {
                var matches = node.style.cssText.match(/([\w-]+)\s*:\s*((?:[^;'"]+|"(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*')*)\s*;?/g);

                $.each(matches, function (k, value) {
                    var _matches = value.match(/^([\w-]+)\s*:\s*(.+?)\;?$/);

                    if (_matches && allowed_style.indexOf(_matches[1].toLowerCase()) >= 0) {
                        new_style[_matches[1].toLowerCase()] = _matches[2];
                    }
                });
            }

            node.removeAttribute('style');

            $(node).css(new_style);
        };

        this._cleanUselessNode = function (parent_node) {
            this._cleanUselessNodeRecursive(parent_node);
            this._cleanUnallowedNode(parent_node);

            while (true) {
                var nodes = parent_node.getElementsByTagName('*');

                var break_flag = true;

                for (var i = 0; i < nodes.length; i++) {
                    var node_name = nodes[i].nodeName.toLowerCase();

                    if (['th', 'td', 'br'].indexOf(node_name) >= 0) {
                        continue;
                    }

                    if (node_name === 'img' && this.image_enable) {
                        continue;
                    }

                    if (node_name === 'a' && nodes[i].hasAttribute('id')) {
                        continue;
                    }

                    if (nodes[i].hasAttribute('editor-skip-empty') || this._nodeIsEditorElement(nodes[i])) {
                        continue;
                    }

                    var has_element_flag = false;

                    for (var x = 0; x < nodes[i].childNodes.length; x++) {
                        if (nodes[i].childNodes[x].nodeType === Node.ELEMENT_NODE) {
                            has_element_flag = true;
                            break;
                        }
                    }

                    if (has_element_flag || nodes[i].textContent.trim() !== '') {
                        continue;
                    }

                    break_flag = false;

                    nodes[i].parentNode.removeChild(nodes[i]);
                }

                if (break_flag) {
                    break;
                }
            }

            var spans = parent_node.querySelectorAll('span');

            for (var i = 0; i < spans.length; i++) {
                if (spans[i].attributes.length > 0) {
                    continue;
                }

                while (spans[i].firstChild) {
                    spans[i].parentNode.insertBefore(spans[i].firstChild, spans[i]);
                }

                spans[i].parentNode.removeChild(spans[i]);
            }
        };

        this._cleanUselessNodeRecursive = function (parent_node) {
            for (var i = 0; i < parent_node.childNodes.length; i++) {
                var child = parent_node.childNodes[i];

                if (child.nodeType === Node.COMMENT_NODE || (child.nodeType === Node.TEXT_NODE && !(/\S/).test(child.nodeValue))) {
                    parent_node.removeChild(child);
                    i--;
                } else if (child.nodeType === Node.ELEMENT_NODE) {
                    this._cleanUselessNodeRecursive(child);
                }
            }
        };

        this._cleanUnallowedNode = function (parent_node) {
            var nodes = parent_node.querySelectorAll(this._remove_element_names.join(','));

            if (nodes.length > 0) {
                for (var i = 0; i < nodes.length; i++) {
                    nodes[i].parentNode.removeChild(nodes[i]);
                }
            }

            var node_names = this._allowed_block_element_names.concat(this._allowed_element_names).join(',');

            nodes = parent_node.getElementsByTagName('*');

            for (var i = 0; i < nodes.length; i++) {
                if (node_names.indexOf(nodes[i].nodeName.toLowerCase()) >= 0) {
                    continue;
                }

                if (nodes[i].nodeType === Node.TEXT_NODE) {
                    continue;
                }

                if (this._block_element_names.indexOf(nodes[i].nodeName.toLowerCase()) >= 0) {
                    var new_node = document.createElement('div');

                    nodes[i].parentNode.insertBefore(new_node, nodes[i]);

                    while (nodes[i].firstChild) {
                        new_node.appendChild(nodes[i].firstChild);
                    }
                } else {
                    while (nodes[i].firstChild) {
                        nodes[i].parentNode.insertBefore(nodes[i].firstChild, nodes[i]);
                    }
                }

                nodes[i].parentNode.removeChild(nodes[i]);
            }
        };

        this._editor_element_markers = {};

        this._packEditorElement = function (node, pack_as_block) {
            var marker_id = $.makeUniqid();

            var marker = document.createElement(pack_as_block ? 'div' : 'span');
            marker.setAttribute('osc-editor-element-marker', marker_id);

            node.parentNode.insertBefore(marker, node);
            node.parentNode.removeChild(node);

            this._nodeSetEditorElement(marker, true);

            marker.innerHTML = 'x';

            this._editor_element_markers[marker_id] = node;
        };

        this._packEditorElements = function (parent_node) {
            for (var i in this._element_packers) {
                var params = this._element_packers[i].params;

                if (!Array.isArray(params)) {
                    params = [];
                }

                params.unshift(parent_node);

                try {
                    this._element_packers[i].matched_flag = this._element_packers[i].callback.apply(this, params) === false ? false : true;
                } catch (e) {

                }
            }
        };

        this._unpackEditorElements = function () {
            var marker = null;

            while (marker = this._editarea[0].querySelector('[osc-editor-element-marker]')) {
                var marker_id = marker.getAttribute('osc-editor-element-marker');

                if (typeof this._editor_element_markers[marker_id] !== 'undefined') {
                    var editor_element = this._editor_element_markers[marker_id];

                    delete this._editor_element_markers[marker_id];

                    marker.parentNode.insertBefore(editor_element, marker);
                    marker.parentNode.removeChild(marker);

                    $(editor_element).trigger('editorElementUnpack', [this]);
                    $(editor_element).unbind('editorElementUnpack');
                } else {
                    marker.parentNode.removeChild(marker);
                }
            }
        };

        this._normalize = function (node) {
            node.normalize();

            this._commandInsertHTML(this._makeCaretMarker('normalize'));

            this._packEditorElements(node);

            this._markUsefulElements(node);

            this._cleanAttributes(node);

            this._cleanUselessNode(node);

            this._normalizeBlock(node);

            this._trimEmptyBlock(this._editarea[0]);

            this._unpackEditorElements();

            this._moveCaretToMarker('normalize');

            this._setContext();
        };

        this._markUsefulElements = function (root_node) {
            for (var selector in this._normalize_element_callback) {
                var callbacks = this._normalize_element_callback[selector];

                var nodes = root_node.querySelectorAll(selector);

                for (var x = 0; x < nodes.length; x++) {
                    for (var y = 0; y < callbacks.length; y++) {
                        if (callbacks[y][1].apply(this, [nodes[x], callbacks[y][2]]) === true) {
                            break;
                        }
                    }
                }
            }
        };

        this._addNormalizeElementCallback = function (selectors, callback, callback_params, priority) {
            if (!Array.isArray(selectors)) {
                selectors = [selectors];
            }

            if (typeof callback !== 'function') {
                return;
            }

            priority = parseInt(priority);

            if (isNaN(priority)) {
                priority = 100;
            }

            for (var i = 0; i < selectors.length; i++) {
                var selector = selectors[i];

                if (typeof selector !== 'string') {
                    continue;
                }

                selector = selector.toLowerCase();

                if (typeof this._normalize_element_callback[selector] === 'undefined') {
                    this._normalize_element_callback[selector] = [];
                }

                this._normalize_element_callback[selector].push([priority, callback, callback_params]);
            }
        };

        this._processNonBlockNode = function (root_node, block_node_name) {
            root_node = this._nodeGetBlockContainer(root_node);

            if (root_node.childNodes.length < 1) {
                return;
            }

            block_node_name = typeof block_node_name === 'string' ? block_node_name.toLowerCase() : this._getDefaultBlockNodeName(root_node);

            var node = root_node.firstChild;

            do {
                if (this._block_element_names.indexOf(node.nodeName.toLowerCase()) >= 0) {
                    if (this._allowed_block_element_names.indexOf(node.nodeName.toLowerCase()) < 0) {
                        var new_node = document.createElement(block_node_name);
                        root_node.insertBefore(new_node, node);

                        while (node.firstChild) {
                            new_node.appendChild(node.firstChild);
                        }

                        root_node.removeChild(node);

                        node = new_node;
                    }

                    continue;
                }

                if (node.previousSibling && node.previousSibling.nodeName.toLowerCase() === block_node_name && (block_node_name !== 'li' || node.previousSibling.childNodes.length < 1 || ['ol', 'ul'].indexOf(node.previousSibling.lastChild.nodeName.toLowerCase()) < 0)) {
                    var block = node.previousSibling;
                } else {
                    var block = document.createElement(block_node_name);
                    root_node.insertBefore(block, node);
                }

                block.appendChild(node);

                node = block;
            } while (node = node.nextSibling);
        };

        this._normalizeBlock = function (node, recall) {
            if (!recall) {
                this._processNonBlockNode(node);
            }

            if (this._nodeIsBlockContainer(node)) {
                var nodes = this._nodeGetChildrenArray(node);

                for (var i = 0; i < nodes.length; i++) {
                    this._normalizeBlock(nodes[i], true);
                }

                return;
            }

            node = this._getEndNode(node);

            if (node.childNodes.length < 1) {
                return;
            }

            if (['ul', 'ol'].indexOf(node.nodeName.toLowerCase()) >= 0) {
                return this._normalizeList(node);
            } else if (node.nodeName.toLowerCase() === 'table') {
                return this._normalizeTable(node);
            }

            node.setAttribute('editor-normalizing', 1);

            var table_array = [], list_array = [], block_container_array = [];

            this._normalizeBlockRecursive(node, table_array, list_array, block_container_array);

            var nodes = node.querySelectorAll('[editor-skip-block]');

            for (var i = 0; i < nodes.length; i++) {
                nodes[i].removeAttribute('editor-skip-block');
            }

            if (table_array.length > 0) {
                nodes = node.querySelectorAll('[editor-table-idx]');

                for (var i = 0; i < nodes.length; i++) {
                    var table = table_array[nodes[i].getAttribute('editor-table-idx')];

                    var marker = nodes[i].parentNode;

                    marker.parentNode.insertBefore(table, marker);
                    marker.parentNode.removeChild(marker);

                    this._normalizeTable(table);
                }
            }

            if (list_array.length > 0) {
                nodes = node.querySelectorAll('[editor-list-idx]');

                for (var i = 0; i < nodes.length; i++) {
                    var list = list_array[nodes[i].getAttribute('editor-list-idx')];

                    var marker = nodes[i].parentNode;

                    marker.parentNode.insertBefore(list, marker);
                    marker.parentNode.removeChild(marker);

                    this._normalizeList(list);
                }
            }

            if (block_container_array.length > 0) {
                nodes = node.querySelectorAll('[editor-block-container-idx]');

                for (var i = 0; i < nodes.length; i++) {
                    var block_container = block_container_array[nodes[i].getAttribute('editor-block-container-idx')];

                    var marker = nodes[i].parentNode;

                    marker.parentNode.insertBefore(block_container, marker);
                    marker.parentNode.removeChild(marker);

                    this._normalizeBlock(block_container);
                }
            }

            node.removeAttribute('editor-normalizing');

            this._outdentChildBlocks(node);
        };

        this._normalizeList = function (list) {
            var nodes = this._nodeGetChildrenArray(list);

            for (var i = 0; i < nodes.length; i++) {
                var node = nodes[i];
                var node_name = node.nodeName.toLowerCase();

                if (node_name === 'li') {
                    continue;
                }

                if (['ul', 'ol'].indexOf(node_name) >= 0) {
                    if (!node.previousSibling) {
                        $('<li />').insertBefore(node).append(node);
                    } else {
                        if (['ul', 'ol'].indexOf(node.previousSibling.lastChild.nodeName.toLowerCase()) >= 0) {
                            while (node.firstChild) {
                                node.previousSibling.lastChild.appendChild(node.firstChild);
                            }

                            list.removeChild(node);
                        } else {
                            node.previousSibling.appendChild(node);
                        }
                    }

                    continue;
                }

                if (this._nodeIsBlock(node)) {
                    var new_node = document.createElement('li');

                    list.insertBefore(new_node, node);

                    while (node.firstChild) {
                        new_node.appendChild(node.firstChild);
                    }

                    list.removeChild(node);

                    continue;
                }

                if (!node.previousSibling) {
                    list.insertBefore(document.createElement('li'), node);
                }

                node.previousSibling.appendChild(node);
            }

            var nodes = this._nodeGetChildrenArray(list);

            for (var i = 0; i < nodes.length; i++) {
                this._normalizeBlock(nodes[i]);
            }
        };

        this._normalizeTable = function (table) {
            var tbody = document.createElement('tbody');

            var rows = [];

            for (var x = 0; x < table.childNodes.length; x++) {
                if (table.childNodes[x].nodeName.toLowerCase() === 'tr') {
                    rows.push(table.childNodes[x]);
                    continue;
                }

                for (var y = 0; y < table.childNodes[x].childNodes.length; y++) {
                    if (table.childNodes[x].childNodes[y].nodeName.toLowerCase() === 'tr') {
                        rows.push(table.childNodes[x].childNodes[y]);
                    }
                }
            }

            var cells = [];

            for (var i = 0; i < rows.length; i++) {
                var node = rows[i].firstChild;

                while (node) {
                    var node_name = node.nodeName.toLowerCase();

                    if (['td', 'th'].indexOf(node_name) < 0) {
                        var next_node = node.nextSibling;
                        rows[i].removeChild(node);
                        node = next_node;
                        continue;
                    }

                    if (node_name === 'th') {
                        var new_node = document.createElement('td');

                        while (node.firstChild) {
                            new_node.appendChild(node.firstChild);
                        }

                        this._nodeCopyAttributes(node, new_node);

                        rows[i].insertBefore(new_node, node);
                        rows[i].removeChild(node);

                        node = new_node;
                    }

                    cells.push(node);

                    node = node.nextSibling;
                }

                tbody.appendChild(rows[i]);
            }

            table.innerHTML = '';

            table.appendChild(tbody);

            for (var i = 0; i < cells.length; i++) {
                var wrap = document.createElement('div');

                while (cells[i].firstChild) {
                    wrap.appendChild(cells[i].firstChild);
                }

                cells[i].appendChild(wrap);

                this._normalizeBlock(wrap);
            }
        };

        this._normalizeBlockRecursive = function (root_node, table_array, list_array, block_container_array) {
            var node = root_node.querySelector(this._block_element_names.join(':not([editor-skip-block]),') + ':not([editor-skip-block])');

            if (!node) {
                return;
            }

            if (!this._nodeIsBlock(node.parentNode)) {
                var parent_block_node = this._nodeGetBlock(node.parentNode);

                node.parentNode.insertBefore(this._makeCaretMarker('normalize-block', true), node);
                node.parentNode.removeChild(node);

                this._moveCaretToMarker('normalize-block');

                var new_node = this._splitBlock(node);

                if (parent_block_node.hasAttribute('editor-normalizing') && new_node) {
                    this._normalizeBlock(node);
                    this._normalizeBlock(new_node);
                }
            } else {
                var node_name = node.nodeName.toLowerCase();

                if (node_name === 'table') {
                    table_array.push(node);

                    var marker_wrap = document.createElement('p');
                    marker_wrap.setAttribute('editor-skip-block', 1);

                    var marker = document.createElement('span');
                    marker.setAttribute('editor-table-idx', table_array.length - 1);
                    marker.appendChild(document.createTextNode("\uFEFF"));
                    marker.normalize();

                    marker_wrap.appendChild(marker);

                    node.parentNode.insertBefore(marker_wrap, node);
                    node.parentNode.removeChild(node);
                } else if (node_name === 'ol' || node_name === 'ul') {
                    list_array.push(node);

                    var marker_wrap = document.createElement('p');
                    marker_wrap.setAttribute('editor-skip-block', 1);

                    var marker = document.createElement('span');
                    marker.setAttribute('editor-list-idx', list_array.length - 1);
                    marker.appendChild(document.createTextNode("\uFEFF"));
                    marker.normalize();

                    marker_wrap.appendChild(marker);

                    node.parentNode.insertBefore(marker_wrap, node);
                    node.parentNode.removeChild(node);
                } else if (this._nodeIsBlockContainer(node)) {
                    block_container_array.push(node);

                    var marker_wrap = document.createElement('p');
                    marker_wrap.setAttribute('editor-skip-block', 1);

                    var marker = document.createElement('span');
                    marker.setAttribute('editor-block-container-idx', block_container_array.length - 1);
                    marker.appendChild(document.createTextNode("\uFEFF"));
                    marker.normalize();

                    marker_wrap.appendChild(marker);

                    node.parentNode.insertBefore(marker_wrap, node);
                    node.parentNode.removeChild(node);
                } else {
                    node.setAttribute('editor-skip-block', 1);
                    this._outdentChildBlocks(node);
                }
            }

            this._normalizeBlockRecursive(root_node, table_array, list_array, block_container_array);
        };

        this._outdentChildBlocks = function (node) {
            var self = this, node_name = node.nodeName.toLowerCase(), child_node, child_node_name, new_list, break_flag, new_node = null;

            node = $(node);

            if (node_name !== 'li') {
                if (this._allowed_block_element_names.indexOf(node_name) === -1) {
                    node_name = this._getDefaultBlockNodeName(this._nodeGetBlockContainer(node[0]));
                }

                node.contents().each(function () {
                    if (!self._nodeIsBlock(this)) {
                        if (new_node === null) {
                            new_node = $('<' + node_name + ' />');

                            self._nodeCopyAttributes(node[0], new_node.get(0));

                            node.before(new_node);
                        }

                        new_node.append(this);

                        return true;
                    }

                    node.before(this);
                    new_node = null;
                });

                node.remove();

                return;
            }

            node.contents().each(function () {
                if (this.nodeType === 3 || !self._nodeIsBlock(this)) {
                    return true;
                }

                child_node = $(this);

                child_node_name = this.nodeName.toLowerCase();

                if (child_node_name === 'table') {
                    return true;
                } else if (child_node_name !== 'ul' && child_node_name !== 'ol') {
                    if (this.previousSibling && this.previousSibling.nodeName.toLowerCase() !== 'br') {
                        child_node.before($('<br />'));
                    }

                    child_node.before(child_node.contents());

                    if (this.nextSibling && this.nextSibling.nodeName.toLowerCase() !== 'br' && !self._nodeIsBlock(this.nextSibling)) {
                        child_node.before($('<br />'));
                    }

                    child_node.remove();

                    return true;
                }

                if (this.previousSibling) {
                    new_list = $('<li />').insertBefore(node);

                    node.contents().each(function () {
                        break_flag = this === child_node.get(0);

                        new_list.append(this);

                        if (break_flag) {
                            return false;
                        }
                    });

                    return true;
                }

                if (node.prev()[0]) {
                    if (node.prev().find('> :last').is('ul,ol')) {
                        node.prev().find('> :last').append(child_node.contents());
                        child_node.remove();
                    } else {
                        node.prev().append(child_node);
                    }

                    return true;
                }

                node.before(child_node.contents());
                child_node.remove();
            });

            if (node.contents().length < 1) {
                node.remove();
            }
        };

        /**
         * 
         * @param Node ancestor
         * @param Node descendant
         * @returns {Number}
         */
        this._nodeIsDescendant = function (ancestor, descendant) {
            return (descendant.compareDocumentPosition(ancestor) & Node.DOCUMENT_POSITION_CONTAINS) === Node.DOCUMENT_POSITION_CONTAINS;
        };

        /**
         * 
         * @param Node descendant
         * @param Node ancestor
         * @returns {Number}
         */
        this._nodeIsAncestor = function (descendant, ancestor) {
            return (ancestor.compareDocumentPosition(descendant) & Node.DOCUMENT_POSITION_CONTAINED_BY) === Node.DOCUMENT_POSITION_CONTAINED_BY;
        };

        this._nodeGetChildrenArray = function (node, filter_callback) {
            if (!filter_callback) {
                return [].slice.call(node.childNodes);
            }

            var children = [];

            for (var i = 0; i < node.childNodes.length; i++) {
                if (!filter_callback || filter_callback.apply(this, [node.childNodes[i]])) {
                    children.push(node.childNodes[i]);
                }
            }

            return children;
        };

        this._nodeInsertAfter = function (new_node, refer_node) {
            if (refer_node.nextSibling) {
                refer_node.parentNode.insertBefore(new_node, refer_node.nextSibling);
            } else {
                refer_node.parentNode.appendChild(new_node);
            }
        };

        this._nodeCopyToClipboard = function (node) {
            var start_node = this._nodeGetBlock(this._getSelectionNode());
            var end_node = this._nodeGetBlock(this._getSelectionNode(true));

            if (start_node === end_node) {
                var saved_selection = this._saveSelectionRange(start_node);
            } else {
                var saved_selection = this._saveSelectionRange(this._getCommonAncestor(start_node, end_node));
            }

            this._selectNode(node, false, false, false);

            this._execStandardCommand('copy');

            this._setSelectionRange(saved_selection);
        };

        this._nodeCopyAttributes = function (src_node, dest_node) {
            for (var i = 0; i < src_node.attributes.length; i++) {
                dest_node.setAttribute(src_node.attributes[i].name, src_node.attributes[i].value);
            }
        };

        this._nodeIsEditorElement = function (node) {
            var self = this;
            return this._getParentNode(node, null, function (n) {
                return n.hasAttribute('editor-element');
            }, null, function (n) {
                return self._nodeIsBlockContainer(n) && !n.hasAttribute('editor-element');
            }) !== null;
        };

        this._nodeSetEditorElement = function (node, flag) {
            if (flag) {
                node.setAttribute('editor-element', 1);
            } else {
                node.removeAttribute('editor-element');
            }
        };

        this._nodeIsEditarea = function (node) {
            return node && node.nodeType === Node.ELEMENT_NODE && this._editarea[0] === node;
        };

        this._nodeIsBlockContainer = function (node) {
            if (!node || node.nodeType !== Node.ELEMENT_NODE) {
                return false;
            }

            return this._nodeIsEditarea(node) || node.hasAttribute('osc-editor-block-container') || ['li', 'td'].indexOf(node.nodeName.toLowerCase()) >= 0;
        };

        this._nodeMarkAsBlockContainer = function (node, flag) {
            if (this._nodeIsEditarea(node) || ['li', 'td'].indexOf(node.nodeName.toLowerCase()) >= 0) {
                return;
            }

            if (flag) {
                node.setAttribute('osc-editor-block-container', 1);
            } else {
                node.removeAttribute('osc-editor-block-container');
            }
        };

        this._nodeIsBlock = function (node) {
            return node && node.nodeType === Node.ELEMENT_NODE && this._block_element_names.indexOf(node.nodeName.toLowerCase()) >= 0;
        };

        this._nodeIsTextBlock = function (node) {
            return node && node.nodeType === Node.ELEMENT_NODE && this._text_block_element_names.indexOf(node.nodeName.toLowerCase()) >= 0;
        };

        this._nodeIsEmptyBlock = function (block) {
            if (this._nodeIsEditorElement(block)) {
                return false;
            }

            if (block.childNodes.length > 1) {
                return false;
            }

            if (block.childNodes.length === 1 && ((block.firstChild.nodeType === Node.ELEMENT_NODE && block.firstChild.nodeName.toLowerCase() !== 'br') || (block.firstChild.nodeType === Node.TEXT_NODE && block.firstChild.nodeValue.trim() !== ''))) {
                return false;
            }

            return true;
        };

        this._nodeGetParentList = function (node) {
            var list_item = this._nodeGetParentListItem(node);

            return list_item && ['ul', 'ol'].indexOf(list_item.parentNode.nodeName.toLowerCase()) >= 0 ? list_item.parentNode : null;
        };

        this._nodeGetParentListItem = function (node) {
            var self = this;

            return this._getParentNode(node, 'li', null, null, function (node) {
                return self._nodeIsBlockContainer(node);
            });
        };

        this._nodeIsInList = function (node) {
            return this._nodeGetParentList(node) !== null;
        };

        this._nodeGetBlock = function (node) {
            var self = this;

            return this._getParentNode(node, this._block_element_names.join(','), null, null, function (node) {
                return self._nodeIsBlockContainer(node);
            });
        };

        this._nodeGetBlockContainer = function (node) {
            while (node && !this._nodeIsBlockContainer(node)) {
                node = node.parentNode;
            }

            return this._nodeIsBlockContainer(node) ? node : this._editarea[0];
        };

        this._getEndNode = function (node) {
            if (this._nodeIsBlockContainer(node)) {
                return null;
            }

            while (!this._nodeIsBlockContainer(node.parentNode)) {
                node = node.parentNode;
            }

            return node;
        };

        this._makeCaretMarker = function (id, get_real_node) {
            id = id ? id : '';

            if (!get_real_node) {
                return '<span editor-caret-marker="' + id + '">&#xFEFF;</span>';
            }

            var marker = document.createElement('span');
            marker.setAttribute('editor-caret-marker', id);

            marker.appendChild(document.createTextNode('\uFEFF'));
            marker.normalize();

            return marker;
        };

        this._moveCaretToMarker = function (id) {
            var nodes = this._editarea[0].getElementsByTagName('span');

            id = id ? id : '';

            var markers = [];

            for (var i = 0; i < nodes.length; i++) {
                var node = nodes[i];

                if (!node.hasAttribute('editor-caret-marker')) {
                    continue;
                }

                if (node.getAttribute('editor-caret-marker') !== id) {
                    continue;
                }

                markers.push(node);
            }

            if (markers[0]) {
                this._selectNode(markers[markers.length - 1], false, false, null);
                this._execStandardCommand('delete');

                for (var i = 0; i < markers.length; i++) {
                    if (markers[i].parentNode) {
                        markers[i].parentNode.removeChild(markers[i]);
                    }
                }
            }
        };

        this._save_selection_timer = null;

        this._backupSelection = function () {
            clearTimeout(this._save_selection_timer);

            var self = this;

            this._save_selection_timer = setTimeout(function () {
                self._saved_selection = self._getRange().cloneRange();
            }, 10);
        };

        this._restoreSelection = function () {
            if (this._saved_selection && !this._disable_restore_selection) {
                var selection = window.getSelection();

                selection.removeAllRanges();
                selection.addRange(this._saved_selection);

                this._setContext();
            } else {
                this._saved_selection = null;
            }
        };

        this._getRange = function () {
            var selection = window.getSelection();

            if (selection.rangeCount > 0) {
                return selection.getRangeAt(0);
            }

            var range = document.createRange();

            selection.removeAllRanges();
            selection.addRange(range);

            return range;
        };

        this._caretInStartOfBlock = function () {
            var range = this._getRange();
            var block = this._nodeGetBlock(this._getSelectionNode(true));

            if (!block) {
                return false;
            }

            var test_range = range.cloneRange();

            test_range.selectNodeContents(block);
            test_range.setEnd(range.startContainer, range.startOffset);

            return test_range.toString().trim() === '';
        };

        this._caretInEndOfBlock = function () {
            var range = this._getRange();
            var block = this._nodeGetBlock(this._getSelectionNode(true));

            if (!block) {
                return false;
            }

            var test_range = range.cloneRange();

            test_range.selectNodeContents(block);
            test_range.setStart(range.endContainer, range.endOffset);

            return test_range.toString().trim() === '';
        };

        this._caretGetPositionFlag = function () {
            return this._caretInEndOfBlock() ? 1 : (this._caretInStartOfBlock() ? -1 : 0);
        };

        this._selectionIsBackward = function () {
            var selection = window.getSelection();

            if (!selection.anchorNode) {
                return false;
            }

            var position = selection.anchorNode.compareDocumentPosition(selection.focusNode);

            return !position && selection.anchorOffset > selection.focusOffset || position === Node.DOCUMENT_POSITION_PRECEDING;
        };

        /**
         * 
         * @param {String} flag Flags: start [default], end, anchor, focus<br />Boolean value: true as END, false as START
         * @param {Boolean} get_orig_flag
         * @returns {Element}
         */
        this._getSelectionNode = function (flag, get_orig_flag) {
            if (typeof flag !== 'string') {
                flag = !flag ? 'start' : 'end';
            } else {
                flag = flag.toLowerCase().trim();

                if (flag !== 'end' || flag !== 'focus' || flag !== 'anchor') {
                    flag = 'start';
                }
            }

            var backward_selection = this._selectionIsBackward();

            var node = window.getSelection()[(flag === 'focus' || (flag === 'end' && !backward_selection) || (flag === 'start' && backward_selection)) ? 'focusNode' : 'anchorNode'];

            node = !get_orig_flag && node && node.nodeType === 3 ? node.parentNode : node;

            if (!node) {
                return node;
            }

            return node === this._editarea[0] || this._nodeIsAncestor(node, this._editarea[0]) ? node : null;
        };

        /**
         * 
         * @param {String} flag Flags: start [default], end, anchor, focus<br />Boolean value: true as END, false as START
         * @returns {Element}
         */
        this._getSelectionOffset = function (flag) {
            if (typeof flag !== 'string') {
                flag = !flag ? 'start' : 'end';
            } else {
                flag = flag.toLowerCase().trim();

                if (flag !== 'end' || flag !== 'focus' || flag !== 'anchor') {
                    flag = 'start';
                }
            }

            var backward_selection = this._selectionIsBackward();

            return window.getSelection()[(flag === 'focus' || (flag === 'end' && !backward_selection) || (flag === 'start' && backward_selection)) ? 'focusOffset' : 'anchorOffset'];
        };

        this._getSelectionCommonAncestor = function () {
            return this._getCommonAncestor(this._getSelectionNode(), this._getSelectionNode(true));
        };

        this._getCommonAncestor = function () {
            if (arguments.length < 1) {
                return null;
            } else if (arguments.length < 2) {
                return arguments[0];
            }

            var common_ancestor_node = null, node;

            for (var i = 0; i < arguments.length; i++) {
                node = arguments[i];

                if (!node) {
                    continue;
                }

                if (node.nodeType === Node.TEXT_NODE) {
                    node = node.parentNode;
                }

                if (!common_ancestor_node) {
                    common_ancestor_node = node;
                    continue;
                }
                /*
                 if (node === common_ancestor_node) {
                 continue;
                 }
                 
                 while (((common_ancestor_node.compareDocumentPosition(node) & Node.DOCUMENT_POSITION_CONTAINED_BY) !== Node.DOCUMENT_POSITION_CONTAINED_BY) && common_ancestor_node !== node) {
                 common_ancestor_node = common_ancestor_node.parentNode;
                 
                 if (!common_ancestor_node) {
                 return null;
                 }
                 }*/

                while (((common_ancestor_node.compareDocumentPosition(node) & Node.DOCUMENT_POSITION_CONTAINED_BY) !== Node.DOCUMENT_POSITION_CONTAINED_BY) && common_ancestor_node !== node) {
                    common_ancestor_node = common_ancestor_node.parentNode;

                    if (!common_ancestor_node) {
                        return null;
                    }
                }
            }

            return common_ancestor_node;
        };

        this._splitBlock = function (additional_block, additional_block_in_left_if_split_failed) {
            if (!window.getSelection().isCollapsed) {
                this._execStandardCommand('delete');
            }

            var orig_block = this._getEndNode(this._getSelectionNode());

            if (!orig_block) {
                if (additional_block) {
                    this._editarea[0].appendChild(additional_block);
                }

                return null;
            }

            if (!this._nodeIsBlock(orig_block)) {
                var orig_block = this._resolveOutsideBlockContent(orig_block);
            }

            var new_block = null;

            if (orig_block.childNodes.length > 1 || (orig_block.childNodes.length === 1 && orig_block.childNodes[0].nodeName.toLowerCase() !== 'br')) {
                if (this._caretInStartOfBlock()) {
                    additional_block_in_left_if_split_failed = true;
                } else if (this._caretInEndOfBlock()) {
                    additional_block_in_left_if_split_failed = false;
                } else {
                    new_block = orig_block.cloneNode();

                    new_block.appendChild(this._makeCaretMarker('split-block', true));
                    new_block.appendChild(this._extractContentFromCaret());

                    this._nodeInsertAfter(new_block, orig_block);
                }
            } else {
                this._commandInsertHTML(this._makeCaretMarker('split-block'));
            }

            if (additional_block) {
                var insert_after_flag = additional_block_in_left_if_split_failed && !new_block ? false : true;

                if (orig_block.nodeName.toLowerCase() === 'li' && additional_block.nodeName.toLowerCase() !== 'li') {
                    var new_li = document.createElement('li');
                    new_li.innerHTML = 'x';

                    if (insert_after_flag) {
                        this._nodeInsertAfter(new_li, orig_block);
                    } else {
                        orig_block.parentNode.insertBefore(new_li, orig_block);
                    }

                    var block = this._formatBlock(new_li, 'div');

                    block.parentNode.insertBefore(additional_block, block);

                    block.parentNode.removeChild(block);
                } else {
                    if (insert_after_flag) {
                        this._nodeInsertAfter(additional_block, orig_block);
                    } else {
                        orig_block.parentNode.insertBefore(additional_block, orig_block);
                    }
                }
            }

            this._moveCaretToMarker('split-block');

            return new_block;
        };

        this._getTextNodesIn = function (node) {
            return this._getNodeTree(node, [], 3);
        };

        this._getCaretTextNode = function (node, offset) {
            var data = {node: node, offset: offset};

            if (node.nodeType === 3) {
                return data;
            }

            var text_nodes = this._getTextNodesIn(node);

            for (var i = 0; i < text_nodes.length; i++) {
                data.node = text_nodes[i];

                if (data.offset - data.node.length <= 0) {
                    break;
                }

                data.offset -= data.node.length;
            }

            return data;
        };

        this._getCaretOffsetInNode = function (node, flag_node, offset) {
            var range = document.createRange();

            range.selectNodeContents(node);

            var text_node = this._getCaretTextNode(flag_node, offset);

            range.setEnd(text_node.node, text_node.offset);

            return range.toString().length;
        };

        this._saveSelectionRange = function (parent) {
            this._checkFocus();

            if (!parent) {
                parent = this._getSelectionCommonAncestor();

                if (!parent) {
                    parent = this._editarea[0];
                }
            }

            if (parent !== this._editarea[0] && !this._nodeIsAncestor(parent, this._editarea[0])) {
                parent = this._editarea[0];
            }

            parent.normalize();

            var start_node = this._getSelectionNode(false, true);
            var start_offset = this._getSelectionOffset();
            var start_text_length = (start_node.nodeType === Node.TEXT_NODE ? start_node.nodeValue : start_node.textContent).length;

            var end_node = this._getSelectionNode(true, true);
            var end_offset = this._getSelectionOffset(true);
            var end_text_length = (end_node.nodeType === Node.TEXT_NODE ? end_node.nodeValue : end_node.textContent).length;

            return {
                node: parent,
                start_offset_position_flag: start_offset === 0 ? -1 : (start_offset === start_text_length ? 1 : 0),
                start_offset: this._getCaretOffsetInNode(parent, start_node, start_offset),
                end_offset_position_flag: end_offset === 0 ? -1 : (end_offset === end_text_length ? 1 : 0),
                end_offset: this._getCaretOffsetInNode(parent, end_node, end_offset)
            };
        };

        this._setSelectionRange = function (saved_selection) {
            saved_selection.node.normalize();

            var range = document.createRange();

            range.selectNodeContents(saved_selection.node);

            var text_nodes = this._getTextNodesIn(saved_selection.node);

            var char_count = 0;

            var found_start = false;

            for (var i = 0; i < text_nodes.length; i++) {
                var text_node = text_nodes[i];

                char_count += text_node.length;

                if (!found_start) {
                    if (i < text_nodes.length - 1) {
                        if (char_count < saved_selection.start_offset) {
                            continue;
                        }

                        if (saved_selection.start_offset_position_flag !== 1 && char_count === saved_selection.start_offset) {
                            continue;
                        }
                    }

                    found_start = true;
                    range.setStart(text_node, Math.max(0, Math.min(text_node.length, text_node.length - (char_count - saved_selection.start_offset))));
                }

                if (found_start) {
                    if (i < text_nodes.length - 1) {
                        if (char_count < saved_selection.end_offset) {
                            continue;
                        }

                        if (saved_selection.end_offset_position_flag !== 1 && char_count === saved_selection.end_offset) {
                            continue;
                        }
                    }

                    range.setEnd(text_node, Math.max(0, Math.min(text_node.length, text_node.length - (char_count - saved_selection.end_offset))));
                    break;
                }
            }

            var selection = window.getSelection();

            selection.removeAllRanges();
            selection.addRange(range);
        };

        this._getSelectedNodes = function (f, range) {
            var node, end_node, range_nodes = [];

            if (!range) {
                range = this._getRange();
            }

            node = range.startContainer;
            end_node = range.endContainer;

            if (node === end_node) {
                if (!f || f(node) !== false) {
                    range_nodes.push(node);
                }

                return range_nodes;
            }

            function __nextNode(node) {
                if (node.hasChildNodes()) {
                    return node.firstChild;
                } else {
                    while (node && !node.nextSibling) {
                        node = node.parentNode;
                    }

                    if (!node) {
                        return null;
                    }

                    return node.nextSibling;
                }
            }

            while (node && node !== end_node) {
                node = __nextNode(node);

                if (!f || f(node) !== false) {
                    range_nodes.push(node);
                }
            }

            node = range.startContainer;

            while (node && node !== range.commonAncestorContainer) {
                if (!f || f(node) !== false) {
                    range_nodes.unshift(node);
                }

                node = node.parentNode;
            }

            return range_nodes;
        };

        /**
         * 
         * @param {Element} node
         * @param {String} parent_tag
         * @param {Function} checker_callback
         * @param {String} break_element
         * @param {Mixed} breaker
         * @returns {Element}
         */
        this._getParentNode = function (node, parent_tag, checker_callback, break_element, breaker) {
            var rx = parent_tag ? new RegExp('^(' + parent_tag.toUpperCase().replace(/,/g, '|') + ')$') : 0, break_tag;

            if (breaker) {
                if (typeof breaker === 'string') {
                    break_tag = breaker;

                    breaker = function (node) {
                        return (new RegExp('^(' + break_tag.toUpperCase().replace(/,/g, '|') + ')$')).test(node.nodeName);
                    };
                }
            }

            function _checkNode(node) {
                return ((node.nodeType === 1 && !rx) || (rx && rx.test(node.nodeName))) && (!checker_callback || checker_callback(node));
            }

            while (node && !this._nodeIsEditarea(node)) {
                if (_checkNode(node)) {
                    return node;
                }

                if (node === break_element) {
                    return null;
                } else if (breaker && breaker(node)) {
                    return null;
                }

                node = node.parentNode;
            }

            return null;
        };

        this._getAnchorNodesInRange = function () {
            return this._getSelectedNodes(function (node) {
                return node.nodeName.toLowerCase() === 'a' && node.hasAttribute('id') && !node.hasAttribute('href');
            });
        };

        this._getLinkNodesInRange = function () {
            var links = this._getSelectedNodes(function (node) {
                return node.nodeName.toLowerCase() === 'a' && node.hasAttribute('href');
            });

            if (links.length < 1) {
                var link = this._getParentNode(this._getSelectionNode(true), 'a', function (node) {
                    return node.nodeName.toLowerCase() === 'a' && node.hasAttribute('href');
                });

                if (link) {
                    links.push(link);
                }
            }

            return links;
        };

        this._selectNode = function (node, collapse, select_text, to_start, skip_scroll_to_node) {
            var selection, range;

            if (!node) {
                return false;
            }

            if (typeof collapse === 'undefined') {
                collapse = true;
            }

            if (typeof to_start === 'undefined') {
                to_start = true;
            }

            selection = window.getSelection();

            range = document.createRange();

            if (select_text) {
                range.selectNodeContents(node);
            } else {
                range.selectNode(node);
            }

            if (collapse) {
                if (!to_start && node.nodeType === 3) {
                    range.setStart(node, node.nodeValue.length);
                    range.setEnd(node, node.nodeValue.length);
                } else {
                    range.collapse(to_start);
                }
            }

            selection.removeAllRanges();
            selection.addRange(range);

            if (!skip_scroll_to_node) {
                this._scrollToNode(node);
            }
        };

        this._extractContentFromCaret = function (extract_left) {
            var range = this._getRange(), clone_range = range.cloneRange();

            var block_node = this._nodeGetBlock(range[extract_left ? 'startContainer' : 'endContainer']);

            if (!block_node) {
                return null;
            }

            clone_range.selectNodeContents(block_node);

            if (extract_left) {
                clone_range.setEnd(range.startContainer, range.startOffset);
            } else {
                clone_range.setStart(range.endContainer, range.endOffset);
            }

            return clone_range.extractContents();
        };

        this._getNodeTree = function (n, na, t, nn) {
            return this._selectNodes(n, function (n) {
                return (!t || n.nodeType === t) && (!nn || n.nodeName === nn);
            }, na ? na : []);
        };

        this._selectNodes = function (n, f, a) {
            var i;

            if (!a) {
                a = [];
            }

            if (f(n)) {
                a[a.length] = n;
            }

            if (n.hasChildNodes()) {
                for (i = 0; i < n.childNodes.length; i++) {
                    this._selectNodes(n.childNodes[i], f, a);
                }
            }

            return a;
        };

        this._scrollToNode = function (node) {
            var scroller_boundary = this._getScrollerBoundary();
            var editarea_boundary = $.extend({}, this._editarea[0].getBoundingClientRect());

            if (editarea_boundary.top > scroller_boundary.bottom || editarea_boundary.bottom < scroller_boundary.top) {
                this.scroller.scrollTop(Math.abs(editarea_boundary.top - scroller_boundary.top + this.scroller.scrollTop()));
            }

            if (editarea_boundary.left > scroller_boundary.right || editarea_boundary.right < scroller_boundary.left) {
                this.scroller.scrollLeft(Math.abs(editarea_boundary.left - scroller_boundary.left + this.scroller.scrollLeft()));
            }

            editarea_boundary = $.extend({}, this._editarea[0].getBoundingClientRect());

            var node_boundary = $.extend({}, node.getBoundingClientRect());

            if (node_boundary.top < editarea_boundary.top || node_boundary.top > editarea_boundary.bottom) {
                this._editarea.scrollTop(Math.abs(node_boundary.top - editarea_boundary.top + this._editarea.scrollTop()));
            }

            if (node_boundary.left < editarea_boundary.left || node_boundary.left > editarea_boundary.right) {
                this._editarea.scrollLeft(Math.abs(node_boundary.left - editarea_boundary.left + this._editarea.scrollLeft()));
            }

            node_boundary = $.extend({}, node.getBoundingClientRect());

            var editarea_viewport_boundary = this._getEditareaViewportBoundary();

            if (editarea_viewport_boundary && (node_boundary.top < editarea_viewport_boundary.top || node_boundary.top > editarea_viewport_boundary.bottom)) {
                this.scroller.scrollTop(Math.abs(node_boundary.top - scroller_boundary.top + this.scroller.scrollTop()) - editarea_viewport_boundary.top);
            }
        };

        this._collapseRange = function (to_start) {
            var node = this._getSelectionNode(!to_start);
            var block = this._nodeGetBlock(node);
            var offset = this._getCaretOffsetInNode(block, node, this._getSelectionOffset(!to_start));

            var text_node = this._getCaretTextNode(block, offset);

            window.getSelection().collapse(text_node.node, text_node.offset);
        };

        this._resolveLastNonEditableNode = function () {
            var root_node = this._nodeGetBlockContainer(this._getSelectionNode());

            if (root_node && root_node.isContentEditable && root_node.lastChild && root_node.lastChild.nodeType === Node.ELEMENT_NODE && (!root_node.lastChild.isContentEditable || this._nodeIsBlockContainer(root_node.lastChild))) {
                root_node.appendChild(document.createTextNode("\uFEFF"));
                root_node.normalize();
            }

            if (root_node !== this._editarea[0] && this._editarea[0].lastChild.nodeType === Node.ELEMENT_NODE && (!this._editarea[0].lastChild.isContentEditable || this._nodeIsBlockContainer(this._editarea[0].lastChild))) {
                this._editarea[0].appendChild(document.createTextNode("\uFEFF"));
                this._editarea[0].normalize();
            }
        };

        this._resolveOutsideBlockContent = function (node, block_node_name) {
            if (!node) {
                node = this._getSelectionNode(false, true);

                if (!node || node === this._editarea[0]) {
                    if (this._editarea[0].lastChild && this._editarea[0].lastChild.isContentEditable) {
                        node = this._editarea[0].lastChild;
                    } else {
                        node = document.createElement(this._getDefaultBlockNodeName());
                        node.appendChild(document.createElement('br'));

                        this._editarea[0].appendChild(node);

                        this._selectNode(node, true, true, true);

                        return;
                    }
                }
            }

            if (!node) {
                return null;
            }

            var end_node = this._getEndNode(node);

            if (!end_node) {
                return null;
            }

            if (this._nodeIsBlock(end_node)) {
                return end_node;
            }

            var saved_selection = this._saveSelectionRange(this._nodeGetBlockContainer(end_node));

            var block = document.createElement(typeof block_node_name === 'string' ? block_node_name : this._getDefaultBlockNodeName(end_node.parentNode));

            end_node.parentNode.insertBefore(block, end_node);

            block.appendChild(end_node);

            while (block.nextSibling && !this._nodeIsBlock(block.nextSibling)) {
                block.appendChild(block.nextSibling);
            }

            while (block.previousSibling && !this._nodeIsBlock(block.previousSibling)) {
                block.insertBefore(block.previousSibling, block.firstChild);
            }

            this._setSelectionRange(saved_selection);

            return block;
        };

        this.setContent = function (content) {
            this._checkFocus();

            this._historyAdd();

            var div = document.createElement('div');

            div.innerHTML = content;

            this._editarea[0].innerHTML = '';

            while (div.firstChild) {
                this._editarea[0].appendChild(div.firstChild);
            }

            this._normalize(this._editarea[0]);
        };

        this.getContent = function () {
            var wrap = this._editarea[0].cloneNode(true);

            this._trimEmptyBlock(wrap);

            return wrap.innerHTML;
        };

        this._trimEmptyBlock = function (block_container) {
            var child_keys = ['firstChild', 'lastChild'];

            for (var x = 0; x < child_keys.length; x++) {
                var child_key = child_keys[x];

                while (block_container[child_key]) {
                    if (!this._nodeIsEmptyBlock(block_container[child_key])) {
                        break;
                    }

                    block_container.removeChild(block_container[child_key]);
                }
            }

            for (var x = 0; x < block_container.childNodes.length; x++) {
                var child = block_container.childNodes[x];

                var node_name = child.nodeName.toLowerCase();

                if (['ul', 'ol'].indexOf(node_name) >= 0) {
                    for (var y = 0; y < child.childNodes.length; y++) {
                        this._trimEmptyBlock(child.childNodes[y]);
                    }

                    continue;
                }

                if (node_name === 'table') {
                    for (var y = 0; y < child.childNodes.length; y++) {
                        var tbody = child.childNodes[y];

                        for (var z = 0; z < tbody.childNodes.length; z++) {
                            var row = tbody.childNodes[z];

                            for (var k = 0; k < row.childNodes.length; k++) {
                                this._trimEmptyBlock(row.childNodes[k]);
                            }
                        }
                    }

                    continue;
                }
            }
        };

        /**
         * 
         * @param Node node
         * @param string node_name
         * @param boolean outdent_list
         * @returns Node
         */
        this._formatBlock = function (node, node_name, break_if_same_node) {
            node = this._nodeGetBlock(node);

            if (!node.isContentEditable) {
                return node;
            }

            if (node_name.toLowerCase() === node.nodeName.toLowerCase()) {
                if (break_if_same_node) {
                    return node;
                }

                node_name = this._getDefaultBlockNodeName(node.parentNode);
            }

            node = $(node);

            var new_node = $('<' + node_name + ' />').append(node.contents()).insertAfter(node);

            node.remove();

            return new_node[0];
        };

        this._setContext = function () {
            this._resolveLastNonEditableNode();
            this._resolveOutsideBlockContent();

            for (var i in this._context_data) {
                if (this._context_data[i].context_callback) {
                    var context_state = this[this._context_data[i].context_callback].apply(this, this._context_data[i].context_arg);
                } else {
                    var context_state = document.queryCommandState(this._context_data[i].context);
                }

                if (this._context_data[i].state_callback) {
                    for (var k = 0; k < this._context_data[i].nodes.length; k++) {
                        this[this._context_data[i].state_callback].apply(this, [this._context_data[i].nodes[k], context_state]);
                    }
                } else {
                    for (var k = 0; k < this._context_data[i].nodes.length; k++) {
                        var node = this._context_data[i].nodes[k];
                        node.className = node.className.replace(/(^|\s+)active($|\s+)/i, '$1$2').replace(/\s{2,}/g, ' ') + (context_state ? ' active' : '');
                    }
                }
            }

            for (var i in this._context_callback) {
                this._context_callback[i].apply(this);
            }

            this._backupSelection();
        };


        this._contextBlockname = function (blocknames) {
            var start_node = this._nodeGetBlock(this._getSelectionNode());
            var end_node = this._nodeGetBlock(this._getSelectionNode(true));

            if (!start_node) {
                return false;
            }

            if (!Array.isArray(blocknames)) {
                blocknames = [(blocknames + '').trim().toLowerCase()];
            } else {
                for (var i = 0; i < blocknames.length; i++) {
                    blocknames[i] = (blocknames[i] + '').trim().toLowerCase();
                }
            }

            if (start_node === end_node) {
                return blocknames.indexOf(start_node.nodeName.toLowerCase()) >= 0;
            }

            start_node.setAttribute('editor-node-marker', 1);
            end_node.setAttribute('editor-node-marker', 1);

            var blocks = this._getCommonAncestor(start_node, end_node).querySelectorAll(this._block_element_names.join(','));

            var found_counter = 0;
            var checker_flag = true;

            for (var i = 0; i < blocks.length; i++) {
                if (blocks[i].hasAttribute('editor-node-marker')) {
                    found_counter++;
                    blocks[i].removeAttribute('editor-node-marker');
                }

                if (found_counter > 0 && blocknames.indexOf(start_node.nodeName.toLowerCase()) < 0) {
                    checker_flag = false;
                }

                if (found_counter === 2) {
                    break;
                }
            }

            start_node.removeAttribute('editor-node-marker');
            end_node.removeAttribute('editor-node-marker');

            return checker_flag;
        };

        this._contextAnchor = function () {
            return this._getAnchorNodesInRange().length > 0;
        };

        this._contextList = function (ordered_list) {
            var list = this._nodeGetParentList(this._getSelectionNode());

            return list && list.nodeName.toLowerCase() === (ordered_list ? 'ol' : 'ul');
        };

        this._contextLink = function () {
            return this._getLinkNodesInRange().length > 0;
        };

        this._execCommand = function (cmd, arg) {
            if (this._command_locked) {
                return;
            }

            this._command_locked = true;

            this._checkFocus();

            var standard_custom_command_name = cmd.substring(0, 1).toUpperCase() + cmd.substring(1);

            if (this['_command' + standard_custom_command_name]) {
                if (!Array.isArray(arg)) {
                    arg = [arg];
                }

                this['_command' + standard_custom_command_name].apply(this, arg);
            } else {
                try {
                    this._execStandardCommand(cmd, false, (typeof arg === 'undefined' ? true : arg));
                } catch (e) {

                }
            }

            this._command_locked = false;

            this._setContext();

            this._historyAdd();
        };

        /**
         * 
         * @param {type} cmd
         * @param {type} dialog
         * @param {type} argument
         * @returns {undefined}
         */
        this._execStandardCommand = function (cmd, dialog, argument) {
            document.execCommand(cmd, (typeof dialog === 'undefined' ? false : dialog), (typeof argument === 'undefined' ? true : argument));
        };


        this._commandParagraph = function () {
            var start_block = this._nodeGetBlock(this._getSelectionNode());
            var end_block = this._nodeGetBlock(this._getSelectionNode(true));

            if (start_block === end_block) {
                var saved_selection = this._saveSelectionRange(start_block);

                saved_selection.node = this._formatBlock(start_block, start_block.nodeName.toLowerCase() === 'div' ? 'p' : 'div');

                this._setSelectionRange(saved_selection);

                return;
            }

            var common_ancestor = this._getCommonAncestor(start_block, end_block);

            var saved_selection = this._saveSelectionRange(common_ancestor);

            var blocks = common_ancestor.querySelectorAll(this._block_element_names.join(','));
            var blocks_in_range = [];

            var paragraph_counter = 0;

            start_block.setAttribute('editor-node-marker', 1);
            end_block.setAttribute('editor-node-marker', 1);

            var found_counter = 0;

            for (var i = 0; i < blocks.length; i++) {
                if (blocks[i].hasAttribute('editor-node-marker')) {
                    found_counter++;
                    blocks[i].removeAttribute('editor-node-marker');
                }

                if (found_counter > 0) {
                    blocks_in_range.push(blocks[i]);

                    if (blocks[i].nodeName.toLowerCase() === 'p') {
                        paragraph_counter++;
                    }

                    if (found_counter === 2) {
                        break;
                    }
                }
            }

            var new_block_name = paragraph_counter === blocks_in_range.length ? 'div' : 'p';

            for (var i = 0; i < blocks_in_range.length; i++) {
                this._formatBlock(blocks_in_range[i], new_block_name, true);
            }

            this._setSelectionRange(saved_selection);
        };

        this._hrPackElement = function (parent_node) {
            var elements = parent_node.querySelectorAll('.osc-editor-hr');

            var matched_flag = false;

            for (var i = 0; i < elements.length; i++) {
                if (elements[i].nodeName.toLowerCase() !== 'div' || elements[i].childNodes.length !== 1 || elements[i].firstChild.nodeName.toLowerCase() !== 'hr') {
                    elements[i].parentNode.removeChild(elements[i]);
                    continue;
                }

                elements[i].contentEditable = false;

                this._packEditorElement(elements[i], true);

                matched_flag = true;
            }

            return matched_flag;
        };

        this._commandHr = function () {
            if (this._nodeGetBlockContainer(this._getSelectionNode()).nodeName.toLowerCase() === 'td') {
                return;
            }

            var block = document.createElement('div');

            block.className = 'osc-editor-hr';
            block.contentEditable = false;

            block.appendChild(document.createElement('hr'));

            this._nodeSetEditorElement(block, true);

            this._insertBlock(block);
        };

        this._insertBlock = function (block) {
            this._splitBlock(block, true);

            var list_item = this._nodeGetParentListItem(block);

            if (list_item) {
                if (block.nextSibling) {
                    this._nodeInsertAfter(list_item.cloneNode(), list_item);

                    while (block.nextSibling) {
                        list_item.nextSibling.appendChild(block.nextSibling);
                    }
                }

                if (block.previousSibling) {
                    this._nodeInsertAfter(list_item.cloneNode(), list_item);
                    list_item.nextSibling.appendChild(block);
                }

                this._outdentListItem(block, true);
            }

            this._selectNextBlock(block);
        };

        this._selectNextBlock = function (block) {
            var sibling_block = block.nextSibling;

            while (sibling_block) {
                if (!sibling_block.isContentEditable) {
                    sibling_block = null;
                    break;
                }

                var sibling_block_node_name = sibling_block.nodeName.toLowerCase();

                if (['ul', 'ol'].indexOf(sibling_block_node_name) >= 0) {
                    sibling_block = sibling_block.firstChild.firstChild;
                } else if (sibling_block_node_name === 'table') {
                    sibling_block = sibling_block.getElementsByTagName('td')[0].firstChild;
                } else {
                    break;
                }
            }

            if (!sibling_block) {
                sibling_block = document.createElement(this._getDefaultBlockNodeName(block.parentNode));
                sibling_block.appendChild(document.createElement('br'));
                this._nodeInsertAfter(sibling_block, block);
            }

            this._selectNode(sibling_block, true, true, true);
        };

        this._renderAnchorFrm = function (e) {
            var self = this;
            var win = null;
            var container = $('<div />').addClass('osc-editor-win-frm').width(400);

            $('<label />').attr('for', '').html('Điền tên anchor').appendTo(container);
            var name_input = $('<input />').prop({type: 'text'}).appendTo($('<div />').addClass('input-wrap').appendTo(container));

            var action_bar = $('<div />').addClass('action-bar').appendTo(container);
            $('<button />').html('Cancel').click(function () {
                win.destroy();
            }).appendTo(action_bar);
            $('<button />').addClass('blue-btn').html('Update').click(function () {
                var anchor_name = name_input.val();

                if (!anchor_name) {
                    alert('Bạn chưa điền tên anchor');
                    return;
                }

                self._execCommand('anchor', anchor_name);

                win.destroy();
            }).appendTo(action_bar);

            win = this._renderWindow('Insert Anchor', container);
        };

        this._commandAnchor = function (anchor_name) {
            var anchors = this._getAnchorNodesInRange();

            if (anchors.length > 0) {
                for (var x = 0; x < anchors.length; x++) {
                    anchors[x].parentNode.removeChild(anchors[x]);
                }

                return;
            }

            if (!anchor_name) {
                this._renderAnchorFrm();
                return;
            }

            anchor_name = (anchor_name + '').replace(/[^a-zA-Z0-9]/i, '_');

            if (anchor_name.length < 1) {
                return;
            }

            var current_anchor = document.getElementById(anchor_name);

            if (current_anchor) {
                current_anchor.parentNode.removeChild(current_anchor);
            }

            var range = this._getRange();

            if (!range.collapsed) {
                range.collapse(true);
            }

            this._commandInsertHTML('<a id="' + anchor_name + '"></a>');
        };

        const target_option = [
            {
                val: '_blank',
                text: 'Blank',
            },
            {
                val: '_self',
                text: 'Self',
            },
            {
                val: '_parent',
                text: 'Parent',
            },
            {
                val: '_top',
                text: 'Top',
            }
        ];

        this._renderLinkFrm = function (data) {
            var self = this;
            var win = null;
            var container = $('<div />').addClass('osc-editor-win-frm').width(400);

            $('<label />').attr('for', '').html('Url').appendTo(container);
            var url_input = $('<input />').prop({type: 'text', value: data.url}).appendTo($('<div />').addClass('input-wrap').appendTo(container));


            if (!data.disable_text) {
                $('<label />').attr('for', '').html('Text to display').appendTo(container);
                var text_input = $('<input />').prop({type: 'text', value: data.text}).appendTo($('<div />').addClass('input-wrap').appendTo(container));
            }

            $('<label />').html('Target').appendTo(container);
            const target_input = $('<select />').addClass('input-wrap').appendTo(container);

            target_option.forEach(({val, text}) => {
                const option = $('<option/>').val(val).text(text);
                if (data.target == val) option.attr('selected','selected');
                target_input.append(option);
            })

            $('<label />').attr('for', '').html('Title').appendTo(container);
            var title_input = $('<input />').prop({type: 'text', value: data.title}).appendTo($('<div />').addClass('input-wrap').appendTo(container));

            var anchors = this._editarea[0].querySelectorAll('a[id]:not([href])');

            if (anchors.length > 0) {
                $('<label />').attr('for', '').html('Anchor').appendTo(container);
                var anchor_selector = $('<select />').appendTo($('<div />').addClass('input-wrap').appendTo(container));

                $('<option />').attr({value: ''}).html('Select an anchor').appendTo(anchor_selector);

                for (var x = 0; x < anchors.length; x++) {
                    var anchor_id = anchors[x].getAttribute('id');

                    var opt_node = $('<option />').attr('value', anchor_id).html(anchor_id).appendTo(anchor_selector);

                    if (anchor_id === data.anchor) {
                        opt_node.attr('selected', 'selected');
                    }
                }
            }

            var action_bar = $('<div />').addClass('action-bar').appendTo(container);
            $('<button />').html('Cancel').click(function () {
                win.destroy();
            }).appendTo(action_bar);
            $('<button />').addClass('blue-btn').html('Update').click(function () {
                var link_data = {
                    url: url_input.val(),
                    text: text_input ? text_input.val() : '',
                    title: title_input.val(),
                    anchor: anchor_selector ? anchor_selector.val() : '',
                    target: target_input.val(),
                };

                if (text_input && !link_data.text) {
                    alert('Bạn cần điền text hiển thị cho đường dẫn');
                    return;
                }

                if (!link_data.url && !link_data.anchor) {
                    alert('Bạn cần điền URL hoặc chọn một anchor cho đường dẫn');
                    return;
                }

                self._execCommand('link', link_data);

                win.destroy();
            }).appendTo(action_bar);

            win = this._renderWindow('Insert Link', container);
        };

        this._commandLink = function (cmd_data) {
            var range = this._getRange();
            var start_node = this._getSelectionNode();
            var end_node = this._getSelectionNode(true);
            var start_block = this._nodeGetBlock(start_node);
            var end_block = this._nodeGetBlock(end_node);

            var link = this._getParentNode(start_node, 'a', function (node) {
                return node.nodeName.toLowerCase() === 'a' && node.hasAttribute('href');
            });

            if (!cmd_data) {
                cmd_data = {
                    title: '',
                    text: '',
                    url: '',
                    anchor: '',
                    target: '',
                    disable_text: start_block !== end_block
                };

                if (!range.collapsed) {
                    cmd_data.text = range.toString();
                } else if (link) {
                    cmd_data.text = link.textContent;
                }

                if (link) {
                    cmd_data.target = link.getAttribute('target');
                    cmd_data.title = link.getAttribute('title');

                    var url = link.getAttribute('href');

                    if (url.substring(0, 1) === '#') {
                        cmd_data.anchor = url.substring(1);
                    } else {
                        cmd_data.url = url;
                    }
                }

                this._renderLinkFrm(cmd_data);

                return;
            }

            var url = '#' + cmd_data.anchor;

            if (cmd_data.url) {
                url = cmd_data.url;
            }

            if (range.collapsed && link) {
                this._selectNode(link, false, true);
            }

            this._commandInsertHTML(document.createTextNode(cmd_data.text), true);

            this._execStandardCommand('unlink', null, null);

            this._execStandardCommand('createLink', null, url);

            const selection = document.getSelection();
            selection.anchorNode.parentElement.target = cmd_data.target;

            this._editarea[0].normalize();
        };

        this._commandUnlink = function () {
            var links = this._getLinkNodesInRange();

            if (links.length > 0) {
                var saved_selection = this._saveSelectionRange(this._editarea[0]);

                $(links).each(function () {
                    var node = $(this);
                    node.after(node.contents());
                    node.remove();
                });

                this._setSelectionRange(saved_selection);

                return;
            }
        };

        this._commandImage = function (data) {
            if (!data) {
                this._renderImageFrm();
                return;
            }
        };

        this._commandVideo = function (code) {
            if (!code) {
                this._renderVideoFrm();
                return;
            }

            var video_content = this._getVideoContentFromCode(code);

            if (!video_content) {
                return;
            }

            //this._splitBlock(this._renderVideoNode(video_content), true); //SPLITTER

            var video_node = this._renderVideoNode(video_content);

            var cur_block = this._nodeGetBlock(this._getSelectionNode());

            if (cur_block) {
                $(cur_block)[this._caretInEndOfBlock() ? 'after' : 'before'](video_node);
            } else {
                $(this._nodeGetBlockContainer(this._getSelectionNode())).append(video_node);
            }
        };

        this._commandAlign = function (align) {
            var start_node = this._nodeGetBlock(this._getSelectionNode()), end_node = this._nodeGetBlock(this._getSelectionNode(true)), node, found_counter = 0;

            if (start_node === end_node) {
                if (start_node.isContentEditable) {
                    $(start_node).css('text-align', align);
                }

                return;
            }

            $(start_node).data('node_marker', 1);
            $(end_node).data('node_marker', 1);

            $(this._getSelectionCommonAncestor()).find(this._block_element_names.join(',')).each(function () {
                node = $(this);

                if (node.data('node_marker') === 1) {
                    found_counter++;
                    node.removeData('node_marker');
                }

                if (found_counter > 0 && this.isContentEditable) {
                    node.css('text-align', align);
                }

                if (found_counter === 2) {
                    return false;
                }
            });
        };

        /**
         * 
         * @param {type} node_name
         * @returns {undefined}
         */
        this._commandConvertBlock = function (node_name) {
            var start_block = this._nodeGetBlock(this._getSelectionNode());
            var end_block = this._nodeGetBlock(this._getSelectionNode(true));

            if (start_block === end_block) {
                var saved_selection = this._saveSelectionRange(start_block);

                saved_selection.node = this._formatBlock(start_block, node_name);

                this._setSelectionRange(saved_selection);

                return;
            }

            var common_ancestor = this._getCommonAncestor(start_block, end_block);

            var saved_selection = this._saveSelectionRange(common_ancestor);

            var blocks = [].slice.call(common_ancestor.querySelectorAll(this._block_element_names.join(',')));

            start_block.setAttribute('editor-node-marker', 1);
            end_block.setAttribute('editor-node-marker', 1);

            var found_counter = 0;

            for (var i = 0; i < blocks.length; i++) {
                if (blocks[i].hasAttribute('editor-node-marker')) {
                    found_counter++;
                    blocks[i].removeAttribute('editor-node-marker');
                }

                if (found_counter > 0) {
                    this._formatBlock(blocks[i], node_name);

                    if (found_counter === 2) {
                        break;
                    }
                }
            }

            this._setSelectionRange(saved_selection);
        };

        this._commandList = function (ordered) {
            var start_node = this._getSelectionNode();

            if (start_node && this._nodeIsInList(start_node)) {
                var list = this._getParentNode(start_node, 'ul,ol');

                var list_name = ordered ? 'ol' : 'ul';

                if (list.nodeName.toLowerCase() !== list_name) {
                    var saved_selection = this._saveSelectionRange(list);

                    var new_list = document.createElement(list_name);

                    list.parentNode.insertBefore(new_list, list);

                    while (list.firstChild) {
                        new_list.appendChild(list.firstChild);
                    }

                    list.parentNode.removeChild(list);

                    saved_selection.node = new_list;

                    this._setSelectionRange(saved_selection);
                } else {
                    this._processOutdentInList(true);
                }

                return;
            }

            if (!start_node) {
                return;
            }

            var end_node = this._getSelectionNode(true);

            if (start_node === end_node && this._nodeIsBlockContainer(start_node)) {
                var blocks = this._nodeGetChildrenArray(start_node);

                if (blocks.length < 1) {
                    var list = document.createElement(ordered ? 'ol' : 'ul');

                    var li = document.createElement('li');

                    li.appendChild(document.createElement(this._getDefaultBlockNodeName(li)));
                    li.lastChild.appendChild(document.createElement('br'));

                    list.appendChild(li);

                    start_node.appendChild(list);

                    this._selectNode(li.lastChild, true, true, false);

                    return;
                }

                var common_ancestor = start_node;
            } else {
                var common_ancestor = this._nodeGetBlockContainer(this._getCommonAncestor(start_node, end_node));

                var blocks = [];

                var found_counter = 0;

                for (var i = 0; i < common_ancestor.childNodes.length; i++) {
                    if (start_node === common_ancestor.childNodes[i] || this._nodeIsDescendant(common_ancestor.childNodes[i], start_node)) {
                        found_counter++;
                    }

                    if (end_node === common_ancestor.childNodes[i] || this._nodeIsDescendant(common_ancestor.childNodes[i], end_node)) {
                        found_counter++;
                    }

                    if (found_counter > 0) {
                        blocks.push(common_ancestor.childNodes[i]);

                        if (found_counter > 1) {
                            break;
                        }
                    }
                }
            }

            if (blocks.length < 1) {
                return;
            }

            var saved_selection = this._saveSelectionRange(common_ancestor);

            var list_name = ordered ? 'ol' : 'ul';

            if (blocks[0].previousSibling && blocks[0].previousSibling.nodeName.toLowerCase() === list_name) {
                var list = blocks[0].previousSibling;
            } else {
                var list = document.createElement(list_name);
                common_ancestor.insertBefore(list, blocks[0]);
            }

            for (var i = 0; i < blocks.length; i++) {
                if (['ul', 'ol'].indexOf(blocks[i].nodeName.toLowerCase()) < 0) {
                    var li = document.createElement('li');
                    li.appendChild(blocks[i]);

                    list.appendChild(li);

                    continue;
                }

                if (!list.lastChild) {
                    while (blocks[i].firstChild) {
                        list.appendChild(blocks[i].firstChild);
                    }

                    common_ancestor.removeChild(blocks[i]);

                    continue;
                }

                if (list.lastChild.lastChild && ['ul', 'ol'].indexOf(list.lastChild.lastChild.nodeName.toLowerCase()) >= 0) {
                    while (blocks[i].firstChild) {
                        list.lastChild.lastChild.appendChild(blocks[i].firstChild);
                    }

                    common_ancestor.removeChild(blocks[i]);

                    continue;
                }

                list.lastChild.appendChild(blocks[i]);
            }

            if (list.nextSibling && list.nextSibling.nodeName.toLowerCase() === list.nodeName.toLowerCase()) {
                while (list.lastChild) {
                    if (list.nextSibling.firstChild) {
                        list.nextSibling.insertBefore(list.lastChild, list.nextSibling.firstChild);
                    } else {
                        list.nextSibling.appendChild(list.lastChild);
                    }
                }

                common_ancestor.removeChild(list);
            }

            this._setSelectionRange(saved_selection);

        };

        /**
         * 
         * @param {type} html
         * @param {type} select_inserted_content
         * @returns {undefined}
         */
        this._commandInsertHTML = function (html, select_inserted_content) {
            var selection = window.getSelection();

            if (!selection.isCollapsed) {
                this._execStandardCommand('delete');
            }

            var el = document.createElement('div');

            if (typeof html === 'object' && typeof html.nodeType !== 'undefined') {
                el.appendChild(html);
            } else {
                el.innerHTML = html;
            }

            var frag = document.createDocumentFragment();

            var node = null;
            var last_node = null;

            while (node = el.firstChild) {
                last_node = frag.appendChild(node);
            }

            var first_node = frag.firstChild;

            var range = this._getRange();
            range.collapse(true);
            range.insertNode(frag);

            if (last_node) {
                range = range.cloneRange();
                range.setStartAfter(last_node);

                if (select_inserted_content) {
                    range.setStartBefore(first_node);
                } else {
                    range.collapse(true);
                }

                selection.removeAllRanges();
                selection.addRange(range);
            }
        };

        this._contextNodePath = function () {
            var nodes = [];
            var node = this._getSelectionNode();

            if (node && node.isContentEditable) {
                while (node && !this._nodeIsEditarea(node)) {
                    nodes.push(node);
                    node = node.parentNode;
                }

                nodes.reverse();
            }

            return nodes;
        };
        this._contextStateNodePath = function (path_list, nodes) {
            var self = this;

            path_list.innerHTML = '';

            for (var i = 0; i < nodes.length; i++) {
                var list_item = document.createElement('li');

                var span = document.createElement('span');
                span.innerHTML = nodes[i].nodeType === nodes[i].TEXT_NODE ? '#TEXT' : nodes[i].nodeName.toLowerCase();
                span.setAttribute('editor-node-idx', i);
                span.onclick = function () {
                    self._checkFocus();
                    self._selectNode(nodes[this.getAttribute('editor-node-idx')], false, true);
                    self._historyAdd(true);
                    self._setContext();
                };

                list_item.appendChild(span);

                path_list.appendChild(list_item);
            }
        };

        this._getDefaultBlockNodeName = function (block_container) {
            return 'p';

            if (!block_container) {
                block_container = this._nodeGetBlockContainer(this._getSelectionNode());
            }

            return ['li', 'td'].indexOf(block_container.nodeName.toLowerCase()) < 0 ? 'p' : 'div';
        };

        this._renderWindow = function (title, content) {
            var self = this;

            this._editbox.attr('disabled', 'disabled');

            var win = $.create_window({
                destroy_hook: function () {
                    self._editbox.removeAttr('disabled');
                    self._checkFocus();
                },
                title: title,
                content: content
            });

            return win;
        };

        this._setupElementControl = function (node, controls) {
            var self = this;

            for (var x in controls) {
                for (var y = 0; y < controls[x].length; y++) {
                    for (var z = 0; z < controls[x][y].length; z++) {
                        var control = controls[x][y][z];

                        if (control.key === 'separate') {
                            continue;
                        }

                        if (typeof control.config !== 'object' || control.config === null) {
                            control.config = {};
                        }

                        try {
                            if (this['_elementControl' + control.key.substring(0, 1).toUpperCase() + control.key.substring(1) + '_Initialize'](node, control.config) === false) {
                                delete controls[x][y][z];
                            }
                        } catch (e) {

                        }
                    }

                    controls[x][y] = controls[x][y].filter(function (val) {
                        return val;
                    });
                }
            }

            $(node).unbind('.editorElementControl').bind('mouseenter.editorElementControl renderEditorElementControl.editorElementControl', function () {
                if (node.hasAttribute('osc-editor-element-control-clear-timer')) {
                    clearTimeout(node.getAttribute('osc-editor-element-control-clear-timer'));
                    node.removeAttribute('osc-editor-element-control-clear-timer');
                    return;
                }

                $(node).trigger('clearEditorElementControl');

                if (typeof controls.top === 'object') {
                    self._elementTopControl_Renderer(node, controls.top);
                }

                if (typeof controls.bottom === 'object') {
                    self._elementBottomControl_Renderer(node, controls.bottom);
                }
            }).bind('mouseleave.editorElementControl', function () {
                node.setAttribute('osc-editor-element-control-clear-timer', setTimeout(function () {
                    $(node).trigger('clearEditorElementControl');
                }, 300));
            }).bind('clearEditorElementControl.editorElementControl', function () {
                node.removeAttribute('osc-editor-element-control-clear-timer');
            });
        };

        this._elementBottomControl_Renderer = function (node, controls) {
            var self = this;

            var bottom_control = $('<div />').addClass('osc-editor-element-control');

            this._renderElementControlBars(node, bottom_control, controls);

            if (bottom_control[0].childNodes.length < 1) {
                return;
            }

            bottom_control.appendTo(this._editbox);

            bottom_control.bind('mouseenter', function () {
                clearTimeout(node.getAttribute('osc-editor-element-control-clear-timer'));
                node.removeAttribute('osc-editor-element-control-clear-timer');
            }).bind('mouseleave', function () {
                node.setAttribute('osc-editor-element-control-clear-timer', setTimeout(function () {
                    $(node).trigger('clearEditorElementControl');
                }, 300));
            });

            var control_clean = function () {
                $(node).unbind('.editorElementBottomControl');

                self._editarea.unbind('.editorElementBottomControl');
                self.scroller.unbind('.editorElementBottomControl');

                bottom_control.remove();
            };

            $(node).bind('clearEditorElementControl.editorElementBottomControl', control_clean);

            this._editarea.bind('scroll.editorElementBottomControl', function () {
                self._elementBottomControl_SetPosition(node, bottom_control);
            });
            this.scroller.bind('scroll.editorElementBottomControl', function () {
                self._elementBottomControl_SetPosition(node, bottom_control);
            });
            $(window).resize(function () {
                self._elementBottomControl_SetPosition(node, bottom_control);
            });

            self._elementBottomControl_SetPosition(node, bottom_control);

            var node_removed_checker = function () {
                if (node.parentNode) {
                    setTimeout(node_removed_checker, 250);
                } else {
                    control_clean();
                }
            };

            node_removed_checker();
        };

        this._elementBottomControl_SetPosition = function (node, control_container) {
            var node_boundary = $.extend({}, node.getBoundingClientRect());
            var editarea_viewport_boundary = this._getEditareaViewportBoundary();

            if (!editarea_viewport_boundary) {
                control_container.hide();
                return;
            }

            if (!this.inline_mode) {
                if (node_boundary.left < editarea_viewport_boundary.left) {
                    node_boundary.left = editarea_viewport_boundary.left;
                }

                if (node_boundary.right > editarea_viewport_boundary.right) {
                    node_boundary.right = editarea_viewport_boundary.right;
                }
            }

            var control_container_width = control_container.width();
            var control_container_height = control_container.height();

            var position = {
                top: node_boundary.bottom + 5,
                left: node_boundary.left + ((node_boundary.right - node_boundary.left - control_container_width) / 2)
            };

            if (editarea_viewport_boundary) {
                if (position.top + control_container_height > editarea_viewport_boundary.bottom - 5) {
                    position.top = editarea_viewport_boundary.bottom - control_container_height - 5;
                }
            }

            if (position.top < node_boundary.top + 50) {
                position.top = node_boundary.top + 50;
            }

            control_container.offset({
                top: $(window).scrollTop() + position.top,
                left: $(window).scrollLeft() + position.left
            });
        };

        this._elementTopControl_Renderer = function (node, controls) {
            var self = this;

            var top_control = $('<div />').addClass('osc-editor-element-control');

            this._renderElementControlBars(node, top_control, controls);

            if (top_control[0].childNodes.length < 1) {
                return;
            }

            top_control.appendTo(this._editbox);

            top_control.bind('mouseenter', function () {
                clearTimeout(node.getAttribute('osc-editor-element-control-clear-timer'));
                node.removeAttribute('osc-editor-element-control-clear-timer');
            }).bind('mouseleave', function () {
                node.setAttribute('osc-editor-element-control-clear-timer', setTimeout(function () {
                    $(node).trigger('clearEditorElementControl');
                }, 300));
            });

            var control_clean = function () {
                $(node).unbind('.editorElementTopControl');

                self._editarea.unbind('.editorElementTopControl');
                self.scroller.unbind('.editorElementTopControl');

                top_control.remove();
            };

            $(node).bind('clearEditorElementControl.editorElementTopControl', control_clean);

            this._editarea.bind('scroll.editorElementTopControl', function () {
                self._elementTopControl_SetPosition(node, top_control);
            });
            this.scroller.bind('scroll.editorElementTopControl', function () {
                self._elementTopControl_SetPosition(node, top_control);
            });
            $(window).resize(function () {
                self._elementTopControl_SetPosition(node, top_control);
            });

            self._elementTopControl_SetPosition(node, top_control);

            var node_removed_checker = function () {
                if (node.parentNode) {
                    setTimeout(node_removed_checker, 250);
                } else {
                    control_clean();
                }
            };

            node_removed_checker();
        };

        this._elementTopControl_SetPosition = function (node, control_container) {
            var node_boundary = $.extend({}, node.getBoundingClientRect());
            var editarea_viewport_boundary = this._getEditareaViewportBoundary();

            if (!editarea_viewport_boundary) {
                control_container.hide();
                return;
            }

            if (!this.inline_mode) {
                if (node_boundary.left < editarea_viewport_boundary.left) {
                    node_boundary.left = editarea_viewport_boundary.left;
                }

                if (node_boundary.right > editarea_viewport_boundary.right) {
                    node_boundary.right = editarea_viewport_boundary.right;
                }
            }

            var control_container_width = control_container.width();
            var control_container_height = control_container.height();

            var position = {
                top: node_boundary.top - control_container_height - 5,
                left: node_boundary.left + ((node_boundary.right - node_boundary.left - control_container_width) / 2)
            };

            if (editarea_viewport_boundary) {
                if (position.top < editarea_viewport_boundary.top + 5) {
                    position.top = editarea_viewport_boundary.top + 5;
                }
            }

            if (position.top > node_boundary.bottom - control_container_height - 50) {
                position.top = node_boundary.bottom - control_container_height - 50;
            }

            control_container.show().offset({
                top: $(window).scrollTop() + position.top,
                left: $(window).scrollLeft() + position.left
            });
        };

        this._renderElementControlBars = function (node, container, controls) {
            for (var x = 0; x < controls.length; x++) {
                var control_bar = $('<ul />');

                for (var y = 0; y < controls[x].length; y++) {
                    var control = controls[x][y];

                    if (control.key === 'separate') {
                        if (!control_bar[0].lastChild || control_bar[0].lastChild.className !== 'separate') {
                            control_bar.append($('<li />').addClass('separate'));
                        }

                        continue;
                    }

                    try {
                        var control_cmds = this['_elementControl' + control.key.substring(0, 1).toUpperCase() + control.key.substring(1) + '_RenderCommand'](node, control.config);

                        for (var i = 0; i < control_cmds.length; i++) {
                            if (control_cmds[i]) {
                                control_bar.append(control_cmds[i]);
                            }
                        }
                    } catch (e) {

                    }
                }

                if (control_bar[0].lastChild && control_bar[0].lastChild.className === 'separate') {
                    control_bar[0].removeChild(control_bar[0].lastChild);
                }

                if (control_bar[0].firstChild && control_bar[0].firstChild.className === 'separate') {
                    control_bar[0].removeChild(control_bar[0].firstChild);
                }

                if (control_bar[0].childNodes.length > 0) {
                    container.append(control_bar);
                }
            }
        };

        this._elementControlAlign_GetConfig = function (custom_config) {
            for (var x in custom_config) {
                if (custom_config[x] === 'initial') {
                    delete custom_config[x];
                }
            }

            var config = $.extend({depends: false, level_enable: false, lock_left: false, lock_right: false, lock_center: false, full_mode: false, overflow_mode: false}, custom_config);

            if (config.lock_left && config.lock_right) {
                config.lock_center = false;
            }

            return config;
        };

        this._elementControlAlign_Initialize = function (node, custom_config) {
            var self = this;

            var config = this._elementControlAlign_GetConfig(custom_config);

            if (!config) {
                return false;
            }

            var align_state = this._elementControlAlign_GetState(node);

            if (config.lock_center && !align_state.align) {
                align_state.align = align_state.lock_left ? 'right' : 'left';
            } else if (align_state.lock_left && align_state.align === 'left') {
                align_state.align = align_state.lock_center ? 'right' : '';
            } else if (align_state.lock_right && align_state.align === 'right') {
                align_state.align = align_state.lock_center ? 'left' : '';
            }

            if (align_state.align) {
                if (!config.level_enable && align_state.level === 2) {
                    self._elementControlAlign_Setter(node, align_state.align, 1);
                }
            }
        };

        this._elementControlAlign_RenderCommand = function (node, custom_config) {
            var self = this;

            var config = this._elementControlAlign_GetConfig(custom_config);

            var align_state = this._elementControlAlign_GetState(node);

            var left_cmd = $('<li />').html(this._renderIcon('block-align-left'))
                    .mousedown(function (e) {
                        e.stopPropagation();

                        if (this.hasAttribute('disabled')) {
                            return;
                        }

                        self._historyAdd();

                        self._elementControlAlign_Setter(node, 'left');
                    });

            if (align_state.align === 'left') {
                if (!config.level_enable) {
                    left_cmd.attr('disabled', 'disabled');
                } else {
                    left_cmd.html(this._renderIcon('block-align-left-more'));

                    if (align_state.level === 2) {
                        left_cmd.attr('disabled', 'disabled');
                    }
                }
            }

            var center_icon = 'block-align-fit';

            if (!align_state.align && (config.overflow_mode || config.full_mode)) {
                if (config.overflow_mode && !align_state.overflow_mode && !align_state.full_mode) {
                    center_icon = 'block-align-wide';
                } else if (config.full_mode && !align_state.full_mode && (align_state.overflow_mode || !config.overflow_mode)) {
                    center_icon = 'block-align-wide-full';
                }
            }

            var center_cmd = $('<li />').html(this._renderIcon(center_icon))
                    .mousedown(function (e) {
                        e.stopPropagation();

                        if (this.hasAttribute('disabled')) {
                            return;
                        }

                        self._historyAdd();

                        self._elementControlAlign_Setter(node);
                    });

            if (!align_state.align && !config.overflow_mode && !config.full_mode) {
                center_cmd.attr('disabled', 'disabled');
            }

            var right_cmd = $('<li />').html(this._renderIcon('block-align-right'))
                    .mousedown(function (e) {
                        e.stopPropagation();

                        if (this.hasAttribute('disabled')) {
                            return;
                        }

                        self._historyAdd();

                        self._elementControlAlign_Setter(node, 'right');
                    });

            if (align_state.align === 'right') {
                if (!config.level_enable) {
                    right_cmd.attr('disabled', 'disabled');
                } else {
                    right_cmd.html(this._renderIcon('block-align-right-more'));

                    if (align_state.level === 2) {
                        right_cmd.attr('disabled', 'disabled');
                    }
                }
            }

            if (config.lock_left) {
                left_cmd = null;
            }

            if (config.lock_right) {
                right_cmd = null;
            }

            if (config.lock_center) {
                center_cmd = null;
            }

            return [left_cmd, center_cmd, right_cmd];
        };

        this._elementControlAlign_Setter = function (node, flag, force_set_level) {
            var before_state = this._elementControlAlign_GetState(node);
            var level = 1;

            node = $(node);

            node.removeClass('align-left').removeClass('align-right').removeClass('overflow-mode').removeClass('full-mode').removeAttr('align-level');

            if (flag) {
                if (!force_set_level) {
                    if (before_state.align === flag) {
                        if (before_state.level === 2) {
                            flag = false;
                        } else {
                            level = 2;
                        }
                    }
                } else {
                    level = force_set_level;
                }

                if (flag === 'left') {
                    node.addClass('align-left');
                } else if (flag === 'right') {
                    node.addClass('align-right');
                }

                if (flag) {
                    node.addClass('align-' + flag).attr('align-level', level);
                }
            } else if (!before_state.align) {
                if (!before_state.overflow_mode && !before_state.full_mode) {
                    node.addClass('overflow-mode');
                } else if (before_state.overflow_mode) {
                    node.addClass('full-mode');
                }
            }

            node.trigger('editorControlAlignSetted');
            node.trigger('renderEditorElementControl');
        };

        this._elementControlAlign_GetState = function (node) {
            var state = {
                align: false,
                level: 0,
                full_mode: false,
                overflow_mode: false
            };

            if (node.className.indexOf('align-left') >= 0) {
                state.align = 'left';
            } else if (node.className.indexOf('align-right') >= 0) {
                state.align = 'right';
            } else {
                if (node.className.indexOf('overflow-mode') >= 0) {
                    state.overflow_mode = true;
                } else if (node.className.indexOf('full-mode') >= 0) {
                    state.full_mode = true;
                }
            }

            if (state.align !== false) {
                state.level = parseInt(node.getAttribute('align-level')) === 2 ? 2 : 1;
            }

            return state;
        };

        this._elementControlZoom_GetConfig = function (node, custom_config) {
            for (var x in custom_config) {
                if (custom_config[x] === 'initial') {
                    delete custom_config[x];
                }
            }

            var config = $.extend({depends: false, max_width: 0, zoom_node: null, constrain_proportions: true, levels: [600, 800, 900, 1080], align_levels: [320, 450]}, custom_config);

            config.max_width = parseInt(config.max_width);

            if (isNaN(config.max_width)) {
                config.max_width = 0;
            }

            var level_keys = ['levels', 'align_levels'];

            for (var x = 0; x < level_keys.length; x++) {
                var level_key = level_keys[x];

                if (Array.isArray(config[level_key])) {
                    var levels = [];

                    for (var i = 0; i < config[level_key].length; i++) {
                        var level = parseInt(config[level_key][i]);

                        if (!isNaN(level) && level > 0 && levels.indexOf(level) === -1 && (config.max_width <= 0 || level <= config.max_width)) {
                            levels.push(level);
                        }
                    }

                    levels.sort(function (a, b) {
                        return a - b;
                    });

                    config[level_key] = levels;
                } else {
                    config[level_key] = [];
                }
            }

            if (config.levels.length < 1 && config.align_levels.length < 1) {
                return null;
            }

            if (config.levels.length < 1) {
                config.levels = config.align_levels;
            } else if (config.align_levels.length < 1) {
                config.align_levels = config.levels;
            }

            if (typeof config.zoom_node === 'string') {
                config.zoom_node = $(config.zoom_node);

                if (!config.zoom_node[0]) {
                    config.zoom_node = null;
                }
            } else if (typeof config.zoom_node === 'object' && config.zoom_node !== null) {
                if (!(config.zoom_node instanceof jQuery) || !config.zoom_node[0]) {
                    if (config.zoom_node.nodeType && config.zoom_node.nodeType === Node.ELEMENT_NODE) {
                        config.zoom_node = $(config.zoom_node);
                    } else {
                        config.zoom_node = null;
                    }
                }
            } else {
                config.zoom_node = null;
            }

            if (!config.zoom_node) {
                config.zoom_node = $(node);
            }

            return config;
        };

        this._elementControlZoom_Initialize = function (node, custom_config) {
            var self = this;

            var config = this._elementControlZoom_GetConfig(node, custom_config);

            if (!config) {
                return false;
            }

            $(node).unbind('.editorElementControlZoom').bind('editorControlAlignSetted.editorElementControlZoom', function () {
                self._elementControlZoom_Setter(node, config, null);
            });

            this._elementControlZoom_Setter(node, config, null);
        };

        this._elementControlZoom_RenderCommand = function (node, custom_config) {
            var self = this;
            var config = this._elementControlZoom_GetConfig(node, custom_config);

            var zoom_state = this._elementControlZoom_GetState(node, config);

            if (zoom_state.levels.length < 2) {
                return [];
            }

            var zoom_in_cmd = $('<li />').append(this._renderIcon('search-plus')).click(function (e) {
                e.stopPropagation();

                if (this.hasAttribute('disabled')) {
                    return;
                }

                self._historyAdd();

                self._elementControlZoom_Setter(node, config, true);
            });

            if (zoom_state.level_idx === zoom_state.levels.length - 1) {
                zoom_in_cmd.attr('disabled', 'disabled');
            }

            var zoom_out_cmd = $('<li />').append(this._renderIcon('search-minus')).click(function (e) {
                e.stopPropagation();

                if (this.hasAttribute('disabled')) {
                    return;
                }

                self._historyAdd();

                self._elementControlZoom_Setter(node, config, false);
            });

            if (zoom_state.level_idx === 0) {
                zoom_out_cmd.attr('disabled', 'disabled');
            }

            return [zoom_in_cmd, zoom_out_cmd];
        };

        this._elementControlZoom_GetState = function (node, config) {
            var state = {levels: this._elementControlAlign_GetState(node).align === false ? config.levels : config.align_levels, level_idx: null};

            var width = config.zoom_node.width();
            var max_width = 0;
            var min_width = 0;

            for (var i = 0; i < state.levels.length; i++) {
                var level_width = state.levels[i];

                if (width === level_width) {
                    state.level_idx = i;
                    break;
                }

                if (level_width > width) {
                    max_width = level_width;
                    break;
                } else {
                    min_width = level_width;
                }
            }

            if (state.level_idx === null) {
                if (max_width < 1) {
                    state.level_idx = state.levels.indexOf(min_width);
                } else if (min_width < 1) {
                    state.level_idx = state.levels.indexOf(max_width);
                } else {
                    state.level_idx = state.levels.indexOf(((max_width - min_width) / 2) > (width - min_width) ? min_width : max_width);
                }
            }

            return state;
        };

        this._elementControlZoom_Setter = function (node, config, increment_flag) {
            var zoom_state = this._elementControlZoom_GetState(node, config);

            node = $(node);

            var rerender_element_controls_flag = true;

            if (increment_flag === true) {
                if (zoom_state.level_idx < zoom_state.levels.length - 1) {
                    zoom_state.level_idx++;
                }
            } else if (increment_flag === false) {
                if (zoom_state.level_idx > 0) {
                    zoom_state.level_idx--;
                }
            } else {
                rerender_element_controls_flag = false;
            }

            var width = zoom_state.levels[zoom_state.level_idx];

            var height = 0;

            if (config.constrain_proportions) {
                height = config.zoom_node.height() * width / config.zoom_node.width();
            }

            config.zoom_node.width(width);

            if (height > 0) {
                config.zoom_node.height(height);
            }

            if (!rerender_element_controls_flag) {
                return zoom_state.level_idx;
            }

            node.trigger('renderEditorElementControl');
        };

        this._elementControlDelete_RenderCommand = function (node) {
            var self = this;

            return [$('<li />').append(this._renderIcon('trash'))
                        .mousedown(function (e) {
                            e.stopPropagation();
                            self._historyAdd();
                            node.parentNode.removeChild(node);
                        })];
        };

        this._elementControlCopy_RenderCommand = function (node) {
            var self = this;

            return [$('<li />').append(this._renderIcon('clone'))
                        .mousedown(function (e) {
                            e.stopPropagation();
                            self._nodeCopyToClipboard(node);
                        })];
        };

        this._elementControlEdit_RenderCommand = function (node, config) {
            if (typeof config !== 'object' || config === null || typeof config.callback !== 'function') {
                return;
            }

            var self = this;

            return [$('<li />').append(this._renderIcon('pencil'))
                        .mousedown(function (e) {
                            e.stopPropagation();
                            config.callback.apply(self, [node]);
                        })];
        };

        this._elementControlCaption_RenderCommand = function (node) {
            var self = this;

            return [$('<li />').append(this._renderIcon('cc'))
                        .mousedown(function (e) {
                            e.stopPropagation();
                            self._elementControlCaption_RenderForm(node);
                        })];
        };

        this._elementControlCaption_RenderForm = function (node) {
            var win = null;

            var caption_node = $(node).find('> .caption')[0];
            var alt_img = $(node).find($('img')).attr('alt');
            var height_img = $(node).find($('img')).attr('height');
            var width_img = $(node).find($('img')).attr('width');
            var hyperlink_img = $(node).find($('img')).parent().attr('href');
            var hyperlink_img_target = $(node).find($('img')).parent().attr('target');

            if (!caption_node) {
                caption_node = document.createElement('div');
                caption_node.className = 'caption';
            }

            var container = $('<div />').addClass('osc-editor-win-frm').width(450);

            $('<label />').attr('for', '').html('Fill caption of image').appendTo(container);

            var caption_input = $('<textarea />').val(caption_node.innerHTML.replace(/<br\s*>/ig, "\n")).appendTo($('<div />').addClass('input-wrap').appendTo(container));

            $('<label />').attr('for', '').html('Fill alt of image').appendTo(container);

            var alt_input = $('<input />').attr('type', 'text').val(alt_img).appendTo($('<div />').addClass('input-wrap').appendTo(container));

            $('<label />').attr('for', '').html('Fill url of image').appendTo(container);

            var url_input = $('<input />').attr({
                type: 'text',
                placeholder: ''
            }).val(hyperlink_img ? hyperlink_img : '').appendTo($('<div />').addClass('input-wrap').appendTo(container));

            let label_input_url = $('<label />').attr('for', '').html('Select target url of image').appendTo(container);

            if(!hyperlink_img){
                label_input_url.css('display','none');
            }

            var hyperlink_image_target_input = $('<select />');

            target_option.forEach(({val, text}) => {
                const option = $('<option/>').val(val).text(text);
                if (hyperlink_img_target == val) option.attr('selected','selected');
                hyperlink_image_target_input.append(option);
            })

            if(!hyperlink_img){
                hyperlink_image_target_input.css('display','none')
            }

            hyperlink_image_target_input.addClass('input-wrap').appendTo(container);

            $('<label />').attr('for', '').html('Fill height of image').appendTo(container);

            var height_input = $('<input />').attr({
                type: 'text',
                placeholder: 'Pixel or percentage. Ex: 100, 100px or 100%'
            }).val(height_img ? height_img : '').appendTo($('<div />').addClass('input-wrap').appendTo(container));

            var warning_height = $('<p />').css({
                color: 'red',
                display: 'none'
            }).text('Please check your height input value').appendTo(container);

            $('<label />').attr('for', '').html('Fill width of image').appendTo(container);

            var width_input = $('<input />').attr({
                type: 'text',
                placeholder: 'Pixel or percentage. Ex: 100, 100px or 100%'
            }).val(width_img ? width_img : '').appendTo($('<div />').addClass('input-wrap').appendTo(container));

            var warning_width = $('<p />').css({
                color: 'red',
                display: 'none'
            }).text('Please check your width input value').appendTo(container);

            var action_bar = $('<div />').addClass('action-bar').appendTo(container);

            $('<button />').html('Cancel').click(function () {
                win.destroy();
            }).appendTo(action_bar);

            url_input.keyup(function () {

              if(!url_input.val().trim()){
                  label_input_url.hide();
                  hyperlink_image_target_input.hide();
              }else{
                  label_input_url.show();
                  hyperlink_image_target_input.show();
              }

            });

            $('<button />').addClass('blue-btn').html('Update').click(function () {
                let check = true;

                var caption_data = caption_input.val().trim();
                var alt_data = alt_input.val().trim();
                var height_data = height_input.val().trim();
                var width_data = width_input.val().trim();
                var url_data = url_input.val().trim();
                let parent_img = $(node).find($('img').parent());

                if (!caption_data) {
                    if($(caption_node).text()) node.removeChild(caption_node);
                } else {
                    caption_node.innerHTML = caption_data.replace(/\n/g, '<br />');
                    node.appendChild(caption_node);
                }

                if (alt_data) {
                    $(node).find($('img')).attr('alt', alt_data);
                }

                if (height_data) {
                    if (/^\d+(px|%)?$/i.test(height_data)) {
                        $(node).find($('img')).attr({
                            height: height_data,
                        });
                        warning_height.css('display', 'none');
                    } else {
                        warning_height.css('display', 'block');
                        check = false;
                    }
                } else {
                    warning_height.css('display', 'none');
                    $(node).find($('img')).removeAttr('height');
                }

                if (width_data) {
                    if (/^\d+(px|%)?$/i.test(width_data)) {
                        $(node).find($('img')).attr({
                            width: width_data,
                        });
                        warning_width.css('display', 'none');
                    } else {
                        warning_width.css('display', 'block');
                        check = false;
                    }
                } else {
                    warning_width.css('display', 'none');
                    $(node).find($('img')).removeAttr('width');
                }
                var image_el = $(node).find($('img'));

                if (url_data) {

                    if (!parent_img.is("a")) {
                        image_el.appendTo($(`<a href= "${url_data}">`).attr('target', hyperlink_image_target_input.val()).addClass('hyperlink-img-container').appendTo(parent_img));
                    } else {
                        parent_img.attr('href', `${url_data}`).attr('target', hyperlink_image_target_input.val());
                    }

                } else {
                    if (parent_img.is("a")) {
                        parent_img.parent().html(image_el);
                    }
                }

                if (check) win.destroy();

            }).appendTo(action_bar);

            win = this._renderWindow('Set up image information', container);
        };

        this._getScrollerBoundary = function () {
            var scroller_boundary = {top: 0, left: 0, right: this.scroller.width(), bottom: this.scroller.height()};

            if (this.scroller[0] !== window) {
                $.extend(scroller_boundary, this.scroller[0].getBoundingClientRect());
            }

            return scroller_boundary;
        };
        this._elementControlImageLeft_RenderCommand = function (node) {
            var self = this;

            return [$('<li />').append(this._renderIcon('block-align-left'))
                        .mousedown(function (e) {
                            e.stopPropagation();
                            self._elementControlAlignImageInline_Setter(node, 'left');
                        })];
        };

        this._elementControlImageRight_RenderCommand = function (node) {
            var self = this;

            return [$('<li />').append(this._renderIcon('block-align-right'))
                        .mousedown(function (e) {
                            e.stopPropagation();
                            self._elementControlAlignImageInline_Setter(node, 'right');
                        })];

        };
        this._elementControlAlignImageInline_Setter = function (node, flag) {
            node = $(node);

            node.removeClass('image-inline-left').removeClass('image-inline-right');

            if (flag) {

                if (flag === 'left') {
                    node.addClass('image-inline-left');
                } else if (flag === 'right') {
                    node.addClass('image-inline-right');
                }
            }
        };

        this._getEditareaViewportBoundary = function () {
            var win = $(window);

            var scroller_boundary = this._getScrollerBoundary();

            var viewport_boundary = $.extend({}, this._editarea[0].getBoundingClientRect());

            if (viewport_boundary.top > win.height() || viewport_boundary.bottom < 0 || viewport_boundary.left > win.width() || viewport_boundary.right < 0) {
                return null;
            }

            if (viewport_boundary.top > scroller_boundary.bottom || viewport_boundary.bottom < scroller_boundary.top || viewport_boundary.left > scroller_boundary.right || viewport_boundary.right < scroller_boundary.left) {
                return null;
            }

            if (viewport_boundary.top < scroller_boundary.top) {
                viewport_boundary.top = scroller_boundary.top;
            }

            if (viewport_boundary.bottom > scroller_boundary.bottom) {
                viewport_boundary.bottom = scroller_boundary.bottom;
            }

            if (viewport_boundary.left < scroller_boundary.left) {
                viewport_boundary.left = scroller_boundary.left;
            }

            if (viewport_boundary.right > scroller_boundary.right) {
                viewport_boundary.right = scroller_boundary.right;
            }

            this._editarea.trigger('getEditareaViewportBoundary', [viewport_boundary]);

            return viewport_boundary;
        };

        this.inline_mode = false;
        this.image_enable = true;
        this.list_enable = true;
        this.table_enable = true;
        this.scroller = null;
        this.scroller_modal = null;
        this.plugins = [];
        this.upload_url = null;
        this.value = '';
        this.set_focus = false;
        this.box_pathbar_enable = true;
        this.box_command_data = [['bold italic underline | heading hr | align_left align_center align_right align_justify | ul ol', 'quote lh_image | paragraph clearFormat']];
        this.box_max_height = 0;
        this.box_min_height = 100;
        this._box_topbar = null;
        this._box_bottombar = null;
        this._box_pathbar = null;
        this._element_packers = {};
        this._editor = null;
        this._textarea = null;
        this._editbox = null;
        this._editarea = null;
        this._fullscreen_mode = false;
        this._disable_restore_selection = false;
        this._normalize_element_callback = {};
        this._block_element_names = [
            'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'ul', 'li', 'ol',
            'address', 'article', 'aside', 'audio', 'canvas', 'div', 'dd', 'dl', 'dt', 'fieldset',
            'figcaption', 'figure', 'footer', 'form', 'header', 'hgroup', 'main', 'nav',
            'noscript', 'output', 'section', 'table', 'tbody', 'thead', 'tfoot', 'video'
        ];
        this._text_block_element_names = ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre'/*, 'ul', 'ol' */];
        this._allowed_block_element_names = ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'ul', 'ol', 'table'];
        this._special_block_element_names = ['blockquote', 'figure', 'pre'];
        this._remove_element_names = ['caption', 'col', 'colgroup', 'noembed', 'frame', 'frameset', 'input', 'button', 'select', 'script', 'style', 'noscript', 'keygen', 'datalist', 'output', 'progress'];
        this._allowed_element_names = ['span', 'em', 'strong', 'b', 'i', 'p', 'u', 'del', 'ins', 'br', 'sub', 'sup', 'a', 'strike', 'tr', 'td', 'th', 'img', 'li', 'tbody', 'tfoot', 'thead'];
        this._empty_filters = [];
        this._saved_selection = null;
        this._focused = false;
        this._node_allowed_attrs = {
            a: ['id', 'href', 'target'],
            img: ['src', 'width', 'height'],
            p: ['mrk-figure'],
            all: ['style'],
            td: ['colspan', 'rowspan']
        };
        this._node_allowed_style = {
            textBlock: ['text-align'],
            span: ['color', 'background-color']
        };

        this._context_callback = {};
        this._context_data = {};
        this._commands = {
            fullscreen: {cmd: 'fullscreen', icon: 'expand'},
            bold: {cmd: 'bold', context: 'bold', icon: 'bold'},
            italic: {cmd: 'italic', context: 'italic', icon: 'italic'},
            underline: {cmd: 'underline', context: 'underline', icon: 'underline'},
            strike: {cmd: 'strikeThrough', context: 'strikeThrough', icon: 'strikethrough'},
            anchor: {cmd: 'anchor', context: 'anchor', icon: 'bookmark'},
            link: {cmd: 'link', context: 'link', icon: 'link'},
            unlink: {cmd: 'unlink', context: 'link', icon: 'unlink'},
            align_left: {cmd: 'align', cmd_arg: 'left', context: 'justifyLeft', icon: 'align-left'},
            align_center: {cmd: 'align', cmd_arg: 'center', context: 'justifyCenter', icon: 'align-center'},
            align_right: {cmd: 'align', cmd_arg: 'right', context: 'justifyRight', icon: 'align-right'},
            align_justify: {cmd: 'align', cmd_arg: 'justify', context: 'justifyFull', icon: 'align-justify'},
            subscript: {cmd: 'subscript', context: 'subscript', icon: 'subscript'},
            superscript: {cmd: 'superscript', context: 'superscript', icon: 'superscript'},
            clearFormat: {cmd: 'removeFormat', icon: 'eraser'},
            quote: {cmd: 'convertBlock', cmd_arg: 'blockquote', context: 'blockname', context_arg: ['blockquote'], icon: 'quote'},
            code: {cmd: 'convertBlock', cmd_arg: 'pre', context: 'blockname', context_arg: ['pre'], icon: 'code'},
            ul: {cmd: 'list', cmd_arg: false, context: 'list', context_arg: [false], icon: 'list-ul'},
            ol: {cmd: 'list', cmd_arg: true, context: 'list', context_arg: [true], icon: 'list-ol'},
            image: {cmd: 'image', context: 'image', icon: 'image'},
            video: {cmd: 'video', icon: 'video'},
            hr: {cmd: 'hr', icon: 'line'},
            heading: {cmd: 'convertBlock', cmd_arg: 'h4', context: 'blockname', context_arg: ['h4'], icon: 'heading'},
            heading_small: {cmd: 'convertBlock', cmd_arg: 'h5', context: 'blockname', context_arg: ['h5'], icon: 'H3'},
            heading_medium: {cmd: 'convertBlock', cmd_arg: 'h3', context: 'blockname', context_arg: ['h3'], icon: 'H2'},
            heading_large: {cmd: 'convertBlock', cmd_arg: 'h1', context: 'blockname', context_arg: ['h1'], icon: 'H1'},
            paragraph: {cmd: 'paragraph', context: 'blockname', context_arg: ['p'], icon: 'paragraph'},
            heading_list: {cmd: 'convertBlock', cmd_arg: 'h1', context: 'blockname', context_arg: [['h1', 'h2', 'h3', 'h4', 'h5', 'h6']], icon: 'heading', sub: [
                    {cmd: 'convertBlock', cmd_arg: 'h1', context: 'blockname', context_arg: ['h1'], icon: 'heading', label: 'Heading 1'},
                    {cmd: 'convertBlock', cmd_arg: 'h2', context: 'blockname', context_arg: ['h2'], icon: 'heading', label: 'Heading 2'},
                    {cmd: 'convertBlock', cmd_arg: 'h3', context: 'blockname', context_arg: ['h3'], icon: 'heading', label: 'Heading 3'},
                    {cmd: 'convertBlock', cmd_arg: 'h4', context: 'blockname', context_arg: ['h4'], icon: 'heading', label: 'Heading 4'},
                    {cmd: 'convertBlock', cmd_arg: 'h5', context: 'blockname', context_arg: ['h5'], icon: 'heading', label: 'Heading 5'},
                    {cmd: 'convertBlock', cmd_arg: 'h6', context: 'blockname', context_arg: ['h6'], icon: 'heading', label: 'Heading 6'}
                ]},
            screen: {cmd: 'screen', cmd_arg: 'mobile-portrait', context: 'screen', icon: 'mobile', sub: [
                    {cmd: 'screen', cmd_arg: 'mobile-portrait', context: 'screen', context_arg: 'mobile-portrait', icon: 'mobile', label: 'Mobile Portrait'},
                    {cmd: 'screen', cmd_arg: 'mobile-landscape', context: 'screen', context_arg: 'mobile-portrait', icon: 'mobile', label: 'Mobile Portrait'},
                    {cmd: 'screen', cmd_arg: 'tablet-portrait', context: 'screen', context_arg: 'tablet-portrait', icon: 'tablet', label: 'Tablet Portrait'},
                    {cmd: 'screen', cmd_arg: 'tablet-landscape', context: 'screen', context_arg: 'tablet-landscape', icon: 'tablet', label: 'Tablet Portrait'},
                    {cmd: 'screen', cmd_arg: 'pc-1024', context: 'screen', context_arg: 'pc-1024', icon: 'television', label: 'Monitor: 1024'},
                    {cmd: 'screen', cmd_arg: 'pc-1280', context: 'screen', context_arg: 'pc-1280', icon: 'television', label: 'Monitor: 1280'},
                    {cmd: 'screen', cmd_arg: 'pc-1600', context: 'screen', context_arg: 'pc-1600', icon: 'television', label: 'Monitor: 1600'},
                    {cmd: 'screen', cmd_arg: 'custom', context: 'screen', context_arg: 'custom', icon: 'cog', label: 'Custom width'}
                ]}
        };
        this._script_root_path = window.OSC_EDITOR_ROOT_PATH;

        this._initialize(node, config);
    }


    $.fn.osc_editor = function () {
        var func = null, instance, opts = [], x;

        if (arguments.length > 0 && typeof arguments[0] === 'string') {
            func = arguments[0];
        }

        if (func) {
            for (x = 1; x < arguments.length; x++) {
                opts.push(arguments[x]);
            }
        } else {
            opts = arguments[0];
        }

        if (func) {
            instance = $(this[0]).data('osc-editor');

            if (instance) {
                switch (func.toLowerCase()) {
                    case 'getcontent':
                        return instance.getContent();
                    case 'setcontent':
                        instance.setContent(arguments[1]);
                        return $(this[0]);
                    case 'getinstance':
                        return instance;
                }
            }
        }

        return this.each(function () {
            instance = $(this).data('osc-editor');

            if (func) {
                if (instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if (!instance) {
                    new OSC_Editor(this, opts);
                }
            }
        });
    };

    window.initEditorBox = function () {
        $(this).osc_editor({
//            value: response.data,
            inline_mode: false,
            box_command_data: [['bold italic underline | heading hr | align_left align_center align_right align_justify | ul ol', 'rich_quote block_image | paragraph clearFormat']],
            plugins: [osc_editor_plugin_block_image()]
        });
    };
})(jQuery);