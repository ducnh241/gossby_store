(function($){
    function OSC_Cropper(target, options) {
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
            
            this._cropped_area.osc_resizer('config', {
                min_w : this.min_w,
                min_h : this.min_h,
                max_w : this.max_w,
                max_h : this.max_h,
                ratio : this.ratio
            });
        };
        
        this.setCoords = function(coords) {            
            coords.x1 += this._container.offset().left;
            coords.y1 += this._container.offset().top;
            coords.x2 += this._container.offset().left;
            coords.y2 += this._container.offset().top;
            
            this._cropped_area.osc_resizer('setCoords', coords);
        };
        
        this._setupOverlay = function() {
            var cropped_area_offset = this._cropped_area.offset();
            
            this._cropped_coords = {
                x1 : cropped_area_offset.left - this._container.offset().left,
                y1 : cropped_area_offset.top - this._container.offset().top,
            };
            
            this._cropped_coords.x2 = this._cropped_coords.x1 + this._cropped_area.width();
            this._cropped_coords.y2 = this._cropped_coords.y1 + this._cropped_area.height();
                        
            this._overlay.top.height(this._cropped_coords.y1);
            this._overlay.left.width(this._cropped_coords.x1).height(this._cropped_coords.y2 - this._cropped_coords.y1).css('top', this._cropped_coords.y1 + 'px');
            this._overlay.right.width(this._container.width() - this._cropped_coords.x2).height(this._cropped_coords.y2 - this._cropped_coords.y1).css('top', this._cropped_coords.y1 + 'px');
            this._overlay.bottom.height(this._container.height() - this._cropped_coords.y2);
            
            try {
                this.callback(this._cropped_coords);
            } catch(e) {};            
        };
        
        var self = this;
        
        if(typeof target != 'object' || ! target.jquery) {
            target = $(target);
        }
        
        this._target = target;
        this._container = $('<div />').addClass('osc-cropper').insertBefore(this._target);
        this._container.append(this._target);
        this._target.addClass('osc-cropper-content');
        this._overlay = {
            top : $('<div />').addClass('osc-cropper-overlay top-overlay').appendTo(this._container),
            left : $('<div />').addClass('osc-cropper-overlay left-overlay').appendTo(this._container),
            right : $('<div />').addClass('osc-cropper-overlay right-overlay').appendTo(this._container),
            bottom : $('<div />').addClass('osc-cropper-overlay bottom-overlay').appendTo(this._container)
        };
        this._renderable_area = $('<div />').addClass('osc-cropper-renderable-area').appendTo(this._container);
        this._cropped_area = $('<div />').addClass('osc-cropper-cropped-area').appendTo(this._container);
            
        this._cropped_area.osc_resizer({
            bound_obj : this._renderable_area,
            always_display_handler: true,
            resize_mode : 'fixed',
            dragable : true,
            renderable : true,
            additional_css_class : 'osc-cropper-cropped-area',
            callback : function(params) {
                self._setupOverlay();
            }
        });
        
        this.callback = null;
        this.ratio = 0;
        this.min_w = null;
        this.min_h = null;
        this.max_w = null;
        this.max_h = null;
        this.display_on_init = false;
        this.on_load_coords = null;
                
        this.config(options);
        
        this._cropped_coords = {x1 : 0, y1 : 0, x2 : this._container.width(), y2 : this._container.height()};
        
        if(this.display_on_init) {
            if(this.on_load_coords != null) {
                this._cropped_coords = this.on_load_coords;
            }
            
            this.setCoords(this._cropped_coords);
        }
    }
		
    $.fn.osc_cropper = function() {
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
            var instance = $(this).data('osc-cropper');
                
            if(func) {
                if(instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if(! instance) {
                    $(this).data('osc-cropper', new OSC_Cropper(this, opts));
                }
            }
        });
    };
})(jQuery);