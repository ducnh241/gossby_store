(function ($) {
    'use strict';

    $.event.props.push('dataTransfer');

    var OSC_UPLOADER_DROP_OUTSIDE_DISABLED = false;

    try {
        if (!OSC_UPLOADER_DROP_OUTSIDE_DISABLED) {
            $(document).bind('dragover', function (e) {
                if (e.dataTransfer) {
                    e.dataTransfer.dropEffect = 'none';
                    e.preventDefault();
                }
            });

            OSC_UPLOADER_DROP_OUTSIDE_DISABLED = true;
        }
    } catch (e) {
    }

    function OSC_Uploader(node, config) {
        this._initialize = function (node, config) {
            if (!this._xhrSupported()) {
                console.error("The browser is not support OSC_Uploader");
                return;
            }

            var self = this, container, x;

            if (typeof config !== 'object') {
                config = {};
            } else {
                for (x in config) {
                    if (x.substring(0, 1) === '_') {
                        delete(config[x]);
                    }
                }
            }

            container = $(node);

            config._container = container;

            $.extend(this, config);

            this.max_connections = parseInt(this.max_connections);
            this.max_files = parseInt(this.max_files);
            this.max_filesize = parseInt(this.max_filesize);
            this.min_filesize = parseInt(this.min_filesize);

            if (!Array.isArray(this.extensions)) {
                this.extensions = (this.extensions + '').replace(/\s+/g, ',').replace(/,{2,}/g, ',').replace(/[^a-zA-Z0-9\,]/g, '').replace(/(^,|,$)/, '');

                if (this.extensions.length > 0) {
                    this.extensions = this.extensions.split(',');
                } else {
                    this.extensions = [];
                }
            }


            this._render();

            $(window).bind('beforeunload', function (e) {
                if (self._queue.length < 1) {
                    return;
                }

                var message = '';

                e.returnValue = message;

                return message;
            });

            $(document).bind('dragenter', function (e) {
                e.preventDefault();
            }).bind('dragover', function (e) {
                self._dragOverListener(e);
            }).bind('dragleave', function (e) {
                self._dragLeaveListener(e);
            });

            this._drag_drop_area.bind('dragover dragenter', function (e) {
                e.preventDefault();
                e.stopPropagation();
            }).bind('drop', function (e) {
                self._dropListener(e);
            });
        };

        this._render = function () {
            this._container.empty().addClass('osc-uploader');

            this._drag_drop_area = $('<div />').addClass('drag-drop-area').append($('<span />').html(this.dragdrop_content)).appendTo(this._container);

            this._renderInput();
        };

        this._renderInput = function () {
            var self = this;

            if (this._input) {
                this._input.closest('.browser-btn').remove();
            }

            this._input = $('<input />').attr({type: 'file', tabIndex: -1}).prependTo($('<div />').addClass('browser-btn').append($('<div />').html(this.btn_content)).appendTo(this._container));

            if (this.extensions.length > 0) {
                this._input.prop('accept', '.' + this.extensions.join(', .'));
            }

            if (this.max_files < 1 || ((this.max_files - this._uploaded_files) > 1)) {
                this._input.attr('multiple', 'multiple');
            }

            this._input.bind('change', function () {
                self._inputChangeListener();
            });
        };

        this._isValidFileDrag = function (e) {
            try {
                var data = e.dataTransfer;
                return data && data.effectAllowed !== 'none' && (data.files || (!$.browser.webkit && data.types.contains && data.types.contains('Files')));
            } catch (e) {
                return false;
            }
        };

        this._dragOverListener = function (e) {
            if (!this._isValidFileDrag(e)) {
                return;
            }

            if (this._drag_drop_area_hide_timeout) {
                clearTimeout(this._drag_drop_area_hide_timeout);
            }

            if (this._drag_drop_area[0] === e.target || this._drag_drop_area.has(e.target).length) {
                var effect = e.dataTransfer.effectAllowed;

                if (effect === 'move' || effect === 'linkMove') {
                    e.dataTransfer.dropEffect = 'move';
                } else {
                    e.dataTransfer.dropEffect = 'copy';
                }

                this._container.addClass('drag-entered');

                e.stopPropagation();
            } else {
                if (this._uploaderAvailable()) {
                    this._in_drag_drop = true;
                    this._container.addClass('dragdrop-active');
                }

                e.dataTransfer.dropEffect = 'none';
            }

            e.preventDefault();
        };

        this._dragEnterListener = function (e) {
            if (!this._isValidFileDrag(e)) {
                return;
            }

            this._container.addClass('drag-entered');

            e.stopPropagation();
        };

        this._dragLeaveListener = function (e) {
            if (!this._isValidFileDrag(e)) {
                return;
            }

            if (this._drag_drop_area[0] === e.target || this._drag_drop_area.has(e.target).length) {
                this._container.removeClass('drag-entered');
                e.stopPropagation();
            } else {
                if (this._drag_drop_area_hide_timeout) {
                    clearTimeout(this._drag_drop_area_hide_timeout);
                }

                var self = this;

                this._drag_drop_area_hide_timeout = setTimeout(function () {
                    self._in_drag_drop = false;

                    if (self._uploaderAvailable()) {
                        self._container.removeClass('dragdrop-active');
                    }
                }, 77);
            }
        };

        this._dropListener = function (e) {
            this._in_drag_drop = false;

            this._container.removeClass('dragdrop-active');
            this._container.removeClass('drag-entered');

            if (!this._isValidFileDrag(e)) {
                return;
            }

            e.preventDefault();

            this._processFiles(e.dataTransfer.files);
        };

        this._inputChangeListener = function () {
            this._processFiles(this._input[0].files);
            this._renderInput();
        };

        this._processFiles = function (files) {
            var file, i;

            if (this.callback_validate_files) {
                try {
                    this.callback_validate_files(files, this);
                } catch (e) {
                    this._error(null, 0, e.message);
                    return this;
                }
            }

            for (i = 0; i < files.length; i++) {
                if (!this._uploaderAvailable()) {
                    break;
                }

                file = files[i];

                if (!this._validateFile(file)) {
                    continue;
                }

                if (this.image_mode) {
                    this._addImage(file);
                } else {
                    this._addFile(file);
                }
            }
        };

        this._addImage = function (file) {
            var self = this, reader = new FileReader(), mime_type = null;

            reader.onload = function (e) {
                var bytes = (new Uint8Array(e.target.result));

                var hex_signature = [];

                for (var i = 0; i < 4; i++) {
                    var byte = bytes[i].toString(16).toUpperCase();

                    if (byte.length === 1) {
                        byte = '0' + byte;
                    }

                    hex_signature.push(byte);
                }

                //https://en.wikipedia.org/wiki/List_of_file_signatures
                //http://filesignatures.net/index.php?page=all&currentpage=3&order=SIGNATURE&sort=DESC

                switch (hex_signature.join(' ')) {
                    case '89 50 4E 47':
                        mime_type = 'image/png';
                        break;
                    case '47 49 46 38':
                        mime_type = 'image/gif';
                        break;
                    case 'FF D8 FF E0':
                    case 'FF D8 FF E1':
                    case 'FF D8 FF E2':
                    case 'FF D8 FF E3':
                    case 'FF D8 FF DB':
                        mime_type = 'image/jpeg';
                        break;
                    default:
                        self._error(null, 403, "File " + self._getFileNameFromFileObj(file) + " is not image");
                        return;
                        break;
                }

                self._addFile(file, mime_type, self._isAnimated(bytes));
            };

            reader.readAsArrayBuffer(this.detect_animated_gif ? file : file.slice(0, 4));
        };

        this._isAnimated = function (bytes) {
            if (bytes[0] !== 0x47 || bytes[1] !== 0x49 || bytes[2] !== 0x46 || bytes[3] !== 0x38) {
                return false;
            }

            var bytes_to_read = bytes.length - 3;
            var frames = 0;

            for (var i = 4; i < bytes_to_read; i++) {
                if (bytes[i] === 0x00 && bytes[i + 1] === 0x21 && bytes[i + 2] === 0xF9) {
                    var block_length = bytes[i + 3];
                    var after_block = i + 4 + block_length;

                    if (after_block + 1 < bytes_to_read && bytes[after_block] === 0x00 && (bytes[after_block + 1] === 0x2C || bytes[after_block + 1] === 0x21)) {
                        frames++;
                    }
                }

                if (frames === 2) {
                    break;
                }
            }

            return frames === 2;
        };

        this._addFile = function (file, mime_type, is_animated) {
            var id = $.makeUniqid(true);

            this._files[id] = {
                file: file,
                loaded: 0,
                is_queue: true
            };

            this._queue.push(id);

            this._container.trigger('uploader_add_file', [id, file, this._getFileName(id), this._getFileSize(id), mime_type, is_animated]);

            this._dequeue();
        };

        this._getFileName = function (id, format) {
            return this._getFileNameFromFileObj(this._files[id].file, format);
        };

        this._getFileNameFromFileObj = function (file, format) {
            var name = typeof file.fileName !== 'undefined' ? file.fileName : file.name;
            return format ? this._formatFileName(name) : name;
        };

        this._formatFileName = function (name) {
            if (name.length > 33) {
                name = name.slice(0, 19) + '...' + name.slice(-13);
            }

            return name;
        };

        this._getFileSize = function (id, format) {
            return this._getFileSizeFromFileObj(this._files[id].file, format);
        };

        this._getFileSizeFromFileObj = function (file, format) {
            var size = typeof file.fileSize !== 'undefined' ? file.fileSize : file.size;
            return format ? $.formatSize(size) : size;
        };

        this._upload = function (id) {
            if (this._total_connections >= this.max_connections) {
                return;
            }

            this._total_connections++;

            this._files[id].is_queue = false;

            this._container.trigger('uploader_upload_start', [id, this._files[id].file, this._getFileName(id), this._getFileSize(id, true)]);

            if (!this._files[id]) {
                return;
            }

            var xhr = this._files[id].xhr = new XMLHttpRequest();
            xhr.withCredentials = true;
            var self = this;

            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    self._uploadProgressHook(id, e.total, e.loaded);
                }
            };

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        self._uploadCompleteHook(id);
                    } else {
                        self._error(id, xhr.status, xhr.statusText);
                    }
                }
            };

            xhr.onerror = function () {
                self._error(id, xhr.status, xhr.statusText);
            };

            xhr.open('POST', this._getProcessUrl(), true);

            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('X-File-Name', encodeURIComponent(this._getFileName(id)));
            xhr.setRequestHeader('Content-Type', 'application/octet-stream');

            xhr.send(this._files[id].file);
        };

        this.cancel = function (id) {
            console.log('UPLOAD CANCEL:' + id);
            this._container.trigger('uploader_cancel', [id]);

            if (!this._files[id]) {
                this._uploaded_files--;
                this._switchOnOffUploader();
            } else {
                var is_queue = this._files[id].is_queue;

                if (this._files[id].xhr) {
                    this._files[id].xhr.abort();
                }

                delete this._files[id];

                this._dequeue(id, is_queue);
            }
        };

        this.cancelAll = function () {
            for (var i = this._queue.length - 1; i >= 0; i--) {
                this.cancel(this._queue[i]);
            }
        };

        this._uploadProgressHook = function (id, total, loaded) {
            this._files[id].loaded = loaded;
            this._container.trigger('uploader_upload_progress', [id, this._files[id].file, total, loaded, loaded * 100 / total]);
        };


        this._error = function (id, code, message) {
            console.error("Uploader ERROR: " + message + '[' + code + ']');

            this._container.trigger('uploader_upload_error', [id, code, message]);

            if (id) {
                delete this._files[id];
            }

            this._dequeue(id);
        };

        this._uploadCompleteHook = function (id) {
            if (!this._files[id]) {
                return;
            }

            var xhr = this._files[id].xhr;

            var response = null;

            if (xhr.status === 200) {
                response = xhr.responseText;
            }

            var pointer = {success: true};

            this._container.trigger('uploader_upload_complete', [id, response, pointer]);

            if (pointer.success) {
                this._uploaded_files++;
            }

            delete this._files[id];

            this._dequeue(id);
        };

        this._dequeue = function (id, keep_connection) {
            if (id) {
                var i = this._queue.indexOf(id);

                if (i >= 0) {
                    this._queue.splice(i, 1);

                    if (!keep_connection) {
                        this._total_connections--;
                    }
                }
            }

            if (this._queue.length > 0 && this._queue.length > this._total_connections) {
                this._upload(this._queue[this._total_connections]);
            }

            this._switchOnOffUploader();
        };

        this._getProcessUrl = function () {
            var server_process_url = this.process_url;

            var request_params = {};

            if (this.request_params) {
                $.extend(request_params, this.request_params);
            }

            request_params = this._formatForPost(request_params);

            server_process_url += server_process_url.indexOf('?') > -1 ? '&' : '?';
            server_process_url += request_params;

            return server_process_url;
        };

        this._formatForPost = function (fields) {
            var str = '';

            try {
                for (var i in fields) {
                    str += i + '=' + this._encodeUrl(fields[i]) + '&';
                }
            } catch (e) {
            }

            return str;
        };

        this._encodeUrl = function (url) {
            return encodeURIComponent(url).replace(/['()]/g, escape).replace(/\*/g, '%2A').replace(/%(?:7C|60|5E)/g, unescape);

            url = url.toString();

            var regcheck = url.match(/[\x90-\xFF]/g);

            if (regcheck) {
                for (var i = 0; i < i.length; i++) {
                    url = url.replace(regcheck[i], '%u00' + (regcheck[i].charCodeAt(0) & 0xFF).toString(16).toUpperCase());
                }
            }

            return escape(url).replace(/\+/g, "%2B");
        };

        this._uploaderAvailable = function () {
            return this.max_files < 1 || (this._queue.length + this._uploaded_files) < this.max_files;
        };

        this._switchOnOffUploader = function () {
            this._container[this._uploaderAvailable() ? 'show' : 'hide']();
        };

        this._validateFile = function (file) {
            if (!(file instanceof File)) {
                this._error('typeError', name);
                return false;
            }

            var name, size;

            name = this._getFileNameFromFileObj(file);
            size = this._getFileSizeFromFileObj(file);

            if (!this._isAllowedExtension(name)) {
                this._error('typeError', name);
                return false;

            } else if (size === 0) {
                this._error('emptyError', name);
                return false;

            } else if (size && this.max_filesize && size > this.max_filesize) {
                this._error('sizeError', name);
                return false;

            } else if (size && this.min_filesize && size < this.min_filesize) {
                this._error('minSizeError', name);
                return false;
            }

            if (this.callback_validate_file) {
                try {
                    this.callback_validate_file(file, name, size, this);
                } catch (e) {
                    this._error(null, 0, e.message);
                    return false;
                }
            }

            return true;
        };

        this._isAllowedExtension = function (file_name) {
            if (this.extensions.length < 1) {
                return true;
            }

            return this.extensions.indexOf(file_name.indexOf('.') >= 0 ? file_name.replace(/.*[.]/, '').toLowerCase() : '') >= 0;
        };

        this._xhrSupported = function () {
            var input = document.createElement('input');
            input.type = 'file';

            return ('multiple' in input && typeof File !== 'undefined' && typeof (new XMLHttpRequest()).upload !== 'undefined');
        };

        this.process_url = '';
        this.request_params = {};
        this.max_connections = 5;
        this.max_files = 1;
        this.extensions = [];
        this.btn_content = 'Browser file(s) to upload';
        this.dragdrop_content = 'Drop file(s) here to upload';
        this.max_filesize = '';
        this.min_filesize = '';
        this.image_mode = false;
        this.detect_animated_gif = false;
        this.callback_validate_file = null;
        this.callback_validate_files = null;

        this._container = null;
        this._input = null;
        this._drag_drop_area = null;
        this._handler = {};
        this._total_connections = 0;
        this._uploaded_files = 0;
        this._files = {};
        this._queue = [];
        this._in_drag_drop = false;
        this._drag_drop_area_hide_timeout = null;

        this._initialize(node, config);
    }

    $.fn.osc_uploader = function () {
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

        return this.each(function () {
            if (func) {
                var instance = $(this).data('osc-uploader');
                instance[func].apply(instance, opts);
            } else {
                $(this).data('osc-uploader', new OSC_Uploader(this, opts));
            }
        });
    };

    window.init_osc_uploader = function (node) {
        $(node).osc_uploader($(node).getAttrConfig({
            process_url: 'process-url',
            request_params: ['request-params', true],
            max_connections: 'max-connections',
            max_files: 'max-files',
            extensions: 'extensions',
            btn_content: 'btn-content',
            dragdrop_content: 'dragdrop-content',
            max_filesize: 'max-filesize',
            min_filesize: 'min-filesize'
        }, {attr_prefix: 'osc-uploader-'}));
    };

    $(document).bind('insert', function (e, node) {
        $(node).findAll('.mrk-osc-uploader').each(function () {
            init_osc_uploader(this);
        });
    });

    $(document.body).find('.mrk-osc-uploader').each(function () {
        init_osc_uploader(this);
    });
})(jQuery);