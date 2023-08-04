(function ($) {
    'use strict';
    window.osc_editor_plugin_image = function (config) {
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

                this.image_max_item = parseInt(this.image_max_item);

                if (isNaN(this.image_max_item) || this.image_max_item < 0) {
                    this.image_max_item = 0;
                }

                this.image_min_width = parseInt(this.image_min_width);

                if (isNaN(this.image_min_width) || this.image_min_width < 0) {
                    this.image_min_width = 0;
                }

                this.image_min_height = parseInt(this.image_min_height);

                if (isNaN(this.image_min_height) || this.image_min_height < 0) {
                    this.image_min_height = 0;
                }

                this._commands.inline_image = {cmd: 'inlineImage', icon: 'image'};
            },
            extends: {
               image_max_item: 0,
                image_verify_url_callback: null,
                image_zoom_enable: true,
                image_zoom_levels: 'initial',
                image_zoom_align_levels: 'initial',
                image_align_enable: true,
                image_align_level_enable: 'initial',
                image_align_lock_left: 'initial',
                image_align_lock_right: 'initial',
                image_align_lock_center: 'initial',
                image_align_overflow_mode: 'initial',
                image_align_full_mode: 'initial',
                image_caption_enable: true,
                image_copy_enable: true,
                image_delete_enable: true,
                image_min_width: 0,
                image_min_height: 0,
                _imageCounter: function () {
                    return this._editarea[0].querySelectorAll('.osc-editor-inline-image').length;
                },
                _imageUploadCallback_Add: function (file_id, upload_progress_flag, skip_add_history) {
                    this._checkFocus();

                    if (!skip_add_history) {
                        this._historyAdd();
                    }

                    var img = document.createElement('img');
                    img.setAttribute('id', 'osc-editor-inline-image-' + file_id);
                    img.className = 'osc-editor-inline-image';

                    var canvas = document.createElement('canvas');
                    canvas.width = 100;
                    canvas.height = 100;

                    var ctx = canvas.getContext('2d');
                    ctx.globalAlpha = 0;
                    ctx.fillStyle = 'rgba(0,0,0,.5)';
                    ctx.fillRect(0, 0, 800, 600);
                    img.setAttribute('src',canvas.toDataURL('image/png'));

                    this._resizeImage(img);
                    this._commandInsertHTML(img, true);
                    this._imageSetupControl(img);
                },

                _resizeImage: function (img) {
                    // Minimum resizable area
                    var minWidth = 20;
                    var minHeight = 20;

                    // Thresholds
                    var MARGINS = 4;

                    // End of what's configurable.
                    var clicked = null;
                    var onRightEdge, onBottomEdge, onLeftEdge, onTopEdge;

                    var rightScreenEdge, bottomScreenEdge;

                    var b, x, y;

                    var redraw = false;

                    var pane = img;

                    // Mouse events
                    pane.addEventListener('mousedown', onMouseDown);
                    document.addEventListener('mousemove', onMove);
                    document.addEventListener('mouseup', onUp);


                    function onMouseDown(e) {
                        onDown(e);
                        e.preventDefault();
                    }

                    function onDown(e) {
                        calc(e);

                        var isResizing = onRightEdge || onBottomEdge || onTopEdge || onLeftEdge;

                        clicked = {
                            x: x,
                            y: y,
                            cx: e.clientX,
                            cy: e.clientY,
                            w: b.width,
                            h: b.height,
                            isResizing: isResizing,
                            onTopEdge: onTopEdge,
                            onLeftEdge: onLeftEdge,
                            onRightEdge: onRightEdge,
                            onBottomEdge: onBottomEdge
                        };
                    }



                    function calc(e) {
                        b = pane.getBoundingClientRect();
                        x = e.clientX - b.left;
                        y = e.clientY - b.top;

                        onTopEdge = y < MARGINS;
                        onLeftEdge = x < MARGINS;
                        onRightEdge = x >= b.width - MARGINS;
                        onBottomEdge = y >= b.height - MARGINS;

                        rightScreenEdge = window.innerWidth - MARGINS;
                        bottomScreenEdge = window.innerHeight - MARGINS;
                    }

                    var e;

                    function onMove(ee) {
                        calc(ee);

                        e = ee;

                        redraw = true;

                    }

                    function animate() {
                        requestAnimationFrame(animate);

                        if (!redraw) return;

                        redraw = false;

                        if (clicked && clicked.isResizing) {

                            if (clicked.onRightEdge) pane.style.width = Math.max(x, minWidth) + 'px';
                            if (clicked.onBottomEdge) pane.style.height = Math.max(y, minHeight) + 'px';

                            if (clicked.onLeftEdge) {
                                var currentWidth = Math.max(clicked.cx - e.clientX  + clicked.w, minWidth);
                                if (currentWidth > minWidth) {
                                    pane.style.width = currentWidth + 'px';
                                    pane.style.left = e.clientX + 'px';
                                }
                            }

                            if (clicked.onTopEdge) {
                                var currentHeight = Math.max(clicked.cy - e.clientY  + clicked.h, minHeight);
                                if (currentHeight > minHeight) {
                                    pane.style.height = currentHeight + 'px';
                                    pane.style.top = e.clientY + 'px';
                                }
                            }

                            return;
                        }

                        // style cursor
                        if (onRightEdge && onBottomEdge || onLeftEdge && onTopEdge) {
                            pane.style.cursor = 'nwse-resize';
                        } else if (onRightEdge && onTopEdge || onBottomEdge && onLeftEdge) {
                            pane.style.cursor = 'nesw-resize';
                        } else if (onRightEdge || onLeftEdge) {
                            pane.style.cursor = 'ew-resize';
                        } else if (onBottomEdge || onTopEdge) {
                            pane.style.cursor = 'ns-resize';
                        } else {
                            pane.style.cursor = 'default';
                        }
                    }

                    animate();

                    function onUp(e) {
                        calc(e);
                        clicked = null;
                    }
                },

                _imageGetByFileId: function (file_id) {
                    return document.getElementById('osc-editor-inline-image-' + file_id);
                },
                _imageSetupControl: function (block) {

                    var controls = [];

                    if (this.image_caption_enable) {
                        controls.push({key: 'imageLeft'});
                    }
                    if (this.image_caption_enable) {
                        controls.push({key: 'imageRight'});
                    }
                    if (this.image_caption_enable) {
                        controls.push({key: 'delete'});
                    }

                    if (controls.length < 1) {
                        return;
                    }

                    this._setupElementControl(block, {top: [controls]});
                },
                _imageUploadCallback_Success: function (file_id, response_data) {
                    var image = this._imageGetByFileId(file_id);
                    if (!image) {
                        return;
                    }

                    image = $(image);
                    image.attr('src', response_data.url);
                    this._imageSetupControl();
                },
                _imageUploadCallback_Error: function (file_id, error_code, error_message) {
                    var image = this._imageGetByFileId(file_id);
                    if (!image) {
                        return;
                    }

                    image = $(image);
                    image.append($('<div />').addClass('error-message').html((error_code > 0 ? '<div>ERROR [' + error_code + ']</div>' : '') + error_message));
                    setTimeout(function () {
                        image.fadeOut({complete: function () {
                            image.remove();
                        }});
                    }, 5000);
                },
                _imageInsertViaUrl: function (image_url, skip_add_history) {
                    var self = this;

                    var file_id = $.makeUniqid();

                    this._imageUploadCallback_Add(file_id, false, skip_add_history);

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
                        url: linkhay_post_url + '/core/editor/imageUpload',
                        success: function (response) {
                            if (response.result !== 'OK') {
                                self._imageUploadCallback_Error(file_id, response.code, response.message);
                            } else {
                                self._imageUploadCallback_Success(file_id, response.data);
                            }
                        }
                    });
                },
                _commandInlineImage: function () {
                    if (this.block_image_max_item > 0 && this._imageCounter() >= this.block_image_max_item) {
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

                        self._imageInsertViaUrl(image_url);
                    }).appendTo(action_bar);
                    win = this._renderWindow('Thêm ảnh', container);
                    uploader.osc_uploader({
                        max_files: 1,
                        max_connections: 1,
                        process_url: linkhay_post_url + '/core/editor/imageUpload',
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
                        self._imageUploadCallback_Add(file_id, true);
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var img = self._imageGetByFileId(file_id);
                            img.setAttribute('src', e.target.result);
                        };
                        reader.readAsDataURL(file);
                    }).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
                        eval('response = ' + response);
                        if (response.result !== 'OK') {
                            self._imageUploadCallback_Error(file_id, response.code, response.message);
                        } else {
                            self._imageUploadCallback_Success(file_id, response.data);
                        }
                    }).bind('uploader_error', function (e, file_id, error_code, error_message) {
                        self._imageUploadCallback_Error(file_id, error_code, error_message);
                    });
                }
            }
        };
    }
    })(jQuery);