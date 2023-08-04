(function($){
    function OSC_Dragger() {
        var self = this;
        
        this.setInstance = function(dragger_item, e) {
            if(this._INSTANCE) {
                this._doc_mouseup_hook(e);
            }
            
            $(document.body).addClass('dis-sel');
            $(document).bind('mousemove touchmove', this._doc_mousemove_hook).bind('mouseup touchend', this._doc_mouseup_hook);
            
            this._INSTANCE = dragger_item;
        };
        
        this._doc_mousemove_hook = function(e) {
            self._INSTANCE._drag(e);
        };
        
        this._doc_mouseup_hook = function(e) {
            $(document.body).removeClass('dis-sel');
            $(document).unbind('mousemove touchmove', this._doc_mousemove_hook).unbind('mouseup touchend', this._doc_mouseup_hook);
            self._INSTANCE._drop(e);
            self._INSTANCE = null;
        };
        
        this._INSTANCE = null;
    };
    
    $.DRAGGER = new OSC_Dragger();
    
    function OSC_Dragger_Item(elm, options) {        
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
        
            if(typeof this.target != 'object' || ! this.target.jquery) {        
                this.target = $(this.target);
            }
            
            this.min_x = parseInt(this.min_x);
            
            if(isNaN(this.min_x) || this.min_x < 1) {
                this.min_x = 0;
            }
            
            this.min_y = parseInt(this.min_y);
            
            if(isNaN(this.min_y) || this.min_y < 1) {
                this.min_y = 0;
            }
            
            var doc_w = $(document).width();
            var doc_h = $(document).height();
            
            this.max_x = parseInt(this.max_x);
            
            if(isNaN(this.max_x) || this.max_x > doc_w) {
                this.max_x = doc_w;
            }
            
            this.max_y = parseInt(this.max_y);
            
            if(isNaN(this.max_y) || this.max_y > doc_h) {
                this.max_y = doc_h;
            }
            
            if(this.min_x > this.max_x) {
                var buff = this.max_x;
                
                this.max_x = this.min_x;
                this.min_x = buff;
            }
            
            if(this.min_y > this.max_y) {
                var buff = this.max_y;
                
                this.max_y = this.min_y;
                this.min_y = buff;
            }
            
            this.divergent_x = parseInt(this.divergent_x);
            
            if(isNaN(this.divergent_x)) {
                this.divergent_x = 0;
            }
            
            this.divergent_y = parseInt(this.divergent_y);
            
            if(isNaN(this.divergent_y)) {
                this.divergent_y = 0;
            }
            
            this._elm.css('cursor', this.cursor);
        };
        
        this.lock = function() {
            this._locked_flag = true;
        };
        
        this.unlock = function() {
            this._locked_flag = false;
        };
        
        this.forceDrop = function(e) {
            $.DRAGGER._doc_mouseup_hook(e);
        };
        
        this._fire = function(e) {
            e.stopPropagation();
            
            if(this._locked_flag) {
                return true;
            }

            try {
                if(this.fire_hook_callback({e : e, inst : this}) === false) {
                    return true;
                }
            } catch(err) {};
            
            var target_offset = this.target.offset();
            
            this._coords_in_bar.x = pointerEventToXY(e).x - target_offset.left;
            this._coords_in_bar.y = pointerEventToXY(e).y - target_offset.top;
                                    
            $.DRAGGER.setInstance(this, e);

            return false;
        };
        
        this._drag = function(e) {
            var x = pointerEventToXY(e).x - this._coords_in_bar.x;
            var y = pointerEventToXY(e).y - this._coords_in_bar.y;
            
            var real_max_x = this.max_x - this.target.width();
            var real_max_y = this.max_y - this.target.height();

            if(x < this.min_x) {
                x = this.min_x;
            } else if(real_max_x >= this.min_x && x > real_max_x) {
                x = real_max_x;
            }

            if(y < this.min_y) {
                y = this.min_y;
            } else if(real_max_y >= this.min_y && y > real_max_y) {
                y = real_max_y;
            }
		
            if(this.divergent_x) {
                x += this.divergent_x;
            }
		
            if(this.divergent_y) {
                y += this.divergent_y;
            }

            this.target.offset({left : x, top: y});

            try {
                this.drag_hook_callback({e : e, inst : this});
            } catch(err) {};
        };
        
        this._drop = function(e) {
            try {
                this.drop_hook_callback({e : e, inst : this});
            } catch(err) {};
        };
        
        var self = this;
        
        if(typeof elm != 'object' || ! elm.jquery) {        
            elm = $(elm);
        }
        
        this._elm = elm;        
        this._locked_flag = false;
        this._coords_in_bar = {x : 0, y : 0};  
        this._fire_hook = function(e){
            self._fire(e);
        };
        
        this._elm.bind('mousedown touchstart', this._fire_hook);
        
        this.target = this._elm;
        this.divergent_x = 0;
        this.divergent_y = 0;
        this.min_x = 0;
        this.min_y = 0;
        this.max_x = $(document).width();
        this.max_y = $(document).height();
        this.cursor = 'move';
        this.fire_hook_callback = null;
        this.drag_hook_callback = null;
        this.drop_hook_callback = null;
        
        this.config(options);        
    }

    function pointerEventToXY (e) {
        var out = {x: 0, y: 0};

        if (e.type === 'touchstart' || e.type === 'touchmove' || e.type === 'touchend' || e.type === 'touchcancel') {
            if (typeof e.touches !== 'undefined') {
                var touch = e.touches[0] || e.changedTouches[0];
            } else {
                var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
            }

            out.x = touch.pageX;
            out.y = touch.pageY;
        } else if (e.type === 'mousedown' || e.type === 'mouseup' || e.type === 'mousemove' || e.type === 'mouseover' || e.type === 'mouseout' || e.type === 'mouseenter' || e.type === 'mouseleave') {
            out.x = e.pageX;
            out.y = e.pageY;
        }

        return out;
    }
	
    $.fn.osc_dragger = function() {
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
            if(func) {
                var instance = $(this).data('osc-dragger');
                instance[func].apply(instance, opts);
            } else {
                $(this).data('osc-dragger', new OSC_Dragger_Item(this, opts));
            }
        });
    };
})(jQuery);