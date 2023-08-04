(function($) {
    function OSC_Resizer(elm, options) {        
        var self = this;
        
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
            
            switch(this.resize_mode.toString().toLowerCase()) {
                case '2':
                case 'fixed':
                    this.resize_mode = 'fixed';
                    break;
                case '3':
                case 'fixed_w':
                    this.resize_mode = 'fixed_w';
                    break;
                case '4':
                case 'fixed_h':
                    this.resize_mode = 'fixed_h';
                    break;
                default:
                    this.resize_mode = 'free';
            }
            
            if(typeof this.restrict_area == 'object' && this.restrict_area != null) {
                var missed = false;
                
                var check_keys = ['x1', 'y1', 'x2', 'y2'];
                
                for(var i = 0; i < check_keys.length; i ++) {
                    var check_key = check_keys[i];
                    
                    if(typeof this.restrict_area[check_key] == 'undefined') {
                        missed = true;
                        break;
                    }
                    
                    this.restrict_area[check_key] = parseInt(this.restrict_area[check_key]);
                    
                    if(isNaN(this.restrict_area[check_key])) {
                        missed = true;
                        break;
                    }
                    
                    if(this.restrict_area[check_key] < 0) {
                        this.restrict_area[check_key] = 0;
                    }
                }
                
                if(missed) {
                    this.restrict_area = null;
                } else if(this.restrict_area.x1 == this.restrict_area.x2 || this.restrict_area.y1 == this.restrict_area.y2) {
                    this.restrict_area = null;
                } else {
                    this.restrict_area = this._transformCoords(this.restrict_area.x1, this.restrict_area.x2, this.restrict_area, 'x');
                    this.restrict_area = this._transformCoords(this.restrict_area.y1, this.restrict_area.y2, this.restrict_area, 'y');
                }
            } else {
                this.restrict_area = null;
            }
            
            if(this.bound_obj) {
                if(typeof this.bound_obj == 'string') {
                    this.bound_obj = $(this.bound_obj);
                } else if(typeof this.bound_obj == 'object') {
                    if(typeof this.bound_obj.tagName == 'string') {
                        this.bound_obj = $(this.bound_obj);
                    }
                }
                
                if(! this.bound_obj[0]) {
                    this.bound_obj = null;
                }
            }
                
            this._ratio = {x : 0, y : 0};
            
            this.ratio = parseFloat(this.ratio);
            
            if(this.ratio != 0) {
                this._ratio = {
                    x : 1,
                    y : 1/this.ratio
                };
            } else if(this.resize_mode == 'fixed') {
                this._ratio = {
                    x : this._elm.width(),
                    y : this._elm.height()
                };
            }

            var gcd = this._getGCD(this._ratio.x, this._ratio.y);

            this._ratio.x = this._ratio.x/gcd;
            this._ratio.y = this._ratio.y/gcd;
            
            this._elm.unbind('mouseenter', this._mouseenterTrigger);
            
            this._removeHandlers();
            
            if(this.always_display_handler) {
                this._renderHandlers();
            } else {
                this._elm.mouseenter(this._mouseenterTrigger);
            }
            
            if(this.bound_obj) {
                this.bound_obj.unbind('mousedown touchstart', this._boundObjMousedownTrigger);
                
                if(this.renderable) {
                    this.bound_obj.bind('mousedown touchstart', this._boundObjMousedownTrigger);
                }
            }
        };
        
        this._removeHandlers = function() {
            if(! this._wrapper) {
                return;
            }
            
            this._elm.insertBefore(this._wrapper);
            this._elm.unbind('dragstart');
            this._elm.removeClass('element');
            this._elm.css({
                width : this._wrapper.width() + 'px',
                height : this._wrapper.height() + 'px'
            });
            this._elm.offset({top : this._wrapper.offset().top, left : this._wrapper.offset().left});
            this._wrapper.remove();
            
            this._wrapper = null;
            
            if(! this.always_display_handler) {                
                this._elm.mouseenter(this._mouseenterTrigger);   
            }
        };
        
        this._renderHandlers = function() {
            var self = this;
            
            this._removeHandlers();
            
            this._wrapper = $('<div />').addClass('osc-resizer');
            this._wrapper.insertBefore(this._elm);
            this._wrapper.css({
                width : this._elm.width() + 'px',
                height : this._elm.height() + 'px'
            });
            this._wrapper.offset({top : this._elm.offset().top, left : this._elm.offset().left});
            
            if(this.additional_css_class) {
                this._wrapper.addClass(this.additional_css_class);
            }
            
            this._elm.css({top : 0, left : 0});
            this._elm.addClass('element');
            this._elm.bind('dragstart', function(e) { e.preventDefault(); });
            this._wrapper.append(this._elm);
            
            var handle_keys = ['NW','N','NE','W','E','SW','S','SE'];
            
            for(var i = 0; i < handle_keys.length; i ++) {
                var handler = $('<div />').addClass('handler ' + handle_keys[i]).appendTo(this._wrapper);
                
                handler.bind('mousedown touchstart', function(e){
                    self._initResize(e, $(this));
                });
            }
            
            if(! this.always_display_handler) {
                this._elm.unbind('mouseenter', this._mouseenterTrigger);     
                this._wrapper.mouseleave(this._mouseleaveTrigger);
            }
            
            if(this.dragable) {                
                this._wrapper.osc_dragger({
                    target : this._wrapper,
                    fire_hook_callback : function(params) {                        
                        if(self._resizing_flag || self._rendering_flag) {
                            return false;
                        }
                        
                        self._dragging_flag = true;
                        
                        self._detectRestrictArea();
                        
                        if(! self.restrict_area) {
                            return true;
                        }
                        
                        params.inst.config({
                            min_x : self.restrict_area.x1,
                            min_y : self.restrict_area.y1,
                            max_x : self.restrict_area.x2,
                            max_y : self.restrict_area.y2
                        });
                    },
                    drag_hook_callback : function(params) {
                        try {
                            self.callback({inst : this});
                        } catch(e) {};                        
                    },
                    drop_hook_callback : function(params) {
                        self._dragging_flag = false;
                        self._tryRemoveHandlers(params.e);
                    }
                });
            }
        };
        
        this._detectRestrictArea = function() {
            if(this.bound_obj) {
                this.restrict_area = {
                    x1 : this.bound_obj.offset().left,
                    y1 : this.bound_obj.offset().top,
                    x2 : this.bound_obj.offset().left + this.bound_obj.width(),
                    y2 : this.bound_obj.offset().top + this.bound_obj.height()
                };
            }
        };
        
        this._initRenderer = function(e) {
            if(this._resizing_flag || this._dragging_flag) {
                return;
            }
            
            this._rendering_flag = true;
            
            this._render_anchor_coords = {
                x : pointerEventToXY(e).x,
                y : pointerEventToXY(e).y
            };
                
            this._renderHandlers();
            
            this._setCoords({x1 : pointerEventToXY(e).x, y1 : pointerEventToXY(e).y, x2 : pointerEventToXY(e).x, y2 : pointerEventToXY(e).y});
            
            this._initDocDrag();
        };
        
        this._initResize = function(e, handler) {
            if(this._rendering_flag || this._dragging_flag) {
                return;
            }
            
            this._resizing_flag = true;
            this._resize_handle = handler[0].className.toString().replace(/([^N|NE|E|SE|S|SW|W|NW])+/, '');
		
            this._initDocDrag();
        };
        
        this._initDocDrag = function() {
            var self = this;
            
            $(document.body).addClass('dis-sel');
            
            this._coords_backup = {
                x1 : this._wrapper.offset().left,
                y1 : this._wrapper.offset().top,
                x2 : this._wrapper.offset().left + this._wrapper.width(),
                y2 : this._wrapper.offset().top + this._wrapper.height()
            };
            
            this._detectRestrictArea();
                
            this._drag_function = function(e){
                self._drag(e);
            };
            this._drop_function = function(e){
                self._drop(e);
                $(document.body).removeClass('dis-sel');
            };
			
            $(document).bind('mousemove touchmove', this._drag_function).bind('mouseup touchend', this._drop_function);            
        };
        
        this._drag = function(e) {            
            var cursorCoordsInsideTarget = {
                x : pointerEventToXY(e).x,
                y : pointerEventToXY(e).y
            };

            var coords = {};

            $.extend(coords, this.getCoords());

            var direction = {
                x : 1, 
                y : 1
            };
                
            if(this._rendering_flag) {
                if(cursorCoordsInsideTarget.x < this._render_anchor_coords.x) {
                    direction.x = -1;
                }

                if(cursorCoordsInsideTarget.y < this._render_anchor_coords.y) {
                    direction.y = -1;
                }

                coords = this._transformCoords(cursorCoordsInsideTarget.x, this._render_anchor_coords.x, coords, 'x');
                coords = this._transformCoords(cursorCoordsInsideTarget.y, this._render_anchor_coords.y, coords, 'y');
            } else {            

                if(this._resize_handle.match(/E/))	{
                    coords = this._transformCoords(cursorCoordsInsideTarget.x, this._coords_backup.x1, coords, 'x');

                    if(cursorCoordsInsideTarget.x < this._coords_backup.x1) {
                        direction.x = -1;
                    }
                } else if(this._resize_handle.match(/W/)) {
                    coords = this._transformCoords(cursorCoordsInsideTarget.x, this._coords_backup.x2, coords, 'x');

                    if(cursorCoordsInsideTarget.x < this._coords_backup.x2) {
                        direction.x = -1;
                    }
                }

                if(this._resize_handle.match(/N/))	{
                    coords = this._transformCoords(cursorCoordsInsideTarget.y, this._coords_backup.y2, coords, 'y');

                    if(cursorCoordsInsideTarget.y < this._coords_backup.y2) {
                        direction.y = -1;
                    }
                } else if(this._resize_handle.match(/S/)) {
                    coords = this._transformCoords(cursorCoordsInsideTarget.y, this._coords_backup.y1, coords, 'y');

                    if(cursorCoordsInsideTarget.y < this._coords_backup.y1) {
                        direction.y = -1;
                    }
                }
            }

            this._setCoords(coords, e.shiftKey, direction);
        };
        
        this._tryRemoveHandlers = function(e) {
            if(! this._resizing_flag && ! this._rendering_flag && ! this._dragging_flag && ! this.always_display_handler && (! e || (pointerEventToXY(e).x < this._wrapper.offset().left || pointerEventToXY(e).x > this._wrapper.offset().left + this._wrapper.width() || pointerEventToXY(e).y < this._wrapper.offset().top || pointerEventToXY(e).y > this._wrapper.offset().top + this._wrapper.height()))) {
                this._removeHandlers();
            }
        };
        
        this._drop = function(e) {
            this._resizing_flag = false;
            this._rendering_flag = false;
            this._shift_mode_ratio = null;
            
            this._tryRemoveHandlers(e);
            
            $(document).unbind('mousemove touchmove', this._drag_function).unbind('mouseup touchend', this._drop_function);
        };
        
        this._transformCoords = function(min, max, coords, axis) {
            var new_pos = [min, max];
		
            if(min > max) {
                new_pos.reverse();
            }
		
            coords[axis + '1'] = new_pos[0];
            coords[axis + '2'] = new_pos[1];
		
            return coords;
        };
        
        this.setCoords = function(coords) {
            this._renderHandlers();
            this._setCoords(coords, false, {x : 1, y : 1});
            this._tryRemoveHandlers();
        };
        
        this._setCoords = function(coords, shift_mode, direction) {
            if(this.restrict_area) {
                if(coords.x1 < this.restrict_area.x1) {
                    coords.x1 = this.restrict_area.x1;
                }

                if(coords.y1 < this.restrict_area.y1) {
                    coords.y1 = this.restrict_area.y1;
                }

                if(coords.x2 > this.restrict_area.x2) {
                    coords.x2 = this.restrict_area.x2;
                }

                if(coords.y2 > this.restrict_area.y2) {
                    coords.y2 = this.restrict_area.y2;
                }
            } else {
                if(coords.x1 < 0) {
                    coords.x1 = 0;
                }

                if(coords.y1 < 0) {
                    coords.y1 = 0;
                }
            }

            if(direction != null) {
                if(this.min_w > 0 || this.min_h > 0 || this.max_w > 0 || this.max_h > 0) {
                    var coordsTransX = {
                        a1: coords.x1,
                        a2: coords.x2
                    };

                    var coordsTransY = {
                        a1: coords.y1,
                        a2: coords.y2
                    };   
                    
                    var boundsX = {min : 0, max : 0};          
                    var boundsY = {min : 0, max : 0};
                    
                    if(this.restrict_area) {
                        boundsX.min = this.restrict_area.x1;
                        boundsX.max = this.restrict_area.x2;
                        boundsY.min = this.restrict_area.y1;
                        boundsY.max = this.restrict_area.y2;
                    }

                    if(this.min_w > 0) {
                        coordsTransX = this._applyDimRestriction(coordsTransX, this.min_w, direction.x, boundsX, 'min');
                    }

                    if(this.min_h > 0) {
                        coordsTransY = this._applyDimRestriction(coordsTransY, this.min_h, direction.y, boundsY, 'min');
                    }

                    if(this.max_w > 0) {
                        coordsTransX = this._applyDimRestriction(coordsTransX, this.max_w, direction.x, boundsX, 'max');
                    }

                    if(this.max_h > 0) {
                        coordsTransY = this._applyDimRestriction(coordsTransY, this.max_h, direction.y, boundsY, 'max');
                    }

                    coords = {
                        x1: coordsTransX.a1,
                        y1: coordsTransY.a1,
                        x2: coordsTransX.a2,
                        y2: coordsTransY.a2
                    };
                }
                
                if(this._ratio.x > 0) {
                    coords = this._applyRatio(coords, this._ratio, direction);
                } else if(shift_mode) {
                    if(this._shift_mode_ratio == null) {
                        this._shift_mode_ratio   = {};

                        this._shift_mode_ratio.x = parseInt(coords.x2 - coords.x1);
                        this._shift_mode_ratio.y = parseInt(coords.y2 - coords.y1);

                        if(isNaN(this._shift_mode_ratio.x) || this._shift_mode_ratio.x == 0) {
                            this._shift_mode_ratio.x = 1;
                        }

                        if(isNaN(this._shift_mode_ratio.y) || this._shift_mode_ratio.y == 0) {
                            this._shift_mode_ratio.y = 1;
                        }

                        var gcd = this._getGCD(this._shift_mode_ratio.x, this._shift_mode_ratio.y);

                        this._shift_mode_ratio.x = this._shift_mode_ratio.x/gcd;
                        this._shift_mode_ratio.y = this._shift_mode_ratio.y/gcd;
                    }

                    coords = this._applyRatio(coords, this._shift_mode_ratio, direction);
                }
            }
		
            this._wrapper.offset({
                top : coords.y1,
                left : coords.x1
            });
            this._wrapper.width(coords.x2 - coords.x1);
            this._wrapper.height(coords.y2-coords.y1);
            
            try {
                this.callback({inst : this});
            } catch(e) {};
        };
        
        this.getCoords = function() {            
            return {
                x1 : this._elm.offset().left,
                y1 : this._elm.offset().top,
                x2 : this._elm.offset().left + this._elm.width(),
                y2 : this._elm.offset().top + this._elm.height()
            };
        };
        
        this._applyDimRestriction = function(coords, val, direction, bounds, type) {
            var check;
		
            if(type == 'min') {
                check = (coords.a2 - coords.a1) < val;
            } else {
                check = (coords.a2 - coords.a1) > val;
            }
		
            if(check) {
                if(direction == 1) {
                    coords.a2 = coords.a1 + val;
                } else {
                    coords.a1 = coords.a2 - val;
                }

                if(coords.a1 < bounds.min) {
                    coords.a1 = bounds.min;
                    coords.a2 = val;
                } else if(bounds.max > 0 && coords.a2 > bounds.max) {
                    coords.a1 = bounds.max - val;
                    coords.a2 = bounds.max;
                }
            }
		
            return coords;
        };
	
        this._getGCD = function(a, b) {
            if(b == 0) {
                return a;
            }
		
            return this._getGCD(b, a%b);
        };
        
        this._applyRatio = function(coords, ratio, direction) {
            var new_coords;
                
            var bounds = {min : 0, max : 0};
            
            if(this.restrict_area) {
                coords.x1 -= this.restrict_area.x1;
                coords.x2 -= this.restrict_area.x1;
                coords.y1 -= this.restrict_area.y1;
                coords.y2 -= this.restrict_area.y1;
            }
		
            if(this._resize_handle == 'N' || this._resize_handle == 'S') {                
                if(this.restrict_area) {
                    bounds.min = 0;
                    bounds.max = this.restrict_area.x2 - this.restrict_area.x1;
                }
                
                new_coords = this._applyRatioToAxis(
                    {
                        a1 : coords.y1, 
                        b1 : coords.x1, 
                        a2 : coords.y2, 
                        b2 : coords.x2
                    }, {
                        a : ratio.y, 
                        b : ratio.x
                    }, {
                        a : direction.y, 
                        b : direction.x
                    }, bounds
                );

                coords.x1 = new_coords.b1;
                coords.y1 = new_coords.a1;
                coords.x2 = new_coords.b2;
                coords.y2 = new_coords.a2;
            } else {                
                if(this.restrict_area) {
                    bounds.min = 0;
                    bounds.max = this.restrict_area.y2 - this.restrict_area.y1;
                }
                
                new_coords = this._applyRatioToAxis(
                    {
                        a1 : coords.x1, 
                        b1 : coords.y1, 
                        a2 : coords.x2, 
                        b2 : coords.y2
                    }, {
                        a : ratio.x, 
                        b : ratio.y
                    }, {
                        a : direction.x, 
                        b : direction.y
                    }, bounds
                );
					
                coords.x1 = new_coords.a1;
                coords.y1 = new_coords.b1;
                coords.x2 = new_coords.a2;
                coords.y2 = new_coords.b2;
            }
            
            if(this.restrict_area) {
                coords.x1 += this.restrict_area.x1;
                coords.x2 += this.restrict_area.x1;
                coords.y1 += this.restrict_area.y1;
                coords.y2 += this.restrict_area.y1;
            }
		
            return coords;
        };
        
        this._applyRatioToAxis = function(coords, ratio, direction, bounds) {
            var newCoords = {};
			
            $.extend(newCoords, coords);
			
            var calcDimA  = newCoords.a2 - newCoords.a1;
            var targDimB  = Math.floor(calcDimA*ratio.b/ratio.a);
            var targB;
            var targDimA;
            var calcDimB = null;

            if(direction.b == 1) {
                targB = newCoords.b1 + targDimB;
			
                if(bounds.max > 0 && targB > bounds.max) {
                    targB = bounds.max;
                    calcDimB = targB - newCoords.b1;
                }

                newCoords.b2 = targB;
            } else {
                targB = newCoords.b2 - targDimB;
			
                if(targB < bounds.min) {
                    targB = bounds.min;
                    calcDimB = targB + newCoords.b2;
                }
			
                newCoords.b1 = targB;
            }

            if(calcDimB != null) {
                targDimA = Math.floor(calcDimB*ratio.a/ratio.b);

                if(direction.a == 1) {
                    newCoords.a2 = newCoords.a1 + targDimA;
                } else {
                    newCoords.a1 = newCoords.a1 = newCoords.a2 - targDimA;
                }
            }

            return newCoords;
        };
        
        this._mouseenterTrigger = function(e) {       
            self._renderHandlers();
        };
        
        this._mouseleaveTrigger = function(e) {        
            if(! self._resizing_flag && ! self._dragging_flag && ! self._rendering_flag) {
                self._removeHandlers();
            }
        };
        
        this._boundObjMousedownTrigger = function(e) {
            self._initRenderer(e);
        };
        
        if(typeof elm != 'object' || ! elm.jquery) {
            elm = $(elm);
        }

        /*
         Resize modes:
            1 - free
            2 - fixed
            3 - fixed_w
            4 - fixed_h
         */
        this.resize_mode = 'free';
        this.dragable = false;
        this.renderable = false;
        this.min_w = 0;
        this.min_h = 0;
        this.max_w = 0;
        this.max_h = 0;
        this.ratio = 0;
        this.always_display_handler = false;
        this.bound_obj = null;
        this.additional_css_class = '';
        this.restrict_area = {
            x1 : 0, 
            y1 : 0, 
            x2 : 0, 
            y2 : 0
        };
        
        this._resize_handle = null;
        this._drag_function = null;
        this._drop_function = null;
        this._shift_mode_ratio = null;
        this._elm = elm;
        this._wrapper = null;
        this._resizing_flag = false;
        this._dragging_flag = false;
        this._rendering_flag = false;
        this._coords_backup = {};
        this._render_anchor_coords = {};
        this._ratio = {};
        
        this.config(options);
    };
	
    $.fn.osc_resizer = function() {
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
        
        if(func && func.toLowerCase() == 'getcoords') {
            var instance = $(this[0]).data('osc-resizer');
            
            if(instance) {
                return instance.getCoords();                
            } else {
                return null;
            }
        }
               
        return this.each(function() {
            if(func) {
                var instance = $(this).data('osc-resizer');
                instance[func].apply(instance, opts);
            } else {
                $(this).data('osc-resizer', new OSC_Resizer(this, opts));
            }
        });
    };
})(jQuery);