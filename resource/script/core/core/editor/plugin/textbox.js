(function ($) {
    'use strict';

    window.osc_editor_plugin_textbox = function (config) {
        if (typeof config !== 'object' || config === null) {
            config = {};
        } else {
            for (var x in config) {
                if (x.indexOf('textbox_') !== 0) {
                    delete(config[x]);
                }
            }
        }

        return {
            initialize: function () {
                $.extend(this, config);

                this.textbox_max_item = parseInt(this.textbox_max_item);

                if (isNaN(this.textbox_max_item) || this.textbox_max_item < 0) {
                    this.textbox_max_item = 0;
                }

                this.textbox_default_width = parseInt(this.textbox_default_width);

                if (isNaN(this.textbox_default_width) || this.textbox_default_width < 0) {
                    this.textbox_default_width = 0;
                }

                this._commands.textbox = {cmd: 'textbox', icon: 'osc-editor-icon-textbox'};

                this._element_packers.textbox = {
                    callback: this._textboxPackElement
                };
            },
            extends: {
                textbox_max_item: 0,
                textbox_default_width: 0,
                textbox_zoom_enable: true,
                textbox_zoom_levels: 'initial',
                textbox_zoom_align_levels: 'initial',
                textbox_align_enable: true,
                textbox_align_level_enable: 'initial',
                textbox_align_lock_left: 'initial',
                textbox_align_lock_right: 'initial',
                textbox_align_lock_center: 'initial',
                textbox_copy_enable: true,
                textbox_delete_enable: true,
                _textboxCounter: function () {
                    return this._editarea[0].querySelectorAll('.osc-editor-textbox').length;
                },
                _textboxVerifyElement: function (node) {
                    if (!node || node.nodeType !== Node.ELEMENT_NODE || this._allowed_block_element_names.indexOf(node.nodeName.toLowerCase()) < 0 || node.childNodes.length !== 1 || node.className.indexOf('osc-editor-textbox') < 0) {
                        return false;
                    }

                    if (node.firstChild.nodeName.toLowerCase() !== 'div' || node.firstChild.className.indexOf('content') < 0 || node.firstChild.childNodes.length < 1) {
                        return false;
                    }

                    return true;
                },
                _textboxPackElement: function (parent_node) {
                    var textboxs = parent_node.querySelectorAll('.osc-editor-textbox');

                    var matched_flag = false;

                    var counter = this._textboxCounter() - textboxs.length;

                    for (var i = 0; i < textboxs.length; i++) {
                        if ((this.textbox_max_item > 0 && counter >= this.textbox_max_item) || this._textboxVerifyElement(textboxs[i]) === false) {
                            textboxs[i].parentNode.removeChild(textboxs[i]);
                            continue;
                        }

                        $(textboxs[i]).bind('editorElementUnpack', function (e, editor) {
                            this.contentEditable = false;
                            editor._textboxSetupControl(this);
                        });

                        this._packEditorElement(textboxs[i], true);

                        matched_flag = true;

                        counter++;
                    }

                    return matched_flag;
                },
                _textboxRender: function (html) {
                    this._checkFocus();

                    this._historyAdd();

                    var block = document.createElement('div');

                    this._insertBlock(block);

                    this._nodeSetEditorElement(block, true);

                    block.contentEditable = false;
                    block.className = 'osc-editor-textbox';

                    block.appendChild($('<div />').addClass('content').html(html)[0]);

                    if (this.textbox_default_width > 0) {
                        block.style.width = this.textbox_default_width + 'px';
                    }

                    this._textboxSetupControl(block);
                },
                _textboxSetupControl: function (block) {
                    var controls = [];

                    if (this.textbox_align_enable) {
                        controls.push({key: 'align', config: {
                                level_enable: this.textbox_align_level_enable,
                                lock_left: this.textbox_align_lock_left,
                                lock_right: this.textbox_align_lock_right,
                                lock_center: this.textbox_align_lock_center,
                            }});
                        controls.push({key: 'separate'});
                    }

                    if (this.textbox_zoom_enable) {
                        controls.push({key: 'zoom', config: {constrain_proportions: false, levels: this.textbox_zoom_levels, align_levels: this.textbox_zoom_align_levels}});
                        controls.push({key: 'separate'});
                    }

                    controls.push({key: 'edit', config: {callback: this._commandTextbox}});

                    if (this.textbox_copy_enable) {
                        controls.push({key: 'copy'});
                    }

                    if (this.textbox_delete_enable) {
                        controls.push({key: 'delete'});
                    }

                    this._setupElementControl(block, {top: [controls]});
                },
                _commandTextbox: function (textbox) {
                    if (!textbox && this.textbox_max_item > 0 && this._textboxCounter() >= this.textbox_max_item) {
                        alert('Bạn chỉ được tạo tối đa ' + this.textbox_max_item + ' textbox');
                        return;
                    }

                    var textbox_content = '';
                    var textbox_content_node = null;

                    if (textbox) {
                        for (var x = 0; x < textbox.childNodes.length; x++) {
                            if (textbox.childNodes[x].className.indexOf('content') >= 0) {
                                textbox_content_node = textbox.childNodes[x];
                                textbox_content = textbox_content_node.innerHTML;
                                break;
                            }
                        }
                    }

                    var self = this;
                    var win = null;
                    var container = $('<div />').addClass('osc-editor-win-frm').width(650);

                    var content_input = $('<div />').appendTo($('<div />').addClass('input-wrap').appendTo(container));

                    var action_bar = $('<div />').addClass('action-bar').appendTo(container);
                    $('<button />').html('Cancel').click(function () {
                        win.destroy();
                    }).appendTo(action_bar);
                    $('<button />').addClass('blue-btn').html('Update').click(function () {
                        textbox_content = content_input.osc_editor('getContent');

                        if (!textbox_content) {
                            alert('Bạn chưa điền nội dung cho textbox');
                            return;
                        }

                        win.destroy();

                        if (textbox_content_node) {
                            self._checkFocus();
                            self._historyAdd();
                            textbox_content_node.innerHTML = textbox_content;
                        } else {
                            self._textboxRender(textbox_content);
                        }
                    }).appendTo(action_bar);

                    win = this._renderWindow(textbox ? 'Sửa nội dung textbox' : 'Thêm textbox mới', container);

                    content_input.osc_editor({
                        value: textbox_content,
                        image_enable: false,
                        scroller: this.scroller[0],
                        inline_mode: false,
                        box_min_height: 250,
                        box_max_height: 250,
                        box_pathbar_enable: false,
                        box_command_data: [['bold italic underline | align_left align_center align_right align_justify | ul ol', 'paragraph clearFormat']]
                    });
                }
            }
        };
    };
})(jQuery);