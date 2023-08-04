(function($) {    
    function OSC_ColorPicker() {
        this.add = function(item) {
            this.items[item._container.attr('id')] = item;
        };
        
        this._fixHSB = function (H, S, B) {
            H = parseInt(H);
            S = parseInt(S);
            B = parseInt(B);
            
            if(isNaN(H)) {
                H = 0;
            }
            
            if(isNaN(S)) {
                S = 0;
            }
            
            if(isNaN(B)) {
                B = 0;
            }
            
            return {
                H : Math.min(360,Math.max(0,H)),
                S : Math.min(100,Math.max(0,S)),
                B : Math.min(100,Math.max(0,B))
            };
        };
        
        this._fixRGB = function (R, G, B) {
            return {
                R: Math.min(255,Math.max(0,R)),
                G: Math.min(255,Math.max(0,G)),
                B: Math.min(255,Math.max(0,B))
            };
        };
        
        this._fixHex = function (hex) {
            var len = 6-hex.length;
            
            if (len > 0) {
                var o = [];
                
                for (var i=0; i<len; i++) {
                    o.push('0');
                }
                
                o.push(hex);
                
                hex = o.join('');
            }
            
            return hex;
        };
        
        this._HSB2RGB = function(H, S, B) {
            var RGB = {};
            
            H = Math.round(H);
            S = Math.round(S*255/100);
            
            var V = Math.round(B*255/100);
            
            if(S == 0) {
                RGB.R = RGB.G = RGB.B = V;
            } else {
                var t1 = V;
                var t2 = (255-S)*V/255;
                var t3 = (t1-t2)*(H%60)/60;
                
                if(H == 360){
                    H = 0;
                }
                
                if(H < 60) {
                    RGB.R = t1;
                    RGB.G = t2+t3;
                    RGB.B = t2;
                } else if(H < 120) {
                    RGB.R = t1-t3;
                    RGB.G = t1;
                    RGB.B = t2;
                } else if(H < 180) {
                    RGB.R = t2;
                    RGB.G = t1;
                    RGB.B = t2+t3;
                } else if(H < 240) {
                    RGB.R = t2;
                    RGB.G = t1-t3;
                    RGB.B = t1;
                } else if(H < 300) {
                    RGB.R = t2+t3;
                    RGB.G = t2;
                    RGB.B = t1;
                } else if(H < 360) {
                    RGB.R = t1;
                    RGB.G = t2;
                    RGB.B = t1-t3;
                } else {
                    RGB.R = 0;
                    RGB.G = 0;
                    RGB.B = 0;
                }
            }
            
            for(var x in RGB) {
                RGB[x] = Math.round(RGB[x]);
            }
            
            return RGB;
        };
        
        this._RGB2HSB = function (R, G, B) {
            var HSB = {
                H : 0, 
                S : 0, 
                B : 0
            };
            
            var min = Math.min(R, G, B);
            var max = Math.max(R, G, B);
            
            var delta = max - min;
            
            HSB.B = max;            
            HSB.S = max != 0 ? 255*delta/max : 0;
            
            if (HSB.S != 0) {
                if (R == max) {
                    HSB.H = (G-B)/delta;
                } else if (G == max) {
                    HSB.H = 2+(B-R)/delta;
                } else {
                    HSB.H = 4+(R-G)/delta;
                }
            } else {
                HSB.H = -1;
            }
            
            HSB.H *= 60;
            
            if (HSB.H < 0) {
                HSB.H += 360;
            }
            
            HSB.S *= 100/255;
            HSB.B *= 100/255;
            
            return HSB;
        };
        
        this._RGB2CMYK = function(R, G, B) {     
            R /= 255;
            G /= 255;
            B /= 255;
            
            var CMYK = {};
	
            CMYK.K = Math.min(1-R, 1-G, 1-B);
            CMYK.C = (1-R-CMYK.K)/(1-CMYK.K);
            CMYK.M = (1-G-CMYK.K)/(1-CMYK.K);
            CMYK.Y = (1-B-CMYK.K)/(1-CMYK.K);

            CMYK.C = Math.round(CMYK.C*100);
            CMYK.M = Math.round(CMYK.M*100);
            CMYK.Y = Math.round(CMYK.Y*100);
            CMYK.K = Math.round(CMYK.K*100);
            
            for(var x in CMYK) {
                if(isNaN(CMYK[x])) {
                    CMYK[x] = 0;
                }
            }
            
            return CMYK;
        };
        
        this._CMYK2RGB = function(C, M, Y, K) {
            C /= 100;
            M /= 100;
            Y /= 100;
            K /= 100;
            
            var RGB = {};

            RGB.R = 1 - Math.min(1, C*(1-K)+K);
            RGB.G = 1 - Math.min(1, M*(1-K)+K);
            RGB.B = 1 - Math.min(1, Y*(1-K)+K);

            RGB.R = Math.round(RGB.R*255);
            RGB.G = Math.round(RGB.G*255);
            RGB.B = Math.round(RGB.B*255);
            
            return RGB;  
        };
        
        this._CMYK2HSB = function(C, M, Y, K) {
            var RGB = this._CMYK2RGB(C, M, Y, K);
            return this._RGB2HSB(RGB.R, RGB.G, RGB.B);
        };
        
        this._RGB2Hex = function (R, G, B) {
            var hex = [this._dec2Hex(R),this._dec2Hex(G),this._dec2Hex(B)];
            
            $.each(hex, function (nr, val) {
                if (val.length == 1) {
                    hex[nr] = '0' + val;
                }
            });
            
            return hex.join('').toLowerCase();
        };
        
        this._HSB2Hex = function (H, S, B) {
            var RGB = this._HSB2RGB(H, S, B);            
            return this._RGB2Hex(RGB.R, RGB.G, RGB.B);
        };
        
        this._Hex2RGB = function (hex) {
            var hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
            
            return {
                R : hex>>16, 
                G : (hex&0x00FF00)>>8, 
                B : hex&0x0000FF
            };
        };
        
        this._Hex2HSB = function (hex) {
            var RGB = this._Hex2RGB(hex);
            return this._RGB2HSB(RGB.R, RGB.G, RGB.B);
        };
        
        this._dec2Hex = function(dec) {
            return dec.toString(16);
        };
        
        this._webSafe = function(R,G,B) {
            var tmp;
            
            tmp = R%51;
                
            if(tmp > 25) {
                tmp = R+51-tmp;
            } else {
                tmp = R-tmp;
            }
                
            var c1 = this._dec2Hex(Math.round(tmp/17));
                
            tmp = G%51;
            
            if(tmp > 25){
                tmp = G+51-tmp;
            } else {
                tmp = G-tmp;
            }
            
            var c2 = this._dec2Hex(Math.round(tmp/17));
            
            tmp = B%51;
            
            if(tmp > 25) {
                tmp = B+51-tmp;
            } else {
                tmp = B-tmp;
            }
            
            var c3 = this._dec2Hex(Math.round(tmp/17));
            
            return (c1+c1+c2+c2+c3+c3).toLowerCase();
        };
        
        this._menu_wrap = $('<div />').addClass('color-picker-menu-wrap').appendTo(document.body);
        this.items = {};
    }
    
    function OSC_ColorPicker_Item(container, options) {      
        var self = this;
        
        this.config = function(options) {
            var self = this;
            
            if(typeof options != 'object') {
                options = {};
            }
            
            for(var k in options) {
                if(k.substr(0,1) == '_') {
                    delete options[k];
                }
            }

            $.extend(this, options);
            
            if(typeof this.renderer != 'object' || this.renderer === null) {
                this.renderer = new OSC_ColorPicker_Renderer();
            }

            this.renderer.setInstance(this);

            this._container.osc_toggleMenu({
                menu : $.osc_colorPicker._menu_wrap,
                divergent : this.divergent,
                open_hook : function(params){
                    try {
                        self.fire_hook({inst : self});
                    } catch(e) {
                        
                    }
                    
                    self._renderColorTable(params.inst);
                },
                close_hook : function(){
                    $.osc_colorPicker._menu_wrap.html('');
                }
            });

            $.osc_colorPicker.add(this);
        };
        
        this._renderColorTable = function(menu) {
            var color_table = this.renderer.renderColorTable();
            $.osc_colorPicker._menu_wrap.html(color_table);

            var self = this;

            $('.mrk-color-picker-toggler', color_table).click(function() { self.renderColorPicker(); });
            $('.mrk-color-item', color_table).click(function(e) { self.select($(this).attr('hex')); menu.hide(e); });
        };
        
        this.toSwatches = function(hex) {
            var buffer = [];

            buffer.push(hex);

            for(var i = 0; i < this._recent_colors.length; i ++) {
                if(this._recent_colors[i] != hex) {
                    buffer.push(this._recent_colors[i]);

                    if(buffer.length == 10) {
                        break;
                    }
                }
            }

            this._recent_colors = buffer;
        };
        
        this.setColor = function(hex) {
            this.select(hex);
        };
        
        this.select = function(hex) {
            this.toSwatches(hex);

            try {
                this.select_hook({inst : this, hex : hex});
            } catch(e) {

            }
        };
        
        this.renderColorPicker = function() {
            var color_picker = this.renderer.renderColorPicker();

            var win = $.create_window({
                title : 'Color Picker',
                content : color_picker
            });

            var self = this;

            for(var x in this._inputs) {
                for(var y in this._inputs[x]) {
                    this._inputs[x][y].attr('color_mode', x).blur(function() { self.updateColor($(this).attr('color_mode')); });
                }
            }

            this.renderer.safe_color.click(function(){ self._inputs.hex.hex.val($(this).attr('safe_hex')); self.updateColor('hex'); });

            this.renderer.hue_area.osc_dragger({
                target : this.renderer.hue_slider,
                cursor : 'pointer',
                fire_hook_callback : function(params){ self.setHueSliderDragArea(params); },
                drag_hook_callback : function(){ self._hueSliderMoveHook(); }
            });

            this.renderer.overlay.osc_dragger({
                target : this.renderer.selector,
                cursor : 'crosshair',
                fire_hook_callback : function(params){ self.setOverlayDragArea(params); self.renderer.overlay.addClass('dis-cursor'); },
                drag_hook_callback : function(){ self._selectorMoveHook(); },
                drop_hook_callback : function(){ self.renderer.overlay.removeClass('dis-cursor'); }
            });

            this.renderer.buttons.select.click(function(e) { self.select(self.hex_value); win.destroy(e); });
            this.renderer.buttons.add2Swatches.click(function(e) { self.toSwatches(self.hex_value); win.destroy(e); self._container.osc_toggleMenu('show'); });

            this.hex_value = 'c00000';
            this._inputs.hex.hex.val(this.hex_value);

            this.updateColor('hex');
        };
        
        this.updateColor = function(color_mode, skip_reset_position) {
            var HSB, RGB, CMYK;

            if(typeof color_mode != 'string') {
                color_mode = 'hsb';
            }

            switch(color_mode.toLowerCase()) {
                case 'hex':
                    HSB = $.osc_colorPicker._Hex2HSB(this._inputs.hex.hex.val());
                    break;
                case 'rgb':
                    HSB = $.osc_colorPicker._RGB2HSB(this._inputs.RGB.R.val(), this._inputs.RGB.G.val(), this._inputs.RGB.B.val());
                    break;
                case 'cmyk':
                    HSB = $.osc_colorPicker._CMYK2HSB(this._inputs.CMYK.C.val(), this._inputs.CMYK.M.val(), this._inputs.CMYK.Y.val(), this._inputs.CMYK.K.val());
                    break;
                default:
                    HSB = $.osc_colorPicker._fixHSB(this._inputs.HSB.H.val(), this._inputs.HSB.S.val(), this._inputs.HSB.B.val());
            }

            this._inputs.HSB.H.val(HSB.H);
            this._inputs.HSB.S.val(HSB.S);
            this._inputs.HSB.B.val(HSB.B);

            if(this._hue_value != HSB.H) {
                this._hue_value = HSB.H;
                this.renderer.selector_area.css('background-color', '#' + $.osc_colorPicker._HSB2Hex(HSB.H, 100, 100));
            }

            this.hex_value = $.osc_colorPicker._HSB2Hex(HSB.H, HSB.S, HSB.B);

            this._inputs.hex.hex.val(this.hex_value);

            var RGB = $.osc_colorPicker._HSB2RGB(HSB.H, HSB.S, HSB.B);

            var safe_hex = $.osc_colorPicker._webSafe(RGB.R, RGB.G, RGB.B);

            this._inputs.RGB.R.val(RGB.R);
            this._inputs.RGB.G.val(RGB.G);
            this._inputs.RGB.B.val(RGB.B);

            var CMYK = $.osc_colorPicker._RGB2CMYK(RGB.R, RGB.G, RGB.B);

            this._inputs.CMYK.C.val(CMYK.C);
            this._inputs.CMYK.M.val(CMYK.M);
            this._inputs.CMYK.Y.val(CMYK.Y);
            this._inputs.CMYK.K.val(CMYK.K);

            this._color_preview.css('background-color', '#' + this.hex_value);

            if(safe_hex != this.hex_value) {
                this.renderer.safe_color.show().attr('safe_hex', safe_hex);
                this.renderer.safe_color_preview.css('background-color', '#' + safe_hex);
            } else {
                this.renderer.safe_color.hide();            
            }

            if(!skip_reset_position) {
                var hue_slider_obj = this.renderer.hue_slider;
                var hue_area_obj = this.renderer.hue_area;

                hue_slider_obj.css({
                    left : (hue_area_obj.width()-hue_slider_obj.width())/2 + 'px',
                    top : (hue_area_obj.height()-(HSB.H/360*hue_area_obj.height())-(hue_slider_obj.height()/2)) + 'px'
                });

                var overlay_obj = this.renderer.overlay;
                var selector_obj = this.renderer.selector;

                selector_obj.css({
                    top : (overlay_obj.height()-(HSB.B/100*overlay_obj.height())-(selector_obj.height()/2)) + 'px',
                    left : ((HSB.S/100*overlay_obj.width())-(selector_obj.width()/2)) + 'px'
                });
            }
        };
        
        this._selectorMoveHook = function() {            
            var x = this.renderer.selector.position().left;
            var y = this.renderer.selector.position().top;

            this._inputs.HSB.S.val(x/this.renderer.overlay.width()*100);
            this._inputs.HSB.B.val(100-y/this.renderer.overlay.height()*100);

            this.updateColor(null, true);
        };

        this._hueSliderMoveHook = function() {            
            var y = this.renderer.hue_slider.position().top;

            this._inputs.HSB.H.val(360 - y/this.renderer.hue_area.height()*360);

            this.updateColor(null, true);
        };

        this.setOverlayDragArea = function(params) {
            var overlay_offset = this.renderer.overlay.offset();

            this.renderer.selector.offset({
                top : params.e.pageY, 
                left : params.e.pageX
            });       

            this._selectorMoveHook();

            params.inst.config({
                min_x : overlay_offset.left,
                min_y : overlay_offset.top,
                max_x : overlay_offset.left + this.renderer.overlay.width(),
                max_y : overlay_offset.top + this.renderer.overlay.height()
            });
        };

        this.setHueSliderDragArea = function(params) {
            var hue_area_offset = this.renderer.hue_area.offset();

            this.renderer.hue_slider.offset({
                top : params.e.pageY, 
                left : hue_area_offset.left + (this.renderer.hue_area.width()-this.renderer.hue_slider.width())/2
            });

            this._hueSliderMoveHook();

            params.inst.config({
                min_x : hue_area_offset.left + (this.renderer.hue_area.width()-this.renderer.hue_slider.width())/2,
                min_y : hue_area_offset.top,
                max_x : hue_area_offset.left + (this.renderer.hue_area.width()+this.renderer.hue_slider.width())/2,
                max_y : hue_area_offset.top + this.renderer.hue_area.height()
            });
        };
        
        if(typeof container != 'object') {
            container = $('#' + container);
        } else if(typeof container.tagName == 'string') {
            container = $(container);
        }
        
        this._container = container;
        this._theme_colors = [['ffffff','f2f2f2','d8d8d8','bfbfbf','a5a5a5','7f7f7f'],
                              ['000000','7f7f7f','595959','3f3f3f','262626','0c0c0c'],
                              ['eeece1','ddd9c3','c4bd97','938953','494429','1d1b10'],
                              ['1f497d','c6d9f0','8db3e2','548dd4','17365d','0f243e'],
                              ['4f81bd','dbe5f1','b8cce4','95b3d7','366092','244061'],
                              ['c0504d','f2dcdb','e5b9b7','d99694','953734','632423'],
                              ['9bbb59','ebf1dd','d7e3bc','c3d69b','76923c','4f6128'],
                              ['8064a2','e5e0ec','ccc1d9','b2a2c7','5f497a','3f3151'],
                              ['4bacc6','dbeef3','b7dde8','92cddc','31859b','205867'],
                              ['f79646','fdeada','fbd5b5','fac08f','e36c09','974806']];
        this._standard_colors = ['c00000','ff0000','ffc000','ffff00','92d050','00b050','00b0f0','0070c0','002060','7030a0'];
        this._recent_colors = [];
        this._color_preview = null;
        this._inputs = {
            HSB : {}, 
            RGB : {}, 
            CMYK : {},
            hex : {}
        };
        this._hue_value = 0;
        
        this.renderer = null;
        this.divergent_x = 0;
        this.divergent_y = 0;
        this.fire_hook = null;
        this.select_hook = null;
        
        this.config(options);
    }
    
function OSC_ColorPicker_Renderer() {
        
    }
    
    $.extend(OSC_ColorPicker_Renderer.prototype, {
        inst : null,
        overlay : null,
        lang : {
            theme_color : 'Theme colors', 
            standard_color : 'Standard colors', 
            recent_color : 'Recent colors', 
            color_picker : 'Color picker',
            RGB_title : 'RGB',
            HSB_title : 'HSB',
            CMYK_title : 'CMYK'
        },
        
        setInstance : function(inst) {
            this.inst = inst;
            return this;
        },
        
        renderColorTable : function() {
            var main, tbl, tr, td, div;
            
            main = $.createElement('div', {className : 'popup-main'});
            
            tbl = $.createElement('table', {cellSpacing : 0}, {}, main);
            
            tr = tbl.insertRow(-1);
            td = tr.insertCell(-1);

            td.colSpan = this.inst._theme_colors.length;

            $.createElement('div', {className : 'head', innerHTML : this.lang.theme_color}, {}, td);

            tr = [tbl.insertRow(-1), tbl.insertRow(-1)];

            for(var x = 0; x < this.inst._theme_colors.length; x ++) {
                td = tr[0].insertCell(-1);
                $($.createElement('div', {className : 'mrk-color-item color-item'}, {background : '#' + this.inst._theme_colors[x][0]}, td)).attr('hex', this.inst._theme_colors[x][0]);

                div = $.createElement('div', {className : 'theme-color-wrap'});

                for(var y = 1; y < this.inst._theme_colors[x].length; y ++) {
                    $($.createElement('div', {className : 'mrk-color-item color-item'}, {background : '#' + this.inst._theme_colors[x][y]}, div)).attr('hex', this.inst._theme_colors[x][y]);
                }
                
                td = tr[1].insertCell(x);
                td.style.width      = '10px';
                td.style.padding    = '3px';
                td.style.background = '#ffffff';
                td.appendChild(div);
            }
            
            tbl = $.createElement('table', {cellSpacing : 0}, {}, main);
            
            tr = tbl.insertRow(-1);
            td = tr.insertCell(0);
            
            td.colSpan = this.inst._theme_colors.length;
            $.createElement('div', {className : 'head', innerHTML : this.lang.standard_color}, {}, td);
            
            tr = tbl.insertRow(-1);
            
            for(var x = 0; x < this.inst._standard_colors.length; x ++) {
                td = tr.insertCell(-1);
                
                $($.createElement('div', {className : 'mrk-color-item color-item'}, {background : '#' + this.inst._standard_colors[x]}, td)).attr('hex', this.inst._standard_colors[x]);
            }
            
            if(this.inst._recent_colors.length > 0) {
                tbl = $.createElement('table', {
                    cellSpacing : 0
                }, {}, main);
                
                tr = tbl.insertRow(-1);
                
                td = tr.insertCell(-1);
                td.colSpan = this.inst._theme_colors.length;
                
                $.createElement('div', {innerHTML : this.lang.recent_color, className : 'head'}, {}, td);
                
                tr = tbl.insertRow(-1);
                
                for(var x = 0; x < this.inst._recent_colors.length; x ++) {
                    td = tr.insertCell(-1);                    
                    $($.createElement('div', {className : 'mrk-color-item color-item'}, {background : '#' + this.inst._recent_colors[x]}, td)).attr('hex', this.inst._recent_colors[x]);
                }
                
                if(this.inst._recent_colors.length < 10) {
                    td = tr.insertCell(-1);
                    td.colSpan = 10 - this.inst._recent_colors.length;
                }
            }
            
            $.createElement('div', {
                innerHTML : this.lang.color_picker, 
                className : 'color-picker-toggler mrk-color-picker-toggler'
            }, {}, main);
            
            return main;
        },
        
        renderColorPicker : function() {
            var self = this;
            
            var scene = $('<div />').addClass('color-picker');
            
            this.selector_area = $('<div />').addClass('selector-area').appendTo(scene);
            this.overlay = $('<div />').addClass('overlay').appendTo(this.selector_area);
            this.selector = $('<div />').addClass('selector').appendTo(this.overlay);
            
            this.hue_area = $('<div />').addClass('hue-area').appendTo(scene);
            this.hue_slider = $('<div />').addClass('slider').appendTo(this.hue_area);
                        
            var form = $('<div />').addClass('form-area').appendTo(scene);
            
            var section = $('<div />').addClass('section').appendTo(form);
            
            var preview = $('<div />').addClass('color-preview').appendTo(section);
            this.inst._color_preview = $('<div />').addClass('preview').appendTo(preview);
            this.inst._inputs.hex.hex = $('<input />').appendTo(preview);
            
            this.safe_color = $('<div />').addClass('safe-color').appendTo(section);
            $('<div />').addClass('icon').appendTo(this.safe_color);
            this.safe_color_preview = $('<div />').addClass('preview').appendTo(this.safe_color);
            
            this.buttons = {};
            
            var action_scene = $('<div />').addClass('action').appendTo(section);            
            this.buttons.select = $('<span />').addClass('btn btn-mini').html('Select the color').appendTo(action_scene);
            this.buttons.add2Swatches = $('<span />').addClass('btn btn-mini').html('Add to swatches').appendTo(action_scene);
            
            section = $('<div />').addClass('section').appendTo(form);
            
            var color_modes = ['RGB','HSB','CMYK'];
            
            for(var i = 0; i < color_modes.length; i ++) {
                var color_mode = color_modes[i];
                
                var input_group = $('<div />').addClass('input-group ' + color_mode.toLowerCase()).appendTo(section);
                
                $('<div />').addClass('label').html(color_mode).appendTo(input_group);
                
                var color_mode_elements = color_mode.split('');
                
                for(var k = 0; k < color_mode_elements.length; k ++) {
                    var input_element = $('<div />').addClass('input-element').appendTo(input_group);
                    $('<span />').html(color_mode_elements[k]).appendTo(input_element);            
                    this.inst._inputs[color_mode][color_mode_elements[k]] = $('<input />').attr('type', 'text').appendTo(input_element);
                }
            }
            
            return scene;
        }
    });
    
    $.osc_colorPicker = new OSC_ColorPicker();
    
    $.fn.osc_colorPicker = function() {
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
                var instance = $(this).data('osc-colorPicker');
                instance[func].apply(instance, opts);
            } else {
                $(this).data('osc-colorPicker', new OSC_ColorPicker_Item(this, opts));
            }
        });
    };
})(jQuery);