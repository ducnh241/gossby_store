(function ($) {
    'use strict';

    function OSC_NodeTextEditor(node, config) {
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

            config._node = $(node);

            $.extend(this, config);

            var self = this;

            this._node.prop('contentEditable', true)
                    .unbind('.nodeTextEditor')
                    .bind('keydown.nodeTextEditor', function (e) {
                        return self._listenerKeydown(e);
                    })
                    .bind('keyup.nodeTextEditor', function (e) {
                        return self._listenerKeyup(e);
                    })
                    .bind('paste.nodeTextEditor', function (e) {
                        return self._listenerPaste(e);
                    });

            this._historyAdd();
        };

        this._backupSelection = function () {
            var selection = window.getSelection();

            if (selection.rangeCount > 0) {
                this._saved_selection = selection.getRangeAt(0).cloneRange();
            } else {
                var range = document.createRange();

                selection.removeAllRanges();
                selection.addRange(range);

                this._saved_selection = range.cloneRange();
            }
        };

        this._pasteGetData = function (e, type) {
            for (var i = 0; i < e.originalEvent.clipboardData.types.length; i++) {
                if (e.originalEvent.clipboardData.types[i] === type) {
                    return e.originalEvent.clipboardData.getData(type);
                }
            }

            return false;
        };

        this._processContent = function (content) {
            content = $('<div />').html(content);

            if (this.multiline) {
                var new_line_marker = '{new_line:' + $.makeUniqid() + '}';

                content.find(this._block_element_names.join(',')).each(function () {
                    $(this).prepend(document.createTextNode(new_line_marker));
                });

                content.find('br').each(function () {
                    $(this).prepend(document.createTextNode(new_line_marker));
                });
            }

            content = content.text().trim();

            if (this.multiline) {
                content = content.split(new_line_marker).join('<br />');
            }

            return content;
        };

        this._processPasteContent = function (content) {
            var selection = window.getSelection(), range, frag, el, first_node, last_node;

            content = this._processContent(content);

            var trigger_data = {content: content};

            this._node.trigger('processPasteContent', [trigger_data]);

            if (typeof trigger_data.content !== 'string') {
                return;
            }

            content = trigger_data.content;

            if (this._saved_selection) {
                selection.removeAllRanges();
                selection.addRange(this._saved_selection);
            }

            this._node[0].focus();

            this._historyAdd();

            this._saved_selection = null;

            if (!selection.isCollapsed) {
                document.execCommand('delete', false, true);
            }

            el = document.createElement('div');

            el.innerHTML = content;

            frag = document.createDocumentFragment();

            while (el.firstChild) {
                last_node = frag.appendChild(el.firstChild);
            }

            first_node = frag.firstChild;

            range = selection.getRangeAt(0);
            range.collapse(true);
            range.insertNode(frag);

            if (last_node) {
                range = range.cloneRange();
                range.setStartAfter(last_node);

                range.collapse(true);

                selection.removeAllRanges();
                selection.addRange(range);
            }
        };

        this._history_data = [];
        this._history_idx = -1;
        this._history_skip_add_flag = false;

        this._historyAdd = function (skip_if_in_undo) {
            var content = this._node.html();

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
                range: this._getRange()
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

        this.historyReset = function () {
            this._history_data = [];
            this._history_idx = -1;
            this._historyAdd();
        };

        this._historyRestore = function () {
            this._node.html(this._history_data[this._history_idx].content);
            this._node[0].normalize();

            if (this._history_data[this._history_idx].range) {
                var selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(this._history_data[this._history_idx].range);
            }
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

        this._listenerKeyup = function (e) {
            if (!this._history_skip_add_flag) {
                if ([13, 37, 38, 39, 40].indexOf(e.keyCode) >= 0) { //Keys: Left + right + up + down arrow, enter                    
                    this._historyAdd(e.keyCode !== 13);
                }
            } else {
                this._history_skip_add_flag = false;
            }
        };

        this._listenerKeydown = function (e) {
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

            if (e.keyCode === 13) {
                if (this.multiline) {
                    if (this.maxLines) {
                        let currentLines = this.getContent().split(/<br\s*\/>/).length || 1;
                        if (currentLines >= this.maxLines) {
                            return false;
                        }
                    }
                    this._commandInsertHTML('<br />' + this._makeCaretMarker('new-line'));
                    this._moveCaretToMarker('new-line');
                }

                return false;
            } else if (e.ctrlKey || e.metaKey) {
                if (e.keyCode !== 86) {
                    if (e.keyCode === 65 || e.keyCode === 67 || e.keyCode === 88) {
                        return true;
                    }

                    return false;
                }

                this._backupSelection();

                var wrap = $('<div />').css({position: 'fixed', width: '1px', height: '1px', overflow: 'hidden', opacity: 0, top: '50%', left: '50%'}).prop('contentEditable', true).appendTo(document.body);

                wrap[0].focus();

                var self = this;

                wrap.bind('paste', function (e) {
                    e.stopPropagation();

                    setTimeout(function () {
                        wrap.remove();
                        self._processPasteContent(wrap.html());
                    }, 0);
                });
            }
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
            var nodes = this._node[0].getElementsByTagName('span');

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

        this._selectNode = function (node, collapse, select_text, to_start) {
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

        this._listenerPaste = function (e) {
            e.preventDefault();

            _title_backup_selection();

            var pasted_content = '';

            if (window.clipboardData && window.clipboardData.getData) {
                pasted_content = window.clipboardData.getData('Text');
            } else if (e.originalEvent.clipboardData && e.originalEvent.clipboardData.getData) {
                pasted_content = this._pasteGetData(e, 'text/html');

                if (pasted_content === false) {
                    pasted_content = this._pasteGetData(e, 'text/plain');
                    pasted_content = pasted_content.replace('\n', '<br />');
                }
            } else {
                pasted_content = '';
            }

            this._processPasteContent(pasted_content);
        };

        this.getContent = function () {
            if (!this.multiline) {
                return this._node.text().trim();
            }

            var new_line_marker = '{new_line:' + $.makeUniqid() + '}';

            return $('<div />').html(this._node.html().replace(/<br\s*\/?>/ig, new_line_marker)).text().split(new_line_marker).join("\n").trim().split("\n").join('<br />');
        };

        this.setContent = function (content) {
            this._node.html(this._processContent(content));
        };

        this.multiline = false;
        this._node = null;
        this._saved_selection = null;
        this._block_element_names = [
            'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'ul', 'li', 'ol',
            'address', 'article', 'aside', 'audio', 'canvas', 'div', 'dd', 'dl', 'dt', 'fieldset',
            'figcaption', 'figure', 'footer', 'form', 'header', 'hgroup', 'main', 'nav',
            'noscript', 'output', 'section', 'table', 'tbody', 'thead', 'tfoot', 'video'
        ];

        this._initialize(node, config);
    }

    $.fn.osc_nodeTextEditor = function () {
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
            instance = $(this[0]).data('osc-nodeTextEditor');

            if (instance) {
                switch (func.toLowerCase()) {
                    case 'getcontent':
                        return instance.getContent();
                    case 'setcontent':
                        instance.setContent(arguments[1]);
                        return $(this[0]);
                }
            }
        }

        return this.each(function () {
            instance = $(this).data('osc-nodeTextEditor');

            if (func) {
                if (instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if (!instance) {
                    $(this).data('osc-nodeTextEditor', new OSC_NodeTextEditor(this, opts));
                }
            }
        });
    };

    window.initNodeTextEditor = function () {
        $(this).osc_nodeTextEditor({multiline: this.getAttribute('data-conf-multiline') === '1'});
    };
})(jQuery);