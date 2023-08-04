(function ($) {
    'use strict';

    window.osc_editor_plugin_block_image = function (config) {
        if (typeof config !== 'object' || config === null) {
            config = {};
        } else {
            for (var x in config) {
                if (x.indexOf('block_image_') !== 0) {
                    delete(config[x]);
                }
            }
        }

        return {
            initialize: function () {
                $.extend(this, config);

                this.block_image_max_item = parseInt(this.block_image_max_item);

                if (isNaN(this.block_image_max_item) || this.block_image_max_item < 0) {
                    this.block_image_max_item = 0;
                }

                this.block_image_min_width = parseInt(this.block_image_min_width);

                if (isNaN(this.block_image_min_width) || this.block_image_min_width < 0) {
                    this.block_image_min_width = 0;
                }

                this.block_image_min_height = parseInt(this.block_image_min_height);

                if (isNaN(this.block_image_min_height) || this.block_image_min_height < 0) {
                    this.block_image_min_height = 0;
                }

                this._commands.block_image = {cmd: 'blockImage', icon: 'image'};
                this._element_packers.block_image = {
                    callback: this._blockImagePackElement
                };

                this._addNormalizeElementCallback(['img'], this._imageBlockNormalizeCallback);
            },
            extends: {
                block_image_max_item: 0,
                block_image_verify_url_callback: null,
                block_image_zoom_enable: true,
                block_image_zoom_levels: 'initial',
                block_image_zoom_align_levels: 'initial',
                block_image_align_enable: true,
                block_image_align_level_enable: 'initial',
                block_image_align_lock_left: 'initial',
                block_image_align_lock_right: 'initial',
                block_image_align_lock_center: 'initial',
                block_image_align_overflow_mode: 'initial',
                block_image_align_full_mode: 'initial',
                block_image_caption_enable: true,
                block_image_copy_enable: true,
                block_image_delete_enable: true,
                block_image_min_width: 0,
                block_image_min_height: 0,
				block_image_upload_url: null,
                _imageBlockNormalizeCallback: function (node) {
                    var img_url = node.getAttribute('src');

                    if (!img_url) {
                        return false;
                    }

                    var new_node = document.createElement('span');

                    node.parentNode.insertBefore(new_node, node);
                    node.parentNode.removeChild(node);

                    this._packEditorElement(new_node, false);

                    $(new_node).bind('editorElementUnpack', function (e, editor) {
                        if (editor.block_image_min_width < 1 && editor.block_image_min_height < 1) {
                            if (editor.block_image_max_item > 0 && editor._blockImageCounter() >= this.block_image_max_item) {
                                new_node.parentNode.removeChild(new_node);
                                return;
                            }

                            editor._selectNode(new_node, true, false, true, false);
                            editor._blockImageInsertViaUrl(img_url, true);                            

                            return;
                        }

                        var img = new Image();

                        img.onerror = function () {
                            new_node.parentNode.removeChild(new_node);
                        };

                        img.onload = function () {
                            if ((editor.block_image_min_width > 0 && img.width < editor.block_image_min_width) || (editor.block_image_min_height > 0 && img.height < editor.block_image_min_height)) {
                                new_node.parentNode.removeChild(new_node);
                                return;
                            }

                            if (editor.block_image_max_item > 0 && editor._blockImageCounter() >= this.block_image_max_item) {
                                new_node.parentNode.removeChild(new_node);
                                return;
                            }

                            editor._selectNode(new_node, true, false, true, false);
                            editor._blockImageInsertViaUrl(img_url, true);
                        };

                        img.src = img_url;
                    });

                    return true;
                },
                _blockImageVerifyElement: function (node) {
                    if (!node || node.nodeType !== Node.ELEMENT_NODE || this._allowed_block_element_names.indexOf(node.nodeName.toLowerCase()) < 0 || node.childNodes.length < 1 || node.childNodes.length > 2 || node.className.indexOf('osc-editor-block-image') < 0 || node.className.indexOf('uploading') >= 0) {
                        return false;
                    }

                    var img_container = node.firstChild.className.indexOf('img-container') < 0 ? node.lastChild : node.firstChild;

                    let note_name_first_child = img_container.firstChild.nodeName.toLowerCase();
                    if (img_container.nodeName.toLowerCase() !== 'div' || img_container.childNodes.length !== 1 || (note_name_first_child !== 'img' && note_name_first_child !== 'a')) {
                        return false;
                    }

                    if (this.block_image_verify_url_callback && this.block_image_verify_url_callback(img_container.firstChild.src) === false) {
                        return false;
                    }

                    if (node.childNodes.length > 1) {
                        var caption = img_container.nextSibling ? img_container.nextSibling : img_container.previousSibling;

                        if (caption.className.indexOf('caption') < 0) {
                            node.removeChild(caption);
                        }
                    }

                    return true;
                },
                _blockImageCounter: function () {
                    return this._editarea[0].querySelectorAll('.osc-editor-block-image').length;
                },
                _blockImagePackElement: function (parent_node) {
                    var block_images = parent_node.querySelectorAll('.osc-editor-block-image:not(.uploading)');

                    var matched_flag = false;

                    var counter = this._blockImageCounter() - block_images.length;

                    for (var i = 0; i < block_images.length; i++) {
                        if ((this.block_image_max_item > 0 && counter >= this.block_image_max_item) || this._blockImageVerifyElement(block_images[i]) === false) {
                            block_images[i].parentNode.removeChild(block_images[i]);
                            continue;
                        }

                        $(block_images[i]).bind('editorElementUnpack', function (e, editor) {
                            this.contentEditable = false;
                            editor._blockImageSetupControl(this);
                        });

                        this._packEditorElement(block_images[i], true);

                        matched_flag = true;

                        counter++;
                    }

                    return matched_flag;
                },
                _blockImageUploadCallback_Add: function (file_id, upload_progress_flag, skip_add_history) {
                    this._checkFocus();

                    if (!skip_add_history) {
                        this._historyAdd();
                    }

                    var block = document.createElement('div');

                    this._insertBlock(block);

                    this._nodeSetEditorElement(block, true);

                    block.setAttribute('id', 'osc-editor-block-image-' + file_id);
                    block.contentEditable = false;
                    block.className = 'osc-editor-block-image uploading';

                    var canvas = document.createElement('canvas');
                    canvas.width = 1000;
                    canvas.height = 600;

                    var ctx = canvas.getContext('2d');

                    ctx.globalAlpha = 0;
                    ctx.fillStyle = 'rgba(0,0,0,.5)';
                    ctx.fillRect(0, 0, 800, 600);

                    block.appendChild($('<div />').addClass('img-container').append($('<div />').addClass('upload-status' + (upload_progress_flag ? '' : ' no-progress')).append($('<div />'))).append($('<img />').attr('src', canvas.toDataURL('image/png')))[0]);

                    this._blockImageSetupControl(block);
                },
                _blockImageGetByFileId: function (file_id) {
                    return document.getElementById('osc-editor-block-image-' + file_id);
                },
                _blockImageSetupControl: function (block) {
                    var img = block.querySelector('.img-container img');

                    if (img.naturalWidth <= 0) {
                        var self = this;

                        setTimeout(function () {
                            self._blockImageSetupControl(block);
                        }, 250);

                        return;
                    }

                    var controls = [];

                    if (this.block_image_align_enable) {
                        controls.push({key: 'align', config: {
                                level_enable: this.block_image_align_level_enable,
                                lock_left: this.block_image_align_lock_left,
                                lock_right: this.block_image_align_lock_right,
                                lock_center: this.block_image_align_lock_center,
                                full_mode: this.block_image_align_full_mode,
                                overflow_mode: this.block_image_align_overflow_mode
                            }});
                        controls.push({key: 'separate'});
                    }

                    if (this.block_image_zoom_enable) {
                        controls.push({key: 'zoom', config: {constrain_proportions: false, max_width: img.naturalWidth, levels: this.block_image_zoom_levels, align_levels: this.block_image_zoom_align_levels}});
                        controls.push({key: 'separate'});
                    }

                    if (this.block_image_caption_enable) {
                        controls.push({key: 'caption'});
                    }

                    if (this.block_image_copy_enable) {
                        controls.push({key: 'copy'});
                    }

                    if (this.block_image_delete_enable) {
                        controls.push({key: 'delete'});
                    }

                    if (controls.length < 1) {
                        return;
                    }

                    this._setupElementControl(block, {top: [controls]});
                },
                _blockImageUploadCallback_UpdateUploadStatus: function (file_id, percent_uploaded) {
                    var block = this._blockImageGetByFileId(file_id);
                    if (!block) {
                        return;
                    }

                    block.querySelector('.upload-status').firstChild.style.width = percent_uploaded + '%';
                },
                _blockImageUploadCallback_Success: function (file_id, response_data) {
                    var block = this._blockImageGetByFileId(file_id);
                    if (!block) {
                        return;
                    }

                    block = $(block);
                    block.removeClass('uploading');
                    block.find('.upload-status').remove();
                    block.find('.img-container img').attr('src', response_data.url);
                    this._blockImageSetupControl(block[0]);
                },
                _blockImageUploadCallback_Error: function (file_id, error_code, error_message) {
                    var block = this._blockImageGetByFileId(file_id);
                    if (!block) {
                        return;
                    }

                    block = $(block);
                    block.removeClass('uploading');
                    block.find('.upload-status').remove();
                    block.append($('<div />').addClass('error-message').html((error_code > 0 ? '<div>ERROR [' + error_code + ']</div>' : '') + error_message));
                    setTimeout(function () {
                        block.fadeOut({complete: function () {
                                block.remove();
                            }});
                    }, 5000);
                },
                _blockImageInsertViaUrl: function (image_url, skip_add_history) {
                    var self = this;

                    var file_id = $.makeUniqid();

                    this._blockImageUploadCallback_Add(file_id, false, skip_add_history);

                    $.ajax({
                        type: 'post',
                        data: {
                            type: 'image',
                            image_url: image_url
                        },
                        crossDomain: true,
                        xhrFields: {withCredentials: true},
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-OSC-Cross-Request': 'OK'
                        },
                        url: this.block_image_upload_url,
                        success: function (response) {
                            if (response.result !== 'OK') {
                                self._blockImageUploadCallback_Error(file_id, response.code, response.message);
                            } else {
                                self._blockImageUploadCallback_Success(file_id, response.data);
                            }
                        }
                    });
                },
                _commandBlockImage: function () {
                    if (this.block_image_max_item > 0 && this._blockImageCounter() >= this.block_image_max_item) {
                        alert('Bạn chỉ được tạo tối đa ' + this.block_image_max_item + ' block ảnh');
                        return;
                    }

                    var self = this;
                    var win = null;
                    var container = $('<div />').addClass('osc-editor-win-frm').width(350);
                    $('<label />').attr('for', '').html('Điền URL của ảnh bạn muốn upload').appendTo(container);
                    var url_input = $('<input />').prop('type', 'text').appendTo($('<div />').addClass('input-wrap').appendTo(container));
                    $('<label />').attr('for', '').html('Hoặc upload ảnh từ máy của bạn').appendTo(container);
                    var uploader = $('<div />').addClass('osc-editor-block-img-uploader');
                    $('<div />').appendTo(container).append(uploader);
                    var action_bar = $('<div />').addClass('action-bar').appendTo(container);
                    $('<button />').html('Cancel').click(function () {
                        win.destroy();
                    }).appendTo(action_bar);
                    $('<button />').addClass('blue-btn').html('Upload ảnh').click(function () {
                        var image_url = url_input.val();
                        if (!image_url) {
                            alert('Bạn chưa điền URL cho ảnh');
                            return;
                        }

                        win.destroy();

                        self._blockImageInsertViaUrl(image_url);
                    }).appendTo(action_bar);
                    win = this._renderWindow('Thêm ảnh', container);
                    uploader.osc_uploader({
                        max_files: 1,
                        max_connections: 1,
                        process_url: this.block_image_upload_url,
                        btn_content: 'Chọn file ảnh từ máy của bạn',
                        dragdrop_content: 'Kéo và thả file vào đây để upload...',
                        image_mode: true,
                        xhrFields: {withCredentials: true},
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-OSC-Cross-Request': 'OK'
                        }
                    }).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
                        uploader.detach();
                        win.destroy();
                        self._blockImageUploadCallback_Add(file_id, true);
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var block = self._blockImageGetByFileId(file_id);
                            if (!block || block.className.indexOf('uploading') < 0) {
                                return;
                            }

                            block.getElementsByTagName('img')[0].src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
                        self._blockImageUploadCallback_UpdateUploadStatus(file_id, uploaded_percent);
                    }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                        eval('response = ' + response);
						console.log(response);
                        if (response.result !== 'OK') {
                            self._blockImageUploadCallback_Error(file_id, response.code, response.message);
                        } else {
                            self._blockImageUploadCallback_Success(file_id, response.data);
                        }
                    }).bind('uploader_error', function (e, file_id, error_code, error_message) {
                        self._blockImageUploadCallback_Error(file_id, error_code, error_message);
                    });
                }
            }
        };
    }
})(jQuery);