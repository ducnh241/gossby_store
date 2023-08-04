(function($){        
    $.event.props.push('wheelDelta');
    $.event.props.push('detail');
        
    function OSC_Scroller(container, options) {
        this.reset = function() {
            var container_obj = $(this.container);
            var content_obj = $(this.content);
            
            this.viewport_height = container_obj.height();
            this.content_height = content_obj.height();
            
            this.ratio = this.viewport_height/this.content_height;
            this.bar_ratio = this.content_height/this.viewport_height;
            
            this.thumb_height = parseInt(this.viewport_height*this.ratio);
            
            if(this.ratio >= 1) {
                $(this.track).hide();
            }
            
            var self = this;
            
            if(isNaN(this.thumb_height)) {
                this.thumb_height = 0;
                setTimeout(function(){
                    self.reset();
                }, 1000);
            }

            $(this.thumb).height(this.thumb_height);
        }
        
        this.scrollToObj = function(obj) {
            if(this.ratio >= 1) {
                return;
            }
            
            if(typeof obj == 'string') {
                obj = $(obj, this.content);
            } else if(obj.nodeName) {
                obj = $(obj);
            }
            
            var content_obj = $(this.content);
            
            var content_offset = content_obj.offset();
            var obj_offset = obj.offset();
            
            var iScroll = obj_offset.top - content_offset.top;
            
            if(this.content_height-iScroll < this.viewport_height) {
                iScroll = this.content_height - this.viewport_height;
            }
            
            iScroll = Math.min(this.content_height - this.viewport_height, Math.max(0, iScroll));
            
            this.y = Math.ceil(iScroll/this.bar_ratio);
            
            content_obj.css('top', -iScroll);
            $(this.thumb).css('top', this.y);
        }
        
        this._keydownHook = function(e) {
            if(this.ratio >= 1) {
                return;
            }
            
            var value = 0;
            
            if(e.keyCode == 38) {
                value = -20;
            } else if(e.keyCode == 40) {
                value = 20;
            } else {
                return;
            }
            
            e.preventDefault();
            
            switch(e.target.tagName) {
                case 'INPUT':
                    if($(e.target).attr('type').toUpperCase() == 'TEXT') {
                        return;
                    }
                    break;
                case 'TEXTAREA':
                    return;
                case 'SELECT':
                    return;
            }
                
            var content_obj = $(this.content);

            var iScroll = -parseInt(content_obj.css('top')) + value;
            
            iScroll = Math.min(this.content_height - this.viewport_height, Math.max(0, iScroll));
                
            this.y = Math.ceil(iScroll/this.bar_ratio);
                
            content_obj.css('top', -iScroll);
            $(this.thumb).css('top', this.y);
                
            this.position = iScroll;

            if(iScroll == 0) {
                this.in_bottom_called = false;

                if(!this.in_top_called && typeof this.callback.inTop == 'function') {
                    this.in_top_called = true;
                    this.callback.inTop({
                        e : e, 
                        inst : this
                    });
                }
            } else if(this.y >= (this.viewport_height - this.thumb_height)) {
                this.in_top_called = false;

                if(!this.in_bottom_called && typeof this.callback.inBottom == 'function') {
                    this.in_bottom_called = true;
                    this.callback.inBottom({
                        e : e, 
                        inst : this
                    });
                }
            } else {
                this.in_top_called = false;
                this.in_bottom_called = false;
            }
        }
        
        this.wheel = function(e){                   
            if(this.ratio >= 1) {
                return true;
            }             
            
            var wheel = 20;

            var content_obj = $(this.content);

            e = $.event.fix(e || window.event);

            var iDelta = e.wheelDelta ? e.wheelDelta/120 : -e.detail/3;
            var iScroll = parseInt(content_obj.css('top'));

            if(isNaN(iScroll)) {
                iScroll = 0;
            }
            
            var cur_coords = iScroll;

            iScroll = -iScroll;
            iScroll -= iDelta * wheel;
            iScroll = Math.min(this.content_height - this.viewport_height, Math.max(0, iScroll));
            
            if(Math.abs(iScroll) == Math.abs(cur_coords)) { 
                if(this._SKIP_TRANSFER_SCROLL_FLAG) {
                    e.preventDefault();
                }
                
                return true;
            }
            
            e.preventDefault();

            this.y = Math.ceil(iScroll/this.bar_ratio);

            content_obj.css('top', -iScroll);
            $(this.thumb).css('top', this.y);

            this.position = iScroll;

            if(typeof this.callback.wheel == 'function') {
                this.callback.wheel({
                    e : e, 
                    inst : this
                });
            }

            if(iScroll == 0) {
                this.in_bottom_called = false;

                if(!this.in_top_called && typeof this.callback.inTop == 'function') {
                    this.in_top_called = true;
                    this.callback.inTop({
                        e : e, 
                        inst : this
                    });
                }
            } else if(this.y >= (this.viewport_height - this.thumb_height)) {
                this.in_top_called = false;

                if(!this.in_bottom_called && typeof this.callback.inBottom == 'function') {
                    this.in_bottom_called = true;
                    this.callback.inBottom({
                        e : e, 
                        inst : this
                    });
                }
            } else {
                this.in_top_called = false;
                this.in_bottom_called = false;
            }
        }
        
        this.scroll = function(e) {
            if(this.ratio < 1){
                var content_obj = $(this.content);
                var thumb_obj = $(this.thumb);
                
                this.y = Math.min(this.viewport_height-this.thumb_height, Math.ceil(Math.max(0, (this.position_start + (e.pageY - this.mouse_start)))));
                
                var iScroll = this.y * this.bar_ratio;
                content_obj.css('top', -iScroll);
                thumb_obj.css('top', this.y);
                
                this.position = iScroll;
                
                if(this.y == 0) {
                    this.in_bottom_called = false;
                    
                    if(!this.in_top_called && typeof this.callback.inTop == 'function') {
                        this.in_top_called = true;
                        this.callback.inTop({
                            e : e, 
                            inst : this
                        });
                    }
                } else if(this.y >= (this.viewport_height - this.thumb_height)) {
                    this.in_top_called = false;
                    
                    if(!this.in_bottom_called && typeof this.callback.inBottom == 'function') {
                        this.in_bottom_called = true;
                        this.callback.inBottom({
                            e : e, 
                            inst : this
                        });
                    }
                } else {
                    this.in_top_called = false;
                    this.in_bottom_called = false;
                }
            }
        }
        
        this._setDragArea = function(e, dragger) {
            var track_obj = $(this.track);
            var thumb_obj = $(this.thumb);
            
            var track_offset = track_obj.offset();
            
            dragger.config({
                min_x : track_offset.left,
                min_y : track_offset.top,
                max_x : track_offset.left,
                max_y : track_offset.top + track_obj.height()
            });
                        
            this.mouse_start = e.pageY;
            var thumb_dir = parseInt(thumb_obj.css('top'));
            this.position_start = isNaN(thumb_dir) ? 0 : thumb_dir;
        }
        
        this._mouseOverHook = function() {
            clearTimeout(this.timer);
            
            if(this.auto_reset) {
                this.reset();
            }
            
            $(this.track)[this.ratio < 1 ? 'show' : 'hide']();
        }
        
        this._hideScroller = function() {
            if(this.scrolling) {
                var self = this;
                this.timer = setTimeout(function(){
                    self._hideScroller();
                }, 100);
                return;
            }
            
            $(this.track).hide();
        }
        
        if(typeof options != 'object') {
            options = {};
        }
        
        options.index = container.id;
        options.container = container;
        
        this.index = null;
        this.container = null;
        this.thumb = null;
        this.track = null;
        this.content = null;
        this.name = null;
        this.renderer = null;
        this._hook_funcs = {
            mouse_over : null, 
            mouse_out : null
        };
        this.callback = {};
        this.timer = null;
        this.in_top_called = null;
        this.in_bottom_called = null;
        this.scrolling = false;
        this._SKIP_TRANSFER_SCROLL_FLAG = false;
        this.transfer_scroll = true;
        
        $.extend(this, options);
        
        if(! this.transfer_scroll) {
            this._SKIP_TRANSFER_SCROLL_FLAG = true;
        }
        
        if(typeof this.renderer != 'object' || this.renderer === null) {
            this.renderer = new OSC_Scroller_Renderer();
        }
        
        this.renderer.setInstance(this).render();
        
        var self = this;
        
        $(this.thumb).osc_dragger({
            target : this.thumb,
            cursor_type : 'pointer',
            fire_hook_callback : function(params) {
                self.scrolling = true;

                if(typeof self.callback.fire == 'function') {
                    self.callback.fire({
                        e : params.e
                        });
                }

                self._setDragArea(params.e, params.inst);
            },
            drag_hook_callback : function(params) {
                self.scrolling = true;

                self.scroll(params.e);

                if(typeof self.callback.drag == 'function') {
                    self.callback.drag({
                        e : params.e
                        });
                }
            },
            drop_hook_callback : function(params) {
                self.scrolling = false;

                if(typeof self.callback.drop == 'function') {
                    self.callback.drop({
                        e : params.e
                        });
                }
            }
        });
        
        if(container.addEventListener) {
            container.addEventListener('DOMMouseScroll', function(e){
                self.wheel(e);
            }, false);
            container.addEventListener('mousewheel', function(e){
                self.wheel(e);
            }, false);
        } else {
            container.onmousewheel = function(e){
                self.wheel(e);
            };
        }
        
        this._hook_funcs._key_down = function(e) {
            self._keydownHook(e);
        }
        this._hook_funcs._mouse_over = function() {
            $(document).keydown(self._hook_funcs._key_down);
            self._mouseOverHook();
        };
        this._hook_funcs._mouse_out = function() {
            $(document).unbind('keydown', self._hook_funcs._key_down);
            self.timer = setTimeout(function(){
                self._hideScroller();
            }, 100);
        }
        
        $(container).hover(this._hook_funcs._mouse_over , this._hook_funcs._mouse_out);
        
        this.reset();
    }
    
    function OSC_Scroller_Renderer() {
        this.setInstance = function(inst) {
            this.inst = inst;
            return this;
        }
        
        this.render = function() {
            this.inst.content = $.createElement('div');
            
            for(var x = this.inst.container.childNodes.length - 1; x >= 0; x --) {
                if(this.inst.container.childNodes[x].nodeName) {
                    this.inst.content.appendChild(this.inst.container.childNodes[x]);
                }
            }
            
            $(this.inst.container).addClass('osc-scroller').html(this.inst.content);
            $(this.inst.content).addClass('content');
            
            this.inst.track = $.createElement('div', {
                className : 'track'
            }, {}, this.inst.container);
            this.inst.thumb = $.createElement('div', {
                className : 'thumb'
            }, {}, this.inst.track);
        }
        
        this.inst = null;
    }
    
    $.fn.osc_scroller = function() {
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
            var instance = $(this).data('osc-scroller');
                
            if(func) {
                if(instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if(! instance) {
                    $(this).data('osc-scroller', new OSC_Scroller(this, opts));
                }
            }
        });
    }
})(jQuery);