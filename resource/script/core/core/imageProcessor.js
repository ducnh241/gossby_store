(function($){
    function OSC_Image_Processor(img, opts) {        
        this.config = function(options) {
            if(typeof options != 'object') {
                options = {};
            }
            
            for(var k in options) {
                if(k.substr(0,1) == '_') {
                    delete options[k];
                }
            }

            $.extend(this, options);
        };
        
        this.exec = function(method, params) {
            this._exec(method, params);
        };
        
        this._execRecall = function() {
            if(! this._ready_flag || this._watermask_processing_flag) {
                var self = this;
                clearTimeout(this._recall_timer);
                this._recall_timer = setTimeout(function(){ self._execRecall(); }, 100);
                return;
            }
            
            if(this._callback_registry.length <= 0) {
                return;
            }  
            
            var callback = this._callback_registry.shift();
            this._exec(callback.method, callback.params, true);
            this._execRecall();
        };
        
        this._exec = function(method, params, is_callback) {
            if(! this._ready_flag || this._watermask_processing_flag || (this._callback_registry.length > 0 && ! is_callback)) {
                var self = this;
                this._callback_registry.push({method : method, params : params});
                clearTimeout(this._recall_timer);
                this._recall_timer = setTimeout(function(){ self._execRecall(); }, 100);
                return;
            }
                
            this['_' + method].apply(this, params);
        };
        
        this._render = function(callback) {
            this._target.attr('src', this._image.attr('src'));
            
            try {
                callback();
            } catch(e) {};
        };
        
        this._addWatermask = function(watermask_url, watermask_position, watermask_dimension) {
            var self = this;
            
            this._watermask_processing_flag = true;
            
            var watermask = $('<img />').load(function(){
                self._processWatermask(watermask, watermask_position, watermask_dimension);
            });
            
            watermask.attr('src', watermask_url);
        };
        
        this._processWatermask = function(watermask, watermask_position, watermask_dimension) {
            var watermask_w = watermask.realWidth();
            var watermask_h = watermask.realHeight();
            
            if(typeof watermask_dimension != 'undefined') {
                if(typeof watermask_dimension != 'object') {
                    watermask_dimension = parseInt(watermask_dimension);
                    
                    if(isNaN(watermask_dimension) || watermask_dimension < 1) {
                        //ERROR: Watermask dimension is incorrect
                    }
                    
                    watermask_dimension = {w : watermask_dimension};
                }
                
                var watermask_new_w = 0;
                var watermask_new_h = 0;
            
                if(typeof watermask_dimension.w != 'undefined') {
                    watermask_new_w = parseInt(watermask_dimension.w);
                    
                    if(isNaN(watermask_new_w)) {
                        watermask_new_w = 0;
                    }
                }
            
                if(typeof watermask_dimension.h != 'undefined') {
                    watermask_new_h = parseInt(watermask_dimension.h);
                    
                    if(isNaN(watermask_new_h)) {
                        watermask_new_h = 0;
                    }
                }

                if (watermask_new_w < 1 && watermask_new_h < 1) {
                    //ERROR: Watermask dimension is incorrect
                }

                var watermask_max_w = watermask_new_w;
                var watermask_max_h = watermask_new_h;

                watermask_new_w = watermask_w;
                watermask_new_h = watermask_h;

                if (watermask_max_w > 0 && watermask_max_w < watermask_new_w) {
                    watermask_new_h *= watermask_max_w/watermask_new_w;
                    watermask_new_w  = watermask_max_w;
                }

                if (watermask_max_h > 0 && watermask_max_h < watermask_new_h) {
                    watermask_new_w *= watermask_max_h/watermask_new_h;
                    watermask_new_h  = watermask_max_h;
                }

                if (watermask_new_w != watermask_w || watermask_new_h != watermask_h) {
                    var watermask_canvas = document.createElement('canvas');
                    watermask_canvas.width = watermask_new_w;
                    watermask_canvas.height = watermask_new_h;
                    this._highDPICanvasDrawImage(watermask_canvas, watermask[0], 0, 0, watermask_w, watermask_h, 0, 0, watermask_new_w, watermask_new_h);

                    watermask_w = watermask_new_w;
                    watermask_h = watermask_new_h;
                    watermask.attr('src', watermask_canvas.toDataURL());
                }     
            }
            
            var x = this._width - watermask_w - 4;
            var y = this._height - watermask_h - 4;
            
            if(typeof watermask_position != 'undefined') {
                if(typeof watermask_position != 'object') {
                    watermask_position = parseInt(watermask_position);

                    if(watermask_position < 0) {
                        //ERROR: Watermask position is incorrect
                    }

                    watermask_position = {x : watermask_position};
                }

                if(typeof watermask_position.x != 'undefined') {
                    x = parseInt(watermask_position.x);

                    if((x + watermask_w) > this._width) {
                        x = this._width - watermask_w;
                    }

                    if(x < 0) {
                        //ERROR: Watermask position is incorrect
                    }
                }

                if(typeof watermask_position.y != 'undefined') {
                    y = parseInt(watermask_position.y);

                    if((y + watermask_h) > this._height) {
                        y = this._height - watermask_h;
                    }

                    if(y < 0) {
                        //ERROR: Watermask position is incorrect
                    }
                }
            }
            
            var canvas = document.createElement('canvas');
            canvas.width = this._width;
            canvas.height = this._height;
            canvas.getContext('2d').drawImage(this._image[0], 0, 0, this._width, this._height, 0, 0, this._width, this._height);
            
            this._highDPICanvasDrawImage(canvas, watermask[0], 0, 0, watermask_w, watermask_h, watermask_position.x, watermask_position.y, watermask_w, watermask_h);
            
            this._image.attr('src', canvas.toDataURL());
            
            this._watermask_processing_flag = false;
        };
        
        this._ratioResize = function(ratio) {
            ratio = parseFloat(ratio);
            
            if(ratio <= 0 || ratio == 1) {
                return;
            }
            
            ratio = $.round(ratio, 2);
            
            var nw = Math.ceil(this._width * ratio);
            var nh = Math.ceil(this._height * ratio);
            
            if(nw == this._width && nh == this._height) {
                return;
            }
                        
            var canvas = document.createElement('canvas');
            canvas.width = nw;
            canvas.height = nh;
            this._highDPICanvasDrawImage(canvas, this._image[0], 0, 0, this._width, this._height, 0, 0, nw, nh);
            
            this._width = nw;
            this._height = nh;
            this._image.attr('src', canvas.toDataURL());
        };
        
        this._percentResize = function(percent) {
            this._ratioResize(percent/100);
        };
        
        this._resize = function(nw, nh) {
            nw = parseInt(nw);
            nh = parseInt(nh);       
            
            if(isNaN(nw)) {
                nw = 0;
            }
            
            if(isNaN(nh)) {
                nh = 0;
            }
            
            if(nw < 1 && nh < 1) {
                return;
            }

            var max_w = nw;
            var max_h = nh;

            nw = this._width;
            nh = this._height;

            if (max_w > 0 && max_w < nw) {
                nh *= max_w/nw;
                nw  = max_w;
            }

            if (max_h > 0 && max_h < nh) {
                nw *= max_h/nh;
                nh  = max_h;
            }

            if (nw == this._width && nh == this._height) {
                return;
            }     
                        
            var canvas = document.createElement('canvas');
            canvas.width = nw;
            canvas.height = nh;
            this._highDPICanvasDrawImage(canvas, this._image[0], 0, 0, this._width, this._height, 0, 0, nw, nh);
            
            this._width = nw;
            this._height = nh;
            this._image.attr('src', canvas.toDataURL());
        };
        
        this._trimAndResize = function(nw, nh, fx, fy) {
            nw = parseInt(nw);
            nh = parseInt(nh);
            
            if(isNaN(nw)) {
                nw = 0;
            }
            
            if(isNaN(nh)) {
                nh = 0;
            }
            
            if(nw < 1 && nh < 1) {
                return;
            }
            
            if(nw < 1) {
                nw = this._width;
            }
            
            if(nh < 1) {
                nh = this._height;
            }
            
            if(nw == this._width && nh == this._height) {
                return;
            }
            
            var sw = nw;
            var sh = nh;
            
            if(sw > sh) {
                sh = Math.ceil(sh*this._width/sw);
                sw = this._width;
                
                if(sh > this._height) {
                    sw = Math.ceil(sw*this._height/sh);
                    sh = this._height;
                }
            } else {
                sw = Math.ceil(sw*this._height/sh);
                sh = this._height;   
                
                if(sw > this._width) {
                    sh = Math.ceil(sh*this._width/sw);
                    sw = this._width;                    
                }
            }
            
            var sx = 0;
            var sy = 0;
            
            if(typeof fx == 'undefined') {
                fx = 'middle';
            }
            
            if(typeof fy == 'undefined') {
                fy = 'middle';
            }
            
            if (fx == 'middle') {
                sx = Math.ceil((this._width - sw) / 2);
            } else if (fx == 'right') {
                sx = this._width - sw;
            }
            
            if (fy == 'middle') {
                sy = Math.ceil((this._height - sh) / 2);
            } else if (fy == 'right') {
                sy = this._height - sh;
            }
                        
            var canvas = document.createElement('canvas');
            canvas.width = nw;
            canvas.height = nh;
            this._highDPICanvasDrawImage(canvas, this._image[0], sx, sy, sw, sh, 0, 0, nw, nh);
            
            this._width = nw;
            this._height = nh;
            this._image.attr('src', canvas.toDataURL());
        };
        
        this._crop = function(x1, y1, x2, y2) {
            x1 = parseInt(x1);
            y1 = parseInt(y1);
            x2 = parseInt(x2);
            y2 = parseInt(y2);
            
            if(isNaN(x1)) {
                x1 = 0;
            }
            
            if(isNaN(y1)) {
                y1 = 0;
            }
            
            if(isNaN(x2)) {
                x2 = this._width;
            }
            
            if(isNaN(y2)) {
                y2 = this._height;
            }
            
            if(x1 > x2) {
                var buff = x1;
                x1 = x2;
                x2 = buff;
            }
            
            if(y1 > y2) {
                var buff = y1;
                y1 = y2;
                y2 = buff;
            }
            
            var w = x2 - x1;
            var h = y2 - y1;
                        
            var canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            this._highDPICanvasDrawImage(canvas, this._image[0], x1, y1, w, h, 0, 0, w, h);
            
            this._width = w;
            this._height = h;
            this._image.attr('src', canvas.toDataURL());
        };
        
        this._highDPICanvasDrawImage = function(canvas, image, srcx, srcy, srcw, srch, desx, desy, desw, desh) {            
            var resample_hermite = function (canvas, W, H, W2, H2) {
                W2 = Math.round(W2);
                H2 = Math.round(H2);
                var img = canvas.getContext("2d").getImageData(0, 0, W, H);
                var img2 = canvas.getContext("2d").getImageData(0, 0, W2, H2);
                var data = img.data;
                var data2 = img2.data;
                var ratio_w = W / W2;
                var ratio_h = H / H2;
                var ratio_w_half = Math.ceil(ratio_w/2);
                var ratio_h_half = Math.ceil(ratio_h/2);
                
                for(var j = 0; j < H2; j++) {
                    for(var i = 0; i < W2; i++) {
                        var x2 = (i + j*W2) * 4;
			var weight = 0;
			var weights = 0;
			var weights_alpha = 0;
			var gx_r = gx_g = gx_b = gx_a = 0;
			var center_y = (j + 0.5) * ratio_h;
                        
			for(var yy = Math.floor(j * ratio_h); yy < (j + 1) * ratio_h; yy++) {
                            var dy = Math.abs(center_y - (yy + 0.5)) / ratio_h_half;
                            var center_x = (i + 0.5) * ratio_w;
                            var w0 = dy*dy; //pre-calc part of w
                            
                            for(var xx = Math.floor(i * ratio_w); xx < (i + 1) * ratio_w; xx ++) {
                                var dx = Math.abs(center_x - (xx + 0.5)) / ratio_w_half;
                                var w = Math.sqrt(w0 + dx*dx);
                                if(w >= -1 && w <= 1) {
                                    //hermite filter
                                    weight = 2 * w*w*w - 3*w*w + 1;
                                    if(weight > 0){
                                        dx = 4*(xx + yy*W);
                                        //alpha
                                        gx_a += weight * data[dx + 3];
                                        weights_alpha += weight;
                                        //colors
                                        if(data[dx + 3] < 255) weight = weight * data[dx + 3] / 250;
                                        gx_r += weight * data[dx];
                                        gx_g += weight * data[dx + 1];
                                        gx_b += weight * data[dx + 2];
                                        weights += weight;
                                    }
                                }
                            }	
                        }
			data2[x2]     = gx_r / weights;
			data2[x2 + 1] = gx_g / weights;
			data2[x2 + 2] = gx_b / weights;
			data2[x2 + 3] = gx_a / weights_alpha;
                    }
		}
                
                canvas.getContext("2d").clearRect(0, 0, Math.max(W, W2), Math.max(H, H2));
                canvas.width = W2;
                canvas.height = H2;
                canvas.getContext("2d").putImageData(img2, 0, 0);
            };       
            
            var pcv = document.createElement('canvas');
            pcv.width = srcw;
            pcv.height = srch;
            var pctx = pcv.getContext('2d');
            pctx.drawImage(image, srcx, srcy, srcw, srch, 0, 0, srcw, srch);
            
            resample_hermite(pcv, srcw, srch, desw, desh);
            
            canvas.getContext('2d').drawImage(pcv, 0, 0, desw, desh, desx, desy, desw, desh);
        };
        
        var self = this;
        
        if(typeof img != 'object' || ! img.jquery) {
            img = $(img);
        }
        
        this._image = $('<img />');
        this._target = img;
        this._ready_flag = false;
        this._watermask_processing_flag = false;
        this._callback_registry = [];
        this._recall_timer = null;
                
        this.config(opts);
    
        this._image.load(function(){
            self._image.unbind('load');
            
            self._width = this.width;
            self._height = this.height;
            
            self._ready_flag = true;
        });
        this._image.attr('src', img.attr('src'));
    }
    
    $.fn.osc_imgProcessor = function() {
        var func = null;
        
        if(arguments.length > 0 && typeof arguments[0] == 'string') {
            func = arguments[0];
        }
        
        if(func) {
            var opts = [];
        
            for(var x = 1; x < arguments.length; x ++) {
                opts.push(arguments[x]);
            }
        } else {
            opts = arguments[0];
        }
               
        return this.each(function() {
            var instance = $(this).data('osc-img-processor');
            
            if(func) {
                if(instance) {                  
                    try {
                        instance[func].apply(instance, opts);
                    } catch(e){ alert(e.message); }
                }
            } else {
                if(! instance) {                    
                    try {
                        $(this).data('osc-img-processor', new OSC_Image_Processor(this, opts));
                    } catch(e){ alert(e.message); }
                }
            }
        });
    };
})(jQuery);