/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

(function($){
    var OSC_TOGGLE_MENU_DEFAULT_CONFIG = {
        menu : '',
        hover_delay_time : 1,
        divergent_x : 0,
        divergent_y : 0,
        toggle_mode : 0,
        auto_toggle : false,
        open_hook : null,
        after_open_hook : null,
        close_hook : null,
        hold_menu_click : true,
        lock_doc_hide : false,
        force_set_position : false  
    };
    
    function OSC_ToggleMenu() {
        this.CUR_INST = null;
        
        this.hideCurrentMenu = function () {
            if(this.CUR_INST) {
                this.CUR_INST.hide();
            }
        },
        
        this.menuClickHook = function(e) {
            var inst = $(this).data('osc-toggle-menu-owner');
            
            if(inst.hold_menu_click) {
                inst.lock_doc_hide = true;
            }
        }
    }
    
    function OSC_ToggleMenu_Item(toggler, options) {
        this.hide = function(e) {
            if(! this.menu_opened) {
                return;
            }
            
            clearTimeout(this.timer);

            this.toggler.removeClass('toggled');
            this.menu.removeClass('active').attr('toggleMenu_inst', null);
            
            this.menu_opened = false;

            if(typeof this.close_hook == 'function') {
                this.close_hook({inst : this});
            }
            
            $.osc_toggleMenu.CUR_INST = null;
            
            $(document).unbind('click', this.doc_click_hook);
        }
        
        this.show = function(e) {
            if(this.menu_opened) {
                return;
            }
            
            this.toggler.addClass('toggled');

            if($.osc_toggleMenu.CUR_INST) {
                $.osc_toggleMenu.CUR_INST.hide();
            }

            $.osc_toggleMenu.CUR_INST = this;

            this.menu.addClass('active').data('osc-toggle-menu-owner', this);
    			
            var set_to_hide = false;

            if(typeof this.open_hook == 'function') {
                var callback_return = this.open_hook({inst : this});
    			
                if(typeof callback_return == 'object') {
                    if(callback_return.set_to_hide) {
                        set_to_hide = true;
                    }
                }
            }

            this.setPosition();

            this.menu_opened = true;
            
            var self = this;
            
            this.timer = setTimeout(function() { $(document).bind('click', self.doc_click_hook) }, 100);
    			
            if(typeof this.after_open_hook == 'function') {
                var callback_return = this.after_open_hook({inst : this});
    			
                if(typeof callback_return == 'object') {
                    if(callback_return.set_to_hide) {
                        set_to_hide = true;
                    }
                }
            }
    			
            if(set_to_hide) {
                this.hide();
                return false;
            }
        }
        
        this._calPosition_TopLeft = function(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy, dx, dy) {
            var x = bx;
            var y = by - h;
                        
            x += dx;
            y += dy;
            
            if(x + w > vx + vw && ! this.force_set_position) {
                if(fx > 0) {
                    
                } else {
                    return this._calPosition_TopRight(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx + 1, fy, -dx, dy);
                }
            }
            
            if(y < vy && ! this.force_set_position) {
                if(fy > 0) {
                    
                } else {
                    return this._calPosition_BottomLeft(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy + 1, dx, -dy);
                }
            }
            
            return {x : x, y : y};            
        }
        
        this._calPosition_TopRight = function(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy, dx, dy) {
            var x = (bx + bw) - w;
            var y = by - h;
                        
            x += dx;
            y += dy;
            
            if(x < vx && ! this.force_set_position) {
                if(fx > 0) {
                    
                } else {
                    return this._calPosition_TopLeft(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx + 1, fy, -dx, dy);
                }
            }
            
            if(y < vy && ! this.force_set_position) {
                if(fy > 0) {
                    
                } else {
                    return this._calPosition_BottomRight(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy + 1, dx, -dy);
                }
            }
            
            return {x : x, y : y};
        }
        
        this._calPosition_BottomLeft = function(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy, dx, dy) {
            var x = bx;
            var y = by + bh;
            
            x += dx;
            y += dy;
            
            if(x + w > vx + vw && ! this.force_set_position) {
                if(fx > 0) {
                    
                } else {
                    return this._calPosition_BottomRight(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx + 1, fy, -dx, dy);
                }
            }
            
            if(y + h > vy + vh && ! this.force_set_position) {
                if(fy > 0) {
                    
                } else {
                    return this._calPosition_TopLeft(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy + 1, dx, -dy);
                }
            }
            
            return {x : x, y : y};
        }
        
        this._calPosition_BottomRight = function(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy, dx, dy) {
            var x = (bx + bw) - w;
            var y = by + bh;
            
            x += dx;
            y += dy;
            
            if(x < vx && ! this.force_set_position) {
                if(fx > 0) {
                    
                } else {
                    return this._calPosition_BottomLeft(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx + 1, fy, -dx, dy);
                }
            }
            
            if(y + h > vy + vh && ! this.force_set_position) {
                if(fy > 0) {
                    
                } else {
                    return this._calPosition_TopRight(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy + 1, dx, -dy);
                }
            }
            
            return {x : x, y : y};
        }
        
        this._calPosition_LeftTop = function(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy, dx, dy) {
            
        }
        
        this._calPosition_LeftBottom = function(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy, dx, dy) {
            
        }
        
        this._calPosition_RightLeft = function(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy, dx, dy) {
            
        }
        
        this._calPosition_RightBottom = function(vx, vy, vw, vh, bx, by, bw, bh, w, h, fx, fy, dx, dy) {
            
        }
        
        this.setPosition = function() {
            var position = {};
            var offset = this.toggler.offset();
            
            var vp = $.getViewPort();
            var dd = $.getDocumentDim(); 
            
            var pos_getter = 'BottomRight';
        	
            switch(this.toggle_mode) {
                case '1':
                case 'br':
                    pos_getter = 'BottomRight';
                    break;
                case '2':
                case 'bl':                    
                    pos_getter = 'BottomLeft';
                    break;
                case '3':
                case 'tr':                    
                    pos_getter = 'TopRight';
                    break;
                case '4':
                case 'tl':                    
                    pos_getter = 'TopLeft';
                    break;
                case '5':
                case 'lt':                    
                    pos_getter = 'LeftTop';
                    break;
                case '6':
                case 'lb':                    
                    pos_getter = 'LeftBottom';
                    break;
                case '7':
                case 'rt':                    
                    pos_getter = 'RightTop';
                    break;
                case '8':
                case 'rb':                    
                    pos_getter = 'RightBottom';
                    break;
            }
            
            position = this['_calPosition_' + pos_getter](vp.x, vp.y, vp.w, vp.h, offset.left, offset.top, this.toggler.realWidth(), this.toggler.realHeight(), this.menu.realWidth(), this.menu.realHeight(), 0, 0, this.divergent_x, this.divergent_y);
                                
            this.menu.offset({top : position.y, left : position.x});
        }
                
        this.timer = null;        
        this.toggler = null;
        this.menu_opened = false;    
        
        $.extend(this, OSC_TOGGLE_MENU_DEFAULT_CONFIG);
        
        if( typeof options != 'object' ) {
            options = {};
        }
        
        options.toggler = $(toggler);
            
        if(typeof options.menu == 'string' || typeof options.menu.tagName == 'string') {
            options.menu = $(options.menu);
        }
        
        options.menu.addClass('osc-toggle-menu');
        
        $.extend(this, options);
        
        this.divergent_x = parseInt(this.divergent_x);
        
        if(isNaN(this.divergent_x)) {
            this.divergent_x = 0;
        }
        
        this.divergent_y = parseInt(this.divergent_y);
        
        if(isNaN(this.divergent_y)) {
            this.divergent_y = 0;
        }

        this.toggle_mode = this.toggle_mode.toString().trim().toLowerCase();
        
        var func_names = ['open_hook', 'after_open_hook', 'close_hook'];

        for(var k = 0; k < func_names.length; k ++) {
            if(typeof this[func_names[k]] == 'string') {
                this[func_names[k]] = this[func_names[k]].replace(/^\s+|\s+$/g, '');
                
                if((/[^a-zA-Z0-9\$\_\.]/).test(this[func_names[k]])) {
                    eval('this.' + func_names[k] + ' = function(params){' + this[func_names[k]] + '}');
                } else {                
                    eval('this.' + func_names[k] + ' = ' + this[func_names[k]]);
                }
            }
        }        
                
        this.doc_click_hook = function(e) { if(self.lock_doc_hide) { self.lock_doc_hide = false; return; } var target = $(e.target); if(!target.closest('body')[0] || target.closest('[data-menu-elm="1"]')[0]){ return; } self.hide(e); };
        this.menu_click_hook = function(e) { var inst = $(this).attr('toggle-menu-owner'); if(! inst.hold_menu) { inst.hide(); }};
        
        var self = this;
        
        this.toggler.bind('click', function(e) { self.show(e); }).bind('focus', function(e) { self.show(e); });
        this.menu.unbind('click', $.osc_toggleMenu.menuClickHook).bind('click', $.osc_toggleMenu.menuClickHook);
            
        if(this.auto_toggle) {
            var timer = null;
            
            this.toggler.hover(
                    function(e) {
                        timer = setTimeout(function(){ self.show(e); }, self.hover_delay_time * 1000);
                    },
                    function(e) {
                        clearTimeout(timer);
                    });
        }
    }

    $.osc_toggleMenu = new OSC_ToggleMenu(); // singleton instance

    $.fn.osc_toggleMenu = function() {
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
            var instance = $(this).data('osc-toggle-menu');
                
            if(func) {
                if(instance) {
                    instance[func].apply(instance, opts);
                }
            } else {
                if(! instance) {
                    $(this).data('osc-toggle-menu', new OSC_ToggleMenu_Item(this, opts));
                }
            }
        });
    };
    
    $(document.body).on('click', '.mrk-toggle-menu', function(e) {
        var elm = $(this);
        
        elm.removeClass('mrk-toggle-menu');
        
        var opts = elm.data('tmConfig');
                
        if(typeof opts != 'undefined') {            
            elm.removeAttr('data-tm-config');
        } else {
            opts = {};
            
            $.extend(opts, OSC_TOGGLE_MENU_DEFAULT_CONFIG);
            
            for(var key in opts) {
                var data_key = key.split('_');
                
                for(var i = 0; i < data_key.length; i ++) {
                    data_key[i] = data_key[i].substring(0,1).toUpperCase() + data_key[i].substring(1).toLowerCase();
                }
                
                data_key = data_key.join('');
                                                
                var val = elm.data('tm' + data_key);
                
                elm.removeAttr('data-tm-' + key.replace(/_/g, "-"));
                            
                if(typeof val != 'undefined') {
                    opts[key] = val;
                }
            }
        }
        
        if(typeof opts.menu != 'undefined') {
            $(this).osc_toggleMenu(opts).trigger('click');
        }
    });
})(jQuery);