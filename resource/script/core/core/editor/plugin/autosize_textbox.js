(function ($) {
    'use strict';

    window.editorAutosizeTextboxRenderCallback = function () {
        var block = $(this);
        var content = block.find('.content');
        var content_wrap = content.find('> div');

        var min_font_size = parseInt(block.attr('min-fs'));

        if (isNaN(min_font_size) || min_font_size < 1) {
            min_font_size = 16;
        }

        var max_font_size = parseInt(block.attr('max-fs'));

        if (isNaN(max_font_size) || max_font_size < 1) {
            max_font_size = 30;
        }

        var checking_flag = false;

        var _autoSetFontSize = function () {
            if (checking_flag) {
                return;
            }

            checking_flag = true;

            content[0].removeAttribute('style');

            var font_size = min_font_size;

            do {
                content.css('font-size', font_size + 'px');
                content.css('line-height', parseInt(font_size * 1.35) + 'px');

                font_size++;
            } while (/*content_wrap.width() < content.width() &&*/ content_wrap.height() < 200 && font_size <= max_font_size);

            checking_flag = false;
        };

        new ResizeSensor(this, _autoSetFontSize);

        _autoSetFontSize();
    };

    window.osc_editor_plugin_autosize_textbox = function (config) {
        if (typeof config !== 'object' || config === null) {
            config = {};
        } else {
            for (var x in config) {
                if (x.indexOf('autosize_textbox_') !== 0) {
                    delete(config[x]);
                }
            }
        }

        return {
            initialize: function () {
                $.extend(this, config);

                this.autosize_textbox_max_item = parseInt(this.autosize_textbox_max_item);

                if (isNaN(this.autosize_textbox_max_item) || this.autosize_textbox_max_item < 0) {
                    this.autosize_textbox_max_item = 0;
                }

                this.autosize_textbox_default_width = parseInt(this.autosize_textbox_default_width);

                if (isNaN(this.autosize_textbox_default_width) || this.autosize_textbox_default_width < 0) {
                    this.autosize_textbox_default_width = 0;
                }

                this.autosize_textbox_min_fs = parseInt(this.autosize_textbox_min_fs);

                if (isNaN(this.autosize_textbox_min_fs) || this.autosize_textbox_min_fs < 1) {
                    this.autosize_textbox_min_fs = 16;
                }

                this.autosize_textbox_max_fs = parseInt(this.autosize_textbox_max_fs);

                if (isNaN(this.autosize_textbox_max_fs) || this.autosize_textbox_max_fs < 1) {
                    this.autosize_textbox_max_fs = 32;
                } else if (this.autosize_textbox_max_fs < this.autosize_textbox_min_fs) {
                    this.autosize_textbox_max_fs = this.autosize_textbox_min_fs;
                }

                this._commands.autosize_textbox = {cmd: 'autosizeTextbox', icon: 'osc-editor-icon-textbox'};

                this._element_packers.autosize_textbox = {
                    callback: this._autosizeTextboxPackElement
                };
            },
            extends: {
                autosize_textbox_max_item: 0,
                autosize_textbox_default_width: 0,
                autosize_textbox_zoom_enable: true,
                autosize_textbox_zoom_levels: 'initial',
                autosize_textbox_zoom_align_levels: 'initial',
                autosize_textbox_align_enable: true,
                autosize_textbox_align_level_enable: 'initial',
                autosize_textbox_align_lock_left: 'initial',
                autosize_textbox_align_lock_right: 'initial',
                autosize_textbox_align_lock_center: 'initial',
                autosize_textbox_copy_enable: true,
                autosize_textbox_delete_enable: true,
                autosize_textbox_min_fs: 'initial',
                autosize_textbox_max_fs: 'initial',
                _autosizeTextboxCounter: function () {
                    return this._editarea[0].querySelectorAll('.osc-editor-autosize-textbox').length;
                },
                _autosizeTextboxVerifyElement: function (node) {
                    if (!node || node.nodeType !== Node.ELEMENT_NODE || this._allowed_block_element_names.indexOf(node.nodeName.toLowerCase()) < 0 || node.childNodes.length !== 1 || node.className.indexOf('osc-editor-autosize-textbox') < 0) {
                        return false;
                    }

                    if (node.firstChild.nodeName.toLowerCase() !== 'div' || node.firstChild.className.indexOf('content') < 0 || node.firstChild.childNodes.length < 1) {
                        return false;
                    }

                    return true;
                },
                _autosizeTextboxPackElement: function (parent_node) {
                    var textboxs = parent_node.querySelectorAll('.osc-editor-autosize-textbox');

                    var matched_flag = false;

                    var counter = this._autosizeTextboxCounter() - textboxs.length;

                    for (var i = 0; i < textboxs.length; i++) {
                        if ((this.autosize_textbox_max_item > 0 && counter >= this.autosize_textbox_max_item) || this._autosizeTextboxVerifyElement(textboxs[i]) === false) {
                            textboxs[i].parentNode.removeChild(textboxs[i]);
                            continue;
                        }

                        $(textboxs[i]).bind('editorElementUnpack', function (e, editor) {
                            this.contentEditable = false;
                            editor._autosizeTextboxSetupControl(this);
                        });

                        this._packEditorElement(textboxs[i], true);

                        matched_flag = true;

                        counter++;
                    }

                    return matched_flag;
                },
                _autosizeTextboxRender: function (html) {
                    this._checkFocus();

                    this._historyAdd();

                    var block = document.createElement('div');

                    this._insertBlock(block);

                    this._nodeSetEditorElement(block, true);

                    block.contentEditable = false;
                    block.className = 'osc-editor-autosize-textbox';
                    block.setAttribute('data-insert-cb', 'editorAutosizeTextboxRenderCallback');
                    block.setAttribute('min-fs', this.autosize_textbox_min_fs);
                    block.setAttribute('max-fs', this.autosize_textbox_max_fs);

                    block.appendChild($('<div />').addClass('content').append($('<div />').html(html))[0]);

                    if (this.autosize_textbox_default_width > 0) {
                        block.style.width = this.autosize_textbox_default_width + 'px';
                    }

                    this._autosizeTextboxSetupControl(block);
                },
                _autosizeTextboxSetupControl: function (block) {
                    var controls = [];

                    if (this.autosize_textbox_align_enable) {
                        controls.push({key: 'align', config: {
                                level_enable: this.autosize_textbox_align_level_enable,
                                lock_left: this.autosize_textbox_align_lock_left,
                                lock_right: this.autosize_textbox_align_lock_right,
                                lock_center: this.autosize_textbox_align_lock_center
                            }});
                        controls.push({key: 'separate'});
                    }

                    if (this.autosize_textbox_zoom_enable) {
                        controls.push({key: 'zoom', config: {constrain_proportions: false, levels: this.autosize_textbox_zoom_levels, align_levels: this.autosize_textbox_zoom_align_levels}});
                        controls.push({key: 'separate'});
                    }

                    controls.push({key: 'edit', config: {callback: this._commandAutosizeTextbox}});

                    if (this.autosize_textbox_copy_enable) {
                        controls.push({key: 'copy'});
                    }

                    if (this.autosize_textbox_delete_enable) {
                        controls.push({key: 'delete'});
                    }

                    this._setupElementControl(block, {top: [controls]});
                },
                _commandAutosizeTextbox: function (textbox) {
                    if (!textbox && this.autosize_textbox_max_item > 0 && this._autosizeTextboxCounter() >= this.autosize_textbox_max_item) {
                        alert('Bạn chỉ được tạo tối đa ' + this.autosize_textbox_max_item + ' textbox');
                        return;
                    }

                    var textbox_content = '';
                    var textbox_content_node = null;

                    if (textbox) {
                        for (var x = 0; x < textbox.childNodes.length; x++) {
                            if (textbox.childNodes[x].className.indexOf('content') >= 0) {
                                textbox_content_node = textbox.childNodes[x].firstChild;
                                textbox_content = textbox_content_node.innerHTML.br2nl();
                                break;
                            }
                        }
                    }

                    var self = this;
                    var win = null;
                    var container = $('<div />').addClass('osc-editor-win-frm').width(550);

                    var content_input = $('<textarea />').css('height', '200px').val(textbox_content).appendTo($('<div />').addClass('input-wrap').appendTo(container));

                    var action_bar = $('<div />').addClass('action-bar').appendTo(container);
                    $('<button />').html('Cancel').click(function () {
                        win.destroy();
                    }).appendTo(action_bar);
                    $('<button />').addClass('blue-btn').html('Update').click(function () {
                        textbox_content = content_input.val().trim();

                        if (!textbox_content) {
                            alert('Bạn chưa điền nội dung cho textbox');
                            return;
                        }

                        textbox_content = textbox_content.nl2br();

                        win.destroy();

                        if (textbox_content_node) {
                            self._checkFocus();
                            self._historyAdd();
                            textbox_content_node.innerHTML = textbox_content;
                        } else {
                            self._autosizeTextboxRender(textbox_content);
                        }
                    }).appendTo(action_bar);

                    win = this._renderWindow(textbox ? 'Sửa nội dung textbox' : 'Thêm textbox mới', container);
                }
            }
        };
    };
})(jQuery);