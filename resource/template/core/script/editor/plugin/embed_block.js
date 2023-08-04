(function ($) {
    'use strict';

    window.osc_editor_plugin_embed_block = function (config) {
        if (typeof config !== 'object' || config === null) {
            config = {};
        } else {
            for (var x in config) {
                if (x.indexOf('embed_block_') !== 0) {
                    delete(config[x]);
                }
            }
        }

        return {
            initialize: function () {
                $.extend(this, config);

                this.embed_block_max_item = parseInt(this.embed_block_max_item);

                if (isNaN(this.embed_block_max_item) || this.embed_block_max_item < 0) {
                    this.embed_block_max_item = 0;
                }

                if (this.embed_block_width === 'initial') {
                    this.embed_block_width = 560;
                }

                this.embed_block_width = parseInt(this.embed_block_width);

                if (isNaN(this.embed_block_width) || this.embed_block_width < 1) {
                    this.embed_block_width = 1;
                }

                if (this.embed_block_height === 'initial') {
                    this.embed_block_height = 315;
                }

                this.embed_block_height = parseInt(this.embed_block_height);

                if (isNaN(this.embed_block_height) || this.embed_block_height < 1) {
                    this.embed_block_height = 1;
                }

                this._commands.embed_block = {cmd: 'embedBlock', icon: 'video'};

                this._element_packers.embed_block = {
                    callback: this._embedBlockPackElement
                };

                this._addNormalizeElementCallback(['iframe', 'embed', 'video'], this._embedBlockNormalizeCallback);
            },
            extends: {
                embed_block_max_item: 0,
                embed_block_width: 'initial',
                embed_block_height: 'initial',
                embed_block_zoom_enable: true,
                embed_block_zoom_levels: 'initial',
                embed_block_zoom_align_levels: 'initial',
                embed_block_align_enable: true,
                embed_block_align_level_enable: 'initial',
                embed_block_align_lock_left: 'initial',
                embed_block_align_lock_right: 'initial',
                embed_block_align_lock_center: 'initial',
                embed_block_copy_enable: true,
                embed_block_delete_enable: true,
                embed_block_code_process_callback: null,
                _embedBlockNormalizeCallback: function (node) {
                    try {
                        var code = this._embedBlockGetCode($('<div />').append($(node).clone()).html());
                    } catch (e) {
                        return false;
                    }

                    var new_node = document.createElement('div');

                    node.parentNode.insertBefore(new_node, node);
                    node.parentNode.removeChild(node);

                    this._packEditorElement(new_node, false);

                    $(new_node).bind('editorElementUnpack', function (e, editor) {
                        if (editor.embed_block_max_item > 0 && editor._embedBlockCounter() >= editor.embed_block_max_item) {
                            this.parentNode.removeChild(this);
                        }

                        editor._selectNode(this, true, false, true, false);
                        editor._embedBlockRenderElement(code);
                    });

                    return true;
                },
                _embedBlockCounter: function () {
                    return this._editarea[0].querySelectorAll('.osc-editor-embed-block').length;
                },
                _embedBlockVerifyElement: function (node) {
                    if (!node || node.nodeType !== Node.ELEMENT_NODE || this._allowed_block_element_names.indexOf(node.nodeName.toLowerCase()) < 0 || node.childNodes.length !== 1 || node.className.indexOf('osc-editor-embed-block') < 0) {
                        return false;
                    }

                    if (node.firstChild.nodeName.toLowerCase() !== 'div' || node.firstChild.className.indexOf('content') < 0 || node.firstChild.childNodes.length < 1) {
                        return false;
                    }

                    return true;
                },
                _embedBlockPackElement: function (parent_node) {
                    var textboxs = parent_node.querySelectorAll('.osc-editor-embed-block');

                    var matched_flag = false;

                    var counter = this._embedBlockCounter() - textboxs.length;

                    for (var i = 0; i < textboxs.length; i++) {
                        if ((this.embed_block_max_item > 0 && counter >= this.embed_block_max_item) || this._embedBlockVerifyElement(textboxs[i]) === false) {
                            textboxs[i].parentNode.removeChild(textboxs[i]);
                            continue;
                        }

                        $(textboxs[i]).bind('editorElementUnpack', function (e, editor) {
                            this.contentEditable = false;
                            editor._embedBlockSetupControl(this);
                        });

                        this._packEditorElement(textboxs[i], true);

                        matched_flag = true;

                        counter++;
                    }

                    return matched_flag;
                },
                _embedBlockRender: function (embed_code) {
                    this._checkFocus();

                    this._historyAdd();

                    this._embedBlockRenderElement(embed_code);
                },
                _embedBlockRenderElement: function (embed_code) {
                    var block = document.createElement('div');

                    this._insertBlock(block);

                    this._nodeSetEditorElement(block, true);

                    block.contentEditable = false;
                    block.className = 'osc-editor-embed-block';

                    block.appendChild($('<div />').addClass('content').width(this.embed_block_width).height(this.embed_block_height).html(embed_code)[0]);

                    this._embedBlockSetupControl(block);
                },
                _embedBlockSetupControl: function (block) {
                    var controls = [];

                    if (this.embed_block_align_enable) {
                        controls.push({key: 'align', config: {
                                level_enable: this.embed_block_align_level_enable,
                                lock_left: this.embed_block_align_lock_left,
                                lock_right: this.embed_block_align_lock_right,
                                lock_center: this.embed_block_align_lock_center
                            }});
                        controls.push({key: 'separate'});
                    }

                    if (this.embed_block_zoom_enable) {
                        controls.push({key: 'zoom', config: {zoom_node: $(block).find('> .content'), constrain_proportions: true, levels: this.embed_block_zoom_levels, align_levels: this.embed_block_zoom_align_levels}});
                        controls.push({key: 'separate'});
                    }

                    controls.push({key: 'edit', config: {callback: this._commandTextbox}});

                    if (this.embed_block_copy_enable) {
                        controls.push({key: 'copy'});
                    }

                    if (this.embed_block_delete_enable) {
                        controls.push({key: 'delete'});
                    }

                    this._setupElementControl(block, {top: [controls]});
                },
                _embedBlockAnalyzeDataFromCode: function (code) {
                    var swap = $('<div />').html(code), youtube_code = '', youtube_regex = [
                        [/^(https?:)?\/\/(www\.)?youtu\.be\/([^\/?&#]+)([\/?&#].*)?/i, 3],
                        [/^(https?:)?\/\/(www\.)?(youtube-nocookie|youtube)\.com\/.*[?&]v=([^\/?&#]+)([\/?&#].*)?/i, 4],
                        [/^(https?:)?\/\/(www\.)?(youtube-nocookie|youtube)\.com(\/.+)?\/(v|embed)\/([^\/?&#]+)([\/?&#].*)?/i, 6]
                    ], url, obj_node, embed, param_nodes, i, params_map = ['wmode', 'movie', 'allowfullscreen', 'allowscriptaccess', 'quality', 'flashvars'], param_val, param_key, params = {};

                    if (swap.find('iframe')[0]) {
                        url = swap.find('iframe').attr('src');

                        if (!youtube_regex[2][0].test(url)) {
                            return false;
                        }

                        eval("youtube_code = url.replace(youtube_regex[2][0], '$" + youtube_regex[2][1] + "')");

                        code = {type: 'iframe', src: url};
                    } else if (swap.find('object')[0] || swap.find('embed')[0]) {
                        obj_node = swap.find('object');
                        embed = null;
                        param_nodes = null;

                        if (obj_node[0]) {
                            embed = obj_node.find('embed');
                            param_nodes = obj_node.find('param');
                        } else {
                            embed = swap.find('embed');
                        }

                        for (i = 0; i < params_map.length; i++) {
                            param_val = null;
                            param_key = params_map[i];

                            if (param_nodes && param_nodes[0]) {
                                param_nodes.each(function () {
                                    if ($(this).attr('name').toLowerCase() === param_key) {
                                        param_val = $(this).attr('value');
                                        return false;
                                    }
                                });
                            }

                            if (!param_val && embed[0]) {
                                param_val = embed.attr(param_key === 'movie' ? 'src' : param_key);
                            }

                            if (param_val) {
                                params[param_key] = param_val;
                            }
                        }

                        if (!params.movie) {
                            return false;
                        }

                        if (youtube_regex[1][0].test(params.movie)) {
                            eval("youtube_code = params.movie.replace(youtube_regex[1][0], '$" + youtube_regex[1][1] + "')");
                        }

                        code = {type: 'object', params: params};
                    } else {
                        url = code.trim();

                        code = '';

                        for (i = 0; i < youtube_regex.length; i++) {
                            if (youtube_regex[i][0].test(url)) {
                                eval("code = url.replace(youtube_regex[i][0], '$" + youtube_regex[i][1] + "')");
                            }
                        }

                        if (!code) {
                            return false;
                        }

                        youtube_code = code;

                        code = {type: 'iframe', src: 'https://www.youtube.com/embed/' + code};
                    }

                    if (youtube_code) {
                        code = {
                            type: 'iframe',
                            src: 'https://www.youtube.com/embed/' + youtube_code + '?showinfo=0',
                            thumbnail: 'http://i2.ytimg.com/vi/' + youtube_code + '/hqdefault.jpg'
                        };
                    }

                    return code;
                },
                _embedBlockGetCode: function (embed_code) {
                    var embed_data = this._embedBlockAnalyzeDataFromCode(embed_code);

                    if (!embed_data) {
                        throw "Your embed code/url is incorrect or not allowed to add";
                    }

                    var wrap = $('<div />');

                    if (embed_data.type === 'iframe') {
                        wrap.append($('<iframe />').attr('allowfullscreen', '').attr('src', embed_data.src));
                    } else {
                        var object_node = $('<object />').appendTo(wrap);
                        var embed_node = $('<embed />').prop('type', 'application/x-shockwave-flash').appendTo(object_node);

                        for (var x in embed_data.params) {
                            $('<param />').prop({
                                name: x,
                                value: embed_data.params[x]
                            }).appendTo(object_node);

                            embed_node.prop(x === 'movie' ? 'src' : x, embed_data.params[x]);
                        }
                    }

                    return wrap.html();
                },
                _commandEmbedBlock: function (textbox) {
                    if (!textbox && this.embed_block_max_item > 0 && this._embedBlockCounter() >= this.embed_block_max_item) {
                        alert('Bạn chỉ được tạo tối đa ' + this.embed_block_max_item + ' embed block');
                        return;
                    }

                    var embed_code = '';
                    var embed_block_content_node = null;

                    if (textbox) {
                        for (var x = 0; x < textbox.childNodes.length; x++) {
                            if (textbox.childNodes[x].className.indexOf('content') >= 0) {
                                embed_block_content_node = textbox.childNodes[x];
                                embed_code = embed_block_content_node.innerHTML;
                                break;
                            }
                        }
                    }

                    var self = this;
                    var win = null;
                    var container = $('<div />').addClass('osc-editor-win-frm').width(400);

                    $('<label />').attr('for', '').html('Paste your embed code or embed URL below:').appendTo(container);
                    var code_input = $('<textarea />').appendTo($('<div />').addClass('input-wrap').appendTo(container));

                    var action_bar = $('<div />').addClass('action-bar').appendTo(container);

                    $('<button />').html('Cancel').click(function () {
                        win.destroy();
                    }).appendTo(action_bar);

                    $('<button />').addClass('blue-btn').html('Update').click(function () {
                        embed_code = code_input.val();

                        if (!embed_code) {
                            alert('Please enter embed URL or embed code');
                            return;
                        }

                        try {
                            if (self.embed_block_code_process_callback) {
                                embed_code = self.embed_block_code_process_callback.apply(this, [embed_code]);
                            } else {
                                embed_code = self._embedBlockGetCode(embed_code);
                            }
                        } catch (error_message) {
                            alert(error_message);
                            return;
                        }

                        win.destroy();

                        if (embed_block_content_node) {
                            embed_block_content_node.innerHTML = embed_code;
                        } else {
                            self._embedBlockRender(embed_code);
                        }
                    }).appendTo(action_bar);

                    win = this._renderWindow(textbox ? 'Sửa mã embed block' : 'Thêm embed block mới', container);
                }
            }
        };
    };
})(jQuery);