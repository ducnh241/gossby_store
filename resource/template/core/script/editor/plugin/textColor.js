(function ($) {
    'use strict';

    window.osc_editor_plugin_textColor = function (config) {

        if (typeof config !== 'object' || config === null) {
            config = {};
        } else {
            for (var x in config) {
                if (x.indexOf('textColor_') !== 0) {
                    delete(config[x]);
                }
            }
        }

        return {
            initialize: function () {
                $.extend(this, config);
                this._commands.textColor = {cmd: 'textColor', context: 'textColor', icon: 'font', initialize: '_initCommandTextColor'};
            },
            extends: {
                _color_preview: [],
                _initCommandTextColor: function (node) {
                    if (typeof this._node_allowed_style.span === 'undefined') {
                        this._node_allowed_style.span = [];
                    }

                    this._node_allowed_style.span.push('color');

                    var self = this;

                    node.className = node.className + ' osc-editor__text-color';
                    this._color_preview.push($("<span/>").addClass('osc-editor__color-preview').appendTo(node));

                    $(node).osc_colorPicker({
                        swatch_name: 'osc_editor_textColor',
                        callback: function (color) {
                            self._checkFocus();
                            self._commandTextColor(color);
                        }
                    });

                },
                _setTextColor: function (node, node_offset, color, left_direction_flag, break_node) {
                    if (this._nodeIsBlock(node)) {
                        this._removeOldTextColor(node);

                        var span = $('<span />').css('color', color)[0];

                        while (node.firstChild) {
                            span.appendChild(node.firstChild);
                        }

                        node.appendChild(span);

                        return;
                    }

                    if (node.nodeType === Node.TEXT_NODE) {
                        if (left_direction_flag) {
                            if (node_offset < node.textContent.length) {
                                node.parentNode.insertBefore(document.createTextNode(node.textContent.substring(0, node_offset)), node);
                                node.textContent = node.textContent.substring(node_offset);

                                node = node.previousSibling;
                            }
                        } else {
                            if (node_offset > 0) {
                                node.parentNode.insertBefore(document.createTextNode(node.textContent.substring(0, node_offset)), node);
                                node.textContent = node.textContent.substring(node_offset);
                            }
                        }
                    }

                    var split_marker = this._makeCaretMarker(null, true);

                    if (left_direction_flag) {
                        if (node.nextSibling) {
                            node.parentNode.insertBefore(split_marker, node.nextSibling);
                        } else {
                            node.parentNode.appendChild(split_marker);
                        }
                    } else {
                        node.parentNode.insertBefore(split_marker, node);
                    }

                    this._splitColorNode(split_marker);

                    split_marker.parentNode.removeChild(split_marker);

                    var block = this._nodeGetBlock(node);

                    do {
                        if (!node[left_direction_flag ? 'nextSibling' : 'previousSibling'] && !this._nodeIsBlock(node.parentNode) && (!break_node || node.parentNode !== break_node)) {
                            node = node.parentNode;
                            continue;
                        }

                        var span = document.createElement('span');
                        span.style.color = color;

                        var break_flag = break_node && node.parentNode === break_node;

                        if (!break_flag) {
                            while (node[left_direction_flag ? 'previousSibling' : 'nextSibling']) {
                                span[left_direction_flag ? 'prepend' : 'appendChild'](node[left_direction_flag ? 'previousSibling' : 'nextSibling']);
                            }
                        }

                        node.parentNode.insertBefore(span, node);

                        span[left_direction_flag ? 'appendChild' : 'prepend'](node);

                        this._removeOldTextColor(span);

                        if (break_flag || this._nodeIsBlock(node.parentNode)) {
                            break;
                        }

                        node = span.parentNode;

                        while (node && !node[left_direction_flag ? 'previousSibling' : 'nextSibling']) {
                            node = node.parentNode;

                            if (this._nodeIsBlock(node) || (break_node && break_node === node)) {
                                node = null;
                            }
                        }

                        if (node) {
                            if (break_node && node.parentNode === break_node) {
                                break;
                            }

                            node = node[left_direction_flag ? 'previousSibling' : 'nextSibling'];
                        }
                    } while (node && !this._nodeIsBlock(node));

                    this._normalizeBlockTextColor(block);
                },
                _splitColorNode: function (split_node) {
                    var self = this;

                    do {
                        var css_hash = {};

                        var span = this._getParentNode(split_node, 'span', function (node) {
                            css_hash = node.getParsedCssText();
                            return css_hash.color;
                        }, null, function (node) {
                            return self._nodeIsBlock(node);
                        });

                        if (span) {
                            var color = css_hash.color;
                            delete css_hash.color;

                            $(span).removeAttr('style').css(css_hash);

                            do {
                                var data = {nextSibling: ['append', 'insertAfter'], previousSibling: ['prepend', 'insertBefore']};

                                for (var sibling_func in data) {
                                    if (!split_node[sibling_func]) {
                                        continue;
                                    }

                                    var new_span = $('<span />').css('color', color);

                                    while (split_node[sibling_func]) {
                                        new_span[data[sibling_func][0]](split_node[sibling_func]);
                                    }

                                    new_span[data[sibling_func][1]](split_node);
                                }

                                split_node = split_node.parentNode;
                            } while (split_node !== span);

                            var css_counter = 0;

                            for (var k in css_hash) {
                                css_counter++;
                            }

                            if (span.attributes.length < 1 && css_counter < 1) {
                                while (span.firstChild) {
                                    span.parentNode.insertBefore(span.firstChild, span);
                                }

                                span.parentNode.removeChild(span);
                            }
                        }
                    } while (span);
                },
                _removeOldTextColor: function (node) {
                    var spans = node.querySelectorAll('span[style]');

                    if (spans.length > 0) {
                        for (var i = 0; i < spans.length; i++) {
                            var span = spans[i];
                            var css_hash = span.getParsedCssText();

                            if (!css_hash.color) {
                                continue;
                            }

                            delete css_hash.color;

                            $(span).removeAttr('style').css(css_hash);

                            var css_counter = 0;

                            for (var k in css_hash) {
                                css_counter++;
                            }

                            if (span.attributes.length < 1 && css_counter < 1) {
                                while (span.firstChild) {
                                    span.parentNode.insertBefore(span.firstChild, span);
                                }

                                span.parentNode.removeChild(span);
                            }
                        }
                    }
                },
                _commandTextColor: function (color) {
                    if (!color) {
                        return;
                    }

                    var self = this;
                    var range = this._getRange();
                    var ancestor = this._getCommonAncestor(range.startContainer, range.endContainer);

                    var saved_selection = this._saveSelectionRange(this._nodeIsBlock(ancestor) ? ancestor : this._nodeGetBlock(ancestor));

                    var start_block = this._nodeGetBlock(range.startContainer);
                    var end_block = this._nodeGetBlock(range.endContainer);

                    if (start_block === end_block) {
                        var snode = range.startContainer;
                        var enode = range.endContainer;

                        var nodes = [];

                        if (snode === enode) {
                            snode.parentNode.insertBefore(document.createTextNode(snode.textContent.substring(0, range.startOffset)), snode);

                            if (snode.nextSibling) {
                                snode.parentNode.insertBefore(document.createTextNode(snode.textContent.substring(range.endOffset)), snode.nextSibling);
                            } else {
                                snode.parentNode.appendChild(document.createTextNode(snode.textContent.substring(range.endOffset)));
                            }

                            snode.textContent = snode.textContent.substring(range.startOffset, range.endOffset);

                            this._splitColorNode(snode);

                            nodes.push(snode);
                        } else {
                            this._setTextColor(snode, range.startOffset, color, false, ancestor);
                            this._setTextColor(enode, range.endOffset, color, true, ancestor);

                            while (snode.parentNode !== ancestor) {
                                snode = snode.parentNode;
                            }

                            while (enode.parentNode !== ancestor) {
                                enode = enode.parentNode;
                            }

                            while (snode.nextSibling && snode.nextSibling !== enode) {
                                snode = snode.nextSibling;
                                nodes.push(snode);
                            }
                        }

                        if (nodes.length > 0) {
                            var span = $('<span />').css('color', color)[0];

                            enode.parentNode.insertBefore(span, enode);

                            for (var i = 0; i < nodes.length; i++) {
                                span.appendChild(nodes[i]);
                            }

                            this._removeOldTextColor(span);
                            this._normalizeBlockTextColor(start_block);
                        }
                    } else {
                        this._setTextColor(range.startContainer, range.startOffset, color, false);

                        var blocks = this._getSelectedNodes(function (node) {
                            return self._nodeIsTextBlock(node) && node !== start_block && node !== end_block;
                        });

                        for (var i = 0; i < blocks.length; i++) {
                            this._setTextColor(blocks[i], 0, color, false);
                        }

                        this._setTextColor(range.endContainer, range.endOffset, color, true);
                    }

                    this._setSelectionRange(saved_selection);
                },
                _normalizeBlockTextColor: function (block) {
                    var spans = block.querySelectorAll('span[style]');

                    for (var i = 0; i < spans.length; i++) {
                        var span = spans[i];

                        var css_hash = span.getParsedCssText();

                        if (!css_hash.color) {
                            continue;
                        }

                        var nested_spans = span.querySelectorAll('span[style]');

                        for (var k = 0; k < nested_spans.length; k++) {
                            var nested_span = nested_spans[k];

                            var nested_css_hash = nested_span.getParsedCssText();

                            if (nested_css_hash.color) {
                                this._splitColorNode(nested_span);
                                return this._normalizeBlockTextColor(block);
                            }
                        }

                        if (Object.keys(css_hash).length > 1 || span.attributes.length > 1) {
                            continue;
                        }

                        var empty_flag = true;

                        for (var k = 0; k < span.childNodes.length; k++) {
                            if (span.childNodes[k].nodeType !== Node.TEXT_NODE || span.childNodes[k].textContent !== '') {
                                empty_flag = false;
                                break;
                            }
                        }

                        if (empty_flag) {
                            span.parentNode.removeChild(span);
                            continue;
                        }

                        var sibling = span.nextSibling;

                        while (sibling && sibling.nodeType === Node.TEXT_NODE && sibling.textContent === '') {
                            sibling = sibling.nextSibling;
                        }

                        if (!sibling || sibling.nodeName.toLowerCase() !== 'span') {
                            continue;
                        }

                        var sibling_css_hash = sibling.getParsedCssText();

                        if (sibling_css_hash.color !== css_hash.color || Object.keys(sibling_css_hash).length > 1 || sibling.attributes.length > 1) {
                            continue;
                        }

                        while (sibling.firstChild) {
                            span.appendChild(sibling.firstChild);
                        }

                        span.parentNode.removeChild(sibling);

                        return this._normalizeBlockTextColor(block);
                    }
                },
                _getColor: function () {
                    var ancestor = this._getSelectionCommonAncestor();

                    if (!ancestor || this._nodeIsBlock(ancestor)) {
                        return null;
                    }

                    var self = this;

                    var color = null;

                    this._getParentNode(ancestor, 'span', function (node) {
                        color = node.getParsedCssText().color;
                        return color;
                    }, null, function (node) {
                        return self._nodeIsBlock(node);
                    });

                    return color;
                },
                _contextTextColor: function () {
                    var color = this._getColor();

                    for (var i = 0; i < this._color_preview.length; i++) {
                        this._color_preview[i].css('background', color ? color : '#333');
                    }

                    return color ? true : false;
                }
            }
        };
    };
})(jQuery);